# Prompt Penyelesaian Aplikasi — AI Execution Guide

> **Versi 1.0** — Juni 2026
> Kumpulan prompt terstruktur untuk AI/LLM agar menyelesaikan aplikasi secara end-to-end.
> **Scope:** Semua fitur kecuali Mobile App (React Native).
> Ditulis berdasarkan riset internet mendalam + analisis dokumen di `docs/`.

---

## Cara Pakai

1. Baca file di `docs/` untuk konteks lengkap:
   - `ACUAN_PROYEK.md` — Spesifikasi lengkap aplikasi
   - `ARCHITECTURE.md` — Arsitektur 9 layer, 85+ modul, DB schema
   - `TEAM_ROADMAP.md` — Roadmap 5 fase, gap analysis, 10 peran
   - `BATCH_ANALYSIS_PROMPTS.md` — 14 prompt analisis + status implementasi
   - `COMPETITIVE_ANALYSIS.md` — Analisis 14 kompetitor
   - `DEVELOPMENT.md` — Panduan development
   - `UI_DESIGN.md` — Desain UI
   - `PENGETAHUAN_DASAR_PASAR_MODAL.md` — Knowledge base pasar modal

2. Jalankan prompt berurutan (Sprint 1 → Sprint 8).
3. Setiap sprint menghasilkan code yang langsung runnable + test.
4. GPU constraint: 2x GTX 1050 Ti (4GB VRAM, SM 6.1), PyTorch 2.5.1+cu121, numpy <2.0.

---

## Sprint 1: Database Migration & Caching (Foundation for Scale)

### Prompt 1.1: PostgreSQL + TimescaleDB Migration

```
Anda adalah backend engineer senior. Migrasikan database dari SQLite ke PostgreSQL + TimescaleDB.

KONTEKS:
- Saat ini: SQLite di src/database.py (tabel: prediksi, log_aktivitas, harga_harian, harga_intraday, notifikasi, fundamental_data, technical_indicators, financial_ratios, alerts)
- Target: PostgreSQL + TimescaleDB dengan hypertable untuk time-series data
- Backend: FastAPI (src/api.py, 10 endpoints)
- Lihat docs/ARCHITECTURE.md untuk schema lengkap

TUGAS:
1. Buat src/database_pg.py — PostgreSQL adapter menggunakan asyncpg + connection pool
2. Konversi semua tabel ke PostgreSQL schema:
   - harga_harian, harga_intraday, technical_indicators → hypertable (partition by time)
   - prediksi → hypertable dengan retention policy 2 tahun
   - Continuous aggregates untuk daily/weekly/monthly OHLCV
3. Buat migration script (src/migrations/001_sqlite_to_pg.py):
   - Auto-detect: jika PostgreSQL available → use PG, else fallback SQLite
   - Migrate existing SQLite data ke PostgreSQL
   - Idempotent: safe to re-run
4. Update src/config.py: tambah DATABASE_URL (postgresql://) dengan fallback ke SQLite
5. Update src/api.py: gunakan async database queries
6. Update docker-compose.yml: tambah PostgreSQL + TimescaleDB service
7. Compression policy untuk data > 30 hari
8. Retention policy: auto-drop data > 5 tahun

CONSTRAINTS:
- Python 3.12, asyncpg, SQLAlchemy 2.0 (async mode)
- Backward compatible: SQLite masih berfungsi jika PG tidak available
- Semua 366 existing tests harus tetap pass

OUTPUT: src/database_pg.py, src/migrations/, updated docker-compose.yml, updated config.py, tests
```

### Prompt 1.2: Redis Caching Layer

```
Anda adalah backend engineer. Implementasikan Redis caching layer untuk aplikasi.

KONTEKS:
- Saat ini: tidak ada caching, setiap request fetch data dari yfinance/DB
- Backend: FastAPI (src/api.py), data fetcher (src/data_fetcher.py)
- Lihat docs/ARCHITECTURE.md untuk data flow

TUGAS:
1. Buat src/cache.py — Redis cache manager dengan:
   - real-time price cache (TTL 5 detik)
   - historical OHLCV cache (TTL 1 jam)
   - prediction result cache (TTL 1 jam, invalidate on new prediction)
   - computed features cache (TTL 1 hari)
   - AI agent report cache (TTL 30 menit)
   - Fundamental data cache (TTL 24 jam)
2. Decorator pattern: @cached(ttl=300, key="ticker:{ticker}") untuk easy use
3. Cache invalidation strategy: event-based (new prediction → invalidate)
4. Fallback: jika Redis down → graceful degradation (direct DB/API)
5. Update src/data_fetcher.py: cache yfinance results
6. Update src/api.py: cache API responses
7. Update src/preprocessor.py: cache computed features
8. Add Redis to docker-compose.yml
9. Cache statistics endpoint: /api/cache/stats (hit rate, memory usage)

CONSTRAINTS:
- redis-py (async), Python 3.12
- Graceful degradation jika Redis unavailable
- Semua tests tetap pass

OUTPUT: src/cache.py, updated modules, docker-compose.yml, tests
```

### Prompt 1.3: Celery Async Task Queue

```
Anda adalah backend engineer. Implementasikan Celery + Redis untuk async task processing.

KONTEKS:
- Saat ini: prediction jobs blocking (bisa 30+ detik), tidak ada async processing
- Backend: FastAPI (src/api.py)
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 13 (Performance)

TUGAS:
1. Buat src/tasks/ — Celery task definitions:
   - predict_task(ticker, target_date) — run full prediction pipeline async
   - backtest_task(ticker, period, strategy) — run backtest async
   - train_model_task(ticker, model_type) — train/retrain model async
   - report_task(ticker, mode) — generate AI agent report async
   - batch_predict_task(tickers[]) — batch prediction untuk multiple tickers
   - data_ingest_task() — scheduled data ingestion
   - drift_check_task() — scheduled drift detection
2. Buat src/celery_app.py — Celery configuration:
   - task_acks_late=True (crash safety)
   - worker_prefetch_multiplier=1 (fair scheduling)
   - task_soft_time_limit=600, task_time_limit=900
   - max_retries=3 dengan exponential backoff
   - Queue routing: predict_queue, train_queue, report_queue
3. Update src/api.py: 
   - POST /predict/{ticker} → return task_id immediately, client polls /task/{id}
   - GET /task/{task_id} → status + result
   - WebSocket /ws/task/{task_id} → real-time progress
4. Celery Beat schedule:
   - Daily prediction (09:00 WIB)
   - Weekly backtest (Saturday 06:00 WIB)
   - Daily drift check (18:00 WIB)
   - Daily data ingestion (06:00 WIB)
5. Flower monitoring di docker-compose.yml
6. Dead letter queue untuk failed tasks

CONSTRAINTS:
- Celery 5.x, Redis as broker + result backend
- In-memory eager mode untuk testing (CELERY_TASK_ALWAYS_EAGER=True)
- Semua 366 tests tetap pass

OUTPUT: src/celery_app.py, src/tasks/, updated api.py, docker-compose.yml, tests
```

---

## Sprint 2: Performance Optimization

### Prompt 2.1: Polars for High-Performance Data Processing

