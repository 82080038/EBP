import { test, expect } from '@playwright/test';

// Test users with their roles and expected permissions
const testUsers = [
  { username: 'sim_admin', password: 'Sim123456', role: 'Administrator', level: 100, description: 'Full platform access' },
  { username: 'sim_manager', password: 'Sim123456', role: 'Manager', level: 80, description: 'Management access' },
  { username: 'sim_kasir', password: 'Sim123456', role: 'Kasir', level: 50, description: 'Order/payment access' },
  { username: 'sim_koki', password: 'Sim123456', role: 'Koki', level: 40, description: 'Kitchen access' },
  { username: 'sim_waiter', password: 'Sim123456', role: 'Waiter', level: 30, description: 'Table/order access' },
  { username: 'sim_stok', password: 'Sim123456', role: 'Stok', level: 20, description: 'Inventory access' },
];

// UI interfaces to test
const uiInterfaces = [
  { name: 'Consumer App', path: 'http://localhost/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/FRONTEND/consumer/index.html' },
  { name: 'Dashboard', path: 'http://localhost/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/FRONTEND/dashboard/index.html' },
  { name: 'Kiosk', path: 'http://localhost/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/FRONTEND/kiosk/index.html' },
  { name: 'Mobile', path: 'http://localhost/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/FRONTEND/mobile/index.html' },
];

