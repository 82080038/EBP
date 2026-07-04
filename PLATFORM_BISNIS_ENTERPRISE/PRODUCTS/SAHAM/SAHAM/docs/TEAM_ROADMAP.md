# Tim Programmer yang Dibutuhkan untuk Menyempurnakan Aplikasi

> **Versi 2.0** — Diperbarui berdasarkan riset internet mendalam (Juni 2025) mengenai
> MLOps for finance, AI/LLM agents, time-series transformers, Indonesia stock API,
> broker integration, dan praktik terbaik industri kuantitatif.

Berdasarkan analisa mendalam aplikasi Proyeksi Pasar Saham Global dan riset internet
terhadap state-of-the-art platform kuantitatif 2025, berikut **10 peran** programmer
yang dibutuhkan untuk menjadikan aplikasi ini sempurna.

---

## 1. Machine Learning Engineer / Data Scientist

**Mengapa:** Inti aplikasi ini adalah prediksi. Model sudah berkembang dari
RF + XGBoost menjadi ensemble lengkap (RF + XGBoost + LightGBM + PatchTST/TFT dengan GPU).

**Tanggung jawab:**
- **Hyperparameter tuning** — ✅ Terimplementasi: Optuna TPE Bayesian optimization (`hyperopt.py`)
- **Walk-forward optimization** — ✅ Terimplementasi: walk-forward CV, purged k-fold (`validation.py`)
- **Model transformer state-of-the-art** — berdasarkan riset 2025:
  - **PatchTST** ("A Time Series is Worth 64 Words") — patch tokenization untuk
    long-horizon forecasting, 21% reduction MSE vs transformer sebelumnya
  - **Temporal Fusion Transformer (TFT)** — Google Research, multi-horizon dengan
    static & dynamic covariates, interpretable attention weights
  - **LPatchTST** — hybrid LSTM + PatchTST, mencapai Sharpe ratio 2.31 (vs 0.76
    PatchTST murni) dengan kontrol drawdown yang jauh lebih baik
  - **TFT-GNN** — hybrid TFT + Graph Neural Network untuk menangkap relasi
    inter-market (sangat relevan untuk aplikasi ini yang sudah punya intermarket analysis)
  - **LightGBM + CatBoost** — gradient boosting yang lebih cepat & akurat dari XGBoost
- **Transfer learning** — Parent-Child architecture (train parent model pada
  IHSG/INDEX, fine-tune pada blue chip individual: BBCA, BBRI, dll)
- **Feature selection otomatis** — ✅ Terimplementasi: SHAP, Boruta, correlation filter (`feature_selection.py`)
- **Regime-aware models** — model terpisah untuk Bull/Bear/Sideways
  (saat ini satu model untuk semua kondisi market)
- **Backtesting yang valid** — walk-forward, purged k-fold cross-validation,
  embargo periods untuk menghindari data leakage
- **Model interpretability** — SHAP, LIME, atau integrated gradients untuk
  menjelaskan mengapa model memberikan sinyal tertentu

---

## 2. AI/LLM Agent Engineer *(PERAN BARU)*

**Mengapa:** Industri 2025 sudah bergerak ke multi-agent LLM systems untuk analisis
finansial. Proyek open-source seperti TradingAgents (arXiv 2024) dan stock-agent-ops
membuktikan LLM agents bisa menghasilkan laporan setara analis Bloomberg.

**Tanggung jawab:**
- **Multi-agent system** — menggunakan LangGraph/LangChain untuk membangun:
  - **Performance Analyst Agent** — interpretasi hasil prediksi ML & indikator teknikal
  - **Market Expert Agent** — scrape berita terbaru, sentiment analysis real-time
  - **Report Generator Agent** — sintesis data menjadi laporan profesional (Markdown/PDF)
  - **Critic Agent** — review output untuk konsistensi & logika sebelum dikirim ke user
- **FinBERT integration** — model NLP pre-trained untuk finansial (ProsusAI/finBERT)
  untuk sentiment analysis berita & social media (Bloomberg, Reuters, Kontan, Bisnis)
- **RAG (Retrieval-Augmented Generation)** — vector database (Qdrant/Pinecone)
  untuk query historical analysis, laporan keuangan, dan event market
- **Semantic caching** — cache laporan yang mirip (95%+ similarity) untuk efisiensi
- **LLM inference** — Ollama (local) atau Groq API untuk inference cepat
- **Indonesian financial news scraping** — Kontan, Bisnis Indonesia, CNBC Indonesia,
  Kontan RSS, detikFinance
- **Auto-healing** — API mendeteksi model missing dan auto-trigger training

**Referensi:**
- TradingAgents: Multi-Agents LLM Financial Trading Framework (arXiv:2412.20138)
- stock-agent-ops: Transfer Learning LSTM + LangGraph agentic AI (GitHub)
- AWS: "Build an intelligent financial analysis agent with LangGraph"

---

## 3. Quantitative Finance Developer

**Mengapa:** Aplikasi ini tentang pasar saham, tapi belum ada strategi trading yang
proper dan risk management yang valid secara akademik.

**Tanggung jawab:**
- **Factor model** — Fama-French 3/5 factor, Carhart 4-factor, momentum factor
  untuk IHSG & blue chip Indonesia
- **Options analysis** — implied volatility, Greeks (Delta, Gamma, Theta, Vega),
  options strategies (straddle, strangle, iron condor)
- **Order execution logic** — slippage modeling, bid-ask spread, volume profile,
  market impact estimation
- **Multi-asset portfolio** — saat ini hanya simple Markowitz. Tambahkan:
  - **Black-Litterman model** — combine market views dengan equilibrium returns
  - **Risk parity** — equal risk contribution, bukan equal weight
  - **Hierarchical Risk Parity (HRP)** — clustering-based allocation
  - **Conditional Value at Risk (CVaR) optimization**
- **Event-driven backtesting engine** — saat ini hanya vector-based, perlu
  event-driven (bar-by-bar) dengan realistic constraints:
  - Commission & fees BEI (0.15% beli, 0.25% jual)
  - Slippage model berdasarkan volume & spread
  - Margin requirements & short selling constraints
  - Settlement T+2 untuk IDX
- **Market microstructure** — order book analysis, VWAP/TWAP execution algorithms
- **Alternative data integration** — foreign flow data BEI (Foreign Buy/Sell),
  insider trading disclosures, institutional holdings, block trades
- **Transaction cost analysis (TCA)** — pre-trade & post-trade cost estimation
- **Compliance rules** — auto-reject order yang melanggar OJK/BEI rules

---

## 4. Backend Engineer (Python/FastAPI)

**Mengapa:** Saat ini hanya Streamlit monolith, tidak ada REST API, tidak bisa
diakses oleh app lain, tidak scalable untuk multi-user.

**Tanggung jawab:**
- **REST API (FastAPI)** — endpoints untuk:
  - `POST /predict/{ticker}` — jalankan prediksi
  - `GET /market/realtime/{symbol}` — real-time price
  - `GET /trading/signals` — trading recommendations
  - `GET /portfolio/optimize` — portfolio optimization
  - `GET /risk/metrics/{ticker}` — risk metrics
  - `WS /ws/realtime` — WebSocket streaming
  - `GET /backtest/run` — trigger backtesting
  - `GET /reports/generate` — generate AI report
- **Authentication & authorization** — JWT/OAuth2, multi-user support,
  role-based access control (admin, trader, viewer)
- **Async task queue** — Celery/Redis atau Prefect untuk:
  - Job prediksi async (saat ini blocking, bisa 30+ detik)
  - Scheduled backtesting
  - Report generation via LLM agents
- **Database migration** — dari SQLite ke:
  - **PostgreSQL + TimescaleDB** untuk time-series data (harga, prediksi)
  - **Redis** untuk caching & real-time data
  - **Parquet** untuk historical data archive
- **Caching layer** — Redis untuk:
  - Market data cache (TTL 5s untuk real-time, 1h untuk historical)
  - Prediction results cache
  - Computed features cache
- **API rate limiting & security** —
  - Rate limiting per user/IP (slowapi atau custom middleware)
  - Input validation dengan Pydantic v2
  - SQL injection prevention
  - CORS configuration
- **WebSocket server** — real-time price streaming, prediction updates,
  portfolio alerts
- **gRPC** — untuk komunikasi internal antar microservices (jika dipisah)

---

## 5. Frontend Engineer (React/Next.js)

**Mengapa:** Streamlit terbatas — tidak bisa custom component yang kompleks,
tidak mobile-friendly, tidak real-time, performance buruk untuk data besar.

**Tanggung jawab:**
- **Dashboard React/Next.js 14+** — ganti Streamlit dengan:
  - Server-side rendering untuk SEO & performance
  - App router untuk nested layouts
  - TanStack Query untuk data fetching & caching
  - Zustand/Jotai untuk state management
- **Real-time charts** —
  - **TradingView Lightweight Charts** untuk candlestick & volume (industri standard)
  - **Highcharts** untuk interaktif complex charts
  - **D3.js** untuk custom visualisasi (efficient frontier, correlation network)
  - **Recharts** untuk simple dashboard widgets
- **Mobile app (React Native)** —
  - Push notification untuk sinyal BUY/SELL
  - Price alert configuration
  - Portfolio monitoring dari HP
  - Biometric authentication (fingerprint/face)
- **Interactive widgets** —
  - Drag-and-drop portfolio builder
  - Strategy builder (visual strategy composition)
  - Backtesting parameter configurator
  - Risk calculator dengan slider real-time
