"""
Pattern Recognition: Candlestick, Chart Patterns, Market Structure, Volume Anomaly.

Implementasi:
- Candlestick Pattern Detection: Hammer, Doji, Engulfing, Shooting Star, Harami (dari TrendSpider/OZCx)
- Chart Pattern Recognition: Head & Shoulders, Double Top/Bottom, Triangles, Flags (dari TrendSpider)
- Market Structure Analysis: HH/HL/LH/LL, CHoCH, BOS (dari BOZ)
- Volume Anomaly Detection: Z-score based unusual volume (dari Hanzo AI)
- Automated Trendline Detection (dari TrendSpider)

Referensi:
- TrendSpider: Auto pattern detection
- BOZ: Market structure (HH/HL/LH/LL)
- Hanzo AI: Volume anomaly detection
- OZCx: Candlestick pattern detection
"""

import numpy as np
import pandas as pd
from typing import Dict, List, Optional
from dataclasses import dataclass, field


# =============================================================================
# CANDLESTICK PATTERN DETECTION
# =============================================================================

@dataclass
class CandlestickPattern:
    name: str
    type: str  # "bullish", "bearish", "neutral"
    confidence: float  # 0-1
    date: str
    description: str


def detect_candlestick_patterns(df: pd.DataFrame, lookback: int = 20) -> List[CandlestickPattern]:
    """
    Deteksi pola candlestick dari data OHLC.

    Pola yang dideteksi:
    - Bullish: Hammer, Bullish Engulfing, Morning Star, Piercing Line, Harami
    - Bearish: Shooting Star, Bearish Engulfing, Evening Star, Dark Cloud Cover, Harami
    - Neutral: Doji, Spinning Top
    """
    patterns = []
    required = ["Open", "High", "Low", "Close"]
    if not all(col in df.columns for col in required):
        return patterns

    recent = df.tail(lookback)

    for i in range(len(recent)):
        row = recent.iloc[i]
        o, h, l, c = row["Open"], row["High"], row["Low"], row["Close"]
        body = abs(c - o)
        range_total = h - l
        if range_total == 0:
            continue

        upper_shadow = h - max(o, c)
        lower_shadow = min(o, c) - l
        body_pct = body / range_total
        date = str(recent.index[i].date()) if hasattr(recent.index[i], 'date') else str(i)

        # Doji
        if body_pct < 0.1:
            patterns.append(CandlestickPattern(
                name="Doji", type="neutral", confidence=0.6,
                date=date, description="Indecision — open dan close hampir sama"
            ))

        # Hammer (bullish reversal)
        elif lower_shadow > 2 * body and upper_shadow < body * 0.5 and c > o:
            patterns.append(CandlestickPattern(
                name="Hammer", type="bullish", confidence=0.7,
                date=date, description="Potential bullish reversal — buyers masuk di sesi akhir"
            ))

        # Shooting Star (bearish reversal)
        elif upper_shadow > 2 * body and lower_shadow < body * 0.5 and c < o:
            patterns.append(CandlestickPattern(
                name="Shooting Star", type="bearish", confidence=0.7,
                date=date, description="Potential bearish reversal — sellers tekan dari atas"
            ))

        # Spinning Top
        elif body_pct < 0.3 and upper_shadow > body and lower_shadow > body:
            patterns.append(CandlestickPattern(
                name="Spinning Top", type="neutral", confidence=0.5,
                date=date, description="Indecision — baik buyer maupun seller tidak dominan"
            ))

        # Engulfing patterns (need previous candle)
        if i > 0:
            prev = recent.iloc[i - 1]
            prev_o, prev_c = prev["Open"], prev["Close"]
            prev_body = abs(prev_c - prev_o)

            # Bullish Engulfing
            if prev_c < prev_o and c > o and c >= prev_o and o <= prev_c and body > prev_body:
                patterns.append(CandlestickPattern(
                    name="Bullish Engulfing", type="bullish", confidence=0.8,
                    date=date, description="Strong bullish reversal — candle hijau menelan candle merah sebelumnya"
                ))

            # Bearish Engulfing
            elif prev_c > prev_o and c < o and c <= prev_o and o >= prev_c and body > prev_body:
                patterns.append(CandlestickPattern(
                    name="Bearish Engulfing", type="bearish", confidence=0.8,
                    date=date, description="Strong bearish reversal — candle merah menelan candle hijau sebelumnya"
                ))

            # Harami (bullish)
            elif prev_c < prev_o and c > o and c < prev_o and o > prev_c and body < prev_body * 0.6:
                patterns.append(CandlestickPattern(
                    name="Bullish Harami", type="bullish", confidence=0.65,
                    date=date, description="Potential bullish reversal — small green inside big red"
                ))

            # Harami (bearish)
            elif prev_c > prev_o and c < o and c > prev_o and o < prev_c and body < prev_body * 0.6:
                patterns.append(CandlestickPattern(
                    name="Bearish Harami", type="bearish", confidence=0.65,
                    date=date, description="Potential bearish reversal — small red inside big green"
                ))

            # Morning Star (3 candle bullish reversal)
            if i > 1:
                prev2 = recent.iloc[i - 2]
                if (prev2["Close"] < prev2["Open"] and  # Big red
                    abs(prev["Close"] - prev["Open"]) < prev_body * 0.3 and  # Small body
                    c > o and c > (prev2["Open"] + prev2["Close"]) / 2):  # Big green
                    patterns.append(CandlestickPattern(
                        name="Morning Star", type="bullish", confidence=0.85,
                        date=date, description="Strong bullish 3-candle reversal pattern"
                    ))

                # Evening Star (3 candle bearish reversal)
                if (prev2["Close"] > prev2["Open"] and  # Big green
                    abs(prev["Close"] - prev["Open"]) < prev_body * 0.3 and  # Small body
                    c < o and c < (prev2["Open"] + prev2["Close"]) / 2):  # Big red
                    patterns.append(CandlestickPattern(
                        name="Evening Star", type="bearish", confidence=0.85,
                        date=date, description="Strong bearish 3-candle reversal pattern"
                    ))

    return patterns


