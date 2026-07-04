"""
Event-Driven Data Layer — "Mengapa" Harga Bergerak.

Mengambil data event yang menjadi alasan pergerakan harga:
1. Earnings Calendar — jadwal rilis laporan keuangan (yfinance)
2. Corporate Actions — dividen, stock split, rights issue (yfinance)
3. Analyst Recommendations — target price, rating (yfinance)
4. Economic Calendar — BI Rate, CPI, GDP, trade balance (Trading Economics)
5. News Sentiment — RSS feeds Indonesia + global (ai_agent.NewsScraper)
6. Foreign Flow Proxy — net buy/sell asing (estimasi dari price/volume pattern)

Time Lag Analysis:
- Earnings: real-time saat publikasi → harga reaksi instan
- Corporate actions: T-1 announcement → efek di ex-date
- Economic calendar: scheduled → reaksi instan saat rilis
- News: real-time → efek 0-2 jam
- Analyst: real-time → reaksi 0-2 jam
- Foreign flow: end of day → efek hari berikutnya
"""

import pandas as pd
import yfinance as yf
import requests
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Optional, List, Dict

from .config import TARGET_TICKER, BLUE_CHIPS_ID
from .rate_limiter import get_yf_limiter, get_web_limiter


@dataclass
class EarningsEvent:
    ticker: str
    earnings_date: str
    eps_estimate: Optional[float] = None
    reported_eps: Optional[float] = None
    surprise_pct: Optional[float] = None
    days_until: Optional[int] = None
    is_upcoming: bool = False


@dataclass
class CorporateAction:
    ticker: str
    date: str
    action_type: str  # "dividend", "split"
    value: float
    is_upcoming: bool = False


@dataclass
class AnalystRecommendation:
    ticker: str
    recommendation_key: str  # "strong_buy", "buy", "hold", "sell", "strong_sell"
    recommendation_mean: float
    target_mean: Optional[float] = None
    target_high: Optional[float] = None
    target_low: Optional[float] = None
    current_price: Optional[float] = None
    upside_pct: Optional[float] = None
    period: str = ""
    strong_buy: int = 0
    buy: int = 0
    hold: int = 0
    sell: int = 0
    strong_sell: int = 0


@dataclass
class EconomicEvent:
    time: str
    country: str
    event: str
    actual: Optional[str] = None
    previous: Optional[str] = None
    consensus: Optional[str] = None
    forecast: Optional[str] = None
    importance: str = ""  # "high", "medium", "low"
    is_today: bool = False


@dataclass
class NewsSentimentSummary:
    total_articles: int = 0
    positive_pct: float = 0.0
    negative_pct: float = 0.0
    neutral_pct: float = 0.0
    sentiment_score: float = 0.0  # -100 to 100
    fear_greed: float = 50.0  # 0-100
    top_positive: List[dict] = field(default_factory=list)
    top_negative: List[dict] = field(default_factory=list)
    sources_used: List[str] = field(default_factory=list)
    error: Optional[str] = None


@dataclass
class EventDrivenAnalysis:
    earnings_events: List[EarningsEvent] = field(default_factory=list)
    corporate_actions: List[CorporateAction] = field(default_factory=list)
    analyst_recs: List[AnalystRecommendation] = field(default_factory=list)
    economic_events: List[EconomicEvent] = field(default_factory=list)
    news_sentiment: Optional[NewsSentimentSummary] = None
    foreign_flow_proxy: dict = field(default_factory=dict)
    event_risk_score: float = 0.0  # 0-100, higher = more event risk
    upcoming_events_count: int = 0
    recommendations: List[str] = field(default_factory=list)
    summary: str = ""


# =============================================================================
# 1. EARNINGS CALENDAR
# =============================================================================

def _is_index_ticker(ticker: str) -> bool:
    """Check if ticker is a market index (no fundamentals available)."""
    return ticker.startswith("^") or "=X" in ticker or ticker in ("GC=F", "CL=F")


