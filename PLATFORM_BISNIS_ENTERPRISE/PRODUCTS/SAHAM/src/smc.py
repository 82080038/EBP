"""
Smart Money Concepts (SMC) / ICT Methodology — Institutional Order Flow Detection.

Based on ICT (Inner Circle Trader) framework by Michael Huddleston:
- Order Blocks (OB): Last candle before institutional impulse
- Fair Value Gaps (FVG): Price imbalances from rapid moves
- Liquidity Sweeps: Stop hunts at swing highs/lows
- BOS (Break of Structure) & CHoCH (Change of Character): Market structure shifts
- Premium/Discount Arrays: Equilibrium-based entry zones
- Power of 3 (PO3): Accumulation → Manipulation → Distribution

Evolution of Wyckoff Method (see src/wyckoff.py) with modern vocabulary.
"""

import pandas as pd
from dataclasses import dataclass, field
from typing import List, Optional, Dict, Tuple


@dataclass
class OrderBlock:
    """Detected order block zone."""
    index: int
    type: str  # "bullish" or "bearish"
    open: float
    high: float
    low: float
    close: float
    volume: float
    displacement_strength: float  # How strong the subsequent move was
    mitigated: bool = False  # Has price returned to this OB?
    mitigation_index: Optional[int] = None


@dataclass
class FairValueGap:
    """Detected Fair Value Gap."""
    index: int  # Index of the middle candle (impulse candle)
    type: str  # "bullish" or "bearish"
    top: float  # Upper boundary of gap
    bottom: float  # Lower boundary of gap
    size: float  # Gap size as % of price
    filled: bool = False
    fill_index: Optional[int] = None


@dataclass
class LiquiditySweep:
    """Detected liquidity sweep / stop hunt."""
    index: int
    direction: str  # "bullish_sweep" (swept lows, then reversed up) or "bearish_sweep"
    level_swept: float  # The price level that was swept
    reversal_strength: float  # How strong the reversal was (0-1)
    sweep_range: float  # How far price went beyond the level


@dataclass
class StructureBreak:
    """BOS or CHoCH detection."""
    index: int
    type: str  # "BOS" or "CHoCH"
    direction: str  # "bullish" or "bearish"
    level_broken: float  # The swing high/low that was broken
    previous_structure: str  # "bullish" or "bearish" — prior structure


@dataclass
class SMCAnalysis:
    """Complete SMC analysis result."""
    signal: str = "HOLD"  # "BUY", "SELL", "HOLD"
    confidence: float = 50.0  # 0-100
    market_structure: str = "unclear"  # "bullish", "bearish", "ranging", "unclear"
    order_blocks: List[OrderBlock] = field(default_factory=list)
    fair_value_gaps: List[FairValueGap] = field(default_factory=list)
    liquidity_sweeps: List[LiquiditySweep] = field(default_factory=list)
    structure_breaks: List[StructureBreak] = field(default_factory=list)
    premium_discount: str = "equilibrium"  # "premium", "discount", "equilibrium"
    current_zone_price: float = 0.0
    swing_high: float = 0.0
    swing_low: float = 0.0
    equilibrium: float = 0.0
    po3_phase: str = ""  # "accumulation", "manipulation", "distribution", ""
    recommendation: str = ""
    details: Dict = field(default_factory=dict)


def detect_swing_highs_lows(
    df: pd.DataFrame,
    lookback: int = 20,
    high_col: str = "High",
    low_col: str = "Low",
) -> Tuple[List[Tuple[int, float]], List[Tuple[int, float]]]:
    """
    Detect swing highs and lows using fractal-based approach.

    Returns:
        (swing_highs, swing_lows) — each is list of (index, price)
    """
    swing_highs = []
    swing_lows = []
    n = len(df)

    for i in range(lookback, n - lookback):
        window_high = df[high_col].iloc[i - lookback:i + lookback + 1]
        window_low = df[low_col].iloc[i - lookback:i + lookback + 1]

        if df[high_col].iloc[i] == window_high.max():
            swing_highs.append((i, df[high_col].iloc[i]))
        if df[low_col].iloc[i] == window_low.min():
            swing_lows.append((i, df[low_col].iloc[i]))

    return swing_highs, swing_lows


