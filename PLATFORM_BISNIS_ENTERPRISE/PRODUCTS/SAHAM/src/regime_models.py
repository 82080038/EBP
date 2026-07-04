"""
Regime-aware model architecture.

Trains separate models for different market regimes (Bull, Bear, Sideways)
and dynamically selects the appropriate model based on current regime detection.
This improves prediction accuracy by recognizing that market dynamics differ
fundamentally across regimes.

Reference:
- Microsoft Qlib: Concept drift modeling
- "Regime-Switching Models" by Hamilton (1989)
"""
from __future__ import annotations

import logging
import pickle
from dataclasses import dataclass, field
from typing import Dict, List, Tuple

import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class RegimeModelResult:
    """Result from regime-aware model prediction."""
    prediction: float = 0.0
    confidence: float = 0.0
    current_regime: str = ""
    regime_probability: Dict[str, float] = field(default_factory=dict)
    model_used: str = ""
    regime_specific_metrics: Dict[str, Dict] = field(default_factory=dict)
    trained: bool = False
    error: str = ""


# =============================================================================
# REGIME DETECTION
# =============================================================================


def detect_market_regime(
    df: pd.DataFrame,
    lookback: int = 60,
    trend_threshold: float = 0.001,
    vol_threshold: float = 0.02,
) -> pd.Series:
    """
    Detect market regime for each row: 'bull', 'bear', or 'sideways'.

    Uses moving average slope + volatility + momentum.

    Args:
        df: DataFrame with at least 'Close' column
        lookback: Lookback period for regime detection
        trend_threshold: Minimum slope for trend classification
        vol_threshold: Volatility threshold for sideways classification

    Returns:
        Series of regime labels
    """
    close = df["Close"]
    ma = close.rolling(lookback).mean()
    ma_slope = ma.pct_change(20)
    returns = close.pct_change()
    vol = returns.rolling(lookback).std()

    rsi = _compute_rsi(close, 14)
    momentum = close.pct_change(20)

    regimes = pd.Series("sideways", index=df.index)

    bull_mask = (ma_slope > trend_threshold) & (momentum > 0) & (rsi > 50)
    bear_mask = (ma_slope < -trend_threshold) & (momentum < 0) & (rsi < 50)
    high_vol_mask = vol > vol_threshold * 2

    regimes[bull_mask] = "bull"
    regimes[bear_mask] = "bear"
    # High vol sideways = uncertain/transitional
    regimes[high_vol_mask & (regimes == "sideways")] = "sideways"

    # Override: if both bull and bear conditions (rare), use momentum direction
    conflict = bull_mask & bear_mask
    regimes[conflict] = np.where(momentum[conflict] > 0, "bull", "bear")

    return regimes


def detect_regime_probabilities(
    df: pd.DataFrame,
    lookback: int = 60,
) -> pd.DataFrame:
    """
    Detect soft regime probabilities (0-1 for each regime).

    Returns:
        DataFrame with columns 'bull_prob', 'bear_prob', 'sideways_prob'
    """
    close = df["Close"]
    ma = close.rolling(lookback).mean()
    ma_slope = ma.pct_change(20)
    returns = close.pct_change()
    returns.rolling(lookback).std()
    rsi = _compute_rsi(close, 14)
    momentum = close.pct_change(20)

    # Normalize signals to 0-1
    bull_signal = (
        0.4 * _sigmoid(ma_slope * 500) +
        0.3 * _sigmoid((rsi - 50) / 10) +
        0.3 * _sigmoid(momentum * 50)
    )
    bear_signal = (
        0.4 * _sigmoid(-ma_slope * 500) +
        0.3 * _sigmoid((50 - rsi) / 10) +
        0.3 * _sigmoid(-momentum * 50)
    )

    sideways_signal = 1 - bull_signal - bear_signal
    sideways_signal = sideways_signal.clip(0, 1)

    # Normalize so they sum to 1
    total = bull_signal + bear_signal + sideways_signal + 1e-10

    return pd.DataFrame({
        "bull_prob": bull_signal / total,
        "bear_prob": bear_signal / total,
        "sideways_prob": sideways_signal / total,
    }, index=df.index)


def _compute_rsi(close: pd.Series, period: int = 14) -> pd.Series:
    """Compute RSI."""
    delta = close.diff()
    gain = delta.clip(lower=0).rolling(period).mean()
    loss = (-delta.clip(upper=0)).rolling(period).mean()
    rs = gain / (loss + 1e-10)
    return 100 - (100 / (1 + rs))


