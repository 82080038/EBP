"""
Pipeline stages for unified daily analysis.
"""
from datetime import datetime
from typing import Dict, List, Optional
from dataclasses import dataclass, field
import pandas as pd

from src.config import TARGET_TICKER, BLUE_CHIPS_ID
from src.notifier import send_in_app
from src.paper_trading import PaperTradingEngine
from src.pipeline.impact_rules import SECTOR_MAP, analyze_news_impact


@dataclass
class PipelineResult:
    """Result of unified pipeline execution."""
    date: str
    market_summary: str
    predictions: Dict[str, Dict]
    impact_analysis: Dict[str, Dict]
    trading_decisions: List[Dict]
    notifications: List[Dict]
    success: bool = True
    errors: List[str] = field(default_factory=list)


class UnifiedPipeline:
    """
    Unified Daily Pipeline — gabungkan SEMUA modul analisis dalam satu run.

    Pipeline:
    1. Scrape berita → sentiment analysis (FinBERT)
    2. Fetch economic calendar → event risk
    3. Fetch fundamental data untuk saham di portfolio
    4. Run technical analysis → ML prediction per ticker
    5. Smart impact analysis: berita/event → map ke saham di portfolio
    6. Decision: augment/reduce/hold position berdasarkan semua data
    7. Eksekusi paper trading
    8. Notifikasi lengkap ke Pusat Notifikasi
    """

    def __init__(self):
        self.paper_trading = PaperTradingEngine()
        self.errors = []

    def run(self, market_data: Dict[str, pd.DataFrame], fred_data: Optional[Dict] = None) -> PipelineResult:
        """Run the complete unified pipeline."""
        result = PipelineResult(
            date=datetime.now().isoformat(),
            market_summary="",
            predictions={},
            impact_analysis={},
            trading_decisions=[],
            notifications=[],
        )

        try:
            # Stage 1: Run predictions for all tickers
            result.predictions = self._run_predictions(market_data, fred_data)

            # Stage 2: Analyze news impact
            result.impact_analysis = self._analyze_impact(market_data)

            # Stage 3: Make trading decisions
            result.trading_decisions = self._make_decisions(result.predictions, result.impact_analysis)

            # Stage 4: Execute paper trading
            self._execute_trading(result.trading_decisions)

            # Stage 5: Generate notifications
            result.notifications = self._generate_notifications(result)

            result.market_summary = self._generate_summary(result)

        except Exception as e:
            result.success = False
            result.errors.append(str(e))
            import traceback
            self.errors.append(traceback.format_exc())

        return result

    def _run_predictions(self, market_data: Dict[str, pd.DataFrame], fred_data: Optional[Dict]) -> Dict[str, Dict]:
        """Stage 1: Run ML predictions for all tickers."""
        from src.predictor import run_prediction as _run_prediction

        predictions = {}
        tickers = list(BLUE_CHIPS_ID.keys()) + [TARGET_TICKER]

        for ticker in tickers:
            try:
                pred = _run_prediction(market_data, fred_data=fred_data, target_ticker=ticker)
                predictions[ticker] = pred
            except Exception as e:
                self.errors.append(f"Prediction failed for {ticker}: {e}")
                predictions[ticker] = {"error": str(e)}

        return predictions

    def _analyze_impact(self, market_data: Dict[str, pd.DataFrame]) -> Dict[str, Dict]:
        """Stage 2: Analyze news/event impact on tickers."""
        from src.ai_agent import generate_daily_briefing

        impact = {}
        # For now, use a placeholder news analysis
        # In production, this would fetch actual news and analyze
        sample_news = "BI rate decision expected next week"

        for ticker in BLUE_CHIPS_ID.keys():
            try:
                impact[ticker] = analyze_news_impact(sample_news, ticker)
            except Exception as e:
                self.errors.append(f"Impact analysis failed for {ticker}: {e}")
                impact[ticker] = {"error": str(e)}

        return impact

    def _make_decisions(self, predictions: Dict[str, Dict], impact: Dict[str, Dict]) -> List[Dict]:
        """Stage 3: Make trading decisions based on predictions and impact."""
        decisions = []

        for ticker in BLUE_CHIPS_ID.keys():
            pred = predictions.get(ticker, {})
            imp = impact.get(ticker, {})

            if "error" in pred or "error" in imp:
                continue

            signal = pred.get("sinyal", "HOLD")
            confidence = pred.get("confidence", 0.5)
            impact_overall = imp.get("overall", "neutral")

            # Adjust signal based on impact
            if impact_overall == "bearish" and signal == "BUY":
                signal = "HOLD"
            elif impact_overall == "bullish" and signal == "SELL":
                signal = "HOLD"

            if signal != "HOLD" and confidence > 0.55:
                decisions.append({
                    "ticker": ticker,
                    "signal": signal,
                    "confidence": confidence,
                    "impact": impact_overall,
                    "reason": f"ML signal: {signal}, impact: {impact_overall}",
                })

        return decisions

    def _execute_trading(self, decisions: List[Dict]):
        """Stage 4: Execute paper trading based on decisions."""
        for decision in decisions:
            try:
                ticker = decision["ticker"]
                signal = decision["signal"]
                # Execute via paper trading engine
                # This is a placeholder - actual implementation would call paper_trading methods
                pass
            except Exception as e:
                self.errors.append(f"Trading execution failed for {ticker}: {e}")

    def _generate_notifications(self, result: PipelineResult) -> List[Dict]:
        """Stage 5: Generate notifications for important events."""
        notifications = []

        # Add notifications for high-confidence signals
        for decision in result.trading_decisions:
            if decision["confidence"] > 0.7:
                notifications.append({
                    "type": "trading_signal",
                    "ticker": decision["ticker"],
                    "signal": decision["signal"],
                    "confidence": decision["confidence"],
                    "message": f"High confidence {decision['signal']} signal for {decision['ticker']}",
                    "timestamp": datetime.now().isoformat(),
                })

        # Send in-app notifications
        for notif in notifications:
            try:
                send_in_app(notif["message"], level="info")
            except Exception as e:
                self.errors.append(f"Failed to send notification: {e}")

        return notifications

    def _generate_summary(self, result: PipelineResult) -> str:
        """Generate market summary from pipeline results."""
        n_predictions = len([p for p in result.predictions.values() if "error" not in p])
        n_decisions = len(result.trading_decisions)
        n_notifications = len(result.notifications)

        return (
            f"Pipeline completed: {n_predictions} predictions, "
            f"{n_decisions} trading decisions, "
            f"{n_notifications} notifications generated."
        )
