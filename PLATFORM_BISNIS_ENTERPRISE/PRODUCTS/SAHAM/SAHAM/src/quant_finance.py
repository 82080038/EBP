"""
Quantitative Finance: Entry/Target/Stop, Realistic Backtesting, Portfolio Analytics.

Implementasi:
- Entry/Target/Stop calculation per sinyal (dari BOZ/Trade Ideas/Hanzo)
- Realistic backtesting: commission BEI (0.15% beli, 0.25% jual), slippage, T+2
- Vectorized backtesting (pandas-based, 75x faster) (dari QuantRocket/Moonshot)
- Pyfolio-style portfolio tear sheet (dari QuantRocket)
- Risk/Reward ratio per sinyal
- Wyckoff phase detection (dari Hanzo AI)

Referensi:
- BOZ: Entry/Target/Stop output
- Trade Ideas: Risk management levels per sinyal
- QuantRocket: Vectorized backtesting, Pyfolio
- Hanzo AI: Wyckoff phase detection
"""

import numpy as np
import pandas as pd
from typing import Dict, List, Optional
from dataclasses import dataclass, field


# =============================================================================
# BEI/IDX TRADING CONSTRAINTS
# =============================================================================

BEI_COMMISSION_BUY = 0.0015   # 0.15% beli
BEI_COMMISSION_SELL = 0.0025  # 0.25% jual
BEI_SETTLEMENT_DAYS = 2       # T+2
BEI_MIN_LOT = 100             # 100 shares per lot

# ===========================================================================
# STRUKTUR BIAYA LENGKAP BEI/IDX (2025)
# ===========================================================================
# Sumber: BEI rulebook, KSEI, KPEI, UU PPh Final saham
# ===========================================================================

BEI_EXCHANGE_FEE = 0.00004     # 0.004% — Bea Bursa Efek Indonesia
BEI_CLEARING_FEE = 0.00002     # 0.002% — Bea Penyelesaian KSEI/KPEI
BEI_PPH_FINAL = 0.001          # 0.1% — PPh Final atas penjualan saham (JUAL ONLY)
BEI_SBK_FEE = 0.00005          # 0.005% — Surat Berharga Kliring (> 500 juta)
BEI_CUSTODY_FEE_ANNUAL = 100_000  # Rp 100.000/tahun — biaya kustodi R-D


def calc_bei_total_cost(
    trade_value: float,
    side: str = "buy",
    include_pph: bool = True,
    include_exchange: bool = True,
    include_clearing: bool = True,
    include_sbk: bool = True,
) -> dict:
    """
    Hitung TOTAL biaya transaksi BEI/IDX yang realistis.

    Args:
        trade_value: Nilai transaksi dalam Rupiah
        side: "buy" atau "sell"
        include_pph: Include PPh Final 0.1% (hanya jual)
        include_exchange: Include Bea Bursa 0.004%
        include_clearing: Include Bea KSEI 0.002%
        include_sbk: Include SBK 0.005% (jika > 500 juta)

    Returns:
        Dict dengan breakdown biaya lengkap
    """
    commission_rate = BEI_COMMISSION_BUY if side == "buy" else BEI_COMMISSION_SELL
    commission = trade_value * commission_rate

    exchange_fee = trade_value * BEI_EXCHANGE_FEE if include_exchange else 0
    clearing_fee = trade_value * BEI_CLEARING_FEE if include_clearing else 0
    sbk_fee = trade_value * BEI_SBK_FEE if (include_sbk and trade_value > 500_000_000) else 0

    # PPh Final hanya saat JUAL
    pph = trade_value * BEI_PPH_FINAL if (include_pph and side == "sell") else 0

    total = commission + exchange_fee + clearing_fee + sbk_fee + pph
    total_bps = (total / trade_value) * 10000 if trade_value > 0 else 0

    return {
        "side": side,
        "trade_value": round(trade_value, 0),
        "commission": round(commission, 0),
        "exchange_fee": round(exchange_fee, 0),
        "clearing_fee": round(clearing_fee, 0),
        "sbk_fee": round(sbk_fee, 0),
        "pph_final": round(pph, 0),
        "total_cost": round(total, 0),
        "total_cost_bps": round(total_bps, 2),
        "total_cost_pct": round(total / trade_value * 100, 4) if trade_value > 0 else 0,
    }


