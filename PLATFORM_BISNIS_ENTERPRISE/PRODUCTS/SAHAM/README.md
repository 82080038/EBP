# 📈 Aplikasi Proyeksi & Simulasi Trading Saham Global — Professional Edition

Aplikasi analisis, prediksi, dan **simulasi trading** pasar saham komprehensif dengan fokus pada IHSG (Bursa Efek Indonesia) dan pasar global terkait. Aplikasi ini menggabungkan ML ensemble, analisis teknikal profesional, event-driven analysis, AI sentiment, multi-timeframe confluence, portfolio optimization, dan **broker simulator realistis dengan dukungan short selling**.

## 🎯 Apa yang Aplikasi Ini Lakukan

Aplikasi ini adalah **sistem trading intelligence** yang bekerja dalam siklus tertutup:

1. **Mengumpulkan data** pasar global (IHSG, S&P500, Nasdaq, STI, Nikkei, Hang Seng, emas, minyak, VIX, USD/IDR) dan data makro dari FRED API.
2. **Membuat fitur** 200+ (teknikal, inter-market, makro, lag) untuk melatih model ensemble.
3. **Memprediksi arah harga** menggunakan ensemble RandomForest + XGBoost + LightGBM.
4. **Mendeteksi regime pasar** (bull / bear / sideways / crisis) dan menyesuaikan strategi secara otomatis.
5. **Mensimulasikan eksekusi** melalui broker simulator dengan komisi, slippage, latency, partial fill, order rejection, dan **short selling**.
6. **Menerapkan risk management** dinamis: ATR-based stop-loss, take-profit, trailing stop, dan position sizing berdasarkan regime.
7. **Mengembangkan diri** dengan **auto-adjust**: threshold confidence, position sizing, dan SL/TP beradaptasi berdasarkan regime pasar dan win rate 20 trade terakhir.
8. **Merekam semua aktivitas** ke SQLite/JSON untuk audit, verifikasi, dan perbaikan berkelanjutan.

## 🚀 Quick Start

```bash
# 1. Clone repository
git clone -b laptop https://github.com/82080038/saham.git
cd saham

# 2. Buat virtual environment
python3 -m venv .venv
source .venv/bin/activate    # Linux/Mac
# .venv\Scripts\activate     # Windows

# 3. Install dependencies
pip install -r requirements.txt

# 4. Setup environment variables
cp .env.example .env
# Edit .env dengan API keys Anda (opsional untuk fitur tambahan)

# 5. Jalankan dashboard
streamlit run src/app.py --server.port 8501

# 6. Atau jalankan analisis harian via CLI
python -m src.run_analysis --all
```

Buka browser: `http://localhost:8501`

## �️ Cara Melanjutkan Development

Jika Anda baru mengambil alih proyek ini, mulai dari file-file ini:

| File | Fungsi |
|------|--------|
| `docs/ACUAN_PROYEK.md` | Visi, tujuan, cara kerja, dan hasil verifikasi aplikasi |
| `src/config.py` | Konfigurasi ticker, broker, parameter model, path data |
| `src/simulation_engine.py` | **Core simulasi walk-forward**: training, prediksi, regime, trend, SL/TP, short selling |
| `src/broker_sim.py` | Broker simulator: order execution, komisi, slippage, short selling |
| `src/predictor.py` | Pipeline prediksi harian: ML + rules + MTF + risk + SHAP |
| `src/regime.py` | Market regime detection (bull/bear/sideways/crisis) |
| `src/preprocessor.py` | Feature engineering (200+ fitur) |
| `src/models.py` | Hybrid ensemble: RF + XGBoost + LightGBM |
| `src/app.py` & `src/pages/` | Streamlit dashboard (24 halaman) |
| `tests/` | 13 test files, 366+ tests |

### Alur Tes Standar

