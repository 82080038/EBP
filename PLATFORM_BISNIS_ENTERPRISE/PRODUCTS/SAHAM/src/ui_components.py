"""
Shared UI components, CSS theme, glossary, and helper functions for Streamlit dashboard.
"""
import streamlit as st
import plotly.graph_objects as go
from plotly.subplots import make_subplots

from .data_fetcher import fetch_all_data, fetch_yfinance_data, fetch_all_market_data


# ===========================================================================
# CUSTOM CSS — Professional Trading Platform Theme
# ===========================================================================
CUSTOM_CSS = """
<style>
/* ===== GLOBAL ===== */
.stApp {
    background-color: #0a0e17;
    color: #e0e6ed;
}
.stApp .stMarkdown, .stApp .stText {
    color: #e0e6ed;
}

/* ===== SIDEBAR ===== */
section[data-testid="stSidebar"] {
    background-color: #111827;
    border-right: 1px solid #1e2a3a;
}
section[data-testid="stSidebar"] .stMarkdown, section[data-testid="stSidebar"] label {
    color: #9ca3af;
}

/* ===== METRIC CARDS ===== */
div[data-testid="stMetric"] {
    background-color: #141b2d;
    border: 1px solid #1e2a3a;
    border-radius: 8px;
    padding: 12px 16px;
}
div[data-testid="stMetric"] label {
    color: #9ca3af !important;
    font-size: 0.85em !important;
}
div[data-testid="stMetric"] div[data-testid="stMetricValue"] {
    color: #e0e6ed !important;
    font-weight: 700 !important;
}

/* ===== TABS ===== */
.stTabs [data-baseweb="tab-list"] {
    gap: 8px;
    background-color: #111827;
    border-radius: 8px 8px 0 0;
    padding: 4px;
}
.stTabs [data-baseweb="tab"] {
    background-color: #1a2332;
    border-radius: 6px;
    color: #9ca3af;
    padding: 8px 20px;
    font-weight: 500;
}
.stTabs [aria-selected="true"] {
    background-color: #1e3a5f !important;
    color: #60a5fa !important;
}

/* ===== DATAFRAME ===== */
.dataframe {
    background-color: #111827 !important;
    color: #e0e6ed !important;
}
.dataframe th {
    background-color: #1a2332 !important;
    color: #60a5fa !important;
}
.dataframe td {
    color: #e0e6ed !important;
}

/* ===== TICKER TAPE ===== */
.ticker-container {
    overflow: hidden;
    background-color: #0d1421;
    border-bottom: 1px solid #1e2a3a;
    padding: 6px 0;
    margin-bottom: 16px;
}
.ticker-track {
    display: inline-block;
    white-space: nowrap;
    animation: ticker-scroll 60s linear infinite;
}
@keyframes ticker-scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.ticker-item {
    display: inline-block;
    padding: 0 24px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Courier New', monospace;
}
.ticker-up { color: #22c55e; }
.ticker-down { color: #ef4444; }
.ticker-neutral { color: #9ca3af; }

/* ===== SIGNAL VERDICT BOX ===== */
.verdict-box {
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    margin: 16px 0;
}
.verdict-buy {
    background: linear-gradient(135deg, #052e16, #064e3b);
    border: 2px solid #22c55e;
}
.verdict-sell {
    background: linear-gradient(135deg, #2e0505, #7f1d1d);
    border: 2px solid #ef4444;
}
.verdict-hold {
    background: linear-gradient(135deg, #2e2705, #78350f);
    border: 2px solid #f59e0b;
}
.verdict-title {
    font-size: 1.5em;
    font-weight: 800;
    margin: 0 0 8px 0;
}
.verdict-subtitle {
    font-size: 0.9em;
    color: #9ca3af;
    margin: 0 0 8px 0;
}

/* ===== STEP PROGRESS ===== */
.step-container {
    background-color: #111827;
    border-radius: 8px;
    padding: 12px 16px;
    margin: 12px 0;
}
.step-item {
    display: flex;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid #1e2a3a;
}
.step-item:last-child { border-bottom: none; }
.step-item.done { color: #22c55e; }
.step-item.active { color: #60a5fa; }
.step-item.pending { color: #6b7280; }
.step-item.error { color: #ef4444; }
.step-icon {
    font-size: 1.2em;
    margin-right: 10px;
}
.step-text {
    color: #e0e6ed;
    font-size: 0.9em;
}
.step-detail {
    color: #6b7280;
    font-size: 0.8em;
    margin-left: auto;
}

/* ===== SIGNAL BADGE ===== */
.signal-badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.9em;
}
.signal-buy { background-color: #064e3b; color: #22c55e; }
.signal-sell { background-color: #7f1d1d; color: #ef4444; }
.signal-hold { background-color: #78350f; color: #f59e0b; }
.signal-neutral { background-color: #1e2a3a; color: #9ca3af; }

/* ===== INFO/ALERT BOXES ===== */
div[data-testid="stAlert"] {
    border-radius: 8px;
}
div[data-testid="stAlert-info"] {
    background-color: #0c1929;
    border: 1px solid #1e3a5f;
}
div[data-testid="stAlert-success"] {
    background-color: #052e16;
    border: 1px solid #22c55e;
}
div[data-testid="stAlert-warning"] {
    background-color: #2e2705;
    border: 1px solid #f59e0b;
}

/* ===== BUTTONS ===== */
.stButton > button {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s;
}
.stButton > button[kind="primary"] {
    background-color: #1e3a5f;
    border: 1px solid #60a5fa;
    color: #60a5fa;
}
.stButton > button[kind="primary"]:hover {
    background-color: #1e5089;
    border-color: #93c5fd;
}

/* ===== SPINNER ===== */
.stSpinner > div {
    border-top-color: #60a5fa !important;
}

/* ===== PROGRESS BAR ===== */
.stProgress > div > div {
    background-color: #60a5fa;
}

/* ===== SEPARATOR ===== */
hr {
    border-color: #1e2a3a !important;
    margin: 16px 0 !important;
}

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: #0a0e17; }
::-webkit-scrollbar-thumb { background: #1e2a3a; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #2a3a4a; }

/* ===== SECTION HEADER ===== */
.section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
    margin-bottom: 8px;
    border-bottom: 1px solid #1e2a3a;
}
.section-header-icon {
    font-size: 1.3em;
}
.section-header-title {
    font-size: 1.1em;
    font-weight: 700;
    color: #60a5fa;
}

/* ===== LIVE INDICATOR ===== */
.live-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #22c55e;
    animation: pulse-live 2s infinite;
    margin-right: 6px;
}
@keyframes pulse-live {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}
.live-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 10px;
    border-radius: 12px;
    background-color: #052e16;
    color: #22c55e;
    font-size: 0.75em;
    font-weight: 600;
}

/* ===== TOOLTIP untuk istilah teknis ===== */
.tooltip-term {
    position: relative;
    cursor: help;
    border-bottom: 1px dotted #60a5fa;
    color: #60a5fa;
    font-weight: 500;
}
.tooltip-term .tooltip-text {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    bottom: 140%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #1a2332;
    color: #e0e6ed;
    text-align: left;
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid #2a3a4a;
    font-size: 0.82em;
    font-weight: 400;
    line-height: 1.5;
    width: 320px;
    max-width: 400px;
    z-index: 9999;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    transition: opacity 0.2s, visibility 0.2s;
    pointer-events: none;
    white-space: normal;
}
.tooltip-term:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
}
.tooltip-term .tooltip-text::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -6px;
    border-width: 6px;
    border-style: solid;
    border-color: #1a2332 transparent transparent transparent;
}
</style>
"""


