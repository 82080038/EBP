"""
Tests for new analysis modules:
- Wyckoff Method
- Elliott Wave
- Behavioral Finance
- Sector Rotation
- Factor Model
- DCF Valuation
"""

import pytest
import pandas as pd
import numpy as np
import sys
import os

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))


# =============================================================================
# Fixtures
# =============================================================================

@pytest.fixture
def sample_ohlc_data():
    """Generate sample OHLCV data for testing."""
    np.random.seed(42)
    n = 200
    dates = pd.bdate_range(start="2025-01-01", periods=n)
    close = 1000 + np.cumsum(np.random.randn(n) * 10)
    high = close + np.abs(np.random.randn(n) * 5)
    low = close - np.abs(np.random.randn(n) * 5)
    open_ = close + np.random.randn(n) * 3
    volume = np.random.randint(100000, 500000, n).astype(float)
    return pd.DataFrame({
        "Open": open_, "High": high, "Low": low,
        "Close": close, "Volume": volume,
    }, index=dates)


@pytest.fixture
def trending_up_data():
    """Generate trending up data."""
    np.random.seed(123)
    n = 200
    dates = pd.bdate_range(start="2025-01-01", periods=n)
    close = 1000 + np.arange(n) * 2 + np.random.randn(n) * 5
    high = close + 5
    low = close - 5
    open_ = close - 2
    volume = 200000 + np.random.randn(n) * 50000
    volume = np.abs(volume)
    return pd.DataFrame({
        "Open": open_, "High": high, "Low": low,
        "Close": close, "Volume": volume,
    }, index=dates)


@pytest.fixture
def trending_down_data():
    """Generate trending down data."""
    np.random.seed(456)
    n = 200
    dates = pd.bdate_range(start="2025-01-01", periods=n)
    close = 2000 - np.arange(n) * 3 + np.random.randn(n) * 5
    high = close + 5
    low = close - 5
    open_ = close + 2
    volume = 200000 + np.random.randn(n) * 50000
    volume = np.abs(volume)
    return pd.DataFrame({
        "Open": open_, "High": high, "Low": low,
        "Close": close, "Volume": volume,
    }, index=dates)


@pytest.fixture
def sample_market_data():
    """Generate market data dict for sector rotation tests."""
    np.random.seed(42)
    n = 200
    dates = pd.bdate_range(start="2025-01-01", periods=n)
    close_flat = 1000 + np.cumsum(np.random.randn(n) * 10)
    close_up = 1000 + np.arange(n) * 2 + np.random.randn(n) * 5
    close_down = 2000 - np.arange(n) * 3 + np.random.randn(n) * 5
    vol = np.abs(200000 + np.random.randn(n) * 50000)
    return {
        "IHSG": pd.DataFrame({"Open": close_flat-2, "High": close_flat+5, "Low": close_flat-5, "Close": close_flat, "Volume": vol}, index=dates),
        "S&P500": pd.DataFrame({"Open": close_up-2, "High": close_up+5, "Low": close_up-5, "Close": close_up, "Volume": vol}, index=dates),
        "GOLD": pd.DataFrame({"Open": close_up-2, "High": close_up+5, "Low": close_up-5, "Close": close_up, "Volume": vol}, index=dates),
        "OIL": pd.DataFrame({"Open": close_flat-2, "High": close_flat+5, "Low": close_flat-5, "Close": close_flat, "Volume": vol}, index=dates),
        "VIX": pd.DataFrame({"Open": close_down+2, "High": close_down+5, "Low": close_down-5, "Close": close_down, "Volume": vol}, index=dates),
        "USD_IDR": pd.DataFrame({"Open": close_flat-2, "High": close_flat+5, "Low": close_flat-5, "Close": close_flat, "Volume": vol}, index=dates),
    }


@pytest.fixture
def sample_fred_data():
    """Generate FRED data for sector rotation tests."""
    dates = pd.bdate_range(start="2025-01-01", periods=60)
    return {
        "TREASURY_10Y": pd.DataFrame({"value": np.linspace(4.0, 3.8, 60)}, index=dates),
        "CPI": pd.DataFrame({"value": np.linspace(300, 302, 60)}, index=dates),
        "UNEMPLOYMENT": pd.DataFrame({"value": np.linspace(4.0, 4.2, 60)}, index=dates),
        "FEDFUNDS": pd.DataFrame({"value": np.linspace(5.0, 4.5, 60)}, index=dates),
    }


# =============================================================================
# Wyckoff Tests
# =============================================================================

