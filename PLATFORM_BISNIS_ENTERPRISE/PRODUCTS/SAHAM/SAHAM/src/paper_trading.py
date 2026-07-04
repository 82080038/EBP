"""
Paper Trading Engine — simulasi trading lengkap tanpa uang real.

Fitur:
- Virtual portfolio (cash + positions)
- Buy/Sell/Hold dengan market atau limit order
- Stop loss & take profit otomatis
- Position tracking: entry price, current price, PnL unrealized/realized
- Trade journal: semua transaksi tersimpan di JSON
- Daily PnL reset untuk guardrails
- Integrasi dengan TradingAgent

Data tersimpan di: src/data/paper_portfolio.json dan src/data/trade_journal.json
"""

import os
import json
from typing import Dict, List, Optional
from dataclasses import dataclass, field, asdict
from datetime import datetime
from enum import Enum

from .config import DATA_DIR
from .notifier import send_in_app


class OrderSide(Enum):
    BUY = "BUY"
    SELL = "SELL"


class OrderStatus(Enum):
    FILLED = "FILLED"
    REJECTED = "REJECTED"
    PENDING = "PENDING"


class PositionStatus(Enum):
    OPEN = "OPEN"
    CLOSED = "CLOSED"
    STOP_LOSS = "STOP_LOSS"
    TAKE_PROFIT = "TAKE_PROFIT"


@dataclass
class Position:
    ticker: str
    side: str  # "LONG" or "SHORT"
    quantity: int
    entry_price: float
    entry_date: str
    stop_loss: float = 0.0
    take_profit: float = 0.0
    current_price: float = 0.0
    unrealized_pnl: float = 0.0
    unrealized_pnl_pct: float = 0.0
    status: str = "OPEN"
    exit_price: float = 0.0
    exit_date: str = ""
    realized_pnl: float = 0.0
    realized_pnl_pct: float = 0.0
    hold_reason: str = ""  # Why we're holding
    confidence_at_entry: float = 0.0
    signal_at_entry: str = ""
    exchange: str = "IDX"  # Exchange code (IDX, NYSE, NASDAQ, TSE, HKEX, SGX, SSE, LSE)
    currency: str = "IDR"  # Trading currency
    # Trailing stop loss
    trailing_stop_enabled: bool = False
    trailing_stop_pct: float = 0.0  # e.g. 5.0 = 5% trailing
    highest_price_since_entry: float = 0.0  # Track peak for trailing
    # Partial exit
    partial_exits_taken: List[str] = field(default_factory=list)  # e.g. ["TP1", "TP2"]
    target_1: float = 0.0  # First partial exit target
    target_2: float = 0.0  # Second partial exit target
    target_3: float = 0.0  # Final target (full exit)
    partial_exit_pct_1: float = 50.0  # % to sell at target 1
    partial_exit_pct_2: float = 30.0  # % to sell at target 2
    # partial_exit_pct_3 is remainder (20%)

    def update_current_price(self, price: float):
        self.current_price = price
        # Track highest price for trailing stop (LONG only)
        if self.side == "LONG" and self.status == "OPEN":
            if price > self.highest_price_since_entry:
                self.highest_price_since_entry = price
            # Update trailing stop loss
            if self.trailing_stop_enabled and self.trailing_stop_pct > 0:
                new_trail = self.highest_price_since_entry * (1 - self.trailing_stop_pct / 100)
                if new_trail > self.stop_loss:
                    self.stop_loss = new_trail
        if self.status == "OPEN":
            if self.side == "LONG":
                self.unrealized_pnl = (price - self.entry_price) * self.quantity
                self.unrealized_pnl_pct = ((price - self.entry_price) / self.entry_price) * 100
            else:  # SHORT
                self.unrealized_pnl = (self.entry_price - price) * self.quantity
                self.unrealized_pnl_pct = ((self.entry_price - price) / self.entry_price) * 100

    def check_stop_loss(self) -> bool:
        if self.status != "OPEN" or self.stop_loss <= 0:
            return False
        if self.side == "LONG" and self.current_price <= self.stop_loss:
            return True
        if self.side == "SHORT" and self.current_price >= self.stop_loss:
            return True
        return False

    def check_take_profit(self) -> bool:
        if self.status != "OPEN" or self.take_profit <= 0:
            return False
        if self.side == "LONG" and self.current_price >= self.take_profit:
            return True
        if self.side == "SHORT" and self.current_price <= self.take_profit:
            return True
        return False

    def close(self, exit_price: float, reason: str = ""):
        self.exit_price = exit_price
        self.exit_date = datetime.now().isoformat()
        if self.side == "LONG":
            self.realized_pnl = (exit_price - self.entry_price) * self.quantity
            self.realized_pnl_pct = ((exit_price - self.entry_price) / self.entry_price) * 100
        else:  # SHORT
            self.realized_pnl = (self.entry_price - exit_price) * self.quantity
            self.realized_pnl_pct = ((self.entry_price - exit_price) / self.entry_price) * 100
        self.status = reason or "CLOSED"
        self.unrealized_pnl = 0.0
        self.unrealized_pnl_pct = 0.0