# ===========================================================================
# GLOSSARY ISTILAH PASAR MODAL (Bahasa Indonesia)
# ===========================================================================
GLOSSARY = {
    "VIX": "Indeks Ketakutan Pasar — mengukur volatilitas yang diharapkan dari S&P 500. VIX tinggi (>30) = pasar panik/takut, VIX rendah (<20) = pasar tenang/serakah",
    "RSI": "Relative Strength Index — indikator momentum skala 0-100. Nilai >70 = overbought (harga terlalu mahal, waktunya jual), <30 = oversold (harga terlalu murah, waktunya beli)",
    "MACD": "Moving Average Convergence Divergence — indikator tren yang membandingkan 2 rata-rata harga. MACD > Signal = tren naik (beli), MACD < Signal = tren turun (jual)",
    "MA": "Moving Average (Rata-rata Pergerakan) — harga rata-rata dalam periode tertentu. MA5 = rata-rata 5 hari, MA20 = rata-rata 20 hari, MA200 = rata-rata 200 hari",
    "MA5": "Rata-rata pergerakan harga 5 hari terakhir — indikator tren jangka pendek",
    "MA10": "Rata-rata pergerakan harga 10 hari terakhir — indikator tren jangka pendek",
    "MA20": "Rata-rata pergerakan harga 20 hari terakhir — indikator tren menengah",
    "MA50": "Rata-rata pergerakan harga 50 hari terakhir — indikator tren menengah",
    "MA100": "Rata-rata pergerakan harga 100 hari terakhir — indikator tren menengah-panjang",
    "MA200": "Rata-rata pergerakan harga 200 hari terakhir — indikator tren jangka panjang (standar industri)",
    "BB": "Bollinger Bands — pita yang mengukur volatilitas harga. Harga menyentuh pita atas = cenderung mahal, pita bawah = cenderung murah",
    "Bollinger Bands": "Pita statistik di atas dan di bawah rata-rata harga. Pita melebar = volatilitas tinggi, pita menyempit = volatilitas rendah",
    "Stochastic": "Oscillator yang membandingkan harga penutupan dengan rentang harga periode tertentu. >80 = overbought, <20 = oversold",
    "ADX": "Average Directional Index — mengukur kekuatan tren (bukan arah). ADX >25 = tren kuat, <20 = tren lemah/sideways",
    "ATR": "Average True Range — mengukur volatilitas rata-rata harga dalam periode tertentu. ATR tinggi = volatilitas tinggi",
    "VaR": "Value at Risk — estimasi kerugian maksimum dalam periode tertentu pada tingkat kepercayaan tertentu. Contoh: VaR 95% Rp 10jt = 95% kemungkinan kerugian tidak melebihi Rp 10jt",
    "CVaR": "Conditional Value at Risk — rata-rata kerugian jika kerugian melebihi VaR. Mengukur skenario terburuk (tail risk)",
    "Sharpe": "Sharpe Ratio — rasio yang mengukur return investasi relatif terhadap risiko. >1 = baik, >2 = sangat baik, <0 = buruk",
    "Sortino": "Sortino Ratio — seperti Sharpe tapi hanya memperhitungkan kerugian (downside risk), bukan total volatilitas. Lebih akurat untuk evaluasi risiko",
    "Calmar": "Calmar Ratio — rasio return tahunan terhadap max drawdown. Semakin tinggi semakin baik",
    "Drawdown": "Penurunan nilai dari puncak tertinggi ke titik terendah. Mengukur seberapa besar kerugian yang bisa terjadi secara historis",
    "Max Drawdown": "Penurunan terbesar yang pernah terjadi dari puncak ke lembah. Semakin kecil semakin baik",
    "Kelly": "Kelly Criterion — formula untuk menentukan ukuran posisi optimal berdasarkan tingkat kemenangan dan rasio profit/loss",
    "Fear & Greed": "Indeks Sentimen Pasar skala 0-100. 0 = ketakutan ekstrem (waktu yang baik untuk beli), 100 = keserakahan ekstrem (waktu untuk berhati-hati)",
    "Market Regime": "Kondisi pasar saat ini: Bull = tren naik/positif, Bear = tren turun/negatif, Sideways = datar/tanpa arah jelas",
    "Bull": "Pasar bull — kondisi pasar yang sedang naik/tren positif. Harga cenderung terus meningkat",
    "Bear": "Pasar bear — kondisi pasar yang sedang turun/tren negatif. Harga cenderung terus menurun",
    "Sideways": "Pasar sideways — kondisi pasar yang bergerak datar tanpa arah tren yang jelas. Harga bergerak naik-turun dalam rentang sempit",
    "Composite": "Sinyal Gabungan — kesimpulan dari semua indikator teknikal. BUY = sinyal beli, SELL = sinyal jual, HOLD = tahan",
    "Efficient Frontier": "Batas Efisien — kurva yang menunjukkan kombinasi portofolio dengan return tertinggi untuk setiap level risiko. Portofolio di kurva ini = optimal",
    "MAPE": "Mean Absolute Percentage Error — rata-rata error prediksi dalam persen. Semakin rendah semakin akurat. <5% = sangat akurat",
    "Directional Accuracy": "Akurasi Arah — persentase berapa kali model benar memprediksi arah pergerakan harga (naik/turun). >50% = lebih baik dari tebakan acak",
    "LSTM": "Long Short-Term Memory — jenis neural network yang dirancang khusus untuk data time series seperti harga saham",
    "Z-Score": "Skor Standar — menunjukkan seberapa jauh nilai saat ini dari rata-rata historis (dalam satuan standar deviasi). >2 = di atas normal, <-2 = di bawah normal",
    "Portfolio": "Portofolio — kumpulan investasi dalam berbagai aset untuk diversifikasi risiko. Jangan taruh semua telur di satu keranjang",
    "Volatility": "Volatilitas — seberapa besar harga berfluktuasi. Volatilitas tinggi = risiko tinggi tapi potensi return juga tinggi",
    "Position Sizing": "Penentuan Ukuran Posisi — berapa banyak saham/unit yang dibeli berdasarkan manajemen risiko. Tujuannya: jangan rugi terlalu besar dalam satu transaksi",
    "Buy & Hold": "Beli dan Tahan — strategi beli saham lalu dipegang jangka panjang tanpa jual-beli aktif. Benchmark untuk menilai strategi trading",
    "Equity Curve": "Kurva Pertumbuhan Modal — grafik yang menunjukkan perkembangan nilai portofolio/investasi dari waktu ke waktu",
    "Spread Analysis": "Analisis Selisih Harga — membandingkan harga 2 aset untuk identifikasi anomali atau peluang arbitrase",
    "Lead-Lag": "Analisis Pendahulu-Pengikut — pasar mana yang bergerak lebih dulu (pendahulu) dan mana yang mengikuti (pengikut)",
    "Correlation": "Korelasi — hubungan antar 2 aset. +1 = bergerak searah, -1 = bergerak berlawanan, 0 = tidak berhubungan",
    "Rolling Correlation": "Korelasi Bergulir — korelasi yang dihitung dalam jendela waktu tertentu (misal 60 hari) untuk melihat perubahan hubungan antar pasar",
    "Confidence": "Tingkat Keyakinan — seberapa yakin model dengan prediksinya. >60% = cukup yakin, >80% = sangat yakin",
    "Overbought": "Overbought (Terbeli Berlebihan) — harga dianggap terlalu mahal, kemungkinan akan turun. RSI >70 atau Stochastic >80",
    "Oversold": "Oversold (Terjual Berlebihan) — harga dianggap terlalu murah, kemungkinan akan naik. RSI <30 atau Stochastic <20",
    "Bullish": "Bullish (Naik) — sinyal bahwa harga diperkirakan akan naik. Waktu yang baik untuk membeli",
    "Bearish": "Bearish (Turun) — sinyal bahwa harga diperkirakan akan turun. Waktu yang baik untuk menjual",
    "Neutral": "Neutral (Netral) — tidak ada sinyal jelas naik atau turun. Sebaiknya tunggu konfirmasi",
    "Ensemble": "Ensemble Model — kombinasi beberapa model AI untuk prediksi yang lebih akurat. Seperti meminta pendapat dari beberapa ahli sekaligus",
    "Backtesting": "Backtesting — menguji strategi trading pada data historis untuk melihat performanya di masa lalu. Bukan jaminan performa masa depan",
    "Risk-Free Rate": "Suku Bunga Tanpa Risiko — return investasi paling aman (misal: SBN/ORI). Digunakan sebagai benchmark untuk menilai investasi berisiko",
    "Blue Chip": "Saham Blue Chip — saham perusahaan besar, mapan, dan terpercaya. Contoh di Indonesia: BBCA, BBRI, TLKM",
    "Ticker": "Ticker — kode singkat untuk saham/indeks di bursa. Contoh: ^JKSE = IHSG, BBCA.JK = Bank Central Asia",
    "IHSG": "Indeks Harga Saham Gabungan — indeks utama Bursa Efek Indonesia. Mengukur pergerakan harga semua saham tercatat di BEI",
    "S&P500": "Standard & Poor's 500 — indeks 500 perusahaan terbesar di Amerika Serikat. Barometer ekonomi AS",
    "NASDAQ": "Indeks Nasdaq Composite — indeks saham teknologi di Amerika Serikat. Mewakili sektor teknologi global",
    "STI": "Straits Times Index — indeks saham utama Singapura. Salah satu barometer pasar Asia Tenggara",
    "DOW": "Dow Jones Industrial Average — indeks 30 perusahaan terbesar AS. Indeks saham tertua di dunia",
    "NIKKEI": "Nikkei 225 — indeks 225 saham terbesar di Bursa Tokyo, Jepang",
    "HANG_SENG": "Hang Seng Index — indeks saham utama Hong Kong. Barometer pasar Asia",
    "GOLD": "Harga Emas — komoditas safe haven (tempat berlindung saat pasar kacau). Harga emas naik = investor takut",
    "OIL": "Harga Minyak Mentah WTI — komoditas energi utama. Harga minyak naik = inflasi berpotensi naik",
    "USD_IDR": "Nilai Tukar USD/IDR — kurs Dolar AS terhadap Rupiah. USD naik = Rupiah melemah = saham bisa tertekan",
    "OHLC": "Open High Low Close — harga pembukaan, tertinggi, terendah, dan penutupan dalam satu periode (hari/minggu)",
    "Candlestick": "Candlestick — jenis grafik yang menunjukkan harga pembukaan, tertinggi, terendah, dan penutupan. Hijau/putih = naik, Merah/hitam = turun",
    "SSE_COMPOSITE": "Shanghai Composite Index — indeks utama Bursa Shanghai (A-shares). Barometer pasar saham China daratan",
    "CSI300": "CSI 300 Index — indeks 300 saham terbesar di China (Shanghai + Shenzhen). Setara S&P500 untuk pasar China",
    "CNY_IDR": "Kurs Yuan China (CNY) ke Rupiah (IDR) — mengukur nilai tukar China-Indonesia. CNY menguat = ekspor Indonesia ke China lebih kompetitif",
    "DeepSeek": "DeepSeek — AI model open-source dari China (performa setara GPT tapi 1/30 biaya). Inspirasi untuk built-in market commentary di aplikasi ini",
    "Pusat Notifikasi": "Pusat Notifikasi — semua notifikasi prediksi, briefing, dan trading agent tersimpan di database lokal. Tidak butuh API eksternal",
    "Trading Agent": "Trading Agent — AI agent otonom yang monitor pasar, analisis sinyal, dan eksekusi trade dengan safety guardrails. OpenClaw-style automation",
    "Kill Switch": "Kill Switch — tombol darurat untuk menghentikan semua aktivitas trading agent. Gunakan saat pasar kacau atau ada error",
}


