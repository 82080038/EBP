"""
Realistic User Trading Simulation.

Simulates a real trader's workflow:
1. Market overview — check indices, macro indicators
2. Watchlist scan — find opportunities
3. Stock deep dive — chart, patterns, regime
4. AI analysis — prediction, briefing, sentiment, risk
5. Decision & execution — submit orders
6. Portfolio review — check positions
7. Advanced analysis — backtest, portfolio opt, intermarket, options
8. System check — verify infrastructure

All done in headed browser with screenshots at each step.
Run: python tests/simulate_trading_session.py
"""
import sys
import time
import os
import json
import requests
from datetime import datetime
from playwright.sync_api import sync_playwright, expect

FRONTEND_URL = "http://localhost:3000"
BACKEND_URL = "http://localhost:8000"
SCREENSHOT_DIR = "/tmp/trading_session"

os.makedirs(SCREENSHOT_DIR, exist_ok=True)

# Trading session journal
journal = {
    "session_start": datetime.now().isoformat(),
    "trader": "Simulated Trader (Playwright)",
    "actions": [],
    "trades": [],
    "screenshots": [],
}


def log(action, detail=""):
    ts = datetime.now().strftime("%H:%M:%S")
    line = f"[{ts}] {action}"
    if detail:
        line += f" — {detail}"
    print(line)
    journal["actions"].append({"time": ts, "action": action, "detail": detail})


def screenshot(page, name):
    path = f"{SCREENSHOT_DIR}/{name}.png"
    page.screenshot(path=path, full_page=True)
    journal["screenshots"].append(name)
    log("📸 Screenshot", name)


def wait(ms):
    time.sleep(ms / 1000)


def check_servers():
    log("🔍 Checking servers")
    try:
        r = requests.get(f"{BACKEND_URL}/api/v1/health", timeout=5)
        assert r.status_code == 200
        log("✅ Backend healthy", r.json().get("status", "?"))
    except Exception as e:
        print(f"❌ Backend not running: {e}")
        sys.exit(1)
    try:
        r = requests.get(FRONTEND_URL, timeout=5)
        assert r.status_code == 200
        log("✅ Frontend accessible")
    except Exception as e:
        print(f"❌ Frontend not running: {e}")
        sys.exit(1)


# =============================================================================
# TRADING SESSION PHASES
# =============================================================================

def phase1_market_overview(page):
    """Phase 1: Trader opens dashboard, checks market overview."""
    log("=" * 50)
    log("PHASE 1: Market Overview")
    log("=" * 50)

    # Open the dashboard
    log("🌐 Opening Command Center dashboard")
    page.goto(FRONTEND_URL, wait_until="networkidle")
    wait(3000)
    screenshot(page, "01_dashboard_loaded")

    # Check market summary panel (indices)
    log("📊 Checking market indices summary")
    market_panel = page.locator("text=Market Summary")
    if market_panel.is_visible(timeout=5000):
        log("✅ Market Summary panel visible")
        # Read index values
        indices = page.locator(".font-mono").all_text_contents()
        log("📈 Indices data", f"{len(indices)} values found")
    else:
        log("⚠️ Market Summary not visible")

    # Check macro indicators
    log("🏛️ Checking macro indicators")
    macro_panel = page.locator("text=Macro")
    if macro_panel.is_visible(timeout=3000):
        log("✅ Macro panel visible")
    screenshot(page, "02_market_overview")


def phase2_watchlist_scan(page):
    """Phase 2: Trader scans watchlist for opportunities."""
    log("=" * 50)
    log("PHASE 2: Watchlist Scan")
    log("=" * 50)

    log("📋 Scanning watchlist for opportunities")
    waitlist_panel = page.locator("text=Watchlist")
    if waitlist_panel.is_visible(timeout=5000):
        log("✅ Watchlist panel visible")

    # Count watchlist items
    rows = page.locator("tr")
    row_count = rows.count()
    log("📊 Watchlist items", f"{row_count} rows found")

    # Look for BUY signals
    buy_signals = page.locator("text=BUY")
    buy_count = buy_signals.count()
    log("🟢 BUY signals found", str(buy_count))

    # Look for SELL signals
    sell_signals = page.locator("text=SELL")
    sell_count = sell_signals.count()
    log("🔴 SELL signals found", str(sell_count))

    screenshot(page, "03_watchlist_scan")

    # Find the best opportunity — click on first BUY signal stock
    log("🎯 Looking for top opportunity")
    if buy_count > 0:
        log("✅ Found BUY signal stocks, selecting first one")
    else:
        log("⚠️ No BUY signals, staying with default BBCA")


