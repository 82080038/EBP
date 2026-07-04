"""
Comprehensive API endpoint test suite.
Tests every registered endpoint for response shape, error handling, and data validity.
"""
import requests
import json
import time
import sys
from typing import Dict, Any, Tuple, Optional

BASE = "http://localhost:8000/api/v1"
TIMEOUT = 120
results: list[Tuple[str, str, bool, str]] = []


def test_endpoint(
    method: str,
    path: str,
    expected_fields: Optional[list[str]] = None,
    expected_status: int = 200,
    payload: Optional[Dict] = None,
    timeout: int = TIMEOUT,
) -> Tuple[bool, str, Any]:
    """Hit an endpoint and validate response."""
    url = f"{BASE}{path}"
    try:
        if method == "GET":
            r = requests.get(url, timeout=timeout)
        elif method == "POST":
            r = requests.post(url, json=payload, timeout=timeout)
        else:
            return False, f"Unsupported method {method}", None

        if r.status_code != expected_status:
            return False, f"HTTP {r.status_code} (expected {expected_status}): {r.text[:200]}", None

        data = r.json()

        if expected_fields:
            missing = [f for f in expected_fields if f not in data]
            if missing:
                return False, f"Missing fields: {missing}", data

        return True, f"OK ({r.status_code})", data

    except requests.exceptions.Timeout:
        return False, f"Timeout after {timeout}s", None
    except requests.exceptions.ConnectionError as e:
        return False, f"Connection error: {e}", None
    except json.JSONDecodeError:
        return False, f"Invalid JSON response: {r.text[:200]}", None
    except Exception as e:
        return False, f"Exception: {e}", None


def check_no_nan(obj: Any, path: str = "") -> bool:
    """Recursively check for NaN/Infinity in response data."""
    if isinstance(obj, float):
        import math
        if math.isnan(obj) or math.isinf(obj):
            return False
    elif isinstance(obj, dict):
        for k, v in obj.items():
            if not check_no_nan(v, f"{path}.{k}"):
                return False
    elif isinstance(obj, list):
        for i, v in enumerate(obj):
            if not check_no_nan(v, f"{path}[{i}]"):
                return False
    return True


def run_test(name: str, method: str, path: str, **kwargs):
    """Run a single test and record results."""
    ok, msg, data = test_endpoint(method, path, **kwargs)
    nan_ok = True
    if ok and data:
        nan_ok = check_no_nan(data)
        if not nan_ok:
            msg = "NaN/Inf found in response"
            ok = False
    status = "PASS" if ok else "FAIL"
    results.append((name, status, ok, msg))
    print(f"  [{status}] {name:40s} {msg[:80]}")
    return ok, data


def main():
    print("=" * 80)
    print("  COMPREHENSIVE API ENDPOINT TEST SUITE")
    print("=" * 80)

    # === HEALTH ===
    print("\n[1] Health & System")
    run_test("health", "GET", "/health", expected_fields=["status", "version"])
    run_test("system_check", "GET", "/system/check", expected_fields=[])

    # === PREDICTION ===
    print("\n[2] Prediction")
    run_test("predict_IHSG", "GET", "/predict?ticker=IHSG&period=6mo",
             expected_fields=["ticker", "sinyal", "confidence", "current_price", "predicted_price"])
    run_test("predict_BBCA", "GET", "/predict?ticker=BBCA.JK&period=6mo",
             expected_fields=["ticker", "sinyal", "confidence"])

    # === ACCURACY ===
    print("\n[3] Accuracy & Metrics")
    run_test("accuracy", "GET", "/accuracy", expected_fields=["total", "benar", "salah", "directional_accuracy"])

    # === MARKET DATA ===
    print("\n[4] Market Data")
    run_test("ohlcv_IHSG", "GET", "/ohlcv/IHSG?period=6mo", expected_fields=[])
    run_test("ohlcv_BBCA", "GET", "/ohlcv/BBCA.JK?period=6mo", expected_fields=[])
    run_test("indices", "GET", "/indices", expected_fields=[])
    run_test("watchlist", "GET", "/watchlist", expected_fields=[])

    # === ORDER BOOK ===
    print("\n[5] Order Book")
    run_test("orderbook_IHSG", "GET", "/orderbook/IHSG", expected_fields=[])
    run_test("orderbook_BBCA", "GET", "/orderbook/BBCA.JK", expected_fields=[])

    # === BACKTEST ===
    print("\n[6] Backtesting")
    run_test("backtest_IHSG", "GET", "/backtest/IHSG?period=1y",
             expected_fields=["backtest", "simulation"])

    # === RISK ===
    print("\n[7] Risk Management")
    run_test("risk_IHSG", "GET", "/risk/IHSG?period=1y",
             expected_fields=["risk", "var"])
    run_test("risk_BBCA", "GET", "/risk/BBCA.JK?period=1y",
             expected_fields=["risk", "var"])

    # === PORTFOLIO ===
    print("\n[8] Portfolio")
    run_test("portfolio_optimize", "GET", "/portfolio/optimize?period=1y&n_sim=500",
             expected_fields=["max_sharpe_portfolio", "min_vol_portfolio"])

    # === REGIME ===
    print("\n[9] Market Regime")
    run_test("regime_IHSG", "GET", "/regime/IHSG?period=1y", expected_fields=[])

    # === INTERMARKET ===
    print("\n[10] Intermarket")
    run_test("intermarket", "GET", "/intermarket?period=1y",
             expected_fields=["correlation", "summary"])

    # === PATTERNS ===
    print("\n[11] Pattern Analysis")
    run_test("patterns_IHSG", "GET", "/patterns/IHSG/full?period=6mo",
             expected_fields=["candlestick_patterns", "chart_patterns", "market_structure"])

    # === SENTIMENT ===
    print("\n[12] Sentiment")
    run_test("sentiment_full", "GET", "/sentiment/full", expected_fields=[])

    # === BRIEFING ===
    print("\n[13] AI Briefing")
    run_test("briefing_full", "GET", "/briefing/full",
             expected_fields=["date", "signal", "final_recommendation"])

    # === SCORE ===
    print("\n[14] Composite Score")
    run_test("score_IHSG", "GET", "/score/IHSG/full", expected_fields=[])

    # === MODEL DETAILS ===
    print("\n[15] Model Details")
    run_test("model_details_IHSG", "GET", "/model/details/IHSG",
             expected_fields=["predictions", "model_votes"])

    # === MACRO ===
    print("\n[16] Macro Calendar")
    run_test("macro", "GET", "/macro", expected_fields=[])

    # === DATA INVENTORY ===
    print("\n[17] Data Inventory")
    run_test("data_inventory", "GET", "/data/inventory", expected_fields=[])

    # === TRADING AGENT ===
    print("\n[18] Trading Agent")
    run_test("trading_agent", "GET", "/trading-agent/portfolio", expected_fields=[])

    # === OPTIONS ===
    print("\n[19] Options Analysis")
    run_test("options_IHSG", "GET", "/options/IHSG", expected_fields=[])

    # === SUMMARY ===
    print("\n" + "=" * 80)
    passed = sum(1 for _, _, ok, _ in results if ok)
    failed = sum(1 for _, _, ok, _ in results if not ok)
    total = len(results)
    print(f"  RESULTS: {passed}/{total} passed, {failed} failed")
    print("=" * 80)

    if failed > 0:
        print("\n  FAILED TESTS:")
        for name, status, ok, msg in results:
            if not ok:
                print(f"    ✗ {name}: {msg}")

    # Exit code
    sys.exit(0 if failed == 0 else 1)


if __name__ == "__main__":
    main()
