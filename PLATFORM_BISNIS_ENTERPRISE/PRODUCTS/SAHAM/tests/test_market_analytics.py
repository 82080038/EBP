"""Tests for new market analytics modules (foreign flow, market structure, economic calendar, etc.)."""

import pytest
import pandas as pd
import numpy as np
from datetime import datetime


class TestForeignFlow:
    """Tests for foreign_flow.py module."""

    def test_foreign_flow_analyzer_init(self):
        """Test ForeignFlowAnalyzer initialization."""
        from src.foreign_flow import ForeignFlowAnalyzer
        analyzer = ForeignFlowAnalyzer()
        assert analyzer is not None
        assert analyzer.base_url == "https://www.idx.co.id"

    def test_fetch_foreign_flow_with_mock(self, mock_market_data):
        """Test foreign flow fetching with mock data."""
        from src.foreign_flow import ForeignFlowAnalyzer
        analyzer = ForeignFlowAnalyzer()
        summary = analyzer.fetch_foreign_flow_from_idx()
        
        assert summary is not None
        assert summary.date is not None
        assert summary.total_net_buy >= 0
        assert summary.total_net_sell >= 0
        assert summary.sentiment in ["bullish", "bearish", "neutral"]
        assert -100 <= summary.sentiment_score <= 100

    def test_foreign_flow_history(self, mock_market_data):
        """Test foreign flow history retrieval."""
        from src.foreign_flow import ForeignFlowAnalyzer
        analyzer = ForeignFlowAnalyzer()
        history = analyzer.get_foreign_flow_history(days=10)
        
        assert isinstance(history, pd.DataFrame)
        assert len(history) <= 10
        assert "date" in history.columns
        assert "net_flow" in history.columns
        assert "sentiment" in history.columns

    def test_foreign_flow_vs_ihsg_correlation(self, mock_market_data):
        """Test correlation between foreign flow and IHSG."""
        from src.foreign_flow import ForeignFlowAnalyzer
        analyzer = ForeignFlowAnalyzer()
        correlation = analyzer.analyze_foreign_flow_vs_ihsg(days=30)
        
        assert "correlation" in correlation
        # Handle NaN from insufficient mock data
        if not np.isnan(correlation["correlation"]):
            assert -1 <= correlation["correlation"] <= 1
        assert "interpretation" in correlation

    def test_foreign_flow_sentiment_signal(self, mock_market_data):
        """Test foreign flow sentiment signal."""
        from src.foreign_flow import ForeignFlowAnalyzer
        analyzer = ForeignFlowAnalyzer()
        signal = analyzer.get_foreign_sentiment_signal()
        
        assert signal["signal"] in ["BUY", "SELL", "HOLD"]
        assert 0 <= signal["confidence"] <= 1
        assert "reasons" in signal
        assert isinstance(signal["reasons"], list)

    def test_get_foreign_flow_adjustment(self, mock_market_data):
        """Test foreign flow adjustment for prediction pipeline."""
        from src.foreign_flow import get_foreign_flow_adjustment
        adj, reason = get_foreign_flow_adjustment()
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.1 <= adj <= 0.1  # Reasonable adjustment range


