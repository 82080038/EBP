"""
Investor Tools — long-term portfolio management utilities.

Features:
1. Dividend Reinvestment Simulation — DRIP compounding over time
2. Asset Allocation Model — stock/bond/cash split based on risk profile
3. Correlation Matrix — diversification analysis across tickers

Usage:
    from src.investor_tools import (
        DividendReinvestmentSim, AssetAllocationModel, CorrelationAnalyzer
    )
"""

import pandas as pd
import numpy as np
from typing import Dict, List, Optional, Tuple
from dataclasses import dataclass


# =========================================================================
# 1. DIVIDEND REINVESTMENT SIMULATION (DRIP)
# =========================================================================

@dataclass
class DRIPResult:
    """Dividend reinvestment simulation result."""
    ticker: str
    initial_shares: int
    final_shares: int
    initial_value: float
    final_value: float
    total_dividends: float
    reinvested_shares: int
    price_appreciation_pct: float
    dividend_return_pct: float
    total_return_pct: float
    years: float
    cagr: float


class DividendReinvestmentSim:
    """
    Simulate dividend reinvestment (DRIP) over time.

    Given historical price + dividend data, calculate:
    - Total return with reinvestment vs without
    - Additional shares from reinvestment
    - CAGR with and without DRIP
    """

    def simulate(
        self,
        price_df: pd.DataFrame,
        dividends: Optional[pd.DataFrame] = None,
        initial_capital: float = 10_000_000,
        ticker: str = "UNKNOWN",
        reinvest: bool = True,
    ) -> DRIPResult:
        """
        Simulate DRIP strategy.

        Args:
            price_df: DataFrame with Date index and 'Close' column
            dividends: DataFrame with 'Date' and 'Dividend' (per share) columns.
                       If None, will attempt to fetch from yfinance.
            initial_capital: Starting capital
            ticker: Ticker symbol for fetching dividends if needed
            reinvest: If True, reinvest dividends; if False, take as cash
        """
        if price_df.empty or "Close" not in price_df.columns:
            return DRIPResult(
                ticker=ticker, initial_shares=0, final_shares=0,
                initial_value=initial_capital, final_value=initial_capital,
                total_dividends=0, reinvested_shares=0,
                price_appreciation_pct=0, dividend_return_pct=0,
                total_return_pct=0, years=0, cagr=0,
            )

        # Fetch dividends if not provided
        if dividends is None:
            dividends = self._fetch_dividends(ticker)

        # Initial buy
        first_price = price_df["Close"].iloc[0]
        shares = int(initial_capital / first_price)
        cash_dividends = 0.0

        # Build dividend lookup by date
        div_lookup = {}
        if dividends is not None and not dividends.empty:
            for _, row in dividends.iterrows():
                date_key = row.name if isinstance(row.name, pd.Timestamp) else pd.to_datetime(row.get("Date", row.name))
                div_lookup[date_key] = float(row["Dividend"] if "Dividend" in row else row.iloc[0])

        # Simulate
        for date, row in price_df.iterrows():
            price = row["Close"]

            # Check for dividend on this date (or nearby)
            if reinvest and div_lookup:
                for div_date, div_amount in div_lookup.items():
                    if date == div_date or (abs((date - div_date).days) <= 3 and date not in div_lookup.get("_processed", set())):
                        div_total = div_amount * shares
                        cash_dividends += div_total
                        # Reinvest at current price
                        new_shares = int(div_total / price)
                        shares += new_shares
                        break

        final_price = price_df["Close"].iloc[-1]
        final_value = shares * final_price
        initial_value = initial_capital

        # Without reinvestment for comparison
        initial_shares = int(initial_capital / first_price)
        initial_shares * final_price + cash_dividends

        price_app = ((final_price - first_price) / first_price) * 100
        div_return = (cash_dividends / initial_value) * 100 if initial_value > 0 else 0
        total_return = ((final_value - initial_value) / initial_value) * 100

        # Years
        if isinstance(price_df.index, pd.DatetimeIndex) and len(price_df) > 1:
            years = (price_df.index[-1] - price_df.index[0]).days / 365.25
        else:
            years = len(price_df) / 252

        cagr = ((final_value / initial_value) ** (1 / years) - 1) * 100 if years > 0 and initial_value > 0 else 0

        return DRIPResult(
            ticker=ticker,
            initial_shares=initial_shares,
            final_shares=shares,
            initial_value=initial_value,
            final_value=final_value,
            total_dividends=cash_dividends,
            reinvested_shares=shares - initial_shares,
            price_appreciation_pct=round(price_app, 2),
            dividend_return_pct=round(div_return, 2),
            total_return_pct=round(total_return, 2),
            years=round(years, 2),
            cagr=round(cagr, 2),
        )

    def _fetch_dividends(self, ticker: str) -> Optional[pd.DataFrame]:
        """Fetch dividend history from yfinance."""
        try:
            import yfinance as yf
            stock = yf.Ticker(ticker)
            divs = stock.dividends
            if divs is not None and not divs.empty:
                divs = divs.to_frame("Dividend")
                return divs
        except Exception:
            pass
        return None


