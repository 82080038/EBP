"""
Agentic Automation Layer — OpenClaw-style autonomous monitoring & execution.

Implementasi:
- TradingAgent: Autonomous agent yang monitor pasar, evaluate sinyal, dan eksekusi via broker API
- Task decomposition: break goal menjadi subtasks (monitor → analyze → decide → execute → notify)
- Memory function: simpan history keputusan untuk learning
- Tool connectivity: connect ke broker API (stub), Telegram notifier, database
- Safety guardrails: kill switch, max position, daily loss limit

Referensi:
- OpenClaw: open-source agentic harness (Peter Steinberger, 2025)
- CrewAI / LangGraph: multi-agent orchestration
- Professional trading rule: "Automate the boring, keep human in the loop for decisions"

CATATAN: Broker API adalah stub — implementasi tergantung broker yang digunakan
(BCA Sekuritas, IndoPremier, Mirae Asset, dll). Sesuaikan dengan API broker Anda.
"""

import time
import schedule
import threading
from typing import Dict, List, Optional
from dataclasses import dataclass, field
from datetime import datetime
from enum import Enum

import numpy as np

from .config import TARGET_TICKER, TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID
from .notifier import send_in_app, send_telegram
from .database import log_aktivitas
from .paper_trading import PaperTradingEngine


class AgentState(Enum):
    IDLE = "idle"
    MONITORING = "monitoring"
    ANALYZING = "analyzing"
    DECIDING = "deciding"
    EXECUTING = "executing"
    ERROR = "error"


@dataclass
class AgentMemory:
    """Memory store untuk agent — simpan history keputusan."""
    decisions: List[Dict] = field(default_factory=list)
    last_signal: str = "HOLD"
    last_confidence: float = 0.0
    last_run: Optional[str] = None
    total_runs: int = 0
    total_trades: int = 0
    win_count: int = 0

    def record_decision(self, signal: str, confidence: float, action: str, reason: str = ""):
        entry = {
            "timestamp": datetime.now().isoformat(),
            "signal": signal,
            "confidence": confidence,
            "action": action,
            "reason": reason,
        }
        self.decisions.append(entry)
        self.last_signal = signal
        self.last_confidence = confidence
        self.last_run = entry["timestamp"]
        self.total_runs += 1
        if action == "EXECUTE_TRADE":
            self.total_trades += 1
        # Keep last 100 decisions in memory
        if len(self.decisions) > 100:
            self.decisions = self.decisions[-100:]

    def get_stats(self) -> Dict:
        return {
            "total_runs": self.total_runs,
            "total_trades": self.total_trades,
            "win_rate": (self.win_count / self.total_trades * 100) if self.total_trades > 0 else 0,
            "last_signal": self.last_signal,
            "last_run": self.last_run,
        }


@dataclass
class SafetyGuardrails:
    """Safety rules untuk autonomous agent."""
    enabled: bool = True
    kill_switch: bool = False
    max_daily_trades: int = 3
    max_position_pct: float = 0.25  # Max 25% capital per position
    daily_loss_limit_pct: float = 0.05  # Stop if daily loss > 5%
    min_confidence_to_trade: float = 0.65
    prevent_consecutive_losses: int = 3  # Stop after 3 consecutive losses
    trades_today: int = 0
    daily_pnl: float = 0.0
    consecutive_losses: int = 0
    last_reset_date: str = ""
    # Trading style presets
    trading_style: str = "swing"  # "investor", "swing", "day_trader", "scalper"

    # Preset configurations per trading style
    STYLE_PRESETS = {
        "investor": {
            "max_daily_trades": 1,
            "max_position_pct": 0.40,
            "daily_loss_limit_pct": 0.03,
            "min_confidence_to_trade": 0.70,
            "prevent_consecutive_losses": 2,
        },
        "swing": {
            "max_daily_trades": 3,
            "max_position_pct": 0.25,
            "daily_loss_limit_pct": 0.05,
            "min_confidence_to_trade": 0.65,
            "prevent_consecutive_losses": 3,
        },
        "day_trader": {
            "max_daily_trades": 20,
            "max_position_pct": 0.15,
            "daily_loss_limit_pct": 0.04,
            "min_confidence_to_trade": 0.60,
            "prevent_consecutive_losses": 5,
        },
        "scalper": {
            "max_daily_trades": 50,
            "max_position_pct": 0.10,
            "daily_loss_limit_pct": 0.03,
            "min_confidence_to_trade": 0.55,
            "prevent_consecutive_losses": 5,
        },
    }

    def apply_style(self, style: str):
        """Apply trading style preset."""
        if style in self.STYLE_PRESETS:
            preset = self.STYLE_PRESETS[style]
            self.trading_style = style
            self.max_daily_trades = preset["max_daily_trades"]
            self.max_position_pct = preset["max_position_pct"]
            self.daily_loss_limit_pct = preset["daily_loss_limit_pct"]
            self.min_confidence_to_trade = preset["min_confidence_to_trade"]
            self.prevent_consecutive_losses = preset["prevent_consecutive_losses"]

    def reset_daily(self):
        today = datetime.now().strftime("%Y-%m-%d")
        if today != self.last_reset_date:
            self.trades_today = 0
            self.daily_pnl = 0.0
            self.last_reset_date = today

    def can_trade(self, signal: str, confidence: float) -> tuple:
        """Check if agent is allowed to trade. Returns (allowed, reason)."""
        if not self.enabled:
            return False, "Agent disabled"
        if self.kill_switch:
            return False, "Kill switch activated"
        self.reset_daily()
        if self.trades_today >= self.max_daily_trades:
            return False, f"Max daily trades reached ({self.max_daily_trades})"
        if signal == "HOLD":
            return False, "Signal is HOLD"
        if confidence < self.min_confidence_to_trade:
            return False, f"Confidence {confidence:.1%} below minimum {self.min_confidence_to_trade:.1%}"
        if self.consecutive_losses >= self.prevent_consecutive_losses:
            return False, f"Consecutive losses ({self.consecutive_losses}) — cooldown"
        if self.daily_pnl <= -self.daily_loss_limit_pct:
            return False, f"Daily loss limit reached ({self.daily_pnl:.1%})"
        return True, "OK"


