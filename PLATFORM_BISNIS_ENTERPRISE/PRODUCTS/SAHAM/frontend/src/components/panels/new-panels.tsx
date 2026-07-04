"use client";

import { useState, useEffect, useCallback } from "react";
import { Panel } from "@/components/ui/panel";
import { StatusBadge } from "@/components/ui/status-badge";
import { api } from "@/lib/api";
import { cn, formatNumber, formatPercent } from "@/lib/utils";
import type {
  BriefingResult,
  BacktestResult,
  SentimentResult,
  PatternResult,
  RiskMetrics,
  PortfolioOptimization,
  PortfolioPosition,
  AccuracyResult,
  ModelDetails,
  RegimeResult,
  IntermarketResult,
  OptionsResult,
  SystemCheckResult,
  DataInventoryItem,
} from "@/lib/types";
import {
  Brain, FlaskConical, Gauge, CandlestickChart, ShieldAlert, Briefcase,
  Target, Cpu, Activity, Globe, Layers, Database, Stethoscope, Play,
} from "lucide-react";
import { createPanelLogger } from "@/lib/panel-logger";

// ==================== AI Briefing ====================
export function AIBriefingPanel() {
  const log = createPanelLogger("AIBriefing");
  const [data, setData] = useState<BriefingResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("generate");
    setLoading(true);
    setError(null);
    try {
      const result = await api.getFullBriefing();
      setData(result);
      log.success("generate", { keys: Object.keys(result) });
    } catch (e) {
      log.error("generate", e);
      setError(e instanceof Error ? e.message : "Failed to load briefing");
    }
    setLoading(false);
  }, []);

  return (
    <Panel
      title="AI Multi-Agent Briefing"
      icon={<Brain className="h-4 w-4" />}
      badge={<StatusBadge status="Simulated" />}
      actions={
        <button
          onClick={load}
          disabled={loading}
          className="flex items-center gap-1 rounded bg-accent/20 px-2 py-1 text-xs text-accent hover:bg-accent/30 disabled:opacity-50"
        >
          <Play className="h-3 w-3" /> {loading ? "Analyzing..." : "Generate"}
        </button>
      }
    >
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {!data && !loading && !error && (
        <div className="p-8 text-center text-sm text-muted">Click Generate to run AI briefing</div>
      )}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 4 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-4 p-4">
          <div className="grid grid-cols-3 gap-3">
            <Metric label="Signal" value={data.signal} />
            <Metric label="Confidence" value={formatPercent(data.confidence)} />
            <Metric label="Date" value={data.date} />
          </div>
          <Section title="Market Summary" text={data.market_summary} />
          <Section title="Final Recommendation" text={data.final_recommendation} />
          <Section title="Risk Assessment" text={data.risk_assessment} />
          {data.actionable_items?.length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Actionable Items</h4>
              <ul className="space-y-1">
                {data.actionable_items.map((item, i) => (
                  <li key={i} className="text-sm text-foreground/80">• {item}</li>
                ))}
              </ul>
            </div>
          )}
        </div>
      )}
    </Panel>
  );
}

