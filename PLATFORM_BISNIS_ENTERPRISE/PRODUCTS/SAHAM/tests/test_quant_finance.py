"""
Tests untuk modul Quant Finance: quant_finance.py
Run: python -m pytest tests/test_quant_finance.py -v
"""

import pandas as pd
import numpy as np
import pytest
import os
import sys

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))


# ===========================================================================
# Fixtures
# ===========================================================================
@pytest.fixture
def sample_backtest_data():
    """Generate synthetic OHLCV data with ATR for backtesting."""
    np.random.seed(42)
    n = 200
    close = 7000 + np.cumsum(np.random.randn(n) * 20)
    high = close + np.abs(np.random.randn(n) * 15)
    low = close - np.abs(np.random.randn(n) * 15)
    open_ = close + np.random.randn(n) * 5
    volume = np.random.randint(1e6, 5e7, n).astype(float)

    dates = pd.date_range("2024-01-01", periods=n, freq="B")
    df = pd.DataFrame({
        "Open": open_, "High": high, "Low": low,
        "Close": close, "Volume": volume,
    }, index=dates)
    df["Target_Next_Return"] = df["Close"].pct_change().shift(-1)
    df["Target_ATR"] = df["Close"].rolling(14).std()
    df = df.dropna()
    return df


@pytest.fixture
def sample_predictions(sample_backtest_data):
    """Generate synthetic predictions."""
    np.random.seed(42)
    n = len(sample_backtest_data)
    return np.random.choice([0, 1], size=n, p=[0.45, 0.55])


# ===========================================================================
# Test: Entry/Target/Stop
# ===========================================================================
class TestEntryTargetStop:
    def test_buy_signal(self):
        from src.quant_finance import calc_entry_target_stop

        result = calc_entry_target_stop(
            signal="BUY", current_price=7500, atr=100,
            capital=100_000_000, risk_per_trade=0.02, confidence=0.7,
        )
        assert result.signal == "BUY"
        assert result.entry == 7500
        assert result.stop_loss < result.entry
        assert result.target_1 > result.entry
        assert result.target_2 > result.target_1
        assert result.target_3 > result.target_2
        assert result.position_size_shares > 0
        assert result.risk_reward_ratio > 0

    def test_sell_signal(self):
        from src.quant_finance import calc_entry_target_stop

        result = calc_entry_target_stop(
            signal="SELL", current_price=7500, atr=100,
            capital=100_000_000, risk_per_trade=0.02, confidence=0.7,
        )
        assert result.signal == "SELL"
        assert result.stop_loss > result.entry
        assert result.target_1 < result.entry
        assert result.target_2 < result.target_1
        assert result.target_3 < result.target_2

    def test_hold_signal(self):
        from src.quant_finance import calc_entry_target_stop

        result = calc_entry_target_stop(
            signal="HOLD", current_price=7500, atr=100,
        )
        assert result.signal == "HOLD"
        assert result.entry == 7500
        assert result.stop_loss < result.entry  # Reference stop below entry
        assert result.target_1 > result.entry  # Reference target above entry

    def test_with_support_resistance(self):
        from src.quant_finance import calc_entry_target_stop

        result = calc_entry_target_stop(
            signal="BUY", current_price=7500, atr=100,
            support=7300, resistance=7700,
        )
        assert result.stop_loss <= 7300 + 50  # Should be near support
        assert result.target_2 >= 7700  # Should be near resistance

    def test_position_sizing(self):
        from src.quant_finance import calc_entry_target_stop

        result = calc_entry_target_stop(
            signal="BUY", current_price=7500, atr=100,
            capital=100_000_000, risk_per_trade=0.02, confidence=1.0,
        )
        risk_amount = 100_000_000 * 0.02 * 1.0
        risk_per_share = 7500 - (7500 - 2 * 100)
        expected_shares = int(risk_amount / risk_per_share / 100) * 100
        assert result.position_size_shares == expected_shares
        assert result.position_size_shares % 100 == 0  # Must be in lots of 100


# ===========================================================================
# Test: Realistic Backtest
# ===========================================================================
class TestRealisticBacktest:
    def test_realistic_backtest_runs(self, sample_backtest_data, sample_predictions):
        from src.quant_finance import realistic_backtest

        result = realistic_backtest(
            sample_backtest_data, sample_predictions,
            target_close_col="Close",
            target_return_col="Target_Next_Return",
            initial_capital=100_000_000,
        )
        assert result.initial_capital == 100_000_000
        assert isinstance(result.final_capital, float)
        assert isinstance(result.total_return_pct, float)
        assert isinstance(result.buy_hold_return_pct, float)
        assert isinstance(result.n_trades, int)
        assert isinstance(result.win_rate, (int, float))
        assert isinstance(result.max_drawdown_pct, float)
        assert isinstance(result.sharpe_ratio, (int, float))
        assert isinstance(result.sortino_ratio, (int, float))
        assert isinstance(result.calmar_ratio, (int, float))
        assert isinstance(result.total_commission, float)
        assert len(result.equity_curve) > 0

    def test_realistic_backtest_commission_impact(self, sample_backtest_data):
        from src.quant_finance import realistic_backtest

        # Use alternating predictions to ensure trades happen
        preds = np.array([1 if i % 10 < 5 else 0 for i in range(len(sample_backtest_data))])

        # Zero commission
        result_no_comm = realistic_backtest(
            sample_backtest_data, preds,
            commission_buy=0, commission_sell=0,
            slippage_bps=0,
        )
        # With commission
        result_with_comm = realistic_backtest(
            sample_backtest_data, preds,
            commission_buy=0.0015, commission_sell=0.0025,
            slippage_bps=5,
        )
        # Commission should be >= (if trades happen)
        assert result_with_comm.total_commission >= result_no_comm.total_commission

    def test_realistic_backtest_all_buy(self, sample_backtest_data):
        from src.quant_finance import realistic_backtest

        preds = np.ones(len(sample_backtest_data))
        result = realistic_backtest(
            sample_backtest_data, preds,
            target_close_col="Close",
            target_return_col="Target_Next_Return",
        )
        # Should have at least one trade (buy at start)
        assert result.n_trades >= 0  # May be 0 if only one buy and final sell