def calc_round_trip_cost(trade_value: float) -> dict:
    """
    Hitung biaya round-trip (beli + jual) lengkap BEI/IDX.

    Ini adalah biaya TOTAL untuk membuka dan menutup posisi.
    """
    buy = calc_bei_total_cost(trade_value, "buy")
    sell = calc_bei_total_cost(trade_value, "sell")

    total = buy["total_cost"] + sell["total_cost"]
    total_bps = buy["total_cost_bps"] + sell["total_cost_bps"]

    # Break-even: return needed just to cover costs
    break_even_pct = total / trade_value * 100 if trade_value > 0 else 0

    return {
        "buy_cost": buy,
        "sell_cost": sell,
        "round_trip_total": round(total, 0),
        "round_trip_bps": round(total_bps, 2),
        "round_trip_pct": round(break_even_pct, 4),
        "break_even_return_pct": round(break_even_pct, 4),
        "break_even_price_move": f"Price must move +{break_even_pct:.3f}% just to break even",
    }


# =============================================================================
# ENTRY / TARGET / STOP CALCULATION
# =============================================================================

@dataclass
class TradeLevels:
    """Level harga actionable per sinyal."""
    signal: str  # BUY / SELL / HOLD
    entry: float
    entry_range_low: float
    entry_range_high: float
    target_1: float
    target_2: float
    target_3: float
    stop_loss: float
    risk_reward_ratio: float
    position_size_pct: float  # % of capital
    position_size_shares: int
    position_value: float
    risk_amount: float
    description: str


def calc_entry_target_stop(
    signal: str,
    current_price: float,
    atr: float = 0.0,
    support: float = 0.0,
    resistance: float = 0.0,
    capital: float = 100_000_000,
    risk_per_trade: float = 0.02,
    confidence: float = 0.5,
    rsi: float = 50.0,
) -> TradeLevels:
    """
    Hitung Entry, Target, dan Stop Loss per sinyal.

    Method:
    - Entry: current price ± buffer (ATR-based)
    - Stop Loss: entry - 2×ATR (for BUY) or entry + 2×ATR (for SELL)
    - Target 1: 1×R (risk/reward 1:1)
    - Target 2: 2×R (risk/reward 1:2)
    - Target 3: 3×R (risk/reward 1:3)
    - Position sizing: risk-based (capital × risk% / risk per share)

    Referensi: BOZ, Trade Ideas, Hanzo AI
    """
    if atr <= 0:
        atr = current_price * 0.015  # Default 1.5% ATR

    if signal == "BUY":
        entry = current_price
        entry_low = current_price - atr * 0.3
        entry_high = current_price + atr * 0.3
        stop_loss = entry - 2 * atr
        risk_per_share = entry - stop_loss
        target_1 = entry + risk_per_share * 1.0
        target_2 = entry + risk_per_share * 2.0
        target_3 = entry + risk_per_share * 3.0

        # Adjust with support/resistance if available
        if support > 0 and stop_loss < support:
            stop_loss = support - atr * 0.5
            risk_per_share = entry - stop_loss
            target_1 = entry + risk_per_share * 1.0
            target_2 = entry + risk_per_share * 2.0
            target_3 = entry + risk_per_share * 3.0
        if resistance > 0 and target_2 < resistance:
            target_2 = resistance
            target_3 = resistance + risk_per_share * 1.0

    elif signal == "SELL":
        entry = current_price
        entry_low = current_price - atr * 0.3
        entry_high = current_price + atr * 0.3
        stop_loss = entry + 2 * atr
        risk_per_share = stop_loss - entry
        target_1 = entry - risk_per_share * 1.0
        target_2 = entry - risk_per_share * 2.0
        target_3 = entry - risk_per_share * 3.0

        if resistance > 0 and stop_loss > resistance:
            stop_loss = resistance + atr * 0.5
            risk_per_share = stop_loss - entry
            target_1 = entry - risk_per_share * 1.0
            target_2 = entry - risk_per_share * 2.0
            target_3 = entry - risk_per_share * 3.0
        if support > 0 and target_2 > support:
            target_2 = support
            target_3 = support - risk_per_share * 1.0

    else:  # HOLD
        entry = current_price
        entry_low = current_price - atr * 0.5
        entry_high = current_price + atr * 0.5
        stop_loss = current_price - 2 * atr  # Reference stop if turning bullish
        risk_per_share = 2 * atr
        target_1 = current_price + risk_per_share * 1.0
        target_2 = current_price + risk_per_share * 2.0
        target_3 = current_price + risk_per_share * 3.0
        # For HOLD, use support/resistance as reference
        if support > 0:
            stop_loss = support - atr * 0.5
            risk_per_share = entry - stop_loss
        if resistance > 0:
            target_1 = resistance
            target_2 = resistance + risk_per_share * 1.0
            target_3 = resistance + risk_per_share * 2.0

    # Position sizing: risk-based
    risk_amount = capital * risk_per_trade * confidence
    if risk_per_share > 0:
        shares = int(risk_amount / risk_per_share / BEI_MIN_LOT) * BEI_MIN_LOT
        position_value = shares * entry
        position_pct = (position_value / capital) * 100
    else:
        shares = 0
        position_value = 0
        position_pct = 0

    rr_ratio = risk_per_share / risk_per_share if risk_per_share > 0 else 0
    rr_ratio = 2.0  # Target 1:2 R/R

    desc = (
        f"Sinyal {signal}: Entry {entry:,.2f} | "
        f"Stop {stop_loss:,.2f} | "
        f"Target1 {target_1:,.2f} | "
        f"Target2 {target_2:,.2f} | "
        f"R/R 1:{rr_ratio:.1f} | "
        f"Size {shares} shares ({position_pct:.1f}%)"
    )

    return TradeLevels(
        signal=signal,
        entry=round(entry, 2),
        entry_range_low=round(entry_low, 2),
        entry_range_high=round(entry_high, 2),
        target_1=round(target_1, 2),
        target_2=round(target_2, 2),
        target_3=round(target_3, 2),
        stop_loss=round(stop_loss, 2),
        risk_reward_ratio=rr_ratio,
        position_size_pct=round(position_pct, 1),
        position_size_shares=shares,
        position_value=round(position_value, 0),
        risk_amount=round(risk_amount, 0),
        description=desc,
    )


