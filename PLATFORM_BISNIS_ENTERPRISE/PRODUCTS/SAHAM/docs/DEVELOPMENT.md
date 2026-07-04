# 💻 Panduan Development Multi-Komputer

## Setup Komputer Baru

### 1. Install System Dependencies

**Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install python3 python3-venv python3-pip git
```

**macOS:**
```bash
brew install python3 git
```

**Windows:**
- Install Python 3.12+ dari https://python.org
- Install Git dari https://git-scm.com

### 2. Clone Repository

```bash
git clone -b laptop https://github.com/82080038/saham.git
cd saham
```

### 3. Setup Virtual Environment

```bash
# Linux/Mac
python3 -m venv .venv
source .venv/bin/activate

# Windows
python -m venv .venv
.venv\Scripts\activate
```

### 4. Install Dependencies

```bash
# Dependencies produksi
pip install -r requirements.txt

# Dependencies development (opsional, untuk testing & linting)
pip install -r requirements-dev.txt
```

### 5. Setup Environment Variables

```bash
cp .env.example .env
```

Edit `.env` dengan credentials Anda:
- `FRED_API_KEY`: Daftar gratis di https://fred.stlouisfed.org
- `TELEGRAM_BOT_TOKEN`: Buat bot via @BotFather di Telegram
- `TELEGRAM_CHAT_ID`: Dapatkan dari @userinfobot
- Email settings: Gunakan App Password untuk Gmail

**Catatan:** Aplikasi berjalan tanpa `.env` — hanya fitur FRED data dan notifikasi yang tidak aktif.

### 6. Inisialisasi Database

```bash
python -c "from src.database import init_db; init_db()"
```

Database SQLite (`src/data/saham_prediksi.db`) akan dibuat otomatis.

### 7. Verifikasi Instalasi

```bash
# Test import semua modul inti
python -c "
from src.config import TICKERS
from src.database import init_db
from src.data_fetcher import fetch_all_market_data
from src.preprocessor import prepare_features
from src.models import HybridEnsemble
from src.predictor import run_prediction
from src.indicators import add_all_indicators
from src.risk_manager import calc_risk_metrics
from src.sentiment import calc_fear_greed_index
from src.intermarket import calc_correlation_matrix
from src.portfolio import optimize_portfolio
print('Core modules OK!')
"

# Test import modul profesional (analisis lanjutan)
python -c "
from src.mtf import run_mtf_analysis
from src.wyckoff import detect_wyckoff_phase
from src.elliott_wave import detect_elliott_wave
from src.behavioral import analyze_behavioral
from src.sector_rotation import detect_economic_phase
from src.factor_model import run_capm_regression
from src.event_driven import run_event_driven_analysis
from src.ai_agent import run_daily_briefing
from src.scoring import calc_composite_ai_score
from src.regime import detect_market_regime
from src.pro_risk import RiskGovernance
from src.slippage import calc_execution_cost
from src.quant_finance import run_realistic_backtest, generate_tear_sheet
from src.portfolio import compare_portfolio_methods, black_litterman_optimize, risk_parity_optimize, hrp_optimize, cvar_optimize
from src.rate_limiter import get_yf_limiter, get_fred_limiter
from src.realtime_feed import get_realtime_price
from src.fundamental import fetch_fundamental_data
from src.dcf_valuation import run_dcf
from src.idx_rules import check_auto_rejection
from src.compliance import get_risk_disclosure
from src.data_pipeline import run_data_quality_check
from src.mlflow_tracking import log_experiment
from src.retrain_scheduler import RetrainScheduler
from src.hyperopt import run_hyperopt
from src.validation import walk_forward_cv
from src.feature_selection import select_features_shap
from src.patterns import detect_candlestick_patterns
from src.technical_advanced import detect_trendlines
from src.api import app as fastapi_app
from src.fraud_detection import FraudDetector
from src.investor_tools import DividendReinvestmentSim, AssetAllocationModel, CorrelationAnalyzer
from src.intraday_model import IntradayModel
from src.execution_algo import VWAPExecution, TWAPExecution, estimate_spread
from src.broker_sim import BrokerSimulator
from src.simulation_engine import MarketSimulation
print('All 85 modules OK!')
"

# Test fetch data
python -c "
from src.data_fetcher import fetch_all_market_data
data = fetch_all_market_data(period='1mo')
print(f'Fetch OK: {list(data.keys())}')
"

# Run test suite
python -m pytest tests/ -q --tb=short
# Expected: 366+ passed, 0 warnings
```

### 8. Jalankan Aplikasi

```bash
# Dashboard Streamlit
streamlit run src/app.py --server.port 8501

# CLI — analisis harian
python -m src.run_analysis --all

