"""
Indonesia Economic Calendar Module.

Tracks and analyzes Indonesia-specific economic events:
- BI Rate decisions (Bank Indonesia)
- GDP growth releases
- Trade balance data
- Inflation (CPI) data
- Foreign reserves
- Fiscal policy announcements

Data Sources:
- Bank Indonesia official website
- Statistics Indonesia (BPS)
- Ministry of Finance
- Trading Economics API (for historical data)

Impact Analysis:
- Surprise analysis (actual vs consensus)
- Time lag analysis (market reaction timing)
- Sector impact mapping
"""

import pandas as pd
import numpy as np
import requests
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging

logger = logging.getLogger("saham.indo_economic_calendar")


@dataclass
class EconomicEvent:
    """Indonesia economic event."""
    event_id: str
    event_name: str
    date: str
    actual: Optional[float] = None
    previous: Optional[float] = None
    consensus: Optional[float] = None
    forecast: Optional[float] = None
    unit: str = ""
    importance: str = "medium"  # "high", "medium", "low"
    category: str = ""  # "monetary", "fiscal", "trade", "inflation", "growth"
    surprise_pct: Optional[float] = None
    impact_description: str = ""


@dataclass
class EconomicCalendarSummary:
    """Summary of upcoming and recent economic events."""
    upcoming_events: List[EconomicEvent] = field(default_factory=list)
    recent_events: List[EconomicEvent] = field(default_factory=list)
    high_impact_count: int = 0
    event_risk_score: float = 0.0  # 0-100
    recommendations: List[str] = field(default_factory=list)


