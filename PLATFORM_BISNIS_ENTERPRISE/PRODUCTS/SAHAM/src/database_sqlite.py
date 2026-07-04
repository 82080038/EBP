"""
SQLite implementation of DatabaseInterface.
Wraps existing database.py functions to conform to the interface.
"""
import sqlite3
from datetime import datetime
from typing import Optional, Dict, List, Any
import pandas as pd

from src.config import DB_PATH
from src.database_base import DatabaseInterface


class SQLiteDatabase(DatabaseInterface):
    """SQLite implementation of database operations."""

    def get_connection(self):
        return sqlite3.connect(DB_PATH)

    def init_db(self):
        """Initialize database schema."""
        # Import from existing database.py to avoid duplication
        from src.database import init_db as _init_db
        _init_db()

    def simpan_prediksi(self, ticker: str, tanggal_prediksi: str, tanggal_target: str,
                       harga_saat_ini: float, harga_prediksi: float, arah_prediksi: str,
                       sinyal: str, confidence: float, model_votes: str):
        """Save prediction to database."""
        from src.database import simpan_prediksi as _simpan
        _simpan(ticker, tanggal_prediksi, tanggal_target, harga_saat_ini,
                harga_prediksi, arah_prediksi, sinyal, confidence, model_votes)

    def get_all_prediksi(self) -> pd.DataFrame:
        """Get all predictions from database."""
        from src.database import get_all_prediksi as _get_all
        return _get_all()

    def simpan_harga_harian(self, ticker: str, tanggal: str, open_price: float,
                           high: float, low: float, close: float, volume: float):
        """Save daily OHLCV data."""
        from src.database import simpan_harga_harian as _simpan
        _simpan(ticker, tanggal, open_price, high, low, close, volume)

    def load_harga_harian(self, ticker: str, period: str = "1y") -> pd.DataFrame:
        """Load daily OHLCV data for a ticker."""
        from src.database import load_harga_harian as _load
        return _load(ticker, period)

    def log_aktivitas(self, aktivitas: str, detail: str = ""):
        """Log activity to database."""
        from src.database import log_aktivitas as _log
        _log(aktivitas, detail)

    def get_akurasi_metrics(self) -> Dict[str, Any]:
        """Get accuracy metrics."""
        from src.database import get_akurasi_metrics as _get_metrics
        return _get_metrics()

    def simpan_alert(self, ticker: str, alert_type: str, condition_value: Optional[float],
                    condition_text: str, message: str) -> int:
        """Save alert to database."""
        from src.database import simpan_alert as _simpan
        return _simpan(ticker, alert_type, condition_value, condition_text, message)

    def get_active_alerts(self, ticker: Optional[str] = None) -> pd.DataFrame:
        """Get active alerts."""
        from src.database import get_active_alerts as _get_alerts
        return _get_alerts(ticker)

    def deactivate_alert(self, alert_id: int):
        """Deactivate an alert."""
        from src.database import deactivate_alert as _deactivate
        _deactivate(alert_id)

    def trigger_alert(self, alert_id: int, message: str):
        """Mark an alert as triggered."""
        from src.database import trigger_alert as _trigger
        _trigger(alert_id, message)

    def delete_alert(self, alert_id: int):
        """Delete an alert."""
        from src.database import delete_alert as _delete
        _delete(alert_id)

    def get_notifikasi(self, limit: int = 100, hanya_belum_dibaca: bool = False) -> pd.DataFrame:
        """Get notifications."""
        from src.database import get_notifikasi as _get_notif
        return _get_notif(limit, hanya_belum_dibaca)

    def get_jumlah_notifikasi_belum_dibaca(self) -> int:
        """Get count of unread notifications."""
        from src.database import get_jumlah_notifikasi_belum_dibaca as _get_count
        return _get_count()

    def mark_notifikasi_dibaca(self, notifikasi_id: Optional[int] = None, semua: bool = False):
        """Mark notification(s) as read."""
        from src.database import mark_notifikasi_dibaca as _mark
        _mark(notifikasi_id, semua)
