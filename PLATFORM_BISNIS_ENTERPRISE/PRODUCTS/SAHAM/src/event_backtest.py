"""
Event-Driven Backtesting Engine.

Simulates trading strategy with realistic assumptions:
- Order submission → fill → portfolio update → risk check → next signal
- Transaction costs (BEI fees, commission)
- Slippage estimation
- Position sizing based on confidence
- Walk-forward: train on expanding window, predict next bar

References:
- López de Prado (2018), "Advances in Financial Machine Learning" — backtesting
- QuantConnect/LEAN — event-driven architecture
- QuantRocket MoonshotML — walk-forward backtesting

Usage:
    from src.event_backtest import run_event_backtest
    result = run_event_backtest(market_data, fred_data, initial_capital=100_000_000)
    print(result.summary())
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import List, Optional

import numpy as np

from .config import TARGET_TICKER, TICKERS
from .models import HybridEnsemble
from .preprocessor import prepare_features, get_feature_columns
from .quant_finance import calc_bei_total_cost

logger = logging.getLogger(__name__)


@dataclass
class Trade:
    """Single trade record."""
    entry_date: str = ""
    exit_date: str = ""
    side: str = "BUY"
    entry_price: float = 0.0
    exit_price: float = 0.0
    shares: int = 0
    pnl: float = 0.0
    pnl_pct: float = 0.0
    cost: float = 0.0
    net_pnl: float = 0.0
    signal: str = ""
    confidence: float = 0.0
    hold_days: int = 0


@dataclass
class EventBacktestResult:
    """Result from event-driven backtest."""
    initial_capital: float = 0.0
    final_capital: float = 0.0
    total_return_pct: float = 0.0
    buy_hold_return_pct: float = 0.0
    n_trades: int = 0
    n_wins: int = 0
    n_losses: int = 0
    win_rate: float = 0.0
    profit_factor: float = 0.0
    max_drawdown_pct: float = 0.0
    sharpe_ratio: float = 0.0
    sortino_ratio: float = 0.0
    calmar_ratio: float = 0.0
    total_commission: float = 0.0
    total_slippage_cost: float = 0.0
    avg_hold_days: float = 0.0
    equity_curve: List[float] = field(default_factory=list)
    trades: List[Trade] = field(default_factory=list)
    daily_returns: List[float] = field(default_factory=list)
    error: str = ""

    def summary(self) -> str:
        if self.error:
            return f"Backtest failed: {self.error}"
        return (
            f"Event Backtest Results:\n"
            f"  Capital: {self.initial_capital:,.0f} → {self.final_capital:,.0f}\n"
            f"  Return: {self.total_return_pct:.2f}% (B&H: {self.buy_hold_return_pct:.2f}%)\n"
            f"  Trades: {self.n_trades} (W: {self.n_wins}, L: {self.n_losses})\n"
            f"  Win Rate: {self.win_rate:.1f}%\n"
            f"  Profit Factor: {self.profit_factor:.2f}\n"
            f"  Max DD: {self.max_drawdown_pct:.2f}%\n"
            f"  Sharpe: {self.sharpe_ratio:.2f} | Sortino: {self.sortino_ratio:.2f}\n"
            f"  Commission: {self.total_commission:,.0f} | Slippage: {self.total_slippage_cost:,.0f}\n"
            f"  Avg Hold: {self.avg_hold_days:.1f} days"
        )


def run_event_backtest(
    market_data: dict,
    fred_data: Optional[dict] = None,
    target_ticker: str = TARGET_TICKER,
    initial_capital: float = 100_000_000,
    position_size_pct: float = 0.20,
    max_positions: int = 1,
    stop_loss_pct: float = 0.05,
    take_profit_pct: float = 0.10,
    slippage_bps: float = 5.0,
    confidence_threshold: float = 0.55,
    walk_forward: bool = True,
    n_walk_forward_folds: int = 5,
) -> EventBacktestResult:
    """
    Run event-driven backtest with realistic costs and risk management.

    Args:
        market_data: Market data dict
        fred_data: FRED macro data
        target_ticker: Target ticker
        initial_capital: Starting capital in IDR
        position_size_pct: Fraction of capital per position
        max_positions: Max concurrent positions
        stop_loss_pct: Stop loss percentage
        take_profit_pct: Take profit percentage
        slippage_bps: Slippage in basis points
        confidence_threshold: Minimum confidence to enter trade
        walk_forward: Use walk-forward retraining
        n_walk_forward_folds: Number of retraining folds

    Returns:
        EventBacktestResult with full trade log and metrics
    """
    result = EventBacktestResult(initial_capital=initial_capital)

    df = prepare_features(market_data, fred_data, target_ticker)
    if df.empty:
        result.error = "Data kosong setelah preprocessing"
        return result

    target_name = None
    for name, ticker in TICKERS.items():
        if ticker == target_ticker:
            target_name = name
            break
    if target_name is None:
        target_name = "TARGET"

    feature_cols = get_feature_columns(df)
    df_clean = df.dropna(subset=feature_cols + ["Target_Next_Return"]).copy()
    df_clean["Target_Direction"] = (df_clean["Target_Next_Return"] > 0).astype(int)

    if len(df_clean) < 100:
        result.error = f"Data terlalu sedikit: {len(df_clean)} baris"
        return result

    close_col = f"{target_name}_Close"
    if close_col not in df_clean.columns:
        for col in df_clean.columns:
            if col.endswith("_Close"):
                close_col = col
                break

    prices = df_clean[close_col].values
    dates = df_clean.index
    df_clean["Target_Next_Return"].values

    # Walk-forward: split into folds
    if walk_forward:
        fold_size = len(df_clean) // (n_walk_forward_folds + 1)
        if fold_size < 50:
            fold_size = len(df_clean) // 2
    else:
        fold_size = len(df_clean)

    capital = initial_capital
    position = 0
    entry_price = 0.0
    entry_shares = 0
    entry_date_idx = 0
    entry_confidence = 0.0
    entry_signal = ""
    peak_equity = initial_capital
    max_dd = 0.0
    total_commission = 0.0
    total_slippage = 0.0
    trades: List[Trade] = []
    equity_curve: List[float] = []
    daily_returns_list: List[float] = []
    gross_profit = 0.0
    gross_loss = 0.0

    ensemble = HybridEnsemble()
    current_fold = 0
    train_end = fold_size

    for i in range(fold_size, len(df_clean)):
        # Walk-forward retrain
        if walk_forward and i >= train_end and current_fold < n_walk_forward_folds:
            train_df = df_clean.iloc[:train_end]
            X_train = train_df[feature_cols]
            y_train = train_df["Target_Direction"]
            ensemble = HybridEnsemble()
            ensemble.train(X_train, y_train)
            current_fold += 1
            train_end = fold_size * (current_fold + 1)

        # Get prediction for current bar
        if not ensemble.trained and i > 0:
            train_df = df_clean.iloc[:i]
            if len(train_df) >= 50:
                X_train = train_df[feature_cols]
                y_train = train_df["Target_Direction"]
                ensemble.train(X_train, y_train)

        if not ensemble.trained:
            equity_curve.append(capital + (entry_shares * prices[i] if position else 0))
            continue

        X_current = df_clean[feature_cols].iloc[i:i+1]
        try:
            preds, probas = ensemble.predict_ensemble(X_current)
            votes_buy = sum(1 for p in preds.values() if p == 1)
            votes_sell = sum(1 for p in preds.values() if p == 0)
            signal = "BUY" if votes_buy > votes_sell else "SELL"
            confidence = float(np.mean(list(probas.values()))) if probas else 0.5
        except Exception:
            signal = "HOLD"
            confidence = 0.0

        current_price = prices[i]
        slippage = current_price * slippage_bps / 10000

        # Check stop loss / take profit for open position
        if position:
            pnl_pct = (current_price - entry_price) / entry_price
            hold_days = i - entry_date_idx

            if pnl_pct <= -stop_loss_pct or pnl_pct >= take_profit_pct or hold_days >= 20:
                # Close position
                exit_price = current_price - slippage if entry_signal == "BUY" else current_price + slippage
                trade_value = entry_shares * exit_price
                cost = calc_bei_total_cost(trade_value, "sell")["total_cost"]
                total_commission += cost
                total_slippage += entry_shares * slippage

                pnl = (exit_price - entry_price) * entry_shares - cost - entry_shares * slippage
                net_pnl = pnl

                trade = Trade(
                    entry_date=str(dates[entry_date_idx].date()),
                    exit_date=str(dates[i].date()),
                    side=entry_signal,
                    entry_price=round(entry_price, 2),
                    exit_price=round(exit_price, 2),
                    shares=entry_shares,
                    pnl=round(pnl, 0),
                    pnl_pct=round(pnl_pct * 100, 2),
                    cost=round(cost, 0),
                    net_pnl=round(net_pnl, 0),
                    signal=entry_signal,
                    confidence=entry_confidence,
                    hold_days=hold_days,
                )
                trades.append(trade)

                capital += net_pnl

                if net_pnl > 0:
                    gross_profit += net_pnl
                else:
                    gross_loss += abs(net_pnl)

                position = 0
                entry_shares = 0

        # Open new position
        if not position and signal == "BUY" and confidence >= confidence_threshold:
            trade_value = capital * position_size_pct
            entry_price = current_price + slippage
            entry_shares = int(trade_value / entry_price / 100) * 100  # Round to lot
            if entry_shares > 0:
                cost = calc_bei_total_cost(entry_shares * entry_price, "buy")["total_cost"]
                total_commission += cost
                capital -= cost
                position = 1
                entry_date_idx = i
                entry_confidence = confidence
                entry_signal = "BUY"

        # Track equity
        equity = capital + (entry_shares * current_price if position else 0)
        equity_curve.append(float(equity))
        peak_equity = max(peak_equity, equity)
        dd = (equity - peak_equity) / peak_equity * 100
        max_dd = min(max_dd, dd)

        if len(equity_curve) > 1:
            daily_ret = (equity_curve[-1] - equity_curve[-2]) / equity_curve[-2]
            daily_returns_list.append(daily_ret)

    # Close any remaining position
    if position and len(prices) > 0:
        exit_price = prices[-1]
        trade_value = entry_shares * exit_price
        cost = calc_bei_total_cost(trade_value, "sell")["total_cost"]
        total_commission += cost
        pnl = (exit_price - entry_price) * entry_shares - cost
        capital += pnl
        if pnl > 0:
            gross_profit += pnl
        else:
            gross_loss += abs(pnl)
        trades.append(Trade(
            entry_date=str(dates[entry_date_idx].date()),
            exit_date=str(dates[-1].date()),
            side=entry_signal,
            entry_price=round(entry_price, 2),
            exit_price=round(exit_price, 2),
            shares=entry_shares,
            pnl=round(pnl, 0),
            pnl_pct=round((exit_price - entry_price) / entry_price * 100, 2),
            cost=round(cost, 0),
            net_pnl=round(pnl, 0),
            signal=entry_signal,
            confidence=entry_confidence,
            hold_days=len(prices) - entry_date_idx,
        ))

    # Calculate metrics
    result.final_capital = round(capital, 0)
    result.total_return_pct = round((capital - initial_capital) / initial_capital * 100, 2)
    result.n_trades = len(trades)
    result.n_wins = sum(1 for t in trades if t.net_pnl > 0)
    result.n_losses = sum(1 for t in trades if t.net_pnl <= 0)
    result.win_rate = round(result.n_wins / max(result.n_trades, 1) * 100, 1)
    result.profit_factor = round(gross_profit / max(gross_loss, 1), 2)
    result.max_drawdown_pct = round(max_dd, 2)
    result.total_commission = round(total_commission, 0)
    result.total_slippage_cost = round(total_slippage, 0)
    result.trades = trades
    result.equity_curve = equity_curve
    result.daily_returns = daily_returns_list

    if trades:
        result.avg_hold_days = round(np.mean([t.hold_days for t in trades]), 1)

    # Buy & hold
    test_prices = prices[fold_size:]
    if len(test_prices) > 1:
        bh_return = (test_prices[-1] / test_prices[0] - 1) * 100
        result.buy_hold_return_pct = round(bh_return, 2)

    # Sharpe & Sortino
    if len(daily_returns_list) > 10:
        dr = np.array(daily_returns_list)
        dr_clean = dr[~np.isnan(dr)]
        if len(dr_clean) > 0 and np.std(dr_clean) > 0:
            result.sharpe_ratio = round(float(np.mean(dr_clean) / np.std(dr_clean) * np.sqrt(252)), 2)
            downside = dr_clean[dr_clean < 0]
            if len(downside) > 0 and np.std(downside) > 0:
                result.sortino_ratio = round(float(np.mean(dr_clean) / np.std(downside) * np.sqrt(252)), 2)

    if result.max_drawdown_pct != 0:
        result.calmar_ratio = round(result.total_return_pct / abs(result.max_drawdown_pct), 2)

    logger.info(result.summary())
    return result
