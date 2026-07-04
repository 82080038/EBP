"""
Advances in Financial Machine Learning (AFML) — Lopez de Prado Techniques.

Implements key methodologies from Marcos Lopez de Prado's book:
- Triple-Barrier Labeling: Path-dependent labels for ML training
- Meta-Labeling: Two-model architecture for bet sizing
- Fractional Differentiation: Stationarity + memory retention
- Purged K-Fold CV: Leakage-free cross-validation for time series
- Deflated Sharpe Ratio: Multiple testing bias correction
- Probability of Backtest Overfitting (PBO): Overfitting detection
"""

import pandas as pd
import numpy as np
from scipy import stats
from typing import Tuple, List, Optional, Dict, Callable
from dataclasses import dataclass, field


# =============================================================================
# TRIPLE-BARRIER LABELING
# =============================================================================

def triple_barrier_labels(
    df: pd.DataFrame,
    close_col: str = "Close",
    high_col: str = "High",
    low_col: str = "Low",
    vol_col: Optional[str] = None,
    pt_sl: Tuple[float, float] = (1.0, 1.0),
    max_holding_period: int = 10,
    vol_lookback: int = 20,
    vol_scalar: float = 1.0,
) -> pd.DataFrame:
    """
    Triple-Barrier Labeling method (AFML Chapter 3).

    Labels observations based on which barrier is touched first:
    - Upper barrier (profit-taking): +1
    - Lower barrier (stop-loss): -1
    - Vertical barrier (time limit): 0

    Args:
        pt_sl: (profit-taking multiplier, stop-loss multiplier) of volatility
        max_holding_period: Maximum bars before vertical barrier
        vol_lookback: Lookback for volatility estimation
        vol_scalar: Scalar multiplier for volatility
    """
    prices = df[close_col].copy()
    n = len(df)

    # Compute volatility (daily returns std)
    if vol_col and vol_col in df.columns:
        vol = df[vol_col].copy()
    else:
        returns = prices.pct_change()
        vol = returns.rolling(vol_lookback).std() * vol_scalar
        vol = vol.fillna(vol.mean())

    labels = pd.Series(0, index=df.index)
    touch_times = pd.Series(df.index, index=df.index)

    for i in range(vol_lookback, n):
        entry_price = prices.iloc[i]
        current_vol = vol.iloc[i]

        if pd.isna(current_vol) or current_vol <= 0:
            continue

        # Set barriers
        upper = entry_price * (1 + pt_sl[0] * current_vol)
        lower = entry_price * (1 - pt_sl[1] * current_vol)
        end_idx = min(i + max_holding_period, n - 1)

        # Find first barrier touch
        label = 0
        for j in range(i + 1, end_idx + 1):
            if df[high_col].iloc[j] >= upper:
                label = 1
                touch_times.iloc[i] = df.index[j]
                break
            elif df[low_col].iloc[j] <= lower:
                label = -1
                touch_times.iloc[i] = df.index[j]
                break

        labels.iloc[i] = label

    result = pd.DataFrame({
        "label": labels,
        "touch_time": touch_times,
        "volatility": vol,
    }, index=df.index)

    return result.iloc[vol_lookback:]


# =============================================================================
# META-LABELING
# =============================================================================

@dataclass
class MetaLabelingResult:
    """Result of meta-labeling pipeline."""
    primary_signals: pd.Series
    meta_labels: pd.Series
    meta_probabilities: Optional[pd.Series] = None
    bet_sizes: Optional[pd.Series] = None
    model: Optional[object] = None
    metrics: Dict = field(default_factory=dict)


