import sqlite3
from datetime import datetime
from typing import Optional
import pandas as pd
from .config import DB_PATH
from .database_base import DatabaseInterface


def get_connection():
    return sqlite3.connect(DB_PATH)


def init_db():
    conn = get_connection()
    cursor = conn.cursor()

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS prediksi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
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

    # Migration: add harga_saat_ini column if not exists (for existing databases)
    cursor.execute("PRAGMA table_info(prediksi)")
    columns = [row[1] for row in cursor.fetchall()]
    if "harga_saat_ini" not in columns:
        cursor.execute("ALTER TABLE prediksi ADD COLUMN harga_saat_ini REAL")

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS log_aktivitas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            aktivitas TEXT NOT NULL,
            detail TEXT
        )
    """)

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS harga_harian (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
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

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS notifikasi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            kategori TEXT NOT NULL,
            judul TEXT NOT NULL,
            pesan TEXT NOT NULL,
            level TEXT DEFAULT 'info',
            dibaca INTEGER DEFAULT 0
        )
    """)

    # Intraday price data (1m, 5m, 15m, 1h intervals)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS harga_intraday (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ticker TEXT NOT NULL,
            timestamp DATETIME NOT NULL,
            interval TEXT NOT NULL,
            open REAL,
            high REAL,
            low REAL,
            close REAL,
            volume REAL,
            UNIQUE(ticker, timestamp, interval)
        )
    """)

    # Fundamental data per ticker (snapshot)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS fundamental_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ticker TEXT NOT NULL,
            tanggal DATE NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            pe_ratio REAL,
            pbv_ratio REAL,
            roe REAL,
            eps REAL,
            debt_to_equity REAL,
            dividend_yield REAL,
            market_cap REAL,
            revenue REAL,
            net_income REAL,
            total_cash REAL,
            total_debt REAL,
            free_cash_flow REAL,
            beta REAL,
            profit_margin REAL,
            current_ratio REAL,
            UNIQUE(ticker, tanggal)
        )
    """)

    # Migration: add columns merged from financial_ratios table
    cursor.execute("PRAGMA table_info(fundamental_data)")
    existing_cols = {row[1] for row in cursor.fetchall()}
    new_cols = [
        "ps_ratio", "peg_ratio", "roa", "roic",
        "gross_margin", "operating_margin",
        "quick_ratio", "interest_coverage", "payout_ratio",
    ]
    for col in new_cols:
        if col not in existing_cols:
            cursor.execute(f"ALTER TABLE fundamental_data ADD COLUMN {col} REAL")

    # ===========================================================================
    # TECHNICAL INDICATORS (diadopsi dari db_saham_optimized.sql)
    # ===========================================================================
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS technical_indicators (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ticker TEXT NOT NULL,
            date DATE NOT NULL,
            sma_5 REAL,
            sma_10 REAL,
            sma_20 REAL,
            sma_50 REAL,
            sma_200 REAL,
            ema_12 REAL,
            ema_26 REAL,
            rsi_14 REAL,
            macd REAL,
            macd_signal REAL,
            macd_histogram REAL,
            bollinger_upper REAL,
            bollinger_middle REAL,
            bollinger_lower REAL,
            stoch_k REAL,
            stoch_d REAL,
            williams_r REAL,
            atr REAL,
            adx REAL,
            cci REAL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(ticker, date)
        )
    """)

    # ===========================================================================
    # FINANCIAL RATIOS (diadopsi dari db_saham_optimized.sql)
    # ===========================================================================
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS financial_ratios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ticker TEXT NOT NULL,
            period_year INTEGER NOT NULL,
            period_quarter INTEGER,
            pe_ratio REAL,
            pb_ratio REAL,
            ps_ratio REAL,
            peg_ratio REAL,
            roe REAL,
            roa REAL,
            roic REAL,
            gross_margin REAL,
            operating_margin REAL,
            net_margin REAL,
            debt_to_equity REAL,
            current_ratio REAL,
            quick_ratio REAL,
            interest_coverage REAL,
            dividend_yield REAL,
            payout_ratio REAL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(ticker, period_year, period_quarter)
        )
    """)

    # ===========================================================================
    # ALERTS (diadopsi dari db_saham_optimized.sql, disesuaikan untuk single-user)
    # ===========================================================================
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS alerts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
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

    conn.commit()

    # Create indexes and apply PRAGMA optimizations
    try:
        from .db_optimizer import create_indexes
        create_indexes(conn)
    except Exception as e:
        print(f"[DB] Index optimization skipped: {e}")

    conn.close()


