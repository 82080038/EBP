import { test, expect } from '@playwright/test';

// Test users with their roles
const testUsers = [
  { username: 'sim_admin', password: 'Sim123456', role: 'Administrator', description: 'Full access' },
  { username: 'sim_manager', password: 'Sim123456', role: 'Manager', description: 'Management access' },
  { username: 'sim_kasir', password: 'Sim123456', role: 'Kasir', description: 'Order/payment access' },
  { username: 'sim_koki', password: 'Sim123456', role: 'Koki', description: 'Kitchen access' },
  { username: 'sim_waiter', password: 'Sim123456', role: 'Waiter', description: 'Table/order access' },
  { username: 'sim_stok', password: 'Sim123456', role: 'Stok', description: 'Inventory access' },
];

test.describe('RESTAURANT_ERP Browser Simulation', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to the application
    await page.goto('/');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
  });

  test('Application loads successfully', async ({ page }) => {
    // Check if the page loads
    const title = await page.title();
    console.log('Page title:', title);
    
    // Take a screenshot
    await page.screenshot({ path: 'screenshots/app-load.png' });
  });

  for (const user of testUsers) {
    test(`Login as ${user.role} (${user.username}) via API`, async ({ page }) => {
      console.log(`\nTesting login for ${user.role} (${user.username})`);
      
      // Login via API call
      const loginResponse = await page.evaluate(async ({ username, password }) => {
        try {
          const response = await fetch('http://localhost:8000/api/v1/auth/login', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username, password }),
          });
          const data = await response.json();
          return { status: response.status, data };
        } catch (error) {
          return { error: error instanceof Error ? error.message : String(error) };
        }
      }, { username: user.username, password: user.password });
      
      console.log(`Login response for ${user.username}:`, JSON.stringify(loginResponse, null, 2));
      
      if (loginResponse.data && loginResponse.data.success) {
        // Store token in localStorage
        await page.evaluate((token) => {
          localStorage.setItem('access_token', token);
        }, loginResponse.data.data.access_token);
        
        // Take screenshot
        await page.screenshot({ path: `screenshots/${user.username}-api-login.png` });
        
        console.log(`✓ ${user.username} login successful`);
      } else {
        console.log(`✗ ${user.username} login failed`);
      }
    });
  }

  test('Test API endpoints directly', async ({ page }) => {
    console.log('\nTesting API endpoints directly');
    
    // Test login
    const loginResult = await page.evaluate(async () => {
      const response = await fetch('http://localhost:8000/api/v1/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: 'sim_admin', password: 'Sim123456' }),
      });
      return await response.json();
    });
    
    console.log('Login result:', JSON.stringify(loginResult, null, 2));
    
    if (loginResult.success) {
      const token = loginResult.data.access_token;
      
      // Test get orders
      const ordersResult = await page.evaluate(async (accessToken) => {
        const response = await fetch('http://localhost:8000/api/v1/orders', {
          headers: { 'Authorization': `Bearer ${accessToken}` },
        });
        return await response.json();
      }, token);
      
      console.log('Orders result:', JSON.stringify(ordersResult, null, 2));
      
      // Test get menu
      const menuResult = await page.evaluate(async (accessToken) => {
        const response = await fetch('http://localhost:8000/api/v1/menu/products', {
          headers: { 'Authorization': `Bearer ${accessToken}` },
        });
        return await response.json();
      }, token);
      
      console.log('Menu result:', JSON.stringify(menuResult, null, 2));
      
      // Test get tables
      const tablesResult = await page.evaluate(async (accessToken) => {
        const response = await fetch('http://localhost:8000/api/v1/tables', {
          headers: { 'Authorization': `Bearer ${accessToken}` },
        });
        return await response.json();
      }, token);
      
      console.log('Tables result:', JSON.stringify(tablesResult, null, 2));
      
      // Test get inventory
      const inventoryResult = await page.evaluate(async () => {
        const response = await fetch('http://localhost:8000/api/v1/public/inventory');
        return await response.json();
      });
      
      console.log('Inventory result:', JSON.stringify(inventoryResult, null, 2));
    }
  });

  test('Test order creation in browser', async ({ page }) => {
    console.log('\nTesting order creation');
    
    // Login first
    const loginResult = await page.evaluate(async () => {
      const response = await fetch('http://localhost:8000/api/v1/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: 'sim_admin', password: 'Sim123456' }),
      });
      return await response.json();
    });
    
    if (loginResult.success) {
      const token = loginResult.data.access_token;
      
      // Create order
      const orderResult = await page.evaluate(async (accessToken) => {
        const response = await fetch('http://localhost:8000/api/v1/orders', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${accessToken}`,
          },
          body: JSON.stringify({
            order_type: 'TAKE_AWAY',
            items: [
              {
                product_id: 1,
                qty: 2,
                price: 30000,
                notes: 'Browser test order'
              }
            ]
          }),
        });
        return await response.json();
      }, token);
      
      console.log('Order creation result:', JSON.stringify(orderResult, null, 2));
      
      // Take screenshot
      await page.screenshot({ path: 'screenshots/order-creation-test.png' });
    }
  });

  test('Test role-based access control', async ({ page }) => {
    console.log('\nTesting role-based access control');
    
    const restrictedUsers = ['sim_kasir', 'sim_koki', 'sim_stok'];
    
    for (const username of restrictedUsers) {
      console.log(`\nTesting restricted user: ${username}`);
      
      // Login
      const loginResult = await page.evaluate(async (user) => {
        const response = await fetch('http://localhost:8000/api/v1/auth/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username: user, password: 'Sim123456' }),
        });
        return await response.json();
      }, username);
      
      if (loginResult.success) {
        const token = loginResult.data.access_token;
        
        // Try to create order (should fail for some roles)
        const orderResult = await page.evaluate(async (accessToken) => {
          const response = await fetch('http://localhost:8000/api/v1/orders', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${accessToken}`,
            },
            body: JSON.stringify({
              order_type: 'TAKE_AWAY',
              items: [{ product_id: 1, qty: 1, price: 30000 }]
            }),
          });
          const data = await response.json();
          return { status: response.status, data };
        }, token);
        
        console.log(`${username} order creation: HTTP ${orderResult.status} - ${orderResult.data.success ? 'Success' : 'Failed'}`);
        
        // Try to access tables (should fail for some roles)
        const tablesResult = await page.evaluate(async (accessToken) => {
          const response = await fetch('http://localhost:8000/api/v1/tables', {
            headers: { 'Authorization': `Bearer ${accessToken}` },
          });
          const data = await response.json();
          return { status: response.status, data };
        }, token);
        
        console.log(`${username} tables access: HTTP ${tablesResult.status} - ${tablesResult.data.success ? 'Success' : 'Failed'}`);
      }
    }
  });

  test('Visual inspection of UI', async ({ page }) => {
    console.log('\nVisual inspection of UI');
    
    // Navigate to main page
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    
    // Take full page screenshot
    await page.screenshot({ 
      path: 'screenshots/full-page-ui.png',
      fullPage: true 
    });
    
    // Check for common UI elements
    const hasNavigation = await page.locator('nav').count() > 0;
    const hasHeader = await page.locator('header').count() > 0;
    const hasMain = await page.locator('main').count() > 0;
    
    console.log('UI Elements found:');
    console.log(`  Navigation: ${hasNavigation ? 'Yes' : 'No'}`);
    console.log(`  Header: ${hasHeader ? 'Yes' : 'No'}`);
    console.log(`  Main content: ${hasMain ? 'Yes' : 'No'}`);
    
    // Get page text content
    const pageText = await page.locator('body').textContent();
    console.log(`Page text length: ${pageText?.length || 0} characters`);
  });
});
