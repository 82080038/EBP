"""
Streamlit Page: Command Center — Professional Trading Desk Layout.

Uses historical yfinance data to render the full decision → action → result
flow of the application in a single page. This is the "low-risk first step"
implementation from docs/UI_DESIGN.md: a native Streamlit Command Center
before migrating to React/Next.js.

Panels:
- Top bar: ticker search + market status + index summary
- Left: Watchlist (screener-ranked)
- Center: Multi-chart candlestick + volume + indicators
- Right: Simulated order book + depth
- Below chart: Decision strip (AI score, signal, confidence, risk)
- Bottom: Portfolio & Orders | Market Summary | Calendar
"""
import streamlit as st
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from typing import Dict, Optional


# ---------------------------------------------------------------------------
# CACHED DATA LOADERS — yfinance historical data
# ---------------------------------------------------------------------------
@st.cache_data(ttl=300, show_spinner=False)
def _load_all_market_data(period: str = "1y") -> Dict[str, pd.DataFrame]:
    """Load all configured market data from yfinance."""
    from ..data_fetcher import fetch_all_market_data
    try:
        return fetch_all_market_data(period=period)
    except Exception as e:
        st.error(f"Failed to load market data: {e}")
        return {}


@st.cache_data(ttl=300, show_spinner=False)
def _load_stock_data(ticker: str, period: str = "1y") -> pd.DataFrame:
    """Load single ticker OHLCV from yfinance."""
    from ..data_fetcher import fetch_yfinance_data
    try:
        return fetch_yfinance_data(ticker, period=period, interval="1d")
    except Exception as e:
        st.error(f"Failed to load {ticker}: {e}")
        return pd.DataFrame()


@st.cache_data(ttl=300, show_spinner=False)
def _run_screener(market_data: Dict[str, pd.DataFrame]) -> pd.DataFrame:
    """Run screener on blue chips using already-fetched market data."""
    from ..config import BLUE_CHIPS_ID
    from ..screener import run_screener, format_screener_results
    try:
        result = run_screener(market_data=market_data, tickers=BLUE_CHIPS_ID, top_n=10)
        return format_screener_results(result)
    except Exception as e:
        st.error(f"Screener error: {e}")
        return pd.DataFrame()


@st.cache_data(ttl=600, show_spinner=False)
def _run_prediction(target_ticker: str, market_data: Dict[str, pd.DataFrame]) -> Optional[Dict]:
    """Run the full prediction pipeline for a single ticker.

    Builds a market_data dict that always includes the selected ticker as
    the prediction target (keyed as 'TARGET' if not in configured TICKERS).
    """
    try:
        from ..predictor import run_prediction
        from ..config import TICKERS
        from ..data_fetcher import fetch_yfinance_data

        # Work on a copy so the original cached market_data is not mutated
        md = dict(market_data)

        # Find if the ticker is already a configured market ticker
        target_name = None
        for name, ticker in TICKERS.items():
            if ticker == target_ticker:
                target_name = name
                break

        if target_name is None:
            # Blue chip / individual stock: fetch and add as TARGET
            target_name = "TARGET"
            if target_name not in md or md[target_name].empty:
                df = fetch_yfinance_data(target_ticker, period="1y", interval="1d")
                if not df.empty:
                    md[target_name] = df

        if not md:
            return None

        return run_prediction(market_data=md, target_ticker=target_ticker)
    except Exception as e:
        st.error(f"Prediction error: {e}")
        return None


# ---------------------------------------------------------------------------
# UI HELPERS
# ---------------------------------------------------------------------------
def _change_color_class(pct: float) -> str:
    if pct > 0:
        return "color: #22c55e;"
    if pct < 0:
        return "color: #ef4444;"
    return "color: #9ca3af;"


def _render_status_badge(status: str) -> str:
    color = {
        "Delayed": "#f59e0b",
        "Simulated": "#8b5cf6",
        "Real-time": "#22c55e",
        "Offline": "#ef4444",
    }.get(status, "#9ca3af")
    return f'<span style="background:{color};color:#000;padding:2px 8px;border-radius:12px;font-size:0.75em;font-weight:700;">{status}</span>'


