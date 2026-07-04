"""
Tests for anti-manipulation metrics (Blueprint Bab 3 & 4).

Covers:
- Z-Score Volume Shock (Bab 4.1)
- Amihud Illiquidity Ratio (Bab 4.1)
- Beneish M-Score (Bab 4.2)
- Wash Trading Detection (Bab 3.1)
- Spoofing Detection (Bab 3.1)
- Fake News Hype Detection (Bab 3.2)
- Combined anti-manipulation scan
- FraudDetector integration (Layer 5 & 6)
"""

import numpy as np
import pandas as pd
import pytest
from datetime import datetime

from src.anti_manipulation import (
    calc_volume_shock,
    scan_volume_anomalies,
    calc_amihud_illiquidity,
    scan_illiquidity,
    calc_beneish_m_score,
    run_anti_manipulation_scan,
    detect_wash_trading,
    detect_spoofing,
    detect_fake_news_hype,
    VolumeShockResult,
    IlliquidityResult,
    BeneishResult,
    AntiManipulationReport,
    WashTradingResult,
    SpoofingResult,
    FakeNewsHypeResult,
)
from src.fraud_detection import FraudDetector


# =============================================================================
# FIXTURES
# =============================================================================

@pytest.fixture
def normal_volume_data():
    """Normal volume data — no anomalies."""
    np.random.seed(42)
    volumes = pd.Series(np.random.lognormal(mean=14, sigma=0.3, size=60).astype(int))
    return volumes


@pytest.fixture
def pump_volume_data():
    """Volume data with a pump (spike at the end)."""
    np.random.seed(42)
    base = np.random.lognormal(mean=14, sigma=0.3, size=60).astype(int)
    base[-1] = int(base.mean() * 10)  # 10x average = massive spike
    return pd.Series(base)


@pytest.fixture
def liquid_market_data():
    """Market data with liquid blue chip stocks."""
    np.random.seed(42)
    data = {}
    for name in ["BBCA.JK", "BBRI.JK", "TLKM.JK"]:
        n = 60
        prices = pd.Series(np.cumsum(np.random.randn(n) * 0.5) + 8000)
        volumes = pd.Series(np.random.randint(1_000_000, 10_000_000, size=n))
        data[name] = pd.DataFrame({"Close": prices, "Volume": volumes})
    return data


@pytest.fixture
def illiquid_market_data():
    """Market data with illiquid stock (low volume, high price impact)."""
    np.random.seed(42)
    data = {}
    # Liquid stock
    p1 = pd.Series(np.cumsum(np.random.randn(60) * 0.5) + 8000)
    v1 = pd.Series(np.random.randint(1_000_000, 10_000_000, size=60))
    data["BBCA.JK"] = pd.DataFrame({"Close": p1, "Volume": v1})
    # Illiquid stock (tiny volume, big price swings)
    p2 = pd.Series(np.cumsum(np.random.randn(60) * 50) + 500)
    v2 = pd.Series(np.random.randint(100, 500, size=60))  # Very low volume
    data["GORENG.JK"] = pd.DataFrame({"Close": p2, "Volume": v2})
    return data


@pytest.fixture
def healthy_financials():
    """Healthy company financials — should NOT be flagged as manipulator."""
    return {
        "receivables_t": 1000, "receivables_t1": 950,
        "sales_t": 10000, "sales_t1": 9000,
        "cogs_t": 6000, "cogs_t1": 5500,
        "ppe_t": 5000, "ppe_t1": 4800,
        "total_assets_t": 15000, "total_assets_t1": 14000,
        "depreciation_t": 500, "depreciation_t1": 480,
        "sga_t": 1500, "sga_t1": 1400,
        "total_debt_t": 3000, "total_debt_t1": 2800,
        "net_income_t": 1500, "cash_flow_ops_t": 1400,
    }


@pytest.fixture
def manipulator_financials():
    """Manipulator company financials — SHOULD be flagged (high accruals, rising receivables)."""
    return {
        "receivables_t": 5000, "receivables_t1": 1000,    # Receivables 5x jump
        "sales_t": 10000, "sales_t1": 9000,
        "cogs_t": 6000, "cogs_t1": 5500,
        "ppe_t": 5000, "ppe_t1": 4800,
        "total_assets_t": 15000, "total_assets_t1": 14000,
        "depreciation_t": 500, "depreciation_t1": 480,
        "sga_t": 1500, "sga_t1": 1400,
        "total_debt_t": 3000, "total_debt_t1": 2800,
        "net_income_t": 2000, "cash_flow_ops_t": 200,     # Huge accruals (NI >> CFO)
    }


