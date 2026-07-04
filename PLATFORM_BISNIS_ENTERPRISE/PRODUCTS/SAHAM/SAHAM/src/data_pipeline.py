"""
Data Quality Monitoring & Multi-Source Data Pipeline.

Implementasi:
- Data quality checks: completeness, freshness, validity, anomaly detection
- Multi-source support: Yahoo Finance, FRED, Alpha Vantage, BEI data
- Feature store scaffolding: centralized feature computation & caching
- Data lineage tracking

Referensi:
- Great Expectations: data quality framework
- Microsoft Qlib: feature store & data pipeline
- Apache Airflow: DAG-based pipeline (future)
"""

import pandas as pd
from typing import Dict, List, Optional, Tuple, Callable
from dataclasses import dataclass, field
from datetime import datetime, timedelta
import os
import json


# =============================================================================
# DATA QUALITY CHECKS
# =============================================================================

@dataclass
class DataQualityReport:
    source: str
    passed: bool
    checks: List[dict] = field(default_factory=list)
    n_rows: int = 0
    n_columns: int = 0
    missing_pct: float = 0.0
    duplicate_pct: float = 0.0
    freshness_hours: float = 0.0
    issues: List[str] = field(default_factory=list)

    def summary(self) -> str:
        status = "PASS" if self.passed else "FAIL"
        lines = [
            f"Data Quality Report [{self.source}]: {status}",
            f"  Rows: {self.n_rows}, Columns: {self.n_columns}",
            f"  Missing: {self.missing_pct:.1f}%, Duplicates: {self.duplicate_pct:.1f}%",
            f"  Freshness: {self.freshness_hours:.1f} hours",
        ]
        if self.issues:
            lines.append(f"  Issues: {'; '.join(self.issues)}")
        return "\n".join(lines)


def check_data_quality(
    df: pd.DataFrame,
    source: str = "unknown",
    required_columns: Optional[List[str]] = None,
    max_missing_pct: float = 10.0,
    max_duplicate_pct: float = 5.0,
    max_stale_hours: float = 72.0,
    date_column: Optional[str] = None,
) -> DataQualityReport:
    """
    Run comprehensive data quality checks.

    Checks:
    1. Completeness: missing values percentage
    2. Freshness: data staleness (hours since last record)
    3. Duplicates: duplicate rows percentage
    4. Validity: required columns present
    5. Anomaly: sudden price gaps (>20% day change)
    6. Volume sanity: non-negative volume
    """
    report = DataQualityReport(source=source, passed=True)
    report.n_rows = len(df)
    report.n_columns = len(df.columns)

    if df.empty:
        report.passed = False
        report.issues.append("DataFrame kosong")
        return report

    # Check 1: Completeness
    total_cells = df.size
    missing_cells = df.isna().sum().sum()
    report.missing_pct = (missing_cells / total_cells * 100) if total_cells > 0 else 0
    check1 = {
        "name": "completeness",
        "passed": report.missing_pct <= max_missing_pct,
        "value": report.missing_pct,
        "threshold": max_missing_pct,
    }
    report.checks.append(check1)
    if not check1["passed"]:
        report.passed = False
        report.issues.append(f"Missing data {report.missing_pct:.1f}% > {max_missing_pct}%")

    # Check 2: Duplicates
    dup_count = df.duplicated().sum()
    report.duplicate_pct = (dup_count / len(df) * 100) if len(df) > 0 else 0
    check2 = {
        "name": "duplicates",
        "passed": report.duplicate_pct <= max_duplicate_pct,
        "value": report.duplicate_pct,
        "threshold": max_duplicate_pct,
    }
    report.checks.append(check2)
    if not check2["passed"]:
        report.passed = False
        report.issues.append(f"Duplicates {report.duplicate_pct:.1f}% > {max_duplicate_pct}%")

    # Check 3: Freshness
    if date_column and date_column in df.columns:
        try:
            last_date = pd.to_datetime(df[date_column]).max()
            hours_old = (datetime.now() - last_date).total_seconds() / 3600
            report.freshness_hours = hours_old
            check3 = {
                "name": "freshness",
                "passed": hours_old <= max_stale_hours,
                "value": hours_old,
                "threshold": max_stale_hours,
            }
            report.checks.append(check3)
            if not check3["passed"]:
                report.passed = False
                report.issues.append(f"Data stale {hours_old:.1f}h > {max_stale_hours}h")
        except Exception:
            pass
    elif isinstance(df.index, pd.DatetimeIndex):
        last_date = df.index.max()
        hours_old = (datetime.now() - last_date.to_pydatetime()).total_seconds() / 3600
        report.freshness_hours = hours_old
        check3 = {
            "name": "freshness",
            "passed": hours_old <= max_stale_hours,
            "value": hours_old,
            "threshold": max_stale_hours,
        }
        report.checks.append(check3)
        if not check3["passed"]:
            report.passed = False
            report.issues.append(f"Data stale {hours_old:.1f}h > {max_stale_hours}h")

    # Check 4: Required columns
    if required_columns:
        missing_cols = [c for c in required_columns if c not in df.columns]
        check4 = {
            "name": "required_columns",
            "passed": len(missing_cols) == 0,
            "value": missing_cols,
            "threshold": [],
        }
        report.checks.append(check4)
        if missing_cols:
            report.passed = False
            report.issues.append(f"Missing columns: {missing_cols}")

    # Check 5: Price anomaly (sudden >20% gap)
    if "Close" in df.columns and len(df) > 1:
        returns = df["Close"].pct_change().abs()
        anomalies = (returns > 0.20).sum()
        check5 = {
            "name": "price_anomaly",
            "passed": anomalies <= 3,
            "value": int(anomalies),
            "threshold": 3,
        }
        report.checks.append(check5)
        if not check5["passed"]:
            report.passed = False
            report.issues.append(f"Price anomalies: {anomalies} gaps > 20%")

    # Check 6: Volume sanity
    if "Volume" in df.columns:
        neg_vol = (df["Volume"] < 0).sum()
        check6 = {
            "name": "volume_sanity",
            "passed": neg_vol == 0,
            "value": int(neg_vol),
            "threshold": 0,
        }
        report.checks.append(check6)
        if neg_vol > 0:
            report.passed = False
            report.issues.append(f"Negative volume: {neg_vol} rows")

    return report