def simpan_prediksi(
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
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO prediksi
            (ticker, tanggal_prediksi, tanggal_target, harga_saat_ini, harga_prediksi,
             arah_prediksi, sinyal, confidence, model_votes, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        """,
        (
            ticker,
            tanggal_prediksi,
            tanggal_target,
            harga_saat_ini,
            harga_prediksi,
            arah_prediksi,
            sinyal,
            confidence,
            model_votes,
            datetime.now().isoformat(),
        ),
    )
    conn.commit()
    conn.close()


def update_aktual(ticker: str, tanggal_target: str, harga_aktual: float):
    conn = get_connection()
    cursor = conn.cursor()

    # Get harga_saat_ini to determine actual direction
    cursor.execute(
        """
        SELECT harga_saat_ini, harga_prediksi FROM prediksi
        WHERE ticker = ? AND tanggal_target = ? AND harga_aktual IS NULL
        """,
        (ticker, tanggal_target),
    )
    row = cursor.fetchone()
    if row is None:
        conn.close()
        return 0

    harga_saat_ini, harga_prediksi = row
    # Determine actual direction: compare actual price with price at prediction time
    if harga_saat_ini is not None and harga_saat_ini > 0:
        arah_aktual = "UP" if harga_aktual > harga_saat_ini else "DOWN"
    else:
        # Fallback: compare with predicted price
        arah_aktual = "UP" if harga_aktual > harga_prediksi else "DOWN"

    cursor.execute(
        """
        UPDATE prediksi
        SET harga_aktual = ?, arah_aktual = ?, updated_at = ?
        WHERE ticker = ? AND tanggal_target = ? AND harga_aktual IS NULL
        """,
        (harga_aktual, arah_aktual, datetime.now().isoformat(), ticker, tanggal_target),
    )
    updated = cursor.rowcount
    conn.commit()
    conn.close()
    return updated


def get_all_prediksi(ticker: Optional[str] = None) -> pd.DataFrame:
    conn = get_connection()
    if ticker:
        query = "SELECT * FROM prediksi WHERE ticker = ? ORDER BY tanggal_prediksi DESC"
        df = pd.read_sql_query(query, conn, params=(ticker,))
    else:
        df = pd.read_sql_query("SELECT * FROM prediksi ORDER BY tanggal_prediksi DESC", conn)
    conn.close()
    return df


def get_verified_prediksi(ticker: Optional[str] = None) -> pd.DataFrame:
    conn = get_connection()
    if ticker:
        query = """
            SELECT * FROM prediksi
            WHERE harga_aktual IS NOT NULL AND ticker = ?
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
    conn.close()
    return df


def get_akurasi_metrics(ticker: Optional[str] = None) -> dict:
    df = get_verified_prediksi(ticker)
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


def simpan_harga_harian(ticker: str, df: pd.DataFrame):
    conn = get_connection()
    for idx, row in df.iterrows():
        tanggal = idx.strftime("%Y-%m-%d") if hasattr(idx, "strftime") else str(idx)
        conn.execute(
            """
            INSERT OR REPLACE INTO harga_harian
                (ticker, tanggal, open, high, low, close, volume)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            """,
            (
                ticker,
                tanggal,
                float(row.get("Open", 0)),
                float(row.get("High", 0)),
                float(row.get("Low", 0)),
                float(row.get("Close", 0)),
                float(row.get("Volume", 0)),
            ),
        )
    conn.commit()
    conn.close()


def load_harga_harian(ticker: str, start_date: Optional[str] = None) -> pd.DataFrame:
    """
    Load historical price data from database.

    Returns DataFrame with columns: Open, High, Low, Close, Volume
    indexed by date (DatetimeIndex).
    """
    conn = get_connection()
    if start_date:
        query = """
            SELECT tanggal, open, high, low, close, volume
            FROM harga_harian
            WHERE ticker = ? AND tanggal >= ?
            ORDER BY tanggal ASC
        """
        df = pd.read_sql_query(query, conn, params=(ticker, start_date))
    else:
        query = """
            SELECT tanggal, open, high, low, close, volume
            FROM harga_harian
            WHERE ticker = ?
            ORDER BY tanggal ASC
        """
        df = pd.read_sql_query(query, conn, params=(ticker,))

    conn.close()

    if df.empty:
        return pd.DataFrame()

    df = df.rename(columns={
        "open": "Open",
        "high": "High",
        "low": "Low",
        "close": "Close",
        "volume": "Volume",
    })
    df["tanggal"] = pd.to_datetime(df["tanggal"])
    df = df.set_index("tanggal")
    df.index.name = "Date"
    return df


def get_last_date_in_db(ticker: str) -> Optional[str]:
    """Get the most recent date stored in database for a ticker."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        "SELECT MAX(tanggal) FROM harga_harian WHERE ticker = ?",
        (ticker,),
    )
    row = cursor.fetchone()
    conn.close()
    if row and row[0]:
        return str(row[0])
    return None


def get_data_count_in_db(ticker: str) -> int:
    """Get total number of rows stored for a ticker."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        "SELECT COUNT(*) FROM harga_harian WHERE ticker = ?",
        (ticker,),
    )
    row = cursor.fetchone()
    conn.close()
    return int(row[0]) if row else 0


def log_aktivitas(aktivitas: str, detail: str = ""):
    conn = get_connection()
    conn.execute(
        "INSERT INTO log_aktivitas (aktivitas, detail) VALUES (?, ?)",
        (aktivitas, detail),
    )
    conn.commit()
    conn.close()


def get_log_aktivitas(limit: int = 50) -> pd.DataFrame:
    conn = get_connection()
    df = pd.read_sql_query(
        "SELECT * FROM log_aktivitas ORDER BY timestamp DESC LIMIT ?", conn, params=(limit,)
    )
    conn.close()
    return df


# ===========================================================================
# NOTIFIKASI IN-APP
# ===========================================================================

def simpan_notifikasi(kategori: str, judul: str, pesan: str, level: str = "info"):
    """Simpan notifikasi ke database untuk ditampilkan di in-app notification center."""
    conn = get_connection()
    conn.execute(
        "INSERT INTO notifikasi (kategori, judul, pesan, level) VALUES (?, ?, ?, ?)",
        (kategori, judul, pesan, level),
    )
    conn.commit()
    conn.close()


def get_notifikasi(limit: int = 100, hanya_belum_dibaca: bool = False) -> pd.DataFrame:
    """Ambil notifikasi dari database."""
    conn = get_connection()
    if hanya_belum_dibaca:
        df = pd.read_sql_query(
            "SELECT * FROM notifikasi WHERE dibaca = 0 ORDER BY timestamp DESC LIMIT ?",
            conn, params=(limit,),
        )
    else:
        df = pd.read_sql_query(
            "SELECT * FROM notifikasi ORDER BY timestamp DESC LIMIT ?",
            conn, params=(limit,),
        )
    conn.close()
    return df


def mark_notifikasi_dibaca(notifikasi_id: int = None, semua: bool = False):
    """Tandai notifikasi sebagai sudah dibaca."""
    conn = get_connection()
    if semua:
        conn.execute("UPDATE notifikasi SET dibaca = 1 WHERE dibaca = 0")
    elif notifikasi_id is not None:
        conn.execute("UPDATE notifikasi SET dibaca = 1 WHERE id = ?", (notifikasi_id,))
    conn.commit()
    conn.close()


def get_jumlah_notifikasi_belum_dibaca() -> int:
    """Hitung jumlah notifikasi belum dibaca."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT COUNT(*) FROM notifikasi WHERE dibaca = 0")
    row = cursor.fetchone()
    conn.close()
    return int(row[0]) if row else 0


# ===========================================================================
# INTRADAY DATA
# ===========================================================================

def simpan_harga_intraday(ticker: str, df: pd.DataFrame, interval: str = "5m"):
    """Save intraday price data to database."""
    conn = get_connection()
    for idx, row in df.iterrows():
        ts = idx.strftime("%Y-%m-%d %H:%M:%S") if hasattr(idx, "strftime") else str(idx)
        conn.execute(
            """
            INSERT OR REPLACE INTO harga_intraday
                (ticker, timestamp, interval, open, high, low, close, volume)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            """,
            (
                ticker, ts, interval,
                float(row.get("Open", 0)),
                float(row.get("High", 0)),
                float(row.get("Low", 0)),
                float(row.get("Close", 0)),
                float(row.get("Volume", 0)),
            ),
        )
    conn.commit()
    conn.close()


def load_harga_intraday(ticker: str, interval: str = "5m", limit: int = 1000) -> pd.DataFrame:
    """Load intraday price data from database."""
    conn = get_connection()
    query = """
        SELECT timestamp, open, high, low, close, volume
        FROM harga_intraday
        WHERE ticker = ? AND interval = ?
        ORDER BY timestamp DESC LIMIT ?
    """
    df = pd.read_sql_query(query, conn, params=(ticker, interval, limit))
    conn.close()
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


def get_last_intraday_timestamp(ticker: str, interval: str = "5m") -> Optional[str]:
    """Get most recent intraday timestamp for a ticker."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        "SELECT MAX(timestamp) FROM harga_intraday WHERE ticker = ? AND interval = ?",
        (ticker, interval),
    )
    row = cursor.fetchone()
    conn.close()
    return str(row[0]) if row and row[0] else None


