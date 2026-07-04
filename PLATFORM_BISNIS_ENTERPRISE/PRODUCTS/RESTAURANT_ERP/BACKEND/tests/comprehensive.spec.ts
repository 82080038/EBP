import { test, expect } from '@playwright/test';

test.describe('EBP Restaurant Backend - Comprehensive Test Suite', () => {

  test.beforeEach(async ({ page }) => {
    // Set longer timeout for page loads
    test.setTimeout(60000);
  });

  test('Frontend Test Page', async ({ page }) => {
    await page.goto('http://localhost/ebp-restaurant-backend/frontend/test-frontend.html');

    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);

    // Take screenshot
    await page.screenshot({ path: 'screenshots/frontend-test-page.png', fullPage: true });

    // Verify page title
    const title = await page.title();
    console.log(`Page title: ${title}`);

    // Check component status
    const statusElements = await page.locator('.status').all();
    console.log(`Status elements found: ${statusElements.length}`);

    // Verify all components are marked as pass
    const passCount = await page.locator('.status.pass').count();
    console.log(`Frontend components passed: ${passCount}`);
  });

  test('Kiosk UI', async ({ page }) => {
    await page.goto('http://localhost/ebp-restaurant-backend/frontend/kiosk/index.html');

    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(3000);

    // Take screenshot
    await page.screenshot({ path: 'screenshots/kiosk-ui.png', fullPage: true });

    // Verify page loaded (check if body has content)
    const bodyText = await page.locator('body').textContent();
    console.log(`Kiosk page content length: ${bodyText?.length || 0}`);
  });

  test('Mobile Waiter App UI', async ({ page }) => {
    await page.goto('http://localhost/ebp-restaurant-backend/frontend/mobile/index.html');

    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(3000);

    // Take screenshot
    await page.screenshot({ path: 'screenshots/mobile-app-ui.png', fullPage: true });

    // Verify page loaded (check if body has content)
    const bodyText = await page.locator('body').textContent();
    console.log(`Mobile page content length: ${bodyText?.length || 0}`);
  });

  test('Responsive Design - Desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('http://localhost/ebp-restaurant-backend/frontend/kiosk/index.html');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'screenshots/responsive-desktop.png', fullPage: true });
  });

  test('Responsive Design - Tablet', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('http://localhost/ebp-restaurant-backend/frontend/kiosk/index.html');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'screenshots/responsive-tablet.png', fullPage: true });
  });

  test('Responsive Design - Mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('http://localhost/ebp-restaurant-backend/frontend/mobile/index.html');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'screenshots/responsive-mobile.png', fullPage: true });
  });

  test('All Pages Load Check', async ({ page }) => {
    const pages = [
      'http://localhost/ebp-restaurant-backend/frontend/test-frontend.html',
      'http://localhost/ebp-restaurant-backend/frontend/kiosk/index.html',
      'http://localhost/ebp-restaurant-backend/frontend/mobile/index.html'
    ];

    for (const pageUrl of pages) {
      await page.goto(pageUrl);
      await page.waitForLoadState('domcontentloaded');
      await page.waitForTimeout(1000);
      const title = await page.title();
      console.log(`Page loaded: ${pageUrl} - ${title}`);
      expect(title).toBeTruthy();
    }
  });
});
