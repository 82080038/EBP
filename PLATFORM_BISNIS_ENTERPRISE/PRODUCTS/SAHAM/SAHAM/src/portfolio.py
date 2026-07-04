"""
Modul Portfolio Optimization
Modern Portfolio Theory: Efficient Frontier, Sharpe-optimal portfolio, diversifikasi

Methods:
1. Markowitz (Mean-Variance Optimization) — Monte Carlo + scipy
2. Black-Litterman — combine market equilibrium with investor views
3. Risk Parity — equal risk contribution from each asset
4. Hierarchical Risk Parity (HRP) — clustering-based allocation
5. CVaR Optimization — minimize Conditional Value at Risk

References:
- Markowitz (1952), "Portfolio Selection"
- Black & Litterman (1991), "Global Portfolio Optimization"
- Maillard et al. (2010), "The Properties of Equally Weighted Risk Contribution Portfolios"
- Lopez de Prado (2016), "Building Diversified Portfolios that Outperform Out of Sample"
"""

import pandas as pd
import numpy as np
from typing import Optional
from scipy.optimize import minimize
from scipy.cluster.hierarchy import linkage
from scipy.spatial.distance import squareform


def calc_portfolio_return(weights: np.ndarray, mean_returns: pd.Series) -> float:
    return np.sum(mean_returns * weights)


def calc_portfolio_volatility(weights: np.ndarray, cov_matrix: pd.DataFrame) -> float:
    return np.sqrt(np.dot(weights.T, np.dot(cov_matrix, weights)))


def calc_portfolio_sharpe(
    weights: np.ndarray, mean_returns: pd.Series, cov_matrix: pd.DataFrame, risk_free: float = 0.05
) -> float:
    annual_return = calc_portfolio_return(weights, mean_returns) * 252
    annual_vol = calc_portfolio_volatility(weights, cov_matrix) * np.sqrt(252)
    if annual_vol == 0:
        return 0
    return (annual_return - risk_free) / annual_vol


def optimize_portfolio(
    returns_df: pd.DataFrame, risk_free: float = 0.05, n_portfolios: int = 5000
) -> dict:
    """
    Monte Carlo simulation untuk efficient frontier.
    """
    mean_returns = returns_df.mean()
    cov_matrix = returns_df.cov()
    n_assets = len(returns_df.columns)

    if n_assets < 2:
        return {"error": "Butuh minimal 2 aset untuk optimasi"}

    # Monte Carlo
    results = np.zeros((n_portfolios, 3 + n_assets))
    for i in range(n_portfolios):
        weights = np.random.random(n_assets)
        weights /= np.sum(weights)

        annual_return = np.sum(mean_returns * weights) * 252
        annual_vol = np.sqrt(np.dot(weights.T, np.dot(cov_matrix * 252, weights)))
        sharpe = (annual_return - risk_free) / annual_vol if annual_vol > 0 else 0

        results[i, 0] = annual_return
        results[i, 1] = annual_vol
        results[i, 2] = sharpe
        results[i, 3:] = weights

    columns = ["Return", "Volatility", "Sharpe"] + list(returns_df.columns)
    results_df = pd.DataFrame(results, columns=columns)

    # Find optimal portfolios
    max_sharpe_idx = results_df["Sharpe"].idxmax()
    min_vol_idx = results_df["Volatility"].idxmin()

    max_sharpe_portfolio = results_df.loc[max_sharpe_idx]
    min_vol_portfolio = results_df.loc[min_vol_idx]

    # Optimization (scipy) for precise max Sharpe
    def negative_sharpe(weights):
        return -calc_portfolio_sharpe(weights, mean_returns, cov_matrix, risk_free)

    constraints = {"type": "eq", "fun": lambda x: np.sum(x) - 1}
    bounds = tuple((0, 1) for _ in range(n_assets))
    initial_guess = np.array([1 / n_assets] * n_assets)

    try:
        opt_result = minimize(
            negative_sharpe, initial_guess, method="SLSQP", bounds=bounds, constraints=constraints
        )
        opt_weights = opt_result.x
        opt_return = np.sum(mean_returns * opt_weights) * 252
        opt_vol = np.sqrt(np.dot(opt_weights.T, np.dot(cov_matrix * 252, opt_weights)))
        opt_sharpe = (opt_return - risk_free) / opt_vol if opt_vol > 0 else 0
    except Exception:
        opt_weights = max_sharpe_portfolio[3:].values
        opt_return = max_sharpe_portfolio["Return"]
        opt_vol = max_sharpe_portfolio["Volatility"]
        opt_sharpe = max_sharpe_portfolio["Sharpe"]

    return {
        "efficient_frontier": results_df,
        "max_sharpe_portfolio": {
            "weights": dict(zip(returns_df.columns, max_sharpe_portfolio[3:].values)),
            "return": round(max_sharpe_portfolio["Return"] * 100, 2),
            "volatility": round(max_sharpe_portfolio["Volatility"] * 100, 2),
            "sharpe": round(max_sharpe_portfolio["Sharpe"], 3),
        },
        "min_vol_portfolio": {
            "weights": dict(zip(returns_df.columns, min_vol_portfolio[3:].values)),
            "return": round(min_vol_portfolio["Return"] * 100, 2),
            "volatility": round(min_vol_portfolio["Volatility"] * 100, 2),
            "sharpe": round(min_vol_portfolio["Sharpe"], 3),
        },
        "optimal_portfolio": {
            "weights": dict(zip(returns_df.columns, np.round(opt_weights, 4))),
            "return": round(opt_return * 100, 2),
            "volatility": round(opt_vol * 100, 2),
            "sharpe": round(opt_sharpe, 3),
        },
        "asset_stats": {
            col: {
                "annual_return": round(mean_returns[col] * 252 * 100, 2),
                "annual_volatility": round(np.sqrt(cov_matrix.loc[col, col] * 252) * 100, 2),
                "sharpe": round(
                    (mean_returns[col] * 252 - risk_free)
                    / (np.sqrt(cov_matrix.loc[col, col] * 252))
                    if np.sqrt(cov_matrix.loc[col, col] * 252) > 0
                    else 0,
                    3,
                ),
            }
            for col in returns_df.columns
        },
    }


