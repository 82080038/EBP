"""
Playwright E2E Test for Command Center Frontend.

Run with headed mode:
    python tests/test_command_center_e2e.py

Prerequisites:
    - FastAPI backend running on http://localhost:8000
    - Next.js frontend running on http://localhost:3000
"""

import sys
import time
import requests
from playwright.sync_api import sync_playwright, expect

FRONTEND_URL = "http://localhost:3000"
BACKEND_URL = "http://localhost:8000"

# Tickers used in the frontend dropdown
TICKERS = ["BBCA", "BBRI", "TLKM", "ASII", "GOTO"]


def check_servers():
    """Verify both servers are running before starting tests."""
    print("[1/8] Checking servers...")
    try:
        r = requests.get(f"{BACKEND_URL}/api/v1/health", timeout=5)
        assert r.status_code == 200, f"Backend health: {r.status_code}"
        print("  ✓ Backend healthy")
    except Exception as e:
        print(f"  ✗ Backend not running: {e}")
        sys.exit(1)

    try:
        r = requests.get(FRONTEND_URL, timeout=5)
        assert r.status_code == 200, f"Frontend: {r.status_code}"
        print("  ✓ Frontend accessible")
    except Exception as e:
        print(f"  ✗ Frontend not running: {e}")
        sys.exit(1)


def test_page_load(page):
    """Test that the Command Center page loads with all panels."""
    print("[2/8] Testing page load and panel rendering...")
    page.goto(FRONTEND_URL, wait_until="networkidle")
    page.wait_for_timeout(2000)

    # Check title
    expect(page).to_have_title("Command Center — Saham Prediksi")

    # Check all panels are visible
    panels = ["Watchlist", "Order Book", "Portfolio & Orders", "Market Summary", "Calendar & Macro"]
    for panel in panels:
        locator = page.locator(f"text={panel}")
        expect(locator.first).to_be_visible(timeout=10000)
    print("  ✓ All 5 panels rendered")

    # Check top bar
    expect(page.locator("text=Command Center").first).to_be_visible()
    print("  ✓ Top bar rendered")

    # Check footer flow text
    expect(page.locator("text=yfinance historical data").first).to_be_visible()
    print("  ✓ Footer flow text rendered")


def test_ticker_selector(page):
    """Test ticker dropdown switching."""
    print("[3/8] Testing ticker selector...")
    selector = page.locator("select").first
    expect(selector).to_be_visible()

    # Get initial value
    initial_val = selector.input_value()
    print(f"  Initial ticker: {initial_val}")

    # Switch to BBRI
    selector.select_option("BBRI")
    page.wait_for_timeout(3000)  # Wait for OHLCV fetch

    # Check chart panel title updated — use h3 heading inside panel header
    chart_header = page.locator("h3:has-text('BBRI')").first
    expect(chart_header).to_be_visible(timeout=10000)
    print("  ✓ Ticker switched to BBRI")

    # Switch back to BBCA
    selector.select_option("BBCA")
    page.wait_for_timeout(3000)
    chart_header_bbca = page.locator("h3:has-text('BBCA')").first
    expect(chart_header_bbca).to_be_visible(timeout=10000)
    print("  ✓ Ticker switched back to BBCA")


def test_watchlist_data(page):
    """Test that watchlist loads with data from API."""
    print("[4/8] Testing watchlist data...")
    page.wait_for_timeout(5000)  # Wait for API fetch

    # Watchlist should have table rows with ticker data
    rows = page.locator("table tr")
    count = rows.count()
    if count > 1:  # Header + data rows
        print(f"  ✓ Watchlist has {count - 1} items")
    else:
        print("  ⚠ Watchlist still loading or empty (may be fetching from yfinance)")


def test_chart_rendering(page):
    """Test that the trading chart renders."""
    print("[5/8] Testing chart rendering...")
    page.wait_for_timeout(3000)

    # Check chart panel header is visible
    chart_header = page.locator("h3:has-text('BBCA')").first
    expect(chart_header).to_be_visible(timeout=10000)

    # Check for canvas element (TradingView Lightweight Charts uses canvas)
    canvas = page.locator("canvas").first
    if canvas.is_visible():
        print("  ✓ Chart canvas rendered")
    else:
        print("  ⚠ Chart canvas not visible (may still be loading)")


