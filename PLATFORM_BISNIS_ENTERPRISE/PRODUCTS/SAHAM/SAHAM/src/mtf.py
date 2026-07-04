"""
Multi-Timeframe Analysis (MTF) Module.

Konsep: "Multi-timeframe confluence" — ketika sinyal dari multiple timeframes
searah, probabilitas keberhasilan trade meningkat signifikan.

Implementasi terinspirasi dari:
- BOZ: Multi-timeframe confluence dengan verdict box
- Market Digest: Multi-timeframe ScoreCard (0-100 composite)
- TrendSpider: Multi-timeframe analysis (MTFA) — weekly indicators on daily chart
- Professional trading rule: "Trade in direction of higher timeframe trend"

Hierarki Timeframe:
  Weekly  → Higher timeframe (HTF) — trend utama, bobot tertinggi
  Daily   → Core timeframe — sinyal utama, bobot tinggi
  4H      → Mid timeframe — konfirmasi, bobot medium
  1H      → Lower timeframe (LTF) — entry timing, bobot terendah

Confluence Scoring:
  - Setiap timeframe menghasilkan sinyal BUY/SELL/HOLD + skor 0-100
  - Confluence score = weighted average berdasarkan hierarki
  - Jika semua timeframe searah → "Strong Confluence" → boost confidence
  - Jika timeframe bertentangan → "Mixed Signals" → reduce confidence

CATATAN: Timeframe 4H dan 1H di-resample dari data harian menggunakan
interpolasi. Hasilnya adalah approximation, bukan data intraday real.
Untuk akurasi MTF yang lebih baik pada sub-daily timeframes, gunakan
data intraday real (misal dari API broker atau data provider berbayar).
"""

import pandas as pd
import numpy as np
from dataclasses import dataclass, field
from typing import List, Optional, Tuple


@dataclass
class TimeframeSignal:
    """Signal dari satu timeframe."""
    timeframe: str
    signal: str  # "BUY", "SELL", "HOLD"
    score: float  # 0-100, 50 = neutral
    trend: str  # "Uptrend", "Downtrend", "Sideways"
    rsi: float = 50.0
    macd_signal: str = "Neutral"  # "Bullish", "Bearish", "Neutral"
    ma_alignment: str = "Neutral"  # "Bullish", "Bearish", "Neutral"
    bb_position: float = 0.5  # 0=lower band, 1=upper band
    volume_trend: str = "Normal"  # "Rising", "Falling", "Normal"
    detail: str = ""


@dataclass
class MTFResult:
    """Hasil multi-timeframe analysis."""
    confluence_score: float = 50.0  # 0-100
    confluence_signal: str = "HOLD"  # "BUY", "SELL", "HOLD"
    confluence_strength: str = "Neutral"  # "Strong", "Moderate", "Weak", "Mixed"
    timeframe_signals: List[TimeframeSignal] = field(default_factory=list)
    aligned_bullish: int = 0
    aligned_bearish: int = 0
    total_timeframes: int = 0
    trend_hierarchy: str = "Mixed"  # "Bullish", "Bearish", "Mixed"
    higher_tf_trend: str = "Sideways"  # Weekly trend
    entry_timing: str = "Wait"  # "Good", "OK", "Wait"
    summary: str = ""


# Timeframe weights — higher timeframe = more important
TIMEFRAME_WEIGHTS = {
    "1W": 0.35,   # Weekly — trend utama
    "1D": 0.30,   # Daily — core signal
    "4H": 0.20,   # 4H — konfirmasi
    "1H": 0.15,   # 1H — entry timing
}


def _resample_to_timeframe(df: pd.DataFrame, freq: str) -> pd.DataFrame:
    """Resample daily data to higher/lower timeframe.

    For sub-daily timeframes (4H, 1H) from daily data, we interpolate
    to generate synthetic intraday bars. This is a reasonable approximation
    since real intraday data is not available from yfinance daily feeds.
    """
    if df.empty:
        return df

    agg = {
        "Open": "first",
        "High": "max",
        "Low": "min",
        "Close": "last",
    }
    if "Volume" in df.columns:
        agg["Volume"] = "sum"

    if freq in ("4h", "1h"):
        # For sub-daily: interpolate daily data to create synthetic bars
        # This gives us more granular data points for shorter timeframes
        resampled = df.resample(freq).agg(agg)
        # Forward fill to fill gaps (weekends, holidays)
        resampled = resampled.ffill().dropna()
        if len(resampled) < 30:
            # Not enough data even after interpolation, use daily as-is
            return df
        return resampled
    else:
        resampled = df.resample(freq).agg(agg).dropna()
        return resampled


