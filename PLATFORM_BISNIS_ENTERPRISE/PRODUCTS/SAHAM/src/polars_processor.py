"""
Polars-accelerated data processing for performance-critical operations.

Polars is ~5-10x faster than pandas for merge/join operations on large DataFrames.
This module provides drop-in replacements that use Polars internally but
return pandas DataFrames for compatibility with the existing pipeline.

Usage:
    from src.polars_processor import merge_market_data_fast
    merged = merge_market_data_fast(market_data)  # same output as preprocessor.merge_market_data
"""

import logging
from typing import Dict, Optional

import pandas as pd

logger = logging.getLogger("saham.polars")

try:
    import polars as pl

    _POLARS_AVAILABLE = True
except ImportError:
    _POLARS_AVAILABLE = False
    logger.info("polars not installed — using pandas fallback")


def merge_market_data_fast(market_data: Dict[str, pd.DataFrame]) -> pd.DataFrame:
    """Merge multiple ticker DataFrames using Polars for speed.

    Drop-in replacement for preprocessor.merge_market_data().
    Falls back to pandas if Polars is not available.

    Args:
        market_data: Dict mapping name -> DataFrame with OHLCV columns

    Returns:
        Merged pandas DataFrame with prefixed columns (e.g. "IHSG_Close")
    """
    if not _POLARS_AVAILABLE or not market_data:
        # Fallback to pandas implementation
        from .preprocessor import merge_market_data

        return merge_market_data(market_data)

    # Convert pandas DataFrames to Polars and join on index (date)
    lazy_frames = []
    for name, data in market_data.items():
        if data.empty:
            continue

        # Reset index to get date as a column
        df_pd = data.copy()
        df_pd.index.name = "date"
        df_pd = df_pd.reset_index()

        # Convert to Polars
        pl_df = pl.from_pandas(df_pd)

        # Prefix all columns except 'date'
        rename_map = {
            col: f"{name}_{col}" for col in pl_df.columns if col != "date"
        }
        pl_df = pl_df.rename(rename_map)

        lazy_frames.append(pl_df.lazy())

    if not lazy_frames:
        return pd.DataFrame()

    # Join all frames on 'date' column (outer join)
    result = lazy_frames[0]
    for frame in lazy_frames[1:]:
        result = result.join(frame, on="date", how="outer", suffix="_dup")

    # Collect and convert back to pandas
    pl_result = result.sort("date").collect()

    # Handle duplicate columns from join
    df_pd = pl_result.to_pandas()
    df_pd = df_pd.set_index("date")
    df_pd.index = pd.to_datetime(df_pd.index)

    # Forward fill NaN values
    df_pd = df_pd.ffill()

    return df_pd


def calculate_returns_fast(
    prices: pd.Series, method: str = "pct_change"
) -> pd.Series:
    """Calculate returns using Polars for speed.

    Args:
        prices: Price series
        method: "pct_change" or "log"

    Returns:
        Returns series (pandas)
    """
    if not _POLARS_AVAILABLE:
        if method == "log":
            import numpy as np

            return np.log(prices / prices.shift(1))
        return prices.pct_change()

    pl_series = pl.from_pandas(prices.reset_index())
    col_name = pl_series.columns[1] if len(pl_series.columns) > 1 else "value"

    if method == "log":
        pl_result = pl_series.with_columns(
            (pl.col(col_name).log() - pl.col(col_name).shift(1).log()).alias(
                "returns"
            )
        )
    else:
        pl_result = pl_series.with_columns(
            (pl.col(col_name) / pl.col(col_name).shift(1) - 1).alias("returns")
        )

    result = pl_result.to_pandas()
    if len(pl_series.columns) > 1:
        result = result.set_index(result.columns[0])

    return result["returns"]


def is_available() -> bool:
    """Check if Polars is available."""
    return _POLARS_AVAILABLE


def get_benchmark_info() -> dict:
    """Return info about Polars availability and version."""
    if _POLARS_AVAILABLE:
        return {"available": True, "version": pl.__version__}
    return {"available": False, "version": None}