// ==================== Backtesting ====================
export function BacktestingPanel({ ticker }: { ticker: string }) {
  const log = createPanelLogger("Backtesting");
  const [data, setData] = useState<BacktestResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("run", ticker);
    setLoading(true);
    setError(null);
    try {
      const result = await api.getBacktest(ticker);
      setData(result);
      log.success("run", { accuracy: result.backtest?.directional_accuracy });
    } catch (e) {
      log.error("run", e);
      setError(e instanceof Error ? e.message : "Failed to load backtest");
    }
    setLoading(false);
  }, [ticker]);

  return (
    <Panel
      title={`Backtesting — ${ticker}`}
      icon={<FlaskConical className="h-4 w-4" />}
      badge={<StatusBadge status="Simulated" />}
      actions={
        <button onClick={load} disabled={loading} className="flex items-center gap-1 rounded bg-accent/20 px-2 py-1 text-xs text-accent hover:bg-accent/30 disabled:opacity-50">
          <Play className="h-3 w-3" /> {loading ? "Running..." : "Run Backtest"}
        </button>
      }
    >
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {!data && !loading && !error && <div className="p-8 text-center text-sm text-muted">Click Run Backtest to start</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-4 p-4">
          <div className="grid grid-cols-3 gap-3">
            <Metric label="Directional Accuracy" value={`${data.backtest.directional_accuracy}%`} />
            <Metric label="Total Predictions" value={String(data.backtest.total_predictions)} />
            {data.backtest.MAPE && <Metric label="MAPE" value={`${data.backtest.MAPE}%`} />}
          </div>
          <h4 className="text-xs font-bold uppercase text-muted">Trading Simulation</h4>
          <div className="grid grid-cols-4 gap-3">
            <Metric label="Initial Capital" value={formatNumber(data.simulation.initial_capital)} />
            <Metric label="Final Capital" value={formatNumber(data.simulation.final_capital)} />
            <Metric label="Total Return" value={formatPercent(data.simulation.total_return)} />
            <Metric label="Buy & Hold" value={formatPercent(data.simulation.buy_hold_return)} />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <Metric label="Trades" value={String(data.simulation.trades)} />
            <Metric label="Max Drawdown" value={formatPercent(data.simulation.max_drawdown)} />
          </div>
          {data.simulation.portfolio_values && data.simulation.portfolio_values.length > 0 && (
            <div className="rounded border border-panel-border p-3">
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Equity Curve</h4>
              <MiniChart values={data.simulation.portfolio_values} />
            </div>
          )}
        </div>
      )}
    </Panel>
  );
}

// ==================== Sentiment ====================
export function SentimentPanel() {
  const log = createPanelLogger("Sentiment");
  const [data, setData] = useState<SentimentResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load");
    setLoading(true);
    setError(null);
    try {
      const result = await api.getFullSentiment();
      setData(result);
      log.success("load", { score: result.composite_score, label: result.label });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load sentiment");
    }
    setLoading(false);
  }, []);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title="Fear & Greed Index" icon={<Gauge className="h-4 w-4" />} badge={<StatusBadge status="Delayed" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-4 p-4">
          <div className="flex items-center justify-center py-4">
            <div className="text-center">
              <div className={cn("text-5xl font-bold", data.composite_score > 55 ? "text-green-400" : data.composite_score < 45 ? "text-red-400" : "text-yellow-400")}>
                {data.composite_score.toFixed(0)}
              </div>
              <div className="mt-1 text-sm text-muted">{data.label}</div>
            </div>
          </div>
          <div className="space-y-1">
            <h4 className="text-xs font-bold uppercase text-muted">Components</h4>
            {Object.entries(data.components || {}).map(([name, info]) => (
              <div key={name} className="flex items-center justify-between border-b border-panel-border/30 py-1 text-sm">
                <span className="text-foreground/80">{name}</span>
                <div className="flex items-center gap-3">
                  <span className="font-mono text-xs text-muted">{String(info.value)}</span>
                  <span className={cn("font-mono font-bold", (info.score as number) > 55 ? "text-green-400" : (info.score as number) < 45 ? "text-red-400" : "text-yellow-400")}>
                    {(info.score as number).toFixed(0)}
                  </span>
                </div>
              </div>
            ))}
          </div>
          {data.advice && <Section title="Advice" text={data.advice} />}
        </div>
      )}
    </Panel>
  );
}

