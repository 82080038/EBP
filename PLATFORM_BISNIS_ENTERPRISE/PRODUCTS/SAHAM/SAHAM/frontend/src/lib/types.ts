export interface OHLCV {
  date: string;
  open: number;
  high: number;
  low: number;
  close: number;
  volume: number;
}

export interface WatchlistItem {
  ticker: string;
  name: string;
  price: number;
  change_pct: number;
  rsi: number;
  ma_trend: string;
  signal: "BUY" | "SELL" | "HOLD";
  ai_score: number;
  confidence: number;
}

export interface OrderBookLevel {
  level: number;
  bid_qty: number;
  bid: number;
  ask: number;
  ask_qty: number;
  spread_pct: number;
}

export interface PredictionResult {
  ticker: string;
  current_price: number;
  predicted_price: number;
  arah_prediksi: string;
  sinyal: "BUY" | "SELL" | "HOLD";
  confidence: number;
  ai_score: number;
  market_regime: string;
  entry: number;
  stop_loss: number;
  target_1: number;
  target_2: number;
  target_3: number;
  risk_reward: number;
  position_shares: number;
  shap_explanations: Record<string, {
    method: string;
    top_features: [string, number][];
    summary: string;
  }>;
}

export interface IndexSummary {
  name: string;
  price: number;
  change_pct: number;
}

export interface MacroIndicator {
  name: string;
  value: number;
  date: string;
}

export interface SimOrderResult {
  order_id: string;
  status: string;
  filled_qty: number;
  avg_fill_price: number;
  commission: number;
  fees: number;
  total_cost: number;
  slippage_bps: number;
  latency_ms: number;
}

export interface PortfolioPosition {
  symbol: string;
  quantity: number;
  avg_price: number;
  market_price: number;
  unrealized_pnl: number;
}

// ==================== NEW TYPES ====================

export interface BacktestResult {
  backtest: {
    directional_accuracy: number;
    total_predictions: number;
    MAPE?: number;
  };
  simulation: {
    initial_capital: number;
    final_capital: number;
    total_return: number;
    buy_hold_return: number;
    trades: number;
    max_drawdown: number;
    portfolio_values?: number[];
  };
}

export interface RiskMetrics {
  risk: {
    annual_volatility_pct: number;
    annual_return_pct: number;
    sharpe: { annualized_sharpe: number };
    sortino: { annualized_sortino: number };
    calmar_ratio: number;
    drawdown: {
      max_drawdown_pct: number;
      max_dd_duration_days: number;
      current_drawdown_pct: number;
    };
    kelly: {
      kelly_fraction: number;
      win_rate: number;
      profit_factor: number;
      recommendation: string;
    };
  };
  var: {
    var_value: number;
    var_percent: number;
    cvar_value: number;
    cvar_percent: number;
    parametric_var_value: number;
    parametric_var_percent: number;
  };
}

export interface PortfolioOptimization {
  efficient_frontier: { Volatility: number; Return: number; Sharpe: number }[];
  max_sharpe_portfolio: {
    return: number;
    volatility: number;
    sharpe: number;
    weights: Record<string, number>;
  };
  min_vol_portfolio: {
    return: number;
    volatility: number;
    sharpe: number;
    weights: Record<string, number>;
  };
  asset_stats: Record<string, {
    annual_return: number;
    annual_volatility: number;
    sharpe: number;
  }>;
}

export interface RegimeResult {
  regime?: string;
  current_regime?: string;
  description?: string;
  confidence?: number;
  [key: string]: unknown;
}

export interface IntermarketResult {
  correlation: Record<string, Record<string, number>>;
  summary: Record<string, unknown>;
}

export interface PatternResult {
  candlestick_patterns: { name: string; type: string; confidence: number; date: string }[];
  chart_patterns: { name: string; type: string; confidence: number; description: string }[];
  market_structure: { structure: string; description: string };
  volume_anomalies: { date: string; type: string; z_score: number }[];
  trendlines: { type: string; slope: number; touches: number }[];
  summary: string;
}

export interface OptionsResult {
  [key: string]: unknown;
}

export interface SystemCheckResult {
  [key: string]: unknown;
}

export interface DataInventoryItem {
  table: string;
  rows: number;
  columns: string[];
}

export interface AccuracyResult {
  metrics: {
    total: number;
    benar: number;
    salah: number;
    directional_accuracy: number;
    mape?: number;
  };
  recent_predictions: {
    date: string;
    ticker: string;
    predicted: number;
    actual: number | null;
    correct: boolean;
    signal: string;
  }[];
}

export interface ModelDetails {
  predictions: Record<string, number>;
  probabilities: Record<string, number>;
  model_votes: string;
  rules: string;
  shap_explanations: Record<string, unknown>;
  feature_importance: Record<string, unknown>;
  ensemble_method: string;
  training_scores: Record<string, unknown>;
  market_regime: string;
  regime_adjusted: boolean;
  risk_governance: Record<string, unknown>;
  advanced_analysis: Record<string, unknown>;
}

export interface SentimentResult {
  composite_score: number;
  label: string;
  emoji?: string;
  components: Record<string, {
    score: number;
    value: number | string;
    interpretation: string;
  }>;
  advice?: string;
}

export interface BriefingResult {
  date: string;
  market_summary: string;
  signal: string;
  confidence: number;
  final_recommendation: string;
  actionable_items: string[];
  risk_assessment: string;
  bull_case?: string;
  bear_case?: string;
  debate?: Record<string, unknown>;
}

export interface ScoreResult {
  ai_score: number;
  composite_score: number;
  technical_rating: number;
  sentiment_rating: number;
  momentum_rating: number;
  risk_rating: number;
  signal_strength: string;
}

export type TabId =
  | "command-center"
  | "ai-briefing"
  | "backtesting"
  | "sentiment"
  | "patterns"
  | "risk"
  | "portfolio-opt"
  | "trading-agent"
  | "accuracy"
  | "model-details"
  | "regime"
  | "intermarket"
  | "options"
  | "data-inventory"
  | "system-check";
