"""
Anti-Manipulation Metrics — implementasi Blueprint Bab 4.

3 metrik deteksi manipulasi pasar & laporan keuangan:

1. Z-Score Volume Shock (Bab 4.1)
   - Deteksi anomali volume transaksi (pump-and-dump indicator)
   - Z-Score > 3 → flag sebagai risiko tinggi manipulasi

2. Amihud Illiquidity Ratio (Bab 4.1)
   - Ukur elastisitas harga terhadap volume uang
   - Illiquid = harga mudah disetir modal kecil → risiko manipulasi

3. Beneish M-Score (Bab 4.2)
   - 8-variabel deteksi rekayasa laba (earnings manipulation)
   - M-Score > -1.78 → blacklist emiten dari daftar investasi

Referensi:
- Blueprint Pasar Modal Bab 4
- Beneish, M. (1999): "The Detection of Earnings Manipulation"
- Amihud, Y. (2002): "Illiquidity and Stock Returns"
"""

import numpy as np
import pandas as pd
from dataclasses import dataclass, field
from typing import Dict, List, Optional


# =============================================================================
# 1. Z-SCORE VOLUME SHOCK
# =============================================================================

@dataclass
class VolumeShockResult:
    """Hasil deteksi volume anomaly."""
    ticker: str
    current_volume: float
    mean_volume_20: float
    std_volume_20: float
    z_score: float
    is_anomaly: bool
    severity: str  # "normal", "elevated", "high", "critical"
    description: str


def calc_volume_shock(
    volumes: pd.Series,
    window: int = 20,
    threshold: float = 3.0,
    ticker: str = "",
) -> VolumeShockResult:
    """
    Hitung Z-Score volume untuk deteksi anomali (pump indicator).

    Formula:
        Z-Score = (V_t - μ_volume(20)) / σ_volume(20)

    Jika Z-Score > threshold (default 3.0), tandai sebagai anomali.

    Args:
        volumes: Series volume transaksi harian
        window: Rolling window untuk mean/std (default 20 hari)
        threshold: Z-Score threshold untuk flag anomaly
        ticker: Nama ticker untuk labeling

    Returns:
        VolumeShockResult dengan z_score, severity, dan description
    """
    if len(volumes) < window + 1:
        return VolumeShockResult(
            ticker=ticker,
            current_volume=float(volumes.iloc[-1]) if len(volumes) > 0 else 0,
            mean_volume_20=0,
            std_volume_20=0,
            z_score=0,
            is_anomaly=False,
            severity="normal",
            description="Insufficient data for volume shock analysis",
        )

    rolling_mean = volumes.rolling(window=window).mean()
    rolling_std = volumes.rolling(window=window).std()

    current_vol = float(volumes.iloc[-1])
    mean_vol = float(rolling_mean.iloc[-2]) if not np.isnan(rolling_mean.iloc[-2]) else float(volumes.iloc[-window-1:-1].mean())
    std_vol = float(rolling_std.iloc[-2]) if not np.isnan(rolling_std.iloc[-2]) else float(volumes.iloc[-window-1:-1].std())

    if std_vol == 0:
        z_score = 0.0
    else:
        z_score = (current_vol - mean_vol) / std_vol

    is_anomaly = z_score > threshold

    if z_score <= 1.0:
        severity = "normal"
    elif z_score <= 2.0:
        severity = "elevated"
    elif z_score <= threshold:
        severity = "high"
    else:
        severity = "critical"

    if is_anomaly:
        desc = (
            f"Volume anomaly: Z-Score {z_score:.2f} > {threshold} "
            f"(volume {current_vol:,.0f} vs avg {mean_vol:,.0f}) — "
            f"potential pump-and-dump risk"
        )
    else:
        desc = f"Volume normal: Z-Score {z_score:.2f} (within threshold {threshold})"

    return VolumeShockResult(
        ticker=ticker,
        current_volume=current_vol,
        mean_volume_20=mean_vol,
        std_volume_20=std_vol,
        z_score=round(z_score, 4),
        is_anomaly=is_anomaly,
        severity=severity,
        description=desc,
    )


