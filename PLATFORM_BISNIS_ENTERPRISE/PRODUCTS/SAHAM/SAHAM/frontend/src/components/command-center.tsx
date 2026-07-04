"use client";

import { useCallback, useEffect, useState } from "react";
import { TopBar } from "@/components/top-bar";
import { TabBar } from "@/components/tab-bar";
import { Watchlist } from "@/components/panels/watchlist";
import { ChartPanel } from "@/components/panels/chart-panel";
import { OrderBook } from "@/components/panels/order-book";
import { DecisionStrip } from "@/components/panels/decision-strip";
import { PortfolioPanel } from "@/components/panels/portfolio";
import { MarketSummary } from "@/components/panels/market-summary";
import { MacroCalendar } from "@/components/panels/macro-calendar";
import {
  AIBriefingPanel,
  BacktestingPanel,
  SentimentPanel,
  PatternsPanel,
  RiskPanel,
  PortfolioOptPanel,
  TradingAgentPanel,
  AccuracyPanel,
  ModelDetailsPanel,
  RegimePanel,
  IntermarketPanel,
  OptionsPanel,
  DataInventoryPanel,
  SystemCheckPanel,
} from "@/components/panels/new-panels";
import { api } from "@/lib/api";
import { createPanelLogger } from "@/lib/panel-logger";
import type {
  OHLCV,
  WatchlistItem,
  OrderBookLevel,
  PredictionResult,
  ScoreResult,
  IndexSummary,
  MacroIndicator,
  SimOrderResult,
  PortfolioPosition,
  TabId,
} from "@/lib/types";

const TICKERS: Record<string, string> = {
  BBCA: "BBCA.JK",
  BBRI: "BBRI.JK",
  TLKM: "TLKM.JK",
  ASII: "ASII.JK",
  GOTO: "GOTO.JK",
  BMRI: "BMRI.JK",
  UNVR: "UNVR.JK",
  KLBF: "KLBF.JK",
  INCO: "INCO.JK",
  ADRO: "ADRO.JK",
};

