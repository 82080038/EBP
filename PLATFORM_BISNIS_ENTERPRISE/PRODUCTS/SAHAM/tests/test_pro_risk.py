"""
Tests for professional risk management modules.
"""

import pytest
import pandas as pd
import numpy as np
from datetime import datetime, timedelta


class TestPreTradeRiskChecker:
    def test_approved_order(self):
        from src.pro_risk import PreTradeRiskChecker, RiskLimits, Order
        limits = RiskLimits(max_position_size=100_000_000, max_order_size=50_000_000)
        checker = PreTradeRiskChecker(limits)
        order = Order(symbol="IHSG", side="BUY", quantity=1000)
        result = checker.check_order(order, current_price=7000)
        assert result.approved
        assert result.checks_passed == 8

    def test_order_size_exceeded(self):
        from src.pro_risk import PreTradeRiskChecker, RiskLimits, Order
        limits = RiskLimits(max_order_size=1_000_000)
        checker = PreTradeRiskChecker(limits)
        order = Order(symbol="IHSG", side="BUY", quantity=1000)
        result = checker.check_order(order, current_price=7000)
        assert not result.approved
        assert "exceeds limit" in result.reason

    def test_lot_size_check(self):
        from src.pro_risk import PreTradeRiskChecker, Order
        checker = PreTradeRiskChecker()
        order = Order(symbol="IHSG", side="BUY", quantity=150)
        result = checker.check_order(order, current_price=7000)
        assert not result.approved
        assert "100" in result.reason

    def test_daily_loss_suspends(self):
        from src.pro_risk import PreTradeRiskChecker, RiskLimits, Order
        limits = RiskLimits(max_daily_loss=1_000_000)
        checker = PreTradeRiskChecker(limits)
        checker.update_pnl(-1_500_000)
        order = Order(symbol="IHSG", side="BUY", quantity=100)
        result = checker.check_order(order, current_price=7000)
        assert not result.approved
        assert "suspended" in result.reason.lower()

    def test_consecutive_losses(self):
        from src.pro_risk import PreTradeRiskChecker, RiskLimits, Order
        limits = RiskLimits(max_consecutive_losses=3)
        checker = PreTradeRiskChecker(limits)
        checker.update_pnl(-100)
        checker.update_pnl(-200)
        checker.update_pnl(-300)
        order = Order(symbol="IHSG", side="BUY", quantity=100)
        result = checker.check_order(order, current_price=7000)
        assert not result.approved
        assert "consecutive" in result.reason.lower()


class TestKillSwitch:
    def test_normal_operation(self):
        from src.pro_risk import KillSwitch
        ks = KillSwitch()
        assert ks.can_trade()
        result = ks.check(daily_pnl=-1000, current_drawdown=0.02)
        assert result

    def test_daily_loss_trigger(self):
        from src.pro_risk import KillSwitch
        ks = KillSwitch()
        ks.check(daily_pnl=-6_000_000, max_daily_loss=5_000_000)
        assert not ks.can_trade()
        assert "Daily loss" in ks.activation_reason

    def test_drawdown_trigger(self):
        from src.pro_risk import KillSwitch
        ks = KillSwitch()
        ks.check(current_drawdown=0.20, max_drawdown_limit=0.15)
        assert not ks.can_trade()
        assert "Drawdown" in ks.activation_reason

    def test_reset_requires_confirmation(self):
        from src.pro_risk import KillSwitch
        ks = KillSwitch()
        ks.check(daily_pnl=-10_000_000, max_daily_loss=5_000_000)
        ks.reset("WRONG")
        assert not ks.can_trade()
        ks.reset("CONFIRM_RESET")
        assert ks.can_trade()

    def test_status(self):
        from src.pro_risk import KillSwitch
        ks = KillSwitch()
        status = ks.status()
        assert "trading_enabled" in status
        assert status["trading_enabled"]


