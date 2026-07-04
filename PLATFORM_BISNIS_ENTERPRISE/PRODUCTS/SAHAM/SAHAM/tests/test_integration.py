"""
Tests untuk modul Data Engineer, AI Agent, Backend API, dan Compliance.
Run: python -m pytest tests/test_integration.py -v
"""

import pandas as pd
import numpy as np
import pytest
import os
import sys
import tempfile

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))


# ===========================================================================
# Fixtures
# ===========================================================================
@pytest.fixture
def sample_ohlc_data():
    np.random.seed(42)
    n = 250
    close = 7000 + np.cumsum(np.random.randn(n) * 20)
    high = close + np.abs(np.random.randn(n) * 15)
    low = close - np.abs(np.random.randn(n) * 15)
    volume = np.random.randint(1e6, 5e7, n).astype(float)
    dates = pd.date_range("2024-01-01", periods=n, freq="B")
    return pd.DataFrame({
        "Open": close - 0.5, "High": high, "Low": low,
        "Close": close, "Volume": volume,
    }, index=dates)


# ===========================================================================
# Test: data_pipeline.py
# ===========================================================================
class TestDataPipeline:
    def test_data_quality_report_pass(self, sample_ohlc_data):
        from src.data_pipeline import check_data_quality
        report = check_data_quality(sample_ohlc_data, source="test")
        assert report.n_rows == 250
        assert report.n_columns == 5
        assert isinstance(report.passed, bool)
        assert isinstance(report.checks, list)

    def test_data_quality_empty_df(self):
        from src.data_pipeline import check_data_quality
        report = check_data_quality(pd.DataFrame(), source="empty")
        assert report.passed is False
        assert "DataFrame kosong" in report.issues

    def test_data_quality_missing_columns(self, sample_ohlc_data):
        from src.data_pipeline import check_data_quality
        report = check_data_quality(
            sample_ohlc_data, source="test",
            required_columns=["Close", "NonExistent"],
        )
        assert any(c["name"] == "required_columns" for c in report.checks)

    def test_data_quality_summary(self, sample_ohlc_data):
        from src.data_pipeline import check_data_quality
        report = check_data_quality(sample_ohlc_data, source="test")
        summary = report.summary()
        assert "Data Quality Report" in summary

    def test_feature_store_cache(self, sample_ohlc_data):
        from src.data_pipeline import FeatureStore
        with tempfile.TemporaryDirectory() as tmpdir:
            store = FeatureStore(cache_dir=tmpdir)
            df = store.compute_and_cache("test_key", lambda: sample_ohlc_data)
            assert len(df) == 250
            cached = store.get_cached("test_key")
            assert cached is not None
            assert len(cached) == 250

    def test_feature_store_list(self, sample_ohlc_data):
        from src.data_pipeline import FeatureStore
        with tempfile.TemporaryDirectory() as tmpdir:
            store = FeatureStore(cache_dir=tmpdir)
            store.compute_and_cache("key1", lambda: sample_ohlc_data)
            store.compute_and_cache("key2", lambda: sample_ohlc_data)
            keys = store.list_cached()
            assert "key1" in keys
            assert "key2" in keys

    def test_feature_store_force_recompute(self, sample_ohlc_data):
        from src.data_pipeline import FeatureStore
        with tempfile.TemporaryDirectory() as tmpdir:
            store = FeatureStore(cache_dir=tmpdir)
            df1 = store.compute_and_cache("test", lambda: sample_ohlc_data)
            df2 = store.compute_and_cache("test", lambda: sample_ohlc_data, force_recompute=True)
            assert len(df1) == len(df2) == 250

    def test_data_lineage(self):
        from src.data_pipeline import DataLineage
        lineage = DataLineage(
            source="yahoo", fetched_at="2024-01-01",
            rows=250, columns=["Open", "High", "Low", "Close", "Volume"],
        )
        d = lineage.to_dict()
        assert d["source"] == "yahoo"
        assert d["rows"] == 250


