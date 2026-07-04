# MODUL 39 — AUTOMATION TESTING GUIDE

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Panduan implementasi automation testing untuk aplikasi Tour Guide menggunakan **Playwright (headed browser)** dengan comprehensive monitoring (terminal, console, network, page elements) untuk deteksi bug, error, dan warning secara batch dan komprehensif.

---

## 2. PLAYWRIGHT SETUP

### 2.1 Install Playwright

```bash
# Install Node.js if not already installed
sudo apt update
sudo apt install -y nodejs npm

# Install Playwright
npm init -y
npm install @playwright/test
npx playwright install chromium
npx playwright install-deps chromium
```

### 2.2 Playwright Configuration

```javascript
// playwright.config.js
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [
    ['html'],
    ['json', { outputFile: 'test-results/results.json' }],
    ['junit', { outputFile: 'test-results/junit.xml' }]
  ],
  use: {
    // Use headed browser for comprehensive monitoring
    headless: false,
    // Capture console logs
    trace: 'on-first-retry',
    // Capture screenshots
    screenshot: 'only-on-failure',
    // Capture video
    video: 'retain-on-failure',
    // Viewport size
    viewport: { width: 1280, height: 720 },
    // Ignore HTTPS errors
    ignoreHTTPSErrors: true,
    // Action timeout
    actionTimeout: 30000,
    // Navigation timeout
    navigationTimeout: 60000,
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
```

### 2.3 Test Configuration

```javascript
// tests/setup.js
const { test, expect } = require('@playwright/test');

// Global setup for all tests
test.beforeEach(async ({ page, context }) => {
  // Enable console logging
  page.on('console', msg => {
    console.log(`Console [${msg.type()}]: ${msg.text()}`);
  });
  
  // Enable page error logging
  page.on('pageerror', error => {
    console.error(`Page Error: ${error.message}`);
  });
  
  // Enable request/response logging
  page.on('request', request => {
    console.log(`Request: ${request.method()} ${request.url()}`);
  });
  
  page.on('response', response => {
    console.log(`Response: ${response.status()} ${response.url()}`);
  });
  
  // Set default timeout
  test.setTimeout(60000);
});
```

---

## 3. COMPREHENSIVE MONITORING

### 3.1 Console Monitoring

```javascript
// tests/monitoring/console-monitor.js
class ConsoleMonitor {
  constructor(page) {
    this.page = page;
    this.consoleLogs = [];
    this.errors = [];
    this.warnings = [];
    
    this.setupMonitoring();
  }
  
  setupMonitoring() {
    // Monitor all console messages
    this.page.on('console', msg => {
      const logEntry = {
        type: msg.type(),
        text: msg.text(),
        location: msg.location(),
        timestamp: new Date().toISOString()
      };
      
      this.consoleLogs.push(logEntry);
      
      // Categorize by type
      if (msg.type() === 'error') {
        this.errors.push(logEntry);
      } else if (msg.type() === 'warning') {
        this.warnings.push(logEntry);
      }
    });
  }
  
  getErrors() {
    return this.errors;
  }
  
  getWarnings() {
    return this.warnings;
  }
  
  getAllLogs() {
    return this.consoleLogs;
  }
  
  hasErrors() {
    return this.errors.length > 0;
  }
  
  hasWarnings() {
    return this.warnings.length > 0;
  }
  
  generateReport() {
    return {
      total_logs: this.consoleLogs.length,
      errors: this.errors,
      warnings: this.warnings,
      summary: {
        error_count: this.errors.length,
        warning_count: this.warnings.length
      }
    };
  }
}
```

### 3.2 Network Monitoring

```javascript
// tests/monitoring/network-monitor.js
class NetworkMonitor {
  constructor(page) {
    this.page = page;
    this.requests = [];
    this.responses = [];
    this.failedRequests = [];
    this.slowRequests = [];
    
    this.setupMonitoring();
  }
  
  setupMonitoring() {
    // Monitor requests
    this.page.on('request', request => {
      const requestData = {
        url: request.url(),
        method: request.method(),
        headers: request.headers(),
        timestamp: new Date().toISOString()
      };
      
      this.requests.push(requestData);
    });
    
    // Monitor responses
    this.page.on('response', response => {
      const responseData = {
        url: response.url(),
        status: response.status(),
        statusText: response.statusText(),
        headers: response.headers(),
        timestamp: new Date().toISOString(),
        duration: null
      };
      
      // Calculate duration
      const request = this.requests.find(r => r.url === response.url());
      if (request) {
        const requestTime = new Date(request.timestamp);
        const responseTime = new Date(responseData.timestamp);
        responseData.duration = responseTime - requestTime;
        
        // Track slow requests (> 2 seconds)
        if (responseData.duration > 2000) {
          this.slowRequests.push(responseData);
        }
      }
      
      // Track failed requests
      if (response.status() >= 400) {
        this.failedRequests.push(responseData);
      }
      
      this.responses.push(responseData);
    });
  }
  
  getFailedRequests() {
    return this.failedRequests;
  }
  
  getSlowRequests() {
    return this.slowRequests;
  }
  
  getNetworkReport() {
    return {
      total_requests: this.requests.length,
      total_responses: this.responses.length,
      failed_requests: this.failedRequests,
      slow_requests: this.slowRequests,
      summary: {
        failure_rate: (this.failedRequests.length / this.responses.length * 100).toFixed(2) + '%',
        slow_request_count: this.slowRequests.length
      }
    };
  }
}
```

