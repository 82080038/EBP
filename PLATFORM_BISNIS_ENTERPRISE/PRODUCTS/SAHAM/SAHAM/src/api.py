"""
FastAPI REST API for Saham Prediction Application.

Endpoints:
- GET  /api/v1/health              — Health check
- GET  /api/v1/health/full         — Full health check (DB, Redis, data source)
- GET  /api/v1/predict/{ticker}    — Get latest prediction
- POST /api/v1/predict             — Run new prediction
- GET  /api/v1/accuracy            — Get accuracy metrics
- GET  /api/v1/patterns/{ticker}   — Get pattern analysis
- GET  /api/v1/sentiment           — Get sentiment analysis
- GET  /api/v1/briefing            — Get daily AI briefing
- GET  /api/v1/score/{ticker}      — Get composite AI score
- GET  /api/v1/risk                — Get risk metrics
- POST /api/v1/alerts              — Create alert
- GET  /api/v1/alerts              — List alerts
- PUT  /api/v1/alerts/{id}/deactivate — Deactivate alert
- PUT  /api/v1/alerts/{id}/trigger    — Trigger alert
- DELETE /api/v1/alerts/{id}       — Delete alert
- GET  /api/v1/notifications       — List notifications
- PUT  /api/v1/notifications/{id}/read — Mark notification read
- GET  /api/v1/fraud/check/{ticker}    — Run 6-layer fraud detection
- GET  /api/v1/anti-manipulation/scan/{ticker} — Anti-manipulation scan
- POST /api/v1/fraud/fake-news-hype    — Check fake news hype
- GET  /api/v1/fundamental/{ticker}    — Get fundamental data
- GET  /api/v1/indicators/{ticker}     — Get technical indicators
- GET  /api/v1/financial-ratios/{ticker} — Get financial ratios

Referensi:
- FastAPI official documentation
- Backend Engineer roadmap: REST API, OpenAPI docs, Pydantic models
"""

from fastapi import FastAPI, HTTPException, Query, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from pydantic import BaseModel, Field
from typing import Dict, List, Optional, Any
from datetime import datetime, timedelta
from contextlib import asynccontextmanager
from starlette.middleware.base import BaseHTTPMiddleware
import os
import sys
import math
import random
import logging
import time as _time
import traceback

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from src.config import TICKERS, TARGET_TICKER, FRED_SERIES
from src.cache import cache_get, cache_set, TTL

# =============================================================================
# LOGGING SETUP
# =============================================================================
# Root stays at INFO so third-party libraries (matplotlib, urllib3, etc.) don't
# flood the console with DEBUG output. Set LOG_LEVEL=DEBUG to get verbose app logs.
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


@asynccontextmanager
async def lifespan(app: FastAPI):
    from src.database import init_db
    init_db()
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


# =============================================================================
# REQUEST/RESPONSE LOGGING MIDDLEWARE
# =============================================================================
class RequestLoggingMiddleware(BaseHTTPMiddleware):
    """Log every request with method, path, status, duration, and errors."""

    async def dispatch(self, request: Request, call_next):
        start = _time.monotonic()
        method = request.method
        path = request.url.path
        query = str(request.url.query) if request.url.query else ""
        req_id = f"{method} {path}"

        # Skip health check noise
        if path == "/api/v1/health":
            return await call_next(request)

        logger.info(f"→ {req_id} ?{query}" if query else f"→ {req_id}")

        try:
            response = await call_next(request)
            duration_ms = (_time.monotonic() - start) * 1000

            if response.status_code >= 400:
                logger.warning(f"← {req_id} [{response.status_code}] {duration_ms:.0f}ms")
            elif duration_ms > 5000:
                logger.info(f"← {req_id} [200] {duration_ms:.0f}ms (SLOW)")
            else:
                logger.info(f"← {req_id} [{response.status_code}] {duration_ms:.0f}ms")

            return response

        except Exception as exc:
            duration_ms = (_time.monotonic() - start) * 1000
            logger.error(
                f"✖ {req_id} CRASHED after {duration_ms:.0f}ms: {exc}\n"
                f"{traceback.format_exc()}"
            )
            return JSONResponse(
                status_code=500,
                content={"detail": str(exc), "type": type(exc).__name__},
            )


app.add_middleware(RequestLoggingMiddleware)


class RateLimitMiddleware(BaseHTTPMiddleware):
    """Global rate limiting: 60 requests per minute per IP, health endpoints exempt."""

    async def dispatch(self, request: Request, call_next):
        path = request.url.path
        if path not in ("/health", "/api/v1/health", "/api/v1/health/full"):
            client_ip = request.client.host if request.client else "unknown"
            now = _time.time()
            _rate_limit_store.setdefault(client_ip, {})
            _rate_limit_store[client_ip].setdefault(path, [])
            window_start = now - 60
            _rate_limit_store[client_ip][path] = [
                t for t in _rate_limit_store[client_ip][path] if t > window_start
            ]
            if len(_rate_limit_store[client_ip][path]) >= 60:
                logger.warning(f"[RATE LIMIT] {client_ip} {path} exceeded 60/60s")
                return JSONResponse(status_code=429, content={"detail": "Rate limit exceeded."})
            _rate_limit_store[client_ip][path].append(now)
        return await call_next(request)


app.add_middleware(RateLimitMiddleware)


@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception):
    """Catch all unhandled exceptions and log them with full traceback."""
    logger.error(
        f"UNHANDLED EXCEPTION on {request.method} {request.url.path}: "
        f"{type(exc).__name__}: {exc}\n{traceback.format_exc()}"
    )
    return JSONResponse(
        status_code=500,
        content={
            "detail": str(exc),
            "type": type(exc).__name__,
            "path": str(request.url.path),
        },
    )


# =============================================================================
# RATE LIMITING & ENDPOINT CACHING (in-memory fallback if Redis unavailable)
# =============================================================================

# Simple per-client rate limiter: {client_ip -> {endpoint -> [(timestamp, ...)}}
_rate_limit_store: Dict[str, Dict[str, List[float]]] = {}


def _rate_limit_dependency(requests: int = 30, window: int = 60):
    """Factory for FastAPI dependency that limits requests per client IP."""
    async def _check(request: Request):
        client_ip = request.client.host if request.client else "unknown"
        path = request.url.path
        now = _time.time()

        _rate_limit_store.setdefault(client_ip, {})
        _rate_limit_store[client_ip].setdefault(path, [])

        # Keep only timestamps within the window
        window_start = now - window
        _rate_limit_store[client_ip][path] = [
            t for t in _rate_limit_store[client_ip][path] if t > window_start
        ]

        if len(_rate_limit_store[client_ip][path]) >= requests:
            logger.warning(f"[RATE LIMIT] {client_ip} {path} exceeded {requests}/{window}s")
            raise HTTPException(status_code=429, detail="Rate limit exceeded. Please slow down.")

        _rate_limit_store[client_ip][path].append(now)

    return _check


def _endpoint_cache_key(request: Request) -> str:
    """Build a cache key from request path + query string."""
    query = str(request.url.query)
    return f"api:{request.url.path}:{hash(query)}"


async def _cached_response(request: Request, ttl: int = TTL.MEDIUM):
    """Try to return a cached JSON response for the request. Returns None if miss."""
    key = _endpoint_cache_key(request)
    cached = cache_get(key)
    if cached is not None:
        return JSONResponse(content=cached)
    return None


def _cache_response(request: Request, data: Any, ttl: int = TTL.MEDIUM) -> None:
    """Store a JSON-serializable response in cache."""
    key = _endpoint_cache_key(request)
    cache_set(key, data, ttl=ttl)


# =============================================================================
# PYDANTIC MODELS
# =============================================================================

class PredictionResponse(BaseModel):
    ticker: str
    current_price: float
    predicted_price: float
    direction: str = Field(..., description="UP or DOWN")
    signal: str = Field(..., description="BUY, SELL, or HOLD")
    confidence: float
    model_votes: Dict[str, str]
    rules: str
    ai_score: Optional[int] = None
    composite_score: Optional[float] = None
    timestamp: str


