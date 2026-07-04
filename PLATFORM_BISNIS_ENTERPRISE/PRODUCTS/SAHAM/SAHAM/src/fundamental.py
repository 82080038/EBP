"""
Fundamental Analysis Module.

Analisa fundamental untuk saham IDX (Bursa Efek Indonesia):
- P/E Ratio (PER), PBV (Price-to-Book Value)
- ROE, ROA, ROIC
- DER (Debt-to-Equity), Current Ratio
- EPS, Dividend Yield, Market Cap
- Revenue Growth, Profit Growth, Net Profit Margin
- Bank-specific: NPL, CAR, BOPO, LDR (jika tersedia)
- Free Cash Flow

Data source: yfinance (ticker.info, financials, balance_sheet)

Scoring: Composite fundamental score (0-100) berdasarkan:
- Valuation (PER, PBV) — cheaper is better
- Profitability (ROE, margin) — higher is better
- Growth (revenue, earnings) — higher is better
- Financial health (DER, current ratio) — lower debt is better
- Dividend (yield) — higher is better

Adopted from: Benjamin Graham "The Intelligent Investor",
Peter Lynch "One Up On Wall Street", IDX fundamental analysis standards.
"""

import yfinance as yf
import pandas as pd
from typing import Dict, Optional
from dataclasses import dataclass
from datetime import datetime
import time
from .rate_limiter import get_yf_limiter


@dataclass
class FundamentalData:
    """Fundamental data for a stock."""
    ticker: str
    # Valuation
    pe_ratio: Optional[float] = None
    forward_pe: Optional[float] = None
    pbv: Optional[float] = None
    peg_ratio: Optional[float] = None
    # Profitability
    roe: Optional[float] = None  # Return on Equity
    roa: Optional[float] = None  # Return on Assets
    profit_margin: Optional[float] = None
    operating_margin: Optional[float] = None
    # Growth
    revenue_growth: Optional[float] = None
    earnings_growth: Optional[float] = None
    # Financial Health
    debt_to_equity: Optional[float] = None
    current_ratio: Optional[float] = None
    quick_ratio: Optional[float] = None
    total_cash: Optional[float] = None
    total_debt: Optional[float] = None
    # Dividend
    dividend_yield: Optional[float] = None
    payout_ratio: Optional[float] = None
    # Market
    market_cap: Optional[float] = None
    enterprise_value: Optional[float] = None
    # EPS
    eps_trailing: Optional[float] = None
    eps_forward: Optional[float] = None
    # Bank-specific (if available)
    npl: Optional[float] = None  # Non-Performing Loan
    car: Optional[float] = None  # Capital Adequacy Ratio
    bopo: Optional[float] = None  # Biaya Operasional / Pendapatan Operasional
    ldr: Optional[float] = None  # Loan-to-Deposit Ratio
    # Metadata
    sector: Optional[str] = None
    industry: Optional[str] = None
    fetch_time: Optional[str] = None
    error: Optional[str] = None