class TestPortfolioRiskMonitor:
    def test_update_and_summary(self):
        from src.pro_risk import PortfolioRiskMonitor
        monitor = PortfolioRiskMonitor()
        np.random.seed(42)
        for i in range(50):
            ret = np.random.randn() * 0.01
            val = 100_000_000 * (1 + ret)
            monitor.update(val, ret)
        summary = monitor.get_risk_summary()
        assert "current_drawdown" in summary
        assert "var_1d_95" in summary
        assert "realized_vol_annualized" in summary

    def test_var_calculation(self):
        from src.pro_risk import PortfolioRiskMonitor
        monitor = PortfolioRiskMonitor()
        np.random.seed(42)
        for i in range(30):
            monitor.update(100_000_000, np.random.randn() * 0.01)
        var = monitor.calculate_var(1)
        assert var > 0  # VaR should be positive (loss)


class TestDrawdownControl:
    def test_no_drawdown(self):
        from src.pro_risk import DrawdownControl
        dc = DrawdownControl()
        result = dc.check(0.02)
        assert result["position_size_multiplier"] == 1.0
        assert result["should_trade"]

    def test_level1_drawdown(self):
        from src.pro_risk import DrawdownControl
        dc = DrawdownControl()
        result = dc.check(0.06)
        assert result["level"] == 1
        assert result["position_size_multiplier"] == 0.75

    def test_level4_drawdown(self):
        from src.pro_risk import DrawdownControl
        dc = DrawdownControl()
        result = dc.check(0.25)
        assert result["level"] == 4
        assert result["position_size_multiplier"] == 0.0
        assert not result["should_trade"]


class TestVolatilityTargeting:
    def test_normal_vol(self):
        from src.pro_risk import VolatilityTargeting
        vt = VolatilityTargeting(target_vol=0.15)
        result = vt.calculate_position_size(0.15)
        assert abs(result["multiplier"] - 1.0) < 0.05

    def test_high_vol_reduces_size(self):
        from src.pro_risk import VolatilityTargeting
        vt = VolatilityTargeting(target_vol=0.15)
        result = vt.calculate_position_size(0.30)
        assert result["multiplier"] < 1.0

    def test_low_vol_increases_size(self):
        from src.pro_risk import VolatilityTargeting
        vt = VolatilityTargeting(target_vol=0.15)
        result = vt.calculate_position_size(0.08)
        assert result["multiplier"] > 1.0

    def test_max_leverage_cap(self):
        from src.pro_risk import VolatilityTargeting
        vt = VolatilityTargeting(target_vol=0.15, max_leverage=1.5)
        result = vt.calculate_position_size(0.05)
        assert result["leverage"] <= 1.5


class TestBlackLitterman:
    def test_bl_allocation(self):
        from src.pro_risk import black_litterman_allocation
        np.random.seed(42)
        n = 3
        cov = np.cov(np.random.randn(100, n), rowvar=False)
        weights = np.array([0.4, 0.3, 0.3])
        views = np.array([0.05, 0.03, 0.07])
        confidences = np.array([0.8, 0.6, 0.7])
        result = black_litterman_allocation(weights, cov, views, confidences)
        assert "bl_weights" in result
        assert "bl_expected_returns" in result
        total = sum(result["bl_weights"].values())
        assert abs(total - 1.0) < 0.1

    def test_bl_no_views(self):
        from src.pro_risk import black_litterman_allocation
        np.random.seed(42)
        n = 3
        cov = np.cov(np.random.randn(100, n), rowvar=False)
        weights = np.array([0.4, 0.3, 0.3])
        result = black_litterman_allocation(weights, cov)
        assert "bl_weights" in result


class TestRiskParity:
    def test_risk_parity(self):
        from src.pro_risk import risk_parity_allocation
        np.random.seed(42)
        n = 4
        cov = np.cov(np.random.randn(200, n), rowvar=False)
        result = risk_parity_allocation(cov)
        assert "weights" in result
        assert "risk_contributions" in result
        assert result["is_equal_risk"]

    def test_risk_pity_equal_contribution(self):
        from src.pro_risk import risk_parity_allocation
        # Diagonal covariance — should give inverse-vol weights
        cov = np.diag([0.04, 0.09, 0.01])
        result = risk_parity_allocation(cov)
        rc = list(result["risk_contributions"].values())
        # All risk contributions should be ~33%
        for r in rc:
            assert abs(r - 33.33) < 8.0


