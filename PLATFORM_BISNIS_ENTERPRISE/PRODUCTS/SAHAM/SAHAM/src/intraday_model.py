"""
Intraday ML Model — 5m/15m interval prediction for day traders.

Features:
- Train on intraday OHLCV data (5m, 15m intervals)
- Technical indicators adapted for intraday (shorter periods)
- Direction prediction for next N bars
- Confidence scoring with volatility adjustment
- Order book proxy via spread estimation

Usage:
    from src.intraday_model import IntradayModel
    model = IntradayModel(interval="5m")
    model.train(intraday_df)
    pred = model.predict(latest_bars)
"""

import pandas as pd
import numpy as np
from typing import Dict, List, Optional
from dataclasses import dataclass
from datetime import datetime
import joblib
import os

from .config import MODELS_DIR


@dataclass
class IntradayPrediction:
    """Intraday prediction result."""
    interval: str
    predicted_direction: str  # "UP", "DOWN", "FLAT"
    confidence: float
    predicted_return_pct: float
    predicted_price: float
    current_price: float
    bars_ahead: int
    entry_signal: str  # "BUY", "SELL", "HOLD"
    stop_loss: float
    take_profit: float
    rsi: float
    vwap: float
    volume_trend: str  # "increasing", "decreasing", "stable"
    spread_estimate: float
    timestamp: str = ""


