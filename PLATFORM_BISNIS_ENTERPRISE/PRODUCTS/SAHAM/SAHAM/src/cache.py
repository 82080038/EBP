"""
Redis caching layer for the Saham Prediction Application.

Features:
- TTL-based caching for market data, predictions, and API responses
- Event-based cache invalidation (invalidate on new data fetch/prediction)
- Decorator-based usage: @cached(ttl=300)
- Automatic fallback to no-cache if Redis is unavailable
- JSON + pickle serialization for complex objects
- Cache key namespacing for organized invalidation
"""

import os
import json
import logging
import functools
import hashlib
import pickle
import time
from typing import Optional, Any, Callable, Dict, Tuple

logger = logging.getLogger("saham.cache")

try:
    import redis

    _REDIS_AVAILABLE = True
except ImportError:
    _REDIS_AVAILABLE = False
    logger.info("redis not installed — in-memory fallback cache enabled")

REDIS_URL = os.getenv("REDIS_URL", "")
_REDIS_CLIENT = None
_CACHE_ENABLED = bool(REDIS_URL) and _REDIS_AVAILABLE

# In-memory fallback cache (used when Redis is unavailable)
# Stores: key -> (value, expiry_timestamp)
_IN_MEMORY_CACHE: Dict[str, Tuple[Any, float]] = {}
_IN_MEMORY_MAX_SIZE = 500


def _mem_get(key: str) -> Optional[Any]:
    if key not in _IN_MEMORY_CACHE:
        return None
    value, expiry = _IN_MEMORY_CACHE[key]
    if time.time() > expiry:
        del _IN_MEMORY_CACHE[key]
        return None
    return value


def _mem_set(key: str, value: Any, ttl: int) -> bool:
    expiry = time.time() + ttl
    # Simple eviction: remove oldest if over max size
    if len(_IN_MEMORY_CACHE) >= _IN_MEMORY_MAX_SIZE and key not in _IN_MEMORY_CACHE:
        oldest = next(iter(_IN_MEMORY_CACHE))
        del _IN_MEMORY_CACHE[oldest]
    _IN_MEMORY_CACHE[key] = (value, expiry)
    return True


def _mem_delete(key: str) -> bool:
    return _IN_MEMORY_CACHE.pop(key, None) is not None


def _mem_delete_pattern(pattern: str) -> int:
    keys = [k for k in _IN_MEMORY_CACHE if pattern.replace("*", "") in k]
    for k in keys:
        del _IN_MEMORY_CACHE[k]
    return len(keys)


def _get_redis():
    """Get or create the Redis client singleton."""
    global _REDIS_CLIENT, _CACHE_ENABLED
    if _REDIS_CLIENT is None and _CACHE_ENABLED:
        try:
            _REDIS_CLIENT = redis.from_url(REDIS_URL, decode_responses=False, socket_timeout=5)
            _REDIS_CLIENT.ping()
            logger.info(f"[CACHE] Redis connected: {REDIS_URL}")
        except Exception as e:
            logger.warning(f"[CACHE] Redis connection failed: {e} — caching disabled")
            _CACHE_ENABLED = False
            _REDIS_CLIENT = None
    return _REDIS_CLIENT


def is_cache_enabled() -> bool:
    """Check if Redis caching is enabled and connected."""
    return _CACHE_ENABLED


# ===========================================================================
# CACHE TTL PRESETS (seconds)
# ===========================================================================
class TTL:
    SHORT = 60          # 1 minute — real-time prices
    MEDIUM = 300        # 5 minutes — market data, screener
    LONG = 1800         # 30 minutes — predictions, scores
    XLONG = 3600        # 1 hour — backtest, portfolio optimization
    DAILY = 86400       # 24 hours — fundamental data, financial ratios
    WEEKLY = 604800     # 7 days — historical metadata


# ===========================================================================
# CORE CACHE FUNCTIONS
# ===========================================================================
def cache_get(key: str) -> Optional[Any]:
    """Get a value from cache. Returns None if miss or disabled."""
    if _CACHE_ENABLED:
        client = _get_redis()
        if client is not None:
            try:
                data = client.get(key)
                if data is not None:
                    return pickle.loads(data)
            except Exception as e:
                logger.debug(f"[CACHE] redis get failed for {key}: {e}")
    # Fallback to in-memory cache
    return _mem_get(key)


def cache_set(key: str, value: Any, ttl: int = TTL.MEDIUM) -> bool:
    """Set a value in cache with TTL. Returns True if successful."""
    if _CACHE_ENABLED:
        client = _get_redis()
        if client is not None:
            try:
                data = pickle.dumps(value, protocol=pickle.HIGHEST_PROTOCOL)
                client.setex(key, ttl, data)
                return True
            except Exception as e:
                logger.debug(f"[CACHE] redis set failed for {key}: {e}")
    # Fallback to in-memory cache
    return _mem_set(key, value, ttl)


def cache_delete(key: str) -> bool:
    """Delete a key from cache."""
    if _CACHE_ENABLED:
        client = _get_redis()
        if client is not None:
            try:
                client.delete(key)
                return True
            except Exception as e:
                logger.debug(f"[CACHE] redis delete failed for {key}: {e}")
    return _mem_delete(key)


