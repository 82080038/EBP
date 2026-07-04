# SPESIFIKASI LENGKAP: APLIKASI PROYEKSI & SIMULASI TRADING SAHAM GLOBAL

> **Versi 3.1** — Juni 2026
> Aplikasi AI-driven untuk analisis pasar saham global dan Indonesia, yang tidak
> hanya memberikan sinyal Beli/Jual/Tahan, tetapi juga **mampu mensimulasikan
> strategi trading, melakukan short selling, dan menyesuaikan diri secara otomatis**
> berdasarkan regime pasar dan performa terkini.

## Tujuan Aplikasi

1. **Menganalisis** data pasar global dan lokal (IHSG, saham global, makro, komoditas) dengan ML/AI.
2. **Memprediksi** arah pergerakan harga (UP/DOWN) dan menghasilkan sinyal `BUY` / `SELL` / `HOLD`.
3. **Mensimulasikan** eksekusi trading realistis (komisi, slippage, partial fill, latency) termasuk **short selling**.
4. **Mengembangkan diri** secara otomatis: menyesuaikan parameter strategi, ukuran posisi, stop-loss/take-profit, dan threshold confidence berdasarkan **regime pasar** dan **win rate terkini**.
5. **Melindungi modal** dengan risk management profesional (ATR-based SL/TP, trailing stop, max drawdown, position sizing).

---

## 1. KONSEP DASAR (Core Thesis) — Diperluas

Pasar modal suatu negara (misal: IHSG) **tidak bergerak sendiri**. Ia dipengaruhi
oleh variabel global melalui 5 saluran utama:

### 1.1 Aliran Modal Asing (Foreign Flow)
Investor global menarik/memasukkan dana berdasarkan persepsi risiko global.
Saat risk-off sentiment, capital outflow dari emerging markets termasuk Indonesia.

### 1.2 Sentimen & Persepsi Risiko
Kebijakan global (tarif dagang AS, geopolitik, pandemi) memicu reaksi berantai
di negara berkembang. VIX sebagai barometer "fear index" global.

### 1.3 Variabel Makro Global
Suku bunga The Fed, harga minyak, dan indeks saham global (terutama STI dan
Nasdaq) memiliki korelasi signifikan terhadap IHSG.

### 1.4 Inter-Market Linkages *(BARU)*
Berdasarkan Modern Portfolio Theory dan inter-market analysis:
- **Equity-Bond-Commodity rotation cycle** (Martin Pring framework)
- **Risk-on vs Risk-off regime** menentukan aliran antar aset
- **Lead-lag relationship**: S&P500 memimpin IHSG 1-3 hari (berdasarkan riset)
- **Spread analysis**: Gold/Oil ratio sebagai indikator makro
- **Correlation regime shift**: korelasi berubah saat stress market (tail risk)

### 1.5 Factor-Based Approach *(BARU)*
Berdasarkan Fama-French 5-Factor Model yang diadaptasi untuk pasar Indonesia:
- **Market factor** (beta terhadap IHSG)
- **Size factor** (small vs large cap premium)
- **Value factor** (PB/PE ratio premium)
- **Profitability factor** (ROE/ROA premium)
- **Momentum factor** (12-1 month momentum)
- **Foreign ownership factor** (khusus IDX: % saham asing)

### 1.6 Cara Kerja & Kemampuan Adaptif Aplikasi *(BARU)*

Aplikasi ini bukan sekadar predictor harga. Ia adalah **sistem trading intelligence** yang bekerja dalam siklus tertutup (closed-loop):

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Data Ingestion │────▶│ Feature Engine  │────▶│ ML Ensemble     │
│  (yfinance+FRED)│     │ (215+ fitur)    │     │ (RF+XGB+LGBM)   │
└─────────────────┘     └─────────────────┘     └────────┬────────┘
                                                       │
┌─────────────────┐     ┌─────────────────┐     ┌────▼────────┐
│  Broker Simulator │◀───│  Risk Overlay   │◀───│  Regime &   │
│  (Long/Short)     │     │  (ATR/SL/TP)    │     │  Trend Filter│
└────────┬────────┘     └─────────────────┘     └─────────────┘
         │
         ▼
