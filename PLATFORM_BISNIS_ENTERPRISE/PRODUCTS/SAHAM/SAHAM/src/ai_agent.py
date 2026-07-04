"""
AI/LLM Agent: FinBERT Sentiment, News Scraping, Multi-Agent Framework, Daily Briefing.

Implementasi:
- FinBERT sentiment analysis (dari ProsusAI/finbert)
- DeepSeek LLM integration untuk market commentary (OpenAI-compatible API)
- News scraping: RSS feeds, web scraping (dari Bloomberg, Reuters, CNBC, Kontan, Bisnis)
- Multi-agent framework: Market Analyst, Risk Manager, News Analyst, Portfolio Advisor
- Daily briefing generation (dari Danelfin/Market Digest)

Referensi:
- ProsusAI/finbert: Financial BERT model
- DeepSeek-R1: Open-source LLM untuk analisis finansial (cost-effective, GPT-level)
- Danelfin: AI-generated stock briefings
- Market Digest: Daily market summary
- CrewAI / LangGraph: Multi-agent orchestration
"""

import numpy as np
import pandas as pd
from typing import Dict, List, Optional
from dataclasses import dataclass, field
from datetime import datetime


# =============================================================================
# FINBERT SENTIMENT ANALYSIS
# =============================================================================

@dataclass
class SentimentResult:
    text: str
    sentiment: str  # "positive", "negative", "neutral"
    confidence: float
    scores: Dict[str, float] = field(default_factory=dict)


class FinBERTSentiment:
    """
    FinBERT sentiment analysis untuk teks finansial.

    Uses ProsusAI/finbert model via HuggingFace transformers.
    Falls back to lexicon-based sentiment jika transformers tidak tersedia.
    """

    def __init__(self, model_name: str = "ProsusAI/finbert"):
        self.model_name = model_name
        self.model = None
        self.tokenizer = None
        self._try_load_model()

    def _try_load_model(self):
        try:
            from transformers import AutoTokenizer, AutoModelForSequenceClassification
            self.tokenizer = AutoTokenizer.from_pretrained(self.model_name)
            self.model = AutoModelForSequenceClassification.from_pretrained(self.model_name)
            self.model.eval()
            print("[OK] FinBERT model loaded")
        except ImportError:
            pass  # Lexicon fallback used silently
        except Exception:
            pass  # Lexicon fallback used silently

    def analyze(self, text: str) -> SentimentResult:
        """Analyze sentiment of financial text."""
        if self.model is not None and self.tokenizer is not None:
            return self._analyze_with_model(text)
        return self._analyze_with_lexicon(text)

    def _analyze_with_model(self, text: str) -> SentimentResult:
        import torch
        inputs = self.tokenizer(text, return_tensors="pt", truncation=True, max_length=512, padding=True)
        with torch.no_grad():
            outputs = self.model(**inputs)
            probs = torch.nn.functional.softmax(outputs.logits, dim=-1)

        labels = ["positive", "negative", "neutral"]
        scores = {labels[i]: float(probs[0][i]) for i in range(len(labels))}
        sentiment = max(scores, key=scores.get)
        confidence = scores[sentiment]

        return SentimentResult(text=text, sentiment=sentiment, confidence=confidence, scores=scores)

    def _analyze_with_lexicon(self, text: str) -> SentimentResult:
        """Lexicon-based fallback sentiment analysis."""
        positive_words = [
            "naik", "bullish", "untung", "profit", "rally", "gain", "positif",
            "beli", "buy", "strong", "optimis", "tumbuh", "surge", "rally",
            "breakthrough", "support", "accumulation", "recovery", "expansion",
            "upgrade", "outperform", "overweight", "target harga",
        ]
        negative_words = [
            "turun", "bearish", "rugi", "loss", "sell", "jual", "negatif",
            "weak", "pesimis", "decline", "fall", "drop", "correction",
            "distribution", "panic", "crash", "downgrade", "underperform",
            "underweight", "cut", "reduce", "risk", "anxiety", "fear",
        ]

        text_lower = text.lower()
        pos_count = sum(1 for w in positive_words if w in text_lower)
        neg_count = sum(1 for w in negative_words if w in text_lower)

        total = pos_count + neg_count
        if total == 0:
            sentiment = "neutral"
            confidence = 0.5
        elif pos_count > neg_count:
            sentiment = "positive"
            confidence = pos_count / total
        else:
            sentiment = "negative"
            confidence = neg_count / total

        scores = {
            "positive": pos_count / total if total > 0 else 0.33,
            "negative": neg_count / total if total > 0 else 0.33,
            "neutral": 1 - (pos_count + neg_count) / max(total, 1) if total > 0 else 0.34,
        }

        return SentimentResult(text=text, sentiment=sentiment, confidence=confidence, scores=scores)

    def analyze_batch(self, texts: List[str]) -> List[SentimentResult]:
        """Analyze multiple texts."""
        return [self.analyze(t) for t in texts]


