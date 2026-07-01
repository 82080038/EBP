import { test } from '@playwright/test';

const restaurantTypes = [
  { code: 'RESTAURANT', name: 'Restoran Makanan', username: 'restaurant_manager' },
  { code: 'CAFE', name: 'Kafe', username: 'cafe_manager' },
  { code: 'BAR_PUB', name: 'Bar/Pub', username: 'bar_pub_manager' },
  { code: 'FOOD_COURT', name: 'Food Court', username: 'food_court_manager' },
  { code: 'CATERING', name: 'Catering Service', username: 'catering_manager' },
  { code: 'FAST_FOOD', name: 'Fast Food Restaurant', username: 'fast_food_manager' },
  { code: 'FINE_DINING', name: 'Fine Dining', username: 'fine_dining_manager' },
  { code: 'COFFEE_SHOP', name: 'Coffee Shop', username: 'coffee_shop_manager' }
];

test.describe('All Restaurant Types Simulation', () => {
  
  restaurantTypes.forEach(restaurant => {
    test(`should login and view dashboard for ${restaurant.name}`, async ({ page }) => {
      await page.goto('http://localhost:8000');
      
      // Login with restaurant-specific credentials
      await page.fill('#username', restaurant.username);
      await page.fill('#password', 'manager123');
      await page.click('.btn');
      
      // Wait for dashboard
      await page.waitForSelector('.dashboard.active', { timeout: 5000 });
      
      // Verify dashboard is visible
      await page.waitForSelector('#totalOrders');
      await page.waitForSelector('#totalRevenue');
      
      // Take a moment to view the dashboard
      await page.waitForTimeout(2000);
      
      // Navigate through tabs
      await page.click('[data-tab="menu"]');
      await page.waitForTimeout(1000);
      
      await page.click('[data-tab="tables"]');
      await page.waitForTimeout(1000);
      
      await page.click('[data-tab="inventory"]');
      await page.waitForTimeout(1000);
      
      if (restaurant.code === 'RESTAURANT' || restaurant.code === 'CATERING' || restaurant.code === 'FINE_DINING') {
        await page.click('[data-tab="orders"]');
        await page.waitForTimeout(1000);
      }
      
      await page.click('[data-tab="overview"]');
      await page.waitForTimeout(1000);
      
      // Logout
      await page.click('#logoutBtn');
      await page.waitForSelector('.login-section');
    });
  });
  
  test('should display summary of all restaurant types', async ({ page }) => {
    await page.goto('http://localhost:8000');
    
    // Login as admin to see overview
    await page.fill('#username', 'admin');
    await page.fill('#password', 'admin123');
    await page.click('.btn');
    
    await page.waitForSelector('.dashboard.active');
    await page.waitForTimeout(3000);
    
    // Navigate through tabs
    await page.click('[data-tab="menu"]');
    await page.waitForTimeout(2000);
    
    await page.click('[data-tab="tables"]');
    await page.waitForTimeout(2000);
    
    await page.click('[data-tab="inventory"]');
    await page.waitForTimeout(2000);
    
    await page.click('[data-tab="kitchen"]');
    await page.waitForTimeout(2000);
    
    await page.click('[data-tab="overview"]');
    await page.waitForTimeout(2000);
  });
});
