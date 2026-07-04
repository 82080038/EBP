# 🎨 UI/UX Design Recommendation — Pasar Modal Dashboard

## Tujuan Dokumen

Dokumen ini menyusun desain tampilan utama aplikasi prediksi pasar modal berdasarkan:
- **Screenshot referensi** dari terminal trading profesional (X / fathurwithyou)
- **Best practices** dari TradingView, institutional terminal (Bloomberg/TradeX), dan dashboard Kite/Next.js
- **Kebutuhan aplikasi ini** saat ini: 85+ modul, 24 halaman Streamlit, fitur ML + AI + simulasi

Tujuan utamanya: **semua proses terlihat** — keputusan (prediksi/sinyal), tindakan (order/simulasi), dan hasil (P&L, verifikasi, metrik) dalam satu tampilan utama yang bisa dipersonalisasi.

---

## 1. Analisis Screenshot Referensi

Screenshot menunjukkan terminal trading profesional dengan pola layout yang sangat umum di Bloomberg, Eikon, IPOT Pro, dan platform broker institusional:

| Area | Komponen | Fungsi UX |
|------|----------|-----------|
| **Top Bar** | Search, tombol BUY, Login, Status koneksi | Aksi cepat & status sistem |
| **Left Sidebar** | Watchlist, Portfolio, Markets, Stream, Support, Settings | Navigasi utama & workspace switcher |
| **Center** | Chart utama + statistik IHSG | Fokus analisis |
| **Left Panel** | All Stocks (time, code, price, action, lot, buyer) | Market activity feed |
| **Right Panel** | Top Stocks, Market Overview (sectors/index) | Context & scan pasar |
| **Bottom Panel** | Calendar, Market Summary, Foreign-Domestic Activity, Top Broker, Index/Commodities/Currencies | Macro & flow informasi |
| **Bottom Ticker** | Running index quotes | Status pasar real-time |

**Pola yang bisa diadopsi:**
1. **Konsentrasi di tengah**: chart dan keputusan utama mendapat ruang terbesar.
2. **Context di sekeliling**: watchlist, market summary, order book di samping.
3. **Status selalu terlihat**: portfolio, P&L, koneksi, market hours.
4. **Actions tidak tersembunyi**: BUY/SELL hanya 1 klik dari chart atau watchlist.

---

## 2. Design Principles untuk Aplikasi Ini

Berdasarkan riset TradingView, TradeX, Vantixs, dan Kite:

1. **Role-based panels** — setiap panel punya 1 tugas jelas:
   - Context panel (market summary, watchlist)
   - Analysis panel (chart + indikator)
   - Decision panel (sinyal, AI score, bull vs bear)
   - Execution panel (order form, simulasi, order book)
   - Management panel (portfolio, positions, alerts)

2. **Timeframe discipline** — multi-chart dengan konteks HTF (higher timeframe) selalu terlihat.

3. **Minimal execution panel** — hindari clutter di area order entry; hanya field penting + konfirmasi risk.

4. **Color discipline** — hijau/merah hanya untuk P&L dan aksi buy/sell; netral untuk data pasar.

5. **Keyboard-first ready** — untuk future auto-trading, tombol dan shortcut harus terstruktur.

6. **Process visibility** — decision → action → result harus dalam 1 layar tanpa navigasi berlebihan.

---

## 3. Layout Utama yang Direkomendasikan: "Command Center"

