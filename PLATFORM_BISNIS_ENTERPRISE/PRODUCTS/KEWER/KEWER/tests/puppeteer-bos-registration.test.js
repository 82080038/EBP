const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: []
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
  console.log('🚀 Starting Bos Registration Tests in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    // Test 1: Bos Registration Page Display
    try {
      console.log('\n📋 Test 1: Bos Registration Page Display');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      await page.waitForSelector('h4', { timeout: 5000 });
      
      const title = await page.title();
      console.log('Page title:', title);
      
      // Check for form elements
      const usernameInput = await page.$('input[name="username"]');
      const passwordInput = await page.$('input[name="password"]');
      const confirmInput = await page.$('input[name="confirm_password"]');
      const namaInput = await page.$('input[name="nama"]');
      const namaPerusahaanInput = await page.$('input[name="nama_perusahaan"]');
      const submitButton = await page.$('button[type="submit"]');
      
      if (!usernameInput || !passwordInput || !confirmInput || !namaInput || !namaPerusahaanInput || !submitButton) {
        throw new Error('Registration form elements not found');
      }
      
      logSuccess('Bos Registration Page Display');
      await takeScreenshot(page, 'bos-registration-page');
    } catch (error) {
      logError('Bos Registration Page Display', error);
    }

    // Test 2: Fill Registration Form
    try {
      console.log('\n📋 Test 2: Fill Registration Form');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      
      // Fill form fields
      await page.type('input[name="username"]', 'testbos');
      await page.type('input[name="password"]', 'password123');
      await page.type('input[name="confirm_password"]', 'password123');
      await page.type('input[name="nama"]', 'Test Bos');
      await page.type('input[name="email"]', 'testbos@example.com');
      await page.type('input[name="telp"]', '08123456789');
      await page.type('input[name="nama_perusahaan"]', 'Test Perusahaan');
      
      // Select province
      await page.select('select[name="province_id"]', '3'); // SUMATERA UTARA
      await new Promise(resolve => setTimeout(resolve, 3000));
      
      // Select regency
      const regencyOptions = await page.evaluate(() => {
        const select = document.querySelector('select[name="regency_id"]');
        return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
      });
      
      if (regencyOptions.length > 1) {
        await page.select('select[name="regency_id"]', regencyOptions[1].value);
        await new Promise(resolve => setTimeout(resolve, 3000));
        
        // Select district
        const districtOptions = await page.evaluate(() => {
          const select = document.querySelector('select[name="district_id"]');
          return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
        });
        
        if (districtOptions.length > 1) {
          await page.select('select[name="district_id"]', districtOptions[1].value);
          await new Promise(resolve => setTimeout(resolve, 3000));
          
          // Select village
          const villageOptions = await page.evaluate(() => {
            const select = document.querySelector('select[name="village_id"]');
            return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
          });
          
          if (villageOptions.length > 1) {
            await page.select('select[name="village_id"]', villageOptions[1].value);
            await new Promise(resolve => setTimeout(resolve, 1000));
          }
        }
      }
      
      await page.type('textarea[name="alamat"]', 'Jalan Test No. 123, RT/RW 001/002');
      
      logSuccess('Fill Registration Form');
      await takeScreenshot(page, 'bos-registration-form-filled');
    } catch (error) {
      logError('Fill Registration Form', error);
    }

    // Test 3: Submit Registration Form
    try {
      console.log('\n📋 Test 3: Submit Registration Form');
      
      // Click submit button
      await page.click('button[type="submit"]');
      
      // Wait for response
      await new Promise(resolve => setTimeout(resolve, 3000));
      
      const currentUrl = page.url();
      console.log('Current URL after submit:', currentUrl);
      
      // Check for success message or redirect
      const successAlert = await page.$('.alert-success');
      const errorAlert = await page.$('.alert-danger');
      
      if (successAlert) {
        const alertText = await page.$eval('.alert-success', el => el.textContent);
        console.log('Success message:', alertText);
      }
      
      if (errorAlert) {
        const alertText = await page.$eval('.alert-danger', el => el.textContent);
        console.log('Error message:', alertText);
      }
      
      await takeScreenshot(page, 'bos-registration-after-submit');
      logSuccess('Submit Registration Form');
    } catch (error) {
      logError('Submit Registration Form', error);
    }

    // Test 4: Validation - Empty Fields
    try {
      console.log('\n📋 Test 4: Validation - Empty Fields');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      
      // Submit empty form
      await page.click('button[type="submit"]');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      const errorAlert = await page.$('.alert-danger');
      if (errorAlert) {
        const alertText = await page.$eval('.alert-danger', el => el.textContent);
        console.log('Validation error:', alertText);
        logSuccess('Validation - Empty Fields');
      } else {
        throw new Error('No validation error displayed for empty fields');
      }
      
      await takeScreenshot(page, 'bos-registration-validation-error');
    } catch (error) {
      logError('Validation - Empty Fields', error);
    }

    // Test 5: Validation - Password Mismatch
    try {
      console.log('\n📋 Test 5: Validation - Password Mismatch');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      
      await page.type('input[name="username"]', 'testbos2');
      await page.type('input[name="password"]', 'password123');
      await page.type('input[name="confirm_password"]', 'differentpassword');
      await page.type('input[name="nama"]', 'Test Bos 2');
      await page.type('input[name="nama_perusahaan"]', 'Test Perusahaan 2');
      
      await page.click('button[type="submit"]');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      const errorAlert = await page.$('.alert-danger');
      if (errorAlert) {
        const alertText = await page.$eval('.alert-danger', el => el.textContent);
        console.log('Password mismatch error:', alertText);
        logSuccess('Validation - Password Mismatch');
      } else {
        throw new Error('No validation error displayed for password mismatch');
      }
      
      await takeScreenshot(page, 'bos-registration-password-mismatch');
    } catch (error) {
      logError('Validation - Password Mismatch', error);
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
  console.log('='.repeat(50));

  if (results.errors.length > 0) {
    console.log('\n❌ Errors:');
    results.errors.forEach(err => {
      console.log(`  - ${err.test}: ${err.error}`);
    });
  }

  console.log('\n📸 Screenshots saved to:', screenshotDir);
  console.log('='.repeat(50));

  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests();
