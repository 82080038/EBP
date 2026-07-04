# 🏗️ Arsitektur Aplikasi

## Overview

Aplikasi ini menggunakan arsitektur **modular** dengan 85 modul Python yang terbagi dalam 9 layer:

1. **Data Layer** — Fetch, cache, rate limiting
2. **Feature Layer** — Preprocessing, feature selection, indicators
3. **Model Layer** — Ensemble ML, hyperopt, validation
4. **Analysis Layer** — MTF, Wyckoff, Elliott, behavioral, sector rotation, factor model, event-driven, regime
5. **Risk Layer** — Risk management, portfolio optimization, slippage, compliance, fraud detection
6. **Simulation Layer** — Broker simulator, walk-forward market simulation, short selling, ATR-based SL/TP
7. **Prediction Layer** — Predictor pipeline, scoring, AI agent, intraday model
8. **Execution Layer** — VWAP/TWAP algorithms, paper trading, trailing stop, partial exit
9. **Presentation Layer** — Streamlit dashboard (24 halaman), FastAPI REST API, CLI, notifier

```
┌──────────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │  Streamlit    │  │  FastAPI     │  │  CLI / GitHub Actions │    │
│  │  (app.py)     │  │  (api.py)    │  │  (run_analysis.py)    │    │
│  │  24 halaman   │  │  10 endpoints│  │  cron harian          │    │
│  └──────┬────────┘  └──────┬───────┘  └───────────┬──────────┘    │
└─────────┼──────────────────┼──────────────────────┼──────────────┘
          │                  │                      │
          ▼                  ▼                      ▼
┌──────────────────────────────────────────────────────────────────┐
│                    PREDICTION LAYER                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │  predictor.py │  │  scoring.py  │  │  ai_agent.py         │    │
│  │  Pipeline     │  │  AI Score    │  │  Multi-agent system  │    │
│  │  + business   │  │  1-10        │  │  (Analyst, Risk,     │    │
│  │  rules        │  │  multi-dim   │  │   News, Portfolio)   │    │
│  │  + multi-day  │  │              │  └──────────────────────┘    │
│  │  forecast     │  │              │  ┌──────────────────────┐    │
│  └──────┬────────┘  └──────────────┘  │ intraday_model.py     │    │
│                                      │  5m/15m ML prediction │    │
│                                      └──────────────────────┘    │
└─────────┼────────────────────────────────────────────────────────┘
          │
          ▼
┌──────────────────────────────────────────────────────────────────┐
│                    ANALYSIS LAYER                                 │
│  ┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────────┐     │
│  │ mtf.py    │ │ wyckoff.py│ │elliott.py │ │ behavioral.py │     │
│  │ 4-TF      │ │ Accum/    │ │ Wave      │ │ FOMO/panic/   │     │
│  │ confluence│ │ Distrib   │ │ patterns  │ │ herding       │     │
│  └───────────┘ └───────────┘ └───────────┘ └───────────────┘     │
│  ┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────────┐     │
│  │sector_    │ │factor_    │ │event_     │ │sentiment_     │     │
│  │rotation.py│ │model.py   │ │driven.py  │ │pipeline.py    │     │
│  │Economic   │ │CAPM alpha │ │Earnings,  │ │RSS + FinBERT  │     │
│  │phase      │ │beta, R²   │ │actions    │ │+ Fear&Greed   │     │
│  └───────────┘ └───────────┘ └───────────┘ └───────────────┘     │
│  ┌───────────┐ ┌───────────┐ ┌───────────┐                       │
│  │patterns.py│ │technical_ │ │regime.py  │                       │
│  │Candlestick│ │advanced.py│ │Bull/Bear/ │                       │
│  │+ chart    │ │Trendline, │ │Sideways   │                       │
│  └───────────┘ └───────────┘ └───────────┘                       │
└──────────────────────────────────────────────────────────────────┘
          │
          ▼
┌──────────────────────────────────────────────────────────────────┐
│                    RISK LAYER                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │risk_manager  │  │ pro_risk.py  │  │ portfolio.py         │    │
│  │VaR, CVaR,    │  │ Kill switch, │  │ 5 methods:           │    │
│  │Sharpe, Kelly │  │ drawdown ctrl│  │ Markowitz, BL,       │    │
│  └──────────────┘  └──────────────┘  │ RP, HRP, CVaR        │    │
│  ┌──────────────┐  ┌──────────────┐  └──────────────────────┘    │
│  │ slippage.py  │  │portfolio_risk│  ┌──────────────────────┐    │
│  │ Market impact│  │ Correlation- │  │ compliance.py         │    │
│  │ bid-ask      │  │ aware alloc  │  │ OJK, audit trail      │    │
│  └──────────────┘  └──────────────┘  └──────────────────────┘    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │execution_algo│  │investor_tools│  │ fraud_detection.py    │    │
│  │ VWAP, TWAP   │  │ DRIP, alloc  │  │ 6-layer anti-fraud    │    │
│  └──────────────┘  └──────────────┘  └──────────────────────┘    │
└──────────────────────────────────────────────────────────────────┘
          │
          ▼
┌──────────────────────────────────────────────────────────────────┐
│                    SIMULATION LAYER *(BARU)*                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │ broker_sim.py│  │simulation_   │  │ src/pages/           │    │
│  │ Order exec,  │  │engine.py     │  │ simulation.py        │    │
│  │ commission,  │  │ Walk-forward │  │ broker_sim.py        │    │
│  │ slippage,    │  │ train 6mo,   │  │ UI di browser        │    │
│  │ short selling│  │ sim 3mo,     │  │                      │    │
│  │              │  │ ATR SL/TP,   │  │                      │    │
│  │              │  │ long + short │  │                      │    │
│  └──────────────┘  └──────────────┘  └──────────────────────┘    │
└──────────────────────────────────────────────────────────────────┘
          │
          ▼
┌──────────────────────────────────────────────────────────────────┐
│                    MODEL LAYER                                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │ models.py    │  │ hyperopt.py  │  │ validation.py        │    │
│  │ RF+XGB+LGBM  │  │ Optuna TPE   │  │ Walk-forward CV      │    │
│  │ + TFT/       │  │ Bayesian opt │  │ Purged k-fold        │    │
│  │ PatchTST/    │  │              │  │ IC/Rank IC/ICIR      │    │
│  │ Kronos GPU   │  │              │  │                      │    │
│  └──────────────┘  └──────────────┘  └──────────────────────┘    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │retrain_      │  │mlflow_track  │  │ feature_selection.py │    │
│  │scheduler.py  │  │ Experiment   │  │ SHAP, Boruta,        │    │
│  │Drift detect  │  │ logging      │  │ correlation filter   │    │
│  │KS test       │  │              │  │                      │    │
│  └──────────────┘  └──────────────┘  └──────────────────────┘    │
└──────────────────────────────────────────────────────────────────┘
          │
          ▼
┌──────────────────────────────────────────────────────────────────┐
│                    FEATURE LAYER                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │preprocessor  │  │ indicators   │  │ fundamental.py       │    │
│  │201 features  │  │ 11 indicators│  │ P/E, P/B, ROE,       │    │
│  │MA, RSI, MACD │  │ + composite  │  │ debt ratio (yfinance)│    │
│  └──────────────┘  └──────────────┘  └──────────────────────┘    │
└──────────────────────────────────────────────────────────────────┘
          │
          ▼
┌──────────────────────────────────────────────────────────────────┐
│                    DATA LAYER                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │data_fetcher  │  │rate_limiter  │  │ database.py          │    │
│  │yfinance+FRED │  │ Sliding win  │  │ SQLite               │    │
│  │+ retry logic │  │ 60/60s yfin  │  │ prediksi, log,       │    │
│  └──────────────┘  │ 120/60s FRED │  │ harga aktual          │    │
│  ┌──────────────┐  └──────────────┘  └──────────────────────┘    │
│  │realtime_feed │  ┌──────────────┐  ┌──────────────────────┐    │
│  │Price polling │  │ data_pipeline│  │ dcf_valuation.py     │    │
│  │+ caching     │  │ Quality mon  │  │ DCF model            │    │
│  └──────────────┘  └──────────────┘  └──────────────────────┘    │
└──────────────────────────────────────────────────────────────────┘
```

