"""
Composite AI Score & Multi-Dimension Rating.

Implementasi:
- Composite AI Score (1-10) — ranking user-friendly (dari Kavout/Danelfin)
- Multi-Dimension Rating: Technical (0-100), Sentiment (0-100), Momentum (0-100)
- 0-100 Composite Score (dari Market Digest)
- Skill Pack Modular Scoring (dari MarketLayer)

Referensi:
- Kavout: Kai Score 1-9 scale
- Danelfin: AI Score 1-10, probability beat market
- Market Digest: 0-100 composite score
- MarketLayer: Skill pack scoring (Momentum, Risk Radar, Mean Reversion, dll)
"""

import numpy as np
import pandas as pd
from typing import Dict, Optional


def calc_composite_ai_score(
    predictions: Dict[str, int],
    probabilities: Dict[str, float],
    confidence: float,
    rsi: float = 50.0,
    trend_bullish: bool = False,
    trend_bearish: bool = False,
    vix: float = 20.0,
    fear_greed: float = 50.0,
    ma_alignment: float = 0.0,
    macd_signal: float = 0.0,
    bb_position: float = 0.5,
    volume_trend: float = 0.0,
) -> Dict[str, float]:
    """
    Hitung Composite AI Score (1-10) dan Multi-Dimension Rating (0-100).

    Menggabungkan:
    - Model ensemble votes & probabilities
    - Technical indicators (RSI, MACD, BB, MA alignment)
    - Sentiment (Fear & Greed, VIX)
    - Momentum (trend, volume)

    Returns:
        Dict dengan:
        - ai_score: 1-10 (user-friendly ranking)
        - composite_score: 0-100 (detailed score)
        - technical_rating: 0-100
        - sentiment_rating: 0-100
        - momentum_rating: 0-100
        - risk_rating: 0-100 (higher = safer)
        - signal_strength: "Sangat Lemah" / "Lemah" / "Netral" / "Kuat" / "Sangat Kuat"
    """
    votes_buy = sum(1 for p in predictions.values() if p == 1)
    votes_sell = sum(1 for p in predictions.values() if p == 0)
    total_models = len(predictions)

    avg_proba = np.mean(list(probabilities.values())) if probabilities else 0.5

    # === Technical Rating (0-100) ===
    tech_score = 50.0

    # RSI component (0-100): 50 is neutral
    rsi_component = 50.0
    if rsi > 70:
        rsi_component = 30.0  # Overbought = bearish technical
    elif rsi < 30:
        rsi_component = 70.0  # Oversold = potential bounce
    elif rsi > 50:
        rsi_component = 50.0 + (rsi - 50) * 0.5
    else:
        rsi_component = 50.0 - (50 - rsi) * 0.5

    # MA alignment: bullish if MA5 > MA10 > MA20
    ma_component = 50.0 + (ma_alignment * 20)  # ma_alignment: -1 to 1

    # MACD signal
    macd_component = 50.0 + (macd_signal * 20)  # macd_signal: -1 to 1

    # Bollinger Bands position (0 = lower band, 1 = upper band)
    bb_component = 50.0 + (0.5 - bb_position) * 40  # Below middle = bullish bias

    tech_score = np.clip(
        0.30 * rsi_component + 0.25 * ma_component + 0.25 * macd_component + 0.20 * bb_component,
        0, 100
    )

    # === Sentiment Rating (0-100) ===
    # Fear & Greed: 0 = extreme fear (contrarian buy), 100 = extreme greed (contrarian sell)
    # For scoring: moderate fear = good for buying
    fg_component = fear_greed

    # VIX: low VIX = calm = bullish sentiment
    vix_component = np.clip(100 - (vix - 15) * 3, 0, 100)

    sentiment_score = np.clip(0.6 * fg_component + 0.4 * vix_component, 0, 100)

    # === Momentum Rating (0-100) ===
    # Trend direction
    if trend_bullish:
        trend_component = 75.0
    elif trend_bearish:
        trend_component = 25.0
    else:
        trend_component = 50.0

    # Volume trend: positive = increasing volume = momentum
    volume_component = np.clip(50.0 + volume_trend * 50, 0, 100)

    # Model consensus
    if total_models > 0:
        consensus = (votes_buy / total_models) * 100
    else:
        consensus = 50.0

    momentum_score = np.clip(
        0.35 * trend_component + 0.25 * volume_component + 0.40 * consensus,
        0, 100
    )

    # === Risk Rating (0-100, higher = safer) ===
    # VIX-based risk
    vix_risk = np.clip(100 - (vix - 15) * 3, 0, 100)

    # Confidence-based risk
    conf_risk = confidence * 100

    # RSI extreme risk
    if rsi > 75 or rsi < 25:
        rsi_risk = 40.0
    else:
        rsi_risk = 70.0

    risk_score = np.clip(0.40 * vix_risk + 0.35 * conf_risk + 0.25 * rsi_risk, 0, 100)

    # === Composite Score (0-100) ===
    composite = np.clip(
        0.30 * tech_score +
        0.20 * sentiment_score +
        0.30 * momentum_score +
        0.20 * risk_score,
        0, 100
    )

    # === AI Score (1-10) ===
    # Map composite 0-100 to 1-10
    ai_score = max(1, min(10, round(composite / 10)))

    # Adjust based on model votes
    if votes_buy > votes_sell and avg_proba > 0.6:
        ai_score = max(ai_score, 6)
    elif votes_sell > votes_buy and avg_proba < 0.4:
        ai_score = min(ai_score, 5)

    # === Signal Strength ===
    if composite >= 80:
        strength = "Sangat Kuat"
    elif composite >= 65:
        strength = "Kuat"
    elif composite >= 45:
        strength = "Netral"
    elif composite >= 25:
        strength = "Lemah"
    else:
        strength = "Sangat Lemah"

    return {
        "ai_score": ai_score,
        "composite_score": round(composite, 1),
        "technical_rating": round(tech_score, 1),
        "sentiment_rating": round(sentiment_score, 1),
        "momentum_rating": round(momentum_score, 1),
        "risk_rating": round(risk_score, 1),
        "signal_strength": strength,
        "model_consensus": round(consensus, 1),
    }


