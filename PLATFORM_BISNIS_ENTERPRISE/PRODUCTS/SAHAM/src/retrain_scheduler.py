"""
Automated Retraining Scheduler dengan Drift Detection.

Memantau model performance dan feature drift secara berkala,
memicu retraining otomatis ketika kondisi terpenuhi.

Referensi:
- Evidently AI: ML monitoring best practices
- Microsoft Qlib: automated retraining pipeline
- Google MLOps: continuous monitoring & retraining
"""

import os
import json
import pandas as pd
from datetime import datetime
from typing import Dict, Optional, List
from .config import MODELS_DIR
from .quant_finance import detect_feature_drift, detect_performance_drift


class RetrainingScheduler:
    """
    Manages automated model retraining based on:
    1. Time-based schedule (weekly/monthly)
    2. Feature drift detection (KS test)
    3. Performance degradation (accuracy drop)
    """

    def __init__(self):
        self.state_file = os.path.join(MODELS_DIR, "retrain_state.json")
        self.state = self._load_state()

    def _load_state(self) -> Dict:
        if os.path.exists(self.state_file):
            with open(self.state_file, "r") as f:
                return json.load(f)
        return {
            "last_train_date": None,
            "last_accuracy": None,
            "baseline_accuracy": None,
            "baseline_features": None,
            "retrain_count": 0,
            "drift_history": [],
        }

    def _save_state(self):
        with open(self.state_file, "w") as f:
            json.dump(self.state, f, indent=2, default=str)

    def check_retrain_needed(
        self,
        current_df: pd.DataFrame,
        feature_cols: List[str],
        current_accuracy: Optional[float] = None,
        schedule: str = "weekly",
        drift_threshold: float = 0.05,
        performance_threshold: float = 0.05,
    ) -> Dict:
        """
        Check if model needs retraining.

        Returns dict with:
        - needs_retrain: bool
        - reasons: list of triggered conditions
        - drift_report: feature drift analysis
        - performance_report: performance drift analysis
        """
        reasons = []
        now = datetime.now()

        # 1. Time-based check
        if self.state["last_train_date"]:
            last = datetime.fromisoformat(self.state["last_train_date"])
            days_since = (now - last).days
            schedule_days = {"daily": 1, "weekly": 7, "monthly": 30}
            threshold = schedule_days.get(schedule, 7)
            if days_since >= threshold:
                reasons.append(f"Schedule: {days_since} days since last train (threshold: {threshold}d)")
        else:
            reasons.append("Never trained before")

        # 2. Feature drift check
        drift_report = None
        if self.state.get("baseline_features"):
            baseline_df = pd.DataFrame(self.state["baseline_features"])
            drift_report = detect_feature_drift(
                baseline_df, current_df, feature_cols, threshold=drift_threshold
            )
            if drift_report.is_drifted:
                n_drifted = len(drift_report.drifted_features)
                reasons.append(f"Feature drift: {n_drifted} features drifted (KS p<{drift_threshold})")

        # 3. Performance degradation check
        perf_report = None
        if current_accuracy is not None and self.state.get("baseline_accuracy"):
            perf_report = detect_performance_drift(
                self.state["baseline_accuracy"], current_accuracy, threshold=performance_threshold
            )
            if perf_report.is_drifted:
                reasons.append(
                    f"Performance drop: {perf_report.baseline_accuracy:.2%} → {perf_report.current_accuracy:.2%}"
                )

        needs_retrain = len(reasons) > 0

        return {
            "needs_retrain": needs_retrain,
            "reasons": reasons,
            "drift_report": drift_report,
            "performance_report": perf_report,
            "days_since_last_train": (now - datetime.fromisoformat(self.state["last_train_date"])).days
            if self.state.get("last_train_date") else None,
        }

    def record_training(
        self,
        accuracy: float,
        baseline_df: pd.DataFrame,
        feature_cols: List[str],
    ):
        """Record that training happened — update state."""
        self.state["last_train_date"] = datetime.now().isoformat()
        self.state["last_accuracy"] = accuracy
        if self.state["baseline_accuracy"] is None:
            self.state["baseline_accuracy"] = accuracy

        # Save baseline feature sample (last 200 rows for drift comparison)
        sample = baseline_df[feature_cols].tail(200)
        self.state["baseline_features"] = sample.to_dict(orient="list")
        self.state["retrain_count"] = self.state.get("retrain_count", 0) + 1
        self._save_state()

    def get_status(self) -> Dict:
        """Get current scheduler status."""
        return {
            "last_train_date": self.state.get("last_train_date"),
            "last_accuracy": self.state.get("last_accuracy"),
            "baseline_accuracy": self.state.get("baseline_accuracy"),
            "retrain_count": self.state.get("retrain_count", 0),
            "drift_history_count": len(self.state.get("drift_history", [])),
        }