## Complete Module Inventory (85+ Modules)

> Diagram di atas hanya menampilkan komponen utama. Berikut inventory
> lengkap semua modul `.py` di `src/` (85+ modul fungsional) dan
> `src/pages/` (24 halaman Streamlit).

### Data Layer

| Modul | Fungsi |
|-------|--------|
| `data_fetcher.py` | Fetch Yahoo Finance, FRED, Alpha Vantage, Finnhub, RSS |
| `rate_limiter.py` | Sliding window rate limiter untuk semua API |
| `realtime_feed.py` | Price polling + caching + real-time alerts |
| `realtime_monitor.py` | Monitor real-time data feed health |
| `data_pipeline.py` | Data quality monitoring & lineage tracking |
| `market_hours.py` | Market session detection (IDX, US, JP, HK, SG) |
| `alt_data_sources.py` | Alternative data integration |
| `fundamental.py` | Fundamental data via yfinance (P/E, P/B, ROE, etc.) |
| `dcf_valuation.py` | DCF valuation model |
| `database.py` | SQLite ORM-style wrapper & schema |

### Feature Layer

| Modul | Fungsi |
|-------|--------|
| `preprocessor.py` | Feature engineering: 201 fitur |
| `indicators.py` | 11+ indikator teknikal + composite signal |
| `feature_selection.py` | SHAP, Boruta, correlation filter, variance threshold |
| `technical_advanced.py` | Trendline, support/resistance, pivot points |