# ===========================================================================
# FUNDAMENTAL DATA
# ===========================================================================

def simpan_fundamental(ticker: str, data: dict):
    """Save fundamental data snapshot to database."""
    conn = get_connection()
    today = datetime.now().strftime("%Y-%m-%d")
    conn.execute(
        """
        INSERT OR REPLACE INTO fundamental_data
            (ticker, tanggal, timestamp, pe_ratio, pbv_ratio, roe, eps,
             debt_to_equity, dividend_yield, market_cap, revenue,
             net_income, total_cash, total_debt, free_cash_flow,
             beta, profit_margin, current_ratio,
             ps_ratio, peg_ratio, roa, roic,
             gross_margin, operating_margin,
             quick_ratio, interest_coverage, payout_ratio)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        """,
        (
            ticker, today, datetime.now().isoformat(),
            data.get("pe_ratio"), data.get("pbv_ratio"),
            data.get("roe"), data.get("eps"),
            data.get("debt_to_equity"), data.get("dividend_yield"),
            data.get("market_cap"), data.get("revenue"),
            data.get("net_income"), data.get("total_cash"),
            data.get("total_debt"), data.get("free_cash_flow"),
            data.get("beta"), data.get("profit_margin"),
            data.get("current_ratio"),
            data.get("ps_ratio"), data.get("peg_ratio"),
            data.get("roa"), data.get("roic"),
            data.get("gross_margin"), data.get("operating_margin"),
            data.get("quick_ratio"), data.get("interest_coverage"),
            data.get("payout_ratio"),
        ),
    )
    conn.commit()
    conn.close()


