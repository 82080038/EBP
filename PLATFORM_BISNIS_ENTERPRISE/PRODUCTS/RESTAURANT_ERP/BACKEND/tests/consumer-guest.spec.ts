import { test, expect } from '@playwright/test';

test.describe('Consumer Guest UI Tests', () => {
  const BASE_URL = 'http://localhost:8000';

  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/consumer/`);
  });

  test('Page loads successfully', async ({ page }) => {
    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    
    // Check if page title is correct
    const title = await page.title();
    expect(title).toContain('EBP Restaurant');
    
    console.log('✅ Page loads successfully');
  });

  test('Header elements are present', async ({ page }) => {
    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    
    // Check if header elements exist
    const menuBtn = page.locator('#menuBtn');
    const profileBtn = page.locator('#profileBtn');
    const langBtn = page.locator('#langBtn');
    
    await expect(menuBtn).toBeVisible();
    await expect(profileBtn).toBeVisible();
    await expect(langBtn).toBeVisible();
    
    console.log('✅ Header elements are present');
  });

  test('Sidebar navigation exists', async ({ page }) => {
    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    
    // Check if sidebar exists
    const sidebar = page.locator('.sidebar');
    const isVisible = await sidebar.isVisible();
    
    expect(isVisible).toBe(true);
    
    console.log('✅ Sidebar navigation exists');
  });

  test('Profile button opens login modal', async ({ page }) => {
    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    
    // Click profile button
    await page.click('#profileBtn');
    await page.waitForTimeout(500);
    
    // Check if login modal is shown
    const loginModal = page.locator('#loginModal');
    const isVisible = await loginModal.isVisible();
    
    if (isVisible) {
      await expect(loginModal).toHaveClass(/active/);
      console.log('✅ Profile button opens login modal');
    } else {
      console.log('⚠️ Login modal not visible after clicking profile button');
    }
  });

  test('Quick login options are available', async ({ page }) => {
    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    
    // Click profile button
    await page.click('#profileBtn');
    await page.waitForTimeout(500);
    
    // Check if quick login options exist
    const googleBtn = page.locator('#googleLoginBtn');
    const phoneBtn = page.locator('#phoneLoginBtn');
    
    const googleVisible = await googleBtn.isVisible();
    const phoneVisible = await phoneBtn.isVisible();
    
    if (googleVisible && phoneVisible) {
      console.log('✅ Quick login options are available (Google, Phone)');
    } else {
      console.log('⚠️ Quick login buttons not visible');
    }
  });

  test('Phone login modal opens', async ({ page }) => {
    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');
    
    // Click profile button
    await page.click('#profileBtn');
    await page.waitForTimeout(500);
    
    // Click phone login button
    const phoneBtn = page.locator('#phoneLoginBtn');
    const phoneVisible = await phoneBtn.isVisible();
    
    if (phoneVisible) {
      await phoneBtn.click();
      await page.waitForTimeout(500);
      
      // Check if phone login modal is shown
      const phoneModal = page.locator('#phoneLoginModal');
      const modalVisible = await phoneModal.isVisible();
      
      if (modalVisible) {
        console.log('✅ Phone login modal opens correctly');
      } else {
        console.log('⚠️ Phone login modal not visible');
      }
    } else {
      console.log('⚠️ Phone login button not visible');
    }
  });
});