```
Anda adalah performance engineer. Implementasikan Polars untuk ultra-fast data processing.

KONTEKS:
- Saat ini: pandas untuk semua data processing (201+ fitur, 85+ modul)
- Bottleneck: feature engineering (preprocessor.py) dan multi-ticker processing
- GPU: 2x GTX 1050 Ti (4GB VRAM)
- Riset: Polars 10-15x lebih cepat dari pandas untuk >100K rows (benchmark 2025-2026)
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 13

TUGAS:
1. Profil pipeline: identify top 5 bottleneck functions
2. Buat src/preprocessor_polars.py — Polars version dari prepare_features():
   - LazyFrame untuk query optimization
   - Expression API untuk parallel feature computation
   - Rolling windows (SMA, EMA, RSI, MACD, Bollinger)
   - Inter-market features (correlation, spread, ratio)
   - Macro features (FRED data merge)
3. Strategy: hybrid approach:
   - Polars untuk bulk transforms (load, filter, feature engineering)
   - .to_pandas() untuk last mile (scikit-learn, matplotlib, SHAP)
4. Update src/data_fetcher.py: Polars untuk batch OHLCV loading
5. Update src/screener.py: Polars untuk multi-ticker screening (1000+ tickers)
6. Benchmark: before/after timing untuk 11 ticker × 2 tahun data
7. Memory profiling: peak RAM usage comparison

CONSTRAINTS:
- polars >= 1.0, Python 3.12
- numpy <2.0 (scipy/shap compatibility)
- Pandas tetap untuk ML pipeline (scikit-learn integration)
- Incremental adoption: tidak rewrite semua sekaligus

OUTPUT: src/preprocessor_polars.py, benchmarks, updated modules, tests
```

### Prompt 2.2: Multiprocessing & GPU Optimization

```
Anda adalah performance engineer. Optimasi GPU utilization dan multiprocessing.

KONTEKS:
- GPU: 2x GTX 1050 Ti (4GB VRAM each, Pascal SM 6.1)
- PyTorch 2.5.1+cu121, LightGBM GPU (device='gpu')
- XGBoost GPU: NOT SUPPORTED (SM 6.1 too old)
- Saat ini: transformer_models.py uses cuda:0, models.py LightGBM GPU auto-detect
- Lihat docs/ARCHITECTURE.md Model Layer

TUGAS:
1. Multi-GPU strategy:
   - GPU 0 (cuda:0): PatchTST/TFT training (transformer_models.py)
   - GPU 1 (cuda:1): LightGBM GPU training (models.py)
   - Auto-detect available GPUs, fallback to single GPU or CPU
2. Mixed precision training (FP16) untuk transformer models:
   - torch.cuda.amp.autocast + GradScaler
   - Reduces VRAM usage ~40%, enables larger batch size
3. Gradient accumulation untuk effective batch size > physical batch
4. Multiprocessing untuk multi-ticker prediction:
   - multiprocessing.Pool untuk parallel ticker prediction
   - GPU tasks serialized per GPU, CPU tasks parallel
5. Lazy module loading:
   - Import heavy modules (torch, transformers) only when needed
   - Reduces startup time dari ~10s → <2s
6. Model warmup: pre-load models ke GPU memory at startup
7. Batch GPU inference: predict multiple tickers in one forward pass

CONSTRAINTS:
- PyTorch 2.5.1+cu121, CUDA 12.1
- numpy <2.0
- 4GB VRAM per GPU — batch size must fit
- XGBoost stays CPU (GPU not supported on SM 6.1)

OUTPUT: Updated transformer_models.py, models.py, new gpu_manager.py, benchmarks, tests
```

---

## Sprint 3: Next.js Dashboard & Real-Time

### Prompt 3.1: Next.js 14+ Dashboard Foundation

```
Anda adalah frontend architect. Bangun Next.js 14+ dashboard untuk menggantikan Streamlit.

KONTEKS:
- Saat ini: Streamlit 24 halaman (src/app.py, src/pages/*.py)
- Backend: FastAPI 10 endpoints (src/api.py)
- Target: Next.js 14+ App Router, TypeScript, shadcn/ui, TailwindCSS
- Lihat docs/UI_DESIGN.md untuk desain
- Lihat docs/ARCHITECTURE.md untuk Presentation Layer
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 8

TUGAS:
1. Buat frontend/ Next.js project structure:
   - App Router dengan nested layouts
   - TypeScript strict mode
   - TailwindCSS + shadcn/ui components
   - Dark/light theme (professional trading terminal aesthetic)
2. Layout:
   - Top bar: global search (Cmd+K), market status, theme toggle
   - Left sidebar: navigation (Dashboard, Prediksi, Simulation, Portfolio, Risk, AI Agent, Settings)
   - Center: main content area
   - Right panel: context info (ticker detail, signal box)
   - Bottom bar: macro indicators ticker (VIX, Fed Rate, Oil, Gold, USD/IDR)
3. Pages (minimum 12):
   - / — Dashboard overview (market summary, top signals, equity curve)
   - /predict/[ticker] — Prediction detail (signal, AI Score, entry/SL/TP, SHAP)
   - /simulation — Walk-forward simulation (equity curve, trade log, metrics)
   - /portfolio — Portfolio optimization (5 methods comparison, efficient frontier)
   - /risk — Risk management (VaR, CVaR, stress test, drawdown)
   - /ai-agent — AI Agent briefing (multi-agent report, bull vs bear debate)
   - /backtest — Backtesting (config, results, tear sheet)
   - /screener — Stock screener (filters, results table)
   - /sentiment — Sentiment analysis (news feed, FinBERT scores, Fear & Greed)
   - /patterns — Pattern detection (candlestick, chart patterns, SMC)
   - /settings — Settings (API keys, notification config, risk parameters)
   - /about — About (architecture, compliance, disclaimer)
4. Reusable components:
   - MetricCard, SignalBox, VerdictBox, ScoreGauge, EquityCurve
   - CandlestickChart, VolumeChart, IndicatorPanel
   - TickerSearch, TickerCard, NewsCard
   - BullBearDebate, AgentReport, SHAPChart
5. State management: Zustand + persist
6. API client: axios dengan retry, error handling, base URL config
7. TanStack Query untuk server state (caching, refetch, optimistic updates)
8. Responsive: desktop-first, tablet-friendly

CONSTRAINTS:
- Next.js 14+ App Router, TypeScript 5.x
- shadcn/ui + TailwindCSS 3.x
- Lucide icons
- TradingView Lightweight Charts untuk candlestick
- PWA support (manifest, service worker)

OUTPUT: frontend/ project, semua pages, components, API client, tests
```

### Prompt 3.2: TradingView Charts & WebSocket Real-Time

```
Anda adalah frontend engineer. Implementasikan TradingView charts dan WebSocket real-time updates.

KONTEKS:
- Backend: FastAPI (src/api.py), WebSocket belum ada
- Frontend: Next.js 14+ (dibuat di Prompt 3.1)
- Data: yfinance (15 min delay), target real-time via WebSocket
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 8

TUGAS:
1. TradingView Lightweight Charts integration:
   - Candlestick chart dengan volume overlay
   - Custom indicators: SMA, EMA, Bollinger, RSI, MACD
   - Auto trendline rendering (dari src/technical_advanced.py)
   - Chart pattern overlay (Head & Shoulders, Triangles, Double Top/Bottom)
   - Multi-timeframe: toggle 1H/4H/Daily/Weekly
   - Entry/SL/TP lines dari prediction
   - Dark theme optimized
2. WebSocket server (src/websocket.py):
   - WS /ws/realtime/{ticker} — real-time price updates
   - WS /ws/signals — new signal alerts
   - WS /ws/portfolio — portfolio P&L updates
   - WS /ws/task/{task_id} — async task progress
   - Heartbeat/ping-pong untuk connection health
   - Auto-reconnect dengan exponential backoff
3. Frontend WebSocket client:
   - useWebSocket() hook — auto-reconnect, message queue
   - useLivePrices(tickers[]) — batch subscription
   - Real-time price ticker di top bar
   - Live candlestick updates (append new candles)
   - Signal alert notifications (toast + sound)
4. Real-time data source:
   - Polling yfinance setiap 5 detik (saat market open)
   - Cache di Redis (TTL 5s)
   - Push ke WebSocket clients
5. Market hours detection (src/market_hours.py): only poll during IDX/US/JP/HK/SG sessions

CONSTRAINTS:
- tradingview-lightweight-charts npm package
- FastAPI WebSocket (Starlette)
- 100+ concurrent WebSocket connections
- < 100ms update latency

OUTPUT: src/websocket.py, frontend chart components, WebSocket hooks, tests
```

### Prompt 3.3: Natural Language Stock Scanner

