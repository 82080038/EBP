import { test, expect, devices } from '@playwright/test';

// Responsive Design Test Suite
// Tests the application across different screen sizes and devices

test.describe('Responsive Design Tests', () => {

  // Mobile Extra Small (< 360px)
  test('Mobile Extra Small - 320px width', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await page.goto('http://localhost:8000/frontend/mobile/');

    // Check if mobile app loads
    await expect(page.locator('.mobile-app')).toBeVisible();

    // Check header visibility
    await expect(page.locator('.mobile-header')).toBeVisible();

    // Check bottom navigation
    await expect(page.locator('.bottom-nav')).toBeVisible();

    // Take screenshot for visual verification
    await page.screenshot({ path: 'playwright-report/mobile-320x568.png' });
  });

  // Mobile Small (360px - 480px)
  test('Mobile Small - 375px width', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('http://localhost:8000/frontend/mobile/');

    await expect(page.locator('.mobile-app')).toBeVisible();
    await expect(page.locator('.mobile-header')).toBeVisible();
    await expect(page.locator('.bottom-nav')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/mobile-375x667.png' });
  });

  // Mobile Standard (480px)
  test('Mobile Standard - 480px width', async ({ page }) => {
    await page.setViewportSize({ width: 480, height: 800 });
    await page.goto('http://localhost:8000/frontend/mobile/');

    await expect(page.locator('.mobile-app')).toBeVisible();
    await expect(page.locator('.mobile-header')).toBeVisible();
    await expect(page.locator('.bottom-nav')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/mobile-480x800.png' });
  });

  // Tablet Portrait (768px)
  test('Tablet Portrait - 768px width', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('http://localhost:8000/frontend/mobile/');

    await expect(page.locator('.mobile-app')).toBeVisible();
    await expect(page.locator('.mobile-header')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/tablet-768x1024.png' });
  });

  // Tablet Landscape (1024px)
  test('Tablet Landscape - 1024px width', async ({ page }) => {
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.goto('http://localhost:8000/frontend/mobile/');

    await expect(page.locator('.mobile-app')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/tablet-landscape-1024x768.png' });
  });

  // Desktop (1440px)
  test('Desktop - 1440px width', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('http://localhost:8000/');

    await expect(page.locator('.container')).toBeVisible();
    await expect(page.locator('.header')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/desktop-1440x900.png' });
  });

  // Large Desktop (1920px)
  test('Large Desktop - 1920px width', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('http://localhost:8000/');

    await expect(page.locator('.container')).toBeVisible();
    await expect(page.locator('.header')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/desktop-1920x1080.png' });
  });

  // Kiosk Responsive Tests
  test('Kiosk on Tablet - 768px width', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('http://localhost:8000/frontend/kiosk/');

    await expect(page.locator('.kiosk-container')).toBeVisible();
    await expect(page.locator('.kiosk-header')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/kiosk-tablet-768x1024.png' });
  });

  test('Kiosk on Desktop - 1920px width', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('http://localhost:8000/frontend/kiosk/');

    await expect(page.locator('.kiosk-container')).toBeVisible();
    await expect(page.locator('.kiosk-header')).toBeVisible();
    await expect(page.locator('.category-nav')).toBeVisible();
    await expect(page.locator('.menu-grid')).toBeVisible();
    await expect(page.locator('.order-summary')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/kiosk-desktop-1920x1080.png' });
  });

  // Public Dashboard Responsive Tests
  test('Public Dashboard on Mobile - 375px width', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('http://localhost:8000/');

    await expect(page.locator('.container')).toBeVisible();
    await expect(page.locator('.header')).toBeVisible();

    // Check if features grid is responsive
    const featuresGrid = page.locator('.features-grid');
    await expect(featuresGrid).toBeVisible();

    await page.screenshot({ path: 'playwright-report/public-mobile-375x667.png' });
  });

  test('Public Dashboard on Tablet - 768px width', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('http://localhost:8000/');

    await expect(page.locator('.container')).toBeVisible();
    await expect(page.locator('.header')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/public-tablet-768x1024.png' });
  });

  // Landscape Orientation Tests
  test('Mobile Landscape - 667x375', async ({ page }) => {
    await page.setViewportSize({ width: 667, height: 375 });
    await page.goto('http://localhost:8000/frontend/mobile/');

    await expect(page.locator('.mobile-app')).toBeVisible();
    await expect(page.locator('.mobile-header')).toBeVisible();

    await page.screenshot({ path: 'playwright-report/mobile-landscape-667x375.png' });
  });

  // Device-specific tests using specific viewport sizes
  test('iPhone SE viewport - 375x667', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('http://localhost:8000/frontend/mobile/');

    await expect(page.locator('.mobile-app')).toBeVisible();
    await page.screenshot({ path: 'playwright-report/iphone-se.png' });
  });

  test('iPhone 12 viewport - 390x844', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('http://localhost:8000/frontend/mobile/');

    await expect(page.locator('.mobile-app')).toBeVisible();
    await page.screenshot({ path: 'playwright-report/iphone-12.png' });
  });

  test('iPad viewport - 768x1024', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('http://localhost:8000/frontend/mobile/');

    await expect(page.locator('.mobile-app')).toBeVisible();
    await page.screenshot({ path: 'playwright-report/ipad.png' });
  });

  test('Desktop Chrome viewport - 1280x720', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('http://localhost:8000/');

    await expect(page.locator('.container')).toBeVisible();
    await page.screenshot({ path: 'playwright-report/desktop-chrome.png' });
  });
});
