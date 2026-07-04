import pandas as pd
import numpy as np
from typing import Dict, Optional
from .config import TICKERS, MODEL_CONFIG, TARGET_TICKER
from .technical_advanced import add_all_advanced_technical


def calculate_rsi(series: pd.Series, period: int = 14) -> pd.Series:
    delta = series.diff()
    gain = delta.where(delta > 0, 0.0)
    loss = -delta.where(delta < 0, 0.0)

    avg_gain = gain.rolling(window=period, min_periods=period).mean()
    avg_loss = loss.rolling(window=period, min_periods=period).mean()

    rs = avg_gain / avg_loss
    rsi = 100 - (100 / (1 + rs))
    return rsi


def add_technical_indicators(df: pd.DataFrame, prefix: str = "") -> pd.DataFrame:
    df = df.copy()

    close_col = f"{prefix}Close" if prefix and f"{prefix}Close" in df.columns else "Close"

    ma_s = MODEL_CONFIG["ma_short"]
    ma_m = MODEL_CONFIG["ma_medium"]
    ma_l = MODEL_CONFIG["ma_long"]

    df[f"{prefix}MA{ma_s}"] = df[close_col].rolling(window=ma_s).mean()
    df[f"{prefix}MA{ma_m}"] = df[close_col].rolling(window=ma_m).mean()
    df[f"{prefix}MA{ma_l}"] = df[close_col].rolling(window=ma_l).mean()

    df[f"{prefix}RSI"] = calculate_rsi(df[close_col], MODEL_CONFIG["rsi_period"])

    df[f"{prefix}Returns"] = df[close_col].pct_change()
    df[f"{prefix}Volatility"] = df[f"{prefix}Returns"].rolling(window=20).std()

    df[f"{prefix}Volume_MA"] = df["Volume"].rolling(window=10).mean() if "Volume" in df.columns else 0

    bb_window = 20
    bb_std = df[close_col].rolling(window=bb_window).std()
    df[f"{prefix}BB_Upper"] = df[close_col].rolling(window=bb_window).mean() + 2 * bb_std
    df[f"{prefix}BB_Lower"] = df[close_col].rolling(window=bb_window).mean() - 2 * bb_std

    return df


def add_lag_features(df: pd.DataFrame, col: str, lag_days: list) -> pd.DataFrame:
    for lag in lag_days:
        df[f"{col}_lag{lag}"] = df[col].shift(lag)
    return df


def merge_market_data(market_data: Dict[str, pd.DataFrame]) -> pd.DataFrame:
    merged = None

    for name, data in market_data.items():
        if data.empty:
            continue

        TICKERS.get(name, name)
        col_prefix = f"{name}_"

        df = data.copy()
        df.columns = [f"{col_prefix}{c}" for c in df.columns]

        if merged is None:
            merged = df
        else:
            merged = merged.join(df, how="outer")

    if merged is not None:
        merged = merged.sort_index()
        merged = merged.ffill()

    return merged


def merge_fred_data(df: pd.DataFrame, fred_data: Dict[str, pd.DataFrame]) -> pd.DataFrame:
    """
    Merge FRED macro data with reporting lag correction.

    FRED data uses observation dates (e.g., May CPI dated 2026-05-01),
    but the data isn't released until later. Without shifting, this
    creates look-ahead bias.

    Reporting lags (approximate):
    - CPI: ~30 days (released mid-month for previous month)
    - Unemployment: ~7 days (released 1st Friday for previous month)
    - FedFunds: ~3 days (daily, posted with slight lag)
    - Treasury 10Y: ~1 day (daily, posted next day)
    """
    if df is None or df.empty:
        return df

    # Reporting lag in days for each FRED series
    fred_reporting_lag = {
        "CPI": 30,
        "UNEMPLOYMENT": 7,
        "FEDFUNDS": 3,
        "TREASURY_10Y": 1,
    }

    for name, data in fred_data.items():
        if data.empty:
            continue

        series_id = data.columns[0]
        lag_days = fred_reporting_lag.get(name, 7)  # Default 7 days

        # Shift FRED data forward by reporting lag
        # This ensures we only use data that was actually available
        shifted = data[series_id].copy()
        shifted.index = shifted.index + pd.Timedelta(days=lag_days)

        df[name] = shifted.reindex(df.index, method="ffill")

    return df


