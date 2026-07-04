"""
Elliott Wave Detection Module.

Based on Ralph Nelson Elliott's wave principle (1930s):
- 5-wave impulse pattern (waves 1,2,3,4,5) in direction of main trend
- 3-wave corrective pattern (waves A,B,C) against main trend
- Fibonacci ratios between waves

Rules (unbreakable):
1. Wave 2 cannot retrace more than 100% of Wave 1
2. Wave 3 cannot be the shortest of waves 1, 3, 5
3. Wave 4 cannot overlap with Wave 1 price territory

Fibonacci relationships:
- Wave 2 retraces 50-61.8% of Wave 1
- Wave 3 = 161.8% extension of Wave 1
- Wave 4 retraces 38.2% of Wave 3
- Wave 5 = 61.8% or 100% of Wave 1
"""

import pandas as pd
from dataclasses import dataclass, field
from typing import Optional, List


@dataclass
class WavePoint:
    index: int
    price: float
    wave_label: str  # "1", "2", "3", "4", "5", "A", "B", "C"


@dataclass
class ElliottWaveAnalysis:
    pattern_type: str  # "impulse", "corrective", "unclear"
    current_wave: str  # Which wave we're likely in
    wave_points: List[WavePoint] = field(default_factory=list)
    fib_ratios: dict = field(default_factory=dict)
    rules_valid: bool = True
    rule_violations: List[str] = field(default_factory=list)
    next_target: Optional[float] = None
    invalidation_level: Optional[float] = None
    confidence: float = 0.0
    recommendation: str = ""


def find_pivots(df: pd.DataFrame, close_col: str = "Close",
                lookback: int = 5) -> List[tuple]:
    """Find swing highs and lows in price data."""
    close = df[close_col].values
    pivots = []

    for i in range(lookback, len(close) - lookback):
        window_left = close[i - lookback:i]
        window_right = close[i + 1:i + lookback + 1]

        # Swing high
        if close[i] > window_left.max() and close[i] > window_right.max():
            pivots.append((i, close[i], "high"))
        # Swing low
        elif close[i] < window_left.min() and close[i] < window_right.min():
            pivots.append((i, close[i], "low"))

    return pivots