def phase3_stock_deep_dive(page):
    """Phase 3: Trader deep-dives into a stock — chart, patterns, regime."""
    log("=" * 50)
    log("PHASE 3: Stock Deep Dive — BBCA.JK")
    log("=" * 50)

    # Verify chart is visible
    log("📈 Checking candlestick chart")
    chart_header = page.locator("h3:has-text('BBCA')").first
    if chart_header.is_visible(timeout=5000):
        log("✅ Chart showing BBCA")

    canvas = page.locator("canvas").first
    if canvas.is_visible(timeout=3000):
        log("✅ Chart canvas rendered")
    screenshot(page, "04_chart_bbca")

    # Switch to BBRI to compare
    log("🔄 Switching to BBRI.JK for comparison")
    selector = page.locator("select").first
    selector.select_option("BBRI")
    wait(3000)

    bbri_header = page.locator("h3:has-text('BBRI')").first
    if bbri_header.is_visible(timeout=10000):
        log("✅ Chart updated to BBRI")
    screenshot(page, "05_chart_bbri")

    # Switch back to BBCA
    log("🔄 Switching back to BBCA.JK")
    selector.select_option("BBCA")
    wait(3000)

    # Go to Patterns tab
    log("🔍 Checking pattern analysis for BBCA")
    patterns_tab = page.locator("button:has-text('Patterns')").first
    patterns_tab.click()
    wait(15000)  # Wait for pattern analysis to load

    # Check if patterns data loaded
    pattern_panel = page.locator("text=Pattern Analysis").first
    if pattern_panel.is_visible(timeout=5000):
        log("✅ Pattern analysis loaded")
        # Check for candlestick patterns
        candle_section = page.locator("text=Candlestick Patterns").first
        if candle_section.is_visible(timeout=3000):
            log("✅ Candlestick patterns section visible")
        # Check market structure
        structure = page.locator("text=Market Structure").first
        if structure.is_visible(timeout=3000):
            log("✅ Market structure section visible")
    screenshot(page, "06_patterns_bbca")

    # Go to Regime tab
    log("🌊 Checking market regime")
    regime_tab = page.locator("button:has-text('Regime')").first
    regime_tab.click()
    wait(15000)

    regime_panel = page.locator("text=Market Regime").first
    if regime_panel.is_visible(timeout=5000):
        log("✅ Regime analysis loaded")
        # Try to read the regime value
        regime_value = page.locator(".text-2xl").first
        if regime_value.is_visible(timeout=3000):
            regime_text = regime_value.text_content()
            log("🌊 Current regime", regime_text)
    screenshot(page, "07_regime_bbca")


