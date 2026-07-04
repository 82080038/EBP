const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

describe('API Integration Tests', () => {
  let browser;
  let page;

  beforeAll(async () => {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
  });

  afterAll(async () => {
    await browser.close();
  });

  test('should authenticate via API', async () => {
    const response = await page.evaluate(async (baseUrl) => {
      const res = await fetch(baseUrl + '/api/auth.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          username: 'admin',
          password: 'password'
        })
      });
      return await res.json();
    }, config.baseUrl);

    expect(response).toHaveProperty('success', true);
  });

  test('should get dashboard data via API', async () => {
    const response = await page.evaluate(async (baseUrl) => {
      const res = await fetch(baseUrl + '/api/dashboard.php?cabang_id=1', {
        method: 'GET',
        headers: {
          'Authorization': 'Bearer kewer-api-token-2024',
          'Content-Type': 'application/json',
        }
      });
      return await res.json();
    }, config.baseUrl);

    expect(response).toHaveProperty('total_nasabah');
    expect(response).toHaveProperty('total_pinjaman');
  });

  test('should get nasabah data via API', async () => {
    const response = await page.evaluate(async (baseUrl) => {
      const res = await fetch(baseUrl + '/api/nasabah.php?cabang_id=1', {
        method: 'GET',
        headers: {
          'Authorization': 'Bearer kewer-api-token-2024',
          'Content-Type': 'application/json',
        }
      });
      return await res.json();
    }, config.baseUrl);

    expect(Array.isArray(response.data)).toBe(true);
  });

  test('should get pinjaman data via API', async () => {
    const response = await page.evaluate(async (baseUrl) => {
      const res = await fetch(baseUrl + '/api/pinjaman.php?cabang_id=1', {
        method: 'GET',
        headers: {
          'Authorization': 'Bearer kewer-api-token-2024',
          'Content-Type': 'application/json',
        }
      });
      return await res.json();
    }, config.baseUrl);

    expect(Array.isArray(response.data)).toBe(true);
  });

  test('should return 401 for unauthorized API requests', async () => {
    const response = await page.evaluate(async (baseUrl) => {
      const res = await fetch(baseUrl + '/api/dashboard.php?cabang_id=1', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        }
      });
      return await res.json();
    }, config.baseUrl);

    expect(response).toHaveProperty('error');
  });
});
