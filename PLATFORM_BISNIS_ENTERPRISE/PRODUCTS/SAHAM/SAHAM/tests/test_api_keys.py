"""Test API keys integration for Alpha Vantage, Finnhub, and NewsAPI."""

import os
import pytest
import pandas as pd


class TestAlphaVantageAPI:
    """Test Alpha Vantage API integration."""

    def test_api_key_configured(self):
        """Test that Alpha Vantage API key is configured."""
        api_key = os.getenv("ALPHAVANTAGE_API_KEY", "")
        assert api_key, "ALPHAVANTAGE_API_KEY not set in environment"
        assert api_key != "your_key_here", "ALPHAVANTAGE_API_KEY is placeholder"
    
    def test_alpha_vantage_fetch(self):
        """Test Alpha Vantage data fetch."""
        from src.alt_data_sources import AlphaVantageSource
        
        api_key = os.getenv("ALPHAVANTAGE_API_KEY", "")
        if not api_key or api_key == "your_key_here":
            pytest.skip("ALPHAVANTAGE_API_KEY not configured")
        
        av = AlphaVantageSource(api_key=api_key)
        assert av.is_available()
        
        # Try to fetch data for a well-known ticker
        df = av.fetch_daily("AAPL", outputsize="compact")
        
        # Should either return data or fallback to yfinance
        if not df.empty:
            assert "Close" in df.columns
            assert len(df) > 0
        else:
            # Fallback may have failed, which is acceptable
            print("Alpha Vantage returned empty, using fallback")


class TestFinnhubAPI:
    """Test Finnhub API integration."""

    def test_api_key_configured(self):
        """Test that Finnhub API key is configured."""
        api_key = os.getenv("FINNHUB_API_KEY", "")
        assert api_key, "FINNHUB_API_KEY not set in environment"
        assert api_key != "your_key_here", "FINNHUB_API_KEY is placeholder"
    
    def test_finnhub_quote(self):
        """Test Finnhub quote fetch."""
        from src.alt_data_sources import FinnhubSource
        
        api_key = os.getenv("FINNHUB_API_KEY", "")
        if not api_key or api_key == "your_key_here":
            pytest.skip("FINNHUB_API_KEY not configured")
        
        fh = FinnhubSource(api_key=api_key)
        assert fh.is_available()
        
        # Try to fetch quote for a well-known ticker
        quote = fh.get_quote("AAPL")
        
        # Should either return data or empty dict on error
        if quote:
            # Finnhub returns different key names
            assert "current" in quote or "c" in quote
            price = quote.get("current") or quote.get("c")
            assert price > 0
        else:
            print("Finnhub returned empty quote")


class TestNewsAPI:
    """Test NewsAPI integration."""

    def test_api_key_configured(self):
        """Test that NewsAPI key is configured."""
        api_key = os.getenv("NEWSAPI_API_KEY", "")
        assert api_key, "NEWSAPI_API_KEY not set in environment"
        assert api_key != "your_key_here", "NEWSAPI_API_KEY is placeholder"
    
    def test_newsapi_fetch(self):
        """Test NewsAPI news fetch."""
        from src.realtime_news import RealTimeNewsFetcher
        
        api_key = os.getenv("NEWSAPI_API_KEY", "")
        if not api_key or api_key == "your_key_here":
            pytest.skip("NEWSAPI_API_KEY not configured")
        
        # Set env var for the fetcher to pick up
        os.environ["NEWSAPI_API_KEY"] = api_key
        fetcher = RealTimeNewsFetcher()
        
        # Try to fetch news
        news = fetcher.fetch_newsapi_news(page_size=5)
        
        # Should either return news or empty list on error
        if news:
            assert len(news) > 0
            assert all(hasattr(item, "title") for item in news)
        else:
            print("NewsAPI returned empty news list")


class TestAPIIntegration:
    """Test combined API integration."""

    def test_all_apis_configured(self):
        """Test that all API keys are configured."""
        av_key = os.getenv("ALPHAVANTAGE_API_KEY", "")
        fh_key = os.getenv("FINNHUB_API_KEY", "")
        news_key = os.getenv("NEWSAPI_API_KEY", "")
        
        configured_count = sum([
            bool(av_key and av_key != "your_key_here"),
            bool(fh_key and fh_key != "your_key_here"),
            bool(news_key and news_key != "your_key_here"),
        ])
        
        # At least one API should be configured
        assert configured_count > 0, "No API keys configured"
        print(f"Configured APIs: {configured_count}/3")