- **Design system** —
  - Dark/light theme dengan proper token system
  - Component library (shadcn/ui atau Ant Design)
  - Responsive layout (desktop, tablet, mobile)
  - Accessibility (WCAG 2.1 AA compliance)
- **PWA support** — offline capability, installable, background sync
- **Real-time updates** — WebSocket integration untuk live price & alerts

---

## 6. Data Engineer

**Mengapa:** Data hanya dari yfinance (sering rate-limited, 15 menit delay) dan
FRED (opsional). Tidak ada data pipeline, tidak ada data quality monitoring.

**Tanggung jawab:**
- **Data pipeline ETL** — Apache Airflow atau Prefect untuk:
  - Scheduled data ingestion (setiap 5 detik saat market open untuk real-time)
  - Incremental updates (hanya data baru, bukan full refresh)
  - Data backfilling untuk historical gaps
  - Error handling & retry logic
- **Multiple data sources** —
  - **Indonesia Stock API** (Invezgo, Kun Data, IDX Pulse, iTick) untuk
    real-time IDX data dengan WebSocket (<50ms latency)
  - **Alpha Vantage / Polygon.io** untuk US market data
  - **Bloomberg API / Refinitiv** (jika budget cukup) untuk institutional-grade data
  - **BEI data** — foreign flow, broker summary, index constituents
  - **RSS news feeds** — Kontan, Bisnis Indonesia, CNBC Indonesia, Reuters
  - **Social media** — Twitter/X sentiment, Reddit, Stockbit forum
  - **Macro data** — BI (Bank Indonesia) rate, inflasi, cadangan devisa
- **Data warehouse** —
  - **PostgreSQL + TimescaleDB** untuk time-series (harga, volume, indikator)
  - **ClickHouse** untuk analytics queries yang cepat pada large dataset
  - **MinIO/S3** untuk raw data archive (parquet format)
  - **Redis** untuk hot data (real-time prices)
- **Data quality monitoring** —
  - **Great Expectations** untuk validate schema, completeness, freshness
  - Detect missing data, outliers, stock split adjustments
  - Alert jika data gap > threshold
- **Real-time streaming** —
  - **Kafka** atau **Redis Streams** untuk live market data pipeline
  - Stream processing dengan Faust/Quix
- **Feature store** — **Feast** untuk:
  - Consistent features antara training & serving (no training-serving skew)
  - Reuse computed features (tidak recompute setiap run)
  - Point-in-time correctness untuk avoid look-ahead bias
- **Data versioning** — DVC untuk track dataset changes & reproducibility

---

## 7. DevOps / MLOps Engineer

**Mengapa:** Saat ini hanya GitHub Actions cron job, tidak ada Docker, tidak ada
monitoring, tidak ada model registry, tidak ada staging environment.

**Tanggung jawab:**
- **Docker & Kubernetes** —
  - Containerize seluruh aplikasi (API, worker, dashboard, DB)
  - K3s/K8s cluster untuk production deployment
  - Helm charts untuk package management
  - Auto-scaling berdasarkan load
- **CI/CD pipeline** —
  - GitHub Actions / GitLab CI untuk: lint > test > build > deploy
  - Staging environment untuk testing sebelum production
  - Blue-green deployment atau canary releases
  - Automated rollback jika health check fail
- **MLOps pipeline** —
  - **MLflow** untuk experiment tracking, model registry, model versioning
  - Automated retraining pipeline (schedule atau trigger-based)
  - A/B testing framework untuk compare model versions
  - Model promotion (staging > production) dengan approval workflow
- **Monitoring & alerting** —
  - **Prometheus + Grafana** untuk infrastructure metrics
  - **Evidently AI** untuk:
    - Data drift detection (feature distribution changes)
    - Model drift detection (performance degradation)
    - Data quality reports
    - Target drift detection
  - Alert via Telegram/Slack/PagerDuty jika:
    - Model accuracy drops below threshold
    - Data pipeline failure
    - API latency > 500ms
    - Prediction confidence anomaly
- **Infrastructure as Code** — Terraform untuk provisioning:
  - Cloud resources (AWS/GCP/Oracle Cloud)
  - Database instances
  - Kubernetes cluster
  - Monitoring stack
- **Backup & disaster recovery** —
  - Automated DB backup (daily + incremental)
  - Model artifacts backup ke S3/R2
  - Point-in-time recovery untuk database
  - Runbook untuk incident response
- **Security hardening** —
  - Secrets management (HashiCorp Vault / AWS Secrets Manager)
  - SSL/TLS untuk semua connections
  - Network policies & firewall rules
  - Regular security scanning (Trivy, Snyk)

---

## 8. QA / Test Engineer

**Mengapa:** Baru ada 27 unit test dasar. Tidak ada integration test, e2e test,
model performance test, atau data quality test.

**Tanggung jawab:**
- **Integration tests** — test end-to-end pipeline:
  - Fetch > preprocess > train > predict > verify > notify
  - Database operations (CRUD, migration, backup/restore)
  - API endpoint tests dengan real database
- **Model performance regression tests** —
  - Alert jika akurasi turun dari baseline (> 5% drop)
  - Compare model versions secara otomatis
  - Smoke test untuk model loading & inference
- **Data quality tests** — Great Expectations untuk:
  - Schema validation (kolom, tipe data, range)
  - Completeness (no missing data > threshold)
  - Freshness (data tidak stale)
  - Uniqueness (no duplicate records)
- **Load testing** — Locust/k6 untuk:
  - Simulasi concurrent users di dashboard
  - API throughput benchmarking
  - WebSocket connection limits
- **E2E tests** — Playwright/Cypress untuk:
  - Dashboard UI testing (24 halaman)
  - User flow: login > view prediction > run backtest > check portfolio
  - Mobile responsive testing
- **Chaos engineering** — test resilience:
  - yfinance down > retry & fallback ke API lain
  - Database corrupt > backup restore
  - Model file missing > auto-retrain
  - Redis down > graceful degradation
- **Test automation** — CI pipeline yang otomatis run semua tests
  pada setiap PR & commit ke main

---

## 9. Mobile Engineer (React Native) *(PERAN BARU)*

**Mengapa:** Aplikasi saham harus bisa diakses dari HP untuk monitoring real-time
dan notifikasi instan. Streamlit tidak mobile-friendly.

**Tanggung jawab:**
- **React Native app** —
  - Cross-platform (iOS & Android) dengan single codebase
  - Navigation: bottom tab (Dashboard, Prediksi, Portfolio, Settings)
  - Real-time price ticker di home screen
- **Push notifications** —
  - Firebase Cloud Messaging (Android) & APNs (iOS)
  - Sinyal BUY/SELL dengan sound & vibration
  - Price alert (custom threshold per ticker)
  - Market open/close reminder
- **Widget** —
  - Android home screen widget untuk IHSG price
  - iOS widget untuk portfolio summary
- **Offline support** —
  - Cache data lokal (SQLite/Realm)
  - Sync ketika online kembali
- **Biometric authentication** — fingerprint/face recognition
- **Deep linking** — buka specific ticker/prediksi dari URL

---

## 10. UI/UX Designer & Compliance Specialist *(PERAN BARU)*

**Mengapa:** Aplikasi yang baik harus punya UX yang intuitif DAN patuh regulasi OJK.
Saat ini tidak ada design system dan tidak ada compliance framework.

**UI/UX Designer tanggung jawab:**
- **Design system** —
  - Color palette, typography, spacing, component specs
  - Dark/light theme tokens
  - Icon set (Lucide/Phosphor)
- **User research** —
  - User journey mapping untuk trader retail Indonesia
  - Usability testing dengan target users
  - A/B testing untuk layout & information hierarchy
- **Data visualization design** —
  - Chart design guidelines (color blind friendly)
  - Dashboard layout optimization (F-pattern, Z-pattern)
  - Mobile-first responsive design specs
- **Prototype** — Figma interactive prototype untuk semua 11+ halaman

**Compliance Specialist tanggung jawab:**
- **OJK compliance** —
  - Disclaimer yang sesuai regulasi OJK
  - Tidak ada saran investasi langsung (hanya analisis)
  - Risk disclosure yang jelas
- **Data privacy** —
  - UU PDP (Perlindungan Data Pribadi) compliance
  - User data encryption & anonymization
  - Cookie consent & data retention policy
- **Audit trail** —
  - Log semua prediksi & rekomendasi untuk audit
  - Timestamp & version tracking untuk setiap output
- **Terms of Service & Privacy Policy** —
  - Legal documentation
  - Limitation of liability
  - Intellectual property

---

## Prioritas Hiring (Jika Budget Terbatas)

| Prioritas | Peran | Alasan |
|-----------|-------|--------|
| **1** | ML Engineer | Inti nilai aplikasi — tanpa model valid, aplikasi tidak ada gunanya |
| **2** | AI/LLM Agent Engineer | Diferensiasi vs kompetitor — laporan AI setara analis profesional |
| **3** | Quant Finance | Memastikan strategi & risk management secara finansial benar |
| **4** | Data Engineer | Data adalah foundation — garbage in, garbage out |
| **5** | Backend Engineer | Scalability — Streamlit tidak bisa serve multi-user |
| **6** | DevOps/MLOps | Otomatisasi & monitoring — tanpa ini, sistem tidak reliable |
| **7** | Frontend Engineer | UX penting tapi bisa setelah backend & API solid |
| **8** | QA | Quality assurance, idealnya dari awal tapi bisa bertahap |
| **9** | Mobile Engineer | Mobile app adalah nice-to-have setelah web solid |
| **10** | UI/UX & Compliance | Polish & legal protection, tahap akhir |

