"""Portfolio Management page — fund management, cash flow, allocation, PnL tracking."""
import streamlit as st
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go

from ..ui_components import section_header
from ..paper_trading import PaperTradingEngine
from ..config import BLUE_CHIPS_ID


def render_portfolio():
    section_header("💼", "Manajemen Portofolio & Keuangan")
    st.caption(
        "Kelola modal, deposit/withdraw, tracking PnL, dan alokasi portofolio. "
        "Paper trading dengan virtual portfolio — aman untuk testing strategi."
    )

    # Initialize paper trading engine in session state
    if "paper_engine" not in st.session_state:
        st.session_state["paper_engine"] = PaperTradingEngine()

    pt: PaperTradingEngine = st.session_state["paper_engine"]
    stats = pt.get_stats()

    # === DASHBOARD ===
    col_a, col_b, col_c, col_d = st.columns(4)
    col_a.metric("💰 Saldo Cash", f"Rp {stats['cash']:,.0f}")
    col_b.metric("📊 Total PnL", f"Rp {stats['total_realized_pnl']:,.0f}",
                 delta=f"{stats['total_realized_pnl_pct']:+.2f}%")
    col_c.metric("📈 Win Rate", f"{stats['win_rate']:.1f}%",
                 delta=f"{stats['winning_trades']}W / {stats['losing_trades']}L")
    col_d.metric("🔄 Net Invested", f"Rp {stats['net_invested']:,.0f}")

    col_e, col_f, col_g, col_h = st.columns(4)
    col_e.metric("💵 Total Deposit", f"Rp {stats['total_deposited']:,.0f}")
    col_f.metric("💸 Total Withdraw", f"Rp {stats['total_withdrawn']:,.0f}")
    col_g.metric("📋 Open Positions", f"{stats['open_positions']}")
    col_h.metric("📅 Daily PnL", f"Rp {stats['daily_pnl']:,.0f}")

    st.markdown("---")

    # === FUND MANAGEMENT ===
    section_header("🏦", "Manajemen Dana")
    st.write("Input modal awal, top-up deposit, atau tarik dana dari portfolio.")

    col1, col2, col3 = st.columns(3)

    with col1:
        st.subheader("💰 Set Modal Awal")
        st.caption("Reset portfolio dengan modal baru. Hanya jika belum ada posisi terbuka.")
        new_capital = st.number_input(
            "Modal Awal (Rp)",
            min_value=100_000,
            max_value=10_000_000_000,
            value=int(stats["initial_capital"]),
            step=100_000,
            format="%d",
        )
        if st.button("🔄 Reset Modal", type="primary"):
            if pt.get_open_positions():
                st.error("❌ Tutup semua posisi dulu sebelum reset modal!")
            else:
                pt.reset(new_capital=new_capital)
                st.success(f"✅ Portfolio direset ke Rp {new_capital:,.0f}")
                st.rerun()

    with col2:
        st.subheader("📥 Deposit Dana")
        st.caption("Top-up dana ke portfolio (menambah cash).")
        dep_amount = st.number_input(
            "Jumlah Deposit (Rp)",
            min_value=10_000,
            max_value=10_000_000_000,
            value=1_000_000,
            step=100_000,
            format="%d",
            key="dep_amount",
        )
        dep_reason = st.text_input("Alasan", value="Top-up modal", key="dep_reason")
        if st.button("📥 Deposit", key="btn_dep"):
            result = pt.deposit(dep_amount, reason=dep_reason)
            if result["status"] == "OK":
                st.success(f"✅ Deposit Rp {dep_amount:,.0f} berhasil! Saldo: Rp {result['cash']:,.0f}")
                st.rerun()
            else:
                st.error(f"❌ {result['reason']}")

    with col3:
        st.subheader("📤 Withdraw Dana")
        st.caption("Tarik dana dari portfolio (mengurangi cash).")
        wd_amount = st.number_input(
            "Jumlah Withdraw (Rp)",
            min_value=10_000,
            max_value=int(stats["cash"]),
            value=500_000,
            step=100_000,
            format="%d",
            key="wd_amount",
        )
        wd_reason = st.text_input("Alasan", value="Tarik dana", key="wd_reason")
        if st.button("📤 Withdraw", key="btn_wd"):
            result = pt.withdraw(wd_amount, reason=wd_reason)
            if result["status"] == "OK":
                st.success(f"✅ Withdraw Rp {wd_amount:,.0f} berhasil! Saldo: Rp {result['cash']:,.0f}")
                st.rerun()
            else:
                st.error(f"❌ {result['reason']}")

    st.markdown("---")

    # === CASH FLOW HISTORY ===
    section_header("📋", "Riwayat Cash Flow")
    cash_flow = pt.get_cash_flow()
    if cash_flow:
        cf_df = pd.DataFrame(cash_flow)
        cf_df["timestamp"] = pd.to_datetime(cf_df["timestamp"])
        cf_df["Type"] = cf_df["type"].map({"DEPOSIT": "📥 Deposit", "WITHDRAW": "📤 Withdraw"})
        cf_df["Amount"] = cf_df["amount"].apply(lambda x: f"Rp {x:,.0f}")
        cf_df["Balance"] = cf_df["balance_after"].apply(lambda x: f"Rp {x:,.0f}")
        cf_df["Time"] = cf_df["timestamp"].dt.strftime("%Y-%m-%d %H:%M")
        display_cols = ["Time", "Type", "Amount", "Balance", "reason"]
        st.dataframe(cf_df[display_cols], use_container_width=True, hide_index=True)

        # Cash flow chart
        fig = go.Figure()
        dep = cf_df[cf_df["type"] == "DEPOSIT"]
        wd = cf_df[cf_df["type"] == "WITHDRAW"]
        if not dep.empty:
            fig.add_trace(go.Bar(x=dep["timestamp"], y=dep["amount"], name="Deposit",
                                 marker_color="green", text=dep["amount"].apply(lambda x: f"Rp {x:,.0f}")))
        if not wd.empty:
            fig.add_trace(go.Bar(x=wd["timestamp"], y=-wd["amount"], name="Withdraw",
                                 marker_color="red", text=wd["amount"].apply(lambda x: f"Rp {x:,.0f}")))
        fig.update_layout(title="Cash Flow History", xaxis_title="Tanggal", yaxis_title="Amount (Rp)",
                          barmode="relative", height=300)
        st.plotly_chart(fig, use_container_width=True)
    else:
        st.info("📭 Belum ada transaksi cash flow. Lakukan deposit atau withdraw untuk mulai.")

    st.markdown("---")

    # === OPEN POSITIONS ===
    section_header("📊", "Posisi Terbuka")
    open_positions = pt.get_open_positions()
    if open_positions:
        pos_data = []
        for p in open_positions:
            pos_data.append({
                "Ticker": p.ticker,
                "Side": p.side,
                "Qty": p.quantity,
                "Entry": p.entry_price,
                "Current": p.current_price,
                "Stop Loss": p.stop_loss,
                "Take Profit": p.take_profit,
                "Unrealized PnL": p.unrealized_pnl,
                "PnL %": p.unrealized_pnl_pct,
                "Entry Date": p.entry_date[:10],
                "Signal": p.signal_at_entry,
                "Confidence": f"{p.confidence_at_entry:.1%}",
            })
        pos_df = pd.DataFrame(pos_data)
        st.dataframe(pos_df, use_container_width=True, hide_index=True)

        # Allocation pie chart
        allocation = pt.get_allocation()
        labels = ["Cash"]
        values = [allocation["cash"]["value"]]
        for ticker, info in allocation["positions"].items():
            labels.append(ticker)
            values.append(info["value"])
        fig_pie = px.pie(values=values, names=labels, title="Alokasi Portofolio",
                         color_discrete_sequence=px.colors.qualitative.Set2)
        fig_pie.update_layout(height=350)
        st.plotly_chart(fig_pie, use_container_width=True)

        # Manual close position
        st.markdown("---")
        st.subheader("🔧 Tutup Posisi Manual")
        close_ticker = st.selectbox("Pilih ticker", [p.ticker for p in open_positions])
        close_price = st.number_input(
            "Harga Exit",
            min_value=0.0,
            value=float(next(p.current_price for p in open_positions if p.ticker == close_ticker)),
            step=50.0,
        )
        if st.button("❌ Tutup Posisi"):
            result = pt.sell(close_ticker, 0, close_price, reason="MANUAL_CLOSE")
            if result.get("status") == "FILLED":
                st.success(f"✅ Posisi {close_ticker} ditutup. PnL: Rp {result['pnl']:,.0f} ({result['pnl_pct']:+.2f}%)")
                st.rerun()
            else:
                st.error(f"❌ {result.get('reason', 'Gagal')}")
    else:
        st.info("📭 Tidak ada posisi terbuka.")

    st.markdown("---")

    # === TRADE HISTORY ===
    section_header("📜", "Trade History")
    if pt.trade_history:
        hist_data = []
        for t in pt.trade_history[-30:]:
            hist_data.append({
                "Time": t["timestamp"][:19],
                "Ticker": t["ticker"],
                "Side": t["side"],
                "Qty": t["quantity"],
                "Price": t["price"],
                "Status": t["status"],
                "PnL": t.get("pnl", 0),
                "Reason": t.get("reason", ""),
            })
        hist_df = pd.DataFrame(hist_data)
        st.dataframe(hist_df, use_container_width=True, hide_index=True)

        # PnL over time chart
        sell_trades = [t for t in pt.trade_history if t.get("pnl", 0) != 0]
        if sell_trades:
            cum_pnl = []
            running = 0
            for t in sell_trades:
                running += t["pnl"]
                cum_pnl.append({"timestamp": t["timestamp"][:19], "cum_pnl": running})
            cum_df = pd.DataFrame(cum_pnl)
            fig_pnl = px.line(cum_df, x="timestamp", y="cum_pnl",
                              title="Cumulative Realized PnL", markers=True)
            fig_pnl.update_layout(xaxis_title="Waktu", yaxis_title="Cumulative PnL (Rp)", height=300)
            fig_pnl.add_hline(y=0, line_dash="dash", line_color="gray")
            st.plotly_chart(fig_pnl, use_container_width=True)
    else:
        st.info("📭 Belum ada trade history.")

    st.markdown("---")

    # === MULTI-TICKER SCANNER ===
    section_header("🔍", "Multi-Ticker Scanner")
    st.caption("Pilih saham blue chip untuk di-scan. Aplikasi akan evaluasi sinyal dan alokasi dana.")
    st.info(
        "💡 **Cara kerja auto-alokasi:**\n"
        "1. Pilih saham yang ingin di-scan\n"
        "2. Klik **Scan & Auto-Allocate**\n"
        "3. Aplikasi prediksi sinyal untuk setiap saham\n"
        "4. BUY signals di-sort by confidence\n"
        "5. Dana dialokasikan ke top picks (max 5 posisi)\n"
        "6. 10% cash buffer dijaga untuk opportunity\n"
        "7. Stop loss & take profit otomatis untuk setiap posisi"
    )

    selected_stocks = st.multiselect(
        "Pilih saham untuk scan",
        list(BLUE_CHIPS_ID.keys()),
        default=list(BLUE_CHIPS_ID.keys())[:5],
        format_func=lambda x: f"{x} — {BLUE_CHIPS_ID[x]}",
    )
    max_pos = st.slider("Max Posisi Simultan", min_value=1, max_value=10, value=5)

    if st.button("🔍 Scan & Auto-Allocate", type="primary"):
        if not selected_stocks:
            st.warning("Pilih minimal 1 saham")
        else:
            with st.spinner("Fetching data & running predictions..."):
                try:
                    from ..data_fetcher import fetch_all_data
                    from ..predictor import run_prediction
                    from ..models import HybridEnsemble
                    from ..trading_agent import TradingAgent, SafetyGuardrails

                    # Fetch data
                    tickers_to_fetch = {}
                    for ticker in selected_stocks:
                        tickers_to_fetch[ticker] = ticker
                    tickers_to_fetch["^JKSE"] = "^JKSE"

                    market_data = fetch_all_data(tickers_to_fetch)

                    # Run predictions
                    predictions = {}
                    progress = st.progress(0)
                    for i, ticker in enumerate(selected_stocks):
                        if ticker in market_data and not market_data[ticker].empty:
                            try:
                                ensemble = HybridEnsemble()
                                result = run_prediction(
                                    market_data=market_data,
                                    fred_data={},
                                    target_ticker=ticker,
                                    ensemble=ensemble,
                                )
                                if "error" not in result:
                                    predictions[ticker] = result
                            except Exception as e:
                                st.warning(f"Prediksi {ticker} gagal: {e}")
                        progress.progress((i + 1) / len(selected_stocks))

                    if predictions:
                        # Run multi-ticker cycle
                        agent = TradingAgent(
                            paper_engine=pt,
                            guardrails=SafetyGuardrails(),
                            auto_execute=True,
                        )
                        decisions = agent.run_multi_ticker_cycle(
                            market_data=market_data,
                            prediction_results=predictions,
                            max_positions=max_pos,
                        )

                        # Display results
                        st.success(f"✅ Scan selesai: {len(decisions)} keputusan")
                        dec_data = []
                        for d in decisions:
                            dec_data.append({
                                "Ticker": d["ticker"],
                                "Signal": d.get("signal", ""),
                                "Confidence": f"{d.get('confidence', 0):.1%}",
                                "Action": d["action"],
                                "PnL": d.get("order", {}).get("pnl", "") if isinstance(d.get("order"), dict) else "",
                            })
                        st.dataframe(pd.DataFrame(dec_data), use_container_width=True, hide_index=True)
                        st.rerun()
                    else:
                        st.error("Tidak ada prediksi yang berhasil. Coba lagi nanti.")
                except Exception as e:
                    st.error(f"Error: {e}")

    st.markdown("---")
    st.caption(
        "⚠️ **Disclaimer:** Paper trading = simulasi dengan uang virtual. "
        "Bukan saran investasi. Hasil paper trading tidak menjamin hasil real trading."
    )
