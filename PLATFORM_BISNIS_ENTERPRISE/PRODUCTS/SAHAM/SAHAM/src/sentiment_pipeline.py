"""
Automated Daily Sentiment Pipeline.

Menjalankan otomatis setiap hari:
1. Scrape berita finansial dari RSS feeds (Kontan, CNBC, Reuters, Bloomberg)
2. Analyze sentiment dengan FinBERT (atau lexicon fallback)
3. Aggregate sentiment score untuk market overview
4. Store results untuk digunakan dalam prediction pipeline
5. Generate alert jika sentiment shift significant

Referensi:
- Danelfin: Daily AI briefing
- Bloomberg Terminal: News sentiment feed
- Refinitiv: News analytics
"""

import os
import json
import pandas as pd
from datetime import datetime
from typing import Dict, List, Optional
from dataclasses import dataclass, field
from .config import DATA_DIR
from .ai_agent import FinBERTSentiment, NewsScraper


@dataclass
class DailySentimentReport:
    """Complete daily sentiment analysis report."""
    date: str
    total_articles: int = 0
    positive_pct: float = 0.0
    negative_pct: float = 0.0
    neutral_pct: float = 0.0
    sentiment_score: float = 0.0  # -100 to 100
    fear_greed_index: float = 50.0  # 0-100
    top_positive_news: List[dict] = field(default_factory=list)
    top_negative_news: List[dict] = field(default_factory=list)
    source_breakdown: Dict[str, dict] = field(default_factory=dict)
    trend_vs_yesterday: Optional[str] = None  # "improving", "deteriorating", "stable"
    alert_triggered: bool = False
    alert_reason: str = ""


