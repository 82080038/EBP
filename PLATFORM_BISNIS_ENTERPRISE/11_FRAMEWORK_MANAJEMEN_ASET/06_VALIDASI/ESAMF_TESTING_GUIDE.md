# ESAMF Testing Guide

**Document ID:** ESAMF-VALIDATION-002

**Version:** 1.0

**Purpose:** Define the testing methodology for ESAMF

---

# Overview

The Testing Guide provides a comprehensive methodology for testing migrated components to ensure they meet EBP standards and function correctly.

---

# Testing Strategy

## Test Pyramid

```
         /\
        /  \
       / E2E \      10% - End-to-End Tests
      /--------\
     / Integration\  30% - Integration Tests
    /--------------\
   /   Unit Tests   \  60% - Unit Tests
  /------------------\
```

## Testing Levels

### Level 1: Unit Tests

**Purpose**: Test individual functions and methods in isolation

**Characteristics**:
- Fast execution (< 1 second per test)
- No external dependencies
- Test specific functionality
- High coverage (> 80%)

### Level 2: Integration Tests

**Purpose**: Test interactions between components

**Characteristics**:
- Moderate execution (< 10 seconds per test)
- External dependencies mocked or test database
- Test component interactions
- Medium coverage (> 60%)

### Level 3: End-to-End Tests

**Purpose**: Test complete user flows

**Characteristics**:
- Slower execution (< 1 minute per test)
- Real external dependencies
- Test user workflows
- Low coverage (critical paths only)

---

# Unit Testing

## Test Structure

### Test Class Structure

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use EBP\Core\Authentication\AuthService;

class AuthServiceTest extends TestCase
{
    private $authService;
    private $mockDb;
    private $mockConfig;
    
    protected function setUp(): void
    {
        $this->mockDb = $this->createMock(DatabaseInterface::class);
        $this->mockConfig = $this->createMock(ConfigServiceInterface::class);
        $this->authService = new AuthService($this->mockConfig, $this->mockDb);
    }
    
    protected function tearDown(): void
    {
        $this->authService = null;
        $this->mockDb = null;
        $this->mockConfig = null;
    }
    
    // Test methods
}
```

### Test Method Structure

```php
public function testLoginWithValidCredentials()
{
    // Arrange
    $username = 'testuser';
    $password = 'password123';
    $expectedUser = new User(['id' => 1, 'username' => $username]);
    
    $this->mockDb->expects($this->once())
                 ->method('fetch')
                 ->willReturn($expectedUser);
    
    // Act
    $result = $this->authService->login($username, $password);
    
    // Assert
    $this->assertTrue($result->success);
    $this->assertNotNull($result->token);
    $this->assertEquals($expectedUser->id, $result->user->id);
}
```

## Test Categories

### Happy Path Tests

Test the expected behavior with valid inputs.

```php
public function testLoginWithValidCredentials()
{
    // Test successful login
}
```

### Edge Case Tests

Test boundary conditions and edge cases.

```php
public function testLoginWithEmptyUsername()
{
    // Test with empty username
}

public function testLoginWithVeryLongUsername()
{
    // Test with very long username
}
```

### Error Path Tests

Test error conditions and exceptions.

```php
public function testLoginWithInvalidCredentials()
{
    // Test with invalid credentials
}

public function testLoginWithLockedAccount()
{
    // Test with locked account
}
```

---

# Integration Testing

## Database Integration Tests

### Test Database Setup

```php
<?php

namespace Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    private $orderService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }
    
    public function testCreateOrder()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        $user = User::create($userData);
        
        $orderData = [
            'user_id' => $user->id,
            'total' => 100.00
        ];
        
        // Act
        $order = $this->orderService->createOrder($orderData);
        
        // Assert
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
            'total' => 100.00
        ]);
    }
}
```

## API Integration Tests

### API Test Structure

```php
<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderApiIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function testCreateOrderEndpoint()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $this->authService->generateToken($user);
        
        $orderData = [
            'total' => 100.00,
            'items' => [
                ['product_id' => 1, 'quantity' => 2]
            ]
        ];
        
        // Act
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                        ->postJson('/api/v1/orders', $orderData);
        
        // Assert
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'total',
                         'items'
                     ],
                     'meta'
                 ]);
    }
}
```

## EBP Service Integration Tests

### EBP Core Integration Test

```php
<?php

namespace Tests\Integration;

use Tests\TestCase;
use EBP\Core\Authentication\AuthServiceInterface;
use EBP\Core\Audit\AuditServiceInterface;

class EBPIntegrationTest extends TestCase
{
    public function testAuthenticationIntegration()
    {
        $authService = $this->app->make(AuthServiceInterface::class);
        
        $result = $authService->login('testuser', 'password');
        
        $this->assertTrue($result->success);
        $this->assertNotNull($result->token);
    }
    
