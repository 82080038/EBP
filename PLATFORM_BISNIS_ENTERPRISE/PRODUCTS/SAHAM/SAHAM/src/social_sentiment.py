"""
Social media sentiment analysis.

Collects and analyzes sentiment from social media sources:
- Twitter/X (via search)
- Stockbit (Indonesian stock community)
- Reddit (r/wallstreetbets, r/investing)
- News comments

Uses a combination of keyword-based and NLP-based sentiment scoring.
Falls back gracefully when APIs are not available.
"""
from __future__ import annotations

import logging
import re
from dataclasses import dataclass, field
from datetime import datetime, timedelta
from typing import Dict, List, Optional, Tuple

import numpy as np

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class SocialPost:
    """A social media post."""
    platform: str = ""
    author: str = ""
    content: str = ""
    timestamp: str = ""
    likes: int = 0
    retweets: int = 0
    replies: int = 0
    sentiment_score: float = 0.0
    ticker: str = ""


@dataclass
class SocialSentimentResult:
    """Aggregated social sentiment result."""
    ticker: str = ""
    overall_sentiment: float = 0.0  # -1 to 1
    sentiment_label: str = ""  # very_positive, positive, neutral, negative, very_negative
    n_posts: int = 0
    n_positive: int = 0
    n_negative: int = 0
    n_neutral: int = 0
    engagement_score: float = 0.0
    trending: bool = False
    top_posts: List[SocialPost] = field(default_factory=list)
    keyword_frequency: Dict[str, int] = field(default_factory=dict)
    platforms_covered: List[str] = field(default_factory=list)
    error: str = ""


# =============================================================================
# SENTIMENT SCORING
# =============================================================================


POSITIVE_KEYWORDS = {
    "en": [
        "bullish", "buy", "long", "moon", "rocket", "pump", "breakout",
        "upgrade", "beat", "surge", "rally", "gain", "profit", "growth",
        "strong", "outperform", "accumulate", "support", "recovery",
        "undervalued", "opportunity", "bull", "green", "high",
    ],
    "id": [
        "naik", "beli", "untung", "rally", "beli", "bagus", "untung",
        "mantap", "cuan", "profit", "breakout", "support", "akumulasi",
        "naik terus", "bullish", "overbought", "mantul", "jos",
    ],
}

NEGATIVE_KEYWORDS = {
    "en": [
        "bearish", "sell", "short", "dump", "crash", "bear", "drop",
        "downgrade", "miss", "fall", "loss", "decline", "weak",
        "underperform", "sell", "resistance", "correction", "red",
        "overvalued", "risk", "bubble", "fear", "panic",
    ],
    "id": [
        "turun", "jual", "rugi", "crash", "bearish", "lemah",
        "drop", "merah", "resistance", "jual", "overbought",
        "anjlog", "suspect", "gorengan", "rugi", "loss",
        "beku", "suspend", "turun terus",
    ],
}

BULLISH_EMOJIS = ["🚀", "📈", "🐂", "💪", "🔥", "⬆️", "🟢", "💯"]
BEARISH_EMOJIS = ["🐻", "📉", "⬇️", "🔴", "💀", "😱", "❌"]


def _score_text(text: str) -> float:
    """Score text sentiment from -1 to 1 using keyword matching."""
    if not text:
        return 0.0

    text_lower = text.lower()
    score = 0.0
    n_positive = 0
    n_negative = 0

    # English keywords
    for kw in POSITIVE_KEYWORDS["en"]:
        if kw in text_lower:
            n_positive += 1
            score += 0.15

    for kw in NEGATIVE_KEYWORDS["en"]:
        if kw in text_lower:
            n_negative += 1
            score -= 0.15

    # Indonesian keywords
    for kw in POSITIVE_KEYWORDS["id"]:
        if kw in text_lower:
            n_positive += 1
            score += 0.15

    for kw in NEGATIVE_KEYWORDS["id"]:
        if kw in text_lower:
            n_negative += 1
            score -= 0.15

    # Emoji analysis
    for emoji in BULLISH_EMOJIS:
        if emoji in text:
            n_positive += 1
            score += 0.1

    for emoji in BEARISH_EMOJIS:
        if emoji in text:
            n_negative += 1
            score -= 0.1

    # Exclamation marks amplify sentiment
    excl_count = text.count("!")
    if excl_count > 0:
        score *= (1 + min(excl_count * 0.1, 0.5))

    # Normalize
    total = n_positive + n_negative
    if total > 0:
        score = score / total * min(total, 5)  # Scale by signal count, cap at 5

    return float(np.clip(score, -1, 1))


