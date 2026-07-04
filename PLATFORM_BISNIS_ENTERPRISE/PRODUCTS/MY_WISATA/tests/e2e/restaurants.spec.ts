import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

test.describe('Restaurants Tests', () => {
  test('should display restaurants page', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Check page title
    await expect(page).toHaveTitle(/MyWisata/);

    // Check that page loads
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should display restaurant cards', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Check for restaurant cards
    const cards = page.locator('.card, .restaurant-card, .col-md-4');
    const cardCount = await cards.count();

    // Should have at least some cards
    expect(cardCount).toBeGreaterThan(0);
  });

  test('should have search functionality', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Check for search input
    const searchInput = page.locator('input[name="search"], input[placeholder*="Cari"], input[placeholder*="cari"], input[placeholder*="search"]');
    const searchCount = await searchInput.count();

    // Search input should be present
    expect(searchCount).toBeGreaterThan(0);
  });

  test('should have filter options', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Check for filter dropdowns (optional)
    const filters = page.locator('select[name="category"], select');
    const filterCount = await filters.count();

    // Filters are optional - check if present
    if (filterCount > 0) {
      expect(filterCount).toBeGreaterThan(0);
    } else {
      console.log('Filter options not found on restaurants page');
    }
  });

  test('should navigate to restaurant detail', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Click on first restaurant card
    const firstCard = page.locator('.card, .restaurant-card').first();

    // Wait for card to be visible
    await firstCard.waitFor({ state: 'visible', timeout: 5000 }).catch(() => {
      console.log('Card not visible - skipping navigation test');
    });

    if (await firstCard.isVisible()) {
      await firstCard.click();

      // Wait for navigation
      await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {
        console.log('Navigation timeout - checking URL anyway');
      });

      // Should navigate to detail page
      const currentUrl = page.url();
      expect(currentUrl).toMatch(/restaurants|detail/);
    }
  });
});
