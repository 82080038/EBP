"""
Tests for Multi-Timeframe Analysis (MTF) and Enhanced Portfolio Optimization.

Covers:
- MTF: confluence scoring, timeframe signals, confidence adjustment
- Portfolio: Black-Litterman, Risk Parity, HRP, CVaR, comparison
"""

import pytest
import numpy as np
import pandas as pd
from datetime import datetime, timedelta


# =============================================================================
# FIXTURES
# =============================================================================

@pytest.fixture
def sample_daily_data():
    """Generate 200 days of synthetic OHLCV data with uptrend."""
    np.random.seed(42)
    n = 200
    dates = pd.date_range(end=datetime.now(), periods=n, freq="D")
    base = 7000
    trend = np.cumsum(np.random.randn(n) * 20 + 5)  # Slight uptrend
    close = base + trend
    high = close + np.abs(np.random.randn(n) * 30)
    low = close - np.abs(np.random.randn(n) * 30)
    open_ = close + np.random.randn(n) * 10
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
def sample_returns_df():
    """Generate synthetic returns for 5 assets."""
    np.random.seed(42)
    n = 252
    data = {}
    for i, ticker in enumerate(["BBCA", "BBRI", "BMRI", "TLKM", "ASII"]):
        mu = 0.0003 + i * 0.0001
        sigma = 0.012 + i * 0.002
        data[ticker] = np.random.normal(mu, sigma, n)
    return pd.DataFrame(data)


# =============================================================================
# MTF TESTS
# =============================================================================

class TestMTF:
    def test_mtf_returns_result(self, sample_daily_data):
        from src.mtf import run_mtf_analysis
        result = run_mtf_analysis(sample_daily_data)
        assert result is not None
        assert 0 <= result.confluence_score <= 100
        assert result.confluence_signal in ("BUY", "SELL", "HOLD")
        assert result.confluence_strength in ("Strong", "Moderate", "Weak", "Mixed")
        assert len(result.timeframe_signals) == 4

    def test_mtf_timeframe_signals(self, sample_daily_data):
        from src.mtf import run_mtf_analysis
        result = run_mtf_analysis(sample_daily_data)
        tfs = [sig.timeframe for sig in result.timeframe_signals]
        assert "1W" in tfs
        assert "1D" in tfs

    def test_mtf_each_signal_has_valid_fields(self, sample_daily_data):
        from src.mtf import run_mtf_analysis
        result = run_mtf_analysis(sample_daily_data)
        for sig in result.timeframe_signals:
            assert sig.signal in ("BUY", "SELL", "HOLD")
            assert 0 <= sig.score <= 100
            assert sig.trend in ("Uptrend", "Downtrend", "Sideways")

    def test_mtf_aligned_counts(self, sample_daily_data):
        from src.mtf import run_mtf_analysis
        result = run_mtf_analysis(sample_daily_data)
        assert result.aligned_bullish + result.aligned_bearish <= result.total_timeframes
        assert result.total_timeframes == 4

    def test_mtf_summary_not_empty(self, sample_daily_data):
        from src.mtf import run_mtf_analysis
        result = run_mtf_analysis(sample_daily_data)
        assert len(result.summary) > 0
        assert "MTF" in result.summary

    def test_mtf_confidence_adjustment_strong(self):
        from src.mtf import MTFResult, TimeframeSignal, get_mtf_confidence_adjustment
        result = MTFResult(
            confluence_signal="BUY",
            confluence_strength="Strong",
            timeframe_signals=[
                TimeframeSignal(timeframe="1W", signal="BUY", score=70, trend="Uptrend"),
                TimeframeSignal(timeframe="1D", signal="BUY", score=65, trend="Uptrend"),
                TimeframeSignal(timeframe="4H", signal="BUY", score=68, trend="Uptrend"),
                TimeframeSignal(timeframe="1H", signal="BUY", score=72, trend="Uptrend"),
            ],
            aligned_bullish=4,
            aligned_bearish=0,
            total_timeframes=4,
        )
        adj, reason = get_mtf_confidence_adjustment(result)
        assert adj > 1.0
        assert "Strong" in reason

    def test_mtf_confidence_adjustment_mixed(self):
        from src.mtf import MTFResult, get_mtf_confidence_adjustment
        result = MTFResult(
            confluence_signal="HOLD",
            confluence_strength="Mixed",
            aligned_bullish=2,
            aligned_bearish=2,
            total_timeframes=4,
        )
        adj, reason = get_mtf_confidence_adjustment(result)
        assert adj < 1.0
        assert "Mixed" in reason

    def test_mtf_empty_data(self):
        from src.mtf import run_mtf_analysis
        empty_df = pd.DataFrame(columns=["Open", "High", "Low", "Close", "Volume"])
        result = run_mtf_analysis(empty_df)
        assert result.confluence_score == pytest.approx(50.0)
        assert result.confluence_signal == "HOLD"

    def test_mtf_custom_timeframes(self, sample_daily_data):
        from src.mtf import run_mtf_analysis
        result = run_mtf_analysis(sample_daily_data, timeframes=["1W", "1D"])
        assert result.total_timeframes == 2
        assert len(result.timeframe_signals) == 2


