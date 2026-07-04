"""Trading Agent page — OpenClaw-style autonomous monitoring & execution."""
import streamlit as st
import pandas as pd

from ..ui_components import section_header
from ..trading_agent import TradingAgent, SafetyGuardrails
from ..paper_trading import PaperTradingEngine


def render_trading_agent():
    section_header("🤖", "Trading Agent — Autonomous Monitoring & Paper Trading")
    st.caption("OpenClaw-style agent: monitor pasar → analisis → keputusan → eksekusi (paper) → notifikasi. "
               "Auto-monitoring harian dengan safety guardrails & virtual portfolio.")

    # Initialize agent in session state
    if "trading_agent" not in st.session_state:
        guardrails = SafetyGuardrails()
        paper = PaperTradingEngine()
        agent = TradingAgent(paper_engine=paper, guardrails=guardrails, auto_execute=False)
        st.session_state["trading_agent"] = agent

    agent: TradingAgent = st.session_state["trading_agent"]
    status = agent.get_status()

    col1, col2 = st.columns([1, 3])

    with col1:
        section_header("⚙️", "Konfigurasi Agent")
        auto_execute = st.checkbox("Auto-Execute Trade", value=agent.auto_execute)
        agent.auto_execute = auto_execute

        st.markdown("---")
        st.subheader("Safety Guardrails")
        g = agent.guardrails
        g.max_daily_trades = st.number_input("Max Trade/Hari", value=g.max_daily_trades, min_value=1, max_value=10)
        g.max_position_pct = st.slider("Max Position (%)", 5, 50, int(g.max_position_pct * 100)) / 100
        g.min_confidence_to_trade = st.slider("Min Confidence", 0.5, 0.95, g.min_confidence_to_trade, 0.05)
        g.daily_loss_limit_pct = st.slider("Daily Loss Limit (%)", 1, 20, int(g.daily_loss_limit_pct * 100)) / 100

        st.markdown("---")
        col_ks1, col_ks2 = st.columns(2)
        with col_ks1:
            if st.button("🚨 KILL SWITCH", type="primary"):
                agent.activate_kill_switch()
                st.rerun()
        with col_ks2:
            if st.button("✅ Reset"):
                agent.deactivate_kill_switch()
                st.rerun()

        st.markdown("---")
        run_time = st.text_input("Jadwal Harian (WIB)", value="09:05")
        col_s1, col_s2 = st.columns(2)
        with col_s1:
            if st.button("▶️ Start Schedule"):
                agent.start_scheduled(run_time=run_time)
                st.success(f"Agent dijadwalkan setiap hari {run_time} WIB")
        with col_s2:
            if st.button("⏹️ Stop"):
                agent.stop_scheduled()
                st.info("Agent dihentikan")

        st.markdown("---")
        st.subheader("💼 Paper Trading")
        pt_stats = agent.paper.get_stats()
        st.metric("Virtual Cash", f"Rp {pt_stats['cash']:,.0f}")
        if st.button("🔄 Reset Portfolio"):
            agent.paper.reset()
            st.rerun()

    with col2:
        section_header("📊", "Status Agent")

        col_a, col_b, col_c, col_d = st.columns(4)
        state_emoji = {"idle": "💤", "monitoring": "📡", "analyzing": "🔍", "deciding": "🤔",
                       "executing": "⚡", "error": "🚨"}.get(status["state"], "❓")
        col_a.metric("State", f"{state_emoji} {status['state'].upper()}")
        col_b.metric("Auto-Execute", "ON" if status["auto_execute"] else "OFF")
        col_c.metric("Total Runs", status["memory"]["total_runs"])
        col_d.metric("Open Positions", status["open_positions"])

        # Paper Trading Portfolio
        pt = status["paper_trading"]
        col_e, col_f, col_g, col_h = st.columns(4)
        col_e.metric("Cash", f"Rp {pt['cash']:,.0f}")
        col_f.metric("Total PnL", f"Rp {pt['total_realized_pnl']:,.0f}", delta=f"{pt['total_realized_pnl_pct']:+.2f}%")
        col_g.metric("Win Rate", f"{pt['win_rate']:.1f}% ({pt['winning_trades']}/{pt['total_trades']})")
        kill_active = status['guardrails']['kill_switch']
        col_h.metric("Kill Switch", "🚨 ACTIVE" if kill_active else "✅ OFF")

        st.markdown("---")
        section_header("�", "Virtual Portfolio")

        # Open positions
        open_positions = agent.paper.get_open_positions()
        if open_positions:
            pos_data = []
            for p in open_positions:
                pos_data.append({
                    "Ticker": p.ticker,
                    "Side": p.side,
                    "Qty": p.quantity,
                    "Entry": f"{p.entry_price:,.2f}",
                    "Current": f"{p.current_price:,.2f}",
                    "Stop Loss": f"{p.stop_loss:,.2f}",
                    "Take Profit": f"{p.take_profit:,.2f}",
                    "Unrealized PnL": f"{p.unrealized_pnl:,.0f}",
                    "PnL %": f"{p.unrealized_pnl_pct:+.2f}%",
                    "Entry Date": p.entry_date[:10],
                })
            st.dataframe(pd.DataFrame(pos_data), use_container_width=True, hide_index=True)
        else:
            st.info("📭 Tidak ada posisi terbuka.")

        # Trade history
        if agent.paper.trade_history:
            st.markdown("---")
            section_header("📜", "Trade History")
            hist_data = []
            for t in agent.paper.trade_history[-15:]:
                hist_data.append({
                    "Time": t["timestamp"][:19],
                    "Ticker": t["ticker"],
                    "Side": t["side"],
                    "Qty": t["quantity"],
                    "Price": f"{t['price']:,.2f}",
                    "Status": t["status"],
                    "PnL": f"{t.get('pnl', 0):,.0f}" if t.get("pnl") else "-",
                    "Reason": t.get("reason", ""),
                })
            st.dataframe(pd.DataFrame(hist_data), use_container_width=True, hide_index=True)

        st.markdown("---")
        section_header("�️", "Guardrails Detail")
        guardrails = status["guardrails"]
        col_g1, col_g2, col_g3 = st.columns(3)
        col_g1.metric("Consecutive Losses", f"{guardrails['consecutive_losses']}")
        col_g2.metric("Trades Today", f"{guardrails['trades_today']}/{guardrails['max_daily_trades']}")
        col_g3.metric("Daily PnL", f"Rp {pt['daily_pnl']:,.0f}")

        st.markdown("---")
        section_header("📜", "Decision History")
        if agent.memory.decisions:
            df_decisions = pd.DataFrame(agent.memory.decisions[-20:])
            display_cols = ["timestamp", "signal", "confidence", "action", "reason"]
            available = [c for c in display_cols if c in df_decisions.columns]
            st.dataframe(df_decisions[available], use_container_width=True, hide_index=True)
        else:
            st.info("Belum ada keputusan. Jalankan agent untuk mulai monitoring.")

        st.markdown("---")
        section_header("🔧", "Manual Run")
        st.caption("Jalankan satu cycle agent secara manual dengan data terbaru")
        if st.button("🚀 Run Manual Cycle", type="primary"):
            with st.spinner("Menjalankan agent cycle..."):
                try:
                    from ..data_fetcher import fetch_all_data
                    from ..predictor import run_prediction
                    from ..config import TARGET_TICKER

                    data = fetch_all_data(period="2y")
                    if data and "market" in data:
                        result = run_prediction(
                            market_data=data["market"],
                            fred_data=data.get("fred"),
                            target_ticker=TARGET_TICKER,
                        )
                        if "error" not in result:
                            decision = agent.run_cycle(data["market"], result)
                            st.success(f"Cycle selesai: {decision['action']}")
                            st.json(decision)
                        else:
                            st.error(f"Prediction error: {result['error']}")
                    else:
                        st.error("Gagal mengambil data pasar")
                except Exception as e:
                    st.error(f"Error: {e}")

        st.markdown("---")
        st.info(
            "💡 **Cara setup:**\n"
            "1. Notifikasi otomatis tersimpan di **Pusat Notifikasi** (tidak butuh API)\n"
            "2. Paper Trading: virtual portfolio Rp 100jt, auto SL/TP, tracking PnL\n"
            "3. Aktifkan **Auto-Execute** untuk simulasi trading otomatis berdasarkan sinyal ML\n"
            "4. Set `TELEGRAM_BOT_TOKEN` dan `TELEGRAM_CHAT_ID` di `.env` untuk notifikasi opsional ke Telegram\n"
            "5. Start schedule untuk monitoring harian otomatis (default 09:05 WIB)\n"
            "6. **Kill Switch** = hentikan semua trading darurat"
        )
