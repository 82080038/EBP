"""
Modul Indikator Teknikal Profesional
Mencakup: MACD, Stochastic, ATR, OBV, ADX, Williams %R, CCI, Parabolic SAR, Ichimoku
"""

import pandas as pd
import numpy as np


def calc_macd(df: pd.DataFrame, fast: int = 12, slow: int = 26, signal: int = 9) -> pd.DataFrame:
    ema_fast = df["Close"].ewm(span=fast, adjust=False).mean()
    ema_slow = df["Close"].ewm(span=slow, adjust=False).mean()
    macd_line = ema_fast - ema_slow
    signal_line = macd_line.ewm(span=signal, adjust=False).mean()
    histogram = macd_line - signal_line

    df["MACD"] = macd_line
    df["MACD_Signal"] = signal_line
    df["MACD_Hist"] = histogram
    return df


def calc_stochastic(df: pd.DataFrame, k_period: int = 14, d_period: int = 3) -> pd.DataFrame:
    low_min = df["Low"].rolling(window=k_period).min()
    high_max = df["High"].rolling(window=k_period).max()
    k_percent = 100 * ((df["Close"] - low_min) / (high_max - low_min))
    d_percent = k_percent.rolling(window=d_period).mean()

    df["Stoch_K"] = k_percent
    df["Stoch_D"] = d_percent
    return df


def calc_atr(df: pd.DataFrame, period: int = 14) -> pd.DataFrame:
    high_low = df["High"] - df["Low"]
    high_close = np.abs(df["High"] - df["Close"].shift())
    low_close = np.abs(df["Low"] - df["Close"].shift())
    tr = pd.concat([high_low, high_close, low_close], axis=1).max(axis=1)
    atr = tr.rolling(window=period).mean()

    df["ATR"] = atr
    df["ATR_Pct"] = (atr / df["Close"]) * 100
    return df


def calc_obv(df: pd.DataFrame) -> pd.DataFrame:
    obv = (np.sign(df["Close"].diff()) * df["Volume"]).fillna(0).cumsum()
    df["OBV"] = obv
    df["OBV_MA"] = obv.rolling(window=20).mean()
    return df


def calc_adx(df: pd.DataFrame, period: int = 14) -> pd.DataFrame:
    high_low = df["High"] - df["Low"]
    high_close = np.abs(df["High"] - df["Close"].shift())
    low_close = np.abs(df["Low"] - df["Close"].shift())
    tr = pd.concat([high_low, high_close, low_close], axis=1).max(axis=1)

    plus_dm = df["High"].diff()
    minus_dm = df["Low"].diff()
    plus_dm[plus_dm < 0] = 0
    minus_dm[minus_dm > 0] = 0
    minus_dm = np.abs(minus_dm)

    plus_di = 100 * (plus_dm.rolling(window=period).mean() / tr.rolling(window=period).mean())
    minus_di = 100 * (minus_dm.rolling(window=period).mean() / tr.rolling(window=period).mean())
    dx = 100 * np.abs(plus_di - minus_di) / (plus_di + minus_di)
    adx = dx.rolling(window=period).mean()

    df["ADX"] = adx
    df["Plus_DI"] = plus_di
    df["Minus_DI"] = minus_di
    return df


def calc_williams_r(df: pd.DataFrame, period: int = 14) -> pd.DataFrame:
    high_max = df["High"].rolling(window=period).max()
    low_min = df["Low"].rolling(window=period).min()
    wr = -100 * ((high_max - df["Close"]) / (high_max - low_min))
    df["Williams_R"] = wr
    return df


def calc_cci(df: pd.DataFrame, period: int = 20) -> pd.DataFrame:
    tp = (df["High"] + df["Low"] + df["Close"]) / 3
    sma_tp = tp.rolling(window=period).mean()
    mad = tp.rolling(window=period).apply(lambda x: np.abs(x - x.mean()).mean())
    cci = (tp - sma_tp) / (0.015 * mad)
    df["CCI"] = cci
    return df