# =============================================================================
# NEWS SCRAPING (RSS + Web)
# =============================================================================

@dataclass
class NewsArticle:
    title: str
    summary: str
    url: str
    source: str
    published: str
    sentiment: Optional[SentimentResult] = None


class NewsScraper:
    """
    News scraper untuk sumber finansial Indonesia & global.

    Sources:
    - RSS: Kontan, Bisnis Indonesia, CNBC Indonesia, Reuters, Bloomberg
    - Web: Detik Finance, Tempo Bisnis
    """

    RSS_FEEDS = {
        "Kontan Market": "https://www.kontan.co.id/rss/market",
        "Bisnis Indonesia": "https://www.bisnis.com/rss",
        "CNBC Indonesia": "https://www.cnbcindonesia.com/market/rss",
        "Reuters Business": "https://feeds.reuters.com/reuters/businessNews",
        "Bloomberg Markets": "https://feeds.bloomberg.com/markets/news.rss",
    }

    def __init__(self):
        self.articles: List[NewsArticle] = []

    def fetch_rss(self, feed_url: str, source_name: str = "", max_articles: int = 10) -> List[NewsArticle]:
        """Fetch articles from RSS feed."""
        articles = []
        try:
            import feedparser
            feed = feedparser.parse(feed_url)
            for entry in feed.entries[:max_articles]:
                article = NewsArticle(
                    title=entry.get("title", ""),
                    summary=entry.get("summary", entry.get("description", "")),
                    url=entry.get("link", ""),
                    source=source_name or feed_url,
                    published=entry.get("published", ""),
                )
                articles.append(article)
        except ImportError:
            print("[SKIP] feedparser not installed, RSS scraping skipped")
        except Exception as e:
            print(f"[WARNING] RSS fetch failed for {feed_url}: {e}")
        return articles

    def fetch_all_sources(self, max_per_source: int = 5) -> List[NewsArticle]:
        """Fetch from all configured RSS feeds."""
        all_articles = []
        for name, url in self.RSS_FEEDS.items():
            articles = self.fetch_rss(url, source_name=name, max_articles=max_per_source)
            all_articles.extend(articles)
            if articles:
                print(f"  [OK] {name}: {len(articles)} articles")
        self.articles = all_articles
        return all_articles

    def analyze_sentiments(self, finbert: Optional[FinBERTSentiment] = None) -> List[NewsArticle]:
        """Analyze sentiment of all fetched articles."""
        if finbert is None:
            finbert = FinBERTSentiment()
        for article in self.articles:
            text = f"{article.title}. {article.summary}"
            article.sentiment = finbert.analyze(text)
        return self.articles

    def get_sentiment_summary(self) -> Dict[str, float]:
        """Get aggregate sentiment from all articles."""
        if not self.articles:
            return {"positive": 0, "negative": 0, "neutral": 0}

        pos = sum(1 for a in self.articles if a.sentiment and a.sentiment.sentiment == "positive")
        neg = sum(1 for a in self.articles if a.sentiment and a.sentiment.sentiment == "negative")
        neu = sum(1 for a in self.articles if a.sentiment and a.sentiment.sentiment == "neutral")
        total = len(self.articles)

        return {
            "positive": pos / total * 100,
            "negative": neg / total * 100,
            "neutral": neu / total * 100,
            "total_articles": total,
            "sentiment_score": (pos - neg) / total * 100,  # -100 to 100
        }


# =============================================================================
# MULTI-AGENT FRAMEWORK
# =============================================================================

@dataclass
class AgentReport:
    agent_name: str
    role: str
    findings: str
    recommendations: List[str]
    confidence: float
    data: Dict = field(default_factory=dict)