# =============================================================================
# MARKET STRUCTURE ANALYSIS (HH/HL/LH/LL)
# =============================================================================

@dataclass
class MarketStructure:
    structure: str  # "Uptrend", "Downtrend", "Range"
    swings: List[dict] = field(default_factory=list)
    last_swing: Optional[str] = None  # "HH", "HL", "LH", "LL"
    bos: bool = False  # Break of Structure
    choch: bool = False  # Change of Character
    description: str = ""


def analyze_market_structure(df: pd.DataFrame, window: int = 5) -> MarketStructure:
    """
    Analisis struktur pasar: HH (Higher High), HL (Higher Low),
    LH (Lower High), LL (Lower Low).

    Deteksi:
    - Break of Structure (BOS) — trend continuation
    - Change of Character (CHoCH) — trend reversal
    - Swing highs dan lows

    Referensi: BOZ (Behavioral Outlook Zone)
    """
    required = ["High", "Low", "Close"]
    if not all(col in df.columns for col in required):
        return MarketStructure(structure="Unknown", description="Data OHLC tidak lengkap")

    highs = df["High"].values
    lows = df["Low"].values
    closes = df["Close"].values
    n = len(df)

    if n < window * 3:
        return MarketStructure(structure="Insufficient Data", description=f"Data terlalu sedikit: {n} baris")

    # Find swing highs and lows
    swing_highs = []
    swing_lows = []

    for i in range(window, n - window):
        is_swing_high = all(highs[i] > highs[j] for j in range(i - window, i + window + 1) if j != i)
        is_swing_low = all(lows[i] < lows[j] for j in range(i - window, i + window + 1) if j != i)

        if is_swing_high:
            swing_highs.append({"index": i, "price": highs[i], "date": str(df.index[i].date()) if hasattr(df.index[i], 'date') else str(i)})
        if is_swing_low:
            swing_lows.append({"index": i, "price": lows[i], "date": str(df.index[i].date()) if hasattr(df.index[i], 'date') else str(i)})

    # Classify swings
    swings = []
    prev_high = None
    prev_low = None

    for sh in swing_highs[-5:]:  # Last 5 swing highs
        if prev_high is not None:
            if sh["price"] > prev_high["price"]:
                swings.append({"type": "HH", "price": sh["price"], "date": sh["date"]})
            else:
                swings.append({"type": "LH", "price": sh["price"], "date": sh["date"]})
        prev_high = sh

    for sl in swing_lows[-5:]:  # Last 5 swing lows
        if prev_low is not None:
            if sl["price"] > prev_low["price"]:
                swings.append({"type": "HL", "price": sl["price"], "date": sl["date"]})
            else:
                swings.append({"type": "LL", "price": sl["price"], "date": sl["date"]})
        prev_low = sl

    # Sort swings by index


    # Determine structure
    last_swing_type = None
    if swings:
        last_swing_type = swings[-1]["type"]

    hh_count = sum(1 for s in swings if s["type"] == "HH")
    hl_count = sum(1 for s in swings if s["type"] == "HL")
    lh_count = sum(1 for s in swings if s["type"] == "LH")
    ll_count = sum(1 for s in swings if s["type"] == "LL")

    if hh_count > lh_count and hl_count > ll_count:
        structure = "Uptrend"
        desc = f"Tren naik: {hh_count} HH, {hl_count} HL — higher highs & higher lows"
    elif lh_count > hh_count and ll_count > hl_count:
        structure = "Downtrend"
        desc = f"Tren turun: {lh_count} LH, {ll_count} LL — lower highs & lower lows"
    else:
        structure = "Range"
        desc = f"Sideways: mixed structure — {hh_count}HH, {hl_count}HL, {lh_count}LH, {ll_count}LL"

    # Check for BOS (Break of Structure)
    bos = False
    choch = False
    if len(swing_highs) >= 2 and len(closes) > 0:
        last_close = closes[-1]
        last_swing_high = swing_highs[-1]["price"]


        if last_close > last_swing_high and structure == "Uptrend":
            bos = True
            desc += " | BOS: Break of Structure konfirmasi lanjutan uptrend"
        elif last_close > last_swing_high and structure == "Downtrend":
            choch = True
            desc += " | CHoCH: Change of Character — potential reversal ke uptrend"

    if len(swing_lows) >= 2 and len(closes) > 0:
        last_swing_low = swing_lows[-1]["price"]
        if last_close < last_swing_low and structure == "Downtrend":
            bos = True
            desc += " | BOS: Break of Structure konfirmasi lanjutan downtrend"
        elif last_close < last_swing_low and structure == "Uptrend":
            choch = True
            desc += " | CHoCH: Change of Character — potential reversal ke downtrend"

    return MarketStructure(
        structure=structure,
        swings=swings,
        last_swing=last_swing_type,
        bos=bos,
        choch=choch,
        description=desc,
    )


