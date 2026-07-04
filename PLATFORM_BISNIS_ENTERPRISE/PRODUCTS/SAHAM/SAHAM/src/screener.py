"""
Multi-Stock Screener — batch prediction & ranking for Indonesian blue chips.

Runs lightweight prediction for all BLUE_CHIPS_ID stocks simultaneously,
ranks by composite AI score, and surfaces top BUY/SELL opportunities.

Usage:
    from src.screener import run_screener, format_screener_results
    results = run_screener(market_data, fred_data)
    top_buys = results["top_buys"]
    top_sells = results["top_sells"]
"""
from __future__ import annotations

import logging
import time
from dataclasses import dataclass, field
from typing import Dict, List, Optional

import numpy as np
import pandas as pd

from .config import BLUE_CHIPS_ID
from .data_fetcher import fetch_yfinance_data

logger = logging.getLogger(__name__)


@dataclass
class ScreenerEntry:
    """Single stock screening result."""
    ticker: str = ""
    name: str = ""
    current_price: float = 0.0
    predicted_direction: str = "NEUTRAL"
    signal: str = "HOLD"
    confidence: float = 0.0
    ai_score: float = 0.0
    rsi: float = 50.0
    ma_trend: str = "sideways"
    change_pct: float = 0.0
    sector: str = ""
    error: str = ""


@dataclass
class ScreenerResult:
    """Combined screener results."""
    timestamp: str = ""
    entries: List[ScreenerEntry] = field(default_factory=list)
    top_buys: List[ScreenerEntry] = field(default_factory=list)
    top_sells: List[ScreenerEntry] = field(default_factory=list)
    n_success: int = 0
    n_errors: int = 0
    duration_seconds: float = 0.0

    def summary(self) -> str:
        return (
            f"Screener: {self.n_success} stocks scanned, "
            f"{len(self.top_buys)} BUY signals, {len(self.top_sells)} SELL signals, "
            f"{self.duration_seconds:.1f}s"
        )


# Import sector mapping from config (100 IDX tickers)
from src.config import SAHAM_IDX_SECTORS

# Convert SAHAM_IDX_SECTORS to flat ticker -> sector mapping
_SECTOR_MAP = {}
for sector, tickers in SAHAM_IDX_SECTORS.items():
    for ticker in tickers:
        _SECTOR_MAP[ticker] = sector


def _quick_screen_single(
    ticker: str,
    name: str,
    df: pd.DataFrame,
) -> ScreenerEntry:
    """Run lightweight screening on a single stock."""
    entry = ScreenerEntry(ticker=ticker, name=name)

    if df is None or df.empty or len(df) < 50:
        entry.error = "Insufficient data"
        return entry

    try:
        close = df["Close"]
        entry.current_price = round(float(close.iloc[-1]), 2)

        if len(close) >= 2:
            entry.change_pct = round(float((close.iloc[-1] / close.iloc[-2] - 1) * 100), 2)

        # RSI
        delta = close.diff()
        gain = delta.where(delta > 0, 0.0)
        loss = -delta.where(delta < 0, 0.0)
        avg_gain = gain.rolling(14, min_periods=14).mean()
        avg_loss = loss.rolling(14, min_periods=14).mean()
        rs = avg_gain / avg_loss
        rsi = 100 - (100 / (1 + rs))
        entry.rsi = round(float(rsi.iloc[-1]) if not np.isnan(rsi.iloc[-1]) else 50, 1)

        # MA trend
        ma5 = close.rolling(5).mean().iloc[-1]
        ma10 = close.rolling(10).mean().iloc[-1]
        ma20 = close.rolling(20).mean().iloc[-1]

        if ma5 > ma10 > ma20:
            entry.ma_trend = "bullish"
        elif ma5 < ma10 < ma20:
            entry.ma_trend = "bearish"
        else:
            entry.ma_trend = "sideways"

        # Simple signal logic
        score = 50.0
        if entry.ma_trend == "bullish":
            score += 15
        elif entry.ma_trend == "bearish":
            score -= 15

        if entry.rsi < 30:
            score += 10  # Oversold → potential buy
        elif entry.rsi > 70:
            score -= 10  # Overbought → potential sell

        if entry.change_pct > 2:
            score += 5
        elif entry.change_pct < -2:
            score -= 5

        entry.ai_score = round(max(0, min(100, score)), 1)

        if entry.ai_score >= 65:
            entry.signal = "BUY"
            entry.predicted_direction = "UP"
        elif entry.ai_score <= 35:
            entry.signal = "SELL"
            entry.predicted_direction = "DOWN"
        else:
            entry.signal = "HOLD"
            entry.predicted_direction = "NEUTRAL"

        entry.confidence = round(abs(entry.ai_score - 50) / 50, 2)
        entry.sector = _SECTOR_MAP.get(ticker, "")

    except Exception as e:
        entry.error = str(e)

    return entry


def run_screener(
    market_data: Optional[dict] = None,
    tickers: Optional[Dict[str, str]] = None,
    period: str = "6mo",
    top_n: int = 5,
) -> ScreenerResult:
    """
    Run multi-stock screener across Indonesian blue chips.

    Args:
        market_data: Pre-fetched market data dict (optional, will fetch if None)
        tickers: Custom ticker dict (default: BLUE_CHIPS_ID)
        period: Data fetch period if fetching needed
        top_n: Number of top BUY/SELL to return

    Returns:
        ScreenerResult with ranked entries
    """
    from datetime import datetime
    start_time = time.time()

    result = ScreenerResult(timestamp=datetime.now().isoformat())
    target_tickers = tickers if tickers else BLUE_CHIPS_ID

    for ticker, name in target_tickers.items():
        df = None
        if market_data and ticker in market_data:
            df = market_data[ticker]
        elif market_data and name in market_data:
            df = market_data[name]

        if df is None or df.empty:
            try:
                df = fetch_yfinance_data(ticker, period=period)
            except Exception as e:
                logger.warning(f"Failed to fetch {ticker}: {e}")
                df = None

        entry = _quick_screen_single(ticker, name, df)
        result.entries.append(entry)

        if entry.error:
            result.n_errors += 1
        else:
            result.n_success += 1

    # Sort and rank
    valid = [e for e in result.entries if not e.error]

    result.top_buys = sorted(
        [e for e in valid if e.signal == "BUY"],
        key=lambda x: x.ai_score,
        reverse=True,
    )[:top_n]

    result.top_sells = sorted(
        [e for e in valid if e.signal == "SELL"],
        key=lambda x: x.ai_score,
    )[:top_n]

    result.duration_seconds = round(time.time() - start_time, 1)

    logger.info(result.summary())
    return result


def format_screener_results(result: ScreenerResult) -> pd.DataFrame:
    """Convert screener results to DataFrame for display."""
    rows = []
    for e in result.entries:
        rows.append({
            "Ticker": e.ticker,
            "Name": e.name,
            "Price": e.current_price,
            "Change %": e.change_pct,
            "RSI": e.rsi,
            "MA Trend": e.ma_trend,
            "Signal": e.signal,
            "AI Score": e.ai_score,
            "Confidence": e.confidence,
            "Sector": e.sector,
            "Error": e.error,
        })
    return pd.DataFrame(rows)
