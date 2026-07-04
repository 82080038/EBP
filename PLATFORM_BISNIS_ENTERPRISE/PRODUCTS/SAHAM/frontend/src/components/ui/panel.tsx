import { cn } from "@/lib/utils";

interface PanelProps {
  title: string;
  icon?: React.ReactNode;
  badge?: React.ReactNode;
  actions?: React.ReactNode;
  children: React.ReactNode;
  className?: string;
  bodyClassName?: string;
}

export function Panel({
  title,
  icon,
  badge,
  actions,
  children,
  className,
  bodyClassName,
}: PanelProps) {
  return (
    <div
      className={cn(
        "flex flex-col rounded-lg border border-panel-border bg-panel overflow-hidden",
        className,
      )}
    >
      <div className="flex items-center justify-between border-b border-panel-border px-3 py-2">
        <div className="flex items-center gap-2">
          {icon && <span className="text-accent">{icon}</span>}
          <h3 className="text-sm font-semibold text-foreground">{title}</h3>
          {badge}
        </div>
        {actions && <div className="flex items-center gap-2">{actions}</div>}
      </div>
      <div className={cn("flex-1 overflow-auto scrollbar-thin", bodyClassName)}>
        {children}
      </div>
    </div>
  );
}
