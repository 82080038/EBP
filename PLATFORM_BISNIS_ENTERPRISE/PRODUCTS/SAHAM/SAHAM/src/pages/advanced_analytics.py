"""
Streamlit page: Advanced Analytics (SMC, AFML, DRL, Complex Systems).
"""
import streamlit as st
import pandas as pd


def render_advanced_analytics():
    """Render the Advanced Analytics page with SMC, AFML, DRL, and Complex Systems."""

    st.markdown("# 🔬 Analisis Lanjutan (Advanced Analytics)")
    st.markdown("""
    Modul analisis tingkat expert/professional:
    - **Smart Money Concepts (SMC/ICT)**: Order blocks, FVG, liquidity sweeps, BOS/CHoCH
    - **AFML (Lopez de Prado)**: Triple-barrier labeling, fractional differentiation, DSR
    - **DRL Trading**: Deep reinforcement learning agent
    - **Complex Systems**: Network analysis, systemic risk, contagion paths
    """)

    tab_smc, tab_afml, tab_drl, tab_cs = st.tabs([
        "🎯 SMC/ICT", "🧮 AFML", "🤖 DRL Trading", "🌐 Complex Systems",
    ])

    # =========================================================================
    # SMC / ICT TAB
    # =========================================================================
    with tab_smc:
        st.subheader("Smart Money Concepts (SMC) / ICT Methodology")

        st.markdown("""
        Analisis institutional order flow berdasarkan ICT framework:
        - **Order Blocks**: Zona entry institusi
        - **Fair Value Gaps (FVG)**: Imbalance harga
        - **Liquidity Sweeps**: Stop hunts
        - **BOS/CHoCH**: Market structure shifts
        - **Premium/Discount**: Entry zone berdasarkan equilibrium
        - **Power of 3 (PO3)**: AMD pattern (Accumulation → Manipulation → Distribution)
        """)

        if st.button("Run SMC Analysis", key="smc_run"):
            with st.spinner("Running SMC analysis..."):
                try:
                    from .data_fetcher import fetch_market_data
                    from .module_integrator import ModuleIntegrator

                    integrator = ModuleIntegrator()
                    market_data = fetch_market_data(period="6mo")

                    target_df = None
                    for name, data in market_data.items():
                        if data is not None and not data.empty and len(data) >= 60:
                            target_df = data
                            break

                    if target_df is not None:
                        result = integrator.run_smc_analysis(target_df)
                        if result and "error" not in result:
                            col1, col2, col3 = st.columns(3)
                            with col1:
                                st.metric("Signal", result.get("signal", "HOLD"))
                            with col2:
                                st.metric("Confidence", f"{result.get('confidence', 50):.0f}")
                            with col3:
                                st.metric("Market Structure", result.get("market_structure", "unclear"))

                            col4, col5, col6 = st.columns(3)
                            with col4:
                                st.metric("Premium/Discount", result.get("premium_discount", "equilibrium"))
                            with col5:
                                st.metric("PO3 Phase", result.get("po3_phase", "N/A"))
                            with col6:
                                st.metric("Equilibrium", f"{result.get('equilibrium', 0):,.0f}")

                            st.info(result.get("recommendation", ""))

                            # Order Blocks
                            obs = result.get("order_blocks", [])
                            if obs:
                                st.markdown("### Order Blocks")
                                ob_df = pd.DataFrame(obs)
                                st.dataframe(ob_df, use_container_width=True)

                            # FVGs
                            fvgs = result.get("fair_value_gaps", [])
                            if fvgs:
                                st.markdown("### Fair Value Gaps")
                                fvg_df = pd.DataFrame(fvgs)
                                st.dataframe(fvg_df, use_container_width=True)

                            # Liquidity Sweeps
                            sweeps = result.get("liquidity_sweeps", [])
                            if sweeps:
                                st.markdown("### Liquidity Sweeps")
                                sweep_df = pd.DataFrame(sweeps)
                                st.dataframe(sweep_df, use_container_width=True)

                            # Structure Breaks
                            breaks = result.get("structure_breaks", [])
                            if breaks:
                                st.markdown("### Structure Breaks (BOS/CHoCH)")
                                break_df = pd.DataFrame(breaks)
                                st.dataframe(break_df, use_container_width=True)
                        else:
                            st.error(f"SMC analysis error: {result.get('error', 'unknown')}")
                    else:
                        st.warning("No suitable data found for SMC analysis.")
                except Exception as e:
                    st.error(f"Error: {e}")

    # =========================================================================
    # AFML TAB
    # =========================================================================
    with tab_afml:
        st.subheader("Advances in Financial Machine Learning (Lopez de Prado)")

        st.markdown("""
        Teknik ML tingkat lanjut dari Lopez de Prado:
        - **Triple-Barrier Labeling**: Path-dependent labels
        - **Fractional Differentiation**: Stationarity + memory
        - **Meta-Labeling**: Bet sizing dengan two-model architecture
        - **Deflated Sharpe Ratio**: Multiple testing correction
        """)

        col_pt, col_sl, col_hold = st.columns(3)
        with col_pt:
            pt_mult = st.number_input("Profit-Taking Multiplier", 0.5, 5.0, 1.0, 0.1)
        with col_sl:
            sl_mult = st.number_input("Stop-Loss Multiplier", 0.5, 5.0, 1.0, 0.1)
        with col_hold:
            max_hold = st.number_input("Max Holding Period (bars)", 5, 60, 10)

        if st.button("Run AFML Pipeline", key="afml_run"):
            with st.spinner("Running AFML pipeline..."):
                try:
                    from .data_fetcher import fetch_market_data
                    from .module_integrator import ModuleIntegrator

                    integrator = ModuleIntegrator()
                    market_data = fetch_market_data(period="1y")

                    target_df = None
                    for name, data in market_data.items():
                        if data is not None and not data.empty and len(data) >= 60:
                            target_df = data
                            break

                    if target_df is not None:
                        # Triple-barrier labels
                        tb_result = integrator.run_triple_barrier_labels(
                            target_df, pt_sl=(pt_mult, sl_mult), max_holding=int(max_hold)
                        )
                        if tb_result and "error" not in tb_result:
                            st.markdown("### Triple-Barrier Labels")
                            col1, col2, col3 = st.columns(3)
                            with col1:
                                st.metric("Buy Labels", f"{tb_result.get('buy_pct', 0):.1%}")
                            with col2:
                                st.metric("Sell Labels", f"{tb_result.get('sell_pct', 0):.1%}")
                            with col3:
                                st.metric("Hold Labels", f"{tb_result.get('hold_pct', 0):.1%}")

                        # Fractional differentiation
                        fd_result = integrator.run_fractional_diff(target_df)
                        if fd_result and "error" not in fd_result:
                            st.markdown("### Fractional Differentiation")
                            st.metric("Optimal d", f"{fd_result.get('optimal_d', 0):.2f}")
                            st.metric("Stationary", "Yes" if fd_result.get("is_stationary") else "No")

                        # Full pipeline
                        full_result = integrator.run_afml_pipeline(target_df)
                        if full_result and "error" not in full_result:
                            st.markdown("### Full AFML Pipeline Results")
                            st.json(full_result)
                    else:
                        st.warning("No suitable data found.")
                except Exception as e:
                    st.error(f"Error: {e}")

        st.markdown("---")
        st.markdown("### Deflated Sharpe Ratio Calculator")
        col_sr, col_nt, col_ns = st.columns(3)
        with col_sr:
            observed_sr = st.number_input("Observed Sharpe Ratio", -5.0, 10.0, 1.5, 0.1)
        with col_nt:
            n_trials = st.number_input("Number of Strategies Tested", 1, 10000, 10)
        with col_ns:
            n_samples = st.number_input("Number of Return Observations", 30, 5000, 252)

        if st.button("Calculate DSR", key="dsr_calc"):
            try:
                from .module_integrator import ModuleIntegrator
                integrator = ModuleIntegrator()
                dsr_result = integrator.calc_deflated_sharpe(observed_sr, n_trials, n_samples)
                if dsr_result and "error" not in dsr_result:
                    st.metric("Deflated Sharpe Ratio", f"{dsr_result['deflated_sr']:.4f}")
                    if dsr_result["is_significant"]:
                        st.success("✅ Strategy is statistically significant (DSR > 0)")
                    else:
                        st.warning("⚠️ Strategy may be a false discovery (DSR ≤ 0)")
                else:
                    st.error(f"DSR error: {dsr_result.get('error', 'unknown')}")
            except Exception as e:
                st.error(f"Error: {e}")

    # =========================================================================
    # DRL TAB
    # =========================================================================
    with tab_drl:
        st.subheader("Deep Reinforcement Learning Trading Agent")

        st.markdown("""
        Agen AI yang belajar optimal trading policy melalui trial-and-error.
        - **Q-Learning**: Fallback agent (selalu tersedia)
        - **DQN/PPO/SAC**: Memerlukan stable-baselines3 (optional)
        """)

        col_ep, col_algo = st.columns(2)
        with col_ep:
            episodes = st.number_input("Training Episodes", 50, 2000, 200, 50)
        with col_algo:
            algorithm = st.selectbox("Algorithm", ["q_learning", "dqn", "ppo", "sac"])

        if st.button("Train DRL Agent", key="drl_train"):
            with st.spinner(f"Training {algorithm} agent ({episodes} episodes)..."):
                try:
                    from .data_fetcher import fetch_market_data
                    from .module_integrator import ModuleIntegrator

                    integrator = ModuleIntegrator()
                    market_data = fetch_market_data(period="6mo")

                    target_df = None
                    for name, data in market_data.items():
                        if data is not None and not data.empty and len(data) >= 60:
                            target_df = data
                            break

                    if target_df is not None:
                        result = integrator.train_drl(target_df, episodes=int(episodes), algorithm=algorithm)
                        if result and "error" not in result:
                            st.success(f"Training complete! Algorithm: {result['algorithm']}")
                            col1, col2, col3 = st.columns(3)
                            with col1:
                                st.metric("Episodes", result["episodes"])
                            with col2:
                                st.metric("Mean Reward", f"{result['mean_reward']:.4f}")
                            with col3:
                                st.metric("Converged", "✅" if result["converged"] else "❌")
                            st.metric("Training Time", f"{result['training_time']:.1f}s")
                        else:
                            st.error(f"Training error: {result.get('error', 'unknown')}")
                    else:
                        st.warning("No suitable data found.")
                except Exception as e:
                    st.error(f"Error: {e}")

        st.markdown("---")
        if st.button("Get DRL Prediction", key="drl_predict"):
            with st.spinner("Getting DRL prediction..."):
                try:
                    from .data_fetcher import fetch_market_data
                    from .module_integrator import ModuleIntegrator

                    integrator = ModuleIntegrator()
                    market_data = fetch_market_data(period="6mo")

                    target_df = None
                    for name, data in market_data.items():
                        if data is not None and not data.empty and len(data) >= 60:
                            target_df = data
                            break

                    if target_df is not None:
                        result = integrator.predict_drl(target_df)
                        if result and "error" not in result:
                            col1, col2 = st.columns(2)
                            with col1:
                                st.metric("Action", result.get("action_name", "HOLD"))
                            with col2:
                                st.metric("Confidence", f"{result.get('confidence', 0.5):.0%}")
                            st.metric("Position Recommendation", f"{result.get('position_recommendation', 0):.2f}")
                        else:
                            st.warning("No trained model found. Train an agent first.")
                    else:
                        st.warning("No suitable data found.")
                except Exception as e:
                    st.error(f"Error: {e}")

    # =========================================================================
    # COMPLEX SYSTEMS TAB
    # =========================================================================
    with tab_cs:
        st.subheader("Complex Systems Network Analysis (CFA Institute 2025)")

        st.markdown("""
        Analisis pasar sebagai sistem kompleks:
        - **Correlation Network**: Hubungan antar aset
        - **Systemic Risk Nodes**: Aset dengan impact tertinggi
        - **Contagion Paths**: Rute penyebaran shock
        - **Stress Test**: Network-based stress scenarios
        """)

        col_thresh, col_shock = st.columns(2)
        with col_thresh:
            st.slider("Correlation Threshold", 0.1, 0.9, 0.3, 0.05)
        with col_shock:
            st.slider("Shock Magnitude (%)", -20, -1, -5) / 100

        if st.button("Run Network Analysis", key="cs_run"):
            with st.spinner("Building correlation network..."):
                try:
                    from .data_fetcher import fetch_market_data
                    from .module_integrator import ModuleIntegrator

                    integrator = ModuleIntegrator()
                    market_data = fetch_market_data(period="1y")

                    returns_df = pd.DataFrame()
                    for name, data in market_data.items():
                        if data is not None and not data.empty and "Close" in data.columns:
                            returns_df[name] = data["Close"].pct_change()

                    if not returns_df.empty and returns_df.shape[1] >= 2:
                        returns_df = returns_df.dropna()
                        if len(returns_df) > 20:
                            result = integrator.run_complex_systems(returns_df)
                            if result and "error" not in result:
                                col1, col2, col3 = st.columns(3)
                                with col1:
                                    st.metric("Network Density", f"{result.get('network_density', 0):.2%}")
                                with col2:
                                    st.metric("Avg Clustering", f"{result.get('avg_clustering', 0):.2f}")
                                with col3:
                                    st.metric("Contagion Risk", f"{result.get('contagion_risk', 0):.2%}")

                                st.markdown("### Systemic Risk Nodes")
                                systemic = result.get("systemic_nodes", [])
                                if systemic:
                                    for node in systemic:
                                        st.write(f"- **{node}**")

                                st.markdown("### Clusters")
                                clusters = result.get("clusters", {})
                                if clusters:
                                    for cid, members in clusters.items():
                                        st.write(f"- **Cluster {cid}**: {', '.join(members)}")

                                st.markdown("### Stress Test")
                                stress = result.get("stress_test", {})
                                if stress.get("worst_case"):
                                    wc = stress["worst_case"]
                                    st.warning(
                                        f"Worst case: Shock from **{wc['shock_source']}** → "
                                        f"avg portfolio impact: {wc['avg_portfolio_impact']:.2%} | "
                                        f"affected: {wc['affected_assets']} assets"
                                    )

                                st.info(result.get("recommendation", ""))
                            else:
                                st.error(f"Analysis error: {result.get('error', 'unknown')}")
                        else:
                            st.warning("Insufficient data for network analysis.")
                    else:
                        st.warning("Need at least 2 assets with data.")
                except Exception as e:
                    st.error(f"Error: {e}")
