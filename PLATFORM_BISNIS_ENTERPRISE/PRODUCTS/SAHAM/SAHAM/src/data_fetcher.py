import yfinance as yf
import pandas as pd
import numpy as np
import requests
import time
import logging
from datetime import datetime, timedelta
from typing import Dict, Optional
from .config import TICKERS, FRED_API_KEY, FRED_SERIES, BLUE_CHIPS_ID, MODEL_CONFIG, SAHAM_IDX_100
from .database import (
    simpan_harga_harian, log_aktivitas, load_harga_harian,
    get_last_date_in_db, get_data_count_in_db,
    simpan_harga_intraday, load_harga_intraday, get_last_intraday_timestamp,
    simpan_fundamental, load_fundamental,
)
from .rate_limiter import get_yf_limiter, get_fred_limiter
from .data_validation import validate_ohlcv, validate_ticker_symbol

logger = logging.getLogger(__name__)

# In-memory cache for yfinance data — avoids redundant API calls within a session
_yf_cache: Dict[str, tuple] = {}  # key: f"{ticker}:{period}:{interval}" -> (timestamp, data)
_YF_CACHE_TTL = 300  # 5 minutes


def _get_cached(key: str) -> Optional[pd.DataFrame]:
    """Return cached DataFrame if still valid, else None."""
    if key in _yf_cache:
        ts, data = _yf_cache[key]
        if (time.time() - ts) < _YF_CACHE_TTL:
            logger.debug(f"[CACHE HIT] {key}")
            return data
        else:
            del _yf_cache[key]
    return None


def _set_cached(key: str, data: pd.DataFrame):
    _yf_cache[key] = (time.time(), data)


def _retry_yf_download(ticker: str, period: str, interval: str, max_retries: int = 3):
    """Download with rate limiting + retry and exponential backoff for Yahoo Finance."""
    limiter = get_yf_limiter()
    for attempt in range(max_retries):
        limiter.acquire()  # Rate limit before each request
        try:
            data = yf.download(ticker, period=period, interval=interval, progress=False, auto_adjust=True)
            if not data.empty:
                limiter.report_success()
                return data
            if attempt < max_retries - 1:
                wait = 2 ** attempt
                print(f"[RETRY] {ticker}: data kosong, retry {attempt + 1}/{max_retries} dalam {wait}s...")
                time.sleep(wait)
        except Exception as e:
            err_str = str(e).lower()
            if "429" in err_str or "too many requests" in err_str:
                limiter.report_429()
            if attempt < max_retries - 1:
                wait = 2 ** attempt
                print(f"[RETRY] {ticker}: error ({e}), retry {attempt + 1}/{max_retries} dalam {wait}s...")
                time.sleep(wait)
            else:
                raise
    return pd.DataFrame()


def fetch_yfinance_data(
    ticker: str, period: str = "2y", interval: str = "1d"
) -> pd.DataFrame:
    if not validate_ticker_symbol(ticker):
        print(f"[ERROR] Ticker symbol invalid: {ticker}")
        return pd.DataFrame()

    cache_key = f"{ticker}:{period}:{interval}"
    cached = _get_cached(cache_key)
    if cached is not None:
        return cached

    try:
        data = _retry_yf_download(ticker, period=period, interval=interval)
        if data.empty:
            print(f"[WARNING] Data kosong untuk ticker: {ticker}")
            return pd.DataFrame()

        if isinstance(data.columns, pd.MultiIndex):
            data.columns = data.columns.get_level_values(0)

        data.index = pd.to_datetime(data.index)
        data = data.dropna()

        # Validate data before caching/using it
        validation = validate_ohlcv(data, ticker=ticker)
        if not validation.is_valid:
            print(f"[ERROR] Data validation failed for {ticker}: {validation.issues}")
            return pd.DataFrame()

        _set_cached(cache_key, data)
        print(f"[OK] {ticker}: {len(data)} baris data ({data.index[0].date()} s/d {data.index[-1].date()})")
        return data
    except Exception as e:
        print(f"[ERROR] Gagal fetch {ticker}: {e}")
        return pd.DataFrame()


