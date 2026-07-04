"""
Unit tests dasar untuk modul inti aplikasi saham.
Run: python -m pytest tests/test_core.py -v
"""

import pandas as pd
import numpy as np
import pytest
import tempfile
import os
import sys

# Ensure project root is in path
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))


# ===========================================================================
# Test: preprocessor.py
# ===========================================================================
class TestPreprocessor:
    def test_calculate_rsi(self):
        from src.preprocessor import calculate_rsi

        prices = pd.Series([100, 102, 101, 103, 105, 104, 106, 108, 107, 109,
                            111, 110, 112, 114, 113, 115, 117, 116, 118, 120])
        rsi = calculate_rsi(prices, period=14)
        assert not rsi.isna().all(), "RSI should not be all NaN"
        valid_rsi = rsi.dropna()
        assert (valid_rsi >= 0).all() and (valid_rsi <= 100).all(), "RSI should be 0-100"

    def test_calculate_rsi_all_up(self):
        from src.preprocessor import calculate_rsi

        prices = pd.Series(range(100, 120))
        rsi = calculate_rsi(prices, period=14)
        valid_rsi = rsi.dropna()
        assert (valid_rsi > 90).all(), "RSI should be near 100 when all prices go up"

    def test_calculate_rsi_all_down(self):
        from src.preprocessor import calculate_rsi

        prices = pd.Series(range(120, 100, -1))
        rsi = calculate_rsi(prices, period=14)
        valid_rsi = rsi.dropna()
        assert (valid_rsi < 10).all(), "RSI should be near 0 when all prices go down"

    def test_train_test_split_time(self):
        from src.preprocessor import train_test_split_time

        df = pd.DataFrame({"A": range(100)})
        train, test = train_test_split_time(df, test_size=0.2)
        assert len(train) == 80
        assert len(test) == 20
        assert train.index[-1] < test.index[0], "Train should come before test"

    def test_add_lag_features(self):
        from src.preprocessor import add_lag_features

        df = pd.DataFrame({"Close": [100, 101, 102, 103, 104, 105]})
        df = add_lag_features(df, "Close", [1, 2, 3])
        assert "Close_lag1" in df.columns
        assert "Close_lag2" in df.columns
        assert "Close_lag3" in df.columns
        assert df["Close_lag1"].iloc[1] == 100


# ===========================================================================
# Test: indicators.py
# ===========================================================================
class TestIndicators:
    @pytest.fixture
    def sample_ohlc(self):
        np.random.seed(42)
        n = 100
        close = 100 + np.cumsum(np.random.randn(n) * 0.5)
        high = close + np.abs(np.random.randn(n) * 0.3)
        low = close - np.abs(np.random.randn(n) * 0.3)
        volume = np.random.randint(1000, 10000, n).astype(float)
        return pd.DataFrame({
            "Open": close - 0.1, "High": high, "Low": low,
            "Close": close, "Volume": volume,
        })

    def test_calc_macd(self, sample_ohlc):
        from src.indicators import calc_macd

        df = calc_macd(sample_ohlc.copy())
        assert "MACD" in df.columns
        assert "MACD_Signal" in df.columns
        assert "MACD_Hist" in df.columns
        assert df["MACD"].isna().sum() < 30

    def test_calc_stochastic(self, sample_ohlc):
        from src.indicators import calc_stochastic

        df = calc_stochastic(sample_ohlc.copy())
        assert "Stoch_K" in df.columns
        assert "Stoch_D" in df.columns
        valid_k = df["Stoch_K"].dropna()
        assert (valid_k >= 0).all() and (valid_k <= 100).all()

    def test_calc_atr(self, sample_ohlc):
        from src.indicators import calc_atr

        df = calc_atr(sample_ohlc.copy())
        assert "ATR" in df.columns
        assert "ATR_Pct" in df.columns
        assert (df["ATR"].dropna() > 0).all()

    def test_calc_adx(self, sample_ohlc):
        from src.indicators import calc_adx

        df = calc_adx(sample_ohlc.copy())
        assert "ADX" in df.columns
        assert "Plus_DI" in df.columns
        assert "Minus_DI" in df.columns

    def test_add_all_indicators(self, sample_ohlc):
        from src.indicators import add_all_indicators

        df = add_all_indicators(sample_ohlc)
        expected = ["MACD", "Stoch_K", "ATR", "ADX", "Williams_R", "CCI", "PSAR"]
        for col in expected:
            assert col in df.columns, f"Missing indicator: {col}"

    def test_get_composite_signal(self):
        from src.indicators import get_composite_signal

        signals = {
            "MACD": {"signal": "Bullish", "value": "1.5", "detail": "MACD > Signal"},
            "RSI": {"signal": "Oversold", "value": "25", "detail": "RSI < 30"},
        }
        signal, score, detail = get_composite_signal(signals)
        assert signal in ["BUY", "SELL", "HOLD"]
        assert -1 <= score <= 1

    def test_get_composite_signal_empty(self):
        from src.indicators import get_composite_signal

        signal, score, detail = get_composite_signal({})
        assert signal == "NEUTRAL"
        assert score == 0


