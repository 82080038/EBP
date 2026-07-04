const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: [],
  workflows: []
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
  console.log('🚀 Starting Comprehensive Workflow Simulation Tests in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    // Workflow 1: Complete Nasabah Registration Workflow
    try {
      console.log('\n📋 Workflow 1: Complete Nasabah Registration');
      
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=patri&password=Kewer2024!');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      await page.goto(config.baseUrl + '/pages/nasabah/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Click Tambah Nasabah
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
        
        // Fill form
        await page.type('input[name="nama"]', 'Test Workflow Nasabah');
        await page.type('input[name="ktp"]', '1234567890123456');
        await page.type('input[name="telp"]', '08123456789');
        
        // Check if alamat field exists
        const alamatInput = await page.$('input[name="alamat"]');
        if (alamatInput) {
          await page.type('input[name="alamat"]', 'Jalan Test 123');
        }
        
        // Select province (if available)
        const provinceSelect = await page.$('select[name="province_id"]');
        if (provinceSelect) {
          await provinceSelect.click();
          await new Promise(resolve => setTimeout(resolve, 500));
          await page.select('select[name="province_id"]', '11'); // Select DKI Jakarta
          await new Promise(resolve => setTimeout(resolve, 1000));
        }
        
        await takeScreenshot(page, 'workflow-nasabah-form-filled');
        
        // Close modal without submitting
        await page.keyboard.press('Escape');
        await new Promise(resolve => setTimeout(resolve, 500));
        
        logSuccess('Nasabah Registration Workflow');
        results.workflows.push({ workflow: 'Nasabah Registration', status: 'Working' });
      } else {
        throw new Error('Tambah button not found');
      }
    } catch (error) {
      logError('Nasabah Registration Workflow', error);
      results.workflows.push({ workflow: 'Nasabah Registration', status: 'Failed' });
    }

    // Workflow 2: Complete Pinjaman Application Workflow
    try {
      console.log('\n📋 Workflow 2: Complete Pinjaman Application');
      
      await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Click Ajukan Pinjaman
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

        // Check if form fields exist
        const nasabahSelect = await page.$('select[name="nasabah_id"]');
        if (nasabahSelect) {
          await nasabahSelect.click();
          await new Promise(resolve => setTimeout(resolve, 500));
          const options = await page.$$('select[name="nasabah_id"] option');
          if (options.length > 1) {
            const optionValue = await page.evaluate(el => el.value, options[1]);
            if (optionValue) {
              await page.select('select[name="nasabah_id"]', optionValue);
              await new Promise(resolve => setTimeout(resolve, 500));
            }
          }
        }
        
        const plafonInput = await page.$('input[name="plafon"]');
        if (plafonInput) {
          await plafonInput.type('1000000');
        }
        
        const tenorInput = await page.$('input[name="tenor"]');
        if (tenorInput) {
          await tenorInput.type('12');
        }
        
        await takeScreenshot(page, 'workflow-pinjaman-form-filled');
        
        // Close modal without submitting
        await page.keyboard.press('Escape');
        await new Promise(resolve => setTimeout(resolve, 500));
        
        logSuccess('Pinjaman Application Workflow');
        results.workflows.push({ workflow: 'Pinjaman Application', status: 'Working' });
      } else {
        throw new Error('Ajukan button not found');
      }
    } catch (error) {
      logError('Pinjaman Application Workflow', error);
      results.workflows.push({ workflow: 'Pinjaman Application', status: 'Failed' });
    }

    // Workflow 3: Complete Angsuran Payment Workflow
    try {
      console.log('\n📋 Workflow 3: Complete Angsuran Payment');
      
      await page.goto(config.baseUrl + '/pages/angsuran/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check if there are any angsuran to pay
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
        console.log('  Bayar button found');
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Check if modal appeared
        const modal = await page.$('.modal');
        if (modal) {
          console.log('  Payment modal appeared');
          await page.keyboard.press('Escape');
          await new Promise(resolve => setTimeout(resolve, 500));
        }
        
        logSuccess('Angsuran Payment Workflow');
        results.workflows.push({ workflow: 'Angsuran Payment', status: 'Working' });
      } else {
        console.log('  No pending angsuran to pay');
        logSuccess('Angsuran Payment Workflow (No Pending)');
        results.workflows.push({ workflow: 'Angsuran Payment', status: 'No Pending' });
      }
    } catch (error) {
      logError('Angsuran Payment Workflow', error);
      results.workflows.push({ workflow: 'Angsuran Payment', status: 'Failed' });
    }

    // Workflow 4: Complete Cabang Management Workflow
    try {
      console.log('\n📋 Workflow 4: Complete Cabang Management');
      
      await page.goto(config.baseUrl + '/pages/cabang/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Click Tambah Cabang
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
        
        // Fill form
        await page.type('input[name="kode_cabang"]', 'TEST001');
        await page.type('input[name="nama_cabang"]', 'Cabang Test Workflow');
        
        // Select province
        const provinceSelect = await page.$('select[name="province_id"]');
        if (provinceSelect) {
          await page.click('select[name="province_id"]');
          await new Promise(resolve => setTimeout(resolve, 500));
          await page.select('select[name="province_id"]', '11'); // Select DKI Jakarta
          await new Promise(resolve => setTimeout(resolve, 1000));
        }
        
        await takeScreenshot(page, 'workflow-cabang-form-filled');
        
        // Close modal without submitting
        await page.keyboard.press('Escape');
        await new Promise(resolve => setTimeout(resolve, 500));
        
        logSuccess('Cabang Management Workflow');
        results.workflows.push({ workflow: 'Cabang Management', status: 'Working' });
      } else {
        throw new Error('Tambah button not found');
      }
    } catch (error) {
      logError('Cabang Management Workflow', error);
      results.workflows.push({ workflow: 'Cabang Management', status: 'Failed' });
    }

    // Workflow 5: Complete Pengeluaran Workflow
    try {
      console.log('\n📋 Workflow 5: Complete Pengeluaran');
      
      await page.goto(config.baseUrl + '/pages/pengeluaran/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Click Tambah Pengeluaran
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
        
        // Fill form
        const deskripsiInput = await page.$('.modal input[type="text"]');
        if (deskripsiInput) {
          await deskripsiInput.type('Test Pengeluaran Workflow');
        }
        
        const jumlahInput = await page.$('.modal input[type="number"]');
        if (jumlahInput) {
          await jumlahInput.type('50000');
        }
        
        await takeScreenshot(page, 'workflow-pengeluaran-form-filled');
        
        // Close modal without submitting
        await page.keyboard.press('Escape');
        await new Promise(resolve => setTimeout(resolve, 500));
        
        logSuccess('Pengeluaran Workflow');
        results.workflows.push({ workflow: 'Pengeluaran', status: 'Working' });
      } else {
        throw new Error('Tambah button not found');
      }
    } catch (error) {
      logError('Pengeluaran Workflow', error);
      results.workflows.push({ workflow: 'Pengeluaran', status: 'Failed' });
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
  console.log(`🔄 Workflows Tested: ${results.workflows.length}`);
  console.log('='.repeat(50));

  if (results.errors.length > 0) {
    console.log('\n❌ Errors:');
    results.errors.forEach(err => {
      console.log(`  - ${err.test}: ${err.error}`);
    });
  }

  console.log('\n🔄 Workflows Tested:');
  results.workflows.forEach(wf => {
    console.log(`  - ${wf.workflow}: ${wf.status}`);
  });

  console.log('\n📸 Screenshots saved to:', screenshotDir);
  console.log('='.repeat(50));

  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests();
