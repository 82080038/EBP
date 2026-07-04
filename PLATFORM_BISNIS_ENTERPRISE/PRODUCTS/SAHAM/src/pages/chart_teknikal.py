"""Chart & Teknikal page — candlestick charts and technical indicators."""
import streamlit as st
import plotly.graph_objects as go

from ..ui_components import load_stock_data, create_candlestick_chart, render_signal_badge, tt, section_header, GLOSSARY
from ..config import TICKERS, BLUE_CHIPS_ID
from ..indicators import add_all_indicators, get_indicator_signals, get_composite_signal
from ..preprocessor import calculate_rsi


def render_chart_teknikal():
    section_header("📈", "Chart & Analisis Teknikal")
    col1, col2 = st.columns([1, 3])

    with col1:
        all_tickers = {**{f"{n} ({t})": t for n, t in TICKERS.items()}, **{f"{n} ({t})": t for n, t in BLUE_CHIPS_ID.items()}}
        selected = st.selectbox(f"Pilih {tt('Ticker')}", list(all_tickers.keys()))
        ticker = all_tickers[selected]
        period = st.selectbox("Periode", ["1mo", "3mo", "6mo", "1y", "2y"], index=2)
        st.markdown("---")
        st.markdown("**📊 Indikator Tambahan**")
        show_rsi = st.checkbox(f"{tt('RSI')} (14)", value=True)
        show_macd = st.checkbox(f"{tt('MACD')}", value=False)
        show_bb = st.checkbox(f"{tt('Bollinger Bands')}", value=False)
        show_stoch = st.checkbox(f"{tt('Stochastic')}", value=False)

    with col2:
        df = load_stock_data(ticker, period=period)
        if df.empty:
            st.error("Gagal mengambil data.")
        else:
            df = add_all_indicators(df)
            fig = create_candlestick_chart(df, f"{selected} - {period}", show_bb=show_bb)
            st.plotly_chart(fig, use_container_width=True)

            st.markdown("---")
            section_header("🔧", "Sinyal Indikator Teknikal")
            signals = get_indicator_signals(df)
            composite, score, detail = get_composite_signal(signals)

            cols = st.columns(min(len(signals), 4))
            for i, (name, sig) in enumerate(signals.items()):
                with cols[i % len(cols)]:
                    s = sig["signal"].lower()
                    color = "#22c55e" if "bullish" in s or "oversold" in s else "#ef4444" if "bearish" in s or "overbought" in s else "#9ca3af"
                    name_html = tt(name) if name in GLOSSARY else name
                    st.markdown(f"{name_html}")
                    st.markdown(f"<span style='color:{color}; font-size:1.1em; font-weight:600'>{sig['signal']}</span>", unsafe_allow_html=True)
                    st.caption(f"{sig['value']} — {sig['detail']}")

            st.markdown("---")
            render_signal_badge(f"Sinyal Gabungan: {composite}")
            st.caption(f"Skor: {score:.2f} — {detail}")

            if show_rsi:
                st.markdown("---")
                st.subheader(f"{tt('RSI')} (14)")
                rsi_vals = calculate_rsi(df["Close"])
                fig_rsi = go.Figure()
                fig_rsi.add_trace(go.Scatter(x=df.index, y=rsi_vals, name="RSI", line=dict(color="purple")))
                fig_rsi.add_hline(y=70, line_dash="dash", line_color="red", annotation_text="Terbeli Berlebihan (>70)")
                fig_rsi.add_hline(y=30, line_dash="dash", line_color="green", annotation_text="Terjual Berlebihan (<30)")
                fig_rsi.update_layout(height=250, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
                st.plotly_chart(fig_rsi, use_container_width=True)

            if show_macd and "MACD" in df.columns:
                st.markdown("---")
                st.subheader(f"{tt('MACD')}")
                fig_macd = go.Figure()
                fig_macd.add_trace(go.Bar(x=df.index, y=df["MACD_Hist"], name="Histogram", marker_color="rgba(100,100,255,0.5)"))
                fig_macd.add_trace(go.Scatter(x=df.index, y=df["MACD"], name="MACD", line=dict(color="blue")))
                fig_macd.add_trace(go.Scatter(x=df.index, y=df["MACD_Signal"], name="Signal", line=dict(color="orange")))
                fig_macd.update_layout(height=250, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
                st.plotly_chart(fig_macd, use_container_width=True)

            if show_stoch and "Stoch_K" in df.columns:
                st.markdown("---")
                st.subheader(f"{tt('Stochastic')} Oscillator")
                fig_stoch = go.Figure()
                fig_stoch.add_trace(go.Scatter(x=df.index, y=df["Stoch_K"], name="%K", line=dict(color="blue")))
                fig_stoch.add_trace(go.Scatter(x=df.index, y=df["Stoch_D"], name="%D", line=dict(color="orange")))
                fig_stoch.add_hline(y=80, line_dash="dash", line_color="red")
                fig_stoch.add_hline(y=20, line_dash="dash", line_color="green")
                fig_stoch.update_layout(height=250, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
                st.plotly_chart(fig_stoch, use_container_width=True)

            st.markdown("---")
            st.subheader("Statistik")
            col_a, col_b, col_c, col_d = st.columns(4)
            col_a.metric("Harga Tertinggi", f"{df['High'].max():,.2f}")
            col_b.metric("Harga Terendah", f"{df['Low'].min():,.2f}")
            if "Volume" in df.columns:
                col_c.metric("Rata-rata Volume", f"{df['Volume'].mean():,.0f}")
            returns = df["Close"].pct_change().dropna()
            col_d.metric("Rata-rata Return Harian", f"{returns.mean()*100:.2f}%")