```
Anda adalah AI engineer. Implementasikan natural language stock scanner.

KONTEKS:
- Saat ini: screener.py dengan filter berbasis parameter
- Target: NL query seperti "Find stocks breaking out with volume spike"
- Inspirasi: TrendSpider NL scanner (lihat docs/COMPETITIVE_ANALYSIS.md)
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 5 (item 7: NL scanner)

TUGAS:
1. Buat src/nl_scanner.py — Natural language stock scanner:
   - Parse NL query → structured filters
   - Contoh queries:
     - "stocks breaking out with volume spike" → price > MA20, volume > 2x avg
     - "oversold blue chips with RSI < 30" → RSI < 30, ticker in BLUE_CHIPS
     - "bullish engulfing with above-average volume" → pattern=engulfing, volume > avg
     - "stocks in accumulation phase" → wyckoff_phase=accumulation
     - "high AI score stocks with BUY signal" → ai_score >= 7, signal=BUY
2. LLM-powered query parsing:
   - Use local LLM (Ollama) atau API (OpenAI/DeepSeek)
   - Few-shot examples untuk query → filter mapping
   - Fallback: keyword-based parser jika LLM unavailable
3. Filter combinations:
   - Technical (RSI, MACD, MA, Bollinger, ATR, ADX)
   - Pattern (candlestick, chart patterns, SMC structure)
   - Fundamental (P/E, P/B, ROE, debt ratio)
   - Sentiment (FinBERT score, Fear & Greed)
   - Regime (bull/bear/sideways)
   - AI Score (1-10)
   - Wyckoff phase
4. Frontend: search bar dengan NL input + autocomplete suggestions
5. API endpoint: POST /api/scanner/nl { query: string } → results[]
6. Result ranking: sort by relevance score + AI Score

CONSTRAINTS:
- Python 3.12, Ollama atau OpenAI API
- Fallback keyword parser tanpa LLM
- Response time < 5 detik

OUTPUT: src/nl_scanner.py, API endpoint, frontend component, tests
```

---

## Sprint 4: ML Model Enhancement

### Prompt 4.1: CatBoost + Stacking Ensemble + Bayesian Averaging

```
Anda adalah quantitative ML researcher. Tingkatkan ensemble dari ~55% ke 58-62% directional accuracy.

KONTEKS:
- Saat ini: RF + XGBoost + LightGBM (majority vote), PatchTST/TFT (GPU), Kronos zero-shot
- GPU: 2x GTX 1050 Ti (4GB, SM 6.1), PyTorch 2.5.1+cu121
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 3
- Riset: XGB+LGB+CatBoost stacking ensemble mencapai Sharpe 1.165 (2025 research)

TUGAS:
1. Tambahkan CatBoost ke ensemble (src/models.py):
   - CatBoostClassifier (CPU, GPU tidak support SM 6.1)
   - Same interface sebagai RF/XGB/LGBM
   - Auto-detect: install catboost, fallback jika unavailable
2. Stacking ensemble (src/stacking_ensemble.py):
   - Base models: RF, XGB, LGBM, CatBoost, PatchTST (jika GPU available)
   - Meta-learner: LogisticRegression atau LightGBM
   - Walk-forward training: train base → predict OOF → train meta-learner
   - Save/load stacking model
3. Bayesian ensemble averaging (src/bayesian_ensemble.py):
   - Posterior probability per model (Bayesian Model Averaging)
   - Online Bayesian Stacking (OBS) — adapt weights over time
   - Non-stationary environment support
   - Portfolio selection connection (weights = portfolio allocation)
4. Regime-aware model selection:
   - 3 model terpisah: bull_model, bear_model, sideways_model
   - Weighted ensemble berdasarkan regime probability
   - Regime transition matrix untuk smooth switching
5. Online learning: partial_fit untuk model update harian
6. Walk-forward validation: IC/Rank IC/ICIR + deflated Sharpe ratio
7. Update src/predictor.py: gunakan stacking ensemble

CONSTRAINTS:
- catboost (CPU mode), scikit-learn, numpy <2.0
- GPU: PyTorch 2.5.1+cu121 untuk transformer models
- 4GB VRAM per GPU — batch size must fit
- Semua 366 tests tetap pass

OUTPUT: Updated models.py, src/stacking_ensemble.py, src/bayesian_ensemble.py, tests
```

### Prompt 4.2: Advanced Feature Engineering (201 → 500+)

```
Anda adalah ML engineer. Ekspansi feature engineering dari 201 → 500+ fitur.

KONTEKS:
- Saat ini: 201 fitur (35 teknikal, 25 inter-market, 10 makro, fundamental, lag)
- Target: 500+ fitur (benchmark: Kavout 900+, Danelfin 10,000+)
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 2
- Lihat docs/ARCHITECTURE.md Feature Layer

TUGAS:
1. Sentiment features (50+):
   - FinBERT sentiment score (positive, negative, neutral probability)
   - Fear & Greed Index (7 komponen: momentum, volatility, price trend, safe haven demand, junk bond demand, market breadth, put/call ratio)
   - News embedding (sentence-transformers → 384-dim → PCA to 10)
   - Social sentiment (Twitter/Stockbit keyword score, emoji sentiment)
   - Sentiment momentum (7-day rolling, 30-day rolling)
   - Sentiment divergence vs price (price up but sentiment down → warning)
   - News volume spike (z-score)
2. Microstructure features (30+):
   - Order flow imbalance (bid-ask spread proxy)
   - Volume profile (POC, value area, high/low volume nodes)
   - Smart money detection (OBV trend, MFI, accumulation/distribution)
   - VWAP deviation
   - Amihud illiquidity ratio
   - Kyle's lambda (price impact coefficient)
   - Realized volatility (5d, 10d, 20d, 60d)
   - Volatility regime (GARCH-like proxy)
3. Cross-asset features (20+):
   - Sector rotation signal (economic phase → sector performance)
   - Commodity-equity correlation (Gold/Oil ratio, Copper/Gold ratio)
   - Risk-on/Risk-off proxy (VIX, USD/IDR, gold, treasury)
   - Inter-market lead-lag (S&P500 → IHSG 1-3 day lag)
   - Correlation regime shift (rolling correlation vs long-term)
4. Regime-aware features (20+):
   - Regime probability (bull/bear/sideways/crisis)
   - Regime transition matrix (Markov chain proxy)
   - Volatility regime (low/medium/high)
   - Trend strength (ADX, linear regression slope)
   - Market breadth (advance/decline ratio, new highs/lows)
5. Feature selection pipeline:
   - SHAP importance → top 100
   - Boruta → confirm/reject
   - Correlation filter → remove >0.95 correlated
   - Variance threshold → remove near-zero
   - Final: 60-80 high-signal features
6. Point-in-time correctness: no look-ahead bias
7. Update src/preprocessor.py (atau preprocessor_polars.py)
8. Update tests untuk validasi feature count dan no leakage

CONSTRAINTS:
- pandas/polars, numpy <2.0, shap, boruta
- Point-in-time: features hanya menggunakan data yang available saat prediksi
- Memory: fitur harus fit dalam 16GB RAM untuk 11 ticker × 2 tahun

OUTPUT: Updated preprocessor.py, feature engineering code, feature selection, tests
```

### Prompt 4.3: Deep RL Trading Agent Enhancement

