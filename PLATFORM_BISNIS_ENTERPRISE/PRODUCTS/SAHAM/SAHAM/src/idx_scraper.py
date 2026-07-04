"""
IDX Web Scraper Module.

Scrapes data from Indonesia Stock Exchange (IDX) website:
- Foreign flow data (top buy/sell)
- Market summary
- Stock information

Note: Web scraping may break if IDX changes their website structure.
This module should be maintained regularly.
"""

import requests
from bs4 import BeautifulSoup
import pandas as pd
from datetime import datetime, timedelta
from typing import Dict, List, Optional
import logging

logger = logging.getLogger("saham.idx_scraper")


class IDXScraper:
    """Scraper for IDX website data."""
    
    BASE_URL = "https://www.idx.co.id"
    
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
        })
    
    def fetch_foreign_flow(self, date: Optional[str] = None) -> Dict:
        """
        Fetch foreign flow data from IDX.
        
        Args:
            date: Date in YYYY-MM-DD format. If None, fetch latest.
            
        Returns:
            Dict with foreign flow data.
        """
        try:
            # IDX foreign flow endpoint
            url = f"{self.BASE_URL}/data-market/stock-market/foreign-buy-sell"
            
            # Add date parameter if provided
            params = {}
            if date:
                params["date"] = date
            
            resp = self.session.get(url, params=params, timeout=30)
            resp.raise_for_status()
            
            # Parse HTML
            soup = BeautifulSoup(resp.content, "html.parser")
            
            # Try to find data in table structure
            # Note: This is a generic implementation - actual structure may vary
            tables = soup.find_all("table")
            
            if not tables:
                logger.warning("No tables found in IDX foreign flow page")
                return self._get_empty_foreign_flow()
            
            # Parse top foreign buy
            top_buy = self._parse_foreign_table(tables, "buy")
            
            # Parse top foreign sell
            top_sell = self._parse_foreign_table(tables, "sell")
            
            # Calculate totals
            total_buy = sum(item.get("value", 0) for item in top_buy)
            total_sell = sum(item.get("value", 0) for item in top_sell)
            net_flow = total_buy - total_sell
            
            # Determine sentiment
            if net_flow > 100:
                sentiment = "bullish"
                sentiment_score = min(100, net_flow / 10)
            elif net_flow < -100:
                sentiment = "bearish"
                sentiment_score = max(-100, net_flow / 10)
            else:
                sentiment = "neutral"
                sentiment_score = 0
            
            return {
                "date": date or datetime.now().strftime("%Y-%m-%d"),
                "total_net_buy": total_buy,
                "total_net_sell": total_sell,
                "total_net_flow": net_flow,
                "top_buyers": top_buy[:10],
                "top_sellers": top_sell[:10],
                "sentiment": sentiment,
                "sentiment_score": sentiment_score,
            }
            
        except Exception as e:
            logger.error(f"Failed to fetch IDX foreign flow: {e}")
            return self._get_empty_foreign_flow()
    
    def _parse_foreign_table(self, tables: List, table_type: str) -> List[Dict]:
        """Parse foreign buy/sell table."""
        results = []
        
        for table in tables:
            rows = table.find_all("tr")
            
            for row in rows[1:]:  # Skip header
                cols = row.find_all("td")
                if len(cols) >= 3:
                    try:
                        ticker = cols[0].text.strip()
                        value = self._parse_number(cols[1].text.strip())
                        volume = self._parse_number(cols[2].text.strip())
                        
                        results.append({
                            "ticker": ticker,
                            "value": value,
                            "volume": volume,
                        })
                    except (ValueError, IndexError):
                        continue
        
        return results
    
    def _parse_number(self, text: str) -> float:
        """Parse number from text (handle commas, etc)."""
        # Remove non-numeric characters except decimal point
        cleaned = text.replace(",", "").replace(".", "")
        try:
            return float(cleaned)
        except ValueError:
            return 0.0
    
    def _get_empty_foreign_flow(self) -> Dict:
        """Return empty foreign flow data."""
        return {
            "date": datetime.now().strftime("%Y-%m-%d"),
            "total_net_buy": 0,
            "total_net_sell": 0,
            "total_net_flow": 0,
            "top_buyers": [],
            "top_sellers": [],
            "sentiment": "neutral",
            "sentiment_score": 0.0,
        }
    
    def fetch_market_summary(self) -> Dict:
        """
        Fetch market summary from IDX.
        
        Returns:
            Dict with market summary data.
        """
        try:
            url = f"{self.BASE_URL}/data-market/stock-market/market-summary"
            resp = self.session.get(url, timeout=30)
            resp.raise_for_status()
            
            soup = BeautifulSoup(resp.content, "html.parser")
            
            # Parse market summary data
            # This is a placeholder - actual implementation depends on IDX structure
            return {
                "ihsg": 0,
                "change": 0,
                "change_pct": 0,
                "volume": 0,
                "value": 0,
                "advancers": 0,
                "decliners": 0,
                "unchanged": 0,
            }
            
        except Exception as e:
            logger.error(f"Failed to fetch IDX market summary: {e}")
            return self._get_empty_market_summary()
    
    def _get_empty_market_summary(self) -> Dict:
        """Return empty market summary."""
        return {
            "ihsg": 0,
            "change": 0,
            "change_pct": 0,
            "volume": 0,
            "value": 0,
            "advancers": 0,
            "decliners": 0,
            "unchanged": 0,
        }


def fetch_idx_foreign_flow(date: Optional[str] = None) -> Dict:
    """
    Convenience function to fetch IDX foreign flow.
    
    Args:
        date: Date in YYYY-MM-DD format.
        
    Returns:
        Dict with foreign flow data.
    """
    scraper = IDXScraper()
    return scraper.fetch_foreign_flow(date)
