"""
Dynamic Intermarket Correlation Analysis Module.

Analyzes dynamic relationships between markets:
- Rolling window correlation (time-varying correlation)
- Lead-lag analysis (which market leads)
- Correlation regime detection (low vs high correlation regimes)
- Cointegration analysis (long-term relationships)
- Dynamic beta calculation

Markets analyzed:
- IHSG vs Global indices (S&P500, NASDAQ, DOW, NIKKEI, HANG_SENG, STI)
- IHSG vs Commodities (Gold, Oil)
- IHSG vs USD/IDR
- IHSG vs VIX
- Sector vs Sector correlations
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging
from scipy import stats

logger = logging.getLogger("saham.dynamic_correlation")


@dataclass
class CorrelationResult:
    """Correlation analysis result."""
    market1: str
    market2: str
    correlation: float
    p_value: float
    window: int
    regime: str  # "high", "low", "neutral"
    beta: float  # Beta of market1 relative to market2
    interpretation: str


@dataclass
class LeadLagResult:
    """Lead-lag analysis result."""
    leader: str
    follower: str
    optimal_lag: int  # Days
    lag_correlation: float
    confidence: float
    interpretation: str


@dataclass
class CorrelationRegime:
    """Correlation regime analysis."""
    regime: str  # "high_correlation", "low_correlation", "decoupling"
    avg_correlation: float
    volatility: float
    duration_days: int
    transition_probability: float


class DynamicCorrelationAnalyzer:
    """
    Analyzes dynamic intermarket correlations.
    
    Uses rolling windows to detect changing relationships.
    Identifies lead-lag patterns and correlation regimes.
    """

    def __init__(self):
        self.correlation_history: List[CorrelationResult] = []

    def calculate_rolling_correlation(
        self,
        series1: pd.Series,
        series2: pd.Series,
        window: int = 20
    ) -> pd.Series:
        """
        Calculate rolling correlation between two series.
        
        Args:
            series1: First price series (returns)
            series2: Second price series (returns)
            window: Rolling window size in days
            
        Returns:
            Series of rolling correlations.
        """
        returns1 = series1.pct_change().dropna()
        returns2 = series2.pct_change().dropna()
        
        # Align series
        aligned = pd.concat([returns1, returns2], axis=1, join='inner')
        if len(aligned) < window:
            return pd.Series()
        
        rolling_corr = aligned.iloc[:, 0].rolling(window=window).corr(aligned.iloc[:, 1])
        return rolling_corr.dropna()

    def calculate_dynamic_beta(
        self,
        market_returns: pd.Series,
        benchmark_returns: pd.Series,
        window: int = 20
    ) -> pd.Series:
        """
        Calculate dynamic beta using rolling regression.
        
        Args:
            market_returns: Market returns
            benchmark_returns: Benchmark returns
            window: Rolling window size
            
        Returns:
            Series of rolling beta values.
        """
        aligned = pd.concat([market_returns, benchmark_returns], axis=1, join='inner')
        if len(aligned) < window:
            return pd.Series()
        
        rolling_beta = pd.Series(index=aligned.index, dtype=float)
        
        for i in range(window, len(aligned)):
            window_data = aligned.iloc[i-window:i]
            if len(window_data.dropna()) < window:
                continue
            
            x = window_data.iloc[:, 1].values
            y = window_data.iloc[:, 0].values
            
            # Simple linear regression
            slope, _, _, _, _ = stats.linregress(x, y)
            rolling_beta.iloc[i] = slope
        
        return rolling_beta.dropna()

    def detect_correlation_regime(
        self,
        correlation_series: pd.Series,
        high_threshold: float = 0.7,
        low_threshold: float = 0.3
    ) -> CorrelationRegime:
        """
        Detect correlation regime from correlation series.
        
        Args:
            correlation_series: Series of correlation values
            high_threshold: Threshold for high correlation regime
            low_threshold: Threshold for low correlation regime
            
        Returns:
            CorrelationRegime with regime information.
        """
        if correlation_series.empty:
            return CorrelationRegime(
                regime="unknown",
                avg_correlation=0.0,
                volatility=0.0,
                duration_days=0,
                transition_probability=0.0,
            )
        
        avg_corr = correlation_series.mean()
        volatility = correlation_series.std()
        
        # Determine regime
        if avg_corr >= high_threshold:
            regime = "high_correlation"
        elif avg_corr <= low_threshold:
            regime = "low_correlation"
        else:
            regime = "neutral"
        
        # Calculate duration (consecutive days in current regime)
        current_regime = "high" if correlation_series.iloc[-1] >= high_threshold else \
                        "low" if correlation_series.iloc[-1] <= low_threshold else "neutral"
        duration = 0
        for corr in reversed(correlation_series):
            if current_regime == "high" and corr >= high_threshold:
                duration += 1
            elif current_regime == "low" and corr <= low_threshold:
                duration += 1
            elif current_regime == "neutral" and low_threshold < corr < high_threshold:
                duration += 1
            else:
                break
        
        # Estimate transition probability (simple heuristic)
        transition_prob = min(1.0, volatility / (avg_corr + 0.01))
        
        return CorrelationRegime(
            regime=regime,
            avg_correlation=round(avg_corr, 3),
            volatility=round(volatility, 3),
            duration_days=duration,
            transition_probability=round(transition_prob, 3),
        )

    def analyze_lead_lag(
        self,
        series1: pd.Series,
        series2: pd.Series,
        max_lag: int = 10
    ) -> LeadLagResult:
        """
        Analyze lead-lag relationship between two series.
        
        Args:
            series1: First price series
            series2: Second price series
            max_lag: Maximum lag to test in days
            
        Returns:
            LeadLagResult with optimal lag and correlation.
        """
        returns1 = series1.pct_change().dropna()
        returns2 = series2.pct_change().dropna()
        
        aligned = pd.concat([returns1, returns2], axis=1, join='inner')
        if len(aligned) < max_lag + 10:
            return LeadLagResult(
                leader="unknown",
                follower="unknown",
                optimal_lag=0,
                lag_correlation=0.0,
                confidence=0.0,
                interpretation="Insufficient data for lead-lag analysis",
            )
        
        correlations = []
        for lag in range(-max_lag, max_lag + 1):
            if lag > 0:
                # series1 leads series2
                corr = aligned.iloc[:, 0].shift(lag).corr(aligned.iloc[:, 1])
            elif lag < 0:
                # series2 leads series1
                corr = aligned.iloc[:, 0].corr(aligned.iloc[:, 1].shift(abs(lag)))
            else:
                # contemporaneous
                corr = aligned.iloc[:, 0].corr(aligned.iloc[:, 1])
            
            correlations.append((lag, corr))
        
        # Find optimal lag
        best_lag, best_corr = max(correlations, key=lambda x: abs(x[1]))
        
        # Determine leader
        if best_lag > 0:
            leader = "series1"
            follower = "series2"
        elif best_lag < 0:
            leader = "series2"
            follower = "series1"
        else:
            leader = "none"
            follower = "none"
        
        # Calculate confidence based on correlation strength
        confidence = min(1.0, abs(best_corr) * 1.5)
        
        interpretation = f"{leader} leads {follower} by {abs(best_lag)} days with correlation {best_corr:.3f}"
        
        return LeadLagResult(
            leader=leader,
            follower=follower,
            optimal_lag=abs(best_lag),
            lag_correlation=round(best_corr, 3),
            confidence=round(confidence, 2),
            interpretation=interpretation,
        )

    def analyze_correlation(
        self,
        market_data: Dict[str, pd.DataFrame],
        market1: str,
        market2: str,
        window: int = 20
    ) -> CorrelationResult:
        """
        Analyze correlation between two markets.
        
        Args:
            market_data: Dict of ticker -> DataFrame
            market1: First market ticker
            market2: Second market ticker
            window: Rolling window size
            
        Returns:
            CorrelationResult with analysis.
        """
        if market1 not in market_data or market2 not in market_data:
            return CorrelationResult(
                market1=market1,
                market2=market2,
                correlation=0.0,
                p_value=1.0,
                window=window,
                regime="unknown",
                beta=0.0,
                interpretation="Data not available",
            )
        
        df1 = market_data[market1]["Close"]
        df2 = market_data[market2]["Close"]
        
        # Calculate rolling correlation
        rolling_corr = self.calculate_rolling_correlation(df1, df2, window)
        
        if rolling_corr.empty:
            return CorrelationResult(
                market1=market1,
                market2=market2,
                correlation=0.0,
                p_value=1.0,
                window=window,
                regime="unknown",
                beta=0.0,
                interpretation="Insufficient data",
            )
        
        current_corr = rolling_corr.iloc[-1]
        
        # Detect regime
        regime_analysis = self.detect_correlation_regime(rolling_corr)
        
        # Calculate beta
        returns1 = df1.pct_change().dropna()
        returns2 = df2.pct_change().dropna()
        aligned = pd.concat([returns1, returns2], axis=1, join='inner')
        if len(aligned) >= window:
            beta = self.calculate_dynamic_beta(aligned.iloc[:, 0], aligned.iloc[:, 1], window).iloc[-1]
        else:
            beta = 0.0
        
        # Statistical significance
        _, p_value = stats.pearsonr(returns1.dropna(), returns2.dropna())
        
        # Interpretation
        if abs(current_corr) > 0.7:
            interpretation = f"Strong {'positive' if current_corr > 0 else 'negative'} correlation - markets move together"
        elif abs(current_corr) > 0.4:
            interpretation = f"Moderate {'positive' if current_corr > 0 else 'negative'} correlation - some relationship"
        elif abs(current_corr) > 0.2:
            interpretation = f"Weak {'positive' if current_corr > 0 else 'negative'} correlation - limited relationship"
        else:
            interpretation = "No significant correlation - markets move independently"
        
        return CorrelationResult(
            market1=market1,
            market2=market2,
            correlation=round(current_corr, 3),
            p_value=round(p_value, 3),
            window=window,
            regime=regime_analysis.regime,
            beta=round(beta, 3),
            interpretation=interpretation,
        )

    def analyze_all_correlations(
        self,
        market_data: Dict[str, pd.DataFrame],
        target: str = "IHSG",
        window: int = 20
    ) -> Dict[str, CorrelationResult]:
        """
        Analyze correlations between target and all other markets.
        
        Args:
            market_data: Dict of ticker -> DataFrame
            target: Target market ticker
            window: Rolling window size
            
        Returns:
            Dict of market -> CorrelationResult.
        """
        results = {}
        
        for market in market_data:
            if market == target:
                continue
            
            result = self.analyze_correlation(market_data, target, market, window)
            results[market] = result
        
        return results

    def get_correlation_signal(self, market_data: Dict[str, pd.DataFrame]) -> Dict:
        """
        Get correlation-based trading signal.
        
        Args:
            market_data: Dict of ticker -> DataFrame
            
        Returns:
            Dict with signal and analysis.
        """
        # Analyze IHSG vs key markets
        key_markets = ["S&P500", "NASDAQ", "DOW", "NIKKEI", "HANG_SENG", "STI", "GOLD", "OIL", "VIX"]
        
        correlations = self.analyze_all_correlations(market_data, "IHSG")
        
        # Count bullish/bearish signals
        bullish_count = 0
        bearish_count = 0
        details = []
        
        for market, result in correlations.items():
            if market not in key_markets:
                continue
            
            if result.correlation > 0.5:
                bullish_count += 1
                details.append(f"{market}: Strong positive correlation ({result.correlation:.2f})")
            elif result.correlation < -0.5:
                bearish_count += 1
                details.append(f"{market}: Strong negative correlation ({result.correlation:.2f})")
        
        # Determine overall signal
        if bullish_count > bearish_count + 2:
            signal = "BUY"
            confidence = 0.6
        elif bearish_count > bullish_count + 2:
            signal = "SELL"
            confidence = 0.6
        else:
            signal = "HOLD"
            confidence = 0.5
        
        return {
            "signal": signal,
            "confidence": confidence,
            "bullish_markets": bullish_count,
            "bearish_markets": bearish_count,
            "details": details,
        }


def get_correlation_adjustment(market_data: Dict[str, pd.DataFrame]) -> Tuple[float, str]:
    """
    Get confidence adjustment from correlation analysis for prediction pipeline.
    
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    analyzer = DynamicCorrelationAnalyzer()
    signal_data = analyzer.get_correlation_signal(market_data)
    
    if signal_data["signal"] == "BUY":
        adjustment = 0.02 * signal_data["confidence"]
        reason = f"Global correlation bullish ({signal_data['bullish_markets']} vs {signal_data['bearish_markets']})"
    elif signal_data["signal"] == "SELL":
        adjustment = -0.02 * signal_data["confidence"]
        reason = f"Global correlation bearish ({signal_data['bearish_markets']} vs {signal_data['bullish_markets']})"
    else:
        adjustment = 0.0
        reason = "Global correlation mixed - no clear signal"
    
    return adjustment, reason
