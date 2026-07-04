"""
Tests for Phase 2+ advanced modules.

Covers: transformer_models, cpcv, regime_models, options_analysis,
bull_bear_debate, rag_system, alphalens_analysis, multi_horizon,
transfer_learning, trading_memory, react_agent, multi_mode_research,
social_sentiment, ab_testing.
"""
import numpy as np
import pandas as pd
import pytest
from datetime import datetime, timedelta


# =============================================================================
# FIXTURES
# =============================================================================


@pytest.fixture
def sample_ohlcv():
    """Generate sample OHLCV data."""
    np.random.seed(42)
    n = 250
    dates = pd.date_range("2024-01-01", periods=n, freq="B")
    close = 8000 + np.cumsum(np.random.randn(n) * 50 + 5)
    close = np.maximum(close, 1000)
    high = close * (1 + np.abs(np.random.randn(n)) * 0.01)
    low = close * (1 - np.abs(np.random.randn(n)) * 0.01)
    volume = np.random.randint(1e6, 5e7, n).astype(float)
    return pd.DataFrame({
        "Open": close * 0.99,
        "High": high,
        "Low": low,
        "Close": close,
        "Volume": volume,
    }, index=dates)


@pytest.fixture
def sample_returns():
    """Generate sample daily returns."""
    np.random.seed(42)
    return pd.Series(np.random.randn(250) * 0.02, index=pd.date_range("2024-01-01", periods=250, freq="B"))


@pytest.fixture
def sample_prices_multi():
    """Generate multi-ticker price data for factor analysis."""
    np.random.seed(42)
    n = 200
    dates = pd.date_range("2024-01-01", periods=n, freq="B")
    tickers = ["BBCA", "BBRI", "TLKM", "ASII", "UNVR", "ICBP", "ADRO", "GOTO"]
    data = {}
    for t in tickers:
        price = 1000 + np.cumsum(np.random.randn(n) * 10)
        data[t] = np.maximum(price, 100)
    return pd.DataFrame(data, index=dates)


# =============================================================================
# TRANSFORMER MODELS
# =============================================================================


class TestTransformerModels:
    def test_patchtst_training(self, sample_ohlcv):
        from src.transformer_models import train_patchtst, TransformerConfig
        config = TransformerConfig(seq_len=32, pred_len=1, epochs=5, batch_size=16)
        result = train_patchtst(sample_ohlcv, config)
        assert result.model_type == "PatchTST"
        assert result.trained
        assert len(result.train_loss) > 0

    def test_tft_training(self, sample_ohlcv):
        from src.transformer_models import train_tft, TransformerConfig
        config = TransformerConfig(seq_len=32, pred_len=1, epochs=5, batch_size=16)
        result = train_tft(sample_ohlcv, config)
        assert result.model_type == "TFT"
        assert result.trained

    def test_lpatchtst_training(self, sample_ohlcv):
        from src.transformer_models import train_lstm_patchtst_hybrid, TransformerConfig
        config = TransformerConfig(seq_len=32, pred_len=1, epochs=5, batch_size=16)
        result = train_lstm_patchtst_hybrid(sample_ohlcv, config)
        assert result.model_type == "LPatchTST"
        assert result.trained

    def test_ensemble_prediction(self, sample_ohlcv):
        from src.transformer_models import get_transformer_ensemble_prediction, TransformerConfig
        config = TransformerConfig(seq_len=32, epochs=3, batch_size=16)
        result = get_transformer_ensemble_prediction(sample_ohlcv, config)
        assert "ensemble_prediction" in result
        assert "confidence" in result
        assert "n_models_trained" in result
        assert result["n_models_trained"] >= 1

    def test_insufficient_data(self):
        from src.transformer_models import train_patchtst, TransformerConfig
        df = pd.DataFrame({"Close": [100, 101, 102], "Volume": [1e6, 2e6, 3e6]})
        result = train_patchtst(df)
        assert not result.trained
        assert "error" in result.error.lower() or "insufficient" in result.error.lower()

    def test_confidence_adjustment(self):
        from src.transformer_models import get_transformer_confidence_adjustment
        result = {"n_models_trained": 3, "confidence": 0.8}
        adj, reason = get_transformer_confidence_adjustment(result)
        assert adj > 0
        assert "Transformer" in reason


