"""
Rate Limiter untuk yfinance dan API calls.

Mencegah HTTP 429 (Too Many Requests) dengan:
1. Token bucket rate limiting — maksimal N request per window
2. Minimum delay antar request
3. Burst protection — jika terkena 429, auto-backoff semua thread
4. Thread-safe untuk pemakaian parallel

Yahoo Finance rate limits (empirical):
- ~2000 requests/hour per IP
- ~200 requests/minute burst
- Recommended: 1 request per 2 seconds untuk safety
"""

import time
import threading
import logging
from collections import deque

# Suppress yfinance's noisy logging (404 for indices, "possibly delisted", etc.)
# These are expected for index tickers like ^JKSE and don't indicate real problems.
logging.getLogger("yfinance").setLevel(logging.CRITICAL)
logging.getLogger("urllib3").setLevel(logging.ERROR)


class RateLimiter:
    """
    Sliding window rate limiter.

    - max_calls: Maximum calls allowed in the time window
    - window_seconds: Time window in seconds
    - min_delay: Minimum delay between calls (seconds)
    """

    def __init__(
        self,
        max_calls: int = 30,
        window_seconds: int = 60,
        min_delay: float = 1.5,
    ):
        self.max_calls = max_calls
        self.window_seconds = window_seconds
        self.min_delay = min_delay
        self._calls: deque = deque()
        self._lock = threading.Lock()
        self._last_call_time: float = 0
        self._backoff_until: float = 0
        self._consecutive_429: int = 0

    def acquire(self):
        """Block until a request slot is available, then return."""
        with self._lock:
            # Check if we're in backoff mode (hit 429)
            now = time.time()
            if now < self._backoff_until:
                wait = self._backoff_until - now
                print(f"[RATE] Backoff active, waiting {wait:.1f}s...")
                time.sleep(wait)

            # Clean old timestamps outside the window
            cutoff = time.time() - self.window_seconds
            while self._calls and self._calls[0] < cutoff:
                self._calls.popleft()

            # If at capacity, wait until oldest call expires
            if len(self._calls) >= self.max_calls:
                wait = self._calls[0] + self.window_seconds - time.time()
                if wait > 0:
                    print(f"[RATE] Rate limit ({self.max_calls}/{self.window_seconds}s), waiting {wait:.1f}s...")
                    time.sleep(wait)
                # Clean again after waiting
                cutoff = time.time() - self.window_seconds
                while self._calls and self._calls[0] < cutoff:
                    self._calls.popleft()

            # Enforce minimum delay between calls
            elapsed = time.time() - self._last_call_time
            if elapsed < self.min_delay:
                delay = self.min_delay - elapsed
                time.sleep(delay)

            # Record this call
            now = time.time()
            self._calls.append(now)
            self._last_call_time = now

    def report_429(self):
        """Report that a 429 error was received. Triggers exponential backoff."""
        with self._lock:
            self._consecutive_429 += 1
            backoff_time = min(2 ** self._consecutive_429, 60)
            self._backoff_until = time.time() + backoff_time
            print(f"[RATE] HTTP 429 detected! Backing off for {backoff_time}s (attempt #{self._consecutive_429})")

    def report_success(self):
        """Report successful request. Resets 429 counter."""
        with self._lock:
            self._consecutive_429 = 0

    def stats(self) -> dict:
        """Get current rate limiter statistics."""
        with self._lock:
            now = time.time()
            cutoff = now - self.window_seconds
            active_calls = sum(1 for t in self._calls if t >= cutoff)
            return {
                "calls_in_window": active_calls,
                "max_calls": self.max_calls,
                "window_seconds": self.window_seconds,
                "min_delay": self.min_delay,
                "consecutive_429": self._consecutive_429,
                "in_backoff": now < self._backoff_until,
                "backoff_remaining": max(0, self._backoff_until - now),
            }


# Global rate limiter instances
_yf_limiter = RateLimiter(
    max_calls=60,
    window_seconds=60,
    min_delay=1.0,
)

_fred_limiter = RateLimiter(
    max_calls=120,
    window_seconds=60,
    min_delay=0.5,
)

_web_limiter = RateLimiter(
    max_calls=20,
    window_seconds=60,
    min_delay=2.0,
)

_av_limiter = RateLimiter(
    max_calls=5,
    window_seconds=60,
    min_delay=12.0,
)

_finnhub_limiter = RateLimiter(
    max_calls=60,
    window_seconds=60,
    min_delay=1.0,
)


def get_yf_limiter() -> RateLimiter:
    """Get the Yahoo Finance rate limiter."""
    return _yf_limiter


def get_fred_limiter() -> RateLimiter:
    """Get the FRED API rate limiter."""
    return _fred_limiter


def get_web_limiter() -> RateLimiter:
    """Get the web scraping rate limiter."""
    return _web_limiter


def get_av_limiter() -> RateLimiter:
    """Get the Alpha Vantage rate limiter."""
    return _av_limiter


def get_finnhub_limiter() -> RateLimiter:
    """Get the Finnhub rate limiter."""
    return _finnhub_limiter
