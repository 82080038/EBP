"""
Middleware for FastAPI: logging, rate limiting, exception handling.
"""
import time as _time
import logging
import traceback
from typing import Dict, List
from fastapi import Request
from fastapi.responses import JSONResponse
from starlette.middleware.base import BaseHTTPMiddleware

logger = logging.getLogger("saham.api.middleware")

# Simple per-client rate limiter: {client_ip -> {endpoint -> [(timestamp, ...)]}
_rate_limit_store: Dict[str, Dict[str, List[float]]] = {}


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