# =============================================================================
# Z-SCORE VOLUME SHOCK TESTS
# =============================================================================

class TestVolumeShock:
    def test_normal_volume_no_anomaly(self, normal_volume_data):
        result = calc_volume_shock(normal_volume_data, ticker="BBCA.JK")
        assert isinstance(result, VolumeShockResult)
        assert result.ticker == "BBCA.JK"
        assert not result.is_anomaly
        assert result.severity in ("normal", "elevated")  # random data may be slightly elevated
        assert abs(result.z_score) < 3.0

    def test_pump_volume_detected(self, pump_volume_data):
        result = calc_volume_shock(pump_volume_data, ticker="GORENG.JK")
        assert result.is_anomaly
        assert result.severity == "critical"
        assert result.z_score > 3.0
        assert "pump" in result.description.lower()

    def test_insufficient_data(self):
        volumes = pd.Series([1000, 2000, 3000])
        result = calc_volume_shock(volumes, ticker="TEST")
        assert not result.is_anomaly
        assert "insufficient" in result.description.lower()

    def test_custom_threshold(self, pump_volume_data):
        # With very high threshold, even pump might not trigger
        result = calc_volume_shock(pump_volume_data, threshold=100.0)
        assert not result.is_anomaly

    def test_scan_multiple_tickers(self, liquid_market_data):
        results = scan_volume_anomalies(liquid_market_data)
        assert len(results) == 3
        # Sorted by z_score descending
        assert results[0].z_score >= results[-1].z_score


# =============================================================================
# AMIHUD ILLIQUIDITY TESTS
# =============================================================================

class TestAmihudIlliquidity:
    def test_liquid_stock(self, liquid_market_data):
        df = liquid_market_data["BBCA.JK"]
        result = calc_amihud_illiquidity(df["Close"], df["Volume"], ticker="BBCA.JK")
        assert isinstance(result, IlliquidityResult)
        assert result.amihud_ratio >= 0
        assert result.classification in ["very_liquid", "liquid", "moderate", "illiquid", "very_illiquid"]

    def test_illiquid_stock_detected(self, illiquid_market_data):
        df = illiquid_market_data["GORENG.JK"]
        result = calc_amihud_illiquidity(df["Close"], df["Volume"], ticker="GORENG.JK")
        assert result.amihud_ratio > 0
        # Illiquid stock should have higher ratio than liquid
        df_liquid = illiquid_market_data["BBCA.JK"]
        liquid_result = calc_amihud_illiquidity(df_liquid["Close"], df_liquid["Volume"], ticker="BBCA.JK")
        assert result.amihud_ratio > liquid_result.amihud_ratio

    def test_insufficient_data(self):
        prices = pd.Series([100])
        volumes = pd.Series([1000])
        result = calc_amihud_illiquidity(prices, volumes, ticker="TEST")
        assert result.classification == "very_liquid"

    def test_scan_illiquidity(self, illiquid_market_data):
        results = scan_illiquidity(illiquid_market_data)
        assert len(results) == 2
        # Sorted by amihud descending (most illiquid first)
        assert results[0].amihud_ratio >= results[1].amihud_ratio

    def test_zero_volume_handling(self):
        prices = pd.Series([100, 101, 102, 103, 104])
        volumes = pd.Series([0, 0, 0, 0, 0])
        result = calc_amihud_illiquidity(prices, volumes, ticker="ZERO")
        assert result.classification == "very_liquid"


# =============================================================================
# BENEISH M-SCORE TESTS
# =============================================================================