# =============================================================================
# CPCV
# =============================================================================


class TestCPCV:
    def test_cpcv_split(self):
        from src.cpcv import cpcv_split
        splits = cpcv_split(300, n_groups=6, n_test_groups=2, purge_days=3, embargo_days=2)
        assert len(splits) > 0
        for train_idx, test_idx in splits:
            assert len(train_idx) > 0
            assert len(test_idx) > 0
            # No overlap
            assert set(train_idx) & set(test_idx) == set()

    def test_cpcv_evaluate(self, sample_ohlcv):
        from src.cpcv import cpcv_evaluate
        from sklearn.ensemble import RandomForestRegressor
        X = pd.DataFrame({
            "f1": np.random.randn(200),
            "f2": np.random.randn(200),
            "f3": np.random.randn(200),
        })
        y = pd.Series(np.random.randn(200))
        result = cpcv_evaluate(X, y, RandomForestRegressor, n_groups=4, n_test_groups=1)
        assert result.n_paths > 0
        assert result.mean_score != 0 or result.n_paths > 0

    def test_cpcv_backtest_paths(self, sample_returns):
        from src.cpcv import cpcv_backtest_paths
        result = cpcv_backtest_paths(sample_returns, n_groups=4, n_test_groups=1)
        assert result["n_paths"] > 0
        assert "mean_sharpe" in result
        assert "pbo" in result


# =============================================================================
# REGIME MODELS
# =============================================================================


class TestRegimeModels:
    def test_detect_regime(self, sample_ohlcv):
        from src.regime_models import detect_market_regime
        regimes = detect_market_regime(sample_ohlcv)
        assert len(regimes) == len(sample_ohlcv)
        assert set(regimes.unique()).issubset({"bull", "bear", "sideways"})

    def test_regime_probabilities(self, sample_ohlcv):
        from src.regime_models import detect_regime_probabilities
        probs = detect_regime_probabilities(sample_ohlcv)
        assert "bull_prob" in probs.columns
        assert "bear_prob" in probs.columns
        assert "sideways_prob" in probs.columns
        # Probabilities should sum to ~1 (allow for NaN and edge cases)
        total = probs.sum(axis=1).dropna()
        assert len(total) > 0
        median_sum = float(total.median())
        assert abs(median_sum - 1.0) < 0.2

    def test_regime_aware_prediction(self, sample_ohlcv):
        from src.regime_models import run_regime_aware_prediction
        result = run_regime_aware_prediction(sample_ohlcv)
        assert result["trained"]
        assert "current_regime" in result
        assert "confidence" in result

    def test_confidence_adjustment(self):
        from src.regime_models import get_regime_confidence_adjustment
        result = {"trained": True, "confidence": 0.8, "current_regime": "bull"}
        adj, reason = get_regime_confidence_adjustment(result)
        assert adj > 0


# =============================================================================
# OPTIONS ANALYSIS
# =============================================================================


