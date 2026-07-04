"""
Tests for src/async_data_fetcher.py — parallel market data fetching.
"""

import pandas as pd
import pytest

from src.async_data_fetcher import (
    fetch_all_market_data_parallel,
    fetch_watchlist_parallel,
)


def test_fetch_all_market_data_parallel_mock(monkeypatch, mock_market_data):
    """Parallel fetch should return the same valid market data as mock."""
    result = fetch_all_market_data_parallel(
        tickers={"IHSG": "^JKSE", "S&P500": "^GSPC"},
        period="2y",
        interval="1d",
        max_workers=2,
    )
    assert isinstance(result, dict)
    assert "IHSG" in result
    assert "S&P500" in result
    for name, df in result.items():
        assert isinstance(df, pd.DataFrame)
        assert not df.empty
        assert {"Open", "High", "Low", "Close", "Volume"}.issubset(set(df.columns))


def test_fetch_watchlist_parallel_mock(monkeypatch, mock_market_data):
    result = fetch_watchlist_parallel(["^JKSE", "^GSPC"], max_workers=2)
    assert "^JKSE" in result
    assert "^GSPC" in result


def test_fetch_all_market_data_parallel_empty():
    result = fetch_all_market_data_parallel(tickers={}, max_workers=2)
    assert result == {}