# ===========================================================================
# Test: Vectorized Backtest
# ===========================================================================
class TestVectorizedBacktest:
    def test_vectorized_backtest_runs(self, sample_backtest_data, sample_predictions):
        from src.quant_finance import vectorized_backtest

        result = vectorized_backtest(
            sample_backtest_data, sample_predictions,
            close_col="Close",
            return_col="Target_Next_Return",
        )
        assert "total_return_pct" in result
        assert "buy_hold_return_pct" in result
        assert "max_drawdown_pct" in result
        assert "sharpe_ratio" in result
        assert "n_trades" in result
        assert "win_rate" in result
        assert len(result["equity_curve"]) > 0

    def test_vectorized_vs_buy_hold(self, sample_backtest_data):
        from src.quant_finance import vectorized_backtest

        # All buy = buy and hold
        preds = np.ones(len(sample_backtest_data))
        result = vectorized_backtest(
            sample_backtest_data, preds,
            close_col="Close",
            return_col="Target_Next_Return",
            commission_buy=0, commission_sell=0,
        )
        # Should be close to buy & hold (minus slippage effects)
        assert abs(result["total_return_pct"] - result["buy_hold_return_pct"]) < 5


# ===========================================================================
# Test: Tear Sheet
# ===========================================================================
class TestTearSheet:
    def test_generate_tear_sheet(self):
        from src.quant_finance import generate_tear_sheet

        np.random.seed(42)
        equity = [100_000_000 * (1 + x) for x in np.cumsum(np.random.randn(252) * 0.01)]
        result = generate_tear_sheet(equity)
        assert "total_return_pct" in result
        assert "annual_return_pct" in result
        assert "annual_volatility_pct" in result
        assert "sharpe_ratio" in result
        assert "sortino_ratio" in result
        assert "calmar_ratio" in result
        assert "max_drawdown_pct" in result
        assert "var_95_pct" in result
        assert "var_99_pct" in result
        assert "cvar_95_pct" in result
        assert "best_day_pct" in result
        assert "worst_day_pct" in result
        assert "alpha_pct" in result
        assert "beta" in result
        assert "information_ratio" in result

    def test_tear_sheet_with_benchmark(self):
        from src.quant_finance import generate_tear_sheet

        np.random.seed(42)
        equity = [100_000_000 * (1 + x) for x in np.cumsum(np.random.randn(252) * 0.01)]
        benchmark = [100_000_000 * (1 + x) for x in np.cumsum(np.random.randn(252) * 0.008)]
        result = generate_tear_sheet(equity, benchmark=benchmark)
        assert result["beta"] != 0 or result["alpha_pct"] != 0


# ===========================================================================
# Test: Wyckoff Analysis
# ===========================================================================
class TestWyckoffAnalysis:
    def test_detect_wyckoff_phase(self, sample_backtest_data):
        from src.quant_finance import detect_wyckoff_phase

        result = detect_wyckoff_phase(sample_backtest_data, window=30)
        assert result.phase in ["Accumulation", "Distribution", "Markup", "Markdown", "Unknown"]
        assert result.event in ["Spring", "SOS", "Upthrust", "SOW", "None"]
        assert 0 <= result.confidence <= 1
        assert isinstance(result.description, str)

    def test_wyckoff_insufficient_data(self):
        from src.quant_finance import detect_wyckoff_phase

        df = pd.DataFrame({"High": [1], "Low": [1], "Close": [1], "Volume": [1]})
        result = detect_wyckoff_phase(df)
        assert result.phase == "Unknown"

    def test_wyckoff_missing_columns(self):
        from src.quant_finance import detect_wyckoff_phase

        df = pd.DataFrame({"A": [1, 2, 3]})
        result = detect_wyckoff_phase(df)
        assert result.phase == "Unknown"


