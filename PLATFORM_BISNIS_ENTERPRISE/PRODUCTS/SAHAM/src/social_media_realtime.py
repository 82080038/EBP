"""
Real-time Social Media Integration Module.

Provides real-time social media sentiment analysis:
- Twitter/X API integration
- Stockbit API integration (Indonesian stock community)
- Reddit API integration
- Real-time sentiment tracking
- Trending topic analysis

Data Sources:
- Twitter/X API (requires API key)
- Stockbit (web scraping or API if available)
- Reddit API (PRAW)

Features:
- Real-time post fetching
- Sentiment analysis
- Trending detection
- Volume spike alerts
"""

import pandas as pd
import numpy as np
import requests
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging

logger = logging.getLogger("saham.social_media_realtime")


@dataclass
class SocialPost:
    """Real-time social media post."""
    id: str
    platform: str
    author: str
    content: str
    timestamp: str
    likes: int = 0
    retweets: int = 0
    replies: int = 0
    sentiment: Optional[str] = None
    sentiment_score: float = 0.0
    tickers_mentioned: List[str] = field(default_factory=list)


@dataclass
class SocialTrend:
    """Trending topic on social media."""
    topic: str
    platform: str
    volume: int
    sentiment: str
    sentiment_score: float
    related_tickers: List[str] = field(default_factory=list)


@dataclass
class SocialMediaFeed:
    """Real-time social media feed."""
    posts: List[SocialPost] = field(default_factory=list)
    trends: List[SocialTrend] = field(default_factory=list)
    last_updated: str = ""
    sentiment_summary: Dict = field(default_factory=dict)


