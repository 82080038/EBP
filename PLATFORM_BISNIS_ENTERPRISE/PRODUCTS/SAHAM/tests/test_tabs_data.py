"""
Playwright Tab Data Verification Test.

Tests that all 14 new tabs load and display real data (not NaN, not empty, not error).
Skips the slow ML prediction step. Run with:

    python tests/test_tabs_data.py
"""
import sys
import time
import re
import requests
from playwright.sync_api import sync_playwright, expect

FRONTEND_URL = "http://localhost:3000"
BACKEND_URL = "http://localhost:8000"


def check_servers():
    print("[1/3] Checking servers...")
    try:
        r = requests.get(f"{BACKEND_URL}/api/v1/health", timeout=5)
        assert r.status_code == 200
        print("  ✓ Backend healthy")
    except Exception as e:
        print(f"  ✗ Backend not running: {e}")
        sys.exit(1)
    try:
        r = requests.get(FRONTEND_URL, timeout=5)
        assert r.status_code == 200
        print("  ✓ Frontend accessible")
    except Exception as e:
        print(f"  ✗ Frontend not running: {e}")
        sys.exit(1)


def test_tab_data(page, tab_name, indicators, wait_ms, desc):
    """Click a tab, wait for data, verify content is real (not NaN/empty/error)."""
    tab = page.locator(f"button:has-text('{tab_name}')").first
    expect(tab).to_be_visible(timeout=5000)
    tab.click()

    # Wait for data to load
    page.wait_for_timeout(wait_ms)

    # Verify panel title appeared
    for indicator in indicators:
        elem = page.locator(f"text={indicator}").first
        if not elem.is_visible(timeout=5000):
            raise AssertionError(f"'{indicator}' not visible in {tab_name}")

    # Check for error messages
    error_elems = page.locator("text=/Failed to load|Error/")
    error_count = error_elems.count()

    # Check for skeleton loaders still present
    skeletons = page.locator(".animate-pulse")
    skeleton_count = skeletons.count()

    # Check for data content — font-mono elements with numbers
    mono_elements = page.locator(".font-mono")
    mono_count = mono_elements.count()

    # Get panel text content
    panel = page.locator(".border.border-panel-border").first
    panel_text = panel.text_content() or ""
    panel_len = len(panel_text.strip())

    # Check for NaN in rendered text
    has_nan = "NaN" in panel_text
    has_null_only = panel_text.strip() in ("", "null")

    if error_count > 0:
        error_text = error_elems.first.text_content() or ""
        return False, f"error: {error_text[:80]}"
    elif skeleton_count > 0:
        return False, f"still loading ({skeleton_count} skeletons)"
    elif has_nan:
        return False, f"NaN in rendered text"
    elif has_null_only:
        return False, f"empty/null panel"
    elif panel_len < 50:
        return False, f"insufficient content ({panel_len} chars)"
    elif mono_count < 2 and tab_name not in ("AI Briefing", "Model Details", "Trading Agent"):
        return False, f"only {mono_count} data points"
    else:
        return True, f"{desc} ({mono_count} data pts, {panel_len} chars)"


def run_tests():
    print("=" * 60)
    print("  Tab Data Verification Test (Playwright Headed)")
    print("=" * 60)

    check_servers()

    tabs_config = [
        # (tab_label, expected_text_indicators, wait_ms, description)
        ("AI Briefing", ["AI Multi-Agent Briefing"], 5000, "briefing panel"),
        ("Backtesting", ["Backtesting"], 3000, "backtest panel"),
        ("Sentiment", ["Fear & Greed"], 20000, "sentiment gauge"),
        ("Patterns", ["Pattern Analysis"], 20000, "patterns + structure"),
        ("Risk Mgmt", ["Risk Management"], 20000, "VaR metrics"),
        ("Portfolio Opt", ["Portfolio Optimization"], 40000, "efficient frontier"),
        ("Trading Agent", ["Trading Agent"], 3000, "agent status"),
        ("Accuracy", ["Accuracy"], 10000, "accuracy metrics"),
        ("Model Details", ["Model Details"], 3000, "model panel"),
        ("Regime", ["Market Regime"], 20000, "regime detection"),
        ("Intermarket", ["Intermarket"], 40000, "correlation matrix"),
        ("Options", ["Options Analysis"], 20000, "options pricing"),
        ("Data Inventory", ["Data Inventory"], 10000, "inventory table"),
        ("System Check", ["System Check"], 15000, "system info"),
    ]

    print(f"\n[2/3] Testing {len(tabs_config)} tabs with data verification...\n")

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False, args=["--start-maximized"])
        page = browser.new_page(viewport={"width": 1920, "height": 1080})

        console_errors = []
        page.on("console", lambda msg: console_errors.append(f"{msg.type}: {msg.text}") if msg.type == "error" else None)

        page.goto(FRONTEND_URL, wait_until="networkidle")
        page.wait_for_timeout(2000)

        passed = 0
        failed = 0
        failures = []

        for tab_name, indicators, wait_ms, desc in tabs_config:
            try:
                ok, detail = test_tab_data(page, tab_name, indicators, wait_ms, desc)
                if ok:
                    print(f"  ✓ {tab_name:20s} — {detail}")
                    passed += 1
                else:
                    print(f"  ✗ {tab_name:20s} — {detail}")
                    failed += 1
                    failures.append((tab_name, detail))
                    page.screenshot(path=f"/tmp/tab_{tab_name.replace(' ', '_')}_fail.png")
            except Exception as e:
                print(f"  ✗ {tab_name:20s} — {e}")
                failed += 1
                failures.append((tab_name, str(e)))
                page.screenshot(path=f"/tmp/tab_{tab_name.replace(' ', '_')}_fail.png")

        # Return to Command Center
        cc_tab = page.locator("button:has-text('Command Center')").first
        cc_tab.click()
        page.wait_for_timeout(1000)

        print(f"\n[3/3] Results")
        print(f"  Passed: {passed}/{len(tabs_config)}")
        print(f"  Failed: {failed}/{len(tabs_config)}")

        if console_errors:
            # Filter out browser extension errors
            real_errors = [e for e in console_errors if "Could not establish connection" not in e]
            if real_errors:
                print(f"\n  ⚠ Console errors ({len(real_errors)}):")
                for err in real_errors[:5]:
                    print(f"    - {err[:100]}")
            else:
                print("\n  ✓ No app console errors (extension errors ignored)")
        else:
            print("  ✓ No console errors")

        if failures:
            print(f"\n  Failures:")
            for name, detail in failures:
                print(f"    - {name}: {detail}")

        browser.close()

        if failed > 0:
            print(f"\n  ✗ {failed} TABS FAILED")
            sys.exit(1)
        else:
            print(f"\n  ✓ ALL TABS PASSED DATA VERIFICATION")


if __name__ == "__main__":
    run_tests()