# ===========================================================================
# Test: risk_manager.py
# ===========================================================================
class TestRiskManager:
    def test_calc_var(self):
        from src.risk_manager import calc_var

        returns = pd.Series(np.random.randn(100) * 0.01)
        var = calc_var(returns, confidence=0.95, position_value=100_000_000)
        assert "var_percent" in var
        assert "cvar_percent" in var
        assert var["var_percent"] < 0, "VaR should be negative (loss)"
        assert var["cvar_percent"] <= var["var_percent"], "CVaR should be worse than VaR"

    def test_calc_sharpe_ratio(self):
        from src.risk_manager import calc_sharpe_ratio

        returns = pd.Series(np.random.randn(252) * 0.01 + 0.0005)
        sharpe = calc_sharpe_ratio(returns)
        assert "sharpe_daily" in sharpe
        assert "annualized_sharpe" in sharpe

    def test_calc_max_drawdown(self):
        from src.risk_manager import calc_max_drawdown

        prices = pd.Series([100, 110, 105, 95, 90, 100, 115])
        dd = calc_max_drawdown(prices)
        assert dd["max_drawdown_pct"] < 0, "Drawdown should be negative"
        assert dd["max_dd_duration_days"] > 0

    def test_calc_kelly_criterion(self):
        from src.risk_manager import calc_kelly_criterion

        returns = pd.Series([0.02, -0.01, 0.03, -0.015, 0.025, -0.01, 0.04, -0.005])
        kelly = calc_kelly_criterion(returns)
        assert "kelly_fraction" in kelly
        assert "win_rate" in kelly
        assert "recommendation" in kelly

    def test_calc_position_sizing_risk_based(self):
        from src.risk_manager import calc_position_sizing

        ps = calc_position_sizing(
            capital=100_000_000,
            risk_per_trade=0.02,
            entry_price=8000,
            stop_loss_price=7600,
            method="risk_based",
        )
        assert ps["shares"] > 0
        assert ps["risk_amount"] == 2_000_000
        assert ps["risk_per_share"] == 400

    def test_calc_position_sizing_atr_based(self):
        from src.risk_manager import calc_position_sizing

        ps = calc_position_sizing(
            capital=100_000_000,
            risk_per_trade=0.02,
            entry_price=8000,
            atr=100,
            method="atr_based",
        )
        assert ps["shares"] > 0
        assert ps["stop_loss"] == 7800  # 8000 - 2*100


# ===========================================================================
# Test: database.py
# ===========================================================================
class TestDatabase:
    @pytest.fixture
    def temp_db(self, monkeypatch):
        db_fd, db_path = tempfile.mkstemp(suffix=".db")
        os.close(db_fd)
        monkeypatch.setattr("src.config.DB_PATH", db_path)
        monkeypatch.setattr("src.database.DB_PATH", db_path)
        from src.database import init_db
        init_db()
        yield db_path
        os.unlink(db_path)

    def test_simpan_and_get_prediksi(self, temp_db):
        from src.database import simpan_prediksi, get_all_prediksi

        simpan_prediksi(
            ticker="^JKSE",
            tanggal_prediksi="2025-01-01",
            tanggal_target="2025-01-02",
            harga_prediksi=7500,
            arah_prediksi="UP",
            sinyal="BUY",
            confidence=0.65,
            model_votes="RF: BUY, XGB: BUY",
            harga_saat_ini=7400,
        )

        df = get_all_prediksi()
        assert len(df) == 1
        assert df.iloc[0]["ticker"] == "^JKSE"
        assert df.iloc[0]["harga_saat_ini"] == 7400

    def test_update_aktual(self, temp_db):
        from src.database import simpan_prediksi, update_aktual, get_verified_prediksi

        simpan_prediksi(
            ticker="^JKSE",
            tanggal_prediksi="2025-01-01",
            tanggal_target="2025-01-02",
            harga_prediksi=7500,
            arah_prediksi="UP",
            sinyal="BUY",
            confidence=0.65,
            model_votes="RF: BUY, XGB: BUY",
            harga_saat_ini=7400,
        )

        # Actual price went up to 7450
        updated = update_aktual("^JKSE", "2025-01-02", 7450)
        assert updated == 1

        df = get_verified_prediksi()
        assert len(df) == 1
        assert df.iloc[0]["arah_aktual"] == "UP", "Price 7450 > 7400 should be UP"

    def test_update_aktual_down(self, temp_db):
        from src.database import simpan_prediksi, update_aktual, get_verified_prediksi

        simpan_prediksi(
            ticker="^JKSE",
            tanggal_prediksi="2025-01-01",
            tanggal_target="2025-01-02",
            harga_prediksi=7500,
            arah_prediksi="UP",
            sinyal="BUY",
            confidence=0.65,
            model_votes="RF: BUY, XGB: BUY",
            harga_saat_ini=7400,
        )

        # Actual price went down to 7350
        updated = update_aktual("^JKSE", "2025-01-02", 7350)
        assert updated == 1

        df = get_verified_prediksi()
        assert df.iloc[0]["arah_aktual"] == "DOWN", "Price 7350 < 7400 should be DOWN"

    def test_akurasi_metrics(self, temp_db):
        from src.database import simpan_prediksi, update_aktual, get_akurasi_metrics

        # Prediction 1: correct (UP, actual UP)
        simpan_prediksi("^JKSE", "2025-01-01", "2025-01-02", 7500, "UP", "BUY", 0.65, "RF: BUY", 7400)
        update_aktual("^JKSE", "2025-01-02", 7450)

        # Prediction 2: wrong (UP, actual DOWN)
        simpan_prediksi("^JKSE", "2025-01-02", "2025-01-03", 7500, "UP", "BUY", 0.60, "RF: BUY", 7450)
        update_aktual("^JKSE", "2025-01-03", 7400)

        metrics = get_akurasi_metrics()
        assert metrics["total"] == 2
        assert metrics["benar"] == 1
        assert metrics["directional_accuracy"] == 50.0


