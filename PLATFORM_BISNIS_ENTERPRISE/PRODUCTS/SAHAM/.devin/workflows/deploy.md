---
description: Deploy application — commit, push, and verify GitHub Actions
---

# Deploy

## Steps

1. **Check git status**
   ```bash
   git status --short
   ```

2. **Stage all changes**
   ```bash
   git add -A
   ```

3. **Commit with descriptive message**
   ```bash
   git commit -m "feat: description of changes"
   ```

4. **Push to remote**
   ```bash
   git push origin laptop
   ```

5. **Verify GitHub Actions CI**
   - Check https://github.com/82080038/saham/actions
   - CI workflow runs pytest on push
   - Daily analysis runs on schedule (14:30 WIB predict, 22:30 WIB verify)

6. **Verify deployment**
   - Streamlit Cloud (if configured): check app is live
   - FastAPI: `uvicorn src.api:app --port 8000` then `curl http://localhost:8000/health`

## Branch Strategy
- `laptop` — main development branch (current, laptop workstation)
- `main` — stable release branch
- `kantor` — office workstation branch
- All commits go to `laptop` first, merge to `main` for production

## Batch Analysis
- `docs/BATCH_ANALYSIS_PROMPTS.md` — 14 development prompts with status markers
- `run_batch_analysis.py` — batch runner (supports OpenAI, DeepSeek, Ollama)
- `docs/batch_output/` — LLM output per prompt (local only, not committed)
- Run: `python3 run_batch_analysis.py --provider ollama` (local, gratis)

## CI/CD Details
- CI runs on push to `main`, `laptop`, `dev` branches
- Tests: pytest with coverage on Python 3.11 + 3.12
- Lint: flake8 (F/E9 selectors, max-line-length=120)
- Build: Docker image on `main` branch only
- Daily analysis: GitHub Actions at 14:30 WIB (predict) and 22:30 WIB (verify), weekdays only

## Files to NEVER commit (local-only)
- `.env` (contains API keys) — in .gitignore
- `*.pyc` / `__pycache__/` — in .gitignore
- `mlruns/` (MLflow artifacts, regenerated) — in .gitignore
- `src/data/simulation_results.json` (regenerated) — in .gitignore
- `src/data/saham_prediksi.db` (SQLite DB, local data) — in .gitignore
- `src/data/realtime_cache.json` (real-time cache, regenerated) — in .gitignore
- `src/data/trade_journal.json` (local trading journal) — in .gitignore
- `src/data/logs/` (local log files) — in .gitignore
- `src/models/*.pkl` (trained model weights, regenerated) — in .gitignore
- `src/models/retrain_state.json` (retrain state, local) — in .gitignore
- `src/models/best_params.json` (Optuna params, regenerated) — in .gitignore
- `docs/batch_output/` (LLM batch output, local reference) — in .gitignore
- `.coverage` (test coverage data) — in .gitignore
- `kronos_cache/` (Kronos model cache) — in .gitignore

## Files TO commit (shared across developers)
- `docs/BATCH_ANALYSIS_PROMPTS.md` — analysis & prompt collection
- `run_batch_analysis.py` — batch runner script
- `docs/*.md` — all documentation
- `src/**/*.py` — all source code
- `tests/**/*.py` — all test code
- `frontend/` — Next.js frontend code
- `.devin/` — Devin workflows
- `.github/` — CI/CD workflows
- `requirements.txt`, `requirements-dev.txt`, `requirements-torch-cpu.txt`, `requirements-torch-cuda.txt` — dependencies
- `Dockerfile`, `docker-compose.yml` — container config
- `.env.example` — env template (NO real keys)
- `conftest.py`, `pytest.ini` — test config
- `.gitattributes` — cross-OS line ending normalization
