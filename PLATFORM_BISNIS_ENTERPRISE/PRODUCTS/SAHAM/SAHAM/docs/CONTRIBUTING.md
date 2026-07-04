# 🤝 Panduan Kontribusi

## Aturan Umum

1. **Branch**: Semua development dilakukan di branch `laptop`
2. **Commit message**: Gunakan bahasa Indonesia atau Inggris, singkat dan jelas
3. **Code style**: Ikuti style code yang ada (PEP 8 untuk Python)
4. **Testing**: Test perubahan sebelum commit
5. **No secrets**: Jangan commit `.env`, API keys, atau password

## Code Style

- Gunakan **4 spaces** untuk indentasi
- Maximum line length: **120 karakter**
- Import order: standard library → third party → local
- Gunakan type hints jika memungkinkan
- Dokumentasi function dengan docstring ringkas

## Contoh Commit Message

```
Tambah indikator CCI ke preprocessor
Fix bug kolom Target_NextReturn di predictor
Update README dengan panduan multi-komputer
Refactor risk_manager untuk support multiple confidence level
```

## Pull Request Process

1. Pull latest changes: `git pull origin laptop`
2. Buat perubahan dan test
3. Commit dengan message yang jelas
4. Push: `git push origin laptop`
5. Buat Pull Request di GitHub (laptop → main) jika ready untuk production

## Testing

```bash
# Run semua 481+ tests
python -m pytest tests/ -q --tb=short

# Run test file spesifik
python -m pytest tests/test_mtf_portfolio.py -v

# Run dengan coverage
python -m pytest tests/ --cov=src --cov-report=term-missing

# Test import semua modul
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
from src.portfolio import optimize_portfolio, compare_portfolio_methods
from src.mtf import run_mtf_analysis
from src.wyckoff import detect_wyckoff_phase
from src.elliott_wave import detect_elliott_wave
from src.event_driven import run_event_driven_analysis
from src.ai_agent import run_daily_briefing
from src.scoring import calc_composite_ai_score
from src.regime import detect_market_regime
from src.pro_risk import RiskGovernance
from src.slippage import calc_execution_cost
from src.quant_finance import run_realistic_backtest, generate_tear_sheet
from src.fraud_detection import FraudDetector
from src.investor_tools import AssetAllocationModel, CorrelationAnalyzer
from src.intraday_model import IntradayModel
from src.execution_algo import VWAPExecution, TWAPExecution
from src.broker_sim import BrokerSimulator
from src.simulation_engine import MarketSimulation
print('All 85 modules OK!')
"

# Test prediksi end-to-end
python -c "
from src.data_fetcher import fetch_all_market_data
from src.models import HybridEnsemble
from src.predictor import run_prediction
md = fetch_all_market_data(period='1y')
result = run_prediction(market_data=md, fred_data={}, ensemble=HybridEnsemble(use_lstm=False))
print(f'Signal: {result[\"sinyal\"]}, Confidence: {result[\"confidence\"]:.2%}')
print(f'MTF: {result.get(\"mtf_confluence_signal\", \"N/A\")} ({result.get(\"mtf_confluence_score\", 0):.0f})')
print(f'AI Score: {result[\"ai_score\"]}/10')
print(f'Wyckoff: {result[\"wyckoff_phase\"]}')
print(f'Regime: {result[\"market_regime\"]}')
"

# Test portfolio optimization (5 methods)
python -c "
import pandas as pd, numpy as np
from src.portfolio import compare_portfolio_methods
np.random.seed(42)
returns = pd.DataFrame({
    'BBCA': np.random.normal(0.0003, 0.012, 252),
    'BBRI': np.random.normal(0.0004, 0.014, 252),
    'TLKM': np.random.normal(0.0002, 0.010, 252),
    'ASII': np.random.normal(0.0006, 0.018, 252),
})
results = compare_portfolio_methods(returns)
print(results['comparison'].to_string(index=False))
"
```

## Modul yang Bisa Dikerjakan

### Layer Data
| Modul | Status | TODO |
|-------|--------|------|
| `config.py` | ✅ Stable | Tambah ticker baru jika perlu |
| `database.py` | ✅ Stable | Migrasi ke PostgreSQL/TimescaleDB |
| `data_fetcher.py` | ✅ Stable | Tambah data source (Alpha Vantage, IDX API) |
| `rate_limiter.py` | ✅ Stable | — |
| `realtime_feed.py` | ✅ Stable | WebSocket real-time streaming |
| `data_pipeline.py` | ✅ Stable | Feature store (Feast) |
| `fundamental.py` | ✅ Stable | Lebih banyak fundamental data |
| `dcf_valuation.py` | ✅ Stable | Multi-stage DCF, sensitivity analysis |

