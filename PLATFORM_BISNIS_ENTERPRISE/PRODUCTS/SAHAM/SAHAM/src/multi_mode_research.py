"""
Multi-mode AI research system.

Provides different depth levels of AI analysis:
- Fast mode: Quick scan of key indicators (seconds)
- Deep mode: Comprehensive analysis with all modules (minutes)
- Custom mode: User-selected analysis modules

Inspired by multi-mode AI research from TradingAgents framework.
"""
from __future__ import annotations

import logging
import time
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple

import numpy as np
import pandas as pd

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class ResearchResult:
    """Result from multi-mode AI research."""
    mode: str = ""
    ticker: str = ""
    analysis: Dict = field(default_factory=dict)
    recommendation: str = ""
    confidence: float = 0.0
    modules_used: List[str] = field(default_factory=list)
    execution_time: float = 0.0
    summary: str = ""
    error: str = ""


# =============================================================================
# RESEARCH MODES
# =============================================================================


def _fast_mode(df: pd.DataFrame, ticker: str) -> Dict:
    """Fast analysis: key indicators only."""
    result = {}
    close = df.get("Close", pd.Series(dtype=float))

    if close.empty or len(close) < 50:
        return {"error": "Insufficient data"}

    # Quick technical scan
    current = close.iloc[-1]
    ma20 = close.rolling(20).mean().iloc[-1]
    ma50 = close.rolling(50).mean().iloc[-1]

    delta = close.diff()
    gain = delta.clip(lower=0).rolling(14).mean()
    loss = (-delta.clip(upper=0)).rolling(14).mean()
    rsi = (100 - (100 / (1 + gain / (loss + 1e-10)))).iloc[-1]

    result["technical"] = {
        "price": float(current),
        "ma20": float(ma20),
        "ma50": float(ma50),
        "rsi": float(rsi),
        "trend": "bullish" if current > ma20 > ma50 else "bearish" if current < ma20 < ma50 else "mixed",
    }

    # Quick return estimate
    ret_5d = float(close.pct_change(5).iloc[-1]) if len(close) > 5 else 0
    result["momentum"] = {"return_5d": ret_5d}

    # Signal
    if current > ma20 > ma50 and rsi < 70 and ret_5d > 0:
        result["signal"] = "BUY"
        result["confidence"] = 0.65
    elif current < ma20 < ma50 and rsi > 30 and ret_5d < 0:
        result["signal"] = "SELL"
        result["confidence"] = 0.65
    else:
        result["signal"] = "HOLD"
        result["confidence"] = 0.5

    return result


