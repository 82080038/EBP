const puppeteer = require('puppeteer');

async function checkBrowserConsoleErrors() {
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage();
    
    const consoleErrors = [];
    const consoleWarnings = [];
    const pageErrors = [];
    
    // Capture console messages
    page.on('console', msg => {
        const type = msg.type();
        const text = msg.text();
        
        if (type === 'error') {
            consoleErrors.push({
                type: type,
                text: text,
                location: msg.location()
            });
        } else if (type === 'warning') {
            consoleWarnings.push({
                type: type,
                text: text,
                location: msg.location()
            });
        }
    });
    
    // Capture page errors
    page.on('pageerror', error => {
        pageErrors.push({
            message: error.message,
            stack: error.stack
        });
    });
    
    // Test pages
    const pagesToTest = [
        'http://localhost/kewer/login.php',
        'http://localhost/kewer/dashboard.php',
        'http://localhost/kewer/pages/nasabah/index.php',
        'http://localhost/kewer/pages/pinjaman/index.php',
        'http://localhost/kewer/pages/angsuran/index.php'
    ];
    
    for (const url of pagesToTest) {
        console.log(`\nTesting: ${url}`);
        
        try {
            await page.goto(url, { waitUntil: 'networkidle2', timeout: 10000 });
            await new Promise(resolve => setTimeout(resolve, 2000)); // Wait for any delayed JS errors
        } catch (error) {
            console.log(`  Error loading page: ${error.message}`);
            pageErrors.push({
                url: url,
                message: error.message
            });
        }
    }
    
    await browser.close();
    
    // Print results
    console.log('\n' + '='.repeat(60));
    console.log('BROWSER CONSOLE CHECK RESULTS');
    console.log('='.repeat(60));
    
    console.log(`\nConsole Errors: ${consoleErrors.length}`);
    if (consoleErrors.length > 0) {
        consoleErrors.forEach((err, i) => {
            console.log(`  ${i + 1}. ${err.text}`);
            if (err.location) {
                console.log(`     Location: ${err.location.url}:${err.location.lineNumber}`);
            }
        });
    }
    
    console.log(`\nConsole Warnings: ${consoleWarnings.length}`);
    if (consoleWarnings.length > 0) {
        consoleWarnings.slice(0, 10).forEach((warn, i) => {
            console.log(`  ${i + 1}. ${warn.text}`);
        });
        if (consoleWarnings.length > 10) {
            console.log(`  ... and ${consoleWarnings.length - 10} more warnings`);
        }
    }
    
    console.log(`\nPage Errors: ${pageErrors.length}`);
    if (pageErrors.length > 0) {
        pageErrors.forEach((err, i) => {
            console.log(`  ${i + 1}. ${err.message}`);
            if (err.stack) {
                console.log(`     Stack: ${err.stack.split('\n')[0]}`);
            }
        });
    }
    
    console.log('\n' + '='.repeat(60));
    console.log(`TOTAL: ${consoleErrors.length} errors, ${consoleWarnings.length} warnings, ${pageErrors.length} page errors`);
    console.log('='.repeat(60));
    
    return {
        consoleErrors,
        consoleWarnings,
        pageErrors
    };
}

// Run the check
checkBrowserConsoleErrors().then(results => {
    process.exit(results.consoleErrors.length > 0 || results.pageErrors.length > 0 ? 1 : 0);
}).catch(error => {
    console.error('Error running browser check:', error);
    process.exit(1);
});
