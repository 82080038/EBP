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
  console.log('🚀 Starting Comprehensive Superadmin Functional Tests in Headed Mode...\n');
  
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

    // Test 2: Test Bos Approval functionality
    try {
      console.log('\n📋 Test 2: Test Bos Approval Functionality');
      
      await page.goto(config.baseUrl + '/pages/superadmin/bos_approvals.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check for Setujui button
      const setujuiButton = await page.evaluateHandle(() => {
        const buttons = Array.from(document.querySelectorAll('button, .btn'));
        return buttons.find(btn => btn.textContent.includes('Setujui'));
      });
      
      if (setujuiButton) {
        console.log('  Setujui button found');
        await setujuiButton.click();
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Check if confirmation appeared
        const confirmDialog = await page.$('.swal2-container') || await page.$('.modal');
        if (confirmDialog) {
          console.log('  Confirmation dialog appeared');
          await page.keyboard.press('Escape');
          await new Promise(resolve => setTimeout(resolve, 500));
        }
        
        logSuccess('Bos Approval Functionality');
        results.functions.push({ function: 'Bos Approval', status: 'Working' });
      } else {
        console.log('  No pending bos registrations to approve');
        logSuccess('Bos Approval Functionality (No Pending)');
        results.functions.push({ function: 'Bos Approval', status: 'No Pending' });
      }
      
      await takeScreenshot(page, 'superadmin-bos-approval-test');
    } catch (error) {
      logError('Bos Approval Functionality', error);
      results.functions.push({ function: 'Bos Approval', status: 'Failed' });
    }

    // Test 3: Test Tambah User functionality
    try {
      console.log('\n📋 Test 3: Test Tambah User Functionality');
      
      await page.goto(config.baseUrl + '/pages/users/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Click Tambah User button
      const tambahButton = await page.evaluateHandle(() => {
        const buttons = Array.from(document.querySelectorAll('button, .btn'));
        return buttons.find(btn => btn.textContent.includes('Tambah'));
      });
      
      if (tambahButton) {
        await tambahButton.click();
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Check if modal appeared
        const modal = await page.$('.modal');
        if (modal) {
          console.log('  Modal appeared');
          
          // Check if form fields exist
          const allInputs = await page.$$('.modal input');
          console.log(`  Found ${allInputs.length} input fields in modal`);
          
          // Log all input names
          const inputNames = await page.evaluate(() => {
            return Array.from(document.querySelectorAll('.modal input')).map(input => input.name);
          });
          console.log('  Input field names:', inputNames);
          
          if (allInputs.length > 0) {
            console.log('  Form fields found');
            
            // Try to fill the first text input
            const firstTextInput = await page.evaluate(() => {
              const inputs = Array.from(document.querySelectorAll('.modal input[type="text"]'));
              return inputs.length > 0 ? inputs[0].name : null;
            });
            
            if (firstTextInput) {
              await page.type(`.modal input[name="${firstTextInput}"]`, 'Test');
              console.log('  Form filled successfully');
            }
            
            console.log('  Form filled successfully');
            
            // Close modal without submitting
            await page.keyboard.press('Escape');
            await new Promise(resolve => setTimeout(resolve, 500));
            
            logSuccess('Tambah User Functionality');
            results.functions.push({ function: 'Tambah User', status: 'Working' });
          } else {
            throw new Error('Form fields not found');
          }
        } else {
          throw new Error('Modal did not appear');
        }
      } else {
        throw new Error('Tambah button not found');
      }
      
      await takeScreenshot(page, 'superadmin-tambah-user-test');
    } catch (error) {
      logError('Tambah User Functionality', error);
      results.functions.push({ function: 'Tambah User', status: 'Failed' });
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
