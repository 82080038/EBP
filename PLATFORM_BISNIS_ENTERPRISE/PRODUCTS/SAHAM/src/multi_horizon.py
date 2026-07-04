"""
Multi-horizon prediction system.

Generates predictions for multiple time horizons (3d, 1w, 1m, 3m)
using a combination of models optimized for each horizon.
Short-term: technical/momentum features, Long-term: fundamental/macro features.
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple

import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.linear_model import Ridge
from sklearn.preprocessing import StandardScaler

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class HorizonPrediction:
    """Prediction for a single horizon."""
    horizon: str = ""
    days: int = 0
    predicted_return: float = 0.0
    predicted_price: float = 0.0
    confidence: float = 0.0
    signal: str = ""  # BUY, SELL, HOLD
    model_type: str = ""
    top_features: Dict[str, float] = field(default_factory=dict)


@dataclass
class MultiHorizonResult:
    """Complete multi-horizon prediction result."""
    ticker: str = ""
    current_price: float = 0.0
    predictions: Dict[str, HorizonPrediction] = field(default_factory=dict)
    consensus_signal: str = ""
    consensus_confidence: float = 0.0
    horizon_agreement: float = 0.0
    error: str = ""


# =============================================================================
# FEATURE ENGINEERING
# =============================================================================


def _create_short_term_features(df: pd.DataFrame) -> pd.DataFrame:
    """Features optimized for short-term (1-5 day) prediction."""
    features = pd.DataFrame(index=df.index)
    close = df.get("Close", pd.Series(dtype=float))

    if close.empty:
        return features

    # Lagged returns
    for lag in [1, 2, 3, 5]:
        features[f"return_{lag}d"] = close.pct_change(lag)

    # Short-term MAs
    for window in [5, 10, 20]:
        features[f"ma_{window}"] = close.rolling(window).mean() / close - 1

    # RSI
    delta = close.diff()
    gain = delta.clip(lower=0).rolling(14).mean()
    loss = (-delta.clip(upper=0)).rolling(14).mean()
    features["rsi"] = 100 - (100 / (1 + gain / (loss + 1e-10)))

    # MACD
    ema12 = close.ewm(span=12).mean()
    ema26 = close.ewm(span=26).mean()
    features["macd"] = (ema12 - ema26) / close
    features["macd_signal"] = features["macd"].ewm(span=9).mean()

    # Bollinger Bands position
    ma20 = close.rolling(20).mean()
    std20 = close.rolling(20).std()
    features["bb_position"] = (close - ma20) / (2 * std20 + 1e-10)

    # Volume
    if "Volume" in df.columns:
        features["vol_ratio"] = df["Volume"] / (df["Volume"].rolling(10).mean() + 1e-10)

    # ATR
    if "High" in df.columns and "Low" in df.columns:
        tr = pd.concat([
            df["High"] - df["Low"],
            (df["High"] - close.shift()).abs(),
            (df["Low"] - close.shift()).abs(),
        ], axis=1).max(axis=1)
        features["atr_pct"] = tr.rolling(14).mean() / close

    return features


def _create_long_term_features(df: pd.DataFrame) -> pd.DataFrame:
    """Features optimized for long-term (20-60 day) prediction."""
    features = pd.DataFrame(index=df.index)
    close = df.get("Close", pd.Series(dtype=float))

    if close.empty:
        return features

    # Long-term returns
    for lag in [20, 40, 60]:
        features[f"return_{lag}d"] = close.pct_change(lag)

    # Long-term MAs
    for window in [50, 100, 200]:
        if len(close) >= window:
            features[f"ma_{window}"] = close.rolling(window).mean() / close - 1

    # Trend strength (slope of 50-day MA)
    if len(close) >= 50:
        ma50 = close.rolling(50).mean()
        features["trend_slope"] = ma50.pct_change(20)

    # Long-term volatility
    features["vol_60d"] = close.pct_change().rolling(60).std() * np.sqrt(252)

    # Momentum
    features["momentum_60d"] = close.pct_change(60)
    features["momentum_120d"] = close.pct_change(120) if len(close) >= 120 else 0

    # Drawdown
    rolling_max = close.rolling(120, min_periods=20).max()
    features["drawdown"] = close / rolling_max - 1

    # Volume trend
    if "Volume" in df.columns:
        features["vol_trend"] = df["Volume"].rolling(20).mean() / (df["Volume"].rolling(60).mean() + 1e-10)

    return features


# =============================================================================
# HORIZON MODELS
# =============================================================================


def _train_horizon_model(
    df: pd.DataFrame,
    horizon_days: int,
    feature_type: str = "short",
) -> Dict:
    """Train a model for a specific horizon."""
    if feature_type == "short":
        features = _create_short_term_features(df)
    else:
        features = _create_long_term_features(df)

    close = df["Close"]
    y = close.pct_change(horizon_days).shift(-horizon_days)

    # Align
    data = pd.concat([features, y.rename("target")], axis=1).dropna()
    if len(data) < 50:
        return {"error": f"Insufficient data: {len(data)} rows"}

    X = data.drop(columns=["target"]).values
    y_arr = data["target"].values

    # Split
    split = int(len(X) * 0.8)
    X_train, X_val = X[:split], X[split:]
    y_train, y_val = y_arr[:split], y_arr[split:]

    scaler = StandardScaler()
    X_train_s = scaler.fit_transform(X_train)
    X_val_s = scaler.transform(X_val)

    # Use Ridge for longer horizons (more regularization)
    if horizon_days <= 5:
        model = RandomForestRegressor(n_estimators=50, max_depth=6, random_state=42)
    else:
        model = Ridge(alpha=1.0)

    model.fit(X_train_s, y_train)

    # Validate
    val_pred = model.predict(X_val_s)
    val_mse = float(np.mean((val_pred - y_val) ** 2))
    val_mae = float(np.mean(np.abs(val_pred - y_val)))

    # Directional accuracy
    if len(val_pred) > 1:
        pred_dir = np.sign(val_pred)
        actual_dir = np.sign(y_val)
        dir_acc = float(np.mean(pred_dir == actual_dir))
    else:
        dir_acc = 0

    # Feature importance
    feature_names = list(data.drop(columns=["target"]).columns)
    if hasattr(model, "feature_importances_"):
        importance = dict(zip(feature_names, model.feature_importances_))
    elif hasattr(model, "coef_"):
        importance = dict(zip(feature_names, np.abs(model.coef_)))
    else:
        importance = {}

    # Predict next
    latest_features = features.iloc[-1:].values
    latest_features = np.nan_to_num(latest_features)
    latest_scaled = scaler.transform(latest_features)
    prediction = float(model.predict(latest_scaled)[0])

    return {
        "prediction": prediction,
        "val_mse": val_mse,
        "val_mae": val_mae,
        "directional_accuracy": dir_acc,
        "feature_importance": importance,
        "model_type": type(model).__name__,
        "n_samples": len(data),
    }


# =============================================================================
# MAIN API
# =============================================================================


def run_multi_horizon_prediction(
    df: pd.DataFrame,
    ticker: str = "",
    horizons: Optional[List[Tuple[str, int, str]]] = None,
) -> MultiHorizonResult:
    """
    Run multi-horizon prediction.

    Args:
        df: OHLCV DataFrame
        ticker: Ticker symbol
        horizons: List of (name, days, feature_type) tuples

    Returns:
        MultiHorizonResult with predictions for each horizon
    """
    if horizons is None:
        horizons = [
            ("3d", 3, "short"),
            ("1w", 5, "short"),
            ("1m", 21, "long"),
            ("3m", 63, "long"),
        ]

    current_price = float(df["Close"].iloc[-1]) if "Close" in df.columns else 0
    result = MultiHorizonResult(ticker=ticker, current_price=current_price)

    signals = []
    confidences = []

    for name, days, feature_type in horizons:
        model_result = _train_horizon_model(df, days, feature_type)

        if "error" in model_result:
            result.predictions[name] = HorizonPrediction(
                horizon=name, days=days, signal="HOLD",
            )
            continue

        pred_return = model_result["prediction"]
        pred_price = current_price * (1 + pred_return)
        dir_acc = model_result["directional_accuracy"]

        # Signal
        if pred_return > 0.02:
            signal = "BUY"
        elif pred_return < -0.02:
            signal = "SELL"
        else:
            signal = "HOLD"

        confidence = max(0, dir_acc)  # Use directional accuracy as confidence proxy

        # Top features
        importance = model_result.get("feature_importance", {})
        top_features = dict(sorted(importance.items(), key=lambda x: abs(x[1]), reverse=True)[:5])

        result.predictions[name] = HorizonPrediction(
            horizon=name,
            days=days,
            predicted_return=pred_return,
            predicted_price=pred_price,
            confidence=confidence,
            signal=signal,
            model_type=model_result["model_type"],
            top_features=top_features,
        )

        signals.append(signal)
        confidences.append(confidence)

    # Consensus
    if signals:
        buy_count = signals.count("BUY")
        sell_count = signals.count("SELL")
        hold_count = signals.count("HOLD")

        if buy_count > sell_count and buy_count > hold_count:
            result.consensus_signal = "BUY"
        elif sell_count > buy_count and sell_count > hold_count:
            result.consensus_signal = "SELL"
        else:
            result.consensus_signal = "HOLD"

        result.consensus_confidence = float(np.mean(confidences))
        result.horizon_agreement = float(max(buy_count, sell_count, hold_count) / len(signals))

    return result


def get_multi_horizon_confidence_adjustment(result: MultiHorizonResult) -> Tuple[float, str]:
    """Get confidence adjustment from multi-horizon prediction."""
    if result.error:
        return 0.0, "Multi-horizon: error"

    agreement = result.horizon_agreement
    signal = result.consensus_signal

    if agreement >= 0.75 and signal != "HOLD":
        return 0.08, f"Multi-horizon: strong {signal} consensus ({agreement:.0%})"
    elif agreement >= 0.5 and signal != "HOLD":
        return 0.04, f"Multi-horizon: moderate {signal} consensus ({agreement:.0%})"
    else:
        return 0.0, f"Multi-horizon: mixed signals ({agreement:.0%})"