def _calc_timeframe_indicators(df: pd.DataFrame) -> dict:
    """Calculate key indicators for a single timeframe."""
    if df.empty or len(df) < 30:
        return {}

    close = df["Close"]
    df["High"]
    df["Low"]
    volume = df.get("Volume", pd.Series(0, index=df.index))

    # RSI (14)
    delta = close.diff()
    gain = delta.clip(lower=0).rolling(14).mean()
    loss = (-delta.clip(upper=0)).rolling(14).mean()
    rs = gain / loss.replace(0, np.nan)
    rsi = 100 - (100 / (1 + rs))
    rsi_val = rsi.iloc[-1] if not rsi.empty else 50.0

    # MA alignment: MA5, MA10, MA20
    ma5 = close.rolling(5).mean()
    ma10 = close.rolling(10).mean()
    ma20 = close.rolling(20).mean()
    ma50 = close.rolling(50).mean() if len(close) >= 50 else ma20

    if not ma5.empty and not ma20.empty:
        if ma5.iloc[-1] > ma10.iloc[-1] > ma20.iloc[-1]:
            ma_align = "Bullish"
        elif ma5.iloc[-1] < ma10.iloc[-1] < ma20.iloc[-1]:
            ma_align = "Bearish"
        else:
            ma_align = "Neutral"
    else:
        ma_align = "Neutral"

    # MACD
    ema12 = close.ewm(span=12, adjust=False).mean()
    ema26 = close.ewm(span=26, adjust=False).mean()
    macd_line = ema12 - ema26
    signal_line = macd_line.ewm(span=9, adjust=False).mean()
    if not macd_line.empty:
        if macd_line.iloc[-1] > signal_line.iloc[-1]:
            macd_sig = "Bullish"
        else:
            macd_sig = "Bearish"
    else:
        macd_sig = "Neutral"

    # Bollinger Bands position
    bb_mid = close.rolling(20).mean()
    bb_std = close.rolling(20).std()
    bb_upper = bb_mid + 2 * bb_std
    bb_lower = bb_mid - 2 * bb_std
    if not bb_upper.empty and (bb_upper.iloc[-1] - bb_lower.iloc[-1]) > 0:
        bb_pos = (close.iloc[-1] - bb_lower.iloc[-1]) / (bb_upper.iloc[-1] - bb_lower.iloc[-1])
        bb_pos = float(np.clip(bb_pos, 0, 1))
    else:
        bb_pos = 0.5

    # Volume trend
    if volume.sum() > 0:
        vol_ma = volume.rolling(20).mean()
        if not vol_ma.empty:
            vol_ratio = volume.iloc[-5:].mean() / vol_ma.iloc[-1] if vol_ma.iloc[-1] > 0 else 1.0
            if vol_ratio > 1.3:
                vol_trend = "Rising"
            elif vol_ratio < 0.7:
                vol_trend = "Falling"
            else:
                vol_trend = "Normal"
        else:
            vol_trend = "Normal"
    else:
        vol_trend = "Normal"

    # Trend determination
    if not ma50.empty and not ma20.empty:
        if close.iloc[-1] > ma20.iloc[-1] > ma50.iloc[-1]:
            trend = "Uptrend"
        elif close.iloc[-1] < ma20.iloc[-1] < ma50.iloc[-1]:
            trend = "Downtrend"
        else:
            trend = "Sideways"
    else:
        trend = "Sideways"

    return {
        "rsi": float(rsi_val),
        "ma_alignment": ma_align,
        "macd_signal": macd_sig,
        "bb_position": bb_pos,
        "volume_trend": vol_trend,
        "trend": trend,
        "close": float(close.iloc[-1]),
    }


