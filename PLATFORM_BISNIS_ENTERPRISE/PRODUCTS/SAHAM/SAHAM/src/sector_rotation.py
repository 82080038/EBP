"""
Sector Rotation Analysis Module.

Based on Sam Stovall's sector rotation model:
- Economic cycle has 4 phases: Reflation, Recovery, Overheating, Stagflation
- Each phase favors different sectors
- Bond market → Stock market → Commodity market rotation sequence

Phase detection uses:
- Bond yields (Treasury 10Y)
- Equity trend (IHSG)
- Commodity trend (Gold, Oil)
- USD direction
- VIX levels
- Macro indicators (CPI, unemployment, Fed funds)

IDX Sector mapping:
- Financials: BBCA, BBRI, BMRI, BBNI
- Energy: ADRO, PTBA, MEDC
- Basic Materials: ANTM, INCO, MDKA, SMGR
- Consumer Cyclicals: ASII, MAPA
- Consumer Non-Cyclicals: UNVR, ICBP, INDF, KLBF
- Infrastructure: TLKM, ISAT
- Property: CTRA, BSDE, LPKR
- Healthcare: KLBF, INAF
- Technology: MTDL
- Industrials: WIKA, WSKT, JSMR
"""

import pandas as pd
from dataclasses import dataclass, field
from typing import Optional, Dict


@dataclass
class SectorRotationAnalysis:
    current_phase: str  # "reflation", "recovery", "overheating", "stagflation"
    phase_confidence: float  # 0-100
    favored_sectors: list = field(default_factory=list)
    avoided_sectors: list = field(default_factory=list)
    bond_trend: str = ""
    equity_trend: str = ""
    commodity_trend: str = ""
    dollar_trend: str = ""
    vix_level: str = ""
    rotation_signal: str = ""
    recommendations: list = field(default_factory=list)
    details: dict = field(default_factory=dict)


# Sector performance by economic cycle phase
SECTOR_ROTATION_MAP = {
    "reflation": {
        "favored": ["Consumer Non-Cyclicals", "Healthcare", "Infrastructure", "Utilities"],
        "avoided": ["Financials", "Energy", "Basic Materials", "Industrials"],
        "description": "Economy slowing, rates being cut. Defensive sectors outperform.",
    },
    "recovery": {
        "favored": ["Financials", "Industrials", "Consumer Cyclicals", "Basic Materials"],
        "avoided": ["Consumer Non-Cyclicals", "Healthcare", "Energy"],
        "description": "Economy growing, rates low. Cyclical and growth sectors lead.",
    },
    "overheating": {
        "favored": ["Energy", "Basic Materials", "Consumer Cyclicals"],
        "avoided": ["Consumer Non-Cyclicals", "Infrastructure", "Healthcare"],
        "description": "Economy strong, rates rising. Commodities and cyclicals peak.",
    },
    "stagflation": {
        "favored": ["Energy", "Healthcare", "Consumer Non-Cyclicals"],
        "avoided": ["Financials", "Consumer Cyclicals", "Property", "Technology"],
        "description": "Economy declining, inflation high. Defensive + commodity sectors.",
    },
}


def detect_trend(series: pd.Series, lookback: int = 20) -> str:
    """Detect trend direction: 'up', 'down', 'flat'."""
    if len(series) < lookback:
        return "flat"
    recent = series.tail(lookback)
    change = (recent.iloc[-1] / recent.iloc[0] - 1) * 100

    if change > 2:
        return "up"
    elif change < -2:
        return "down"
    else:
        return "flat"