# =============================================================================
# PORTFOLIO TESTS
# =============================================================================

class TestBlackLitterman:
    def test_bl_returns_weights(self, sample_returns_df):
        from src.portfolio import black_litterman_optimize
        result = black_litterman_optimize(sample_returns_df)
        assert "weights" in result
        assert len(result["weights"]) == 5
        assert abs(sum(result["weights"].values()) - 1.0) < 0.01
        assert "bl_expected_returns" in result
        assert "implied_returns" in result

    def test_bl_with_views(self, sample_returns_df):
        from src.portfolio import black_litterman_optimize
        n = len(sample_returns_df.columns)
        views = np.array([0.15, 0.10, 0.12, 0.08, 0.20])
        confidence = np.array([0.01, 0.02, 0.01, 0.03, 0.01])
        result = black_litterman_optimize(
            sample_returns_df, views=views, view_confidence=confidence
        )
        assert "weights" in result
        assert abs(sum(result["weights"].values()) - 1.0) < 0.01

    def test_bl_single_asset_error(self):
        from src.portfolio import black_litterman_optimize
        df = pd.DataFrame({"A": np.random.randn(100)})
        result = black_litterman_optimize(df)
        assert "error" in result


class TestRiskParity:
    def test_rp_returns_weights(self, sample_returns_df):
        from src.portfolio import risk_parity_optimize
        result = risk_parity_optimize(sample_returns_df)
        assert "weights" in result
        assert len(result["weights"]) == 5
        assert abs(sum(result["weights"].values()) - 1.0) < 0.01

    def test_rp_equal_risk_contribution(self, sample_returns_df):
        from src.portfolio import risk_parity_optimize
        result = risk_parity_optimize(sample_returns_df)
        contribs = result["risk_contributions"]
        values = list(contribs.values())
        # Risk contributions should be roughly equal (~0.20 each for 5 assets)
        for v in values:
            assert 0.10 < v < 0.35

    def test_rp_low_vol_higher_weight(self, sample_returns_df):
        from src.portfolio import risk_parity_optimize
        result = risk_parity_optimize(sample_returns_df)
        # BBCA has lowest vol (index 0) → should have higher weight than ASII (index 4, highest vol)
        weights = result["weights"]
        assert weights["BBCA"] >= weights["ASII"] - 0.05  # Allow some tolerance


class TestHRP:
    def test_hrp_returns_weights(self, sample_returns_df):
        from src.portfolio import hrp_optimize
        result = hrp_optimize(sample_returns_df)
        assert "weights" in result
        assert len(result["weights"]) == 5
        assert abs(sum(result["weights"].values()) - 1.0) < 0.01

    def test_hrp_method_label(self, sample_returns_df):
        from src.portfolio import hrp_optimize
        result = hrp_optimize(sample_returns_df)
        assert "HRP" in result["method"]

    def test_hrp_all_positive_weights(self, sample_returns_df):
        from src.portfolio import hrp_optimize
        result = hrp_optimize(sample_returns_df)
        for w in result["weights"].values():
            assert w >= 0


class TestCVaR:
    def test_cvar_returns_weights(self, sample_returns_df):
        from src.portfolio import cvar_optimize
        result = cvar_optimize(sample_returns_df)
        assert "weights" in result
        assert len(result["weights"]) == 5
        assert abs(sum(result["weights"].values()) - 1.0) < 0.01

    def test_cvar_has_var_cvar(self, sample_returns_df):
        from src.portfolio import cvar_optimize
        result = cvar_optimize(sample_returns_df)
        assert "var_95" in result
        assert "cvar_95" in result
        assert result["cvar_95"] >= result["var_95"]  # CVaR >= VaR

    def test_cvar_alpha_parameter(self, sample_returns_df):
        from src.portfolio import cvar_optimize
        result_95 = cvar_optimize(sample_returns_df, alpha=0.05)
        result_99 = cvar_optimize(sample_returns_df, alpha=0.01)
        # 99% CVaR should be worse (larger loss) than 95% CVaR
        assert result_99["cvar_95"] >= result_95["cvar_95"]


class TestPortfolioComparison:
    def test_comparison_returns_all_methods(self, sample_returns_df):
        from src.portfolio import compare_portfolio_methods
        result = compare_portfolio_methods(sample_returns_df)
        assert "markowitz" in result
        assert "black_litterman" in result
        assert "risk_parity" in result
        assert "hrp" in result
        assert "cvar" in result
        assert "comparison" in result
        assert isinstance(result["comparison"], pd.DataFrame)
        assert len(result["comparison"]) >= 4

    def test_comparison_sorted_by_sharpe(self, sample_returns_df):
        from src.portfolio import compare_portfolio_methods
        result = compare_portfolio_methods(sample_returns_df)
        df = result["comparison"]
        sharpes = df["Sharpe"].tolist()
        assert sharpes == sorted(sharpes, reverse=True)
