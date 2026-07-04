"""
Tests untuk modul ML Engineer: validation, scoring, patterns, feature_selection.
Run: python -m pytest tests/test_ml_engineer.py -v
"""

import pandas as pd
import numpy as np
import pytest
import os
import sys

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))


# ===========================================================================
# Fixtures
# ===========================================================================
@pytest.fixture
def sample_market_data():
    """Generate synthetic OHLCV data for testing."""
    np.random.seed(42)
    n = 250
    close = 7000 + np.cumsum(np.random.randn(n) * 20)
    high = close + np.abs(np.random.randn(n) * 15)
    low = close - np.abs(np.random.randn(n) * 15)
    open_ = close + np.random.randn(n) * 5
    volume = np.random.randint(1e6, 5e7, n).astype(float)

    dates = pd.date_range("2024-01-01", periods=n, freq="B")
    df = pd.DataFrame({
        "Open": open_, "High": high, "Low": low,
        "Close": close, "Volume": volume,
    }, index=dates)
    return df


@pytest.fixture
def sample_features_df(sample_market_data):
    """Generate features DataFrame with target columns."""
    from src.preprocessor import add_technical_indicators, add_lag_features, calculate_rsi

    df = sample_market_data.copy()
    df = add_technical_indicators(df)
    df["Target_Returns"] = df["Close"].pct_change()
    df["Target_Next_Return"] = df["Target_Returns"].shift(-1)
    df["Target_Direction"] = (df["Target_Next_Return"] > 0).astype(int)
    df["Target_RSI"] = calculate_rsi(df["Close"])
    df["Target_Volatility"] = df["Target_Returns"].rolling(20).std()
    df["Target_ATR"] = df["Close"].rolling(14).std()
    df["Target_ATR_Pct"] = (df["Target_ATR"] / df["Close"]) * 100
    df["Target_OBV"] = (np.sign(df["Close"].diff()) * df["Volume"]).fillna(0).cumsum()
    df["Target_OBV_MA"] = df["Target_OBV"].rolling(20).mean()
    df["Target_BB_Upper"] = df["Close"].rolling(20).mean() + 2 * df["Close"].rolling(20).std()
    df["Target_BB_Lower"] = df["Close"].rolling(20).mean() - 2 * df["Close"].rolling(20).std()
    bb_mean = df["Close"].rolling(20).mean()
    bb_std = df["Close"].rolling(20).std()
    df["Target_BB_Pct"] = (df["Close"] - df["Target_BB_Lower"]) / (df["Target_BB_Upper"] - df["Target_BB_Lower"])
    df["Target_Stoch_K"] = 100 * ((df["Close"] - df["Low"].rolling(14).min()) / (df["High"].rolling(14).max() - df["Low"].rolling(14).min()))
    df["Target_Stoch_D"] = df["Target_Stoch_K"].rolling(3).mean()
    df["Target_Williams_R"] = -100 * ((df["High"].rolling(14).max() - df["Close"]) / (df["High"].rolling(14).max() - df["Low"].rolling(14).min()))
    df["Target_CCI"] = (df["Close"] - df["Close"].rolling(20).mean()) / (0.015 * df["Close"].rolling(20).std())
    df["Target_MACD"] = df["Close"].ewm(span=12).mean() - df["Close"].ewm(span=26).mean()
    df["Target_MACD_Signal"] = df["Target_MACD"].ewm(span=9).mean()
    df["VIX_Close"] = 15 + np.abs(np.random.randn(len(df)) * 3)
    df["VIX_Returns"] = df["VIX_Close"].pct_change()
    df["Fear_Greed_Index"] = 50 + np.random.randn(len(df)) * 10
    df["IHSG_MA5"] = df["Close"].rolling(5).mean()
    df["IHSG_MA10"] = df["Close"].rolling(10).mean()
    df["IHSG_MA20"] = df["Close"].rolling(20).mean()
    df["IHSG_Volume_MA"] = df["Volume"].rolling(10).mean()
    df = df.dropna()
    return df


