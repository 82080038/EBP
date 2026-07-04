"""
Real-time News Integration Module.

Provides real-time news feeds for Indonesian and global markets:
- Real-time news API integration
- News impact scoring
- News-to-price correlation
- Breaking news alerts

Data Sources:
- NewsAPI.org (global news)
- Finnhub News API (company-specific)
- Alpha Vantage News (economic news)
- Local Indonesian news sources (via RSS with real-time updates)

Features:
- Real-time news fetching
- Sentiment analysis integration
- News categorization
- Impact scoring for trading decisions
"""

import pandas as pd
import numpy as np
import requests
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging

logger = logging.getLogger("saham.realtime_news")


@dataclass
class NewsItem:
    """Real-time news item."""
    id: str
    title: str
    summary: str
    url: str
    source: str
    published_at: str
    sentiment: Optional[str] = None
    sentiment_score: Optional[float] = None
    impact_score: float = 0.0  # 0-100
    categories: List[str] = field(default_factory=list)
    tickers_mentioned: List[str] = field(default_factory=list)


@dataclass
class NewsFeed:
    """Real-time news feed."""
    items: List[NewsItem] = field(default_factory=list)
    last_updated: str = ""
    total_items: int = 0
    high_impact_count: int = 0
    sentiment_summary: Dict = field(default_factory=dict)


