"""Market Hours page — global exchange trading hours & status."""
import streamlit as st
import plotly.graph_objects as go
from datetime import datetime

from ..ui_components import section_header


def render_market_hours():
    section_header("🌍", "Market Hours — Jadwal Bursa Global")
    st.caption("Real-time status 8 bursa dunia: IDX, NYSE, NASDAQ, TSE, HKEX, SGX, SSE, LSE")

    try:
        from ..market_hours import MarketHours, EXCHANGES
        mh = MarketHours()
    except Exception as e:
        st.error(f"Gagal memuat modul market hours: {e}")
        return

    # === Real-time status ===
    st.markdown("## 📊 Status Real-Time")
    all_status = mh.get_all_status()

    cols = st.columns(4)
    for i, (code, status) in enumerate(all_status.items()):
        with cols[i % 4]:
            exchange = EXCHANGES[code]
            color = "🟢" if status.is_open else "🔴"
            st.markdown(f"### {color} {code}")
            st.markdown(f"**{exchange.name}**")
            st.markdown(f"📍 {exchange.timezone}")
            st.markdown(f"🕐 Local: `{status.local_time}`")
            st.markdown(f"🇮🇩 WIB: `{status.wib_time}`")
            st.markdown(f"**Session:** `{status.session.value}`")

            if status.is_open:
                st.markdown(f"⏰ Closes in: `{status.countdown_close}`")
            else:
                if status.next_open:
                    st.markdown(f"⏳ Opens in: `{status.countdown_open}`")
                if status.holiday_name:
                    st.warning(f"🎉 Holiday: {status.holiday_name}")

    # === Next market event ===
    st.markdown("---")
    st.markdown("## ⏰ Event Pasar Terdekat")
    next_event = mh.get_next_market_event()
    if next_event:
        col1, col2, col3 = st.columns(3)
        with col1:
            st.metric("Exchange", next_event[0])
        with col2:
            st.metric("Event", next_event[1])
        with col3:
            st.metric("Countdown", next_event[2])
    else:
        st.info("Tidak ada event pasar terdekat.")

    # === 24h schedule ===
    st.markdown("---")
    st.markdown("## 📅 Jadwal 24 Jam (WIB)")
    st.caption("Jam berapa bursa mana yang buka — dalam waktu Indonesia Barat (WIB)")

    schedule = mh.get_24h_schedule()

    # Build visual schedule
    hours = []
    for slot in schedule:
        hour = slot["hour_wib"]
        opens = slot["exchanges_open"]
        pres = slot["exchanges_pre_open"]
        hours.append({
            "hour": hour,
            "opens": opens,
            "pre": pres,
            "total": len(opens) + len(pres),
        })

    # Display as table
    rows = []
    for h in hours:
        rows.append([
            f"{h['hour']:02d}:00",
            ", ".join(h["opens"]) if h["opens"] else "—",
            ", ".join(h["pre"]) if h["pre"] else "—",
            h["total"],
        ])

    st.dataframe(rows, column_config={
        0: "Jam WIB",
        1: "Buka",
        2: "Pre-Open",
        3: "Jumlah",
    }, use_container_width=True, hide_index=True)

    # Visual bar chart
    fig = go.Figure()
    fig.add_trace(go.Bar(
        x=[f"{h['hour']:02d}:00" for h in hours],
        y=[h["total"] for h in hours],
        marker_color=["green" if h["total"] > 0 else "#333" for h in hours],
        text=[", ".join(h["opens"][:3]) if h["opens"] else "" for h in hours],
        textposition="outside",
    ))
    fig.update_layout(
        title="Jumlah Bursa Buka per Jam (WIB)",
        xaxis_title="Jam (WIB)",
        yaxis_title="Jumlah Bursa",
        height=350,
        template="plotly_dark",
    )
    st.plotly_chart(fig, use_container_width=True)

    # === Exchange details ===
    st.markdown("---")
    st.markdown("## 📋 Detail Jadwal per Bursa")

    for code, exchange in EXCHANGES.items():
        with st.expander(f"{code} — {exchange.name}"):
            col1, col2, col3 = st.columns(3)
            with col1:
                st.markdown(f"**Timezone:** `{exchange.timezone}`")
                st.markdown(f"**Currency:** `{exchange.currency}`")
            with col2:
                for session in exchange.sessions:
                    st.markdown(f"**{session.name}:** {session.start} - {session.end}")
            with col3:
                hols = mh._get_holidays(code, datetime.now().year)
                st.markdown(f"**Holidays {datetime.now().year}:** {len(hols)}")
                for hname, hdate in list(hols.items())[:5]:
                    st.markdown(f"  - {hname}: {hdate}")
                if len(hols) > 5:
                    st.markdown(f"  ... dan {len(hols) - 5} lainnya")

    # === Ticker → Exchange mapping ===
    st.markdown("---")
    st.markdown("## 🏷️ Deteksi Bursa dari Ticker")
    test_ticker = st.text_input("Masukkan ticker", value="BBCA.JK")
    if test_ticker:
        detected = mh.get_exchange_for_ticker(test_ticker)
        if detected:
            exchange = EXCHANGES[detected]
            st.success(f"**{test_ticker}** → {detected} ({exchange.name}) | Currency: {exchange.currency}")
        else:
            st.warning(f"Ticker `{test_ticker}` tidak dikenali. Format: .JK (IDX), .T (TSE), .HK (HKEX), .SI (SGX), .SS (SSE), .L (LSE), no suffix (NYSE/NASDAQ)")
