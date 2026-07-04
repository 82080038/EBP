"""
Streamlit Page: Market Simulation — walk-forward trading simulation results.
"""
import streamlit as st
import pandas as pd
from datetime import datetime


def render_simulation():
    st.markdown("# 🎮 Simulasi Pasar Modal")
    st.markdown("*Walk-forward trading simulation: train 1 month, simulate 3 months, 1 day = 1 detik*")
    st.markdown("---")

    col1, col2, col3 = st.columns([2, 2, 1])
    with col1:
        capital = st.number_input("Modal Awal (IDR)", value=10_000_000, step=1_000_000)
    with col2:
        train_end = st.date_input("Tanggal Akhir Training", value=datetime(2026, 1, 31))
    with col3:
        sim_speed = st.number_input("Detik/Hari", value=1, min_value=1, max_value=10)

    col1, col2 = st.columns(2)
    with col1:
        run_sim = st.button("🚀 Jalankan Simulasi", type="primary")
    with col2:
        if st.button("🔄 Refresh Results"):
            st.rerun()

    if run_sim:
        st.session_state["sim_running"] = True
        st.info("Simulasi sedang berjalan...")

        try:
            from src.simulation_engine import MarketSimulation
            sim = MarketSimulation(
                initial_capital=capital,
                train_months=6,
                sim_months=3,
                day_duration_seconds=sim_speed,
                target_ticker="^JKSE",
                broker_name="bca_sekuritas",
                train_end_date=str(train_end),
            )
            # Run simulation with a simple progress hook via day callback
            sim.run()
            st.success("✅ Simulasi selesai!")
            st.session_state["sim_running"] = False
        except Exception as e:
            st.error(f"❌ Simulasi error: {e}")
            st.session_state["sim_running"] = False
        st.rerun()

    # Load results
    from src.simulation_engine import load_simulation_results
    results = load_simulation_results()

    if results is None:
        st.info("Belum ada hasil simulasi. Klik **Jalankan Simulasi** untuk memulai.")
        _show_simulation_info()
        return

    status = results.get("status", "unknown")
    current_day = results.get("current_day", 0)
    total_days = results.get("n_trading_days", 0)

    # Status banner
    if status == "running":
        progress = current_day / max(total_days, 1) if total_days > 0 else 0
        st.warning(f"⏳ Simulasi berjalan... Day {current_day}/{total_days} ({progress:.0%})")
        st.progress(progress)
    elif status == "completed":
        st.success("✅ Simulasi selesai!")
    elif status == "error":
        st.error(f"❌ Simulasi error: {results.get('error', 'Unknown')}")

    # Summary metrics
    st.markdown("---")
    st.markdown("### 📊 Ringkasan Hasil")

    c1, c2, c3, c4, c5 = st.columns(5)
    with c1:
        st.metric("Modal Awal", f"Rp {results.get('initial_capital', 0):,.0f}")
    with c2:
        st.metric("Modal Akhir", f"Rp {results.get('final_capital', 0):,.0f}")
    with c3:
        ret = results.get('total_return_pct', 0)
        st.metric("Total Return", f"{ret:.2f}%", delta=f"{ret:.2f}%")
    with c4:
        bh = results.get('buy_hold_return_pct', 0)
        st.metric("Buy & Hold", f"{bh:.2f}%")
    with c5:
        st.metric("Win Rate", f"{results.get('win_rate', 0):.1f}%")

    c1, c2, c3, c4, c5 = st.columns(5)
    with c1:
        st.metric("Jumlah Trade", results.get('n_trades', 0))
    with c2:
        st.metric("BUY", results.get('n_buys', 0))
    with c3:
        st.metric("SELL", results.get('n_sells', 0))
    with c4:
        st.metric("Max Drawdown", f"{results.get('max_drawdown_pct', 0):.2f}%")
    with c5:
        st.metric("Sharpe Ratio", f"{results.get('sharpe_ratio', 0):.2f}")

    c1, c2, c3 = st.columns(3)
    with c1:
        st.metric("Total Komisi", f"Rp {results.get('total_commission', 0):,.0f}")
    with c2:
        st.metric("Profit Factor", f"{results.get('profit_factor', 0):.2f}")
    with c3:
        st.metric("Avg Confidence", f"{results.get('avg_confidence', 0):.0%}")

    # Equity curve
    st.markdown("---")
    st.markdown("### 📈 Equity Curve")
    equity = results.get("equity_curve", [])
    if equity:
        import plotly.graph_objects as go
        fig = go.Figure()
        fig.add_trace(go.Scatter(
            y=equity,
            mode="lines",
            name="Portfolio Value",
            line=dict(color="#10b981", width=2),
        ))
        fig.update_layout(
            xaxis_title="Trading Day",
            yaxis_title="Portfolio Value (IDR)",
            height=400,
            template="plotly_dark",
        )
        st.plotly_chart(fig, use_container_width=True)

    # Daily log
    st.markdown("---")
    st.markdown("### 📋 Daily Trading Log")

    days = results.get("days", [])
    if days:
        df_days = pd.DataFrame(days)
        display_cols = [
            "day_number", "date", "current_price", "signal", "confidence",
            "ai_score", "regime", "action", "shares_traded", "fill_price",
            "portfolio_value", "unrealized_pnl", "risk_status",
        ]
        available_cols = [c for c in display_cols if c in df_days.columns]
        st.dataframe(
            df_days[available_cols],
            use_container_width=True,
            hide_index=True,
            height=400,
        )

        # Detailed view for selected day
        st.markdown("### 🔍 Detail Per Hari")
        selected_day = st.selectbox(
            "Pilih hari untuk detail:",
            options=range(len(days)),
            format_func=lambda i: f"Day {days[i].get('day_number', i+1)} — {days[i].get('date', '')}",
        )

        if selected_day is not None and selected_day < len(days):
            day = days[selected_day]
            col1, col2, col3, col4 = st.columns(4)
            with col1:
                st.metric("Price", f"{day.get('current_price', 0):,.2f}")
                st.metric("Signal", day.get("signal", "—"))
            with col2:
                st.metric("Confidence", f"{day.get('confidence', 0):.0%}")
                st.metric("AI Score", f"{day.get('ai_score', 0):.1f}")
            with col3:
                st.metric("Action", day.get("action", "—"))
                st.metric("Regime", day.get("regime", "—"))
            with col4:
                st.metric("Portfolio", f"Rp {day.get('portfolio_value', 0):,.0f}")
                st.metric("Risk", day.get("risk_status", "—"))

            if day.get("model_votes"):
                st.markdown("**Model Votes:**")
                st.text(day["model_votes"])

            if day.get("shap_top_features"):
                st.markdown("**SHAP Explanation:**")
                st.text(day["shap_top_features"])

            if day.get("mtf_signal"):
                st.markdown(f"**MTF Signal:** {day['mtf_signal']}")

            if day.get("wyckoff_phase"):
                st.markdown(f"**Wyckoff Phase:** {day['wyckoff_phase']}")

            if day.get("error"):
                st.error(f"Error: {day['error']}")

    # Trades log
    st.markdown("---")
    st.markdown("### 📝 Trade History")
    trades = results.get("trades_log", [])
    if trades:
        df_trades = pd.DataFrame(trades)
        st.dataframe(df_trades, use_container_width=True, hide_index=True)
    else:
        st.info("Tidak ada trade yang dieksekusi.")

    # Timestamps
    st.markdown("---")
    col1, col2 = st.columns(2)
    with col1:
        st.text(f"Started: {results.get('started_at', '—')}")
    with col2:
        st.text(f"Finished: {results.get('finished_at', '—')}")


def _show_simulation_info():
    st.markdown("---")
    st.markdown("### ℹ️ Cara Kerja Simulasi")
    st.markdown("""
    1. **Training**: Model ensemble dilatih 6 bulan data historis (no forward-looking bias)
    2. **Prediction**: Setiap hari, RF + XGBoost + LightGBM memprediksi arah harga dan confidence
    3. **Regime & Trend**: Pasar diklasifikasikan (bull/bear/sideways/crisis), trend filter (MA5/MA20) diterapkan
    4. **Auto-Adjust**: Parameter confidence, position sizing, SL/TP menyesuaikan regime dan win rate terkini
    5. **Execution**: Sinyal BUY/SELL dieksekusi via broker simulator (komisi, slippage, latency, partial fill, short selling)
    6. **Risk Management**: ATR-based stop-loss, take-profit, trailing stop, max drawdown control
    7. **All Features**: ML ensemble, regime detection, MTF, Wyckoff, SHAP, AI score, price forecast
    8. **Duration**: 3 bulan simulasi, 1 hari = 1 detik (default)
    9. **Capital**: Rp 10.000.000 default
    """)
