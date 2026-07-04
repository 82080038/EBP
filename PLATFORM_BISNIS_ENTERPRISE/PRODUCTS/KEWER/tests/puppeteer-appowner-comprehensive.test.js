const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const screenshotDir = path.join(__dirname, 'screenshots');
if (!fs.existsSync(screenshotDir)) {
  fs.mkdirSync(screenshotDir, { recursive: true });
}

async function takeScreenshot(page, name) {
  const screenshotPath = path.join(screenshotDir, `appowner-${name}.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });
  console.log(`   📸 Screenshot: ${name}`);
}

async function testAppOwnerComprehensive() {
  console.log('🚀 Comprehensive AppOwner Testing (Headed Mode)\n');
  
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
    // Login
    console.log('📋 Step 1: Login as AppOwner');
    await page.goto('http://localhost/kewer/login.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.waitForSelector('input[name="username"]', { timeout: 15000 });
    await page.type('input[name="username"]', 'appowner', { delay: 50 });
    await page.type('input[name="password"]', 'AppOwner2024!', { delay: 50 });
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 3000));
    
    const currentUrl = page.url();
    if (!currentUrl.includes('dashboard.php')) {
      throw new Error('Login failed - not redirected to dashboard');
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

    // Test Approvals
    console.log('\n📋 Step 3: Test Approvals');
    await page.goto('http://localhost/kewer/pages/app_owner/approvals.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const approvalTabs = await page.$$('.nav-tabs .nav-link');
    console.log(`   Found ${approvalTabs.length} tabs`);

    const approvalTable = await page.$('table');
    if (!approvalTable) {
      console.log('⚠️  No approval table (might be empty)');
    } else {
      console.log('✅ Approvals page loaded');
    }
    await takeScreenshot(page, '03-approvals');

    // Test Koperasi
    console.log('\n📋 Step 4: Test Koperasi');
    await page.goto('http://localhost/kewer/pages/app_owner/koperasi.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const koperasiTable = await page.$('table');
    if (!koperasiTable) {
      throw new Error('Koperasi: Table not found');
    }

    const koperasiRows = await page.$$('tbody tr');
    console.log(`✅ Koperasi page loaded (${koperasiRows.length} koperasi)`);

    // Check for billing button
    const billingBtn = await page.$('button[title="Assign Plan"]');
    if (billingBtn) {
      console.log('✅ Billing assignment button found');
    }
    await takeScreenshot(page, '04-koperasi');

    // Test Billing
    console.log('\n📋 Step 5: Test Billing');
    await page.goto('http://localhost/kewer/pages/app_owner/billing.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const billingPlans = await page.$$('.card');
    if (billingPlans.length === 0) {
      throw new Error('Billing: No plans found');
    }
    console.log(`✅ Billing page loaded (${billingPlans.length} cards)`);

    // Check for payment info
    const paymentAlert = await page.evaluate(() => {
      const alerts = Array.from(document.querySelectorAll('.alert-info'));
      return alerts.some(alert => alert.textContent.includes('Metode Pembayaran'));
    });
    if (paymentAlert) {
      console.log('✅ Payment info alert found');
    }
    await takeScreenshot(page, '05-billing');

    // Test Usage
    console.log('\n📋 Step 6: Test Usage');
    await page.goto('http://localhost/kewer/pages/app_owner/usage.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const usageCards = await page.$$('.card');
    if (usageCards.length === 0) {
      throw new Error('Usage: No cards found');
    }
    console.log(`✅ Usage page loaded (${usageCards.length} cards)`);
    await takeScreenshot(page, '06-usage');

    // Test AI Advisor
    console.log('\n📋 Step 7: Test AI Advisor');
    await page.goto('http://localhost/kewer/pages/app_owner/ai_advisor.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const aiCards = await page.$$('.card');
    if (aiCards.length === 0) {
      console.log('⚠️  AI Advisor: No advice cards (might be empty)');
    } else {
      console.log(`✅ AI Advisor page loaded (${aiCards.length} cards)`);
    }
    await takeScreenshot(page, '07-ai-advisor');

    // Test Settings
    console.log('\n📋 Step 8: Test Settings');
    await page.goto('http://localhost/kewer/pages/app_owner/settings.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const settingsCards = await page.$$('.card');
    if (settingsCards.length === 0) {
      throw new Error('Settings: No cards found');
    }
    console.log(`✅ Settings page loaded (${settingsCards.length} cards)`);

    // Check for payment methods section
    const paymentSection = await page.evaluate(() => {
      const cards = Array.from(document.querySelectorAll('.card'));
      return cards.some(card => card.textContent.includes('Rekening Bank Platform'));
    });
    if (paymentSection) {
      console.log('✅ Payment methods section found');
    }

    // Check for billing plans section
    const billingPlansSection = await page.evaluate(() => {
      const cards = Array.from(document.querySelectorAll('.card'));
      return cards.some(card => card.textContent.includes('Billing Plans'));
    });
    if (billingPlansSection) {
      console.log('✅ Billing plans section found');
    }
    await takeScreenshot(page, '08-settings');

    // Test Profile Update
    console.log('\n📋 Step 9: Test Profile Update');
    await page.goto('http://localhost/kewer/pages/app_owner/settings.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const namaInput = await page.$('input[name="nama"]');
    const submitBtn = await page.$('button[type="submit"]');
    if (namaInput && submitBtn) {
      console.log('✅ Profile form found (input and submit button)');
    } else {
      console.log('⚠️  Profile form not found');
    }
    await takeScreenshot(page, '09-settings-profile');

    // Test Billing Plans Toggle
    console.log('\n📋 Step 10: Test Billing Plans Toggle');
    await page.goto('http://localhost/kewer/pages/app_owner/settings.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await new Promise(resolve => setTimeout(resolve, 1000));

    const planToggle = await page.$('input[name="action"][value="manage_plan"]');
    if (planToggle) {
      console.log('✅ Billing plans toggle form found');
    } else {
      console.log('⚠️  Billing plans toggle not found');
    }
    await takeScreenshot(page, '10-settings-billing-plans');

    console.log('\n✅ All AppOwner tests completed successfully');

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

testAppOwnerComprehensive();
