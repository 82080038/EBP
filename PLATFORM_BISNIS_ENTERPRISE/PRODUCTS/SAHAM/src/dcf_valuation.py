"""
DCF (Discounted Cash Flow) Valuation Module.

Implements:
- Free Cash Flow projection
- WACC calculation (CAPM-based)
- Terminal value (Gordon Growth + Exit Multiple)
- Intrinsic value per share
- Margin of safety calculation
- Reverse DCF (implied growth rate)

Based on:
- Benjamin Graham "The Intelligent Investor"
- Aswath Damodaran (NYU Stern) valuation framework
- McKinsey "Valuation" textbook

For IDX stocks: data from yfinance financials + info.
"""

import yfinance as yf
from dataclasses import dataclass, field
from typing import Optional
from .rate_limiter import get_yf_limiter


@dataclass
class DCFValuation:
    ticker: str
    current_price: Optional[float] = None
    intrinsic_value: Optional[float] = None
    margin_of_safety: Optional[float] = None  # % discount to intrinsic value
    wacc: Optional[float] = None
    terminal_value: Optional[float] = None
    fcf_projections: list = field(default_factory=list)
    assumptions: dict = field(default_factory=dict)
    valuation_status: str = ""  # "undervalued", "fair", "overvalued"
    reverse_dcf_growth: Optional[float] = None  # Growth rate implied by current price
    recommendation: str = ""
    error: Optional[str] = None


def calc_wacc(
    market_cap: float,
    total_debt: float,
    total_cash: float,
    cost_of_equity: float,
    cost_of_debt: float,
    tax_rate: float = 0.22,  # Indonesia corporate tax
) -> float:
    """Calculate Weighted Average Cost of Capital."""
    equity_value = market_cap
    debt_value = total_debt
    total_value = equity_value + debt_value

    if total_value <= 0:
        return cost_of_equity  # Fallback to just cost of equity

    weight_equity = equity_value / total_value
    weight_debt = debt_value / total_value

    wacc = weight_equity * cost_of_equity + weight_debt * cost_of_debt * (1 - tax_rate)
    return wacc


def calc_cost_of_equity(
    risk_free_rate: float = 0.04,  # ~4% (ORI/SBN yield)
    market_risk_premium: float = 0.07,  # ~7% for emerging markets
    beta: float = 1.0,
) -> float:
    """CAPM: Cost of Equity = Rf + β × (Rm - Rf)."""
    return risk_free_rate + beta * market_risk_premium


def estimate_fcf(
    operating_cash_flow: float,
    capex: float,
    growth_rate: float = 0.05,
    years: int = 5,
) -> list:
    """Project Free Cash Flow for N years."""
    base_fcf = operating_cash_flow - capex
    projections = []
    for year in range(1, years + 1):
        projected = base_fcf * (1 + growth_rate) ** year
        projections.append(projected)
    return projections


def calc_terminal_value(
    final_fcf: float,
    terminal_growth: float = 0.03,
    wacc: float = 0.10,
) -> float:
    """Gordon Growth Model terminal value."""
    if wacc <= terminal_growth:
        return final_fcf * 10  # Fallback
    return final_fcf * (1 + terminal_growth) / (wacc - terminal_growth)