# ===========================================================================
# Test: ai_agent.py
# ===========================================================================
class TestAIAgent:
    def test_finbert_sentiment_lexicon(self):
        from src.ai_agent import FinBERTSentiment
        finbert = FinBERTSentiment()
        result = finbert.analyze("IHSG naik bullish, profit meningkat")
        assert result.sentiment in ["positive", "negative", "neutral"]
        assert 0 <= result.confidence <= 1

    def test_finbert_sentiment_negative(self):
        from src.ai_agent import FinBERTSentiment
        finbert = FinBERTSentiment()
        result = finbert.analyze("Pasar turun, rugi besar, bearish, panic sell")
        assert result.sentiment == "negative"

    def test_finbert_sentiment_neutral(self):
        from src.ai_agent import FinBERTSentiment
        finbert = FinBERTSentiment()
        result = finbert.analyze("Harga stabil di kisaran 7000")
        assert result.sentiment in ["positive", "negative", "neutral"]

    def test_finbert_batch(self):
        from src.ai_agent import FinBERTSentiment
        finbert = FinBERTSentiment()
        results = finbert.analyze_batch(["IHSG naik", "Pasar turun", "Stabil"])
        assert len(results) == 3

    def test_news_scraper_sentiment_summary(self):
        from src.ai_agent import NewsScraper, NewsArticle, SentimentResult
        scraper = NewsScraper()
        scraper.articles = [
            NewsArticle("IHSG naik", "bullish", "url1", "test", "2024-01-01",
                       SentimentResult("test", "positive", 0.8)),
            NewsArticle("IHSG turun", "bearish", "url2", "test", "2024-01-01",
                       SentimentResult("test", "negative", 0.7)),
        ]
        summary = scraper.get_sentiment_summary()
        assert summary["positive"] == 50.0
        assert summary["negative"] == 50.0
        assert summary["total_articles"] == 2

    def test_market_analyst_agent(self, sample_ohlc_data):
        from src.ai_agent import MarketAnalystAgent
        agent = MarketAnalystAgent()
        report = agent.analyze({"IHSG": sample_ohlc_data}, sample_ohlc_data)
        assert report.agent_name == "Market Analyst"
        assert len(report.recommendations) > 0
        assert 0 <= report.confidence <= 1

    def test_risk_manager_agent(self, sample_ohlc_data):
        from src.ai_agent import RiskManagerAgent
        agent = RiskManagerAgent()
        df = sample_ohlc_data.copy()
        df["IHSG_Close"] = df["Close"]
        df["Target_ATR"] = df["Close"].rolling(14).std()
        df["VIX_Close"] = 20
        report = agent.analyze(df, signal="BUY")
        assert report.agent_name == "Risk Manager"
        assert len(report.recommendations) > 0

    def test_portfolio_advisor_agent(self):
        from src.ai_agent import PortfolioAdvisorAgent, AgentReport
        advisor = PortfolioAdvisorAgent()
        reports = [
            AgentReport("A1", "R1", "findings1", ["rec1"], 0.7),
            AgentReport("A2", "R2", "findings2", ["rec2"], 0.8),
        ]
        final = advisor.analyze(reports, signal="BUY", confidence=0.7)
        assert "BUY" in final.findings
        assert len(final.recommendations) >= 2


# ===========================================================================
# Test: compliance.py
# ===========================================================================
class TestCompliance:
    def test_ojk_disclaimer_exists(self):
        from src.compliance import OJK_DISCLAIMER
        assert "OJK" in OJK_DISCLAIMER
        assert "risiko" in OJK_DISCLAIMER.lower()

    def test_risk_disclosure_exists(self):
        from src.compliance import RISK_DISCLOSURE
        assert "DISKLAIMER" in RISK_DISCLOSURE
        assert "100%" in RISK_DISCLOSURE

    def test_get_compliance_disclaimer(self):
        from src.compliance import get_compliance_disclaimer
        text = get_compliance_disclaimer()
        assert "OJK" in text
        assert "DISKLAIMER" in text
        assert "SUMBER DATA" in text

    def test_audit_trail_log_and_retrieve(self):
        from src.compliance import AuditTrail
        with tempfile.NamedTemporaryFile(suffix=".db", delete=False) as f:
            db_path = f.name
        try:
            audit = AuditTrail(db_path=db_path)
            entry = audit.log(action="TEST", actor="test_user", details="Test entry")
            assert entry.action == "TEST"
            entries = audit.get_entries(action="TEST")
            assert len(entries) == 1
            assert entries[0]["action"] == "TEST"
        finally:
            os.unlink(db_path)

    def test_audit_trail_risk_events(self):
        from src.compliance import AuditTrail
        with tempfile.NamedTemporaryFile(suffix=".db", delete=False) as f:
            db_path = f.name
        try:
            audit = AuditTrail(db_path=db_path)
            audit.log(action="TEST_LOW", details="low risk", risk_level="low")
            audit.log(action="TEST_HIGH", details="high risk", risk_level="high")
            high_events = audit.get_risk_events()
            assert len(high_events) == 1
            assert high_events[0]["risk_level"] == "high"
        finally:
            os.unlink(db_path)

    def test_log_prediction_audit(self):
        from src.compliance import AuditTrail, log_prediction_audit
        with tempfile.NamedTemporaryFile(suffix=".db", delete=False) as f:
            db_path = f.name
        try:
            audit = AuditTrail(db_path=db_path)
            log_prediction_audit(
                ticker="^JKSE", signal="BUY", confidence=0.65,
                model_votes={"RF": 1, "XGB": 1}, rules="trend bullish",
                audit=audit,
            )
            entries = audit.get_entries(action="PREDICT")
            assert len(entries) == 1
        finally:
            os.unlink(db_path)


# ===========================================================================
# Test: api.py (FastAPI)
# ===========================================================================
class TestAPI:
    def test_health_endpoint(self):
        from src.api_main import app
        from fastapi.testclient import TestClient
        client = TestClient(app)
        response = client.get("/api/v1/health")
        assert response.status_code == 200
        data = response.json()
        assert data["status"] == "healthy"
        assert data["version"] == "1.0.0"

    def test_openapi_docs(self):
        from src.api_main import app
        from fastapi.testclient import TestClient
        client = TestClient(app)
        response = client.get("/docs")
        assert response.status_code == 200

    def test_openapi_schema(self):
        from src.api_main import app
        from fastapi.testclient import TestClient
        client = TestClient(app)
        response = client.get("/openapi.json")
        assert response.status_code == 200
        schema = response.json()
        assert schema["info"]["title"] == "Saham Prediction API"
        assert "/api/v1/health" in schema["paths"]
        assert "/api/v1/predict/{ticker}" in schema["paths"]
        assert "/api/v1/briefing" in schema["paths"]
        assert "/api/v1/score/{ticker}" in schema["paths"]