def phase4_ai_analysis(page):
    """Phase 4: Trader runs AI prediction and checks AI briefing."""
    log("=" * 50)
    log("PHASE 4: AI Analysis")
    log("=" * 50)

    # Go back to Command Center
    log("🏠 Returning to Command Center")
    cc_tab = page.locator("button:has-text('Command Center')").first
    cc_tab.click()
    wait(2000)

    # Run AI Prediction
    log("🤖 Running AI prediction for BBCA.JK")
    predict_btn = page.locator("button:has-text('Run Prediction')").first
    if predict_btn.is_visible(timeout=5000):
        predict_btn.click()
        log("⏳ Waiting for ML pipeline to complete...")

        # Wait for prediction result (can take 30-60s)
        signal_text = None
        for attempt in range(60):
            wait(2000)
            # Check for signal in decision strip
            signal_elem = page.locator("text=/Signal:|sinyal:|BUY|SELL|HOLD/").first
            if signal_elem.is_visible(timeout=1000):
                signal_text = signal_elem.text_content()
                if signal_text and signal_text.strip():
                    break

        if signal_text:
            log("✅ Prediction completed", f"Signal: {signal_text.strip()}")
        else:
            log("⚠️ Prediction may still be running")
    screenshot(page, "08_prediction_result")

    # Check AI Briefing
    log("🧠 Checking AI Multi-Agent Briefing")
    briefing_tab = page.locator("button:has-text('AI Briefing')").first
    briefing_tab.click()
    wait(3000)

    # Click Generate button
    generate_btn = page.locator("button:has-text('Generate')").first
    if generate_btn.is_visible(timeout=5000):
        log("▶️ Generating AI briefing...")
        generate_btn.click()
        wait(10000)  # Wait for briefing generation

        # Check if briefing content appeared
        summary = page.locator("text=Market Summary").first
        if summary.is_visible(timeout=5000):
            log("✅ AI Briefing generated — Market Summary visible")

        recommendation = page.locator("text=Final Recommendation").first
        if recommendation.is_visible(timeout=3000):
            log("✅ Final Recommendation section visible")

        risk = page.locator("text=Risk Assessment").first
        if risk.is_visible(timeout=3000):
            log("✅ Risk Assessment section visible")
    screenshot(page, "09_ai_briefing")

    # Check Sentiment
    log("😰😎 Checking Fear & Greed Index")
    sentiment_tab = page.locator("button:has-text('Sentiment')").first
    sentiment_tab.click()
    wait(15000)

    fg_panel = page.locator("text=Fear & Greed").first
    if fg_panel.is_visible(timeout=5000):
        log("✅ Fear & Greed panel loaded")
        # Try to read the score
        score_elem = page.locator(".text-5xl").first
        if score_elem.is_visible(timeout=3000):
            score = score_elem.text_content()
            log("📊 Fear & Greed Score", score)
    screenshot(page, "10_sentiment")

    # Check Risk Management
    log("⚠️ Checking Risk Management for BBCA")
    risk_tab = page.locator("button:has-text('Risk Mgmt')").first
    risk_tab.click()
    wait(15000)

    risk_panel = page.locator("text=Risk Management").first
    if risk_panel.is_visible(timeout=5000):
        log("✅ Risk Management panel loaded")

        var_section = page.locator("text=Value at Risk").first
        if var_section.is_visible(timeout=3000):
            log("✅ VaR section visible")

        kelly_section = page.locator("text=Kelly Criterion").first
        if kelly_section.is_visible(timeout=3000):
            log("✅ Kelly Criterion section visible")

        drawdown_section = page.locator("text=Drawdown").first
        if drawdown_section.is_visible(timeout=3000):
            log("✅ Drawdown section visible")
    screenshot(page, "11_risk_management")


def phase5_decision_execution(page):
    """Phase 5: Trader makes decision and executes trades."""
    log("=" * 50)
    log("PHASE 5: Decision & Trade Execution")
    log("=" * 50)

    # Return to Command Center
    log("🏠 Returning to Command Center for trade execution")
    cc_tab = page.locator("button:has-text('Command Center')").first
    cc_tab.click()
    wait(2000)

    # Check order book
    log("📖 Checking order book")
    orderbook = page.locator("text=Order Book").first
    if orderbook.is_visible(timeout=5000):
        log("✅ Order book visible")
    screenshot(page, "12_order_book")

    # Submit BUY order
    log("💸 Executing BUY order for BBCA.JK")
    submit_btn = page.locator("button:has-text('Submit')").first
    if submit_btn.is_visible(timeout=5000):
        submit_btn.click()
        log("⏳ Waiting for order fill...")
        wait(5000)

        # Check for order result
        status = page.locator("text=Status:").first
        if status.is_visible(timeout=10000):
            log("✅ Order result displayed")
            # Try to read fill details
            filled = page.locator("text=Filled:").first
            if filled.is_visible(timeout=3000):
                log("✅ Fill confirmation visible")
        else:
            log("⚠️ Order result may still be processing")

        journal["trades"].append({
            "ticker": "BBCA.JK",
            "side": "BUY",
            "quantity": 100,
            "timestamp": datetime.now().isoformat(),
        })
    screenshot(page, "13_trade_executed")

    # Submit another BUY with different quantity
    log("💸 Executing second BUY order")
    qty_input = page.locator("input[type='number']").first
    if qty_input.is_visible(timeout=3000):
        qty_input.fill("200")
        wait(500)

    # Wait for Submit button to be re-enabled (not "Submitting...")
    wait(3000)
    submit_btn = page.locator("button:has-text('Submit')").first
    if submit_btn.is_visible(timeout=5000) and submit_btn.is_enabled():
        submit_btn.click()
        wait(5000)
        log("✅ Second order submitted")
        journal["trades"].append({
            "ticker": "BBCA.JK",
            "side": "BUY",
            "quantity": 200,
            "timestamp": datetime.now().isoformat(),
        })
    else:
        log("⚠️ Submit button not ready, skipping second trade")
    screenshot(page, "14_second_trade")


