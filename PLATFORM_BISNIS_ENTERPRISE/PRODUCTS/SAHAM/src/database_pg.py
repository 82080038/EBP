"""
PostgreSQL + TimescaleDB adapter for Saham Prediction Application.

Provides the same function signatures as src/database.py but uses PostgreSQL
with TimescaleDB hypertables for time-series data.

Features:
- Connection pooling via psycopg2
- Hypertables for time-series tables (harga_harian, harga_intraday, technical_indicators, prediksi)
- Continuous aggregates for daily/weekly/monthly OHLCV
- Compression policy for data > 30 days
- Retention policy: auto-drop data > 5 years
- Backward compatible: falls back to SQLite if PostgreSQL unavailable
"""

import os
import logging
from datetime import datetime
from typing import Optional
import pandas as pd

logger = logging.getLogger("saham.database_pg")

try:
    import psycopg2
    from psycopg2.extras import RealDictCursor
    from psycopg2.pool import ThreadedConnectionPool

    _PSYCOPG_AVAILABLE = True
except ImportError:
    _PSYCOPG_AVAILABLE = False
    logger.info("psycopg2 not installed — PostgreSQL adapter disabled")

from .config import DB_PATH

DATABASE_URL = os.getenv("DATABASE_URL", "")
PG_POOL: Optional["ThreadedConnectionPool"] = None
PG_ENABLED = bool(DATABASE_URL) and _PSYCOPG_AVAILABLE


def _get_pg_conn():
    """Get a connection from the pool."""
    global PG_POOL
    if PG_POOL is None:
        PG_POOL = ThreadedConnectionPool(
            minconn=2,
            maxconn=10,
            dsn=DATABASE_URL,
        )
    return PG_POOL.getconn()


def _put_pg_conn(conn):
    """Return a connection to the pool."""
    global PG_POOL
    if PG_POOL is not None:
        PG_POOL.putconn(conn)


def is_pg_enabled() -> bool:
    """Check if PostgreSQL backend is enabled and available."""
    return PG_ENABLED


