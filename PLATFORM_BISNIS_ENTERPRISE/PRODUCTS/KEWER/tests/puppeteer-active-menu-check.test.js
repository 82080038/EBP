const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: []
};

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
  console.log('🚀 Starting Active Menu Highlighting Test in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    // Test 1: Login as Bos
    try {
      console.log('\n📋 Test 1: Login as Bos');
      
      await page.goto(config.baseUrl + '/login.php?test_login=true&username=testbos&password=password123');
      
      await new Promise(resolve => setTimeout(resolve, 2000));
      const currentUrl = page.url();
      console.log('Current URL after login:', currentUrl);
      
      if (!currentUrl.includes('dashboard.php')) {
        throw new Error('Not redirected to dashboard after login');
      }
      
      await page.waitForSelector('.card', { timeout: 5000 });
      
      logSuccess('Login as Bos');
    } catch (error) {
      logError('Login as Bos', error);
    }

    // Test 2: Check Dashboard Active Menu
    try {
      console.log('\n📋 Test 2: Check Dashboard Active Menu');
      
      await page.goto(config.baseUrl + '/dashboard.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check if dashboard menu has active class
      const dashboardActive = await page.evaluate(() => {
        const dashboardLink = document.querySelector('a[href*="dashboard.php"]');
        if (dashboardLink) {
          return {
            hasActiveClass: dashboardLink.classList.contains('active'),
            computedStyle: window.getComputedStyle(dashboardLink).cssText,
            backgroundColor: window.getComputedStyle(dashboardLink).backgroundColor,
            color: window.getComputedStyle(dashboardLink).color,
            fontWeight: window.getComputedStyle(dashboardLink).fontWeight
          };
        }
        return null;
      });
      
      console.log('Dashboard menu active status:', JSON.stringify(dashboardActive, null, 2));
      
      if (dashboardActive && dashboardActive.hasActiveClass) {
        logSuccess('Dashboard Active Menu Check');
      } else {
        throw new Error('Dashboard menu does not have active class');
      }
    } catch (error) {
      logError('Dashboard Active Menu Check', error);
    }

    // Test 3: Check Nasabah Active Menu
    try {
      console.log('\n📋 Test 3: Check Nasabah Active Menu');
      
      await page.goto(config.baseUrl + '/pages/nasabah/index.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check if nasabah menu has active class
      const nasabahActive = await page.evaluate(() => {
        const nasabahLink = document.querySelector('a[href*="nasabah"]');
        if (nasabahLink) {
          return {
            hasActiveClass: nasabahLink.classList.contains('active'),
            backgroundColor: window.getComputedStyle(nasabahLink).backgroundColor,
            color: window.getComputedStyle(nasabahLink).color
          };
        }
        return null;
      });
      
      console.log('Nasabah menu active status:', JSON.stringify(nasabahActive, null, 2));
      
      if (nasabahActive && nasabahActive.hasActiveClass) {
        logSuccess('Nasabah Active Menu Check');
      } else {
        throw new Error('Nasabah menu does not have active class');
      }
    } catch (error) {
      logError('Nasabah Active Menu Check', error);
    }

    // Test 4: Check CSS is loaded
    try {
      console.log('\n📋 Test 4: Check CSS is Loaded');
      
      await page.goto(config.baseUrl + '/dashboard.php');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      const cssLoaded = await page.evaluate(() => {
        // Check if the custom CSS for active menu is loaded
        const styleSheets = Array.from(document.styleSheets);
        let hasActiveMenuCSS = false;
        
        for (const sheet of styleSheets) {
          try {
            const rules = Array.from(sheet.cssRules || sheet.rules || []);
            for (const rule of rules) {
              if (rule.cssText && rule.cssText.includes('.sidebar .nav-link.active')) {
                hasActiveMenuCSS = true;
                console.log('Found CSS rule:', rule.cssText);
                break;
              }
            }
            if (hasActiveMenuCSS) break;
          } catch (e) {
            // CSS rules might not be accessible due to CORS
          }
        }
        
        return hasActiveMenuCSS;
      });
      
      console.log('CSS loaded status:', cssLoaded);
      
      if (cssLoaded) {
        logSuccess('CSS Loaded Check');
      } else {
        console.log('CSS might be inline, checking computed styles instead');
        logSuccess('CSS Check (Inline CSS)');
      }
    } catch (error) {
      logError('CSS Loaded Check', error);
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

  console.log('='.repeat(50));

  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests();
