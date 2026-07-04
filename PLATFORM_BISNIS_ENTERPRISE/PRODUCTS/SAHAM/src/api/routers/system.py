"""
System endpoints: health check, cache management.
"""
from fastapi import APIRouter
from datetime import datetime

from src.api.models import HealthResponse, FullHealthResponse
from src.api.utils import _cached_response, _cache_response
from src.cache import cache_get, _CACHE_ENABLED, cache_stats, cache_delete_pattern, invalidate_all, TTL

router = APIRouter(prefix="/api/v1", tags=["System"])


@router.get("/health", response_model=HealthResponse)
@router.get("/api/v1/health", response_model=HealthResponse)
async def health_check():
    """Health check endpoint."""
    return HealthResponse(
        status="healthy",
        version="1.0.0",
        timestamp=datetime.now().isoformat(),
    )


@router.get("/health/full", response_model=FullHealthResponse)
async def health_check_full():
    """Full health check including database, Redis, data source, and validation."""
    from src.database import init_db, get_connection
    from src.data_fetcher import fetch_yfinance_data
    from src.data_validation import validate_ticker_symbol

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


@router.get("/cache/stats", tags=["Cache"])
async def get_cache_stats():
    """Get Redis cache statistics."""
    return cache_stats()


@router.delete("/cache", tags=["Cache"])
async def clear_cache(pattern: str = ""):
    """Clear cache. If pattern provided, only clear matching keys."""
    if pattern:
        count = cache_delete_pattern(pattern)
    else:
        count = invalidate_all()
    return {"cleared": count}
