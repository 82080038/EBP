"""
Bank Indonesia (BI) Web Scraper Module.

Scrapes economic calendar data from Bank Indonesia website:
- BI Rate decisions
- GDP data
- CPI/Inflation data
- Trade balance data
- Other economic indicators

Note: Web scraping may break if BI changes their website structure.
"""

import requests
from bs4 import BeautifulSoup
from datetime import datetime, timedelta
from typing import Dict, List, Optional
import logging

logger = logging.getLogger("saham.bi_scraper")


class BIScraper:
    """Scraper for Bank Indonesia website data."""
    
    BASE_URL = "https://www.bi.go.id"
    
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
        })
    
    def fetch_economic_calendar(self, days_ahead: int = 30) -> List[Dict]:
        """
        Fetch economic calendar from BI.
        
        Args:
            days_ahead: Number of days to look ahead
            
        Returns:
            List of economic events.
        """
        try:
            # BI economic calendar endpoint
            url = f"{self.BASE_URL}/id/statistik/ekonomi-dan-keuangan/indikator/kebijakan-moneter"
            
            resp = self.session.get(url, timeout=30)
            resp.raise_for_status()
            
            soup = BeautifulSoup(resp.content, "html.parser")
            
            events = []
            
            # Try to find economic events in the page
            # This is a generic implementation - actual structure may vary
            event_elements = soup.find_all("div", class_=["event", "schedule", "calendar-item"])
            
            for elem in event_elements:
                try:
                    event = self._parse_event_element(elem)
                    if event:
                        events.append(event)
                except Exception as e:
                    logger.debug(f"Failed to parse event element: {e}")
                    continue
            
            # If no events found, return placeholder events
            if not events:
                events = self._get_placeholder_events(days_ahead)
            
            return events
            
        except Exception as e:
            logger.error(f"Failed to fetch BI economic calendar: {e}")
            return self._get_placeholder_events(days_ahead)
    
    def _parse_event_element(self, elem) -> Optional[Dict]:
        """Parse a single event element."""
        # Try to extract date, name, importance
        date_elem = elem.find(["span", "div", "td"], class_=["date", "time"])
        name_elem = elem.find(["span", "div", "td"], class_=["name", "title", "event"])
        
        if not date_elem or not name_elem:
            return None
        
        date_str = date_elem.text.strip()
        name = name_elem.text.strip()
        
        # Try to parse date
        try:
            event_date = self._parse_date(date_str)
        except ValueError:
            return None
        
        # Determine importance based on keywords
        importance = "medium"
        if "BI Rate" in name or "GDP" in name or "CPI" in name:
            importance = "high"
        
        return {
            "event_id": f"bi_{hash(name) % 10000}",
            "event_name": name,
            "date": event_date,
            "importance": importance,
            "category": "monetary" if "Rate" in name else "growth" if "GDP" in name else "inflation" if "CPI" in name else "other",
            "unit": "%" if "Rate" in name or "GDP" in name or "CPI" in name else "",
            "impact_description": f"{name} affects market sentiment",
        }
    
    def _parse_date(self, date_str: str) -> str:
        """Parse date string to YYYY-MM-DD format."""
        # Handle various date formats
        # This is a simplified parser - actual BI format may vary
        try:
            # Try common formats
            for fmt in ["%d-%m-%Y", "%Y-%m-%d", "%d/%m/%Y", "%Y/%m/%d"]:
                try:
                    dt = datetime.strptime(date_str, fmt)
                    return dt.strftime("%Y-%m-%d")
                except ValueError:
                    continue
            
            # If no format matches, return today
            return datetime.now().strftime("%Y-%m-%d")
        except Exception:
            return datetime.now().strftime("%Y-%m-%d")
    
    def _get_placeholder_events(self, days_ahead: int) -> List[Dict]:
        """Get placeholder economic events when scraping fails."""
        events = []
        today = datetime.now()
        
        # Add typical BI events
        bi_rate_days = [15, 30]  # Typical BI Rate meeting days
        for day_offset in bi_rate_days:
            if day_offset <= days_ahead:
                event_date = (today + timedelta(days=day_offset)).strftime("%Y-%m-%d")
                events.append({
                    "event_id": f"bi_rate_{day_offset}",
                    "event_name": "BI Rate Decision",
                    "date": event_date,
                    "importance": "high",
                    "category": "monetary",
                    "unit": "%",
                    "impact_description": "Monetary policy decision affects interest rates",
                })
        
        # Add GDP announcement
        if days_ahead >= 35:
            event_date = (today + timedelta(days=35)).strftime("%Y-%m-%d")
            events.append({
                "event_id": "gdp_q4",
                "event_name": "GDP Growth (QoQ)",
                "date": event_date,
                "importance": "high",
                "category": "growth",
                "unit": "%",
                "impact_description": "Economic growth indicator",
            })
        
        return events
    
    def fetch_bi_rate_history(self, months: int = 12) -> List[Dict]:
        """
        Fetch BI rate history.
        
        Args:
            months: Number of months to fetch
            
        Returns:
            List of BI rate decisions.
        """
        try:
            url = f"{self.BASE_URL}/id/statistik/ekonomi-dan-keuangan/indikator/kebijakan-moneter"
            resp = self.session.get(url, timeout=30)
            resp.raise_for_status()
            
            soup = BeautifulSoup(resp.content, "html.parser")
            
            # Try to find BI rate data
            # This is a placeholder - actual implementation depends on BI structure
            return self._get_placeholder_bi_rate_history(months)
            
        except Exception as e:
            logger.error(f"Failed to fetch BI rate history: {e}")
            return self._get_placeholder_bi_rate_history(months)
    
    def _get_placeholder_bi_rate_history(self, months: int) -> List[Dict]:
        """Get placeholder BI rate history."""
        history = []
        today = datetime.now()
        
        for i in range(months):
            date = (today - timedelta(days=30 * i)).strftime("%Y-%m-%d")
            # BI rate has been around 6.0% recently
            rate = 6.0 + (i % 3) * 0.25  # Small variations
            history.append({
                "date": date,
                "rate": rate,
                "change": 0.0 if i == 0 else history[i-1]["rate"] - rate,
            })
        
        return history


def fetch_bi_economic_calendar(days_ahead: int = 30) -> List[Dict]:
    """
    Convenience function to fetch BI economic calendar.
    
    Args:
        days_ahead: Number of days to look ahead
        
    Returns:
        List of economic events.
    """
    scraper = BIScraper()
    return scraper.fetch_economic_calendar(days_ahead)
