"""
Alphalens-style factor analysis.

Replicates key functionality of Quantopian's Alphalens library:
- Factor quantile analysis
- IC (Information Coefficient) by forward period
- Factor returns by quantile
- Turnover analysis
- Cumulative return by quantile

References:
- Quantopian Alphalens: https://github.com/quantopian/alphalens
- "What Happened to Quantopian?" — community maintained forks
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple

import numpy as np
import pandas as pd

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class FactorAnalysisResult:
    """Complete factor analysis result."""
    ic_mean: float = 0.0
    ic_std: float = 0.0
    ic_sharpe: float = 0.0
    rank_ic_mean: float = 0.0
    rank_ic_std: float = 0.0
    rank_ic_sharpe: float = 0.0
    quantile_returns: Dict[int, Dict] = field(default_factory=dict)
    turnover: float = 0.0
    autocorrelation: float = 0.0
    n_signals: int = 0
    n_assets: int = 0
    long_short_return: float = 0.0
    long_short_sharpe: float = 0.0
    factor_data: Optional[pd.DataFrame] = None


# =============================================================================
# CORE FUNCTIONS
# =============================================================================


def compute_forward_returns(
    prices: pd.DataFrame,
    periods: List[int] = (1, 5, 10, 21),
) -> pd.DataFrame:
    """
    Compute forward returns for multiple periods.

    Args:
        prices: DataFrame with datetime index, columns = tickers, values = prices
        periods: Forward return periods in days

    Returns:
        Multi-index DataFrame: (datetime, ticker) x periods
    """
    returns = pd.DataFrame()
    for period in periods:
        fwd_ret = prices.pct_change(period).shift(-period)
        col_name = f"period_{period}"
        returns[col_name] = fwd_ret.stack()

    returns.index.names = ["date", "asset"]
    return returns


def compute_factor_quantiles(
    factor: pd.Series,
    n_quantiles: int = 5,
) -> pd.Series:
    """
    Compute factor quantile for each observation.

    Args:
        factor: Series with MultiIndex (date, asset) and factor values
        n_quantiles: Number of quantile bins

    Returns:
        Series with quantile labels (1 to n_quantiles)
    """
    def _quantile_group(group):
        try:
            return pd.qcut(group, n_quantiles, labels=False, duplicates="drop") + 1
        except Exception:
            return pd.Series(1, index=group.index)

    quantiles = factor.groupby(level=0, group_keys=False).apply(_quantile_group)
    return quantiles


def compute_ic(
    factor: pd.Series,
    forward_returns: pd.Series,
    period: str = "period_1",
) -> Tuple[pd.Series, pd.Series]:
    """
    Compute Information Coefficient (Spearman rank correlation)
    and Pearson IC between factor and forward returns.

    Returns:
        (rank_ic_series, pearson_ic_series) indexed by date
    """
    from scipy.stats import spearmanr, pearsonr

    # Align
    aligned = pd.DataFrame({"factor": factor, "fwd_ret": forward_returns}).dropna()

    rank_ics = {}
    pearson_ics = {}

    for date, group in aligned.groupby(level=0):
        if len(group) < 5:
            continue
        f = group["factor"].values
        r = group["fwd_ret"].values

        try:
            rank_ic, _ = spearmanr(f, r)
            pearson_ic, _ = pearsonr(f, r)
            rank_ics[date] = rank_ic
            pearson_ics[date] = pearson_ic
        except Exception:
            continue

    rank_ic_series = pd.Series(rank_ics)
    pearson_ic_series = pd.Series(pearson_ics)

    return rank_ic_series, pearson_ic_series


def compute_quantile_returns(
    factor: pd.Series,
    forward_returns: pd.Series,
    n_quantiles: int = 5,
    period: str = "period_1",
) -> Dict[int, Dict]:
    """
    Compute average returns by factor quantile.

    Returns:
        Dict mapping quantile -> {mean_return, std_return, sharpe, count}
    """
    quantiles = compute_factor_quantiles(factor, n_quantiles)

    aligned = pd.DataFrame({
        "factor": factor,
        "fwd_ret": forward_returns,
        "quantile": quantiles,
    }).dropna()

    results = {}
    for q in range(1, n_quantiles + 1):
        q_data = aligned[aligned["quantile"] == q]["fwd_ret"]
        if len(q_data) > 0:
            mean_ret = float(q_data.mean())
            std_ret = float(q_data.std())
            sharpe = mean_ret / (std_ret + 1e-10) * np.sqrt(252) if std_ret > 0 else 0
            results[q] = {
                "mean_return": mean_ret,
                "std_return": std_ret,
                "sharpe": sharpe,
                "count": len(q_data),
            }
        else:
            results[q] = {"mean_return": 0, "std_return": 0, "sharpe": 0, "count": 0}

    return results


def compute_turnover(factor: pd.Series) -> float:
    """
    Compute factor turnover (average change in portfolio composition).

    Returns:
        Average turnover (0-1)
    """
    # Reshape: date x asset
    factor_df = factor.unstack()
    # Rank each day
    ranks = factor_df.rank(axis=1, pct=True)
    # Turnover = average absolute change in ranks
    rank_diff = ranks.diff().abs()
    turnover = float(rank_diff.mean().mean()) if not rank_diff.empty else 0
    return turnover


def compute_factor_autocorrelation(factor: pd.Series, lag: int = 1) -> float:
    """
    Compute factor autocorrelation (stability of signal).

    Returns:
        Autocorrelation coefficient
    """
    factor_df = factor.unstack()
    autocorrs = []
    for col in factor_df.columns:
        s = factor_df[col].dropna()
        if len(s) > 1:
            ac = s.autocorr(lag)
            if not np.isnan(ac):
                autocorrs.append(ac)
    return float(np.mean(autocorrs)) if autocorrs else 0


# =============================================================================
# MAIN API
# =============================================================================


def run_factor_analysis(
    factor: pd.Series,
    prices: pd.DataFrame,
    periods: List[int] = (1, 5, 10, 21),
    n_quantiles: int = 5,
    quantile_period: str = "period_5",
) -> FactorAnalysisResult:
    """
    Run complete Alphalens-style factor analysis.

    Args:
        factor: Series with MultiIndex (date, asset) and factor values
        prices: DataFrame with datetime index, columns = tickers, values = prices
        periods: Forward return periods
        n_quantiles: Number of quantiles
        quantile_period: Which period to use for quantile returns

    Returns:
        FactorAnalysisResult with all metrics
    """
    # Compute forward returns
    fwd_returns = compute_forward_returns(prices, periods)

    # Align factor with forward returns
    aligned_factor = factor.reindex(fwd_returns.index)

    # IC analysis for period_1
    rank_ic, pearson_ic = compute_ic(aligned_factor, fwd_returns["period_1"], "period_1")

    # Quantile returns
    q_returns = compute_quantile_returns(
        aligned_factor, fwd_returns[quantile_period], n_quantiles, quantile_period,
    )

    # Turnover
    turnover = compute_turnover(aligned_factor)

    # Autocorrelation
    autocorr = compute_factor_autocorrelation(aligned_factor)

    # Long-short return
    if 1 in q_returns and n_quantiles in q_returns:
        ls_return = q_returns[n_quantiles]["mean_return"] - q_returns[1]["mean_return"]
        ls_std = np.sqrt(
            q_returns[1]["std_return"] ** 2 + q_returns[n_quantiles]["std_return"] ** 2
        )
        ls_sharpe = ls_return / (ls_std + 1e-10) * np.sqrt(252) if ls_std > 0 else 0
    else:
        ls_return = 0
        ls_sharpe = 0

    # IC Sharpe
    ic_mean = float(pearson_ic.mean()) if len(pearson_ic) > 0 else 0
    ic_std = float(pearson_ic.std()) if len(pearson_ic) > 0 else 0
    ic_sharpe = ic_mean / (ic_std + 1e-10) * np.sqrt(252) if ic_std > 0 else 0

    rank_ic_mean = float(rank_ic.mean()) if len(rank_ic) > 0 else 0
    rank_ic_std = float(rank_ic.std()) if len(rank_ic) > 0 else 0
    rank_ic_sharpe = rank_ic_mean / (rank_ic_std + 1e-10) * np.sqrt(252) if rank_ic_std > 0 else 0

    # Build factor data for reference
    factor_data = pd.DataFrame({
        "factor": aligned_factor,
        **{f"period_{p}": fwd_returns[f"period_{p}"] for p in periods},
    })

    return FactorAnalysisResult(
        ic_mean=ic_mean,
        ic_std=ic_std,
        ic_sharpe=ic_sharpe,
        rank_ic_mean=rank_ic_mean,
        rank_ic_std=rank_ic_std,
        rank_ic_sharpe=rank_ic_sharpe,
        quantile_returns=q_returns,
        turnover=turnover,
        autocorrelation=autocorr,
        n_signals=len(aligned_factor.dropna()),
        n_assets=factor.unstack().shape[1] if not factor.empty else 0,
        long_short_return=float(ls_return),
        long_short_sharpe=float(ls_sharpe),
        factor_data=factor_data,
    )


def create_tear_sheet(result: FactorAnalysisResult) -> Dict:
    """
    Create a summary tear sheet for factor analysis.

    Returns:
        Dict with human-readable summary
    """
    return {
        "summary": {
            "IC Mean": f"{result.ic_mean:.4f}",
            "IC Std": f"{result.ic_std:.4f}",
            "IC Sharpe": f"{result.ic_sharpe:.2f}",
            "Rank IC Mean": f"{result.rank_ic_mean:.4f}",
            "Rank IC Sharpe": f"{result.rank_ic_sharpe:.2f}",
            "Turnover": f"{result.turnover:.4f}",
            "Autocorrelation": f"{result.autocorrelation:.4f}",
            "N Signals": result.n_signals,
            "N Assets": result.n_assets,
        },
        "quantile_returns": {
            f"Q{q}": {
                "Mean Return": f"{data['mean_return']:.4f}",
                "Sharpe": f"{data['sharpe']:.2f}",
                "Count": data["count"],
            }
            for q, data in result.quantile_returns.items()
        },
        "long_short": {
            "Return": f"{result.long_short_return:.4f}",
            "Sharpe": f"{result.long_short_sharpe:.2f}",
        },
        "assessment": _assess_factor(result),
    }


def _assess_factor(result: FactorAnalysisResult) -> str:
    """Assess factor quality based on metrics."""
    score = 0
    reasons = []

    if abs(result.rank_ic_mean) > 0.03:
        score += 1
        reasons.append("Meaningful IC")
    if result.rank_ic_sharpe > 0.5:
        score += 1
        reasons.append("Good IC Sharpe")
    if result.autocorrelation > 0.6:
        score += 1
        reasons.append("Stable signal (high autocorrelation)")
    if result.turnover < 0.3:
        score += 1
        reasons.append("Low turnover (cost-efficient)")
    if abs(result.long_short_sharpe) > 0.5:
        score += 1
        reasons.append("Profitable long-short spread")

    if score >= 4:
        grade = "A (Excellent)"
    elif score >= 3:
        grade = "B (Good)"
    elif score >= 2:
        grade = "C (Moderate)"
    elif score >= 1:
        grade = "D (Weak)"
    else:
        grade = "F (No edge)"

    return f"{grade} — {', '.join(reasons) if reasons else 'No strong signals'}"
