import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        background: "var(--background)",
        foreground: "var(--foreground)",
        panel: {
          DEFAULT: "#111827",
          border: "#1f2937",
          hover: "#1a2332",
        },
        accent: {
          DEFAULT: "#3b82f6",
          hover: "#2563eb",
        },
        bull: "#22c55e",
        bear: "#ef4444",
        neutral: "#f59e0b",
        muted: "#6b7280",
      },
      fontFamily: {
        mono: ["var(--font-geist-mono)", "monospace"],
        sans: ["var(--font-geist-sans)", "sans-serif"],
      },
    },
  },
  plugins: [],
};
export default config;