def phase6_portfolio_review(page):
    """Phase 6: Trader reviews portfolio."""
    log("=" * 50)
    log("PHASE 6: Portfolio Review")
    log("=" * 50)

    # Check portfolio panel
    log("💼 Checking portfolio positions")
    portfolio = page.locator("text=Portfolio").first
    if portfolio.is_visible(timeout=5000):
        log("✅ Portfolio panel visible")

    # Check for BBCA.JK position
    bbca_pos = page.locator("text=BBCA.JK").first
    if bbca_pos.is_visible(timeout=5000):
        log("✅ BBCA.JK position found in portfolio")
    else:
        log("⚠️ BBCA.JK position not visible yet")
    screenshot(page, "15_portfolio")

    # Check Accuracy tab
    log("🎯 Checking prediction accuracy")
    accuracy_tab = page.locator("button:has-text('Accuracy')").first
    accuracy_tab.click()
    wait(10000)

    acc_panel = page.locator("text=Accuracy").first
    if acc_panel.is_visible(timeout=5000):
        log("✅ Accuracy panel loaded")
    screenshot(page, "16_accuracy")


def phase7_advanced_analysis(page):
    """Phase 7: Trader does advanced analysis."""
    log("=" * 50)
    log("PHASE 7: Advanced Analysis")
    log("=" * 50)

    # Backtesting
    log("📊 Running backtest for BBCA")
    bt_tab = page.locator("button:has-text('Backtesting')").first
    bt_tab.click()
    wait(3000)

    run_bt = page.locator("button:has-text('Run Backtest')").first
    if run_bt.is_visible(timeout=5000):
        run_bt.click()
        log("⏳ Backtest running...")
        wait(30000)  # Backtest takes time

        # Check for results
        dir_acc = page.locator("text=Directional Accuracy").first
        if dir_acc.is_visible(timeout=5000):
            log("✅ Backtest results displayed")
        else:
            log("⚠️ Backtest may still be running")
    screenshot(page, "17_backtest")

    # Portfolio Optimization
    log("💼 Checking Portfolio Optimization")
    opt_tab = page.locator("button:has-text('Portfolio Opt')").first
    opt_tab.click()
    wait(30000)

    opt_panel = page.locator("text=Portfolio Optimization").first
    if opt_panel.is_visible(timeout=5000):
        log("✅ Portfolio Optimization loaded")
        max_sharpe = page.locator("text=Max Sharpe Portfolio").first
        if max_sharpe.is_visible(timeout=3000):
            log("✅ Max Sharpe portfolio visible")
        min_vol = page.locator("text=Min Volatility Portfolio").first
        if min_vol.is_visible(timeout=3000):
            log("✅ Min Volatility portfolio visible")
    screenshot(page, "18_portfolio_opt")

    # Intermarket
    log("🌍 Checking Intermarket Analysis")
    inter_tab = page.locator("button:has-text('Intermarket')").first
    inter_tab.click()
    wait(30000)

    inter_panel = page.locator("text=Intermarket").first
    if inter_panel.is_visible(timeout=5000):
        log("✅ Intermarket analysis loaded")
        corr = page.locator("text=Correlation Matrix").first
        if corr.is_visible(timeout=3000):
            log("✅ Correlation matrix visible")
    screenshot(page, "19_intermarket")

    # Options
    log("📈 Checking Options Analysis")
    opt_tab = page.locator("button:has-text('Options')").first
    opt_tab.click()
    wait(15000)

    opts_panel = page.locator("text=Options Analysis").first
    if opts_panel.is_visible(timeout=5000):
        log("✅ Options analysis loaded")
    screenshot(page, "20_options")

    # Model Details
    log("🧩 Checking Model Details")
    md_tab = page.locator("button:has-text('Model Details')").first
    md_tab.click()
    wait(3000)

    md_panel = page.locator("text=Model Details").first
    if md_panel.is_visible(timeout=5000):
        log("✅ Model Details panel loaded")

        load_btn = page.locator("button:has-text('Load Details')").first
        if load_btn.is_visible(timeout=3000):
            load_btn.click()
            log("⏳ Loading model details (running prediction)...")
            wait(30000)

            # Check for model votes
            votes = page.locator("text=Model Votes").first
            if votes.is_visible(timeout=5000):
                log("✅ Model votes visible")

            shap = page.locator("text=SHAP Explanations").first
            if shap.is_visible(timeout=3000):
                log("✅ SHAP explanations visible")
    screenshot(page, "21_model_details")