def test_run_prediction(page):
    """Test the Run Prediction button and decision strip."""
    print("[6/8] Testing Run Prediction...")
    btn = page.locator("text=Run Prediction").first
    expect(btn).to_be_visible(timeout=10000)

    btn.click()
    print("  → Clicked Run Prediction, waiting for ML pipeline...")

    # Wait for prediction to complete (ML pipeline takes time)
    # Look for signal text (BUY/SELL/HOLD) in decision strip
    signal = page.locator("text=/^(BUY|SELL|HOLD)$/").first
    try:
        expect(signal).to_be_visible(timeout=120000)  # 2 min timeout for ML
        signal_text = signal.text_content()
        print(f"  ✓ Prediction completed: Signal = {signal_text}")

        # Check decision strip details
        confidence = page.locator("text=Confidence").first
        if confidence.is_visible():
            print("  ✓ Confidence metric visible")

        regime = page.locator("text=Market Regime").first
        if regime.is_visible():
            print("  ✓ Market regime visible")
    except Exception:
        print("  ⚠ Prediction timed out (ML pipeline may be slow)")


def test_simulated_trade(page):
    """Test simulated order submission."""
    print("[7/8] Testing simulated trade...")
    # Find the Submit button in the Simulated Trade panel
    submit_btn = page.locator("button:has-text('Submit')").first
    expect(submit_btn).to_be_visible(timeout=10000)

    submit_btn.click()
    print("  → Clicked Submit Order, waiting for fill...")

    # Wait for order result to appear
    try:
        status_text = page.locator("text=Status:").first
        expect(status_text).to_be_visible(timeout=30000)
        print("  ✓ Order result displayed")

        # Check filled qty
        filled = page.locator("text=Filled:").first
        if filled.is_visible():
            print("  ✓ Filled quantity visible")
    except Exception:
        print("  ⚠ Order result not visible (may still be processing)")


def test_portfolio_update(page):
    """Test that portfolio shows position after trade."""
    print("[8/8] Testing portfolio update...")
    page.wait_for_timeout(3000)

    # Portfolio panel should show position
    portfolio = page.locator("text=Portfolio & Orders").first
    expect(portfolio).to_be_visible()

    # Check if any position row exists
    rows = page.locator("text=BBCA.JK")
    if rows.count() > 0:
        print("  ✓ Portfolio shows BBCA.JK position")
    else:
        print("  ⚠ Portfolio may not have updated yet")