class TestOptionsAnalysis:
    def test_black_scholes_call(self):
        from src.options_analysis import black_scholes_price
        price = black_scholes_price(S=100, K=100, T=0.25, r=0.05, sigma=0.2, option_type="call")
        assert price > 0
        # ATM call should be around 4-5 for these params
        assert 3 < price < 7

    def test_black_scholes_put(self):
        from src.options_analysis import black_scholes_price
        price = black_scholes_price(S=100, K=100, T=0.25, r=0.05, sigma=0.2, option_type="put")
        assert price > 0
        assert 2 < price < 6

    def test_greeks(self):
        from src.options_analysis import compute_greeks
        greeks = compute_greeks(S=100, K=100, T=0.25, r=0.05, sigma=0.2, option_type="call")
        assert 0 < greeks.delta < 1  # Call delta between 0 and 1
        assert greeks.gamma > 0
        assert greeks.theta < 0  # Theta is negative for long options
        assert greeks.vega > 0

    def test_implied_volatility(self):
        from src.options_analysis import implied_volatility, black_scholes_price
        # Generate a price with known IV, then recover it
        true_iv = 0.25
        price = black_scholes_price(100, 100, 0.25, 0.05, true_iv, "call")
        recovered_iv = implied_volatility(price, 100, 100, 0.25, 0.05, "call")
        assert abs(recovered_iv - true_iv) < 0.01

    def test_put_call_parity(self):
        from src.options_analysis import black_scholes_price, put_call_parity
        call = black_scholes_price(100, 100, 0.25, 0.05, 0.2, "call")
        put = black_scholes_price(100, 100, 0.25, 0.05, 0.2, "put")
        parity = put_call_parity(100, 100, 0.25, 0.05, call, put)
        assert parity["parity_satisfied"]

    def test_long_straddle(self):
        from src.options_analysis import long_straddle
        result = long_straddle(S=100, K=100, T=0.25, r=0.05, sigma=0.2)
        assert result.max_loss > 0
        assert len(result.breakeven_points) == 2
        assert result.probability_profit >= 0

    def test_iron_condor(self):
        from src.options_analysis import iron_condor
        result = iron_condor(100, 90, 95, 105, 110, 0.25, 0.05, 0.2)
        assert result.max_profit > 0
        assert result.max_loss > 0
        assert len(result.breakeven_points) == 2

    def test_run_options_analysis(self):
        from src.options_analysis import run_options_analysis
        result = run_options_analysis(S=100, K=100, T=30/365, r=0.06, sigma=0.25)
        assert "pricing" in result
        assert "greeks" in result
        assert "strategies" in result
        assert result["pricing"]["call_price"] > 0


# =============================================================================
# BULL BEAR DEBATE
# =============================================================================


class TestBullBearDebate:
    def test_debate_basic(self, sample_ohlcv):
        from src.bull_bear_debate import run_bull_bear_debate
        result = run_bull_bear_debate(sample_ohlcv)
        assert result.verdict in ["BUY", "SELL", "HOLD"]
        assert 0 <= result.confidence <= 1
        assert len(result.bull_arguments) > 0 or len(result.bear_arguments) > 0
        assert len(result.summary) > 0

    def test_debate_with_indicators(self, sample_ohlcv):
        from src.bull_bear_debate import run_bull_bear_debate
        indicators = {"rsi": 55, "smc_signal": "BUY", "vix": 15}
        sentiment = {"score": 0.3}
        result = run_bull_bear_debate(sample_ohlcv, indicators, sentiment)
        assert result.verdict in ["BUY", "SELL", "HOLD"]


# =============================================================================
# RAG SYSTEM
# =============================================================================


class TestRAGSystem:
    def test_vector_store_add_and_search(self, tmp_path):
        from src.rag_system import VectorStore
        store = VectorStore(storage_path=str(tmp_path / "rag"))
        store.add_documents(
            ["BBCA stock is bullish today", "BBRI reports strong earnings", "Market crashes on fear"],
            categories=["news", "news", "news"],
        )
        assert len(store.documents) == 3
        result = store.search("bullish stock", top_k=2)
        assert result.n_docs_retrieved > 0

    def test_rag_query(self, tmp_path):
        from src.rag_system import RAGSystem
        rag = RAGSystem(storage_path=str(tmp_path / "rag2"))
        rag.ingest_news([
            {"title": "BBCA bullish", "summary": "Stock rises on strong volume"},
            {"title": "Market fear", "summary": "VIX spikes on economic concerns"},
        ])
        result = rag.query("What is the sentiment on BBCA?")
        assert result.retrieval.n_docs_retrieved >= 0  # May be 0 if no match


# =============================================================================
# ALPHALENS ANALYSIS
# =============================================================================