def fetch_yfinance_incremental(
    ticker: str,
    period: str = "5y",
    interval: str = "1d",
) -> pd.DataFrame:
    """
    Incremental fetch: load from database, then fetch only new data from internet.

    Strategy:
    1. Load all existing data from database
    2. Find last date in DB
    3. If DB has data and last date is recent (<=2 days ago), use DB only
    4. If DB has data but stale, fetch only from last_date+1 to today
    5. If DB empty, full fetch from internet
    6. Merge DB + new data, save new data to DB
    """
    # Step 1: Load from DB
    db_data = load_harga_harian(ticker)
    db_count = get_data_count_in_db(ticker)
    last_date = get_last_date_in_db(ticker)

    if db_count > 0 and last_date:
        last_date_dt = pd.to_datetime(last_date)
        today = pd.Timestamp.now().normalize()
        days_behind = (today - last_date_dt).days

        if days_behind <= 2:
            # DB is fresh — no need to fetch from internet
            print(f"[DB] {ticker}: {db_count} baris dari DB ({db_data.index[0].date()} s/d {db_data.index[-1].date()}) — up to date")
            return db_data

        # Step 2: Fetch only recent data (from last_date to today)
        # Use period that covers the gap (minimum 5d to ensure we get at least 1 trading day)
        gap_days = max(days_behind + 5, 5)
        fetch_period = f"{gap_days}d"

        print(f"[DB→NET] {ticker}: {db_count} baris di DB, last={last_date}, fetching {gap_days}d dari internet...")
        new_data = fetch_yfinance_data(ticker, period=fetch_period, interval=interval)

        if not new_data.empty:
            # Filter: only keep rows newer than DB's last date
            new_data = new_data[new_data.index > last_date_dt]

            if not new_data.empty:
                print(f"[NEW] {ticker}: {len(new_data)} baris baru ({new_data.index[0].date()} s/d {new_data.index[-1].date()})")
                simpan_harga_harian(ticker, new_data)
                # Merge DB + new data
                combined = pd.concat([db_data, new_data])
                combined = combined[~combined.index.duplicated(keep='last')]
                combined = combined.sort_index()
                return combined
            else:
                print(f"[DB] {ticker}: Tidak ada data baru — menggunakan DB ({db_count} baris)")
                return db_data
        else:
            print(f"[DB] {ticker}: Fetch gagal — menggunakan DB ({db_count} baris)")
            return db_data
    else:
        # Step 3: DB empty — full fetch from internet
        print(f"[NET] {ticker}: DB kosong, full fetch {period} dari internet...")
        data = fetch_yfinance_data(ticker, period=period, interval=interval)
        if not data.empty:
            simpan_harga_harian(ticker, data)
        return data


def _compute_cny_idr(cny_usd: pd.DataFrame, usd_idr: pd.DataFrame) -> pd.DataFrame:
    """Compute CNY/IDR cross rate from CNY=X and IDR=X (USD/IDR).

    Uses close-only ratio and sets OHLC equal to avoid artificial
    inconsistencies when dividing two separate OHLC series.
    """
    if cny_usd.empty or usd_idr.empty:
        return pd.DataFrame()
    ratio = cny_usd["Close"] / usd_idr["Close"]
    ratio = ratio.dropna()
    if ratio.empty:
        return pd.DataFrame()
    df = pd.DataFrame({
        "Open": ratio,
        "High": ratio,
        "Low": ratio,
        "Close": ratio,
        "Volume": 0.0,
    })
    return df


def fetch_all_market_data(period: str = "5y", use_cache: bool = True) -> Dict[str, pd.DataFrame]:
    """
    Fetch all market data with incremental caching.

    Args:
        period: Fallback period for full fetch if DB is empty
        use_cache: If True, use database cache + incremental fetch.
                   If False, always fetch full data from internet.
    """
    results = {}
    print("=" * 60)
    print("MENGAMBIL DATA PASAR GLOBAL")
    print("=" * 60)

    for name, ticker in TICKERS.items():
        if use_cache:
            data = fetch_yfinance_incremental(ticker, period=period)
        else:
            data = fetch_yfinance_data(ticker, period=period)
            if not data.empty:
                simpan_harga_harian(ticker, data)

        if not data.empty:
            results[name] = data

    # CNYIDR=X is not a valid Yahoo Finance ticker; derive CNY/IDR from CNY=X and IDR=X
    cny_idr_key = "CNY_IDR"
    cny_idr_ticker = TICKERS.get(cny_idr_key)
    if cny_idr_ticker and (cny_idr_key not in results or len(results.get(cny_idr_key, pd.DataFrame())) <= 2):
        print(f"\n[INFO] {cny_idr_ticker} hanya punya {len(results.get(cny_idr_key, pd.DataFrame()))} baris; menghitung CNY/IDR dari CNY=X / IDR=X")
        cny_usd = fetch_yfinance_data("CNY=X", period=period)
        usd_idr = fetch_yfinance_data("IDR=X", period=period)
        cny_idr = _compute_cny_idr(cny_usd, usd_idr)
        if not cny_idr.empty:
            simpan_harga_harian(cny_idr_ticker, cny_idr)
            results[cny_idr_key] = cny_idr
            print(f"[OK] {cny_idr_ticker}: {len(cny_idr)} baris data (computed)")

    total_rows = sum(len(df) for df in results.values())
    print(f"\n[SUMMARY] {len(results)} ticker, {total_rows:,} total baris data")
    log_aktivitas("FETCH_MARKET_DATA", f"{len(results)} ticker, {total_rows} baris, cache={'ON' if use_cache else 'OFF'}")
    return results


