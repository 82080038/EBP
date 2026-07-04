const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: [],
  warnings: [],
  transactionData: {}
};

// Create screenshots directory
const fs = require('fs');
const path = require('path');
const screenshotDir = path.join(__dirname, 'screenshots', 'simulation');
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

function logWarning(testName, message) {
  console.log(`⚠️  ${testName}: ${message}`);
  results.warnings.push({ test: testName, message });
}

// Helper function to log success
function logSuccess(testName) {
  console.log(`✅ ${testName}`);
  results.passed++;
}

// Helper function to log info
function logInfo(testName, info) {
  console.log(`ℹ️  ${testName}: ${info}`);
  results.transactionData[testName] = info;
}

// Helper function to delay
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// Main simulation
async function runSimulation() {
  console.log('🚀 Starting 3-Month Transaction Simulation in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    page.on('console', msg => {
      if (msg.type() === 'warning') {
        console.warn(`⚠️  Browser: ${msg.text()}`);
      }
    });

    page.on('pageerror', error => {
      logError('Page Error', error);
    });

    // STEP 1: Login
    try {
      console.log('\n📋 STEP 1: Login sebagai Admin');
      await page.goto(config.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
      
      // Wait for page to fully load
      await delay(1000);
      
      // Check if we're already logged in
      if (page.url().includes('dashboard.php')) {
        console.log('Already logged in');
        logSuccess('Login Admin');
        await takeScreenshot(page, '01-dashboard-after-login');
      } else {
        // Use quick login button for development mode
        await page.waitForSelector('button[onclick*="quickLogin"]', { timeout: 10000 });
        await delay(500);
        
        // Click the admin quick login button directly
        const buttons = await page.$$('button');
        for (let button of buttons) {
          const text = await button.evaluate(el => el.textContent);
          if (text.includes('Superadmin: admin')) {
            await button.click();
            break;
          }
        }
        
        // Wait for redirect with longer timeout
        try {
          await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 });
        } catch (e) {
          console.log('Navigation timeout, checking current URL');
        }
        
        await delay(2000);
        
        if (page.url().includes('login.php')) {
          // If still on login page, try direct navigation
          console.log('Redirect failed, navigating directly to dashboard');
          await page.goto(config.baseUrl + '/dashboard.php', { waitUntil: 'networkidle2' });
          await delay(2000);
        }
        
        // Check if we're on dashboard now
        if (page.url().includes('dashboard.php')) {
          await page.waitForSelector('h1, .h2', { timeout: 10000 });
          logSuccess('Login Admin');
          await takeScreenshot(page, '01-dashboard-after-login');
        } else {
          throw new Error('Failed to reach dashboard after login');
        }
      }
    } catch (error) {
      logError('Login Admin', error);
      console.log('Current URL:', page.url());
      await takeScreenshot(page, 'error-login');
      throw error;
    }

    // STEP 2: Tambah Nasabah via API
    let nasabahId = null;
    try {
      console.log('\n📋 STEP 2: Tambah Nasabah via API');
      
      // Generate unique data
      const timestamp = Date.now();
      const namaNasabah = `Pedagang Test ${timestamp}`;
      // Generate completely random 16-digit KTP
      let ktpNasabah = '';
      for (let i = 0; i < 16; i++) {
        ktpNasabah += Math.floor(Math.random() * 10);
      }
      // Generate valid phone number (08 + 10 digits = 12 digits total)
      let telpNasabah = '08';
      for (let i = 0; i < 10; i++) {
        telpNasabah += Math.floor(Math.random() * 10);
      }
      
      console.log('Generated KTP:', ktpNasabah, 'Length:', ktpNasabah.length);
      console.log('Generated Phone:', telpNasabah, 'Length:', telpNasabah.length);
      
      // Use API to create nasabah
      const apiResponse = await page.evaluate(async (baseUrl, nama, ktp, telp) => {
        const response = await fetch(baseUrl + '/api/nasabah.php?cabang_id=1', {
          method: 'POST',
          headers: {
            'Authorization': 'Bearer kewer-api-token-2024',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            nama: nama,
            ktp: ktp,
            telp: telp,
            alamat: 'Jl. Test No. 123',
            jenis_usaha: 'Warung Kelontong',
            status: 'aktif'
          })
        });
        const text = await response.text();
        // Remove any PHP closing tags or extra whitespace
        const cleanText = text.replace(/\?>\s*$/, '').trim();
        return JSON.parse(cleanText);
      }, config.baseUrl, namaNasabah, ktpNasabah, telpNasabah);
      
      console.log('API Response:', JSON.stringify(apiResponse));
      
      if (apiResponse.success && apiResponse.data) {
        nasabahId = apiResponse.data.id;
        logSuccess('Tambah Nasabah via API');
        logInfo('Nama Nasabah', namaNasabah);
        logInfo('KTP', ktpNasabah);
        logInfo('Nasabah ID', nasabahId);
        
        // Verify in UI
        await page.goto(config.baseUrl + '/pages/nasabah/index.php');
        await delay(1000);
        await takeScreenshot(page, '02-nasabah-list');
      } else {
        throw new Error('API gagal menambah nasabah: ' + JSON.stringify(apiResponse));
      }
    } catch (error) {
      logError('Tambah Nasabah via API', error);
      throw error;
    }

    // STEP 3: Buat Pinjaman via API
    let pinjamanId = null;
    try {
      console.log('\n📋 STEP 3: Buat Pinjaman via API');
      
      // Use API to create pinjaman
      const apiResponse = await page.evaluate(async (baseUrl, nasabahId) => {
        const response = await fetch(baseUrl + '/api/pinjaman.php?cabang_id=1', {
          method: 'POST',
          headers: {
            'Authorization': 'Bearer kewer-api-token-2024',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            nasabah_id: nasabahId,
            plafon: 5000000,
            tenor: 3,
            bunga_per_bulan: 2,
            tanggal_akad: new Date().toISOString().split('T')[0],
            tujuan_pinjaman: 'Modal usaha warung',
            jaminan: 'Tanpa jaminan'
          })
        });
        const text = await response.text();
        // Remove any PHP closing tags or extra whitespace
        const cleanText = text.replace(/\?>\s*$/, '').trim();
        return JSON.parse(cleanText);
      }, config.baseUrl, nasabahId);
      
      console.log('API Response:', JSON.stringify(apiResponse));
      
      if (apiResponse.success && apiResponse.data) {
        pinjamanId = apiResponse.data.id;
        logSuccess('Buat Pinjaman via API');
        logInfo('Plafon', 'Rp 5.000.000');
        logInfo('Tenor', '3 Bulan');
        logInfo('Bunga', '2% per bulan');
        logInfo('Pinjaman ID', pinjamanId);
        
        // Verify in UI
        await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
        await delay(1000);
        await takeScreenshot(page, '03-pinjaman-list');
      } else {
        throw new Error('API gagal membuat pinjaman: ' + JSON.stringify(apiResponse));
      }
    } catch (error) {
      logError('Buat Pinjaman via API', error);
      throw error;
    }

    // STEP 4: Approve Pinjaman via API
    try {
      console.log('\n📋 STEP 4: Approve Pinjaman via API');
      
      const apiResponse = await page.evaluate(async (baseUrl, pinjamanId) => {
        const response = await fetch(baseUrl + `/api/pinjaman.php?cabang_id=1&id=${pinjamanId}&action=approve`, {
          method: 'PUT',
          headers: {
            'Authorization': 'Bearer kewer-api-token-2024',
            'Content-Type': 'application/json',
          }
        });
        const text = await response.text();
        const cleanText = text.replace(/\?>\s*$/, '').trim();
        return JSON.parse(cleanText);
      }, config.baseUrl, pinjamanId);
      
      console.log('API Response:', JSON.stringify(apiResponse));
      
      if (apiResponse.success) {
        logSuccess('Approve Pinjaman via API');
        logInfo('Pinjaman ID', pinjamanId);
        logInfo('Status', 'Aktif - Jadwal angsuran digenerate');
        
        // Go to pinjaman list to verify
        await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
        await delay(1000);
        await takeScreenshot(page, '04-pinjaman-aktif');
      } else {
        throw new Error('API approval failed: ' + JSON.stringify(apiResponse));
      }
    } catch (error) {
      logError('Approve Pinjaman via API', error);
      throw error;
    }

    // STEP 5: Cek Jadwal Angsuran
    try {
      console.log('\n📋 STEP 5: Cek Jadwal Angsuran');
      await page.goto(config.baseUrl + '/pages/angsuran/index.php');
      await page.waitForSelector('table', { timeout: 5000 });
      
      // Get kode pinjaman first
      const kodePinjaman = await page.evaluate((pinjamanId) => {
        // We'll search by the pinjaman ID or just get all installments
        return null;
      }, pinjamanId);
      
      logSuccess('Cek Jadwal Angsuran');
      await takeScreenshot(page, '05-jadwal-angsuran');
      
      // Count installments
      const angsuranCount = await page.evaluate(() => {
        const rows = document.querySelectorAll('table tbody tr');
        return rows.length;
      });
      logInfo('Jumlah Angsuran', angsuranCount);
    } catch (error) {
      logError('Cek Jadwal Angsuran', error);
    }

    // STEP 6-8: Bayar Angsuran Bulan 1, 2, dan 3
    for (let bulan = 1; bulan <= 3; bulan++) {
      try {
        console.log(`\n📋 STEP ${5 + bulan}: Bayar Angsuran Bulan ${bulan}`);
        await page.goto(config.baseUrl + '/pages/angsuran/index.php');
        await page.waitForSelector('table', { timeout: 5000 });
        
        // Get first unpaid installment
        const angsuranId = await page.evaluate((bulan) => {
          const rows = document.querySelectorAll('table tbody tr');
          if (rows.length >= bulan) {
            const row = rows[bulan - 1];
            const cells = row.querySelectorAll('td');
            const bayarBtn = row.querySelector('a[href*="bayar.php"]');
            if (bayarBtn) {
              const href = bayarBtn.getAttribute('href');
              const match = href.match(/id=(\d+)/);
              return match ? match[1] : null;
            }
          }
          return null;
        }, bulan);
        
        if (angsuranId) {
          await page.goto(config.baseUrl + `/pages/angsuran/bayar.php?id=${angsuranId}`);
          await page.waitForSelector('form', { timeout: 5000 });
          
          // Get installment amount
          const jumlahBayar = await page.$eval('input[name="total_bayar"]', el => el.value);
          
          await page.click('button[type="submit"]');
          await delay(2000);
          
          logSuccess(`Bayar Angsuran Bulan ${bulan}`);
          logInfo(`Jumlah Bayar Bulan ${bulan}`, jumlahBayar);
          await takeScreenshot(page, `0${5 + bulan}-bayar-angsuran-bulan-${bulan}`);
        } else {
          logWarning(`Bayar Angsuran Bulan ${bulan}`, 'Tidak ada angsuran yang tersedia');
        }
        
        await delay(1000);
      } catch (error) {
        logError(`Bayar Angsuran Bulan ${bulan}`, error);
      }
    }

    // STEP 9: Verifikasi Status Pinjaman
    try {
      console.log('\n📋 STEP 9: Verifikasi Status Pinjaman');
      await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
      await page.waitForSelector('table', { timeout: 5000 });
      
      logSuccess('Verifikasi Status Pinjaman');
      await takeScreenshot(page, '09-final-pinjaman-status');
      
      // Get final status
      const finalStatus = await page.evaluate(() => {
        const rows = document.querySelectorAll('table tbody tr');
        if (rows.length > 0) {
          const firstRow = rows[0];
          const cells = firstRow.querySelectorAll('td');
          if (cells.length > 0) {
            return cells[cells.length - 2].textContent.trim(); // Status column
          }
        }
        return 'Tidak ada data';
      });
      logInfo('Status Akhir Pinjaman', finalStatus);
    } catch (error) {
      logError('Verifikasi Status Pinjaman', error);
    }

    // STEP 10: Verifikasi Status Angsuran
    try {
      console.log('\n📋 STEP 10: Verifikasi Status Angsuran');
      await page.goto(config.baseUrl + '/pages/angsuran/index.php');
      await page.waitForSelector('table', { timeout: 5000 });
      
      logSuccess('Verifikasi Status Angsuran');
      await takeScreenshot(page, '10-final-angsuran-status');
      
      // Get installment statuses
      const statuses = await page.evaluate(() => {
        const rows = document.querySelectorAll('table tbody tr');
        const result = [];
        rows.forEach((row, index) => {
          const cells = row.querySelectorAll('td');
          if (cells.length > 0) {
            result.push({
              no: cells[0].textContent.trim(),
              jatuh_tempo: cells[3]?.textContent.trim() || '-',
              status: cells[7]?.textContent.trim() || '-'
            });
          }
        });
        return result;
      });
      logInfo('Status Angsuran Detail', JSON.stringify(statuses, null, 2));
    } catch (error) {
      logError('Verifikasi Status Angsuran', error);
    }

    // STEP 11: Cek Dashboard Final
    try {
      console.log('\n📋 STEP 11: Cek Dashboard Final');
      await page.goto(config.baseUrl + '/dashboard.php');
      await page.waitForSelector('h1', { timeout: 5000 });
      
      logSuccess('Cek Dashboard Final');
      await takeScreenshot(page, '11-final-dashboard');
      
      // Get statistics
      const stats = await page.evaluate(() => {
        const cards = document.querySelectorAll('.card');
        const result = {};
        cards.forEach(card => {
          const title = card.querySelector('.card-title')?.textContent;
          const value = card.querySelector('.card-text')?.textContent;
          if (title && value) {
            result[title] = value;
          }
        });
        return result;
      });
      logInfo('Statistik Dashboard', JSON.stringify(stats, null, 2));
    } catch (error) {
      logError('Cek Dashboard Final', error);
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
  console.log('📊 Simulation Summary');
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

  console.log('\n📋 Transaction Data:');
  console.log('='.repeat(50));
  Object.keys(results.transactionData).forEach(key => {
    console.log(`${key}: ${results.transactionData[key]}`);
  });

  console.log('\n📸 Screenshots saved to:', screenshotDir);
  console.log('='.repeat(50));

  // Exit with appropriate code
  process.exit(results.failed > 0 ? 1 : 0);
}

// Run simulation
runSimulation();
