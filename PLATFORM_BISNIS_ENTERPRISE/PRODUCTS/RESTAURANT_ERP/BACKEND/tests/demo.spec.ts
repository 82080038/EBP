import { test } from '@playwright/test';

test('Interactive demo - keeps browser open', async ({ page }) => {
  await page.goto('http://localhost:8000');
  
  // Wait for user to see the landing page
  await page.waitForTimeout(3000);
  
  // Click login button to show login form
  await page.click('#loginBtn');
  await page.waitForTimeout(1000);
  
  // Auto-login
  await page.fill('#username', 'admin');
  await page.fill('#password', 'admin123');
  await page.click('#loginForm .btn');
  
  // Wait for dashboard
  await page.waitForSelector('.dashboard.active');
  await page.waitForTimeout(2000);
  
  // Navigate through tabs slowly
  await page.click('[data-tab="menu"]');
  await page.waitForTimeout(2000);
  
  await page.click('[data-tab="tables"]');
  await page.waitForTimeout(2000);
  
  await page.click('[data-tab="inventory"]');
  await page.waitForTimeout(2000);
  
  await page.click('[data-tab="kitchen"]');
  await page.waitForTimeout(2000);
  
  await page.click('[data-tab="overview"]');
  await page.waitForTimeout(2000);
  
  // Keep browser open for manual interaction
  await page.waitForTimeout(10000);
});