def _simulated_order_book(last_price: float, atr: float = 0.0, levels: int = 8) -> pd.DataFrame:
    """Build a simulated L2 order book around last price and ATR-based spread."""
    if atr <= 0:
        atr = last_price * 0.015
    spread = max(atr * 0.05, last_price * 0.0005)
    rows = []
    for i in range(levels):
        bid_price = last_price - spread * (i + 1)
        ask_price = last_price + spread * (i + 1)
        bid_qty = int(np.random.lognormal(8, 1.2) * (1 + i * 0.3))
        ask_qty = int(np.random.lognormal(8, 1.2) * (1 + i * 0.3))
        rows.append({
            "Level": i + 1,
            "Bid Qty": bid_qty,
            "Bid": bid_price,
            "Ask": ask_price,
            "Ask Qty": ask_qty,
            "Spread %": ((ask_price - bid_price) / last_price) * 100,
        })
    return pd.DataFrame(rows)


def _create_multi_chart(df: pd.DataFrame, title: str = "Chart Harga", levels: Optional[Dict] = None) -> object:
    """Create a candlestick chart with volume, MA, and optional entry/target/stop lines."""
    from plotly.subplots import make_subplots
    import plotly.graph_objects as go

    fig = make_subplots(
        rows=2, cols=1, shared_xaxes=True, vertical_spacing=0.03,
        row_heights=[0.75, 0.25], subplot_titles=(title, "Volume"),
    )

    fig.add_trace(go.Candlestick(
        x=df.index, open=df["Open"], high=df["High"],
        low=df["Low"], close=df["Close"], name="OHLC",
    ), row=1, col=1)

    # Moving averages
    for window, color, name in [(5, "#f59e0b", "MA5"), (20, "#3b82f6", "MA20"), (60, "#a855f7", "MA60")]:
        if len(df) >= window:
            ma = df["Close"].rolling(window=window).mean()
            fig.add_trace(go.Scatter(x=df.index, y=ma, name=name, line=dict(color=color, width=1)), row=1, col=1)

    # Entry / Target / Stop levels from prediction
    if levels:
        if levels.get("entry"):
            fig.add_hline(y=levels["entry"], line=dict(color="#60a5fa", width=1.5, dash="dash"), annotation_text="Entry", row=1, col=1)
        for idx, (key, color) in enumerate([("target_1", "#22c55e"), ("target_2", "#22c55e"), ("target_3", "#22c55e"), ("stop_loss", "#ef4444")], 1):
            if levels.get(key):
                fig.add_hline(y=levels[key], line=dict(color=color, width=1, dash="dot"), annotation_text=key.replace("_", " ").title(), row=1, col=1)

    # Volume
    if "Volume" in df.columns:
        colors = ["#22c55e" if df["Close"].iloc[i] >= df["Open"].iloc[i] else "#ef4444" for i in range(len(df))]
        fig.add_trace(go.Bar(x=df.index, y=df["Volume"], name="Volume", marker_color=colors), row=2, col=1)

    fig.update_layout(
        xaxis_rangeslider_visible=False,
        height=520,
        template="plotly_dark",
        paper_bgcolor="rgba(0,0,0,0)",
        plot_bgcolor="rgba(0,0,0,0)",
        showlegend=True,
        legend=dict(orientation="h", yanchor="bottom", y=1.02, xanchor="right", x=1),
        margin=dict(l=40, r=40, t=60, b=40),
    )
    return fig


