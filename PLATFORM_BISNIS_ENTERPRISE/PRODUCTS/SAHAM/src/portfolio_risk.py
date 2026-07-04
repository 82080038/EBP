"""
Portfolio-Level Risk Management.

Implementasi risk management untuk portofolio multi-asset:
- Portfolio VaR (Historical, Parametric, Monte Carlo)
- Conditional VaR (Expected Shortfall)
- Correlation-aware position allocation
- Sector exposure limits
- Portfolio Sharpe / Sortino / Calmar
- Stress testing (scenario analysis)
- Kelly-optimal portfolio weights

Referensi:
- Markowitz Modern Portfolio Theory
- RiskMetrics (JPMorgan) — VaR methodology
- Kelly Criterion for portfolio sizing
- OJK / BEI risk management guidelines
"""

import numpy as np
import pandas as pd
from typing import Dict, List, Optional
from dataclasses import dataclass, field
from scipy import stats


@dataclass
class PortfolioRiskReport:
    """Complete portfolio risk assessment."""
    portfolio_var_95: float = 0.0
    portfolio_var_99: float = 0.0
    portfolio_cvar_95: float = 0.0
    portfolio_cvar_99: float = 0.0
    portfolio_volatility: float = 0.0
    portfolio_sharpe: float = 0.0
    portfolio_sortino: float = 0.0
    portfolio_max_drawdown: float = 0.0
    correlation_matrix: Optional[pd.DataFrame] = None
    sector_exposures: Dict[str, float] = field(default_factory=dict)
    concentration_risk: float = 0.0  # Herfindahl index
    recommendations: List[str] = field(default_factory=list)


def calculate_portfolio_var(
    weights: np.ndarray,
    returns: pd.DataFrame,
    confidence: float = 0.95,
    method: str = "historical",
    position_value: float = 100_000_000,
) -> Dict:
    """
    Calculate portfolio VaR using multiple methods.

    Args:
        weights: Array of portfolio weights (sum to 1)
        returns: DataFrame of asset returns (columns = assets)
        confidence: 0.95 or 0.99
        method: "historical", "parametric", "monte_carlo"
        position_value: Total portfolio value in IDR
    """
    portfolio_returns = (returns * weights).sum(axis=1)

    if method == "historical":
        var_pct = np.percentile(portfolio_returns, (1 - confidence) * 100)
        cvar_pct = portfolio_returns[portfolio_returns <= var_pct].mean()

    elif method == "parametric":
        mean = portfolio_returns.mean()
        std = portfolio_returns.std()
        z = stats.norm.ppf(1 - confidence)
        var_pct = mean + z * std
        cvar_pct = mean - stats.norm.pdf(z) / (1 - confidence) * std

    elif method == "monte_carlo":
        mean = portfolio_returns.mean()
        cov = returns.cov()
        n_sims = 10000
        sim_returns = np.random.multivariate_normal(
            mean, cov, n_sims
        )
        port_sims = sim_returns @ weights
        var_pct = np.percentile(port_sims, (1 - confidence) * 100)
        cvar_pct = port_sims[port_sims <= var_pct].mean()

    else:
        raise ValueError(f"Unknown method: {method}")

    return {
        "method": method,
        "confidence": confidence,
        "var_percent": round(float(var_pct), 4),
        "var_value": round(float(var_pct * position_value), 0),
        "cvar_percent": round(float(cvar_pct), 4),
        "cvar_value": round(float(cvar_pct * position_value), 0),
        "portfolio_volatility": round(float(portfolio_returns.std() * np.sqrt(252)), 4),
        "position_value": position_value,
    }


def correlation_aware_allocation(
    returns: pd.DataFrame,
    target_return: float = 0.10,
    max_weight: float = 0.30,
    min_weight: float = 0.05,
    risk_free_rate: float = 0.05,
) -> Dict:
    """
    Optimize portfolio weights using mean-variance optimization
    with correlation awareness.

    Uses analytical solution for minimum-variance portfolio
    with constraints.
    """
    n_assets = len(returns.columns)
    mean_returns = returns.mean() * 252
    cov_matrix = returns.cov() * 252

    # Minimum variance portfolio (analytical)
    try:
        inv_cov = np.linalg.pinv(cov_matrix.values)
        ones = np.ones(n_assets)
        min_var_weights = inv_cov @ ones / (ones @ inv_cov @ ones)
    except np.linalg.LinAlgError:
        min_var_weights = np.ones(n_assets) / n_assets

    # Apply constraints
    weights = np.clip(min_var_weights, min_weight, max_weight)
    weights = weights / weights.sum()

    # Calculate portfolio metrics
    port_return = float(weights @ mean_returns)
    port_vol = float(np.sqrt(weights @ cov_matrix.values @ weights))
    sharpe = (port_return - risk_free_rate) / port_vol if port_vol > 0 else 0

    # Correlation analysis
    corr = returns.corr()
    avg_correlation = float(corr.values[np.triu_indices(n_assets, k=1)].mean())
    max_correlation = float(corr.values[np.triu_indices(n_assets, k=1)].max())

    # Diversification ratio
    avg_vol = returns.std().values * np.sqrt(252)
    div_ratio = float((weights @ avg_vol) / port_vol) if port_vol > 0 else 1.0

    return {
        "weights": dict(zip(returns.columns, [round(w, 4) for w in weights])),
        "expected_return": round(port_return, 4),
        "portfolio_volatility": round(port_vol, 4),
        "sharpe_ratio": round(sharpe, 4),
        "avg_correlation": round(avg_correlation, 4),
        "max_correlation": round(max_correlation, 4),
        "diversification_ratio": round(div_ratio, 4),
        "method": "Minimum Variance with constraints",
    }