# =============================================================================
# MULTI-SOURCE DATA FETCHER
# =============================================================================

class MultiSourceDataFetcher:
    """
    Multi-source data fetcher dengan fallback strategy.

    Sources:
    1. Yahoo Finance (primary — free, global coverage)
    2. Alpha Vantage (fallback — API key required)
    3. FRED (macro data — API key required)
    4. BEI/IDX data (Indonesia-specific — future)

    Referensi: ACUAN_PROYEK.md data strategy
    """

    def __init__(self, alpha_vantage_key: str = "", fred_key: str = ""):
        self.alpha_vantage_key = alpha_vantage_key or os.getenv("ALPHA_VANTAGE_API_KEY", "")
        self.fred_key = fred_key or os.getenv("FRED_API_KEY", "")
        self.source_status = {}

    def fetch_yahoo(self, ticker: str, period: str = "2y") -> pd.DataFrame:
        """Fetch from Yahoo Finance (primary source)."""
        try:
            import yfinance as yf
            df = yf.download(ticker, period=period, progress=False, auto_adjust=True)
            if not df.empty:
                self.source_status[ticker] = "yahoo"
                return df
        except Exception as e:
            print(f"[WARNING] Yahoo Finance failed for {ticker}: {e}")
        return pd.DataFrame()

    def fetch_alpha_vantage(self, ticker: str) -> pd.DataFrame:
        """Fetch from Alpha Vantage (fallback source)."""
        if not self.alpha_vantage_key:
            return pd.DataFrame()
        try:
            import requests
            url = "https://www.alphavantage.co/query"
            params = {
                "function": "TIME_SERIES_DAILY",
                "symbol": ticker,
                "outputsize": "full",
                "apikey": self.alpha_vantage_key,
            }
            resp = requests.get(url, params=params, timeout=30)
            data = resp.json()
            if "Time Series (Daily)" in data:
                ts = data["Time Series (Daily)"]
                df = pd.DataFrame(ts).T
                df.index = pd.to_datetime(df.index)
                df = df.sort_index()
                df.columns = ["Open", "High", "Low", "Close", "Volume"]
                df = df.astype(float)
                self.source_status[ticker] = "alpha_vantage"
                return df
        except Exception as e:
            print(f"[WARNING] Alpha Vantage failed for {ticker}: {e}")
        return pd.DataFrame()

    def fetch_fred(self, series_id: str) -> pd.DataFrame:
        """Fetch macro data from FRED API."""
        if not self.fred_key:
            return pd.DataFrame()
        try:
            import requests
            url = "https://api.stlouisfed.org/fred/series/observations"
            params = {
                "series_id": series_id,
                "api_key": self.fred_key,
                "file_type": "json",
                "observation_start": (datetime.now() - timedelta(days=730)).strftime("%Y-%m-%d"),
            }
            resp = requests.get(url, params=params, timeout=30)
            data = resp.json()
            if "observations" in data:
                obs = data["observations"]
                df = pd.DataFrame(obs)
                df["date"] = pd.to_datetime(df["date"])
                df = df.set_index("date")
                df["value"] = pd.to_numeric(df["value"], errors="coerce")
                return df[["value"]].rename(columns={"value": series_id})
        except Exception as e:
            print(f"[WARNING] FRED failed for {series_id}: {e}")
        return pd.DataFrame()

    def fetch_with_fallback(self, ticker: str, period: str = "2y") -> pd.DataFrame:
        """Fetch with fallback: Yahoo → Alpha Vantage."""
        df = self.fetch_yahoo(ticker, period)
        if df.empty:
            print(f"[FALLBACK] Trying Alpha Vantage for {ticker}")
            df = self.fetch_alpha_vantage(ticker)
        return df

    def fetch_all(
        self,
        tickers: Dict[str, str],
        period: str = "2y",
        run_quality_checks: bool = True,
    ) -> Tuple[Dict[str, pd.DataFrame], List[DataQualityReport]]:
        """
        Fetch all tickers with quality checks.

        Returns (data_dict, quality_reports)
        """
        data = {}
        reports = []

        for name, ticker in tickers.items():
            df = self.fetch_with_fallback(ticker, period)
            if not df.empty:
                data[name] = df
                if run_quality_checks:
                    report = check_data_quality(
                        df, source=f"{name} ({ticker})",
                        required_columns=["Close"],
                    )
                    reports.append(report)
                    if not report.passed:
                        print(f"[DQ] {report.summary()}")
            else:
                print(f"[ERROR] No data for {name} ({ticker})")

        return data, reports