# FastAPI REST API
python -m uvicorn src.api:app --host 0.0.0.0 --port 8000 --reload
# API docs: http://localhost:8000/docs

# Docker (opsional)
docker-compose up --build
# Services: streamlit (8501), api (8000), mlflow (5000)
```

### 9. Verifikasi Simulasi Walk-Forward

Setelah aplikasi berjalan, jalankan simulasi untuk memastikan strategi adaptive + short selling berfungsi:

```bash
# CLI — simulasi 3 bulan dengan Rp 10 juta modal
python -c "
from src.simulation_engine import MarketSimulation
sim = MarketSimulation(
    initial_capital=10_000_000,
    train_months=6,
    sim_months=3,
    day_duration_seconds=0.1,
    target_ticker='^JKSE',
    broker_name='bca_sekuritas',
    train_end_date='2026-01-31',
)
results = sim.run()
print(f'Return: {results.total_return_pct:.2f}%')
print(f'Buy&Hold: {results.buy_hold_return_pct:.2f}%')
print(f'Trades: {results.n_trades}, Shorts: {results.n_shorts}, Covers: {results.n_covers}')
print(f'Win Rate: {results.win_rate:.1f}%, Max DD: {results.max_drawdown_pct:.2f}%')
"
```

Atau buka browser, pilih sidebar **🎮 Simulasi Pasar**, lalu klik **🚀 Jalankan Simulasi**.

## Struktur Branch

| Branch | Deskripsi |
|--------|-----------|
| `main` | Production-ready code |
| `laptop` | Development branch aktif (semua commit terbaru di sini) |

## Testing

```bash
# Run semua 366+ tests
python -m pytest tests/ -q --tb=short

# Run test file spesifik
python -m pytest tests/test_mtf_portfolio.py -v

# Run dengan coverage
python -m pytest tests/ --cov=src --cov-report=term-missing

# Run tanpa warning (sudah dikonfigurasi di pytest.ini)
python -m pytest tests/ -q
```

## Workflow Antar Komputer

### Sebelum Mulai Coding
```bash
git pull origin laptop
```

### Setelah Selesai Coding
```bash
git add -A
git commit -m "deskripsi perubahan"
git push origin laptop
```

### Konflik Merge
```bash
git pull origin laptop
# Resolve konflik di editor
git add -A
git commit -m "resolve merge conflict"
git push origin laptop
```

## Struktur Modul (85 modul di src/)

### Layer Data
| Modul | Fungsi |
|-------|--------|
| `config.py` | Konfigurasi: tickers, API keys, parameter model |
| `database.py` | SQLite: prediksi, harga aktual, log aktivitas |
| `data_fetcher.py` | Fetch data Yahoo Finance & FRED API |
| `rate_limiter.py` | Sliding window rate limiter (yfinance, FRED, web) |
| `realtime_feed.py` | Real-time price polling + caching + alerts |
| `data_pipeline.py` | Data quality monitoring, lineage tracking |
| `fundamental.py` | Fundamental data via yfinance (P/E, P/B, ROE) |
| `dcf_valuation.py` | DCF valuation model |

### Layer Feature
| Modul | Fungsi |
|-------|--------|
| `preprocessor.py` | Feature engineering: 201 fitur |
| `feature_selection.py` | SHAP, Boruta, correlation filter |
| `indicators.py` | 11 indikator teknikal + composite signal |
| `technical_advanced.py` | Trendline, support/resistance, pivot points |
| `patterns.py` | Candlestick + chart pattern detection |

### Layer Model
| Modul | Fungsi |
|-------|--------|
| `models.py` | Hybrid Ensemble: RF + XGBoost + LightGBM (+ LSTM) |
| `hyperopt.py` | Optuna TPE Bayesian optimization |
| `validation.py` | Walk-forward CV, purged k-fold, IC metrics |
| `retrain_scheduler.py` | Automated retraining + drift detection |
| `mlflow_tracking.py` | MLflow experiment/model logging |

### Layer Simulation *(BARU)*
| Modul | Fungsi |
|-------|--------|
| `broker_sim.py` | Broker simulator: order execution, komisi, slippage, latency, partial fill, **short selling** |
| `simulation_engine.py` | Walk-forward market simulation: training, prediksi, regime detection, trend filter, ATR-based SL/TP, trailing stop, adaptive strategy, **long + short** |
| `src/pages/broker_sim.py` | UI simulasi broker |
| `src/pages/simulation.py` | UI walk-forward simulation di browser |

### Layer Analysis
| Modul | Fungsi |
|-------|--------|
| `mtf.py` | Multi-Timeframe Analysis (1W/1D/4H/1H confluence) |
| `scoring.py` | Composite AI Score (1-10), multi-dimension rating |
| `wyckoff.py` | Wyckoff phase detection |
| `elliott_wave.py` | Elliott Wave pattern + Fibonacci |
| `behavioral.py` | Behavioral finance: FOMO, panic, herding |
| `sector_rotation.py` | Economic phase + sector rotation |
| `factor_model.py` | CAPM regression, factor score |
| `event_driven.py` | Earnings, corporate actions, analyst, news |
| `ai_agent.py` | Multi-agent: Analyst, Risk, News, Portfolio |
| `sentiment.py` | Fear & Greed Index + Market Regime |
| `sentiment_pipeline.py` | RSS scraping + FinBERT + alerts |
| `regime.py` | Market Regime Detection (Bull/Bear/Sideways) |
| `intermarket.py` | Rolling correlation, lead-lag, spread |

### Layer Risk
| Modul | Fungsi |
|-------|--------|
| `risk_manager.py` | VaR, CVaR, Sharpe, Sortino, Kelly |
| `pro_risk.py` | Professional risk governance: kill switch |
| `portfolio.py` | 5 methods: Markowitz, BL, Risk Parity, HRP, CVaR |
| `portfolio_risk.py` | Correlation-aware allocation, stress test |
| `slippage.py` | Market impact, bid-ask, execution optimization |
| `quant_finance.py` | Entry/stop, backtest, tear sheet, DSR, drift |
| `idx_rules.py` | IDX/BEI rules: auto-rejection, circuit breaker |
| `compliance.py` | OJK disclaimers, audit trail |
| `fraud_detection.py` | 6-layer anti-fraud: data quality, cross-source, index consistency, news divergence, anti-manipulation metrics, market manipulation |
| `anti_manipulation.py` | Z-Score volume shock, Amihud illiquidity, Beneish M-Score, wash trading, spoofing, fake news hype (Blueprint Bab 3+4) |
| `execution_algo.py` | VWAP/TWAP execution algorithms, Corwin-Schultz spread estimator |
| `investor_tools.py` | Dividend reinvestment (DRIP), asset allocation model, correlation matrix |

### Layer Presentation
| Modul | Fungsi |
|-------|--------|
| `predictor.py` | Pipeline prediksi: ML + rules + MTF + risk + multi-day forecast |
| `intraday_model.py` | Intraday ML model (5m/15m) for day trader mode |
| `app.py` | Streamlit dashboard (24 halaman) |
| `api.py` | FastAPI REST API (10 endpoints) |
| `run_analysis.py` | CLI script untuk cron job harian |
| `notifier.py` | Notifikasi Telegram & Email |

## Troubleshooting

### `ModuleNotFoundError: No module named 'xxx'`
```bash
# Pastikan virtual environment aktif
source .venv/bin/activate
# Reinstall dependencies
pip install -r requirements.txt

