import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

test.describe('API Tests', () => {
  test('should get destinations API', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/getDestinations`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data).toHaveProperty('status', 'success');
    expect(data).toHaveProperty('data');
    expect(Array.isArray(data.data)).toBeTruthy();
  });

  test('should get tour guides API', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/getTourGuides`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data).toHaveProperty('status', 'success');
    expect(data).toHaveProperty('data');
    expect(Array.isArray(data.data)).toBeTruthy();
  });

  test('should get hotels API', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/getHotels`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data).toHaveProperty('status', 'success');
    expect(data).toHaveProperty('data');
    expect(Array.isArray(data.data)).toBeTruthy();
  });

  test('should get restaurants API', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/getRestaurants`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data).toHaveProperty('status', 'success');
    expect(data).toHaveProperty('data');
    expect(Array.isArray(data.data)).toBeTruthy();
  });

  test('should get events API', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/getEvents`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data).toHaveProperty('status', 'success');
    expect(data).toHaveProperty('data');
    expect(Array.isArray(data.data)).toBeTruthy();
  });

  test('should search API', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/search?q=Jakarta`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data).toHaveProperty('status', 'success');
    expect(data).toHaveProperty('data');
  });
});
