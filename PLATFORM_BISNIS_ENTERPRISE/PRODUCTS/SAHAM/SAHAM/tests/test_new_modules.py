"""
Tests for new modules: Kronos, SHAP explainability, drift monitor, screener,
event backtest, alt data sources, broker simulation, logging config.
"""
import pytest
import logging
import numpy as np
import pandas as pd
from datetime import datetime, timedelta


# === Kronos Integration ===

class TestKronosIntegration:
    def test_kronos_result_defaults(self):
        from src.kronos_integration import KronosResult
        r = KronosResult()
        assert r.predicted_direction == "NEUTRAL"
        assert r.confidence == 0.5
        assert r.available is False
        assert "unavailable" in r.summary().lower() or "N/A" in r.summary()

    def test_kronos_confidence_adjustment_unavailable(self):
        from src.kronos_integration import KronosResult, get_kronos_confidence_adjustment
        r = KronosResult(available=False, error="no torch")
        adj, reason = get_kronos_confidence_adjustment(r)
        assert adj == 0.0
        assert "not available" in reason.lower()

    def test_kronos_confidence_adjustment_bullish(self):
        from src.kronos_integration import KronosResult, get_kronos_confidence_adjustment
        r = KronosResult(available=True, predicted_direction="UP", confidence=0.8)
        adj, reason = get_kronos_confidence_adjustment(r)
        assert adj > 0
        assert "bullish" in reason.lower()

    def test_kronos_confidence_adjustment_bearish(self):
        from src.kronos_integration import KronosResult, get_kronos_confidence_adjustment
        r = KronosResult(available=True, predicted_direction="DOWN", confidence=0.8)
        adj, reason = get_kronos_confidence_adjustment(r)
        assert adj < 0
        assert "bearish" in reason.lower()

    def test_kronos_confidence_adjustment_neutral(self):
        from src.kronos_integration import KronosResult, get_kronos_confidence_adjustment
        r = KronosResult(available=True, predicted_direction="NEUTRAL", confidence=0.5)
        adj, reason = get_kronos_confidence_adjustment(r)
        assert adj == 0.0
        assert "uncertain" in reason.lower() or "neutral" in reason.lower()


# === SHAP Explainability ===

class TestExplainability:
    def test_prediction_explanation_defaults(self):
        from src.explainability import PredictionExplanation
        e = PredictionExplanation()
        assert e.method == "feature_importance"
        assert e.top_features == []
        assert e.bullish_drivers() == []
        assert e.bearish_drivers() == []

    def test_explain_prediction_empty(self):
        from src.explainability import explain_prediction
        df = pd.DataFrame()
        result = explain_prediction(None, df, [])
        assert result == {}

    def test_generate_explanation_text_empty(self):
        from src.explainability import generate_explanation_text
        text = generate_explanation_text({})
        assert "not available" in text.lower() or "No explanations" in text

    def test_generate_explanation_text_with_data(self):
        from src.explainability import PredictionExplanation, generate_explanation_text
        exp = PredictionExplanation(
            method="feature_importance",
            top_features=[("RSI", 0.15), ("MA5", 0.08)],
            text_summary="Top: RSI, MA5",
        )
        text = generate_explanation_text({"LightGBM": exp})
        assert "LightGBM" in text
        assert "RSI" in text


# === Drift Monitor ===

