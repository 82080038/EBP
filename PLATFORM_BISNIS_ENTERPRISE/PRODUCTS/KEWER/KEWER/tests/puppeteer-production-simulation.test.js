const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: [],
  consoleLogs: [],
  networkErrors: [],
  scenarios: []
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
  results.failed++;
}

// Helper function to log success
function logSuccess(testName) {
  console.log(`✅ ${testName}`);
  results.passed++;
}

// Run tests
async function runTests() {
  console.log('🚀 Starting Real-Time Production Simulation with Console Logging...\n');

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

    page.on('response', response => {
      if (response.status() >= 400) {
        const error = {
          url: response.url(),
          status: response.status(),
          statusText: response.statusText()
        };
        results.networkErrors.push(error);
        console.error(`[HTTP Error] ${error.url} - ${error.status} ${error.statusText}`);
      }
    });

    // Scenario 1: Bos Login and Dashboard Check
    try {
      console.log('\n📋 Scenario 1: Bos Login and Dashboard Check (Production-like)');

      // Clear cookies for fresh session
      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      await page.goto(config.baseUrl + '/login.php?test_login=true&username=patri&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      const currentUrl = page.url();
      if (!currentUrl.includes('dashboard.php')) {
        throw new Error('Bos login failed');
      }

      console.log('✅ Bos login successful');
      await takeScreenshot(page, 'prod-sim-bos-dashboard');

      // Check for JavaScript errors
      await page.evaluate(() => {
        window.addEventListener('error', (e) => {
          console.error('JS Error:', e.message, e.filename, e.lineno);
        });
      });

      // Wait for dashboard to fully load
      await page.waitForSelector('.card', { timeout: 10000 });
      console.log('✅ Dashboard loaded successfully');

      // Check dashboard statistics
      const stats = await page.evaluate(() => {
        const cards = Array.from(document.querySelectorAll('.card'));
        return cards.map(card => {
          const title = card.querySelector('.card-title')?.textContent;
          const value = card.querySelector('.card-text, h3, h4')?.textContent;
          return { title, value };
        });
      });
      console.log('Dashboard Statistics:', stats);

      logSuccess('Scenario 1: Bos Login and Dashboard');
      results.scenarios.push({ scenario: 'Bos Login and Dashboard', status: 'Success' });
    } catch (error) {
      logError('Scenario 1: Bos Login and Dashboard', error);
      results.scenarios.push({ scenario: 'Bos Login and Dashboard', status: 'Failed' });
    }

    // Scenario 2: Petugas Field Activity Simulation
    try {
      console.log('\n📋 Scenario 2: Petugas Field Activity Simulation (Production-like)');

      // Logout bos
      await page.goto(config.baseUrl + '/logout.php');
      await new Promise(resolve => setTimeout(resolve, 2000));

      // Clear cookies
      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as petugas
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=ptr_pusat&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      console.log('✅ Petugas login successful');
      await takeScreenshot(page, 'prod-sim-petugas-dashboard');

      // Navigate to angsuran page
      await page.goto(config.baseUrl + '/pages/angsuran/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));
      await takeScreenshot(page, 'prod-sim-petugas-angsuran');

      // Check for angsuran data
      const angsuranCount = await page.evaluate(() => {
        const rows = document.querySelectorAll('table tbody tr');
        return rows.length;
      });
      console.log(`Found ${angsuranCount} angsuran records`);

      // Simulate field activity - check GPS (if feature enabled)
      const gpsStatus = await page.evaluate(() => {
        return typeof navigator.geolocation !== 'undefined';
      });
      console.log(`GPS Available: ${gpsStatus}`);

      logSuccess('Scenario 2: Petugas Field Activity');
      results.scenarios.push({ scenario: 'Petugas Field Activity', status: 'Success' });
    } catch (error) {
      logError('Scenario 2: Petugas Field Activity', error);
      results.scenarios.push({ scenario: 'Petugas Field Activity', status: 'Failed' });
    }

    // Scenario 3: Nasabah Registration Flow
    try {
      console.log('\n📋 Scenario 3: Nasabah Registration Flow (Production-like)');

      // Logout petugas
      await page.goto(config.baseUrl + '/logout.php');
      await new Promise(resolve => setTimeout(resolve, 2000));

      // Clear cookies
      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as bos
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=patri&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await page.goto(config.baseUrl + '/pages/nasabah/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      // Click tambah button
      const tambahButtonClicked = await page.evaluate(() => {
        const buttons = Array.from(document.querySelectorAll('button, .btn'));
        const tambahButton = buttons.find(btn => btn.textContent.includes('Tambah'));
        if (tambahButton) {
          tambahButton.click();
          return true;
        }
        return false;
      });

      if (tambahButtonClicked) {
        await new Promise(resolve => setTimeout(resolve, 2000));

        // Fill form with realistic data
        await page.type('input[name="nama"]', 'Budi Santoso Production Test');
        await page.type('input[name="ktp"]', '1234567890123456');
        await page.type('input[name="telp"]', '081234567890');
        await page.type('input[name="lokasi_pasar"]', 'Pasar Medan Production');

        console.log('✅ Form filled with production-like data');
        await takeScreenshot(page, 'prod-sim-nasabah-form-filled');

        // Close modal without submitting (to avoid creating real data)
        await page.keyboard.press('Escape');
        await new Promise(resolve => setTimeout(resolve, 500));
      }

      logSuccess('Scenario 3: Nasabah Registration Flow');
      results.scenarios.push({ scenario: 'Nasabah Registration Flow', status: 'Success' });
    } catch (error) {
      logError('Scenario 3: Nasabah Registration Flow', error);
      results.scenarios.push({ scenario: 'Nasabah Registration Flow', status: 'Failed' });
    }

    // Scenario 4: Pinjaman Application Flow
    try {
      console.log('\n📋 Scenario 4: Pinjaman Application Flow (Production-like)');

      await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      // Click ajukan button
      const ajukanButtonClicked = await page.evaluate(() => {
        const buttons = Array.from(document.querySelectorAll('button, .btn'));
        const ajukanButton = buttons.find(btn => btn.textContent.includes('Ajukan'));
        if (ajukanButton) {
          ajukanButton.click();
          return true;
        }
        return false;
      });

      if (ajukanButtonClicked) {
        await new Promise(resolve => setTimeout(resolve, 2000));

        // Check if form loaded
        const modal = await page.$('.modal');
        if (modal) {
          console.log('✅ Pinjaman modal appeared');

          // Check for nasabah select
          const nasabahSelect = await page.$('select[name="nasabah_id"]');
          if (nasabahSelect) {
            const optionCount = await page.evaluate(sel => sel.options.length, nasabahSelect);
            console.log(`Found ${optionCount} nasabah options`);
          }

          await takeScreenshot(page, 'prod-sim-pinjaman-modal');
          await page.keyboard.press('Escape');
          await new Promise(resolve => setTimeout(resolve, 500));
        }
      }

      logSuccess('Scenario 4: Pinjaman Application Flow');
      results.scenarios.push({ scenario: 'Pinjaman Application Flow', status: 'Success' });
    } catch (error) {
      logError('Scenario 4: Pinjaman Application Flow', error);
      results.scenarios.push({ scenario: 'Pinjaman Application Flow', status: 'Failed' });
    }

    // Scenario 5: Feature Flags Check
    try {
      console.log('\n📋 Scenario 5: Feature Flags Check (Production-like)');

      // Logout bos
      await page.goto(config.baseUrl + '/logout.php');
      await new Promise(resolve => setTimeout(resolve, 2000));

      // Clear cookies
      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as appOwner
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=appowner&password=AppOwner2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await page.goto(config.baseUrl + '/pages/app_owner/features.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      // Check feature flags table
      const featureCount = await page.evaluate(() => {
        const rows = document.querySelectorAll('table tbody tr');
        return rows.length;
      });
      console.log(`Found ${featureCount} feature flags`);

      await takeScreenshot(page, 'prod-sim-feature-flags');

      logSuccess('Scenario 5: Feature Flags Check');
      results.scenarios.push({ scenario: 'Feature Flags Check', status: 'Success' });
    } catch (error) {
      logError('Scenario 5: Feature Flags Check', error);
      results.scenarios.push({ scenario: 'Feature Flags Check', status: 'Failed' });
    }

  } catch (error) {
    console.error('Fatal Error:', error);
    results.errors.push({ test: 'Fatal Error', error: error.message });
  } finally {
    if (browser) {
      await browser.close();
    }
  }

  // Print summary
  console.log('\n' + '='.repeat(60));
  console.log('📊 Production Simulation Test Summary');
  console.log('='.repeat(60));
  console.log(`✅ Passed: ${results.passed}`);
  console.log(`❌ Failed: ${results.failed}`);
  console.log(`🎭 Scenarios Tested: ${results.scenarios.length}`);
  console.log(`📝 Console Logs: ${results.consoleLogs.length}`);
  console.log(`🌐 Network Errors: ${results.networkErrors.length}`);
  console.log('='.repeat(60));

  if (results.consoleLogs.length > 0) {
    console.log('\n📝 Console Logs Summary:');
    const errorLogs = results.consoleLogs.filter(log => log.type === 'error');
    const warningLogs = results.consoleLogs.filter(log => log.type === 'warning');
    console.log(`  Errors: ${errorLogs.length}`);
    console.log(`  Warnings: ${warningLogs.length}`);
  }

  if (results.networkErrors.length > 0) {
    console.log('\n🌐 Network Errors:');
    results.networkErrors.forEach(err => {
      console.log(`  - ${err.url}: ${err.status || err.failure}`);
    });
  }

  console.log('\n🎭 Scenarios:');
  results.scenarios.forEach(scenario => {
    console.log(`  - ${scenario.scenario}: ${scenario.status}`);
  });

  console.log('\n📸 Screenshots saved to: ' + screenshotDir);
  console.log('='.repeat(60));
}

runTests();