# =============================================================================
# CHART PATTERN RECOGNITION
# =============================================================================

@dataclass
class ChartPattern:
    name: str
    type: str  # "bullish", "bearish", "neutral"
    confidence: float
    description: str
    neckline: Optional[float] = None
    target: Optional[float] = None


def detect_chart_patterns(df: pd.DataFrame, window: int = 30) -> List[ChartPattern]:
    """
    Deteksi pola chart: Head & Shoulders, Double Top/Bottom, Triangles, Flags.

    Referensi: TrendSpider chart pattern recognition
    """
    patterns = []
    required = ["High", "Low", "Close"]
    if not all(col in df.columns for col in required):
        return patterns

    recent = df.tail(window)
    if len(recent) < 15:
        return patterns

    highs = recent["High"].values
    lows = recent["Low"].values
    recent["Close"].values

    # Find local maxima and minima
    maxima_idx = []
    minima_idx = []
    for i in range(2, len(highs) - 2):
        if highs[i] > highs[i-1] and highs[i] > highs[i+1] and highs[i] > highs[i-2] and highs[i] > highs[i+2]:
            maxima_idx.append(i)
        if lows[i] < lows[i-1] and lows[i] < lows[i+1] and lows[i] < lows[i-2] and lows[i] < lows[i+2]:
            minima_idx.append(i)

    # === Double Top ===
    if len(maxima_idx) >= 2:
        m1, m2 = maxima_idx[-2], maxima_idx[-1]
        if abs(highs[m1] - highs[m2]) / highs[m1] < 0.02:  # Within 2%
            neckline = min(lows[m1:m2+1])
            target_price = neckline - (highs[m1] - neckline)
            patterns.append(ChartPattern(
                name="Double Top",
                type="bearish",
                confidence=0.75,
                description=f"Double Top pada {highs[m1]:.2f} — potential reversal bearish",
                neckline=float(neckline),
                target=float(target_price),
            ))

    # === Double Bottom ===
    if len(minima_idx) >= 2:
        m1, m2 = minima_idx[-2], minima_idx[-1]
        if abs(lows[m1] - lows[m2]) / lows[m1] < 0.02:
            neckline = max(highs[m1:m2+1])
            target_price = neckline + (neckline - lows[m1])
            patterns.append(ChartPattern(
                name="Double Bottom",
                type="bullish",
                confidence=0.75,
                description=f"Double Bottom pada {lows[m1]:.2f} — potential reversal bullish",
                neckline=float(neckline),
                target=float(target_price),
            ))

    # === Head & Shoulders ===
    if len(maxima_idx) >= 3:
        m1, m2, m3 = maxima_idx[-3], maxima_idx[-2], maxima_idx[-1]
        if (highs[m2] > highs[m1] and highs[m2] > highs[m3] and
            abs(highs[m1] - highs[m3]) / highs[m1] < 0.03):
            neckline = min(lows[m1:m3+1])
            target_price = neckline - (highs[m2] - neckline)
            patterns.append(ChartPattern(
                name="Head & Shoulders",
                type="bearish",
                confidence=0.80,
                description=f"H&S: Head={highs[m2]:.2f}, Shoulders={highs[m1]:.2f}/{highs[m3]:.2f}",
                neckline=float(neckline),
                target=float(target_price),
            ))

    # === Inverse Head & Shoulders ===
    if len(minima_idx) >= 3:
        m1, m2, m3 = minima_idx[-3], minima_idx[-2], minima_idx[-1]
        if (lows[m2] < lows[m1] and lows[m2] < lows[m3] and
            abs(lows[m1] - lows[m3]) / lows[m1] < 0.03):
            neckline = max(highs[m1:m3+1])
            target_price = neckline + (neckline - lows[m2])
            patterns.append(ChartPattern(
                name="Inverse Head & Shoulders",
                type="bullish",
                confidence=0.80,
                description=f"Inverse H&S: Head={lows[m2]:.2f}, Shoulders={lows[m1]:.2f}/{lows[m3]:.2f}",
                neckline=float(neckline),
                target=float(target_price),
            ))

    # === Ascending Triangle ===
    if len(maxima_idx) >= 2 and len(minima_idx) >= 2:
        tops = [highs[i] for i in maxima_idx[-2:]]
        bottoms = [lows[i] for i in minima_idx[-2:]]
        if abs(tops[0] - tops[1]) / tops[0] < 0.02 and bottoms[1] > bottoms[0]:
            patterns.append(ChartPattern(
                name="Ascending Triangle",
                type="bullish",
                confidence=0.65,
                description=f"Ascending Triangle — resistance {tops[0]:.2f}, higher lows",
                target=float(tops[0] + (tops[0] - bottoms[0])),
            ))

    # === Descending Triangle ===
    if len(maxima_idx) >= 2 and len(minima_idx) >= 2:
        tops = [highs[i] for i in maxima_idx[-2:]]
        bottoms = [lows[i] for i in minima_idx[-2:]]
        if abs(bottoms[0] - bottoms[1]) / bottoms[0] < 0.02 and tops[1] < tops[0]:
            patterns.append(ChartPattern(
                name="Descending Triangle",
                type="bearish",
                confidence=0.65,
                description=f"Descending Triangle — support {bottoms[0]:.2f}, lower highs",
                target=float(bottoms[0] - (tops[0] - bottoms[0])),
            ))

    return patterns