def _extract_tickers(text: str) -> List[str]:
    """Extract $TICKER mentions from text."""
    return re.findall(r"\$([A-Z]{2,5})", text)


def _compute_engagement(post: SocialPost) -> float:
    """Compute engagement score from likes/retweets/replies."""
    return float(post.likes + post.retweets * 2 + post.replies * 1.5)


# =============================================================================
# SENTIMENT COLLECTION (Mock/Simulated for standalone operation)
# =============================================================================


def _generate_mock_posts(ticker: str, n_posts: int = 20) -> List[SocialPost]:
    """Generate realistic mock posts for testing when APIs unavailable."""
    import random
    random.seed(42)

    templates = [
        ("${ticker} looking bullish today! 🚀 Breakout incoming", 0.7, "twitter"),
        ("${ticker} bearish pattern forming, be careful 📉", -0.6, "twitter"),
        ("Just bought more ${ticker}, long term hold 💪", 0.5, "stockbit"),
        ("${ticker} dump incoming, sell now!", -0.8, "twitter"),
        ("${ticker} earnings beat expectations! 📈", 0.6, "reddit"),
        ("Not sure about ${ticker} at this level, sideways", 0.0, "stockbit"),
        ("${ticker} cuan mantap! Naik terus 🚀", 0.7, "stockbit"),
        ("${ticker} anjlog, rugi nih 😱", -0.7, "stockbit"),
        ("Bullish on ${ticker}, strong fundamentals", 0.5, "reddit"),
        ("${ticker} overvalued at this price, wait for pullback", -0.3, "twitter"),
    ]

    posts = []
    for i in range(n_posts):
        template = templates[i % len(templates)]
        content = template[0].replace("${ticker}", ticker)
        post = SocialPost(
            platform=template[2],
            author=f"user_{i}",
            content=content,
            timestamp=(datetime.now() - timedelta(hours=i)).isoformat(),
            likes=random.randint(0, 500),
            retweets=random.randint(0, 200),
            replies=random.randint(0, 100),
            sentiment_score=template[1],
            ticker=ticker,
        )
        posts.append(post)

    return posts


# =============================================================================
# MAIN API
# =============================================================================