def fetch_earnings_calendar(tickers: Dict[str, str], days_ahead: int = 30) -> List[EarningsEvent]:
    """Fetch upcoming earnings dates for given tickers."""
    events = []
    today = datetime.now().date()


    limiter = get_yf_limiter()
    for name, ticker in tickers.items():
        if _is_index_ticker(ticker):
            continue
        try:
            limiter.acquire()
            t = yf.Ticker(ticker)
            # Method 1: .calendar
            cal = t.calendar
            if cal and "Earnings Date" in cal:
                for ed in cal["Earnings Date"]:
                    if hasattr(ed, 'date'):
                        ed_date = ed
                    else:
                        ed_date = datetime.strptime(str(ed), "%Y-%m-%d").date()
                    days_until = (ed_date - today).days
                    events.append(EarningsEvent(
                        ticker=ticker,
                        earnings_date=ed_date.isoformat(),
                        days_until=days_until,
                        is_upcoming=days_until >= 0,
                    ))

            # Method 2: .earnings_dates (historical + upcoming)
            ed = t.earnings_dates
            if ed is not None and not ed.empty:
                for idx, row in ed.head(5).iterrows():  # Last 5
                    ed_date = idx.date() if hasattr(idx, 'date') else today
                    days_until = (ed_date - today).days
                    if days_until <= days_ahead:
                        events.append(EarningsEvent(
                            ticker=ticker,
                            earnings_date=ed_date.isoformat(),
                            eps_estimate=float(row.get("EPS Estimate", 0)) if pd.notna(row.get("EPS Estimate")) else None,
                            reported_eps=float(row.get("Reported EPS", 0)) if pd.notna(row.get("Reported EPS")) else None,
                            surprise_pct=float(row.get("Surprise(%)", 0)) if pd.notna(row.get("Surprise(%)")) else None,
                            days_until=days_until,
                            is_upcoming=days_until >= 0,
                        ))
        except Exception:
            pass

    # Sort by date
    events.sort(key=lambda x: x.earnings_date)
    return events


# =============================================================================
# 2. CORPORATE ACTIONS
# =============================================================================

def fetch_corporate_actions(tickers: Dict[str, str], days_ahead: int = 30) -> List[CorporateAction]:
    """Fetch upcoming corporate actions (dividends, splits)."""
    actions = []
    today = datetime.now().date()
    future_limit = today + timedelta(days=days_ahead)
    past_limit = today - timedelta(days=7)

    limiter = get_yf_limiter()
    for name, ticker in tickers.items():
        if _is_index_ticker(ticker):
            continue
        try:
            limiter.acquire()
            t = yf.Ticker(ticker)
            acts = t.actions
            if acts is not None and not acts.empty:
                for idx, row in acts.tail(10).iterrows():
                    action_date = idx.date() if hasattr(idx, 'date') else today
                    if past_limit <= action_date <= future_limit:
                        div = float(row.get("Dividends", 0))
                        split = float(row.get("Stock Splits", 0))
                        if div > 0:
                            actions.append(CorporateAction(
                                ticker=ticker, date=action_date.isoformat(),
                                action_type="dividend", value=div,
                                is_upcoming=action_date >= today,
                            ))
                        if split > 0:
                            actions.append(CorporateAction(
                                ticker=ticker, date=action_date.isoformat(),
                                action_type="split", value=split,
                                is_upcoming=action_date >= today,
                            ))

            # Also check info for ex-dividend date
            info = t.info
            if info:
                ex_div_ts = info.get("exDividendDate")
                if ex_div_ts and ex_div_ts > 0:
                    ex_div_date = datetime.fromtimestamp(ex_div_ts).date()
                    if past_limit <= ex_div_date <= future_limit:
                        div_rate = info.get("dividendRate", 0)
                        # Check if not already added
                        if not any(a.date == ex_div_date.isoformat() and a.ticker == ticker for a in actions):
                            actions.append(CorporateAction(
                                ticker=ticker, date=ex_div_date.isoformat(),
                                action_type="dividend", value=div_rate,
                                is_upcoming=ex_div_date >= today,
                            ))
        except Exception:
            pass

    actions.sort(key=lambda x: x.date)
    return actions