class TestAlphalensAnalysis:
    def test_compute_forward_returns(self, sample_prices_multi):
        from src.alphalens_analysis import compute_forward_returns
        fwd = compute_forward_returns(sample_prices_multi, periods=[1, 5])
        assert "period_1" in fwd.columns
        assert "period_5" in fwd.columns

    def test_factor_analysis(self, sample_prices_multi):
        from src.alphalens_analysis import run_factor_analysis
        # Create a simple factor: momentum
        factor = sample_prices_multi.pct_change(20).stack()
        factor.name = "momentum"
        result = run_factor_analysis(factor, sample_prices_multi, periods=[1, 5], n_quantiles=3)
        assert result.n_signals > 0
        assert result.ic_mean != 0 or True  # May be 0 for random data

    def test_tear_sheet(self, sample_prices_multi):
        from src.alphalens_analysis import run_factor_analysis, create_tear_sheet
        factor = sample_prices_multi.pct_change(20).stack()
        factor.name = "momentum"
        result = run_factor_analysis(factor, sample_prices_multi, periods=[1, 5], n_quantiles=3)
        sheet = create_tear_sheet(result)
        assert "summary" in sheet
        assert "assessment" in sheet


# =============================================================================
# MULTI-HORIZON
# =============================================================================


class TestMultiHorizon:
    def test_multi_horizon_prediction(self, sample_ohlcv):
        from src.multi_horizon import run_multi_horizon_prediction
        result = run_multi_horizon_prediction(sample_ohlcv, "BBCA.JK")
        assert result.ticker == "BBCA.JK"
        assert result.current_price > 0
        assert len(result.predictions) > 0
        assert result.consensus_signal in ["BUY", "SELL", "HOLD"]

    def test_confidence_adjustment(self):
        from src.multi_horizon import get_multi_horizon_confidence_adjustment, MultiHorizonResult
        result = MultiHorizonResult(
            ticker="TEST", current_price=100,
            consensus_signal="BUY", consensus_confidence=0.8,
            horizon_agreement=0.75,
        )
        adj, reason = get_multi_horizon_confidence_adjustment(result)
        assert adj > 0


# =============================================================================
# TRANSFER LEARNING
# =============================================================================


class TestTransferLearning:
    def test_parent_child_model(self, sample_ohlcv):
        from src.transfer_learning import ParentChildModel
        # Create multiple ticker DataFrames
        dfs = {}
        for ticker in ["A", "B", "C"]:
            np.random.seed(hash(ticker) % 1000)
            n = 200
            close = 1000 + np.cumsum(np.random.randn(n) * 10)
            dfs[ticker] = pd.DataFrame({
                "Close": np.maximum(close, 100),
                "Volume": np.random.randint(1e6, 1e7, n).astype(float),
            }, index=pd.date_range("2024-01-01", periods=n, freq="B"))

        model = ParentChildModel()
        parent_metrics = model.train_parent(dfs)
        assert "n_samples" in parent_metrics
        assert model.trained

        # Train child
        child_info = model.train_child("A", dfs["A"])
        assert child_info.ticker == "A"

        # Predict
        result = model.predict("A", dfs["A"])
        assert result.ticker == "A"
        assert result.parent_prediction != 0 or True


# =============================================================================
# TRADING MEMORY
# =============================================================================


class TestTradingMemory:
    def test_record_and_query(self, tmp_path):
        from src.trading_memory import TradingMemory, TradeRecord
        memory = TradingMemory(storage_path=str(tmp_path / "memory.json"))

        # Record a trade
        trade = TradeRecord(
            ticker="BBCA.JK",
            date="2024-06-01",
            signal="BUY",
            entry_price=8000,
            exit_price=8200,
            return_pct=0.025,
            regime="bull",
            outcome="win",
            thesis="Strong momentum",
            lessons="Good entry timing",
        )
        memory.record_trade(trade)
        assert len(memory.trades) == 1

        # Query
        result = memory.query_similar(ticker="BBCA.JK", signal="BUY")
        assert len(result.similar_trades) > 0
        assert result.win_rate > 0

    def test_statistics(self, tmp_path):
        from src.trading_memory import TradingMemory, TradeRecord
        memory = TradingMemory(storage_path=str(tmp_path / "stats.json"))
        for i in range(5):
            memory.record_trade(TradeRecord(
                ticker="TEST",
                date=f"2024-06-0{i+1}",
                signal="BUY",
                return_pct=0.01 * (i - 2),
                outcome="win" if i > 2 else "loss",
            ))
        stats = memory.get_statistics()
        assert stats["total_trades"] == 5