def init_db_pg():
    """Initialize PostgreSQL database with TimescaleDB hypertables."""
    if not PG_ENABLED:
        return

    conn = _get_pg_conn()
    cursor = conn.cursor()

    try:
        cursor.execute("CREATE EXTENSION IF NOT EXISTS timescaledb CASCADE;")

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS prediksi (
                id SERIAL PRIMARY KEY,
                ticker TEXT NOT NULL,
                tanggal_prediksi DATE NOT NULL,
                tanggal_target DATE NOT NULL,
                harga_saat_ini REAL,
                harga_prediksi REAL NOT NULL,
                harga_aktual REAL,
                arah_prediksi TEXT,
                arah_aktual TEXT,
                sinyal TEXT,
                confidence REAL,
                model_votes TEXT,
                updated_at TIMESTAMP
            )
        """)
        cursor.execute(
            "SELECT create_hypertable('prediksi', 'tanggal_prediksi', "
            "if_not_exists => TRUE, chunk_time_interval => INTERVAL '30 days');"
        )

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS log_aktivitas (
                id SERIAL PRIMARY KEY,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                aktivitas TEXT NOT NULL,
                detail TEXT
            )
        """)

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS harga_harian (
                id SERIAL PRIMARY KEY,
                ticker TEXT NOT NULL,
                tanggal DATE NOT NULL,
                open REAL,
                high REAL,
                low REAL,
                close REAL,
                volume REAL,
                UNIQUE(ticker, tanggal)
            )
        """)
        cursor.execute(
            "SELECT create_hypertable('harga_harian', 'tanggal', "
            "if_not_exists => TRUE, chunk_time_interval => INTERVAL '7 days');"
        )

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS notifikasi (
                id SERIAL PRIMARY KEY,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                kategori TEXT NOT NULL,
                judul TEXT NOT NULL,
                pesan TEXT NOT NULL,
                level TEXT DEFAULT 'info',
                dibaca INTEGER DEFAULT 0
            )
        """)

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS harga_intraday (
                id SERIAL PRIMARY KEY,
                ticker TEXT NOT NULL,
                timestamp TIMESTAMP NOT NULL,
                interval TEXT NOT NULL,
                open REAL,
                high REAL,
                low REAL,
                close REAL,
                volume REAL,
                UNIQUE(ticker, timestamp, interval)
            )
        """)
        cursor.execute(
            "SELECT create_hypertable('harga_intraday', 'timestamp', "
            "if_not_exists => TRUE, chunk_time_interval => INTERVAL '1 day');"
        )

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS fundamental_data (
                id SERIAL PRIMARY KEY,
                ticker TEXT NOT NULL,
                tanggal DATE NOT NULL,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                pe_ratio REAL, pbv_ratio REAL, roe REAL, eps REAL,
                debt_to_equity REAL, dividend_yield REAL, market_cap REAL,
                revenue REAL, net_income REAL, total_cash REAL, total_debt REAL,
                free_cash_flow REAL, beta REAL, profit_margin REAL, current_ratio REAL,
                ps_ratio REAL, peg_ratio REAL, roa REAL, roic REAL,
                gross_margin REAL, operating_margin REAL,
                quick_ratio REAL, interest_coverage REAL, payout_ratio REAL,
                UNIQUE(ticker, tanggal)
            )
        """)

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS technical_indicators (
                id SERIAL PRIMARY KEY,
                ticker TEXT NOT NULL,
                date DATE NOT NULL,
                sma_5 REAL, sma_10 REAL, sma_20 REAL, sma_50 REAL, sma_200 REAL,
                ema_12 REAL, ema_26 REAL, rsi_14 REAL,
                macd REAL, macd_signal REAL, macd_histogram REAL,
                bollinger_upper REAL, bollinger_middle REAL, bollinger_lower REAL,
                stoch_k REAL, stoch_d REAL, williams_r REAL,
                atr REAL, adx REAL, cci REAL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(ticker, date)
            )
        """)
        cursor.execute(
            "SELECT create_hypertable('technical_indicators', 'date', "
            "if_not_exists => TRUE, chunk_time_interval => INTERVAL '30 days');"
        )

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS financial_ratios (
                id SERIAL PRIMARY KEY,
                ticker TEXT NOT NULL,
                period_year INTEGER NOT NULL,
                period_quarter INTEGER,
                pe_ratio REAL, pb_ratio REAL, ps_ratio REAL, peg_ratio REAL,
                roe REAL, roa REAL, roic REAL,
                gross_margin REAL, operating_margin REAL, net_margin REAL,
                debt_to_equity REAL, current_ratio REAL, quick_ratio REAL,
                interest_coverage REAL, dividend_yield REAL, payout_ratio REAL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(ticker, period_year, period_quarter)
            )
        """)

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS alerts (
                id SERIAL PRIMARY KEY,
                ticker TEXT NOT NULL,
                alert_type TEXT NOT NULL,
                condition_value REAL,
                condition_text TEXT,
                is_active INTEGER DEFAULT 1,
                is_triggered INTEGER DEFAULT 0,
                triggered_at TIMESTAMP,
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        """)

        cursor.execute(
            "ALTER TABLE harga_harian SET (timescaledb.compress, "
            "timescaledb.compress_segmentby = 'ticker');"
        )
        cursor.execute(
            "SELECT add_compression_policy('harga_harian', INTERVAL '30 days', "
            "if_not_exists => TRUE);"
        )
        cursor.execute(
            "ALTER TABLE harga_intraday SET (timescaledb.compress, "
            "timescaledb.compress_segmentby = 'ticker');"
        )
        cursor.execute(
            "SELECT add_compression_policy('harga_intraday', INTERVAL '7 days', "
            "if_not_exists => TRUE);"
        )
        cursor.execute(
            "ALTER TABLE technical_indicators SET (timescaledb.compress, "
            "timescaledb.compress_segmentby = 'ticker');"
        )
        cursor.execute(
            "SELECT add_compression_policy('technical_indicators', INTERVAL '60 days', "
            "if_not_exists => TRUE);"
        )

        cursor.execute(
            "SELECT add_retention_policy('prediksi', INTERVAL '2 years', "
            "if_not_exists => TRUE);"
        )
        cursor.execute(
            "SELECT add_retention_policy('harga_harian', INTERVAL '5 years', "
            "if_not_exists => TRUE);"
        )
        cursor.execute(
            "SELECT add_retention_policy('harga_intraday', INTERVAL '90 days', "
            "if_not_exists => TRUE);"
        )

        cursor.execute("""
            CREATE MATERIALIZED VIEW IF NOT EXISTS harga_weekly_agg
            WITH (timescaledb.continuous) AS
            SELECT
                ticker,
                time_bucket('1 week', tanggal) AS week,
                first(open, tanggal) AS open,
                max(high) AS high,
                min(low) AS low,
                last(close, tanggal) AS close,
                sum(volume) AS volume
            FROM harga_harian
            GROUP BY ticker, week
            WITH NO DATA;
        """)
        cursor.execute(
            "SELECT add_continuous_aggregate_policy('harga_weekly_agg', "
            "start_offset => INTERVAL '1 month', "
            "end_offset => INTERVAL '1 week', "
            "schedule_interval => INTERVAL '1 hour', if_not_exists => TRUE);"
        )

        cursor.execute("""
            CREATE MATERIALIZED VIEW IF NOT EXISTS harga_monthly_agg
            WITH (timescaledb.continuous) AS
            SELECT
                ticker,
                time_bucket('1 month', tanggal) AS month,
                first(open, tanggal) AS open,
                max(high) AS high,
                min(low) AS low,
                last(close, tanggal) AS close,
                sum(volume) AS volume
            FROM harga_harian
            GROUP BY ticker, month
            WITH NO DATA;
        """)
        cursor.execute(
            "SELECT add_continuous_aggregate_policy('harga_monthly_agg', "
            "start_offset => INTERVAL '3 months', "
            "end_offset => INTERVAL '1 month', "
            "schedule_interval => INTERVAL '1 day', if_not_exists => TRUE);"
        )

        conn.commit()
        logger.info("[PG] Database initialized with TimescaleDB hypertables")
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] init_db_pg failed: {e}")
        raise
    finally:
        cursor.close()
        _put_pg_conn(conn)


def simpan_prediksi_pg(
    ticker: str,
    tanggal_prediksi: str,
    tanggal_target: str,
    harga_prediksi: float,
    arah_prediksi: str,
    sinyal: str,
    confidence: float,
    model_votes: str,
    harga_saat_ini: Optional[float] = None,
):
    conn = _get_pg_conn()
    try:
        conn.execute(
            """
            INSERT INTO prediksi
                (ticker, tanggal_prediksi, tanggal_target, harga_saat_ini,
                 harga_prediksi, arah_prediksi, sinyal, confidence,
                 model_votes, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """,
            (
                ticker, tanggal_prediksi, tanggal_target, harga_saat_ini,
                harga_prediksi, arah_prediksi, sinyal, confidence,
                model_votes, datetime.now().isoformat(),
            ),
        )
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] simpan_prediksi failed: {e}")
        raise
    finally:
        _put_pg_conn(conn)


def update_aktual_pg(ticker: str, tanggal_target: str, harga_aktual: float) -> int:
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cursor.execute(
            """
            SELECT harga_saat_ini, harga_prediksi FROM prediksi
            WHERE ticker = %s AND tanggal_target = %s AND harga_aktual IS NULL
            """,
            (ticker, tanggal_target),
        )
        row = cursor.fetchone()
        if row is None:
            return 0

        harga_saat_ini, harga_prediksi = row
        if harga_saat_ini is not None and harga_saat_ini > 0:
            arah_aktual = "UP" if harga_aktual > harga_saat_ini else "DOWN"
        else:
            arah_aktual = "UP" if harga_aktual > harga_prediksi else "DOWN"

        cursor.execute(
            """
            UPDATE prediksi
            SET harga_aktual = %s, arah_aktual = %s, updated_at = %s
            WHERE ticker = %s AND tanggal_target = %s AND harga_aktual IS NULL
            """,
            (harga_aktual, arah_aktual, datetime.now().isoformat(), ticker, tanggal_target),
        )
        updated = cursor.rowcount
        conn.commit()
        return updated
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] update_aktual failed: {e}")
        raise
    finally:
        cursor.close()
        _put_pg_conn(conn)


def get_all_prediksi_pg(ticker: Optional[str] = None) -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        if ticker:
            query = "SELECT * FROM prediksi WHERE ticker = %s ORDER BY tanggal_prediksi DESC"
            df = pd.read_sql_query(query, conn, params=(ticker,))
        else:
            df = pd.read_sql_query("SELECT * FROM prediksi ORDER BY tanggal_prediksi DESC", conn)
        return df
    finally:
        _put_pg_conn(conn)


def get_verified_prediksi_pg(ticker: Optional[str] = None) -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        if ticker:
            query = """
                SELECT * FROM prediksi
                WHERE harga_aktual IS NOT NULL AND ticker = %s
                ORDER BY tanggal_prediksi DESC
            """
            df = pd.read_sql_query(query, conn, params=(ticker,))
        else:
            query = """
                SELECT * FROM prediksi
                WHERE harga_aktual IS NOT NULL
                ORDER BY tanggal_prediksi DESC
            """
            df = pd.read_sql_query(query, conn)
        return df
    finally:
        _put_pg_conn(conn)


def get_akurasi_metrics_pg(ticker: Optional[str] = None) -> dict:
    df = get_verified_prediksi_pg(ticker)
    if df.empty:
        return {"directional_accuracy": 0, "mape": 0, "total": 0, "benar": 0, "salah": 0}

    benar = (df["arah_prediksi"] == df["arah_aktual"]).sum()
    total = len(df)
    da = (benar / total) * 100

    valid = df.dropna(subset=["harga_prediksi", "harga_aktual"])
    valid = valid[valid["harga_aktual"] != 0]
    if not valid.empty:
        mape = (abs(valid["harga_aktual"] - valid["harga_prediksi"]) / valid["harga_aktual"]).mean() * 100
    else:
        mape = 0

    return {
        "directional_accuracy": round(da, 2),
        "mape": round(mape, 2),
        "total": total,
        "benar": int(benar),
        "salah": int(total - benar),
    }


def simpan_harga_harian_pg(ticker: str, df: pd.DataFrame):
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        for idx, row in df.iterrows():
            tanggal = idx.strftime("%Y-%m-%d") if hasattr(idx, "strftime") else str(idx)
            cursor.execute(
                """
                INSERT INTO harga_harian
                    (ticker, tanggal, open, high, low, close, volume)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
                ON CONFLICT (ticker, tanggal) DO UPDATE SET
                    open=EXCLUDED.open, high=EXCLUDED.high, low=EXCLUDED.low,
                    close=EXCLUDED.close, volume=EXCLUDED.volume
                """,
                (
                    ticker, tanggal,
                    float(row.get("Open", 0)), float(row.get("High", 0)),
                    float(row.get("Low", 0)), float(row.get("Close", 0)),
                    float(row.get("Volume", 0)),
                ),
            )
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] simpan_harga_harian failed: {e}")
        raise
    finally:
        cursor.close()
        _put_pg_conn(conn)


def load_harga_harian_pg(ticker: str, start_date: Optional[str] = None) -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        if start_date:
            query = """
                SELECT tanggal, open, high, low, close, volume
                FROM harga_harian
                WHERE ticker = %s AND tanggal >= %s
                ORDER BY tanggal ASC
            """
            df = pd.read_sql_query(query, conn, params=(ticker, start_date))
        else:
            query = """
                SELECT tanggal, open, high, low, close, volume
                FROM harga_harian
                WHERE ticker = %s
                ORDER BY tanggal ASC
            """
            df = pd.read_sql_query(query, conn, params=(ticker,))

        if df.empty:
            return pd.DataFrame()

        df = df.rename(columns={
            "open": "Open", "high": "High", "low": "Low",
            "close": "Close", "volume": "Volume",
        })
        df["tanggal"] = pd.to_datetime(df["tanggal"])
        df = df.set_index("tanggal")
        df.index.name = "Date"
        return df
    finally:
        _put_pg_conn(conn)


def get_last_date_in_db_pg(ticker: str) -> Optional[str]:
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cursor.execute(
            "SELECT MAX(tanggal) FROM harga_harian WHERE ticker = %s",
            (ticker,),
        )
        row = cursor.fetchone()
        return str(row[0]) if row and row[0] else None
    finally:
        cursor.close()
        _put_pg_conn(conn)


def get_data_count_in_db_pg(ticker: str) -> int:
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cursor.execute(
            "SELECT COUNT(*) FROM harga_harian WHERE ticker = %s",
            (ticker,),
        )
        row = cursor.fetchone()
        return int(row[0]) if row else 0
    finally:
        cursor.close()
        _put_pg_conn(conn)


def log_aktivitas_pg(aktivitas: str, detail: str = ""):
    conn = _get_pg_conn()
    try:
        conn.execute(
            "INSERT INTO log_aktivitas (aktivitas, detail) VALUES (%s, %s)",
            (aktivitas, detail),
        )
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] log_aktivitas failed: {e}")
    finally:
        _put_pg_conn(conn)


def get_log_aktivitas_pg(limit: int = 50) -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        df = pd.read_sql_query(
            "SELECT * FROM log_aktivitas ORDER BY timestamp DESC LIMIT %s",
            conn, params=(limit,),
        )
        return df
    finally:
        _put_pg_conn(conn)


def simpan_notifikasi_pg(kategori: str, judul: str, pesan: str, level: str = "info"):
    conn = _get_pg_conn()
    try:
        conn.execute(
            "INSERT INTO notifikasi (kategori, judul, pesan, level) VALUES (%s, %s, %s, %s)",
            (kategori, judul, pesan, level),
        )
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] simpan_notifikasi failed: {e}")
    finally:
        _put_pg_conn(conn)


def get_notifikasi_pg(limit: int = 100, hanya_belum_dibaca: bool = False) -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        if hanya_belum_dibaca:
            df = pd.read_sql_query(
                "SELECT * FROM notifikasi WHERE dibaca = 0 ORDER BY timestamp DESC LIMIT %s",
                conn, params=(limit,),
            )
        else:
            df = pd.read_sql_query(
                "SELECT * FROM notifikasi ORDER BY timestamp DESC LIMIT %s",
                conn, params=(limit,),
            )
        return df
    finally:
        _put_pg_conn(conn)


def mark_notifikasi_dibaca_pg(notifikasi_id: int = None, semua: bool = False):
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        if semua:
            cursor.execute("UPDATE notifikasi SET dibaca = 1 WHERE dibaca = 0")
        elif notifikasi_id is not None:
            cursor.execute("UPDATE notifikasi SET dibaca = 1 WHERE id = %s", (notifikasi_id,))
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] mark_notifikasi_dibaca failed: {e}")
    finally:
        cursor.close()
        _put_pg_conn(conn)


def get_jumlah_notifikasi_belum_dibaca_pg() -> int:
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cursor.execute("SELECT COUNT(*) FROM notifikasi WHERE dibaca = 0")
        row = cursor.fetchone()
        return int(row[0]) if row else 0
    finally:
        cursor.close()
        _put_pg_conn(conn)


def simpan_harga_intraday_pg(ticker: str, df: pd.DataFrame, interval: str = "5m"):
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        for idx, row in df.iterrows():
            ts = idx.strftime("%Y-%m-%d %H:%M:%S") if hasattr(idx, "strftime") else str(idx)
            cursor.execute(
                """
                INSERT INTO harga_intraday
                    (ticker, timestamp, interval, open, high, low, close, volume)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                ON CONFLICT (ticker, timestamp, interval) DO UPDATE SET
                    open=EXCLUDED.open, high=EXCLUDED.high, low=EXCLUDED.low,
                    close=EXCLUDED.close, volume=EXCLUDED.volume
                """,
                (
                    ticker, ts, interval,
                    float(row.get("Open", 0)), float(row.get("High", 0)),
                    float(row.get("Low", 0)), float(row.get("Close", 0)),
                    float(row.get("Volume", 0)),
                ),
            )
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] simpan_harga_intraday failed: {e}")
        raise
    finally:
        cursor.close()
        _put_pg_conn(conn)


def load_harga_intraday_pg(ticker: str, interval: str = "5m", limit: int = 1000) -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        query = """
            SELECT timestamp, open, high, low, close, volume
            FROM harga_intraday
            WHERE ticker = %s AND interval = %s
            ORDER BY timestamp DESC LIMIT %s
        """
        df = pd.read_sql_query(query, conn, params=(ticker, interval, limit))
        if df.empty:
            return pd.DataFrame()
        df = df.rename(columns={
            "open": "Open", "high": "High", "low": "Low",
            "close": "Close", "volume": "Volume",
        })
        df["timestamp"] = pd.to_datetime(df["timestamp"])
        df = df.set_index("timestamp")
        df = df.sort_index()
        return df
    finally:
        _put_pg_conn(conn)


def get_last_intraday_timestamp_pg(ticker: str, interval: str = "5m") -> Optional[str]:
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cursor.execute(
            "SELECT MAX(timestamp) FROM harga_intraday WHERE ticker = %s AND interval = %s",
            (ticker, interval),
        )
        row = cursor.fetchone()
        return str(row[0]) if row and row[0] else None
    finally:
        cursor.close()
        _put_pg_conn(conn)


def simpan_fundamental_pg(ticker: str, data: dict):
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        today = datetime.now().strftime("%Y-%m-%d")
        cols = [
            "pe_ratio", "pbv_ratio", "roe", "eps", "debt_to_equity",
            "dividend_yield", "market_cap", "revenue", "net_income",
            "total_cash", "total_debt", "free_cash_flow", "beta",
            "profit_margin", "current_ratio", "ps_ratio", "peg_ratio",
            "roa", "roic", "gross_margin", "operating_margin",
            "quick_ratio", "interest_coverage", "payout_ratio",
        ]
        placeholders = ", ".join(["%s"] * (3 + len(cols)))
        col_names = ", ".join(cols)
        cursor.execute(
            f"""
            INSERT INTO fundamental_data
                (ticker, tanggal, timestamp, {col_names})
            VALUES (%s, %s, %s, {placeholders})
            ON CONFLICT (ticker, tanggal) DO UPDATE SET
                timestamp=EXCLUDED.timestamp, {", ".join(f"{c}=EXCLUDED.{c}" for c in cols)}
            """,
            (
                ticker, today, datetime.now().isoformat(),
                *[data.get(c) for c in cols],
            ),
        )
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] simpan_fundamental failed: {e}")
        raise
    finally:
        cursor.close()
        _put_pg_conn(conn)


def load_fundamental_pg(ticker: str) -> Optional[dict]:
    conn = _get_pg_conn()
    cursor = conn.cursor(cursor_factory=RealDictCursor)
    try:
        cursor.execute(
            """SELECT * FROM fundamental_data WHERE ticker = %s
               ORDER BY timestamp DESC LIMIT 1""",
            (ticker,),
        )
        row = cursor.fetchone()
        return dict(row) if row else None
    finally:
        cursor.close()
        _put_pg_conn(conn)


def load_all_fundamentals_pg() -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        query = """
            SELECT f.* FROM fundamental_data f
            INNER JOIN (
                SELECT ticker, MAX(timestamp) as max_ts
                FROM fundamental_data GROUP BY ticker
            ) latest ON f.ticker = latest.ticker AND f.timestamp = latest.max_ts
        """
        df = pd.read_sql_query(query, conn)
        return df
    finally:
        _put_pg_conn(conn)


def simpan_technical_indicators_pg(ticker: str, indicator_date: str, data: dict):
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cols = [
            "sma_5", "sma_10", "sma_20", "sma_50", "sma_200",
            "ema_12", "ema_26", "rsi_14", "macd", "macd_signal",
            "macd_histogram", "bollinger_upper", "bollinger_middle",
            "bollinger_lower", "stoch_k", "stoch_d", "williams_r",
            "atr", "adx", "cci",
        ]
        placeholders = ", ".join(["%s"] * (2 + len(cols)))
        col_names = ", ".join(cols)
        cursor.execute(
            f"""
            INSERT INTO technical_indicators
                (ticker, date, {col_names})
            VALUES (%s, %s, {placeholders})
            ON CONFLICT (ticker, date) DO UPDATE SET
                {", ".join(f"{c}=EXCLUDED.{c}" for c in cols)}
            """,
            (ticker, indicator_date, *[data.get(c) for c in cols]),
        )
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] simpan_technical_indicators failed: {e}")
        raise
    finally:
        cursor.close()
        _put_pg_conn(conn)


def load_technical_indicators_pg(ticker: str, limit: int = 100) -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        df = pd.read_sql_query(
            """SELECT * FROM technical_indicators
               WHERE ticker = %s ORDER BY date DESC LIMIT %s""",
            conn, params=(ticker, limit),
        )
        return df
    finally:
        _put_pg_conn(conn)


def load_latest_technical_indicators_pg(ticker: str) -> Optional[dict]:
    conn = _get_pg_conn()
    cursor = conn.cursor(cursor_factory=RealDictCursor)
    try:
        cursor.execute(
            """SELECT * FROM technical_indicators
               WHERE ticker = %s ORDER BY date DESC LIMIT 1""",
            (ticker,),
        )
        row = cursor.fetchone()
        return dict(row) if row else None
    finally:
        cursor.close()
        _put_pg_conn(conn)


def simpan_financial_ratios_pg(ticker: str, period_year: int, data: dict, period_quarter: Optional[int] = None):
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cols = [
            "pe_ratio", "pb_ratio", "ps_ratio", "peg_ratio", "roe", "roa",
            "roic", "gross_margin", "operating_margin", "net_margin",
            "debt_to_equity", "current_ratio", "quick_ratio",
            "interest_coverage", "dividend_yield", "payout_ratio",
        ]
        placeholders = ", ".join(["%s"] * (3 + len(cols)))
        col_names = ", ".join(cols)
        cursor.execute(
            f"""
            INSERT INTO financial_ratios
                (ticker, period_year, period_quarter, {col_names})
            VALUES (%s, %s, %s, {placeholders})
            ON CONFLICT (ticker, period_year, period_quarter) DO UPDATE SET
                {", ".join(f"{c}=EXCLUDED.{c}" for c in cols)}
            """,
            (ticker, period_year, period_quarter, *[data.get(c) for c in cols]),
        )
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] simpan_financial_ratios failed: {e}")
        raise
    finally:
        cursor.close()
        _put_pg_conn(conn)


def load_financial_ratios_pg(ticker: str, limit: int = 20) -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        df = pd.read_sql_query(
            """SELECT * FROM financial_ratios
               WHERE ticker = %s ORDER BY period_year DESC, period_quarter DESC LIMIT %s""",
            conn, params=(ticker, limit),
        )
        return df
    finally:
        _put_pg_conn(conn)


def load_latest_financial_ratios_pg(ticker: str) -> Optional[dict]:
    conn = _get_pg_conn()
    cursor = conn.cursor(cursor_factory=RealDictCursor)
    try:
        cursor.execute(
            """SELECT * FROM financial_ratios
               WHERE ticker = %s ORDER BY period_year DESC, period_quarter DESC LIMIT 1""",
            (ticker,),
        )
        row = cursor.fetchone()
        return dict(row) if row else None
    finally:
        cursor.close()
        _put_pg_conn(conn)


def simpan_alert_pg(ticker: str, alert_type: str, condition_value: Optional[float] = None,
                    condition_text: str = "", message: str = "") -> int:
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cursor.execute(
            """INSERT INTO alerts (ticker, alert_type, condition_value, condition_text, message)
               VALUES (%s, %s, %s, %s, %s) RETURNING id""",
            (ticker, alert_type, condition_value, condition_text, message),
        )
        alert_id = cursor.fetchone()[0]
        conn.commit()
        return alert_id
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] simpan_alert failed: {e}")
        raise
    finally:
        cursor.close()
        _put_pg_conn(conn)


def get_active_alerts_pg(ticker: Optional[str] = None) -> pd.DataFrame:
    conn = _get_pg_conn()
    try:
        if ticker:
            df = pd.read_sql_query(
                """SELECT * FROM alerts WHERE is_active = 1 AND is_triggered = 0
                   AND ticker = %s ORDER BY created_at DESC""",
                conn, params=(ticker,),
            )
        else:
            df = pd.read_sql_query(
                """SELECT * FROM alerts WHERE is_active = 1 AND is_triggered = 0
                   ORDER BY created_at DESC""",
                conn,
            )
        return df
    finally:
        _put_pg_conn(conn)


def trigger_alert_pg(alert_id: int, message: str = ""):
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cursor.execute(
            """UPDATE alerts SET is_triggered = 1, triggered_at = %s, message = %s
               WHERE id = %s""",
            (datetime.now().isoformat(), message, alert_id),
        )
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] trigger_alert failed: {e}")
    finally:
        cursor.close()
        _put_pg_conn(conn)


def deactivate_alert_pg(alert_id: int):
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cursor.execute("UPDATE alerts SET is_active = 0 WHERE id = %s", (alert_id,))
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] deactivate_alert failed: {e}")
    finally:
        cursor.close()
        _put_pg_conn(conn)


def delete_alert_pg(alert_id: int):
    conn = _get_pg_conn()
    cursor = conn.cursor()
    try:
        cursor.execute("DELETE FROM alerts WHERE id = %s", (alert_id,))
        conn.commit()
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] delete_alert failed: {e}")
    finally:
        cursor.close()
        _put_pg_conn(conn)


def cleanup_old_data_pg(
    prediksi_days: int = 365,
    harga_harian_days: int = 730,
    harga_intraday_days: int = 30,
    notifikasi_days: int = 90,
    log_days: int = 90,
    tech_indicators_days: int = 365,
) -> dict:
    conn = _get_pg_conn()
    cursor = conn.cursor()
    results = {}
    try:
        tables_and_conditions = [
            ("prediksi", f"tanggal_prediksi < NOW() - INTERVAL '{prediksi_days} days'"),
            ("harga_harian", f"tanggal < NOW() - INTERVAL '{harga_harian_days} days'"),
            ("harga_intraday", f"timestamp < NOW() - INTERVAL '{harga_intraday_days} days'"),
            ("notifikasi", f"dibaca = 1 AND timestamp < NOW() - INTERVAL '{notifikasi_days} days'"),
            ("log_aktivitas", f"timestamp < NOW() - INTERVAL '{log_days} days'"),
            ("technical_indicators", f"date < NOW() - INTERVAL '{tech_indicators_days} days'"),
        ]
        for table, condition in tables_and_conditions:
            cursor.execute(f"DELETE FROM {table} WHERE {condition}")
            results[table] = cursor.rowcount
        conn.commit()
        total = sum(results.values())
        print(f"[PG CLEANUP] Deleted {total} old rows: {results}")
    except Exception as e:
        conn.rollback()
        logger.error(f"[PG] cleanup_old_data failed: {e}")
    finally:
        cursor.close()
        _put_pg_conn(conn)
    return results