def fetch_fundamental_data(ticker: str, retries: int = 2) -> FundamentalData:
    """
    Fetch fundamental data from yfinance for a stock.

    Args:
        ticker: Stock ticker (e.g., "BBCA.JK", "TLKM.JK")
        retries: Number of retry attempts
    """
    fd = FundamentalData(ticker=ticker)

    for attempt in range(retries):
        try:
            limiter = get_yf_limiter()
            limiter.acquire()
            t = yf.Ticker(ticker)
            info = t.info

            fd.pe_ratio = info.get("trailingPE")
            fd.forward_pe = info.get("forwardPE")
            fd.pbv = info.get("priceToBook")
            fd.peg_ratio = info.get("pegRatio")
            fd.roe = info.get("returnOnEquity")
            fd.roa = info.get("returnOnAssets")
            fd.profit_margin = info.get("profitMargins")
            fd.operating_margin = info.get("operatingMargins")
            fd.revenue_growth = info.get("revenueGrowth")
            fd.earnings_growth = info.get("earningsGrowth")
            fd.debt_to_equity = info.get("debtToEquity")
            fd.current_ratio = info.get("currentRatio")
            fd.quick_ratio = info.get("quickRatio")
            fd.total_cash = info.get("totalCash")
            fd.total_debt = info.get("totalDebt")
            fd.dividend_yield = info.get("dividendYield")
            fd.payout_ratio = info.get("payoutRatio")
            fd.market_cap = info.get("marketCap")
            fd.enterprise_value = info.get("enterpriseValue")
            fd.eps_trailing = info.get("trailingEps")
            fd.eps_forward = info.get("forwardEps")
            fd.sector = info.get("sector")
            fd.industry = info.get("industry")
            fd.fetch_time = datetime.now().isoformat()

            # Try to compute bank-specific metrics from financials
            try:
                financials = t.financials
                t.balance_sheet
                if financials is not None and not financials.empty:
                    # BOPO approximation: operating expenses / operating income
                    if "Operating Expense" in financials.index and "Operating Income" in financials.index:
                        op_exp = financials.loc["Operating Expense"].iloc[0]
                        op_inc = financials.loc["Operating Income"].iloc[0]
                        if op_inc and op_inc != 0:
                            fd.bopo = float(op_exp / op_inc)

                    # NPL approximation: provision for loan losses / total loans
                    if "Provision For Loan Losses" in financials.index:
                        prov = financials.loc["Provision For Loan Losses"].iloc[0]
                        # This is a rough proxy
                        if fd.total_debt and fd.total_debt != 0:
                            fd.npl = float(abs(prov) / fd.total_debt) if prov else None
            except Exception:
                pass  # Bank-specific metrics not available

            return fd

        except Exception as e:
            if attempt < retries - 1:
                time.sleep(1)
            else:
                fd.error = str(e)
                return fd

    return fd