def scan_volume_anomalies(
    market_data: Dict[str, pd.DataFrame],
    window: int = 20,
    threshold: float = 3.0,
) -> List[VolumeShockResult]:
    """
    Scan semua ticker untuk volume anomalies.

    Args:
        market_data: Dict nama -> DataFrame OHLCV
        window: Rolling window
        threshold: Z-Score threshold

    Returns:
        List of VolumeShockResult, sorted by z_score descending
    """
    results = []
    for name, df in market_data.items():
        if df is None or df.empty or "Volume" not in df.columns:
            continue
        result = calc_volume_shock(df["Volume"], window, threshold, ticker=name)
        results.append(result)

    return sorted(results, key=lambda x: x.z_score, reverse=True)


# =============================================================================
# 2. AMIHUD ILLIQUIDITY RATIO
# =============================================================================

@dataclass
class IlliquidityResult:
    """Hasil perhitungan Amihud illiquidity."""
    ticker: str
    amihud_ratio: float  # rata-rata |return| / volume_rupiah
    amihud_daily: pd.Series  # series harian
    classification: str  # "very_liquid", "liquid", "moderate", "illiquid", "very_illiquid"
    is_illiquid: bool
    description: str


def calc_amihud_illiquidity(
    prices: pd.Series,
    volumes: pd.Series,
    ticker: str = "",
    scale: float = 1e9,
) -> IlliquidityResult:
    """
    Hitung Amihud Illiquidity Ratio.

    Formula:
        Amihud = |Return_t| / Volume_Rupiah_t

    Volume_Rupiah = close * volume (estimasi nilai transaksi harian)
    Scale factor untuk readability (default: 1e9 = per miliar rupiah).

    Interpretasi:
        < 0.1  → very_liquid (blue chip, likuid)
        0.1-0.5 → liquid
        0.5-1.0 → moderate
        1.0-5.0 → illiquid (hati-hati, mudah dimanipulasi)
        > 5.0  → very_illiquid (sangat berisiko, mudah disetir)

    Args:
        prices: Series harga penutupan
        volumes: Series volume transaksi (dalam lembar)
        ticker: Nama ticker
        scale: Scale factor (1e9 = per miliar rupiah)

    Returns:
        IlliquidityResult dengan klasifikasi likuiditas
    """
    if len(prices) < 2 or len(volumes) < 2:
        return IlliquidityResult(
            ticker=ticker,
            amihud_ratio=0,
            amihud_daily=pd.Series(dtype=float),
            classification="very_liquid",
            is_illiquid=False,
            description="Insufficient data",
        )

    # Align index
    aligned = pd.concat([prices, volumes], axis=1, keys=["price", "volume"]).dropna()
    if len(aligned) < 2:
        return IlliquidityResult(
            ticker=ticker,
            amihud_ratio=0,
            amihud_daily=pd.Series(dtype=float),
            classification="very_liquid",
            is_illiquid=False,
            description="Insufficient data after alignment",
        )

    returns = aligned["price"].pct_change().abs()
    volume_rupiah = aligned["price"] * aligned["volume"]

    # Hindari divisi dengan nol
    valid = volume_rupiah > 0
    if valid.sum() == 0:
        return IlliquidityResult(
            ticker=ticker,
            amihud_ratio=0,
            amihud_daily=pd.Series(dtype=float),
            classification="very_liquid",
            is_illiquid=False,
            description="No valid volume data",
        )

    amihud_daily = (returns[valid] / volume_rupiah[valid]) * scale
    amihud_ratio = float(amihud_daily.mean())

    if amihud_ratio < 0.1:
        classification = "very_liquid"
    elif amihud_ratio < 0.5:
        classification = "liquid"
    elif amihud_ratio < 1.0:
        classification = "moderate"
    elif amihud_ratio < 5.0:
        classification = "illiquid"
    else:
        classification = "very_illiquid"

    is_illiquid = amihud_ratio >= 1.0

    if is_illiquid:
        desc = (
            f"Illiquid: Amihud={amihud_ratio:.4f} ({classification}) — "
            f"harga mudah disetir modal kecil, risiko manipulasi tinggi"
        )
    else:
        desc = f"Liquid: Amihud={amihud_ratio:.4f} ({classification})"

    return IlliquidityResult(
        ticker=ticker,
        amihud_ratio=round(amihud_ratio, 6),
        amihud_daily=amihud_daily,
        classification=classification,
        is_illiquid=is_illiquid,
        description=desc,
    )


