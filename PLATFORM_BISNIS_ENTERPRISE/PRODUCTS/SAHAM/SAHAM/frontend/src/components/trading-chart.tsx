"use client";

import { useEffect, useRef } from "react";
import {
  createChart,
  ColorType,
  CrosshairMode,
  CandlestickSeries,
  HistogramSeries,
  LineSeries,
  LineStyle,
  type IChartApi,
  type ISeriesApi,
} from "lightweight-charts";
import type { OHLCV } from "@/lib/types";

interface TradingChartProps {
  data: OHLCV[];
  levels?: {
    entry?: number;
    target_1?: number;
    target_2?: number;
    target_3?: number;
    stop_loss?: number;
  };
  height?: number;
}

export function TradingChart({ data, levels, height = 400 }: TradingChartProps) {
  const containerRef = useRef<HTMLDivElement>(null);
  const chartRef = useRef<IChartApi | null>(null);
  const seriesRef = useRef<ISeriesApi<"Candlestick"> | null>(null);
  const volumeRef = useRef<ISeriesApi<"Histogram"> | null>(null);
  const maSeriesRef = useRef<ISeriesApi<"Line">[]>([]);
  const priceLinesRef = useRef<import("lightweight-charts").IPriceLine[]>([]);

  useEffect(() => {
    if (!containerRef.current) return;

    const chart = createChart(containerRef.current, {
      width: containerRef.current.clientWidth,
      height,
      layout: {
        background: { type: ColorType.Solid, color: "transparent" },
        textColor: "#9ca3af",
        fontSize: 11,
      },
      grid: {
        vertLines: { color: "rgba(55,65,81,0.3)" },
        horzLines: { color: "rgba(55,65,81,0.3)" },
      },
      crosshair: {
        mode: CrosshairMode.Normal,
        vertLine: { color: "#3b82f6", width: 1, style: 2 },
        horzLine: { color: "#3b82f6", width: 1, style: 2 },
      },
      rightPriceScale: { borderColor: "#1f2937" },
      timeScale: { borderColor: "#1f2937", timeVisible: false },
    });

    const candleSeries = chart.addSeries(CandlestickSeries, {
      upColor: "#22c55e",
      downColor: "#ef4444",
      borderUpColor: "#22c55e",
      borderDownColor: "#ef4444",
      wickUpColor: "#22c55e",
      wickDownColor: "#ef4444",
    });

    const volumeSeries = chart.addSeries(HistogramSeries, {
      priceFormat: { type: "volume" },
      priceScaleId: "volume",
    });
    chart.priceScale("volume").applyOptions({
      scaleMargins: { top: 0.8, bottom: 0 },
    });

    chartRef.current = chart;
    seriesRef.current = candleSeries;
    volumeRef.current = volumeSeries;

    const handleResize = () => {
      if (containerRef.current && chartRef.current) {
        chartRef.current.applyOptions({ width: containerRef.current.clientWidth });
      }
    };
    window.addEventListener("resize", handleResize);

    return () => {
      window.removeEventListener("resize", handleResize);
      chart.remove();
      chartRef.current = null;
    };
  }, [height]);

  useEffect(() => {
    if (!seriesRef.current || !volumeRef.current || data.length === 0) return;

    // Clean up old MA series
    maSeriesRef.current.forEach(s => chartRef.current?.removeSeries(s));
    maSeriesRef.current = [];

    // Clean up old price lines
    priceLinesRef.current.forEach(pl => seriesRef.current?.removePriceLine(pl));
    priceLinesRef.current = [];

    const candleData = data.map((d) => ({
      time: d.date as never,
      open: d.open,
      high: d.high,
      low: d.low,
      close: d.close,
    }));

    const volumeData = data.map((d) => ({
      time: d.date as never,
      value: d.volume,
      color: d.close >= d.open ? "rgba(34,197,94,0.4)" : "rgba(239,68,68,0.4)",
    }));

    seriesRef.current.setData(candleData);
    volumeRef.current.setData(volumeData);

    // Add MA lines
    const addMA = (period: number, color: string) => {
      if (data.length < period) return;
      const maData = calculateMA(data, period);
      const maSeries = chartRef.current!.addSeries(LineSeries, {
        color,
        lineWidth: 1,
        priceLineVisible: false,
        lastValueVisible: false,
      });
      maSeries.setData(maData as never);
      maSeriesRef.current.push(maSeries);
    };

    addMA(5, "#f59e0b");
    addMA(20, "#3b82f6");
    addMA(60, "#a855f7");

    // Add entry/target/stop price lines
    if (levels) {
      const lineColors: Record<string, { color: string; title: string }> = {
        entry: { color: "#60a5fa", title: "Entry" },
        target_1: { color: "#22c55e", title: "T1" },
        target_2: { color: "#22c55e", title: "T2" },
        target_3: { color: "#22c55e", title: "T3" },
        stop_loss: { color: "#ef4444", title: "Stop" },
      };

      for (const [key, val] of Object.entries(levels)) {
        if (val && val > 0 && lineColors[key]) {
          const pl = seriesRef.current.createPriceLine({
            price: val,
            color: lineColors[key].color,
            lineWidth: 1,
            lineStyle: LineStyle.Dashed,
            axisLabelVisible: true,
            title: lineColors[key].title,
          });
          priceLinesRef.current.push(pl);
        }
      }
    }

    chartRef.current?.timeScale().fitContent();
  }, [data, levels]);

  return <div ref={containerRef} className="w-full" style={{ height }} />;
}

function calculateMA(data: OHLCV[], period: number) {
  const result: { time: string; value: number }[] = [];
  for (let i = period - 1; i < data.length; i++) {
    const slice = data.slice(i - period + 1, i + 1);
    const avg = slice.reduce((sum, d) => sum + d.close, 0) / period;
    result.push({ time: data[i].date, value: avg });
  }
  return result;
}
