"""
Tests for production real-world modules:
- hyperopt (Optuna tuning)
- retrain_scheduler (automated retraining)
- realtime_feed (real-time data)
- portfolio_risk (portfolio-level risk)
- slippage (execution cost model)
- sentiment_pipeline (daily sentiment)
"""

import pytest
import pandas as pd
import numpy as np
from datetime import datetime, timedelta


# =============================================================================
# HYPEROPT TESTS
# =============================================================================

class TestHyperopt:
    def test_optuna_import(self):
        from src.hyperopt import run_hyperopt_tuning, load_best_params
        assert callable(run_hyperopt_tuning)
        assert callable(load_best_params)

    def test_hyperopt_small_run(self):
        from src.hyperopt import run_hyperopt_tuning
        from src.preprocessor import prepare_features, get_feature_columns
        from src.data_fetcher import fetch_all_market_data
        from src.database import init_db
        init_db()

        market = fetch_all_market_data(period="1y")
        df = prepare_features(market)
        feature_cols = get_feature_columns(df)
        df_clean = df.dropna(subset=feature_cols + ["Target_Direction"])

        result = run_hyperopt_tuning(df_clean, feature_cols, n_trials=3, n_splits=2)
        assert "best_params" in result
        assert "best_cv_accuracy" in result
        assert result["n_trials"] == 3
        assert 0.3 <= result["best_cv_accuracy"] <= 1.0

    def test_load_best_params(self):
        from src.hyperopt import load_best_params
        params = load_best_params()
        # Should either be None or have best_params
        if params is not None:
            assert "best_params" in params


# =============================================================================
# RETRAIN SCHEDULER TESTS
# =============================================================================

class TestRetrainScheduler:
    def test_scheduler_init(self):
        from src.retrain_scheduler import RetrainingScheduler
        scheduler = RetrainingScheduler()
        assert hasattr(scheduler, "state")
        assert "last_train_date" in scheduler.state

    def test_check_retrain_needed_never_trained(self):
        from src.retrain_scheduler import RetrainingScheduler
        scheduler = RetrainingScheduler()
        scheduler.state["last_train_date"] = None

        df = pd.DataFrame({"f1": np.random.randn(100), "f2": np.random.randn(100)})
        check = scheduler.check_retrain_needed(df, ["f1", "f2"])
        assert check["needs_retrain"] is True
        assert "Never trained" in check["reasons"][0]

    def test_check_retrain_needed_schedule(self):
        from src.retrain_scheduler import RetrainingScheduler
        scheduler = RetrainingScheduler()
        scheduler.state["last_train_date"] = (datetime.now() - timedelta(days=10)).isoformat()

        df = pd.DataFrame({"f1": np.random.randn(100), "f2": np.random.randn(100)})
        check = scheduler.check_retrain_needed(df, ["f1", "f2"], schedule="weekly")
        assert check["needs_retrain"] is True
        assert any("Schedule" in r for r in check["reasons"])

    def test_check_retrain_not_needed(self):
        from src.retrain_scheduler import RetrainingScheduler
        scheduler = RetrainingScheduler()
        scheduler.state["last_train_date"] = datetime.now().isoformat()
        scheduler.state["baseline_features"] = None

        df = pd.DataFrame({"f1": np.random.randn(100), "f2": np.random.randn(100)})
        check = scheduler.check_retrain_needed(df, ["f1", "f2"], schedule="weekly")
        assert check["needs_retrain"] is False

    def test_record_training(self):
        from src.retrain_scheduler import RetrainingScheduler
        scheduler = RetrainingScheduler()
        scheduler.state["last_train_date"] = None
        scheduler.state["baseline_accuracy"] = None

        df = pd.DataFrame({"f1": np.random.randn(200), "f2": np.random.randn(200)})
        scheduler.record_training(0.55, df, ["f1", "f2"])
        assert scheduler.state["last_train_date"] is not None
        assert scheduler.state["baseline_accuracy"] == 0.55
        assert scheduler.state["retrain_count"] >= 1

    def test_get_status(self):
        from src.retrain_scheduler import RetrainingScheduler
        scheduler = RetrainingScheduler()
        status = scheduler.get_status()
        assert "last_train_date" in status
        assert "retrain_count" in status


# =============================================================================
# REALTIME FEED TESTS
# =============================================================================

