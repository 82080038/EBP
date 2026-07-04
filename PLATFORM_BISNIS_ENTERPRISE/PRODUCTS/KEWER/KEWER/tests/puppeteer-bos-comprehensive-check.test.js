const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: [],
  pages: []
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

// Helper function to log page info
function logPageInfo(pageName, url, screenshot) {
  results.pages.push({ page: pageName, url, screenshot });
}

// Bos menu pages to check
const bosPages = [
  { name: 'Dashboard', url: '/dashboard.php' },
  { name: 'Nasabah', url: '/pages/nasabah/index.php' },
  { name: 'Pinjaman', url: '/pages/pinjaman/index.php' },
  { name: 'Angsuran', url: '/pages/angsuran/index.php' },
  { name: 'Aktivitas Lapangan', url: '/pages/aktivitas_lapangan/index.php' },
  { name: 'Rekonsiliasi Kas', url: '/pages/rekonsiliasi_kas/index.php' },
  { name: 'Cabang', url: '/pages/cabang/index.php' },
  { name: 'Setting Bunga', url: '/pages/setting_bunga/index.php' },
  { name: 'Pengeluaran', url: '/pages/pengeluaran/index.php' },
  { name: 'Kas Bon', url: '/pages/kas_bon/index.php' },
  { name: 'Family Risk', url: '/pages/family_risk/index.php' },
  { name: 'Petugas', url: '/pages/petugas/index.php' },
  { name: 'Laporan', url: '/pages/laporan/index.php' },
  { name: 'Rute Harian', url: '/pages/rute_harian/index.php' },
  { name: 'Kinerja Petugas', url: '/pages/kinerja/index.php' },
  { name: 'Delegasi Permission', url: '/pages/bos/delegated_permissions.php' },
  { name: 'Audit Trail', url: '/pages/audit/index.php' },
  { name: 'Permissions', url: '/pages/permissions/index.php' }
];

// Run tests
async function runTests() {
  console.log('🚀 Starting Comprehensive Bos Pages Check in Headed Mode...\n');
  
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

    // Test 2: Check all bos pages
    for (const bosPage of bosPages) {
      try {
        console.log(`\n📋 Checking Page: ${bosPage.name}`);
        
        await page.goto(config.baseUrl + bosPage.url);
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        const currentUrl = page.url();
        console.log(`  URL: ${currentUrl}`);
        
        // Check for navbar
        const hasNavbar = await page.$('nav.navbar');
        console.log(`  Navbar: ${hasNavbar ? '✓' : '✗'}`);
        
        // Check for sidebar
        const hasSidebar = await page.$('.nav-link');
        console.log(`  Sidebar: ${hasSidebar ? '✓' : '✗'}`);
        
        // Check for main content area
        const hasMain = await page.$('main');
        console.log(`  Main Content: ${hasMain ? '✓' : '✗'}`);
        
        // Check for any error messages
        const errorAlert = await page.$('.alert-danger');
        if (errorAlert) {
          const errorText = await page.$eval('.alert-danger', el => el.textContent);
          console.log(`  ⚠️ Error found: ${errorText}`);
          logError(`${bosPage.name} - Error Alert`, new Error(errorText));
        } else {
          console.log(`  No errors found`);
        }
        
        // Check for PHP errors/warnings
        const pageContent = await page.content();
        if (pageContent.includes('Warning:') || pageContent.includes('Fatal error:') || pageContent.includes('Parse error:')) {
          console.log(`  ⚠️ PHP error detected in page content`);
          logError(`${bosPage.name} - PHP Error`, new Error('PHP error detected'));
        }
        
        const screenshot = await takeScreenshot(page, `bos-page-${bosPage.name.toLowerCase().replace(/ /g, '-')}`);
        
        logPageInfo(bosPage.name, currentUrl, screenshot);
        logSuccess(`${bosPage.name} Check`);
        
      } catch (error) {
        logError(`${bosPage.name} Check`, error);
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
  console.log(`📄 Pages Checked: ${results.pages.length}`);
  console.log('='.repeat(50));

  if (results.errors.length > 0) {
    console.log('\n❌ Errors:');
    results.errors.forEach(err => {
      console.log(`  - ${err.test}: ${err.error}`);
    });
  }

  console.log('\n📋 Pages Checked:');
  results.pages.forEach(p => {
    console.log(`  - ${p.page}: ${p.url}`);
  });

  console.log('\n📸 Screenshots saved to:', screenshotDir);
  console.log('='.repeat(50));

  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests();