Layout utama diganti dari halaman statis menjadi **single-page dashboard** dengan panel yang bisa di-resize/ditoggle. Layout ini cocok untuk pasar modal IDX dan global.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ TOP BAR                                                                     │
│ [Logo] [Search Ticker] [Market Status] [IHSG ▼] [SP500 ▲] [Alerts] [Buy] [User]│
├──────────┬──────────────────────────────────────────────────────────┬───────┤
│          │                                                          │       │
│ WATCHLIST│  MULTI-CHART PANEL (center)                              │ ORDER │
│  (left)  │  ┌──────────────────────────┐  ┌──────────────────┐     │ BOOK  │
│          │  │  Main Chart (Daily)      │  │ MTF Chart (1W)   │     │       │
│  Saved   │  │  Candlestick + MA/RSI/   │  │ Higher TF context│     │ Bid   │
│  groups  │  │  MACD + entry/target/stop│  │                  │     │ Ask   │
│  1-5     │  └──────────────────────────┘  └──────────────────┘     │ Spread│
│          │  ┌──────────────────────────┐  ┌──────────────────┐     │       │
│  [+Add]  │  │ 4H Chart                 │  │ 1H/15m Intraday  │     │ Depth│
│          │  │ Trend + structure         │  │ (opsional)       │     │      │
│          │  └──────────────────────────┘  └──────────────────┘     │       │
│          │                                                          │       │
├──────────┴──────────────────────────────────────────────────────────┴───────┤
│ DECISION STRIP                                                                │
│ [AI Score 8.5/10] [Sinyal: BUY] [Confidence 72%] [Regime: Bull] [Bull vs Bear]│
│ [SHAP Top 3] [RAG Reason] [Risk: VaR 1.2%] [Kelly 0.15]                       │
├──────────┬──────────────────────────┬──────────────────────────┬──────────────┤
│ PORTFOLIO│  MARKET SUMMARY          │  FOREIGN-FLOW / BROKER  │  CALENDAR    │
│ & ORDER  │  IHSG, Sectors, Most     │  Net foreign buy/sell,  │  Economic,   │
│ STATUS   │  Active, Top Gainers/     │  top broker,            │  Earnings,   │
│ (P&L)    │  Losers, heatmap          │  broker distribution    │  Dividends   │
└──────────┴──────────────────────────┴──────────────────────────┴──────────────┘
```

### 3.1 Panel Detail

#### A. Top Bar
- **Ticker search**: autocomplete semua ticker (IHSG, blue chip, global indices, commodities)
- **Market status**: Open/Closed + countdown market hours (gunakan `market_hours.py`)
- **Index summary**: IHSG, S&P500, NASDAQ, N225, HSI, STI + % change
- **Quick alerts**: notifikasi belum dibaca
- **User profile**: settings, theme toggle, logout
- **Action button**: `Simulate Trade` (bukan Buy real karena belum ada broker API)

#### B. Watchlist (Left Panel)
- Ticker list dengan kolom: Symbol, Last Price, Change %, Volume, Signal, AI Score
- Group support (Group 1-5): Blue Chip, Tech, Finance, Global, Custom
- Drag-and-drop reorder
- Right-click menu: Analyze, Predict, Add Alert, Simulate Trade
- Integrasi dengan `screener.py` untuk ranking point-in-time

#### C. Multi-Chart Panel (Center)
- **Main chart**: candlestick, volume, indikator overlay (MA, RSI, MACD, Bollinger, ATR, VWAP)
- **Secondary charts**: higher timeframe (1W/1D) + intraday (4H/1H/15m)
- **Timeframe controller**: sinkron atau independen per chart
- **Drawing tools**: trendline, support/resistance, Fibonacci (dari `patterns.py`, `technical_advanced.py`)
- **Signal markers**: panah BUY/SELL di chart dengan confidence %
- **Entry/Target/Stop**: garis horizontal otomatis dari `quant_finance.py`
- **Library rekomendasi**: TradingView Lightweight Charts (free, performant) atau Chart.js

#### D. Order Book (Right Panel)
- **Bid/Ask ladder**: 5-10 level dengan volume dan cumulative depth
- **Spread indicator**: bid-ask spread absolute & %
- **Depth chart**: area chart cumulative bid vs ask
- **Status**: data dari `broker_sim.py` (simulated) atau real broker API di masa depan
- **Keterbatasan saat ini**: tanpa real-time IDX L2 data, panel ini menampilkan simulated order book dari harga terakhir + spread estimasi (`slippage.py`)

#### E. Decision Strip (di bawah chart)
Ringkasan keputusan yang selalu terlihat:
- AI Score 1-10 (dari `scoring.py`)
- Sinyal utama (BUY/SELL/HOLD) + confidence
- Market regime (Bull/Bear/Sideways/Crisis) dari `regime.py`
- Bull vs Bear debate summary (dari `bull_bear_debate.py`)
- Top 3 SHAP features (dari `explainability.py`)
- RAG reasoning (dari `rag_system.py`)
- Risk metrics: VaR, CVaR, Kelly, position size (dari `risk_manager.py`)
- Quick actions: Run Prediction, Simulate Trade, Add Alert

#### F. Portfolio & Order Status (Bottom Left)
- **Positions table**: ticker, qty, avg cost, last price, market value, unrealized P&L, day P&L, allocation %
- **Orders table**: pending orders, filled, cancelled, partial fill
- **Trade history**: date, side, qty, price, fees, net
- **P&L summary**: total equity, total P&L, win rate, Sharpe ratio
- **Data source**: `broker_sim.py`, `simulation_engine.py`, `paper_trading.py`

#### G. Market Summary (Bottom Center)
- **Index performance**: IHSG, sector performance heatmap (gunakan `sector_rotation.py`)
- **Most Active / Top Gainers / Top Losers**: dari `screener.py` atau `data_fetcher.py`
- **Market breadth**: advance-decline line, sentiment meter
- **Fear & Greed Index** (dari `sentiment_pipeline.py`)

#### H. Foreign Flow & Broker (Bottom Right)
- **Net foreign buy/sell**: placeholder untuk IDX real-time data (Invezgo/Kun) — saat ini dari simulated atau yfinance
- **Top broker distribution**: di masa depan dari BEI data
- **Broker map**: visualisasi per sekuritas (placeholder)

#### I. Calendar (Bottom Far Right)
- **Economic calendar**: FRED events (CPI, Fed funds, unemployment) dari `data_fetcher.py`
- **Earnings calendar**: dari `event_driven.py`
- **Dividend calendar**: dari `fundamental.py`
- **IDX holidays**: dari `idx_rules.py`

#### J. Bottom Ticker (optional)
- Running ticker: IHSG, blue chips, global indices, commodities, forex
- Status: connected / disconnected per data source

---

## 4. Mapping Fitur Aplikasi Saat Ini ke Layout

| Fitur Saat Ini | Modul | Letak di Layout Utama |
|----------------|-------|------------------------|
| Prediksi harian | `predictor.py` | Decision Strip + Signal marker di chart |
| AI Score 1-10 | `scoring.py` | Decision Strip |
| Multi-timeframe | `mtf.py` | Multi-Chart Panel (1W/1D/4H/1H) |
| Technical indicators | `indicators.py`, `technical_advanced.py` | Overlay chart + panel toggle |
| Chart/candlestick patterns | `patterns.py`, `smc.py` | Auto-detect annotations di chart |
| Wyckoff/Elliott | `wyckoff.py`, `elliott_wave.py` | Phase/structure indicator di chart |
| Market regime | `regime.py` | Decision Strip + regime badge |
| Bull vs Bear debate | `bull_bear_debate.py` | Decision Strip summary |
| SHAP explainability | `explainability.py` | Decision Strip top features |
| RAG reasoning | `rag_system.py` | Decision Strip reasoning |
| Screener | `screener.py` | Watchlist + Market Summary panels |
| Portfolio optimization | `portfolio.py` | Portfolio panel (optimization result) |
| Risk metrics | `risk_manager.py`, `pro_risk.py` | Decision Strip + Portfolio panel |
| Walk-forward simulation | `simulation_engine.py` | Portfolio & Order Status panel |
| Broker simulation | `broker_sim.py` | Order form + Order Book + trade history |
| Paper trading | `paper_trading.py` | Portfolio & Order Status panel |
| Fundamental data | `fundamental.py` | Side panel (P/E, P/B, ROE, etc.) |
| Sentiment | `sentiment_pipeline.py`, `social_sentiment.py` | Market Summary + Fear & Greed |
| Notifications | `notifier.py` | Top bar alerts + in-app notifikasi |
| FastAPI | `api.py` | Backend untuk semua panel (JSON data) |
| System check | `system_check.py` | Top bar status icon |

---

## 5. Design System (Color & Typography)

### Color Palette — Dark Mode (default untuk trading desk)

```
Background primary:    #0B0E11  (black/deep charcoal)
Background secondary:#151A21  (panel background)
Background tertiary: #1C2128  (hover/selected)
Border:               #2A3038
Text primary:         #E6E9EF
Text secondary:       #8B949E
Accent:               #3B82F6  (blue for AI/primary actions)
Buy / Profit:         #00C076  (green)
Sell / Loss:          #FF5353  (red)
Warning:              #FFAA00  (amber)
Neutral:              #94A3B8
```

### Color Palette — Light Mode (opsional untuk review/compliance)

```
Background:           #F5F5F5
Surface:              #FFFFFF
Border:               #E5E7EB
Text:                 #111827
Buy / Profit:         #059669
Sell / Loss:          #DC2626
```

### Typography

- **Primary font**: Inter atau Geist Sans (clean, data-dense)
- **Monospace font**: JetBrains Mono atau Geist Mono (untuk harga/angka)
- **Font sizes**:
  - Harga utama: 24-32px
  - Angka data: 13-14px
  - Label/headers: 11-12px uppercase tracking-wide
- **Density**: data-dense UI seperti Kite/Bloomberg; padding minimal 8-12px.

### Semantik Warna P&L

- **Hijau**: keuntungan, BUY, bid, up-tick
- **Merah**: kerugian, SELL, ask, down-tick
- **Abu-abu**: netral/HOLD, data makro, label
- **Biru**: AI/ML insight, primary action
- **Kuning/Amber**: warning, alert, pending order

---

## 6. Interaction Patterns

### 1. Watchlist → Chart → Decision → Action (Flow)

```
User klik ticker di watchlist
  → Chart utama reload (data dari `data_fetcher.py`)
  → Prediction pipeline run (`predictor.py`)
  → Decision Strip update (AI Score, signal, confidence, risk)
  → User klik "Simulate Trade"
  → Order form muncul dengan default size dari Kelly/risk
  → Order dieksekusi di `broker_sim.py`
  → Portfolio & Order Status update real-time
  → Trade tersimpan di `database.py` (prediksi + broker sim)
