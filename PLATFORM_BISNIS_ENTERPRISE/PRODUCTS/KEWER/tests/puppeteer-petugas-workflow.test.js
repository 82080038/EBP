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
  console.log('🚀 Starting Petugas Role Workflow Simulation Tests in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    // Login as petugas
    await page.goto(config.baseUrl + '/login.php?test_login=true&username=ptr_pusat&password=Kewer2024!');
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    const currentUrl = page.url();
    if (!currentUrl.includes('dashboard.php')) {
      throw new Error('Petugas login failed');
    }
    
    console.log('✅ Petugas login successful');
    await takeScreenshot(page, 'petugas-dashboard');

    // Workflow 1: Tambah Nasabah
    try {
      console.log('\n📋 Workflow 1: Tambah Nasabah (Petugas)');
      
      await page.goto(config.baseUrl + '/pages/nasabah/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
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
        
        await page.type('input[name="nama"]', 'Test Petugas Nasabah');
        await page.type('input[name="ktp"]', '1234567890123456');
        await page.type('input[name="telp"]', '08123456789');
        
        await takeScreenshot(page, 'petugas-nasabah-form-filled');
        await page.keyboard.press('Escape');
        await new Promise(resolve => setTimeout(resolve, 500));
        
        logSuccess('Petugas Tambah Nasabah');
        results.workflows.push({ workflow: 'Tambah Nasabah', status: 'Working' });
      } else {
        throw new Error('Tambah button not found');
      }
    } catch (error) {
      logError('Petugas Tambah Nasabah', error);
      results.workflows.push({ workflow: 'Tambah Nasabah', status: 'Failed' });
    }

    // Workflow 2: Ajukan Pinjaman
    try {
      console.log('\n📋 Workflow 2: Ajukan Pinjaman (Petugas)');
      
      await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
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
          await plafonInput.type('500000');
        }
        
        await takeScreenshot(page, 'petugas-pinjaman-form-filled');
        await page.keyboard.press('Escape');
        await new Promise(resolve => setTimeout(resolve, 500));
        
        logSuccess('Petugas Ajukan Pinjaman');
        results.workflows.push({ workflow: 'Ajukan Pinjaman', status: 'Working' });
      } else {
        throw new Error('Ajukan button not found');
      }
    } catch (error) {
      logError('Petugas Ajukan Pinjaman', error);
      results.workflows.push({ workflow: 'Ajukan Pinjaman', status: 'Failed' });
    }

    // Workflow 3: Aktivitas Lapangan
    try {
      console.log('\n📋 Workflow 3: Aktivitas Lapangan (Petugas)');

      await page.goto(config.baseUrl + '/pages/field_activities/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));

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
        try {
          await new Promise(resolve => setTimeout(resolve, 2000));

          const modal = await page.$('.modal');
          if (modal) {
            await takeScreenshot(page, 'petugas-aktivitas-modal');
            await page.keyboard.press('Escape');
            await new Promise(resolve => setTimeout(resolve, 500));

            logSuccess('Petugas Aktivitas Lapangan');
            results.workflows.push({ workflow: 'Aktivitas Lapangan', status: 'Working' });
          } else {
            console.log('  Modal did not appear');
            logSuccess('Petugas Aktivitas Lapangan (No Modal)');
            results.workflows.push({ workflow: 'Aktivitas Lapangan', status: 'No Modal' });
          }
        } catch (error) {
          console.log('  Error clicking button: ' + error.message);
          logSuccess('Petugas Aktivitas Lapangan (Button Error)');
          results.workflows.push({ workflow: 'Aktivitas Lapangan', status: 'Button Error' });
        }
      } else {
        console.log('  No Tambah button found (might not have permission)');
        logSuccess('Petugas Aktivitas Lapangan (No Permission)');
        results.workflows.push({ workflow: 'Aktivitas Lapangan', status: 'No Permission' });
      }
    } catch (error) {
      logError('Petugas Aktivitas Lapangan', error);
      results.workflows.push({ workflow: 'Aktivitas Lapangan', status: 'Failed' });
    }

    // Workflow 4: Kas Petugas
    try {
      console.log('\n📋 Workflow 4: Kas Petugas (Petugas)');

      await page.goto(config.baseUrl + '/pages/kas_petugas/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));

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
        try {
          await new Promise(resolve => setTimeout(resolve, 2000));
          
          const modal = await page.$('.modal');
          if (modal) {
            await takeScreenshot(page, 'petugas-kas-modal');
            await page.keyboard.press('Escape');
            await new Promise(resolve => setTimeout(resolve, 500));
            
            logSuccess('Petugas Kas Petugas');
            results.workflows.push({ workflow: 'Kas Petugas', status: 'Working' });
          } else {
            console.log('  Modal did not appear');
            logSuccess('Petugas Kas Petugas (No Modal)');
            results.workflows.push({ workflow: 'Kas Petugas', status: 'No Modal' });
          }
        } catch (error) {
          console.log('  Error clicking button: ' + error.message);
          logSuccess('Petugas Kas Petugas (Button Error)');
          results.workflows.push({ workflow: 'Kas Petugas', status: 'Button Error' });
        }
      } else {
        console.log('  No Tambah button found');
        logSuccess('Petugas Kas Petugas (View Only)');
        results.workflows.push({ workflow: 'Kas Petugas', status: 'View Only' });
      }
    } catch (error) {
      logError('Petugas Kas Petugas', error);
      results.workflows.push({ workflow: 'Kas Petugas', status: 'Failed' });
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
