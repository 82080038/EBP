"""Notification Center page — in-app notification history & management."""
import streamlit as st

from ..ui_components import section_header
from ..database import (
    get_notifikasi,
    mark_notifikasi_dibaca,
    get_jumlah_notifikasi_belum_dibaca,
)


def render_notifikasi():
    section_header("🔔", "Pusat Notifikasi")
    st.caption("Semua notifikasi prediksi, briefing, dan trading agent dalam satu tempat. Tidak perlu API eksternal.")

    # Badge jumlah belum dibaca
    belum_dibaca = get_jumlah_notifikasi_belum_dibaca()

    col1, col2 = st.columns([3, 1])
    with col1:
        filter_pilihan = st.radio(
            "Filter",
            ["Semua", "Belum Dibaca"],
            horizontal=True,
            index=1 if belum_dibaca > 0 else 0,
        )
    with col2:
        if belum_dibaca > 0:
            if st.button(f"✅ Tandai Semua Dibaca ({belum_dibaca})", type="primary"):
                mark_notifikasi_dibaca(semua=True)
                st.rerun()

    # Ambil data
    hanya_belum = filter_pilihan == "Belum Dibaca"
    df = get_notifikasi(limit=100, hanya_belum_dibaca=hanya_belum)

    if df.empty:
        st.info("📭 Tidak ada notifikasi.")
        return

    # Filter by kategori
    kategori_options = ["Semua"] + sorted(df["kategori"].unique().tolist())
    kategori = st.selectbox("Kategori", kategori_options)
    if kategori != "Semua":
        df = df[df["kategori"] == kategori]

    # Display notifications
    level_colors = {
        "success": "🟢",
        "warning": "🟡",
        "error": "🔴",
        "info": "🔵",
    }
    level_bg = {
        "success": "#dcfce7",
        "warning": "#fef9c3",
        "error": "#fee2e2",
        "info": "#dbeafe",
    }

    st.markdown(f"**{len(df)} notifikasi** ditampilkan.")

    for _, row in df.iterrows():
        emoji = level_colors.get(row.get("level", "info"), "🔵")
        bg = level_bg.get(row.get("level", "info"), "#f3f4f6")
        dibaca_badge = "" if row.get("dibaca", 1) == 1 else " **(Baru)**"

        with st.container():
            st.markdown(
                f"""
                <div style="background: {bg}; border-left: 4px solid #3b82f6; padding: 12px; margin: 8px 0; border-radius: 4px;">
                    <div style="display: flex; justify-content: space-between;">
                        <strong>{emoji} {row['judul']}{dibaca_badge}</strong>
                        <span style="color: #6b7280; font-size: 0.85em;">{row['timestamp']}</span>
                    </div>
                    <div style="margin-top: 8px; white-space: pre-wrap; font-size: 0.9em;">{row['pesan']}</div>
                    <div style="margin-top: 4px; font-size: 0.8em; color: #6b7280;">Kategori: {row['kategori']}</div>
                </div>
                """,
                unsafe_allow_html=True,
            )
            if row.get("dibaca", 1) == 0:
                col_a, _ = st.columns([1, 4])
                with col_a:
                    if st.button(f"Tandai Dibaca #{row['id']}", key=f"read_{row['id']}"):
                        mark_notifikasi_dibaca(notifikasi_id=row["id"])
                        st.rerun()

    st.markdown("---")
    st.caption("💡 Notifikasi tersimpan di database lokal SQLite. Tidak perlu Telegram atau API berbayar.")