```
Anda adalah RL researcher. Tingkatkan Deep RL trading agent.

KONTEKS:
- Saat ini: src/drl_trading.py — DRL trading agent (basic)
- GPU: 2x GTX 1050 Ti (4GB, SM 6.1)
- Lihat docs/TEAM_ROADMAP.md Batch 11

TUGAS:
1. Implement DQN (Deep Q-Network) untuk trading:
   - State: 60-day window of top 50 features + regime + position
   - Action: BUY/SELL/HOLD dengan position sizing
   - Reward: risk-adjusted return (Sharpe-like)
   - Experience replay buffer (100K transitions)
   - Target network update setiap 1000 steps
   - Epsilon-greedy exploration dengan decay
2. Implement PPO (Proximal Policy Optimization):
   - Actor-Critic architecture
   - Clipped surrogate objective
   - GAE (Generalized Advantage Estimation)
   - Multiple epochs per update
3. Risk-aware reward shaping:
   - Penalize drawdown
   - Reward consistency (low volatility)
   - Transaction cost penalty
   - Regime-aware reward (higher reward in bull, capital preservation in bear)
4. Training:
   - Walk-forward: train on 6 months, validate on 1 month
   - Early stopping berdasarkan validation Sharpe
   - Save best model ke MLflow
5. Integration dengan predictor.py:
   - DRL signal sebagai input ke ensemble voting
   - DRL position sizing sebagai input ke risk_manager.py
6. Backtest: compare DRL vs ensemble vs buy-and-hold

CONSTRAINTS:
- PyTorch 2.5.1+cu121, CUDA 12.1
- 4GB VRAM — network size must fit (small MLPs, not large CNNs)
- numpy <2.0
- stable-baselines3 optional (custom implementation preferred untuk control)

OUTPUT: Updated drl_trading.py, training script, backtest results, tests
```

---

## Sprint 5: Risk Management & Backtesting

### Prompt 5.1: Dynamic Position Sizing & De-Risking Ladder

```
Anda adalah professional risk manager. Implementasikan dynamic position sizing dan de-risking ladder.

KONTEKS:
- Saat ini: VaR, CVaR, Sharpe, Kelly, ATR SL/TP, trailing stop, kill switch
- Gap: dynamic sizing berdasarkan regime + win rate, de-risking ladder
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 4

TUGAS:
1. Dynamic position sizing (src/risk_manager.py enhancement):
   - Regime-based: bull=2% risk/trade, bear=0.5%, crisis=0%, sideways=1%
   - Win rate feedback: last 20 trades win rate → adjust risk
     - WR > 60%: +20% risk budget
     - WR 40-60%: normal risk
     - WR < 40%: -50% risk budget
     - WR < 30%: stop trading (kill switch)
   - Volatility-adjusted: ATR-based risk per trade
   - Kelly fraction: use half-Kelly untuk safety
2. Correlation-aware exposure (src/portfolio_risk.py enhancement):
   - Rolling correlation matrix (30-day)
   - If all positions > 0.8 correlation → reduce total exposure 50%
   - Sector concentration limit: max 30% per sector
3. Portfolio-level VaR constraint:
   - Portfolio VaR < 5% of capital
   - If exceeded → reduce largest positions first
4. Stress testing:
   - COVID crash (Mar 2020): -30% in 1 month
   - Asian Crisis (1997): -60% in 6 months
   - 2008 GFC: -50% in 12 months
   - Apply to current portfolio → survival check
5. Monte Carlo probability of ruin:
   - 10,000 simulations of 252 trading days
   - Report: P(ruin) = P(drawdown > 20%)
6. Automatic de-risking ladder:
   - DD 0-5%: normal (100% risk budget)
   - DD 5-10%: reduce to 75% risk budget
   - DD 10-15%: reduce to 50% risk budget
   - DD > 15%: stop trading (kill switch)
7. Backtest pada IHSG 2020-2026 dengan BEI costs

CONSTRAINTS:
- numpy <2.0, scipy
- Backtest harus menggunakan walk-forward (no look-ahead)
- Semua risk metrics harus realistis (bukan hypothetical)

OUTPUT: Updated risk_manager.py, portfolio_risk.py, stress test code, backtest, tests
```

### Prompt 5.2: Advanced Backtesting & Validation

```
Anda adalah quantitative researcher. Perkuat validasi model dan strategi trading.

KONTEKS:
- Saat ini: walk-forward CV, purged k-fold, CPCV, IC/Rank IC/ICIR, Alphalens, Pyfolio
- Gap: deflated Sharpe, multi-regime backtest, shadow mode, White's Reality Check
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 6

TUGAS:
1. Deflated Sharpe ratio (src/validation.py enhancement):
   - Correct for multiple testing bias
   - Formula: SR_deflated = SR - SR_0 * (1 - skew * SR + (kurt-1)/4 * SR^2)
   - Report: original Sharpe, deflated Sharpe, p-value
2. Multi-regime backtesting:
   - Split backtest period by regime (bull/bear/sideways/crisis)
   - Report performance per regime
   - Identify regime-dependent strategies
3. Bootstrap performance comparison:
   - Challenger vs champion model
   - 10,000 bootstrap samples of returns
   - Report: P(challenger > champion)
4. Shadow mode evaluation:
   - Run new model in parallel with production model
   - Log predictions tanpa eksekusi trading
   - Compare: directional accuracy, Sharpe, max DD
   - Minimum 30 days shadow period sebelum promote
5. Capital-capped A/B testing:
   - Allocate 10% capital to challenger, 90% to champion
   - Gradual increase: 10% → 25% → 50% → 100% jika outperform
   - Rollback jika underperform selama 2 minggu
6. White's Reality Check:
   - Multiple strategy comparison
   - Bootstrap reality check untuk data snooping bias
   - Report: which strategies are statistically significant
7. Minimum effect size: 0.2-0.3 Sharpe improvement untuk promote
8. Rollback procedures: test sebelum deployment

CONSTRAINTS:
- numpy <2.0, scipy, scikit-learn
- Walk-forward: no look-ahead bias
- Semua tests harus reproducible (random seed)

OUTPUT: Updated validation.py, src/shadow_mode.py, src/bootstrap_test.py, tests
```

---

## Sprint 6: MLOps & Monitoring

### Prompt 6.1: Prometheus + Grafana + 3-Level Monitoring

```
Anda adalah MLOps engineer. Bangun production-grade monitoring stack.

KONTEKS:
- Saat ini: MLflow tracking, PSI+KS-test drift, retrain scheduler, Docker, GitHub Actions CI/CD
- Gap: 3-level monitoring, Prometheus+Grafana, Evidently AI, automated alerting
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 7

TUGAS:
1. Prometheus instrumentation (src/monitoring.py):
   - prometheus_client library
   - Custom metrics:
     - Counter: predict_requests_total, predict_errors_total
     - Histogram: predict_latency_seconds (p50/p95/p99)
     - Gauge: model_directional_accuracy, model_mape
     - Gauge: drift_score_psi, drift_score_ks
     - Gauge: data_freshness_seconds
     - Gauge: active_positions, portfolio_var
     - Gauge: api_active_connections
   - /metrics endpoint di FastAPI
2. Grafana dashboards (grafana/ directory):
   - API Overview: RPS, latency p50/p95/p99, error rate, uptime
   - Model Performance: directional accuracy over time, MAPE, IC/Rank IC
   - Data Quality: freshness, completeness, anomaly count
   - Drift Monitoring: PSI per feature, KS test p-value, drift score gauge
   - Portfolio: P&L, VaR, positions, exposure
   - Alerts: drift > 0.2, accuracy < 50%, latency > 500ms, data stale > 1h
3. 3-level monitoring response:
   - Level 1 (Warning): drift > 0.1 → investigation, log to Telegram
   - Level 2 (Control): drift > 0.2 OR accuracy < 50% → review, auto-retrain
   - Level 3 (Suspension): drift > 0.3 OR accuracy < 40% → withdraw model, fallback
4. SHAP-based feature monitoring:
   - Track feature importance shifts over time
   - Alert jika top-5 features change significantly
5. Concept drift: ADWIN + DDM pada prediction error
6. 4-quadrant diagnostic: drift × performance decay → response matrix
7. Automated alerting: Telegram + PagerDuty webhook
8. Docker Compose: prometheus + grafana + alertmanager services

CONSTRAINTS:
- prometheus-client Python library
- Grafana dashboard JSON (version-controlled)
- Docker Compose untuk local + production

OUTPUT: src/monitoring.py, grafana/ dashboards, prometheus.yml, docker-compose update, tests
```

### Prompt 6.2: Data Lineage & Feature Store

