"""
Volume-Weighted Slippage Model untuk Realistic Execution.

Implementasi slippage model berdasarkan:
- Square-root market impact (Almgren-Chriss)
- Volume participation rate
- Bid-ask spread estimation
- Temporary vs permanent impact

Referensi:
- Almgren & Chriss (2000) "Optimal Execution of Portfolio Transactions"
- Kissell (2013) "The Science of Algorithmic Trading"
- BEI/IDX microstructure characteristics
"""

import numpy as np
import pandas as pd
from typing import Dict
from dataclasses import dataclass


@dataclass
class ExecutionCost:
    """Full execution cost breakdown."""
    shares: int
    price: float
    market_impact: float  # in price points
    slippage_bps: float   # basis points
    total_cost: float     # in IDR
    cost_pct: float       # % of trade value
    execution_time_minutes: int
    method: str


def estimate_bid_ask_spread(
    price: float,
    volume: float,
    volatility: float = 0.02,
    is_liquid: bool = True,
) -> float:
    """
    Estimate bid-ask spread in basis points.

    Liquid stocks (BBCA, BBRI): ~5-15 bps
    Illiquid stocks: ~20-50 bps
    Index (IHSG): ~2-5 bps
    """
    if is_liquid:
        base_spread_bps = 5.0
    else:
        base_spread_bps = 25.0

    # Adjust for volatility
    vol_adjustment = (volatility / 0.02) ** 0.5
    spread_bps = base_spread_bps * vol_adjustment

    # Adjust for volume (higher volume = tighter spread)
    if volume > 1_000_000:
        spread_bps *= 0.8
    elif volume < 100_000:
        spread_bps *= 1.5

    return spread_bps


def calculate_market_impact(
    order_shares: int,
    adv: float,  # Average Daily Volume
    price: float,
    volatility: float = 0.02,
    participation_rate: float = 0.10,
) -> Dict:
    """
    Calculate market impact using square-root model.

    Impact = sigma * sqrt(order_size / ADV) * coefficient

    Based on Almgren et al. (2005) square-root law.
    """
    if adv <= 0 or order_shares <= 0:
        return {"impact_bps": 0, "impact_price": 0, "temporary": 0, "permanent": 0}

    # Order size as fraction of ADV
    order_frac = order_shares / adv

    # Square-root impact
    # Coefficient from empirical studies: ~0.5 for liquid markets
    impact_coeff = 0.5
    sigma = volatility

    # Total impact (basis points)
    total_impact_bps = impact_coeff * sigma * np.sqrt(order_frac) * 10000

    # Split: 60% temporary, 40% permanent
    temporary_bps = total_impact_bps * 0.6
    permanent_bps = total_impact_bps * 0.4

    # In price terms
    impact_price = price * total_impact_bps / 10000
    temporary_price = price * temporary_bps / 10000
    permanent_price = price * permanent_bps / 10000

    # Execution time estimate (based on participation rate)
    # If participating 10% of volume, need ~10% of trading day = ~24 min
    exec_minutes = int(order_frac / participation_rate * 240)  # 240 min trading day
    exec_minutes = max(1, min(exec_minutes, 240))

    return {
        "impact_bps": round(total_impact_bps, 2),
        "impact_price": round(impact_price, 4),
        "temporary_bps": round(temporary_bps, 2),
        "temporary_price": round(temporary_price, 4),
        "permanent_bps": round(permanent_bps, 2),
        "permanent_price": round(permanent_price, 4),
        "execution_time_minutes": exec_minutes,
        "participation_rate": participation_rate,
        "order_fraction_adv": round(order_frac, 4),
    }


def calculate_total_execution_cost(
    shares: int,
    price: float,
    adv: float,
    volatility: float = 0.02,
    side: str = "buy",
    commission_bps: float = 15.0,  # BEI: 15 bps buy, 25 bps sell
    is_liquid: bool = True,
) -> ExecutionCost:
    """
    Calculate total execution cost including:
    - Commission (BEI)
    - Bid-ask spread
    - Market impact (temporary + permanent)
    - Slippage
    """
    if side == "sell":
        commission_bps = 25.0

    # Spread
    spread_bps = estimate_bid_ask_spread(price, adv, volatility, is_liquid)
    half_spread_bps = spread_bps / 2

    # Market impact
    impact = calculate_market_impact(shares, adv, price, volatility)

    # Total slippage (half spread + impact)
    total_slippage_bps = half_spread_bps + impact["impact_bps"]

    # Total cost in bps
    total_cost_bps = commission_bps + total_slippage_bps

    # In IDR
    trade_value = shares * price
    total_cost_idr = trade_value * total_cost_bps / 10000

    # Effective execution price
    if side == "buy":
        price * (1 + total_cost_bps / 10000)
    else:
        price * (1 - total_cost_bps / 10000)

    return ExecutionCost(
        shares=shares,
        price=price,
        market_impact=impact["impact_price"],
        slippage_bps=round(total_slippage_bps, 2),
        total_cost=round(total_cost_idr, 0),
        cost_pct=round(total_cost_bps / 100, 4),
        execution_time_minutes=impact["execution_time_minutes"],
        method="Square-root (Almgren-Chriss) + BEI commission",
    )


