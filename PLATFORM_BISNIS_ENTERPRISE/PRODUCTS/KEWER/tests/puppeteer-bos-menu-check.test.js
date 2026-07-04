const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: []
};

// Create screenshots directory
const fs = require('fs');
const path = require('path');
const screenshotDir = path.join(__dirname, 'screenshots');
if (!fs.existsSync(screenshotDir)) {
  fs.mkdirSync(screenshotDir, { recursive: true });
}

// Helper function to take screenshot
async function takeScreenshot(page, name) {
  const screenshotPath = path.join(screenshotDir, `${name}.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });
  console.log(`Screenshot saved: ${screenshotPath}`);
}

// Helper function to log errors
function logError(testName, error) {
  console.error(`❌ ${testName}: ${error.message}`);
  results.errors.push({ test: testName, error: error.message });
  results.failed++;
}

// Helper function to log success
function logSuccess(testName) {
  console.log(`✅ ${testName}`);
  results.passed++;
}

// Run tests
async function runTests() {
  console.log('🚀 Starting Bos Menu Check Tests in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    // Test 1: Login as Bos
    try {
      console.log('\n📋 Test 1: Login as Bos');
      
      // Use test-specific login endpoint
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=testbos&password=password123');
      
      // Wait for redirect
      await new Promise(resolve => setTimeout(resolve, 2000));
      const currentUrl = page.url();
      console.log('Current URL after login:', currentUrl);
      
      if (!currentUrl.includes('dashboard.php')) {
        throw new Error('Not redirected to dashboard after login');
      }
      
      // Wait for dashboard to load
      await page.waitForSelector('.card', { timeout: 5000 });
      
      logSuccess('Login as Bos');
      await takeScreenshot(page, 'bos-dashboard');
    } catch (error) {
      logError('Login as Bos', error);
    }

    // Test 2: Check Sidebar Menu Items for Bos
    try {
      console.log('\n📋 Test 2: Check Sidebar Menu Items for Bos');
      
      await page.goto(config.baseUrl + '/dashboard.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Get all menu items
      const menuItems = await page.evaluate(() => {
        const items = [];
        document.querySelectorAll('.nav-link').forEach(link => {
          const text = link.textContent.trim();
          const href = link.getAttribute('href');
          if (text && text !== 'Logout') {
            items.push({ text, href });
          }
        });
        return items;
      });
      
      console.log('Menu items found:', menuItems.length);
      menuItems.forEach(item => {
        console.log(`  - ${item.text}: ${item.href}`);
      });
      
      logSuccess('Sidebar Menu Items Check');
      await takeScreenshot(page, 'bos-sidebar-menu');
    } catch (error) {
      logError('Sidebar Menu Items Check', error);
    }

    // Test 3: Check Dashboard Content
    try {
      console.log('\n📋 Test 3: Check Dashboard Content');
      
      await page.goto(config.baseUrl + '/dashboard.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check for stats cards
      const statsCards = await page.$$('.card');
      console.log('Stats cards found:', statsCards.length);
      
      // Check for charts
      const charts = await page.$$('canvas');
      console.log('Charts found:', charts.length);
      
      if (statsCards.length === 0) {
        throw new Error('No stats cards found on dashboard');
      }
      
      logSuccess('Dashboard Content Check');
      await takeScreenshot(page, 'bos-dashboard-content');
    } catch (error) {
      logError('Dashboard Content Check', error);
    }

    // Test 4: Check if Bos has headquarters setup prompt
    try {
      console.log('\n📋 Test 4: Check Headquarters Setup Prompt');
      
      const currentUrl = page.url();
      console.log('Current URL:', currentUrl);
      
      if (currentUrl.includes('setup_headquarters')) {
        console.log('Bos is being redirected to setup headquarters page');
        logSuccess('Headquarters Setup Prompt Found');
        await takeScreenshot(page, 'bos-setup-headquarters');
      } else {
        console.log('Bos is not being redirected to setup headquarters');
        logSuccess('No Headquarters Setup Prompt (Bos may already have headquarters)');
      }
    } catch (error) {
      logError('Headquarters Setup Prompt Check', error);
    }

    // Test 5: Check for any errors in console
    try {
      console.log('\n📋 Test 5: Check Console Errors');
      
      const errors = await page.evaluate(() => {
        const errors = [];
        window.addEventListener('error', e => errors.push(e.message));
        return errors;
      });
      
      if (errors.length > 0) {
        console.log('Console errors found:', errors);
        throw new Error(`Console errors found: ${errors.join(', ')}`);
      }
      
      logSuccess('No Console Errors');
    } catch (error) {
      logError('Console Errors Check', error);
    }

    await browser.close();

  } catch (error) {
    console.error('\n💥 Fatal Error:', error);
    results.errors.push({ test: 'Fatal Error', error: error.message });
    if (browser) {
      await browser.close();
    }
  }

  // Print summary
  console.log('\n' + '='.repeat(50));
  console.log('📊 Test Summary');
  console.log('='.repeat(50));
  console.log(`✅ Passed: ${results.passed}`);
  console.log(`❌ Failed: ${results.failed}`);
  console.log('='.repeat(50));

  if (results.errors.length > 0) {
    console.log('\n❌ Errors:');
    results.errors.forEach(err => {
      console.log(`  - ${err.test}: ${err.error}`);
    });
  }

  console.log('\n📸 Screenshots saved to:', screenshotDir);
  console.log('='.repeat(50));

  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests();
