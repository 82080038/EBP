"""Pytest configuration — suppress cosmetic warnings and mock market data."""
import os
import warnings
from datetime import datetime, timedelta

import numpy as np
import pandas as pd
import pytest

# Suppress sklearn UserWarning about LightGBM feature names
# This is a cosmetic issue: MinMaxScaler output (numpy) passed to LightGBM
# which was fitted with DataFrame feature names. Predictions are correct.
warnings.filterwarnings("ignore", message="X does not have valid feature names", category=UserWarning)

# Standalone scripts under tests/ that require a live backend/frontend (and
# optionally Playwright). They are meant to be run manually via `python tests/<file>.py`
# and must not be auto-collected by pytest, which has no running servers in CI.
collect_ignore = [
    os.path.join("tests", "test_api_comprehensive.py"),
    os.path.join("tests", "test_command_center_e2e.py"),
    os.path.join("tests", "test_tabs_data.py"),
]

# ---------------------------------------------------------------------------
# MOCK MARKET DATA (untuk test — tidak mengunduh dari internet)
# ---------------------------------------------------------------------------

def _make_ohlcv(ticker: str, n: int = 300, seed: int = 42, regime: str = "normal") -> pd.DataFrame:
    """
    Generate deterministic daily OHLCV data for a ticker.
    
    Args:
        ticker: Ticker symbol
        n: Number of days
        seed: Random seed
        regime: Market regime - "normal", "bullish", "bearish", "volatile"
    """
    rng = np.random.default_rng(seed + hash(ticker) % 10000)
    base = 1000 + (hash(ticker) % 1000)
    dates = pd.date_range(end=datetime.now().date(), periods=n, freq="B")
    
    # Adjust parameters based on regime
    if regime == "bullish":
        returns = rng.normal(0.0015, 0.012, n)  # Positive drift, lower volatility
    elif regime == "bearish":
        returns = rng.normal(-0.0015, 0.018, n)  # Negative drift, higher volatility
    elif regime == "volatile":
        returns = rng.normal(0.0005, 0.025, n)  # High volatility
    else:  # normal
        returns = rng.normal(0.0005, 0.015, n)
    
    close = base * np.exp(np.cumsum(returns))
    noise = rng.uniform(0.005, 0.02, n)
    high = close * (1 + noise)
    low = close * (1 - noise)
    open_ = close * (1 + rng.normal(0, 0.005, n))
    volume = rng.integers(1_000_000, 50_000_000, n)
    
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


def _make_market_data() -> dict:
    """Generate mock market data for all configured tickers."""
    from src.config import TICKERS

    return {name: _make_ohlcv(ticker) for name, ticker in TICKERS.items()}


def _make_fred_data() -> dict:
    """Generate mock FRED macro data."""
    dates = pd.date_range(end=datetime.now().date(), periods=60, freq="MS")
    return {
        "CPI": pd.DataFrame({"CPIAUCSL": np.linspace(300, 320, 60)}, index=dates),
        "UNEMPLOYMENT": pd.DataFrame({"UNRATE": np.linspace(4.0, 5.0, 60)}, index=dates),
        "FEDFUNDS": pd.DataFrame({"FEDFUNDS": np.linspace(5.0, 5.5, 60)}, index=dates),
        "TREASURY_10Y": pd.DataFrame({"DGS10": np.linspace(3.5, 4.5, 60)}, index=dates),
    }


def _make_global_indices_data() -> dict:
    """Generate mock global indices data for dynamic correlation."""
    indices = {
        "S&P500": "^GSPC",
        "NASDAQ": "^IXIC",
        "DOW": "^DJI",
        "NIKKEI": "^N225",
        "HANG_SENG": "^HSI",
        "STI": "^STI",
        "GOLD": "GC=F",
        "OIL": "CL=F",
        "VIX": "^VIX",
        "USD_IDR": "IDR=X",
    }
    return {name: _make_ohlcv(ticker, n=300) for name, ticker in indices.items()}