def load_fundamental(ticker: str) -> Optional[dict]:
    """Load latest fundamental data for a ticker."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        """SELECT * FROM fundamental_data WHERE ticker = ?
           ORDER BY timestamp DESC LIMIT 1""",
        (ticker,),
    )
    row = cursor.fetchone()
    conn.close()
    if not row:
        return None
    columns = [
        "id", "ticker", "tanggal", "timestamp", "pe_ratio", "pbv_ratio",
        "roe", "eps", "debt_to_equity", "dividend_yield",
        "market_cap", "revenue", "net_income", "total_cash",
        "total_debt", "free_cash_flow", "beta", "profit_margin",
        "current_ratio",
        "ps_ratio", "peg_ratio", "roa", "roic",
        "gross_margin", "operating_margin",
        "quick_ratio", "interest_coverage", "payout_ratio",
    ]
    return dict(zip(columns, row))


def load_all_fundamentals() -> pd.DataFrame:
    """Load latest fundamental data for all tickers."""
    conn = get_connection()
    query = """
        SELECT f.* FROM fundamental_data f
        INNER JOIN (
            SELECT ticker, MAX(timestamp) as max_ts
            FROM fundamental_data GROUP BY ticker
        ) latest ON f.ticker = latest.ticker AND f.timestamp = latest.max_ts
    """
    df = pd.read_sql_query(query, conn)
    conn.close()
    return df


# ===========================================================================
# TECHNICAL INDICATORS
# ===========================================================================

def simpan_technical_indicators(ticker: str, indicator_date: str, data: dict):
    """Save technical indicator snapshot for a ticker on a given date."""
    conn = get_connection()
    conn.execute(
        """
        INSERT OR REPLACE INTO technical_indicators
            (ticker, date, sma_5, sma_10, sma_20, sma_50, sma_200,
             ema_12, ema_26, rsi_14, macd, macd_signal, macd_histogram,
             bollinger_upper, bollinger_middle, bollinger_lower,
             stoch_k, stoch_d, williams_r, atr, adx, cci)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        """,
        (
            ticker, indicator_date,
            data.get("sma_5"), data.get("sma_10"), data.get("sma_20"),
            data.get("sma_50"), data.get("sma_200"),
            data.get("ema_12"), data.get("ema_26"),
            data.get("rsi_14"),
            data.get("macd"), data.get("macd_signal"), data.get("macd_histogram"),
            data.get("bollinger_upper"), data.get("bollinger_middle"), data.get("bollinger_lower"),
            data.get("stoch_k"), data.get("stoch_d"),
            data.get("williams_r"),
            data.get("atr"), data.get("adx"), data.get("cci"),
        ),
    )
    conn.commit()
    conn.close()


def load_technical_indicators(ticker: str, limit: int = 100) -> pd.DataFrame:
    """Load technical indicators for a ticker, most recent first."""
    conn = get_connection()
    df = pd.read_sql_query(
        """SELECT * FROM technical_indicators
           WHERE ticker = ? ORDER BY date DESC LIMIT ?""",
        conn, params=(ticker, limit),
    )
    conn.close()
    return df


def load_latest_technical_indicators(ticker: str) -> Optional[dict]:
    """Load most recent technical indicator snapshot for a ticker."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        """SELECT * FROM technical_indicators
           WHERE ticker = ? ORDER BY date DESC LIMIT 1""",
        (ticker,),
    )
    row = cursor.fetchone()
    conn.close()
    if not row:
        return None
    columns = [
        "id", "ticker", "date", "sma_5", "sma_10", "sma_20", "sma_50", "sma_200",
        "ema_12", "ema_26", "rsi_14", "macd", "macd_signal", "macd_histogram",
        "bollinger_upper", "bollinger_middle", "bollinger_lower",
        "stoch_k", "stoch_d", "williams_r", "atr", "adx", "cci", "created_at",
    ]
    return dict(zip(columns, row))


# ===========================================================================
# FINANCIAL RATIOS
# ===========================================================================

def simpan_financial_ratios(ticker: str, period_year: int, data: dict, period_quarter: Optional[int] = None):
    """Save financial ratio snapshot for a ticker per period."""
    conn = get_connection()
    conn.execute(
        """
        INSERT OR REPLACE INTO financial_ratios
            (ticker, period_year, period_quarter,
             pe_ratio, pb_ratio, ps_ratio, peg_ratio,
             roe, roa, roic, gross_margin, operating_margin, net_margin,
             debt_to_equity, current_ratio, quick_ratio, interest_coverage,
             dividend_yield, payout_ratio)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        """,
        (
            ticker, period_year, period_quarter,
            data.get("pe_ratio"), data.get("pb_ratio"),
            data.get("ps_ratio"), data.get("peg_ratio"),
            data.get("roe"), data.get("roa"), data.get("roic"),
            data.get("gross_margin"), data.get("operating_margin"), data.get("net_margin"),
            data.get("debt_to_equity"), data.get("current_ratio"),
            data.get("quick_ratio"), data.get("interest_coverage"),
            data.get("dividend_yield"), data.get("payout_ratio"),
        ),
    )
    conn.commit()
    conn.close()


def load_financial_ratios(ticker: str, limit: int = 20) -> pd.DataFrame:
    """Load financial ratios for a ticker, most recent first."""
    conn = get_connection()
    df = pd.read_sql_query(
        """SELECT * FROM financial_ratios
           WHERE ticker = ? ORDER BY period_year DESC, period_quarter DESC LIMIT ?""",
        conn, params=(ticker, limit),
    )
    conn.close()
    return df


def load_latest_financial_ratios(ticker: str) -> Optional[dict]:
    """Load most recent financial ratio snapshot for a ticker."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        """SELECT * FROM financial_ratios
           WHERE ticker = ? ORDER BY period_year DESC, period_quarter DESC LIMIT 1""",
        (ticker,),
    )
    row = cursor.fetchone()
    conn.close()
    if not row:
        return None
    columns = [
        "id", "ticker", "period_year", "period_quarter",
        "pe_ratio", "pb_ratio", "ps_ratio", "peg_ratio",
        "roe", "roa", "roic", "gross_margin", "operating_margin", "net_margin",
        "debt_to_equity", "current_ratio", "quick_ratio", "interest_coverage",
        "dividend_yield", "payout_ratio", "created_at",
    ]
    return dict(zip(columns, row))


# ===========================================================================
# ALERTS
# ===========================================================================

def simpan_alert(ticker: str, alert_type: str, condition_value: Optional[float] = None,
                 condition_text: str = "", message: str = ""):
    """Create a new price/technical alert for a ticker."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        """INSERT INTO alerts (ticker, alert_type, condition_value, condition_text, message)
           VALUES (?, ?, ?, ?, ?)""",
        (ticker, alert_type, condition_value, condition_text, message),
    )
    alert_id = cursor.lastrowid
    conn.commit()
    conn.close()
    return alert_id


def get_active_alerts(ticker: Optional[str] = None) -> pd.DataFrame:
    """Get all active, untriggered alerts."""
    conn = get_connection()
    if ticker:
        df = pd.read_sql_query(
            """SELECT * FROM alerts WHERE is_active = 1 AND is_triggered = 0
               AND ticker = ? ORDER BY created_at DESC""",
            conn, params=(ticker,),
        )
    else:
        df = pd.read_sql_query(
            """SELECT * FROM alerts WHERE is_active = 1 AND is_triggered = 0
               ORDER BY created_at DESC""",
            conn,
        )
    conn.close()
    return df


def trigger_alert(alert_id: int, message: str = ""):
    """Mark an alert as triggered."""
    conn = get_connection()
    conn.execute(
        """UPDATE alerts SET is_triggered = 1, triggered_at = ?, message = ?
           WHERE id = ?""",
        (datetime.now().isoformat(), message, alert_id),
    )
    conn.commit()
    conn.close()


def deactivate_alert(alert_id: int):
    """Deactivate an alert."""
    conn = get_connection()
    conn.execute("UPDATE alerts SET is_active = 0 WHERE id = ?", (alert_id,))
    conn.commit()
    conn.close()


def delete_alert(alert_id: int):
    """Delete an alert."""
    conn = get_connection()
    conn.execute("DELETE FROM alerts WHERE id = ?", (alert_id,))
    conn.commit()
    conn.close()


# ===========================================================================
# HOUSEKEEPING — DELETE old data for storage management
# ===========================================================================

def delete_old_prediksi(days: int = 365) -> int:
    """Delete predictions older than N days. Returns deleted row count."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        "DELETE FROM prediksi WHERE tanggal_prediksi < date('now', ?)",
        (f"-{days} days",),
    )
    deleted = cursor.rowcount
    conn.commit()
    conn.close()
    return deleted


