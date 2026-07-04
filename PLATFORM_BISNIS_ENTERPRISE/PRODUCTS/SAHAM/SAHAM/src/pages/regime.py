"""Market Regime page — detect bull/bear/sideways conditions."""
import streamlit as st
import plotly.graph_objects as go

from ..ui_components import load_market_data, tt, section_header
from ..config import TICKERS, BLUE_CHIPS_ID
from ..regime import detect_market_regime as calc_market_regime


def render_regime():
    section_header("🎯", f"{tt('Market Regime')} — Deteksi Kondisi Pasar")
    st.caption("Deteksi kondisi pasar: Naik (Bull), Turun (Bear), atau Datar (Sideways) berdasarkan pergerakan rata-rata harga, ADX, volatilitas, dan arah tren")

    data = load_market_data(period="2y")
    if not data:
        st.error("Gagal mengambil data.")
        return

    col1, col2 = st.columns([1, 2])
    with col1:
        target_options = {"IHSG (^JKSE)": "^JKSE"}
        target_options.update({f"{name} ({ticker})": ticker for name, ticker in BLUE_CHIPS_ID.items()})
        selected = st.selectbox("Target", list(target_options.keys()))
        target_ticker = target_options[selected]

    regime = calc_market_regime(data, target_ticker)
    if regime["regime"] == "Unknown":
        st.error("Data tidak cukup untuk analisis kondisi pasar.")
        return

    regime_emoji = {"BULL": "🐂", "BEAR": "🐻", "SIDEWAYS": "➡️"}
    regime_label = {"BULL": "NAIK (Bull)", "BEAR": "TURUN (Bear)", "SIDEWAYS": "DATAR (Sideways)"}
    st.markdown(f"## {regime_emoji.get(regime['regime'], '⚪')} Kondisi: **{regime_label.get(regime['regime'], regime['regime'])}**")
    st.progress(regime["confidence"] / 100)
    st.caption(f"Tingkat Keyakinan: {regime['confidence']}%")

    st.markdown("---")
    st.subheader("Rekomendasi Strategi")
    st.info(regime["strategy"])

    st.markdown("---")
    st.subheader("Detail Analisis Kondisi Pasar")
    col_a, col_b, col_c = st.columns(3)
    col_a.metric("Skor Naik (Bull)", f"{regime['bull_score']}")
    col_b.metric("Skor Turun (Bear)", f"{regime['bear_score']}")
    col_c.metric(f"{tt('ADX')}", f"{regime['adx']}")

    col_d, col_e, col_f = st.columns(3)
    col_d.metric(f"{tt('MA20')}", f"{regime['ma20']:,.2f}")
    col_e.metric(f"{tt('MA50')}", f"{regime['ma50']:,.2f}")
    col_f.metric(f"{tt('MA100')}", f"{regime['ma100']:,.2f}")

    col_g, col_h = st.columns(2)
    col_g.metric(f"{tt('Volatility')} Tahunan", f"{regime['volatility_annual']}%")
    col_h.metric("Kemiringan Tren", f"{regime['slope_pct']:+.3f}%/hari")

    st.markdown("---")
    st.subheader("Alasan Analisis")
    for r in regime["reasons"]:
        st.markdown(f"- {r}")

    st.markdown("---")
    st.subheader(f"Harga vs Rata-rata Pergerakan ({tt('MA')})")
    target_name = None
    for name, t in TICKERS.items():
        if t == target_ticker:
            target_name = name
            break
    if target_name and target_name in data:
        df = data[target_name]
        fig = go.Figure()
        fig.add_trace(go.Scatter(x=df.index, y=df["Close"], name="Close", line=dict(color="white")))
        fig.add_trace(go.Scatter(x=df.index, y=df["Close"].rolling(20).mean(), name="MA20", line=dict(color="orange")))
        fig.add_trace(go.Scatter(x=df.index, y=df["Close"].rolling(50).mean(), name="MA50", line=dict(color="blue")))
        fig.add_trace(go.Scatter(x=df.index, y=df["Close"].rolling(100).mean(), name="MA100", line=dict(color="red")))
        if len(df) >= 200:
            fig.add_trace(go.Scatter(x=df.index, y=df["Close"].rolling(200).mean(), name="MA200", line=dict(color="purple")))
        fig.update_layout(height=400, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
        st.plotly_chart(fig, use_container_width=True)