@dataclass
class TradeRecord:
    timestamp: str
    ticker: str
    side: str
    quantity: int
    price: float
    status: str
    order_id: str
    reason: str = ""
    pnl: float = 0.0
    confidence: float = 0.0
    signal: str = ""


@dataclass
class CashFlowRecord:
    timestamp: str
    type: str  # "DEPOSIT" or "WITHDRAW"
    amount: float
    balance_after: float
    reason: str = ""


class PaperTradingEngine:
    """
    Paper trading engine — virtual portfolio dengan auto stop loss & take profit.
    
    Portfolio disimpan di JSON. Semua transaksi di-log ke trade journal.
    Integrasi dengan TradingAgent untuk auto-execution berdasarkan sinyal ML.
    """

    PORTFOLIO_FILE = os.path.join(DATA_DIR, "paper_portfolio.json")
    JOURNAL_FILE = os.path.join(DATA_DIR, "trade_journal.json")

    def __init__(self, initial_capital: float = 100_000_000):
        self.initial_capital = initial_capital
        self.cash = initial_capital
        self.positions: List[Position] = []
        self.trade_history: List[Dict] = []
        self.cash_flow: List[Dict] = []
        self.total_deposited = initial_capital
        self.total_withdrawn = 0.0
        self.daily_pnl: float = 0.0
        self.daily_pnl_date: str = ""
        self.total_realized_pnl: float = 0.0
        self.total_trades: int = 0
        self.winning_trades: int = 0
        self.losing_trades: int = 0
        self._load()

    def _load(self):
        """Load portfolio dan trade journal dari file."""
        if os.path.exists(self.PORTFOLIO_FILE):
            with open(self.PORTFOLIO_FILE, "r") as f:
                data = json.load(f)
                self.cash = data.get("cash", self.initial_capital)
                self.initial_capital = data.get("initial_capital", self.initial_capital)
                self.total_deposited = data.get("total_deposited", self.initial_capital)
                self.total_withdrawn = data.get("total_withdrawn", 0.0)
                self.total_realized_pnl = data.get("total_realized_pnl", 0.0)
                self.total_trades = data.get("total_trades", 0)
                self.winning_trades = data.get("winning_trades", 0)
                self.losing_trades = data.get("losing_trades", 0)
                self.daily_pnl = data.get("daily_pnl", 0.0)
                self.daily_pnl_date = data.get("daily_pnl_date", "")
                self.positions = [Position(**p) for p in data.get("positions", [])]

        if os.path.exists(self.JOURNAL_FILE):
            with open(self.JOURNAL_FILE, "r") as f:
                journal = json.load(f)
                self.trade_history = journal.get("trades", journal) if isinstance(journal, dict) else journal
                self.cash_flow = journal.get("cash_flow", []) if isinstance(journal, dict) else []

    def _save(self):
        """Save portfolio dan trade journal ke file."""
        data = {
            "cash": self.cash,
            "initial_capital": self.initial_capital,
            "total_deposited": self.total_deposited,
            "total_withdrawn": self.total_withdrawn,
            "total_realized_pnl": self.total_realized_pnl,
            "total_trades": self.total_trades,
            "winning_trades": self.winning_trades,
            "losing_trades": self.losing_trades,
            "daily_pnl": self.daily_pnl,
            "daily_pnl_date": self.daily_pnl_date,
            "positions": [asdict(p) for p in self.positions],
        }
        with open(self.PORTFOLIO_FILE, "w") as f:
            json.dump(data, f, indent=2, default=str)

        with open(self.JOURNAL_FILE, "w") as f:
            journal = {"trades": self.trade_history[-200:], "cash_flow": self.cash_flow[-100:]}
            json.dump(journal, f, indent=2, default=str)

    def _reset_daily_pnl(self):
        """Reset daily PnL jika ganti hari."""
        today = datetime.now().strftime("%Y-%m-%d")
        if today != self.daily_pnl_date:
            self.daily_pnl = 0.0
            self.daily_pnl_date = today

    def _log_trade(self, record: TradeRecord):
        """Log trade ke journal."""
        self.trade_history.append(asdict(record))
        if len(self.trade_history) > 200:
            self.trade_history = self.trade_history[-200:]

    def get_portfolio_value(self, current_prices: Dict[str, float] = None) -> float:
        """Hitung total portfolio value (cash + positions value)."""
        total = self.cash
        for pos in self.positions:
            if pos.status == "OPEN":
                price = current_prices.get(pos.ticker, pos.current_price) if current_prices else pos.current_price
                total += pos.quantity * price if pos.side == "LONG" else pos.quantity * (2 * pos.entry_price - price)
        return total

    def get_open_positions(self) -> List[Position]:
        """Return semua posisi yang masih open."""
        return [p for p in self.positions if p.status == "OPEN"]

    def get_position(self, ticker: str) -> Optional[Position]:
        """Get open position untuk ticker tertentu."""
        for p in self.positions:
            if p.ticker == ticker and p.status == "OPEN":
                return p
        return None

    def buy(
        self,
        ticker: str,
        quantity: int,
        price: float,
        stop_loss: float = 0.0,
        take_profit: float = 0.0,
        confidence: float = 0.0,
        signal: str = "",
        reason: str = "",
        use_slippage: bool = False,
    ) -> Dict:
        """Execute buy order (paper)."""
        # Apply slippage if enabled
        slippage_bps = 0
        slippage_cost = 0
        exec_price = price
        if use_slippage:
            try:
                from .slippage import calculate_total_execution_cost
                # Estimate ADV from price (fallback: assume liquid stock)
                adv = 1_000_000  # Default ADV assumption
                cost_est = calculate_total_execution_cost(
                    shares=quantity, price=price, adv=adv, volatility=0.25,
                    side="buy", commission_bps=15.0,
                )
                exec_price = price * (1 + cost_est.slippage_bps / 10000)
                slippage_bps = cost_est.slippage_bps
                slippage_cost = cost_est.total_cost
            except Exception:
                pass  # Fall back to no slippage

        cost = quantity * exec_price
        if cost > self.cash:
            return {"status": "REJECTED", "reason": f"Insufficient cash (need {cost:,.0f}, have {self.cash:,.0f})"}

        # Check if already holding this ticker
        existing = self.get_position(ticker)
        if existing:
            return {"status": "REJECTED", "reason": f"Already holding {ticker} ({existing.quantity} shares)"}

        self.cash -= cost
        # Auto-detect exchange and currency from ticker
        exchange = "IDX"
        currency = "IDR"
        try:
            from .market_hours import MarketHours
            mh = MarketHours()
            detected = mh.get_exchange_for_ticker(ticker)
            if detected:
                exchange = detected
                currency = mh.exchanges[detected].currency
        except Exception:
            pass

        pos = Position(
            ticker=ticker,
            side="LONG",
            quantity=quantity,
            entry_price=exec_price,
            entry_date=datetime.now().isoformat(),
            stop_loss=stop_loss,
            take_profit=take_profit,
            current_price=exec_price,
            confidence_at_entry=confidence,
            signal_at_entry=signal,
            hold_reason=reason or "ML signal BUY",
            exchange=exchange,
            currency=currency,
        )
        self.positions.append(pos)

        order_id = f"PT_BUY_{datetime.now().strftime('%Y%m%d%H%M%S')}"
        record = TradeRecord(
            timestamp=datetime.now().isoformat(),
            ticker=ticker,
            side="BUY",
            quantity=quantity,
            price=exec_price,
            status="FILLED",
            order_id=order_id,
            reason=reason,
            confidence=confidence,
            signal=signal,
        )
        self._log_trade(record)
        self._save()

        print(f"[PaperTrading] BUY {quantity} {ticker} @ {exec_price:,.2f} | Stop: {stop_loss:,.2f} | TP: {take_profit:,.2f}" + (f" | Slippage: {slippage_bps:.1f}bps" if slippage_bps else ""))

        # In-app notification
        send_in_app(
            kategori="PAPER_TRADE",
            judul=f"🟢 BUY {ticker} — {quantity} shares @ {exec_price:,.2f}",
            pesan=(
                f"Order: {order_id}\n"
                f"Side: BUY (LONG)\n"
                f"Quantity: {quantity}\n"
                f"Entry: {exec_price:,.2f}" + (f" (slippage: {slippage_bps:.1f}bps, cost: {slippage_cost:,.0f})" if slippage_bps else "") + "\n"
                f"Stop Loss: {stop_loss:,.2f}\n"
                f"Take Profit: {take_profit:,.2f}\n"
                f"Confidence: {confidence:.1%}\n"
                f"Signal: {signal}\n"
                f"Reason: {reason}\n"
                f"Cash remaining: {self.cash:,.0f}"
            ),
            level="success",
        )

        return {
            "status": "FILLED",
            "order_id": order_id,
            "ticker": ticker,
            "side": "BUY",
            "quantity": quantity,
            "price": exec_price,
            "slippage_bps": slippage_bps,
        }

    def sell(self, ticker: str, quantity: int, price: float, reason: str = "") -> Dict:
        """Execute sell order (paper) — close existing LONG position."""
        pos = self.get_position(ticker)
        if not pos:
            return {"status": "REJECTED", "reason": f"No open position for {ticker}"}

        if quantity > pos.quantity:
            quantity = pos.quantity  # Sell all if requested more

        proceeds = quantity * price
        self.cash += proceeds

        # Close position
        pos.close(price, reason or "CLOSED")

        # Update stats
        self.total_trades += 1
        self.total_realized_pnl += pos.realized_pnl
        self._reset_daily_pnl()
        self.daily_pnl += pos.realized_pnl
        if pos.realized_pnl > 0:
            self.winning_trades += 1
        else:
            self.losing_trades += 1

        order_id = f"PT_SELL_{datetime.now().strftime('%Y%m%d%H%M%S')}"
        record = TradeRecord(
            timestamp=datetime.now().isoformat(),
            ticker=ticker,
            side="SELL",
            quantity=quantity,
            price=price,
            status="FILLED",
            order_id=order_id,
            reason=reason,
            pnl=pos.realized_pnl,
        )
        self._log_trade(record)
        self._save()

        emoji = "✅" if pos.realized_pnl > 0 else "❌"
        print(f"[PaperTrading] SELL {quantity} {ticker} @ {price:,.2f} | PnL: {pos.realized_pnl:,.0f} ({pos.realized_pnl_pct:+.2f}%)")

        send_in_app(
            kategori="PAPER_TRADE",
            judul=f"{emoji} SELL {ticker} — PnL {pos.realized_pnl:,.0f} ({pos.realized_pnl_pct:+.2f}%)",
            pesan=(
                f"Order: {order_id}\n"
                f"Side: SELL (close LONG)\n"
                f"Quantity: {quantity}\n"
                f"Entry: {pos.entry_price:,.2f}\n"
                f"Exit: {price:,.2f}\n"
                f"Realized PnL: {pos.realized_pnl:,.0f}\n"
                f"Return: {pos.realized_pnl_pct:+.2f}%\n"
                f"Reason: {reason}\n"
                f"Cash: {self.cash:,.0f}\n"
                f"Total PnL: {self.total_realized_pnl:,.0f}\n"
                f"Win rate: {self.winning_trades}/{self.total_trades}"
            ),
            level="success" if pos.realized_pnl > 0 else "warning",
        )

        return {
            "status": "FILLED",
            "order_id": order_id,
            "ticker": ticker,
            "side": "SELL",
            "quantity": quantity,
            "price": price,
            "pnl": pos.realized_pnl,
            "pnl_pct": pos.realized_pnl_pct,
        }

    def hold(self, ticker: str, current_price: float, reason: str = "") -> Dict:
        """Update position dengan harga terbaru dan evaluate hold/stop/TP/partial exit."""
        pos = self.get_position(ticker)
        if not pos:
            return {"status": "NO_POSITION", "action": "HOLD"}

        pos.update_current_price(current_price)

        # Check stop loss (including trailing)
        if pos.check_stop_loss():
            reason = "TRAILING_STOP_LOSS" if pos.trailing_stop_enabled else "STOP_LOSS"
            return self.sell(ticker, pos.quantity, current_price, reason=reason)

        # Check partial exits (target 1 and target 2)
        partial_results = []
        if pos.target_1 > 0 and "TP1" not in pos.partial_exits_taken:
            if (pos.side == "LONG" and current_price >= pos.target_1) or \
               (pos.side == "SHORT" and current_price <= pos.target_1):
                sell_qty = int(pos.quantity * pos.partial_exit_pct_1 / 100)
                if sell_qty > 0:
                    result = self.sell(ticker, sell_qty, current_price, reason="PARTIAL_TP1")
                    pos.partial_exits_taken.append("TP1")
                    partial_results.append(result)

        if pos.target_2 > 0 and "TP2" not in pos.partial_exits_taken:
            if (pos.side == "LONG" and current_price >= pos.target_2) or \
               (pos.side == "SHORT" and current_price <= pos.target_2):
                sell_qty = int(pos.quantity * pos.partial_exit_pct_2 / 100)
                if sell_qty > 0:
                    result = self.sell(ticker, sell_qty, current_price, reason="PARTIAL_TP2")
                    pos.partial_exits_taken.append("TP2")
                    partial_results.append(result)

        # If partial exits taken, return partial result
        if partial_results:
            self._save()
            return {
                "status": "PARTIAL_FILLED",
                "ticker": ticker,
                "current_price": current_price,
                "partial_exits": partial_results,
                "remaining_quantity": pos.quantity,
                "unrealized_pnl": pos.unrealized_pnl,
                "stop_loss": pos.stop_loss,
                "take_profit": pos.take_profit,
            }

        # Check take profit (final target)
        if pos.check_take_profit():
            return self.sell(ticker, pos.quantity, current_price, reason="TAKE_PROFIT")

        # Hold — update reason
        trail_info = f" | Trail: {pos.stop_loss:,.0f}" if pos.trailing_stop_enabled else ""
        partial_info = f" | Exits: {','.join(pos.partial_exits_taken)}" if pos.partial_exits_taken else ""
        pos.hold_reason = reason or f"Holding {ticker} — SL: {pos.stop_loss:,.0f} | TP: {pos.take_profit:,.0f}{trail_info}{partial_info} | PnL: {pos.unrealized_pnl:,.0f}"
        self._save()

        return {
            "status": "HOLDING",
            "ticker": ticker,
            "current_price": current_price,
            "unrealized_pnl": pos.unrealized_pnl,
            "unrealized_pnl_pct": pos.unrealized_pnl_pct,
            "stop_loss": pos.stop_loss,
            "take_profit": pos.take_profit,
            "trailing_stop_enabled": pos.trailing_stop_enabled,
            "partial_exits_taken": pos.partial_exits_taken,
        }

    def check_all_positions(self, current_prices: Dict[str, float]) -> List[Dict]:
        """Check semua open positions untuk stop loss / take profit."""
        results = []
        for pos in self.get_open_positions():
            price = current_prices.get(pos.ticker)
            if price is None:
                continue
            result = self.hold(pos.ticker, price, reason="AUTO_CHECK")
            if result.get("status") == "FILLED":
                results.append(result)
        return results

    def get_stats(self) -> Dict:
        """Get portfolio statistics."""
        open_positions = self.get_open_positions()
        win_rate = (self.winning_trades / self.total_trades * 100) if self.total_trades > 0 else 0
        net_invested = self.total_deposited - self.total_withdrawn
        return {
            "initial_capital": self.initial_capital,
            "cash": self.cash,
            "total_deposited": self.total_deposited,
            "total_withdrawn": self.total_withdrawn,
            "net_invested": net_invested,
            "total_realized_pnl": self.total_realized_pnl,
            "total_realized_pnl_pct": (self.total_realized_pnl / net_invested * 100) if net_invested > 0 else 0,
            "daily_pnl": self.daily_pnl,
            "open_positions": len(open_positions),
            "total_trades": self.total_trades,
            "winning_trades": self.winning_trades,
            "losing_trades": self.losing_trades,
            "win_rate": round(win_rate, 1),
        }

    def deposit(self, amount: float, reason: str = "") -> Dict:
        """Tambah dana ke portfolio (top-up modal)."""
        if amount <= 0:
            return {"status": "REJECTED", "reason": "Amount must be positive"}
        self.cash += amount
        self.total_deposited += amount
        record = CashFlowRecord(
            timestamp=datetime.now().isoformat(),
            type="DEPOSIT",
            amount=amount,
            balance_after=self.cash,
            reason=reason or "Top-up modal",
        )
        self.cash_flow.append(asdict(record))
        self._save()

        print(f"[PaperTrading] DEPOSIT Rp {amount:,.0f} | Cash: {self.cash:,.0f}")
        send_in_app(
            kategori="FUND",
            judul=f"💰 Dana Masuk +Rp {amount:,.0f}",
            pesan=f"Type: DEPOSIT\nAmount: Rp {amount:,.0f}\nSaldo: Rp {self.cash:,.0f}\nTotal deposited: Rp {self.total_deposited:,.0f}\nReason: {reason}",
            level="success",
        )
        return {"status": "OK", "cash": self.cash, "total_deposited": self.total_deposited}

    def withdraw(self, amount: float, reason: str = "") -> Dict:
        """Tarik dana dari portfolio."""
        if amount <= 0:
            return {"status": "REJECTED", "reason": "Amount must be positive"}
        if amount > self.cash:
            return {"status": "REJECTED", "reason": f"Insufficient cash (have {self.cash:,.0f}, need {amount:,.0f})"}
        self.cash -= amount
        self.total_withdrawn += amount
        record = CashFlowRecord(
            timestamp=datetime.now().isoformat(),
            type="WITHDRAW",
            amount=amount,
            balance_after=self.cash,
            reason=reason or "Withdraw dana",
        )
        self.cash_flow.append(asdict(record))
        self._save()

        print(f"[PaperTrading] WITHDRAW Rp {amount:,.0f} | Cash: {self.cash:,.0f}")
        send_in_app(
            kategori="FUND",
            judul=f"💸 Dana Keluar -Rp {amount:,.0f}",
            pesan=f"Type: WITHDRAW\nAmount: Rp {amount:,.0f}\nSaldo: Rp {self.cash:,.0f}\nTotal withdrawn: Rp {self.total_withdrawn:,.0f}\nReason: {reason}",
            level="warning",
        )
        return {"status": "OK", "cash": self.cash, "total_withdrawn": self.total_withdrawn}

    def get_cash_flow(self) -> List[Dict]:
        """Return cash flow history (deposits & withdrawals)."""
        return self.cash_flow

    def get_allocation(self, current_prices: Dict[str, float] = None) -> Dict:
        """Get current allocation breakdown per ticker."""
        allocation = {}
        total_invested = 0.0
        for pos in self.get_open_positions():
            price = current_prices.get(pos.ticker, pos.current_price) if current_prices else pos.current_price
            value = pos.quantity * price
            allocation[pos.ticker] = {
                "value": value,
                "pct": 0,  # filled below
                "unrealized_pnl": pos.unrealized_pnl,
                "unrealized_pnl_pct": pos.unrealized_pnl_pct,
                "quantity": pos.quantity,
                "entry_price": pos.entry_price,
                "current_price": price,
            }
            total_invested += value

        total_value = self.cash + total_invested
        for ticker in allocation:
            allocation[ticker]["pct"] = (allocation[ticker]["value"] / total_value * 100) if total_value > 0 else 0

        return {
            "cash": {"value": self.cash, "pct": (self.cash / total_value * 100) if total_value > 0 else 0},
            "positions": allocation,
            "total_value": total_value,
            "total_invested": total_invested,
        }

    def reset(self, new_capital: float = None):
        """Reset portfolio. Jika new_capital diberikan, set modal baru."""
        if new_capital is not None and new_capital > 0:
            self.initial_capital = new_capital
        self.cash = self.initial_capital
        self.positions = []
        self.total_realized_pnl = 0.0
        self.total_trades = 0
        self.winning_trades = 0
        self.losing_trades = 0
        self.daily_pnl = 0.0
        self.total_deposited = self.initial_capital
        self.total_withdrawn = 0.0
        self.trade_history = []
        self.cash_flow = []
        self._save()
        send_in_app(
            kategori="PAPER_TRADE",
            judul="🔄 Paper Trading Reset",
            pesan=f"Portfolio direset ke Rp {self.initial_capital:,.0f}. Semua posisi dan history dihapus.",
            level="info",
        )