def _deep_mode(df: pd.DataFrame, ticker: str, sentiment_data: Optional[Dict] = None) -> Dict:
    """Deep analysis: all available modules."""
    result = {}
    modules_used = []

    # Technical analysis
    try:
        from src.indicators import compute_indicators
        indicators = compute_indicators(df)
        result["technical"] = {k: float(v) for k, v in indicators.items() if isinstance(v, (int, float, np.floating))}
        modules_used.append("indicators")
    except Exception as e:
        logger.warning(f"Indicators module failed: {e}")

    # MTF analysis
    try:
        from src.mtf import run_mtf_analysis
        mtf_result = run_mtf_analysis(df, ticker)
        result["mtf"] = {
            "confluence_score": getattr(mtf_result, "confluence_score", 0),
            "signal": getattr(mtf_result, "signal", "HOLD"),
        }
        modules_used.append("mtf")
    except Exception as e:
        logger.warning(f"MTF module failed: {e}")

    # SMC analysis
    try:
        from src.smc import run_smc_analysis
        smc_result = run_smc_analysis(df)
        result["smc"] = {
            "signal": getattr(smc_result, "signal", "HOLD"),
            "confidence": getattr(smc_result, "confidence", 0),
        }
        modules_used.append("smc")
    except Exception as e:
        logger.warning(f"SMC module failed: {e}")

    # Regime detection
    try:
        from src.regime_models import detect_market_regime
        regime = detect_market_regime(df)
        result["regime"] = str(regime.iloc[-1]) if not regime.empty else "unknown"
        modules_used.append("regime")
    except Exception as e:
        logger.warning(f"Regime module failed: {e}")

    # Risk analysis
    try:
        from src.risk_manager import calculate_risk_metrics
        risk = calculate_risk_metrics(df)
        result["risk"] = risk if isinstance(risk, dict) else {}
        modules_used.append("risk_manager")
    except Exception as e:
        logger.warning(f"Risk module failed: {e}")

    # Bull vs Bear debate
    try:
        from src.bull_bear_debate import run_bull_bear_debate
        debate = run_bull_bear_debate(df, indicators=result.get("technical", {}), sentiment=sentiment_data or {})
        result["debate"] = {
            "verdict": debate.verdict,
            "confidence": debate.confidence,
            "bull_score": debate.bull_score,
            "bear_score": debate.bear_score,
        }
        modules_used.append("bull_bear_debate")
    except Exception as e:
        logger.warning(f"Debate module failed: {e}")

    # ReAct agent
    try:
        from src.react_agent import run_react_agent
        react = run_react_agent(df, ticker, sentiment_data)
        result["react_agent"] = {
            "recommendation": react["recommendation"],
            "confidence": react["confidence"],
        }
        modules_used.append("react_agent")
    except Exception as e:
        logger.warning(f"ReAct agent failed: {e}")

    # Multi-horizon
    try:
        from src.multi_horizon import run_multi_horizon_prediction
        mh = run_multi_horizon_prediction(df, ticker)
        result["multi_horizon"] = {
            "consensus": mh.consensus_signal,
            "confidence": mh.consensus_confidence,
            "horizons": {
                name: {
                    "signal": p.signal,
                    "return": p.predicted_return,
                    "confidence": p.confidence,
                }
                for name, p in mh.predictions.items()
            },
        }
        modules_used.append("multi_horizon")
    except Exception as e:
        logger.warning(f"Multi-horizon failed: {e}")

    # Trading memory
    try:
        from src.trading_memory import query_trading_memory
        memory = query_trading_memory(ticker=ticker)
        result["memory"] = {
            "n_similar": memory["n_similar_trades"],
            "win_rate": memory["win_rate"],
            "recommendation": memory["recommendation"],
        }
        modules_used.append("trading_memory")
    except Exception as e:
        logger.warning(f"Trading memory failed: {e}")

    # Aggregate signal
    signals = []
    confidences = []

    for key in ["debate", "react_agent", "multi_horizon"]:
        if key in result:
            sub = result[key]
            sig = sub.get("verdict") or sub.get("recommendation") or sub.get("consensus", "HOLD")
            conf = sub.get("confidence", 0.5)
            signals.append(sig)
            confidences.append(conf)

    if "mtf" in result:
        signals.append(result["mtf"].get("signal", "HOLD"))
        confidences.append(0.6)

    if "smc" in result:
        signals.append(result["smc"].get("signal", "HOLD"))
        confidences.append(result["smc"].get("confidence", 0.5))

    # Consensus
    if signals:
        buy_count = signals.count("BUY")
        sell_count = signals.count("SELL")
        hold_count = signals.count("HOLD")

        if buy_count > sell_count and buy_count > hold_count:
            result["signal"] = "BUY"
        elif sell_count > buy_count and sell_count > hold_count:
            result["signal"] = "SELL"
        else:
            result["signal"] = "HOLD"

        result["confidence"] = float(np.mean(confidences))
        result["agreement"] = float(max(buy_count, sell_count, hold_count) / len(signals))
    else:
        result["signal"] = "HOLD"
        result["confidence"] = 0.5

    result["modules_used"] = modules_used
    return result


