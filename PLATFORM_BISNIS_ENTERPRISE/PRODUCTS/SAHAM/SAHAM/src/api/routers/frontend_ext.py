"""
Extended frontend endpoints: sim, backtest, risk, portfolio, regime, intermarket, options, patterns/full, system check, data inventory, accuracy/full, model/details, sentiment/full, briefing/full, score/full, predict/{ticker} POST.
"""
import logging
import os
import traceback
from datetime import datetime
from fastapi import APIRouter, HTTPException, Query, Request

from src.api.utils import _cached_response, _cache_response, _sanitize, _resolve_ticker, TTL
from src.api.models import SimOrderRequest
from src.config import TICKERS, TARGET_TICKER

logger = logging.getLogger("saham.api.frontend_ext")
router = APIRouter(prefix="/api/v1", tags=["Frontend"])


# =============================================================================
# SIM (Broker Simulator)
# =============================================================================

_broker_instance = None


def _get_broker():
    global _broker_instance
    if _broker_instance is None:
        from src.broker_sim import BrokerSimulator
        _broker_instance = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
    return _broker_instance


@router.post("/sim/order")
async def submit_sim_order(req: SimOrderRequest):
    """Submit a simulated order via BrokerSimulator."""
    logger.info(f"[SIM ORDER] {req.side} {req.quantity} {req.symbol}")
    from src.broker_sim import SimOrder
    from src.data_fetcher import fetch_yfinance_data

    df = fetch_yfinance_data(req.symbol, period="1mo", interval="1d")
    if df is None or df.empty:
        raise HTTPException(status_code=404, detail=f"No price data for {req.symbol}")
    current_price = float(df["Close"].iloc[-1])

    broker = _get_broker()
    order = SimOrder(symbol=req.symbol, side=req.side, quantity=req.quantity, order_type="MARKET")
    result = broker.submit_order(order, current_price=current_price)

    return {
        "order_id": result.order_id,
        "status": result.status,
        "filled_qty": result.filled_qty,
        "avg_fill_price": result.avg_fill_price,
        "commission": result.commission,
        "fees": result.fees,
        "total_cost": result.total_cost,
        "slippage_bps": result.slippage_bps,
        "latency_ms": result.latency_ms,
    }


@router.get("/sim/portfolio")
async def get_sim_portfolio():
    """Get simulated portfolio positions."""
    from src.data_fetcher import fetch_yfinance_data

    broker = _get_broker()
    portfolio = broker.get_portfolio()
    positions = portfolio.get("positions", [])

    symbols = [p.get("symbol") for p in positions if p.get("symbol")]
    if symbols:
        prices = {}
        for sym in symbols:
            try:
                df = fetch_yfinance_data(sym, period="1mo", interval="1d")
                if df is not None and not df.empty:
                    prices[sym] = float(df["Close"].iloc[-1])
            except Exception:
                pass
        if prices:
            broker.update_market_prices(prices)
            portfolio = broker.get_portfolio()
            positions = portfolio.get("positions", [])

    result = []
    for pos in positions:
        result.append({
            "symbol": pos.get("symbol", ""),
            "quantity": pos.get("quantity", 0),
            "avg_price": pos.get("avg_price", 0),
            "market_price": pos.get("market_price", 0),
            "unrealized_pnl": pos.get("unrealized_pnl", 0),
        })
    return result


# =============================================================================
# BACKTEST
# =============================================================================

@router.get("/backtest/{ticker}")
async def run_backtest(ticker: str, period: str = "2y"):
    """Run backtest and trading simulation for a ticker."""
    logger.info(f"[BACKTEST] ticker={ticker}, period={period}")
    from src.data_fetcher import fetch_all_market_data
    from src.backtesting import run_backtest as _run_bt, simulate_trading

    market_data = fetch_all_market_data(period=period)
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    try:
        bt = _run_bt(market_data=market_data, fred_data={}, target_ticker=ticker)
        sim = simulate_trading(market_data=market_data, fred_data={}, target_ticker=ticker, initial_capital=100_000_000)
    except Exception as e:
        logger.error(f"[BACKTEST] failed: {e}\n{traceback.format_exc()}")
        raise HTTPException(status_code=500, detail=f"Backtest error: {e}")

    if "error" in bt:
        raise HTTPException(status_code=500, detail=bt["error"])
    ensemble_bt = bt.get("Ensemble", {})
    directional_acc = ensemble_bt.get("directional_accuracy", 0) if isinstance(ensemble_bt, dict) else 0
    total_preds = sum(1 for k, v in bt.items() if isinstance(v, dict) and "directional_accuracy" in v)
    mape_val = bt.get("MAPE")

    bt_mapped = {
        "directional_accuracy": directional_acc,
        "total_predictions": total_preds,
        "MAPE": mape_val,
    }

    if "error" in sim:
        sim = {"initial_capital": 100_000_000, "final_capital": 100_000_000, "total_return": 0, "buy_hold_return": 0, "trades": 0, "max_drawdown": 0}

    return _sanitize({"backtest": bt_mapped, "simulation": sim})


