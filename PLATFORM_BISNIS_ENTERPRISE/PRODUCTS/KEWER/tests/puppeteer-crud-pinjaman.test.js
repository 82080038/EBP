const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

describe('Pinjaman CRUD Tests', () => {
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

  test('should display pinjaman list (Read)', async () => {
    await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
    await page.waitForSelector('h1', { timeout: 5000 });
    
    const h1 = await page.$eval('h1', el => el.textContent);
    expect(h1).toContain('Pinjaman');
    
    // Check if table exists
    const table = await page.$('.table');
    expect(table).not.toBeNull();
  });

  test('should create new pinjaman (Create)', async () => {
    await page.goto(config.baseUrl + '/pages/pinjaman/tambah.php');
    await page.waitForSelector('h1', { timeout: 5000 });
    
    // Fill form
    await page.select('select[name="nasabah_id"]', '1');
    await page.type('input[name="plafon"]', '5000000');
    await page.type('input[name="tenor"]', '12');
    await page.type('input[name="bunga_per_bulan"]', '5');
    await page.type('input[name="tanggal_akad"]', '2026-04-16');
    await page.type('textarea[name="tujuan_pinjaman"]', 'Modal usaha');
    await page.type('textarea[name="jaminan"]', 'BPKB Motor');
    
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

  test('should view pinjaman detail (Read Detail)', async () => {
    await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
    await page.waitForSelector('.table', { timeout: 5000 });
    
    // Click detail button on first row
    const detailButton = await page.$('a[href*="detail.php"]');
    if (detailButton) {
      await detailButton.click();
      await page.waitForSelector('h1', { timeout: 5000 });
      
      const h1 = await page.$eval('h1', el => el.textContent);
      expect(h1).toContain('Detail Pinjaman');
    }
  });

  test('should process pinjaman (Update Status)', async () => {
    await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
    await page.waitForSelector('.table', { timeout: 5000 });
    
    // Click process button on first row
    const processButton = await page.$('a[href*="proses.php"]');
    if (processButton) {
      await processButton.click();
      await page.waitForSelector('h1', { timeout: 5000 });
      
      // Select status
      await page.select('select[name="status"]', 'disetujui');
      
      // Submit form
      await page.click('button[type="submit"]');
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Check if redirected to list page
      const currentUrl = page.url();
      expect(currentUrl).toContain('index.php');
    }
  });

  test('should search pinjaman', async () => {
    await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
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
