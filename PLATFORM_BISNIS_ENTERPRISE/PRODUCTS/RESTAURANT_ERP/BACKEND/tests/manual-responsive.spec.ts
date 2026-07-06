import { test, expect } from '@playwright/test';

/**
 * Manual Responsive Data Fetching Test
 * 
 * This test opens the frontend applications in different screen sizes
 * and takes screenshots to demonstrate responsive data fetching
 */

test.describe('Manual Responsive Data Fetching Demo', () => {
  test('Mobile App - Different Screen Sizes', async ({ page }) => {
    // Mobile view
    await page.goto('http://localhost:8000/mobile/index.html');
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(3000);

    await page.screenshot({
      path: 'screenshots/mobile-app-375x667.png',
      fullPage: true
    });

    // Tablet view
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.waitForTimeout(3000);

    await page.screenshot({
      path: 'screenshots/mobile-app-768x1024.png',
      fullPage: true
    });

    // Desktop view
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.waitForTimeout(3000);

    await page.screenshot({
      path: 'screenshots/mobile-app-1440x900.png',
      fullPage: true
    });

    console.log('Mobile app screenshots saved');
  });

  test('Kiosk App - Different Screen Sizes', async ({ page }) => {
    // Mobile view
    await page.goto('http://localhost:8000/kiosk/index.html');
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(3000);

    await page.screenshot({
      path: 'screenshots/kiosk-app-375x667.png',
      fullPage: true
    });

    // Desktop view
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.waitForTimeout(3000);

    await page.screenshot({
      path: 'screenshots/kiosk-app-1920x1080.png',
      fullPage: true
    });

    console.log('Kiosk app screenshots saved');
  });

  test('Consumer App - Different Screen Sizes', async ({ page }) => {
    // Mobile view
    await page.goto('http://localhost:8000/consumer/index.html');
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(3000);

    await page.screenshot({
      path: 'screenshots/consumer-app-375x667.png',
      fullPage: true
    });

    // Desktop view
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.waitForTimeout(3000);

    await page.screenshot({
      path: 'screenshots/consumer-app-1440x900.png',
      fullPage: true
    });

    console.log('Consumer app screenshots saved');
  });

  test('Verify Screen Size Detection in Console', async ({ page }) => {
    await page.goto('http://localhost:8000/mobile/index.html');

    // Listen to console messages
    const consoleMessages: string[] = [];
    page.on('console', msg => {
      consoleMessages.push(msg.text());
    });

    // Test different screen sizes
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(2000);

    const mobileScreenSize = await page.evaluate(() => {
      return (window as any).screenSizeDetector ? (window as any).screenSizeDetector.getScreenSize() : 'not loaded';
    });

    console.log(`Mobile screen size detected: ${mobileScreenSize}`);

    await page.setViewportSize({ width: 1440, height: 900 });
    await page.waitForTimeout(2000);

    const desktopScreenSize = await page.evaluate(() => {
      return (window as any).screenSizeDetector ? (window as any).screenSizeDetector.getScreenSize() : 'not loaded';
    });

    console.log(`Desktop screen size detected: ${desktopScreenSize}`);

    console.log('Console messages:', consoleMessages);
  });
});