def calc_parabolic_sar(df: pd.DataFrame, af_start: float = 0.02, af_max: float = 0.2) -> pd.DataFrame:
    high = df["High"].values
    low = df["Low"].values
    close = df["Close"].values
    n = len(df)

    sar = np.zeros(n)
    af = af_start
    ep = 0.0
    trend = 1  # 1 = uptrend, -1 = downtrend

    sar[0] = low[0]
    ep = high[0]

    for i in range(1, n):
        sar[i] = sar[i - 1] + af * (ep - sar[i - 1])

        if trend == 1:
            if low[i] < sar[i]:
                trend = -1
                sar[i] = ep
                ep = low[i]
                af = af_start
            else:
                if high[i] > ep:
                    ep = high[i]
                    af = min(af + af_start, af_max)
        else:
            if high[i] > sar[i]:
                trend = 1
                sar[i] = ep
                ep = high[i]
                af = af_start
            else:
                if low[i] < ep:
                    ep = low[i]
                    af = min(af + af_start, af_max)

    df["PSAR"] = sar
    df["PSAR_Trend"] = np.where(close > sar, 1, -1)
    return df


def calc_ichimoku(df: pd.DataFrame) -> pd.DataFrame:
    nine_high = df["High"].rolling(window=9).max()
    nine_low = df["Low"].rolling(window=9).min()
    df["Tenkan"] = (nine_high + nine_low) / 2

    period26_high = df["High"].rolling(window=26).max()
    period26_low = df["Low"].rolling(window=26).min()
    df["Kijun"] = (period26_high + period26_low) / 2

    df["Senkou_A"] = ((df["Tenkan"] + df["Kijun"]) / 2).shift(26)
    df["Senkou_B"] = ((df["High"].rolling(window=52).max() + df["Low"].rolling(window=52).min()) / 2).shift(26)

    df["Chikou"] = df["Close"].shift(-26)
    return df


def calc_vwap(df: pd.DataFrame) -> pd.DataFrame:
    typical_price = (df["High"] + df["Low"] + df["Close"]) / 3
    vwap = (typical_price * df["Volume"]).cumsum() / df["Volume"].cumsum()
    df["VWAP"] = vwap
    return df


def calc_money_flow_index(df: pd.DataFrame, period: int = 14) -> pd.DataFrame:
    typical_price = (df["High"] + df["Low"] + df["Close"]) / 3
    money_flow = typical_price * df["Volume"]
    positive_flow = money_flow.where(typical_price > typical_price.shift(), 0)
    negative_flow = money_flow.where(typical_price < typical_price.shift(), 0)
    positive_mf = positive_flow.rolling(window=period).sum()
    negative_mf = negative_flow.rolling(window=period).sum()
    mfr = positive_mf / negative_mf
    mfi = 100 - (100 / (1 + mfr))
    df["MFI"] = mfi
    return df


def add_all_indicators(df: pd.DataFrame) -> pd.DataFrame:
    df = df.copy()
    df = calc_macd(df)
    df = calc_stochastic(df)
    df = calc_atr(df)
    if "Volume" in df.columns and df["Volume"].sum() > 0:
        df = calc_obv(df)
        df = calc_vwap(df)
        df = calc_money_flow_index(df)
    df = calc_adx(df)
    df = calc_williams_r(df)
    df = calc_cci(df)
    df = calc_parabolic_sar(df)
    df = calc_ichimoku(df)
    return df


