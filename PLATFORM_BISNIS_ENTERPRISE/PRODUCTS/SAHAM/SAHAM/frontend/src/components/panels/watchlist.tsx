"use client";

import { Panel } from "@/components/ui/panel";
import { StatusBadge } from "@/components/ui/status-badge";
import { List } from "lucide-react";
import { formatNumber, formatPercent, changeColor, cn } from "@/lib/utils";
import type { WatchlistItem } from "@/lib/types";

interface WatchlistProps {
  items: WatchlistItem[];
  loading: boolean;
  onSelect: (ticker: string, name: string) => void;
  selectedTicker: string;
}

const signalColors: Record<string, string> = {
  BUY: "text-bull bg-bull/10",
  SELL: "text-bear bg-bear/10",
  HOLD: "text-neutral bg-neutral/10",
};

export function Watchlist({ items, loading, onSelect, selectedTicker }: WatchlistProps) {
  return (
    <Panel
      title="Watchlist"
      icon={<List className="h-4 w-4" />}
      badge={<StatusBadge status="Delayed" />}
      className="h-full"
    >
      {loading ? (
        <div className="space-y-2 p-3">
          {Array.from({ length: 8 }).map((_, i) => (
            <div key={i} className="h-12 animate-pulse rounded bg-panel-border/50" />
          ))}
        </div>
      ) : items.length === 0 ? (
        <div className="p-4 text-center text-sm text-muted">
          No watchlist data available
        </div>
      ) : (
        <table className="w-full text-xs">
          <thead className="sticky top-0 bg-panel text-muted">
            <tr>
              <th className="px-2 py-1.5 text-left font-medium">Ticker</th>
              <th className="px-2 py-1.5 text-right font-medium">Price</th>
              <th className="px-2 py-1.5 text-right font-medium">Chg%</th>
              <th className="px-2 py-1.5 text-center font-medium">Signal</th>
              <th className="px-2 py-1.5 text-right font-medium">AI</th>
            </tr>
          </thead>
          <tbody>
            {items.map((item) => (
              <tr
                key={item.ticker}
                onClick={() => onSelect(item.ticker, item.name)}
                className={cn(
                  "cursor-pointer border-b border-panel-border/30 transition-colors hover:bg-panel-hover",
                  selectedTicker === item.ticker && "bg-accent/10",
                )}
              >
                <td className="px-2 py-1.5 font-mono font-medium">{item.ticker}</td>
                <td className="px-2 py-1.5 text-right font-mono">
                  {formatNumber(item.price)}
                </td>
                <td className={cn("px-2 py-1.5 text-right font-mono", changeColor(item.change_pct))}>
                  {formatPercent(item.change_pct)}
                </td>
                <td className="px-2 py-1.5 text-center">
                  <span className={cn("rounded px-1.5 py-0.5 text-[10px] font-bold", signalColors[item.signal])}>
                    {item.signal}
                  </span>
                </td>
                <td className="px-2 py-1.5 text-right font-mono text-accent">
                  {item.ai_score.toFixed(1)}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </Panel>
  );
}
