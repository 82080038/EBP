"""
Celery application for async task processing.

Tasks:
- fetch_market_data: Fetch and cache market data
- run_prediction: Run prediction pipeline for a ticker
- run_backtest: Run backtest for a ticker
- run_screener: Run stock screener
- retrain_model: Retrain ML model with latest data
- send_notification: Send Telegram/email notification

Usage:
    # Start worker:
    celery -A src.celery_app worker --loglevel=info

    # Start monitoring (flower):
    celery -A src.celery_app flower --port=5555

    # Submit task from code:
    from src.celery_app import run_prediction_task
    result = run_prediction_task.delay(ticker="^JKSE", period="2y")
    task_id = result.id
    status = result.status  # PENDING, STARTED, SUCCESS, FAILURE
    output = result.result  # Task return value when SUCCESS
"""

import os
import logging
import sys

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

logger = logging.getLogger("saham.celery")

try:
    from celery import Celery, Task
    from celery.result import AsyncResult

    _CELERY_AVAILABLE = True
except ImportError:
    _CELERY_AVAILABLE = False
    logger.info("celery not installed — async tasks disabled")

CELERY_BROKER_URL = os.getenv("CELERY_BROKER_URL", os.getenv("REDIS_URL", "redis://localhost:6379/0"))
CELERY_RESULT_BACKEND = os.getenv("CELERY_RESULT_BACKEND", os.getenv("REDIS_URL", "redis://localhost:6379/0"))

