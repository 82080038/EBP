"""
Professional Trading Risk Manager.

Adopsi fitur institutional-grade dari:
- Bloomberg EMSX: Pre-trade risk checks, kill switch, TCA
- Algoment/Quantology: Drawdown control, volatility targeting, dynamic rebalancing
- PyPortfolioOpt: Black-Litterman, Risk Parity, Min Variance
- FIA Best Practices: Kill switch, circuit breaker, audit trail
- Two Sigma/Renaissance: Performance attribution, trade journaling

Implementasi:
1. Pre-trade risk checker (order-level validation)
2. Kill switch (emergency stop all trading)
3. Real-time portfolio risk monitor
4. Drawdown control (auto de-risking cascade)
5. Volatility targeting (dynamic position sizing)
6. Black-Litterman allocation
7. Risk parity allocation
8. Dynamic rebalancing (drift-based)
9. Performance attribution
10. Trade journal / audit log
11. Daily P&L tracking
12. Max consecutive loss limit
13. Order management (simplified OMS)
14. Post-trade TCA (transaction cost analysis)
"""

import pandas as pd
import numpy as np
from datetime import datetime
from typing import Dict, List, Optional
from dataclasses import dataclass, field
import json
import os

from .config import DATA_DIR


# =============================================================================
# 1. PRE-TRADE RISK CHECKER
# =============================================================================

@dataclass
class RiskLimits:
    """Risk limit configuration (institutional grade)."""
    max_position_size: float = 100_000_000     # Max notional per position
    max_order_size: float = 50_000_000         # Max notional per order
    max_portfolio_exposure: float = 500_000_000  # Total long + short
    max_concentration: float = 0.20            # Max weight single position
    max_daily_loss: float = 5_000_000           # Stop trading if exceeded
    max_drawdown: float = 0.15                 # 15% drawdown limit
    max_consecutive_losses: int = 5            # Stop after 5 consecutive losses
    max_orders_per_day: int = 20               # Order frequency limit
    min_confidence: float = 0.55               # Minimum model confidence
    min_rr_ratio: float = 1.5                  # Minimum risk/reward


@dataclass
class Order:
    """Trading order representation."""
    symbol: str
    side: str  # "BUY" or "SELL"
    quantity: int
    order_type: str = "MARKET"  # "MARKET", "LIMIT"
    limit_price: Optional[float] = None
    timestamp: datetime = field(default_factory=datetime.now)


@dataclass
class PreTradeCheckResult:
    """Pre-trade risk check result."""
    approved: bool
    reason: str
    checks_passed: int = 0
    checks_total: int = 0
    warnings: List[str] = field(default_factory=list)


