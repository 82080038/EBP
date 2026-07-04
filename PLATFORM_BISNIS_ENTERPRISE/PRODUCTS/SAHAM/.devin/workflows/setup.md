---
description: Setup development environment for new contributor
---

# Development Setup

## Prerequisites
- Python 3.12+
- pip
- Git
- Node.js 20/22 LTS (Node 24 is **not supported** by Next.js 14; frontend has been upgraded to Next.js 15.1.0 to support newer Node versions)
- Internet connection (for Yahoo Finance data)

## Steps

1. **Clone repository**
   ```bash
   git clone -b laptop https://github.com/82080038/saham.git
   cd saham
   ```

2. **Create virtual environment (recommended)**
   ```bash
   python -m venv .venv
   source .venv/bin/activate  # Linux/Mac
   # .venv\Scripts\activate   # Windows
   ```

3. **Install dependencies**

   Pilih wheel PyTorch sesuai hardware:

   **CPU (komputer kantor/Windows tanpa GPU):**
   ```bash
   pip install -r requirements.txt -r requirements-dev.txt -r requirements-torch-cpu.txt
   ```

   **CUDA/GPU (komputer rumah dengan NVIDIA):**
   ```bash
   pip install -r requirements.txt -r requirements-dev.txt -r requirements-torch-cuda.txt
   ```

   **Important**: numpy must stay <2.0 for compatibility with scipy/shap.
   If pip overrides numpy, run: `pip install "numpy>=1.24,<2.0" "scipy>=1.11,<1.13"`

4. **Install frontend dependencies**
   ```bash
   cd frontend
   npm install
   npm run build
   ```
   For development, use `npm run dev` instead of `npm run build`.

5. **Setup environment variables**
   ```bash
   cp .env.example .env  # Linux/Mac
   # copy .env.example .env  # Windows
   ```
   Edit `.env` with your API keys (optional for basic features).

6. **Verify installation**
   ```bash
   python -c "from src.config import TARGET_TICKER; print(f'Target: {TARGET_TICKER}')"
   python -m pytest tests/ -v --tb=short
   ```

7. **Run the application**
   ```bash
   # Streamlit dashboard
   streamlit run src/app.py --server.port 8501

   # Or CLI analysis
   python -m src.run_analysis --all

   # Or FastAPI
   uvicorn src.api:app --reload --port 8000
   ```

7. **Verify database**
   ```bash
   python -c "
   from src.database import init_db
   init_db()
   print('Database initialized OK')
   "
   ```

8. **Optional: Install Ollama for local LLM (batch analysis)**
   ```bash
   curl -fsSL https://ollama.com/install.sh | sh
   ollama pull llama3:8b
   python3 run_batch_analysis.py --provider ollama
   ```

9. **Optional: GPU setup (NVIDIA)**
   - Install NVIDIA driver + CUDA toolkit
   - PyTorch: `pip install -r requirements.txt -r requirements-dev.txt -r requirements-torch-cuda.txt`
   - LightGBM GPU: auto-detected at import (device='gpu')
   - XGBoost GPU: NOT supported on Pascal (SM 6.1), needs SM 7.0+
   - numpy must stay <2.0 for scipy/shap compatibility

## Cross-OS Notes (Linux ↔ Windows)
- **Line endings**: repo uses `* text=auto` in `.gitattributes` so line endings stay LF in the repo and adapt to OS on checkout.
- **Paths**: all code uses `os.path.join()` or `pathlib`; avoid hard-coded Windows paths like `C:\` in new code.
- **Database**: SQLite path is resolved relative to `src/config.py` via `DB_PATH`; the DB file is in `.gitignore`, so it is not shared between machines.
- **Virtual env**: use `.venv` on both OS; Python version must be 3.12+.
- **PyTorch**: use `requirements-torch-cpu.txt` on CPU machines and `requirements-torch-cuda.txt` on NVIDIA machines. Never commit the installed torch wheel; it is selected per environment.
- **Docker**: `docker-compose.yml` gives a Linux environment identical across machines; useful for eliminating OS-specific differences.

## Project Structure
- `src/` — 85+ Python modules (analysis, trading, data, UI, fraud detection, investor tools, intraday, execution, broker simulation)
- `src/data_validation.py` — OHLCV/ticker validation layer (production safety)
- `src/model_registry.py` — lightweight model versioning with metadata, hash, timestamp
- `src/async_data_fetcher.py` — parallel fetch for 100+ tickers
- `src/cache.py` — Redis caching with in-memory fallback
- `src/lazy_imports.py` — deferred import utilities for faster module load
- `src/db_optimizer.py` — SQLite index optimization + PRAGMA tuning
- `src/batch_predictor.py` — multiprocessing multi-ticker prediction
- `src/polars_processor.py` — Polars-accelerated data processing
- `src/pages/` — 24 Streamlit UI pages
- `src/data/` — SQLite DB, JSON cache, models
- `src/models/` — Trained ML models (pkl), best params, retrain state
- `tests/` — 18 test files (437+ tests, mock data)
- `docs/` — Architecture, roadmap, knowledge base, acuan proyek
- `.github/workflows/` — CI/CD + daily analysis pipelines
- `.devin/workflows/` — Devin AI workflows (setup, test, deploy, daily-analysis)
- `requirements.txt`, `requirements-dev.txt`, `requirements-torch-cpu.txt`, `requirements-torch-cuda.txt` — dependencies
- `.gitattributes` — cross-OS line ending normalization

## Key Entry Points
- `src/app.py` — Streamlit dashboard dispatcher (24 pages)
- `src/predictor.py` — daily prediction pipeline (ML ensemble + advanced analytics)
- `src/unified_pipeline.py` — full analysis pipeline (all modules)
- `src/simulation_engine.py` — walk-forward trading simulation (long/short, ATR-based SL/TP, regime-aware)
- `src/broker_sim.py` — realistic broker simulator with short selling
- `src/api.py` — FastAPI REST API
- `src/run_analysis.py` — CLI entry point

## Database Schema (10 tables)
- `prediksi` — prediction records with actual verification
- `harga_harian` — daily OHLCV price data
- `harga_intraday` — intraday price data (1m, 5m, 15m, 1h)
- `fundamental_data` — fundamental snapshots per ticker
- `technical_indicators` — technical indicator snapshots
- `financial_ratios` — financial ratio history
- `alerts` — price/technical alerts
- `notifikasi` — in-app notifications
- `log_aktivitas` — activity log
- `sqlite_sequence` — autoincrement counters
