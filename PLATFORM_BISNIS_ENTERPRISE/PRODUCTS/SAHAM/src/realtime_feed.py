"""
Real-Time Data Feed dengan Polling & Caching.

Menyediakan data near-real-time melalui polling Yahoo Finance
dengan caching untuk mengurangi API calls. Mendukung:
- Intraday polling (1m, 5m, 15m, 30m, 1h intervals)
- Daily data dengan caching
- Price alerts dan monitoring
- Auto-refresh dengan configurable interval

Referensi:
- Yahoo Finance API rate limits
- Cache-first pattern untuk data feeds
- WebSocket alternative via polling
"""

import yfinance as yf
import pandas as pd
import time
import os
import json
from datetime import datetime
from typing import Dict, Optional, List, Callable
from .config import TICKERS, DATA_DIR
from .rate_limiter import get_yf_limiter


class RealTimeFeed:
    """
    Near-real-time data feed via polling Yahoo Finance.
    Caches results to minimize API calls.
    """

    def __init__(self, interval: str = "5m", cache_ttl: int = 60):
        """
        Args:
            interval: 1m, 5m, 15m, 30m, 1h, 1d
            cache_ttl: Cache time-to-live in seconds
        """
        self.interval = interval
        self.cache_ttl = cache_ttl
        self._cache: Dict[str, dict] = {}
        self._cache_file = os.path.join(DATA_DIR, "realtime_cache.json")
        self._load_cache()

    def _load_cache(self):
        if os.path.exists(self._cache_file):
            try:
                with open(self._cache_file, "r") as f:
                    self._cache = json.load(f)
            except (json.JSONDecodeError, IOError):
                self._cache = {}

    def _save_cache(self):
        try:
            with open(self._cache_file, "w") as f:
                json.dump(self._cache, f, default=str)
        except IOError:
            pass

    def _is_cache_valid(self, ticker: str) -> bool:
        if ticker not in self._cache:
            return False
        cached_at = self._cache[ticker].get("timestamp", 0)
        return (time.time() - cached_at) < self.cache_ttl

    def get_realtime_price(self, ticker: str) -> Optional[dict]:
        """Get real-time price for a single ticker."""
        if self._is_cache_valid(ticker):
            return self._cache[ticker]["data"]

        try:
            limiter = get_yf_limiter()
            limiter.acquire()
            data = yf.download(
                ticker, period="1d", interval=self.interval,
                progress=False, prepost=True, auto_adjust=True,
            )
            if isinstance(data.columns, pd.MultiIndex):
                data.columns = data.columns.get_level_values(0)
            if data.empty:
                return None

            latest = data.iloc[-1]
            result = {
                "ticker": ticker,
                "price": float(latest["Close"]),
                "open": float(latest["Open"]),
                "high": float(latest["High"]),
                "low": float(latest["Low"]),
                "volume": float(latest["Volume"]),
                "timestamp": datetime.now().isoformat(),
                "interval": self.interval,
            }

            self._cache[ticker] = {
                "data": result,
                "timestamp": time.time(),
            }
            self._save_cache()
            return result

        except Exception as e:
            print(f"[ERROR] Realtime fetch {ticker}: {e}")
            return None

    def get_all_realtime(self, tickers: Optional[Dict[str, str]] = None) -> Dict[str, dict]:
        """Get real-time prices for all configured tickers."""
        tickers = tickers or TICKERS
        results = {}
        for name, ticker in tickers.items():
            data = self.get_realtime_price(ticker)
            if data:
                results[name] = data
        return results

    def get_intraday_history(
        self, ticker: str, period: str = "1d", interval: str = "5m",
    ) -> pd.DataFrame:
        """Get intraday historical data."""
        try:
            data = yf.download(
                ticker, period=period, interval=interval, progress=False,
                auto_adjust=True,
            )
            if isinstance(data.columns, pd.MultiIndex):
                data.columns = data.columns.get_level_values(0)
            return data
        except Exception as e:
            print(f"[ERROR] Intraday history {ticker}: {e}")
            return pd.DataFrame()

    def monitor(
        self,
        tickers: Dict[str, str],
        callback: Callable,
        interval_seconds: int = 60,
        max_iterations: int = 0,
    ):
        """
        Continuous monitoring loop — calls callback on each tick.

        Args:
            tickers: Dict of name -> ticker symbol
            callback: Function called with (ticker_name, price_data)
            interval_seconds: Polling interval
            max_iterations: 0 = infinite, N = stop after N polls
        """
        iteration = 0
        while max_iterations == 0 or iteration < max_iterations:
            for name, ticker in tickers.items():
                data = self.get_realtime_price(ticker)
                if data:
                    callback(name, data)
            iteration += 1
            if max_iterations == 0 or iteration < max_iterations:
                time.sleep(interval_seconds)


