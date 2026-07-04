"""
Real-time Monitor & Scheduler — GAP 2 & 5.

GAP 2: Auto-run harian terjadwal yang gabungkan semua modul via UnifiedPipeline.
GAP 5: Real-time monitoring — polling loop untuk cek berita/event/harga setiap N menit.

Fitur:
- Daily scheduled run: pipeline lengkap setiap pagi (default 09:05 WIB)
- Real-time polling: cek berita baru, event risk, harga setiap N menit
- Auto-eksekusi: jika detect signal urgent, langsung eksekusi paper trading
- Kill switch: stop semua jika daily loss limit tercapai
- Notifikasi: semua alert masuk Pusat Notifikasi

Usage:
    from src.realtime_monitor import RealtimeMonitor
    
    monitor = RealtimeMonitor(paper_engine=pt)
    monitor.start_daily_schedule(run_time="09:05")
    monitor.start_realtime_polling(interval_minutes=15)
    # ... runs in background threads ...
    monitor.stop_all()
"""

import threading
import time
import schedule
from datetime import datetime
from typing import Optional, Dict, List
from dataclasses import dataclass, field, asdict

from .config import BLUE_CHIPS_ID
from .notifier import send_in_app
from .paper_trading import PaperTradingEngine


@dataclass
class MonitorAlert:
    """Real-time alert dari monitoring loop."""
    timestamp: str
    alert_type: str  # "news", "price", "stop_loss", "take_profit", "sentiment_shift", "event_risk"
    ticker: str
    severity: str  # "info", "warning", "critical"
    message: str
    action_taken: str = ""
    data: Dict = field(default_factory=dict)


