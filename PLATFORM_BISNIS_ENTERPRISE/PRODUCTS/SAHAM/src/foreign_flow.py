"""
Foreign Flow Analysis Module.

Analyzes foreign investor activity in Indonesian stock market.
Foreign flow is a key driver of IHSG movements.

Data Sources:
- IDX official data (via web scraping or API)
- Top foreign buy/sell lists
- Sector-wise foreign flow
- Cumulative foreign flow

Metrics:
- Net foreign buy/sell (daily, weekly, monthly)
- Top foreign stocks (buy/sell)
- Foreign flow by sector
- Foreign sentiment indicator
- Foreign flow vs IHSG correlation
"""

import pandas as pd
import numpy as np
import requests
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging

logger = logging.getLogger("saham.foreign_flow")


@dataclass
class ForeignFlowData:
    """Daily foreign flow data for a stock."""
    ticker: str
    date: str
    net_buy: float  # Net foreign buy (in billion IDR)
    net_sell: float  # Net foreign sell (in billion IDR)
    net_flow: float  # Net flow (buy - sell)
    foreign_percentage: float  # Foreign ownership percentage


@dataclass
class ForeignFlowSummary:
    """Aggregated foreign flow summary."""
    date: str
    total_net_buy: float
    total_net_sell: float
    total_net_flow: float
    top_buyers: List[Dict] = field(default_factory=list)
    top_sellers: List[Dict] = field(default_factory=list)
    sector_flows: Dict[str, float] = field(default_factory=dict)
    sentiment: str = ""  # "bullish", "bearish", "neutral"
    sentiment_score: float = 0.0  # -100 to 100