def detect_order_blocks(
    df: pd.DataFrame,
    close_col: str = "Close",
    open_col: str = "Open",
    high_col: str = "High",
    low_col: str = "Low",
    volume_col: str = "Volume",
    min_displacement: float = 0.01,
    lookback: int = 3,
) -> List[OrderBlock]:
    """
    Detect Order Blocks — last opposite-color candle before a strong displacement move.

    Bullish OB: Last bearish candle before strong bullish move
    Bearish OB: Last bullish candle before strong bearish move

    Args:
        min_displacement: Minimum move size (as fraction of price) to qualify as displacement
        lookback: Number of candles after OB to check for displacement
    """
    order_blocks = []
    n = len(df)

    for i in range(lookback, n - lookback):
        # Check for bullish OB (bearish candle before bullish displacement)
        is_bearish = df[close_col].iloc[i] < df[open_col].iloc[i]

        if is_bearish:
            # Check if subsequent candles show strong bullish displacement
            displacement = (df[high_col].iloc[i + lookback] - df[close_col].iloc[i]) / df[close_col].iloc[i]
            if displacement >= min_displacement:
                # Check if this is the last bearish candle in a sequence
                is_last_bearish = True
                for j in range(i + 1, i + 1 + lookback):
                    if df[close_col].iloc[j] < df[open_col].iloc[j]:
                        is_last_bearish = False
                        break

                if is_last_bearish:
                    ob = OrderBlock(
                        index=i,
                        type="bullish",
                        open=df[open_col].iloc[i],
                        high=df[high_col].iloc[i],
                        low=df[low_col].iloc[i],
                        close=df[close_col].iloc[i],
                        volume=df[volume_col].iloc[i] if volume_col in df.columns else 0,
                        displacement_strength=displacement,
                    )
                    # Check mitigation
                    for j in range(i + lookback + 1, n):
                        if df[low_col].iloc[j] <= ob.high and df[low_col].iloc[j] >= ob.low:
                            ob.mitigated = True
                            ob.mitigation_index = j
                            break
                    order_blocks.append(ob)

        # Check for bearish OB (bullish candle before bearish displacement)
        is_bullish = df[close_col].iloc[i] > df[open_col].iloc[i]

        if is_bullish:
            displacement = (df[close_col].iloc[i] - df[low_col].iloc[i + lookback]) / df[close_col].iloc[i]
            if displacement >= min_displacement:
                is_last_bullish = True
                for j in range(i + 1, i + 1 + lookback):
                    if df[close_col].iloc[j] > df[open_col].iloc[j]:
                        is_last_bullish = False
                        break

                if is_last_bullish:
                    ob = OrderBlock(
                        index=i,
                        type="bearish",
                        open=df[open_col].iloc[i],
                        high=df[high_col].iloc[i],
                        low=df[low_col].iloc[i],
                        close=df[close_col].iloc[i],
                        volume=df[volume_col].iloc[i] if volume_col in df.columns else 0,
                        displacement_strength=displacement,
                    )
                    for j in range(i + lookback + 1, n):
                        if df[high_col].iloc[j] >= ob.low and df[high_col].iloc[j] <= ob.high:
                            ob.mitigated = True
                            ob.mitigation_index = j
                            break
                    order_blocks.append(ob)

    return order_blocks


def detect_fvg(
    df: pd.DataFrame,
    high_col: str = "High",
    low_col: str = "Low",
    min_gap_pct: float = 0.001,
) -> List[FairValueGap]:
    """
    Detect Fair Value Gaps — 3-candle pattern with price imbalance.

    Bullish FVG: Low of candle 3 > High of candle 1 (gap to the upside)
    Bearish FVG: High of candle 3 < Low of candle 1 (gap to the downside)
    """
    fvgs = []
    n = len(df)

    for i in range(1, n - 1):
        # Bullish FVG: candle i-1 high < candle i+1 low
        gap_top = df[low_col].iloc[i + 1]
        gap_bottom = df[high_col].iloc[i - 1]

        if gap_top > gap_bottom:
            gap_size = (gap_top - gap_bottom) / df[low_col].iloc[i]
            if gap_size >= min_gap_pct:
                fvg = FairValueGap(
                    index=i,
                    type="bullish",
                    top=gap_top,
                    bottom=gap_bottom,
                    size=gap_size,
                )
                # Check if filled
                for j in range(i + 2, n):
                    if df[low_col].iloc[j] <= gap_bottom:
                        fvg.filled = True
                        fvg.fill_index = j
                        break
                fvgs.append(fvg)

        # Bearish FVG: candle i-1 low > candle i+1 high
        gap_top_bear = df[low_col].iloc[i - 1]
        gap_bottom_bear = df[high_col].iloc[i + 1]

        if gap_top_bear > gap_bottom_bear:
            gap_size = (gap_top_bear - gap_bottom_bear) / df[high_col].iloc[i]
            if gap_size >= min_gap_pct:
                fvg = FairValueGap(
                    index=i,
                    type="bearish",
                    top=gap_top_bear,
                    bottom=gap_bottom_bear,
                    size=gap_size,
                )
                for j in range(i + 2, n):
                    if df[high_col].iloc[j] >= gap_top_bear:
                        fvg.filled = True
                        fvg.fill_index = j
                        break
                fvgs.append(fvg)

    return fvgs