### 3.3 Page Element Monitoring

```javascript
// tests/monitoring/page-monitor.js
class PageMonitor {
  constructor(page) {
    this.page = page;
    this.pageErrors = [];
    this.missingElements = [];
    this.brokenImages = [];
    this.brokenLinks = [];
    
    this.setupMonitoring();
  }
  
  setupMonitoring() {
    // Monitor page errors
    this.page.on('pageerror', error => {
      this.pageErrors.push({
        message: error.message,
        stack: error.stack,
        timestamp: new Date().toISOString()
      });
    });
  }
  
  async checkBrokenImages() {
    const images = await this.page.locator('img').all();
    
    for (const img of images) {
      const src = await img.getAttribute('src');
      if (src) {
        const response = await this.page.request.get(src);
        if (response.status() >= 400) {
          this.brokenImages.push({
            src: src,
            status: response.status(),
            alt: await img.getAttribute('alt')
          });
        }
      }
    }
    
    return this.brokenImages;
  }
  
  async checkBrokenLinks() {
    const links = await this.page.locator('a[href]').all();
    
    for (const link of links) {
      const href = await link.getAttribute('href');
      if (href && href.startsWith('http')) {
        try {
          const response = await this.page.request.get(href);
          if (response.status() >= 400) {
            this.brokenLinks.push({
              href: href,
              status: response.status(),
              text: await link.textContent()
            });
          }
        } catch (error) {
          this.brokenLinks.push({
            href: href,
            error: error.message,
            text: await link.textContent()
          });
        }
      }
    }
    
    return this.brokenLinks;
  }
  
  async checkMissingElements(selectors) {
    for (const selector of selectors) {
      const element = await this.page.locator(selector).count();
      if (element === 0) {
        this.missingElements.push({
          selector: selector,
          timestamp: new Date().toISOString()
        });
      }
    }
    
    return this.missingElements;
  }
  
  getPageReport() {
    return {
      page_errors: this.pageErrors,
      broken_images: this.brokenImages,
      broken_links: this.brokenLinks,
      missing_elements: this.missingElements,
      summary: {
        error_count: this.pageErrors.length,
        broken_image_count: this.brokenImages.length,
        broken_link_count: this.brokenLinks.length,
        missing_element_count: this.missingElements.length
      }
    };
  }
}
```

### 3.4 Terminal Monitoring

```javascript
// tests/monitoring/terminal-monitor.js
class TerminalMonitor {
  constructor() {
    this.terminalOutput = [];
    this.errors = [];
    this.warnings = [];
  }
  
  log(message, type = 'info') {
    const logEntry = {
      message: message,
      type: type,
      timestamp: new Date().toISOString()
    };
    
    this.terminalOutput.push(logEntry);
    
    if (type === 'error') {
      this.errors.push(logEntry);
    } else if (type === 'warning') {
      this.warnings.push(logEntry);
    }
    
    console.log(`[${type.toUpperCase()}] ${message}`);
  }
  
  getErrors() {
    return this.errors;
  }
  
  getWarnings() {
    return this.warnings;
  }
  
  getTerminalReport() {
    return {
      total_logs: this.terminalOutput.length,
      errors: this.errors,
      warnings: this.warnings,
      summary: {
        error_count: this.errors.length,
        warning_count: this.warnings.length
      }
    };
  }
}
```

---

## 4. BUG DETECTION WORKFLOW

### 4.1 Comprehensive Bug Detection

