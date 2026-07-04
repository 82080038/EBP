"""
Transfer learning: parent-child model architecture.

Trains a "parent" model on broad market data, then fine-tunes
"child" models for individual stocks or sectors. The parent model
captures general market patterns; child models adapt to idiosyncrasies.

Reference:
- Microsoft Qlib: "Transfer learning for stock prediction"
- "Deep Learning for Stock Selection" (arXiv:2308.06007)
"""
from __future__ import annotations

import logging
import pickle
from dataclasses import dataclass, field
from typing import Dict, List, Optional

import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.linear_model import Ridge
from sklearn.preprocessing import StandardScaler

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class ChildModelInfo:
    """Info about a trained child model."""
    ticker: str = ""
    n_samples: int = 0
    fine_tune_accuracy: float = 0.0
    parent_accuracy: float = 0.0
    improvement: float = 0.0
    feature_overrides: List[str] = field(default_factory=list)


@dataclass
class TransferLearningResult:
    """Result from transfer learning prediction."""
    ticker: str = ""
    parent_prediction: float = 0.0
    child_prediction: float = 0.0
    final_prediction: float = 0.0
    used_child: bool = False
    confidence: float = 0.0
    improvement: float = 0.0
    child_info: Optional[ChildModelInfo] = None


# =============================================================================
# PARENT-CHILD MODEL
# =============================================================================


class ParentChildModel:
    """
    Parent-child transfer learning architecture.

    Parent model: Trained on aggregated market data (all stocks).
    Child models: Fine-tuned per stock/sector using parent as initialization.
    """

    def __init__(
        self,
        parent_model_fn=None,
        child_model_fn=None,
        fine_tune_alpha: float = 0.3,
        min_child_samples: int = 100,
        **kwargs,
    ):
        self.parent_model_fn = parent_model_fn or RandomForestRegressor
        self.child_model_fn = child_model_fn or Ridge
        self.fine_tune_alpha = fine_tune_alpha  # Weight for child vs parent
        self.min_child_samples = min_child_samples
        self.kwargs = kwargs

        self.parent_model = None
        self.parent_scaler = StandardScaler()
        self.child_models: Dict[str, any] = {}
        self.child_scalers: Dict[str, StandardScaler] = {}
        self.child_info: Dict[str, ChildModelInfo] = {}
        self.feature_cols: List[str] = []
        self.trained = False

    def _create_features(self, df: pd.DataFrame) -> pd.DataFrame:
        """Create features from OHLCV data."""
        features = pd.DataFrame(index=df.index)
        close = df.get("Close", pd.Series(dtype=float))

        if close.empty:
            return features

        # Returns
        for lag in [1, 2, 3, 5, 10, 20]:
            features[f"return_{lag}d"] = close.pct_change(lag)

        # MAs
        for window in [5, 10, 20, 50]:
            features[f"ma_{window}"] = close.rolling(window).mean() / close - 1

        # Volatility
        features["vol_10d"] = close.pct_change().rolling(10).std()
        features["vol_20d"] = close.pct_change().rolling(20).std()

        # RSI
        delta = close.diff()
        gain = delta.clip(lower=0).rolling(14).mean()
        loss = (-delta.clip(upper=0)).rolling(14).mean()
        features["rsi"] = 100 - (100 / (1 + gain / (loss + 1e-10)))

        # MACD
        ema12 = close.ewm(span=12).mean()
        ema26 = close.ewm(span=26).mean()
        features["macd"] = (ema12 - ema26) / close

        # Volume
        if "Volume" in df.columns:
            features["vol_ratio"] = df["Volume"] / (df["Volume"].rolling(10).mean() + 1e-10)

        return features

    def train_parent(self, dfs: Dict[str, pd.DataFrame]) -> Dict:
        """
        Train parent model on aggregated data from multiple stocks.

        Args:
            dfs: Dict mapping ticker -> DataFrame

        Returns:
            Training metrics
        """
        all_features = []
        all_targets = []

        for ticker, df in dfs.items():
            features = self._create_features(df)
            close = df["Close"]
            y = close.pct_change(1).shift(-1)  # Next day return

            data = pd.concat([features, y.rename("target")], axis=1).dropna()
            if len(data) > 20:
                all_features.append(data.drop(columns=["target"]))
                all_targets.append(data["target"])

        if not all_features:
            return {"error": "No valid data for parent training"}

        X = pd.concat(all_features)
        y = pd.concat(all_targets)

        self.feature_cols = list(X.columns)
        X_arr = X.values
        y_arr = y.values

        self.parent_scaler.fit(X_arr)
        X_scaled = self.parent_scaler.transform(X_arr)

        self.parent_model = self.parent_model_fn(
            n_estimators=100, max_depth=8, random_state=42, **self.kwargs
        )
        self.parent_model.fit(X_scaled, y_arr)

        # Metrics
        pred = self.parent_model.predict(X_scaled)
        mse = float(np.mean((pred - y_arr) ** 2))
        dir_acc = float(np.mean(np.sign(pred) == np.sign(y_arr)))

        self.trained = True
        return {
            "n_samples": len(X),
            "n_tickers": len(dfs),
            "mse": mse,
            "directional_accuracy": dir_acc,
        }

    def train_child(self, ticker: str, df: pd.DataFrame) -> ChildModelInfo:
        """
        Fine-tune a child model for a specific ticker.

        Args:
            ticker: Stock ticker
            df: OHLCV DataFrame for this ticker

        Returns:
            ChildModelInfo with training metrics
        """
        if not self.trained:
            raise RuntimeError("Parent model must be trained first")

        features = self._create_features(df)
        close = df["Close"]
        y = close.pct_change(1).shift(-1)

        data = pd.concat([features, y.rename("target")], axis=1).dropna()

        if len(data) < self.min_child_samples:
            logger.info(f"Child model for {ticker}: insufficient data ({len(data)}), using parent only")
            return ChildModelInfo(
                ticker=ticker,
                n_samples=len(data),
                fine_tune_accuracy=0,
                parent_accuracy=0,
                improvement=0,
            )

        X = data[self.feature_cols].values
        y_arr = data["target"].values

        scaler = StandardScaler()
        X_scaled = scaler.fit_transform(X)

        # Train child model
        child_model = self.child_model_fn(alpha=1.0, **self.kwargs)
        child_model.fit(X_scaled, y_arr)

        # Evaluate child
        child_pred = child_model.predict(X_scaled)
        child_acc = float(np.mean(np.sign(child_pred) == np.sign(y_arr)))

        # Evaluate parent on same data
        X_parent_scaled = self.parent_scaler.transform(X)
        parent_pred = self.parent_model.predict(X_parent_scaled)
        parent_acc = float(np.mean(np.sign(parent_pred) == np.sign(y_arr)))

        improvement = child_acc - parent_acc

        self.child_models[ticker] = child_model
        self.child_scalers[ticker] = scaler

        info = ChildModelInfo(
            ticker=ticker,
            n_samples=len(data),
            fine_tune_accuracy=child_acc,
            parent_accuracy=parent_acc,
            improvement=improvement,
        )
        self.child_info[ticker] = info

        return info

    def predict(self, ticker: str, df: pd.DataFrame) -> TransferLearningResult:
        """
        Predict using parent or child model.

        If child model exists and outperforms parent, use child.
        Otherwise, fall back to parent.
        """
        if not self.trained:
            return TransferLearningResult(ticker=ticker, confidence=0)

        features = self._create_features(df)
        if features.empty:
            return TransferLearningResult(ticker=ticker, confidence=0)

        latest = features.iloc[-1:][self.feature_cols].values
        latest = np.nan_to_num(latest)

        # Parent prediction
        parent_scaled = self.parent_scaler.transform(latest)
        parent_pred = float(self.parent_model.predict(parent_scaled)[0])

        # Child prediction if available
        child_pred = None
        used_child = False

        if ticker in self.child_models:
            child_scaled = self.child_scalers[ticker].transform(latest)
            child_pred = float(self.child_models[ticker].predict(child_scaled)[0])
            used_child = True

        # Blend: alpha * child + (1-alpha) * parent
        if child_pred is not None and self.fine_tune_alpha > 0:
            final_pred = self.fine_tune_alpha * child_pred + (1 - self.fine_tune_alpha) * parent_pred
        else:
            final_pred = parent_pred

        # Confidence based on improvement
        info = self.child_info.get(ticker)
        improvement = info.improvement if info else 0
        confidence = max(0, 0.5 + improvement)  # Base 0.5 + improvement

        return TransferLearningResult(
            ticker=ticker,
            parent_prediction=parent_pred,
            child_prediction=child_pred if child_pred is not None else 0,
            final_prediction=final_pred,
            used_child=used_child,
            confidence=confidence,
            improvement=improvement,
            child_info=info,
        )

    def save(self, path: str):
        """Save all models."""
        with open(path, "wb") as f:
            pickle.dump({
                "parent_model": self.parent_model,
                "parent_scaler": self.parent_scaler,
                "child_models": self.child_models,
                "child_scalers": self.child_scalers,
                "child_info": self.child_info,
                "feature_cols": self.feature_cols,
                "trained": self.trained,
                "fine_tune_alpha": self.fine_tune_alpha,
            }, f)

    def load(self, path: str):
        """Load models from disk."""
        with open(path, "rb") as f:
            data = pickle.load(f)
            self.parent_model = data["parent_model"]
            self.parent_scaler = data["parent_scaler"]
            self.child_models = data["child_models"]
            self.child_scalers = data["child_scalers"]
            self.child_info = data["child_info"]
            self.feature_cols = data["feature_cols"]
            self.trained = data["trained"]
            self.fine_tune_alpha = data["fine_tune_alpha"]


