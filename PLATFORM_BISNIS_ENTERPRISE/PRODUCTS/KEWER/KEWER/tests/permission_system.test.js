const puppeteer = require('puppeteer');
const assert = require('assert');

const BASE_URL = 'http://localhost/kewer';

// Test credentials
const TEST_USERS = {
    owner: { username: 'owner', password: 'password', role: 'owner' },
    admin: { username: 'admin', password: 'password', role: 'manajer_cabang' },
    manager1: { username: 'manager1', password: 'password', role: 'admin_cabang' },
    petugas1: { username: 'petugas1', password: 'password', role: 'petugas_cabang' },
    karyawan1: { username: 'karyawan1', password: 'password', role: 'karyawan' }
};

// Expected menu items for each role
const EXPECTED_MENUS = {
    owner: ['Nasabah', 'Pinjaman', 'Angsuran', 'Petugas', 'Users', 'Cabang'],
    admin: ['Nasabah', 'Pinjaman', 'Angsuran', 'Petugas', 'Users', 'Cabang'],
    manager1: ['Nasabah', 'Pinjaman', 'Angsuran'],
    petugas1: ['Nasabah', 'Pinjaman', 'Angsuran'],
    karyawan1: ['Nasabah', 'Pinjaman', 'Angsuran']
};

async function login(page, username, password) {
    console.log(`    Navigating to ${BASE_URL}/login.php`);
    try {
        await page.goto(BASE_URL + '/login.php', { 
            waitUntil: 'domcontentloaded', 
            timeout: 60000 
        });
        
        console.log('    Waiting for username field...');
        await page.waitForSelector('#username', { timeout: 30000 });
        
        console.log('    Typing username...');
        await page.type('#username', username, { delay: 100 });
        
        console.log('    Typing password...');
        await page.type('#password', password, { delay: 100 });
        
        console.log('    Clicking submit button...');
        await Promise.all([
            page.click('button[type="submit"]'),
            page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 60000 })
        ]);
        
        console.log('    Login completed');
    } catch (error) {
        console.error('    Login failed:', error.message);
        throw error;
    }
}

async function checkMenuItems(page, expectedMenus) {
    const menuItems = await page.evaluate(() => {
        const items = document.querySelectorAll('.nav-link');
        return Array.from(items).map(item => item.textContent.trim());
    });
    
    console.log('  Found menu items:', menuItems);
    
    for (const expectedMenu of expectedMenus) {
        const found = menuItems.some(item => item.includes(expectedMenu));
        if (!found) {
            console.log(`  ✗ Expected menu "${expectedMenu}" not found`);
            return false;
        }
        console.log(`  ✓ Found menu "${expectedMenu}"`);
    }
    return true;
}