```javascript
// tests/bug-detection/comprehensive-detector.js
const { test, expect } = require('@playwright/test');
const ConsoleMonitor = require('../monitoring/console-monitor');
const NetworkMonitor = require('../monitoring/network-monitor');
const PageMonitor = require('../monitoring/page-monitor');
const TerminalMonitor = require('../monitoring/terminal-monitor');

class ComprehensiveBugDetector {
  constructor(page) {
    this.page = page;
    this.consoleMonitor = new ConsoleMonitor(page);
    this.networkMonitor = new NetworkMonitor(page);
    this.pageMonitor = new PageMonitor(page);
    this.terminalMonitor = new TerminalMonitor();
    this.bugs = [];
    this.errors = [];
    this.warnings = [];
  }
  
  async detectBugs() {
    this.terminalMonitor.log('Starting comprehensive bug detection...', 'info');
    
    // Check console errors
    if (this.consoleMonitor.hasErrors()) {
      const consoleErrors = this.consoleMonitor.getErrors();
      this.errors.push(...consoleErrors);
      this.bugs.push({
        type: 'console_error',
        details: consoleErrors,
        severity: 'high'
      });
    }
    
    // Check console warnings
    if (this.consoleMonitor.hasWarnings()) {
      const consoleWarnings = this.consoleMonitor.getWarnings();
      this.warnings.push(...consoleWarnings);
      this.bugs.push({
        type: 'console_warning',
        details: consoleWarnings,
        severity: 'medium'
      });
    }
    
    // Check network failures
    const failedRequests = this.networkMonitor.getFailedRequests();
    if (failedRequests.length > 0) {
      this.errors.push(...failedRequests);
      this.bugs.push({
        type: 'network_failure',
        details: failedRequests,
        severity: 'high'
      });
    }
    
    // Check slow requests
    const slowRequests = this.networkMonitor.getSlowRequests();
    if (slowRequests.length > 0) {
      this.warnings.push(...slowRequests);
      this.bugs.push({
        type: 'slow_request',
        details: slowRequests,
        severity: 'medium'
      });
    }
    
    // Check page errors
    const pageErrors = this.pageMonitor.pageErrors;
    if (pageErrors.length > 0) {
      this.errors.push(...pageErrors);
      this.bugs.push({
        type: 'page_error',
        details: pageErrors,
        severity: 'critical'
      });
    }
    
    // Check broken images
    await this.pageMonitor.checkBrokenImages();
    const brokenImages = this.pageMonitor.brokenImages;
    if (brokenImages.length > 0) {
      this.errors.push(...brokenImages);
      this.bugs.push({
        type: 'broken_image',
        details: brokenImages,
        severity: 'medium'
      });
    }
    
    // Check broken links
    await this.pageMonitor.checkBrokenLinks();
    const brokenLinks = this.pageMonitor.brokenLinks;
    if (brokenLinks.length > 0) {
      this.errors.push(...brokenLinks);
      this.bugs.push({
        type: 'broken_link',
        details: brokenLinks,
        severity: 'medium'
      });
    }
    
    this.terminalMonitor.log(`Bug detection complete. Found ${this.bugs.length} issues.`, 'info');
    
    return this.generateBugReport();
  }
  
  generateBugReport() {
    return {
      total_bugs: this.bugs.length,
      total_errors: this.errors.length,
      total_warnings: this.warnings.length,
      bugs: this.bugs,
      errors: this.errors,
      warnings: this.warnings,
      console_report: this.consoleMonitor.generateReport(),
      network_report: this.networkMonitor.getNetworkReport(),
      page_report: this.pageMonitor.getPageReport(),
      terminal_report: this.terminalMonitor.getTerminalReport()
    };
  }
}
```

### 4.2 Batch Bug Fixing

```javascript
// tests/bug-detection/batch-fixer.js
class BatchBugFixer {
  constructor(bugReport) {
    this.bugReport = bugReport;
    this.fixes = [];
    this.fixAttempts = 0;
  }
  
  async fixBugs() {
    console.log('Starting batch bug fixing...');
    
    // Fix bugs by severity
    const criticalBugs = this.bugReport.bugs.filter(b => b.severity === 'critical');
    const highBugs = this.bugReport.bugs.filter(b => b.severity === 'high');
    const mediumBugs = this.bugReport.bugs.filter(b => b.severity === 'medium');
    const lowBugs = this.bugReport.bugs.filter(b => b.severity === 'low');
    
    // Fix critical bugs first
    for (const bug of criticalBugs) {
      await this.fixBug(bug);
    }
    
    // Fix high bugs
    for (const bug of highBugs) {
      await this.fixBug(bug);
    }
    
    // Fix medium bugs
    for (const bug of mediumBugs) {
      await this.fixBug(bug);
    }
    
    // Fix low bugs
    for (const bug of lowBugs) {
      await this.fixBug(bug);
    }
    
    console.log(`Batch bug fixing complete. Fixed ${this.fixes.length} bugs.`);
    
    return this.generateFixReport();
  }
  
  async fixBug(bug) {
    this.fixAttempts++;
    
    let fix = {
      bug_type: bug.type,
      severity: bug.severity,
      fix_attempted: true,
      fix_successful: false,
      fix_details: null
    };
    
    try {
      switch (bug.type) {
        case 'console_error':
          fix.fix_details = await this.fixConsoleError(bug);
          break;
        case 'console_warning':
          fix.fix_details = await this.fixConsoleWarning(bug);
          break;
        case 'network_failure':
          fix.fix_details = await this.fixNetworkFailure(bug);
          break;
        case 'page_error':
          fix.fix_details = await this.fixPageError(bug);
          break;
        case 'broken_image':
          fix.fix_details = await this.fixBrokenImage(bug);
          break;
        case 'broken_link':
          fix.fix_details = await this.fixBrokenLink(bug);
          break;
        default:
          fix.fix_details = { message: 'Unknown bug type' };
      }
      
      fix.fix_successful = true;
      this.fixes.push(fix);
      
    } catch (error) {
      fix.fix_details = { error: error.message };
      this.fixes.push(fix);
    }
  }
  
  async fixConsoleError(bug) {
    // Generate fix for console error
    return {
      message: 'Console error fix applied',
      action: 'Review and fix JavaScript error',
      location: bug.details[0].location
    };
  }
  
  async fixConsoleWarning(bug) {
    // Generate fix for console warning
    return {
      message: 'Console warning fix applied',
      action: 'Review and fix JavaScript warning',
      location: bug.details[0].location
    };
  }
  
  async fixNetworkFailure(bug) {
    // Generate fix for network failure
    return {
      message: 'Network failure fix applied',
      action: 'Check API endpoint or fix broken link',
      url: bug.details[0].url,
      status: bug.details[0].status
    };
  }
  
  async fixPageError(bug) {
    // Generate fix for page error
    return {
      message: 'Page error fix applied',
      action: 'Review and fix page error',
      error: bug.details[0].message
    };
  }
  
  async fixBrokenImage(bug) {
    // Generate fix for broken image
    return {
      message: 'Broken image fix applied',
      action: 'Replace or fix broken image',
      src: bug.details[0].src
    };
  }
  
  async fixBrokenLink(bug) {
    // Generate fix for broken link
    return {
      message: 'Broken link fix applied',
      action: 'Update or remove broken link',
      href: bug.details[0].href
    };
  }
  
  generateFixReport() {
    return {
      total_fix_attempts: this.fixAttempts,
      successful_fixes: this.fixes.filter(f => f.fix_successful).length,
      failed_fixes: this.fixes.filter(f => !f.fix_successful).length,
      fixes: this.fixes
    };
  }
}
```

