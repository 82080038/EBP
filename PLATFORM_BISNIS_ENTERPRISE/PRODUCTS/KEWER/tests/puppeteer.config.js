module.exports = {
  launchOptions: {
    headless: false,
    args: [
      '--disable-save-password-bubble',
      '--disable-password-generation',
      '--disable-translate',
      '--no-first-run',
      '--disable-blink-features=AutomationControlled',
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--window-position=3840,0',
      '--window-size=1920,1080'
    ],
    defaultViewport: null,
  },
  timeout: 60000,
  baseUrl: 'http://localhost/kewer',
  screenshotPath: './tests/screenshots',
  contextOptions: {
    ignoreHTTPSErrors: true,
    permissions: {}
  },
  multiMonitor: true,
  monitors: 3,
  monitorPositions: {
    1: { x: 0, y: 0 },
    2: { x: 1920, y: 0 },
    3: { x: 3840, y: 0 }
  }
};