```

### 2. Chart-driven Analysis

- Klik di chart untuk men-set entry/stop manual
- Toggle indicator overlays dari panel kanan chart
- Crosshair sync antar chart multi-timeframe
- Hover candlestick menampilkan tooltip OHLCV + indikator

### 3. Alert & Notification

- Set alert dari watchlist atau chart
- Alert tersimpan di tabel `alerts`
- Real-time monitor (`realtime_monitor.py`) cek kondisi dan trigger
- Notification center di top bar + Telegram fallback

### 4. Layout Customization

- Save/load workspace layout (multi-chart grid, panel visibility)
- Default layouts: "Day Trader", "Swing Trader", "Investor", "AI Analyst"
- Responsive breakpoint: desktop multi-panel, tablet 2-column, mobile accordion

---

## 7. Tech Stack Rekomendasi untuk Implementasi

Saat ini aplikasi menggunakan **Streamlit** (24 halaman). Untuk layout Command Center ini, direkomendasikan migrasi bertahap:

| Layer | Saat Ini | Target |
|-------|----------|--------|
| Frontend | Streamlit | React 18+ / Next.js 14+ |
| Styling | Streamlit CSS | Tailwind CSS + shadcn/ui |
| Charts | Plotly/Streamlit | TradingView Lightweight Charts atau Chart.js |
| Icons | - | Lucide React |
| Layout grid | - | react-grid-layout (draggable panels) |
| State | - | Zustand atau Redux Toolkit |
| Real-time | - | WebSocket (future) + Server-Sent Events (SSE) |
| Backend | FastAPI (10 endpoints) | FastAPI + WebSocket + Redis |
| API | REST | REST + WebSocket streaming |
| Data push | Poll | TanStack Query + polling (fallback) |

### Alur Migrasi Bertahap

1. **Fase 1**: FastAPI endpoint lengkap untuk semua data panel (chart, watchlist, portfolio, order book, market summary)
2. **Fase 2**: Buat Next.js shell dengan layout Command Center; panel awal hanya iframe/embed Streamlit pages
3. **Fase 3**: Rebuild panel satu per satu: Chart, Watchlist, Portfolio, Decision Strip
4. **Fase 4**: Ganti panel embed Streamlit menjadi native React components
5. **Fase 5**: Multi-chart grid, drag-drop layout, custom workspace save

---

## 8. Keterbatasan Data Saat Ini & Solusi

| Data Ideal | Ketersediaan Saat Ini | Solusi UI |
|------------|----------------------|-----------|
| Real-time IDX L2 order book | ❌ Tidak ada | Simulated order book dari harga + spread (`slippage.py`) |
| Real-time broker API | ❌ Belum | Tombol "Simulate Trade" sebagai placeholder; real order jika broker API sudah terhubung |
| Foreign flow BEI | ❌ Belum | Panel dengan data placeholder + status "Waiting for IDX API" |
| Bid/ask depth | ❌ Partial | Depth chart dari estimasi bid/ask; disclaimer di panel |
| 1-second tick data | ❌ | Aggregated 5m/15m intraday dari yfinance + polling |
| Multiple broker accounts | ❌ Single user | User switcher di masa depan |

**Prinsip**: UI tidak boleh menutupi keterbatasan data. Setiap panel yang datanya belum real-time harus punya:
- Status badge: `Simulated`, `Delayed`, `Real-time`
- Last update timestamp
- Tooltip/disclaimer sumber data

---

## 9. Accessibility & Responsiveness

- **Contrast ratio**: minimal 4.5:1 untuk teks, 3:1 untuk UI components
- **Color-blind safe**: jangan hanya mengandalkan warna hijau/merah; tambahkan icon ▲/▼ dan label BUY/SELL
- **Keyboard navigation**: Tab antar panel, Enter untuk execute, Esc untuk cancel order
- **Screen reader**: ARIA labels untuk harga, sinyal, dan alert
- **Responsive**:
  - Desktop (>1280px): multi-panel layout
  - Tablet (768-1280px): 2-column, panel collapsible
  - Mobile (<768px): single column, bottom sheet untuk order form, tab navigation

---

## 10. Halaman Lain yang Perlu Dibangun

Selain Command Center, halaman-halaman berikut tetap diperlukan (mapping dari 24 halaman Streamlit saat ini):

| Halaman | Isi | Status Saat Ini |
|---------|-----|-----------------|
| Command Center | Layout utama di atas | ✅ Perlu dibangun |
| Prediction Detail | Hasil prediksi, SHAP, votes, RAG | ✅ (`prediksi.py`) |
| Backtesting | Walk-forward backtest, tear sheet | ✅ (`backtesting.py`) |
| Simulation | Walk-forward market simulation | ✅ (`simulation.py`) |
| Portfolio Optimizer | 5 methods comparison | ✅ (`portfolio_v2.py`) |
| Screener | Point-in-time scan + rank | ✅ (`screener.py`) |
| Risk Management | VaR, CVaR, kill switch, exposure | ✅ (`risk_management.py`) |
| AI Agent | Daily briefing, agents, debate | ✅ (`trading_agent.py`) |
| Sentiment | Fear & Greed, FinBERT, RSS | ✅ (`sentiment.py`) |
| Calendar | Economic, earnings, dividends | ✅ (`market_hours.py`) |
| Settings | API keys, theme, notification | ✅ (`pengaturan.py`) |
| System Check | Health status, GPU, tests | ✅ (`system_check.py`) |

---

## 11. Kesimpulan & Rekomendasi Prioritas

**Rekomendasi utama**: ganti halaman utama (`dashboard.py`) dari ringkasan statis menjadi **Command Center single-page** dengan:
1. Multi-chart di tengah
2. Watchlist di kiri
3. Order book di kanan
4. Decision strip di bawah chart
5. Portfolio + Market summary + Calendar di bottom panel

**Langkah pertama yang paling rendah risiko**:
- Pertahankan Streamlit untuk halaman detail/analysis
- Bangun **satu halaman "Command Center"** di Streamlit dengan kolom layout (watchlist | chart | order book) menggunakan `st.columns` dan `st.container`
- Gunakan **Plotly** untuk chart interaktif sementara (karena TradingView Lightweight Charts butuh React/Next.js)
- Panel diisi dengan data dari FastAPI endpoint yang sudah ada

**Langkah kedua (medium term)**: migrasi ke React/Next.js untuk layout profesional dan drag-drop panel.

Dengan layout ini, semua proses — **keputusan (AI signal), tindakan (simulate trade), dan hasil (portfolio P&L)** — menjadi terlihat dalam satu layar tanpa user harus berpindah halaman.