# =============================================================================
# REALISTIC BACKTESTING (with BEI commission, slippage, T+2)
# =============================================================================

@dataclass
class RealisticBacktestResult:
    initial_capital: float
    final_capital: float
    total_return_pct: float
    buy_hold_return_pct: float
    n_trades: int
    n_winning_trades: int
    n_losing_trades: int
    win_rate: float
    profit_factor: float
    max_drawdown_pct: float
    sharpe_ratio: float
    sortino_ratio: float
    calmar_ratio: float
    avg_win: float
    avg_loss: float
    total_commission: float
    total_slippage: float
    equity_curve: List[float] = field(default_factory=list)
    trade_log: List[dict] = field(default_factory=list)


def realistic_backtest(
    df: pd.DataFrame,
    predictions: np.ndarray,
    target_close_col: str = "Close",
    target_return_col: str = "Target_Next_Return",
    initial_capital: float = 100_000_000,
    commission_buy: float = BEI_COMMISSION_BUY,
    commission_sell: float = BEI_COMMISSION_SELL,
    slippage_bps: float = 5.0,  # 5 basis points = 0.05%
    settlement_days: int = BEI_SETTLEMENT_DAYS,
    risk_per_trade: float = 0.02,
    use_atr_stops: bool = True,
    atr_col: str = "Target_ATR",
) -> RealisticBacktestResult:
    """
    Event-driven backtesting dengan realistic BEI constraints.

    Features:
    - Commission: 0.15% beli, 0.25% jual
    - Slippage: configurable (default 5 bps)
    - Settlement: T+2 (tidak bisa jual saham yang baru dibeli dalam 2 hari)
    - ATR-based stop loss
    - Risk-based position sizing

    Referensi: QuantRocket event-driven backtesting
    """
    slippage = slippage_bps / 10000

    capital = initial_capital
    position = 0  # 0 = no position, 1 = long
    shares = 0
    entry_price = 0
    stop_loss = 0
    entry_day = 0
    trades = []
    equity_curve = []
    total_commission = 0
    total_slippage_cost = 0

    prices = df[target_close_col].values if target_close_col in df.columns else df.iloc[:, 0].values
    df[target_return_col].values if target_return_col in df.columns else np.zeros(len(df))
    atrs = df[atr_col].values if atr_col in df.columns else np.zeros(len(df))

    n = min(len(predictions), len(prices))

    for i in range(n):
        signal = predictions[i]
        current_price = prices[i]
        current_atr = atrs[i] if i < len(atrs) and not np.isnan(atrs[i]) else current_price * 0.015

        # Check stop loss first
        if position == 1 and stop_loss > 0 and current_price <= stop_loss:
            exec_price = current_price * (1 - slippage)
            proceeds = shares * exec_price
            commission = proceeds * commission_sell
            capital += proceeds - commission
            total_commission += commission
            total_slippage_cost += shares * current_price * slippage

            pnl = (exec_price - entry_price) * shares - commission - (entry_price * shares * commission_buy)
            trades.append({
                "type": "SELL (stop)",
                "day": i,
                "price": exec_price,
                "shares": shares,
                "pnl": pnl,
                "commission": commission,
            })
            position = 0
            shares = 0

        # BUY signal
        elif signal == 1 and position == 0:
            risk_amount = capital * risk_per_trade
            if current_atr > 0:
                risk_per_share = 2 * current_atr
            else:
                risk_per_share = current_price * 0.03

            if risk_per_share > 0:
                target_shares = int(risk_amount / risk_per_share / BEI_MIN_LOT) * BEI_MIN_LOT
                if target_shares <= 0:
                    target_shares = BEI_MIN_LOT

                cost = target_shares * current_price
                commission = cost * commission_buy
                total_cost = cost + commission

                if total_cost <= capital:
                    exec_price = current_price * (1 + slippage)
                    shares = target_shares
                    entry_price = exec_price
                    stop_loss = exec_price - 2 * current_atr if current_atr > 0 else exec_price * 0.97
                    entry_day = i
                    capital -= total_cost
                    total_commission += commission
                    total_slippage_cost += target_shares * current_price * slippage
                    position = 1

                    trades.append({
                        "type": "BUY",
                        "day": i,
                        "price": exec_price,
                        "shares": shares,
                        "commission": commission,
                    })

        # SELL signal (and past settlement)
        elif signal == 0 and position == 1 and (i - entry_day) >= settlement_days:
            exec_price = current_price * (1 - slippage)
            proceeds = shares * exec_price
            commission = proceeds * commission_sell
            capital += proceeds - commission
            total_commission += commission
            total_slippage_cost += shares * current_price * slippage

            pnl = (exec_price - entry_price) * shares - commission - (entry_price * shares * commission_buy)
            trades.append({
                "type": "SELL",
                "day": i,
                "price": exec_price,
                "shares": shares,
                "pnl": pnl,
                "commission": commission,
            })
            position = 0
            shares = 0

        # Track equity
        current_value = capital + (shares * current_price if position == 1 else 0)
        equity_curve.append(current_value)

    # Close any remaining position
    if position == 1 and n > 0:
        exec_price = prices[-1] * (1 - slippage)
        proceeds = shares * exec_price
        commission = proceeds * commission_sell
        capital += proceeds - commission
        total_commission += commission
        trades.append({
            "type": "SELL (final)",
            "day": n - 1,
            "price": exec_price,
            "shares": shares,
            "pnl": (exec_price - entry_price) * shares - commission,
            "commission": commission,
        })
        position = 0
        equity_curve[-1] = capital

    # Calculate metrics
    final_capital = capital
    total_return = ((final_capital - initial_capital) / initial_capital) * 100

    # Buy & hold
    if n > 0:
        bh_shares = initial_capital / prices[0]
        bh_final = bh_shares * prices[-1]
        bh_return = ((bh_final - initial_capital) / initial_capital) * 100
    else:
        bh_return = 0

    # Trade analysis
    sell_trades = [t for t in trades if "SELL" in t["type"] and "pnl" in t]
    n_trades = len(sell_trades)
    winning = [t for t in sell_trades if t["pnl"] > 0]
    losing = [t for t in sell_trades if t["pnl"] <= 0]
    win_rate = (len(winning) / n_trades * 100) if n_trades > 0 else 0

    gross_profit = sum(t["pnl"] for t in winning) if winning else 0
    gross_loss = abs(sum(t["pnl"] for t in losing)) if losing else 0
    profit_factor = gross_profit / gross_loss if gross_loss > 0 else float('inf')

    avg_win = np.mean([t["pnl"] for t in winning]) if winning else 0
    avg_loss = np.mean([t["pnl"] for t in losing]) if losing else 0

    # Drawdown
    if equity_curve:
        peak = np.maximum.accumulate(equity_curve)
        dd = (np.array(equity_curve) - peak) / peak * 100
        max_dd = float(np.min(dd))
    else:
        max_dd = 0

    # Sharpe & Sortino
    if len(equity_curve) > 1:
        eq_returns = pd.Series(equity_curve).pct_change().dropna()
        if eq_returns.std() > 0:
            sharpe = float(eq_returns.mean() / eq_returns.std() * np.sqrt(252))
        else:
            sharpe = 0
        downside = eq_returns[eq_returns < 0]
        if len(downside) > 0 and downside.std() > 0:
            sortino = float(eq_returns.mean() / downside.std() * np.sqrt(252))
        else:
            sortino = 0
    else:
        sharpe = 0
        sortino = 0

    calmar = total_return / abs(max_dd) if max_dd != 0 else 0

    return RealisticBacktestResult(
        initial_capital=initial_capital,
        final_capital=float(round(final_capital, 0)),
        total_return_pct=round(total_return, 2),
        buy_hold_return_pct=round(bh_return, 2),
        n_trades=n_trades,
        n_winning_trades=len(winning),
        n_losing_trades=len(losing),
        win_rate=round(win_rate, 1),
        profit_factor=round(profit_factor, 2) if profit_factor != float('inf') else 999.0,
        max_drawdown_pct=round(max_dd, 2),
        sharpe_ratio=round(sharpe, 2),
        sortino_ratio=round(sortino, 2),
        calmar_ratio=round(calmar, 2),
        avg_win=round(avg_win, 0),
        avg_loss=round(avg_loss, 0),
        total_commission=float(round(total_commission, 0)),
        total_slippage=float(round(total_slippage_cost, 0)),
        equity_curve=equity_curve,
        trade_log=trades,
    )