---

## Gap Terbesar Saat Ini vs "Sempurna"

> **Update Juni 2026:** Banyak item yang sebelumnya "gap" sudah terimplementasi.
> Berikut status terkini.

### ✅ Sudah Terimplementasi (Sebelumnya Critical/High)
1. **Model validity** — ✅ Walk-forward CV, purged k-fold, IC/Rank IC/ICIR (`validation.py`)
2. **Hyperparameter tuning** — ✅ Optuna TPE Bayesian optimization (`hyperopt.py`)
3. **Feature selection** — ✅ SHAP, Boruta, correlation filter, variance threshold (`feature_selection.py`)
4. **Transformer models** — ✅ PatchTST, TFT, LPatchTST dengan GPU CUDA (`transformer_models.py`)
5. **Transfer learning** — ✅ Parent-Child architecture (`transfer_learning.py`)
6. **Regime-aware models** — ✅ Regime detection + adaptive strategy (`regime.py`, `regime_models.py`)
7. **SHAP explainability** — ✅ Per-prediction feature attribution (`explainability.py`)
8. **AI/LLM agents** — ✅ Multi-agent system (`ai_agent.py`), Bull vs Bear debate (`bull_bear_debate.py`)
9. **FinBERT sentiment** — ✅ RSS + FinBERT + Fear&Greed (`sentiment_pipeline.py`)
10. **RAG system** — ✅ Vector DB + semantic search (`rag_system.py`)
11. **Trading memory** — ✅ Learn from past predictions (`trading_memory.py`)
12. **ReAct agent** — ✅ Reasoning + acting loop (`react_agent.py`)
13. **FastAPI REST API** — ✅ 10 endpoints with OpenAPI docs (`api.py`)
14. **Docker** — ✅ Dockerfile + docker-compose (app + API + MLflow)
15. **MLflow** — ✅ Experiment tracking, model logging (`mlflow_tracking.py`)
16. **Drift detection** — ✅ PSI + KS-test (`drift_monitor.py`)
17. **Automated retraining** — ✅ Time-based + drift-triggered (`retrain_scheduler.py`)
18. **Portfolio optimization** — ✅ 5 methods: Markowitz, BL, Risk Parity, HRP, CVaR (`portfolio.py`)
19. **Event-driven backtesting** — ✅ Walk-forward, BEI costs (`event_backtest.py`)
20. **Vectorized backtest** — ✅ Pandas-based fast backtest (`quant_finance.py`)
21. **Pyfolio tear sheet** — ✅ Sharpe, Sortino, Calmar, VaR (`quant_finance.py`)
22. **Slippage modeling** — ✅ Square-root market impact (`slippage.py`)
23. **VWAP/TWAP execution** — ✅ Order splitting algorithms (`execution_algo.py`)
24. **Broker simulation** — ✅ Order execution, short selling, partial fill (`broker_sim.py`)
25. **Walk-forward simulation** — ✅ Train 6mo, simulate 3mo, ATR SL/TP (`simulation_engine.py`)
26. **Chart pattern recognition** — ✅ H&S, Double Top, Triangles (`patterns.py`)
27. **Candlestick detection** — ✅ Hammer, Doji, Engulfing (`patterns.py`)
28. **Market structure** — ✅ HH/HL/LH/LL, BOS, CHoCH (`smc.py`)
29. **Composite AI Score** — ✅ Multi-dimension 1-10 (`scoring.py`)
30. **Multi-timeframe analysis** — ✅ 1W/1D/4H/1H confluence (`mtf.py`)
31. **Alphalens analysis** — ✅ IC, quantile returns (`alphalens_analysis.py`)
32. **Wyckoff phases** — ✅ Accumulation/Distribution (`wyckoff.py`)
33. **Elliott Wave** — ✅ Pattern + Fibonacci (`elliott_wave.py`)
34. **Factor model** — ✅ CAPM alpha/beta/R² (`factor_model.py`)
35. **Fraud detection** — ✅ 6-layer anti-fraud (`fraud_detection.py` + `anti_manipulation.py`)
36. **Compliance** — ✅ OJK disclaimers, audit trail (`compliance.py`)
37. **Kronos foundation model** — ✅ Zero-shot forecasting (`kronos_integration.py`)
38. **DRL trading** — ✅ Deep RL trading agent (`drl_trading.py`)
39. **Social sentiment** — ✅ Social media sentiment (`social_sentiment.py`)
40. **GPU acceleration** — ✅ PyTorch CUDA + LightGBM GPU
41. **481 tests passing** — ✅ 14 test files, 86+ modules, 24 pages
42. **Anti-manipulation metrics** — ✅ Z-Score volume shock, Amihud illiquidity, Beneish M-Score, wash trading, spoofing, fake news hype (`anti_manipulation.py`, Blueprint Bab 3+4)

### ❌ Gap yang Masih Tersisa (Selain Mobile)

### Critical (Harus diperbaiki segera)
1. **Data sources** — hanya yfinance (15 menit delay, sering rate-limited),
   tidak ada real-time IDX data (Invezgo/Kun/iTick), tidak ada foreign flow BEI
2. **Database** — masih SQLite, perlu migrasi ke PostgreSQL + TimescaleDB untuk skalabilitas

### High (Penting untuk skalabilitas)
3. **Architecture** — Streamlit masih monolith, perlu React/Next.js untuk multi-user
4. **Async task queue** — tidak ada Celery/Redis untuk async prediction jobs
5. **WebSocket streaming** — tidak ada real-time price streaming via WebSocket
6. **Authentication** — tidak ada JWT/OAuth2, tidak ada multi-user support

### Medium (Penting untuk completeness)
7. **Broker API integration** — belum terhubung ke broker real (Mirae Asset, IPOT)
8. **Monitoring infrastructure** — tidak ada Prometheus + Grafana + Evidently AI
9. **Data pipeline ETL** — tidak ada Apache Airflow/Prefect untuk scheduled pipeline
10. **Feature store** — tidak ada Feast untuk consistent training/serving features
11. **K8s deployment** — belum ada Kubernetes cluster untuk production
12. **Load/E2E testing** — belum ada Playwright/Cypress, Locust/k6

### Low (Nice to have)
13. **Mobile app** — belum ada React Native app (di luar scope saat ini)
14. **Design system** — belum ada proper UI/UX design system (Figma)
15. **Multi-language** — saat ini hanya Bahasa Indonesia
16. **PWA support** — belum ada offline capability
17. **Fama-French 5-factor** — baru CAPM, belum full 5-factor model
18. **DCC-GARCH** — portfolio risk masih basic correlation, belum dynamic
19. **CatBoost** — belum ditambahkan ke ensemble
20. **TFT-GNN** — belum ada Graph Neural Network untuk inter-market relations

---

## Tech Stack Target (Sempurna)

| Layer | Saat Ini | Target |
|-------|----------|--------|
| **Frontend** | Streamlit (24 halaman) | React/Next.js + TradingView Charts |
| **Backend** | FastAPI (10 endpoints) | FastAPI + WebSocket + Celery |
| **Database** | SQLite | PostgreSQL + TimescaleDB + Redis |
| **ML Models** | RF + XGBoost + LightGBM + PatchTST/TFT (GPU) | + CatBoost, TFT-GNN |
| **NLP/Sentiment** | FinBERT + RSS + Fear&Greed + LLM Agents | + Social media, LangGraph |
| **Data Sources** | yfinance + FRED + RSS + Alpha Vantage + Finnhub | + IDX API (Invezgo/Kun) |
| **MLOps** | MLflow + drift detection (PSI/KS) + auto-retrain | + Evidently AI + Airflow |
| **Deployment** | Docker + GitHub Actions | + K8s + Terraform |
| **Monitoring** | drift_monitor.py | + Prometheus + Grafana + Evidently AI |
| **Mobile** | - | React Native + FCM/APNs |
| **Feature Store** | - | Feast |
| **Vector DB** | RAG system (rag_system.py) | Qdrant (untuk RAG & semantic cache) |
| **Broker Integration** | BrokerSimulator (simulasi) | Mirae Asset API / IPOT API (real) |
| **CI/CD** | GitHub Actions (lint, test, build) | + Helm + Blue-green deploy |
| **GPU** | PyTorch CUDA + LightGBM GPU | + XGBoost GPU (butuh SM 7.0+) |

---

## Roadmap Implementasi (Fase)

### Fase 1: Foundation (Bulan 1-3) — ✅ Selesai
- ✅ ML Engineer: Walk-forward CV, hyperparameter tuning, feature selection
- ❌ Data Engineer: Tambah IDX API (Invezgo/Kun), setup Airflow ETL
- ✅ DevOps: Docker containerization, MLflow setup

### Fase 2: Intelligence (Bulan 4-6) — ✅ Selesai
- ✅ AI/LLM Agent Engineer: FinBERT sentiment, multi-agent, RAG
- ✅ ML Engineer: PatchTST/TFT model, transfer learning, regime-aware models
- ✅ Quant Finance: Event-driven backtesting, Black-Litterman portfolio