// ==================== Pattern Analysis ====================
export function PatternsPanel({ ticker }: { ticker: string }) {
  const log = createPanelLogger("Patterns");
  const [data, setData] = useState<PatternResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load", ticker);
    setLoading(true);
    setError(null);
    try {
      const result = await api.getFullPatterns(ticker);
      setData(result);
      log.success("load", { candlestick: result.candlestick_patterns?.length, structure: result.market_structure?.structure });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load patterns");
    }
    setLoading(false);
  }, [ticker]);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title={`Pattern Analysis — ${ticker}`} icon={<CandlestickChart className="h-4 w-4" />} badge={<StatusBadge status="Delayed" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-4 p-4">
          <Section title="Summary" text={data.summary} />
          <div>
            <h4 className="mb-2 text-xs font-bold uppercase text-muted">Market Structure</h4>
            <div className="rounded border border-panel-border p-2 text-sm">
              <span className="font-bold text-accent">{data.market_structure.structure}</span>
              <p className="mt-1 text-muted">{data.market_structure.description}</p>
            </div>
          </div>
          {data.candlestick_patterns.length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Candlestick Patterns ({data.candlestick_patterns.length})</h4>
              <div className="space-y-1">
                {data.candlestick_patterns.slice(0, 10).map((p, i) => (
                  <div key={i} className="flex items-center justify-between text-sm">
                    <span>{p.name}</span>
                    <span className={cn("font-mono text-xs", p.type === "bullish" ? "text-green-400" : "text-red-400")}>
                      {(p.confidence * 100).toFixed(0)}%
                    </span>
                  </div>
                ))}
              </div>
            </div>
          )}
          {data.chart_patterns.length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Chart Patterns ({data.chart_patterns.length})</h4>
              <div className="space-y-1">
                {data.chart_patterns.slice(0, 10).map((p, i) => (
                  <div key={i} className="flex items-center justify-between text-sm">
                    <span>{p.name}</span>
                    <span className="font-mono text-xs text-muted">{(p.confidence * 100).toFixed(0)}%</span>
                  </div>
                ))}
              </div>
            </div>
          )}
          {data.volume_anomalies.length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Volume Anomalies ({data.volume_anomalies.length})</h4>
              <div className="space-y-1">
                {data.volume_anomalies.slice(0, 5).map((a, i) => (
                  <div key={i} className="flex items-center justify-between text-sm">
                    <span>{a.date}</span>
                    <span className="font-mono text-xs text-yellow-400">{a.type} (z={a.z_score.toFixed(1)})</span>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}
    </Panel>
  );
}

// ==================== Risk Management ====================
export function RiskPanel({ ticker }: { ticker: string }) {
  const log = createPanelLogger("Risk");
  const [data, setData] = useState<RiskMetrics | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load", ticker);
    setLoading(true);
    setError(null);
    try {
      const result = await api.getRiskMetrics(ticker);
      setData(result);
      log.success("load", { sharpe: result.risk?.sharpe?.annualized_sharpe, max_dd: result.risk?.drawdown?.max_drawdown_pct });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load risk metrics");
    }
    setLoading(false);
  }, [ticker]);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title={`Risk Management — ${ticker}`} icon={<ShieldAlert className="h-4 w-4" />} badge={<StatusBadge status="Delayed" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-4 p-4">
          <div>
            <h4 className="mb-2 text-xs font-bold uppercase text-muted">Value at Risk (95%)</h4>
            <div className="grid grid-cols-4 gap-3">
              <Metric label="VaR" value={formatNumber(data.var.var_value)} />
              <Metric label="CVaR" value={formatNumber(data.var.cvar_value)} />
              <Metric label="Param. VaR" value={formatNumber(data.var.parametric_var_value)} />
              <Metric label="Ann. Vol" value={`${data.risk.annual_volatility_pct}%`} />
            </div>
          </div>
          <div>
            <h4 className="mb-2 text-xs font-bold uppercase text-muted">Risk-Adjusted Returns</h4>
            <div className="grid grid-cols-4 gap-3">
              <Metric label="Sharpe" value={String(data.risk.sharpe.annualized_sharpe)} />
              <Metric label="Sortino" value={String(data.risk.sortino.annualized_sortino)} />
              <Metric label="Calmar" value={String(data.risk.calmar_ratio)} />
              <Metric label="Ann. Return" value={`${data.risk.annual_return_pct}%`} />
            </div>
          </div>
          <div>
            <h4 className="mb-2 text-xs font-bold uppercase text-muted">Drawdown</h4>
            <div className="grid grid-cols-3 gap-3">
              <Metric label="Max DD" value={`${data.risk.drawdown.max_drawdown_pct}%`} />
              <Metric label="Duration" value={`${data.risk.drawdown.max_dd_duration_days}d`} />
              <Metric label="Current DD" value={`${data.risk.drawdown.current_drawdown_pct}%`} />
            </div>
          </div>
          <div>
            <h4 className="mb-2 text-xs font-bold uppercase text-muted">Kelly Criterion</h4>
            <div className="grid grid-cols-3 gap-3">
              <Metric label="Kelly %" value={`${data.risk.kelly.kelly_fraction}%`} />
              <Metric label="Win Rate" value={`${data.risk.kelly.win_rate}%`} />
              <Metric label="Profit Factor" value={String(data.risk.kelly.profit_factor)} />
            </div>
            <p className="mt-2 text-sm text-muted">{data.risk.kelly.recommendation}</p>
          </div>
        </div>
      )}
    </Panel>
  );
}

