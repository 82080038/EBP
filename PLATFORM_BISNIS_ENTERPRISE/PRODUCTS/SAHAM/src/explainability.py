"""
Model Explainability via SHAP (SHapley Additive exPlanations).

Provides per-prediction feature attribution to answer "WHY did the model
predict BUY/SELL?" — making the ML ensemble transparent and trustworthy.

Features:
- TreeExplainer for LightGBM, XGBoost, RandomForest (fast, exact)
- Fallback to feature_importances_ if SHAP not installed
- Top-K feature contributions per prediction
- Global feature importance summary
- Text-based explanation generation for UI

References:
- Lundberg & Lee (2017), "A Unified Approach to Interpreting Model Predictions"
- SHAP: https://github.com/shap/shap

Usage:
    from src.explainability import explain_prediction, get_global_importance
    explanation = explain_prediction(ensemble, X_latest, feature_cols)
    top_features = explanation.top_features  # [("RSI", 0.15), ("MA5", -0.08), ...]
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple, Any

import numpy as np
import pandas as pd

logger = logging.getLogger(__name__)

SHAP_AVAILABLE = False
try:
    import shap
    SHAP_AVAILABLE = True
except ImportError:
    logger.info("SHAP not installed — using feature_importances_ fallback")


@dataclass
class PredictionExplanation:
    """Explanation for a single prediction."""
    top_features: List[Tuple[str, float]] = field(default_factory=list)
    base_value: float = 0.5
    prediction_value: float = 0.5
    feature_names: List[str] = field(default_factory=list)
    shap_values: Optional[np.ndarray] = None
    method: str = "feature_importance"
    text_summary: str = ""

    def bullish_drivers(self) -> List[Tuple[str, float]]:
        return [(n, v) for n, v in self.top_features if v > 0]

    def bearish_drivers(self) -> List[Tuple[str, float]]:
        return [(n, v) for n, v in self.top_features if v < 0]


def _get_tree_explainer(model: Any, model_name: str):
    """Create or get cached TreeExplainer for a tree-based model."""
    if not SHAP_AVAILABLE:
        return None
    try:
        if hasattr(model, 'model') and model.model is not None:
            inner = model.model
        else:
            inner = model

        model_type = type(inner).__module__
        if 'lightgbm' in model_type or 'xgboost' in model_type or 'sklearn' in model_type:
            return shap.TreeExplainer(inner)
    except Exception as e:
        logger.debug(f"TreeExplainer failed for {model_name}: {e}")
    return None


def explain_prediction(
    ensemble: Any,
    X: pd.DataFrame,
    feature_cols: List[str],
    top_k: int = 10,
) -> Dict[str, PredictionExplanation]:
    """
    Explain each model's prediction in the ensemble.

    Args:
        ensemble: HybridEnsemble instance with trained models
        X: Feature DataFrame (single row or multiple)
        feature_cols: List of feature column names
        top_k: Number of top features to return

    Returns:
        Dict mapping model_name -> PredictionExplanation
    """
    explanations: Dict[str, PredictionExplanation] = {}

    if X.empty or not feature_cols:
        return explanations

    X_input = X[feature_cols] if all(c in X.columns for c in feature_cols) else X

    for model_wrapper in getattr(ensemble, 'models', []):
        model_name = model_wrapper.name
        try:
            if SHAP_AVAILABLE:
                explainer = _get_tree_explainer(model_wrapper, model_name)
                if explainer is not None:
                    X_scaled = X_input
                    if hasattr(model_wrapper, 'scaler'):
                        X_scaled = pd.DataFrame(
                            model_wrapper.scaler.transform(X_input),
                            columns=feature_cols,
                        )

                    shap_values = explainer.shap_values(X_scaled)

                    if isinstance(shap_values, list):
                        sv = shap_values[1] if len(shap_values) > 1 else shap_values[0]
                    elif isinstance(shap_values, np.ndarray) and shap_values.ndim == 3:
                        sv = shap_values[:, :, 1] if shap_values.shape[2] > 1 else shap_values[:, :, 0]
                    else:
                        sv = shap_values

                    sv_flat = sv.flatten() if sv.ndim > 1 else sv
                    base_val = float(explainer.expected_value[1]) if hasattr(explainer, 'expected_value') and isinstance(explainer.expected_value, (list, np.ndarray)) and len(explainer.expected_value) > 1 else 0.5
                    pred_val = base_val + float(np.sum(sv_flat))

                    contributions = list(zip(feature_cols, sv_flat.astype(float)))
                    contributions.sort(key=lambda x: abs(x[1]), reverse=True)

                    top_feats = contributions[:top_k]

                    bull = [f"{n} (+{v:.4f})" for n, v in top_feats if v > 0]
                    bear = [f"{n} ({v:.4f})" for n, v in top_feats if v < 0]

                    summary_parts = []
                    if bull:
                        summary_parts.append(f"Bullish: {', '.join(bull[:3])}")
                    if bear:
                        summary_parts.append(f"Bearish: {', '.join(bear[:3])}")
                    text = " | ".join(summary_parts) if summary_parts else "No significant drivers"

                    explanations[model_name] = PredictionExplanation(
                        top_features=top_feats,
                        base_value=base_val,
                        prediction_value=pred_val,
                        feature_names=list(feature_cols),
                        shap_values=sv,
                        method="shap",
                        text_summary=text,
                    )
                    continue

            # Fallback: use feature_importances_
            inner = getattr(model_wrapper, 'model', None)
            if inner is not None and hasattr(inner, 'feature_importances_'):
                importances = inner.feature_importances_
                contributions = list(zip(feature_cols, importances.astype(float)))
                contributions.sort(key=lambda x: x[1], reverse=True)
                top_feats = contributions[:top_k]

                top_names = [n for n, _ in top_feats[:5]]
                text = f"Top features: {', '.join(top_names)}"

                explanations[model_name] = PredictionExplanation(
                    top_features=top_feats,
                    feature_names=list(feature_cols),
                    method="feature_importance",
                    text_summary=text,
                )
            else:
                explanations[model_name] = PredictionExplanation(
                    method="unavailable",
                    text_summary=f"Explanation unavailable for {model_name}",
                )

        except Exception as e:
            logger.debug(f"Explanation failed for {model_name}: {e}")
            explanations[model_name] = PredictionExplanation(
                method="error",
                text_summary=f"Error: {e}",
            )

    return explanations


def get_global_importance(
    ensemble: Any,
    feature_cols: List[str],
) -> Dict[str, Dict[str, float]]:
    """
    Get global feature importance for each model in the ensemble.

    Returns:
        Dict mapping model_name -> {feature_name: importance}
    """
    result: Dict[str, Dict[str, float]] = {}

    for model_wrapper in getattr(ensemble, 'models', []):
        model_name = model_wrapper.name
        inner = getattr(model_wrapper, 'model', None)
        if inner is not None and hasattr(inner, 'feature_importances_'):
            importances = inner.feature_importances_
            result[model_name] = dict(zip(feature_cols, importances.astype(float)))
        else:
            result[model_name] = {}

    return result


def generate_explanation_text(explanations: Dict[str, PredictionExplanation]) -> str:
    """Generate human-readable explanation combining all models."""
    if not explanations:
        return "No explanations available."

    lines = []
    for model_name, exp in explanations.items():
        if exp.method == "shap":
            lines.append(f"[{model_name} — SHAP]")
            bull = exp.bullish_drivers()[:3]
            bear = exp.bearish_drivers()[:3]
            if bull:
                lines.append(f"  ↑ {', '.join(f'{n}' for n, v in bull)}")
            if bear:
                lines.append(f"  ↓ {', '.join(f'{n}' for n, v in bear)}")
        elif exp.method == "feature_importance":
            lines.append(f"[{model_name} — Feature Importance]")
            top = exp.top_features[:5]
            lines.append(f"  Top: {', '.join(f'{n}' for n, v in top)}")
        else:
            lines.append(f"[{model_name}] {exp.text_summary}")

    return "\n".join(lines)