def _score_timeframe(indicators: dict) -> Tuple[str, float, str]:
    """Score a single timeframe → (signal, score 0-100, detail)."""
    if not indicators:
        return "HOLD", 50.0, "Insufficient data"

    score = 50.0
    bullish_points = 0
    bearish_points = 0

    # RSI
    rsi = indicators["rsi"]
    if rsi < 30:
        score += 10
        bullish_points += 1
    elif rsi > 70:
        score -= 10
        bearish_points += 1
    elif rsi > 55:
        score += 3
        bullish_points += 1
    elif rsi < 45:
        score -= 3
        bearish_points += 1

    # MA alignment
    ma = indicators["ma_alignment"]
    if ma == "Bullish":
        score += 15
        bullish_points += 1
    elif ma == "Bearish":
        score -= 15
        bearish_points += 1

    # MACD
    macd = indicators["macd_signal"]
    if macd == "Bullish":
        score += 10
        bullish_points += 1
    elif macd == "Bearish":
        score -= 10
        bearish_points += 1

    # Bollinger Bands
    bb = indicators["bb_position"]
    if bb < 0.2:
        score += 5  # Near lower band — potential bounce
        bullish_points += 1
    elif bb > 0.8:
        score -= 5  # Near upper band — potential reversal
        bearish_points += 1

    # Volume
    vol = indicators["volume_trend"]
    if vol == "Rising" and bullish_points > bearish_points:
        score += 5  # Volume confirms bullish
    elif vol == "Rising" and bearish_points > bullish_points:
        score -= 5  # Volume confirms bearish

    score = float(np.clip(score, 0, 100))

    if score >= 65:
        signal = "BUY"
    elif score <= 35:
        signal = "SELL"
    else:
        signal = "HOLD"

    detail = f"RSI={rsi:.0f}, MA={ma}, MACD={macd}, BB={bb:.1%}, Vol={vol}"
    return signal, score, detail


def analyze_timeframe(df: pd.DataFrame, timeframe: str, freq: str) -> TimeframeSignal:
    """Analyze a single timeframe and return signal."""
    if timeframe == "1D":
        tf_df = df
    else:
        tf_df = _resample_to_timeframe(df, freq)

    indicators = _calc_timeframe_indicators(tf_df)

    if not indicators:
        return TimeframeSignal(
            timeframe=timeframe,
            signal="HOLD",
            score=50.0,
            trend="Sideways",
            detail="Insufficient data",
        )

    signal, score, detail = _score_timeframe(indicators)

    return TimeframeSignal(
        timeframe=timeframe,
        signal=signal,
        score=score,
        trend=indicators["trend"],
        rsi=indicators["rsi"],
        macd_signal=indicators["macd_signal"],
        ma_alignment=indicators["ma_alignment"],
        bb_position=indicators["bb_position"],
        volume_trend=indicators["volume_trend"],
        detail=detail,
    )


