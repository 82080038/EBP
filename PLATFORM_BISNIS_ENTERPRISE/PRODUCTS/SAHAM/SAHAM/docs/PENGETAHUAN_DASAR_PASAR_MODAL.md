# PENGETAHUAN DASAR PASAR MODAL LENGKAP

> Dokumen referensi komprehensif untuk analisa pasar modal, mencakup seluruh disiplin ilmu yang dibutuhkan dari level dasar hingga profesional institusional. Disusun dari riset mendalam berbagai sumber profesional.

---

## DAFTAR ISI

1. [Anatomi Pasar Modal](#1-anatomi-pasar-modal)
2. [Analisa Fundamental](#2-analisa-fundamental)
3. [Analisa Teknikal](#3-analisa-teknikal)
4. [Analisa Kuantitatif](#4-analisa-kuantitatif)
5. [Analisa Intermarket & Makroekonomi](#5-analisa-intermarket--makroekonomi)
6. [Analisa Behavioral Finance](#6-analisa-behavioral-finance)
7. [Manajemen Portofolio](#7-manajemen-portofolio)
8. [Manajemen Risiko Profesional](#8-manajemen-risiko-profesional)
9. [Market Microstructure & Eksekusi](#9-market-microstructure--eksekusi)
10. [Regulasi & Aturan Bursa Efek Indonesia (IDX/BEI)](#10-regulasi--aturan-bursa-efek-indonesia-idxbei)
11. [Sektor & Industri di IDX](#11-sektor--industri-di-idx)
12. [Strategi Trading Profesional](#12-strategi-trading-profesional)
13. [Sumber Data & Tools](#13-sumber-data--tools)
14. [Glosarium Lengkap](#14-glosarium-lengkap)
15. [Multi-Timeframe Analysis (MTF)](#15-multi-timeframe-analysis-mtf)
16. [Advanced Portfolio Optimization](#16-advanced-portfolio-optimization)
17. [Smart Money Concepts (SMC) / ICT Methodology](#17-smart-money-concepts-smc--ict-methodology)
18. [Advances in Financial Machine Learning (Lopez de Prado)](#18-advances-in-financial-machine-learning-lopez-de-prado)
19. [Deep Reinforcement Learning untuk Trading](#19-deep-reinforcement-learning-untuk-trading)
20. [Complex Systems Approach (CFA Institute 2025)](#20-complex-systems-approach-cfa-institute-2025)
21. [AI in Asset Management (CFA Institute 2025)](#21-ai-in-asset-management-cfa-institute-2025)

---

## 1. ANATOMI PASAR MODAL

### 1.1 Peserta Pasar

| Peserta | Peran | Karakteristik |
|---------|-------|---------------|
| **Retail Investors** | Individu | Modal kecil, emosional, informasi terbatas |
| **Institutional Investors** | Reksa dana, dana pensiun, asuransi | Modal besar, analisa mendalam, horizon panjang |
| **Hedge Funds** | Dana hedge | Fleksibel, leverage, strategi kompleks |
| **Market Makers** | Sekuritas | Provider likuiditas, profit dari spread |
| **Brokers** | Perantara | Eksekusi order, komisi |
| **Clearing Agency** | KPEI (Indonesia) | Penjaminan penyelesaian |
| **Custodian** | KSEI (Indonesia) | Penyimpanan dan penyelesaian |
| **Regulator** | OJK (Indonesia), SEC (US) | Pengawasan, regulasi |

### 1.2 Jenis Pasar

- **Primary Market**: IPO (Initial Public Offering), rights issue, private placement
- **Secondary Market**: Regular market, cash market, negotiated market, crossing market
- **Tertiary Market**: OTC (Over The Counter) — block trading di luar bursa
- **Derivatives Market**: Opsi, futures, warrant, structured warrant

### 1.3 Tipe Order

| Tipe | Deskripsi |
|------|-----------|
| **Market Order** | Eksekusi segera pada harga pasar terbaik |
| **Limit Order** | Eksekusi pada harga tertentu atau lebih baik |
| **Stop Order** | Berubah menjadi market order ketika harga mencapai trigger |
| **Stop-Limit Order** | Berubah menjadi limit order ketika trigger tercapai |
| **Iceberg Order** | Order besar yang ditampilkan sebagian untuk sembunyikan ukuran |
| **IOC (Immediate or Cancel)** | Eksekusi yang bisa segera, sisanya dibatalkan |
| **FOK (Fill or Kill)** | Semua eksekusi atau tidak sama sekali |

---

## 2. ANALISA FUNDAMENTAL

### 2.1 Trinitas Laporan Keuangan

Setiap analisa fundamental dimulai dari **tiga laporan keuangan** yang harus dibaca bersamaan:

#### Income Statement (Laporan Laba Rugi)
- **Revenue/Pendapatan**: Total penjualan
- **COGS (Cost of Goods Sold)**: Harga pokok penjualan
- **Gross Profit**: Revenue - COGS
- **Operating Expenses**: Beban operasional (SG&A, R&D, marketing)
- **Operating Income (EBIT)**: Gross Profit - OpEx
- **Net Income (EAT)**: EBIT - Interest - Tax = Laba bersih
- **EPS (Earnings Per Share)**: Net Income / Shares Outstanding

#### Balance Sheet (Neraca)
- **Assets**: Kas, piutang, inventaris, Aset tetap, goodwill
- **Liabilities**: Utang jangka pendek, utang jangka panjang
- **Equity**: Modal saham, retained earnings
- **Formula dasar**: Assets = Liabilities + Equity

#### Cash Flow Statement (Arus Kas)
- **Operating Cash Flow (CFO)**: Kas dari operasi bisnis — **indikator paling jujur**
- **Investing Cash Flow (CFI)**: CapEx, akuisisi, divestasi
- **Financing Cash Flow (CFF)**: Utang, ekuitas, dividen
- **Free Cash Flow (FCF)**: CFO - CapEx — **kas yang benar-benar bebas**

> **Aturan Emas**: Perusahaan bisa memanipulasi satu laporan, tapi sangat sulit memanipulasi ketiganya secara bersamaan. Cash flow statement adalah "truth teller" — kas tidak bisa dipalsukan.

### 2.2 Rasio Keuangan Kunci

#### Rasio Profitabilitas
| Rasio | Formula | Interpretasi |
|-------|---------|--------------|
| **ROE** | Net Income / Equity | >15% baik, >20% excellent |
| **ROA** | Net Income / Assets | >5% baik, >10% excellent |
| **ROIC** | NOPAT / (Debt + Equity) | >WACC = menciptakan nilai |
| **Net Profit Margin** | Net Income / Revenue | >10% sehat, >20% excellent |
| **Gross Margin** | Gross Profit / Revenue | Stabil/naik = pricing power |
| **Operating Margin** | EBIT / Revenue | Efisiensi operasional |

#### Rasio Valuasi
| Rasio | Formula | Interpretasi |
|-------|---------|--------------|
| **P/E (PER)** | Price / EPS | <15 murah, 15-25 wajar, >25 mahal |
| **Forward P/E** | Price / EPS estimasi | Lebih relevan dari trailing P/E |
| **P/B (PBV)** | Price / Book Value | <1 di bawah nilai buku |
| **PEG** | P/E / Growth Rate | <1 undervalued, >2 overvalued |
| **EV/EBITDA** | Enterprise Value / EBITDA | Kapital-struktur neutral |
| **P/S (PSR)** | Price / Revenue | Untuk perusahaan belum untung |
| **Dividend Yield** | Dividen / Price | >5% tinggi untuk IDX |

#### Rasio Solvabilitas
| Rasio | Formula | Interpretasi |
|-------|---------|--------------|
| **DER** | Total Debt / Equity | <1 sehat, >3 berisiko |
| **DAR** | Total Debt / Assets | <0.5 aman |
| **Interest Coverage** | EBIT / Interest | >5 aman, <2 berisiko |
| **Current Ratio** | Current Assets / Current Liabilities | >1.5 sehat |
| **Quick Ratio** | (CA - Inventory) / CL | >1 baik |

#### Rasio Pertumbuhan
| Rasio | Formula | Interpretasi |
|-------|---------|--------------|
| **Revenue Growth** | (Rev_t / Rev_t-1) - 1 | >10% baik, >20% excellent |
| **Earnings Growth** | (EPS_t / EPS_t-1) - 1 | >15% strong growth |
| **CAGR** | (End/Start)^(1/n) - 1 | Growth rate tahunan compounding |

### 2.3 Rasio Khusus Perbankan (IDX — Sektor Dominan)

| Rasio | Formula | Standar OJK |
|-------|---------|-------------|
| **CAR** | Modal / Aset Tertimbang | ≥8% (Basel III: ≥11.5%) |
| **NPL** | Kredit Bermasalah / Total Kredit | <5% sehat |
| **BOPO** | Beban Operasional / Pendapatan Operasional | <90% efisien |
| **LDR** | Kredit / Dana Pihak Ketiga | 75-92% optimal |
| **NIM** | Pendapatan Bunga Net / Aset Produktif | >4% baik |
| **ROE Bank** | Laba / Ekuitas | >12% sehat |

### 2.4 Valuasi: Metode Penilaian Intrinsic

#### DCF (Discounted Cash Flow)
```
Nilai = Σ FCF_t / (1+WACC)^t + Terminal Value / (1+WACC)^n

WACC = (E/V × Re) + (D/V × Rd × (1-Tax))
Terminal Value = FCF_n × (1+g) / (WACC-g)
```
- **WACC**: Weighted Average Cost of Capital
- **Re**: Cost of Equity (dari CAPM: Re = Rf + β × (Rm - Rf))
- **Rd**: Cost of Debt
- **g**: Terminal growth rate (biasanya 2-3%)

#### Multiples Valuation
- **Comparable Company Analysis**: Bandingkan P/E, PBV, EV/EBITDA dengan peer
- **Precedent Transactions**: Harga akuisisi serupa
- **Reverse DCF**: Hitung growth rate yang sudah priced-in di harga saat ini

#### Margin of Safety (Benjamin Graham)
- Beli ketika harga < 70% dari nilai intrinsic
- Semakin lebar margin, semakin aman dari kesalahan analisa
- Untuk growth stock: margin dari waktu (growth akan menutup premium)

### 2.5 Analisa Kualitatif (Yang Tidak Tercatat di Angka)

- **Competitive Moat (Fossa Kompetitif)**:
  - Network effect (Gojek, Tokopedia)
  - Switching cost (banking core systems)
  - Cost advantage (economies of scale)
  - Intangible assets (brand, patent, license)
- **Manajemen**: Track record, alignment dengan shareholder, kompensasi
- **Industry Dynamics**: TAM/SAM/SOM, struktur kompetisi, barrier to entry
- **Corporate Governance**: Transparansi, audit quality, related-party transactions

### 2.6 Red Flags (Tanda Bahaya)

- Akuntansi agresif: revenue recognition dipercepat, restatement频繁
- Kompensasi eksekutif berlebihan
- Underperformance kronik: selalu miss guidance
- Pertumbuhan dengan unit economics yang hancur
- Industri dalam penurunan struktural
- Utang naik terus sambil revenue turun
- CFO negatif sambil net income positif (red flag besar!)

---

## 3. ANALISA TEKNIKAL

### 3.1 Filosofi Dasar Analisa Teknikal

1. **Harga diskon semua informasi** — semua news, fundamentals, sentiment sudah tercermin di harga
2. **Harga bergerak dalam tren** — trend is your friend
3. **History repeats** — pola perilaku manusia berulang

### 3.2 Jenis Chart

| Chart | Kegunaan |
|-------|----------|
| **Line Chart** | Trend overview sederhana |
| **Bar Chart (OHLC)** | Open-High-Low-Close per periode |
| **Candlestick** | Visualisasi terbaik untuk pola |
| **Heikin-Ashi** | Smoothing trend, filter noise |
| **Point & Figure** | Fokus pada pergerakan harga signifikan |
| **Renko** | Time-independent, fokus pada volatility |
| **Volume Profile** | Volume per level harga (bukan per waktu) |

### 3.3 Indikator Teknikal — Klasifikasi Lengkap

#### A. Trend Indicators
| Indikator | Fungsi | Sinyal |
|-----------|--------|--------|
| **MA (Moving Average)** | Smoothing harga | Golden cross (MA50 > MA200), Death cross |
| **EMA** | MA dengan weight lebih ke data terbaru | Lebih responsif |
| **MACD** | Convergence/divergence 2 EMA | Crossover signal line, histogram |
| **ADX/DI+ / DI-** | Kekuatan dan arah tren | ADX>25 strong, DI+>DI- bull |
| **Ichimoku Cloud** | Sistem komplet: trend + S/R + momentum | Harga di atas cloud = bull |
| **Parabolic SAR** | Trailing stop | Dot di bawah = bull, di atas = bear |
| **TRIX** | Triple EMA momentum | Cross zero line = trend change |
| **SuperTrend** | ATR-based trend | Garis hijau = bull, merah = bear |

#### B. Momentum Oscillators
| Indikator | Range | Overbought | Oversold |
|-----------|-------|------------|----------|
| **RSI** | 0-100 | >70 | <30 |
| **Stochastic** | 0-100 | >80 | <20 |
| **CCI** | Unbounded | >100 | <-100 |
| **Williams %R** | -100 to 0 | <-20 | <-80 |
| **MFI** | 0-100 | >80 | <20 |
| **ROC** | Unbounded | Divergence | Divergence |
| **Stoch RSI** | 0-100 | >80 | <20 |

#### C. Volatility Indicators
| Indikator | Fungsi |
|-----------|--------|
| **Bollinger Bands** | 2 std dev dari MA20 — squeeze = breakout imminent |
| **ATR** | Average True Range — ukur volatilitas absolut |
| **Keltner Channel** | ATR-based bands |
| **Donchian Channel** | High/low range — turtle trading |
| **Standard Deviation** | Volatilitas statistik |

#### D. Volume Indicators
| Indikator | Fungsi |
|-----------|--------|
| **OBV** | On Balance Volume — akumulasi/distribusi |
| **VWAP** | Volume Weighted Average Price — benchmark institusional |
| **MFI** | Money Flow Index — volume-weighted RSI |
| **Accumulation/Distribution** | Aliran uang masuk/keluar |
| **Volume Profile** | Volume per level harga — POC, VAH, VAL |
| **Ease of Movement** | Seberapa mudah harga bergerak dengan volume |

#### E. Support/Resistance & Price Levels
| Konsep | Deskripsi |
|--------|-----------|
| **Pivot Points** | PP=(H+L+C)/3, R1-R3, S1-S3 |
| **Fibonacci Retracement** | 23.6%, 38.2%, 50%, 61.8%, 78.6% |
| **Fibonacci Extension** | 127.2%, 161.8%, 261.8%, 423.6% |
| **Swing High/Low** | Level pivot struktural |
| **Round Numbers** | Psikologis: 1000, 5000, 10000 |
| **Previous Day H/L** | Level referensi intraday |

### 3.4 Candlestick Patterns

#### Pola Reversal Bullish
| Pola | Deskripsi | Reliabilitas |
|------|-----------|--------------|
| **Hammer** | Shadow bawah panjang, body kecil di atas | Medium |
| **Bullish Engulfing** | Candle bullish menelan candle bearish sebelumnya | Tinggi |
| **Morning Star** | 3 candle: bearish → star → bullish | Tinggi |
| **Piercing Line** | Open di bawah close sebelumnya, close di atas midpoint | Medium |
| **Inverted Hammer** | Shadow atas panjang setelah downtrend | Low-Medium |
| **Three White Soldiers** | 3 candle bullish berturut-turut | Tinggi |

#### Pola Reversal Bearish
| Pola | Deskripsi | Reliabilitas |
|------|-----------|--------------|
| **Shooting Star** | Shadow atas panjang, body kecil di bawah | Medium |
| **Bearish Engulfing** | Candle bearish menelan candle bullish | Tinggi |
| **Evening Star** | 3 candle: bullish → star → bearish | Tinggi |
| **Dark Cloud Cover** | Open di atas close sebelumnya, close di bawah midpoint | Medium |
| **Hanging Man** | Hammer di uptrend | Medium |
| **Three Black Crows** | 3 candle bearish berturut-turut | Tinggi |

#### Pola Kontinuasi
| Pola | Deskripsi |
|------|-----------|
| **Doji** | Indecision — open dan close hampir sama |
| **Spinning Top** | Body kecil, shadow atas dan bawah seimbang |
| **Rising/Falling Three Methods** | 3 candle kecil di dalam range 2 candle besar |

### 3.5 Chart Patterns

#### Reversal Patterns
| Pola | Deskripsi | Target |
|------|-----------|--------|
| **Head & Shoulders** | 3 puncak, tengah tertinggi | Tinggi = jarak head ke neckline |
| **Inverse H&S** | 3 lembah, tengah terendah | Bullish reversal |
| **Double Top/Bottom** | 2 puncak/lembah di level sama | Reversal |
| **Triple Top/Bottom** | 3 puncak/lembah | Reversal lebih kuat |
| **Rounding Bottom** | U-shhape, akumulasi panjang | Bullish jangka panjang |

#### Continuation Patterns
| Pola | Deskripsi |
|------|-----------|
| **Flag/Pennant** | Konsolidasi pendek setelah move tajam |
| **Triangle** | Ascending, descending, symmetrical |
| **Rectangle** | Trading range horizontal |
| **Cup & Handle** | U-shape + pullback kecil — bullish |

### 3.6 Wyckoff Method — Smart Money Analysis

Dikembangkan oleh Richard Wyckoff (1910s), masih relevan karena perilaku institusional tidak berubah.

#### 4 Fase Siklus Market:
1. **Accumulation**: Institusi beli diam-diam di harga rendah, retail pesimis
2. **Markup**: Tren naik, retail mulai ikut
3. **Distribution**: Institusi jual ke retail yang euforia
4. **Markdown**: Tren turun, retail kapitulasi

#### 3 Hukum Wyckoff:
1. **Supply & Demand**: Harga naik ketika demand > supply
2. **Cause & Effect**: Semakin lama akumulasi, semakin besar markup
3. **Effort vs Result**: Volume = effort, pergerakan harga = result. Volume tinggi tapi harga tidak bergerak = absorpsi institusi

#### Event Kunci:
- **Spring**: False breakdown di akhir accumulation — perangkap bear
- **UTAD (Upthrust After Distribution)**: False breakout di akhir distribution — perangkap bull
- **SOS (Sign of Strength)**: Bar besar dengan volume tinggi, konfirmasi markup
- **SOW (Sign of Weakness)**: Bar besar bearish, konfirmasi markdown

### 3.7 Elliott Wave Theory

Dikembangkan oleh Ralph Nelson Elliott (1930s).

#### Struktur 5-3:
- **Impulse waves** (5 gelombang): 1, 2, 3, 4, 5 — searah tren utama
- **Corrective waves** (3 gelombang): A, B, C — berlawanan tren

#### Aturan Tak Terbantahkan:
1. Wave 2 tidak boleh retracement lebih dari 100% Wave 1
2. Wave 3 tidak boleh terpendek dari Wave 1, 3, 5
3. Wave 4 tidak boleh overlap dengan territory Wave 1

#### Fibonacci Relationships:
- Wave 2 retraces 50-61.8% of Wave 1
- Wave 3 = 161.8% extension of Wave 1
- Wave 4 retraces 38.2% of Wave 3
- Wave 5 = 61.8% or 100% of Wave 1

---

## 4. ANALISA KUANTITATIF

### 4.1 Factor Models

#### CAPM (Capital Asset Pricing Model)
```
E(Ri) = Rf + βi × (Rm - Rf)

Rf = Risk-free rate (SBN/ORI)
β  = Beta (sensitivitas terhadap market)
Rm = Market return
```

#### Fama-French Three Factor Model
```
E(Ri) = Rf + β×MKT + s×SMB + h×HML

MKT = Market risk premium
SMB = Small Minus Big (size factor)
HML = High Minus Low (value factor)
```

#### Fama-French Five Factor Model
```
E(Ri) = Rf + β×MKT + s×SMB + h×HML + r×RMW + c×CMA

RMW = Robust Minus Weak (profitability factor)
CMA = Conservative Minus Aggressive (investment factor)
```

#### Carhart Four Factor Model
```
E(Ri) = Rf + β×MKT + s×SMB + h×HML + m×MOM

MOM = Momentum factor (12-1 month)
```

### 4.2 Factor Investing — Smart Beta

| Factor | Definisi | Rationale |
|--------|----------|-----------|
| **Value** | P/E rendah, PBV rendah | Mean reversion, undervalued |
| **Size** | Market cap kecil | Small caps outperform long-term |
| **Momentum** | Return 3-12 bulan terakhir | Trend persistence |
| **Quality** | ROE tinggi, stabilitas | Quality companies survive |
| **Low Volatility** | Beta rendah | Less drawdown, better risk-adjusted |
| **Dividend** | Yield tinggi | Income + compounding |
| **Profitability** | Gross margin tinggi | Quality signal |

### 4.3 Performance Metrics

| Metric | Formula | Interpretasi |
|--------|---------|--------------|
| **Sharpe Ratio** | (Rp - Rf) / σp | >1 baik, >2 excellent |
| **Sortino Ratio** | (Rp - Rf) / σ_downside | Lebih baik dari Sharpe (hanya downside) |
| **Information Ratio** | (Rp - Rb) / TE | Alpha per unit tracking error |
| **Treynor Ratio** | (Rp - Rf) / β | Return per unit market risk |
| **Max Drawdown** | (Peak - Trough) / Peak | Worst loss from peak |
| **Calmar Ratio** | CAGR / Max DD | Return per unit drawdown |
| **Beta** | Cov(Rp,Rm) / Var(Rm) | 1=market, <1=defensive, >1=aggressive |
| **Alpha** | Rp - (Rf + β×(Rm-Rf)) | Excess return vs risk-adjusted |
| **R²** | — | % variance explained by benchmark |

### 4.4 Backtesting Framework

#### Best Practices:
1. **Walk-forward optimization**: Train → Test → Slide → Repeat
2. **Out-of-sample testing**: Data yang tidak pernah dilihat model
3. **Cross-validation**: K-fold untuk time series (purged k-fold)
4. **Transaction costs**: Include commission, slippage, market impact
5. **Survivorship bias**: Include delisted companies
6. **Look-ahead bias**: Gunakan hanya data yang tersedia saat keputusan
7. **Overfitting prevention**: Fewer parameters, regularization, ensemble

#### Key Metrics:
- **CAGR**: Compound Annual Growth Rate
- **Win rate**: % trades profitable
- **Profit factor**: Gross profit / Gross loss
- **Expectancy**: (Win% × Avg win) - (Loss% × Avg loss)
- **Maximum consecutive losses**: Stress test mental
- **Recovery factor**: Net profit / Max drawdown

---

## 5. ANALISA INTERMARKET & MAKROEKONOMI

### 5.1 Intermarket Relationships (John Murphy Framework)

#### The Four Market Model:
```
Bonds ↑ → Stocks ↑ → Commodities ↑ → Dollar ↓ (Economic expansion)
Bonds ↓ → Stocks ↓ → Commodities ↓ → Dollar ↑ (Economic contraction)
```

#### Key Relationships:
| Relationship | Direction | Logic |
|-------------|-----------|-------|
| **Bonds vs Commodities** | Inverse | Rising commodities = inflation = bond prices fall |
| **Bonds vs Stocks** | Same (usually) | Falling rates = good for stocks |
| **Dollar vs Commodities** | Inverse | Commodities priced in USD |
| **Dollar vs Stocks** | Inverse (usually) | Weak dollar = export competitive |
| **Gold vs Dollar** | Inverse | Gold as dollar alternative |
| **Oil vs Stocks** | Complex | Low oil = consumer benefit, high oil = inflation risk |
| **VIX vs Stocks** | Inverse | Fear index — high VIX = low stocks |

### 5.2 Economic Cycle & Sector Rotation

#### 4 Phases of Economic Cycle:
| Phase | Economy | Bonds | Stocks | Commodities | Best Sectors |
|-------|---------|-------|--------|-------------|--------------|
| **Reflation** | Slowing, rates cut | ↑↑ | ↑ | ↓ | Utilities, Telco, Consumer Staples |
| **Recovery** | Growing, rates low | ↑ | ↑↑ | ↑ | Financials, Industrials, Materials |
| **Overheating** | Strong, rates rising | ↓ | ↑ | ↑↑ | Energy, Commodities, Materials |
| **Stagflation** | Declining, rates high | ↓ | ↓ | ↑ | Energy, Gold, Defensive |

### 5.3 Leading Economic Indicators
- **Yield Curve** (10Y-2Y spread): Inversion = recession signal
- **PMI (Purchasing Managers Index)**: >50 expansion, <50 contraction
- **Consumer Confidence**: Spending prediction
- **Housing Starts**: Leading for construction sector
- **Initial Jobless Claims**: Labor market health
- **M2 Money Supply**: Liquidity in economy

### 5.4 Lagging Economic Indicators
- **GDP Growth**: Reported quarterly, backward-looking
- **Unemployment Rate**: Lags recovery
- **CPI/Inflation**: Lags monetary policy
- **Corporate Profits**: Reported after quarter end

### 5.5 Indonesia-Specific Macro

| Indikator | Sumber | Frekuensi | Impact |
|-----------|--------|-----------|--------|
| **BI Rate** | Bank Indonesia | Bulanan | Biaya modal, arah kurs |
| **Inflation (CPI)** | BPS | Bulanan | Purchasing power, rate decision |
| **Trade Balance** | BPS | Bulanan | IDR strength |
| **FX Reserves** | BI | Bulanan | IDR defense capacity |
| **USD/IDR** | Market | Real-time | Import cost, foreign outflow |
| **CPO Price** | Market | Daily | Sektor sawit (IDX significant) |
| **Coal Price** | Market | Daily | Sektor tambang batubara |
| **Gold Price** | Market | Daily | ANTM, MDKA |

---

## 6. ANALISA BEHAVIORAL FINANCE

### 6.1 Cognitive Biases (Kesalahan Berpikir Sistematis)

| Bias | Definisi | Dampak | Mitigasi |
|------|----------|--------|----------|
| **Loss Aversion** | Loss terasa 2x lebih sakit dari gain sama | Hold loser too long, sell winner too early | Stop-loss otomatis, rules-based |
| **Confirmation Bias** | Cari info yang mendukung, abaikan yang bertentangan | Overconfident in thesis | Devil's advocate, pre-mortem |
| **Anchoring** | Fixate pada angka awal (harga beli, 52-week high) | Tidak cut loss karena "nanti balik" | Fokus pada nilai intrinsic, bukan entry price |
| **Overconfidence** | Terlalu yakin pada kemampuan | Overtrading, position too large | Position sizing, journaling |
| **Recency Bias** | Beri bobot berlebih pada event terbaru | Chase momentum, panic sell | Historical context, long-term data |
| **Hindsight Bias** | "Saya tahu itu akan terjadi" | Overconfidence, tidak belajar | Journal decisions real-time |
| **Representativeness** | Judge probability by similarity | Follow trend terlalu lama | Base rate statistics |
| **Availability** | Info mudah diingat = lebih probable | React pada news sensational | Data-driven, bukan narrative |
| **Endowment Effect** | Nilai apa yang dimiliki lebih tinggi | Tidak mau jual posisi | Compare dengan opportunity cost |
| **Sunk Cost Fallacy** | Continue karena sudah invest | Hold losing position | "What would I do if I didn't own this?" |

### 6.2 Emotional Biases

| Bias | Dampak |
|------|--------|
| **FOMO (Fear of Missing Out)** | Chase rally, beli di puncak |
| **Panic Selling** | Jual di bottom, lock in loss |
| **Greed** | Position terlalu besar, leverage berlebih |
| **Regret Aversion** | Tidak ambil risiko yang perlu |
| **Disposition Effect** | Sell winner too early, hold loser too long |

### 6.3 Market Sentiment Indicators

| Indikator | Sinyal Kontrarian |
|-----------|-------------------|
| **VIX > 30** | Extreme fear → bullish contrarian |
| **VIX < 12** | Complacency → bearish contrarian |
| **Put/Call Ratio > 1.2** | Bearish sentiment → bullish |
| **Put/Call Ratio < 0.5** | Bullish sentiment → bearish |
| **Advance/Decline Line** | Divergence dengan index = warning |
| **New Highs/New Lows** | Divergence = trend weakening |
| **Margin Debt** | Extreme = market top risk |
| **AAII Sentiment Survey** | >50% bullish = contrarian bearish |
| **Social Media Buzz** | Extreme hype = topping signal |

### 6.4 Kahneman-Tversky Prospect Theory

```
Value Function:
- Concave for gains (risk averse) 
- Convex for losses (risk seeking) 
- Steeper for losses than gains (loss aversion)
```

Implikasi: Investor lebih bersedia mengambil risiko untuk menghindari loss daripada untuk mendapatkan gain yang sama besar. Ini menyebabkan disposition effect.

---

## 7. MANAJEMEN PORTOFOLIO

### 7.1 Modern Portfolio Theory (Markowitz)

#### Efficient Frontier
- Kombinasi aset dengan return maksimum untuk setiap level risiko
- Diversifikasi: reduksi risk tanpa sacrifice return
- **Correlation** adalah kunci: semakin rendah korelasi, semakin baik diversifikasi

#### CAPM
```
E(Ri) = Rf + βi × (Rm - Rf)
```
- Beta 1 = move dengan market
- Beta < 1 = defensive (utilities, consumer staples)
- Beta > 1 = aggressive (tech, cyclicals)

### 7.2 Portfolio Allocation Methods

#### Mean-Variance Optimization (Markowitz)
- Maximize: E(Rp) - λ × σp²
- Input: expected returns, covariance matrix
- Output: optimal weights
- Problem: sensitive to input errors (garbage in, garbage out)

#### Black-Litterman
- Mulai dari market equilibrium (implied returns)
- Overlay investor views dengan confidence level
- Hasil: stable, tidak extreme weights
- Formula: E[R] = [(τΣ)^-1 + P'Ω^-1P]^-1 × [(τΣ)^-1Π + P'Ω^-1Q]

#### Risk Parity
- Equal risk contribution dari setiap aset
- Bukan equal weight, tapi equal risk
- Aset vol rendah → weight lebih besar
- Aset vol tinggi → weight lebih kecil
- Iterative: w_i × MRC_i = target_rc untuk semua i

#### Kelly Criterion
```
f* = (p × b - q) / b

f* = fraction of capital to bet
p = probability of winning
q = 1 - p
b = win/loss ratio
```
- Kelly fraction biasanya di-set 0.25-0.5 (quarter/half Kelly) untuk safety
- Overbetting > Kelly = ruin risk
- Underbetting < Kelly = suboptimal growth

### 7.3 Rebalancing Strategies

| Strategi | Trigger | Kelebihan | Kekurangan |
|----------|---------|-----------|------------|
| **Calendar** | Waktu (bulanan/kuartal) | Sederhana | Bisa miss drift besar |
| **Threshold** | Drift > 5% atau 10% | Responsif | Lebih transaksi |
| **Hybrid** | Waktu + threshold | Optimal | Kompleks |
| **Tactical** | Market signal | Fleksibel | Subjektif |

### 7.4 Dynamic Position Sizing

#### Volatility Targeting
```
Position Size = Target Vol / Realized Vol × Capital
```
- Vol tinggi → kurangi posisi
- Vol rendah → tambah posisi
- Target: portfolio vol konstan

#### Drawdown-Based Sizing
| Drawdown | Action |
|----------|--------|
| 0-5% | Full size (100%) |
| 5-10% | Reduce to 75% |
| 10-15% | Reduce to 50% |
| 15-20% | Reduce to 25% |
| >20% | Close all, stop trading |

---

## 8. MANAJEMEN RISIKO PROFESIONAL

### 8.1 Tipe Risiko

| Risiko | Definisi | Mitigasi |
|--------|----------|----------|
| **Market Risk** | Pergerakan harga pasar | Diversifikasi, hedging |
| **Credit Risk** | Counterparty gagal | Counterparty selection, collateral |
| **Liquidity Risk** | Tidak bisa exit posisi | Position sizing, limit order |
| **Operational Risk** | System failure, human error | Redundancy, audit |
| **Model Risk** | Model salah | Backtesting, stress test |
| **Concentration Risk** | Terlalu fokus 1 aset/sekotr | Diversifikasi, position limit |
| **Currency Risk** | Pergerakan FX | Hedging FX |
| **Regulatory Risk** | Perubahan regulasi | Compliance monitoring |

### 8.2 Risk Metrics

| Metric | Formula | Target |
|--------|---------|--------|
| **VaR 95%** | -1.65 × σ × √t | Max loss 1-day, 95% confidence |
| **VaR 99%** | -2.33 × σ × √t | Max loss 1-day, 99% confidence |
| **CVaR (ES)** | E[Loss | Loss > VaR] | Expected loss beyond VaR |
| **Max Drawdown** | (Peak - Trough) / Peak | <15% acceptable |
| **Beta** | Cov(Rp,Rm)/Var(Rm) | Match risk tolerance |
| **Tracking Error** | σ(Rp - Rb) | <5% for passive, >5% active |

### 8.3 Risk Limits (Institutional Standard)

| Limit | Typical Value |
|-------|---------------|
| Max position size | 10% of portfolio |
| Max sector exposure | 25% of portfolio |
| Max single order | 5% of daily volume |
| Max daily loss | 2-3% of capital |
| Max drawdown | 15-20% |
| Max consecutive losses | 5-7 |
| Min cash buffer | 5-10% |

### 8.4 Kill Switch (Emergency Stop)

Triggers:
- Daily loss > limit
- Drawdown > max
- Consecutive losses > max
- System malfunction
- Regulatory halt

Protocol:
1. Halt all new orders
2. Close existing positions (market order)
3. Notify risk manager
4. Require confirmation code to resume

### 8.5 Pre-Trade Risk Checks

1. **Order size check**: Order value < max order limit
2. **Position size check**: Post-trade position < max position limit
3. **Concentration check**: Sector exposure < limit
4. **Portfolio exposure check**: Total exposure < max
5. **Daily loss check**: Today's P&L > -max daily loss
6. **Consecutive loss check**: Streak < max
7. **Lot size check**: Multiple of 100 (IDX standard)
8. **Order frequency check**: No excessive orders

---

## 9. MARKET MICROSTRUCTURE & EKSEUSI

### 9.1 Order Book Mechanics

```
BID (Buyers)          ASK (Sellers)
Price  Volume         Price  Volume
10.00  1,000          10.05  500
9.95   2,000          10.10  1,500
9.90   3,000          10.15  2,000

Spread = 10.05 - 10.00 = 0.05
```

- **Bid-Ask Spread**: Biaya implicit setiap transaksi
- **Depth**: Volume tersedia di setiap level
- **Liquidity**: Kemudahan transaksi tanpa move harga
- **Slippage**: Selisih harga expected vs actual execution

### 9.2 Execution Algorithms

| Algo | Deskripsi | Best For |
|------|-----------|----------|
| **TWAP** | Split order evenly over time | Low volume periods |
| **VWAP** | Match volume profile | Large orders, full day |
| **Implementation Shortfall** | Minimize total cost (timing + impact) | Urgent large orders |
| **POV (Percent of Volume)** | Participate at X% of volume | Continuous execution |
| **Sniper** | Wait for liquidity, strike quickly | Opportunistic |
| **Dark Pool** | Hidden orders | Block trading |

### 9.3 Transaction Cost Analysis (TCA)

```
Total Cost = Explicit Cost + Implicit Cost

Explicit: Commission + Fees + Taxes
Implicit: Spread + Market Impact + Timing Cost + Opportunity Cost

Implementation Shortfall = 
  (Decision Price - Execution Price) / Decision Price × Shares
```

#### Cost Components (IDX/BEI):
| Komponen | Rate |
|----------|------|
| Komisi broker | 0.15% - 0.25% |
| Fee BEI | 0.005% |
| Fee KPEI | 0.005% |
| Biaya KSEI | ~Rp0/share |
| PPh Final (sell) | 0.1% |

### 9.4 Slippage Models

```
Slippage = σ × √(Volume / ADV) × coefficient

σ = daily volatility
ADV = average daily volume
```

- Linear model: slippage ∝ order size
- Square-root model: slippage ∝ √order size (more realistic)
- Almgren-Chriss model: optimal execution trajectory

---

## 10. REGULASI & ATURAN BURSA EFEK INDONESIA (IDX/BEI)

### 10.1 Trading Hours (2025)

| Session | Waktu (WIB) |
|---------|-------------|
| **Pre-Opening** | 08:45 - 09:00 |
| **Opening** | 09:00 - 09:05 (call auction) |
| **Regular Session 1** | 09:00 - 11:30 |
| **Lunch Break** | 11:30 - 13:30 |
| **Regular Session 2** | 13:30 - 15:15 |
| **Pre-Closing** | 15:15 - 15:45 |
| **Closing** | 15:45 - 15:50 (call auction) |
| **Post-Session** | 15:50 - 16:00 |

### 10.2 Trading Unit
- **1 Lot = 100 saham** (sejak Januari 2014, sebelumnya 500 saham)
- **Round lot**: Order harus kelipatan 100
- **Odd lot**: < 100 saham, diperdagangkan di pasar khusus

### 10.3 Price Fraction (Fraksi Harga)

| Harga Saham | Fraksi | Max Deviasi |
|-------------|--------|-------------|
| < Rp200 | Rp1 | Rp10 |
| Rp200 - Rp500 | Rp2 | Rp20 |
| Rp500 - Rp2,000 | Rp5 | Rp50 |
| Rp2,000 - Rp5,000 | Rp10 | Rp100 |
| > Rp5,000 | Rp25 | Rp250 |

### 10.4 Auto Rejection (Efektif April 2025)

| Harga Referensi | Batas Auto Rejection |
|-----------------|----------------------|
| Rp50 - Rp200 | ±35% |
| Rp200 - Rp5,000 | ±25% |
| > Rp5,000 | ±20% |
| IPO (hari pertama) | 2x batas normal |
| Warrant (hari pertama) | Sesuai underlying |

**Lot Auto Rejection**: > 50,000 lot atau > 5% saham tercatat (mana yang lebih kecil)

### 10.5 Settlement
- **T+2**: Regular market — penyelesaian 2 hari kerja setelah transaksi
- **T+0**: Cash market — penyelesaian hari yang sama
- **Negotiated market**: T+0 atau sesuai kesepakatan
- **KPEI**: Clearing agency — menjamin penyelesaian
- **KSEI**: Custodian — penyimpanan saham (KSEI Sub-Registry)

### 10.6 Margin Trading & Short Selling

| Aspek | Regulasi |
|-------|----------|
| **Margin Trading** | Boleh dengan persyaratan: saham marginable, min Rp50jt, margin 30-50% |
| **Short Selling** | Terbatas — hanya saham tertentu, perlu borrowing arrangement |
| **Margin Call** | Jika equity < maintenance margin, harus top-up atau likuidasi |
| **Marginable Stocks** | Ditentukan oleh BEI (LQ45, IDX30 umumnya marginable) |

### 10.7 Circuit Breaker

| Trigger | Tindakan |
|---------|----------|
| IHSG turun >5% | Halt 30 menit |
| IHSG turun >10% | Halt 30 menit |
| IHSG turun >15% | Halt 30 menit |
| IHSG turun >20% | Halt sampai akhir sesi |

### 10.8 Indeks Utama IDX

| Indeks | Jumlah Saham | Kriteria |
|--------|-------------|----------|
| **IHSG (Composite)** | Semua | Market cap weighted, semua saham tercatat |
| **LQ45** | 45 | Likuiditas tinggi + fundamental baik |
| **IDX30** | 30 | Paling likuid dari LQ45 |
| **IDX80** | 80 | Market cap besar + likuid |
| **IDXGROWTH** | 30 | Growth stocks |
| **IDXVALUE** | 30 | Value stocks |
| **Sektoral** | Per sektor | 9 sektor: Finance, Trade, Mining, dll |
| **ISSI** | Semua syariah | Jakarta Islamic Index |

### 10.9 Biaya Transaksi Lengkap (IDX)

| Komponen | Rate | Buy/Sell |
|----------|------|----------|
| Komisi Sekuritas | 0.15-0.25% | Both |
| BEI Fee | 0.005% | Both |
| KPEI Fee | 0.003% | Both |
| KSEI Fee | Rp1/share | Both |
| PPh Final | 0.1% | Sell only |
| **Total Buy** | ~0.16-0.26% | |
| **Total Sell** | ~0.26-0.36% | |
| **Round-trip** | ~0.42-0.62% | |

---

## 11. SEKTOR & INDUSTRI DI IDX

### 11.1 Klasifikasi Sektor (IDX Industrial Classification)

| Sektor | Sub-Sektor | Contoh Saham |
|--------|-----------|--------------|
| **Financials** | Bank, Asuransi, Sekuritas, REIT | BBCA, BBRI, BMRI, BBNI, BBTN |
| **Consumer Cyclicals** | Retail, Auto, Media, Textile | ASII, MAPA, RALS, MYOR |
| **Consumer Non-Cyclicals** | Food, Beverage, Personal Care | UNVR, ICBP, INDF, KLBF, TSPC |
| **Energy** | Oil & Gas, Coal, Renewable | ADRO, PTBA, MEDC, PGAS |
| **Basic Materials** | Mining, Chemicals, Metals | ANTM, INCO, MDKA, TPIA, SMGR |
| **Industrials** | Construction, Transport, Logistics | WIKA, WSKT, JSMR, TBIG |
| **Infrastructure** | Telco, Utilities, Ports | TLKM, ISAT, EXCL, POWR |
| **Property** | Real Estate, Developers | CTRA, BSDE, LPKR, PWON |
| **Healthcare** | Pharma, Hospital, Devices | KLBF, INAF, SAME, HEAL |
| **Technology** | IT, Software | MTDL, LUAM, DIVA |

### 11.2 Sektor Dominan di IDX (by weight in IHSG)

1. **Financials** (~35-40%) — Banks dominate: BBCA, BBRI, BMRI
2. **Consumer Cyclicals** (~15%) — ASII, retail
3. **Basic Materials** (~12%) — Mining, coal
4. **Energy** (~10%) — Coal mining
5. **Infrastructure** (~8%) — Telco
6. **Consumer Non-Cyclicals** (~8%) — FMCG
7. **Property** (~5%)
8. **Industrials** (~4%)
9. **Healthcare** (~2%)
10. **Technology** (~1%)

### 11.3 Karakteristik Sektor vs Economic Cycle

| Sektor | Beta | Cyclical? | Best Phase |
|--------|------|-----------|------------|
| Financials | 1.2 | Pro-cyclical | Recovery, Overheating |
| Consumer Cyclicals | 1.3 | Cyclical | Recovery |
| Energy | 1.1 | Cyclical | Overheating |
| Basic Materials | 1.2 | Cyclical | Recovery, Overheating |
| Consumer Non-Cyclicals | 0.7 | Defensive | Reflation, Stagflation |
| Healthcare | 0.8 | Defensive | All phases |
| Infrastructure | 0.9 | Defensive | Reflation |
| Property | 1.1 | Cyclical | Recovery |
| Technology | 1.4 | Growth | Recovery |

---

## 12. STRATEGI TRADING PROFESIONAL

### 12.1 Tipe Strategi

| Strategi | Horizon | Analisa | Frekuensi |
|----------|---------|---------|-----------|
| **Scalping** | Detik-menit | Order flow, microstructure | 100+ per hari |
| **Day Trading** | Intraday | Teknikal, momentum | 5-20 per hari |
| **Swing Trading** | 2-10 hari | Teknikal + sentimen | 2-10 per minggu |
| **Position Trading** | Minggu-bulan | Teknikal + fundamental | 1-5 per bulan |
| **Investing** | Bulan-tahun | Fundamental + makro | <1 per bulan |

### 12.2 Entry/Exit Framework

#### Entry Checklist:
- [ ] Trend searah (multi-timeframe: daily + weekly)
- [ ] Level support dekat (risk reward minimal 1:2)
- [ ] Volume konfirmasi (volume naik pada breakout)
- [ ] Tidak ada event berisiko dekat (earnings, dividend)
- [ ] Position size sesuai risk management
- [ ] Stop-loss level ditentukan sebelum entry
- [ ] Fundamental tidak ada red flag

#### Exit Rules:
- **Stop-loss hit**: Exit tanpa pertimbangan
- **Target tercapai**: Exit atau trail stop
- **Thesis berubah**: Exit ketika alasan beli tidak lagi valid
- **Risk regime change**: De-risk ketika market regime shifts
- **Time stop**: Exit jika tidak bergerak dalam X hari

### 12.3 Risk-Reward Ratio

| R:R | Win Rate Needed | Assessment |
|-----|----------------|------------|
| 1:1 | 50% | Break even |
| 1:2 | 34% | Profitable |
| 1:3 | 25% | Very profitable |
| 1:5 | 17% | Excellent |

### 12.4 Trading Plan Template

```
1. Market Context: Trend? Regime? Volatility?
2. Setup: Pattern? Signal? Catalyst?
3. Entry: Price level + trigger
4. Stop-Loss: Level + reason
5. Target: Multiple targets (1:2, 1:3, 1:5)
6. Position Size: % risk × capital / (entry - stop)
7. Management: Trail stop? Scale out? Add?
8. Journal: Log reason, emotion, outcome
```

### 12.5 Top-Down Analysis Framework

```
Step 1: Global Macro
  → Fed policy, USD direction, global growth, commodity cycle

Step 2: National Macro
  → BI rate, inflation, trade balance, IDR strength

Step 3: Market Regime
  → Bull/Bear/Sideways? Volatility? Breadth?

Step 4: Sector Selection
  → Which sectors benefit from current macro?

Step 5: Stock Selection
  → Fundamental screen → Technical entry timing

Step 6: Risk Management
  → Position sizing, stop-loss, portfolio limits

Step 7: Execution
  → Entry, monitoring, exit, journaling
```

### 12.6 Bottom-Up Analysis Framework

```
Step 1: Stock Screen
  → P/E < 15, ROE > 15%, Debt < 2x equity, Growth > 10%

Step 2: Deep Dive
  → Read 3 financial statements, 5-year history

Step 3: Competitive Analysis
  → Moat? Market share? Industry dynamics?

Step 4: Valuation
  → DCF + multiples + reverse DCF

Step 5: Technical Timing
  → Chart pattern, support/resistance, momentum

Step 6: Risk Assessment
  → What can go wrong? Worst case?

Step 7: Decision
  → Buy / Watch / Pass
```

---

## 13. SUMBER DATA & TOOLS

### 13.1 Data Sources

| Data | Source | Coverage | Cost |
|------|--------|----------|------|
| **IDX Price Data** | Yahoo Finance (^JKSE, ticker.JK) | OHLCV | Free |
| **IDX Fundamental** | Yahoo Finance (ticker.info) | P/E, PBV, ROE, dll | Free |
| **US Market** | Yahoo Finance (^GSPC, ^IXIC, ^DJI) | OHLCV | Free |
| **Macro US** | FRED API | CPI, Unemployment, FedFunds, Treasury | Free |
| **Commodities** | Yahoo Finance (GC=F, CL=F) | Gold, Oil | Free |
| **FX** | Yahoo Finance (IDR=X) | USD/IDR | Free |
| **VIX** | Yahoo Finance (^VIX) | Fear index | Free |
| **IDX Financial Statements** | yfinance Ticker.financials | Income, Balance, Cash Flow | Free |
| **Indonesia Macro** | BPS, Bank Indonesia | Inflation, BI rate, trade | Free |

### 13.2 Tools & Libraries

| Tool | Fungsi |
|------|--------|
| **Python** | Core language |
| **pandas** | Data manipulation |
| **numpy** | Numerical computation |
| **yfinance** | Yahoo Finance data |
| **scikit-learn** | ML models |
| **xgboost** | Gradient boosting |
| **lightgbm** | Light gradient boosting |
| **matplotlib/plotly** | Visualization |
| **streamlit** | Web app framework |
| **sqlite3** | Database (embedded) |
| **ta-lib** | Technical indicators (alternative) |
| **PyPortfolioOpt** | Portfolio optimization |
| **backtrader** | Backtesting framework |

---

## 14. GLOSARIUM LENGKAP

### A
- **Alpha**: Excess return vs benchmark (risk-adjusted)
- **ADR**: American Depositary Receipt
- **ADX**: Average Directional Index — trend strength
- **ATR**: Average True Range — volatility measure
- **Accumulation**: Institusi beli diam-diam (Wyckoff)

### B
- **Beta**: Sensitivitas terhadap market (1=market)
- **Blue Chip**: Saham besar, likuid, fundamental kuat
- **Bull/Bear Market**: Tren naik/turun jangka panjang
- **Bollinger Bands**: Volatility band (MA ± 2σ)

### C
- **CAGR**: Compound Annual Growth Rate
- **CAPM**: Capital Asset Pricing Model
- **Candlestick**: Chart type showing OHLC
- **Circuit Breaker**: Halt otomatis saat market crash
- **CPI**: Consumer Price Index — inflation
- **CVaR**: Conditional Value at Risk (Expected Shortfall)

### D
- **DCF**: Discounted Cash Flow — valuasi intrinsic
- **DER**: Debt to Equity Ratio
- **Distribution**: Institusi jual ke retail (Wyckoff)
- **Drawdown**: Penurunan dari peak ke trough

### E
- **EPS**: Earnings Per Share
- **EMA**: Exponential Moving Average
- **EV/EBITDA**: Enterprise Value to EBITDA — valuasi

### F
- **FCF**: Free Cash Flow
- **Fibonacci**: Retracement levels: 23.6%, 38.2%, 50%, 61.8%, 78.6%
- **Fraksi Harga**: Unit perubahan harga di IDX

### G
- **GDP**: Gross Domestic Product
- **Graham**: Benjamin Graham — value investing father

### H
- **Hedge**: Posisi untuk mengurangi risiko
- **Heikin-Ashi**: Modified candlestick (smoothing)

### I
- **ICHIMOKU**: Japanese cloud indicator system
- **IPO**: Initial Public Offering
- **IR**: Information Ratio — alpha per tracking error

### K
- **Kelly Criterion**: Optimal position sizing formula
- **KPEI**: Kliring Penjaminan Efek Indonesia
- **KSEI**: Kustodian Sentral Efek Indonesia

### L
- **LQ45**: Indeks 45 saham likuid + fundamental baik
- **LDR**: Loan to Deposit Ratio (banking)

### M
- **MACD**: Moving Average Convergence Divergence
- **Margin of Safety**: Diskon dari nilai intrinsic
- **MFI**: Money Flow Index — volume-weighted RSI
- **Moat**: Competitive advantage (Warren Buffett)

### N
- **NPL**: Non-Performing Loan (banking)
- **NIM**: Net Interest Margin (banking)

### O
- **OBV**: On Balance Volume
- **OJK**: Otoritas Jasa Keuangan (regulator Indonesia)

### P
- **P/E (PER)**: Price to Earnings Ratio
- **PBV**: Price to Book Value
- **PEG**: P/E to Growth Ratio
- **PMI**: Purchasing Managers Index
- **PSAR**: Parabolic SAR
- **Put/Call Ratio**: Sentiment indicator

### R
- **RSI**: Relative Strength Index (0-100)
- **ROE**: Return on Equity
- **ROA**: Return on Assets
- **ROIC**: Return on Invested Capital
- **Risk Parity**: Equal risk contribution allocation

### S
- **Sharpe Ratio**: (Return - Rf) / Std Dev
- **Short Selling**: Borrow → sell → buy back lower
- **Slippage**: Difference between expected and actual execution price
- **Sortino Ratio**: Like Sharpe but only downside deviation
- **Spring**: False breakdown in Wyckoff accumulation
- **SOS/SOW**: Sign of Strength/Weakness (Wyckoff)

### T
- **T+2**: Settlement 2 days after trade
- **TCA**: Transaction Cost Analysis
- **TE**: Tracking Error
- **TRIX**: Triple EMA momentum

### V
- **VaR**: Value at Risk — max loss at confidence level
- **VIX**: Volatility Index — fear gauge
- **VWAP**: Volume Weighted Average Price
- **VSA**: Volume Spread Analysis (Wyckoff derivative)

### W
- **WACC**: Weighted Average Cost of Capital
- **Wickoff Method**: Smart money analysis via price+volume
- **Williams %R**: Momentum oscillator (-100 to 0)

### Y
- **Yield Curve**: Plot of yields across maturities
- **YTD**: Year to Date

---

## 15. MULTI-TIMEFRAME ANALYSIS (MTF)

### 15.1 Filosofi MTF

> **Aturan Emas Trading**: "Trade in the direction of the higher timeframe trend."

Multi-Timeframe Analysis (MTF) adalah teknik menganalisis aset yang sama dari beberapa
periode waktu (timeframe) secara bersamaan untuk mendapatkan **confluence** — keselarasan
sinyal antar timeframe. Ketika semua timeframe menunjukkan arah yang sama, probabilitas
keberhasilan trade meningkat signifikan.

### 15.2 Hierarki Timeframe

| Timeframe | Peran | Bobot | Fungsi |
|-----------|-------|-------|--------|
| **Weekly (1W)** | Higher Timeframe (HTF) | 35% | Trend utama, arah makro |
| **Daily (1D)** | Core Timeframe | 30% | Sinyal utama, keputusan trade |
| **4 Hour (4H)** | Mid Timeframe | 20% | Konfirmasi, struktur market |
| **1 Hour (1H)** | Lower Timeframe (LTF) | 15% | Entry timing, presisi |

### 15.3 Confluence Scoring

Setiap timeframe dianalisis menggunakan indikator teknikal (RSI, MACD, MA Alignment,
Bollinger Bands, Volume) dan menghasilkan:
- **Signal**: BUY / SELL / HOLD
- **Score**: 0-100 (50 = neutral)
- **Trend**: Uptrend / Downtrend / Sideways

**Confluence Score** = weighted average dari semua timeframe berdasarkan bobot hierarki.

| Confluence Strength | Kriteria | Confidence Adjustment |
|---------------------|----------|----------------------|
| **Strong** | Semua timeframe searah | +15% boost |
| **Moderate** | ≥75% timeframe searah | +8% boost |
| **Weak** | ≥50% timeframe searah | -5% reduce |
| **Mixed** | Timeframe bertentangan | -15% reduce |

### 15.4 Entry Timing Rules

| Kondisi | Entry Timing | Arti |
|---------|-------------|------|
| HTF + LTF searah bullish | **Good** | Waktu yang tepat untuk entry long |
| HTF + LTF searah bearish | **Good** | Waktu yang tepat untuk entry short |
| LTF neutral / sideways | **OK** | Boleh entry tapi hati-hati |
| HTF dan LTF bertentangan | **Wait** | Tunggu hingga alignment lebih baik |

### 15.5 Implementasi di Aplikasi

Aplikasi ini mengimplementasikan MTF di `src/mtf.py` dengan:
- `run_mtf_analysis()`: Analisis 4 timeframe (1W, 1D, 4H, 1H)
- `get_mtf_confidence_adjustment(): Adjustment factor untuk confidence prediksi
- Integrasi dengan `predictor.py`: MTF score menyesuaikan signal dan confidence
- Override signal: Strong bearish confluence → HOLD; Mixed signals → HOLD

### 15.6 Referensi

- BOZ: Multi-timeframe confluence dengan verdict box
- Market Digest: Multi-timeframe ScoreCard (0-100 composite)
- TrendSpider: Multi-timeframe analysis (MTFA) — weekly indicators on daily chart
- Alexander Elder: "Triple Screen Trading System" — konsep serupa (Elder Ray)

---

## 16. ADVANCED PORTFOLIO OPTIMIZATION

### 16.1 Lima Metode Optimasi Portofolio

Aplikasi ini mengimplementasikan 5 metode optimasi portofolio di `src/portfolio.py`:

#### 1. Markowitz (Mean-Variance Optimization)
- **Konsep**: Maksimalkan return untuk setiap level risiko (Efficient Frontier)
- **Kelebihan**: Sederhana, well-established (Nobel Prize 1952)
- **Kelemahan**: Sensitif terhadap input errors, extreme weights, tidak robust
- **Best for**: Aset dengan return distribution normal dan estimasi yang akurat

#### 2. Black-Litterman
- **Konsep**: Mulai dari market equilibrium (implied returns), overlay investor views
- **Formula**: E[R] = [(τΣ)^-1 + P'Ω^-1P]^-1 × [(τΣ)^-1 Π + P'Ω^-1 Q]
- **Kelebihan**: Stable weights, tidak extreme, menggabungkan views dengan market
- **Kelemahan**: Memerlukan market cap weights, views subjective
- **Best for**: Ketika analyst punya views tentang aset tertentu

#### 3. Risk Parity
- **Konsep**: Equal risk contribution dari setiap aset (bukan equal weight)
- **Kelebihan**: Diversifikasi yang lebih baik, low-vol aset dapat weight lebih besar
- **Kelemahan**: Bisa underperform saat high-vol aset outperform
- **Best for**: Portfolio balanced dengan risk distribution yang merata

#### 4. Hierarchical Risk Parity (HRP)
- **Konsep**: Clustering-based allocation — group similar aset, allocate risk across clusters
- **Kelebihan**: Tidak perlu invert covariance matrix, robust terhadap noise
- **Kelemahan**: Lebih kompleks, hasil bisa berbeda dari Markowitz
- **Best for**: Portfolio dengan banyak aset yang berkorelasi (IDX: banyak bank)

#### 5. CVaR Optimization
- **Konsep**: Minimalkan Conditional Value at Risk (Expected Shortfall)
- **Formula**: Minimize VaR + 1/(α×n) × Σ tail_vars
- **Kelebihan**: Fokus pada tail risk, better untuk non-normal distributions
- **Kelemahan**: Computationally intensive, banyak constraints
- **Best for**: Portfolio dengan fat-tail risk, pasar emerging (IDX)

### 16.2 Perbandingan Metode

| Metode | Return | Volatility | Sharpe | Diversifikasi | Robustness |
|--------|--------|------------|--------|---------------|------------|
| Markowitz | Tinggi | Tinggi | Variabel | Rendah | Rendah |
| Black-Litterman | Medium | Medium | Stabil | Medium | Tinggi |
| Risk Parity | Medium | Rendah | Stabil | Tinggi | Tinggi |
| HRP | Medium | Rendah | Stabil | Sangat Tinggi | Sangat Tinggi |
| CVaR | Medium | Rendah | Stabil | Tinggi | Tinggi |

### 16.3 Penggunaan di Aplikasi

```python
from src.portfolio import compare_portfolio_methods

# Run semua metode dan bandingkan
results = compare_portfolio_methods(returns_df)

# Results berisi:
# - results["markowitz"]: Efficient frontier + optimal portfolio
# - results["black_litterman"]: BL expected returns + weights
# - results["risk_parity"]: Equal risk contribution weights
# - results["hrp"]: HRP clustering-based weights
# - results["cvar"]: CVaR-optimized weights
# - results["comparison"]: DataFrame sorted by Sharpe ratio
```

### 16.4 Referensi

- Markowitz (1952), "Portfolio Selection" — Nobel Prize
- Black & Litterman (1991), "Global Portfolio Optimization"
- Maillard et al. (2010), "The Properties of Equally Weighted Risk Contribution Portfolios"
- Lopez de Prado (2016), "Building Diversified Portfolios that Outperform Out of Sample"
- Rockafellar & Uryasev (2000), "Optimization of CVaR"

---

## 17. SMART MONEY CONCEPTS (SMC) / ICT METHODOLOGY

> **Evolusi modern dari Wyckoff Method** (bab 3.6). Dikembangkan oleh Michael Huddleston (Inner Circle Trader / ICT) sejak 2010s, kini menjadi framework dominant di komunitas trading profesional retail. SMC berfokus pada footprint institusi di chart — bukan indikator lagging.

### 17.1 Filosofi SMC

Smart Money Concepts berangkat dari premis bahwa **institusi (bank, hedge fund, pension fund)** menggerakkan market dengan order size yang sangat besar. Mereka tidak bisa enter/exit dengan satu klik — mereka harus:
1. **Break orders menjadi chunks** (algo execution)
2. **Engineer false breakouts** untuk mengumpulkan likuiditas (stop-loss retail)
3. **Mengisi posisi di zona tertentu** yang meninggalkan jejak teridentifikasi

SMC trader belajar membaca jejak ini untuk **trade searah institusi**, bukan melawan.

### 17.2 Core Concepts

#### Order Blocks (OB)
Candle terakhir sebelum impuls institusi:
- **Bullish OB**: Candle bearish terakhir sebelum move bullish eksplosif
- **Bearish OB**: Candle bullish terakhir sebelum move bearish eksplosif
- Zona ini menyimpan "unfinished institutional business" — harga sering kembali untuk mengisi sisa order
- Validasi: OB harus diikuti displacement (move kuat) dengan volume tinggi

#### Fair Value Gaps (FVG)
Imbalance harga dari move cepat — gap antara candle sebelum dan sesudah impuls:
- **Bullish FVG**: Low candle-3 tidak menyentuh High candle-1 (gap ke atas)
- **Bearish FVG**: High candle-3 tidak menyentuh Low candle-1 (gap ke bawah)
- FVG bertindak sebagai magnet — harga kembali untuk "fill the gap"
- Multiple FVG yang tidak terisi = strong directional bias

#### Liquidity Sweeps (Stop Hunts)
Institusi mencari likuiditas di tempat retail menaruh stop-loss:
- **External Liquidity (ERL)**: Di atas swing high / di bawah swing low yang jelas
- **Internal Liquidity (IRL)**: Di dalam range (equal highs, equal lows)
- Pola: Spike cepat menembus level → snap back = likuiditas diambil
- Setelah sweep, harga bergerak ke arah sebenarnya karena opposing orders sudah habis

#### Break of Structure (BOS) & Change of Character (CHoCH)
- **BOS**: Harga menembus swing high/low sebelumnya → konfirmasi kelanjutan tren
- **CHoCH**: Harga mematahkan struktur terakhir → indikasi awal reversal
- BOS + displacement = validasi institusi; tanpa BOS, setup OB/FVG kurang reliabel

#### Market Structure Shift (MSS)
Perubahan struktur market dari bullish ke bearish (atau sebaliknya):
- Bullish MSS: Higher low → break higher high
- Bearish MSS: Lower high → break lower low
- MSS adalah konfirmasi akhir bahwa institusi sudah pindah posisi

### 17.3 ICT Kill Zones

Window waktu ketika aktivitas institusional paling tinggi:

| Kill Zone | Waktu (ET) | Karakteristik |
|-----------|------------|---------------|
| **London Open** | 02:00 - 05:00 | Displacement Eropa, sweep Asian session |
| **NY Pre-Open** | 07:00 - 09:30 | Positioning US desks sebelum open |
| **NY Open** | 09:30 - 11:30 | Volume tertinggi, setup paling reliable |
| **London Close** | 10:00 - 12:00 | Squaring positions, reversal window |
| **Midday Lull** | 12:00 - 14:00 | Likuiditas tipis — hindari |

> **Untuk IDX (WIB = ET + 11-12 jam)**: Kill zone IDX efektif di sesi opening (09:00-10:30) dan sesi afternoon (13:30-15:00) — window dengan volume tertinggi.

### 17.4 Power of 3 (PO3) — AMD Pattern

ICT framework untuk pola harian institusional:

```
Accumulation → Manipulation → Distribution (AMD)

1. Accumulation: Range sempit di awal session, institusi build position
2. Manipulation: False move (sweep) ke satu arah untuk ambil likuiditas
3. Distribution: Real move ke arah sebenarnya setelah likuiditas terkumpul
```

- Jika manipulation ke atas (sweep highs) → distribution ke bawah (bearish day)
- Jika manipulation ke bawah (sweep lows) → distribution ke atas (bullish day)
- PO3 paling reliable di London dan NY kill zones

### 17.5 Premium & Discount Arrays

Konsep equilibrium price — pembagian range menjadi zona:

```
      Premium Zone (sell zone)
      ─────────────────────────  ← Swing High
      │     75% — Premium
      │
      │     50% — Equilibrium
      │
      │     25% — Discount
      ─────────────────────────  ← Swing Low
      Discount Zone (buy zone)
```

- **Premium**: Harga di atas 50% equilibrium → cari setup SELL (bearish OB, bearish FVG)
- **Discount**: Harga di bawah 50% equilibrium → cari setup BUY (bullish OB, bullish FVG)
- **Equilibrium**: Midpoint range — area transisi, hindari entry di sini

### 17.6 SMC Trading Checklist (Top-Down)

```
1. HTF Bias (Daily/4H):
   → Tentukan arah: BOS bullish atau bearish?
   → Tandai OB dan FVG yang belum terisi

2. Mark Liquidity:
   → External: swing high/low hari sebelumnya
   → Internal: equal highs/lows di dalam range

3. Wait for Sweep:
   → Harga menyapu likuiditas → displacement berlawanan?
   → CHoCH setelah sweep = sinyal entry

4. Entry (LTF — 15m/5m):
   → Pullback ke OB atau FVG di zona discount/premium
   → Micro BOS di LTF konfirmasi
   → Stop-loss di bawah/atas OB
   → Target: likuiditas berlawanan berikutnya

5. Risk Management:
   → Max 1% risk per trade
   → R:R minimum 1:2
   → Trail stop di belakang OB baru
```

### 17.7 Relasi SMC dengan Wyckoff

| Wyckoff | SMC Equivalent |
|---------|----------------|
| Accumulation | Order Block + FVG di base |
| Spring | Liquidity Sweep (bearish) |
| UTAD | Liquidity Sweep (bullish) |
| SOS | BOS + Displacement |
| SOW | CHoCH + Displacement |
| Markup | Distribution phase (PO3) |
| Distribution | Order Block (bearish) + FVG di top |

### 17.8 Implementasi di Aplikasi

Aplikasi ini mengimplementasikan SMC di `src/smc.py` dengan:
- `detect_order_blocks()`: Identifikasi bullish/bearish OB dari OHLC data
- `detect_fvg()`: Deteksi Fair Value Gaps
- `detect_liquidity_sweeps(): Sweep detection di swing high/low
- `detect_bos_choch()`: Market structure analysis
- `run_smc_analysis()`: Combined SMC analysis dengan signal dan rekomendasi
- Integrasi dengan `predictor.py`: SMC signal menyesuaikan confidence prediksi

### 17.9 Referensi

- Michael Huddleston (ICT), "Inner Circle Trader" series (2010s-2020s)
- Rubén Villahermosa, "Trading Wyckoff" (2020) — SMC systematization
- ICT YouTube channel — kill zones, PO3, AMD pattern
- Wyckoff Method (bab 3.6) — fondasi teoritis SMC

---

## 18. ADVANCES IN FINANCIAL MACHINE LEARNING (LOPEZ DE PRADO)

> Berdasarkan Marcos Lopez de Prado, "Advances in Financial Machine Learning" (2018) — buku fundamental yang mengubah cara practitioner serius mengembangkan strategi ML untuk finance. Dokumen ini sudah mencantumkannya di referensi (baris 1319), namun berikut konsep-konsep kuncinya yang diimplementasikan di aplikasi.

### 18.1 Triple-Barrier Labeling

**Masalah**: Fixed-horizon labeling (return di T+N) mengabaikan path harga — trade bisa hit stop-loss dulu sebelum profit, tapi label tetap menghitung return akhir.

**Solusi**: Tiga barrier yang mensimulasikan real trading:

```
                    Upper Barrier (Profit-Taking)
                    ──────────────────────────── ← +σ × multiplier
                    │
                    │     Vertical Barrier
                    │     (Max Holding Period)
                    │         │
                    │         │
                    ──────────────────────────── ← Entry Price
                    │         │
                    │
                    ──────────────────────────── ← -σ × multiplier
                    Lower Barrier (Stop-Loss)
```

- **Upper barrier**: Take-profit level (multiple of volatility)
- **Lower barrier**: Stop-loss level
- **Vertical barrier**: Maximum holding period (time stop)
- **Label**: Barrier mana yang tersentuh PERTAMA
  - Upper → +1 (buy signal)
  - Lower → -1 (sell signal)
  - Vertical → 0 (time stop, no clear signal)

> **Keunggulan**: Labels mencerminkan realitas trading (path-dependent), adaptif terhadap volatilitas (barrier melebar saat vol tinggi), dan menghasilkan model yang lebih robust.

### 18.2 Meta-Labeling

**Two-model architecture** yang memisahkan direction dari conviction:

```
Primary Model → Direction (Long/Short)
         ↓
Meta-Model  → Should I take this trade? (0 or 1)
         ↓
Bet Size    → Position size = probability × signal
```

- **Primary model**: Tentukan arah (strategi existing: teknikal, fundamental, dll)
- **Meta-model**: ML model yang belajar dari primary model — prediksi apakah primary signal akan benar
- **Output**: Probability 0-1 → convert ke bet size
- **Benefit**: Dramatis improve F1-score, filter false positives, preserve capital saat confidence rendah

### 18.3 Fractional Differentiation

**Dilemma klasik**: 
- d=1 (returns): Stationary tapi hilang semua memory/level
- d=0 (prices): Full memory tapi non-stationary (ML models gagal)

**Solusi**: Fractional d (0.3-0.6) — stationary dengan memory retained:

```
d=0.0: Raw prices (full memory, non-stationary)
d=0.4: Fractional diff (memory + stationarity) ← optimal
d=1.0: Returns (no memory, stationary)
```

- Implementasi: Fixed-width window fractional differentiation (FFD)
- Cari d minimum yang lulus ADF test (Augmented Dickey-Fuller)
- Hasil: Feature yang lebih informatif untuk ML models

### 18.4 Purged K-Fold Cross-Validation

**Masalah**: Standard k-fold menghasilkan information leakage di time series — data test overlap dengan data train karena autocorrelation.

**Solusi**:
1. **Purge**: Hapus observasi di sekitar train/test boundary (overlap period)
2. **Embargo**: Tambahan buffer setelah test set untuk mencegah leakage dari lag features

```
Train | [purge] | Test | [embargo] | Train
```

- Eliminasi information leakage yang menyebabkan inflated backtest results
- Khusus untuk time-series data dengan autocorrelation

### 18.5 Combinatorial Purged Cross-Validation (CPCV)

**Masalah**: Walk-forward menghasilkan satu backtest path — terlalu fragile, bisa kebetulan untung.

**Solusi**: Generate ribuan backtest paths:
1. Bagi data menjadi N grup
2. Kombinasi grup untuk train/test (C(N, k) kombinasi)
3. Setiap kombinasi menghasilkan satu backtest path
4. Distribusi hasil = estimasi real performance

### 18.6 Deflated Sharpe Ratio (DSR)

**Masalah**: Multiple testing bias — jika Anda test 100 strategi, beberapa akan terlihat bagus purely by chance.

**DSR Formula**:
```
DSR = (SR_observed - E[max(SR)] under H0) / σ(SR)

E[max(SR)] ≈ √(2 × ln(N)) × σ(SR)  (untuk N strategi independen)
```

- Koreksi untuk jumlah strategi yang di-test
- Jika DSR < 0 → performance kemungkinan false discovery
- Standard di quant hedge funds untuk evaluate strategy significance

### 18.7 Probability of Backtest Overfitting (PBO)

**Konsep**: Quantify probabilitas bahwa strategi Anda curve-fitted:
- PBO > 0.5 → kemungkinan besar overfitted
- PBO < 0.5 → strategi robust
- Dihitung dari CPCV distribution — probabilitas rank out-of-sample lebih rendah dari in-sample

### 18.8 Implementasi di Aplikasi

Aplikasi ini mengimplementasikan Lopez de Prado techniques di `src/afml.py`:
- `triple_barrier_labels()`: Labeling dengan profit/stop/time barriers
- `meta_labeling()`: Two-model pipeline untuk bet sizing
- `fractional_differentiation()`: FFD untuk stationarity + memory
- `purged_kfold()`: Cross-validation tanpa leakage
- `deflated_sharpe_ratio()`: DSR untuk multiple testing correction
- `probability_of_backtest_overfitting()`: PBO calculation
- Integrasi dengan `predictor.py` dan `validation.py`

### 18.9 Referensi

- Lopez de Prado, M. (2018). "Advances in Financial Machine Learning". Wiley. Chapters 3-7
- Lopez de Prado, M. (2020). "Machine Learning for Asset Managers". Cambridge
- mlfinlab library: Open-source implementation of AFML methodologies
- Hudson & Thames: "Fractional Differentiation" article

---

## 19. DEEP REINFORCEMENT LEARNING UNTUK TRADING

> Frontier terbaru quantitative finance — agen AI yang belajar optimal trading policy melalui trial-and-error di environment simulasi pasar.

### 19.1 Konsep Dasar Reinforcement Learning

```
Agent ←── State ──→ Environment (Market)
  │                   │
  └── Action ────────→
  │                   │
  ←── Reward ────────┘
```

- **State (S)**: Market conditions (price, indicators, portfolio state)
- **Action (A)**: Buy/Sell/Hold, position size, order type
- **Reward (R)**: P&L, risk-adjusted return, Sharpe ratio
- **Policy (π)**: Strategy yang dipelajari agent — mapping state → action
- **Value Function (Q)**: Expected cumulative reward dari state-action pair

### 19.2 Algoritma DRL untuk Trading

| Algoritma | Tipe | Kelebihan | Best For |
|-----------|------|-----------|----------|
| **DQN** | Value-based | Stable, well-understood | Discrete actions (buy/sell/hold) |
| **PPO** | Policy gradient | Stable, sample-efficient | Continuous actions (position sizing) |
| **SAC** | Off-policy + entropy | Exploration-efficient | Complex environments |
| **A2C** | Actor-Critic | Fast training | Simple strategies |
| **DDPG** | Deterministic policy | Continuous control | Hedging, execution |

### 19.3 FinRL Framework

**FinRL** (AI4Finance Foundation) — open-source framework untuk DRL trading:

```
FinRL Pipeline:
1. Environment: Stock trading simulation (OpenAI Gym interface)
2. State: Technical indicators + fundamental + portfolio state
3. Action: {0: Hold, 1: Buy, 2: Sell} atau continuous [−1, +1]
4. Reward: Portfolio return / Sharpe ratio
5. Training: DQN/PPO/SAC via Stable-Baselines3
6. Evaluation: Out-of-sample backtest
```

- **FinRL-DeepSeek (2025)**: LLM-infused PPO — menggabungkan news sentiment dengan RL
- **News-aware RL**: State diperkaya dengan LLM embeddings dari financial news

### 19.4 Trading Environment Design

```python
State (observation):
  - Price data: OHLCV normalized
  - Technical indicators: RSI, MACD, ATR, BB
  - Portfolio state: cash, holdings, P&L
  - Market regime: bull/bear/sideways

Action space:
  - Discrete: {Hold, Buy 25%, Buy 50%, Buy 100%, Sell 25%, Sell 50%, Sell 100%}
  - Continuous: [−1, +1] → short/long fraction

Reward function:
  - Simple: Portfolio return
  - Advanced: Sharpe ratio, Sortino ratio
  - Risk-aware: Return − λ × drawdown penalty
```

### 19.5 Challenges & Best Practices

| Challenge | Mitigasi |
|-----------|----------|
| **Sample inefficiency** | Pre-train on synthetic data, transfer learning |
| **Non-stationarity** | Continual learning, regime detection |
| **Overfitting** | Walk-forward validation, CPCV (bab 18.5) |
| **Reward shaping** | Multi-objective: return + risk + transaction cost |
| **Exploration vs exploitation** | Entropy regularization (SAC), ε-greedy decay |
| **Transaction costs** | Include commission + slippage in reward |

### 19.6 Implementasi di Aplikasi

Aplikasi ini mengimplementasikan DRL di `src/drl_trading.py` dengan:
- `StockTradingEnv`: OpenAI Gym-compatible trading environment
- `DRLAgent`: Wrapper untuk DQN/PPO/SAC training dan prediction
- `train_drl_agent()`: Training pipeline dengan walk-forward
- `predict_drl_action()`: Inference untuk trading decision
- Integrasi dengan `predictor.py`: DRL signal sebagai ensemble member

### 19.7 Referensi

- Liu et al. (2022), "FinRL: Deep Reinforcement Learning Framework to Automate Trading"
- Hambly, Xu, Yang (2023), "Recent Advances in Reinforcement Learning in Finance"
- FinRL-DeepSeek (2025) — LLM + PPO untuk news-aware trading
- Stable-Baselines3: DRL library implementation
- OpenAI Gym: Environment interface standard

---

## 20. COMPLEX SYSTEMS APPROACH (CFA INSTITUTE 2025)

> Berdasarkan CFA Institute Research Report (2025), "Reframing Financial Markets as Complex Systems" — paradigma baru melihat pasar bukan sebagai kumpulan aset independen, tetapi sebagai sistem kompleks yang saling terhubung.

### 20.1 Filosofi Complex Systems

**Traditional view**: Pasar = agregasi aset independen → diversifikasi = reduce correlation

**Complex systems view**: Pasar = jaringan interdependen → contagion, feedback loops, emergent behavior

```
Traditional Portfolio Theory:
  → Optimasi weights berdasarkan covariance matrix
  → Asumsi: korelasi stabil

Complex Systems Approach:
  → Map network struktur antar aset
  → Identifikasi contagion paths
  → Model feedback loops dan cascading failures
  → Stress test berdasarkan network topology
```

### 20.2 Network Theory untuk Portfolio

| Konsep | Aplikasi |
|--------|----------|
| **Nodes** | Saham/aset individual |
| **Edges** | Korelasi/dependency antar aset |
| **Centrality** | Aset paling terhubung → systemic risk |
| **Clustering** | Kelompok aset yang bergerak bersama |
| **Contagion paths** | Rute penyebaran shock antar aset |
| **Systemic nodes** | Aset yang bisa trigger cascade |

### 20.3 Agent-Based Models (ABM)

Simulasi heterogen investor behavior:
- **Agent types**: Fundamental, technical, noise, institutional, retail
- **Interaction rules**: Herding, contrarian, momentum
- **Emergent phenomena**: Bubbles, crashes, regime shifts
- **Central bank usage**: Stress test yang lebih realistis dari historical scenarios

### 20.4 System-Level Investing

Institutional investors dengan horizon panjang mulai adopt:
- **System-level risk**: Bukan hanya portfolio risk, tapi risk sistem finansial secara keseluruhan
- **Feedback effects**: Portfolio decisions mempengaruhi market → market mempengaruhi portfolio
- **Universal owners**: Investor besar yang memiliki proportional share dari seluruh market

### 20.5 Implementasi di Aplikasi

Aplikasi ini mengimplementasikan complex systems di `src/complex_systems.py`:
- `build_correlation_network()`: Network graph dari correlation matrix
- `detect_contagion_paths()`: Identifikasi rute penyebaran shock
- `systemic_risk_nodes()`: Aset dengan centrality tertinggi
- `agent_based_simulation()`: Simulasi heterogen investor behavior
- `network_stress_test()`: Stress test berdasarkan network topology
- Integrasi dengan `portfolio.py`: Network-aware portfolio optimization

### 20.6 Referensi

- CFA Institute (2025), "Reframing Financial Markets as Complex Systems"
- Hayman, M. (2025), RPC Research Report
- Greenwood & Sammon (2025), Complexity in financial markets
- NetworkX: Python library untuk network analysis

---

## 21. AI IN ASSET MANAGEMENT (CFA INSTITUTE 2025)

> Berdasarkan CFA Institute Report (November 2025), "AI in Asset Management: Tools, Applications, and Frontiers" — framework komprehensif untuk profesional mengadopsi AI.

### 21.1 AI Applications dalam Asset Management

| Application | Teknologi | Use Case |
|-------------|-----------|----------|
| **Sentiment Analysis** | NLP, LLM | Parse news, earnings calls, social media |
| **Price Prediction** | ML/DL, RL | Forecast return, volatility, regime |
| **Portfolio Optimization** | ML, RL | Dynamic allocation, risk parity |
| **Risk Management** | ML, DL | VaR, stress test, fraud detection |
| **Execution** | RL, Algo | Optimal order execution, slippage minimization |
| **Alternative Data** | DL, NLP | Satellite, web scraping, credit card data |

### 21.2 Deep Learning untuk Trading

| Architecture | Aplikasi |
|--------------|----------|
| **LSTM/GRU** | Time series forecasting, sequence modeling |
| **Transformer** | Long-range dependencies, attention mechanism |
| **CNN** | Pattern recognition di chart images |
| **Autoencoder** | Feature extraction, anomaly detection |
| **GAN** | Synthetic data generation, market simulation |

### 21.3 LLM dalam Asset Management

- **News understanding**: Parse financial news → extract entities, sentiment, implications
- **Earnings call analysis**: Transcribe → summarize → extract forward guidance
- **Research automation**: Generate reports, summarize filings
- **Conversational AI**: Investment assistant, Q&A dengan portfolio data
- **FinRL-DeepSeek (2025)**: LLM embeddings sebagai state features untuk RL agent

### 21.4 Risk Management untuk AI Models

| Risk | Mitigasi |
|------|----------|
| **Model risk** | Backtesting, stress test, explainable AI |
| **Data risk** | Data quality checks, bias detection |
| **Algorithmic risk** | Circuit breakers, human-in-the-loop |
| **Regulatory risk** | Compliance monitoring, audit trail |
| **Operational risk** | Redundancy, monitoring, failover |

### 21.5 Explainable AI (XAI) untuk Finance

- **SHAP values**: Feature importance per prediction
- **LIME**: Local model approximation
- **Attention weights**: Transformer interpretability
- **Counterfactual**: "What if" scenario analysis
- **Regulatory requirement**: EU AI Act, SEC guidance on AI usage

### 21.6 Implementasi di Aplikasi

Aplikasi ini sudah mengimplementasikan beberapa AI in Asset Management concepts:
- **Sentiment analysis**: `src/sentiment.py` (FinBERT) + `src/ai_agent.py`
- **LLM reasoning**: `src/local_llm.py` untuk deep news understanding
- **ML prediction**: `src/predictor.py` dengan ensemble (XGBoost, LightGBM, RF)
- **Fraud detection**: `src/fraud_detection.py` — multi-layer data integrity
- **Explainable AI**: SHAP values di `src/feature_selection.py`
- **Adaptive learning**: `src/adaptive_learning.py` — online model updates
- **DRL trading**: `src/drl_trading.py` — reinforcement learning agent (section 19)

### 21.7 Referensi

- CFA Institute (November 2025), "AI in Asset Management: Tools, Applications, and Frontiers"
- Hambly, Xu, Yang (2023), "Recent Advances in Reinforcement Learning in Finance"
- FinRL-DeepSeek (2025) — LLM + PPO integration
- EU AI Act (2024) — regulatory framework untuk AI

---

## REFERENSI

### Buku
- Benjamin Graham, "The Intelligent Investor" (1949)
- Peter Lynch, "One Up On Wall Street" (1989)
- John Murphy, "Technical Analysis of the Financial Markets" (1999)
- John Murphy, "Intermarket Analysis" (2004)
- Steve Nison, "Japanese Candlestick Charting Techniques" (1991)
- Richard Wyckoff, "Stock Market Technique" (1930s)
- Ralph Elliott, "The Wave Principle" (1938)
- Harry Markowitz, "Portfolio Selection" (1952)
- Daniel Kahneman, "Thinking, Fast and Slow" (2011)
- Nassim Taleb, "The Black Swan" (2007)
- Ernie Chan, "Algorithmic Trading" (2013)
- Marcos Lopez de Prado, "Advances in Financial Machine Learning" (2018)
- Marcos Lopez de Prado, "Machine Learning for Asset Managers" (2020)
- Michael Huddleston (ICT), "Inner Circle Trader" series (2010s-2020s)
- Rubén Villahermosa, "Trading Wyckoff" (2020)

### Akademik
- Fama & French (1993), "Common risk factors in the returns on stocks and bonds"
- Carhart (1997), "On Persistence in Mutual Fund Performance"
- Black & Litterman (1991), "Global Portfolio Optimization"
- Kahneman & Tversky (1979), "Prospect Theory: An Analysis of Decision under Risk"
- Almgren & Chriss (2001), "Optimal Execution of Portfolio Transactions"
- Liu et al. (2022), "FinRL: Deep Reinforcement Learning Framework to Automate Trading"
- Hambly, Xu, Yang (2023), "Recent Advances in Reinforcement Learning in Finance"
- CFA Institute (2025), "Reframing Financial Markets as Complex Systems"
- CFA Institute (2025), "AI in Asset Management: Tools, Applications, and Frontiers"

### Regulasi IDX
- Peraturan BEI No. II-A Kep-00196/BEI/12-2024 (Auto Rejection, efektif April 2025)
- Peraturan BEI No. II-A Kep-00023/BEI/04-2016 (Price Fraction)
- POJK OJK No. 35/POJK.04/2020 (Business Valuation)
- Basel III (CAR requirements untuk bank)

---

> **Catatan**: Dokumen ini adalah pengetahuan dasar yang komprehensif. Setiap topik bisa diperdalam lebih lanjut. Praktik terbaik adalah menggabungkan fundamental, teknikal, kuantitatif, dan behavioral finance dalam satu framework terintegrasi — tidak ada satu metode yang cukup sendirian.
