const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

describe('Address Dropdown Tests', () => {
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
    await page.goto(config.baseUrl + '/pages/nasabah/tambah.php');
  });

  test('should display province dropdown', async () => {
    const provinceSelect = await page.$('select[name="province_id"]');
    expect(provinceSelect).not.toBeNull();
  });

  test('should display regency dropdown', async () => {
    const regencySelect = await page.$('select[name="regency_id"]');
    expect(regencySelect).not.toBeNull();
  });

  test('should display district dropdown', async () => {
    const districtSelect = await page.$('select[name="district_id"]');
    expect(districtSelect).not.toBeNull();
  });

  test('should display village dropdown', async () => {
    const villageSelect = await page.$('select[name="village_id"]');
    expect(villageSelect).not.toBeNull();
  });

  test('should load provinces on page load', async () => {
    await page.waitForSelector('select[name="province_id"] option', { timeout: 5000 });
    const options = await page.$$('select[name="province_id"] option');
    expect(options.length).toBeGreaterThan(0);
  });

  test('should have SUMATERA UTARA as a province option', async () => {
    await page.waitForSelector('select[name="province_id"] option', { timeout: 5000 });
    const options = await page.$$eval('select[name="province_id"] option', opts => 
      opts.map(opt => opt.textContent)
    );
    expect(options).toContain('SUMATERA UTARA');
  });

  test('should load regencies when province is selected', async () => {
    await page.waitForSelector('select[name="province_id"]', { timeout: 5000 });
    await page.select('select[name="province_id"]', '3'); // SUMATERA UTARA ID
    
    // Wait for regencies to load
    await page.waitForTimeout(1000);
    
    const regencyOptions = await page.$$('select[name="regency_id"] option');
    expect(regencyOptions.length).toBeGreaterThan(1); // Should have more than just default option
  });

  test('should load districts when regency is selected', async () => {
    await page.waitForSelector('select[name="province_id"]', { timeout: 5000 });
    await page.select('select[name="province_id"]', '3');
    await page.waitForTimeout(1000);
    
    // Select first regency
    await page.select('select[name="regency_id"]', '1');
    await page.waitForTimeout(1000);
    
    const districtOptions = await page.$$('select[name="district_id"] option');
    expect(districtOptions.length).toBeGreaterThan(1);
  });

  test('should load villages when district is selected', async () => {
    await page.waitForSelector('select[name="province_id"]', { timeout: 5000 });
    await page.select('select[name="province_id"]', '3');
    await page.waitForTimeout(1000);
    
    await page.select('select[name="regency_id"]', '1');
    await page.waitForTimeout(1000);
    
    await page.select('select[name="district_id"]', '1');
    await page.waitForTimeout(1000);
    
    const villageOptions = await page.$$('select[name="village_id"] option');
    expect(villageOptions.length).toBeGreaterThan(1);
  });
});