def detect_liquidity_sweeps(
    df: pd.DataFrame,
    swing_highs: List[Tuple[int, float]],
    swing_lows: List[Tuple[int, float]],
    close_col: str = "Close",
    high_col: str = "High",
    low_col: str = "Low",
    min_sweep_range: float = 0.002,
    reversal_window: int = 3,
) -> List[LiquiditySweep]:
    """
    Detect liquidity sweeps — price briefly penetrates a swing high/low then reverses.

    Bullish sweep: Price goes below a swing low, then reverses upward
    Bearish sweep: Price goes above a swing high, then reverses downward
    """
    sweeps = []
    n = len(df)

    for idx, level in swing_lows:
        for j in range(idx + 1, min(idx + 20, n)):
            if df[low_col].iloc[j] < level:
                sweep_range = (level - df[low_col].iloc[j]) / level
                if sweep_range >= min_sweep_range:
                    # Check for reversal within reversal_window
                    reversed_up = False
                    reversal_strength = 0.0
                    for k in range(j + 1, min(j + 1 + reversal_window, n)):
                        if df[close_col].iloc[k] > level:
                            reversed_up = True
                            reversal_strength = (df[close_col].iloc[k] - df[low_col].iloc[j]) / (
                                df[high_col].iloc[k] - df[low_col].iloc[j] + 1e-10
                            )
                            reversal_strength = min(1.0, reversal_strength)
                            break

                    if reversed_up:
                        sweeps.append(LiquiditySweep(
                            index=j,
                            direction="bullish_sweep",
                            level_swept=level,
                            reversal_strength=reversal_strength,
                            sweep_range=sweep_range,
                        ))
                        break

    for idx, level in swing_highs:
        for j in range(idx + 1, min(idx + 20, n)):
            if df[high_col].iloc[j] > level:
                sweep_range = (df[high_col].iloc[j] - level) / level
                if sweep_range >= min_sweep_range:
                    reversed_down = False
                    reversal_strength = 0.0
                    for k in range(j + 1, min(j + 1 + reversal_window, n)):
                        if df[close_col].iloc[k] < level:
                            reversed_down = True
                            reversal_strength = (df[high_col].iloc[j] - df[close_col].iloc[k]) / (
                                df[high_col].iloc[k] - df[low_col].iloc[k] + 1e-10
                            )
                            reversal_strength = min(1.0, reversal_strength)
                            break

                    if reversed_down:
                        sweeps.append(LiquiditySweep(
                            index=j,
                            direction="bearish_sweep",
                            level_swept=level,
                            reversal_strength=reversal_strength,
                            sweep_range=sweep_range,
                        ))
                        break

    return sweeps


def detect_bos_choch(
    df: pd.DataFrame,
    swing_highs: List[Tuple[int, float]],
    swing_lows: List[Tuple[int, float]],
    close_col: str = "Close",
    high_col: str = "High",
    low_col: str = "Low",
) -> List[StructureBreak]:
    """
    Detect Break of Structure (BOS) and Change of Character (CHoCH).

    BOS: Price breaks a swing high (bullish BOS) or swing low (bearish BOS) in direction of trend
    CHoCH: Price breaks structure in opposite direction of current trend → potential reversal
    """
    breaks = []
    current_structure = "unclear"
    n = len(df)

    all_swings = [(idx, "high", level) for idx, level in swing_highs] + \
                 [(idx, "low", level) for idx, level in swing_lows]
    all_swings.sort(key=lambda x: x[0])

    for swing_idx, swing_type, level in all_swings:
        for j in range(swing_idx + 1, n):
            if swing_type == "high" and df[close_col].iloc[j] > level:
                direction = "bullish"
                break_type = "BOS" if current_structure == "bullish" else "CHoCH"
                breaks.append(StructureBreak(
                    index=j,
                    type=break_type,
                    direction=direction,
                    level_broken=level,
                    previous_structure=current_structure,
                ))
                current_structure = "bullish"
                break
            elif swing_type == "low" and df[close_col].iloc[j] < level:
                direction = "bearish"
                break_type = "BOS" if current_structure == "bearish" else "CHoCH"
                breaks.append(StructureBreak(
                    index=j,
                    type=break_type,
                    direction=direction,
                    level_broken=level,
                    previous_structure=current_structure,
                ))
                current_structure = "bearish"
                break

    return breaks


