import os
from dotenv import load_dotenv

load_dotenv()

# ===========================================================================
# KONFIGURASI DATA - Ticker symbols untuk Yahoo Finance
# ===========================================================================
TICKERS = {
    # Pasar Target
    "IHSG": "^JKSE",           # IDX Composite (Indonesia)
    # Indeks Global
    "S&P500": "^GSPC",         # S&P 500 (US)
    "NASDAQ": "^IXIC",         # Nasdaq Composite (US)
    "STI": "^STI",             # Straits Times Index (Singapore)
    "DOW": "^DJI",             # Dow Jones Industrial Average
    "NIKKEI": "^N225",         # Nikkei 225 (Japan)
    "HANG_SENG": "^HSI",       # Hang Seng Index (Hong Kong)
    # Pasar China (A-Shares) — korelasi penting untuk IHSG
    "SSE_COMPOSITE": "000001.SS",  # Shanghai Composite Index (A-shares)
    "CSI300": "000300.SS",         # CSI 300 Index (top 300 A-shares)
    # Komoditas
    "GOLD": "GC=F",            # Gold Futures
    "OIL": "CL=F",             # Crude Oil WTI Futures
    # Volatilitas
    "VIX": "^VIX",             # CBOE Volatility Index
    # Forex
    "USD_IDR": "IDR=X",        # USD to IDR
    "CNY_IDR": "CNYIDR=X",     # CNY to IDR (China-Indonesia forex)
    # Bond Yield (Blueprint Bab 7.1)
    "US_10Y": "^TNX",          # US 10-Year Treasury Yield (cost of capital global)
}

# Ticker utama yang akan diprediksi
TARGET_TICKER = "^JKSE"

# Saham blue chip Indonesia (contoh untuk analisis individual)
BLUE_CHIPS_ID = {
    "BBCA.JK": "Bank Central Asia",
    "BBRI.JK": "Bank Rakyat Indonesia",
    "TLKM.JK": "Telkom Indonesia",
    "ASII.JK": "Astra International",
    "UNVR.JK": "Unilever Indonesia",
    "GOTO.JK": "GoTo Gojek Tokopedia",
    "ICBP.JK": "Indofood CBP Sukses Makmur",
    "ADRO.JK": "Adaro Energy",
}

# ===========================================================================
# 100 SAHAM IDX PER SEKTOR (diekstrak dari 100_saham_per_sektor.sql)
# Sumber: branch main/kantor repository GitHub
# ===========================================================================
SAHAM_IDX_SECTORS = {
    "Financial Services": [
        "BBCA.JK", "BBRI.JK", "BMRI.JK", "BNGA.JK", "BBNI.JK",
        "BJTM.JK", "BJBR.JK", "BTPN.JK", "MEGA.JK", "PNBN.JK",
        "BRIS.JK", "ARTO.JK", "HDFA.JK",
    ],
    "Technology": [
        "GOTO.JK", "EMTK.JK", "BUKA.JK", "DMMX.JK", "TECH.JK",
    ],
    "Telecommunications": [
        "TLKM.JK", "EXCL.JK", "ISAT.JK", "FREN.JK", "TOWR.JK",
    ],
    "Consumer Staples": [
        "UNVR.JK", "ICBP.JK", "INDF.JK", "INCI.JK", "AISA.JK",
        "MLBI.JK", "ULTJ.JK", "CLEO.JK", "ADES.JK", "SIDO.JK",
        "GGRM.JK", "HMSP.JK",
    ],
    "Consumer Discretionary": [
        "ASII.JK", "AUTO.JK", "AKRA.JK", "BIRD.JK", "CARS.JK",
        "ERAA.JK", "HEXA.JK",
    ],
    "Healthcare": [
        "KLBF.JK", "SILO.JK", "MERK.JK", "TSPC.JK", "PRDA.JK",
        "HEAL.JK",
    ],
    "Energy": [
        "PGAS.JK", "PERT.JK", "ADRO.JK", "PTBA.JK", "ITMG.JK",
        "BUMI.JK", "MEDC.JK", "TOBA.JK", "BSSR.JK", "GEMS.JK",
    ],
    "Materials": [
        "ANTM.JK", "INCO.JK", "SMGR.JK", "INTP.JK", "SMCB.JK",
        "TPIA.JK", "TKIM.JK", "INKP.JK", "TINS.JK",
    ],
    "Real Estate": [
        "CTRA.JK", "DART.JK", "RSGK.JK", "ASRI.JK", "BSDE.JK",
        "LPCK.JK", "SMRA.JK", "KIJA.JK", "PWON.JK",
    ],
    "Infrastructure & Utilities": [
        "WIKA.JK", "ADHI.JK", "PTPP.JK", "JSMR.JK", "PLN.JK",
        "POWR.JK", "PAMG.JK", "TOPS.JK", "UNTR.JK", "HITS.JK",
    ],
}

