"""
ReAct (Reasoning + Acting) agent framework.

Implements a ReAct-style agent that alternates between:
1. Thought: Reason about the current state
2. Action: Use a tool (technical analysis, sentiment, etc.)
3. Observation: Process the tool's output
4. Repeat until a final answer is reached

Reference:
- Yao et al. (2023), "ReAct: Synergizing Reasoning and Acting in Language Models"
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import Callable, Dict, List, Optional, Tuple

import numpy as np
import pandas as pd

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class AgentStep:
    """A single step in the ReAct reasoning chain."""
    step_number: int = 0
    thought: str = ""
    action: str = ""
    action_input: str = ""
    observation: str = ""
    timestamp: str = ""


@dataclass
class ReActResult:
    """Complete ReAct agent result."""
    steps: List[AgentStep] = field(default_factory=list)
    final_answer: str = ""
    recommendation: str = ""  # BUY, SELL, HOLD
    confidence: float = 0.0
    reasoning_chain: str = ""
    tools_used: List[str] = field(default_factory=list)
    n_steps: int = 0


# =============================================================================
# TOOLS
# =============================================================================


def _tool_technical_analysis(df: pd.DataFrame) -> str:
    """Analyze technical indicators."""
    close = df.get("Close", pd.Series(dtype=float))
    if close.empty or len(close) < 50:
        return "Insufficient data for technical analysis"

    current = close.iloc[-1]
    ma20 = close.rolling(20).mean().iloc[-1]
    ma50 = close.rolling(50).mean().iloc[-1]

    # RSI
    delta = close.diff()
    gain = delta.clip(lower=0).rolling(14).mean()
    loss = (-delta.clip(upper=0)).rolling(14).mean()
    rsi = (100 - (100 / (1 + gain / (loss + 1e-10)))).iloc[-1]

    # MACD
    ema12 = close.ewm(span=12).mean()
    ema26 = close.ewm(span=26).mean()
    macd = (ema12 - ema26).iloc[-1]

    trend = "above" if current > ma20 else "below"
    ma_signal = "bullish" if ma20 > ma50 else "bearish"

    return (
        f"Price: {current:.0f}, MA20: {ma20:.0f} ({trend}), MA50: {ma50:.0f}. "
        f"RSI: {rsi:.1f}, MACD: {macd:.2f}. "
        f"MA signal: {ma_signal}. "
        f"RSI {'overbought' if rsi > 70 else 'oversold' if rsi < 30 else 'neutral'}."
    )


def _tool_fundamental_analysis(df: pd.DataFrame, ticker: str = "") -> str:
    """Analyze fundamental data if available."""
    # Check for fundamental columns
    if "PE" in df.columns or "P/E" in df.columns:
        pe = df.get("PE", df.get("P/E")).iloc[-1]
        return f"P/E ratio: {pe:.1f}. {'Undervalued' if pe < 15 else 'Overvalued' if pe > 25 else 'Fairly valued'}."

    return "Fundamental data not available in current dataset."


def _tool_sentiment_analysis(sentiment_data: Dict) -> str:
    """Analyze sentiment data."""
    if not sentiment_data:
        return "No sentiment data available."

    score = sentiment_data.get("score", 0)
    n_articles = sentiment_data.get("n_articles", 0)

    if score > 0.3:
        sentiment = "positive"
    elif score < -0.3:
        sentiment = "negative"
    else:
        sentiment = "neutral"

    return f"Sentiment: {sentiment} (score: {score:.2f}, {n_articles} articles)."


def _tool_intermarket_analysis(df: pd.DataFrame) -> str:
    """Analyze intermarket relationships."""
    close = df.get("Close", pd.Series(dtype=float))
    if close.empty:
        return "No data for intermarket analysis"

    returns = close.pct_change(20).iloc[-1] if len(close) > 20 else 0
    vol = close.pct_change().rolling(20).std().iloc[-1] * np.sqrt(252) if len(close) > 20 else 0

    return f"20-day return: {returns:.1%}, Annualized vol: {vol:.1%}."


def _tool_risk_analysis(df: pd.DataFrame) -> str:
    """Analyze risk metrics."""
    close = df.get("Close", pd.Series(dtype=float))
    if close.empty or len(close) < 20:
        return "Insufficient data for risk analysis"

    returns = close.pct_change().dropna()
    var_95 = float(np.percentile(returns, 5))
    max_dd = float((close / close.cummax() - 1).min())
    vol = float(returns.std() * np.sqrt(252))

    return f"VaR 95%: {var_95:.2%}, Max drawdown: {max_dd:.2%}, Annual vol: {vol:.1%}."


# =============================================================================
# REACT AGENT
# =============================================================================


class ReActAgent:
    """
    ReAct agent that reasons and acts in steps to produce a trading recommendation.
    """

    def __init__(self, max_steps: int = 6):
        self.max_steps = max_steps
        self.tools: Dict[str, Callable] = {
            "technical_analysis": _tool_technical_analysis,
            "fundamental_analysis": _tool_fundamental_analysis,
            "sentiment_analysis": _tool_sentiment_analysis,
            "intermarket_analysis": _tool_intermarket_analysis,
            "risk_analysis": _tool_risk_analysis,
        }

    def run(
        self,
        df: pd.DataFrame,
        ticker: str = "",
        sentiment_data: Optional[Dict] = None,
    ) -> ReActResult:
        """
        Run the ReAct agent.

        Args:
            df: OHLCV DataFrame
            ticker: Stock ticker
            sentiment_data: Pre-computed sentiment data

        Returns:
            ReActResult with reasoning chain and recommendation
        """
        result = ReActResult()
        observations = []
        tools_used = []

        # Step 1: Technical analysis
        step1 = AgentStep(step_number=1)
        step1.thought = f"I need to analyze {ticker} for a trading recommendation. Let me start with technical analysis."
        step1.action = "technical_analysis"
        step1.action_input = ticker
        step1.observation = self.tools["technical_analysis"](df)
        observations.append(step1.observation)
        tools_used.append("technical_analysis")
        result.steps.append(step1)

        # Step 2: Sentiment analysis
        step2 = AgentStep(step_number=2)
        step2.thought = "Now let me check market sentiment to understand the news context."
        step2.action = "sentiment_analysis"
        step2.action_input = ticker
        step2.observation = self.tools["sentiment_analysis"](sentiment_data or {})
        observations.append(step2.observation)
        tools_used.append("sentiment_analysis")
        result.steps.append(step2)

        # Step 3: Risk analysis
        step3 = AgentStep(step_number=3)
        step3.thought = "I should assess the risk profile before making a recommendation."
        step3.action = "risk_analysis"
        step3.action_input = ticker
        step3.observation = self.tools["risk_analysis"](df)
        observations.append(step3.observation)
        tools_used.append("risk_analysis")
        result.steps.append(step3)

        # Step 4: Intermarket
        step4 = AgentStep(step_number=4)
        step4.thought = "Let me check intermarket context for broader market conditions."
        step4.action = "intermarket_analysis"
        step4.action_input = ticker
        step4.observation = self.tools["intermarket_analysis"](df)
        observations.append(step4.observation)
        tools_used.append("intermarket_analysis")
        result.steps.append(step4)

        # Step 5: Fundamental
        step5 = AgentStep(step_number=5)
        step5.thought = "Let me check fundamental data for valuation context."
        step5.action = "fundamental_analysis"
        step5.action_input = ticker
        step5.observation = self.tools["fundamental_analysis"](df, ticker)
        observations.append(step5.observation)
        tools_used.append("fundamental_analysis")
        result.steps.append(step5)

        # Step 6: Final reasoning
        step6 = AgentStep(step_number=6)
        step6.thought = "Now I'll synthesize all observations into a final recommendation."

        # Parse observations for signals
        bull_signals = 0
        bear_signals = 0

        for obs in observations:
            obs_lower = obs.lower()
            if any(w in obs_lower for w in ["bullish", "positive", "undervalued", "above", "oversold"]):
                bull_signals += 1
            if any(w in obs_lower for w in ["bearish", "negative", "overvalued", "below", "overbought"]):
                bear_signals += 1

        if bull_signals > bear_signals:
            recommendation = "BUY"
            confidence = min(0.9, 0.5 + 0.1 * (bull_signals - bear_signals))
        elif bear_signals > bull_signals:
            recommendation = "SELL"
            confidence = min(0.9, 0.5 + 0.1 * (bear_signals - bull_signals))
        else:
            recommendation = "HOLD"
            confidence = 0.5

        step6.observation = f"Synthesis: {bull_signals} bullish signals, {bear_signals} bearish signals. Recommendation: {recommendation}"
        observations.append(step6.observation)
        result.steps.append(step6)

        # Final result
        result.recommendation = recommendation
        result.confidence = confidence
        result.tools_used = tools_used
        result.n_steps = len(result.steps)
        result.reasoning_chain = "\n".join(
            f"Step {s.step_number}: Thought: {s.thought}\n  Action: {s.action}\n  Observation: {s.observation}"
            for s in result.steps
        )
        result.final_answer = (
            f"Based on {len(result.steps)} steps of analysis "
            f"(tools: {', '.join(tools_used)}), "
            f"recommendation for {ticker}: {recommendation} "
            f"(confidence: {confidence:.0%}). "
            f"Bull signals: {bull_signals}, Bear signals: {bear_signals}."
        )

        return result


# =============================================================================
# CONVENIENCE FUNCTIONS
# =============================================================================


def run_react_agent(
    df: pd.DataFrame,
    ticker: str = "",
    sentiment_data: Optional[Dict] = None,
) -> Dict:
    """
    Run the ReAct agent for trading analysis.

    Returns:
        Dict with recommendation, confidence, and reasoning chain
    """
    agent = ReActAgent()
    result = agent.run(df, ticker, sentiment_data)

    return {
        "recommendation": result.recommendation,
        "confidence": result.confidence,
        "n_steps": result.n_steps,
        "tools_used": result.tools_used,
        "reasoning_chain": result.reasoning_chain,
        "final_answer": result.final_answer,
        "steps": [
            {
                "step": s.step_number,
                "thought": s.thought,
                "action": s.action,
                "observation": s.observation,
            }
            for s in result.steps
        ],
    }


def get_react_confidence_adjustment(result: Dict) -> Tuple[float, str]:
    """Get confidence adjustment from ReAct agent."""
    rec = result.get("recommendation", "HOLD")
    conf = result.get("confidence", 0)

    if rec == "BUY" and conf > 0.65:
        return 0.06, f"ReAct agent: BUY with {conf:.0%} confidence"
    elif rec == "SELL" and conf > 0.65:
        return -0.06, f"ReAct agent: SELL with {conf:.0%} confidence"
    else:
        return 0.0, f"ReAct agent: {rec} with {conf:.0%} confidence"