```
Anda adalah data engineer. Implementasikan data lineage tracking dan feature store.

KONTEKS:
- Saat ini: data_pipeline.py (quality monitoring), parquet persistence
- Gap: DVC data versioning, Feast feature store, run manifests
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 7 (item 6: data lineage)
- Lihat docs/TEAM_ROADMAP.md Data Engineer role

TUGAS:
1. Data versioning (DVC integration):
   - dvc init di project root
   - Track raw data (CSV/parquet) dengan DVC
   - .dvc files untuk raw market data, FRED data, RSS snapshots
   - dvc push/pull ke S3/MinIO remote
2. Feature store (src/feature_store.py):
   - Feast-compatible feature definitions
   - Point-in-time correctness: features hanya dari data available at prediction time
   - Online store: Redis (low-latency serving)
   - Offline store: PostgreSQL/Parquet (training)
   - Feature retrieval: get_features(ticker, date, feature_list) → DataFrame
   - Consistent training/serving: no training-serving skew
3. Run manifests:
   - Setiap pipeline run: log timestamp, data version, code version, parameters, output
   - JSON manifest saved ke manifests/ directory
   - Reproducibility: re-run dengan same manifest → same result
4. Data lineage tracking (enhance data_pipeline.py):
   - Source: yfinance, FRED, Alpha Vantage, Finnhub, RSS
   - Transformations: list of applied transformations
   - Quality status: pass/fail per check
   - Timestamps: fetch, process, store
   - Dependency graph: which features depend on which data sources
5. Model card generation:
   - Auto-generate model card setiap training run
   - Include: model type, features, hyperparameters, performance metrics, data version
   - Save ke MLflow

CONSTRAINTS:
- dvc, feast (optional, custom implementation acceptable)
- Redis untuk online feature store
- Parquet untuk offline
- numpy <2.0

OUTPUT: src/feature_store.py, dvc config, manifest system, updated data_pipeline.py, tests
```

---

## Sprint 7: Backend Enhancement & Security

### Prompt 7.1: JWT/OAuth2 Authentication & Multi-User

```
Anda adalah security engineer. Implementasikan authentication dan authorization.

KONTEKS:
- Saat ini: tidak ada auth, API open access
- Backend: FastAPI (src/api.py, 10 endpoints)
- Target: JWT/OAuth2, multi-user, role-based access control
- Lihat docs/TEAM_ROADMAP.md Backend Engineer role

TUGAS:
1. Buat src/auth.py — Authentication system:
   - JWT token generation + validation (python-jose)
   - OAuth2 password flow (FastAPI OAuth2PasswordBearer)
   - Password hashing (passlib + bcrypt)
   - Token refresh mechanism
   - Token expiration: access 30min, refresh 7 days
2. User management (src/user_manager.py):
   - User registration: username, email, password, role
   - User database table (PostgreSQL/SQLite)
   - Role-based access control: admin, trader, viewer
   - Rate limiting per user (slowapi)
3. API endpoints:
   - POST /auth/register — create new user
   - POST /auth/login — login, return JWT
   - POST /auth/refresh — refresh token
   - GET /auth/me — current user info
   - PUT /auth/me — update profile
4. Protect existing endpoints:
   - admin: all endpoints (CRUD, train, deploy)
   - trader: predict, backtest, portfolio, scanner
   - viewer: GET endpoints only (read-only)
5. API key support: alternative auth untuk programmatic access
6. CORS configuration: restrict to known origins
7. Input validation: Pydantic v2 untuk semua request bodies
8. SQL injection prevention: parameterized queries
9. Rate limiting: 100 req/min per user, 1000 req/min per API key

CONSTRAINTS:
- python-jose[cryptography], passlib[bcrypt], slowapi
- Pydantic v2
- Backward compatible: jika auth disabled (config), API tetap open

OUTPUT: src/auth.py, src/user_manager.py, updated api.py, tests
```

### Prompt 7.2: Data Encryption & Regulatory Compliance

```
Anda adalah compliance officer dan security engineer. Implementasikan data encryption dan compliance.

KONTEKS:
- Saat ini: compliance.py (OJK disclaimers, audit trail), tidak ada encryption
- Target: AES-256 at rest, TLS 1.3 in transit, UU PDP compliance, SR 11-7
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 12

TUGAS:
1. Data encryption at rest (src/encryption.py):
   - AES-256-GCM untuk sensitive data (user credentials, API keys, broker credentials)
   - Encryption key management: environment variable atau HashiCorp Vault
   - Encrypt database columns: user.password_hash, broker_api_key, telegram_token
   - File encryption untuk sensitive config files
2. TLS in transit:
   - HTTPS untuk API (uvicorn --ssl-keyfile --ssl-certfile)
   - WebSocket Secure (wss://)
   - Database connection SSL
   - Redis connection SSL (jika remote)
3. User consent management (src/consent.py):
   - Opt-in/opt-out untuk data collection
   - Right to erasure: delete user data
   - Data retention policy: auto-delete after 5 years
   - Consent log: track all consent changes
4. Data breach notification:
   - Detect breach (anomalous access patterns)
   - Auto-notify admin via Telegram + email
   - OJK notification template (72 jam deadline)
5. Model governance (src/model_governance.py):
   - Document model decisions (model card)
   - Version control: setiap model change di log
   - Approval workflow: staging → review → production
   - Change management: diff report sebelum/after
6. SR 11-7 model validation:
   - Ongoing monitoring: performance, drift, stability
   - Change management: documented approval process
   - Independent review: periodic validation
7. Audit trail enhancement (compliance.py):
   - Immutable log (append-only)
   - Hash chain: setiap entry chained ke previous hash
   - Tamper detection: verify hash chain integrity
   - Retention: 5 years (OJK requirement)
8. Disclaimer system: mandatory di semua output (API, UI, reports)

CONSTRAINTS:
- cryptography library (Python)
- Compliance dengan OJK, UU PDP, BEI rules
- Audit trail: append-only, hash-chained

OUTPUT: src/encryption.py, src/consent.py, src/model_governance.py, updated compliance.py, tests
```

### Prompt 7.3: Broker API Integration & Auto-Trading

```
Anda adalah trading systems engineer. Integrasikan broker IDX untuk auto-trading.

KONTEKS:
- Saat ini: broker_sim.py (simulator dengan komisi, slippage, short selling)
- Target: live broker API (Mirae Asset/IPOT/BNI Sekuritas)
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 9
- Lihat docs/TEAM_ROADMAP.md Quant Finance role

TUGAS:
1. Broker abstraction layer (src/broker_interface.py):
   - Abstract base class: BrokerInterface
   - Methods: connect, disconnect, get_balance, get_positions, submit_order, modify_order, cancel_order, get_order_status
   - BrokerSimulator (existing) implements BrokerInterface
   - LiveBroker implements BrokerInterface (untuk real broker)
2. Order Management System (src/oms.py):
   - Order lifecycle: PENDING → SUBMITTED → PARTIAL_FILL → FILLED / REJECTED / CANCELLED
   - Order types: MARKET, LIMIT, STOP, STOP_LIMIT
   - Order modification: amend quantity, price
   - Order cancellation
   - Status tracking dengan timestamp
3. Pre-trade validation (src/pre_trade.py):
   - Auto-rejection check (BEI rules: price limit, volume limit)
   - Circuit breaker check (market halt status)
   - Position limit check (max 20% per ticker)
   - Margin sufficiency check
   - Trading session check (market open/closed)
   - Risk check: portfolio VaR after trade < 5% capital
4. Post-trade processing:
   - Fill confirmation: update position, P&L
   - Settlement T+2 tracking
   - Reconciliation: internal state vs broker statement (daily)
5. Paper trading mode:
   - Live data + simulated execution
   - Full risk management active
   - Log semua orders untuk audit
6. Auto-trading engine (src/auto_trader.py):
   - Signal → pre-trade check → order submission → fill tracking → SL/TP placement
   - Configurable: auto_trade=True/False, max_positions, max_daily_trades
   - Kill switch: stop all trading jika DD > 15%
   - Audit trail: log semua order events untuk OJK compliance
7. Error handling:
   - Timeout: retry 3x dengan backoff
   - Rejection: log reason, notify user
   - Partial fill: track filled qty, cancel remainder
   - Connection loss: reconnect, sync state

CONSTRAINTS:
- Broker API: mulai dengan paper trading mode (no real money)
- Audit trail: immutable, hash-chained
- Kill switch: always active
- OJK compliance: semua orders logged

OUTPUT: src/broker_interface.py, src/oms.py, src/pre_trade.py, src/auto_trader.py, tests
```