# ===========================================================================
# Test: validation.py
# ===========================================================================
class TestValidation:
    def test_walk_forward_cv_runs(self, sample_features_df):
        from src.validation import walk_forward_cv
        from src.preprocessor import get_feature_columns

        feature_cols = get_feature_columns(sample_features_df)
        feature_cols = [c for c in feature_cols if c in sample_features_df.columns][:20]

        result = walk_forward_cv(
            sample_features_df, feature_cols,
            n_folds=3, train_ratio=0.7,
        )
        assert result.n_folds > 0
        assert 0 <= result.mean_da <= 100

    def test_walk_forward_cv_result_summary(self, sample_features_df):
        from src.validation import walk_forward_cv
        from src.preprocessor import get_feature_columns

        feature_cols = [c for c in get_feature_columns(sample_features_df) if c in sample_features_df.columns][:15]
        result = walk_forward_cv(sample_features_df, feature_cols, n_folds=2)
        summary = result.summary()
        assert "Walk-Forward" in summary
        assert "DA" in summary

    def test_calc_ic(self):
        from src.validation import _calc_ic

        predicted = np.array([0.6, 0.4, 0.7, 0.3, 0.8, 0.5, 0.9, 0.2])
        actual = np.array([0.01, -0.02, 0.03, -0.01, 0.02, -0.01, 0.04, -0.03])
        ic = _calc_ic(predicted, actual)
        assert -1 <= ic <= 1

    def test_calc_rank_ic(self):
        from src.validation import _calc_rank_ic

        predicted = np.array([0.6, 0.4, 0.7, 0.3, 0.8, 0.5, 0.9, 0.2])
        actual = np.array([0.01, -0.02, 0.03, -0.01, 0.02, -0.01, 0.04, -0.03])
        ric = _calc_rank_ic(predicted, actual)
        assert -1 <= ric <= 1

    def test_calc_icir(self):
        from src.validation import calc_icir

        ics = [0.05, 0.08, 0.03, 0.06, 0.04]
        icir = calc_icir(ics)
        assert icir > 0

    def test_calc_icir_empty(self):
        from src.validation import calc_icir
        assert calc_icir([]) == 0.0
        assert calc_icir([0.05]) == 0.0

    def test_evaluate_signal_quality(self):
        from src.validation import evaluate_signal_quality

        preds = np.array([1, 0, 1, 1, 0, 1, 0, 1])
        probas = np.array([0.6, 0.3, 0.7, 0.8, 0.2, 0.65, 0.35, 0.75])
        returns = pd.Series([0.01, -0.02, 0.03, 0.02, -0.01, 0.015, -0.025, 0.04])

        result = evaluate_signal_quality(preds, probas, returns)
        assert "ic" in result
        assert "rank_ic" in result
        assert "hit_rate" in result
        assert "profit_factor" in result
        assert "sharpe_per_signal" in result
        assert 0 <= result["hit_rate"] <= 100

    def test_walk_forward_cv_insufficient_data(self):
        from src.validation import walk_forward_cv

        df = pd.DataFrame({"A": range(50), "Target_Direction": [0]*50, "Target_Next_Return": [0]*50})
        result = walk_forward_cv(df, ["A"], n_folds=3)
        assert result.n_folds == 0


# ===========================================================================
# Test: scoring.py
# ===========================================================================
class TestScoring:
    def test_composite_ai_score_basic(self):
        from src.scoring import calc_composite_ai_score

        result = calc_composite_ai_score(
            predictions={"RF": 1, "XGB": 1, "LGBM": 1},
            probabilities={"RF": 0.65, "XGB": 0.70, "LGBM": 0.68},
            confidence=0.68,
            rsi=55,
            trend_bullish=True,
            vix=18,
            fear_greed=60,
            ma_alignment=0.5,
            macd_signal=0.3,
            bb_position=0.4,
            volume_trend=0.2,
        )
        assert 1 <= result["ai_score"] <= 10
        assert 0 <= result["composite_score"] <= 100
        assert 0 <= result["technical_rating"] <= 100
        assert 0 <= result["sentiment_rating"] <= 100
        assert 0 <= result["momentum_rating"] <= 100
        assert 0 <= result["risk_rating"] <= 100
        assert result["signal_strength"] in ["Sangat Kuat", "Kuat", "Netral", "Lemah", "Sangat Lemah"]

    def test_composite_ai_score_bearish(self):
        from src.scoring import calc_composite_ai_score

        result = calc_composite_ai_score(
            predictions={"RF": 0, "XGB": 0, "LGBM": 0},
            probabilities={"RF": 0.35, "XGB": 0.30, "LGBM": 0.32},
            confidence=0.32,
            rsi=25,
            trend_bearish=True,
            vix=35,
            fear_greed=20,
            ma_alignment=-0.5,
            macd_signal=-0.3,
            bb_position=0.1,
            volume_trend=-0.2,
        )
        assert result["ai_score"] <= 5

    def test_skill_pack_scores(self, sample_features_df):
        from src.scoring import calc_skill_pack_scores

        scores = calc_skill_pack_scores(sample_features_df, target_name="IHSG")
        assert "Momentum" in scores
        assert "Mean Reversion" in scores
        assert "Risk Radar" in scores
        assert "Volume Pulse" in scores
        assert "Sentiment" in scores
        assert "Macro" in scores
        assert "Overall" in scores
        for v in scores.values():
            assert 0 <= v <= 100