def scan_illiquidity(
    market_data: Dict[str, pd.DataFrame],
    scale: float = 1e9,
) -> List[IlliquidityResult]:
    """
    Scan semua ticker untuk illiquidity.

    Returns:
        List of IlliquidityResult, sorted by amihud_ratio descending (paling illiquid dulu)
    """
    results = []
    for name, df in market_data.items():
        if df is None or df.empty or "Close" not in df.columns or "Volume" not in df.columns:
            continue
        result = calc_amihud_illiquidity(df["Close"], df["Volume"], ticker=name, scale=scale)
        results.append(result)

    return sorted(results, key=lambda x: x.amihud_ratio, reverse=True)


# =============================================================================
# 3. BENEISH M-SCORE
# =============================================================================

@dataclass
class BeneishResult:
    """Hasil Beneish M-Score analysis."""
    ticker: str
    m_score: float
    is_manipulator: bool  # True if M-Score > -1.78
    threshold: float
    variables: Dict[str, float] = field(default_factory=dict)
    description: str = ""


def calc_beneish_m_score(
    financials: Dict[str, float],
    ticker: str = "",
) -> BeneishResult:
    """
    Hitung Beneish M-Score untuk deteksi manipulasi laba.

    8 variabel M-Score:
    1. Days Sales in Receivables Index (DSRI)
    2. Gross Margin Index (GMI)
    3. Asset Quality Index (AQI)
    4. Sales Growth Index (SGI)
    5. Depreciation Index (DEPI)
    6. Sales, General & Administrative Index (SGAI)
    7. Leverage Index (LVGI)
    8. Total Accruals to Total Assets (TATA)

    Formula:
        M = -4.84 + 0.92*DSRI + 0.528*GMI + 0.404*AQI + 0.892*SGI
            + 0.115*DEPI - 0.172*SGAI + 4.679*TATA - 0.327*LVGI

    Interpretasi:
        M > -1.78 → kemungkinan manipulator (blacklist)
        M < -1.78 → kemungkinan bukan manipulator

    Args:
        financials: Dict dengan keys:
            - receivables_t, receivables_t1
            - sales_t, sales_t1
            - cogs_t, cogs_t1
            - ppe_t, ppe_t1
            - total_assets_t, total_assets_t1
            - depreciation_t, depreciation_t1
            - sga_t, sga_t1
            - total_debt_t, total_debt_t1
            - net_income_t
            - cash_flow_ops_t
            (t = periode berjalan, t1 = periode sebelumnya)

        ticker: Nama ticker

    Returns:
        BeneishResult dengan m_score, is_manipulator, dan 8 variabel
    """
    threshold = -1.78

    def safe_div(a, b):
        if b == 0 or b is None or a is None:
            return 0.0
        return a / b

    def safe_idx(a, b):
        if b == 0 or b is None or a is None:
            return 1.0  # neutral index
        return a / b

    # Extract financials
    rec_t = financials.get("receivables_t", 0)
    rec_t1 = financials.get("receivables_t1", 0)
    sales_t = financials.get("sales_t", 0)
    sales_t1 = financials.get("sales_t1", 0)
    cogs_t = financials.get("cogs_t", 0)
    cogs_t1 = financials.get("cogs_t1", 0)
    ppe_t = financials.get("ppe_t", 0)
    ppe_t1 = financials.get("ppe_t1", 0)
    total_assets_t = financials.get("total_assets_t", 0)
    total_assets_t1 = financials.get("total_assets_t1", 0)
    dep_t = financials.get("depreciation_t", 0)
    dep_t1 = financials.get("depreciation_t1", 0)
    sga_t = financials.get("sga_t", 0)
    sga_t1 = financials.get("sga_t1", 0)
    debt_t = financials.get("total_debt_t", 0)
    debt_t1 = financials.get("total_debt_t1", 0)
    ni_t = financials.get("net_income_t", 0)
    cfo_t = financials.get("cash_flow_ops_t", 0)

    # 1. DSRI = (Receivables_t / Sales_t) / (Receivables_t1 / Sales_t1)
    dsri = safe_idx(safe_div(rec_t, sales_t), safe_div(rec_t1, sales_t1))

    # 2. GMI = GrossMargin_t1 / GrossMargin_t
    gm_t = safe_div(sales_t - cogs_t, sales_t)
    gm_t1 = safe_div(sales_t1 - cogs_t1, sales_t1)
    gmi = safe_idx(gm_t1, gm_t)

    # 3. AQI = (1 - (PPE_t + CA_t) / TA_t) / (1 - (PPE_t1 + CA_t1) / TA_t1)
    # Simplified: AQI = (AssetQuality_t1 / AssetQuality_t)
    # AssetQuality = 1 - PPE/TA
    aq_t = 1 - safe_div(ppe_t, total_assets_t)
    aq_t1 = 1 - safe_div(ppe_t1, total_assets_t1)
    aqi = safe_idx(aq_t1, aq_t)

    # 4. SGI = Sales_t / Sales_t1
    sgi = safe_idx(sales_t, sales_t1)

    # 5. DEPI = DepRate_t1 / DepRate_t
    # DepRate = Depreciation / (Depreciation + PPE)
    dep_rate_t = safe_div(dep_t, dep_t + ppe_t)
    dep_rate_t1 = safe_div(dep_t1, dep_t1 + ppe_t1)
    depi = safe_idx(dep_rate_t1, dep_rate_t)

    # 6. SGAI = (SGA_t / Sales_t) / (SGA_t1 / Sales_t1)
    sgai = safe_idx(safe_div(sga_t, sales_t), safe_div(sga_t1, sales_t1))

    # 7. LVGI = (Debt_t / TA_t) / (Debt_t1 / TA_t1)
    lvgi = safe_idx(safe_div(debt_t, total_assets_t), safe_div(debt_t1, total_assets_t1))

    # 8. TATA = (NetIncome_t - CFO_t) / TotalAssets_t
    tata = safe_div(ni_t - cfo_t, total_assets_t)

    # M-Score formula
    m_score = (
        -4.84
        + 0.92 * dsri
        + 0.528 * gmi
        + 0.404 * aqi
        + 0.892 * sgi
        + 0.115 * depi
        - 0.172 * sgai
        + 4.679 * tata
        - 0.327 * lvgi
    )

    is_manipulator = m_score > threshold

    variables = {
        "DSRI": round(dsri, 4),
        "GMI": round(gmi, 4),
        "AQI": round(aqi, 4),
        "SGI": round(sgi, 4),
        "DEPI": round(depi, 4),
        "SGAI": round(sgai, 4),
        "LVGI": round(lvgi, 4),
        "TATA": round(tata, 4),
    }

    if is_manipulator:
        desc = (
            f"M-Score {m_score:.4f} > {threshold} — "
            f"BLACKLIST: terindikasi kuat manipulasi laba"
        )
    else:
        desc = f"M-Score {m_score:.4f} <= {threshold} — bukan manipulator"

    return BeneishResult(
        ticker=ticker,
        m_score=round(m_score, 4),
        is_manipulator=is_manipulator,
        threshold=threshold,
        variables=variables,
        description=desc,
    )