class PreTradeRiskChecker:
    """
    Pre-trade risk checker — validates every order before submission.

    Adopted from: Bloomberg EMSX pre-trade risk, FIA best practices.
    """

    def __init__(self, limits: RiskLimits = None):
        self.limits = limits or RiskLimits()
        self.orders_today = 0
        self.daily_pnl = 0.0
        self.consecutive_losses = 0
        self.current_positions: Dict[str, float] = {}  # symbol -> notional
        self.portfolio_value = 100_000_000
        self.total_exposure = 0.0

    def check_order(
        self,
        order: Order,
        current_price: float,
    ) -> PreTradeCheckResult:
        """Check if order passes all pre-trade risk checks."""
        result = PreTradeCheckResult(approved=True, reason="Order approved")
        result.checks_total = 8

        order_notional = order.quantity * current_price

        # Check 1: Order size limit
        if order_notional > self.limits.max_order_size:
            return PreTradeCheckResult(
                False,
                f"Order size Rp {order_notional:,.0f} exceeds limit Rp {self.limits.max_order_size:,.0f}",
                result.checks_passed, result.checks_total
            )
        result.checks_passed += 1

        # Check 2: Position size limit
        current_pos = self.current_positions.get(order.symbol, 0)
        if order.side == "BUY":
            new_pos = current_pos + order_notional
        else:
            new_pos = current_pos - order_notional
        if abs(new_pos) > self.limits.max_position_size:
            return PreTradeCheckResult(
                False,
                f"Position Rp {abs(new_pos):,.0f} exceeds limit Rp {self.limits.max_position_size:,.0f}",
                result.checks_passed, result.checks_total
            )
        result.checks_passed += 1

        # Check 3: Concentration limit
        concentration = abs(new_pos) / self.portfolio_value
        if concentration > self.limits.max_concentration:
            return PreTradeCheckResult(
                False,
                f"Concentration {concentration:.1%} exceeds {self.limits.max_concentration:.1%}",
                result.checks_passed, result.checks_total
            )
        result.checks_passed += 1

        # Check 4: Portfolio exposure
        new_exposure = self.total_exposure + order_notional
        if new_exposure > self.limits.max_portfolio_exposure:
            return PreTradeCheckResult(
                False,
                f"Portfolio exposure Rp {new_exposure:,.0f} exceeds limit",
                result.checks_passed, result.checks_total
            )
        result.checks_passed += 1

        # Check 5: Daily loss limit
        if self.daily_pnl < -self.limits.max_daily_loss:
            return PreTradeCheckResult(
                False,
                f"Daily loss Rp {self.daily_pnl:,.0f} exceeds limit — trading suspended",
                result.checks_passed, result.checks_total
            )
        result.checks_passed += 1

        # Check 6: Consecutive losses
        if self.consecutive_losses >= self.limits.max_consecutive_losses:
            return PreTradeCheckResult(
                False,
                f"Consecutive losses ({self.consecutive_losses}) exceed limit — pause trading",
                result.checks_passed, result.checks_total
            )
        result.checks_passed += 1

        # Check 7: Order frequency
        if self.orders_today >= self.limits.max_orders_per_day:
            return PreTradeCheckResult(
                False,
                f"Daily order limit ({self.limits.max_orders_per_day}) reached",
                result.checks_passed, result.checks_total
            )
        result.checks_passed += 1

        # Check 8: Lot size (BEI: 100 shares)
        if order.quantity % 100 != 0:
            return PreTradeCheckResult(
                False,
                f"Quantity {order.quantity} not multiple of 100 (BEI lot size)",
                result.checks_passed, result.checks_total
            )
        result.checks_passed += 1

        return result

    def update_after_trade(self, order: Order, fill_price: float):
        """Update internal state after order is filled."""
        self.orders_today += 1
        notional = order.quantity * fill_price
        if order.side == "BUY":
            self.current_positions[order.symbol] = (
                self.current_positions.get(order.symbol, 0) + notional
            )
            self.total_exposure += notional
        else:
            self.current_positions[order.symbol] = (
                self.current_positions.get(order.symbol, 0) - notional
            )
            self.total_exposure -= notional

    def update_pnl(self, pnl: float):
        """Update daily P&L and consecutive loss counter."""
        self.daily_pnl += pnl
        if pnl < 0:
            self.consecutive_losses += 1
        else:
            self.consecutive_losses = 0

    def reset_daily(self):
        """Reset daily counters (call at start of each trading day)."""
        self.orders_today = 0
        self.daily_pnl = 0.0


# =============================================================================
# 2. KILL SWITCH
# =============================================================================