def detect_premium_discount(
    df: pd.DataFrame,
    swing_high: float,
    swing_low: float,
    close_col: str = "Close",
) -> Tuple[str, float, float]:
    """
    Determine if current price is in premium or discount zone.

    Returns:
        (zone, equilibrium, current_price)
    """
    equilibrium = (swing_high + swing_low) / 2
    current_price = df[close_col].iloc[-1]

    if current_price > equilibrium:
        zone = "premium"
    elif current_price < equilibrium:
        zone = "discount"
    else:
        zone = "equilibrium"

    return zone, equilibrium, current_price


def detect_po3_phase(
    df: pd.DataFrame,
    lookback: int = 10,
    close_col: str = "Close",
    high_col: str = "High",
    low_col: str = "Low",
    volume_col: str = "Volume",
) -> str:
    """
    Detect Power of 3 (AMD) phase — Accumulation, Manipulation, or Distribution.

    Simplified detection based on recent price action:
    - Accumulation: Narrow range, low volume, after downtrend
    - Manipulation: False breakout with quick reversal
    - Distribution: Strong directional move with volume
    """
    if len(df) < lookback * 3:
        return ""

    recent = df.tail(lookback * 3)
    first_third = recent.head(lookback)
    middle_third = recent.iloc[lookback:lookback * 2]
    last_third = recent.tail(lookback)

    # Range of first third (accumulation candidate)
    first_range = (first_third[high_col].max() - first_third[low_col].min()) / first_third[close_col].mean()
    # Range of middle third (manipulation candidate)
    middle_range = (middle_third[high_col].max() - middle_third[low_col].min()) / middle_third[close_col].mean()
    # Move of last third (distribution candidate)
    last_move = (last_third[close_col].iloc[-1] - last_third[close_col].iloc[0]) / last_third[close_col].iloc[0]

    vol_col = volume_col if volume_col in df.columns else close_col
    first_vol = first_third[vol_col].mean()
    last_vol = last_third[vol_col].mean()

    # Accumulation: narrow range, low volume
    if first_range < 0.02 and first_vol < last_vol * 0.7:
        # Check if middle has a sweep (manipulation)
        if middle_range > first_range * 1.5:
            # Check if last third has directional move (distribution)
            if abs(last_move) > 0.01:
                return "distribution"
            return "manipulation"
        return "accumulation"

    # Distribution: strong move with volume
    if abs(last_move) > 0.02 and last_vol > first_vol * 1.3:
        return "distribution"

    return "accumulation"


