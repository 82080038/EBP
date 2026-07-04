"""Data Inventory page — overview semua data di sistem."""
import streamlit as st
import plotly.graph_objects as go
import pandas as pd

from ..ui_components import section_header


def render_data_inventory():
    section_header("📦", "Data Inventory — Overview Data Sistem")
    st.caption("Audit lengkap: data harian, intraday, fundamental, makro — jumlah, rentang, freshness")

    try:
        from ..data_fetcher import get_data_inventory, check_data_sufficiency, SUFFICIENCY_REQUIREMENTS
        from ..config import TICKERS, BLUE_CHIPS_ID, ALL_BLUE_CHIPS, FRED_SERIES
    except Exception as e:
        st.error(f"Gagal memuat modul: {e}")
        return

    inventory = get_data_inventory()
    summary = inventory["summary"]

    # === Summary cards ===
    st.markdown("## 📊 Ringkasan")
    col1, col2, col3, col4 = st.columns(4)
    with col1:
        st.metric("Data Harian", f"{summary['daily_tickers']} tickers", f"{summary['daily_total_rows']:,} rows")
    with col2:
        st.metric("Data Intraday", f"{summary['intraday_tickers']} entries", f"{summary['intraday_total_rows']:,} rows")
    with col3:
        st.metric("Fundamental", f"{summary['fundamental_tickers']} tickers")
    with col4:
        total = summary['daily_total_rows'] + summary['intraday_total_rows']
        st.metric("Total Rows", f"{total:,}")

    # === Daily data table ===
    st.markdown("---")
    st.markdown("## 📅 Data Harian (harga_harian)")
    if inventory["daily"]:
        df_daily = pd.DataFrame(inventory["daily"])
        df_daily.columns = ["Ticker", "Rows", "Earliest", "Latest", "Freshness"]
        df_daily["Freshness"] = df_daily["Freshness"].apply(
            lambda x: "🟢 Fresh" if x == "fresh" else "🔴 Stale"
        )
        st.dataframe(df_daily, use_container_width=True, hide_index=True)

        # Chart: rows per ticker
        fig = go.Figure()
        fig.add_trace(go.Bar(
            x=df_daily["Ticker"],
            y=df_daily["Rows"],
            marker_color=["green" if f == "🟢 Fresh" else "red" for f in df_daily["Freshness"]],
        ))
        fig.update_layout(
            title="Jumlah Rows per Ticker (Daily)",
            xaxis_title="Ticker",
            yaxis_title="Rows",
            height=400,
            template="plotly_dark",
        )
        st.plotly_chart(fig, use_container_width=True)
    else:
        st.warning("Belum ada data harian.")

    # === Intraday data ===
    st.markdown("---")
    st.markdown("## ⏱️ Data Intraday (harga_intraday)")
    if inventory["intraday"]:
        df_intra = pd.DataFrame(inventory["intraday"])
        df_intra.columns = ["Ticker", "Interval", "Rows", "Earliest", "Latest"]
        st.dataframe(df_intra, use_container_width=True, hide_index=True)
    else:
        st.info("Belum ada data intraday. Jalankan `fetch_intraday_data()` untuk mengambil data 1m/5m/15m/1h.")

    # === Fundamental data ===
    st.markdown("---")
    st.markdown("## 💰 Data Fundamental")
    if inventory["fundamental"]:
        df_fund = pd.DataFrame(inventory["fundamental"])
        df_fund.columns = ["Ticker", "Metrics", "Latest Update"]
        st.dataframe(df_fund, use_container_width=True, hide_index=True)
    else:
        st.info("Belum ada data fundamental. Jalankan `fetch_fundamental_data()` untuk mengambil PE, PBV, ROE, dll.")

    # === Data sufficiency checker ===
    st.markdown("---")
    st.markdown("## ✅ Data Sufficiency Checker")
    st.caption("Apakah data cukup untuk simulasi, prediksi, dan keputusan trading?")

    use_case = st.selectbox(
        "Pilih use case",
        list(SUFFICIENCY_REQUIREMENTS.keys()),
        format_func=lambda x: f"{x} — {SUFFICIENCY_REQUIREMENTS[x]['description']}",
    )

    req = SUFFICIENCY_REQUIREMENTS[use_case]
    col1, col2 = st.columns(2)
    with col1:
        st.info(f"**Minimum:** {req['min_rows']} rows / {req['min_period_days']} hari")
    with col2:
        st.info(f"**Recommended:** {req['recommended_rows']} rows")

    # Check all tickers
    all_tickers = {}
    all_tickers.update({t: t for t in TICKERS.values()})
    all_tickers.update(BLUE_CHIPS_ID)
    all_tickers.update(ALL_BLUE_CHIPS)

    results = []
    for ticker in all_tickers:
        report = check_data_sufficiency(ticker, use_case=use_case)
        if "error" not in report:
            results.append(report)

    if results:
        df_suff = pd.DataFrame(results)
        df_suff = df_suff[["ticker", "sufficient", "current_rows", "min_required", "recommended", "completeness_pct", "period_days", "gap"]]
        df_suff.columns = ["Ticker", "Sufficient", "Current", "Min", "Recommended", "Completeness %", "Period (days)", "Gap"]
        df_suff["Sufficient"] = df_suff["Sufficient"].apply(lambda x: "✅" if x else "❌")

        st.dataframe(df_suff, use_container_width=True, hide_index=True)

        sufficient_count = sum(1 for r in results if r["sufficient"])
        total = len(results)
        st.markdown(f"**{sufficient_count}/{total}** tickers sufficient for **{use_case}**")

        # Bar chart: current vs minimum
        fig = go.Figure()
        fig.add_trace(go.Bar(name="Current", x=[r["ticker"] for r in results], y=[r["current_rows"] for r in results], marker_color="blue"))
        fig.add_trace(go.Bar(name="Minimum", x=[r["ticker"] for r in results], y=[r["min_required"] for r in results], marker_color="red"))
        fig.add_trace(go.Bar(name="Recommended", x=[r["ticker"] for r in results], y=[r["recommended"] for r in results], marker_color="green"))
        fig.update_layout(
            title=f"Data Sufficiency — {use_case}",
            barmode="group",
            xaxis_title="Ticker",
            yaxis_title="Rows",
            height=450,
            template="plotly_dark",
        )
        st.plotly_chart(fig, use_container_width=True)
    else:
        st.warning("Tidak ada data untuk diperiksa.")

    # === FRED data ===
    st.markdown("---")
    st.markdown("## 📈 Data Makro Ekonomi (FRED)")
    fred_info = []
    for name, series_id in FRED_SERIES.items():
        from ..database import get_data_count_in_db, get_last_date_in_db
        rows = get_data_count_in_db(series_id)
        last = get_last_date_in_db(series_id)
        fred_info.append({"Series": name, "FRED ID": series_id, "Rows": rows, "Last Date": last or "—"})
    st.dataframe(pd.DataFrame(fred_info), use_container_width=True, hide_index=True)
