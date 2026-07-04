"""
Options analysis: pricing, Greeks, implied volatility, and strategy evaluation.

Implements:
- Black-Scholes pricing (European options)
- Greeks: Delta, Gamma, Theta, Vega, Rho
- Implied volatility (Newton-Raphson + bisection fallback)
- Options strategies: straddle, strangle, iron condor, covered call, protective put
- Put-Call parity verification

References:
- Black & Scholes (1973), "The Pricing of Options and Corporate Liabilities"
- Hull, "Options, Futures, and Other Derivatives"
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import Dict, List, Optional

import numpy as np
from scipy.stats import norm

logger = logging.getLogger(__name__)


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class OptionGreeks:
    """Option Greeks."""
    delta: float = 0.0
    gamma: float = 0.0
    theta: float = 0.0  # Per day
    vega: float = 0.0  # Per 1% change in vol
    rho: float = 0.0  # Per 1% change in rate


@dataclass
class OptionPrice:
    """Option pricing result."""
    call_price: float = 0.0
    put_price: float = 0.0
    greeks_call: OptionGreeks = field(default_factory=OptionGreeks)
    greeks_put: OptionGreeks = field(default_factory=OptionGreeks)
    implied_vol: float = 0.0
    d1: float = 0.0
    d2: float = 0.0


@dataclass
class StrategyResult:
    """Options strategy evaluation result."""
    strategy_name: str = ""
    max_profit: float = 0.0
    max_loss: float = 0.0
    breakeven_points: List[float] = field(default_factory=list)
    margin_required: float = 0.0
    legs: List[Dict] = field(default_factory=list)
    payoff_at_expiry: Optional[np.ndarray] = None
    probability_profit: float = 0.0
    risk_reward_ratio: float = 0.0


# =============================================================================
# BLACK-SCHOLES PRICING
# =============================================================================


def black_scholes_price(
    S: float,
    K: float,
    T: float,
    r: float,
    sigma: float,
    option_type: str = "call",
    q: float = 0.0,
) -> float:
    """
    Black-Scholes option price.

    Args:
        S: Underlying price
        K: Strike price
        T: Time to expiry (years)
        r: Risk-free rate (annual)
        sigma: Volatility (annual)
        option_type: 'call' or 'put'
        q: Dividend yield (annual)

    Returns:
        Option price
    """
    if T <= 0:
        if option_type == "call":
            return max(S - K, 0)
        else:
            return max(K - S, 0)

    if sigma <= 0:
        if option_type == "call":
            return max(S * np.exp(-q * T) - K * np.exp(-r * T), 0)
        else:
            return max(K * np.exp(-r * T) - S * np.exp(-q * T), 0)

    d1 = (np.log(S / K) + (r - q + 0.5 * sigma ** 2) * T) / (sigma * np.sqrt(T))
    d2 = d1 - sigma * np.sqrt(T)

    if option_type == "call":
        price = S * np.exp(-q * T) * norm.cdf(d1) - K * np.exp(-r * T) * norm.cdf(d2)
    else:
        price = K * np.exp(-r * T) * norm.cdf(-d2) - S * np.exp(-q * T) * norm.cdf(-d1)

    return float(price)


def compute_greeks(
    S: float,
    K: float,
    T: float,
    r: float,
    sigma: float,
    option_type: str = "call",
    q: float = 0.0,
) -> OptionGreeks:
    """
    Compute option Greeks.

    Returns:
        OptionGreeks with delta, gamma, theta, vega, rho
    """
    if T <= 0 or sigma <= 0:
        return OptionGreeks()

    d1 = (np.log(S / K) + (r - q + 0.5 * sigma ** 2) * T) / (sigma * np.sqrt(T))
    d2 = d1 - sigma * np.sqrt(T)

    # Delta
    if option_type == "call":
        delta = np.exp(-q * T) * norm.cdf(d1)
    else:
        delta = -np.exp(-q * T) * norm.cdf(-d1)

    # Gamma (same for call and put)
    gamma = np.exp(-q * T) * norm.pdf(d1) / (S * sigma * np.sqrt(T))

    # Theta (per day)
    if option_type == "call":
        theta = (
            -S * np.exp(-q * T) * norm.pdf(d1) * sigma / (2 * np.sqrt(T))
            - r * K * np.exp(-r * T) * norm.cdf(d2)
            + q * S * np.exp(-q * T) * norm.cdf(d1)
        ) / 365
    else:
        theta = (
            -S * np.exp(-q * T) * norm.pdf(d1) * sigma / (2 * np.sqrt(T))
            + r * K * np.exp(-r * T) * norm.cdf(-d2)
            - q * S * np.exp(-q * T) * norm.cdf(-d1)
        ) / 365

    # Vega (per 1% change in vol)
    vega = S * np.exp(-q * T) * norm.pdf(d1) * np.sqrt(T) / 100

    # Rho (per 1% change in rate)
    if option_type == "call":
        rho = K * T * np.exp(-r * T) * norm.cdf(d2) / 100
    else:
        rho = -K * T * np.exp(-r * T) * norm.cdf(-d2) / 100

    return OptionGreeks(
        delta=float(delta),
        gamma=float(gamma),
        theta=float(theta),
        vega=float(vega),
        rho=float(rho),
    )


def price_option(
    S: float,
    K: float,
    T: float,
    r: float,
    sigma: float,
    q: float = 0.0,
) -> OptionPrice:
    """
    Price both call and put options with full Greeks.

    Returns:
        OptionPrice with call/put prices and Greeks
    """
    if T <= 0:
        return OptionPrice(
            call_price=max(S - K, 0),
            put_price=max(K - S, 0),
        )

    d1 = (np.log(S / K) + (r - q + 0.5 * sigma ** 2) * T) / (sigma * np.sqrt(T))
    d2 = d1 - sigma * np.sqrt(T)

    call = black_scholes_price(S, K, T, r, sigma, "call", q)
    put = black_scholes_price(S, K, T, r, sigma, "put", q)

    greeks_call = compute_greeks(S, K, T, r, sigma, "call", q)
    greeks_put = compute_greeks(S, K, T, r, sigma, "put", q)

    return OptionPrice(
        call_price=call,
        put_price=put,
        greeks_call=greeks_call,
        greeks_put=greeks_put,
        implied_vol=sigma,
        d1=float(d1),
        d2=float(d2),
    )


# =============================================================================
# IMPLIED VOLATILITY
# =============================================================================


def implied_volatility(
    market_price: float,
    S: float,
    K: float,
    T: float,
    r: float,
    option_type: str = "call",
    q: float = 0.0,
    tol: float = 1e-6,
    max_iter: int = 100,
) -> float:
    """
    Compute implied volatility using Newton-Raphson with bisection fallback.

    Args:
        market_price: Observed option price
        S: Underlying price
        K: Strike price
        T: Time to expiry (years)
        r: Risk-free rate
        option_type: 'call' or 'put'
        q: Dividend yield
        tol: Tolerance
        max_iter: Maximum iterations

    Returns:
        Implied volatility
    """
    if market_price <= 0 or T <= 0:
        return 0.0

    # Check for arbitrage bounds
    if option_type == "call":
        intrinsic = max(S - K * np.exp(-r * T), 0)
        if market_price < intrinsic:
            return 0.0
    else:
        intrinsic = max(K * np.exp(-r * T) - S, 0)
        if market_price < intrinsic:
            return 0.0

    # Initial guess
    sigma = 0.3

    # Newton-Raphson
    for i in range(max_iter):
        price = black_scholes_price(S, K, T, r, sigma, option_type, q)
        diff = market_price - price

        if abs(diff) < tol:
            return sigma

        # Vega for Newton-Raphson
        d1 = (np.log(S / K) + (r - q + 0.5 * sigma ** 2) * T) / (sigma * np.sqrt(T))
        vega = S * np.exp(-q * T) * norm.pdf(d1) * np.sqrt(T)

        if vega < 1e-10:
            break

        sigma_new = sigma + diff / vega

        # Bounds check
        if sigma_new <= 0 or sigma_new > 5:
            break

        sigma = sigma_new

    # Bisection fallback
    lo, hi = 0.001, 5.0
    for _ in range(100):
        mid = (lo + hi) / 2
        price = black_scholes_price(S, K, T, r, mid, option_type, q)
        if abs(price - market_price) < tol:
            return mid
        if price < market_price:
            lo = mid
        else:
            hi = mid

    return float((lo + hi) / 2)


# =============================================================================
# PUT-CALL PARITY
# =============================================================================


def put_call_parity(
    S: float,
    K: float,
    T: float,
    r: float,
    call_price: float,
    put_price: float,
    q: float = 0.0,
) -> Dict:
    """
    Verify put-call parity: C - P = S*exp(-q*T) - K*exp(-r*T)

    Returns:
        Dict with parity check results
    """
    lhs = call_price - put_price
    rhs = S * np.exp(-q * T) - K * np.exp(-r * T)
    diff = lhs - rhs

    return {
        "parity_satisfied": abs(diff) < 0.01 * S,
        "lhs": float(lhs),
        "rhs": float(rhs),
        "difference": float(diff),
        "difference_pct": float(diff / S * 100),
    }


# =============================================================================
# OPTIONS STRATEGIES
# =============================================================================


def long_straddle(
    S: float,
    K: float,
    T: float,
    r: float,
    sigma: float,
    q: float = 0.0,
) -> StrategyResult:
    """
    Long Straddle: Buy call + Buy put at same strike.
    Profit when underlying moves significantly in either direction.
    """
    call = black_scholes_price(S, K, T, r, sigma, "call", q)
    put = black_scholes_price(S, K, T, r, sigma, "put", q)
    cost = call + put

    # Payoff at expiry
    prices = np.linspace(S * 0.5, S * 1.5, 100)
    payoff = np.maximum(prices - K, 0) + np.maximum(K - prices, 0) - cost

    # Probability of profit (lognormal)
    mu = np.log(S) + (r - q - 0.5 * sigma ** 2) * T
    std = sigma * np.sqrt(T)
    # Profit if |S_T - K| > cost
    upper_breakeven = K + cost
    lower_breakeven = K - cost
    prob_above = 1 - norm.cdf((np.log(upper_breakeven) - mu) / std)
    prob_below = norm.cdf((np.log(lower_breakeven) - mu) / std)
    prob_profit = prob_above + prob_below

    return StrategyResult(
        strategy_name="Long Straddle",
        max_profit=float("inf"),  # Theoretically unlimited
        max_loss=float(cost),
        breakeven_points=[float(lower_breakeven), float(upper_breakeven)],
        legs=[
            {"type": "call", "action": "buy", "strike": K, "premium": call},
            {"type": "put", "action": "buy", "strike": K, "premium": put},
        ],
        payoff_at_expiry=payoff,
        probability_profit=float(prob_profit),
        risk_reward_ratio=0.0,  # Unlimited upside
    )


def long_strangle(
    S: float,
    K1: float,
    K2: float,
    T: float,
    r: float,
    sigma: float,
    q: float = 0.0,
) -> StrategyResult:
    """
    Long Strangle: Buy OTM call + Buy OTM put.
    K1 < K2 (K1 = put strike, K2 = call strike)
    """
    put = black_scholes_price(S, K1, T, r, sigma, "put", q)
    call = black_scholes_price(S, K2, T, r, sigma, "call", q)
    cost = call + put

    prices = np.linspace(S * 0.5, S * 1.5, 100)
    payoff = np.maximum(prices - K2, 0) + np.maximum(K1 - prices, 0) - cost

    upper_breakeven = K2 + cost
    lower_breakeven = K1 - cost

    mu = np.log(S) + (r - q - 0.5 * sigma ** 2) * T
    std = sigma * np.sqrt(T)
    prob_above = 1 - norm.cdf((np.log(upper_breakeven) - mu) / std)
    prob_below = norm.cdf((np.log(lower_breakeven) - mu) / std)
    prob_profit = prob_above + prob_below

    return StrategyResult(
        strategy_name="Long Strangle",
        max_profit=float("inf"),
        max_loss=float(cost),
        breakeven_points=[float(lower_breakeven), float(upper_breakeven)],
        legs=[
            {"type": "put", "action": "buy", "strike": K1, "premium": put},
            {"type": "call", "action": "buy", "strike": K2, "premium": call},
        ],
        payoff_at_expiry=payoff,
        probability_profit=float(prob_profit),
    )


def iron_condor(
    S: float,
    K1: float,  # Lower put strike (buy)
    K2: float,  # Higher put strike (sell)
    K3: float,  # Lower call strike (sell)
    K4: float,  # Higher call strike (buy)
    T: float,
    r: float,
    sigma: float,
    q: float = 0.0,
) -> StrategyResult:
    """
    Iron Condor: Sell put spread + Sell call spread.
    Profit when underlying stays between K2 and K3.
    """
    put_buy = black_scholes_price(S, K1, T, r, sigma, "put", q)
    put_sell = black_scholes_price(S, K2, T, r, sigma, "put", q)
    call_sell = black_scholes_price(S, K3, T, r, sigma, "call", q)
    call_buy = black_scholes_price(S, K4, T, r, sigma, "call", q)

    net_credit = (put_sell - put_buy) + (call_sell - call_buy)

    max_profit = net_credit
    max_loss = min(K2 - K1, K4 - K3) - net_credit

    prices = np.linspace(S * 0.5, S * 1.5, 200)
    payoff = (
        np.maximum(K1 - prices, 0) - np.maximum(K2 - prices, 0)
        - np.maximum(prices - K4, 0) + np.maximum(prices - K3, 0)
        + net_credit
    )

    # Breakevens
    lower_be = K2 - net_credit
    upper_be = K3 + net_credit

    mu = np.log(S) + (r - q - 0.5 * sigma ** 2) * T
    std = sigma * np.sqrt(T)
    prob_profit = norm.cdf((np.log(upper_be) - mu) / std) - norm.cdf((np.log(lower_be) - mu) / std)

    return StrategyResult(
        strategy_name="Iron Condor",
        max_profit=float(max_profit),
        max_loss=float(max_loss),
        breakeven_points=[float(lower_be), float(upper_be)],
        legs=[
            {"type": "put", "action": "buy", "strike": K1, "premium": put_buy},
            {"type": "put", "action": "sell", "strike": K2, "premium": put_sell},
            {"type": "call", "action": "sell", "strike": K3, "premium": call_sell},
            {"type": "call", "action": "buy", "strike": K4, "premium": call_buy},
        ],
        payoff_at_expiry=payoff,
        probability_profit=float(prob_profit),
        risk_reward_ratio=float(max_profit / max_loss) if max_loss > 0 else 0,
    )


def covered_call(
    S: float,
    K: float,
    T: float,
    r: float,
    sigma: float,
    q: float = 0.0,
) -> StrategyResult:
    """
    Covered Call: Long stock + Short call.
    Generates income, caps upside.
    """
    call = black_scholes_price(S, K, T, r, sigma, "call", q)

    max_profit = (K - S) + call

    breakeven = S - call

    prices = np.linspace(S * 0.3, S * 1.5, 100)
    payoff = prices - S - np.maximum(prices - K, 0) + call

    mu = np.log(S) + (r - q - 0.5 * sigma ** 2) * T
    std = sigma * np.sqrt(T)
    prob_profit = 1 - norm.cdf((np.log(breakeven) - mu) / std)

    return StrategyResult(
        strategy_name="Covered Call",
        max_profit=float(max_profit),
        max_loss=float(S - call),  # Max loss if stock goes to 0
        breakeven_points=[float(breakeven)],
        legs=[
            {"type": "stock", "action": "buy", "price": S},
            {"type": "call", "action": "sell", "strike": K, "premium": call},
        ],
        payoff_at_expiry=payoff,
        probability_profit=float(prob_profit),
    )


def protective_put(
    S: float,
    K: float,
    T: float,
    r: float,
    sigma: float,
    q: float = 0.0,
) -> StrategyResult:
    """
    Protective Put: Long stock + Long put.
    Insurance against downside.
    """
    put = black_scholes_price(S, K, T, r, sigma, "put", q)

    max_profit = float("inf")
    max_loss = float((S - K) + put)
    breakeven = S + put

    prices = np.linspace(S * 0.3, S * 1.5, 100)
    payoff = prices - S + np.maximum(K - prices, 0) - put

    mu = np.log(S) + (r - q - 0.5 * sigma ** 2) * T
    std = sigma * np.sqrt(T)
    prob_profit = 1 - norm.cdf((np.log(breakeven) - mu) / std)

    return StrategyResult(
        strategy_name="Protective Put",
        max_profit=max_profit,
        max_loss=max_loss,
        breakeven_points=[float(breakeven)],
        legs=[
            {"type": "stock", "action": "buy", "price": S},
            {"type": "put", "action": "buy", "strike": K, "premium": put},
        ],
        payoff_at_expiry=payoff,
        probability_profit=float(prob_profit),
    )


# =============================================================================
# VOLATILITY ANALYSIS
# =============================================================================


def volatility_smile(
    S: float,
    strikes: List[float],
    T: float,
    r: float,
    market_prices: List[float],
    option_type: str = "call",
    q: float = 0.0,
) -> Dict:
    """
    Compute implied volatility across strikes to visualize volatility smile/skew.

    Returns:
        Dict with strikes, implied vols, and moneyness
    """
    ivs = []
    for K, price in zip(strikes, market_prices):
        iv = implied_volatility(price, S, K, T, r, option_type, q)
        ivs.append(iv)

    moneyness = [K / S for K in strikes]

    return {
        "strikes": strikes,
        "implied_vols": ivs,
        "moneyness": moneyness,
        "atm_iv": float(ivs[len(ivs) // 2]) if ivs else 0,
        "smile_slope": float(np.polyfit(moneyness, ivs, 1)[0]) if len(ivs) > 1 else 0,
    }


def run_options_analysis(
    S: float,
    K: float,
    T: float = 30 / 365,
    r: float = 0.06,
    sigma: float = 0.25,
    q: float = 0.0,
) -> Dict:
    """
    Run complete options analysis.

    Returns:
        Dict with pricing, Greeks, strategies, and volatility info
    """
    pricing = price_option(S, K, T, r, sigma, q)

    # Strategies
    straddle = long_straddle(S, K, T, r, sigma, q)
    covered = covered_call(S, K, T, r, sigma, q)
    protective = protective_put(S, K, T, r, sigma, q)

    # ATM strangle (5% OTM)
    K_put = S * 0.95
    K_call = S * 1.05
    strangle = long_strangle(S, K_put, K_call, T, r, sigma, q)

    # Iron condor
    ic = iron_condor(
        S, S * 0.90, S * 0.95, S * 1.05, S * 1.10,
        T, r, sigma, q,
    )

    return {
        "pricing": {
            "call_price": pricing.call_price,
            "put_price": pricing.put_price,
            "implied_vol": pricing.implied_vol,
            "d1": pricing.d1,
            "d2": pricing.d2,
        },
        "greeks": {
            "call": {
                "delta": pricing.greeks_call.delta,
                "gamma": pricing.greeks_call.gamma,
                "theta": pricing.greeks_call.theta,
                "vega": pricing.greeks_call.vega,
                "rho": pricing.greeks_call.rho,
            },
            "put": {
                "delta": pricing.greeks_put.delta,
                "gamma": pricing.greeks_put.gamma,
                "theta": pricing.greeks_put.theta,
                "vega": pricing.greeks_put.vega,
                "rho": pricing.greeks_put.rho,
            },
        },
        "strategies": {
            "straddle": {
                "max_profit": straddle.max_profit,
                "max_loss": straddle.max_loss,
                "breakevens": straddle.breakeven_points,
                "prob_profit": straddle.probability_profit,
            },
            "strangle": {
                "max_profit": strangle.max_profit,
                "max_loss": strangle.max_loss,
                "breakevens": strangle.breakeven_points,
                "prob_profit": strangle.probability_profit,
            },
            "iron_condor": {
                "max_profit": ic.max_profit,
                "max_loss": ic.max_loss,
                "breakevens": ic.breakeven_points,
                "prob_profit": ic.probability_profit,
                "risk_reward": ic.risk_reward_ratio,
            },
            "covered_call": {
                "max_profit": covered.max_profit,
                "max_loss": covered.max_loss,
                "breakeven": covered.breakeven_points,
                "prob_profit": covered.probability_profit,
            },
            "protective_put": {
                "max_profit": protective.max_profit,
                "max_loss": protective.max_loss,
                "breakeven": protective.breakeven_points,
                "prob_profit": protective.probability_profit,
            },
        },
        "put_call_parity": put_call_parity(S, K, T, r, pricing.call_price, pricing.put_price, q),
    }
