"""
Feature Selection: SHAP-based, Boruta, Correlation filter, Variance threshold.

Implementasi:
- SHAP values untuk feature importance (dari ML Engineer roadmap)
- Boruta all-relevant feature selection
- Recursive Feature Elimination (RFE)
- Correlation filter (> 0.95)
- Variance threshold (near-zero variance)
- Target: reduce 135 → 40-60 high-signal features

Referensi:
- SHAP: Lundberg & Lee (2017)
- Boruta: Kursa & Rudnicki (2010)
- Microsoft Qlib: Alpha158 feature benchmark
"""

import numpy as np
import pandas as pd
from typing import List, Dict, Tuple
from dataclasses import dataclass, field


@dataclass
class FeatureSelectionResult:
    selected_features: List[str] = field(default_factory=list)
    removed_features: List[str] = field(default_factory=list)
    feature_importance: Dict[str, float] = field(default_factory=dict)
    method: str = ""
    n_before: int = 0
    n_after: int = 0

    def summary(self) -> str:
        return (
            f"Feature Selection ({self.method}): "
            f"{self.n_before} → {self.n_after} features "
            f"({self.n_before - self.n_after} removed)"
        )


def correlation_filter(df: pd.DataFrame, feature_cols: List[str], threshold: float = 0.95) -> Tuple[List[str], List[str]]:
    """
    Hapus fitur dengan korelasi > threshold.
    Keep feature dengan variance lebih tinggi, drop yang lebih rendah.
    """
    if not feature_cols:
        return [], []

    corr_matrix = df[feature_cols].corr().abs()
    upper = corr_matrix.where(np.triu(np.ones(corr_matrix.shape), k=1).astype(bool))

    to_drop = set()
    for col in upper.columns:
        high_corr = upper[col][upper[col] > threshold].index.tolist()
        for hc in high_corr:
            var_col = df[col].var()
            var_hc = df[hc].var()
            if var_col >= var_hc:
                to_drop.add(hc)
            else:
                to_drop.add(col)

    kept = [c for c in feature_cols if c not in to_drop]
    dropped = list(to_drop)
    return kept, dropped


def variance_threshold_filter(df: pd.DataFrame, feature_cols: List[str], min_variance: float = 0.0001) -> Tuple[List[str], List[str]]:
    """
    Hapus fitur dengan variance hampir nol (tidak informatif).
    """
    kept = []
    dropped = []
    for col in feature_cols:
        if col in df.columns:
            var = df[col].var()
            if var < min_variance:
                dropped.append(col)
            else:
                kept.append(col)
        else:
            dropped.append(col)
    return kept, dropped


def shap_feature_selection(
    X_train: pd.DataFrame,
    y_train: pd.Series,
    X_test: pd.DataFrame,
    y_test: pd.Series,
    n_features: int = 50,
    model=None,
) -> FeatureSelectionResult:
    """
    SHAP-based feature selection.

    Uses TreeExplainer untuk model tree-based (RF, XGBoost).
    Falls back to feature_importances_ jika SHAP tidak tersedia.
    """
    result = FeatureSelectionResult(method="SHAP", n_before=X_train.shape[1])

    if model is None:
        from sklearn.ensemble import RandomForestClassifier
        model = RandomForestClassifier(
            n_estimators=100, max_depth=8, random_state=42, n_jobs=-1
        )

    model.fit(X_train, y_train)

    # Try SHAP
    try:
        import shap
        explainer = shap.TreeExplainer(model)
        shap_values = explainer.shap_values(X_test)
        if isinstance(shap_values, list):
            shap_importance = np.abs(shap_values[1]).mean(axis=0)
        else:
            shap_importance = np.abs(shap_values).mean(axis=0)

        # Ensure 1D scalar per feature (handle multi-class shape n_features x n_classes)
        if shap_importance.ndim > 1:
            shap_importance = shap_importance.mean(axis=-1)
        shap_importance = np.asarray(shap_importance).ravel()

        importance_dict = dict(zip(X_train.columns, shap_importance))
    except ImportError:
        # Fallback to feature_importances_
        importance_dict = dict(zip(X_train.columns, model.feature_importances_))
        result.method = "FeatureImportance (SHAP not available)"

    # Sort by importance
    sorted_features = sorted(importance_dict.items(), key=lambda x: x[1], reverse=True)
    result.feature_importance = {k: float(v) for k, v in sorted_features}

    # Select top n_features
    result.selected_features = [f[0] for f in sorted_features[:n_features]]
    result.removed_features = [f[0] for f in sorted_features[n_features:]]
    result.n_after = len(result.selected_features)

    return result