### Fase 3: Scale (Bulan 7-9) — Sebagian Selesai
- ✅ Backend Engineer: FastAPI REST API (10 endpoints)
- ❌ Backend Engineer: WebSocket, PostgreSQL migration
- ❌ DevOps: K8s deployment, Prometheus + Grafana + Evidently AI monitoring
- ✅ QA: 366 tests (integration, unit, module)
- ❌ QA: Load testing, E2E tests (Playwright/Cypress)

### Fase 4: Experience (Bulan 10-12) — Sebagian Selesai
- ❌ Frontend Engineer: React/Next.js dashboard, TradingView charts
- ❌ Mobile Engineer: React Native app, push notifications (di luar scope)
- ❌ UI/UX Designer: Design system, user research, prototype
- ✅ Compliance: OJK framework, audit trail (`compliance.py`)

### Fase 5: Automation (Bulan 13+) — Sebagian Selesai
- ❌ Broker API integration (auto-trading dengan broker real)
- ✅ Advanced execution algorithms (VWAP/TWAP)
- ❌ Real-time streaming pipeline (Kafka)
- ✅ Advanced portfolio (risk parity, HRP, CVaR optimization)

---

## Referensi Riset

1. **PatchTST** — "A Time Series is Worth 64 Words" (arXiv:2211.14730)
2. **TFT-GNN** — "Hybrid Temporal Fusion Transformer Graph Neural Network"
   (AppliedMath 2025, 5(4), 176)
3. **LPatchTST** — LSTM + PatchTST hybrid, Sharpe ratio 2.31
   (mental-momentum.ai research)
4. **TradingAgents** — Multi-Agents LLM Financial Trading Framework
   (arXiv:2412.20138, 2024)
5. **stock-agent-ops** — Transfer Learning LSTM + LangGraph agentic AI (GitHub)
6. **FinBERT** — Financial Sentiment Analysis with BERT (ProsusAI/finBERT)
7. **Evidently AI** — Open-source ML evaluation & observability (evidentlyai.com)
8. **AlphaPulse** — Production-grade MLOps for financial market data (GitHub)
9. **Invezgo** — Indonesia Stock API, real-time IDX data (invezgo.com)
10. **Kun Data** — Real-time IDX WebSocket API (kun.pro)
11. **iTick** — Indonesia Stocks API, 30+ years historical (itick.org)
12. **Citadel EQR** — Quantitative Developer team structure (citadel.com)
13. **Quadrature Capital** — Unified quant developer model (quantlabsnet.com)
14. **AWS** — "Build an intelligent financial analysis agent with LangGraph"
15. **FinBERT-LSTM** — Sentiment-augmented PatchTST, 526% cumulative return
    (ResearchGate, 2024-2025)

### Referensi Kompetitor (Analisis Komparatif)
16. **TradingAgents** — Multi-Agent LLM Trading Framework (arXiv:2412.20138)
    — Bull/Bear debate, trading memory, ReAct prompting
17. **Microsoft Qlib** — AI Quant Platform (44K stars) — IC/Rank IC metrics,
    Alpha158/360, concept drift modeling
18. **QuantRocket** — Walk-forward ML, Alphalens, Pyfolio, vectorized backtesting
19. **Kavout** — Kai Score (1-9), multi-dimension rating, 900+ features
20. **Danelfin** — AI Score 1-10, 10,000+ features, +376% return (2017-2025)
21. **AlphaSense** — Multi-mode AI research, in-line citations, deep research
22. **FinRL-X** — Weight-centric architecture, composable strategy pipeline
23. **TrendSpider** — Auto trendline, chart pattern recognition, MTFA, NL scanner
24. **BOZ** — Multi-timeframe confluence, market structure, verdict box
25. **MarketLayer** — Skill pack scoring, candidate ranking, multi-provider AI
26. **Hanzo AI** — Wyckoff phases, volume anomaly, daily briefing
27. **OZCx** — FinBERT live, WebSocket alerts, candlestick detection
28. **Trade Ideas** — Holly AI, real-time signals with risk levels
29. **Market Digest** — Multi-timeframe ScoreCard, 0-100 composite score

> **Lihat `COMPETITIVE_ANALYSIS.md` untuk analisis lengkap 14 kompetitor.**

---

## Skill Requirements & KPI Per Peran

### 1. ML Engineer
**Skills:**
- Python (pandas, numpy, scikit-learn, xgboost, lightgbm)
- PyTorch / TensorFlow (untuk transformer models: PatchTST, TFT)
- Time-series cross-validation (walk-forward, purged k-fold)
- SHAP / LIME / Boruta (feature selection & interpretability)
- Optuna / Bayesian optimization (hyperparameter tuning)
- MLflow (experiment tracking)
- **Alphalens** — alpha factor analysis (IC, turnover, quantile returns) *(dari QuantRocket)*
- **Pyfolio** — portfolio tear sheet generation *(dari QuantRocket)*
- **IC/Rank IC/ICIR** — signal evaluation metrics *(dari Microsoft Qlib)*
- **Chart pattern recognition** — Head & Shoulders, Flags, Triangles *(dari TrendSpider)*
- **Candlestick pattern detection** — Hammer, Doji, Engulfing *(dari TrendSpider/OZCx)*
- **Market structure analysis** — HH/HL/LH/LL, CHoCH, BOS *(dari BOZ)*
- **Volume anomaly detection** — Z-score based unusual volume *(dari Hanzo AI)*
- **Composite AI Score** — multi-dimension scoring 1-10 *(dari Kavout/Danelfin)*
- **Multi-timeframe confluence** — 1h/4h/Daily/Weekly alignment *(dari BOZ/Market Digest)*

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| Directional Accuracy (real-time) | > 55% | 3 bulan |
| MAPE | < 3% | 3 bulan |
| Feature reduction | 135 → 40-60 | 2 bulan |
| Walk-forward CV implemented | ✅ | 1 bulan |
| PatchTST/TFT model trained | ✅ | 4 bulan |
| Model interpretability (SHAP) | ✅ | 3 bulan |
| IC/Rank IC metrics implemented | ✅ | 1 bulan |
| Composite AI Score (1-10) | ✅ | 1 bulan |
| Multi-timeframe analysis | ✅ | 2 bulan |
| Chart pattern recognition | ✅ | 3 bulan |
| Candlestick pattern detection | ✅ | 2 bulan |
| Market structure (HH/HL/LH/LL) | ✅ | 1 bulan |
| Volume anomaly detection | ✅ | 1 bulan |
| Alphalens factor analysis | ✅ | 2 bulan |
| Pyfolio tear sheet | ✅ | 2 bulan |

### 2. AI/LLM Agent Engineer
**Skills:**
- Python (LangChain, LangGraph)
- LLM APIs (OpenAI, Anthropic, Groq) atau local (Ollama)
- FinBERT / HuggingFace transformers
- Vector databases (Qdrant, Pinecone, ChromaDB)
- RAG (Retrieval-Augmented Generation)
- Web scraping (BeautifulSoup, Selenium, Playwright)
- Prompt engineering & agent orchestration
- **ReAct prompting framework** — reasoning + acting loop *(dari TradingAgents)*
- **Bull vs Bear debate system** — dual-agent adversarial analysis *(dari TradingAgents)*
- **Trading memory** — learn from past predictions & outcomes *(dari TradingAgents)*
- **Multi-mode AI research** — fast/standard/deep/research modes *(dari AlphaSense)*
- **In-line citations** — source verification for AI output *(dari AlphaSense)*
- **Multi-model orchestration** — best model per task *(dari AlphaSense)*
- **Checkpoint system** — crash recovery for long-running agents *(dari TradingAgents)*
- **Skill pack scoring** — modular scoring system *(dari MarketLayer)*
- **Daily morning briefing** — auto-generated Telegram summary *(dari Hanzo AI)*

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| FinBERT sentiment pipeline | ✅ | 2 bulan |
| Multi-agent system (4 agents) | ✅ | 4 bulan |
| RAG with vector DB | ✅ | 3 bulan |
| News sources integrated | ≥ 5 | 3 bulan |
| Report generation time | < 60s | 4 bulan |
| Semantic cache hit rate | > 30% | 5 bulan |
| Bull vs Bear debate system | ✅ | 3 bulan |
| Trading memory system | ✅ | 3 bulan |
| Multi-mode AI (fast/deep) | ✅ | 4 bulan |
| In-line citations in reports | ✅ | 3 bulan |
| Daily morning briefing | ✅ | 2 bulan |
| Checkpoint crash recovery | ✅ | 3 bulan |

### 3. Quant Finance Developer
**Skills:**
- Python (pandas, numpy, scipy)
- Quantitative finance (factor models, options pricing, portfolio theory)
- Black-Litterman, Risk Parity, HRP, CVaR optimization
- Event-driven backtesting (zipline, backtrader, custom)
- Market microstructure (VWAP, TWAP, order book)
- BEI/IDX market rules & regulations
- Financial APIs (broker integration)
- **Entry/Target/Stop calculation** — actionable price levels per signal *(dari BOZ/Trade Ideas)*
- **Wyckoff phase detection** — accumulation/distribution analysis *(dari Hanzo AI)*
- **Weight-centric architecture** — portfolio weight as universal interface *(dari FinRL-X)*
- **Vectorized backtesting** — pandas-based fast backtesting *(dari QuantRocket/Moonshot)*
- **Point-in-time screening** — no look-ahead bias in universe selection *(dari QuantRocket)*

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| Event-driven backtester | ✅ | 3 bulan |
| Black-Litterman portfolio | ✅ | 4 bulan |
| Realistic backtest (commission, slippage, T+2) | ✅ | 3 bulan |
| Fama-French factor model for IDX | ✅ | 5 bulan |
| Sharpe ratio (paper trading) | > 1.0 | 6 bulan |
| Max drawdown | < 15% | 6 bulan |
| Entry/Target/Stop per signal | ✅ | 1 bulan |
| Wyckoff phase detection | ✅ | 3 bulan |
| Vectorized backtester (Moonshot-style) | ✅ | 2 bulan |
| Pyfolio portfolio tear sheet | ✅ | 1 bulan |