def delete_old_harga_harian(days: int = 730) -> int:
    """Delete daily price data older than N days. Returns deleted row count."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        "DELETE FROM harga_harian WHERE tanggal < date('now', ?)",
        (f"-{days} days",),
    )
    deleted = cursor.rowcount
    conn.commit()
    conn.close()
    return deleted


def delete_old_harga_intraday(days: int = 30) -> int:
    """Delete intraday price data older than N days. Returns deleted row count."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        "DELETE FROM harga_intraday WHERE timestamp < datetime('now', ?)",
        (f"-{days} days",),
    )
    deleted = cursor.rowcount
    conn.commit()
    conn.close()
    return deleted


def delete_old_notifikasi(days: int = 90) -> int:
    """Delete read notifications older than N days. Returns deleted row count."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        "DELETE FROM notifikasi WHERE dibaca = 1 AND timestamp < datetime('now', ?)",
        (f"-{days} days",),
    )
    deleted = cursor.rowcount
    conn.commit()
    conn.close()
    return deleted


def delete_old_log_aktivitas(days: int = 90) -> int:
    """Delete activity logs older than N days. Returns deleted row count."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        "DELETE FROM log_aktivitas WHERE timestamp < datetime('now', ?)",
        (f"-{days} days",),
    )
    deleted = cursor.rowcount
    conn.commit()
    conn.close()
    return deleted