# =============================================================================
# FEATURE STORE SCAFFOLDING
# =============================================================================

class FeatureStore:
    """
    Centralized feature computation & caching.

    - Compute features once, cache for reuse
    - Version features by date
    - Avoid redundant computation across pipeline stages

    Referensi: Microsoft Qlib factor/feature store
    """

    def __init__(self, cache_dir: Optional[str] = None):
        self.cache_dir = cache_dir or os.path.join(os.path.dirname(__file__), "data", "feature_cache")
        os.makedirs(self.cache_dir, exist_ok=True)
        self._memory_cache = {}

    def compute_and_cache(
        self,
        key: str,
        compute_fn: Callable,
        force_recompute: bool = False,
    ) -> pd.DataFrame:
        """
        Compute features and cache result.

        Args:
            key: Cache key (e.g. "features_2024-01-15")
            compute_fn: Function that returns DataFrame
            force_recompute: If True, skip cache
        """
        if not force_recompute and key in self._memory_cache:
            return self._memory_cache[key]

        cache_path = os.path.join(self.cache_dir, f"{key}.parquet")
        if not force_recompute and os.path.exists(cache_path):
            df = pd.read_parquet(cache_path)
            self._memory_cache[key] = df
            return df

        df = compute_fn()
        if not df.empty:
            df.to_parquet(cache_path)
            self._memory_cache[key] = df
        return df

    def get_cached(self, key: str) -> Optional[pd.DataFrame]:
        """Get cached features by key."""
        if key in self._memory_cache:
            return self._memory_cache[key]
        cache_path = os.path.join(self.cache_dir, f"{key}.parquet")
        if os.path.exists(cache_path):
            df = pd.read_parquet(cache_path)
            self._memory_cache[key] = df
            return df
        return None

    def clear_cache(self, older_than_days: Optional[int] = None):
        """Clear cache, optionally only entries older than N days."""
        if older_than_days is None:
            import shutil
            shutil.rmtree(self.cache_dir, ignore_errors=True)
            os.makedirs(self.cache_dir, exist_ok=True)
            self._memory_cache = {}
        else:
            cutoff = datetime.now() - timedelta(days=older_than_days)
            for f in os.listdir(self.cache_dir):
                fpath = os.path.join(self.cache_dir, f)
                if os.path.getmtime(fpath) < cutoff.timestamp():
                    os.remove(fpath)

    def list_cached(self) -> List[str]:
        """List all cached feature keys."""
        files = os.listdir(self.cache_dir)
        return [f.replace(".parquet", "") for f in files if f.endswith(".parquet")]


# =============================================================================
# DATA LINEAGE TRACKING
# =============================================================================

@dataclass
class DataLineage:
    """Track data origin and transformations."""
    source: str
    fetched_at: str
    rows: int
    columns: List[str]
    transformations: List[str] = field(default_factory=list)
    quality_passed: bool = True

    def to_dict(self) -> dict:
        return {
            "source": self.source,
            "fetched_at": self.fetched_at,
            "rows": self.rows,
            "columns": self.columns,
            "transformations": self.transformations,
            "quality_passed": self.quality_passed,
        }

    def save(self, path: Optional[str] = None):
        """Save lineage record."""
        path = path or os.path.join(os.path.dirname(__file__), "data", "lineage.json")
        records = []
        if os.path.exists(path):
            with open(path) as f:
                records = json.load(f)
        records.append(self.to_dict())
        with open(path, "w") as f:
            json.dump(records, f, indent=2, default=str)
