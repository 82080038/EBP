import { test } from '@playwright/test';

/**
 * Manual interaction test for debugging
 * This test keeps the browser open for manual inspection
 * Run with: npx playwright test tests/manual.spec.ts --headed
 */
test('Manual interaction - browser stays open', async ({ page }) => {
  test.setTimeout(300000); // 5 minutes timeout
  
  await page.goto('http://localhost:8000');
  
  // Click login button to show login form
  await page.click('#loginBtn');
  
  // Auto-login for convenience
  await page.fill('#username', 'admin');
  await page.fill('#password', 'admin123');
  await page.click('#loginForm .btn');
  
  // Wait for dashboard
  await page.waitForSelector('.dashboard.active');
  
  // Keep browser open for manual interaction
  // The browser will stay open for 5 minutes
  await page.waitForTimeout(300000); // 5 minutes
});
