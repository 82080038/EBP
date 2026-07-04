"""
Additional Data Sources — Alpha Vantage & Finnhub integration.

Provides cross-source verification and richer data than yfinance alone.

Data Sources:
- Alpha Vantage: Free API key (500 req/day), global stocks, forex, crypto
- Finnhub: Free tier (60 req/min), real-time quotes, news, fundamentals

Usage:
    from src.alt_data_sources import AlphaVantageSource, FinnhubSource
    av = AlphaVantageSource(api_key="YOUR_KEY")
    df = av.fetch_daily("BBCA.JK")
    fh = FinnhubSource(api_key="YOUR_KEY")
    quote = fh.get_quote("AAPL")
"""
from __future__ import annotations

import logging
import os
from typing import Dict, Optional

import pandas as pd
import requests

from .rate_limiter import get_av_limiter, get_finnhub_limiter

logger = logging.getLogger(__name__)


class AlphaVantageSource:
    """
    Alpha Vantage data source — global stocks, forex, crypto, indicators.

    Get free API key: https://www.alphavantage.co/support/#api-key
    Rate limit: 500 requests/day (free), 75 req/min (premium)
    """

    BASE_URL = "https://www.alphavantage.co/query"

    def __init__(self, api_key: Optional[str] = None):
        self.api_key = api_key or os.getenv("ALPHAVANTAGE_API_KEY", "")
        self.limiter = get_av_limiter()

    def is_available(self) -> bool:
        return bool(self.api_key)

    def fetch_daily(self, symbol: str, outputsize: str = "compact") -> pd.DataFrame:
        """
        Fetch daily OHLCV data.

        Args:
            symbol: Ticker symbol (e.g., "BBCA.JK")
            outputsize: "compact" (100 days) or "full" (20 years)

        Returns:
            DataFrame with OHLCV columns
        """
        if not self.is_available():
            logger.warning("Alpha Vantage API key not set, using yfinance fallback")
            return self._fetch_yfinance_fallback(symbol, outputsize)

        self.limiter.acquire()
        try:
            resp = requests.get(self.BASE_URL, params={
                "function": "TIME_SERIES_DAILY",
                "symbol": symbol,
                "outputsize": outputsize,
                "apikey": self.api_key,
            }, timeout=30)
            self.limiter.report_success()

            data = resp.json()
            key = "Time Series (Daily)"
            if key not in data:
                if "Note" in data:
                    logger.warning(f"Alpha Vantage rate limit: {data['Note']}, using yfinance fallback")
                return self._fetch_yfinance_fallback(symbol, outputsize)

            rows = []
            for date, values in data[key].items():
                rows.append({
                    "Date": date,
                    "Open": float(values["1. open"]),
                    "High": float(values["2. high"]),
                    "Low": float(values["3. low"]),
                    "Close": float(values["4. close"]),
                    "Volume": int(values["5. volume"]),
                })

            df = pd.DataFrame(rows)
            df["Date"] = pd.to_datetime(df["Date"])
            df = df.set_index("Date").sort_index()
            return df

        except Exception as e:
            logger.error(f"Alpha Vantage fetch failed for {symbol}: {e}, using yfinance fallback")
            return self._fetch_yfinance_fallback(symbol, outputsize)
    
    def _fetch_yfinance_fallback(self, symbol: str, outputsize: str) -> pd.DataFrame:
        """Fallback to yfinance when Alpha Vantage is unavailable."""
        try:
            import yfinance as yf
            period = "1y" if outputsize == "full" else "3mo"
            ticker = yf.Ticker(symbol)
            df = ticker.history(period=period)
            
            if df.empty:
                return pd.DataFrame()
            
            # Rename columns to match Alpha Vantage format
            df.columns = [col.capitalize() for col in df.columns]
            return df
        except Exception as e:
            logger.error(f"yfinance fallback failed: {e}")
            return pd.DataFrame()

    def fetch_intraday(self, symbol: str, interval: str = "5min") -> pd.DataFrame:
        """Fetch intraday data."""
        if not self.is_available():
            return pd.DataFrame()

        self.limiter.acquire()
        try:
            resp = requests.get(self.BASE_URL, params={
                "function": "TIME_SERIES_INTRADAY",
                "symbol": symbol,
                "interval": interval,
                "apikey": self.api_key,
            }, timeout=30)
            self.limiter.report_success()

            data = resp.json()
            key = f"Time Series ({interval})"
            if key not in data:
                return pd.DataFrame()

            rows = []
            for ts, values in data[key].items():
                rows.append({
                    "Datetime": ts,
                    "Open": float(values["1. open"]),
                    "High": float(values["2. high"]),
                    "Low": float(values["3. low"]),
                    "Close": float(values["4. close"]),
                    "Volume": int(values["5. volume"]),
                })

            df = pd.DataFrame(rows)
            df["Datetime"] = pd.to_datetime(df["Datetime"])
            df = df.set_index("Datetime").sort_index()
            return df

        except Exception as e:
            logger.error(f"Alpha Vantage intraday failed: {e}")
            return pd.DataFrame()

    def get_global_quote(self, symbol: str) -> Dict:
        """Get real-time quote."""
        if not self.is_available():
            return {}

        self.limiter.acquire()
        try:
            resp = requests.get(self.BASE_URL, params={
                "function": "GLOBAL_QUOTE",
                "symbol": symbol,
                "apikey": self.api_key,
            }, timeout=30)
            self.limiter.report_success()
            data = resp.json().get("Global Quote", {})
            return {
                "symbol": data.get("01. symbol", ""),
                "price": float(data.get("05. price", 0)),
                "change": float(data.get("09. change", 0)),
                "change_pct": float(data.get("10. change percent", "0%").strip("%")),
                "volume": int(data.get("06. volume", 0)),
            }
        except Exception as e:
            logger.error(f"Alpha Vantage quote failed: {e}")
            return {}