def _sigmoid(x):
    return 1 / (1 + np.exp(-np.clip(x, -500, 500)))


# =============================================================================
# REGIME-AWARE MODEL
# =============================================================================


class RegimeAwareModel:
    """
    Trains separate models per market regime and dynamically selects
    the appropriate model based on current regime.
    """

    def __init__(
        self,
        model_fn=None,
        regimes: List[str] = None,
        lookback: int = 60,
        **model_kwargs,
    ):
        self.model_fn = model_fn or RandomForestRegressor
        self.regimes = regimes or ["bull", "bear", "sideways"]
        self.lookback = lookback
        self.model_kwargs = model_kwargs
        self.models: Dict[str, any] = {}
        self.scalers: Dict[str, StandardScaler] = {}
        self.regime_classifier = None
        self.regime_scaler = StandardScaler()
        self.feature_cols: List[str] = []
        self.trained = False

    def _prepare_features(self, df: pd.DataFrame) -> pd.DataFrame:
        """Create feature set from DataFrame."""
        features = pd.DataFrame(index=df.index)

        if "Close" in df.columns:
            close = df["Close"]
            # Price-based features
            for lag in [1, 2, 3, 5, 10, 20]:
                features[f"close_lag_{lag}"] = close.shift(lag)
            for window in [5, 10, 20, 50]:
                features[f"ma_{window}"] = close.rolling(window).mean()
                features[f"std_{window}"] = close.rolling(window).std()
                features[f"return_{window}d"] = close.pct_change(window)
            features["rsi"] = _compute_rsi(close, 14)
            # MACD
            ema12 = close.ewm(span=12).mean()
            ema26 = close.ewm(span=26).mean()
            features["macd"] = ema12 - ema26
            features["macd_signal"] = features["macd"].ewm(span=9).mean()

        if "Volume" in df.columns:
            vol = df["Volume"]
            features["vol_ma_5"] = vol.rolling(5).mean()
            features["vol_ma_20"] = vol.rolling(20).mean()
            features["vol_ratio"] = vol / (features["vol_ma_5"] + 1e-10)

        if "High" in df.columns and "Low" in df.columns:
            features["high_low_spread"] = (df["High"] - df["Low"]) / (df["Close"] + 1e-10)
            features["atr"] = (df["High"] - df["Low"]).rolling(14).mean()

        return features

    def fit(self, df: pd.DataFrame, target_col: str = "Close") -> Dict:
        """
        Train regime-aware models.

        Args:
            df: DataFrame with OHLCV data
            target_col: Target column for prediction

        Returns:
            Dict with training metrics per regime
        """
        features = self._prepare_features(df)
        regimes = detect_market_regime(df, lookback=self.lookback)

        # Target: forward return
        y = df[target_col].pct_change(1).shift(-1)

        # Align
        data = pd.concat([features, regimes.rename("regime"), y.rename("target")], axis=1).dropna()
        if len(data) < 100:
            return {"error": "Insufficient data for regime-aware training"}

        self.feature_cols = [c for c in features.columns if c in data.columns]

        metrics = {}

        # Train regime classifier
        regime_data = data.drop(columns=["target"])
        X_regime = regime_data[self.feature_cols].values
        y_regime = regime_data["regime"].values

        self.regime_scaler.fit(X_regime)
        X_regime_scaled = self.regime_scaler.transform(X_regime)

        self.regime_classifier = LogisticRegression(max_iter=1000, random_state=42)
        self.regime_classifier.fit(X_regime_scaled, y_regime)

        regime_acc = float(np.mean(self.regime_classifier.predict(X_regime_scaled) == y_regime))
        metrics["regime_classifier_accuracy"] = regime_acc

        # Train per-regime models
        for regime in self.regimes:
            regime_mask = data["regime"] == regime
            regime_data_subset = data[regime_mask]

            if len(regime_data_subset) < 20:
                logger.warning(f"Regime '{regime}': only {len(regime_data_subset)} samples, skipping")
                continue

            X = regime_data_subset[self.feature_cols].values
            y_reg = regime_data_subset["target"].values

            # Binary classification: up (1) vs down (0)
            y_cls = (y_reg > 0).astype(int)

            scaler = StandardScaler()
            X_scaled = scaler.fit_transform(X)

            model = self.model_fn(**self.model_kwargs)
            model.fit(X_scaled, y_cls)

            self.models[regime] = model
            self.scalers[regime] = scaler

            # Metrics
            pred = model.predict(X_scaled)
            accuracy = float(np.mean(pred == y_cls))
            metrics[regime] = {
                "n_samples": len(regime_data_subset),
                "accuracy": accuracy,
                "pct_of_data": float(regime_mask.mean()),
            }

        self.trained = len(self.models) > 0
        metrics["n_regimes_trained"] = len(self.models)
        return metrics

    def predict(self, df: pd.DataFrame) -> RegimeModelResult:
        """
        Predict using the appropriate regime model.

        Returns:
            RegimeModelResult with prediction and regime info
        """
        if not self.trained:
            return RegimeModelResult(error="Model not trained")

        features = self._prepare_features(df)
        if features.empty or len(features) < self.lookback:
            return RegimeModelResult(error="Insufficient features")

        # Get current regime
        latest_features = features.iloc[-1:][self.feature_cols].values
        if np.any(np.isnan(latest_features)):
            # Fill NaN with 0
            latest_features = np.nan_to_num(latest_features)

        latest_scaled = self.regime_scaler.transform(latest_features)
        regime_probs = self.regime_classifier.predict_proba(latest_scaled)[0]
        regime_classes = self.regime_classifier.classes_

        regime_prob_dict = {cls: float(prob) for cls, prob in zip(regime_classes, regime_probs)}
        current_regime = regime_classes[np.argmax(regime_probs)]

        # Use the model for the detected regime
        if current_regime not in self.models:
            # Fallback to any available model
            current_regime = list(self.models.keys())[0]

        model = self.models[current_regime]
        scaler = self.scalers[current_regime]
        X_scaled = scaler.transform(latest_features)

        prediction_proba = model.predict_proba(X_scaled)[0]
        pred_class = model.predict(X_scaled)[0]

        # Convert to signal: 1 = up, 0 = down
        confidence = float(max(prediction_proba))

        result = RegimeModelResult(
            prediction=float(pred_class),
            confidence=confidence,
            current_regime=current_regime,
            regime_probability=regime_prob_dict,
            model_used=current_regime,
            trained=True,
        )

        return result

    def save(self, path: str):
        """Save model to disk."""
        with open(path, "wb") as f:
            pickle.dump({
                "models": self.models,
                "scalers": self.scalers,
                "regime_classifier": self.regime_classifier,
                "regime_scaler": self.regime_scaler,
                "feature_cols": self.feature_cols,
                "regimes": self.regimes,
                "lookback": self.lookback,
                "trained": self.trained,
            }, f)

    def load(self, path: str):
        """Load model from disk."""
        with open(path, "rb") as f:
            data = pickle.load(f)
            self.models = data["models"]
            self.scalers = data["scalers"]
            self.regime_classifier = data["regime_classifier"]
            self.regime_scaler = data["regime_scaler"]
            self.feature_cols = data["feature_cols"]
            self.regimes = data["regimes"]
            self.lookback = data["lookback"]
            self.trained = data["trained"]