class RealTimeSocialMediaAnalyzer:
    """
    Analyzes real-time social media sentiment.
    
    Integrates with Twitter/X, Stockbit, and Reddit APIs
    for comprehensive social sentiment tracking.
    """

    def __init__(self):
        self.twitter_bearer_token = None  # Set via TWITTER_BEARER_TOKEN env var
        self.reddit_client_id = None  # Set via REDDIT_CLIENT_ID env var
        self.reddit_client_secret = None  # Set via REDDIT_CLIENT_SECRET env var

    def fetch_twitter_posts(
        self,
        query: str,
        hours: int = 24,
        max_results: int = 100
    ) -> List[SocialPost]:
        """
        Fetch posts from Twitter/X API.
        
        Args:
            query: Search query
            hours: Number of hours to look back
            max_results: Maximum number of results
            
        Returns:
            List of SocialPost objects.
        """
        import os
        
        bearer_token = self.twitter_bearer_token or os.getenv("TWITTER_BEARER_TOKEN", "")
        if not bearer_token:
            logger.warning("Twitter bearer token not set")
            return []
        
        # Twitter API v2 search endpoint
        url = "https://api.twitter.com/2/tweets/search/recent"
        
        headers = {
            "Authorization": f"Bearer {bearer_token}",
            "Content-Type": "application/json",
        }
        
        params = {
            "query": query,
            "max_results": min(max_results, 100),
            "tweet.fields": "created_at,public_metrics,entities",
        }
        
        try:
            resp = requests.get(url, headers=headers, params=params, timeout=30)
            data = resp.json()
            
            if "data" not in data:
                logger.warning(f"Twitter API error: {data.get('title', 'Unknown error')}")
                return []
            
            posts = []
            for tweet in data["data"]:
                # Extract tickers mentioned
                tickers = []
                if "entities" in tweet and "hashtags" in tweet["entities"]:
                    for tag in tweet["entities"]["hashtags"]:
                        tickers.append(tag["tag"])
                
                posts.append(SocialPost(
                    id=tweet["id"],
                    platform="twitter",
                    author=tweet.get("author_id", ""),
                    content=tweet.get("text", ""),
                    timestamp=tweet.get("created_at", ""),
                    likes=tweet.get("public_metrics", {}).get("like_count", 0),
                    retweets=tweet.get("public_metrics", {}).get("retweet_count", 0),
                    replies=tweet.get("public_metrics", {}).get("reply_count", 0),
                    tickers_mentioned=tickers,
                ))
            
            return posts
            
        except Exception as e:
            logger.error(f"Twitter API fetch failed: {e}")
            return []

    def fetch_stockbit_posts(
        self,
        ticker: str,
        hours: int = 24
    ) -> List[SocialPost]:
        """
        Fetch posts from Stockbit (Indonesian stock community).
        
        Note: Stockbit doesn't have a public API, so this uses
        web scraping as a placeholder. In production, would need
        to handle Stockbit's anti-scraping measures.
        
        Args:
            ticker: Stock ticker
            hours: Number of hours to look back
            
        Returns:
            List of SocialPost objects.
        """
        # Placeholder for Stockbit web scraping
        # In production, would need to handle Stockbit website structure
        logger.info("Stockbit integration requires web scraping - using placeholder")
        return []

    def fetch_reddit_posts(
        self,
        subreddit: str = "wallstreetbets",
        hours: int = 24,
        limit: int = 50
    ) -> List[SocialPost]:
        """
        Fetch posts from Reddit using PRAW.
        
        Args:
            subreddit: Subreddit name
            hours: Number of hours to look back
            limit: Number of posts to fetch
            
        Returns:
            List of SocialPost objects.
        """
        import os
        
        client_id = self.reddit_client_id or os.getenv("REDDIT_CLIENT_ID", "")
        client_secret = self.reddit_client_secret or os.getenv("REDDIT_CLIENT_SECRET", "")
        user_agent = os.getenv("REDDIT_USER_AGENT", "SahamApp/1.0")
        
        if not client_id or not client_secret:
            logger.warning("Reddit credentials not set, using public endpoint")
            return self._fetch_reddit_public(subreddit, limit)
        
        # Try to use PRAW with credentials
        try:
            import praw
            reddit = praw.Reddit(
                client_id=client_id,
                client_secret=client_secret,
                user_agent=user_agent,
            )
            
            subreddit_obj = reddit.subreddit(subreddit)
            posts = []
            
            cutoff_time = datetime.now() - timedelta(hours=hours)
            
            for submission in subreddit_obj.hot(limit=limit):
                # Check if post is within time window
                post_time = datetime.fromtimestamp(submission.created_utc)
                if post_time < cutoff_time:
                    continue
                
                # Extract tickers from text
                import re
                text = submission.title + " " + (submission.selftext or "")
                tickers = re.findall(r'\$([A-Z]{2,5})', text)
                
                posts.append(SocialPost(
                    id=submission.id,
                    platform="reddit",
                    author=str(submission.author),
                    content=text,
                    timestamp=post_time.isoformat(),
                    likes=submission.score,
                    retweets=0,  # Reddit doesn't have retweets
                    replies=submission.num_comments,
                    tickers_mentioned=tickers,
                ))
            
            return posts
            
        except ImportError:
            logger.warning("PRAW not installed, using public endpoint")
            return self._fetch_reddit_public(subreddit, limit)
        except Exception as e:
            logger.error(f"Reddit PRAW fetch failed: {e}")
            return self._fetch_reddit_public(subreddit, limit)
    
    def _fetch_reddit_public(self, subreddit: str, limit: int = 50) -> List[SocialPost]:
        """
        Fetch Reddit posts via public JSON endpoint (no credentials required).
        
        Note: This has rate limits and may be less reliable than PRAW.
        """
        url = f"https://www.reddit.com/r/{subreddit}/hot.json"
        params = {"limit": limit}
        
        try:
            resp = requests.get(url, params=params, timeout=30, headers={"User-Agent": "SahamApp/1.0"})
            data = resp.json()
            
            if "data" not in data or "children" not in data["data"]:
                return []
            
            posts = []
            for child in data["data"]["children"]:
                post_data = child["data"]
                
                # Extract tickers from text
                import re
                text = post_data.get("title", "") + " " + post_data.get("selftext", "")
                tickers = re.findall(r'\$([A-Z]{2,5})', text)
                
                posts.append(SocialPost(
                    id=post_data["id"],
                    platform="reddit",
                    author=post_data.get("author", ""),
                    content=text,
                    timestamp=datetime.fromtimestamp(post_data.get("created_utc", 0)).isoformat(),
                    likes=post_data.get("ups", 0),
                    retweets=0,  # Reddit doesn't have retweets
                    replies=post_data.get("num_comments", 0),
                    tickers_mentioned=tickers,
                ))
            
            return posts
            
        except Exception as e:
            logger.error(f"Reddit public endpoint fetch failed: {e}")
            return []

    def analyze_post_sentiment(self, post: SocialPost) -> SocialPost:
        """
        Analyze sentiment of a social media post.
        
        Args:
            post: SocialPost to analyze
            
        Returns:
            SocialPost with sentiment added.
        """
        from src.social_sentiment import _score_text
        
        post.sentiment_score = _score_text(post.content)
        
        if post.sentiment_score > 0.3:
            post.sentiment = "positive"
        elif post.sentiment_score < -0.3:
            post.sentiment = "negative"
        else:
            post.sentiment = "neutral"
        
        return post

    def detect_trending_topics(self, posts: List[SocialPost]) -> List[SocialTrend]:
        """
        Detect trending topics from social media posts.
        
        Args:
            posts: List of SocialPost objects
            
        Returns:
            List of SocialTrend objects.
        """
        from collections import Counter
        import re
        
        # Extract keywords
        all_words = []
        for post in posts:
            words = re.findall(r'\b[a-zA-Z]{3,}\b', post.content.lower())
            all_words.extend(words)
        
        # Count word frequency
        word_counts = Counter(all_words)
        
        # Filter common words
        common_words = {"the", "and", "for", "are", "but", "not", "you", "all", "can", "had", "her", "was", "one", "our", "out", "has", "buy", "sell", "stock", "market"}
        filtered_counts = {k: v for k, v in word_counts.items() if k not in common_words and v >= 3}
        
        # Create trends
        trends = []
        for word, count in sorted(filtered_counts.items(), key=lambda x: x[1], reverse=True)[:10]:
            # Calculate sentiment for this topic
            topic_posts = [p for p in posts if word in p.content.lower()]
            if topic_posts:
                avg_sentiment = np.mean([p.sentiment_score for p in topic_posts])
                sentiment = "positive" if avg_sentiment > 0.1 else "negative" if avg_sentiment < -0.1 else "neutral"
            else:
                avg_sentiment = 0
                sentiment = "neutral"
            
            # Find related tickers
            related_tickers = []
            for post in topic_posts:
                related_tickers.extend(post.tickers_mentioned)
            
            trends.append(SocialTrend(
                topic=word,
                platform="mixed",
                volume=count,
                sentiment=sentiment,
                sentiment_score=round(avg_sentiment, 2),
                related_tickers=list(set(related_tickers))[:5],
            ))
        
        return trends

    def get_social_feed(self, hours: int = 24) -> SocialMediaFeed:
        """
        Get comprehensive social media feed.
        
        Args:
            hours: Number of hours to look back
            
        Returns:
            SocialMediaFeed with all posts and trends.
        """
        # Fetch from multiple platforms
        twitter_posts = self.fetch_twitter_posts("stocks OR trading OR investing", hours=hours)
        reddit_posts = self.fetch_reddit_posts("wallstreetbets", hours=hours)
        
        # Combine
        all_posts = twitter_posts + reddit_posts
        
        # Analyze sentiment
        for post in all_posts:
            self.analyze_post_sentiment(post)
        
        # Detect trends
        trends = self.detect_trending_topics(all_posts)
        
        # Calculate sentiment summary
        sentiment_summary = {
            "positive": sum(1 for p in all_posts if p.sentiment == "positive"),
            "negative": sum(1 for p in all_posts if p.sentiment == "negative"),
            "neutral": sum(1 for p in all_posts if p.sentiment == "neutral"),
        }
        
        return SocialMediaFeed(
            posts=all_posts[:100],  # Limit to top 100
            trends=trends,
            last_updated=datetime.now().isoformat(),
            sentiment_summary=sentiment_summary,
        )

    def get_social_sentiment_signal(self, ticker: str = "") -> Dict:
        """
        Get social media sentiment signal for trading decisions.
        
        Args:
            ticker: Optional ticker to filter by
            
        Returns:
            Dict with signal and analysis.
        """
        feed = self.get_social_feed(hours=24)
        
        if not feed.posts:
            return {
                "signal": "HOLD",
                "confidence": 0.5,
                "reason": "No social media data available",
            }
        
        # Filter by ticker if specified
        if ticker:
            relevant_posts = [p for p in feed.posts if ticker in p.tickers_mentioned or ticker in p.content]
        else:
            relevant_posts = feed.posts
        
        if not relevant_posts:
            return {
                "signal": "HOLD",
                "confidence": 0.5,
                "reason": f"No social media posts for {ticker}",
            }
        
        # Calculate weighted sentiment
        total_weight = 0
        weighted_sentiment = 0
        
        for post in relevant_posts:
            weight = (post.likes + post.retweets * 2 + post.replies * 1.5) / 100  # Engagement weight
            sentiment_val = post.sentiment_score
            
            weighted_sentiment += sentiment_val * weight
            total_weight += weight
        
        if total_weight > 0:
            avg_sentiment = weighted_sentiment / total_weight
        else:
            avg_sentiment = 0
        
        # Determine signal
        if avg_sentiment > 0.2:
            signal = "BUY"
            confidence = 0.5 + avg_sentiment * 0.3
            reason = f"Social sentiment bullish (score: {avg_sentiment:.2f})"
        elif avg_sentiment < -0.2:
            signal = "SELL"
            confidence = 0.5 + abs(avg_sentiment) * 0.3
            reason = f"Social sentiment bearish (score: {avg_sentiment:.2f})"
        else:
            signal = "HOLD"
            confidence = 0.5
            reason = "Social sentiment neutral"
        
        return {
            "signal": signal,
            "confidence": round(confidence, 2),
            "reason": reason,
            "sentiment_score": round(avg_sentiment, 2),
            "post_count": len(relevant_posts),
        }


def get_social_media_adjustment(ticker: str = "") -> Tuple[float, str]:
    """
    Get confidence adjustment from social media for prediction pipeline.
    
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    analyzer = RealTimeSocialMediaAnalyzer()
    signal_data = analyzer.get_social_sentiment_signal(ticker)
    
    if signal_data["signal"] == "BUY":
        adjustment = 0.02 * signal_data["confidence"]
        reason = f"Social media bullish (score: {signal_data['sentiment_score']:.2f})"
    elif signal_data["signal"] == "SELL":
        adjustment = -0.02 * signal_data["confidence"]
        reason = f"Social media bearish (score: {signal_data['sentiment_score']:.2f})"
    else:
        adjustment = 0.0
        reason = "Social media neutral"
    
    return adjustment, reason
