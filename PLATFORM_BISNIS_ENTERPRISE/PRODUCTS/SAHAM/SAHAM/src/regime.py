"""
Market Regime Detection.

Mendeteksi regime pasar (Bull, Bear, Sideways) berdasarkan:
- Moving average alignment (MA50 vs MA200)
- Volatility regime (VIX levels, realized vol)
- Trend strength (ADX)
- Drawdown depth
- IHSG momentum

Regime detection penting untuk:
- Adjust strategy parameters per regime
- Filter out trades in unfavorable regimes
- Alert when regime changes

Referensi:
- Hamilton (1989) "Regime Switching Models"
- Markov-switching models for market regimes
- Fidelity: Market regime classification
"""

import pandas as pd
import numpy as np
from typing import Dict, List
from dataclasses import dataclass, field


@dataclass
class RegimeReport:
    """Market regime analysis result."""
    current_regime: str  # "bull", "bear", "sideways", "crisis"
    regime_score: float  # -100 (extreme bear) to +100 (extreme bull)
    ma_alignment: str  # "bullish", "bearish", "neutral"
    volatility_regime: str  # "low", "normal", "high", "extreme"
    trend_strength: float  # ADX-like score 0-100
    drawdown_status: str  # "none", "shallow", "deep", "extreme"
    momentum: str  # "positive", "negative", "flat"
    days_in_regime: int = 0
    regime_history: List[dict] = field(default_factory=list)
    recommendations: List[str] = field(default_factory=list)
    should_trade: bool = True
    position_size_multiplier: float = 1.0