# =============================================================================
# COMBINED ANTI-MANIPULATION REPORT
# =============================================================================

@dataclass
class AntiManipulationReport:
    """Combined report dari semua anti-manipulation checks."""
    timestamp: str
    volume_shocks: List[VolumeShockResult] = field(default_factory=list)
    illiquidity: List[IlliquidityResult] = field(default_factory=list)
    beneish: List[BeneishResult] = field(default_factory=list)
    blacklisted_tickers: List[str] = field(default_factory=list)
    warnings: List[str] = field(default_factory=list)

    @property
    def passed(self) -> bool:
        return len(self.blacklisted_tickers) == 0 and all(not vs.is_anomaly for vs in self.volume_shocks)

    def summary(self) -> str:
        lines = [
            f"Anti-Manipulation Report [{'PASS' if self.passed else 'FAIL'}]",
            f"  Volume anomalies: {sum(1 for vs in self.volume_shocks if vs.is_anomaly)}",
            f"  Illiquid tickers: {sum(1 for il in self.illiquidity if il.is_illiquid)}",
            f"  Beneish manipulators: {sum(1 for b in self.beneish if b.is_manipulator)}",
            f"  Blacklisted: {', '.join(self.blacklisted_tickers) if self.blacklisted_tickers else 'None'}",
        ]
        return "\n".join(lines)


