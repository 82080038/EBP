"use client";

import { cn } from "@/lib/utils";
import type { TabId } from "@/lib/types";
import {
  LayoutDashboard,
  Brain,
  FlaskConical,
  Gauge,
  CandlestickChart,
  ShieldAlert,
  Briefcase,
  Bot,
  Target,
  Cpu,
  Activity,
  Globe,
  Layers,
  Database,
  Stethoscope,
} from "lucide-react";

interface TabBarProps {
  activeTab: TabId;
  onTabChange: (tab: TabId) => void;
}

const TABS: { id: TabId; label: string; icon: typeof LayoutDashboard }[] = [
  { id: "command-center", label: "Command Center", icon: LayoutDashboard },
  { id: "ai-briefing", label: "AI Briefing", icon: Brain },
  { id: "backtesting", label: "Backtesting", icon: FlaskConical },
  { id: "sentiment", label: "Sentiment", icon: Gauge },
  { id: "patterns", label: "Patterns", icon: CandlestickChart },
  { id: "risk", label: "Risk Mgmt", icon: ShieldAlert },
  { id: "portfolio-opt", label: "Portfolio Opt", icon: Briefcase },
  { id: "trading-agent", label: "Trading Agent", icon: Bot },
  { id: "accuracy", label: "Accuracy", icon: Target },
  { id: "model-details", label: "Model Details", icon: Cpu },
  { id: "regime", label: "Regime", icon: Activity },
  { id: "intermarket", label: "Intermarket", icon: Globe },
  { id: "options", label: "Options", icon: Layers },
  { id: "data-inventory", label: "Data Inventory", icon: Database },
  { id: "system-check", label: "System Check", icon: Stethoscope },
];

export function TabBar({ activeTab, onTabChange }: TabBarProps) {
  return (
    <div className="flex items-center gap-1 overflow-x-auto border-b border-panel-border bg-panel-dark px-2 py-1.5">
      {TABS.map((tab) => {
        const Icon = tab.icon;
        const active = activeTab === tab.id;
        return (
          <button
            key={tab.id}
            onClick={() => onTabChange(tab.id)}
            className={cn(
              "flex items-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1.5 text-xs font-medium transition-colors",
              active
                ? "bg-accent/20 text-accent"
                : "text-muted hover:bg-panel-hover hover:text-foreground",
            )}
          >
            <Icon className="h-3.5 w-3.5" />
            {tab.label}
          </button>
        );
      })}
    </div>
  );
}