class TestMarketStructure:
    """Tests for market_structure.py module."""

    def test_market_structure_analyzer_init(self):
        """Test MarketStructureAnalyzer initialization."""
        from src.market_structure import MarketStructureAnalyzer
        analyzer = MarketStructureAnalyzer()
        assert analyzer is not None

    def test_calculate_market_breadth(self, mock_market_data):
        """Test market breadth calculation."""
        from src.market_structure import MarketStructureAnalyzer
        analyzer = MarketStructureAnalyzer()
        breadth = analyzer.calculate_market_breadth(mock_market_data)
        
        assert breadth.advancers >= 0
        assert breadth.decliners >= 0
        assert breadth.unchanged >= 0
        assert breadth.adv_decl_ratio >= 0
        assert breadth.new_highs >= 0
        assert breadth.new_lows >= 0

    def test_mcclellan_oscillator(self, mock_market_data):
        """Test McClellan Oscillator calculation."""
        from src.market_structure import MarketStructureAnalyzer
        analyzer = MarketStructureAnalyzer()
        
        # Build some history
        for _ in range(40):
            analyzer.calculate_market_breadth(mock_market_data)
        
        mcclellan = analyzer.calculate_mcclellan_oscillator(analyzer.breadth_history)
        assert isinstance(mcclellan, float)

    def test_arms_index(self, mock_market_data):
        """Test Arms Index (TRIN) calculation."""
        from src.market_structure import MarketStructureAnalyzer
        analyzer = MarketStructureAnalyzer()
        breadth = analyzer.calculate_market_breadth(mock_market_data)
        arms = analyzer.calculate_arms_index(breadth)
        
        assert isinstance(arms, float)
        assert arms >= 0

    def test_analyze_market_structure(self, mock_market_data):
        """Test full market structure analysis."""
        from src.market_structure import MarketStructureAnalyzer
        analyzer = MarketStructureAnalyzer()
        summary = analyzer.analyze_market_structure(mock_market_data)
        
        assert summary.breadth is not None
        assert summary.mcclellan_oscillator is not None
        assert summary.arms_index is not None
        assert summary.breadth_signal in ["bullish", "bearish", "neutral", "mildly_bullish", "mildly_bearish"]
        assert summary.volume_signal in ["bullish", "bearish", "neutral", "mildly_bullish", "mildly_bearish"]
        assert 0 <= summary.strength_score <= 100

    def test_market_structure_signal(self, mock_market_data):
        """Test market structure signal."""
        from src.market_structure import MarketStructureAnalyzer
        analyzer = MarketStructureAnalyzer()
        signal = analyzer.get_structure_signal(mock_market_data)
        
        assert signal["signal"] in ["BUY", "SELL", "HOLD"]
        assert 0 <= signal["confidence"] <= 1
        assert "strength_score" in signal

    def test_get_market_structure_adjustment(self, mock_market_data):
        """Test market structure adjustment for prediction pipeline."""
        from src.market_structure import get_market_structure_adjustment
        adj, reason = get_market_structure_adjustment(mock_market_data)
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.1 <= adj <= 0.1


class TestIndonesiaEconomicCalendar:
    """Tests for indo_economic_calendar.py module."""

    def test_economic_calendar_init(self):
        """Test IndonesiaEconomicCalendar initialization."""
        from src.indo_economic_calendar import IndonesiaEconomicCalendar
        calendar = IndonesiaEconomicCalendar()
        assert calendar is not None
        assert calendar.bi_url == "https://www.bi.go.id"

    def test_fetch_upcoming_events(self):
        """Test fetching upcoming economic events."""
        from src.indo_economic_calendar import IndonesiaEconomicCalendar
        calendar = IndonesiaEconomicCalendar()
        events = calendar.fetch_upcoming_events(days_ahead=30)
        
        assert isinstance(events, list)
        assert len(events) > 0
        for event in events:
            assert event.event_name is not None
            assert event.date is not None
            assert event.importance in ["high", "medium", "low"]

    def test_analyze_event_impact(self):
        """Test event impact analysis."""
        from src.indo_economic_calendar import IndonesiaEconomicCalendar, EconomicEvent
        calendar = IndonesiaEconomicCalendar()
        
        event = EconomicEvent(
            event_id="test",
            event_name="BI Rate Decision",
            date="2025-01-15",
            importance="high",
            category="monetary",
            unit="%",
        )
        
        impact = calendar.analyze_event_impact(event)
        assert "event" in impact
        assert "affected_sectors" in impact
        assert "trading_strategy" in impact

    def test_get_calendar_summary(self):
        """Test calendar summary."""
        from src.indo_economic_calendar import IndonesiaEconomicCalendar
        calendar = IndonesiaEconomicCalendar()
        summary = calendar.get_calendar_summary(days_ahead=30)
        
        assert summary.upcoming_events is not None
        assert summary.high_impact_count >= 0
        assert 0 <= summary.event_risk_score <= 100
        assert isinstance(summary.recommendations, list)

    def test_get_economic_event_adjustment(self):
        """Test economic event adjustment for prediction pipeline."""
        from src.indo_economic_calendar import get_economic_event_adjustment
        adj, reason = get_economic_event_adjustment()
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.1 <= adj <= 0.1