### Model Layer

| Modul | Fungsi |
|-------|--------|
| `models.py` | Hybrid ensemble: RF + XGBoost + LightGBM (+ LSTM) |
| `transformer_models.py` | PatchTST, TFT, LPatchTST dengan GPU PyTorch |
| `kronos_integration.py` | Zero-shot foundation model ensemble |
| `transfer_learning.py` | Parent-child transfer learning |
| `regime_models.py` | Regime-aware adaptive models |
| `drl_trading.py` | Deep RL trading agent |
| `hyperopt.py` | Optuna TPE Bayesian optimization |
| `validation.py` | Walk-forward CV, purged k-fold, IC/Rank IC/ICIR |
| `cpcv.py` | Combinatorial purged cross-validation |
| `alphalens_analysis.py` | Alpha factor IC & quantile returns |
| `feature_selection.py` | SHAP/Boruta selection |
| `mlflow_tracking.py` | Experiment tracking & model logging |
| `retrain_scheduler.py` | Automated retraining + drift trigger |
| `drift_monitor.py` | PSI + KS-test drift detection |
| `ab_testing.py` | A/B testing framework untuk model/strategy |
| `adaptive_learning.py` | Online adaptive learning |
| `afml.py` | Advances in Financial Machine Learning utilities |

### Analysis Layer

| Modul | Fungsi |
|-------|--------|
| `mtf.py` | Multi-timeframe confluence (1W/1D/4H/1H) |
| `wyckoff.py` | Wyckoff accumulation/distribution phases |
| `elliott_wave.py` | Elliott Wave pattern + Fibonacci |
| `behavioral.py` | FOMO/panic/herding behavioral signals |
| `sector_rotation.py` | Economic phase & sector rotation |
| `factor_model.py` | CAPM alpha/beta/R² |
| `event_driven.py` | Earnings, corporate actions, economic calendar |
| `event_backtest.py` | Event-driven backtesting engine |
| `patterns.py` | Candlestick + chart pattern detection |
| `smc.py` | Smart Money Concepts: HH/HL/LH/LL, BOS, CHoCH |
| `sentiment_pipeline.py` | FinBERT + RSS + Fear & Greed Index |
| `social_sentiment.py` | Social media sentiment |
| `regime.py` | Market regime detection (bull/bear/sideways/crisis) |
| `complex_systems.py` | Complex systems / network analysis |

### Risk Layer

