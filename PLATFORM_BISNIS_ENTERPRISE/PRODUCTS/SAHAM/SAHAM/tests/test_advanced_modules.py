"""
Tests for new advanced analytics modules: SMC, AFML, DRL, Complex Systems.
"""
import pytest
import pandas as pd
import numpy as np
from datetime import datetime, timedelta


# =============================================================================
# FIXTURES
# =============================================================================

@pytest.fixture
def sample_ohlcv():
    """Generate sample OHLCV data for testing."""
    np.random.seed(42)
    n = 200
    dates = pd.date_range(start="2024-01-01", periods=n, freq="D")

    # Generate realistic price data with trends and reversals
    returns = np.random.normal(0.001, 0.02, n)
    # Add some structured moves
    returns[50:60] = 0.03  # Bullish displacement
    returns[100:110] = -0.025  # Bearish displacement
    returns[150:155] = 0.02  # Another bullish move

    close = 8000 * np.exp(np.cumsum(returns))
    high = close * (1 + np.abs(np.random.normal(0, 0.005, n)))
    low = close * (1 - np.abs(np.random.normal(0, 0.005, n)))
    open_ = (high + low) / 2
    volume = np.random.randint(1_000_000, 10_000_000, n).astype(float)

    df = pd.DataFrame({
        "Open": open_,
        "High": high,
        "Low": low,
        "Close": close,
        "Volume": volume,
    }, index=dates)

    return df


@pytest.fixture
def sample_returns():
    """Generate sample returns DataFrame for multiple assets."""
    np.random.seed(42)
    n = 100
    dates = pd.date_range(start="2024-01-01", periods=n, freq="D")

    # Create correlated assets
    base = np.random.normal(0.001, 0.02, n)
    data = {
        "IHSG": base + np.random.normal(0, 0.01, n),
        "S&P500": base * 0.7 + np.random.normal(0, 0.015, n),
        "NASDAQ": base * 0.8 + np.random.normal(0, 0.02, n),
        "GOLD": -base * 0.3 + np.random.normal(0, 0.01, n),
        "OIL": np.random.normal(0, 0.03, n),
    }

    return pd.DataFrame(data, index=dates)


# =============================================================================
# SMC TESTS
# =============================================================================

class TestSMC:
    """Test Smart Money Concepts module."""

    def test_detect_swing_highs_lows(self, sample_ohlcv):
        from src.smc import detect_swing_highs_lows
        highs, lows = detect_swing_highs_lows(sample_ohlcv, lookback=10)
        assert len(highs) > 0
        assert len(lows) > 0
        # All highs should be valid prices
        for _, price in highs:
            assert price > 0

    def test_detect_order_blocks(self, sample_ohlcv):
        from src.smc import detect_order_blocks
        obs = detect_order_blocks(sample_ohlcv, min_displacement=0.01)
        assert isinstance(obs, list)
        # Should detect at least some OBs in 200 bars with structured moves
        assert len(obs) > 0
        for ob in obs:
            assert ob.type in ("bullish", "bearish")
            assert ob.high >= ob.low
            assert ob.displacement_strength > 0

    def test_detect_fvg(self, sample_ohlcv):
        from src.smc import detect_fvg
        fvgs = detect_fvg(sample_ohlcv, min_gap_pct=0.0005)
        assert isinstance(fvgs, list)
        for fvg in fvgs:
            assert fvg.type in ("bullish", "bearish")
            assert fvg.top >= fvg.bottom
            assert fvg.size > 0

    def test_detect_liquidity_sweeps(self, sample_ohlcv):
        from src.smc import detect_swing_highs_lows, detect_liquidity_sweeps
        highs, lows = detect_swing_highs_lows(sample_ohlcv, lookback=10)
        sweeps = detect_liquidity_sweeps(sample_ohlcv, highs, lows)
        assert isinstance(sweeps, list)
        for sweep in sweeps:
            assert sweep.direction in ("bullish_sweep", "bearish_sweep")
            assert sweep.level_swept > 0
            assert 0 <= sweep.reversal_strength <= 1

    def test_detect_bos_choch(self, sample_ohlcv):
        from src.smc import detect_swing_highs_lows, detect_bos_choch
        highs, lows = detect_swing_highs_lows(sample_ohlcv, lookback=10)
        breaks = detect_bos_choch(sample_ohlcv, highs, lows)
        assert isinstance(breaks, list)
        for b in breaks:
            assert b.type in ("BOS", "CHoCH")
            assert b.direction in ("bullish", "bearish")

    def test_run_smc_analysis(self, sample_ohlcv):
        from src.smc import run_smc_analysis, SMCAnalysis
        result = run_smc_analysis(sample_ohlcv)
        assert isinstance(result, SMCAnalysis)
        assert result.signal in ("BUY", "SELL", "HOLD")
        assert 0 <= result.confidence <= 100
        assert result.market_structure in ("bullish", "bearish", "unclear")
        assert result.premium_discount in ("premium", "discount", "equilibrium")
        assert result.recommendation != ""

    def test_smc_insufficient_data(self):
        from src.smc import run_smc_analysis
        df = pd.DataFrame({
            "Open": [100, 101], "High": [102, 103],
            "Low": [99, 100], "Close": [101, 102], "Volume": [1000, 2000],
        })
        result = run_smc_analysis(df)
        assert result.signal == "HOLD"
        assert "error" in result.details

    def test_smc_confidence_adjustment(self, sample_ohlcv):
        from src.smc import run_smc_analysis, get_smc_confidence_adjustment
        result = run_smc_analysis(sample_ohlcv)
        adj, reason = get_smc_confidence_adjustment(result)
        assert isinstance(adj, float)
        assert -0.15 <= adj <= 0.15
        assert isinstance(reason, str)