┌─────────────────┐     ┌─────────────────┐
│  Performance    │────▶│  Auto-Adjust    │
│  Log & Metrics  │     │  Parameter      │
└─────────────────┘     └─────────────────┘
```

#### A. Cara Aplikasi Bekerja

1. **Collect**: Ambil data harian IHSG, saham global, FRED makro, komoditas, dan VIX.
2. **Engineer**: Bangun 215+ fitur (teknikal, inter-market, makro, lag) dengan `prepare_features`.
3. **Predict**: Ensemble RandomForest + XGBoost + LightGBM memberikan arah (UP/DOWN) dan confidence.
4. **Regime Check**: `detect_market_regime` mengklasifikasikan pasar sebagai `bull`, `bear`, `sideways`, `crisis`, dll.
5. **Signal Filter**: Trend filter (MA5 vs MA20), RSI, VIX, MTF, dan Wyckoff mengubah atau menahan sinyal mentah.
6. **Simulate Execution**: `BrokerSimulator` mensimulasikan order MARKET dengan komisi broker, slippage, latency, partial fill, dan rejection.
7. **Risk Management**: Setiap posisi memiliki stop-loss, take-profit, dan trailing stop yang dihitung dari **ATR 14 hari**.
8. **Log & Report**: Semua hari, trade, dan metrik disimpan ke JSON/DB untuk analisis dan verifikasi.

#### B. Bagaimana Aplikasi Mengembangkan & Menyesuaikan Diri

Aplikasi ini memiliki beberapa mekanisme **auto-adjust**:

| Mekanisme | Input | Output | Tujuan |
|-----------|-------|--------|--------|
| **Regime-Based Sizing** | Regime saat ini (`bull`/`bear`/`crisis`) | % modal yang digunakan per trade | Kurangi resiko di bear/crisis, agresif di bull |
| **Regime-Based Confidence** | Regime + win rate 20 trade terakhir | Threshold `confidence_buy` / `confidence_sell` | Naikkan threshold saat kalah sering, turunkan saat menang |
| **Trend-Following Override** | 5 hari berturut-turut bullish/bearish | Sinyal BUY/SELL diutamakan | Hindari perdagangan melawan trend kuat |
| **ATR-Based Stops** | ATR 14 hari | Stop-loss & take-profit dinamis | Stops mengikuti volatilitas, tidak statis |
| **Trailing Stop** | Highest/lowest price since entry | Keluar posisi jika reversal signifikan | Lock profit di tengah trend |
| **Recent Win Rate Feedback** | Realized PnL 20 trade terakhir | Adjust confidence threshold | Hindari over-trading saat model sedang "tidak cocok" dengan pasar |
| **Short Selling** | Signal SELL + bear regime | Buka posisi short | Profit saat pasar turun, tidak hanya pasar naik |

### 1.7 Hasil Verifikasi Terbaru — Bear Market (Juni 2026)

Simulasi walk-forward 3 bulan (Feb–Apr 2026) pada IHSG (^JKSE) menunjukkan kemampuan aplikasi bertahan dan profit di pasar bearish:

| Metrik | Strategi | Buy & Hold | Selisih |
|--------|----------|------------|---------|
| **Total Return** | **+0.28%** | -14.75% | **+15.03%** |
| **Max Drawdown** | -0.99% | > -14% | jauh lebih aman |
| **Win Rate** | 66.7% | — | — |
| **Profit Factor** | 1.82 | — | — |
| **Trades** | 6 (3 short entries, 3 short exits) | — | — |

> **Catatan:** Aplikasi secara otomatis **menghindari posisi long** di bear market dan **membuka posisi short** untuk menangkap profit saat IHSG turun. Semua exit dikontrol oleh ATR-based stop-loss, take-profit, dan trailing stop.

---

## 2. STRATEGI DATA — Komprehensif

### 2.1 Sumber Data Global

| Variabel | Sumber | Format | Biaya | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Saham Global & IHSG** | Yahoo Finance (`yfinance`) | Python Lib | Gratis | ✅ Aktif |
| **Suku Bunga The Fed** | FRED API | REST API | Gratis | ✅ Aktif |
| **Inflasi AS (CPI) & Treasury** | FRED API | REST API | Gratis | ✅ Aktif |
| **Harga Minyak & Emas** | Yahoo Finance (CL=F, GC=F) | Python Lib | Gratis | ✅ Aktif |
| **VIX** | Yahoo Finance (^VIX) | Python Lib | Gratis | ✅ Aktif |
| **USD/IDR** | Yahoo Finance (IDR=X) | Python Lib | Gratis | ✅ Aktif |

### 2.2 Sumber Data Indonesia — Target *(BARU)*

| Variabel | Sumber | Format | Biaya | Priority |
| :--- | :--- | :--- | :--- | :--- |
| **Real-time IDX prices** | Invezgo / Kun Data / iTick | REST + WebSocket | Berbayar | High |
| **Historical IDX (30+ tahun)** | iTick | REST API | Free tier | High |
| **Foreign flow BEI** | BEI website / scraper | CSV/HTML | Gratis | High |
| **Broker summary** | BEI RTI / IDX data | HTML | Gratis | Medium |
| **Index constituents (LQ45, IDX30)** | BEI / IDX Pulse | REST API | Gratis | Medium |
| **Financial statements** | Invezgo / BEI | REST API | Berbayar | Medium |
| **BI Rate & macro Indonesia** | Bank Indonesia API | REST API | Gratis | Medium |

### 2.3 Alternative Data — Future *(BARU)*

| Variabel | Sumber | Format | Priority |
| :--- | :--- | :--- | :--- |
| **News sentiment (ID)** | Kontan RSS, Bisnis, CNBC ID | RSS/Scrape | High |
| **News sentiment (Global)** | Reuters, Bloomberg RSS | RSS/Scrape | High |
| **Social media sentiment** | Twitter/X API, Stockbit | REST API | Medium |
| **Insider trading** | BEI disclosures | HTML | Low |
| **Block trades** | BEI data | HTML | Low |
| **Economic calendar** | Investing.com / Forex Factory | Scrape | Medium |

### 2.4 Data Pipeline Architecture *(BARU)*

```
┌─────────────┐     ┌──────────────┐     ┌───────────────┐
│  Data Sources│────▶│  ETL Pipeline │────▶│  Data Warehouse│
│  (yfinance,  │     │  (Airflow/    │     │  (PostgreSQL + │
│   FRED, IDX  │     │   Prefect)    │     │   TimescaleDB) │
│   API, RSS)  │     │               │     │                │
└─────────────┘     └──────────────┘     └───────┬───────┘
                                                │
                                    ┌───────────▼───────────┐
                                    │   Feature Store (Feast)│
                                    │   - 135+ features      │
                                    │   - Point-in-time      │
                                    │   - Training & serving │
                                    └───────────┬───────────┘
                                                │
                                    ┌───────────▼───────────┐
                                    │  ML Models (MLflow)    │
                                    │  - PatchTST/TFT        │
                                    │  - RF/XGBoost ensemble │
                                    │  - FinBERT sentiment   │
                                    └───────────┬───────────┘
                                                │
                                    ┌───────────▼───────────┐
                                    │  API (FastAPI)         │
                                    │  - REST + WebSocket    │
                                    │  - Celery async tasks  │
                                    └───────────┬───────────┘
                                                │
                         ┌──────────────────────┼──────────────────────┐
                         ▼                      ▼                      ▼
                   ┌──────────┐          ┌──────────┐          ┌──────────┐
                   │ Dashboard │          │  Mobile  │          │ Telegram │
                   │ (Next.js) │          │ (RN app) │          │  Bot     │
                   └──────────┘          └──────────┘          └──────────┘
```

---

## 3. ARSITEKTUR TEKNOLOGI — Evolusi

### 3.1 Tech Stack Saat Ini (Phase 1-2 — Already Running)
- **Backend:** Python + FastAPI REST API (10 endpoints)
- **Frontend:** Streamlit (24 halaman dashboard)
- **Database:** SQLite (`saham_prediksi.db`)
- **ML:** RF + XGBoost + LightGBM + PatchTST/TFT/LPatchTST (GPU CUDA) + Kronos (Hybrid Ensemble)
- **Data:** yfinance + FRED API + RSS feeds + Alpha Vantage + Finnhub
- **Otomatisasi:** GitHub Actions cron job
- **Notifikasi:** Telegram Bot + Email SMTP
- **MLOps:** MLflow + drift detection (PSI/KS) + automated retraining
- **AI/LLM:** Multi-agent system (Analyst, Risk, News, Portfolio) + RAG + ReAct + Bull vs Bear
- **GPU:** PyTorch CUDA + LightGBM GPU

### 3.2 Tech Stack Target (Phase 5 — "Sempurna")

| Layer | Saat Ini | Target |
|-------|----------|--------|
| **Frontend** | Streamlit | React/Next.js 14+ + TradingView Charts |
| **Backend** | FastAPI (10 endpoints) | + WebSocket + Celery |
| **Database** | SQLite | PostgreSQL + TimescaleDB + Redis |
| **ML Models** | RF + XGBoost + LightGBM + PatchTST/TFT (GPU) | + CatBoost, TFT-GNN |
| **NLP** | FinBERT + RSS + LLM Agents | + Social media, LangGraph |
| **Data** | yfinance only | IDX API + yfinance + RSS + social |
| **MLOps** | MLflow + drift detection | + Evidently AI + Airflow |
| **Deployment** | Docker + GitHub Actions | + K8s + Terraform |
| **Monitoring** | drift_monitor.py | + Prometheus + Grafana + Evidently AI |
| **Mobile** | - | React Native + FCM/APNs |
| **Feature Store** | - | Feast |
| **Vector DB** | RAG system (`rag_system.py`) | Qdrant (RAG & semantic cache) |
| **Broker** | BrokerSimulator (simulasi) | Mirae Asset API / IPOT API |

### 3.3 Proyek Open-Source Rujukan (Updated 2025)
1. **TradingAgents** — Multi-Agent LLM Financial Trading Framework (arXiv:2412.20138)
2. **stock-agent-ops** — Transfer Learning LSTM + LangGraph agentic AI
3. **AlphaPulse** — Production-grade MLOps for financial market data
4. **StockPriceMLPipeline** — End-to-end MLOps dengan FastAPI + MLflow + Redis
5. **StockPatchTST** — PatchTST-based stock ranking dengan LambdaRank loss
6. **FinBERT** (ProsusAI) — Financial sentiment analysis pre-trained model

---

## 4. ALUR PENGAMBILAN KEPUTUSAN — Detail Pipeline

### 4.1 Pipeline Harian (Daily Prediction Flow)

```
[09:00 WIB] Market Open
    │
    ▼
[1. Data Ingestion]
    ├── yfinance: fetch ^JKSE, ^GSPC, ^IXIC, ^STI, ^N225, ^HSI, GC=F, CL=F, ^VIX
    ├── FRED API: fetch FEDFUNDS, CPI, DGS10
    ├── IDX API (future): real-time prices, foreign flow
    └── RSS feeds (future): news headlines for sentiment
    │
    ▼
