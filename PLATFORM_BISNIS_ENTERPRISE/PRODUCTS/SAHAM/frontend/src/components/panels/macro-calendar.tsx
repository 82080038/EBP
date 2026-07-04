"use client";

import { Panel } from "@/components/ui/panel";
import { Calendar } from "lucide-react";
import { formatNumber } from "@/lib/utils";
import type { MacroIndicator } from "@/lib/types";

interface MacroCalendarProps {
  indicators: MacroIndicator[];
  loading: boolean;
}

export function MacroCalendar({ indicators, loading }: MacroCalendarProps) {
  return (
    <Panel
      title="Calendar & Macro"
      icon={<Calendar className="h-4 w-4" />}
      className="h-full"
    >
      {loading ? (
        <div className="space-y-2 p-3">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="h-6 animate-pulse rounded bg-panel-border/50" />
          ))}
        </div>
      ) : indicators.length === 0 ? (
        <div className="p-4 text-center text-xs text-muted">No macro data available</div>
      ) : (
        <table className="w-full text-xs">
          <tbody>
            {indicators.map((ind) => (
              <tr key={ind.name} className="border-b border-panel-border/30">
                <td className="px-2 py-1.5 font-medium">{ind.name}</td>
                <td className="px-2 py-1.5 text-right font-mono font-bold">
                  {formatNumber(ind.value)}
                </td>
                <td className="px-2 py-1.5 text-right font-mono text-muted">{ind.date}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </Panel>
  );
}