SAHAM_IDX_100 = {
    # Financial Services
    "BBCA.JK": "Bank Central Asia Tbk",
    "BBRI.JK": "Bank Rakyat Indonesia Tbk",
    "BMRI.JK": "Bank Mandiri Tbk",
    "BNGA.JK": "Bank CIMB Niaga Tbk",
    "BBNI.JK": "Bank Negara Indonesia Tbk",
    "BJTM.JK": "Bank Pembangunan Daerah Jawa Timur Tbk",
    "BJBR.JK": "Bank Pembangunan Daerah Jawa Barat Tbk",
    "BTPN.JK": "Bank BTPN Tbk",
    "MEGA.JK": "Bank Mega Tbk",
    "PNBN.JK": "Bank Panin Tbk",
    "BRIS.JK": "Bank Syariah Indonesia Tbk",
    "ARTO.JK": "Bank Jago Tbk",
    "HDFA.JK": "Radiance Medan Multifinance Tbk",
    # Technology
    "GOTO.JK": "GoTo Gojek Tokopedia Tbk",
    "EMTK.JK": "Elang Mahkota Teknologi Tbk",
    "BUKA.JK": "Bukalapak.com Tbk",
    "DMMX.JK": "Digital Mediatama Maxima Tbk",
    "TECH.JK": "Indo Kordsa Tbk",
    # Telecommunications
    "TLKM.JK": "Telkom Indonesia Tbk",
    "EXCL.JK": "XL Axiata Tbk",
    "ISAT.JK": "Indosat Ooredoo Hutchison Tbk",
    "FREN.JK": "Smartfren Telecom Tbk",
    "TOWR.JK": "Sarana Menara Nusantara Tbk",
    # Consumer Staples
    "UNVR.JK": "Unilever Indonesia Tbk",
    "ICBP.JK": "Indofood CBP Sukses Makmur Tbk",
    "INDF.JK": "Indofood Sukses Makmur Tbk",
    "INCI.JK": "Inti Agri Resources Tbk",
    "AISA.JK": "FKS Food Sejahtera Tbk",
    "MLBI.JK": "Multi Bintang Indonesia Tbk",
    "ULTJ.JK": "Ultra Jaya Milk Industry Tbk",
    "CLEO.JK": "Sariguna Primatirta Tbk",
    "ADES.JK": "Akasha Wira International Tbk",
    "SIDO.JK": "Sido Muncul Tbk",
    "GGRM.JK": "Gudang Garam Tbk",
    "HMSP.JK": "H.M. Sampoerna Tbk",
    # Consumer Discretionary
    "ASII.JK": "Astra International Tbk",
    "AUTO.JK": "Astra Otoparts Tbk",
    "AKRA.JK": "AKR Corporindo Tbk",
    "BIRD.JK": "Blue Bird Tbk",
    "CARS.JK": "Industri dan Perdagangan Bintraco Dharma Tbk",
    "ERAA.JK": "Erajaya Swasembada Tbk",
    "HEXA.JK": "Hexindo Adiperkasa Tbk",
    # Healthcare
    "KLBF.JK": "Kalbe Farma Tbk",
    "SILO.JK": "Siloam International Hospitals Tbk",
    "MERK.JK": "Merck Tbk",
    "TSPC.JK": "Tempo Scan Pacific Tbk",
    "PRDA.JK": "Prodia Widyahusada Tbk",
    "HEAL.JK": "Medikaloka Hermina Tbk",
    # Energy
    "PGAS.JK": "Perusahaan Gas Negara Tbk",
    "PERT.JK": "Pertamina (Persero) Tbk",
    "ADRO.JK": "Adaro Energy Tbk",
    "PTBA.JK": "Bukit Asam Tbk",
    "ITMG.JK": "Indo Tambangraya Megah Tbk",
    "BUMI.JK": "Bumi Resources Tbk",
    "MEDC.JK": "Medco Energi Internasional Tbk",
    "TOBA.JK": "Toba Pulp Lestari Tbk",
    "BSSR.JK": "Baramulti Suksessarana Tbk",
    "GEMS.JK": "Golden Energy Mines Tbk",
    # Materials
    "ANTM.JK": "Aneka Tambang Tbk",
    "INCO.JK": "Vale Indonesia Tbk",
    "SMGR.JK": "Semen Indonesia Tbk",
    "INTP.JK": "Indocement Tunggal Prakarsa Tbk",
    "SMCB.JK": "Holcim Indonesia Tbk",
    "TPIA.JK": "Chandra Asri Pacific Tbk",
    "TKIM.JK": "Pabrik Kertas Tjiwi Kimia Tbk",
    "INKP.JK": "Indah Kiat Pulp & Paper Tbk",
    "TINS.JK": "Timah Tbk",
    # Real Estate
    "CTRA.JK": "Ciputra Development Tbk",
    "DART.JK": "Duta Anggada Realty Tbk",
    "RSGK.JK": "Lippo Karawaci Tbk",
    "ASRI.JK": "Alam Sutera Realty Tbk",
    "BSDE.JK": "Bumi Serpong Damai Tbk",
    "LPCK.JK": "Lippo Cikarang Tbk",
    "SMRA.JK": "Summarecon Agung Tbk",
    "KIJA.JK": "Kawasan Industri Jababeka Tbk",
    "PWON.JK": "Pakuwon Jati Tbk",
    # Infrastructure & Utilities
    "WIKA.JK": "Wijaya Karya Tbk",
    "ADHI.JK": "Adhi Karya Tbk",
    "PTPP.JK": "PP Tbk",
    "JSMR.JK": "Jasa Marga Tbk",
    "PLN.JK": "Perusahaan Listrik Negara Tbk",
    "POWR.JK": "Cikarang Listrindo Tbk",
    "PAMG.JK": "Pam Mineral Tbk",
    "TOPS.JK": "Totalindo Eka Persada Tbk",
    "UNTR.JK": "United Tractors Tbk",
    "HITS.JK": "Humpuss Intermoda Transportasi Tbk",
}

