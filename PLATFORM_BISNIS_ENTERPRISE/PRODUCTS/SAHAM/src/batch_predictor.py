"""
Multiprocessing multi-ticker prediction module.

Runs predictions for multiple tickers in parallel using ProcessPoolExecutor.
Each worker process loads its own copy of the predictor (with lazy imports),
so heavy ML libraries are only loaded in workers that actually need them.

Usage:
    from src.batch_predictor import run_batch_predictions
    results = run_batch_predictions(
        tickers=["^JKSE", "BBCA.JK", "BBRI.JK"],
        period="2y",
        max_workers=4,
    )
"""

import logging
import os
import sys
from concurrent.futures import ProcessPoolExecutor, as_completed
from typing import Dict, List, Optional

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

logger = logging.getLogger("saham.batch_predictor")


def _predict_single_ticker(
    ticker: str,
    market_data: dict,
    fred_data: Optional[dict] = None,
) -> dict:
    """Worker function: run prediction for a single ticker.

    This runs in a separate process, so imports are local to that process.
    """
    try:
        from src.predictor import run_prediction

        result = run_prediction(
            market_data=market_data,
            fred_data=fred_data,
            target_ticker=ticker,
        )
        result["ticker"] = ticker
        return result
    except Exception as e:
        logger.error(f"[BATCH] Prediction failed for {ticker}: {e}")
        return {"ticker": ticker, "error": str(e)}


def run_batch_predictions(
    tickers: List[str],
    market_data: Optional[dict] = None,
    fred_data: Optional[dict] = None,
    period: str = "2y",
    max_workers: Optional[int] = None,
    use_multiprocessing: bool = True,
) -> Dict[str, dict]:
    """Run predictions for multiple tickers in parallel.

    Args:
        tickers: List of ticker symbols to predict
        market_data: Pre-fetched market data dict. If None, fetches per-ticker.
        fred_data: FRED economic data (shared across all tickers)
        period: Data period if fetching is needed
        max_workers: Max parallel processes. Default: min(len(tickers), os.cpu_count())
        use_multiprocessing: If False, runs sequentially (for debugging)

    Returns:
        Dict mapping ticker -> prediction result
    """
    if not tickers:
        return {}

    if max_workers is None:
        max_workers = min(len(tickers), os.cpu_count() or 4)

    results: Dict[str, dict] = {}

    if not use_multiprocessing or len(tickers) == 1:
        # Sequential fallback
        logger.info(f"[BATCH] Sequential prediction for {len(tickers)} tickers")
        for ticker in tickers:
            results[ticker] = _predict_single_ticker(
                ticker, market_data or {}, fred_data
            )
        return results

    # Parallel execution
    logger.info(
        f"[BATCH] Parallel prediction for {len(tickers)} tickers "
        f"with {max_workers} workers"
    )

    # Prepare per-ticker market data
    ticker_data_map = {}
    if market_data:
        # If shared market_data is provided, each worker gets the same dict
        ticker_data_map = {t: market_data for t in tickers}
    else:
        # Fetch data per ticker (could use async_data_fetcher for parallel fetch)
        from src.async_data_fetcher import fetch_watchlist_parallel

        fetched = fetch_watchlist_parallel(tickers, period=period)
        for ticker in tickers:
            df = fetched.get(ticker)
            if df is not None and not df.empty:
                ticker_data_map[ticker] = {ticker: df}
            else:
                results[ticker] = {"ticker": ticker, "error": "No data available"}

    tickers_to_run = [t for t in tickers if t in ticker_data_map]

    with ProcessPoolExecutor(max_workers=max_workers) as executor:
        futures = {
            executor.submit(
                _predict_single_ticker,
                ticker,
                ticker_data_map[ticker],
                fred_data,
            ): ticker
            for ticker in tickers_to_run
        }

        for future in as_completed(futures):
            ticker = futures[future]
            try:
                result = future.result(timeout=300)
                results[ticker] = result
            except Exception as e:
                logger.error(f"[BATCH] Worker error for {ticker}: {e}")
                results[ticker] = {"ticker": ticker, "error": str(e)}

    success_count = sum(1 for r in results.values() if "error" not in r)
    logger.info(
        f"[BATCH] Done: {success_count}/{len(tickers)} succeeded"
    )

    return results


def run_batch_predictions_threaded(
    tickers: List[str],
    market_data: Optional[dict] = None,
    fred_data: Optional[dict] = None,
    max_workers: Optional[int] = None,
) -> Dict[str, dict]:
    """Run predictions using ThreadPoolExecutor (lighter weight, GIL-bound).

    Use this when predictions are I/O-bound or when ProcessPoolExecutor
    overhead is too high. ML training is CPU-bound so prefer
    run_batch_predictions() for training-heavy workloads.
    """
    from concurrent.futures import ThreadPoolExecutor

    if not tickers:
        return {}

    if max_workers is None:
        max_workers = min(len(tickers), os.cpu_count() or 4)

    results: Dict[str, dict] = {}

    with ThreadPoolExecutor(max_workers=max_workers) as executor:
        futures = {
            executor.submit(
                _predict_single_ticker,
                ticker,
                market_data or {},
                fred_data,
            ): ticker
            for ticker in tickers
        }

        for future in as_completed(futures):
            ticker = futures[future]
            try:
                result = future.result(timeout=300)
                results[ticker] = result
            except Exception as e:
                results[ticker] = {"ticker": ticker, "error": str(e)}

    return results