def calc_fundamental_score(fd: FundamentalData) -> dict:
    """
    Calculate composite fundamental score (0-100).

    Scoring breakdown:
    - Valuation (25 pts): PER, PBV, PEG
    - Profitability (25 pts): ROE, margin
    - Growth (20 pts): revenue, earnings growth
    - Financial Health (20 pts): DER, current ratio
    - Dividend (10 pts): yield, payout
    """
    scores = {}
    details = {}

    # 1. Valuation Score (0-25)
    val_score = 0
    if fd.pe_ratio is not None and fd.pe_ratio > 0:
        if fd.pe_ratio < 10:
            val_score += 10  # Very cheap
        elif fd.pe_ratio < 15:
            val_score += 8
        elif fd.pe_ratio < 20:
            val_score += 5
        elif fd.pe_ratio < 30:
            val_score += 2
        details["pe_assessment"] = f"PER {fd.pe_ratio:.1f} — {'cheap' if fd.pe_ratio < 15 else 'fair' if fd.pe_ratio < 25 else 'expensive'}"

    if fd.pbv is not None and fd.pbv > 0:
        if fd.pbv < 1:
            val_score += 10  # Below book value
        elif fd.pbv < 2:
            val_score += 7
        elif fd.pbv < 3:
            val_score += 4
        elif fd.pbv < 5:
            val_score += 2
        details["pbv_assessment"] = f"PBV {fd.pbv:.2f} — {'cheap' if fd.pbv < 1.5 else 'fair' if fd.pbv < 3 else 'expensive'}"

    if fd.peg_ratio is not None and fd.peg_ratio > 0:
        if fd.peg_ratio < 1:
            val_score += 5  # PEG < 1 = undervalued
        elif fd.peg_ratio < 2:
            val_score += 3
        details["peg_assessment"] = f"PEG {fd.peg_ratio:.2f} — {'undervalued' if fd.peg_ratio < 1 else 'fair' if fd.peg_ratio < 2 else 'overvalued'}"

    val_score = min(val_score, 25)
    scores["valuation"] = val_score

    # 2. Profitability Score (0-25)
    prof_score = 0
    if fd.roe is not None:
        if fd.roe > 0.20:
            prof_score += 12  # Excellent ROE
        elif fd.roe > 0.15:
            prof_score += 9
        elif fd.roe > 0.10:
            prof_score += 6
        elif fd.roe > 0.05:
            prof_score += 3
        details["roe_assessment"] = f"ROE {fd.roe*100:.1f}% — {'excellent' if fd.roe > 0.2 else 'good' if fd.roe > 0.15 else 'fair' if fd.roe > 0.1 else 'weak'}"

    if fd.profit_margin is not None:
        if fd.profit_margin > 0.20:
            prof_score += 8
        elif fd.profit_margin > 0.10:
            prof_score += 5
        elif fd.profit_margin > 0.05:
            prof_score += 3
        details["margin_assessment"] = f"Net margin {fd.profit_margin*100:.1f}%"

    if fd.operating_margin is not None:
        if fd.operating_margin > 0.30:
            prof_score += 5
        elif fd.operating_margin > 0.15:
            prof_score += 3
        elif fd.operating_margin > 0.05:
            prof_score += 1

    prof_score = min(prof_score, 25)
    scores["profitability"] = prof_score

    # 3. Growth Score (0-20)
    growth_score = 0
    if fd.revenue_growth is not None:
        if fd.revenue_growth > 0.20:
            growth_score += 10
        elif fd.revenue_growth > 0.10:
            growth_score += 7
        elif fd.revenue_growth > 0.05:
            growth_score += 4
        elif fd.revenue_growth > 0:
            growth_score += 2
        details["revenue_growth"] = f"Revenue growth {fd.revenue_growth*100:.1f}%"

    if fd.earnings_growth is not None:
        if fd.earnings_growth > 0.20:
            growth_score += 10
        elif fd.earnings_growth > 0.10:
            growth_score += 7
        elif fd.earnings_growth > 0.05:
            growth_score += 4
        elif fd.earnings_growth > 0:
            growth_score += 2
        details["earnings_growth"] = f"Earnings growth {fd.earnings_growth*100:.1f}%"

    growth_score = min(growth_score, 20)
    scores["growth"] = growth_score

    # 4. Financial Health Score (0-20)
    health_score = 0
    if fd.debt_to_equity is not None:
        if fd.debt_to_equity < 0.5:
            health_score += 10
        elif fd.debt_to_equity < 1.0:
            health_score += 7
        elif fd.debt_to_equity < 2.0:
            health_score += 4
        elif fd.debt_to_equity < 3.0:
            health_score += 2
        details["der_assessment"] = f"DER {fd.debt_to_equity:.2f} — {'healthy' if fd.debt_to_equity < 1 else 'moderate' if fd.debt_to_equity < 2 else 'high'}"

    if fd.current_ratio is not None:
        if fd.current_ratio > 2.0:
            health_score += 6
        elif fd.current_ratio > 1.5:
            health_score += 4
        elif fd.current_ratio > 1.0:
            health_score += 2
        details["current_ratio"] = f"Current ratio {fd.current_ratio:.2f}"

    # Cash vs Debt
    if fd.total_cash is not None and fd.total_debt is not None and fd.total_debt > 0:
        cash_debt_ratio = fd.total_cash / fd.total_debt
        if cash_debt_ratio > 1.0:
            health_score += 4  # More cash than debt
        elif cash_debt_ratio > 0.5:
            health_score += 2
        details["cash_debt"] = f"Cash/Debt {cash_debt_ratio:.2f}"

    health_score = min(health_score, 20)
    scores["financial_health"] = health_score

    # 5. Dividend Score (0-10)
    div_score = 0
    # yfinance returns dividend_yield as percentage (e.g., 5.65 = 5.65%)
    div_yield_pct = fd.dividend_yield
    if div_yield_pct is not None and div_yield_pct > 0:
        if div_yield_pct > 6:
            div_score += 7  # High yield
        elif div_yield_pct > 3:
            div_score += 5
        elif div_yield_pct > 1:
            div_score += 3
        elif div_yield_pct > 0:
            div_score += 1
        details["dividend_yield"] = f"Dividend yield {div_yield_pct:.2f}%"

    if fd.payout_ratio is not None:
        # yfinance returns payout_ratio as percentage too
        payout_pct = fd.payout_ratio if fd.payout_ratio > 1 else fd.payout_ratio * 100
        if 20 < payout_pct < 70:
            div_score += 3  # Sustainable payout
        elif payout_pct < 90:
            div_score += 1
        details["payout_ratio"] = f"Payout ratio {payout_pct:.1f}%"

    div_score = min(div_score, 10)
    scores["dividend"] = div_score

    # Total Score
    total = sum(scores.values())

    # Grade
    if total >= 80:
        grade = "A"
        rating = "Strong Buy"
    elif total >= 65:
        grade = "B"
        rating = "Buy"
    elif total >= 50:
        grade = "C"
        rating = "Hold"
    elif total >= 35:
        grade = "D"
        rating = "Reduce"
    else:
        grade = "F"
        rating = "Sell"

    return {
        "total_score": round(total, 1),
        "grade": grade,
        "rating": rating,
        "scores": scores,
        "details": details,
        "fundamental_data": {
            "pe_ratio": fd.pe_ratio,
            "forward_pe": fd.forward_pe,
            "pbv": fd.pbv,
            "peg_ratio": fd.peg_ratio,
            "roe": fd.roe,
            "roa": fd.roa,
            "profit_margin": fd.profit_margin,
            "operating_margin": fd.operating_margin,
            "revenue_growth": fd.revenue_growth,
            "earnings_growth": fd.earnings_growth,
            "debt_to_equity": fd.debt_to_equity,
            "current_ratio": fd.current_ratio,
            "dividend_yield": fd.dividend_yield,
            "payout_ratio": fd.payout_ratio,
            "market_cap": fd.market_cap,
            "eps_trailing": fd.eps_trailing,
            "eps_forward": fd.eps_forward,
            "sector": fd.sector,
            "industry": fd.industry,
            "npl": fd.npl,
            "bopo": fd.bopo,
        },
    }