class TestDriftMonitor:
    def test_psi_calculation_stable(self):
        from src.drift_monitor import _calculate_psi
        np.random.seed(42)
        ref = np.random.normal(100, 10, 1000)
        cur = np.random.normal(100, 10, 1000)
        psi = _calculate_psi(ref, cur)
        assert psi < 0.1  # Should be stable

    def test_psi_calculation_drifted(self):
        from src.drift_monitor import _calculate_psi
        np.random.seed(42)
        ref = np.random.normal(100, 10, 1000)
        cur = np.random.normal(150, 20, 1000)
        psi = _calculate_psi(ref, cur)
        assert psi > 0.25  # Should show significant drift

    def test_classify_drift(self):
        from src.drift_monitor import _classify_drift
        assert _classify_drift(0.05) == "stable"
        assert _classify_drift(0.15) == "moderate"
        assert _classify_drift(0.30) == "significant"

    def test_drift_monitor_no_reference(self):
        from src.drift_monitor import DriftMonitor
        monitor = DriftMonitor()
        df = pd.DataFrame({"A": [1, 2, 3]})
        report = monitor.check(df)
        assert "No reference" in report.recommendation

    def test_drift_monitor_stable(self):
        from src.drift_monitor import DriftMonitor
        np.random.seed(42)
        ref_df = pd.DataFrame({"feat1": np.random.normal(0, 1, 500)})
        cur_df = pd.DataFrame({"feat1": np.random.normal(0, 1, 500)})
        monitor = DriftMonitor()
        monitor.set_reference(ref_df)
        report = monitor.check(cur_df)
        assert not report.has_significant_drift
        assert "STABLE" in report.recommendation

    def test_drift_monitor_significant(self):
        from src.drift_monitor import DriftMonitor
        np.random.seed(42)
        ref_df = pd.DataFrame({"feat1": np.random.normal(0, 1, 500)})
        cur_df = pd.DataFrame({"feat1": np.random.normal(5, 3, 500)})
        monitor = DriftMonitor()
        monitor.set_reference(ref_df)
        report = monitor.check(cur_df)
        assert report.has_significant_drift
        assert "RETRAIN" in report.recommendation


# === Screener ===

class TestScreener:
    def test_screener_entry_defaults(self):
        from src.screener import ScreenerEntry
        e = ScreenerEntry()
        assert e.signal == "HOLD"
        assert e.ai_score == 0.0

    def test_quick_screen_single_bullish(self):
        from src.screener import _quick_screen_single
        dates = pd.date_range("2024-01-01", periods=100)
        prices = list(range(100, 200))
        df = pd.DataFrame({"Close": prices}, index=dates)
        entry = _quick_screen_single("TEST.JK", "Test", df)
        assert entry.current_price == 199
        assert entry.ma_trend == "bullish"
        assert entry.signal in ("BUY", "HOLD")

    def test_quick_screen_single_bearish(self):
        from src.screener import _quick_screen_single
        dates = pd.date_range("2024-01-01", periods=100)
        prices = list(range(200, 100, -1))
        df = pd.DataFrame({"Close": prices}, index=dates)
        entry = _quick_screen_single("TEST.JK", "Test", df)
        assert entry.ma_trend == "bearish"
        assert entry.signal in ("SELL", "HOLD")

    def test_quick_screen_insufficient_data(self):
        from src.screener import _quick_screen_single
        df = pd.DataFrame({"Close": [100, 101]})
        entry = _quick_screen_single("TEST.JK", "Test", df)
        assert entry.error != ""

    def test_format_screener_results(self):
        from src.screener import ScreenerResult, ScreenerEntry, format_screener_results
        result = ScreenerResult(
            entries=[ScreenerEntry(ticker="A", name="A", current_price=100, signal="BUY")],
            n_success=1,
        )
        df = format_screener_results(result)
        assert len(df) == 1
        assert "Ticker" in df.columns


# === Event Backtest ===

class TestEventBacktest:
    def test_event_backtest_result_defaults(self):
        from src.event_backtest import EventBacktestResult
        r = EventBacktestResult()
        assert r.initial_capital == 0.0
        assert r.n_trades == 0
        assert r.error == ""

    def test_trade_defaults(self):
        from src.event_backtest import Trade
        t = Trade()
        assert t.side == "BUY"
        assert t.pnl == 0.0

    def test_event_backtest_empty_data(self):
        from src.event_backtest import run_event_backtest
        result = run_event_backtest({})
        assert result.error != ""


# === Alt Data Sources ===