class TestRebalancing:
    def test_no_rebalance_needed(self):
        from src.pro_risk import check_rebalance_needed
        current = {"A": 0.40, "B": 0.30, "C": 0.30}
        target = {"A": 0.40, "B": 0.30, "C": 0.30}
        result = check_rebalance_needed(current, target)
        assert not result.should_rebalance

    def test_drift_triggers_rebalance(self):
        from src.pro_risk import check_rebalance_needed
        current = {"A": 0.50, "B": 0.25, "C": 0.25}
        target = {"A": 0.33, "B": 0.33, "C": 0.34}
        result = check_rebalance_needed(current, target, drift_threshold=0.05)
        assert result.should_rebalance
        assert len(result.recommended_trades) > 0

    def test_time_based_rebalance(self):
        from src.pro_risk import check_rebalance_needed
        current = {"A": 0.40, "B": 0.30, "C": 0.30}
        target = {"A": 0.40, "B": 0.30, "C": 0.30}
        result = check_rebalance_needed(current, target, time_since_last=100, max_days=90)
        assert result.should_rebalance
        assert "Time-based" in result.reason


class TestPerformanceAttribution:
    def test_attribution(self):
        from src.pro_risk import performance_attribution
        np.random.seed(42)
        n = 252
        portfolio = pd.Series(np.random.randn(n) * 0.01 + 0.0005)
        benchmark = pd.Series(np.random.randn(n) * 0.01 + 0.0003)
        result = performance_attribution(portfolio, benchmark)
        assert "total_return" in result
        assert "benchmark_return" in result
        assert "excess_return" in result
        assert "information_ratio" in result
        assert "alpha" in result
        assert "beta" in result


class TestTradeJournal:
    def test_log_and_summary(self):
        from src.pro_risk import TradeJournal
        journal = TradeJournal()
        journal.trades = []  # Clear
        journal.log_trade("IHSG", "BUY", 1000, 7000, signal="BUY", confidence=0.65)
        journal.log_trade("IHSG", "SELL", 1000, 7200, signal="SELL", confidence=0.70)
        summary = journal.get_summary()
        assert summary["total_trades"] >= 2


class TestPostTradeTCA:
    def test_tca_analysis(self):
        from src.pro_risk import post_trade_tca
        trades = [
            {"symbol": "IHSG", "side": "BUY", "price": 7000, "quantity": 1000, "value": 7_000_000, "cost": 15_000},
            {"symbol": "IHSG", "side": "SELL", "price": 7100, "quantity": 1000, "value": 7_100_000, "cost": 25_000},
        ]
        benchmark = {"IHSG": 7050}
        result = post_trade_tca(trades, benchmark)
        assert "avg_cost_bps" in result
        assert "implementation_shortfall_bps" in result
        assert "execution_quality" in result


class TestRiskGovernance:
    def test_evaluate_trade_approved(self):
        from src.pro_risk import RiskGovernance, Order
        gov = RiskGovernance(capital=100_000_000)
        order = Order(symbol="IHSG", side="BUY", quantity=1000)
        result = gov.evaluate_trade(order, current_price=7000, signal="BUY", confidence=0.65)
        assert "approved" in result
        assert "pre_trade_checks" in result
        assert "drawdown_control" in result
        assert "volatility_targeting" in result
        assert "final_position_size_multiplier" in result

    def test_evaluate_trade_kill_switch(self):
        from src.pro_risk import RiskGovernance, Order
        gov = RiskGovernance(capital=100_000_000)
        gov.kill_switch.check(daily_pnl=-100_000_000, max_daily_loss=5_000_000)
        order = Order(symbol="IHSG", side="BUY", quantity=100)
        result = gov.evaluate_trade(order, current_price=7000)
        assert not result["approved"]
        assert "KILL SWITCH" in result["reason"]

    def test_full_report(self):
        from src.pro_risk import RiskGovernance
        gov = RiskGovernance(capital=100_000_000)
        report = gov.get_full_report()
        assert "capital" in report
        assert "kill_switch" in report
        assert "portfolio_risk" in report
        assert "drawdown_control" in report
        assert "trade_journal" in report
        assert "risk_limits" in report
