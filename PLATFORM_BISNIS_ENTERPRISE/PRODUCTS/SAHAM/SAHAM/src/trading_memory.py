"""
Trading memory system.

Stores historical trade decisions, outcomes, and lessons learned.
The AI agent can query this memory to avoid repeating mistakes
and reinforce successful patterns.

Features:
- Trade journal with outcome tracking
- Pattern recognition from past trades
- Mistake avoidance: warn when current setup resembles past losers
- Success reinforcement: boost confidence when setup matches past winners
"""
from __future__ import annotations

import json
import logging
from dataclasses import dataclass, field
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Optional

import numpy as np

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class TradeRecord:
    """A single trade record in memory."""
    trade_id: str = ""
    ticker: str = ""
    date: str = ""
    signal: str = ""  # BUY, SELL, HOLD
    entry_price: float = 0.0
    exit_price: float = 0.0
    return_pct: float = 0.0
    confidence: float = 0.0
    regime: str = ""
    indicators: Dict = field(default_factory=dict)
    thesis: str = ""
    outcome: str = ""  # win, loss, breakeven
    lessons: str = ""
    tags: List[str] = field(default_factory=list)


@dataclass
class MemoryQueryResult:
    """Result from querying trading memory."""
    similar_trades: List[TradeRecord] = field(default_factory=list)
    win_rate: float = 0.0
    avg_return: float = 0.0
    common_mistakes: List[str] = field(default_factory=list)
    success_patterns: List[str] = field(default_factory=list)
    recommendation: str = ""
    confidence_adjustment: float = 0.0


# =============================================================================
# TRADING MEMORY
# =============================================================================


class TradingMemory:
    """
    Persistent trading memory that stores and retrieves trade records.
    """

    def __init__(self, storage_path: str = "src/data/trading_memory.json"):
        self.storage_path = Path(storage_path)
        self.storage_path.parent.mkdir(parents=True, exist_ok=True)
        self.trades: List[TradeRecord] = []
        self._load()

    def record_trade(self, trade: TradeRecord) -> bool:
        """Record a new trade."""
        if not trade.trade_id:
            trade.trade_id = f"{trade.ticker}_{trade.date}_{datetime.now().strftime('%H%M%S')}"
        self.trades.append(trade)
        self._save()
        return True

    def query_similar(
        self,
        ticker: str = "",
        signal: str = "",
        regime: str = "",
        indicators: Optional[Dict] = None,
        top_k: int = 10,
    ) -> MemoryQueryResult:
        """
        Query memory for similar past trades.

        Args:
            ticker: Ticker symbol
            signal: Signal type
            regime: Market regime
            indicators: Current indicator values for matching
            top_k: Max results

        Returns:
            MemoryQueryResult with similar trades and statistics
        """
        scored_trades = []

        for trade in self.trades:
            score = 0

            if ticker and trade.ticker == ticker:
                score += 3
            if signal and trade.signal == signal:
                score += 2
            if regime and trade.regime == regime:
                score += 2

            # Indicator similarity
            if indicators and trade.indicators:
                common_keys = set(indicators.keys()) & set(trade.indicators.keys())
                for key in common_keys:
                    try:
                        val1 = float(indicators[key])
                        val2 = float(trade.indicators[key])
                        diff = abs(val1 - val2)
                        if diff < 0.1 * max(abs(val1), abs(val2), 1):
                            score += 1
                    except (ValueError, TypeError):
                        continue

            if score > 0:
                scored_trades.append((score, trade))

        scored_trades.sort(key=lambda x: x[0], reverse=True)
        similar = [t for _, t in scored_trades[:top_k]]

        # Statistics
        if similar:
            wins = [t for t in similar if t.outcome == "win"]
            losses = [t for t in similar if t.outcome == "loss"]
            win_rate = len(wins) / len(similar) if similar else 0
            avg_return = float(np.mean([t.return_pct for t in similar]))

            # Common mistakes from losing trades
            mistakes = []
            for t in losses:
                if t.lessons and t.lessons not in mistakes:
                    mistakes.append(t.lessons)

            # Success patterns from winning trades
            success = []
            for t in wins:
                if t.thesis and t.thesis not in success:
                    success.append(t.thesis)

            # Recommendation
            if win_rate > 0.6 and avg_return > 0:
                recommendation = f"Historical setup favorable: {win_rate:.0%} win rate, avg return {avg_return:.1%}"
                confidence_adj = 0.05
            elif win_rate < 0.4 and avg_return < 0:
                recommendation = f"Warning: similar setups had {win_rate:.0%} win rate, avg return {avg_return:.1%}"
                confidence_adj = -0.05
            else:
                recommendation = f"Mixed historical results: {win_rate:.0%} win rate"
                confidence_adj = 0.0
        else:
            win_rate = 0
            avg_return = 0
            mistakes = []
            success = []
            recommendation = "No similar historical trades found"
            confidence_adj = 0.0

        return MemoryQueryResult(
            similar_trades=similar,
            win_rate=win_rate,
            avg_return=avg_return,
            common_mistakes=mistakes[:5],
            success_patterns=success[:5],
            recommendation=recommendation,
            confidence_adjustment=confidence_adj,
        )

    def get_statistics(self) -> Dict:
        """Get overall trading memory statistics."""
        if not self.trades:
            return {"total_trades": 0}

        wins = [t for t in self.trades if t.outcome == "win"]
        losses = [t for t in self.trades if t.outcome == "loss"]

        returns = [t.return_pct for t in self.trades]

        return {
            "total_trades": len(self.trades),
            "wins": len(wins),
            "losses": len(losses),
            "win_rate": len(wins) / len(self.trades) if self.trades else 0,
            "avg_return": float(np.mean(returns)) if returns else 0,
            "best_trade": float(max(returns)) if returns else 0,
            "worst_trade": float(min(returns)) if returns else 0,
            "tickers_traded": list(set(t.ticker for t in self.trades)),
            "regimes_seen": list(set(t.regime for t in self.trades if t.regime)),
        }

    def get_lessons(self, limit: int = 10) -> List[str]:
        """Get top lessons from past trades."""
        lessons = []
        for t in self.trades:
            if t.lessons:
                lessons.append(f"[{t.ticker} {t.date}] {t.lessons}")
        return lessons[-limit:]

    def _save(self):
        """Save to disk."""
        data = []
        for t in self.trades:
            data.append({
                "trade_id": t.trade_id,
                "ticker": t.ticker,
                "date": t.date,
                "signal": t.signal,
                "entry_price": t.entry_price,
                "exit_price": t.exit_price,
                "return_pct": t.return_pct,
                "confidence": t.confidence,
                "regime": t.regime,
                "indicators": t.indicators,
                "thesis": t.thesis,
                "outcome": t.outcome,
                "lessons": t.lessons,
                "tags": t.tags,
            })

        with open(self.storage_path, "w", encoding="utf-8") as f:
            json.dump(data, f, ensure_ascii=False, indent=2)

    def _load(self):
        """Load from disk."""
        if self.storage_path.exists():
            try:
                with open(self.storage_path, "r", encoding="utf-8") as f:
                    data = json.load(f)
                self.trades = [TradeRecord(**d) for d in data]
                logger.info(f"Loaded {len(self.trades)} trades from memory")
            except Exception as e:
                logger.warning(f"Failed to load trading memory: {e}")
                self.trades = []


