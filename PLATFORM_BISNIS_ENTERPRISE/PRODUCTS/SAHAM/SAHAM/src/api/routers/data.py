"""
Fundamental and technical data endpoints.
"""
import logging
from fastapi import APIRouter, HTTPException, Query

from src.api.utils import _sanitize
from src.config import TICKERS

logger = logging.getLogger("saham.api.data")
router = APIRouter(prefix="/api/v1", tags=["Fundamental & Technical"])


@router.get("/fundamental/{ticker}")
async def get_fundamental(ticker: str):
    """Get latest fundamental data snapshot for a ticker."""
    from src.database import load_fundamental
    target = TICKERS.get(ticker, ticker)
    data = load_fundamental(target)
    if not data:
        raise HTTPException(status_code=404, detail=f"No fundamental data for {ticker}")
    return _sanitize(data)


@router.get("/fundamental")
async def get_all_fundamentals():
    """Get latest fundamental data for all tickers."""
    from src.database import load_all_fundamentals
    df = load_all_fundamentals()
    if df.empty:
        return []
    return _sanitize(df.to_dict(orient="records"))


@router.get("/indicators/{ticker}")
async def get_technical_indicators(ticker: str, limit: int = 30):
    """Get technical indicator snapshots for a ticker."""
    from src.database import load_technical_indicators
    target = TICKERS.get(ticker, ticker)
    df = load_technical_indicators(target, limit=limit)
    if df.empty:
        raise HTTPException(status_code=404, detail=f"No technical indicators for {ticker}")
    return _sanitize(df.to_dict(orient="records"))


@router.get("/indicators/{ticker}/latest")
async def get_latest_technical_indicators(ticker: str):
    """Get most recent technical indicator snapshot for a ticker."""
    from src.database import load_latest_technical_indicators
    target = TICKERS.get(ticker, ticker)
    data = load_latest_technical_indicators(target)
    if not data:
        raise HTTPException(status_code=404, detail=f"No technical indicators for {ticker}")
    return _sanitize(data)


@router.get("/financial-ratios/{ticker}")
async def get_financial_ratios(ticker: str, limit: int = 20):
    """Get financial ratio history for a ticker."""
    from src.database import load_financial_ratios
    target = TICKERS.get(ticker, ticker)
    df = load_financial_ratios(target, limit=limit)
    if df.empty:
        raise HTTPException(status_code=404, detail=f"No financial ratios for {ticker}")
    return _sanitize(df.to_dict(orient="records"))
