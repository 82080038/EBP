"""
FastAPI REST API for Saham Prediction Application.

This is the main entry point that sets up the FastAPI app and includes all routers.
The actual endpoint implementations are split into router modules in src/api/routers/.

Endpoints:
- System: /api/v1/health, /api/v1/health/full, /api/v1/cache/*
- Prediction: /api/v1/predict/*
- Analysis: /api/v1/patterns/*, /api/v1/sentiment, /api/v1/briefing, /api/v1/score/*
- Frontend: /api/v1/stock/*, /api/v1/screener/*, /api/v1/market/*, /api/v1/macro/*
- Alerts: /api/v1/alerts/*
- And more...

Referensi:
- FastAPI official documentation
- Backend Engineer roadmap: REST API, OpenAPI docs, Pydantic models
"""

from fastapi import FastAPI, Depends, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.security import APIKeyHeader
from contextlib import asynccontextmanager
import os
import sys
import logging

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from src.api.middleware import RequestLoggingMiddleware, RateLimitMiddleware
from src.api.routers import system, prediction, analysis, frontend, frontend_ext, alerts, notifications, fraud, realtime, data

# =============================================================================
# LOGGING SETUP
# =============================================================================
_log_level = os.getenv("LOG_LEVEL", "INFO").upper()
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)-7s] %(name)s: %(message)s",
    datefmt="%H:%M:%S",
)
logger = logging.getLogger("saham.api")
logger.setLevel(_log_level)
logging.getLogger("uvicorn.access").setLevel(logging.WARNING)
for _noisy in ("matplotlib", "urllib3", "yfinance", "peewee", "PIL"):
    logging.getLogger(_noisy).setLevel(logging.WARNING)


# =============================================================================
# API KEY AUTHENTICATION
# =============================================================================
API_KEY_NAME = "X-API-Key"
api_key_header = APIKeyHeader(name=API_KEY_NAME, auto_error=False)

API_KEY = os.getenv("API_KEY", "dev_api_key_change_in_production")


async def get_api_key(api_key_header: str = Depends(api_key_header)):
    """Validate API key from header."""
    if api_key_header == API_KEY:
        return api_key_header
    
    # Allow health endpoint without auth
    return None


def require_auth(api_key: str = Depends(get_api_key)):
    """Require valid API key for protected endpoints."""
    if api_key is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid or missing API Key",
        )
    return api_key


@asynccontextmanager
async def lifespan(app: FastAPI):
    from src.database_base import get_database
    db = get_database()
    db.init_db()
    print("[OK] API started — database initialized")
    yield


app = FastAPI(
    title="Saham Prediction API",
    description="REST API untuk prediksi saham IHSG dengan ML ensemble, pattern recognition, dan multi-agent AI briefing.",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:3000", "http://127.0.0.1:3000", "http://localhost:3001", "http://127.0.0.1:3001"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.add_middleware(RequestLoggingMiddleware)
app.add_middleware(RateLimitMiddleware)


# =============================================================================
# GLOBAL EXCEPTION HANDLER
# =============================================================================
@app.exception_handler(Exception)
async def global_exception_handler(request, exc: Exception):
    """Catch all unhandled exceptions and log them with full traceback."""
    import traceback
    logger.error(
        f"UNHANDLED EXCEPTION on {request.method} {request.url.path}: "
        f"{type(exc).__name__}: {exc}\n{traceback.format_exc()}"
    )
    from fastapi.responses import JSONResponse
    return JSONResponse(
        status_code=500,
        content={
            "detail": str(exc),
            "type": type(exc).__name__,
            "path": str(request.url.path),
        },
    )


# =============================================================================
# INCLUDE ROUTERS
# =============================================================================
app.include_router(system.router)
app.include_router(prediction.router)
app.include_router(analysis.router)
app.include_router(frontend.router)
app.include_router(alerts.router)
app.include_router(notifications.router)
app.include_router(fraud.router)
app.include_router(realtime.router)
app.include_router(data.router)
app.include_router(frontend_ext.router)