class TestRealtimeFeed:
    def test_feed_init(self):
        from src.realtime_feed import RealTimeFeed
        feed = RealTimeFeed(interval="5m", cache_ttl=60)
        assert feed.interval == "5m"
        assert feed.cache_ttl == 60

    def test_get_realtime_price(self):
        from src.realtime_feed import RealTimeFeed
        feed = RealTimeFeed(interval="1d", cache_ttl=300)
        result = feed.get_realtime_price("^JKSE")
        if result:  # Network may fail in CI
            assert "price" in result
            assert "ticker" in result
            assert result["ticker"] == "^JKSE"

    def test_market_status(self):
        from src.realtime_feed import get_market_status
        status = get_market_status()
        assert "is_open" in status
        assert "is_weekend" in status
        assert isinstance(status["is_open"], bool)

    def test_price_alert(self):
        from src.realtime_feed import PriceAlert
        alert = PriceAlert()
        alert.add_alert("^JKSE", "above", 7000)
        assert len(alert.get_active_alerts()) == 1

        prices = {"^JKSE": {"price": 7100}}
        triggered = alert.check_alerts(prices)
        assert len(triggered) == 1
        assert triggered[0]["triggered"] is True


# =============================================================================
# PORTFOLIO RISK TESTS
# =============================================================================

class TestPortfolioRisk:
    def test_portfolio_var_historical(self):
        from src.portfolio_risk import calculate_portfolio_var
        np.random.seed(42)
        returns = pd.DataFrame({
            "A": np.random.randn(500) * 0.01,
            "B": np.random.randn(500) * 0.015,
            "C": np.random.randn(500) * 0.02,
        })
        weights = np.array([0.4, 0.3, 0.3])
        result = calculate_portfolio_var(weights, returns, 0.95, "historical")
        assert "var_percent" in result
        assert "cvar_percent" in result
        assert result["var_percent"] < 0  # VaR should be negative (loss)

    def test_portfolio_var_parametric(self):
        from src.portfolio_risk import calculate_portfolio_var
        np.random.seed(42)
        returns = pd.DataFrame({
            "A": np.random.randn(500) * 0.01,
            "B": np.random.randn(500) * 0.015,
        })
        weights = np.array([0.5, 0.5])
        result = calculate_portfolio_var(weights, returns, 0.99, "parametric")
        assert result["var_percent"] < 0
        assert result["confidence"] == 0.99

    def test_correlation_aware_allocation(self):
        from src.portfolio_risk import correlation_aware_allocation
        np.random.seed(42)
        returns = pd.DataFrame({
            "BBCA": np.random.randn(252) * 0.01,
            "BBRI": np.random.randn(252) * 0.012,
            "TLKM": np.random.randn(252) * 0.008,
        })
        result = correlation_aware_allocation(returns)
        assert "weights" in result
        assert sum(result["weights"].values()) <= 1.01
        assert "diversification_ratio" in result

    def test_sector_exposure(self):
        from src.portfolio_risk import check_sector_exposure
        weights = {"BBCA": 0.30, "BBRI": 0.25, "TLKM": 0.20, "ADRO": 0.25}
        sector_map = {"BBCA": "Financials", "BBRI": "Financials", "TLKM": "Technology", "ADRO": "Energy"}
        result = check_sector_exposure(weights, sector_map)
        assert "Financials" in result["sector_weights"]
        assert result["sector_weights"]["Financials"] == 0.55
        assert len(result["violations"]) > 0  # Financials exceeds 35%

    def test_stress_test(self):
        from src.portfolio_risk import stress_test_portfolio
        np.random.seed(42)
        returns = pd.DataFrame({
            "A": np.random.randn(252) * 0.01,
            "B": np.random.randn(252) * 0.015,
        })
        weights = np.array([0.5, 0.5])
        result = stress_test_portfolio(weights, returns)
        assert len(result["scenarios"]) == 6
        assert "worst_case" in result
        assert result["worst_case"]["portfolio_impact_pct"] < 0

    def test_kelly_portfolio(self):
        from src.portfolio_risk import kelly_portfolio_weights
        np.random.seed(42)
        returns = pd.DataFrame({
            "A": np.random.randn(252) * 0.01 + 0.0005,
            "B": np.random.randn(252) * 0.015 + 0.0008,
        })
        result = kelly_portfolio_weights(returns)
        assert "kelly_weights" in result
        assert "half_kelly_weights" in result
        assert "recommendation" in result

    def test_full_portfolio_risk_report(self):
        from src.portfolio_risk import full_portfolio_risk_report
        np.random.seed(42)
        returns = pd.DataFrame({
            "A": np.random.randn(252) * 0.01,
            "B": np.random.randn(252) * 0.015,
        })
        weights = np.array([0.6, 0.4])
        report = full_portfolio_risk_report(weights, returns)
        assert report.portfolio_var_95 < 0
        assert isinstance(report.recommendations, list)
        assert report.concentration_risk > 0