# Pastikan import menggunakan prefix 'src.'
# BENAR: from src.config import TICKERS
# SALAH: from config import TICKERS
```

### `yfinance` gagal fetch data
- Cek koneksi internet
- Yahoo Finance mungkin rate-limit, tunggu beberapa menit
- Rate limiter sudah ada (60 calls/60s), tapi bisa terlalu ketat untuk 35+ tickers
- Coba kurangi periode data: `period='1mo'` instead of `'2y'`
- yfinance logger sudah di-suppress (404 untuk index tickers normal)

### Streamlit tidak muncul
```bash
streamlit run src/app.py --server.port 8501 --server.address 127.0.0.1
# Buka browser: http://127.0.0.1:8501
```

### Database locked
```bash
# Tutup semua koneksi yang menggunakan database
rm src/data/saham_prediksi.db
python -c "from src.database import init_db; init_db()"
```

### TensorFlow/LSTM error
LSTM bersifat opsional. Jika TensorFlow tidak terinstall, model akan menggunakan RF + XGBoost + LightGBM saja.
```bash
# Install TensorFlow (opsional)
pip install tensorflow
```

### LightGBM warning: "X does not have valid feature names"
Warning ini sudah di-suppress di `pytest.ini` dan `src/models.py`. Tidak mempengaruhi fungsi.

### transformers/FinBERT tidak terinstall
FinBERT bersifat opsional. Aplikasi akan menggunakan lexicon fallback untuk sentiment analysis.
```bash
# Install transformers (opsional, untuk FinBERT)
pip install transformers torch
```

### PyTorch CUDA/GPU
PyTorch dengan CUDA support sudah terinstall. Untuk GPU acceleration:
- **PyTorch**: PatchTST/TFT training di GPU (auto-detected)
- **LightGBM**: `device='gpu'` (auto-detected)
- **XGBoost**: Tidak support GPU (GTX 1050 Ti SM 6.1 terlalu lama)
```bash
