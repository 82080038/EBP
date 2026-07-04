import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

test.describe('Authentication Tests', () => {
  test('should display login page', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/login`);

    // Check page title
    await expect(page).toHaveTitle(/Login|Masuk/);

    // Check for login form
    const loginForm = page.locator('form');
    await expect(loginForm).toBeVisible();

    // Check for email input
    const emailInput = page.locator('input[type="email"], input[name="email"]');
    await expect(emailInput).toBeVisible();

    // Check for password input
    const passwordInput = page.locator('input[type="password"], input[name="password"]');
    await expect(passwordInput).toBeVisible();

    // Check for submit button
    const submitButton = page.locator('button[type="submit"], input[type="submit"]');
    await expect(submitButton).toBeVisible();
  });

  test('should display register page', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/register`);

    // Check page title
    await expect(page).toHaveTitle(/Register|Daftar/);

    // Check for register form
    const registerForm = page.locator('form');
    await expect(registerForm).toBeVisible();
  });

  test('should have CSRF token in login form', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/login`);

    // Check for CSRF token
    const csrfToken = page.locator('input[name="csrf_token"]');
    const csrfTokenCount = await csrfToken.count();

    // CSRF token should be present
    expect(csrfTokenCount).toBeGreaterThan(0);
  });

  test('should redirect to login when accessing protected route', async ({ page }) => {
    // Try to access admin dashboard without login
    await page.goto(`${BASE_URL}/admin/dashboard`);

    // Should redirect to login page
    const currentUrl = page.url();
    expect(currentUrl).toMatch(/auth\/login|login/);
  });

  test('should have forgot password link', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/login`);

    // Check for forgot password link
    const forgotLink = page.locator('a[href*="forgot"], a[href*="lupa"]');
    const linkCount = await forgotLink.count();

    // Forgot password link should be present
    expect(linkCount).toBeGreaterThan(0);
  });
});