| Modul | Fungsi |
|-------|--------|
| `risk_manager.py` | VaR, CVaR, Sharpe, Kelly, position sizing |
| `pro_risk.py` | Kill switch, drawdown control |
| `portfolio.py` | 5 portfolio optimization methods (Markowitz, BL, RP, HRP, CVaR) |
| `portfolio_risk.py` | Correlation-aware portfolio risk |
| `slippage.py` | Market impact & bid-ask slippage |
| `compliance.py` | OJK framework, audit trail |
| `fraud_detection.py` | 6-layer anti-fraud (data quality, cross-source, index, news, anti-manip, market manip) |
| `execution_algo.py` | VWAP/TWAP execution algorithms |
| `investor_tools.py` | DRIP, asset allocation, correlation matrix |
| `options_analysis.py` | Options Greeks & implied volatility |
| `quant_finance.py` | Entry/stop/target, backtest, tear sheet |

### Simulation Layer

| Modul | Fungsi |
|-------|--------|
| `broker_sim.py` | Order execution, commission, slippage, short selling |
| `simulation_engine.py` | Walk-forward market simulation |
| `paper_trading.py` | Paper trading ledger |

### Prediction Layer

| Modul | Fungsi |
|-------|--------|
| `predictor.py` | Pipeline prediksi harian |
| `scoring.py` | Composite AI Score 1-10 multi-dimension |
| `intraday_model.py` | Intraday ML model (5m/15m) |
| `ai_agent.py` | Multi-agent daily briefing |
| `bull_bear_debate.py` | Bull vs Bear debate |
| `trading_memory.py` | Learn from past predictions |
| `react_agent.py` | ReAct reasoning agent |
| `rag_system.py` | Retrieval augmented generation |
| `local_llm.py` | Local LLM integration (Ollama, phi3/llama3) |
| `anti_manipulation.py` | Anti-manipulation metrics: Z-Score volume shock, Amihud illiquidity, Beneish M-Score, wash trading, spoofing, fake news hype |
| `multi_mode_research.py` | Multi-mode AI research |
| `trading_agent.py` | Trading agent orchestration |
| `explainability.py` | SHAP explainability |
| `screener.py` | Point-in-time stock screening |
| `unified_pipeline.py` | Unified prediction pipeline orchestrator |

### Execution Layer

| Modul | Fungsi |
|-------|--------|
| `execution_algo.py` | VWAP/TWAP order splitting |
| `paper_trading.py` | Virtual paper trading |
| `run_analysis.py` | CLI cron runner |

### Presentation Layer

| Modul | Fungsi |
|-------|--------|
| `app.py` | Streamlit dashboard dispatcher |
| `api.py` | FastAPI REST API (10 endpoints) |
| `notifier.py` | Telegram + Email notifications |
| `ui_components.py` | Reusable Streamlit UI components |
| `system_check.py` | System health checks |
| `pages/*.py` | 24 halaman Streamlit dashboard |

### Shared / Utility

| Modul | Fungsi |
|-------|--------|
| `config.py` | Centralized configuration |
| `module_integrator.py` | Module integration helpers |
| `logging_config.py` | Logging configuration |
| `idx_rules.py` | IDX exchange rules & holidays |
| `backtesting.py` | Legacy backtesting utilities |
| `sentiment.py` | Legacy sentiment utilities |

## Prediction Pipeline (Data Flow)

### 1. Prediksi Harian (Full Pipeline)

