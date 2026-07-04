"""
Utility functions for API: sanitization, ticker resolution, caching.
"""
import math
import numpy as np
import pandas as pd
from dataclasses import asdict, is_dataclass
from typing import Any
from fastapi import Request

from src.config import TICKERS
from src.cache import cache_get, cache_set, TTL


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


def _endpoint_cache_key(request: Request) -> str:
    """Build a cache key from request path + query string."""
    query = str(request.url.query)
    return f"api:{request.url.path}:{hash(query)}"


async def _cached_response(request: Request, ttl: int = TTL.MEDIUM):
    """Try to return a cached JSON response for the request. Returns None if miss."""
    key = _endpoint_cache_key(request)
    cached = cache_get(key)
    if cached is not None:
        from fastapi.responses import JSONResponse
        return JSONResponse(content=cached)
    return None


def _cache_response(request: Request, data: Any, ttl: int = TTL.MEDIUM) -> None:
    """Store a JSON-serializable response in cache."""
    key = _endpoint_cache_key(request)
    cache_set(key, data, ttl=ttl)