def run_smc_analysis(
    df: pd.DataFrame,
    close_col: str = "Close",
    open_col: str = "Open",
    high_col: str = "High",
    low_col: str = "Low",
    volume_col: str = "Volume",
    swing_lookback: int = 20,
) -> SMCAnalysis:
    """
    Run complete SMC analysis on OHLCV data.

    Combines all SMC components into a unified signal:
    - Order Blocks + FVG → entry zones
    - Liquidity Sweeps → reversal signals
    - BOS/CHoCH → market structure
    - Premium/Discount → entry timing
    - PO3 → daily phase

    Returns SMCAnalysis with signal (BUY/SELL/HOLD) and confidence (0-100).
    """
    if len(df) < swing_lookback * 3:
        return SMCAnalysis(details={"error": "Insufficient data for SMC analysis"})

    # Detect all components
    swing_highs, swing_lows = detect_swing_highs_lows(df, lookback=swing_lookback,
                                                       high_col=high_col, low_col=low_col)
    order_blocks = detect_order_blocks(df, close_col=close_col, open_col=open_col,
                                        high_col=high_col, low_col=low_col,
                                        volume_col=volume_col)
    fvgs = detect_fvg(df, high_col=high_col, low_col=low_col)
    sweeps = detect_liquidity_sweeps(df, swing_highs, swing_lows,
                                      close_col=close_col, high_col=high_col, low_col=low_col)
    structure_breaks = detect_bos_choch(df, swing_highs, swing_lows,
                                         close_col=close_col, high_col=high_col, low_col=low_col)

    # Determine market structure from latest break
    market_structure = "unclear"
    if structure_breaks:
        latest_break = structure_breaks[-1]
        market_structure = latest_break.direction

    # Premium/Discount
    swing_high = df[high_col].tail(swing_lookback).max()
    swing_low = df[low_col].tail(swing_lookback).min()
    zone, equilibrium, current_price = detect_premium_discount(
        df, swing_high, swing_low, close_col=close_col
    )

    # PO3 phase
    po3_phase = detect_po3_phase(df, close_col=close_col, high_col=high_col,
                                  low_col=low_col, volume_col=volume_col)

    # Build signal
    bullish_score = 0
    bearish_score = 0

    # Unmitigated OBs near current price
    recent_obs = [ob for ob in order_blocks if not ob.mitigated and ob.index > len(df) - 50]
    for ob in recent_obs:
        if ob.type == "bullish" and zone == "discount":
            bullish_score += 15
        elif ob.type == "bearish" and zone == "premium":
            bearish_score += 15

    # Unfilled FVGs
    recent_fvgs = [f for f in fvgs if not f.filled and f.index > len(df) - 50]
    for fvg in recent_fvgs:
        if fvg.type == "bullish":
            bullish_score += 10
        else:
            bearish_score += 10

    # Recent liquidity sweeps
    recent_sweeps = [s for s in sweeps if s.index > len(df) - 20]
    for sweep in recent_sweeps:
        if sweep.direction == "bullish_sweep":
            bullish_score += int(sweep.reversal_strength * 20)
        else:
            bearish_score += int(sweep.reversal_strength * 20)

    # Structure breaks
    if structure_breaks:
        latest = structure_breaks[-1]
        if latest.direction == "bullish":
            bullish_score += 15 if latest.type == "BOS" else 10
        else:
            bearish_score += 15 if latest.type == "BOS" else 10

    # PO3 phase
    if po3_phase == "distribution":
        last_move = (df[close_col].iloc[-1] - df[close_col].iloc[-10]) / df[close_col].iloc[-10]
        if last_move > 0:
            bullish_score += 10
        else:
            bearish_score += 10

    # Determine signal
    total = bullish_score + bearish_score
    if total == 0:
        signal = "HOLD"
        confidence = 50.0
    elif bullish_score > bearish_score:
        signal = "BUY"
        confidence = 50.0 + min(45.0, (bullish_score / max(total, 1)) * 50.0)
    else:
        signal = "SELL"
        confidence = 50.0 + min(45.0, (bearish_score / max(total, 1)) * 50.0)

    # Recommendation
    if signal == "BUY":
        rec = f"SMC: Bullish structure, {len(recent_obs)} unmitigated bullish OBs in discount zone"
    elif signal == "SELL":
        rec = f"SMC: Bearish structure, {len(recent_obs)} unmitigated bearish OBs in premium zone"
    else:
        rec = "SMC: No clear institutional footprint — wait for structure break"

    return SMCAnalysis(
        signal=signal,
        confidence=confidence,
        market_structure=market_structure,
        order_blocks=recent_obs,
        fair_value_gaps=recent_fvgs,
        liquidity_sweeps=recent_sweeps,
        structure_breaks=[b for b in structure_breaks if b.index > len(df) - 50],
        premium_discount=zone,
        current_zone_price=current_price,
        swing_high=swing_high,
        swing_low=swing_low,
        equilibrium=equilibrium,
        po3_phase=po3_phase,
        recommendation=rec,
        details={
            "total_bullish_score": bullish_score,
            "total_bearish_score": bearish_score,
            "unmitigated_obs": len(recent_obs),
            "unfilled_fvgs": len(recent_fvgs),
            "recent_sweeps": len(recent_sweeps),
            "recent_breaks": len([b for b in structure_breaks if b.index > len(df) - 50]),
        },
    )


def get_smc_confidence_adjustment(smc: SMCAnalysis) -> Tuple[float, str]:
    """
    Get confidence adjustment factor from SMC analysis for predictor integration.

    Returns:
        (adjustment_factor, reason)
    """
    if smc.signal == "BUY" and smc.confidence > 70:
        return 0.10, f"SMC bullish confluence ({smc.confidence:.0f})"
    elif smc.signal == "SELL" and smc.confidence > 70:
        return -0.10, f"SMC bearish confluence ({smc.confidence:.0f})"
    elif smc.signal == "BUY":
        return 0.05, f"SMC mildly bullish ({smc.confidence:.0f})"
    elif smc.signal == "SELL":
        return -0.05, f"SMC mildly bearish ({smc.confidence:.0f})"
    return 0.0, "SMC neutral"