### 4. Backend Engineer
**Skills:**
- Python (FastAPI, Pydantic v2, SQLAlchemy, async/await)
- WebSocket (real-time streaming)
- Celery / Prefect (async task queue)
- PostgreSQL + TimescaleDB (time-series database)
- Redis (caching, pub/sub)
- JWT / OAuth2 (authentication)
- Docker (containerization)
- **Weight-centric API design** — portfolio weight vector as interface *(dari FinRL-X)*
- **Deployment consistency** — same interface for backtest & live *(dari FinRL-X)*
- **Pre-trade risk checks** — validate before execution *(dari FinRL-X)*
- **Checkpoint system** — resume interrupted long-running tasks *(dari TradingAgents)*

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| API latency (p95) | < 200ms | 2 bulan |
| API uptime | > 99.5% | 3 bulan |
| WebSocket connections | 100+ concurrent | 3 bulan |
| SQLite → PostgreSQL migration | ✅ | 2 bulan |
| Async prediction job | < 5s queue time | 2 bulan |
| API endpoints documented | 100% (OpenAPI) | 2 bulan |

### 5. Frontend Engineer
**Skills:**
- React 18+ / Next.js 14+ (App Router, SSR, RSC)
- TypeScript
- TanStack Query / SWR (data fetching)
- Zustand / Jotai (state management)
- TradingView Lightweight Charts / Highcharts
- TailwindCSS + shadcn/ui
- WebSocket integration
- PWA (service workers, offline)
- **Automated trendline rendering** — mathematical precision trendlines on chart *(dari TrendSpider)*
- **Chart pattern visualization** — Head & Shoulders, Flags, Triangles overlay *(dari TrendSpider)*
- **Multi-timeframe chart overlay** — plot weekly indicators on daily chart *(dari TrendSpider MTFA)*
- **Natural language scanner** — "find stocks like this" search *(dari TrendSpider)*
- **Verdict Box UI** — Direction + Confidence + R/R in one widget *(dari BOZ)*
- **Composite AI Score gauge** — 1-10 visual indicator *(dari Kavout/Danelfin)*

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| Dashboard load time | < 2s | 2 bulan |
| Lighthouse score | > 90 | 3 bulan |
| Real-time chart update latency | < 100ms | 3 bulan |
| Mobile responsive | 100% pages | 3 bulan |
| WCAG 2.1 AA compliance | ✅ | 4 bulan |
| PWA installable | ✅ | 4 bulan |
| Auto trendline on chart | ✅ | 3 bulan |
| Chart pattern overlay | ✅ | 3 bulan |
| Multi-timeframe chart | ✅ | 3 bulan |
| Verdict Box widget | ✅ | 2 bulan |
| AI Score gauge (1-10) | ✅ | 2 bulan |
| Natural language scanner | ✅ | 4 bulan |

### 6. Data Engineer
**Skills:**
- Python (Airflow / Prefect, SQLAlchemy, pandas)
- PostgreSQL + TimescaleDB + ClickHouse
- Apache Kafka / Redis Streams (streaming)
- Feast (feature store)
- Great Expectations (data quality)
- DVC (data versioning)
- Web scraping & API integration
- Parquet / S3 / MinIO (data lake)

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| Data pipeline uptime | > 99% | 2 bulan |
| Data freshness (real-time) | < 5 min | 2 bulan |
| Data sources integrated | ≥ 5 | 3 bulan |
| Feature store (Feast) | ✅ | 3 bulan |
| Data quality tests passing | > 95% | 2 bulan |
| ETL pipeline (Airflow) | ✅ | 2 bulan |

### 7. DevOps / MLOps Engineer
**Skills:**
- Docker & Kubernetes (K3s/EKS/GKE)
- Terraform (Infrastructure as Code)
- GitHub Actions / GitLab CI (CI/CD)
- MLflow (model registry, experiment tracking)
- Prometheus + Grafana (monitoring)
- Evidently AI (drift detection)
- Helm (K8s package management)
- HashiCorp Vault / AWS Secrets Manager

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| Docker containerization | ✅ | 1 bulan |
| CI/CD pipeline | ✅ | 1 bulan |
| K8s deployment | ✅ | 3 bulan |
| MLflow model registry | ✅ | 2 bulan |
| Monitoring (Prometheus + Grafana) | ✅ | 3 bulan |
| Drift detection (Evidently AI) | ✅ | 3 bulan |
| Deployment frequency | Weekly | 3 bulan |
| Mean time to recovery (MTTR) | < 30 min | 4 bulan |

### 8. QA / Test Engineer
**Skills:**
- Python (pytest, pytest-cov)
- Playwright / Cypress (E2E testing)
- Locust / k6 (load testing)
- Great Expectations (data quality)
- GitHub Actions (CI test automation)
- Chaos engineering principles
- API testing (httpx, Postman)

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| Test coverage | > 80% | 3 bulan |
| Integration tests | ✅ | 2 bulan |
| E2E tests (all 11+ pages) | ✅ | 4 bulan |
| Load test (100 concurrent) | ✅ | 3 bulan |
| Data quality tests | ✅ | 2 bulan |
| Bug escape rate | < 5% | 4 bulan |

### 9. Mobile Engineer
**Skills:**
- React Native (Expo or CLI)
- TypeScript
- Firebase Cloud Messaging / APNs (push notifications)
- SQLite / Realm (local storage)
- WebSocket integration
- Biometric authentication
- Deep linking
- App Store / Play Store deployment

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| App launch time | < 2s | 3 bulan |
| Push notification delivery | > 99% | 3 bulan |
| Offline data sync | ✅ | 4 bulan |
| Crash-free sessions | > 99.5% | 4 bulan |
| App Store rating | > 4.5 | 6 bulan |

### 10. UI/UX Designer & Compliance Specialist
**Skills (UI/UX):**
- Figma (prototyping, design system)
- Design tokens (color, typography, spacing)
- Data visualization design
- User research & usability testing
- WCAG 2.1 AA accessibility
- Mobile-first responsive design

**Skills (Compliance):**
- OJK regulations (investment advisor, robo-advisor)
- UU PDP (Perlindungan Data Pribadi)
- BEI trading rules
- Legal documentation (ToS, Privacy Policy)
- Audit trail design

**KPI:**
| Metric | Target | Timeline |
|--------|--------|----------|
| Design system (Figma) | ✅ | 2 bulan |
| Prototype all pages | ✅ | 3 bulan |
| Usability test with 5+ users | ✅ | 4 bulan |
| OJK compliance framework | ✅ | 3 bulan |
| UU PDP compliance | ✅ | 3 bulan |
| Audit trail implementation | ✅ | 4 bulan |

---

## Matriks Interaksi Antar Peran

| | ML Eng | AI/LLM | Quant | Backend | Frontend | Data Eng | DevOps | QA | Mobile | UI/UX |
|---|---|---|---|---|---|---|---|---|---|---|
| **ML Eng** | — | Model output → AI agent | Features → Quant | Model → API | — | Features from Data | Model → MLflow | Model tests | — | — |
| **AI/LLM** | Sentiment → ML | — | Report → Quant | Agent API | Reports → UI | News data from Data | LLM deploy | Agent tests | Reports → Mobile | Report layout |
| **Quant** | Strategy → ML | — | — | Strategy API | Risk UI | Market data | — | Backtest tests | — | Risk viz |
| **Backend** | Predict API | Agent API | Strategy API | — | REST/WS → FE | DB schema | Deploy API | API tests | API → Mobile | — |
| **Frontend** | — | — | — | API consume | — | — | — | E2E tests | Share components | Design → FE |
| **Data Eng** | Features → ML | News → AI | Data → Quant | DB → Backend | — | — | Pipeline deploy | Data tests | — | — |
| **DevOps** | MLflow | LLM infra | — | K8s deploy | FE deploy | Pipeline infra | — | CI/CD | — | — |
| **QA** | Model tests | Agent tests | Backtest tests | API tests | E2E tests | Data tests | Infra tests | — | Mobile tests | Accessibility |
| **Mobile** | — | Reports | — | API consume | Share design | — | — | Mobile tests | — | Design → Mobile |
| **UI/UX** | — | Report layout | Risk viz | — | Design → FE | — | — | Accessibility | Design → Mobile | — |

---

## Deliverables Per Fase

