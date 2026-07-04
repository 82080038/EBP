"""Portfolio Optimizer page — Efficient Frontier, Markowitz optimization."""
import streamlit as st
import pandas as pd
import plotly.graph_objects as go
import plotly.express as px

from ..ui_components import load_market_data, tt, section_header
from ..portfolio import optimize_portfolio


def render_portfolio():
    section_header("💼", f"Optimasi {tt('Portfolio')}")
    st.caption(f"Modern Portfolio Theory: {tt('Efficient Frontier')}, portofolio optimal {tt('Sharpe')}, diversifikasi")

    data = load_market_data(period="2y")
    if not data:
        st.error("Gagal mengambil data.")
        return

    col1, col2 = st.columns([1, 3])
    with col1:
        st.subheader("Pilih Aset")
        available = list(data.keys())
        selected_assets = st.multiselect(f"Aset untuk {tt('Portfolio')}", available, default=["IHSG", "S&P500", "STI", "GOLD"][:4])
        n_sim = st.slider("Jumlah Simulasi", 500, 10000, 3000, step=500)
        risk_free = st.number_input(f"{tt('Risk-Free Rate')} (%)", value=5.0, step=0.5) / 100

    if len(selected_assets) < 2:
        st.warning("Pilih minimal 2 aset untuk optimasi.")
        return

    returns_dict = {}
    for name in selected_assets:
        if name in data and not data[name].empty:
            returns_dict[name] = data[name]["Close"].pct_change().dropna()

    if len(returns_dict) < 2:
        st.warning("Data tidak cukup.")
        return

    returns_df = pd.DataFrame(returns_dict).dropna()

    with st.spinner("Menjalankan optimasi portofolio..."):
        opt = optimize_portfolio(returns_df, risk_free=risk_free, n_portfolios=n_sim)

    if "error" in opt:
        st.error(opt["error"])
        return

    with col2:
        st.subheader(f"{tt('Efficient Frontier')}")
        ef = opt["efficient_frontier"]
        fig = go.Figure()
        fig.add_trace(go.Scatter(
            x=ef["Volatility"] * 100, y=ef["Return"] * 100, mode="markers", name="Portofolio",
            marker=dict(color=ef["Sharpe"], colorscale="Viridis", size=3),
        ))
        ms = opt["max_sharpe_portfolio"]
        mv = opt["min_vol_portfolio"]
        fig.add_trace(go.Scatter(x=[ms["volatility"]], y=[ms["return"]], mode="markers+text", name="Max Sharpe", marker=dict(size=15, color="green", symbol="star"), text=["Sharpe Tertinggi"], textposition="top center"))
        fig.add_trace(go.Scatter(x=[mv["volatility"]], y=[mv["return"]], mode="markers+text", name="Min Vol", marker=dict(size=15, color="blue", symbol="star"), text=["Volatilitas Terendah"], textposition="top center"))
        fig.update_layout(xaxis_title=f"{tt('Volatility')} Tahunan (%)", yaxis_title="Return Tahunan (%)", height=500, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
        st.plotly_chart(fig, use_container_width=True)

        st.markdown("---")
        col_a, col_b = st.columns(2)
        with col_a:
            st.subheader(f"🟢 Portofolio {tt('Sharpe')} Tertinggi")
            st.metric("Return Tahunan", f"{ms['return']}%")
            st.metric(f"{tt('Volatility')}", f"{ms['volatility']}%")
            st.metric(f"{tt('Sharpe')} Ratio", f"{ms['sharpe']}")
            weights_df = pd.DataFrame([{"Aset": k, "Bobot (%)": round(v * 100, 2)} for k, v in ms["weights"].items()]).sort_values("Bobot (%)", ascending=False)
            st.table(weights_df)
            fig_pie = px.pie(weights_df, values="Bobot (%)", names="Aset", title="Alokasi Sharpe Tertinggi")
            fig_pie.update_layout(template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
            st.plotly_chart(fig_pie, use_container_width=True)

        with col_b:
            st.subheader(f"🔵 Portofolio {tt('Volatility')} Terendah")
            st.metric("Return Tahunan", f"{mv['return']}%")
            st.metric(f"{tt('Volatility')}", f"{mv['volatility']}%")
            st.metric(f"{tt('Sharpe')} Ratio", f"{mv['sharpe']}")
            weights_df_mv = pd.DataFrame([{"Aset": k, "Bobot (%)": round(v * 100, 2)} for k, v in mv["weights"].items()]).sort_values("Bobot (%)", ascending=False)
            st.table(weights_df_mv)
            fig_pie_mv = px.pie(weights_df_mv, values="Bobot (%)", names="Aset", title="Alokasi Volatilitas Terendah")
            fig_pie_mv.update_layout(template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
            st.plotly_chart(fig_pie_mv, use_container_width=True)

        st.markdown("---")
        st.subheader("Statistik per Aset")
        asset_stats = pd.DataFrame([{"Aset": k, "Return (%)": v["annual_return"], "Volatilitas (%)": v["annual_volatility"], "Sharpe": v["sharpe"]} for k, v in opt["asset_stats"].items()])
        st.table(asset_stats)
