const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Screen configuration from xrandr
// Total screen: 3840x1880
// HDMI-0: 1920x1080 at x=0, y=800 (bottom left)
// DVI-D-1-0: 1920x1080 at x=1920, y=800 (bottom right)
// HDMI-1-0: 1280x800 at x=1356, y=0 (top middle)

// Distribute 9 roles across 3 screens (3 roles per screen)
const roles = [
  // Screen 1 (HDMI-0, bottom left): appOwner, bos, manager_pusat
  { name: 'appOwner', username: 'appowner', password: 'AppOwner2024!', dashboard: 'pages/app_owner/dashboard.php', screen: 0, position: 0 },
  { name: 'bos', username: 'patri', password: 'Kewer2024!', dashboard: 'dashboard.php', screen: 0, position: 1 },
  { name: 'manager_pusat', username: 'mgr_pusat', password: 'Kewer2024!', dashboard: 'dashboard.php', screen: 0, position: 2 },
  
  // Screen 2 (DVI-D-1-0, bottom right): manager_cabang, admin_pusat, admin_cabang
  { name: 'manager_cabang', username: 'mgr_balige', password: 'Kewer2024!', dashboard: 'dashboard.php', screen: 1, position: 0 },
  { name: 'admin_pusat', username: 'adm_pusat', password: 'Kewer2024!', dashboard: 'dashboard.php', screen: 1, position: 1 },
  { name: 'admin_cabang', username: 'adm_balige', password: 'Kewer2024!', dashboard: 'dashboard.php', screen: 1, position: 2 },
  
  // Screen 3 (HDMI-1-0, top middle): petugas_pusat, petugas_cabang, karyawan
  { name: 'petugas_pusat', username: 'ptr_pusat', password: 'Kewer2024!', dashboard: 'dashboard.php', screen: 2, position: 0 },
  { name: 'petugas_cabang', username: 'ptr_balige', password: 'Kewer2024!', dashboard: 'dashboard.php', screen: 2, position: 1 },
  { name: 'karyawan', username: 'krw_pusat', password: 'Kewer2024!', dashboard: 'dashboard.php', screen: 2, position: 2 },
];

// Screen configurations
const screens = [
  { name: 'HDMI-0', x: 0, y: 800, width: 1920, height: 1080 },
  { name: 'DVI-D-1-0', x: 1920, y: 800, width: 1920, height: 1080 },
  { name: 'HDMI-1-0', x: 1356, y: 0, width: 1280, height: 800 }
];

// Test results
const results = {
  roles: {},
  screenshots: []
};

// Create screenshots directory
const fs = require('fs');
const path = require('path');
const screenshotDir = path.join(__dirname, 'screenshots');
if (!fs.existsSync(screenshotDir)) {
  fs.mkdirSync(screenshotDir, { recursive: true });
}

