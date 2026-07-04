"""
Tests for src/cache.py in-memory fallback when Redis is unavailable.
"""

import pytest
import time

from src import cache
from src.cache import cache_get, cache_set, cache_delete, cache_exists, cache_ttl, cache_stats, TTL


class TestCacheFallback:
    def test_cache_set_get_without_redis(self):
        # Ensure Redis is not enabled in this test environment
        cache_set("test_key", {"value": 42}, ttl=60)
        result = cache_get("test_key")
        assert result == {"value": 42}

    def test_cache_delete(self):
        cache_set("delete_key", "x", ttl=60)
        assert cache_exists("delete_key") is True
        cache_delete("delete_key")
        assert cache_get("delete_key") is None

    def test_cache_ttl_expiry(self):
        cache_set("ttl_key", "x", ttl=1)
        assert cache_get("ttl_key") == "x"
        assert cache_ttl("ttl_key") > 0
        time.sleep(1.1)
        assert cache_get("ttl_key") is None

    def test_cache_stats(self):
        cache_set("stats_key", "x", ttl=60)
        stats = cache_stats()
        assert stats["enabled"] is True
        assert stats["backend"] == "memory"
        assert stats["keys"] >= 1

    def test_cached_decorator(self):
        call_count = 0

        @cache.cached(ttl=60, key_prefix="test")
        def compute(x):
            nonlocal call_count
            call_count += 1
            return x * 2

        assert compute(5) == 10
        assert compute(5) == 10
        assert call_count == 1

    def test_cache_delete_pattern(self):
        cache_set("pattern_a_1", 1, ttl=60)
        cache_set("pattern_a_2", 2, ttl=60)
        cache_set("pattern_b_1", 3, ttl=60)
        deleted = cache.cache_delete_pattern("pattern_a_*")
        assert deleted >= 2
        assert cache_get("pattern_a_1") is None
        assert cache_get("pattern_b_1") is not None
