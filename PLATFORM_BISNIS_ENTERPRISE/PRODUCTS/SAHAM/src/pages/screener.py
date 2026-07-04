"""
Streamlit Page: Multi-Stock Screener.
"""
import streamlit as st
import pandas as pd


def render_screener():
    st.markdown("# 📡 Multi-Stock Screener")
    st.markdown("*Scan all Indonesian blue chips and rank by AI score*")
    st.markdown("---")

    col1, col2 = st.columns([3, 1])
    with col1:
        if st.button("🔄 Run Screener", type="primary"):
            with st.spinner("Scanning blue chips..."):
                try:
                    from src.screener import run_screener, format_screener_results
                    result = run_screener()
                    st.session_state["screener_result"] = result
                    st.session_state["screener_df"] = format_screener_results(result)
                except Exception as e:
                    st.error(f"Screener failed: {e}")

    with col2:
        pass

    if "screener_result" in st.session_state:
        result = st.session_state["screener_result"]
        st.success(result.summary())

        col1, col2, col3 = st.columns(3)
        with col1:
            st.metric("Stocks Scanned", result.n_success)
        with col2:
            st.metric("BUY Signals", len(result.top_buys))
        with col3:
            st.metric("SELL Signals", len(result.top_sells))

        st.markdown("---")

        col1, col2 = st.columns(2)

        with col1:
            st.markdown("### 🟢 Top BUY Opportunities")
            if result.top_buys:
                buy_data = []
                for e in result.top_buys:
                    buy_data.append({
                        "Ticker": e.ticker,
                        "Name": e.name,
                        "Price": f"{e.current_price:,.0f}",
                        "AI Score": e.ai_score,
                        "RSI": e.rsi,
                        "Trend": e.ma_trend,
                        "Sector": e.sector,
                    })
                st.dataframe(pd.DataFrame(buy_data), use_container_width=True, hide_index=True)
            else:
                st.info("No BUY signals detected.")

        with col2:
            st.markdown("### 🔴 Top SELL Opportunities")
            if result.top_sells:
                sell_data = []
                for e in result.top_sells:
                    sell_data.append({
                        "Ticker": e.ticker,
                        "Name": e.name,
                        "Price": f"{e.current_price:,.0f}",
                        "AI Score": e.ai_score,
                        "RSI": e.rsi,
                        "Trend": e.ma_trend,
                        "Sector": e.sector,
                    })
                st.dataframe(pd.DataFrame(sell_data), use_container_width=True, hide_index=True)
            else:
                st.info("No SELL signals detected.")

        st.markdown("---")
        st.markdown("### Full Results")
        if "screener_df" in st.session_state:
            st.dataframe(st.session_state["screener_df"], use_container_width=True, hide_index=True)
    else:
        st.info("Click **Run Screener** to scan all blue chip stocks.")