def run_anti_manipulation_scan(
    market_data: Dict[str, pd.DataFrame],
    financials: Optional[Dict[str, Dict[str, float]]] = None,
    volume_threshold: float = 3.0,
) -> AntiManipulationReport:
    """
    Run full anti-manipulation scan: volume shock + illiquidity + Beneish.

    Args:
        market_data: Dict nama -> DataFrame OHLCV
        financials: Dict ticker -> financial metrics dict (optional)
        volume_threshold: Z-Score threshold for volume anomaly

    Returns:
        AntiManipulationReport dengan semua findings
    """
    from datetime import datetime

    report = AntiManipulationReport(timestamp=datetime.now().isoformat())

    # 1. Volume shock scan
    report.volume_shocks = scan_volume_anomalies(market_data, threshold=volume_threshold)
    for vs in report.volume_shocks:
        if vs.is_anomaly:
            report.warnings.append(f"{vs.ticker}: {vs.description}")

    # 2. Illiquidity scan
    report.illiquidity = scan_illiquidity(market_data)
    for il in report.illiquidity:
        if il.is_illiquid:
            report.warnings.append(f"{il.ticker}: {il.description}")

    # 3. Beneish M-Score
    if financials:
        for ticker, fins in financials.items():
            result = calc_beneish_m_score(fins, ticker=ticker)
            report.beneish.append(result)
            if result.is_manipulator:
                report.blacklisted_tickers.append(ticker)
                report.warnings.append(f"{ticker}: {result.description}")

    return report


# =============================================================================
# 4. WASH TRADING DETECTION (Blueprint Bab 3.1)
# =============================================================================

@dataclass
class WashTradingResult:
    """Hasil deteksi wash trading."""
    ticker: str
    is_suspected: bool
    confidence: float  # 0-100
    indicators: Dict[str, float] = field(default_factory=dict)
    description: str = ""


