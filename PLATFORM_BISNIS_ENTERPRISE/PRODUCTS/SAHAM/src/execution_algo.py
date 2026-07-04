"""
Execution Algorithms — VWAP and TWAP order simulation for day traders.

Features:
- VWAP (Volume Weighted Average Price) execution: split large orders
  across time weighted by historical volume profile
- TWAP (Time Weighted Average Price) execution: split evenly across time
- Order book proxy: estimate bid-ask spread from OHLCV
- Slippage estimation per slice

Usage:
    from src.execution_algo import VWAPExecution, TWAPExecution
    vwap = VWAPExecution()
    slices = vwap.plan(order_qty=10000, duration_minutes=60, interval="5m")
    results = vwap.execute(slices, price_feed)
"""

import pandas as pd
import numpy as np
from typing import Dict, List, Optional
from dataclasses import dataclass, field
from datetime import datetime, timedelta


@dataclass
class OrderSlice:
    """Single slice of a split order."""
    slice_number: int
    timestamp: str
    quantity: int
    weight: float  # % of total order
    estimated_price: float
    estimated_slippage: float
    status: str = "PENDING"  # PENDING, FILLED, PARTIAL, CANCELLED
    filled_price: float = 0.0
    filled_quantity: int = 0


@dataclass
class ExecutionResult:
    """Result of execution algorithm."""
    algorithm: str  # "VWAP" or "TWAP"
    total_quantity: int
    filled_quantity: int
    avg_fill_price: float
    total_slippage: float
    benchmark_price: float  # Arrival price
    implementation_shortfall: float  # Difference from benchmark
    slices: List[OrderSlice] = field(default_factory=list)
    execution_summary: str = ""


