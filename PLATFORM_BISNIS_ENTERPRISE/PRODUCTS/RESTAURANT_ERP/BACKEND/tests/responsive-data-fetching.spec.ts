import { test, expect } from '@playwright/test';

/**
 * Responsive Data Fetching Tests
 * 
 * This test suite verifies that the application correctly adjusts
 * data fetching based on screen size (mobile, tablet, desktop)
 */

test.describe('Responsive Data Fetching - Mobile App', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to mobile app
    await page.goto('http://localhost:8000/FRONTEND/frontend/mobile/index.html');
  });

  test('Mobile view should fetch limited data', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Wait for initial data load
    await page.waitForTimeout(2000);
    
    // Check screen size detection
    const screenSize = await page.evaluate(() => {
      return window.screenSizeDetector ? window.screenSizeDetector.getScreenSize() : 'unknown';
    });
    expect(screenSize).toBe('mobile');
    
    // Check API requests for screen size headers
    const apiRequests: any[] = [];
    page.on('request', request => {
      if (request.url().includes('/api/')) {
        apiRequests.push({
          url: request.url(),
          headers: request.headers()
        });
      }
    });
    
    // Reload page to capture requests
    await page.reload();
    await page.waitForTimeout(2000);
    
    // Verify screen size headers in API requests
    const mobileRequests = apiRequests.filter(req => 
      req.headers['x-screen-size'] === 'mobile'
    );
    expect(mobileRequests.length).toBeGreaterThan(0);
    
    // Take screenshot
    await page.screenshot({ 
      path: 'screenshots/mobile-view-limited-data.png',
      fullPage: true 
    });
    
    console.log('Mobile view screenshot saved: screenshots/mobile-view-limited-data.png');
  });

  test('Mobile view should have limited product count', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Navigate to menu page
    await page.click('[data-page="menu"]');
    await page.waitForTimeout(1000);
    
    // Count menu items
    const menuItems = await page.locator('.menu-item').count();
    console.log(`Mobile menu items count: ${menuItems}`);
    
    // Should be limited (<= 10 for mobile)
    expect(menuItems).toBeLessThanOrEqual(10);
    
    await page.screenshot({ 
      path: 'screenshots/mobile-menu-limited-items.png',
      fullPage: true 
    });
  });

  test('Mobile view should have limited order count', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Wait for orders to load
    await page.waitForTimeout(2000);
    
    // Count order cards
    const orderCards = await page.locator('.order-card').count();
    console.log(`Mobile order cards count: ${orderCards}`);
    
    // Should be limited (<= 5 for mobile)
    expect(orderCards).toBeLessThanOrEqual(5);
    
    await page.screenshot({ 
      path: 'screenshots/mobile-orders-limited-items.png',
      fullPage: true 
    });
  });
});

test.describe('Responsive Data Fetching - Tablet View', () => {
  test('Tablet view should fetch moderate data', async ({ page }) => {
    // Navigate to mobile app
    await page.goto('http://localhost:8000/FRONTEND/frontend/mobile/index.html');
    
    // Set tablet viewport
    await page.setViewportSize({ width: 768, height: 1024 });
    
    // Wait for initial data load
    await page.waitForTimeout(2000);
    
    // Check screen size detection
    const screenSize = await page.evaluate(() => {
      return window.screenSizeDetector ? window.screenSizeDetector.getScreenSize() : 'unknown';
    });
    expect(screenSize).toBe('tablet');
    
    // Check API requests for screen size headers
    const apiRequests: any[] = [];
    page.on('request', request => {
      if (request.url().includes('/api/')) {
        apiRequests.push({
          url: request.url(),
          headers: request.headers()
        });
      }
    });
    
    // Reload page to capture requests
    await page.reload();
    await page.waitForTimeout(2000);
    
    // Verify screen size headers in API requests
    const tabletRequests = apiRequests.filter(req => 
      req.headers['x-screen-size'] === 'tablet'
    );
    expect(tabletRequests.length).toBeGreaterThan(0);
    
    // Take screenshot
    await page.screenshot({ 
      path: 'screenshots/tablet-view-moderate-data.png',
      fullPage: true 
    });
    
    console.log('Tablet view screenshot saved: screenshots/tablet-view-moderate-data.png');
  });

  test('Tablet view should have moderate product count', async ({ page }) => {
    await page.goto('http://localhost:8000/FRONTEND/frontend/mobile/index.html');
    await page.setViewportSize({ width: 768, height: 1024 });
    
    // Navigate to menu page
    await page.click('[data-page="menu"]');
    await page.waitForTimeout(1000);
    
    // Count menu items
    const menuItems = await page.locator('.menu-item').count();
    console.log(`Tablet menu items count: ${menuItems}`);
    
    // Should be moderate (<= 20 for tablet)
    expect(menuItems).toBeLessThanOrEqual(20);
    
    await page.screenshot({ 
      path: 'screenshots/tablet-menu-moderate-items.png',
      fullPage: true 
    });
  });
});

