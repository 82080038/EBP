const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: [],
  warnings: []
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

// Helper function to log warning
function logWarning(testName, warning) {
  console.warn(`⚠️  ${testName}: ${warning}`);
  results.warnings.push({ test: testName, warning });
}

// Run tests
async function runTests() {
  console.log('🚀 Starting Puppeteer Tests in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    // Enable cookies for session handling
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    // Set up console listener to catch warnings
    page.on('console', msg => {
      if (msg.type() === 'warning') {
        logWarning('Browser Console', msg.text());
      }
    });

    page.on('pageerror', error => {
      logError('Page Error', error);
    });

    // Test 1: Login Page
    try {
      console.log('\n📋 Test 1: Login Page Display');
      await page.goto(config.baseUrl + '/login.php');
      await page.waitForSelector('h2', { timeout: 5000 });
      
      const title = await page.title();
      if (!title.includes('Login')) {
        throw new Error('Page title does not contain "Login"');
      }
      
      const h2 = await page.$eval('h2', el => el.textContent);
      if (!h2.includes('Koperasi')) {
        throw new Error('Page heading does not contain "Koperasi"');
      }
      
      const usernameInput = await page.$('input[name="username"]');
      const passwordInput = await page.$('input[name="password"]');
      const submitButton = await page.$('button[type="submit"]');
      
      if (!usernameInput || !passwordInput || !submitButton) {
        throw new Error('Login form elements not found');
      }
      
      logSuccess('Login Page Display');
      await takeScreenshot(page, '01-login-page');
    } catch (error) {
      logError('Login Page Display', error);
    }

    // Test 2: Invalid Login
    try {
      console.log('\n📋 Test 2: Invalid Login');
      await page.goto(config.baseUrl + '/login.php');
      await page.type('input[name="username"]', 'invalid');
      await page.type('input[name="password"]', 'invalid');
      await page.click('button[type="submit"]');
      
      await page.waitForSelector('.alert-danger', { timeout: 5000 });
      const alertText = await page.$eval('.alert-danger', el => el.textContent);
      
      if (!alertText.includes('Username atau password salah')) {
        throw new Error('Error message not displayed correctly');
      }
      
      logSuccess('Invalid Login');
      await takeScreenshot(page, '02-invalid-login');
    } catch (error) {
      logError('Invalid Login', error);
    }

    // Test 3: Valid Login
    try {
      console.log('\n📋 Test 3: Valid Login');
      
      // Use test-specific login endpoint (GET request with credentials)
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=patri&password=password');
      
      // Wait for redirect
      await new Promise(resolve => setTimeout(resolve, 2000));
      const currentUrl = page.url();
      console.log('Current URL after login:', currentUrl);
      
      if (!currentUrl.includes('dashboard.php')) {
        throw new Error('Not redirected to dashboard after login');
      }
      
      // Wait for dashboard to load
      await page.waitForSelector('.card', { timeout: 5000 });
      
      logSuccess('Valid Login');
      await takeScreenshot(page, '03-dashboard-after-login');
    } catch (error) {
      logError('Valid Login', error);
    }

    // Test 4: Dashboard Display
    try {
      console.log('\n📋 Test 4: Dashboard Display');
      await page.goto(config.baseUrl + '/dashboard.php');
      await page.waitForSelector('.card', { timeout: 5000 });
      
      const cards = await page.$$('.card');
      if (cards.length === 0) {
        logWarning('Dashboard Display', 'No statistics cards found');
      }
      
      const h5 = await page.$('h5');
      if (!h5) {
        logWarning('Dashboard Display', 'No heading found');
      }
      
      logSuccess('Dashboard Display');
      await takeScreenshot(page, '04-dashboard-display');
    } catch (error) {
      logError('Dashboard Display', error);
    }

    // Test 5: Nasabah Page
    try {
      console.log('\n📋 Test 5: Nasabah Page');
      await page.goto(config.baseUrl + '/pages/nasabah/index.php');
      
      // Check if redirected to login (not authenticated)
      if (page.url().includes('login.php')) {
        throw new Error('Redirected to login - authentication required');
      }
      
      await page.waitForSelector('h1', { timeout: 5000 });
      
      const h1 = await page.$eval('h1', el => el.textContent);
      if (!h1.includes('Nasabah')) {
        throw new Error('Nasabah page not displayed correctly');
      }
      
      logSuccess('Nasabah Page');
      await takeScreenshot(page, '05-nasabah-page');
    } catch (error) {
      logError('Nasabah Page', error);
    }

    // Test 6: Pinjaman Page
    try {
      console.log('\n📋 Test 6: Pinjaman Page');
      await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
      await page.waitForSelector('table', { timeout: 5000 });
      
      const h1 = await page.$eval('h1', el => el.textContent);
      if (!h1.includes('Pinjaman')) {
        throw new Error('Pinjaman page not displayed correctly');
      }
      
      const table = await page.$('.table');
      if (!table) {
        throw new Error('Pinjaman table not found');
      }
      
      logSuccess('Pinjaman Page');
      await takeScreenshot(page, '06-pinjaman-page');
    } catch (error) {
      logError('Pinjaman Page', error);
    }

    // Test 7: Angsuran Page
    try {
      console.log('\n📋 Test 7: Angsuran Page');
      await page.goto(config.baseUrl + '/pages/angsuran/index.php');
      
      // Check if redirected to login (not authenticated)
      if (page.url().includes('login.php')) {
        throw new Error('Redirected to login - authentication required');
      }
      
      await page.waitForSelector('h1', { timeout: 5000 });
      
      const h1 = await page.$eval('h1', el => el.textContent);
      if (!h1.includes('Angsuran')) {
        throw new Error('Angsuran page not displayed correctly');
      }
      
      logSuccess('Angsuran Page');
      await takeScreenshot(page, '07-angsuran-page');
    } catch (error) {
      logError('Angsuran Page', error);
    }

    // Test 8: Logout
    try {
      console.log('\n📋 Test 8: Logout');
      // Navigate to dashboard (should be logged in from test 3)
      await page.goto(config.baseUrl + '/dashboard.php');
      
      // If redirected to login, login first
      if (page.url().includes('login.php')) {
        await page.goto(config.baseUrl + '/login.php?test_login=true&username=patri&password=password');
        await new Promise(resolve => setTimeout(resolve, 2000));
        await page.goto(config.baseUrl + '/dashboard.php');
      }
      
      // Wait for dashboard to load
      await page.waitForSelector('.card', { timeout: 5000 });
      
      await page.click('a[href="logout.php"]');
      
      // Wait for redirect to complete
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check if we're on login page
      const currentUrl = page.url();
      if (!currentUrl.includes('login.php')) {
        throw new Error('Did not redirect to login after logout');
      }
      
      // Check for login form elements
      const usernameInput = await page.$('input[name="username"]');
      if (!usernameInput) {
        throw new Error('Login form not displayed after logout');
      }
      
      logSuccess('Logout');
      await takeScreenshot(page, '08-after-logout');
    } catch (error) {
      logError('Logout', error);
    }

    // Test 9: API Authentication
    try {
      console.log('\n📋 Test 9: API Authentication');
      const response = await page.evaluate(async (baseUrl) => {
        const res = await fetch(baseUrl + '/api/auth.php?action=login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            username: 'patri',
            password: 'password'
          })
        });
        return await res.json();
      }, config.baseUrl);

      console.log('API Auth Response:', response);

      if (!response.success) {
        throw new Error('API authentication failed: ' + (response.error || 'Unknown error'));
      }
      
      logSuccess('API Authentication');
    } catch (error) {
      logError('API Authentication', error);
    }

    // Test 10: API Dashboard
    try {
      console.log('\n📋 Test 10: API Dashboard');
      const response = await page.evaluate(async (baseUrl) => {
        try {
          const res = await fetch(baseUrl + '/api/dashboard.php', {
            method: 'GET',
            headers: {
              'Authorization': 'Bearer kewer-api-token-2024',
              'Content-Type': 'application/json',
            }
          });
          const data = await res.json();
          return { status: res.status, data };
        } catch (error) {
          return { error: error.message };
        }
      }, config.baseUrl);

      if (response.error) {
        throw new Error(response.error);
      }
      
      if (response.status !== 200) {
        throw new Error(`API returned status ${response.status}`);
      }
      
      logSuccess('API Dashboard');
    } catch (error) {
      logError('API Dashboard', error);
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
  console.log(`⚠️  Warnings: ${results.warnings.length}`);
  console.log('='.repeat(50));

  if (results.errors.length > 0) {
    console.log('\n❌ Errors:');
    results.errors.forEach(err => {
      console.log(`  - ${err.test}: ${err.error}`);
    });
  }

  if (results.warnings.length > 0) {
    console.log('\n⚠️  Warnings:');
    results.warnings.forEach(warn => {
      console.log(`  - ${warn.test}: ${warn.warning}`);
    });
  }

  console.log('\n📸 Screenshots saved to:', screenshotDir);
  console.log('='.repeat(50));

  // Exit with appropriate code
  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests();