// ==================== Portfolio Optimization ====================
export function PortfolioOptPanel() {
  const log = createPanelLogger("PortfolioOpt");
  const [data, setData] = useState<PortfolioOptimization | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load");
    setLoading(true);
    setError(null);
    try {
      const result = await api.getPortfolioOptimization();
      setData(result);
      log.success("load", { max_sharpe: result.max_sharpe_portfolio?.sharpe });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load optimization");
    }
    setLoading(false);
  }, []);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title="Portfolio Optimization" icon={<Briefcase className="h-4 w-4" />} badge={<StatusBadge status="Delayed" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 4 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-4 p-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="rounded border border-green-500/20 p-3">
              <h4 className="mb-2 text-xs font-bold uppercase text-green-400">Max Sharpe Portfolio</h4>
              <div className="grid grid-cols-3 gap-2 text-sm">
                <Metric label="Return" value={`${data.max_sharpe_portfolio.return}%`} />
                <Metric label="Volatility" value={`${data.max_sharpe_portfolio.volatility}%`} />
                <Metric label="Sharpe" value={String(data.max_sharpe_portfolio.sharpe)} />
              </div>
              <div className="mt-2 space-y-1">
                {Object.entries(data.max_sharpe_portfolio.weights).sort((a, b) => b[1] - a[1]).slice(0, 5).map(([k, v]) => (
                  <div key={k} className="flex justify-between text-xs">
                    <span className="text-muted">{k}</span>
                    <span className="font-mono">{(v * 100).toFixed(1)}%</span>
                  </div>
                ))}
              </div>
            </div>
            <div className="rounded border border-blue-500/20 p-3">
              <h4 className="mb-2 text-xs font-bold uppercase text-blue-400">Min Volatility Portfolio</h4>
              <div className="grid grid-cols-3 gap-2 text-sm">
                <Metric label="Return" value={`${data.min_vol_portfolio.return}%`} />
                <Metric label="Volatility" value={`${data.min_vol_portfolio.volatility}%`} />
                <Metric label="Sharpe" value={String(data.min_vol_portfolio.sharpe)} />
              </div>
              <div className="mt-2 space-y-1">
                {Object.entries(data.min_vol_portfolio.weights).sort((a, b) => b[1] - a[1]).slice(0, 5).map(([k, v]) => (
                  <div key={k} className="flex justify-between text-xs">
                    <span className="text-muted">{k}</span>
                    <span className="font-mono">{(v * 100).toFixed(1)}%</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
          {data.efficient_frontier && data.efficient_frontier.length > 0 && (
            <div className="rounded border border-panel-border p-3">
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Efficient Frontier ({data.efficient_frontier.length} portfolios)</h4>
              <ScatterChart points={data.efficient_frontier.map(p => ({ x: p.Volatility * 100, y: p.Return * 100, c: p.Sharpe }))} />
            </div>
          )}
        </div>
      )}
    </Panel>
  );
}

