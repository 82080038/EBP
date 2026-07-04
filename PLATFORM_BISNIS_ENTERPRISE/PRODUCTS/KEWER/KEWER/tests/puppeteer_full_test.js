const puppeteer = require('puppeteer');
const fs = require('fs');

// Configuration
const BASE_URL = 'http://localhost/kewer';
const ROLES = [
    { code: 'bos', username: 'patri', password: 'Kewer2024!' },
    { code: 'manager_pusat', username: 'mgr_pusat', password: 'Kewer2024!' },
    { code: 'manager_cabang', username: 'mgr_balige', password: 'Kewer2024!' },
    { code: 'admin_pusat', username: 'adm_pusat', password: 'Kewer2024!' },
    { code: 'admin_cabang', username: 'adm_balige', password: 'Kewer2024!' },
    { code: 'petugas_pusat', username: 'ptr_pusat', password: 'Kewer2024!' },
    { code: 'petugas_cabang', username: 'ptr_balige', password: 'Kewer2024!' },
    { code: 'karyawan', username: 'krw_pusat', password: 'Kewer2024!' }
];

// Bos role accessible menus based on bos.json
const BOS_MENUS = [
    { name: 'Dashboard', url: '/dashboard.php' },
    { name: 'Nasabah', url: '/pages/nasabah/index.php' },
    { name: 'Pinjaman', url: '/pages/pinjaman/index.php' },
    { name: 'Angsuran', url: '/pages/angsuran/index.php' },
    { name: 'Users', url: '/pages/users/index.php' },
    { name: 'Laporan', url: '/pages/laporan/index.php' },
    { name: 'Pengeluaran', url: '/pages/pengeluaran/index.php' },
    { name: 'Kas Bon', url: '/pages/kas_bon/index.php' },
    { name: 'Setting Bunga', url: '/pages/setting_bunga/index.php' },
    { name: 'Family Risk', url: '/pages/family_risk/index.php' },
    { name: 'Petugas', url: '/pages/petugas/index.php' },
    { name: 'Audit', url: '/pages/audit/index.php' },
    { name: 'Kas Petugas', url: '/pages/kas_petugas/index.php' },
    { name: 'Auto Confirm', url: '/pages/auto_confirm/index.php' }
];

// Results storage
const results = {
    summary: [],
    errors: [],
    consoleErrors: []
};

async function login(page, username, password) {
    try {
        await page.goto(`${BASE_URL}/login.php`, { waitUntil: 'networkidle2' });
        
        // Check if already logged in
        const currentUrl = page.url();
        if (currentUrl.includes('dashboard.php')) {
            console.log(`  ✓ Already logged in as ${username}`);
            return true;
        }

        // Fill login form
        await page.type('input[name="username"]', username);
        await page.type('input[name="password"]', password);
        await page.click('button[type="submit"]');
        
        await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 });
        
        // Verify login success
        const loggedIn = page.url().includes('dashboard.php');
        if (loggedIn) {
            console.log(`  ✓ Login successful for ${username}`);
            return true;
        } else {
            console.log(`  ✗ Login failed for ${username}`);
            return false;
        }
    } catch (error) {
        console.log(`  ✗ Login error for ${username}: ${error.message}`);
        return false;
    }
}

async function checkConsoleErrors(page, pageName) {
    const errors = [];
    page.on('console', msg => {
        if (msg.type() === 'error') {
            errors.push({
                page: pageName,
                text: msg.text(),
                location: msg.location()
            });
            console.log(`    Console Error: ${msg.text()}`);
        }
    });
    return errors;
}