class ForeignFlowAnalyzer:
    """
    Analyzes foreign investor activity in IDX.
    
    Uses web scraping from IDX website or API if available.
    Falls back to proxy estimation from price/volume patterns.
    """

    def __init__(self):
        self.base_url = "https://www.idx.co.id"
        self.flow_history: List[ForeignFlowData] = []

    def fetch_foreign_flow_from_idx(self, date: Optional[str] = None) -> ForeignFlowSummary:
        """
        Fetch foreign flow data from IDX website.
        
        Args:
            date: Date in YYYY-MM-DD format. If None, fetch latest.
            
        Returns:
            ForeignFlowSummary with aggregated data.
        """
        if date is None:
            date = datetime.now().strftime("%Y-%m-%d")
        
        # Try to fetch from IDX website
        try:
            summary = self._fetch_from_idx_web(date)
            if summary:
                return summary
        except Exception as e:
            logger.warning(f"Failed to fetch from IDX web: {e}")
        
        # Fallback to proxy estimation
        logger.info("Using proxy estimation for foreign flow")
        return self._estimate_foreign_flow(date)

    def _fetch_from_idx_web(self, date: str) -> Optional[ForeignFlowSummary]:
        """
        Fetch foreign flow from IDX website via web scraping.
        
        Uses the idx_scraper module to fetch real data from IDX.
        """
        try:
            from src.idx_scraper import fetch_idx_foreign_flow
            
            data = fetch_idx_foreign_flow(date)
            
            if not data or data.get("total_net_flow", 0) == 0:
                return None
            
            # Convert to ForeignFlowSummary
            return ForeignFlowSummary(
                date=data["date"],
                total_net_buy=data["total_net_buy"],
                total_net_sell=data["total_net_sell"],
                total_net_flow=data["total_net_flow"],
                top_buyers=data["top_buyers"],
                top_sellers=data["top_sellers"],
                sector_flows={},  # IDX doesn't provide sector breakdown
                sentiment=data["sentiment"],
                sentiment_score=data["sentiment_score"],
            )
            
        except Exception as e:
            logger.warning(f"IDX web scraping failed: {e}")
            return None

    def _estimate_foreign_flow(self, date: str) -> ForeignFlowSummary:
        """
        Estimate foreign flow from price/volume patterns.
        
        This is a proxy method when direct data is unavailable.
        Uses:
        - Price movement with high volume = likely foreign activity
        - Large cap stocks with unusual moves = foreign interest
        - Sector rotation patterns = foreign sector rotation
        """
        from src.data_fetcher import fetch_all_market_data
        from src.config import BLUE_CHIPS_ID
        from src.pipeline.impact_rules import SECTOR_MAP
        
        market_data = fetch_all_market_data(period="1mo")
        
        if not market_data:
            return ForeignFlowSummary(
                date=date,
                total_net_buy=0,
                total_net_sell=0,
                total_net_flow=0,
                sentiment="neutral",
                sentiment_score=0.0,
            )
        
        # Analyze each blue chip stock
        stock_flows = []
        sector_flows = {}
        
        for name, ticker in BLUE_CHIPS_ID.items():
            if ticker not in market_data or market_data[ticker].empty:
                continue
            
            df = market_data[ticker]
            if len(df) < 2:
                continue
            
            # Calculate price change and volume change
            price_change = (df["Close"].iloc[-1] / df["Close"].iloc[-2] - 1) * 100
            volume_change = (df["Volume"].iloc[-1] / df["Volume"].iloc[-2] - 1) * 100 if df["Volume"].iloc[-2] > 0 else 0
            
            # Estimate foreign flow based on pattern
            # Large price move + high volume = likely foreign activity
            if abs(price_change) > 2 and volume_change > 50:
                # Likely foreign buying
                estimated_flow = abs(price_change) * 10  # Proxy in billion IDR
                direction = "buy" if price_change > 0 else "sell"
            elif abs(price_change) > 1 and volume_change > 20:
                # Possible foreign activity
                estimated_flow = abs(price_change) * 5
                direction = "buy" if price_change > 0 else "sell"
            else:
                # Mostly retail
                estimated_flow = 0
                direction = "neutral"
            
            if estimated_flow > 0:
                flow_data = ForeignFlowData(
                    ticker=ticker,
                    date=date,
                    net_buy=estimated_flow if direction == "buy" else 0,
                    net_sell=estimated_flow if direction == "sell" else 0,
                    net_flow=estimated_flow if direction == "buy" else -estimated_flow,
                    foreign_percentage=0,  # Not available in proxy
                )
                stock_flows.append(flow_data)
                
                # Aggregate by sector
                sector = SECTOR_MAP.get(ticker, "unknown")
                if sector not in sector_flows:
                    sector_flows[sector] = 0
                sector_flows[sector] += flow_data.net_flow
        
        # Calculate summary
        total_net_buy = sum(f.net_buy for f in stock_flows)
        total_net_sell = sum(f.net_sell for f in stock_flows)
        total_net_flow = total_net_buy - total_net_sell
        
        # Top buyers and sellers
        top_buyers = sorted(
            [{"ticker": f.ticker, "net_buy": f.net_buy} for f in stock_flows if f.net_buy > 0],
            key=lambda x: x["net_buy"],
            reverse=True
        )[:10]
        
        top_sellers = sorted(
            [{"ticker": f.ticker, "net_sell": f.net_sell} for f in stock_flows if f.net_sell > 0],
            key=lambda x: x["net_sell"],
            reverse=True
        )[:10]
        
        # Determine sentiment
        if total_net_flow > 100:
            sentiment = "bullish"
            sentiment_score = min(100, total_net_flow / 10)
        elif total_net_flow < -100:
            sentiment = "bearish"
            sentiment_score = max(-100, total_net_flow / 10)
        else:
            sentiment = "neutral"
            sentiment_score = 0.0
        
        return ForeignFlowSummary(
            date=date,
            total_net_buy=round(total_net_buy, 2),
            total_net_sell=round(total_net_sell, 2),
            total_net_flow=round(total_net_flow, 2),
            top_buyers=top_buyers,
            top_sellers=top_sellers,
            sector_flows={k: round(v, 2) for k, v in sector_flows.items()},
            sentiment=sentiment,
            sentiment_score=round(sentiment_score, 2),
        )

    def get_foreign_flow_history(self, days: int = 30) -> pd.DataFrame:
        """
        Get historical foreign flow data.
        
        Args:
            days: Number of days of history to retrieve.
            
        Returns:
            DataFrame with foreign flow history.
        """
        history = []
        for i in range(days):
            date = (datetime.now() - timedelta(days=i)).strftime("%Y-%m-%d")
            summary = self.fetch_foreign_flow_from_idx(date)
            history.append({
                "date": date,
                "net_flow": summary.total_net_flow,
                "sentiment": summary.sentiment,
                "sentiment_score": summary.sentiment_score,
            })
        
        df = pd.DataFrame(history)
        df["date"] = pd.to_datetime(df["date"])
        df = df.sort_values("date").reset_index(drop=True)
        return df

    def analyze_foreign_flow_vs_ihsg(self, days: int = 30) -> Dict:
        """
        Analyze correlation between foreign flow and IHSG movements.
        
        Args:
            days: Number of days to analyze.
            
        Returns:
            Dict with correlation analysis results.
        """
        from src.data_fetcher import fetch_yfinance_data
        
        # Get foreign flow history
        flow_df = self.get_foreign_flow_history(days)
        
        # Get IHSG data
        ihsg_df = fetch_yfinance_data("^JKSE", period=f"{days}d", interval="1d")
        
        if flow_df.empty or ihsg_df.empty:
            return {"error": "Insufficient data for correlation analysis"}
        
        # Merge data
        flow_df = flow_df.set_index("date")
        ihsg_df = ihsg_df[["Close"]].copy()
        ihsg_df.index = pd.to_datetime(ihsg_df.index)
        
        merged = pd.merge(flow_df, ihsg_df, left_index=True, right_index=True, how="inner")
        
        if merged.empty:
            return {"error": "No overlapping dates"}
        
        # Calculate IHSG daily returns
        merged["ihsg_return"] = merged["Close"].pct_change() * 100
        
        # Calculate correlation
        correlation = merged["net_flow"].corr(merged["ihsg_return"])
        
        # Calculate lead-lag (does foreign flow lead IHSG?)
        lead_corr = []
        for lag in range(1, 6):
            lead_corr.append({
                "lag_days": lag,
                "correlation": merged["net_flow"].shift(lag).corr(merged["ihsg_return"])
            })
        
        return {
            "correlation": round(correlation, 3),
            "interpretation": "Positive correlation - foreign flow supports IHSG" if correlation > 0.3 else 
                           "Negative correlation - foreign flow opposes IHSG" if correlation < -0.3 else
                           "Weak correlation - foreign flow not strongly related to IHSG",
            "lead_lag_analysis": lead_corr,
            "avg_daily_flow": round(merged["net_flow"].mean(), 2),
            "avg_ihsg_return": round(merged["ihsg_return"].mean(), 2),
        }

    def get_foreign_sentiment_signal(self) -> Dict:
        """
        Get foreign sentiment signal for trading decisions.
        
        Returns:
            Dict with sentiment signal and recommendations.
        """
        summary = self.fetch_foreign_flow_from_idx()
        
        signal = "HOLD"
        confidence = 0.5
        reasons = []
        
        if summary.sentiment == "bullish" and summary.sentiment_score > 50:
            signal = "BUY"
            confidence = 0.6 + (summary.sentiment_score / 200)
            reasons.append(f"Strong foreign buying ({summary.total_net_flow:.0f}B IDR)")
            reasons.append("Foreign sentiment bullish - supports upside")
        elif summary.sentiment == "bearish" and summary.sentiment_score < -50:
            signal = "SELL"
            confidence = 0.6 + (abs(summary.sentiment_score) / 200)
            reasons.append(f"Strong foreign selling ({summary.total_net_flow:.0f}B IDR)")
            reasons.append("Foreign sentiment bearish - downside risk")
        else:
            signal = "HOLD"
            confidence = 0.5
            reasons.append("Foreign sentiment neutral - no clear signal")
        
        # Check sector flows
        if summary.sector_flows:
            top_sector = max(summary.sector_flows.items(), key=lambda x: x[1])
            if top_sector[1] > 50:
                reasons.append(f"Foreign favoring {top_sector[0]} sector")
        
        return {
            "signal": signal,
            "confidence": round(confidence, 2),
            "reasons": reasons,
            "foreign_sentiment": summary.sentiment,
            "foreign_score": summary.sentiment_score,
            "net_flow": summary.total_net_flow,
        }


def get_foreign_flow_adjustment() -> Tuple[float, str]:
    """
    Get confidence adjustment from foreign flow for prediction pipeline.
    
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    analyzer = ForeignFlowAnalyzer()
    signal_data = analyzer.get_foreign_sentiment_signal()
    
    if signal_data["signal"] == "BUY":
        adjustment = 0.05 * signal_data["confidence"]
        reason = f"Foreign flow bullish ({signal_data['foreign_sentiment']}, score: {signal_data['foreign_score']:.0f})"
    elif signal_data["signal"] == "SELL":
        adjustment = -0.05 * signal_data["confidence"]
        reason = f"Foreign flow bearish ({signal_data['foreign_sentiment']}, score: {signal_data['foreign_score']:.0f})"
    else:
        adjustment = 0.0
        reason = "Foreign flow neutral"
    
    return adjustment, reason