# ===========================================================================
# MULTI-COUNTRY BLUE CHIPS
# ===========================================================================
BLUE_CHIPS_US = {
    "AAPL": "Apple",
    "MSFT": "Microsoft",
    "GOOGL": "Alphabet",
    "AMZN": "Amazon",
    "NVDA": "NVIDIA",
    "META": "Meta Platforms",
    "TSLA": "Tesla",
    "JPM": "JPMorgan Chase",
}

BLUE_CHIPS_JP = {
    "7203.T": "Toyota Motor",
    "9984.T": "SoftBank Group",
    "6861.T": "Keyence",
    "8306.T": "Mitsubishi UFJ",
    "7974.T": "Nintendo",
    "6758.T": "Sony Group",
    "8316.T": "Sumitomo Mitsui",
    "9433.T": "KDDI",
}

BLUE_CHIPS_HK = {
    "0700.HK": "Tencent Holdings",
    "9988.HK": "Alibaba Group",
    "1299.HK": "AIA Group",
    "0939.HK": "China Construction Bank",
    "0005.HK": "HSBC Holdings",
    "3690.HK": "Meituan",
    "1810.HK": "Xiaomi",
    "2318.HK": "Ping An Insurance",
}

BLUE_CHIPS_SG = {
    "D05.SI": "DBS Group",
    "O39.SI": "OCBC Bank",
    "U11.SI": "UOB",
    "Z74.SI": "Singtel",
    "C6L.SI": "Singapore Airlines",
    "F34.SI": "Wilmar International",
    "C38U.SI": "CapitaLand Integrated",
    "S58.SI": "Sembcorp Industries",
}

# All blue chips combined
ALL_BLUE_CHIPS = {}
ALL_BLUE_CHIPS.update(BLUE_CHIPS_ID)
ALL_BLUE_CHIPS.update(BLUE_CHIPS_US)
ALL_BLUE_CHIPS.update(BLUE_CHIPS_JP)
ALL_BLUE_CHIPS.update(BLUE_CHIPS_HK)
ALL_BLUE_CHIPS.update(BLUE_CHIPS_SG)