// ==================== Trading Agent ====================
export function TradingAgentPanel() {
  const log = createPanelLogger("TradingAgent");
  const [portfolio, setPortfolio] = useState<PortfolioPosition[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("loadPortfolio");
    setLoading(true);
    setError(null);
    try {
      const data = await api.getPortfolio();
      setPortfolio(data);
      log.success("loadPortfolio", { positions: data.length });
    } catch (e) {
      log.error("loadPortfolio", e);
      setError(e instanceof Error ? e.message : "Failed to load portfolio");
    }
    setLoading(false);
  }, []);

  useEffect(() => { load(); }, [load]);

  const totalPnl = portfolio.reduce((sum, p) => sum + (p.unrealized_pnl || 0), 0);
  const totalPositions = portfolio.length;
  const cashValue = 100_000_000 - portfolio.reduce((sum, p) => sum + (p.quantity * p.avg_price), 0);

  return (
    <Panel title="Trading Agent" icon={<Activity className="h-4 w-4" />} badge={<StatusBadge status="Simulated" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {!loading && (
        <div className="space-y-4 p-4">
          <div className="grid grid-cols-4 gap-3">
            <Metric label="State" value="💤 IDLE" />
            <Metric label="Auto-Execute" value="OFF" />
            <Metric label="Open Positions" value={String(totalPositions)} />
            <Metric label="Virtual Cash" value={formatNumber(cashValue)} />
          </div>
          <div className="grid grid-cols-4 gap-3">
            <Metric label="Total PnL" value={formatNumber(totalPnl)} />
            <Metric label="Win Rate" value="—" />
            <Metric label="Total Runs" value="0" />
            <Metric label="Kill Switch" value="✅ OFF" />
          </div>
          <div className="rounded border border-panel-border p-3">
            <h4 className="mb-2 text-xs font-bold uppercase text-muted">Safety Guardrails</h4>
            <div className="grid grid-cols-2 gap-2 text-sm">
              <div>Max Trades/Day: <span className="font-mono">3</span></div>
              <div>Max Position: <span className="font-mono">20%</span></div>
              <div>Min Confidence: <span className="font-mono">0.65</span></div>
              <div>Daily Loss Limit: <span className="font-mono">5%</span></div>
            </div>
          </div>
          {portfolio.length > 0 && (
            <div className="rounded border border-panel-border p-3">
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Current Positions</h4>
              <div className="space-y-1">
                {portfolio.map((pos) => (
                  <div key={pos.symbol} className="flex justify-between text-xs">
                    <span className="text-muted">{pos.symbol}</span>
                    <span className="font-mono">{pos.quantity} @ {formatNumber(pos.avg_price)}</span>
                    <span className={pos.unrealized_pnl >= 0 ? "text-bull" : "text-bear"}>
                      {formatNumber(pos.unrealized_pnl)}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          )}
          <div className="flex gap-2">
            <button className="rounded bg-green-500/20 px-3 py-1.5 text-xs text-green-400 hover:bg-green-500/30">▶ Start Schedule</button>
            <button className="rounded bg-red-500/20 px-3 py-1.5 text-xs text-red-400 hover:bg-red-500/30">🚨 Kill Switch</button>
            <button className="rounded bg-accent/20 px-3 py-1.5 text-xs text-accent hover:bg-accent/30">🚀 Run Manual Cycle</button>
          </div>
          <p className="text-xs text-muted">Trading Agent runs the full prediction pipeline → decision → paper trade execution cycle. Configure guardrails and schedule for autonomous monitoring.</p>
        </div>
      )}
    </Panel>
  );
}

// ==================== Accuracy ====================
export function AccuracyPanel() {
  const log = createPanelLogger("Accuracy");
  const [data, setData] = useState<AccuracyResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load");
    setLoading(true);
    setError(null);
    try {
      const result = await api.getAccuracy();
      setData(result);
      log.success("load", { total: result.metrics?.total, dir_acc: result.metrics?.directional_accuracy });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load accuracy");
    }
    setLoading(false);
  }, []);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title="Accuracy & Verification" icon={<Target className="h-4 w-4" />} badge={<StatusBadge status="Delayed" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-4 p-4">
          <div className="grid grid-cols-4 gap-3">
            <Metric label="Total" value={String(data.metrics.total)} />
            <Metric label="Correct" value={String(data.metrics.benar)} />
            <Metric label="Wrong" value={String(data.metrics.salah)} />
            <Metric label="Dir. Accuracy" value={`${data.metrics.directional_accuracy}%`} />
          </div>
          {data.metrics.mape != null && <Metric label="MAPE" value={`${data.metrics.mape}%`} />}
          {data.recent_predictions.length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Recent Predictions</h4>
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-panel-border text-xs text-muted">
                    <th className="px-2 py-1 text-left">Date</th>
                    <th className="px-2 py-1 text-left">Ticker</th>
                    <th className="px-2 py-1 text-right">Predicted</th>
                    <th className="px-2 py-1 text-right">Actual</th>
                    <th className="px-2 py-1 text-center">Signal</th>
                    <th className="px-2 py-1 text-center">Correct</th>
                  </tr>
                </thead>
                <tbody>
                  {data.recent_predictions.slice(0, 15).map((p, i) => (
                    <tr key={i} className="border-b border-panel-border/30">
                      <td className="px-2 py-1 text-xs">{p.date}</td>
                      <td className="px-2 py-1 font-mono text-xs">{p.ticker}</td>
                      <td className="px-2 py-1 text-right font-mono text-xs">{formatNumber(p.predicted)}</td>
                      <td className="px-2 py-1 text-right font-mono text-xs">{p.actual ? formatNumber(p.actual) : "—"}</td>
                      <td className="px-2 py-1 text-center text-xs">{p.signal}</td>
                      <td className={cn("px-2 py-1 text-center text-xs", p.correct ? "text-green-400" : "text-red-400")}>{p.correct ? "✓" : "✗"}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}
    </Panel>
  );
}

// ==================== Model Details ====================
export function ModelDetailsPanel({ ticker }: { ticker: string }) {
  const log = createPanelLogger("ModelDetails");
  const [data, setData] = useState<ModelDetails | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load", ticker);
    setLoading(true);
    setError(null);
    try {
      const result = await api.getModelDetails(ticker);
      setData(result);
      log.success("load", { models: Object.keys(result.predictions || {}) });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load model details");
    }
    setLoading(false);
  }, [ticker]);

  return (
    <Panel
      title={`Model Details — ${ticker}`}
      icon={<Cpu className="h-4 w-4" />}
      badge={<StatusBadge status="Simulated" />}
      actions={
        <button onClick={load} disabled={loading} className="flex items-center gap-1 rounded bg-accent/20 px-2 py-1 text-xs text-accent hover:bg-accent/30 disabled:opacity-50">
          <Play className="h-3 w-3" /> {loading ? "Running..." : "Load Details"}
        </button>
      }
    >
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {!data && !loading && !error && <div className="p-8 text-center text-sm text-muted">Click Load Details to run prediction with full model info</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 4 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-4 p-4">
          <div className="grid grid-cols-3 gap-3">
            <Metric label="Regime" value={data.market_regime} />
            <Metric label="Regime Adjusted" value={data.regime_adjusted ? "Yes" : "No"} />
            <Metric label="Ensemble" value={data.ensemble_method || "N/A"} />
          </div>
          {Object.keys(data.predictions).length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Model Votes</h4>
              <div className="space-y-1">
                {Object.entries(data.predictions).map(([model, vote]) => (
                  <div key={model} className="flex items-center justify-between text-sm">
                    <span className="font-mono text-xs">{model}</span>
                    <span className={cn("font-bold", vote === 1 ? "text-green-400" : "text-red-400")}>{vote === 1 ? "BUY" : "SELL"}</span>
                  </div>
                ))}
              </div>
            </div>
          )}
          {Object.keys(data.probabilities).length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Probabilities</h4>
              <div className="space-y-1">
                {Object.entries(data.probabilities).map(([model, prob]) => (
                  <div key={model} className="flex items-center justify-between text-sm">
                    <span className="font-mono text-xs">{model}</span>
                    <div className="flex items-center gap-2">
                      <div className="h-1.5 w-20 rounded-full bg-panel-border">
                        <div className="h-1.5 rounded-full bg-accent" style={{ width: `${(prob as number) * 100}%` }} />
                      </div>
                      <span className="font-mono text-xs text-muted">{(prob as number).toFixed(2)}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
          {data.rules && <Section title="Rules" text={data.rules} />}
          {Object.keys(data.shap_explanations).length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">SHAP Explanations</h4>
              <pre className="overflow-auto rounded border border-panel-border p-2 text-xs text-muted">{JSON.stringify(data.shap_explanations, null, 2)}</pre>
            </div>
          )}
          {Object.keys(data.feature_importance).length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Feature Importance</h4>
              <pre className="overflow-auto rounded border border-panel-border p-2 text-xs text-muted">{JSON.stringify(data.feature_importance, null, 2)}</pre>
            </div>
          )}
        </div>
      )}
    </Panel>
  );
}

// ==================== Regime ====================
export function RegimePanel({ ticker }: { ticker: string }) {
  const log = createPanelLogger("Regime");
  const [data, setData] = useState<RegimeResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load", ticker);
    setLoading(true);
    setError(null);
    try {
      const result = await api.getRegime(ticker);
      setData(result);
      log.success("load", { regime: result.regime || result.current_regime });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load regime");
    }
    setLoading(false);
  }, [ticker]);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title={`Market Regime — ${ticker}`} icon={<Activity className="h-4 w-4" />} badge={<StatusBadge status="Delayed" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 2 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-3 p-4">
          <div className="rounded border border-panel-border p-3 text-center">
            <div className="text-2xl font-bold text-accent">{data.regime || data.current_regime || "Unknown"}</div>
            <p className="mt-1 text-sm text-muted">{data.description || ""}</p>
          </div>
          <pre className="overflow-auto rounded border border-panel-border p-2 text-xs text-muted">{JSON.stringify(data, null, 2)}</pre>
        </div>
      )}
    </Panel>
  );
}

