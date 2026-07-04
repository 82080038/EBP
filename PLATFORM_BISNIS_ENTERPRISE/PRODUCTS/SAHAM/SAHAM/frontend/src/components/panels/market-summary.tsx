"use client";

import { Panel } from "@/components/ui/panel";
import { Globe } from "lucide-react";
import { formatNumber, formatPercent, changeColor, cn } from "@/lib/utils";
import type { IndexSummary } from "@/lib/types";

interface MarketSummaryProps {
  indices: IndexSummary[];
  loading: boolean;
}

export function MarketSummary({ indices, loading }: MarketSummaryProps) {
  return (
    <Panel
      title="Market Summary"
      icon={<Globe className="h-4 w-4" />}
      className="h-full"
    >
      {loading ? (
        <div className="space-y-2 p-3">
          {Array.from({ length: 5 }).map((_, i) => (
            <div key={i} className="h-8 animate-pulse rounded bg-panel-border/50" />
          ))}
        </div>
      ) : indices.length === 0 ? (
        <div className="p-4 text-center text-xs text-muted">No market data available</div>
      ) : (
        <table className="w-full text-xs">
          <thead className="sticky top-0 bg-panel text-muted">
            <tr>
              <th className="px-2 py-1.5 text-left font-medium">Index</th>
              <th className="px-2 py-1.5 text-right font-medium">Price</th>
              <th className="px-2 py-1.5 text-right font-medium">Change</th>
            </tr>
          </thead>
          <tbody>
            {indices.map((idx) => (
              <tr key={idx.name} className="border-b border-panel-border/30">
                <td className="px-2 py-1.5 font-medium">{idx.name}</td>
                <td className="px-2 py-1.5 text-right font-mono">{formatNumber(idx.price)}</td>
                <td className={cn("px-2 py-1.5 text-right font-mono font-bold", changeColor(idx.change_pct))}>
                  {formatPercent(idx.change_pct)}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </Panel>
  );
}
