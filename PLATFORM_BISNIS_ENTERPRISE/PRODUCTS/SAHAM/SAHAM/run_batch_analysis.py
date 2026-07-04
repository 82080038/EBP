#!/usr/bin/env python3
"""
Batch Analysis Runner — Jalankan semua prompt dari BATCH_ANALYSIS_PROMPTS.md secara batch.

Usage:
    python run_batch_analysis.py                    # Run all prompts
    python run_batch_analysis.py --prompt 3         # Run specific prompt only
    python run_batch_analysis.py --dry-run          # Print prompts without executing
    python run_batch_analysis.py --provider ollama  # Use Ollama instead of OpenAI

Requires:
    - OpenAI API key in .env (OPENAI_API_KEY) or
    - Ollama running locally (ollama serve) or
    - DeepSeek API key in .env (DEEPSEEK_API_KEY)

Output:
    - docs/batch_output/prompt_01_architecture.md
    - docs/batch_output/prompt_02_feature_engineering.md
    - ...
    - docs/batch_output/summary_report.md
"""

import argparse
import json
import os
import re
import sys
import time
from datetime import datetime
from pathlib import Path

# Load .env
try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:
    pass

BASE_DIR = Path(__file__).parent
PROMPTS_FILE = BASE_DIR / "docs" / "BATCH_ANALYSIS_PROMPTS.md"
OUTPUT_DIR = BASE_DIR / "docs" / "batch_output"

PROMPT_TITLES = {
    1: "architecture",
    2: "feature_engineering",
    3: "ml_model_enhancement",
    4: "risk_management",
    5: "multi_agent_llm",
    6: "backtesting_validation",
    7: "mlops_drift_detection",
    8: "nextjs_dashboard",
    9: "broker_api_integration",
    10: "sentiment_analysis",
    11: "portfolio_optimization",
    12: "regulatory_compliance",
    13: "performance_optimization",
    14: "testing_qa",
}


def parse_prompts(filepath: Path) -> dict:
    """Extract prompts from the Markdown file."""
    content = filepath.read_text(encoding="utf-8")
    prompts = {}

    # Match: ### Prompt N: Title\n\n```...\n```
    pattern = r"### Prompt (\d+):[^\n]+\n\n```\n(.*?)\n```"
    matches = re.findall(pattern, content, re.DOTALL)

    for num_str, prompt_text in matches:
        num = int(num_str)
        prompts[num] = prompt_text.strip()

    return prompts


def call_openai(prompt: str, model: str = "gpt-4o") -> str:
    """Call OpenAI API."""
    from openai import OpenAI
    client = OpenAI()
    response = client.chat.completions.create(
        model=model,
        messages=[
            {"role": "system", "content": "You are an expert quantitative trading system developer. Provide detailed, production-ready Python code and architecture recommendations."},
            {"role": "user", "content": prompt},
        ],
        temperature=0.7,
        max_tokens=4096,
    )
    return response.choices[0].message.content


def call_deepseek(prompt: str, model: str = "deepseek-chat") -> str:
    """Call DeepSeek API (OpenAI-compatible)."""
    from openai import OpenAI
    api_key = os.getenv("DEEPSEEK_API_KEY", "")
    client = OpenAI(api_key=api_key, base_url="https://api.deepseek.com")
    response = client.chat.completions.create(
        model=model,
        messages=[
            {"role": "system", "content": "You are an expert quantitative trading system developer. Provide detailed, production-ready Python code and architecture recommendations."},
            {"role": "user", "content": prompt},
        ],
        temperature=0.7,
        max_tokens=4096,
    )
    return response.choices[0].message.content


def call_ollama(prompt: str, model: str = "llama3:8b") -> str:
    """Call local Ollama API."""
    import requests
    response = requests.post(
        "http://localhost:11434/api/generate",
        json={
            "model": model,
            "prompt": prompt,
            "system": "You are an expert quantitative trading system developer. Provide detailed, production-ready Python code and architecture recommendations.",
            "stream": False,
            "options": {"temperature": 0.7, "num_predict": 4096},
        },
        timeout=300,
    )
    response.raise_for_status()
    return response.json().get("response", "")


def save_output(prompt_num: int, title: str, prompt: str, response: str, elapsed: float):
    """Save prompt + response to Markdown file."""
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    filename = f"prompt_{prompt_num:02d}_{title}.md"
    filepath = OUTPUT_DIR / filename

    content = f"""# Prompt {prompt_num:02d}: {title.replace('_', ' ').title()}

> Generated: {datetime.now().isoformat()}
> Elapsed: {elapsed:.1f}s

---

## PROMPT

{prompt}

---

## RESPONSE

{response}
"""
    filepath.write_text(content, encoding="utf-8")
    print(f"  [SAVED] {filepath}")


