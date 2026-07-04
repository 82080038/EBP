"""
Advanced Technical Analysis Module.

Implements advanced technical analysis techniques:
- Market Profile Analysis (volume at price)
- Volume Profile Analysis
- Wyckoff Method (accumulation/distribution)
- Support/Resistance zones
- Fibonacci cluster analysis
- Order flow analysis

These techniques provide deeper insight into market structure
beyond basic price action analysis.
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging

logger = logging.getLogger("saham.advanced_technical")


@dataclass
class MarketProfileLevel:
    """Single price level in market profile."""
    price: float
    volume: float
    volume_pct: float
    tpo_count: int  # Time Price Opportunity count


@dataclass
class MarketProfile:
    """Market profile analysis result."""
    date: str
    poc_price: float  # Point of Control (highest volume price)
    vah_price: float  # Value Area High
    val_price: float  # Value Area Low
    value_area_range: float
    profile_range: float
    levels: List[MarketProfileLevel] = field(default_factory=list)
    profile_type: str = ""  # "balanced", "trending", "rotational"


@dataclass
class WyckoffPhase:
    """Wyckoff market phase."""
    phase: str  # "accumulation", "markup", "distribution", "markdown"
    confidence: float
    description: str
    trading_range: Tuple[float, float]
    spring: Optional[float] = None
    test: Optional[float] = None
    sign_of_strength: Optional[float] = None
    sign_of_weakness: Optional[float] = None


@dataclass
class FibonacciCluster:
    """Fibonacci cluster analysis."""
    levels: List[float]
    strong_levels: List[float]
    current_price: float
    nearest_support: Optional[float]
    nearest_resistance: Optional[float]
    confluence_zones: List[Dict] = field(default_factory=list)


class AdvancedTechnicalAnalyzer:
    """
    Implements advanced technical analysis techniques.
    
    Focuses on market structure and volume analysis
    to identify institutional activity and key levels.
    """

    def __init__(self):
        pass

    def calculate_market_profile(
        self,
        df: pd.DataFrame,
        num_levels: int = 50
    ) -> MarketProfile:
        """
        Calculate market profile (volume at price).
        
        Args:
            df: DataFrame with OHLCV data
            num_levels: Number of price levels to analyze
            
        Returns:
            MarketProfile with analysis results.
        """
        if df.empty or len(df) < 10:
            return MarketProfile(
                date=datetime.now().strftime("%Y-%m-%d"),
                poc_price=0,
                vah_price=0,
                val_price=0,
                value_area_range=0,
                profile_range=0,
                profile_type="insufficient_data",
            )
        
        # Calculate price range
        high = df["High"].max()
        low = df["Low"].min()
        price_range = high - low
        
        if price_range == 0:
            return MarketProfile(
                date=datetime.now().strftime("%Y-%m-%d"),
                poc_price=df["Close"].iloc[-1],
                vah_price=df["Close"].iloc[-1],
                val_price=df["Close"].iloc[-1],
                value_area_range=0,
                profile_range=0,
                profile_type="flat",
            )
        
        # Create price levels
        level_size = price_range / num_levels
        levels = []
        
        for i in range(num_levels):
            price_low = low + i * level_size
            price_high = price_low + level_size
            price_mid = (price_low + price_high) / 2
            
            # Calculate volume at this price level
            level_volume = 0
            for _, row in df.iterrows():
                if row["Low"] <= price_mid <= row["High"]:
                    level_volume += row["Volume"]
            
            levels.append(MarketProfileLevel(
                price=price_mid,
                volume=level_volume,
                volume_pct=0,
                tpo_count=0,
            ))
        
        # Calculate volume percentages
        total_volume = sum(l.volume for l in levels)
        for level in levels:
            level.volume_pct = (level.volume / total_volume * 100) if total_volume > 0 else 0
        
        # Find Point of Control (POC) - highest volume level
        poc = max(levels, key=lambda x: x.volume)
        
        # Calculate Value Area (70% of volume)
        sorted_levels = sorted(levels, key=lambda x: x.volume, reverse=True)
        cumulative_volume = 0
        value_area_levels = []
        
        for level in sorted_levels:
            cumulative_volume += level.volume
            value_area_levels.append(level)
            if cumulative_volume >= total_volume * 0.7:
                break
        
        if value_area_levels:
            vah = max(l.price for l in value_area_levels)
            val = min(l.price for l in value_area_levels)
        else:
            vah = poc.price
            val = poc.price
        
        # Determine profile type
        value_area_pct = (vah - val) / price_range if price_range > 0 else 0
        if value_area_pct < 0.3:
            profile_type = "trending"
        elif value_area_pct > 0.6:
            profile_type = "rotational"
        else:
            profile_type = "balanced"
        
        return MarketProfile(
            date=datetime.now().strftime("%Y-%m-%d"),
            poc_price=round(poc.price, 2),
            vah_price=round(vah, 2),
            val_price=round(val, 2),
            value_area_range=round(vah - val, 2),
            profile_range=round(price_range, 2),
            levels=levels,
            profile_type=profile_type,
        )

    def analyze_wyckoff_phase(self, df: pd.DataFrame) -> WyckoffPhase:
        """
        Analyze Wyckoff market phase.
        
        Args:
            df: DataFrame with OHLCV data
            
        Returns:
            WyckoffPhase with phase analysis.
        """
        if df.empty or len(df) < 50:
            return WyckoffPhase(
                phase="unknown",
                confidence=0.0,
                description="Insufficient data",
                trading_range=(0, 0),
            )
        
        # Calculate moving averages
        df = df.copy()
        df["MA20"] = df["Close"].rolling(20).mean()
        df["MA50"] = df["Close"].rolling(50).mean()
        
        # Calculate recent performance
        recent_return = (df["Close"].iloc[-1] / df["Close"].iloc[-20] - 1) * 100
        volume_trend = df["Volume"].iloc[-10:].mean() / df["Volume"].iloc[-30:-10].mean() if len(df) > 30 else 1.0
        
        # Determine phase based on price action and volume
        if recent_return > 5 and volume_trend > 1.2:
            phase = "markup"
            confidence = 0.7
            description = "Price rising with increasing volume - markup phase"
        elif recent_return < -5 and volume_trend > 1.2:
            phase = "markdown"
            confidence = 0.7
            description = "Price falling with increasing volume - markdown phase"
        elif abs(recent_return) < 2 and volume_trend < 0.8:
            phase = "accumulation" if df["MA20"].iloc[-1] > df["MA50"].iloc[-1] else "distribution"
            confidence = 0.5
            description = "Price consolidating with low volume - accumulation/distribution phase"
        else:
            phase = "unknown"
            confidence = 0.3
            description = "Unclear phase - mixed signals"
        
        # Calculate trading range
        trading_range = (df["Low"].iloc[-20:].min(), df["High"].iloc[-20:].max())
        
        return WyckoffPhase(
            phase=phase,
            confidence=round(confidence, 2),
            description=description,
            trading_range=trading_range,
        )

    def calculate_fibonacci_cluster(
        self,
        df: pd.DataFrame,
        lookback: int = 100
    ) -> FibonacciCluster:
        """
        Calculate Fibonacci cluster analysis.
        
        Args:
            df: DataFrame with OHLCV data
            lookback: Number of bars to look back
            
        Returns:
            FibonacciCluster with analysis.
        """
        if df.empty or len(df) < lookback:
            return FibonacciCluster(
                levels=[],
                strong_levels=[],
                current_price=0,
                nearest_support=None,
                nearest_resistance=None,
            )
        
        # Get recent data
        recent = df.tail(lookback)
        high = recent["High"].max()
        low = recent["Low"].min()
        current_price = df["Close"].iloc[-1]
        
        # Calculate Fibonacci levels
        diff = high - low
        fib_ratios = [0, 0.236, 0.382, 0.5, 0.618, 0.786, 1]
        levels = [high - diff * ratio for ratio in fib_ratios]
        
        # Identify strong levels (near previous highs/lows)
        strong_levels = []
        for level in levels:
            # Check if level is near previous swing high/low
            for i in range(len(recent) - 10, len(recent)):
                if abs(recent["High"].iloc[i] - level) < diff * 0.02:
                    strong_levels.append(level)
                    break
                if abs(recent["Low"].iloc[i] - level) < diff * 0.02:
                    strong_levels.append(level)
                    break
        
        # Find nearest support and resistance
        supports = [l for l in levels if l < current_price]
        resistances = [l for l in levels if l > current_price]
        
        nearest_support = max(supports) if supports else None
        nearest_resistance = min(resistances) if resistances else None
        
        # Find confluence zones (multiple levels close together)
        confluence_zones = []
        for i, level1 in enumerate(levels):
            nearby_levels = []
            for level2 in levels[i+1:]:
                if abs(level1 - level2) < diff * 0.05:
                    nearby_levels.append(level2)
            if len(nearby_levels) >= 2:
                confluence_zones.append({
                    "price": level1,
                    "strength": len(nearby_levels) + 1,
                    "levels": [level1] + nearby_levels,
                })
        
        return FibonacciCluster(
            levels=[round(l, 2) for l in levels],
            strong_levels=[round(l, 2) for l in strong_levels],
            current_price=round(current_price, 2),
            nearest_support=round(nearest_support, 2) if nearest_support else None,
            nearest_resistance=round(nearest_resistance, 2) if nearest_resistance else None,
            confluence_zones=confluence_zones,
        )

    def get_advanced_technical_signal(self, df: pd.DataFrame) -> Dict:
        """
        Get advanced technical analysis signal.
        
        Args:
            df: DataFrame with OHLCV data
            
        Returns:
            Dict with signal and analysis.
        """
        # Market profile
        profile = self.calculate_market_profile(df)
        
        # Wyckoff phase
        wyckoff = self.analyze_wyckoff_phase(df)
        
        # Fibonacci
        fib = self.calculate_fibonacci_cluster(df)
        
        # Determine overall signal
        signal = "HOLD"
        confidence = 0.5
        reasons = []
        
        # Wyckoff phase influence
        if wyckoff.phase == "markup" and wyckoff.confidence > 0.6:
            signal = "BUY"
            confidence = 0.65
            reasons.append(f"Wyckoff markup phase - {wyckoff.description}")
        elif wyckoff.phase == "markdown" and wyckoff.confidence > 0.6:
            signal = "SELL"
            confidence = 0.65
            reasons.append(f"Wyckoff markdown phase - {wyckoff.description}")
        
        # Market profile influence
        current_price = df["Close"].iloc[-1]
        if profile.poc_price > 0:
            if current_price > profile.vah_price:
                if signal != "SELL":
                    signal = "BUY"
                    confidence = max(confidence, 0.6)
                reasons.append(f"Price above Value Area High - bullish")
            elif current_price < profile.val_price:
                if signal != "BUY":
                    signal = "SELL"
                    confidence = max(confidence, 0.6)
                reasons.append(f"Price below Value Area Low - bearish")
        
        # Fibonacci influence
        if fib.nearest_resistance and current_price > fib.nearest_resistance * 0.98:
            reasons.append(f"Near Fibonacci resistance at {fib.nearest_resistance}")
            if signal == "BUY":
                confidence *= 0.9
        if fib.nearest_support and current_price < fib.nearest_support * 1.02:
            reasons.append(f"Near Fibonacci support at {fib.nearest_support}")
            if signal == "SELL":
                confidence *= 0.9
        
        return {
            "signal": signal,
            "confidence": round(confidence, 2),
            "reasons": reasons,
            "wyckoff_phase": wyckoff.phase,
            "market_profile_type": profile.profile_type,
            "poc": profile.poc_price,
            "vah": profile.vah_price,
            "val": profile.val_price,
        }


def get_advanced_technical_adjustment(df: pd.DataFrame) -> Tuple[float, str]:
    """
    Get confidence adjustment from advanced technical analysis.
    
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    analyzer = AdvancedTechnicalAnalyzer()
    signal_data = analyzer.get_advanced_technical_signal(df)
    
    if signal_data["signal"] == "BUY":
        adjustment = 0.02 * signal_data["confidence"]
        reason = f"Advanced technical bullish (Wyckoff: {signal_data['wyckoff_phase']}, Profile: {signal_data['market_profile_type']})"
    elif signal_data["signal"] == "SELL":
        adjustment = -0.02 * signal_data["confidence"]
        reason = f"Advanced technical bearish (Wyckoff: {signal_data['wyckoff_phase']}, Profile: {signal_data['market_profile_type']})"
    else:
        adjustment = 0.0
        reason = "Advanced technical neutral"
    
    return adjustment, reason