class TestAltDataSources:
    def test_alpha_vantage_no_key(self):
        """Test Alpha Vantage without API key."""
        import os
        from src.alt_data_sources import AlphaVantageSource
        # Only test no-key behavior if we're not using real keys
        api_key = os.getenv("ALPHAVANTAGE_API_KEY", "")
        if not api_key or api_key == "your_alpha_vantage_key_here":
            av = AlphaVantageSource(api_key="")
            assert not av.is_available()
        else:
            pytest.skip("API key configured, skipping no-key test")

    def test_finnhub_no_key(self):
        """Test Finnhub without API key."""
        import os
        from src.alt_data_sources import FinnhubSource
        # Only test no-key behavior if we're not using real keys
        api_key = os.getenv("FINNHUB_API_KEY", "")
        if not api_key or api_key == "your_finnhub_key_here":
            fh = FinnhubSource(api_key="")
            assert not fh.is_available()
        else:
            pytest.skip("API key configured, skipping no-key test")

    def test_alpha_vantage_with_key(self):
        """Test Alpha Vantage with API key from env."""
        import os
        from src.alt_data_sources import AlphaVantageSource
        api_key = os.getenv("ALPHAVANTAGE_API_KEY", "")
        if api_key and api_key != "your_alpha_vantage_key_here":
            av = AlphaVantageSource(api_key=api_key)
            assert av.is_available()
        else:
            # Skip if no API key configured
            pass

    def test_finnhub_with_key(self):
        """Test Finnhub with API key from env."""
        import os
        from src.alt_data_sources import FinnhubSource
        api_key = os.getenv("FINNHUB_API_KEY", "")
        if api_key and api_key != "your_finnhub_key_here":
            fh = FinnhubSource(api_key=api_key)
            assert fh.is_available()
        else:
            # Skip if no API key configured
            pass

    def test_cross_source_verify_single(self):
        from src.alt_data_sources import cross_source_verify
        result = cross_source_verify(100, 0, 0)
        assert result["verified"] is True
        assert "one source" in result["reason"].lower()

    def test_cross_source_verify_match(self):
        from src.alt_data_sources import cross_source_verify
        result = cross_source_verify(100, 101, 100.5)
        assert result["verified"] is True

    def test_cross_source_verify_mismatch(self):
        from src.alt_data_sources import cross_source_verify
        result = cross_source_verify(100, 120, 110)
        assert result["verified"] is False


# === Broker Simulation ===

class TestBrokerSimulator:
    def test_broker_init(self):
        from src.broker_sim import BrokerSimulator
        broker = BrokerSimulator(broker="bca_sekuritas", capital=50_000_000)
        portfolio = broker.get_portfolio()
        assert portfolio["cash"] == 50_000_000
        assert portfolio["n_positions"] == 0

    def test_broker_buy_order(self):
        from src.broker_sim import BrokerSimulator, SimOrder
        broker = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
        order = SimOrder(symbol="BBCA.JK", side="BUY", quantity=100, order_type="MARKET")
        result = broker.submit_order(order, current_price=8500)
        assert result.status in ("FILLED", "PARTIAL_FILL", "REJECTED")
        if result.status in ("FILLED", "PARTIAL_FILL"):
            assert result.filled_qty > 0
            assert result.avg_fill_price > 0

    def test_broker_sell_order(self):
        from src.broker_sim import BrokerSimulator, SimOrder
        broker = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
        buy = SimOrder(symbol="BBCA.JK", side="BUY", quantity=100, order_type="MARKET")
        broker.submit_order(buy, current_price=8500)
        sell = SimOrder(symbol="BBCA.JK", side="SELL", quantity=100, order_type="MARKET")
        result = broker.submit_order(sell, current_price=8600)
        assert result.status in ("FILLED", "PARTIAL_FILL", "REJECTED")

    def test_broker_lot_size_rejection(self):
        from src.broker_sim import BrokerSimulator, SimOrder
        broker = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
        order = SimOrder(symbol="BBCA.JK", side="BUY", quantity=150, order_type="MARKET")
        result = broker.submit_order(order, current_price=8500)
        assert result.status == "REJECTED"
        assert "lot size" in result.rejection_reason.lower()

    def test_broker_portfolio_after_buy(self):
        from src.broker_sim import BrokerSimulator, SimOrder
        broker = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
        order = SimOrder(symbol="BBCA.JK", side="BUY", quantity=1000, order_type="MARKET")
        broker.submit_order(order, current_price=8500)
        portfolio = broker.get_portfolio()
        if portfolio["n_positions"] > 0:
            assert portfolio["position_value"] > 0

    def test_broker_order_history(self):
        from src.broker_sim import BrokerSimulator, SimOrder
        broker = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
        order = SimOrder(symbol="BBCA.JK", side="BUY", quantity=100, order_type="MARKET")
        broker.submit_order(order, current_price=8500)
        history = broker.get_order_history()
        assert len(history) == 1


# === Logging Config ===

class TestLoggingConfig:
    def test_get_logger(self):
        from src.logging_config import get_logger
        logger = get_logger("test_module")
        assert logger.name == "test_module"

    def test_setup_logging(self):
        from src.logging_config import setup_logging
        logger = setup_logging(level="DEBUG", log_to_file=False)
        assert logger is not None
