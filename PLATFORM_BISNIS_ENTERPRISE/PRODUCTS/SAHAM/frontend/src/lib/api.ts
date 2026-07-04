import type {
  OHLCV,
  WatchlistItem,
  OrderBookLevel,
  PredictionResult,
  IndexSummary,
  MacroIndicator,
  SimOrderResult,
  PortfolioPosition,
  BacktestResult,
  RiskMetrics,
  PortfolioOptimization,
  RegimeResult,
  IntermarketResult,
  PatternResult,
  OptionsResult,
  SystemCheckResult,
  DataInventoryItem,
  AccuracyResult,
  ModelDetails,
  SentimentResult,
  BriefingResult,
  ScoreResult,
} from "./types";

const API_BASE = process.env.NEXT_PUBLIC_API_BASE || "http://localhost:8000/api/v1";

const LOG_PREFIX = "[API]";
const isDev = process.env.NODE_ENV === "development";

async function fetchJSON<T>(url: string, options?: RequestInit): Promise<T> {
  const start = performance.now();
  const method = options?.method || "GET";
  const shortUrl = url.replace(API_BASE, "");

  if (isDev) console.log(`${LOG_PREFIX} → ${method} ${shortUrl}`);

  let res: Response;
  try {
    res = await fetch(url, {
      ...options,
      headers: { "Content-Type": "application/json", ...options?.headers },
    });
  } catch (err) {
    const duration = (performance.now() - start).toFixed(0);
    console.error(`${LOG_PREFIX} ✖ ${method} ${shortUrl} NETWORK ERROR (${duration}ms)`, err);
    throw new Error(`Network error: ${err instanceof Error ? err.message : "unknown"}`);
  }

  const duration = (performance.now() - start).toFixed(0);

  if (!res.ok) {
    let detail = "";
    try {
      const body = await res.json();
      detail = body.detail || body.message || JSON.stringify(body);
    } catch {
      detail = res.statusText;
    }
    console.error(`${LOG_PREFIX} ✖ ${method} ${shortUrl} ${res.status} (${duration}ms) — ${detail}`);
    throw new Error(`API ${res.status}: ${detail}`);
  }

  let data: T;
  try {
    data = await res.json() as T;
  } catch (err) {
    console.error(`${LOG_PREFIX} ✖ ${method} ${shortUrl} JSON PARSE ERROR (${duration}ms)`, err);
    throw new Error(`Invalid JSON response from ${shortUrl}`);
  }

  if (isDev) {
    const dataKeys = typeof data === "object" && data !== null
      ? Object.keys(data).slice(0, 5).join(",")
      : Array.isArray(data) ? `array[${data.length}]` : typeof data;
    console.log(`${LOG_PREFIX} ← ${method} ${shortUrl} 200 (${duration}ms) keys={${dataKeys}}`);
  }

  return data;
}

export const api = {
  // === Existing endpoints ===
  async getHealth(): Promise<{ status: string; version: string }> {
    return fetchJSON(`${API_BASE}/health`);
  },

  async getOHLCV(ticker: string, period = "1y"): Promise<OHLCV[]> {
    return fetchJSON(`${API_BASE}/stock/${ticker}/ohlcv?period=${period}`);
  },

  async getWatchlist(): Promise<WatchlistItem[]> {
    return fetchJSON(`${API_BASE}/screener/watchlist`);
  },

  async getOrderBook(ticker: string): Promise<OrderBookLevel[]> {
    return fetchJSON(`${API_BASE}/stock/${ticker}/orderbook`);
  },

  async runPrediction(ticker: string): Promise<PredictionResult> {
    return fetchJSON(`${API_BASE}/predict/${ticker}`, { method: "POST" });
  },

  async getMarketSummary(): Promise<IndexSummary[]> {
    return fetchJSON(`${API_BASE}/market/summary`);
  },

  async getMacro(): Promise<MacroIndicator[]> {
    return fetchJSON(`${API_BASE}/macro/indicators`);
  },

  async submitSimOrder(
    ticker: string,
    side: "BUY" | "SELL",
    quantity: number,
  ): Promise<SimOrderResult> {
    return fetchJSON(`${API_BASE}/sim/order`, {
      method: "POST",
      body: JSON.stringify({ symbol: ticker, side, quantity }),
    });
  },

  async getPortfolio(): Promise<PortfolioPosition[]> {
    return fetchJSON(`${API_BASE}/sim/portfolio`);
  },

  // === New endpoints (full feature coverage) ===
  async getBacktest(ticker: string, period = "2y"): Promise<BacktestResult> {
    return fetchJSON(`${API_BASE}/backtest/${ticker}?period=${period}`);
  },

  async getRiskMetrics(ticker: string, period = "2y", positionValue = 100_000_000): Promise<RiskMetrics> {
    return fetchJSON(`${API_BASE}/risk/${ticker}?period=${period}&position_value=${positionValue}`);
  },

  async getPortfolioOptimization(period = "2y", nSim = 3000, riskFree = 0.05): Promise<PortfolioOptimization> {
    return fetchJSON(`${API_BASE}/portfolio/optimize?period=${period}&n_sim=${nSim}&risk_free=${riskFree}`);
  },

  async getRegime(ticker: string, period = "2y"): Promise<RegimeResult> {
    return fetchJSON(`${API_BASE}/regime/${ticker}?period=${period}`);
  },

  async getIntermarket(period = "1y"): Promise<IntermarketResult> {
    return fetchJSON(`${API_BASE}/intermarket?period=${period}`);
  },

  async getFullPatterns(ticker: string, period = "6mo"): Promise<PatternResult> {
    return fetchJSON(`${API_BASE}/patterns/${ticker}/full?period=${period}`);
  },

  async getOptions(ticker: string): Promise<OptionsResult> {
    return fetchJSON(`${API_BASE}/options/${ticker}`);
  },

  async getSystemCheck(): Promise<SystemCheckResult> {
    return fetchJSON(`${API_BASE}/system/check`);
  },

  async getDataInventory(): Promise<DataInventoryItem[]> {
    return fetchJSON(`${API_BASE}/data/inventory`);
  },

  async getAccuracy(): Promise<AccuracyResult> {
    return fetchJSON(`${API_BASE}/accuracy/full`);
  },

  async getModelDetails(ticker: string): Promise<ModelDetails> {
    return fetchJSON(`${API_BASE}/model/details/${ticker}`);
  },

  async getFullSentiment(): Promise<SentimentResult> {
    return fetchJSON(`${API_BASE}/sentiment/full`);
  },

  async getFullBriefing(): Promise<BriefingResult> {
    return fetchJSON(`${API_BASE}/briefing/full`);
  },

  async getFullScore(ticker: string): Promise<ScoreResult> {
    return fetchJSON(`${API_BASE}/score/${ticker}/full`);
  },
};