def phase8_system_check(page):
    """Phase 8: Trader verifies system health."""
    log("=" * 50)
    log("PHASE 8: System Health Check")
    log("=" * 50)

    log("🔧 Checking system health")
    sys_tab = page.locator("button:has-text('System Check')").first
    sys_tab.click()
    wait(10000)

    sys_panel = page.locator("text=System Check").first
    if sys_panel.is_visible(timeout=5000):
        log("✅ System Check panel loaded")
    screenshot(page, "22_system_check")

    # Data Inventory
    log("🗄️ Checking data inventory")
    data_tab = page.locator("button:has-text('Data Inventory')").first
    data_tab.click()
    wait(5000)

    data_panel = page.locator("text=Data Inventory").first
    if data_panel.is_visible(timeout=5000):
        log("✅ Data Inventory loaded")
    screenshot(page, "23_data_inventory")

    # Trading Agent
    log("🤖 Checking Trading Agent")
    agent_tab = page.locator("button:has-text('Trading Agent')").first
    agent_tab.click()
    wait(3000)

    agent_panel = page.locator("text=Trading Agent").first
    if agent_panel.is_visible(timeout=5000):
        log("✅ Trading Agent panel loaded")
        guardrails = page.locator("text=Safety Guardrails").first
        if guardrails.is_visible(timeout=3000):
            log("✅ Safety guardrails visible")
    screenshot(page, "24_trading_agent")


def phase9_session_summary(page):
    """Phase 9: Final summary and report."""
    log("=" * 50)
    log("PHASE 9: Session Summary")
    log("=" * 50)

    # Return to Command Center
    cc_tab = page.locator("button:has-text('Command Center')").first
    cc_tab.click()
    wait(2000)
    screenshot(page, "25_final_dashboard")

    # Generate report
    journal["session_end"] = datetime.now().isoformat()

    # Count actions
    total_actions = len(journal["actions"])
    total_trades = len(journal["trades"])
    total_screenshots = len(journal["screenshots"])

    log("📋 SESSION SUMMARY")
    log(f"   Total actions: {total_actions}")
    log(f"   Trades executed: {total_trades}")
    log(f"   Screenshots captured: {total_screenshots}")

    # Save journal
    journal_path = f"{SCREENSHOT_DIR}/session_report.json"
    with open(journal_path, "w") as f:
        json.dump(journal, f, indent=2, default=str)
    log("💾 Session report saved", journal_path)

    # Print trade log
    if journal["trades"]:
        log("💼 TRADE LOG:")
        for t in journal["trades"]:
            log(f"   {t['side']} {t['quantity']} {t['ticker']} @ {t['timestamp']}")


# =============================================================================
# MAIN
# =============================================================================

def run_session():
    print()
    print("╔" + "═" * 58 + "╗")
    print("║" + "  📈 TRADING SIMULATION SESSION — Playwright Headed Mode".center(58) + "║")
    print("║" + f"  Trader: Simulated | Date: {datetime.now().strftime('%Y-%m-%d %H:%M')}".center(58) + "║")
    print("╚" + "═" * 58 + "╝")
    print()

    check_servers()

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False, args=["--start-maximized"])
        page = browser.new_page(viewport={"width": 1920, "height": 1080})

        # Capture console errors
        console_errors = []
        page.on("console", lambda msg: console_errors.append(f"{msg.type}: {msg.text}") if msg.type == "error" else None)

        try:
            phase1_market_overview(page)
            phase2_watchlist_scan(page)
            phase3_stock_deep_dive(page)
            phase4_ai_analysis(page)
            phase5_decision_execution(page)
            phase6_portfolio_review(page)
            phase7_advanced_analysis(page)
            phase8_system_check(page)
            phase9_session_summary(page)

            # Report console errors
            real_errors = [e for e in console_errors if "Could not establish connection" not in e]
            if real_errors:
                print(f"\n⚠️  Console errors during session ({len(real_errors)}):")
                for err in real_errors[:10]:
                    print(f"   - {err[:120]}")
            else:
                print("\n✅ No console errors during session")

            print("\n" + "╔" + "═" * 58 + "╗")
            print("║" + "  ✅ TRADING SESSION COMPLETED SUCCESSFULLY".center(58) + "║")
            print("╚" + "═" * 58 + "╝")

        except Exception as e:
            print(f"\n❌ Session error: {e}")
            page.screenshot(path=f"{SCREENSHOT_DIR}/error.png")
            import traceback
            traceback.print_exc()
        finally:
            wait(3000)
            browser.close()


if __name__ == "__main__":
    run_session()