# =============================================================================
# RISK
# =============================================================================

@router.get("/risk/{ticker}")
async def get_risk_metrics(ticker: str, period: str = "2y", position_value: float = 100_000_000):
    """Get risk metrics: VaR, CVaR, Sharpe, Sortino, Kelly, drawdown."""
    from src.data_fetcher import fetch_all_market_data
    from src.risk_manager import calc_var, calc_risk_metrics

    market_data = fetch_all_market_data(period=period)
    target_name = _resolve_ticker(ticker)

    if target_name not in market_data:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    df = market_data[target_name]
    returns = df["Close"].pct_change().dropna()
    prices = df["Close"]
    risk = calc_risk_metrics(returns, prices, position_value)
    var = calc_var(returns, 0.95, position_value)
    return _sanitize({"risk": risk, "var": var})


# =============================================================================
# PORTFOLIO OPTIMIZATION
# =============================================================================

@router.get("/portfolio/optimize")
async def get_portfolio_optimization(period: str = "2y", n_sim: int = 3000, risk_free: float = 0.05):
    """Run portfolio optimization with efficient frontier."""
    from src.data_fetcher import fetch_all_market_data
    from src.portfolio import optimize_portfolio
    import pandas as pd

    market_data = fetch_all_market_data(period=period)
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    returns_dict = {}
    for name, df in market_data.items():
        if not df.empty and len(df) > 30:
            returns_dict[name] = df["Close"].pct_change().dropna()

    if len(returns_dict) < 2:
        raise HTTPException(status_code=400, detail="Not enough assets for optimization")

    returns_df = pd.DataFrame(returns_dict).dropna()
    try:
        opt = optimize_portfolio(returns_df, risk_free=risk_free, n_portfolios=n_sim)
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Optimization error: {e}")
    return _sanitize(opt)


# =============================================================================
# REGIME
# =============================================================================

@router.get("/regime/{ticker}")
async def get_regime(ticker: str, period: str = "2y"):
    """Get market regime detection for a ticker."""
    from src.data_fetcher import fetch_all_market_data
    from src.regime import detect_market_regime

    market_data = fetch_all_market_data(period=period)
    target_name = _resolve_ticker(ticker)

    if target_name not in market_data:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    df = market_data[target_name]
    result = detect_market_regime(df, close_col="Close")
    return _sanitize(result)


# =============================================================================
# INTERMARKET
# =============================================================================

@router.get("/intermarket")
async def get_intermarket(period: str = "1y"):
    """Get intermarket correlation analysis."""
    from src.data_fetcher import fetch_all_market_data
    from src.intermarket import calc_correlation_matrix, calc_intermarket_summary
    import pandas as pd

    market_data = fetch_all_market_data(period=period)
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    data_dict = {name: df for name, df in market_data.items() if not df.empty}
    corr = calc_correlation_matrix(data_dict)
    summary = calc_intermarket_summary(data_dict)

    if isinstance(corr, pd.DataFrame):
        corr = corr.to_dict()

    return _sanitize({"correlation": corr, "summary": summary})


# =============================================================================
# FULL PATTERNS
# =============================================================================

@router.get("/patterns/{ticker}/full")
async def get_full_patterns(ticker: str, period: str = "6mo"):
    """Get full pattern analysis for a ticker."""
    from src.data_fetcher import fetch_all_market_data
    from src.patterns import full_pattern_analysis

    market_data = fetch_all_market_data(period=period)
    target_name = _resolve_ticker(ticker)

    if target_name not in market_data:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    df = market_data[target_name]
    result = full_pattern_analysis(df)
    return _sanitize({
        "candlestick_patterns": [{"name": p.name, "type": p.type, "confidence": p.confidence, "date": p.date} for p in result["candlestick_patterns"]],
        "chart_patterns": [{"name": p.name, "type": p.type, "confidence": p.confidence, "description": p.description} for p in result["chart_patterns"]],
        "market_structure": {"structure": result["market_structure"].structure, "description": result["market_structure"].description},
        "volume_anomalies": [{"date": a.date, "type": a.anomaly_type, "z_score": a.z_score} for a in result["volume_anomalies"]],
        "trendlines": [{"type": t.type, "slope": t.slope, "touches": t.touches} for t in result["trendlines"]],
        "summary": result["summary"],
    })


# =============================================================================
# OPTIONS
# =============================================================================