class VWAPExecution:
    """
    VWAP execution algorithm.

    Splits a large order into smaller slices distributed according to
    historical intraday volume profile (U-shaped: heavy at open/close).
    """

    # Default intraday volume profile (% of daily volume per 5-min bucket)
    # U-shaped: more volume at open and close
    DEFAULT_VOLUME_PROFILE_5M = None  # Will be computed dynamically

    def plan(
        self,
        order_qty: int,
        duration_minutes: int = 60,
        interval: str = "5m",
        volume_profile: Optional[pd.DataFrame] = None,
        side: str = "BUY",
    ) -> List[OrderSlice]:
        """
        Plan VWAP order execution.

        Args:
            order_qty: Total shares to execute
            duration_minutes: Total execution window
            interval: "5m" or "15m"
            volume_profile: Historical volume by time bucket. If None, use default U-shape.
            side: "BUY" or "SELL"

        Returns:
            List of OrderSlice objects
        """
        interval_minutes = 5 if interval == "5m" else 15
        num_slices = max(1, duration_minutes // interval_minutes)

        # Get volume weights
        if volume_profile is not None and not volume_profile.empty:
            weights = self._extract_volume_weights(volume_profile, num_slices)
        else:
            weights = self._default_volume_profile(num_slices)

        # Normalize weights
        total_weight = sum(weights)
        weights = [w / total_weight for w in weights]

        # Create slices
        slices = []
        remaining = order_qty
        now = datetime.now()

        for i in range(num_slices):
            if i == num_slices - 1:
                qty = remaining  # Last slice gets remainder
            else:
                qty = int(order_qty * weights[i])
                remaining -= qty

            slice_time = now + timedelta(minutes=i * interval_minutes)

            slices.append(OrderSlice(
                slice_number=i + 1,
                timestamp=slice_time.isoformat(),
                quantity=qty,
                weight=round(weights[i] * 100, 2),
                estimated_price=0,  # Will be filled during execution
                estimated_slippage=0,
            ))

        return slices

    def execute(
        self,
        slices: List[OrderSlice],
        price_data: pd.DataFrame,
        side: str = "BUY",
    ) -> ExecutionResult:
        """
        Simulate execution of VWAP slices against price data.

        Args:
            slices: Planned order slices
            price_data: OHLCV data with DatetimeIndex
            side: "BUY" or "SELL"

        Returns:
            ExecutionResult with fill details
        """
        if price_data.empty:
            return ExecutionResult(
                algorithm="VWAP", total_quantity=sum(s.quantity for s in slices),
                filled_quantity=0, avg_fill_price=0, total_slippage=0,
                benchmark_price=0, implementation_shortfall=0,
                execution_summary="No price data",
            )

        arrival_price = price_data["Close"].iloc[0]
        total_filled = 0
        total_value = 0
        total_slippage = 0

        for s in slices:
            # Find closest price bar
            slice_time = pd.to_datetime(s.timestamp)
            if isinstance(price_data.index, pd.DatetimeIndex):
                idx = price_data.index.get_indexer([slice_time], method="nearest")[0]
                if idx < 0 or idx >= len(price_data):
                    continue
                bar = price_data.iloc[idx]
            else:
                bar = price_data.iloc[min(s.slice_number - 1, len(price_data) - 1)]

            # Estimate fill price with slippage
            # Buy: fill near High (paying up), Sell: fill near Low (selling down)
            if side == "BUY":
                fill_price = bar["Close"] + (bar["High"] - bar["Low"]) * 0.1
            else:
                fill_price = bar["Close"] - (bar["High"] - bar["Low"]) * 0.1

            slippage = (fill_price - bar["Close"]) / bar["Close"] * 100

            s.filled_price = round(fill_price, 2)
            s.filled_quantity = s.quantity
            s.estimated_slippage = round(slippage, 4)
            s.status = "FILLED"

            total_filled += s.quantity
            total_value += fill_price * s.quantity
            total_slippage += abs(slippage) * s.quantity

        avg_fill = total_value / total_filled if total_filled > 0 else 0
        avg_slippage = total_slippage / total_filled if total_filled > 0 else 0
        shortfall = (avg_fill - arrival_price) / arrival_price * 100 if side == "BUY" else (arrival_price - avg_fill) / arrival_price * 100

        summary = (
            f"VWAP: {total_filled} shares filled, avg price {avg_fill:.2f}, "
            f"slippage {avg_slippage:.3f}%, shortfall {shortfall:.3f}%"
        )

        return ExecutionResult(
            algorithm="VWAP",
            total_quantity=sum(s.quantity for s in slices),
            filled_quantity=total_filled,
            avg_fill_price=round(avg_fill, 2),
            total_slippage=round(avg_slippage, 4),
            benchmark_price=round(arrival_price, 2),
            implementation_shortfall=round(shortfall, 4),
            slices=slices,
            execution_summary=summary,
        )

    def _default_volume_profile(self, num_slices: int) -> List[float]:
        """Generate U-shaped volume profile (heavy at open and close)."""
        if num_slices <= 1:
            return [1.0]
        # U-shape: high at start and end, lower in middle
        weights = []
        for i in range(num_slices):
            t = i / (num_slices - 1)  # 0 to 1
            # U-shape function
            w = 1.5 - 1.0 * (1 - 4 * (t - 0.5) ** 2)
            weights.append(max(0.3, w))
        return weights

    def _extract_volume_weights(self, vol_df: pd.DataFrame, num_slices: int) -> List[float]:
        """Extract volume weights from historical data."""
        if "Volume" not in vol_df.columns:
            return self._default_volume_profile(num_slices)

        # Take last N bars of volume
        recent_vol = vol_df["Volume"].tail(num_slices).values
        if len(recent_vol) < num_slices:
            # Pad with average
            avg = np.mean(recent_vol) if len(recent_vol) > 0 else 1
            recent_vol = np.concatenate([recent_vol, [avg] * (num_slices - len(recent_vol))])

        return [max(0.1, v) for v in recent_vol]


class TWAPExecution:
    """
    TWAP execution algorithm.

    Splits a large order into equal-sized slices distributed evenly
    across the execution window. Simpler than VWAP, minimizes market impact.
    """

    def plan(
        self,
        order_qty: int,
        duration_minutes: int = 60,
        interval: str = "5m",
        side: str = "BUY",
    ) -> List[OrderSlice]:
        """
        Plan TWAP order execution.

        Args:
            order_qty: Total shares to execute
            duration_minutes: Total execution window
            interval: "5m" or "15m"
            side: "BUY" or "SELL"

        Returns:
            List of OrderSlice objects
        """
        interval_minutes = 5 if interval == "5m" else 15
        num_slices = max(1, duration_minutes // interval_minutes)

        # Equal weights
        weight = 1.0 / num_slices
        base_qty = order_qty // num_slices
        remainder = order_qty % num_slices

        slices = []
        now = datetime.now()

        for i in range(num_slices):
            qty = base_qty + (1 if i < remainder else 0)
            slice_time = now + timedelta(minutes=i * interval_minutes)

            slices.append(OrderSlice(
                slice_number=i + 1,
                timestamp=slice_time.isoformat(),
                quantity=qty,
                weight=round(weight * 100, 2),
                estimated_price=0,
                estimated_slippage=0,
            ))

        return slices

    def execute(
        self,
        slices: List[OrderSlice],
        price_data: pd.DataFrame,
        side: str = "BUY",
    ) -> ExecutionResult:
        """Simulate execution of TWAP slices against price data."""
        if price_data.empty:
            return ExecutionResult(
                algorithm="TWAP", total_quantity=sum(s.quantity for s in slices),
                filled_quantity=0, avg_fill_price=0, total_slippage=0,
                benchmark_price=0, implementation_shortfall=0,
                execution_summary="No price data",
            )

        arrival_price = price_data["Close"].iloc[0]
        total_filled = 0
        total_value = 0
        total_slippage = 0

        for s in slices:
            if isinstance(price_data.index, pd.DatetimeIndex):
                slice_time = pd.to_datetime(s.timestamp)
                idx = price_data.index.get_indexer([slice_time], method="nearest")[0]
                if idx < 0 or idx >= len(price_data):
                    continue
                bar = price_data.iloc[idx]
            else:
                bar = price_data.iloc[min(s.slice_number - 1, len(price_data) - 1)]

            if side == "BUY":
                fill_price = bar["Close"] + (bar["High"] - bar["Low"]) * 0.1
            else:
                fill_price = bar["Close"] - (bar["High"] - bar["Low"]) * 0.1

            slippage = (fill_price - bar["Close"]) / bar["Close"] * 100

            s.filled_price = round(fill_price, 2)
            s.filled_quantity = s.quantity
            s.estimated_slippage = round(slippage, 4)
            s.status = "FILLED"

            total_filled += s.quantity
            total_value += fill_price * s.quantity
            total_slippage += abs(slippage) * s.quantity

        avg_fill = total_value / total_filled if total_filled > 0 else 0
        avg_slippage = total_slippage / total_filled if total_filled > 0 else 0
        shortfall = (avg_fill - arrival_price) / arrival_price * 100 if side == "BUY" else (arrival_price - avg_fill) / arrival_price * 100

        summary = (
            f"TWAP: {total_filled} shares filled, avg price {avg_fill:.2f}, "
            f"slippage {avg_slippage:.3f}%, shortfall {shortfall:.3f}%"
        )

        return ExecutionResult(
            algorithm="TWAP",
            total_quantity=sum(s.quantity for s in slices),
            filled_quantity=total_filled,
            avg_fill_price=round(avg_fill, 2),
            total_slippage=round(avg_slippage, 4),
            benchmark_price=round(arrival_price, 2),
            implementation_shortfall=round(shortfall, 4),
            slices=slices,
            execution_summary=summary,
        )


def estimate_spread(df: pd.DataFrame, lookback: int = 20) -> Dict:
    """
    Estimate bid-ask spread from OHLCV data.

    Uses the Corwin-Schultz (2012) estimator: spread from daily high-low.

    Args:
        df: OHLCV DataFrame
        lookback: Number of bars for estimation

    Returns:
        Dict with spread estimate
    """
    if df.empty or len(df) < lookback:
        return {"spread_estimate": 0, "spread_pct": 0, "method": "insufficient_data"}

    recent = df.tail(lookback)
    high = recent["High"].values
    low = recent["Low"].values

    # Corwin-Schultz spread estimator
    # beta = sum((ln(H/L))^2) / n
    # gamma = (ln(H_n/L_n))^2
    # alpha = (sqrt(2*beta) - sqrt(beta)) / (3 - 2*sqrt(2)) - sqrt(gamma/(3-2*sqrt(2)))
    # spread = 2*(exp(alpha) - 1) / (1 + exp(alpha))

    log_hl = np.log(high / low)
    beta = np.sum(log_hl ** 2) / lookback
    gamma = np.log(recent["High"].max() / recent["Low"].min()) ** 2

    denom = 3 - 2 * np.sqrt(2)
    if denom == 0:
        return {"spread_estimate": 0, "spread_pct": 0, "method": "calculation_error"}

    alpha = (np.sqrt(2 * beta) - np.sqrt(beta)) / denom - np.sqrt(gamma / denom)

    if np.isnan(alpha) or alpha < 0:
        # Fallback: simple high-low spread
        avg_hl = ((recent["High"] - recent["Low"]) / recent["Close"]).mean()
        return {
            "spread_estimate": round(float(avg_hl * recent["Close"].mean() * 0.5), 4),
            "spread_pct": round(float(avg_hl * 0.5 * 100), 4),
            "method": "hl_proxy",
        }

    spread = 2 * (np.exp(alpha) - 1) / (1 + np.exp(alpha))
    avg_price = recent["Close"].mean()

    return {
        "spread_estimate": round(float(spread * avg_price), 4),
        "spread_pct": round(float(spread * 100), 4),
        "method": "corwin_schultz",
    }