def cache_delete_pattern(pattern: str) -> int:
    """Delete all keys matching a pattern (e.g., 'market_data:*'). Returns count."""
    total = 0
    if _CACHE_ENABLED:
        client = _get_redis()
        if client is not None:
            try:
                keys = list(client.scan_iter(match=pattern, count=100))
                if keys:
                    client.delete(*keys)
                total += len(keys)
            except Exception as e:
                logger.debug(f"[CACHE] redis delete_pattern failed for {pattern}: {e}")
    total += _mem_delete_pattern(pattern)
    return total


def cache_exists(key: str) -> bool:
    """Check if a key exists in cache."""
    if _CACHE_ENABLED:
        client = _get_redis()
        if client is not None:
            try:
                return bool(client.exists(key))
            except Exception:
                pass
    return _mem_get(key) is not None


def cache_ttl(key: str) -> int:
    """Get remaining TTL for a key. Returns -1 if no expiry, -2 if not found."""
    if _CACHE_ENABLED:
        client = _get_redis()
        if client is not None:
            try:
                return client.ttl(key)
            except Exception:
                pass
    if key in _IN_MEMORY_CACHE:
        remaining = _IN_MEMORY_CACHE[key][1] - time.time()
        return int(remaining) if remaining > 0 else -2
    return -2


# ===========================================================================
# DECORATOR
# ===========================================================================
def cached(ttl: int = TTL.MEDIUM, key_prefix: str = ""):
    """
    Decorator to cache function results in Redis or in-memory fallback.

    Usage:
        @cached(ttl=300, key_prefix="market_data")
        def fetch_market_data(period="1y"):
            ...

    Cache key: {key_prefix}:{function_name}:{args_hash}
    """
    def decorator(func: Callable) -> Callable:
        @functools.wraps(func)
        def wrapper(*args, **kwargs):
            # Build cache key
            prefix = key_prefix or func.__module__.replace(".", "_")
            func_name = func.__name__
            key_parts = [str(arg) for arg in args]
            key_parts.extend(f"{k}={v}" for k, v in sorted(kwargs.items()))
            args_hash = hashlib.md5("|".join(key_parts).encode()).hexdigest()[:12]
            cache_key = f"{prefix}:{func_name}:{args_hash}"

            # Try cache
            cached_val = cache_get(cache_key)
            if cached_val is not None:
                logger.debug(f"[CACHE] HIT {cache_key}")
                return cached_val

            # Compute and cache
            logger.debug(f"[CACHE] MISS {cache_key}")
            result = func(*args, **kwargs)
            cache_set(cache_key, result, ttl)
            return result

        return wrapper
    return decorator


# ===========================================================================
# EVENT-BASED INVALIDATION
# ===========================================================================
def invalidate_market_data(ticker: Optional[str] = None):
    """Invalidate market data cache after new data fetch."""
    if ticker:
        cache_delete_pattern(f"market_data:*{ticker}*")
        cache_delete_pattern(f"ohlcv:*{ticker}*")
    else:
        cache_delete_pattern("market_data:*")
        cache_delete_pattern("ohlcv:*")
    logger.info(f"[CACHE] Invalidated market_data cache (ticker={ticker or 'all'})")


def invalidate_predictions(ticker: Optional[str] = None):
    """Invalidate prediction cache after new prediction."""
    if ticker:
        cache_delete_pattern(f"predict:*{ticker}*")
        cache_delete_pattern(f"score:*{ticker}*")
        cache_delete_pattern(f"model_details:*{ticker}*")
    else:
        cache_delete_pattern("predict:*")
        cache_delete_pattern("score:*")
        cache_delete_pattern("model_details:*")
    logger.info(f"[CACHE] Invalidated predictions cache (ticker={ticker or 'all'})")


def invalidate_screener():
    """Invalidate screener/watchlist cache."""
    cache_delete_pattern("screener:*")
    cache_delete_pattern("watchlist:*")
    logger.info("[CACHE] Invalidated screener cache")


def invalidate_all():
    """Flush all application cache keys."""
    patterns = [
        "market_data:*", "ohlcv:*", "predict:*", "score:*",
        "model_details:*", "screener:*", "watchlist:*",
        "backtest:*", "portfolio:*", "regime:*", "sentiment:*",
        "briefing:*", "risk:*", "patterns:*", "macro:*",
    ]
    total = 0
    for p in patterns:
        total += cache_delete_pattern(p)
    logger.info(f"[CACHE] Flushed {total} keys from all patterns")
    return total


# ===========================================================================
# CACHE STATISTICS
# ===========================================================================
def cache_stats() -> dict:
    """Get cache statistics."""
    if _CACHE_ENABLED:
        client = _get_redis()
        if client is not None:
            try:
                info = client.info("memory")
                db_size = client.dbsize()
                return {
                    "enabled": True,
                    "backend": "redis",
                    "keys": db_size,
                    "used_memory_human": info.get("used_memory_human", "?"),
                    "peak_memory_human": info.get("used_memory_peak_human", "?"),
                    "redis_url": REDIS_URL.split("@")[-1] if "@" in REDIS_URL else REDIS_URL,
                }
            except Exception as e:
                return {"enabled": True, "backend": "redis", "keys": 0, "error": str(e)}

    # Fallback in-memory cache stats
    return {
        "enabled": True,
        "backend": "memory",
        "keys": len(_IN_MEMORY_CACHE),
        "redis_url": None,
    }
