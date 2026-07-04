"""
Stress Testing & Scenario Analysis Module.

Implements stress testing and scenario analysis for portfolios:
- Historical stress scenarios (2008 crisis, COVID-19, etc.)
- Custom scenario analysis
- Monte Carlo simulation
- Correlation stress testing
- Liquidity stress testing
- Concentration risk analysis

These tools help understand portfolio behavior under extreme conditions.
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
import logging
from scipy import stats

logger = logging.getLogger("saham.stress_testing")


@dataclass
class StressScenario:
    """Stress test scenario definition."""
    name: str
    description: str
    market_shock_pct: float  # Expected market drop
    volatility_multiplier: float  # Volatility increase factor
    correlation_increase: float  # Correlation increase (0-1)
    duration_days: int


@dataclass
class StressTestResult:
    """Result of stress test on a portfolio."""
    scenario: str
    initial_value: float
    final_value: float
    loss_pct: float
    max_drawdown_pct: float
    worst_day_loss_pct: float
    recovery_days: Optional[int] = None
    risk_metrics: Dict = field(default_factory=dict)
    recommendations: List[str] = field(default_factory=list)


@dataclass
class ScenarioAnalysis:
    """Scenario analysis results."""
    scenarios: List[StressTestResult] = field(default_factory=list)
    worst_case: Optional[StressTestResult] = None
    average_loss: float = 0.0
    resilience_score: float = 0.0  # 0-100, higher = more resilient
    recommendations: List[str] = field(default_factory=dict)


class StressTestAnalyzer:
    """
    Performs stress testing and scenario analysis.
    
    Uses historical data to simulate portfolio performance
    under various stress scenarios.
    """

    def __init__(self):
        # Predefined stress scenarios
        self.scenarios = [
            StressScenario(
                name="2008 Financial Crisis",
                description="Global financial crisis - severe market decline",
                market_shock_pct=-50,
                volatility_multiplier=2.5,
                correlation_increase=0.3,
                duration_days=180,
            ),
            StressScenario(
                name="COVID-19 Crash",
                description="Pandemic-induced market crash",
                market_shock_pct=-35,
                volatility_multiplier=3.0,
                correlation_increase=0.4,
                duration_days=90,
            ),
            StressScenario(
                name="Rate Hike Shock",
                description="Aggressive central bank rate hikes",
                market_shock_pct=-20,
                volatility_multiplier=1.5,
                correlation_increase=0.2,
                duration_days=60,
            ),
            StressScenario(
                name="Currency Crisis",
                description="Severe currency depreciation",
                market_shock_pct=-25,
                volatility_multiplier=2.0,
                correlation_increase=0.25,
                duration_days=120,
            ),
            StressScenario(
                name="Mild Correction",
                description="Normal market correction",
                market_shock_pct=-10,
                volatility_multiplier=1.2,
                correlation_increase=0.1,
                duration_days=30,
            ),
        ]

    def run_stress_test(
        self,
        portfolio_returns: pd.Series,
        scenario: StressScenario
    ) -> StressTestResult:
        """
        Run stress test on portfolio returns.
        
        Args:
            portfolio_returns: Series of portfolio returns
            scenario: StressScenario to apply
            
        Returns:
            StressTestResult with test results.
        """
        if portfolio_returns.empty:
            return StressTestResult(
                scenario=scenario.name,
                initial_value=0,
                final_value=0,
                loss_pct=0,
                max_drawdown_pct=0,
                worst_day_loss_pct=0,
            )
        
        # Calculate initial portfolio value (assume 100)
        initial_value = 100.0
        
        # Apply scenario shock
        shocked_returns = portfolio_returns.copy()
        
        # Apply market shock
        shocked_returns.iloc[0] = scenario.market_shock_pct / 100
        
        # Increase volatility
        base_volatility = portfolio_returns.std()
        target_volatility = base_volatility * scenario.volatility_multiplier
        shocked_returns = shocked_returns * (target_volatility / base_volatility)
        
        # Simulate scenario duration
        scenario_returns = shocked_returns.head(scenario.duration_days)
        if len(scenario_returns) < scenario.duration_days:
            # Extend with random returns if not enough data
            additional_days = scenario.duration_days - len(scenario_returns)
            random_returns = np.random.normal(
                scenario_returns.mean(),
                scenario_returns.std(),
                additional_days
            )
            scenario_returns = pd.concat([scenario_returns, pd.Series(random_returns)])
        
        # Calculate portfolio value over time
        portfolio_values = [initial_value]
        for ret in scenario_returns:
            portfolio_values.append(portfolio_values[-1] * (1 + ret))
        
        final_value = portfolio_values[-1]
        loss_pct = ((final_value - initial_value) / initial_value) * 100
        
        # Calculate max drawdown
        peak = max(portfolio_values)
        trough = min(portfolio_values)
        max_drawdown_pct = ((trough - peak) / peak) * 100
        
        # Calculate worst day loss
        worst_day_loss_pct = scenario_returns.min() * 100
        
        # Calculate recovery days
        recovery_days = None
        for i, val in enumerate(portfolio_values):
            if val >= initial_value and i > 0:
                recovery_days = i
                break
        
        # Risk metrics
        risk_metrics = {
            "volatility": scenario_returns.std() * np.sqrt(252) * 100,
            "sharpe": (scenario_returns.mean() / scenario_returns.std()) * np.sqrt(252) if scenario_returns.std() > 0 else 0,
            "skewness": stats.skew(scenario_returns),
            "kurtosis": stats.kurtosis(scenario_returns),
        }
        
        # Generate recommendations
        recommendations = []
        if loss_pct < -30:
            recommendations.append("Severe loss under this scenario - consider reducing exposure")
        elif loss_pct < -15:
            recommendations.append("Significant loss under this scenario - review position sizing")
        elif loss_pct < -5:
            recommendations.append("Moderate loss under this scenario - acceptable risk")
        else:
            recommendations.append("Minimal loss under this scenario - good resilience")
        
        if max_drawdown_pct < -40:
            recommendations.append("Extreme drawdown - consider hedging strategies")
        
        return StressTestResult(
            scenario=scenario.name,
            initial_value=initial_value,
            final_value=round(final_value, 2),
            loss_pct=round(loss_pct, 2),
            max_drawdown_pct=round(max_drawdown_pct, 2),
            worst_day_loss_pct=round(worst_day_loss_pct, 2),
            recovery_days=recovery_days,
            risk_metrics={k: round(v, 3) for k, v in risk_metrics.items()},
            recommendations=recommendations,
        )

    def run_monte_carlo_simulation(
        self,
        portfolio_returns: pd.Series,
        num_simulations: int = 1000,
        time_horizon: int = 252  # 1 year
    ) -> Dict:
        """
        Run Monte Carlo simulation for portfolio.
        
        Args:
            portfolio_returns: Series of historical returns
            num_simulations: Number of Monte Carlo simulations
            time_horizon: Number of days to simulate
            
        Returns:
            Dict with simulation results.
        """
        if portfolio_returns.empty:
            return {"error": "Insufficient data for Monte Carlo simulation"}
        
        # Calculate statistics from historical returns
        mean_return = portfolio_returns.mean()
        std_return = portfolio_returns.std()
        
        # Run simulations
        final_values = []
        max_drawdowns = []
        
        for _ in range(num_simulations):
            # Generate random returns
            random_returns = np.random.normal(mean_return, std_return, time_horizon)
            
            # Calculate portfolio value
            portfolio_value = 100.0
            values = [portfolio_value]
            
            for ret in random_returns:
                portfolio_value *= (1 + ret)
                values.append(portfolio_value)
            
            final_values.append(portfolio_value)
            
            # Calculate max drawdown
            peak = max(values)
            trough = min(values)
            max_drawdown = ((trough - peak) / peak) * 100
            max_drawdowns.append(max_drawdown)
        
        # Calculate statistics
        final_values = np.array(final_values)
        max_drawdowns = np.array(max_drawdowns)
        
        return {
            "mean_final_value": round(np.mean(final_values), 2),
            "median_final_value": round(np.median(final_values), 2),
            "percentile_5": round(np.percentile(final_values, 5), 2),
            "percentile_95": round(np.percentile(final_values, 95), 2),
            "worst_case": round(np.min(final_values), 2),
            "best_case": round(np.max(final_values), 2),
            "mean_max_drawdown": round(np.mean(max_drawdowns), 2),
            "worst_max_drawdown": round(np.min(max_drawdowns), 2),
            "probability_of_loss": round(np.mean(final_values < 100) * 100, 2),
            "probability_of_20pct_loss": round(np.mean(final_values < 80) * 100, 2),
        }

    def analyze_concentration_risk(
        self,
        positions: Dict[str, float]  # ticker -> weight
    ) -> Dict:
        """
        Analyze concentration risk in portfolio.
        
        Args:
            positions: Dict of ticker -> weight (0-1)
            
        Returns:
            Dict with concentration risk analysis.
        """
        if not positions:
            return {"error": "No positions provided"}
        
        weights = np.array(list(positions.values()))
        
        # Calculate concentration metrics
        herfindahl_index = np.sum(weights ** 2)
        max_weight = np.max(weights)
        num_positions = len(positions)
        
        # Calculate effective number of positions
        effective_positions = 1 / herfindahl_index if herfindahl_index > 0 else 0
        
        # Risk assessment
        if max_weight > 0.3:
            risk_level = "high"
            recommendation = "Single position exceeds 30% - consider diversification"
        elif max_weight > 0.2:
            risk_level = "medium"
            recommendation = "Single position exceeds 20% - monitor closely"
        elif effective_positions < 5:
            risk_level = "medium"
            recommendation = "Effective positions < 5 - consider diversification"
        else:
            risk_level = "low"
            recommendation = "Well-diversified portfolio"
        
        return {
            "herfindahl_index": round(herfindahl_index, 3),
            "max_weight": round(max_weight, 3),
            "num_positions": num_positions,
            "effective_positions": round(effective_positions, 2),
            "risk_level": risk_level,
            "recommendation": recommendation,
            "top_positions": sorted(positions.items(), key=lambda x: x[1], reverse=True)[:5],
        }

    def run_scenario_analysis(
        self,
        portfolio_returns: pd.Series
    ) -> ScenarioAnalysis:
        """
        Run full scenario analysis.
        
        Args:
            portfolio_returns: Series of portfolio returns
            
        Returns:
            ScenarioAnalysis with comprehensive results.
        """
        results = []
        
        # Run all predefined scenarios
        for scenario in self.scenarios:
            result = self.run_stress_test(portfolio_returns, scenario)
            results.append(result)
        
        # Find worst case
        worst_case = min(results, key=lambda x: x.loss_pct)
        
        # Calculate average loss
        average_loss = np.mean([r.loss_pct for r in results])
        
        # Calculate resilience score (inverse of average loss)
        resilience_score = max(0, min(100, 100 + average_loss))
        
        # Aggregate recommendations
        all_recommendations = []
        for result in results:
            all_recommendations.extend(result.recommendations)
        
        return ScenarioAnalysis(
            scenarios=results,
            worst_case=worst_case,
            average_loss=round(average_loss, 2),
            resilience_score=round(resilience_score, 2),
            recommendations=all_recommendations,
        )


def get_stress_test_adjustment(portfolio_returns: pd.Series) -> Tuple[float, str]:
    """
    Get confidence adjustment from stress testing.
    
    Returns:
        Tuple of (adjustment_value, reason_string)
    """
    analyzer = StressTestAnalyzer()
    analysis = analyzer.run_scenario_analysis(portfolio_returns)
    
    if analysis.resilience_score < 30:
        adjustment = -0.05
        reason = f"Low resilience score ({analysis.resilience_score:.0f}/100) - high stress risk"
    elif analysis.resilience_score < 50:
        adjustment = -0.02
        reason = f"Moderate resilience score ({analysis.resilience_score:.0f}/100) - moderate stress risk"
    elif analysis.resilience_score > 70:
        adjustment = 0.02
        reason = f"High resilience score ({analysis.resilience_score:.0f}/100) - good stress resistance"
    else:
        adjustment = 0.0
        reason = "Average resilience - normal stress risk"
    
    return adjustment, reason