# =============================================================================
# 3. ANALYST RECOMMENDATIONS
# =============================================================================

def fetch_analyst_recommendations(tickers: Dict[str, str]) -> List[AnalystRecommendation]:
    """Fetch analyst recommendations and target prices."""
    recs = []

    limiter = get_yf_limiter()
    for name, ticker in tickers.items():
        if _is_index_ticker(ticker):
            continue
        try:
            limiter.acquire()
            t = yf.Ticker(ticker)
            info = t.info
            if not info:
                continue

            rec_key = info.get("recommendationKey", "")
            rec_mean = info.get("recommendationMean", 0)
            target_mean = info.get("targetMeanPrice")
            target_high = info.get("targetHighPrice")
            target_low = info.get("targetLowPrice")
            current = info.get("currentPrice") or info.get("regularMarketPrice")

            upside = None
            if target_mean and current and current > 0:
                upside = (target_mean - current) / current * 100

            # Get detailed recommendation counts
            strong_buy = buy = hold = sell = strong_sell = 0
            period = ""
            try:
                recs_df = t.recommendations
                if recs_df is not None and not recs_df.empty:
                    latest = recs_df.iloc[0]
                    period = str(latest.get("period", ""))
                    strong_buy = int(latest.get("strongBuy", 0))
                    buy = int(latest.get("buy", 0))
                    hold = int(latest.get("hold", 0))
                    sell = int(latest.get("sell", 0))
                    strong_sell = int(latest.get("strongSell", 0))
            except Exception:
                pass

            if rec_key or target_mean:
                recs.append(AnalystRecommendation(
                    ticker=ticker,
                    recommendation_key=rec_key,
                    recommendation_mean=rec_mean,
                    target_mean=target_mean,
                    target_high=target_high,
                    target_low=target_low,
                    current_price=current,
                    upside_pct=round(upside, 1) if upside is not None else None,
                    period=period,
                    strong_buy=strong_buy,
                    buy=buy,
                    hold=hold,
                    sell=sell,
                    strong_sell=strong_sell,
                ))
        except Exception:
            pass

    return recs


# =============================================================================
# 4. ECONOMIC CALENDAR (Trading Economics scrape)
# =============================================================================

def fetch_economic_calendar(country: str = "indonesia") -> List[EconomicEvent]:
    """Scrape economic calendar from Trading Economics."""
    events = []
    try:
        limiter = get_web_limiter()
        limiter.acquire()
        url = f"https://tradingeconomics.com/{country}/calendar"
        resp = requests.get(url, timeout=15, headers={"User-Agent": "Mozilla/5.0"})
        if resp.status_code != 200:
            return events

        from bs4 import BeautifulSoup
        soup = BeautifulSoup(resp.text, "html.parser")
        table = soup.find("table", {"id": "calendar"})
        if not table:
            return events

        rows = table.find_all("tr")
        datetime.now().strftime("%Y-%m-%d")

        for row in rows:
            cells = row.find_all("td")
            if len(cells) < 5:
                continue
            texts = [c.get_text(strip=True) for c in cells]

            time_val = texts[0] if texts[0] else ""
            country_val = texts[1] if len(texts) > 1 else ""
            event_name = texts[3] if len(texts) > 3 else ""
            actual = texts[4] if len(texts) > 4 and texts[4] else None
            previous = texts[5] if len(texts) > 5 and texts[5] else None
            consensus = texts[6] if len(texts) > 6 and texts[6] else None
            forecast = texts[7] if len(texts) > 7 and texts[7] else None

            if not event_name:
                continue

            # Determine importance
            importance = "low"
            high_impact_keywords = [
                "interest rate", "rate decision", "inflation rate", "cpi",
                "gdp", "unemployment", "trade balance", "current account",
                "manufacturing pmi", "services pmi", "retail sales",
                "bank indonesia",
            ]
            if any(kw in event_name.lower() for kw in high_impact_keywords):
                importance = "high"
            elif any(kw in event_name.lower() for kw in ["loan", "deposit", "car sales", "motorcycle"]):
                importance = "medium"

            events.append(EconomicEvent(
                time=time_val,
                country=country_val,
                event=event_name,
                actual=actual,
                previous=previous,
                consensus=consensus,
                forecast=forecast,
                importance=importance,
                is_today=True,  # TE shows today's calendar
            ))

    except ImportError:
        print("[SKIP] beautifulsoup4 not installed, economic calendar skipped")
    except Exception as e:
        print(f"[WARNING] Economic calendar fetch failed: {e}")

    return events


