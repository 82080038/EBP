"""
Async / parallel data fetcher for multi-ticker market data.

Provides:
- fetch_all_market_data_parallel: fetch many tickers concurrently using a thread pool.
- fetch_watchlist_parallel: convenience wrapper for a list of ticker symbols.

This is meant to replace the sequential fetch_all_market_data when speed matters,
e.g. for the daily prediction pipeline that covers 100+ IDX stocks.
"""

import logging
from concurrent.futures import ThreadPoolExecutor, as_completed
from typing import Callable, Dict, List, Optional

import pandas as pd

from .config import TICKERS, BLUE_CHIPS_ID
from .data_fetcher import fetch_yfinance_data
from .data_validation import validate_market_data

logger = logging.getLogger(__name__)

DEFAULT_MAX_WORKERS = 8


def _fetch_one(ticker: str, period: str, interval: str) -> tuple:
    """Helper to fetch one ticker and return (ticker, DataFrame)."""
    try:
        df = fetch_yfinance_data(ticker, period=period, interval=interval)
        return ticker, df
    except Exception as e:
        logger.warning(f"[ASYNC FETCH] Failed {ticker}: {e}")
        return ticker, None


def fetch_all_market_data_parallel(
    tickers: Optional[Dict[str, str]] = None,
    period: str = "5y",
    interval: str = "1d",
    max_workers: int = DEFAULT_MAX_WORKERS,
    validate: bool = True,
) -> Dict[str, pd.DataFrame]:
    """
    Fetch market data for many tickers in parallel.

    Args:
        tickers: dict {name: ticker_symbol}. Defaults to TICKERS from config.
        period: yfinance period string.
        interval: yfinance interval string.
        max_workers: thread pool size.
        validate: if True, run data validation and drop invalid tickers.

    Returns:
        Dict[str, DataFrame] with valid data only (or all non-empty data if validate=False).
    """
    if tickers is None:
        tickers = TICKERS
    if not tickers:
        return {}

    market_data: Dict[str, pd.DataFrame] = {}

    logger.info(f"[ASYNC FETCH] Fetching {len(tickers)} tickers with {max_workers} workers")

    with ThreadPoolExecutor(max_workers=max_workers) as executor:
        future_to_name = {
            executor.submit(_fetch_one, ticker, period, interval): name
            for name, ticker in tickers.items()
        }

        for future in as_completed(future_to_name):
            name = future_to_name[future]
            try:
                _, df = future.result()
                if df is not None and not df.empty:
                    market_data[name] = df
            except Exception as e:
                logger.warning(f"[ASYNC FETCH] Error fetching {name}: {e}")

    if validate:
        validation = validate_market_data(market_data)
        if not validation.is_valid:
            logger.warning(f"[ASYNC FETCH] Validation issues: {validation.issues}")
        # Return only valid data; keep empty invalid tickers out of the pipeline
        market_data = {
            name: df
            for name, df in market_data.items()
            if validation.results.get(name, type("R", (), {"is_valid": True})()).is_valid
        }

    logger.info(f"[ASYNC FETCH] Completed: {len(market_data)}/{len(tickers)} tickers valid")
    return market_data


def fetch_watchlist_parallel(
    tickers: List[str],
    period: str = "1y",
    interval: str = "1d",
    max_workers: int = DEFAULT_MAX_WORKERS,
) -> Dict[str, pd.DataFrame]:
    """
    Fetch a simple list of ticker symbols (e.g. watchlist) in parallel.

    Returns a dict {ticker: DataFrame}.
    """
    ticker_dict = {t: t for t in tickers}
    return fetch_all_market_data_parallel(
        tickers=ticker_dict,
        period=period,
        interval=interval,
        max_workers=max_workers,
        validate=True,
    )


def fetch_idx_blue_chips_parallel(
    period: str = "1y",
    interval: str = "1d",
    max_workers: int = DEFAULT_MAX_WORKERS,
) -> Dict[str, pd.DataFrame]:
    """
    Fetch all IDX blue-chip tickers in parallel.

    BLUE_CHIPS_ID is a list of ticker symbols from config.
    """
    return fetch_watchlist_parallel(
        tickers=BLUE_CHIPS_ID,
        period=period,
        interval=interval,
        max_workers=max_workers,
    )