# =============================================================================
# CONVENIENCE FUNCTIONS
# =============================================================================


def run_regime_aware_prediction(df: pd.DataFrame) -> Dict:
    """
    Run regime-aware model prediction on a DataFrame.

    Returns:
        Dict with regime info, prediction, and confidence
    """
    model = RegimeAwareModel(
        model_fn=RandomForestClassifier,
        n_estimators=50,
        max_depth=8,
        random_state=42,
    )

    metrics = model.fit(df)

    if not model.trained:
        return {"error": metrics.get("error", "Training failed"), "trained": False}

    result = model.predict(df)

    return {
        "trained": True,
        "prediction": result.prediction,
        "signal": "BUY" if result.prediction == 1 else "SELL",
        "confidence": result.confidence,
        "current_regime": result.current_regime,
        "regime_probability": result.regime_probability,
        "model_used": result.model_used,
        "training_metrics": metrics,
    }


def get_regime_confidence_adjustment(regime_result: Dict) -> Tuple[float, str]:
    """Get confidence adjustment based on regime-aware prediction."""
    if not regime_result.get("trained"):
        return 0.0, "Regime model not trained"

    confidence = regime_result.get("confidence", 0)
    regime = regime_result.get("current_regime", "")

    if confidence > 0.7:
        return 0.08, f"Regime model: high confidence in {regime} regime"
    elif confidence > 0.55:
        return 0.04, f"Regime model: moderate confidence in {regime} regime"
    else:
        return 0.0, f"Regime model: low confidence in {regime} regime"
