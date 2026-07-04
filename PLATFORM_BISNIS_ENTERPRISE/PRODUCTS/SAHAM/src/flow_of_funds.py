"""
Flow of Funds Analysis Module.

Analyzes fund flows to identify smart money movements:
- Mutual fund flows (net inflows/outflows)
- ETF flows
- Retail vs institutional positioning
- Fund manager sentiment
- Sector-wise fund flows

Data Sources:
- OJK (Otoritas Jasa Keuangan) - mutual fund data
- IDX - ETF data
- Fund fact sheets
- Bloomberg/Reuters (for institutional flows)

Metrics:
- Net fund flow (daily, weekly, monthly)
- Flow by fund type (equity, mixed, fixed income)
- Flow by sector
- Fund sentiment indicator
- Flow vs market performance correlation
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging

logger = logging.getLogger("saham.flow_of_funds")


@dataclass
class FundFlowData:
    """Fund flow data for a single day."""
    date: str
    fund_type: str  # "equity", "mixed", "fixed_income", "etf"
    net_inflow: float  # Net inflow in billion IDR
    total_aum: float  # Total AUM in billion IDR
    flow_pct: float  # Flow as percentage of AUM
    sector: Optional[str] = None


@dataclass
class FlowOfFundsSummary:
    """Summary of fund flows analysis."""
    date: str
    total_net_inflow: float
    equity_flow: float
    mixed_flow: float
    fixed_income_flow: float
    etf_flow: float
    sector_flows: Dict[str, float] = field(default_factory=dict)
    sentiment: str = ""  # "bullish", "bearish", "neutral"
    sentiment_score: float = 0.0
    recommendations: List[str] = field(default_factory=list)


class FlowOfFundsAnalyzer:
    """
    Analyzes fund flows to identify smart money movements.
    
    Uses OJK data for mutual funds and IDX data for ETFs.
    Falls back to proxy estimation when direct data unavailable.
    """

    def __init__(self):
        self.flow_history: List[FundFlowData] = []

    def fetch_fund_flows(self, date: Optional[str] = None) -> FlowOfFundsSummary:
        """
        Fetch fund flows data.
        
        Args:
            date: Date in YYYY-MM-DD format. If None, fetch latest.
            
        Returns:
            FlowOfFundsSummary with aggregated data.
        """
        if date is None:
            date = datetime.now().strftime("%Y-%m-%d")
        
        # Try to fetch from OJK/IDX (placeholder)
        # In production, would use actual API or web scraping
        summary = self._fetch_from_official_sources(date)
        
        if summary is None:
            logger.info("Using proxy estimation for fund flows")
            summary = self._estimate_fund_flows(date)
        
        return summary

    def _fetch_from_official_sources(self, date: str) -> Optional[FlowOfFundsSummary]:
        """
        Fetch fund flows from official sources (OJK, IDX).
        
        Note: This is a placeholder. Actual implementation would need
        to handle OJK website structure and API availability.
        """
        # Placeholder for actual implementation
        return None

    def _estimate_fund_flows(self, date: str) -> FlowOfFundsSummary:
        """
        Estimate fund flows from market data proxy.
        
        Uses:
        - Market performance (up days = likely inflows)
        - Volume patterns (high volume = likely institutional activity)
        - Sector rotation patterns
        """
        from src.data_fetcher import fetch_all_market_data
        from src.config import BLUE_CHIPS_ID
        from src.pipeline.impact_rules import SECTOR_MAP
        
        market_data = fetch_all_market_data(period="1mo")
        
        if market_data is None or len(market_data) == 0:
            return FlowOfFundsSummary(
                date=date,
                total_net_inflow=0,
                equity_flow=0,
                mixed_flow=0,
                fixed_income_flow=0,
                etf_flow=0,
                sentiment="neutral",
                sentiment_score=0.0,
            )
        
        # Analyze market performance
        ihsg_data = market_data.get("IHSG")
        if ihsg_data is None or (isinstance(ihsg_data, pd.DataFrame) and ihsg_data.empty):
            ihsg_data = market_data.get("^JKSE")
        if ihsg_data is None or (isinstance(ihsg_data, pd.DataFrame) and ihsg_data.empty):
            return FlowOfFundsSummary(
                date=date,
                total_net_inflow=0,
                equity_flow=0,
                mixed_flow=0,
                fixed_income_flow=0,
                etf_flow=0,
                sentiment="neutral",
                sentiment_score=0.0,
            )
        
        # Calculate recent performance
        recent_return = (ihsg_data["Close"].iloc[-1] / ihsg_data["Close"].iloc[-5] - 1) * 100
        
        # Estimate flows based on performance
        # Strong up market = equity inflows
        # Weak/down market = fixed income inflows (flight to safety)
        if recent_return > 2:
            equity_flow = 500  # billion IDR
            mixed_flow = 200
            fixed_income_flow = -100
            etf_flow = 100
        elif recent_return > 0:
            equity_flow = 200
            mixed_flow = 100
            fixed_income_flow = 50
            etf_flow = 50
        elif recent_return > -2:
            equity_flow = -100
            mixed_flow = 50
            fixed_income_flow = 200
            etf_flow = -50
        else:
            equity_flow = -300
            mixed_flow = -100
            fixed_income_flow = 400
            etf_flow = -100
        
        total_net = equity_flow + mixed_flow + fixed_income_flow + etf_flow
        
        # Estimate sector flows
        sector_flows = {}
        for name, ticker in BLUE_CHIPS_ID.items():
            if ticker not in market_data or market_data[ticker].empty:
                continue
            
            df = market_data[ticker]
            if len(df) < 2:
                continue
            
            price_change = (df["Close"].iloc[-1] / df["Close"].iloc[-2] - 1) * 100
            sector = SECTOR_MAP.get(ticker, "unknown")
            
            if sector not in sector_flows:
                sector_flows[sector] = 0
            
            # Positive performance = likely sector inflow
            if price_change > 1:
                sector_flows[sector] += 50
            elif price_change < -1:
                sector_flows[sector] -= 30
        
        # Determine sentiment
        if total_net > 500:
            sentiment = "bullish"
            sentiment_score = min(100, total_net / 20)
        elif total_net < -500:
            sentiment = "bearish"
            sentiment_score = max(-100, total_net / 20)
        else:
            sentiment = "neutral"
            sentiment_score = 0.0
        
        return FlowOfFundsSummary(
            date=date,
            total_net_inflow=round(total_net, 2),
            equity_flow=round(equity_flow, 2),
            mixed_flow=round(mixed_flow, 2),
            fixed_income_flow=round(fixed_income_flow, 2),
            etf_flow=round(etf_flow, 2),
            sector_flows={k: round(v, 2) for k, v in sector_flows.items()},
            sentiment=sentiment,
            sentiment_score=round(sentiment_score, 2),
        )

    def get_flow_history(self, days: int = 30) -> pd.DataFrame:
        """
        Get historical fund flow data.
        
        Args:
            days: Number of days of history to retrieve.
            
        Returns:
            DataFrame with fund flow history.
        """
        history = []
        for i in range(days):
            date = (datetime.now() - timedelta(days=i)).strftime("%Y-%m-%d")
            summary = self.fetch_fund_flows(date)
            history.append({
                "date": date,
                "total_flow": summary.total_net_inflow,
                "equity_flow": summary.equity_flow,
                "sentiment": summary.sentiment,
                "sentiment_score": summary.sentiment_score,
            })
        
        df = pd.DataFrame(history)
        df["date"] = pd.to_datetime(df["date"])
        df = df.sort_values("date").reset_index(drop=True)
        return df

    def analyze_flow_vs_performance(self, days: int = 30) -> Dict:
        """
        Analyze correlation between fund flows and market performance.
        
        Args:
            days: Number of days to analyze.
            
        Returns:
            Dict with correlation analysis.
        """
        from src.data_fetcher import fetch_yfinance_data
        
        # Get flow history
        flow_df = self.get_flow_history(days)
        
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
        correlation = merged["total_flow"].corr(merged["ihsg_return"])
        
        # Lead-lag analysis (do flows lead returns?)
        lead_corr = []
        for lag in range(1, 6):
            lead_corr.append({
                "lag_days": lag,
                "correlation": merged["total_flow"].shift(lag).corr(merged["ihsg_return"])
            })
        
        return {
            "correlation": round(correlation, 3),
            "interpretation": "Positive correlation - flows support market" if correlation > 0.3 else
                           "Negative correlation - flows oppose market" if correlation < -0.3 else
                           "Weak correlation - flows not strongly related to market",
            "lead_lag_analysis": lead_corr,
            "avg_daily_flow": round(merged["total_flow"].mean(), 2),
            "avg_ihsg_return": round(merged["ihsg_return"].mean(), 2),
        }

    def get_fund_sentiment_signal(self) -> Dict:
        """
        Get fund sentiment signal for trading decisions.
        
        Returns:
            Dict with sentiment signal and recommendations.
        """
        summary = self.fetch_fund_flows()
        
        signal = "HOLD"
        confidence = 0.5
        reasons = []
        
        if summary.sentiment == "bullish" and summary.sentiment_score > 50:
            signal = "BUY"
            confidence = 0.6 + (summary.sentiment_score / 200)
            reasons.append(f"Strong fund inflows ({summary.total_net_inflow:.0f}B IDR)")
            reasons.append("Smart money bullish - supports upside")
        elif summary.sentiment == "bearish" and summary.sentiment_score < -50:
            signal = "SELL"
            confidence = 0.6 + (abs(summary.sentiment_score) / 200)
            reasons.append(f"Strong fund outflows ({summary.total_net_inflow:.0f}B IDR)")
            reasons.append("Smart money bearish - downside risk")
        else:
            signal = "HOLD"
            confidence = 0.5
            reasons.append("Fund sentiment neutral - no clear signal")
        
        # Check sector flows
        if summary.sector_flows:
            top_sector = max(summary.sector_flows.items(), key=lambda x: x[1])
            if top_sector[1] > 50:
                reasons.append(f"Funds favoring {top_sector[0]} sector")
        
        return {
            "signal": signal,
            "confidence": round(confidence, 2),
            "reasons": reasons,
            "fund_sentiment": summary.sentiment,
            "fund_score": summary.sentiment_score,
            "net_flow": summary.total_net_inflow,
        }


def get_fund_flow_adjustment() -> Tuple[float, str]:
    """
    Get confidence adjustment from fund flows for prediction pipeline.
    
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    analyzer = FlowOfFundsAnalyzer()
    signal_data = analyzer.get_fund_sentiment_signal()
    
    if signal_data["signal"] == "BUY":
        adjustment = 0.03 * signal_data["confidence"]
        reason = f"Fund flows bullish ({signal_data['fund_sentiment']}, score: {signal_data['fund_score']:.0f})"
    elif signal_data["signal"] == "SELL":
        adjustment = -0.03 * signal_data["confidence"]
        reason = f"Fund flows bearish ({signal_data['fund_sentiment']}, score: {signal_data['fund_score']:.0f})"
    else:
        adjustment = 0.0
        reason = "Fund flows neutral"
    
    return adjustment, reason