def detect_economic_phase(
    market_data: Dict[str, pd.DataFrame],
    fred_data: Optional[Dict[str, pd.DataFrame]] = None,
) -> SectorRotationAnalysis:
    """
    Detect current economic cycle phase using intermarket signals.
    """
    details = {}

    # === Bond Trend (Treasury 10Y) ===
    bond_trend = "flat"
    if fred_data and "TREASURY_10Y" in fred_data:
        bond_series = fred_data["TREASURY_10Y"].iloc[:, 0]
        bond_trend = detect_trend(bond_series)
        details["bond_yield_trend"] = bond_trend
        details["bond_yield_current"] = round(bond_series.iloc[-1], 2)

    # === Equity Trend (IHSG) ===
    equity_trend = "flat"
    for name in ["IHSG", "^JKSE"]:
        if name in market_data:
            equity_trend = detect_trend(market_data[name]["Close"])
            break
    details["equity_trend"] = equity_trend

    # === Commodity Trend (Gold + Oil) ===
    gold_trend = "flat"
    oil_trend = "flat"
    if "GOLD" in market_data or "GC=F" in market_data:
        key = "GOLD" if "GOLD" in market_data else "GC=F"
        gold_trend = detect_trend(market_data[key]["Close"])
    if "OIL" in market_data or "CL=F" in market_data:
        key = "OIL" if "OIL" in market_data else "CL=F"
        oil_trend = detect_trend(market_data[key]["Close"])

    commodity_trend = "up" if gold_trend == "up" or oil_trend == "up" else "down" if gold_trend == "down" and oil_trend == "down" else "flat"
    details["gold_trend"] = gold_trend
    details["oil_trend"] = oil_trend
    details["commodity_trend"] = commodity_trend

    # === USD Trend ===
    dollar_trend = "flat"
    if "USD_IDR" in market_data or "IDR=X" in market_data:
        key = "USD_IDR" if "USD_IDR" in market_data else "IDR=X"
        dollar_trend = detect_trend(market_data[key]["Close"])
    details["dollar_trend"] = dollar_trend

    # === VIX Level ===
    vix_level = "normal"
    vix_value = None
    if "VIX" in market_data or "^VIX" in market_data:
        key = "VIX" if "VIX" in market_data else "^VIX"
        vix_value = market_data[key]["Close"].iloc[-1]
        if vix_value > 30:
            vix_level = "extreme_fear"
        elif vix_value > 20:
            vix_level = "elevated"
        elif vix_value < 12:
            vix_level = "complacent"
        else:
            vix_level = "normal"
    details["vix_level"] = vix_level
    details["vix_value"] = round(vix_value, 1) if vix_value else None

    # === Macro Indicators ===
    cpi_trend = "flat"
    unemployment_trend = "flat"
    fed_rate = None
    if fred_data:
        if "CPI" in fred_data:
            cpi_series = fred_data["CPI"].iloc[:, 0]
            if len(cpi_series) >= 2:
                cpi_change = (cpi_series.iloc[-1] / cpi_series.iloc[-2] - 1) * 100
                cpi_trend = "up" if cpi_change > 0.3 else "down" if cpi_change < 0 else "flat"
                details["cpi_change"] = round(cpi_change, 2)
        if "UNEMPLOYMENT" in fred_data:
            unemp_series = fred_data["UNEMPLOYMENT"].iloc[:, 0]
            if len(unemp_series) >= 2:
                unemp_change = unemp_series.iloc[-1] - unemp_series.iloc[-2]
                unemployment_trend = "up" if unemp_change > 0.1 else "down" if unemp_change < -0.1 else "flat"
                details["unemployment_change"] = round(unemp_change, 2)
        if "FEDFUNDS" in fred_data:
            fed_rate = fred_data["FEDFUNDS"].iloc[:, 0].iloc[-1]
            details["fed_funds_rate"] = round(fed_rate, 2)

    # === Phase Determination ===
    # Scoring system for each phase
    phase_scores = {
        "reflation": 0,
        "recovery": 0,
        "overheating": 0,
        "stagflation": 0,
    }

    # Bond yield falling = reflation signal
    if bond_trend == "down":
        phase_scores["reflation"] += 25
        phase_scores["recovery"] += 10
    elif bond_trend == "up":
        phase_scores["overheating"] += 20
        phase_scores["stagflation"] += 15

    # Equity rising
    if equity_trend == "up":
        phase_scores["recovery"] += 25
        phase_scores["overheating"] += 15
    elif equity_trend == "down":
        phase_scores["reflation"] += 10
        phase_scores["stagflation"] += 20

    # Commodities rising
    if commodity_trend == "up":
        phase_scores["overheating"] += 20
        phase_scores["stagflation"] += 15
        phase_scores["recovery"] += 10
    elif commodity_trend == "down":
        phase_scores["reflation"] += 15

    # Dollar falling = good for stocks/commodities
    if dollar_trend == "down":
        phase_scores["recovery"] += 10
        phase_scores["overheating"] += 10
    elif dollar_trend == "up":
        phase_scores["stagflation"] += 10
        phase_scores["reflation"] += 5

    # VIX
    if vix_level == "extreme_fear":
        phase_scores["reflation"] += 15
        phase_scores["stagflation"] += 10
    elif vix_level == "complacent":
        phase_scores["overheating"] += 10

    # CPI
    if cpi_trend == "up":
        phase_scores["overheating"] += 10
        phase_scores["stagflation"] += 10
    elif cpi_trend == "down":
        phase_scores["reflation"] += 10

    # Unemployment
    if unemployment_trend == "up":
        phase_scores["reflation"] += 10
        phase_scores["stagflation"] += 10
    elif unemployment_trend == "down":
        phase_scores["recovery"] += 10
        phase_scores["overheating"] += 5

    # Determine winning phase
    best_phase = max(phase_scores, key=phase_scores.get)
    best_score = phase_scores[best_phase]
    total_score = sum(phase_scores.values())
    confidence = (best_score / total_score * 100) if total_score > 0 else 0

    rotation_info = SECTOR_ROTATION_MAP[best_phase]
    favored = rotation_info["favored"]
    avoided = rotation_info["avoided"]

    # Recommendations
    recommendations = [
        f"Economic Phase: {best_phase.upper()} — {rotation_info['description']}",
        f"Favored sectors: {', '.join(favored)}",
        f"Avoid sectors: {', '.join(avoided)}",
    ]

    if vix_level == "extreme_fear":
        recommendations.append("⚠ VIX extreme — Market fear high, contrarian buy opportunity in favored sectors.")
    elif vix_level == "complacent":
        recommendations.append("⚠ VIX low — Market complacency, consider hedging.")

    # Rotation signal
    if equity_trend == "up" and bond_trend == "down":
        rotation_signal = "Early cycle rotation → Financials, Industrials"
    elif equity_trend == "up" and commodity_trend == "up":
        rotation_signal = "Mid cycle → Energy, Materials"
    elif equity_trend == "down" and commodity_trend == "up":
        rotation_signal = "Late cycle → Defensive + Energy"
    elif equity_trend == "down" and bond_trend == "down":
        rotation_signal = "Recession → Bonds, Defensive sectors"
    else:
        rotation_signal = "No clear rotation signal"

    details["phase_scores"] = {k: round(v, 1) for k, v in phase_scores.items()}

    return SectorRotationAnalysis(
        current_phase=best_phase,
        phase_confidence=round(confidence, 1),
        favored_sectors=favored,
        avoided_sectors=avoided,
        bond_trend=bond_trend,
        equity_trend=equity_trend,
        commodity_trend=commodity_trend,
        dollar_trend=dollar_trend,
        vix_level=vix_level,
        rotation_signal=rotation_signal,
        recommendations=recommendations,
        details=details,
    )