def calc_skill_pack_scores(
    df: pd.DataFrame,
    target_name: str = "IHSG",
    predictions: Optional[Dict[str, int]] = None,
    probabilities: Optional[Dict[str, float]] = None,
) -> Dict[str, float]:
    """
    Skill Pack Modular Scoring (dari MarketLayer).

    Setiap skill pack menghasilkan skor 0-100:
    - Momentum: trend following strength
    - Mean Reversion: oversold/overbought reversal potential
    - Risk Radar: volatility & risk assessment
    - Volume Pulse: volume anomaly & smart money detection
    - Sentiment: fear & greed based
    - Macro: inter-market & macro conditions
    """
    prefix = f"{target_name}_"
    latest = df.iloc[-1]

    scores = {}

    # === Momentum Skill Pack ===
    rsi = latest.get("Target_RSI", 50)
    ma5 = latest.get(f"{prefix}MA5", 0)
    ma10 = latest.get(f"{prefix}MA10", 0)
    ma20 = latest.get(f"{prefix}MA20", 0)
    returns = latest.get("Target_Returns", 0)

    momentum = 50.0
    if ma5 > ma10 > ma20:
        momentum += 20
    elif ma5 < ma10 < ma20:
        momentum -= 20
    if rsi > 50:
        momentum += (rsi - 50) * 0.5
    else:
        momentum -= (50 - rsi) * 0.5
    if returns > 0:
        momentum += 10
    scores["Momentum"] = round(np.clip(momentum, 0, 100), 1)

    # === Mean Reversion Skill Pack ===
    bb_pct = latest.get("Target_BB_Pct", 0.5)
    williams_r = latest.get("Target_Williams_R", -50)
    stoch_k = latest.get("Target_Stoch_K", 50)

    mean_rev = 50.0
    if bb_pct < 0.2:
        mean_rev += 25  # Near lower BB = oversold = reversal candidate
    elif bb_pct > 0.8:
        mean_rev -= 25  # Near upper BB = overbought
    if williams_r > -20:
        mean_rev -= 15
    elif williams_r < -80:
        mean_rev += 15
    if stoch_k < 20:
        mean_rev += 10
    elif stoch_k > 80:
        mean_rev -= 10
    scores["Mean Reversion"] = round(np.clip(mean_rev, 0, 100), 1)

    # === Risk Radar Skill Pack ===
    vix_close = latest.get("VIX_Close", 20)
    atr_pct = latest.get("Target_ATR_Pct", 1.5)
    volatility = latest.get("Target_Volatility", 0.01)

    risk = 100.0
    risk -= max(0, (vix_close - 15) * 2.5)
    risk -= max(0, (atr_pct - 1.5) * 10)
    risk -= max(0, (volatility - 0.02) * 500)
    scores["Risk Radar"] = round(np.clip(risk, 0, 100), 1)

    # === Volume Pulse Skill Pack ===
    obv = latest.get("Target_OBV", 0)
    obv_ma = latest.get("Target_OBV_MA", 0)
    vol_ma = latest.get(f"{prefix}Volume_MA", 0)
    volume = latest.get(f"{prefix}Volume", 0)

    vol_pulse = 50.0
    if obv > obv_ma:
        vol_pulse += 15  # OBV above MA = accumulation
    if volume > vol_ma * 1.5:
        vol_pulse += 20  # Volume spike
    elif volume < vol_ma * 0.5:
        vol_pulse -= 10  # Volume dry-up
    scores["Volume Pulse"] = round(np.clip(vol_pulse, 0, 100), 1)

    # === Sentiment Skill Pack ===
    fg = latest.get("Fear_Greed_Index", 50)
    sentiment = fg
    scores["Sentiment"] = round(np.clip(sentiment, 0, 100), 1)

    # === Macro Skill Pack ===
    vix_ret = latest.get("VIX_Returns", 0)
    usd_idr = latest.get("USD_IDR_Returns", 0) if "USD_IDR_Returns" in df.columns else 0
    latest.get("Gold_IHSG_Ratio", 0)

    macro = 50.0
    if vix_ret < 0:
        macro += 10  # VIX dropping = risk-on
    if usd_idr < 0:
        macro += 10  # IDR strengthening = good for IHSG
    scores["Macro"] = round(np.clip(macro, 0, 100), 1)

    # === Overall Skill Pack Score ===
    scores["Overall"] = round(np.mean(list(scores.values())), 1)

    return scores