def boruta_feature_selection(
    X_train: pd.DataFrame,
    y_train: pd.Series,
    max_features: int = 60,
) -> FeatureSelectionResult:
    """
    Boruta all-relevant feature selection.

    Boruta menemukan SEMUA fitur yang relevan (bukan hanya yang paling penting).
    """
    result = FeatureSelectionResult(method="Boruta", n_before=X_train.shape[1])

    try:
        from boruta import BorutaPy
        from sklearn.ensemble import RandomForestClassifier

        rf = RandomForestClassifier(n_estimators=100, max_depth=8, random_state=42, n_jobs=-1)
        boruta = BorutaPy(rf, n_estimators='auto', max_iter=50, random_state=42)
        boruta.fit(X_train.values, y_train.values)

        selected = X_train.columns[boruta.support_].tolist()
        tentative = X_train.columns[boruta.support_weak_].tolist()

        # Combine confirmed + tentative, limit to max_features
        all_selected = selected + tentative
        if len(all_selected) > max_features:
            all_selected = all_selected[:max_features]

        result.selected_features = all_selected
        result.removed_features = [c for c in X_train.columns if c not in all_selected]
        result.n_after = len(result.selected_features)

    except ImportError:
        # Fallback: use correlation filter + variance threshold + top importance
        result.method = "Hybrid (Boruta not available)"
        kept, dropped = variance_threshold_filter(X_train, list(X_train.columns))
        kept, dropped2 = correlation_filter(X_train, kept)

        from sklearn.ensemble import RandomForestClassifier
        rf = RandomForestClassifier(n_estimators=100, max_depth=8, random_state=42, n_jobs=-1)
        rf.fit(X_train[kept], y_train)
        importance = dict(zip(kept, rf.feature_importances_))
        sorted_feats = sorted(importance.items(), key=lambda x: x[1], reverse=True)

        result.selected_features = [f[0] for f in sorted_feats[:max_features]]
        result.removed_features = [c for c in X_train.columns if c not in result.selected_features]
        result.feature_importance = {k: float(v) for k, v in sorted_feats}
        result.n_after = len(result.selected_features)

    return result


def select_features(
    df: pd.DataFrame,
    feature_cols: List[str],
    target_col: str = "Target_Direction",
    target_n: int = 50,
    method: str = "hybrid",
) -> FeatureSelectionResult:
    """
    Feature selection pipeline lengkap.

    Methods:
    - "shap": SHAP-based selection
    - "boruta": Boruta all-relevant
    - "hybrid": Variance threshold → Correlation filter → SHAP/Boruta (recommended)

    Target: reduce 135 → 40-60 high-signal features
    """
    from .preprocessor import train_test_split_time

    df_clean = df.dropna(subset=feature_cols + [target_col]).copy()
    if target_col not in df_clean.columns:
        df_clean[target_col] = (df_clean["Target_Next_Return"] > 0).astype(int)

    if len(df_clean) < 100:
        return FeatureSelectionResult(
            method=method, n_before=len(feature_cols),
            selected_features=feature_cols, n_after=len(feature_cols)
        )

    train, test = train_test_split_time(df_clean)
    X_train = train[feature_cols]
    y_train = train[target_col]
    X_test = test[feature_cols]
    y_test = test[target_col]

    if method == "hybrid":
        # Step 1: Variance threshold
        kept, dropped = variance_threshold_filter(df_clean, feature_cols)
        print(f"  [Variance] {len(feature_cols)} → {len(kept)} ({len(dropped)} removed)")

        # Step 2: Correlation filter
        kept, dropped_corr = correlation_filter(df_clean, kept)
        print(f"  [Correlation] {len(kept) + len(dropped_corr)} → {len(kept)} ({len(dropped_corr)} removed)")

        # Step 3: SHAP selection
        result = shap_feature_selection(
            X_train[kept], y_train, X_test[kept], y_test, n_features=target_n
        )
        result.method = "Hybrid (Variance + Correlation + SHAP)"
        print(f"  [SHAP] {len(kept)} → {result.n_after} ({len(kept) - result.n_after} removed)")
        print(f"  {result.summary()}")

    elif method == "boruta":
        result = boruta_feature_selection(X_train, y_train, max_features=target_n)
        print(f"  {result.summary()}")

    elif method == "shap":
        result = shap_feature_selection(X_train, y_train, X_test, y_test, n_features=target_n)
        print(f"  {result.summary()}")

    else:
        raise ValueError(f"Unknown method: {method}. Use 'shap', 'boruta', or 'hybrid'")

    return result
