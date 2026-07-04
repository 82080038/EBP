"""
Unified Daily Pipeline — gabungkan SEMUA modul analisis dalam satu run.

Pipeline:
1. Scrape berita → sentiment analysis (FinBERT)
2. Fetch economic calendar → event risk
3. Fetch fundamental data untuk saham di portfolio
4. Run technical analysis → ML prediction per ticker
5. Smart impact analysis: berita/event → map ke saham di portfolio
6. Decision: augment/reduce/hold position berdasarkan semua data
7. Eksekusi paper trading
8. Notifikasi lengkap ke Pusat Notifikasi

Smart Impact Analysis:
- "BI naikkan rate" → bearish untuk properti, bank (tergantung)
- "GDP turun" → bearish cyclicals (ASII, ICBP)
- "Rupiah melemah" → bearish import-dependent, bullish export
- "China slowdown" → bearish komoditas (ADRO)
- "Sentiment extreme fear" → reduce all positions
- "Earnings beat" → augment position
"""

from datetime import datetime
from typing import Dict, List, Optional, Tuple
from dataclasses import dataclass, field, asdict
import pandas as pd

from .config import TARGET_TICKER, BLUE_CHIPS_ID
from .notifier import send_in_app
from .paper_trading import PaperTradingEngine


# Sector mapping untuk impact analysis
# Import sector mapping from config (100 IDX tickers)
from src.config import SAHAM_IDX_SECTORS

# Convert SAHAM_IDX_SECTORS to flat ticker -> sector mapping
SECTOR_MAP = {}
for sector, tickers in SAHAM_IDX_SECTORS.items():
    for ticker in tickers:
        SECTOR_MAP[ticker] = sector.lower().replace(" ", "").replace("&", "")

# Impact rules: event keyword → sector → direction (bullish/bearish/neutral)
# Updated to match SAHAM_IDX_SECTORS sector names
IMPACT_RULES = [
    {
        "keywords": ["rate", "interest rate", "bi rate", "bank indonesia", "rate decision", "rate hike", "rate cut"],
        "sector_impact": {
            "financialservices": "bullish_if_hike",
            "realestate": "bearish_if_hike",
            "consumerstaples": "bearish_if_hike",
            "consumerdiscretionary": "bearish_if_hike",
        },
        "description": "Kebijakan suku bunga BI",
    },
    {
        "keywords": ["inflation", "cpi", "inflasi", "harga naik"],
        "sector_impact": {
            "consumerstaples": "bearish",
            "financialservices": "neutral",
            "consumerdiscretionary": "bearish",
            "energy": "bullish",
        },
        "description": "Data inflasi/CPI",
    },
    {
        "keywords": ["gdp", "pertumbuhan ekonomi", "economic growth", "resesi", "recession"],
        "sector_impact": {
            "consumerdiscretionary": "bearish",
            "consumerstaples": "bearish",
            "financialservices": "bearish",
            "energy": "bearish",
            "telecommunications": "neutral",
        },
        "description": "Data pertumbuhan ekonomi / GDP",
    },
    {
        "keywords": ["rupiah", "rupiah melemah", "rupiah menguat", "currency", "usd idr", "depreciation"],
        "sector_impact": {
            "consumerstaples": "bearish_if_weak",
            "energy": "bullish_if_weak",
            "technology": "bearish_if_weak",
            "financialservices": "neutral",
        },
        "description": "Pergerakan nilai tukar Rupiah",
    },
    {
        "keywords": ["china", "china slowdown", "china economy", "a-shares", "sse", "csi300"],
        "sector_impact": {
            "energy": "bearish",
            "materials": "bearish",
            "financialservices": "neutral",
            "consumerstaples": "neutral",
        },
        "description": "Kondisi ekonomi China",
    },
    {
        "keywords": ["trade war", "tariff", "perang dagang", "sanction", "embargo"],
        "sector_impact": {
            "energy": "bearish",
            "materials": "bearish",
            "consumerstaples": "bearish",
            "technology": "bearish",
            "financialservices": "neutral",
        },
        "description": "Tegangan perdagangan internasional",
    },
    {
        "keywords": ["earnings", "laba", "profit", "quarterly", "laporan keuangan", "earnings beat", "earnings miss"],
        "sector_impact": {},  # Per-ticker, bukan per-sector
        "description": "Hasil laporan keuangan perusahaan",
    },
    {
        "keywords": ["dividen", "dividend", "stock split", "split", "rights issue", "buyback"],
        "sector_impact": {},  # Per-ticker
        "description": "Aksi korporasi (dividen, split, buyback)",
    },
    {
        "keywords": ["regulasi", "regulation", "kebijakan", "policy", "ojk", "bapepam", "otoritas"],
        "sector_impact": {
            "financialservices": "neutral",
            "consumerstaples": "neutral",
            "telecom": "neutral",
        },
        "description": "Kebijakan regulator pasar modal",
    },
    {
        "keywords": ["fed", "fomc", "federal reserve", "rate hike us", "us interest rate"],
        "sector_impact": {
            "banking": "neutral",
            "commodity": "bearish_if_hike",  # Gold/oil turun saat Fed hike
            "consumer": "bearish_if_hike",
            "cyclical": "bearish_if_hike",
        },
        "description": "Kebijakan Federal Reserve AS",
    },
    {
        "keywords": ["oil", "minyak", "crude", "wti", "brent", "oil price"],
        "sector_impact": {
            "commodity": "bullish_if_oil_up",
            "cyclical": "bearish_if_oil_up",  # Biaya energi naik
            "consumer": "bearish_if_oil_up",
        },
        "description": "Harga minyak mentah",
    },
    {
        "keywords": ["gold", "emas", "safe haven", "gold price"],
        "sector_impact": {
            "commodity": "bullish_if_gold_up",
            "banking": "bearish_if_gold_up",  # Risk-off sentiment
        },
        "description": "Harga emas",
    },
]


@dataclass
class ImpactAssessment:
    """Hasil analisis impact berita/event ke saham tertentu."""
    ticker: str
    sector: str
    direction: str  # "bullish", "bearish", "neutral"
    confidence: float  # 0-1
    source: str  # "news", "event", "economic", "sentiment"
    reason: str
    action_recommendation: str  # "augment", "reduce", "hold", "close"
    magnitude: str = "moderate"  # "low", "moderate", "high", "extreme"
    time_horizon: str = "short"  # "immediate", "short", "medium"
    headline: str = ""  # Original headline that triggered this


@dataclass
class NewsUnderstanding:
    """Hasil pemahaman berita — bukan hanya sentiment tapi implikasi lengkap."""
    headline: str
    source: str
    event_type: str  # "rate_decision", "earnings", "policy", "commodity", "currency", "geopolitical", "corporate_action"
    entities: List[str]  # Companies/sectors mentioned
    sentiment: str  # "positive", "negative", "neutral"
    magnitude: str  # "low", "moderate", "high", "extreme"
    time_horizon: str  # "immediate", "short", "medium"
    affected_sectors: List[str]
    affected_tickers: List[str]
    direction: str  # "bullish", "bearish", "neutral"
    reasoning: str  # Full explanation of WHY this news matters
    recommended_actions: List[str]  # Specific actions per ticker
    llm_reasoning: str = ""  # Deep reasoning from LocalLLM (if available)