// ==================== Intermarket ====================
export function IntermarketPanel() {
  const log = createPanelLogger("Intermarket");
  const [data, setData] = useState<IntermarketResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load");
    setLoading(true);
    setError(null);
    try {
      const result = await api.getIntermarket();
      setData(result);
      const corrKeys = result.correlation ? Object.keys(result.correlation) : [];
      log.success("load", { corr_markets: corrKeys.length });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load intermarket");
    }
    setLoading(false);
  }, []);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title="Intermarket Analysis" icon={<Globe className="h-4 w-4" />} badge={<StatusBadge status="Delayed" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-4 p-4">
          {data.correlation && Object.keys(data.correlation).length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Correlation Matrix</h4>
              <CorrelationMatrix data={data.correlation} />
            </div>
          )}
          {data.summary && Object.keys(data.summary).length > 0 && (
            <div>
              <h4 className="mb-2 text-xs font-bold uppercase text-muted">Summary</h4>
              <pre className="overflow-auto rounded border border-panel-border p-2 text-xs text-muted">{JSON.stringify(data.summary, null, 2)}</pre>
            </div>
          )}
        </div>
      )}
    </Panel>
  );
}

// ==================== Options ====================
export function OptionsPanel({ ticker }: { ticker: string }) {
  const log = createPanelLogger("Options");
  const [data, setData] = useState<OptionsResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load", ticker);
    setLoading(true);
    setError(null);
    try {
      const result = await api.getOptions(ticker);
      setData(result);
      log.success("load", { keys: Object.keys(result) });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load options");
    }
    setLoading(false);
  }, [ticker]);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title={`Options Analysis — ${ticker}`} icon={<Layers className="h-4 w-4" />} badge={<StatusBadge status="Simulated" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="space-y-3 p-4">
          <pre className="overflow-auto rounded border border-panel-border p-2 text-xs text-muted">{JSON.stringify(data, null, 2)}</pre>
        </div>
      )}
    </Panel>
  );
}