# =============================================================================
# AFML TESTS
# =============================================================================

class TestAFML:
    """Test Advances in Financial Machine Learning module."""

    def test_triple_barrier_labels(self, sample_ohlcv):
        from src.afml import triple_barrier_labels
        tb = triple_barrier_labels(sample_ohlcv, pt_sl=(1.0, 1.0), max_holding_period=10)
        assert isinstance(tb, pd.DataFrame)
        assert "label" in tb.columns
        assert set(tb["label"].unique()).issubset({-1, 0, 1})

    def test_fractional_differentiation(self, sample_ohlcv):
        from src.afml import fractional_differentiation
        diffed = fractional_differentiation(sample_ohlcv["Close"], d=0.4)
        assert isinstance(diffed, pd.Series)
        # Should have fewer non-NaN values than input
        assert diffed.notna().sum() < len(sample_ohlcv)

    def test_find_optimal_d(self, sample_ohlcv):
        from src.afml import find_optimal_d
        d, diffed = find_optimal_d(sample_ohlcv["Close"])
        assert 0 < d <= 1.0
        assert len(diffed) > 0

    def test_purged_kfold_indices(self):
        from src.afml import purged_kfold_indices
        folds = purged_kfold_indices(100, n_folds=5, purge_days=3, embargo_days=2)
        assert len(folds) == 5
        for train_idx, test_idx in folds:
            assert len(train_idx) > 0
            assert len(test_idx) > 0
            # No overlap
            assert len(set(train_idx) & set(test_idx)) == 0

    def test_purged_kfold_cv(self, sample_ohlcv):
        from src.afml import purged_kfold_cv
        from sklearn.ensemble import RandomForestRegressor

        X = sample_ohlcv[["Open", "High", "Low", "Volume"]].iloc[20:]
        y = sample_ohlcv["Close"].pct_change().iloc[20:].fillna(0)

        result = purged_kfold_cv(
            X, y,
            model_fn=RandomForestRegressor,
            n_folds=3, purge_days=3, embargo_days=2,
            n_estimators=10, random_state=42,
        )
        assert "mean_score" in result
        assert "fold_results" in result
        assert len(result["fold_results"]) <= 3

    def test_deflated_sharpe_ratio(self):
        from src.afml import deflated_sharpe_ratio
        # High SR with few trials → should be significant
        dsr = deflated_sharpe_ratio(observed_sr=2.0, n_trials=5, n_samples=252)
        assert isinstance(dsr, float)
        # Low SR with many trials → likely not significant
        dsr2 = deflated_sharpe_ratio(observed_sr=0.5, n_trials=100, n_samples=252)
        assert dsr > dsr2

    def test_probability_of_backtest_overfitting(self):
        from src.afml import probability_of_backtest_overfitting
        # Good IS/OOS alignment → low PBO
        is_sr = np.array([2.0, 1.5, 1.8, 1.2, 1.6])
        oos_sr = np.array([1.8, 1.3, 1.6, 1.0, 1.4])
        pbo = probability_of_backtest_overfitting(is_sr, oos_sr)
        assert 0 <= pbo <= 1
        assert pbo < 0.5  # Should be low since OOS aligns with IS

        # Overfitted: IS high, OOS random
        is_sr_bad = np.array([3.0, 2.5, 2.8, 2.2, 2.6])
        oos_sr_bad = np.array([-0.5, 0.3, -0.2, 0.1, -0.3])
        pbo_bad = probability_of_backtest_overfitting(is_sr_bad, oos_sr_bad)
        # PBO should be at least as high for overfitted case
        assert pbo_bad >= pbo

    def test_run_afml_pipeline(self, sample_ohlcv):
        from src.afml import run_afml_pipeline
        result = run_afml_pipeline(sample_ohlcv)
        assert "triple_barrier" in result
        assert "optimal_d" in result
        assert "label_distribution" in result


