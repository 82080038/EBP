"""
Main Streamlit Dashboard — Thin Dispatcher
Aplikasi Proyeksi Pasar Saham Global
"""
import os
import sys
import streamlit as st

# Bootstrap: allow running both "streamlit run app.py" and "streamlit run src/app.py"
if __package__ is None or __package__ == "":
    # Running directly — fix sys.path and set package
    _src_dir = os.path.dirname(os.path.abspath(__file__))
    _project_root = os.path.dirname(_src_dir)
    if _project_root not in sys.path:
        sys.path.insert(0, _project_root)
    # Convert relative imports to absolute
    from src.ui_components import CUSTOM_CSS
    from src.database import init_db
    from src.pages.dashboard import render_dashboard
    from src.pages.prediksi import render_prediksi
    from src.pages.chart_teknikal import render_chart_teknikal
    from src.pages.sentiment import render_sentiment
    from src.pages.regime import render_regime
    from src.pages.intermarket import render_intermarket
    from src.pages.risk_management import render_risk_management
    from src.pages.portfolio import render_portfolio
    from src.pages.portfolio_v2 import render_portfolio as render_portfolio_v2
    from src.pages.unified_pipeline import render_unified_pipeline
    from src.pages.backtesting import render_backtesting
    from src.pages.riwayat import render_riwayat
    from src.pages.pengaturan import render_pengaturan
    from src.pages.trading_agent import render_trading_agent
    from src.pages.notifikasi import render_notifikasi
    from src.pages.market_hours import render_market_hours
    from src.pages.data_inventory import render_data_inventory
    from src.pages.advanced_analytics import render_advanced_analytics
    from src.pages.command_center import render_command_center
    from src.pages.screener import render_screener
    from src.pages.options_analysis import render_options_analysis
    from src.pages.broker_sim import render_broker_sim
    from src.pages.simulation import render_simulation
    from src.pages.system_check import render_system_check
    from src.database import get_jumlah_notifikasi_belum_dibaca
else:
    # Running as package module
    from .ui_components import CUSTOM_CSS
    from .database import init_db
    from .pages.dashboard import render_dashboard
    from .pages.prediksi import render_prediksi
    from .pages.chart_teknikal import render_chart_teknikal
    from .pages.sentiment import render_sentiment
    from .pages.regime import render_regime
    from .pages.intermarket import render_intermarket
    from .pages.risk_management import render_risk_management
    from .pages.portfolio import render_portfolio
    from .pages.portfolio_v2 import render_portfolio as render_portfolio_v2
    from .pages.unified_pipeline import render_unified_pipeline
    from .pages.backtesting import render_backtesting
    from .pages.riwayat import render_riwayat
    from .pages.pengaturan import render_pengaturan
    from .pages.trading_agent import render_trading_agent
    from .pages.notifikasi import render_notifikasi
    from .pages.market_hours import render_market_hours
    from .pages.data_inventory import render_data_inventory
    from .pages.advanced_analytics import render_advanced_analytics
    from .pages.command_center import render_command_center
    from .pages.screener import render_screener
    from .pages.options_analysis import render_options_analysis
    from .pages.broker_sim import render_broker_sim
    from .pages.simulation import render_simulation
    from .pages.system_check import render_system_check
    from .database import get_jumlah_notifikasi_belum_dibaca

st.set_page_config(
    page_title="Terminal Saham — Proyeksi Pasar Saham Global",
    page_icon="📈",
    layout="wide",
    initial_sidebar_state="expanded",
)

init_db()

st.markdown(CUSTOM_CSS, unsafe_allow_html=True)

# ===========================================================================
# SIDEBAR — Professional Navigation
# ===========================================================================
st.sidebar.markdown("## 📈 Terminal Saham")
st.sidebar.markdown('<span class="live-badge"><span class="live-dot"></span>LANGSUNG</span> **Edisi Profesional**', unsafe_allow_html=True)
st.sidebar.markdown("---")

page = st.sidebar.radio("Navigasi", [
    "🎯 Command Center",
    "📊 Dashboard",
    "🔮 Prediksi",
    "📈 Chart & Teknikal",
    "📡 Screener",
    "😰 Indeks Sentimen Pasar",
    "🎯 Kondisi Pasar",
    "🌐 Analisis Antar-Pasar",
    "⚠️ Manajemen Risiko",
    "💼 Optimasi Portofolio",
    "💰 Keuangan & Trading",
    "🔄 Daily Pipeline",
    "🧪 Backtesting",
    "🤖 Trading Agent",
    "🏦 Broker Simulation",
    "📐 Options Analysis",
    "🎮 Simulasi Pasar",
    "� Cek Sistem",
    "� Notifikasi",
    "📋 Riwayat & Akurasi",
    "⚙️ Pengaturan",
    "🌍 Market Hours",
    "📦 Data Inventory",
    "🔬 Analisis Lanjutan",
], index=0)

st.sidebar.markdown("---")

# Notification badge
belum_dibaca = get_jumlah_notifikasi_belum_dibaca()
if belum_dibaca > 0:
    st.sidebar.markdown(
        f'<div style="background: #ef4444; color: white; padding: 8px 12px; border-radius: 8px; '
        f'text-align: center; margin-bottom: 8px;">'
        f'🔔 <strong>{belum_dibaca} notifikasi belum dibaca</strong>'
        f'</div>',
        unsafe_allow_html=True,
    )

st.sidebar.markdown("""
<div style="font-size: 0.8em; color: #6b7280;">
<p><strong>⚠️ Disclaimer</strong></p>
<p>Prediksi bersifat probabilistik dan <strong>bukan saran investasi</strong>.</p>
<p>Lakukan riset mandiri sebelum keputusan investasi.</p>
</div>
""", unsafe_allow_html=True)

# ===========================================================================
# PAGE DISPATCH
# ===========================================================================
PAGE_MAP = {
    "🎯 Command Center": render_command_center,
    "📊 Dashboard": render_dashboard,
    "🔮 Prediksi": render_prediksi,
    "📈 Chart & Teknikal": render_chart_teknikal,
    "📡 Screener": render_screener,
    "😰 Indeks Sentimen Pasar": render_sentiment,
    "🎯 Kondisi Pasar": render_regime,
    "🌐 Analisis Antar-Pasar": render_intermarket,
    "⚠️ Manajemen Risiko": render_risk_management,
    "💼 Optimasi Portofolio": render_portfolio,
    "💰 Keuangan & Trading": render_portfolio_v2,
    "🔄 Daily Pipeline": render_unified_pipeline,
    "🧪 Backtesting": render_backtesting,
    "🤖 Trading Agent": render_trading_agent,
    "🏦 Broker Simulation": render_broker_sim,
    "📐 Options Analysis": render_options_analysis,
    "🎮 Simulasi Pasar": render_simulation,
    "💻 Cek Sistem": render_system_check,
    "🔔 Notifikasi": render_notifikasi,
    "📋 Riwayat & Akurasi": render_riwayat,
    "🌍 Market Hours": render_market_hours,
    "📦 Data Inventory": render_data_inventory,
    "🔬 Analisis Lanjutan": render_advanced_analytics,
    "⚙️ Pengaturan": render_pengaturan,
}

render_fn = PAGE_MAP.get(page)
if render_fn:
    render_fn()