class BrokerAPIStub:
    """
    Stub untuk broker API — sesuaikan dengan broker Anda.
    
    Implementasi nyata butuh:
    - BCA Sekuritas: https://api.bcainvest.co.id (perlu token)
    - IndoPremier: https://api.indopremier.com (perlu API key)
    - Mirae Asset: https://api.miraeasset.co.id
    
    Atau gunakan:
    - Bibit API (untuk reksa dana)
    - Stockbit API (untuk data + trading)
    """

    def __init__(self, api_key: str = "", broker: str = "stub"):
        self.api_key = api_key
        self.broker = broker
        self.is_connected = False

    def connect(self) -> bool:
        """Connect ke broker API."""
        if self.broker == "stub":
            self.is_connected = True
            print("[BrokerAPI] Stub mode — no real connection")
            return True
        # TODO: Implement real broker connection
        return False

    def get_portfolio(self) -> Dict:
        """Get current portfolio."""
        if self.broker == "stub":
            return {"cash": 100_000_000, "positions": {}, "total_value": 100_000_000}
        return {}

    def place_order(self, symbol: str, side: str, quantity: int, price: float = 0) -> Dict:
        """Place buy/sell order."""
        if self.broker == "stub":
            order_id = f"STUB_{datetime.now().strftime('%Y%m%d%H%M%S')}"
            print(f"[BrokerAPI] STUB ORDER: {side} {quantity} {symbol} @ {price:.2f} (ID: {order_id})")
            return {
                "order_id": order_id,
                "status": "FILLED" if self.is_connected else "REJECTED",
                "symbol": symbol,
                "side": side,
                "quantity": quantity,
                "price": price,
                "timestamp": datetime.now().isoformat(),
            }
        # TODO: Implement real broker order
        return {"status": "NOT_IMPLEMENTED"}

    def get_position(self, symbol: str) -> Optional[Dict]:
        """Get current position for symbol."""
        portfolio = self.get_portfolio()
        return portfolio.get("positions", {}).get(symbol)


