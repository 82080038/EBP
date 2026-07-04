"""
Tests for src/data_validation.py — OHLCV and ticker validation layer.
"""

import pandas as pd
import numpy as np
import pytest

from src.data_validation import (
    validate_ohlcv,
    validate_market_data,
    validate_ticker_symbol,
    REQUIRED_COLUMNS,
    MIN_ROWS,
)


def _make_ohlcv(rows: int = 100, start_price: float = 1000.0, seed: int = 42) -> pd.DataFrame:
    rng = np.random.default_rng(seed)
    dates = pd.date_range(end="2026-06-25", periods=rows, freq="B")
    returns = rng.normal(0.0, 0.015, rows)
    close = start_price * np.exp(np.cumsum(returns))
    noise = rng.uniform(0.005, 0.02, rows)
    high = close * (1 + noise)
    low = close * (1 - noise)
    open_ = close * (1 + rng.normal(0, 0.005, rows))
    volume = rng.integers(1_000_000, 50_000_000, rows)
    return pd.DataFrame(
        {
            "Open": open_,
            "High": high,
            "Low": low,
            "Close": close,
            "Volume": volume,
        },
        index=dates,
    )


class TestValidateTickerSymbol:
    def test_valid_tickers(self):
        assert validate_ticker_symbol("BBCA") is True
        assert validate_ticker_symbol("BBCA.JK") is True
        assert validate_ticker_symbol("^JKSE") is True
        assert validate_ticker_symbol("USDIDR=X") is True

    def test_invalid_tickers(self):
        assert validate_ticker_symbol("") is False
        assert validate_ticker_symbol("a" * 25) is False
        assert validate_ticker_symbol("BB;CA") is False
        assert validate_ticker_symbol("BB CA") is False
        assert validate_ticker_symbol("../../etc") is False


class TestValidateOhlcv:
    def test_valid_data(self):
        df = _make_ohlcv(rows=100)
        result = validate_ohlcv(df, ticker="TEST")
        assert result.is_valid is True
        assert result.rows == 100
        assert len(result.issues) == 0
        assert "last_close" in result.stats

    def test_empty_data(self):
        result = validate_ohlcv(pd.DataFrame(), ticker="EMPTY")
        assert result.is_valid is False
        assert "empty" in result.issues[0].lower()

    def test_missing_columns(self):
        df = _make_ohlcv(rows=100)
        df = df.drop(columns=["Volume"])
        result = validate_ohlcv(df, ticker="TEST")
        assert result.is_valid is False
        assert any("Missing" in i for i in result.issues)

    def test_nan_values(self):
        df = _make_ohlcv(rows=100)
        df.iloc[10, df.columns.get_loc("Close")] = np.nan
        result = validate_ohlcv(df, ticker="TEST")
        assert result.is_valid is False
        assert any("NaN" in i for i in result.issues)

    def test_high_low_inconsistency(self):
        df = _make_ohlcv(rows=100)
        df.iloc[10, df.columns.get_loc("High")] = df.iloc[10]["Low"] - 100
        result = validate_ohlcv(df, ticker="TEST")
        assert result.is_valid is False
        assert any("High < Low" in i for i in result.issues)

    def test_negative_volume(self):
        df = _make_ohlcv(rows=100)
        df.iloc[10, df.columns.get_loc("Volume")] = -1
        result = validate_ohlcv(df, ticker="TEST")
        assert result.is_valid is False
        assert any("Negative volume" in i for i in result.issues)

    def test_unsorted_index(self):
        df = _make_ohlcv(rows=100)
        df = df.sample(frac=1)  # shuffle
        result = validate_ohlcv(df, ticker="TEST")
        assert result.is_valid is False
        assert any("not sorted" in i for i in result.issues)

    def test_insufficient_rows(self):
        df = _make_ohlcv(rows=5)
        result = validate_ohlcv(df, ticker="TEST", min_rows=30)
        assert result.is_valid is False
        assert any("Insufficient rows" in i for i in result.issues)


class TestValidateMarketData:
    def test_valid_market(self):
        market = {
            "IHSG": _make_ohlcv(rows=100),
            "S&P500": _make_ohlcv(rows=100, start_price=4000),
        }
        result = validate_market_data(market)
        assert result.is_valid is True
        assert result.valid == 2
        assert result.invalid == 0

    def test_invalid_market(self):
        market = {
            "IHSG": _make_ohlcv(rows=100),
            "BAD": pd.DataFrame(),
        }
        result = validate_market_data(market)
        assert result.is_valid is False
        assert result.valid == 1
        assert result.invalid == 1

    def test_empty_market(self):
        result = validate_market_data({})
        assert result.is_valid is False
        assert "empty" in result.issues[0].lower()