def meta_labeling(
    df: pd.DataFrame,
    primary_signal_col: str,
    close_col: str = "Close",
    pt_sl: Tuple[float, float] = (1.0, 1.0),
    max_holding_period: int = 10,
    features: Optional[List[str]] = None,
    test_size: float = 0.3,
) -> MetaLabelingResult:
    """
    Meta-Labeling pipeline (AFML Chapter 3).

    Two-stage approach:
    1. Primary model generates directional signals (+1/-1)
    2. Meta-model predicts whether primary signal will be correct (1/0)

    The meta-model's probability output is used for bet sizing.

    Args:
        primary_signal_col: Column name with primary model signals (+1/-1/0)
        features: Feature columns for meta-model training. If None, uses all numeric cols.
    """
    from sklearn.ensemble import RandomForestClassifier
    from sklearn.metrics import f1_score, precision_score, recall_score

    # Get triple-barrier labels
    tb = triple_barrier_labels(
        df, close_col=close_col, pt_sl=pt_sl,
        max_holding_period=max_holding_period,
    )

    # Align with primary signals
    common_idx = tb.index.intersection(df.index)
    tb = tb.loc[common_idx]
    primary = df[primary_signal_col].loc[common_idx]

    # Create meta-labels: 1 if primary signal matches barrier label, 0 otherwise
    meta_labels = pd.Series(0, index=common_idx)
    for idx in common_idx:
        p = primary.loc[idx]
        t = tb.loc[idx, "label"]
        if p != 0 and t != 0:
            meta_labels.loc[idx] = 1 if p == t else 0
        elif p != 0 and t == 0:
            meta_labels.loc[idx] = 0  # Time stop = no profit

    # Prepare features
    if features is None:
        features = [c for c in df.select_dtypes(include=[np.number]).columns
                     if c not in [primary_signal_col, close_col]]

    X = df[features].loc[common_idx].fillna(0)
    y = meta_labels

    # Split
    split_idx = int(len(X) * (1 - test_size))
    X_train, X_test = X.iloc[:split_idx], X.iloc[split_idx:]
    y_train, y_test = y.iloc[:split_idx], y.iloc[split_idx:]

    # Train meta-model
    model = RandomForestClassifier(
        n_estimators=100, max_depth=5, random_state=42, class_weight="balanced",
    )
    model.fit(X_train, y_train)

    # Predict
    meta_proba = pd.Series(model.predict_proba(X)[:, 1], index=common_idx)
    meta_pred = pd.Series(model.predict(X), index=common_idx)

    # Bet sizing: probability × primary signal
    bet_sizes = meta_proba * primary

    # Metrics
    metrics = {}
    if len(y_test) > 0:
        y_pred_test = model.predict(X_test)
        metrics = {
            "f1": f1_score(y_test, y_pred_test, zero_division=0),
            "precision": precision_score(y_test, y_pred_test, zero_division=0),
            "recall": recall_score(y_test, y_pred_test, zero_division=0),
        }

    return MetaLabelingResult(
        primary_signals=primary,
        meta_labels=meta_pred,
        meta_probabilities=meta_proba,
        bet_sizes=bet_sizes,
        model=model,
        metrics=metrics,
    )


# =============================================================================
# FRACTIONAL DIFFERENTIATION
# =============================================================================

def _get_ffd_weights(d: float, threshold: float = 1e-5) -> np.ndarray:
    """Compute weights for fixed-width fractional differentiation."""
    w = [1.0]
    k = 1
    while True:
        w_k = -w[-1] * (d - k + 1) / k
        if abs(w_k) < threshold:
            break
        w.append(w_k)
        k += 1
    return np.array(w)


def fractional_differentiation(
    series: pd.Series,
    d: float = 0.4,
    threshold: float = 1e-5,
) -> pd.Series:
    """
    Fixed-Width Window Fractional Differentiation (AFML Chapter 5).

    Finds the minimum d that makes the series stationary while retaining memory.
    d=0: raw prices (full memory, non-stationary)
    d=1: returns (no memory, stationary)
    d=0.3-0.6: optimal balance

    Args:
        d: Differentiation order (0 < d < 1)
        threshold: Weight cutoff threshold
    """
    weights = _get_ffd_weights(d, threshold)
    n = len(weights)
    result = pd.Series(index=series.index, dtype=float)

    for i in range(n - 1, len(series)):
        window = series.iloc[i - n + 1:i + 1]
        if len(window) == n:
            result.iloc[i] = np.dot(weights[::-1], window.values)

    return result