class BacktestResponse(BaseModel):
    total_return_pct: float
    buy_hold_return_pct: float
    n_trades: int
    win_rate: float
    max_drawdown_pct: float
    sharpe_ratio: float
    sortino_ratio: float
    calmar_ratio: float
    profit_factor: float


class AccuracyResponse(BaseModel):
    total: int
    benar: int
    salah: int
    directional_accuracy: float
    mape: Optional[float] = None


class PatternResponse(BaseModel):
    candlestick_patterns: List[Dict[str, Any]]
    chart_patterns: List[Dict[str, Any]]
    market_structure: Dict[str, Any]
    volume_anomalies: List[Dict[str, Any]]
    trendlines: List[Dict[str, Any]]
    summary: str


class SentimentResponse(BaseModel):
    composite_score: float
    label: str
    emoji: Optional[str] = None
    advice: Optional[str] = None
    components: Dict[str, Any]
    news_sentiment: Optional[Dict[str, float]] = None


class BriefingResponse(BaseModel):
    date: str
    market_summary: str
    signal: str
    confidence: float
    final_recommendation: str
    actionable_items: List[str]
    risk_assessment: str
    bull_case: Optional[str] = None
    bear_case: Optional[str] = None
    debate: Optional[Dict[str, Any]] = None


class ScoreResponse(BaseModel):
    ai_score: int = Field(..., ge=1, le=10)
    composite_score: float
    technical_rating: float
    sentiment_rating: float
    momentum_rating: float
    risk_rating: float
    signal_strength: str


class HealthResponse(BaseModel):
    status: str
    version: str
    timestamp: str


class FullHealthResponse(BaseModel):
    status: str
    version: str
    timestamp: str
    database: str
    redis: str
    data_source: str
    data_validation: str


class AlertCreateRequest(BaseModel):
    ticker: str
    alert_type: str = Field(..., description="price_above, price_below, volume_spike, etc.")
    condition_value: Optional[float] = None
    condition_text: str = ""
    message: str = ""


class AlertResponse(BaseModel):
    id: int
    ticker: str
    alert_type: str
    condition_value: Optional[float] = None
    condition_text: str = ""
    is_active: int
    is_triggered: int
    triggered_at: Optional[str] = None
    message: str = ""
    created_at: Optional[str] = None


# =============================================================================
# ENDPOINTS
# =============================================================================

@app.get("/health", response_model=HealthResponse, tags=["System"])
@app.get("/api/v1/health", response_model=HealthResponse, tags=["System"])
async def health_check():
    """Health check endpoint."""
    return HealthResponse(
        status="healthy",
        version="1.0.0",
        timestamp=datetime.now().isoformat(),
    )


@app.get("/api/v1/health/full", response_model=FullHealthResponse, tags=["System"])
async def health_check_full():
    """Full health check including database, Redis, data source, and validation."""
    from src.database import init_db, get_connection
    from src.data_fetcher import fetch_yfinance_data
    from src.data_validation import validate_ticker_symbol
    from src.cache import cache_get, _CACHE_ENABLED

    checks = {
        "database": "unknown",
        "redis": "unknown",
        "data_source": "unknown",
        "data_validation": "unknown",
    }

    # Database check
    try:
        init_db()
        conn = get_connection()
        conn.execute("SELECT 1")
        conn.close()
        checks["database"] = "healthy"
    except Exception as e:
        logger.error(f"[HEALTH] database check failed: {e}")
        checks["database"] = f"unhealthy: {e}"

    # Redis check
    try:
        if _CACHE_ENABLED:
            cache_get("__health_check__")
        checks["redis"] = "healthy" if _CACHE_ENABLED else "disabled"
    except Exception as e:
        logger.error(f"[HEALTH] redis check failed: {e}")
        checks["redis"] = f"unhealthy: {e}"

    # Data source + validation check
    try:
        if not validate_ticker_symbol("^JKSE"):
            checks["data_validation"] = "unhealthy: invalid ticker validation"
        else:
            df = fetch_yfinance_data("^JKSE", period="5d", interval="1d")
            if df is not None and not df.empty:
                checks["data_source"] = "healthy"
                checks["data_validation"] = "healthy"
            else:
                checks["data_source"] = "unhealthy: empty response"
                checks["data_validation"] = "unhealthy: no data to validate"
    except Exception as e:
        logger.error(f"[HEALTH] data source check failed: {e}")
        checks["data_source"] = f"unhealthy: {e}"
        checks["data_validation"] = f"unhealthy: {e}"

    overall = "healthy" if all(v == "healthy" or v == "disabled" for v in checks.values()) else "degraded"

    return FullHealthResponse(
        status=overall,
        version="1.0.0",
        timestamp=datetime.now().isoformat(),
        database=checks["database"],
        redis=checks["redis"],
        data_source=checks["data_source"],
        data_validation=checks["data_validation"],
    )


@app.get("/api/v1/predict/{ticker}", response_model=PredictionResponse, tags=["Prediction"])
async def get_prediction(request: Request, ticker: str = "IHSG"):
    """
    Get latest prediction for a ticker.
    Uses cached prediction from database if available, plus short-term API cache.
    """
    # Try API cache first
    cached = await _cached_response(request, ttl=TTL.SHORT)
    if cached is not None:
        return cached

    from src.database import get_all_prediksi

    target = TICKERS.get(ticker, ticker)
    df = get_all_prediksi()

    if df.empty:
        raise HTTPException(status_code=404, detail=f"No predictions found for {ticker}")

    latest = df.iloc[-1]
    response_data = {
        "ticker": target,
        "current_price": float(latest.get("harga_saat_ini", 0)),
        "predicted_price": float(latest.get("harga_prediksi", 0)),
        "direction": latest.get("arah_prediksi", "UNKNOWN"),
        "signal": latest.get("sinyal", "HOLD"),
        "confidence": float(latest.get("confidence", 0)),
        "model_votes": {},
        "rules": latest.get("model_votes", ""),
        "timestamp": str(latest.get("tanggal_prediksi", "")),
    }
    _cache_response(request, response_data, ttl=TTL.SHORT)
    return PredictionResponse(**response_data)


@app.post("/api/v1/predict", tags=["Prediction"])
async def run_prediction(
    ticker: str = Query(default="IHSG", description="Ticker symbol"),
    period: str = Query(default="2y", description="Data period"),
):
    """Run a new prediction."""
    logger.info(f"[PREDICT] ticker={ticker}, period={period}")
    from src.data_fetcher import fetch_all_data
    from src.predictor import run_prediction as _run_prediction

    target = TICKERS.get(ticker, TARGET_TICKER)
    logger.debug(f"[PREDICT] resolved target={target}")

    data = fetch_all_data(period=period)
    market_data = data.get("market", {})

    if not market_data:
        logger.error(f"[PREDICT] No market data returned for ticker={ticker}")
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    logger.debug(f"[PREDICT] market_data keys={list(market_data.keys())[:10]}, fred={'present' if data.get('fred') else 'missing'}")

    try:
        result = _run_prediction(market_data, fred_data=data.get("fred"), target_ticker=target)
    except Exception as e:
        logger.error(f"[PREDICT] run_prediction failed: {e}\n{traceback.format_exc()}")
        raise HTTPException(status_code=500, detail=f"Prediction engine error: {e}")

    if "error" in result:
        logger.error(f"[PREDICT] result has error: {result['error']}")
        raise HTTPException(status_code=500, detail=result["error"])

    logger.info(f"[PREDICT] OK signal={result['sinyal']}, confidence={result['confidence']:.2f}, price={result['current_price']}")
    return _sanitize(result)


@app.get("/api/v1/accuracy", response_model=AccuracyResponse, tags=["Metrics"])
async def get_accuracy():
    """Get prediction accuracy metrics."""
    from src.database import get_akurasi_metrics

    metrics = get_akurasi_metrics()
    return AccuracyResponse(
        total=metrics.get("total", 0),
        benar=metrics.get("benar", 0),
        salah=metrics.get("salah", 0),
        directional_accuracy=metrics.get("directional_accuracy", 0),
        mape=metrics.get("mape"),
    )