def tt(term):
    """Tooltip helper — wraps a term with hover explanation in Indonesian."""
    explanation = GLOSSARY.get(term, term)
    return f'<span class="tooltip-term">{term}<span class="tooltip-text">{explanation}</span></span>'


# ===========================================================================
# CACHED DATA LOADERS
# ===========================================================================
@st.cache_data(ttl=300)
def load_market_data(period="2y"):
    return fetch_all_market_data(period=period)


@st.cache_data(ttl=300)
def load_all_data(period="2y"):
    return fetch_all_data(period=period)


@st.cache_data(ttl=300)
def load_stock_data(ticker, period="1y"):
    return fetch_yfinance_data(ticker, period=period)


# ===========================================================================
# RENDER HELPERS
# ===========================================================================
def render_ticker_tape(data):
    """Render scrolling ticker tape with live prices."""
    items = []
    ticker_names = {"IHSG": "^JKSE", "S&P500": "^GSPC", "NASDAQ": "^IXIC", "DOW": "^DJI",
                    "NIKKEI": "^N225", "HANG_SENG": "^HSI", "STI": "^STI", "GOLD": "GC=F",
                    "OIL": "CL=F", "VIX": "^VIX", "USD_IDR": "USDIDR=X",
                    "SSE_COMPOSITE": "000001.SS", "CSI300": "000300.SS", "CNY_IDR": "CNYIDR=X"}
    for name, ticker in ticker_names.items():
        for key in data:
            if key == name and not data[key].empty:
                df = data[key]
                current = df["Close"].iloc[-1]
                prev = df["Close"].iloc[-2] if len(df) > 1 else current
                change_pct = ((current - prev) / prev) * 100
                direction = "▲" if change_pct > 0 else "▼" if change_pct < 0 else "—"
                css_class = "ticker-up" if change_pct > 0 else "ticker-down" if change_pct < 0 else "ticker-neutral"
                items.append(f'<span class="ticker-item {css_class}">{name} {current:,.2f} {direction} {change_pct:+.2f}%</span>')
                break
    if items:
        track = "".join(items)
        st.markdown(f'<div class="ticker-container"><div class="ticker-track">{track}{track}</div></div>', unsafe_allow_html=True)


