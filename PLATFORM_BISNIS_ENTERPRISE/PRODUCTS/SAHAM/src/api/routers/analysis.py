"""
Analysis endpoints: patterns, sentiment, briefing, score.
"""
import logging
import traceback
from fastapi import APIRouter, HTTPException, Query

from src.api.models import PatternResponse, SentimentResponse, BriefingResponse, ScoreResponse
from src.api.utils import _sanitize, _resolve_ticker
from src.config import TICKERS, TARGET_TICKER

logger = logging.getLogger("saham.api.analysis")
router = APIRouter(prefix="/api/v1", tags=["Analysis"])


@router.get("/patterns/{ticker}", response_model=PatternResponse)
async def get_patterns(ticker: str = "IHSG", period: str = "6mo"):
    """Get pattern analysis for a ticker."""
    from src.data_fetcher import fetch_all_market_data
    from src.patterns import full_pattern_analysis

    target = TICKERS.get(ticker, TARGET_TICKER)
    market_data = fetch_all_market_data(period=period)

    if not market_data or (ticker not in market_data and target not in market_data):
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    df = market_data.get(ticker, market_data.get(target))
    result = full_pattern_analysis(df)

    return PatternResponse(
        candlestick_patterns=[{"name": p.name, "type": p.type, "confidence": p.confidence, "date": p.date} for p in result["candlestick_patterns"]],
        chart_patterns=[{"name": p.name, "type": p.type, "confidence": p.confidence, "description": p.description} for p in result["chart_patterns"]],
        market_structure={"structure": result["market_structure"].structure, "description": result["market_structure"].description},
        volume_anomalies=[{"date": a.date, "type": a.anomaly_type, "z_score": a.z_score} for a in result["volume_anomalies"]],
        trendlines=[{"type": t.type, "slope": t.slope, "touches": t.touches} for t in result["trendlines"]],
        summary=result["summary"],
    )


@router.get("/sentiment", response_model=SentimentResponse)
async def get_sentiment():
    """Get Fear & Greed sentiment analysis."""
    from src.data_fetcher import fetch_all_market_data
    from src.sentiment import calc_fear_greed_index

    market_data = fetch_all_market_data(period="6mo")
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    fg = calc_fear_greed_index(market_data)
    return SentimentResponse(
        composite_score=fg["composite_score"],
        label=fg["label"],
        emoji=fg.get("emoji"),
        advice=fg.get("advice"),
        components=fg["components"],
    )


@router.get("/briefing", response_model=BriefingResponse)
async def get_briefing():
    """Get daily AI multi-agent briefing."""
    from src.data_fetcher import fetch_all_data
    from src.ai_agent import generate_daily_briefing
    from src.predictor import run_prediction

    data = fetch_all_data(period="2y")
    market_data = data.get("market", {})
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    try:
        result = run_prediction(market_data, fred_data=data.get("fred"))
        signal = result.get("sinyal", "HOLD")
        confidence = result.get("confidence", 0.5)
        predictions = result.get("predictions", {})
        probabilities = result.get("probabilities", {})
    except Exception:
        signal = "HOLD"
        confidence = 0.5
        predictions = {}
        probabilities = {}

    from src.preprocessor import prepare_features
    df = prepare_features(market_data, fred_data=data.get("fred"))

    briefing = generate_daily_briefing(
        market_data, df, signal, confidence, predictions, probabilities
    )

    agents = briefing.get("agents", [])
    bull_case = next((a.findings for a in agents if "bull" in a.agent_name.lower() or signal == "BUY"), None)
    bear_case = next((a.findings for a in agents if "bear" in a.agent_name.lower() or signal == "SELL"), None)

    return BriefingResponse(
        date=briefing["date"],
        market_summary=briefing["market_summary"],
        signal=briefing["signal"],
        confidence=briefing["confidence"],
        final_recommendation=briefing["final_recommendation"].findings,
        actionable_items=briefing["actionable_items"],
        risk_assessment=briefing["risk_assessment"],
        bull_case=bull_case,
        bear_case=bear_case,
        debate={a.agent_name: a.findings for a in agents} if agents else None,
    )


@router.get("/score/{ticker}", response_model=ScoreResponse)
async def get_score(ticker: str = "IHSG"):
    """Get composite AI score for a ticker."""
    from src.data_fetcher import fetch_all_data
    from src.scoring import calc_composite_ai_score
    from src.predictor import run_prediction

    target = TICKERS.get(ticker, TARGET_TICKER)
    data = fetch_all_data(period="2y")
    market_data = data.get("market", {})

    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    try:
        result = run_prediction(market_data, fred_data=data.get("fred"), target_ticker=target)
        predictions = result.get("predictions", {})
        probabilities = result.get("probabilities", {})
        confidence = result.get("confidence", 0.5)
    except Exception:
        predictions = {}
        probabilities = {}
        confidence = 0.5

    from src.preprocessor import prepare_features
    df = prepare_features(market_data, fred_data=data.get("fred"), target_ticker=target)
    latest = df.iloc[-1]

    rsi = float(latest.get("Target_RSI", 50))
    vix = float(latest.get("VIX_Close", 20))
    fear_greed = float(latest.get("Fear_Greed_Index", 50))

    ma5 = float(latest.get("IHSG_MA5", 0))
    ma10 = float(latest.get("IHSG_MA10", 0))
    ma20 = float(latest.get("IHSG_MA20", 0))
    ma_alignment = 1.0 if ma5 > ma10 > ma20 else (-1.0 if ma5 < ma10 < ma20 else 0.0)

    macd = float(latest.get("Target_MACD", 0))
    macd_signal = float(latest.get("Target_MACD_Signal", 0))
    macd_val = 1.0 if macd > macd_signal else (-1.0 if macd < macd_signal else 0.0)

    bb_pct = float(latest.get("Target_BB_Pct", 0.5))

    score = calc_composite_ai_score(
        predictions=predictions,
        probabilities=probabilities,
        confidence=confidence,
        rsi=rsi,
        trend_bullish=ma5 > ma10 > ma20,
        trend_bearish=ma5 < ma10 < ma20,
        vix=vix,
        fear_greed=fear_greed,
        ma_alignment=ma_alignment,
        macd_signal=macd_val,
        bb_position=bb_pct,
    )

    return ScoreResponse(
        ai_score=score["ai_score"],
        composite_score=score["composite_score"],
        technical_rating=score["technical_rating"],
        sentiment_rating=score["sentiment_rating"],
        momentum_rating=score["momentum_rating"],
        risk_rating=score["risk_rating"],
        signal_strength=score["signal_strength"],
    )