# =============================================================================
# VECTORIZED BACKTESTING (pandas-based, fast)
# =============================================================================

def vectorized_backtest(
    df: pd.DataFrame,
    predictions: np.ndarray,
    close_col: str = "Close",
    return_col: str = "Target_Next_Return",
    commission_buy: float = BEI_COMMISSION_BUY,
    commission_sell: float = BEI_COMMISSION_SELL,
    initial_capital: float = 100_000_000,
    include_full_bei_cost: bool = True,
) -> Dict:
    """
    Vectorized backtesting — pandas-based, 75x faster dari event-driven.

    When include_full_bei_cost=True, uses complete BEI/IDX cost structure:
    commission + exchange fee + clearing fee + PPh final + SBK.

    Referensi: QuantRocket Moonshot vectorized backtesting
    """
    n = min(len(predictions), len(df))
    preds = predictions[:n]
    df_bt = df.iloc[:n].copy()

    if close_col not in df_bt.columns:
        return {"error": f"Column {close_col} not found"}
    if return_col not in df_bt.columns:
        df_bt[return_col] = df_bt[close_col].pct_change()

    # Strategy: long when BUY signal, flat when SELL
    position = pd.Series(preds, index=df_bt.index)
    strategy_returns = position.shift(1) * df_bt[return_col]

    # Full BEI cost: commission + exchange + clearing + PPh + SBK
    position_change = position.diff().abs()

    if include_full_bei_cost:
        # Buy cost: commission + exchange + clearing + SBK
        buy_cost_rate = (
            BEI_COMMISSION_BUY + BEI_EXCHANGE_FEE + BEI_CLEARING_FEE + BEI_SBK_FEE
        )
        # Sell cost: commission + exchange + clearing + PPh + SBK
        sell_cost_rate = (
            BEI_COMMISSION_SELL + BEI_EXCHANGE_FEE + BEI_CLEARING_FEE
            + BEI_PPH_FINAL + BEI_SBK_FEE
        )
    else:
        buy_cost_rate = commission_buy
        sell_cost_rate = commission_sell

    total_cost = position_change * (
        np.where(position == 1, buy_cost_rate, sell_cost_rate)
    )
    net_returns = strategy_returns - total_cost

    # Equity curve
    equity = (1 + net_returns).cumprod() * initial_capital

    # Buy & hold
    bh_equity = (1 + df_bt[return_col]).cumprod() * initial_capital

    # Metrics
    total_return = ((equity.iloc[-1] - initial_capital) / initial_capital) * 100
    bh_return = ((bh_equity.iloc[-1] - initial_capital) / initial_capital) * 100

    # Drawdown
    peak = equity.cummax()
    drawdown = ((equity - peak) / peak) * 100
    max_dd = float(drawdown.min())

    # Sharpe
    if net_returns.std() > 0:
        sharpe = float(net_returns.mean() / net_returns.std() * np.sqrt(252))
    else:
        sharpe = 0

    # Win rate
    n_trades = int(position_change.sum() / 2)  # Each round trip = 2 changes
    winning_days = (net_returns > 0).sum()
    total_days = (net_returns != 0).sum()
    win_rate = (winning_days / total_days * 100) if total_days > 0 else 0

    # Total costs
    total_costs = float((total_cost.sum()) * initial_capital)
    cost_pct_capital = (total_costs / initial_capital) * 100

    return {
        "total_return_pct": round(total_return, 2),
        "buy_hold_return_pct": round(bh_return, 2),
        "max_drawdown_pct": round(max_dd, 2),
        "sharpe_ratio": round(sharpe, 2),
        "n_trades": n_trades,
        "win_rate": round(win_rate, 1),
        "equity_curve": equity.tolist(),
        "bh_equity_curve": bh_equity.tolist(),
        "total_trading_costs": round(total_costs, 0),
        "cost_pct_of_capital": round(cost_pct_capital, 4),
        "cost_per_trade_bps": round(float(total_cost.sum() / max(position_change.sum(), 1)) * 10000, 2),
        "cost_structure": "full_bei" if include_full_bei_cost else "commission_only",
        "buy_cost_rate_bps": round(buy_cost_rate * 10000, 2),
        "sell_cost_rate_bps": round(sell_cost_rate * 10000, 2),
    }


