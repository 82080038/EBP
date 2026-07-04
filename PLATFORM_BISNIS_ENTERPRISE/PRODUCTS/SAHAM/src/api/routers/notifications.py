"""
Notifications API endpoints: list, unread count, mark read.
"""
import logging
from fastapi import APIRouter, Query

logger = logging.getLogger("saham.api.notifications")
router = APIRouter(prefix="/api/v1", tags=["Notifications"])


@router.get("/notifications")
async def list_notifications(limit: int = 100, unread_only: bool = False):
    """List in-app notifications, optionally filtered to unread only."""
    from src.database import get_notifikasi
    from src.api.utils import _sanitize
    df = get_notifikasi(limit=limit, hanya_belum_dibaca=unread_only)
    if df.empty:
        return []
    return _sanitize(df.to_dict(orient="records"))


@router.get("/notifications/unread-count")
async def get_unread_notification_count():
    """Get count of unread notifications."""
    from src.database import get_jumlah_notifikasi_belum_dibaca
    count = get_jumlah_notifikasi_belum_dibaca()
    return {"unread": count}


@router.put("/notifications/{notification_id}/read")
async def mark_notification_read(notification_id: int):
    """Mark a single notification as read."""
    from src.database import mark_notifikasi_dibaca
    mark_notifikasi_dibaca(notifikasi_id=notification_id)
    return {"id": notification_id, "status": "read"}


@router.put("/notifications/read-all")
async def mark_all_notifications_read():
    """Mark all notifications as read."""
    from src.database import mark_notifikasi_dibaca
    mark_notifikasi_dibaca(semua=True)
    return {"status": "all_read"}
