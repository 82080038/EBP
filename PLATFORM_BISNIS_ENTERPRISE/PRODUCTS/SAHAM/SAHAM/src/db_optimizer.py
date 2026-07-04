"""
Database index optimization for SQLite.

Creates indexes on frequently queried columns to speed up:
- Prediction lookups by ticker + date
- Harga harian by ticker + date range
- Intraday by ticker + timestamp + interval
- Technical indicators by ticker + date
- Fundamental data by ticker + date
- Notifications by dibaca status
- Log aktivitas by timestamp

Also enables WAL mode and sets PRAGMA optimizations.
"""

import sqlite3
from typing import List, Tuple


INDEX_DEFINITIONS: List[Tuple[str, str]] = [
    # prediksi table
    ("idx_prediksi_ticker_date", "CREATE INDEX IF NOT EXISTS idx_prediksi_ticker_date ON prediksi(ticker, tanggal_prediksi)"),
    ("idx_prediksi_ticker_target", "CREATE INDEX IF NOT EXISTS idx_prediksi_ticker_target ON prediksi(ticker, tanggal_target)"),
    # harga_harian table
    ("idx_harga_harian_ticker_date", "CREATE INDEX IF NOT EXISTS idx_harga_harian_ticker_date ON harga_harian(ticker, tanggal)"),
    # harga_intraday table
    ("idx_harga_intraday_ticker_ts", "CREATE INDEX IF NOT EXISTS idx_harga_intraday_ticker_ts ON harga_intraday(ticker, timestamp, interval)"),
    # technical_indicators table
    ("idx_tech_ind_ticker_date", "CREATE INDEX IF NOT EXISTS idx_tech_ind_ticker_date ON technical_indicators(ticker, date)"),
    # fundamental_data table
    ("idx_fundamental_ticker_date", "CREATE INDEX IF NOT EXISTS idx_fundamental_ticker_date ON fundamental_data(ticker, tanggal)"),
    # financial_ratios table
    ("idx_fin_ratios_ticker_date", "CREATE INDEX IF NOT EXISTS idx_fin_ratios_ticker_date ON financial_ratios(ticker, tanggal)"),
    # notifikasi table
    ("idx_notifikasi_dibaca", "CREATE INDEX IF NOT EXISTS idx_notifikasi_dibaca ON notifikasi(dibaca, timestamp)"),
    # log_aktivitas table
    ("idx_log_aktivitas_ts", "CREATE INDEX IF NOT EXISTS idx_log_aktivitas_ts ON log_aktivitas(timestamp)"),
    # alerts table
    ("idx_alerts_active", "CREATE INDEX IF NOT EXISTS idx_alerts_active ON alerts(is_active, ticker)"),
]

PRAGMA_SETTINGS = [
    "PRAGMA journal_mode=WAL",
    "PRAGMA synchronous=NORMAL",
    "PRAGMA cache_size=-64000",  # 64MB cache
    "PRAGMA temp_store=MEMORY",
    "PRAGMA mmap_size=268435456",  # 256MB memory-mapped I/O
]


def create_indexes(conn: sqlite3.Connection) -> dict:
    """Create all indexes and apply PRAGMA optimizations.

    Args:
        conn: SQLite connection

    Returns:
        Dict with 'indexes_created', 'pragmas_set', 'errors'
    """
    cursor = conn.cursor()
    results = {"indexes_created": [], "pragmas_set": [], "errors": []}

    # Apply PRAGMA settings
    for pragma in PRAGMA_SETTINGS:
        try:
            cursor.execute(pragma)
            results["pragmas_set"].append(pragma)
        except Exception as e:
            results["errors"].append(f"PRAGMA {pragma}: {e}")

    # Create indexes
    for name, sql in INDEX_DEFINITIONS:
        try:
            cursor.execute(sql)
            results["indexes_created"].append(name)
        except Exception as e:
            results["errors"].append(f"Index {name}: {e}")

    conn.commit()
    return results


def get_index_info(conn: sqlite3.Connection) -> list:
    """Return list of existing indexes for all tables."""
    cursor = conn.cursor()
    cursor.execute("""
        SELECT name, tbl_name, sql
        FROM sqlite_master
        WHERE type='index' AND sql IS NOT NULL
        ORDER BY tbl_name, name
    """)
    return [
        {"name": row[0], "table": row[1], "sql": row[2]}
        for row in cursor.fetchall()
    ]


def get_query_plan(conn: sqlite3.Connection, query: str, params: tuple = ()) -> list:
    """Run EXPLAIN QUERY PLAN for a given query."""
    cursor = conn.cursor()
    cursor.execute(f"EXPLAIN QUERY PLAN {query}", params)
    return [row for row in cursor.fetchall()]