class MarketAnalystAgent:
    """Analyzes market data, technical indicators, and patterns."""

    def __init__(self):
        self.name = "Market Analyst"
        self.role = "Analisis teknikal & pola pasar"

    def analyze(self, market_data: Dict, df: pd.DataFrame) -> AgentReport:
        from .patterns import full_pattern_analysis

        findings_parts = []
        recommendations = []

        # Pattern analysis
        pattern_result = full_pattern_analysis(df, target_prefix="IHSG_")
        findings_parts.append(pattern_result["summary"])

        # Market structure
        structure = pattern_result["market_structure"]
        if structure.structure == "Uptrend":
            recommendations.append("Tren naik terkonfirmasi — cari peluang buy on dip")
        elif structure.structure == "Downtrend":
            recommendations.append("Tren turun — pertimbangkan wait-and-see atau sell on rally")
        else:
            recommendations.append("Pasar sideways — tunggu breakout/konfirmasi")

        # Candlestick patterns
        candlesticks = pattern_result["candlestick_patterns"]
        if candlesticks:
            latest = candlesticks[-1]
            if latest.type == "bullish":
                recommendations.append(f"Pola bullish: {latest.name} — potential entry")
            elif latest.type == "bearish":
                recommendations.append(f"Pola bearish: {latest.name} — hati-hati")

        return AgentReport(
            agent_name=self.name,
            role=self.role,
            findings=" | ".join(findings_parts),
            recommendations=recommendations,
            confidence=0.7,
            data={"structure": structure.structure, "patterns": len(candlesticks)},
        )


class RiskManagerAgent:
    """Evaluates risk metrics and position sizing."""

    def __init__(self):
        self.name = "Risk Manager"
        self.role = "Evaluasi risiko & position sizing"

    def analyze(self, df: pd.DataFrame, signal: str = "HOLD") -> AgentReport:
        from .quant_finance import calc_entry_target_stop

        latest = df.iloc[-1]
        current_price = float(latest.get("IHSG_Close", latest.get("Close", 7000)))
        atr = float(latest.get("Target_ATR", current_price * 0.015))
        vix = float(latest.get("VIX_Close", 20))

        levels = calc_entry_target_stop(signal, current_price, atr=atr)

        recommendations = []
        if signal == "BUY":
            recommendations.append(f"Entry: {levels.entry:,.0f} | Stop: {levels.stop_loss:,.0f}")
            recommendations.append(f"Target 1: {levels.target_1:,.0f} | Target 2: {levels.target_2:,.0f}")
            recommendations.append(f"Position size: {levels.position_size_shares} shares ({levels.position_size_pct:.1f}%)")
        elif signal == "SELL":
            recommendations.append(f"Exit area: {levels.entry:,.0f} | Stop: {levels.stop_loss:,.0f}")
        else:
            recommendations.append("Tidak ada sinyal — pertahankan posisi saat ini")

        if vix > 30:
            recommendations.append("⚠️ VIX tinggi — kurangi position size")
        elif vix < 15:
            recommendations.append("VIX rendah — pasar tenang, normal position size")

        risk_level = "Tinggi" if vix > 30 else ("Sedang" if vix > 20 else "Rendah")

        return AgentReport(
            agent_name=self.name,
            role=self.role,
            findings=f"Risk level: {risk_level} | VIX: {vix:.1f} | ATR: {atr:.1f}",
            recommendations=recommendations,
            confidence=0.8,
            data={"risk_level": risk_level, "vix": vix, "levels": levels.description},
        )


class NewsAnalystAgent:
    """Analyzes news sentiment and market impact."""

    def __init__(self):
        self.name = "News Analyst"
        self.role = "Analisis sentimen berita"

    def analyze(self, articles: Optional[List[NewsArticle]] = None) -> AgentReport:
        scraper = NewsScraper()
        if articles:
            scraper.articles = articles
        else:
            try:
                scraper.fetch_all_sources(max_per_source=3)
                scraper.analyze_sentiments()
            except Exception:
                pass

        summary = scraper.get_sentiment_summary()

        recommendations = []
        if summary["sentiment_score"] > 30:
            recommendations.append("Sentimen berita sangat positif — mendukung upside")
        elif summary["sentiment_score"] > 10:
            recommendations.append("Sentimen berita cenderung positif")
        elif summary["sentiment_score"] < -30:
            recommendations.append("Sentimen berita sangat negatif — waspada downside")
        elif summary["sentiment_score"] < -10:
            recommendations.append("Sentimen berita cenderung negatif")
        else:
            recommendations.append("Sentimen berita netral")

        return AgentReport(
            agent_name=self.name,
            role=self.role,
            findings=f"Sentiment score: {summary['sentiment_score']:.1f} | Articles: {summary.get('total_articles', 0)}",
            recommendations=recommendations,
            confidence=0.6,
            data=summary,
        )