// Helper function to take screenshot
async function takeScreenshot(page, name, role) {
  const screenshotPath = path.join(screenshotDir, `${role}_${name}.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });
  console.log(`Screenshot saved: ${screenshotPath}`);
  results.screenshots.push({ role, name, path: screenshotPath });
  return screenshotPath;
}

// Function to handle a single role
async function handleRole(browser, roleConfig) {
  const { name, username, password, dashboard, screen, position } = roleConfig;
  const screenConfig = screens[screen];
  
  console.log(`\n🚀 Starting ${name} role...`);
  console.log(`   Screen: ${screenConfig.name} (${screenConfig.width}x${screenConfig.height})`);
  console.log(`   Position: ${position} (0=left, 1=center, 2=right)`);
  
  try {
    // Create new browser context for this role
    const context = await browser.createBrowserContext();
    const page = await context.newPage();
    
    // Calculate window position based on screen and position within screen
    const screenWidth = screenConfig.width;
    const screenHeight = screenConfig.height;
    const windowWidth = screenWidth / 3; // Divide screen into 3 columns
    const windowHeight = screenHeight;
    const windowX = screenConfig.x + (position * windowWidth);
    const windowY = screenConfig.y;
    
    // Set viewport to match window size
    await page.setViewport({ width: windowWidth, height: windowHeight });
    
    // Set window position using Chrome DevTools Protocol
    try {
      const session = await page.target().createCDPSession();
      await session.send('Browser.setWindowBounds', {
        bounds: {
          left: windowX,
          top: windowY,
          width: windowWidth,
          height: windowHeight
        }
      });
    } catch (error) {
      console.warn(`   Could not set window bounds via CDP: ${error.message}`);
      // Fallback: try to position using JavaScript
      await page.evaluateOnNewDocument((x, y) => {
        window.moveTo(x, y);
        window.resizeTo(window.innerWidth, window.innerHeight);
      }, windowX, windowY);
    }
    
    console.log(`   Window positioned at: x=${windowX}, y=${windowY}, ${windowWidth}x${windowHeight}`);
    
    // Login
    const loginUrl = `${config.baseUrl}/login.php?test_login=true&username=${username}&password=${password}`;
    await page.goto(loginUrl);
    await new Promise(resolve => setTimeout(resolve, 3000));
    
    // Navigate to dashboard
    const dashboardUrl = `${config.baseUrl}/${dashboard}`;
    await page.goto(dashboardUrl);
    await new Promise(resolve => setTimeout(resolve, 3000));
    
    // Take dashboard screenshot
    await takeScreenshot(page, 'dashboard', name);
    
    // Navigate to key features based on role
    const features = getFeaturesForRole(name);
    
    for (const feature of features) {
      try {
        console.log(`  Navigating to ${feature.name}...`);
        await page.goto(`${config.baseUrl}${feature.url}`);
        await new Promise(resolve => setTimeout(resolve, 3000));
        await takeScreenshot(page, feature.name.replace(/\s+/g, '_').toLowerCase(), name);
        
        // Get page content for analysis
        const content = await page.evaluate(() => {
          return {
            title: document.title,
            url: window.location.href,
            hasData: document.querySelector('table') !== null || document.querySelector('.card') !== null,
            hasForm: document.querySelector('form') !== null,
            hasButtons: document.querySelectorAll('button').length > 0
          };
        });
        
        results.roles[name] = {
          ...results.roles[name],
          features: {
            ...results.roles[name]?.features,
            [feature.name]: content
          }
        };
      } catch (error) {
        console.error(`  Error navigating to ${feature.name}:`, error.message);
      }
    }
    
    // Get role-specific data from dashboard
    const dashboardData = await page.evaluate(() => {
      const stats = {};
      const cards = document.querySelectorAll('.card, .stat-card');
      cards.forEach(card => {
        const title = card.querySelector('h5, .card-title, .text-xs')?.textContent?.trim();
        const value = card.querySelector('h3, .h2, .display-6')?.textContent?.trim();
        if (title && value) {
          stats[title] = value;
        }
      });
      return {
        stats,
        hasMenu: document.querySelector('.navbar, .sidebar, nav') !== null,
        menuItems: Array.from(document.querySelectorAll('.nav-link, .sidebar a')).map(a => a.textContent.trim())
      };
    });
    
    results.roles[name] = {
      ...results.roles[name],
      dashboard: dashboardData,
      screen: screenConfig.name,
      status: 'success'
    };
    
    console.log(`✅ ${name} role completed on ${screenConfig.name}`);
    
    // Keep the page open for demonstration
    return { page, context };
    
  } catch (error) {
    console.error(`❌ ${name} role failed:`, error.message);
    results.roles[name] = {
      status: 'failed',
      error: error.message
    };
    return null;
  }
}

// Get features to test for each role
function getFeaturesForRole(role) {
  const commonFeatures = [
    { name: 'Dashboard', url: '/dashboard.php' }
  ];
  
  const roleFeatures = {
    appOwner: [
      { name: 'Features Management', url: '/pages/app_owner/features.php' },
      { name: 'BOS Registrations', url: '/pages/app_owner/registrations.php' }
    ],
    bos: [
      { name: 'Nasabah', url: '/pages/nasabah/index.php' },
      { name: 'Pinjaman', url: '/pages/pinjaman/index.php' },
      { name: 'Cabang', url: '/pages/cabang/index.php' },
      { name: 'Users', url: '/pages/users/index.php' }
    ],
    manager_pusat: [
      { name: 'Nasabah', url: '/pages/nasabah/index.php' },
      { name: 'Pinjaman', url: '/pages/pinjaman/index.php' },
      { name: 'Cabang', url: '/pages/cabang/index.php' },
      { name: 'Users', url: '/pages/users/index.php' }
    ],
    manager_cabang: [
      { name: 'Nasabah', url: '/pages/nasabah/index.php' },
      { name: 'Pinjaman', url: '/pages/pinjaman/index.php' },
      { name: 'Angsuran', url: '/pages/angsuran/index.php' }
    ],
    admin_pusat: [
      { name: 'Nasabah', url: '/pages/nasabah/index.php' },
      { name: 'Pinjaman', url: '/pages/pinjaman/index.php' },
      { name: 'Angsuran', url: '/pages/angsuran/index.php' },
      { name: 'Users', url: '/pages/users/index.php' }
    ],
    admin_cabang: [
      { name: 'Nasabah', url: '/pages/nasabah/index.php' },
      { name: 'Pinjaman', url: '/pages/pinjaman/index.php' },
      { name: 'Angsuran', url: '/pages/angsuran/index.php' }
    ],
    petugas_pusat: [
      { name: 'Nasabah', url: '/pages/nasabah/index.php' },
      { name: 'Pinjaman', url: '/pages/pinjaman/index.php' },
      { name: 'Angsuran', url: '/pages/angsuran/index.php' },
      { name: 'Field Activities', url: '/pages/field_activities/index.php' }
    ],
    petugas_cabang: [
      { name: 'Nasabah', url: '/pages/nasabah/index.php' },
      { name: 'Pinjaman', url: '/pages/pinjaman/index.php' },
      { name: 'Angsuran', url: '/pages/angsuran/index.php' },
      { name: 'Field Activities', url: '/pages/field_activities/index.php' }
    ],
    karyawan: [
      { name: 'Nasabah', url: '/pages/nasabah/index.php' },
      { name: 'Pinjaman', url: '/pages/pinjaman/index.php' }
    ]
  };
  
  return [...commonFeatures, ...(roleFeatures[role] || [])];
}

// Main function
async function runTest() {
  console.log('🚀 Starting Multi-Role Simultaneous Test (Puppeteer Headed)...\n');
  console.log('This test will open all 9 roles simultaneously in different browser windows.\n');
  
  let browser;
  const contexts = [];
  
  try {
    browser = await puppeteer.launch({
      headless: false,
      args: [
        '--start-maximized',
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-web-security',
        '--disable-features=IsolateOrigins,site-per-process'
      ]
    });
    
    // Launch all roles simultaneously
    console.log('📋 Launching all roles simultaneously...\n');
    
    const rolePromises = roles.map(role => handleRole(browser, role));
    const resultsArray = await Promise.all(rolePromises);
    
    contexts.push(...resultsArray.filter(r => r !== null));
    
    console.log('\n' + '='.repeat(80));
    console.log('📊 Multi-Role Integration & Logic Report');
    console.log('='.repeat(80));
    
    // Print role status
    console.log('\n📋 Role Status:');
    Object.entries(results.roles).forEach(([role, data]) => {
      const icon = data.status === 'success' ? '✅' : '❌';
      console.log(`${icon} ${role}: ${data.status}`);
      if (data.status === 'success' && data.dashboard) {
        console.log(`   Dashboard Stats: ${Object.keys(data.dashboard.stats || {}).length} metrics`);
        console.log(`   Menu Items: ${data.dashboard.menuItems?.length || 0} items`);
      }
    });
    
    // Print feature access matrix
    console.log('\n📋 Feature Access Matrix:');
    console.log('Role'.padEnd(20) + 'Features');
    console.log('-'.repeat(80));
    Object.entries(results.roles).forEach(([role, data]) => {
      if (data.status === 'success' && data.features) {
        const featureNames = Object.keys(data.features);
        console.log(role.padEnd(20) + featureNames.join(', '));
      }
    });
    
    console.log('\n📸 Screenshots saved to: ' + screenshotDir);
    console.log(`Total screenshots: ${results.screenshots.length}`);
    
    console.log('\n' + '='.repeat(80));
    console.log('✅ All roles launched successfully in headed mode');
    console.log('📌 Browser windows will remain open for manual inspection');
    console.log('⏱️  Press Ctrl+C to close all browsers and exit');
    console.log('='.repeat(80));
    
    // Keep browsers open for demonstration
    console.log('\n⏸️  Keeping browsers open for demonstration...');
    console.log('Press Ctrl+C to exit\n');
    
    // Wait indefinitely (user can Ctrl+C to exit)
    await new Promise(() => {});
    
  } catch (error) {
    console.error('Fatal Error:', error);
  } finally {
    // Clean up
    if (contexts.length > 0) {
      console.log('\n🧹 Closing all browser contexts...');
      await Promise.all(contexts.map(ctx => {
        if (ctx) {
          return ctx.context.close();
        }
        return Promise.resolve();
      }));
    }
    if (browser) {
      await browser.close();
    }
  }
}

// Run the test
runTest().catch(console.error);