@app.get("/api/v1/patterns/{ticker}", response_model=PatternResponse, tags=["Analysis"])
async def get_patterns(ticker: str = "IHSG", period: str = "6mo"):
    """Get pattern analysis for a ticker."""
    from src.data_fetcher import fetch_all_market_data
    from src.patterns import full_pattern_analysis

    target = TICKERS.get(ticker, TARGET_TICKER)
    market_data = fetch_all_market_data(period=period)

    if not market_data or (ticker not in market_data and target not in market_data):
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    df = market_data.get(ticker, market_data.get(target))
    result = full_pattern_analysis(df)

    return PatternResponse(
        candlestick_patterns=[{"name": p.name, "type": p.type, "confidence": p.confidence, "date": p.date} for p in result["candlestick_patterns"]],
        chart_patterns=[{"name": p.name, "type": p.type, "confidence": p.confidence, "description": p.description} for p in result["chart_patterns"]],
        market_structure={"structure": result["market_structure"].structure, "description": result["market_structure"].description},
        volume_anomalies=[{"date": a.date, "type": a.anomaly_type, "z_score": a.z_score} for a in result["volume_anomalies"]],
        trendlines=[{"type": t.type, "slope": t.slope, "touches": t.touches} for t in result["trendlines"]],
        summary=result["summary"],
    )


@app.get("/api/v1/sentiment", response_model=SentimentResponse, tags=["Analysis"])
async def get_sentiment():
    """Get Fear & Greed sentiment analysis."""
    from src.data_fetcher import fetch_all_market_data
    from src.sentiment import calc_fear_greed_index

    market_data = fetch_all_market_data(period="6mo")
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    fg = calc_fear_greed_index(market_data)
    return SentimentResponse(
        composite_score=fg["composite_score"],
        label=fg["label"],
        emoji=fg.get("emoji"),
        advice=fg.get("advice"),
        components=fg["components"],
    )


@app.get("/api/v1/briefing", response_model=BriefingResponse, tags=["AI Agent"])
async def get_briefing():
    """Get daily AI multi-agent briefing."""
    from src.data_fetcher import fetch_all_data
    from src.ai_agent import generate_daily_briefing
    from src.predictor import run_prediction

    data = fetch_all_data(period="2y")
    market_data = data.get("market", {})
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    try:
        result = run_prediction(market_data, fred_data=data.get("fred"))
        signal = result.get("sinyal", "HOLD")
        confidence = result.get("confidence", 0.5)
        predictions = result.get("predictions", {})
        probabilities = result.get("probabilities", {})
    except Exception:
        signal = "HOLD"
        confidence = 0.5
        predictions = {}
        probabilities = {}

    from src.preprocessor import prepare_features
    df = prepare_features(market_data, fred_data=data.get("fred"))

    briefing = generate_daily_briefing(
        market_data, df, signal, confidence, predictions, probabilities
    )

    agents = briefing.get("agents", [])
    bull_case = next((a.findings for a in agents if "bull" in a.agent_name.lower() or signal == "BUY"), None)
    bear_case = next((a.findings for a in agents if "bear" in a.agent_name.lower() or signal == "SELL"), None)

    return BriefingResponse(
        date=briefing["date"],
        market_summary=briefing["market_summary"],
        signal=briefing["signal"],
        confidence=briefing["confidence"],
        final_recommendation=briefing["final_recommendation"].findings,
        actionable_items=briefing["actionable_items"],
        risk_assessment=briefing["risk_assessment"],
        bull_case=bull_case,
        bear_case=bear_case,
        debate={a.agent_name: a.findings for a in agents} if agents else None,
    )


@app.get("/api/v1/score/{ticker}", response_model=ScoreResponse, tags=["Analysis"])
async def get_score(ticker: str = "IHSG"):
    """Get composite AI score for a ticker."""
    from src.data_fetcher import fetch_all_data
    from src.scoring import calc_composite_ai_score
    from src.predictor import run_prediction

    target = TICKERS.get(ticker, TARGET_TICKER)
    data = fetch_all_data(period="2y")
    market_data = data.get("market", {})

    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    try:
        result = run_prediction(market_data, fred_data=data.get("fred"), target_ticker=target)
        predictions = result.get("predictions", {})
        probabilities = result.get("probabilities", {})
        confidence = result.get("confidence", 0.5)
    except Exception:
        predictions = {}
        probabilities = {}
        confidence = 0.5

    from src.preprocessor import prepare_features
    df = prepare_features(market_data, fred_data=data.get("fred"), target_ticker=target)
    latest = df.iloc[-1]

    rsi = float(latest.get("Target_RSI", 50))
    vix = float(latest.get("VIX_Close", 20))
    fear_greed = float(latest.get("Fear_Greed_Index", 50))

    ma5 = float(latest.get("IHSG_MA5", 0))
    ma10 = float(latest.get("IHSG_MA10", 0))
    ma20 = float(latest.get("IHSG_MA20", 0))
    ma_alignment = 1.0 if ma5 > ma10 > ma20 else (-1.0 if ma5 < ma10 < ma20 else 0.0)

    macd = float(latest.get("Target_MACD", 0))
    macd_signal = float(latest.get("Target_MACD_Signal", 0))
    macd_val = 1.0 if macd > macd_signal else (-1.0 if macd < macd_signal else 0.0)

    bb_pct = float(latest.get("Target_BB_Pct", 0.5))

    score = calc_composite_ai_score(
        predictions=predictions,
        probabilities=probabilities,
        confidence=confidence,
        rsi=rsi,
        trend_bullish=ma5 > ma10 > ma20,
        trend_bearish=ma5 < ma10 < ma20,
        vix=vix,
        fear_greed=fear_greed,
        ma_alignment=ma_alignment,
        macd_signal=macd_val,
        bb_position=bb_pct,
    )

    return ScoreResponse(
        ai_score=score["ai_score"],
        composite_score=score["composite_score"],
        technical_rating=score["technical_rating"],
        sentiment_rating=score["sentiment_rating"],
        momentum_rating=score["momentum_rating"],
        risk_rating=score["risk_rating"],
        signal_strength=score["signal_strength"],
    )


# =============================================================================
# FRONTEND ENDPOINTS (for Next.js Command Center)
# =============================================================================

@app.get("/api/v1/stock/{ticker}/ohlcv", tags=["Frontend"])
async def get_ohlcv(ticker: str, period: str = "1y"):
    """Get OHLCV data for charting."""
    from src.data_fetcher import fetch_yfinance_data
    df = fetch_yfinance_data(ticker, period=period, interval="1d")
    if df is None or df.empty:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")
    rows = []
    for idx, row in df.iterrows():
        rows.append({
            "date": idx.strftime("%Y-%m-%d") if hasattr(idx, "strftime") else str(idx),
            "open": float(row.get("Open", 0)),
            "high": float(row.get("High", 0)),
            "low": float(row.get("Low", 0)),
            "close": float(row.get("Close", 0)),
            "volume": float(row.get("Volume", 0)),
        })
    return rows


@app.get("/api/v1/screener/watchlist", tags=["Frontend"])
async def get_watchlist():
    """Get screener results for watchlist."""
    from src.data_fetcher import fetch_all_market_data
    from src.screener import run_screener, format_screener_results
    from src.config import BLUE_CHIPS_ID

    market_data = fetch_all_market_data(period="6mo")
    result = run_screener(market_data=market_data, tickers=BLUE_CHIPS_ID, top_n=10)
    df = format_screener_results(result)
    if df is None or df.empty:
        return []
    # Map column names to match frontend TypeScript types
    df = df.rename(columns={
        "Ticker": "ticker",
        "Name": "name",
        "Price": "price",
        "Change %": "change_pct",
        "RSI": "rsi",
        "MA Trend": "ma_trend",
        "Signal": "signal",
        "AI Score": "ai_score",
        "Confidence": "confidence",
    })
    return df.to_dict(orient="records")


