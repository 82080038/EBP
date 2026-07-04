"""
Prediction endpoints: get prediction, run prediction, accuracy.
"""
import logging
import traceback
from fastapi import APIRouter, HTTPException, Query, Request

from src.api.models import PredictionResponse, AccuracyResponse
from src.api.utils import _cached_response, _cache_response, _sanitize, TTL
from src.config import TICKERS, TARGET_TICKER

logger = logging.getLogger("saham.api.prediction")
router = APIRouter(prefix="/api/v1", tags=["Prediction"])


@router.get("/predict/{ticker}", response_model=PredictionResponse)
async def get_prediction(request: Request, ticker: str = "IHSG"):
    """
    Get latest prediction for a ticker.
    Uses cached prediction from database if available, plus short-term API cache.
    """
    # Try API cache first
    cached = await _cached_response(request, ttl=TTL.SHORT)
    if cached is not None:
        return cached

    from src.database import get_all_prediksi

    target = TICKERS.get(ticker, ticker)
    df = get_all_prediksi()

    if df.empty:
        raise HTTPException(status_code=404, detail=f"No predictions found for {ticker}")

    latest = df.iloc[-1]
    response_data = {
        "ticker": target,
        "current_price": float(latest.get("harga_saat_ini", 0)),
        "predicted_price": float(latest.get("harga_prediksi", 0)),
        "direction": latest.get("arah_prediksi", "UNKNOWN"),
        "signal": latest.get("sinyal", "HOLD"),
        "confidence": float(latest.get("confidence", 0)),
        "model_votes": {},
        "rules": latest.get("model_votes", ""),
        "timestamp": str(latest.get("tanggal_prediksi", "")),
    }
    _cache_response(request, response_data, ttl=TTL.SHORT)
    return PredictionResponse(**response_data)


@router.post("/predict")
async def run_prediction(
    ticker: str = Query(default="IHSG", description="Ticker symbol"),
    period: str = Query(default="2y", description="Data period"),
):
    """Run a new prediction."""
    logger.info(f"[PREDICT] ticker={ticker}, period={period}")
    from src.data_fetcher import fetch_all_data
    from src.predictor import run_prediction as _run_prediction

    target = TICKERS.get(ticker, TARGET_TICKER)
    logger.debug(f"[PREDICT] resolved target={target}")

    data = fetch_all_data(period=period)
    market_data = data.get("market", {})

    if not market_data:
        logger.error(f"[PREDICT] No market data returned for ticker={ticker}")
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    logger.debug(f"[PREDICT] market_data keys={list(market_data.keys())[:10]}, fred={'present' if data.get('fred') else 'missing'}")

    try:
        result = _run_prediction(market_data, fred_data=data.get("fred"), target_ticker=target)
    except Exception as e:
        logger.error(f"[PREDICT] run_prediction failed: {e}\n{traceback.format_exc()}")
        raise HTTPException(status_code=500, detail=f"Prediction engine error: {e}")

    if "error" in result:
        logger.error(f"[PREDICT] result has error: {result['error']}")
        raise HTTPException(status_code=500, detail=result["error"])

    logger.info(f"[PREDICT] OK signal={result['sinyal']}, confidence={result['confidence']:.2f}, price={result['current_price']}")
    return _sanitize(result)


@router.get("/accuracy", response_model=AccuracyResponse)
async def get_accuracy():
    """Get prediction accuracy metrics."""
    from src.database import get_akurasi_metrics

    metrics = get_akurasi_metrics()
    return AccuracyResponse(
        total=metrics.get("total", 0),
        benar=metrics.get("benar", 0),
        salah=metrics.get("salah", 0),
        directional_accuracy=metrics.get("directional_accuracy", 0),
        mape=metrics.get("mape"),
    )