```
data_fetcher.py → fetch_all_market_data()
    ├─ yfinance (daily/ intraday)
    ├─ FRED API (macro data)
    ├─ Alpha Vantage (fundamental + macro)
    ├─ Finnhub (news + fundamental)
    └─ RSS feeds (news + sentiment)
    ↓ (rate_limiter.py controls API frequency)
preprocessor.py → prepare_features() → 201 fitur
    ├─ technical indicators (indicators.py)
    ├─ intermarket features (intermarket.py)
    ├─ macro features (FRED)
    ├─ fundamental features (fundamental.py)
    └─ sentiment features (sentiment_pipeline.py)
    ↓
feature_selection.py → SHAP/Boruta → top 50 fitur (opsional)
    ↓
models.py → HybridEnsemble.train() → RF + XGBoost + LightGBM
    ├─ transformer_models.py → PatchTST / TFT / LPatchTST (GPU)
    ├─ kronos_integration.py → zero-shot foundation model
    ├─ transfer_learning.py → parent-child fine-tuning
    ├─ regime_models.py → regime-aware adaptive models
    ├─ drl_trading.py → Deep RL agent (opsional)
    ├─ cpcv.py → combinatorial purged cross-validation
    └─ alphalens_analysis.py → IC/Rank IC factor analysis
    ↓ (hyperopt.py: Optuna TPE tuning, validation.py: walk-forward CV)
predictor.py → run_prediction()
    ├─ apply_business_rules() → Trend, Anti-FOMO, VIX, Oversold
    ├─ calc_composite_ai_score() → AI Score 1-10
    ├─ detect_market_regime() → Bull/Bear/Sideways → adjust signal
    ├─ calc_entry_target_stop() → Entry, Stop, Target 1/2/3
    ├─ calc_bei_total_cost() → Commission, break-even
    ├─ detect_wyckoff_phase() → Accumulation/Distribution → override
    ├─ detect_elliott_wave() → Wave pattern + Fibonacci
    ├─ analyze_behavioral() → FOMO/panic/herding → adjust confidence
    ├─ detect_economic_phase() → Sector rotation signals
    ├─ run_capm_regression() → Alpha/Beta/R² factor score
    ├─ run_event_driven_analysis() → Earnings, news, economic calendar
    ├─ run_mtf_analysis() → 4-timeframe confluence → adjust confidence
    ├─ explainability.py → SHAP per-prediction feature attribution
    ├─ bull_bear_debate.py → bull vs bear argumentation
    ├─ rag_system.py → retrieval augmented reasoning
    ├─ react_agent.py → ReAct reasoning loop
    └─ RiskGovernance.evaluate_trade() → Kill switch, position sizing
    ↓
FraudDetector.validate_all() → Data quality, cross-source, index consistency, news divergence
    ↓
screener.py → batch scan + rank (point-in-time universe)
    ↓
ai_agent.py → run_daily_briefing() → multi-agent consolidated output
    ↓
InvestorTools → Asset allocation, correlation matrix, DRIP simulation
    ↓
database.py → simpan_prediksi()
    ↓
notifier.py → Telegram/Email (opsional)
```

### 2. Verifikasi
```
database.py → get_unverified_prediksi()
    ↓
data_fetcher.py → get_current_price()
    ↓
database.py → update_aktual() → hitung akurasi (DA, MAPE)
```

### 3. Backtesting
```
data_fetcher.py → fetch_all_market_data(2y)
    ↓
preprocessor.py → prepare_features()
    ↓
quant_finance.py → run_realistic_backtest() → BEI commission + slippage
    ↓
quant_finance.py → generate_tear_sheet() → Sharpe, Sortino, Calmar, VaR
```

### 4. Walk-Forward Simulation *(BARU)*
```
data_fetcher.py → fetch_all_market_data(2y)
    ↓
preprocessor.py → prepare_features() → 201+ fitur
    ↓
simulation_engine.py → MarketSimulation.run()
    ├─ models.py → HybridEnsemble.train() pada 6 bulan data training
    ├─ regime.py → detect_market_regime() → bull/bear/sideways/crisis
    ├─ Trend filter (MA5/MA20) → override sinyal jika trend kuat
    ├─ Win rate feedback → adjust confidence threshold
    ├─ ATR(14) → stop-loss, take-profit, trailing stop dinamis
    ├─ broker_sim.py → submit_order() dengan komisi, slippage, latency, partial fill
    │   ├─ BUY → long position
    │   ├─ SELL → long exit atau short entry
    │   ├─ COVER → short exit
    │   └─ Cash & position update
    ↓
JSON results + Streamlit page (simulation.py) → equity curve, trade log, metrics
```

### 5. Portfolio Optimization
```
data_fetcher.py → fetch returns for multiple tickers
    ↓
portfolio.py → compare_portfolio_methods()
    ├─ optimize_portfolio() → Markowitz (Monte Carlo + scipy)
    ├─ black_litterman_optimize() → Market equilibrium + views
    ├─ risk_parity_optimize() → Equal risk contribution
    ├─ hrp_optimize() → Hierarchical clustering + recursive bisection
    └─ cvar_optimize() → CVaR minimization (Rockafellar-Uryasev)
    ↓
Comparison table sorted by Sharpe ratio
```