@app.get("/api/v1/stock/{ticker}/orderbook", tags=["Frontend"])
async def get_orderbook(ticker: str):
    """Get simulated order book for ticker."""
    from src.data_fetcher import fetch_yfinance_data
    df = fetch_yfinance_data(ticker, period="1mo", interval="1d")
    if df is None or df.empty:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    last_price = float(df["Close"].iloc[-1])
    if len(df) >= 20:
        atr = float((df["High"].iloc[-20:] - df["Low"].iloc[-20:]).mean())
    else:
        atr = last_price * 0.015

    spread = max(atr * 0.05, last_price * 0.0005)
    levels = []
    for i in range(8):
        bid_price = last_price - spread * (i + 1)
        ask_price = last_price + spread * (i + 1)
        bid_qty = int(random.lognormvariate(8, 1.2) * (1 + i * 0.3))
        ask_qty = int(random.lognormvariate(8, 1.2) * (1 + i * 0.3))
        levels.append({
            "level": i + 1,
            "bid_qty": bid_qty,
            "bid": round(bid_price, 2),
            "ask": round(ask_price, 2),
            "ask_qty": ask_qty,
            "spread_pct": round(((ask_price - bid_price) / last_price) * 100, 3),
        })
    return levels


@app.post("/api/v1/predict/{ticker}", tags=["Frontend"])
async def run_prediction_for_ticker(ticker: str):
    """Run full prediction pipeline for a specific ticker."""
    from src.data_fetcher import fetch_all_market_data, fetch_yfinance_data
    from src.predictor import run_prediction as _run_prediction

    market_data = fetch_all_market_data(period="2y")
    md = dict(market_data)

    target_name = _resolve_ticker(ticker, default="TARGET")
    if target_name == "TARGET" and (target_name not in md or md[target_name].empty):
        df = fetch_yfinance_data(ticker, period="1y", interval="1d")
        if not df.empty:
            md[target_name] = df

    if not md:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    result = _run_prediction(market_data=md, target_ticker=ticker)

    if "error" in result:
        raise HTTPException(status_code=500, detail=result["error"])

    return _sanitize(result)


@app.get("/api/v1/market/summary", tags=["Frontend"])
async def get_market_summary(request: Request):
    """Get global index summary (cached for 1 minute)."""
    cached = await _cached_response(request, ttl=TTL.SHORT)
    if cached is not None:
        return cached

    from src.data_fetcher import fetch_all_market_data
    market_data = fetch_all_market_data(period="1mo")

    index_names = ["IHSG", "S&P500", "NASDAQ", "DOW", "NIKKEI", "HANG_SENG", "STI"]
    result = []
    for name in index_names:
        if name in market_data and not market_data[name].empty:
            df = market_data[name]
            cur = float(df["Close"].iloc[-1])
            prev = float(df["Close"].iloc[-2]) if len(df) > 1 else cur
            pct = ((cur - prev) / prev) * 100 if prev > 0 else 0
            result.append({"name": name, "price": cur, "change_pct": round(pct, 2)})
    _cache_response(request, result, ttl=TTL.SHORT)
    return result


@app.get("/api/v1/macro/indicators", tags=["Frontend"])
async def get_macro_indicators():
    """Get FRED macro indicators."""
    from src.data_fetcher import fetch_fred_data
    result = []
    for series_name, series_id in FRED_SERIES.items():
        try:
            s = fetch_fred_data(series_id, observation_start=(datetime.now() - timedelta(days=90)).strftime("%Y-%m-%d"))
            if not s.empty:
                last_val = float(s.iloc[-1, 0])
                date = s.index[-1].strftime("%Y-%m-%d") if hasattr(s.index[-1], "strftime") else str(s.index[-1])
                result.append({"name": series_name, "value": last_val, "date": date})
        except Exception:
            pass
    return result


class SimOrderRequest(BaseModel):
    symbol: str
    side: str = "BUY"
    quantity: int = 100


# Module-level singleton so portfolio state persists across API calls
_broker_instance = None


def _get_broker():
    global _broker_instance
    if _broker_instance is None:
        from src.broker_sim import BrokerSimulator
        _broker_instance = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
    return _broker_instance


@app.post("/api/v1/sim/order", tags=["Frontend"])
async def submit_sim_order(req: SimOrderRequest):
    """Submit a simulated order via BrokerSimulator."""
    logger.info(f"[SIM ORDER] {req.side} {req.quantity} {req.symbol}")
    from src.broker_sim import SimOrder
    from src.data_fetcher import fetch_yfinance_data

    df = fetch_yfinance_data(req.symbol, period="1mo", interval="1d")
    if df is None or df.empty:
        logger.error(f"[SIM ORDER] No price data for {req.symbol}")
        raise HTTPException(status_code=404, detail=f"No price data for {req.symbol}")
    current_price = float(df["Close"].iloc[-1])
    logger.debug(f"[SIM ORDER] current_price={current_price}")

    broker = _get_broker()
    order = SimOrder(symbol=req.symbol, side=req.side, quantity=req.quantity, order_type="MARKET")
    result = broker.submit_order(order, current_price=current_price)

    logger.info(f"[SIM ORDER] OK id={result.order_id}, status={result.status}, fill={result.avg_fill_price}, cost={result.total_cost}")

    return {
        "order_id": result.order_id,
        "status": result.status,
        "filled_qty": result.filled_qty,
        "avg_fill_price": result.avg_fill_price,
        "commission": result.commission,
        "fees": result.fees,
        "total_cost": result.total_cost,
        "slippage_bps": result.slippage_bps,
        "latency_ms": result.latency_ms,
    }


@app.get("/api/v1/sim/portfolio", tags=["Frontend"])
async def get_sim_portfolio():
    """Get simulated portfolio positions."""
    from src.data_fetcher import fetch_yfinance_data

    broker = _get_broker()
    portfolio = broker.get_portfolio()
    positions = portfolio.get("positions", [])

    # Update market prices for current positions
    symbols = [p.get("symbol") for p in positions if p.get("symbol")]
    if symbols:
        prices = {}
        for sym in symbols:
            try:
                df = fetch_yfinance_data(sym, period="1mo", interval="1d")
                if df is not None and not df.empty:
                    prices[sym] = float(df["Close"].iloc[-1])
            except Exception:
                pass
        if prices:
            broker.update_market_prices(prices)
            portfolio = broker.get_portfolio()
            positions = portfolio.get("positions", [])

    result = []
    for pos in positions:
        result.append({
            "symbol": pos.get("symbol", ""),
            "quantity": pos.get("quantity", 0),
            "avg_price": pos.get("avg_price", 0),
            "market_price": pos.get("market_price", 0),
            "unrealized_pnl": pos.get("unrealized_pnl", 0),
        })
    return result


# =============================================================================
# ADDITIONAL FRONTEND ENDPOINTS (Full Feature Coverage)
# =============================================================================

def _resolve_ticker(ticker: str, default: str = "IHSG") -> str:
    """Resolve a ticker symbol to its market data key name.
    
    Looks up the ticker in TICKERS dict. If not found, returns the default.
    Used by risk, regime, patterns, model details, and prediction endpoints.
    """
    for name, t in TICKERS.items():
        if t == ticker:
            return name
    return default


