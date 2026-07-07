# RESTAURANT_ERP Testing Guide

**Complete testing guide for the RESTAURANT_ERP application**

---

## Overview

This project has **3 types of testing**:
1. **Unit Tests** (PHPUnit) - Test individual components
2. **E2E Tests** (Playwright) - Test complete user flows
3. **Integration Tests** - Test module interactions

---

## 1. Unit Testing (PHPUnit)

### Setup
PHPUnit is already configured in `phpunit.xml` with test suites for:
- Core Components (Router, JWT, Database, Response)
- Business Engines (StockEngine, KitchenEngine, AccountingEngine)
- Middleware (Auth, Permission, Tenant, ErrorHandler)

### Run Unit Tests
```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND

# Run all unit tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite "Core Components"
./vendor/bin/phpunit --testsuite "Business Engines"
./vendor/bin/phpunit --testsuite "Middleware"

# Run specific test file
./vendor/bin/phpunit tests/unit/core/RouterTest.php
./vendor/bin/phpunit tests/unit/core/JWTTest.php

# Run with coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

### Unit Test Files
- `tests/unit/core/RouterTest.php` - Router functionality
- `tests/unit/core/JWTTest.php` - JWT encoding/decoding
- `tests/unit/core/ResponseTest.php` - Response helpers
- `tests/unit/core/DatabaseTest.php` - Database connection
- `tests/unit/engines/StockEngineTest.php` - Stock engine logic
- `tests/unit/engines/KitchenEngineTest.php` - Kitchen order logic
- `tests/unit/engines/AccountingEngineTest.php` - Accounting logic

---

## 2. E2E Testing (Playwright)

### Setup
Playwright is configured in `playwright.config.ts` with:
- Test directory: `tests/e2e/`
- Base URL: `http://localhost:8000`
- Browser: Chromium (can be changed)
- Viewport: 1280x720

### Run E2E Tests
```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND

# Run all E2E tests (headless)
npm test

# Run E2E tests with browser UI
npm run test:headed

# Run E2E tests with Playwright UI (interactive)
npm run test:ui

# View test report
npm run test:report
```

### E2E Test Structure
Tests are organized by module and role:
- `tests/e2e/auth/` - Authentication tests
- `tests/e2e/menu/` - Menu management tests
- `tests/e2e/orders/` - Order management tests
- `tests/e2e/kitchen/` - Kitchen operations tests
- `tests/e2e/inventory/` - Inventory management tests
- `tests/e2e/reservations/` - Reservation tests

### Test Users
| Username | Password | Role |
|----------|----------|------|
| admin | password | Administrator |
| manager | password | Restaurant Manager |
| waiter | password | Waiter |
| kitchen | password | Kitchen Staff |
| cashier | password | Cashier |
| inventory | password | Inventory Manager |
| host | password | Host/Hostess |

---

## 3. Integration Testing

### Database Setup
```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND

# Setup test database
php setup_database.php

# Seed test data
php seed_data.php
```

### Run Integration Tests
```bash
# Run API integration tests
./vendor/bin/phpunit tests/integration/

# Run specific integration test
./vendor/bin/phpunit tests/integration/MenuIntegrationTest.php
```

---

## 4. Test by Module

### Authentication Module
```bash
# Unit tests
./vendor/bin/phpunit tests/unit/middleware/AuthMiddlewareTest.php

# E2E tests
npm test -- tests/e2e/auth/
```

### Menu Module
```bash
# Unit tests
./vendor/bin/phpunit tests/unit/modules/Menu/

# E2E tests
npm test -- tests/e2e/menu/
```

### Order Module
```bash
# Unit tests
./vendor/bin/phpunit tests/unit/modules/Orders/

# E2E tests
npm test -- tests/e2e/orders/
```

### Kitchen Module
```bash
# Unit tests
./vendor/bin/phpunit tests/unit/engines/KitchenEngineTest.php

# E2E tests
npm test -- tests/e2e/kitchen/
```

### Inventory Module
```bash
# Unit tests
./vendor/bin/phpunit tests/unit/modules/Inventory/

# E2E tests
npm test -- tests/e2e/inventory/
```

---

## 5. Role-Based Testing

### Administrator Tests
```bash
# Test all admin features
npm test -- tests/e2e/admin/
```

### Manager Tests
```bash
# Test all manager features
npm test -- tests/e2e/manager/
```

### Waiter Tests
```bash
# Test all waiter features
npm test -- tests/e2e/waiter/
```

### Kitchen Staff Tests
```bash
# Test all kitchen features
npm test -- tests/e2e/kitchen/
```

---

## 6. Running All Tests

### Complete Test Suite
```bash
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND

# Run all tests (unit + E2E)
./vendor/bin/phpunit && npm test

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/ && npm test
```

### Quick Test (Critical Path)
```bash
# Run only critical tests
./vendor/bin/phpunit --testsuite "Core Components"
npm test -- tests/e2e/auth/ tests/e2e/orders/
```

---

## 7. Test Reports

### View Test Results
```bash
# PHPUnit HTML report
open coverage/index.html

# Playwright HTML report
npm run test:report
```

### Existing Test Reports
- `TEST_REPORT.md` - Previous E2E test results (156 tests, 100% passed)
- `TEST_RESULTS_SUMMARY.md` - Detailed test summary
- `COMPREHENSIVE_TEST_PLAN.md` - Complete test plan document

---

## 8. Continuous Testing

### Watch Mode (Development)
```bash
# PHPUnit watch mode (requires phpunit-watcher)
./vendor/bin/phpunit-watcher watch

# Playwright watch mode
npm test -- --watch
```

### Pre-Commit Testing
```bash
# Run quick tests before commit
./vendor/bin/phpunit --testsuite "Core Components"
```

---

## 9. Troubleshooting

### Common Issues

**PHPUnit not found**
```bash
composer install
```

**Playwright not found**
```bash
npm install
npx playwright install
```

**Database connection error**
```bash
# Check database connection
php setup_database.php

# Verify environment variables
cat .env
```

**Port 8000 already in use**
```bash
# Kill process on port 8000
sudo lsof -ti:8000 | xargs kill -9

# Or use different port
php -S localhost:8001
```

---

## 10. Test Coverage Goals

### Target Coverage
- **Core Components**: 90%+
- **Business Engines**: 85%+
- **Controllers**: 80%+
- **Services**: 85%+
- **Overall**: 80%+

### Check Coverage
```bash
./vendor/bin/phpunit --coverage-text
./vendor/bin/phpunit --coverage-html coverage/
```

---

## 11. Best Practices

1. **Run tests before committing** - Always run unit tests before committing code
2. **Test in isolation** - Each test should be independent
3. **Use descriptive names** - Test names should describe what they test
4. **Mock external dependencies** - Don't rely on external services in tests
5. **Clean up after tests** - Ensure test data is cleaned up
6. **Test edge cases** - Don't just test happy paths
7. **Keep tests fast** - Unit tests should run in seconds, not minutes

---

## 12. Quick Reference

```bash
# Quick test commands
cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND

# Unit tests
./vendor/bin/phpunit

# E2E tests
npm test

# All tests
./vendor/bin/phpunit && npm test

# View reports
npm run test:report
open coverage/index.html
```

---

**Last Updated**: 2026-07-05
**Test Framework**: PHPUnit 12.5.30 + Playwright 1.61.1
