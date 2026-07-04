"""Pengaturan page — configuration and manual verification."""
import streamlit as st
import pandas as pd

from ..ui_components import tt, section_header
from ..config import TICKERS, BLUE_CHIPS_ID, MODEL_CONFIG, BUSINESS_RULES


def render_pengaturan():
    section_header("⚙️", "Pengaturan & Konfigurasi")
    st.subheader(f"{tt('Ticker')} Dipantau")
    st.table(pd.DataFrame([{"Nama": k, "Ticker": v} for k, v in TICKERS.items()]))
    st.subheader(f"{tt('Blue Chip')} Indonesia")
    st.table(pd.DataFrame([{"Ticker": k, "Nama": v} for k, v in BLUE_CHIPS_ID.items()]))
    st.subheader("Konfigurasi Model")
    st.json(MODEL_CONFIG)
    st.subheader("Aturan Bisnis (Business Rules)")
    st.json(BUSINESS_RULES)
    st.markdown("---")
    st.subheader("Verifikasi Manual")
    if st.button("🔄 Verifikasi Prediksi"):
        from ..run_analysis import cmd_verify
        with st.spinner("Memverifikasi..."):
            cmd_verify()
        st.success("Verifikasi selesai!")
