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
  console.log('🚀 Starting Bos Address Combo Tests in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    // Enable console logging
    page.on('console', msg => {
      console.log('Browser Console:', msg.text());
    });

    // Test 1: Load Registration Page
    try {
      console.log('\n📋 Test 1: Load Registration Page');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      logSuccess('Load Registration Page');
      await takeScreenshot(page, 'bos-address-page-load');
    } catch (error) {
      logError('Load Registration Page', error);
    }

    // Test 2: Check Province Dropdown
    try {
      console.log('\n📋 Test 2: Check Province Dropdown');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      const provinceSelect = await page.$('select[name="province_id"]');
      if (!provinceSelect) {
        throw new Error('Province dropdown not found');
      }
      
      // Get options
      const options = await page.evaluate(() => {
        const select = document.querySelector('select[name="province_id"]');
        return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
      });
      
      console.log('Province options:', options);
      
      if (options.length <= 1) {
        throw new Error('Province dropdown has no options');
      }
      
      logSuccess('Check Province Dropdown');
      await takeScreenshot(page, 'bos-address-province-dropdown');
    } catch (error) {
      logError('Check Province Dropdown', error);
    }

    // Test 3: Select Province
    try {
      console.log('\n📋 Test 3: Select Province');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Select province
      await page.select('select[name="province_id"]', '3');
      await new Promise(resolve => setTimeout(resolve, 3000)); // Wait for JavaScript to load regencies
      
      // Check if regency dropdown is populated
      const regencySelect = await page.$('select[name="regency_id"]');
      if (!regencySelect) {
        throw new Error('Regency dropdown not found');
      }
      
      const regencyOptions = await page.evaluate(() => {
        const select = document.querySelector('select[name="regency_id"]');
        return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
      });
      
      console.log('Regency options after province selection:', regencyOptions);
      
      if (regencyOptions.length <= 1) {
        throw new Error('Regency dropdown not populated after province selection');
      }
      
      logSuccess('Select Province');
      await takeScreenshot(page, 'bos-address-after-province-select');
    } catch (error) {
      logError('Select Province', error);
    }

    // Test 4: Select Regency
    try {
      console.log('\n📋 Test 4: Select Regency');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Select province first
      await page.select('select[name="province_id"]', '3');
      await new Promise(resolve => setTimeout(resolve, 3000));
      
      // Select regency
      const regencyOptions = await page.evaluate(() => {
        const select = document.querySelector('select[name="regency_id"]');
        return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
      });
      
      if (regencyOptions.length > 1) {
        await page.select('select[name="regency_id"]', regencyOptions[1].value);
        await new Promise(resolve => setTimeout(resolve, 3000)); // Wait for JavaScript to load districts
      }
      
      // Check if district dropdown is populated
      const districtSelect = await page.$('select[name="district_id"]');
      if (!districtSelect) {
        throw new Error('District dropdown not found');
      }
      
      const districtOptions = await page.evaluate(() => {
        const select = document.querySelector('select[name="district_id"]');
        return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
      });
      
      console.log('District options after regency selection:', districtOptions);
      
      if (districtOptions.length <= 1) {
        throw new Error('District dropdown not populated after regency selection');
      }
      
      logSuccess('Select Regency');
      await takeScreenshot(page, 'bos-address-after-regency-select');
    } catch (error) {
      logError('Select Regency', error);
    }

    // Test 5: Select District
    try {
      console.log('\n📋 Test 5: Select District');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Select province
      await page.select('select[name="province_id"]', '3');
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
          await new Promise(resolve => setTimeout(resolve, 3000)); // Wait for JavaScript to load villages
        }
      }
      
      // Check if village dropdown is populated
      const villageSelect = await page.$('select[name="village_id"]');
      if (!villageSelect) {
        throw new Error('Village dropdown not found');
      }
      
      const villageOptions = await page.evaluate(() => {
        const select = document.querySelector('select[name="village_id"]');
        return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
      });
      
      console.log('Village options after district selection:', villageOptions);
      
      if (villageOptions.length <= 1) {
        throw new Error('Village dropdown not populated after district selection');
      }
      
      logSuccess('Select District');
      await takeScreenshot(page, 'bos-address-after-district-select');
    } catch (error) {
      logError('Select District', error);
    }

    // Test 6: Full Address Selection Flow
    try {
      console.log('\n📋 Test 6: Full Address Selection Flow');
      await page.goto(config.baseUrl + '/pages/bos/register.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Fill basic fields
      await page.type('input[name="username"]', 'testbos3');
      await page.type('input[name="password"]', 'password123');
      await page.type('input[name="confirm_password"]', 'password123');
      await page.type('input[name="nama"]', 'Test Bos 3');
      await page.type('input[name="nama_perusahaan"]', 'Test Perusahaan 3');
      
      // Select province
      await page.select('select[name="province_id"]', '3');
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
      
      await page.type('textarea[name="alamat"]', 'Jalan Test No. 123');
      
      logSuccess('Full Address Selection Flow');
      await takeScreenshot(page, 'bos-address-full-flow');
    } catch (error) {
      logError('Full Address Selection Flow', error);
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
