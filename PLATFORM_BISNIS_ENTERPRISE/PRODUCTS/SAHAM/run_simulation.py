"""
Run market simulation: train 6 months, simulate 3 months, 1 day = 1 second.
Capital: Rp 10.000.000

Requirements:
- Minimum training data: 6 months (~126 trading days) before train_end_date
- Recommended target data: 1+ years
- Recommended capital: Rp 5.000.000+ for index trading
"""
import sys
import os
import logging

# Setup logging
logging.basicConfig(level=logging.INFO, format="%(asctime)s | %(levelname)-8s | %(name)-20s | %(message)s")

logger = logging.getLogger(__name__)

def main():
    from src.simulation_engine import MarketSimulation, SIM_RESULTS_PATH

    logger.info("Starting Market Simulation")
    logger.info(f"Results will be saved to: {SIM_RESULTS_PATH}")

    sim = MarketSimulation(
        initial_capital=10_000_000,
        train_months=6,
        sim_months=3,
        day_duration_seconds=1.0,
        target_ticker="^JKSE",
        broker_name="bca_sekuritas",
        train_end_date="2026-01-31",
    )

    results = sim.run()

    logger.info("=" * 60)
    logger.info("FINAL RESULTS:")
    logger.info(f"  Initial Capital:  Rp {results.initial_capital:,.0f}")
    logger.info(f"  Final Capital:    Rp {results.final_capital:,.0f}")
    logger.info(f"  Total Return:     {results.total_return_pct:.2f}%")
    logger.info(f"  Buy & Hold:       {results.buy_hold_return_pct:.2f}%")
    logger.info(f"  Trades:           {results.n_trades}")
    logger.info(f"  Win Rate:         {results.win_rate:.1f}%")
    logger.info(f"  Max Drawdown:     {results.max_drawdown_pct:.2f}%")
    logger.info(f"  Sharpe Ratio:     {results.sharpe_ratio:.2f}")
    logger.info(f"  Profit Factor:    {results.profit_factor:.2f}")
    logger.info(f"  Total Commission: Rp {results.total_commission:,.0f}")
    logger.info("=" * 60)

    return results

if __name__ == "__main__":
    main()