def _custom_mode(
    df: pd.DataFrame,
    ticker: str,
    modules: List[str],
    sentiment_data: Optional[Dict] = None,
) -> Dict:
    """Custom analysis: user-selected modules."""
    result = {}
    modules_used = []

    module_map = {
        "technical": lambda: _fast_mode(df, ticker).get("technical", {}),
        "mtf": lambda: _try_module("src.mtf", "run_mtf_analysis", df, ticker),
        "smc": lambda: _try_module("src.smc", "run_smc_analysis", df),
        "regime": lambda: _try_module("src.regime_models", "run_regime_aware_prediction", df),
        "debate": lambda: _try_module("src.bull_bear_debate", "run_bull_bear_debate", df, {}, sentiment_data or {}),
        "react": lambda: _try_module("src.react_agent", "run_react_agent", df, ticker, sentiment_data),
        "multi_horizon": lambda: _try_module("src.multi_horizon", "run_multi_horizon_prediction", df, ticker),
        "risk": lambda: _try_module("src.risk_manager", "calculate_risk_metrics", df),
        "memory": lambda: _try_module("src.trading_memory", "query_trading_memory", ticker=ticker),
        "options": lambda: _try_module("src.options_analysis", "run_options_analysis", float(df["Close"].iloc[-1])),
    }

    for mod_name in modules:
        if mod_name in module_map:
            try:
                result[mod_name] = module_map[mod_name]()
                modules_used.append(mod_name)
            except Exception as e:
                logger.warning(f"Custom module {mod_name} failed: {e}")

    result["modules_used"] = modules_used
    result["signal"] = "HOLD"
    result["confidence"] = 0.5
    return result


def _try_module(module_path: str, function_name: str, *args, **kwargs) -> Dict:
    """Try to import and run a module function."""
    import importlib
    module_path.split(".")
    module = importlib.import_module(module_path)
    fn = getattr(module, function_name)
    result = fn(*args, **kwargs)

    # Convert to dict if needed
    if hasattr(result, "__dict__"):
        return {k: v for k, v in vars(result).items() if not k.startswith("_")}
    elif isinstance(result, dict):
        return result
    else:
        return {"result": str(result)}


# =============================================================================
# MAIN API
# =============================================================================


def run_research(
    df: pd.DataFrame,
    ticker: str = "",
    mode: str = "fast",
    modules: Optional[List[str]] = None,
    sentiment_data: Optional[Dict] = None,
) -> ResearchResult:
    """
    Run multi-mode AI research.

    Args:
        df: OHLCV DataFrame
        ticker: Stock ticker
        mode: 'fast', 'deep', or 'custom'
        modules: List of modules for custom mode
        sentiment_data: Pre-computed sentiment data

    Returns:
        ResearchResult with analysis and recommendation
    """
    start_time = time.time()
    result = ResearchResult(mode=mode, ticker=ticker)

    if mode == "fast":
        analysis = _fast_mode(df, ticker)
        result.modules_used = ["technical", "momentum"]
    elif mode == "deep":
        analysis = _deep_mode(df, ticker, sentiment_data)
        result.modules_used = analysis.get("modules_used", [])
    elif mode == "custom":
        if modules is None:
            modules = ["technical", "mtf", "smc", "debate"]
        analysis = _custom_mode(df, ticker, modules, sentiment_data)
        result.modules_used = analysis.get("modules_used", [])
    else:
        result.error = f"Unknown mode: {mode}"
        return result

    if "error" in analysis:
        result.error = analysis["error"]
        return result

    result.analysis = analysis
    result.recommendation = analysis.get("signal", "HOLD")
    result.confidence = analysis.get("confidence", 0.5)
    result.execution_time = float(time.time() - start_time)

    # Summary
    n_modules = len(result.modules_used)
    result.summary = (
        f"[{mode.upper()}] {ticker}: {result.recommendation} "
        f"(confidence: {result.confidence:.0%}, "
        f"modules: {n_modules}, "
        f"time: {result.execution_time:.1f}s)"
    )

    return result


def get_research_confidence_adjustment(result: ResearchResult) -> Tuple[float, str]:
    """Get confidence adjustment from research result."""
    if result.error:
        return 0.0, "Research: error"

    if result.mode == "deep" and result.confidence > 0.65:
        if result.recommendation == "BUY":
            return 0.08, f"Deep research: BUY with {result.confidence:.0%}"
        elif result.recommendation == "SELL":
            return -0.08, f"Deep research: SELL with {result.confidence:.0%}"

    if result.mode == "fast" and result.confidence > 0.6:
        if result.recommendation == "BUY":
            return 0.03, "Fast research: BUY"
        elif result.recommendation == "SELL":
            return -0.03, "Fast research: SELL"

    return 0.0, f"Research: {result.recommendation}"