def fetch_fred_data(series_id: str, observation_start: Optional[str] = None) -> pd.DataFrame:
    if observation_start is None:
        observation_start = (datetime.now() - timedelta(days=730)).strftime("%Y-%m-%d")

    fred_limiter = get_fred_limiter()

    # Strategy 1: FRED API (if key available)
    if FRED_API_KEY:
        try:
            fred_limiter.acquire()
            url = "https://api.stlouisfed.org/fred/series/observations"
            params = {
                "series_id": series_id,
                "api_key": FRED_API_KEY,
                "file_type": "json",
                "observation_start": observation_start,
            }
            resp = requests.get(url, params=params, timeout=30)
            resp.raise_for_status()
            data = resp.json()
            observations = data.get("observations", [])

            df = pd.DataFrame(observations)
            if df.empty:
                return pd.DataFrame()

            df["date"] = pd.to_datetime(df["date"])
            df["value"] = pd.to_numeric(df["value"], errors="coerce")
            df = df[["date", "value"]].rename(columns={"date": "Date", "value": series_id})
            df = df.set_index("Date")
            df = df.replace(".", np.nan).dropna()

            fred_limiter.report_success()
            print(f"[OK] FRED API {series_id}: {len(df)} baris data")
            return df
        except Exception as e:
            print(f"[WARNING] FRED API failed for {series_id}: {e}, trying CSV fallback...")

    # Strategy 2: FRED CSV download (no API key needed)
    try:
        from io import StringIO
        fred_limiter.acquire()
        url = f"https://fred.stlouisfed.org/graph/fredgraph.csv?id={series_id}"
        resp = requests.get(url, timeout=30)
        resp.raise_for_status()

        df = pd.read_csv(StringIO(resp.text))
        if df.empty or "observation_date" not in df.columns:
            print(f"[SKIP] FRED CSV {series_id}: format tidak dikenali")
            return pd.DataFrame()

        df["observation_date"] = pd.to_datetime(df["observation_date"])
        df = df.rename(columns={"observation_date": "Date", series_id: "value"})
        df["value"] = pd.to_numeric(df["value"], errors="coerce")
        df = df.set_index("Date")
        df = df.replace(".", np.nan).dropna()

        # Filter to observation_start
        df = df[df.index >= pd.Timestamp(observation_start)]

        print(f"[OK] FRED CSV {series_id}: {len(df)} baris data (no API key)")
        return df
    except Exception as e:
        print(f"[ERROR] Gagal fetch FRED {series_id}: {e}")
        return pd.DataFrame()


def fetch_all_fred_data(observation_start: Optional[str] = None) -> Dict[str, pd.DataFrame]:
    results = {}
    print("\n" + "=" * 60)
    print("MENGAMBIL DATA MAKRO EKONOMI DARI FRED API")
    print("=" * 60)

    for name, series_id in FRED_SERIES.items():
        # Try loading from DB first
        db_data = load_harga_harian(series_id)
        db_count = get_data_count_in_db(series_id)
        last_date = get_last_date_in_db(series_id)

        if db_count > 0 and last_date:
            last_date_dt = pd.to_datetime(last_date)
            today = pd.Timestamp.now().normalize()
            days_behind = (today - last_date_dt).days
            if days_behind <= 30:  # FRED data is monthly/weekly, 30 days is fresh
                print(f"[DB] {series_id}: {db_count} baris dari DB — up to date")
                results[name] = db_data
                continue

        # Fetch from FRED (API or CSV fallback)
        data = fetch_fred_data(series_id, observation_start)
        if not data.empty:
            # Save to DB for future use
            simpan_harga_harian(series_id, data)
            results[name] = data

    log_aktivitas("FETCH_FRED_DATA", f"Berhasil fetch {len(results)} series dari {len(FRED_SERIES)}")
    return results


def fetch_stock_data(ticker: str, period: str = "2y") -> pd.DataFrame:
    return fetch_yfinance_data(ticker, period=period)