def _sanitize(obj):
    """Recursively convert numpy/pandas types to native Python for JSON."""
    import numpy as np
    import pandas as pd
    from dataclasses import asdict, is_dataclass
    if is_dataclass(obj) and not isinstance(obj, type):
        return _sanitize(asdict(obj))
    if isinstance(obj, pd.DataFrame):
        return _sanitize(obj.to_dict(orient="records"))
    if isinstance(obj, pd.Series):
        return _sanitize(obj.to_dict())
    if isinstance(obj, dict):
        return {k: _sanitize(v) for k, v in obj.items()}
    elif isinstance(obj, (list, tuple)):
        return [_sanitize(v) for v in obj]
    elif isinstance(obj, (np.bool_, bool)):
        return bool(obj)
    elif isinstance(obj, (np.integer,)):
        return int(obj)
    elif isinstance(obj, (np.floating,)):
        val = float(obj)
        return val if math.isfinite(val) else None
    elif isinstance(obj, float):
        return obj if math.isfinite(obj) else None
    elif isinstance(obj, np.ndarray):
        return _sanitize(obj.tolist())
    elif obj is None or isinstance(obj, (str, int, float)):
        return obj
    else:
        return str(obj)


@app.get("/api/v1/backtest/{ticker}", tags=["Frontend"])
async def run_backtest(ticker: str, period: str = "2y"):
    """Run backtest and trading simulation for a ticker."""
    logger.info(f"[BACKTEST] ticker={ticker}, period={period}")
    from src.data_fetcher import fetch_all_market_data
    from src.backtesting import run_backtest as _run_bt, simulate_trading

    market_data = fetch_all_market_data(period=period)
    if not market_data:
        logger.error(f"[BACKTEST] No market data for {ticker}")
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    logger.debug(f"[BACKTEST] market_data keys={list(market_data.keys())[:10]}")
    try:
        bt = _run_bt(market_data=market_data, fred_data={}, target_ticker=ticker)
        sim = simulate_trading(market_data=market_data, fred_data={}, target_ticker=ticker, initial_capital=100_000_000)
    except Exception as e:
        logger.error(f"[BACKTEST] failed: {e}\n{traceback.format_exc()}")
        raise HTTPException(status_code=500, detail=f"Backtest error: {e}")

    # Map run_backtest results to frontend BacktestResult type
    if "error" in bt:
        raise HTTPException(status_code=500, detail=bt["error"])
    ensemble_bt = bt.get("Ensemble", {})
    directional_acc = ensemble_bt.get("directional_accuracy", 0) if isinstance(ensemble_bt, dict) else 0
    total_preds = sum(1 for k, v in bt.items() if isinstance(v, dict) and "directional_accuracy" in v)
    mape_val = bt.get("MAPE")

    bt_mapped = {
        "directional_accuracy": directional_acc,
        "total_predictions": total_preds,
        "MAPE": mape_val,
    }

    if "error" in sim:
        sim = {"initial_capital": 100_000_000, "final_capital": 100_000_000, "total_return": 0, "buy_hold_return": 0, "trades": 0, "max_drawdown": 0}

    logger.info(f"[BACKTEST] OK da={directional_acc}%, return={sim.get('total_return', '?')}%, trades={sim.get('trades', '?')}")
    return _sanitize({"backtest": bt_mapped, "simulation": sim})


@app.get("/api/v1/risk/{ticker}", tags=["Frontend"])
async def get_risk_metrics(ticker: str, period: str = "2y", position_value: float = 100_000_000):
    """Get risk metrics: VaR, CVaR, Sharpe, Sortino, Kelly, drawdown."""
    logger.info(f"[RISK] ticker={ticker}, period={period}, pos_val={position_value}")
    from src.data_fetcher import fetch_all_market_data
    from src.risk_manager import calc_var, calc_risk_metrics

    market_data = fetch_all_market_data(period=period)
    target_name = _resolve_ticker(ticker)

    if target_name not in market_data:
        logger.error(f"[RISK] {ticker} not in market_data (keys={list(market_data.keys())[:10]})")
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    df = market_data[target_name]
    returns = df["Close"].pct_change().dropna()
    prices = df["Close"]
    logger.debug(f"[RISK] {len(returns)} returns, last_price={prices.iloc[-1]:.2f}")
    risk = calc_risk_metrics(returns, prices, position_value)
    var = calc_var(returns, 0.95, position_value)
    logger.info(f"[RISK] OK sharpe={risk.get('sharpe', {}).get('annualized_sharpe', '?')}, max_dd={risk.get('drawdown', {}).get('max_drawdown_pct', '?')}%")
    return _sanitize({"risk": risk, "var": var})


@app.get("/api/v1/portfolio/optimize", tags=["Frontend"])
async def get_portfolio_optimization(period: str = "2y", n_sim: int = 3000, risk_free: float = 0.05):
    """Run portfolio optimization with efficient frontier."""
    logger.info(f"[PORTFOLIO OPT] period={period}, n_sim={n_sim}, risk_free={risk_free}")
    from src.data_fetcher import fetch_all_market_data
    from src.portfolio import optimize_portfolio
    import pandas as pd

    market_data = fetch_all_market_data(period=period)
    if not market_data:
        logger.error("[PORTFOLIO OPT] No market data")
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    returns_dict = {}
    for name, df in market_data.items():
        if not df.empty and len(df) > 30:
            returns_dict[name] = df["Close"].pct_change().dropna()

    if len(returns_dict) < 2:
        logger.error(f"[PORTFOLIO OPT] Only {len(returns_dict)} assets, need >=2")
        raise HTTPException(status_code=400, detail="Not enough assets for optimization")

    returns_df = pd.DataFrame(returns_dict).dropna()
    logger.debug(f"[PORTFOLIO OPT] {len(returns_dict)} assets, {len(returns_df)} rows")
    try:
        opt = optimize_portfolio(returns_df, risk_free=risk_free, n_portfolios=n_sim)
    except Exception as e:
        logger.error(f"[PORTFOLIO OPT] failed: {e}\n{traceback.format_exc()}")
        raise HTTPException(status_code=500, detail=f"Optimization error: {e}")
    logger.info(f"[PORTFOLIO OPT] OK max_sharpe={opt.get('max_sharpe_portfolio', {}).get('sharpe', '?')}")
    return _sanitize(opt)


@app.get("/api/v1/regime/{ticker}", tags=["Frontend"])
async def get_regime(ticker: str, period: str = "2y"):
    """Get market regime detection for a ticker."""
    logger.info(f"[REGIME] ticker={ticker}, period={period}")
    from src.data_fetcher import fetch_all_market_data
    from src.regime import detect_market_regime

    market_data = fetch_all_market_data(period=period)
    target_name = _resolve_ticker(ticker)

    if target_name not in market_data:
        logger.error(f"[REGIME] {ticker} not in market_data")
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    df = market_data[target_name]
    result = detect_market_regime(df, close_col="Close")
    regime_val = getattr(result, 'current_regime', '?')
    logger.info(f"[REGIME] OK regime={regime_val}")
    return _sanitize(result)


@app.get("/api/v1/intermarket", tags=["Frontend"])
async def get_intermarket(period: str = "1y"):
    """Get intermarket correlation analysis."""
    logger.info(f"[INTERMARKET] period={period}")
    from src.data_fetcher import fetch_all_market_data
    from src.intermarket import calc_correlation_matrix, calc_intermarket_summary
    import pandas as pd

    market_data = fetch_all_market_data(period=period)
    if not market_data:
        logger.error("[INTERMARKET] No market data")
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    # calc_correlation_matrix expects Dict[str, DataFrame]
    data_dict = {name: df for name, df in market_data.items() if not df.empty}
    logger.debug(f"[INTERMARKET] {len(data_dict)} markets: {list(data_dict.keys())}")
    corr = calc_correlation_matrix(data_dict)
    summary = calc_intermarket_summary(data_dict)

    # Correlation matrix needs nested dict format for frontend CorrelationMatrix component
    if isinstance(corr, pd.DataFrame):
        corr = corr.to_dict()  # orient='dict' → {col: {row: value}}

    logger.info(f"[INTERMARKET] OK corr_keys={list(corr.keys())[:5] if isinstance(corr, dict) else '?'}")
    return _sanitize({"correlation": corr, "summary": summary})


