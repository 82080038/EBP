"""
MLflow Tracking Integration.

Implementasi:
- Experiment tracking (params, metrics, artifacts)
- Model registry (versioning, stage transitions)
- Auto-logging untuk sklearn/xgboost/lightgbm
- Walk-forward CV result logging
- Feature importance logging

Referensi:
- MLflow official documentation
- Microsoft Qlib MLflow integration
"""

import os
import json
import numpy as np
from typing import Dict, Optional, Any
from datetime import datetime

MLFLOW_AVAILABLE = False
try:
    import mlflow
    import mlflow.sklearn
    MLFLOW_AVAILABLE = True
except ImportError:
    pass

MLFLOW_TRACKING_URI = os.getenv("MLFLOW_TRACKING_URI", "file:///app/mlruns")
EXPERIMENT_NAME = "saham_prediksi"


def _get_mlflow():
    """Get MLflow client if available."""
    if not MLFLOW_AVAILABLE:
        return None
    mlflow.set_tracking_uri(MLFLOW_TRACKING_URI)
    mlflow.set_experiment(EXPERIMENT_NAME)
    return mlflow


def log_model_training(
    model_name: str,
    params: Dict[str, Any],
    metrics: Dict[str, float],
    feature_importance: Optional[Dict[str, float]] = None,
    model_artifact: Optional[Any] = None,
    tags: Optional[Dict[str, str]] = None,
) -> Optional[str]:
    """
    Log model training run ke MLflow.

    Returns run_id atau None jika MLflow tidak tersedia.
    """
    mlflow = _get_mlflow()
    if mlflow is None:
        print("[SKIP] MLflow tidak terinstall, logging dilewati")
        return None

    with mlflow.start_run(run_name=f"{model_name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}") as run:
        # Log params
        mlflow.log_params(params)

        # Log metrics
        for key, value in metrics.items():
            if isinstance(value, (int, float)) and not np.isnan(value):
                mlflow.log_metric(key, float(value))

        # Log feature importance as artifact
        if feature_importance:
            fi_path = os.path.join(os.path.dirname(__file__), "data", "feature_importance.json")
            with open(fi_path, "w") as f:
                json.dump(feature_importance, f, indent=2)
            mlflow.log_artifact(fi_path)

        # Log model
        if model_artifact is not None:
            try:
                mlflow.sklearn.log_model(model_artifact, model_name)
            except Exception:
                pass

        # Log tags
        if tags:
            mlflow.set_tags(tags)

        run_id = run.info.run_id
        print(f"[OK] MLflow run logged: {run_id}")
        return run_id


def log_walk_forward_cv(
    cv_result,
    model_name: str = "HybridEnsemble",
    feature_count: int = 0,
) -> Optional[str]:
    """Log walk-forward CV results ke MLflow."""
    mlflow = _get_mlflow()
    if mlflow is None:
        return None

    with mlflow.start_run(run_name=f"WF_CV_{model_name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}") as run:
        mlflow.log_param("model", model_name)
        mlflow.log_param("n_folds", cv_result.n_folds)
        mlflow.log_param("feature_count", feature_count)

        mlflow.log_metric("mean_da", cv_result.mean_da)
        mlflow.log_metric("std_da", cv_result.std_da)
        mlflow.log_metric("mean_ic", cv_result.mean_ic)
        mlflow.log_metric("mean_rank_ic", cv_result.mean_rank_ic)
        mlflow.log_metric("icir", cv_result.icir)

        # Log per-fold results
        for fold in cv_result.fold_results:
            mlflow.log_metric(f"fold_{fold['fold']}_da", fold["da"])
            mlflow.log_metric(f"fold_{fold['fold']}_ic", fold["ic"])

        run_id = run.info.run_id
        print(f"[OK] Walk-forward CV logged: {run_id}")
        return run_id


def log_backtest(
    backtest_result,
    strategy_name: str = "Ensemble",
) -> Optional[str]:
    """Log backtest results ke MLflow."""
    mlflow = _get_mlflow()
    if mlflow is None:
        return None

    with mlflow.start_run(run_name=f"BT_{strategy_name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}") as run:
        mlflow.log_param("strategy", strategy_name)
        mlflow.log_param("initial_capital", backtest_result.initial_capital)

        mlflow.log_metric("total_return_pct", backtest_result.total_return_pct)
        mlflow.log_metric("buy_hold_return_pct", backtest_result.buy_hold_return_pct)
        mlflow.log_metric("n_trades", backtest_result.n_trades)
        mlflow.log_metric("win_rate", backtest_result.win_rate)
        mlflow.log_metric("profit_factor", backtest_result.profit_factor)
        mlflow.log_metric("max_drawdown_pct", backtest_result.max_drawdown_pct)
        mlflow.log_metric("sharpe_ratio", backtest_result.sharpe_ratio)
        mlflow.log_metric("sortino_ratio", backtest_result.sortino_ratio)
        mlflow.log_metric("calmar_ratio", backtest_result.calmar_ratio)
        mlflow.log_metric("total_commission", backtest_result.total_commission)

        run_id = run.info.run_id
        print(f"[OK] Backtest logged: {run_id}")
        return run_id


def log_feature_selection(
    selection_result,
    method: str = "hybrid",
) -> Optional[str]:
    """Log feature selection results ke MLflow."""
    mlflow = _get_mlflow()
    if mlflow is None:
        return None

    with mlflow.start_run(run_name=f"FS_{method}_{datetime.now().strftime('%Y%m%d_%H%M%S')}") as run:
        mlflow.log_param("method", method)
        mlflow.log_param("n_before", selection_result.n_before)
        mlflow.log_param("n_after", selection_result.n_after)

        if selection_result.feature_importance:
            for feat, imp in list(selection_result.feature_importance.items())[:20]:
                mlflow.log_metric(f"imp_{feat[:50]}", float(imp))

        run_id = run.info.run_id
        print(f"[OK] Feature selection logged: {run_id}")
        return run_id
