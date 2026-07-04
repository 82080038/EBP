/**
 * Panel-level logging utilities for tracking data load lifecycle.
 *
 * Usage in panel components:
 *   const log = createPanelLogger("AIBriefing");
 *   log.start("load");
 *   log.success("load", { keys: Object.keys(data) });
 *   log.error("load", err);
 */

type PanelPhase = string;

export function createPanelLogger(panelName: string) {
  const prefix = `[Panel:${panelName}]`;

  return {
    start(phase: PanelPhase, detail?: string) {
      console.log(`${prefix} ▶ ${phase}${detail ? ` — ${detail}` : ""}`);
    },

    success(phase: PanelPhase, detail?: Record<string, unknown> | string) {
      const d = typeof detail === "string" ? detail : detail ? JSON.stringify(detail) : "";
      console.log(`${prefix} ✓ ${phase}${d ? ` — ${d}` : ""}`);
    },

    error(phase: PanelPhase, err: unknown) {
      const msg = err instanceof Error ? err.message : String(err);
      console.error(`${prefix} ✖ ${phase} FAILED — ${msg}`, err);
    },

    warn(phase: PanelPhase, msg: string) {
      console.warn(`${prefix} ⚠ ${phase} — ${msg}`);
    },

    info(msg: string) {
      console.log(`${prefix} ℹ ${msg}`);
    },
  };
}