def check_sector_exposure(
    weights: Dict[str, float],
    sector_map: Dict[str, str],
    sector_limits: Optional[Dict[str, float]] = None,
) -> Dict:
    """
    Check sector exposure against limits.

    Args:
        weights: Dict of ticker -> weight
        sector_map: Dict of ticker -> sector name
        sector_limits: Dict of sector -> max weight (default 30%)
    """
    if sector_limits is None:
        sector_limits = {
            "Financials": 0.35,
            "Energy": 0.25,
            "Consumer": 0.25,
            "Technology": 0.20,
            "Industrial": 0.20,
            "Healthcare": 0.15,
            "Other": 0.30,
        }

    sector_weights = {}
    for ticker, weight in weights.items():
        sector = sector_map.get(ticker, "Other")
        sector_weights[sector] = sector_weights.get(sector, 0) + weight

    violations = []
    for sector, weight in sector_weights.items():
        limit = sector_limits.get(sector, 0.30)
        if weight > limit:
            violations.append(f"{sector}: {weight:.1%} exceeds limit {limit:.1%}")

    # Concentration risk (Herfindahl index)
    hhi = sum(w ** 2 for w in sector_weights.values())

    return {
        "sector_weights": {k: round(v, 4) for k, v in sector_weights.items()},
        "sector_limits": sector_limits,
        "violations": violations,
        "concentration_hhi": round(hhi, 4),
        "is_diversified": hhi < 0.25,
        "max_sector_exposure": max(sector_weights.values()) if sector_weights else 0,
    }


def stress_test_portfolio(
    weights: np.ndarray,
    returns: pd.DataFrame,
    scenarios: Optional[List[dict]] = None,
    position_value: float = 100_000_000,
) -> Dict:
    """
    Stress test portfolio under various scenarios.

    Scenarios:
    - 2008 Financial Crisis: -40% market drop
    - COVID-19 Crash: -35% in 1 month
    - Asian Financial Crisis 1997: -65% IDX
    - Rate hike shock: -15%
    - Currency crisis: IDR devaluation 20%
    """
    if scenarios is None:
        scenarios = [
            {"name": "2008 Financial Crisis", "market_shock": -0.40, "vol_multiplier": 2.5},
            {"name": "COVID-19 Crash", "market_shock": -0.35, "vol_multiplier": 2.0},
            {"name": "Asian Crisis 1997", "market_shock": -0.65, "vol_multiplier": 3.0},
            {"name": "Rate Hike Shock", "market_shock": -0.15, "vol_multiplier": 1.5},
            {"name": "Currency Crisis (IDR -20%)", "market_shock": -0.20, "vol_multiplier": 1.8},
            {"name": "Mild Correction", "market_shock": -0.10, "vol_multiplier": 1.3},
        ]

    results = []
    portfolio_returns = (returns * weights).sum(axis=1)
    base_vol = portfolio_returns.std()

    for scenario in scenarios:
        shock = scenario["market_shock"]
        vol_mult = scenario["vol_multiplier"]

        # Apply shock: portfolio loses proportional to beta * market_shock
        stressed_return = shock * 0.8  # Assume portfolio beta ~0.8
        stressed_vol = base_vol * vol_mult

        # VaR under stress
        stressed_var_95 = stressed_return - 1.645 * stressed_vol
        stressed_var_99 = stressed_return - 2.326 * stressed_vol

        # Portfolio value impact
        value_impact = stressed_return * position_value

        results.append({
            "scenario": scenario["name"],
            "market_shock": f"{shock:.0%}",
            "portfolio_impact_pct": round(stressed_return, 4),
            "portfolio_impact_value": round(value_impact, 0),
            "stressed_var_95": round(float(stressed_var_95), 4),
            "stressed_var_99": round(float(stressed_var_99), 4),
            "vol_multiplier": vol_mult,
        })

    return {
        "scenarios": results,
        "worst_case": min(results, key=lambda x: x["portfolio_impact_pct"]),
        "position_value": position_value,
    }