    public function testAuditIntegration()
    {
        $auditService = $this->app->make(AuditServiceInterface::class);
        
        $auditService->log('test_action', [
            'entity_type' => 'test',
            'entity_id' => 1
        ]);
        
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'test_action'
        ]);
    }
}
```

---

# End-to-End Testing

## E2E Test Structure

### Using Cypress

```javascript
// cypress/integration/order_flow.spec.js

describe('Order Flow', () => {
  beforeEach(() => {
    cy.login('testuser', 'password');
  });
  
  it('should create an order successfully', () => {
    cy.visit('/orders');
    
    cy.get('[data-cy="create-order-button"]').click();
    
    cy.get('[data-cy="product-select"]').select('Product 1');
    cy.get('[data-cy="quantity-input"]').type('2');
    
    cy.get('[data-cy="submit-button"]').click();
    
    cy.get('[data-cy="order-success-message"]').should('be.visible');
    cy.get('[data-cy="order-id"]').should('contain', 'Order #');
  });
  
  it('should display order in list', () => {
    cy.visit('/orders');
    
    cy.get('[data-cy="order-list"]').should('contain', 'Order #');
  });
});
```

### Using Playwright

```javascript
// tests/e2e/order_flow.spec.js

const { test, expect } = require('@playwright/test');

test('should create an order successfully', async ({ page }) => {
  await page.goto('/login');
  await page.fill('[name="username"]', 'testuser');
  await page.fill('[name="password"]', 'password');
  await page.click('[type="submit"]');
  
  await page.goto('/orders');
  await page.click('[data-cy="create-order-button"]');
  
  await page.selectOption('[data-cy="product-select"]', '1');
  await page.fill('[data-cy="quantity-input"]', '2');
  
  await page.click('[data-cy="submit-button"]');
  
  await expect(page.locator('[data-cy="order-success-message"]')).toBeVisible();
  await expect(page.locator('[data-cy="order-id"]')).toContainText('Order #');
});
```

---

# Test Coverage

## Coverage Targets

| Type | Target | Minimum |
|------|--------|---------|
| Unit Tests | 90% | 80% |
| Integration Tests | 70% | 60% |
| E2E Tests | Critical Paths | N/A |
| Overall | 85% | 75% |

## Coverage Measurement

### Using PHPUnit

```bash
# Generate coverage report
phpunit --coverage-html coverage --coverage-text
```

### Using Jest

```bash
# Generate coverage report
npm test -- --coverage
```

### Using Xdebug

```bash
# Enable Xdebug
export XDEBUG_MODE=coverage

# Run tests
phpunit --coverage-html coverage
```

---

# Test Data Management

## Factories

### Laravel Factory Example

```php
<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;
    
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'total' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'completed_at' => now(),
            ];
        });
    }
}
```

## Fixtures

### Fixture Example

```php
<?php

namespace Tests\Fixtures;

class OrderFixtures
{
    public static function validOrderData()
    {
        return [
            'user_id' => 1,
            'total' => 100.00,
            'items' => [
                ['product_id' => 1, 'quantity' => 2]
            ]
        ];
    }
    
    public static function invalidOrderData()
    {
        return [
            'user_id' => null,
            'total' => -100,
            'items' => []
        ];
    }
}
```

---

# Test Best Practices

## Unit Test Best Practices

1. **Test One Thing**: Each test should test one specific behavior
2. **Arrange-Act-Assert**: Use AAA pattern for clarity
3. **Descriptive Names**: Use descriptive test method names
4. **Independent Tests**: Tests should not depend on each other
5. **Fast Execution**: Unit tests should be fast
6. **No External Dependencies**: Mock external dependencies

## Integration Test Best Practices

1. **Test Interactions**: Focus on component interactions
2. **Use Test Database**: Use separate test database
4. **Clean Up**: Clean up test data after each test
5. **Realistic Data**: Use realistic test data
6. **Test Boundaries**: Test integration boundaries

## E2E Test Best Practices

1. **Test Critical Paths**: Focus on critical user flows
2. **Real Browser**: Use real browser for testing
3. **Page Objects**: Use page object pattern
4. **Wait Strategies**: Use proper wait strategies
5. **Isolation**: Tests should be isolated
6. **Flaky Tests**: Avoid flaky tests

---

# Continuous Testing

## CI/CD Integration

### GitHub Actions Example

```yaml
# .github/workflows/tests.yml

name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: secret
          MYSQL_DATABASE: test_db
        ports:
          - 3306:3306
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, mysql
    
    - name: Install Dependencies
      run: composer install --no-progress --no-interaction
    
    - name: Run Unit Tests
      run: vendor/bin/phpunit --testsuite=Unit --coverage-clover=coverage.xml
    
    - name: Run Integration Tests
      run: vendor/bin/phpunit --testsuite=Integration
    
    - name: Upload Coverage
      uses: codecov/codecov-action@v2
      with:
        file: ./coverage.xml
```

---

# Document End

**Document ID:** ESAMF-VALIDATION-002

**Version:** 1.0