# ===========================================================================
# Test: portfolio.py
# ===========================================================================
class TestPortfolio:
    def test_optimize_portfolio(self):
        from src.portfolio import optimize_portfolio

        np.random.seed(42)
        n = 252
        returns_df = pd.DataFrame({
            "A": np.random.randn(n) * 0.01 + 0.0005,
            "B": np.random.randn(n) * 0.015 + 0.0008,
            "C": np.random.randn(n) * 0.008 + 0.0003,
        })

        opt = optimize_portfolio(returns_df, n_portfolios=500)
        assert "efficient_frontier" in opt
        assert "max_sharpe_portfolio" in opt
        assert "min_vol_portfolio" in opt
        assert len(opt["efficient_frontier"]) == 500

    def test_optimize_single_asset_error(self):
        from src.portfolio import optimize_portfolio

        returns_df = pd.DataFrame({"A": np.random.randn(100) * 0.01})
        opt = optimize_portfolio(returns_df)
        assert "error" in opt


# ===========================================================================
# Test: intermarket.py
# ===========================================================================
class TestIntermarket:
    def test_calc_correlation_matrix(self):
        from src.intermarket import calc_correlation_matrix

        np.random.seed(42)
        n = 100
        market_data = {
            "IHSG": pd.DataFrame({"Close": 100 + np.cumsum(np.random.randn(n) * 0.5)}),
            "S&P500": pd.DataFrame({"Close": 200 + np.cumsum(np.random.randn(n) * 0.5)}),
        }
        corr = calc_correlation_matrix(market_data, period=60)
        assert corr.shape == (2, 2)
        assert (corr.values <= 1).all() and (corr.values >= -1).all()
        assert corr.loc["IHSG", "IHSG"] == 1.0


# ===========================================================================
# Test: sentiment.py
# ===========================================================================
class TestSentiment:
    def test_fear_greed_index(self):
        from src.sentiment import calc_fear_greed_index

        np.random.seed(42)
        n = 150
        market_data = {
            "IHSG": pd.DataFrame({
                "Close": 7000 + np.cumsum(np.random.randn(n) * 10),
                "High": 7050 + np.cumsum(np.random.randn(n) * 10),
                "Low": 6950 + np.cumsum(np.random.randn(n) * 10),
                "Volume": np.random.randint(1e6, 1e7, n).astype(float),
            }),
            "VIX": pd.DataFrame({"Close": 15 + np.abs(np.random.randn(n) * 3)}),
            "GOLD": pd.DataFrame({"Close": 2000 + np.cumsum(np.random.randn(n) * 5)}),
            "OIL": pd.DataFrame({"Close": 80 + np.cumsum(np.random.randn(n) * 2)}),
        }

        fg = calc_fear_greed_index(market_data)
        assert "composite_score" in fg
        assert 0 <= fg["composite_score"] <= 100
        assert "label" in fg
        assert "components" in fg

    def test_market_regime(self):
        from src.sentiment import calc_market_regime

        np.random.seed(42)
        n = 150
        market_data = {
            "IHSG": pd.DataFrame({
                "Close": 7000 + np.cumsum(np.random.randn(n) * 10),
                "High": 7050 + np.cumsum(np.random.randn(n) * 10),
                "Low": 6950 + np.cumsum(np.random.randn(n) * 10),
                "Volume": np.random.randint(1e6, 1e7, n).astype(float),
            }),
            "VIX": pd.DataFrame({"Close": 15 + np.abs(np.random.randn(n) * 3)}),
        }

        regime = calc_market_regime(market_data, "^JKSE")
        assert regime["regime"] in ["BULL", "BEAR", "SIDEWAYS"]
        assert 0 <= regime["confidence"] <= 100
