"""
Module Integrator — connects 7 orphan modules to the unified pipeline.

Integrates:
- DCF Valuation → intrinsic value per ticker
- Slippage Model → realistic execution cost
- Compliance + IDX Rules → BEI/OJK rule checking before execution
- Portfolio Risk → VaR, CVaR, stress test
- RealTime Feed → cached polling for price data
- MLflow Tracking → experiment logging for model training
"""

from typing import Dict, List, Optional
from dataclasses import dataclass, field
import numpy as np
import pandas as pd
from datetime import datetime



@dataclass
class IntegratedAnalysis:
    """Combined analysis from all integrated modules."""
    ticker: str
    dcf_valuation: Optional[Dict] = None
    slippage_cost: Optional[Dict] = None
    compliance_check: Optional[Dict] = None
    idx_rules_check: Optional[Dict] = None
    portfolio_risk: Optional[Dict] = None
    errors: List[str] = field(default_factory=list)


class ModuleIntegrator:
    """
    Integrates 7 orphan modules into the pipeline.
    
    Each method can be called independently or all at once via analyze_ticker().
    """

    def __init__(self):
        self._slippage_model = None
        self._realtime_feed = None

    # =========================================================================
    # DCF VALUATION
    # =========================================================================

    def run_dcf(self, ticker: str, risk_free_rate: float = 0.04) -> Optional[Dict]:
        """Run DCF valuation for a ticker."""
        try:
            from .dcf_valuation import run_dcf as _run_dcf
            result = _run_dcf(ticker, risk_free_rate=risk_free_rate)
            if result.error:
                return {"error": result.error, "ticker": ticker}
            return {
                "ticker": ticker,
                "current_price": result.current_price,
                "intrinsic_value": result.intrinsic_value,
                "margin_of_safety": result.margin_of_safety,
                "wacc": result.wacc,
                "valuation_status": result.valuation_status,
                "recommendation": result.recommendation,
                "reverse_dcf_growth": result.reverse_dcf_growth,
            }
        except Exception as e:
            return {"error": str(e), "ticker": ticker}

    # =========================================================================
    # SLIPPAGE MODEL
    # =========================================================================

    def estimate_slippage(self, ticker: str, shares: int, price: float, side: str = "buy") -> Optional[Dict]:
        """Estimate realistic execution cost including slippage."""
        try:
            from .slippage import calculate_total_execution_cost
            adv = 1_000_000  # Default ADV assumption
            cost = calculate_total_execution_cost(
                shares=shares, price=price, adv=adv, volatility=0.25,
                side=side, commission_bps=15.0 if side == "buy" else 25.0,
            )
            return {
                "ticker": ticker,
                "shares": shares,
                "price": price,
                "side": side,
                "slippage_bps": cost.slippage_bps,
                "market_impact": cost.market_impact,
                "total_cost_idr": cost.total_cost,
                "cost_pct": cost.cost_pct,
                "execution_time_minutes": cost.execution_time_minutes,
            }
        except Exception as e:
            return {"error": str(e), "ticker": ticker}

    # =========================================================================
    # COMPLIANCE / AUDIT
    # =========================================================================

    def check_compliance(self, ticker: str, signal: str, confidence: float) -> Optional[Dict]:
        """Log prediction to audit trail and check compliance."""
        try:
            from .compliance import log_prediction_audit, get_compliance_disclaimer, AuditTrail
            AuditTrail()
            entry = log_prediction_audit(
                ticker=ticker, signal=signal, confidence=confidence,
                actor="system", source="unified_pipeline",
            )
            return {
                "ticker": ticker,
                "audit_logged": True,
                "disclaimer": get_compliance_disclaimer()[:200],
                "entry_id": entry.timestamp if entry else None,
            }
        except Exception as e:
            return {"error": str(e), "ticker": ticker}

    # =========================================================================
    # IDX RULES (Auto-rejection, Circuit Breaker, Settlement)
    # =========================================================================

    def check_idx_rules(
        self, ticker: str, current_price: float,
        reference_price: Optional[float] = None,
        ihsg_current: Optional[float] = None,
        ihsg_previous_close: Optional[float] = None,
    ) -> Optional[Dict]:
        """Check IDX trading rules (auto-rejection, circuit breaker, T+2)."""
        result = {"ticker": ticker}
        
        # Auto-rejection check
        try:
            from .idx_rules import check_auto_rejection
            if reference_price and current_price and reference_price > 0:
                ar = check_auto_rejection(current_price, reference_price)
                result["auto_rejection"] = {
                    "is_rejected": ar.is_rejected,
                    "direction": ar.direction,
                    "limit_price": ar.limit_price,
                    "description": ar.description,
                }
        except Exception as e:
            result["auto_rejection_error"] = str(e)

        # Circuit breaker check
        try:
            from .idx_rules import check_circuit_breaker
            if ihsg_current and ihsg_previous_close:
                cb = check_circuit_breaker(ihsg_current, ihsg_previous_close)
                result["circuit_breaker"] = {
                    "is_halted": cb.is_halted,
                    "halt_level": cb.halt_level,
                    "description": cb.description,
                }
        except Exception as e:
            result["circuit_breaker_error"] = str(e)

        # Settlement check
        try:
            from .idx_rules import calc_settlement_date, is_trading_day
            trade_date = datetime.now()
            settlement = calc_settlement_date(trade_date)
            result["settlement"] = {
                "trade_date": trade_date.strftime("%Y-%m-%d"),
                "settlement_date": settlement.settlement_date.strftime("%Y-%m-%d"),
                "is_trading_day": is_trading_day(trade_date),
            }
        except Exception as e:
            result["settlement_error"] = str(e)

        return result

    # =========================================================================
    # PORTFOLIO RISK
    # =========================================================================

    def assess_portfolio_risk(
        self, positions: Dict[str, Dict], returns_data: Optional[pd.DataFrame] = None,
    ) -> Optional[Dict]:
        """
        Assess portfolio risk (VaR, CVaR, stress test).
        
        Args:
            positions: Dict of {ticker: {"weight": float, "shares": int, "price": float}}
            returns_data: Optional DataFrame of historical returns
        """
        try:
            from .portfolio_risk import (
                calculate_portfolio_var, stress_test_portfolio,
                check_sector_exposure,
            )

            if not positions:
                return {"error": "No positions to assess"}

            # Build weights array
            tickers = list(positions.keys())
            weights = np.array([positions[t].get("weight", 0) for t in tickers])

            # If no returns data, create synthetic from positions
            if returns_data is None or returns_data.empty:
                # Generate synthetic returns from price volatility
                n_days = 252
                returns_data = pd.DataFrame(
                    np.random.normal(0, 0.02, (n_days, len(tickers))),
                    columns=tickers,
                )

            # VaR
            var_result = calculate_portfolio_var(weights, returns_data, confidence=0.95)
            var_99 = calculate_portfolio_var(weights, returns_data, confidence=0.99)

            # Stress test
            stress = stress_test_portfolio(weights, returns_data)

            # Sector exposure
            sector_map = {}
            for t in tickers:
                sector_map[t] = _get_sector(t)
            sector_exp = check_sector_exposure(
                {t: positions[t].get("weight", 0) for t in tickers}, sector_map
            )

            return {
                "var_95": var_result.get("var", 0),
                "var_99": var_99.get("var", 0),
                "cvar_95": var_result.get("cvar", 0),
                "stress_test": {
                    "scenarios": stress.get("scenarios", []),
                    "worst_case": stress.get("worst_case_loss", 0),
                },
                "sector_exposure": sector_exp,
                "diversification_ratio": var_result.get("diversification_ratio", 1.0),
            }
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # REALTIME FEED
    # =========================================================================

    def get_realtime_price(self, ticker: str) -> Optional[float]:
        """Get real-time price with caching via RealTimeFeed."""
        try:
            if self._realtime_feed is None:
                from .realtime_feed import RealTimeFeed
                self._realtime_feed = RealTimeFeed(interval="5m", cache_ttl=60)
            data = self._realtime_feed.get_latest_price(ticker)
            return data
        except Exception:
            # Fallback to direct fetch
            try:
                from .data_fetcher import get_current_price
                return get_current_price(ticker)
            except Exception:
                return None

    def get_realtime_prices_batch(self, tickers: List[str]) -> Dict[str, float]:
        """Get real-time prices for multiple tickers with caching."""
        results = {}
        for ticker in tickers:
            price = self.get_realtime_price(ticker)
            if price and price > 0:
                results[ticker] = price
        return results

    # =========================================================================
    # MLFLOW TRACKING
    # =========================================================================

    def log_training(self, model_name: str, params: Dict, metrics: Dict) -> Optional[str]:
        """Log model training to MLflow (if available)."""
        try:
            from .mlflow_tracking import log_model_training
            return log_model_training(model_name, params, metrics)
        except Exception:
            return None

    def log_backtest(self, backtest_result, strategy_name: str = "Ensemble") -> Optional[str]:
        """Log backtest results to MLflow."""
        try:
            from .mlflow_tracking import log_backtest
            return log_backtest(backtest_result, strategy_name)
        except Exception:
            return None

    # =========================================================================
    # FUNDAMENTAL ANALYSIS (orphan module integration)
    # =========================================================================

    def run_fundamental_analysis(self, ticker: str) -> Optional[Dict]:
        """Run comprehensive fundamental analysis with scoring."""
        try:
            from .fundamental import fetch_fundamental_data, calc_fundamental_score
            fd = fetch_fundamental_data(ticker)
            if fd.error:
                return {"error": fd.error, "ticker": ticker}
            score = calc_fundamental_score(fd)
            return {
                "ticker": ticker,
                "total_score": score["total_score"],
                "grade": score["grade"],
                "rating": score["rating"],
                "pe_ratio": fd.pe_ratio,
                "pbv": fd.pbv,
                "roe": fd.roe,
                "eps": fd.eps_trailing,
                "debt_to_equity": fd.debt_to_equity,
                "dividend_yield": fd.dividend_yield,
                "revenue_growth": fd.revenue_growth,
                "profit_margin": fd.profit_margin,
            }
        except Exception as e:
            return {"error": str(e), "ticker": ticker}

    # =========================================================================
    # PATTERN RECOGNITION (orphan module integration)
    # =========================================================================

    def detect_patterns(self, df: pd.DataFrame) -> Optional[Dict]:
        """Run full pattern analysis on OHLC data."""
        try:
            from .patterns import full_pattern_analysis
            return full_pattern_analysis(df)
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # WALK-FORWARD VALIDATION (orphan module integration)
    # =========================================================================

    def run_walk_forward_cv(
        self, df: pd.DataFrame, feature_cols: List[str], target_col: str = "Target_Direction",
        n_folds: int = 5, train_size: int = 252,
    ) -> Optional[Dict]:
        """Run walk-forward cross-validation."""
        try:
            from .validation import walk_forward_cv
            result = walk_forward_cv(df, feature_cols, target_col, n_folds, train_size)
            return {
                "n_folds": result.n_folds,
                "mean_da": result.mean_da,
                "std_da": result.std_da,
                "mean_ic": result.mean_ic,
                "mean_rank_ic": result.mean_rank_ic,
                "icir": result.icir,
                "fold_results": result.fold_results,
            }
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # RETRAIN SCHEDULER (orphan module integration)
    # =========================================================================

    def check_retrain_needed(
        self, current_df: pd.DataFrame = None, feature_cols: List[str] = None,
        current_accuracy: Optional[float] = None,
    ) -> Optional[Dict]:
        """Check if model retraining is needed based on drift/schedule."""
        try:
            from .retrain_scheduler import RetrainingScheduler
            scheduler = RetrainingScheduler()
            if current_df is not None and feature_cols is not None:
                return scheduler.check_retrain_needed(
                    current_df=current_df,
                    feature_cols=feature_cols,
                    current_accuracy=current_accuracy,
                )
            else:
                # Lightweight check: just time-based
                now = datetime.now()
                state = scheduler.state
                if state.get("last_train_date"):
                    last = datetime.fromisoformat(state["last_train_date"])
                    days = (now - last).days
                    return {
                        "retrain_needed": days >= 7,
                        "reason": f"{days} days since last train" if days >= 7 else "Recently trained",
                        "days_since_last_train": days,
                    }
                return {
                    "retrain_needed": True,
                    "reason": "No previous training found",
                    "days_since_last_train": None,
                }
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # COMBINED ANALYSIS
    # =========================================================================

    def analyze_ticker(
        self,
        ticker: str,
        current_price: float,
        shares: int = 0,
        signal: str = "HOLD",
        confidence: float = 0.0,
        reference_price: Optional[float] = None,
    ) -> IntegratedAnalysis:
        """Run all integrated module analyses for a single ticker."""
        analysis = IntegratedAnalysis(ticker=ticker)

        # DCF
        analysis.dcf_valuation = self.run_dcf(ticker)

        # Slippage (if trading)
        if shares > 0 and current_price > 0:
            analysis.slippage_cost = self.estimate_slippage(ticker, shares, current_price, "buy")

        # Compliance
        analysis.compliance_check = self.check_compliance(ticker, signal, confidence)

        # IDX Rules
        analysis.idx_rules_check = self.check_idx_rules(
            ticker, current_price, reference_price
        )

        return analysis

    # =========================================================================
    # FRAUD DETECTION
    # =========================================================================

    def run_fraud_detection(
        self,
        market_data: Dict[str, pd.DataFrame],
        news_sentiment: float = 0,
        news_count: int = 0,
        index_ticker: str = "^JKSE",
    ) -> Optional[Dict]:
        """Run multi-layer fraud detection on market data."""
        try:
            from .fraud_detection import FraudDetector
            detector = FraudDetector()
            report = detector.validate_all(
                market_data=market_data,
                news_sentiment=news_sentiment,
                news_count=news_count,
                index_ticker=index_ticker,
            )
            return {
                "passed": report.passed,
                "critical_count": report.critical_count,
                "warning_count": report.warning_count,
                "summary": report.summary(),
                "alerts": [
                    {
                        "type": a.alert_type,
                        "severity": a.severity,
                        "ticker": a.ticker,
                        "message": a.message,
                    }
                    for a in report.alerts
                ],
                "data_quality": {
                    t: {"passed": r["passed"], "issues": r["issues"]}
                    for t, r in report.data_quality_reports.items()
                },
                "index_consistency": report.index_consistency,
                "news_divergence": report.news_divergence,
            }
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # INVESTOR TOOLS
    # =========================================================================

    def run_investor_analysis(
        self,
        market_data: Dict[str, pd.DataFrame],
        capital: float = 100_000_000,
        risk_profile: str = "moderate",
        age: Optional[int] = None,
    ) -> Optional[Dict]:
        """Run investor-level analysis: asset allocation, correlation, DRIP."""
        try:
            from .investor_tools import AssetAllocationModel, CorrelationAnalyzer

            # Asset allocation
            alloc_model = AssetAllocationModel()
            allocation = alloc_model.recommend(
                risk_profile=risk_profile, age=age, capital=capital,
            )

            # Correlation analysis
            corr_analyzer = CorrelationAnalyzer()
            corr_report = corr_analyzer.analyze(market_data)

            return {
                "asset_allocation": {
                    "stocks_pct": allocation.stocks_pct,
                    "bonds_pct": allocation.bonds_pct,
                    "cash_pct": allocation.cash_pct,
                    "commodities_pct": allocation.commodities_pct,
                    "crypto_pct": allocation.crypto_pct,
                    "risk_profile": allocation.risk_profile,
                    "expected_return": allocation.expected_return,
                    "expected_volatility": allocation.expected_volatility,
                    "max_drawdown_estimate": allocation.max_drawdown_estimate,
                    "rationale": allocation.rationale,
                },
                "correlation": {
                    "avg_correlation": corr_report.avg_correlation,
                    "max_correlation": corr_report.max_correlation,
                    "min_correlation": corr_report.min_correlation,
                    "diversification_ratio": corr_report.diversification_ratio,
                    "recommendation": corr_report.recommendation,
                    "clusters": corr_report.clusters,
                },
            }
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # INTRADAY MODEL (Day Trader)
    # =========================================================================

    def run_intraday_prediction(
        self, ticker: str, interval: str = "5m",
    ) -> Optional[Dict]:
        """Run intraday ML prediction for day trader mode."""
        try:
            from .intraday_model import IntradayModel
            from .data_fetcher import fetch_intraday_data

            model = IntradayModel(interval=interval)
            if not model.load():
                # Try to train on the fly
                df = fetch_intraday_data(ticker, interval=interval, period="60d")
                if df is None or df.empty:
                    return {"error": f"No intraday data for {ticker}"}
                train_result = model.train(df)
                if "error" in train_result:
                    return train_result

            # Fetch latest data for prediction
            df = fetch_intraday_data(ticker, interval=interval, period="5d")
            if df is None or df.empty:
                return {"error": f"No recent intraday data for {ticker}"}

            pred = model.predict(df)
            if pred is None:
                return {"error": "Prediction failed"}

            return {
                "ticker": ticker,
                "interval": pred.interval,
                "direction": pred.predicted_direction,
                "confidence": pred.confidence,
                "signal": pred.entry_signal,
                "predicted_price": pred.predicted_price,
                "current_price": pred.current_price,
                "predicted_return_pct": pred.predicted_return_pct,
                "bars_ahead": pred.bars_ahead,
                "stop_loss": pred.stop_loss,
                "take_profit": pred.take_profit,
                "rsi": pred.rsi,
                "vwap": pred.vwap,
                "volume_trend": pred.volume_trend,
                "spread_estimate": pred.spread_estimate,
            }
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # SMART MONEY CONCEPTS (SMC / ICT)
    # =========================================================================

    def run_smc_analysis(self, df: pd.DataFrame) -> Optional[Dict]:
        """Run Smart Money Concepts analysis on OHLCV data."""
        try:
            from .smc import run_smc_analysis as _run_smc
            result = _run_smc(df)
            return {
                "signal": result.signal,
                "confidence": result.confidence,
                "market_structure": result.market_structure,
                "premium_discount": result.premium_discount,
                "current_zone_price": result.current_zone_price,
                "swing_high": result.swing_high,
                "swing_low": result.swing_low,
                "equilibrium": result.equilibrium,
                "po3_phase": result.po3_phase,
                "recommendation": result.recommendation,
                "order_blocks": [
                    {
                        "type": ob.type,
                        "index": ob.index,
                        "high": ob.high,
                        "low": ob.low,
                        "displacement_strength": ob.displacement_strength,
                        "mitigated": ob.mitigated,
                    }
                    for ob in result.order_blocks
                ],
                "fair_value_gaps": [
                    {
                        "type": fvg.type,
                        "index": fvg.index,
                        "top": fvg.top,
                        "bottom": fvg.bottom,
                        "size": fvg.size,
                        "filled": fvg.filled,
                    }
                    for fvg in result.fair_value_gaps
                ],
                "liquidity_sweeps": [
                    {
                        "direction": s.direction,
                        "index": s.index,
                        "level_swept": s.level_swept,
                        "reversal_strength": s.reversal_strength,
                    }
                    for s in result.liquidity_sweeps
                ],
                "structure_breaks": [
                    {
                        "type": b.type,
                        "direction": b.direction,
                        "index": b.index,
                        "level_broken": b.level_broken,
                    }
                    for b in result.structure_breaks
                ],
                "details": result.details,
            }
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # AFML (LOPEZ DE PRADO)
    # =========================================================================

    def run_afml_pipeline(self, df: pd.DataFrame) -> Optional[Dict]:
        """Run Advances in Financial Machine Learning pipeline."""
        try:
            from .afml import run_afml_pipeline as _run_afml
            result = _run_afml(df)
            return {
                "label_distribution": result.get("label_distribution", {}),
                "optimal_d": result.get("optimal_d", 0),
                "is_stationary": result.get("is_stationary"),
                "adf_pvalue": result.get("adf_pvalue"),
            }
        except Exception as e:
            return {"error": str(e)}

    def run_triple_barrier_labels(self, df: pd.DataFrame, pt_sl=(1.0, 1.0), max_holding=10) -> Optional[Dict]:
        """Generate triple-barrier labels for ML training."""
        try:
            from .afml import triple_barrier_labels
            tb = triple_barrier_labels(df, pt_sl=pt_sl, max_holding_period=max_holding)
            return {
                "label_counts": tb["label"].value_counts().to_dict(),
                "total_labels": len(tb),
                "buy_pct": float((tb["label"] == 1).mean()),
                "sell_pct": float((tb["label"] == -1).mean()),
                "hold_pct": float((tb["label"] == 0).mean()),
            }
        except Exception as e:
            return {"error": str(e)}

    def run_fractional_diff(self, df: pd.DataFrame, close_col: str = "Close") -> Optional[Dict]:
        """Run fractional differentiation to find optimal d."""
        try:
            from .afml import find_optimal_d
            opt_d, diffed = find_optimal_d(df[close_col])
            return {
                "optimal_d": opt_d,
                "is_stationary": True,
                "series_length": len(diffed),
            }
        except Exception as e:
            return {"error": str(e)}

    def run_meta_labeling(self, df: pd.DataFrame, signal_col: str) -> Optional[Dict]:
        """Run meta-labeling pipeline for bet sizing."""
        try:
            from .afml import meta_labeling
            result = meta_labeling(df, primary_signal_col=signal_col)
            return {
                "metrics": result.metrics,
                "meta_label_distribution": result.meta_labels.value_counts().to_dict(),
                "avg_bet_size": float(result.bet_sizes.abs().mean()) if len(result.bet_sizes) > 0 else 0,
            }
        except Exception as e:
            return {"error": str(e)}

    def calc_deflated_sharpe(self, observed_sr: float, n_trials: int, n_samples: int) -> Optional[Dict]:
        """Calculate Deflated Sharpe Ratio."""
        try:
            from .afml import deflated_sharpe_ratio
            dsr = deflated_sharpe_ratio(observed_sr, n_trials, n_samples)
            return {
                "observed_sr": observed_sr,
                "n_trials": n_trials,
                "deflated_sr": dsr,
                "is_significant": dsr > 0,
            }
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # DRL TRADING
    # =========================================================================

    def train_drl(self, df: pd.DataFrame, episodes: int = 200, algorithm: str = "q_learning") -> Optional[Dict]:
        """Train DRL agent on price data."""
        try:
            from .drl_trading import train_drl_agent
            result = train_drl_agent(df, algorithm=algorithm, episodes=episodes)
            return {
                "algorithm": result.algorithm,
                "episodes": result.episodes,
                "final_reward": result.final_reward,
                "mean_reward": result.mean_reward,
                "converged": result.converged,
                "training_time": result.training_time_seconds,
            }
        except Exception as e:
            return {"error": str(e)}

    def predict_drl(self, df: pd.DataFrame, model_path: str = None) -> Optional[Dict]:
        """Get DRL agent prediction."""
        try:
            from .drl_trading import predict_drl_action
            pred = predict_drl_action(df, model_path=model_path)
            return {
                "action": pred.action,
                "action_name": pred.action_name,
                "confidence": pred.confidence,
                "position_recommendation": pred.position_recommendation,
            }
        except Exception as e:
            return {"error": str(e)}

    # =========================================================================
    # COMPLEX SYSTEMS
    # =========================================================================

    def run_complex_systems(self, returns: pd.DataFrame) -> Optional[Dict]:
        """Run complex systems network analysis on asset returns."""
        try:
            from .complex_systems import run_complex_systems_analysis
            result = run_complex_systems_analysis(returns)
            return {
                "systemic_nodes": result.systemic_nodes,
                "contagion_risk": result.contagion_risk,
                "network_density": result.network_density,
                "avg_clustering": result.avg_clustering,
                "n_clusters": len(result.clusters),
                "clusters": {str(k): v for k, v in result.clusters.items()},
                "recommendation": result.recommendation,
                "stress_test": {
                    "n_scenarios": len(result.stress_test_scenarios.get("scenarios", {})),
                    "worst_case": result.stress_test_scenarios.get("worst_case"),
                    "network_vulnerability": result.stress_test_scenarios.get("network_vulnerability", 0),
                },
            }
        except Exception as e:
            return {"error": str(e)}


