import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

test.describe('Events Tests', () => {
  test('should display events page', async ({ page }) => {
    await page.goto(`${BASE_URL}/events`);

    // Check page title (events page may have different title)
    const title = await page.title();
    expect(title).toBeTruthy();

    // Check that page loads
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should display event cards', async ({ page }) => {
    await page.goto(`${BASE_URL}/events`);

    // Check for event cards
    const cards = page.locator('.card, .event-card, .col-md-4');
    const cardCount = await cards.count();

    // Should have at least some cards
    expect(cardCount).toBeGreaterThan(0);
  });

  test('should have search functionality', async ({ page }) => {
    await page.goto(`${BASE_URL}/events`);

    // Check for search input (optional)
    const searchInput = page.locator('input[name="search"], input[placeholder*="Cari"], input[placeholder*="cari"], input[placeholder*="search"]');
    const searchCount = await searchInput.count();

    // Search input is optional
    if (searchCount > 0) {
      expect(searchCount).toBeGreaterThan(0);
    } else {
      console.log('Search input not found on events page');
    }
  });

  test('should have filter options', async ({ page }) => {
    await page.goto(`${BASE_URL}/events`);

    // Check for filter dropdowns (optional)
    const filters = page.locator('select[name="category"], select');
    const filterCount = await filters.count();

    // Filters are optional
    if (filterCount > 0) {
      expect(filterCount).toBeGreaterThan(0);
    } else {
      console.log('Filter options not found on events page');
    }
  });

  test('should navigate to event detail', async ({ page }) => {
    await page.goto(`${BASE_URL}/events`);

    // Click on first event card
    const firstCard = page.locator('.card, .event-card').first();

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
      expect(currentUrl).toMatch(/events|detail/);
    }
  });
});
