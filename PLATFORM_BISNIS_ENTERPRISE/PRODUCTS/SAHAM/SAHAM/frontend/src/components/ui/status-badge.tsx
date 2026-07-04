import { cn } from "@/lib/utils";

interface StatusBadgeProps {
  status: "Delayed" | "Simulated" | "Real-time" | "Offline";
  className?: string;
}

const colors: Record<StatusBadgeProps["status"], string> = {
  Delayed: "bg-yellow-500/20 text-yellow-400 border-yellow-500/30",
  Simulated: "bg-purple-500/20 text-purple-400 border-purple-500/30",
  "Real-time": "bg-green-500/20 text-green-400 border-green-500/30",
  Offline: "bg-red-500/20 text-red-400 border-red-500/30",
};

export function StatusBadge({ status, className }: StatusBadgeProps) {
  return (
    <span
      className={cn(
        "inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-bold",
        colors[status],
        className,
      )}
    >
      {status}
    </span>
  );
}
