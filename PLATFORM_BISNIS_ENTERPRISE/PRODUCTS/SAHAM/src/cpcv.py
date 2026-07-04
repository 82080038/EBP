"""
Combinatorial Purged Cross-Validation (CPCV).

Based on Lopez de Prado, "Advances in Financial Machine Learning" (2018), Chapter 12.
CPCV generates multiple backtest paths from combinatorial train/test splits,
providing a distribution of out-of-sample performance rather than a single path.

Key advantages over walk-forward:
- Generates C(N, k) backtest paths for robust performance estimation
- Every observation is used in both train and test across paths
- Purging + embargo prevent information leakage
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from itertools import combinations
from typing import Callable, Dict, List, Optional, Tuple

import numpy as np
import pandas as pd
from sklearn.base import BaseEstimator

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class CPCVPath:
    """A single CPCV backtest path."""
    path_id: int
    train_indices: List[int]
    test_indices: List[int]
    score: float = 0.0
    predictions: Optional[np.ndarray] = None
    actual: Optional[np.ndarray] = None


@dataclass
class CPCVResult:
    """Complete CPCV results."""
    n_paths: int = 0
    paths: List[CPCVPath] = field(default_factory=list)
    mean_score: float = 0.0
    std_score: float = 0.0
    median_score: float = 0.0
    min_score: float = 0.0
    max_score: float = 0.0
    pbo: float = 0.0  # Probability of Backtest Overfitting
    score_distribution: List[float] = field(default_factory=list)
    n_train_groups: int = 0
    n_test_groups: int = 0


# =============================================================================
# CPCV IMPLEMENTATION
# =============================================================================


def cpcv_split(
    n_samples: int,
    n_groups: int = 6,
    n_test_groups: int = 2,
    purge_days: int = 5,
    embargo_days: int = 3,
) -> List[Tuple[List[int], List[int]]]:
    """
    Generate CPCV train/test splits.

    Args:
        n_samples: Total number of samples
        n_groups: Number of groups to divide data into
        n_test_groups: Number of groups reserved for testing per path
        purge_days: Number of samples to purge around train/test boundary
        embargo_days: Number of samples to embargo after test set

    Returns:
        List of (train_indices, test_indices) tuples
    """
    if n_groups < 2:
        raise ValueError("n_groups must be >= 2")
    if n_test_groups >= n_groups:
        raise ValueError("n_test_groups must be < n_groups")

    # Divide data into groups
    group_size = n_samples // n_groups
    groups: List[List[int]] = []
    for i in range(n_groups):
        start = i * group_size
        end = (i + 1) * group_size if i < n_groups - 1 else n_samples
        groups.append(list(range(start, end)))

    # Generate all combinations of test groups
    test_combos = list(combinations(range(n_groups), n_test_groups))
    splits = []

    for test_group_ids in test_combos:
        test_indices = []
        train_indices = []

        for g_id in range(n_groups):
            if g_id in test_group_ids:
                test_indices.extend(groups[g_id])
            else:
                train_indices.extend(groups[g_id])

        # Apply purging: remove samples near train/test boundary
        test_set = set(test_indices)
        train_set = set(train_indices)

        # Purge samples within purge_days of any test index
        purge_set = set()
        for t_idx in test_indices:
            for d in range(1, purge_days + 1):
                if t_idx - d >= 0:
                    purge_set.add(t_idx - d)
                if t_idx + d < n_samples:
                    purge_set.add(t_idx + d)

        # Embargo: remove samples after last test group
        max_test_idx = max(test_indices)
        for d in range(1, embargo_days + 1):
            if max_test_idx + d < n_samples:
                purge_set.add(max_test_idx + d)

        train_set -= purge_set
        train_indices = sorted(train_set)
        test_indices = sorted(test_set)

        if train_indices and test_indices:
            splits.append((train_indices, test_indices))

    return splits


def cpcv_evaluate(
    X: pd.DataFrame,
    y: pd.Series,
    model_fn: Callable[..., BaseEstimator],
    n_groups: int = 6,
    n_test_groups: int = 2,
    purge_days: int = 5,
    embargo_days: int = 3,
    scoring: str = "r2",
    **model_kwargs,
) -> CPCVResult:
    """
    Run CPCV cross-validation with a model.

    Args:
        X: Feature DataFrame
        y: Target Series
        model_fn: Function that returns a fitted model (e.g., RandomForestRegressor)
        n_groups: Number of groups
        n_test_groups: Number of test groups per path
        purge_days: Purge window
        embargo_days: Embargo window
        scoring: Scoring metric ('r2', 'mse', 'mae', 'sharpe')
        **model_kwargs: Additional arguments for model_fn

    Returns:
        CPCVResult with all paths and aggregate statistics
    """
    n_samples = len(X)
    splits = cpcv_split(n_samples, n_groups, n_test_groups, purge_days, embargo_days)

    result = CPCVResult(
        n_paths=len(splits),
        n_train_groups=n_groups - n_test_groups,
        n_test_groups=n_test_groups,
    )

    scores = []
    is_scores = []  # In-sample scores for PBO
    oos_scores = []  # Out-of-sample scores for PBO

    for path_id, (train_idx, test_idx) in enumerate(splits):
        try:
            X_train = X.iloc[train_idx]
            y_train = y.iloc[train_idx]
            X_test = X.iloc[test_idx]
            y_test = y.iloc[test_idx]

            if len(X_train) < 10 or len(X_test) < 2:
                continue

            model = model_fn(**model_kwargs)
            model.fit(X_train, y_train)

            # OOS prediction
            pred = model.predict(X_test)
            oos_score = _compute_score(y_test.values, pred, scoring)

            # IS prediction (for PBO)
            is_pred = model.predict(X_train)
            is_score = _compute_score(y_train.values, is_pred, scoring)

            path = CPCVPath(
                path_id=path_id,
                train_indices=train_idx,
                test_indices=test_idx,
                score=oos_score,
                predictions=pred,
                actual=y_test.values,
            )
            result.paths.append(path)
            scores.append(oos_score)
            is_scores.append(is_score)
            oos_scores.append(oos_score)

        except Exception as e:
            logger.warning(f"CPCV path {path_id} failed: {e}")
            continue

    if scores:
        result.score_distribution = scores
        result.mean_score = float(np.mean(scores))
        result.std_score = float(np.std(scores))
        result.median_score = float(np.median(scores))
        result.min_score = float(np.min(scores))
        result.max_score = float(np.max(scores))

        # Calculate PBO
        if is_scores and oos_scores:
            result.pbo = _calculate_pbo(is_scores, oos_scores)

    return result


def _compute_score(actual: np.ndarray, pred: np.ndarray, scoring: str) -> float:
    """Compute scoring metric."""
    if scoring == "r2":
        ss_res = np.sum((actual - pred) ** 2)
        ss_tot = np.sum((actual - actual.mean()) ** 2)
        return float(1 - ss_res / (ss_tot + 1e-10))
    elif scoring == "mse":
        return float(-np.mean((actual - pred) ** 2))  # Negative so higher is better
    elif scoring == "mae":
        return float(-np.mean(np.abs(actual - pred)))
    elif scoring == "sharpe":
        returns = np.diff(pred) / pred[:-1] if len(pred) > 1 else np.array([0])
        if np.std(returns) > 0:
            return float(np.mean(returns) / np.std(returns) * np.sqrt(252))
        return 0.0
    else:
        return float(1 - np.mean((actual - pred) ** 2) / (np.var(actual) + 1e-10))


def _calculate_pbo(is_scores: List[float], oos_scores: List[float]) -> float:
    """
    Calculate Probability of Backtest Overfitting (PBO).

    PBO = probability that the best in-sample strategy ranks below median out-of-sample.
    """
    is_arr = np.array(is_scores)
    oos_arr = np.array(oos_scores)

    # Rank OOS scores
    oos_ranks = np.argsort(np.argsort(oos_arr))
    median_rank = len(oos_arr) / 2

    # Find best IS strategy
    best_is_idx = np.argmax(is_arr)

    # PBO = probability that best IS strategy has OOS rank below median
    if oos_ranks[best_is_idx] < median_rank:
        return 1.0
    else:
        # Use logistic transition for smoother PBO
        n = len(oos_arr)
        pbo_count = sum(1 for i in range(n) if is_arr[i] == np.max(is_arr) and oos_ranks[i] < median_rank)
        return pbo_count / max(n, 1)


def cpcv_backtest_paths(
    strategy_returns: pd.Series,
    n_groups: int = 8,
    n_test_groups: int = 2,
    purge_days: int = 5,
    embargo_days: int = 3,
) -> Dict:
    """
    Generate multiple backtest equity curves from CPCV splits.

    Args:
        strategy_returns: Daily returns series from a strategy
        n_groups: Number of groups
        n_test_groups: Number of test groups
        purge_days: Purge window
        embargo_days: Embargo window

    Returns:
        Dict with paths, statistics, and PBO
    """
    n = len(strategy_returns)
    splits = cpcv_split(n, n_groups, n_test_groups, purge_days, embargo_days)

    paths = []
    path_returns = []

    for path_id, (train_idx, test_idx) in enumerate(splits):
        test_returns = strategy_returns.iloc[test_idx]
        if len(test_returns) < 2:
            continue

        # Build equity curve
        equity = (1 + test_returns).cumprod()
        path_sharpe = float(test_returns.mean() / (test_returns.std() + 1e-10) * np.sqrt(252))
        path_max_dd = float((equity / equity.cummax() - 1).min())

        paths.append({
            "path_id": path_id,
            "n_days": len(test_returns),
            "total_return": float(equity.iloc[-1] - 1),
            "sharpe": path_sharpe,
            "max_drawdown": path_max_dd,
            "equity_curve": equity.values,
        })
        path_returns.append(path_sharpe)

    if path_returns:
        stats = {
            "n_paths": len(paths),
            "mean_sharpe": float(np.mean(path_returns)),
            "std_sharpe": float(np.std(path_returns)),
            "median_sharpe": float(np.median(path_returns)),
            "min_sharpe": float(np.min(path_returns)),
            "max_sharpe": float(np.max(path_returns)),
            "pct_positive_sharpe": float(np.mean(np.array(path_returns) > 0)),
            "paths": paths,
        }

        # PBO: compare IS (train) vs OOS (test) Sharpe
        is_sharpes = []
        oos_sharpes = []
        for train_idx, test_idx in splits:
            if len(train_idx) < 2 or len(test_idx) < 2:
                continue
            is_ret = strategy_returns.iloc[train_idx]
            oos_ret = strategy_returns.iloc[test_idx]
            is_sharpe = float(is_ret.mean() / (is_ret.std() + 1e-10) * np.sqrt(252))
            oos_sharpe = float(oos_ret.mean() / (oos_ret.std() + 1e-10) * np.sqrt(252))
            is_sharpes.append(is_sharpe)
            oos_sharpes.append(oos_sharpe)

        if is_sharpes and oos_sharpes:
            stats["pbo"] = _calculate_pbo(is_sharpes, oos_sharpes)
        else:
            stats["pbo"] = 0.0

        return stats
    else:
        return {"n_paths": 0, "error": "No valid paths generated"}