def calc_diversification_ratio(weights: np.ndarray, cov_matrix: pd.DataFrame) -> float:
    """
    Diversification Ratio = weighted avg volatility / portfolio volatility
    Semakin tinggi = semakin terdiversifikasi.
    """
    asset_vols = np.sqrt(np.diag(cov_matrix))
    weighted_avg_vol = np.sum(weights * asset_vols)
    portfolio_vol = calc_portfolio_volatility(weights, cov_matrix)
    if portfolio_vol == 0:
        return 0
    return weighted_avg_vol / portfolio_vol


def calc_sector_allocation(weights: dict, sector_map: dict) -> pd.DataFrame:
    """
    Kelompokkan alokasi berdasarkan sektor.
    """
    sector_weights = {}
    for ticker, weight in weights.items():
        sector = sector_map.get(ticker, "Other")
        if sector not in sector_weights:
            sector_weights[sector] = 0
        sector_weights[sector] += weight

    df = pd.DataFrame(
        [{"Sektor": k, "Alokasi (%)": round(v * 100, 2)} for k, v in sector_weights.items()]
    ).sort_values("Alokasi (%)", ascending=False)
    return df


# =============================================================================
# BLACK-LITTERMAN MODEL
# =============================================================================

def black_litterman_optimize(
    returns_df: pd.DataFrame,
    market_weights: Optional[np.ndarray] = None,
    views: Optional[np.ndarray] = None,
    view_confidence: Optional[np.ndarray] = None,
    risk_free: float = 0.05,
    tau: float = 0.05,
) -> dict:
    """
    Black-Litterman portfolio optimization.

    Combines market equilibrium returns with investor views to produce
    stable, non-extreme portfolio weights.

    Args:
        returns_df: DataFrame of asset returns
        market_weights: Market cap weights (default: equal weight)
        views: Investor views as P @ Q matrix (absolute views: Q_i = expected excess return of asset i)
        view_confidence: Confidence in each view (omega diagonal, lower = more confident)
        risk_free: Risk-free rate
        tau: Scaling parameter for prior covariance (default 0.05)

    Returns:
        Dict with BL expected returns, weights, and metrics
    """
    n_assets = len(returns_df.columns)
    if n_assets < 2:
        return {"error": "Butuh minimal 2 aset"}

    cov_matrix = returns_df.cov() * 252  # Annualized
    mean_returns = returns_df.mean() * 252

    if market_weights is None:
        market_weights = np.array([1 / n_assets] * n_assets)
    else:
        market_weights = np.asarray(market_weights, dtype=float)
        market_weights = market_weights / market_weights.sum()

    # Implied equilibrium excess returns: Π = λ × Σ × w_market
    risk_aversion = (mean_returns.mean() - risk_free) / np.trace(cov_matrix) * n_assets
    risk_aversion = max(risk_aversion, 1.0)
    pi = risk_aversion * cov_matrix.dot(market_weights)

    if views is not None:
        views = np.asarray(views, dtype=float)
        P = np.eye(n_assets)  # Absolute views
        Q = views

        if view_confidence is not None:
            omega = np.diag(view_confidence)
        else:
            omega = np.diag(np.diag(P.dot(tau * cov_matrix).dot(P.T)))

        # BL expected returns: E[R] = [(τΣ)^-1 + P'Ω^-1 P]^-1 × [(τΣ)^-1 Π + P'Ω^-1 Q]
        tau_cov_inv = np.linalg.inv(tau * cov_matrix)
        omega_inv = np.linalg.inv(omega)

        bl_returns = np.linalg.inv(
            tau_cov_inv + P.T.dot(omega_inv).dot(P)
        ).dot(
            tau_cov_inv.dot(pi) + P.T.dot(omega_inv).dot(Q)
        )
    else:
        bl_returns = pi.values if isinstance(pi, pd.Series) else pi

    # Optimize based on BL returns
    def negative_sharpe(weights):
        port_return = np.sum(bl_returns * weights)
        port_vol = np.sqrt(np.dot(weights.T, np.dot(cov_matrix, weights)))
        if port_vol == 0:
            return 0
        return -(port_return - risk_free) / port_vol

    constraints = {"type": "eq", "fun": lambda x: np.sum(x) - 1}
    bounds = tuple((0, 1) for _ in range(n_assets))
    initial_guess = market_weights.copy()

    try:
        opt_result = minimize(
            negative_sharpe, initial_guess, method="SLSQP",
            bounds=bounds, constraints=constraints,
        )
        bl_weights = opt_result.x
    except Exception:
        bl_weights = market_weights

    bl_return = np.sum(bl_returns * bl_weights)
    bl_vol = np.sqrt(np.dot(bl_weights.T, np.dot(cov_matrix, bl_weights)))
    bl_sharpe = (bl_return - risk_free) / bl_vol if bl_vol > 0 else 0

    return {
        "method": "Black-Litterman",
        "bl_expected_returns": dict(zip(returns_df.columns, np.round(bl_returns, 4))),
        "weights": dict(zip(returns_df.columns, np.round(bl_weights, 4))),
        "return": round(bl_return * 100, 2),
        "volatility": round(bl_vol * 100, 2),
        "sharpe": round(bl_sharpe, 3),
        "implied_returns": dict(zip(returns_df.columns, np.round(
            pi.values if isinstance(pi, pd.Series) else pi, 4
        ))),
    }