def create_sparkline(prices, height=40, width=120):
    """Create a mini sparkline chart for metric cards."""
    fig = go.Figure()
    fig.add_trace(go.Scatter(
        y=prices.values, x=prices.index,
        mode="lines", line=dict(color="#22c55e" if prices.iloc[-1] >= prices.iloc[0] else "#ef4444", width=1.5),
        fill="tozeroy", fillcolor="rgba(34,197,94,0.1)" if prices.iloc[-1] >= prices.iloc[0] else "rgba(239,68,68,0.1)",
    ))
    fig.update_layout(
        height=height, width=width, margin=dict(l=0, r=0, t=0, b=0),
        xaxis=dict(visible=False), yaxis=dict(visible=False),
        template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)",
        showlegend=False,
    )
    return fig


def render_verdict_box(sinyal, confidence, current_price, predicted_price, arah):
    """Render a professional signal verdict box."""
    if sinyal == "BUY":
        css = "verdict-buy"
        emoji = "🟢"
        action = "BELI"
    elif sinyal == "SELL":
        css = "verdict-sell"
        emoji = "🔴"
        action = "JUAL"
    else:
        css = "verdict-hold"
        emoji = "🟡"
        action = "TUNGGU"

    change_pct = ((predicted_price - current_price) / current_price) * 100 if current_price > 0 else 0
    direction_arrow = "▲" if change_pct > 0 else "▼" if change_pct < 0 else "—"

    st.markdown(f"""
    <div class="verdict-box {css}">
        <p class="verdict-title">{emoji} {action}</p>
        <p class="verdict-subtitle">Tingkat Keyakinan: {confidence:.1%} | Arah: {arah}</p>
        <p style="font-size: 1.1em; margin-top: 12px;">
            <span style="color:#9ca3af;">Rp {current_price:,.2f}</span>
            <span style="color:#9ca3af;"> → </span>
            <span style="color:{'#22c55e' if change_pct > 0 else '#ef4444' if change_pct < 0 else '#f59e0b'}; font-weight:700;">
                Rp {predicted_price:,.2f} ({direction_arrow} {change_pct:+.2f}%)
            </span>
        </p>
    </div>
    """, unsafe_allow_html=True)