class TestBeneishMScore:
    def test_healthy_company_not_flagged(self, healthy_financials):
        result = calc_beneish_m_score(healthy_financials, ticker="BBCA.JK")
        assert isinstance(result, BeneishResult)
        assert result.ticker == "BBCA.JK"
        assert result.threshold == -1.78
        # Healthy company should have low M-Score
        assert not result.is_manipulator
        assert "8 variables" not in result.description  # Just check it has a description

    def test_manipulator_company_flagged(self, manipulator_financials):
        result = calc_beneish_m_score(manipulator_financials, ticker="FRAUD.JK")
        assert result.is_manipulator
        assert result.m_score > -1.78
        assert "blacklist" in result.description.lower() or "manipul" in result.description.lower()

    def test_eight_variables_present(self, healthy_financials):
        result = calc_beneish_m_score(healthy_financials, ticker="BBCA.JK")
        expected_vars = ["DSRI", "GMI", "AQI", "SGI", "DEPI", "SGAI", "LVGI", "TATA"]
        for var in expected_vars:
            assert var in result.variables
            assert isinstance(result.variables[var], float)

    def test_empty_financials(self):
        result = calc_beneish_m_score({}, ticker="EMPTY")
        # With all zeros, M-Score should be calculable (uses safe_div)
        assert isinstance(result.m_score, float)
        assert not np.isnan(result.m_score)

    def test_manipulator_has_higher_score(self, healthy_financials, manipulator_financials):
        healthy = calc_beneish_m_score(healthy_financials, ticker="HEALTHY")
        manipulator = calc_beneish_m_score(manipulator_financials, ticker="MANIP")
        assert manipulator.m_score > healthy.m_score


# =============================================================================
# COMBINED ANTI-MANIPULATION SCAN TESTS
# =============================================================================

class TestAntiManipulationScan:
    def test_full_scan_with_market_data(self, liquid_market_data):
        report = run_anti_manipulation_scan(liquid_market_data)
        assert isinstance(report, AntiManipulationReport)
        assert len(report.volume_shocks) == 3
        assert len(report.illiquidity) == 3
        assert len(report.beneish) == 0  # No financials provided
        assert isinstance(report.passed, bool)

    def test_full_scan_with_financials(self, liquid_market_data, manipulator_financials):
        financials = {"BBCA.JK": manipulator_financials}
        report = run_anti_manipulation_scan(liquid_market_data, financials=financials)
        assert len(report.beneish) == 1
        assert "BBCA.JK" in report.blacklisted_tickers
        assert not report.passed

    def test_summary_string(self, liquid_market_data):
        report = run_anti_manipulation_scan(liquid_market_data)
        summary = report.summary()
        assert "Anti-Manipulation Report" in summary
        assert "Volume anomalies" in summary

    def test_empty_market_data(self):
        report = run_anti_manipulation_scan({})
        assert len(report.volume_shocks) == 0
        assert len(report.illiquidity) == 0
        assert report.passed


# =============================================================================
# FRAUD DETECTOR INTEGRATION TESTS (Layer 5)
# =============================================================================

class TestFraudDetectorLayer5:
    def test_check_volume_anomalies(self, liquid_market_data):
        detector = FraudDetector()
        results = detector.check_volume_anomalies(liquid_market_data)
        assert len(results) == 3
        for r in results:
            assert "ticker" in r
            assert "z_score" in r
            assert "is_anomaly" in r

    def test_check_illiquidity(self, illiquid_market_data):
        detector = FraudDetector()
        results = detector.check_illiquidity(illiquid_market_data)
        assert len(results) == 2
        # GORENG.JK should be flagged as illiquid
        goreng = [r for r in results if r["ticker"] == "GORENG.JK"][0]
        assert goreng["is_illiquid"]

    def test_check_beneish_healthy(self, healthy_financials):
        detector = FraudDetector()
        results = detector.check_beneish({"BBCA.JK": healthy_financials})
        assert len(results) == 1
        assert not results[0]["is_manipulator"]

    def test_check_beneish_manipulator(self, manipulator_financials):
        detector = FraudDetector()
        results = detector.check_beneish({"FRAUD.JK": manipulator_financials})
        assert len(results) == 1
        assert results[0]["is_manipulator"]
        # Should have generated a critical alert
        critical_alerts = [a for a in detector.alerts if a.severity == "critical"]
        assert len(critical_alerts) >= 1
        assert any("FRAUD.JK" in a.ticker for a in critical_alerts)

    def test_check_beneish_empty(self):
        detector = FraudDetector()
        results = detector.check_beneish({})
        assert len(results) == 0


# =============================================================================
# BAB 3: WASH TRADING DETECTION TESTS
# =============================================================================

