"""
Advanced Technical Analysis Module.

Indikator teknikal lanjutan yang belum ada di preprocessor:
- Ichimoku Cloud (Tenkan/Kijun/Senkou/Chikou)
- ADX/DI+ / DI- (Directional Movement)
- Parabolic SAR
- Money Flow Index (MFI)
- VWAP (Volume Weighted Average Price)
- TRIX (Triple EMA)
- Elder Ray (Bull/Bear Power)
- Pivot Points (daily/weekly)
- Support/Resistance (swing highs/lows)
- Candlestick Pattern Recognition (Doji, Hammer, Engulfing, Shooting Star)
- Fibonacci Retracement levels

Adopted from: Technical Analysis of the Financial Markets (John Murphy),
Japanese Candlestick Charting Techniques (Steve Nison).
"""

import pandas as pd
import numpy as np


# =============================================================================
# 1. ICHIMOKU CLOUD
# =============================================================================

def add_ichimoku(df: pd.DataFrame, close_col: str = "Close",
                 high_col: str = "High", low_col: str = "Low",
                 prefix: str = "") -> pd.DataFrame:
    """
    Ichimoku Kinko Hyo — 5 components:
    - Tenkan-sen (Conversion Line): (9H + 9L) / 2
    - Kijun-sen (Base Line): (26H + 26L) / 2
    - Senkou Span A (Leading Span A): (Tenkan + Kijun) / 2, shifted +26
    - Senkou Span B (Leading Span B): (52H + 52L) / 2, shifted +26
    - Chikou Span (Lagging Span): Close shifted -26
    """
    high = df[high_col]
    low = df[low_col]
    close = df[close_col]

    tenkan = (high.rolling(9).max() + low.rolling(9).min()) / 2
    kijun = (high.rolling(26).max() + low.rolling(26).min()) / 2
    senkou_a = ((tenkan + kijun) / 2).shift(26)
    senkou_b = (high.rolling(52).max() + low.rolling(52).min()) / 2
    senkou_b = senkou_b.shift(26)

    df[f"{prefix}Ichimoku_Tenkan"] = tenkan
    df[f"{prefix}Ichimoku_Kijun"] = kijun
    df[f"{prefix}Ichimoku_SenkouA"] = senkou_a
    df[f"{prefix}Ichimoku_SenkouB"] = senkou_b

    # Cloud color: green (bullish) if A > B, red (bearish) if A < B
    df[f"{prefix}Ichimoku_Cloud"] = np.where(
        senkou_a > senkou_b, 1,  # Bullish cloud
        np.where(senkou_a < senkou_b, -1, 0)  # Bearish cloud
    )

    # Price vs cloud
    df[f"{prefix}Ichimoku_AboveCloud"] = np.where(
        close > senkou_a, 1,
        np.where(close < senkou_b, -1, 0)
    )

    return df


# =============================================================================
# 2. ADX / DI+ / DI-
# =============================================================================

def add_adx(df: pd.DataFrame, close_col: str = "Close",
            high_col: str = "High", low_col: str = "Low",
            prefix: str = "", period: int = 14) -> pd.DataFrame:
    """
    ADX (Average Directional Index) + DI+ / DI-.

    ADX > 25: strong trend
    ADX < 20: weak/no trend
    DI+ > DI-: bullish
    DI- > DI+: bearish
    """
    high = df[high_col]
    low = df[low_col]
    close = df[close_col]

    # True Range
    tr1 = high - low
    tr2 = (high - close.shift(1)).abs()
    tr3 = (low - close.shift(1)).abs()
    tr = pd.concat([tr1, tr2, tr3], axis=1).max(axis=1)
    atr = tr.ewm(span=period, adjust=False).mean()

    # Directional Movement
    up_move = high - high.shift(1)
    down_move = low.shift(1) - low

    plus_dm = np.where((up_move > down_move) & (up_move > 0), up_move, 0)
    minus_dm = np.where((down_move > up_move) & (down_move > 0), down_move, 0)

    plus_dm = pd.Series(plus_dm, index=df.index)
    minus_dm = pd.Series(minus_dm, index=df.index)

    plus_di = 100 * (plus_dm.ewm(span=period, adjust=False).mean() / atr.replace(0, np.nan))
    minus_di = 100 * (minus_dm.ewm(span=period, adjust=False).mean() / atr.replace(0, np.nan))

    dx = 100 * ((plus_di - minus_di).abs() / (plus_di + minus_di).replace(0, np.nan))
    adx = dx.ewm(span=period, adjust=False).mean()

    df[f"{prefix}ADX"] = adx
    df[f"{prefix}DI_Plus"] = plus_di
    df[f"{prefix}DI_Minus"] = minus_di
    df[f"{prefix}DI_Cross"] = np.where(plus_di > minus_di, 1, -1)  # 1=bull, -1=bear

    return df