[2. Preprocessing & Feature Engineering]
    ├── Merge all data into single DataFrame (outer join, ffill/bfill)
    ├── Calculate 201+ features:
    │   ├── Technical: RSI, MA5/10/20, MACD, BB, Stochastic, ATR, OBV, ADX
    │   ├── Inter-market: Gold/IHSG ratio, Oil/IHSG ratio, rolling correlation
    │   ├── Cross-market returns: S&P500 ret, NASDAQ ret, STI ret (lag 1,2,3,5)
    │   ├── Macro: FEDFUNDS change, CPI change, Treasury 10Y change
    │   ├── Volatility: VIX return, VIX MA10, IHSG volatility 20d
    │   └── Lag features: all features lagged 1, 2, 3, 5 days
    ├── Target: Target_Next_Return (shift -1) → binary UP/DOWN
    └── Time-based train/test split (80/20)
    │
    ▼
[3. ML Prediction — Hybrid Ensemble]
    ├── RandomForest: 200 trees, max_depth=10, MinMaxScaler
    ├── XGBoost: 200 trees, max_depth=6, lr=0.1, MinMaxScaler
    ├── LSTM (optional): 50 units, 2 layers, 50 epochs
    ├── LightGBM: 200 trees, max_depth=6, lr=0.1, MinMaxScaler (GPU-accelerated)
    ├── PatchTST/TFT: Transformer models with GPU (PyTorch CUDA)
    ├── Future: CatBoost, TFT-GNN
    └── Voting: majority vote → BUY/SELL
    │
    ▼
[4. Business Rules Layer]
    ├── Rule 1: Trend Follower (MA5>MA10>MA20 = Bullish)
    ├── Rule 2: Anti-FOMO (RSI>70 → downgrade BUY to HOLD)
    ├── Rule 3: VIX Panic (VIX>30 → downgrade BUY to HOLD)
    ├── Rule 4: Oversold (RSI<30 + bullish trend → downgrade SELL to HOLD)
    ├── Rule 5: Upgrade HOLD→BUY if bullish + not overbought
    ├── Rule 6: Upgrade HOLD→SELL if bearish
    └── Rule 7: Confidence < 0.55 → HOLD
    │
    ▼
[5. Risk Management Overlay]
    ├── VaR (95%, 99% confidence)
    ├── CVaR (Expected Shortfall)
    ├── Kelly Criterion → position sizing
    ├── Max Drawdown check
    └── Sharpe/Sortino ratio assessment
    │
    ▼
[6. AI/LLM Agent Layer (Future)]
    ├── Performance Analyst: interpret ML output + indicators
    ├── Market Expert: news sentiment + event detection
    ├── Report Generator: professional markdown report
    └── Critic: validate consistency before sending
    │
    ▼
[7. Output & Notification]
    ├── Save to database (prediksi table)
    ├── Telegram notification (formatted message)
    ├── Email notification (SMTP)
    ├── Dashboard update (Streamlit → Next.js)
    └── Mobile push notification (future)
```

### 4.2 Business Rules — Expanded

| Rule | Kondisi | Aksi | Rationale |
|------|---------|------|-----------|
| Trend Follower | MA5 > MA10 > MA20 | Bullish bias | Follow the trend, don't fight the tape |
| Anti-FOMO | RSI > 70 | BUY → HOLD | Avoid buying at overbought levels |
| VIX Panic | VIX > 30 | BUY → HOLD | Global fear = high risk, wait for calm |
| Oversold Bounce | RSI < 30 + Bullish trend | SELL → HOLD | Potential bounce, don't sell at bottom |
| Confidence Filter | Confidence < 55% | → HOLD | Low conviction = no action |
| Foreign Outflow | Foreign sell > 1T IDR | BUY → HOLD | Heavy foreign selling = bearish signal |
| Rate Hike Risk | Fed hike expected < 7 days | BUY → HOLD | Rate hike = capital outflow risk |
| Weekend Effect | Friday close | Reduce position | Monday gap risk |
| Holiday Effect | Market closed > 2 days | Reduce position | Gap risk upon reopening |

---

## 5. STRATEGI FEATURE ENGINEERING — Detail

### 5.1 Fitur Teknikal (35 fitur)

| Kategori | Fitur | Formula/Source |
|----------|-------|----------------|
| **Moving Averages** | MA5, MA10, MA20, MA50 | Simple moving average |
| **Momentum** | RSI(14), MACD(12,26,9), Stochastic(14,3) | Standard formulas |
| **Volatility** | ATR(14), Bollinger Bands(20,2) | Standard formulas |
| **Volume** | OBV, MFI(14), Volume MA | Volume-based |
| **Trend** | ADX(14), Parabolic SAR, Ichimoku | Trend strength |
| **Other** | Williams %R, CCI(20), VWAP | Oscillators |

### 5.2 Fitur Inter-Market (25 fitur) *(BARU)*

| Fitur | Deskripsi |
|-------|-----------|
| S&P500_Return_lag1/2/3/5 | S&P500 daily return lagged |
| NASDAQ_Return_lag1/2/3/5 | NASDAQ return lagged |
| STI_Return_lag1/2/3/5 | Singapore STI return lagged |
| Nikkei_Return_lag1/2/3 | Japan Nikkei return lagged |
| HSI_Return_lag1/2/3 | Hang Seng return lagged |
| Gold_Return | Gold daily return |
| Oil_Return | Oil daily return |
| VIX_Return | VIX daily return |
| VIX_MA10 | VIX 10-day moving average |
| Gold_IHSG_Ratio | Gold/IHSG ratio (safe haven) |
| Oil_IHSG_Ratio | Oil/IHSG ratio (commodity) |
| USD_IDR_Return | USD/IDR daily return |
| Rolling_Corr_IHSG_SP500 | 60-day rolling correlation |
| Rolling_Corr_IHSG_STI | 60-day rolling correlation |

### 5.3 Fitur Makro (10 fitur)

| Fitur | Source |
|-------|--------|
| FEDFUNDS | FRED API |
| FEDFUNDS_Change | Fed rate change |
| CPI_YoY | US inflation |
| Treasury_10Y | 10Y yield |
| Treasury_Spread | 10Y - 2Y spread (yield curve) |
| BI_Rate (future) | Bank Indonesia |
| IDR_Foreign_Reserves (future) | BI data |

### 5.4 Fitur Sentiment (future, 15+ fitur) *(BARU)*

| Fitur | Source |
|-------|--------|
| Fear_Greed_Index | Composite (7 components) |
| News_Sentiment_ID | FinBERT on Kontan/Bisnis/CNBC |
| News_Sentiment_Global | FinBERT on Reuters/Bloomberg |
| Social_Sentiment | Twitter/X NLP |
| Foreign_Flow_Net | BEI foreign buy - sell |
| Market_Breadth | Advance/decline ratio |
| Put_Call_Ratio (future) | Options data |

### 5.5 Feature Selection Strategy *(BARU)*

1. **SHAP values** — identify feature importance per model
2. **Boruta** — all-relevant feature selection
3. **Recursive Feature Elimination (RFE)** — iteratively remove weak features
4. **Correlation filter** — remove features with > 0.95 correlation
5. **Variance threshold** — remove near-zero variance features
6. **Target: reduce from 201 → 40-60 high-signal features**

---

## 6. ARSITEKTUR MODEL ML — Detail

### 6.1 Model Saat Ini

| Model | Type | Parameters | Role |
|-------|------|------------|------|
| **RandomForest** | Tree ensemble | 200 trees, depth=10 | Base voter |
| **XGBoost** | Gradient boosting | 200 trees, depth=6, lr=0.1 | Base voter |
| **LightGBM** | Gradient boosting (GPU) | 200 trees, depth=6, lr=0.1, device=gpu | Base voter |
| **PatchTST** | Transformer (GPU) | d_model=64, n_heads=4, n_layers=2 | Long-horizon forecast |
| **TFT** | Transformer (GPU) | hidden=64, lstm_layers=1 | Multi-covariate forecast |
| **LPatchTST** | Hybrid LSTM+Transformer (GPU) | LSTM + PatchTST | Production trading |
| **Kronos** | Foundation model | Zero-shot forecasting | Ensemble member |
| **HybridEnsemble** | Voting | Majority vote | Final prediction |

### 6.2 Model Target — State-of-the-Art 2025

| Model | Type | Keunggulan | Status |
|-------|------|------------|--------|
| **PatchTST** | Transformer | Patch tokenization, 21% MSE reduction | ✅ Implemented |
| **TFT** | Transformer | Multi-horizon, interpretable attention | ✅ Implemented |
| **LPatchTST** | Hybrid LSTM+Transformer | Sharpe ratio 2.31, robust drawdown | ✅ Implemented |
| **TFT-GNN** | Transformer+GNN | Captures inter-asset relations | Future |
| **LightGBM** | Gradient boosting | Faster than XGBoost, leaf-wise | ✅ Implemented (GPU) |
| **CatBoost** | Gradient boosting | Handles categorical natively | Future |
| **FinBERT** | NLP/Transformer | Financial sentiment classification | ✅ Implemented |
| **LLM Agents** | Multi-agent AI | Bloomberg-quality reports | Analysis & reporting |

### 6.3 Regime-Aware Model Architecture *(BARU)*

```
                    ┌─── Market Regime Detection ───┐
                    │  (MA alignment + ADX + VIX)   │
                    └───────────┬───────────────────┘
                                │
                    ┌───────────▼───────────────────┐
                    │     Regime Classifier          │
                    │  BULL / BEAR / SIDEWAYS        │
                    └───┬───────────┬───────────┬────┘
                        │           │           │
                   ┌────▼───┐  ┌───▼────┐  ┌───▼────┐
                   │ Bull   │  │ Bear   │  │Side-   │
                   │ Model  │  │ Model  │  │ways    │
                   │ (trend │  │ (mean  │  │ Model  │
                   │ follow)│  │ revert)│  │(range) │
                   └────┬───┘  └───┬────┘  └───┬────┘
                        │          │           │
                        └──────────┼───────────┘
                                   │
                        ┌──────────▼──────────┐
                        │  Ensemble Combiner   │
                        │  (weighted by regime │
                        │   confidence)        │
                        └─────────────────────┘