# ===========================================================================
# FRED API - Data Makro Ekonomi AS
# ===========================================================================
FRED_API_KEY = os.getenv("FRED_API_KEY", "")
FRED_SERIES = {
    "FEDFUNDS": "FEDFUNDS",     # Federal Funds Rate
    "CPI": "CPIAUCSL",          # Consumer Price Index (Inflasi)
    "TREASURY_10Y": "DGS10",    # 10-Year Treasury Rate
    "UNEMPLOYMENT": "UNRATE",   # Unemployment Rate
}

# ===========================================================================
# PARAMETER MODEL MACHINE LEARNING
# ===========================================================================
MODEL_CONFIG = {
    # Data fetch period (5y = ~1200 rows, enough for 5-fold walk-forward CV)
    "data_period": "5y",
    # Lookback window untuk feature engineering
    "lookback_days": 60,
    # Moving averages
    "ma_short": 5,
    "ma_medium": 10,
    "ma_long": 20,
    # RSI period
    "rsi_period": 14,
    # Lag features (hari)
    "lag_days": [1, 2, 3, 5],
    # Train/test split ratio
    "test_size": 0.2,
    # Random state untuk reproducibility
    "random_state": 42,
    # Random Forest
    "rf_n_estimators": 200,
    "rf_max_depth": 10,
    # XGBoost
    "xgb_n_estimators": 200,
    "xgb_max_depth": 6,
    "xgb_learning_rate": 0.1,
    # LightGBM
    "lgbm_n_estimators": 200,
    "lgbm_max_depth": 6,
    "lgbm_learning_rate": 0.1,
    "use_lightgbm": True,
    # LSTM (opsional, butuh TensorFlow)
    "lstm_units": 50,
    "lstm_epochs": 50,
    "lstm_batch_size": 32,
    "use_lstm": False,  # Set True jika TensorFlow terinstall
    "lstm_attention": True,  # Use attention-enhanced LSTM
}

# ===========================================================================
# ATURAN BISNIS (Business Rules)
# ===========================================================================
BUSINESS_RULES = {
    # Trend Follower: MA5 > MA10 > MA20 = Bullish
    "trend_follower_enabled": True,
    # Anti-FOMO: Jangan beli jika RSI > 70 (overbought)
    "anti_fomo_rsi_threshold": 70,
    # Stop Loss: Jangan beli jika VIX > 30 (panik pasar)
    "vix_panic_threshold": 30,
    # Minimum confidence untuk sinyal
    "min_confidence": 0.55,
    # Minimum jumlah vote untuk ensemble
    "min_votes": 2,
}

# ===========================================================================
# DIRECTORY STRUCTURE
# ===========================================================================
BASE_DIR = os.path.dirname(__file__)
MODELS_DIR = os.path.join(BASE_DIR, "models")
DATA_DIR = os.path.join(BASE_DIR, "data")

for d in [MODELS_DIR, DATA_DIR]:
    os.makedirs(d, exist_ok=True)

# ===========================================================================
# DATABASE
# ===========================================================================
DB_PATH = os.path.join(DATA_DIR, "saham_prediksi.db")
DATABASE_URL = os.getenv("DATABASE_URL", "")  # PostgreSQL: postgresql://user:pass@host:5432/dbname

# ===========================================================================
# REDIS CACHE
# ===========================================================================
REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")
REDIS_ENABLED = bool(os.getenv("REDIS_URL", ""))

# ===========================================================================
# CELERY
# ===========================================================================
CELERY_BROKER_URL = os.getenv("CELERY_BROKER_URL", REDIS_URL)
CELERY_RESULT_BACKEND = os.getenv("CELERY_RESULT_BACKEND", REDIS_URL)

# ===========================================================================
# NOTIFIKASI
# ===========================================================================
TELEGRAM_BOT_TOKEN = os.getenv("TELEGRAM_BOT_TOKEN", "")
TELEGRAM_CHAT_ID = os.getenv("TELEGRAM_CHAT_ID", "")

SMTP_SERVER = os.getenv("SMTP_SERVER", "smtp.gmail.com")
SMTP_PORT = int(os.getenv("SMTP_PORT", "587"))
SMTP_USERNAME = os.getenv("SMTP_USERNAME", "")
SMTP_PASSWORD = os.getenv("SMTP_PASSWORD", "")
EMAIL_TO = os.getenv("EMAIL_TO", "")
