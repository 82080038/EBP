"""
Broker API Simulation — realistic order execution simulation.

Simulates broker API interactions for measuring the application's
end-to-end trading capability without risking real capital.

Simulates:
- Order submission, acknowledgment, partial fills, rejection
- Latency modeling (network + broker processing)
- Slippage based on order size vs. average volume
- Order types: market, limit, stop-loss
- Portfolio tracking with BEI lot sizes (100 shares/lot)
- Commission & fees calculation

Supported brokers (simulated):
- BCA Sekuritas
- IndoPremier
- Mirae Asset
- Mandiri Sekuritas

Usage:
    from src.broker_sim import BrokerSimulator, SimOrder
    broker = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
    order = SimOrder(symbol="BBCA.JK", side="BUY", quantity=100, order_type="MARKET")
    result = broker.submit_order(order, current_price=8500)
    portfolio = broker.get_portfolio()
"""
from __future__ import annotations

import logging
import random
from dataclasses import dataclass, field
from datetime import datetime
from enum import Enum
from typing import Dict, List


from .quant_finance import calc_bei_total_cost

logger = logging.getLogger(__name__)


class OrderType(Enum):
    MARKET = "MARKET"
    LIMIT = "LIMIT"
    STOP_LOSS = "STOP_LOSS"
    STOP_LIMIT = "STOP_LIMIT"


class OrderStatus(Enum):
    PENDING = "PENDING"
    ACKNOWLEDGED = "ACKNOWLEDGED"
    PARTIAL_FILL = "PARTIAL_FILL"
    FILLED = "FILLED"
    REJECTED = "REJECTED"
    CANCELLED = "CANCELLED"


@dataclass
class SimOrder:
    """Order to submit to broker simulator."""
    symbol: str = ""
    side: str = "BUY"  # BUY or SELL
    quantity: int = 0
    order_type: str = "MARKET"
    limit_price: float = 0.0
    stop_price: float = 0.0
    time_in_force: str = "DAY"  # DAY, GTC, IOC, FOK


@dataclass
class FillResult:
    """Result of order execution."""
    order_id: str = ""
    status: str = "PENDING"
    symbol: str = ""
    side: str = ""
    requested_qty: int = 0
    filled_qty: int = 0
    avg_fill_price: float = 0.0
    commission: float = 0.0
    fees: float = 0.0
    total_cost: float = 0.0
    slippage_bps: float = 0.0
    latency_ms: float = 0.0
    timestamp: str = ""
    rejection_reason: str = ""
    fills: List[dict] = field(default_factory=list)


@dataclass
class SimPosition:
    """Simulated portfolio position."""
    symbol: str = ""
    quantity: int = 0
    avg_price: float = 0.0
    market_price: float = 0.0
    unrealized_pnl: float = 0.0
    realized_pnl: float = 0.0


# Broker configurations
BROKER_CONFIGS = {
    "bca_sekuritas": {
        "commission_pct": 0.15,
        "min_commission": 10_000,
        "latency_ms_range": (50, 200),
        "partial_fill_prob": 0.05,
        "reject_prob": 0.01,
        "slippage_bps_range": (1, 8),
    },
    "indopremier": {
        "commission_pct": 0.15,
        "min_commission": 10_000,
        "latency_ms_range": (30, 150),
        "partial_fill_prob": 0.03,
        "reject_prob": 0.005,
        "slippage_bps_range": (1, 6),
    },
    "mirae_asset": {
        "commission_pct": 0.15,
        "min_commission": 10_000,
        "latency_ms_range": (40, 180),
        "partial_fill_prob": 0.04,
        "reject_prob": 0.008,
        "slippage_bps_range": (1, 7),
    },
    "mandiri_sekuritas": {
        "commission_pct": 0.15,
        "min_commission": 10_000,
        "latency_ms_range": (60, 250),
        "partial_fill_prob": 0.06,
        "reject_prob": 0.012,
        "slippage_bps_range": (2, 10),
    },
}