# =============================================================================
# PORTFOLIO TEAR SHEET (Pyfolio-style)
# =============================================================================

def generate_tear_sheet(
    equity_curve: List[float],
    benchmark: Optional[List[float]] = None,
    risk_free_rate: float = 0.05,
    periods_per_year: int = 252,
) -> Dict:
    """
    Generate portfolio tear sheet metrics (Pyfolio-style).

    Returns dict dengan:
    - Returns analysis: total return, annual return, monthly return
    - Risk metrics: Sharpe, Sortino, Calmar, max drawdown
    - Trade metrics: win rate, profit factor, avg win/loss
    - Benchmark comparison: alpha, beta, information ratio

    Referensi: QuantRocket Pyfolio
    """
    equity = pd.Series(equity_curve, name="equity")
    returns = equity.pct_change().dropna()

    # Basic returns
    total_return = ((equity.iloc[-1] - equity.iloc[0]) / equity.iloc[0]) * 100
    n_years = len(returns) / periods_per_year
    annual_return = ((equity.iloc[-1] / equity.iloc[0]) ** (1 / n_years) - 1) * 100 if n_years > 0 else 0

    # Risk metrics
    if returns.std() > 0:
        sharpe = (returns.mean() - risk_free_rate / periods_per_year) / returns.std() * np.sqrt(periods_per_year)
    else:
        sharpe = 0

    downside = returns[returns < 0]
    if len(downside) > 0 and downside.std() > 0:
        sortino = (returns.mean() - risk_free_rate / periods_per_year) / downside.std() * np.sqrt(periods_per_year)
    else:
        sortino = 0

    # Drawdown
    peak = equity.cummax()
    drawdown = (equity - peak) / peak * 100
    max_dd = float(drawdown.min())
    max_dd_duration = int((drawdown < 0).astype(int).groupby((drawdown == 0).cumsum()).sum().max())

    calmar = annual_return / abs(max_dd) if max_dd != 0 else 0

    # Volatility
    annual_vol = returns.std() * np.sqrt(periods_per_year) * 100

    # Value at Risk
    var_95 = float(np.percentile(returns, 5) * 100)
    var_99 = float(np.percentile(returns, 1) * 100)

    # CVaR
    cvar_95 = float(returns[returns <= np.percentile(returns, 5)].mean() * 100)

    # Best/Worst day
    best_day = float(returns.max() * 100)
    worst_day = float(returns.min() * 100)

    # Benchmark comparison
    alpha = beta = info_ratio = 0
    if benchmark and len(benchmark) == len(equity_curve):
        bench = pd.Series(benchmark).pct_change().dropna()
        if len(bench) == len(returns) and returns.std() > 0 and bench.std() > 0:
            cov_matrix = np.cov(returns, bench)
            beta = float(cov_matrix[0, 1] / cov_matrix[1, 1])
            alpha = float(returns.mean() - beta * bench.mean()) * periods_per_year * 100
            tracking_error = (returns - bench).std()
            if tracking_error > 0:
                info_ratio = float((returns.mean() - bench.mean()) / tracking_error * np.sqrt(periods_per_year))

    return {
        "total_return_pct": round(total_return, 2),
        "annual_return_pct": round(annual_return, 2),
        "annual_volatility_pct": round(annual_vol, 2),
        "sharpe_ratio": round(sharpe, 2),
        "sortino_ratio": round(sortino, 2),
        "calmar_ratio": round(calmar, 2),
        "max_drawdown_pct": round(max_dd, 2),
        "max_drawdown_duration_days": max_dd_duration,
        "var_95_pct": round(var_95, 2),
        "var_99_pct": round(var_99, 2),
        "cvar_95_pct": round(cvar_95, 2),
        "best_day_pct": round(best_day, 2),
        "worst_day_pct": round(worst_day, 2),
        "alpha_pct": round(alpha, 2),
        "beta": round(beta, 2),
        "information_ratio": round(info_ratio, 2),
    }


