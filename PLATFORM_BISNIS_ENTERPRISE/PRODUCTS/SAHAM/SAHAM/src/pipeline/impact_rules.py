"""
Impact analysis rules for news/events → sector mapping.
"""

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
        "description": "Perang dagang / sanksi",
    },
]


def analyze_news_impact(news_text: str, ticker: str) -> dict:
    """Analyze news impact on a ticker based on sector mapping."""
    from typing import Dict, Any
    
    sector = SECTOR_MAP.get(ticker, "unknown")
    news_lower = news_text.lower()
    
    impacts = []
    for rule in IMPACT_RULES:
        for keyword in rule["keywords"]:
            if keyword in news_lower:
                sector_impact = rule["sector_impact"].get(sector, "neutral")
                impacts.append({
                    "keyword": keyword,
                    "sector": sector,
                    "impact": sector_impact,
                    "description": rule["description"],
                })
    
    return {
        "ticker": ticker,
        "sector": sector,
        "impacts": impacts,
        "overall": _determine_overall_impact(impacts),
    }


def _determine_overall_impact(impacts: list) -> str:
    """Determine overall impact from multiple impact rules."""
    if not impacts:
        return "neutral"
    
    bullish_count = sum(1 for i in impacts if "bullish" in i["impact"])
    bearish_count = sum(1 for i in impacts if "bearish" in i["impact"])
    
    if bullish_count > bearish_count:
        return "bullish"
    elif bearish_count > bullish_count:
        return "bearish"
    else:
        return "neutral"