def find_optimal_d(
    series: pd.Series,
    d_range: np.ndarray = None,
    significance: float = 0.05,
) -> Tuple[float, pd.Series]:
    """
    Find minimum d that passes ADF test (stationarity).

    Returns:
        (optimal_d, differentiated_series)
    """
    if d_range is None:
        d_range = np.arange(0.1, 1.1, 0.1)

    for d in d_range:
        diffed = fractional_differentiation(series, d=d)
        diffed = diffed.dropna()
        if len(diffed) < 20:
            continue

        try:
            adf_result = stats.adfuller(diffed)
            p_value = adf_result[1]
            if p_value < significance:
                return d, diffed
        except Exception:
            continue

    # Fallback to d=1
    return 1.0, series.pct_change().dropna()


# =============================================================================
# PURGED K-FOLD CROSS-VALIDATION
# =============================================================================

def purged_kfold_indices(
    n: int,
    n_folds: int = 5,
    purge_days: int = 5,
    embargo_days: int = 3,
) -> List[Tuple[np.ndarray, np.ndarray]]:
    """
    Generate purged k-fold indices for time series (AFML Chapter 7).

    Args:
        n: Total number of samples
        n_folds: Number of folds
        purge_days: Number of samples to purge around train/test boundary
        embargo_days: Number of samples to embargo after test set

    Returns:
        List of (train_indices, test_indices) tuples
    """
    fold_size = n // n_folds
    folds = []

    for k in range(n_folds):
        test_start = k * fold_size
        test_end = min((k + 1) * fold_size, n)
        test_indices = np.arange(test_start, test_end)

        # Purge: remove samples around test boundaries
        purge_start = max(0, test_start - purge_days)
        purge_end = min(n, test_end + purge_days)

        # Train = everything except test + purge
        train_indices = np.concatenate([
            np.arange(0, purge_start),
            np.arange(purge_end, n),
        ])

        # Embargo: remove samples after test set
        embargo_start = test_end
        embargo_end = min(n, test_end + embargo_days)
        train_indices = train_indices[
            (train_indices < embargo_start) | (train_indices >= embargo_end)
        ]

        folds.append((train_indices, test_indices))

    return folds


def purged_kfold_cv(
    X: pd.DataFrame,
    y: pd.Series,
    model_fn: Callable,
    n_folds: int = 5,
    purge_days: int = 5,
    embargo_days: int = 3,
    **model_kwargs,
) -> Dict:
    """
    Run purged k-fold cross-validation.

    Args:
        model_fn: Function that returns a fresh model instance
    """
    n = len(X)
    folds = purged_kfold_indices(n, n_folds, purge_days, embargo_days)

    results = []
    for i, (train_idx, test_idx) in enumerate(folds):
        if len(train_idx) == 0 or len(test_idx) == 0:
            continue

        X_train, X_test = X.iloc[train_idx], X.iloc[test_idx]
        y_train, y_test = y.iloc[train_idx], y.iloc[test_idx]

        model = model_fn(**model_kwargs)
        model.fit(X_train, y_train)
        score = model.score(X_test, y_test)
        results.append({
            "fold": i,
            "train_size": len(train_idx),
            "test_size": len(test_idx),
            "score": score,
        })

    scores = [r["score"] for r in results]
    return {
        "mean_score": np.mean(scores) if scores else 0,
        "std_score": np.std(scores) if scores else 0,
        "fold_results": results,
        "n_folds": len(results),
    }


# =============================================================================
# DEFLATED SHARPE RATIO
# =============================================================================

