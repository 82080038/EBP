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
  console.log('🚀 Starting Superadmin Bos Approval Tests in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    // Test 1: Login as Superadmin
    try {
      console.log('\n📋 Test 1: Login as Superadmin');
      
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
      
      logSuccess('Login as Superadmin');
      await takeScreenshot(page, 'superadmin-dashboard');
    } catch (error) {
      logError('Login as Superadmin', error);
    }

    // Test 2: Check if there's a page for bos registrations
    try {
      console.log('\n📋 Test 2: Check Bos Registration Page');
      
      // Try to navigate to bos approval page (correct path)
      await page.goto(config.baseUrl + '/pages/superadmin/bos_approvals.php');
      
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      const currentUrl = page.url();
      console.log('Current URL:', currentUrl);
      
      // Check if page exists and has content
      const h1 = await page.$('h1');
      const h4 = await page.$('h4');
      
      if (h1 || h4) {
        const headingText = h1 ? await page.$eval('h1', el => el.textContent) : await page.$eval('h4', el => el.textContent);
        console.log('Page heading:', headingText);
        logSuccess('Bos Registration Page');
        await takeScreenshot(page, 'bos-approvals-page');
      } else {
        throw new Error('Bos approval page not found or has no heading');
      }
    } catch (error) {
      logError('Bos Registration Page', error);
    }

    // Test 3: Check for pending bos registrations
    try {
      console.log('\n📋 Test 3: Check Pending Bos Registrations');
      
      // Navigate to bos approval page (correct path)
      await page.goto(config.baseUrl + '/pages/superadmin/bos_approvals.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check for table or list of registrations
      const table = await page.$('table');
      const card = await page.$('.card');
      
      if (table) {
        const rows = await page.$$('table tbody tr');
        console.log('Number of rows in table:', rows.length);
        
        if (rows.length > 0) {
          // Get data from first row
          const firstRowText = await page.$eval('table tbody tr:first-child', el => el.textContent);
          console.log('First row data:', firstRowText);
          logSuccess('Pending Bos Registrations Found');
          await takeScreenshot(page, 'bos-pending-registrations');
        } else {
          logWarning('No pending registrations found');
        }
      } else if (card) {
        const cardText = await page.$eval('.card', el => el.textContent);
        console.log('Card content:', cardText);
        logSuccess('Bos Registration Card Found');
        await takeScreenshot(page, 'bos-registration-card');
      } else {
        throw new Error('No table or card found for bos registrations');
      }
    } catch (error) {
      logError('Pending Bos Registrations', error);
    }

    // Test 4: Check if superadmin can approve bos registration
    try {
      console.log('\n📋 Test 4: Check Bos Approval Functionality');
      
      await page.goto(config.baseUrl + '/pages/superadmin/bos_approvals.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Look for approve button using page.evaluate with XPath
      const approveButton = await page.evaluate(() => {
        const button = document.evaluate('//button[contains(text(), "Setujui")]', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
        return button !== null;
      });
      const approveButton2 = await page.$('button.btn-success');
      
      if (approveButton || approveButton2) {
        console.log('Approve button found');
        logSuccess('Bos Approval Functionality Available');
        await takeScreenshot(page, 'bos-approval-buttons');
      } else {
        throw new Error('Approve button not found');
      }
    } catch (error) {
      logError('Bos Approval Functionality', error);
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

// Helper function to log warning
function logWarning(testName, warning) {
  console.warn(`⚠️  ${testName}: ${warning}`);
}

// Run tests
runTests();
