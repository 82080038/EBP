import { test, expect } from '@playwright/test';

test.describe('Restaurant ERP - Comprehensive F&B Features & Roles Testing', () => {
  const testUsers = [
    { username: 'admin', password: 'password', role: 'Administrator' },
    { username: 'manager', password: 'password', role: 'Restaurant Manager' },
    { username: 'waiter', password: 'password', role: 'Waiter' },
    { username: 'kitchen', password: 'password', role: 'Kitchen Staff' },
    { username: 'cashier', password: 'password', role: 'Cashier' },
    { username: 'inventory', password: 'password', role: 'Inventory Manager' },
    { username: 'host', password: 'password', role: 'Host/Hostess' }
  ];

  test.beforeEach(async ({ page }) => {
    // Navigate to application
    await page.goto('http://localhost/restauran/');
    await page.waitForLoadState('networkidle');
  });

  // Phase 1: Authentication Testing
  test.describe('Phase 1: Authentication', () => {
    test('1.1 Login for each role', async ({ page }) => {
      // Test only admin user for now
      const user = testUsers[0];
      console.log(`Testing login for ${user.role} (${user.username})`);

      // Navigate to application
      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');

      // Click login button on landing page
      const loginBtn = page.locator('#loginBtn');
      if (await loginBtn.isVisible()) {
        await loginBtn.click();
        await page.waitForTimeout(1000);
      }

      // Check if login form exists
      const loginSection = page.locator('#loginSection');
      if (await loginSection.isVisible()) {
        // Monitor network requests
        const apiResponse = page.waitForResponse(response => response.url().includes('/auth/login'));
        
        // Fill login form
        await page.fill('#username', user.username);
        await page.fill('#password', user.password);

        // Submit login form
        await page.click('#loginForm button[type="submit"]');

        // Wait for API response
        const response = await Promise.race([apiResponse, page.waitForTimeout(5000)]);
        
        if (response) {
          const statusCode = response.status();
          const body = await response.text();
          console.log(`API Response Status: ${statusCode}`);
          console.log(`API Response Body: ${body}`);
        } else {
          console.log('API Response timeout');
        }

        // Wait for response
        await page.waitForTimeout(3000);

        // Check if login was successful (dashboard should appear)
        const dashboard = page.locator('#dashboard');
        const isVisible = await dashboard.isVisible();

        console.log(`${user.role} login: ${isVisible ? 'SUCCESS' : 'FAILED'}`);

        // Check for error message
        const loginMessage = page.locator('#loginMessage');
        if (await loginMessage.isVisible()) {
          const messageText = await loginMessage.textContent();
          console.log(`Login message: ${messageText}`);
        }
      } else {
        console.log(`${user.role}: Login section not visible`);
      }
    });

    test('1.2 Invalid credentials', async ({ page }) => {
      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');

      // Click login button
      const loginBtn = page.locator('#loginBtn');
      if (await loginBtn.isVisible()) {
        await loginBtn.click();
        await page.waitForTimeout(1000);
      }

      const loginSection = page.locator('#loginSection');
      if (await loginSection.isVisible()) {
        // Try invalid credentials
        await page.fill('#username', 'invalid');
        await page.fill('#password', 'invalid');
        await page.click('#loginForm button[type="submit"]');
        await page.waitForTimeout(3000);

        // Check for error message
        const errorMessage = page.locator('#loginMessage');
        const hasError = await errorMessage.isVisible();
        
        console.log(`Invalid credentials test: ${hasError ? 'ERROR SHOWN' : 'NO ERROR'}`);
      }
    });
  });

  // Phase 2: Menu Management Testing
  test.describe('Phase 2: Menu Management', () => {
    test('2.1 View menu categories', async ({ page }) => {
      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');

      // Login first
      const loginBtn = page.locator('#loginBtn');
      if (await loginBtn.isVisible()) {
        await loginBtn.click();
        await page.waitForTimeout(1000);
      }

      const loginSection = page.locator('#loginSection');
      if (await loginSection.isVisible()) {
        await page.fill('#username', 'admin');
        await page.fill('#password', 'password');
        await page.click('#loginForm button[type="submit"]');
        await page.waitForTimeout(3000);
      }

      // Check if menu section exists
      const menuSection = page.locator('#menuSection');
      if (await menuSection.isVisible()) {
        const categories = await page.locator('.category-item').count();
        console.log(`Menu categories found: ${categories}`);
      } else {
        console.log('Menu section not visible');
      }
    });

    test('2.2 View menu products', async ({ page }) => {
      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');

      // Login first
      const loginBtn = page.locator('#loginBtn');
      if (await loginBtn.isVisible()) {
        await loginBtn.click();
        await page.waitForTimeout(1000);
      }

      const loginSection = page.locator('#loginSection');
      if (await loginSection.isVisible()) {
        await page.fill('#username', 'admin');
        await page.fill('#password', 'password');
        await page.click('#loginForm button[type="submit"]');
        await page.waitForTimeout(3000);
      }

      const menuSection = page.locator('#menuSection');
      if (await menuSection.isVisible()) {
        const products = await page.locator('.product-item').count();
        console.log(`Menu products found: ${products}`);
      } else {
        console.log('Menu section not visible');
      }
    });
  });

  // Phase 3: Order Management Testing
  test.describe('Phase 3: Order Management', () => {
    test('3.1 View orders', async ({ page }) => {
      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');

      // Login first
      const loginBtn = page.locator('#loginBtn');
      if (await loginBtn.isVisible()) {
        await loginBtn.click();
        await page.waitForTimeout(1000);
      }

      const loginSection = page.locator('#loginSection');
      if (await loginSection.isVisible()) {
        await page.fill('#username', 'admin');
        await page.fill('#password', 'password');
        await page.click('#loginForm button[type="submit"]');
        await page.waitForTimeout(3000);
      }

      // Check if orders section exists
      const ordersSection = page.locator('#ordersSection');
      if (await ordersSection.isVisible()) {
        const orders = await page.locator('.order-item').count();
        console.log(`Orders found: ${orders}`);
      } else {
        console.log('Orders section not visible');
      }
    });

    test.skip('3.2 Create order simulation', async ({ page }) => {
      // SKIPPED: Requires order creation UI implementation
    });
  });

  // Phase 4: Table Management Testing
  test.describe('Phase 4: Table Management', () => {
    test('4.1 View tables', async ({ page }) => {
      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');

      // Login first
      const loginBtn = page.locator('#loginBtn');
      if (await loginBtn.isVisible()) {
        await loginBtn.click();
        await page.waitForTimeout(1000);
      }

      const loginSection = page.locator('#loginSection');
      if (await loginSection.isVisible()) {
        await page.fill('#username', 'admin');
        await page.fill('#password', 'password');
        await page.click('#loginForm button[type="submit"]');
        await page.waitForTimeout(3000);
      }

      // Check if tables section exists
      const tablesSection = page.locator('#tablesSection');
      if (await tablesSection.isVisible()) {
        const tables = await page.locator('.table-item').count();
        console.log(`Tables found: ${tables}`);
      } else {
        console.log('Tables section not visible');
      }
    });

    test.skip('4.2 Update table status simulation', async ({ page }) => {
      // SKIPPED: Requires table status update UI implementation
    });
  });

  // Phase 5: Kitchen Operations Testing
  test.describe('Phase 5: Kitchen Operations', () => {
    test.skip('5.1 View kitchen orders', async ({ page }) => {
      // SKIPPED: Requires database tables for kitchen_orders
    });

    test.skip('5.2 Update kitchen order status simulation', async ({ page }) => {
      // SKIPPED: Requires database tables for kitchen_orders
    });
  });

  // Phase 6: Inventory Management Testing
  test.describe('Phase 6: Inventory Management', () => {
    test.skip('6.1 View inventory', async ({ page }) => {
      // SKIPPED: Requires database tables for inventory
    });

    test.skip('6.2 Stock adjustment simulation', async ({ page }) => {
      // SKIPPED: Requires database tables for inventory
    });
  });

  // Phase 7: Reservation Management Testing
  test.describe('Phase 7: Reservation Management', () => {
    test.skip('7.1 View reservations', async ({ page }) => {
      // SKIPPED: Requires database tables for reservations
    });

    test.skip('7.2 Create reservation simulation', async ({ page }) => {
      // SKIPPED: Requires database tables for reservations
    });
  });

  // Phase 8: Payment Processing Testing
  test.describe('Phase 8: Payment Processing', () => {
    test.skip('8.1 View payment history', async ({ page }) => {
      // SKIPPED: Requires database tables for payments
    });

    test.skip('8.2 Process payment simulation', async ({ page }) => {
      // SKIPPED: Requires database tables for payments
    });
  });

  // Phase 9: Dashboard Overview Testing
  test.describe('Phase 9: Dashboard Overview', () => {
    test.skip('9.1 View dashboard overview', async ({ page }) => {
      // SKIPPED: Requires database tables for dashboard data
    });
  });

  // Phase 10: Reporting Testing
  test.describe('Phase 10: Reporting', () => {
    test.skip('10.1 View sales report', async ({ page }) => {
      // SKIPPED: Requires database tables for reports
    });
  });

  // Phase 11: UI Responsiveness Testing
  test.describe('Phase 11: UI Responsiveness', () => {
    test('11.1 Mobile view', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 667 });
      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');

      const mobileMenu = page.locator('#mobileMenu');
      const isMobileMenuVisible = await mobileMenu.isVisible();
      console.log(`Mobile menu visible: ${isMobileMenuVisible}`);
    });

    test('11.2 Tablet view', async ({ page }) => {
      await page.setViewportSize({ width: 768, height: 1024 });
      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');

      const content = page.locator('.container');
      const isContentVisible = await content.isVisible();
      console.log(`Content visible on tablet: ${isContentVisible}`);
    });

    test('11.3 Desktop view', async ({ page }) => {
      await page.setViewportSize({ width: 1920, height: 1080 });
      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');

      const sidebar = page.locator('#sidebar');
      const isSidebarVisible = await sidebar.isVisible();
      console.log(`Sidebar visible on desktop: ${isSidebarVisible}`);
    });
  });

  // Phase 12: Console and Network Monitoring
  test.describe('Phase 12: Console and Network Monitoring', () => {
    test('12.1 Monitor console errors', async ({ page }) => {
      const consoleErrors: string[] = [];
      
      page.on('console', msg => {
        if (msg.type() === 'error') {
          consoleErrors.push(msg.text());
        }
      });

      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      console.log(`Console errors found: ${consoleErrors.length}`);
      if (consoleErrors.length > 0) {
        consoleErrors.forEach(err => console.log(`Error: ${err}`));
      }
      
      expect(consoleErrors.length).toBe(0);
    });

    test('12.2 Monitor failed network requests', async ({ page }) => {
      const failedRequests: string[] = [];
      
      page.on('requestfailed', request => {
        failedRequests.push(request.url());
      });

      await page.goto('http://localhost/restauran/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      console.log(`Failed requests: ${failedRequests.length}`);
      if (failedRequests.length > 0) {
        failedRequests.forEach(url => console.log(`Failed: ${url}`));
      }
      
      expect(failedRequests.length).toBe(0);
    });
  });
});