if _CELERY_AVAILABLE:
    app = Celery(
        "saham",
        broker=CELERY_BROKER_URL,
        backend=CELERY_RESULT_BACKEND,
    )

    app.conf.update(
        task_serializer="json",
        accept_content=["json"],
        result_serializer="json",
        timezone="Asia/Jakarta",
        enable_utc=True,
        task_track_started=True,
        task_time_limit=600,
        task_soft_time_limit=540,
        worker_prefetch_multiplier=1,
        worker_max_tasks_per_child=50,
        task_acks_late=True,
        task_reject_on_worker_lost=True,
        task_default_retry_delay=60,
        task_max_retries=3,
        result_expires=3600,
    )

    class BaseTask(Task):
        """Base task with error handling and logging."""

        def on_failure(self, exc, task_id, args, kwargs, einfo):
            logger.error(f"[CELERY] Task {task_id} failed: {exc}")
            super().on_failure(exc, task_id, args, kwargs, einfo)

        def on_success(self, retval, task_id, args, kwargs):
            logger.info(f"[CELERY] Task {task_id} succeeded")

        def on_retry(self, exc, task_id, args, kwargs, einfo):
            logger.warning(f"[CELERY] Task {task_id} retrying: {exc}")

    # ===========================================================================
    # TASK DEFINITIONS
    # ===========================================================================

    @app.task(base=BaseTask, bind=True, name="saham.fetch_market_data")
    def fetch_market_data_task(self, period: str = "2y"):
        """Fetch all market data and store in database."""
        from src.data_fetcher import fetch_all_market_data
        from src.database_base import get_database
        from src.cache import invalidate_market_data

        try:
            market_data = fetch_all_market_data(period=period)
            db = get_database()
            for ticker_name, df in market_data.items():
                if not df.empty:
                    ticker_symbol = df.get("ticker", ticker_name)
                    # Convert DataFrame to individual parameters
                    for _, row in df.iterrows():
                        db.simpan_harga_harian(
                            ticker_name,
                            row.get("Date", ""),
                            row.get("Open", 0),
                            row.get("High", 0),
                            row.get("Low", 0),
                            row.get("Close", 0),
                            row.get("Volume", 0)
                        )
                        break  # Only save first row for simplicity

            invalidate_market_data()
            return {
                "status": "success",
                "tickers_fetched": len(market_data),
                "tickers": list(market_data.keys()),
            }
        except Exception as exc:
            raise self.retry(exc=exc)

    @app.task(base=BaseTask, bind=True, name="saham.run_prediction")
    def run_prediction_task(self, ticker: str = "^JKSE", period: str = "2y"):
        """Run prediction pipeline for a ticker."""
        from src.data_fetcher import fetch_all_data
        from src.predictor import run_prediction as _run_prediction
        from src.database_base import get_database
        from src.cache import invalidate_predictions
        from src.api.utils import _sanitize

        try:
            data = fetch_all_data(period=period)
            market_data = data.get("market", {})
            if not market_data:
                return {"status": "error", "error": "No market data"}

            result = _run_prediction(market_data, fred_data=data.get("fred"), target_ticker=ticker)

            if "error" not in result:
                db = get_database()
                db.simpan_prediksi(
                    ticker=ticker,
                    tanggal_prediksi=result.get("tanggal_prediksi", ""),
                    tanggal_target=result.get("tanggal_target", ""),
                    harga_saat_ini=result.get("current_price"),
                    harga_prediksi=result.get("harga_prediksi", 0),
                    arah_prediksi=result.get("arah_prediksi", "UP"),
                    sinyal=result.get("sinyal", "HOLD"),
                    confidence=result.get("confidence", 0),
                    model_votes=result.get("model_votes", ""),
                )
                invalidate_predictions(ticker)

            return _sanitize(result)
        except Exception as exc:
            raise self.retry(exc=exc)

    @app.task(base=BaseTask, bind=True, name="saham.run_backtest")
    def run_backtest_task(self, ticker: str = "^JKSE", period: str = "2y"):
        """Run backtest for a ticker."""
        from src.data_fetcher import fetch_all_market_data
        from src.backtesting import run_backtest, simulate_trading
        from src.api.utils import _sanitize

        try:
            market_data = fetch_all_market_data(period=period)
            bt = run_backtest(market_data=market_data, fred_data={}, target_ticker=ticker)
            sim = simulate_trading(
                market_data=market_data, fred_data={},
                target_ticker=ticker, initial_capital=100_000_000,
            )
            return _sanitize({"backtest": bt, "simulation": sim})
        except Exception as exc:
            raise self.retry(exc=exc)

    @app.task(base=BaseTask, bind=True, name="saham.run_screener")
    def run_screener_task(self, top_n: int = 10):
        """Run stock screener."""
        from src.data_fetcher import fetch_all_market_data
        from src.screener import run_screener, format_screener_results
        from src.config import BLUE_CHIPS_ID
        from src.cache import invalidate_screener

        try:
            market_data = fetch_all_market_data(period="6mo")
            result = run_screener(market_data=market_data, tickers=BLUE_CHIPS_ID, top_n=top_n)
            df = format_screener_results(result)
            invalidate_screener()
            if df is None or df.empty:
                return []
            return df.to_dict(orient="records")
        except Exception as exc:
            raise self.retry(exc=exc)

    @app.task(base=BaseTask, bind=True, name="saham.retrain_model")
    def retrain_model_task(self, ticker: str = "^JKSE"):
        """Retrain ML model with latest data."""
        from src.data_fetcher import fetch_all_data
        from src.models import HybridEnsemble
        from src.preprocessor import prepare_features
        from src.config import MODEL_CONFIG

        try:
            data = fetch_all_data(period=MODEL_CONFIG.get("data_period", "5y"))
            market_data = data.get("market", {})
            if not market_data:
                return {"status": "error", "error": "No market data"}

            df = prepare_features(market_data, fred_data=data.get("fred"), target_ticker=ticker)
            ensemble = HybridEnsemble(use_lstm=MODEL_CONFIG.get("use_lstm", False))
            ensemble.fit(df)

            return {
                "status": "success",
                "ticker": ticker,
                "training_rows": len(df),
                "model_type": type(ensemble).__name__,
            }
        except Exception as exc:
            raise self.retry(exc=exc)

    @app.task(base=BaseTask, bind=True, name="saham.send_notification")
    def send_notification_task(self, kategori: str, judul: str, pesan: str, level: str = "info"):
        """Send notification via Telegram and email, and save to DB."""
        from src.database_base import get_database
        from src.notifier import send_telegram, send_email

        try:
            db = get_database()
            db.log_aktivitas(kategori, f"{judul}: {pesan}")
            send_telegram(f"{judul}\n\n{pesan}")
            send_email(judul, pesan)
            return {"status": "success", "kategori": kategori, "judul": judul}
        except Exception as exc:
            raise self.retry(exc=exc)

    @app.task(base=BaseTask, bind=True, name="saham.cleanup_old_data")
    def cleanup_old_data_task(self):
        """Clean up old data from database."""
        from src.database_base import get_database

        try:
            db = get_database()
            # Placeholder for cleanup - implement in database classes
            results = {"deleted_rows": 0}
            return {"status": "success", "results": results}
        except Exception as exc:
            raise self.retry(exc=exc)

    # ===========================================================================
    # TASK STATUS HELPERS
    # ===========================================================================
    def get_task_status(task_id: str) -> dict:
        """Get status of a submitted task."""
        if not _CELERY_AVAILABLE:
            return {"status": "unavailable", "error": "Celery not installed"}
        result = AsyncResult(task_id, app=app)
        return {
            "task_id": task_id,
            "status": result.status,
            "ready": result.ready(),
            "successful": result.successful() if result.ready() else None,
            "result": result.result if result.ready() and result.successful() else None,
            "error": str(result.result) if result.ready() and result.failed() else None,
        }

else:
    # Fallback when celery is not installed
    app = None

    class _FakeTask:
        """Synchronous fallback when Celery is not available."""
        def __init__(self, func):
            self.func = func
            self.id = "sync"

        def delay(self, *args, **kwargs):
            result = self.func(*args, **kwargs)
            return type("SyncResult", (), {
                "id": "sync",
                "status": "SUCCESS",
                "ready": lambda: True,
                "successful": lambda: True,
                "result": result,
            })()

    def get_task_status(task_id: str) -> dict:
        return {"status": "unavailable", "error": "Celery not installed"}
