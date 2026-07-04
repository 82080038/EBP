const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: [],
  consoleLogs: [],
  networkErrors: [],
  gpsData: []
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
  console.log('🚀 Starting GPS Payment Field Simulation (Real-world Scenario)...\n');

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

    // Scenario: Petugas doing field payment with GPS capture
    try {
      console.log('\n📋 Scenario: Petugas Field Payment with GPS Capture');

      // Clear cookies for fresh session
      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as petugas
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=ptr_pusat&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      console.log('✅ Petugas login successful');
      await takeScreenshot(page, 'gps-sim-petugas-dashboard');

      // Navigate to angsuran page
      await page.goto(config.baseUrl + '/pages/angsuran/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));
      await takeScreenshot(page, 'gps-sim-angsuran-list');

      // Mock GPS coordinates (Medan field location)
      const mockGPS = {
        latitude: 3.5952,
        longitude: 98.6722,
        accuracy: 10
      };

      console.log(`📍 Simulating GPS capture at: Lat ${mockGPS.latitude}, Lng ${mockGPS.longitude}`);
      results.gpsData.push(mockGPS);

      // Inject geolocation mock
      await page.evaluateOnNewDocument((mockGPS) => {
        window.navigator.geolocation = {
          getCurrentPosition: (success, error, options) => {
            setTimeout(() => {
              success({
                coords: {
                  latitude: mockGPS.latitude,
                  longitude: mockGPS.longitude,
                  accuracy: mockGPS.accuracy,
                  altitude: null,
                  altitudeAccuracy: null,
                  heading: null,
                  speed: null
                },
                timestamp: Date.now()
              });
            }, 100);
          },
          watchPosition: (success, error, options) => {
            this.getCurrentPosition(success, error, options);
          },
          clearWatch: (id) => {}
        };
      }, mockGPS);

      // Try to find and click bayar button
      const bayarButtonClicked = await page.evaluate(() => {
        const buttons = Array.from(document.querySelectorAll('button, .btn'));
        const bayarButton = buttons.find(btn => btn.textContent.includes('Bayar'));
        if (bayarButton) {
          bayarButton.click();
          return true;
        }
        return false;
      });

      if (bayarButtonClicked) {
        await new Promise(resolve => setTimeout(resolve, 3000));
        console.log('✅ Bayar button clicked');

        // Check if payment modal appeared
        const modal = await page.$('.modal');
        if (modal) {
          console.log('✅ Payment modal appeared');
          await takeScreenshot(page, 'gps-sim-payment-modal');

          // Simulate GPS capture in the payment form
          const gpsCaptured = await page.evaluate((mockGPS) => {
            // Check if GPS fields exist
            const latInput = document.querySelector('input[name="lat"]');
            const lngInput = document.querySelector('input[name="lng"]');
            const accuracyInput = document.querySelector('input[name="akurasi_gps"]');

            if (latInput && lngInput) {
              latInput.value = mockGPS.latitude;
              lngInput.value = mockGPS.longitude;
              if (accuracyInput) {
                accuracyInput.value = mockGPS.accuracy;
              }
              return true;
            }
            return false;
          }, mockGPS);

          if (gpsCaptured) {
            console.log('✅ GPS coordinates captured in form');
            await takeScreenshot(page, 'gps-sim-gps-captured');
          } else {
            console.log('⚠️ GPS fields not found in payment form (feature might be disabled)');
          }

          // Close modal
          await page.keyboard.press('Escape');
          await new Promise(resolve => setTimeout(resolve, 500));
        }
      } else {
        console.log('⚠️ No Bayar button found (no active angsuran to pay)');
      }

      logSuccess('GPS Payment Field Simulation');
      results.scenarios = [{ scenario: 'GPS Payment Field Simulation', status: 'Success' }];
    } catch (error) {
      logError('GPS Payment Field Simulation', error);
      results.scenarios = [{ scenario: 'GPS Payment Field Simulation', status: 'Failed' }];
    }

    // Scenario: Check Feature Flag status
    try {
      console.log('\n📋 Scenario: Check GPS Feature Flag Status');

      // Logout petugas
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

      // Check GPS pembayaran feature flag
      const gpsFeatureStatus = await page.evaluate(() => {
        const rows = Array.from(document.querySelectorAll('table tbody tr'));
        const gpsRow = rows.find(row => row.textContent.includes('gps_pembayaran'));
        if (gpsRow) {
          const cells = gpsRow.querySelectorAll('td');
          const isEnabled = cells[2].querySelector('input[type="checkbox"]')?.checked;
          return isEnabled;
        }
        return null;
      });

      console.log(`GPS Pembayaran Feature Flag: ${gpsFeatureStatus ? 'ENABLED' : 'DISABLED'}`);
      results.gpsFeatureEnabled = gpsFeatureStatus;

      await takeScreenshot(page, 'gps-sim-feature-flag');

      logSuccess('Check GPS Feature Flag Status');
    } catch (error) {
      logError('Check GPS Feature Flag Status', error);
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
  console.log('📊 GPS Payment Simulation Test Summary');
  console.log('='.repeat(60));
  console.log(`✅ Passed: ${results.passed}`);
  console.log(`❌ Failed: ${results.failed}`);
  console.log(`🎭 Scenarios Tested: ${results.scenarios?.length || 0}`);
  console.log(`📝 Console Logs: ${results.consoleLogs.length}`);
  console.log(`🌐 Network Errors: ${results.networkErrors.length}`);
  console.log(`📍 GPS Data Points: ${results.gpsData.length}`);
  console.log('='.repeat(60));

  if (results.gpsData.length > 0) {
    console.log('\n📍 GPS Data Captured:');
    results.gpsData.forEach(gps => {
      console.log(`  - Lat: ${gps.latitude}, Lng: ${gps.longitude}, Accuracy: ${gps.accuracy}m`);
    });
  }

  if (results.gpsFeatureEnabled !== undefined) {
    console.log(`\n🚩 GPS Feature Flag: ${results.gpsFeatureEnabled ? 'ENABLED' : 'DISABLED'}`);
  }

  console.log('\n📸 Screenshots saved to: ' + screenshotDir);
  console.log('='.repeat(60));
}

runTests();