def run_mtf_analysis(
    daily_df: pd.DataFrame,
    timeframes: Optional[List[str]] = None,
) -> MTFResult:
    """
    Run multi-timeframe analysis.

    Args:
        daily_df: Daily OHLCV DataFrame with DatetimeIndex
        timeframes: List of timeframes to analyze (default: 1W, 1D, 4H, 1H)

    Returns:
        MTFResult with confluence score and per-timeframe signals
    """
    if timeframes is None:
        timeframes = ["1W", "1D", "4H", "1H"]

    freq_map = {"1W": "W", "1D": "D", "4H": "4h", "1H": "1h"}

    tf_signals = []
    for tf in timeframes:
        freq = freq_map.get(tf, "D")
        sig = analyze_timeframe(daily_df, tf, freq)
        tf_signals.append(sig)

    # Calculate weighted confluence score
    total_weight = 0
    weighted_score = 0
    aligned_bull = 0
    aligned_bear = 0

    for sig in tf_signals:
        w = TIMEFRAME_WEIGHTS.get(sig.timeframe, 0.25)
        total_weight += w
        weighted_score += sig.score * w

        if sig.signal == "BUY":
            aligned_bull += 1
        elif sig.signal == "SELL":
            aligned_bear += 1

    confluence_score = weighted_score / total_weight if total_weight > 0 else 50.0
    confluence_score = float(np.clip(confluence_score, 0, 100))

    # Confluence signal
    if confluence_score >= 65:
        confluence_signal = "BUY"
    elif confluence_score <= 35:
        confluence_signal = "SELL"
    else:
        confluence_signal = "HOLD"

    # Confluence strength
    total_tf = len(tf_signals)
    max_aligned = max(aligned_bull, aligned_bear)

    if max_aligned == total_tf:
        confluence_strength = "Strong"
    elif max_aligned >= total_tf * 0.75:
        confluence_strength = "Moderate"
    elif max_aligned >= total_tf * 0.5:
        confluence_strength = "Weak"
    else:
        confluence_strength = "Mixed"

    # Trend hierarchy
    weekly_sig = next((s for s in tf_signals if s.timeframe == "1W"), None)
    daily_sig = next((s for s in tf_signals if s.timeframe == "1D"), None)

    if weekly_sig and daily_sig:
        if weekly_sig.trend == "Uptrend" and daily_sig.trend == "Uptrend":
            trend_hierarchy = "Bullish"
        elif weekly_sig.trend == "Downtrend" and daily_sig.trend == "Downtrend":
            trend_hierarchy = "Bearish"
        else:
            trend_hierarchy = "Mixed"
    else:
        trend_hierarchy = "Mixed"

    higher_tf_trend = weekly_sig.trend if weekly_sig else "Sideways"

    # Entry timing: good when lower TF aligns with higher TF
    h1_sig = next((s for s in tf_signals if s.timeframe == "1H"), None)
    h4_sig = next((s for s in tf_signals if s.timeframe == "4H"), None)

    if h1_sig and h4_sig and daily_sig:
        if trend_hierarchy == "Bullish" and h1_sig.signal in ("BUY", "HOLD") and h4_sig.signal in ("BUY", "HOLD"):
            entry_timing = "Good"
        elif trend_hierarchy == "Bearish" and h1_sig.signal in ("SELL", "HOLD") and h4_sig.signal in ("SELL", "HOLD"):
            entry_timing = "Good"
        elif h1_sig.signal == "HOLD" or h4_sig.signal == "HOLD":
            entry_timing = "OK"
        else:
            entry_timing = "Wait"
    else:
        entry_timing = "OK"

    # Summary
    bull_str = f"{aligned_bull} bullish" if aligned_bull > 0 else "none bullish"
    bear_str = f"{aligned_bear} bearish" if aligned_bear > 0 else "none bearish"
    summary = (
        f"MTF Confluence: {confluence_strength} ({confluence_signal}), "
        f"Score: {confluence_score:.0f}/100, "
        f"{bull_str}, {bear_str} dari {total_tf} timeframes. "
        f"Higher TF: {higher_tf_trend}. Entry: {entry_timing}."
    )

    return MTFResult(
        confluence_score=confluence_score,
        confluence_signal=confluence_signal,
        confluence_strength=confluence_strength,
        timeframe_signals=tf_signals,
        aligned_bullish=aligned_bull,
        aligned_bearish=aligned_bear,
        total_timeframes=total_tf,
        trend_hierarchy=trend_hierarchy,
        higher_tf_trend=higher_tf_trend,
        entry_timing=entry_timing,
        summary=summary,
    )


def get_mtf_confidence_adjustment(mtf_result: MTFResult) -> Tuple[float, str]:
    """
    Get confidence adjustment based on MTF confluence.

    Returns:
        (adjustment_factor, reason)
        adjustment_factor: multiply with base confidence (e.g., 1.1 = boost 10%)
    """
    if mtf_result.confluence_strength == "Strong":
        if mtf_result.confluence_signal == "BUY":
            return 1.15, "Strong bullish confluence across all timeframes"
        elif mtf_result.confluence_signal == "SELL":
            return 1.15, "Strong bearish confluence across all timeframes"
        else:
            return 1.0, "Strong confluence but neutral direction"

    elif mtf_result.confluence_strength == "Moderate":
        if mtf_result.confluence_signal in ("BUY", "SELL"):
            return 1.08, f"Moderate {mtf_result.confluence_signal} confluence"
        else:
            return 1.0, "Moderate confluence, neutral direction"

    elif mtf_result.confluence_strength == "Mixed":
        return 0.85, "Mixed signals across timeframes — reduce confidence"

    else:  # Weak
        return 0.95, "Weak confluence — no strong directional bias"