# =============================================================================
# CONVENIENCE FUNCTIONS
# =============================================================================


def query_trading_memory(
    ticker: str = "",
    signal: str = "",
    regime: str = "",
    indicators: Optional[Dict] = None,
) -> Dict:
    """
    Query trading memory for similar past setups.

    Returns:
        Dict with similar trades, win rate, and recommendation
    """
    memory = TradingMemory()
    result = memory.query_similar(ticker, signal, regime, indicators)

    return {
        "n_similar_trades": len(result.similar_trades),
        "win_rate": result.win_rate,
        "avg_return": result.avg_return,
        "recommendation": result.recommendation,
        "confidence_adjustment": result.confidence_adjustment,
        "common_mistakes": result.common_mistakes,
        "success_patterns": result.success_patterns,
        "similar_trades": [
            {
                "ticker": t.ticker,
                "date": t.date,
                "signal": t.signal,
                "return": t.return_pct,
                "outcome": t.outcome,
            }
            for t in result.similar_trades[:5]
        ],
    }


def record_trade_outcome(
    ticker: str,
    date: str,
    signal: str,
    entry_price: float,
    exit_price: float,
    return_pct: float,
    regime: str = "",
    indicators: Optional[Dict] = None,
    thesis: str = "",
    lessons: str = "",
) -> bool:
    """Record a completed trade in memory."""
    memory = TradingMemory()

    outcome = "win" if return_pct > 0.001 else ("loss" if return_pct < -0.001 else "breakeven")

    trade = TradeRecord(
        ticker=ticker,
        date=date,
        signal=signal,
        entry_price=entry_price,
        exit_price=exit_price,
        return_pct=return_pct,
        regime=regime,
        indicators=indicators or {},
        thesis=thesis,
        outcome=outcome,
        lessons=lessons,
    )

    return memory.record_trade(trade)
