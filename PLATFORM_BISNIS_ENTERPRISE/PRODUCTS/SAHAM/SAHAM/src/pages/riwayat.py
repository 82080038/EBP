"""Riwayat & Akurasi page — prediction history and accuracy metrics."""
import streamlit as st
import plotly.express as px

from ..ui_components import tt, section_header
from ..database import get_all_prediksi, get_verified_prediksi, get_akurasi_metrics, get_log_aktivitas


def render_riwayat():
    section_header("📋", "Riwayat Prediksi & Akurasi")
    metrics = get_akurasi_metrics()
    col1, col2, col3, col_d = st.columns(4)
    col1.markdown(f"<label style='font-size:0.85em;color:#9ca3af;'>{tt('Directional Accuracy')}</label><div style='font-size:1.5em;font-weight:700;'>{metrics['directional_accuracy']}%</div>", unsafe_allow_html=True)
    col2.markdown(f"<label style='font-size:0.85em;color:#9ca3af;'>{tt('MAPE')}</label><div style='font-size:1.5em;font-weight:700;'>{metrics['mape']}%</div>", unsafe_allow_html=True)
    col3.metric("Total Terverifikasi", f"{metrics['total']}")
    col_d.metric("Benar", f"{metrics['benar']}")

    st.markdown("---")
    tab1, tab2, tab3 = st.tabs(["Riwayat Prediksi", "Prediksi Terverifikasi", "Log Aktivitas"])

    with tab1:
        df_all = get_all_prediksi()
        if df_all.empty:
            st.info("Belum ada riwayat prediksi.")
        else:
            display_cols = ["ticker", "tanggal_prediksi", "tanggal_target", "harga_prediksi", "harga_aktual", "arah_prediksi", "arah_aktual", "sinyal", "confidence"]
            available_cols = [c for c in display_cols if c in df_all.columns]
            st.dataframe(df_all[available_cols], use_container_width=True)

    with tab2:
        df_verified = get_verified_prediksi()
        if df_verified.empty:
            st.info("Belum ada prediksi terverifikasi.")
        else:
            df_verified["benar"] = df_verified["arah_prediksi"] == df_verified["arah_aktual"]
            col_a, col_b = st.columns(2)
            with col_a:
                fig = px.pie(df_verified, names="benar", title="Benar vs Salah", color="benar", color_discrete_map={True: "green", False: "red"})
                fig.update_layout(template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
                st.plotly_chart(fig, use_container_width=True)
            with col_b:
                fig2 = px.pie(df_verified, names="sinyal", title="Distribusi Sinyal")
                fig2.update_layout(template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
                st.plotly_chart(fig2, use_container_width=True)
            st.dataframe(df_verified, use_container_width=True)

    with tab3:
        df_log = get_log_aktivitas(limit=100)
        if df_log.empty:
            st.info("Belum ada log aktivitas.")
        else:
            st.dataframe(df_log, use_container_width=True)