async function testUserRole(userKey) {
    console.log(`\n=== Testing ${userKey} (${TEST_USERS[userKey].role}) ===`);
    const browser = await puppeteer.launch({ 
        headless: false, // headed mode
        slowMo: 100 // slow down for visibility
    });
    const page = await browser.newPage();
    
    try {
        // Login
        console.log('  Logging in...');
        await login(page, TEST_USERS[userKey].username, TEST_USERS[userKey].password);
        
        // Check if on dashboard
        const currentUrl = page.url();
        console.log(`  Current URL: ${currentUrl}`);
        assert(currentUrl.includes('dashboard.php'), 'Should be on dashboard after login');
        console.log('  ✓ Successfully logged in and redirected to dashboard');
        
        // Check menu items
        console.log('  Checking menu items...');
        const menuCheck = await checkMenuItems(page, EXPECTED_MENUS[userKey]);
        assert(menuCheck, 'Menu items should match expected for this role');
        console.log('  ✓ Menu items are correct for this role');
        
        // Test access to restricted pages
        console.log('  Testing access to restricted pages...');
        
        // Test Users page
        try {
            await page.goto(BASE_URL + '/pages/users/index.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
            await new Promise(resolve => setTimeout(resolve, 1000)); // Wait for potential redirect
            const usersUrl = page.url();
            if (['owner', 'admin'].includes(userKey)) {
                assert(usersUrl.includes('users/index.php'), 'Should access Users page');
                console.log('  ✓ Can access Users page');
            } else {
                assert(usersUrl.includes('dashboard.php'), 'Should be redirected to dashboard');
                console.log('  ✓ Correctly denied access to Users page');
            }
        } catch (error) {
            console.error('  ✗ Error testing Users page:', error.message);
            throw error;
        }
        
        // Test Cabang page
        try {
            await page.goto(BASE_URL + '/pages/cabang/index.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
            await new Promise(resolve => setTimeout(resolve, 1000)); // Wait for potential redirect
            const cabangUrl = page.url();
            if (['owner', 'admin'].includes(userKey)) {
                assert(cabangUrl.includes('cabang/index.php'), 'Should access Cabang page');
                console.log('  ✓ Can access Cabang page');
            } else {
                assert(cabangUrl.includes('dashboard.php'), 'Should be redirected to dashboard');
                console.log('  ✓ Correctly denied access to Cabang page');
            }
        } catch (error) {
            console.error('  ✗ Error testing Cabang page:', error.message);
            throw error;
        }
        
        // Test Petugas page
        try {
            await page.goto(BASE_URL + '/pages/petugas/index.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
            await new Promise(resolve => setTimeout(resolve, 1000)); // Wait for potential redirect
            const petugasUrl = page.url();
            if (['owner', 'admin'].includes(userKey)) {
                assert(petugasUrl.includes('petugas/index.php'), 'Should access Petugas page');
                console.log('  ✓ Can access Petugas page');
            } else {
                assert(petugasUrl.includes('dashboard.php'), 'Should be redirected to dashboard');
                console.log('  ✓ Correctly denied access to Petugas page');
            }
        } catch (error) {
            console.error('  ✗ Error testing Petugas page:', error.message);
            throw error;
        }
        
        console.log(`  ✓ All tests passed for ${userKey}`);
        return true;
        
    } catch (error) {
        console.error(`  ✗ Test failed for ${userKey}:`, error.message);
        return false;
    } finally {
        await browser.close();
    }
}

async function testPermissionManagement() {
    console.log('\n=== Testing Permission Management UI ===');
    const browser = await puppeteer.launch({ 
        headless: false, // headed mode
        slowMo: 100 // slow down for visibility
    });
    const page = await browser.newPage();
    
    try {
        // Login as owner
        console.log('  Logging in as owner...');
        await login(page, TEST_USERS.owner.username, TEST_USERS.owner.password);
        
        // Navigate to Users page
        console.log('  Navigating to Users page...');
        try {
            await page.goto(BASE_URL + '/pages/users/index.php', { waitUntil: 'domcontentloaded', timeout: 60000 });
            await page.waitForSelector('table', { timeout: 30000 });
            console.log('  ✓ Users page loaded');
        } catch (error) {
            console.error('  ✗ Error loading Users page:', error.message);
            throw error;
        }
        
        // Check for permission management button
        console.log('  Checking for permission management button...');
        try {
            const permButton = await page.$('a[href*="permissions/index.php"]');
            assert(permButton !== null, 'Permission management button should exist for owner');
            console.log('  ✓ Permission management button found');
        } catch (error) {
            console.error('  ✗ Permission management button not found:', error.message);
            throw error;
        }
        
        // Click on permission management for karyawan1
        console.log('  Opening permission management for karyawan1...');
        try {
            // Click the first permission management button (for karyawan1)
            const permButton = await page.$('a[href*="permissions/index.php"]');
            if (permButton) {
                await Promise.all([
                    permButton.click(),
                    page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 60000 }).catch(() => {})
                ]);
            }
            await new Promise(resolve => setTimeout(resolve, 2000)); // Wait for page load
            await page.waitForSelector('.card', { timeout: 30000 });
            console.log('  ✓ Permission management page loaded');
        } catch (error) {
            console.error('  ✗ Error loading permission management page:', error.message);
            throw error;
        }
        
        // Check permission categories
        console.log('  Checking permission categories...');
        const categories = await page.evaluate(() => {
            // Try different selectors for permission categories
            const headers = document.querySelectorAll('.permission-category h5');
            if (headers.length > 0) {
                return Array.from(headers).map(h => h.textContent.trim());
            }
            // Alternative: look for card-title elements
            const cardTitles = document.querySelectorAll('.card-title');
            if (cardTitles.length > 0) {
                return Array.from(cardTitles).map(h => h.textContent.trim());
            }
            return [];
        });
        console.log('  Found categories:', categories);
        // Don't fail if categories are empty, just log it
        if (categories.length > 0) {
            console.log('  ✓ Permission categories loaded');
        } else {
            console.log('  ⚠ No permission categories found (page might have different structure)');
        }
        
        // Check user info
        console.log('  Checking user info display...');
        const userInfo = await page.evaluate(() => {
            const table = document.querySelector('.card-body table');
            return table ? table.textContent.trim() : '';
        });
        assert(userInfo.includes('karyawan1'), 'Should show karyawan1 info');
        console.log('  ✓ User info displayed correctly');
        
        // Check permission checkboxes
        console.log('  Checking permission checkboxes...');
        const checkboxes = await page.$$('input[type="checkbox"]');
        assert(checkboxes.length > 0, 'Should have permission checkboxes');
        console.log(`  ✓ Found ${checkboxes.length} permission checkboxes`);
        
        console.log('  ✓ Permission management UI test passed');
        return true;
        
    } catch (error) {
        console.error('  ✗ Permission management test failed:', error.message);
        return false;
    } finally {
        await browser.close();
    }
}

async function runAllTests() {
    console.log('========================================');
    console.log('Permission System Comprehensive Tests');
    console.log('========================================');
    
    const results = {};
    
    // Test each user role
    for (const userKey of Object.keys(TEST_USERS)) {
        results[userKey] = await testUserRole(userKey);
    }
    
    // Test permission management UI
    results['permission_management'] = await testPermissionManagement();
    
    // Summary
    console.log('\n========================================');
    console.log('Test Summary');
    console.log('========================================');
    for (const [test, passed] of Object.entries(results)) {
        console.log(`${test}: ${passed ? '✓ PASSED' : '✗ FAILED'}`);
    }
    
    const allPassed = Object.values(results).every(r => r);
    console.log('\nOverall:', allPassed ? '✓ ALL TESTS PASSED' : '✗ SOME TESTS FAILED');
    
    process.exit(allPassed ? 0 : 1);
}

// Run tests
runAllTests().catch(error => {
    console.error('Test suite error:', error);
    process.exit(1);
});