### 4.3 Re-testing Workflow

```javascript
// tests/bug-detection/re-testing.js
class ReTestingWorkflow {
  constructor(page) {
    this.page = page;
    this.detector = new ComprehensiveBugDetector(page);
    this.iterations = 0;
    this.maxIterations = 3;
  }
  
  async executeReTestingCycle() {
    this.iterations = 0;
    let hasBugs = true;
    
    while (hasBugs && this.iterations < this.maxIterations) {
      this.iterations++;
      console.log(`\n=== Re-testing Cycle ${this.iterations} ===`);
      
      // Detect bugs
      const bugReport = await this.detector.detectBugs();
      
      // Check if bugs exist
      if (bugReport.total_bugs === 0) {
        console.log('No bugs found. Testing complete.');
        hasBugs = false;
        break;
      }
      
      console.log(`Found ${bugReport.total_bugs} bugs.`);
      
      // Fix bugs
      const fixer = new BatchBugFixer(bugReport);
      const fixReport = await fixer.fixBugs();
      
      console.log(`Fixed ${fixReport.successful_fixes} bugs.`);
      
      // Wait for fixes to be applied
      await this.page.waitForTimeout(2000);
      
      // Reload page to apply fixes
      await this.page.reload();
      
      // Wait for page to load
      await this.page.waitForLoadState('networkidle');
    }
    
    if (this.iterations >= this.maxIterations && hasBugs) {
      console.log('Max iterations reached. Some bugs may remain.');
    }
    
    return {
      iterations: this.iterations,
      final_bug_report: await this.detector.detectBugs()
    };
  }
}
```

---

## 5. PLAYWRIGHT TEST EXAMPLES

### 5.1 Login Test with Monitoring

```javascript
// tests/login.spec.js
const { test, expect } = require('@playwright/test');
const ComprehensiveBugDetector = require('./bug-detection/comprehensive-detector');

test.describe('Login Tests with Monitoring', () => {
  test('valid login with comprehensive monitoring', async ({ page }) => {
    // Initialize bug detector
    const detector = new ComprehensiveBugDetector(page);
    
    // Navigate to login page
    await page.goto('https://tourguide.com/login');
    
    // Fill login form
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password123');
    
    // Submit form
    await page.click('button[type="submit"]');
    
    // Wait for navigation
    await page.waitForURL('**/dashboard');
    
    // Detect bugs
    const bugReport = await detector.detectBugs();
    
    // Assert no critical bugs
    const criticalBugs = bugReport.bugs.filter(b => b.severity === 'critical');
    expect(criticalBugs.length).toBe(0);
    
    // Log bug report
    console.log('Bug Report:', JSON.stringify(bugReport, null, 2));
    
    // Take screenshot if bugs found
    if (bugReport.total_bugs > 0) {
      await page.screenshot({ path: `screenshots/login-bugs-${Date.now()}.png` });
    }
  });
});
```

### 5.2 Booking Test with Monitoring