# =============================================================================
# WYCKOFF PHASE DETECTION
# =============================================================================

@dataclass
class WyckoffAnalysis:
    phase: str  # "Accumulation", "Distribution", "Markup", "Markdown", "Unknown"
    event: str  # "Spring", "SOS", "Upthrust", "SOW", "None"
    confidence: float
    description: str


def detect_wyckoff_phase(df: pd.DataFrame, window: int = 30) -> WyckoffAnalysis:
    """
    Deteksi fase Wyckoff: Accumulation/Distribution.

    Events:
    - Spring: false breakdown di bawah support → bullish (smart money buying)
    - SOS (Sign of Strength): breakout di atas resistance → bullish
    - Upthrust: false breakout di atas resistance → bearish (smart money selling)
    - SOW (Sign of Weakness): breakdown di bawah support → bearish

    Referensi: Hanzo AI Wyckoff phase detection
    """
    required = ["High", "Low", "Close", "Volume"]
    if not all(c in df.columns for c in required):
        return WyckoffAnalysis(phase="Unknown", event="None", confidence=0, description="Data tidak lengkap")

    recent = df.tail(window)
    if len(recent) < 15:
        return WyckoffAnalysis(phase="Unknown", event="None", confidence=0, description="Data terlalu sedikit")

    highs = recent["High"].values
    lows = recent["Low"].values
    closes = recent["Close"].values
    volumes = recent["Volume"].values

    # Find support and resistance
    resistance = np.percentile(highs, 90)
    support = np.percentile(lows, 10)
    avg_volume = np.mean(volumes)

    last_close = closes[-1]
    last_volume = volumes[-1]
    price_range = resistance - support

    if price_range <= 0:
        return WyckoffAnalysis(phase="Unknown", event="None", confidence=0, description="Range tidak valid")

    # Check for Spring (price dips below support then recovers)
    recent_lows = lows[-5:]
    if any(l < support * 0.98 for l in recent_lows) and last_close > support:
        confidence = 0.7 if last_volume > avg_volume * 1.5 else 0.5
        return WyckoffAnalysis(
            phase="Accumulation",
            event="Spring",
            confidence=confidence,
            description=f"Spring terdeteksi: harga sempat break support {support:.2f} lalu recover ke {last_close:.2f}"
        )

    # Check for Upthrust (price spikes above resistance then falls back)
    recent_highs = highs[-5:]
    if any(h > resistance * 1.02 for h in recent_highs) and last_close < resistance:
        confidence = 0.7 if last_volume > avg_volume * 1.5 else 0.5
        return WyckoffAnalysis(
            phase="Distribution",
            event="Upthrust",
            confidence=confidence,
            description=f"Upthrust terdeteksi: harga sempat break resistance {resistance:.2f} lalu turun ke {last_close:.2f}"
        )

    # Check for SOS (breakout above resistance with volume)
    if last_close > resistance * 1.02 and last_volume > avg_volume * 1.3:
        return WyckoffAnalysis(
            phase="Markup",
            event="SOS",
            confidence=0.75,
            description=f"SOS: breakout resistance {resistance:.2f} dengan volume tinggi"
        )

    # Check for SOW (breakdown below support with volume)
    if last_close < support * 0.98 and last_volume > avg_volume * 1.3:
        return WyckoffAnalysis(
            phase="Markdown",
            event="SOW",
            confidence=0.75,
            description=f"SOW: breakdown support {support:.2f} dengan volume tinggi"
        )

    # Range-bound = potential accumulation/distribution
    if abs(last_close - (support + resistance) / 2) < price_range * 0.2:
        vol_trend = np.mean(volumes[-10:]) / np.mean(volumes[:10]) if len(volumes) >= 20 else 1
        if vol_trend < 0.8:
            phase = "Accumulation"
            desc = "Consolidation dengan volume menurun — potential accumulation phase"
        elif vol_trend > 1.2:
            phase = "Distribution"
            desc = "Consolidation dengan volume meningkat — potential distribution phase"
        else:
            phase = "Unknown"
            desc = "Consolidation tanpa klarifikasi fase"
        return WyckoffAnalysis(phase=phase, event="None", confidence=0.4, description=desc)

    return WyckoffAnalysis(
        phase="Unknown", event="None", confidence=0,
        description=f"Tidak ada pola Wyckoff jelas. Support={support:.2f}, Resistance={resistance:.2f}"
    )


