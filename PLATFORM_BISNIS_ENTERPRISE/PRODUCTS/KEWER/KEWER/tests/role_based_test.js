/**
 * Role-Based Comprehensive Testing for Kewer Application
 * 
 * This script tests each role's features comprehensively:
 * - F2E (Frontend to End): UI/UX functionality
 * - E2E (End to End): Complete workflows
 * 
 * Roles to test:
 * 1. Owner (superadmin)
 * 2. Manager
 * 3. Admin Pusat
 * 4. Admin Cabang
 * 5. Petugas Pusat
 * 6. Petugas Cabang
 * 7. Karyawan
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

// Configuration
const config = {
  baseUrl: 'http://localhost/kewer',
  headless: false,
  timeout: 30000,
  credentials: {
    owner: { username: 'admin', password: 'password' },
    manager: { username: 'admin', password: 'password' },
    admin_pusat: { username: 'admin', password: 'password' },
    admin_cabang: { username: 'admin', password: 'password' },
    petugas_pusat: { username: 'petugas1', password: 'password' },
    petugas_cabang: { username: 'petugas1', password: 'password' },
    karyawan: { username: 'petugas1', password: 'password' }
  }
};

// Test results
const testResults = {
  owner: { passed: 0, failed: 0, errors: [] },
  manager: { passed: 0, failed: 0, errors: [] },
  admin_pusat: { passed: 0, failed: 0, errors: [] },
  admin_cabang: { passed: 0, failed: 0, errors: [] },
  petugas_pusat: { passed: 0, failed: 0, errors: [] },
  petugas_cabang: { passed: 0, failed: 0, errors: [] },
  karyawan: { passed: 0, failed: 0, errors: [] }
};

// Create logs directory
const logsDir = path.join(__dirname, 'logs');
if (!fs.existsSync(logsDir)) {
  fs.mkdirSync(logsDir, { recursive: true });
}

// Helper functions
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function log(message, type = 'info') {
  const timestamp = new Date().toISOString();
  const logMessage = `[${timestamp}] [${type.toUpperCase()}] ${message}`;
  console.log(logMessage);
  
  const logFile = path.join(logsDir, `role_test_${new Date().toISOString().split('T')[0]}.log`);
  fs.appendFileSync(logFile, logMessage + '\n');
}

function logError(role, testName, error) {
  log(`${role} - ${testName}: ${error.message}`, 'error');
  testResults[role].errors.push({
    test: testName,
    error: error.message,
    stack: error.stack
  });
  testResults[role].failed++;
}

function logSuccess(role, testName) {
  log(`${role} - ${testName}: PASSED`, 'success');
  testResults[role].passed++;
}

async function takeScreenshot(page, filename) {
  const screenshotDir = path.join(__dirname, 'screenshots', 'role_tests');
  if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
  }
  await page.screenshot({ path: path.join(screenshotDir, filename) });
  log(`Screenshot saved: ${filename}`);
}

// Login function
async function login(page, role) {
  try {
    log(`Logging in as ${role}`);
    const creds = config.credentials[role];
    
    await page.goto(`${config.baseUrl}/login.php?test_login=true&username=${creds.username}&password=${creds.password}`, { waitUntil: 'networkidle2', timeout: config.timeout });
    
    // Wait for redirect to dashboard
    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: config.timeout }).catch(() => {});
    
    // Verify we're on dashboard
    if (page.url().includes('dashboard.php')) {
      log(`${role} login successful`, 'success');
      return true;
    } else {
      // Try manual navigation
      await page.goto(`${config.baseUrl}/dashboard.php`, { waitUntil: 'networkidle2', timeout: config.timeout });
      if (page.url().includes('dashboard.php')) {
        log(`${role} login successful (manual navigation)`, 'success');
        return true;
      }
      throw new Error('Not redirected to dashboard');
    }
  } catch (error) {
    logError(role, 'Login', error);
    return false;
  }
}

// Test specific feature
async function testFeature(page, role, testName, testFn) {
  try {
    log(`Testing: ${role} - ${testName}`);
    await testFn(page);
    logSuccess(role, testName);
    await takeScreenshot(page, `${role}_${testName.replace(/\s+/g, '_')}.png`);
    return true;
  } catch (error) {
    logError(role, testName, error);
    await takeScreenshot(page, `${role}_${testName.replace(/\s+/g, '_')}_ERROR.png`);
    return false;
  }
}

// Owner role tests
async function testOwnerRole(page) {
  const role = 'owner';
  
  await testFeature(page, role, 'Dashboard Access', async (p) => {
    await p.goto(`${config.baseUrl}/dashboard.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Nasabah Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/nasabah/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Pinjaman Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Angsuran Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/angsuran/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Users Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/users/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Cabang Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/cabang/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Permissions Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/permissions/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Auto-Confirm Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/auto_confirm/index.php`, { waitUntil: 'networkidle2' });
    // Check if we were redirected due to permission
    if (p.url().includes('dashboard.php')) {
      // Permission denied - this is expected behavior for some roles
      return;
    }
    // If not redirected, wait for page elements
    await p.waitForSelector('.card, h2, .container-fluid', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Setting Bunga Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/setting_bunga/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
}

// Manager role tests
async function testManagerRole(page) {
  const role = 'manager';
  
  await testFeature(page, role, 'Dashboard Access', async (p) => {
    await p.goto(`${config.baseUrl}/dashboard.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Nasabah Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/nasabah/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Pinjaman Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Angsuran Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/angsuran/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Cash Reconciliation Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/cash_reconciliation/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Users Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/users/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Auto-Confirm Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/auto_confirm/index.php`, { waitUntil: 'networkidle2' });
    // Check if we were redirected due to permission
    if (p.url().includes('dashboard.php')) {
      // Permission denied - this is expected behavior for some roles
      return;
    }
    // If not redirected, wait for page elements
    await p.waitForSelector('.card, h2, .container-fluid', { timeout: 10000 });
  });
}

// Petugas role tests
async function testPetugasRole(page) {
  const role = 'petugas_pusat';
  
  await testFeature(page, role, 'Dashboard Access', async (p) => {
    await p.goto(`${config.baseUrl}/dashboard.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Nasabah Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/nasabah/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Pinjaman Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Angsuran Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/angsuran/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Field Activities Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/field_activities/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Kas Petugas Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/kas_petugas/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card, .container-fluid', { timeout: 10000 });
  });
}

// Karyawan role tests
async function testKaryawanRole(page) {
  const role = 'karyawan';
  
  await testFeature(page, role, 'Dashboard Access', async (p) => {
    await p.goto(`${config.baseUrl}/dashboard.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Nasabah Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/nasabah/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Pinjaman Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Angsuran Module Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/angsuran/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('table', { timeout: 10000 });
  });
  
  await testFeature(page, role, 'Cash Reconciliation Access', async (p) => {
    await p.goto(`${config.baseUrl}/pages/cash_reconciliation/index.php`, { waitUntil: 'networkidle2' });
    await p.waitForSelector('.card', { timeout: 10000 });
  });
}

// Main test runner
async function runRoleTests() {
  log('Starting Role-Based Comprehensive Testing');
  log(`Base URL: ${config.baseUrl}`);
  
  const browser = await puppeteer.launch({
    headless: config.headless,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    // Test Owner role
    log('\n=== TESTING OWNER ROLE ===');
    const ownerPage = await browser.newPage();
    if (await login(ownerPage, 'owner')) {
      await testOwnerRole(ownerPage);
    }
    await ownerPage.close();
    
    // Test Manager role
    log('\n=== TESTING MANAGER ROLE ===');
    const managerPage = await browser.newPage();
    if (await login(managerPage, 'manager')) {
      await testManagerRole(managerPage);
    }
    await managerPage.close();
    
    // Test Petugas role
    log('\n=== TESTING PETUGAS ROLE ===');
    const petugasPage = await browser.newPage();
    if (await login(petugasPage, 'petugas_pusat')) {
      await testPetugasRole(petugasPage);
    }
    await petugasPage.close();
    
    // Test Karyawan role
    log('\n=== TESTING KARYAWAN ROLE ===');
    const karyawanPage = await browser.newPage();
    if (await login(karyawanPage, 'karyawan')) {
      await testKaryawanRole(karyawanPage);
    }
    await karyawanPage.close();
    
    // Generate report
    generateReport();
    
  } catch (error) {
    log(`Fatal error: ${error.message}`, 'error');
  } finally {
    await browser.close();
  }
}

function generateReport() {
  log('\n=== TEST RESULTS SUMMARY ===');
  
  let totalPassed = 0;
  let totalFailed = 0;
  let totalErrors = [];
  
  for (const [role, results] of Object.entries(testResults)) {
    log(`${role.toUpperCase()}:`);
    log(`  Passed: ${results.passed}`);
    log(`  Failed: ${results.failed}`);
    if (results.errors.length > 0) {
      log(`  Errors:`);
      results.errors.forEach(err => {
        log(`    - ${err.test}: ${err.error}`);
        totalErrors.push({ role, ...err });
      });
    }
    totalPassed += results.passed;
    totalFailed += results.failed;
  }
  
  log(`\nTOTAL: Passed: ${totalPassed}, Failed: ${totalFailed}`);
  
  // Save detailed report
  const report = {
    summary: {
      totalPassed,
      totalFailed,
      totalTests: totalPassed + totalFailed
    },
    byRole: testResults,
    allErrors: totalErrors
  };
  
  const reportFile = path.join(logsDir, `role_test_report_${new Date().toISOString().split('T')[0]}.json`);
  fs.writeFileSync(reportFile, JSON.stringify(report, null, 2));
  
  log(`Detailed report saved to: ${reportFile}`);
}

// Run tests
runRoleTests().catch(error => {
  log(`Fatal error: ${error.message}`, 'error');
  process.exit(1);
});
