"""
IDX Market Rules & Constraints (2025).

Implementasi aturan Bursa Efek Indonesia yang realistis:
- Auto-rejection limits (upper/lower) per board
- Circuit breaker / trading halt rules
- T+2 settlement simulation
- Holiday calendar (Indonesia)
- Short selling constraints
- Fractional share / lot size rules

Sumber:
- IDX Decree Kep-00196/BEI/12-2024 (effective Apr 8, 2025)
- IDX Decree Kep-00024/BEI/03-2020 (emergency trading halt)
- OJK Regulation on short selling
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from typing import Dict, Optional, List
from dataclasses import dataclass


# =============================================================================
# AUTO-REJECTION LIMITS (effective April 8, 2025)
# =============================================================================

AUTO_REJECTION_UPPER = {
    "main_board": 0.10,       # 10% upper limit
    "development_board": 0.10,
    "new_economy_board": 0.10,
    "etf": 0.10,
    "dire": 0.10,
}

AUTO_REJECTION_LOWER = {
    "main_board": 0.15,       # 15% lower limit (all boards)
    "development_board": 0.15,
    "new_economy_board": 0.15,
    "etf": 0.15,
    "dire": 0.15,
}

# Special: stocks under Rp 200 have different limits
AUTO_REJECTION_UPPER_SPECIAL = 0.20  # 20% for stocks < Rp 200
AUTO_REJECTION_LOWER_SPECIAL = 0.20


# =============================================================================
# CIRCUIT BREAKER / TRADING HALT (IHSG-based)
# =============================================================================

CIRCUIT_BREAKER_LEVELS = [
    {"ihsg_drop_pct": 5.0, "halt_minutes": 30, "description": "Phase 1: 30 min halt"},
    {"ihsg_drop_pct": 8.0, "halt_minutes": 30, "description": "Phase 2: 30 min halt"},
    {"ihsg_drop_pct": 15.0, "halt_minutes": 30, "description": "Phase 3: 30 min halt"},
    {"ihsg_drop_pct": 20.0, "halt_minutes": None, "description": "Suspend until next session"},
]


@dataclass
class AutoRejectionResult:
    """Auto-rejection check result."""
    is_rejected: bool
    direction: str  # "upper" or "lower" or "none"
    price_change_pct: float
    limit_pct: float
    reference_price: float
    rejected_price: float
    description: str


def check_auto_rejection(
    current_price: float,
    reference_price: float,
    board: str = "main_board",
    is_special_price: bool = False,
) -> AutoRejectionResult:
    """
    Check if price hits auto-rejection limit.

    Args:
        current_price: Current stock price
        reference_price: Previous close (reference for auto-rejection)
        board: "main_board", "development_board", "new_economy_board", "etf", "dire"
        is_special_price: True if stock price < Rp 200 (special limits)
    """
    if reference_price <= 0:
        return AutoRejectionResult(
            is_rejected=False, direction="none", price_change_pct=0,
            limit_pct=0, reference_price=reference_price,
            rejected_price=current_price, description="No reference price"
        )

    change_pct = ((current_price - reference_price) / reference_price) * 100

    if is_special_price:
        upper_limit = AUTO_REJECTION_UPPER_SPECIAL
        lower_limit = AUTO_REJECTION_LOWER_SPECIAL
    else:
        upper_limit = AUTO_REJECTION_UPPER.get(board, 0.10)
        lower_limit = AUTO_REJECTION_LOWER.get(board, 0.15)

    upper_price = reference_price * (1 + upper_limit)
    lower_price = reference_price * (1 - lower_limit)

    if current_price >= upper_price:
        return AutoRejectionResult(
            is_rejected=True, direction="upper",
            price_change_pct=round(change_pct, 2),
            limit_pct=upper_limit * 100,
            reference_price=reference_price,
            rejected_price=round(upper_price, 2),
            description=f"Auto-rejection UPPER: +{change_pct:.2f}% exceeds +{upper_limit*100:.0f}% limit"
        )
    elif current_price <= lower_price:
        return AutoRejectionResult(
            is_rejected=True, direction="lower",
            price_change_pct=round(change_pct, 2),
            limit_pct=lower_limit * 100,
            reference_price=reference_price,
            rejected_price=round(lower_price, 2),
            description=f"Auto-rejection LOWER: {change_pct:.2f}% exceeds -{lower_limit*100:.0f}% limit"
        )
    else:
        return AutoRejectionResult(
            is_rejected=False, direction="none",
            price_change_pct=round(change_pct, 2),
            limit_pct=0, reference_price=reference_price,
            rejected_price=current_price,
            description=f"Within limits: {change_pct:.2f}% (upper: +{upper_limit*100:.0f}%, lower: -{lower_limit*100:.0f}%)"
        )


@dataclass
class CircuitBreakerResult:
    """Circuit breaker / trading halt check result."""
    is_halted: bool
    halt_level: int  # 0 = no halt, 1-4 = halt level
    halt_minutes: Optional[int]
    ihsg_drop_pct: float
    description: str


def check_circuit_breaker(
    current_ihsg: float,
    previous_close_ihsg: float,
) -> CircuitBreakerResult:
    """
    Check if IHSG decline triggers circuit breaker / trading halt.

    IDX 2025 Rules:
    - IHSG drops 5% → 30 min halt
    - IHSG drops 8% → 30 min halt
    - IHSG drops 15% → 30 min halt
    - IHSG drops 20% → suspend until next session
    """
    if previous_close_ihsg <= 0:
        return CircuitBreakerResult(
            is_halted=False, halt_level=0, halt_minutes=None,
            ihsg_drop_pct=0, description="No reference"
        )

    drop_pct = ((previous_close_ihsg - current_ihsg) / previous_close_ihsg) * 100

    # Check from highest threshold to lowest (return the most severe)
    for i, level in enumerate(reversed(CIRCUIT_BREAKER_LEVELS), 1):
        if drop_pct >= level["ihsg_drop_pct"]:
            return CircuitBreakerResult(
                is_halted=True,
                halt_level=len(CIRCUIT_BREAKER_LEVELS) - i + 1,
                halt_minutes=level["halt_minutes"],
                ihsg_drop_pct=round(drop_pct, 2),
                description=f"Circuit Breaker Level {len(CIRCUIT_BREAKER_LEVELS) - i + 1}: IHSG -{drop_pct:.2f}% → {level['description']}"
            )

    return CircuitBreakerResult(
        is_halted=False, halt_level=0, halt_minutes=None,
        ihsg_drop_pct=round(drop_pct, 2),
        description=f"No halt: IHSG {drop_pct:.2f}% (within normal range)"
    )


# =============================================================================
# T+2 SETTLEMENT SIMULATION
# =============================================================================

@dataclass
class SettlementResult:
    """T+2 settlement simulation result."""
    trade_date: datetime
    settlement_date: datetime
    capital_frozen_days: int
    capital_available_date: datetime
    is_settled: bool


def calc_settlement_date(
    trade_date: datetime,
    days: int = 2,
    holidays: Optional[List[datetime]] = None,
) -> SettlementResult:
    """
    Calculate T+2 settlement date (skipping weekends and holidays).

    BEI/IDX settlement: T+2 business days.
    Capital is frozen from trade_date until settlement_date.
    """
    if holidays is None:
        holidays = get_idx_holidays(trade_date.year)

    current = trade_date
    business_days_added = 0

    while business_days_added < days:
        current += timedelta(days=1)
        # Skip weekends (Saturday=5, Sunday=6)
        if current.weekday() >= 5:
            continue
        # Skip holidays
        if any(
            current.date() == h.date() if isinstance(h, datetime) else current.date() == h
            for h in holidays
        ):
            continue
        business_days_added += 1

    return SettlementResult(
        trade_date=trade_date,
        settlement_date=current,
        capital_frozen_days=(current - trade_date).days,
        capital_available_date=current,
        is_settled=datetime.now() >= current,
    )


def get_idx_holidays(year: int) -> List[datetime]:
    """
    Indonesian national holidays affecting IDX trading.

    Note: This is a simplified list. For production, use a proper
    holiday calendar API or jpholiday library.
    """
    holidays = [
        # 2025 holidays
        datetime(2025, 1, 1),    # New Year
        datetime(2025, 1, 27),   # Isra Mi'raj
        datetime(2025, 1, 29),   # Chinese New Year
        datetime(2025, 3, 29),   # Nyepi (Day of Silence)
        datetime(2025, 3, 31),   # Eid al-Fitr (1)
        datetime(2025, 4, 1),    # Eid al-Fitr (2)
        datetime(2025, 4, 18),   # Good Friday
        datetime(2025, 5, 1),    # Labour Day
        datetime(2025, 5, 12),   # Ascension Day
        datetime(2025, 5, 29),   # Ascension of Prophet Muhammad
        datetime(2025, 6, 1),    # Pancasila Day
        datetime(2025, 6, 6),    # Eid al-Adha
        datetime(2025, 8, 17),   # Independence Day
        datetime(2025, 9, 5),    # Islamic New Year
        datetime(2025, 10, 2),   # Prophet Muhammad's Birthday
        datetime(2025, 12, 25),  # Christmas
        datetime(2025, 12, 26),  # Boxing Day (optional)
        # 2026 holidays (approximate)
        datetime(2026, 1, 1),    # New Year
        datetime(2026, 2, 17),   # Chinese New Year
        datetime(2026, 3, 19),   # Nyepi
        datetime(2026, 3, 20),   # Eid al-Fitr (approx)
        datetime(2026, 3, 21),   # Eid al-Fitr (approx)
        datetime(2026, 4, 3),    # Good Friday
        datetime(2026, 5, 1),    # Labour Day
        datetime(2026, 6, 1),    # Pancasila Day
        datetime(2026, 8, 17),   # Independence Day
        datetime(2026, 12, 25),  # Christmas
    ]

    return [h for h in holidays if h.year == year]


def is_trading_day(date: datetime, holidays: Optional[List[datetime]] = None) -> bool:
    """Check if a date is an IDX trading day."""
    if date.weekday() >= 5:  # Weekend
        return False
    if holidays is None:
        holidays = get_idx_holidays(date.year)
    for h in holidays:
        if date.date() == h.date():
            return False
    return True


# =============================================================================
# SHORT SELLING CONSTRAINTS (IDX 2025)
# =============================================================================

SHORT_SELLING_ENABLED = True  # Effective Sep 2025
SHORT_SELLING_MARGIN_REQ = 0.50  # 50% minimum margin
SHORT_SELLING_ELIGIBLE_STOCKS = True  # Only specific stocks eligible


# =============================================================================
# BACKTEST WITH IDX RULES
# =============================================================================

def backtest_with_idx_rules(
    df: pd.DataFrame,
    predictions: np.ndarray,
    close_col: str = "Close",
    return_col: str = "Target_Next_Return",
    initial_capital: float = 100_000_000,
    apply_auto_rejection: bool = True,
    apply_circuit_breaker: bool = True,
    apply_t2_settlement: bool = True,
) -> Dict:
    """
    Backtest with realistic IDX market rules:
    - Auto-rejection: trades at rejected prices are cancelled
    - Circuit breaker: no trading during halt periods
    - T+2 settlement: capital frozen for 2 business days after buy
    """
    from .quant_finance import (
        BEI_COMMISSION_BUY, BEI_COMMISSION_SELL,
        BEI_EXCHANGE_FEE, BEI_CLEARING_FEE, BEI_PPH_FINAL, BEI_SBK_FEE,
    )

    n = min(len(predictions), len(df))
    preds = predictions[:n]
    df_bt = df.iloc[:n].copy()

    if close_col not in df_bt.columns:
        return {"error": f"Column {close_col} not found"}
    if return_col not in df_bt.columns:
        df_bt[return_col] = df_bt[close_col].pct_change()

    position = 0
    cash = initial_capital
    settlement_date = None
    trades = []
    rejected_trades = 0
    halted_days = 0

    for i in range(n):
        pred = preds[i]
        price = df_bt[close_col].iloc[i]
        prev_close = df_bt[close_col].iloc[i - 1] if i > 0 else price
        df_bt[return_col].iloc[i]

        # Check circuit breaker
        if apply_circuit_breaker:
            cb = check_circuit_breaker(price, prev_close)
            if cb.is_halted:
                halted_days += 1
                continue  # No trading on halted days

        # Check auto-rejection
        ar = check_auto_rejection(price, prev_close)
        if apply_auto_rejection and ar.is_rejected:
            # Trade at rejected price is cancelled
            rejected_trades += 1
            continue

        # T+2 settlement: check if capital is available
        if apply_t2_settlement and settlement_date:
            current_date = df_bt.index[i] if hasattr(df_bt.index, '__getitem__') else datetime.now()
            if isinstance(current_date, str):
                current_date = pd.to_datetime(current_date)
            if current_date < settlement_date:
                # Capital still frozen — can't buy
                if pred == 1 and position == 0:
                    continue

        # Execute trades
        target = 1 if pred == 1 else 0
        if target != position:
            if target == 1:  # Buy
                trade_value = cash * 0.95
                shares = int(trade_value / price / 100) * 100  # Round to lot
                if shares > 0:
                    buy_cost_rate = (
                        BEI_COMMISSION_BUY + BEI_EXCHANGE_FEE +
                        BEI_CLEARING_FEE + BEI_SBK_FEE
                    )
                    cost = shares * price * buy_cost_rate
                    cash -= shares * price + cost
                    position = shares

                    # T+2 settlement
                    if apply_t2_settlement:
                        trade_date = df_bt.index[i] if hasattr(df_bt.index, '__getitem__') else datetime.now()
                        if isinstance(trade_date, str):
                            trade_date = pd.to_datetime(trade_date)
                        settlement = calc_settlement_date(trade_date)
                        settlement_date = settlement.settlement_date

                    trades.append({
                        "date": str(df_bt.index[i]),
                        "side": "buy",
                        "shares": shares,
                        "price": price,
                        "cost": cost,
                    })
            else:  # Sell
                if position > 0:
                    sell_cost_rate = (
                        BEI_COMMISSION_SELL + BEI_EXCHANGE_FEE +
                        BEI_CLEARING_FEE + BEI_PPH_FINAL + BEI_SBK_FEE
                    )
                    cost = position * price * sell_cost_rate
                    cash += position * price - cost
                    trades.append({
                        "date": str(df_bt.index[i]),
                        "side": "sell",
                        "shares": position,
                        "price": price,
                        "cost": cost,
                    })
                    position = 0
                    settlement_date = None

    final_value = cash + position * df_bt[close_col].iloc[-1]
    total_return = ((final_value - initial_capital) / initial_capital) * 100

    return {
        "total_return_pct": round(total_return, 2),
        "final_value": round(final_value, 0),
        "num_trades": len(trades),
        "rejected_trades": rejected_trades,
        "halted_days": halted_days,
        "auto_rejection_applied": apply_auto_rejection,
        "circuit_breaker_applied": apply_circuit_breaker,
        "t2_settlement_applied": apply_t2_settlement,
        "trades": trades[-10:],
    }
