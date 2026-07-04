---
description: Run tests for KEWER microfinance platform
---

# Run Tests - KEWER

## Test Setup

KEWER uses Playwright for E2E testing. Tests are located in the `tests/` directory.

## Prerequisites

- Node.js installed
- Playwright installed: `npm install -D @playwright/test`
- Browsers installed: `npx playwright install`

## Run All Tests

```bash
npx playwright test
```

## Run Specific Test File

```bash
npx playwright test tests/login.spec.ts
```

## Run Tests in Headed Mode

```bash
npx playwright test --headed
```

## Run Tests with UI

```bash
npx playwright test --ui
```

## View Test Report

After running tests, view the HTML report:
```bash
npx playwright show-report
```

## Test Categories

### Login Tests
- Test login for all 9 roles
- Test quick login (development mode)
- Test session management

### CRUD Tests
- Nasabah management
- Pinjaman management
- Angsuran management
- Pembayaran management
- User management

### Feature Tests
- Credit scoring
- GPS tracking
- Webhook system
- Multi-branch sync
- Dashboard analytics

### Integration Tests
- Cross-database operations
- API endpoints
- Cron job functionality

## Test Data

Test users are defined in the database with default credentials (see README.md).

## Troubleshooting

### Browser Not Found
```bash
npx playwright install
```

### Port Already in Use
Ensure XAMPP Apache is running on port 80.

### Database Connection Failed
Ensure MySQL/MariaDB is running and databases are imported.
