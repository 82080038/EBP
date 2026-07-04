const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

describe('Pinjaman Management Tests', () => {
  let browser;
  let page;

  beforeAll(async () => {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    // Login first
    await page.goto(config.baseUrl + '/login.php');
    await page.type('input[name="username"]', 'admin');
    await page.type('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
  });

  afterAll(async () => {
    await browser.close();
  });

  beforeEach(async () => {
    await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
    await page.waitForSelector('table', { timeout: 5000 });
  });

  test('should display pinjaman list page', async () => {
    const h1 = await page.$eval('h1', el => el.textContent);
    expect(h1).toContain('Pinjaman');
  });

  test('should display pinjaman table', async () => {
    const table = await page.$('.table');
    expect(table).not.toBeNull();
  });

  test('should display add pinjaman button', async () => {
    const addButton = await page.$('a[href*="tambah.php"]');
    expect(addButton).not.toBeNull();
  });

  test('should navigate to add pinjaman page', async () => {
    await page.click('a[href*="tambah.php"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    expect(page.url()).toContain('tambah.php');
  });

  test('should display search functionality', async () => {
    const searchInput = await page.$('input[name="search"]');
    expect(searchInput).not.toBeNull();
  });

  test('should display status filter', async () => {
    const statusFilter = await page.$('select[name="status"]');
    expect(statusFilter).not.toBeNull();
  });

  test('should filter pinjaman by status', async () => {
    await page.select('select[name="status"]', 'aktif');
    await page.waitForTimeout(1000);
    const table = await page.$('.table');
    expect(table).not.toBeNull();
  });

  test('should display pinjaman details', async () => {
    const firstRow = await page.$('table tbody tr:first-child');
    if (firstRow) {
      const detailButton = await firstRow.$('a[href*="detail.php"]');
      if (detailButton) {
        await detailButton.click();
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        expect(page.url()).toContain('detail.php');
      }
    }
  });
});
