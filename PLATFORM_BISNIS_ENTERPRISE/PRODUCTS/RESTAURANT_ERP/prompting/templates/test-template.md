# Test Template

## Purpose

Template for creating comprehensive tests for RESTAURANT_ERP modules.

## REASONS Canvas

### R - Requirements

**Testing Requirements:**
- Test type: [Unit/Integration/E2E]
- Module: [MODULE_NAME]
- Coverage target: >80%
- Test framework: [PHPUnit/Playwright]

### E - Entities

**Test Entities:**
- Test data fixtures
- Mock objects
- Test users
- Test tenants

### A - Approach

**Testing Strategy:**
1. Unit tests for business logic
2. Integration tests for API endpoints
3. E2E tests for user flows
4. Security tests for authentication/authorization
5. Performance tests for critical paths

### S - Structure

**Test File Structure:**
```
BACKEND/tests/
├── unit/
│   └── [MODULE_NAME]ServiceTest.php
├── integration/
│   └── [MODULE_NAME]ApiTest.php
└── e2e/
    └── [MODULE_NAME].spec.ts
```

### O - Operations

**Testing Steps:**

1. **Create Unit Tests**
   - Test all service methods
   - Test validation logic
   - Test business rules
   - Mock dependencies

2. **Create Integration Tests**
   - Test all API endpoints
   - Test authentication
   - Test authorization
   - Test error handling

3. **Create E2E Tests**
   - Test user flows
   - Test UI interactions
   - Test responsive design
   - Test cross-browser

### N - Norms

**Testing Standards:**
- AAA pattern (Arrange, Act, Assert)
- Descriptive test names
- One assertion per test
- Test both success and failure
- Use test fixtures

### S - Safeguards

**Non-negotiable Rules:**
- MUST test all public methods
- MUST test all API endpoints
- MUST test authentication
- MUST test authorization
- MUST test error cases
- MUST achieve >80% coverage

## Unit Test Template

Create file: `BACKEND/tests/unit/[MODULE_NAME]ServiceTest.php`

```php
<?php

use PHPUnit\Framework\TestCase;

class [MODULE_NAME]ServiceTest extends TestCase
{
    private $service;
    private $mockRepository;
    private $mockDb;
    
    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock([MODULE_NAME]Repository::class);
        $this->mockDb = $this->createMock(Database::class);
        $this->service = new [MODULE_NAME]Service($this->mockRepository, $this->mockDb);
    }
    
    public function testGetAllSuccess()
    {
        // Arrange
        $tenantId = 1;
        $expectedData = [['id' => 1, 'name' => 'Test']];
        $this->mockRepository->method('findAll')
            ->with($tenantId)
            ->willReturn($expectedData);
        
        // Act
        $result = $this->service->getAll($tenantId);
        
        // Assert
        $this->assertEquals($expectedData, $result);
    }
    
    public function testGetByIdSuccess()
    {
        // Arrange
        $id = 1;
        $tenantId = 1;
        $expectedData = ['id' => 1, 'name' => 'Test'];
        $this->mockRepository->method('findById')
            ->with($id, $tenantId)
            ->willReturn($expectedData);
        
        // Act
        $result = $this->service->getById($id, $tenantId);
        
        // Assert
        $this->assertEquals($expectedData, $result);
    }
    
    public function testCreateSuccess()
    {
        // Arrange
        $data = ['name' => 'Test'];
        $tenantId = 1;
        $userId = 1;
        $expectedId = 1;
        
        $this->mockRepository->method('create')
            ->willReturn($expectedId);
        
        $this->mockDb->method('connect')
            ->willReturn($this->createMock(PDO::class));
        
        // Act
        $result = $this->service->create($data, $tenantId, $userId);
        
        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals($expectedId, $result['data']['id']);
    }
    
    public function testCreateValidationError()
    {
        // Arrange
        $data = []; // Invalid data
        $tenantId = 1;
        $userId = 1;
        
        // Act
        $result = $this->service->create($data, $tenantId, $userId);
        
        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
    }
    
    public function testUpdateSuccess()
    {
        // Arrange
        $id = 1;
        $data = ['name' => 'Updated'];
        $tenantId = 1;
        $userId = 1;
        
        $this->mockRepository->method('update')
            ->willReturn(true);
        
        $this->mockDb->method('connect')
            ->willReturn($this->createMock(PDO::class));
        
        // Act
        $result = $this->service->update($id, $data, $tenantId, $userId);
        
        // Assert
        $this->assertTrue($result['success']);
    }
    
    public function testDeleteSuccess()
    {
        // Arrange
        $id = 1;
        $tenantId = 1;
        $userId = 1;
        
        $this->mockRepository->method('delete')
            ->willReturn(true);
        
        $this->mockDb->method('connect')
            ->willReturn($this->createMock(PDO::class));
        
        // Act
        $result = $this->service->delete($id, $tenantId, $userId);
        
        // Assert
        $this->assertTrue($result['success']);
    }
}
```

## Integration Test Template

Create file: `BACKEND/tests/integration/[MODULE_NAME]ApiTest.php`

