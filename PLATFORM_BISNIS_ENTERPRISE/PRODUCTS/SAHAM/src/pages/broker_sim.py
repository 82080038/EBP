"""
Streamlit Page: Broker Simulation.
"""
import streamlit as st
import pandas as pd


def render_broker_sim():
    st.markdown("# 🏦 Broker Simulation")
    st.markdown("*Simulated broker API for testing trading strategies without real capital*")
    st.markdown("---")

    col1, col2, col3 = st.columns(3)
    with col1:
        broker_name = st.selectbox("Broker", [
            "bca_sekuritas", "indopremier", "mirae_asset", "mandiri_sekuritas",
        ])
    with col2:
        capital = st.number_input("Initial Capital (IDR)", value=100_000_000, step=10_000_000)
    with col3:
        if st.button("Initialize Broker", type="primary"):
            from src.broker_sim import BrokerSimulator
            st.session_state["broker"] = BrokerSimulator(broker=broker_name, capital=capital)
            st.success(f"Broker initialized: {broker_name}")

    if "broker" not in st.session_state:
        st.info("Click **Initialize Broker** to start.")
        return

    broker = st.session_state["broker"]
    portfolio = broker.get_portfolio()

    st.markdown("---")
    st.markdown("### Portfolio Summary")

    c1, c2, c3, c4 = st.columns(4)
    with c1:
        st.metric("Cash", f"Rp {portfolio['cash']:,.0f}")
    with c2:
        st.metric("Total Capital", f"Rp {portfolio['total_capital']:,.0f}")
    with c3:
        st.metric("Unrealized PnL", f"Rp {portfolio['unrealized_pnl']:,.0f}")
    with c4:
        st.metric("Return %", f"{portfolio['total_return_pct']:.2f}%")

    st.markdown("---")

    tab1, tab2, tab3 = st.tabs(["📝 Submit Order", "📊 Positions", "📋 Order History"])

    with tab1:
        col1, col2, col3, col4 = st.columns(4)
        with col1:
            symbol = st.text_input("Symbol", value="BBCA.JK")
        with col2:
            side = st.selectbox("Side", ["BUY", "SELL"])
        with col3:
            quantity = st.number_input("Quantity (shares)", value=100, step=100)
        with col4:
            order_type = st.selectbox("Order Type", ["MARKET", "LIMIT", "STOP_LOSS"])

        col1, col2 = st.columns(2)
        with col1:
            limit_price = st.number_input("Limit Price", value=0.0, step=50.0)
        with col2:
            current_price = st.number_input("Current Market Price", value=8500.0, step=50.0)

        if st.button("Submit Order"):
            from src.broker_sim import SimOrder
            order = SimOrder(
                symbol=symbol, side=side, quantity=quantity,
                order_type=order_type, limit_price=limit_price,
            )
            result = broker.submit_order(order, current_price=current_price)

            if result.status == "FILLED":
                st.success(f"✅ Filled: {result.filled_qty} @ {result.avg_fill_price:,.2f} (slippage: {result.slippage_bps:.1f} bps)")
            elif result.status == "PARTIAL_FILL":
                st.warning(f"⚠️ Partial fill: {result.filled_qty}/{result.requested_qty} @ {result.avg_fill_price:,.2f}")
            elif result.status == "REJECTED":
                st.error(f"❌ Rejected: {result.rejection_reason}")
            else:
                st.info(f"Status: {result.status}")

    with tab2:
        if portfolio["positions"]:
            df_pos = pd.DataFrame(portfolio["positions"])
            st.dataframe(df_pos, use_container_width=True, hide_index=True)
        else:
            st.info("No open positions.")

    with tab3:
        history = broker.get_order_history()
        if history:
            st.dataframe(pd.DataFrame(history), use_container_width=True, hide_index=True)
        else:
            st.info("No orders submitted yet.")
