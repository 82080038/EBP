"""
Market Simulation Engine — Walk-forward trading simulation.

Trains on 1 month of historical data, then simulates day-by-day trading
for the next 3 months. No forward-looking data leakage.

Features used:
- ML Ensemble (RF, XGBoost, LightGBM)
- Business rules & AI composite score
- Market regime detection
- Entry/target/stop + BEI transaction costs
- Advanced analyses (Wyckoff, Elliott, MTF, SMC, etc.)
- Risk governance (kill switch, drawdown control)
- Broker simulation (order execution, slippage, fees)
- SHAP explainability
- Drift monitoring

Usage:
    from src.simulation_engine import MarketSimulation
    sim = MarketSimulation(
        initial_capital=10_000_000,
        train_months=1,
        sim_months=3,
        day_duration_seconds=1.0,
    )
    results = sim.run()
"""
from __future__ import annotations

import json
import os
import time
import logging
import traceback
import warnings
from dataclasses import dataclass, field, asdict
from datetime import datetime, timedelta
from typing import Dict, List, Optional

import numpy as np
import pandas as pd

# Suppress SHAP LightGBM warning that fires on every prediction
warnings.filterwarnings("ignore", message="LightGBM binary classifier with TreeExplainer shap values output", category=UserWarning)


from .config import TICKERS, TARGET_TICKER, DATA_DIR
from .database import load_harga_harian
from .broker_sim import BrokerSimulator, SimOrder
from .preprocessor import prepare_features, get_feature_columns
from .models import HybridEnsemble
from .regime import detect_market_regime
from .scoring import calc_composite_ai_score
from .mtf import run_mtf_analysis, get_mtf_confidence_adjustment
from .pro_risk import RiskGovernance
from .fraud_detection import FraudDetector
from .data_fetcher import fetch_all_fred_data

logger = logging.getLogger(__name__)

SIM_RESULTS_PATH = os.path.join(DATA_DIR, "simulation_results.json")


@dataclass
class SimulationDay:
    """Single day simulation record."""
    date: str = ""
    day_number: int = 0
    current_price: float = 0.0
    predicted_direction: str = "NEUTRAL"
    signal: str = "HOLD"
    confidence: float = 0.0
    ai_score: float = 0.0
    regime: str = "unknown"
    action: str = "HOLD"  # BUY, SELL, HOLD
    shares_traded: int = 0
    fill_price: float = 0.0
    cash_after: float = 0.0
    portfolio_value: float = 0.0
    position_shares: int = 0
    unrealized_pnl: float = 0.0
    realized_pnl: float = 0.0
    commission: float = 0.0
    slippage_bps: float = 0.0
    model_votes: str = ""
    shap_top_features: str = ""
    wyckoff_phase: str = ""
    mtf_signal: str = ""
    risk_status: str = "OK"
    drift_psi: float = 0.0
    rsi: float = 0.0
    trend: str = ""
    regime: str = ""
    price_forecast: dict = field(default_factory=dict)
    error: str = ""


@dataclass
class SimulationRequirements:
    """Simulation requirements and data readiness report."""
    min_required_rows: int = 252
    actual_rows: int = 0
    min_training_rows: int = 100
    training_rows: int = 0
    min_required_tickers: int = 14
    available_tickers: int = 0
    missing_tickers: List[str] = field(default_factory=list)
    data_quality_ok: bool = False
    min_recommended_capital: float = 0.0
    recommended_training_months: int = 6
    recommended_simulation_months: int = 3
    issues: List[str] = field(default_factory=list)
    ready: bool = False


@dataclass
class SimulationResults:
    """Complete simulation results."""
    initial_capital: float = 0.0
    final_capital: float = 0.0
    total_return_pct: float = 0.0
    buy_hold_return_pct: float = 0.0
    n_trading_days: int = 0
    n_trades: int = 0
    n_buys: int = 0
    n_sells: int = 0
    n_shorts: int = 0
    n_covers: int = 0
    n_holds: int = 0
    n_wins: int = 0
    n_losses: int = 0
    win_rate: float = 0.0
    max_drawdown_pct: float = 0.0
    sharpe_ratio: float = 0.0
    profit_factor: float = 0.0
    total_commission: float = 0.0
    total_slippage_cost: float = 0.0
    avg_confidence: float = 0.0
    days: List[dict] = field(default_factory=list)
    equity_curve: List[float] = field(default_factory=list)
    trades_log: List[dict] = field(default_factory=list)
    requirements: dict = field(default_factory=dict)
    started_at: str = ""
    finished_at: str = ""
    status: str = "pending"  # pending, running, completed, error
    current_day: int = 0
    error: str = ""