# =============================================================================
# RISK PARITY
# =============================================================================

def risk_parity_optimize(
    returns_df: pd.DataFrame,
    risk_free: float = 0.05,
    target_risk: Optional[float] = None,
) -> dict:
    """
    Risk Parity portfolio — equal risk contribution from each asset.

    Each asset contributes equally to total portfolio risk.
    Low-vol assets get higher weight, high-vol assets get lower weight.

    Reference: Maillard et al. (2010)

    Args:
        returns_df: DataFrame of asset returns
        risk_free: Risk-free rate
        target_risk: Target portfolio volatility (default: unconstrained)

    Returns:
        Dict with weights and metrics
    """
    n_assets = len(returns_df.columns)
    if n_assets < 2:
        return {"error": "Butuh minimal 2 aset"}

    cov_matrix = returns_df.cov() * 252
    mean_returns = returns_df.mean() * 252

    def risk_contribution_objective(weights):
        weights = np.abs(weights) + 1e-8
        weights = weights / weights.sum()
        port_vol = np.sqrt(np.dot(weights.T, np.dot(cov_matrix, weights)))
        if port_vol == 0:
            return 0
        marginal_contrib = cov_matrix.dot(weights) / port_vol
        contrib = weights * marginal_contrib
        target = port_vol / n_assets
        return np.sum((contrib - target) ** 2)

    constraints = {"type": "eq", "fun": lambda x: np.sum(x) - 1}
    bounds = tuple((0.01, 1.0) for _ in range(n_assets))
    initial_guess = np.array([1 / n_assets] * n_assets)

    try:
        opt_result = minimize(
            risk_contribution_objective, initial_guess, method="SLSQP",
            bounds=bounds, constraints=constraints,
            options={"maxiter": 500, "ftol": 1e-12},
        )
        rp_weights = opt_result.x
        rp_weights = rp_weights / rp_weights.sum()
    except Exception:
        # Fallback: inverse volatility weighting
        asset_vols = np.sqrt(np.diag(cov_matrix))
        rp_weights = (1 / asset_vols) / np.sum(1 / asset_vols)

    rp_return = np.sum(mean_returns * rp_weights)
    rp_vol = np.sqrt(np.dot(rp_weights.T, np.dot(cov_matrix, rp_weights)))
    rp_sharpe = (rp_return - risk_free) / rp_vol if rp_vol > 0 else 0

    # Calculate actual risk contributions
    marginal = cov_matrix.dot(rp_weights) / rp_vol if rp_vol > 0 else np.zeros(n_assets)
    risk_contribs = rp_weights * marginal
    risk_contrib_pct = risk_contribs / risk_contribs.sum() if risk_contribs.sum() > 0 else risk_contribs

    return {
        "method": "Risk Parity",
        "weights": dict(zip(returns_df.columns, np.round(rp_weights, 4))),
        "return": round(rp_return * 100, 2),
        "volatility": round(rp_vol * 100, 2),
        "sharpe": round(rp_sharpe, 3),
        "risk_contributions": dict(zip(returns_df.columns, np.round(risk_contrib_pct, 4))),
    }