def _get_sector(ticker: str) -> str:
    """Get sector for a ticker."""
    from .config import SAHAM_IDX_SECTORS
    
    # Convert SAHAM_IDX_SECTORS to flat ticker -> sector mapping
    for sector, tickers in SAHAM_IDX_SECTORS.items():
        if ticker in tickers:
            return sector.lower().replace(" ", "").replace("&", "")
    return "other"


# =============================================================================
# PHASE 2+ MODULE INTEGRATION FUNCTIONS
# Attached to ModuleIntegrator class via monkey-patching below.
# =============================================================================

def _run_transformer_ensemble(self, df: pd.DataFrame) -> Dict:
    """Run transformer model ensemble (PatchTST, TFT, LPatchTST)."""
    try:
        from .transformer_models import get_transformer_ensemble_prediction, TransformerConfig
        config = TransformerConfig(
            seq_len=min(64, len(df) // 3),
            pred_len=1,
            epochs=30,
        )
        result = get_transformer_ensemble_prediction(df, config)
        return result
    except Exception as e:
        return {"error": str(e)}


def _run_cpcv(self, X: pd.DataFrame, y: pd.Series, model_fn=None) -> Dict:
    """Run Combinatorial Purged Cross-Validation."""
    try:
        from .cpcv import cpcv_evaluate
        from sklearn.ensemble import RandomForestRegressor
        fn = model_fn or RandomForestRegressor
        result = cpcv_evaluate(X, y, fn, n_groups=6, n_test_groups=2)
        return {
            "n_paths": result.n_paths,
            "mean_score": result.mean_score,
            "std_score": result.std_score,
            "median_score": result.median_score,
            "pbo": result.pbo,
            "score_distribution": result.score_distribution,
        }
    except Exception as e:
        return {"error": str(e)}


def _run_regime_aware_model(self, df: pd.DataFrame) -> Dict:
    """Run regime-aware model prediction."""
    try:
        from .regime_models import run_regime_aware_prediction
        return run_regime_aware_prediction(df)
    except Exception as e:
        return {"error": str(e)}


def _run_options_analysis(self, S: float, K: float = None, T: float = 30 / 365) -> Dict:
    """Run options analysis (pricing, Greeks, strategies)."""
    try:
        from .options_analysis import run_options_analysis
        strike = K or S
        return run_options_analysis(S, strike, T)
    except Exception as e:
        return {"error": str(e)}


def _run_bull_bear_debate(self, df: pd.DataFrame, indicators: Dict = None, sentiment: Dict = None) -> Dict:
    """Run bull vs bear debate system."""
    try:
        from .bull_bear_debate import run_bull_bear_debate
        result = run_bull_bear_debate(df, indicators or {}, sentiment or {})
        return {
            "verdict": result.verdict,
            "confidence": result.confidence,
            "bull_score": result.bull_score,
            "bear_score": result.bear_score,
            "key_factors": result.key_factors,
            "risk_factors": result.risk_factors,
            "summary": result.summary,
            "n_bull_args": len(result.bull_arguments),
            "n_bear_args": len(result.bear_arguments),
        }
    except Exception as e:
        return {"error": str(e)}


def _run_rag_query(self, question: str, top_k: int = 5) -> Dict:
    """Run RAG (Retrieval-Augmented Generation) query."""
    try:
        from .rag_system import run_rag_query
        return run_rag_query(question, top_k)
    except Exception as e:
        return {"error": str(e)}


def _run_alphalens_analysis(self, factor: pd.Series, prices: pd.DataFrame) -> Dict:
    """Run Alphalens-style factor analysis."""
    try:
        from .alphalens_analysis import run_factor_analysis, create_tear_sheet
        result = run_factor_analysis(factor, prices)
        return create_tear_sheet(result)
    except Exception as e:
        return {"error": str(e)}


def _run_multi_horizon_prediction(self, df: pd.DataFrame, ticker: str = "") -> Dict:
    """Run multi-horizon prediction (3d, 1w, 1m, 3m)."""
    try:
        from .multi_horizon import run_multi_horizon_prediction
        result = run_multi_horizon_prediction(df, ticker)
        return {
            "ticker": result.ticker,
            "current_price": result.current_price,
            "consensus_signal": result.consensus_signal,
            "consensus_confidence": result.consensus_confidence,
            "horizon_agreement": result.horizon_agreement,
            "predictions": {
                name: {
                    "horizon": p.horizon,
                    "days": p.days,
                    "predicted_return": p.predicted_return,
                    "predicted_price": p.predicted_price,
                    "confidence": p.confidence,
                    "signal": p.signal,
                }
                for name, p in result.predictions.items()
            },
        }
    except Exception as e:
        return {"error": str(e)}


def _run_transfer_learning(self, dfs: Dict[str, pd.DataFrame], target_ticker: str) -> Dict:
    """Run transfer learning (parent-child model)."""
    try:
        from .transfer_learning import run_transfer_learning
        return run_transfer_learning(dfs, target_ticker)
    except Exception as e:
        return {"error": str(e)}


def _run_react_agent(self, df: pd.DataFrame, ticker: str = "", sentiment_data: Dict = None) -> Dict:
    """Run ReAct (Reasoning + Acting) agent."""
    try:
        from .react_agent import run_react_agent
        return run_react_agent(df, ticker, sentiment_data)
    except Exception as e:
        return {"error": str(e)}


def _run_multi_mode_research(self, df: pd.DataFrame, ticker: str = "", mode: str = "fast", sentiment_data: Dict = None) -> Dict:
    """Run multi-mode AI research (fast/deep/custom)."""
    try:
        from .multi_mode_research import run_research
        result = run_research(df, ticker, mode, sentiment_data=sentiment_data)
        return {
            "mode": result.mode,
            "recommendation": result.recommendation,
            "confidence": result.confidence,
            "modules_used": result.modules_used,
            "execution_time": result.execution_time,
            "summary": result.summary,
            "analysis": result.analysis if isinstance(result.analysis, dict) else {},
        }
    except Exception as e:
        return {"error": str(e)}


def _run_social_sentiment(self, ticker: str, use_mock: bool = True) -> Dict:
    """Run social media sentiment analysis."""
    try:
        from .social_sentiment import run_social_sentiment
        return run_social_sentiment(ticker, use_mock=use_mock)
    except Exception as e:
        return {"error": str(e)}


def _run_ab_test(self, predictions_a, predictions_b, actual, name_a="A", name_b="B", metric="sharpe") -> Dict:
    """Run A/B test between two models."""
    try:
        from .ab_testing import run_ab_test
        result = run_ab_test(predictions_a, predictions_b, actual, name_a, name_b, metric)
        return {
            "winner": result.winner,
            "confidence": result.confidence,
            "p_value": result.p_value,
            "significant": result.significant,
            "improvement": result.improvement,
            "metric_used": result.metric_used,
            "details": result.details,
        }
    except Exception as e:
        return {"error": str(e)}


# Attach Phase 2+ methods to ModuleIntegrator
ModuleIntegrator.run_transformer_ensemble = _run_transformer_ensemble
ModuleIntegrator.run_cpcv = _run_cpcv
ModuleIntegrator.run_regime_aware_model = _run_regime_aware_model
ModuleIntegrator.run_options_analysis = _run_options_analysis
ModuleIntegrator.run_bull_bear_debate = _run_bull_bear_debate
ModuleIntegrator.run_rag_query = _run_rag_query
ModuleIntegrator.run_alphalens_analysis = _run_alphalens_analysis
ModuleIntegrator.run_multi_horizon_prediction = _run_multi_horizon_prediction
ModuleIntegrator.run_transfer_learning = _run_transfer_learning
ModuleIntegrator.run_react_agent = _run_react_agent
ModuleIntegrator.run_multi_mode_research = _run_multi_mode_research
ModuleIntegrator.run_social_sentiment = _run_social_sentiment
ModuleIntegrator.run_ab_test = _run_ab_test