---

## Sprint 8: Testing, E2E & Deployment

### Prompt 8.1: E2E Tests with Playwright

```
Anda adalah QA engineer. Implementasikan E2E tests dengan Playwright untuk Next.js dashboard.

KONTEKS:
- Frontend: Next.js 14+ dashboard (dibuat di Sprint 3)
- Backend: FastAPI (src/api.py)
- Saat ini: 366 unit tests, tidak ada E2E
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 14

TUGAS:
1. Playwright setup (frontend/e2e/ directory):
   - @playwright/test configuration
   - Base URL: http://localhost:3000
   - Auto-start dev server sebelum tests
   - Browser: Chromium, Firefox, WebKit
2. E2E test scenarios (minimum 15):
   - Dashboard: load, verify market summary, verify signal cards
   - Prediction: search ticker → view prediction → verify AI Score, entry/SL/TP
   - Simulation: configure → run simulation → verify equity curve, trade log
   - Portfolio: select tickers → run optimization → verify comparison table
   - Risk: view VaR, CVaR, stress test results
   - AI Agent: run briefing → verify multi-agent report, bull vs bear
   - Backtest: configure → run → verify tear sheet
   - Screener: set filters → verify results table
   - Sentiment: view news feed, FinBERT scores, Fear & Greed
   - Patterns: view candlestick patterns, chart patterns
   - NL Scanner: type query → verify results
   - Auth: register → login → access protected page → logout
   - Settings: update risk parameters → verify saved
   - Theme: toggle dark/light → verify persisted
   - Responsive: verify tablet layout
3. Visual regression testing:
   - Screenshot comparison untuk setiap page
   - Baseline images di e2e/baselines/
   - Alert jika UI changes detected
4. API mocking untuk deterministic tests:
   - Mock FastAPI responses untuk consistent test data
   - MSW (Mock Service Worker) atau Playwright route interception
5. CI integration: GitHub Actions workflow untuk E2E

CONSTRAINTS:
- @playwright/test
- Headless mode untuk CI
- Test timeout: 60s per test
- Parallel execution

OUTPUT: frontend/e2e/ tests, playwright.config.ts, CI workflow, baseline screenshots
```

### Prompt 8.2: Load Testing, Chaos Engineering & Mutation Testing

```
Anda adalah QA engineer. Implementasikan load testing, chaos engineering, dan mutation testing.

KONTEKS:
- Saat ini: 366 unit tests, GitHub Actions CI/CD
- Gap: load testing (k6/Locust), chaos engineering, mutation testing (mutmut)
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 14

TUGAS:
1. Load testing (tests/load/ directory):
   - k6 scripts untuk API:
     - GET /api/predict/{ticker}: 1000 RPS target, p95 < 200ms
     - POST /api/scanner: 100 concurrent, p95 < 5s
     - WebSocket: 100 concurrent connections, 10 min duration
   - Locust scripts untuk dashboard:
     - 100 concurrent users browsing pages
     - Simulate user journey: dashboard → predict → backtest → portfolio
   - Report: RPS, latency p50/p95/p99, error rate, throughput
2. Model regression tests (tests/test_model_regression.py):
   - Baseline: save current model performance metrics
   - New model ≥ old model: directional accuracy, Sharpe, max DD
   - Alert jika performance drop > 5%
   - Smoke test: model loading + inference < 5s
3. Data quality tests (tests/test_data_quality.py):
   - Freshness: data tidak stale > 1 day (weekday)
   - Completeness: no missing OHLCV > 5% per ticker
   - Anomaly: no impossible prices (negative, > 10x previous)
   - Uniqueness: no duplicate (ticker, date) rows
   - Schema: correct types, ranges
4. Chaos engineering (tests/chaos/):
   - yfinance down: retry + fallback ke Alpha Vantage
   - Database corrupt: backup restore test
   - Model file missing: auto-retrain trigger
   - Redis down: graceful degradation (direct DB)
   - GPU failure: fallback ke CPU
   - WebSocket disconnect: auto-reconnect
5. Mutation testing (mutmut):
   - Run mutmut pada src/ core modules
   - Target: mutation score > 70%
   - Identify weak spots in test coverage
6. Security tests:
   - SQL injection: parameterized queries verification
   - XSS: input sanitization verification
   - Auth bypass: protected endpoint access without token
   - Rate limit: exceed limit → 429 response
7. Performance tests:
   - API p95 < 200ms
   - Prediction < 5s
   - Training < 30min (GPU)
   - Dashboard load < 2s
8. CI integration: GitHub Actions workflow untuk semua test types

CONSTRAINTS:
- k6 (Go-based), locust, mutmut, pytest
- Tests harus runnable di CI (GitHub Actions)
- Mutation testing: time-boxed (max 30 min)

OUTPUT: tests/load/, tests/chaos/, tests/test_model_regression.py, tests/test_data_quality.py, CI workflows
```

### Prompt 8.3: Airflow ETL & Kubernetes Deployment

```
Anda adalah DevOps/MLOps engineer. Implementasikan Airflow ETL pipeline dan K3s deployment.

KONTEKS:
- Saat ini: Docker + docker-compose, GitHub Actions CI/CD, cron job
- Gap: Airflow ETL, K3s/K8s deployment, Helm charts
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 7, 13
- Lihat docs/TEAM_ROADMAP.md DevOps role

TUGAS:
1. Airflow ETL pipeline (airflow/ directory):
   - DAG 1: daily_data_ingestion (06:00 WIB)
     - Fetch yfinance: 11 tickers + blue chips
     - Fetch FRED: macro data
     - Fetch RSS: news feeds
     - Validate: data quality checks
     - Load: PostgreSQL + Redis cache
     - Notify: Telegram success/failure
   - DAG 2: daily_prediction (09:00 WIB)
     - Trigger: setelah DAG 1 success
     - Run prediction untuk all tickers
     - Save ke database
     - Send Telegram briefing
   - DAG 3: weekly_backtest (Saturday 06:00 WIB)
     - Walk-forward backtest
     - Generate tear sheet
     - Compare vs previous week
   - DAG 4: drift_check (18:00 WIB)
     - PSI + KS-test
     - If drift > 0.2: trigger retrain
   - DAG 5: model_retrain (triggered by DAG 4)
     - Train new model
     - Shadow mode evaluation
     - Promote jika outperform
   - Task dependencies: XCom untuk data passing
   - Retry: 3x dengan exponential backoff
   - Alert: Slack/Telegram pada failure
2. K3s deployment (k8s/ directory):
   - Namespace: saham-prod, saham-staging
   - Deployments:
     - fastapi: 2 replicas, resource limits (1 CPU, 1GB RAM)
     - celery-worker: 2 replicas, resource limits (2 CPU, 2GB RAM)
     - celery-beat: 1 replica
     - nextjs: 2 replicas
     - mlflow: 1 replica
   - Services: ClusterIP untuk internal, LoadBalancer untuk API + dashboard
   - Ingress: TLS termination, routing
   - ConfigMaps: non-sensitive config
   - Secrets: API keys, database credentials (encrypted)
   - PersistentVolumes: PostgreSQL data, Redis data, model artifacts
   - Resource limits: CPU, memory, GPU (jika available)
   - Health checks: liveness + readiness probes
   - Auto-scaling: HPA berdasarkan CPU > 70%
3. Helm chart (helm/saham/):
   - values.yaml: configurable parameters
   - Chart.yaml: versioning
   - templates/: deployment, service, ingress, configmap, secret
   - values-prod.yaml, values-staging.yaml
4. Terraform (terraform/ directory):
   - VPS provisioning (Oracle Cloud / AWS)
   - PostgreSQL managed instance
   - Redis managed instance
   - S3 bucket untuk backups
   - DNS + CDN
5. Backup & disaster recovery:
   - Daily PostgreSQL backup (pg_dump → S3)
   - Model artifacts backup ke S3
   - Point-in-time recovery untuk database
   - Runbook untuk incident response
6. Blue-green deployment:
   - Staging → production dengan zero downtime
   - Auto-rollback jika health check fail

CONSTRAINTS:
- K3s (lightweight Kubernetes) untuk single-node atau small cluster
- Airflow 2.x dengan TaskFlow API
- Helm 3.x
- Terraform untuk IaC
- Docker images harus multi-arch (amd64)

OUTPUT: airflow/ DAGs, k8s/ manifests, helm/ chart, terraform/, backup scripts, CI/CD update
```

