"""
Model Drift Monitoring — PSI & KS-test based detection.

Monitors feature distribution shifts and prediction distribution changes
to detect when a model may be degrading and needs retraining.

Metrics:
- PSI (Population Stability Index): measures distribution shift between
  reference (training) and current (production) feature distributions.
  PSI < 0.1: stable, 0.1-0.25: moderate drift, >0.25: significant drift
- KS-test (Kolmogorov-Smirnov): statistical test for distribution equality
- Prediction drift: monitors if prediction confidence distribution shifts

References:
- Yiu (2019), "Population Stability Index (PSI)"
- López de Prado (2018), "Advances in Financial Machine Learning"

Usage:
    from src.drift_monitor import DriftMonitor
    monitor = DriftMonitor()
    monitor.set_reference(df_train[feature_cols])
    report = monitor.check(df_current[feature_cols])
    if report.has_significant_drift:
        # trigger retrain
"""
from __future__ import annotations

import logging
import json
import os
from dataclasses import dataclass, field, asdict
from datetime import datetime
from typing import Dict, List, Optional

import numpy as np
import pandas as pd

logger = logging.getLogger(__name__)

try:
    from scipy import stats
    HAS_SCIPY = True
except ImportError:
    HAS_SCIPY = False
    logger.info("scipy not available — KS-test disabled, PSI only")


@dataclass
class FeatureDrift:
    """Drift metrics for a single feature."""
    feature: str = ""
    psi: float = 0.0
    ks_statistic: float = 0.0
    ks_pvalue: float = 1.0
    drift_level: str = "stable"  # stable, moderate, significant
    reference_mean: float = 0.0
    current_mean: float = 0.0
    mean_shift_pct: float = 0.0


@dataclass
class DriftReport:
    """Combined drift monitoring report."""
    timestamp: str = ""
    features: List[FeatureDrift] = field(default_factory=list)
    has_significant_drift: bool = False
    has_moderate_drift: bool = False
    significant_features: List[str] = field(default_factory=list)
    moderate_features: List[str] = field(default_factory=list)
    overall_psi: float = 0.0
    recommendation: str = ""

    def summary(self) -> str:
        status = "OK" if not self.has_significant_drift else "DRIFT DETECTED"
        lines = [
            f"Drift Report [{status}] — {self.timestamp}",
            f"  Overall PSI: {self.overall_psi:.4f}",
            f"  Significant: {len(self.significant_features)} features",
            f"  Moderate: {len(self.moderate_features)} features",
        ]
        if self.significant_features:
            lines.append(f"  Drifted: {', '.join(self.significant_features[:5])}")
        if self.recommendation:
            lines.append(f"  Action: {self.recommendation}")
        return "\n".join(lines)


def _calculate_psi(reference: np.ndarray, current: np.ndarray, n_bins: int = 10) -> float:
    """Calculate Population Stability Index between two distributions."""
    ref = reference[~np.isnan(reference)]
    cur = current[~np.isnan(current)]

    if len(ref) == 0 or len(cur) == 0:
        return 0.0

    edges = np.percentile(ref, np.linspace(0, 100, n_bins + 1))
    edges = np.unique(edges)

    if len(edges) < 3:
        return 0.0

    ref_counts, _ = np.histogram(ref, bins=edges)
    cur_counts, _ = np.histogram(cur, bins=edges)

    ref_pct = ref_counts / len(ref)
    cur_pct = cur_counts / len(cur)

    ref_pct = np.where(ref_pct == 0, 0.0001, ref_pct)
    cur_pct = np.where(cur_pct == 0, 0.0001, cur_pct)

    psi = np.sum((cur_pct - ref_pct) * np.log(cur_pct / ref_pct))
    return float(psi)


def _classify_drift(psi: float) -> str:
    if psi < 0.1:
        return "stable"
    elif psi < 0.25:
        return "moderate"
    else:
        return "significant"