@app.get("/api/v1/patterns/{ticker}/full", tags=["Frontend"])
async def get_full_patterns(ticker: str, period: str = "6mo"):
    """Get full pattern analysis for a ticker (same as existing but with numpy sanitize)."""
    from src.data_fetcher import fetch_all_market_data
    from src.patterns import full_pattern_analysis

    market_data = fetch_all_market_data(period=period)
    target_name = _resolve_ticker(ticker)

    if target_name not in market_data:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    df = market_data[target_name]
    result = full_pattern_analysis(df)
    return _sanitize({
        "candlestick_patterns": [{"name": p.name, "type": p.type, "confidence": p.confidence, "date": p.date} for p in result["candlestick_patterns"]],
        "chart_patterns": [{"name": p.name, "type": p.type, "confidence": p.confidence, "description": p.description} for p in result["chart_patterns"]],
        "market_structure": {"structure": result["market_structure"].structure, "description": result["market_structure"].description},
        "volume_anomalies": [{"date": a.date, "type": a.anomaly_type, "z_score": a.z_score} for a in result["volume_anomalies"]],
        "trendlines": [{"type": t.type, "slope": t.slope, "touches": t.touches} for t in result["trendlines"]],
        "summary": result["summary"],
    })


@app.get("/api/v1/options/{ticker}", tags=["Frontend"])
async def get_options_analysis(ticker: str):
    """Get options analysis for a ticker."""
    logger.info(f"[OPTIONS] ticker={ticker}")
    from src.data_fetcher import fetch_yfinance_data
    from src.options_analysis import run_options_analysis
    import numpy as np

    df = fetch_yfinance_data(ticker, period="1y", interval="1d")
    if df is None or df.empty:
        logger.error(f"[OPTIONS] No data for {ticker}")
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    S = float(df["Close"].iloc[-1])
    K = S  # ATM option
    T = 30 / 365  # 30 days
    r = 0.06  # risk-free rate
    returns = df["Close"].pct_change().dropna()
    sigma = float(returns.std() * np.sqrt(252))  # annualized volatility
    logger.debug(f"[OPTIONS] S={S:.2f}, K={K:.2f}, sigma={sigma:.4f}, T={T:.4f}")

    try:
        result = run_options_analysis(S, K, T=T, r=r, sigma=sigma)
    except Exception as e:
        logger.error(f"[OPTIONS] failed: {e}\n{traceback.format_exc()}")
        raise HTTPException(status_code=500, detail=f"Options error: {e}")
    logger.info(f"[OPTIONS] OK keys={list(result.keys())[:5]}")
    return _sanitize(result)


@app.get("/api/v1/system/check", tags=["Frontend"])
async def get_system_check():
    """Get system check results (packages, GPU, etc)."""
    from src.system_check import check_system
    from dataclasses import asdict, is_dataclass
    result = check_system()
    if is_dataclass(result):
        result = asdict(result)
    return _sanitize(result)


@app.get("/api/v1/data/inventory", tags=["Frontend"])
async def get_data_inventory():
    """Get data inventory from database."""
    import sqlite3
    db_path = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "src", "data", "saham_prediksi.db")
    if not os.path.exists(db_path):
        return []
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    cursor.execute("SELECT name FROM sqlite_master WHERE type='table'")
    tables = cursor.fetchall()
    inventory = []
    for (table_name,) in tables:
        cursor.execute(f"SELECT COUNT(*) FROM {table_name}")
        count = cursor.fetchone()[0]
        cursor.execute(f"PRAGMA table_info({table_name})")
        cols = [c[1] for c in cursor.fetchall()]
        inventory.append({"table": table_name, "rows": count, "columns": cols})
    conn.close()
    return inventory


@app.get("/api/v1/accuracy/full", tags=["Frontend"])
async def get_full_accuracy():
    """Get full accuracy and verification metrics."""
    from src.database import get_akurasi_metrics, get_all_prediksi
    metrics = get_akurasi_metrics()
    df = get_all_prediksi()
    recent = []
    if not df.empty:
        recent_df = df.tail(20)
        for _, row in recent_df.iterrows():
            actual = row.get("harga_aktual")
            predicted = row.get("harga_prediksi")
            arah_pred = row.get("arah_prediksi", "")
            arah_akt = row.get("arah_aktual", "")
            recent.append({
                "date": str(row.get("tanggal_prediksi", "")),
                "ticker": str(row.get("ticker", "")),
                "predicted": float(predicted) if predicted else 0,
                "actual": float(actual) if actual else None,
                "correct": bool(arah_pred and arah_akt and arah_pred == arah_akt),
                "signal": str(row.get("sinyal", "")),
            })
    return _sanitize({"metrics": metrics, "recent_predictions": recent})


@app.get("/api/v1/model/details/{ticker}", tags=["Frontend"])
async def get_model_details(ticker: str):
    """Get detailed model information including ensemble votes and SHAP."""
    from src.data_fetcher import fetch_all_market_data, fetch_yfinance_data
    from src.predictor import run_prediction as _run_prediction

    market_data = fetch_all_market_data(period="2y")
    md = dict(market_data)
    target_name = _resolve_ticker(ticker, default="TARGET")
    if target_name == "TARGET" and (target_name not in md or md[target_name].empty):
        df = fetch_yfinance_data(ticker, period="1y", interval="1d")
        if not df.empty:
            md[target_name] = df

    if not md:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    result = _run_prediction(market_data=md, target_ticker=ticker)
    # Extract model-specific details
    details = {
        "predictions": result.get("predictions", {}),
        "probabilities": result.get("probabilities", {}),
        "model_votes": result.get("model_votes", ""),
        "rules": result.get("rules", ""),
        "shap_explanations": result.get("shap_explanations", {}),
        "feature_importance": result.get("feature_importance", {}),
        "ensemble_method": result.get("ensemble_method", ""),
        "training_scores": result.get("training_scores", {}),
        "market_regime": result.get("market_regime", "unknown"),
        "regime_adjusted": result.get("regime_adjusted", False),
        "risk_governance": result.get("risk_governance", {}),
        "advanced_analysis": result.get("advanced_analysis", {}),
    }
    return _sanitize(details)


@app.get("/api/v1/sentiment/full", tags=["Frontend"])
async def get_full_sentiment():
    """Get full Fear & Greed sentiment with components."""
    from src.data_fetcher import fetch_all_market_data
    from src.sentiment import calc_fear_greed_index

    market_data = fetch_all_market_data(period="6mo")
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    fg = calc_fear_greed_index(market_data)
    return _sanitize(fg)


@app.get("/api/v1/briefing/full", tags=["Frontend"])
async def get_full_briefing():
    """Get full AI multi-agent briefing with Bull vs Bear debate."""
    from src.data_fetcher import fetch_all_data
    from src.ai_agent import generate_daily_briefing
    from src.predictor import run_prediction

    data = fetch_all_data(period="2y")
    market_data = data.get("market", {})
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    try:
        result = run_prediction(market_data, fred_data=data.get("fred"))
        signal = result.get("sinyal", "HOLD")
        confidence = result.get("confidence", 0.5)
        predictions = result.get("predictions", {})
        probabilities = result.get("probabilities", {})
    except Exception:
        signal = "HOLD"
        confidence = 0.5
        predictions = {}
        probabilities = {}

    from src.preprocessor import prepare_features
    df = prepare_features(market_data, fred_data=data.get("fred"))

    briefing = generate_daily_briefing(
        market_data, df, signal, confidence, predictions, probabilities
    )

    # Extract agent findings for frontend BriefingResult type
    final_rec = briefing.get("final_recommendation")
    final_rec_str = final_rec.findings if hasattr(final_rec, "findings") else str(final_rec)

    agents = briefing.get("agents", [])
    bull_case = next((a.findings for a in agents if "bull" in a.agent_name.lower() or signal == "BUY"), None)
    bear_case = next((a.findings for a in agents if "bear" in a.agent_name.lower() or signal == "SELL"), None)

    result = {
        "date": briefing["date"],
        "market_summary": briefing["market_summary"],
        "signal": briefing["signal"],
        "confidence": briefing["confidence"],
        "final_recommendation": final_rec_str,
        "actionable_items": briefing["actionable_items"],
        "risk_assessment": briefing["risk_assessment"],
        "bull_case": bull_case,
        "bear_case": bear_case,
        "debate": {a.agent_name: a.findings for a in agents} if agents else None,
        "llm_commentary": briefing.get("llm_commentary"),
    }
    return _sanitize(result)


