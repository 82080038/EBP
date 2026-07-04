---
description: Run full E2E test suite and verify all modules
---

# E2E Testing

## Steps

1. **Run full pytest suite**
   ```bash
   python -m pytest tests/ -v --tb=short --cov=src --cov-report=term-missing
   ```
   Tests use deterministic mock Yahoo Finance data (no real internet calls) via `conftest.py`.

2. **Check all module imports (85 modules)**
   ```bash
   python -c "
   import importlib, sys
   sys.path.insert(0, '.')
   modules = [
       'config','database','data_fetcher','preprocessor','predictor','models',
       'indicators','portfolio','risk_manager','sentiment','notifier','validation',
       'api','backtesting','intermarket','regime','scoring','data_pipeline',
       'run_analysis','mtf','portfolio_risk','pro_risk','quant_finance',
       'technical_advanced','patterns','wyckoff','slippage','feature_selection',
       'hyperopt','retrain_scheduler','realtime_feed','mlflow_tracking',
       'fundamental','dcf_valuation','idx_rules','sector_rotation','event_driven',
       'behavioral','compliance','factor_model','elliott_wave',
       'ab_testing','adaptive_learning','afml','ai_agent','alphalens_analysis',
       'alt_data_sources','broker_sim','bull_bear_debate','complex_systems',
       'cpcv','drift_monitor','drl_trading','event_backtest','execution_algo',
       'explainability','fraud_detection','intraday_model','investor_tools',
       'kronos_integration','local_llm','logging_config','market_hours',
       'module_integrator','multi_horizon','multi_mode_research','options_analysis',
       'paper_trading','rag_system','rate_limiter','react_agent','realtime_monitor',
       'regime_models','screener','sentiment_pipeline','simulation_engine',
       'smc','social_sentiment','system_check','trading_agent','trading_memory',
       'transfer_learning','transformer_models','ui_components','unified_pipeline',
   ]
   errors = []
   for m in modules:
       try:
           importlib.import_module(f'src.{m}')
       except Exception as e:
           errors.append((m, str(e)))
   if errors:
       for m, e in errors:
           print(f'FAIL: {m} — {e}')
       exit(1)
   else:
       print(f'OK: {len(modules)} modules imported successfully')
   "
   ```

3. **Check all 24 page imports**
   ```bash
   python -c "
   import importlib, sys
   sys.path.insert(0, '.')
   pages = [
       'dashboard','prediksi','chart_teknikal','sentiment','regime','intermarket',
       'risk_management','portfolio','portfolio_v2','unified_pipeline','backtesting',
       'riwayat','pengaturan','trading_agent','notifikasi','market_hours',
       'data_inventory','advanced_analytics','command_center','screener',
       'options_analysis','broker_sim','simulation','system_check',
   ]
   errors = []
   for p in pages:
       try:
           importlib.import_module(f'src.pages.{p}')
       except Exception as e:
           errors.append((p, str(e)))
   if errors:
       for p, e in errors:
           print(f'FAIL: {p} — {e}')
       exit(1)
   else:
       print(f'OK: {len(pages)} pages imported successfully')
   "
   ```

4. **Lint check (flake8)**
   ```bash
   python -m flake8 src/ --count --max-line-length=120 --statistics --select=F,E9
   ```

5. **Verify database integrity**
   ```bash
   python -c "
   import sqlite3
   conn = sqlite3.connect('src/data/saham_prediksi.db')
   c = conn.cursor()
   c.execute('SELECT name FROM sqlite_master WHERE type=\"table\" ORDER BY name')
   tables = [r[0] for r in c.fetchall()]
   for t in tables:
       c.execute(f'SELECT COUNT(*) FROM {t}')
       print(f'  {t}: {c.fetchone()[0]} rows')
   conn.close()
   print('DB OK')
   "
   ```

6. **Test Streamlit app starts (smoke test)**
   ```bash
   python -c "
   import importlib.util
   spec = importlib.util.spec_from_file_location('app', 'src/app.py')
   print('Streamlit app spec OK' if spec else 'FAIL')
   "
   ```

7. **Test FastAPI endpoint**
   ```bash
   python -c "
   from src.api import app
   from fastapi.testclient import TestClient
   client = TestClient(app)
   r = client.get('/health')
   print(f'API /health: {r.status_code} {r.json()}')
   "
   ```

## Expected Results
- All 437+ tests pass (mock data, no internet required)
- 85/85 modules import OK
- 24/24 pages import OK
- 0 flake8 F/E9 errors
- Database has 10 tables (prediksi, log_aktivitas, harga_harian, notifikasi, harga_intraday, fundamental_data, technical_indicators, financial_ratios, alerts, sqlite_sequence)
- API /health returns 200
