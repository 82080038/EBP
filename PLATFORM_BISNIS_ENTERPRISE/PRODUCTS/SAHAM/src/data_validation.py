"""
Data validation layer for market data fetched from external sources.

Ensures OHLCV data is complete, internally consistent, and safe to use
before it enters the feature engineering / ML pipeline.
"""

from dataclasses import dataclass, field
from datetime import datetime
from typing import Dict, List, Optional

import pandas as pd


@dataclass
class ValidationResult:
    """Result of validating a single OHLCV DataFrame."""

    ticker: str
    is_valid: bool
    rows: int
    issues: List[str] = field(default_factory=list)
    stats: Dict[str, float] = field(default_factory=dict)


@dataclass
class MarketValidationResult:
    """Result of validating a Dict[str, DataFrame] of market data."""

    is_valid: bool
    total: int
    valid: int
    invalid: int
    results: Dict[str, ValidationResult] = field(default_factory=dict)
    issues: List[str] = field(default_factory=list)


REQUIRED_COLUMNS = {"Open", "High", "Low", "Close", "Volume"}
MIN_ROWS = 30


def _safe_float(series: pd.Series) -> Optional[float]:
    if series.empty or series.isna().all():
        return None
    return float(series.dropna().iloc[-1])


def validate_ohlcv(
    df: pd.DataFrame,
    ticker: str,
    min_rows: int = MIN_ROWS,
    required_columns: Optional[set] = None,
) -> ValidationResult:
    """
    Validate a single OHLCV DataFrame.

    Checks:
    - DataFrame is not empty
    - Required columns are present
    - No NaN values in required columns
    - Index is datetime and sorted
    - No duplicate index values
    - High >= Low for every row
    - Close is within [Low, High] (allowing small rounding tolerance)
    - Volume is non-negative
    - Prices are positive
    - Minimum row count

    Returns ValidationResult with is_valid flag and list of issues.
    """
    if required_columns is None:
        required_columns = REQUIRED_COLUMNS

    issues: List[str] = []
    stats: Dict[str, float] = {}

    # Empty check
    if df is None or df.empty:
        return ValidationResult(ticker=ticker, is_valid=False, rows=0, issues=["DataFrame is empty or None"], stats={})

    rows = len(df)
    stats["rows"] = float(rows)

    # Columns check
    missing = required_columns - set(df.columns)
    if missing:
        issues.append(f"Missing required columns: {sorted(missing)}")

    # Only proceed with column-dependent checks if all columns are present
    if not missing:
        cols = list(required_columns)
        df_check = df[cols].copy()

        # NaN check
        nan_counts = df_check.isna().sum()
        if nan_counts.any():
            bad_cols = nan_counts[nan_counts > 0].to_dict()
            issues.append(f"NaN values found: {bad_cols}")

        # Index check
        if not isinstance(df_check.index, pd.DatetimeIndex):
            try:
                df_check.index = pd.to_datetime(df_check.index)
            except Exception as e:
                issues.append(f"Index cannot be parsed as datetime: {e}")
        else:
            if not df_check.index.is_monotonic_increasing:
                issues.append("Index is not sorted ascending")
            if df_check.index.has_duplicates:
                issues.append("Duplicate index (dates) found")
            if df_check.index.isna().any():
                issues.append("Index contains NaT values")

        # Price consistency checks
        if not (df_check["High"] >= df_check["Low"]).all():
            bad = (~(df_check["High"] >= df_check["Low"])).sum()
            issues.append(f"High < Low on {bad} rows")

        tolerance = 0.02  # allow 2% slippage outside H/L due to rounding/adjustments
        close_below_low = (df_check["Close"] < df_check["Low"] * (1 - tolerance)).sum()
        close_above_high = (df_check["Close"] > df_check["High"] * (1 + tolerance)).sum()
        if close_below_low > 0 or close_above_high > 0:
            issues.append(f"Close outside High/Low range on {close_below_low + close_above_high} rows")

        if (df_check["Volume"] < 0).any():
            issues.append("Negative volume values found")

        for col in ("Open", "High", "Low", "Close"):
            if (df_check[col] <= 0).any():
                issues.append(f"Non-positive values in {col}")

        # Stats (last known values)
        for col in ("Open", "High", "Low", "Close", "Volume"):
            val = _safe_float(df_check[col])
            if val is not None:
                stats[f"last_{col.lower()}"] = val

    # Minimum rows check
    if rows < min_rows:
        issues.append(f"Insufficient rows: {rows} (minimum {min_rows})")

    is_valid = len(issues) == 0
    return ValidationResult(ticker=ticker, is_valid=is_valid, rows=rows, issues=issues, stats=stats)


def validate_market_data(
    market_data: Dict[str, pd.DataFrame],
    min_rows: int = MIN_ROWS,
) -> MarketValidationResult:
    """
    Validate a dictionary of market DataFrames.

    Returns MarketValidationResult with per-ticker results and aggregated flags.
    """
    if not market_data:
        return MarketValidationResult(is_valid=False, total=0, valid=0, invalid=0, issues=["market_data is empty"])

    results: Dict[str, ValidationResult] = {}
    for name, df in market_data.items():
        results[name] = validate_ohlcv(df, ticker=name, min_rows=min_rows)

    valid = sum(1 for r in results.values() if r.is_valid)
    invalid = len(results) - valid
    is_valid = invalid == 0
    issues = [f"{name}: {', '.join(r.issues)}" for name, r in results.items() if not r.is_valid]

    return MarketValidationResult(
        is_valid=is_valid,
        total=len(results),
        valid=valid,
        invalid=invalid,
        results=results,
        issues=issues,
    )


def validate_ticker_symbol(ticker: str) -> bool:
    """
    Validate a ticker symbol string to prevent injection / malformed input.

    Allowed: alphanumeric, dot, dash, caret, equals. Length 1-20.
    """
    if not isinstance(ticker, str):
        return False
    if len(ticker) < 1 or len(ticker) > 20:
        return False
    return ticker.replace(".", "").replace("-", "").replace("^", "").replace("=", "").isalnum()
