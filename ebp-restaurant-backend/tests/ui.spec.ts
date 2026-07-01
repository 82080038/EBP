import { test, expect } from '@playwright/test';

test.describe('Restaurant Backend UI Tests', () => {

  test('should load restaurant management UI', async ({ page }) => {
    await page.goto('http://localhost:8000');

    // Check if page loads with restaurant UI
    await expect(page.locator('h1')).toContainText('Restaurant Management System');
    await expect(page.locator('.login-section')).toBeVisible();
  });

  test('should login with valid credentials', async ({ page }) => {
    await page.goto('http://localhost:8000');

    // Fill login form
    await page.fill('#username', 'admin');
    await page.fill('#password', 'admin123');

    // Submit form
    await page.click('.btn');

    // Wait for dashboard to appear
    await page.waitForSelector('.dashboard.active', { timeout: 5000 });

    // Verify dashboard is visible
    await expect(page.locator('.dashboard')).toHaveClass(/active/);
  });

  test('should display overview tab after login', async ({ page }) => {
    await page.goto('http://localhost:8000');

    // Login
    await page.fill('#username', 'admin');
    await page.fill('#password', 'admin123');
    await page.click('.btn');

    // Wait for dashboard
    await page.waitForSelector('.dashboard.active');

    // Check overview tab is active
    await expect(page.locator('[data-tab="overview"]')).toHaveClass(/active/);
    await expect(page.locator('#overviewTab')).toHaveClass(/active/);

    // Check stats are displayed
    await expect(page.locator('#totalOrders')).toBeVisible();
    await expect(page.locator('#totalRevenue')).toBeVisible();
    await expect(page.locator('#activeTables')).toBeVisible();
    await expect(page.locator('#pendingOrders')).toBeVisible();
  });

  test('should navigate between tabs', async ({ page }) => {
    await page.goto('http://localhost:8000');

    // Login
    await page.fill('#username', 'admin');
    await page.fill('#password', 'admin123');
    await page.click('.btn');

    // Wait for dashboard
    await page.waitForSelector('.dashboard.active');

    // Click Menu tab
    await page.click('[data-tab="menu"]');
    await expect(page.locator('[data-tab="menu"]')).toHaveClass(/active/);
    await expect(page.locator('#menuTab')).toHaveClass(/active/);

    // Click Tables tab
    await page.click('[data-tab="tables"]');
    await expect(page.locator('[data-tab="tables"]')).toHaveClass(/active/);
    await expect(page.locator('#tablesTab')).toHaveClass(/active/);

    // Click Inventory tab
    await page.click('[data-tab="inventory"]');
    await expect(page.locator('[data-tab="inventory"]')).toHaveClass(/active/);
    await expect(page.locator('#inventoryTab')).toHaveClass(/active/);

    // Click Kitchen tab
    await page.click('[data-tab="kitchen"]');
    await expect(page.locator('[data-tab="kitchen"]')).toHaveClass(/active/);
    await expect(page.locator('#kitchenTab')).toHaveClass(/active/);
  });

  test('should display menu data', async ({ page }) => {
    await page.goto('http://localhost:8000');

    // Login
    await page.fill('#username', 'admin');
    await page.fill('#password', 'admin123');
    await page.click('.btn');

    // Wait for dashboard
    await page.waitForSelector('.dashboard.active');

    // Navigate to Menu tab
    await page.click('[data-tab="menu"]');

    // Check menu categories are displayed
    await expect(page.locator('#menuCategories')).toBeVisible();
    await expect(page.locator('#menuProducts')).toBeVisible();
  });

  test('should display tables data', async ({ page }) => {
    await page.goto('http://localhost:8000');

    // Login
    await page.fill('#username', 'admin');
    await page.fill('#password', 'admin123');
    await page.click('.btn');

    // Wait for dashboard
    await page.waitForSelector('.dashboard.active');

    // Navigate to Tables tab
    await page.click('[data-tab="tables"]');

    // Check tables are displayed
    await expect(page.locator('#tablesList')).toBeVisible();
    await expect(page.locator('#availableTables')).toBeVisible();
  });

  test('should logout successfully', async ({ page }) => {
    await page.goto('http://localhost:8000');

    // Login
    await page.fill('#username', 'admin');
    await page.fill('#password', 'admin123');
    await page.click('.btn');

    // Wait for dashboard
    await page.waitForSelector('.dashboard.active');

    // Click logout
    await page.click('#logoutBtn');

    // Verify login section is visible again
    await expect(page.locator('.login-section')).toBeVisible();
    await expect(page.locator('.dashboard')).not.toHaveClass(/active/);
  });
});