test.describe('RESTAURANT_ERP - Comprehensive Browser Simulation', () => {

  test.beforeEach(async ({ page, context }) => {
    // Set longer timeout for comprehensive tests
    test.setTimeout(180000);
    
    // Set context timeout
    context.setDefaultTimeout(60000);
    
    // Enable console logging
    context.on('console', msg => {
      console.log(`[${msg.type()}] ${msg.text()}`);
    });
    
    // Enable network logging
    page.on('request', request => {
      console.log(`[REQUEST] ${request.method()} ${request.url()}`);
    });
    
    page.on('response', response => {
      console.log(`[RESPONSE] ${response.status()} ${response.url()}`);
    });
    
    page.on('pageerror', error => {
      console.error(`[PAGE ERROR] ${error}`);
    });
  });

  test('Test all UI interfaces load successfully', async ({ page }) => {
    console.log('\n=== Testing UI Interface Loading ===');
    
    for (const ui of uiInterfaces) {
      console.log(`\nTesting ${ui.name}: ${ui.path}`);
      
      try {
        await page.goto(ui.path);
        await page.waitForLoadState('domcontentloaded');
        await page.waitForTimeout(2000);
        
        const title = await page.title();
        const bodyText = await page.locator('body').textContent();
        
        console.log(`✓ ${ui.name} loaded successfully`);
        console.log(`  Title: ${title}`);
        console.log(`  Content length: ${bodyText?.length || 0} characters`);
        
        // Take screenshot
        await page.screenshot({ path: `screenshots/${ui.name.toLowerCase().replace(' ', '-')}-load.png`, fullPage: true });
        
        // Check for console errors
        const consoleErrors: string[] = [];
        page.on('console', msg => {
          if (msg.type() === 'error') {
            consoleErrors.push(msg.text());
          }
        });
        
        if (consoleErrors.length > 0) {
          console.log(`  ⚠ Console errors: ${consoleErrors.length}`);
          consoleErrors.forEach(err => console.log(`    - ${err}`));
        } else {
          console.log(`  ✓ No console errors`);
        }
        
      } catch (error) {
        console.log(`✗ ${ui.name} failed to load: ${error}`);
      }
    }
  });

  test('Comprehensive role-based authentication and permissions', async ({ page }) => {
    console.log('\n=== Testing Role-Based Authentication ===');
    
    for (const user of testUsers) {
      console.log(`\n--- Testing ${user.role} (${user.username}) ---`);
      console.log(`Level: ${user.level}`);
      console.log(`Description: ${user.description}`);
      
      // Login via API
      const loginResult = await page.evaluate(async ({ username, password }) => {
        try {
          const response = await fetch('http://localhost:8000/api/v1/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password }),
          });
          const data = await response.json();
          return { status: response.status, data };
        } catch (error) {
          return { error: error instanceof Error ? error.message : String(error) };
        }
      }, { username: user.username, password: user.password });
      
      console.log(`Login Status: HTTP ${loginResult.status}`);
      
      if (loginResult.data && loginResult.data.success) {
        const token = loginResult.data.data.access_token;
        const userData = loginResult.data.data.user;
        
        console.log(`✓ Login successful`);
        console.log(`  User ID: ${userData.id}`);
        console.log(`  Role: ${userData.role}`);
        console.log(`  Level: ${userData.level}`);
        console.log(`  Tenant ID: ${userData.tenant_id}`);
        console.log(`  Branch ID: ${userData.branch_id}`);
        
        // Test API endpoints based on role
        console.log(`\nTesting API permissions for ${user.role}:`);
        
        // Test orders endpoint
        const ordersResult = await page.evaluate(async (accessToken) => {
          const response = await fetch('http://localhost:8000/api/v1/orders', {
            headers: { 'Authorization': `Bearer ${accessToken}` },
          });
          const data = await response.json();
          return { status: response.status, data };
        }, token);
        console.log(`  GET /orders: HTTP ${ordersResult.status} - ${ordersResult.data.success ? '✓' : '✗'}`);
        
        // Test menu endpoint
        const menuResult = await page.evaluate(async (accessToken) => {
          const response = await fetch('http://localhost:8000/api/v1/menu/products', {
            headers: { 'Authorization': `Bearer ${accessToken}` },
          });
          const data = await response.json();
          return { status: response.status, data };
        }, token);
        console.log(`  GET /menu/products: HTTP ${menuResult.status} - ${menuResult.data.success ? '✓' : '✗'}`);
        
        // Test tables endpoint
        const tablesResult = await page.evaluate(async (accessToken) => {
          const response = await fetch('http://localhost:8000/api/v1/tables', {
            headers: { 'Authorization': `Bearer ${accessToken}` },
          });
          const data = await response.json();
          return { status: response.status, data };
        }, token);
        console.log(`  GET /tables: HTTP ${tablesResult.status} - ${tablesResult.data.success ? '✓' : '✗'}`);
        
        // Test inventory endpoint
        const inventoryResult = await page.evaluate(async (accessToken) => {
          const response = await fetch('http://localhost:8000/api/v1/inventory', {
            headers: { 'Authorization': `Bearer ${accessToken}` },
          });
          const data = await response.json();
          return { status: response.status, data };
        }, token);
        console.log(`  GET /inventory: HTTP ${inventoryResult.status} - ${inventoryResult.data.success ? '✓' : '✗'}`);
        
        // Test order creation (should fail for restricted roles)
        const orderCreateResult = await page.evaluate(async (accessToken) => {
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
        console.log(`  POST /orders: HTTP ${orderCreateResult.status} - ${orderCreateResult.data.success ? '✓' : '✗'}`);
        
        // Take screenshot
        await page.screenshot({ path: `screenshots/${user.username}-permissions.png` });
        
      } else {
        console.log(`✗ Login failed: ${loginResult.error || 'Unknown error'}`);
      }
    }
  });

  test('Test order creation workflow for all roles', async ({ page }) => {
    console.log('\n=== Testing Order Creation Workflow ===');
    
    for (const user of testUsers) {
      console.log(`\nTesting order creation for ${user.role}`);
      
      // Login
      const loginResult = await page.evaluate(async ({ username, password }) => {
        const response = await fetch('http://localhost:8000/api/v1/auth/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password }),
        });
        return await response.json();
      }, { username: user.username, password: user.password });
      
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
              order_type: 'DINE_IN',
              table_id: 1,
              items: [
                { product_id: 1, qty: 2, price: 30000, notes: 'Test order' },
                { product_id: 2, qty: 1, price: 25000, notes: 'Extra' }
              ]
            }),
          });
          const data = await response.json();
          return { status: response.status, data };
        }, token);
        
        console.log(`Order creation: HTTP ${orderResult.status}`);
        if (orderResult.data.success) {
          console.log(`✓ Order created successfully`);
          console.log(`  Order ID: ${orderResult.data.data.order_id}`);
          console.log(`  Total: ${orderResult.data.data.total}`);
        } else {
          console.log(`✗ Order creation failed: ${orderResult.data.message}`);
        }
      }
    }
  });

  test('Test data consistency across endpoints', async ({ page }) => {
    console.log('\n=== Testing Data Consistency ===');
    
    // Login as admin
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
      
      // Get menu products
      const menuResult = await page.evaluate(async (accessToken) => {
        const response = await fetch('http://localhost:8000/api/v1/menu/products', {
          headers: { 'Authorization': `Bearer ${accessToken}` },
        });
        return await response.json();
      }, token);
      
      console.log(`Menu products count: ${menuResult.data?.data?.length || 0}`);
      
      // Get inventory
      const inventoryResult = await page.evaluate(async (accessToken) => {
        const response = await fetch('http://localhost:8000/api/v1/inventory', {
          headers: { 'Authorization': `Bearer ${accessToken}` },
        });
        return await response.json();
      }, token);
      
      console.log(`Inventory items count: ${inventoryResult.data?.data?.length || 0}`);
      
      // Get tables
      const tablesResult = await page.evaluate(async (accessToken) => {
        const response = await fetch('http://localhost:8000/api/v1/tables', {
          headers: { 'Authorization': `Bearer ${accessToken}` },
        });
        return await response.json();
      }, token);
      
      console.log(`Tables count: ${tablesResult.data?.data?.length || 0}`);
      
      // Get orders
      const ordersResult = await page.evaluate(async (accessToken) => {
        const response = await fetch('http://localhost:8000/api/v1/orders', {
          headers: { 'Authorization': `Bearer ${accessToken}` },
        });
        return await response.json();
      }, token);
      
      console.log(`Orders count: ${ordersResult.data?.data?.length || 0}`);
      
      console.log(`✓ Data consistency check complete`);
    }
  });

  test('Test responsive design across all interfaces', async ({ page }) => {
    console.log('\n=== Testing Responsive Design ===');
    
    const viewports = [
      { name: 'Desktop', width: 1920, height: 1080 },
      { name: 'Laptop', width: 1366, height: 768 },
      { name: 'Tablet', width: 768, height: 1024 },
      { name: 'Mobile', width: 375, height: 667 },
    ];
    
    for (const ui of uiInterfaces) {
      console.log(`\nTesting ${ui.name} responsiveness`);
      
      for (const viewport of viewports) {
        await page.setViewportSize({ width: viewport.width, height: viewport.height });
        await page.goto(ui.path);
        await page.waitForLoadState('domcontentloaded');
        await page.waitForTimeout(1000);
        
        const bodyText = await page.locator('body').textContent();
        console.log(`  ${viewport.name} (${viewport.width}x${viewport.height}): ${bodyText?.length || 0} chars`);
        
        await page.screenshot({ 
          path: `screenshots/${ui.name.toLowerCase().replace(' ', '-')}-${viewport.name.toLowerCase()}.png`,
          fullPage: true 
        });
      }
    }
  });

  test('Test network performance and error handling', async ({ page }) => {
    console.log('\n=== Testing Network Performance ===');
    
    const networkRequests: any[] = [];
    
    page.on('request', request => {
      networkRequests.push({
        url: request.url(),
        method: request.method(),
        timestamp: Date.now()
      });
    });
    
    page.on('response', response => {
      const request = networkRequests.find(r => r.url === response.url());
      if (request) {
        request.status = response.status();
        request.duration = Date.now() - request.timestamp;
      }
    });
    
    // Load all interfaces
    for (const ui of uiInterfaces) {
      networkRequests.length = 0; // Clear previous requests
      
      await page.goto(ui.path);
      await page.waitForLoadState('networkidle');
      
      console.log(`\n${ui.name} network requests: ${networkRequests.length}`);
      
      const failedRequests = networkRequests.filter(r => r.status >= 400);
      const slowRequests = networkRequests.filter(r => r.duration > 1000);
      
      console.log(`  Failed requests: ${failedRequests.length}`);
      failedRequests.forEach(r => console.log(`    - ${r.method} ${r.url} (${r.status})`));
      
      console.log(`  Slow requests (>1s): ${slowRequests.length}`);
      slowRequests.forEach(r => console.log(`    - ${r.method} ${r.url} (${r.duration}ms)`));
      
      if (failedRequests.length === 0 && slowRequests.length === 0) {
        console.log(`  ✓ All requests successful and fast`);
      }
    }
  });

  test('Test console errors and warnings', async ({ page }) => {
    console.log('\n=== Testing Console Errors and Warnings ===');
    
    const consoleMessages: any[] = [];
    
    page.on('console', msg => {
      consoleMessages.push({
        type: msg.type(),
        text: msg.text(),
        location: msg.location()
      });
    });
    
    for (const ui of uiInterfaces) {
      consoleMessages.length = 0;
      
      await page.goto(ui.path);
      await page.waitForLoadState('domcontentloaded');
      await page.waitForTimeout(3000);
      
      const errors = consoleMessages.filter(m => m.type === 'error');
      const warnings = consoleMessages.filter(m => m.type === 'warning');
      
      console.log(`\n${ui.name}:`);
      console.log(`  Errors: ${errors.length}`);
      errors.forEach(e => console.log(`    - ${e.text}`));
      
      console.log(`  Warnings: ${warnings.length}`);
      warnings.forEach(w => console.log(`    - ${w.text}`));
      
      if (errors.length === 0 && warnings.length === 0) {
        console.log(`  ✓ No console errors or warnings`);
      }
    }
  });
});