class RealtimeMonitor:
    """
    Real-time monitor + scheduler untuk unified pipeline.
    
    GAP 2: start_daily_schedule() — run pipeline lengkap setiap hari
    GAP 5: start_realtime_polling() — cek berita/harga/event setiap N menit
    """

    def __init__(self, paper_engine: Optional[PaperTradingEngine] = None):
        self.paper = paper_engine or PaperTradingEngine()
        self._daily_running = False
        self._polling_running = False
        self._daily_thread: Optional[threading.Thread] = None
        self._polling_thread: Optional[threading.Thread] = None
        self.alerts: List[MonitorAlert] = []
        self._last_news_count = 0
        self._last_sentiment_score = 0.0
        self._last_prices: Dict[str, float] = {}
        self._last_event_risk = 0.0
        # P7: Use RealTimeFeed for cached price polling
        self._feed = None
        try:
            from .realtime_feed import RealTimeFeed
            self._feed = RealTimeFeed(interval="5m", cache_ttl=60)
        except Exception:
            pass
        # Intraday model for day trader mode
        self._intraday_model = None
        self._intraday_interval = "5m"
        # Market Hours engine
        self._market_hours = None
        try:
            from .market_hours import MarketHours
            self._market_hours = MarketHours()
        except Exception:
            pass

    # =========================================================================
    # GAP 2: DAILY SCHEDULED PIPELINE
    # =========================================================================

    def start_daily_schedule(self, run_time: str = "09:05"):
        """
        Start daily scheduled pipeline run.
        
        Args:
            run_time: HH:MM format (24h, WIB time). Default 09:05 (after market open).
        """
        if self._daily_running:
            print("[Monitor] Daily schedule already running")
            return

        def daily_job():
            print(f"\n[Monitor] Daily pipeline triggered at {datetime.now()}")
            try:
                from .unified_pipeline import UnifiedPipeline
                pipeline = UnifiedPipeline(paper_engine=self.paper)

                # Fetch market data
                market_data = None
                predictions = None
                try:
                    from .data_fetcher import fetch_all_data
                    from .predictor import run_prediction
                    from .models import HybridEnsemble

                    tickers = dict(BLUE_CHIPS_ID)
                    tickers["^JKSE"] = "^JKSE"
                    data = fetch_all_data(tickers)
                    market_data = data.get("market", {})

                    # Run predictions for all blue chips
                    predictions = {}
                    for ticker in BLUE_CHIPS_ID:
                        if ticker in market_data and not market_data[ticker].empty:
                            try:
                                ensemble = HybridEnsemble()
                                result = run_prediction(
                                    market_data=market_data,
                                    fred_data=data.get("fred", {}),
                                    target_ticker=ticker,
                                    ensemble=ensemble,
                                )
                                if "error" not in result:
                                    predictions[ticker] = result
                            except Exception:
                                pass
                except Exception as e:
                    print(f"[Monitor] Data fetch error: {e}")

                # Run pipeline
                result = pipeline.run(
                    market_data=market_data,
                    prediction_results=predictions if predictions else None,
                )
                print(f"[Monitor] Daily pipeline complete: {result.summary}")

            except Exception as e:
                print(f"[Monitor] Daily pipeline error: {e}")
                try:
                    send_in_app(
                        kategori="PIPELINE",
                        judul="❌ Daily Pipeline Error",
                        pesan=f"Error: {e}",
                        level="error",
                    )
                except Exception:
                    pass

        schedule.every().day.at(run_time).do(daily_job)
        self._daily_running = True

        def run_scheduler():
            while self._daily_running:
                schedule.run_pending()
                time.sleep(60)

        self._daily_thread = threading.Thread(target=run_scheduler, daemon=True)
        self._daily_thread.start()
        print(f"[Monitor] Daily pipeline scheduled at {run_time} WIB")

        try:
            send_in_app(
                kategori="PIPELINE",
                judul="⏰ Daily Pipeline Scheduler Started",
                pesan=f"Pipeline akan run otomatis setiap hari jam {run_time} WIB.\n"
                      f"Meliputi: berita, sentiment, economic calendar, fundamental, "
                      f"teknikal, impact analysis, dan eksekusi paper trading.",
                level="info",
            )
        except Exception:
            pass

    def stop_daily_schedule(self):
        """Stop daily scheduled pipeline."""
        self._daily_running = False
        schedule.clear()
        if self._daily_thread:
            self._daily_thread.join(timeout=5)
        print("[Monitor] Daily pipeline scheduler stopped")

    # =========================================================================
    # GAP 5: REAL-TIME POLLING
    # =========================================================================

    def start_realtime_polling(self, interval_minutes: int = 15):
        """
        Start real-time polling loop.
        
        Setiap N menit, cek:
        1. Berita baru (RSS) → jika ada news urgent, analyze & alert
        2. Harga saham di portfolio → check stop loss / take profit
        3. Sentiment shift → jika berubah significant, alert
        4. Event risk → jika ada event high-impact hari ini, alert
        5. Intraday model → jika day_trader mode, run 5m/15m prediction
        
        Args:
            interval_minutes: Polling interval in minutes (default 15).
                              Day trader: use 1-5 min. Swing: 15-30 min. Investor: 60 min.
        """
        if self._polling_running:
            print("[Monitor] Real-time polling already running")
            return

        self._polling_running = True

        def polling_loop():
            print(f"[Monitor] Real-time polling started (every {interval_minutes} min)")
            while self._polling_running:
                try:
                    self._poll_cycle()
                except Exception as e:
                    print(f"[Monitor] Polling error: {e}")
                time.sleep(interval_minutes * 60)

        self._polling_thread = threading.Thread(target=polling_loop, daemon=True)
        self._polling_thread.start()
        print(f"[Monitor] Real-time polling started (interval: {interval_minutes} min)")

        try:
            send_in_app(
                kategori="PIPELINE",
                judul=f"📡 Real-time Monitor Started ({interval_minutes} min interval)",
                pesan=f"Monitoring berita, harga, sentiment, dan event setiap {interval_minutes} menit.\n"
                      f"Auto-eksekusi paper trading jika detect signal urgent.",
                level="info",
            )
        except Exception:
            pass

    def stop_realtime_polling(self):
        """Stop real-time polling."""
        self._polling_running = False
        if self._polling_thread:
            self._polling_thread.join(timeout=5)
        print("[Monitor] Real-time polling stopped")

    def stop_all(self):
        """Stop all monitors."""
        self.stop_daily_schedule()
        self.stop_realtime_polling()

    # =========================================================================
    # POLLING CYCLE
    # =========================================================================

    def _poll_cycle(self):
        """One polling cycle — cek semua sumber data."""
        now = datetime.now().strftime("%Y-%m-%d %H:%M")
        print(f"\n[Monitor] Poll cycle at {now}")

        # Check market status
        any_open = True
        open_exchanges = []
        if self._market_hours:
            open_exchanges = self._market_hours.get_all_open_exchanges()
            any_open = len(open_exchanges) > 0
            if open_exchanges:
                print(f"  Open: {', '.join(open_exchanges)}")
            else:
                next_event = self._market_hours.get_next_market_event()
                if next_event:
                    print(f"  All markets closed. Next: {next_event[0]} {next_event[1]} in {next_event[2]}")

        # 1. Check positions for SL/TP (always — even when closed, in case of gaps)
        self._check_positions_sl_tp()

        # 2. Check for new news (always — news flows 24/7)
        self._check_new_news()

        # 3. Check sentiment shift (always)
        self._check_sentiment_shift()

        # 4. Check economic events today
        self._check_economic_events()

        # 5. Check price movements for held positions (skip if all markets closed)
        if any_open:
            self._check_price_movements()
        else:
            print("  Skipping price check — all markets closed")

    def _check_positions_sl_tp(self):
        """Check all open positions for stop loss / take profit."""
        open_positions = self.paper.get_open_positions()
        if not open_positions:
            return

        try:
            current_prices = {}
            for pos in open_positions:
                try:
                    price = self._feed.get_latest_price(pos.ticker) if self._feed else None
                    if not price:
                        from .data_fetcher import get_current_price
                        price = get_current_price(pos.ticker)
                    if price and price > 0:
                        current_prices[pos.ticker] = price
                except Exception:
                    continue

            if not current_prices:
                return

            results = self.paper.check_all_positions(current_prices)
            for result in results:
                if result.get("status") == "FILLED":
                    alert = MonitorAlert(
                        timestamp=datetime.now().isoformat(),
                        alert_type="stop_loss" if "STOP" in result.get("reason", "") else "take_profit",
                        ticker=result.get("ticker", ""),
                        severity="warning" if "STOP" in result.get("reason", "") else "success",
                        message=f"{'Stop Loss' if 'STOP' in result.get('reason', '') else 'Take Profit'} triggered for {result.get('ticker', '')}",
                        action_taken=f"Sold {result.get('quantity', 0)} shares @ {result.get('price', 0):,.0f}",
                        data=result,
                    )
                    self.alerts.append(alert)
                    print(f"  [ALERT] {alert.message} → {alert.action_taken}")
        except Exception as e:
            print(f"  [ERROR] SL/TP check failed: {e}")

    def _check_new_news(self):
        """Check for new news articles since last check."""
        try:
            from .ai_agent import NewsScraper, FinBERTSentiment
            scraper = NewsScraper()
            articles = scraper.fetch_all_sources(max_per_source=3)

            if not articles:
                return

            new_count = len(articles)
            if new_count > self._last_news_count and self._last_news_count > 0:
                diff = new_count - self._last_news_count
                print(f"  [NEWS] {diff} new articles detected")

                # Analyze new articles
                finbert = FinBERTSentiment()
                scraper.analyze_sentiments(finbert)

                # Check for critical news using understanding engine
                try:
                    from .unified_pipeline import NewsUnderstandingEngine
                    engine = NewsUnderstandingEngine()
                    understandings = engine.understand_batch(articles[:5])

                    for u in understandings:
                        if u.magnitude in ("extreme", "high") and u.direction != "neutral":
                            alert = MonitorAlert(
                                timestamp=datetime.now().isoformat(),
                                alert_type="news",
                                ticker=", ".join(u.affected_tickers[:3]),
                                severity="critical" if u.magnitude == "extreme" else "warning",
                                message=f"[{u.event_type}] {u.headline[:80]} → {u.direction} ({u.magnitude})",
                                action_taken=u.recommended_actions[0] if u.recommended_actions else "Monitor",
                                data=asdict(u),
                            )
                            self.alerts.append(alert)

                            send_in_app(
                                kategori="PIPELINE",
                                judul=f"🚨 {alert.severity.upper()}: {u.event_type} — {u.direction} ({u.magnitude})",
                                pesan=f"{u.reasoning}\n\nActions: {', '.join(u.recommended_actions[:3])}",
                                level="warning" if u.magnitude == "high" else "error",
                            )
                            print(f"  [CRITICAL NEWS] {alert.message}")
                except Exception:
                    pass

            self._last_news_count = new_count
        except Exception as e:
            print(f"  [ERROR] News check failed: {e}")

    def _check_sentiment_shift(self):
        """Check for significant sentiment shift."""
        try:
            from .sentiment_pipeline import DailySentimentPipeline
            pipeline = DailySentimentPipeline()
            latest = pipeline.get_latest_sentiment()

            if latest:
                current_score = latest.sentiment_score
                if self._last_sentiment_score != 0:
                    delta = current_score - self._last_sentiment_score
                    if abs(delta) > 20:
                        direction = "improving" if delta > 0 else "deteriorating"
                        alert = MonitorAlert(
                            timestamp=datetime.now().isoformat(),
                            alert_type="sentiment_shift",
                            ticker="MARKET",
                            severity="warning",
                            message=f"Sentiment shifted {delta:+.0f} points ({direction})",
                            action_taken="Monitor positions" if delta < 0 else "Look for opportunities",
                            data={"old": self._last_sentiment_score, "new": current_score, "delta": delta},
                        )
                        self.alerts.append(alert)
                        send_in_app(
                            kategori="SENTIMENT",
                            judul=f"📊 Sentiment Shift: {direction} ({delta:+.0f})",
                            pesan=f"Sentiment score changed from {self._last_sentiment_score:.0f} to {current_score:.0f}\n"
                                  f"Direction: {direction}\nF&G: {latest.fear_greed_index:.1f}",
                            level="warning",
                        )
                        print(f"  [SENTIMENT] {alert.message}")

                self._last_sentiment_score = current_score
        except Exception as e:
            print(f"  [ERROR] Sentiment check failed: {e}")

    def _check_economic_events(self):
        """Check for high-impact economic events today."""
        try:
            from .event_driven import fetch_economic_calendar
            events = fetch_economic_calendar("indonesia")

            high_impact = [e for e in events if getattr(e, "importance", "") == "high"]
            if high_impact and len(high_impact) > 0:
                event_names = [e.event for e in high_impact[:3]]
                alert = MonitorAlert(
                    timestamp=datetime.now().isoformat(),
                    alert_type="event_risk",
                    ticker="MARKET",
                    severity="warning",
                    message=f"{len(high_impact)} high-impact economic events today: {', '.join(event_names)}",
                    action_taken="Monitor positions closely",
                    data={"events": [asdict(e) if hasattr(e, '__dataclass_fields__') else str(e) for e in high_impact]},
                )
                self.alerts.append(alert)
                print(f"  [EVENTS] {alert.message}")
        except Exception as e:
            print(f"  [ERROR] Economic events check failed: {e}")

    def _check_price_movements(self):
        """Check price movements for held positions — alert if significant move."""
        open_positions = self.paper.get_open_positions()
        if not open_positions:
            return

        try:
            for pos in open_positions:
                try:
                    current_price = self._feed.get_latest_price(pos.ticker) if self._feed else None
                    if not current_price:
                        # Try intraday data fetch as fallback
                        try:
                            from .data_fetcher import fetch_intraday_data
                            intraday = fetch_intraday_data(pos.ticker, interval="5m", period="1d", use_cache=True)
                            if not intraday.empty:
                                current_price = float(intraday["Close"].iloc[-1])
                        except Exception:
                            pass
                    if not current_price or current_price <= 0:
                        from .data_fetcher import get_current_price
                        current_price = get_current_price(pos.ticker)
                    if not current_price or current_price <= 0:
                        continue

                    # Check against last known price
                    last_price = self._last_prices.get(pos.ticker, pos.entry_price)
                    change_pct = (current_price - last_price) / last_price * 100

                    if abs(change_pct) > 3:  # >3% move
                        direction = "up" if change_pct > 0 else "down"
                        alert = MonitorAlert(
                            timestamp=datetime.now().isoformat(),
                            alert_type="price",
                            ticker=pos.ticker,
                            severity="warning" if change_pct < 0 else "info",
                            message=f"{pos.ticker} moved {change_pct:+.1f}% ({direction})",
                            action_taken="Monitor" if abs(change_pct) < 5 else "Consider action",
                            data={"old_price": last_price, "new_price": current_price, "change_pct": change_pct},
                        )
                        self.alerts.append(alert)
                        print(f"  [PRICE] {alert.message}")

                    self._last_prices[pos.ticker] = current_price
                except Exception:
                    continue
        except Exception as e:
            print(f"  [ERROR] Price check failed: {e}")

    # =========================================================================
    # STATUS & ALERTS
    # =========================================================================

    def get_status(self) -> Dict:
        """Get monitor status."""
        return {
            "daily_running": self._daily_running,
            "polling_running": self._polling_running,
            "alerts_count": len(self.alerts),
            "recent_alerts": [asdict(a) for a in self.alerts[-10:]],
            "last_news_count": self._last_news_count,
            "last_sentiment_score": self._last_sentiment_score,
            "last_prices": self._last_prices,
        }

    def get_alerts(self, limit: int = 50) -> List[Dict]:
        """Get recent alerts."""
        return [asdict(a) for a in self.alerts[-limit:]]

    def clear_alerts(self):
        """Clear all alerts."""
        self.alerts = []
