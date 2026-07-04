"""
A/B testing framework for trading models.

Compares two or more models on the same dataset using:
- Paired t-test on returns
- Diebold-Mariano test for forecast accuracy
- Sharpe ratio comparison
- Cumulative return comparison
- Drawdown comparison

Reference:
- Diebold & Mariano (1995), "Comparing Predictive Accuracy"
- Bailey & Lopez de Prado (2012), "The Sharpe Ratio Efficient Frontier"
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import Dict, Optional, Tuple

import numpy as np
from scipy import stats

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class ModelPerformance:
    """Performance metrics for a single model."""
    name: str = ""
    predictions: Optional[np.ndarray] = None
    actual: Optional[np.ndarray] = None
    returns: Optional[np.ndarray] = None
    mse: float = 0.0
    mae: float = 0.0
    directional_accuracy: float = 0.0
    sharpe_ratio: float = 0.0
    max_drawdown: float = 0.0
    cumulative_return: float = 0.0
    win_rate: float = 0.0
    profit_factor: float = 0.0
    ic: float = 0.0  # Information Coefficient


@dataclass
class ABTestResult:
    """A/B test comparison result."""
    model_a: str = ""
    model_b: str = ""
    winner: str = ""
    confidence: float = 0.0
    p_value: float = 0.0
    metric_used: str = ""
    performance_a: Optional[ModelPerformance] = None
    performance_b: Optional[ModelPerformance] = None
    improvement: float = 0.0
    significant: bool = False
    details: Dict = field(default_factory=dict)


# =============================================================================
# PERFORMANCE COMPUTATION
# =============================================================================


def compute_model_performance(
    name: str,
    predictions: np.ndarray,
    actual: np.ndarray,
) -> ModelPerformance:
    """
    Compute comprehensive performance metrics for a model.

    Args:
        name: Model name
        predictions: Predicted values
        actual: Actual values

    Returns:
        ModelPerformance with all metrics
    """
    pred = np.array(predictions, dtype=float)
    act = np.array(actual, dtype=float)

    if len(pred) != len(act) or len(pred) < 2:
        return ModelPerformance(name=name)

    # Error metrics
    mse = float(np.mean((pred - act) ** 2))
    mae = float(np.mean(np.abs(pred - act)))

    # Directional accuracy
    if len(pred) > 1:
        pred_dir = np.diff(pred) > 0
        actual_dir = np.diff(act) > 0
        dir_acc = float(np.mean(pred_dir == actual_dir))
    else:
        dir_acc = 0

    # Trading returns (go long when predicted up, short when down)
    if len(pred) > 1:
        positions = np.sign(np.diff(pred))
        actual_returns = np.diff(act) / act[:-1]
        strategy_returns = positions * actual_returns
    else:
        strategy_returns = np.array([0])

    # Sharpe ratio
    if np.std(strategy_returns) > 0:
        sharpe = float(np.mean(strategy_returns) / np.std(strategy_returns) * np.sqrt(252))
    else:
        sharpe = 0

    # Cumulative return
    cum_return = float(np.prod(1 + strategy_returns) - 1)

    # Max drawdown
    equity = np.cumprod(1 + strategy_returns)
    peak = np.maximum.accumulate(equity)
    drawdown = (equity / peak - 1)
    max_dd = float(np.min(drawdown)) if len(drawdown) > 0 else 0

    # Win rate
    wins = strategy_returns[strategy_returns > 0]
    losses = strategy_returns[strategy_returns < 0]
    win_rate = float(len(wins) / len(strategy_returns)) if len(strategy_returns) > 0 else 0

    # Profit factor
    gross_profit = float(np.sum(wins))
    gross_loss = float(abs(np.sum(losses)))
    profit_factor = gross_profit / (gross_loss + 1e-10) if gross_loss > 0 else float("inf")

    # Information Coefficient (rank correlation)
    if len(pred) > 5:
        ic, _ = stats.spearmanr(pred, act)
        ic = float(ic) if not np.isnan(ic) else 0
    else:
        ic = 0

    return ModelPerformance(
        name=name,
        predictions=pred,
        actual=act,
        returns=strategy_returns,
        mse=mse,
        mae=mae,
        directional_accuracy=dir_acc,
        sharpe_ratio=sharpe,
        max_drawdown=max_dd,
        cumulative_return=cum_return,
        win_rate=win_rate,
        profit_factor=profit_factor,
        ic=ic,
    )


# =============================================================================
# STATISTICAL TESTS
# =============================================================================


def paired_t_test(returns_a: np.ndarray, returns_b: np.ndarray) -> Tuple[float, float]:
    """
    Paired t-test on strategy returns.

    Returns:
        (t_statistic, p_value)
    """
    diff = returns_a - returns_b
    if len(diff) < 2 or np.std(diff) == 0:
        return 0, 1.0
    t_stat, p_value = stats.ttest_rel(returns_a, returns_b)
    return float(t_stat), float(p_value)


def diebold_mariano_test(
    errors_a: np.ndarray,
    errors_b: np.ndarray,
    loss_fn: str = "mse",
) -> Tuple[float, float]:
    """
    Diebold-Mariano test for forecast accuracy comparison.

    H0: Equal predictive accuracy
    HA: Different predictive accuracy

    Returns:
        (dm_statistic, p_value)
    """
    if loss_fn == "mse":
        loss_a = errors_a ** 2
        loss_b = errors_b ** 2
    elif loss_fn == "mae":
        loss_a = np.abs(errors_a)
        loss_b = np.abs(errors_b)
    else:
        loss_a = errors_a ** 2
        loss_b = errors_b ** 2

    d = loss_a - loss_b
    n = len(d)

    if n < 2:
        return 0, 1.0

    d_mean = np.mean(d)
    d_var = np.var(d, ddof=1)

    if d_var == 0:
        return 0, 1.0

    dm_stat = d_mean / np.sqrt(d_var / n)
    p_value = 2 * (1 - stats.norm.cdf(abs(dm_stat)))

    return float(dm_stat), float(p_value)


# =============================================================================
# A/B TEST
# =============================================================================


def run_ab_test(
    predictions_a: np.ndarray,
    predictions_b: np.ndarray,
    actual: np.ndarray,
    name_a: str = "Model A",
    name_b: str = "Model B",
    metric: str = "sharpe",
    alpha: float = 0.05,
) -> ABTestResult:
    """
    Run A/B test between two models.

    Args:
        predictions_a: Predictions from model A
        predictions_b: Predictions from model B
        actual: Actual values
        name_a: Name of model A
        name_b: Name of model B
        metric: Primary metric for comparison ('sharpe', 'mse', 'ic', 'dir_acc')
        alpha: Significance level

    Returns:
        ABTestResult with comparison
    """
    perf_a = compute_model_performance(name_a, predictions_a, actual)
    perf_b = compute_model_performance(name_b, predictions_b, actual)

    # Select metric for comparison
    metric_map = {
        "sharpe": ("sharpe_ratio", "higher"),
        "mse": ("mse", "lower"),
        "ic": ("ic", "higher"),
        "dir_acc": ("directional_accuracy", "higher"),
        "cum_return": ("cumulative_return", "higher"),
        "win_rate": ("win_rate", "higher"),
    }

    metric_attr, direction = metric_map.get(metric, ("sharpe_ratio", "higher"))
    val_a = getattr(perf_a, metric_attr)
    val_b = getattr(perf_b, metric_attr)

    # Statistical test
    if perf_a.returns is not None and perf_b.returns is not None and len(perf_a.returns) > 2:
        if metric in ("sharpe", "cum_return", "win_rate"):
            t_stat, p_value = paired_t_test(perf_a.returns, perf_b.returns)
        else:
            errors_a = predictions_a - actual
            errors_b = predictions_b - actual
            dm_stat, p_value = diebold_mariano_test(errors_a, errors_b)
    else:
        p_value = 1.0

    significant = p_value < alpha

    # Determine winner
    if direction == "higher":
        if val_a > val_b:
            winner = name_a
            improvement = float((val_a - val_b) / (abs(val_b) + 1e-10))
        else:
            winner = name_b
            improvement = float((val_b - val_a) / (abs(val_a) + 1e-10))
    else:
        if val_a < val_b:
            winner = name_a
            improvement = float((val_b - val_a) / (abs(val_b) + 1e-10))
        else:
            winner = name_b
            improvement = float((val_a - val_b) / (abs(val_a) + 1e-10))

    confidence = float(1 - p_value)

    return ABTestResult(
        model_a=name_a,
        model_b=name_b,
        winner=winner,
        confidence=confidence,
        p_value=float(p_value),
        metric_used=metric,
        performance_a=perf_a,
        performance_b=perf_b,
        improvement=improvement,
        significant=significant,
        details={
            "metric_value_a": val_a,
            "metric_value_b": val_b,
            "sharpe_a": perf_a.sharpe_ratio,
            "sharpe_b": perf_b.sharpe_ratio,
            "mse_a": perf_a.mse,
            "mse_b": perf_b.mse,
            "ic_a": perf_a.ic,
            "ic_b": perf_b.ic,
            "dir_acc_a": perf_a.directional_accuracy,
            "dir_acc_b": perf_b.directional_accuracy,
            "max_dd_a": perf_a.max_drawdown,
            "max_dd_b": perf_b.max_drawdown,
            "cum_return_a": perf_a.cumulative_return,
            "cum_return_b": perf_b.cumulative_return,
            "win_rate_a": perf_a.win_rate,
            "win_rate_b": perf_b.win_rate,
        },
    )


def run_multi_model_comparison(
    models: Dict[str, np.ndarray],
    actual: np.ndarray,
    metric: str = "sharpe",
) -> Dict:
    """
    Compare multiple models pairwise.

    Args:
        models: Dict of model_name -> predictions
        actual: Actual values
        metric: Comparison metric

    Returns:
        Dict with ranking and pairwise results
    """
    names = list(models.keys())
    performances = {}
    pairwise = []

    for name in names:
        performances[name] = compute_model_performance(name, models[name], actual)

    # Pairwise A/B tests
    for i in range(len(names)):
        for j in range(i + 1, len(names)):
            result = run_ab_test(
                models[names[i]], models[names[j]], actual,
                names[i], names[j], metric,
            )
            pairwise.append({
                "model_a": result.model_a,
                "model_b": result.model_b,
                "winner": result.winner,
                "p_value": result.p_value,
                "significant": result.significant,
                "improvement": result.improvement,
            })

    # Rank by metric
    metric_map = {
        "sharpe": "sharpe_ratio",
        "mse": "mse",
        "ic": "ic",
        "dir_acc": "directional_accuracy",
    }
    metric_attr = metric_map.get(metric, "sharpe_ratio")
    reverse = metric != "mse"

    ranking = sorted(
        [(name, getattr(perf, metric_attr)) for name, perf in performances.items()],
        key=lambda x: x[1],
        reverse=reverse,
    )

    return {
        "ranking": [{"model": name, "score": float(score)} for name, score in ranking],
        "best_model": ranking[0][0] if ranking else "",
        "pairwise_results": pairwise,
        "performances": {
            name: {
                "sharpe": perf.sharpe_ratio,
                "mse": perf.mse,
                "mae": perf.mae,
                "ic": perf.ic,
                "dir_acc": perf.directional_accuracy,
                "cum_return": perf.cumulative_return,
                "max_dd": perf.max_drawdown,
                "win_rate": perf.win_rate,
                "profit_factor": perf.profit_factor,
            }
            for name, perf in performances.items()
        },
    }
