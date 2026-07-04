"""
Market Structure Analysis Module.

Analyzes internal market strength/weakness through:
- Advancers vs Decliners (breadth)
- New Highs vs New Lows
- Volume Analysis (up volume vs down volume)
- Tick Analysis (uptick vs downtick)
- McClellan Oscillator
- Arms Index (TRIN)

Market structure shows what's happening beneath the surface of the index.
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging

logger = logging.getLogger("saham.market_structure")


@dataclass
class MarketBreadth:
    """Market breadth data for a single day."""
    date: str
    advancers: int  # Number of stocks that went up
    decliners: int  # Number of stocks that went down
    unchanged: int  # Number of stocks that stayed the same
    advancers_volume: float  # Total volume of advancers
    decliners_volume: float  # Total volume of decliners
    new_highs: int  # Number of stocks making new highs
    new_lows: int  # Number of stocks making new lows
    adv_decl_ratio: float  # Advancers/Decliners ratio
    volume_ratio: float  # Up volume/Down volume ratio


@dataclass
class MarketStructureSummary:
    """Summary of market structure analysis."""
    date: str
    breadth: MarketBreadth
    mcclellan_oscillator: float
    arms_index: float
    breadth_signal: str  # "bullish", "bearish", "neutral"
    volume_signal: str
    overall_signal: str
    strength_score: float  # 0-100, higher = stronger market
    recommendations: List[str] = field(default_factory=list)


class MarketStructureAnalyzer:
    """
    Analyzes market structure to gauge internal strength/weakness.
    
    Uses stock-level data to calculate breadth indicators.
    Falls back to proxy estimation when individual stock data unavailable.
    """

    def __init__(self):
        self.breadth_history: List[MarketBreadth] = []

    def calculate_market_breadth(
        self,
        market_data: Dict[str, pd.DataFrame],
        date: Optional[str] = None
    ) -> MarketBreadth:
        """
        Calculate market breadth indicators.
        
        Args:
            market_data: Dict of ticker -> DataFrame with OHLCV data
            date: Date to analyze. If None, use latest date.
            
        Returns:
            MarketBreadth with breadth indicators.
        """
        if date is None:
            date = datetime.now().strftime("%Y-%m-%d")
        
        advancers = 0
        decliners = 0
        unchanged = 0
        advancers_volume = 0
        decliners_volume = 0
        new_highs = 0
        new_lows = 0
        
        for ticker, df in market_data.items():
            if df.empty or len(df) < 2:
                continue
            
            # Calculate daily change
            close_change = (df["Close"].iloc[-1] / df["Close"].iloc[-2] - 1) * 100
            
            # Count advancers/decliners
            if close_change > 0:
                advancers += 1
                advancers_volume += df["Volume"].iloc[-1]
            elif close_change < 0:
                decliners += 1
                decliners_volume += df["Volume"].iloc[-1]
            else:
                unchanged += 1
            
            # Check for new highs/lows (52-week)
            if len(df) >= 252:
                recent_high = df["Close"].iloc[-252:].max()
                recent_low = df["Close"].iloc[-252:].min()
                current = df["Close"].iloc[-1]
                
                if current >= recent_high * 0.999:  # Allow small rounding
                    new_highs += 1
                elif current <= recent_low * 1.001:
                    new_lows += 1
        
        # Calculate ratios
        adv_decl_ratio = advancers / decliners if decliners > 0 else float('inf') if advancers > 0 else 1.0
        volume_ratio = advancers_volume / decliners_volume if decliners_volume > 0 else float('inf') if advancers_volume > 0 else 1.0
        
        return MarketBreadth(
            date=date,
            advancers=advancers,
            decliners=decliners,
            unchanged=unchanged,
            advancers_volume=advancers_volume,
            decliners_volume=decliners_volume,
            new_highs=new_highs,
            new_lows=new_lows,
            adv_decl_ratio=round(adv_decl_ratio, 2),
            volume_ratio=round(volume_ratio, 2),
        )

    def calculate_mcclellan_oscillator(self, breadth_history: List[MarketBreadth]) -> float:
        """
        Calculate McClellan Oscillator.
        
        McClellan Oscillator = EMA(19) of (Advancers - Decliners) - EMA(39) of (Advancers - Decliners)
        
        Args:
            breadth_history: List of MarketBreadth data points.
            
        Returns:
            McClellan Oscillator value.
        """
        if len(breadth_history) < 39:
            return 0.0
        
        # Calculate net advances
        net_advances = [b.advancers - b.decliners for b in breadth_history]
        
        # Calculate EMAs
        ema19 = pd.Series(net_advances).ewm(span=19, adjust=False).mean().iloc[-1]
        ema39 = pd.Series(net_advances).ewm(span=39, adjust=False).mean().iloc[-1]
        
        return round(ema19 - ema39, 2)

    def calculate_arms_index(self, breadth: MarketBreadth) -> float:
        """
        Calculate Arms Index (TRIN).
        
        TRIN = (Advancers/Decliners) / (Up Volume/Down Volume)
        
        Args:
            breadth: MarketBreadth data.
            
        Returns:
            Arms Index value.
        """
        if breadth.decliners == 0 or breadth.decliners_volume == 0:
            return 1.0
        
        trin = (breadth.advancers / breadth.decliners) / (breadth.advancers_volume / breadth.decliners_volume)
        return round(trin, 4)

    def analyze_market_structure(
        self,
        market_data: Dict[str, pd.DataFrame],
        date: Optional[str] = None
    ) -> MarketStructureSummary:
        """
        Analyze overall market structure.
        
        Args:
            market_data: Dict of ticker -> DataFrame with OHLCV data
            date: Date to analyze.
            
        Returns:
            MarketStructureSummary with analysis results.
        """
        if date is None:
            date = datetime.now().strftime("%Y-%m-%d")
        
        # Calculate breadth
        breadth = self.calculate_market_breadth(market_data, date)
        
        # Store in history
        self.breadth_history.append(breadth)
        
        # Calculate indicators
        mcclellan = self.calculate_mcclellan_oscillator(self.breadth_history)
        arms = self.calculate_arms_index(breadth)
        
        # Determine signals
        breadth_signal = self._interpret_breadth(breadth)
        volume_signal = self._interpret_volume(breadth)
        overall_signal = self._interpret_overall(breadth, mcclellan, arms)
        
        # Calculate strength score
        strength_score = self._calculate_strength_score(breadth, mcclellan, arms)
        
        # Generate recommendations
        recommendations = self._generate_recommendations(breadth, mcclellan, arms, overall_signal)
        
        return MarketStructureSummary(
            date=date,
            breadth=breadth,
            mcclellan_oscillator=mcclellan,
            arms_index=arms,
            breadth_signal=breadth_signal,
            volume_signal=volume_signal,
            overall_signal=overall_signal,
            strength_score=round(strength_score, 2),
            recommendations=recommendations,
        )

    def _interpret_breadth(self, breadth: MarketBreadth) -> str:
        """Interpret breadth indicators."""
        if breadth.adv_decl_ratio > 2.0:
            return "bullish"
        elif breadth.adv_decl_ratio > 1.5:
            return "mildly_bullish"
        elif breadth.adv_decl_ratio < 0.5:
            return "bearish"
        elif breadth.adv_decl_ratio < 0.67:
            return "mildly_bearish"
        else:
            return "neutral"

    def _interpret_volume(self, breadth: MarketBreadth) -> str:
        """Interpret volume indicators."""
        if breadth.volume_ratio > 2.0:
            return "bullish"
        elif breadth.volume_ratio > 1.5:
            return "mildly_bullish"
        elif breadth.volume_ratio < 0.5:
            return "bearish"
        elif breadth.volume_ratio < 0.67:
            return "mildly_bearish"
        else:
            return "neutral"

    def _interpret_overall(self, breadth: MarketBreadth, mcclellan: float, arms: float) -> str:
        """Interpret overall market structure."""
        bullish_signals = 0
        bearish_signals = 0
        
        # Breadth
        if breadth.adv_decl_ratio > 1.5:
            bullish_signals += 1
        elif breadth.adv_decl_ratio < 0.67:
            bearish_signals += 1
        
        # McClellan
        if mcclellan > 50:
            bullish_signals += 1
        elif mcclellan < -50:
            bearish_signals += 1
        
        # Arms Index
        if arms < 0.8:
            bullish_signals += 1
        elif arms > 1.2:
            bearish_signals += 1
        
        # New highs/lows
        if breadth.new_highs > breadth.new_lows * 2:
            bullish_signals += 1
        elif breadth.new_lows > breadth.new_highs * 2:
            bearish_signals += 1
        
        if bullish_signals >= 3:
            return "bullish"
        elif bearish_signals >= 3:
            return "bearish"
        elif bullish_signals > bearish_signals:
            return "mildly_bullish"
        elif bearish_signals > bullish_signals:
            return "mildly_bearish"
        else:
            return "neutral"

    def _calculate_strength_score(self, breadth: MarketBreadth, mcclellan: float, arms: float) -> float:
        """Calculate overall market strength score (0-100)."""
        score = 50.0  # Base score
        
        # Breadth contribution
        if breadth.adv_decl_ratio > 1:
            score += min(20, (breadth.adv_decl_ratio - 1) * 10)
        else:
            score -= min(20, (1 - breadth.adv_decl_ratio) * 10)
        
        # McClellan contribution
        score += min(15, max(-15, mcclellan / 10))
        
        # Arms Index contribution
        if arms < 1:
            score += min(10, (1 - arms) * 20)
        else:
            score -= min(10, (arms - 1) * 20)
        
        # New highs/lows contribution
        if breadth.new_highs > 0 or breadth.new_lows > 0:
            hl_ratio = breadth.new_highs / (breadth.new_lows + 1)
            score += min(5, max(-5, (hl_ratio - 1) * 2))
        
        return max(0, min(100, score))

    def _generate_recommendations(
        self,
        breadth: MarketBreadth,
        mcclellan: float,
        arms: float,
        overall_signal: str
    ) -> List[str]:
        """Generate trading recommendations based on market structure."""
        recommendations = []
        
        if overall_signal == "bullish":
            recommendations.append("Market breadth strong - consider increasing equity exposure")
            if breadth.new_highs > 10:
                recommendations.append("Multiple new highs - trend likely to continue")
            if arms < 0.7:
                recommendations.append("Low TRIN indicates strong buying pressure")
        elif overall_signal == "bearish":
            recommendations.append("Market breadth weak - consider reducing equity exposure")
            if breadth.new_lows > 10:
                recommendations.append("Multiple new lows - avoid catching falling knives")
            if arms > 1.3:
                recommendations.append("High TRIN indicates panic selling - contrarian opportunity")
        else:
            recommendations.append("Market breadth mixed - wait for clearer signal")
        
        # McClellan specific
        if mcclellan > 100:
            recommendations.append("McClellan overbought - potential pullback ahead")
        elif mcclellan < -100:
            recommendations.append("McClellan oversold - potential bounce ahead")
        
        return recommendations

    def get_structure_signal(self, market_data: Dict[str, pd.DataFrame]) -> Dict:
        """
        Get market structure signal for trading decisions.
        
        Args:
            market_data: Dict of ticker -> DataFrame with OHLCV data
            
        Returns:
            Dict with signal and confidence.
        """
        summary = self.analyze_market_structure(market_data)
        
        signal = "HOLD"
        confidence = 0.5
        
        if summary.overall_signal == "bullish":
            signal = "BUY"
            confidence = 0.5 + (summary.strength_score / 200)
        elif summary.overall_signal == "bearish":
            signal = "SELL"
            confidence = 0.5 + ((100 - summary.strength_score) / 200)
        elif summary.overall_signal == "mildly_bullish":
            signal = "BUY"
            confidence = 0.55
        elif summary.overall_signal == "mildly_bearish":
            signal = "SELL"
            confidence = 0.55
        
        return {
            "signal": signal,
            "confidence": round(confidence, 2),
            "strength_score": summary.strength_score,
            "breadth_signal": summary.breadth_signal,
            "volume_signal": summary.volume_signal,
            "mcclellan": summary.mcclellan_oscillator,
            "arms": summary.arms_index,
            "recommendations": summary.recommendations,
        }


def get_market_structure_adjustment(market_data: Dict[str, pd.DataFrame]) -> Tuple[float, str]:
    """
    Get confidence adjustment from market structure for prediction pipeline.
    
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    analyzer = MarketStructureAnalyzer()
    signal_data = analyzer.get_structure_signal(market_data)
    
    if signal_data["signal"] == "BUY":
        adjustment = 0.03 * signal_data["confidence"]
        reason = f"Market structure bullish (strength: {signal_data['strength_score']:.0f})"
    elif signal_data["signal"] == "SELL":
        adjustment = -0.03 * signal_data["confidence"]
        reason = f"Market structure bearish (strength: {signal_data['strength_score']:.0f})"
    else:
        adjustment = 0.0
        reason = "Market structure neutral"
    
    return adjustment, reason