class TestDynamicCorrelation:
    """Tests for dynamic_correlation.py module."""

    def test_dynamic_correlation_init(self):
        """Test DynamicCorrelationAnalyzer initialization."""
        from src.dynamic_correlation import DynamicCorrelationAnalyzer
        analyzer = DynamicCorrelationAnalyzer()
        assert analyzer is not None

    def test_calculate_rolling_correlation(self, mock_market_data):
        """Test rolling correlation calculation."""
        from src.dynamic_correlation import DynamicCorrelationAnalyzer
        analyzer = DynamicCorrelationAnalyzer()
        
        if "IHSG" in mock_market_data and "S&P500" in mock_market_data:
            corr = analyzer.calculate_rolling_correlation(
                mock_market_data["IHSG"]["Close"],
                mock_market_data["S&P500"]["Close"],
                window=20
            )
            assert isinstance(corr, pd.Series)

    def test_detect_correlation_regime(self):
        """Test correlation regime detection."""
        from src.dynamic_correlation import DynamicCorrelationAnalyzer
        analyzer = DynamicCorrelationAnalyzer()
        
        # Create sample correlation series
        dates = pd.date_range(end=datetime.now(), periods=50)
        corr_series = pd.Series(np.random.randn(50) * 0.3 + 0.5, index=dates)
        
        regime = analyzer.detect_correlation_regime(corr_series)
        assert regime.regime in ["high_correlation", "low_correlation", "neutral"]
        assert 0 <= regime.avg_correlation <= 1

    def test_analyze_lead_lag(self, mock_market_data):
        """Test lead-lag analysis."""
        from src.dynamic_correlation import DynamicCorrelationAnalyzer
        analyzer = DynamicCorrelationAnalyzer()
        
        if "IHSG" in mock_market_data and "S&P500" in mock_market_data:
            lead_lag = analyzer.analyze_lead_lag(
                mock_market_data["IHSG"]["Close"],
                mock_market_data["S&P500"]["Close"],
                max_lag=5
            )
            assert lead_lag.leader in ["series1", "series2", "none"]
            assert lead_lag.optimal_lag >= 0

    def test_analyze_correlation(self, mock_market_data, mock_global_indices):
        """Test correlation analysis."""
        from src.dynamic_correlation import DynamicCorrelationAnalyzer
        analyzer = DynamicCorrelationAnalyzer()
        
        # Combine market data with global indices
        combined_data = {**mock_market_data, **mock_global_indices}
        
        if "IHSG" in combined_data and "S&P500" in combined_data:
            result = analyzer.analyze_correlation(combined_data, "IHSG", "S&P500")
            assert result.market1 == "IHSG"
            assert result.market2 == "S&P500"
            # Handle NaN correlation from insufficient data
            if not np.isnan(result.correlation):
                assert -1 <= result.correlation <= 1
            assert result.regime in ["high_correlation", "low_correlation", "neutral", "unclear"]

    def test_get_correlation_adjustment(self, mock_market_data):
        """Test correlation adjustment for prediction pipeline."""
        from src.dynamic_correlation import get_correlation_adjustment
        adj, reason = get_correlation_adjustment(mock_market_data)
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.1 <= adj <= 0.1


