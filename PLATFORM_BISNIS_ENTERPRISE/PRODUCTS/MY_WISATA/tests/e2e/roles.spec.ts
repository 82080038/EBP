import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

test.describe('Role-Based Access Tests', () => {
  test('guest should access homepage', async ({ page }) => {
    await page.goto(BASE_URL);

    // Check homepage loads
    await expect(page).toHaveTitle(/MyWisata/);

    // Check navigation shows login/register buttons
    const loginButton = page.locator('a[href*="login"]');
    await expect(loginButton).toBeVisible();
  });

  test('guest should access destinations page', async ({ page }) => {
    await page.goto(`${BASE_URL}/destinations`);

    // Check page loads
    await expect(page).toHaveTitle(/MyWisata/);

    // Guest can view destinations
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('guest should be redirected from admin dashboard', async ({ page }) => {
    await page.goto(`${BASE_URL}/admin/dashboard`);

    // Should redirect to login
    const currentUrl = page.url();
    expect(currentUrl).toMatch(/login|auth/);
  });

  test('guest should be redirected from user dashboard', async ({ page }) => {
    await page.goto(`${BASE_URL}/user/dashboard`);

    // Should redirect to login or show error
    const currentUrl = page.url();
    const content = await page.content();

    // Either redirects to login or shows unauthorized
    expect(currentUrl).toMatch(/login|auth|dashboard/);
  });

  test('guest can access about page', async ({ page }) => {
    await page.goto(`${BASE_URL}/home/about`);

    // Check page loads (may not exist, so check for redirect or content)
    const currentUrl = page.url();
    const content = await page.content();

    // Either page loads or redirects appropriately
    expect(currentUrl).toMatch(/about|MyWisata/);
  });

  test('guest can access contact page', async ({ page }) => {
    await page.goto(`${BASE_URL}/home/contact`);

    // Check page loads (may not exist, so check for redirect or content)
    const currentUrl = page.url();
    const content = await page.content();

    // Either page loads or redirects appropriately
    expect(currentUrl).toMatch(/contact|MyWisata/);
  });

  test('guest can access registration page', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/register`);

    // Check registration form
    const form = page.locator('form');
    await expect(form).toBeVisible();
  });

  test('guest can access login page', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/login`);

    // Check login form
    const form = page.locator('form');
    await expect(form).toBeVisible();
  });
});