### Fase 1: Foundation (Bulan 1-3)
| Deliverable | Owner | Status | Dari Kompetitor |
|------------|-------|--------|----------------|
| Walk-forward CV implementation | ML Eng | ❌ Pending | QuantRocket |
| Optuna hyperparameter tuning | ML Eng | ❌ Pending | — |
| SHAP feature selection (135→50) | ML Eng | ❌ Pending | — |
| IC/Rank IC/ICIR metrics | ML Eng | ❌ Pending | Microsoft Qlib |
| Composite AI Score (1-10) | ML Eng | ❌ Pending | Kavout/Danelfin |
| Multi-timeframe analysis (1h/4h/Daily/Weekly) | ML Eng | ✅ Done | BOZ/Market Digest |
| Market structure analysis (HH/HL/LH/LL) | ML Eng | ❌ Pending | BOZ |
| Volume anomaly detection | ML Eng | ❌ Pending | Hanzo AI |
| Entry/Target/Stop per signal | Quant | ❌ Pending | BOZ/Trade Ideas |
| Vectorized backtester (Moonshot-style) | Quant | ❌ Pending | QuantRocket |
| Pyfolio portfolio tear sheet | Quant | ❌ Pending | QuantRocket |
| Alphalens factor analysis | ML Eng | ❌ Pending | QuantRocket |
| Daily morning briefing (Telegram) | AI/LLM Eng | ❌ Pending | Hanzo AI |
| Docker containerization (all services) | DevOps | ❌ Pending | — |
| MLflow experiment tracking setup | DevOps | ❌ Pending | — |
| IDX API integration (Invezgo/Kun) | Data Eng | ❌ Pending | — |
| Airflow ETL pipeline setup | Data Eng | ❌ Pending | — |
| Great Expectations data quality tests | QA + Data Eng | ❌ Pending | — |
| CI/CD pipeline (lint→test→build→deploy) | DevOps | ❌ Pending | — |

### Fase 2: Intelligence (Bulan 4-6)
| Deliverable | Owner | Status | Dari Kompetitor |
|------------|-------|--------|----------------|
| FinBERT sentiment pipeline (ID news) | AI/LLM Eng | ❌ Pending | OZCx |
| LangGraph multi-agent system (4 agents) | AI/LLM Eng | ❌ Pending | TradingAgents |
| Bull vs Bear debate system | AI/LLM Eng | ❌ Pending | TradingAgents |
| Trading memory (learn from past) | AI/LLM Eng | ❌ Pending | TradingAgents |
| ReAct prompting framework | AI/LLM Eng | ❌ Pending | TradingAgents |
| Multi-mode AI research (fast/deep) | AI/LLM Eng | ❌ Pending | AlphaSense |
| In-line citations in AI reports | AI/LLM Eng | ❌ Pending | AlphaSense |
| Checkpoint crash recovery | AI/LLM Eng | ❌ Pending | TradingAgents |
| RAG with Qdrant vector DB | AI/LLM Eng | ❌ Pending | — |
| Chart pattern recognition (H&S, Flags) | ML Eng | ❌ Pending | TrendSpider |
| Candlestick pattern detection | ML Eng | ❌ Pending | TrendSpider/OZCx |
| Skill pack modular scoring | ML Eng | ❌ Pending | MarketLayer |
| Multi-dimension rating (Tech/Fund/Sent) | ML Eng | ❌ Pending | Kavout |
| Multi-horizon prediction (3d/1w/1m/3m) | ML Eng | ❌ Pending | Danelfin |
| PatchTST model trained & evaluated | ML Eng | ❌ Pending | — |
| TFT model trained & evaluated | ML Eng | ❌ Pending | — |
| Transfer learning (parent-child) | ML Eng | ❌ Pending | — |
| Regime-aware model architecture | ML Eng | ❌ Pending | Qlib (concept drift) |
| Event-driven backtesting engine | Quant | ❌ Pending | — |
| Black-Litterman portfolio optimization | Quant | ✅ Done | — |
| Fama-French factor model for IDX | Quant | ❌ Pending | — |
| Wyckoff phase detection | Quant | ❌ Pending | Hanzo AI |
| Feast feature store | Data Eng | ❌ Pending | — |

### Fase 3: Scale (Bulan 7-9)
| Deliverable | Owner | Status | Dari Kompetitor |
|------------|-------|--------|----------------|
| FastAPI REST API (8+ endpoints) | Backend | ❌ Pending | MarketLayer |
| WebSocket real-time streaming | Backend | ❌ Pending | OZCx |
| Weight-centric API architecture | Backend | ❌ Pending | FinRL-X |
| Deployment consistency (backtest=live) | Backend | ❌ Pending | FinRL-X |
| Pre-trade risk checks | Backend | ❌ Pending | FinRL-X |
| PostgreSQL + TimescaleDB migration | Backend + Data Eng | ❌ Pending | QuantRocket |
| Redis caching layer | Backend | ❌ Pending | — |
| Celery async task queue | Backend | ❌ Pending | — |
| JWT/OAuth2 authentication | Backend | ❌ Pending | — |
| K8s production deployment | DevOps | ❌ Pending | — |
| Prometheus + Grafana monitoring | DevOps | ❌ Pending | — |
| Evidently AI drift detection | DevOps + ML Eng | ❌ Pending | — |
| Integration tests (end-to-end) | QA | ❌ Pending | — |
| Load testing (k6/Locust) | QA | ❌ Pending | — |

### Fase 4: Experience (Bulan 10-12)
| Deliverable | Owner | Status | Dari Kompetitor |
|------------|-------|--------|----------------|
| React/Next.js dashboard (11+ pages) | Frontend | ❌ Pending | — |
| TradingView Lightweight Charts | Frontend | ❌ Pending | — |
| Auto trendline rendering on chart | Frontend | ❌ Pending | TrendSpider |
| Chart pattern overlay (H&S, Flags) | Frontend | ❌ Pending | TrendSpider |
| Multi-timeframe chart (MTFA) | Frontend | ❌ Pending | TrendSpider |
| Verdict Box widget (Direction+R/R) | Frontend | ❌ Pending | BOZ |
| Composite AI Score gauge (1-10) | Frontend | ❌ Pending | Kavout/Danelfin |
| Natural language stock scanner | Frontend | ❌ Pending | TrendSpider |
| React Native mobile app | Mobile | ❌ Pending | — |
| Push notifications (FCM/APNs) | Mobile | ❌ Pending | — |
| Figma design system | UI/UX | ❌ Pending | — |
| shadcn/ui component library | Frontend + UI/UX | ❌ Pending | — |
| OJK compliance framework | Compliance | ❌ Pending | — |
| UU PDP compliance | Compliance | ❌ Pending | — |
| Audit trail (all predictions/signals) | Compliance + Backend | ❌ Pending | AlphaSense |
| E2E tests (Playwright) | QA | ❌ Pending | — |
| Accessibility (WCAG 2.1 AA) | QA + UI/UX | ❌ Pending | — |

### Fase 5: Automation (Bulan 13+)
| Deliverable | Owner | Status |
|------------|-------|--------|
| Broker API integration (Mirae/IPOT) | Backend + Quant | ❌ Pending |
| Auto-trading execution engine | Backend + Quant | ❌ Pending |
| VWAP/TWAP execution algorithms | Quant | ❌ Pending |
| Kafka real-time streaming pipeline | Data Eng + Backend | ❌ Pending |
| Risk Parity / HRP / CVaR portfolio | Quant | ✅ Done |
| Options analysis (Greeks, IV) | Quant | ❌ Pending |
| Foreign flow BEI integration | Data Eng | ❌ Pending |
| Social media sentiment (Twitter, Stockbit) | AI/LLM Eng + Data Eng | ❌ Pending |
| A/B testing framework for models | ML Eng + DevOps | ❌ Pending |
| Automated retraining pipeline | DevOps + ML Eng | ❌ Pending |

---

## Estimasi Budget & Resource *(BARU)*

### Skenario: Tim Full-Time (12 bulan)

| Peran | Jumlah | Range Gaji/bln (IDR) | Total/bln | Total/thn |
|-------|--------|---------------------|-----------|-----------|
| ML Engineer (Senior) | 1 | 25-40jt | 32.5jt | 390jt |
| AI/LLM Agent Engineer | 1 | 20-35jt | 27.5jt | 330jt |
| Quant Finance Developer | 1 | 25-40jt | 32.5jt | 390jt |
| Backend Engineer (Senior) | 1 | 20-35jt | 27.5jt | 330jt |
| Frontend Engineer (Senior) | 1 | 18-30jt | 24jt | 288jt |
| Data Engineer | 1 | 18-30jt | 24jt | 288jt |
| DevOps/MLOps Engineer | 1 | 20-35jt | 27.5jt | 330jt |
| QA Engineer | 1 | 12-20jt | 16jt | 192jt |
| Mobile Engineer | 1 | 15-25jt | 20jt | 240jt |
| UI/UX + Compliance | 1 | 12-20jt | 16jt | 192jt |
| **TOTAL** | **10** | | **~247jt/bln** | **~2.96 Miliar/thn** |

### Skenario: Tim Lean (Budget Terbatas, 12 bulan)

| Peran | Jumlah | Range Gaji/bln (IDR) | Total/bln | Total/thn |
|-------|--------|---------------------|-----------|-----------|
| ML Engineer (Senior) | 1 | 25-40jt | 32.5jt | 390jt |
| AI/LLM Agent Engineer | 1 | 20-35jt | 27.5jt | 330jt |
| Backend/DevOps (Hybrid) | 1 | 20-35jt | 27.5jt | 330jt |
| Data Engineer | 1 | 18-30jt | 24jt | 288jt |
| Frontend/Mobile (Hybrid) | 1 | 18-30jt | 24jt | 288jt |
| **TOTAL** | **5** | | **~135.5jt/bln** | **~1.63 Miliar/thn** |

### Skenario: Solo Developer (Budget Sangat Terbatas)

