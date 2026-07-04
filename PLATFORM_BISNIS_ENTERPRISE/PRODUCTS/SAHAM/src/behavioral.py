"""
Behavioral Finance Module — Bias Detection & Sentiment Scoring.

Based on Kahneman-Tversky Prospect Theory and behavioral finance research:
- 10 cognitive biases that systematically distort investment decisions
- 5 emotional biases
- Market sentiment indicators (contrarian signals)
- Bias-adjusted decision framework

This module analyzes market data to detect:
1. Herding behavior (crowd positioning extremes)
2. Loss aversion signals (disposition effect proxy)
3. Overconfidence signals (excessive trading / volume spikes)
4. Anchoring (price fixation levels)
5. Recency bias (chasing recent performance)
6. FOMO / Panic signals
7. Contrarian sentiment scoring
"""

import pandas as pd
import numpy as np
from dataclasses import dataclass, field
from typing import Optional


@dataclass
class BehavioralAnalysis:
    overall_sentiment: str  # "extreme_fear", "fear", "neutral", "greed", "extreme_greed"
    sentiment_score: float  # -100 (extreme fear) to +100 (extreme greed)
    contrarian_signal: str  # "buy", "sell", "neutral"
    biases_detected: dict = field(default_factory=dict)
    herding_score: float = 0.0
    loss_aversion_score: float = 0.0
    overconfidence_score: float = 0.0
    recency_bias_score: float = 0.0
    fomo_score: float = 0.0
    panic_score: float = 0.0
    recommendations: list = field(default_factory=list)


def detect_herding(df: pd.DataFrame, close_col: str = "Close",
                   volume_col: str = "Volume", lookback: int = 20) -> float:
    """
    Detect herding behavior via volume clustering and price momentum alignment.
    High herding = everyone moving in same direction (dangerous).
    """
    returns = df[close_col].pct_change().tail(lookback)
    volume = df[volume_col].tail(lookback)

    # Volume trend: rising volume + same-direction returns = herding
    vol_trend = volume.pct_change().mean()
    ret_direction = returns.mean()

    # Alignment: % of days moving in same direction
    same_direction = (np.sign(returns) == np.sign(ret_direction)).mean()

    # Herding score: 0-100
    score = same_direction * 50 + min(abs(vol_trend) * 100, 50)
    return round(min(100, score), 1)


def detect_loss_aversion(df: pd.DataFrame, close_col: str = "Close",
                         volume_col: str = "Volume", lookback: int = 20) -> float:
    """
    Detect disposition effect proxy: volume higher on up-days than down-days
    suggests selling winners too early / holding losers too long.
    """
    returns = df[close_col].pct_change().tail(lookback)
    volume = df[volume_col].tail(lookback)

    up_days = returns > 0
    down_days = returns < 0

    up_vol = volume[up_days].mean() if up_days.any() else 0
    down_vol = volume[down_days].mean() if down_days.any() else 0

    # If up_vol > down_vol, people are selling winners (loss aversion)
    ratio = up_vol / down_vol if down_vol > 0 else 1.0

    # Score: higher ratio = more loss aversion
    score = min(100, (ratio - 1) * 50) if ratio > 1 else 0
    return round(score, 1)


def detect_overconfidence(df: pd.DataFrame, close_col: str = "Close",
                          volume_col: str = "Volume", lookback: int = 20) -> float:
    """
    Overconfidence proxy: high turnover (volume / avg volume) + high volatility.
    """
    volume = df[volume_col].tail(lookback)
    returns = df[close_col].pct_change().tail(lookback)

    avg_vol = volume.mean()
    vol_volatility = volume.std() / avg_vol if avg_vol > 0 else 0
    price_vol = returns.std()

    # High volume volatility + high price volatility = overconfidence trading
    score = min(100, vol_volatility * 50 + price_vol * 1000)
    return round(score, 1)


def detect_recency_bias(df: pd.DataFrame, close_col: str = "Close",
                        lookback: int = 5, long_lookback: int = 60) -> float:
    """
    Recency bias: recent returns overweighted vs longer-term.
    High when short-term returns diverge significantly from long-term.
    """
    short_ret = df[close_col].pct_change().tail(lookback).mean()
    long_ret = df[close_col].pct_change().tail(long_lookback).mean()

    divergence = abs(short_ret - long_ret)
    score = min(100, divergence * 10000)
    return round(score, 1)


def detect_fomo(df: pd.DataFrame, close_col: str = "Close",
                volume_col: str = "Volume", lookback: int = 10) -> float:
    """
    FOMO: rapid price increase + surging volume + closing near highs.
    """
    returns = df[close_col].pct_change().tail(lookback)
    volume = df[volume_col].tail(lookback)

    price_momentum = returns.sum()
    vol_surge = volume.iloc[-1] / volume.mean() if volume.mean() > 0 else 1

    # Strong positive momentum + volume surge = FOMO
    score = 0
    if price_momentum > 0.05:  # >5% in lookback
        score += 40
    if price_momentum > 0.10:
        score += 20
    if vol_surge > 1.5:
        score += 30
    if vol_surge > 2.0:
        score += 10

    return round(min(100, score), 1)


def detect_panic(df: pd.DataFrame, close_col: str = "Close",
                 volume_col: str = "Volume", lookback: int = 10) -> float:
    """
    Panic: rapid price decline + surging volume + closing near lows.
    """
    returns = df[close_col].pct_change().tail(lookback)
    volume = df[volume_col].tail(lookback)

    price_decline = -returns.sum()  # Positive if declining
    vol_surge = volume.iloc[-1] / volume.mean() if volume.mean() > 0 else 1

    score = 0
    if price_decline > 0.05:
        score += 40
    if price_decline > 0.10:
        score += 20
    if vol_surge > 1.5:
        score += 30
    if vol_surge > 2.0:
        score += 10

    return round(min(100, score), 1)