def optimize_execution(
    shares: int,
    price: float,
    adv: float,
    volatility: float = 0.02,
    side: str = "buy",
    urgency: str = "normal",  # "low", "normal", "high"
) -> Dict:
    """
    Optimize execution strategy based on urgency.

    Returns recommended slicing strategy and cost estimates.
    """
    urgency_config = {
        "low": {"participation_rate": 0.05, "slices": 10, "time_horizon": "1-2 days"},
        "normal": {"participation_rate": 0.10, "slices": 5, "time_horizon": "same day"},
        "high": {"participation_rate": 0.20, "slices": 2, "time_horizon": "1-2 hours"},
    }

    config = urgency_config.get(urgency, urgency_config["normal"])

    # Calculate cost at chosen participation rate
    cost = calculate_total_execution_cost(
        shares, price, adv, volatility, side,
        is_liquid=adv > 500_000,
    )

    # Recalculate impact with chosen participation rate
    impact = calculate_market_impact(
        shares, adv, price, volatility,
        participation_rate=config["participation_rate"],
    )

    shares_per_slice = shares // config["slices"]
    remainder = shares % config["slices"]

    return {
        "urgency": urgency,
        "participation_rate": config["participation_rate"],
        "slices": config["slices"],
        "shares_per_slice": shares_per_slice,
        "remainder_shares": remainder,
        "time_horizon": config["time_horizon"],
        "total_cost": cost.total_cost,
        "total_cost_bps": round(cost.slippage_bps + (15 if side == "buy" else 25), 2),
        "market_impact_bps": impact["impact_bps"],
        "execution_time_minutes": impact["execution_time_minutes"],
        "recommendation": (
            f"Split into {config['slices']} orders of ~{shares_per_slice} shares, "
            f"participating at {config['participation_rate']:.0%} of volume. "
            f"Estimated cost: Rp {cost.total_cost:,.0f} ({cost.cost_pct:.2f}%)"
        ),
    }


def backtest_with_slippage(
    df: pd.DataFrame,
    predictions: np.ndarray,
    close_col: str = "Close",
    volume_col: str = "Volume",
    adv_window: int = 20,
    initial_capital: float = 100_000_000,
    commission_buy_bps: float = 15.0,
    commission_sell_bps: float = 25.0,
) -> Dict:
    """
    Backtest with realistic slippage model integrated.

    Uses volume-weighted slippage for each trade based on
    actual volume data and order size.
    """
    n = min(len(predictions), len(df))
    preds = predictions[:n]
    df_bt = df.iloc[:n].copy()

    if close_col not in df_bt.columns or volume_col not in df_bt.columns:
        return {"error": f"Columns {close_col} or {volume_col} not found"}

    # Calculate ADV
    df_bt["ADV"] = df_bt[volume_col].rolling(window=adv_window, min_periods=1).mean()

    position = 0
    cash = initial_capital
    trades = []

    for i in range(n):
        pred = preds[i]
        price = df_bt[close_col].iloc[i]
        adv = df_bt["ADV"].iloc[i]
        vol = df_bt[volume_col].iloc[i]

        # Determine target position
        target = 1 if pred == 1 else 0

        if target != position:
            # Trade needed
            trade_shares = int(cash * 0.95 / price) if target == 1 else position

            if trade_shares > 0:
                side = "buy" if target == 1 else "sell"
                cost = calculate_total_execution_cost(
                    trade_shares, price, max(adv, vol), side=side,
                    commission_bps=commission_buy_bps if side == "buy" else commission_sell_bps,
                )

                effective_price = price * (1 + cost.slippage_bps / 10000) if side == "buy" \
                    else price * (1 - cost.slippage_bps / 10000)

                if side == "buy":
                    cash -= trade_shares * effective_price
                    position = trade_shares
                else:
                    cash += trade_shares * effective_price
                    position = 0

                trades.append({
                    "date": df_bt.index[i],
                    "side": side,
                    "shares": trade_shares,
                    "price": price,
                    "effective_price": round(effective_price, 2),
                    "slippage_bps": cost.slippage_bps,
                    "cost_idr": cost.total_cost,
                })

    final_value = cash + position * df_bt[close_col].iloc[-1]
    total_return = ((final_value - initial_capital) / initial_capital) * 100

    total_costs = sum(t["cost_idr"] for t in trades)
    total_slippage_bps = np.mean([t["slippage_bps"] for t in trades]) if trades else 0

    return {
        "total_return_pct": round(total_return, 2),
        "final_value": round(final_value, 0),
        "num_trades": len(trades),
        "total_costs_idr": round(total_costs, 0),
        "avg_slippage_bps": round(total_slippage_bps, 2),
        "cost_as_pct_of_capital": round(total_costs / initial_capital * 100, 4),
        "trades": trades[-10:],  # Last 10 trades
    }