# =============================================================================
# HIERARCHICAL RISK PARITY (HRP)
# =============================================================================

def hrp_optimize(
    returns_df: pd.DataFrame,
    risk_free: float = 0.05,
) -> dict:
    """
    Hierarchical Risk Parity (HRP) — clustering-based allocation.

    Uses hierarchical clustering to group similar assets, then allocates
    risk equally across clusters and within clusters. More robust than
    Markowitz because it doesn't require inverting the covariance matrix.

    Reference: Lopez de Prado (2016), "Building Diversified Portfolios"

    Args:
        returns_df: DataFrame of asset returns
        risk_free: Risk-free rate

    Returns:
        Dict with weights and metrics
    """
    n_assets = len(returns_df.columns)
    if n_assets < 2:
        return {"error": "Butuh minimal 2 aset"}

    cov_matrix = returns_df.cov() * 252
    mean_returns = returns_df.mean() * 252
    corr_matrix = returns_df.corr()

    # Step 1: Distance matrix from correlation
    dist = np.sqrt(0.5 * (1 - corr_matrix.values))
    np.fill_diagonal(dist, 0)
    dist = np.clip(dist, 0, 2)

    # Step 2: Hierarchical clustering
    try:
        condensed_dist = squareform(dist, checks=False)
        link = linkage(condensed_dist, method="single")
    except Exception:
        # Fallback to equal weight if clustering fails
        hrp_weights = np.array([1 / n_assets] * n_assets)
        hrp_return = np.sum(mean_returns * hrp_weights)
        hrp_vol = np.sqrt(np.dot(hrp_weights.T, np.dot(cov_matrix, hrp_weights)))
        hrp_sharpe = (hrp_return - risk_free) / hrp_vol if hrp_vol > 0 else 0
        return {
            "method": "HRP (Equal Weight Fallback)",
            "weights": dict(zip(returns_df.columns, np.round(hrp_weights, 4))),
            "return": round(hrp_return * 100, 2),
            "volatility": round(hrp_vol * 100, 2),
            "sharpe": round(hrp_sharpe, 3),
        }

    # Step 3: Quasi-diagonalization — reorder assets by cluster
    def get_quasi_diag(link):
        link = link.astype(int)
        n = link[-1, 3]  # Total number of original items
        sort_ix = pd.Series([int(link[-1, 0]), int(link[-1, 1])])

        while sort_ix.max() >= n:
            sort_ix.index = range(0, sort_ix.shape[0] * 2, 2)
            df0 = sort_ix[sort_ix >= n]
            i = df0.index
            j = df0.values - n
            sort_ix[i] = link[j, 0]
            df0 = pd.Series(link[j, 1], index=i + 1)
            sort_ix = pd.concat([sort_ix, df0])
            sort_ix = sort_ix.sort_index()
            sort_ix.index = range(sort_ix.shape[0])

        return sort_ix.values.tolist()

    sort_ix = get_quasi_diag(link)

    # Step 4: Recursive bisection — allocate weight to clusters
    def get_recursive_bisection(cov, sort_ix):
        w = pd.Series(1.0, index=sort_ix)
        clusters = [sort_ix]

        while len(clusters) > 0:
            clusters = [c[j:k] for c in clusters for j, k in (
                [0, len(c) // 2], [len(c) // 2, len(c)]
            ) if len(c) > 1]

            for i in range(0, len(clusters), 2):
                c0 = clusters[i]
                c1 = clusters[i + 1] if i + 1 < len(clusters) else None
                if c1 is None:
                    continue

                v0 = np.sqrt(np.diag(cov.loc[c0, c0]).sum())
                v1 = np.sqrt(np.diag(cov.loc[c1, c1]).sum())
                alpha = 1 - v0 / (v0 + v1)
                w[c0] *= alpha
                w[c1] *= (1 - alpha)

        return w

    asset_names = list(returns_df.columns)
    cov_indexed = cov_matrix.loc[asset_names, asset_names]
    sort_ix_names = [asset_names[i] for i in sort_ix]
    w = get_recursive_bisection(cov_indexed, sort_ix_names)

    hrp_weights = np.array([w[name] for name in asset_names])
    hrp_weights = hrp_weights / hrp_weights.sum()

    hrp_return = np.sum(mean_returns * hrp_weights)
    hrp_vol = np.sqrt(np.dot(hrp_weights.T, np.dot(cov_matrix, hrp_weights)))
    hrp_sharpe = (hrp_return - risk_free) / hrp_vol if hrp_vol > 0 else 0

    return {
        "method": "Hierarchical Risk Parity (HRP)",
        "weights": dict(zip(asset_names, np.round(hrp_weights, 4))),
        "return": round(hrp_return * 100, 2),
        "volatility": round(hrp_vol * 100, 2),
        "sharpe": round(hrp_sharpe, 3),
    }


# =============================================================================
# CVaR OPTIMIZATION
# =============================================================================

def cvar_optimize(
    returns_df: pd.DataFrame,
    risk_free: float = 0.05,
    alpha: float = 0.05,
    target_return: Optional[float] = None,
) -> dict:
    """
    CVaR (Conditional Value at Risk) optimization.

    Minimizes Conditional Value at Risk (Expected Shortfall) instead of
    variance. Better for non-normal return distributions with fat tails.

    Reference: Rockafellar & Uryasev (2000), "Optimization of CVaR"

    Args:
        returns_df: DataFrame of asset returns
        risk_free: Risk-free rate
        alpha: VaR confidence level (0.05 = 95% VaR)
        target_return: Minimum target return (default: mean return)

    Returns:
        Dict with weights and CVaR metrics
    """
    n_assets = len(returns_df.columns)
    if n_assets < 2:
        return {"error": "Butuh minimal 2 aset"}

    mean_returns = returns_df.mean() * 252
    returns_array = returns_df.values
    n_scenarios = len(returns_array)

    if target_return is None:
        target_return = mean_returns.mean() * 252 * 0.8  # 80% of average

    # CVaR optimization using Rockafellar-Uryasev formulation
    # Variables: weights (n), VaR (1), tail_vars (n_scenarios)
    # Minimize: VaR + 1/(alpha * n) * sum(tail_vars)
    # Subject to: tail_vars >= -returns @ weights - VaR
    #             tail_vars >= 0
    #             sum(weights) = 1
    #             weights @ mean_returns * 252 >= target_return

    n_vars = n_assets + 1 + n_scenarios

    def objective(x):

        var = x[n_assets]
        tail_vars = x[n_assets + 1:]
        return var + np.sum(tail_vars) / (alpha * n_scenarios)

    # Constraints
    constraints = [
        {"type": "eq", "fun": lambda x: np.sum(x[:n_assets]) - 1},
        {"type": "ineq", "fun": lambda x: np.dot(x[:n_assets], mean_returns * 252) - target_return},
    ]

    # Tail variable constraints: tail_var_i >= -returns_i @ weights - VaR
    for i in range(n_scenarios):
        constraints.append({
            "type": "ineq",
            "fun": lambda x, i=i: x[n_assets + 1 + i] - (-np.dot(returns_array[i], x[:n_assets]) - x[n_assets]),
        })
        constraints.append({
            "type": "ineq",
            "fun": lambda x, i=i: x[n_assets + 1 + i],
        })

    bounds = [(0, 1)] * n_assets + [(-1, 1)] + [(0, 1)] * n_scenarios

    # Initial guess
    x0 = np.zeros(n_vars)
    x0[:n_assets] = 1 / n_assets
    x0[n_assets] = -0.02  # Initial VaR estimate

    try:
        opt_result = minimize(
            objective, x0, method="SLSQP",
            bounds=bounds, constraints=constraints,
            options={"maxiter": 1000, "ftol": 1e-10},
        )
        cvar_weights = opt_result.x[:n_assets]
        cvar_weights = np.abs(cvar_weights) / np.abs(cvar_weights).sum()
        opt_result.x[n_assets]
    except Exception:
        # Fallback to min-variance
        cvar_weights = np.array([1 / n_assets] * n_assets)
        port_losses = -returns_array.dot(cvar_weights)
        np.percentile(port_losses, (1 - alpha) * 100)

    # Calculate metrics
    cvar_return = np.sum(mean_returns * cvar_weights)
    cov_matrix = returns_df.cov() * 252
    cvar_vol = np.sqrt(np.dot(cvar_weights.T, np.dot(cov_matrix, cvar_weights)))

    # Historical VaR and CVaR
    portfolio_returns = returns_array.dot(cvar_weights)
    portfolio_losses = -portfolio_returns
    hist_var = np.percentile(portfolio_losses, (1 - alpha) * 100)
    hist_cvar = portfolio_losses[portfolio_losses >= hist_var].mean()

    cvar_sharpe = (cvar_return - risk_free) / cvar_vol if cvar_vol > 0 else 0

    return {
        "method": "CVaR Optimization",
        "weights": dict(zip(returns_df.columns, np.round(cvar_weights, 4))),
        "return": round(cvar_return * 100, 2),
        "volatility": round(cvar_vol * 100, 2),
        "sharpe": round(cvar_sharpe, 3),
        "var_95": round(hist_var * 100, 2),
        "cvar_95": round(hist_cvar * 100, 2),
        "alpha": alpha,
    }


# =============================================================================
# COMPREHENSIVE PORTFOLIO COMPARISON
# =============================================================================

def compare_portfolio_methods(
    returns_df: pd.DataFrame,
    risk_free: float = 0.05,
) -> dict:
    """
    Run all portfolio optimization methods and compare results.

    Returns dict with all methods' weights and metrics for comparison.
    """
    results = {}

    # Markowitz
    results["markowitz"] = optimize_portfolio(returns_df, risk_free)

    # Black-Litterman (no views — pure equilibrium)
    results["black_litterman"] = black_litterman_optimize(
        returns_df, risk_free=risk_free
    )

    # Risk Parity
    results["risk_parity"] = risk_parity_optimize(
        returns_df, risk_free=risk_free
    )

    # HRP
    results["hrp"] = hrp_optimize(returns_df, risk_free)

    # CVaR
    results["cvar"] = cvar_optimize(returns_df, risk_free)

    # Summary comparison table
    summary = []
    for method, res in results.items():
        if "error" in res:
            continue
        if "optimal_portfolio" in res:
            # Markowitz format
            opt = res["optimal_portfolio"]
            summary.append({
                "Method": "Markowitz",
                "Return (%)": opt["return"],
                "Volatility (%)": opt["volatility"],
                "Sharpe": opt["sharpe"],
            })
        else:
            summary.append({
                "Method": res.get("method", method),
                "Return (%)": res.get("return", 0),
                "Volatility (%)": res.get("volatility", 0),
                "Sharpe": res.get("sharpe", 0),
            })

    results["comparison"] = pd.DataFrame(summary).sort_values("Sharpe", ascending=False)

    return results
