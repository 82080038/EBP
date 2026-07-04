"""
Market Analytics Integration Module.

Integrates all new market analysis modules into a unified pipeline:
- Foreign Flow Analysis
- Market Structure Analysis
- Indonesia Economic Calendar
- Dynamic Intermarket Correlation
- Flow of Funds Analysis
- Advanced Technical Analysis
- Stress Testing & Scenario Analysis
- Real-time News Integration
- Social Media Real-time Integration
- Alternative Data Sources

This module provides a unified interface for all market analytics
and aggregates signals from multiple sources.
"""

import pandas as pd
import numpy as np
from datetime import datetime
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging

logger = logging.getLogger("saham.market_analytics_integration")


@dataclass
class MarketAnalyticsSignal:
    """Unified market analytics signal."""
    signal: str  # "BUY", "SELL", "HOLD"
    confidence: float
    source_signals: Dict[str, Dict] = field(default_factory=dict)
    weighted_confidence: float = 0.0
    consensus: str = ""  # "bullish", "bearish", "mixed"
    key_factors: List[str] = field(default_factory=list)
    risk_assessment: str = ""
    recommendations: List[str] = field(default_factory=dict)


class MarketAnalyticsIntegrator:
    """
    Integrates all market analytics modules.
    
    Aggregates signals from multiple sources and provides
    a unified market analysis.
    """

    def __init__(self):
        self.modules = {
            "foreign_flow": None,
            "market_structure": None,
            "economic_calendar": None,
            "dynamic_correlation": None,
            "flow_of_funds": None,
            "advanced_technical": None,
            "stress_testing": None,
            "realtime_news": None,
            "social_media": None,
            "alternative_data": None,
        }

    def get_all_signals(self, market_data: Dict[str, pd.DataFrame], ticker: str = "") -> Dict[str, Dict]:
        """
        Get signals from all market analytics modules.
        
        Args:
            market_data: Dict of ticker -> DataFrame with OHLCV data
            ticker: Optional ticker for specific analysis
            
        Returns:
            Dict of module_name -> signal_data
        """
        signals = {}
        
        # Foreign Flow Analysis
        try:
            from src.foreign_flow import get_foreign_flow_adjustment
            adj, reason = get_foreign_flow_adjustment()
            signals["foreign_flow"] = {
                "adjustment": adj,
                "reason": reason,
                "signal": "BUY" if adj > 0 else "SELL" if adj < 0 else "HOLD",
            }
        except Exception as e:
            logger.warning(f"Foreign flow analysis failed: {e}")
            signals["foreign_flow"] = {"signal": "HOLD", "adjustment": 0, "reason": "Analysis failed"}
        
        # Market Structure Analysis
        try:
            from src.market_structure import get_market_structure_adjustment
            adj, reason = get_market_structure_adjustment(market_data)
            signals["market_structure"] = {
                "adjustment": adj,
                "reason": reason,
                "signal": "BUY" if adj > 0 else "SELL" if adj < 0 else "HOLD",
            }
        except Exception as e:
            logger.warning(f"Market structure analysis failed: {e}")
            signals["market_structure"] = {"signal": "HOLD", "adjustment": 0, "reason": "Analysis failed"}
        
        # Economic Calendar
        try:
            from src.indo_economic_calendar import get_economic_event_adjustment
            adj, reason = get_economic_event_adjustment()
            signals["economic_calendar"] = {
                "adjustment": adj,
                "reason": reason,
                "signal": "BUY" if adj > 0 else "SELL" if adj < 0 else "HOLD",
            }
        except Exception as e:
            logger.warning(f"Economic calendar analysis failed: {e}")
            signals["economic_calendar"] = {"signal": "HOLD", "adjustment": 0, "reason": "Analysis failed"}
        
        # Dynamic Correlation
        try:
            from src.dynamic_correlation import get_correlation_adjustment
            adj, reason = get_correlation_adjustment(market_data)
            signals["dynamic_correlation"] = {
                "adjustment": adj,
                "reason": reason,
                "signal": "BUY" if adj > 0 else "SELL" if adj < 0 else "HOLD",
            }
        except Exception as e:
            logger.warning(f"Dynamic correlation analysis failed: {e}")
            signals["dynamic_correlation"] = {"signal": "HOLD", "adjustment": 0, "reason": "Analysis failed"}
        
        # Flow of Funds
        try:
            from src.flow_of_funds import get_fund_flow_adjustment
            adj, reason = get_fund_flow_adjustment()
            signals["flow_of_funds"] = {
                "adjustment": adj,
                "reason": reason,
                "signal": "BUY" if adj > 0 else "SELL" if adj < 0 else "HOLD",
            }
        except Exception as e:
            logger.warning(f"Flow of funds analysis failed: {e}")
            signals["flow_of_funds"] = {"signal": "HOLD", "adjustment": 0, "reason": "Analysis failed"}
        
        # Advanced Technical Analysis
        try:
            if ticker and ticker in market_data:
                from src.advanced_technical import get_advanced_technical_adjustment
                adj, reason = get_advanced_technical_adjustment(market_data[ticker])
                signals["advanced_technical"] = {
                    "adjustment": adj,
                    "reason": reason,
                    "signal": "BUY" if adj > 0 else "SELL" if adj < 0 else "HOLD",
                }
            else:
                signals["advanced_technical"] = {"signal": "HOLD", "adjustment": 0, "reason": "No ticker data"}
        except Exception as e:
            logger.warning(f"Advanced technical analysis failed: {e}")
            signals["advanced_technical"] = {"signal": "HOLD", "adjustment": 0, "reason": "Analysis failed"}
        
        # Real-time News
        try:
            from src.realtime_news import get_realtime_news_adjustment
            adj, reason = get_realtime_news_adjustment()
            signals["realtime_news"] = {
                "adjustment": adj,
                "reason": reason,
                "signal": "BUY" if adj > 0 else "SELL" if adj < 0 else "HOLD",
            }
        except Exception as e:
            logger.warning(f"Real-time news analysis failed: {e}")
            signals["realtime_news"] = {"signal": "HOLD", "adjustment": 0, "reason": "Analysis failed"}
        
        # Social Media
        try:
            from src.social_media_realtime import get_social_media_adjustment
            adj, reason = get_social_media_adjustment(ticker)
            signals["social_media"] = {
                "adjustment": adj,
                "reason": reason,
                "signal": "BUY" if adj > 0 else "SELL" if adj < 0 else "HOLD",
            }
        except Exception as e:
            logger.warning(f"Social media analysis failed: {e}")
            signals["social_media"] = {"signal": "HOLD", "adjustment": 0, "reason": "Analysis failed"}
        
        # Alternative Data
        try:
            from src.alternative_data import get_alternative_data_adjustment
            adj, reason = get_alternative_data_adjustment()
            signals["alternative_data"] = {
                "adjustment": adj,
                "reason": reason,
                "signal": "BUY" if adj > 0 else "SELL" if adj < 0 else "HOLD",
            }
        except Exception as e:
            logger.warning(f"Alternative data analysis failed: {e}")
            signals["alternative_data"] = {"signal": "HOLD", "adjustment": 0, "reason": "Analysis failed"}
        
        return signals

    def aggregate_signals(self, signals: Dict[str, Dict]) -> MarketAnalyticsSignal:
        """
        Aggregate signals from all modules.
        
        Args:
            signals: Dict of module_name -> signal_data
            
        Returns:
            MarketAnalyticsSignal with aggregated analysis.
        """
        # Count bullish/bearish signals
        buy_count = sum(1 for s in signals.values() if s["signal"] == "BUY")
        sell_count = sum(1 for s in signals.values() if s["signal"] == "SELL")
        hold_count = sum(1 for s in signals.values() if s["signal"] == "HOLD")
        
        total = len(signals)
        
        # Calculate weighted adjustment
        total_adjustment = sum(s["adjustment"] for s in signals.values())
        weighted_confidence = 0.5 + total_adjustment
        weighted_confidence = max(0, min(1, weighted_confidence))
        
        # Determine overall signal
        if buy_count > sell_count + 2:
            signal = "BUY"
            consensus = "bullish"
        elif sell_count > buy_count + 2:
            signal = "SELL"
            consensus = "bearish"
        elif buy_count > sell_count:
            signal = "BUY"
            consensus = "mildly_bullish"
        elif sell_count > buy_count:
            signal = "SELL"
            consensus = "mildly_bearish"
        else:
            signal = "HOLD"
            consensus = "mixed"
        
        # Extract key factors
        key_factors = []
        for module, data in signals.items():
            if data["signal"] in ["BUY", "SELL"]:
                key_factors.append(f"{module}: {data['reason']}")
        
        # Risk assessment
        if consensus == "bearish" and weighted_confidence > 0.7:
            risk_assessment = "High - multiple bearish signals"
        elif consensus == "bullish" and weighted_confidence > 0.7:
            risk_assessment = "Low - multiple bullish signals"
        else:
            risk_assessment = "Moderate - mixed signals"
        
        # Generate recommendations
        recommendations = []
        if signal == "BUY":
            recommendations.append("Multiple analytics sources support upside")
            if weighted_confidence > 0.7:
                recommendations.append("Strong conviction - consider increasing position size")
            else:
                recommendations.append("Moderate conviction - normal position size")
        elif signal == "SELL":
            recommendations.append("Multiple analytics sources suggest downside")
            if weighted_confidence > 0.7:
                recommendations.append("Strong conviction - consider reducing exposure")
            else:
                recommendations.append("Moderate conviction - wait for confirmation")
        else:
            recommendations.append("Mixed signals - wait for clearer direction")
        
        return MarketAnalyticsSignal(
            signal=signal,
            confidence=round(weighted_confidence, 2),
            source_signals=signals,
            weighted_confidence=round(weighted_confidence, 2),
            consensus=consensus,
            key_factors=key_factors[:5],  # Top 5 factors
            risk_assessment=risk_assessment,
            recommendations=recommendations,
        )

    def run_full_analysis(
        self,
        market_data: Dict[str, pd.DataFrame],
        ticker: str = ""
    ) -> MarketAnalyticsSignal:
        """
        Run full market analytics analysis.
        
        Args:
            market_data: Dict of ticker -> DataFrame with OHLCV data
            ticker: Optional ticker for specific analysis
            
        Returns:
            MarketAnalyticsSignal with comprehensive analysis.
        """
        logger.info("Running full market analytics analysis...")
        
        # Get signals from all modules
        signals = self.get_all_signals(market_data, ticker)
        
        # Aggregate signals
        aggregated = self.aggregate_signals(signals)
        
        logger.info(f"Market analytics complete: {aggregated.signal} (confidence: {aggregated.confidence})")
        
        return aggregated


def get_market_analytics_adjustment(
    market_data: Dict[str, pd.DataFrame],
    ticker: str = ""
) -> Tuple[float, str]:
    """
    Get confidence adjustment from market analytics for prediction pipeline.
    
    Args:
        market_data: Dict of ticker -> DataFrame with OHLCV data
        ticker: Optional ticker for specific analysis
        
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    integrator = MarketAnalyticsIntegrator()
    signal = integrator.run_full_analysis(market_data, ticker)
    
    adjustment = (signal.confidence - 0.5) * 0.2  # Scale to ±0.1
    
    if signal.signal == "BUY":
        reason = f"Market analytics bullish (consensus: {signal.consensus}, confidence: {signal.confidence:.2f})"
    elif signal.signal == "SELL":
        reason = f"Market analytics bearish (consensus: {signal.consensus}, confidence: {signal.confidence:.2f})"
    else:
        reason = f"Market analytics neutral (consensus: {signal.consensus})"
    
    return adjustment, reason