def _render_decision_strip(prediction: Optional[Dict], df: pd.DataFrame):
    """Render the decision strip: AI score, signal, confidence, risk, explainability."""
    if prediction is None:
        st.info("Klik **Run Prediction** di atas untuk menghasilkan sinyal dari data historis yfinance.")
        return

    sinyal = prediction.get("sinyal", "HOLD")
    confidence = prediction.get("confidence", 0.0)
    ai_score = prediction.get("ai_score", 0.0)
    regime = prediction.get("market_regime", "unknown")
    current_price = prediction.get("current_price", 0.0)
    predicted_price = prediction.get("predicted_price", 0.0)
    risk_pct = prediction.get("risk_reward", 0.0)
    position_size = prediction.get("position_shares", 0)
    shap_exps = prediction.get("shap_explanations", {})
    shap_text = ""
    if shap_exps:
        # Summarize top SHAP features from the first available model
        first_exp = next(iter(shap_exps.values()))
        top = first_exp.get("top_features", [])
        shap_text = ", ".join([f"{name} ({value:+.4f})" for name, value in top[:3]])

    c1, c2, c3, c4, c5 = st.columns([1.2, 1, 1, 1.2, 2.2])

    with c1:
        badge_color = {
            "BUY": "#064e3b",
            "SELL": "#7f1d1d",
            "HOLD": "#78350f",
        }.get(sinyal, "#1e2a3a")
        text_color = {
            "BUY": "#22c55e",
            "SELL": "#ef4444",
            "HOLD": "#f59e0b",
        }.get(sinyal, "#9ca3af")
        st.markdown(
            f'<div style="background:{badge_color};border:1px solid {text_color};border-radius:8px;padding:10px 14px;text-align:center;">'
            f'<div style="color:{text_color};font-size:1.4em;font-weight:800;">{sinyal}</div>'
            f'<div style="color:#9ca3af;font-size:0.8em;">AI Score {ai_score:.1f}/10</div></div>',
            unsafe_allow_html=True,
        )

    with c2:
        st.metric("Confidence", f"{confidence:.1%}")
    with c3:
        st.metric("Regime", regime.upper())
    with c4:
        st.metric("Risk/Trade", f"{risk_pct:.2%}")
    with c5:
        st.markdown(
            f"<div style='font-size:0.85em;color:#9ca3af;'>"
            f"Harga saat ini: <b>{current_price:,.2f}</b> → Prediksi: <b>{predicted_price:,.2f}</b> "
            f"({((predicted_price - current_price) / current_price * 100):+.2f}%)<br>"
            f"Saran posisi: <b>{position_size:,.0f}</b> lembar<br>"
            f"<span style='color:#60a5fa;'>SHAP:</span> {shap_text}</div>",
            unsafe_allow_html=True,
        )