export function CommandCenter() {
  const log = createPanelLogger("CommandCenter");
  const [selectedName, setSelectedName] = useState("BBCA");
  const [activeTab, setActiveTab] = useState<TabId>("command-center");
  const selectedTicker = TICKERS[selectedName] || "BBCA.JK";

  // Data states
  const [ohlcv, setOHLCV] = useState<OHLCV[]>([]);
  const [ohlcvLoading, setOHLCVLoading] = useState(true);
  const [watchlist, setWatchlist] = useState<WatchlistItem[]>([]);
  const [watchlistLoading, setWatchlistLoading] = useState(true);
  const [orderBook, setOrderBook] = useState<OrderBookLevel[]>([]);
  const [orderBookLoading, setOrderBookLoading] = useState(true);
  const [prediction, setPrediction] = useState<PredictionResult | null>(null);
  const [score, setScore] = useState<ScoreResult | null>(null);
  const [predictionLoading, setPredictionLoading] = useState(false);
  const [indices, setIndices] = useState<IndexSummary[]>([]);
  const [indicesLoading, setIndicesLoading] = useState(true);
  const [macro, setMacro] = useState<MacroIndicator[]>([]);
  const [macroLoading, setMacroLoading] = useState(true);
  const [portfolio, setPortfolio] = useState<PortfolioPosition[]>([]);
  const [portfolioLoading, setPortfolioLoading] = useState(true);
  const [orderResult, setOrderResult] = useState<SimOrderResult | null>(null);
  const [orderLoading, setOrderLoading] = useState(false);

  // Load OHLCV when ticker changes
  const loadOHLCV = useCallback(async () => {
    log.start("loadOHLCV", selectedTicker);
    setOHLCVLoading(true);
    try {
      const data = await api.getOHLCV(selectedTicker);
      setOHLCV(data);
      log.success("loadOHLCV", { candles: data.length });
    } catch (e) {
      log.error("loadOHLCV", e);
      setOHLCV([]);
    } finally {
      setOHLCVLoading(false);
    }
  }, [selectedTicker]);

  // Load watchlist
  const loadWatchlist = useCallback(async () => {
    log.start("loadWatchlist");
    setWatchlistLoading(true);
    try {
      const data = await api.getWatchlist();
      setWatchlist(data);
      log.success("loadWatchlist", { items: data.length });
    } catch (e) {
      log.error("loadWatchlist", e);
      setWatchlist([]);
    } finally {
      setWatchlistLoading(false);
    }
  }, []);

  // Load order book
  const loadOrderBook = useCallback(async () => {
    setOrderBookLoading(true);
    try {
      const data = await api.getOrderBook(selectedTicker);
      setOrderBook(data);
    } catch (e) {
      log.error("loadOrderBook", e);
      setOrderBook([]);
    } finally {
      setOrderBookLoading(false);
    }
  }, [selectedTicker, log]);

  // Load market summary
  const loadIndices = useCallback(async () => {
    setIndicesLoading(true);
    try {
      const data = await api.getMarketSummary();
      setIndices(data);
    } catch (e) {
      log.error("loadIndices", e);
      setIndices([]);
    } finally {
      setIndicesLoading(false);
    }
  }, [log]);

  // Load macro
  const loadMacro = useCallback(async () => {
    setMacroLoading(true);
    try {
      const data = await api.getMacro();
      setMacro(data);
    } catch (e) {
      log.error("loadMacro", e);
      setMacro([]);
    } finally {
      setMacroLoading(false);
    }
  }, [log]);

  // Load portfolio
  const loadPortfolio = useCallback(async () => {
    setPortfolioLoading(true);
    try {
      const data = await api.getPortfolio();
      setPortfolio(data);
    } catch (e) {
      log.error("loadPortfolio", e);
      setPortfolio([]);
    } finally {
      setPortfolioLoading(false);
    }
  }, [log]);

  // Initial load
  useEffect(() => {
    loadWatchlist();
    loadIndices();
    loadMacro();
    loadPortfolio();
  }, [loadWatchlist, loadIndices, loadMacro, loadPortfolio]);

  // Load when ticker changes
  useEffect(() => {
    loadOHLCV();
    loadOrderBook();
    setPrediction(null);
  }, [loadOHLCV, loadOrderBook]);

  const handleRunPrediction = async () => {
    log.start("runPrediction", selectedTicker);
    setPredictionLoading(true);
    try {
      const result = await api.runPrediction(selectedTicker);
      setPrediction(result);
      log.success("runPrediction", { signal: result.sinyal, confidence: result.confidence });
      api.getFullScore(selectedTicker).then(s => {
        setScore(s);
        log.success("getFullScore", { ai_score: s.ai_score, strength: s.signal_strength });
      }).catch(e => log.error("getFullScore", e));
    } catch (e) {
      log.error("runPrediction", e);
      setPrediction(null);
      setScore(null);
    } finally {
      setPredictionLoading(false);
    }
  };

  const handleSubmitOrder = async (side: "BUY" | "SELL", quantity: number) => {
    log.start("submitOrder", `${side} ${quantity} ${selectedTicker}`);
    setOrderLoading(true);
    try {
      const result = await api.submitSimOrder(selectedTicker, side, quantity);
      setOrderResult(result);
      log.success("submitOrder", { status: result.status, fill: result.avg_fill_price });
      loadPortfolio();
    } catch (e) {
      log.error("submitOrder", e);
      setOrderResult(null);
    } finally {
      setOrderLoading(false);
    }
  };

  const handleRefresh = () => {
    loadOHLCV();
    loadWatchlist();
    loadOrderBook();
    loadIndices();
    loadMacro();
    loadPortfolio();
  };

  return (
    <div className="flex min-h-screen flex-col gap-2 p-2">
      {/* Top Bar */}
      <TopBar
        tickers={TICKERS}
        selectedName={selectedName}
        onSelectTicker={setSelectedName}
        indices={indices}
        onRefresh={handleRefresh}
      />

      {/* Tab Navigation */}
      <TabBar activeTab={activeTab} onTabChange={setActiveTab} />

      {/* Tab Content */}
      {activeTab === "command-center" && (
        <>
          {/* Main Grid: Watchlist | Chart | Order Book */}
          <div className="grid grid-cols-12 gap-2" style={{ minHeight: "520px" }}>
            <div className="col-span-3">
              <Watchlist
                items={watchlist}
                loading={watchlistLoading}
                onSelect={(ticker) => {
                  const entry = Object.entries(TICKERS).find(([, t]) => t === ticker);
                  if (entry) setSelectedName(entry[0]);
                }}
                selectedTicker={selectedTicker}
              />
            </div>
            <div className="col-span-6">
              <ChartPanel
                ticker={selectedTicker}
                name={selectedName}
                data={ohlcv}
                loading={ohlcvLoading}
                prediction={prediction}
                predictionLoading={predictionLoading}
                onRunPrediction={handleRunPrediction}
              />
            </div>
            <div className="col-span-3">
              <OrderBook
                data={orderBook}
                loading={orderBookLoading}
                ticker={selectedTicker}
                onSubmitOrder={handleSubmitOrder}
                orderResult={orderResult}
                orderLoading={orderLoading}
              />
            </div>
          </div>

          {/* Decision Strip */}
          <DecisionStrip prediction={prediction} score={score} loading={predictionLoading} />

          {/* Bottom Panel: Portfolio | Market Summary | Macro */}
          <div className="grid grid-cols-12 gap-2" style={{ minHeight: "200px" }}>
            <div className="col-span-4">
              <PortfolioPanel positions={portfolio} loading={portfolioLoading} />
            </div>
            <div className="col-span-4">
              <MarketSummary indices={indices} loading={indicesLoading} />
            </div>
            <div className="col-span-4">
              <MacroCalendar indicators={macro} loading={macroLoading} />
            </div>
          </div>
        </>
      )}

      {activeTab === "ai-briefing" && <AIBriefingPanel />}
      {activeTab === "backtesting" && <BacktestingPanel ticker={selectedTicker} />}
      {activeTab === "sentiment" && <SentimentPanel />}
      {activeTab === "patterns" && <PatternsPanel ticker={selectedTicker} />}
      {activeTab === "risk" && <RiskPanel ticker={selectedTicker} />}
      {activeTab === "portfolio-opt" && <PortfolioOptPanel />}
      {activeTab === "trading-agent" && <TradingAgentPanel />}
      {activeTab === "accuracy" && <AccuracyPanel />}
      {activeTab === "model-details" && <ModelDetailsPanel ticker={selectedTicker} />}
      {activeTab === "regime" && <RegimePanel ticker={selectedTicker} />}
      {activeTab === "intermarket" && <IntermarketPanel />}
      {activeTab === "options" && <OptionsPanel ticker={selectedTicker} />}
      {activeTab === "data-inventory" && <DataInventoryPanel />}
      {activeTab === "system-check" && <SystemCheckPanel />}

      {/* Footer */}
      <div className="px-2 py-1 text-xs text-muted">
        Flow: <span className="font-medium text-foreground">yfinance historical data</span> →{" "}
        feature engineering → ensemble ML prediction → AI score + risk → simulated trade →
        portfolio result | Data source: yfinance (delayed, for testing only)
      </div>
    </div>
  );
}
