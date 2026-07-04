"""
Modul Inter-Market Analysis
Menganalisis hubungan antar pasar: korelasi rolling, lead-lag, spread, ratio
"""

import pandas as pd
from typing import Dict


def calc_rolling_correlation(
    market_data: Dict[str, pd.DataFrame],
    target: str = "IHSG",
    window: int = 60,
) -> pd.DataFrame:
    """
    Rolling correlation target vs semua pasar lainnya.
    """
    if target not in market_data:
        return pd.DataFrame()

    target_returns = market_data[target]["Close"].pct_change()

    correlations = {}
    for name, df in market_data.items():
        if name == target or df.empty:
            continue
        other_returns = df["Close"].pct_change()

        # Align indices
        combined = pd.concat([target_returns, other_returns], axis=1, keys=["target", "other"]).dropna()
        if len(combined) < window:
            continue

        rolling_corr = combined["target"].rolling(window=window).corr(combined["other"])
        correlations[name] = rolling_corr

    if correlations:
        return pd.DataFrame(correlations)
    return pd.DataFrame()


def calc_correlation_matrix(market_data: Dict[str, pd.DataFrame], period: int = 60) -> pd.DataFrame:
    """
    Static correlation matrix dari returns.
    """
    returns_dict = {}
    for name, df in market_data.items():
        if df.empty:
            continue
        returns_dict[name] = df["Close"].pct_change()

    returns_df = pd.DataFrame(returns_dict).dropna()
    if len(returns_df) < period:
        period = len(returns_df)

    return returns_df.tail(period).corr().round(3)


def calc_lead_lag(
    market_data: Dict[str, pd.DataFrame],
    target: str = "IHSG",
    max_lag: int = 10,
) -> pd.DataFrame:
    """
    Lead-lag analysis: pasar mana yang memimpin/mengikuti target.
    Positive lag = other leads target, negative lag = target leads other.
    """
    if target not in market_data:
        return pd.DataFrame()

    target_returns = market_data[target]["Close"].pct_change().dropna()

    results = []
    for name, df in market_data.items():
        if name == target or df.empty:
            continue

        other_returns = df["Close"].pct_change().dropna()

        # Align
        combined = pd.concat([target_returns, other_returns], axis=1, keys=["target", "other"]).dropna()
        if len(combined) < 50:
            continue

        # Cross-correlation at different lags
        best_corr = 0
        best_lag = 0

        for lag in range(-max_lag, max_lag + 1):
            if lag > 0:
                # other leads target by 'lag' days
                shifted = combined["other"].shift(lag)
                valid = combined["target"].align(shifted, join="inner")[0].dropna()
                shifted_valid = shifted.reindex(valid.index).dropna()
                if len(valid) > 30:
                    corr = valid.corr(shifted_valid.reindex(valid.index))
                    if abs(corr) > abs(best_corr):
                        best_corr = corr
                        best_lag = lag
            elif lag < 0:
                # target leads other by |lag| days
                shifted = combined["target"].shift(-lag)
                valid = combined["other"].align(shifted, join="inner")[0].dropna()
                shifted_valid = shifted.reindex(valid.index).dropna()
                if len(valid) > 30:
                    corr = valid.corr(shifted_valid.reindex(valid.index))
                    if abs(corr) > abs(best_corr):
                        best_corr = corr
                        best_lag = lag
            else:
                corr = combined["target"].corr(combined["other"])
                if abs(corr) > abs(best_corr):
                    best_corr = corr
                    best_lag = 0

        if best_lag > 0:
            relationship = f"{name} leads {target} by {best_lag}d"
            interpretation = "Leading indicator"
        elif best_lag < 0:
            relationship = f"{target} leads {name} by {abs(best_lag)}d"
            interpretation = "Lagging indicator"
        else:
            relationship = "Synchronous"
            interpretation = "Contemporaneous"

        results.append({
            "Market": name,
            "Best Correlation": round(best_corr, 3),
            "Best Lag (days)": best_lag,
            "Relationship": relationship,
            "Interpretation": interpretation,
        })

    return pd.DataFrame(results).sort_values("Best Correlation", ascending=False, key=abs)


def calc_spread_analysis(market_data: Dict[str, pd.DataFrame], asset_a: str, asset_b: str) -> dict:
    """
    Spread analysis antara dua aset (ratio, z-score).
    """
    if asset_a not in market_data or asset_b not in market_data:
        return {"error": "Data tidak tersedia"}

    price_a = market_data[asset_a]["Close"]
    price_b = market_data[asset_b]["Close"]

    # Align
    combined = pd.concat([price_a, price_b], axis=1, keys=["a", "b"]).dropna()
    if len(combined) < 50:
        return {"error": "Data terlalu sedikit"}

    ratio = combined["a"] / combined["b"]
    ratio_mean = ratio.rolling(window=60).mean()
    ratio_std = ratio.rolling(window=60).std()
    z_score = (ratio - ratio_mean) / ratio_std

    current_ratio = ratio.iloc[-1]
    current_z = z_score.iloc[-1]

    if current_z > 2:
        signal = f"{asset_a} overvalued vs {asset_b} (Z={current_z:.2f})"
        action = "Consider short A / long B"
    elif current_z < -2:
        signal = f"{asset_a} undervalued vs {asset_b} (Z={current_z:.2f})"
        action = "Consider long A / short B"
    else:
        signal = f"Normal range (Z={current_z:.2f})"
        action = "No spread trade"

    return {
        "asset_a": asset_a,
        "asset_b": asset_b,
        "current_ratio": round(current_ratio, 4),
        "ratio_mean_60d": round(ratio_mean.iloc[-1], 4),
        "z_score": round(current_z, 2),
        "signal": signal,
        "action": action,
        "ratio_series": ratio,
        "z_score_series": z_score,
    }


def calc_intermarket_summary(market_data: Dict[str, pd.DataFrame]) -> dict:
    """
    Ringkasan inter-market analysis untuk dashboard.
    """
    corr_matrix = calc_correlation_matrix(market_data, period=60)
    rolling_corr = calc_rolling_correlation(market_data, "IHSG", window=60)
    lead_lag = calc_lead_lag(market_data, "IHSG", max_lag=5)

    # Key relationships
    key_relationships = {}
    if "IHSG" in corr_matrix.columns:
        for col in corr_matrix.columns:
            if col != "IHSG":
                key_relationships[col] = corr_matrix.loc["IHSG", col]

    # Sort by absolute correlation
    sorted_corr = sorted(key_relationships.items(), key=lambda x: abs(x[1]), reverse=True)

    return {
        "correlation_matrix": corr_matrix,
        "rolling_correlation": rolling_corr,
        "lead_lag": lead_lag,
        "key_relationships": sorted_corr,
        "top_positive": [k for k, v in sorted_corr if v > 0.3][:3],
        "top_negative": [k for k, v in sorted_corr if v < -0.3][:3],
    }