class NewsUnderstandingEngine:
    """
    Engine untuk "memahami" berita — bukan hanya sentiment classification.
    
    Ekstrak:
    - Event type (rate decision, earnings, policy, commodity, dll)
    - Entities mentioned (companies, sectors, countries)
    - Magnitude (berapa besar impact)
    - Time horizon (immediate vs medium term)
    - Affected sectors & tickers
    - Direction (bullish/bearish)
    - Reasoning (WHY this matters)
    - Recommended actions per ticker
    """

    # Event type detection patterns
    EVENT_PATTERNS = {
        "rate_decision": {
            "keywords": ["rate", "suku bunga", "bi rate", "interest rate", "rate decision", "rate hike", "rate cut", "fed", "fomc", "bi rate", "bank indonesia"],
            "magnitude_words": {"bps": "moderate", "50 bps": "high", "25 bps": "moderate", "100 bps": "extreme", "emergency": "extreme"},
        },
        "earnings": {
            "keywords": ["earnings", "laba", "profit", "quarterly", "laporan keuangan", "revenue", "pendapatan", "ebitda", "net profit", "laba bersih"],
            "magnitude_words": {"beat": "high", "miss": "high", "surprise": "moderate", "record": "high", "loss": "high"},
        },
        "policy": {
            "keywords": ["regulasi", "regulation", "kebijakan", "policy", "ojk", "bapepam", "otoritas", "pemerintah", "menteri", "peraturan"],
            "magnitude_words": {"ban": "extreme", "restrict": "high", "relax": "high", "new rule": "moderate", "reform": "high"},
        },
        "commodity": {
            "keywords": ["oil", "minyak", "crude", "wti", "brent", "gold", "emas", "coal", "batu bara", "nickel", "tembaga", "copper", "cpo", "sawit"],
            "magnitude_words": {"surge": "high", "crash": "extreme", "rally": "moderate", "plunge": "high", "spike": "high"},
        },
        "currency": {
            "keywords": ["rupiah", "currency", "usd idr", "dolar", "exchange rate", "depreciation", "appreciation", "melemah", "menguat"],
            "magnitude_words": {"melemah": "moderate", "menguat": "moderate", "anjlok": "extreme", "terjun": "extreme", "rally": "moderate"},
        },
        "geopolitical": {
            "keywords": ["war", "perang", "conflict", "konflik", "sanction", "embargo", "tariff", "trade war", "geopolitical", "tension"],
            "magnitude_words": {"war": "extreme", "escalation": "high", "sanction": "high", "tariff": "moderate", "tension": "low"},
        },
        "corporate_action": {
            "keywords": ["dividen", "dividend", "stock split", "split", "rights issue", "buyback", "acquisition", "akuisisi", "merger"],
            "magnitude_words": {"split": "moderate", "buyback": "high", "dividen": "moderate", "merger": "high", "akuisisi": "high"},
        },
        "macro": {
            "keywords": ["gdp", "inflasi", "inflation", "cpi", "unemployment", "pengangguran", "trade balance", "neraca perdagangan", "current account"],
            "magnitude_words": {"surge": "high", "drop": "high", "record": "high", "improvement": "moderate", "deterioration": "moderate"},
        },
    }

    # Magnitude detection from text
    MAGNITUDE_INDICATORS = {
        "extreme": ["crash", "anjlok", "terjun bebas", "plunge", "collapse", "emergency", "crisis", "darurat", "panic", "free fall"],
        "high": ["surge", "rally", "melonjak", "significant", "drastic", "drastis", "major", "besar", "substantial", "sharp", "tajam"],
        "moderate": ["naik", "turun", "rise", "fall", "increase", "decrease", "adjustment", "penyesuaian", "moderate", "sedang"],
        "low": ["slight", "tipis", "marginal", "kecil", "minor", "sedikit", "thin"],
    }

    # Time horizon indicators
    TIME_HORIZON_INDICATORS = {
        "immediate": ["hari ini", "today", "sekarang", "now", "urgent", "darurat", "emergency"],
        "short": ["minggu", "week", "besok", "tomorrow", "near term", "jangka pendek"],
        "medium": ["bulan", "month", "quarterly", "kuartal", "tahun", "year", "jangka panjang", "long term"],
    }

    def __init__(self):
        self.sentiment_analyzer = None
        try:
            from .ai_agent import FinBERTSentiment
            self.sentiment_analyzer = FinBERTSentiment()
        except Exception:
            pass

    def understand(self, article) -> NewsUnderstanding:
        """
        Understand a news article — extract full implications.
        
        Args:
            article: NewsArticle with title, summary, source
        
        Returns:
            NewsUnderstanding with event_type, magnitude, affected tickers, reasoning
        """
        text = f"{article.title}. {article.summary}"
        text_lower = text.lower()

        # 1. Detect event type
        event_type = "other"
        for etype, config in self.EVENT_PATTERNS.items():
            if any(kw in text_lower for kw in config["keywords"]):
                event_type = etype
                break

        # 2. Detect magnitude
        magnitude = "moderate"
        for mag, words in self.MAGNITUDE_INDICATORS.items():
            if any(w in text_lower for w in words):
                magnitude = mag
                break

        # Also check event-specific magnitude words
        if event_type in self.EVENT_PATTERNS:
            mag_words = self.EVENT_PATTERNS[event_type].get("magnitude_words", {})
            for word, mag in mag_words.items():
                if word in text_lower:
                    magnitude = mag
                    break

        # 3. Detect time horizon
        time_horizon = "short"
        for horizon, words in self.TIME_HORIZON_INDICATORS.items():
            if any(w in text_lower for w in words):
                time_horizon = horizon
                break

        # 4. Sentiment
        sentiment = "neutral"
        if hasattr(article, "sentiment") and article.sentiment:
            sentiment = article.sentiment.sentiment
            article.sentiment.confidence
        elif self.sentiment_analyzer:
            result = self.sentiment_analyzer.analyze(text)
            sentiment = result.sentiment
            result.confidence

        # 5. Detect affected sectors
        affected_sectors = set()
        sector_keywords = {
            "banking": ["bank", "perbankan", "kredit", "loan", "bca", "bri", "mandiri", "bni"],
            "consumer": ["konsumen", "consumer", "fmcg", "unilever", "indofood", "retail"],
            "cyclical": ["otomotif", "automotive", "astra", "mobil", "motor", "properti", "property"],
            "commodity": ["mining", "tambang", "batu bara", "coal", "nickel", "emas", "gold", "oil", "minyak", "sawit", "cpo", "adaro"],
            "telecom": ["telekomunikasi", "telkom", "telecom", "internet", "5g"],
            "tech": ["teknologi", "technology", "startup", "digital", "goto", "tokopedia"],
        }
        for sector, keywords in sector_keywords.items():
            if any(kw in text_lower for kw in keywords):
                affected_sectors.add(sector)

        # 6. Map sectors to tickers
        affected_tickers = set()
        for sector in affected_sectors:
            for ticker, t_sector in SECTOR_MAP.items():
                if t_sector == sector:
                    affected_tickers.add(ticker)

        # Also detect direct ticker mentions
        for ticker in BLUE_CHIPS_ID:
            name_part = ticker.replace(".JK", "").lower()
            if name_part in text_lower:
                affected_tickers.add(ticker)
            company_name = BLUE_CHIPS_ID[ticker].lower()
            if company_name in text_lower:
                affected_tickers.add(ticker)

        # 7. Determine direction
        direction = "neutral"
        if sentiment == "positive":
            direction = "bullish"
        elif sentiment == "negative":
            direction = "bearish"

        # Override based on event type and context
        if event_type == "rate_decision":
            if "hike" in text_lower or "naik" in text_lower:
                if "banking" in affected_sectors:
                    direction = "bullish"  # Banks benefit from rate hikes
                elif "consumer" in affected_sectors or "cyclical" in affected_sectors:
                    direction = "bearish"
            elif "cut" in text_lower or "turun" in text_lower:
                if "banking" in affected_sectors:
                    direction = "bearish"
                elif "consumer" in affected_sectors or "cyclical" in affected_sectors:
                    direction = "bullish"
        elif event_type == "commodity":
            if "surge" in text_lower or "naik" in text_lower or "rally" in text_lower:
                if "commodity" in affected_sectors:
                    direction = "bullish"
                elif "cyclical" in affected_sectors or "consumer" in affected_sectors:
                    direction = "bearish"  # Higher costs
            elif "drop" in text_lower or "turun" in text_lower or "plunge" in text_lower:
                if "commodity" in affected_sectors:
                    direction = "bearish"
                elif "cyclical" in affected_sectors or "consumer" in affected_sectors:
                    direction = "bullish"  # Lower costs

        # 8. Generate reasoning
        reasoning = self._generate_reasoning(
            headline=article.title,
            event_type=event_type,
            sentiment=sentiment,
            magnitude=magnitude,
            direction=direction,
            affected_sectors=list(affected_sectors),
            affected_tickers=list(affected_tickers),
            time_horizon=time_horizon,
        )

        # 9. Recommended actions
        recommended_actions = self._recommend_actions(
            direction=direction,
            affected_tickers=list(affected_tickers),
            magnitude=magnitude,
        )

        return NewsUnderstanding(
            headline=article.title,
            source=getattr(article, "source", ""),
            event_type=event_type,
            entities=list(affected_tickers),
            sentiment=sentiment,
            magnitude=magnitude,
            time_horizon=time_horizon,
            affected_sectors=list(affected_sectors),
            affected_tickers=list(affected_tickers),
            direction=direction,
            reasoning=reasoning,
            recommended_actions=recommended_actions,
        )

    def _generate_reasoning(self, headline, event_type, sentiment, magnitude, direction,
                            affected_sectors, affected_tickers, time_horizon) -> str:
        """Generate human-readable reasoning for why this news matters."""
        parts = []

        event_desc = {
            "rate_decision": "Kebijakan suku bunga",
            "earnings": "Hasil laporan keuangan",
            "policy": "Kebijakan regulator",
            "commodity": "Pergerakan harga komoditas",
            "currency": "Pergerakan nilai tukar",
            "geopolitical": "Event geopolitik",
            "corporate_action": "Aksi korporasi",
            "macro": "Data makro ekonomi",
            "other": "Berita pasar",
        }.get(event_type, "Berita pasar")

        parts.append(f"[{event_desc}] {headline[:80]}")

        if magnitude in ("extreme", "high"):
            parts.append(f"Magnitude: {magnitude.upper()} — impact signifikan")
        else:
            parts.append(f"Magnitude: {magnitude}")

        if affected_sectors:
            parts.append(f"Sektor terdampak: {', '.join(affected_sectors)}")
        if affected_tickers:
            parts.append(f"Saham terdampak: {', '.join(affected_tickers)}")

        parts.append(f"Direction: {direction} ({sentiment})")
        parts.append(f"Time horizon: {time_horizon}")

        if direction == "bearish":
            parts.append("→ Pertimbangkan reduce position pada saham terdampak")
        elif direction == "bullish":
            parts.append("→ Opportunity untuk augment/entry pada saham terdampak")

        return " | ".join(parts)

    def _recommend_actions(self, direction, affected_tickers, magnitude) -> List[str]:
        """Generate specific action recommendations per ticker."""
        actions = []


        for ticker in affected_tickers:
            if direction == "bearish":
                if magnitude in ("extreme", "high"):
                    actions.append(f"{ticker}: REDUCE 50% — {magnitude} bearish signal")
                else:
                    actions.append(f"{ticker}: MONITOR — mild bearish, hold for now")
            elif direction == "bullish":
                if magnitude in ("extreme", "high"):
                    actions.append(f"{ticker}: CONSIDER BUY — {magnitude} bullish signal")
                else:
                    actions.append(f"{ticker}: WATCH — mild bullish, wait for confirmation")
            else:
                actions.append(f"{ticker}: HOLD — neutral signal")

        return actions

    def understand_batch(self, articles: List) -> List[NewsUnderstanding]:
        """Understand multiple articles at once."""
        return [self.understand(a) for a in articles]