@router.get("/options/{ticker}")
async def get_options_analysis(ticker: str):
    """Get options analysis for a ticker."""
    from src.data_fetcher import fetch_yfinance_data
    from src.options_analysis import run_options_analysis
    import numpy as np

    df = fetch_yfinance_data(ticker, period="1y", interval="1d")
    if df is None or df.empty:
        raise HTTPException(status_code=404, detail=f"No data for {ticker}")

    S = float(df["Close"].iloc[-1])
    K = S
    T = 30 / 365
    r = 0.06
    returns = df["Close"].pct_change().dropna()
    sigma = float(returns.std() * np.sqrt(252))

    try:
        result = run_options_analysis(S, K, T=T, r=r, sigma=sigma)
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Options error: {e}")
    return _sanitize(result)


# =============================================================================
# SYSTEM CHECK & DATA INVENTORY
# =============================================================================

@router.get("/system/check")
async def get_system_check():
    """Get system check results (packages, GPU, etc)."""
    from src.system_check import check_system
    from dataclasses import asdict, is_dataclass
    result = check_system()
    if is_dataclass(result):
        result = asdict(result)
    return _sanitize(result)


@router.get("/data/inventory")
async def get_data_inventory():
    """Get data inventory from database."""
    import sqlite3
    db_path = os.path.join(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))), "src", "data", "saham_prediksi.db")
    if not os.path.exists(db_path):
        return []
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    cursor.execute("SELECT name FROM sqlite_master WHERE type='table'")
    tables = cursor.fetchall()
    inventory = []
    for (table_name,) in tables:
        cursor.execute(f"SELECT COUNT(*) FROM {table_name}")
        count = cursor.fetchone()[0]
        cursor.execute(f"PRAGMA table_info({table_name})")
        cols = [c[1] for c in cursor.fetchall()]
        inventory.append({"table": table_name, "rows": count, "columns": cols})
    conn.close()
    return inventory


# =============================================================================
# ACCURACY FULL
# =============================================================================

@router.get("/accuracy/full")
async def get_full_accuracy():
    """Get full accuracy and verification metrics."""
    from src.database import get_akurasi_metrics, get_all_prediksi
    metrics = get_akurasi_metrics()
    df = get_all_prediksi()
    recent = []
    if not df.empty:
        recent_df = df.tail(20)
        for _, row in recent_df.iterrows():
            actual = row.get("harga_aktual")
            predicted = row.get("harga_prediksi")
            arah_pred = row.get("arah_prediksi", "")
            arah_akt = row.get("arah_aktual", "")
            recent.append({
                "date": str(row.get("tanggal_prediksi", "")),
                "ticker": str(row.get("ticker", "")),
                "predicted": float(predicted) if predicted else 0,
                "actual": float(actual) if actual else None,
                "correct": bool(arah_pred and arah_akt and arah_pred == arah_akt),
                "signal": str(row.get("sinyal", "")),
            })
    return _sanitize({"metrics": metrics, "recent_predictions": recent})


# =============================================================================
# MODEL DETAILS
# =============================================================================

@router.get("/model/details/{ticker}")
async def get_model_details(ticker: str):
    """Get detailed model information including ensemble votes and SHAP."""
    from src.data_fetcher import fetch_all_market_data, fetch_yfinance_data
    from src.predictor import run_prediction as _run_prediction

    market_data = fetch_all_market_data(period="2y")
    md = dict(market_data)
    target_name = _resolve_ticker(ticker, default="TARGET")
    if target_name == "TARGET" and (target_name not in md or md[target_name].empty):
        df = fetch_yfinance_data(ticker, period="1y", interval="1d")
        if not df.empty:
            md[target_name] = df

    if not md:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    result = _run_prediction(market_data=md, target_ticker=ticker)
    details = {
        "predictions": result.get("predictions", {}),
        "probabilities": result.get("probabilities", {}),
        "model_votes": result.get("model_votes", ""),
        "rules": result.get("rules", ""),
        "shap_explanations": result.get("shap_explanations", {}),
        "feature_importance": result.get("feature_importance", {}),
        "ensemble_method": result.get("ensemble_method", ""),
        "training_scores": result.get("training_scores", {}),
        "market_regime": result.get("market_regime", "unknown"),
        "regime_adjusted": result.get("regime_adjusted", False),
        "risk_governance": result.get("risk_governance", {}),
        "advanced_analysis": result.get("advanced_analysis", {}),
    }
    return _sanitize(details)


# =============================================================================
# FULL SENTIMENT / BRIEFING / SCORE
# =============================================================================

@router.get("/sentiment/full")
async def get_full_sentiment():
    """Get full Fear & Greed sentiment with components."""
    from src.data_fetcher import fetch_all_market_data
    from src.sentiment import calc_fear_greed_index

    market_data = fetch_all_market_data(period="6mo")
    if not market_data:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    fg = calc_fear_greed_index(market_data)
    return _sanitize(fg)


