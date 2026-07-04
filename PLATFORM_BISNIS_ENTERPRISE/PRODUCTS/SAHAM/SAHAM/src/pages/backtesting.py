"""Backtesting page — run backtest and trading simulation."""
import streamlit as st
import plotly.graph_objects as go

from ..ui_components import load_market_data, render_step_progress, tt, section_header
from ..config import BLUE_CHIPS_ID
from ..backtesting import run_backtest, simulate_trading


def render_backtesting():
    section_header("🧪", f"{tt('Backtesting')} & Simulasi Trading")
    col1, col2 = st.columns([1, 3])

    with col1:
        target_options = {"IHSG (^JKSE)": "^JKSE"}
        target_options.update({f"{name} ({ticker})": ticker for name, ticker in BLUE_CHIPS_ID.items()})
        selected = st.selectbox("Pilih Target", list(target_options.keys()))
        target_ticker = target_options[selected]
        initial_capital = st.number_input("Modal Awal (Rp)", value=100_000_000, step=10_000_000)

    if st.button("🚀 Jalankan Backtest", type="primary"):
        render_step_progress([
            ("active", "Mengambil data pasar", ""),
            ("pending", "Pra-pemrosesan & rekayasa fitur", ""),
            ("pending", "Melatih model ensemble", ""),
            ("pending", "Menjalankan simulasi backtest", ""),
        ])
        with st.spinner("Menjalankan backtesting..."):
            md = load_market_data(period="2y")
            if not md:
                st.error("Gagal mengambil data.")
            else:
                bt_results = run_backtest(market_data=md, fred_data={}, target_ticker=target_ticker)
                sim_results = simulate_trading(market_data=md, fred_data={}, target_ticker=target_ticker, initial_capital=initial_capital)
                st.session_state["bt_results"] = bt_results
                st.session_state["sim_results"] = sim_results
                render_step_progress([
                    ("done", "Mengambil data pasar", f"{len(md)} ticker"),
                    ("done", "Pra-pemrosesan & rekayasa fitur", "135 fitur"),
                    ("done", "Melatih model ensemble", "RF + XGB"),
                    ("done", "Menjalankan simulasi backtest", "Selesai"),
                ])

    with col2:
        bt = st.session_state.get("bt_results")
        sim = st.session_state.get("sim_results")
        if not bt:
            st.info("Klik 'Jalankan Backtest' untuk melihat hasil.")
            return

        st.subheader("📊 Hasil Backtest")
        col_a, col_b, col_c = st.columns(3)
        col_a.metric(f"{tt('Directional Accuracy')}", f"{bt.get('directional_accuracy', 0)}%")
        col_b.metric("Total Prediksi", f"{bt.get('total_predictions', 0)}")
        if "MAPE" in bt:
            col_c.metric(f"{tt('MAPE')}", f"{bt['MAPE']}%")

        st.markdown("---")
        if sim and "error" not in sim:
            section_header("💹", "Simulasi Paper Trading")
            col_a, col_b, col_c, col_d = st.columns(4)
            col_a.metric("Modal Awal", f"Rp {sim['initial_capital']:,.0f}")
            col_b.metric("Modal Akhir", f"Rp {sim['final_capital']:,.0f}")
            col_c.metric("Total Return", f"{sim['total_return']:.2f}%")
            col_d.metric(f"{tt('Buy & Hold')}", f"{sim['buy_hold_return']:.2f}%")
            col_e, col_f = st.columns(2)
            col_e.metric("Jumlah Transaksi", f"{sim['trades']}")
            col_f.metric(f"{tt('Max Drawdown')}", f"{sim['max_drawdown']:.2f}%")
            if sim.get("portfolio_values"):
                fig_port = go.Figure()
                fig_port.add_trace(go.Scatter(y=sim["portfolio_values"], mode="lines", name="Portofolio", line=dict(color="cyan")))
                fig_port.update_layout(title=f"{tt('Equity Curve')}", xaxis_title="Hari", yaxis_title="Nilai (Rp)", height=300, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
                st.plotly_chart(fig_port, use_container_width=True)
        st.info("💡 Akurasi backtest biasanya lebih tinggi dari real-time. Ekspektasi realistis: 50-55% akurasi arah.")
