"use client";

import { Panel } from "@/components/ui/panel";
import { Brain } from "lucide-react";
import { formatNumber, formatPercent, cn } from "@/lib/utils";
import type { PredictionResult, ScoreResult } from "@/lib/types";

interface DecisionStripProps {
  prediction: PredictionResult | null;
  score: ScoreResult | null;
  loading: boolean;
}

const signalStyles: Record<string, { bg: string; text: string; border: string }> = {
  BUY: { bg: "bg-bull/10", text: "text-bull", border: "border-bull/40" },
  SELL: { bg: "bg-bear/10", text: "text-bear", border: "border-bear/40" },
  HOLD: { bg: "bg-neutral/10", text: "text-neutral", border: "border-neutral/40" },
};

export function DecisionStrip({ prediction, score, loading }: DecisionStripProps) {
  if (loading) {
    return (
      <div className="flex h-20 items-center justify-center rounded-lg border border-panel-border bg-panel">
        <div className="h-6 w-6 animate-spin rounded-full border-2 border-accent border-t-transparent" />
      </div>
    );
  }

  if (!prediction) {
    return (
      <Panel
        title="Decision Strip"
        icon={<Brain className="h-4 w-4" />}
        className="h-20"
        bodyClassName="flex items-center justify-center"
      >
        <p className="text-sm text-muted">
          Click <span className="font-semibold text-accent">Run Prediction</span> to generate AI signal
        </p>
      </Panel>
    );
  }

  const style = signalStyles[prediction.sinyal] || signalStyles.HOLD;
  const priceChange =
    prediction.current_price > 0
      ? ((prediction.predicted_price - prediction.current_price) / prediction.current_price) * 100
      : 0;

  const shapExps = prediction.shap_explanations || {};
  const firstExp = Object.values(shapExps)[0];
  const shapText = firstExp
    ? firstExp.top_features
      .slice(0, 3)
      .map(([name, val]: [string, number]) => `${name} (${val > 0 ? "+" : ""}${val.toFixed(4)})`)
      .join(", ")
    : "";

  return (
    <div className="grid grid-cols-12 gap-2 rounded-lg border border-panel-border bg-panel p-3">
      {/* Signal Badge */}
      <div className={cn("col-span-2 flex flex-col items-center justify-center rounded-lg border p-2", style.bg, style.border)}>
        <span className={cn("text-2xl font-extrabold", style.text)}>{prediction.sinyal}</span>
        <span className="text-xs text-muted">AI Score: {prediction.ai_score.toFixed(1)}/10</span>
      </div>

      {/* Confidence */}
      <div className="col-span-2 flex flex-col justify-center">
        <span className="text-xs text-muted">Confidence</span>
        <span className="text-lg font-bold text-foreground">{(prediction.confidence * 100).toFixed(1)}%</span>
      </div>

      {/* Regime */}
      <div className="col-span-2 flex flex-col justify-center">
        <span className="text-xs text-muted">Market Regime</span>
        <span className="text-lg font-bold capitalize text-foreground">{prediction.market_regime}</span>
      </div>

      {/* Risk/Reward */}
      <div className="col-span-2 flex flex-col justify-center">
        <span className="text-xs text-muted">Risk/Reward</span>
        <span className="text-lg font-bold text-foreground">
          1:{prediction.risk_reward.toFixed(2)}
        </span>
      </div>

      {/* Price Prediction + SHAP */}
      <div className="col-span-4 flex flex-col justify-center">
        <div className="flex items-center gap-2 text-sm">
          <span className="text-muted">Price:</span>
          <span className="font-mono font-bold">{formatNumber(prediction.current_price)}</span>
          <span className="text-muted">→</span>
          <span className="font-mono font-bold">{formatNumber(prediction.predicted_price)}</span>
          <span className={cn("font-mono text-xs", priceChange > 0 ? "text-bull" : "text-bear")}>
            ({formatPercent(priceChange)})
          </span>
        </div>
        {shapText && (
          <div className="mt-1 text-xs text-muted">
            <span className="text-accent">SHAP:</span> {shapText}
          </div>
        )}
      </div>

      {/* Score Breakdown */}
      {score && (
        <div className="col-span-12 flex items-center gap-3 border-t border-panel-border pt-2 text-xs">
          <span className="text-muted">Ratings:</span>
          <span className="text-foreground">Tech {score.technical_rating.toFixed(0)}</span>
          <span className="text-foreground">Sent {score.sentiment_rating.toFixed(0)}</span>
          <span className="text-foreground">Mom {score.momentum_rating.toFixed(0)}</span>
          <span className="text-foreground">Risk {score.risk_rating.toFixed(0)}</span>
          <span className="text-accent font-bold">{score.signal_strength}</span>
        </div>
      )}
    </div>
  );
}
