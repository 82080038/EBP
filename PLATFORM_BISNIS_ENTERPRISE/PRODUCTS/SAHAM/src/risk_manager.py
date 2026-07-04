"""
Modul Risk Management Profesional
Mencakup: VaR, CVaR, Sharpe Ratio, Sortino Ratio, Max Drawdown, Kelly Criterion, Position Sizing
"""

import pandas as pd
import numpy as np
from scipy import stats
from typing import Dict


def calc_var(returns: pd.Series, confidence: float = 0.95, position_value: float = 100_000_000) -> dict:
    """
    Value at Risk (VaR) - estimasi maksimum kerugian dalam periode tertentu
    pada tingkat confidence tertentu.
    """
    if returns.empty:
        return {"var_percent": 0, "var_value": 0, "cvar_percent": 0, "cvar_value": 0, "parametric_var_percent": 0, "parametric_var_value": 0, "confidence": confidence, "method": "N/A"}

    returns_clean = returns.dropna()
    if returns_clean.empty:
        return {"var_percent": 0, "var_value": 0, "cvar_percent": 0, "cvar_value": 0, "parametric_var_percent": 0, "parametric_var_value": 0, "confidence": confidence, "method": "N/A"}

    # Historical VaR
    var_percent = np.percentile(returns_clean, (1 - confidence) * 100)
    var_value = position_value * var_percent

    # Conditional VaR (Expected Shortfall) - rata-rata kerugian di luar VaR
    tail_losses = returns_clean[returns_clean <= var_percent]
    cvar_percent = tail_losses.mean() if not tail_losses.empty else var_percent
    cvar_value = position_value * cvar_percent

    # Parametric VaR (asumsi normal distribution)
    mean_return = returns_clean.mean()
    std_return = returns_clean.std()
    z_score = stats.norm.ppf(1 - confidence)
    parametric_var = mean_return + z_score * std_return
    parametric_var_value = position_value * parametric_var

    return {
        "var_percent": round(var_percent * 100, 2),
        "var_value": round(var_value, 0),
        "cvar_percent": round(cvar_percent * 100, 2),
        "cvar_value": round(cvar_value, 0),
        "parametric_var_percent": round(parametric_var * 100, 2),
        "parametric_var_value": round(parametric_var_value, 0),
        "confidence": confidence,
        "method": "Historical + Parametric",
    }


def calc_sharpe_ratio(returns: pd.Series, risk_free_rate: float = 0.05, periods: int = 252) -> dict:
    """
    Sharpe Ratio - return excess per unit risk.
    risk_free_rate default 5% (SBN Indonesia tahunan).
    """
    if returns.empty:
        return {"sharpe_daily": 0, "annualized_sharpe": 0, "risk_free_rate": risk_free_rate, "excess_return": 0}

    returns_clean = returns.dropna()
    daily_rf = risk_free_rate / periods

    excess_returns = returns_clean - daily_rf
    sharpe = excess_returns.mean() / excess_returns.std() if excess_returns.std() > 0 else 0
    annualized_sharpe = sharpe * np.sqrt(periods)

    return {
        "sharpe_daily": round(sharpe, 4),
        "annualized_sharpe": round(annualized_sharpe, 2),
        "risk_free_rate": risk_free_rate,
        "excess_return": round(excess_returns.mean() * periods * 100, 2),
    }


def calc_sortino_ratio(returns: pd.Series, risk_free_rate: float = 0.05, periods: int = 252) -> dict:
    """
    Sortino Ratio - seperti Sharpe tapi hanya mempertimbangkan downside volatility.
    """
    if returns.empty:
        return {"sortino_daily": 0, "annualized_sortino": 0, "downside_std": 0}

    returns_clean = returns.dropna()
    daily_rf = risk_free_rate / periods
    excess_returns = returns_clean - daily_rf

    downside_returns = excess_returns[excess_returns < 0]
    downside_std = downside_returns.std() if not downside_returns.empty else 0

    if downside_std > 0:
        sortino = excess_returns.mean() / downside_std
    else:
        sortino = 0

    annualized_sortino = sortino * np.sqrt(periods)

    return {
        "sortino_daily": round(sortino, 4),
        "annualized_sortino": round(annualized_sortino, 2),
        "downside_std": round(downside_std * 100, 4),
    }