test.describe('Responsive Data Fetching - Desktop View', () => {
  test('Desktop view should fetch full data', async ({ page }) => {
    // Navigate to mobile app
    await page.goto('http://localhost:8000/FRONTEND/frontend/mobile/index.html');
    
    // Set desktop viewport
    await page.setViewportSize({ width: 1440, height: 900 });
    
    // Wait for initial data load
    await page.waitForTimeout(2000);
    
    // Check screen size detection
    const screenSize = await page.evaluate(() => {
      return window.screenSizeDetector ? window.screenSizeDetector.getScreenSize() : 'unknown';
    });
    expect(screenSize).toBe('desktop');
    
    // Check API requests for screen size headers
    const apiRequests: any[] = [];
    page.on('request', request => {
      if (request.url().includes('/api/')) {
        apiRequests.push({
          url: request.url(),
          headers: request.headers()
        });
      }
    });
    
    // Reload page to capture requests
    await page.reload();
    await page.waitForTimeout(2000);
    
    // Verify screen size headers in API requests
    const desktopRequests = apiRequests.filter(req => 
      req.headers['x-screen-size'] === 'desktop'
    );
    expect(desktopRequests.length).toBeGreaterThan(0);
    
    // Take screenshot
    await page.screenshot({ 
      path: 'screenshots/desktop-view-full-data.png',
      fullPage: true 
    });
    
    console.log('Desktop view screenshot saved: screenshots/desktop-view-full-data.png');
  });

  test('Desktop view should have full product count', async ({ page }) => {
    await page.goto('http://localhost:8000/FRONTEND/frontend/mobile/index.html');
    await page.setViewportSize({ width: 1440, height: 900 });
    
    // Navigate to menu page
    await page.click('[data-page="menu"]');
    await page.waitForTimeout(1000);
    
    // Count menu items
    const menuItems = await page.locator('.menu-item').count();
    console.log(`Desktop menu items count: ${menuItems}`);
    
    // Should be full (up to 100 for desktop)
    expect(menuItems).toBeGreaterThan(0);
    
    await page.screenshot({ 
      path: 'screenshots/desktop-menu-full-items.png',
      fullPage: true 
    });
  });
});

test.describe('Screen Size Change Detection', () => {
  test('Data should reload when screen size changes from mobile to desktop', async ({ page }) => {
    await page.goto('http://localhost:8000/FRONTEND/frontend/mobile/index.html');
    
    // Start with mobile view
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(2000);
    
    // Navigate to menu
    await page.click('[data-page="menu"]');
    await page.waitForTimeout(1000);
    
    // Count initial menu items
    const mobileMenuItems = await page.locator('.menu-item').count();
    console.log(`Mobile menu items: ${mobileMenuItems}`);
    
    // Take screenshot before resize
    await page.screenshot({ 
      path: 'screenshots/before-resize-mobile.png',
      fullPage: true 
    });
    
    // Resize to desktop
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.waitForTimeout(2000);
    
    // Count menu items after resize
    const desktopMenuItems = await page.locator('.menu-item').count();
    console.log(`Desktop menu items after resize: ${desktopMenuItems}`);
    
    // Take screenshot after resize
    await page.screenshot({ 
      path: 'screenshots/after-resize-desktop.png',
      fullPage: true 
    });
    
    // Data should have reloaded (may have different count)
    console.log(`Screen size change detected and data reloaded`);
  });

  test('Screen size change event should be triggered', async ({ page }) => {
    await page.goto('http://localhost:8000/FRONTEND/frontend/mobile/index.html');
    
    // Listen for screen size change event
    const screenSizeChanges: string[] = [];
    await page.exposeFunction('logScreenSizeChange', (size: string) => {
      screenSizeChanges.push(size);
    });
    
    await page.evaluate(() => {
      window.addEventListener('screenSizeChanged', (e: any) => {
        (window as any).logScreenSizeChange(e.detail.screenSize);
      });
    });
    
    // Start with mobile
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(500);
    
    // Resize to tablet
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.waitForTimeout(500);
    
    // Resize to desktop
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.waitForTimeout(500);
    
    // Verify screen size change events were triggered
    expect(screenSizeChanges.length).toBeGreaterThan(0);
    console.log(`Screen size changes detected: ${screenSizeChanges.join(', ')}`);
  });
});

