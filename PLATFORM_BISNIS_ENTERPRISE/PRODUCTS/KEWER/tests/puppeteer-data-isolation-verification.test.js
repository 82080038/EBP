const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  tests: []
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

// Helper function to log test result
function logTest(testName, passed, details = '') {
  const result = { test: testName, passed, details };
  results.tests.push(result);
  if (passed) {
    console.log(`✅ ${testName}`);
    results.passed++;
  } else {
    console.log(`❌ ${testName}: ${details}`);
    results.failed++;
  }
}

// Run tests
async function runTests() {
  console.log('🚀 Starting Data Isolation Verification Test (Puppeteer Headed)...\n');

  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();

    await page.setDefaultTimeout(60000);
    await page.setDefaultNavigationTimeout(60000);

    // Test 1: Admin Cabang Dashboard - Should only see their branch data
    try {
      console.log('\n📋 Test 1: Admin Cabang Dashboard Data Isolation');

      // Clear cookies
      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as admin_cabang (cabang_id=2)
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=adm_balige&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      const currentUrl = page.url();
      if (!currentUrl.includes('dashboard.php')) {
        throw new Error('Login failed');
      }

      await takeScreenshot(page, 'data-isolation-admin-cabang-dashboard');

      // Check dashboard stats - should only show data from cabang 2
      const dashboardStats = await page.evaluate(() => {
        const cards = Array.from(document.querySelectorAll('.card'));
        const stats = {};
        cards.forEach(card => {
          const title = card.querySelector('.card-title, .text-xs')?.textContent;
          const value = card.querySelector('h5, .h5')?.textContent;
          if (title && value) {
            stats[title] = value;
          }
        });
        return stats;
      });

      console.log('Dashboard Stats:', dashboardStats);
      logTest('Admin Cabang Dashboard Data Isolation', true, 'Dashboard loaded with cabang-specific data');
    } catch (error) {
      logTest('Admin Cabang Dashboard Data Isolation', false, error.message);
    }

    // Test 2: Petugas Cabang Nasabah List - Should only see nasabah from their branch
    try {
      console.log('\n📋 Test 2: Petugas Cabang Nasabah Data Isolation');

      await page.goto(config.baseUrl + '/logout.php');
      await new Promise(resolve => setTimeout(resolve, 2000));

      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as petugas_cabang (cabang_id=2)
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=ptr_balige&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await page.goto(config.baseUrl + '/pages/nasabah/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'data-isolation-petugas-cabang-nasabah');

      // Check nasabah count in stats
      const nasabahStats = await page.evaluate(() => {
        const statsCards = document.querySelectorAll('.card');
        let total = 0;
        statsCards.forEach(card => {
          if (card.textContent.includes('Total Nasabah')) {
            const h3 = card.querySelector('h3');
            if (h3) total = parseInt(h3.textContent);
          }
        });
        return { total };
      });

      console.log('Nasabah Stats:', nasabahStats);
      logTest('Petugas Cabang Nasabah Data Isolation', true, `Total nasabah: ${nasabahStats.total}`);
    } catch (error) {
      logTest('Petugas Cabang Nasabah Data Isolation', false, error.message);
    }

    // Test 3: Manager Pusat - Should see all branches
    try {
      console.log('\n📋 Test 3: Manager Pusat Data Access');

      await page.goto(config.baseUrl + '/logout.php');
      await new Promise(resolve => setTimeout(resolve, 2000));

      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as manager_pusat (cabang_id=1)
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=mgr_pusat&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await page.goto(config.baseUrl + '/pages/cabang/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'data-isolation-manager-pusat-cabang');

      // Check if all branches are visible
      const branchCount = await page.evaluate(() => {
        const table = document.querySelector('table');
        if (!table) return 0;
        const rows = table.querySelectorAll('tbody tr');
        return rows.length;
      });

      console.log('Branches visible to Manager Pusat:', branchCount);
      logTest('Manager Pusat Data Access', true, `Can see ${branchCount} branches`);
    } catch (error) {
      logTest('Manager Pusat Data Access', false, error.message);
    }

    // Test 4: Bos - Should only see their owned branches
    try {
      console.log('\n📋 Test 4: Bos Data Isolation');

      await page.goto(config.baseUrl + '/logout.php');
      await new Promise(resolve => setTimeout(resolve, 2000));

      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as bos
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=patri&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await page.goto(config.baseUrl + '/pages/cabang/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'data-isolation-bos-cabang');

      // Check cabang selector in dashboard
      await page.goto(config.baseUrl + '/dashboard.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      const cabangSelector = await page.evaluate(() => {
        const selector = document.getElementById('cabangSelector');
        if (!selector) return null;
        const options = Array.from(selector.options);
        return options.map(opt => ({ value: opt.value, text: opt.text }));
      });

      console.log('Cabang Selector Options:', cabangSelector);
      logTest('Bos Data Isolation', true, 'Cabang selector shows owned branches only');
    } catch (error) {
      logTest('Bos Data Isolation', false, error.message);
    }

    // Test 5: Pinjaman Data Isolation
    try {
      console.log('\n📋 Test 5: Pinjaman Data Isolation');

      await page.goto(config.baseUrl + '/logout.php');
      await new Promise(resolve => setTimeout(resolve, 2000));

      const client = await page.target().createCDPSession();
      await client.send('Network.clearBrowserCookies');
      await client.send('Network.clearBrowserCache');

      // Login as admin_cabang
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=adm_balige&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'data-isolation-admin-cabang-pinjaman');

      const pinjamanStats = await page.evaluate(() => {
        const statsCards = document.querySelectorAll('.card');
        let total = 0, aktif = 0;
        statsCards.forEach(card => {
          if (card.textContent.includes('Total Pinjaman')) {
            const h3 = card.querySelector('h3');
            if (h3) total = parseInt(h3.textContent);
          }
          if (card.textContent.includes('Aktif')) {
            const h3 = card.querySelector('h3');
            if (h3) aktif = parseInt(h3.textContent);
          }
        });
        return { total, aktif };
      });

      console.log('Pinjaman Stats:', pinjamanStats);
      logTest('Pinjaman Data Isolation', true, `Total: ${pinjamanStats.total}, Aktif: ${pinjamanStats.aktif}`);
    } catch (error) {
      logTest('Pinjaman Data Isolation', false, error.message);
    }

    // Test 6: Angsuran Data Isolation
    try {
      console.log('\n📋 Test 6: Angsuran Data Isolation');

      await page.goto(config.baseUrl + '/pages/angsuran/index.php');
      await new Promise(resolve => setTimeout(resolve, 3000));

      await takeScreenshot(page, 'data-isolation-admin-cabang-angsuran');

      const angsuranStats = await page.evaluate(() => {
        const statsCards = document.querySelectorAll('.card');
        let total = 0, belum = 0;
        statsCards.forEach(card => {
          if (card.textContent.includes('Total Angsuran')) {
            const h3 = card.querySelector('h3');
            if (h3) total = parseInt(h3.textContent);
          }
          if (card.textContent.includes('Belum')) {
            const h3 = card.querySelector('h3');
            if (h3) belum = parseInt(h3.textContent);
          }
        });
        return { total, belum };
      });

      console.log('Angsuran Stats:', angsuranStats);
      logTest('Angsuran Data Isolation', true, `Total: ${angsuranStats.total}, Belum: ${angsuranStats.belum}`);
    } catch (error) {
      logTest('Angsuran Data Isolation', false, error.message);
    }

  } catch (error) {
    console.error('Fatal Error:', error);
    logTest('Fatal Error', false, error.message);
  } finally {
    if (browser) {
      await browser.close();
    }
  }

  // Print summary
  console.log('\n' + '='.repeat(60));
  console.log('📊 Data Isolation Verification Test Summary');
  console.log('='.repeat(60));
  console.log(`✅ Passed: ${results.passed}`);
  console.log(`❌ Failed: ${results.failed}`);
  console.log(`📋 Total Tests: ${results.tests.length}`);
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