class KillSwitch:
    """
    Emergency kill switch — halts ALL trading activity.

    Adopted from: FIA Automated Trading Risk Controls, IOSCO guidelines.
    """

    def __init__(self):
        self.trading_enabled = True
        self.breach_reasons: List[str] = []
        self.activated_at: Optional[datetime] = None
        self.activation_reason: str = ""

    def check(
        self,
        daily_pnl: float = 0,
        current_drawdown: float = 0,
        max_drawdown_limit: float = 0.15,
        max_daily_loss: float = 5_000_000,
        consecutive_losses: int = 0,
        max_consecutive: int = 5,
    ) -> bool:
        """Check if kill switch should be activated."""
        if not self.trading_enabled:
            return False

        if daily_pnl < -max_daily_loss:
            self._activate(f"Daily loss Rp {daily_pnl:,.0f} exceeds -Rp {max_daily_loss:,.0f}")
            return False

        if current_drawdown > max_drawdown_limit:
            self._activate(f"Drawdown {current_drawdown:.1%} exceeds {max_drawdown_limit:.1%}")
            return False

        if consecutive_losses >= max_consecutive:
            self._activate(f"Consecutive losses ({consecutive_losses}) >= {max_consecutive}")
            return False

        return True

    def _activate(self, reason: str):
        """Activate kill switch."""
        self.trading_enabled = False
        self.activated_at = datetime.now()
        self.activation_reason = reason
        self.breach_reasons.append(reason)
        print(f"🛑 KILL SWITCH ACTIVATED: {reason}")
        print(f"   All trading HALTED at {self.activated_at}")

    def can_trade(self) -> bool:
        """Check if trading is currently allowed."""
        return self.trading_enabled

    def reset(self, confirmation: str = "CONFIRM_RESET"):
        """Reset kill switch (requires manual confirmation)."""
        if confirmation == "CONFIRM_RESET":
            self.trading_enabled = True
            self.breach_reasons = []
            self.activated_at = None
            self.activation_reason = ""
            print("✅ Kill switch reset — trading resumed")
        else:
            print("❌ Reset requires 'CONFIRM_RESET' confirmation")

    def status(self) -> dict:
        return {
            "trading_enabled": self.trading_enabled,
            "activated_at": str(self.activated_at) if self.activated_at else None,
            "reason": self.activation_reason,
            "total_breaches": len(self.breach_reasons),
        }


# =============================================================================
# 3. REAL-TIME PORTFOLIO RISK MONITOR
# =============================================================================

class PortfolioRiskMonitor:
    """
    Real-time portfolio risk monitoring.

    Adopted from: Bloomberg AIM, Algoment risk analytics.
    """

    def __init__(self, confidence_level: float = 0.95):
        self.confidence_level = confidence_level
        self.returns_history: List[float] = []
        self.portfolio_values: List[float] = []
        self.peak_value: float = 0
        self.current_drawdown: float = 0
        self.daily_pnl: float = 0
        self.trade_count: int = 0

    def update(self, portfolio_value: float, portfolio_return: float):
        """Update risk metrics with new portfolio state."""
        self.returns_history.append(portfolio_return)
        self.portfolio_values.append(portfolio_value)

        if portfolio_value > self.peak_value:
            self.peak_value = portfolio_value
        self.current_drawdown = (self.peak_value - portfolio_value) / self.peak_value if self.peak_value > 0 else 0

    def calculate_var(self, horizon_days: int = 1) -> float:
        """Calculate parametric VaR."""
        if len(self.returns_history) < 20:
            return float("inf")
        from scipy import stats
        returns = np.array(self.returns_history)
        mu = returns.mean() * horizon_days
        sigma = returns.std() * np.sqrt(horizon_days)
        var = -(mu + sigma * stats.norm.ppf(1 - self.confidence_level))
        return float(var)

    def calculate_expected_shortfall(self, horizon_days: int = 1) -> float:
        """Calculate Expected Shortfall (CVaR)."""
        if len(self.returns_history) < 20:
            return float("inf")
        from scipy import stats
        returns = np.array(self.returns_history)
        mu = returns.mean() * horizon_days
        sigma = returns.std() * np.sqrt(horizon_days)
        alpha = 1 - self.confidence_level
        es = -mu + sigma * stats.norm.pdf(stats.norm.ppf(alpha)) / alpha
        return float(es)

    def get_risk_summary(self) -> dict:
        """Get current risk metric summary."""
        realized_vol = np.std(self.returns_history) * np.sqrt(252) if len(self.returns_history) > 1 else 0
        return {
            "current_drawdown": round(self.current_drawdown, 4),
            "current_drawdown_pct": round(self.current_drawdown * 100, 2),
            "var_1d_95": round(self.calculate_var(1), 4),
            "es_1d_95": round(self.calculate_expected_shortfall(1), 4),
            "realized_vol_annualized": round(realized_vol, 4),
            "peak_value": round(self.peak_value, 0),
            "current_value": round(self.portfolio_values[-1] if self.portfolio_values else 0, 0),
            "trade_count": self.trade_count,
            "daily_pnl": round(self.daily_pnl, 0),
        }


# =============================================================================
# 4. DRAWDOWN CONTROL (AUTO DE-RISKING)
# =============================================================================