def calc_max_drawdown(prices: pd.Series) -> dict:
    """
    Maximum Drawdown - penurunan terbesar dari peak ke trough.
    """
    if prices.empty:
        return {"max_drawdown_pct": 0, "max_dd_duration_days": 0, "current_drawdown_pct": 0}

    running_max = prices.cummax()
    drawdown = (prices - running_max) / running_max
    max_dd = drawdown.min()

    # Durasi drawdown
    in_dd = drawdown < 0
    dd_durations = []
    start = None
    for i, is_dd in enumerate(in_dd):
        if is_dd and start is None:
            start = i
        elif not is_dd and start is not None:
            dd_durations.append(i - start)
            start = None
    if start is not None:
        dd_durations.append(len(in_dd) - start)

    max_dd_duration = max(dd_durations) if dd_durations else 0

    return {
        "max_drawdown_pct": round(max_dd * 100, 2),
        "max_dd_duration_days": max_dd_duration,
        "current_drawdown_pct": round(drawdown.iloc[-1] * 100, 2),
    }


def calc_kelly_criterion(returns: pd.Series) -> dict:
    """
    Kelly Criterion - ukuran posisi optimal untuk memaksimalkan growth.
    """
    if returns.empty:
        return {"kelly_fraction": 0, "half_kelly": 0, "quarter_kelly": 0, "win_rate": 0, "profit_factor": 0, "recommendation": "Jangan trading (data kosong)"}

    returns_clean = returns.dropna()
    wins = returns_clean[returns_clean > 0]
    losses = returns_clean[returns_clean < 0]

    if wins.empty or losses.empty:
        return {"kelly_fraction": 0, "half_kelly": 0, "quarter_kelly": 0, "win_rate": 0, "profit_factor": 0, "recommendation": "Jangan trading (data tidak cukup)"}

    win_rate = len(wins) / len(returns_clean)
    avg_win = wins.mean()
    avg_loss = abs(losses.mean())

    if avg_loss == 0:
        return {"kelly_fraction": 0, "half_kelly": 0, "quarter_kelly": 0, "win_rate": round(win_rate * 100, 2), "profit_factor": 0, "recommendation": "Jangan trading (avg_loss=0)"}

    # Kelly % = W - (1-W)/R, di mana W = win rate, R = avg_win/avg_loss
    R = avg_win / avg_loss
    kelly = win_rate - (1 - win_rate) / R

    # Half Kelly dan Quarter Kelly (lebih konservatif)
    half_kelly = kelly / 2
    quarter_kelly = kelly / 4

    return {
        "kelly_fraction": round(kelly * 100, 2),
        "half_kelly": round(half_kelly * 100, 2),
        "quarter_kelly": round(quarter_kelly * 100, 2),
        "win_rate": round(win_rate * 100, 2),
        "profit_factor": round(R, 2),
        "recommendation": (
            "Full Kelly (agresif)" if kelly > 0.25
            else f"Half Kelly ({half_kelly*100:.1f}% - moderat)"
            if kelly > 0.1
            else f"Quarter Kelly ({quarter_kelly*100:.1f}% - konservatif)"
            if kelly > 0
            else "Jangan trading (Kelly negatif)"
        ),
    }


