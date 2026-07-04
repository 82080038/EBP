"""
Alerts CRUD endpoints: create, list, deactivate, trigger, delete.
"""
import logging
from fastapi import APIRouter, HTTPException, Query
from datetime import datetime

from src.api.models import AlertCreateRequest, AlertResponse
from src.config import TICKERS

logger = logging.getLogger("saham.api.alerts")
router = APIRouter(prefix="/api/v1", tags=["Alerts"])


@router.post("/alerts", response_model=AlertResponse)
async def create_alert(req: AlertCreateRequest):
    """Create a new price/volume alert for a ticker."""
    from src.database import simpan_alert
    target = TICKERS.get(req.ticker, req.ticker)
    alert_id = simpan_alert(
        ticker=target,
        alert_type=req.alert_type,
        condition_value=req.condition_value,
        condition_text=req.condition_text,
        message=req.message,
    )
    logger.info(f"[ALERT] Created id={alert_id} ticker={target} type={req.alert_type}")
    return AlertResponse(
        id=alert_id,
        ticker=target,
        alert_type=req.alert_type,
        condition_value=req.condition_value,
        condition_text=req.condition_text,
        is_active=1,
        is_triggered=0,
        triggered_at=None,
        message=req.message,
        created_at=datetime.now().isoformat(),
    )


@router.get("/alerts")
async def list_alerts(ticker: str = None, active_only: bool = True):
    """List alerts, optionally filtered by ticker and active status."""
    from src.database import get_active_alerts
    from src.api.utils import _sanitize
    target = TICKERS.get(ticker, ticker) if ticker else None
    df = get_active_alerts(target)
    if df.empty:
        return []
    return _sanitize(df.to_dict(orient="records"))


@router.put("/alerts/{alert_id}/deactivate")
async def deactivate_alert(alert_id: int):
    """Deactivate an alert (soft delete — keeps record but stops monitoring)."""
    from src.database import deactivate_alert as _deactivate
    _deactivate(alert_id)
    logger.info(f"[ALERT] Deactivated id={alert_id}")
    return {"id": alert_id, "is_active": 0, "status": "deactivated"}


@router.put("/alerts/{alert_id}/trigger")
async def trigger_alert(alert_id: int, message: str = ""):
    """Mark an alert as triggered."""
    from src.database import trigger_alert as _trigger
    _trigger(alert_id, message)
    logger.info(f"[ALERT] Triggered id={alert_id}")
    return {"id": alert_id, "is_triggered": 1, "status": "triggered"}


@router.delete("/alerts/{alert_id}")
async def delete_alert(alert_id: int):
    """Permanently delete an alert."""
    from src.database import delete_alert as _delete
    _delete(alert_id)
    logger.info(f"[ALERT] Deleted id={alert_id}")
    return {"id": alert_id, "status": "deleted"}