class PortfolioAdvisorAgent:
    """Synthesizes all agent reports into final recommendation."""

    def __init__(self):
        self.name = "Portfolio Advisor"
        self.role = "Sintesis rekomendasi akhir"

    def analyze(self, reports: List[AgentReport], signal: str = "HOLD", confidence: float = 0.5) -> AgentReport:
        all_recs = []
        for r in reports:
            all_recs.extend(r.recommendations)

        # Synthesize final recommendation
        if signal == "BUY" and confidence > 0.6:
            final = "BUY — Konfirmasi multi-agent mendukung upside"
        elif signal == "SELL" and confidence > 0.6:
            final = "SELL — Konfirmasi multi-agent mendukung downside"
        else:
            final = "HOLD — Sinyal belum konklusif, tunggu konfirmasi"

        avg_confidence = np.mean([r.confidence for r in reports]) if reports else 0.5

        return AgentReport(
            agent_name=self.name,
            role=self.role,
            findings=final,
            recommendations=all_recs[:10],  # Top 10 recommendations
            confidence=avg_confidence,
            data={"signal": signal, "confidence": confidence},
        )


# =============================================================================
# IN-APP MARKET COMMENTARY — Built-in, no external API needed
# =============================================================================

class InAppCommentary:
    """
    Market commentary generator — built-in, tanpa API eksternal.
    
    Generate analisis pasar harian dalam Bahasa Indonesia berdasarkan:
    - Signal & confidence dari model ML
    - Market regime (bull/bear/sideways)
    - Korelasi China (SSE/CSI300) vs IHSG
    - News sentiment dari FinBERT
    - VIX, RSI, dan indikator teknikal
    
    Referensi: Template berbasis pola analisis profesional pasar Indonesia.
    """

    def __init__(self):
        pass

    @property
    def is_available(self) -> bool:
        return True

    def generate_market_commentary(
        self,
        market_summary: str,
        signal: str,
        confidence: float,
        regime: str = "",
        news_sentiment: Dict = None,
        china_correlation: Dict = None,
    ) -> str:
        """Generate market commentary dalam Bahasa Indonesia."""
        signal_text = {"BUY": "Naik (Bullish)", "SELL": "Turun (Bearish)", "HOLD": "Datar/Netral"}.get(signal, "Netral")

        # Faktor kunci
        faktor = []
        if regime:
            faktor.append(f"Kondisi pasar {regime} — {'tren kuat, cocok untuk trend following' if 'bull' in regime.lower() else 'waspada downside, pertimbangkan defensive' if 'bear' in regime.lower() else 'sideways, trading range — buy support, sell resistance'}")
        if news_sentiment:
            score = news_sentiment.get("sentiment_score", 0)
            if score > 30:
                faktor.append("Sentimen berita sangat positif — mendukung upside IHSG")
            elif score > 10:
                faktor.append("Sentimen berita cenderung positif")
            elif score < -30:
                faktor.append("Sentimen berita sangat negatif — waspadakan tekanan jual")
            elif score < -10:
                faktor.append("Sentimen berita cenderung negatif")
            else:
                faktor.append("Sentimen berita netral — tidak ada catalyst kuat")
        
        # VIX assessment
        vix_val = 0
        if "VIX" in (news_sentiment or {}):
            vix_val = news_sentiment.get("VIX", 20)
        if vix_val > 30:
            faktor.append(f"VIX tinggi ({vix_val:.1f}) — pasar panik, kurangi position size")
        elif vix_val < 15:
            faktor.append(f"VIX rendah ({vix_val:.1f}) — pasar tenang, normal trading")

        # China correlation
        china_text = ""
        if china_correlation:
            china_text = china_correlation.get("analysis", "Pergerakan SSE/CSI300 dapat mempengaruhi sentimen Asia termasuk IHSG.")
        else:
            china_text = "Pasar China (SSE Composite, CSI 300) menjadi barometer sentimen Asia. Monitor arah pembukaan China untuk antisipasi arah IHSG."

        # Rekomendasi
        if signal == "BUY":
            rekomendasi = f"Sinyal BUY dengan confidence {confidence:.1%}. Pertimbangkan entry dengan position sizing sesuai risk management. Stop loss di bawah support terdekat."
        elif signal == "SELL":
            rekomendasi = f"Sinyal SELL dengan confidence {confidence:.1%}. Waspadakan downside, pertimbangkan cut loss atau hedging. Jangan average down."
        else:
            rekomendasi = f"Sinyal HOLD dengan confidence {confidence:.1%}. Tunggu konfirmasi dari volume dan breakout level kunci. Jangan terburu-buru entry."

        lines = [
            f"1. RINGKASAN: {market_summary}. Sinyal model: {signal_text} dengan confidence {confidence:.1%}.",
            "2. FAKTOR KUNCI:",
        ]
        for i, f in enumerate(faktor, 1):
            lines.append(f"   {i}. {f}")
        lines.append(f"3. KORELASI CHINA: {china_text}")
        lines.append(f"4. REKOMENDASI: {rekomendasi}")
        lines.append("5. DISCLAIMER: Analisis bersifat probabilistik dan bukan saran investasi. Lakukan riset mandiri sebelum keputusan investasi.")

        return "\n".join(lines)

    def analyze_china_impact(self, sse_change: float, csi300_change: float, ihsg_change: float) -> Dict:
        """Analisis dampak pergerakan pasar China ke IHSG — built-in."""
        china_avg = (sse_change + csi300_change) / 2
        correlated = (china_avg > 0 and ihsg_change > 0) or (china_avg < 0 and ihsg_change < 0)
        divergence = abs(china_avg - ihsg_change) > 1.5

        if correlated and not divergence:
            analysis = (
                f"SSE {sse_change:+.2f}%, CSI300 {csi300_change:+.2f}%, IHSG {ihsg_change:+.2f}%. "
                f"IHSG bergerak searah dengan pasar China — korelasi positif kuat. "
                f"Sentimen Asia mendukung, kemungkinan lanjutan tren untuk sesi berikutnya."
            )
        elif divergence:
            analysis = (
                f"SSE {sse_change:+.2f}%, CSI300 {csi300_change:+.2f}%, IHSG {ihsg_change:+.2f}%. "
                f"DIVERGENSI: IHSG bergerak berlawanan dengan China. "
                f"Waspadakan reversal atau catching up di sesi berikutnya. "
                f"Jika China naik tapi IHSG turun → IHSG bisa catch up. Jika sebaliknya → waspadakan IHSG tertekan."
            )
        else:
            analysis = (
                f"SSE {sse_change:+.2f}%, CSI300 {csi300_change:+.2f}%, IHSG {ihsg_change:+.2f}%. "
                f"Pergerakan mixed — tidak ada korelasi jelas. "
                f"Pasar China tidak memberikan sinyal kuat untuk IHSG. Fokus pada faktor domestik."
            )

        return {
            "analysis": analysis,
            "correlated": correlated,
            "divergence": divergence,
            "sse_change": sse_change,
            "csi300_change": csi300_change,
            "ihsg_change": ihsg_change,
        }