@app.get("/api/v1/score/{ticker}/full", tags=["Frontend"])
async def get_full_score(ticker: str):
    """Get composite AI score with all component ratings."""
    from src.data_fetcher import fetch_all_data
    from src.scoring import calc_composite_ai_score
    from src.predictor import run_prediction

    target = TICKERS.get(ticker, TARGET_TICKER)
    data = fetch_all_data(period="2y")
    market_data = data.get("market", {})
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    try:
        result = run_prediction(market_data, fred_data=data.get("fred"), target_ticker=target)
        predictions = result.get("predictions", {})
        probabilities = result.get("probabilities", {})
        confidence = result.get("confidence", 0.5)
    except Exception:
        predictions = {}
        probabilities = {}
        confidence = 0.5

    from src.preprocessor import prepare_features
    df = prepare_features(market_data, fred_data=data.get("fred"), target_ticker=target)
    latest = df.iloc[-1]

    rsi = float(latest.get("Target_RSI", 50))
    vix = float(latest.get("VIX_Close", 20))
    fear_greed = float(latest.get("Fear_Greed_Index", 50))
    ma5 = float(latest.get("IHSG_MA5", 0))
    ma10 = float(latest.get("IHSG_MA10", 0))
    ma20 = float(latest.get("IHSG_MA20", 0))
    ma_alignment = 1.0 if ma5 > ma10 > ma20 else (-1.0 if ma5 < ma10 < ma20 else 0.0)
    macd = float(latest.get("Target_MACD", 0))
    macd_signal = float(latest.get("Target_MACD_Signal", 0))
    macd_val = 1.0 if macd > macd_signal else (-1.0 if macd < macd_signal else 0.0)
    bb_pct = float(latest.get("Target_BB_Pct", 0.5))

    score = calc_composite_ai_score(
        predictions=predictions, probabilities=probabilities, confidence=confidence,
        rsi=rsi, trend_bullish=ma5 > ma10 > ma20, trend_bearish=ma5 < ma10 < ma20,
        vix=vix, fear_greed=fear_greed, ma_alignment=ma_alignment,
        macd_signal=macd_val, bb_position=bb_pct,
    )
    return _sanitize(score)


# =============================================================================
# ASYNC TASK ENDPOINTS (Celery)
# =============================================================================

@app.post("/api/v1/tasks/predict", tags=["Async Tasks"])
async def submit_prediction_task(ticker: str = Query(default="IHSG"), period: str = Query(default="2y")):
    """Submit async prediction task. Returns task_id for polling."""
    from src.celery_app import run_prediction_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed. Run synchronously via POST /api/v1/predict"}

    target = TICKERS.get(ticker, TARGET_TICKER)
    result = run_prediction_task.delay(ticker=target, period=period)
    logger.info(f"[TASK] Prediction submitted: ticker={ticker}, task_id={result.id}")
    return {"task_id": result.id, "status": "PENDING", "ticker": ticker}


@app.post("/api/v1/tasks/fetch", tags=["Async Tasks"])
async def submit_fetch_task(period: str = Query(default="2y")):
    """Submit async market data fetch task."""
    from src.celery_app import fetch_market_data_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed"}

    result = fetch_market_data_task.delay(period=period)
    logger.info(f"[TASK] Data fetch submitted: task_id={result.id}")
    return {"task_id": result.id, "status": "PENDING"}


@app.post("/api/v1/tasks/backtest", tags=["Async Tasks"])
async def submit_backtest_task(ticker: str = Query(default="IHSG"), period: str = Query(default="2y")):
    """Submit async backtest task."""
    from src.celery_app import run_backtest_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed"}

    target = TICKERS.get(ticker, TARGET_TICKER)
    result = run_backtest_task.delay(ticker=target, period=period)
    logger.info(f"[TASK] Backtest submitted: ticker={ticker}, task_id={result.id}")
    return {"task_id": result.id, "status": "PENDING", "ticker": ticker}


@app.post("/api/v1/tasks/screener", tags=["Async Tasks"])
async def submit_screener_task(top_n: int = Query(default=10)):
    """Submit async screener task."""
    from src.celery_app import run_screener_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed"}

    result = run_screener_task.delay(top_n=top_n)
    logger.info(f"[TASK] Screener submitted: task_id={result.id}")
    return {"task_id": result.id, "status": "PENDING"}


@app.post("/api/v1/tasks/retrain", tags=["Async Tasks"])
async def submit_retrain_task(ticker: str = Query(default="IHSG")):
    """Submit async model retrain task."""
    from src.celery_app import retrain_model_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed"}

    target = TICKERS.get(ticker, TARGET_TICKER)
    result = retrain_model_task.delay(ticker=target)
    logger.info(f"[TASK] Retrain submitted: ticker={ticker}, task_id={result.id}")
    return {"task_id": result.id, "status": "PENDING", "ticker": ticker}


@app.get("/api/v1/tasks/{task_id}", tags=["Async Tasks"])
async def get_task_status(task_id: str):
    """Get status of an async task."""
    from src.celery_app import get_task_status as _get_status
    return _get_status(task_id)


# =============================================================================
# CACHE MANAGEMENT ENDPOINTS
# =============================================================================

@app.get("/api/v1/cache/stats", tags=["Cache"])
async def get_cache_stats():
    """Get Redis cache statistics."""
    from src.cache import cache_stats
    return cache_stats()


@app.delete("/api/v1/cache", tags=["Cache"])
async def clear_cache(pattern: str = Query(default="")):
    """Clear cache. If pattern provided, only clear matching keys."""
    from src.cache import cache_delete_pattern, invalidate_all
    if pattern:
        count = cache_delete_pattern(pattern)
    else:
        count = invalidate_all()
    return {"cleared": count}


# =============================================================================
# ALERTS CRUD (Blueprint: Price Alert System)
# =============================================================================

@app.post("/api/v1/alerts", response_model=AlertResponse, tags=["Alerts"])
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


@app.get("/api/v1/alerts", tags=["Alerts"])
async def list_alerts(ticker: Optional[str] = None, active_only: bool = True):
    """List alerts, optionally filtered by ticker and active status."""
    from src.database import get_active_alerts
    target = TICKERS.get(ticker, ticker) if ticker else None
    df = get_active_alerts(target)
    if df.empty:
        return []
    return _sanitize(df.to_dict(orient="records"))


@app.put("/api/v1/alerts/{alert_id}/deactivate", tags=["Alerts"])
async def deactivate_alert(alert_id: int):
    """Deactivate an alert (soft delete — keeps record but stops monitoring)."""
    from src.database import deactivate_alert as _deactivate
    _deactivate(alert_id)
    logger.info(f"[ALERT] Deactivated id={alert_id}")
    return {"id": alert_id, "is_active": 0, "status": "deactivated"}


@app.put("/api/v1/alerts/{alert_id}/trigger", tags=["Alerts"])
async def trigger_alert(alert_id: int, message: str = ""):
    """Mark an alert as triggered."""
    from src.database import trigger_alert as _trigger
    _trigger(alert_id, message)
    logger.info(f"[ALERT] Triggered id={alert_id}")
    return {"id": alert_id, "is_triggered": 1, "status": "triggered"}


@app.delete("/api/v1/alerts/{alert_id}", tags=["Alerts"])
async def delete_alert(alert_id: int):
    """Permanently delete an alert."""
    from src.database import delete_alert as _delete
    _delete(alert_id)
    logger.info(f"[ALERT] Deleted id={alert_id}")
    return {"id": alert_id, "status": "deleted"}


# =============================================================================
# NOTIFICATIONS API
# =============================================================================

