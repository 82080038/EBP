"""
Streamlit Page: Options Analysis.
"""
import streamlit as st


def render_options_analysis():
    st.markdown("# 📐 Options Analysis")
    st.markdown("*Greeks calculation, implied volatility, and options strategies*")
    st.markdown("---")

    col1, col2, col3 = st.columns(3)
    with col1:
        spot = st.number_input("Spot Price", value=8500.0, step=50.0)
    with col2:
        strike = st.number_input("Strike Price", value=8500.0, step=50.0)
    with col3:
        expiry_days = st.number_input("Days to Expiry", value=30, step=1)

    col1, col2, col3 = st.columns(3)
    with col1:
        volatility = st.number_input("Implied Volatility (%)", value=25.0, step=1.0) / 100
    with col2:
        risk_free = st.number_input("Risk-Free Rate (%)", value=5.0, step=0.5) / 100
    with col3:
        option_type = st.selectbox("Option Type", ["Call", "Put"])

    if st.button("Calculate Greeks", type="primary"):
        try:
            from src.options_analysis import black_scholes_greeks
            T = expiry_days / 365
            greeks = black_scholes_greeks(
                S=spot, K=strike, T=T, r=risk_free, sigma=volatility,
                option_type=option_type.lower(),
            )

            c1, c2, c3, c4, c5 = st.columns(5)
            with c1:
                st.metric("Price", f"{greeks.get('price', 0):.2f}")
            with c2:
                st.metric("Delta", f"{greeks.get('delta', 0):.4f}")
            with c3:
                st.metric("Gamma", f"{greeks.get('gamma', 0):.4f}")
            with c4:
                st.metric("Theta", f"{greeks.get('theta', 0):.4f}")
            with c5:
                st.metric("Vega", f"{greeks.get('vega', 0):.4f}")

            st.markdown("---")
            st.markdown("### Interpretation")
            st.info(
                f"**{option_type}** at strike {strike:,.0f} with {expiry_days} days to expiry.\n\n"
                f"Delta: {'In-the-money' if abs(greeks.get('delta', 0)) > 0.5 else 'Out-of-the-money'}\n"
                f"Theta: {'High time decay' if abs(greeks.get('theta', 0)) > 5 else 'Moderate decay'}\n"
                f"Vega: {'High vol sensitivity' if abs(greeks.get('vega', 0)) > 10 else 'Low vol sensitivity'}"
            )

        except Exception as e:
            st.error(f"Calculation failed: {e}")

    st.markdown("---")
    st.markdown("### Options Strategies")

    strategy = st.selectbox("Strategy", [
        "Long Straddle", "Short Straddle",
        "Long Strangle", "Short Strangle",
        "Covered Call", "Protective Put",
    ])

    if st.button("Analyze Strategy"):
        try:
            from src.options_analysis import analyze_strategy
            T = expiry_days / 365
            result = analyze_strategy(
                strategy=strategy, S=spot, K=strike, T=T,
                r=risk_free, sigma=volatility,
            )
            if result:
                st.markdown(f"**Max Profit:** {result.get('max_profit', 'Unlimited')}")
                st.markdown(f"**Max Loss:** {result.get('max_loss', 'Unlimited')}")
                st.markdown(f"**Breakeven:** {result.get('breakeven', 'N/A')}")
                st.markdown(f"**Net Cost:** {result.get('net_cost', 0):.2f}")
            else:
                st.info("Strategy analysis not available for this configuration.")
        except Exception as e:
            st.info(f"Strategy analysis requires options_analysis module: {e}")
