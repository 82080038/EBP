---
description: Run EBP Restaurant backend tests using Playwright
---

# Run Tests Workflow

This workflow runs the Playwright tests for the EBP Restaurant backend.

## Prerequisites

- Node.js installed
- Playwright browsers installed
- PHP development server running on port 8000
- Database configured and seeded

## Steps

### Windows (XAMPP)

1. **Start XAMPP services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Navigate to backend directory**
   ```bash
   cd C:\xampp\htdocs\EBP\PLATFORM_BISNIS_ENTERPRISE\PRODUCTS\RESTAURANT_ERP\BACKEND
   ```

3. **Install dependencies** (if not already installed)
   ```bash
   npm install
   npx playwright install chromium
   ```

4. **Start PHP development server** (in separate terminal)
   ```bash
   php -S localhost:8000 -t public
   ```

5. **Run all tests** (in another terminal)
   ```bash
   npm test
   ```

6. **Run tests with headed browser** (for debugging)
   ```bash
   npm run test:headed
   ```

7. **Run tests with UI mode** (interactive)
   ```bash
   npm run test:ui
   ```

8. **View test report**
   ```bash
   npm run test:report
   ```

### Linux (XAMPP/LAMP)

1. **Start XAMPP services** (if not already running)
   ```bash
   echo "8208" | sudo -S /opt/lampp/lampp start
   ```

2. **Navigate to backend directory**
   ```bash
   cd /opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND
   ```

3. **Install dependencies** (if not already installed)
   ```bash
   npm install
   npx playwright install chromium
   ```

4. **Start PHP development server** (in background)
   ```bash
   php -S localhost:8000 -t public &
   ```

5. **Run all tests**
   ```bash
   npm test
   ```

6. **Run tests with headed browser** (for debugging)
   ```bash
   npm run test:headed
   ```

7. **Run tests with UI mode** (interactive)
   ```bash
   npm run test:ui
   ```

8. **View test report**
   ```bash
   npm run test:report
   ```

9. **Stop the server**
   ```bash
   pkill -f "php -S localhost:8000"
   ```

## Test Files

- `tests/api.spec.ts` - API endpoint tests
- `tests/responsive.spec.ts` - Responsive design tests
- `tests/screenshots.spec.ts` - Screenshot tests
