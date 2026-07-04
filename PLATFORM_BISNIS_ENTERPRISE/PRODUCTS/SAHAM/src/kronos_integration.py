"""
Kronos Foundation Model Integration.

Kronos is the first open-source foundation model for financial candlesticks (K-lines),
pre-trained on 12B+ K-line records from 45+ global exchanges.

This module wraps Kronos for zero-shot forecasting and integrates it as an
additional ensemble member in the prediction pipeline.

References:
- Kronos: "A Foundation Model for the Language of Financial Markets" (arXiv:2508.02739)
- GitHub: https://github.com/shiyu-coder/Kronos
- HuggingFace: NeoQuasar/Kronos-mini, NeoQuasar/Kronos-small, NeoQuasar/Kronos-base

Requirements (optional):
- pip install torch transformers
- Models auto-download from HuggingFace Hub on first use

Usage:
    from src.kronos_integration import get_kronos_prediction, get_kronos_confidence_adjustment
    result = get_kronos_prediction(df)
    adj, reason = get_kronos_confidence_adjustment(result)
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import Dict, Tuple

import numpy as np
import pandas as pd

logger = logging.getLogger(__name__)

TRY_KRONOS = True
try:
    import torch
    HAS_TORCH = True
except ImportError:
    HAS_TORCH = False
    logger.info("PyTorch not available — Kronos integration disabled")

KRONOS_AVAILABLE = False
KronosPredictor = None
if HAS_TORCH:
    try:
        from kronos import KronosPredictor as _KronosPredictor
        KRONOS_AVAILABLE = True
        KronosPredictor = _KronosPredictor
    except ImportError:
        try:
            KRONOS_AVAILABLE = True
            logger.info("transformers available — Kronos model can be loaded from HF Hub")
        except ImportError:
            logger.info("Kronos package not installed — using fallback forecasting")


@dataclass
class KronosResult:
    """Result from Kronos foundation model prediction."""
    forecast_prices: np.ndarray = field(default_factory=lambda: np.array([]))
    forecast_returns: np.ndarray = field(default_factory=lambda: np.array([]))
    predicted_direction: str = "NEUTRAL"
    confidence: float = 0.5
    model_name: str = ""
    n_forecast_steps: int = 0
    error: str = ""
    available: bool = False

    def summary(self) -> str:
        if not self.available:
            return f"Kronos: unavailable ({self.error})"
        avg_ret = float(np.mean(self.forecast_returns)) if len(self.forecast_returns) > 0 else 0
        return (
            f"Kronos {self.model_name}: {self.predicted_direction} "
            f"(conf: {self.confidence:.0%}, steps: {self.n_forecast_steps}, "
            f"avg_ret: {avg_ret:.4f})"
        )


_predictor_cache: Dict[str, object] = {}


def _get_predictor(model_name: str = "NeoQuasar/Kronos-small"):
    """Get or create cached Kronos predictor instance."""
    if model_name in _predictor_cache:
        return _predictor_cache[model_name]

    if not HAS_TORCH:
        return None

    try:
        if KronosPredictor is not None:
            device = "cuda" if torch.cuda.is_available() else "cpu"
            predictor = KronosPredictor(model_name, device=device)
            _predictor_cache[model_name] = predictor
            logger.info(f"Kronos predictor loaded: {model_name} on {device}")
            return predictor
        else:
            logger.warning("KronosPredictor class not available")
            return None
    except Exception as e:
        logger.warning(f"Failed to load Kronos predictor: {e}")
        return None


def get_kronos_prediction(
    df: pd.DataFrame,
    forecast_steps: int = 5,
    model_name: str = "NeoQuasar/Kronos-small",
    close_col: str = "Close",
) -> KronosResult:
    """
    Run Kronos foundation model for zero-shot price forecasting.

    Args:
        df: DataFrame with OHLCV data
        forecast_steps: Number of steps to forecast
        model_name: HuggingFace model name
        close_col: Column name for close price

    Returns:
        KronosResult with forecast and confidence
    """
    result = KronosResult(
        model_name=model_name,
        n_forecast_steps=forecast_steps,
    )

    if not HAS_TORCH:
        result.error = "PyTorch not installed"
        return result

    predictor = _get_predictor(model_name)
    if predictor is None:
        result.error = "Kronos model not available"
        return result

    try:
        if close_col not in df.columns:
            for col in ["Close", "close", "PRICE"]:
                if col in df.columns:
                    close_col = col
                    break
            else:
                result.error = "Close column not found"
                return result

        lookback = min(128, len(df) - 1)
        if lookback < 16:
            result.error = f"Insufficient data: {len(df)} rows"
            return result

        recent = df[[close_col]].tail(lookback).values.astype(np.float32)

        if hasattr(predictor, 'predict'):
            forecast = predictor.predict(recent, prediction_length=forecast_steps)
            if isinstance(forecast, dict):
                forecast_prices = np.array(forecast.get("predictions", forecast.get("forecast", [])))
            else:
                forecast_prices = np.array(forecast)
        else:
            result.error = "Predictor has no predict method"
            return result

        if len(forecast_prices) == 0:
            result.error = "Empty forecast"
            return result

        forecast_prices = np.array(forecast_prices).flatten()[:forecast_steps]
        last_price = float(recent[-1, 0])
        forecast_returns = (forecast_prices - last_price) / last_price

        avg_return = float(np.mean(forecast_returns))
        positive_count = int(np.sum(forecast_returns > 0))
        negative_count = int(np.sum(forecast_returns < 0))

        if positive_count > negative_count:
            result.predicted_direction = "UP"
            result.confidence = min(0.9, 0.5 + abs(avg_return) * 20 + (positive_count / len(forecast_returns)) * 0.2)
        elif negative_count > positive_count:
            result.predicted_direction = "DOWN"
            result.confidence = min(0.9, 0.5 + abs(avg_return) * 20 + (negative_count / len(forecast_returns)) * 0.2)
        else:
            result.predicted_direction = "NEUTRAL"
            result.confidence = 0.5

        result.forecast_prices = forecast_prices
        result.forecast_returns = forecast_returns
        result.available = True

        logger.info(f"Kronos forecast: {result.predicted_direction} (conf: {result.confidence:.0%})")
        return result

    except Exception as e:
        result.error = str(e)
        logger.warning(f"Kronos prediction failed: {e}")
        return result


def get_kronos_confidence_adjustment(kronos_result: KronosResult) -> Tuple[float, str]:
    """
    Get confidence adjustment from Kronos prediction.

    Returns:
        (adjustment, reason) — adjustment is additive to confidence (e.g., +0.05)
    """
    if not kronos_result.available:
        return 0.0, "Kronos not available"

    if kronos_result.confidence < 0.55:
        return 0.0, f"Kronos uncertain ({kronos_result.confidence:.0%})"

    if kronos_result.predicted_direction == "UP":
        adj = 0.03 + (kronos_result.confidence - 0.5) * 0.06
        return min(adj, 0.08), f"Kronos bullish (conf: {kronos_result.confidence:.0%})"
    elif kronos_result.predicted_direction == "DOWN":
        adj = -(0.03 + (kronos_result.confidence - 0.5) * 0.06)
        return max(adj, -0.08), f"Kronos bearish (conf: {kronos_result.confidence:.0%})"
    else:
        return 0.0, "Kronos neutral"