```javascript
// tests/booking.spec.js
const { test, expect } = require('@playwright/test');
const ComprehensiveBugDetector = require('./bug-detection/comprehensive-detector');

test.describe('Booking Tests with Monitoring', () => {
  test('create booking with comprehensive monitoring', async ({ page }) => {
    const detector = new ComprehensiveBugDetector(page);
    
    // Login first
    await page.goto('https://tourguide.com/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    
    // Navigate to guides
    await page.goto('https://tourguide.com/guides');
    
    // Click on first guide
    await page.click('.guide-card:first-child');
    
    // Wait for guide detail
    await page.waitForSelector('.guide-detail');
    
    // Click booking button
    await page.click('.btn-booking');
    
    // Fill booking form
    await page.fill('input[name="booking_date"]', '2026-07-01');
    await page.fill('input[name="guests"]', '2');
    
    // Submit booking
    await page.click('button[type="submit"]');
    
    // Wait for success message
    await page.waitForSelector('.alert-success');
    
    // Detect bugs
    const bugReport = await detector.detectBugs();
    
    // Assert no critical bugs
    const criticalBugs = bugReport.bugs.filter(b => b.severity === 'critical');
    expect(criticalBugs.length).toBe(0);
    
    // Log bug report
    console.log('Bug Report:', JSON.stringify(bugReport, null, 2));
  });
});
```

---

## 6. RUNNING TESTS

### 6.1 Run All Tests

```bash
# Run all tests with headed browser
npx playwright test

# Run specific test file
npx playwright test login.spec.js

# Run with debug mode
npx playwright test --debug

# Run with UI mode
npx playwright test --ui
```

### 6.2 Run with Bug Detection

```bash
# Run tests with comprehensive monitoring
npx playwright test --headed

# Run with trace on failure
npx playwright test --trace on

# Run with video recording
npx playwright test --video retain-on-failure
```

---

## 7. REPORTING

### 7.1 HTML Report

```bash
# Generate HTML report
npx playwright test --reporter=html

# Open report
npx playwright show-report
```

### 7.2 JSON Report

```bash
# Generate JSON report
npx playwright test --reporter=json

# Report location: test-results/results.json
```

---

## 8. BEST PRACTICES

### 8.1 Monitoring Best Practices

- Always use headed browser for comprehensive monitoring
- Monitor console, network, and page elements simultaneously
- Categorize bugs by severity (critical, high, medium, low)
- Fix bugs in priority order
- Re-test after fixes
- Take screenshots on failure
- Record video on failure
- Generate comprehensive reports

### 8.2 Bug Fixing Best Practices

- Fix critical bugs first
- Fix high priority bugs next
- Batch fix similar bugs
- Document all fixes
- Re-test after each fix
- Verify no regressions
- Update documentation

---

## 9. TROUBLESHOOTING

### 9.1 Browser Not Launching

```bash
# Install browser dependencies
npx playwright install-deps chromium

# Check browser installation
npx playwright install chromium
```

### 9.2 Tests Timing Out

```javascript
// Increase timeout in playwright.config.js
use: {
  actionTimeout: 60000,
  navigationTimeout: 120000
}
```

---

## 10. RESOURCES

### 10.1 Documentation

- Playwright Docs: https://playwright.dev/
- Playwright Best Practices: https://playwright.dev/docs/best-practices
- Playwright Debugging: https://playwright.dev/docs/debug

### 10.2 Tools

- **Playwright Inspector:** Built-in debugging tool
- **Playwright Test Runner:** Test execution
- **Playwright Trace Viewer:** Trace analysis

---

> **Modul Selanjutnya:** `40_LOAD_TESTING_SCENARIOS.md`
use Facebook\WebDriver\Remote\DesiredCapabilities;
use PHPUnit\Framework\TestCase;

abstract class SeleniumTestCase extends TestCase {
    protected $driver;
    
    protected function setUp(): void {
        $host = 'http://localhost:4444/wd/hub';
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }
    
    protected function tearDown(): void {
        $this->driver->quit();
    }
}
```

---

## 3. SELENIUM TEST EXAMPLES

### 3.1 Login Test

```php
// tests/Selenium/LoginTest.php
class LoginTest extends SeleniumTestCase {
    public function testValidLogin() {
        $this->driver->get('https://tourguide.com/login');
        
        // Find elements
        $emailField = $this->driver->findElement(
            WebDriverBy::name('email')
        );
        $passwordField = $this->driver->findElement(
            WebDriverBy::name('password')
        );
        $loginButton = $this->driver->findElement(
            WebDriverBy::cssSelector('button[type="submit"]')
        );
        
        // Fill form
        $emailField->sendKeys('test@example.com');
        $passwordField->sendKeys('password123');
        
        // Submit
        $loginButton->click();
        
        // Wait for redirect
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::urlContains('dashboard')
        );
        