def get_current_price(ticker: str) -> Optional[float]:
    try:
        limiter = get_yf_limiter()
        limiter.acquire()
        data = _retry_yf_download(ticker, period="2d", interval="1d")
        if not data.empty:
            if isinstance(data.columns, pd.MultiIndex):
                data.columns = data.columns.get_level_values(0)
            return float(data["Close"].iloc[-1])
    except Exception as e:
        print(f"[ERROR] Gagal get current price {ticker}: {e}")
    return None


def fetch_all_data(period: str = None, use_cache: bool = True) -> dict:
    if period is None:
        period = MODEL_CONFIG.get("data_period", "5y")

    period_days = {"6mo": 183, "1y": 365, "2y": 730, "5y": 1825, "10y": 3650, "max": 7300}
    obs_start = (datetime.now() - timedelta(days=period_days.get(period, 1825))).strftime("%Y-%m-%d")

    market_data = fetch_all_market_data(period=period, use_cache=use_cache)
    fred_data = fetch_all_fred_data(observation_start=obs_start)

    # Fetch blue chip individual stock data
    blue_chip_data = fetch_blue_chips_data(period=period, use_cache=use_cache)

    # Fetch multi-country blue chips (US, JP, HK, SG)
    multi_country_data = {}
    try:
        multi_country_data = fetch_multi_country_blue_chips(period="2y", use_cache=use_cache)
    except Exception as e:
        print(f"[WARNING] Multi-country fetch failed: {e}")

    # Fetch fundamental data for all blue chips
    fundamental_data = {}
    try:
        from .config import ALL_BLUE_CHIPS
        fundamental_data = fetch_all_fundamentals(ALL_BLUE_CHIPS, use_cache=use_cache)
    except Exception as e:
        print(f"[WARNING] Fundamental fetch failed: {e}")

    return {
        "market": market_data,
        "fred": fred_data,
        "blue_chips": blue_chip_data,
        "multi_country": multi_country_data,
        "fundamental": fundamental_data,
    }


def fetch_blue_chips_data(period: str = "5y", use_cache: bool = True, use_idx100: bool = True) -> Dict[str, pd.DataFrame]:
    """
    Fetch all blue chip individual stock data with incremental caching.
    
    Stores to SQLite harga_harian table with ticker as key (e.g. 'BBCA.JK').
    
    Args:
        use_idx100: If True, fetch all 86 tickers from SAHAM_IDX_100.
                    If False, fetch only 8 tickers from BLUE_CHIPS_ID.
    """
    ticker_dict = SAHAM_IDX_100 if use_idx100 else BLUE_CHIPS_ID
    results = {}
    print("\n" + "=" * 60)
    print(f"MENGAMBIL DATA SAHAM INDONESIA ({len(ticker_dict)} ticker)")
    print("=" * 60)

    for ticker, name in ticker_dict.items():
        print(f"  [{ticker}] {name}...")
        if use_cache:
            data = fetch_yfinance_incremental(ticker, period=period)
        else:
            data = fetch_yfinance_data(ticker, period=period)
            if not data.empty:
                simpan_harga_harian(ticker, data)

        if not data.empty:
            results[ticker] = data
            print(f"    OK: {len(data)} baris ({data.index[0].date()} s/d {data.index[-1].date()})")
        else:
            print("    SKIP: data kosong")

    total_rows = sum(len(df) for df in results.values())
    print(f"\n[SUMMARY] {len(results)}/{len(ticker_dict)} tickers, {total_rows:,} total baris")
    log_aktivitas("FETCH_BLUE_CHIPS", f"{len(results)}/{len(ticker_dict)} tickers, {total_rows} baris")
    return results