---

## Sprint 9: AI/LLM Agent Enhancement

### Prompt 9.1: Multi-LLM Provider & Checkpoint System

```
Anda adalah AI agent architect. Tingkatkan multi-agent LLM framework.

KONTEKS:
- Saat ini: 4 agents (Market Analyst, Risk Manager, News Analyst, Portfolio Advisor), Bull vs Bear, ReAct, RAG, Trading Memory
- Gap: checkpoint system, multi-LLM provider, interactive chat, reflection protocol
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 5
- Lihat docs/COMPETITIVE_ANALYSIS.md (TradingAgents, AlphaSense)

TUGAS:
1. Multi-LLM provider support (src/llm_provider.py):
   - Unified interface: LLMProvider.generate(prompt, **kwargs) → response
   - Providers: OpenAI, DeepSeek, Ollama (local), Anthropic, Gemini
   - Auto-fallback: primary → secondary → tertiary
   - Cost tracking: log token usage + cost per request
   - Config: LLM_PROVIDER=openai, LLM_MODEL=gpt-4, LLM_FALLBACK=ollama
   - Ollama: auto-detect local models (llama3, mistral, qwen)
2. Checkpoint system (src/checkpoint.py):
   - Save agent state setiap step: JSON snapshot ke disk/Redis
   - Crash recovery: resume dari checkpoint terakhir
   - Checkpoint format: {step, agent_name, state, timestamp, partial_output}
   - Auto-cleanup: delete checkpoints older than 24h
   - Resume command: python run_analysis.py --resume {checkpoint_id}
3. Interactive chat agent (src/chat_agent.py):
   - Follow-up questions tentang prediksi
   - Context: previous prediction, current market data, agent reports
   - Streaming response (SSE atau WebSocket)
   - Chat history persistence
   - API endpoint: POST /api/chat { message, context } → response
   - Frontend: chat widget di prediction page
4. Multi-mode AI (enhance multi_mode_research.py):
   - Fast (10s): quick analysis, 1 agent, key signals only
   - Standard (30s): 2 agents, technical + sentiment
   - Deep (5min): 4 agents, full analysis, bull vs bear
   - Research (15min): 4 agents + RAG + historical comparison + reflection
   - User selects mode via UI/API
5. Reflection protocol (src/reflection.py):
   - Agent evaluates own output: completeness, consistency, bias check
   - Self-critique: "What did I miss? What assumptions are risky?"
   - Revision: improve output berdasarkan self-critique
   - Log reflection untuk future improvement
6. In-line citations (enhance rag_system.py):
   - Setiap claim dalam AI report → link ke source document
   - Source: news article URL, data point, indicator value
   - Frontend: clickable citations → expand source
7. Workflow-driven agents:
   - End-to-end process: data → analysis → signal → risk → report → notification
   - Agent handles entire workflow, bukan satu step
   - State machine: track workflow progress

CONSTRAINTS:
- Python 3.12, langchain (optional), ollama, openai, anthropic
- Ollama: local inference, no API cost
- Checkpoint: JSON format, Redis-backed
- Response time: Fast < 10s, Standard < 30s, Deep < 5min

OUTPUT: src/llm_provider.py, src/checkpoint.py, src/chat_agent.py, src/reflection.py, tests
```

### Prompt 9.2: Sentiment Pipeline Enhancement

```
Anda adalah NLP engineer. Kembangkan sentiment analysis pipeline komprehensif.

KONTEKS:
- Saat ini: FinBERT, RSS (Kontan, Bisnis, CNBC, Reuters, Bloomberg), Fear & Greed, social sentiment
- Gap: multi-language, NER, event detection, LLM summarization, sentiment→ML integration
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 10

TUGAS:
1. Multi-language sentiment (src/sentiment_pipeline.py enhancement):
   - Bahasa Indonesia: IndoBERT atau mBERT fine-tuned untuk financial sentiment
   - English: FinBERT (existing)
   - Auto-detect language → route ke appropriate model
   - Fallback: lexicon-based (VADER untuk EN, InSet untuk ID)
2. News aggregation pipeline:
   - RSS → scrape → clean → language detect → sentiment → score → database
   - Deduplication: similar articles (TF-IDF cosine > 0.85) → keep earliest
   - Real-time: poll RSS setiap 5 menit saat market open
3. Entity recognition (src/ner.py):
   - Ticker extraction: "BBCA naik 2%" → ticker=BBCA, direction=up, magnitude=2%
   - Sector extraction: "sektor perbankan melemah" → sector=banking, direction=down
   - Price level: "support di 7.500" → level=support, price=7500
   - Event type: earnings, IPO, split, dividend, regulatory, M&A
   - Use spaCy (en) + custom NER untuk Indonesian financial terms
4. Sentiment trend tracking:
   - 7-day rolling sentiment per ticker
   - Sentiment momentum: change rate of sentiment
   - Sentiment divergence vs price: price up but sentiment down → alert
   - Sentiment extreme: z-score > 2 → contrarian signal
5. Event detection (src/event_detector.py):
   - Earnings: detect "laporan keuangan", "earnings", "labu bersih"
   - IPO: detect "IPO", "penawaran umum perdana"
   - Split: detect "stock split", "pemecahan saham"
   - Dividend: detect "dividen", "payout"
   - Regulatory: detect "OJK", "BEI", "aturan", "regulasi"
   - M&A: detect "akuisisi", "merger", "pengambilalihan"
6. LLM summarization (src/news_summary.py):
   - Daily news digest: LLM summarize top 10 news per ticker
   - Sentiment summary: "Market sentiment hari ini: bullish pada perbankan, bearish pada properti"
   - Auto-generate setiap 17:00 WIB
   - Send via Telegram + save ke database
7. Sentiment → ML feature integration:
   - Sentiment score sebagai feature di preprocessor
   - Sentiment momentum sebagai feature
   - Sentiment divergence sebagai feature
   - Event flags sebagai categorical features

CONSTRAINTS:
- transformers (HuggingFace), spacy, nltk
- Ollama untuk LLM summarization (local, no API cost)
- Indonesian NLP: IndoNLU models atau mBERT
- numpy <2.0

OUTPUT: Updated sentiment_pipeline.py, src/ner.py, src/event_detector.py, src/news_summary.py, tests
```

---

## Sprint 10: Portfolio Optimization & Quant Finance

### Prompt 10.1: Dynamic Portfolio & Factor Model Enhancement