def detect_wash_trading(
    df: pd.DataFrame,
    ticker: str = "",
    window: int = 20,
) -> WashTradingResult:
    """
    Deteksi wash trading — transaksi semu antar-akun milik kelompok yang sama
    untuk merekayasa volume perdagangan agar terlihat aktif.

    Indikator wash trading:
    1. Volume tinggi tapi range harga sempit (high volume, low price movement)
       — transaksi terjadi tapi harga tidak bergerak = antar-akun yang sama
    2. Rasio volume/range tinggi secara konsisten
    3. Pola berulang: volume spike di jam yang sama setiap hari

    Formula:
        Volume-Range Ratio = Volume / (High - Low)
        Normal: VR_Ratio stabil, range proporsional dengan volume
        Wash: VR_Ratio sangat tinggi (volume besar, range kecil)

    Args:
        df: DataFrame OHLCV dengan kolom High, Low, Close, Volume
        ticker: Nama ticker
        window: Rolling window untuk analisis

    Returns:
        WashTradingResult dengan confidence dan indicators
    """
    required = ["High", "Low", "Close", "Volume"]
    missing = [c for c in required if c not in df.columns]
    if missing or len(df) < window:
        return WashTradingResult(
            ticker=ticker,
            is_suspected=False,
            confidence=0,
            description="Insufficient data for wash trading analysis",
        )

    # 1. Volume-Range Ratio: tinggi = volume besar tapi harga hampir tidak bergerak
    price_range = (df["High"] - df["Low"]).replace(0, np.nan)
    vr_ratio = (df["Volume"] / price_range).fillna(0)

    # Normalisasi: bandingkan dengan rolling median
    vr_median = vr_ratio.rolling(window=window).median()
    vr_ratio_norm = vr_ratio / vr_median.replace(0, np.nan)
    vr_ratio_norm = vr_ratio_norm.fillna(1.0)

    # 2. Range-to-Return ratio: wash trading = range kecil tapi "close" bergerak
    # Jika range sangat kecil tapi close berubah, kemungkinan ada mark-up
    daily_return = df["Close"].pct_change().abs()
    range_to_return = (price_range / df["Close"]) / (daily_return.replace(0, np.nan))
    range_to_return = range_to_return.fillna(1.0)

    # 3. Volume consistency: wash trading sering punya volume sangat konsisten
    # (karena dilakukan oleh bot/akun yang sama dengan jadwal tetap)
    vol_cv = df["Volume"].rolling(window=window).std() / df["Volume"].rolling(window=window).mean()
    vol_cv = vol_cv.replace([np.inf, -np.inf, 0], np.nan).fillna(1.0)
    # Low CV = volume terlalu konsisten = suspicious

    # Scoring
    indicators = {}

    # Indicator 1: VR ratio anomaly (high volume, low range)
    recent_vr = float(vr_ratio_norm.iloc[-window:].mean())
    indicators["vr_ratio_normalized"] = round(recent_vr, 4)
    vr_score = min(100, max(0, (recent_vr - 1.5) * 50)) if recent_vr > 1.5 else 0

    # Indicator 2: Low volume CV (too consistent)
    recent_cv = float(vol_cv.iloc[-1])
    indicators["volume_cv"] = round(recent_cv, 4)
    cv_score = min(100, max(0, (0.3 - recent_cv) * 200)) if recent_cv < 0.3 else 0

    # Indicator 3: High volume but low price range (classic wash pattern)
    recent_vol = float(df["Volume"].iloc[-window:].mean())
    avg_vol = float(df["Volume"].mean())
    vol_ratio = recent_vol / avg_vol if avg_vol > 0 else 1.0
    recent_range_pct = float(((df["High"] - df["Low"]) / df["Close"]).iloc[-window:].mean())
    avg_range_pct = float(((df["High"] - df["Low"]) / df["Close"]).mean())
    range_ratio = recent_range_pct / avg_range_pct if avg_range_pct > 0 else 1.0
    indicators["volume_vs_avg"] = round(vol_ratio, 4)
    indicators["range_vs_avg"] = round(range_ratio, 4)
    # High volume + low range = wash trading signal
    wash_score = 0
    if vol_ratio > 1.5 and range_ratio < 0.7:
        wash_score = min(100, (vol_ratio - 1.0) * 30 + (1.0 - range_ratio) * 50)

    # Combined confidence
    confidence = max(vr_score, cv_score, wash_score)
    confidence = round(min(100, confidence), 2)

    is_suspected = confidence >= 50

    if is_suspected:
        desc = (
            f"Wash trading suspected: confidence {confidence:.1f}% — "
            f"high volume with low price range, consistent volume pattern"
        )
    else:
        desc = f"No wash trading indicators: confidence {confidence:.1f}%"

    return WashTradingResult(
        ticker=ticker,
        is_suspected=is_suspected,
        confidence=confidence,
        indicators=indicators,
        description=desc,
    )


# =============================================================================
# 5. SPOOFING DETECTION (Blueprint Bab 3.1)
# =============================================================================

@dataclass
class SpoofingResult:
    """Hasil deteksi spoofing."""
    ticker: str
    is_suspected: bool
    confidence: float  # 0-100
    indicators: Dict[str, float] = field(default_factory=dict)
    description: str = ""


