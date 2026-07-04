"""
Wyckoff Method — Smart Money Accumulation/Distribution Detection.

Based on Richard Wyckoff's work (1910s-1930s):
- 4 phases: Accumulation → Markup → Distribution → Markdown
- 3 laws: Supply/Demand, Cause/Effect, Effort vs Result
- Key events: Spring, UTAD, SOS, SOW
- Volume is the tell — institutional footprints

This module detects which Wyckoff phase the market is in by analyzing
price-volume interaction, range characteristics, and volume signatures.
"""

import pandas as pd
import numpy as np
from dataclasses import dataclass, field


@dataclass
class WyckoffAnalysis:
    phase: str  # "accumulation", "markup", "distribution", "markdown", "unclear"
    phase_score: float  # -100 (strong markdown) to +100 (strong markup)
    volume_signature: str  # "accumulation", "distribution", "neutral"
    spring_detected: bool = False
    utad_detected: bool = False
    sos_detected: bool = False  # Sign of Strength
    sow_detected: bool = False  # Sign of Weakness
    cause_built: float = 0.0  # Range size as % — larger = bigger expected move
    effort_result_divergence: float = 0.0  # Positive = bullish absorption
    recommendation: str = ""
    details: dict = field(default_factory=dict)


def detect_wyckoff_phase(
    df: pd.DataFrame,
    close_col: str = "Close",
    high_col: str = "High",
    low_col: str = "Low",
    volume_col: str = "Volume",
    lookback: int = 60,
    short_lookback: int = 20,
) -> WyckoffAnalysis:
    """
    Detect Wyckoff phase by analyzing price-volume behavior.

    Accumulation signals:
    - Range-bound after downtrend
    - Down-bars on shrinking volume, up-bars on rising volume
    - Spring: false breakdown with high volume but quick recovery

    Distribution signals:
    - Range-bound after uptrend
    - Up-bars on high volume but price doesn't extend
    - UTAD: false breakout with high volume but quick reversal

    Markup: trending up with volume on up-days
    Markdown: trending down with volume on down-days
    """
    if len(df) < lookback:
        return WyckoffAnalysis(phase="unclear", phase_score=0, volume_signature="neutral")

    recent = df.tail(lookback).copy()
    df.tail(short_lookback).copy()

    close = recent[close_col]
    high = recent[high_col]
    low = recent[low_col]
    volume = recent[volume_col]

    # === Trend Detection ===
    close.pct_change().dropna()
    cumulative_return = (close.iloc[-1] / close.iloc[0] - 1) * 100

    # Linear regression slope for trend
    x = np.arange(len(close))
    if len(close) > 2:
        slope = np.polyfit(x, close.values, 1)[0]
        slope_pct = slope / close.mean() * 100
    else:
        slope_pct = 0

    # === Range Analysis ===
    range_high = high.max()
    range_low = low.min()
    range_size_pct = (range_high - range_low) / range_low * 100
    current_pos = (close.iloc[-1] - range_low) / (range_high - range_low)  # 0=bottom, 1=top

    # Is it range-bound? (low slope, contained within range)
    is_range_bound = abs(slope_pct) < 0.05 and range_size_pct < 30

    # === Volume Signature ===
    # Up-days vs down-days volume
    price_change = close.diff()
    up_mask = price_change > 0
    down_mask = price_change < 0

    avg_up_vol = volume[up_mask].mean() if up_mask.any() else 0
    avg_down_vol = volume[down_mask].mean() if down_mask.any() else 0
    vol_ratio = avg_up_vol / avg_down_vol if avg_down_vol > 0 else 1.0

    # Volume trend (increasing or decreasing over the range)
    vol_first_half = volume.head(lookback // 2).mean()
    vol_second_half = volume.tail(lookback // 2).mean()
    vol_trend = (vol_second_half / vol_first_half - 1) if vol_first_half > 0 else 0

    # === Effort vs Result (Law of Effort vs Result) ===
    # Effort = volume, Result = price change
    # High volume + small price move = absorption (institutions taking the other side)
    spread = (high - low) / close * 100  # Daily range as %
    vol_normalized = volume / volume.rolling(20).mean()
    effort = vol_normalized * spread
    result = price_change.abs() / close * 100

    # Divergence: high effort, low result = absorption
    recent_effort = effort.tail(short_lookback).mean()
    recent_result = result.tail(short_lookback).mean()
    effort_result_ratio = recent_effort / recent_result if recent_result > 0 else 1
    # High ratio = high effort low result = absorption (bullish if at lows)

    # === Spring Detection ===
    # Spring: price breaks below range low with high volume, then quickly recovers
    spring_detected = False
    for i in range(-5, 0):
        if abs(i) <= len(low):
            if low.iloc[i] < range_low * 0.98 and close.iloc[i] > range_low * 0.99:
                if volume.iloc[i] > volume.rolling(20).mean().iloc[i] * 1.5:
                    spring_detected = True

    # === UTAD Detection (Upthrust After Distribution) ===
    # UTAD: price breaks above range high with high volume, then quickly reverses
    utad_detected = False
    for i in range(-5, 0):
        if abs(i) <= len(high):
            if high.iloc[i] > range_high * 1.02 and close.iloc[i] < range_high * 1.01:
                if volume.iloc[i] > volume.rolling(20).mean().iloc[i] * 1.5:
                    utad_detected = True

    # === SOS / SOW Detection ===
    # SOS: large bar with high volume, closes near high (bullish)
    # SOW: large bar with high volume, closes near low (bearish)
    last_bar_spread = (high.iloc[-1] - low.iloc[-1]) / close.iloc[-1] * 100
    last_bar_vol_ratio = volume.iloc[-1] / volume.rolling(20).mean().iloc[-1]
    last_bar_close_pos = (close.iloc[-1] - low.iloc[-1]) / (high.iloc[-1] - low.iloc[-1]) if (high.iloc[-1] - low.iloc[-1]) > 0 else 0.5

    sos_detected = last_bar_spread > spread.rolling(20).mean().iloc[-1] * 1.5 and last_bar_vol_ratio > 1.5 and last_bar_close_pos > 0.7
    sow_detected = last_bar_spread > spread.rolling(20).mean().iloc[-1] * 1.5 and last_bar_vol_ratio > 1.5 and last_bar_close_pos < 0.3

    # === Phase Determination ===
    phase_score = 0
    phase = "unclear"
    volume_signature = "neutral"

    # Prior trend context
    prior_trend = "down" if cumulative_return < -5 else "up" if cumulative_return > 5 else "flat"

    if is_range_bound:
        if prior_trend == "down" or current_pos < 0.4:
            # Potential accumulation
            if vol_ratio > 1.2:  # Up volume > down volume
                phase = "accumulation"
                volume_signature = "accumulation"
                phase_score = 30 + vol_ratio * 10
            elif spring_detected:
                phase = "accumulation"
                volume_signature = "accumulation"
                phase_score = 50
            else:
                phase = "accumulation"
                phase_score = 15

        elif prior_trend == "up" or current_pos > 0.6:
            # Potential distribution
            if vol_ratio < 0.8:  # Down volume > up volume
                phase = "distribution"
                volume_signature = "distribution"
                phase_score = -30 + vol_ratio * 10
            elif utad_detected:
                phase = "distribution"
                volume_signature = "distribution"
                phase_score = -50
            else:
                phase = "distribution"
                phase_score = -15

        else:
            phase = "unclear"
            phase_score = 0
    else:
        # Trending
        if slope_pct > 0.1:
            phase = "markup"
            phase_score = 50 + min(slope_pct * 10, 50)
            volume_signature = "markup" if vol_ratio > 1.0 else "weak_markup"
        elif slope_pct < -0.1:
            phase = "markdown"
            phase_score = -50 + max(slope_pct * 10, -50)
            volume_signature = "markdown" if vol_ratio < 1.0 else "weak_markdown"

    # Adjust for events
    if spring_detected:
        phase_score += 20
        phase = "accumulation"
    if utad_detected:
        phase_score -= 20
        phase = "distribution"
    if sos_detected:
        phase_score += 15
    if sow_detected:
        phase_score -= 15

    # Effort-Result divergence at range bottom = bullish absorption
    if effort_result_ratio > 1.5 and current_pos < 0.4:
        phase_score += 10
    if effort_result_ratio > 1.5 and current_pos > 0.6:
        phase_score -= 10

    phase_score = max(-100, min(100, phase_score))

    # === Recommendation ===
    if phase == "accumulation" and phase_score > 40:
        recommendation = "ACCUMULATION detected — Smart money buying. Consider building position."
    elif phase == "accumulation":
        recommendation = "Possible accumulation — Watch for spring or SOS for confirmation."
    elif phase == "markup":
        recommendation = "MARKUP phase — Trend is up. Buy on pullbacks to support."
    elif phase == "distribution" and phase_score < -40:
        recommendation = "DISTRIBUTION detected — Smart money selling. Consider reducing position."
    elif phase == "distribution":
        recommendation = "Possible distribution — Watch for UTAD or SOW for confirmation."
    elif phase == "markdown":
        recommendation = "MARKDOWN phase — Downtrend in progress. Stay defensive."
    else:
        recommendation = "Phase unclear — Wait for clearer signals."

    return WyckoffAnalysis(
        phase=phase,
        phase_score=round(phase_score, 1),
        volume_signature=volume_signature,
        spring_detected=spring_detected,
        utad_detected=utad_detected,
        sos_detected=sos_detected,
        sow_detected=sow_detected,
        cause_built=round(range_size_pct, 2),
        effort_result_divergence=round(effort_result_ratio, 2),
        recommendation=recommendation,
        details={
            "cumulative_return_pct": round(cumulative_return, 2),
            "slope_pct": round(slope_pct, 4),
            "range_size_pct": round(range_size_pct, 2),
            "current_range_position": round(current_pos, 2),
            "up_down_vol_ratio": round(vol_ratio, 2),
            "vol_trend": round(vol_trend, 2),
            "is_range_bound": is_range_bound,
            "prior_trend": prior_trend,
            "last_bar_close_position": round(last_bar_close_pos, 2),
        }
    )