@app.get("/api/v1/notifications", tags=["Notifications"])
async def list_notifications(limit: int = 100, unread_only: bool = False):
    """List in-app notifications, optionally filtered to unread only."""
    from src.database import get_notifikasi
    df = get_notifikasi(limit=limit, hanya_belum_dibaca=unread_only)
    if df.empty:
        return []
    return _sanitize(df.to_dict(orient="records"))


@app.get("/api/v1/notifications/unread-count", tags=["Notifications"])
async def get_unread_notification_count():
    """Get count of unread notifications."""
    from src.database import get_jumlah_notifikasi_belum_dibaca
    count = get_jumlah_notifikasi_belum_dibaca()
    return {"unread": count}


@app.put("/api/v1/notifications/{notification_id}/read", tags=["Notifications"])
async def mark_notification_read(notification_id: int):
    """Mark a single notification as read."""
    from src.database import mark_notifikasi_dibaca
    mark_notifikasi_dibaca(notifikasi_id=notification_id)
    return {"id": notification_id, "status": "read"}


@app.put("/api/v1/notifications/read-all", tags=["Notifications"])
async def mark_all_notifications_read():
    """Mark all notifications as read."""
    from src.database import mark_notifikasi_dibaca
    mark_notifikasi_dibaca(semua=True)
    return {"status": "all_read"}


# =============================================================================
# ANTI-MANIPULATION & FRAUD DETECTION API (Blueprint Bab 3 & 4)
# =============================================================================

@app.get("/api/v1/fraud/check/{ticker}", tags=["Fraud Detection"])
async def run_fraud_check(ticker: str, period: str = "6mo"):
    """
    Run full fraud detection (6-layer) for a ticker.

    Layers:
    1. Data quality validation
    2. Cross-source verification
    3. Index-constituent consistency
    4. News-price divergence
    5. Anti-manipulation metrics (Z-Score, Amihud, Beneish)
    6. Market manipulation (wash trading, spoofing)
    """
    from src.data_fetcher import fetch_yfinance_data
    from src.fraud_detection import FraudDetector

    target = TICKERS.get(ticker, ticker)
    df = fetch_yfinance_data(target, period=period)
    if df is None or df.empty:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    detector = FraudDetector()
    market_data = {target: df}

    results = {
        "ticker": target,
        "data_quality": detector.check_data_quality(market_data),
        "wash_trading": detector.check_wash_trading(market_data),
        "spoofing": detector.check_spoofing(market_data),
        "alerts": [{"type": a.alert_type, "severity": a.severity, "ticker": a.ticker, "message": a.message} for a in detector.alerts],
    }
    return _sanitize(results)


@app.get("/api/v1/anti-manipulation/scan/{ticker}", tags=["Fraud Detection"])
async def run_anti_manipulation_scan(ticker: str, period: str = "1y"):
    """
    Run anti-manipulation metrics scan (Blueprint Bab 3 & 4).

    Returns: Z-Score volume shock, Amihud illiquidity, wash trading, spoofing results.
    """
    from src.data_fetcher import fetch_yfinance_data
    from src.anti_manipulation import detect_wash_trading, detect_spoofing, calc_volume_shock, calc_amihud_illiquidity

    target = TICKERS.get(ticker, ticker)
    df = fetch_yfinance_data(target, period=period)
    if df is None or df.empty:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    results = {
        "ticker": target,
        "volume_shock": None,
        "illiquidity": None,
        "wash_trading": None,
        "spoofing": None,
    }

    try:
        vs = calc_volume_shock(df, ticker=target)
        results["volume_shock"] = {
            "is_anomaly": vs.is_anomaly,
            "z_score": vs.z_score,
            "severity": vs.severity,
            "description": vs.description,
        }
    except Exception as e:
        results["volume_shock"] = {"error": str(e)}

    try:
        il = calc_amihud_illiquidity(df, ticker=target)
        results["illiquidity"] = {
            "illiquidity_ratio": il.illiquidity_ratio,
            "is_illiquid": il.is_illiquid,
            "classification": il.classification,
            "description": il.description,
        }
    except Exception as e:
        results["illiquidity"] = {"error": str(e)}

    try:
        wt = detect_wash_trading(df, ticker=target)
        results["wash_trading"] = {
            "is_suspected": wt.is_suspected,
            "confidence": wt.confidence,
            "indicators": wt.indicators,
            "description": wt.description,
        }
    except Exception as e:
        results["wash_trading"] = {"error": str(e)}

    try:
        sp = detect_spoofing(df, ticker=target)
        results["spoofing"] = {
            "is_suspected": sp.is_suspected,
            "confidence": sp.confidence,
            "indicators": sp.indicators,
            "description": sp.description,
        }
    except Exception as e:
        results["spoofing"] = {"error": str(e)}

    return _sanitize(results)


@app.post("/api/v1/fraud/fake-news-hype", tags=["Fraud Detection"])
async def check_fake_news_hype(
    ticker: str,
    price_change_pct: float,
    volume_z_score: float,
    news_sentiment: float,
    news_count: int = 0,
    insider_selling: Optional[float] = None,
    social_hype_score: Optional[float] = None,
):
    """
    Check for fake news hype pattern (Blueprint Bab 3.2).

    Detects coordinated positive sentiment during distribution phases.
    """
    from src.fraud_detection import FraudDetector

    target = TICKERS.get(ticker, ticker)
    detector = FraudDetector()
    result = detector.check_fake_news_hype(
        ticker=target,
        price_change_pct=price_change_pct,
        volume_z_score=volume_z_score,
        news_sentiment=news_sentiment,
        news_count=news_count,
        insider_selling=insider_selling,
        social_hype_score=social_hype_score,
    )
    return _sanitize(result)


# =============================================================================
# FUNDAMENTAL & TECHNICAL DATA API
# =============================================================================

@app.get("/api/v1/fundamental/{ticker}", tags=["Fundamental"])
async def get_fundamental(ticker: str):
    """Get latest fundamental data snapshot for a ticker."""
    from src.database import load_fundamental
    target = TICKERS.get(ticker, ticker)
    data = load_fundamental(target)
    if not data:
        raise HTTPException(status_code=404, detail=f"No fundamental data for {ticker}")
    return _sanitize(data)


@app.get("/api/v1/fundamental", tags=["Fundamental"])
async def get_all_fundamentals():
    """Get latest fundamental data for all tickers."""
    from src.database import load_all_fundamentals
    df = load_all_fundamentals()
    if df.empty:
        return []
    return _sanitize(df.to_dict(orient="records"))


@app.get("/api/v1/indicators/{ticker}", tags=["Technical Indicators"])
async def get_technical_indicators(ticker: str, limit: int = 30):
    """Get technical indicator snapshots for a ticker."""
    from src.database import load_technical_indicators
    target = TICKERS.get(ticker, ticker)
    df = load_technical_indicators(target, limit=limit)
    if df.empty:
        raise HTTPException(status_code=404, detail=f"No technical indicators for {ticker}")
    return _sanitize(df.to_dict(orient="records"))


@app.get("/api/v1/indicators/{ticker}/latest", tags=["Technical Indicators"])
async def get_latest_technical_indicators(ticker: str):
    """Get most recent technical indicator snapshot for a ticker."""
    from src.database import load_latest_technical_indicators
    target = TICKERS.get(ticker, ticker)
    data = load_latest_technical_indicators(target)
    if not data:
        raise HTTPException(status_code=404, detail=f"No technical indicators for {ticker}")
    return _sanitize(data)


@app.get("/api/v1/financial-ratios/{ticker}", tags=["Fundamental"])
async def get_financial_ratios(ticker: str, limit: int = 20):
    """Get financial ratio history for a ticker."""
    from src.database import load_financial_ratios
    target = TICKERS.get(ticker, ticker)
    df = load_financial_ratios(target, limit=limit)
    if df.empty:
        raise HTTPException(status_code=404, detail=f"No financial ratios for {ticker}")
    return _sanitize(df.to_dict(orient="records"))


# =============================================================================
# STARTUP
# =============================================================================

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