@pytest.fixture
def wash_trading_data():
    """OHLCV data with wash trading pattern: high volume, tiny price range."""
    np.random.seed(42)
    n = 60
    # Very small price range (wash trading = price barely moves)
    base = 5000
    high = base + np.random.uniform(0, 5, n)     # tiny range
    low = base + np.random.uniform(-5, 0, n)
    close = base + np.random.uniform(-2, 2, n)
    open_ = base + np.random.uniform(-2, 2, n)
    # High volume but consistent (bot-driven)
    volume = np.random.randint(9_000_000, 11_000_000, n)
    return pd.DataFrame({"Open": open_, "High": high, "Low": low, "Close": close, "Volume": volume})


@pytest.fixture
def normal_ohlcv_data():
    """Normal OHLCV data — no manipulation."""
    np.random.seed(42)
    n = 60
    base = 8000 + np.cumsum(np.random.randn(n) * 20)
    high = base + np.random.uniform(10, 80, n)
    low = base - np.random.uniform(10, 80, n)
    close = base + np.random.uniform(-30, 30, n)
    open_ = base + np.random.uniform(-30, 30, n)
    volume = np.random.randint(500_000, 5_000_000, n)
    return pd.DataFrame({"Open": open_, "High": high, "Low": low, "Close": close, "Volume": volume})


class TestWashTrading:
    def test_normal_data_not_flagged(self, normal_ohlcv_data):
        result = detect_wash_trading(normal_ohlcv_data, ticker="BBCA.JK")
        assert isinstance(result, WashTradingResult)
        assert result.ticker == "BBCA.JK"
        assert not result.is_suspected
        assert result.confidence < 50

    def test_wash_trading_detected(self, wash_trading_data):
        result = detect_wash_trading(wash_trading_data, ticker="GORENG.JK")
        # High volume + tiny range should trigger at least some indicators
        assert result.confidence > 0
        assert "vr_ratio_normalized" in result.indicators
        assert "volume_cv" in result.indicators

    def test_insufficient_data(self):
        df = pd.DataFrame({"High": [100], "Low": [99], "Close": [100], "Volume": [1000]})
        result = detect_wash_trading(df, ticker="TEST")
        assert not result.is_suspected
        assert "insufficient" in result.description.lower()

    def test_missing_columns(self):
        df = pd.DataFrame({"Close": [100, 101, 102], "Volume": [1000, 2000, 3000]})
        result = detect_wash_trading(df, ticker="TEST")
        assert not result.is_suspected
        assert "insufficient" in result.description.lower()


# =============================================================================
# BAB 3: SPOOFING DETECTION TESTS
# =============================================================================

@pytest.fixture
def spoofing_data():
    """OHLCV data with spoofing pattern: volume spikes + reversals + upper shadows in recent window."""
    np.random.seed(42)
    n = 60
    # First 40 days: normal pattern
    base = 8000 + np.cumsum(np.random.randn(n) * 10)
    open_ = base.copy()
    close = base + np.random.uniform(-20, 20, n)
    # Normal upper shadows for first 40 days (small)
    high = np.maximum(open_, close) + np.random.uniform(5, 20, n)
    low = np.minimum(open_, close) - np.random.uniform(10, 30, n)
    volume = np.random.randint(500_000, 2_000_000, n).astype(float)

    # Last 20 days: spoofing pattern — extreme upper shadows + volume spikes + reversals
    for i in range(40, 60):
        high[i] = max(open_[i], close[i]) + np.random.uniform(80, 150)  # huge upper shadow
        volume[i] = 15_000_000 + np.random.randint(0, 5_000_000)  # 10x+ normal volume
        # Alternate direction for reversals
        if i % 2 == 0:
            close[i] = open_[i] - 30  # down day
        else:
            close[i] = open_[i] + 30  # up day (reversal from previous down)

    return pd.DataFrame({"Open": open_, "High": high, "Low": low, "Close": close, "Volume": volume})


class TestSpoofing:
    def test_normal_data_not_flagged(self, normal_ohlcv_data):
        result = detect_spoofing(normal_ohlcv_data, ticker="BBCA.JK")
        assert isinstance(result, SpoofingResult)
        assert not result.is_suspected
        assert result.confidence < 50

    def test_spoofing_pattern_detected(self, spoofing_data):
        result = detect_spoofing(spoofing_data, ticker="SUSPECT.JK")
        assert result.confidence > 0
        assert "close_to_high_ratio" in result.indicators
        assert "spike_reversal_count" in result.indicators
        assert "upper_shadow_ratio" in result.indicators

    def test_insufficient_data(self):
        df = pd.DataFrame({"Open": [100], "High": [101], "Low": [99], "Close": [100], "Volume": [1000]})
        result = detect_spoofing(df, ticker="TEST")
        assert not result.is_suspected
        assert "insufficient" in result.description.lower()

    def test_missing_columns(self):
        df = pd.DataFrame({"Close": [100, 101, 102], "Volume": [1000, 2000, 3000]})
        result = detect_spoofing(df, ticker="TEST")
        assert not result.is_suspected
        assert "insufficient" in result.description.lower()