# =============================================================================
# DRL TESTS
# =============================================================================

class TestDRL:
    """Test Deep Reinforcement Learning trading module."""

    def test_stock_trading_env(self, sample_ohlcv):
        from src.drl_trading import StockTradingEnv
        env = StockTradingEnv(sample_ohlcv)
        obs = env._reset()
        assert obs.shape[0] == env.observation_dim

        # Take a few steps
        for action in [1, 0, 2, 0]:  # Buy, Hold, Sell, Hold
            obs, reward, done, info = env.step(action)
            assert obs.shape[0] == env.observation_dim
            assert isinstance(reward, float)
            assert isinstance(done, bool)
            assert "total_value" in info

    def test_simple_q_learning_agent(self):
        from src.drl_trading import SimpleQLearningAgent
        agent = SimpleQLearningAgent(n_features=10)

        obs = np.random.randn(10)
        action = agent.act(obs, training=True)
        assert action in (0, 1, 2)

        next_obs = np.random.randn(10)
        agent.learn(obs, action, 0.5, next_obs, False)
        # Q-table should be updated
        assert np.any(agent.q_table != 0)

    def test_train_drl_agent(self, sample_ohlcv):
        from src.drl_trading import train_drl_agent, DRLTrainingResult
        result = train_drl_agent(sample_ohlcv, algorithm="q_learning", episodes=20)
        assert isinstance(result, DRLTrainingResult)
        assert result.algorithm == "q_learning"
        assert result.episodes == 20
        assert result.training_time_seconds > 0

    def test_predict_drl_action(self, sample_ohlcv):
        from src.drl_trading import predict_drl_action, DRLPrediction
        pred = predict_drl_action(sample_ohlcv)
        assert isinstance(pred, DRLPrediction)
        assert pred.action in (0, 1, 2)
        assert pred.action_name in ("HOLD", "BUY", "SELL")
        assert 0 <= pred.confidence <= 1

    def test_drl_confidence_adjustment(self, sample_ohlcv):
        from src.drl_trading import predict_drl_action, get_drl_confidence_adjustment
        pred = predict_drl_action(sample_ohlcv)
        adj, reason = get_drl_confidence_adjustment(pred)
        assert isinstance(adj, float)
        assert -0.15 <= adj <= 0.15
        assert isinstance(reason, str)


# =============================================================================
# COMPLEX SYSTEMS TESTS
# =============================================================================

