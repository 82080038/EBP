import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8000';
const API_BASE = 'http://localhost:8000/api/v1';

// Configure headed mode for all tests in this file
test.use({
  headless: false,
  viewport: { width: 1280, height: 720 },
  video: 'retain-on-failure',
  screenshot: 'only-on-failure',
});

test.describe('RESTAURANT_ERP - Full Headed Browser Simulation', () => {

  test('Complete Restaurant Operations Simulation', async ({ page, context }) => {
    console.log('🚀 Starting Full Restaurant Operations Simulation');

    // Step 1: Navigate to application
    console.log('📍 Step 1: Navigating to application...');
    await page.goto(BASE_URL);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'test-results/simulation/01-landing.png' });
    console.log('✅ Application loaded');

    // Step 2: Test API endpoints directly
    console.log('📍 Step 2: Testing API endpoints...');

    // Login via API
    const loginResponse = await page.request.post(`${API_BASE}/auth/login`, {
      data: {
        username: 'admin',
        password: 'admin123'
      }
    });
    const loginData = await loginResponse.json();
    expect(loginData.success).toBe(true);
    const token = loginData.data.access_token;
    console.log('✅ API Login successful');

    // Get categories
    const categoriesResponse = await page.request.get(`${API_BASE}/menu/categories`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const categoriesData = await categoriesResponse.json();
    console.log(`✅ Retrieved ${categoriesData.data.length} categories`);

    // Get products
    const productsResponse = await page.request.get(`${API_BASE}/menu/products`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const productsData = await productsResponse.json();
    console.log(`✅ Retrieved ${productsData.data.length} products`);

    // Get tables
    const tablesResponse = await page.request.get(`${API_BASE}/tables`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const tablesData = await tablesResponse.json();
    console.log(`✅ Retrieved ${tablesData.data.length} tables`);

    await page.screenshot({ path: 'test-results/simulation/02-api-test.png' });

    // Step 3: Navigate to mobile app
    console.log('📍 Step 3: Testing Mobile App...');
    await page.goto(`${BASE_URL}/frontend/mobile/`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'test-results/simulation/03-mobile-app.png' });
    console.log('✅ Mobile app loaded');

    // Step 4: Navigate to kiosk app
    console.log('📍 Step 4: Testing Kiosk App...');
    await page.goto(`${BASE_URL}/frontend/kiosk/`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'test-results/simulation/04-kiosk-app.png' });
    console.log('✅ Kiosk app loaded');

    // Step 5: Test responsive design - Desktop
    console.log('📍 Step 5: Testing Responsive Design - Desktop...');
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto(BASE_URL);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'test-results/simulation/05-desktop-view.png' });
    console.log('✅ Desktop view loaded');

    // Step 6: Test responsive design - Tablet
    console.log('📍 Step 6: Testing Responsive Design - Tablet...');
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto(BASE_URL);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'test-results/simulation/06-tablet-view.png' });
    console.log('✅ Tablet view loaded');

    // Step 7: Test responsive design - Mobile
    console.log('📍 Step 7: Testing Responsive Design - Mobile...');
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(BASE_URL);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'test-results/simulation/07-mobile-view.png' });
    console.log('✅ Mobile view loaded');

    // Step 8: Test API error handling
    console.log('📍 Step 8: Testing API Error Handling...');
    const errorResponse = await page.request.get(`${API_BASE}/settings`, {
      headers: { 'Authorization': 'Bearer invalid_token' }
    });
    expect(errorResponse.status()).toBe(401);
    console.log('✅ API error handling working correctly');

    // Step 9: Test data integrity
    console.log('📍 Step 9: Testing Data Integrity...');

    // Verify categories have required fields
    for (const category of categoriesData.data) {
      expect(category).toHaveProperty('category_id');
      expect(category).toHaveProperty('category_name');
      expect(category).toHaveProperty('status');
    }
    console.log('✅ Categories data structure valid');

    // Verify products have required fields
    for (const product of productsData.data) {
      expect(product).toHaveProperty('product_id');
      expect(product).toHaveProperty('product_name');
      expect(product).toHaveProperty('price');
      expect(product).toHaveProperty('category_name');
    }
    console.log('✅ Products data structure valid');

    // Verify tables have required fields
    for (const table of tablesData.data) {
      expect(table).toHaveProperty('table_id');
      expect(table).toHaveProperty('table_number');
      expect(table).toHaveProperty('capacity');
      expect(table).toHaveProperty('status');
    }
    console.log('✅ Tables data structure valid');

    // Step 10: Performance metrics
    console.log('📍 Step 10: Collecting Performance Metrics...');
    const metrics = await page.evaluate(() => {
      const navigation = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming;
      return {
        domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
        loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
        totalLoadTime: navigation.loadEventEnd - navigation.fetchStart,
      };
    });
    console.log(`📊 DOM Content Loaded: ${metrics.domContentLoaded}ms`);
    console.log(`📊 Load Complete: ${metrics.loadComplete}ms`);
    console.log(`📊 Total Load Time: ${metrics.totalLoadTime}ms`);

    await page.screenshot({ path: 'test-results/simulation/08-final-state.png' });

    console.log('🎉 Full Simulation Complete!');
    console.log('📸 Screenshots saved to test-results/simulation/');
  });

  test('API-Only Full Workflow Simulation', async ({ page }) => {
    console.log('🚀 Starting API-Only Workflow Simulation');

    // Step 1: Login
    console.log('📍 Step 1: Authenticating...');
    const loginResponse = await page.request.post(`${API_BASE}/auth/login`, {
      data: { username: 'admin', password: 'admin123' }
    });
    const loginData = await loginResponse.json();
    expect(loginData.success).toBe(true);
    const token = loginData.data.access_token;
    console.log('✅ Authentication successful');
    console.log(`👤 User: ${loginData.data.user.username}`);
    console.log(`🏢 Tenant ID: ${loginData.data.user.tenant_id}`);
    console.log(`🔑 Role: ${loginData.data.user.role}`);

    // Step 2: Get Settings
    console.log('📍 Step 2: Fetching Settings...');
    const settingsResponse = await page.request.get(`${API_BASE}/settings`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const settingsData = await settingsResponse.json();
    expect(settingsData.success).toBe(true);
    console.log(`✅ Settings retrieved: ${settingsData.data.length} items`);

    // Step 3: Get Categories
    console.log('📍 Step 3: Fetching Menu Categories...');
    const categoriesResponse = await page.request.get(`${API_BASE}/menu/categories`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const categoriesData = await categoriesResponse.json();
    expect(categoriesData.success).toBe(true);
    console.log('✅ Categories retrieved:');
    categoriesData.data.forEach((cat: any) => {
      console.log(`   - ${cat.category_name} (${cat.category_code})`);
    });

    // Step 4: Get Products
    console.log('📍 Step 4: Fetching Products...');
    const productsResponse = await page.request.get(`${API_BASE}/menu/products`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const productsData = await productsResponse.json();
    expect(productsData.success).toBe(true);
    console.log('✅ Products retrieved:');
    productsData.data.forEach((prod: any) => {
      console.log(`   - ${prod.product_name} (${prod.category_name}) - Rp ${prod.price}`);
    });

    // Step 5: Get Tables
    console.log('📍 Step 5: Fetching Tables...');
    const tablesResponse = await page.request.get(`${API_BASE}/tables`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const tablesData = await tablesResponse.json();
    expect(tablesData.success).toBe(true);
    console.log('✅ Tables retrieved:');
    tablesData.data.forEach((table: any) => {
      console.log(`   - Table ${table.table_number} (Capacity: ${table.capacity}, Status: ${table.status})`);
    });

    // Step 6: Get Available Tables
    console.log('📍 Step 6: Fetching Available Tables...');
    const availableTablesResponse = await page.request.get(`${API_BASE}/tables/available`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const availableTablesData = await availableTablesResponse.json();
    expect(availableTablesData.success).toBe(true);
    console.log(`✅ Available tables: ${availableTablesData.data.length}`);

    // Step 7: Get Reservations
    console.log('📍 Step 7: Fetching Reservations...');
    const reservationsResponse = await page.request.get(`${API_BASE}/reservations`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const reservationsData = await reservationsResponse.json();
    expect(reservationsData.success).toBe(true);
    console.log(`✅ Reservations retrieved: ${reservationsData.data.length}`);

    // Step 8: Get Inventory
    console.log('📍 Step 8: Fetching Inventory...');
    const inventoryResponse = await page.request.get(`${API_BASE}/inventory`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const inventoryData = await inventoryResponse.json();
    expect(inventoryData.success).toBe(true);
    console.log(`✅ Inventory items: ${inventoryData.data.length}`);

    // Step 9: Get Low Stock Items
    console.log('📍 Step 9: Fetching Low Stock Items...');
    const lowStockResponse = await page.request.get(`${API_BASE}/inventory/low-stock`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const lowStockData = await lowStockResponse.json();
    expect(lowStockData.success).toBe(true);
    console.log(`✅ Low stock items: ${lowStockData.data.length}`);

    // Step 10: Get Kitchen Orders
    console.log('📍 Step 10: Fetching Kitchen Orders...');
    const kitchenResponse = await page.request.get(`${API_BASE}/kitchen/orders`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const kitchenData = await kitchenResponse.json();
    expect(kitchenData.success).toBe(true);
    console.log(`✅ Kitchen orders: ${kitchenData.data.length}`);

    // Step 11: Get Sales Report
    console.log('📍 Step 11: Fetching Sales Report...');
    const salesResponse = await page.request.get(`${API_BASE}/reports/sales`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const salesData = await salesResponse.json();
    expect(salesData.success).toBe(true);
    console.log(`✅ Sales report retrieved`);

    console.log('🎉 API-Only Workflow Simulation Complete!');
  });
});