class DrawdownControl:
    """
    Automated drawdown control with de-risking cascade.

    Adopted from: Algoment drawdown control, Milliman volatility management.
    """

    LEVELS = [
        {"drawdown_pct": 5, "action": "reduce_25%", "size_mult": 0.75, "description": "Reduce position 25%"},
        {"drawdown_pct": 10, "action": "reduce_50%", "size_mult": 0.50, "description": "Reduce position 50%"},
        {"drawdown_pct": 15, "action": "reduce_75%", "size_mult": 0.25, "description": "Reduce position 75%"},
        {"drawdown_pct": 20, "action": "close_all", "size_mult": 0.0, "description": "Close all positions"},
    ]

    def __init__(self):
        self.current_level = 0
        self.activated_actions: List[str] = []

    def check(self, current_drawdown: float) -> dict:
        """Check drawdown and return recommended action."""
        triggered_level = 0
        action = "none"
        size_mult = 1.0
        description = "Normal — full position size"

        for i, level in enumerate(self.LEVELS, 1):
            if current_drawdown * 100 >= level["drawdown_pct"]:
                triggered_level = i
                action = level["action"]
                size_mult = level["size_mult"]
                description = level["description"]

        if triggered_level > self.current_level:
            self.current_level = triggered_level
            self.activated_actions.append(f"{datetime.now()}: {description} (DD={current_drawdown:.1%})")

        return {
            "level": triggered_level,
            "action": action,
            "position_size_multiplier": size_mult,
            "description": description,
            "should_trade": size_mult > 0,
            "activated_actions": self.activated_actions[-5:],
        }


# =============================================================================
# 5. VOLATILITY TARGETING
# =============================================================================

class VolatilityTargeting:
    """
    Dynamic volatility targeting — adjust position size based on realized vol.

    Adopted from: Milliman Managed Risk Parity, Target Volatility strategy.
    """

    def __init__(self, target_vol: float = 0.15, max_leverage: float = 1.5):
        self.target_vol = target_vol  # 15% annualized target
        self.max_leverage = max_leverage
        self.vol_history: List[float] = []

    def calculate_position_size(self, realized_vol: float) -> dict:
        """Calculate position size multiplier based on volatility targeting."""
        if realized_vol <= 0:
            return {"multiplier": 1.0, "leverage": 1.0, "status": "normal"}

        target_mult = self.target_vol / realized_vol
        leverage = min(target_mult, self.max_leverage)
        leverage = max(leverage, 0.0)  # No negative leverage

        if leverage < 0.5:
            status = "de_risked"
        elif leverage > 1.2:
            status = "leveraged"
        else:
            status = "normal"

        return {
            "multiplier": round(leverage, 3),
            "leverage": round(leverage, 3),
            "target_vol": self.target_vol,
            "realized_vol": round(realized_vol, 4),
            "status": status,
            "description": f"Target vol {self.target_vol:.0%} / Realized {realized_vol:.1%} → {leverage:.2f}x",
        }


# =============================================================================
# 6. BLACK-LITTERMAN ALLOCATION
# =============================================================================