def prepare_features(
    market_data: Dict[str, pd.DataFrame],
    fred_data: Optional[Dict[str, pd.DataFrame]] = None,
    target_ticker: str = TARGET_TICKER,
) -> pd.DataFrame:
    print("\n" + "=" * 60)
    print("PREPROCESSING & FEATURE ENGINEERING")
    print("=" * 60)

    merged = merge_market_data(market_data)
    if merged is None or merged.empty:
        print("[ERROR] Tidak ada data market untuk diproses")
        return pd.DataFrame()

    target_name = None
    for name, ticker in TICKERS.items():
        if ticker == target_ticker:
            target_name = name
            break

    if target_name is None:
        target_name = "TARGET"
        target_col_prefix = f"{target_name}_"
    else:
        target_col_prefix = f"{target_name}_"

    target_close_col = f"{target_col_prefix}Close"
    if target_close_col not in merged.columns:
        print(f"[ERROR] Kolom target {target_close_col} tidak ditemukan")
        return pd.DataFrame()

    new_cols = {}

    for name in market_data.keys():
        prefix = f"{name}_"
        close_col = f"{prefix}Close"
        if close_col in merged.columns:
            new_cols[f"{prefix}Returns"] = merged[close_col].pct_change()
            new_cols[f"{prefix}MA{MODEL_CONFIG['ma_short']}"] = merged[close_col].rolling(
                window=MODEL_CONFIG["ma_short"]
            ).mean()
            new_cols[f"{prefix}MA{MODEL_CONFIG['ma_long']}"] = merged[close_col].rolling(
                window=MODEL_CONFIG["ma_long"]
            ).mean()

    new_cols["Target_Returns"] = merged[target_close_col].pct_change()
    new_cols["Target_RSI"] = calculate_rsi(merged[target_close_col], MODEL_CONFIG["rsi_period"])
    new_cols["Target_Volatility"] = new_cols["Target_Returns"].rolling(window=20).std()

    # === Indikator teknikal profesional untuk target ===
    target_high_col = f"{target_col_prefix}High"
    target_low_col = f"{target_col_prefix}Low"
    target_vol_col = f"{target_col_prefix}Volume"

    # MACD
    ema_fast = merged[target_close_col].ewm(span=12, adjust=False).mean()
    ema_slow = merged[target_close_col].ewm(span=26, adjust=False).mean()
    new_cols["Target_MACD"] = ema_fast - ema_slow
    new_cols["Target_MACD_Signal"] = new_cols["Target_MACD"].ewm(span=9, adjust=False).mean()
    new_cols["Target_MACD_Hist"] = new_cols["Target_MACD"] - new_cols["Target_MACD_Signal"]

    # Bollinger Bands
    bb_mean = merged[target_close_col].rolling(window=20).mean()
    bb_std = merged[target_close_col].rolling(window=20).std()
    new_cols["Target_BB_Upper"] = bb_mean + 2 * bb_std
    new_cols["Target_BB_Lower"] = bb_mean - 2 * bb_std
    new_cols["Target_BB_Width"] = (new_cols["Target_BB_Upper"] - new_cols["Target_BB_Lower"]) / bb_mean
    new_cols["Target_BB_Pct"] = (merged[target_close_col] - new_cols["Target_BB_Lower"]) / (
        new_cols["Target_BB_Upper"] - new_cols["Target_BB_Lower"]
    )

    # Stochastic
    if target_high_col in merged.columns and target_low_col in merged.columns:
        low_min = merged[target_low_col].rolling(window=14).min()
        high_max = merged[target_high_col].rolling(window=14).max()
        new_cols["Target_Stoch_K"] = 100 * ((merged[target_close_col] - low_min) / (high_max - low_min))
        new_cols["Target_Stoch_D"] = new_cols["Target_Stoch_K"].rolling(window=3).mean()

    # ATR
    if target_high_col in merged.columns and target_low_col in merged.columns:
        hl = merged[target_high_col] - merged[target_low_col]
        hc = np.abs(merged[target_high_col] - merged[target_close_col].shift())
        lc = np.abs(merged[target_low_col] - merged[target_close_col].shift())
        tr = pd.concat([hl, hc, lc], axis=1).max(axis=1)
        new_cols["Target_ATR"] = tr.rolling(window=14).mean()
        new_cols["Target_ATR_Pct"] = (new_cols["Target_ATR"] / merged[target_close_col]) * 100

    # Williams %R
    if target_high_col in merged.columns and target_low_col in merged.columns:
        hh = merged[target_high_col].rolling(window=14).max()
        ll = merged[target_low_col].rolling(window=14).min()
        new_cols["Target_Williams_R"] = -100 * ((hh - merged[target_close_col]) / (hh - ll))

    # CCI
    if target_high_col in merged.columns and target_low_col in merged.columns:
        tp = (merged[target_high_col] + merged[target_low_col] + merged[target_close_col]) / 3
        sma_tp = tp.rolling(window=20).mean()
        mad = tp.rolling(window=20).apply(lambda x: np.abs(x - x.mean()).mean())
        new_cols["Target_CCI"] = (tp - sma_tp) / (0.015 * mad)

    # OBV
    if target_vol_col in merged.columns:
        obv = (np.sign(merged[target_close_col].diff()) * merged[target_vol_col]).fillna(0).cumsum()
        new_cols["Target_OBV"] = obv
        new_cols["Target_OBV_MA"] = obv.rolling(window=20).mean()

    # === Advanced Technical Indicators ===
    target_open_col = f"{target_col_prefix}Open"
    if all(col in merged.columns for col in [target_open_col, target_high_col, target_low_col, target_close_col, target_vol_col]):
        target_df = merged[[target_open_col, target_high_col, target_low_col, target_close_col, target_vol_col]].copy()
        target_df.columns = ["Open", "High", "Low", "Close", "Volume"]
        target_df = add_all_advanced_technical(target_df, prefix="Target_")
        for col in target_df.columns:
            if col not in ["Open", "High", "Low", "Close", "Volume"]:
                new_cols[col] = target_df[col]

    # Inter-market spreads
    if "GOLD_Close" in merged.columns:
        new_cols["Gold_IHSG_Ratio"] = merged["GOLD_Close"] / merged[target_close_col]
    if "OIL_Close" in merged.columns:
        new_cols["Oil_IHSG_Ratio"] = merged["OIL_Close"] / merged[target_close_col]
    if "VIX_Close" in merged.columns:
        new_cols["VIX_Returns"] = merged["VIX_Close"].pct_change()
        new_cols["VIX_MA10"] = merged["VIX_Close"].rolling(window=10).mean()

    # Cross-market returns
    for name in market_data.keys():
        if name == target_name:
            continue
        prefix = f"{name}_"
        close_col = f"{prefix}Close"
        if close_col in merged.columns:
            new_cols[f"{prefix}Returns"] = merged[close_col].pct_change()
            new_cols[f"{prefix}Returns_lag1"] = new_cols[f"{prefix}Returns"].shift(1)
            new_cols[f"{prefix}Returns_lag2"] = new_cols[f"{prefix}Returns"].shift(2)

    for lag in MODEL_CONFIG["lag_days"]:
        new_cols[f"Target_Returns_lag{lag}"] = new_cols["Target_Returns"].shift(lag)

    # Rolling correlation with key markets
    if "S&P500_Returns" in new_cols:
        new_cols["Corr_IHSG_SP500"] = (
            new_cols["Target_Returns"].rolling(window=20).corr(new_cols["S&P500_Returns"])
        )
    if "STI_Returns" in new_cols:
        new_cols["Corr_IHSG_STI"] = (
            new_cols["Target_Returns"].rolling(window=20).corr(new_cols["STI_Returns"])
        )

    new_cols["Target_Next_Return"] = new_cols["Target_Returns"].shift(-1)
    new_cols["Target_Next_Close"] = merged[target_close_col].shift(-1)
    new_cols["Target_Direction"] = (new_cols["Target_Next_Return"] > 0).astype(int)

    # Concat all new columns at once to avoid fragmentation
    new_df = pd.DataFrame(new_cols, index=merged.index)
    merged = pd.concat([merged, new_df], axis=1)

    if fred_data:
        merged = merge_fred_data(merged, fred_data)

    merged = merged.dropna(subset=["Target_Next_Return"])
    merged = merged.ffill()
    # Drop columns that are entirely NaN (from indicators with long warmup)
    merged = merged.dropna(axis=1, how="all")
    # Fill remaining NaN with 0 instead of dropping rows (preserves data)
    merged = merged.fillna(0)

    feature_cols = [
        col
        for col in merged.columns
        if col not in ["Target_Next_Return", "Target_Next_Close"]
        and not col.startswith("Target_Next")
    ]

    print(f"[OK] Total fitur: {len(feature_cols)}")
    print(f"[OK] Total baris setelah preprocessing: {len(merged)}")

    return merged


