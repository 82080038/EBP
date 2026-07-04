import { create } from "zustand";
import type {
  OHLCV,
  WatchlistItem,
  OrderBookLevel,
  PredictionResult,
  IndexSummary,
  MacroIndicator,
  PortfolioPosition,
  SimOrderResult,
} from "./types";

interface CommandCenterState {
  selectedTicker: string;
  selectedName: string;
  setSelectedTicker: (ticker: string, name: string) => void;

  ohlcv: OHLCV[];
  ohlcvLoading: boolean;
  setOHLCV: (data: OHLCV[]) => void;
  setOHLCVLoading: (v: boolean) => void;

  watchlist: WatchlistItem[];
  watchlistLoading: boolean;
  setWatchlist: (data: WatchlistItem[]) => void;
  setWatchlistLoading: (v: boolean) => void;

  orderBook: OrderBookLevel[];
  orderBookLoading: boolean;
  setOrderBook: (data: OrderBookLevel[]) => void;
  setOrderBookLoading: (v: boolean) => void;

  prediction: PredictionResult | null;
  predictionLoading: boolean;
  setPrediction: (data: PredictionResult | null) => void;
  setPredictionLoading: (v: boolean) => void;

  marketSummary: IndexSummary[];
  setMarketSummary: (data: IndexSummary[]) => void;

  macro: MacroIndicator[];
  setMacro: (data: MacroIndicator[]) => void;

  portfolio: PortfolioPosition[];
  setPortfolio: (data: PortfolioPosition[]) => void;

  orderResult: SimOrderResult | null;
  orderLoading: boolean;
  setOrderResult: (data: SimOrderResult | null) => void;
  setOrderLoading: (v: boolean) => void;
}

export const useStore = create<CommandCenterState>((set) => ({
  selectedTicker: "BBCA.JK",
  selectedName: "BBCA",
  setSelectedTicker: (ticker, name) =>
    set({ selectedTicker: ticker, selectedName: name }),

  ohlcv: [],
  ohlcvLoading: false,
  setOHLCV: (data) => set({ ohlcv: data }),
  setOHLCVLoading: (v) => set({ ohlcvLoading: v }),

  watchlist: [],
  watchlistLoading: false,
  setWatchlist: (data) => set({ watchlist: data }),
  setWatchlistLoading: (v) => set({ watchlistLoading: v }),

  orderBook: [],
  orderBookLoading: false,
  setOrderBook: (data) => set({ orderBook: data }),
  setOrderBookLoading: (v) => set({ orderBookLoading: v }),

  prediction: null,
  predictionLoading: false,
  setPrediction: (data) => set({ prediction: data }),
  setPredictionLoading: (v) => set({ predictionLoading: v }),

  marketSummary: [],
  setMarketSummary: (data) => set({ marketSummary: data }),

  macro: [],
  setMacro: (data) => set({ macro: data }),

  portfolio: [],
  setPortfolio: (data) => set({ portfolio: data }),

  orderResult: null,
  orderLoading: false,
  setOrderResult: (data) => set({ orderResult: data }),
  setOrderLoading: (v) => set({ orderLoading: v }),
}));
