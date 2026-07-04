---
description: Run daily market analysis pipeline (manual or scheduled)
---

# Daily Analysis

## Steps

1. **Run unified pipeline (full analysis)**
   ```bash
   python -m src.run_analysis --all
   ```
   This runs:
   - Data fetch (Yahoo Finance + FRED)
   - ML prediction (XGBoost + LightGBM ensemble)
   - Sentiment analysis (FinBERT + Fear & Greed)
   - Event-driven analysis (economic calendar + news)
   - Fundamental scoring (Graham/Lynch)
   - Pattern recognition (candlestick, chart, volume)
   - DCF valuation
   - Fraud detection (4-layer: data quality, cross-source, index consistency, news divergence)
   - Investor analysis (asset allocation, correlation matrix, DRIP)
   - Portfolio risk assessment (VaR, CVaR, stress test)
   - Compliance & IDX rules check
   - Model retrain check (drift detection)
   - Paper trading execution (if auto_execute enabled, with trailing stop & partial exit)

2. **Run via Streamlit dashboard**
   ```bash
   streamlit run src/app.py --server.port 8501
   ```
   Navigate to "Pipeline" page and click "Run Analysis"

3. **Run via API**
   ```bash
   uvicorn src.api:app --port 8000
   curl -X POST http://localhost:8000/api/v1/analyze
   ```

4. **Check results**
   - In-app notifications (notification badge in dashboard)
   - Database: `SELECT * FROM prediksi ORDER BY id DESC LIMIT 5`
   - Activity log: `SELECT * FROM log_aktivitas ORDER BY id DESC LIMIT 10`

## Schedule
- GitHub Actions: 14:30 WIB (07:30 UTC) predict, 22:30 WIB (15:30 UTC) verify — weekdays only
- Local scheduler: `python -c "from src.trading_agent import TradingAgent; agent = TradingAgent(auto_execute=True); agent.start_scheduled('14:30')"`
