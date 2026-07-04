"use client";

import { Panel } from "@/components/ui/panel";
import { Briefcase } from "lucide-react";
import { formatNumber, cn } from "@/lib/utils";
import type { PortfolioPosition } from "@/lib/types";

interface PortfolioPanelProps {
  positions: PortfolioPosition[];
  loading: boolean;
}

export function PortfolioPanel({ positions, loading }: PortfolioPanelProps) {
  return (
    <Panel
      title="Portfolio & Orders"
      icon={<Briefcase className="h-4 w-4" />}
      className="h-full"
    >
      {loading ? (
        <div className="space-y-2 p-3">
          {Array.from({ length: 3 }).map((_, i) => (
            <div key={i} className="h-8 animate-pulse rounded bg-panel-border/50" />
          ))}
        </div>
      ) : positions.length === 0 ? (
        <div className="p-4 text-center text-xs text-muted">
          No positions yet. Submit a simulated order to see portfolio.
        </div>
      ) : (
        <table className="w-full text-xs">
          <thead className="sticky top-0 bg-panel text-muted">
            <tr>
              <th className="px-2 py-1.5 text-left font-medium">Symbol</th>
              <th className="px-2 py-1.5 text-right font-medium">Qty</th>
              <th className="px-2 py-1.5 text-right font-medium">Avg</th>
              <th className="px-2 py-1.5 text-right font-medium">Market</th>
              <th className="px-2 py-1.5 text-right font-medium">PnL</th>
            </tr>
          </thead>
          <tbody>
            {positions.map((pos) => (
              <tr key={pos.symbol} className="border-b border-panel-border/30">
                <td className="px-2 py-1.5 font-mono font-medium">{pos.symbol}</td>
                <td className="px-2 py-1.5 text-right font-mono">{pos.quantity}</td>
                <td className="px-2 py-1.5 text-right font-mono">{formatNumber(pos.avg_price)}</td>
                <td className="px-2 py-1.5 text-right font-mono">{formatNumber(pos.market_price)}</td>
                <td className={cn(
                  "px-2 py-1.5 text-right font-mono font-bold",
                  pos.unrealized_pnl > 0 ? "text-bull" : pos.unrealized_pnl < 0 ? "text-bear" : "text-muted",
                )}>
                  {pos.unrealized_pnl >= 0 ? "+" : ""}{formatNumber(pos.unrealized_pnl)}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </Panel>
  );
}