# =============================================================================
# 3. PARABOLIC SAR
# =============================================================================

def add_parabolic_sar(df: pd.DataFrame, high_col: str = "High",
                      low_col: str = "Low", close_col: str = "Close",
                      prefix: str = "", af_start: float = 0.02,
                      af_max: float = 0.2) -> pd.DataFrame:
    """
    Parabolic SAR — trailing stop indicator.
    """
    high = df[high_col].values
    low = df[low_col].values
    close = df[close_col].values
    n = len(df)

    sar = np.zeros(n)
    af = af_start
    ep = 0.0  # Extreme point
    trend = 1  # 1=up, -1=down

    sar[0] = low[0]
    ep = high[0]

    for i in range(1, n):
        if trend == 1:
            sar[i] = sar[i-1] + af * (ep - sar[i-1])

            if low[i] < sar[i]:
                trend = -1
                sar[i] = ep
                ep = low[i]
                af = af_start
            else:
                if high[i] > ep:
                    ep = high[i]
                    af = min(af + af_start, af_max)

                sar[i] = min(sar[i], low[i-1], low[i-2] if i > 1 else low[i-1])
        else:
            sar[i] = sar[i-1] + af * (ep - sar[i-1])

            if high[i] > sar[i]:
                trend = 1
                sar[i] = ep
                ep = high[i]
                af = af_start
            else:
                if low[i] < ep:
                    ep = low[i]
                    af = min(af + af_start, af_max)

                sar[i] = max(sar[i], high[i-1], high[i-2] if i > 1 else high[i-1])

    df[f"{prefix}PSAR"] = sar
    df[f"{prefix}PSAR_Trend"] = np.where(close > sar, 1, -1)  # 1=bull, -1=bear

    return df


# =============================================================================
# 4. MONEY FLOW INDEX (MFI)
# =============================================================================

def add_mfi(df: pd.DataFrame, high_col: str = "High", low_col: str = "Low",
            close_col: str = "Close", volume_col: str = "Volume",
            prefix: str = "", period: int = 14) -> pd.DataFrame:
    """
    Money Flow Index — volume-weighted RSI.
    MFI > 80: overbought
    MFI < 20: oversold
    """
    typical_price = (df[high_col] + df[low_col] + df[close_col]) / 3
    money_flow = typical_price * df[volume_col]

    positive_flow = money_flow.where(typical_price > typical_price.shift(1), 0)
    negative_flow = money_flow.where(typical_price < typical_price.shift(1), 0)

    positive_mf = positive_flow.rolling(period).sum()
    negative_mf = negative_flow.rolling(period).sum()

    mfr = positive_mf / negative_mf.replace(0, np.nan)
    mfi = 100 - (100 / (1 + mfr))

    df[f"{prefix}MFI"] = mfi
    return df


# =============================================================================
# 5. VWAP (Volume Weighted Average Price)
# =============================================================================

def add_vwap(df: pd.DataFrame, high_col: str = "High", low_col: str = "Low",
             close_col: str = "Close", volume_col: str = "Volume",
             prefix: str = "", window: int = 20) -> pd.DataFrame:
    """
    Rolling VWAP — institutional benchmark price.
    """
    typical_price = (df[high_col] + df[low_col] + df[close_col]) / 3
    cum_vol = df[volume_col].rolling(window).sum()
    cum_tp_vol = (typical_price * df[volume_col]).rolling(window).sum()

    vwap = cum_tp_vol / cum_vol.replace(0, np.nan)
    df[f"{prefix}VWAP"] = vwap
    df[f"{prefix}VWAP_Diff"] = (df[close_col] - vwap) / vwap.replace(0, np.nan) * 100

    return df


# =============================================================================
# 6. TRIX (Triple EMA)
# =============================================================================

def add_trix(df: pd.DataFrame, close_col: str = "Close",
             prefix: str = "", period: int = 12) -> pd.DataFrame:
    """
    TRIX — triple-smoothed EMA rate of change.
    Crosses zero line = trend change.
    """
    ema1 = df[close_col].ewm(span=period, adjust=False).mean()
    ema2 = ema1.ewm(span=period, adjust=False).mean()
    ema3 = ema2.ewm(span=period, adjust=False).mean()

    trix = (ema3 - ema3.shift(1)) / ema3.shift(1).replace(0, np.nan) * 100
    trix_signal = trix.rolling(9).mean()

    df[f"{prefix}TRIX"] = trix
    df[f"{prefix}TRIX_Signal"] = trix_signal
    df[f"{prefix}TRIX_Cross"] = np.where(trix > trix_signal, 1, -1)

    return df