def fetch_multi_country_blue_chips(period: str = "2y", use_cache: bool = True) -> Dict[str, pd.DataFrame]:
    """
    Fetch blue chip stocks from US, Japan, Hong Kong, Singapore.
    
    Stores to SQLite harga_harian table with ticker as key.
    """
    from .config import BLUE_CHIPS_US, BLUE_CHIPS_JP, BLUE_CHIPS_HK, BLUE_CHIPS_SG
    
    all_chips = {}
    all_chips.update(BLUE_CHIPS_US)
    all_chips.update(BLUE_CHIPS_JP)
    all_chips.update(BLUE_CHIPS_HK)
    all_chips.update(BLUE_CHIPS_SG)
    
    results = {}
    print("\n" + "=" * 60)
    print("MENGAMBIL DATA BLUE CHIP MULTI-COUNTRY (US, JP, HK, SG)")
    print("=" * 60)

    for ticker, name in all_chips.items():
        print(f"  [{ticker}] {name}...")
        try:
            if use_cache:
                data = fetch_yfinance_incremental(ticker, period=period)
            else:
                data = fetch_yfinance_data(ticker, period=period)
                if not data.empty:
                    simpan_harga_harian(ticker, data)

            if not data.empty:
                results[ticker] = data
                print(f"    OK: {len(data)} baris ({data.index[0].date()} s/d {data.index[-1].date()})")
            else:
                print("    SKIP: data kosong")
        except Exception as e:
            print(f"    ERROR: {e}")

    total_rows = sum(len(df) for df in results.values())
    print(f"\n[SUMMARY] {len(results)}/{len(all_chips)} multi-country blue chips, {total_rows:,} total baris")
    log_aktivitas("FETCH_MULTI_COUNTRY", f"{len(results)}/{len(all_chips)} tickers, {total_rows} baris")
    return results


# =============================================================================
# INTRADAY DATA (1m, 5m, 15m, 1h)
# =============================================================================

# yfinance interval limits: 1m=7d, 5m=60d, 15m=60d, 1h=730d, 1d=max
INTRADAY_LIMITS = {
    "1m": 7,    # 7 days max
    "2m": 60,   # 60 days max
    "5m": 60,   # 60 days max
    "15m": 60,  # 60 days max
    "30m": 60,  # 60 days max
    "60m": 730, # 730 days max
    "1h": 730,  # 730 days max
}


def fetch_intraday_data(
    ticker: str,
    interval: str = "5m",
    period: str = "1d",
    use_cache: bool = True,
) -> pd.DataFrame:
    """
    Fetch intraday price data for a ticker.
    
    Args:
        ticker: Stock ticker (e.g. "BBCA.JK", "AAPL")
        interval: "1m", "2m", "5m", "15m", "30m", "60m", "1h"
        period: "1d", "5d", "1mo", "3mo", "6mo", "1y", "2y"
        use_cache: If True, load from DB and fetch only new data
    
    Returns:
        DataFrame with Open, High, Low, Close, Volume indexed by datetime
    
    Note: yfinance limits:
        - 1m: max 7 days
        - 5m/15m/30m: max 60 days
        - 1h: max 730 days
    """
    # Validate interval
    if interval not in INTRADAY_LIMITS:
        print(f"[ERROR] Invalid interval: {interval}. Supported: {list(INTRADAY_LIMITS.keys())}")
        return pd.DataFrame()

    # Check period doesn't exceed yfinance limits
    max_days = INTRADAY_LIMITS[interval]
    period_days_map = {"1d": 1, "5d": 5, "1mo": 30, "3mo": 90, "6mo": 180, "1y": 365, "2y": 730}
    requested_days = period_days_map.get(period, 1)
    if requested_days > max_days:
        period = f"{max_days}d"
        print(f"[WARNING] {interval} max period is {max_days}d, adjusted to {period}")

    # Try loading from cache
    if use_cache:
        db_data = load_harga_intraday(ticker, interval=interval, limit=10000)
        last_ts = get_last_intraday_timestamp(ticker, interval=interval)

        if not db_data.empty and last_ts:
            last_dt = pd.to_datetime(last_ts)
            now = pd.Timestamp.now()
            # If data is fresh enough (within same trading day), use cache
            if (now - last_dt).total_seconds() < 3600:  # 1 hour freshness
                print(f"[DB] {ticker} {interval}: {len(db_data)} rows from DB (fresh)")
                return db_data

    # Fetch from yfinance
    try:
        data = _retry_yf_download(ticker, period=period, interval=interval)
        if data.empty:
            print(f"[WARNING] No intraday data for {ticker} ({interval})")
            return pd.DataFrame()

        if isinstance(data.columns, pd.MultiIndex):
            data.columns = data.columns.get_level_values(0)

        data.index = pd.to_datetime(data.index)
        data = data.dropna()

        # Save to DB
        simpan_harga_intraday(ticker, data, interval=interval)

        # Merge with cache if available
        if use_cache and not db_data.empty:
            combined = pd.concat([db_data, data])
            combined = combined[~combined.index.duplicated(keep='last')]
            combined = combined.sort_index()
            print(f"[OK] {ticker} {interval}: {len(data)} new rows, {len(combined)} total")
            return combined

        print(f"[OK] {ticker} {interval}: {len(data)} rows ({data.index[0]} s/d {data.index[-1]})")
        return data

    except Exception as e:
        print(f"[ERROR] Intraday fetch {ticker} {interval}: {e}")
        # Fall back to cache
        if use_cache and not db_data.empty:
            print(f"[DB] Using cached data: {len(db_data)} rows")
            return db_data
        return pd.DataFrame()