def detect_spoofing(
    df: pd.DataFrame,
    ticker: str = "",
    window: int = 20,
) -> SpoofingResult:
    """
    Deteksi spoofing — memasang pesanan Bid raksasa untuk memancing retail
    membeli, kemudian membatalkan pesanan tersebut sebelum tereksekusi.

    Indikator spoofing (dari OHLCV daily data):
    1. Volume spike diikuti price reversal tajam (buy wall ditarik → harga turun)
    2. High volume tapi close jauh dari high (pesanan besar tidak tereksekusi)
    3. Repeated pattern: volume spike → next day reversal

    Formula:
        Reversal = sign(Close_t - Open_t) != sign(Close_t-1 - Open_t-1)
        Close-to-High ratio = (High - Close) / (High - Low)
        High ratio = (High - Close) / (High - Low) — tinggi = close jauh dari high

    Args:
        df: DataFrame OHLCV
        ticker: Nama ticker
        window: Rolling window

    Returns:
        SpoofingResult dengan confidence dan indicators
    """
    required = ["Open", "High", "Low", "Close", "Volume"]
    missing = [c for c in required if c not in df.columns]
    if missing or len(df) < window + 1:
        return SpoofingResult(
            ticker=ticker,
            is_suspected=False,
            confidence=0,
            description="Insufficient data for spoofing analysis",
        )

    indicators = {}

    # 1. Close-to-High ratio: tinggi = close jauh dari high
    # Spoofing: pesanan besar di high (buy wall) ditarik, close turun
    daily_range = (df["High"] - df["Low"]).replace(0, np.nan)
    close_to_high = ((df["High"] - df["Close"]) / daily_range).fillna(0)
    recent_cth = float(close_to_high.iloc[-window:].mean())
    avg_cth = float(close_to_high.mean())
    indicators["close_to_high_ratio"] = round(recent_cth, 4)
    indicators["close_to_high_avg"] = round(avg_cth, 4)
    cth_score = min(100, max(0, (recent_cth - avg_cth - 0.1) * 200)) if recent_cth > avg_cth + 0.1 else 0

    # 2. Volume spike followed by reversal
    vol_mean = df["Volume"].rolling(window=window).mean()
    vol_std = df["Volume"].rolling(window=window).std()
    vol_z = ((df["Volume"] - vol_mean) / vol_std.replace(0, np.nan)).fillna(0)

    # Reversal: today's direction opposite to yesterday's
    direction = np.sign(df["Close"] - df["Open"])
    reversal = (direction != direction.shift(1)).astype(int)

    # Volume spike + reversal pattern
    spike_reversal = ((vol_z > 2.0) & (reversal == 1)).rolling(window=window).sum()
    recent_sr = int(spike_reversal.iloc[-1]) if not np.isnan(spike_reversal.iloc[-1]) else 0
    indicators["spike_reversal_count"] = recent_sr
    sr_score = min(100, recent_sr * 25)

    # 3. Upper shadow ratio: besar = ada tekanan jual setelah buy wall ditarik
    upper_shadow = ((df["High"] - df[["Open", "Close"]].max(axis=1)) / daily_range).fillna(0)
    recent_us = float(upper_shadow.iloc[-window:].mean())
    avg_us = float(upper_shadow.mean())
    indicators["upper_shadow_ratio"] = round(recent_us, 4)
    indicators["upper_shadow_avg"] = round(avg_us, 4)
    us_score = min(100, max(0, (recent_us - avg_us - 0.1) * 200)) if recent_us > avg_us + 0.1 else 0

    # Combined confidence
    confidence = max(cth_score, sr_score, us_score)
    confidence = round(min(100, confidence), 2)

    is_suspected = confidence >= 50

    if is_suspected:
        desc = (
            f"Spoofing suspected: confidence {confidence:.1f}% — "
            f"volume spikes with reversals, large upper shadows, close far from high"
        )
    else:
        desc = f"No spoofing indicators: confidence {confidence:.1f}%"

    return SpoofingResult(
        ticker=ticker,
        is_suspected=is_suspected,
        confidence=confidence,
        indicators=indicators,
        description=desc,
    )