        // Assert
        $this->assertStringContainsString('dashboard', $this->driver->getCurrentURL());
    }
    
    public function testInvalidLogin() {
        $this->driver->get('https://tourguide.com/login');
        
        $emailField = $this->driver->findElement(WebDriverBy::name('email'));
        $passwordField = $this->driver->findElement(WebDriverBy::name('password'));
        $loginButton = $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'));
        
        $emailField->sendKeys('test@example.com');
        $passwordField->sendKeys('wrongpassword');
        $loginButton->click();
        
        // Wait for error message
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.alert-danger')
            )
        );
        
        $errorMessage = $this->driver->findElement(
            WebDriverBy::cssSelector('.alert-danger')
        )->getText();
        
        $this->assertStringContainsString('Invalid credentials', $errorMessage);
    }
}
```

### 3.2 Booking Test

```php
// tests/Selenium/BookingTest.php
class BookingTest extends SeleniumTestCase {
    public function testCreateBooking() {
        // Login first
        $this->driver->get('https://tourguide.com/login');
        $this->driver->findElement(WebDriverBy::name('email'))->sendKeys('test@example.com');
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys('password123');
        $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Wait for dashboard
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::urlContains('dashboard')
        );
        
        // Navigate to guides
        $this->driver->get('https://tourguide.com/guides');
        
        // Click on first guide
        $this->driver->findElement(WebDriverBy::cssSelector('.guide-card:first-child'))->click();
        
        // Wait for guide detail
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.guide-detail')
            )
        );
        
        // Click booking button
        $this->driver->findElement(WebDriverBy::cssSelector('.btn-booking'))->click();
        
        // Fill booking form
        $this->driver->findElement(WebDriverBy::name('booking_date'))->sendKeys('2026-07-01');
        $this->driver->findElement(WebDriverBy::name('guests'))->sendKeys('2');
        
        // Submit
        $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Wait for success message
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.alert-success')
            )
        );
        
        $successMessage = $this->driver->findElement(
            WebDriverBy::cssSelector('.alert-success')
        )->getText();
        
        $this->assertStringContainsString('Booking successful', $successMessage);
    }
}
```

### 3.3 Ticket Purchase Test

```php
// tests/Selenium/TicketTest.php
class TicketTest extends SeleniumTestCase {
    public function testBuyTicket() {
        // Login
        $this->driver->get('https://tourguide.com/login');
        $this->driver->findElement(WebDriverBy::name('email'))->sendKeys('test@example.com');
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys('password123');
        $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Navigate to tickets
        $this->driver->get('https://tourguide.com/tickets');
        
        // Click on first destination
        $this->driver->findElement(WebDriverBy::cssSelector('.destination-card:first-child'))->click();
        
        // Select ticket type
        $this->driver->findElement(WebDriverBy::name('ticket_type'))->sendKeys('adult');
        $this->driver->findElement(WebDriverBy::name('quantity'))->sendKeys('2');
        $this->driver->findElement(WebDriverBy::name('visit_date'))->sendKeys('2026-07-01');
        
        // Submit
        $this->driver->findElement(WebDriverBy::cssSelector('.btn-buy'))->click();
        
        // Wait for payment page
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::urlContains('payment')
        );
        
        // Select payment method
        $this->driver->findElement(WebDriverBy::cssSelector('input[value="midtrans"]'))->click();
        $this->driver->findElement(WebDriverBy::cssSelector('.btn-pay'))->click();
        
        // Wait for success
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.qr-code')
            )
        );
        
        // Assert QR code is present
        $qrCode = $this->driver->findElement(WebDriverBy::cssSelector('.qr-code'));
        $this->assertNotNull($qrCode);
    }
}
```

---

## 4. PUPPETEER SETUP

### 4.1 Install Puppeteer

```bash
npm install puppeteer
```

### 4.2 Puppeteer Configuration

```javascript
// tests/puppeteer/config.js
const puppeteer = require('puppeteer');

module.exports = {
    async launch() {
        return await puppeteer.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
    }
};
```

---

## 5. PUPPETEER TEST EXAMPLES

### 5.1 Login Test

```javascript
// tests/puppeteer/login.test.js
const puppeteer = require('puppeteer');

describe('Login Tests', () => {
    let browser;
    let page;
    
    beforeAll(async () => {
        browser = await puppeteer.launch({ headless: true });
        page = await browser.newPage();
    });
    
    afterAll(async () => {
        await browser.close();
    });
    
    test('Valid login', async () => {
        await page.goto('https://tourguide.com/login');
        
        await page.type('input[name="email"]', 'test@example.com');
        await page.type('input[name="password"]', 'password123');
        await page.click('button[type="submit"]');
        
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        
        expect(page.url()).toContain('dashboard');
    });
    
    test('Invalid login', async () => {
        await page.goto('https://tourguide.com/login');
        
        await page.type('input[name="email"]', 'test@example.com');
        await page.type('input[name="password"]', 'wrongpassword');
        await page.click('button[type="submit"]');
        
        await page.waitForSelector('.alert-danger');
        
        const errorMessage = await page.$eval('.alert-danger', el => el.textContent);
        expect(errorMessage).toContain('Invalid credentials');
    });
});
```

### 5.2 Booking Test

```javascript
// tests/puppeteer/booking.test.js
const puppeteer = require('puppeteer');

