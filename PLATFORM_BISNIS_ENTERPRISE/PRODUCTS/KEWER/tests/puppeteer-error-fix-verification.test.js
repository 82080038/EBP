const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  tests: [],
  errors: [],
  consoleLogs: [],
  networkErrors: []
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
  return screenshotPath;
}

// Helper function to log errors
function logError(testName, error) {
  console.error(`❌ ${testName}: ${error.message}`);
  results.errors.push({ test: testName, error: error.message });
  results.tests.push({ test: testName, passed: false, details: error.message });
  results.failed++;
}

// Helper function to log success
function logSuccess(testName) {
  console.log(`✅ ${testName}`);
  results.tests.push({ test: testName, passed: true, details: '' });
  results.passed++;
}

// Run tests
async function runTests() {
  console.log('🚀 Starting PHP/JS Error Fix Verification Test (Puppeteer Headed)...\n');

  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();

    await page.setDefaultTimeout(60000);
    await page.setDefaultNavigationTimeout(60000);

    // Enable console logging
    page.on('console', msg => {
      const log = {
        type: msg.type(),
        text: msg.text(),
        location: msg.location()
      };
      results.consoleLogs.push(log);
      console.log(`[Browser Console ${log.type}] ${log.text}`);
    });

    // Enable network logging
    page.on('requestfailed', request => {
      const error = {
        url: request.url(),
        failure: request.failure().errorText
      };
      results.networkErrors.push(error);
      console.error(`[Network Error] ${error.url} - ${error.failure}`);
    });

    // Test 1: Dashboard - Verify no PHP errors
    try {
      console.log('\n📋 Test 1: Dashboard PHP Error Check');

      // Clear cookies
      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as bos
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=patri&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      const currentUrl = page.url();
      if (!currentUrl.includes('dashboard.php')) {
        throw new Error('Login failed');
      }

      await takeScreenshot(page, 'error-fix-dashboard');

      // Check for PHP error indicators in page
      const phpErrors = await page.evaluate(() => {
        const bodyText = document.body.textContent;
        const htmlContent = document.documentElement.outerHTML;
        
        // Look for actual PHP error messages, not CSS class names
        const errorPatterns = [
          /Fatal error:.*in.*on line/i,
          /Parse error:.*in.*on line/i,
          /Notice:.*Undefined variable/i,
          /Warning:.*Undefined variable/i,
          /Warning:.*mysqli_.*expects parameter/i,
          /Warning:.*include.*failed to open/i
        ];
        
        const foundErrors = [];
        errorPatterns.forEach(pattern => {
          const matches = bodyText.match(pattern);
          if (matches) {
            foundErrors.push(matches[0]);
          }
        });
        
        return foundErrors;
      });

      if (phpErrors.length > 0) {
        throw new Error(`PHP errors found: ${phpErrors.join(', ')}`);
      }

      logSuccess('Dashboard PHP Error Check');
    } catch (error) {
      logError('Dashboard PHP Error Check', error);
    }

    // Test 2: Nasabah Page - Verify no PHP errors
    try {
      console.log('\n📋 Test 2: Nasabah Page PHP Error Check');

      await page.goto(config.baseUrl + '/pages/nasabah/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'error-fix-nasabah');

      const phpErrors = await page.evaluate(() => {
        const bodyText = document.body.textContent;
        
        // Look for actual PHP error messages, not CSS class names
        const errorPatterns = [
          /Fatal error:.*in.*on line/i,
          /Parse error:.*in.*on line/i,
          /Notice:.*Undefined variable/i,
          /Warning:.*Undefined variable/i,
          /Warning:.*mysqli_.*expects parameter/i,
          /Warning:.*include.*failed to open/i
        ];
        
        const foundErrors = [];
        errorPatterns.forEach(pattern => {
          const matches = bodyText.match(pattern);
          if (matches) {
            foundErrors.push(matches[0]);
          }
        });
        
        return foundErrors;
      });

      if (phpErrors.length > 0) {
        throw new Error(`PHP errors found: ${phpErrors.join(', ')}`);
      }

      logSuccess('Nasabah Page PHP Error Check');
    } catch (error) {
      logError('Nasabah Page PHP Error Check', error);
    }

    // Test 3: Pinjaman Page - Verify no PHP errors
    try {
      console.log('\n📋 Test 3: Pinjaman Page PHP Error Check');

      await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'error-fix-pinjaman');

      const phpErrors = await page.evaluate(() => {
        const bodyText = document.body.textContent;
        
        // Look for actual PHP error messages, not CSS class names
        const errorPatterns = [
          /Fatal error:.*in.*on line/i,
          /Parse error:.*in.*on line/i,
          /Notice:.*Undefined variable/i,
          /Warning:.*Undefined variable/i,
          /Warning:.*mysqli_.*expects parameter/i,
          /Warning:.*include.*failed to open/i
        ];
        
        const foundErrors = [];
        errorPatterns.forEach(pattern => {
          const matches = bodyText.match(pattern);
          if (matches) {
            foundErrors.push(matches[0]);
          }
        });
        
        return foundErrors;
      });

      if (phpErrors.length > 0) {
        throw new Error(`PHP errors found: ${phpErrors.join(', ')}`);
      }

      logSuccess('Pinjaman Page PHP Error Check');
    } catch (error) {
      logError('Pinjaman Page PHP Error Check', error);
    }

    // Test 4: Angsuran Page - Verify no PHP errors
    try {
      console.log('\n📋 Test 4: Angsuran Page PHP Error Check');

      await page.goto(config.baseUrl + '/pages/angsuran/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'error-fix-angsuran');

      const phpErrors = await page.evaluate(() => {
        const bodyText = document.body.textContent;
        
        // Look for actual PHP error messages, not CSS class names
        const errorPatterns = [
          /Fatal error:.*in.*on line/i,
          /Parse error:.*in.*on line/i,
          /Notice:.*Undefined variable/i,
          /Warning:.*Undefined variable/i,
          /Warning:.*mysqli_.*expects parameter/i,
          /Warning:.*include.*failed to open/i
        ];
        
        const foundErrors = [];
        errorPatterns.forEach(pattern => {
          const matches = bodyText.match(pattern);
          if (matches) {
            foundErrors.push(matches[0]);
          }
        });
        
        return foundErrors;
      });

      if (phpErrors.length > 0) {
        throw new Error(`PHP errors found: ${phpErrors.join(', ')}`);
      }

      logSuccess('Angsuran Page PHP Error Check');
    } catch (error) {
      logError('Angsuran Page PHP Error Check', error);
    }

    // Test 5: Cabang Page - Verify no PHP errors
    try {
      console.log('\n📋 Test 5: Cabang Page PHP Error Check');

      await page.goto(config.baseUrl + '/pages/cabang/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'error-fix-cabang');

      const phpErrors = await page.evaluate(() => {
        const bodyText = document.body.textContent;
        
        // Look for actual PHP error messages, not CSS class names
        const errorPatterns = [
          /Fatal error:.*in.*on line/i,
          /Parse error:.*in.*on line/i,
          /Notice:.*Undefined variable/i,
          /Warning:.*Undefined variable/i,
          /Warning:.*mysqli_.*expects parameter/i,
          /Warning:.*include.*failed to open/i
        ];
        
        const foundErrors = [];
        errorPatterns.forEach(pattern => {
          const matches = bodyText.match(pattern);
          if (matches) {
            foundErrors.push(matches[0]);
          }
        });
        
        return foundErrors;
      });

      if (phpErrors.length > 0) {
        throw new Error(`PHP errors found: ${phpErrors.join(', ')}`);
      }

      logSuccess('Cabang Page PHP Error Check');
    } catch (error) {
      logError('Cabang Page PHP Error Check', error);
    }

    // Test 6: Users Page - Verify no PHP errors
    try {
      console.log('\n📋 Test 6: Users Page PHP Error Check');

      await page.goto(config.baseUrl + '/pages/users/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'error-fix-users');

      const phpErrors = await page.evaluate(() => {
        const bodyText = document.body.textContent;
        
        // Look for actual PHP error messages, not CSS class names
        const errorPatterns = [
          /Fatal error:.*in.*on line/i,
          /Parse error:.*in.*on line/i,
          /Notice:.*Undefined variable/i,
          /Warning:.*Undefined variable/i,
          /Warning:.*mysqli_.*expects parameter/i,
          /Warning:.*include.*failed to open/i
        ];
        
        const foundErrors = [];
        errorPatterns.forEach(pattern => {
          const matches = bodyText.match(pattern);
          if (matches) {
            foundErrors.push(matches[0]);
          }
        });
        
        return foundErrors;
      });

      if (phpErrors.length > 0) {
        throw new Error(`PHP errors found: ${phpErrors.join(', ')}`);
      }

      logSuccess('Users Page PHP Error Check');
    } catch (error) {
      logError('Users Page PHP Error Check', error);
    }

    // Test 7: JavaScript Error Check
    try {
      console.log('\n📋 Test 7: JavaScript Console Error Check');

      await page.goto(config.baseUrl + '/dashboard.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      // Check console logs for JS errors
      const jsErrors = results.consoleLogs.filter(log => 
        log.type === 'error' || log.type === 'warning'
      );

      console.log(`JS Errors/Warnings found: ${jsErrors.length}`);
      if (jsErrors.length > 0) {
        jsErrors.forEach(err => {
          console.log(`  - [${err.type}] ${err.text}`);
        });
      }

      // Check for critical JS errors (not warnings)
      const criticalErrors = jsErrors.filter(log => log.type === 'error');
      if (criticalErrors.length > 0) {
        throw new Error(`Critical JS errors found: ${criticalErrors.length}`);
      }

      logSuccess('JavaScript Console Error Check');
    } catch (error) {
      logError('JavaScript Console Error Check', error);
    }

  } catch (error) {
    console.error('Fatal Error:', error);
    logError('Fatal Error', error);
  } finally {
    if (browser) {
      await browser.close();
    }
  }

  // Print summary
  console.log('\n' + '='.repeat(60));
  console.log('📊 PHP/JS Error Fix Verification Test Summary');
  console.log('='.repeat(60));
  console.log(`✅ Passed: ${results.passed}`);
  console.log(`❌ Failed: ${results.failed}`);
  console.log(`📋 Total Tests: ${results.tests.length}`);
  console.log(`📝 Console Logs: ${results.consoleLogs.length}`);
  console.log(`🌐 Network Errors: ${results.networkErrors.length}`);
  console.log('='.repeat(60));

  console.log('\n📋 Test Details:');
  results.tests.forEach(test => {
    const icon = test.passed ? '✅' : '❌';
    console.log(`${icon} ${test.test}`);
    if (test.details) {
      console.log(`   ${test.details}`);
    }
  });

  console.log('\n📸 Screenshots saved to: ' + screenshotDir);
  console.log('='.repeat(60));
}

runTests();
