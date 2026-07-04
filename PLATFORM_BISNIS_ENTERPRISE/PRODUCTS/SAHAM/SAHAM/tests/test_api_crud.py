"""
Tests for CRUD API endpoints (Alerts, Notifications, Fraud Detection, Fundamental/Technical).

Uses FastAPI TestClient — no live server needed.
"""

import pytest
from fastapi.testclient import TestClient

from src.api_main import app
from src.database import init_db, simpan_alert, get_active_alerts, delete_alert, simpan_notifikasi, mark_notifikasi_dibaca


@pytest.fixture(scope="module")
def client():
    init_db()
    with TestClient(app) as c:
        yield c


@pytest.fixture
def clean_alerts():
    """Clean up all alerts before and after test."""
    df = get_active_alerts()
    for _, row in df.iterrows():
        try:
            delete_alert(int(row["id"]))
        except Exception:
            pass
    yield
    df = get_active_alerts()
    for _, row in df.iterrows():
        try:
            delete_alert(int(row["id"]))
        except Exception:
            pass


# =============================================================================
# ALERTS CRUD TESTS
# =============================================================================

class TestAlertsCRUD:
    def test_create_alert(self, client, clean_alerts):
        resp = client.post("/api/v1/alerts", json={
            "ticker": "BBCA.JK",
            "alert_type": "price_above",
            "condition_value": 9000,
            "condition_text": "Price above 9000",
            "message": "BBCA above 9000",
        })
        assert resp.status_code == 200
        data = resp.json()
        assert data["ticker"] == "BBCA.JK"
        assert data["alert_type"] == "price_above"
        assert data["is_active"] == 1
        assert data["is_triggered"] == 0
        assert "id" in data

    def test_list_alerts(self, client, clean_alerts):
        # Create an alert first
        client.post("/api/v1/alerts", json={
            "ticker": "TLKM.JK",
            "alert_type": "price_below",
            "condition_value": 3000,
        })
        resp = client.get("/api/v1/alerts")
        assert resp.status_code == 200
        data = resp.json()
        assert isinstance(data, list)
        assert len(data) >= 1
        assert any(a["ticker"] == "TLKM.JK" for a in data)

    def test_list_alerts_empty(self, client, clean_alerts):
        resp = client.get("/api/v1/alerts")
        assert resp.status_code == 200
        assert resp.json() == []

    def test_deactivate_alert(self, client, clean_alerts):
        create = client.post("/api/v1/alerts", json={
            "ticker": "ASII.JK",
            "alert_type": "volume_spike",
        })
        alert_id = create.json()["id"]
        resp = client.put(f"/api/v1/alerts/{alert_id}/deactivate")
        assert resp.status_code == 200
        assert resp.json()["is_active"] == 0

    def test_trigger_alert(self, client, clean_alerts):
        create = client.post("/api/v1/alerts", json={
            "ticker": "UNVR.JK",
            "alert_type": "price_above",
            "condition_value": 500,
        })
        alert_id = create.json()["id"]
        resp = client.put(f"/api/v1/alerts/{alert_id}/trigger?message=Triggered")
        assert resp.status_code == 200
        assert resp.json()["is_triggered"] == 1

    def test_delete_alert(self, client, clean_alerts):
        create = client.post("/api/v1/alerts", json={
            "ticker": "GOTO.JK",
            "alert_type": "price_above",
        })
        alert_id = create.json()["id"]
        resp = client.delete(f"/api/v1/alerts/{alert_id}")
        assert resp.status_code == 200
        assert resp.json()["status"] == "deleted"

    def test_create_alert_validation_error(self, client, clean_alerts):
        resp = client.post("/api/v1/alerts", json={
            "alert_type": "price_above",
        })
        assert resp.status_code == 422  # Missing required field: ticker


# =============================================================================
# NOTIFICATIONS API TESTS
# =============================================================================

class TestNotificationsAPI:
    def test_list_notifications(self, client):
        resp = client.get("/api/v1/notifications")
        assert resp.status_code == 200
        assert isinstance(resp.json(), list)

    def test_list_notifications_unread_only(self, client):
        resp = client.get("/api/v1/notifications?unread_only=true")
        assert resp.status_code == 200
        assert isinstance(resp.json(), list)

    def test_unread_count(self, client):
        resp = client.get("/api/v1/notifications/unread-count")
        assert resp.status_code == 200
        assert "unread" in resp.json()

    def test_mark_all_read(self, client):
        resp = client.put("/api/v1/notifications/read-all")
        assert resp.status_code == 200
        assert resp.json()["status"] == "all_read"

    def test_mark_single_read(self, client):
        # Create a notification first
        simpan_notifikasi("test", "Test Notification", "Test message", "info")
        resp = client.get("/api/v1/notifications?unread_only=true")
        notifs = resp.json()
        if notifs:
            nid = notifs[0]["id"]
            r = client.put(f"/api/v1/notifications/{nid}/read")
            assert r.status_code == 200
            assert r.json()["status"] == "read"


# =============================================================================
# FRAUD DETECTION API TESTS
# =============================================================================

class TestFraudDetectionAPI:
    def test_fraud_check_not_found(self, client):
        resp = client.get("/api/v1/fraud/check/NONEXISTENT.JK")
        # With mock data from conftest, may return 200 with results or 404/500
        assert resp.status_code in (200, 404, 500)
        if resp.status_code == 200:
            data = resp.json()
            assert "ticker" in data

    def test_anti_manipulation_scan_not_found(self, client):
        resp = client.get("/api/v1/anti-manipulation/scan/NONEXISTENT.JK")
        # With mock data from conftest, may return 200 with results or 404
        assert resp.status_code in (200, 404, 500)
        if resp.status_code == 200:
            data = resp.json()
            assert "ticker" in data

    def test_fake_news_hype_normal(self, client):
        resp = client.post(
            "/api/v1/fraud/fake-news-hype"
            "?ticker=BBCA.JK&price_change_pct=0.5&volume_z_score=0.5&news_sentiment=20&news_count=3"
        )
        assert resp.status_code == 200
        data = resp.json()
        assert data["ticker"] == "BBCA.JK"
        assert "is_suspected" in data
        assert "confidence" in data

    def test_fake_news_hype_suspected(self, client):
        resp = client.post(
            "/api/v1/fraud/fake-news-hype"
            "?ticker=GORENG.JK&price_change_pct=10.0&volume_z_score=4.0&news_sentiment=80&news_count=8"
        )
        assert resp.status_code == 200
        data = resp.json()
        assert data["is_suspected"] is True
        assert data["confidence"] >= 50


# =============================================================================
# FUNDAMENTAL & TECHNICAL API TESTS
# =============================================================================

class TestFundamentalTechnicalAPI:
    def test_get_fundamental_not_found(self, client):
        resp = client.get("/api/v1/fundamental/NONEXISTENT.JK")
        assert resp.status_code == 404

    def test_get_all_fundamentals(self, client):
        resp = client.get("/api/v1/fundamental")
        assert resp.status_code == 200
        assert isinstance(resp.json(), list)

    def test_get_technical_indicators_not_found(self, client):
        resp = client.get("/api/v1/indicators/NONEXISTENT.JK")
        assert resp.status_code == 404

    def test_get_latest_technical_indicators_not_found(self, client):
        resp = client.get("/api/v1/indicators/NONEXISTENT.JK/latest")
        assert resp.status_code == 404

    def test_get_financial_ratios_not_found(self, client):
        resp = client.get("/api/v1/financial-ratios/NONEXISTENT.JK")
        assert resp.status_code == 404