### Layer Feature
| Modul | Status | TODO |
|-------|--------|------|
| `preprocessor.py` | ✅ Stable | Feature selection otomatis |
| `feature_selection.py` | ✅ Stable | Genetic algorithm feature selection |
| `indicators.py` | ✅ Stable | Tambah indikator custom |
| `technical_advanced.py` | ✅ Stable | Auto trendline detection improvement |
| `patterns.py` | ✅ Stable | Lebih banyak chart patterns |

### Layer Model
| Modul | Status | TODO |
|-------|--------|------|
| `models.py` | ✅ Stable | PatchTST, TFT transformer models |
| `hyperopt.py` | ✅ Stable | Multi-objective optimization |
| `validation.py` | ✅ Stable | Combinatorial purged CV |
| `retrain_scheduler.py` | ✅ Stable | Online learning, incremental retrain |
| `mlflow_tracking.py` | ✅ Stable | Model registry, A/B testing |

### Layer Analysis
| Modul | Status | TODO |
|-------|--------|------|
| `mtf.py` | ✅ Stable | Real intraday data, more timeframes |
| `scoring.py` | ✅ Stable | More skill packs, adaptive weights |
| `wyckoff.py` | ✅ Stable | Volume spread analysis integration |
| `elliott_wave.py` | ✅ Stable | Auto degree detection |
| `behavioral.py` | ✅ Stable | Social media sentiment integration |
| `sector_rotation.py` | ✅ Stable | Fama-French factor model for IDX |
| `factor_model.py` | ✅ Stable | Multi-factor model (Fama-French 5) |
| `event_driven.py` | ✅ Stable | Foreign flow BEI integration |
| `ai_agent.py` | ✅ Stable | RAG, semantic caching, more agents |
| `sentiment.py` | ✅ Stable | News API, social media |
| `sentiment_pipeline.py` | ✅ Stable | Twitter, Stockbit sentiment |
| `regime.py` | ✅ Stable | Hidden Markov Model regime detection |
| `intermarket.py` | ✅ Stable | Granger causality, cointegration |

### Layer Risk
| Modul | Status | TODO |
|-------|--------|------|
| `risk_manager.py` | ✅ Stable | Monte Carlo VaR, stress testing |
| `pro_risk.py` | ✅ Stable | Real-time kill switch monitoring |
| `portfolio.py` | ✅ Stable | Robust optimization, factor tilts |
| `portfolio_risk.py` | ✅ Stable | Dynamic correlation, DCC-GARCH |
| `slippage.py` | ✅ Stable | Almgren-Chriss optimal execution |
| `quant_finance.py` | ✅ Stable | Event-driven backtesting engine |
| `idx_rules.py` | ✅ Stable | Short selling rules, margin rules |
| `compliance.py` | ✅ Stable | Automated compliance reporting |

### Layer Simulation *(BARU)*
| Modul | Status | TODO |
|-------|--------|------|
| `broker_sim.py` | ✅ Active | Dukungan short selling, partial fill, latency |
| `simulation_engine.py` | ✅ Active | Regime-aware adaptive strategy, ATR-based SL/TP, short selling |
| `src/pages/simulation.py` | ✅ Active | UI untuk menjalankan simulasi dari browser |
| `src/pages/broker_sim.py` | ✅ Active | UI untuk simulasi broker |

### Layer Presentation
| Modul | Status | TODO |
|-------|--------|------|
| `predictor.py` | ✅ Stable | Multi-target prediction |
| `intraday_model.py` | ✅ Stable | Tick data integration, more intervals |
| `fraud_detection.py` | ✅ Stable | Real-time alerting, more data sources |
| `anti_manipulation.py` | ✅ Stable | More metrics, real-time integration |
| `execution_algo.py` | ✅ Stable | Implementation Shortfall algo, POV |
| `investor_tools.py` | ✅ Stable | Tax-loss harvesting, rebalancing automation |
| `app.py` | ✅ Stable | React/Next.js frontend, mobile app |
| `api.py` | ✅ Stable | WebSocket endpoints, authentication |
| `run_analysis.py` | ✅ Stable | Logging improvement, cron flexibility |
| `notifier.py` | ✅ Stable | Discord, WhatsApp integration |
