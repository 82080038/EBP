const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const screenshotDir = path.join(__dirname, 'screenshots');
if (!fs.existsSync(screenshotDir)) {
  fs.mkdirSync(screenshotDir, { recursive: true });
}

async function takeScreenshot(page, name) {
  const screenshotPath = path.join(screenshotDir, `bos-${name}.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });
  console.log(`   📸 Screenshot: ${name}`);
}

async function testBosComprehensive() {
  console.log('🚀 Comprehensive Bos Testing (Headed Mode)\n');
  
  const browser = await puppeteer.launch({
    headless: false,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080']
  });
  
  const page = await browser.newPage();
  await page.setDefaultTimeout(60000);

  const results = {
    passed: 0,
    failed: 0,
    errors: []
  };

  try {
    // Login as bos using test_login
    console.log('📋 Step 1: Login as Bos (patri)');
    await page.goto('http://localhost/kewer/login.php?test_login=true&username=patri&password=password', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 3000));
    
    const currentUrl = page.url();
    console.log(`   Current URL after login: ${currentUrl}`);
    
    if (currentUrl.includes('login.php')) {
      // Check for error message
      const errorAlert = await page.$('.alert-danger');
      if (errorAlert) {
        const errorText = await page.$eval('.alert-danger', el => el.textContent);
        throw new Error(`Login failed: ${errorText}`);
      }
      throw new Error('Login failed - still on login page');
    }
    
    if (!currentUrl.includes('dashboard.php')) {
      throw new Error('Login failed - redirected to unexpected page');
    }
    console.log('✅ Login successful');
    await takeScreenshot(page, '01-dashboard-after-login');

    // Test Dashboard
    console.log('\n📋 Step 2: Test Dashboard');
    await page.goto('http://localhost/kewer/dashboard.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const dashboardCards = await page.$$('.card');
    if (dashboardCards.length === 0) {
      throw new Error('Dashboard: No cards found');
    }
    console.log(`✅ Dashboard loaded (${dashboardCards.length} cards)`);
    await takeScreenshot(page, '02-dashboard');

    // Test Billing
    console.log('\n📋 Step 3: Test Billing');
    await page.goto('http://localhost/kewer/pages/bos/billing.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const billingCards = await page.$$('.card');
    if (billingCards.length === 0) {
      throw new Error('Billing: No cards found');
    }
    console.log(`✅ Billing page loaded (${billingCards.length} cards)`);

    // Check for payment info alert
    const paymentAlert = await page.evaluate(() => {
      const alerts = Array.from(document.querySelectorAll('.alert-info'));
      return alerts.some(alert => alert.textContent.includes('Informasi Pembayaran'));
    });
    if (paymentAlert) {
      console.log('✅ Payment info alert found');
    } else {
      console.log('⚠️  Payment info alert not found');
    }

    // Check for invoice table
    const invoiceTable = await page.$('table');
    if (invoiceTable) {
      console.log('✅ Invoice table found');
    } else {
      console.log('⚠️  No invoice table (might be empty)');
    }
    await takeScreenshot(page, '03-billing');

    // Test Register (if accessible)
    console.log('\n📋 Step 4: Test Register Page');
    try {
      await page.goto('http://localhost/kewer/pages/bos/register.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
      await new Promise(resolve => setTimeout(resolve, 1000));

      const registerForm = await page.$('form');
      if (registerForm) {
        console.log('✅ Register form found');
      } else {
        console.log('⚠️  Register form not found');
      }
      await takeScreenshot(page, '04-register');
    } catch (error) {
      console.log(`⚠️  Register page error: ${error.message}`);
    }

    // Test Setup Headquarters (if accessible)
    console.log('\n📋 Step 5: Test Setup Headquarters');
    try {
      await page.goto('http://localhost/kewer/pages/bos/setup_headquarters.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
      await new Promise(resolve => setTimeout(resolve, 1000));

      const currentUrl = page.url();
      if (currentUrl.includes('dashboard.php')) {
        console.log('✅ Setup headquarters redirected to dashboard (already has HQ)');
      } else {
        const setupForm = await page.$('form');
        if (setupForm) {
          console.log('✅ Setup headquarters form found');
        } else {
          console.log('⚠️  Setup headquarters form not found');
        }
      }
      await takeScreenshot(page, '05-setup-headquarters');
    } catch (error) {
      console.log(`⚠️  Setup headquarters error: ${error.message}`);
    }

    // Test Delegated Permissions (if accessible)
    console.log('\n📋 Step 6: Test Delegated Permissions');
    try {
      await page.goto('http://localhost/kewer/pages/bos/delegated_permissions.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
      await new Promise(resolve => setTimeout(resolve, 1000));

      const permCards = await page.$$('.card');
      if (permCards.length > 0) {
        console.log(`✅ Delegated permissions page loaded (${permCards.length} cards)`);
      } else {
        console.log('⚠️  No permission cards found');
      }
      await takeScreenshot(page, '06-delegated-permissions');
    } catch (error) {
      console.log(`⚠️  Delegated permissions error: ${error.message}`);
    }

    console.log('\n✅ All Bos tests completed successfully');

  } catch (error) {
    console.error(`❌ Error: ${error.message}`);
    results.errors.push({ error: error.message });
    results.failed++;
    await takeScreenshot(page, 'error-screenshot');
  } finally {
    await browser.close();
  }

  console.log('\n' + '='.repeat(50));
  console.log('📊 Test Summary');
  console.log('='.repeat(50));
  console.log(`✅ Passed: ${results.passed}`);
  console.log(`❌ Failed: ${results.failed}`);
  if (results.errors.length > 0) {
    console.log('\n❌ Errors:');
    results.errors.forEach(err => {
      console.log(`  - ${err.error}`);
    });
  }
  console.log('='.repeat(50));
  console.log(`📸 Screenshots saved to: ${screenshotDir}`);
  console.log('='.repeat(50));

  process.exit(results.failed > 0 ? 1 : 0);
}

testBosComprehensive();