# =============================================================================
# 5. NEWS SENTIMENT (connect existing RSS scraper)
# =============================================================================

def fetch_news_sentiment(max_per_source: int = 5) -> NewsSentimentSummary:
    """Fetch and analyze news sentiment from RSS feeds."""
    try:
        from .ai_agent import NewsScraper, FinBERTSentiment

        scraper = NewsScraper()
        articles = scraper.fetch_all_sources(max_per_source=max_per_source)

        if not articles:
            return NewsSentimentSummary(error="No articles found from RSS feeds")

        # Analyze sentiment
        finbert = FinBERTSentiment()
        scraper.analyze_sentiments(finbert)
        summary = scraper.get_sentiment_summary()

        sentiment_score = summary.get("sentiment_score", 0)
        fear_greed = (sentiment_score + 100) / 2
        fear_greed = max(0, min(100, fear_greed))

        # Top positive/negative
        top_pos = []
        top_neg = []
        for article in scraper.articles:
            if article.sentiment:
                item = {
                    "title": article.title,
                    "source": article.source,
                    "sentiment": article.sentiment.sentiment,
                    "confidence": round(article.sentiment.confidence, 3),
                }
                if article.sentiment.sentiment == "positive":
                    top_pos.append(item)
                elif article.sentiment.sentiment == "negative":
                    top_neg.append(item)

        top_pos.sort(key=lambda x: x["confidence"], reverse=True)
        top_neg.sort(key=lambda x: x["confidence"], reverse=True)

        sources = list(set(a.source for a in scraper.articles))

        return NewsSentimentSummary(
            total_articles=len(articles),
            positive_pct=summary.get("positive", 0),
            negative_pct=summary.get("negative", 0),
            neutral_pct=summary.get("neutral", 0),
            sentiment_score=round(sentiment_score, 1),
            fear_greed=round(fear_greed, 1),
            top_positive=top_pos[:5],
            top_negative=top_neg[:5],
            sources_used=sources,
        )
    except Exception as e:
        return NewsSentimentSummary(error=str(e))


# =============================================================================
# 6. FOREIGN FLOW PROXY
# =============================================================================

def estimate_foreign_flow_proxy(df: pd.DataFrame, close_col: str = "Close",
                                volume_col: str = "Volume",
                                lookback: int = 20) -> dict:
    """
    Estimate foreign flow direction from price-volume pattern.
    
    Heuristic:
    - Price up + volume surge = likely foreign net buy
    - Price down + volume surge = likely foreign net sell
    - Price up + low volume = domestic retail driven
    - Price down + low volume = lack of interest
    
    This is a PROXY — actual foreign flow data requires IDX API (berbayar).
    """
    if len(df) < lookback:
        return {"signal": "unknown", "confidence": 0}

    recent = df.tail(lookback)
    returns = recent[close_col].pct_change().dropna()
    volume = recent[volume_col]

    avg_vol = volume.mean()
    recent_vol = volume.iloc[-1]
    vol_ratio = recent_vol / avg_vol if avg_vol > 0 else 1

    recent_return = returns.iloc[-1] if len(returns) > 0 else 0
    cumulative_return = (recent[close_col].iloc[-1] / recent[close_col].iloc[0] - 1) * 100

    # Large volume + price up = foreign buying
    if vol_ratio > 1.5 and recent_return > 0.01:
        signal = "foreign_net_buy"
        confidence = min(100, vol_ratio * 30)
    elif vol_ratio > 1.5 and recent_return < -0.01:
        signal = "foreign_net_sell"
        confidence = min(100, vol_ratio * 30)
    elif vol_ratio < 0.7 and abs(recent_return) < 0.005:
        signal = "low_activity"
        confidence = 50
    elif recent_return > 0:
        signal = "domestic_buy"
        confidence = 40
    else:
        signal = "domestic_sell"
        confidence = 40

    return {
        "signal": signal,
        "confidence": round(confidence, 1),
        "volume_ratio": round(vol_ratio, 2),
        "recent_return_pct": round(recent_return * 100, 2),
        "cumulative_return_pct": round(cumulative_return, 2),
        "note": "Proxy estimation — actual foreign flow requires IDX API",
    }