def kelly_portfolio_weights(
    returns: pd.DataFrame,
    max_weight: float = 0.25,
    risk_free_rate: float = 0.05,
) -> Dict:
    """
    Calculate Kelly-optimal portfolio weights.

    Kelly criterion for multi-asset: w* = (mu - rf) / Sigma
    with constraints (max weight per asset).
    """
    mean_returns = returns.mean() * 252
    excess_returns = mean_returns - risk_free_rate
    cov_matrix = returns.cov() * 252

    try:
        kelly_weights = np.linalg.solve(cov_matrix, excess_returns)
    except np.linalg.LinAlgError:
        kelly_weights = excess_returns / np.diag(cov_matrix)

    # Clip to reasonable range
    kelly_weights = np.clip(kelly_weights, 0, max_weight)
    total = kelly_weights.sum()
    if total > 0:
        kelly_weights = kelly_weights / total
    else:
        kelly_weights = np.ones(len(returns.columns)) / len(returns.columns)

    # Half-Kelly for safety
    half_kelly = kelly_weights * 0.5

    port_return = float(kelly_weights @ mean_returns)
    port_vol = float(np.sqrt(kelly_weights @ cov_matrix.values @ kelly_weights))

    return {
        "kelly_weights": dict(zip(returns.columns, [round(w, 4) for w in kelly_weights])),
        "half_kelly_weights": dict(zip(returns.columns, [round(w, 4) for w in half_kelly])),
        "expected_return": round(port_return, 4),
        "portfolio_volatility": round(port_vol, 4),
        "sharpe_ratio": round((port_return - risk_free_rate) / port_vol, 4) if port_vol > 0 else 0,
        "recommendation": "Use Half-Kelly for risk management",
    }


def full_portfolio_risk_report(
    weights: np.ndarray,
    returns: pd.DataFrame,
    sector_map: Optional[Dict[str, str]] = None,
    position_value: float = 100_000_000,
) -> PortfolioRiskReport:
    """Generate complete portfolio risk report in one call."""
    portfolio_returns = (returns * weights).sum(axis=1)

    # VaR
    var_95 = calculate_portfolio_var(weights, returns, 0.95, "historical", position_value)
    var_99 = calculate_portfolio_var(weights, returns, 0.99, "historical", position_value)

    # Sharpe
    ann_return = portfolio_returns.mean() * 252
    ann_vol = portfolio_returns.std() * np.sqrt(252)
    sharpe = (ann_return - 0.05) / ann_vol if ann_vol > 0 else 0

    # Sortino
    downside = portfolio_returns[portfolio_returns < 0]
    sortino = (ann_return - 0.05) / (downside.std() * np.sqrt(252)) if len(downside) > 0 and downside.std() > 0 else 0

    # Max drawdown
    cum = (1 + portfolio_returns).cumprod()
    max_dd = float(((cum / cum.cummax()) - 1).min())

    # Correlation
    corr = returns.corr()

    # Concentration (HHI)
    hhi = float(np.sum(weights ** 2))

    # Sector exposure
    sector_exp = {}
    if sector_map:
        for i, col in enumerate(returns.columns):
            sector = sector_map.get(col, "Other")
            sector_exp[sector] = sector_exp.get(sector, 0) + weights[i]

    # Recommendations
    recommendations = []
    if hhi > 0.25:
        recommendations.append("High concentration risk — diversify holdings")
    if max_dd < -0.20:
        recommendations.append(f"Large max drawdown ({max_dd:.1%}) — consider hedging")
    if sharpe < 0.5:
        recommendations.append(f"Low Sharpe ({sharpe:.2f}) — review strategy")
    if not recommendations:
        recommendations.append("Portfolio risk within acceptable parameters")

    return PortfolioRiskReport(
        portfolio_var_95=var_95["var_percent"],
        portfolio_var_99=var_99["var_percent"],
        portfolio_cvar_95=var_95["cvar_percent"],
        portfolio_cvar_99=var_99["cvar_percent"],
        portfolio_volatility=round(ann_vol, 4),
        portfolio_sharpe=round(sharpe, 4),
        portfolio_sortino=round(sortino, 4),
        portfolio_max_drawdown=round(max_dd, 4),
        correlation_matrix=corr,
        sector_exposures={k: round(v, 4) for k, v in sector_exp.items()},
        concentration_risk=round(hhi, 4),
        recommendations=recommendations,
    )