// ==================== Data Inventory ====================
export function DataInventoryPanel() {
  const log = createPanelLogger("DataInventory");
  const [data, setData] = useState<DataInventoryItem[] | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load");
    setLoading(true);
    setError(null);
    try {
      const result = await api.getDataInventory();
      setData(result);
      log.success("load", { items: result.length });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load data inventory");
    }
    setLoading(false);
  }, []);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title="Data Inventory" icon={<Database className="h-4 w-4" />} badge={<StatusBadge status="Delayed" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="p-4">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-panel-border text-xs text-muted">
                <th className="px-2 py-1 text-left">Table</th>
                <th className="px-2 py-1 text-right">Rows</th>
                <th className="px-2 py-1 text-left">Columns</th>
              </tr>
            </thead>
            <tbody>
              {data.map((item) => (
                <tr key={item.table} className="border-b border-panel-border/30">
                  <td className="px-2 py-1 font-mono text-xs">{item.table}</td>
                  <td className="px-2 py-1 text-right font-mono text-xs">{item.rows.toLocaleString()}</td>
                  <td className="px-2 py-1 text-xs text-muted">{item.columns.join(", ")}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </Panel>
  );
}

// ==================== System Check ====================
export function SystemCheckPanel() {
  const log = createPanelLogger("SystemCheck");
  const [data, setData] = useState<SystemCheckResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    log.start("load");
    setLoading(true);
    setError(null);
    try {
      const result = await api.getSystemCheck();
      setData(result);
      log.success("load", { keys: Object.keys(result) });
    } catch (e) {
      log.error("load", e);
      setError(e instanceof Error ? e.message : "Failed to load system check");
    }
    setLoading(false);
  }, []);

  useEffect(() => { load(); }, [load]);

  return (
    <Panel title="System Check" icon={<Stethoscope className="h-4 w-4" />} badge={<StatusBadge status="Real-time" />}>
      {error && <div className="p-4 text-sm text-red-400">{error}</div>}
      {loading && <div className="space-y-2 p-4">{Array.from({ length: 3 }).map((_, i) => <SkeletonRow key={i} />)}</div>}
      {data && (
        <div className="p-4">
          <pre className="overflow-auto rounded border border-panel-border p-2 text-xs text-muted">{JSON.stringify(data, null, 2)}</pre>
        </div>
      )}
    </Panel>
  );
}