# =============================================================================
# 7. ELDER RAY (Bull/Bear Power)
# =============================================================================

def add_elder_ray(df: pd.DataFrame, high_col: str = "High", low_col: str = "Low",
                  close_col: str = "Close", prefix: str = "",
                  ema_period: int = 13) -> pd.DataFrame:
    """
    Elder Ray — Bull Power and Bear Power.
    Bull Power = High - EMA
    Bear Power = Low - EMA
    """
    ema = df[close_col].ewm(span=ema_period, adjust=False).mean()
    df[f"{prefix}Bull_Power"] = df[high_col] - ema
    df[f"{prefix}Bear_Power"] = df[low_col] - ema

    # Signal: both bull > 0 and bear < 0 = strong trend
    df[f"{prefix}Elder_Signal"] = np.where(
        (df[f"{prefix}Bull_Power"] > 0) & (df[f"{prefix}Bear_Power"] < 0), 1,
        np.where((df[f"{prefix}Bull_Power"] < 0) & (df[f"{prefix}Bear_Power"] > 0), -1, 0)
    )

    return df


# =============================================================================
# 8. PIVOT POINTS
# =============================================================================

def add_pivot_points(df: pd.DataFrame, high_col: str = "High",
                     low_col: str = "Low", close_col: str = "Close",
                     prefix: str = "") -> pd.DataFrame:
    """
    Daily Pivot Points — support/resistance levels.
    PP = (H + L + C) / 3
    R1 = 2*PP - L, S1 = 2*PP - H
    R2 = PP + (H - L), S2 = PP - (H - L)
    R3 = H + 2*(PP - L), S3 = L - 2*(H - PP)
    """
    h = df[high_col].shift(1)
    l = df[low_col].shift(1)
    c = df[close_col].shift(1)

    pp = (h + l + c) / 3
    df[f"{prefix}Pivot_PP"] = pp
    df[f"{prefix}Pivot_R1"] = 2 * pp - l
    df[f"{prefix}Pivot_S1"] = 2 * pp - h
    df[f"{prefix}Pivot_R2"] = pp + (h - l)
    df[f"{prefix}Pivot_S2"] = pp - (h - l)
    df[f"{prefix}Pivot_R3"] = h + 2 * (pp - l)
    df[f"{prefix}Pivot_S3"] = l - 2 * (h - pp)

    return df


# =============================================================================
# 9. SUPPORT / RESISTANCE (Swing Highs/Lows)
# =============================================================================

def add_support_resistance(df: pd.DataFrame, high_col: str = "High",
                           low_col: str = "Low", close_col: str = "Close",
                           prefix: str = "", lookback: int = 20) -> pd.DataFrame:
    """
    Support/Resistance levels based on rolling swing highs/lows.
    """
    df[f"{prefix}Resistance"] = df[high_col].rolling(lookback).max()
    df[f"{prefix}Support"] = df[low_col].rolling(lookback).min()

    # Distance from S/R as percentage
    df[f"{prefix}Dist_Resistance"] = (
        (df[f"{prefix}Resistance"] - df[close_col]) /
        df[close_col].replace(0, np.nan) * 100
    )
    df[f"{prefix}Dist_Support"] = (
        (df[close_col] - df[f"{prefix}Support"]) /
        df[close_col].replace(0, np.nan) * 100
    )

    return df


# =============================================================================
# 10. CANDLESTICK PATTERN RECOGNITION
# =============================================================================