def fetch_intraday_batch(
    tickers: Dict[str, str],
    interval: str = "5m",
    period: str = "1d",
    use_cache: bool = True,
) -> Dict[str, pd.DataFrame]:
    """
    Fetch intraday data for multiple tickers.
    
    Args:
        tickers: Dict of {ticker: name}
        interval: "1m", "5m", "15m", "1h"
        period: "1d", "5d", "1mo"
    """
    results = {}
    print(f"\n{'='*60}")
    print(f"MENGAMBIL DATA INTRADAY ({interval}, {period})")
    print(f"{'='*60}")

    for ticker, name in tickers.items():
        print(f"  [{ticker}] {name}...")
        data = fetch_intraday_data(ticker, interval=interval, period=period, use_cache=use_cache)
        if not data.empty:
            results[ticker] = data
            print(f"    OK: {len(data)} rows")
        else:
            print("    SKIP: empty")

    total_rows = sum(len(df) for df in results.values())
    print(f"\n[SUMMARY] {len(results)}/{len(tickers)} tickers, {total_rows:,} total intraday rows")
    log_aktivitas("FETCH_INTRADAY", f"{len(results)}/{len(tickers)} tickers, {interval}, {total_rows} rows")
    return results


# =============================================================================
# FUNDAMENTAL DATA
# =============================================================================

def fetch_fundamental_data(ticker: str, use_cache: bool = True) -> Dict:
    """
    Fetch fundamental data for a ticker via yfinance.
    
    Returns dict with:
        pe_ratio, pbv_ratio, roe, eps, debt_to_equity, dividend_yield,
        market_cap, revenue, net_income, total_cash, total_debt,
        free_cash_flow, beta, profit_margin, current_ratio
    
    Cached in DB (1-day freshness).
    """
    # Try cache first
    if use_cache:
        cached = load_fundamental(ticker)
        if cached and cached.get("timestamp"):
            try:
                ts = pd.to_datetime(cached["timestamp"])
                if (pd.Timestamp.now() - ts).days < 1:
                    print(f"[DB] {ticker} fundamental: cached (fresh)")
                    return cached
            except Exception:
                pass

    # Fetch from yfinance
    try:
        limiter = get_yf_limiter()
        limiter.acquire()
        stock = yf.Ticker(ticker)
        info = stock.info or {}

        data = {
            "pe_ratio": info.get("trailingPE") or info.get("forwardPE"),
            "pbv_ratio": info.get("priceToBook"),
            "roe": info.get("returnOnEquity"),
            "eps": info.get("trailingEps"),
            "debt_to_equity": info.get("debtToEquity"),
            "dividend_yield": info.get("dividendYield"),
            "market_cap": info.get("marketCap"),
            "revenue": info.get("totalRevenue"),
            "net_income": info.get("netIncomeToCommon"),
            "total_cash": info.get("totalCash"),
            "total_debt": info.get("totalDebt"),
            "free_cash_flow": info.get("freeCashflow"),
            "beta": info.get("beta"),
            "profit_margin": info.get("profitMargins"),
            "current_ratio": info.get("currentRatio"),
        }

        # Clean None values
        data = {k: v for k, v in data.items() if v is not None}

        if data:
            simpan_fundamental(ticker, data)
            print(f"[OK] {ticker} fundamental: {len(data)} metrics")
        else:
            print(f"[WARNING] {ticker}: no fundamental data available")

        data["ticker"] = ticker
        return data

    except Exception as e:
        print(f"[ERROR] Fundamental fetch {ticker}: {e}")
        if use_cache:
            cached = load_fundamental(ticker)
            if cached:
                print(f"[DB] Using cached fundamental for {ticker}")
                return cached
        return {"ticker": ticker, "error": str(e)}


def fetch_all_fundamentals(tickers: Dict[str, str], use_cache: bool = True) -> Dict[str, Dict]:
    """
    Fetch fundamental data for multiple tickers.
    
    Args:
        tickers: Dict of {ticker: name}
    """
    results = {}
    print(f"\n{'='*60}")
    print("MENGAMBIL DATA FUNDAMENTAL")
    print(f"{'='*60}")

    for ticker, name in tickers.items():
        print(f"  [{ticker}] {name}...")
        data = fetch_fundamental_data(ticker, use_cache=use_cache)
        if data and "error" not in data:
            results[ticker] = data
        else:
            print(f"    SKIP: {data.get('error', 'no data')}")

    print(f"\n[SUMMARY] {len(results)}/{len(tickers)} tickers with fundamental data")
    log_aktivitas("FETCH_FUNDAMENTAL", f"{len(results)}/{len(tickers)} tickers")
    return results