```

### 6.4 Transfer Learning Strategy *(BARU)*

```
[Parent Model]
    ├── Trained on ^JKSE (IHSG index) — 2+ years data
    ├── Learns general Indonesian market patterns
    └── Fine-tuned for each blue chip:
        ├── BBCA → Child Model (banking sector)
        ├── BBRI → Child Model (banking sector)
        ├── TLKM → Child Model (telecom sector)
        ├── ASII → Child Model (automotive)
        ├── UNVR → Child Model (consumer goods)
        ├── GOTO → Child Model (tech)
        ├── ICBP → Child Model (food)
        └── ADRO → Child Model (mining/energy)
```

### 6.5 Validation Strategy *(BARU)*

| Method | Deskripsi | Kapan Digunakan |
|--------|-----------|-----------------|
| **Walk-Forward CV** | Rolling window train→test, slide forward | Time-series utama |
| **Purged K-Fold** | K-fold dengan embargo period setelah train | Hindari leakage |
| **Combinatorial CV** | Multiple train/test combinations | Robust evaluation |
| **Backtesting** | Historical simulation dengan realistic constraints | Final validation |
| **Forward testing** | Paper trading dengan real-time data | Pre-production |

---

## 7. STRATEGI PENGUKURAN AKURASI — Diperluas

### 7.1 Database Schema (Updated)

```sql
CREATE TABLE prediksi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticker TEXT NOT NULL,
    tanggal_prediksi DATE NOT NULL,
    tanggal_target DATE NOT NULL,
    harga_saat_ini REAL,           -- Harga saat prediksi dibuat
    harga_prediksi REAL NOT NULL,
    harga_aktual REAL,             -- NULL (diisi saat verifikasi)
    arah_prediksi TEXT,            -- 'UP' atau 'DOWN'
    arah_aktual TEXT,              -- NULL (diisi saat verifikasi)
    sinyal TEXT,                   -- 'BUY' / 'SELL' / 'HOLD'
    confidence REAL,               -- 0.0 - 1.0
    model_votes TEXT,              -- JSON: {RF: 1, XGB: 0, ...}
    rules_triggered TEXT,          -- JSON: which business rules fired
    regime TEXT,                   -- 'BULL' / 'BEAR' / 'SIDEWAYS'
    updated_at TIMESTAMP
);

CREATE TABLE harga_harian (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticker TEXT NOT NULL,
    tanggal DATE NOT NULL,
    open REAL, high REAL, low REAL, close REAL, volume REAL,
    UNIQUE(ticker, tanggal)
);

CREATE TABLE log_aktivitas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    aktivitas TEXT NOT NULL,
    detail TEXT
);
```

### 7.2 Metrik Pengukuran — Komprehensif

#### A. Prediction Accuracy
| Metrik | Formula | Target |
|--------|---------|--------|
| **Directional Accuracy (DA)** | (Benar / Total) × 100% | > 55% |
| **MAPE** | mean(\|Aktual - Prediksi\| / Aktual) × 100% | < 3% |
| **RMSE** | sqrt(mean((Aktual - Prediksi)²)) | Minimize |
| **Log Loss** | -mean(y log(p) + (1-y) log(1-p)) | < 0.69 |

#### B. Risk-Adjusted Performance *(BARU)*
| Metrik | Formula | Target |
|--------|---------|--------|
| **Sharpe Ratio** | (Return - Rf) / Std(Return) | > 1.0 |
| **Sortino Ratio** | (Return - Rf) / Std(Downside) | > 1.5 |
| **Max Drawdown** | Max(Peak - Trough) / Peak | < 20% |
| **Calmar Ratio** | Annual Return / Max Drawdown | > 0.5 |
| **Information Ratio** | (Alpha) / Tracking Error | > 0.5 |

#### C. Trading Performance *(BARU)*
| Metrik | Deskripsi | Target |
|--------|-----------|--------|
| **Win Rate** | % profitable trades | > 50% |
| **Profit Factor** | Gross Profit / Gross Loss | > 1.5 |
| **Expectancy** | (Win% × Avg Win) - (Loss% × Avg Loss) | > 0 |
| **Kelly Fraction** | W - (1-W)/R | > 0.25 |
| **Maximum Consecutive Losses** | Longest losing streak | < 5 |

### 7.3 Otomatisasi Verifikasi

| Jadwal | WIB | Aksi | Platform |
|--------|-----|------|----------|
| **Prediksi** | 14:30 | Fetch data → train → predict → save → notify | GitHub Actions / Airflow |
| **Verifikasi** | 22:30 | Fetch aktual → compare → update DB → calc metrics | GitHub Actions / Airflow |
| **Weekly Report** | Jumat 16:00 | Generate AI report → send Telegram/Email | Airflow + LLM Agent |
| **Monthly Backtest** | 1st of month | Full backtest → model retrain if needed | Airflow + MLflow |

---

## 8. RISK MANAGEMENT FRAMEWORK *(BARU)*

### 8.1 Risk Metrics

```
┌─────────────────────────────────────────────────────┐
│              RISK MANAGEMENT LAYER                   │
├─────────────────────────────────────────────────────┤
│                                                     │
│  1. Value at Risk (VaR)                             │
│     ├── Historical VaR (95%, 99%)                   │
│     └── Parametric VaR (variance-covariance)        │
│                                                     │
│  2. Conditional VaR (CVaR / Expected Shortfall)     │
│     └── Average loss beyond VaR threshold           │
│                                                     │
│  3. Sharpe & Sortino Ratio                          │
│     ├── Sharpe: (Return - Rf) / Total Volatility    │
│     └── Sortino: (Return - Rf) / Downside Volatility│
│                                                     │
│  4. Maximum Drawdown & Duration                     │
│     ├── Max DD: largest peak-to-trough decline      │
│     └── DD Duration: longest underwater period      │
│                                                     │
│  5. Kelly Criterion                                 │
│     ├── Full Kelly: optimal aggressive sizing       │
│     ├── Half Kelly: recommended for safety          │
│     └── Quarter Kelly: conservative                 │
│                                                     │
│  6. Position Sizing                                 │
│     ├── Risk-based: capital × risk% / risk per share│
│     ├── ATR-based: stop = entry - 2×ATR             │
│     └── Fixed fractional: % of capital per trade    │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### 8.2 Risk Rules