def get_indicator_signals(df: pd.DataFrame) -> dict:
    if df.empty or len(df) < 50:
        return {}

    latest = df.iloc[-1]
    signals = {}

    # MACD
    if "MACD" in df.columns and "MACD_Signal" in df.columns:
        if latest["MACD"] > latest["MACD_Signal"]:
            signals["MACD"] = {"signal": "Bullish", "value": f"{latest['MACD']:.2f}", "detail": "MACD > Signal"}
        else:
            signals["MACD"] = {"signal": "Bearish", "value": f"{latest['MACD']:.2f}", "detail": "MACD < Signal"}

    # Stochastic
    if "Stoch_K" in df.columns and "Stoch_D" in df.columns:
        k = latest["Stoch_K"]
        if k < 20:
            signals["Stochastic"] = {"signal": "Oversold", "value": f"{k:.1f}", "detail": "K < 20, potential buy"}
        elif k > 80:
            signals["Stochastic"] = {"signal": "Overbought", "value": f"{k:.1f}", "detail": "K > 80, potential sell"}
        else:
            signals["Stochastic"] = {"signal": "Neutral", "value": f"{k:.1f}", "detail": "20 < K < 80"}

    # ADX (trend strength)
    if "ADX" in df.columns:
        adx_val = latest["ADX"]
        if adx_val > 25:
            if latest.get("Plus_DI", 0) > latest.get("Minus_DI", 0):
                signals["ADX"] = {"signal": "Strong Uptrend", "value": f"{adx_val:.1f}", "detail": "ADX > 25, +DI > -DI"}
            else:
                signals["ADX"] = {"signal": "Strong Downtrend", "value": f"{adx_val:.1f}", "detail": "ADX > 25, -DI > +DI"}
        else:
            signals["ADX"] = {"signal": "Weak/No Trend", "value": f"{adx_val:.1f}", "detail": "ADX < 25"}

    # Williams %R
    if "Williams_R" in df.columns:
        wr = latest["Williams_R"]
        if wr > -20:
            signals["Williams_R"] = {"signal": "Overbought", "value": f"{wr:.1f}", "detail": "%R > -20"}
        elif wr < -80:
            signals["Williams_R"] = {"signal": "Oversold", "value": f"{wr:.1f}", "detail": "%R < -80"}
        else:
            signals["Williams_R"] = {"signal": "Neutral", "value": f"{wr:.1f}", "detail": "-80 < %R < -20"}

    # CCI
    if "CCI" in df.columns:
        cci = latest["CCI"]
        if cci > 100:
            signals["CCI"] = {"signal": "Overbought", "value": f"{cci:.1f}", "detail": "CCI > 100"}
        elif cci < -100:
            signals["CCI"] = {"signal": "Oversold", "value": f"{cci:.1f}", "detail": "CCI < -100"}
        else:
            signals["CCI"] = {"signal": "Neutral", "value": f"{cci:.1f}", "detail": "-100 < CCI < 100"}

    # MFI
    if "MFI" in df.columns:
        mfi = latest["MFI"]
        if mfi > 80:
            signals["MFI"] = {"signal": "Overbought", "value": f"{mfi:.1f}", "detail": "MFI > 80"}
        elif mfi < 20:
            signals["MFI"] = {"signal": "Oversold", "value": f"{mfi:.1f}", "detail": "MFI < 20"}
        else:
            signals["MFI"] = {"signal": "Neutral", "value": f"{mfi:.1f}", "detail": "20 < MFI < 80"}

    # ATR (volatility)
    if "ATR_Pct" in df.columns:
        atr_pct = latest["ATR_Pct"]
        if atr_pct > 3:
            signals["ATR"] = {"signal": "High Volatility", "value": f"{atr_pct:.2f}%", "detail": "ATR > 3% of price"}
        elif atr_pct < 1:
            signals["ATR"] = {"signal": "Low Volatility", "value": f"{atr_pct:.2f}%", "detail": "ATR < 1% of price"}
        else:
            signals["ATR"] = {"signal": "Normal", "value": f"{atr_pct:.2f}%", "detail": "1-3% of price"}

    # Parabolic SAR
    if "PSAR_Trend" in df.columns:
        psar_trend = latest["PSAR_Trend"]
        signals["PSAR"] = {
            "signal": "Bullish" if psar_trend == 1 else "Bearish",
            "value": f"{latest['PSAR']:.2f}",
            "detail": "Price above SAR" if psar_trend == 1 else "Price below SAR",
        }

    # Ichimoku
    if "Tenkan" in df.columns and "Kijun" in df.columns:
        if latest["Tenkan"] > latest["Kijun"]:
            signals["Ichimoku"] = {"signal": "Bullish", "value": "TK Cross", "detail": "Tenkan > Kijun"}
        else:
            signals["Ichimoku"] = {"signal": "Bearish", "value": "TK Cross", "detail": "Tenkan < Kijun"}

    return signals


def get_composite_signal(indicators: dict) -> tuple:
    if not indicators:
        return "NEUTRAL", 0, "No data"

    bullish = 0
    bearish = 0
    for sig in indicators.values():
        s = sig["signal"].lower()
        if "bullish" in s or "oversold" in s or "strong up" in s or "low vol" in s:
            bullish += 1
        elif "bearish" in s or "overbought" in s or "strong down" in s or "high vol" in s:
            bearish += 1

    total = len(indicators)
    score = (bullish - bearish) / total if total > 0 else 0

    if score > 0.3:
        return "BUY", score, f"{bullish} bullish vs {bearish} bearish dari {total} indikator"
    elif score < -0.3:
        return "SELL", score, f"{bullish} bullish vs {bearish} bearish dari {total} indikator"
    else:
        return "HOLD", score, f"{bullish} bullish vs {bearish} bearish dari {total} indikator"
