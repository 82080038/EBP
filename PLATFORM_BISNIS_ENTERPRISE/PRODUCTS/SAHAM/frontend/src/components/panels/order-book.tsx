"use client";

import { useState } from "react";
import { Panel } from "@/components/ui/panel";
import { StatusBadge } from "@/components/ui/status-badge";
import { BookOpen, Zap } from "lucide-react";
import { formatNumber } from "@/lib/utils";
import type { OrderBookLevel, SimOrderResult } from "@/lib/types";

interface OrderBookProps {
  data: OrderBookLevel[];
  loading: boolean;
  ticker: string;
  onSubmitOrder: (side: "BUY" | "SELL", qty: number) => void;
  orderResult: SimOrderResult | null;
  orderLoading: boolean;
}

export function OrderBook({
  data,
  loading,
  ticker,
  onSubmitOrder,
  orderResult,
  orderLoading,
}: OrderBookProps) {
  const [side, setSide] = useState<"BUY" | "SELL">("BUY");
  const [qty, setQty] = useState(10);

  return (
    <div className="flex h-full flex-col gap-2">
      <Panel
        title="Order Book"
        icon={<BookOpen className="h-4 w-4" />}
        badge={<StatusBadge status="Simulated" />}
        className="flex-1"
      >
        {loading ? (
          <div className="space-y-1.5 p-2">
            {Array.from({ length: 8 }).map((_, i) => (
              <div key={i} className="h-6 animate-pulse rounded bg-panel-border/50" />
            ))}
          </div>
        ) : data.length === 0 ? (
          <div className="p-4 text-center text-xs text-muted">No order book data</div>
        ) : (
          <table className="w-full text-xs font-mono">
            <thead className="sticky top-0 bg-panel text-muted">
              <tr>
                <th className="px-2 py-1 text-right font-medium">Bid Qty</th>
                <th className="px-2 py-1 text-right font-medium text-bull">Bid</th>
                <th className="px-2 py-1 text-right font-medium text-bear">Ask</th>
                <th className="px-2 py-1 text-right font-medium">Ask Qty</th>
              </tr>
            </thead>
            <tbody>
              {data.map((row) => (
                <tr key={row.level} className="border-b border-panel-border/20">
                  <td className="px-2 py-1 text-right text-muted">{row.bid_qty}</td>
                  <td className="px-2 py-1 text-right text-bull">{formatNumber(row.bid)}</td>
                  <td className="px-2 py-1 text-right text-bear">{formatNumber(row.ask)}</td>
                  <td className="px-2 py-1 text-right text-muted">{row.ask_qty}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </Panel>

      <Panel
        title="Simulated Trade"
        icon={<Zap className="h-4 w-4" />}
        className="flex-none"
        bodyClassName="p-3 space-y-3"
      >
        <div className="flex gap-2">
          <button
            onClick={() => setSide("BUY")}
            className={`flex-1 rounded py-1.5 text-xs font-bold transition-colors ${side === "BUY"
                ? "bg-bull/20 text-bull border border-bull/40"
                : "bg-panel-border text-muted border border-transparent"
              }`}
          >
            BUY
          </button>
          <button
            onClick={() => setSide("SELL")}
            className={`flex-1 rounded py-1.5 text-xs font-bold transition-colors ${side === "SELL"
                ? "bg-bear/20 text-bear border border-bear/40"
                : "bg-panel-border text-muted border border-transparent"
              }`}
          >
            SELL
          </button>
        </div>
        <div className="flex items-center gap-2">
          <label className="text-xs text-muted">Qty (lots)</label>
          <input
            type="number"
            min={1}
            value={qty}
            onChange={(e) => setQty(parseInt(e.target.value) || 1)}
            className="w-20 rounded border border-panel-border bg-panel px-2 py-1 text-xs font-mono text-foreground focus:border-accent focus:outline-none"
          />
          <span className="text-xs text-muted">× 100 = {qty * 100} shares</span>
        </div>
        <button
          onClick={() => onSubmitOrder(side, qty * 100)}
          disabled={orderLoading}
          className="w-full rounded bg-accent py-1.5 text-xs font-bold text-white transition-colors hover:bg-accent-hover disabled:opacity-50"
        >
          {orderLoading ? "Submitting..." : `Submit ${side} ${ticker}`}
        </button>
        {orderResult && (
          <div className="rounded border border-panel-border bg-panel-hover/50 p-2 text-xs font-mono">
            <div className="flex justify-between">
              <span className="text-muted">Status:</span>
              <span className={orderResult.status === "FILLED" ? "text-bull" : "text-neutral"}>
                {orderResult.status}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted">Filled:</span>
              <span>{orderResult.filled_qty}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted">Avg Price:</span>
              <span>{formatNumber(orderResult.avg_fill_price)}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted">Commission:</span>
              <span>{formatNumber(orderResult.commission)}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted">Slippage:</span>
              <span>{orderResult.slippage_bps.toFixed(1)} bps</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted">Latency:</span>
              <span>{orderResult.latency_ms.toFixed(0)} ms</span>
            </div>
          </div>
        )}
      </Panel>
    </div>
  );
}