# =========================================================================
# 2. ASSET ALLOCATION MODEL
# =========================================================================

@dataclass
class AssetAllocation:
    """Recommended asset allocation."""
    stocks_pct: float
    bonds_pct: float
    cash_pct: float
    commodities_pct: float
    crypto_pct: float
    risk_profile: str
    expected_return: float
    expected_volatility: float
    max_drawdown_estimate: float
    rationale: str


class AssetAllocationModel:
    """
    Stock/Bond/Cash allocation model based on investor risk profile.

    Profiles:
    - Conservative: Capital preservation, low risk tolerance
    - Moderate: Balanced growth and income
    - Aggressive: Maximum growth, high risk tolerance
    - Custom: Based on age-based rule (110 - age = stock %)
    """

    PROFILES = {
        "conservative": {
            "stocks": 30, "bonds": 50, "cash": 15, "commodities": 5, "crypto": 0,
            "expected_return": 5.0, "expected_vol": 8.0, "max_dd": 15.0,
        },
        "moderate": {
            "stocks": 55, "bonds": 30, "cash": 10, "commodities": 4, "crypto": 1,
            "expected_return": 8.0, "expected_vol": 12.0, "max_dd": 25.0,
        },
        "aggressive": {
            "stocks": 75, "bonds": 15, "cash": 5, "commodities": 3, "crypto": 2,
            "expected_return": 12.0, "expected_vol": 20.0, "max_dd": 40.0,
        },
    }

    def recommend(
        self,
        risk_profile: str = "moderate",
        age: Optional[int] = None,
        capital: float = 100_000_000,
        risk_tolerance_score: Optional[float] = None,
    ) -> AssetAllocation:
        """
        Recommend asset allocation.

        Args:
            risk_profile: "conservative", "moderate", "aggressive", or "custom"
            age: Investor age (for age-based allocation if profile is "custom")
            capital: Total investable capital
            risk_tolerance_score: 0-100 score from questionnaire (overrides profile)
        """
        # If risk tolerance score provided, map to profile
        if risk_tolerance_score is not None:
            if risk_tolerance_score < 30:
                risk_profile = "conservative"
            elif risk_tolerance_score < 70:
                risk_profile = "moderate"
            else:
                risk_profile = "aggressive"

        if risk_profile == "custom" and age is not None:
            stock_pct = max(20, min(90, 110 - age))
            bond_pct = max(5, min(60, 100 - stock_pct - 10))
            cash_pct = max(5, 100 - stock_pct - bond_pct - 5)
            commodities_pct = 4
            crypto_pct = max(0, 100 - stock_pct - bond_pct - cash_pct - commodities_pct)
            expected_return = 5 + stock_pct / 100 * 8
            expected_vol = 5 + stock_pct / 100 * 18
            max_dd = 10 + stock_pct / 100 * 35
        else:
            profile = self.PROFILES.get(risk_profile, self.PROFILES["moderate"])
            stock_pct = profile["stocks"]
            bond_pct = profile["bonds"]
            cash_pct = profile["cash"]
            commodities_pct = profile["commodities"]
            crypto_pct = profile["crypto"]
            expected_return = profile["expected_return"]
            expected_vol = profile["expected_vol"]
            max_dd = profile["max_dd"]

        rationale = self._build_rationale(risk_profile, stock_pct, bond_pct, cash_pct, capital)

        return AssetAllocation(
            stocks_pct=stock_pct,
            bonds_pct=bond_pct,
            cash_pct=cash_pct,
            commodities_pct=commodities_pct,
            crypto_pct=crypto_pct,
            risk_profile=risk_profile,
            expected_return=expected_return,
            expected_volatility=expected_vol,
            max_drawdown_estimate=max_dd,
            rationale=rationale,
        )

    def _build_rationale(self, profile: str, stocks: float, bonds: float, cash: float, capital: float) -> str:
        stock_val = capital * stocks / 100
        bond_val = capital * bonds / 100
        cash_val = capital * cash / 100
        return (
            f"Profil: {profile}. "
            f"Saham: {stocks}% (Rp {stock_val:,.0f}) — growth engine. "
            f"Obligasi: {bonds}% (Rp {bond_val:,.0f}) — stability & income. "
            f"Kas: {cash}% (Rp {cash_val:,.0f}) — emergency & opportunity fund. "
            f"Rebalance tahunan jika drift > 5%."
        )


# =========================================================================
# 3. CORRELATION MATRIX & DIVERSIFICATION
# =========================================================================