```
Anda adalah quantitative portfolio manager. Kembangkan portfolio optimization dan factor model.

KONTEKS:
- Saat ini: 5 portfolio methods (Markowitz, BL, Risk Parity, HRP, CVaR), CAPM
- Gap: dynamic allocation, Kelly-optimal, factor tilting, Fama-French 5-factor, DCC-GARCH
- Lihat docs/BATCH_ANALYSIS_PROMPTS.md Prompt 11

TUGAS:
1. Dynamic allocation (src/portfolio.py enhancement):
   - Adjust weights berdasarkan regime:
     - Bull: momentum tilt, higher equity allocation
     - Bear: defensive tilt, lower equity, more cash/bonds
     - Crisis: capital preservation, minimal allocation
     - Sideways: mean-reversion tilt, balanced
   - Regime probability weighting: smooth transition
2. Kelly-optimal portfolio (src/kelly_portfolio.py):
   - Maximize expected log growth rate
   - Constraint: max single position 20%, max leverage 1x
   - Multi-asset Kelly: solve constrained optimization
   - Compare vs Markowitz dan Risk Parity
3. Factor tilting (src/factor_tilt.py):
   - Momentum tilt: overweight high 12-1 month momentum
   - Value tilt: overweight low P/B, P/E
   - Quality tilt: overweight high ROE, low debt
   - Combine tilts: multi-factor portfolio
4. Transaction-aware rebalancing (src/rebalance.py):
   - Minimize turnover: only rebalance jika drift > 5%
   - Transaction cost model: BEI commission + slippage
   - Optimal rebalance frequency: daily vs weekly vs monthly
   - Trade-off: tracking error vs transaction cost
5. Fama-French 5-factor model (src/fama_french.py):
   - Market factor (beta terhadap IHSG)
   - Size factor (small vs large cap premium)
   - Value factor (PB/PE ratio premium)
   - Profitability factor (ROE/ROA premium)
   - Investment factor (asset growth)
   - Adaptasi untuk IDX: foreign ownership factor
   - Factor regression: identify factor exposures per ticker
6. DCC-GARCH (src/dcc_garch.py):
   - Dynamic Conditional Correlation
   - Time-varying correlation matrix
   - Better portfolio risk estimation vs static correlation
   - Forecast correlation 1-step ahead
7. Multi-objective optimization:
   - Max return + min risk + min turnover
   - Pareto frontier: efficient set of solutions
   - User selects preference: aggressive, moderate, conservative
8. Risk decomposition:
   - Factor exposure: how much risk from each factor
   - Sector exposure: how much risk from each sector
   - Country exposure: domestic vs global
9. Walk-forward rebalancing backtest dengan BEI costs

CONSTRAINTS:
- numpy <2.0, scipy, scikit-learn, arch (untuk GARCH)
- Optimization: scipy.optimize atau cvxpy
- Backtest: walk-forward, no look-ahead

OUTPUT: Updated portfolio.py, src/kelly_portfolio.py, src/factor_tilt.py, src/fama_french.py, src/dcc_garch.py, tests
```

---

## Dependency Graph

```
Sprint 1 (DB & Caching)
  ├─→ Sprint 2 (Performance) — needs Redis cache
  ├─→ Sprint 3 (Next.js) — needs async API
  │    └─→ Sprint 8.1 (E2E Tests) — needs frontend
  ├─→ Sprint 6 (MLOps) — needs PostgreSQL + Redis
  └─→ Sprint 7 (Backend & Security)
       ├─→ Sprint 8.3 (K8s Deploy) — needs all services
       └─→ Sprint 7.3 (Auto-Trading) — needs auth + OMS

Sprint 4 (ML Enhancement) — independent, can run parallel
Sprint 5 (Risk & Backtesting) — independent, can run parallel
Sprint 9 (AI Agents) — independent, can run parallel
Sprint 10 (Portfolio & Quant) — independent, can run parallel
```

## Parallel Execution Strategy

| Track | Sprints | Dependency |
|-------|---------|------------|
| **Track A: Infrastructure** | 1 → 2 → 3 → 7 → 8 | Sequential |
| **Track B: ML/Quant** | 4 → 5 → 10 | Sequential, parallel with Track A |
| **Track C: AI Agents** | 9 | Parallel with Track A & B |
| **Merge** | 8 (Testing + Deploy) | After Track A + B complete |

## Estimated Timeline (AI-Assisted Development)

| Sprint | Effort | Dependencies |
|--------|--------|-------------|
| Sprint 1 | 2-3 minggu | None |
| Sprint 2 | 1-2 minggu | Sprint 1 |
| Sprint 3 | 4-6 minggu | Sprint 1 |
| Sprint 4 | 3-4 minggu | None (parallel) |
| Sprint 5 | 2-3 minggu | Sprint 4 |
| Sprint 6 | 2-3 minggu | Sprint 1 |
| Sprint 7 | 3-4 minggu | Sprint 1, 3 |
| Sprint 8 | 2-3 minggu | Sprint 3, 7 |
| Sprint 9 | 2-3 minggu | None (parallel) |
| Sprint 10 | 2-3 minggu | Sprint 4 |
| **Total** | **~20-28 minggu** | **3 tracks parallel** |

## Referensi Riset Internet

1. **TimescaleDB + FastAPI** — Neon guide, timescaledb-python SDK (PyPI)
2. **Celery + FastAPI + Redis** — Production patterns (hassanr.com, dev.to, uguraslim.com)
3. **Next.js 14 + TradingView** — CryptoTraderPro, crypto-stock-lens, trade-analytics (GitHub)
4. **Prometheus + Grafana + ML** — Fast-Api-Prometheus-Grafana, ml-platform-demo (GitHub)
5. **Airflow ETL** — stock_market_pipeline, Financial-Time-Series-ETL (GitHub)
6. **K3s + FastAPI** — ML deployment guides (Medium, GitHub)
7. **JWT/OAuth2 FastAPI** — FastAPI official docs, Medium guides 2025
8. **Polars vs Pandas** — Quant migration diary, benchmarks 2025-2026
9. **CatBoost + Stacking** — quantitative-ml-dl-ensemble-algotrading (Sharpe 1.165)
10. **Bayesian Ensembling** — arXiv:2505.15638 (Online Bayesian Stacking)
11. **IDX Broker API** — Invezgo Python SDK, Saftrade, stockai (GitHub)
12. **Playwright + Next.js** — Next.js official docs, deviqa.com guide
13. **Evidently AI + Monitoring** — python.elitedev.in MLOps guide
14. **DCC-GARCH** — arch Python library documentation
15. **Fama-French** — Adaptasi untuk emerging markets (IDX)

## File Referensi di docs/

| File | Relevansi |
|------|-----------|
| `ACUAN_PROYEK.md` | Spesifikasi lengkap, data strategy, arsitektur target |
| `ARCHITECTURE.md` | 9 layer arsitektur, 85+ modul, DB schema, config |
| `TEAM_ROADMAP.md` | 10 peran, gap analysis, roadmap 5 fase, budget |
| `BATCH_ANALYSIS_PROMPTS.md` | 14 prompt analisis + status implementasi |
| `COMPETITIVE_ANALYSIS.md` | 14 kompetitor, fitur yang dapat diadopsi |
| `DEVELOPMENT.md` | Panduan development, setup, testing |
| `UI_DESIGN.md` | Desain UI, layout, components |
| `PENGETAHUAN_DASAR_PASAR_MODAL.md` | Knowledge base pasar modal Indonesia |
| `CONTRIBUTING.md` | Contributing guidelines |

---

## Catatan untuk AI

1. **Baca docs/ sebelum mulai** — konteks lengkap ada di sana
2. **GPU constraint** — 2x GTX 1050 Ti (4GB, SM 6.1), tidak semua GPU features supported
3. **numpy <2.0** — wajib untuk scipy/shap compatibility
4. **Backward compatible** — SQLite fallback jika PostgreSQL unavailable
5. **366 tests must pass** — jangan break existing tests
6. **OJK compliance** — semua output harus ada disclaimer
7. **Point-in-time correctness** — no look-ahead bias in features/backtest
8. **Incremental adoption** — Polars alongside pandas, PostgreSQL alongside SQLite
9. **Production safety** — kill switch, audit trail, graceful degradation
10. **Indonesian market focus** — BEI rules, OJK compliance, IDX trading hours
