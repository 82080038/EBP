"""
Hyperparameter Optimization dengan Optuna.

Mengoptimalkan hyperparameter untuk RandomForest, XGBoost, dan LightGBM
menggunakan Bayesian optimization (TPE sampler) dengan walk-forward CV
sebagai objective function.

Referensi:
- Optuna: https://optuna.org/
- Bayesian optimization for ML hyperparameters
- Walk-forward CV untuk time-series (mencegah look-ahead bias)
"""

import numpy as np
import pandas as pd
from typing import Dict, Optional
import os
import json
from .config import MODEL_CONFIG, MODELS_DIR


def _optuna_objective(trial, X, y, n_splits=3):
    """Objective function untuk Optuna — maximize walk-forward CV accuracy."""
    from sklearn.model_selection import TimeSeriesSplit
    from sklearn.ensemble import RandomForestClassifier
    from sklearn.preprocessing import MinMaxScaler

    tscv = TimeSeriesSplit(n_splits=n_splits)
    scores = []

    rf_n = trial.suggest_int("rf_n_estimators", 100, 500, step=50)
    rf_depth = trial.suggest_int("rf_max_depth", 3, 20)
    rf_min_samples = trial.suggest_int("rf_min_samples_split", 2, 20)

    xgb_n = trial.suggest_int("xgb_n_estimators", 100, 500, step=50)
    xgb_depth = trial.suggest_int("xgb_max_depth", 3, 12)
    xgb_lr = trial.suggest_float("xgb_learning_rate", 0.01, 0.3, log=True)
    xgb_subsample = trial.suggest_float("xgb_subsample", 0.6, 1.0)
    xgb_colsample = trial.suggest_float("xgb_colsample_bytree", 0.6, 1.0)

    lgbm_n = trial.suggest_int("lgbm_n_estimators", 100, 500, step=50)
    lgbm_depth = trial.suggest_int("lgbm_max_depth", 3, 12)
    lgbm_lr = trial.suggest_float("lgbm_learning_rate", 0.01, 0.3, log=True)
    lgbm_leaves = trial.suggest_int("lgbm_num_leaves", 15, 127)
    lgbm_subsample = trial.suggest_float("lgbm_subsample", 0.6, 1.0)

    for train_idx, val_idx in tscv.split(X):
        X_train, X_val = X.iloc[train_idx], X.iloc[val_idx]
        y_train, y_val = y.iloc[train_idx], y.iloc[val_idx]

        scaler = MinMaxScaler()
        X_train_s = scaler.fit_transform(X_train)
        X_val_s = scaler.transform(X_val)

        preds = []

        try:
            from xgboost import XGBClassifier
            xgb = XGBClassifier(
                n_estimators=xgb_n, max_depth=xgb_depth,
                learning_rate=xgb_lr, subsample=xgb_subsample,
                colsample_bytree=xgb_colsample,
                eval_metric="logloss", n_jobs=-1,
                random_state=MODEL_CONFIG["random_state"],
            )
            xgb.fit(X_train_s, y_train)
            preds.append(xgb.predict(X_val_s))
        except ImportError:
            pass

        try:
            from lightgbm import LGBMClassifier
            lgbm = LGBMClassifier(
                n_estimators=lgbm_n, max_depth=lgbm_depth,
                learning_rate=lgbm_lr, num_leaves=lgbm_leaves,
                subsample=lgbm_subsample, n_jobs=-1,
                random_state=MODEL_CONFIG["random_state"],
                verbose=-1,
            )
            lgbm.fit(np.asarray(X_train_s), np.asarray(y_train))
            preds.append(lgbm.predict(np.asarray(X_val_s)))
        except ImportError:
            pass

        rf = RandomForestClassifier(
            n_estimators=rf_n, max_depth=rf_depth,
            min_samples_split=rf_min_samples,
            random_state=MODEL_CONFIG["random_state"],
            n_jobs=-1,
        )
        rf.fit(X_train_s, y_train)
        preds.append(rf.predict(X_val_s))

        ensemble_preds = np.mean(preds, axis=0)
        ensemble_preds = (ensemble_preds >= 0.5).astype(int)
        acc = (ensemble_preds == y_val.values).mean()
        scores.append(acc)

    return np.mean(scores)


def run_hyperopt_tuning(
    df: pd.DataFrame,
    feature_cols: list,
    n_trials: int = 50,
    n_splits: int = 3,
) -> Dict:
    """
    Jalankan hyperparameter optimization dengan Optuna.

    Returns dict dengan best params, best score, dan full study info.
    """
    import optuna
    optuna.logging.set_verbosity(optuna.logging.WARNING)

    X = df[feature_cols].fillna(0)
    y = df["Target_Direction"] if "Target_Direction" in df.columns else (
        (df["Target_Next_Return"] > 0).astype(int)
    )

    study = optuna.create_study(
        direction="maximize",
        sampler=optuna.samplers.TPESampler(seed=MODEL_CONFIG["random_state"]),
    )
    study.optimize(
        lambda trial: _optuna_objective(trial, X, y, n_splits),
        n_trials=n_trials,
        show_progress_bar=False,
    )

    best = study.best_params
    best_score = study.best_value

    result = {
        "best_params": best,
        "best_cv_accuracy": round(best_score, 4),
        "n_trials": n_trials,
        "n_splits": n_splits,
        "improvement": round(best_score - 0.4738, 4),
    }

    path = os.path.join(MODELS_DIR, "best_params.json")
    with open(path, "w") as f:
        json.dump(result, f, indent=2)

    print(f"\n[OK] Hyperopt selesai: {n_trials} trials")
    print(f"  Best CV Accuracy: {best_score:.2%}")
    print(f"  Improvement over baseline: {result['improvement']:+.2%}")
    print(f"  Saved to: {path}")

    return result


def apply_best_params(best_params: Dict) -> None:
    """Apply best hyperparameters to MODEL_CONFIG."""
    param_map = {
        "rf_n_estimators": "rf_n_estimators",
        "rf_max_depth": "rf_max_depth",
        "rf_min_samples_split": "rf_min_samples_split",
        "xgb_n_estimators": "xgb_n_estimators",
        "xgb_max_depth": "xgb_max_depth",
        "xgb_learning_rate": "xgb_learning_rate",
        "lgbm_n_estimators": "lgbm_n_estimators",
        "lgbm_max_depth": "lgbm_max_depth",
        "lgbm_learning_rate": "lgbm_learning_rate",
    }

    for optuna_key, config_key in param_map.items():
        if optuna_key in best_params:
            MODEL_CONFIG[config_key] = best_params[optuna_key]

    if "xgb_subsample" in best_params:
        MODEL_CONFIG["xgb_subsample"] = best_params["xgb_subsample"]
    if "xgb_colsample_bytree" in best_params:
        MODEL_CONFIG["xgb_colsample_bytree"] = best_params["xgb_colsample_bytree"]
    if "lgbm_num_leaves" in best_params:
        MODEL_CONFIG["lgbm_num_leaves"] = best_params["lgbm_num_leaves"]
    if "lgbm_subsample" in best_params:
        MODEL_CONFIG["lgbm_subsample"] = best_params["lgbm_subsample"]

    print("[OK] Best params applied to MODEL_CONFIG")


def load_best_params() -> Optional[Dict]:
    """Load saved best params if available."""
    path = os.path.join(MODELS_DIR, "best_params.json")
    if os.path.exists(path):
        with open(path, "r") as f:
            return json.load(f)
    return None