def delete_old_technical_indicators(days: int = 365) -> int:
    """Delete technical indicator snapshots older than N days. Returns deleted row count."""
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute(
        "DELETE FROM technical_indicators WHERE date < date('now', ?)",
        (f"-{days} days",),
    )
    deleted = cursor.rowcount
    conn.commit()
    conn.close()
    return deleted


def cleanup_old_data(
    prediksi_days: int = 365,
    harga_harian_days: int = 730,
    harga_intraday_days: int = 30,
    notifikasi_days: int = 90,
    log_days: int = 90,
    tech_indicators_days: int = 365,
) -> dict:
    """Master cleanup — delete old data from all tables. Returns summary dict."""
    results = {
        "prediksi": delete_old_prediksi(prediksi_days),
        "harga_harian": delete_old_harga_harian(harga_harian_days),
        "harga_intraday": delete_old_harga_intraday(harga_intraday_days),
        "notifikasi": delete_old_notifikasi(notifikasi_days),
        "log_aktivitas": delete_old_log_aktivitas(log_days),
        "technical_indicators": delete_old_technical_indicators(tech_indicators_days),
    }
    total = sum(results.values())
    print(f"[CLEANUP] Deleted {total} old rows: {results}")
    return results


# ===========================================================================
# POSTGRESQL DISPATCH LAYER
# If DATABASE_URL is set and psycopg2 is available, override all functions
# with PostgreSQL implementations from database_pg.py.
# Otherwise, the SQLite functions above remain active.
# ===========================================================================
import os as _os