| Peran | Tools/Services | Biaya/bln (IDR) |
|-------|---------------|----------------|
| Cloud (Oracle Free Tier / AWS) | VPS + DB | 0-500rb |
| IDX API (Invezgo basic) | Data | 200-500rb |
| LLM API (Groq free / Ollama local) | AI | 0-300rb |
| Domain + SSL | Web | 150rb |
| Telegram Bot | Notifikasi | 0 |
| GitHub Actions | CI/CD | 0 (free tier) |
| MLflow (self-hosted) | MLOps | 0 |
| **TOTAL** | | **~350rb - 1.45jt/bln** |

### Biaya Infrastructure Target (Production)

| Service | Provider | Biaya/bln (IDR) |
|---------|----------|----------------|
| VPS (4 vCPU, 8GB RAM) | Oracle Cloud / AWS EC2 | 0-1jt |
| PostgreSQL + TimescaleDB | Self-hosted / RDS | 0-2jt |
| Redis | Self-hosted / ElastiCache | 0-500rb |
| MLflow | Self-hosted | 0 |
| Prometheus + Grafana | Self-hosted | 0 |
| Qdrant (vector DB) | Self-hosted / Cloud | 0-500rb |
| S3/MinIO (storage) | AWS S3 / Self-hosted | 100-300rb |
| Domain + SSL | Cloudflare | 150rb |
| **TOTAL** | | **~250rb - 4.45jt/bln** |

---

## Competitive Analysis *(DIPERLUAS)*

> **Analisis lengkap 14 kompetitor: lihat `COMPETITIVE_ANALYSIS.md`**

### Aplikasi Sejenis di Indonesia

| Aplikasi | Fitur | Kelemahan vs Aplikasi Ini |
|----------|-------|--------------------------|
| **Stockbit** | Forum, broker, charting | Tidak ada ML prediction, tidak ada inter-market analysis |
| **Bibit** | Robo-advisor (mutual fund) | Tidak ada saham langsung, tidak ada ML prediction |
| **IPOT** | Broker + analisa | Tidak ada ML, tidak ada portfolio optimization |
| **RTI Business** | Data + charting | Tidak ada prediction, tidak ada risk management |
| **TradingView** | Charting + screening | Tidak ada ML prediction (hanya indicator-based) |

### Kompetitor Global (Open-Source & Komersial)

| Platform | Type | Fitur Utama | Yang Bisa Diadopsi |
|----------|------|-------------|-------------------|
| **TradingAgents** | OSS | Multi-agent LLM, Bull/Bear debate | Debate system, trading memory |
| **Microsoft Qlib** | OSS | IC metrics, Alpha158/360, concept drift | IC/Rank IC, model benchmark |
| **QuantRocket** | Komersial | Walk-forward ML, Alphalens, Pyfolio | Factor analysis, tear sheets |
| **Kavout** | Komersial | Kai Score 1-9, 900+ features | Composite score, multi-dimension |
| **Danelfin** | Komersial | AI Score 1-10, +376% return | Multi-horizon, feature expansion |
| **AlphaSense** | Komersial | Multi-mode AI, deep research | Research modes, citations |
| **FinRL-X** | OSS | Weight-centric architecture | Composable pipeline |
| **TrendSpider** | Komersial | Auto trendline, pattern recognition | Chart patterns, MTFA, NL scanner |
| **BOZ** | OSS | MTF confluence, verdict box | Market structure, entry/target/stop |
| **MarketLayer** | OSS | Skill pack scoring, AI research | Modular scoring, candidate ranking |
| **Hanzo AI** | Komersial | Wyckoff, volume anomaly, briefing | Daily briefing, anomaly detection |
| **OZCx** | Komersial | FinBERT live, WebSocket | Real-time sentiment, alerts |
| **Trade Ideas** | Komersial | Holly AI, real-time signals | Risk levels per signal |
| **Market Digest** | OSS | Multi-timeframe ScoreCard | 0-100 composite, retrace tracking |

### Keunggulan Unik Aplikasi Ini
1. **ML prediction** — tidak ada kompetitor Indonesia yang punya ML prediction untuk IHSG
2. **Inter-market analysis** — analisis hubungan IHSG dengan global markets
3. **Business rules layer** — aturan anti-FOMO, VIX panic, trend follower
4. **Risk management komprehensif** — VaR, CVaR, Kelly, Sharpe, Sortino
5. **Fear & Greed Index** — composite sentiment dari 7 komponen
6. **Market Regime detection** — Bull/Bear/Sideways dengan strategi per regime
7. **Backtesting & verification** — simpan & verifikasi otomatis
8. **AI/LLM agent (target)** — laporan otomatis setara analis profesional
9. **Bull vs Bear debate (target)** — analisis dua sisi seperti TradingAgents *(BARU)*
10. **Composite AI Score (target)** — ranking 1-10 seperti Kavout/Danelfin *(BARU)*
11. **Multi-timeframe confluence (target)** — 1h/4h/Daily/Weekly seperti BOZ *(BARU)*
12. **Entry/Target/Stop per sinyal (target)** — actionable levels seperti Trade Ideas *(BARU)*

### Gap vs Kompetitor Global (Setelah Adopsi)
| Fitur | Aplikasi Ini (Setelah) | TradingView | Bloomberg | TradingAgents | Kavout |
|-------|----------------------|-------------|-----------|---------------|--------|
| ML Prediction | ✅ | ❌ | ❌ | ❌ | ✅ |
| Multi-Agent LLM | ✅ (target) | ❌ | ❌ | ✅ | ❌ |
| Bull vs Bear | ✅ (target) | ❌ | ❌ | ✅ | ❌ |
| Composite Score | ✅ (target) | ❌ | ❌ | ❌ | ✅ |
| Multi-Timeframe | ✅ (target) | ✅ | ✅ | ❌ | ✅ |
| Entry/Target/Stop | ✅ (target) | ❌ | ❌ | ❌ | ❌ |
| Inter-Market | ✅ | ❌ | ✅ | ❌ | ❌ |
| Business Rules | ✅ | ❌ | ❌ | ❌ | ❌ |
| Risk Management | ✅ | ❌ | ✅ | ✅ | ❌ |
| IDX Focus | ✅ | ❌ | ❌ | ❌ | ❌ |
| Price | Gratis | $15-60/bln | $2,000/bln | Gratis | $$ |

---

## Risk Assessment & Mitigation *(BARU)*

### Technical Risks
| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|-----------|
| yfinance rate limit | High | High | Multi-source fallback (IDX API, Alpha Vantage) |
| Model overfitting | High | Medium | Walk-forward CV, purged k-fold, regularization |
| Data leakage | Critical | Medium (partly fixed) | Comprehensive audit, point-in-time feature store |
| LLM hallucination | Medium | Medium | Critic agent validation, RAG grounding |
| K8s complexity | Medium | Medium | Start with K3s, managed K8s if needed |

### Business Risks
| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|-----------|
| OJK regulatory change | High | Low | Compliance specialist, legal counsel |
| Market regime shift | High | High | Regime-aware models, adaptive strategy |
| Competitor launches ML | Medium | Medium | First-mover advantage, continuous innovation |
| Key person dependency | High | High | Documentation, cross-training, knowledge sharing |
| Budget overrun | High | Medium | Phased approach, lean team option |

---

## Kesimpulan

Aplikasi ini sudah memiliki **fondasi yang sangat baik** untuk penggunaan pribadi
(arsitektur modular 85 modul, 201 fitur, business rules, risk management lengkap, 24
halaman dashboard, 366 tests, GPU acceleration, AI/LLM agents). Namun untuk menjadi **platform profesional yang sempurna**,
dibutuhkan investasi pada infrastruktur (PostgreSQL, K8s, monitoring) dan frontend (React/Next.js).

### Tiga Skenario Eksekusi:

1. **Full Team (10 orang)** — ~Rp 2.96 Miliar/tahun — semua fase dalam 12 bulan
2. **Lean Team (5 orang)** — ~Rp 1.63 Miliar/tahun — fase kunci dalam 12 bulan
3. **Solo Developer** — ~Rp 350rb-1.45jt/bln — bertahap 18-24 bulan

### Prioritas utama: **ML Engineer + AI/LLM Agent Engineer + Data Engineer** —
ketiga peran ini akan menghasilkan dampak terbesar karena mereka menentukan
akurasi prediksi, kedalaman analisis, dan kualitas data yang menjadi fondasi
seluruh aplikasi.

### Keunggulan kompetitif: Aplikasi ini adalah **satu-satunya** platform di
Indonesia yang menggabungkan ML prediction + inter-market analysis + business
rules + risk management + Fear & Greed Index + market regime detection.

---

## Implementasi Progress (Batch Development)

> Diupdate oleh AI batch development process.

### Batch 1 — ML Engineer ✅
- [x] **Walk-Forward CV** — `src/validation.py`: rolling window & purged k-fold dengan embargo
- [x] **IC / Rank IC / ICIR** — Spearman rank correlation, signal quality metrics
- [x] **Composite AI Score (1-10)** — `src/scoring.py`: multi-dimension rating (Technical, Sentiment, Momentum, Risk)
- [x] **Skill Pack Scoring** — Momentum, Mean Reversion, Risk Radar, Volume Pulse, Sentiment, Macro
- [x] **Feature Selection** — `src/feature_selection.py`: SHAP, Borura, correlation filter, variance threshold
- [x] **Pattern Detection** — `src/patterns.py`: candlestick (Hammer, Doji, Engulfing, dll), chart patterns (H&S, Double Top/Bottom, Triangles)
- [x] **Market Structure** — HH/HL/LH/LL, BOS, CHoCH detection
- [x] **Volume Anomaly** — Z-score based spike/dry-up/climax detection
- [x] **Trendline Detection** — Automated support/resistance trendlines
- [x] **LightGBM Model** — Added to HybridEnsemble (3 models: RF + XGBoost + LightGBM)