# =============================================================================
# BAB 3: FAKE NEWS HYPE DETECTION TESTS
# =============================================================================

class TestFakeNewsHype:
    def test_normal_news_not_flagged(self):
        result = detect_fake_news_hype(
            ticker="BBCA.JK",
            price_change_pct=0.5,
            volume_z_score=0.5,
            news_sentiment=20,
            news_count=3,
        )
        assert isinstance(result, FakeNewsHypeResult)
        assert not result.is_suspected
        assert result.confidence < 50

    def test_hype_pattern_detected(self):
        result = detect_fake_news_hype(
            ticker="GORENG.JK",
            price_change_pct=8.0,
            volume_z_score=3.5,
            news_sentiment=75,
            news_count=5,
        )
        assert result.is_suspected
        assert result.confidence >= 50
        assert "hype" in result.description.lower()

    def test_insider_selling_flag(self):
        result = detect_fake_news_hype(
            ticker="DUMP.JK",
            price_change_pct=6.0,
            volume_z_score=2.5,
            news_sentiment=55,
            news_count=4,
            insider_selling=0.8,
        )
        assert result.is_suspected
        assert result.indicators["insider_selling"] == 0.8

    def test_social_hype_flag(self):
        result = detect_fake_news_hype(
            ticker="VIRAL.JK",
            price_change_pct=5.0,
            volume_z_score=2.0,
            news_sentiment=40,
            news_count=3,
            social_hype_score=80,
        )
        assert result.is_suspected
        assert result.indicators["social_hype_score"] == 80

    def test_news_flood_flag(self):
        result = detect_fake_news_hype(
            ticker="FLOOD.JK",
            price_change_pct=4.0,
            volume_z_score=1.5,
            news_sentiment=70,
            news_count=15,
        )
        assert result.is_suspected
        assert result.indicators["news_count"] == 15

    def test_no_data_no_crash(self):
        result = detect_fake_news_hype(
            ticker="TEST",
            price_change_pct=0,
            volume_z_score=0,
            news_sentiment=0,
            news_count=0,
        )
        assert not result.is_suspected
        assert result.confidence == 0


# =============================================================================
# FRAUD DETECTOR LAYER 6 INTEGRATION TESTS
# =============================================================================

class TestFraudDetectorLayer6:
    def test_check_wash_trading(self, liquid_market_data):
        detector = FraudDetector()
        results = detector.check_wash_trading(liquid_market_data)
        assert len(results) == 3
        for r in results:
            assert "ticker" in r
            assert "is_suspected" in r
            assert "confidence" in r

    def test_check_spoofing(self, liquid_market_data):
        detector = FraudDetector()
        results = detector.check_spoofing(liquid_market_data)
        assert len(results) == 3
        for r in results:
            assert "ticker" in r
            assert "is_suspected" in r
            assert "confidence" in r

    def test_check_fake_news_hype_normal(self):
        detector = FraudDetector()
        result = detector.check_fake_news_hype(
            ticker="BBCA.JK",
            price_change_pct=0.5,
            volume_z_score=0.5,
            news_sentiment=20,
            news_count=3,
        )
        assert not result["is_suspected"]
        assert result["ticker"] == "BBCA.JK"

    def test_check_fake_news_hype_suspected(self):
        detector = FraudDetector()
        result = detector.check_fake_news_hype(
            ticker="GORENG.JK",
            price_change_pct=10.0,
            volume_z_score=4.0,
            news_sentiment=80,
            news_count=8,
        )
        assert result["is_suspected"]
        # Should have generated an alert
        alerts = [a for a in detector.alerts if a.ticker == "GORENG.JK"]
        assert len(alerts) >= 1

    def test_check_wash_trading_empty(self):
        detector = FraudDetector()
        results = detector.check_wash_trading({})
        assert len(results) == 0

    def test_check_spoofing_empty(self):
        detector = FraudDetector()
        results = detector.check_spoofing({})
        assert len(results) == 0