```bash
# Jalankan semua test
python -m pytest tests/ -q --tb=short

# Jalankan simulasi dari CLI
python -c "from src.simulation_engine import MarketSimulation; \
sim = MarketSimulation(10_000_000, 6, 3, target_ticker='^JKSE', broker_name='bca_sekuritas', train_end_date='2026-01-31'); \
print(sim.run())"
```

### Panduan Menambah Fitur

1. **Analisis baru** → tambahkan di `src/` dan expose lewat `src/pages/advanced_analytics.py` atau halaman baru.
2. **Model baru** → daftarkan di `src/models.py` dan update `src/predictor.py`.
3. **Strategy baru** → edit `src/simulation_engine.py` dan `src/broker_sim.py`.
4. **UI baru** → tambahkan file di `src/pages/` dan daftarkan di `src/app.py`.
5. **Jangan lupa test** → tambahkan test di `tests/` dan jalankan `pytest`.

## � Prerequisites

- Python 3.12+
- pip
- Internet connection (untuk fetch data Yahoo Finance)

### System Dependencies (Linux)
```bash
sudo apt install python3 python3-venv python3-pip
```

## 🏗️ Arsitektur

```
saham/
├── .env.example              # Template environment variables
├── .github/workflows/        # GitHub Actions CI/CD
├── .gitignore
├── README.md                 # File ini
├── requirements.txt          # Dependencies produksi
├── requirements-dev.txt      # Dependencies development (pytest, shap, mlflow, fastapi)
├── pytest.ini                # Pytest configuration (warning filters)
├── conftest.py               # Pytest fixtures
├── Dockerfile                # Docker containerization
├── docker-compose.yml        # App + API + MLflow services
├── app.py                    # Entry point (delegates to src/app.py)
├── run_analysis.py           # Entry point (delegates to src/run_analysis.py)
│
├── docs/                     # Dokumentasi lengkap
│   ├── ACUAN_PROYEK.md              # Spesifikasi & acuan proyek
│   ├── ARCHITECTURE.md              # Detail arsitektur & data flow
│   ├── COMPETITIVE_ANALYSIS.md      # Analisis 14 kompetitor
│   ├── CONTRIBUTING.md              # Panduan kontribusi
│   ├── DEVELOPMENT.md               # Setup development multi-komputer
│   ├── PENGETAHUAN_DASAR_PASAR_MODAL.md  # 16 section pengetahuan pasar modal
│   └── TEAM_ROADMAP.md              # Roadmap tim, 10 batch implementasi
│
├── src/                      # Source code Python (85 modul)
│   ├── __init__.py
│   ├── config.py             # Konfigurasi: tickers, API keys, parameter model
│   ├── database.py           # SQLite: prediksi, harga aktual, log aktivitas
│   ├── data_fetcher.py       # Fetch data Yahoo Finance & FRED API + rate limiter
│   ├── rate_limiter.py       # Sliding window rate limiter (yfinance, FRED, AV, Finnhub)
│   ├── preprocessor.py       # Feature engineering: 201 fitur
│   ├── feature_selection.py  # SHAP, Boruta, correlation filter, variance threshold
│   ├── models.py             # Hybrid Ensemble: RF + XGBoost + LightGBM (+ LSTM opsional)
│   ├── hyperopt.py           # Optuna TPE Bayesian optimization + walk-forward CV
│   ├── validation.py         # Walk-forward CV, purged k-fold, IC/Rank IC/ICIR metrics
│   ├── predictor.py          # Pipeline prediksi: ML + business rules + MTF + risk + SHAP + MLflow + drift
│   ├── indicators.py         # 11 indikator teknikal + composite signal
│   ├── technical_advanced.py # Advanced: trendline, support/resistance, pivot points
│   ├── patterns.py           # Candlestick + chart pattern (H&S, Double Top, Triangles)
│   ├── mtf.py                # Multi-Timeframe Analysis (1W/1D/4H/1H confluence)
│   ├── scoring.py            # Composite AI Score (1-10), multi-dimension rating, skill packs
│   ├── risk_manager.py       # VaR, CVaR, Sharpe, Sortino, Kelly, Position Sizing
│   ├── pro_risk.py           # Professional risk governance: kill switch, drawdown control
│   ├── portfolio.py          # 5 methods: Markowitz, Black-Litterman, Risk Parity, HRP, CVaR
│   ├── portfolio_risk.py     # Correlation-aware allocation, stress testing, Kelly weights
│   ├── slippage.py           # Square-root market impact, bid-ask spread, execution optimization
│   ├── quant_finance.py      # Entry/Target/Stop, realistic backtest, vectorized backtest, tear sheet
│   ├── sentiment.py          # Fear & Greed Index (7 komponen) + Market Regime
│   ├── regime.py             # Market Regime Detection (Bull/Bear/Sideways)
│   ├── intermarket.py        # Rolling correlation, lead-lag, spread/z-score
│   ├── behavioral.py         # Behavioral finance: FOMO, panic, herding, contrarian
│   ├── wyckoff.py            # Wyckoff phase detection (accumulation/distribution)
│   ├── elliott_wave.py       # Elliott Wave pattern detection + Fibonacci ratios
│   ├── sector_rotation.py    # Economic phase detection + sector rotation signals
│   ├── factor_model.py       # CAPM regression, factor score (alpha/beta/R²)
│   ├── event_driven.py       # Earnings, corporate actions, analyst recs, economic calendar, news
│   ├── ai_agent.py           # Multi-agent: Market Analyst, Risk Manager, News Analyst, Portfolio Advisor
│   ├── sentiment_pipeline.py # RSS scraping + FinBERT + Fear&Greed + trend tracking + alerts
│   ├── fundamental.py        # Fundamental data via yfinance (P/E, P/B, ROE, debt ratio)
│   ├── dcf_valuation.py      # DCF valuation model
│   ├── idx_rules.py          # IDX/BEI trading rules: auto-rejection, circuit breaker, T+2
│   ├── compliance.py         # OJK disclaimers, audit trail, risk disclosure
│   ├── data_pipeline.py      # Data quality monitoring, lineage tracking, feature store
│   ├── mlflow_tracking.py    # MLflow experiment/model/backtest logging
│   ├── retrain_scheduler.py  # Automated retraining: time-based + drift detection (KS test)
│   ├── realtime_feed.py      # Real-time price polling + caching + price alerts
│   ├── fraud_detection.py    # 4-layer anti-fraud: data quality, cross-source, index consistency, news divergence
│   ├── investor_tools.py     # Dividend reinvestment (DRIP), asset allocation model, correlation matrix
│   ├── intraday_model.py     # Intraday ML model (5m/15m) for day trader mode
│   ├── execution_algo.py     # VWAP/TWAP execution algorithms, Corwin-Schultz spread estimator
│   ├── api.py                # FastAPI REST API (10 endpoints with OpenAPI docs)
│   ├── notifier.py           # Notifikasi Telegram & Email
│   ├── run_analysis.py       # CLI script untuk cron job harian
│   ├── app.py                # Streamlit dashboard (24 halaman)
│   ├── kronos_integration.py # Kronos foundation model: zero-shot forecasting ensemble member
│   ├── explainability.py     # SHAP explainability: per-prediction feature attribution
│   ├── drift_monitor.py      # Model drift monitoring: PSI + KS-test + auto-alert
│   ├── screener.py           # Multi-stock screener: batch scan blue chips, rank by AI score
│   ├── event_backtest.py     # Event-driven backtesting: walk-forward, BEI costs, stop-loss/TP
│   ├── alt_data_sources.py   # Alpha Vantage + Finnhub integration + cross-source verification
│   ├── broker_sim.py         # Broker API simulation: order execution, slippage, partial fills
│   ├── logging_config.py     # Structured logging: rotating file handler, configurable level
│   ├── simulation_engine.py  # Walk-forward market simulation: train 6mo, simulate 3mo, long+short, ATR-based SL/TP
│   ├── system_check.py       # Hardware compatibility checker: CPU/RAM/GPU/disk/packages
│   └── pages/                # Streamlit UI pages (24 halaman)
│       ├── dashboard.py          # 📊 Dashboard utama
│       ├── prediksi.py           # 🔮 Prediksi dengan ensemble model
│       ├── chart_teknikal.py     # 📈 Chart & indikator teknikal
│       ├── sentiment.py          # 😰 Fear & Greed Index
│       ├── regime.py             # 🎯 Market Regime Detection
│       ├── intermarket.py        # 🌐 Analisis Antar-Pasar
│       ├── risk_management.py    # ⚠️ Manajemen Risiko
│       ├── portfolio.py          # 💼 Optimasi Portofolio (5 methods)
│       ├── portfolio_v2.py       # 💰 Keuangan & Trading
│       ├── unified_pipeline.py   # 🔄 Daily Pipeline
│       ├── backtesting.py        # 🧪 Backtesting
│       ├── trading_agent.py      # 🤖 Trading Agent
│       ├── notifikasi.py         # 🔔 Notifikasi
│       ├── riwayat.py            # 📋 Riwayat & Akurasi
│       ├── pengaturan.py         # ⚙️ Pengaturan
│       ├── market_hours.py       # 🌍 Market Hours
│       ├── data_inventory.py     # 📦 Data Inventory
│       ├── advanced_analytics.py # 🔬 Analisis Lanjutan
│       ├── command_center.py     # 🎯 Command Center (consolidated view)
│       ├── screener.py           # 📡 Multi-Stock Screener
│       ├── options_analysis.py   # 📐 Options Analysis (Greeks)
│       ├── broker_sim.py         # 🏦 Broker Simulation
│       ├── simulation.py         # 🎮 Simulasi Pasar (walk-forward)
│       └── system_check.py       # 💻 Cek Kecocokan Sistem
│
├── tests/                    # Unit & integration tests (366+ tests, 13 files)
│   ├── __init__.py
│   ├── test_core.py              # Core: config, database, data_fetcher, preprocessor, models
│   ├── test_advanced_analysis.py # Wyckoff, Elliott, behavioral, sector rotation, factor model
│   ├── test_event_driven.py      # Event-driven: earnings, corporate actions, news sentiment
│   ├── test_idx_regime.py        # IDX rules, regime detection
│   ├── test_integration.py       # End-to-end: data pipeline, AI agent, API, compliance
│   ├── test_ml_engineer.py       # Validation, scoring, patterns, feature selection
│   ├── test_quant_finance.py     # Entry/stop, backtest, tear sheet, Wyckoff, DSR, drift
│   ├── test_pro_risk.py          # Risk governance, kill switch, drawdown control
│   ├── test_production.py        # Hyperopt, retrain, realtime, portfolio risk, slippage
│   ├── test_mtf_portfolio.py     # MTF confluence + 5 portfolio optimization methods
│   ├── test_new_modules.py       # Kronos, SHAP, drift, screener, event backtest, alt data, broker sim, logging
│   └── conftest.py               # Shared test fixtures
│
├── data/                     # Data cache (gitignored)
└── models/                   # Model artifacts (gitignored)
```