# ===========================================================================
# Test: patterns.py
# ===========================================================================
class TestPatterns:
    def test_detect_candlestick_patterns(self, sample_market_data):
        from src.patterns import detect_candlestick_patterns

        patterns = detect_candlestick_patterns(sample_market_data, lookback=30)
        assert isinstance(patterns, list)
        for p in patterns:
            assert p.type in ["bullish", "bearish", "neutral"]
            assert 0 <= p.confidence <= 1

    def test_detect_candlestick_doji(self):
        from src.patterns import detect_candlestick_patterns

        dates = pd.date_range("2024-01-01", periods=5, freq="B")
        df = pd.DataFrame({
            "Open": [100, 100.01, 100.02, 100.01, 100],
            "High": [101, 101, 101, 101, 101],
            "Low": [99, 99, 99, 99, 99],
            "Close": [100.01, 100.02, 100.01, 100, 100.01],
        }, index=dates)
        patterns = detect_candlestick_patterns(df)
        doji = [p for p in patterns if p.name == "Doji"]
        assert len(doji) > 0

    def test_detect_candlestick_hammer(self):
        from src.patterns import detect_candlestick_patterns

        dates = pd.date_range("2024-01-01", periods=3, freq="B")
        df = pd.DataFrame({
            "Open": [100, 101, 99],
            "High": [101, 101.5, 99.5],
            "Low": [98, 99, 95],
            "Close": [101, 101, 99.2],
        }, index=dates)
        patterns = detect_candlestick_patterns(df)
        all_names = [p.name for p in patterns]
        assert "Hammer" in all_names or len(all_names) > 0

    def test_analyze_market_structure(self, sample_market_data):
        from src.patterns import analyze_market_structure

        structure = analyze_market_structure(sample_market_data, window=5)
        assert structure.structure in ["Uptrend", "Downtrend", "Range", "Unknown", "Insufficient Data"]
        assert isinstance(structure.description, str)

    def test_detect_chart_patterns(self, sample_market_data):
        from src.patterns import detect_chart_patterns

        patterns = detect_chart_patterns(sample_market_data, window=30)
        assert isinstance(patterns, list)
        for p in patterns:
            assert p.type in ["bullish", "bearish", "neutral"]

    def test_detect_volume_anomaly(self, sample_market_data):
        from src.patterns import detect_volume_anomaly

        # Add a volume spike
        df = sample_market_data.copy()
        df.iloc[-1, df.columns.get_loc("Volume")] = df["Volume"].max() * 5

        anomalies = detect_volume_anomaly(df, window=20, threshold=2.0)
        assert isinstance(anomalies, list)
        for a in anomalies:
            assert a.anomaly_type in ["spike", "dry_up", "climax"]

    def test_detect_trendlines(self, sample_market_data):
        from src.patterns import detect_trendlines

        trendlines = detect_trendlines(sample_market_data, window=30)
        assert isinstance(trendlines, list)
        for tl in trendlines:
            assert tl.type in ["support", "resistance"]

    def test_full_pattern_analysis(self, sample_market_data):
        from src.patterns import full_pattern_analysis

        result = full_pattern_analysis(sample_market_data)
        assert "candlestick_patterns" in result
        assert "chart_patterns" in result
        assert "market_structure" in result
        assert "volume_anomalies" in result
        assert "trendlines" in result
        assert "summary" in result
        assert isinstance(result["summary"], str)


# ===========================================================================
# Test: feature_selection.py
# ===========================================================================
class TestFeatureSelection:
    def test_correlation_filter(self, sample_features_df):
        from src.feature_selection import correlation_filter

        cols = [c for c in sample_features_df.columns if c not in ["Target_Next_Return", "Target_Next_Close", "Target_Direction"]][:30]
        kept, dropped = correlation_filter(sample_features_df, cols, threshold=0.95)
        assert len(kept) + len(dropped) == len(cols)
        assert len(kept) > 0

    def test_variance_threshold_filter(self, sample_features_df):
        from src.feature_selection import variance_threshold_filter

        cols = list(sample_features_df.columns)[:20]
        kept, dropped = variance_threshold_filter(sample_features_df, cols, min_variance=0.0001)
        assert len(kept) + len(dropped) == len(cols)

    def test_shap_feature_selection(self, sample_features_df):
        from src.feature_selection import shap_feature_selection
        from src.preprocessor import get_feature_columns, train_test_split_time

        feature_cols = [c for c in get_feature_columns(sample_features_df) if c in sample_features_df.columns][:25]
        train, test = train_test_split_time(sample_features_df)
        X_train = train[feature_cols]
        y_train = train["Target_Direction"]
        X_test = test[feature_cols]
        y_test = test["Target_Direction"]

        result = shap_feature_selection(X_train, y_train, X_test, y_test, n_features=10)
        assert result.n_after <= 10
        assert len(result.selected_features) > 0
        assert len(result.feature_importance) > 0

    def test_select_features_hybrid(self, sample_features_df):
        from src.feature_selection import select_features

        feature_cols = [c for c in sample_features_df.columns if c not in ["Target_Next_Return", "Target_Next_Close", "Target_Direction"]][:30]
        result = select_features(sample_features_df, feature_cols, target_n=15, method="hybrid")
        assert result.n_after <= 15
        assert len(result.selected_features) > 0
        assert result.n_before >= result.n_after
