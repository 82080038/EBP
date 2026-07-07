# Cycle 4: Testing Phase

## Purpose

Generate comprehensive tests for implemented code. Create unit tests, integration tests, and E2E tests using Playwright.

## REASONS Canvas

### R - Requirements

**Testing Requirements:**
- Test all implemented functionality
- Cover happy paths and edge cases
- Test security controls
- Verify tenant isolation
- Test error handling
- Validate Indonesian/English messages

**Testing DoD:**
- Unit tests for all service methods
- Integration tests for API endpoints
- E2E tests for critical user flows
- Security tests for authentication/authorization
- Performance tests for critical endpoints
- Test coverage > 80%
- All tests passing

### E - Entities

**Test Entities:**
- Test data fixtures
- Mock objects for external dependencies
- Test users with different roles
- Test tenants for multi-tenant verification
- Sample data for each entity type

**Test Scenarios:**
- CRUD operations for each entity
- Permission checks for each role
- Validation rules for each input
- Error cases for each endpoint
- Edge cases for business logic

### A - Approach

**Testing Strategy:**
1. **Unit Tests** - Test individual methods in isolation
2. **Integration Tests** - Test API endpoints with database
3. **E2E Tests** - Test complete user flows with Playwright
4. **Security Tests** - Test authentication, authorization, input validation
5. **Performance Tests** - Test response times and load

**Test Framework:**
- Unit/Integration: PHPUnit
- E2E: Playwright (TypeScript)
- Security: Manual + automated checks
- Performance: Playwright metrics

### S - Structure

**Test Structure:**
```
BACKEND/tests/
├── unit/
│   ├── Services/
│   │   └── [ModuleName]ServiceTest.php
│   └── Repositories/
│       └── [ModuleName]RepositoryTest.php
├── integration/
│   └── [ModuleName]ApiTest.php
└── e2e/
    └── [ModuleName].spec.ts
```

**Test File Pattern:**

**Unit Test (PHPUnit):**
```php
<?php

use PHPUnit\Framework\TestCase;

class [ModuleName]ServiceTest extends TestCase
{
    private $service;
    private $mockRepository;
    
    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock([ModuleName]Repository::class);
        $this->service = new [ModuleName]Service($this->mockRepository);
    }
    
    public function testCreateSuccess()
    {
        // Arrange
        $data = ['field' => 'value'];
        
        // Act
        $result = $this->service->create($data, 1, 1);
        
        // Assert
        $this->assertTrue($result['success']);
    }
    
    public function testCreateValidationError()
    {
        // Test validation failure
    }
}
```

**Integration Test (PHPUnit):**
```php
<?php

class [ModuleName]ApiTest extends TestCase
{
    private $client;
    private $token;
    
    protected function setUp(): void
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => 'http://localhost/api/v1']);
        $this->token = $this->login();
    }
    
    public function testIndex()
    {
        $response = $this->client->get('/[module]', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

**E2E Test (Playwright):**
```typescript
import { test, expect } from '@playwright/test';