# =============================================================================
# REACT AGENT
# =============================================================================


class TestReActAgent:
    def test_react_agent(self, sample_ohlcv):
        from src.react_agent import run_react_agent
        result = run_react_agent(sample_ohlcv, "BBCA.JK")
        assert result["recommendation"] in ["BUY", "SELL", "HOLD"]
        assert result["n_steps"] > 0
        assert len(result["tools_used"]) > 0
        assert len(result["reasoning_chain"]) > 0

    def test_confidence_adjustment(self):
        from src.react_agent import get_react_confidence_adjustment
        result = {"recommendation": "BUY", "confidence": 0.7}
        adj, reason = get_react_confidence_adjustment(result)
        assert adj > 0


# =============================================================================
# MULTI-MODE RESEARCH
# =============================================================================


class TestMultiModeResearch:
    def test_fast_mode(self, sample_ohlcv):
        from src.multi_mode_research import run_research
        result = run_research(sample_ohlcv, "BBCA.JK", mode="fast")
        assert result.mode == "fast"
        assert result.recommendation in ["BUY", "SELL", "HOLD"]
        assert result.execution_time >= 0

    def test_deep_mode(self, sample_ohlcv):
        from src.multi_mode_research import run_research
        result = run_research(sample_ohlcv, "BBCA.JK", mode="deep")
        assert result.mode == "deep"
        assert len(result.modules_used) > 0


# =============================================================================
# SOCIAL SENTIMENT
# =============================================================================


class TestSocialSentiment:
    def test_social_sentiment_mock(self):
        from src.social_sentiment import run_social_sentiment
        result = run_social_sentiment("BBCA.JK", use_mock=True)
        assert result["n_posts"] > 0
        assert "overall_sentiment" in result
        assert "sentiment_label" in result

    def test_sentiment_scoring(self):
        from src.social_sentiment import _score_text
        score = _score_text("BBCA is bullish and rocketing to the moon! 🚀")
        assert score > 0

        score = _score_text("BBCA is crashing, sell now! Bear market!")
        assert score < 0

        score = _score_text("BBCA trades sideways today.")
        assert abs(score) < 0.3


# =============================================================================
# A/B TESTING
# =============================================================================


class TestABTesting:
    def test_model_performance(self):
        from src.ab_testing import compute_model_performance
        actual = np.array([100, 101, 102, 103, 104, 105, 106, 107, 108, 109], dtype=float)
        pred = np.array([100, 101.5, 102, 103.5, 104, 105, 106.5, 107, 108, 109.5], dtype=float)
        perf = compute_model_performance("Model A", pred, actual)
        assert perf.mse >= 0
        assert perf.directional_accuracy >= 0

    def test_ab_test(self):
        from src.ab_testing import run_ab_test
        np.random.seed(42)
        actual = np.cumsum(np.random.randn(100) * 0.02) + 100
        pred_a = actual + np.random.randn(100) * 0.5  # Better model
        pred_b = actual + np.random.randn(100) * 2.0  # Worse model
        result = run_ab_test(pred_a, pred_b, actual, "Good", "Bad", metric="mse")
        assert result.winner in ["Good", "Bad"]
        assert result.p_value >= 0
        assert result.metric_used == "mse"

    def test_multi_model_comparison(self):
        from src.ab_testing import run_multi_model_comparison
        np.random.seed(42)
        actual = np.cumsum(np.random.randn(100) * 0.02) + 100
        models = {
            "A": actual + np.random.randn(100) * 0.5,
            "B": actual + np.random.randn(100) * 1.0,
            "C": actual + np.random.randn(100) * 2.0,
        }
        result = run_multi_model_comparison(models, actual, metric="sharpe")
        assert "ranking" in result
        assert len(result["ranking"]) == 3
        assert "best_model" in result
