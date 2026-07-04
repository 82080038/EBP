const puppeteer = require('puppeteer');

async function testPage(url, pageName) {
    console.log(`\n=== Testing ${pageName} ===`);
    console.log(`URL: ${url}`);
    
    const browser = await puppeteer.launch({ 
        headless: false,
        slowMo: 50,
        args: ['--start-maximized']
    });
    
    try {
        const page = await browser.newPage();
        
        // Set viewport
        await page.setViewport({ width: 1920, height: 1080 });
        
        // Check for console errors
        page.on('console', msg => {
            if (msg.type() === 'error') {
                console.error(`Console Error: ${msg.text()}`);
            }
        });
        
        // Check for JavaScript errors
        page.on('pageerror', error => {
            console.error(`Page Error: ${error.message}`);
        });
        
        // Navigate to login page first
        console.log('Navigating to login page...');
        await page.goto('http://localhost/kewer/login.php', { waitUntil: 'networkidle2', timeout: 60000 });
        
        // Wait for login form
        await page.waitForSelector('input[name="username"]', { timeout: 10000 });
        
        // Login with test credentials
        console.log('Logging in...');
        await page.type('input[name="username"]', 'patri', { delay: 50 });
        await page.type('input[name="password"]', 'Kewer2024!', { delay: 50 });
        
        // Click submit button
        await page.click('button[type="submit"]');
        
        // Wait for navigation to dashboard (with longer timeout)
        try {
            await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 });
            console.log('Login successful');
        } catch (navError) {
            console.log('Navigation timeout, checking if already logged in...');
            // Check if we're already on dashboard
            const currentUrl = page.url();
            if (currentUrl.includes('dashboard.php') || currentUrl.includes('pages/')) {
                console.log('Already logged in, continuing...');
            } else {
                throw navError;
            }
        }
        
        // Navigate to target page
        console.log(`Navigating to ${pageName}...`);
        await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
        
        // Wait for page to load
        await page.waitForTimeout(3000);
        
        // Check if page loaded successfully
        const title = await page.title();
        console.log(`Page title: ${title}`);
        
        // Check for common issues
        const hasSpinner = await page.$('.spinner-border');
        if (hasSpinner) {
            console.log('WARNING: Spinner still visible - data might not be loading');
        }
        
        // Check for error messages
        const errorAlert = await page.$('.alert-danger');
        if (errorAlert) {
            const errorText = await page.evaluate(el => el.textContent, errorAlert);
            console.error(`ERROR ALERT: ${errorText}`);
        }
        
        // Take screenshot
        const screenshotPath = `tests/screenshots/${pageName.replace(/\//g, '-')}.png`;
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`Screenshot saved: ${screenshotPath}`);
        
        console.log(`✓ ${pageName} test completed`);
        
        await page.close();
        return true;
        
    } catch (error) {
        console.error(`✗ ${pageName} test failed: ${error.message}`);
        // Take screenshot of error state
        try {
            const errorScreenshotPath = `tests/screenshots/${pageName.replace(/\//g, '-')}-error.png`;
            await page.screenshot({ path: errorScreenshotPath, fullPage: true });
            console.log(`Error screenshot saved: ${errorScreenshotPath}`);
        } catch (screenshotError) {
            console.log('Could not save error screenshot');
        }
        return false;
    } finally {
        await browser.close();
    }
}

async function runTests() {
    console.log('Starting Puppeteer Headed Tests...');
    console.log('=====================================');
    
    const tests = [
        { url: 'http://localhost/kewer/dashboard.php', name: 'dashboard.php' },
        { url: 'http://localhost/kewer/pages/nasabah/index.php', name: 'nasabah/index.php' },
        { url: 'http://localhost/kewer/pages/pinjaman/index.php', name: 'pinjaman/index.php' },
        { url: 'http://localhost/kewer/pages/angsuran/index.php', name: 'angsuran/index.php' },
        { url: 'http://localhost/kewer/pages/pembayaran/index.php', name: 'pembayaran/index.php' },
    ];
    
    const results = [];
    
    for (const test of tests) {
        const result = await testPage(test.url, test.name);
        results.push({ name: test.name, success: result });
        
        // Wait between tests
        await new Promise(resolve => setTimeout(resolve, 2000));
    }
    
    console.log('\n=====================================');
    console.log('Test Results:');
    console.log('=====================================');
    
    results.forEach(result => {
        console.log(`${result.success ? '✓' : '✗'} ${result.name}`);
    });
    
    const passed = results.filter(r => r.success).length;
    const failed = results.filter(r => !r.success).length;
    
    console.log(`\nTotal: ${results.length}`);
    console.log(`Passed: ${passed}`);
    console.log(`Failed: ${failed}`);
    
    if (failed > 0) {
        console.log('\nFAILED TESTS:');
        results.filter(r => !r.success).forEach(r => {
            console.log(`- ${r.name}`);
        });
    }
}

// Create screenshots directory
const fs = require('fs');
const screenshotDir = 'tests/screenshots';
if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
}

// Run tests
runTests().catch(console.error);