def run_batch(prompts: dict, provider: str, only: int = None):
    """Run prompts in batch."""
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    results = []
    items = sorted(prompts.items())

    if only:
        items = [(n, p) for n, p in items if n == only]

    print(f"\n{'='*60}")
    print(f"  BATCH ANALYSIS RUNNER")
    print(f"  Provider: {provider}")
    print(f"  Prompts: {len(items)}")
    print(f"  Output: {OUTPUT_DIR}")
    print(f"{'='*60}\n")

    for num, prompt in items:
        title = PROMPT_TITLES.get(num, f"prompt_{num}")
        print(f"[{num:02d}/{len(items)}] {title}...")

        try:
            start = time.time()

            if provider == "openai":
                response = call_openai(prompt)
            elif provider == "deepseek":
                response = call_deepseek(prompt)
            elif provider == "ollama":
                response = call_ollama(prompt)
            else:
                raise ValueError(f"Unknown provider: {provider}")

            elapsed = time.time() - start
            save_output(num, title, prompt, response, elapsed)

            results.append({
                "prompt": num,
                "title": title,
                "status": "success",
                "elapsed": elapsed,
                "response_length": len(response),
            })
            print(f"  [OK] {elapsed:.1f}s — {len(response)} chars")

        except Exception as e:
            elapsed = time.time() - start
            results.append({
                "prompt": num,
                "title": title,
                "status": "error",
                "error": str(e),
                "elapsed": elapsed,
            })
            print(f"  [ERROR] {e}")

        # Rate limit: 2s between calls
        if num < len(items):
            time.sleep(2)

    # Generate summary
    generate_summary(results, provider)
    return results


def generate_summary(results: list, provider: str):
    """Generate summary report."""
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    filepath = OUTPUT_DIR / "summary_report.md"

    total = len(results)
    success = sum(1 for r in results if r["status"] == "success")
    errors = sum(1 for r in results if r["status"] == "error")
    total_time = sum(r.get("elapsed", 0) for r in results)

    content = f"""# Batch Analysis Summary Report

> Generated: {datetime.now().isoformat()}
> Provider: {provider}

---

## Overview

| Metric | Value |
|--------|-------|
| Total Prompts | {total} |
| Successful | {success} |
| Failed | {errors} |
| Total Time | {total_time:.1f}s |
| Avg Time/Prompt | {total_time/max(total,1):.1f}s |

## Results

| # | Title | Status | Time | Response Size |
|---|-------|--------|------|---------------|
"""
    for r in results:
        status_icon = "✅" if r["status"] == "success" else "❌"
        size = r.get("response_length", 0)
        content += f"| {r['prompt']:02d} | {r['title']} | {status_icon} | {r.get('elapsed',0):.1f}s | {size} chars |\n"

    content += "\n## Files\n\n"
    for r in results:
        if r["status"] == "success":
            content += f"- `prompt_{r['prompt']:02d}_{r['title']}.md`\n"

    filepath.write_text(content, encoding="utf-8")
    print(f"\n[SUMMARY] {filepath}")


def main():
    parser = argparse.ArgumentParser(description="Batch Analysis Runner")
    parser.add_argument("--prompt", type=int, help="Run specific prompt number only")
    parser.add_argument("--dry-run", action="store_true", help="Print prompts without executing")
    parser.add_argument("--provider", choices=["openai", "deepseek", "ollama"], default="openai", help="LLM provider")
    parser.add_argument("--list", action="store_true", help="List all available prompts")
    args = parser.parse_args()

    if not PROMPTS_FILE.exists():
        print(f"[ERROR] Prompts file not found: {PROMPTS_FILE}")
        sys.exit(1)

    prompts = parse_prompts(PROMPTS_FILE)

    if not prompts:
        print("[ERROR] No prompts found in file")
        sys.exit(1)

    if args.list:
        print(f"\nFound {len(prompts)} prompts:\n")
        for num in sorted(prompts):
            title = PROMPT_TITLES.get(num, f"prompt_{num}")
            preview = prompts[num][:80].replace("\n", " ")
            print(f"  [{num:02d}] {title}: {preview}...")
        return

    if args.dry_run:
        print(f"\n{'='*60}")
        print(f"  DRY RUN — {len(prompts)} prompts")
        print(f"{'='*60}\n")
        for num in sorted(prompts):
            title = PROMPT_TITLES.get(num, f"prompt_{num}")
            print(f"[{num:02d}] {title}")
            print(f"     {prompts[num][:200]}...")
            print()
        return

    run_batch(prompts, args.provider, only=args.prompt)


if __name__ == "__main__":
    main()