def black_litterman_allocation(
    market_weights: np.ndarray,
    cov_matrix: np.ndarray,
    views: Optional[np.ndarray] = None,
    view_confidences: Optional[np.ndarray] = None,
    risk_free_rate: float = 0.05,
    tau: float = 0.025,
) -> dict:
    """
    Black-Litterman portfolio allocation.

    Combines market equilibrium returns with investor views.
    Adopted from: PyPortfolioOpt Black-Litterman implementation.

    Args:
        market_weights: Market cap weights (equilibrium)
        cov_matrix: Covariance matrix of returns
        views: Investor views (expected excess returns)
        view_confidences: Confidence in each view (0-1)
        risk_free_rate: Risk-free rate
        tau: Scaling parameter for prior covariance

    Returns:
        Dict with BL expected returns and optimal weights
    """
    n = len(market_weights)

    # Implied equilibrium returns
    risk_aversion = 3.0  # Typical risk aversion
    pi = risk_aversion * cov_matrix @ market_weights

    if views is None:
        views = np.zeros(n)
        view_confidences = np.ones(n) * 0.5

    # View matrix (identity for simplicity)
    P = np.eye(n)
    Q = views
    omega = np.diag(1.0 / view_confidences) * tau if view_confidences is not None else np.eye(n) * tau

    # BL posterior returns
    inv_tau_cov = np.linalg.inv(tau * cov_matrix)
    inv_omega = np.linalg.inv(omega)

    bl_returns = np.linalg.inv(inv_tau_cov + P.T @ inv_omega @ P) @ (
        inv_tau_cov @ pi + P.T @ inv_omega @ Q
    )

    # BL posterior covariance
    bl_cov = np.linalg.inv(inv_tau_cov + P.T @ inv_omega @ P)

    # Optimal weights (mean-variance)
    bl_weights = np.linalg.inv(risk_aversion * bl_cov) @ bl_returns
    bl_weights = np.maximum(bl_weights, 0)  # Long-only
    bl_weights = bl_weights / bl_weights.sum() if bl_weights.sum() > 0 else market_weights

    return {
        "bl_expected_returns": {f"Asset_{i}": round(float(bl_returns[i]), 4) for i in range(n)},
        "bl_weights": {f"Asset_{i}": round(float(bl_weights[i]), 4) for i in range(n)},
        "market_weights": {f"Asset_{i}": round(float(market_weights[i]), 4) for i in range(n)},
        "risk_aversion": risk_aversion,
        "tau": tau,
    }


# =============================================================================
# 7. RISK PARITY ALLOCATION
# =============================================================================

def risk_parity_allocation(cov_matrix: np.ndarray) -> dict:
    """
    Risk parity allocation — equal risk contribution from each asset.

    Adopted from: Milliman Managed Risk Parity, Bridgewater All Weather.

    Uses iterative approach to equalize risk contributions.
    """
    n = cov_matrix.shape[0]
    weights = np.ones(n) / n  # Start with equal weights
    lr = 0.1  # Learning rate to prevent oscillation

    for iteration in range(500):
        portfolio_vol = np.sqrt(weights @ cov_matrix @ weights)
        if portfolio_vol == 0:
            break

        # Marginal risk contributions
        mrc = cov_matrix @ weights / portfolio_vol
        # Risk contributions
        rc = weights * mrc

        # Target: equal risk contribution
        target_rc = portfolio_vol / n

        # Adjust weights with damping to prevent oscillation
        rc_safe = np.where(rc > 1e-12, rc, 1e-12)
        adjustment = target_rc / rc_safe
        new_weights = weights * (1 - lr + lr * adjustment)
        w_sum = new_weights.sum()
        if w_sum > 0:
            weights = new_weights / w_sum

    # Final risk contributions
    portfolio_vol = np.sqrt(weights @ cov_matrix @ weights)
    mrc = cov_matrix @ weights / portfolio_vol
    rc = weights * mrc
    rc_pct = rc / rc.sum() * 100

    return {
        "weights": {f"Asset_{i}": round(float(weights[i]), 4) for i in range(n)},
        "risk_contributions": {f"Asset_{i}": round(float(rc_pct[i]), 2) for i in range(n)},
        "portfolio_volatility": round(float(portfolio_vol), 4),
        "is_equal_risk": np.std(rc_pct) < 1.0,
    }


# =============================================================================
# 8. DYNAMIC REBALANCING (DRIFT-BASED)
# =============================================================================

@dataclass
class RebalanceSignal:
    """Rebalancing signal."""
    should_rebalance: bool
    reason: str
    current_weights: Dict[str, float] = field(default_factory=dict)
    target_weights: Dict[str, float] = field(default_factory=dict)
    drift: Dict[str, float] = field(default_factory=dict)
    max_drift: float = 0.0
    recommended_trades: List[dict] = field(default_factory=list)


