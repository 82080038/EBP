const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

describe('Dashboard Tests', () => {
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
    await page.goto(config.baseUrl + '/dashboard.php');
    await page.waitForSelector('h1', { timeout: 5000 });
  });

  test('should display dashboard', async () => {
    const h1 = await page.$eval('h1', el => el.textContent);
    expect(h1).toContain('Dashboard');
  });

  test('should display statistics cards', async () => {
    const cards = await page.$$('.card');
    expect(cards.length).toBeGreaterThan(0);
  });

  test('should display recent activities', async () => {
    await page.waitForSelector('.table', { timeout: 5000 });
    const table = await page.$('.table');
    expect(table).not.toBeNull();
  });

  test('should navigate to nasabah page', async () => {
    await page.click('a[href="pages/nasabah/index.php"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    expect(page.url()).toContain('pages/nasabah/index.php');
  });

  test('should navigate to pinjaman page', async () => {
    await page.click('a[href="pages/pinjaman/index.php"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    expect(page.url()).toContain('pages/pinjaman/index.php');
  });

  test('should navigate to angsuran page', async () => {
    await page.click('a[href="pages/angsuran/index.php"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    expect(page.url()).toContain('pages/angsuran/index.php');
  });

  test('should display user information in navbar', async () => {
    const navbarText = await page.$eval('.navbar-text', el => el.textContent);
    expect(navbarText).toContain('Administrator');
  });
});
