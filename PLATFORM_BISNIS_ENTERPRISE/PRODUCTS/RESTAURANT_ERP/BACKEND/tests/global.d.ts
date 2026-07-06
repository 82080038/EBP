declare global {
  interface Window {
    screenSizeDetector?: {
      getScreenSize(): string;
      getDataLimits(): any;
      getFields(resourceType: string): string[] | null;
      getApiParams(resourceType: string): any;
      isMobile(): boolean;
      isTablet(): boolean;
      isDesktop(): boolean;
    };
  }
}

export {};