def check_rebalance_needed(
    current_weights: Dict[str, float],
    target_weights: Dict[str, float],
    drift_threshold: float = 0.05,  # 5% drift triggers rebalance
    time_since_last: int = 30,  # Days since last rebalance
    max_days: int = 90,
) -> RebalanceSignal:
    """
    Check if portfolio needs rebalancing based on drift.

    Adopted from: Algoment automated rebalancing, Quantology SAA/TAA.
    """
    drift = {}
    max_drift = 0.0

    for asset in target_weights:
        current = current_weights.get(asset, 0)
        target = target_weights[asset]
        d = abs(current - target)
        drift[asset] = round(d, 4)
        max_drift = max(max_drift, d)

    should_rebalance = False
    reason = "No rebalance needed"

    if max_drift >= drift_threshold:
        should_rebalance = True
        reason = f"Max drift {max_drift:.1%} exceeds threshold {drift_threshold:.1%}"
    elif time_since_last >= max_days:
        should_rebalance = True
        reason = f"Time-based: {time_since_last} days since last rebalance (max {max_days})"

    # Calculate recommended trades
    trades = []
    if should_rebalance:
        for asset in target_weights:
            current = current_weights.get(asset, 0)
            target = target_weights[asset]
            diff = target - current
            if abs(diff) > 0.001:  # 0.1% minimum trade
                trades.append({
                    "asset": asset,
                    "action": "BUY" if diff > 0 else "SELL",
                    "weight_change": round(diff, 4),
                })

    return RebalanceSignal(
        should_rebalance=should_rebalance,
        reason=reason,
        current_weights=current_weights,
        target_weights=target_weights,
        drift=drift,
        max_drift=round(max_drift, 4),
        recommended_trades=trades,
    )


# =============================================================================
# 9. PERFORMANCE ATTRIBUTION
# =============================================================================

def performance_attribution(
    portfolio_returns: pd.Series,
    benchmark_returns: pd.Series,
    asset_returns: Optional[pd.DataFrame] = None,
    asset_weights: Optional[pd.DataFrame] = None,
) -> dict:
    """
    Performance attribution — decompose returns into sources.

    Adopted from: Quantology performance attribution, Bloomberg AIM.
    """
    excess_return = portfolio_returns.mean() - benchmark_returns.mean()
    tracking_error = (portfolio_returns - benchmark_returns).std() * np.sqrt(252)
    information_ratio = excess_return / tracking_error * 252 if tracking_error > 0 else 0

    # Brinson-style attribution (if asset-level data available)
    attribution = {
        "total_return": round(float(portfolio_returns.mean() * 252 * 100), 2),
        "benchmark_return": round(float(benchmark_returns.mean() * 252 * 100), 2),
        "excess_return": round(float(excess_return * 252 * 100), 2),
        "tracking_error": round(float(tracking_error * 100), 2),
        "information_ratio": round(float(information_ratio), 3),
        "alpha": round(float(excess_return * 252), 4),
        "beta": round(float(portfolio_returns.corr(benchmark_returns) *
                           portfolio_returns.std() / benchmark_returns.std()), 3)
                  if benchmark_returns.std() > 0 else 0,
    }

    if asset_returns is not None and asset_weights is not None:
        # Asset-level attribution
        asset_attribution = {}
        for col in asset_returns.columns:
            if col in asset_weights.columns:
                avg_weight = asset_weights[col].mean()
                asset_return = asset_returns[col].mean() * 252
                contribution = avg_weight * asset_return
                asset_attribution[col] = {
                    "avg_weight": round(float(avg_weight), 4),
                    "annual_return": round(float(asset_return * 100), 2),
                    "contribution": round(float(contribution * 100), 2),
                }
        attribution["asset_attribution"] = asset_attribution

    return attribution


# =============================================================================
# 10. TRADE JOURNAL / AUDIT LOG
# =============================================================================