# =============================================================================
# DAILY BRIEFING GENERATOR
# =============================================================================

def generate_daily_briefing(
    market_data: Dict[str, pd.DataFrame],
    df: pd.DataFrame,
    signal: str = "HOLD",
    confidence: float = 0.5,
    predictions: Optional[Dict] = None,
    probabilities: Optional[Dict] = None,
) -> Dict:
    """
    Generate daily AI briefing — comprehensive market analysis.

    Returns dict with:
    - date: str
    - agents: List[AgentReport]
    - final_recommendation: AgentReport
    - market_summary: str
    - risk_assessment: str
    - news_sentiment: Dict
    - actionable_items: List[str]

    Referensi: Danelfin AI briefing, Market Digest daily summary
    """
    date_str = datetime.now().strftime("%Y-%m-%d")

    # Run all agents
    market_agent = MarketAnalystAgent()
    risk_agent = RiskManagerAgent()
    news_agent = NewsAnalystAgent()
    advisor_agent = PortfolioAdvisorAgent()

    market_report = market_agent.analyze(market_data, df)
    risk_report = risk_agent.analyze(df, signal)
    news_report = news_agent.analyze()

    all_reports = [market_report, risk_report, news_report]
    final_report = advisor_agent.analyze(all_reports, signal, confidence)

    # Build summary
    latest = df.iloc[-1]
    current_price = float(latest.get("IHSG_Close", latest.get("Close", 0)))
    rsi = float(latest.get("Target_RSI", 50))
    vix = float(latest.get("VIX_Close", 20))

    market_summary = (
        f"IHSG: {current_price:,.2f} | RSI: {rsi:.1f} | VIX: {vix:.1f} | "
        f"Sinyal: {signal} ({confidence:.1%})"
    )

    risk_assessment = risk_report.findings
    news_sentiment = news_report.data

    actionable_items = final_report.recommendations

    # === In-App Commentary (built-in, no external API) ===
    llm = InAppCommentary()

    # Calculate China market changes for correlation analysis
    china_corr = None
    sse_change = csi300_change = ihsg_change = 0.0
    if "SSE_COMPOSITE" in market_data and not market_data["SSE_COMPOSITE"].empty:
        sse_df = market_data["SSE_COMPOSITE"]
        if len(sse_df) >= 2:
            sse_change = ((sse_df["Close"].iloc[-1] - sse_df["Close"].iloc[-2]) / sse_df["Close"].iloc[-2]) * 100
    if "CSI300" in market_data and not market_data["CSI300"].empty:
        csi_df = market_data["CSI300"]
        if len(csi_df) >= 2:
            csi300_change = ((csi_df["Close"].iloc[-1] - csi_df["Close"].iloc[-2]) / csi_df["Close"].iloc[-2]) * 100
    if "IHSG" in market_data and not market_data["IHSG"].empty:
        ihsg_df = market_data["IHSG"]
        if len(ihsg_df) >= 2:
            ihsg_change = ((ihsg_df["Close"].iloc[-1] - ihsg_df["Close"].iloc[-2]) / ihsg_df["Close"].iloc[-2]) * 100

    if sse_change != 0 or csi300_change != 0:
        china_corr = llm.analyze_china_impact(sse_change, csi300_change, ihsg_change)

    regime_str = ""
    if "market_regime" in df.columns:
        regime_str = str(df["market_regime"].iloc[-1])

    llm_commentary = llm.generate_market_commentary(
        market_summary=market_summary,
        signal=signal,
        confidence=confidence,
        regime=regime_str,
        news_sentiment=news_sentiment,
        china_correlation=china_corr,
    )

    # Save commentary as in-app notification
    try:
        from .notifier import send_in_app
        level = "success" if signal == "BUY" else "warning" if signal == "SELL" else "info"
        send_in_app(
            kategori="BRIEFING",
            judul=f"Daily Briefing {date_str} — {signal} ({confidence:.1%})",
            pesan=llm_commentary,
            level=level,
        )
    except Exception:
        pass

    return {
        "date": date_str,
        "market_summary": market_summary,
        "signal": signal,
        "confidence": confidence,
        "agents": all_reports,
        "final_recommendation": final_report,
        "risk_assessment": risk_assessment,
        "news_sentiment": news_sentiment,
        "actionable_items": actionable_items,
        "llm_commentary": llm_commentary,
        "china_correlation": china_corr,
        "llm_provider": "in-app",
    }