class TestWyckoff:
    def test_wyckoff_returns_analysis(self, sample_ohlc_data):
        from src.wyckoff import detect_wyckoff_phase
        result = detect_wyckoff_phase(sample_ohlc_data)
        assert result.phase in ["accumulation", "markup", "distribution", "markdown", "unclear"]
        assert -100 <= result.phase_score <= 100
        assert isinstance(result.recommendation, str)
        assert len(result.recommendation) > 0

    def test_wyckoff_trending_up(self, trending_up_data):
        from src.wyckoff import detect_wyckoff_phase
        result = detect_wyckoff_phase(trending_up_data)
        assert result.phase in ["markup", "accumulation", "distribution"]
        assert result.phase_score > 0  # Should be positive for uptrend

    def test_wyckoff_trending_down(self, trending_down_data):
        from src.wyckoff import detect_wyckoff_phase
        result = detect_wyckoff_phase(trending_down_data)
        assert result.phase in ["markdown", "distribution", "accumulation"]
        assert result.phase_score < 0  # Should be negative for downtrend

    def test_wyckoff_insufficient_data(self):
        from src.wyckoff import detect_wyckoff_phase
        small_df = pd.DataFrame({
            "Close": [100, 101], "High": [102, 103],
            "Low": [99, 100], "Volume": [1000, 2000],
        })
        result = detect_wyckoff_phase(small_df)
        assert result.phase == "unclear"

    def test_wyckoff_has_details(self, sample_ohlc_data):
        from src.wyckoff import detect_wyckoff_phase
        result = detect_wyckoff_phase(sample_ohlc_data)
        assert "cumulative_return_pct" in result.details
        assert "range_size_pct" in result.details
        assert "up_down_vol_ratio" in result.details


# =============================================================================
# Elliott Wave Tests
# =============================================================================

class TestElliottWave:
    def test_elliott_returns_analysis(self, sample_ohlc_data):
        from src.elliott_wave import detect_elliott_wave
        result = detect_elliott_wave(sample_ohlc_data)
        assert result.pattern_type in ["impulse", "corrective", "unclear"]
        assert isinstance(result.confidence, float)
        assert 0 <= result.confidence <= 100
        assert isinstance(result.recommendation, str)

    def test_elliott_trending_up(self, trending_up_data):
        from src.elliott_wave import detect_elliott_wave
        result = detect_elliott_wave(trending_up_data)
        assert result.pattern_type in ["impulse", "corrective", "unclear"]

    def test_elliott_insufficient_data(self):
        from src.elliott_wave import detect_elliott_wave
        small_df = pd.DataFrame({"Close": [100, 101, 102]})
        result = detect_elliott_wave(small_df)
        assert result.pattern_type == "unclear"

    def test_elliott_find_pivots(self, trending_up_data):
        from src.elliott_wave import find_pivots
        pivots = find_pivots(trending_up_data, "Close", lookback=5)
        assert len(pivots) > 0
        for idx, price, ptype in pivots:
            assert ptype in ["high", "low"]


# =============================================================================
# Behavioral Finance Tests
# =============================================================================

class TestBehavioral:
    def test_behavioral_returns_analysis(self, sample_ohlc_data):
        from src.behavioral import analyze_behavioral
        result = analyze_behavioral(sample_ohlc_data)
        assert result.overall_sentiment in ["extreme_fear", "fear", "neutral", "greed", "extreme_greed"]
        assert -100 <= result.sentiment_score <= 100
        assert result.contrarian_signal in ["buy", "sell", "cautious", "watch", "neutral"]
        assert isinstance(result.recommendations, list)
        assert len(result.recommendations) > 0

    def test_behavioral_fomo_detection(self, trending_up_data):
        from src.behavioral import detect_fomo
        score = detect_fomo(trending_up_data)
        assert 0 <= score <= 100

    def test_behavioral_panic_detection(self, trending_down_data):
        from src.behavioral import detect_panic
        score = detect_panic(trending_down_data)
        assert 0 <= score <= 100

    def test_behavioral_herding(self, sample_ohlc_data):
        from src.behavioral import detect_herding
        score = detect_herding(sample_ohlc_data)
        assert 0 <= score <= 100

    def test_behavioral_contrarian_sentiment(self, sample_ohlc_data):
        from src.behavioral import calc_contrarian_sentiment
        result = calc_contrarian_sentiment(sample_ohlc_data)
        assert "sentiment" in result
        assert "score" in result
        assert "contrarian_signal" in result
        assert -100 <= result["score"] <= 100


# =============================================================================
# Sector Rotation Tests
# =============================================================================

class TestSectorRotation:
    def test_sector_rotation_returns_analysis(self, sample_market_data, sample_fred_data):
        from src.sector_rotation import detect_economic_phase
        result = detect_economic_phase(sample_market_data, sample_fred_data)
        assert result.current_phase in ["reflation", "recovery", "overheating", "stagflation"]
        assert 0 <= result.phase_confidence <= 100
        assert isinstance(result.favored_sectors, list)
        assert isinstance(result.avoided_sectors, list)
        assert isinstance(result.recommendations, list)

    def test_sector_rotation_no_fred(self, sample_market_data):
        from src.sector_rotation import detect_economic_phase
        result = detect_economic_phase(sample_market_data, None)
        assert result.current_phase in ["reflation", "recovery", "overheating", "stagflation"]

    def test_sector_rotation_has_details(self, sample_market_data, sample_fred_data):
        from src.sector_rotation import detect_economic_phase
        result = detect_economic_phase(sample_market_data, sample_fred_data)
        assert "phase_scores" in result.details
        assert "equity_trend" in result.details


