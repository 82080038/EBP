"""Inter-Market Analysis page — correlation, lead-lag, spread."""
import streamlit as st
import plotly.graph_objects as go

from ..ui_components import load_market_data, tt, section_header
from ..intermarket import calc_correlation_matrix, calc_rolling_correlation, calc_lead_lag, calc_spread_analysis


def render_intermarket():
    section_header("🌐", "Analisis Antar-Pasar")
    st.caption(f"Analisis hubungan antar pasar: {tt('Correlation')}, {tt('Lead-Lag')}, {tt('Spread Analysis')}")

    data = load_market_data(period="2y")
    if not data:
        st.error("Gagal mengambil data.")
        return

    tab1, tab2, tab3 = st.tabs(["Korelasi", "Pendahulu-Pengikut", "Analisis Selisih"])

    with tab1:
        st.subheader(f"Matriks {tt('Correlation')} (60 hari)")
        corr = calc_correlation_matrix(data, period=60)
        if not corr.empty:
            fig = go.Figure(data=go.Heatmap(z=corr.values, x=corr.columns, y=corr.index, colorscale="RdYlGn", zmid=0, text=corr.values.round(2), texttemplate="%{text}", showscale=True))
            fig.update_layout(height=500, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
            st.plotly_chart(fig, use_container_width=True)

        st.markdown("---")
        st.subheader(f"{tt('Rolling Correlation')} dengan IHSG (60 hari)")
        rolling_corr = calc_rolling_correlation(data, "IHSG", window=60)
        if not rolling_corr.empty:
            fig = go.Figure()
            for col in rolling_corr.columns:
                fig.add_trace(go.Scatter(x=rolling_corr.index, y=rolling_corr[col], name=col, mode="lines"))
            fig.add_hline(y=0, line_dash="dash", line_color="gray")
            fig.update_layout(height=400, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
            st.plotly_chart(fig, use_container_width=True)

    with tab2:
        st.subheader(f"Analisis {tt('Lead-Lag')}")
        st.markdown("Pasar mana yang bergerak lebih dulu (pendahulu) dan mana yang mengikuti (pengikut) IHSG?")
        ll = calc_lead_lag(data, "IHSG", max_lag=5)
        if not ll.empty:
            st.dataframe(ll, use_container_width=True)
            st.markdown("---")
            st.subheader("Indikator Pendahulu untuk IHSG")
            leaders = ll[ll["Best Lag (days)"] > 0]
            if not leaders.empty:
                for _, row in leaders.iterrows():
                    st.markdown(f"- **{row['Market']}** memimpin IHSG {row['Best Lag (days)']} hari (korelasi={row['Best Correlation']:.3f})")
            else:
                st.info("Tidak ada indikator pendahulu yang signifikan.")

    with tab3:
        st.subheader(f"{tt('Spread Analysis')}")
        col_a, col_b = st.columns(2)
        with col_a:
            asset_a = st.selectbox("Aset A", list(data.keys()))
        with col_b:
            asset_b = st.selectbox("Aset B", [x for x in data.keys() if x != asset_a])

        spread = calc_spread_analysis(data, asset_a, asset_b)
        if "error" not in spread:
            col1, col2, col3 = st.columns(3)
            col1.metric("Rasio Saat Ini", f"{spread['current_ratio']:.4f}")
            col2.metric("Rata-rata 60 Hari", f"{spread['ratio_mean_60d']:.4f}")
            col3.metric(f"{tt('Z-Score')}", f"{spread['z_score']:.2f}")
            st.info(f"**Sinyal:** {spread['signal']}")
            st.caption(f"**Tindakan:** {spread['action']}")

            fig = go.Figure()
            fig.add_trace(go.Scatter(x=spread["ratio_series"].index, y=spread["ratio_series"], name="Ratio", line=dict(color="cyan")))
            fig.update_layout(title=f"{asset_a}/{asset_b} Ratio", height=300, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
            st.plotly_chart(fig, use_container_width=True)

            fig2 = go.Figure()
            fig2.add_trace(go.Scatter(x=spread["z_score_series"].index, y=spread["z_score_series"], name="Z-Score", line=dict(color="orange")))
            fig2.add_hline(y=2, line_dash="dash", line_color="red")
            fig2.add_hline(y=-2, line_dash="dash", line_color="green")
            fig2.update_layout(title="Z-Score (60d)", height=250, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
            st.plotly_chart(fig2, use_container_width=True)
        else:
            st.error(spread["error"])