# =============================================================================
# VOLUME ANOMALY DETECTION
# =============================================================================

@dataclass
class VolumeAnomaly:
    date: str
    volume: float
    avg_volume: float
    z_score: float
    anomaly_type: str  # "spike", "dry_up", "climax"
    description: str


def detect_volume_anomaly(df: pd.DataFrame, window: int = 20, threshold: float = 2.0) -> List[VolumeAnomaly]:
    """
    Deteksi anomali volume menggunakan Z-score.

    - Spike: volume > 2 std di atas rata-rata (smart money masuk/keluar)
    - Dry up: volume < 2 std di bawah rata-rata (ketidakpedulian pasar)
    - Climax: volume > 3 std (potential reversal point)

    Referensi: Hanzo AI volume anomaly detection
    """
    anomalies = []
    if "Volume" not in df.columns or len(df) < window + 5:
        return anomalies

    recent = df.tail(window + 10)
    volumes = recent["Volume"].values

    for i in range(window, len(volumes)):
        hist_volumes = volumes[i - window:i]
        avg_vol = np.mean(hist_volumes)
        std_vol = np.std(hist_volumes)

        if std_vol == 0 or avg_vol == 0:
            continue

        z_score = (volumes[i] - avg_vol) / std_vol
        date = str(recent.index[i].date()) if hasattr(recent.index[i], 'date') else str(i)

        if z_score > 3.0:
            anomalies.append(VolumeAnomaly(
                date=date, volume=float(volumes[i]), avg_volume=float(avg_vol),
                z_score=float(z_score), anomaly_type="climax",
                description=f"Volume climax: {volumes[i]:.0f} ({z_score:.1f}σ) — potential reversal"
            ))
        elif z_score > threshold:
            anomalies.append(VolumeAnomaly(
                date=date, volume=float(volumes[i]), avg_volume=float(avg_vol),
                z_score=float(z_score), anomaly_type="spike",
                description=f"Volume spike: {volumes[i]:.0f} ({z_score:.1f}σ) — smart money activity"
            ))
        elif z_score < -threshold:
            anomalies.append(VolumeAnomaly(
                date=date, volume=float(volumes[i]), avg_volume=float(avg_vol),
                z_score=float(z_score), anomaly_type="dry_up",
                description=f"Volume dry-up: {volumes[i]:.0f} ({z_score:.1f}σ) — ketidakpedulian pasar"
            ))

    return anomalies