# =============================================================================
# 7. COMBINED EVENT-DRIVEN ANALYSIS
# =============================================================================

def run_event_driven_analysis(
    market_data: Dict[str, pd.DataFrame],
    target_ticker: str = TARGET_TICKER,
    include_news: bool = True,
    include_economic: bool = True,
) -> EventDrivenAnalysis:
    """
    Run full event-driven analysis combining all event sources.
    """
    datetime.now().date()
    recommendations = []
    event_risk_score = 0
    upcoming_count = 0

    # 1. Earnings Calendar
    print("[EVENT] Fetching earnings calendar...")
    # BLUE_CHIPS_ID is {ticker: name}, we need {name: ticker} for fetch functions
    earnings_tickers = {name: ticker for ticker, name in BLUE_CHIPS_ID.items()}
    if target_ticker not in earnings_tickers.values():
        earnings_tickers["TARGET"] = target_ticker
    earnings = fetch_earnings_calendar(earnings_tickers, days_ahead=30)

    upcoming_earnings = [e for e in earnings if e.is_upcoming and e.days_until is not None and e.days_until <= 7]
    if upcoming_earnings:
        upcoming_count += len(upcoming_earnings)
        event_risk_score += min(30, len(upcoming_earnings) * 10)
        for e in upcoming_earnings[:3]:
            recommendations.append(f"📅 Earnings {e.ticker} dalam {e.days_until} hari ({e.earnings_date}) — volatilitas tinggi expected")

    # 2. Corporate Actions
    print("[EVENT] Fetching corporate actions...")
    actions = fetch_corporate_actions(earnings_tickers, days_ahead=30)
    upcoming_actions = [a for a in actions if a.is_upcoming]
    if upcoming_actions:
        upcoming_count += len(upcoming_actions)
        event_risk_score += min(20, len(upcoming_actions) * 5)
        for a in upcoming_actions[:3]:
            if a.action_type == "dividend":
                recommendations.append(f"💰 Dividen {a.ticker} ({a.date}) — Rp{a.value}/share, ex-date mendekat")
            elif a.action_type == "split":
                recommendations.append(f"✂️ Stock split {a.ticker} ({a.date}) — ratio {a.value}")

    # 3. Analyst Recommendations
    print("[EVENT] Fetching analyst recommendations...")
    analyst_recs = fetch_analyst_recommendations(earnings_tickers)
    for rec in analyst_recs[:5]:
        if rec.recommendation_key == "strong_buy" and rec.upside_pct and rec.upside_pct > 15:
            recommendations.append(f"🎯 Analyst STRONG BUY {rec.ticker} — upside {rec.upside_pct}% (target: {rec.target_mean})")
        elif rec.recommendation_key == "strong_sell":
            recommendations.append(f"⚠️ Analyst STRONG SELL {rec.ticker} — downside {abs(rec.upside_pct or 0)}%")

    # 4. Economic Calendar
    economic_events = []
    if include_economic:
        print("[EVENT] Fetching economic calendar...")
        economic_events = fetch_economic_calendar("indonesia")
        high_impact = [e for e in economic_events if e.importance == "high" and e.actual is None]
        if high_impact:
            event_risk_score += min(25, len(high_impact) * 8)
            for e in high_impact[:3]:
                recommendations.append(f"🏛️ Economic event: {e.event} — forecast: {e.forecast or 'N/A'}, previous: {e.previous or 'N/A'}")

    # 5. News Sentiment
    news_sentiment = None
    if include_news:
        print("[EVENT] Fetching news sentiment...")
        news_sentiment = fetch_news_sentiment(max_per_source=5)
        if news_sentiment and not news_sentiment.error:
            if news_sentiment.sentiment_score < -30:
                event_risk_score += 15
                recommendations.append(f"📰 News sentiment NEGATIF ({news_sentiment.sentiment_score}) — caution warranted")
            elif news_sentiment.sentiment_score > 30:
                recommendations.append(f"📰 News sentiment POSITIF ({news_sentiment.sentiment_score}) — supportive for market")
            if news_sentiment.top_negative:
                top = news_sentiment.top_negative[0]
                recommendations.append(f"📰 Top negative: \"{top['title'][:60]}...\" ({top['source']})")
            if news_sentiment.top_positive:
                top = news_sentiment.top_positive[0]
                recommendations.append(f"📰 Top positive: \"{top['title'][:60]}...\" ({top['source']})")
        elif news_sentiment and news_sentiment.error:
            recommendations.append(f"📰 News sentiment unavailable: {news_sentiment.error}")

    # 6. Foreign Flow Proxy
    foreign_flow = {"signal": "unknown", "confidence": 0}
    target_data = None
    for name, data in market_data.items():
        if target_ticker in name or name == "IHSG":
            target_data = data
            break
    if target_data is not None and not target_data.empty:
        foreign_flow = estimate_foreign_flow_proxy(target_data)
        if foreign_flow["signal"] == "foreign_net_sell" and foreign_flow["confidence"] > 60:
            event_risk_score += 10
            recommendations.append(f"💸 Foreign flow proxy: NET SELL (confidence {foreign_flow['confidence']}%)")
        elif foreign_flow["signal"] == "foreign_net_buy" and foreign_flow["confidence"] > 60:
            recommendations.append(f"💵 Foreign flow proxy: NET BUY (confidence {foreign_flow['confidence']}%)")

    # Clamp risk score
    event_risk_score = min(100, event_risk_score)

    # Summary
    if event_risk_score > 50:
        summary = f"HIGH event risk ({event_risk_score}/100) — {upcoming_count} upcoming events. Extra caution needed."
    elif event_risk_score > 25:
        summary = f"MODERATE event risk ({event_risk_score}/100) — {upcoming_count} upcoming events. Monitor closely."
    else:
        summary = f"LOW event risk ({event_risk_score}/100) — {upcoming_count} upcoming events. Normal conditions."

    if not recommendations:
        recommendations.append("No significant events detected in next 30 days.")

    # Send in-app notification for high event risk
    if event_risk_score > 25:
        try:
            from .notifier import send_in_app
            level = "error" if event_risk_score > 50 else "warning"
            send_in_app(
                kategori="EVENT",
                judul=f"⚠️ Event Risk {event_risk_score:.0f}/100 — {upcoming_count} upcoming events",
                pesan=(
                    f"{summary}\n\n"
                    f"Rekomendasi:\n" + "\n".join(f"• {r}" for r in recommendations[:8])
                ),
                level=level,
            )
        except Exception:
            pass

    return EventDrivenAnalysis(
        earnings_events=earnings,
        corporate_actions=actions,
        analyst_recs=analyst_recs,
        economic_events=economic_events,
        news_sentiment=news_sentiment,
        foreign_flow_proxy=foreign_flow,
        event_risk_score=round(event_risk_score, 1),
        upcoming_events_count=upcoming_count,
        recommendations=recommendations,
        summary=summary,
    )