def fetch_fundamental_batch(tickers: list, delay: float = 0.5) -> Dict[str, dict]:
    """
    Fetch fundamental data for multiple tickers.
    Returns dict of ticker -> fundamental analysis result.
    """
    results = {}
    print(f"\n{'='*60}")
    print(f"MENGAMBIL DATA FUNDAMENTAL UNTUK {len(tickers)} SAHAM")
    print(f"{'='*60}")

    for ticker in tickers:
        print(f"  [{ticker}] Fetching...", end=" ")
        fd = fetch_fundamental_data(ticker)
        if fd.error:
            print(f"ERROR: {fd.error}")
            results[ticker] = {"error": fd.error}
        else:
            score = calc_fundamental_score(fd)
            print(f"Score: {score['total_score']}/100 ({score['grade']} — {score['rating']})")
            results[ticker] = score

        time.sleep(delay)  # Rate limiting

    return results


def fundamental_summary_table(results: Dict[str, dict]) -> pd.DataFrame:
    """
    Create a summary DataFrame from fundamental analysis results.
    """
    rows = []
    for ticker, result in results.items():
        if "error" in result:
            rows.append({"Ticker": ticker, "Score": 0, "Grade": "N/A", "Rating": "Error"})
            continue

        fd = result.get("fundamental_data", {})
        rows.append({
            "Ticker": ticker,
            "Score": result["total_score"],
            "Grade": result["grade"],
            "Rating": result["rating"],
            "PER": round(fd.get("pe_ratio", 0), 1) if fd.get("pe_ratio") else None,
            "PBV": round(fd.get("pbv", 0), 2) if fd.get("pbv") else None,
            "ROE%": round(fd.get("roe", 0) * 100, 1) if fd.get("roe") else None,
            "Margin%": round(fd.get("profit_margin", 0) * 100, 1) if fd.get("profit_margin") else None,
            "RevGrowth%": round(fd.get("revenue_growth", 0) * 100, 1) if fd.get("revenue_growth") else None,
            "DivYield%": round(fd.get("dividend_yield", 0) * 100, 2) if fd.get("dividend_yield") else None,
            "DER": round(fd.get("debt_to_equity", 0), 2) if fd.get("debt_to_equity") else None,
            "Sector": fd.get("sector", "N/A"),
        })

    df = pd.DataFrame(rows)
    if not df.empty:
        df = df.sort_values("Score", ascending=False)
    return df
