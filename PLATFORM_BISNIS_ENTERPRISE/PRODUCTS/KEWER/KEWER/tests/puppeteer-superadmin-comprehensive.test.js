const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: [],
  buttons: [],
  functions: []
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

// Superadmin pages to test
const superadminPages = [
  { name: 'Dashboard', url: '/dashboard.php', buttons: [], functions: [] },
  { name: 'Bos Approvals', url: '/pages/superadmin/bos_approvals.php', buttons: ['Setujui', 'Tolak'], functions: ['approve', 'reject'] },
  { name: 'Users Management', url: '/pages/users/index.php', buttons: ['Tambah User', 'Edit', 'Hapus'], functions: ['create', 'update', 'delete'] }
];

// Run tests
async function runTests() {
  console.log('🚀 Starting Comprehensive Superadmin Tests in Headed Mode...\n');
  
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
      
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=patri&password=password');
      
      await new Promise(resolve => setTimeout(resolve, 2000));
      const currentUrl = page.url();
      console.log('Current URL after login:', currentUrl);
      
      if (!currentUrl.includes('dashboard.php')) {
        throw new Error('Not redirected to dashboard after login');
      }
      
      await page.waitForSelector('.card', { timeout: 5000 });
      
      logSuccess('Login as Superadmin');
      await takeScreenshot(page, 'superadmin-login-success');
    } catch (error) {
      logError('Login as Superadmin', error);
    }

    // Test 2: Check and test buttons on each superadmin page
    for (const pageConfig of superadminPages) {
      try {
        console.log(`\n📋 Testing Page: ${pageConfig.name}`);
        
        await page.goto(config.baseUrl + pageConfig.url);
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        const currentUrl = page.url();
        console.log(`  URL: ${currentUrl}`);
        
        // Check for buttons
        for (const buttonText of pageConfig.buttons) {
          try {
            const button = await page.evaluate((text) => {
              const buttons = Array.from(document.querySelectorAll('button, .btn'));
              return buttons.some(btn => btn.textContent.includes(text));
            }, buttonText);
            
            if (button) {
              console.log(`  ✓ Button "${buttonText}" found`);
              results.buttons.push({ page: pageConfig.name, button: buttonText, found: true });
            } else {
              console.log(`  ✗ Button "${buttonText}" not found`);
              results.buttons.push({ page: pageConfig.name, button: buttonText, found: false });
            }
          } catch (error) {
            console.log(`  ⚠️ Error checking button "${buttonText}": ${error.message}`);
          }
        }
        
        // Test clicking buttons if found
        for (const buttonText of pageConfig.buttons) {
          try {
            const button = await page.evaluateHandle((text) => {
              const buttons = Array.from(document.querySelectorAll('button, .btn'));
              return buttons.find(btn => btn.textContent.includes(text));
            }, buttonText);
            
            if (button) {
              console.log(`  Testing click on "${buttonText}" button`);
              await button.click();
              await new Promise(resolve => setTimeout(resolve, 1000));
              
              // Check if modal appeared or action performed
              const modal = await page.$('.modal');
              if (modal) {
                console.log(`  ✓ Modal appeared after clicking "${buttonText}"`);
                await page.keyboard.press('Escape'); // Close modal
                await new Promise(resolve => setTimeout(resolve, 500));
              }
            }
          } catch (error) {
            console.log(`  ⚠️ Error clicking button "${buttonText}": ${error.message}`);
          }
        }
        
        logSuccess(`${pageConfig.name} Page Test`);
        await takeScreenshot(page, `superadmin-${pageConfig.name.toLowerCase().replace(/ /g, '-')}`);
        
      } catch (error) {
        logError(`${pageConfig.name} Page Test`, error);
      }
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
  console.log(`🔘 Buttons Checked: ${results.buttons.length}`);
  console.log('='.repeat(50));

  if (results.errors.length > 0) {
    console.log('\n❌ Errors:');
    results.errors.forEach(err => {
      console.log(`  - ${err.test}: ${err.error}`);
    });
  }

  console.log('\n🔘 Buttons Checked:');
  results.buttons.forEach(btn => {
    console.log(`  - ${btn.page}: ${btn.button} (${btn.found ? '✓' : '✗'})`);
  });

  console.log('\n📸 Screenshots saved to:', screenshotDir);
  console.log('='.repeat(50));

  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests();