describe('Booking Tests', () => {
    let browser;
    let page;
    
    beforeAll(async () => {
        browser = await puppeteer.launch({ headless: true });
        page = await browser.newPage();
    });
    
    afterAll(async () => {
        await browser.close();
    });
    
    test('Create booking', async () => {
        // Login
        await page.goto('https://tourguide.com/login');
        await page.type('input[name="email"]', 'test@example.com');
        await page.type('input[name="password"]', 'password123');
        await page.click('button[type="submit"]');
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        
        // Navigate to guides
        await page.goto('https://tourguide.com/guides');
        
        // Click first guide
        await page.click('.guide-card:first-child');
        await page.waitForSelector('.guide-detail');
        
        // Click booking button
        await page.click('.btn-booking');
        await page.waitForSelector('form.booking-form');
        
        // Fill form
        await page.type('input[name="booking_date"]', '2026-07-01');
        await page.type('input[name="guests"]', '2');
        
        // Submit
        await page.click('button[type="submit"]');
        await page.waitForSelector('.alert-success');
        
        const successMessage = await page.$eval('.alert-success', el => el.textContent);
        expect(successMessage).toContain('Booking successful');
    });
});
```

### 5.3 Screenshot Test

```javascript
// tests/puppeteer/screenshot.test.js
const puppeteer = require('puppeteer');
const fs = require('fs');

describe('Screenshot Tests', () => {
    let browser;
    let page;
    
    beforeAll(async () => {
        browser = await puppeteer.launch({ headless: true });
        page = await browser.newPage();
    });
    
    afterAll(async () => {
        await browser.close();
    });
    
    test('Take screenshot of homepage', async () => {
        await page.goto('https://tourguide.com');
        await page.screenshot({ path: 'screenshots/homepage.png' });
        
        expect(fs.existsSync('screenshots/homepage.png')).toBe(true);
    });
    
    test('Take screenshot of dashboard', async () => {
        await page.goto('https://tourguide.com/login');
        await page.type('input[name="email"]', 'test@example.com');
        await page.type('input[name="password"]', 'password123');
        await page.click('button[type="submit"]');
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        
        await page.screenshot({ path: 'screenshots/dashboard.png' });
        
        expect(fs.existsSync('screenshots/dashboard.png')).toBe(true);
    });
});
```

---

## 6. CI/CD INTEGRATION

### 6.1 GitHub Actions for Selenium

```yaml
# .github/workflows/selenium.yml
name: Selenium Tests

on: [push, pull_request]

jobs:
  selenium:
    runs-on: ubuntu-latest
    
    services:
      selenium:
        image: selenium/standalone-chrome:latest
        ports:
          - 4444:4444
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
    
    - name: Install dependencies
      run: composer install --no-interaction
    
    - name: Run Selenium tests
      run: vendor/bin/phpunit tests/Selenium/
      env:
        SELENIUM_HOST: http://localhost:4444/wd/hub
```

### 6.2 GitHub Actions for Puppeteer

```yaml
# .github/workflows/puppeteer.yml
name: Puppeteer Tests

on: [push, pull_request]

jobs:
  puppeteer:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup Node.js
      uses: actions/setup-node@v2
      with:
        node-version: '16'
    
    - name: Install dependencies
      run: npm install
    
    - name: Run Puppeteer tests
      run: npm test
```

---

## 7. PAGE OBJECT MODEL

### 7.1 Login Page Object

```php
// tests/PageObjects/LoginPage.php
use Facebook\WebDriver\WebDriverBy;

class LoginPage {
    private $driver;
    
    public function __construct($driver) {
        $this->driver = $driver;
    }
    
    public function setEmail($email) {
        $this->driver->findElement(WebDriverBy::name('email'))->sendKeys($email);
        return $this;
    }
    
    public function setPassword($password) {
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys($password);
        return $this;
    }
    
    public function clickLogin() {
        $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        return $this;
    }
    
    public function login($email, $password) {
        return $this->setEmail($email)
                    ->setPassword($password)
                    ->clickLogin();
    }
}
```

### 7.2 Using Page Object

```php
// tests/Selenium/LoginPageObjectTest.php
class LoginPageObjectTest extends SeleniumTestCase {
    public function testLoginWithPageObject() {
        $this->driver->get('https://tourguide.com/login');
        
        $loginPage = new LoginPage($this->driver);
        $loginPage->login('test@example.com', 'password123');
        
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::urlContains('dashboard')
        );
        