# =============================================================================
# SLIPPAGE TESTS
# =============================================================================

class TestSlippage:
    def test_bid_ask_spread(self):
        from src.slippage import estimate_bid_ask_spread
        spread = estimate_bid_ask_spread(7500, 1_000_000, 0.02, True)
        assert spread > 0
        assert spread < 50  # Should be reasonable

    def test_market_impact(self):
        from src.slippage import calculate_market_impact
        impact = calculate_market_impact(10000, 500_000, 7500, 0.02)
        assert impact["impact_bps"] > 0
        assert impact["execution_time_minutes"] > 0
        assert impact["temporary_bps"] > impact["permanent_bps"]

    def test_total_execution_cost(self):
        from src.slippage import calculate_total_execution_cost
        cost = calculate_total_execution_cost(
            shares=1000, price=7500, adv=500_000, side="buy"
        )
        assert cost.shares == 1000
        assert cost.total_cost > 0
        assert cost.slippage_bps > 0
        assert cost.cost_pct > 0

    def test_execution_cost_sell(self):
        from src.slippage import calculate_total_execution_cost
        cost_buy = calculate_total_execution_cost(1000, 7500, 500_000, side="buy")
        cost_sell = calculate_total_execution_cost(1000, 7500, 500_000, side="sell")
        # Sell commission is higher (25 bps vs 15 bps)
        assert cost_sell.total_cost > cost_buy.total_cost

    def test_optimize_execution(self):
        from src.slippage import optimize_execution
        result = optimize_execution(50000, 7500, 1_000_000, urgency="normal")
        assert result["slices"] == 5
        assert "recommendation" in result
        assert result["total_cost"] > 0

    def test_optimize_execution_urgent(self):
        from src.slippage import optimize_execution
        result = optimize_execution(10000, 7500, 500_000, urgency="high")
        assert result["participation_rate"] == 0.20
        assert result["slices"] == 2

    def test_backtest_with_slippage(self):
        from src.slippage import backtest_with_slippage
        np.random.seed(42)
        n = 100
        df = pd.DataFrame({
            "Close": 7500 + np.cumsum(np.random.randn(n) * 50),
            "Volume": np.random.randint(100_000, 1_000_000, n),
        }, index=pd.date_range("2025-01-01", periods=n))
        preds = np.random.randint(0, 2, n)
        result = backtest_with_slippage(df, preds, "Close", "Volume")
        assert "total_return_pct" in result
        assert "num_trades" in result
        assert "avg_slippage_bps" in result


# =============================================================================
# SENTIMENT PIPELINE TESTS
# =============================================================================

class TestSentimentPipeline:
    def test_pipeline_init(self):
        from src.sentiment_pipeline import DailySentimentPipeline
        pipeline = DailySentimentPipeline()
        assert hasattr(pipeline, "scraper")
        assert hasattr(pipeline, "sentiment_analyzer")
        assert hasattr(pipeline, "_history")

    def test_get_fear_greed_default(self):
        from src.sentiment_pipeline import DailySentimentPipeline
        pipeline = DailySentimentPipeline()
        pipeline._history = []  # Clear history
        fg = pipeline.get_fear_greed_for_prediction()
        assert fg == 50.0  # Neutral default

    def test_get_fear_greed_from_history(self):
        from src.sentiment_pipeline import DailySentimentPipeline
        pipeline = DailySentimentPipeline()
        pipeline._history = [{"date": "2025-01-01", "sentiment_score": 30, "fear_greed_index": 65.0, "total_articles": 10}]
        fg = pipeline.get_fear_greed_for_prediction()
        assert fg == 65.0

    def test_sentiment_history_dataframe(self):
        from src.sentiment_pipeline import DailySentimentPipeline
        pipeline = DailySentimentPipeline()
        pipeline._history = [
            {"date": "2025-01-01", "sentiment_score": 10, "fear_greed_index": 55, "total_articles": 5},
            {"date": "2025-01-02", "sentiment_score": -20, "fear_greed_index": 40, "total_articles": 8},
        ]
        df = pipeline.get_sentiment_history(days=30)
        assert len(df) == 2
        assert "sentiment_score" in df.columns