# =============================================================================
# DATA SUFFICIENCY CHECKER
# =============================================================================

# Minimum data requirements per use case
SUFFICIENCY_REQUIREMENTS = {
    "walk_forward_cv": {
        "min_rows": 252,       # 1 year of trading days
        "recommended_rows": 504, # 2 years
        "min_period_days": 365,
        "description": "Walk-forward cross-validation (5-fold)",
    },
    "model_training": {
        "min_rows": 180,       # ~9 months
        "recommended_rows": 504, # 2 years
        "min_period_days": 270,
        "description": "ML model training (RF, XGB, LGBM)",
    },
    "backtesting": {
        "min_rows": 252,
        "recommended_rows": 756, # 3 years
        "min_period_days": 365,
        "description": "Strategy backtesting with meaningful statistics",
    },
    "intraday_simulation": {
        "min_rows": 78,        # ~1 day of 5m bars (6.5h * 12)
        "recommended_rows": 390, # ~5 days
        "min_period_days": 1,
        "description": "Intraday simulation (5m interval)",
    },
    "sentiment_analysis": {
        "min_rows": 30,        # 30 news articles
        "recommended_rows": 100,
        "min_period_days": 7,
        "description": "Sentiment analysis with sufficient sample",
    },
    "var_cvar": {
        "min_rows": 252,
        "recommended_rows": 504,
        "min_period_days": 365,
        "description": "VaR/CVaR calculation (needs 1yr returns)",
    },
    "sector_rotation": {
        "min_rows": 252,
        "recommended_rows": 504,
        "min_period_days": 365,
        "description": "Sector rotation analysis",
    },
}


def check_data_sufficiency(
    ticker: str,
    use_case: str = "model_training",
    interval: str = "1d",
) -> Dict:
    """
    Check if available data is sufficient for a specific use case.
    
    Args:
        ticker: Stock ticker
        use_case: One of SUFFICIENCY_REQUIREMENTS keys
        interval: "1d" for daily, "5m" for intraday
    
    Returns:
        {
            "ticker": str,
            "use_case": str,
            "sufficient": bool,
            "current_rows": int,
            "min_required": int,
            "recommended": int,
            "completeness_pct": float,
            "period_days": int,
            "min_period_days": int,
            "gap": str,  # Description of what's missing
            "recommendation": str,
        }
    """
    req = SUFFICIENCY_REQUIREMENTS.get(use_case)
    if not req:
        return {"error": f"Unknown use_case: {use_case}. Available: {list(SUFFICIENCY_REQUIREMENTS.keys())}"}

    # Get data count from DB
    if interval == "1d":
        rows = get_data_count_in_db(ticker)
        last_date = get_last_date_in_db(ticker)
        first_date = None

        # Get first date
        from .database import get_connection
        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute(
            "SELECT MIN(tanggal) FROM harga_harian WHERE ticker = ?",
            (ticker,),
        )
        result = cursor.fetchone()
        conn.close()
        if result and result[0]:
            first_date = str(result[0])
    else:
        # Intraday
        db_data = load_harga_intraday(ticker, interval=interval, limit=100000)
        rows = len(db_data)
        last_date = str(db_data.index[-1]) if not db_data.empty else None
        first_date = str(db_data.index[0]) if not db_data.empty else None

    # Calculate period coverage
    period_days = 0
    if first_date and last_date:
        try:
            fd = pd.to_datetime(first_date)
            ld = pd.to_datetime(last_date)
            period_days = (ld - fd).days
        except Exception:
            pass

    # Calculate completeness (trading days vs calendar days)
    completeness_pct = 0.0
    if period_days > 0:
        expected_trading_days = period_days * 5 / 7  # Rough: 5 trading days per 7 calendar
        completeness_pct = round(min(rows / expected_trading_days * 100, 100), 1) if expected_trading_days > 0 else 0

    # Determine sufficiency
    sufficient = rows >= req["min_rows"] and period_days >= req["min_period_days"]
    meets_recommended = rows >= req["recommended_rows"]

    # Generate gap description and recommendation
    gap = ""
    recommendation = ""

    if rows == 0:
        gap = f"No data available for {ticker}"
        recommendation = f"Run fetch_yfinance_incremental('{ticker}', period='5y') to fetch data"
    elif rows < req["min_rows"]:
        shortfall = req["min_rows"] - rows
        gap = f"Only {rows} rows (need {req['min_rows']} minimum for {req['description']})"
        recommendation = f"Fetch {shortfall} more rows. Try: fetch_yfinance_incremental('{ticker}', period='5y')"
    elif period_days < req["min_period_days"]:
        gap = f"Only {period_days} days of history (need {req['min_period_days']} for {req['description']})"
        recommendation = f"Need {req['min_period_days'] - period_days} more days of historical data"
    elif not meets_recommended:
        gap = f"Meets minimum ({rows} rows) but below recommended ({req['recommended_rows']} rows)"
        recommendation = "Sufficient for basic use, but more data will improve results"
    else:
        gap = "None — data is sufficient"
        recommendation = "Data is ready for " + req["description"]

    return {
        "ticker": ticker,
        "use_case": use_case,
        "use_case_description": req["description"],
        "sufficient": sufficient,
        "meets_recommended": meets_recommended,
        "current_rows": rows,
        "min_required": req["min_rows"],
        "recommended": req["recommended_rows"],
        "completeness_pct": completeness_pct,
        "period_days": period_days,
        "min_period_days": req["min_period_days"],
        "first_date": first_date,
        "last_date": last_date,
        "gap": gap,
        "recommendation": recommendation,
    }