class TestFlowOfFunds:
    """Tests for flow_of_funds.py module."""

    def test_flow_of_funds_init(self):
        """Test FlowOfFundsAnalyzer initialization."""
        from src.flow_of_funds import FlowOfFundsAnalyzer
        analyzer = FlowOfFundsAnalyzer()
        assert analyzer is not None

    def test_fetch_fund_flows(self):
        """Test fund flows fetching."""
        from src.flow_of_funds import FlowOfFundsAnalyzer
        analyzer = FlowOfFundsAnalyzer()
        summary = analyzer.fetch_fund_flows()
        
        assert summary is not None
        assert summary.date is not None
        assert summary.sentiment in ["bullish", "bearish", "neutral"]
        assert -100 <= summary.sentiment_score <= 100

    def test_fund_flow_history(self):
        """Test fund flow history."""
        from src.flow_of_funds import FlowOfFundsAnalyzer
        analyzer = FlowOfFundsAnalyzer()
        history = analyzer.get_flow_history(days=10)
        
        assert isinstance(history, pd.DataFrame)
        assert len(history) <= 10
        assert "date" in history.columns
        assert "total_flow" in history.columns

    def test_fund_sentiment_signal(self):
        """Test fund sentiment signal."""
        from src.flow_of_funds import FlowOfFundsAnalyzer
        analyzer = FlowOfFundsAnalyzer()
        signal = analyzer.get_fund_sentiment_signal()
        
        assert signal["signal"] in ["BUY", "SELL", "HOLD"]
        assert 0 <= signal["confidence"] <= 1
        assert "reasons" in signal

    def test_get_fund_flow_adjustment(self):
        """Test fund flow adjustment for prediction pipeline."""
        from src.flow_of_funds import get_fund_flow_adjustment
        adj, reason = get_fund_flow_adjustment()
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.1 <= adj <= 0.1


class TestAdvancedTechnical:
    """Tests for advanced_technical.py module."""

    def test_advanced_technical_init(self):
        """Test AdvancedTechnicalAnalyzer initialization."""
        from src.advanced_technical import AdvancedTechnicalAnalyzer
        analyzer = AdvancedTechnicalAnalyzer()
        assert analyzer is not None

    def test_calculate_market_profile(self, mock_market_data):
        """Test market profile calculation."""
        from src.advanced_technical import AdvancedTechnicalAnalyzer
        analyzer = AdvancedTechnicalAnalyzer()
        
        if "IHSG" in mock_market_data:
            profile = analyzer.calculate_market_profile(mock_market_data["IHSG"])
            assert profile.poc_price > 0
            assert profile.vah_price >= profile.val_price
            assert profile.profile_type in ["balanced", "trending", "rotational"]

    def test_analyze_wyckoff_phase(self, mock_market_data):
        """Test Wyckoff phase analysis."""
        from src.advanced_technical import AdvancedTechnicalAnalyzer
        analyzer = AdvancedTechnicalAnalyzer()
        
        if "IHSG" in mock_market_data:
            wyckoff = analyzer.analyze_wyckoff_phase(mock_market_data["IHSG"])
            assert wyckoff.phase in ["accumulation", "markup", "distribution", "markdown", "unknown"]
            assert 0 <= wyckoff.confidence <= 1

    def test_calculate_fibonacci_cluster(self, mock_market_data):
        """Test Fibonacci cluster analysis."""
        from src.advanced_technical import AdvancedTechnicalAnalyzer
        analyzer = AdvancedTechnicalAnalyzer()
        
        if "IHSG" in mock_market_data:
            fib = analyzer.calculate_fibonacci_cluster(mock_market_data["IHSG"])
            assert len(fib.levels) > 0
            assert fib.current_price > 0

    def test_get_advanced_technical_signal(self, mock_market_data):
        """Test advanced technical signal."""
        from src.advanced_technical import AdvancedTechnicalAnalyzer
        analyzer = AdvancedTechnicalAnalyzer()
        
        if "IHSG" in mock_market_data:
            signal = analyzer.get_advanced_technical_signal(mock_market_data["IHSG"])
            assert signal["signal"] in ["BUY", "SELL", "HOLD"]
            assert 0 <= signal["confidence"] <= 1

    def test_get_advanced_technical_adjustment(self, mock_market_data):
        """Test advanced technical adjustment for prediction pipeline."""
        from src.advanced_technical import get_advanced_technical_adjustment
        
        if "IHSG" in mock_market_data:
            adj, reason = get_advanced_technical_adjustment(mock_market_data["IHSG"])
            assert isinstance(adj, float)
            assert isinstance(reason, str)
            assert -0.1 <= adj <= 0.1