async function testPage(page, menu) {
    const pageErrors = [];
    console.log(`  Testing: ${menu.name} (${menu.url})`);
    
    try {
        await page.goto(`${BASE_URL}${menu.url}`, { waitUntil: 'networkidle2', timeout: 15000 });
        
        // Wait for page to load
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Check for page errors
        const pageContent = await page.content();
        const hasError = pageContent.includes('Fatal error') || 
                        pageContent.includes('Warning:') ||
                        pageContent.includes('Notice:');
        
        if (hasError) {
            console.log(`    ✗ PHP errors detected on ${menu.name}`);
            pageErrors.push(`PHP errors detected on ${menu.name}`);
        } else {
            console.log(`    ✓ ${menu.name} loaded successfully`);
        }
        
        // Check for DataTables errors
        try {
            await page.waitForSelector('table', { timeout: 5000 });
            console.log(`    ✓ DataTable found on ${menu.name}`);
        } catch (e) {
            console.log(`    ⚠ No DataTable on ${menu.name} (might be normal)`);
        }
        
        // Check for Select2 errors
        try {
            await page.waitForSelector('.select2-hidden-accessible', { timeout: 3000 });
            console.log(`    ✓ Select2 found on ${menu.name}`);
        } catch (e) {
            console.log(`    ⚠ No Select2 on ${menu.name} (might be normal)`);
        }
        
        // Take screenshot for visual verification
        await page.screenshot({ path: `tests/screenshots/${menu.name.replace(/\s+/g, '_')}.png` });
        
    } catch (error) {
        console.log(`    ✗ Error loading ${menu.name}: ${error.message}`);
        pageErrors.push(`Error loading ${menu.name}: ${error.message}`);
    }
    
    return pageErrors;
}

async function testRole(role) {
    console.log(`\n=== Testing Role: ${role.code} (${role.username}) ===`);
    
    const browser = await puppeteer.launch({
        headless: false, // Headed mode as requested
        args: ['--start-maximized', '--no-sandbox', '--disable-setuid-sandbox'],
        defaultViewport: null
    });
    
    const page = await browser.newPage();
    
    // Setup console error listener
    page.on('console', msg => {
        if (msg.type() === 'error') {
            results.consoleErrors.push({
                role: role.code,
                text: msg.text(),
                location: msg.location()
            });
        }
    });
    
    try {
        // Login
        const loginSuccess = await login(page, role.username, role.password);
        if (!loginSuccess) {
            results.summary.push({ role: role.code, status: 'FAILED', reason: 'Login failed' });
            await browser.close();
            return;
        }
        
        // Test menus based on role
        let menus = [];
        if (role.code === 'bos') {
            menus = BOS_MENUS;
        } else {
            // For other roles, test common pages
            menus = [
                { name: 'Dashboard', url: '/dashboard.php' },
                { name: 'Nasabah', url: '/pages/nasabah/index.php' },
                { name: 'Pinjaman', url: '/pages/pinjaman/index.php' }
            ];
        }
        
        let pageErrors = [];
        for (const menu of menus) {
            const errors = await testPage(page, menu);
            pageErrors = pageErrors.concat(errors);
        }
        
        if (pageErrors.length === 0) {
            results.summary.push({ role: role.code, status: 'PASSED', pages: menus.length });
            console.log(`\n✓ ${role.code} role testing completed successfully`);
        } else {
            results.summary.push({ role: role.code, status: 'PARTIAL', errors: pageErrors });
            console.log(`\n⚠ ${role.code} role testing completed with ${pageErrors.length} errors`);
        }
        
    } catch (error) {
        console.log(`✗ Error testing ${role.code}: ${error.message}`);
        results.summary.push({ role: role.code, status: 'FAILED', reason: error.message });
        results.errors.push({ role: role.code, error: error.message });
    } finally {
        await browser.close();
    }
}

async function runAllTests() {
    console.log('=== Starting Puppeteer Full Application Test ===');
    console.log('Mode: Headed (visible browser)');
    console.log('Base URL:', BASE_URL);
    console.log('Roles to test:', ROLES.map(r => r.code).join(', '));
    
    // Create screenshots directory
    if (!fs.existsSync('tests/screenshots')) {
        fs.mkdirSync('tests/screenshots', { recursive: true });
    }
    
    for (const role of ROLES) {
        await testRole(role);
    }
    
    // Print summary
    console.log('\n=== TEST SUMMARY ===');
    results.summary.forEach(result => {
        console.log(`${result.role}: ${result.status}`);
        if (result.status === 'PARTIAL') {
            console.log(`  Errors: ${result.errors.length}`);
        }
    });
    
    // Print console errors
    if (results.consoleErrors.length > 0) {
        console.log('\n=== CONSOLE ERRORS ===');
        results.consoleErrors.forEach(err => {
            console.log(`${err.role}: ${err.text}`);
        });
    }
    
    // Save results to file
    fs.writeFileSync('tests/puppeteer_test_results.json', JSON.stringify(results, null, 2));
    console.log('\nResults saved to tests/puppeteer_test_results.json');
}

// Run tests
runAllTests().catch(console.error);
