"use client";

import React, { Component, ErrorInfo, ReactNode } from "react";

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
  errorInfo: ErrorInfo | null;
}

export class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false, error: null, errorInfo: null };
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error, errorInfo: null };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error("[ERROR BOUNDARY] React component error:", error);
    console.error("[ERROR BOUNDARY] Component stack:", errorInfo.componentStack);
    console.error("[ERROR BOUNDARY] Error stack:", error.stack);
    this.setState({ errorInfo });
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="flex min-h-screen items-center justify-center bg-background p-8">
          <div className="max-w-2xl rounded-lg border border-red-500/30 bg-red-500/10 p-6">
            <h1 className="mb-4 text-2xl font-bold text-red-400">
              Application Error
            </h1>
            <p className="mb-4 text-sm text-muted">
              An unexpected error occurred in the Command Center.
            </p>
            <div className="mb-4 rounded border border-red-500/20 bg-black/30 p-4">
              <p className="mb-2 font-mono text-xs text-red-300">
                {this.state.error?.name}: {this.state.error?.message}
              </p>
              {this.state.error?.stack && (
                <pre className="mt-2 max-h-48 overflow-auto text-xs text-muted">
                  {this.state.error.stack}
                </pre>
              )}
              {this.state.errorInfo?.componentStack && (
                <pre className="mt-2 max-h-48 overflow-auto text-xs text-muted">
                  {this.state.errorInfo.componentStack}
                </pre>
              )}
            </div>
            <button
              onClick={() => window.location.reload()}
              className="rounded bg-accent px-4 py-2 text-sm font-bold text-white hover:bg-accent-hover"
            >
              Reload Page
            </button>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
