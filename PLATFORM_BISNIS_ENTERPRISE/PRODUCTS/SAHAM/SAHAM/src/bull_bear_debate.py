"""
Bull vs Bear Debate System.

Inspired by TradingAgents (arXiv:2412.20138) — two adversarial AI agents
debate whether to buy or sell, producing a balanced analysis before
the final trading decision.

Architecture:
- Bull Agent: Argues for BUY (finds bullish signals, positive catalysts)
- Bear Agent: Argues for SELL (finds bearish signals, risk factors)
- Judge Agent: Weighs arguments and produces final verdict
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple

import numpy as np
import pandas as pd

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class DebateArgument:
    """A single argument from a debate agent."""
    agent: str = ""
    stance: str = ""  # 'bull' or 'bear'
    point: str = ""
    evidence: str = ""
    strength: float = 0.0  # 0-1
    category: str = ""  # 'technical', 'fundamental', 'sentiment', 'macro'


@dataclass
class DebateResult:
    """Complete debate result."""
    bull_arguments: List[DebateArgument] = field(default_factory=list)
    bear_arguments: List[DebateArgument] = field(default_factory=list)
    bull_score: float = 0.0
    bear_score: float = 0.0
    verdict: str = ""  # 'BUY', 'SELL', 'HOLD'
    confidence: float = 0.0
    key_factors: List[str] = field(default_factory=list)
    risk_factors: List[str] = field(default_factory=list)
    summary: str = ""


# =============================================================================
# BULL AGENT
# =============================================================================


def _bull_agent(df: pd.DataFrame, indicators: Dict, sentiment: Dict) -> List[DebateArgument]:
    """Generate bullish arguments from data."""
    args = []
    close = df.get("Close", pd.Series(dtype=float))

    # Technical arguments
    if len(close) > 50:
        ma50 = close.rolling(50).mean().iloc[-1]
        ma200 = close.rolling(200).mean().iloc[-1] if len(close) > 200 else ma50
        current = close.iloc[-1]

        if current > ma50:
            args.append(DebateArgument(
                agent="bull", stance="bull", category="technical",
                point="Price above 50-day MA",
                evidence=f"Current: {current:.0f}, MA50: {ma50:.0f} ({((current/ma50)-1)*100:.1f}% above)",
                strength=0.7,
            ))

        if ma50 > ma200:
            args.append(DebateArgument(
                agent="bull", stance="bull", category="technical",
                point="Golden cross structure (MA50 > MA200)",
                evidence=f"MA50: {ma50:.0f} > MA200: {ma200:.0f}",
                strength=0.6,
            ))

        # RSI
        if "rsi" in indicators:
            rsi = indicators["rsi"]
            if 40 < rsi < 60:
                args.append(DebateArgument(
                    agent="bull", stance="bull", category="technical",
                    point="RSI in healthy zone (not overbought)",
                    evidence=f"RSI: {rsi:.1f} — room to run higher",
                    strength=0.5,
                ))
            elif rsi < 30:
                args.append(DebateArgument(
                    agent="bull", stance="bull", category="technical",
                    point="RSI oversold — bounce likely",
                    evidence=f"RSI: {rsi:.1f} — historically bounces from this level",
                    strength=0.6,
                ))

        # Recent momentum
        if len(close) > 20:
            ret_20d = (close.iloc[-1] / close.iloc[-20] - 1) * 100
            if ret_20d > 5:
                args.append(DebateArgument(
                    agent="bull", stance="bull", category="technical",
                    point="Strong 20-day momentum",
                    evidence=f"20-day return: +{ret_20d:.1f}%",
                    strength=0.7,
                ))

        # Volume
        if "Volume" in df.columns and len(df) > 20:
            vol_recent = df["Volume"].iloc[-5:].mean()
            vol_avg = df["Volume"].iloc[-20:].mean()
            if vol_recent > vol_avg * 1.5:
                args.append(DebateArgument(
                    agent="bull", stance="bull", category="technical",
                    point="Volume surge — institutional accumulation",
                    evidence=f"Recent volume {vol_recent/vol_avg:.1f}x average",
                    strength=0.6,
                ))

    # Sentiment arguments
    if sentiment:
        score = sentiment.get("score", 0)
        if score > 0.3:
            args.append(DebateArgument(
                agent="bull", stance="bull", category="sentiment",
                point="Positive news sentiment",
                evidence=f"Sentiment score: {score:.2f} (positive)",
                strength=min(score, 0.8),
            ))

    # SMC arguments
    if "smc_signal" in indicators:
        if indicators["smc_signal"] == "BUY":
            args.append(DebateArgument(
                agent="bull", stance="bull", category="technical",
                point="SMC signal: Bullish order block + BOS",
                evidence="Institutional footprint suggests accumulation",
                strength=0.7,
            ))

    return args


# =============================================================================
# BEAR AGENT
# =============================================================================


def _bear_agent(df: pd.DataFrame, indicators: Dict, sentiment: Dict) -> List[DebateArgument]:
    """Generate bearish arguments from data."""
    args = []
    close = df.get("Close", pd.Series(dtype=float))

    if len(close) > 50:
        ma50 = close.rolling(50).mean().iloc[-1]
        ma200 = close.rolling(200).mean().iloc[-1] if len(close) > 200 else ma50
        current = close.iloc[-1]

        if current < ma50:
            args.append(DebateArgument(
                agent="bear", stance="bear", category="technical",
                point="Price below 50-day MA",
                evidence=f"Current: {current:.0f}, MA50: {ma50:.0f} ({((current/ma50)-1)*100:.1f}% below)",
                strength=0.7,
            ))

        if ma50 < ma200:
            args.append(DebateArgument(
                agent="bear", stance="bear", category="technical",
                point="Death cross structure (MA50 < MA200)",
                evidence=f"MA50: {ma50:.0f} < MA200: {ma200:.0f}",
                strength=0.6,
            ))

        # RSI overbought
        if "rsi" in indicators:
            rsi = indicators["rsi"]
            if rsi > 70:
                args.append(DebateArgument(
                    agent="bear", stance="bear", category="technical",
                    point="RSI overbought — correction likely",
                    evidence=f"RSI: {rsi:.1f} — historically pulls back from this level",
                    strength=0.6,
                ))

        # Recent decline
        if len(close) > 20:
            ret_20d = (close.iloc[-1] / close.iloc[-20] - 1) * 100
            if ret_20d < -5:
                args.append(DebateArgument(
                    agent="bear", stance="bear", category="technical",
                    point="Sharp 20-day decline",
                    evidence=f"20-day return: {ret_20d:.1f}%",
                    strength=0.7,
                ))

        # High volatility
        if len(close) > 20:
            vol = close.pct_change().iloc[-20:].std() * np.sqrt(252) * 100
            if vol > 35:
                args.append(DebateArgument(
                    agent="bear", stance="bear", category="technical",
                    point="Elevated volatility — risk-off environment",
                    evidence=f"Annualized vol: {vol:.1f}%",
                    strength=0.5,
                ))

    # Negative sentiment
    if sentiment:
        score = sentiment.get("score", 0)
        if score < -0.3:
            args.append(DebateArgument(
                agent="bear", stance="bear", category="sentiment",
                point="Negative news sentiment",
                evidence=f"Sentiment score: {score:.2f} (negative)",
                strength=min(abs(score), 0.8),
            ))

    # SMC bearish
    if "smc_signal" in indicators:
        if indicators["smc_signal"] == "SELL":
            args.append(DebateArgument(
                agent="bear", stance="bear", category="technical",
                point="SMC signal: Bearish order block + CHoCH",
                evidence="Institutional footprint suggests distribution",
                strength=0.7,
            ))

    # VIX
    if "vix" in indicators:
        vix = indicators["vix"]
        if vix > 25:
            args.append(DebateArgument(
                agent="bear", stance="bear", category="macro",
                point="Elevated VIX — market fear",
                evidence=f"VIX: {vix:.1f} (above 25 threshold)",
                strength=0.5,
            ))

    return args


# =============================================================================
# JUDGE AGENT
# =============================================================================


def _judge_agent(bull_args: List[DebateArgument], bear_args: List[DebateArgument]) -> DebateResult:
    """Weigh arguments and produce verdict."""
    bull_score = sum(a.strength for a in bull_args)
    bear_score = sum(a.strength for a in bear_args)

    # Normalize
    total = bull_score + bear_score
    if total > 0:
        bull_pct = bull_score / total
        bear_pct = bear_score / total
    else:
        bull_pct = 0.5
        bear_pct = 0.5

    # Verdict
    if bull_pct > 0.65:
        verdict = "BUY"
        confidence = bull_pct
    elif bear_pct > 0.65:
        verdict = "SELL"
        confidence = bear_pct
    else:
        verdict = "HOLD"
        confidence = 1 - abs(bull_pct - bear_pct)

    # Key factors (top 3 by strength)
    all_args = bull_args + bear_args
    all_args.sort(key=lambda x: x.strength, reverse=True)
    key_factors = [a.point for a in all_args[:3] if a.stance == "bull"]
    risk_factors = [a.point for a in all_args[:3] if a.stance == "bear"]

    # Summary
    summary = (
        f"Bull score: {bull_score:.2f} ({bull_pct:.0%}) vs "
        f"Bear score: {bear_score:.2f} ({bear_pct:.0%}). "
        f"Verdict: {verdict} (confidence: {confidence:.0%}). "
        f"Key bullish factors: {', '.join(key_factors[:2])}. "
        f"Key bearish factors: {', '.join(risk_factors[:2])}."
    )

    return DebateResult(
        bull_arguments=bull_args,
        bear_arguments=bear_args,
        bull_score=bull_score,
        bear_score=bear_score,
        verdict=verdict,
        confidence=confidence,
        key_factors=key_factors,
        risk_factors=risk_factors,
        summary=summary,
    )


# =============================================================================
# MAIN API
# =============================================================================


def run_bull_bear_debate(
    df: pd.DataFrame,
    indicators: Optional[Dict] = None,
    sentiment: Optional[Dict] = None,
) -> DebateResult:
    """
    Run a full bull vs bear debate on the given data.

    Args:
        df: OHLCV DataFrame
        indicators: Dict of pre-computed indicators (rsi, vix, smc_signal, etc.)
        sentiment: Dict with sentiment score and news data

    Returns:
        DebateResult with arguments, scores, and verdict
    """
    if indicators is None:
        indicators = {}
    if sentiment is None:
        sentiment = {}

    bull_args = _bull_agent(df, indicators, sentiment)
    bear_args = _bear_agent(df, indicators, sentiment)

    return _judge_agent(bull_args, bear_args)


def get_debate_confidence_adjustment(debate: DebateResult) -> Tuple[float, str]:
    """Get confidence adjustment from debate verdict."""
    if debate.verdict == "BUY" and debate.confidence > 0.7:
        return 0.08, f"Debate: strong bull verdict ({debate.confidence:.0%})"
    elif debate.verdict == "SELL" and debate.confidence > 0.7:
        return -0.08, f"Debate: strong bear verdict ({debate.confidence:.0%})"
    elif debate.verdict == "HOLD":
        return 0.0, f"Debate: balanced ({debate.confidence:.0%})"
    else:
        return 0.0, f"Debate: {debate.verdict} with low confidence"