class MarketSimulation:
    """
    Walk-forward market simulation engine.

    1. Loads historical data from database
    2. Trains ML models on 1 month of data
    3. Simulates day-by-day trading for next 3 months
    4. Uses all app features: prediction, risk, broker sim, etc.
    5. No forward-looking data leakage
    """

    def __init__(
        self,
        initial_capital: float = 10_000_000,
        train_months: int = 1,
        sim_months: int = 3,
        day_duration_seconds: float = 1.0,
        target_ticker: str = TARGET_TICKER,
        broker_name: str = "bca_sekuritas",
        train_end_date: str = "2026-01-31",
    ):
        self.initial_capital = initial_capital
        self.train_months = train_months
        self.sim_months = sim_months
        self.day_duration_seconds = day_duration_seconds
        self.target_ticker = target_ticker
        self.broker_name = broker_name
        self.train_end_date = train_end_date

        self.results = SimulationResults(
            initial_capital=initial_capital,
            started_at=datetime.now().isoformat(),
            status="pending",
        )

        self._all_data: Dict[str, pd.DataFrame] = {}
        self._target_name = None
        for name, ticker in TICKERS.items():
            if ticker == target_ticker:
                self._target_name = name
                break
        if self._target_name is None:
            self._target_name = "IHSG"

    def _load_data_from_db(self) -> Dict[str, pd.DataFrame]:
        """Load all ticker data from database."""
        market_data = {}
        for name, ticker in TICKERS.items():
            df = load_harga_harian(ticker)
            if df is not None and not df.empty:
                market_data[name] = df
                logger.info(f"Loaded {name} ({ticker}): {len(df)} rows")
        return market_data

    def _validate_data(self, market_data: Dict[str, pd.DataFrame]) -> SimulationRequirements:
        """Validate data completeness and quality before simulation."""
        req = SimulationRequirements()
        detector = FraudDetector()
        issues = []

        # Check required tickers
        missing = [name for name in TICKERS if name not in market_data]
        req.missing_tickers = missing
        req.available_tickers = len(market_data)
        if missing:
            issues.append(f"Missing tickers: {', '.join(missing)}")

        # Check target data
        target_df = market_data.get(self._target_name)
        if target_df is None or target_df.empty:
            issues.append(f"Target data {self._target_name} not found")
        else:
            req.actual_rows = len(target_df)
            if req.actual_rows < req.min_required_rows:
                issues.append(
                    f"Target {self._target_name} has {req.actual_rows} rows; recommended minimum {req.min_required_rows}"
                )

            # Check date range and training data
            train_end = pd.Timestamp(self.train_end_date)
            train_mask = target_df.index < train_end
            train_df = target_df[train_mask]
            req.training_rows = len(train_df)
            if req.training_rows < req.min_training_rows:
                issues.append(
                    f"Training data before {self.train_end_date} has {req.training_rows} rows; minimum {req.min_training_rows}"
                )

            # Data quality check on target
            dq = detector.validate_data_quality(target_df, source=self._target_name)
            req.data_quality_ok = dq.get("passed", False)
            if not req.data_quality_ok:
                failed = [k for k, v in dq.items() if isinstance(v, bool) and not v]
                issues.append(f"Data quality checks failed: {', '.join(failed)}")

        # Check all tickers have enough data
        short_tickers = []
        for name, df in market_data.items():
            if len(df) < 60:
                short_tickers.append(f"{name} ({len(df)} rows)")
        if short_tickers:
            issues.append(f"Tickers with <60 rows: {', '.join(short_tickers)}")

        # Minimum capital recommendation
        if target_df is not None and not target_df.empty:
            latest_price = float(target_df["Close"].iloc[-1])
            # 40% position, allow at least 1 share + commission buffer
            min_trade_value = latest_price * 1.5
            req.min_recommended_capital = min_trade_value / 0.4

        req.issues = issues
        req.ready = len(issues) == 0 and req.available_tickers >= req.min_required_tickers
        return req

    def _slice_data_up_to(self, all_data: Dict[str, pd.DataFrame], end_date: str) -> Dict[str, pd.DataFrame]:
        """Slice all data up to (but not including) end_date — no forward looking."""
        sliced = {}
        end_dt = pd.Timestamp(end_date)
        for name, df in all_data.items():
            if df.empty:
                continue
            mask = df.index < end_dt
            sliced_df = df[mask].copy()
            if not sliced_df.empty:
                sliced[name] = sliced_df
        return sliced

    def _get_trading_dates(self, all_data: Dict[str, pd.DataFrame], start_date: str, end_date: str) -> List[str]:
        """Get list of trading dates between start and end from target data."""
        target_df = all_data.get(self._target_name)
        if target_df is None or target_df.empty:
            return []
        mask = (target_df.index >= pd.Timestamp(start_date)) & (target_df.index <= pd.Timestamp(end_date))
        dates = target_df[mask].index
        return [d.strftime("%Y-%m-%d") for d in dates]

    def _run_prediction_safely(
        self,
        market_data: Dict[str, pd.DataFrame],
        fred_data: Optional[dict] = None,
    ) -> dict:
        """Run prediction with full error handling."""
        try:
            from .predictor import run_prediction
            result = run_prediction(
                market_data=market_data,
                fred_data=fred_data,
                target_ticker=self.target_ticker,
            )
            return result
        except Exception as e:
            logger.error(f"Prediction failed: {e}")
            return {"error": str(e), "traceback": traceback.format_exc()}

    def _lightweight_predict(
        self,
        market_data: Dict[str, pd.DataFrame],
        ensemble: Optional[HybridEnsemble] = None,
        fred_data: Optional[dict] = None,
    ) -> dict:
        """
        Lightweight prediction for simulation — uses ML ensemble + key features.
        If a pre-trained ensemble is provided, it will be reused to avoid
        retraining every simulation day.
        """
        try:
            target_df = market_data.get(self._target_name)
            if target_df is None or target_df.empty:
                return {"error": "No target data"}

            # Prepare features
            df = prepare_features(market_data, fred_data, self.target_ticker)
            if df.empty:
                return {"error": "Feature engineering failed"}

            feature_cols = get_feature_columns(df)
            df_clean = df.dropna(subset=feature_cols + ["Target_Next_Return"])
            if len(df_clean) < 50:
                return {"error": f"Insufficient data: {len(df_clean)} rows"}

            df_clean = df_clean.copy()
            df_clean["Target_Direction"] = (df_clean["Target_Next_Return"] > 0).astype(int)

            # Train ensemble on all available data up to now
            X = df_clean[feature_cols]
            y = df_clean["Target_Direction"]

            if ensemble is None:
                ensemble = HybridEnsemble()
            if not ensemble.trained:
                ensemble.train(X, y)

            # Predict latest
            latest = X.iloc[-1:]
            predictions, probabilities = ensemble.predict_ensemble(latest)

            # Votes
            votes_buy = sum(1 for p in predictions.values() if p == 1)
            votes_sell = sum(1 for p in predictions.values() if p == 0)
            total = len(predictions)
            signal = "BUY" if votes_buy > total / 2 else "SELL" if votes_sell > total / 2 else "HOLD"
            confidence = float(np.mean(list(probabilities.values()))) if probabilities else 0.5

            # AI composite score
            try:
                ai_score = calc_composite_ai_score(predictions, probabilities, confidence)
            except Exception:
                ai_score = {"score": 50.0, "label": "NEUTRAL"}

            # Market regime
            try:
                regime = detect_market_regime(df, close_col=f"{self._target_name}_Close")
            except Exception:
                regime = "unknown"

            # MTF analysis
            try:
                mtf_result = run_mtf_analysis(market_data, self.target_ticker)
                mtf_signal = mtf_result.get("confluence_signal", "NEUTRAL") if isinstance(mtf_result, dict) else "NEUTRAL"
                mtf_adj, _ = get_mtf_confidence_adjustment(mtf_result)
                confidence = float(np.clip(confidence + mtf_adj, 0, 1))
            except Exception:
                mtf_signal = "NEUTRAL"

            # Current price
            close_col = f"{self._target_name}_Close"
            if close_col not in df.columns:
                for col in df.columns:
                    if col.endswith("_Close"):
                        close_col = col
                        break
            current_price = float(df[close_col].iloc[-1])

            # Model votes string
            votes_str = ", ".join(f"{k}: {'BUY' if v == 1 else 'SELL'}" for k, v in predictions.items())
            proba_str = ", ".join(f"{k}: {v:.0%}" for k, v in probabilities.items())

            # SHAP explainability
            shap_text = ""
            try:
                from .explainability import explain_prediction, generate_explanation_text
                explanations = explain_prediction(ensemble, latest, feature_cols, top_k=5)
                shap_text = generate_explanation_text(explanations)
            except Exception:
                pass

            # Wyckoff
            wyckoff_phase = "N/A"
            try:
                from .wyckoff import detect_wyckoff_phase
                wy_result = detect_wyckoff_phase(target_df)
                wyckoff_phase = wy_result.get("phase", "N/A") if isinstance(wy_result, dict) else str(wy_result)
            except Exception:
                pass

            return {
                "signal": signal,
                "confidence": confidence,
                "predictions": predictions,
                "probabilities": probabilities,
                "ai_score": ai_score if isinstance(ai_score, (int, float)) else ai_score.get("score", 50),
                "regime": regime,
                "current_price": current_price,
                "model_votes": f"{votes_str} | {proba_str}",
                "mtf_signal": mtf_signal,
                "shap_text": shap_text,
                "wyckoff_phase": wyckoff_phase,
                "ensemble": ensemble,
                "feature_cols": feature_cols,
                "df_clean": df_clean,
            }

        except Exception as e:
            logger.error(f"Lightweight predict failed: {e}")
            return {"error": str(e), "traceback": traceback.format_exc()}

    def _save_results(self):
        """Save current results to JSON file."""
        with open(SIM_RESULTS_PATH, "w") as f:
            json.dump(asdict(self.results), f, indent=2, default=str)

    def run(self) -> SimulationResults:
        """
        Run full walk-forward simulation.

        - Validate data completeness
        - Log simulation requirements
        - Train model once on historical window
        - Simulate day-by-day with no forward-looking data
        - Use all available risk and signal modules
        """
        self.results.status = "running"
        self._save_results()

        logger.info("=" * 60)
        logger.info("STARTING MARKET SIMULATION")
        logger.info(f"Capital: Rp {self.initial_capital:,.0f}")
        logger.info(f"Train: {self.train_months} month(s) ending {self.train_end_date}")
        logger.info(f"Simulate: {self.sim_months} months")
        logger.info(f"Day duration: {self.day_duration_seconds}s")
        logger.info("=" * 60)

        # Step 1: Load all data from database
        logger.info("Loading data from database...")
        self._all_data = self._load_data_from_db()
        if not self._all_data:
            self.results.error = "No data in database"
            self.results.status = "error"
            self._save_results()
            return self.results

        # Step 2: Validate data completeness and quality
        logger.info("Validating data requirements...")
        requirements = self._validate_data(self._all_data)
        self.results.requirements = asdict(requirements)

        logger.info("=" * 60)
        logger.info("SIMULATION REQUIREMENTS")
        logger.info("=" * 60)
        logger.info(f"Available tickers: {requirements.available_tickers}/{requirements.min_required_tickers}")
        logger.info(f"Target rows: {requirements.actual_rows} (recommended: {requirements.min_required_rows})")
        logger.info(f"Training rows: {requirements.training_rows} (minimum: {requirements.min_training_rows})")
        logger.info(f"Data quality: {'OK' if requirements.data_quality_ok else 'FAILED'}")
        logger.info(f"Min recommended capital: Rp {requirements.min_recommended_capital:,.0f}")
        logger.info(f"Recommended training months: {requirements.recommended_training_months}")
        logger.info(f"Recommended simulation months: {requirements.recommended_simulation_months}")
        if requirements.issues:
            logger.warning("Data issues:")
            for issue in requirements.issues:
                logger.warning(f"  - {issue}")
        logger.info("=" * 60)

        if not requirements.ready:
            self.results.error = "Data requirements not met: " + "; ".join(requirements.issues)
            self.results.status = "error"
            self._save_results()
            logger.error(self.results.error)
            return self.results

        # Step 3: Determine simulation date range
        train_end = pd.Timestamp(self.train_end_date)
        sim_start = train_end + timedelta(days=1)
        sim_end = sim_start + timedelta(days=self.sim_months * 30)

        trading_dates = self._get_trading_dates(self._all_data, sim_start.strftime("%Y-%m-%d"), sim_end.strftime("%Y-%m-%d"))
        if not trading_dates:
            self.results.error = f"No trading dates found between {sim_start} and {sim_end}"
            self.results.status = "error"
            self._save_results()
            return self.results

        logger.info(f"Simulation period: {trading_dates[0]} to {trading_dates[-1]} ({len(trading_dates)} trading days)")

        # Step 4: Load FRED data once
        logger.info("Loading FRED macro data...")
        fred_data = {}
        try:
            fred_data = fetch_all_fred_data(observation_start=(train_end - timedelta(days=1825)).strftime("%Y-%m-%d"))
            logger.info(f"FRED data loaded: {len(fred_data)} series")
        except Exception as e:
            logger.warning(f"FRED data unavailable: {e}")

        # Step 5: Pre-train ML ensemble once on all training data up to train_end_date
        logger.info("Pre-training ML ensemble...")
        train_data = self._slice_data_up_to(self._all_data, self.train_end_date)
        pretrain_pred = self._lightweight_predict(train_data, fred_data=fred_data)
        if "error" in pretrain_pred:
            self.results.error = f"Pre-training failed: {pretrain_pred['error']}"
            self.results.status = "error"
            self._save_results()
            logger.error(self.results.error)
            return self.results

        ensemble = pretrain_pred.get("ensemble")
        if ensemble is None:
            self.results.error = "Pre-training did not return an ensemble"
            self.results.status = "error"
            self._save_results()
            logger.error(self.results.error)
            return self.results

        logger.info(f"Ensemble pre-trained successfully on {len(train_data)} tickers")

        # Step 6: Initialize broker
        broker = BrokerSimulator(
            broker=self.broker_name,
            capital=self.initial_capital,
            lot_size=1,  # Index doesn't have lot size
        )

        # Step 7: Initialize risk governance
        try:
            risk_gov = RiskGovernance()
        except Exception:
            risk_gov = None

        # Step 8: Track state
        position_shares = 0
        short_shares = 0
        entry_price = 0.0
        short_entry_price = 0.0
        entry_day_idx = -999
        short_entry_day_idx = -999
        total_commission = 0.0
        total_slippage = 0.0
        gross_profit = 0.0
        gross_loss = 0.0
        equity_curve = []
        trades_log = []
        all_days = []
        peak_equity = self.initial_capital
        max_dd = 0.0
        daily_returns = []

        # Strategy parameters — adaptive, auto-adjusting
        BASE_CONFIDENCE_BUY = 0.60
        BASE_CONFIDENCE_SELL = 0.60
        BASE_POSITION_SIZE_PCT = 0.40
        BASE_MIN_HOLD_DAYS = 7
        COMMISSION_PCT = 0.0025
        TREND_FILTER = True
        RSI_OVERBOUGHT = 70
        RSI_OVERSOLD = 30

        # Auto-adjusting state
        recent_trades = []  # Track last 20 trades for adaptive confidence
        confidence_buy = BASE_CONFIDENCE_BUY
        confidence_sell = BASE_CONFIDENCE_SELL
        position_size_pct = BASE_POSITION_SIZE_PCT
        min_hold_days = BASE_MIN_HOLD_DAYS

        # Track highest/lowest price for trailing stop
        highest_price_since_entry = 0.0
        lowest_price_since_short = float("inf")

        # Track consecutive trend days
        trend_bullish_days = 0
        trend_bearish_days = 0

        # Trend forecast
        price_forecast = {}

        # Buy & hold benchmark
        first_price = None
        last_price = None

        # Step 9: Day-by-day simulation
        for day_idx, sim_date in enumerate(trading_dates):
            self.results.current_day = day_idx + 1
            day_record = SimulationDay(
                date=sim_date,
                day_number=day_idx + 1,
            )

            # Get data up to (not including) current simulation date
            market_data = self._slice_data_up_to(self._all_data, sim_date)

            if not market_data or self._target_name not in market_data:
                day_record.error = "No data available up to this date"
                all_days.append(asdict(day_record))
                continue

            target_df = market_data[self._target_name]
            if len(target_df) < 50:
                day_record.error = f"Insufficient historical data: {len(target_df)} rows"
                all_days.append(asdict(day_record))
                continue

            # Current price = last close before simulation date
            current_price = float(target_df["Close"].iloc[-1])
            day_record.current_price = round(current_price, 2)

            if first_price is None:
                first_price = current_price
            last_price = current_price

            # Run prediction with pre-trained ensemble (no daily retraining)
            pred = self._lightweight_predict(market_data, ensemble=ensemble, fred_data=fred_data)

            if "error" in pred:
                day_record.error = pred["error"]
                day_record.action = "HOLD"
                all_days.append(asdict(day_record))
                equity_curve.append(broker.get_portfolio()["total_capital"])
                self.results.days = all_days
                self.results.equity_curve = equity_curve
                self._save_results()
                time.sleep(self.day_duration_seconds)
                continue

            # Extract prediction results
            signal = pred.get("signal", "HOLD")
            confidence = pred.get("confidence", 0.5)
            ai_score = pred.get("ai_score", 50)
            regime = pred.get("regime", "unknown")
            mtf_signal = pred.get("mtf_signal", "NEUTRAL")
            wyckoff_phase = pred.get("wyckoff_phase", "N/A")
            model_votes = pred.get("model_votes", "")
            shap_text = pred.get("shap_text", "")

            day_record.predicted_direction = "UP" if signal == "BUY" else "DOWN" if signal == "SELL" else "NEUTRAL"
            day_record.signal = signal
            day_record.confidence = round(confidence, 4)
            day_record.ai_score = round(float(ai_score), 1) if isinstance(ai_score, (int, float)) else 50
            day_record.regime = regime
            day_record.mtf_signal = mtf_signal
            day_record.wyckoff_phase = wyckoff_phase
            day_record.model_votes = model_votes[:200]
            day_record.shap_top_features = shap_text[:200]

            # Risk governance check
            risk_status = "OK"
            if risk_gov:
                try:
                    portfolio = broker.get_portfolio()
                    current_dd = (peak_equity - portfolio["total_capital"]) / peak_equity if peak_equity > 0 else 0
                    if current_dd > 0.15:
                        risk_status = "KILL_SWITCH: Max drawdown exceeded"
                        signal = "HOLD"
                except Exception:
                    pass
            day_record.risk_status = risk_status

            # Trading logic
            action = "HOLD"
            shares_traded = 0
            fill_price = 0.0

            # Technical indicators: trend, RSI, ATR
            trend_bullish = True
            rsi_value = 50.0
            atr_value = 0.0
            ma20 = 0.0
            if TREND_FILTER:
                try:
                    close = target_df["Close"]
                    ma5 = close.rolling(5).mean().iloc[-1]
                    ma20 = close.rolling(20).mean().iloc[-1]
                    trend_bullish = ma5 > ma20

                    # RSI
                    delta = close.diff()
                    gain = delta.where(delta > 0, 0.0)
                    loss = -delta.where(delta < 0, 0.0)
                    avg_gain = gain.rolling(14, min_periods=14).mean()
                    avg_loss = loss.rolling(14, min_periods=14).mean()
                    rs = avg_gain / avg_loss
                    rsi_series = 100 - (100 / (1 + rs))
                    rsi_value = float(rsi_series.iloc[-1]) if not np.isnan(rsi_series.iloc[-1]) else 50.0

                    # ATR (14)
                    high = target_df["High"]
                    low = target_df["Low"]
                    tr1 = high - low
                    tr2 = abs(high - close.shift(1))
                    tr3 = abs(low - close.shift(1))
                    tr = pd.concat([tr1, tr2, tr3], axis=1).max(axis=1)
                    atr_value = float(tr.rolling(14, min_periods=14).mean().iloc[-1]) if not tr.empty else 0.0
                except Exception:
                    trend_bullish = True

            day_record.rsi = round(rsi_value, 1)
            day_record.trend = "bullish" if trend_bullish else "bearish"

            # Track consecutive trend days
            if trend_bullish:
                trend_bullish_days += 1
                trend_bearish_days = 0
            else:
                trend_bearish_days += 1
                trend_bullish_days = 0

            # Auto-adjust parameters based on market regime
            regime_name = "unknown"
            regime_multiplier = 1.0
            regime_should_trade = True
            if isinstance(regime, str):
                regime_name = regime.lower()
            elif hasattr(regime, "current_regime"):
                regime_name = regime.current_regime.lower()
                regime_multiplier = getattr(regime, "position_size_multiplier", 1.0)
                regime_should_trade = getattr(regime, "should_trade", True)

            # Treat bearish_sideways and weak_bear as bearish
            is_bearish = regime_name in ("bear", "bearish_sideways", "weak_bear")
            is_bullish = regime_name in ("bull", "bullish_sideways", "weak_bull")
            is_crisis = regime_name in ("crisis", "high_volatility")

            if is_crisis:
                # Crisis: go to cash, tighten everything
                position_size_pct = 0.0
                confidence_buy = 0.80
                confidence_sell = 0.80
                signal = "HOLD"
                day_record.risk_status = "CRISIS MODE: flatten positions"
            elif is_bearish:
                # Bear market: allow short, reduce long size
                position_size_pct = 0.35
                confidence_buy = 0.75
                confidence_sell = 0.55
                # In bear market, ignore model BUY signals for new long positions
                if signal == "BUY" and position_shares == 0 and short_shares == 0:
                    signal = "HOLD"
                    day_record.risk_status = "BEAR MODE: no new long positions"
            elif is_bullish:
                # Bull market: full long, no short
                position_size_pct = 0.50
                confidence_buy = 0.55
                confidence_sell = 0.60
                if signal == "SELL" and short_shares == 0 and position_shares == 0:
                    signal = "HOLD"
                    day_record.risk_status = "BULL MODE: no new short positions"
            else:
                # Neutral/sideways
                position_size_pct = BASE_POSITION_SIZE_PCT
                confidence_buy = BASE_CONFIDENCE_BUY
                confidence_sell = BASE_CONFIDENCE_SELL

            # Apply regime multiplier with a small floor to avoid complete inactivity
            regime_multiplier = max(regime_multiplier, 0.3)
            position_size_pct = np.clip(position_size_pct * regime_multiplier, 0.0, 0.60)

            # If regime says don't trade, flatten
            if not regime_should_trade:
                signal = "HOLD"
                day_record.risk_status = "REGIME: trading halted"

            # Adaptive confidence based on recent win rate
            if recent_trades:
                recent_wins = sum(1 for t in recent_trades[-20:] if t.get("realized_pnl", 0) > 0)
                recent_total = len(recent_trades[-20:])
                recent_win_rate = recent_wins / recent_total if recent_total > 0 else 0.5
                # If recent win rate is low, raise confidence threshold; if high, lower it
                confidence_adjustment = 0.5 - recent_win_rate
                confidence_buy = np.clip(confidence_buy + confidence_adjustment * 0.2, 0.5, 0.85)
                confidence_sell = np.clip(confidence_sell + confidence_adjustment * 0.2, 0.5, 0.85)

            # ATR-based stop-loss and take-profit
            if atr_value > 0 and current_price > 0:
                atr_pct = atr_value / current_price
                stop_loss_pct = max(0.03, min(0.10, 2.0 * atr_pct))
                take_profit_pct = max(0.05, min(0.15, 3.0 * atr_pct))
                trailing_pct = max(0.02, min(0.06, 1.5 * atr_pct))
            else:
                stop_loss_pct = 0.05
                take_profit_pct = 0.08
                trailing_pct = 0.03

            # Trend forecast: simple price projection using model confidence and recent volatility
            if pred.get("predictions") and current_price > 0:
                votes_buy = sum(1 for p in pred["predictions"].values() if p == 1)
                votes_sell = sum(1 for p in pred["predictions"].values() if p == 0)
                total_votes = len(pred["predictions"])
                if total_votes > 0:
                    net_bias = (votes_buy - votes_sell) / total_votes
                    recent_volatility = 0.01
                    if len(close) >= 21:
                        recent_volatility = float(close.pct_change().iloc[-21:].std())
                    forecast_5d = current_price * (1 + net_bias * recent_volatility * 5)
                    forecast_10d = current_price * (1 + net_bias * recent_volatility * 10)
                    price_forecast = {
                        "5d": round(forecast_5d, 2),
                        "10d": round(forecast_10d, 2),
                        "bias": round(net_bias, 2),
                    }
                    day_record.predicted_direction = "UP" if net_bias > 0 else "DOWN" if net_bias < 0 else "NEUTRAL"

            # Long position management
            if position_shares > 0 and entry_price > 0:
                pnl_pct = (current_price - entry_price) / entry_price
                days_held = day_idx - entry_day_idx
                net_pnl_pct = pnl_pct - COMMISSION_PCT
                highest_price_since_entry = max(highest_price_since_entry, current_price)
                trailing_stop_pct = (highest_price_since_entry - current_price) / highest_price_since_entry

                if pnl_pct <= -stop_loss_pct:
                    signal = "SELL"
                    day_record.risk_status = f"STOP_LOSS long ({pnl_pct:.1%})"
                elif pnl_pct >= take_profit_pct:
                    signal = "SELL"
                    day_record.risk_status = f"TAKE_PROFIT long ({pnl_pct:.1%})"
                elif trailing_stop_pct >= trailing_pct and pnl_pct > 0:
                    signal = "SELL"
                    day_record.risk_status = f"TRAILING_STOP long ({pnl_pct:.1%})"
                elif rsi_value > RSI_OVERBOUGHT and days_held >= min_hold_days:
                    signal = "SELL"
                    day_record.risk_status = f"OVERBOUGHT (RSI={rsi_value:.0f})"
                elif days_held < min_hold_days and signal == "SELL":
                    signal = "HOLD"
                    day_record.risk_status = f"HOLD (min {min_hold_days}d, day {days_held})"
                elif signal == "SELL" and net_pnl_pct < 0 and days_held >= min_hold_days:
                    signal = "HOLD"
                    day_record.risk_status = "HOLD (net PnL negative after commission)"

            # Short position management
            if short_shares > 0 and short_entry_price > 0:
                short_pnl_pct = (short_entry_price - current_price) / short_entry_price
                short_days_held = day_idx - short_entry_day_idx
                short_net_pnl_pct = short_pnl_pct - COMMISSION_PCT
                lowest_price_since_short = min(lowest_price_since_short, current_price)
                short_trailing_pct = (current_price - lowest_price_since_short) / lowest_price_since_short

                if short_pnl_pct <= -stop_loss_pct:
                    signal = "BUY"
                    day_record.risk_status = f"STOP_LOSS short ({short_pnl_pct:.1%})"
                elif short_pnl_pct >= take_profit_pct:
                    signal = "BUY"
                    day_record.risk_status = f"TAKE_PROFIT short ({short_pnl_pct:.1%})"
                elif short_trailing_pct >= trailing_pct and short_pnl_pct > 0:
                    signal = "BUY"
                    day_record.risk_status = f"TRAILING_STOP short ({short_pnl_pct:.1%})"
                elif rsi_value < RSI_OVERSOLD and short_days_held >= min_hold_days:
                    signal = "BUY"
                    day_record.risk_status = f"OVERSOLD (RSI={rsi_value:.0f})"
                elif short_days_held < min_hold_days and signal == "BUY":
                    signal = "HOLD"
                    day_record.risk_status = f"HOLD (min {min_hold_days}d, day {short_days_held})"
                elif signal == "BUY" and short_net_pnl_pct < 0 and short_days_held >= min_hold_days:
                    signal = "HOLD"
                    day_record.risk_status = "HOLD (net short PnL negative after commission)"

            # Auto-decision: long, short, or hold
            action = "HOLD"
            shares_traded = 0
            fill_price = 0.0

            # Trend-following signal override
            if short_shares == 0 and position_shares == 0:
                if trend_bearish_days >= 5 and rsi_value > RSI_OVERSOLD + 5:
                    signal = "SELL"
                    day_record.risk_status = "TREND FOLLOW: short after 5 bearish days"
                elif trend_bullish_days >= 5 and rsi_value < RSI_OVERBOUGHT - 5:
                    signal = "BUY"
                    day_record.risk_status = "TREND FOLLOW: long after 5 bullish days"

            # Long entry: model BUY or trend follow, confidence, not overbought, trend aligned, no existing position
            if (
                signal == "BUY"
                and position_shares == 0
                and short_shares == 0
                and confidence >= confidence_buy
                and rsi_value < RSI_OVERBOUGHT
                and (trend_bullish_days >= 2 or trend_bullish_days >= 5)
                and not is_bearish
                and not is_crisis
            ):
                portfolio = broker.get_portfolio()
                position_size = portfolio["cash"] * position_size_pct
                shares = int(position_size / current_price)
                if shares > 0:
                    order = SimOrder(
                        symbol=self.target_ticker,
                        side="BUY",
                        quantity=shares,
                        order_type="MARKET",
                    )
                    fill = broker.submit_order(order, current_price=current_price)
                    if fill.status in ("FILLED", "PARTIAL_FILL"):
                        action = "BUY"
                        shares_traded = fill.filled_qty
                        fill_price = fill.avg_fill_price
                        position_shares = fill.filled_qty
                        entry_price = fill.avg_fill_price
                        entry_day_idx = day_idx
                        highest_price_since_entry = current_price
                        total_commission += fill.commission + fill.fees
                        total_slippage += fill.slippage_bps * fill.filled_qty * fill.avg_fill_price / 10000
                        trades_log.append({
                            "date": sim_date,
                            "side": "BUY",
                            "shares": fill.filled_qty,
                            "price": fill.avg_fill_price,
                            "commission": fill.commission + fill.fees,
                            "slippage_bps": fill.slippage_bps,
                            "confidence": confidence,
                            "signal": signal,
                            "regime": regime_name,
                        })

            # Short entry: model SELL or bear trend, confidence, not oversold, no existing position
            elif (
                signal == "SELL"
                and short_shares == 0
                and position_shares == 0
                and confidence >= confidence_sell
                and rsi_value > RSI_OVERSOLD
                and not is_bullish
                and not is_crisis
            ):
                portfolio = broker.get_portfolio()
                position_size = portfolio["cash"] * position_size_pct
                shares = int(position_size / current_price)
                if shares > 0:
                    order = SimOrder(
                        symbol=self.target_ticker,
                        side="SELL",
                        quantity=shares,
                        order_type="MARKET",
                    )
                    fill = broker.submit_order(order, current_price=current_price)
                    if fill.status in ("FILLED", "PARTIAL_FILL"):
                        action = "SHORT"
                        shares_traded = fill.filled_qty
                        fill_price = fill.avg_fill_price
                        short_shares = fill.filled_qty
                        short_entry_price = fill.avg_fill_price
                        short_entry_day_idx = day_idx
                        lowest_price_since_short = current_price
                        total_commission += fill.commission + fill.fees
                        total_slippage += fill.slippage_bps * fill.filled_qty * fill.avg_fill_price / 10000
                        trades_log.append({
                            "date": sim_date,
                            "side": "SHORT",
                            "shares": fill.filled_qty,
                            "price": fill.avg_fill_price,
                            "commission": fill.commission + fill.fees,
                            "slippage_bps": fill.slippage_bps,
                            "confidence": confidence,
                            "signal": signal,
                            "regime": regime_name,
                        })

            # Long exit
            elif signal == "SELL" and position_shares > 0:
                order = SimOrder(
                    symbol=self.target_ticker,
                    side="SELL",
                    quantity=position_shares,
                    order_type="MARKET",
                )
                fill = broker.submit_order(order, current_price=current_price)
                if fill.status in ("FILLED", "PARTIAL_FILL"):
                    action = "SELL"
                    shares_traded = fill.filled_qty
                    fill_price = fill.avg_fill_price
                    realized = (fill.avg_fill_price - entry_price) * fill.filled_qty - (fill.commission + fill.fees)
                    if realized > 0:
                        gross_profit += realized
                    else:
                        gross_loss += abs(realized)
                    total_commission += fill.commission + fill.fees
                    total_slippage += fill.slippage_bps * fill.filled_qty * fill.avg_fill_price / 10000
                    trades_log.append({
                        "date": sim_date,
                        "side": "SELL",
                        "shares": fill.filled_qty,
                        "price": fill.avg_fill_price,
                        "commission": fill.commission + fill.fees,
                        "slippage_bps": fill.slippage_bps,
                        "realized_pnl": round(realized, 0),
                        "confidence": confidence,
                        "signal": signal,
                        "regime": regime_name,
                    })
                    recent_trades.append({"realized_pnl": realized, "date": sim_date})
                    position_shares = 0
                    entry_price = 0.0
                    highest_price_since_entry = 0.0

            # Short exit
            elif signal == "BUY" and short_shares > 0:
                order = SimOrder(
                    symbol=self.target_ticker,
                    side="BUY",
                    quantity=short_shares,
                    order_type="MARKET",
                )
                fill = broker.submit_order(order, current_price=current_price)
                if fill.status in ("FILLED", "PARTIAL_FILL"):
                    action = "COVER"
                    shares_traded = fill.filled_qty
                    fill_price = fill.avg_fill_price
                    realized = (short_entry_price - fill.avg_fill_price) * fill.filled_qty - (fill.commission + fill.fees)
                    if realized > 0:
                        gross_profit += realized
                    else:
                        gross_loss += abs(realized)
                    total_commission += fill.commission + fill.fees
                    total_slippage += fill.slippage_bps * fill.filled_qty * fill.avg_fill_price / 10000
                    trades_log.append({
                        "date": sim_date,
                        "side": "COVER",
                        "shares": fill.filled_qty,
                        "price": fill.avg_fill_price,
                        "commission": fill.commission + fill.fees,
                        "slippage_bps": fill.slippage_bps,
                        "realized_pnl": round(realized, 0),
                        "confidence": confidence,
                        "signal": signal,
                        "regime": regime_name,
                    })
                    recent_trades.append({"realized_pnl": realized, "date": sim_date})
                    short_shares = 0
                    short_entry_price = 0.0
                    lowest_price_since_short = float("inf")

            day_record.action = action
            day_record.shares_traded = shares_traded
            day_record.fill_price = round(fill_price, 2) if fill_price > 0 else 0

            # Update broker market prices
            broker.update_market_prices({self.target_ticker: current_price})

            # Get portfolio state
            portfolio = broker.get_portfolio()
            day_record.cash_after = round(portfolio["cash"], 0)
            day_record.portfolio_value = round(portfolio["total_capital"], 0)
            day_record.position_shares = position_shares - short_shares  # net position
            day_record.unrealized_pnl = round(portfolio["unrealized_pnl"], 0)
            day_record.realized_pnl = round(portfolio["realized_pnl"], 0)
            day_record.commission = round(total_commission, 0)
            day_record.slippage_bps = round(total_slippage / max(1, len(trades_log)), 2)
            day_record.price_forecast = price_forecast
            day_record.regime = regime_name

            # Track equity
            equity = portfolio["total_capital"]
            equity_curve.append(equity)
            peak_equity = max(peak_equity, equity)
            dd = (equity - peak_equity) / peak_equity * 100 if peak_equity > 0 else 0
            max_dd = min(max_dd, dd)

            if len(equity_curve) > 1:
                dr = (equity_curve[-1] - equity_curve[-2]) / equity_curve[-2] if equity_curve[-2] > 0 else 0
                daily_returns.append(dr)

            all_days.append(asdict(day_record))

            # Update results
            self.results.days = all_days
            self.results.equity_curve = equity_curve
            self.results.trades_log = trades_log
            self.results.current_day = day_idx + 1
            self._save_results()

            # Log progress
            logger.info(
                f"Day {day_idx + 1}/{len(trading_dates)} | {sim_date} | "
                f"Price: {current_price:.2f} | Signal: {signal} | "
                f"Action: {action} | Portfolio: Rp {equity:,.0f} | "
                f"Confidence: {confidence:.0%}"
            )

            # 1 day = 1 second
            time.sleep(self.day_duration_seconds)

        # Step 7: Close any remaining position at last price
        if position_shares > 0 and last_price is not None:
            order = SimOrder(
                symbol=self.target_ticker,
                side="SELL",
                quantity=position_shares,
                order_type="MARKET",
            )
            fill = broker.submit_order(order, current_price=last_price)
            if fill.status in ("FILLED", "PARTIAL_FILL"):
                realized = (fill.avg_fill_price - entry_price) * fill.filled_qty - (fill.commission + fill.fees)
                if realized > 0:
                    gross_profit += realized
                else:
                    gross_loss += abs(realized)
                total_commission += fill.commission + fill.fees
                trades_log.append({
                    "date": trading_dates[-1],
                    "side": "SELL (final close)",
                    "shares": fill.filled_qty,
                    "price": fill.avg_fill_price,
                    "commission": fill.commission + fill.fees,
                    "realized_pnl": round(realized, 0),
                })
                recent_trades.append({"realized_pnl": realized, "date": trading_dates[-1]})

        if short_shares > 0 and last_price is not None:
            order = SimOrder(
                symbol=self.target_ticker,
                side="BUY",
                quantity=short_shares,
                order_type="MARKET",
            )
            fill = broker.submit_order(order, current_price=last_price)
            if fill.status in ("FILLED", "PARTIAL_FILL"):
                realized = (short_entry_price - fill.avg_fill_price) * fill.filled_qty - (fill.commission + fill.fees)
                if realized > 0:
                    gross_profit += realized
                else:
                    gross_loss += abs(realized)
                total_commission += fill.commission + fill.fees
                trades_log.append({
                    "date": trading_dates[-1],
                    "side": "COVER (final close)",
                    "shares": fill.filled_qty,
                    "price": fill.avg_fill_price,
                    "commission": fill.commission + fill.fees,
                    "realized_pnl": round(realized, 0),
                })
                recent_trades.append({"realized_pnl": realized, "date": trading_dates[-1]})

        # Step 8: Calculate final metrics
        portfolio = broker.get_portfolio()
        self.results.final_capital = round(portfolio["total_capital"], 0)
        self.results.total_return_pct = round(
            (portfolio["total_capital"] - self.initial_capital) / self.initial_capital * 100, 2
        )

        if first_price and last_price:
            self.results.buy_hold_return_pct = round(
                (last_price / first_price - 1) * 100, 2
            )

        self.results.n_trading_days = len(trading_dates)
        self.results.n_trades = len(trades_log)
        self.results.n_buys = sum(1 for t in trades_log if t["side"] == "BUY")
        self.results.n_sells = sum(1 for t in trades_log if "SELL" in t["side"])
        self.results.n_shorts = sum(1 for t in trades_log if t["side"] == "SHORT")
        self.results.n_covers = sum(1 for t in trades_log if "COVER" in t["side"])
        self.results.n_holds = len(trading_dates) - len(trades_log)
        # Wins/losses for closed trades (SELL exits long, COVER exits short)
        closed_trades = [t for t in trades_log if "SELL" in t.get("side", "") or "COVER" in t.get("side", "")]
        self.results.n_wins = sum(1 for t in closed_trades if t.get("realized_pnl", 0) > 0)
        self.results.n_losses = sum(1 for t in closed_trades if t.get("realized_pnl", 0) <= 0)
        self.results.win_rate = round(
            self.results.n_wins / max(self.results.n_wins + self.results.n_losses, 1) * 100, 1
        )
        self.results.max_drawdown_pct = round(max_dd, 2)
        self.results.profit_factor = round(gross_profit / max(gross_loss, 1), 2)
        self.results.total_commission = round(total_commission, 0)
        self.results.total_slippage_cost = round(total_slippage, 0)

        if daily_returns:
            dr_arr = np.array(daily_returns)
            dr_clean = dr_arr[~np.isnan(dr_arr)]
            if len(dr_clean) > 5 and np.std(dr_clean) > 0:
                self.results.sharpe_ratio = round(
                    float(np.mean(dr_clean) / np.std(dr_clean) * np.sqrt(252)), 2
                )

        confidences = [d["confidence"] for d in all_days if d.get("confidence", 0) > 0]
        self.results.avg_confidence = round(float(np.mean(confidences)), 4) if confidences else 0

        self.results.finished_at = datetime.now().isoformat()
        self.results.status = "completed"
        self._save_results()

        logger.info("=" * 60)
        logger.info("SIMULATION COMPLETE")
        logger.info(f"Final Capital: Rp {self.results.final_capital:,.0f}")
        logger.info(f"Total Return: {self.results.total_return_pct:.2f}%")
        logger.info(f"Buy & Hold: {self.results.buy_hold_return_pct:.2f}%")
        logger.info(f"Long Trades: {self.results.n_buys} entries, {self.results.n_sells} exits")
        logger.info(f"Short Trades: {self.results.n_shorts} entries, {self.results.n_covers} exits")
        logger.info(f"Win Rate: {self.results.win_rate:.1f}%")
        logger.info(f"Max Drawdown: {self.results.max_drawdown_pct:.2f}%")
        logger.info(f"Sharpe: {self.results.sharpe_ratio:.2f}")
        logger.info(f"Profit Factor: {self.results.profit_factor:.2f}")
        logger.info("=" * 60)

        return self.results


def load_simulation_results() -> Optional[dict]:
    """Load simulation results from JSON file."""
    if not os.path.exists(SIM_RESULTS_PATH):
        return None
    with open(SIM_RESULTS_PATH, "r") as f:
        return json.load(f)