class TradeJournal:
    """
    Trade journal with audit trail.

    Adopted from: FIA audit trail requirements, professional trade logging.
    """

    def __init__(self):
        self.trades: List[dict] = []
        self.journal_file = os.path.join(DATA_DIR, "trade_journal.json")

    def log_trade(
        self,
        symbol: str,
        side: str,
        quantity: int,
        price: float,
        signal: str = "",
        confidence: float = 0,
        ai_score: float = 0,
        regime: str = "",
        cost: float = 0,
        notes: str = "",
    ):
        """Log a trade with full context."""
        entry = {
            "timestamp": datetime.now().isoformat(),
            "symbol": symbol,
            "side": side,
            "quantity": quantity,
            "price": price,
            "value": quantity * price,
            "signal": signal,
            "confidence": confidence,
            "ai_score": ai_score,
            "regime": regime,
            "cost": cost,
            "notes": notes,
        }
        self.trades.append(entry)
        self._save()

    def log_close(self, symbol: str, entry_price: float, exit_price: float,
                  quantity: int, days_held: int, side: str = "BUY"):
        """Log a closed position with P&L."""
        if side == "BUY":
            pnl = (exit_price - entry_price) * quantity
        else:
            pnl = (entry_price - exit_price) * quantity
        return_pct = ((exit_price - entry_price) / entry_price) * 100
        entry = {
            "timestamp": datetime.now().isoformat(),
            "type": "CLOSE",
            "symbol": symbol,
            "entry_price": entry_price,
            "exit_price": exit_price,
            "quantity": quantity,
            "pnl": round(pnl, 0),
            "return_pct": round(return_pct, 2),
            "days_held": days_held,
        }
        self.trades.append(entry)
        self._save()

    def get_summary(self) -> dict:
        """Get trade journal summary."""
        closed = [t for t in self.trades if t.get("type") == "CLOSE"]
        wins = [t for t in closed if t.get("pnl", 0) > 0]
        losses = [t for t in closed if t.get("pnl", 0) < 0]

        total_pnl = sum(t.get("pnl", 0) for t in closed)
        win_rate = len(wins) / len(closed) * 100 if closed else 0

        return {
            "total_trades": len(self.trades),
            "closed_trades": len(closed),
            "open_trades": len(self.trades) - len(closed),
            "wins": len(wins),
            "losses": len(losses),
            "win_rate": round(win_rate, 1),
            "total_pnl": round(total_pnl, 0),
            "avg_win": round(np.mean([t["pnl"] for t in wins]), 0) if wins else 0,
            "avg_loss": round(np.mean([t["pnl"] for t in losses]), 0) if losses else 0,
            "profit_factor": abs(sum(t["pnl"] for t in wins) / sum(t["pnl"] for t in losses))
                             if losses and sum(t["pnl"] for t in losses) != 0 else float("inf"),
        }

    def _save(self):
        try:
            with open(self.journal_file, "w") as f:
                json.dump(self.trades[-500:], f, indent=2)  # Keep last 500
        except (IOError, json.JSONEncodeError):
            pass

    def load(self):
        try:
            if os.path.exists(self.journal_file):
                with open(self.journal_file, "r") as f:
                    self.trades = json.load(f)
        except (IOError, json.JSONDecodeError):
            pass


# =============================================================================
# 11. POST-TRADE TCA (TRANSACTION COST ANALYSIS)
# =============================================================================

def post_trade_tca(
    trades: List[dict],
    benchmark_prices: Optional[Dict[str, float]] = None,
) -> dict:
    """
    Post-trade Transaction Cost Analysis.

    Adopted from: Bloomberg BTCA, Refinitiv TCA.

    Measures: Implementation Shortfall, slippage vs benchmark, execution quality.
    """
    if not trades:
        return {"error": "No trades to analyze"}

    total_slippage_bps = []
    total_cost_idr = 0
    total_value = 0

    for trade in trades:
        price = trade.get("price", 0)
        value = trade.get("value", trade.get("quantity", 0) * price)
        cost = trade.get("cost", 0)

        # Slippage vs benchmark (if available)
        if benchmark_prices and trade.get("symbol") in benchmark_prices:
            bench = benchmark_prices[trade["symbol"]]
            slippage_bps = ((price - bench) / bench) * 10000
            if trade.get("side") == "SELL":
                slippage_bps = -slippage_bps
            total_slippage_bps.append(slippage_bps)

        total_cost_idr += cost
        total_value += value

    avg_slippage = np.mean(total_slippage_bps) if total_slippage_bps else 0
    cost_bps = (total_cost_idr / total_value * 10000) if total_value > 0 else 0

    return {
        "num_trades": len(trades),
        "total_value": round(total_value, 0),
        "total_cost": round(total_cost_idr, 0),
        "avg_cost_bps": round(cost_bps, 2),
        "avg_slippage_bps": round(float(avg_slippage), 2),
        "implementation_shortfall_bps": round(cost_bps + abs(float(avg_slippage)), 2),
        "execution_quality": "GOOD" if cost_bps < 20 else "FAIR" if cost_bps < 40 else "POOR",
    }


