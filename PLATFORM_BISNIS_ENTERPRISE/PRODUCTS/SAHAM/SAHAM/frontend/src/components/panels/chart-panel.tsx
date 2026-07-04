"use client";

import { Panel } from "@/components/ui/panel";
import { StatusBadge } from "@/components/ui/status-badge";
import { CandlestickChart, Sparkles } from "lucide-react";
import { TradingChart } from "@/components/trading-chart";
import type { OHLCV, PredictionResult } from "@/lib/types";

interface ChartPanelProps {
  ticker: string;
  name: string;
  data: OHLCV[];
  loading: boolean;
  prediction: PredictionResult | null;
  predictionLoading: boolean;
  onRunPrediction: () => void;
}

export function ChartPanel({
  ticker,
  name,
  data,
  loading,
  prediction,
  predictionLoading,
  onRunPrediction,
}: ChartPanelProps) {
  const levels = prediction
    ? {
        entry: prediction.entry,
        target_1: prediction.target_1,
        target_2: prediction.target_2,
        target_3: prediction.target_3,
        stop_loss: prediction.stop_loss,
      }
    : undefined;

  return (
    <Panel
      title={`${name} (${ticker})`}
      icon={<CandlestickChart className="h-4 w-4" />}
      badge={<StatusBadge status="Delayed" />}
      actions={
        <button
          onClick={onRunPrediction}
          disabled={predictionLoading}
          className="flex items-center gap-1 rounded bg-accent px-2.5 py-1 text-xs font-semibold text-white transition-colors hover:bg-accent-hover disabled:opacity-50"
        >
          <Sparkles className="h-3.5 w-3.5" />
          {predictionLoading ? "Predicting..." : "Run Prediction"}
        </button>
      }
      className="h-full"
      bodyClassName="p-2"
    >
      {loading ? (
        <div className="flex h-[400px] items-center justify-center">
          <div className="h-8 w-8 animate-spin rounded-full border-2 border-accent border-t-transparent" />
        </div>
      ) : data.length === 0 ? (
        <div className="flex h-[400px] items-center justify-center text-sm text-muted">
          No data available for {ticker}
        </div>
      ) : (
        <TradingChart data={data} levels={levels} height={400} />
      )}
    </Panel>
  );
}