@router.get("/briefing/full")
async def get_full_briefing():
    """Get full AI multi-agent briefing with Bull vs Bear debate."""
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

    final_rec = briefing.get("final_recommendation")
    final_rec_str = final_rec.findings if hasattr(final_rec, "findings") else str(final_rec)

    agents = briefing.get("agents", [])
    bull_case = next((a.findings for a in agents if "bull" in a.agent_name.lower() or signal == "BUY"), None)
    bear_case = next((a.findings for a in agents if "bear" in a.agent_name.lower() or signal == "SELL"), None)

    result = {
        "date": briefing["date"],
        "market_summary": briefing["market_summary"],
        "signal": briefing["signal"],
        "confidence": briefing["confidence"],
        "final_recommendation": final_rec_str,
        "actionable_items": briefing["actionable_items"],
        "risk_assessment": briefing["risk_assessment"],
        "bull_case": bull_case,
        "bear_case": bear_case,
        "debate": {a.agent_name: a.findings for a in agents} if agents else None,
        "llm_commentary": briefing.get("llm_commentary"),
    }
    return _sanitize(result)


@router.get("/score/{ticker}/full")
async def get_full_score(ticker: str):
    """Get composite AI score with all component ratings."""
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
        predictions=predictions, probabilities=probabilities, confidence=confidence,
        rsi=rsi, trend_bullish=ma5 > ma10 > ma20, trend_bearish=ma5 < ma10 < ma20,
        vix=vix, fear_greed=fear_greed, ma_alignment=ma_alignment,
        macd_signal=macd_val, bb_position=bb_pct,
    )
    return _sanitize(score)


# =============================================================================
# PREDICT FOR SPECIFIC TICKER (POST)
# =============================================================================

@router.post("/predict/{ticker}")
async def run_prediction_for_ticker(ticker: str):
    """Run full prediction pipeline for a specific ticker."""
    from src.data_fetcher import fetch_all_market_data, fetch_yfinance_data
    from src.predictor import run_prediction as _run_prediction

    market_data = fetch_all_market_data(period="2y")
    md = dict(market_data)

    target_name = _resolve_ticker(ticker, default="TARGET")
    if target_name == "TARGET" and (target_name not in md or md[target_name].empty):
        df = fetch_yfinance_data(ticker, period="1y", interval="1d")
        if not df.empty:
            md[target_name] = df

    if not md:
        raise HTTPException(status_code=500, detail="Failed to fetch market data")

    result = _run_prediction(market_data=md, target_ticker=ticker)

    if "error" in result:
        raise HTTPException(status_code=500, detail=result["error"])

    return _sanitize(result)


# =============================================================================
# ASYNC TASK ENDPOINTS (Celery)
# =============================================================================

@router.post("/tasks/predict")
async def submit_prediction_task(ticker: str = Query(default="IHMG"), period: str = Query(default="2y")):
    """Submit async prediction task. Returns task_id for polling."""
    from src.celery_app import run_prediction_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed. Run synchronously via POST /api/v1/predict"}

    target = TICKERS.get(ticker, TARGET_TICKER)
    result = run_prediction_task.delay(ticker=target, period=period)
    return {"task_id": result.id, "status": "PENDING", "ticker": ticker}


@router.post("/tasks/fetch")
async def submit_fetch_task(period: str = Query(default="2y")):
    """Submit async market data fetch task."""
    from src.celery_app import fetch_market_data_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed"}

    result = fetch_market_data_task.delay(period=period)
    return {"task_id": result.id, "status": "PENDING"}


@router.post("/tasks/backtest")
async def submit_backtest_task(ticker: str = Query(default="IHMG"), period: str = Query(default="2y")):
    """Submit async backtest task."""
    from src.celery_app import run_backtest_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed"}

    target = TICKERS.get(ticker, TARGET_TICKER)
    result = run_backtest_task.delay(ticker=target, period=period)
    return {"task_id": result.id, "status": "PENDING", "ticker": ticker}


@router.post("/tasks/screener")
async def submit_screener_task(top_n: int = Query(default=10)):
    """Submit async screener task."""
    from src.celery_app import run_screener_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed"}

    result = run_screener_task.delay(top_n=top_n)
    return {"task_id": result.id, "status": "PENDING"}


@router.post("/tasks/retrain")
async def submit_retrain_task(ticker: str = Query(default="IHMG")):
    """Submit async model retrain task."""
    from src.celery_app import retrain_model_task, _CELERY_AVAILABLE

    if not _CELERY_AVAILABLE:
        return {"status": "unavailable", "error": "Celery not installed"}

    target = TICKERS.get(ticker, TARGET_TICKER)
    result = retrain_model_task.delay(ticker=target)
    return {"task_id": result.id, "status": "PENDING", "ticker": ticker}


@router.get("/tasks/{task_id}")
async def get_task_status(task_id: str):
    """Get status of an async task."""
    from src.celery_app import get_task_status as _get_status
    return _get_status(task_id)
