"""Tests for performance optimization modules: lazy_imports, db_optimizer, batch_predictor, polars_processor."""

import sqlite3
import tempfile
import os
import time
import pandas as pd
import pytest


# =====================================================================
# Lazy Imports
# =====================================================================


class TestLazyImports:
    def test_lazy_module_defers_import(self):
        from src.lazy_imports import LazyModule

        lazy = LazyModule("json")
        # Before access, module not loaded
        assert object.__getattribute__(lazy, "_module") is None
        # Access triggers import
        result = lazy.loads
        assert result is not None
        # After access, module is loaded
        assert object.__getattribute__(lazy, "_module") is not None
        assert "loaded" in repr(lazy)

    def test_lazy_attr_defers_until_called(self):
        from src.lazy_imports import LazyAttr

        lazy = LazyAttr("json", "loads")
        # Should not be loaded yet
        assert not object.__getattribute__(lazy, "_loaded")
        # Call triggers load
        result = lazy('{"key": "value"}')
        assert result == {"key": "value"}
        assert object.__getattribute__(lazy, "_loaded")

    def test_lazy_attr_repr(self):
        from src.lazy_imports import LazyAttr

        lazy = LazyAttr("json", "loads")
        assert "lazy" in repr(lazy)
        lazy._load()
        assert "loaded" in repr(lazy)

    def test_is_loaded(self):
        from src.lazy_imports import is_loaded

        assert is_loaded("pandas")
        assert not is_loaded("nonexistent_module_xyz")

    def test_get_import_times(self):
        from src.lazy_imports import get_import_times

        times = get_import_times()
        assert "numpy" in times
        assert "pandas" in times
        assert times["pandas"] == "loaded"


# =====================================================================
# DB Optimizer
# =====================================================================


class TestDBOptimizer:
    @pytest.fixture
    def temp_db(self):
        """Create a temp DB with all tables."""
        fd, db_path = tempfile.mkstemp(suffix=".db")
        os.close(fd)

        conn = sqlite3.connect(db_path)
        cursor = conn.cursor()

        # Create minimal tables matching production schema
        cursor.executescript("""
            CREATE TABLE IF NOT EXISTS prediksi (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticker TEXT NOT NULL,
                tanggal_prediksi DATE NOT NULL,
                tanggal_target DATE NOT NULL,
                harga_prediksi REAL NOT NULL
            );
            CREATE TABLE IF NOT EXISTS harga_harian (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticker TEXT NOT NULL,
                tanggal DATE NOT NULL,
                close REAL,
                UNIQUE(ticker, tanggal)
            );
            CREATE TABLE IF NOT EXISTS harga_intraday (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticker TEXT NOT NULL,
                timestamp DATETIME NOT NULL,
                interval TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS technical_indicators (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticker TEXT NOT NULL,
                date DATE NOT NULL
            );
            CREATE TABLE IF NOT EXISTS fundamental_data (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticker TEXT NOT NULL,
                tanggal DATE NOT NULL
            );
            CREATE TABLE IF NOT EXISTS financial_ratios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticker TEXT NOT NULL,
                tanggal DATE NOT NULL
            );
            CREATE TABLE IF NOT EXISTS notifikasi (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                dibaca INTEGER DEFAULT 0
            );
            CREATE TABLE IF NOT EXISTS log_aktivitas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                timestamp DATETIME
            );
            CREATE TABLE IF NOT EXISTS alerts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticker TEXT NOT NULL,
                is_active INTEGER DEFAULT 1
            );
        """)
        conn.commit()

        yield conn

        conn.close()
        os.unlink(db_path)

    def test_create_indexes(self, temp_db):
        from src.db_optimizer import create_indexes

        result = create_indexes(temp_db)

        assert len(result["indexes_created"]) >= 8
        assert len(result["errors"]) == 0
        # Verify indexes exist
        cursor = temp_db.cursor()
        cursor.execute(
            "SELECT name FROM sqlite_master WHERE type='index' AND sql IS NOT NULL"
        )
        index_names = [row[0] for row in cursor.fetchall()]
        assert "idx_prediksi_ticker_date" in index_names
        assert "idx_harga_harian_ticker_date" in index_names

    def test_pragma_settings_applied(self, temp_db):
        from src.db_optimizer import create_indexes

        create_indexes(temp_db)

        cursor = temp_db.cursor()
        cursor.execute("PRAGMA journal_mode")
        mode = cursor.fetchone()[0]
        assert mode == "wal"

    def test_get_index_info(self, temp_db):
        from src.db_optimizer import create_indexes, get_index_info

        create_indexes(temp_db)
        indexes = get_index_info(temp_db)
        assert len(indexes) >= 8
        # Check structure
        assert "name" in indexes[0]
        assert "table" in indexes[0]

    def test_get_query_plan(self, temp_db):
        from src.db_optimizer import create_indexes, get_query_plan

        create_indexes(temp_db)
        plan = get_query_plan(
            temp_db,
            "SELECT * FROM prediksi WHERE ticker = ? AND tanggal_prediksi = ?",
            ("^JKSE", "2026-01-01"),
        )
        assert len(plan) > 0