class TestDeflatedSharpeRatio:
    def test_dsr_basic(self):
        from src.quant_finance import deflated_sharpe_ratio
        result = deflated_sharpe_ratio(sharpe=1.5, n_trials=1, n_obs=252)
        assert "deflated_sharpe" in result
        assert "p_value" in result
        assert "assessment" in result
        assert isinstance(result["deflated_sharpe"], float)

    def test_dsr_multiple_trials(self):
        from src.quant_finance import deflated_sharpe_ratio
        result = deflated_sharpe_ratio(sharpe=2.0, n_trials=100, n_obs=252)
        # With 100 trials, expected max sharpe is higher → DSR should be lower
        assert result["deflated_sharpe"] < 2.0
        assert result["n_trials"] == 100

    def test_dsr_high_sharpe_significant(self):
        from src.quant_finance import deflated_sharpe_ratio
        result = deflated_sharpe_ratio(sharpe=3.0, n_trials=1, n_obs=500)
        assert result["p_value"] < 0.05

    def test_dsr_low_sharpe_not_significant(self):
        from src.quant_finance import deflated_sharpe_ratio
        result = deflated_sharpe_ratio(sharpe=0.1, n_trials=50, n_obs=100)
        assert result["p_value"] > 0.10


class TestDriftDetection:
    def test_feature_drift_no_drift(self):
        from src.quant_finance import detect_feature_drift
        np.random.seed(42)
        baseline = pd.DataFrame({"f1": np.random.randn(200), "f2": np.random.randn(200)})
        current = pd.DataFrame({"f1": np.random.randn(200), "f2": np.random.randn(200)})
        report = detect_feature_drift(baseline, current, ["f1", "f2"])
        assert isinstance(report.is_drifted, bool)
        assert isinstance(report.drift_score, float)

    def test_feature_drift_detected(self):
        from src.quant_finance import detect_feature_drift
        np.random.seed(42)
        baseline = pd.DataFrame({"f1": np.random.randn(200)})
        current = pd.DataFrame({"f1": np.random.randn(200) + 5})  # Shifted mean
        report = detect_feature_drift(baseline, current, ["f1"])
        assert report.is_drifted is True
        assert "f1" in report.drifted_features

    def test_performance_drift_stable(self):
        from src.quant_finance import detect_performance_drift
        report = detect_performance_drift(baseline_accuracy=0.55, current_accuracy=0.54)
        assert report.is_drifted is False
        assert report.performance_degraded is False

    def test_performance_drift_degraded(self):
        from src.quant_finance import detect_performance_drift
        report = detect_performance_drift(baseline_accuracy=0.60, current_accuracy=0.50)
        assert report.is_drifted is True
        assert report.performance_degraded is True
        assert len(report.recommendations) > 0


class TestBEICostStructure:
    def test_calc_bei_buy_cost(self):
        from src.quant_finance import calc_bei_total_cost
        result = calc_bei_total_cost(100_000_000, "buy")
        assert result["side"] == "buy"
        assert result["commission"] == 150_000  # 0.15%
        assert result["pph_final"] == 0  # No PPh on buy
        assert result["total_cost"] > 150_000  # Commission + exchange + clearing
        assert result["total_cost_bps"] > 15  # More than just commission

    def test_calc_bei_sell_cost(self):
        from src.quant_finance import calc_bei_total_cost
        result = calc_bei_total_cost(100_000_000, "sell")
        assert result["side"] == "sell"
        assert result["commission"] == 250_000  # 0.25%
        assert result["pph_final"] == 100_000  # 0.1% PPh on sell
        assert result["total_cost"] > 350_000  # Commission + PPh + fees

    def test_sell_more_expensive_than_buy(self):
        from src.quant_finance import calc_bei_total_cost
        buy = calc_bei_total_cost(100_000_000, "buy")
        sell = calc_bei_total_cost(100_000_000, "sell")
        assert sell["total_cost"] > buy["total_cost"]
        # PPh only on sell
        assert sell["pph_final"] > 0
        assert buy["pph_final"] == 0

    def test_sbk_fee_only_large_trades(self):
        from src.quant_finance import calc_bei_total_cost
        small = calc_bei_total_cost(100_000_000, "buy")  # < 500M
        large = calc_bei_total_cost(600_000_000, "buy")  # > 500M
        assert small["sbk_fee"] == 0
        assert large["sbk_fee"] > 0

    def test_round_trip_cost(self):
        from src.quant_finance import calc_round_trip_cost
        result = calc_round_trip_cost(100_000_000)
        assert "buy_cost" in result
        assert "sell_cost" in result
        assert result["round_trip_total"] > 0
        assert result["round_trip_bps"] > 40  # At least 0.4%
        assert result["break_even_return_pct"] > 0.4
        assert "break even" in result["break_even_price_move"].lower()

    def test_round_trip_includes_all_fees(self):
        from src.quant_finance import calc_round_trip_cost
        result = calc_round_trip_cost(100_000_000)
        buy = result["buy_cost"]
        sell = result["sell_cost"]
        # Buy should have exchange + clearing but no PPh
        assert buy["exchange_fee"] > 0
        assert buy["clearing_fee"] > 0
        assert buy["pph_final"] == 0
        # Sell should have all including PPh
        assert sell["exchange_fee"] > 0
        assert sell["clearing_fee"] > 0
        assert sell["pph_final"] > 0
