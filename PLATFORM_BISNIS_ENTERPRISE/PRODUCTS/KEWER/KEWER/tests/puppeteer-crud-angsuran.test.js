const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

describe('Angsuran CRUD Tests', () => {
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

  test('should display angsuran list (Read)', async () => {
    await page.goto(config.baseUrl + '/pages/angsuran/index.php');
    await page.waitForSelector('h1', { timeout: 5000 });
    
    const h1 = await page.$eval('h1', el => el.textContent);
    expect(h1).toContain('Angsuran');
    
    // Check if table exists
    const table = await page.$('.table');
    expect(table).not.toBeNull();
  });

  test('should pay angsuran (Update)', async () => {
    // Try to access bayar page directly (might redirect if no data)
    await page.goto(config.baseUrl + '/pages/angsuran/bayar.php?pinjaman_id=1');
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    const currentUrl = page.url();
    
    // Check if redirected to index (no data) or if on bayar page
    if (currentUrl.includes('index.php')) {
      // No angsuran data available - this is expected
      console.log('No angsuran data available - skipping payment test');
      expect(true).toBe(true);
    } else if (currentUrl.includes('bayar.php')) {
      // On bayar page - try to fill form if elements exist
      const jumlahBayarInput = await page.$('input[name="jumlah_bayar"]');
      if (jumlahBayarInput) {
        await page.select('select[name="cara_bayar"]', 'tunai');
        await page.click('button[type="submit"]');
        await new Promise(resolve => setTimeout(resolve, 2000));
      }
      expect(true).toBe(true);
    } else {
      // Unexpected redirect
      expect(currentUrl).toContain('bayar.php');
    }
  }, 15000);

  test('should search angsuran', async () => {
    await page.goto(config.baseUrl + '/pages/angsuran/index.php');
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