# =============================================================================
# Factor Model Tests
# =============================================================================

class TestFactorModel:
    def test_capm_regression(self, sample_ohlc_data, trending_up_data):
        from src.factor_model import run_capm_regression
        asset_ret = sample_ohlc_data["Close"].pct_change().dropna()
        market_ret = trending_up_data["Close"].pct_change().dropna()
        common = asset_ret.index.intersection(market_ret.index)
        result = run_capm_regression(asset_ret.loc[common], market_ret.loc[common])
        assert isinstance(result.alpha, float)
        assert isinstance(result.beta_market, float)
        assert 0 <= result.r_squared <= 1
        assert len(result.interpretation) > 0

    def test_factor_score(self):
        from src.factor_model import FactorExposure, calc_factor_score
        exposure = FactorExposure(
            alpha=0.001, beta_market=1.2, beta_size=0.3,
            beta_value=0.4, beta_momentum=0.5, r_squared=0.65,
        )
        score = calc_factor_score(exposure)
        assert "score" in score
        assert 0 <= score["score"] <= 100
        assert "rating" in score
        assert "factor_tilts" in score

    def test_fama_french_3factor(self, sample_ohlc_data, trending_up_data, trending_down_data):
        from src.factor_model import run_fama_french_3factor
        asset_ret = sample_ohlc_data["Close"].pct_change().dropna()
        market_ret = trending_up_data["Close"].pct_change().dropna()
        smb = trending_down_data["Close"].pct_change().dropna()  # Small caps
        hml = trending_up_data["Close"].pct_change().dropna() * 0.5  # Value
        result = run_fama_french_3factor(asset_ret, market_ret, smb, hml)
        assert isinstance(result.alpha, float)
        assert isinstance(result.beta_market, float)

    def test_insufficient_data(self):
        from src.factor_model import run_capm_regression
        short_ret = pd.Series([0.01, 0.02, 0.03])
        result = run_capm_regression(short_ret, short_ret)
        # With only 3 data points, regression still runs but is unreliable
        assert isinstance(result.beta_market, float)


# =============================================================================
# DCF Valuation Tests
# =============================================================================

class TestDCFValuation:
    def test_dcf_wacc_calculation(self):
        from src.dcf_valuation import calc_wacc
        wacc = calc_wacc(
            market_cap=1e12, total_debt=2e11, total_cash=5e10,
            cost_of_equity=0.10, cost_of_debt=0.05, tax_rate=0.22,
        )
        assert 0.05 < wacc < 0.15

    def test_dcf_cost_of_equity(self):
        from src.dcf_valuation import calc_cost_of_equity
        coe = calc_cost_of_equity(risk_free_rate=0.04, market_risk_premium=0.07, beta=1.2)
        assert abs(coe - 0.124) < 0.01  # 0.04 + 1.2 * 0.07 = 0.124

    def test_dcf_fcf_projection(self):
        from src.dcf_valuation import estimate_fcf
        projections = estimate_fcf(1e9, 2e8, growth_rate=0.10, years=5)
        assert len(projections) == 5
        assert projections[0] > 0
        assert projections[4] > projections[0]  # Growing

    def test_dcf_terminal_value(self):
        from src.dcf_valuation import calc_terminal_value
        tv = calc_terminal_value(1e9, terminal_growth=0.03, wacc=0.10)
        expected = 1e9 * 1.03 / (0.10 - 0.03)
        assert abs(tv - expected) < 1e6

    def test_dcf_terminal_value_edge_case(self):
        from src.dcf_valuation import calc_terminal_value
        # When WACC <= terminal growth, should fallback
        tv = calc_terminal_value(1e9, terminal_growth=0.10, wacc=0.05)
        assert tv > 0  # Should not crash


# =============================================================================
# Integration Test
# =============================================================================

class TestIntegration:
    def test_all_modules_import(self):
        """Verify all new modules can be imported."""
        from src.wyckoff import detect_wyckoff_phase
        from src.elliott_wave import detect_elliott_wave
        from src.behavioral import analyze_behavioral
        from src.sector_rotation import detect_economic_phase
        from src.factor_model import run_capm_regression, calc_factor_score
        from src.dcf_valuation import run_dcf, calc_wacc

    def test_predictor_has_new_fields(self):
        """Verify predictor imports new modules."""
        import src.predictor as pred
        # Check that the new imports are in the module
        assert hasattr(pred, 'detect_wyckoff_phase')
        assert hasattr(pred, 'detect_elliott_wave')
        assert hasattr(pred, 'analyze_behavioral')
        assert hasattr(pred, 'detect_economic_phase')
        assert hasattr(pred, 'run_capm_regression')
