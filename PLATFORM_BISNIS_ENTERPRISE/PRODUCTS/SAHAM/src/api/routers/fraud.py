"""
Fraud detection and anti-manipulation endpoints.
"""
import logging
import traceback
from fastapi import APIRouter, HTTPException, Query

from src.api.utils import _sanitize
from src.config import TICKERS

logger = logging.getLogger("saham.api.fraud")
router = APIRouter(prefix="/api/v1", tags=["Fraud Detection"])


@router.get("/fraud/check/{ticker}")
async def run_fraud_check(ticker: str, period: str = "6mo"):
    """
    Run full fraud detection (6-layer) for a ticker.

    Layers:
    1. Data quality validation
    2. Cross-source verification
    3. Index-constituent consistency
    4. News-price divergence
    5. Anti-manipulation metrics (Z-Score, Amihud, Beneish)
    6. Market manipulation (wash trading, spoofing)
    """
    from src.data_fetcher import fetch_yfinance_data
    from src.fraud_detection import FraudDetector

    target = TICKERS.get(ticker, ticker)
    df = fetch_yfinance_data(target, period=period)
    if df is None or df.empty:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    detector = FraudDetector()
    market_data = {target: df}

    results = {
        "ticker": target,
        "data_quality": detector.check_data_quality(market_data),
        "wash_trading": detector.check_wash_trading(market_data),
        "spoofing": detector.check_spoofing(market_data),
        "alerts": [{"type": a.alert_type, "severity": a.severity, "ticker": a.ticker, "message": a.message} for a in detector.alerts],
    }
    return _sanitize(results)


@router.get("/anti-manipulation/scan/{ticker}")
async def run_anti_manipulation_scan(ticker: str, period: str = "1y"):
    """
    Run anti-manipulation metrics scan (Blueprint Bab 3 & 4).

    Returns: Z-Score volume shock, Amihud illiquidity, wash trading, spoofing results.
    """
    from src.data_fetcher import fetch_yfinance_data
    from src.anti_manipulation import detect_wash_trading, detect_spoofing, calc_volume_shock, calc_amihud_illiquidity

    target = TICKERS.get(ticker, ticker)
    df = fetch_yfinance_data(target, period=period)
    if df is None or df.empty:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    results = {
        "ticker": target,
        "volume_shock": None,
        "illiquidity": None,
        "wash_trading": None,
        "spoofing": None,
    }

    try:
        vs = calc_volume_shock(df, ticker=target)
        results["volume_shock"] = {
            "is_anomaly": vs.is_anomaly,
            "z_score": vs.z_score,
            "severity": vs.severity,
            "description": vs.description,
        }
    except Exception as e:
        results["volume_shock"] = {"error": str(e)}

    try:
        il = calc_amihud_illiquidity(df, ticker=target)
        results["illiquidity"] = {
            "illiquidity_ratio": il.illiquidity_ratio,
            "is_illiquid": il.is_illiquid,
            "classification": il.classification,
            "description": il.description,
        }
    except Exception as e:
        results["illiquidity"] = {"error": str(e)}

    try:
        wt = detect_wash_trading(df, ticker=target)
        results["wash_trading"] = {
            "is_suspicious": wt.is_suspicious,
            "score": wt.score,
            "description": wt.description,
        }
    except Exception as e:
        results["wash_trading"] = {"error": str(e)}

    try:
        sp = detect_spoofing(df, ticker=target)
        results["spoofing"] = {
            "is_suspicious": sp.is_suspicious,
            "score": sp.score,
            "description": sp.description,
        }
    except Exception as e:
        results["spoofing"] = {"error": str(e)}

    return _sanitize(results)


@router.post("/fraud/fake-news-hype")
async def check_fake_news_hype(
    ticker: str,
    price_change_pct: float,
    volume_z_score: float,
    news_sentiment: float,
    news_count: int = 0,
    insider_selling: float = None,
    social_hype_score: float = None,
):
    """
    Check for fake news hype pattern (Blueprint Bab 3.2).

    Detects coordinated positive sentiment during distribution phases.
    """
    from src.fraud_detection import FraudDetector

    target = TICKERS.get(ticker, ticker)
    detector = FraudDetector()
    result = detector.check_fake_news_hype(
        ticker=target,
        price_change_pct=price_change_pct,
        volume_z_score=volume_z_score,
        news_sentiment=news_sentiment,
        news_count=news_count,
        insider_selling=insider_selling,
        social_hype_score=social_hype_score,
    )
    return _sanitize(result)
