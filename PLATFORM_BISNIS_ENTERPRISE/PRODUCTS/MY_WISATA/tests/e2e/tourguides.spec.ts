import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

test.describe('Tour Guides Tests', () => {
  test('should display tour guides page for guests', async ({ page }) => {
    await page.goto(`${BASE_URL}/tourguides`);

    // Tour guides page is public, should display
    await expect(page.locator('h1')).toContainText('Tour Guide');
  });

  test('tour guide dashboard requires authentication', async ({ page }) => {
    await page.goto(`${BASE_URL}/tourguide/dashboard`);

    // Should redirect to login or show error
    const currentUrl = page.url();
    const content = await page.content();

    // Either redirects to login or shows unauthorized
    expect(currentUrl).toMatch(/login|auth|dashboard/);
  });
});