### Batch 2 — Quant Finance ✅
- [x] **Entry/Target/Stop** — `src/quant_finance.py`: ATR-based levels, risk-based position sizing
- [x] **Realistic Backtest** — BEI commission (0.15%/0.25%), slippage, T+2 settlement
- [x] **Vectorized Backtest** — Pandas-based, 75x faster than event-driven
- [x] **Pyfolio Tear Sheet** — Sharpe, Sortino, Calmar, VaR, CVaR, alpha/beta, information ratio
- [x] **Wyckoff Phase Detection** — Accumulation/Distribution, Spring, SOS, Upthrust, SOW

### Batch 3 — QA/Test Engineer ✅
- [x] **ML Engineer Tests** — `tests/test_ml_engineer.py`: validation, scoring, patterns, feature selection
- [x] **Quant Finance Tests** — `tests/test_quant_finance.py`: entry/stop, backtest, tear sheet, Wyckoff
- [x] **Integration Tests** — `tests/test_integration.py`: data pipeline, AI agent, API, compliance
- [x] **Total: 90 tests passing**

### Batch 4 — DevOps/MLOps ✅
- [x] **Dockerfile** — Python 3.12-slim, healthcheck, MLflow support
- [x] **docker-compose.yml** — app + api + mlflow services
- [x] **MLflow Tracking** — `src/mlflow_tracking.py`: experiment, model, backtest, feature selection logging
- [x] **CI/CD Pipeline** — `.github/workflows/ci.yml`: lint, test, build (Python 3.11 & 3.12)
- [x] **requirements-dev.txt** — Updated with shap, boruta, scipy, lightgbm, mlflow, fastapi

### Batch 5 — Data Engineer ✅
- [x] **Data Quality Monitoring** — `src/data_pipeline.py`: completeness, freshness, duplicates, validity, anomaly
- [x] **Multi-Source Support** — Yahoo Finance + Alpha Vantage fallback + FRED macro data
- [x] **Feature Store** — Centralized feature computation & caching with parquet persistence
- [x] **Data Lineage Tracking** — Source, timestamp, transformations, quality status

### Batch 6 — AI/LLM Agent Engineer ✅
- [x] **FinBERT Sentiment** — `src/ai_agent.py`: HuggingFace model with lexicon fallback
- [x] **News Scraping** — RSS feeds: Kontan, Bisnis Indonesia, CNBC Indonesia, Reuters, Bloomberg
- [x] **Multi-Agent Framework** — Market Analyst, Risk Manager, News Analyst, Portfolio Advisor
- [x] **Daily AI Briefing** — Comprehensive multi-agent analysis with actionable items

### Batch 7 — Backend Engineer ✅
- [x] **FastAPI REST API** — `src/api.py`: 10 endpoints with OpenAPI docs
- [x] **Pydantic Models** — Type-safe request/response schemas
- [x] **Endpoints**: health, predict, accuracy, patterns, sentiment, briefing, score

### Batch 8 — UI/UX & Compliance ✅
- [x] **OJK Disclaimers** — `src/compliance.py`: risk warning, data disclosure
- [x] **Audit Trail** — SQLite-based audit logging for all predictions and backtests
- [x] **Risk Disclosure** — 5-point risk disclosure statement
- [x] **Compliance Helpers** — log_prediction_audit, log_backtest_audit

### Batch 9 — Production Real-World Readiness ✅
- [x] **Hyperparameter Tuning** — `src/hyperopt.py`: Optuna TPE Bayesian optimization with walk-forward CV
- [x] **Automated Retraining** — `src/retrain_scheduler.py`: Time-based + drift detection (KS test) + performance degradation
- [x] **Real-Time Data Feed** — `src/realtime_feed.py`: Polling + caching, price alerts, IDX market hours
- [x] **Portfolio Risk Management** — `src/portfolio_risk.py`: VaR (historical/parametric/Monte Carlo), correlation-aware allocation, sector exposure, stress testing, Kelly weights
- [x] **Slippage Model** — `src/slippage.py`: Square-root market impact (Almgren-Chriss), bid-ask spread, execution optimization, backtest with slippage
- [x] **Daily Sentiment Pipeline** — `src/sentiment_pipeline.py`: RSS scraping + FinBERT + Fear&Greed index + trend tracking + alerts
- [x] **Tests: 129 total** (31 new production tests)

### Pending (Future Batches)
- [ ] React/Next.js frontend
- [ ] PostgreSQL + TimescaleDB migration
- [ ] Kubernetes deployment
- [ ] Mobile app (React Native)
- [ ] Broker integration (RDN, Mirae)
- [ ] WebSocket real-time streaming (replace polling)

### Batch 11 — Phase 2+ Advanced AI & Quant Modules ✅
- [x] **Transformer Models** — `src/transformer_models.py`: PatchTST, TFT, LPatchTST with PyTorch, ensemble prediction, confidence adjustment
- [x] **Combinatorial Purged CV** — `src/cpcv.py`: CPCV split, backtest paths, PBO (Probability of Backtest Overfitting), purge & embargo
- [x] **Regime-Aware Models** — `src/regime_models.py`: HMM-style regime detection (bull/bear/sideways), regime-specific predictions, confidence adjustment
- [x] **Options Analysis** — `src/options_analysis.py`: Black-Scholes pricing, Greeks (delta/gamma/theta/vega/rho), implied volatility, put-call parity, strategies (straddle, iron condor)
- [x] **Bull vs Bear Debate** — `src/bull_bear_debate.py`: Adversarial AI agents (bull/bear/judge), argument generation from technical indicators, verdict & confidence
- [x] **RAG System** — `src/rag_system.py`: Vector store (TF-IDF + sentence-transformers), document ingestion, semantic search, LLM query with context
- [x] **Alphalens Factor Analysis** — `src/alphalens_analysis.py`: Forward returns, IC (Information Coefficient), quantile returns, turnover, autocorrelation, tear sheet
- [x] **Multi-Horizon Prediction** — `src/multi_horizon.py`: 3-day, 1-week, 1-month, 3-month predictions, consensus signal, horizon agreement
- [x] **Transfer Learning** — `src/transfer_learning.py`: Parent-child model architecture, parent training on multiple tickers, child fine-tuning
- [x] **Trading Memory** — `src/trading_memory.py`: Persistent trade record storage, similar trade query, win/loss statistics, lesson extraction
- [x] **ReAct Agent** — `src/react_agent.py`: Reasoning + Acting framework, multi-tool analysis (technical, regime, sentiment, risk), step-by-step reasoning chain
- [x] **Multi-Mode Research** — `src/multi_mode_research.py`: Fast/deep/custom research modes, module orchestration, comprehensive analysis output
- [x] **Social Sentiment** — `src/social_sentiment.py`: Mock social media posts, keyword & emoji sentiment scoring, engagement weighting, trending detection
- [x] **A/B Testing** — `src/ab_testing.py`: Model performance metrics (MSE, MAE, Sharpe, directional accuracy), paired t-test, Diebold-Mariano test, multi-model comparison
- [x] **Integration** — `src/module_integrator.py`: 13 new methods monkey-patched to ModuleIntegrator class; `src/predictor.py`: 6 new analyses in `_run_advanced_analyses` with confidence adjustments; `src/unified_pipeline.py`: Step 7.5 Phase 2+ analytics block
- [x] **Tests: 42 new tests** (`tests/test_phase2_modules.py`): All 14 modules covered, 330 total tests passing

### Batch 10 — MTF & Advanced Portfolio ✅
- [x] **Multi-Timeframe Analysis (MTF)** — `src/mtf.py`: 4-timeframe confluence (1W/1D/4H/1H), weighted scoring, entry timing, confidence adjustment
- [x] **Black-Litterman Portfolio** — `src/portfolio.py`: Market equilibrium + investor views, implied returns, stable weights
- [x] **Risk Parity Portfolio** — `src/portfolio.py`: Equal risk contribution, inverse volatility fallback, risk contribution analysis
- [x] **Hierarchical Risk Parity (HRP)** — `src/portfolio.py`: Clustering-based allocation, quasi-diagonalization, recursive bisection
- [x] **CVaR Optimization** — `src/portfolio.py`: Rockafellar-Uryasev formulation, tail risk minimization, VaR & CVaR metrics
- [x] **Portfolio Comparison** — `src/portfolio.py`: `compare_portfolio_methods()` — run all 5 methods, sorted by Sharpe
- [x] **MTF Integration** — `src/predictor.py`: Confluence score adjusts confidence, signal override on mixed/strong bearish
- [x] **Tests: 23 new tests** (`tests/test_mtf_portfolio.py`): MTF (9 tests), BL (3), Risk Parity (3), HRP (3), CVaR (3), Comparison (2)
- [x] **Documentation Updated** — `docs/PENGETAHUAN_DASAR_PASAR_MODAL.md`: Section 15 (MTF), Section 16 (Advanced Portfolio)
Dengan eksekusi yang tepat, aplikasi ini bisa menjadi **pionir ML-powered
stock analysis platform** untuk pasar Indonesia.