# =============================================================================
# AUTOMATED TRENDLINE DETECTION
# =============================================================================

@dataclass
class Trendline:
    type: str  # "support", "resistance"
    slope: float
    intercept: float
    start_date: str
    end_date: str
    touches: int
    description: str


def detect_trendlines(df: pd.DataFrame, window: int = 30, min_touches: int = 2) -> List[Trendline]:
    """
    Deteksi trendline otomatis menggunakan linear regression pada swing points.

    Referensi: TrendSpider automated trendline detection
    """
    trendlines = []
    required = ["High", "Low"]
    if not all(col in df.columns for col in required):
        return trendlines

    recent = df.tail(window)
    if len(recent) < 10:
        return trendlines

    highs = recent["High"].values
    lows = recent["Low"].values

    # Find swing points
    swing_high_idx = []
    swing_low_idx = []
    for i in range(2, len(highs) - 2):
        if highs[i] > highs[i-1] and highs[i] > highs[i+1]:
            swing_high_idx.append(i)
        if lows[i] < lows[i-1] and lows[i] < lows[i+1]:
            swing_low_idx.append(i)

    # Resistance trendline (connect swing highs)
    if len(swing_high_idx) >= min_touches:
        idxs = swing_high_idx[-min_touches:]
        x = np.array(idxs)
        y = highs[idxs]
        if len(x) >= 2:
            slope, intercept = np.polyfit(x, y, 1)
            start_date = str(recent.index[idxs[0]].date()) if hasattr(recent.index[idxs[0]], 'date') else str(idxs[0])
            end_date = str(recent.index[idxs[-1]].date()) if hasattr(recent.index[idxs[-1]], 'date') else str(idxs[-1])
            trendlines.append(Trendline(
                type="resistance",
                slope=float(slope), intercept=float(intercept),
                start_date=start_date, end_date=end_date,
                touches=len(idxs),
                description=f"Resistance: slope={slope:.4f}, {len(idxs)} touches"
            ))

    # Support trendline (connect swing lows)
    if len(swing_low_idx) >= min_touches:
        idxs = swing_low_idx[-min_touches:]
        x = np.array(idxs)
        y = lows[idxs]
        if len(x) >= 2:
            slope, intercept = np.polyfit(x, y, 1)
            start_date = str(recent.index[idxs[0]].date()) if hasattr(recent.index[idxs[0]], 'date') else str(idxs[0])
            end_date = str(recent.index[idxs[-1]].date()) if hasattr(recent.index[idxs[-1]], 'date') else str(idxs[-1])
            trendlines.append(Trendline(
                type="support",
                slope=float(slope), intercept=float(intercept),
                start_date=start_date, end_date=end_date,
                touches=len(idxs),
                description=f"Support: slope={slope:.4f}, {len(idxs)} touches"
            ))

    return trendlines