class FinnhubSource:
    """
    Finnhub data source — real-time quotes, news, fundamentals.

    Get free API key: https://finnhub.io/register
    Rate limit: 60 req/min (free)
    """

    BASE_URL = "https://finnhub.io/api/v1"

    def __init__(self, api_key: Optional[str] = None):
        self.api_key = api_key or os.getenv("FINNHUB_API_KEY", "")
        self.limiter = get_finnhub_limiter()

    def is_available(self) -> bool:
        return bool(self.api_key)

    def get_quote(self, symbol: str) -> Dict:
        """Get real-time quote."""
        if not self.is_available():
            logger.warning("Finnhub API key not set")
            return {}

        self.limiter.acquire()
        try:
            resp = requests.get(f"{self.BASE_URL}/quote", params={
                "symbol": symbol,
                "token": self.api_key,
            }, timeout=15)
            self.limiter.report_success()
            data = resp.json()
            return {
                "symbol": symbol,
                "current": float(data.get("c", 0)),
                "change": float(data.get("d", 0)),
                "change_pct": float(data.get("dp", 0)),
                "high": float(data.get("h", 0)),
                "low": float(data.get("l", 0)),
                "open": float(data.get("o", 0)),
                "prev_close": float(data.get("pc", 0)),
                "timestamp": data.get("t", 0),
            }
        except Exception as e:
            logger.error(f"Finnhub quote failed: {e}")
            return {}

    def get_company_news(self, symbol: str, from_date: str = "", to_date: str = "") -> list:
        """Get company news."""
        if not self.is_available():
            return []

        if not from_date:
            from datetime import datetime, timedelta
            from_date = (datetime.now() - timedelta(days=7)).strftime("%Y-%m-%d")
        if not to_date:
            to_date = datetime.now().strftime("%Y-%m-%d")

        self.limiter.acquire()
        try:
            resp = requests.get(f"{self.BASE_URL}/company-news", params={
                "symbol": symbol,
                "from": from_date,
                "to": to_date,
                "token": self.api_key,
            }, timeout=15)
            self.limiter.report_success()
            return resp.json()[:20]  # Limit to 20 articles
        except Exception as e:
            logger.error(f"Finnhub news failed: {e}")
            return []

    def get_company_profile(self, symbol: str) -> Dict:
        """Get company profile/fundamentals."""
        if not self.is_available():
            return {}

        self.limiter.acquire()
        try:
            resp = requests.get(f"{self.BASE_URL}/stock/profile2", params={
                "symbol": symbol,
                "token": self.api_key,
            }, timeout=15)
            self.limiter.report_success()
            return resp.json()
        except Exception as e:
            logger.error(f"Finnhub profile failed: {e}")
            return {}

    def get_financials(self, symbol: str, statement: str = "ic") -> Dict:
        """
        Get financial statements.

        Args:
            symbol: Stock symbol
            statement: "ic" (income), "bs" (balance), "cf" (cash flow)
        """
        if not self.is_available():
            return {}

        self.limiter.acquire()
        try:
            resp = requests.get(f"{self.BASE_URL}/stock/financials-reported", params={
                "symbol": symbol,
                "freq": "annual",
                "token": self.api_key,
            }, timeout=15)
            self.limiter.report_success()
            return resp.json()
        except Exception as e:
            logger.error(f"Finnhub financials failed: {e}")
            return {}


def cross_source_verify(
    yf_price: float,
    av_price: float,
    fh_price: float,
    tolerance_pct: float = 2.0,
) -> Dict:
    """
    Cross-verify prices from multiple sources.

    Returns dict with verification results.
    """
    prices = {"yfinance": yf_price, "alpha_vantage": av_price, "finnhub": fh_price}
    valid = {k: v for k, v in prices.items() if v > 0}

    if len(valid) < 2:
        return {"verified": True, "reason": "Only one source available", "prices": prices}

    values = list(valid.values())
    max_diff = max(values) - min(values)
    avg_price = sum(values) / len(values)
    diff_pct = (max_diff / avg_price) * 100 if avg_price > 0 else 0

    return {
        "verified": diff_pct <= tolerance_pct,
        "max_diff_pct": round(diff_pct, 2),
        "avg_price": round(avg_price, 2),
        "prices": prices,
        "reason": "Prices match" if diff_pct <= tolerance_pct else f"Price discrepancy: {diff_pct:.2f}%",
    }


def get_multi_source_price(symbol: str) -> Dict:
    """
    Get price from multiple sources and verify.
    
    Args:
        symbol: Stock symbol
        
    Returns:
        Dict with verified price and source information.
    """
    import yfinance as yf
    
    # Fetch from yfinance
    yf_price = 0
    try:
        ticker = yf.Ticker(symbol)
        yf_price = ticker.history(period="1d")["Close"].iloc[-1]
    except Exception:
        pass
    
    # Fetch from Alpha Vantage
    av = AlphaVantageSource()
    av_quote = av.get_global_quote(symbol)
    av_price = av_quote.get("price", 0)
    
    # Fetch from Finnhub
    fh = FinnhubSource()
    fh_quote = fh.get_quote(symbol)
    fh_price = fh_quote.get("current", 0)
    
    # Verify
    verification = cross_source_verify(yf_price, av_price, fh_price)
    
    return {
        "symbol": symbol,
        "verified_price": verification.get("avg_price", yf_price),
        "verification": verification,
        "sources": {
            "yfinance": yf_price,
            "alpha_vantage": av_price,
            "finnhub": fh_price,
        },
    }