class PriceAlert:
    """Price alert system — triggers callback when conditions met.
    
    Persists alerts to SQLite alerts table for survival across restarts.
    """

    _COND_MAP = {
        "above": "PRICE_ABOVE",
        "below": "PRICE_BELOW",
        "crosses_up": "PRICE_ABOVE",
        "crosses_down": "PRICE_BELOW",
    }

    def __init__(self, load_from_db: bool = True):
        self._alerts: List[dict] = []
        if load_from_db:
            self._load_from_db()

    def _load_from_db(self):
        """Load active, untriggered alerts from database."""
        try:
            from .database import get_active_alerts
            df = get_active_alerts()
            for _, row in df.iterrows():
                cond_text = str(row.get("condition_text", ""))
                cond = "above" if "above" in cond_text else "below" if "below" in cond_text else cond_text
                self._alerts.append({
                    "id": int(row["id"]),
                    "ticker": row["ticker"],
                    "condition": cond,
                    "target_price": float(row["condition_value"]) if row.get("condition_value") else 0,
                    "callback": None,
                    "triggered": False,
                    "created_at": str(row.get("created_at", "")),
                })
        except Exception as e:
            print(f"[WARNING] PriceAlert: could not load from DB: {e}")

    def add_alert(
        self,
        ticker: str,
        condition: str,  # "above", "below", "crosses_up", "crosses_down"
        target_price: float,
        callback: Optional[Callable] = None,
    ):
        alert_type = self._COND_MAP.get(condition, "PRICE_ABOVE")
        condition_text = f"{condition} {target_price}"
        
        alert_id = None
        try:
            from .database import simpan_alert
            alert_id = simpan_alert(
                ticker=ticker,
                alert_type=alert_type,
                condition_value=target_price,
                condition_text=condition_text,
            )
        except Exception as e:
            print(f"[WARNING] PriceAlert: could not persist to DB: {e}")

        self._alerts.append({
            "id": alert_id,
            "ticker": ticker,
            "condition": condition,
            "target_price": target_price,
            "callback": callback,
            "triggered": False,
            "created_at": datetime.now().isoformat(),
        })

    def check_alerts(self, current_prices: Dict[str, dict]):
        """Check all alerts against current prices."""
        from .database import trigger_alert as db_trigger
        triggered = []
        for alert in self._alerts:
            if alert["triggered"]:
                continue
            ticker = alert["ticker"]
            if ticker not in current_prices:
                continue

            price = current_prices[ticker]["price"]
            target = alert["target_price"]
            cond = alert["condition"]

            should_trigger = False
            if cond == "above" and price > target:
                should_trigger = True
            elif cond == "below" and price < target:
                should_trigger = True
            elif cond == "crosses_up" and price > target:
                should_trigger = True
            elif cond == "crosses_down" and price < target:
                should_trigger = True

            if should_trigger:
                alert["triggered"] = True
                alert["triggered_at"] = datetime.now().isoformat()
                alert["triggered_price"] = price
                msg = f"{ticker} {cond} {target}: triggered at {price}"
                if alert.get("id"):
                    try:
                        db_trigger(alert["id"], message=msg)
                    except Exception as e:
                        print(f"[WARNING] PriceAlert: could not update DB: {e}")
                triggered.append(alert)
                if alert["callback"]:
                    alert["callback"](alert)

        return triggered

    def get_active_alerts(self) -> List[dict]:
        return [a for a in self._alerts if not a["triggered"]]

    def get_triggered_alerts(self) -> List[dict]:
        return [a for a in self._alerts if a["triggered"]]

    def remove_alert(self, alert_index: int):
        """Remove an alert by index in the in-memory list."""
        if 0 <= alert_index < len(self._alerts):
            alert = self._alerts.pop(alert_index)
            if alert.get("id"):
                try:
                    from .database import delete_alert
                    delete_alert(alert["id"])
                except Exception as e:
                    print(f"[WARNING] PriceAlert: could not delete from DB: {e}")


def get_market_status() -> Dict:
    """Check if IDX/BEI market is currently open."""
    now = datetime.now()
    # IDX trading hours: 09:00 - 15:00 WIB (Mon-Fri)
    is_weekday = now.weekday() < 5
    current_time = now.hour * 100 + now.minute
    is_trading_hours = 900 <= current_time <= 1500

    # Pre-open: 08:45 - 09:00
    is_pre_open = is_weekday and 845 <= current_time < 900

    # Post-close: 15:00 - 15:15
    is_post_close = is_weekday and 1500 < current_time <= 1515

    return {
        "is_open": is_weekday and is_trading_hours,
        "is_pre_open": is_pre_open,
        "is_post_close": is_post_close,
        "is_weekend": not is_weekday,
        "current_time_wib": now.strftime("%H:%M"),
        "next_open": "09:00 WIB" if not is_trading_hours else "Now",
        "next_close": "15:00 WIB" if is_trading_hours else "Closed",
    }