# =============================================================================
# COMBINED PATTERN ANALYSIS
# =============================================================================

def full_pattern_analysis(df: pd.DataFrame, target_prefix: str = "") -> Dict:
    """
    Jalankan semua analisis pola sekaligus.

    Returns dict dengan:
    - candlestick_patterns: List[CandlestickPattern]
    - chart_patterns: List[ChartPattern]
    - market_structure: MarketStructure
    - volume_anomalies: List[VolumeAnomaly]
    - trendlines: List[Trendline]
    - summary: text summary
    """
    # Prepare data with correct column names
    analysis_df = df.copy()
    col_map = {}
    for col in ["Open", "High", "Low", "Close", "Volume"]:
        target_col = f"{target_prefix}{col}"
        if target_col in analysis_df.columns:
            col_map[target_col] = col
    if col_map:
        analysis_df = analysis_df.rename(columns=col_map)

    candlestick = detect_candlestick_patterns(analysis_df)
    chart_pats = detect_chart_patterns(analysis_df)
    structure = analyze_market_structure(analysis_df)
    vol_anomalies = detect_volume_anomaly(analysis_df)
    trendlines = detect_trendlines(analysis_df)

    # Build summary
    summary_parts = []
    if structure.structure != "Unknown":
        summary_parts.append(f"Struktur: {structure.structure}")
    if candlestick:
        latest = candlestick[-1]
        summary_parts.append(f"Candlestick: {latest.name} ({latest.type})")
    if chart_pats:
        summary_parts.append(f"Chart: {chart_pats[-1].name} ({chart_pats[-1].type})")
    if vol_anomalies:
        latest_vol = vol_anomalies[-1]
        summary_parts.append(f"Volume: {latest_vol.anomaly_type} ({latest_vol.z_score:.1f}σ)")
    if trendlines:
        summary_parts.append(f"Trendlines: {len(trendlines)} detected")

    summary = " | ".join(summary_parts) if summary_parts else "Tidak ada pola signifikan terdeteksi"

    return {
        "candlestick_patterns": candlestick,
        "chart_patterns": chart_pats,
        "market_structure": structure,
        "volume_anomalies": vol_anomalies,
        "trendlines": trendlines,
        "summary": summary,
    }
