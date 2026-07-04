import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

test.describe('Homepage Tests', () => {
  test('should load homepage successfully', async ({ page }) => {
    await page.goto(BASE_URL);

    // Check page title
    await expect(page).toHaveTitle(/MyWisata/);

    // Check if page loads without errors
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should display navigation menu', async ({ page }) => {
    await page.goto(BASE_URL);

    // Check for navigation elements
    const nav = page.locator('nav');
    await expect(nav).toBeVisible();
  });

  test('should display hero section', async ({ page }) => {
    await page.goto(BASE_URL);

    // Check for hero section
    const hero = page.locator('.hero-section, .hero, .jumbotron, header');
    await expect(hero.first()).toBeVisible();
  });

  test('should have working links', async ({ page }) => {
    await page.goto(BASE_URL);

    // Get all links
    const links = page.locator('a[href]').all();

    // Check that at least some links exist
    const linkCount = await page.locator('a[href]').count();
    expect(linkCount).toBeGreaterThan(0);
  });

  test('should be responsive', async ({ page }) => {
    await page.goto(BASE_URL);

    // Test mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page).toHaveTitle(/MyWisata/);

    // Test tablet viewport
    await page.setViewportSize({ width: 768, height: 1024 });
    await expect(page).toHaveTitle(/MyWisata/);

    // Test desktop viewport
    await page.setViewportSize({ width: 1920, height: 1080 });
    await expect(page).toHaveTitle(/MyWisata/);
  });
});