| Rule | Threshold | Aksi |
|------|-----------|------|
| **Max position size** | 20% of portfolio | Reject larger orders |
| **Max daily loss** | 3% of portfolio | Stop trading for the day |
| **Max drawdown** | 15% | Reduce position size by 50% |
| **Min confidence** | 55% | HOLD (no action) |
| **VIX extreme** | > 35 | Reduce all positions to 50% |
| **Foreign outflow** | > 2T IDR/day | No new BUY positions |
| **Correlation spike** | All assets correlated > 0.8 | Diversification warning |

---

## 9. PORTFOLIO OPTIMIZATION STRATEGY *(BARU)*

### 9.1 Current: Simple Markowitz
- Monte Carlo simulation (5000 random portfolios)
- Efficient frontier visualization
- Max Sharpe portfolio
- Min Volatility portfolio

### 9.2 Target: Multi-Model Approach

| Model | Deskripsi | When to Use |
|-------|-----------|-------------|
| **Markowitz (MVO)** | Mean-Variance Optimization | Normal market |
| **Black-Litterman** | Combine market views + equilibrium | When have directional views |
| **Risk Parity** | Equal risk contribution | Low volatility target |
| **HRP** | Hierarchical Risk Parity (clustering) | Correlated assets |
| **CVaR Optimization** | Minimize tail risk | High volatility regime |
| **Kelly Optimal** | Maximize long-term growth | High confidence signals |

### 9.3 Portfolio Constraints (IDX-specific)
- **Commission:** 0.15% beli, 0.25% jual
- **Settlement:** T+2
- **Minimum lot:** 100 shares (1 lot)
- **Fractional shares:** Not available on IDX
- **Short selling:** Restricted on IDX
- **Margin:** Available with broker approval

---

## 10. SENTIMENT ANALYSIS STRATEGY *(BARU)*

### 10.1 Current: Price-Based Fear & Greed Index
7 komponen: VIX, Momentum, RSI, Safe Haven, Market Breadth, Volatility Trend, Oil Sentiment

### 10.2 Target: Multi-Source Sentiment