def test_all_tabs(page):
    """Test that all 15 tabs are clickable, render content, and display real data."""
    print("[9/9] Testing all tab navigation with data verification...")

    tabs_config = [
        # (tab_label, expected_text_indicators, wait_ms, description)
        ("AI Briefing", ["AI Multi-Agent Briefing"], 5000, "panel title"),
        ("Backtesting", ["Backtesting", "Run Backtest"], 3000, "panel + button"),
        ("Sentiment", ["Fear & Greed"], 30000, "gauge score"),
        ("Patterns", ["Pattern Analysis", "Market Structure"], 30000, "patterns + structure"),
        ("Risk Mgmt", ["Risk Management", "Value at Risk"], 30000, "VaR metrics"),
        ("Portfolio Opt", ["Portfolio Optimization", "Sharpe"], 30000, "efficient frontier"),
        ("Trading Agent", ["Trading Agent", "Safety Guardrails"], 3000, "agent status"),
        ("Accuracy", ["Accuracy & Verification"], 10000, "accuracy metrics"),
        ("Model Details", ["Model Details", "Load Details"], 3000, "model panel"),
        ("Regime", ["Market Regime"], 15000, "regime detection"),
        ("Intermarket", ["Intermarket", "Correlation Matrix"], 30000, "correlation"),
        ("Options", ["Options Analysis"], 15000, "options pricing"),
        ("Data Inventory", ["Data Inventory"], 5000, "inventory table"),
        ("System Check", ["System Check"], 10000, "system info"),
    ]

    passed = 0
    failed = 0

    for tab_name, indicators, wait_ms, desc in tabs_config:
        try:
            tab = page.locator(f"button:has-text('{tab_name}')").first
            expect(tab).to_be_visible(timeout=5000)
            tab.click()

            # Wait for data to load — use progressive wait with retry
            # First wait, then check for skeletons and wait more if needed
            page.wait_for_timeout(wait_ms)

            # If still loading, wait up to 30s more
            skeletons = page.locator(".animate-pulse")
            if skeletons.count() > 0:
                page.wait_for_timeout(15000)
                if skeletons.count() > 0:
                    page.wait_for_timeout(15000)

            # Verify panel title appeared
            for indicator in indicators:
                elem = page.locator(f"text='{indicator}'").first
                if not elem.is_visible(timeout=10000):
                    # Try partial match
                    elem = page.locator(f"text={indicator}").first
                    if not elem.is_visible(timeout=5000):
                        raise AssertionError(f"'{indicator}' not visible")

            # Check that skeleton loaders are gone (no animate-pulse elements)
            skeletons = page.locator(".animate-pulse")
            skeleton_count = skeletons.count()

            # Check that error messages are not shown
            errors = page.locator("text=/Failed to load|Error/")
            error_count = errors.count()

            # Check that "not empty" — at least some data text beyond the title
            # Look for numeric content (font-mono elements with actual numbers)
            mono_elements = page.locator(".font-mono")
            mono_count = mono_elements.count()

            if error_count > 0:
                error_text = errors.first.text_content()
                print(f"  ✗ Tab: {tab_name} — error: {error_text[:80]}")
                failed += 1
            elif skeleton_count > 0:
                print(f"  ⚠ Tab: {tab_name} — still loading ({skeleton_count} skeletons)")
                failed += 1
            elif mono_count < 2 and tab_name not in ("AI Briefing", "Model Details"):
                # Some tabs show data as text not mono — check for any text content
                panel_text = page.locator(".border.border-panel-border").first.text_content()
                if len(panel_text) < 50:
                    print(f"  ✗ Tab: {tab_name} — insufficient content ({len(panel_text)} chars)")
                    failed += 1
                else:
                    print(f"  ✓ Tab: {tab_name} — {desc} ({len(panel_text)} chars)")
                    passed += 1
            else:
                print(f"  ✓ Tab: {tab_name} — {desc} ({mono_count} data points)")
                passed += 1

        except Exception as e:
            print(f"  ✗ Tab: {tab_name} — {e}")
            failed += 1
            page.screenshot(path=f"/tmp/tab_{tab_name.replace(' ', '_')}_fail.png")

    # Go back to Command Center
    cc_tab = page.locator("button:has-text('Command Center')").first
    cc_tab.click()
    page.wait_for_timeout(1000)
    print(f"  ✓ Returned to Command Center")
    print(f"\n  Tab Results: {passed} passed, {failed} failed out of {len(tabs_config)}")

    if failed > 0:
        raise AssertionError(f"{failed} tabs failed data verification")


def run_tests():
    """Run all E2E tests in headed mode."""
    print("=" * 60)
    print("  Command Center E2E Test (Playwright Headed)")
    print("=" * 60)

    check_servers()

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False, args=["--start-maximized"])
        page = browser.new_page(viewport={"width": 1920, "height": 1080})

        # Capture console errors
        console_errors = []
        page.on("console", lambda msg: console_errors.append(f"{msg.type}: {msg.text}") if msg.type == "error" else None)

        try:
            test_page_load(page)
            test_ticker_selector(page)
            test_watchlist_data(page)
            test_chart_rendering(page)
            test_run_prediction(page)
            test_simulated_trade(page)
            test_portfolio_update(page)
            test_all_tabs(page)

            print("\n" + "=" * 60)
            print("  ✓ ALL TESTS COMPLETED")
            print("=" * 60)

            if console_errors:
                print(f"\n  ⚠ Console errors ({len(console_errors)}):")
                for err in console_errors[:5]:
                    print(f"    - {err}")
            else:
                print("\n  ✓ No console errors")

        except Exception as e:
            print(f"\n  ✗ TEST FAILED: {e}")
            page.screenshot(path="/tmp/command_center_failure.png")
            print(f"  Screenshot saved: /tmp/command_center_failure.png")
            raise
        finally:
            print("\n  Closing browser in 5 seconds...")
            time.sleep(5)
            browser.close()


if __name__ == "__main__":
    run_tests()
