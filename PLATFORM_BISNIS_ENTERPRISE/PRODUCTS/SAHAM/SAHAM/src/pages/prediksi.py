"""Prediksi page — run prediction and display results."""
import streamlit as st
import pandas as pd

from ..ui_components import (
    load_market_data, render_verdict_box, render_step_progress, tt, section_header
)
from ..config import BLUE_CHIPS_ID, BUSINESS_RULES
from ..models import HybridEnsemble
from ..predictor import run_prediction
from ..notifier import notify_prediction


def render_prediksi():
    section_header("🔮", "Prediksi Pasar Saham")
    col1, col2 = st.columns([1, 2])

    with col1:
        section_header("⚙️", "Konfigurasi")
        target_options = {"IHSG (^JKSE)": "^JKSE"}
        target_options.update({f"{name} ({ticker})": ticker for name, ticker in BLUE_CHIPS_ID.items()})
        selected = st.selectbox("Pilih Target", list(target_options.keys()))
        target_ticker = target_options[selected]
        period = st.selectbox("Periode Data Latih", ["6mo", "1y", "2y"], index=2)
        use_lstm = st.checkbox(f"Aktifkan {tt('LSTM')} (butuh TensorFlow)", value=False)
        st.markdown("---")
        with st.expander("📋 Aturan Bisnis (Business Rules)"):
            st.json(BUSINESS_RULES)

        st.markdown("---")
        if st.button("🚀 Jalankan Prediksi", type="primary"):
            steps = [
                ("active", "Mengambil data pasar", ""),
                ("pending", "Pra-pemrosesan & rekayasa fitur", ""),
                ("pending", "Melatih model ensemble", ""),
                ("pending", "Membuat prediksi", ""),
                ("pending", "Menerapkan aturan bisnis", ""),
            ]
            render_step_progress(steps)

            with st.spinner("Mengambil data pasar..."):
                md = load_market_data(period=period)
                if not md:
                    steps[0] = ("error", "Mengambil data pasar", "GAGAL")
                    render_step_progress(steps)
                    st.error("Gagal mengambil data.")
                else:
                    steps[0] = ("done", "Mengambil data pasar", f"{len(md)} ticker")
                    steps[1] = ("active", "Pra-pemrosesan & rekayasa fitur", "")
                    render_step_progress(steps)

                    with st.spinner("Pra-pemrosesan data..."):
                        ensemble = HybridEnsemble(use_lstm=use_lstm)

                    steps[1] = ("done", "Pra-pemrosesan & rekayasa fitur", "135 fitur")
                    steps[2] = ("active", "Melatih model ensemble", "")
                    render_step_progress(steps)

                    with st.spinner("Melatih model ensemble..."):
                        result = run_prediction(market_data=md, fred_data={}, target_ticker=target_ticker, ensemble=ensemble)

                    if "error" in result:
                        steps[2] = ("error", "Melatih model ensemble", "GAGAL")
                        render_step_progress(steps)
                        st.error(f"Error: {result['error']}")
                    else:
                        steps[2] = ("done", "Melatih model ensemble", "RF + XGB" + (" + LSTM" if use_lstm else ""))
                        steps[3] = ("done", "Membuat prediksi", result["sinyal"])
                        steps[4] = ("done", "Menerapkan aturan bisnis", "OK")
                        render_step_progress(steps)
                        st.session_state["pred_result"] = result
                        st.success("✅ Prediksi selesai! Notifikasi tersimpan di Pusat Notifikasi.")
                        notify_prediction(result)
                        if st.checkbox("Kirim juga ke Telegram (opsional)"):
                            from ..notifier import send_telegram
                            from ..notifier import format_prediction_message
                            send_telegram(format_prediction_message(result))

    with col2:
        section_header("📊", "Hasil Prediksi")
        if "pred_result" in st.session_state:
            result = st.session_state["pred_result"]
            sinyal = result["sinyal"]

            render_verdict_box(
                sinyal, result["confidence"],
                result["current_price"], result["predicted_price"],
                result["arah_prediksi"]
            )

            col_a, col_b, col_c = st.columns(3)
            col_a.metric("Harga Saat Ini", f"Rp {result['current_price']:,.2f}")
            col_b.metric("Harga Prediksi", f"Rp {result['predicted_price']:,.2f}")
            change_pct = ((result["predicted_price"] - result["current_price"]) / result["current_price"]) * 100
            col_c.metric("Perubahan Diharapkan", f"{change_pct:+.2f}%")

            st.markdown("---")
            section_header("🤖", f"Suara Model {tt('Ensemble')}")
            votes_data = []
            for model, vote in result["predictions"].items():
                proba = result["probabilities"].get(model, 0)
                vote_label = "BELI 🟢" if vote == 1 else "JUAL 🔴"
                proba_pct = f"{proba:.1%}"
                votes_data.append({"Model": model, "Suara": vote_label, "Probabilitas": proba_pct})
            st.dataframe(pd.DataFrame(votes_data), use_container_width=True, hide_index=True)

            st.markdown("---")
            section_header("📋", "Aturan Bisnis yang Diterapkan")
            st.info(result["rules"])
            st.warning("⚠️ **Disclaimer:** Prediksi bersifat probabilistik. Bukan saran investasi.")
        else:
            st.info("👈 Klik **Jalankan Prediksi** untuk melihat hasil.")
