"""
Walk-Forward Cross-Validation & Signal Quality Metrics.

Implementasi:
- Walk-Forward CV (rolling window train→test)
- Purged K-Fold CV dengan embargo period
- IC (Information Coefficient) / Rank IC / ICIR
- Combinatorial CV untuk robust evaluation

Referensi:
- QuantRocket MoonshotML (walk-forward validation)
- Microsoft Qlib (IC/Rank IC/ICIR metrics)
- Marcos López de Prado: "Advances in Financial Machine Learning"
"""

import numpy as np
import pandas as pd
from typing import Dict, List, Optional, Callable
from dataclasses import dataclass, field


@dataclass
class WalkForwardResult:
    """Hasil walk-forward cross-validation."""
    fold_results: List[dict] = field(default_factory=list)
    mean_da: float = 0.0
    std_da: float = 0.0
    mean_ic: float = 0.0
    mean_rank_ic: float = 0.0
    icir: float = 0.0
    n_folds: int = 0

    def summary(self) -> str:
        lines = [
            f"Walk-Forward CV Summary ({self.n_folds} folds)",
            f"  Mean DA:     {self.mean_da:.2f}% ± {self.std_da:.2f}%",
            f"  Mean IC:     {self.mean_ic:.4f}",
            f"  Mean RankIC: {self.mean_rank_ic:.4f}",
            f"  ICIR:        {self.icir:.4f}",
        ]
        return "\n".join(lines)