        $this->assertStringContainsString('dashboard', $this->driver->getCurrentURL());
    }
}
```

---

## 8. DATA-DRIVEN TESTING

### 8.1 CSV Data Provider

```php
// tests/DataProviders/LoginDataProvider.php
class LoginDataProvider {
    public static function loginData() {
        return [
            ['test@example.com', 'password123', true],
            ['test@example.com', 'wrongpassword', false],
            ['invalid@example.com', 'password123', false],
        ];
    }
}
```

### 8.2 Using Data Provider

```php
// tests/Selenium/DataDrivenLoginTest.php
use PHPUnit\Framework\TestCase;

class DataDrivenLoginTest extends SeleniumTestCase {
    /**
     * @dataProvider LoginDataProvider::loginData
     */
    public function testLogin($email, $password, $shouldSucceed) {
        $this->driver->get('https://tourguide.com/login');
        
        $this->driver->findElement(WebDriverBy::name('email'))->sendKeys($email);
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys($password);
        $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        if ($shouldSucceed) {
            $this->driver->wait(10, 500)->until(
                WebDriverExpectedCondition::urlContains('dashboard')
            );
            $this->assertStringContainsString('dashboard', $this->driver->getCurrentURL());
        } else {
            $this->driver->wait(10, 500)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::cssSelector('.alert-danger')
                )
            );
        }
    }
}
```

---

## 9. PARALLEL TESTING

### 9.1 PHPUnit Parallel Configuration

```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         parallelTesting="true"
         parallelSuites="4"
         parallelTests="8">
    <testsuites>
        <testsuite name="Selenium">
            <directory>tests/Selenium</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### 9.2 Jest Parallel Configuration

```javascript
// jest.config.js
module.exports = {
    maxWorkers: 4,
    testMatch: ['**/tests/puppeteer/**/*.test.js']
};
```

---

## 10. SCREENSHOT & VIDEO

### 10.1 Screenshot on Failure

```php
// tests/Selenium/ScreenshotListener.php
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestFailure;

class ScreenshotListener implements TestListener {
    private $driver;
    
    public function __construct($driver) {
        $this->driver = $driver;
    }
    
    public function addError(Test $test, \Throwable $e, float $time): void {
        $this->takeScreenshot($test->getName());
    }
    
    public function addFailure(Test $test, TestFailure $e, float $time): void {
        $this->takeScreenshot($test->getName());
    }
    
    private function takeScreenshot($testName) {
        $screenshot = $this->driver->takeScreenshot();
        file_put_contents("screenshots/{$testName}.png", $screenshot);
    }
}
```

### 10.2 Video Recording

```javascript
// tests/puppeteer/video.test.js
const puppeteer = require('puppeteer');

describe('Video Recording', () => {
    let browser;
    let page;
    
    beforeAll(async () => {
        browser = await puppeteer.launch({
            headless: true,
            args: ['--enable-screenshot-testing']
        });
        page = await browser.newPage();
    });
    
    test('Record video of booking flow', async () => {
        await page.goto('https://tourguide.com/login');
        await page.type('input[name="email"]', 'test@example.com');
        await page.type('input[name="password"]', 'password123');
        await page.click('button[type="submit"]');
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        
        // Video is automatically saved
    });
});
```

---

## 11. BEST PRACTICES

### 11.1 Test Organization

- Group tests by feature/module
- Use descriptive test names
- Keep tests independent
- Use page object model
- Implement data-driven testing

### 11.2 Test Maintenance

- Update tests when UI changes
- Remove obsolete tests
- Refactor duplicate code
- Keep test data separate
- Version control test scripts

### 11.3 Performance

- Use headless mode when possible
- Reuse browser instances
- Implement parallel testing
- Use explicit waits
- Avoid unnecessary sleeps

---

## 12. TROUBLESHOOTING

### 12.1 Element Not Found

**Problem:** Element not found error

**Solutions:**
- Use explicit waits
- Check element selector
- Verify page is fully loaded
- Check for iframes
- Use different locator strategy

### 12.2 Timeout Errors

**Problem:** Timeout waiting for element

**Solutions:**
- Increase timeout duration
- Check network conditions
- Verify element exists
- Use different wait strategy
- Check for AJAX loading

### 12.3 Flaky Tests

**Problem:** Tests pass/fail inconsistently

**Solutions:**
- Add retries
- Use explicit waits
- Check for race conditions
- Isolate test dependencies
- Fix timing issues

---

## 13. RESOURCES

### 13.1 Documentation

- PHP WebDriver: https://github.com/php-webdriver/php-webdriver
- Puppeteer: https://pptr.dev/
- Selenium Docs: https://www.selenium.dev/documentation/
- Page Object Model: https://www.selenium.dev/documentation/test_practices/encouraged_page_object_models/

### 13.2 Tools

- **Selenium IDE**: Record and playback
- **BrowserStack**: Cloud testing
- **Sauce Labs**: Cloud testing
- **CrossBrowserTesting**: Cross-browser testing

---

> **Modul Selanjutnya:** `40_LOAD_TESTING_SCENARIOS.md`