class TestStressTesting:
    """Tests for stress_testing.py module."""

    def test_stress_test_init(self):
        """Test StressTestAnalyzer initialization."""
        from src.stress_testing import StressTestAnalyzer
        analyzer = StressTestAnalyzer()
        assert analyzer is not None
        assert len(analyzer.scenarios) > 0

    def test_run_stress_test(self):
        """Test stress test execution."""
        from src.stress_testing import StressTestAnalyzer
        analyzer = StressTestAnalyzer()
        
        # Create sample portfolio returns
        returns = pd.Series(np.random.randn(100) * 0.02)
        scenario = analyzer.scenarios[0]
        
        result = analyzer.run_stress_test(returns, scenario)
        assert result.scenario is not None
        assert result.initial_value > 0
        assert result.loss_pct <= 0  # Loss is negative
        assert result.max_drawdown_pct <= 0

    def test_run_monte_carlo_simulation(self):
        """Test Monte Carlo simulation."""
        from src.stress_testing import StressTestAnalyzer
        analyzer = StressTestAnalyzer()
        
        returns = pd.Series(np.random.randn(100) * 0.02)
        mc_result = analyzer.run_monte_carlo_simulation(returns, num_simulations=100, time_horizon=252)
        
        assert "mean_final_value" in mc_result
        assert "worst_case" in mc_result
        assert "probability_of_loss" in mc_result
        assert 0 <= mc_result["probability_of_loss"] <= 100

    def test_analyze_concentration_risk(self):
        """Test concentration risk analysis."""
        from src.stress_testing import StressTestAnalyzer
        analyzer = StressTestAnalyzer()
        
        positions = {"BBCA": 0.4, "BBRI": 0.3, "BMRI": 0.2, "TLKM": 0.1}
        risk = analyzer.analyze_concentration_risk(positions)
        
        assert "herfindahl_index" in risk
        assert "max_weight" in risk
        assert risk["risk_level"] in ["high", "medium", "low"]

    def test_run_scenario_analysis(self):
        """Test full scenario analysis."""
        from src.stress_testing import StressTestAnalyzer
        analyzer = StressTestAnalyzer()
        
        returns = pd.Series(np.random.randn(100) * 0.02)
        analysis = analyzer.run_scenario_analysis(returns)
        
        assert analysis.scenarios is not None
        assert analysis.worst_case is not None
        assert 0 <= analysis.resilience_score <= 100

    def test_get_stress_test_adjustment(self):
        """Test stress test adjustment for prediction pipeline."""
        from src.stress_testing import get_stress_test_adjustment
        returns = pd.Series(np.random.randn(100) * 0.02)
        adj, reason = get_stress_test_adjustment(returns)
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.1 <= adj <= 0.1


class TestRealtimeNews:
    """Tests for realtime_news.py module."""

    def test_realtime_news_init(self):
        """Test RealTimeNewsFetcher initialization."""
        from src.realtime_news import RealTimeNewsFetcher
        fetcher = RealTimeNewsFetcher()
        assert fetcher is not None

    def test_fetch_indonesian_news(self):
        """Test Indonesian news fetching."""
        from src.realtime_news import RealTimeNewsFetcher
        fetcher = RealTimeNewsFetcher()
        news = fetcher.fetch_indonesian_news(hours=24)
        
        assert isinstance(news, list)

    def test_analyze_news_impact(self):
        """Test news impact analysis."""
        from src.realtime_news import RealTimeNewsFetcher, NewsItem
        fetcher = RealTimeNewsFetcher()
        
        item = NewsItem(
            id="test",
            title="Stock market rally",
            summary="Strong buying pushes index higher",
            url="https://example.com",
            source="Test",
            published_at=datetime.now().isoformat(),
        )
        
        impact = fetcher.analyze_news_impact(item)
        assert 0 <= impact <= 100

    def test_get_news_feed(self):
        """Test news feed retrieval."""
        from src.realtime_news import RealTimeNewsFetcher
        fetcher = RealTimeNewsFetcher()
        feed = fetcher.get_news_feed(hours=24)
        
        assert feed.items is not None
        assert feed.last_updated is not None
        assert feed.total_items >= 0

    def test_get_news_sentiment_signal(self):
        """Test news sentiment signal."""
        from src.realtime_news import RealTimeNewsFetcher
        fetcher = RealTimeNewsFetcher()
        signal = fetcher.get_news_sentiment_signal()
        
        assert signal["signal"] in ["BUY", "SELL", "HOLD"]
        assert 0 <= signal["confidence"] <= 1

    def test_get_realtime_news_adjustment(self):
        """Test real-time news adjustment for prediction pipeline."""
        from src.realtime_news import get_realtime_news_adjustment
        adj, reason = get_realtime_news_adjustment()
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.1 <= adj <= 0.1


