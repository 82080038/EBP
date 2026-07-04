"""
Frontend endpoints for Next.js Command Center: OHLCV, watchlist, orderbook, market summary, macro indicators.
"""
import logging
import random
import traceback
from datetime import datetime, timedelta
from fastapi import APIRouter, HTTPException, Query, Request

from src.api.utils import _cached_response, _cache_response, _sanitize, _resolve_ticker, TTL
from src.config import TICKERS, TARGET_TICKER, FRED_SERIES, BLUE_CHIPS_ID

logger = logging.getLogger("saham.api.frontend")
router = APIRouter(prefix="/api/v1", tags=["Frontend"])


@router.get("/stock/{ticker}/ohlcv")
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


@router.get("/screener/watchlist")
async def get_watchlist():
    """Get screener results for watchlist."""
    from src.data_fetcher import fetch_all_market_data
    from src.screener import run_screener, format_screener_results

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


@router.get("/stock/{ticker}/orderbook")
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


@router.get("/market/summary")
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


@router.get("/macro/indicators")
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