Untuk detail arsitektur dan data flow, baca [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## 📊 Fitur Utama

### Dashboard (24 Halaman)

> **Catatan:** 24 halaman aktif di sidebar Streamlit (termasuk Command Center sebagai consolidated view).
1. **🎯 Command Center** — Consolidated view: prediksi, teknikal, sentimen, risk dalam satu halaman
2. **📊 Dashboard** — Overview pasar: metric cards, Fear & Greed gauge, Market Regime, chart IHSG, indikator teknikal, korelasi antar pasar
3. **🔮 Prediksi** — Jalankan prediksi dengan ensemble model, pilih target (IHSG atau blue chip ID), lihat model votes, business rules, MTF confluence, entry/target/stop, Wyckoff, Elliott Wave, SHAP explainability
4. **📈 Chart & Teknikal** — Candlestick chart + 11 indikator (MACD, Stochastic, RSI, BB, dll), composite signal, pattern detection
5. **� Screener** — Multi-stock screener: batch scan blue chip Indonesia, rank by AI score, top BUY/SELL
6. **�😰 Fear & Greed** — Indeks sentimen 0-100 dari 7 komponen (VIX, Momentum, RSI, Safe Haven, Breadth, Volatility, Oil)
7. **🎯 Market Regime** — Deteksi Bull/Bear/Sideways dengan MA alignment, ADX, slope, volatility + strategy recommendation
8. **🌐 Inter-Market** — Korelasi matrix, rolling correlation, lead-lag analysis, spread/z-score untuk pair trading
9. **⚠️ Risk Management** — VaR, CVaR, Sharpe, Sortino, Max Drawdown, Kelly Criterion, Position Sizing Calculator
10. **💼 Portfolio Optimizer** — 5 methods: Markowitz, Black-Litterman, Risk Parity, HRP, CVaR + comparison table
11. **💰 Keuangan & Trading** — Kelola modal, deposit/withdraw, tracking PnL, alokasi portofolio
12. **🔄 Daily Pipeline** — Pipeline harian terintegrasi: fetch data → predict → verify → notify
13. **🧪 Backtesting** — Directional Accuracy per model, MAPE, simulasi paper trading dengan equity curve
14. **🤖 Trading Agent** — Multi-agent AI: Market Analyst, Risk Manager, News Analyst, Portfolio Advisor
15. **🏦 Broker Simulation** — Simulasi broker API: order submission, slippage, partial fills, portfolio tracking
16. **📐 Options Analysis** — Options Greeks calculator, implied volatility, strategy analyzer
17. **🎮 Simulasi Pasar** — Walk-forward trading simulation: train 1 bulan, simulate 3 bulan, 1 hari = 1 detik
18. **💻 Cek Sistem** — Cek kecocokan hardware: CPU, RAM, GPU, disk, packages + persentase kompatibilitas
19. **🔔 Notifikasi** — Daftar notifikasi Telegram/Email/in-app
20. **📋 Riwayat & Akurasi** — Riwayat prediksi, verifikasi, pie chart benar/salah, log aktivitas
21. **⚙️ Pengaturan** — Konfigurasi tickers, model, business rules, manual verifikasi
22. **🌍 Market Hours** — Jam buka bursa global, status market real-time
23. **📦 Data Inventory** — Inventory data tersimpan di database, jumlah row per ticker
24. **🔬 Analisis Lanjutan** — Wyckoff, Elliott Wave, Behavioral, Sector Rotation, Factor Model

### Model ML
- **Hybrid Ensemble**: Random Forest + XGBoost + LightGBM (LSTM opsional jika TensorFlow terinstall)
- **Kronos Foundation Model**: Zero-shot financial time series forecasting (NeoQuasar/Kronos-small)
- **201 fitur**: Returns, MA, RSI, MACD, Bollinger Bands, Stochastic, ATR, Williams %R, CCI, OBV, inter-market spreads, rolling correlation, lag features
- **SHAP Explainability**: Per-prediction feature attribution dengan TreeExplainer + feature_importance fallback
- **MLflow Tracking**: Experiment tracking, model registry, backtest logging
- **Model Drift Monitoring**: PSI + KS-test untuk deteksi distribution shift, auto-alert
- **Hyperparameter Tuning**: Optuna TPE Bayesian optimization with walk-forward CV
- **Feature Selection**: SHAP, Boruta, correlation filter, variance threshold
- **Validation**: Walk-forward CV, purged k-fold with embargo, IC/Rank IC/ICIR metrics

### Analisis Profesional
- **Multi-Timeframe Analysis (MTF)**: Confluence scoring across 4 timeframes (1W/1D/4H/1H) dengan confidence adjustment
- **Composite AI Score (1-10)**: Multi-dimension rating (Technical, Sentiment, Momentum, Risk) + skill pack scoring
- **Pattern Detection**: Candlestick (Hammer, Doji, Engulfing) + chart patterns (H&S, Double Top/Bottom, Triangles)
- **Market Structure**: HH/HL/LH/LL, BOS, CHoCH detection
- **Wyckoff Method**: Accumulation/Distribution phase detection, Spring, SOS, Upthrust, SOW
- **Elliott Wave**: Pattern detection + Fibonacci ratios + next target/invalidation
- **Behavioral Finance**: FOMO, panic, herding, contrarian signal analysis
- **Sector Rotation**: Economic phase detection + favored/avoided sectors
- **Factor Model**: CAPM regression (alpha, beta, R²) + factor score
- **Event-Driven**: Earnings calendar, corporate actions, analyst recommendations, economic calendar, news sentiment (FinBERT)
- **Entry/Target/Stop**: ATR-based levels with risk-based position sizing
- **Realistic Backtest**: BEI commission (0.15%/0.25%), slippage, T+2 settlement
- **Vectorized Backtest**: Pandas-based, 75x faster than event-driven
- **Pyfolio Tear Sheet**: Sharpe, Sortino, Calmar, VaR, CVaR, alpha/beta, information ratio

### Portfolio Optimization (5 Methods)
1. **Markowitz** — Mean-Variance Optimization (Monte Carlo + scipy)
2. **Black-Litterman** — Market equilibrium + investor views
3. **Risk Parity** — Equal risk contribution per asset
4. **Hierarchical Risk Parity (HRP)** — Clustering-based allocation
5. **CVaR Optimization** — Conditional Value at Risk minimization

### Risk Management
- **VaR**: Historical, Parametric, Monte Carlo
- **CVaR**: Expected Shortfall
- **Sharpe, Sortino, Calmar** ratios
- **Kelly Criterion**: Position sizing
- **Professional Risk Governance**: Kill switch, drawdown control, volatility targeting
- **Slippage Model**: Square-root market impact (Almgren-Chriss), bid-ask spread
- **Trading Style Presets**: Investor (1 trade/day), Swing (3), Day Trader (20), Scalper (50)

### Anti-Fraud Detection (4-Layer)
- **Data Quality Validation**: Completeness, freshness, price anomaly, volume sanity, duplicates
- **Cross-Source Verification**: Yahoo Finance vs Alpha Vantage price comparison (tolerance 2%)
- **Index-Constituent Consistency**: ^JKSE vs weighted blue chips correlation check
- **News-Price Divergence**: Alert if price moves contra all news sentiment

### Investor Tools
- **Dividend Reinvestment (DRIP)**: Compounding simulation with/without reinvestment, CAGR
- **Asset Allocation Model**: Conservative/Moderate/Aggressive/Custom (age-based) stock/bond/cash split
- **Correlation Matrix**: Pearson/Spearman, clustering, diversification ratio, redundancy detection

### Day Trader Tools
- **Intraday ML Model**: 5m/15m interval prediction with VWAP deviation, volume ROC, session timing
- **Execution Algorithms**: VWAP (volume-weighted) and TWAP (time-weighted) order splitting
- **Spread Estimator**: Corwin-Schultz bid-ask spread estimation from OHLCV
- **Multi-Day Forecast**: 3d/5d/10d/20d price projection with volatility-based confidence intervals
- **Trailing Stop Loss**: Auto-adjusting stop based on highest price since entry
- **Partial Exit**: Sell 50% at TP1, 30% at TP2, 20% at final TP

### AI/LLM Agent
- **Multi-Agent System**: Market Analyst, Risk Manager, News Analyst, Portfolio Advisor
- **FinBERT Sentiment**: HuggingFace model with lexicon fallback
- **News Scraping**: RSS feeds (Kontan, Bisnis Indonesia, CNBC Indonesia, Reuters, Bloomberg)
- **Daily AI Briefing**: Comprehensive multi-agent analysis with actionable items

### Data Sources
- **Yahoo Finance** (yfinance): IHSG, S&P500, NASDAQ, Dow Jones, Nikkei, Hang Seng, STI, Gold, Oil, VIX, USD/IDR, CNY/IDR
- **FRED API** (opsional): US GDP, Treasury yields, inflation data
- **Alpha Vantage** (opsional): Daily/intraday data, fundamentals, cross-source verification
- **Finnhub** (opsional): Quotes, news, fundamentals, alternative data
- **RSS Feeds**: Kontan, Bisnis Indonesia, CNBC Indonesia, Reuters, Bloomberg
- **Blue Chip Indonesia**: BBCA, BBRI, TLKM, ASII, UNVR, GOTO, ICBP, ADRO

### API & Deployment
- **FastAPI REST API**: 10 endpoints with OpenAPI docs (health, predict, accuracy, patterns, sentiment, briefing, score)
- **Docker**: Dockerfile + docker-compose (app + API + MLflow services)
- **MLflow**: Experiment tracking, model registry, backtest logging
- **CI/CD**: GitHub Actions (lint, test, build — Python 3.11 & 3.12)
- **Rate Limiter**: Sliding window with exponential backoff for yfinance, FRED, web scraping

## 🔧 CLI Usage

```bash
# Prediksi harian (semua ticker)
python -m src.run_analysis --predict

# Verifikasi prediksi sebelumnya
python -m src.run_analysis --verify

# Backtesting
python -m src.run_analysis --backtest

# Jalankan semua (predict + verify + backtest + notify)
python -m src.run_analysis --all

# Prediksi untuk ticker spesifik
python -m src.run_analysis --predict --ticker BBCA.JK
```

## 🧪 Testing

```bash
# Run all tests (366 tests)
python -m pytest tests/ -v --tb=short

# Run new modules test only
python -m pytest tests/test_new_modules.py -v

# Run specific test file
python -m pytest tests/test_mtf_portfolio.py -v

# Run with coverage
python -m pytest tests/ --cov=src --cov-report=term-missing
```

## 🎮 Simulasi Pasar

```bash
# Run walk-forward simulation (train 1 month, simulate 3 months)
python run_simulation.py

# Results saved to src/data/simulation_results.json
# View in Streamlit: 🎮 Simulasi Pasar page
```

## 📡 Notifikasi

Setup di `.env`:
- **Telegram**: Buat bot via @BotFather, dapatkan token & chat ID
- **Email**: SMTP (Gunakan App Password untuk Gmail)

## 🔄 GitHub Actions

Otomatisasi terjadwal di `.github/workflows/`:
- **CI/CD**: Lint, test, build pada setiap PR & commit (Python 3.11 & 3.12)
- **Daily Analysis**: Prediksi harian & verifikasi otomatis

## 👥 Multi-Developer Setup

Untuk panduan setup di komputer baru, baca [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md).
Untuk kontribusi kode, baca [docs/CONTRIBUTING.md](docs/CONTRIBUTING.md).
Untuk analisis kompetitor, baca [docs/COMPETITIVE_ANALYSIS.md](docs/COMPETITIVE_ANALYSIS.md).
Untuk roadmap tim, baca [docs/TEAM_ROADMAP.md](docs/TEAM_ROADMAP.md).
Untuk pengetahuan dasar pasar modal, baca [docs/PENGETAHUAN_DASAR_PASAR_MODAL.md](docs/PENGETAHUAN_DASAR_PASAR_MODAL.md).

## ⚠️ Disclaimer

Aplikasi ini dibuat untuk tujuan **edukasi dan penelitian pribadi**. Prediksi bersifat probabilistik dan **bukan saran investasi**. Selalu lakukan riset mandiri dan konsultasi dengan penasihat keuangan yang berlisensi sebelum membuat keputusan investasi.

## 📄 License

MIT License - bebas digunakan untuk tujuan pribadi.