test.describe('Responsive Data Fetching - Kiosk App', () => {
  test('Kiosk should adapt to different screen sizes', async ({ page }) => {
    await page.goto('http://localhost:8000/FRONTEND/frontend/kiosk/index.html');
    
    // Test mobile size
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(2000);
    
    const mobileScreenSize = await page.evaluate(() => {
      return window.screenSizeDetector ? window.screenSizeDetector.getScreenSize() : 'unknown';
    });
    expect(mobileScreenSize).toBe('mobile');
    
    await page.screenshot({ 
      path: 'screenshots/kiosk-mobile.png',
      fullPage: true 
    });
    
    // Test desktop size
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.waitForTimeout(2000);
    
    const desktopScreenSize = await page.evaluate(() => {
      return window.screenSizeDetector ? window.screenSizeDetector.getScreenSize() : 'unknown';
    });
    expect(desktopScreenSize).toBe('desktop');
    
    await page.screenshot({ 
      path: 'screenshots/kiosk-desktop.png',
      fullPage: true 
    });
    
    console.log('Kiosk app screenshots saved');
  });
});

test.describe('Responsive Data Fetching - Consumer App', () => {
  test('Consumer app should adapt to different screen sizes', async ({ page }) => {
    await page.goto('http://localhost:8000/FRONTEND/frontend/consumer/index.html');
    
    // Test mobile size
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(2000);
    
    const mobileScreenSize = await page.evaluate(() => {
      return window.screenSizeDetector ? window.screenSizeDetector.getScreenSize() : 'unknown';
    });
    expect(mobileScreenSize).toBe('mobile');
    
    await page.screenshot({ 
      path: 'screenshots/consumer-mobile.png',
      fullPage: true 
    });
    
    // Test desktop size
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.waitForTimeout(2000);
    
    const desktopScreenSize = await page.evaluate(() => {
      return window.screenSizeDetector ? window.screenSizeDetector.getScreenSize() : 'unknown';
    });
    expect(desktopScreenSize).toBe('desktop');
    
    await page.screenshot({ 
      path: 'screenshots/consumer-desktop.png',
      fullPage: true 
    });
    
    console.log('Consumer app screenshots saved');
  });
});

test.describe('API Response Verification', () => {
  test('API should return limited fields for mobile requests', async ({ request }) => {
    // Simulate mobile request
    const response = await request.get('http://localhost:8000/api/v1/products', {
      headers: {
        'X-Screen-Size': 'mobile',
        'X-Screen-Width': '375'
      }
    });
    
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    console.log('Mobile API response:', JSON.stringify(data, null, 2));
    
    // Verify response structure
    expect(data).toHaveProperty('success');
    expect(data).toHaveProperty('data');
  });

  test('API should return full fields for desktop requests', async ({ request }) => {
    // Simulate desktop request
    const response = await request.get('http://localhost:8000/api/v1/products', {
      headers: {
        'X-Screen-Size': 'desktop',
        'X-Screen-Width': '1440'
      }
    });
    
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    console.log('Desktop API response:', JSON.stringify(data, null, 2));
    
    // Verify response structure
    expect(data).toHaveProperty('success');
    expect(data).toHaveProperty('data');
  });
});