def render_step_progress(steps):
    """Render a step-by-step progress display."""
    icons = {"done": "✅", "active": "⏳", "pending": "⬜", "error": "❌"}
    html = '<div class="step-container">'
    for status, text, detail in steps:
        html += f'<div class="step-item {status}"><span class="step-icon">{icons.get(status, "⬜")}</span><span class="step-text">{text}</span><span class="step-detail">{detail}</span></div>'
    html += '</div>'
    st.markdown(html, unsafe_allow_html=True)


def render_signal_badge(signal_text):
    """Render a colored signal badge."""
    s = signal_text.upper()
    if "BUY" in s or "BULLISH" in s:
        css = "signal-buy"
    elif "SELL" in s or "BEARISH" in s:
        css = "signal-sell"
    elif "HOLD" in s or "NEUTRAL" in s:
        css = "signal-hold"
    else:
        css = "signal-neutral"
    st.markdown(f'<span class="signal-badge {css}">{signal_text}</span>', unsafe_allow_html=True)


def create_candlestick_chart(df, title="Chart Harga", show_bb=False):
    fig = make_subplots(
        rows=2, cols=1, shared_xaxes=True, vertical_spacing=0.03,
        row_heights=[0.7, 0.3], subplot_titles=("Harga (Candlestick)", "Volume Transaksi"),
    )
    fig.add_trace(go.Candlestick(
        x=df.index, open=df["Open"], high=df["High"], low=df["Low"], close=df["Close"], name="OHLC",
    ), row=1, col=1)

    ma5 = df["Close"].rolling(window=5).mean()
    ma10 = df["Close"].rolling(window=10).mean()
    ma20 = df["Close"].rolling(window=20).mean()
    fig.add_trace(go.Scatter(x=df.index, y=ma5, name="MA5", line=dict(color="orange", width=1)), row=1, col=1)
    fig.add_trace(go.Scatter(x=df.index, y=ma10, name="MA10", line=dict(color="blue", width=1)), row=1, col=1)
    fig.add_trace(go.Scatter(x=df.index, y=ma20, name="MA20", line=dict(color="red", width=1)), row=1, col=1)

    if show_bb:
        bb_mean = df["Close"].rolling(20).mean()
        bb_std = df["Close"].rolling(20).std()
        fig.add_trace(go.Scatter(x=df.index, y=bb_mean + 2*bb_std, name="BB Atas", line=dict(color="gray", dash="dash", width=1)), row=1, col=1)
        fig.add_trace(go.Scatter(x=df.index, y=bb_mean - 2*bb_std, name="BB Bawah", line=dict(color="gray", dash="dash", width=1)), row=1, col=1)

    if "Volume" in df.columns:
        fig.add_trace(go.Bar(x=df.index, y=df["Volume"], name="Volume", marker_color="rgba(100,100,255,0.5)"), row=2, col=1)

    fig.update_layout(title=title, xaxis_rangeslider_visible=False, height=500, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)", showlegend=True)
    return fig


def create_gauge_chart(value, title, min_val=0, max_val=100):
    fig = go.Figure(go.Indicator(
        mode="gauge+number", value=value, title={"text": title},
        gauge={
            "axis": {"range": [min_val, max_val]}, "bar": {"color": "white"},
            "steps": [
                {"range": [0, 25], "color": "#ff4444"},
                {"range": [25, 45], "color": "#ff8844"},
                {"range": [45, 55], "color": "#ffcc44"},
                {"range": [55, 75], "color": "#88cc44"},
                {"range": [75, 100], "color": "#44aa44"},
            ],
            "threshold": {"line": {"color": "white", "width": 4}, "thickness": 0.75, "value": value},
        },
    ))
    fig.update_layout(height=300, template="plotly_dark", paper_bgcolor="rgba(0,0,0,0)", plot_bgcolor="rgba(0,0,0,0)")
    return fig


def section_header(icon, title):
    """Render a section header."""
    st.markdown(f'<div class="section-header"><span class="section-header-icon">{icon}</span><span class="section-header-title">{title}</span></div>', unsafe_allow_html=True)