def detect_market_regime(
    df: pd.DataFrame,
    close_col: str = "IHSG_Close",
    volume_col: str = "IHSG_Volume",
    vix_col: str = "VIX_Close",
    lookback: int = 252,  # 1 year
) -> RegimeReport:
    """
    Detect current market regime using multiple indicators.

    Args:
        df: DataFrame with market data
        close_col: Column name for IHSG close
        volume_col: Column name for volume
        vix_col: Column name for VIX
        lookback: Lookback period for regime analysis
    """
    if close_col not in df.columns:
        return RegimeReport(
            current_regime="unknown",
            regime_score=0,
            ma_alignment="neutral",
            volatility_regime="normal",
            trend_strength=0,
            drawdown_status="none",
            momentum="flat",
            recommendations=["Insufficient data for regime detection"],
            should_trade=True,
        )

    recent = df.tail(lookback).copy()
    close = recent[close_col].dropna()

    if len(close) < 50:
        return RegimeReport(
            current_regime="unknown",
            regime_score=0,
            ma_alignment="neutral",
            volatility_regime="normal",
            trend_strength=0,
            drawdown_status="none",
            momentum="flat",
            recommendations=["Insufficient data — need at least 50 bars"],
            should_trade=True,
        )

    # 1. MA Alignment (MA50 vs MA200)
    ma50 = close.rolling(window=50, min_periods=20).mean()
    ma200 = close.rolling(window=200, min_periods=50).mean()


    current_ma50 = ma50.iloc[-1] if not ma50.empty else close.iloc[-1]
    current_ma200 = ma200.iloc[-1] if not ma200.empty else close.iloc[-1]
    current_price = close.iloc[-1]

    if current_price > current_ma50 > current_ma200:
        ma_alignment = "bullish"
        ma_score = 40
    elif current_price < current_ma50 < current_ma200:
        ma_alignment = "bearish"
        ma_score = -40
    else:
        ma_alignment = "neutral"
        ma_score = 0

    # 2. Volatility Regime
    returns = close.pct_change().dropna()
    realized_vol = returns.std() * np.sqrt(252) if len(returns) > 1 else 0.15

    if vix_col in recent.columns:
        vix = recent[vix_col].dropna()
        current_vix = vix.iloc[-1] if not vix.empty else 20
    else:
        current_vix = realized_vol * 100

    if current_vix < 15:
        vol_regime = "low"
        vol_score = 20
    elif current_vix < 25:
        vol_regime = "normal"
        vol_score = 10
    elif current_vix < 35:
        vol_regime = "high"
        vol_score = -20
    else:
        vol_regime = "extreme"
        vol_score = -40

    # 3. Trend Strength (ADX-like calculation)
    # Simplified: use directional movement
    if len(close) > 20:
        up_moves = close.diff().clip(lower=0)
        down_moves = (-close.diff()).clip(lower=0)
        avg_up = up_moves.rolling(window=14).mean()
        avg_down = down_moves.rolling(window=14).mean()
        if avg_down.iloc[-1] > 0:
            avg_up.iloc[-1] / avg_down.iloc[-1]
            dx = (abs(avg_up.iloc[-1] - avg_down.iloc[-1]) /
                  (avg_up.iloc[-1] + avg_down.iloc[-1]) * 100)
        else:
            dx = 100 if avg_up.iloc[-1] > 0 else 0
        trend_strength = round(float(dx), 1)
    else:
        trend_strength = 0
        dx = 0

    # 4. Drawdown Status
    cummax = close.cummax()
    drawdown = ((close - cummax) / cummax) * 100
    current_dd = float(drawdown.iloc[-1])

    if current_dd > -5:
        dd_status = "none"
        dd_score = 20
    elif current_dd > -15:
        dd_status = "shallow"
        dd_score = 0
    elif current_dd > -30:
        dd_status = "deep"
        dd_score = -20
    else:
        dd_status = "extreme"
        dd_score = -40

    # 5. Momentum (20-day return)
    if len(close) > 20:
        momentum_20d = ((close.iloc[-1] / close.iloc[-20]) - 1) * 100
        if momentum_20d > 3:
            momentum = "positive"
            mom_score = 20
        elif momentum_20d < -3:
            momentum = "negative"
            mom_score = -20
        else:
            momentum = "flat"
            mom_score = 0
    else:
        momentum = "flat"
        mom_score = 0

    # 6. Composite Regime Score (-100 to +100)
    regime_score = ma_score + vol_score + dd_score + mom_score
    regime_score = max(-100, min(100, regime_score))

    # 7. Classify Regime
    if regime_score >= 50:
        regime = "bull"
    elif regime_score >= 20:
        regime = "bullish_sideways"
    elif regime_score >= -20:
        regime = "sideways"
    elif regime_score >= -50:
        regime = "bearish_sideways"
    else:
        regime = "bear"

    # Crisis override: extreme vol + extreme drawdown
    if vol_regime == "extreme" and dd_status == "extreme":
        regime = "crisis"

    # 8. Recommendations
    recommendations = []
    should_trade = True
    size_mult = 1.0

    if regime == "crisis":
        recommendations.append("CRISIS: Hentikan trading — volatilitas extreme + drawdown extreme")
        should_trade = False
        size_mult = 0.0
    elif regime == "bear":
        recommendations.append("BEAR: Hanya consider short atau cash — hindari long")
        size_mult = 0.3
    elif regime == "bearish_sideways":
        recommendations.append("BEARISH SIDEWAYS: Reduce position size, tight stops")
        size_mult = 0.5
    elif regime == "sideways":
        recommendations.append("SIDEWAYS: Normal trading dengan tight stops")
        size_mult = 0.7
    elif regime == "bullish_sideways":
        recommendations.append("BULLISH SIDEWAYS: Normal position size")
        size_mult = 0.8
    elif regime == "bull":
        recommendations.append("BULL: Full position size, trend following")
        size_mult = 1.0

    if vol_regime == "high":
        recommendations.append("Volatilitas tinggi — reduce position size 20%")
        size_mult *= 0.8
    if vol_regime == "extreme":
        recommendations.append("Volatilitas extreme — halt new entries")
        should_trade = False

    if trend_strength < 20:
        recommendations.append(f"Trend lemah (ADX={trend_strength:.0f}) — sideways strategy")
    elif trend_strength > 50:
        recommendations.append(f"Trend kuat (ADX={trend_strength:.0f}) — trend following")

    return RegimeReport(
        current_regime=regime,
        regime_score=round(regime_score, 1),
        ma_alignment=ma_alignment,
        volatility_regime=vol_regime,
        trend_strength=trend_strength,
        drawdown_status=dd_status,
        momentum=momentum,
        recommendations=recommendations,
        should_trade=should_trade,
        position_size_multiplier=round(size_mult, 2),
    )


def regime_adjusted_prediction(
    df: pd.DataFrame,
    signal: str,
    confidence: float,
    close_col: str = "IHSG_Close",
) -> Dict:
    """
    Adjust prediction based on market regime.

    Returns adjusted signal, confidence, and position size multiplier.
    """
    regime = detect_market_regime(df, close_col=close_col)

    adjusted_signal = signal
    adjusted_confidence = confidence

    # Override signal if regime is unfavorable
    if not regime.should_trade:
        adjusted_signal = "HOLD"
        adjusted_confidence = confidence * 0.5
    elif regime.current_regime == "bear" and signal == "BUY":
        # In bear market, downgrade BUY to HOLD unless very confident
        if confidence < 0.70:
            adjusted_signal = "HOLD"
            adjusted_confidence = confidence * 0.7

    return {
        "original_signal": signal,
        "adjusted_signal": adjusted_signal,
        "original_confidence": confidence,
        "adjusted_confidence": round(adjusted_confidence, 4),
        "regime": regime.current_regime,
        "regime_score": regime.regime_score,
        "should_trade": regime.should_trade,
        "position_size_multiplier": regime.position_size_multiplier,
        "volatility_regime": regime.volatility_regime,
        "trend_strength": regime.trend_strength,
        "drawdown_status": regime.drawdown_status,
        "recommendations": regime.recommendations,
    }