def check_all_data_sufficiency(
    tickers: Dict[str, str],
    use_case: str = "model_training",
) -> Dict[str, Dict]:
    """
    Check data sufficiency for multiple tickers.
    
    Returns:
        Dict of {ticker: sufficiency_report}
    """
    results = {}
    print(f"\n{'='*60}")
    print(f"DATA SUFFICIENCY CHECK — {use_case}")
    print(f"{'='*60}")

    sufficient_count = 0
    for ticker in tickers:
        report = check_data_sufficiency(ticker, use_case=use_case)
        results[ticker] = report
        status = "✓" if report.get("sufficient") else "✗"
        rows = report.get("current_rows", 0)
        min_req = report.get("min_required", 0)
        print(f"  {status} {ticker}: {rows}/{min_req} rows — {report.get('gap', '')[:60]}")
        if report.get("sufficient"):
            sufficient_count += 1

    print(f"\n[SUMMARY] {sufficient_count}/{len(tickers)} tickers sufficient for {use_case}")
    return results


def get_data_inventory() -> Dict:
    """
    Get complete data inventory — what data exists, how much, and freshness.
    
    Returns overview of all data in the system:
        - Market indices: ticker, rows, date range, freshness
        - Blue chips: ticker, rows, date range, freshness
        - FRED: series, rows, date range
        - Intraday: ticker, interval, rows
        - Fundamental: ticker, metrics count, timestamp
    """
    from .database import get_connection

    conn = get_connection()
    cursor = conn.cursor()

    # Daily price data
    cursor.execute("""
        SELECT ticker, COUNT(*) as rows, MIN(tanggal) as earliest, MAX(tanggal) as latest
        FROM harga_harian GROUP BY ticker ORDER BY rows DESC
    """)
    daily = cursor.fetchall()

    # Intraday data
    cursor.execute("""
        SELECT ticker, interval, COUNT(*) as rows,
               MIN(timestamp) as earliest, MAX(timestamp) as latest
        FROM harga_intraday GROUP BY ticker, interval
    """)
    intraday = cursor.fetchall()

    # Fundamental data
    cursor.execute("""
        SELECT ticker, COUNT(*) as metrics, MAX(timestamp) as latest
        FROM fundamental_data GROUP BY ticker
    """)
    fundamental = cursor.fetchall()

    conn.close()

    # Build inventory
    inventory = {
        "daily": [
            {
                "ticker": r[0], "rows": r[1],
                "earliest": r[2], "latest": r[3],
                "freshness": "fresh" if r[3] and (datetime.now() - pd.to_datetime(r[3])).days <= 2 else "stale",
            }
            for r in daily
        ],
        "intraday": [
            {
                "ticker": r[0], "interval": r[1], "rows": r[2],
                "earliest": r[3], "latest": r[4],
            }
            for r in intraday
        ],
        "fundamental": [
            {
                "ticker": r[0], "metrics": r[1], "latest": r[2],
            }
            for r in fundamental
        ],
        "summary": {
            "daily_tickers": len(daily),
            "daily_total_rows": sum(r[1] for r in daily),
            "intraday_tickers": len(intraday),
            "intraday_total_rows": sum(r[2] for r in intraday),
            "fundamental_tickers": len(fundamental),
        },
    }

    return inventory