# =============================================================================
# 12. COMPREHENSIVE RISK GOVERNANCE
# =============================================================================

class RiskGovernance:
    """
    Comprehensive risk governance — integrates all risk components.

    This is the master risk controller that ties together:
    - Pre-trade checks
    - Kill switch
    - Portfolio monitor
    - Drawdown control
    - Volatility targeting
    - Trade journal
    """

    def __init__(self, capital: float = 100_000_000):
        self.capital = capital
        self.limits = RiskLimits(
            max_position_size=capital * 0.20,
            max_order_size=capital * 0.10,
            max_portfolio_exposure=capital * 1.5,
            max_daily_loss=capital * 0.03,
            max_drawdown=0.15,
        )
        self.pre_trade = PreTradeRiskChecker(self.limits)
        self.kill_switch = KillSwitch()
        self.portfolio_monitor = PortfolioRiskMonitor()
        self.drawdown_control = DrawdownControl()
        self.vol_targeting = VolatilityTargeting()
        self.journal = TradeJournal()
        self.journal.load()

    def evaluate_trade(
        self,
        order: Order,
        current_price: float,
        signal: str = "",
        confidence: float = 0,
        ai_score: float = 0,
        regime: str = "",
    ) -> dict:
        """
        Full risk evaluation before executing a trade.
        Returns comprehensive risk assessment.
        """
        # 1. Kill switch check
        if not self.kill_switch.can_trade():
            return {
                "approved": False,
                "reason": "KILL SWITCH ACTIVE — all trading halted",
                "kill_switch": self.kill_switch.status(),
            }

        # 2. Pre-trade risk checks
        check = self.pre_trade.check_order(order, current_price)

        # 3. Drawdown control
        dd = self.drawdown_control.check(self.portfolio_monitor.current_drawdown)

        # 4. Volatility targeting
        realized_vol = self.portfolio_monitor.get_risk_summary().get("realized_vol_annualized", 0.15)
        vol_target = self.vol_targeting.calculate_position_size(realized_vol)

        # 5. Combine all multipliers
        final_size_mult = dd["position_size_multiplier"] * vol_target["multiplier"]

        result = {
            "approved": check.approved and dd["should_trade"] and final_size_mult > 0,
            "reason": check.reason if not check.approved else (
                dd["description"] if not dd["should_trade"] else "All checks passed"
            ),
            "pre_trade_checks": {
                "passed": check.checks_passed,
                "total": check.checks_total,
                "warnings": check.warnings,
            },
            "kill_switch": self.kill_switch.status(),
            "drawdown_control": dd,
            "volatility_targeting": vol_target,
            "final_position_size_multiplier": round(final_size_mult, 3),
            "adjusted_quantity": int(order.quantity * final_size_mult / 100) * 100,  # Round to lot
            "signal": signal,
            "confidence": confidence,
            "ai_score": ai_score,
            "regime": regime,
        }

        if result["approved"]:
            self.journal.log_trade(
                symbol=order.symbol, side=order.side,
                quantity=result["adjusted_quantity"],
                price=current_price,
                signal=signal, confidence=confidence,
                ai_score=ai_score, regime=regime,
            )

        return result

    def get_full_report(self) -> dict:
        """Get comprehensive risk governance report."""
        return {
            "capital": self.capital,
            "kill_switch": self.kill_switch.status(),
            "portfolio_risk": self.portfolio_monitor.get_risk_summary(),
            "drawdown_control": self.drawdown_control.check(self.portfolio_monitor.current_drawdown),
            "trade_journal": self.journal.get_summary(),
            "risk_limits": {
                "max_position_size": self.limits.max_position_size,
                "max_order_size": self.limits.max_order_size,
                "max_daily_loss": self.limits.max_daily_loss,
                "max_drawdown": self.limits.max_drawdown,
                "max_consecutive_losses": self.limits.max_consecutive_losses,
            },
        }