// ==================== Shared UI helpers ====================
function Metric({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded border border-panel-border/50 bg-panel/30 p-2">
      <div className="text-[10px] uppercase text-muted">{label}</div>
      <div className="mt-0.5 font-mono text-sm font-bold text-foreground">{value}</div>
    </div>
  );
}

function Section({ title, text }: { title: string; text: string }) {
  return (
    <div>
      <h4 className="mb-1 text-xs font-bold uppercase text-muted">{title}</h4>
      <p className="text-sm text-foreground/80">{text}</p>
    </div>
  );
}

function SkeletonRow() {
  return <div className="h-4 animate-pulse rounded bg-panel-border/40" />;
}

function MiniChart({ values }: { values: number[] }) {
  if (values.length < 2) return null;
  const min = Math.min(...values);
  const max = Math.max(...values);
  const range = max - min || 1;
  const points = values.map((v, i) => `${(i / (values.length - 1)) * 100},${100 - ((v - min) / range) * 100}`).join(" ");
  return (
    <svg viewBox="0 0 100 100" className="h-24 w-full" preserveAspectRatio="none">
      <polyline points={points} fill="none" stroke="rgb(34, 211, 238)" strokeWidth="0.5" />
    </svg>
  );
}

function ScatterChart({ points }: { points: { x: number; y: number; c: number }[] }) {
  if (points.length === 0) return null;
  const xs = points.map(p => p.x);
  const ys = points.map(p => p.y);
  const minX = Math.min(...xs), maxX = Math.max(...xs);
  const minY = Math.min(...ys), maxY = Math.max(...ys);
  const rangeX = maxX - minX || 1;
  const rangeY = maxY - minY || 1;
  return (
    <svg viewBox="0 0 100 100" className="h-32 w-full" preserveAspectRatio="none">
      {points.map((p, i) => {
        const cx = ((p.x - minX) / rangeX) * 100;
        const cy = 100 - ((p.y - minY) / rangeY) * 100;
        const opacity = 0.3 + (p.c / Math.max(...points.map(pp => pp.c))) * 0.7;
        return <circle key={i} cx={cx} cy={cy} r="0.8" fill="rgb(34, 211, 238)" opacity={opacity} />;
      })}
    </svg>
  );
}

function CorrelationMatrix({ data }: { data: Record<string, Record<string, number>> }) {
  const keys = Object.keys(data);
  if (keys.length === 0) return null;
  return (
    <div className="overflow-auto">
      <table className="text-xs">
        <thead>
          <tr>
            <th className="px-1 py-0.5"></th>
            {keys.map(k => <th key={k} className="px-1 py-0.5 text-muted">{k.slice(0, 6)}</th>)}
          </tr>
        </thead>
        <tbody>
          {keys.map(row => (
            <tr key={row}>
              <td className="px-1 py-0.5 font-mono text-muted">{row.slice(0, 6)}</td>
              {keys.map(col => {
                const val = data[row]?.[col] ?? 0;
                const color = val > 0.5 ? "bg-green-500/30" : val < -0.5 ? "bg-red-500/30" : "bg-panel-border/20";
                return <td key={col} className={cn("px-1 py-0.5 text-center font-mono", color)}>{val.toFixed(2)}</td>;
              })}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