def run_scheduled_retrain(
    market_data: dict,
    fred_data: Optional[dict] = None,
    schedule: str = "weekly",
    n_trials: int = 20,
) -> Dict:
    """
    Full automated retraining pipeline:
    1. Check if retrain needed
    2. If yes: fetch data, preprocess, tune hyperparams, retrain
    3. Record results
    """
    from .preprocessor import prepare_features, get_feature_columns, train_test_split_time
    from .models import HybridEnsemble
    from .validation import walk_forward_cv
    from .hyperopt import run_hyperopt_tuning, apply_best_params

    scheduler = RetrainingScheduler()

    # Fetch and prepare data
    df = prepare_features(market_data, fred_data=fred_data)
    if df.empty:
        return {"error": "No data for retraining"}

    feature_cols = get_feature_columns(df)
    df_clean = df.dropna(subset=feature_cols + ["Target_Direction"])

    # Check if retrain needed
    wf = walk_forward_cv(df_clean, feature_cols, n_folds=3)
    current_acc = wf.mean_da / 100.0

    check = scheduler.check_retrain_needed(
        df_clean, feature_cols,
        current_accuracy=current_acc,
        schedule=schedule,
    )

    if not check["needs_retrain"]:
        return {
            "retrained": False,
            "reason": "No retrain needed",
            "current_accuracy": current_acc,
            "status": scheduler.get_status(),
        }

    # Retrain
    print(f"[RETRAIN] Reasons: {', '.join(check['reasons'])}")

    # Send in-app notification for retrain event
    try:
        from .notifier import send_in_app
        send_in_app(
            kategori="RETRAIN",
            judul=f"🔄 Model Retrained — {', '.join(check['reasons'])}",
            pesan=(
                f"Model diretrain pada {datetime.now().strftime('%Y-%m-%d %H:%M')}\n"
                f"Alasan: {', '.join(check['reasons'])}\n"
                f"Accuracy sebelum: {current_acc:.2%}\n"
                f"Drift detected: {check['drift_report'].is_drifted if check['drift_report'] else False}\n"
                f"Performance degraded: {check['performance_report'].is_drifted if check['performance_report'] else False}"
            ),
            level="info",
        )
    except Exception:
        pass

    # Try hyperopt if available
    try:
        result = run_hyperopt_tuning(df_clean, feature_cols, n_trials=n_trials)
        apply_best_params(result["best_params"])
    except Exception as e:
        print(f"[WARNING] Hyperopt failed, using default params: {e}")

    # Train ensemble
    train, test = train_test_split_time(df_clean)
    ensemble = HybridEnsemble()
    ensemble.train(train[feature_cols].fillna(0), train["Target_Direction"])

    # P8: Log to MLflow if available
    try:
        from .mlflow_tracking import log_model_training, log_walk_forward_cv
        log_model_training(
            model_name="HybridEnsemble",
            params={"schedule": schedule, "n_trials": n_trials, "features": len(feature_cols)},
            metrics={"accuracy": current_acc, "drift_detected": float(check["drift_report"].is_drifted if check["drift_report"] else False)},
        )
        log_walk_forward_cv(wf, model_name="HybridEnsemble", feature_count=len(feature_cols))
        print("[RETRAIN] MLflow logging complete")
    except Exception:
        pass

    # Record
    scheduler.record_training(current_acc, df_clean, feature_cols)

    return {
        "retrained": True,
        "reasons": check["reasons"],
        "new_accuracy": current_acc,
        "drift_detected": check["drift_report"].is_drifted if check["drift_report"] else False,
        "performance_degraded": check["performance_report"].is_drifted if check["performance_report"] else False,
        "status": scheduler.get_status(),
    }