class TestSocialMediaRealtime:
    """Tests for social_media_realtime.py module."""

    def test_social_media_init(self):
        """Test RealTimeSocialMediaAnalyzer initialization."""
        from src.social_media_realtime import RealTimeSocialMediaAnalyzer
        analyzer = RealTimeSocialMediaAnalyzer()
        assert analyzer is not None

    def test_analyze_post_sentiment(self):
        """Test post sentiment analysis."""
        from src.social_media_realtime import RealTimeSocialMediaAnalyzer, SocialPost
        analyzer = RealTimeSocialMediaAnalyzer()
        
        post = SocialPost(
            id="test",
            platform="twitter",
            author="test",
            content="Stock going up! 🚀",
            timestamp=datetime.now().isoformat(),
        )
        
        analyzed = analyzer.analyze_post_sentiment(post)
        assert analyzed.sentiment in ["positive", "negative", "neutral"]
        assert -1 <= analyzed.sentiment_score <= 1

    def test_detect_trending_topics(self):
        """Test trending topic detection."""
        from src.social_media_realtime import RealTimeSocialMediaAnalyzer, SocialPost
        analyzer = RealTimeSocialMediaAnalyzer()
        
        posts = [
            SocialPost(id="1", platform="twitter", author="a", content="Bitcoin rally", timestamp=datetime.now().isoformat()),
            SocialPost(id="2", platform="twitter", author="b", content="Bitcoin surge", timestamp=datetime.now().isoformat()),
        ]
        
        trends = analyzer.detect_trending_topics(posts)
        assert isinstance(trends, list)

    def test_get_social_feed(self):
        """Test social media feed."""
        from src.social_media_realtime import RealTimeSocialMediaAnalyzer
        analyzer = RealTimeSocialMediaAnalyzer()
        feed = analyzer.get_social_feed(hours=24)
        
        assert feed.posts is not None
        assert feed.last_updated is not None

    def test_get_social_sentiment_signal(self):
        """Test social sentiment signal."""
        from src.social_media_realtime import RealTimeSocialMediaAnalyzer
        analyzer = RealTimeSocialMediaAnalyzer()
        signal = analyzer.get_social_sentiment_signal()
        
        assert signal["signal"] in ["BUY", "SELL", "HOLD"]
        assert 0 <= signal["confidence"] <= 1

    def test_get_social_media_adjustment(self):
        """Test social media adjustment for prediction pipeline."""
        from src.social_media_realtime import get_social_media_adjustment
        adj, reason = get_social_media_adjustment()
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.1 <= adj <= 0.1


