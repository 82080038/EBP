"""Sentiment page — Fear & Greed Index."""
import streamlit as st
import numpy as np
import plotly.graph_objects as go

from ..ui_components import load_market_data, create_gauge_chart, tt, section_header
from ..sentiment import calc_fear_greed_index


def render_sentiment():
    section_header("😰", f"{tt('Fear & Greed')} — Indeks Sentimen Pasar")
    st.caption("Indeks sentimen pasar skala 0-100 berdasarkan 7 komponen: VIX, Momentum, RSI, Safe Haven, Breadth, Volatility, Harga Minyak")

    data = load_market_data(period="1y")
    if not data:
        st.error("Gagal mengambil data.")
        return

    fg = calc_fear_greed_index(data)
    col1, col2 = st.columns([1, 2])

    with col1:
        st.plotly_chart(create_gauge_chart(fg["composite_score"], f"{fg['emoji']} {fg['label']}"), use_container_width=True)

    with col2:
        st.subheader("Komponen Indeks Sentimen")
        import pandas as pd
        comp_data = []
        for name, info in fg["components"].items():
            comp_data.append({"Komponen": name, "Skor (0-100)": info["score"], "Nilai": info["value"], "Interpretasi": info["interpretation"]})
        st.table(pd.DataFrame(comp_data))

        fig = go.Figure(go.Bar(
            x=[c["Skor (0-100)"] for c in comp_data], y=[c["Komponen"] for c in comp_data], orientation="h",
            marker_color=["green" if c["Skor (0-100)"] > 55 else "red" if c["Skor (0-100)"] < 45 else "orange" for c in comp_data],
        ))
        fig.update_layout(title="Skor per Komponen", xaxis_range=[0, 100], height=350, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
        st.plotly_chart(fig, use_container_width=True)

    st.markdown("---")
    st.subheader("Interpretasi & Rekomendasi")
    st.info(fg["advice"])

    st.markdown("---")
    st.subheader("Tren Sentimen Historis")
    if "IHSG" in data and "VIX" in data:
        ihsg = data["IHSG"]["Close"]
        vix = data["VIX"]["Close"]
        ihsg_ret = ihsg.pct_change(periods=20) * 100
        vix_score = np.clip(100 - vix * 3, 0, 100)
        mom_score = np.clip(50 + ihsg_ret * 2, 0, 100)
        composite_hist = (vix_score + mom_score) / 2

        fig_hist = go.Figure()
        fig_hist.add_trace(go.Scatter(x=vix.index, y=composite_hist, name="F&G Proxy", line=dict(color="cyan")))
        fig_hist.add_hline(y=25, line_dash="dash", line_color="red", annotation_text="Takut Ekstrem")
        fig_hist.add_hline(y=75, line_dash="dash", line_color="green", annotation_text="Serakah Ekstrem")
        fig_hist.update_layout(title="Tren Historis Indeks Sentimen", height=300, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
        st.plotly_chart(fig_hist, use_container_width=True)
