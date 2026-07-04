"""
Tests for IDX market rules and regime detection.
"""

import pytest
import pandas as pd
import numpy as np
from datetime import datetime, timedelta


# =============================================================================
# IDX RULES TESTS
# =============================================================================

class TestAutoRejection:
    def test_within_limits(self):
        from src.idx_rules import check_auto_rejection
        result = check_auto_rejection(7000, 6900, "main_board")
        assert not result.is_rejected
        assert result.direction == "none"

    def test_upper_rejection(self):
        from src.idx_rules import check_auto_rejection
        result = check_auto_rejection(7800, 7000, "main_board")
        assert result.is_rejected
        assert result.direction == "upper"
        assert result.limit_pct == 10.0

    def test_lower_rejection(self):
        from src.idx_rules import check_auto_rejection
        result = check_auto_rejection(5800, 7000, "main_board")
        assert result.is_rejected
        assert result.direction == "lower"
        assert result.limit_pct == 15.0

    def test_special_price_limits(self):
        from src.idx_rules import check_auto_rejection
        # Stock under Rp 200 has 20% limits
        result = check_auto_rejection(250, 200, "main_board", is_special_price=True)
        assert result.is_rejected  # 25% > 20% — should reject
        result2 = check_auto_rejection(240, 200, "main_board", is_special_price=True)
        assert result2.is_rejected  # 20% = limit

    def test_zero_reference(self):
        from src.idx_rules import check_auto_rejection
        result = check_auto_rejection(7000, 0, "main_board")
        assert not result.is_rejected


class TestCircuitBreaker:
    def test_no_halt(self):
        from src.idx_rules import check_circuit_breaker
        result = check_circuit_breaker(7000, 7100)
        assert not result.is_halted
        assert result.halt_level == 0

    def test_level1_halt(self):
        from src.idx_rules import check_circuit_breaker
        result = check_circuit_breaker(6700, 7100)  # -5.6%
        assert result.is_halted
        assert result.halt_level >= 1
        assert result.halt_minutes == 30

    def test_suspend(self):
        from src.idx_rules import check_circuit_breaker
        result = check_circuit_breaker(5500, 7100)  # -22.5%
        assert result.is_halted
        # At 22.5% drop, should hit level 4 (20% threshold)
        assert result.halt_level == 4

    def test_exact_5_percent(self):
        from src.idx_rules import check_circuit_breaker
        result = check_circuit_breaker(6905, 7270)  # ~-5%
        # Should trigger at exactly 5%
        if result.ihsg_drop_pct >= 5.0:
            assert result.is_halted


class TestSettlement:
    def test_t2_settlement(self):
        from src.idx_rules import calc_settlement_date
        # Monday
        monday = datetime(2025, 6, 16)  # Monday
        result = calc_settlement_date(monday, days=2)
        # T+2 = Wednesday (no holidays in this week)
        assert result.settlement_date.weekday() == 2  # Wednesday
        assert result.capital_frozen_days >= 2

    def test_t2_with_weekend(self):
        from src.idx_rules import calc_settlement_date
        # Friday
        friday = datetime(2025, 6, 20)  # Friday
        result = calc_settlement_date(friday, days=2)
        # T+2 = Tuesday (skip weekend)
        assert result.settlement_date.weekday() == 1  # Tuesday

    def test_is_trading_day_weekend(self):
        from src.idx_rules import is_trading_day
        saturday = datetime(2025, 6, 21)
        assert not is_trading_day(saturday)

    def test_is_trading_day_weekday(self):
        from src.idx_rules import is_trading_day
        monday = datetime(2025, 6, 16)
        assert is_trading_day(monday)

    def test_is_trading_day_holiday(self):
        from src.idx_rules import is_trading_day
        new_year = datetime(2025, 1, 1)
        assert not is_trading_day(new_year)


class TestBacktestWithIDXRules:
    def test_backtest_runs(self):
        from src.idx_rules import backtest_with_idx_rules
        np.random.seed(42)
        n = 100
        df = pd.DataFrame({
            "Close": 7000 + np.cumsum(np.random.randn(n) * 50),
            "Target_Next_Return": np.random.randn(n) * 0.01,
        }, index=pd.date_range("2025-01-01", periods=n))
        preds = np.random.randint(0, 2, n)
        result = backtest_with_idx_rules(df, preds, "Close", "Target_Next_Return")
        assert "total_return_pct" in result
        assert "num_trades" in result
        assert "rejected_trades" in result
        assert "halted_days" in result


# =============================================================================
# REGIME DETECTION TESTS
# =============================================================================

class TestRegimeDetection:
    def _make_bull_data(self, n=300):
        np.random.seed(42)
        prices = 7000 + np.cumsum(np.random.randn(n) * 20 + 5)  # Upward trend
        return pd.DataFrame({
            "IHSG_Close": prices,
            "IHSG_Volume": np.random.randint(1e8, 5e8, n),
            "VIX_Close": np.random.uniform(10, 18, n),
        }, index=pd.date_range("2025-01-01", periods=n))

    def _make_bear_data(self, n=300):
        np.random.seed(42)
        prices = 7000 + np.cumsum(np.random.randn(n) * 30 - 8)  # Downward trend
        return pd.DataFrame({
            "IHSG_Close": prices,
            "IHSG_Volume": np.random.randint(1e8, 5e8, n),
            "VIX_Close": np.random.uniform(25, 40, n),
        }, index=pd.date_range("2025-01-01", periods=n))

    def test_bull_regime(self):
        from src.regime import detect_market_regime
        df = self._make_bull_data()
        regime = detect_market_regime(df)
        assert regime.current_regime in ["bull", "bullish_sideways"]
        assert regime.regime_score > 0

    def test_bear_regime(self):
        from src.regime import detect_market_regime
        df = self._make_bear_data()
        regime = detect_market_regime(df)
        assert regime.current_regime in ["bear", "bearish_sideways", "sideways"]
        assert regime.regime_score < 20

    def test_regime_recommendations(self):
        from src.regime import detect_market_regime
        df = self._make_bull_data()
        regime = detect_market_regime(df)
        assert len(regime.recommendations) > 0
        assert isinstance(regime.should_trade, bool)
        assert 0 <= regime.position_size_multiplier <= 1.0

    def test_regime_adjusted_prediction(self):
        from src.regime import regime_adjusted_prediction
        df = self._make_bear_data()
        result = regime_adjusted_prediction(df, "BUY", 0.50)
        assert "original_signal" in result
        assert "adjusted_signal" in result
        assert "regime" in result
        # In bear market with low confidence, BUY should be downgraded
        assert result["adjusted_signal"] == "HOLD"

    def test_regime_no_data(self):
        from src.regime import detect_market_regime
        df = pd.DataFrame({"OTHER_Close": [1, 2, 3]})
        regime = detect_market_regime(df, close_col="IHSG_Close")
        assert regime.current_regime == "unknown"

    def test_crisis_detection(self):
        from src.regime import detect_market_regime
        np.random.seed(42)
        n = 300
        # Sharp decline + extreme VIX
        prices = 7000 - np.arange(n) * 15  # Linear decline
        df = pd.DataFrame({
            "IHSG_Close": prices,
            "VIX_Close": [45] * n,  # Extreme VIX
        }, index=pd.date_range("2025-01-01", periods=n))
        regime = detect_market_regime(df)
        assert regime.current_regime in ["bear", "crisis"]
        assert regime.regime_score < 0