# =============================================================================
# DEFLATED SHARPE RATIO (DSR)
# =============================================================================
# Referensi: Bailey & López de Prado (2014) "The Deflated Sharpe Ratio"
# Corrects for multiple testing bias — adjusts Sharpe for number of trials,
# skewness, and kurtosis of returns.
# =============================================================================

def deflated_sharpe_ratio(
    sharpe: float,
    n_trials: int = 1,
    n_obs: int = 252,
    skew: float = 0.0,
    kurtosis: float = 3.0,
    return_mean: float = 0.0,
    return_std: float = 0.01,
) -> Dict:
    """
    Calculate Deflated Sharpe Ratio (DSR).

    Corrects observed Sharpe for:
    - Multiple testing (n_trials)
    - Non-normality (skew, kurtosis)
    - Sample length (n_obs)

    Returns dict with DSR, p-value, and assessment.
    """
    from scipy import stats

    # Expected maximum Sharpe under null (multiple testing)
    if n_trials > 1:
        euler_mascheroni = 0.5772156649
        expected_max_sharpe = (
            np.sqrt(2 * np.log(n_trials)) * (1 - euler_mascheroni / (2 * np.log(n_trials)))
        )
    else:
        expected_max_sharpe = 0.0

    # Variance of Sharpe estimate (Inference-Safe Sharpe)
    sr_var = (1 - skew * sharpe + (kurtosis - 1) / 4 * sharpe**2) / (n_obs - 1)
    sr_std = np.sqrt(sr_var) if sr_var > 0 else 0.01

    # Deflated Sharpe
    dsr = (sharpe - expected_max_sharpe) / sr_std if sr_std > 0 else 0.0

    # p-value: probability of observing this Sharpe under null
    p_value = 1 - stats.norm.cdf(dsr) if sr_std > 0 else 0.5

    # Assessment
    if p_value < 0.01:
        assessment = "Highly significant — strategy likely has real predictive power"
    elif p_value < 0.05:
        assessment = "Significant — strategy shows genuine skill"
    elif p_value < 0.10:
        assessment = "Marginally significant — possible skill but uncertain"
    else:
        assessment = "Not significant — likely result of multiple testing bias"

    return {
        "deflated_sharpe": round(dsr, 4),
        "p_value": round(p_value, 4),
        "expected_max_sharpe": round(expected_max_sharpe, 4),
        "sharpe_std": round(sr_std, 4),
        "assessment": assessment,
        "n_trials": n_trials,
        "n_obs": n_obs,
    }