def add_candlestick_patterns(df: pd.DataFrame, open_col: str = "Open",
                             high_col: str = "High", low_col: str = "Low",
                             close_col: str = "Close",
                             prefix: str = "") -> pd.DataFrame:
    """
    Recognize common candlestick patterns.
    Returns 1=bullish, -1=bearish, 0=none for each pattern.
    """
    o = df[open_col]
    h = df[high_col]
    l = df[low_col]
    c = df[close_col]

    body = (c - o).abs()
    body_pct = body / o.replace(0, np.nan) * 100
    upper_shadow = h - np.maximum(o, c)
    lower_shadow = np.minimum(o, c) - l
    (h - l).replace(0, np.nan)

    # Doji (indecision)
    df[f"{prefix}Doji"] = np.where(body_pct < 0.5, 1, 0)

    # Hammer (bullish reversal)
    df[f"{prefix}Hammer"] = np.where(
        (lower_shadow > 2 * body) & (upper_shadow < body * 0.5) & (c > o),
        1, 0
    )

    # Shooting Star (bearish reversal)
    df[f"{prefix}Shooting_Star"] = np.where(
        (upper_shadow > 2 * body) & (lower_shadow < body * 0.5) & (c < o),
        -1, 0
    )

    # Bullish Engulfing
    df[f"{prefix}Bull_Engulfing"] = np.where(
        (c.shift(1) < o.shift(1)) & (c > o) & (c > o.shift(1)) & (o < c.shift(1)),
        1, 0
    )

    # Bearish Engulfing
    df[f"{prefix}Bear_Engulfing"] = np.where(
        (c.shift(1) > o.shift(1)) & (c < o) & (c < o.shift(1)) & (o > c.shift(1)),
        -1, 0
    )

    # Morning Star (3-candle bullish reversal)
    df[f"{prefix}Morning_Star"] = np.where(
        (c.shift(2) < o.shift(2)) &  # Day 1: bearish
        (body_pct.shift(1) < 1) &    # Day 2: small body (star)
        (c > o) & (c > (o.shift(2) + c.shift(2)) / 2),  # Day 3: bullish, closes above midpoint
        1, 0
    )

    # Evening Star (3-candle bearish reversal)
    df[f"{prefix}Evening_Star"] = np.where(
        (c.shift(2) > o.shift(2)) &  # Day 1: bullish
        (body_pct.shift(1) < 1) &    # Day 2: small body (star)
        (c < o) & (c < (o.shift(2) + c.shift(2)) / 2),  # Day 3: bearish, closes below midpoint
        -1, 0
    )

    # Aggregate pattern signal
    patterns = ["Doji", "Hammer", "Shooting_Star", "Bull_Engulfing",
                "Bear_Engulfing", "Morning_Star", "Evening_Star"]
    df[f"{prefix}Candle_Signal"] = df[[f"{prefix}{p}" for p in patterns]].sum(axis=1)

    return df


# =============================================================================
# 11. FIBONACCI RETRACEMENT
# =============================================================================

def add_fibonacci_retracement(df: pd.DataFrame, high_col: str = "High",
                              low_col: str = "Low", close_col: str = "Close",
                              prefix: str = "", lookback: int = 50) -> pd.DataFrame:
    """
    Fibonacci retracement levels based on recent swing.
    Levels: 0%, 23.6%, 38.2%, 50%, 61.8%, 78.6%, 100%
    """
    swing_high = df[high_col].rolling(lookback).max()
    swing_low = df[low_col].rolling(lookback).min()
    diff = swing_high - swing_low

    df[f"{prefix}Fib_236"] = swing_high - 0.236 * diff
    df[f"{prefix}Fib_382"] = swing_high - 0.382 * diff
    df[f"{prefix}Fib_500"] = swing_high - 0.500 * diff
    df[f"{prefix}Fib_618"] = swing_high - 0.618 * diff
    df[f"{prefix}Fib_786"] = swing_high - 0.786 * diff

    # Current price position in Fibonacci range
    df[f"{prefix}Fib_Position"] = np.where(
        diff.replace(0, np.nan) > 0,
        (swing_high - df[close_col]) / diff.replace(0, np.nan),
        0.5
    )

    return df


# =============================================================================
# MASTER FUNCTION: Add all advanced technical indicators
# =============================================================================

def add_all_advanced_technical(
    df: pd.DataFrame,
    open_col: str = "Open",
    high_col: str = "High",
    low_col: str = "Low",
    close_col: str = "Close",
    volume_col: str = "Volume",
    prefix: str = "",
) -> pd.DataFrame:
    """
    Add all advanced technical indicators to a DataFrame.
    """
    df = add_ichimoku(df, close_col, high_col, low_col, prefix)
    df = add_adx(df, close_col, high_col, low_col, prefix)
    df = add_parabolic_sar(df, high_col, low_col, close_col, prefix)
    df = add_mfi(df, high_col, low_col, close_col, volume_col, prefix)
    df = add_vwap(df, high_col, low_col, close_col, volume_col, prefix)
    df = add_trix(df, close_col, prefix)
    df = add_elder_ray(df, high_col, low_col, close_col, prefix)
    df = add_pivot_points(df, high_col, low_col, close_col, prefix)
    df = add_support_resistance(df, high_col, low_col, close_col, prefix)
    df = add_candlestick_patterns(df, open_col, high_col, low_col, close_col, prefix)
    df = add_fibonacci_retracement(df, high_col, low_col, close_col, prefix)
    return df