@dataclass
class DiversificationReport:
    """Diversification analysis result."""
    correlation_matrix: pd.DataFrame
    avg_correlation: float
    max_correlation: Tuple[str, str, float]
    min_correlation: Tuple[str, str, float]
    diversification_ratio: float
    recommendation: str
    clusters: Dict[str, List[str]]


class CorrelationAnalyzer:
    """
    Analyze correlation between tickers for optimal diversification.

    Features:
    - Correlation matrix (Pearson, Spearman)
    - Identify highly correlated pairs (redundancy)
    - Identify uncorrelated pairs (diversification benefit)
    - Cluster tickers by correlation
    - Calculate diversification ratio
    """

    def analyze(
        self,
        market_data: Dict[str, pd.DataFrame],
        method: str = "pearson",
        lookback_days: int = 60,
    ) -> DiversificationReport:
        """
        Analyze correlations across multiple tickers.

        Args:
            market_data: Dict of ticker → DataFrame with 'Close' column
            method: "pearson" or "spearman"
            lookback_days: Use last N days for correlation calculation
        """
        returns_dict = {}
        for ticker, df in market_data.items():
            if df.empty or "Close" not in df.columns:
                continue
            ret = df["Close"].pct_change().dropna().tail(lookback_days)
            if len(ret) > 10:
                returns_dict[ticker] = ret

        if len(returns_dict) < 2:
            return DiversificationReport(
                correlation_matrix=pd.DataFrame(),
                avg_correlation=0,
                max_correlation=("", "", 0),
                min_correlation=("", "", 0),
                diversification_ratio=1.0,
                recommendation="Insufficient data for correlation analysis",
                clusters={},
            )

        returns_df = pd.DataFrame(returns_dict)
        corr_matrix = returns_df.corr(method=method)

        # Extract upper triangle (exclude diagonal)
        tickers = list(corr_matrix.columns)
        pairs = []
        for i in range(len(tickers)):
            for j in range(i + 1, len(tickers)):
                pairs.append((tickers[i], tickers[j], corr_matrix.iloc[i, j]))

        if not pairs:
            return DiversificationReport(
                correlation_matrix=corr_matrix,
                avg_correlation=0,
                max_correlation=("", "", 0),
                min_correlation=("", "", 0),
                diversification_ratio=1.0,
                recommendation="Only one ticker — no diversification possible",
                clusters={},
            )

        # Find max and min correlation pairs
        max_pair = max(pairs, key=lambda x: abs(x[2]))
        min_pair = min(pairs, key=lambda x: abs(x[2]))

        # Average correlation (absolute)
        avg_corr = np.mean([abs(p[2]) for p in pairs])

        # Diversification ratio: 1 - avg_correlation (1 = perfect diversification, 0 = no diversification)
        div_ratio = 1 - avg_corr

        # Simple clustering: group tickers with correlation > 0.7
        clusters = self._cluster_tickers(corr_matrix, threshold=0.7)

        # Recommendation
        high_corr_pairs = [p for p in pairs if abs(p[2]) > 0.7]
        if high_corr_pairs:
            rec = (
                f"⚠️ {len(high_corr_pairs)} pasangan berkorelasi tinggi (>0.7): "
                + ", ".join(f"{p[0]}-{p[1]}({p[2]:.2f})" for p in high_corr_pairs[:5])
                + ". Pertimbangkan untuk mengurangi posisi yang redundant."
            )
        elif avg_corr < 0.3:
            rec = f"✅ Diversifikasi baik (avg corr: {avg_corr:.2f}). Portfolio terdistribusi dengan baik."
        else:
            rec = f"📊 Diversifikasi moderat (avg corr: {avg_corr:.2f}). Beberapa korelasi sedang."

        return DiversificationReport(
            correlation_matrix=corr_matrix,
            avg_correlation=round(avg_corr, 4),
            max_correlation=(max_pair[0], max_pair[1], round(max_pair[2], 4)),
            min_correlation=(min_pair[0], min_pair[1], round(min_pair[2], 4)),
            diversification_ratio=round(div_ratio, 4),
            recommendation=rec,
            clusters=clusters,
        )

    def _cluster_tickers(self, corr_matrix: pd.DataFrame, threshold: float = 0.7) -> Dict[str, List[str]]:
        """Group tickers into clusters based on correlation threshold."""
        tickers = list(corr_matrix.columns)
        visited = set()
        clusters = {}

        for ticker in tickers:
            if ticker in visited:
                continue
            cluster = [ticker]
            visited.add(ticker)
            for other in tickers:
                if other in visited:
                    continue
                if abs(corr_matrix.loc[ticker, other]) > threshold:
                    cluster.append(other)
                    visited.add(other)
            clusters[f"Cluster_{len(clusters) + 1}"] = cluster

        return clusters
