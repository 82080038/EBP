"""
Abstract base class for database operations.
Provides a common interface for SQLite and PostgreSQL implementations.
"""
from abc import ABC, abstractmethod
from typing import Optional, Dict, List, Any
import pandas as pd


class DatabaseInterface(ABC):
    """Abstract base class for database operations."""

    @abstractmethod
    def get_connection(self):
        """Get a database connection."""
        pass

    @abstractmethod
    def init_db(self):
        """Initialize database schema."""
        pass

    @abstractmethod
    def simpan_prediksi(self, ticker: str, tanggal_prediksi: str, tanggal_target: str,
                       harga_saat_ini: float, harga_prediksi: float, arah_prediksi: str,
                       sinyal: str, confidence: float, model_votes: str):
        """Save prediction to database."""
        pass

    @abstractmethod
    def get_all_prediksi(self) -> pd.DataFrame:
        """Get all predictions from database."""
        pass

    @abstractmethod
    def simpan_harga_harian(self, ticker: str, tanggal: str, open_price: float,
                           high: float, low: float, close: float, volume: float):
        """Save daily OHLCV data."""
        pass

    @abstractmethod
    def load_harga_harian(self, ticker: str, period: str = "1y") -> pd.DataFrame:
        """Load daily OHLCV data for a ticker."""
        pass

    @abstractmethod
    def log_aktivitas(self, aktivitas: str, detail: str = ""):
        """Log activity to database."""
        pass

    @abstractmethod
    def get_akurasi_metrics(self) -> Dict[str, Any]:
        """Get accuracy metrics."""
        pass

    @abstractmethod
    def simpan_alert(self, ticker: str, alert_type: str, condition_value: Optional[float],
                    condition_text: str, message: str) -> int:
        """Save alert to database."""
        pass

    @abstractmethod
    def get_active_alerts(self, ticker: Optional[str] = None) -> pd.DataFrame:
        """Get active alerts."""
        pass

    @abstractmethod
    def deactivate_alert(self, alert_id: int):
        """Deactivate an alert."""
        pass

    @abstractmethod
    def trigger_alert(self, alert_id: int, message: str):
        """Mark an alert as triggered."""
        pass

    @abstractmethod
    def delete_alert(self, alert_id: int):
        """Delete an alert."""
        pass

    @abstractmethod
    def get_notifikasi(self, limit: int = 100, hanya_belum_dibaca: bool = False) -> pd.DataFrame:
        """Get notifications."""
        pass

    @abstractmethod
    def get_jumlah_notifikasi_belum_dibaca(self) -> int:
        """Get count of unread notifications."""
        pass

    @abstractmethod
    def mark_notifikasi_dibaca(self, notifikasi_id: Optional[int] = None, semua: bool = False):
        """Mark notification(s) as read."""
        pass


def get_database() -> DatabaseInterface:
    """Factory function to get the appropriate database implementation."""
    import os
    from src.database_pg import PG_ENABLED

    database_url = os.getenv("DATABASE_URL", "")
    
    if database_url and PG_ENABLED:
        from src.database_pg import PostgreSQLDatabase
        return PostgreSQLDatabase()
    else:
        from src.database_sqlite import SQLiteDatabase
        return SQLiteDatabase()