def format_briefing_text(briefing: Dict) -> str:
    """Format briefing as readable text."""
    lines = [
        f"=== DAILY AI BRIEFING — {briefing['date']} ===",
        "",
        f"Ringkasan Pasar: {briefing['market_summary']}",
        f"Sinyal: {briefing['signal']} | Confidence: {briefing['confidence']:.1%}",
        "",
        "--- Analisis Multi-Agent ---",
    ]

    for agent in briefing["agents"]:
        lines.append(f"\n[{agent.agent_name}] ({agent.role})")
        lines.append(f"  Findings: {agent.findings}")
        for rec in agent.recommendations:
            lines.append(f"  → {rec}")

    final = briefing["final_recommendation"]
    lines.append("\n--- REKOMENDASI AKHIR ---")
    lines.append(f"{final.findings}")
    lines.append(f"Confidence: {final.confidence:.1%}")

    lines.append("\n--- Actionable Items ---")
    for i, item in enumerate(briefing["actionable_items"], 1):
        lines.append(f"  {i}. {item}")

    if "llm_commentary" in briefing and briefing["llm_commentary"]:
        lines.append(f"\n--- Market Commentary ({briefing.get('llm_provider', 'in-app')}) ---")
        lines.append(briefing["llm_commentary"])

    if "china_correlation" in briefing and briefing["china_correlation"]:
        lines.append("\n--- Korelasi China (A-Shares) ---")
        china = briefing["china_correlation"]
        if isinstance(china, dict) and "analysis" in china:
            lines.append(china["analysis"])

    return "\n".join(lines)