test.describe('[ModuleName] E2E Tests', () => {
  test('Complete flow', async ({ page }) => {
    // Navigate
    await page.goto('http://localhost:8000');
    
    // Login
    await page.fill('[name="username"]', 'admin');
    await page.fill('[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    
    // Navigate to module
    await page.click('[data-testid="[module]-menu"]');
    
    // Perform action
    await page.click('[data-testid="create-button"]');
    await page.fill('[name="field"]', 'value');
    await page.click('[data-testid="save-button"]');
    
    // Verify
    await expect(page.locator('[data-testid="success-message"]')).toBeVisible();
  });
});
```

### O - Operations

**Testing Steps:**

1. **Unit Testing**
   - Test all service methods
   - Test repository methods
   - Test validation logic
   - Test business rules
   - Mock external dependencies

2. **Integration Testing**
   - Test all API endpoints
   - Test authentication
   - Test authorization
   - Test tenant isolation
   - Test error responses
   - Test validation errors

3. **E2E Testing**
   - Test critical user flows
   - Test multi-step processes
   - Test responsive design
   - Test cross-browser compatibility
   - Test mobile/tablet views

4. **Security Testing**
   - Test authentication bypass
   - Test authorization bypass
   - Test SQL injection
   - Test XSS vulnerabilities
   - Test CSRF protection
   - Test rate limiting

5. **Performance Testing**
   - Measure response times
   - Test under load
   - Identify bottlenecks
   - Verify pagination
   - Check query performance

### N - Norms

**Testing Standards:**
- Follow AAA pattern (Arrange, Act, Assert)
- Use descriptive test names
- One assertion per test (when possible)
- Test both success and failure cases
- Mock external dependencies
- Use test data fixtures
- Clean up test data after tests

**Test Naming:**
- Unit tests: `test[MethodName][Scenario]`
- Integration tests: `test[Endpoint][Method][Scenario]`
- E2E tests: `[Feature][Scenario]`

**Test Data Standards:**
- Use realistic test data
- Include edge cases
- Test with invalid data
- Test with boundary values
- Test with empty/null values

### S - Safeguards

**Non-negotiable Testing Rules:**
- MUST test all public methods
- MUST test all API endpoints
- MUST test authentication for all protected endpoints
- MUST test authorization for each role
- MUST test tenant isolation
- MUST test validation rules
- MUST test error handling
- MUST achieve >80% code coverage

**Security Testing Rules:**
- MUST test authentication bypass attempts
- MUST test authorization bypass attempts
- MUST test SQL injection attempts
- MUST test XSS attempts
- MUST test CSRF protection
- MUST test rate limiting

**Performance Testing Rules:**
- MUST measure response times
- MUST test under expected load
- MUST identify slow queries
- MUST verify pagination works
- MUST test with large datasets

## Testing Checklist

### Unit Tests
- [ ] All service methods tested
- [ ] All repository methods tested
- [ ] Validation logic tested
- [ ] Business rules tested
- [ ] Error cases tested
- [ ] Edge cases tested
- [ ] Mock dependencies used

### Integration Tests
- [ ] All GET endpoints tested
- [ ] All POST endpoints tested
- [ ] All PUT endpoints tested
- [ ] All DELETE endpoints tested
- [ ] Authentication tested
- [ ] Authorization tested
- [ ] Tenant isolation tested
- [ ] Validation errors tested
- [ ] Error responses tested

### E2E Tests
- [ ] Critical user flows tested
- [ ] Multi-step processes tested
- [ ] Responsive design tested
- [ ] Mobile view tested
- [ ] Tablet view tested
- [ ] Desktop view tested

### Security Tests
- [ ] Authentication bypass tested
- [ ] Authorization bypass tested
- [ ] SQL injection tested
- [ ] XSS tested
- [ ] CSRF tested
- [ ] Rate limiting tested

### Performance Tests
- [ ] Response times measured
- [ ] Load testing performed
- [ ] Slow queries identified
- [ ] Pagination verified
- [ ] Large dataset tested

## Test Execution

**Run Unit Tests:**
```bash
cd BACKEND/tests
phpunit unit/
```

**Run Integration Tests:**
```bash
cd BACKEND/tests
phpunit integration/
```

**Run E2E Tests:**
```bash
cd BACKEND/tests
npx playwright test e2e/
```

**Run All Tests:**
```bash
cd BACKEND/tests
phpunit
npx playwright test
```

**Generate Coverage Report:**
```bash
phpunit --coverage-html coverage/
```

## Output Format

After testing, produce:

1. **Test Files**
   - Unit test files
   - Integration test files
   - E2E test files

2. **Test Results**
   - Test execution report
   - Coverage report
   - Performance metrics
   - Security test results

3. **Issue Report**
   - Failed tests
   - Coverage gaps
   - Performance issues
   - Security vulnerabilities

## Next Steps

After completing testing:
1. Review test results
2. Fix any failing tests
3. Address coverage gaps
4. Proceed to Integration Phase (05-integration.md)

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
