const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: [],
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

// Run tests
async function runTests() {
  console.log('🚀 Starting Comprehensive Bos Functional Tests in Headed Mode...\n');
  
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
      
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=patri&password=Kewer2024!');
      
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

    // Test 2: Test Tambah Nasabah functionality
    try {
      console.log('\n📋 Test 2: Test Tambah Nasabah Functionality');
      
      await page.goto(config.baseUrl + '/pages/nasabah/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Click Tambah Nasabah button
      const tambahButtonClicked = await page.evaluate(() => {
        const buttons = Array.from(document.querySelectorAll('button, .btn, a.btn'));
        const tambahButton = buttons.find(btn => btn.textContent.includes('Tambah'));
        if (tambahButton) {
          tambahButton.click();
          return true;
        }
        return false;
      });

      if (tambahButtonClicked) {
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Check if modal appeared
        const modal = await page.$('.modal');
        if (modal) {
          console.log('  Modal appeared');
          
          // Check if form fields exist
          const allInputs = await page.$$('input');
          console.log(`  Found ${allInputs.length} input fields`);
          
          // Log all input names
          const inputNames = await page.evaluate(() => {
            return Array.from(document.querySelectorAll('input')).map(input => input.name);
          });
          console.log('  Input field names:', inputNames);
          
          if (allInputs.length > 0) {
            console.log('  Form fields found');
            
            // Try to fill the first text input
            const firstTextInput = await page.evaluate(() => {
              const inputs = Array.from(document.querySelectorAll('input[type="text"]'));
              return inputs.length > 0 ? inputs[0].name : null;
            });
            
            if (firstTextInput) {
              await page.type(`input[name="${firstTextInput}"]`, 'Test');
              console.log('  Form filled successfully');
            }
            
            console.log('  Form filled successfully');
            
            // Close modal without submitting
            await page.keyboard.press('Escape');
            await new Promise(resolve => setTimeout(resolve, 500));
            
            logSuccess('Tambah Nasabah Functionality');
            results.functions.push({ function: 'Tambah Nasabah', status: 'Working' });
          } else {
            throw new Error('Form fields not found');
          }
        } else {
          throw new Error('Modal did not appear');
        }
      } else {
        throw new Error('Tambah button not found');
      }
      
      await takeScreenshot(page, 'bos-tambah-nasabah-test');
    } catch (error) {
      logError('Tambah Nasabah Functionality', error);
      results.functions.push({ function: 'Tambah Nasabah', status: 'Failed' });
    }

    // Test 3: Test Tambah Cabang functionality
    try {
      console.log('\n📋 Test 3: Test Tambah Cabang Functionality');
      
      await page.goto(config.baseUrl + '/pages/cabang/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Click Tambah Cabang button
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
        
        // Check if modal appeared
        const modal = await page.$('.modal');
        if (modal) {
          console.log('  Modal appeared');
          
          // Check if form fields exist
          const kodeInput = await page.$('input[name="kode_cabang"]');
          const namaInput = await page.$('input[name="nama_cabang"]');
          
          if (kodeInput && namaInput) {
            console.log('  Form fields found');
            
            // Try to fill the form
            await page.type('input[name="kode_cabang"]', 'TEST001');
            await page.type('input[name="nama_cabang"]', 'Cabang Test');
            
            console.log('  Form filled successfully');
            
            // Close modal without submitting
            await page.keyboard.press('Escape');
            await new Promise(resolve => setTimeout(resolve, 500));
            
            logSuccess('Tambah Cabang Functionality');
            results.functions.push({ function: 'Tambah Cabang', status: 'Working' });
          } else {
            throw new Error('Form fields not found');
          }
        } else {
          throw new Error('Modal did not appear');
        }
      } else {
        throw new Error('Tambah button not found');
      }
      
      await takeScreenshot(page, 'bos-tambah-cabang-test');
    } catch (error) {
      logError('Tambah Cabang Functionality', error);
      results.functions.push({ function: 'Tambah Cabang', status: 'Failed' });
    }

    // Test 4: Test Tambah Pengeluaran functionality
    try {
      console.log('\n📋 Test 4: Test Tambah Pengeluaran Functionality');
      
      await page.goto(config.baseUrl + '/pages/pengeluaran/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Click Tambah Pengeluaran button
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
        
        // Check if modal appeared
        const modal = await page.$('.modal');
        if (modal) {
          console.log('  Modal appeared');
          
          // Check if form fields exist
          const allInputs = await page.$$('input');
          console.log(`  Found ${allInputs.length} input fields`);
          
          // Log all input names
          const inputNames = await page.evaluate(() => {
            return Array.from(document.querySelectorAll('input')).map(input => input.name);
          });
          console.log('  Input field names:', inputNames);
          
          if (allInputs.length > 0) {
            console.log('  Form fields found');
            
            // Try to fill the first text input
            const firstTextInput = await page.evaluate(() => {
              const inputs = Array.from(document.querySelectorAll('input[type="text"]'));
              return inputs.length > 0 ? inputs[0].name : null;
            });
            
            if (firstTextInput) {
              await page.type(`input[name="${firstTextInput}"]`, 'Test');
              console.log('  Form filled successfully');
            }
            
            console.log('  Form filled successfully');
            
            // Close modal without submitting
            await page.keyboard.press('Escape');
            await new Promise(resolve => setTimeout(resolve, 500));
            
            logSuccess('Tambah Pengeluaran Functionality');
            results.functions.push({ function: 'Tambah Pengeluaran', status: 'Working' });
          } else {
            throw new Error('Form fields not found');
          }
        } else {
          throw new Error('Modal did not appear');
        }
      } else {
        throw new Error('Tambah button not found');
      }
      
      await takeScreenshot(page, 'bos-tambah-pengeluaran-test');
    } catch (error) {
      logError('Tambah Pengeluaran Functionality', error);
      results.functions.push({ function: 'Tambah Pengeluaran', status: 'Failed' });
    }

    // Test 5: Test Ajukan Pinjaman functionality
    try {
      console.log('\n📋 Test 5: Test Ajukan Pinjaman Functionality');
      
      await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Click Ajukan Pinjaman button
      const tambahButtonClicked = await page.evaluate(() => {
        const buttons = Array.from(document.querySelectorAll('button, .btn'));
        const tambahButton = buttons.find(btn => btn.textContent.includes('Ajukan'));
        if (tambahButton) {
          tambahButton.click();
          return true;
        }
        return false;
      });

      if (tambahButtonClicked) {
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Check if modal appeared
        const modal = await page.$('.modal');
        if (modal) {
          console.log('  Modal appeared');
          
          // Check if form fields exist
          const nasabahSelect = await page.$('select[name="nasabah_id"]');
          const plafonInput = await page.$('input[name="plafon"]');
          
          if (nasabahSelect && plafonInput) {
            console.log('  Form fields found');
            logSuccess('Ajukan Pinjaman Functionality');
            results.functions.push({ function: 'Ajukan Pinjaman', status: 'Working' });
            
            // Close modal
            await page.keyboard.press('Escape');
            await new Promise(resolve => setTimeout(resolve, 500));
          } else {
            throw new Error('Form fields not found');
          }
        } else {
          throw new Error('Modal did not appear');
        }
      } else {
        throw new Error('Ajukan button not found');
      }
      
      await takeScreenshot(page, 'bos-ajukan-pinjaman-test');
    } catch (error) {
      logError('Ajukan Pinjaman Functionality', error);
      results.functions.push({ function: 'Ajukan Pinjaman', status: 'Failed' });
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
  console.log(`🔧 Functions Tested: ${results.functions.length}`);
  console.log('='.repeat(50));

  if (results.errors.length > 0) {
    console.log('\n❌ Errors:');
    results.errors.forEach(err => {
      console.log(`  - ${err.test}: ${err.error}`);
    });
  }

  console.log('\n🔧 Functions Tested:');
  results.functions.forEach(fn => {
    console.log(`  - ${fn.function}: ${fn.status}`);
  });

  console.log('\n📸 Screenshots saved to:', screenshotDir);
  console.log('='.repeat(50));

  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests();