```
┌─────────────────────────────────────────────────────┐
│           SENTIMENT ANALYSIS PIPELINE                │
├─────────────────────────────────────────────────────┤
│                                                     │
│  [News Sources]                                     │
│  ├── Kontan RSS → FinBERT → sentiment score         │
│  ├── Bisnis Indonesia → FinBERT → sentiment score   │
│  ├── CNBC Indonesia → FinBERT → sentiment score     │
│  ├── Reuters → FinBERT → sentiment score            │
│  └── Bloomberg RSS → FinBERT → sentiment score      │
│                                                     │
│  [Social Media]                                     │
│  ├── Twitter/X → NLP → sentiment score              │
│  ├── Stockbit forum → NLP → sentiment score         │
│  └── Reddit r/IndonesiaInvesting → NLP              │
│                                                     │
│  [Market-Based]                                      │
│  ├── VIX → Fear component                           │
│  ├── Foreign flow → Institutional sentiment         │
│  ├── Put/Call ratio → Options sentiment (future)    │
│  └── Advance/Decline → Market breadth               │
│                                                     │
│  [Composite]                                         │
│  └── Weighted average → Fear & Greed Index (0-100)  │
│      ├── 0-24: Extreme Fear → Contrarian BUY        │
│      ├── 25-44: Fear → Cautious BUY                 │
│      ├── 45-55: Neutral → HOLD                      │
│      ├── 56-75: Greed → Cautious SELL               │
│      └── 76-100: Extreme Greed → Contrarian SELL    │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### 10.3 LLM Agent Integration (Future)

| Agent | Role | Input | Output |
|-------|------|-------|--------|
| **Performance Analyst** | Interpret ML + indicators | Prediction, indicators | Technical analysis summary |
| **Market Expert** | News + sentiment | RSS feeds, social | Sentiment report |
| **Report Generator** | Synthesize all | All agent outputs | Professional report (Markdown) |
| **Critic** | Validate | Report draft | Approved/rejected report |

---

## 11. EKSPEKTASI AKURASI — Updated

| Skenario | Akurasi | Catatan |
| :--- | :--- | :--- |
| **Pro analis (rata-rata)** | ~48% | Lebih buruk dari lemparan koin |
| **ML model (backtest)** | 70-85% | Sering overfit, tidak realistis |
| **ML model (real-time)** | 50-55% | Ekspektasi realistis |
| **ML + business rules** | 52-58% | Business rules membantu filter false positive |
| **ML + sentiment + rules** | 55-60% | Target dengan NLP integration |
| **PatchTST/TFT (target)** | 55-62% | State-of-the-art transformer models |
| **> 60% konsisten** | Exceptional | Setara hedge fund level (jarang) |

**Disclaimer:** Prediksi pasar saham bersifat probabilistik. **Tidak ada jaminan**
profit. Gunakan hasil sebagai alat bantu analisis, bukan satu-satunya dasar
keputusan investasi. Terapkan manajemen risiko (Stop Loss, diversifikasi) dan
lakukan *paper trading* terlebih dahulu.

---

## 12. CONTOH KASUS — Updated 2025

### 12.1 Sukses
1. **FinBERT-LSTM (2024-2025)** — Sentiment-augmented PatchTST mencapai
   526% cumulative return dengan Sharpe ratio 0.407 (ResearchGate)
2. **StockPatchTST (2025)** — PatchTST ranking model di KRX: 74.29% win rate
   pada 2025, avg win +7.54%, avg loss -3.88% (GitHub: purefe11)
3. **TradingAgents (2024)** — Multi-agent LLM framework setara analis
   Bloomberg (arXiv:2412.20138)
4. **DeepSeek + A-Shares** — Return tahunan 28.7% dalam uji coba 6 bulan
5. **stock-agent-ops** — Transfer learning LSTM + LangGraph, auto-healing
   pipeline (GitHub)

### 12.2 Peringatan
1. **OpenClaw (Si Lobster)** — Profit 90% di simulasi, tapi rugi Rp 80 juta
   di pasar nyata. **Backtest ≠ real trading**
2. **Overfitting trap** — Model 85% DA di backtest → 48% di real-time.
   **Selalu gunakan walk-forward validation**
3. **Black swan events** — COVID-19 (Mar 2020), geopolitical conflicts.
   Model tidak bisa memprediksi event ekstrem.

---

## 13. REGULASI & COMPLIANCE — Diperluas

### 13.1 OJK (Otoritas Jasa Keuangan)
- **Personal use:** Tidak perlu izin OJK
- **Jika digunakan orang lain:** Prinsip "Same Activity, Same Risk, Same Regulation"
- **Disclaimer wajib:** "Prediksi bukan saran investasi"
- **Investment Manager license:** Diperlukan jika mengelola dana orang lain
- **Robo-advisor:** Memerlukan izin OJK sebagai Penasihat Investasi

### 13.2 UU PDP (Perlindungan Data Pribadi) *(BARU)*
- **Data user:** Nama, email, phone, portfolio data = data pribadi
- **Consent:** Harus ada opt-in untuk collect data
- **Encryption:** Data pribadi harus dienkripsi (at rest & in transit)
- **Retention:** Data tidak disimpan lebih lama dari necessary
- **Right to erasure:** User bisa request hapus data
- **Data breach:** Wajib lapor ke OJK dalam 72 jam

### 13.3 BEI (Bursa Efek Indonesia)
- **Auto-trading:** Harus melalui broker yang terdaftar BEI
- **API broker:** Mirae Asset, IPOT, BNI Sekuritas menyediakan trading API
- **Short selling:** Terbatas, hanya untuk saham tertentu
- **Margin trading:** Perlu akun margin & approval broker

### 13.4 Audit Trail
- Log semua prediksi dengan timestamp & model version
- Log semua trading signals dengan rationale
- Log semua business rules yang triggered
- Retention: minimum 5 tahun untuk audit

---

## 14. DEPLOYMENT & MONITORING STRATEGY *(BARU)*

### 14.1 Deployment Architecture

```
┌─────────────────────────────────────────────────────┐
│                  PRODUCTION                          │
│                                                     │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐            │
│  │ API     │  │ Worker  │  │ Dashboard│            │
│  │ (FastAPI)│  │(Celery) │  │(Next.js) │            │
│  └────┬────┘  └────┬────┘  └────┬────┘            │
│       │            │            │                    │
│  ┌────▼────────────▼────────────▼────┐              │
│  │          Kubernetes Cluster        │              │
│  │          (K3s / managed K8s)       │              │
│  └────┬────────────┬────────────┬────┘              │
│       │            │            │                    │
│  ┌────▼────┐ ┌────▼────┐ ┌────▼────┐               │
│  │PostgreSQL│ │  Redis  │ │ MLflow  │               │
│  │+TimeScale│ │ (cache) │ │ (model  │               │
│  │  DB      │ │         │ │  registry│              │
│  └─────────┘ └─────────┘ └─────────┘               │
│                                                     │
│  ┌─────────────────────────────────────┐            │
│  │         Monitoring Stack             │            │
│  │  Prometheus → Grafana → AlertManager │            │
│  │  Evidently AI → Drift Detection      │            │
│  └─────────────────────────────────────┘            │
└─────────────────────────────────────────────────────┘
```

### 14.2 Monitoring & Alerting

| Metric | Threshold | Alert Method |
|--------|-----------|-------------|
| API latency | > 500ms p95 | Telegram + Grafana |
| Model DA drop | > 5% from baseline | Telegram + Email |
| Data pipeline failure | Any failure | Telegram + PagerDuty |
| Data drift (PSI) | > 0.2 | Evidently AI report |
| Prediction confidence | < 40% or > 95% | Anomaly alert |
| DB disk usage | > 80% | Grafana warning |
| Model training time | > 30 min | Performance alert |

### 14.3 CI/CD Pipeline

```
[Git Push] → [Lint & Type Check] → [Unit Tests] → [Integration Tests]
    → [Build Docker Image] → [Push to Registry] → [Deploy Staging]
    → [E2E Tests] → [Manual Approval] → [Deploy Production]
    → [Health Check] → [Monitor]
```

---

## 15. ACTION PLAN — Phased Roadmap

### Phase 1: Foundation (Bulan 1-3) — ✅ Selesai
- [x] Setup yfinance + FRED data pipeline
- [x] Build preprocessor with 201 features
- [x] Train RF + XGBoost + LightGBM hybrid ensemble (+ PatchTST/TFT)
- [x] Implement business rules layer
- [x] Build Streamlit dashboard (24 pages)
- [x] Setup SQLite database
- [x] Telegram + Email notification
- [x] GitHub Actions cron job
- [x] Fix data leakage bugs (predictor, backtesting, verification)
- [x] Add retry/backoff for yfinance
- [x] 481 unit/integration tests
- [x] Walk-forward CV implementation (`validation.py`)
- [x] Hyperparameter tuning — Optuna TPE (`hyperopt.py`)
- [x] Feature selection — SHAP/Boruta (`feature_selection.py`)
- [x] Docker containerization
- [x] MLflow setup (`mlflow_tracking.py`)

### Phase 2: Intelligence (Bulan 4-6) — ✅ Selesai
- [ ] Add IDX API (Invezgo/Kun Data) for real-time data ❌
- [x] Implement FinBERT news sentiment (`sentiment_pipeline.py`)
- [x] Build multi-agent system (`ai_agent.py` — 4 agents, `bull_bear_debate.py`, `react_agent.py`)
- [x] Train PatchTST / TFT model (`transformer_models.py` with GPU)
- [x] Implement transfer learning (`transfer_learning.py`)
- [x] Build regime-aware model architecture (`regime.py`, `regime_models.py`)
- [x] Add Black-Litterman portfolio optimization (`portfolio.py` — 5 methods)
- [x] Event-driven backtesting engine (`event_backtest.py`)
- [ ] Setup Airflow ETL pipeline ❌

### Phase 3: Scale (Bulan 7-9) — Sebagian Selesai
- [x] Build FastAPI REST API (`api.py` — 10 endpoints)
- [ ] Migrate SQLite → PostgreSQL + TimescaleDB ❌
- [ ] Setup Redis caching layer ❌
- [ ] Implement Celery async task queue ❌
- [ ] Deploy on Kubernetes (K3s) ❌
- [ ] Setup Prometheus + Grafana monitoring ❌
- [x] Drift detection (`drift_monitor.py` — PSI + KS-test)
- [x] Comprehensive integration tests (481 tests)
- [ ] Load testing (k6/Locust) ❌

### Phase 4: Experience (Bulan 10-12) — Sebagian Selesai
- [ ] Build React/Next.js dashboard ❌
- [ ] Integrate TradingView Lightweight Charts ❌
- [ ] Build React Native mobile app ❌ (di luar scope)
- [ ] Implement push notifications (FCM/APNs) ❌
- [ ] Design system (Figma → shadcn/ui) ❌
- [x] OJK compliance framework (`compliance.py`)
- [ ] UU PDP compliance ❌
- [x] Audit trail implementation (`compliance.py`, `database.py`)
- [ ] E2E tests (Playwright) ❌

### Phase 5: Automation (Bulan 13+) — Sebagian Selesai
- [ ] Broker API integration (Mirae Asset / IPOT) ❌
- [ ] Auto-trading execution engine ❌
- [x] VWAP/TWAP execution algorithms (`execution_algo.py`)
- [ ] Kafka real-time streaming pipeline ❌
- [x] Advanced portfolio — Risk Parity, HRP, CVaR (`portfolio.py`)
- [ ] Options analysis (Greeks, IV) ❌
- [ ] Foreign flow BEI integration ❌
- [x] Social media sentiment (`social_sentiment.py`)

---

## 16. KPI & SUCCESS METRICS *(BARU)*

### 16.1 Model KPI
| KPI | Target | Measurement |
|-----|--------|-------------|
| Directional Accuracy (real-time) | > 55% | Daily verification |
| MAPE | < 3% | Daily verification |
| Sharpe Ratio (paper trading) | > 1.0 | Monthly backtest |
| Max Drawdown | < 15% | Rolling 252 days |
| Model drift (PSI) | < 0.1 | Weekly Evidently AI |

### 16.2 System KPI
| KPI | Target | Measurement |
|-----|--------|-------------|
| API latency (p95) | < 200ms | Prometheus |
| Uptime | > 99.5% | Grafana |
| Data freshness | < 5 min | Airflow sensors |
| Test coverage | > 80% | pytest-cov |
| Deployment frequency | Weekly | CI/CD metrics |

### 16.3 Business KPI
| KPI | Target | Measurement |
|-----|--------|-------------|
| Active users (if multi-user) | Growing | Analytics |
| Notification delivery rate | > 99% | Telegram API logs |
| User satisfaction (NPS) | > 50 | Survey |
| Paper trading return | > IHSG return | Monthly comparison |

---

## 17. FITUR TERBUKTI DARI KOMPETITOR — Adoptasi *(BARU)*

Berdasarkan analisis 14 aplikasi kompetitor (lihat `COMPETITIVE_ANALYSIS.md`),
berikut fitur-fitur terbukti yang akan diadopsi:

### 17.1 Signal Evaluation Metrics (dari Microsoft Qlib)

Saat ini hanya menggunakan Directional Accuracy & MAPE. Qlib (44K GitHub stars)
membuktikan bahwa **IC (Information Coefficient)** lebih baik untuk evaluate signal:

| Metrik | Formula | Interpretasi |
|--------|---------|--------------|
| **IC** | Correlation(predicted_score, actual_return) | > 0.05 = good, > 0.1 = excellent |
| **Rank IC** | Correlation(rank(predicted), rank(actual)) | Lebih robust terhadap outliers |
| **ICIR** | mean(IC) / std(IC) | Consistency of signal (> 0.5 = good) |
| **Rank ICIR** | mean(RankIC) / std(RankIC) | Consistency of ranked signal |

### 17.2 Composite AI Score (dari Kavout & Danelfin)

Mengganti sinyal BUY/SELL/HOLD dengan **Composite AI Score** yang lebih informatif:

```
AI Score (1-10) = weighted_average(
    Technical_Score (0-100),    # dari 35+ technical indicators
    Fundamental_Score (0-100),   # dari PE, PB, ROE, debt ratio (future)
    Sentiment_Score (0-100),     # dari FinBERT + Fear & Greed
    InterMarket_Score (0-100),   # dari inter-market analysis
    Macro_Score (0-100)          # dari FRED + BI data
)

Score Interpretation:
  9-10: Strong BUY  (probability > 70% beat market)
  7-8:  BUY         (probability 55-70%)
  5-6:  HOLD        (probability 45-55%)
  3-4:  SELL        (probability 30-45%)
  1-2:  Strong SELL (probability < 30%)
```

### 17.3 Multi-Timeframe Analysis (dari BOZ, Market Digest, TrendSpider)

Saat ini hanya daily prediction. Kompetitor membuktikan multi-timeframe lebih akurat:

| Timeframe | Horizon | Strategy | Indicators |
|-----------|---------|----------|------------|
| **Intraday** | 1-4 jam | Day trading | 1h/4h RSI, VWAP, volume profile |
| **Daily** | 1-5 hari | Swing trading | Daily RSI, MACD, MA5/10/20 (current) |
| **Weekly** | 1-4 minggu | Position trading | Weekly MA, trend structure |
| **Monthly** | 1-3 bulan | Investment | Monthly momentum, fundamental |

**Confluence Rule:** Sinyal lebih kuat jika 3+ timeframe agree (bullish confluence).

### 17.4 Entry/Target/Stop per Sinyal (dari BOZ, Trade Ideas, Hanzo AI)

Saat ini hanya BUY/SELL tanpa level harga. Kompetitor memberikan level actionable:

```
Sinyal: BUY BBCA
├── Entry Range: Rp 9.150 - Rp 9.250
├── Target 1: Rp 9.500 (+2.7%)  — 50% position
├── Target 2: Rp 9.800 (+6.0%)  — 30% position
├── Target 3: Rp 10.200 (+10.3%) — 20% position
├── Stop Loss: Rp 8.900 (-3.3%)
├── Risk/Reward: 1:1.8 (to Target 1), 1:3.2 (weighted)
├── Position Size: 15% of portfolio (Kelly Quarter)
├── Confidence: 68% (AI Score: 7/10)
└── Timeframe: Daily (hold 3-7 days)
```

### 17.5 Bull vs Bear Debate System (dari TradingAgents)

Saat ini analisis satu sisi. TradingAgents membuktikan Bull vs Bear debate
menghasilkan keputusan lebih balanced:

```
[Analyst Team]
├── Technical Analyst → "RSI oversold, MA crossover imminent → BULLISH"
├── Sentiment Analyst → "Fear & Greed at 25 (Extreme Fear) → CONTRARIAN BULLISH"
├── News Analyst → "Fed hawkish, foreign outflow 1.2T → BEARISH"
└── Fundamental Analyst → "PE 15x, ROE 18%, solid earnings → BULLISH"

[Debate]
├── Bull Researcher: "Technical + sentiment mendukung rally.
│   Historical: Extreme Fear + oversold RSI → 72% bounce rate"
├── Bear Researcher: "Fed hawkish + foreign outflow adalah headwind besar.
│   Historical: Foreign outflow >1T → 65% continued decline"
└── Research Manager: "Bull case lebih kuat untuk short-term (3-5 days),
    Bear case valid untuk medium-term (2-4 weeks). Recommendation: SHORT-TERM BUY
    with tight stop, exit before Fed meeting"

[Trader] → Execute: BUY with 5-day horizon, stop at -3%
[Risk Manager] → Approve: Position size 10%, R/R acceptable
[Portfolio Manager] → Final: APPROVED
```

### 17.6 Trading Memory (dari TradingAgents)

Sistem belajar dari keputusan sebelumnya:

```python
# Setiap prediksi dicatat dengan outcome:
trading_memory = {
    "BBCA_2025-06-15": {
        "signal": "BUY", "confidence": 0.72, "outcome": "CORRECT",
        "return": +2.3%, "rules_triggered": ["trend_follower", "anti_fomo"],
        "lesson": "Trend follower + bullish MA alignment = reliable signal"
    },
    "BBRI_2025-06-14": {
        "signal": "BUY", "confidence": 0.68, "outcome": "WRONG",
        "return": -1.5%, "rules_triggered": ["trend_follower"],
        "lesson": "VIX was elevated (28), should have triggered VIX panic rule"
    }
}

# AI Agent menggunakan memory untuk improve future predictions:
# "Based on 45 past predictions: Trend follower signals are 62% accurate,
#  but drop to 38% when VIX > 25. Adjust confidence accordingly."
```

### 17.7 Chart & Candlestick Pattern Recognition (dari TrendSpider, OZCx)

| Pattern Type | Patterns | Signal |
|--------------|----------|--------|
| **Reversal** | Head & Shoulders, Double Top/Bottom, Rounding Bottom | Trend reversal |
| **Continuation** | Flags, Pennants, Triangles, Wedges | Trend continue |
| **Candlestick** | Hammer, Doji, Engulfing, Morning Star, Evening Star | Short-term signal |
| **Support/Resistance** | Auto-detect key levels, pivot points | Entry/stop levels |

### 17.8 Market Structure Analysis (dari BOZ)

```
Market Structure Detection:
├── HH (Higher High) + HL (Higher Low) → Uptrend confirmed
├── LH (Lower High) + LL (Lower Low) → Downtrend confirmed
├── CHoCH (Change of Character) → Potential reversal
└── BOS (Break of Structure) → Trend continuation

Integration dengan existing regime detection:
├── BULL regime + HH/HL → Strong BUY bias
├── BEAR regime + LH/LL → Strong SELL bias
├── SIDEWAYS + CHoCH → Watch for breakout
└── Any regime + BOS → Confirm trend direction
```

### 17.9 Skill Pack Modular Scoring (dari MarketLayer)

Setiap "skill pack" adalah module scoring terpisah yang dapat di-enable/disable:

| Skill Pack | Deskripsi | Status |
|------------|-----------|--------|
| **Momentum** | RSI, MACD, MA crossover signals | ✅ Ada (indicators.py) |
| **Mean Reversion** | Bollinger Bands, Z-score, RSI extreme | ✅ Ada (indicators.py) |
| **Trend Follower** | MA alignment, ADX, trend strength | ✅ Ada (business rules) |
| **Risk Radar** | VaR, CVaR, Kelly, drawdown | ✅ Ada (risk_manager.py) |
| **Fear & Greed** | Composite sentiment (7 components) | ✅ Ada (sentiment.py) |
| **Inter-Market** | Correlation, lead-lag, spread | ✅ Ada (intermarket.py) |
| **News Catalyst** *(BARU)* | FinBERT sentiment dari RSS | ❌ Target Phase 2 |
| **Earnings Watch** *(BARU)* | Earnings calendar, surprise detection | ❌ Target Phase 2 |
| **Sector Rotation** *(BARU)* | Sector momentum, rotation detection | ❌ Target Phase 2 |
| **Volume Anomaly** *(BARU)* | Unusual volume, smart money detection | ❌ Target Phase 2 |
| **Foreign Flow** *(BARU)* | BEI foreign buy/sell tracking | ❌ Target Phase 2 |

### 17.10 Daily Morning Briefing (dari Hanzo AI)

Telegram message setiap pagi sebelum market open:

```
🌅 MORNING BRIEFING — 21 Jun 2025, 08:30 WIB

📊 MARKET OVERVIEW
├── IHSG: 7,234 (+0.8% yesterday)
├── S&P500: 5,432 (+0.3%) → Positive lead
├── VIX: 14.2 (-2.1%) → Low fear
├── USD/IDR: 16,250 (+0.1%)
└── Gold: $2,340 (+0.5%) → Safe haven stable

🎯 TODAY'S PREDICTIONS
├── BBCA: AI Score 7/10 (BUY) — Entry 9.150, Target 9.500, Stop 8.900
├── BBRI: AI Score 6/10 (HOLD) — Wait for better entry
├── TLKM: AI Score 5/10 (HOLD) — Sideways, no clear signal
└── IHSG: AI Score 7/10 (BUY) — Bullish confluence 3/4 timeframes

📈 KEY LEVELS
├── IHSG Support: 7,180 / Resistance: 7,300
├── Foreign Flow: +450M IDR (net buy yesterday)
└── Market Regime: BULL (MA5>MA10>MA20, ADX 25)

⚠️ RISK ALERTS
├── Fed Meeting: 2 days away — reduce position if uncertain
├── VIX still low but watch for spike
└── No major earnings today

🤖 AI SUMMARY
"Bullish bias today dengan 3 dari 4 timeframe konfluensi. BBCA top pick
dengan AI Score 7. Watch Fed meeting dalam 2 hari — jika hawkish,
reduce exposure. Foreign flow positif mendukung rally."

📋 DISCLOSURE
Prediksi bersifat probabilistik. Bukan saran investasi.
Gunakan manajemen risiko & paper trading untuk validasi.
```

### 17.11 Alphalens & Pyfolio Integration (dari QuantRocket)

**Alphalens** — Alpha factor analysis:
- IC by day, IC cumulative, IC heatmap
- Returns by quantile (Q1-Q5)
- Turnover analysis
- Factor autocorrelation

**Pyfolio** — Portfolio tear sheet:
- Cumulative returns vs benchmark
- Sharpe, Sortino, Calmar ratios
- Drawdown analysis
- Monthly returns heatmap
- Sector exposure
- Beta & alpha vs benchmark

### 17.12 Multi-Mode AI Research (dari AlphaSense)

| Mode | Response Time | Use Case |
|------|---------------|----------|
| **Fast** | ~10s | Quick check — "BBCA bagus gak hari ini?" |
| **Standard** | ~30s | Daily prediction summary |
| **Deep** | ~5min | Weekly comprehensive report |
| **Research** | ~15min | Monthly investment thesis |

### 17.13 Volume Anomaly Detection (dari Hanzo AI)

```python
# Detect unusual volume yang mengindikasikan smart money activity
volume_z_score = (current_volume - volume_ma_20) / volume_std_20

if volume_z_score > 3.0:
    alert = "VOLUME ANOMALY: Volume {ticker} {z_score}x di atas normal"
    # Check apakah ada news catalyst
    # Check foreign flow direction
    # Generate smart money signal
```

### 17.14 Wyckoff Phase Detection (dari Hanzo AI)

| Phase | Description | Signal |
|-------|-------------|--------|
| **Accumulation** | Smart money buying quietly | Prepare for long |
| **Spring** | False breakdown below support → trap | BUY signal |
| **SOS** (Sign of Strength) | Breakout above resistance | Confirm BUY |
| **Distribution** | Smart money selling quietly | Prepare for short |
| **Upthrust** | False breakout above resistance → trap | SELL signal |
| **SOW** (Sign of Weakness) | Breakdown below support | Confirm SELL |

---

## 18. RINGKASAN ADOPSI KOMPETITOR → ROADMAP

| Fitur (dari Kompetitor) | Fase | Owner | Effort |
|------------------------|------|-------|--------|
| Walk-Forward CV (QuantRocket) | Fase 1 | ML Eng | 1-2 minggu |
| IC/Rank IC Metrics (Qlib) | Fase 1 | ML Eng | 1 minggu |
| Composite AI Score (Kavout) | Fase 1 | ML Eng | 1 minggu |
| Multi-Timeframe (BOZ) | Fase 1-2 | ML Eng | 2-3 minggu |
| Entry/Target/Stop (BOZ) | Fase 1 | Quant | 1-2 minggu |
| Market Structure HH/HL (BOZ) | Fase 1 | ML Eng | 1 minggu |
| Volume Anomaly (Hanzo) | Fase 1 | ML Eng | 1 minggu |
| Daily Briefing (Hanzo) | Fase 1 | ML Eng | 1 minggu |
| Alphalens (QuantRocket) | Fase 1 | ML Eng | 1-2 minggu |
| Pyfolio (QuantRocket) | Fase 1 | Quant | 1 minggu |
| Bull vs Bear (TradingAgents) | Fase 2 | AI/LLM Eng | 2-3 minggu |
| Trading Memory (TradingAgents) | Fase 2 | AI/LLM Eng | 1-2 minggu |
| FinBERT Sentiment (OZCx) | Fase 2 | AI/LLM Eng | 2 minggu |
| Chart Patterns (TrendSpider) | Fase 2 | ML Eng | 2-3 minggu |
| Candlestick Patterns (TrendSpider) | Fase 2 | ML Eng | 1-2 minggu |
| Skill Pack Scoring (MarketLayer) | Fase 2 | ML Eng | 2-3 minggu |
| Multi-Dimension Rating (Kavout) | Fase 2 | ML Eng | 1-2 minggu |
| Multi-Mode AI (AlphaSense) | Fase 2 | AI/LLM Eng | 2-3 minggu |
| In-Line Citations (AlphaSense) | Fase 2 | AI/LLM Eng | 1-2 minggu |
| Multi-Horizon (Danelfin) | Fase 2 | ML Eng | 2-3 minggu |
| Weight-Centric Arch (FinRL-X) | Fase 3 | Backend | 2-3 minggu |
| NL Scanner (TrendSpider) | Fase 4 | Frontend | 3-4 minggu |

---

**Catatan Akhir:** Kesuksesan aplikasi ini tidak hanya ditentukan oleh kecanggihan
kode, tetapi oleh **kualitas strategi**, **manajemen risiko**, dan **disiplin
eksekusi** yang Anda terapkan. Aplikasi ini adalah alat bantu, bukan kristal bola.
Gunakan dengan bijak. Selamat membangun!