### 6. AI Agent Daily Briefing
```
ai_agent.py → run_daily_briefing()
    ├─ MarketAnalyst → Technical + fundamental analysis
    ├─ RiskManager → Risk assessment + position sizing
    ├─ NewsAnalyst → RSS scraping + FinBERT sentiment
    └─ PortfolioAdvisor → Portfolio recommendation
    ↓
Consolidated briefing with actionable items
```

## Database Schema

### Tabel: prediksi
Hasil prediksi harian per ticker + tanggal target. Diverifikasi otomatis
berdasarkan harga aktual keesokan harinya.

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER PK | Auto increment |
| ticker | TEXT | Simbol ticker |
| tanggal_prediksi | DATE | Tanggal prediksi dibuat |
| tanggal_target | DATE | Tanggal target prediksi |
| harga_saat_ini | REAL | Harga saat prediksi dibuat |
| harga_prediksi | REAL | Harga yang diprediksi |
| harga_aktual | REAL | Harga aktual (diisi saat verifikasi) |
| arah_prediksi | TEXT | UP atau DOWN |
| arah_aktual | TEXT | UP atau DOWN (diisi saat verifikasi) |
| sinyal | TEXT | BUY, SELL, atau HOLD |
| confidence | REAL | Nilai confidence 0-1 |
| model_votes | TEXT | JSON votes per model |
| updated_at | TIMESTAMP | Waktu terakhir update |

### Tabel: log_aktivitas
Log aktivitas sistem untuk audit trail dan debugging.

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER PK | Auto increment |
| timestamp | DATETIME | Waktu aktivitas (default CURRENT_TIMESTAMP) |
| aktivitas | TEXT | Deskripsi aktivitas |
| detail | TEXT | Detail tambahan |

### Tabel: harga_harian
Data OHLCV historis harian per ticker. Di-cache dari yfinance untuk
menghindari fetch berulang.

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER PK | Auto increment |
| ticker | TEXT | Simbol ticker |
| tanggal | DATE | Tanggal harga |
| open | REAL | Harga open |
| high | REAL | Harga high |
| low | REAL | Harga low |
| close | REAL | Harga close |
| volume | REAL | Volume |

### Tabel: harga_intraday
Data OHLCV intraday (1m, 5m, 15m, 1h) per ticker.

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER PK | Auto increment |
| ticker | TEXT | Simbol ticker |
| timestamp | DATETIME | Timestamp candle |
| interval | TEXT | Interval (1m/5m/15m/1h) |
| open | REAL | Harga open |
| high | REAL | Harga high |
| low | REAL | Harga low |
| close | REAL | Harga close |
| volume | REAL | Volume |

### Tabel: notifikasi
In-app notification center + histori notifikasi.

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER PK | Auto increment |
| timestamp | DATETIME | Waktu notifikasi |
| kategori | TEXT | Kategori notifikasi |
| judul | TEXT | Judul notifikasi |
| pesan | TEXT | Isi notifikasi |
| level | TEXT | info / warning / error |
| dibaca | INTEGER | 0 = belum dibaca, 1 = sudah dibaca |

### Tabel: fundamental_data
Data fundamental per ticker (snapshot harian dari yfinance).

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER PK | Auto increment |
| ticker | TEXT | Simbol ticker |
| tanggal | DATE | Tanggal snapshot |
| timestamp | DATETIME | Waktu insert |
| pe_ratio | REAL | Price-to-Earnings |
| pbv_ratio | REAL | Price-to-Book |
| roe | REAL | Return on Equity |
| eps | REAL | Earnings per Share |
| debt_to_equity | REAL | Debt-to-Equity |
| dividend_yield | REAL | Dividend Yield |
| market_cap | REAL | Market cap |
| revenue | REAL | Revenue |
| net_income | REAL | Net income |
| total_cash | REAL | Total cash |
| total_debt | REAL | Total debt |
| free_cash_flow | REAL | Free cash flow |
| beta | REAL | Beta |
| profit_margin | REAL | Profit margin |
| current_ratio | REAL | Current ratio |
| ps_ratio | REAL | Price-to-Sales |
| peg_ratio | REAL | PEG ratio |
| roa | REAL | Return on Assets |
| roic | REAL | Return on Invested Capital |
| gross_margin | REAL | Gross margin |
| operating_margin | REAL | Operating margin |
| quick_ratio | REAL | Quick ratio |
| interest_coverage | REAL | Interest coverage |
| payout_ratio | REAL | Payout ratio |