_DATABASE_URL = _os.getenv("DATABASE_URL", "")

if _DATABASE_URL:
    try:
        from .database_pg import (
            is_pg_enabled as _is_pg_enabled,
            init_db_pg as _init_db_pg,
            simpan_prediksi_pg as _simpan_prediksi_pg,
            update_aktual_pg as _update_aktual_pg,
            get_all_prediksi_pg as _get_all_prediksi_pg,
            get_verified_prediksi_pg as _get_verified_prediksi_pg,
            get_akurasi_metrics_pg as _get_akurasi_metrics_pg,
            simpan_harga_harian_pg as _simpan_harga_harian_pg,
            load_harga_harian_pg as _load_harga_harian_pg,
            get_last_date_in_db_pg as _get_last_date_in_db_pg,
            get_data_count_in_db_pg as _get_data_count_in_db_pg,
            log_aktivitas_pg as _log_aktivitas_pg,
            get_log_aktivitas_pg as _get_log_aktivitas_pg,
            simpan_notifikasi_pg as _simpan_notifikasi_pg,
            get_notifikasi_pg as _get_notifikasi_pg,
            mark_notifikasi_dibaca_pg as _mark_notifikasi_dibaca_pg,
            get_jumlah_notifikasi_belum_dibaca_pg as _get_jumlah_notifikasi_belum_dibaca_pg,
            simpan_harga_intraday_pg as _simpan_harga_intraday_pg,
            load_harga_intraday_pg as _load_harga_intraday_pg,
            get_last_intraday_timestamp_pg as _get_last_intraday_timestamp_pg,
            simpan_fundamental_pg as _simpan_fundamental_pg,
            load_fundamental_pg as _load_fundamental_pg,
            load_all_fundamentals_pg as _load_all_fundamentals_pg,
            simpan_technical_indicators_pg as _simpan_technical_indicators_pg,
            load_technical_indicators_pg as _load_technical_indicators_pg,
            load_latest_technical_indicators_pg as _load_latest_technical_indicators_pg,
            simpan_financial_ratios_pg as _simpan_financial_ratios_pg,
            load_financial_ratios_pg as _load_financial_ratios_pg,
            load_latest_financial_ratios_pg as _load_latest_financial_ratios_pg,
            simpan_alert_pg as _simpan_alert_pg,
            get_active_alerts_pg as _get_active_alerts_pg,
            trigger_alert_pg as _trigger_alert_pg,
            deactivate_alert_pg as _deactivate_alert_pg,
            delete_alert_pg as _delete_alert_pg,
            cleanup_old_data_pg as _cleanup_old_data_pg,
        )

        if _is_pg_enabled():
            # Override all functions with PostgreSQL implementations
            init_db = _init_db_pg
            simpan_prediksi = _simpan_prediksi_pg
            update_aktual = _update_aktual_pg
            get_all_prediksi = _get_all_prediksi_pg
            get_verified_prediksi = _get_verified_prediksi_pg
            get_akurasi_metrics = _get_akurasi_metrics_pg
            simpan_harga_harian = _simpan_harga_harian_pg
            load_harga_harian = _load_harga_harian_pg
            get_last_date_in_db = _get_last_date_in_db_pg
            get_data_count_in_db = _get_data_count_in_db_pg
            log_aktivitas = _log_aktivitas_pg
            get_log_aktivitas = _get_log_aktivitas_pg
            simpan_notifikasi = _simpan_notifikasi_pg
            get_notifikasi = _get_notifikasi_pg
            mark_notifikasi_dibaca = _mark_notifikasi_dibaca_pg
            get_jumlah_notifikasi_belum_dibaca = _get_jumlah_notifikasi_belum_dibaca_pg
            simpan_harga_intraday = _simpan_harga_intraday_pg
            load_harga_intraday = _load_harga_intraday_pg
            get_last_intraday_timestamp = _get_last_intraday_timestamp_pg
            simpan_fundamental = _simpan_fundamental_pg
            load_fundamental = _load_fundamental_pg
            load_all_fundamentals = _load_all_fundamentals_pg
            simpan_technical_indicators = _simpan_technical_indicators_pg
            load_technical_indicators = _load_technical_indicators_pg
            load_latest_technical_indicators = _load_latest_technical_indicators_pg
            simpan_financial_ratios = _simpan_financial_ratios_pg
            load_financial_ratios = _load_financial_ratios_pg
            load_latest_financial_ratios = _load_latest_financial_ratios_pg
            simpan_alert = _simpan_alert_pg
            get_active_alerts = _get_active_alerts_pg
            trigger_alert = _trigger_alert_pg
            deactivate_alert = _deactivate_alert_pg
            delete_alert = _delete_alert_pg
            cleanup_old_data = _cleanup_old_data_pg

            def get_connection():
                from .database_pg import _get_pg_conn
                return _get_pg_conn()

            print("[DB] Using PostgreSQL + TimescaleDB backend")
        else:
            print("[DB] DATABASE_URL set but psycopg2 not available — using SQLite fallback")
    except ImportError:
        print("[DB] database_pg import failed — using SQLite fallback")