def walk_forward_cv(
    df: pd.DataFrame,
    feature_cols: List[str],
    target_col: str = "Target_Direction",
    return_col: str = "Target_Next_Return",
    n_folds: int = 5,
    train_ratio: float = 0.7,
    embargo_days: int = 5,
    model_fn: Optional[Callable] = None,
) -> WalkForwardResult:
    """
    Walk-forward cross-validation dengan rolling window dan embargo.

    Args:
        df: DataFrame dengan fitur dan target
        feature_cols: Kolom fitur
        target_col: Kolom target binary (0/1)
        return_col: Kolom return kontinu untuk IC calculation
        n_folds: Jumlah fold
        train_ratio: Rasio train dalam setiap fold
        embargo_days: Hari embargo setelah train untuk hindari leakage
        model_fn: Fungsi (X_train, y_train) -> model dengan predict_batch

    Returns:
        WalkForwardResult dengan metrik per fold
    """
    from .models import HybridEnsemble

    if model_fn is None:
        def model_fn(X_train, y_train):
            ens = HybridEnsemble()
            ens.train(X_train, y_train)
            return ens

    df_clean = df.dropna(subset=feature_cols + [target_col, return_col]).copy()
    n = len(df_clean)
    if n < 100:
        return WalkForwardResult()

    fold_size = (n - int(n * train_ratio)) // n_folds
    if fold_size < 20:
        n_folds = max(2, (n - int(n * train_ratio)) // 20)
        fold_size = max(20, (n - int(n * train_ratio)) // n_folds)

    result = WalkForwardResult()
    result.n_folds = 0

    for fold_idx in range(n_folds):
        train_end = int(n * train_ratio) + fold_idx * fold_size
        test_start = train_end + embargo_days
        test_end = min(test_start + fold_size, n)

        if test_start >= n or test_end <= test_start:
            break

        train = df_clean.iloc[:train_end]
        test = df_clean.iloc[test_start:test_end]

        X_train = train[feature_cols]
        y_train = train[target_col]
        X_test = test[feature_cols]
        y_test = test[target_col]
        returns_test = test[return_col]

        try:
            model = model_fn(X_train, y_train)
            all_preds, all_probas = model.predict_batch(X_test)

            if not all_preds:
                continue

            min_len = min(len(p) for p in all_preds.values())
            ensemble_preds = []
            for i in range(min_len):
                votes = sum(1 for preds in all_preds.values() if i < len(preds) and preds[i] == 1)
                total = len(all_preds)
                ensemble_preds.append(1 if votes > total / 2 else 0)

            ensemble_preds = np.array(ensemble_preds)
            y_test_aligned = y_test.iloc[:min_len]
            returns_aligned = returns_test.iloc[:min_len]

            da = float((y_test_aligned.values == ensemble_preds).mean() * 100)

            proba_values = []
            for model_name, probas in all_probas.items():
                if len(probas) >= min_len:
                    proba_values.append(probas[:min_len])
            if proba_values:
                avg_proba = np.mean(proba_values, axis=0)
            else:
                avg_proba = ensemble_preds.astype(float)

            ic = _calc_ic(avg_proba, returns_aligned.values)
            rank_ic = _calc_rank_ic(avg_proba, returns_aligned.values)

            fold_result = {
                "fold": fold_idx + 1,
                "train_size": len(train),
                "test_size": min_len,
                "da": da,
                "ic": ic,
                "rank_ic": rank_ic,
            }
            result.fold_results.append(fold_result)
            result.n_folds += 1

            print(f"  Fold {fold_idx + 1}: DA={da:.2f}%, IC={ic:.4f}, RankIC={rank_ic:.4f}")

        except Exception as e:
            print(f"  Fold {fold_idx + 1}: ERROR - {e}")
            continue

    if result.fold_results:
        das = [r["da"] for r in result.fold_results]
        ics = [r["ic"] for r in result.fold_results]
        rank_ics = [r["rank_ic"] for r in result.fold_results]

        result.mean_da = np.mean(das)
        result.std_da = np.std(das)
        result.mean_ic = np.mean(ics)
        result.mean_rank_ic = np.mean(rank_ics)
        ic_std = np.std(ics)
        result.icir = result.mean_ic / ic_std if ic_std > 0 else 0.0

    return result


def purged_kfold_cv(
    df: pd.DataFrame,
    feature_cols: List[str],
    target_col: str = "Target_Direction",
    return_col: str = "Target_NextReturn",
    n_splits: int = 5,
    purge_days: int = 5,
    embargo_days: int = 3,
    model_fn: Optional[Callable] = None,
) -> WalkForwardResult:
    """
    Purged K-Fold CV dengan embargo period (López de Prado).

    Setiap fold memiliki:
    - Purge: hapus data di sekitar train/test boundary
    - Embargo: tambahan buffer setelah train untuk hindari label leakage
    """
    from .models import HybridEnsemble

    if model_fn is None:
        def model_fn(X_train, y_train):
            ens = HybridEnsemble()
            ens.train(X_train, y_train)
            return ens

    df_clean = df.dropna(subset=feature_cols + [target_col]).copy()
    n = len(df_clean)
    if n < 100:
        return WalkForwardResult()

    fold_size = n // n_splits
    result = WalkForwardResult()
    result.n_folds = 0

    for k in range(n_splits):
        test_start = k * fold_size
        test_end = min((k + 1) * fold_size, n)

        purge_start = max(0, test_start - purge_days)
        purge_end = min(n, test_end + purge_days)

        train_mask = np.ones(n, dtype=bool)
        train_mask[purge_start:purge_end] = False
        embargo_end = min(n, test_end + purge_days + embargo_days)
        train_mask[test_end:embargo_end] = False

        if test_start > 0:
            train_mask[:test_start] = True
        train_mask[test_start:test_end] = False

        train = df_clean[train_mask]
        test = df_clean.iloc[test_start:test_end]

        if len(train) < 50 or len(test) < 10:
            continue

        X_train = train[feature_cols]
        y_train = train[target_col]
        X_test = test[feature_cols]
        y_test = test[target_col]

        try:
            model = model_fn(X_train, y_train)
            all_preds, all_probas = model.predict_batch(X_test)

            if not all_preds:
                continue

            min_len = min(len(p) for p in all_preds.values())
            ensemble_preds = []
            for i in range(min_len):
                votes = sum(1 for preds in all_preds.values() if i < len(preds) and preds[i] == 1)
                total = len(all_preds)
                ensemble_preds.append(1 if votes > total / 2 else 0)

            ensemble_preds = np.array(ensemble_preds)
            y_test_aligned = y_test.iloc[:min_len]

            da = float((y_test_aligned.values == ensemble_preds).mean() * 100)

            proba_values = []
            for model_name, probas in all_probas.items():
                if len(probas) >= min_len:
                    proba_values.append(probas[:min_len])
            avg_proba = np.mean(proba_values, axis=0) if proba_values else ensemble_preds.astype(float)

            returns_col = return_col if return_col in test.columns else "Target_Next_Return"
            returns_aligned = test[returns_col].iloc[:min_len] if returns_col in test.columns else pd.Series(np.zeros(min_len))

            ic = _calc_ic(avg_proba, returns_aligned.values)
            rank_ic = _calc_rank_ic(avg_proba, returns_aligned.values)

            fold_result = {
                "fold": k + 1,
                "train_size": len(train),
                "test_size": min_len,
                "da": da,
                "ic": ic,
                "rank_ic": rank_ic,
            }
            result.fold_results.append(fold_result)
            result.n_folds += 1
            print(f"  Purged Fold {k + 1}: DA={da:.2f}%, IC={ic:.4f}")

        except Exception as e:
            print(f"  Purged Fold {k + 1}: ERROR - {e}")
            continue

    if result.fold_results:
        das = [r["da"] for r in result.fold_results]
        ics = [r["ic"] for r in result.fold_results]
        rank_ics = [r["rank_ic"] for r in result.fold_results]

        result.mean_da = np.mean(das)
        result.std_da = np.std(das)
        result.mean_ic = np.mean(ics)
        result.mean_rank_ic = np.mean(rank_ics)
        ic_std = np.std(ics)
        result.icir = result.mean_ic / ic_std if ic_std > 0 else 0.0

    return result


def _calc_ic(predicted: np.ndarray, actual_returns: np.ndarray) -> float:
    """
    Information Coefficient (IC) = Spearman rank correlation
    antara prediksi dan return aktual.

    IC > 0.05 dianggap sinyal yang baik.
    """
    if len(predicted) < 5 or len(actual_returns) < 5:
        return 0.0
    if len(predicted) != len(actual_returns):
        min_len = min(len(predicted), len(actual_returns))
        predicted = predicted[:min_len]
        actual_returns = actual_returns[:min_len]

    if np.std(predicted) == 0 or np.std(actual_returns) == 0:
        return 0.0

    from scipy.stats import spearmanr
    corr, _ = spearmanr(predicted, actual_returns)
    return float(corr) if not np.isnan(corr) else 0.0


def _calc_rank_ic(predicted: np.ndarray, actual_returns: np.ndarray) -> float:
    """
    Rank IC = Pearson correlation antara ranked predictions dan ranked returns.
    """
    if len(predicted) < 5 or len(actual_returns) < 5:
        return 0.0
    if len(predicted) != len(actual_returns):
        min_len = min(len(predicted), len(actual_returns))
        predicted = predicted[:min_len]
        actual_returns = actual_returns[:min_len]

    if np.std(predicted) == 0 or np.std(actual_returns) == 0:
        return 0.0

    from scipy.stats import rankdata
    ranked_pred = rankdata(predicted)
    ranked_actual = rankdata(actual_returns)

    corr = np.corrcoef(ranked_pred, ranked_actual)[0, 1]
    return float(corr) if not np.isnan(corr) else 0.0


def calc_icir(ics: List[float]) -> float:
    """
    ICIR (Information Coefficient to Irrelevant Ratio)
    = mean(IC) / std(IC)

    ICIR > 0.3 dianggap sinyal yang konsisten.
    """
    if not ics or len(ics) < 2:
        return 0.0
    mean_ic = np.mean(ics)
    std_ic = np.std(ics)
    return float(mean_ic / std_ic) if std_ic > 0 else 0.0


def evaluate_signal_quality(
    predictions: np.ndarray,
    probabilities: np.ndarray,
    actual_returns: pd.Series,
) -> Dict[str, float]:
    """
    Evaluasi kualitas sinyal dengan metrik profesional.

    Returns dict dengan:
    - IC, Rank IC, ICIR
    - Hit Rate (% arah benar)
    - Profit Factor (sum positive / sum negative returns)
    - Sharpe Ratio per sinyal
    """
    if len(predictions) != len(actual_returns):
        min_len = min(len(predictions), len(actual_returns))
        predictions = predictions[:min_len]
        probabilities = probabilities[:min_len]
        actual_returns = actual_returns.iloc[:min_len]

    ic = _calc_ic(probabilities, actual_returns.values)
    rank_ic = _calc_rank_ic(probabilities, actual_returns.values)

    correct_direction = (
        ((predictions == 1) & (actual_returns > 0)) |
        ((predictions == 0) & (actual_returns < 0))
    )
    hit_rate = float(correct_direction.mean() * 100)

    signal_returns = np.where(predictions == 1, actual_returns.values, -actual_returns.values)
    positive_returns = signal_returns[signal_returns > 0].sum()
    negative_returns = abs(signal_returns[signal_returns < 0].sum())
    profit_factor = float(positive_returns / negative_returns) if negative_returns > 0 else float('inf')

    if np.std(signal_returns) > 0:
        sharpe = float(np.mean(signal_returns) / np.std(signal_returns) * np.sqrt(252))
    else:
        sharpe = 0.0

    return {
        "ic": ic,
        "rank_ic": rank_ic,
        "hit_rate": hit_rate,
        "profit_factor": profit_factor,
        "sharpe_per_signal": sharpe,
    }
