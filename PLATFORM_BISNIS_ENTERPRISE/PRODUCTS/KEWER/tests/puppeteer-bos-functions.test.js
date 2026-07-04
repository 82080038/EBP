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

// Bos pages to test
const bosPages = [
  { name: 'Dashboard', url: '/dashboard.php', buttons: [], functions: [] },
  { name: 'Nasabah', url: '/pages/nasabah/index.php', buttons: ['Tambah Nasabah', 'Edit', 'Hapus'], functions: ['create', 'update', 'delete'] },
  { name: 'Pinjaman', url: '/pages/pinjaman/index.php', buttons: ['Ajukan Pinjaman', 'Setujui', 'Tolak'], functions: ['create', 'approve', 'reject'] },
  { name: 'Angsuran', url: '/pages/angsuran/index.php', buttons: ['Bayar'], functions: ['pay'] },
  { name: 'Cabang', url: '/pages/cabang/index.php', buttons: ['Tambah Cabang', 'Edit', 'Hapus'], functions: ['create', 'update', 'delete'] },
  { name: 'Setting Bunga', url: '/pages/setting_bunga/index.php', buttons: ['Tambah Setting', 'Edit', 'Hapus'], functions: ['create', 'update', 'delete'] },
  { name: 'Pengeluaran', url: '/pages/pengeluaran/index.php', buttons: ['Tambah Pengeluaran', 'Edit', 'Hapus'], functions: ['create', 'update', 'delete'] },
  { name: 'Kas Bon', url: '/pages/kas_bon/index.php', buttons: ['Ajukan Kas Bon', 'Setujui', 'Tolak'], functions: ['create', 'approve', 'reject'] }
];

// Run tests
async function runTests() {
  console.log('🚀 Starting Comprehensive Bos Functions Tests in Headed Mode...\n');
  
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
      
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=testbos&password=password123');
      
      await new Promise(resolve => setTimeout(resolve, 2000));
      const currentUrl = page.url();
      console.log('Current URL after login:', currentUrl);
      
      if (!currentUrl.includes('dashboard.php')) {
        throw new Error('Not redirected to dashboard after login');
      }
      
      await page.waitForSelector('.card', { timeout: 5000 });
      
      logSuccess('Login as Bos');
      await takeScreenshot(page, 'bos-login-success');
    } catch (error) {
      logError('Login as Bos', error);
    }

    // Test 2: Check and test buttons on each bos page
    for (const pageConfig of bosPages) {
      try {
        console.log(`\n📋 Testing Page: ${pageConfig.name}`);
        
        await page.goto(config.baseUrl + pageConfig.url);
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        const currentUrl = page.url();
        console.log(`  URL: ${currentUrl}`);
        
        // Check for Indonesian date format
        const pageContent = await page.content();
        const hasIndonesianDate = /Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember/.test(pageContent);
        console.log(`  Indonesian date format: ${hasIndonesianDate ? '✓' : '✗'}`);
        
        // Check for Indonesian number format (Rp)
        const hasIndonesianCurrency = /Rp\s*\d{1,3}(\.\d{3})*/.test(pageContent);
        console.log(`  Indonesian currency format: ${hasIndonesianCurrency ? '✓' : '✗'}`);
        
        // Check for buttons
        for (const buttonText of pageConfig.buttons) {
          try {
            const button = await page.evaluate((text) => {
              const buttons = Array.from(document.querySelectorAll('button, .btn, a.btn'));
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
        
        // Test clicking "Tambah" buttons if found
        for (const buttonText of pageConfig.buttons) {
          if (buttonText.includes('Tambah')) {
            try {
              const button = await page.evaluateHandle((text) => {
                const buttons = Array.from(document.querySelectorAll('button, .btn, a.btn'));
                return buttons.find(btn => btn.textContent.includes(text));
              }, buttonText);
              
              if (button) {
                console.log(`  Testing click on "${buttonText}" button`);
                await button.click();
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                // Check if navigated to form page or modal appeared
                const modal = await page.$('.modal');
                const currentUrlAfterClick = page.url();
                
                if (modal) {
                  console.log(`  ✓ Modal appeared after clicking "${buttonText}"`);
                  await page.keyboard.press('Escape'); // Close modal
                  await new Promise(resolve => setTimeout(resolve, 500));
                } else if (currentUrlAfterClick !== currentUrl) {
                  console.log(`  ✓ Navigated to form page after clicking "${buttonText}"`);
                  await page.goBack();
                  await new Promise(resolve => setTimeout(resolve, 500));
                }
              }
            } catch (error) {
              console.log(`  ⚠️ Error clicking button "${buttonText}": ${error.message}`);
            }
          }
        }
        
        logSuccess(`${pageConfig.name} Page Test`);
        await takeScreenshot(page, `bos-${pageConfig.name.toLowerCase().replace(/ /g, '-')}`);
        
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
