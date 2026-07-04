"""Risk Management page — VaR, CVaR, Sharpe, Sortino, Kelly, Position Sizing."""
import streamlit as st
import plotly.graph_objects as go

from ..ui_components import load_market_data, tt, section_header
from ..config import TICKERS, BLUE_CHIPS_ID
from ..risk_manager import calc_var, calc_risk_metrics, calc_position_sizing, calc_risk_per_sector


def render_risk_management():
    section_header("⚠️", "Manajemen Risiko Profesional")
    st.caption(f"{tt('VaR')}, {tt('CVaR')}, {tt('Sharpe')} Ratio, {tt('Sortino')}, {tt('Max Drawdown')}, {tt('Kelly')} Criterion, {tt('Position Sizing')}")

    data = load_market_data(period="2y")
    if not data:
        st.error("Gagal mengambil data.")
        return

    col1, col2 = st.columns([1, 3])
    with col1:
        target_options = {"IHSG (^JKSE)": "^JKSE"}
        target_options.update({f"{name} ({ticker})": ticker for name, ticker in BLUE_CHIPS_ID.items()})
        selected = st.selectbox("Pilih Target", list(target_options.keys()))
        target_ticker = target_options[selected]
        position_value = st.number_input("Nilai Posisi (Rp)", value=100_000_000, step=10_000_000)
        confidence_level = st.selectbox(f"Tingkat Kepercayaan {tt('VaR')}", [0.90, 0.95, 0.99], index=1)

    target_name = None
    for name, t in TICKERS.items():
        if t == target_ticker:
            target_name = name
            break

    if not (target_name and target_name in data):
        return

    df = data[target_name]
    returns = df["Close"].pct_change().dropna()
    prices = df["Close"]
    risk = calc_risk_metrics(returns, prices, position_value)

    with col2:
        st.subheader(f"📉 Value at Risk ({tt('VaR')})")
        var = calc_var(returns, confidence_level, position_value)
        col_a, col_b, col_c, col_d = st.columns(4)
        col_a.metric(f"{tt('VaR')} {int(confidence_level*100)}%", f"Rp {var['var_value']:,.0f}", f"{var['var_percent']}%")
        col_b.metric(f"{tt('CVaR')}", f"Rp {var['cvar_value']:,.0f}", f"{var['cvar_percent']}%")
        col_c.metric(f"{tt('VaR')} Parametrik", f"Rp {var['parametric_var_value']:,.0f}", f"{var['parametric_var_percent']}%")
        col_d.metric(f"{tt('Volatility')} Tahunan", f"{risk['annual_volatility_pct']}%")

        st.markdown("---")
        st.subheader("📊 Return Disesuaikan Risiko")
        col_e, col_f, col_g, col_h = st.columns(4)
        col_e.metric(f"{tt('Sharpe')} (Tahunan)", f"{risk['sharpe']['annualized_sharpe']}")
        col_f.metric(f"{tt('Sortino')} (Tahunan)", f"{risk['sortino']['annualized_sortino']}")
        col_g.metric(f"{tt('Calmar')} Ratio", f"{risk['calmar_ratio']}")
        col_h.metric("Return Tahunan", f"{risk['annual_return_pct']}%")

        st.markdown("---")
        st.subheader(f"📉 Analisis {tt('Drawdown')}")
        col_i, col_j, col_k = st.columns(3)
        col_i.metric(f"{tt('Max Drawdown')}", f"{risk['drawdown']['max_drawdown_pct']}%")
        col_j.metric("Durasi Max DD", f"{risk['drawdown']['max_dd_duration_days']} hari")
        col_k.metric("DD Saat Ini", f"{risk['drawdown']['current_drawdown_pct']}%")

        running_max = prices.cummax()
        drawdown = ((prices - running_max) / running_max) * 100
        fig_dd = go.Figure()
        fig_dd.add_trace(go.Scatter(x=drawdown.index, y=drawdown, name="Drawdown", fill="tozeroy", line=dict(color="red")))
        fig_dd.update_layout(title="Grafik Drawdown (%)", height=250, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
        st.plotly_chart(fig_dd, use_container_width=True)

        st.markdown("---")
        st.subheader(f"🎲 {tt('Kelly')} Criterion & {tt('Position Sizing')}")
        kelly = risk["kelly"]
        col_l, col_m, col_n = st.columns(3)
        col_l.metric(f"{tt('Kelly')} %", f"{kelly['kelly_fraction']}%")
        col_m.metric("Tingkat Menang", f"{kelly['win_rate']}%")
        col_n.metric("Faktor Profit", f"{kelly['profit_factor']}")
        st.info(f"**Rekomendasi:** {kelly['recommendation']}")

        st.markdown("---")
        st.subheader(f"🧮 Kalkulator {tt('Position Sizing')}")
        col_p1, col_p2, col_p3 = st.columns(3)
        with col_p1:
            entry_price = st.number_input("Harga Beli", value=float(prices.iloc[-1]), step=0.01)
            stop_loss = st.number_input("Harga Stop Loss", value=float(prices.iloc[-1] * 0.98), step=0.01)
        with col_p2:
            risk_per_trade = st.slider("Risiko per Transaksi (%)", 0.5, 5.0, 2.0, 0.5) / 100
            method = st.selectbox("Metode", ["risk_based", "atr_based", "fixed_fractional"])
        with col_p3:
            if "ATR" not in df.columns:
                from ..indicators import calc_atr
                df = calc_atr(df)
            atr_val = float(df["ATR"].iloc[-1]) if "ATR" in df else 0
            st.metric(f"{tt('ATR')} Saat Ini", f"{atr_val:.2f}")

        ps = calc_position_sizing(position_value, risk_per_trade, entry_price, stop_loss, atr_val, method)
        if "error" not in ps:
            st.success(f"**{ps['method']}**")
            col_q1, col_q2, col_q3, col_q4 = st.columns(4)
            col_q1.metric("Jumlah Saham", f"{ps['shares']:,}")
            col_q2.metric("Nilai Posisi", f"Rp {ps['position_value']:,.0f}")
            col_q3.metric("Jumlah Risiko", f"Rp {ps['risk_amount']:,.0f}")
            col_q4.metric("Persentase Posisi", f"{ps['position_pct']}%")
        else:
            st.error(ps["error"])

        st.markdown("---")
        st.subheader("📋 Perbandingan Risiko Antar Pasar")
        prices_dict = {name: d["Close"] for name, d in data.items() if not d.empty}
        risk_df = calc_risk_per_sector(prices_dict)
        st.dataframe(risk_df, use_container_width=True)
