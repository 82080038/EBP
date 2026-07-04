"""Unified Pipeline page — satu tombol untuk run semua analisis & eksekusi."""
import streamlit as st
import pandas as pd

from ..ui_components import section_header
from ..unified_pipeline import UnifiedPipeline
from ..paper_trading import PaperTradingEngine
from ..config import BLUE_CHIPS_ID
from ..realtime_monitor import RealtimeMonitor


def render_unified_pipeline():
    section_header("🔄", "Unified Daily Pipeline")
    st.caption(
        "Satu klik untuk: baca berita → sentiment → economic calendar → fundamental → "
        "teknikal → impact analysis → eksekusi paper trading. "
        "Aplikasi membaca, memahami, dan bertindak."
    )

    # Initialize
    if "paper_engine" not in st.session_state:
        st.session_state["paper_engine"] = PaperTradingEngine()
    if "pipeline_result" not in st.session_state:
        st.session_state["pipeline_result"] = None
    if "rt_monitor" not in st.session_state:
        st.session_state["rt_monitor"] = RealtimeMonitor(st.session_state["paper_engine"])

    pt = st.session_state["paper_engine"]
    monitor = st.session_state["rt_monitor"]

    # === RUN BUTTON ===
    col1, col2 = st.columns([2, 1])
    with col1:
        st.markdown("### 🚀 Jalankan Pipeline Lengkap")
        st.write(
            "Pipeline akan menjalankan:\n"
            "1. 📰 **Scrape berita** dari Kontan, CNBC, Reuters, Bloomberg\n"
            "2. 🧠 **Sentiment analysis** dengan FinBERT (AI model)\n"
            "3. 📅 **Economic calendar** — BI Rate, CPI, GDP, trade balance\n"
            "4. 📊 **Fundamental data** — PE, ROE, dividen, NPL\n"
            "5. 📈 **Technical analysis** — RSI, MA, MACD, Bollinger\n"
            "6. 🎯 **Smart impact analysis** — berita/event → map ke saham portfolio\n"
            "7. ⚡ **Eksekusi paper trading** — buy/sell/reduce berdasarkan semua data\n"
            "8. 🔔 **Notifikasi** — semua hasil masuk Pusat Notifikasi"
        )

        run_with_ml = st.checkbox(
            "Sertakan ML Prediction (lebih lama, lebih akurat)",
            value=False,
            help="Jika dicentang, pipeline akan run ML prediction per ticker. Butuh 2-5 menit."
        )

        if st.button("🚀 RUN PIPELINE", type="primary"):
            pipeline = UnifiedPipeline(paper_engine=pt)

            with st.spinner("📡 Scraping berita & sentiment..."):
                if run_with_ml:
                    with st.spinner("📊 Running ML predictions untuk blue chips..."):
                        try:
                            from ..data_fetcher import fetch_all_data
                            from ..predictor import run_prediction
                            from ..models import HybridEnsemble

                            tickers = dict(BLUE_CHIPS_ID)
                            tickers["^JKSE"] = "^JKSE"
                            market_data = fetch_all_data(tickers)

                            predictions = {}
                            for ticker in BLUE_CHIPS_ID:
                                if ticker in market_data and not market_data[ticker].empty:
                                    try:
                                        ensemble = HybridEnsemble()
                                        result = run_prediction(
                                            market_data=market_data,
                                            fred_data={},
                                            target_ticker=ticker,
                                            ensemble=ensemble,
                                        )
                                        if "error" not in result:
                                            predictions[ticker] = result
                                    except Exception as e:
                                        st.warning(f"Prediksi {ticker} gagal: {e}")

                            result = pipeline.run(
                                market_data=market_data,
                                prediction_results=predictions if predictions else None,
                            )
                        except Exception as e:
                            st.error(f"ML prediction error: {e}")
                            result = pipeline.run()
                else:
                    result = pipeline.run()

                st.session_state["pipeline_result"] = result
                st.success("✅ Pipeline selesai!")
                st.rerun()

    with col2:
        st.markdown("### 💼 Portfolio Status")
        stats = pt.get_stats()
        st.metric("Cash", f"Rp {stats['cash']:,.0f}")
        st.metric("Total PnL", f"Rp {stats['total_realized_pnl']:,.0f}",
                  delta=f"{stats['total_realized_pnl_pct']:+.2f}%")
        st.metric("Open Positions", stats["open_positions"])
        st.metric("Win Rate", f"{stats['win_rate']:.1f}%")

    st.markdown("---")

    # === RESULTS ===
    result = st.session_state.get("pipeline_result")
    if result:
        section_header("📋", "Hasil Pipeline")

        # Dashboard
        col_a, col_b, col_c, col_d = st.columns(4)
        col_a.metric("📰 Articles", result.news_articles_count)
        col_b.metric("😰 Sentiment", f"{result.sentiment_score:.0f}",
                      delta=f"F&G: {result.fear_greed_index:.0f}")
        col_c.metric("📅 Economic Events", result.economic_events_count)
        col_d.metric("⚠️ Event Risk", f"{result.event_risk_score:.0f}/100")

        col_e, col_f, col_g, col_h = st.columns(4)
        col_e.metric("📊 Earnings", result.earnings_count)
        col_f.metric("💰 Corp Actions", result.corporate_actions_count)
        col_g.metric("🎯 Analyst Recs", result.analyst_recs_count)
        col_h.metric("⚡ Actions Taken", len(result.portfolio_actions))

        # Errors
        if result.errors:
            with st.expander(f"⚠️ Errors ({len(result.errors)})"):
                for err in result.errors:
                    st.error(err)

        # News Understanding (GAP 3)
        if result.news_understandings:
            st.markdown("---")
            section_header("🧠", "News Understanding — Pemahaman Berita")
            st.caption("Aplikasi tidak hanya baca sentiment, tapi memahami: event type, magnitude, sektor terdampak, dan rekomendasi action")

            nu_data = []
            for u in result.news_understandings:
                nu_data.append({
                    "Headline": u["headline"][:60],
                    "Event Type": u["event_type"],
                    "Sentiment": u["sentiment"],
                    "Magnitude": u["magnitude"],
                    "Direction": u["direction"],
                    "Sectors": ", ".join(u["affected_sectors"][:3]),
                    "Tickers": ", ".join(u["affected_tickers"][:3]),
                    "Horizon": u["time_horizon"],
                    "Actions": u["recommended_actions"][:2],
                })
            st.dataframe(pd.DataFrame(nu_data), use_container_width=True, hide_index=True)

            # Show detailed reasoning for top items
            with st.expander("📖 Detailed Reasoning"):
                for u in result.news_understandings[:5]:
                    st.markdown(f"**{u['headline']}**")
                    st.write(u["reasoning"])
                    if u["recommended_actions"]:
                        st.write("**Recommended actions:**")
                        for action in u["recommended_actions"]:
                            st.write(f"- {action}")
                    st.markdown("---")

        # Portfolio-Specific Impact (GAP 4)
        if result.portfolio_impact_summary:
            st.markdown("---")
            section_header("🎯", "Portfolio-Specific Impact")
            st.caption("Berita dipetakan langsung ke saham yang Anda pegang — dengan reasoning spesifik")
            st.info(result.portfolio_impact_summary)

        # Impact Analysis
        if result.impact_assessments:
            st.markdown("---")
            section_header("🎯", "Smart Impact Analysis")
            st.caption("Berita/event → dipetakan ke saham di portfolio → rekomendasi action")

            impact_df = pd.DataFrame(result.impact_assessments)
            # Color code direction
            impact_df["Direction"] = impact_df["direction"].map({
                "bullish": "🟢 Bullish", "bearish": "🔴 Bearish", "neutral": "🟡 Neutral"
            })
            impact_df["Confidence"] = impact_df["confidence"].apply(lambda x: f"{x:.0%}")
            impact_df["Action"] = impact_df["action_recommendation"].map({
                "reduce": "📉 Reduce", "close": "❌ Close", "consider_buy": "🟢 Consider Buy",
                "hold": "⏸️ Hold", "avoid": "🚫 Avoid"
            })
            display_cols = ["ticker", "Direction", "Confidence", "source", "Action", "reason"]
            display_cols = [c for c in display_cols if c in impact_df.columns]
            st.dataframe(impact_df[display_cols], use_container_width=True, hide_index=True)

        # Portfolio Actions
        if result.portfolio_actions:
            st.markdown("---")
            section_header("⚡", "Portfolio Actions — Eksekusi")
            action_data = []
            for a in result.portfolio_actions:
                res = a.get("result", {})
                action_data.append({
                    "Ticker": a["ticker"],
                    "Action": a["action"],
                    "Qty": a.get("quantity", res.get("quantity", "")),
                    "Status": res.get("status", ""),
                    "PnL": f"Rp {res.get('pnl', 0):,.0f}" if res.get("pnl") else "-",
                    "Reason": a.get("reason", "")[:100],
                })
            st.dataframe(pd.DataFrame(action_data), use_container_width=True, hide_index=True)

        # Summary
        st.markdown("---")
        st.info(f"📋 **Summary:** {result.summary}")
    else:
        st.info("👆 Klik **RUN PIPELINE** untuk menjalankan analisis lengkap.")

    # === REAL-TIME MONITOR (GAP 2 & 5) ===
    st.markdown("---")
    section_header("📡", "Real-time Monitor & Scheduler")
    st.caption(
        "GAP 2: Auto-run pipeline setiap hari terjadwal. "
        "GAP 5: Real-time polling untuk cek berita/harga/event setiap N menit."
    )

    col_m1, col_m2 = st.columns(2)

    with col_m1:
        st.markdown("#### ⏰ Daily Scheduler (GAP 2)")
        st.write("Pipeline lengkap otomatis setiap hari pada jam yang ditentukan.")
        schedule_time = st.text_input("Jam Run (WIB)", value="09:05", help="Format HH:MM 24h")

        col_s1, col_s2 = st.columns(2)
        with col_s1:
            if st.button("▶️ Start Daily", key="start_daily"):
                monitor.start_daily_schedule(run_time=schedule_time)
                st.success(f"✅ Daily pipeline scheduled at {schedule_time} WIB")
        with col_s2:
            if st.button("⏹️ Stop Daily", key="stop_daily"):
                monitor.stop_daily_schedule()
                st.warning("⏹️ Daily scheduler stopped")

    with col_m2:
        st.markdown("#### 📡 Real-time Polling (GAP 5)")
        st.write("Cek berita, harga, sentiment, dan event setiap N menit.")
        interval = st.slider("Interval (menit)", min_value=5, max_value=60, value=15, step=5)

        col_p1, col_p2 = st.columns(2)
        with col_p1:
            if st.button("▶️ Start Polling", key="start_poll"):
                monitor.start_realtime_polling(interval_minutes=interval)
                st.success(f"✅ Real-time polling started ({interval} min)")
        with col_p2:
            if st.button("⏹️ Stop Polling", key="stop_poll"):
                monitor.stop_realtime_polling()
                st.warning("⏹️ Polling stopped")

    # Monitor status
    mon_status = monitor.get_status()
    col_ms1, col_ms2, col_ms3 = st.columns(3)
    col_ms1.metric("Daily Scheduler", "🟢 Running" if mon_status["daily_running"] else "🔴 Off")
    col_ms2.metric("Real-time Polling", "🟢 Running" if mon_status["polling_running"] else "🔴 Off")
    col_ms3.metric("Alerts", mon_status["alerts_count"])

    # Recent alerts
    if mon_status["alerts_count"] > 0:
        with st.expander(f"🚨 Recent Alerts ({mon_status['alerts_count']})"):
            alerts_df = pd.DataFrame(mon_status["recent_alerts"])
            if not alerts_df.empty:
                display_cols = [c for c in ["timestamp", "alert_type", "ticker", "severity", "message", "action_taken"] if c in alerts_df.columns]
                st.dataframe(alerts_df[display_cols], use_container_width=True, hide_index=True)
            if st.button("🗑️ Clear Alerts"):
                monitor.clear_alerts()
                st.rerun()

    st.markdown("---")
    st.caption(
        "⚠️ **Disclaimer:** Paper trading = simulasi. Bukan saran investasi. "
        "Pipeline ini membaca data publik (RSS, yfinance, Trading Economics) dan "
        "menganalisis dengan AI (FinBERT + rules-based). Hasil bersifat probabilistik."
    )
