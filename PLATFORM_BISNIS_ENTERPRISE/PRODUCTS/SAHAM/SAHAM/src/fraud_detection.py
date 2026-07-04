"""
Market Fraud Detection & Data Integrity Module.

4-layer anti-fraud system:
1. Data quality validation — every incoming DataFrame checked before use
2. Cross-source verification — compare prices from Yahoo Finance vs Alpha Vantage
3. Index-constituent consistency — ^JKSE vs weighted sum of blue chips
4. News-price divergence — alert if price moves contra all news sentiment

Usage:
    from src.fraud_detection import FraudDetector
    detector = FraudDetector()
    report = detector.validate_all(market_data, news_sentiment=-20)
"""

import pandas as pd
from typing import Dict, List, Optional
from dataclasses import dataclass, field
from datetime import datetime


@dataclass
class FraudAlert:
    """Single fraud/anomaly alert."""
    timestamp: str
    alert_type: str  # "data_quality", "cross_source", "index_constituent", "news_divergence"
    severity: str    # "info", "warning", "critical"
    ticker: str
    message: str
    details: Dict = field(default_factory=dict)


@dataclass
class FraudReport:
    """Combined fraud detection report."""
    timestamp: str
    alerts: List[FraudAlert] = field(default_factory=list)
    passed: bool = True
    data_quality_reports: Dict = field(default_factory=dict)
    cross_source_mismatches: Dict = field(default_factory=dict)
    index_consistency: Dict = field(default_factory=dict)
    news_divergence: Dict = field(default_factory=dict)

    @property
    def critical_count(self) -> int:
        return sum(1 for a in self.alerts if a.severity == "critical")

    @property
    def warning_count(self) -> int:
        return sum(1 for a in self.alerts if a.severity == "warning")

    def summary(self) -> str:
        status = "PASS" if self.passed else "FAIL"
        return (
            f"Fraud Detection [{status}]: "
            f"{len(self.alerts)} alerts ({self.critical_count} critical, {self.warning_count} warning)"
        )