class BrokerSimulator:
    """
    Simulated broker for paper trading and strategy testing.

    Simulates realistic order execution with latency, slippage,
    partial fills, and rejection — without risking real capital.
    """

    def __init__(
        self,
        broker: str = "bca_sekuritas",
        capital: float = 100_000_000,
        lot_size: int = 100,
    ):
        self.broker_name = broker
        self.config = BROKER_CONFIGS.get(broker, BROKER_CONFIGS["bca_sekuritas"])
        self.capital = capital
        self.cash = capital
        self.lot_size = lot_size
        self.positions: Dict[str, SimPosition] = {}
        self.short_positions: Dict[str, SimPosition] = {}
        self.total_realized_pnl: float = 0.0
        self.order_history: List[FillResult] = []
        self._order_counter = 0
        self._latency_jitter = random.Random(42)

    def _gen_order_id(self) -> str:
        self._order_counter += 1
        return f"SIM-{datetime.now().strftime('%Y%m%d')}-{self._order_counter:06d}"

    def _simulate_latency(self) -> float:
        low, high = self.config["latency_ms_range"]
        return self._latency_jitter.uniform(low, high)

    def _simulate_slippage(self, price: float, quantity: int, avg_volume: int = 0) -> float:
        """Calculate slippage in basis points based on order impact."""
        low, high = self.config["slippage_bps_range"]
        base_slippage = self._latency_jitter.uniform(low, high)

        if avg_volume > 0:
            impact = min(quantity / avg_volume, 0.1)
            base_slippage += impact * 20  # Add impact slippage

        return base_slippage

    def submit_order(
        self,
        order: SimOrder,
        current_price: float,
        avg_volume: int = 0,
    ) -> FillResult:
        """
        Submit order to simulated broker.

        Args:
            order: SimOrder with order details
            current_price: Current market price
            avg_volume: Average daily volume (for slippage calc)

        Returns:
            FillResult with execution details
        """
        result = FillResult(
            order_id=self._gen_order_id(),
            symbol=order.symbol,
            side=order.side,
            requested_qty=order.quantity,
            timestamp=datetime.now().isoformat(),
        )

        # Simulate network latency
        latency = self._simulate_latency()
        result.latency_ms = round(latency, 1)

        # Check for rejection
        if self._latency_jitter.random() < self.config["reject_prob"]:
            result.status = OrderStatus.REJECTED.value
            result.rejection_reason = "Broker rejected: insufficient liquidity or system error"
            self.order_history.append(result)
            logger.warning(f"Order rejected: {result.rejection_reason}")
            return result

        # Check lot size
        if order.quantity % self.lot_size != 0:
            result.status = OrderStatus.REJECTED.value
            result.rejection_reason = f"Quantity must be multiple of {self.lot_size} (lot size)"
            self.order_history.append(result)
            return result

        # Determine execution price
        exec_price = current_price
        if order.order_type == "LIMIT" and order.limit_price > 0:
            if order.side == "BUY" and current_price > order.limit_price:
                result.status = OrderStatus.PENDING.value
                result.rejection_reason = "Limit order not filled — price above limit"
                self.order_history.append(result)
                return result
            exec_price = order.limit_price
        elif order.order_type == "STOP_LOSS" and order.stop_price > 0:
            if order.side == "SELL" and current_price > order.stop_price:
                result.status = OrderStatus.PENDING.value
                self.order_history.append(result)
                return result
            exec_price = order.stop_price

        # Apply slippage
        slippage_bps = self._simulate_slippage(exec_price, order.quantity, avg_volume)
        slippage_amount = exec_price * slippage_bps / 10000

        if order.side == "BUY":
            fill_price = exec_price + slippage_amount
        else:
            fill_price = exec_price - slippage_amount

        result.slippage_bps = round(slippage_bps, 2)

        # Check for partial fill
        filled_qty = order.quantity
        if self._latency_jitter.random() < self.config["partial_fill_prob"]:
            fill_ratio = self._latency_jitter.uniform(0.5, 0.9)
            filled_qty = int(order.quantity * fill_ratio / self.lot_size) * self.lot_size
            if filled_qty == 0:
                filled_qty = self.lot_size
            result.status = OrderStatus.PARTIAL_FILL.value
        else:
            result.status = OrderStatus.FILLED.value

        result.filled_qty = filled_qty
        result.avg_fill_price = round(fill_price, 2)

        # Calculate costs
        trade_value = filled_qty * fill_price
        cost_detail = calc_bei_total_cost(trade_value, order.side.lower())
        result.commission = round(cost_detail.get("commission", 0), 0)
        result.fees = round(cost_detail.get("bebi_fee", 0) + cost_detail.get("idx_fee", 0) + cost_detail.get("vat", 0), 0)
        result.total_cost = round(result.commission + result.fees, 0)

        # Update portfolio
        if order.side == "BUY":
            # Check if this is a short cover
            if order.symbol in self.short_positions and self.short_positions[order.symbol].quantity > 0:
                pos = self.short_positions[order.symbol]
                cover_qty = min(filled_qty, pos.quantity)
                realized = (pos.avg_price - fill_price) * cover_qty - result.total_cost
                pos.realized_pnl += realized
                self.total_realized_pnl += realized
                pos.quantity -= cover_qty
                self.cash -= trade_value + result.total_cost
                if pos.quantity == 0:
                    del self.short_positions[order.symbol]
                # Record remaining buy as long if any
                if filled_qty > cover_qty:
                    long_qty = filled_qty - cover_qty
                    self.positions[order.symbol] = SimPosition(
                        symbol=order.symbol,
                        quantity=long_qty,
                        avg_price=fill_price,
                        market_price=fill_price,
                    )
            else:
                # Long purchase
                self.cash -= trade_value + result.total_cost
                if order.symbol in self.positions:
                    pos = self.positions[order.symbol]
                    total_qty = pos.quantity + filled_qty
                    pos.avg_price = (pos.avg_price * pos.quantity + fill_price * filled_qty) / total_qty
                    pos.quantity = total_qty
                else:
                    self.positions[order.symbol] = SimPosition(
                        symbol=order.symbol,
                        quantity=filled_qty,
                        avg_price=fill_price,
                        market_price=fill_price,
                    )
        else:  # SELL
            # Check if we have long position
            if order.symbol in self.positions and self.positions[order.symbol].quantity > 0:
                pos = self.positions[order.symbol]
                sell_qty = min(filled_qty, pos.quantity)
                realized = (fill_price - pos.avg_price) * sell_qty - result.total_cost
                pos.realized_pnl += realized
                self.total_realized_pnl += realized
                pos.quantity -= sell_qty
                self.cash += trade_value - result.total_cost
                if pos.quantity == 0:
                    del self.positions[order.symbol]
                # Excess sell becomes short
                if filled_qty > sell_qty:
                    short_qty = filled_qty - sell_qty
                    if order.symbol in self.short_positions:
                        spos = self.short_positions[order.symbol]
                        total_short = spos.quantity + short_qty
                        spos.avg_price = (spos.avg_price * spos.quantity + fill_price * short_qty) / total_short
                        spos.quantity = total_short
                    else:
                        self.short_positions[order.symbol] = SimPosition(
                            symbol=order.symbol,
                            quantity=short_qty,
                            avg_price=fill_price,
                            market_price=fill_price,
                        )
                    self.cash += short_qty * fill_price - result.total_cost
            else:
                # Opening or adding short position
                if order.symbol in self.short_positions:
                    spos = self.short_positions[order.symbol]
                    total_short = spos.quantity + filled_qty
                    spos.avg_price = (spos.avg_price * spos.quantity + fill_price * filled_qty) / total_short
                    spos.quantity = total_short
                else:
                    self.short_positions[order.symbol] = SimPosition(
                        symbol=order.symbol,
                        quantity=filled_qty,
                        avg_price=fill_price,
                        market_price=fill_price,
                    )
                self.cash += trade_value - result.total_cost

        # Record fill
        result.fills.append({
            "timestamp": result.timestamp,
            "qty": filled_qty,
            "price": fill_price,
            "slippage_bps": slippage_bps,
        })

        self.order_history.append(result)
        logger.info(f"Order {result.order_id}: {result.status} {filled_qty} {order.symbol} @ {fill_price:.2f}")
        return result

    def update_market_prices(self, prices: Dict[str, float]):
        """Update market prices for all long and short positions."""
        for symbol, price in prices.items():
            if symbol in self.positions:
                pos = self.positions[symbol]
                pos.market_price = price
                pos.unrealized_pnl = (price - pos.avg_price) * pos.quantity
            if symbol in self.short_positions:
                spos = self.short_positions[symbol]
                spos.market_price = price
                # Short profit = (entry_price - current_price) * quantity
                spos.unrealized_pnl = (spos.avg_price - price) * spos.quantity

    def get_portfolio(self) -> Dict:
        """Get current portfolio summary including short positions."""
        long_value = sum(p.market_price * p.quantity for p in self.positions.values())
        short_value = sum(-spos.market_price * spos.quantity for spos in self.short_positions.values())
        total_position_value = long_value + short_value
        total_unrealized = sum(p.unrealized_pnl for p in self.positions.values()) + sum(
            spos.unrealized_pnl for spos in self.short_positions.values()
        )
        total_realized = self.total_realized_pnl
        total_pnl = total_unrealized + total_realized

        return {
            "broker": self.broker_name,
            "cash": round(self.cash, 0),
            "total_capital": round(self.cash + total_position_value, 0),
            "position_value": round(total_position_value, 0),
            "unrealized_pnl": round(total_unrealized, 0),
            "realized_pnl": round(total_realized, 0),
            "total_pnl": round(total_pnl, 0),
            "total_return_pct": round(total_pnl / self.capital * 100, 2),
            "n_positions": len(self.positions) + len(self.short_positions),
            "positions": [
                {
                    "symbol": p.symbol,
                    "quantity": p.quantity,
                    "avg_price": p.avg_price,
                    "market_price": p.market_price,
                    "unrealized_pnl": round(p.unrealized_pnl, 0),
                    "realized_pnl": round(p.realized_pnl, 0),
                    "side": "long",
                }
                for p in self.positions.values()
            ] + [
                {
                    "symbol": spos.symbol,
                    "quantity": -spos.quantity,
                    "avg_price": spos.avg_price,
                    "market_price": spos.market_price,
                    "unrealized_pnl": round(spos.unrealized_pnl, 0),
                    "realized_pnl": round(spos.realized_pnl, 0),
                    "side": "short",
                }
                for spos in self.short_positions.values()
            ],
            "n_orders": len(self.order_history),
            "n_fills": sum(1 for o in self.order_history if o.status in ("FILLED", "PARTIAL_FILL")),
            "n_rejections": sum(1 for o in self.order_history if o.status == "REJECTED"),
        }

    def get_order_history(self) -> List[Dict]:
        """Get order history as list of dicts."""
        return [
            {
                "order_id": o.order_id,
                "timestamp": o.timestamp,
                "symbol": o.symbol,
                "side": o.side,
                "status": o.status,
                "requested_qty": o.requested_qty,
                "filled_qty": o.filled_qty,
                "avg_fill_price": o.avg_fill_price,
                "commission": o.commission,
                "fees": o.fees,
                "slippage_bps": o.slippage_bps,
                "latency_ms": o.latency_ms,
                "rejection_reason": o.rejection_reason,
            }
            for o in self.order_history
        ]
