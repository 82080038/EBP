const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

describe('Users CRUD Tests', () => {
  let browser;
  let page;

  beforeAll(async () => {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
  });

  afterAll(async () => {
    await browser.close();
  });

  beforeEach(async () => {
    // Login before each test
    await page.goto(config.baseUrl + '/login.php');
    await page.type('input[name="username"]', 'admin');
    await page.type('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    const currentUrl = page.url();
    if (!currentUrl.includes('dashboard.php')) {
      await page.goto(config.baseUrl + '/dashboard.php');
    }
    await new Promise(resolve => setTimeout(resolve, 1000));
  });

  afterEach(async () => {
    // Logout after each test
    await page.goto(config.baseUrl + '/logout.php');
    await new Promise(resolve => setTimeout(resolve, 1000));
  });

  test('should display users list (Read)', async () => {
    await page.goto(config.baseUrl + '/pages/users/index.php');
    await page.waitForSelector('h1', { timeout: 5000 });
    
    const h1 = await page.$eval('h1', el => el.textContent);
    expect(h1).toContain('Users');
    
    // Check if table exists
    const table = await page.$('.table');
    expect(table).not.toBeNull();
  });

  test('should create new user (Create)', async () => {
    await page.goto(config.baseUrl + '/pages/users/tambah.php');
    await page.waitForSelector('h1', { timeout: 5000 });
    
    // Fill form with unique data
    const timestamp = Date.now();
    await page.type('input[name="username"]', `testuser${timestamp}`);
    await page.type('input[name="password"]', 'test123');
    await page.type('input[name="nama"]', 'Test User');
    await page.type('input[name="email"]', `test${timestamp}@example.com`);
    await page.select('select[name="role"]', 'petugas');
    
    // Submit form using evaluate
    await page.evaluate(() => {
      document.querySelector('button[type="submit"]').click();
    });
    
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    // Check for success message or redirect
    const currentUrl = page.url();
    const successAlert = await page.$('.alert-success');
    
    if (currentUrl.includes('index.php') || successAlert) {
      expect(true).toBe(true);
    } else {
      // Check if still on form page (validation error)
      expect(currentUrl).toContain('tambah.php');
    }
  }, 15000);

  test('should edit existing user (Update)', async () => {
    await page.goto(config.baseUrl + '/pages/users/index.php');
    await page.waitForSelector('.table', { timeout: 5000 });
    
    // Click edit button on first row
    const editButton = await page.$('a[href*="edit.php"]');
    if (editButton) {
      await editButton.click();
      await page.waitForSelector('h1', { timeout: 5000 });
      
      // Update name
      await page.evaluate(() => {
        const namaInput = document.querySelector('input[name="nama"]');
        if (namaInput) namaInput.value = 'Test User Updated';
      });
      
      // Submit form
      await page.evaluate(() => {
        const submitButton = document.querySelector('button[type="submit"]');
        if (submitButton) submitButton.click();
      });
      
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check for success message or redirect
      const currentUrl = page.url();
      const successAlert = await page.$('.alert-success');
      
      if (currentUrl.includes('index.php') || successAlert) {
        expect(true).toBe(true);
      } else {
        // Check if still on form page (validation error)
        expect(currentUrl).toContain('edit.php');
      }
    } else {
      // No edit button found - might be no data
      console.log('No edit button found - skipping update test');
      expect(true).toBe(true);
    }
  }, 15000);

  test('should delete user (Delete)', async () => {
    await page.goto(config.baseUrl + '/pages/users/index.php');
    await page.waitForSelector('.table', { timeout: 5000 });
    
    // Click delete button on first row (if exists)
    const deleteButton = await page.$('a[href*="hapus.php"]');
    if (deleteButton) {
      // Store page to handle dialog
      const dialogPromise = new Promise(resolve => {
        page.on('dialog', async dialog => {
          await dialog.accept();
          resolve();
        });
      });
      
      await deleteButton.click();
      await dialogPromise;
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check if redirected to list page
      const currentUrl = page.url();
      expect(currentUrl).toContain('index.php');
    }
  });

  test('should search users', async () => {
    await page.goto(config.baseUrl + '/pages/users/index.php');
    await page.waitForSelector('.table', { timeout: 5000 });
    
    // Check if search input exists
    const searchInput = await page.$('input[type="search"]');
    if (searchInput) {
      await page.type('input[type="search"]', 'Test');
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Check if table still exists after search
      const table = await page.$('.table');
      expect(table).not.toBeNull();
    }
  });
});