def calc_position_sizing(
    capital: float,
    risk_per_trade: float = 0.02,
    entry_price: float = 0,
    stop_loss_price: float = 0,
    atr: float = 0,
    method: str = "risk_based",
) -> dict:
    """
    Position Sizing - berapa banyak saham yang harus dibeli berdasarkan risk.
    """
    risk_amount = capital * risk_per_trade

    if method == "risk_based" and entry_price > 0 and stop_loss_price > 0:
        risk_per_share = entry_price - stop_loss_price
        if risk_per_share <= 0:
            return {"error": "Stop loss harus di bawah entry price"}
        shares = int(risk_amount / risk_per_share)
        position_value = shares * entry_price
        return {
            "method": "Risk-Based",
            "capital": capital,
            "risk_per_trade_pct": risk_per_trade * 100,
            "risk_amount": round(risk_amount, 0),
            "entry_price": entry_price,
            "stop_loss": stop_loss_price,
            "risk_per_share": round(risk_per_share, 2),
            "shares": shares,
            "position_value": round(position_value, 0),
            "position_pct": round((position_value / capital) * 100, 2),
        }

    elif method == "atr_based" and entry_price > 0 and atr > 0:
        stop_distance = 2 * atr  # 2x ATR stop
        stop_loss_price = entry_price - stop_distance
        risk_per_share = stop_distance
        shares = int(risk_amount / risk_per_share)
        position_value = shares * entry_price
        return {
            "method": "ATR-Based (2x ATR)",
            "capital": capital,
            "risk_per_trade_pct": risk_per_trade * 100,
            "risk_amount": round(risk_amount, 0),
            "entry_price": entry_price,
            "stop_loss": round(stop_loss_price, 2),
            "atr": round(atr, 2),
            "risk_per_share": round(risk_per_share, 2),
            "shares": shares,
            "position_value": round(position_value, 0),
            "position_pct": round((position_value / capital) * 100, 2),
        }

    elif method == "fixed_fractional":
        position_value = capital * risk_per_trade * 10  # 10x risk = position size
        shares = int(position_value / entry_price) if entry_price > 0 else 0
        return {
            "method": "Fixed Fractional",
            "capital": capital,
            "risk_per_trade_pct": risk_per_trade * 100,
            "position_value": round(position_value, 0),
            "shares": shares,
            "position_pct": round(risk_per_trade * 1000, 2),
        }

    return {"error": "Parameter tidak lengkap"}


def calc_risk_metrics(returns: pd.Series, prices: pd.Series, position_value: float = 100_000_000) -> dict:
    """
    Gabungan semua metrik risk dalam satu call.
    """
    var = calc_var(returns, confidence=0.95, position_value=position_value)
    sharpe = calc_sharpe_ratio(returns)
    sortino = calc_sortino_ratio(returns)
    drawdown = calc_max_drawdown(prices)
    kelly = calc_kelly_criterion(returns)

    # Beta dan Alpha (vs benchmark, asumsi market = returns sendiri jika tidak ada benchmark)
    returns_clean = returns.dropna()
    volatility_annual = returns_clean.std() * np.sqrt(252) * 100

    # Calmar Ratio
    annual_return = returns_clean.mean() * 252 * 100
    calmar = annual_return / abs(drawdown["max_drawdown_pct"]) if drawdown["max_drawdown_pct"] != 0 else 0

    return {
        "var": var,
        "sharpe": sharpe,
        "sortino": sortino,
        "drawdown": drawdown,
        "kelly": kelly,
        "annual_return_pct": round(annual_return, 2),
        "annual_volatility_pct": round(volatility_annual, 2),
        "calmar_ratio": round(calmar, 2),
    }


def calc_risk_per_sector(prices_dict: Dict[str, pd.Series]) -> pd.DataFrame:
    """
    Risk contribution per ticker/sector untuk diversifikasi.
    """
    returns_dict = {k: v.pct_change().dropna() for k, v in prices_dict.items() if not v.empty}
    if not returns_dict:
        return pd.DataFrame()

    stats_list = []
    for name, ret in returns_dict.items():
        if ret.empty:
            continue
        vol = ret.std() * np.sqrt(252) * 100
        sharpe = calc_sharpe_ratio(ret)
        dd = calc_max_drawdown(prices_dict[name])
        var = calc_var(ret, confidence=0.95)

        stats_list.append({
            "Ticker": name,
            "Annual Volatility (%)": round(vol, 2),
            "Sharpe Ratio": sharpe["annualized_sharpe"],
            "Max Drawdown (%)": dd["max_drawdown_pct"],
            "VaR 95% (%)": var["var_percent"],
            "CVaR 95% (%)": var["cvar_percent"],
        })

    return pd.DataFrame(stats_list).sort_values("Annual Volatility (%)", ascending=False)