### Tabel: technical_indicators
Snapshot indikator teknikal per ticker per tanggal.

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER PK | Auto increment |
| ticker | TEXT | Simbol ticker |
| date | DATE | Tanggal snapshot |
| sma_5, sma_10, sma_20, sma_50, sma_200 | REAL | Simple moving averages |
| ema_12, ema_26 | REAL | Exponential moving averages |
| rsi_14 | REAL | RSI |
| macd, macd_signal, macd_histogram | REAL | MACD |
| bollinger_upper, bollinger_middle, bollinger_lower | REAL | Bollinger Bands |
| stoch_k, stoch_d | REAL | Stochastic oscillator |
| williams_r | REAL | Williams %R |
| atr | REAL | Average True Range |
| adx | REAL | Average Directional Index |
| cci | REAL | Commodity Channel Index |
| created_at | TIMESTAMP | Waktu insert |

### Tabel: financial_ratios
Financial ratios per ticker per tahun/kuartal (dari data yfinance).

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER PK | Auto increment |
| ticker | TEXT | Simbol ticker |
| period_year | INTEGER | Tahun laporan |
| period_quarter | INTEGER | Kuartal (opsional) |
| pe_ratio, pb_ratio, ps_ratio, peg_ratio | REAL | Valuation ratios |
| roe, roa, roic | REAL | Profitability ratios |
| gross_margin, operating_margin, net_margin | REAL | Margin ratios |
| debt_to_equity, current_ratio, quick_ratio | REAL | Solvency & liquidity |
| interest_coverage | REAL | Interest coverage |
| dividend_yield, payout_ratio | REAL | Dividend ratios |
| created_at | TIMESTAMP | Waktu insert |

### Tabel: alerts
Price/technical alerts yang dibuat user.

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER PK | Auto increment |
| ticker | TEXT | Simbol ticker |
| alert_type | TEXT | Tipe alert |
| condition_value | REAL | Nilai threshold (opsional) |
| condition_text | TEXT | Deskripsi kondisi |
| is_active | INTEGER | 1 = aktif, 0 = non-aktif |
| is_triggered | INTEGER | 0 = belum trigger, 1 = sudah trigger |
| triggered_at | TIMESTAMP | Waktu trigger |
| message | TEXT | Pesan alert saat trigger |
| created_at | TIMESTAMP | Waktu dibuat |

## Konfigurasi

Semua konfigurasi terpusat di `src/config.py`:
- `TICKERS`: 11 ticker pasar target (IHSG, S&P500, NASDAQ, N225, HSI, STI, dll)
- `BLUE_CHIPS_ID`: 8 blue chip Indonesia (BBCA, BBRI, TLKM, ASII, UNVR, ICBP, KLBF, EMTK)
- `BLUE_CHIPS_US/JP/HK/SG`: 25 blue chip global (5 per region)
- `MODEL_CONFIG`: Parameter model (RF, XGB, LGBM, LSTM, GPU, lookback, lags)
- `BUSINESS_RULES`: Threshold trend follower, anti-FOMO, VIX panic, min confidence
- `NOTIFICATION_CONFIG`: Telegram & email settings
- `FRED_SERIES`: Makro indicators (FEDFUNDS, CPI, Treasury 10Y, Unemployment)
- `DB_PATH`: SQLite path (`src/data/saham_prediksi.db`)

## Rate Limiter Configuration

Didefinisikan di `src/rate_limiter.py`:

| Limiter | max_calls | window_seconds | min_delay | Target |
|---------|-----------|----------------|-----------|--------|
| `_yf_limiter` | 60 | 60s | 1.0s | Yahoo Finance API |
| `_fred_limiter` | 120 | 60s | 0.5s | FRED API |
| `_av_limiter` | 5 | 60s | 12.0s | Alpha Vantage API (500 req/day free tier) |
| `_finnhub_limiter` | 60 | 60s | 1.0s | Finnhub API (60 req/min free tier) |
| `_web_limiter` | 20 | 60s | 2.0s | Web scraping (RSS, Trading Economics) |