# =====================================================================
# Batch Predictor
# =====================================================================


class TestBatchPredictor:
    def test_sequential_mode(self):
        from src.batch_predictor import run_batch_predictions

        # Use sequential mode with mock data
        results = run_batch_predictions(
            tickers=["^JKSE"],
            market_data={},
            use_multiprocessing=False,
        )
        assert "^JKSE" in results
        # Empty market_data will produce error result
        assert "error" in results["^JKSE"]

    def test_empty_tickers(self):
        from src.batch_predictor import run_batch_predictions

        results = run_batch_predictions(tickers=[])
        assert results == {}

    def test_threaded_mode(self):
        from src.batch_predictor import run_batch_predictions_threaded

        results = run_batch_predictions_threaded(
            tickers=["^JKSE"],
            market_data={},
        )
        assert "^JKSE" in results


# =====================================================================
# Polars Processor
# =====================================================================


class TestPolarsProcessor:
    @pytest.fixture
    def sample_market_data(self):
        dates = pd.date_range("2026-01-01", periods=50, freq="D")
        return {
            "IHSG": pd.DataFrame(
                {
                    "Open": range(50),
                    "High": range(1, 51),
                    "Low": range(50),
                    "Close": range(2, 52),
                    "Volume": range(100, 150),
                },
                index=dates,
            ),
            "S&P500": pd.DataFrame(
                {
                    "Open": range(50, 100),
                    "High": range(51, 101),
                    "Low": range(50, 100),
                    "Close": range(52, 102),
                    "Volume": range(200, 250),
                },
                index=dates,
            ),
        }

    def test_merge_market_data_fast(self, sample_market_data):
        from src.polars_processor import merge_market_data_fast

        merged = merge_market_data_fast(sample_market_data)

        assert not merged.empty
        assert "IHSG_Close" in merged.columns
        assert "S&P500_Close" in merged.columns
        assert len(merged) == 50

    def test_merge_market_data_fast_empty(self):
        from src.polars_processor import merge_market_data_fast

        result = merge_market_data_fast({})
        assert result is None or result.empty

    def test_is_available(self):
        from src.polars_processor import is_available

        # Should return a bool
        assert isinstance(is_available(), bool)

    def test_get_benchmark_info(self):
        from src.polars_processor import get_benchmark_info

        info = get_benchmark_info()
        assert "available" in info
        assert "version" in info

    def test_calculate_returns_fast(self, sample_market_data):
        from src.polars_processor import calculate_returns_fast

        prices = sample_market_data["IHSG"]["Close"]
        returns = calculate_returns_fast(prices)
        assert len(returns) == len(prices)
        # First return should be NaN
        assert pd.isna(returns.iloc[0])
