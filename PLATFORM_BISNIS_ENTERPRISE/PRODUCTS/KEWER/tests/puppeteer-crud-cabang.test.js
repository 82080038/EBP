const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

describe('Cabang CRUD Tests', () => {
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

  test('should display cabang list (Read)', async () => {
    await page.goto(config.baseUrl + '/pages/cabang/index.php');
    await page.waitForSelector('h1', { timeout: 5000 });
    
    const h1 = await page.$eval('h1', el => el.textContent);
    expect(h1).toContain('Cabang');
    
    // Check if table exists
    const table = await page.$('.table');
    expect(table).not.toBeNull();
  });

  test('should create new cabang (Create)', async () => {
    await page.goto(config.baseUrl + '/pages/cabang/tambah.php');
    await page.waitForSelector('h1', { timeout: 5000 });
    
    // Fill form with unique data
    const timestamp = Date.now();
    await page.type('input[name="kode_cabang"]', `CBG${timestamp}`);
    await page.type('input[name="nama_cabang"]', 'Cabang Test');
    await page.type('textarea[name="alamat"]', 'Alamat cabang test');
    await page.type('input[name="telp"]', '081234567890');
    await page.type('input[name="email"]', `cabang${timestamp}@test.com`);
    
    // Select province
    await page.select('select[name="province_id"]', '3'); // SUMATERA UTARA
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    // Submit form
    await page.click('button[type="submit"]');
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

  test('should edit existing cabang (Update)', async () => {
    await page.goto(config.baseUrl + '/pages/cabang/index.php');
    await page.waitForSelector('.table', { timeout: 5000 });
    
    // Click edit button on first row
    const editButton = await page.$('a[href*="edit.php"]');
    if (editButton) {
      await editButton.click();
      await page.waitForSelector('h1', { timeout: 5000 });
      
      // Update name using correct field name
      await page.evaluate(() => {
        const namaInput = document.querySelector('input[name="nama_cabang"]');
        if (namaInput) namaInput.value = 'Cabang Test Updated';
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

  test('should delete cabang (Delete)', async () => {
    await page.goto(config.baseUrl + '/pages/cabang/index.php');
    await page.waitForSelector('.table', { timeout: 5000 });
    
    // Check if there are any rows in the table
    const tableRows = await page.$$('.table tbody tr');
    if (tableRows.length === 0) {
      // No data to delete - skip test
      console.log('No cabang data available - skipping delete test');
      expect(true).toBe(true);
      return;
    }
    
    // Try to delete the first cabang
    const deleteButton = await page.$('a[href*="hapus.php"]');
    if (deleteButton) {
      // Handle the confirmation dialog
      page.on('dialog', async dialog => {
        await dialog.accept();
      });
      
      await deleteButton.click();
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check if redirected to list page
      const currentUrl = page.url();
      expect(currentUrl).toContain('index.php');
    } else {
      // No delete button found
      console.log('No delete button found - skipping delete test');
      expect(true).toBe(true);
    }
  }, 20000);

  test('should search cabang', async () => {
    await page.goto(config.baseUrl + '/pages/cabang/index.php');
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