class IntradayModel:
    """
    ML model for intraday trading (5m, 15m intervals).

    Uses LightGBM/XGBoost with intraday-specific features:
    - Short-period RSI (5, 7)
    - VWAP deviation
    - Volume rate of change
    - Price momentum (1-3 bars)
    - Opening range breakout
    - Intraday session timing
    """

    INTRADAY_PARAMS = {
        "5m": {
            "rsi_period": 7,
            "ma_short": 5,
            "ma_long": 20,
            "vwap_reset": "daily",
            "vol_lookback": 10,
            "label_bars_ahead": 3,  # Predict 3 bars ahead (15 min)
        },
        "15m": {
            "rsi_period": 7,
            "ma_short": 5,
            "ma_long": 20,
            "vwap_reset": "daily",
            "vol_lookback": 10,
            "label_bars_ahead": 2,  # Predict 2 bars ahead (30 min)
        },
    }

    def __init__(self, interval: str = "5m"):
        self.interval = interval
        self.params = self.INTRADAY_PARAMS.get(interval, self.INTRADAY_PARAMS["5m"])
        self.model = None
        self.feature_cols: List[str] = []
        self.is_trained = False
        self.model_path = os.path.join(MODELS_DIR, f"intraday_{interval}_model.pkl")

    def _add_intraday_features(self, df: pd.DataFrame) -> pd.DataFrame:
        """Add intraday-specific technical features."""
        df = df.copy()

        p = self.params
        # RSI (short period for intraday)
        delta = df["Close"].diff()
        gain = delta.clip(lower=0).rolling(p["rsi_period"]).mean()
        loss = (-delta.clip(upper=0)).rolling(p["rsi_period"]).mean()
        rs = gain / loss.replace(0, np.nan)
        df["RSI"] = 100 - (100 / (1 + rs))

        # Moving averages
        df["MA_Short"] = df["Close"].rolling(p["ma_short"]).mean()
        df["MA_Long"] = df["Close"].rolling(p["ma_long"]).mean()
        df["MA_Diff"] = (df["MA_Short"] - df["MA_Long"]) / df["MA_Long"]

        # VWAP (reset daily)
        if isinstance(df.index, pd.DatetimeIndex):
            df["Date"] = df.index.date
            df["Typical_Price"] = (df["High"] + df["Low"] + df["Close"]) / 3
            df["Cum_Vol"] = df.groupby("Date")["Volume"].cumsum()
            df["Cum_TP_Vol"] = df.groupby("Date").apply(
                lambda x: (x["Typical_Price"] * x["Volume"]).cumsum()
            ).reset_index(level=0, drop=True)
            df["VWAP"] = df["Cum_TP_Vol"] / df["Cum_Vol"].replace(0, np.nan)
            df["VWAP_Dev"] = (df["Close"] - df["VWAP"]) / df["VWAP"]
        else:
            df["VWAP"] = df["Close"].rolling(p["ma_long"]).mean()
            df["VWAP_Dev"] = 0

        # Volume features
        df["Vol_MA"] = df["Volume"].rolling(p["vol_lookback"]).mean()
        df["Vol_Ratio"] = df["Volume"] / df["Vol_MA"].replace(0, np.nan)
        df["Vol_ROC"] = df["Volume"].pct_change(p["vol_lookback"] // 2)

        # Price momentum
        df["Mom_1"] = df["Close"].pct_change(1)
        df["Mom_2"] = df["Close"].pct_change(2)
        df["Mom_3"] = df["Close"].pct_change(3)

        # High-Low spread (proxy for order book)
        df["HL_Spread"] = (df["High"] - df["Low"]) / df["Close"]
        df["HL_Spread_MA"] = df["HL_Spread"].rolling(p["vol_lookback"]).mean()

        # Body and shadow
        df["Body"] = (df["Close"] - df["Open"]) / df["Open"]
        df["Upper_Shadow"] = (df["High"] - df[["Open", "Close"]].max(axis=1)) / df["Close"]
        df["Lower_Shadow"] = (df[["Open", "Close"]].min(axis=1) - df["Low"]) / df["Close"]

        # Session timing (if datetime index)
        if isinstance(df.index, pd.DatetimeIndex):
            hour = df.index.hour
            minute = df.index.minute
            df["Session_Hour"] = hour
            df["Session_Minute"] = minute
            # Opening range (first 30 min)
            df["Is_Opening"] = ((hour == 9) & (minute < 30)).astype(int)
            # Closing session (last 30 min)
            df["Is_Closing"] = ((hour == 15) & (minute >= 30)).astype(int)
        else:
            df["Session_Hour"] = 0
            df["Session_Minute"] = 0
            df["Is_Opening"] = 0
            df["Is_Closing"] = 0

        return df

    def _create_labels(self, df: pd.DataFrame) -> pd.DataFrame:
        """Create prediction labels (direction N bars ahead)."""
        bars = self.params["label_bars_ahead"]
        df["Future_Return"] = df["Close"].shift(-bars).pct_change(bars)
        df["Target_Direction"] = (df["Future_Return"] > 0).astype(int)
        return df

    def train(self, df: pd.DataFrame) -> Dict:
        """
        Train intraday model on OHLCV data.

        Args:
            df: DataFrame with DatetimeIndex, columns: Open, High, Low, Close, Volume

        Returns:
            Training metrics dict
        """
        if df.empty or len(df) < 200:
            return {"error": f"Insufficient data: {len(df)} rows (need 200+)"}

        # Add features
        df_feat = self._add_intraday_features(df)
        df_feat = self._create_labels(df_feat)

        # Define feature columns
        self.feature_cols = [
            "RSI", "MA_Diff", "VWAP_Dev", "Vol_Ratio", "Vol_ROC",
            "Mom_1", "Mom_2", "Mom_3", "HL_Spread", "HL_Spread_MA",
            "Body", "Upper_Shadow", "Lower_Shadow",
            "Session_Hour", "Session_Minute", "Is_Opening", "Is_Closing",
        ]

        # Drop NaN
        df_clean = df_feat.dropna(subset=self.feature_cols + ["Target_Direction", "Future_Return"])

        if len(df_clean) < 100:
            return {"error": f"Insufficient clean data: {len(df_clean)} rows"}

        # Split (80/20 temporal)
        split_idx = int(len(df_clean) * 0.8)
        train_df = df_clean.iloc[:split_idx]
        test_df = df_clean.iloc[split_idx:]

        X_train = train_df[self.feature_cols]
        y_train = train_df["Target_Direction"]
        X_test = test_df[self.feature_cols]
        y_test = test_df["Target_Direction"]

        # Try LightGBM first, then XGBoost, then sklearn
        try:
            import lightgbm as lgb
            self.model = lgb.LGBMClassifier(
                n_estimators=100, max_depth=5, learning_rate=0.1,
                random_state=42, verbose=-1,
            )
        except ImportError:
            try:
                import xgboost as xgb
                self.model = xgb.XGBClassifier(
                    n_estimators=100, max_depth=5, learning_rate=0.1,
                    random_state=42, verbosity=0,
                )
            except ImportError:
                from sklearn.ensemble import RandomForestClassifier
                self.model = RandomForestClassifier(
                    n_estimators=100, max_depth=5, random_state=42,
                )

        self.model.fit(X_train, y_train)

        # Evaluate
        train_acc = self.model.score(X_train, y_train)
        test_acc = self.model.score(X_test, y_test)

        # Feature importance
        try:
            importance = dict(zip(self.feature_cols, self.model.feature_importances_))
            top_features = sorted(importance.items(), key=lambda x: x[1], reverse=True)[:5]
        except Exception:
            top_features = []

        self.is_trained = True

        # Save model
        try:
            joblib.dump({
                "model": self.model,
                "feature_cols": self.feature_cols,
                "interval": self.interval,
                "params": self.params,
            }, self.model_path)
        except Exception:
            pass

        return {
            "status": "trained",
            "interval": self.interval,
            "train_samples": len(train_df),
            "test_samples": len(test_df),
            "train_accuracy": round(train_acc, 4),
            "test_accuracy": round(test_acc, 4),
            "top_features": top_features,
            "bars_ahead": self.params["label_bars_ahead"],
        }

    def predict(self, df: pd.DataFrame) -> Optional[IntradayPrediction]:
        """
        Predict direction for next N bars.

        Args:
            df: Recent OHLCV data (at least 30 bars)

        Returns:
            IntradayPrediction or None if insufficient data
        """
        if not self.is_trained and os.path.exists(self.model_path):
            try:
                saved = joblib.load(self.model_path)
                self.model = saved["model"]
                self.feature_cols = saved["feature_cols"]
                self.is_trained = True
            except Exception:
                pass

        if not self.is_trained:
            return None

        if df.empty or len(df) < 30:
            return None

        # Add features
        df_feat = self._add_intraday_features(df)

        # Get latest row with features
        latest = df_feat.dropna(subset=self.feature_cols).iloc[-1:]
        if latest.empty:
            return None

        X = latest[self.feature_cols]
        pred_proba = self.model.predict_proba(X)[0]
        pred_class = self.model.predict(X)[0]

        confidence = max(pred_proba)
        direction = "UP" if pred_class == 1 else "DOWN"
        if confidence < 0.55:
            direction = "FLAT"

        current_price = latest["Close"].iloc[0]
        bars = self.params["label_bars_ahead"]

        # Estimate predicted return from historical volatility
        vol = df["Close"].pct_change().std()
        predicted_return = vol * np.sqrt(bars) * (1 if direction == "UP" else -1) * confidence
        predicted_price = current_price * (1 + predicted_return)

        # Entry signal
        if direction == "UP" and confidence > 0.6:
            entry_signal = "BUY"
        elif direction == "DOWN" and confidence > 0.6:
            entry_signal = "SELL"
        else:
            entry_signal = "HOLD"

        # SL/TP based on ATR-like measure
        recent_range = (df["High"] - df["Low"]).tail(20).mean()
        if entry_signal == "BUY":
            stop_loss = current_price - recent_range * 1.0
            take_profit = current_price + recent_range * 2.0
        elif entry_signal == "SELL":
            stop_loss = current_price + recent_range * 1.0
            take_profit = current_price - recent_range * 2.0
        else:
            stop_loss = 0
            take_profit = 0

        # VWAP
        vwap = latest["VWAP"].iloc[0] if "VWAP" in latest.columns else current_price

        # Volume trend
        if "Vol_Ratio" in latest.columns:
            vol_ratio = latest["Vol_Ratio"].iloc[0]
            if vol_ratio > 1.5:
                vol_trend = "increasing"
            elif vol_ratio < 0.5:
                vol_trend = "decreasing"
            else:
                vol_trend = "stable"
        else:
            vol_trend = "stable"

        # Spread estimate
        spread = latest["HL_Spread"].iloc[0] if "HL_Spread" in latest.columns else 0

        # RSI
        rsi = latest["RSI"].iloc[0] if "RSI" in latest.columns else 50

        return IntradayPrediction(
            interval=self.interval,
            predicted_direction=direction,
            confidence=round(confidence, 4),
            predicted_return_pct=round(predicted_return * 100, 2),
            predicted_price=round(predicted_price, 2),
            current_price=round(current_price, 2),
            bars_ahead=bars,
            entry_signal=entry_signal,
            stop_loss=round(stop_loss, 2),
            take_profit=round(take_profit, 2),
            rsi=round(rsi, 2),
            vwap=round(vwap, 2),
            volume_trend=vol_trend,
            spread_estimate=round(spread, 6),
            timestamp=datetime.now().isoformat(),
        )

    def load(self) -> bool:
        """Load saved model."""
        if os.path.exists(self.model_path):
            try:
                saved = joblib.load(self.model_path)
                self.model = saved["model"]
                self.feature_cols = saved["feature_cols"]
                self.interval = saved.get("interval", self.interval)
                self.params = saved.get("params", self.params)
                self.is_trained = True
                return True
            except Exception:
                return False
        return False