class TradingAgent:
    """
    Autonomous trading agent — OpenClaw-style.
    
    Workflow (task decomposition):
    1. MONITOR: Fetch market data, check if market is open
    2. ANALYZE: Run prediction model, get signal + confidence
    3. DECIDE: Apply safety guardrails, decide whether to trade
    4. EXECUTE: Place order via broker API (if approved)
    5. NOTIFY: Send Telegram/email notification with decision
    6. LEARN: Record decision in memory for future reference
    """

    def __init__(
        self,
        paper_engine: Optional[PaperTradingEngine] = None,
        guardrails: Optional[SafetyGuardrails] = None,
        auto_execute: bool = False,
    ):
        self.state = AgentState.IDLE
        self.memory = AgentMemory()
        self.guardrails = guardrails or SafetyGuardrails()
        self.paper = paper_engine or PaperTradingEngine()
        self.auto_execute = auto_execute
        self._scheduler_thread = None
        self._running = False
        # P10: Online learning
        self.online_learner = None
        try:
            from .adaptive_learning import OnlineLearner
            self.online_learner = OnlineLearner()
            self.online_learner.load()
        except Exception:
            pass
        # P11: RL agent
        self.rl_agent = None
        try:
            from .adaptive_learning import TradingRLAgent
            self.rl_agent = TradingRLAgent()
            self.rl_agent.load()
        except Exception:
            pass

    def _set_state(self, state: AgentState, log: str = ""):
        self.state = state
        if log:
            print(f"[Agent] {state.value}: {log}")

    def run_cycle(self, market_data: Dict, prediction_result: Dict, context_data: Dict = None) -> Dict:
        """
        Run one complete agent cycle: monitor → analyze → decide → execute → notify.
        
        Auto-execution logic:
        - BUY signal → open LONG position (if no existing position)
        - SELL signal → close existing LONG position (if any)
        - HOLD signal → check stop loss & take profit on existing positions
        
        GAP 1: Now integrates sentiment, event risk, and fundamental data
        via context_data parameter to adjust trading decisions.
        
        Args:
            market_data: Market price data
            prediction_result: ML prediction output
            context_data: Optional dict with keys:
                - sentiment_score: float (-100 to 100)
                - fear_greed_index: float (0-100)
                - event_risk_score: float (0-100)
                - news_direction: str ("bullish", "bearish", "neutral")
                - impact_assessments: List[ImpactAssessment]
                - fundamental: FundamentalData
        """
        self._set_state(AgentState.MONITORING, "Starting cycle")
        
        signal = prediction_result.get("sinyal", "HOLD")
        confidence = prediction_result.get("confidence", 0.0)
        ticker = prediction_result.get("ticker", TARGET_TICKER)
        current_price = prediction_result.get("current_price", 0)
        predicted_price = prediction_result.get("predicted_price", 0)
        prediction_result.get("atr", 0)
        prediction_result.get("entry", current_price)
        stop_loss_level = prediction_result.get("stop_loss", 0)
        target_1 = prediction_result.get("target_1", 0)
        prediction_result.get("target_2", 0)

        # === MARKET HOURS CHECK ===
        context_notes = []
        market_closed = False
        market_status_note = ""
        try:
            from .market_hours import MarketHours
            mh = MarketHours()
            exchange = mh.get_exchange_for_ticker(ticker) or "IDX"
            status = mh.get_status(exchange)
            if not status.is_open:
                if status.session.value == "holiday":
                    market_closed = True
                    market_status_note = f"⛔ {exchange} closed (Holiday: {status.holiday_name})"
                elif status.session.value == "weekend":
                    market_closed = True
                    market_status_note = f"⛔ {exchange} closed (Weekend)"
                elif status.session.value in ("closed", "post_close"):
                    market_closed = True
                    market_status_note = f"⛔ {exchange} closed (next open: {status.next_open})"
                elif status.session.value == "lunch_break":
                    market_status_note = f"⏸️ {exchange} lunch break (resumes: {status.next_open})"
                elif status.session.value == "pre_open":
                    market_status_note = f"⏳ {exchange} pre-open (opens: {status.countdown_open})"
        except Exception:
            pass

        if market_closed and signal in ("BUY", "SELL"):
            context_notes.append(market_status_note)
            signal = "HOLD"
            context_notes.append("🔄 Signal changed to HOLD — market closed")

        # === GAP 1: INTEGRATE CONTEXT DATA ===
        adjusted_confidence = confidence
        signal_override = None

        if context_data:
            # Sentiment adjustment
            sentiment_score = context_data.get("sentiment_score", 0)
            context_data.get("fear_greed_index", 50)
            
            if sentiment_score < -30 and signal == "BUY":
                adjusted_confidence *= 0.7  # Reduce confidence in extreme fear
                context_notes.append(f"⚠️ Extreme fear (sentiment: {sentiment_score:.0f}) — BUY confidence reduced 30%")
            elif sentiment_score > 30 and signal == "BUY":
                adjusted_confidence = min(1.0, adjusted_confidence * 1.1)
                context_notes.append(f"✅ Strong sentiment (score: {sentiment_score:.0f}) — BUY confidence boosted 10%")
            elif sentiment_score < -50:
                if signal == "HOLD" and self.paper.get_position(ticker):
                    signal_override = "SELL"
                    context_notes.append("🚨 Extreme negative sentiment → override HOLD to SELL")

            # Event risk adjustment
            event_risk = context_data.get("event_risk_score", 0)
            if event_risk > 60:
                adjusted_confidence *= 0.6
                context_notes.append(f"⚠️ High event risk ({event_risk:.0f}/100) — confidence reduced 40%")
            elif event_risk > 40:
                adjusted_confidence *= 0.8
                context_notes.append(f"⚠️ Moderate event risk ({event_risk:.0f}/100) — confidence reduced 20%")

            # Impact assessments for this ticker
            impacts = context_data.get("impact_assessments", [])
            ticker_impacts = [i for i in impacts if i.get("ticker") == ticker]
            if ticker_impacts:
                bearish_impacts = [i for i in ticker_impacts if i.get("direction") == "bearish"]
                bullish_impacts = [i for i in ticker_impacts if i.get("direction") == "bullish"]
                
                if bearish_impacts and signal == "BUY":
                    adjusted_confidence *= 0.65
                    context_notes.append(f"🔴 {len(bearish_impacts)} bearish impact(s) for {ticker} — confidence reduced 35%")
                elif bullish_impacts and signal == "BUY":
                    adjusted_confidence = min(1.0, adjusted_confidence * 1.15)
                    context_notes.append(f"🟢 {len(bullish_impacts)} bullish impact(s) for {ticker} — confidence boosted 15%")
                elif bearish_impacts and signal == "HOLD" and self.paper.get_position(ticker):
                    signal_override = "SELL"
                    context_notes.append("🔴 Bearish impact detected → override HOLD to SELL")

            # Fundamental data
            fundamental = context_data.get("fundamental")
            if fundamental and hasattr(fundamental, 'pe_ratio') and fundamental.pe_ratio:
                if fundamental.pe_ratio > 50 and signal == "BUY":
                    adjusted_confidence *= 0.8
                    context_notes.append(f"⚠️ High P/E ({fundamental.pe_ratio:.1f}) — overvalued, confidence reduced 20%")
                elif fundamental.pe_ratio and fundamental.pe_ratio < 10 and signal == "BUY":
                    adjusted_confidence = min(1.0, adjusted_confidence * 1.1)
                    context_notes.append(f"✅ Low P/E ({fundamental.pe_ratio:.1f}) — undervalued, confidence boosted 10%")

        # Fundamental data fallback from DB
        if not context_data or not context_data.get("fundamental"):
            try:
                from .database import load_fundamental
                fund = load_fundamental(ticker)
                if fund and fund.get("pe_ratio"):
                    pe = fund["pe_ratio"]
                    if pe > 50 and signal == "BUY":
                        adjusted_confidence *= 0.8
                        context_notes.append(f"⚠️ High P/E ({pe:.1f} from DB) — overvalued, confidence reduced 20%")
                    elif pe < 10 and signal == "BUY":
                        adjusted_confidence = min(1.0, adjusted_confidence * 1.1)
                        context_notes.append(f"✅ Low P/E ({pe:.1f} from DB) — undervalued, confidence boosted 10%")
                    if fund.get("roe") and fund["roe"] > 0.20 and signal == "BUY":
                        context_notes.append(f"✅ Strong ROE ({fund['roe']:.1%}) — quality company")
                    if fund.get("debt_to_equity") and fund["debt_to_equity"] > 200 and signal == "BUY":
                        adjusted_confidence *= 0.85
                        context_notes.append(f"⚠️ High debt/equity ({fund['debt_to_equity']:.0f}) — leverage risk")
            except Exception:
                pass

        # Apply override
        if signal_override:
            signal = signal_override
            prediction_result = {**prediction_result, "sinyal": signal}

        confidence = adjusted_confidence
        self._set_state(AgentState.ANALYZING, f"Signal={signal}, Confidence={confidence:.1%}, Context={len(context_notes)} notes")

        # === CHECK EXISTING POSITIONS ===
        existing_pos = self.paper.get_position(ticker)
        if existing_pos:
            # Update current price and check SL/TP
            sl_tp_result = self.paper.hold(ticker, current_price, reason="AGENT_CYCLE")
            if sl_tp_result.get("status") == "FILLED":
                # Position was closed by SL/TP
                self._set_state(AgentState.EXECUTING, f"Position closed: {sl_tp_result.get('pnl', 0):,.0f}")
                decision = {
                    "timestamp": datetime.now().isoformat(),
                    "signal": signal,
                    "confidence": confidence,
                    "ticker": ticker,
                    "current_price": current_price,
                    "action": sl_tp_result.get("reason", "CLOSED"),
                    "order": sl_tp_result,
                    "reason": "Auto SL/TP triggered",
                }
                self.memory.record_decision(signal, confidence, decision["action"], "SL/TP triggered")
                self._notify(decision)
                log_aktivitas("AGENT", f"SL/TP closed {ticker}: PnL {sl_tp_result.get('pnl', 0):,.0f}")
                self._set_state(AgentState.IDLE, "Cycle complete (SL/TP)")
                return decision

        # === DECIDE ===
        self._set_state(AgentState.DECIDING)
        can_trade, reason = self.guardrails.can_trade(signal, confidence)

        # P5: IDX Rules & Compliance checks before execution
        idx_notes = []
        if can_trade and signal in ("BUY", "SELL") and current_price > 0:
            try:
                from .idx_rules import check_auto_rejection
                # Auto-rejection check (use current price as both ref and current for simplicity)
                ar = check_auto_rejection(current_price, current_price)
                if ar.is_rejected:
                    idx_notes.append(f"⛔ IDX auto-rejection: {ar.description}")
                    can_trade = False
                    reason = f"IDX auto-rejection: {ar.direction}"

                # Circuit breaker would need IHSG data — skip if not available
            except Exception:
                pass

            try:
                from .compliance import log_prediction_audit
                log_prediction_audit(
                    ticker=ticker, signal=signal, confidence=confidence,
                    actor="system", source="trading_agent",
                )
            except Exception:
                pass

        if idx_notes:
            context_notes.extend(idx_notes)

        # P11: Get RL agent suggestion
        rl_action = None
        rl_action_name = ""
        if self.rl_agent:
            try:
                from .adaptive_learning import TradingState
                signal_map = {"SELL": 0, "HOLD": 1, "BUY": 2}
                rl_state = TradingState(
                    signal=signal_map.get(signal, 1),
                    confidence=confidence,
                    sentiment=context_data.get("sentiment_score", 0) if context_data else 0,
                    event_risk=context_data.get("event_risk_score", 0) if context_data else 0,
                    has_position=existing_pos is not None,
                    pnl_pct=0.0,
                    rsi=prediction_result.get("rsi", 50),
                    trend=2 if signal == "BUY" else 0 if signal == "SELL" else 1,
                )
                rl_action = self.rl_agent.choose_action(rl_state, training=False)
                rl_action_name = self.rl_agent.get_action_name(rl_action)
                if rl_action_name != signal:
                    context_notes.append(f"🤖 RL suggests: {rl_action_name} (ML says: {signal})")
            except Exception:
                pass

        decision = {
            "timestamp": datetime.now().isoformat(),
            "signal": signal,
            "confidence": confidence,
            "ticker": ticker,
            "current_price": current_price,
            "predicted_price": predicted_price,
            "can_trade": can_trade,
            "reason": reason,
            "action": "MONITOR_ONLY",
            "order": None,
            "context_notes": context_notes,
            "original_confidence": prediction_result.get("confidence", 0.0),
            "rl_suggestion": rl_action_name,
        }

        # === EXECUTE ===
        if signal == "SELL" and existing_pos:
            # Close existing position on SELL signal
            self._set_state(AgentState.EXECUTING, f"Closing {ticker} position on SELL signal")
            result = self.paper.sell(ticker, existing_pos.quantity, current_price, reason="ML_SIGNAL_SELL")
            decision["action"] = "CLOSE_POSITION"
            decision["order"] = result
            if result.get("status") == "FILLED":
                self.guardrails.trades_today += 1
                if result.get("pnl", 0) < 0:
                    self.guardrails.consecutive_losses += 1
                else:
                    self.guardrails.consecutive_losses = 0

        elif signal == "BUY" and not existing_pos and can_trade and self.auto_execute:
            # Open new LONG position on BUY signal
            self._set_state(AgentState.EXECUTING, f"Opening {ticker} position on BUY signal")
            
            # Calculate position size from guardrails
            max_value = self.paper.cash * self.guardrails.max_position_pct
            quantity = int(max_value / current_price) if current_price > 0 else 0
            
            # Use ATR-based stop loss if available, otherwise use 5% below entry
            sl = stop_loss_level if stop_loss_level > 0 else current_price * 0.95
            # Use target_1 as take profit if available
            tp = target_1 if target_1 > 0 else current_price * 1.05
            
            if quantity > 0:
                result = self.paper.buy(
                    ticker=ticker,
                    quantity=quantity,
                    price=current_price,
                    stop_loss=sl,
                    take_profit=tp,
                    confidence=confidence,
                    signal=signal,
                    reason=f"ML signal BUY (conf: {confidence:.1%})",
                )
                decision["action"] = "OPEN_POSITION"
                decision["order"] = result
                if result.get("status") == "FILLED":
                    self.guardrails.trades_today += 1
            else:
                decision["action"] = "SKIP_INSUFFICIENT_FUNDS"

        elif signal == "HOLD" and existing_pos:
            # Hold existing position — already checked SL/TP above
            decision["action"] = "HOLD_POSITION"
            decision["order"] = {
                "status": "HOLDING",
                "ticker": ticker,
                "unrealized_pnl": existing_pos.unrealized_pnl,
                "unrealized_pnl_pct": existing_pos.unrealized_pnl_pct,
                "stop_loss": existing_pos.stop_loss,
                "take_profit": existing_pos.take_profit,
            }

        else:
            decision["action"] = "MONITOR_ONLY" if not can_trade else "MONITOR_NO_AUTO_EXECUTE"

        # === LEARN ===
        self.memory.record_decision(signal, confidence, decision["action"], reason)

        # P11: RL agent learns from this cycle's outcome
        if self.rl_agent and rl_action is not None:
            try:
                from .adaptive_learning import TradingState
                # Calculate reward from action taken
                order = decision.get("order", {})
                pnl = order.get("pnl", 0) if isinstance(order, dict) else 0
                pnl_pct = (pnl / (current_price * 100)) if current_price > 0 else 0
                reward = self.rl_agent.calculate_reward(
                    action=rl_action,
                    pnl_pct=pnl_pct,
                    has_position=existing_pos is not None,
                )
                # Next state (simplified — same state since we don't have next period data yet)
                next_state = TradingState(
                    signal=signal_map.get(signal, 1),
                    confidence=confidence,
                    sentiment=context_data.get("sentiment_score", 0) if context_data else 0,
                    event_risk=context_data.get("event_risk_score", 0) if context_data else 0,
                    has_position=self.paper.get_position(ticker) is not None,
                    pnl_pct=pnl_pct,
                    rsi=prediction_result.get("rsi", 50),
                    trend=2 if signal == "BUY" else 0 if signal == "SELL" else 1,
                )
                self.rl_agent.learn(rl_state, rl_action, reward, next_state, done=False)
                self.rl_agent.save()
            except Exception:
                pass

        # P10: Online learner update (if features available)
        if self.online_learner and "features" in prediction_result:
            try:
                features = np.array(prediction_result["features"]).reshape(1, -1)
                label = np.array([signal_map.get(signal, 1)])
                self.online_learner.partial_fit(features, label)
                self.online_learner.save()
            except Exception:
                pass

        # === NOTIFY ===
        self._notify(decision)

        # === LOG ===
        log_aktivitas(
            "AGENT",
            f"Signal={signal}, Confidence={confidence:.1%}, Action={decision['action']}, Reason={reason}",
        )

        self._set_state(AgentState.IDLE, "Cycle complete")
        return decision

    def run_multi_ticker_cycle(
        self,
        market_data: Dict,
        prediction_results: Dict[str, Dict],
        max_positions: int = 5,
    ) -> List[Dict]:
        """
        Run multi-ticker scanning cycle — evaluate signals for multiple tickers,
        allocate funds to best opportunities.
        
        Args:
            market_data: Dict of ticker → DataFrame
            prediction_results: Dict of ticker → prediction_result
            max_positions: Max simultaneous positions
        
        Logic:
        1. Collect all BUY signals sorted by confidence
        2. Check existing positions for SL/TP
        3. Close positions on SELL signal
        4. Open new positions for top BUY signals (up to max_positions)
        5. Allocate cash equally or by confidence weight
        """
        self._set_state(AgentState.MONITORING, "Multi-ticker scan starting")
        decisions = []

        # Step 1: Check existing positions for SL/TP and SELL signals
        self._set_state(AgentState.ANALYZING, "Checking existing positions")
        open_positions = self.paper.get_open_positions()
        for pos in open_positions:
            ticker = pos.ticker
            pred = prediction_results.get(ticker, {})
            current_price = pred.get("current_price", pos.current_price)

            # Check SL/TP
            sl_tp = self.paper.hold(ticker, current_price, reason="MULTI_CYCLE_CHECK")
            if sl_tp.get("status") == "FILLED":
                decisions.append({
                    "ticker": ticker,
                    "action": sl_tp.get("reason", "CLOSED"),
                    "order": sl_tp,
                    "signal": pred.get("sinyal", "HOLD"),
                    "confidence": pred.get("confidence", 0),
                })
                continue

            # Check SELL signal
            signal = pred.get("sinyal", "HOLD")
            if signal == "SELL":
                result = self.paper.sell(ticker, pos.quantity, current_price, reason="ML_SIGNAL_SELL")
                decisions.append({
                    "ticker": ticker,
                    "action": "CLOSE_POSITION",
                    "order": result,
                    "signal": signal,
                    "confidence": pred.get("confidence", 0),
                })

        # Step 2: Collect BUY signals
        self._set_state(AgentState.DECIDING, "Evaluating BUY signals")
        buy_candidates = []
        for ticker, pred in prediction_results.items():
            signal = pred.get("sinyal", "HOLD")
            confidence = pred.get("confidence", 0)
            if signal == "BUY" and confidence >= self.guardrails.min_confidence_to_trade:
                # Skip if already holding
                if self.paper.get_position(ticker):
                    continue
                buy_candidates.append({
                    "ticker": ticker,
                    "confidence": confidence,
                    "current_price": pred.get("current_price", 0),
                    "stop_loss": pred.get("stop_loss", 0),
                    "target_1": pred.get("target_1", 0),
                    "prediction": pred,
                })

        # Sort by confidence descending
        buy_candidates.sort(key=lambda x: x["confidence"], reverse=True)

        # Step 3: Allocate funds and open positions
        current_open = len(self.paper.get_open_positions())
        available_slots = max_positions - current_open

        if available_slots > 0 and buy_candidates and self.auto_execute:
            self._set_state(AgentState.EXECUTING, f"Opening {min(available_slots, len(buy_candidates))} positions")

            # Allocate cash equally among candidates
            # Reserve 10% cash buffer
            investable_cash = self.paper.cash * 0.9
            per_position = investable_cash / available_slots if available_slots > 0 else 0

            for candidate in buy_candidates[:available_slots]:
                ticker = candidate["ticker"]
                price = candidate["current_price"]
                if price <= 0:
                    continue

                # Check guardrails
                can_trade, reason = self.guardrails.can_trade("BUY", candidate["confidence"])
                if not can_trade:
                    decisions.append({
                        "ticker": ticker,
                        "action": "SKIP_GUARDRAILS",
                        "reason": reason,
                        "signal": "BUY",
                        "confidence": candidate["confidence"],
                    })
                    continue

                quantity = int(per_position / price)
                if quantity <= 0:
                    decisions.append({
                        "ticker": ticker,
                        "action": "SKIP_INSUFFICIENT_FUNDS",
                        "signal": "BUY",
                        "confidence": candidate["confidence"],
                    })
                    continue

                sl = candidate["stop_loss"] if candidate["stop_loss"] > 0 else price * 0.95
                tp = candidate["target_1"] if candidate["target_1"] > 0 else price * 1.05

                result = self.paper.buy(
                    ticker=ticker,
                    quantity=quantity,
                    price=price,
                    stop_loss=sl,
                    take_profit=tp,
                    confidence=candidate["confidence"],
                    signal="BUY",
                    reason=f"Multi-ticker BUY (conf: {candidate['confidence']:.1%})",
                )
                decisions.append({
                    "ticker": ticker,
                    "action": "OPEN_POSITION",
                    "order": result,
                    "signal": "BUY",
                    "confidence": candidate["confidence"],
                })
                self.guardrails.trades_today += 1

        # Step 4: Summary notification
        opened = sum(1 for d in decisions if d["action"] == "OPEN_POSITION")
        closed = sum(1 for d in decisions if "CLOSE" in d["action"] or "STOP" in d["action"] or "TAKE" in d["action"])
        held = sum(1 for d in decisions if d["action"] == "HOLD_POSITION" or d.get("order", {}).get("status") == "HOLDING")

        try:
            send_in_app(
                kategori="AGENT",
                judul=f"📊 Multi-Ticker Scan: {len(decisions)} decisions ({opened} buy, {closed} sell, {held} hold)",
                pesan=(
                    f"Scanned {len(prediction_results)} tickers\n"
                    f"Decisions: {len(decisions)}\n"
                    f"  Opened: {opened}\n"
                    f"  Closed: {closed}\n"
                    f"  Holding: {held}\n"
                    f"Open positions: {len(self.paper.get_open_positions())}\n"
                    f"Cash: Rp {self.paper.cash:,.0f}\n"
                    f"Buy candidates: {len(buy_candidates)} (top conf: {buy_candidates[0]['confidence']:.1%})" if buy_candidates else ""
                ),
                level="info",
            )
        except Exception:
            pass

        log_aktivitas("AGENT", f"Multi-ticker scan: {len(decisions)} decisions, {opened} opened, {closed} closed")
        self._set_state(AgentState.IDLE, "Multi-ticker cycle complete")
        return decisions

    def _notify(self, decision: Dict):
        """Send notification via in-app (primary) and Telegram (optional)."""
        signal = decision["signal"]
        confidence = decision["confidence"]
        action = decision["action"]
        ticker = decision["ticker"]
        price = decision["current_price"]

        emoji = "🟢" if signal == "BUY" else "🔴" if signal == "SELL" else "🟡"
        level = "success" if signal == "BUY" else "warning" if signal == "SELL" else "info"
        msg = (
            f"{emoji} Trading Agent Update\n"
            f"Ticker: {ticker}\n"
            f"Signal: {signal} ({confidence:.1%})\n"
            f"Price: {price:,.2f}\n"
            f"Action: {action}\n"
            f"Time: {decision['timestamp']}\n"
            f"Stats: {self.memory.total_runs} runs, {self.memory.total_trades} trades"
        )

        # In-app notification (selalu tersedia)
        try:
            send_in_app(
                kategori="AGENT",
                judul=f"{emoji} {signal} {ticker} — {action}",
                pesan=msg,
                level=level,
            )
        except Exception as e:
            print(f"[Agent] In-app notify failed: {e}")

        # Telegram (opsional, jika dikonfigurasi)
        try:
            if TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID:
                send_telegram(msg)
        except Exception as e:
            print(f"[Agent] Telegram notify failed: {e}")

    def start_scheduled(self, run_time: str = "09:05"):
        """
        Start agent on schedule (default: 09:05 WIB, setelah market open).
        
        Args:
            run_time: HH:MM format (24h, WIB time)
        """
        if self._running:
            print("[Agent] Already running")
            return

        def job():
            print(f"[Agent] Scheduled run at {datetime.now()}")
            try:
                from .data_fetcher import fetch_all_data
                from .predictor import run_prediction
                from .sentiment_pipeline import DailySentimentPipeline
                from .event_driven import run_event_analysis

                data = fetch_all_data(period="2y")
                if not data or "market" not in data:
                    print("[Agent] No data — skipping")
                    return

                result = run_prediction(
                    market_data=data["market"],
                    fred_data=data.get("fred"),
                    target_ticker=TARGET_TICKER,
                )

                if "error" not in result:
                    # GAP 1: Fetch context data for informed decisions
                    context = {}
                    try:
                        sentiment_pipeline = DailySentimentPipeline()
                        latest_sentiment = sentiment_pipeline.get_latest_sentiment()
                        if latest_sentiment:
                            context["sentiment_score"] = latest_sentiment.sentiment_score
                            context["fear_greed_index"] = latest_sentiment.fear_greed_index
                    except Exception:
                        pass

                    try:
                        event_analysis = run_event_analysis(
                            data["market"], target_ticker=TARGET_TICKER,
                            include_economic=True, include_news=True,
                        )
                        context["event_risk_score"] = event_analysis.event_risk_score
                    except Exception:
                        pass

                    self.run_cycle(data["market"], result, context_data=context)
                else:
                    print(f"[Agent] Prediction error: {result['error']}")
            except Exception as e:
                print(f"[Agent] Run error: {e}")
                self._set_state(AgentState.ERROR, str(e))

        schedule.every().day.at(run_time).do(job)
        self._running = True

        def run_scheduler():
            while self._running:
                schedule.run_pending()
                time.sleep(60)

        self._scheduler_thread = threading.Thread(target=run_scheduler, daemon=True)
        self._scheduler_thread.start()
        print(f"[Agent] Scheduled daily at {run_time} WIB")

    def stop_scheduled(self):
        """Stop scheduled agent."""
        self._running = False
        schedule.clear()
        if self._scheduler_thread:
            self._scheduler_thread.join(timeout=5)
        print("[Agent] Stopped")

    def get_status(self) -> Dict:
        """Get current agent status."""
        paper_stats = self.paper.get_stats()
        return {
            "state": self.state.value,
            "auto_execute": self.auto_execute,
            "memory": self.memory.get_stats(),
            "guardrails": {
                "kill_switch": self.guardrails.kill_switch,
                "trades_today": self.guardrails.trades_today,
                "max_daily_trades": self.guardrails.max_daily_trades,
                "consecutive_losses": self.guardrails.consecutive_losses,
                "daily_pnl": self.guardrails.daily_pnl,
            },
            "paper_trading": paper_stats,
            "open_positions": len(self.paper.get_open_positions()),
            "running": self._running,
        }

    def activate_kill_switch(self):
        """Emergency stop — halt all trading."""
        self.guardrails.kill_switch = True
        self._set_state(AgentState.ERROR, "KILL SWITCH ACTIVATED")
        try:
            send_in_app(
                kategori="AGENT",
                judul="🚨 KILL SWITCH ACTIVATED",
                pesan="Trading agent dihentikan darurat. Semua trading halted.",
                level="error",
            )
        except Exception:
            pass
        try:
            if TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID:
                send_telegram("🚨 KILL SWITCH ACTIVATED — Trading agent dihentikan!")
        except Exception:
            pass
        print("[Agent] KILL SWITCH ACTIVATED — All trading halted")

    def deactivate_kill_switch(self):
        """Reset kill switch."""
        self.guardrails.kill_switch = False
        self.guardrails.consecutive_losses = 0
        self._set_state(AgentState.IDLE, "Kill switch deactivated")
        print("[Agent] Kill switch deactivated — Agent ready")