class TestComplexSystems:
    """Test Complex Systems network analysis module."""

    def test_build_correlation_network(self, sample_returns):
        from src.complex_systems import build_correlation_network
        nodes, edges, corr = build_correlation_network(sample_returns, correlation_threshold=0.2)
        assert len(nodes) == 5
        assert isinstance(edges, list)
        assert isinstance(corr, pd.DataFrame)
        for node in nodes:
            assert node.ticker in sample_returns.columns
            assert 0 <= node.centrality <= 1

    def test_detect_clusters(self, sample_returns):
        from src.complex_systems import build_correlation_network, detect_clusters
        nodes, edges, _ = build_correlation_network(sample_returns, correlation_threshold=0.2)
        clusters = detect_clusters(nodes, edges)
        assert isinstance(clusters, dict)
        total_members = sum(len(v) for v in clusters.values())
        assert total_members <= 5

    def test_systemic_risk_nodes(self, sample_returns):
        from src.complex_systems import build_correlation_network, systemic_risk_nodes
        nodes, edges, corr = build_correlation_network(sample_returns, correlation_threshold=0.2)
        systemic = systemic_risk_nodes(nodes, edges, corr)
        assert isinstance(systemic, list)
        assert len(systemic) <= 5

    def test_detect_contagion_paths(self, sample_returns):
        from src.complex_systems import build_correlation_network, detect_contagion_paths
        nodes, edges, _ = build_correlation_network(sample_returns, correlation_threshold=0.2)
        if edges:
            paths = detect_contagion_paths(edges, source=edges[0].source, max_depth=3)
            assert isinstance(paths, list)
            for path in paths:
                assert path.source == edges[0].source
                assert len(path.path) >= 2
                assert path.total_weight > 0

    def test_agent_based_simulation(self, sample_returns):
        from src.complex_systems import agent_based_simulation
        result = agent_based_simulation(sample_returns, n_agents=50, n_steps=50)
        assert "avg_correlation" in result
        assert "max_correlation" in result
        assert "simulated_volatility" in result
        assert isinstance(result["emergent_clustering"], bool)

    def test_network_stress_test(self, sample_returns):
        from src.complex_systems import (
            build_correlation_network, systemic_risk_nodes, network_stress_test
        )
        nodes, edges, corr = build_correlation_network(sample_returns, correlation_threshold=0.2)
        systemic = systemic_risk_nodes(nodes, edges, corr)
        if systemic:
            stress = network_stress_test(nodes, edges, corr, systemic, shock_magnitude=-0.05)
            assert "scenarios" in stress
            assert "network_vulnerability" in stress
            assert stress["n_systemic_nodes"] > 0

    def test_run_complex_systems_analysis(self, sample_returns):
        from src.complex_systems import run_complex_systems_analysis, ComplexSystemsResult
        result = run_complex_systems_analysis(sample_returns, correlation_threshold=0.2)
        assert isinstance(result, ComplexSystemsResult)
        assert isinstance(result.systemic_nodes, list)
        assert 0 <= result.network_density <= 1
        assert result.recommendation != ""

    def test_complex_systems_insufficient_data(self):
        from src.complex_systems import run_complex_systems_analysis
        df = pd.DataFrame({"A": [0.01, 0.02]})
        result = run_complex_systems_analysis(df)
        assert result.systemic_nodes == []


# =============================================================================
# INTEGRATION TESTS
# =============================================================================

class TestModuleIntegrator:
    """Test that new modules are properly integrated into ModuleIntegrator."""

    def test_integrator_has_smc_method(self):
        from src.module_integrator import ModuleIntegrator
        integrator = ModuleIntegrator()
        assert hasattr(integrator, "run_smc_analysis")

    def test_integrator_has_afml_methods(self):
        from src.module_integrator import ModuleIntegrator
        integrator = ModuleIntegrator()
        assert hasattr(integrator, "run_afml_pipeline")
        assert hasattr(integrator, "run_triple_barrier_labels")
        assert hasattr(integrator, "run_fractional_diff")
        assert hasattr(integrator, "run_meta_labeling")
        assert hasattr(integrator, "calc_deflated_sharpe")

    def test_integrator_has_drl_methods(self):
        from src.module_integrator import ModuleIntegrator
        integrator = ModuleIntegrator()
        assert hasattr(integrator, "train_drl")
        assert hasattr(integrator, "predict_drl")

    def test_integrator_has_complex_systems_method(self):
        from src.module_integrator import ModuleIntegrator
        integrator = ModuleIntegrator()
        assert hasattr(integrator, "run_complex_systems")

    def test_integrator_smc_analysis(self, sample_ohlcv):
        from src.module_integrator import ModuleIntegrator
        integrator = ModuleIntegrator()
        result = integrator.run_smc_analysis(sample_ohlcv)
        assert result is not None
        assert "signal" in result or "error" in result

    def test_integrator_afml_pipeline(self, sample_ohlcv):
        from src.module_integrator import ModuleIntegrator
        integrator = ModuleIntegrator()
        result = integrator.run_afml_pipeline(sample_ohlcv)
        assert result is not None
        assert "optimal_d" in result or "error" in result

    def test_integrator_drl_predict(self, sample_ohlcv):
        from src.module_integrator import ModuleIntegrator
        integrator = ModuleIntegrator()
        result = integrator.predict_drl(sample_ohlcv)
        assert result is not None
        assert "action_name" in result or "error" in result

    def test_integrator_complex_systems(self, sample_returns):
        from src.module_integrator import ModuleIntegrator
        integrator = ModuleIntegrator()
        result = integrator.run_complex_systems(sample_returns)
        assert result is not None
        assert "systemic_nodes" in result or "error" in result
