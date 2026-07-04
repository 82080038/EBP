"use client";

import { RefreshCw, Target } from "lucide-react";
import { StatusBadge } from "@/components/ui/status-badge";
import { formatNumber, formatPercent, changeColor, cn } from "@/lib/utils";
import type { IndexSummary } from "@/lib/types";

interface TopBarProps {
  tickers: Record<string, string>;
  selectedName: string;
  onSelectTicker: (name: string) => void;
  indices: IndexSummary[];
  onRefresh: () => void;
}

export function TopBar({
  tickers,
  selectedName,
  onSelectTicker,
  indices,
  onRefresh,
}: TopBarProps) {
  return (
    <div className="flex items-center gap-4 rounded-lg border border-panel-border bg-panel px-4 py-2.5">
      {/* Logo / Title */}
      <div className="flex items-center gap-2">
        <Target className="h-5 w-5 text-accent" />
        <span className="text-sm font-bold">Command Center</span>
      </div>

      <div className="h-6 w-px bg-panel-border" />

      {/* Ticker Selector */}
      <select
        value={selectedName}
        onChange={(e) => onSelectTicker(e.target.value)}
        className="rounded border border-panel-border bg-panel-hover px-3 py-1 text-sm font-mono text-foreground focus:border-accent focus:outline-none"
      >
        {Object.entries(tickers).map(([name, ticker]) => (
          <option key={ticker} value={name}>
            {name} ({ticker})
          </option>
        ))}
      </select>

      {/* Index Summary */}
      <div className="flex flex-1 items-center gap-4 overflow-x-auto scrollbar-thin">
        {indices.map((idx) => (
          <div key={idx.name} className="flex items-center gap-1.5 whitespace-nowrap text-xs">
            <span className="font-semibold text-foreground">{idx.name}</span>
            <span className="font-mono">{formatNumber(idx.price)}</span>
            <span className={cn("font-mono font-bold", changeColor(idx.change_pct))}>
              {formatPercent(idx.change_pct)}
            </span>
          </div>
        ))}
      </div>

      {/* Status + Refresh */}
      <div className="flex items-center gap-3">
        <StatusBadge status="Delayed" />
        <button
          onClick={onRefresh}
          className="flex items-center gap-1 rounded border border-panel-border bg-panel-hover px-2.5 py-1 text-xs text-muted transition-colors hover:text-foreground"
        >
          <RefreshCw className="h-3.5 w-3.5" />
          Refresh
        </button>
      </div>
    </div>
  );
}