def _make_foreign_flow_data() -> dict:
    """Generate mock foreign flow data."""
    from src.foreign_flow import ForeignFlowData, ForeignFlowSummary
    from datetime import datetime, timedelta
    
    today = datetime.now().strftime("%Y-%m-%d")
    
    # Mock top foreign buy/sell
    top_buyers = [
        {"ticker": "BBCA.JK", "net_buy": 150.5},
        {"ticker": "BBRI.JK", "net_buy": 120.3},
        {"ticker": "BMRI.JK", "net_buy": 98.7},
    ]
    
    top_sellers = [
        {"ticker": "TLKM.JK", "net_sell": 85.2},
        {"ticker": "ASII.JK", "net_sell": 65.4},
        {"ticker": "UNVR.JK", "net_sell": 45.1},
    ]
    
    sector_flows = {
        "Financials": 200.5,
        "Consumer": -50.3,
        "Energy": 80.2,
        "Infrastructure": -30.1,
    }
    
    return ForeignFlowSummary(
        date=today,
        total_net_buy=369.5,
        total_net_sell=195.7,
        total_net_flow=173.8,
        top_buyers=top_buyers,
        top_sellers=top_sellers,
        sector_flows=sector_flows,
        sentiment="bullish",
        sentiment_score=65.0,
    )


def _make_economic_events_data() -> dict:
    """Generate mock Indonesia economic calendar events."""
    from src.indo_economic_calendar import EconomicEvent, EconomicCalendarSummary
    from datetime import datetime, timedelta
    
    today = datetime.now()
    
    upcoming_events = [
        EconomicEvent(
            event_id="bi_rate_jan",
            event_name="BI Rate Decision",
            date=(today + timedelta(days=15)).strftime("%Y-%m-%d"),
            importance="high",
            category="monetary",
            unit="%",
            impact_description="Monetary policy decision affects interest rates",
        ),
        EconomicEvent(
            event_id="gdp_q4",
            event_name="GDP Growth (QoQ)",
            date=(today + timedelta(days=35)).strftime("%Y-%m-%d"),
            importance="high",
            category="growth",
            unit="%",
            impact_description="Economic growth indicator",
        ),
    ]
    
    return EconomicCalendarSummary(
        upcoming_events=upcoming_events,
        recent_events=[],
        high_impact_count=2,
        event_risk_score=50.0,
        recommendations=["High-impact event(s) upcoming - monitor closely"],
    )


def _make_fund_flows_data() -> dict:
    """Generate mock fund flows data."""
    from src.flow_of_funds import FlowOfFundsSummary
    from datetime import datetime
    
    today = datetime.now().strftime("%Y-%m-%d")
    
    return FlowOfFundsSummary(
        date=today,
        total_net_inflow=250.5,
        equity_flow=300.0,
        mixed_flow=50.0,
        fixed_income_flow=-100.0,
        etf_flow=0.5,
        sector_flows={
            "Financials": 150.0,
            "Technology": 80.0,
            "Consumer": 20.5,
        },
        sentiment="bullish",
        sentiment_score=55.0,
    )


def _make_news_data() -> dict:
    """Generate mock news data."""
    from src.realtime_news import NewsItem, NewsFeed
    from datetime import datetime
    
    items = [
        NewsItem(
            id="news_1",
            title="IHSG Reaches New High",
            summary="Indonesia stock index reaches all-time high driven by foreign inflows",
            url="https://example.com/news1",
            source="Kontan",
            published_at=datetime.now().isoformat(),
            sentiment="positive",
            sentiment_score=0.7,
            impact_score=75.0,
            tickers_mentioned=["IHSG"],
        ),
        NewsItem(
            id="news_2",
            title="Bank Indonesia Holds Rates",
            summary="Central bank maintains benchmark rate at 6.0%",
            url="https://example.com/news2",
            source="Bisnis Indonesia",
            published_at=datetime.now().isoformat(),
            sentiment="neutral",
            sentiment_score=0.1,
            impact_score=60.0,
            tickers_mentioned=[],
        ),
    ]
    
    return NewsFeed(
        items=items,
        last_updated=datetime.now().isoformat(),
        total_items=2,
        high_impact_count=1,
        sentiment_summary={"positive": 1, "negative": 0, "neutral": 1},
    )


def _make_social_media_data() -> dict:
    """Generate mock social media data."""
    from src.social_media_realtime import SocialPost, SocialMediaFeed
    from datetime import datetime
    
    posts = [
        SocialPost(
            id="post_1",
            platform="twitter",
            author="trader_joe",
            content="BBCA looking strong today! 🚀",
            timestamp=datetime.now().isoformat(),
            likes=150,
            retweets=30,
            replies=10,
            sentiment="positive",
            sentiment_score=0.6,
            tickers_mentioned=["BBCA"],
        ),
        SocialPost(
            id="post_2",
            platform="reddit",
            author="investor_id",
            content="Market seems overbought, be careful",
            timestamp=datetime.now().isoformat(),
            likes=80,
            retweets=0,
            replies=25,
            sentiment="negative",
            sentiment_score=-0.4,
            tickers_mentioned=[],
        ),
    ]
    
    return SocialMediaFeed(
        posts=posts,
        trends=[],
        last_updated=datetime.now().isoformat(),
        sentiment_summary={"positive": 1, "negative": 1, "neutral": 0},
    )