@dataclass
class PipelineResult:
    """Hasil lengkap unified pipeline run."""
    timestamp: str
    sentiment_score: float = 0.0
    fear_greed_index: float = 50.0
    event_risk_score: float = 0.0
    news_articles_count: int = 0
    economic_events_count: int = 0
    earnings_count: int = 0
    corporate_actions_count: int = 0
    analyst_recs_count: int = 0
    news_understandings: List[Dict] = field(default_factory=list)
    impact_assessments: List[Dict] = field(default_factory=list)
    portfolio_actions: List[Dict] = field(default_factory=list)
    portfolio_impact_summary: str = ""
    dcf_valuations: List[Dict] = field(default_factory=list)
    slippage_estimates: List[Dict] = field(default_factory=list)
    compliance_checks: List[Dict] = field(default_factory=list)
    idx_rules_checks: List[Dict] = field(default_factory=list)
    portfolio_risk_report: Dict = field(default_factory=dict)
    fundamental_data: Dict = field(default_factory=dict)
    data_sufficiency: Dict = field(default_factory=dict)
    pattern_signals: List[Dict] = field(default_factory=list)
    fundamental_scores: List[Dict] = field(default_factory=list)
    retrain_status: Dict = field(default_factory=dict)
    fraud_detection_report: Dict = field(default_factory=dict)
    investor_analysis: Dict = field(default_factory=dict)
    smc_analysis: Dict = field(default_factory=dict)
    afml_analysis: Dict = field(default_factory=dict)
    drl_analysis: Dict = field(default_factory=dict)
    complex_systems_analysis: Dict = field(default_factory=dict)
    # Phase 2+ fields
    transformer_analysis: Dict = field(default_factory=dict)
    regime_model_analysis: Dict = field(default_factory=dict)
    debate_analysis: Dict = field(default_factory=dict)
    multi_horizon_analysis: Dict = field(default_factory=dict)
    react_agent_analysis: Dict = field(default_factory=dict)
    social_sentiment_analysis: Dict = field(default_factory=dict)
    options_analysis: Dict = field(default_factory=dict)
    multi_mode_research: Dict = field(default_factory=dict)
    summary: str = ""
    errors: List[str] = field(default_factory=list)


