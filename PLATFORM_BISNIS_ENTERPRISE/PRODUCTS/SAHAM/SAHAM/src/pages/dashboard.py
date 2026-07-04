"""Dashboard page — market overview with ticker tape and summary."""
import streamlit as st
import plotly.graph_objects as go

from ..ui_components import render_ticker_tape, create_sparkline, load_market_data, tt, section_header
from ..database import get_akurasi_metrics


def render_dashboard():
    section_header("📊", "Dashboard Pasar Saham Global")
    data = load_market_data(period="1y")

    if not data:
        st.error("Gagal mengambil data pasar.")
        return

    render_ticker_tape(data)

    col1, col2, col3, col4 = st.columns(4)
    for i, (name, df) in enumerate(data.items()):
        if df.empty:
            continue
        col = [col1, col2, col3, col4][i % 4]
        current = df["Close"].iloc[-1]
        prev = df["Close"].iloc[-2] if len(df) > 1 else current
        change = ((current - prev) / prev) * 100
        col.metric(name, f"{current:,.2f}", f"{change:+.2f}%")
        if len(df) > 20:
            with col:
                st.plotly_chart(create_sparkline(df["Close"].tail(20)), use_container_width=True)

    st.markdown("---")
    section_header("📉", "Grafik IHSG (30 Hari)")
    if "IHSG" in data and not data["IHSG"].empty:
        df_ihsg = data["IHSG"].tail(30)
        fig = go.Figure()
        fig.add_trace(go.Candlestick(
            x=df_ihsg.index, open=df_ihsg["Open"], high=df_ihsg["High"],
            low=df_ihsg["Low"], close=df_ihsg["Close"], name="IHSG",
        ))
        fig.update_layout(height=400, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)", xaxis_rangeslider_visible=False)
        st.plotly_chart(fig, use_container_width=True)

    st.markdown("---")
    section_header("📋", "Ringkasan Akurasi Model")
    metrics = get_akurasi_metrics()
    col_a, col_b, col_c, col_d = st.columns(4)
    col_a.markdown(f"<label style='font-size:0.85em;color:#9ca3af;'>{tt('Directional Accuracy')}</label><div style='font-size:1.5em;font-weight:700;'>{metrics['directional_accuracy']}%</div>", unsafe_allow_html=True)
    col_b.markdown(f"<label style='font-size:0.85em;color:#9ca3af;'>{tt('MAPE')}</label><div style='font-size:1.5em;font-weight:700;'>{metrics['mape']}%</div>", unsafe_allow_html=True)
    col_c.metric("Total Prediksi", f"{metrics['total']}")
    col_d.metric("Benar", f"{metrics['benar']}")