def get_feature_columns(df: pd.DataFrame) -> list:
    exclude = ["Target_Next_Return", "Target_Next_Close", "Target_Direction"]
    return [c for c in df.columns if c not in exclude and not c.startswith("Target_Next")]


def train_test_split_time(df: pd.DataFrame, test_size: float = 0.2):
    split_idx = int(len(df) * (1 - test_size))
    train = df.iloc[:split_idx]
    test = df.iloc[split_idx:]
    return train, test


def persist_technical_indicators(ticker: str, df: pd.DataFrame, prefix: str = ""):
    """Persist the latest row of computed technical indicators to database.
    
    Call after add_technical_indicators() and add_all_advanced_technical()
    to save the most recent indicator snapshot for the ticker.
    
    Args:
        ticker: Stock ticker symbol (e.g. 'BBCA.JK')
        df: DataFrame with technical indicator columns (from add_technical_indicators)
        prefix: Column prefix used when adding indicators (e.g. 'IHSG_')
    """
    if df.empty:
        return

    from .database import simpan_technical_indicators

    p = prefix
    f"{p}Close" if p and f"{p}Close" in df.columns else "Close"

    latest = df.iloc[-1]
    date_str = latest.name.strftime("%Y-%m-%d") if hasattr(latest.name, "strftime") else str(latest.name)

    def _get(col_name):
        full = f"{p}{col_name}" if p else col_name
        val = latest.get(full, latest.get(col_name))
        return float(val) if pd.notna(val) else None

    data = {
        "sma_5": _get("MA5"),
        "sma_10": _get("MA10"),
        "sma_20": _get("MA20"),
        "sma_50": _get("MA50"),
        "sma_200": _get("MA200"),
        "ema_12": _get("EMA12"),
        "ema_26": _get("EMA26"),
        "rsi_14": _get("RSI"),
        "macd": _get("MACD"),
        "macd_signal": _get("MACD_Signal"),
        "macd_histogram": _get("MACD_Hist"),
        "bollinger_upper": _get("BB_Upper"),
        "bollinger_middle": _get("BB_Middle"),
        "bollinger_lower": _get("BB_Lower"),
        "stoch_k": _get("Stoch_K"),
        "stoch_d": _get("Stoch_D"),
        "williams_r": _get("Williams_R"),
        "atr": _get("ATR"),
        "adx": _get("ADX"),
        "cci": _get("CCI"),
    }

    simpan_technical_indicators(ticker, date_str, data)