class IndonesiaEconomicCalendar:
    """
    Manages Indonesia economic calendar and impact analysis.
    
    Sources:
    - Bank Indonesia (BI Rate decisions)
    - BPS (GDP, CPI, Trade Balance)
    - Trading Economics API (historical data)
    """

    def __init__(self):
        self.base_url = "https://tradingeconomics.com"
        self.bi_url = "https://www.bi.go.id"
        self.bps_url = "https://www.bps.go.id"
        self.events: List[EconomicEvent] = []

    def fetch_upcoming_events(self, days_ahead: int = 30) -> List[EconomicEvent]:
        """
        Fetch upcoming economic events for Indonesia.
        
        Args:
            days_ahead: Number of days ahead to fetch.
            
        Returns:
            List of upcoming EconomicEvent objects.
        """
        # Try to fetch from BI scraper first
        try:
            from src.bi_scraper import fetch_bi_economic_calendar
            raw_events = fetch_bi_economic_calendar(days_ahead)
            
            if raw_events:
                events = []
                for raw in raw_events:
                    events.append(EconomicEvent(
                        event_id=raw["event_id"],
                        event_name=raw["event_name"],
                        date=raw["date"],
                        importance=raw["importance"],
                        category=raw["category"],
                        unit=raw["unit"],
                        impact_description=raw["impact_description"],
                    ))
                return events
        except Exception as e:
            logger.warning(f"BI scraper failed: {e}")
        
        # Fallback to generated events
        today = datetime.now()
        upcoming = []
        
        # Generate typical schedule for key events
        # In production, this would fetch from actual calendar API
        
        # BI Rate (typically monthly, around 3rd week)
        for month in range(3):
            event_date = today + timedelta(days=month * 30 + 15)
            if event_date <= today + timedelta(days=days_ahead):
                upcoming.append(EconomicEvent(
                    event_id=f"bi_rate_{event_date.strftime('%Y%m')}",
                    event_name="BI Rate Decision",
                    date=event_date.strftime("%Y-%m-%d"),
                    importance="high",
                    category="monetary",
                    unit="%",
                    impact_description="Monetary policy decision affects interest rates, banking sector, and overall market liquidity",
                ))
        
        # GDP (quarterly, around 5th week after quarter end)
        for quarter in range(2):
            event_date = today + timedelta(days=quarter * 90 + 35)
            if event_date <= today + timedelta(days=days_ahead):
                upcoming.append(EconomicEvent(
                    event_id=f"gdp_{event_date.strftime('%Y%m')}",
                    event_name="GDP Growth (QoQ)",
                    date=event_date.strftime("%Y-%m-%d"),
                    importance="high",
                    category="growth",
                    unit="%",
                    impact_description="Economic growth indicator affects cyclical sectors and overall market sentiment",
                ))
        
        # CPI (monthly, around 1st week)
        for month in range(3):
            event_date = today + timedelta(days=month * 30 + 5)
            if event_date <= today + timedelta(days=days_ahead):
                upcoming.append(EconomicEvent(
                    event_id=f"cpi_{event_date.strftime('%Y%m')}",
                    event_name="CPI Inflation (MoM)",
                    date=event_date.strftime("%Y-%m-%d"),
                    importance="high",
                    category="inflation",
                    unit="%",
                    impact_description="Inflation data affects BI Rate expectations and consumer sector",
                ))
        
        # Trade Balance (monthly, around 15th)
        for month in range(3):
            event_date = today + timedelta(days=month * 30 + 15)
            if event_date <= today + timedelta(days=days_ahead):
                upcoming.append(EconomicEvent(
                    event_id=f"trade_balance_{event_date.strftime('%Y%m')}",
                    event_name="Trade Balance",
                    date=event_date.strftime("%Y-%m-%d"),
                    importance="medium",
                    category="trade",
                    unit="USD Billion",
                    impact_description="Trade balance affects Rupiah and export/import sectors",
                ))
        
        # Foreign Reserves (weekly)
        for week in range(4):
            event_date = today + timedelta(days=week * 7)
            if event_date <= today + timedelta(days=days_ahead):
                upcoming.append(EconomicEvent(
                    event_id=f"reserves_{event_date.strftime('%Y%m%d')}",
                    event_name="Foreign Reserves",
                    date=event_date.strftime("%Y-%m-%d"),
                    importance="medium",
                    category="monetary",
                    unit="USD Billion",
                    impact_description="Foreign reserves indicate Rupiah stability and central bank intervention capacity",
                ))
        
        return sorted(upcoming, key=lambda x: x.date)

    def fetch_historical_data(self, indicator: str, start_date: str, end_date: str) -> pd.DataFrame:
        """
        Fetch historical economic data from Trading Economics.
        
        Args:
            indicator: Economic indicator code (e.g., "INDONESIACPIALLMINMEI")
            start_date: Start date in YYYY-MM-DD format
            end_date: End date in YYYY-MM-DD format
            
        Returns:
            DataFrame with historical data.
        """
        # Placeholder for Trading Economics API integration
        # In production, would use actual API calls
        return pd.DataFrame()

    def analyze_event_impact(self, event: EconomicEvent) -> Dict:
        """
        Analyze potential market impact of an economic event.
        
        Args:
            event: EconomicEvent to analyze.
            
        Returns:
            Dict with impact analysis.
        """
        impact = {
            "event": event.event_name,
            "date": event.date,
            "importance": event.importance,
            "category": event.category,
            "affected_sectors": [],
            "expected_market_reaction": "",
            "trading_strategy": "",
        }
        
        # Sector impact mapping
        sector_impact = {
            "monetary": {
                "favored": ["banking", "consumer", "infrastructure"],
                "avoided": ["property", "cyclical"],
                "reaction": "Rate hike = banking bullish, property bearish. Rate cut = opposite.",
            },
            "fiscal": {
                "favored": ["infrastructure", "construction"],
                "avoided": ["consumer"],
                "reaction": "Fiscal stimulus = infrastructure bullish. Fiscal tightening = opposite.",
            },
            "trade": {
                "favored": ["export", "commodity"],
                "avoided": ["import", "consumer"],
                "reaction": "Trade surplus = export bullish. Trade deficit = opposite.",
            },
            "inflation": {
                "favored": ["commodity", "energy"],
                "avoided": ["consumer", "banking"],
                "reaction": "High inflation = commodity bullish, consumer bearish.",
            },
            "growth": {
                "favored": ["cyclical", "consumer"],
                "avoided": ["defensive"],
                "reaction": "Strong growth = cyclical bullish. Weak growth = defensive bullish.",
            },
        }
        
        if event.category in sector_impact:
            impact["affected_sectors"] = sector_impact[event.category]["favored"] + sector_impact[event.category]["avoided"]
            impact["expected_market_reaction"] = sector_impact[event.category]["reaction"]
        
        # Trading strategy based on importance
        if event.importance == "high":
            impact["trading_strategy"] = "Reduce position size 1-2 days before event. Wait for actual data before re-entering."
        elif event.importance == "medium":
            impact["trading_strategy"] = "Monitor event but maintain normal position sizing. Adjust if surprise > 10%."
        else:
            impact["trading_strategy"] = "Normal trading. Event unlikely to cause significant volatility."
        
        return impact

    def calculate_surprise(self, event: EconomicEvent) -> Optional[float]:
        """
        Calculate surprise percentage (actual vs consensus).
        
        Args:
            event: EconomicEvent with actual and consensus values.
            
        Returns:
            Surprise percentage or None if data unavailable.
        """
        if event.actual is None or event.consensus is None or event.consensus == 0:
            return None
        
        surprise = ((event.actual - event.consensus) / abs(event.consensus)) * 100
        return round(surprise, 2)

    def get_calendar_summary(self, days_ahead: int = 30) -> EconomicCalendarSummary:
        """
        Get summary of economic calendar.
        
        Args:
            days_ahead: Number of days ahead to include.
            
        Returns:
            EconomicCalendarSummary with analysis.
        """
        upcoming = self.fetch_upcoming_events(days_ahead)
        
        # Count high impact events
        high_impact = sum(1 for e in upcoming if e.importance == "high")
        
        # Calculate event risk score
        risk_score = 0
        for event in upcoming:
            if event.importance == "high":
                risk_score += 25
            elif event.importance == "medium":
                risk_score += 10
            else:
                risk_score += 5
        
        risk_score = min(100, risk_score)
        
        # Generate recommendations
        recommendations = []
        if high_impact >= 3:
            recommendations.append("Multiple high-impact events upcoming - consider reducing exposure")
        elif high_impact >= 1:
            recommendations.append("High-impact event(s) upcoming - monitor closely")
        
        if risk_score > 50:
            recommendations.append("High event risk - use smaller position sizes")
        
        # Analyze individual events
        for event in upcoming:
            if event.importance == "high":
                impact = self.analyze_event_impact(event)
                recommendations.append(f"{event.event_name} on {event.date}: {impact['trading_strategy']}")
        
        return EconomicCalendarSummary(
            upcoming_events=upcoming,
            recent_events=[],
            high_impact_count=high_impact,
            event_risk_score=round(risk_score, 2),
            recommendations=recommendations,
        )

    def get_economic_sentiment(self) -> Dict:
        """
        Get overall economic sentiment based on recent data.
        
        Returns:
            Dict with sentiment analysis.
        """
        # Placeholder for sentiment analysis
        # In production, would analyze recent economic data releases
        
        return {
            "sentiment": "neutral",
            "score": 0.0,
            "factors": [],
            "description": "Economic sentiment analysis requires historical data integration",
        }


def get_economic_event_adjustment() -> Tuple[float, str]:
    """
    Get confidence adjustment from economic events for prediction pipeline.
    
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    calendar = IndonesiaEconomicCalendar()
    summary = calendar.get_calendar_summary(days_ahead=7)
    
    if summary.event_risk_score > 50:
        adjustment = -0.02  # Reduce confidence due to high event risk
        reason = f"High economic event risk ({summary.event_risk_score:.0f}/100) - reduce position size"
    elif summary.high_impact_count >= 1:
        adjustment = -0.01
        reason = f"High-impact economic event(s) upcoming ({summary.high_impact_count}) - monitor closely"
    else:
        adjustment = 0.0
        reason = "No significant economic events upcoming"
    
    return adjustment, reason
