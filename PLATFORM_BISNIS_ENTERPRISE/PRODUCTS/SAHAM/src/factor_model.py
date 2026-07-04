"""
Factor Model Module — Fama-French & Carhart.

Implements:
- CAPM (1-factor)
- Fama-French 3-Factor (Market, Size, Value)
- Fama-French 5-Factor (+ Profitability, Investment)
- Carhart 4-Factor (+ Momentum)

Factor definitions:
- MKT: Market excess return (Rm - Rf)
- SMB: Small Minus Big (size factor)
- HML: High Minus Low (value factor — book-to-market)
- RMW: Robust Minus Weak (profitability factor)
- CMA: Conservative Minus Aggressive (investment factor)
- MOM: Momentum (12-1 month returns)

For IDX: factors are approximated using available data.
"""

import pandas as pd
from dataclasses import dataclass, field
import statsmodels.api as sm


@dataclass
class FactorExposure:
    alpha: float  # Excess return not explained by factors
    beta_market: float  # Market beta
    beta_size: float = 0.0  # SMB loading
    beta_value: float = 0.0  # HML loading
    beta_momentum: float = 0.0  # MOM loading
    beta_profitability: float = 0.0  # RMW loading
    beta_investment: float = 0.0  # CMA loading
    r_squared: float = 0.0
    factor_returns: dict = field(default_factory=dict)
    interpretation: str = ""


def calc_market_factor(returns: pd.Series, risk_free_rate: float = 0.0) -> pd.Series:
    """Market excess return (MKT factor)."""
    return returns - risk_free_rate


def calc_size_factor(small_returns: pd.Series, big_returns: pd.Series) -> pd.Series:
    """SMB: Small Minus Big."""
    return small_returns - big_returns


def calc_value_factor(high_bm_returns: pd.Series, low_bm_returns: pd.Series) -> pd.Series:
    """HML: High Minus Low (book-to-market)."""
    return high_bm_returns - low_bm_returns


def calc_momentum_factor(winners: pd.Series, losers: pd.Series) -> pd.Series:
    """MOM: Winners Minus Losers (12-1 month momentum)."""
    return winners - losers


def run_capm_regression(asset_returns: pd.Series, market_returns: pd.Series,
                        risk_free_rate: float = 0.0) -> FactorExposure:
    """CAPM single-factor regression."""
    excess_asset = asset_returns - risk_free_rate
    excess_market = market_returns - risk_free_rate

    X = sm.add_constant(excess_market)
    model = sm.OLS(excess_asset, X).fit()

    alpha = model.params.iloc[0]
    beta = model.params.iloc[1]
    r_sq = model.rsquared

    interpretation = f"Beta={beta:.2f} ({'aggressive' if beta > 1.2 else 'defensive' if beta < 0.8 else 'market-like'}), "
    interpretation += f"Alpha={alpha*252*100:.2f}% annualized, R²={r_sq:.2f}"

    return FactorExposure(
        alpha=alpha,
        beta_market=beta,
        r_squared=r_sq,
        factor_returns={"MKT": beta},
        interpretation=interpretation,
    )


def run_fama_french_3factor(
    asset_returns: pd.Series,
    market_returns: pd.Series,
    smb: pd.Series,
    hml: pd.Series,
    risk_free_rate: float = 0.0,
) -> FactorExposure:
    """Fama-French 3-Factor regression."""
    excess_asset = asset_returns - risk_free_rate

    factors = pd.DataFrame({
        "MKT": market_returns - risk_free_rate,
        "SMB": smb,
        "HML": hml,
    }).dropna()

    excess_asset = excess_asset.reindex(factors.index).dropna()
    common_idx = factors.index.intersection(excess_asset.index)
    factors = factors.loc[common_idx]
    excess_asset = excess_asset.loc[common_idx]

    if len(common_idx) < 30:
        return FactorExposure(alpha=0, beta_market=0, interpretation="Insufficient data")

    X = sm.add_constant(factors)
    model = sm.OLS(excess_asset, X).fit()

    alpha = model.params.iloc[0]
    betas = model.params.iloc[1:]
    r_sq = model.rsquared

    interpretation = f"Beta(MKT)={betas.iloc[0]:.2f}, "
    interpretation += f"Beta(SMB)={betas.iloc[1]:.2f} ({'small-cap tilt' if betas.iloc[1] > 0.2 else 'large-cap tilt' if betas.iloc[1] < -0.2 else 'neutral'}), "
    interpretation += f"Beta(HML)={betas.iloc[2]:.2f} ({'value tilt' if betas.iloc[2] > 0.2 else 'growth tilt' if betas.iloc[2] < -0.2 else 'neutral'}), "
    interpretation += f"Alpha={alpha*252*100:.2f}% ann, R²={r_sq:.2f}"

    return FactorExposure(
        alpha=alpha,
        beta_market=betas.iloc[0],
        beta_size=betas.iloc[1],
        beta_value=betas.iloc[2],
        r_squared=r_sq,
        factor_returns={"MKT": betas.iloc[0], "SMB": betas.iloc[1], "HML": betas.iloc[2]},
        interpretation=interpretation,
    )