class UnifiedPipeline:
    """
    Unified Daily Pipeline — gabungkan semua modul analisis.
    
    Run setiap pagi (atau manual) untuk:
    1. Baca semua berita → sentiment
    2. Baca economic calendar → event risk
    3. Baca fundamental → valuation
    4. Baca teknikal → ML prediction
    5. Impact analysis → portfolio action
    6. Eksekusi paper trading
    7. Notifikasi
    """

    def __init__(self, paper_engine: Optional[PaperTradingEngine] = None):
        self.paper = paper_engine or PaperTradingEngine()
        self.result = PipelineResult(timestamp=datetime.now().isoformat())
        self.understanding_engine = NewsUnderstandingEngine()
        from .module_integrator import ModuleIntegrator
        self.integrator = ModuleIntegrator()
        from .local_llm import LocalLLM
        self.llm = LocalLLM()

    def _analyze_portfolio_specific_impact(
        self, understandings: List[NewsUnderstanding]
    ) -> Tuple[List[ImpactAssessment], str]:
        """
        GAP 4: Portfolio-specific impact analysis.
        
        Untuk setiap berita yang dipahami, cek apakah saham di portfolio terdampak.
        Hasilkan reasoning spesifik: "Berita X → saham Y di portfolio Anda akan tertekan karena Z"
        """
        assessments = []
        open_positions = self.paper.get_open_positions()
        held_tickers = {p.ticker: p for p in open_positions}
        portfolio_lines = []

        for understanding in understandings:
            for ticker in understanding.affected_tickers:
                is_held = ticker in held_tickers
                sector = SECTOR_MAP.get(ticker, "")

                # Skip neutral
                if understanding.direction == "neutral":
                    continue

                # Magnitude → confidence
                mag_conf = {"low": 0.3, "moderate": 0.5, "high": 0.75, "extreme": 0.9}.get(
                    understanding.magnitude, 0.5
                )

                # Action recommendation
                if understanding.direction == "bearish":
                    if is_held:
                        pos = held_tickers[ticker]
                        if understanding.magnitude in ("extreme", "high"):
                            action = "reduce"
                            portfolio_lines.append(
                                f"🔴 {ticker} ({sector}, {pos.quantity} shares @ {pos.entry_price:,.0f}): "
                                f"{understanding.headline[:60]} → REDUCE ({understanding.magnitude} bearish)"
                            )
                        else:
                            action = "hold"
                            portfolio_lines.append(
                                f"🟡 {ticker} ({sector}): {understanding.headline[:60]} → "
                                f"MONITOR (mild bearish, hold)"
                            )
                    else:
                        action = "avoid"
                elif understanding.direction == "bullish":
                    if is_held:
                        action = "hold"
                        portfolio_lines.append(
                            f"🟢 {ticker} ({sector}, held): {understanding.headline[:60]} → "
                            f"HOLD (bullish, maintain position)"
                        )
                    else:
                        if understanding.magnitude in ("extreme", "high"):
                            action = "consider_buy"
                            portfolio_lines.append(
                                f"🟢 {ticker} ({sector}, not held): {understanding.headline[:60]} → "
                                f"CONSIDER BUY ({understanding.magnitude} bullish)"
                            )
                        else:
                            action = "watch"
                else:
                    action = "hold"

                assessments.append(ImpactAssessment(
                    ticker=ticker,
                    sector=sector,
                    direction=understanding.direction,
                    confidence=mag_conf,
                    source="news_understanding",
                    reason=understanding.reasoning,
                    action_recommendation=action,
                    magnitude=understanding.magnitude,
                    time_horizon=understanding.time_horizon,
                    headline=understanding.headline,
                ))

        # Build portfolio impact summary
        if portfolio_lines:
            summary = f"Portfolio Impact Analysis ({len(portfolio_lines)} items):\n"
            summary += "\n".join(f"  {line}" for line in portfolio_lines[:20])
        else:
            summary = "No direct portfolio impact detected from current news."

        return assessments, summary

    def _analyze_news_impact(self, articles: List, sentiment_score: float) -> List[ImpactAssessment]:
        """Analisis impact berita ke saham di portfolio."""
        assessments = []
        open_positions = self.paper.get_open_positions()
        held_tickers = {p.ticker for p in open_positions}

        # Also check all blue chips for new opportunities
        all_tickers = held_tickers | set(BLUE_CHIPS_ID.keys())

        for article in articles:
            text = f"{article.title}. {article.summary}".lower()

            for rule in IMPACT_RULES:
                # Check if any keyword matches
                matched = any(kw in text for kw in rule["keywords"])
                if not matched:
                    continue

                # Determine direction from sentiment
                if hasattr(article, "sentiment") and article.sentiment:
                    sentiment = article.sentiment.sentiment
                    conf = article.sentiment.confidence
                else:
                    sentiment = "neutral"
                    conf = 0.5

                # Map to tickers by sector
                for ticker in all_tickers:
                    sector = SECTOR_MAP.get(ticker, "")
                    if not sector:
                        continue

                    sector_impact = rule["sector_impact"].get(sector)
                    if not sector_impact:
                        continue

                    # Determine direction
                    direction = "neutral"
                    if "bullish_if" in sector_impact:
                        sector_impact.replace("bullish_if_", "").replace("bearish_if_", "")
                        if sentiment == "positive":
                            direction = "bullish" if "bullish" in sector_impact else "bearish"
                        elif sentiment == "negative":
                            direction = "bearish" if "bullish" in sector_impact else "bullish"
                    elif sector_impact == "bullish":
                        direction = "bullish" if sentiment != "negative" else "neutral"
                    elif sector_impact == "bearish":
                        direction = "bearish" if sentiment != "positive" else "neutral"

                    if direction == "neutral":
                        continue

                    # Action recommendation
                    is_held = ticker in held_tickers
                    if direction == "bearish" and is_held:
                        action = "reduce"
                    elif direction == "bullish" and not is_held:
                        action = "consider_buy"
                    elif direction == "bullish" and is_held:
                        action = "hold"
                    elif direction == "bearish" and not is_held:
                        action = "avoid"
                    else:
                        action = "hold"

                    assessments.append(ImpactAssessment(
                        ticker=ticker,
                        sector=sector,
                        direction=direction,
                        confidence=conf,
                        source="news",
                        reason=f"{rule['description']} → {sector}: {direction} (sentiment: {sentiment})",
                        action_recommendation=action,
                    ))

        # Global sentiment impact on all positions
        if sentiment_score < -30:
            for pos in open_positions:
                assessments.append(ImpactAssessment(
                    ticker=pos.ticker,
                    sector=SECTOR_MAP.get(pos.ticker, ""),
                    direction="bearish",
                    confidence=min(0.9, abs(sentiment_score) / 100),
                    source="sentiment",
                    reason=f"Extreme negative sentiment (score: {sentiment_score:.0f}) → reduce all positions",
                    action_recommendation="reduce",
                ))
        elif sentiment_score > 30:
            for ticker in BLUE_CHIPS_ID:
                if ticker not in held_tickers:
                    assessments.append(ImpactAssessment(
                        ticker=ticker,
                        sector=SECTOR_MAP.get(ticker, ""),
                        direction="bullish",
                        confidence=min(0.8, sentiment_score / 100),
                        source="sentiment",
                        reason=f"Strong positive sentiment (score: {sentiment_score:.0f}) → consider new positions",
                        action_recommendation="consider_buy",
                    ))

        return assessments

    def _analyze_event_impact(self, economic_events: List, event_risk_score: float) -> List[ImpactAssessment]:
        """Analisis impact economic events ke portfolio."""
        assessments = []
        open_positions = self.paper.get_open_positions()

        for event in economic_events:
            if not hasattr(event, "event"):
                continue
            event_text = (event.event or "").lower()

            for rule in IMPACT_RULES:
                matched = any(kw in event_text for kw in rule["keywords"])
                if not matched:
                    continue

                importance = getattr(event, "importance", "low")
                base_conf = 0.7 if importance == "high" else 0.5 if importance == "medium" else 0.3

                for ticker in {p.ticker for p in open_positions}:
                    sector = SECTOR_MAP.get(ticker, "")
                    if not sector:
                        continue

                    sector_impact = rule["sector_impact"].get(sector)
                    if not sector_impact:
                        continue

                    # Check actual vs forecast
                    getattr(event, "actual", None)
                    getattr(event, "forecast", None)

                    direction = "neutral"
                    if "bearish" in sector_impact:
                        direction = "bearish"
                    elif "bullish" in sector_impact:
                        direction = "bullish"

                    if direction == "neutral":
                        continue

                    action = "reduce" if direction == "bearish" else "hold"

                    assessments.append(ImpactAssessment(
                        ticker=ticker,
                        sector=sector,
                        direction=direction,
                        confidence=base_conf,
                        source="economic",
                        reason=f"Economic event: {event.event} ({importance}) → {sector}: {direction}",
                        action_recommendation=action,
                    ))

        # High event risk → reduce all
        if event_risk_score > 50:
            for pos in open_positions:
                assessments.append(ImpactAssessment(
                    ticker=pos.ticker,
                    sector=SECTOR_MAP.get(pos.ticker, ""),
                    direction="bearish",
                    confidence=min(0.85, event_risk_score / 100),
                    source="event_risk",
                    reason=f"High event risk ({event_risk_score:.0f}/100) → reduce all positions",
                    action_recommendation="reduce",
                ))

        return assessments

    def _execute_impact_actions(self, assessments: List[ImpactAssessment], current_prices: Dict[str, float]) -> List[Dict]:
        """Eksekusi paper trading berdasarkan impact assessments."""
        actions = []

        # Aggregate: per ticker, ambil assessment dengan confidence tertinggi
        ticker_assessments: Dict[str, List[ImpactAssessment]] = {}
        for a in assessments:
            ticker_assessments.setdefault(a.ticker, []).append(a)

        for ticker, assess_list in ticker_assessments.items():
            # Sort by confidence
            assess_list.sort(key=lambda x: x.confidence, reverse=True)
            best = assess_list[0]

            price = current_prices.get(ticker, 0)
            if price <= 0:
                continue

            pos = self.paper.get_position(ticker)

            if best.action_recommendation == "reduce" and pos:
                # Reduce 50% of position
                reduce_qty = max(1, pos.quantity // 2)
                result = self.paper.sell(ticker, reduce_qty, price, reason=f"IMPACT: {best.reason}")
                actions.append({
                    "ticker": ticker,
                    "action": "REDUCE",
                    "quantity": reduce_qty,
                    "reason": best.reason,
                    "result": result,
                })

            elif best.action_recommendation == "close" and pos:
                result = self.paper.sell(ticker, pos.quantity, price, reason=f"IMPACT CLOSE: {best.reason}")
                actions.append({
                    "ticker": ticker,
                    "action": "CLOSE",
                    "quantity": pos.quantity,
                    "reason": best.reason,
                    "result": result,
                })

            elif best.action_recommendation == "consider_buy" and not pos:
                # Only if we have cash and sentiment is positive
                if self.paper.cash > price * 100:  # Min 100 shares worth
                    investable = self.paper.cash * 0.15  # Max 15% per impact-driven buy
                    qty = int(investable / price)
                    if qty > 0:
                        sl = price * 0.93  # 7% stop loss
                        tp = price * 1.07  # 7% take profit
                        result = self.paper.buy(
                            ticker=ticker,
                            quantity=qty,
                            price=price,
                            stop_loss=sl,
                            take_profit=tp,
                            confidence=best.confidence,
                            signal="IMPACT",
                            reason=f"IMPACT BUY: {best.reason}",
                        )
                        actions.append({
                            "ticker": ticker,
                            "action": "BUY",
                            "quantity": qty,
                            "reason": best.reason,
                            "result": result,
                        })

        return actions

    def run(
        self,
        market_data: Dict = None,
        prediction_results: Dict[str, Dict] = None,
        max_positions: int = 5,
    ) -> PipelineResult:
        """
        Run unified pipeline — baca semua data → analisis → eksekusi.
        
        Args:
            market_data: Optional pre-fetched market data
            prediction_results: Optional pre-computed predictions
            max_positions: Max simultaneous positions
        """
        print(f"\n{'='*60}")
        print(f"UNIFIED DAILY PIPELINE — {datetime.now().strftime('%Y-%m-%d %H:%M')}")
        print(f"{'='*60}")

        # Market hours status
        try:
            from .market_hours import MarketHours
            mh = MarketHours()
            all_status = mh.get_all_status()
            open_list = [code for code, s in all_status.items() if s.is_open]
            closed_list = [f"{code}({s.session.value})" for code, s in all_status.items() if not s.is_open]
            print("\n📊 Market Status:")
            if open_list:
                print(f"  🟢 Open: {', '.join(open_list)}")
            if closed_list:
                print(f"  🔴 Closed: {', '.join(closed_list[:4])}{'...' if len(closed_list) > 4 else ''}")
            next_event = mh.get_next_market_event()
            if next_event:
                print(f"  ⏰ Next event: {next_event[0]} {next_event[1]} in {next_event[2]}")
        except Exception:
            pass

        result = self.result

        # === STEP 1: NEWS & SENTIMENT ===
        print("\n[1/7] Scraping berita & sentiment analysis...")
        articles = []
        try:
            from .ai_agent import NewsScraper, FinBERTSentiment
            scraper = NewsScraper()
            articles = scraper.fetch_all_sources(max_per_source=8)
            if articles:
                finbert = FinBERTSentiment()
                scraper.analyze_sentiments(finbert)
                summary = scraper.get_sentiment_summary()
                result.sentiment_score = summary.get("sentiment_score", 0)
                result.news_articles_count = len(articles)

                # Fear & Greed
                result.fear_greed_index = (result.sentiment_score + 100) / 2
                result.fear_greed_index = max(0, min(100, result.fear_greed_index))

                print(f"  Articles: {len(articles)} | Sentiment: {result.sentiment_score:.1f} | F&G: {result.fear_greed_index:.1f}")
            else:
                result.errors.append("No news articles fetched")
        except Exception as e:
            result.errors.append(f"News/sentiment: {e}")
            print(f"  [ERROR] {e}")

        # === STEP 1b: NEWS UNDERSTANDING (GAP 3) ===
        print("\n[2/7] Understanding news — extracting implications...")
        understandings = []
        try:
            if articles:
                understandings = self.understanding_engine.understand_batch(articles)
                result.news_understandings = [asdict(u) for u in understandings]
                print(f"  Understood {len(understandings)} articles:")
                for u in understandings[:5]:
                    print(f"    [{u.event_type}] {u.headline[:50]} → {u.direction} ({u.magnitude}) → {u.affected_tickers}")
        except Exception as e:
            result.errors.append(f"News understanding: {e}")
            print(f"  [ERROR] {e}")

        # === STEP 1c: LLM DEEP REASONING (P9) ===
        if understandings and self.llm.is_available():
            print("\n[2b/9] LLM deep reasoning for top news...")
            try:
                for u in understandings[:3]:
                    if u.magnitude in ("high", "extreme"):
                        reasoning = self.llm.reason_about_news(
                            headline=u.headline,
                            summary=u.reasoning,
                            context=f"Sectors: {u.affected_sectors}, Tickers: {u.affected_tickers}",
                        )
                        u.llm_reasoning = reasoning
                        print(f"    LLM: {u.headline[:40]} → {reasoning[:80]}...")
                # Update result with LLM reasoning
                result.news_understandings = [asdict(u) for u in understandings]
            except Exception as e:
                print(f"  [LLM] {e}")

        # === STEP 2: ECONOMIC CALENDAR & EVENTS ===
        print("\n[3/7] Fetching economic calendar & corporate events...")
        economic_events = []
        earnings = []
        corporate_actions = []
        analyst_recs = []
        try:
            from .event_driven import (
                fetch_economic_calendar, fetch_earnings_calendar,
                fetch_corporate_actions, fetch_analyst_recommendations,
                run_event_analysis,
            )

            economic_events = fetch_economic_calendar("indonesia")
            result.economic_events_count = len(economic_events)

            earnings_tickers = {name: ticker for ticker, name in BLUE_CHIPS_ID.items()}
            earnings = fetch_earnings_calendar(earnings_tickers, days_ahead=30)
            corporate_actions = fetch_corporate_actions(earnings_tickers, days_ahead=30)
            analyst_recs = fetch_analyst_recommendations(earnings_tickers)

            result.earnings_count = len(earnings)
            result.corporate_actions_count = len(corporate_actions)
            result.analyst_recs_count = len(analyst_recs)

            # Event risk score from run_event_analysis if market_data available
            if market_data:
                try:
                    event_analysis = run_event_analysis(
                        market_data, target_ticker=TARGET_TICKER,
                        include_economic=True, include_news=True,
                    )
                    result.event_risk_score = event_analysis.event_risk_score
                except Exception:
                    pass

            print(f"  Economic: {len(economic_events)} | Earnings: {len(earnings)} | Actions: {len(corporate_actions)} | Analysts: {len(analyst_recs)}")
        except Exception as e:
            result.errors.append(f"Events: {e}")
            print(f"  [ERROR] {e}")

        # === STEP 3: IMPACT ANALYSIS ===
        print("\n[4/7] Smart impact analysis: berita/event → portfolio...")
        all_assessments = []

        # Portfolio-specific impact from news understandings (GAP 4)
        if understandings:
            portfolio_impacts, portfolio_summary = self._analyze_portfolio_specific_impact(understandings)
            all_assessments.extend(portfolio_impacts)
            result.portfolio_impact_summary = portfolio_summary
            print(f"  Portfolio-specific impacts: {len(portfolio_impacts)}")
            for line in portfolio_summary.split("\n")[:5]:
                if line.strip():
                    print(f"    {line.strip()}")

        # Legacy news impact (sector-based rules)
        if articles:
            news_impacts = self._analyze_news_impact(articles, result.sentiment_score)
            all_assessments.extend(news_impacts)
            print(f"  Sector-rule impacts: {len(news_impacts)}")

        # Event impact
        if economic_events:
            event_impacts = self._analyze_event_impact(economic_events, result.event_risk_score)
            all_assessments.extend(event_impacts)
            print(f"  Event impacts: {len(event_impacts)}")

        # Deduplicate: per ticker, keep highest confidence
        seen = {}
        for a in all_assessments:
            key = (a.ticker, a.direction)
            if key not in seen or a.confidence > seen[key].confidence:
                seen[key] = a
        unique_assessments = list(seen.values())
        result.impact_assessments = [asdict(a) for a in unique_assessments]

        print(f"  Total unique impacts: {len(unique_assessments)}")
        for a in unique_assessments[:10]:
            print(f"    {a.ticker}: {a.direction} ({a.confidence:.0%}) → {a.action_recommendation} | {a.reason[:60]}")

        # === STEP 4: ML PREDICTIONS (if provided) ===
        print("\n[5/7] ML predictions...")
        if prediction_results:
            print(f"  Predictions provided: {len(prediction_results)} tickers")
        else:
            print("  No predictions provided — skipping ML step")

        # === STEP 5: EXECUTE IMPACT ACTIONS ===
        print("\n[6/7] Eksekusi paper trading berdasarkan impact...")
        current_prices = {}
        if prediction_results:
            for ticker, pred in prediction_results.items():
                current_prices[ticker] = pred.get("current_price", 0)
        elif market_data:
            from .data_fetcher import get_current_price
            for ticker in BLUE_CHIPS_ID:
                try:
                    current_prices[ticker] = get_current_price(ticker)
                except Exception:
                    pass

        portfolio_actions = self._execute_impact_actions(unique_assessments, current_prices)
        result.portfolio_actions = portfolio_actions
        print(f"  Actions executed: {len(portfolio_actions)}")
        for a in portfolio_actions:
            status = a.get("result", {}).get("status", "")
            print(f"    {a['action']} {a['ticker']}: {status}")

        # === STEP 5b: INTEGRATED MODULE ANALYSIS (P3-P8) ===
        print("\n[6b/9] Integrated module analysis (DCF, slippage, compliance, IDX rules, portfolio risk)...")
        try:
            # DCF Valuation for held + candidate tickers
            tickers_to_analyze = set(current_prices.keys()) | {p.ticker for p in self.paper.get_open_positions()}
            for ticker in list(tickers_to_analyze)[:5]:  # Limit to 5 for speed
                price = current_prices.get(ticker, 0)
                if price <= 0:
                    continue

                # DCF
                dcf = self.integrator.run_dcf(ticker)
                if dcf and "error" not in dcf:
                    result.dcf_valuations.append(dcf)
                    print(f"    DCF {ticker}: intrinsic={dcf.get('intrinsic_value', 0):,.0f} vs price={price:,.0f} → {dcf.get('valuation_status', '')}")

                # Slippage estimate for potential trades
                pos = self.paper.get_position(ticker)
                if not pos and self.paper.cash > price * 100:
                    est_shares = int((self.paper.cash * 0.15) / price)
                    if est_shares > 0:
                        slip = self.integrator.estimate_slippage(ticker, est_shares, price, "buy")
                        if slip and "error" not in slip:
                            result.slippage_estimates.append(slip)

                # Compliance check
                comp = self.integrator.check_compliance(ticker, "BUY", 0.7)
                if comp and "error" not in comp:
                    result.compliance_checks.append(comp)

                # IDX rules check
                idx = self.integrator.check_idx_rules(ticker, price)
                if idx:
                    result.idx_rules_checks.append(idx)
                    ar = idx.get("auto_rejection", {})
                    if ar.get("is_rejected"):
                        print(f"    ⚠️ IDX auto-rejection: {ticker} → {ar.get('description', '')}")

            # Portfolio risk assessment
            open_positions = self.paper.get_open_positions()
            if open_positions:
                positions = {}
                total_value = sum(p.quantity * current_prices.get(p.ticker, p.entry_price) for p in open_positions)
                for p in open_positions:
                    val = p.quantity * current_prices.get(p.ticker, p.entry_price)
                    positions[p.ticker] = {
                        "weight": val / total_value if total_value > 0 else 0,
                        "shares": p.quantity,
                        "price": current_prices.get(p.ticker, p.entry_price),
                    }
                risk = self.integrator.assess_portfolio_risk(positions)
                if risk and "error" not in risk:
                    result.portfolio_risk_report = risk
                    print(f"    Portfolio VaR(95%): {risk.get('var_95', 0):,.0f} | CVaR: {risk.get('cvar_95', 0):,.0f}")
                    print(f"    Stress worst case: {risk.get('stress_test', {}).get('worst_case', 0):,.0f}")

            print(f"  DCF: {len(result.dcf_valuations)} | Slippage: {len(result.slippage_estimates)} | Compliance: {len(result.compliance_checks)} | IDX: {len(result.idx_rules_checks)}")
        except Exception as e:
            result.errors.append(f"Integrated analysis: {e}")
            print(f"  [ERROR] {e}")

        # === STEP 5c: FUNDAMENTAL DATA ===
        print("\n[6c/9] Fundamental data fetch...")
        try:
            from .data_fetcher import fetch_fundamental_data
            from .config import ALL_BLUE_CHIPS
            tickers_to_check = set(current_prices.keys()) | {p.ticker for p in self.paper.get_open_positions()}
            tickers_to_check = tickers_to_check | set(list(ALL_BLUE_CHIPS.keys())[:8])
            for ticker in list(tickers_to_check)[:10]:
                fund = fetch_fundamental_data(ticker, use_cache=True)
                if fund and "error" not in fund:
                    result.fundamental_data[ticker] = fund
                    pe = fund.get("pe_ratio")
                    roe = fund.get("roe")
                    if pe:
                        print(f"    {ticker}: PE={pe:.1f}, ROE={roe:.1%}" if roe else f"    {ticker}: PE={pe:.1f}")
            print(f"  Fundamental: {len(result.fundamental_data)} tickers")
        except Exception as e:
            result.errors.append(f"Fundamental data: {e}")
            print(f"  [ERROR] {e}")

        # === STEP 5d: DATA SUFFICIENCY CHECK ===
        print("\n[6d/9] Data sufficiency check...")
        try:
            from .data_fetcher import check_data_sufficiency
            for ticker in list(tickers_to_check)[:8]:
                report = check_data_sufficiency(ticker, use_case="model_training")
                if "error" not in report:
                    result.data_sufficiency[ticker] = report
                    status = "✓" if report["sufficient"] else "✗"
                    print(f"    {status} {ticker}: {report['current_rows']}/{report['min_required']} rows — {report['gap'][:50]}")
        except Exception as e:
            result.errors.append(f"Data sufficiency: {e}")
            print(f"  [ERROR] {e}")

        # === STEP 5e: PATTERN RECOGNITION ===
        print("\n[6e/9] Pattern recognition (candlestick, chart, volume)...")
        try:
            if market_data:
                from .data_fetcher import load_harga_harian
                for ticker in list(tickers_to_check)[:5]:
                    df = load_harga_harian(ticker)
                    if not df.empty and len(df) > 20:
                        patterns = self.integrator.detect_patterns(df.tail(100))
                        if patterns and "error" not in patterns:
                            candles = patterns.get("candlestick_patterns", [])
                            charts = patterns.get("chart_patterns", [])
                            vol_anom = patterns.get("volume_anomalies", [])
                            ms = patterns.get("market_structure")
                            ms_trend = ms.structure if ms and hasattr(ms, "structure") else "unknown"
                            if candles or charts or vol_anom:
                                result.pattern_signals.append({
                                    "ticker": ticker,
                                    "candlestick": [c.name for c in candles[-3:]],
                                    "chart_patterns": [c.pattern_type for c in charts[-2:]],
                                    "volume_anomalies": len(vol_anom),
                                    "market_structure": ms_trend,
                                })
                                print(f"    {ticker}: {len(candles)} candle, {len(charts)} chart, {len(vol_anom)} vol anomaly, trend={ms_trend}")
            print(f"  Pattern signals: {len(result.pattern_signals)} tickers")
        except Exception as e:
            result.errors.append(f"Pattern recognition: {e}")
            print(f"  [ERROR] {e}")

        # === STEP 5f: FUNDAMENTAL SCORING ===
        print("\n[6f/9] Fundamental scoring (Graham/Lynch style)...")
        try:
            for ticker in list(tickers_to_check)[:5]:
                score = self.integrator.run_fundamental_analysis(ticker)
                if score and "error" not in score:
                    result.fundamental_scores.append(score)
                    print(f"    {ticker}: score={score.get('total_score', 0)}/100, grade={score.get('grade', '?')}, rating={score.get('rating', '?')}")
            print(f"  Fundamental scores: {len(result.fundamental_scores)} tickers")
        except Exception as e:
            result.errors.append(f"Fundamental scoring: {e}")
            print(f"  [ERROR] {e}")

        # === STEP 5g: RETRAIN CHECK ===
        print("\n[6g/9] Model retrain check (drift detection)...")
        try:
            retrain = self.integrator.check_retrain_needed()
            if retrain and "error" not in retrain:
                result.retrain_status = retrain
                needed = retrain.get("retrain_needed", False)
                reason = retrain.get("reason", "")
                if needed:
                    print(f"    ⚠️ Retrain needed: {reason}")
                else:
                    print(f"    ✓ No retrain needed ({reason})")
            else:
                print("    No retrain data available")
        except Exception as e:
            result.errors.append(f"Retrain check: {e}")
            print(f"  [ERROR] {e}")

        # === STEP 6.5: FRAUD DETECTION ===
        if market_data:
            print("\n[6.5/7] Fraud detection & data integrity check...")
            try:
                fraud_report = self.integrator.run_fraud_detection(
                    market_data=market_data,
                    news_sentiment=result.sentiment_score,
                    news_count=result.news_articles_count,
                    index_ticker="^JKSE",
                )
                if fraud_report and "error" not in fraud_report:
                    result.fraud_detection_report = fraud_report
                    if not fraud_report["passed"]:
                        print(f"  ⚠️ FRAUD ALERT: {fraud_report['summary']}")
                        for alert in fraud_report.get("alerts", []):
                            if alert["severity"] == "critical":
                                result.errors.append(f"Fraud: {alert['ticker']} — {alert['message']}")
                    else:
                        print(f"  ✅ {fraud_report['summary']}")
                else:
                    print(f"  [SKIP] Fraud detection error: {fraud_report.get('error', 'unknown') if fraud_report else 'None'}")
            except Exception as e:
                result.errors.append(f"Fraud detection: {e}")
                print(f"  [ERROR] {e}")

        # === STEP 6.6: INVESTOR ANALYSIS ===
        if market_data:
            print("\n[6.6/7] Investor analysis (asset allocation + correlation)...")
            try:
                investor_result = self.integrator.run_investor_analysis(
                    market_data=market_data,
                    capital=self.paper.initial_capital if self.paper else 100_000_000,
                    risk_profile="moderate",
                )
                if investor_result and "error" not in investor_result:
                    result.investor_analysis = investor_result
                    alloc = investor_result.get("asset_allocation", {})
                    corr = investor_result.get("correlation", {})
                    print(f"  Allocation: {alloc.get('stocks_pct', 0)}% stocks / {alloc.get('bonds_pct', 0)}% bonds / {alloc.get('cash_pct', 0)}% cash")
                    print(f"  Correlation: avg={corr.get('avg_correlation', 0):.2f}, div_ratio={corr.get('diversification_ratio', 0):.2f}")
                else:
                    print(f"  [SKIP] Investor analysis error: {investor_result.get('error', 'unknown') if investor_result else 'None'}")
            except Exception as e:
                result.errors.append(f"Investor analysis: {e}")
                print(f"  [ERROR] {e}")

        # === STEP 6.7: SMC / ICT ANALYSIS ===
        if market_data:
            print("\n[6.7/9] Smart Money Concepts (SMC/ICT) analysis...")
            try:
                target_df = None
                for name, data in market_data.items():
                    if data is not None and hasattr(data, 'empty') and not data.empty and len(data) >= 60:
                        target_df = data
                        break
                if target_df is not None:
                    smc_result = self.integrator.run_smc_analysis(target_df)
                    if smc_result and "error" not in smc_result:
                        result.smc_analysis = smc_result
                        print(f"  SMC: {smc_result.get('signal', 'HOLD')} (conf: {smc_result.get('confidence', 50):.0f}) | "
                              f"Structure: {smc_result.get('market_structure', 'unclear')} | "
                              f"Zone: {smc_result.get('premium_discount', 'equilibrium')}")
                    else:
                        print(f"  [SKIP] SMC error: {smc_result.get('error', 'unknown') if smc_result else 'None'}")
            except Exception as e:
                result.errors.append(f"SMC analysis: {e}")
                print(f"  [ERROR] {e}")

        # === STEP 6.8: AFML (LOPEZ DE PRADO) ANALYSIS ===
        if market_data:
            print("\n[6.8/9] AFML (Lopez de Prado) pipeline...")
            try:
                target_df = None
                for name, data in market_data.items():
                    if data is not None and hasattr(data, 'empty') and not data.empty and len(data) >= 60:
                        target_df = data
                        break
                if target_df is not None:
                    afml_result = self.integrator.run_afml_pipeline(target_df)
                    if afml_result and "error" not in afml_result:
                        result.afml_analysis = afml_result
                        print(f"  AFML: optimal_d={afml_result.get('optimal_d', 0):.1f} | "
                              f"stationary={afml_result.get('is_stationary')} | "
                              f"labels={afml_result.get('label_distribution', {})}")
                    else:
                        print(f"  [SKIP] AFML error: {afml_result.get('error', 'unknown') if afml_result else 'None'}")
            except Exception as e:
                result.errors.append(f"AFML analysis: {e}")
                print(f"  [ERROR] {e}")

        # === STEP 6.9: DRL TRADING PREDICTION ===
        if market_data:
            print("\n[6.9/9] DRL trading agent prediction...")
            try:
                target_df = None
                for name, data in market_data.items():
                    if data is not None and hasattr(data, 'empty') and not data.empty and len(data) >= 60:
                        target_df = data
                        break
                if target_df is not None:
                    drl_result = self.integrator.predict_drl(target_df)
                    if drl_result and "error" not in drl_result:
                        result.drl_analysis = drl_result
                        print(f"  DRL: {drl_result.get('action_name', 'HOLD')} "
                              f"(conf: {drl_result.get('confidence', 0.5):.0%}) | "
                              f"position_rec: {drl_result.get('position_recommendation', 0):.2f}")
                    else:
                        print(f"  [SKIP] DRL error: {drl_result.get('error', 'unknown') if drl_result else 'None'}")
            except Exception as e:
                result.errors.append(f"DRL analysis: {e}")
                print(f"  [ERROR] {e}")

        # === STEP 6.10: COMPLEX SYSTEMS ANALYSIS ===
        if market_data:
            print("\n[6.10/9] Complex systems network analysis...")
            try:
                returns_df = pd.DataFrame()
                for name, data in market_data.items():
                    if data is not None and hasattr(data, 'empty') and not data.empty and "Close" in data.columns:
                        returns_df[name] = data["Close"].pct_change()
                if not returns_df.empty and returns_df.shape[1] >= 2:
                    returns_df = returns_df.dropna()
                    if len(returns_df) > 20:
                        cs_result = self.integrator.run_complex_systems(returns_df)
                        if cs_result and "error" not in cs_result:
                            result.complex_systems_analysis = cs_result
                            print(f"  Network: density={cs_result.get('network_density', 0):.2f} | "
                                  f"systemic_nodes={cs_result.get('systemic_nodes', [])} | "
                                  f"contagion_risk={cs_result.get('contagion_risk', 0):.2%}")
                        else:
                            print(f"  [SKIP] Complex systems error: {cs_result.get('error', 'unknown') if cs_result else 'None'}")
            except Exception as e:
                result.errors.append(f"Complex systems: {e}")
                print(f"  [ERROR] {e}")

        # === STEP 7: ML-DRIVEN TRADING (if predictions available) ===
        if prediction_results:
            print("\n[7/7] ML-driven multi-ticker trading...")
            try:
                from .trading_agent import TradingAgent, SafetyGuardrails
                agent = TradingAgent(
                    paper_engine=self.paper,
                    guardrails=SafetyGuardrails(),
                    auto_execute=True,
                )
                ml_decisions = agent.run_multi_ticker_cycle(
                    market_data=market_data or {},
                    prediction_results=prediction_results,
                    max_positions=max_positions,
                )
                for d in ml_decisions:
                    if d["action"] == "OPEN_POSITION":
                        result.portfolio_actions.append({
                            "ticker": d["ticker"],
                            "action": "ML_BUY",
                            "reason": f"ML signal BUY (conf: {d.get('confidence', 0):.1%})",
                            "result": d.get("order", {}),
                        })
                print(f"  ML decisions: {len(ml_decisions)}")
            except Exception as e:
                result.errors.append(f"ML trading: {e}")
                print(f"  [ERROR] {e}")
        else:
            print("\n[7/7] ML trading skipped (no predictions)")

        # === STEP 7.5: PHASE 2+ ADVANCED ANALYTICS ===
        print("\n[Extra] Phase 2+ advanced analytics...")
        try:
            # Transformer ensemble on target ticker
            if market_data:
                for name, data in market_data.items():
                    if data is not None and not data.empty and len(data) >= 80:
                        tf_result = self.integrator.run_transformer_ensemble(data)
                        if tf_result and "error" not in tf_result:
                            result.transformer_analysis[name] = tf_result
                            print(f"  Transformer[{name}]: ensemble={tf_result.get('ensemble_prediction', 0):.4f}, "
                                  f"models={tf_result.get('n_models_trained', 0)}")
                        break  # Only run on first valid ticker

            # Regime-aware model
            if market_data:
                for name, data in market_data.items():
                    if data is not None and not data.empty and len(data) >= 120:
                        reg_result = self.integrator.run_regime_aware_model(data)
                        if reg_result and "error" not in reg_result:
                            result.regime_model_analysis = reg_result
                            print(f"  Regime: {reg_result.get('current_regime', '?')} | "
                                  f"signal={reg_result.get('signal', 'HOLD')} | "
                                  f"conf={reg_result.get('confidence', 0):.1%}")
                        break

            # Bull vs Bear debate
            if market_data:
                for name, data in market_data.items():
                    if data is not None and not data.empty and len(data) >= 50:
                        debate = self.integrator.run_bull_bear_debate(data)
                        if debate and "error" not in debate:
                            result.debate_analysis = debate
                            print(f"  Debate: {debate.get('verdict', 'HOLD')} "
                                  f"(bull={debate.get('bull_score', 0):.1f} vs bear={debate.get('bear_score', 0):.1f})")
                        break

            # Multi-horizon prediction
            if market_data:
                for name, data in market_data.items():
                    if data is not None and not data.empty and len(data) >= 80:
                        mh = self.integrator.run_multi_horizon_prediction(data, name)
                        if mh and "error" not in mh:
                            result.multi_horizon_analysis = mh
                            print(f"  Multi-Horizon: {mh.get('consensus_signal', 'HOLD')} "
                                  f"(agreement={mh.get('horizon_agreement', 0):.0%})")
                        break

            # ReAct agent
            if market_data:
                for name, data in market_data.items():
                    if data is not None and not data.empty and len(data) >= 50:
                        react = self.integrator.run_react_agent(data, name)
                        if react and "error" not in react:
                            result.react_agent_analysis = react
                            print(f"  ReAct: {react.get('recommendation', 'HOLD')} "
                                  f"(steps={react.get('n_steps', 0)}, conf={react.get('confidence', 0):.0%})")
                        break

            # Social sentiment (mock for now)
            ss = self.integrator.run_social_sentiment("BBCA.JK", use_mock=True)
            if ss and "error" not in ss:
                result.social_sentiment_analysis = ss
                print(f"  Social: {ss.get('sentiment_label', 'neutral')} "
                      f"(score={ss.get('overall_sentiment', 0):.2f}, posts={ss.get('n_posts', 0)})")

            # Options analysis on first available price
            if market_data:
                for name, data in market_data.items():
                    if data is not None and not data.empty and "Close" in data.columns:
                        price = float(data["Close"].iloc[-1])
                        opt = self.integrator.run_options_analysis(price)
                        if opt and "error" not in opt:
                            result.options_analysis = opt
                            print(f"  Options: call={opt.get('pricing', {}).get('call_price', 0):.2f}, "
                                  f"put={opt.get('pricing', {}).get('put_price', 0):.2f}")
                        break

            # Multi-mode research (fast mode for pipeline)
            if market_data:
                for name, data in market_data.items():
                    if data is not None and not data.empty and len(data) >= 50:
                        research = self.integrator.run_multi_mode_research(data, name, mode="fast")
                        if research and "error" not in research:
                            result.multi_mode_research = research
                            print(f"  Research[{research.get('mode', '?')}]: "
                                  f"{research.get('recommendation', 'HOLD')} "
                                  f"({research.get('confidence', 0):.0%})")
                        break

        except Exception as e:
            result.errors.append(f"Phase 2+ analytics: {e}")
            print(f"  [ERROR] Phase 2+: {e}")

        # === SUMMARY ===
        buys = sum(1 for a in result.portfolio_actions if "BUY" in a["action"])
        sells = sum(1 for a in result.portfolio_actions if "REDUCE" in a["action"] or "CLOSE" in a["action"])
        result.summary = (
            f"Pipeline {datetime.now().strftime('%Y-%m-%d %H:%M')} | "
            f"News: {result.news_articles_count} | Sentiment: {result.sentiment_score:.0f} | "
            f"F&G: {result.fear_greed_index:.0f} | Event Risk: {result.event_risk_score:.0f} | "
            f"Impacts: {len(unique_assessments)} | Actions: {len(result.portfolio_actions)} ({buys} buy, {sells} sell)"
        )

        # === NOTIFICATION ===
        try:
            level = "success" if result.sentiment_score > 10 else "warning" if result.sentiment_score < -10 else "info"
            notif_msg = (
                f"{result.summary}\n\n"
                f"--- NEWS UNDERSTANDING ---\n"
                + "\n".join(f"• [{u['event_type']}] {u['headline'][:60]} → {u['direction']} ({u['magnitude']}) → {u['affected_tickers'][:3]}"
                  for u in result.news_understandings[:8])
                if result.news_understandings else "No news analyzed.\n"
            )
            notif_msg += f"\n\n--- PORTFOLIO IMPACT ---\n{result.portfolio_impact_summary}\n"
            notif_msg += (
                "\n--- PORTFOLIO ACTIONS ---\n"
                + "\n".join(f"• {a['action']} {a['ticker']}: {a.get('reason', '')[:80]}" for a in result.portfolio_actions[:10])
                if result.portfolio_actions else "\nNo actions taken."
            )
            send_in_app(
                kategori="PIPELINE",
                judul=f"📊 Daily Pipeline: {result.news_articles_count} news, sentiment {result.sentiment_score:.0f}, {len(result.portfolio_actions)} actions",
                pesan=notif_msg,
                level=level,
            )
        except Exception:
            pass

        print(f"\n{'='*60}")
        print("PIPELINE COMPLETE")
        print(f"  {result.summary}")
        print(f"{'='*60}")

        return result