# =============================================================================
# MODEL DRIFT DETECTION
# =============================================================================
# Referensi: Evidently AI, Microsoft Qlib drift detection
# Detects when model performance degrades or feature distributions shift.
# =============================================================================

@dataclass
class DriftReport:
    is_drifted: bool
    drift_score: float
    drifted_features: List[str] = field(default_factory=list)
    performance_degraded: bool = False
    current_accuracy: float = 0.0
    baseline_accuracy: float = 0.0
    recommendations: List[str] = field(default_factory=list)


def detect_feature_drift(
    baseline_df: pd.DataFrame,
    current_df: pd.DataFrame,
    feature_cols: List[str],
    threshold: float = 0.05,
) -> DriftReport:
    """
    Detect feature distribution drift using Kolmogorov-Smirnov test.

    Compares baseline (training) distribution vs current distribution.
    If p-value < threshold, feature has drifted.
    """
    from scipy.stats import ks_2samp

    drifted_features = []
    drift_scores = []

    for col in feature_cols:
        if col not in baseline_df.columns or col not in current_df.columns:
            continue
        baseline_vals = baseline_df[col].dropna().values
        current_vals = current_df[col].dropna().values
        if len(baseline_vals) < 10 or len(current_vals) < 10:
            continue
        stat, p_value = ks_2samp(baseline_vals, current_vals)
        drift_scores.append(p_value)
        if p_value < threshold:
            drifted_features.append(col)

    avg_p = np.mean(drift_scores) if drift_scores else 1.0
    drift_score = 1 - avg_p  # Higher = more drift

    recommendations = []
    if len(drifted_features) > len(feature_cols) * 0.3:
        recommendations.append("Severe drift detected — retrain model immediately")
    elif len(drifted_features) > 0:
        recommendations.append(f"Drift in {len(drifted_features)} features — monitor closely")
    else:
        recommendations.append("No significant drift — model is stable")

    return DriftReport(
        is_drifted=len(drifted_features) > 0,
        drift_score=round(drift_score, 4),
        drifted_features=drifted_features,
        recommendations=recommendations,
    )


def detect_performance_drift(
    baseline_accuracy: float,
    current_accuracy: float,
    threshold: float = 0.05,
) -> DriftReport:
    """
    Detect model performance degradation.

    Triggers if accuracy drops by more than threshold percentage points.
    """
    degradation = baseline_accuracy - current_accuracy
    is_degraded = degradation > threshold

    recommendations = []
    if is_degraded:
        recommendations.append(
            f"Accuracy dropped {degradation:.2%} (from {baseline_accuracy:.2%} to {current_accuracy:.2%}) — retrain recommended"
        )
    else:
        recommendations.append(f"Performance stable ({current_accuracy:.2%} vs baseline {baseline_accuracy:.2%})")

    return DriftReport(
        is_drifted=is_degraded,
        drift_score=round(degradation, 4),
        performance_degraded=is_degraded,
        current_accuracy=current_accuracy,
        baseline_accuracy=baseline_accuracy,
        recommendations=recommendations,
    )