def run_carhart_4factor(
    asset_returns: pd.Series,
    market_returns: pd.Series,
    smb: pd.Series,
    hml: pd.Series,
    mom: pd.Series,
    risk_free_rate: float = 0.0,
) -> FactorExposure:
    """Carhart 4-Factor regression (FF3 + Momentum)."""
    excess_asset = asset_returns - risk_free_rate

    factors = pd.DataFrame({
        "MKT": market_returns - risk_free_rate,
        "SMB": smb,
        "HML": hml,
        "MOM": mom,
    }).dropna()

    excess_asset = excess_asset.reindex(factors.index).dropna()
    common_idx = factors.index.intersection(excess_asset.index)
    factors = factors.loc[common_idx]
    excess_asset = excess_asset.loc[common_idx]

    if len(common_idx) < 30:
        return FactorExposure(alpha=0, beta_market=0, interpretation="Insufficient data")

    X = sm.add_constant(factors)
    model = sm.OLS(excess_asset, X).fit()

    alpha = model.params.iloc[0]
    betas = model.params.iloc[1:]
    r_sq = model.rsquared

    interpretation = f"Beta(MKT)={betas.iloc[0]:.2f}, "
    interpretation += f"Beta(SMB)={betas.iloc[1]:.2f}, "
    interpretation += f"Beta(HML)={betas.iloc[2]:.2f}, "
    interpretation += f"Beta(MOM)={betas.iloc[3]:.2f} ({'momentum' if betas.iloc[3] > 0.2 else 'reversal' if betas.iloc[3] < -0.2 else 'neutral'}), "
    interpretation += f"Alpha={alpha*252*100:.2f}% ann, R²={r_sq:.2f}"

    return FactorExposure(
        alpha=alpha,
        beta_market=betas.iloc[0],
        beta_size=betas.iloc[1],
        beta_value=betas.iloc[2],
        beta_momentum=betas.iloc[3],
        r_squared=r_sq,
        factor_returns={"MKT": betas.iloc[0], "SMB": betas.iloc[1], "HML": betas.iloc[2], "MOM": betas.iloc[3]},
        interpretation=interpretation,
    )


def calc_factor_score(exposure: FactorExposure) -> dict:
    """
    Convert factor exposures to an investable score.
    Positive alpha + good factor tilts = higher score.
    """
    score = 50  # Base

    # Alpha contribution (annualized)
    alpha_annual = exposure.alpha * 252 * 100
    score += min(20, max(-20, alpha_annual))

    # Value tilt (HML > 0 is historically rewarded)
    if exposure.beta_value > 0.2:
        score += 10
    elif exposure.beta_value < -0.2:
        score -= 5

    # Momentum (MOM > 0 is rewarded short-term)
    if exposure.beta_momentum > 0.2:
        score += 10
    elif exposure.beta_momentum < -0.2:
        score -= 10

    # Size (SMB > 0 small-cap, historically rewarded long-term)
    if exposure.beta_size > 0.2:
        score += 5

    # R² (higher = more explained = less idiosyncratic risk)
    if exposure.r_squared > 0.6:
        score += 5
    elif exposure.r_squared < 0.2:
        score -= 5

    score = max(0, min(100, score))

    if score >= 70:
        rating = "Strong Factor Score"
    elif score >= 55:
        rating = "Good Factor Score"
    elif score >= 45:
        rating = "Neutral Factor Score"
    else:
        rating = "Weak Factor Score"

    return {
        "score": round(score, 1),
        "rating": rating,
        "alpha_annualized_pct": round(alpha_annual, 2),
        "factor_tilts": {
            "market_beta": round(exposure.beta_market, 3),
            "size_smb": round(exposure.beta_size, 3),
            "value_hml": round(exposure.beta_value, 3),
            "momentum_mom": round(exposure.beta_momentum, 3),
        },
        "r_squared": round(exposure.r_squared, 3),
    }