# =============================================================================
# 6. FAKE NEWS HYPE DETECTION (Blueprint Bab 3.2)
# =============================================================================

@dataclass
class FakeNewsHypeResult:
    """Hasil deteksi fake news hype / pump via media."""
    ticker: str
    is_suspected: bool
    confidence: float  # 0-100
    indicators: Dict = field(default_factory=dict)
    description: str = ""


def detect_fake_news_hype(
    ticker: str,
    price_change_pct: float,
    volume_z_score: float,
    news_sentiment: float,  # -100 to 100
    news_count: int = 0,
    insider_selling: Optional[float] = None,  # net insider sell ratio 0-1
    social_hype_score: Optional[float] = None,  # 0-100, social media buzz
) -> FakeNewsHypeResult:
    """
    Deteksi fake news hype — distribusi sentimen positif via media bayaran
    atau bot media sosial saat posisi bandar sedang melakukan distribusi (jual massal).

    Pattern:
    - Berita sangat positif (sentiment > 60)
    - Harga naik tajam (pump)
    - Volume sangat tinggi (distribusi terjadi)
    - TAPI: insider selling tinggi (bandar keluar)
    - Social media hype tinggi (bot/berita bayalan)

    Blueprint Bab 3.2: Fake News Hype

    Args:
        ticker: Nama ticker
        price_change_pct: Perubahan harga (%) periode terbaru
        volume_z_score: Z-Score volume (dari calc_volume_shock)
        news_sentiment: Sentiment score (-100 to 100)
        news_count: Jumlah berita
        insider_selling: Rasio insider selling (0=no sell, 1=full sell)
        social_hype_score: Social media hype score (0-100)

    Returns:
        FakeNewsHypeResult dengan confidence dan indicators
    """
    indicators = {}
    scores = []

    # 1. Positive news + price pump + high volume = classic hype pattern
    indicators["news_sentiment"] = news_sentiment
    indicators["price_change_pct"] = price_change_pct
    indicators["volume_z_score"] = volume_z_score

    if news_sentiment > 50 and price_change_pct > 5 and volume_z_score > 2:
        hype_score = min(100, (news_sentiment - 30) + (price_change_pct - 3) * 5 + (volume_z_score - 1) * 15)
        scores.append(hype_score)
    else:
        scores.append(0)

    # 2. Insider selling while news is positive = distribution via hype
    if insider_selling is not None:
        indicators["insider_selling"] = insider_selling
        if insider_selling > 0.5 and news_sentiment > 40:
            insider_score = min(100, insider_selling * 80 + (news_sentiment - 20) * 0.5)
            scores.append(insider_score)
        else:
            scores.append(0)

    # 3. Social media hype + price pump = bot-driven pump
    if social_hype_score is not None:
        indicators["social_hype_score"] = social_hype_score
        if social_hype_score > 60 and price_change_pct > 3:
            social_score = min(100, (social_hype_score - 40) + (price_change_pct - 2) * 10)
            scores.append(social_score)
        else:
            scores.append(0)

    # 4. News count: too many positive news in short period = coordinated
    indicators["news_count"] = news_count
    if news_count > 10 and news_sentiment > 60:
        news_flood_score = min(100, (news_count - 5) * 8 + (news_sentiment - 40))
        scores.append(news_flood_score)
    else:
        scores.append(0)

    # Combined confidence = max of all scores, weighted by available data
    confidence = max(scores) if scores else 0
    confidence = round(min(100, confidence), 2)

    is_suspected = confidence >= 50

    if is_suspected:
        desc = (
            f"Fake news hype suspected: confidence {confidence:.1f}% — "
            f"positive sentiment ({news_sentiment:.0f}) + price pump ({price_change_pct:+.1f}%) "
            f"+ high volume (Z={volume_z_score:.1f})"
        )
    else:
        desc = f"No fake news hype indicators: confidence {confidence:.1f}%"

    return FakeNewsHypeResult(
        ticker=ticker,
        is_suspected=is_suspected,
        confidence=confidence,
        indicators=indicators,
        description=desc,
    )