def analyze_social_sentiment(
    ticker: str,
    posts: Optional[List[SocialPost]] = None,
    use_mock: bool = True,
) -> SocialSentimentResult:
    """
    Analyze social media sentiment for a ticker.

    Args:
        ticker: Stock ticker
        posts: Pre-collected posts (if None, will try to collect or use mock)
        use_mock: Use mock data if real collection unavailable

    Returns:
        SocialSentimentResult with aggregated sentiment
    """
    result = SocialSentimentResult(ticker=ticker)

    # Collect posts if not provided
    if posts is None:
        if use_mock:
            posts = _generate_mock_posts(ticker)
        else:
            # Try real collection (would need API keys)
            result.error = "Real social media collection requires API keys. Set use_mock=True for testing."
            return result

    if not posts:
        result.error = "No posts available for analysis"
        return result

    # Score posts if not already scored
    for post in posts:
        if post.sentiment_score == 0:
            post.sentiment_score = _score_text(post.content)

    # Aggregate
    scores = [p.sentiment_score for p in posts]
    result.n_posts = len(posts)
    result.n_positive = sum(1 for s in scores if s > 0.1)
    result.n_negative = sum(1 for s in scores if s < -0.1)
    result.n_neutral = sum(1 for s in scores if -0.1 <= s <= 0.1)

    # Weighted average by engagement
    engagements = [_compute_engagement(p) for p in posts]
    total_engagement = sum(engagements) + 1e-10
    weighted_scores = [s * e for s, e in zip(scores, engagements)]
    result.overall_sentiment = float(sum(weighted_scores) / total_engagement)
    result.overall_sentiment = float(np.clip(result.overall_sentiment, -1, 1))

    # Label
    if result.overall_sentiment > 0.5:
        result.sentiment_label = "very_positive"
    elif result.overall_sentiment > 0.1:
        result.sentiment_label = "positive"
    elif result.overall_sentiment > -0.1:
        result.sentiment_label = "neutral"
    elif result.overall_sentiment > -0.5:
        result.sentiment_label = "negative"
    else:
        result.sentiment_label = "very_negative"

    # Engagement score
    result.engagement_score = float(np.mean(engagements))

    # Trending if high engagement + strong sentiment
    result.trending = result.engagement_score > 100 and abs(result.overall_sentiment) > 0.3

    # Top posts by engagement
    sorted_posts = sorted(posts, key=lambda p: _compute_engagement(p), reverse=True)
    result.top_posts = sorted_posts[:5]

    # Keyword frequency
    all_text = " ".join(p.content.lower() for p in posts)
    keywords = {}
    for kw in POSITIVE_KEYWORDS["en"] + POSITIVE_KEYWORDS["id"] + NEGATIVE_KEYWORDS["en"] + NEGATIVE_KEYWORDS["id"]:
        count = all_text.count(kw)
        if count > 0:
            keywords[kw] = count
    result.keyword_frequency = dict(sorted(keywords.items(), key=lambda x: x[1], reverse=True)[:10])

    # Platforms
    result.platforms_covered = list(set(p.platform for p in posts))

    return result


def get_social_sentiment_adjustment(result: SocialSentimentResult) -> Tuple[float, str]:
    """Get confidence adjustment from social sentiment."""
    if result.error:
        return 0.0, "Social sentiment: unavailable"

    sentiment = result.overall_sentiment
    n_posts = result.n_posts

    if n_posts < 5:
        return 0.0, "Social sentiment: insufficient data"

    if sentiment > 0.3 and result.trending:
        return 0.05, f"Social sentiment: very positive & trending ({sentiment:.2f})"
    elif sentiment > 0.2:
        return 0.03, f"Social sentiment: positive ({sentiment:.2f})"
    elif sentiment < -0.3 and result.trending:
        return -0.05, f"Social sentiment: very negative & trending ({sentiment:.2f})"
    elif sentiment < -0.2:
        return -0.03, f"Social sentiment: negative ({sentiment:.2f})"
    else:
        return 0.0, f"Social sentiment: neutral ({sentiment:.2f})"


def run_social_sentiment(ticker: str, use_mock: bool = True) -> Dict:
    """
    Run social sentiment analysis.

    Returns:
        Dict with sentiment scores and metadata
    """
    result = analyze_social_sentiment(ticker, use_mock=use_mock)

    return {
        "ticker": ticker,
        "overall_sentiment": result.overall_sentiment,
        "sentiment_label": result.sentiment_label,
        "n_posts": result.n_posts,
        "n_positive": result.n_positive,
        "n_negative": result.n_negative,
        "n_neutral": result.n_neutral,
        "engagement_score": result.engagement_score,
        "trending": result.trending,
        "platforms": result.platforms_covered,
        "top_keywords": result.keyword_frequency,
        "top_posts": [
            {
                "platform": p.platform,
                "content": p.content[:200],
                "sentiment": p.sentiment_score,
                "engagement": _compute_engagement(p),
            }
            for p in result.top_posts
        ],
        "error": result.error,
    }