def run_dcf(
    ticker: str,
    risk_free_rate: float = 0.04,
    market_risk_premium: float = 0.07,
    beta: float = 1.0,
    growth_rate: float = 0.05,
    terminal_growth: float = 0.03,
    tax_rate: float = 0.22,
    projection_years: int = 5,
) -> DCFValuation:
    """
    Run full DCF valuation for a stock.
    """
    try:
        limiter = get_yf_limiter()
        limiter.acquire()
        t = yf.Ticker(ticker)
        info = t.info
        financials = t.financials
        cashflow = t.cashflow

        current_price = info.get("currentPrice") or info.get("regularMarketPrice")
        market_cap = info.get("marketCap", 0)
        total_debt = info.get("totalDebt", 0)
        total_cash = info.get("totalCash", 0)
        shares_outstanding = info.get("sharesOutstanding", 0)

        if not current_price or not market_cap or shares_outstanding == 0:
            return DCFValuation(ticker=ticker, error="Insufficient data for DCF")

        # Get operating cash flow and capex
        ocf = None
        capex = None

        if cashflow is not None and not cashflow.empty:
            if "Operating Cash Flow" in cashflow.index:
                ocf = cashflow.loc["Operating Cash Flow"].iloc[0]
            if "Capital Expenditure" in cashflow.index:
                capex = abs(cashflow.loc["Capital Expenditure"].iloc[0])

        if ocf is None or capex is None:
            # Fallback: estimate from net income
            net_income = info.get("netIncomeToCommon", 0)
            if net_income > 0:
                ocf = net_income * 1.2  # Rough estimate
                capex = net_income * 0.3  # Rough estimate
            else:
                return DCFValuation(ticker=ticker, error="Cannot estimate cash flows")

        # Calculate WACC
        cost_of_equity = calc_cost_of_equity(risk_free_rate, market_risk_premium, beta)

        # Estimate cost of debt from interest expense
        interest_expense = 0
        if financials is not None and not financials.empty:
            if "Interest Expense" in financials.index:
                interest_expense = abs(financials.loc["Interest Expense"].iloc[0])

        cost_of_debt = (interest_expense / total_debt) if total_debt > 0 else 0.05

        wacc = calc_wacc(market_cap, total_debt, total_cash, cost_of_equity, cost_of_debt, tax_rate)
        wacc = max(0.06, min(0.20, wacc))  # Clamp to reasonable range

        # Project FCF
        fcf_projections = estimate_fcf(ocf, capex, growth_rate, projection_years)

        # Discount FCFs
        pv_fcf = []
        for i, fcf in enumerate(fcf_projections, 1):
            pv = fcf / (1 + wacc) ** i
            pv_fcf.append(pv)

        # Terminal value
        tv = calc_terminal_value(fcf_projections[-1], terminal_growth, wacc)
        pv_tv = tv / (1 + wacc) ** projection_years

        # Enterprise value
        ev = sum(pv_fcf) + pv_tv

        # Equity value = EV - Net Debt
        net_debt = total_debt - total_cash
        equity_value = ev - net_debt

        # Intrinsic value per share
        intrinsic_per_share = equity_value / shares_outstanding if shares_outstanding > 0 else 0

        # Margin of safety
        if intrinsic_per_share > 0 and current_price > 0:
            margin = (intrinsic_per_share - current_price) / current_price * 100
        else:
            margin = 0

        # Valuation status
        if margin > 30:
            status = "undervalued"
            rec = f"UNDervalued by {margin:.0f}% — Strong buy signal (margin of safety > 30%)"
        elif margin > 10:
            status = "undervalued"
            rec = f"Undervalued by {margin:.0f}% — Buy (margin of safety > 10%)"
        elif margin > -10:
            status = "fair"
            rec = "Fairly valued (within ±10% of intrinsic value)"
        elif margin > -30:
            status = "overvalued"
            rec = f"Overvalued by {-margin:.0f}% — Wait for pullback"
        else:
            status = "overvalued"
            rec = f"OVERvalued by {-margin:.0f}% — Avoid (price far exceeds intrinsic value)"

        # Reverse DCF: what growth rate is implied by current price?
        reverse_growth = _reverse_dcf_growth(
            current_price, shares_outstanding, ocf, capex,
            total_debt, total_cash, wacc, projection_years, terminal_growth
        )

        return DCFValuation(
            ticker=ticker,
            current_price=round(current_price, 2),
            intrinsic_value=round(intrinsic_per_share, 2),
            margin_of_safety=round(margin, 1),
            wacc=round(wacc * 100, 2),
            terminal_value=round(tv, 0),
            fcf_projections=[round(f, 0) for f in fcf_projections],
            assumptions={
                "risk_free_rate": risk_free_rate,
                "market_risk_premium": market_risk_premium,
                "beta": beta,
                "growth_rate": growth_rate,
                "terminal_growth": terminal_growth,
                "tax_rate": tax_rate,
                "projection_years": projection_years,
                "cost_of_equity": round(cost_of_equity * 100, 2),
                "cost_of_debt": round(cost_of_debt * 100, 2),
                "market_cap": market_cap,
                "total_debt": total_debt,
                "total_cash": total_cash,
                "shares_outstanding": shares_outstanding,
                "operating_cash_flow": ocf,
                "capex": capex,
            },
            valuation_status=status,
            reverse_dcf_growth=round(reverse_growth * 100, 1) if reverse_growth else None,
            recommendation=rec,
        )

    except Exception as e:
        return DCFValuation(ticker=ticker, error=str(e))


def _reverse_dcf_growth(
    price: float, shares: float, ocf: float, capex: float,
    debt: float, cash: float, wacc: float, years: int, terminal_growth: float
) -> Optional[float]:
    """Find growth rate implied by current market price."""
    if price <= 0 or shares <= 0 or ocf <= 0:
        return None

    base_fcf = ocf - capex
    target_equity_value = price * shares
    target_ev = target_equity_value + debt - cash

    # Binary search for growth rate
    lo, hi = -0.05, 0.30
    for _ in range(100):
        mid = (lo + hi) / 2
        projections = [base_fcf * (1 + mid) ** i for i in range(1, years + 1)]
        pv_fcf = sum(f / (1 + wacc) ** i for i, f in enumerate(projections, 1))
        tv = calc_terminal_value(projections[-1], terminal_growth, wacc)
        pv_tv = tv / (1 + wacc) ** years
        ev = pv_fcf + pv_tv

        if ev < target_ev:
            lo = mid
        else:
            hi = mid

    return (lo + hi) / 2