class DailySentimentPipeline:
    """
    Automated daily sentiment pipeline.
    Runs news scraping + sentiment analysis + scoring + storage.
    """

    def __init__(self):
        self.scraper = NewsScraper()
        self.sentiment_analyzer = FinBERTSentiment()
        self.history_file = os.path.join(DATA_DIR, "sentiment_history.json")
        self._history = self._load_history()

    def _load_history(self) -> List[dict]:
        if os.path.exists(self.history_file):
            try:
                with open(self.history_file, "r") as f:
                    return json.load(f)
            except (json.JSONDecodeError, IOError):
                return []
        return []

    def _save_history(self):
        try:
            with open(self.history_file, "w") as f:
                json.dump(self._history[-90:], f, indent=2)  # Keep last 90 days
        except IOError:
            pass

    def run_daily(self, max_per_source: int = 10) -> DailySentimentReport:
        """
        Run full daily sentiment pipeline.
        Returns DailySentimentReport.
        """
        today = datetime.now().strftime("%Y-%m-%d")
        print(f"\n{'='*60}")
        print(f"DAILY SENTIMENT PIPELINE — {today}")
        print(f"{'='*60}")

        # Step 1: Scrape news
        print("\n[1/4] Scraping news...")
        articles = self.scraper.fetch_all_sources(max_per_source=max_per_source)
        print(f"  Found {len(articles)} articles")

        if not articles:
            print("  [WARNING] No articles found — using neutral sentiment")
            return DailySentimentReport(
                date=today,
                sentiment_score=0.0,
                fear_greed_index=50.0,
                alert_reason="No news articles available",
            )

        # Step 2: Analyze sentiment
        print("\n[2/4] Analyzing sentiment...")
        self.scraper.analyze_sentiments(self.sentiment_analyzer)

        # Step 3: Aggregate
        print("\n[3/4] Aggregating scores...")
        summary = self.scraper.get_sentiment_summary()
        sentiment_score = summary.get("sentiment_score", 0)

        # Convert to Fear & Greed index (0-100)
        # sentiment_score: -100 (extreme fear) to 100 (extreme greed)
        fear_greed = (sentiment_score + 100) / 2
        fear_greed = max(0, min(100, fear_greed))

        # Source breakdown
        source_breakdown = {}
        for article in self.scraper.articles:
            source = article.source
            if source not in source_breakdown:
                source_breakdown[source] = {
                    "count": 0, "positive": 0, "negative": 0, "neutral": 0
                }
            source_breakdown[source]["count"] += 1
            if article.sentiment:
                source_breakdown[source][article.sentiment.sentiment] += 1

        # Top positive/negative news
        positive_news = []
        negative_news = []
        for article in self.scraper.articles:
            if article.sentiment:
                item = {
                    "title": article.title,
                    "source": article.source,
                    "sentiment": article.sentiment.sentiment,
                    "confidence": round(article.sentiment.confidence, 3),
                }
                if article.sentiment.sentiment == "positive":
                    positive_news.append(item)
                elif article.sentiment.sentiment == "negative":
                    negative_news.append(item)

        positive_news.sort(key=lambda x: x["confidence"], reverse=True)
        negative_news.sort(key=lambda x: x["confidence"], reverse=True)

        # Step 4: Trend comparison
        print("\n[4/4] Comparing with yesterday...")
        trend = "stable"
        alert_triggered = False
        alert_reason = ""

        if self._history:
            yesterday_score = self._history[-1].get("sentiment_score", 0)
            delta = sentiment_score - yesterday_score
            if delta > 15:
                trend = "improving"
            elif delta < -15:
                trend = "deteriorating"
                alert_triggered = True
                alert_reason = f"Sentiment dropped {delta:.0f} points vs yesterday"

            # Extreme fear/greed alert
            if fear_greed < 20:
                alert_triggered = True
                alert_reason = f"Extreme Fear detected (Fear&Greed: {fear_greed:.0f})"
            elif fear_greed > 80:
                alert_triggered = True
                alert_reason = f"Extreme Greed detected (Fear&Greed: {fear_greed:.0f})"

        report = DailySentimentReport(
            date=today,
            total_articles=len(articles),
            positive_pct=summary.get("positive", 0),
            negative_pct=summary.get("negative", 0),
            neutral_pct=summary.get("neutral", 0),
            sentiment_score=round(sentiment_score, 2),
            fear_greed_index=round(fear_greed, 1),
            top_positive_news=positive_news[:5],
            top_negative_news=negative_news[:5],
            source_breakdown=source_breakdown,
            trend_vs_yesterday=trend,
            alert_triggered=alert_triggered,
            alert_reason=alert_reason,
        )

        # Save to history
        self._history.append({
            "date": today,
            "sentiment_score": report.sentiment_score,
            "fear_greed_index": report.fear_greed_index,
            "total_articles": report.total_articles,
        })
        self._save_history()

        # Send in-app notification if alert triggered
        if report.alert_triggered:
            try:
                from .notifier import send_in_app
                level = "warning" if "Fear" in report.alert_reason else "info"
                if "Extreme" in report.alert_reason:
                    level = "warning"
                send_in_app(
                    kategori="SENTIMENT",
                    judul=f"⚠️ Sentiment Alert — {report.alert_reason}",
                    pesan=(
                        f"Tanggal: {today}\n"
                        f"Sentiment Score: {report.sentiment_score:.1f} (-100 to 100)\n"
                        f"Fear & Greed Index: {report.fear_greed_index:.1f} (0-100)\n"
                        f"Trend: {report.trend_vs_yesterday}\n"
                        f"Articles: {report.total_articles}\n"
                        f"Alert: {report.alert_reason}"
                    ),
                    level=level,
                )
            except Exception:
                pass

        # Print summary
        print(f"\n{'='*60}")
        print(f"SENTIMENT SUMMARY — {today}")
        print(f"{'='*60}")
        print(f"  Articles: {report.total_articles}")
        print(f"  Positive: {report.positive_pct:.1f}% | Negative: {report.negative_pct:.1f}% | Neutral: {report.neutral_pct:.1f}%")
        print(f"  Sentiment Score: {report.sentiment_score:.1f} (-100 to 100)")
        print(f"  Fear & Greed Index: {report.fear_greed_index:.1f} (0-100)")
        labels = ["Extreme Fear", "Fear", "Neutral", "Greed", "Extreme Greed"]
        label_idx = min(int(report.fear_greed_index // 20), 4)
        print(f"  Classification: {labels[label_idx]}")
        print(f"  Trend: {report.trend_vs_yesterday}")
        if report.alert_triggered:
            print(f"  ⚠ ALERT: {report.alert_reason}")
        print(f"{'='*60}")

        return report

    def get_latest_sentiment(self) -> Optional[DailySentimentReport]:
        """Get latest sentiment report from history."""
        if not self._history:
            return None
        latest = self._history[-1]
        return DailySentimentReport(
            date=latest.get("date", ""),
            sentiment_score=latest.get("sentiment_score", 0),
            fear_greed_index=latest.get("fear_greed_index", 50),
            total_articles=latest.get("total_articles", 0),
        )

    def get_sentiment_history(self, days: int = 30) -> pd.DataFrame:
        """Get sentiment history as DataFrame."""
        history = self._history[-days:]
        if not history:
            return pd.DataFrame()
        df = pd.DataFrame(history)
        df["date"] = pd.to_datetime(df["date"])
        df = df.set_index("date")
        return df

    def get_fear_greed_for_prediction(self) -> float:
        """
        Get Fear & Greed index for use in prediction pipeline.
        Returns 0-100 scale (50 = neutral).
        """
        latest = self.get_latest_sentiment()
        if latest:
            return latest.fear_greed_index
        return 50.0  # Neutral default


def integrate_sentiment_with_prediction(
    market_data: dict,
    fred_data: Optional[dict] = None,
) -> Dict:
    """
    Run sentiment pipeline and integrate with prediction.

    Returns prediction result enriched with sentiment data.
    """
    from .predictor import run_prediction

    # Run sentiment pipeline
    pipeline = DailySentimentPipeline()
    sentiment_report = pipeline.run_daily()

    # Run prediction
    result = run_prediction(market_data, fred_data=fred_data)

    # Enrich with sentiment
    result["sentiment_score"] = sentiment_report.sentiment_score
    result["fear_greed_index"] = sentiment_report.fear_greed_index
    result["sentiment_trend"] = sentiment_report.trend_vs_yesterday
    result["sentiment_alert"] = sentiment_report.alert_triggered
    result["sentiment_alert_reason"] = sentiment_report.alert_reason
    result["top_positive_news"] = sentiment_report.top_positive_news[:3]
    result["top_negative_news"] = sentiment_report.top_negative_news[:3]

    return result