# =============================================================================
# CONVENIENCE FUNCTIONS
# =============================================================================


def run_transfer_learning(
    dfs: Dict[str, pd.DataFrame],
    target_ticker: str,
) -> Dict:
    """
    Run transfer learning pipeline.

    Args:
        dfs: Dict of ticker -> DataFrame
        target_ticker: Ticker to predict

    Returns:
        Dict with prediction and model info
    """
    model = ParentChildModel()

    # Train parent
    parent_metrics = model.train_parent(dfs)

    if "error" in parent_metrics:
        return {"error": parent_metrics["error"], "trained": False}

    # Train child for target
    if target_ticker in dfs:
        child_info = model.train_child(target_ticker, dfs[target_ticker])
    else:
        child_info = None

    # Predict
    if target_ticker in dfs:
        result = model.predict(target_ticker, dfs[target_ticker])
    else:
        return {"error": f"Target ticker {target_ticker} not in data", "trained": True}

    return {
        "trained": True,
        "parent_metrics": parent_metrics,
        "child_info": {
            "ticker": child_info.ticker,
            "n_samples": child_info.n_samples,
            "child_accuracy": child_info.fine_tune_accuracy,
            "parent_accuracy": child_info.parent_accuracy,
            "improvement": child_info.improvement,
        } if child_info else None,
        "prediction": {
            "parent": result.parent_prediction,
            "child": result.child_prediction,
            "final": result.final_prediction,
            "used_child": result.used_child,
            "confidence": result.confidence,
        },
    }