def calc_contrarian_sentiment(df: pd.DataFrame, close_col: str = "Close",
                              volume_col: str = "Volume",
                              rsi_col: Optional[str] = None,
                              lookback: int = 20) -> dict:
    """
    Contrarian sentiment: when crowd is most fearful, it's time to buy.
    When crowd is most greedy, it's time to sell.
    """
    returns = df[close_col].pct_change().tail(lookback)
    volume = df[volume_col].tail(lookback)

    # RSI
    if rsi_col and rsi_col in df.columns:
        rsi = df[rsi_col].iloc[-1]
    else:
        # Calculate simple RSI
        delta = df[close_col].diff()
        gain = delta.where(delta > 0, 0.0)
        loss = -delta.where(delta < 0, 0.0)
        avg_gain = gain.rolling(14).mean().iloc[-1]
        avg_loss = loss.rolling(14).mean().iloc[-1]
        rs = avg_gain / avg_loss if avg_loss > 0 else 100
        rsi = 100 - (100 / (1 + rs))

    # Volume surge
    vol_surge = volume.iloc[-1] / volume.mean() if volume.mean() > 0 else 1

    # Recent performance
    recent_ret = returns.sum() * 100

    # Sentiment score: -100 (extreme fear) to +100 (extreme greed)
    score = 0

    # RSI contribution
    if rsi > 70:
        score += 30  # Greedy
    elif rsi > 55:
        score += 15
    elif rsi < 30:
        score -= 30  # Fearful
    elif rsi < 45:
        score -= 15

    # Recent returns contribution
    if recent_ret > 10:
        score += 25
    elif recent_ret > 5:
        score += 15
    elif recent_ret < -10:
        score -= 25
    elif recent_ret < -5:
        score -= 15

    # Volume surge (high volume at extremes = emotional)
    if vol_surge > 2.0:
        if recent_ret > 0:
            score += 15  # Greed buying
        else:
            score -= 15  # Panic selling

    score = max(-100, min(100, score))

    # Labels
    if score > 50:
        sentiment = "extreme_greed"
        contrarian = "sell"
    elif score > 20:
        sentiment = "greed"
        contrarian = "cautious"
    elif score > -20:
        sentiment = "neutral"
        contrarian = "neutral"
    elif score > -50:
        sentiment = "fear"
        contrarian = "watch"
    else:
        sentiment = "extreme_fear"
        contrarian = "buy"

    return {
        "sentiment": sentiment,
        "score": round(score, 1),
        "contrarian_signal": contrarian,
        "rsi": round(rsi, 1),
        "volume_surge": round(vol_surge, 2),
        "recent_return_pct": round(recent_ret, 2),
    }


def analyze_behavioral(df: pd.DataFrame, close_col: str = "Close",
                       volume_col: str = "Volume",
                       rsi_col: Optional[str] = None) -> BehavioralAnalysis:
    """
    Full behavioral finance analysis.
    """
    # Detect individual biases
    herding = detect_herding(df, close_col, volume_col)
    loss_aversion = detect_loss_aversion(df, close_col, volume_col)
    overconfidence = detect_overconfidence(df, close_col, volume_col)
    recency = detect_recency_bias(df, close_col)
    fomo = detect_fomo(df, close_col, volume_col)
    panic = detect_panic(df, close_col, volume_col)

    # Contrarian sentiment
    sentiment = calc_contrarian_sentiment(df, close_col, volume_col, rsi_col)

    # Recommendations based on biases
    recommendations = []

    if fomo > 60:
        recommendations.append("⚠ FOMO detected — Avoid chasing. Wait for pullback.")
    if panic > 60:
        recommendations.append("⚠ Panic detected — Contrarian buy signal. Fear is high.")
    if herding > 70:
        recommendations.append("⚠ Herding detected — Crowd is aligned, reversal risk high.")
    if loss_aversion > 50:
        recommendations.append("⚠ Loss aversion detected — Don't hold losers too long. Use stop-loss.")
    if overconfidence > 60:
        recommendations.append("⚠ Overconfidence detected — Reduce position size, avoid overtrading.")
    if recency > 60:
        recommendations.append("⚠ Recency bias — Recent performance doesn't predict future. Check long-term.")

    if sentiment["contrarian_signal"] == "buy":
        recommendations.append("✅ Contrarian BUY signal — Extreme fear, smart money accumulating.")
    elif sentiment["contrarian_signal"] == "sell":
        recommendations.append("⚠ Contrarian SELL signal — Extreme greed, distribution likely.")
    elif sentiment["contrarian_signal"] == "cautious":
        recommendations.append("⚠ Contrarian CAUTIOUS — Greed rising, trim positions.")

    if not recommendations:
        recommendations.append("Sentiment is balanced — No strong contrarian signal.")

    biases = {
        "herding": herding,
        "loss_aversion": loss_aversion,
        "overconfidence": overconfidence,
        "recency_bias": recency,
        "fomo": fomo,
        "panic": panic,
    }

    return BehavioralAnalysis(
        overall_sentiment=sentiment["sentiment"],
        sentiment_score=sentiment["score"],
        contrarian_signal=sentiment["contrarian_signal"],
        biases_detected=biases,
        herding_score=herding,
        loss_aversion_score=loss_aversion,
        overconfidence_score=overconfidence,
        recency_bias_score=recency,
        fomo_score=fomo,
        panic_score=panic,
        recommendations=recommendations,
    )
