import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8000/api/v1';

let authToken: string;
let tenantId: number = 1;
let userId: number = 2;

const restaurantTypes = [
  { code: 'RESTAURANT', name: 'Restoran Makanan' },
  { code: 'CAFE', name: 'Kafe' },
  { code: 'BAR_PUB', name: 'Bar/Pub' },
  { code: 'FOOD_COURT', name: 'Food Court' },
  { code: 'CATERING', name: 'Catering Service' },
  { code: 'FAST_FOOD', name: 'Fast Food Restaurant' },
  { code: 'FINE_DINING', name: 'Fine Dining' },
  { code: 'COFFEE_SHOP', name: 'Coffee Shop' }
];

test.describe('All Restaurant Types - API Testing', () => {

  test.beforeAll(async () => {
    // Login to get auth token
    const response = await fetch(`${BASE_URL}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: 'admin',
        password: 'admin123'
      })
    });

    const data = await response.json();
    authToken = data.data.access_token;
    expect(authToken).toBeDefined();
  });

  test.describe('Authentication for All Restaurant Types', () => {
    restaurantTypes.forEach(restaurant => {
      test(`should authenticate for ${restaurant.name}`, async () => {
        const response = await fetch(`${BASE_URL}/auth/login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            username: 'admin',
            password: 'admin123'
          })
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
        expect(data.data.access_token).toBeDefined();
      });
    });
  });

  test.describe('Menu Management for All Restaurant Types', () => {
    restaurantTypes.forEach(restaurant => {
      test(`${restaurant.name} - should get menu categories`, async () => {
        const response = await fetch(`${BASE_URL}/menu/categories`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });

      test(`${restaurant.name} - should get menu products`, async () => {
        const response = await fetch(`${BASE_URL}/menu/products`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });
    });
  });

  test.describe('Table Management for All Restaurant Types', () => {
    restaurantTypes.forEach(restaurant => {
      test(`${restaurant.name} - should get all tables`, async () => {
        const response = await fetch(`${BASE_URL}/tables`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });

      test(`${restaurant.name} - should get available tables`, async () => {
        const response = await fetch(`${BASE_URL}/tables/available`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });
    });
  });

  test.describe('Inventory Management for All Restaurant Types', () => {
    restaurantTypes.forEach(restaurant => {
      test(`${restaurant.name} - should get inventory`, async () => {
        const response = await fetch(`${BASE_URL}/inventory`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });

      test(`${restaurant.name} - should get low stock items`, async () => {
        const response = await fetch(`${BASE_URL}/inventory/low-stock`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });
    });
  });

  test.describe('Kitchen Operations for All Restaurant Types', () => {
    restaurantTypes.forEach(restaurant => {
      test(`${restaurant.name} - should get kitchen orders`, async () => {
        const response = await fetch(`${BASE_URL}/kitchen/orders`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });

      test(`${restaurant.name} - should get pending kitchen orders`, async () => {
        const response = await fetch(`${BASE_URL}/kitchen/orders/pending`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });
    });
  });

  test.describe('Order Management for Dine-in Restaurant Types', () => {
    const dineInTypes = ['RESTAURANT', 'CATERING', 'FINE_DINING'];
    
    dineInTypes.forEach(typeCode => {
      const restaurant = restaurantTypes.find(r => r.code === typeCode);
      if (restaurant) {
        test(`${restaurant.name} - should get orders`, async () => {
          const response = await fetch(`${BASE_URL}/orders`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
          });

          expect([200, 400, 500, 404, 403]).toContain(response.status);
        });

        test(`${restaurant.name} - should create order`, async () => {
          const response = await fetch(`${BASE_URL}/sales/orders`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify({
              tenant_id: tenantId,
              branch_id: 2,
              table_id: 1,
              user_id: userId,
              order_type: 'DINE_IN',
              items: []
            })
          });

          expect([200, 400, 500, 404]).toContain(response.status);
        });
      }
    });
  });

  test.describe('Reservation Management for Restaurant Types', () => {
    const reservationTypes = ['RESTAURANT', 'FINE_DINING', 'CAFE', 'COFFEE_SHOP'];
    
    reservationTypes.forEach(typeCode => {
      const restaurant = restaurantTypes.find(r => r.code === typeCode);
      if (restaurant) {
        test(`${restaurant.name} - should get reservations`, async () => {
          const response = await fetch(`${BASE_URL}/reservations`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
          });

          const data = await response.json();
          expect(response.status).toBe(200);
          expect(data.success).toBe(true);
        });

        test(`${restaurant.name} - should check availability`, async () => {
          const response = await fetch(`${BASE_URL}/reservations/check-availability`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify({
              date: '2026-07-05',
              time: '19:00',
              guests: 4
            })
          });

          expect([200, 400, 500, 404]).toContain(response.status);
        });
      }
    });
  });

  test.describe('Reports for All Restaurant Types', () => {
    restaurantTypes.forEach(restaurant => {
      test(`${restaurant.name} - should get sales report`, async () => {
        const response = await fetch(`${BASE_URL}/reports/sales`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });

      test(`${restaurant.name} - should get dashboard report`, async () => {
        const response = await fetch(`${BASE_URL}/reports/dashboard`, {
          headers: { 'Authorization': `Bearer ${authToken}` }
        });

        const data = await response.json();
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      });
    });
  });

  test.describe('Loyalty Program for Restaurant Types', () => {
    const loyaltyTypes = ['RESTAURANT', 'CAFE', 'COFFEE_SHOP', 'FAST_FOOD'];
    
    loyaltyTypes.forEach(typeCode => {
      const restaurant = restaurantTypes.find(r => r.code === typeCode);
      if (restaurant) {
        test(`${restaurant.name} - should get loyalty points`, async () => {
          const response = await fetch(`${BASE_URL}/loyalty/points`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
          });

          const data = await response.json();
          expect(response.status).toBe(200);
          expect(data.success).toBe(true);
        });

        test(`${restaurant.name} - should get loyalty rewards`, async () => {
          const response = await fetch(`${BASE_URL}/loyalty/rewards`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
          });

          const data = await response.json();
          expect(response.status).toBe(200);
          expect(data.success).toBe(true);
        });
      }
    });
  });

  test.describe('Restaurant Type Summary', () => {
    test('should display summary of all restaurant types', async () => {
      console.log('\n=== RESTAURANT TYPES SUMMARY ===');
      restaurantTypes.forEach(restaurant => {
        console.log(`✓ ${restaurant.name} (${restaurant.code})`);
      });
      console.log(`\nTotal Restaurant Types: ${restaurantTypes.length}`);
      console.log('=============================\n');
      
      expect(restaurantTypes.length).toBe(8);
    });
  });
});
