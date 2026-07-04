"""
Migration script: SQLite → PostgreSQL + TimescaleDB.

Usage:
    python -m src.migrations.001_sqlite_to_pg

Reads all data from the existing SQLite database and inserts it into PostgreSQL.
Requires DATABASE_URL environment variable to be set and PostgreSQL + TimescaleDB running.
"""

import os
import sys
import sqlite3
import logging
import pandas as pd
from datetime import datetime

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))

from src.config import DB_PATH
from src.database_pg import is_pg_enabled, init_db_pg, _get_pg_conn, _put_pg_conn

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
logger = logging.getLogger("migration")


TABLES_TO_MIGRATE = [
    "prediksi",
    "log_aktivitas",
    "harga_harian",
    "notifikasi",
    "harga_intraday",
    "fundamental_data",
    "technical_indicators",
    "financial_ratios",
    "alerts",
]

BATCH_SIZE = 1000


def migrate_table(sqlite_conn: sqlite3.Connection, table_name: str):
    """Migrate a single table from SQLite to PostgreSQL."""
    cursor = sqlite_conn.cursor()

    try:
        cursor.execute(f"SELECT COUNT(*) FROM {table_name}")
        total_rows = cursor.fetchone()[0]
    except sqlite3.OperationalError:
        logger.info(f"  {table_name}: table not found in SQLite, skipping")
        return 0

    if total_rows == 0:
        logger.info(f"  {table_name}: 0 rows, skipping")
        return 0

    cursor.execute(f"PRAGMA table_info({table_name})")
    columns = [row[1] for row in cursor.fetchall()]

    pg_conn = _get_pg_conn()
    pg_cursor = pg_conn.cursor()

    migrated = 0
    try:
        cursor.execute(f"SELECT {', '.join(columns)} FROM {table_name}")
        col_placeholders = ", ".join(["%s"] * len(columns))
        col_names = ", ".join(columns)

        while True:
            rows = cursor.fetchmany(BATCH_SIZE)
            if not rows:
                break

            for row in rows:
                row_list = list(row)
                for i, val in enumerate(row_list):
                    if isinstance(val, str) and val == "":
                        row_list[i] = None

                pg_cursor.execute(
                    f"INSERT INTO {table_name} ({col_names}) VALUES ({col_placeholders}) "
                    f"ON CONFLICT DO NOTHING",
                    row_list,
                )
                migrated += 1

            pg_conn.commit()
            logger.info(f"  {table_name}: migrated {migrated}/{total_rows} rows")

        logger.info(f"  {table_name}: DONE — {migrated} rows migrated")
    except Exception as e:
        pg_conn.rollback()
        logger.error(f"  {table_name}: FAILED — {e}")
        raise
    finally:
        pg_cursor.close()
        _put_pg_conn(pg_conn)
        cursor.close()

    return migrated


def run_migration():
    """Run full migration from SQLite to PostgreSQL."""
    if not is_pg_enabled():
        logger.error("PostgreSQL is not enabled. Set DATABASE_URL and install psycopg2.")
        sys.exit(1)

    if not os.path.exists(DB_PATH):
        logger.error(f"SQLite database not found at {DB_PATH}")
        sys.exit(1)

    logger.info("=" * 60)
    logger.info("SQLite → PostgreSQL + TimescaleDB Migration")
    logger.info("=" * 60)
    logger.info(f"Source: {DB_PATH}")
    logger.info(f"Target: {os.getenv('DATABASE_URL', 'NOT SET')}")
    logger.info("")

    logger.info("Step 1: Initialize PostgreSQL schema with TimescaleDB hypertables...")
    init_db_pg()

    logger.info("Step 2: Migrate data table by table...")
    sqlite_conn = sqlite3.connect(DB_PATH)

    total_migrated = 0
    for table in TABLES_TO_MIGRATE:
        logger.info(f"  Migrating {table}...")
        count = migrate_table(sqlite_conn, table)
        total_migrated += count

    sqlite_conn.close()

    logger.info("")
    logger.info("=" * 60)
    logger.info(f"Migration complete! Total rows migrated: {total_migrated}")
    logger.info("")
    logger.info("Next steps:")
    logger.info("  1. Verify data: python -c \"from src.database import get_all_prediksi; print(get_all_prediksi().shape)\"")
    logger.info("  2. Run tests: python -m pytest tests/ -q --tb=short")
    logger.info("  3. Set DATABASE_URL in .env for permanent switch")
    logger.info("=" * 60)


if __name__ == "__main__":
    run_migration()