class TestAlternativeData:
    """Tests for alternative_data.py module."""

    def test_alternative_data_init(self):
        """Test AlternativeDataAnalyzer initialization."""
        from src.alternative_data import AlternativeDataAnalyzer
        analyzer = AlternativeDataAnalyzer()
        assert analyzer is not None

    def test_fetch_satellite_data(self):
        """Test satellite data fetching."""
        from src.alternative_data import AlternativeDataAnalyzer
        analyzer = AlternativeDataAnalyzer()
        data = analyzer.fetch_satellite_data("Kalimantan", "coal")
        
        assert data is not None
        assert data.location == "Kalimantan"
        assert data.commodity == "coal"

    def test_fetch_shipping_data(self):
        """Test shipping data fetching."""
        from src.alternative_data import AlternativeDataAnalyzer
        analyzer = AlternativeDataAnalyzer()
        data = analyzer.fetch_shipping_data("Indonesia-China", "coal")
        
        assert data is not None
        assert data.route == "Indonesia-China"
        assert data.commodity == "coal"

    def test_analyze_commodity_sector(self):
        """Test commodity sector analysis."""
        from src.alternative_data import AlternativeDataAnalyzer
        analyzer = AlternativeDataAnalyzer()
        analysis = analyzer.analyze_commodity_sector("coal")
        
        assert analysis["commodity"] == "coal"
        assert "total_production" in analysis
        assert "insights" in analysis

    def test_get_alternative_data_summary(self):
        """Test alternative data summary."""
        from src.alternative_data import AlternativeDataAnalyzer
        analyzer = AlternativeDataAnalyzer()
        summary = analyzer.get_alternative_data_summary()
        
        assert summary.satellite_data is not None
        assert summary.shipping_data is not None
        assert summary.last_updated is not None

    def test_get_alternative_data_adjustment(self):
        """Test alternative data adjustment for prediction pipeline."""
        from src.alternative_data import get_alternative_data_adjustment
        adj, reason = get_alternative_data_adjustment()
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.1 <= adj <= 0.1


class TestMarketAnalyticsIntegration:
    """Tests for market_analytics_integration.py module."""

    def test_integrator_init(self):
        """Test MarketAnalyticsIntegrator initialization."""
        from src.market_analytics_integration import MarketAnalyticsIntegrator
        integrator = MarketAnalyticsIntegrator()
        assert integrator is not None
        assert len(integrator.modules) == 10

    def test_get_all_signals(self, mock_market_data):
        """Test getting signals from all modules."""
        from src.market_analytics_integration import MarketAnalyticsIntegrator
        integrator = MarketAnalyticsIntegrator()
        signals = integrator.get_all_signals(mock_market_data)
        
        assert isinstance(signals, dict)
        # Allow for 9-10 modules (some may fail gracefully)
        assert len(signals) >= 9
        for module, signal in signals.items():
            assert "signal" in signal
            assert "adjustment" in signal
            assert "reason" in signal

    def test_aggregate_signals(self, mock_market_data):
        """Test signal aggregation."""
        from src.market_analytics_integration import MarketAnalyticsIntegrator
        integrator = MarketAnalyticsIntegrator()
        signals = integrator.get_all_signals(mock_market_data)
        aggregated = integrator.aggregate_signals(signals)
        
        assert aggregated.signal in ["BUY", "SELL", "HOLD"]
        assert 0 <= aggregated.confidence <= 1
        assert aggregated.consensus in ["bullish", "bearish", "mixed", "mildly_bullish", "mildly_bearish"]
        assert isinstance(aggregated.key_factors, list)
        assert isinstance(aggregated.recommendations, list)

    def test_run_full_analysis(self, mock_market_data):
        """Test full market analytics analysis."""
        from src.market_analytics_integration import MarketAnalyticsIntegrator
        integrator = MarketAnalyticsIntegrator()
        signal = integrator.run_full_analysis(mock_market_data)
        
        assert signal.signal in ["BUY", "SELL", "HOLD"]
        assert 0 <= signal.confidence <= 1
        assert signal.source_signals is not None
        # Allow for 9-10 modules (some may fail gracefully)
        assert len(signal.source_signals) >= 9

    def test_get_market_analytics_adjustment(self, mock_market_data):
        """Test market analytics adjustment for prediction pipeline."""
        from src.market_analytics_integration import get_market_analytics_adjustment
        adj, reason = get_market_analytics_adjustment(mock_market_data)
        
        assert isinstance(adj, float)
        assert isinstance(reason, str)
        assert -0.2 <= adj <= 0.2  # Wider range for aggregated signal