# ---------------------------------------------------------------------------
# MAIN RENDER
# ---------------------------------------------------------------------------
def render_command_center():
    st.markdown("# 🎯 Command Center")
    st.markdown("*Professional trading desk — decision, action, and result in one view*")

    # -----------------------------------------------------------------------
    # TOP BAR: index summary, market status, ticker selector
    # -----------------------------------------------------------------------
    market_data = _load_all_market_data(period="1y")

    top_c1, top_c2, top_c3, top_c4 = st.columns([1.5, 2, 1, 1])
    with top_c1:
        # Ticker selector
        from ..config import ALL_BLUE_CHIPS, TICKERS
        watchlist_tickers = {**TICKERS, **ALL_BLUE_CHIPS}
        selected_name = st.selectbox("Ticker", list(watchlist_tickers.keys()), index=0)
        selected_ticker = watchlist_tickers[selected_name]

    with top_c2:
        # Mini index summary
        if market_data:
            index_html = []
            for name in ["IHSG", "S&P500", "NASDAQ", "NIKKEI"]:
                if name in market_data and not market_data[name].empty:
                    df = market_data[name]
                    cur = df["Close"].iloc[-1]
                    prev = df["Close"].iloc[-2] if len(df) > 1 else cur
                    pct = ((cur - prev) / prev) * 100
                    style = _change_color_class(pct)
                    index_html.append(f"<span style='{style};font-weight:700;'>{name} {cur:,.2f} ({pct:+.2f}%)</span>")
            st.markdown(" &nbsp;|&nbsp; ".join(index_html), unsafe_allow_html=True)

    with top_c3:
        st.markdown("Status: " + _render_status_badge("Delayed") + " (yfinance 15m delay)", unsafe_allow_html=True)
    with top_c4:
        if st.button("🔄 Refresh Data", use_container_width=True):
            st.cache_data.clear()
            st.rerun()

    st.markdown("---")

    # -----------------------------------------------------------------------
    # MAIN LAYOUT: Watchlist | Chart | Order Book
    # -----------------------------------------------------------------------
    left_col, center_col, right_col = st.columns([1.1, 2.4, 1.1])

    # -----------------------------------------------------------------------
    # LEFT: Watchlist
    # -----------------------------------------------------------------------
    with left_col:
        st.markdown("##### 📋 Watchlist")
        screener_df = _run_screener(market_data)
        if not screener_df.empty:
            display_df = screener_df[["Ticker", "Name", "Price", "Change %", "Signal", "AI Score"]].copy()
            display_df["Change %"] = display_df["Change %"].apply(lambda x: f"{x:+.2f}%")
            display_df["AI Score"] = display_df["AI Score"].apply(lambda x: f"{x:.1f}")
            st.dataframe(
                display_df,
                column_config={"Ticker": st.column_config.TextColumn(width="small")},
                use_container_width=True,
                height=520,
                hide_index=True,
            )
            st.caption(f"Screener data: delayed (yfinance) | Last update: {datetime.now().strftime('%H:%M:%S')}")
        else:
            st.info("Watchlist data belum tersedia. Pastikan yfinance dapat diakses.")

    # -----------------------------------------------------------------------
    # CENTER: Chart + Prediction trigger
    # -----------------------------------------------------------------------
    with center_col:
        df = _load_stock_data(selected_ticker, period="1y")

        if df.empty:
            st.warning(f"Data historis untuk {selected_ticker} tidak tersedia.")
            return

        # Chart header + action buttons
        cc1, cc2, cc3 = st.columns([1.5, 1, 1])
        with cc1:
            st.markdown(f"##### 📈 {selected_name} ({selected_ticker})")
        with cc2:
            st.markdown("Status: " + _render_status_badge("Delayed"), unsafe_allow_html=True)
        with cc3:
            run_pred = st.button("🔮 Run Prediction", use_container_width=True)

        # Run prediction if requested
        prediction = None
        levels = None
        if run_pred:
            with st.spinner("Menjalankan pipeline prediksi..."):
                prediction = _run_prediction(selected_ticker, market_data)
            if prediction:
                st.success("Prediksi selesai.")
                # Build entry/target/stop levels for chart overlay
                levels = {
                    "entry": prediction.get("entry", 0),
                    "target_1": prediction.get("target_1", 0),
                    "target_2": prediction.get("target_2", 0),
                    "target_3": prediction.get("target_3", 0),
                    "stop_loss": prediction.get("stop_loss", 0),
                }
                levels = {k: v for k, v in levels.items() if v > 0}

        # Chart
        fig = _create_multi_chart(df, title=f"{selected_name} — Candlestick", levels=levels)
        st.plotly_chart(fig, use_container_width=True)

        # Decision strip
        st.markdown("---")
        _render_decision_strip(prediction, df)

    # -----------------------------------------------------------------------
    # RIGHT: Order Book (simulated) + Quick simulate trade
    # -----------------------------------------------------------------------
    with right_col:
        st.markdown("##### 📖 Order Book")
        st.markdown("Status: " + _render_status_badge("Simulated") + " (no real IDX L2 data)", unsafe_allow_html=True)

        last_price = df["Close"].iloc[-1]
        atr = (df["High"].iloc[-20:] - df["Low"].iloc[-20:]).mean() if len(df) >= 20 else last_price * 0.015
        order_book = _simulated_order_book(last_price, atr=atr, levels=8)
        st.dataframe(
            order_book,
            column_config={
                "Bid Qty": st.column_config.NumberColumn(width="small"),
                "Bid": st.column_config.NumberColumn(format="%.2f", width="small"),
                "Ask": st.column_config.NumberColumn(format="%.2f", width="small"),
                "Ask Qty": st.column_config.NumberColumn(width="small"),
                "Spread %": st.column_config.NumberColumn(format="%.3f%%", width="small"),
            },
            use_container_width=True,
            hide_index=True,
            height=220,
        )

        st.markdown("---")
        st.markdown("##### ⚡ Simulated Trade")
        side = st.selectbox("Side", ["BUY", "SELL"], key="cc_side")
        qty = st.number_input("Qty (lots)", min_value=1, value=10, step=1, key="cc_qty")
        if st.button("Submit Simulated Order", use_container_width=True, key="cc_submit"):
            try:
                from ..broker_sim import BrokerSimulator, SimOrder
                broker = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
                order = SimOrder(symbol=selected_ticker, side=side, quantity=qty * 100, order_type="MARKET")
                result = broker.submit_order(order, current_price=last_price)
                st.json({
                    "order_id": result.order_id,
                    "status": result.status,
                    "filled_qty": result.filled_qty,
                    "avg_fill_price": result.avg_fill_price,
                    "commission": result.commission,
                    "fees": result.fees,
                    "total_cost": result.total_cost,
                    "slippage_bps": result.slippage_bps,
                    "latency_ms": result.latency_ms,
                })
            except Exception as e:
                st.error(f"Order simulation failed: {e}")

    # -----------------------------------------------------------------------
    # BOTTOM PANEL: Portfolio | Market Summary | Calendar
    # -----------------------------------------------------------------------
    st.markdown("---")
    bot1, bot2, bot3 = st.columns([1.2, 1.4, 1.2])

    with bot1:
        st.markdown("##### 💼 Portfolio & Orders")
        try:
            from ..broker_sim import BrokerSimulator
            broker = BrokerSimulator(broker="bca_sekuritas", capital=100_000_000)
            portfolio = broker.get_portfolio()
            positions = portfolio.get("positions", {})
            if positions:
                pos_rows = []
                for sym, pos in positions.items():
                    pos_rows.append({
                        "Symbol": sym,
                        "Qty": pos.quantity,
                        "Avg": pos.avg_price,
                        "Market": pos.market_price,
                        "Unrealized PnL": pos.unrealized_pnl,
                    })
                st.dataframe(pd.DataFrame(pos_rows), use_container_width=True, hide_index=True)
            else:
                st.info("Belum ada posisi. Jalankan simulated trade di panel kanan.")
        except Exception as e:
            st.info(f"Portfolio data belum tersedia: {e}")

    with bot2:
        st.markdown("##### 🌐 Market Summary")
        if market_data:
            summary_rows = []
            for name, df in market_data.items():
                if df.empty or name not in ["IHSG", "S&P500", "NASDAQ", "DOW", "NIKKEI", "HANG_SENG", "STI"]:
                    continue
                cur = df["Close"].iloc[-1]
                prev = df["Close"].iloc[-2] if len(df) > 1 else cur
                pct = ((cur - prev) / prev) * 100
                summary_rows.append({"Index": name, "Price": cur, "Change %": pct})
            summary_df = pd.DataFrame(summary_rows).sort_values("Change %", ascending=False)
            summary_df["Change %"] = summary_df["Change %"].apply(lambda x: f"{x:+.2f}%")
            st.dataframe(summary_df, use_container_width=True, hide_index=True, height=180)
        else:
            st.info("Market summary tidak tersedia.")

    with bot3:
        st.markdown("##### 📅 Calendar & Macro")
        try:
            from ..config import FRED_SERIES
            from ..data_fetcher import fetch_fred_data
            macro_html = ["<table style='width:100%;font-size:0.85em;color:#e0e6ed;'>"]
            for series_name, series_id in FRED_SERIES.items():
                try:
                    s = fetch_fred_data(series_id, observation_start=(datetime.now() - timedelta(days=90)).strftime("%Y-%m-%d"))
                    if not s.empty:
                        last_val = s.iloc[-1]
                        date = s.index[-1].strftime("%Y-%m-%d") if hasattr(s.index[-1], "strftime") else str(s.index[-1])
                        macro_html.append(f"<tr><td>{series_name}</td><td style='text-align:right;'><b>{last_val:.2f}</b></td><td style='color:#9ca3af;text-align:right;'>{date}</td></tr>")
                except Exception:
                    pass
            macro_html.append("</table>")
            st.markdown("".join(macro_html), unsafe_allow_html=True)
        except Exception as e:
            st.info(f"Macro data belum tersedia: {e}")

    # -----------------------------------------------------------------------
    # PROCESS VISIBILITY FOOTER
    # -----------------------------------------------------------------------
    st.markdown("---")
    st.markdown(
        "<div style='font-size:0.8em;color:#6b7280;'>"
        "Flow: <b>yfinance historical data</b> → <b>feature engineering</b> → "
        "<b>ensemble ML prediction</b> → <b>AI score + risk</b> → "
        "<b>simulated trade</b> → <b>portfolio result</b> "
        "| Data source: yfinance (delayed, for testing only)"
        "</div>",
        unsafe_allow_html=True,
    )