def deflated_sharpe_ratio(
    observed_sr: float,
    n_trials: int,
    n_samples: int,
    skewness: float = 0.0,
    kurtosis: float = 3.0,
) -> float:
    """
    Deflated Sharpe Ratio (AFML Chapter 11-12).

    Corrects observed Sharpe Ratio for multiple testing bias.

    Args:
        observed_sr: Observed annualized Sharpe Ratio
        n_trials: Number of strategies tested
        n_samples: Number of return observations
        skewness: Skewness of returns
        kurtosis: Kurtosis of returns (3 = normal)

    Returns:
        DSR value. If DSR < 0, performance is likely a false discovery.
    """
    # Expected max SR under null hypothesis
    # E[max(SR)] ≈ sqrt(2 * ln(N)) for N independent trials
    if n_trials <= 1:
        expected_max_sr = 0.0
    else:
        expected_max_sr = np.sqrt(2 * np.log(n_trials))

    # Variance of SR estimate
    # Var(SR) ≈ (1 - skew*SR + (kurt-1)/4 * SR^2) / (n-1)
    sr_var = (1 - skewness * observed_sr + (kurtosis - 1) / 4 * observed_sr ** 2) / max(n_samples - 1, 1)
    sr_std = np.sqrt(sr_var)

    if sr_std <= 0:
        return 0.0

    # DSR = (SR_observed - E[max(SR)]) / sigma(SR)
    dsr = (observed_sr - expected_max_sr) / sr_std

    return dsr


# =============================================================================
# PROBABILITY OF BACKTEST OVERFITTING (PBO)
# =============================================================================

def probability_of_backtest_overfitting(
    is_sharpe_ratios: np.ndarray,
    oos_sharpe_ratios: np.ndarray,
) -> float:
    """
    Probability of Backtest Overfitting (AFML Chapter 12).

    PBO = probability that OOS rank is worse than median IS rank.

    Args:
        is_sharpe_ratios: In-sample Sharpe ratios (n_strategies,)
        oos_sharpe_ratios: Out-of-sample Sharpe ratios (n_strategies,)

    Returns:
        PBO value (0-1). PBO > 0.5 suggests overfitting.
    """
    n = len(is_sharpe_ratios)
    if n == 0:
        return 0.5

    # Rank IS and OOS
    is_ranks = stats.rankdata(is_sharpe_ratios)
    oos_ranks = stats.rankdata(oos_sharpe_ratios)

    # Median IS rank
    median_is_rank = np.median(is_ranks)

    # PBO = fraction of strategies where OOS rank < median IS rank
    pbo = np.mean(oos_ranks < median_is_rank)

    return float(pbo)


# =============================================================================
# CONVENIENCE: RUN FULL AFML PIPELINE
# =============================================================================

def run_afml_pipeline(
    df: pd.DataFrame,
    close_col: str = "Close",
    high_col: str = "High",
    low_col: str = "Low",
    volume_col: str = "Volume",
    pt_sl: Tuple[float, float] = (1.0, 1.0),
    max_holding_period: int = 10,
) -> Dict:
    """
    Run full AFML pipeline on price data.

    Returns dict with:
    - triple_barrier_labels
    - fractional_diff (optimal d + differentiated series)
    - stationarity check
    """
    results = {}

    # Triple-barrier labels
    tb = triple_barrier_labels(
        df, close_col=close_col, high_col=high_col, low_col=low_col,
        pt_sl=pt_sl, max_holding_period=max_holding_period,
    )
    results["triple_barrier"] = tb
    results["label_distribution"] = tb["label"].value_counts().to_dict()

    # Fractional differentiation
    opt_d, diffed = find_optimal_d(df[close_col])
    results["optimal_d"] = opt_d
    results["fractional_diff"] = diffed

    # Stationarity check
    try:
        adf_result = stats.adfuller(diffed.dropna())
        results["adf_pvalue"] = adf_result[1]
        results["is_stationary"] = adf_result[1] < 0.05
    except Exception:
        results["adf_pvalue"] = None
        results["is_stationary"] = None

    return results