class FraudDetector:
    """
    Multi-layer fraud detection for market data.

    Layer 1: Data quality validation (completeness, freshness, anomalies)
    Layer 2: Cross-source price verification (Yahoo vs Alpha Vantage)
    Layer 3: Index-constituent consistency (^JKSE vs sum of blue chips)
    Layer 4: News-price divergence detection
    """

    def __init__(self, alpha_vantage_key: str = ""):
        import os
        self.alpha_vantage_key = alpha_vantage_key or os.getenv("ALPHA_VANTAGE_API_KEY", "")
        self.alerts: List[FraudAlert] = []

    # =========================================================================
    # LAYER 1: DATA QUALITY VALIDATION
    # =========================================================================

    def validate_data_quality(
        self, df: pd.DataFrame, source: str = "unknown",
        required_cols: Optional[List[str]] = None,
    ) -> Dict:
        """
        Validate incoming data before pipeline use.
        Returns dict with passed/failed checks and anomaly flags.
        """
        result = {"source": source, "passed": True, "issues": [], "checks": {}}

        if df.empty:
            result["passed"] = False
            result["issues"].append("DataFrame kosong")
            self._alert("data_quality", "critical", source, "DataFrame kosong")
            return result

        # Check 1: Completeness
        missing_pct = (df.isna().sum().sum() / df.size * 100) if df.size > 0 else 0
        result["checks"]["missing_pct"] = round(missing_pct, 2)
        if missing_pct > 10:
            result["passed"] = False
            result["issues"].append(f"Missing data {missing_pct:.1f}% > 10%")
            self._alert("data_quality", "warning", source, f"Missing data {missing_pct:.1f}%")

        # Check 2: Required columns
        if required_cols:
            missing = [c for c in required_cols if c not in df.columns]
            if missing:
                result["passed"] = False
                result["issues"].append(f"Missing columns: {missing}")
                self._alert("data_quality", "critical", source, f"Missing columns: {missing}")

        # Check 3: Price anomaly (>20% daily gap)
        if "Close" in df.columns and len(df) > 1:
            returns = df["Close"].pct_change().abs()
            anomalies = (returns > 0.20).sum()
            result["checks"]["price_anomalies"] = int(anomalies)
            if anomalies > 3:
                result["passed"] = False
                result["issues"].append(f"Price anomalies: {anomalies} gaps > 20%")
                self._alert("data_quality", "warning", source,
                            f"{anomalies} price gaps > 20% — possible data corruption")

        # Check 4: Volume sanity
        if "Volume" in df.columns:
            neg_vol = int((df["Volume"] < 0).sum())
            result["checks"]["negative_volume"] = neg_vol
            if neg_vol > 0:
                result["passed"] = False
                result["issues"].append(f"Negative volume: {neg_vol} rows")
                self._alert("data_quality", "critical", source,
                            f"Negative volume in {neg_vol} rows — data tampering suspected")

        # Check 5: Duplicate timestamps
        if isinstance(df.index, pd.DatetimeIndex):
            dup_dates = df.index.duplicated().sum()
            result["checks"]["duplicate_dates"] = int(dup_dates)
            if dup_dates > 0:
                result["passed"] = False
                result["issues"].append(f"Duplicate dates: {dup_dates}")
                self._alert("data_quality", "warning", source,
                            f"{dup_dates} duplicate dates — possible data injection")

        # Check 6: Freshness
        if isinstance(df.index, pd.DatetimeIndex) and len(df) > 0:
            last_date = df.index.max()
            hours_old = (datetime.now() - last_date.to_pydatetime()).total_seconds() / 3600
            result["checks"]["freshness_hours"] = round(hours_old, 1)
            if hours_old > 96:
                result["passed"] = False
                result["issues"].append(f"Data stale {hours_old:.0f}h > 96h")
                self._alert("data_quality", "warning", source,
                            f"Data stale {hours_old:.0f} hours")

        # Check 7: Price gaps (Open vs previous Close)
        if "Open" in df.columns and "Close" in df.columns and len(df) > 1:
            gap = ((df["Open"] - df["Close"].shift(1)) / df["Close"].shift(1)).abs()
            big_gaps = (gap > 0.15).sum()
            result["checks"]["overnight_gaps"] = int(big_gaps)
            if big_gaps > 5:
                self._alert("data_quality", "info", source,
                            f"{big_gaps} overnight gaps > 15% — check for stock splits/corporate actions")

        return result

    # =========================================================================
    # LAYER 2: CROSS-SOURCE VERIFICATION
    # =========================================================================

    def cross_source_verify(
        self, ticker: str, primary_df: pd.DataFrame,
        tolerance_pct: float = 2.0,
    ) -> Dict:
        """
        Compare primary source (Yahoo) with secondary source (Alpha Vantage).

        Args:
            ticker: Ticker symbol
            primary_df: DataFrame from primary source (Yahoo Finance)
            tolerance_pct: Max allowed price difference (%)

        Returns:
            Dict with mismatch details
        """
        result = {"ticker": ticker, "verified": True, "mismatches": [], "match_pct": 0}

        if not self.alpha_vantage_key:
            result["verified"] = None
            result["mismatches"].append("No Alpha Vantage API key — cross-check skipped")
            return result

        if primary_df.empty:
            result["verified"] = False
            result["mismatches"].append("Primary data empty")
            return result

        try:
            import requests
            url = "https://www.alphavantage.co/query"
            params = {
                "function": "TIME_SERIES_DAILY",
                "symbol": ticker,
                "outputsize": "compact",
                "apikey": self.alpha_vantage_key,
            }
            resp = requests.get(url, params=params, timeout=30)
            data = resp.json()

            if "Time Series (Daily)" not in data:
                result["verified"] = None
                result["mismatches"].append(f"Alpha Vantage error: {data.get('Note', 'Unknown')}")
                return result

            ts = data["Time Series (Daily)"]
            av_df = pd.DataFrame(ts).T
            av_df.index = pd.to_datetime(av_df.index)
            av_df = av_df.sort_index()
            av_df.columns = ["Open", "High", "Low", "Close", "Volume"]
            av_df = av_df.astype(float)

            # Compare overlapping dates
            common_dates = primary_df.index.intersection(av_df.index)
            if len(common_dates) == 0:
                result["verified"] = None
                result["mismatches"].append("No overlapping dates")
                return result

            matches = 0
            total = 0
            for date in common_dates:
                p_close = primary_df.loc[date, "Close"]
                a_close = av_df.loc[date, "Close"]
                if isinstance(p_close, pd.Series):
                    p_close = p_close.iloc[0]
                if isinstance(a_close, pd.Series):
                    a_close = a_close.iloc[0]

                diff_pct = abs(p_close - a_close) / a_close * 100
                total += 1
                if diff_pct <= tolerance_pct:
                    matches += 1
                else:
                    result["mismatches"].append(
                        f"{date.date()}: Yahoo={p_close:.2f} vs AV={a_close:.2f} ({diff_pct:.1f}% diff)"
                    )
                    if diff_pct > 5:
                        self._alert("cross_source", "critical", ticker,
                                    f"Price mismatch {diff_pct:.1f}% on {date.date()} — data manipulation suspected")

            result["match_pct"] = round(matches / total * 100, 1) if total > 0 else 0
            result["verified"] = result["match_pct"] >= 95

            if result["match_pct"] < 95:
                self._alert("cross_source", "warning", ticker,
                            f"Cross-source match only {result['match_pct']}% — {len(result['mismatches'])} mismatches")

        except Exception as e:
            result["verified"] = None
            result["mismatches"].append(f"Cross-source error: {e}")

        return result

    # =========================================================================
    # LAYER 3: INDEX-CONSTITUENT CONSISTENCY
    # =========================================================================

    def check_index_consistency(
        self, index_df: pd.DataFrame, constituent_dfs: Dict[str, pd.DataFrame],
        index_ticker: str = "^JKSE",
    ) -> Dict:
        """
        Check if index price is consistent with weighted sum of constituents.

        Args:
            index_df: Index DataFrame with Close column
            constituent_dfs: Dict of ticker → DataFrame
            index_ticker: Index ticker symbol

        Returns:
            Dict with consistency analysis
        """
        result = {
            "index_ticker": index_ticker,
            "consistent": True,
            "correlation": 0.0,
            "divergence_days": 0,
            "details": [],
        }

        if index_df.empty or "Close" not in index_df.columns:
            result["consistent"] = None
            result["details"].append("Index data empty or no Close column")
            return result

        if not constituent_dfs:
            result["consistent"] = None
            result["details"].append("No constituent data provided")
            return result

        # Build equal-weighted constituent index
        constituent_returns = []
        for ticker, df in constituent_dfs.items():
            if df.empty or "Close" not in df.columns:
                continue
            ret = df["Close"].pct_change().dropna()
            ret.name = ticker
            constituent_returns.append(ret)

        if not constituent_returns:
            result["consistent"] = None
            result["details"].append("No valid constituent data")
            return result

        combined = pd.concat(constituent_returns, axis=1)
        avg_constituent_return = combined.mean(axis=1)

        index_return = index_df["Close"].pct_change().dropna()

        # Align dates
        common = index_return.index.intersection(avg_constituent_return.index)
        if len(common) < 20:
            result["consistent"] = None
            result["details"].append(f"Only {len(common)} common dates — insufficient")
            return result

        idx_ret = index_return.loc[common]
        const_ret = avg_constituent_return.loc[common]

        # Correlation
        corr = idx_ret.corr(const_ret)
        result["correlation"] = round(corr, 4)

        # Divergence: days where index and constituents move in opposite directions
        divergence = ((idx_ret > 0) & (const_ret < 0)) | ((idx_ret < 0) & (const_ret > 0))
        divergence_days = int(divergence.sum())
        result["divergence_days"] = divergence_days
        divergence_pct = divergence_days / len(common) * 100

        result["details"].append(
            f"Correlation: {corr:.4f}, Divergence: {divergence_days}/{len(common)} days ({divergence_pct:.1f}%)"
        )

        # Flag if correlation too low or divergence too high
        if corr < 0.3:
            result["consistent"] = False
            self._alert("index_constituent", "critical", index_ticker,
                        f"Index-constituent correlation {corr:.2f} < 0.3 — index data may be corrupted")
        elif divergence_pct > 40:
            result["consistent"] = False
            self._alert("index_constituent", "warning", index_ticker,
                        f"{divergence_pct:.0f}% divergence days — index may not reflect constituents")
        elif corr < 0.5:
            result["consistent"] = False
            self._alert("index_constituent", "warning", index_ticker,
                        f"Low index-constituent correlation {corr:.2f} — check data integrity")

        return result

    # =========================================================================
    # LAYER 4: NEWS-PRICE DIVERGENCE
    # =========================================================================

    def check_news_price_divergence(
        self, ticker: str, price_change_pct: float,
        news_sentiment: float,  # -100 to 100
        news_count: int = 0,
    ) -> Dict:
        """
        Detect if price movement contradicts all news sentiment.

        Args:
            ticker: Ticker symbol
            price_change_pct: Price change in percentage
            news_sentiment: Aggregate sentiment (-100 bearish to +100 bullish)
            news_count: Number of news articles analyzed

        Returns:
            Dict with divergence analysis
        """
        result = {
            "ticker": ticker,
            "divergence": False,
            "severity": "info",
            "price_direction": "up" if price_change_pct > 0 else "down" if price_change_pct < 0 else "flat",
            "news_direction": "bullish" if news_sentiment > 10 else "bearish" if news_sentiment < -10 else "neutral",
            "message": "",
        }

        # Only flag if there's meaningful news and meaningful price move
        if news_count < 3 or abs(price_change_pct) < 1.0:
            result["message"] = "Insufficient news or price movement for divergence check"
            return result

        # Divergence: price up but news very bearish, or price down but news very bullish
        price_up = price_change_pct > 1.0
        price_down = price_change_pct < -1.0
        news_bullish = news_sentiment > 20
        news_bearish = news_sentiment < -20

        if price_up and news_bearish:
            result["divergence"] = True
            result["severity"] = "critical" if abs(price_change_pct) > 3 else "warning"
            result["message"] = (
                f"Price +{price_change_pct:.1f}% but news sentiment {news_sentiment:.0f}/100 (bearish) — "
                f"possible manipulation or insider information"
            )
            self._alert("news_divergence", result["severity"], ticker, result["message"])

        elif price_down and news_bullish:
            result["divergence"] = True
            result["severity"] = "critical" if abs(price_change_pct) > 3 else "warning"
            result["message"] = (
                f"Price {price_change_pct:.1f}% but news sentiment {news_sentiment:.0f}/100 (bullish) — "
                f"possible bear raid or negative insider info"
            )
            self._alert("news_divergence", result["severity"], ticker, result["message"])

        elif abs(price_change_pct) > 5 and abs(news_sentiment) < 10:
            result["divergence"] = True
            result["severity"] = "warning"
            result["message"] = (
                f"Price moved {price_change_pct:.1f}% with neutral news — "
                f"unexplained move, possible manipulation"
            )
            self._alert("news_divergence", "warning", ticker, result["message"])

        else:
            result["message"] = "Price movement consistent with news sentiment"

        return result

    # =========================================================================
    # COMBINED VALIDATION
    # =========================================================================

    def validate_all(
        self,
        market_data: Dict[str, pd.DataFrame],
        news_sentiment: float = 0,
        news_count: int = 0,
        index_ticker: str = "^JKSE",
    ) -> FraudReport:
        """
        Run all 4 layers of fraud detection on market data.

        Args:
            market_data: Dict of ticker → DataFrame
            news_sentiment: Aggregate news sentiment (-100 to 100)
            news_count: Number of news articles
            index_ticker: Which ticker is the index

        Returns:
            FraudReport with all alerts and checks
        """
        self.alerts = []
        report = FraudReport(timestamp=datetime.now().isoformat())

        # Layer 1: Data quality for each ticker
        for ticker, df in market_data.items():
            dq = self.validate_data_quality(df, source=ticker, required_cols=["Close"])
            report.data_quality_reports[ticker] = dq
            if not dq["passed"]:
                report.passed = False

        # Layer 2: Cross-source verification (only for main tickers)
        if self.alpha_vantage_key:
            for ticker in list(market_data.keys())[:3]:
                df = market_data[ticker]
                xsrc = self.cross_source_verify(ticker, df)
                report.cross_source_mismatches[ticker] = xsrc
                if xsrc.get("verified") is False:
                    report.passed = False

        # Layer 3: Index-constituent consistency
        index_df = market_data.get(index_ticker, pd.DataFrame())
        constituent_dfs = {
            t: df for t, df in market_data.items()
            if t != index_ticker and not df.empty
        }
        if not index_df.empty and constituent_dfs:
            ic = self.check_index_consistency(index_df, constituent_dfs, index_ticker)
            report.index_consistency[index_ticker] = ic
            if ic.get("consistent") is False:
                report.passed = False

        # Layer 4: News-price divergence (for index)
        if not index_df.empty and news_count > 0:
            recent_change = index_df["Close"].pct_change().tail(5).sum() * 100
            nd = self.check_news_price_divergence(
                index_ticker, recent_change, news_sentiment, news_count
            )
            report.news_divergence[index_ticker] = nd
            if nd.get("divergence"):
                report.passed = False

        report.alerts = self.alerts
        return report

    def _alert(self, alert_type: str, severity: str, ticker: str, message: str):
        """Create and store a fraud alert."""
        alert = FraudAlert(
            timestamp=datetime.now().isoformat(),
            alert_type=alert_type,
            severity=severity,
            ticker=ticker,
            message=message,
        )
        self.alerts.append(alert)
        if severity == "critical":
            print(f"[FRAUD DETECT] {ticker}: {message}")
        elif severity == "warning":
            print(f"[FRAUD WARN] {ticker}: {message}")

    # =========================================================================
    # LAYER 5: ANTI-MANIPULATION METRICS (Blueprint Bab 4)
    # =========================================================================

    def check_volume_anomalies(
        self, market_data: Dict[str, pd.DataFrame],
        threshold: float = 3.0,
    ) -> List[Dict]:
        """
        Detect volume anomalies (pump-and-dump indicator) via Z-Score.

        Blueprint Bab 4.1: Z-Score Volume Shock
        """
        from .anti_manipulation import scan_volume_anomalies

        results = scan_volume_anomalies(market_data, threshold=threshold)
        anomalies = []
        for r in results:
            if r.is_anomaly:
                self._alert(
                    alert_type="volume_anomaly",
                    severity="critical" if r.severity == "critical" else "warning",
                    ticker=r.ticker,
                    message=r.description,
                )
            anomalies.append({
                "ticker": r.ticker,
                "z_score": r.z_score,
                "is_anomaly": r.is_anomaly,
                "severity": r.severity,
                "description": r.description,
            })
        return anomalies

    def check_illiquidity(
        self, market_data: Dict[str, pd.DataFrame],
    ) -> List[Dict]:
        """
        Check Amihud illiquidity ratio for all tickers.

        Blueprint Bab 4.1: Amihud Illiquidity Ratio
        """
        from .anti_manipulation import scan_illiquidity

        results = scan_illiquidity(market_data)
        illiquid_list = []
        for r in results:
            if r.is_illiquid:
                self._alert(
                    alert_type="illiquidity",
                    severity="warning",
                    ticker=r.ticker,
                    message=r.description,
                )
            illiquid_list.append({
                "ticker": r.ticker,
                "amihud_ratio": r.amihud_ratio,
                "classification": r.classification,
                "is_illiquid": r.is_illiquid,
                "description": r.description,
            })
        return illiquid_list

    def check_beneish(
        self, financials: Dict[str, Dict[str, float]],
    ) -> List[Dict]:
        """
        Check Beneish M-Score for earnings manipulation.

        Blueprint Bab 4.2: Beneish M-Score
        M-Score > -1.78 → blacklist emiten.
        """
        from .anti_manipulation import calc_beneish_m_score

        results = []
        for ticker, fins in financials.items():
            r = calc_beneish_m_score(fins, ticker=ticker)
            if r.is_manipulator:
                self._alert(
                    alert_type="earnings_manipulation",
                    severity="critical",
                    ticker=ticker,
                    message=r.description,
                )
            results.append({
                "ticker": ticker,
                "m_score": r.m_score,
                "is_manipulator": r.is_manipulator,
                "variables": r.variables,
                "description": r.description,
            })
        return results

    # =========================================================================
    # LAYER 6: MARKET MANIPULATION DETECTION (Blueprint Bab 3)
    # =========================================================================

    def check_wash_trading(
        self, market_data: Dict[str, pd.DataFrame],
    ) -> List[Dict]:
        """
        Detect wash trading — transaksi semu untuk rekayasa volume.

        Blueprint Bab 3.1: Wash Trading
        """
        from .anti_manipulation import detect_wash_trading

        results = []
        for name, df in market_data.items():
            if df is None or df.empty:
                continue
            r = detect_wash_trading(df, ticker=name)
            if r.is_suspected:
                self._alert(
                    alert_type="wash_trading",
                    severity="critical" if r.confidence >= 70 else "warning",
                    ticker=name,
                    message=r.description,
                )
            results.append({
                "ticker": name,
                "is_suspected": r.is_suspected,
                "confidence": r.confidence,
                "indicators": r.indicators,
                "description": r.description,
            })
        return results

    def check_spoofing(
        self, market_data: Dict[str, pd.DataFrame],
    ) -> List[Dict]:
        """
        Detect spoofing — pesanan besar yang dibatalkan untuk memancing retail.

        Blueprint Bab 3.1: Spoofing
        """
        from .anti_manipulation import detect_spoofing

        results = []
        for name, df in market_data.items():
            if df is None or df.empty:
                continue
            r = detect_spoofing(df, ticker=name)
            if r.is_suspected:
                self._alert(
                    alert_type="spoofing",
                    severity="critical" if r.confidence >= 70 else "warning",
                    ticker=name,
                    message=r.description,
                )
            results.append({
                "ticker": name,
                "is_suspected": r.is_suspected,
                "confidence": r.confidence,
                "indicators": r.indicators,
                "description": r.description,
            })
        return results

    def check_fake_news_hype(
        self,
        ticker: str,
        price_change_pct: float,
        volume_z_score: float,
        news_sentiment: float,
        news_count: int = 0,
        insider_selling: Optional[float] = None,
        social_hype_score: Optional[float] = None,
    ) -> Dict:
        """
        Detect fake news hype — sentimen positif bayaran saat distribusi bandar.

        Blueprint Bab 3.2: Fake News Hype
        """
        from .anti_manipulation import detect_fake_news_hype

        r = detect_fake_news_hype(
            ticker=ticker,
            price_change_pct=price_change_pct,
            volume_z_score=volume_z_score,
            news_sentiment=news_sentiment,
            news_count=news_count,
            insider_selling=insider_selling,
            social_hype_score=social_hype_score,
        )
        if r.is_suspected:
            self._alert(
                alert_type="fake_news_hype",
                severity="critical" if r.confidence >= 70 else "warning",
                ticker=ticker,
                message=r.description,
            )
        return {
            "ticker": ticker,
            "is_suspected": r.is_suspected,
            "confidence": r.confidence,
            "indicators": r.indicators,
            "description": r.description,
        }