def _make_alternative_data() -> dict:
    """Generate mock alternative data."""
    from src.alternative_data import SatelliteData, ShippingData, AlternativeDataSummary
    from datetime import datetime
    
    satellite_data = [
        SatelliteData(
            location="Kalimantan",
            commodity="coal",
            date=datetime.now().strftime("%Y-%m-%d"),
            production_estimate=1000000.0,
            confidence=0.7,
            change_vs_previous=5.2,
            description="Coal production in Kalimantan",
        ),
    ]
    
    shipping_data = [
        ShippingData(
            route="Indonesia-China",
            commodity="coal",
            volume=5000000.0,
            vessel_count=15,
            date=datetime.now().strftime("%Y-%m-%d"),
            trend="increasing",
        ),
    ]
    
    return AlternativeDataSummary(
        satellite_data=satellite_data,
        shipping_data=shipping_data,
        economic_indicators={},
        last_updated=datetime.now().isoformat(),
        insights=["Production increasing - bullish for coal sector"],
    )


@pytest.fixture(scope="session")
def mock_market_data():
    """Shared deterministic mock market data."""
    return _make_market_data()


@pytest.fixture(scope="session")
def mock_all_data():
    """Shared deterministic mock market + FRED data."""
    return {"market": _make_market_data(), "fred": _make_fred_data()}


@pytest.fixture(scope="session")
def mock_global_indices():
    """Shared deterministic mock global indices data."""
    return _make_global_indices_data()


@pytest.fixture(scope="session")
def mock_foreign_flow():
    """Shared deterministic mock foreign flow data."""
    return _make_foreign_flow_data()


@pytest.fixture(scope="session")
def mock_economic_events():
    """Shared deterministic mock economic events data."""
    return _make_economic_events_data()


@pytest.fixture(scope="session")
def mock_fund_flows():
    """Shared deterministic mock fund flows data."""
    return _make_fund_flows_data()


@pytest.fixture(scope="session")
def mock_news():
    """Shared deterministic mock news data."""
    return _make_news_data()


@pytest.fixture(scope="session")
def mock_social_media():
    """Shared deterministic mock social media data."""
    return _make_social_media_data()


@pytest.fixture(scope="session")
def mock_alternative_data():
    """Shared deterministic mock alternative data."""
    return _make_alternative_data()


@pytest.fixture(scope="session")
def mock_market_analytics_data():
    """Shared deterministic mock data for all market analytics modules."""
    return {
        "market": _make_market_data(),
        "fred": _make_fred_data(),
        "global_indices": _make_global_indices_data(),
        "foreign_flow": _make_foreign_flow_data(),
        "economic_events": _make_economic_events_data(),
        "fund_flows": _make_fund_flows_data(),
        "news": _make_news_data(),
        "social_media": _make_social_media_data(),
        "alternative_data": _make_alternative_data(),
    }


@pytest.fixture(autouse=True)
def patch_yahoo_finance(mock_market_data, mock_all_data, monkeypatch):
    """
    Patch Yahoo Finance fetchers so tests run offline and fast.

    Ini hanya mock untuk test. Data dihasilkan secara deterministik dan
    tidak merepresentasikan harga pasar nyata.
    """
    from src import data_fetcher

    def _mock_fetch_yfinance(ticker, period="2y", interval="1d", **kwargs):
        return _make_ohlcv(ticker)

    def _mock_fetch_all_market_data(period="5y", use_cache=True):
        return mock_market_data

    def _mock_fetch_all_data(period="2y"):
        return mock_all_data

    monkeypatch.setattr(data_fetcher, "fetch_yfinance_data", _mock_fetch_yfinance)
    monkeypatch.setattr(data_fetcher, "fetch_all_market_data", _mock_fetch_all_market_data)
    monkeypatch.setattr(data_fetcher, "fetch_all_data", _mock_fetch_all_data)