```php
<?php

use PHPUnit\Framework\TestCase;

class [MODULE_NAME]ApiTest extends TestCase
{
    private $client;
    private $token;
    private $baseUrl = 'http://localhost/api/v1';
    
    protected function setUp(): void
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => $this->baseUrl]);
        $this->token = $this->login();
    }
    
    private function login(): string
    {
        $response = $this->client->post('/auth/login', [
            'json' => [
                'username' => 'admin',
                'password' => 'admin123'
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        return $data['data']['access_token'];
    }
    
    public function testIndex()
    {
        $response = $this->client->get('/[module]', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }
    
    public function testIndexUnauthorized()
    {
        $response = $this->client->get('/[module]', [
            'headers' => ['Authorization' => 'Bearer invalid_token']
        ]);
        
        $this->assertEquals(401, $response->getStatusCode());
    }
    
    public function testShow()
    {
        $response = $this->client->get('/[module]/1', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }
    
    public function testShowNotFound()
    {
        $response = $this->client->get('/[module]/99999', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);
        
        $this->assertEquals(404, $response->getStatusCode());
    }
    
    public function testStore()
    {
        $response = $this->client->post('/[module]', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'name' => 'Test',
                'status' => 'ACTIVE'
            ]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }
    
    public function testStoreValidationError()
    {
        $response = $this->client->post('/[module]', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [] // Invalid data
        ]);
        
        $this->assertEquals(422, $response->getStatusCode());
    }
    
    public function testUpdate()
    {
        $response = $this->client->put('/[module]/1', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'name' => 'Updated'
            ]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }
    
    public function testDelete()
    {
        $response = $this->client->delete('/[module]/1', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }
}
```

## E2E Test Template

Create file: `BACKEND/tests/e2e/[MODULE_NAME].spec.ts`

```typescript
import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8000';
const API_BASE = 'http://localhost:8000/api/v1';

test.describe('[MODULE_NAME] E2E Tests', () => {
  let token: string;

  test.beforeAll(async ({ request }) => {
    // Login and get token
    const loginResponse = await request.post(`${API_BASE}/auth/login`, {
      data: {
        username: 'admin',
        password: 'admin123'
      }
    });
    const loginData = await loginResponse.json();
    token = loginData.data.access_token;
  });

  test('List all items', async ({ request }) => {
    const response = await request.get(`${API_BASE}/[module]`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
  });

  test('View single item', async ({ request }) => {
    const response = await request.get(`${API_BASE}/[module]/1`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
  });

  test('Create new item', async ({ request }) => {
    const response = await request.post(`${API_BASE}/[module]`, {
      headers: { 'Authorization': `Bearer ${token}` },
      data: {
        name: 'Test Item',
        status: 'ACTIVE'
      }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
  });

  test('Update item', async ({ request }) => {
    const response = await request.put(`${API_BASE}/[module]/1`, {
      headers: { 'Authorization': `Bearer ${token}` },
      data: {
        name: 'Updated Item'
      }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
  });

  test('Delete item', async ({ request }) => {
    const response = await request.delete(`${API_BASE}/[module]/1`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
  });

  test('Unauthorized access', async ({ request }) => {
    const response = await request.get(`${API_BASE}/[module]`, {
      headers: { 'Authorization': 'Bearer invalid_token' }
    });

    expect(response.status()).toBe(401);
  });

  test('Complete user flow', async ({ page }) => {
    // Navigate to application
    await page.goto(BASE_URL);
    
    // Login
    await page.fill('[name="username"]', 'admin');
    await page.fill('[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    
    // Navigate to module
    await page.click('[data-testid="[module]-menu"]');
    
    // Create new item
    await page.click('[data-testid="create-button"]');
    await page.fill('[name="name"]', 'Test Item');
    await page.click('[data-testid="save-button"]');
    
    // Verify success
    await expect(page.locator('[data-testid="success-message"]')).toBeVisible();
  });
});
```

## Running Tests

**Unit Tests:**
```bash
cd BACKEND/tests
phpunit unit/[MODULE_NAME]ServiceTest.php
```

**Integration Tests:**
```bash
cd BACKEND/tests
phpunit integration/[MODULE_NAME]ApiTest.php
```

**E2E Tests:**
```bash
cd BACKEND/tests
npx playwright test e2e/[MODULE_NAME].spec.ts
```

**All Tests:**
```bash
cd BACKEND/tests
phpunit
npx playwright test
```

**Coverage Report:**
```bash
phpunit --coverage-html coverage/
```

## Test Checklist

### Unit Tests
- [ ] All service methods tested
- [ ] Validation logic tested
- [ ] Business rules tested
- [ ] Success cases tested
- [ ] Error cases tested
- [ ] Edge cases tested

### Integration Tests
- [ ] All GET endpoints tested
- [ ] All POST endpoints tested
- [ ] All PUT endpoints tested
- [ ] All DELETE endpoints tested
- [ ] Authentication tested
- [ ] Authorization tested
- [ ] Error responses tested

### E2E Tests
- [ ] User flows tested
- [ ] UI interactions tested
- [ ] Responsive design tested
- [ ] Cross-browser tested

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
