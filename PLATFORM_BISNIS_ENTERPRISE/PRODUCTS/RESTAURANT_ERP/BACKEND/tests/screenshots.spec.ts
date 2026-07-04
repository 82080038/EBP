import { test, expect } from '@playwright/test';

test.describe('Screenshot Analysis', () => {
  test('Main Dashboard - Landing Page', async ({ page }) => {
    await page.goto('http://localhost:8000');
    await page.waitForTimeout(2000); // Wait for page to fully render
    await page.screenshot({ path: 'screenshots/dashboard-landing.png', fullPage: true });
  });

  test('Main Dashboard - After Login', async ({ page }) => {
    await page.goto('http://localhost:8000');
    await page.click('#loginBtn');
    await page.fill('#username', 'admin');
    await page.fill('#password', 'admin123');
    await page.click('#loginForm .btn');
    await page.waitForSelector('.dashboard.active');
    await page.waitForTimeout(2000); // Wait for content to load
    await page.screenshot({ path: 'screenshots/dashboard-logged-in.png', fullPage: true });
  });

  test('Mobile App - Orders Page', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('http://localhost:8000/frontend/mobile/');
    await page.waitForTimeout(3000); // Wait for data to load
    await page.screenshot({ path: 'screenshots/mobile-orders.png', fullPage: true });
  });

  test('Mobile App - Menu Page', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('http://localhost:8000/frontend/mobile/');
    await page.waitForTimeout(3000); // Wait for data to load
    await page.screenshot({ path: 'screenshots/mobile-menu.png', fullPage: true });
  });

  test('Mobile App - Tables Page', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('http://localhost:8000/frontend/mobile/');
    await page.waitForTimeout(3000); // Wait for data to load
    await page.evaluate(() => {
      document.querySelector('[data-page="tables"]').click();
    });
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'screenshots/mobile-tables.png', fullPage: true });
  });

  test('Kiosk App - Main View', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('http://localhost:8000/frontend/kiosk/');
    await page.waitForTimeout(3000); // Wait for data to load
    await page.screenshot({ path: 'screenshots/kiosk-main.png', fullPage: true });
  });

  test('Kiosk App - Tablet View', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('http://localhost:8000/frontend/kiosk/');
    await page.waitForTimeout(3000); // Wait for data to load
    await page.screenshot({ path: 'screenshots/kiosk-tablet.png', fullPage: true });
  });
});
