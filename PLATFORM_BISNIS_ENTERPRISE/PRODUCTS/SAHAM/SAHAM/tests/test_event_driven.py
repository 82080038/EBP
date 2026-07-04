"""
Tests for event-driven data layer module.
"""

import pytest
import pandas as pd
import numpy as np
import sys
import os
from datetime import datetime, timedelta
from unittest.mock import patch, MagicMock

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))


@pytest.fixture
def sample_market_data():
    np.random.seed(42)
    n = 200
    dates = pd.bdate_range(start="2025-01-01", periods=n)
    close = 1000 + np.cumsum(np.random.randn(n) * 10)
    volume = np.abs(200000 + np.random.randn(n) * 50000)
    return {
        "IHSG": pd.DataFrame({
            "Open": close - 2, "High": close + 5, "Low": close - 5,
            "Close": close, "Volume": volume,
        }, index=dates),
    }


class TestForeignFlowProxy:
    def test_returns_signal(self, sample_market_data):
        from src.event_driven import estimate_foreign_flow_proxy
        result = estimate_foreign_flow_proxy(sample_market_data["IHSG"])
        assert "signal" in result
        assert "confidence" in result
        assert result["signal"] in [
            "foreign_net_buy", "foreign_net_sell", "domestic_buy",
            "domestic_sell", "low_activity", "unknown",
        ]
        assert 0 <= result["confidence"] <= 100

    def test_insufficient_data(self):
        from src.event_driven import estimate_foreign_flow_proxy
        small = pd.DataFrame({"Close": [100, 101], "Volume": [1000, 2000]})
        result = estimate_foreign_flow_proxy(small)
        assert result["signal"] == "unknown"


class TestEconomicCalendar:
    def test_returns_list_or_empty(self):
        from src.event_driven import fetch_economic_calendar
        events = fetch_economic_calendar("indonesia")
        assert isinstance(events, list)

    def test_event_structure(self):
        from src.event_driven import EconomicEvent
        e = EconomicEvent(time="07:30", country="ID", event="Rate Decision")
        assert e.importance == ""
        assert e.is_today is False


class TestEventDrivenDataclasses:
    def test_earnings_event(self):
        from src.event_driven import EarningsEvent
        e = EarningsEvent(ticker="BBCA.JK", earnings_date="2025-07-30")
        assert e.ticker == "BBCA.JK"
        assert e.is_upcoming is False

    def test_corporate_action(self):
        from src.event_driven import CorporateAction
        a = CorporateAction(ticker="BBCA.JK", date="2025-06-17",
                           action_type="dividend", value=50.0)
        assert a.action_type == "dividend"
        assert a.is_upcoming is False

    def test_analyst_recommendation(self):
        from src.event_driven import AnalystRecommendation
        r = AnalystRecommendation(ticker="BBCA.JK", recommendation_key="strong_buy",
                                  recommendation_mean=1.28, target_mean=8653)
        assert r.recommendation_key == "strong_buy"

    def test_news_sentiment_summary(self):
        from src.event_driven import NewsSentimentSummary
        ns = NewsSentimentSummary(total_articles=10, sentiment_score=-20)
        assert ns.total_articles == 10
        assert ns.error is None


class TestRunEventDrivenAnalysis:
    def test_full_analysis(self, sample_market_data):
        from src.event_driven import run_event_driven_analysis, EventDrivenAnalysis
        # Use include_news=False and include_economic=False to avoid network calls
        result = run_event_driven_analysis(
            sample_market_data,
            target_ticker="^JKSE",
            include_news=False,
            include_economic=False,
        )
        assert isinstance(result, EventDrivenAnalysis)
        assert isinstance(result.event_risk_score, (int, float))
        assert 0 <= result.event_risk_score <= 100
        assert isinstance(result.recommendations, list)
        assert isinstance(result.foreign_flow_proxy, dict)
        assert "signal" in result.foreign_flow_proxy

    def test_summary_string(self, sample_market_data):
        from src.event_driven import run_event_driven_analysis
        result = run_event_driven_analysis(
            sample_market_data,
            include_news=False,
            include_economic=False,
        )
        assert isinstance(result.summary, str)
        assert len(result.summary) > 0


class TestIntegration:
    def test_predictor_imports_event_driven(self):
        import src.predictor as pred
        assert hasattr(pred, 'run_event_driven_analysis')

    def test_event_driven_module_imports(self):
        from src.event_driven import (
            run_event_driven_analysis,
            fetch_earnings_calendar,
            fetch_corporate_actions,
            fetch_analyst_recommendations,
            fetch_economic_calendar,
            fetch_news_sentiment,
            estimate_foreign_flow_proxy,
        )