Fitur: Sliding window, exponential backoff on 429, thread-safe.

## Environment Variables

| Variable | Required | Deskripsi |
|----------|----------|-----------|
| `FRED_API_KEY` | No | API key FRED untuk macro data |
| `ALPHA_VANTAGE_API_KEY` | No | API key Alpha Vantage (fallback data + cross-check) |
| `FINNHUB_API_KEY` | No | API key Finnhub (real-time quotes, news, fundamentals) |
| `TELEGRAM_BOT_TOKEN` | No | Token bot Telegram |
| `TELEGRAM_CHAT_ID` | No | Chat ID Telegram |
| `SMTP_SERVER` | No | Server SMTP untuk email |
| `SMTP_PORT` | No | Port SMTP |
| `SMTP_USERNAME` | No | Username email |
| `SMTP_PASSWORD` | No | Password/App Password email |
| `EMAIL_TO` | No | Email penerima |

**Catatan:** Aplikasi berjalan tanpa `.env` — hanya fitur data alternatif
(Alpha Vantage, Finnhub, FRED) dan notifikasi yang tidak aktif.

## Testing

```bash
# Run all 366 tests
python -m pytest tests/ -v --tb=short

# Run with coverage
python -m pytest tests/ --cov=src --cov-report=term-missing

# Run specific module tests
python -m pytest tests/test_mtf_portfolio.py -v
```

| Test File | Tests | Coverage |
|-----------|-------|----------|
| test_core.py | ~30 | config, database, data_fetcher, preprocessor, models |
| test_advanced_analysis.py | ~20 | Wyckoff, Elliott, behavioral, sector rotation, factor model |
| test_event_driven.py | ~10 | Earnings, corporate actions, news sentiment |
| test_idx_regime.py | ~15 | IDX rules, regime detection |
| test_integration.py | ~20 | End-to-end: data pipeline, AI agent, API, compliance |
| test_ml_engineer.py | ~20 | Validation, scoring, patterns, feature selection |
| test_quant_finance.py | ~25 | Entry/stop, backtest, tear sheet, DSR, drift |
| test_pro_risk.py | ~15 | Risk governance, kill switch, drawdown control |
| test_production.py | ~30 | Hyperopt, retrain, realtime, portfolio risk, slippage |
| test_mtf_portfolio.py | 23 | MTF confluence + 5 portfolio optimization methods |
| test_new_modules.py | ~30 | Kronos, SHAP, drift, screener, event backtest, alt data, broker sim, logging |
| test_phase2_modules.py | ~100 | Transformer models (PatchTST/TFT), DRL, RAG, social sentiment, CPCV, more |
| test_anti_manipulation.py | 44 | Z-Score volume shock, Amihud illiquidity, Beneish M-Score, wash trading, spoofing, fake news hype |
| **Total** | **481** | **All passing** |

## Anti-Fraud Detection (6-Layer)

Layer | Module | Function
-----|--------|----------
1. Data Quality | `fraud_detection.py` | Completeness, freshness, price anomaly, volume sanity, duplicates
2. Cross-Source | `fraud_detection.py` | Yahoo vs Alpha Vantage price comparison (tolerance 2%)
3. Index Consistency | `fraud_detection.py` | ^JKSE vs weighted blue chips correlation
4. News Divergence | `fraud_detection.py` | Price contra sentiment alert
5. Anti-Manipulation Metrics | `anti_manipulation.py` | Z-Score volume shock, Amihud illiquidity, Beneish M-Score (Blueprint Bab 4)
6. Market Manipulation | `anti_manipulation.py` | Wash trading, spoofing, fake news hype detection (Blueprint Bab 3)

## Trading Style Presets

Style | Max Trades/Day | Max Position | Min Confidence | Use Case
-------|---------------|-------------|----------------|----------
Investor | 1 | 40% | 70% | Long-term hold
Swing | 3 | 25% | 65% | 1-30 day horizon
Day Trader | 20 | 15% | 60% | Intraday 5m/15m
Scalper | 50 | 10% | 55% | Sub-minute execution