class DriftMonitor:
    """
    Monitor model drift using PSI and KS-test.

    Workflow:
    1. Call set_reference() with training data
    2. Call check() with current production data
    3. Review report and trigger retrain if needed
    """

    def __init__(self, psi_threshold: float = 0.25, moderate_threshold: float = 0.1):
        self.reference_data: Optional[pd.DataFrame] = None
        self.reference_predictions: Optional[np.ndarray] = None
        self.psi_threshold = psi_threshold
        self.moderate_threshold = moderate_threshold
        self.history: List[Dict] = []

    def set_reference(self, df: pd.DataFrame, predictions: Optional[np.ndarray] = None):
        """Set reference (training) data for comparison."""
        self.reference_data = df.copy()
        if predictions is not None:
            self.reference_predictions = np.array(predictions)

    def check(
        self,
        df_current: pd.DataFrame,
        predictions: Optional[np.ndarray] = None,
        feature_cols: Optional[List[str]] = None,
    ) -> DriftReport:
        """
        Check for drift between reference and current data.

        Args:
            df_current: Current production feature DataFrame
            predictions: Current prediction probabilities (optional)
            feature_cols: Features to check (default: all common columns)

        Returns:
            DriftReport with per-feature drift metrics
        """
        report = DriftReport(timestamp=datetime.now().isoformat())

        if self.reference_data is None or self.reference_data.empty:
            report.recommendation = "No reference data set — cannot check drift"
            return report

        if feature_cols is None:
            feature_cols = [
                c for c in df_current.columns
                if c in self.reference_data.columns and df_current[c].dtype in (np.float64, np.float32, np.int64, np.int32)
            ]

        psi_values = []

        for feat in feature_cols:
            if feat not in self.reference_data.columns or feat not in df_current.columns:
                continue

            ref_vals = self.reference_data[feat].values.astype(float)
            cur_vals = df_current[feat].values.astype(float)

            psi = _calculate_psi(ref_vals, cur_vals)
            drift_level = _classify_drift(psi)
            psi_values.append(psi)

            ks_stat = 0.0
            ks_pval = 1.0
            if HAS_SCIPY:
                try:
                    ks_result = stats.ks_2samp(ref_vals, cur_vals)
                    ks_stat = float(ks_result.statistic)
                    ks_pval = float(ks_result.pvalue)
                except Exception:
                    pass

            ref_mean = float(np.nanmean(ref_vals))
            cur_mean = float(np.nanmean(cur_vals))
            mean_shift = ((cur_mean - ref_mean) / ref_mean * 100) if ref_mean != 0 else 0.0

            fd = FeatureDrift(
                feature=feat,
                psi=round(psi, 4),
                ks_statistic=round(ks_stat, 4),
                ks_pvalue=round(ks_pval, 6),
                drift_level=drift_level,
                reference_mean=round(ref_mean, 4),
                current_mean=round(cur_mean, 4),
                mean_shift_pct=round(mean_shift, 2),
            )

            report.features.append(fd)

            if drift_level == "significant":
                report.significant_features.append(feat)
            elif drift_level == "moderate":
                report.moderate_features.append(feat)

        report.overall_psi = round(float(np.mean(psi_values)) if psi_values else 0.0, 4)
        report.has_significant_drift = len(report.significant_features) > 0
        report.has_moderate_drift = len(report.moderate_features) > 0

        if report.has_significant_drift:
            report.recommendation = (
                f"RETRAIN: {len(report.significant_features)} features show significant drift "
                f"(PSI > {self.psi_threshold}). Trigger model retraining."
            )
        elif report.has_moderate_drift:
            report.recommendation = (
                f"MONITOR: {len(report.moderate_features)} features show moderate drift. "
                f"Schedule retraining soon."
            )
        else:
            report.recommendation = "STABLE: No significant drift detected."

        # Check prediction drift
        if predictions is not None and self.reference_predictions is not None:
            pred_psi = _calculate_psi(self.reference_predictions, np.array(predictions))
            if pred_psi > self.psi_threshold:
                report.has_significant_drift = True
                report.significant_features.append("__prediction_distribution__")
                report.recommendation = (
                    f"RETRAIN: Prediction distribution drift detected (PSI={pred_psi:.4f}). "
                    f"Model output has shifted significantly."
                )

        self.history.append({
            "timestamp": report.timestamp,
            "overall_psi": report.overall_psi,
            "significant_count": len(report.significant_features),
            "moderate_count": len(report.moderate_features),
            "recommendation": report.recommendation,
        })

        return report

    def save_report(self, report: DriftReport, path: Optional[str] = None) -> str:
        """Save drift report to JSON file."""
        if path is None:
            from .config import DATA_DIR
            path = os.path.join(DATA_DIR, "drift_report.json")

        data = {
            "timestamp": report.timestamp,
            "overall_psi": report.overall_psi,
            "has_significant_drift": report.has_significant_drift,
            "has_moderate_drift": report.has_moderate_drift,
            "significant_features": report.significant_features,
            "moderate_features": report.moderate_features,
            "recommendation": report.recommendation,
            "features": [asdict(f) for f in report.features],
        }

        with open(path, "w") as f:
            json.dump(data, f, indent=2, default=str)

        logger.info(f"Drift report saved to {path}")
        return path