class RealTimeNewsFetcher:
    """
    Fetches real-time news from multiple sources.
    
    Integrates with NewsAPI, Finnhub, and Alpha Vantage
    for comprehensive news coverage.
    """

    def __init__(self):
        self.newsapi_key = None  # Set via NEWSAPI_API_KEY env var
        self.base_url = "https://newsapi.org/v2"
        self.finnhub_source = None  # Will be initialized if needed

    def fetch_newsapi_news(
        self,
        query: str = "stock market OR economy OR finance",
        language: str = "en",
        from_date: Optional[str] = None,
        to_date: Optional[str] = None,
        page_size: int = 20
    ) -> List[NewsItem]:
        """
        Fetch news from NewsAPI.org.
        
        Args:
            query: Search query
            language: Language code
            from_date: Start date (YYYY-MM-DD)
            to_date: End date (YYYY-MM-DD)
            page_size: Number of articles to fetch
            
        Returns:
            List of NewsItem objects.
        """
        import os
        
        api_key = self.newsapi_key or os.getenv("NEWSAPI_API_KEY", "")
        if not api_key:
            logger.warning("NewsAPI key not set")
            return []
        
        params = {
            "q": query,
            "language": language,
            "apiKey": api_key,
            "pageSize": page_size,
            "sortBy": "publishedAt",
        }
        
        if from_date:
            params["from"] = from_date
        if to_date:
            params["to"] = to_date
        
        try:
            resp = requests.get(f"{self.base_url}/everything", params=params, timeout=30)
            data = resp.json()
            
            if data.get("status") != "ok":
                logger.warning(f"NewsAPI error: {data.get('message', 'Unknown error')}")
                return []
            
            articles = data.get("articles", [])
            items = []
            
            for article in articles:
                items.append(NewsItem(
                    id=str(hash(article.get("url", ""))),
                    title=article.get("title", ""),
                    summary=article.get("description", ""),
                    url=article.get("url", ""),
                    source=article.get("source", {}).get("name", "NewsAPI"),
                    published_at=article.get("publishedAt", ""),
                ))
            
            return items
            
        except Exception as e:
            logger.error(f"NewsAPI fetch failed: {e}")
            return []

    def fetch_indonesian_news(self, hours: int = 24) -> List[NewsItem]:
        """
        Fetch Indonesian financial news.
        
        Args:
            hours: Number of hours to look back
            
        Returns:
            List of NewsItem objects.
        """
        from src.ai_agent import NewsScraper
        
        scraper = NewsScraper()
        articles = scraper.fetch_all_sources(max_per_source=10)
        
        items = []
        for article in articles:
            items.append(NewsItem(
                id=str(hash(article.url)),
                title=article.title,
                summary=article.summary,
                url=article.url,
                source=article.source,
                published_at=article.published,
            ))
        
        return items

    def fetch_company_news(self, symbol: str, days: int = 7) -> List[NewsItem]:
        """
        Fetch company-specific news from Finnhub.
        
        Args:
            symbol: Stock symbol
            days: Number of days to look back
            
        Returns:
            List of NewsItem objects.
        """
        from src.alt_data_sources import FinnhubSource
        
        fh = FinnhubSource()
        if not fh.is_available():
            logger.warning("Finnhub not available for company news")
            return []
        
        from_date = (datetime.now() - timedelta(days=days)).strftime("%Y-%m-%d")
        to_date = datetime.now().strftime("%Y-%m-%d")
        
        news_data = fh.get_company_news(symbol, from_date, to_date)
        
        items = []
        for article in news_data:
            items.append(NewsItem(
                id=str(hash(article.get("url", ""))),
                title=article.get("headline", ""),
                summary=article.get("summary", ""),
                url=article.get("url", ""),
                source="Finnhub",
                published_at=datetime.fromtimestamp(article.get("datetime", 0)).isoformat(),
                tickers_mentioned=[symbol],
            ))
        
        return items

    def analyze_news_impact(self, news_item: NewsItem) -> float:
        """
        Analyze impact score of a news item.
        
        Args:
            news_item: NewsItem to analyze
            
        Returns:
            Impact score (0-100).
        """
        from src.ai_agent import FinBERTSentiment
        
        # Get sentiment
        finbert = FinBERTSentiment()
        text = f"{news_item.title}. {news_item.summary}"
        sentiment = finbert.analyze(text)
        
        news_item.sentiment = sentiment.sentiment
        news_item.sentiment_score = sentiment.confidence
        
        # Calculate impact based on sentiment and keywords
        impact_keywords = {
            "high": ["crash", "surge", "breakthrough", "bankruptcy", "merger", "acquisition", "rate hike", "earnings beat", "earnings miss"],
            "medium": ["upgrade", "downgrade", "guidance", "outlook", "forecast", "inflation", "gdp", "recession"],
            "low": ["report", "update", "announcement", "statement"],
        }
        
        text_lower = text.lower()
        high_count = sum(1 for kw in impact_keywords["high"] if kw in text_lower)
        medium_count = sum(1 for kw in impact_keywords["medium"] if kw in text_lower)
        
        # Base impact from sentiment
        if sentiment.sentiment == "positive":
            base_impact = 50 + sentiment.confidence * 30
        elif sentiment.sentiment == "negative":
            base_impact = 50 + sentiment.confidence * 30
        else:
            base_impact = 30
        
        # Adjust for keywords
        impact = base_impact + (high_count * 15) + (medium_count * 5)
        
        return min(100, max(0, impact))

    def get_news_feed(self, hours: int = 24) -> NewsFeed:
        """
        Get comprehensive news feed.
        
        Args:
            hours: Number of hours to look back
            
        Returns:
            NewsFeed with all news items.
        """
        # Fetch from multiple sources
        global_news = self.fetch_newsapi_news(page_size=10)
        indo_news = self.fetch_indonesian_news(hours=hours)
        
        # Combine
        all_items = global_news + indo_news
        
        # Analyze impact
        for item in all_items:
            item.impact_score = self.analyze_news_impact(item)
        
        # Sort by impact and recency
        all_items.sort(key=lambda x: (x.impact_score, x.published_at), reverse=True)
        
        # Calculate summary
        high_impact = sum(1 for item in all_items if item.impact_score > 70)
        
        sentiment_summary = {
            "positive": sum(1 for item in all_items if item.sentiment == "positive"),
            "negative": sum(1 for item in all_items if item.sentiment == "negative"),
            "neutral": sum(1 for item in all_items if item.sentiment == "neutral"),
        }
        
        return NewsFeed(
            items=all_items[:50],  # Limit to top 50
            last_updated=datetime.now().isoformat(),
            total_items=len(all_items),
            high_impact_count=high_impact,
            sentiment_summary=sentiment_summary,
        )

    def get_news_sentiment_signal(self) -> Dict:
        """
        Get news sentiment signal for trading decisions.
        
        Returns:
            Dict with signal and analysis.
        """
        feed = self.get_news_feed(hours=24)
        
        if not feed.items:
            return {
                "signal": "HOLD",
                "confidence": 0.5,
                "reason": "No news available",
            }
        
        # Calculate weighted sentiment
        total_weight = 0
        weighted_sentiment = 0
        
        for item in feed.items:
            weight = item.impact_score / 100
            if item.sentiment == "positive":
                sentiment_val = 1
            elif item.sentiment == "negative":
                sentiment_val = -1
            else:
                sentiment_val = 0
            
            weighted_sentiment += sentiment_val * weight
            total_weight += weight
        
        if total_weight > 0:
            avg_sentiment = weighted_sentiment / total_weight
        else:
            avg_sentiment = 0
        
        # Determine signal
        if avg_sentiment > 0.3:
            signal = "BUY"
            confidence = 0.5 + avg_sentiment * 0.3
            reason = f"News sentiment bullish (score: {avg_sentiment:.2f})"
        elif avg_sentiment < -0.3:
            signal = "SELL"
            confidence = 0.5 + abs(avg_sentiment) * 0.3
            reason = f"News sentiment bearish (score: {avg_sentiment:.2f})"
        else:
            signal = "HOLD"
            confidence = 0.5
            reason = "News sentiment neutral"
        
        return {
            "signal": signal,
            "confidence": round(confidence, 2),
            "reason": reason,
            "sentiment_score": round(avg_sentiment, 2),
            "high_impact_count": feed.high_impact_count,
        }


def get_realtime_news_adjustment() -> Tuple[float, str]:
    """
    Get confidence adjustment from real-time news for prediction pipeline.
    
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    fetcher = RealTimeNewsFetcher()
    signal_data = fetcher.get_news_sentiment_signal()
    
    if signal_data["signal"] == "BUY":
        adjustment = 0.02 * signal_data["confidence"]
        reason = f"Real-time news bullish (score: {signal_data['sentiment_score']:.2f})"
    elif signal_data["signal"] == "SELL":
        adjustment = -0.02 * signal_data["confidence"]
        reason = f"Real-time news bearish (score: {signal_data['sentiment_score']:.2f})"
    else:
        adjustment = 0.0
        reason = "Real-time news neutral"
    
    return adjustment, reason