def detect_elliott_wave(
    df: pd.DataFrame,
    close_col: str = "Close",
    pivot_lookback: int = 5,
    min_pivots: int = 5,
) -> ElliottWaveAnalysis:
    """
    Detect Elliott Wave pattern from swing pivots.

    Attempts to label pivots as impulse (1-2-3-4-5) or corrective (A-B-C)
    and validate against Elliott's rules.
    """
    if len(df) < 50:
        return ElliottWaveAnalysis(
            pattern_type="unclear",
            current_wave="unknown",
            recommendation="Insufficient data for Elliott Wave analysis"
        )

    pivots = find_pivots(df, close_col, pivot_lookback)

    if len(pivots) < min_pivots:
        return ElliottWaveAnalysis(
            pattern_type="unclear",
            current_wave="unknown",
            recommendation="Not enough pivots to identify wave structure"
        )

    # Use last N pivots for analysis
    recent_pivots = pivots[-min(min_pivots, 7):]

    # Determine if pattern is impulse or corrective
    # Impulse: alternating high-low-high-low-high (5 pivots for 5 waves)
    # Corrective: high-low-high or low-high-low (3 pivots for A-B-C)

    pattern_type = "unclear"
    current_wave = "unknown"
    wave_points = []
    fib_ratios = {}
    rules_valid = True
    rule_violations = []

    # Try to fit impulse pattern (need at least 5 pivots: low-high-low-high-low for bullish)
    if len(recent_pivots) >= 5:
        # Check if pattern starts with low (bullish impulse)
        starts_low = recent_pivots[0][2] == "low"

        if starts_low:
            # Bullish impulse: low(1) - high(1) - low(2) - high(3) - low(4) - high(5)
            wave_labels = ["1_start", "1_end", "2_end", "3_end", "4_end", "5_end"]
        else:
            # Bearish impulse: high(1) - low(1) - high(2) - low(3) - high(4) - low(5)
            wave_labels = ["1_start", "1_end", "2_end", "3_end", "4_end", "5_end"]

        if len(recent_pivots) >= 6:
            pattern_type = "impulse"
            for i, (idx, price, ptype) in enumerate(recent_pivots[:6]):
                label = wave_labels[i] if i < len(wave_labels) else f"extra_{i}"
                wave_points.append(WavePoint(index=idx, price=price, wave_label=label))

            # Calculate wave lengths
            if len(wave_points) >= 6:
                w1 = wave_points[1].price - wave_points[0].price  # Wave 1
                w2 = wave_points[2].price - wave_points[1].price  # Wave 2 (retracement)
                w3 = wave_points[3].price - wave_points[2].price  # Wave 3
                w4 = wave_points[4].price - wave_points[3].price  # Wave 4 (retracement)
                w5 = wave_points[5].price - wave_points[4].price  # Wave 5

                # Fibonacci ratios
                if w1 != 0:
                    fib_ratios["w2_retrace_w1"] = round(abs(w2 / w1) * 100, 1)
                if w1 != 0:
                    fib_ratios["w3_extend_w1"] = round(abs(w3 / w1) * 100, 1)
                if w3 != 0:
                    fib_ratios["w4_retrace_w3"] = round(abs(w4 / w3) * 100, 1)
                if w1 != 0:
                    fib_ratios["w5_vs_w1"] = round(abs(w5 / w1) * 100, 1)

                # Validate rules
                # Rule 1: Wave 2 cannot retrace more than 100% of Wave 1
                if starts_low and w2 > 0 and abs(w2) > abs(w1):
                    rules_valid = False
                    rule_violations.append("Wave 2 retraces >100% of Wave 1")
                elif not starts_low and w2 < 0 and abs(w2) > abs(w1):
                    rules_valid = False
                    rule_violations.append("Wave 2 retraces >100% of Wave 1")

                # Rule 2: Wave 3 cannot be shortest of 1, 3, 5
                waves_135 = [abs(w1), abs(w3), abs(w5)]
                if waves_135[1] < min(waves_135[0], waves_135[2]) and waves_135[2] > 0:
                    rules_valid = False
                    rule_violations.append("Wave 3 is shortest of 1,3,5")

                # Rule 3: Wave 4 cannot overlap Wave 1 territory
                if starts_low:
                    if wave_points[4].price < wave_points[1].price:
                        rules_valid = False
                        rule_violations.append("Wave 4 overlaps Wave 1 territory")
                else:
                    if wave_points[4].price > wave_points[1].price:
                        rules_valid = False
                        rule_violations.append("Wave 4 overlaps Wave 1 territory")

                # Determine current wave
                current_price = df[close_col].iloc[-1]
                if current_price > wave_points[5].price and starts_low:
                    current_wave = "beyond_5"
                elif current_price < wave_points[5].price and not starts_low:
                    current_wave = "beyond_5"
                else:
                    current_wave = "5"

                # Next target (Fibonacci extension)
                if starts_low:
                    next_target = wave_points[3].price + (wave_points[3].price - wave_points[2].price) * 0.618
                    invalidation = wave_points[2].price  # Wave 2 low
                else:
                    next_target = wave_points[3].price - (wave_points[2].price - wave_points[3].price) * 0.618
                    invalidation = wave_points[2].price  # Wave 2 high

                # Confidence based on rule compliance and Fibonacci alignment
                confidence = 50.0
                if rules_valid:
                    confidence += 20
                if 50 <= fib_ratios.get("w2_retrace_w1", 0) <= 78.6:
                    confidence += 10
                if fib_ratios.get("w3_extend_w1", 0) >= 127.2:
                    confidence += 10
                if 23.6 <= fib_ratios.get("w4_retrace_w3", 0) <= 50:
                    confidence += 10

                confidence = min(95, confidence)

    # Try corrective pattern if impulse not found
    if pattern_type == "unclear" and len(recent_pivots) >= 3:
        pattern_type = "corrective"
        labels = ["A", "B", "C"]
        for i, (idx, price, ptype) in enumerate(recent_pivots[-3:]):
            wave_points.append(WavePoint(index=idx, price=price, wave_label=labels[i]))

        current_wave = "C"
        confidence = 40.0

        a_len = abs(wave_points[0].price - wave_points[1].price)
        b_len = abs(wave_points[1].price - wave_points[2].price)
        if a_len > 0:
            fib_ratios["b_retrace_a"] = round(b_len / a_len * 100, 1)

    # Recommendation
    if pattern_type == "impulse" and rules_valid:
        if current_wave in ["3", "beyond_3", "beyond_5"]:
            recommendation = f"Impulse pattern valid — Wave {current_wave}. Trend continuation likely."
        elif current_wave == "5":
            recommendation = "Wave 5 completing — Prepare for correction (A-B-C)."
        else:
            recommendation = "Impulse pattern detected — Follow trend."
    elif pattern_type == "impulse" and not rules_valid:
        recommendation = f"Impulse pattern but rules violated: {'; '.join(rule_violations)}. Pattern may be corrective."
    elif pattern_type == "corrective":
        recommendation = "Corrective pattern (A-B-C) — Wait for correction to complete before entering."
    else:
        recommendation = "Wave structure unclear — Use other analysis methods."

    return ElliottWaveAnalysis(
        pattern_type=pattern_type,
        current_wave=current_wave,
        wave_points=wave_points,
        fib_ratios=fib_ratios,
        rules_valid=rules_valid,
        rule_violations=rule_violations,
        next_target=next_target if pattern_type == "impulse" else None,
        invalidation_level=invalidation if pattern_type == "impulse" else None,
        confidence=round(confidence, 1),
        recommendation=recommendation,
    )
