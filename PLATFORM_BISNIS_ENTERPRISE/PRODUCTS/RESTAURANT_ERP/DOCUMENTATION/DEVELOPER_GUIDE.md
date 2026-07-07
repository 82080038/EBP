# RESTAURANT_ERP Developer Guide

**Version**: 1.0  
**Last Updated**: July 7, 2026

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Development Environment Setup](#development-environment-setup)
3. [Project Structure](#project-structure)
4. [Backend Development](#backend-development)
5. [Frontend Development](#frontend-development)
6. [Database Schema](#database-schema)
7. [API Development](#api-development)
8. [Testing](#testing)
9. [Deployment](#deployment)
10. [Contributing Guidelines](#contributing-guidelines)

---

## Architecture Overview

### System Architecture

RESTAURANT_ERP follows a multi-tier architecture:

```
┌─────────────────────────────────────────────────────────┐
│                    Frontend Layer                       │
│  (Dashboard, Kiosk, Mobile, Consumer Interfaces)        │
└──────────────────────┬──────────────────────────────────┘
                       │ HTTP/REST API
┌──────────────────────▼──────────────────────────────────┐
│                   API Gateway Layer                      │
│         (Authentication, Rate Limiting, Routing)         │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                  Application Layer                       │
│     (Controllers, Services, Business Logic)              │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                   Data Access Layer                      │
│            (Repositories, Models, ORM)                    │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                   Database Layer                         │
│              (MySQL, Multi-tenant Schema)                │
└─────────────────────────────────────────────────────────┘
```

### Technology Stack

**Backend:**
- **Language**: PHP 8.1+
- **Framework**: Custom MVC Framework
- **Database**: MySQL 8.0+
- **Web Server**: Apache/Nginx
- **Authentication**: JWT (JSON Web Tokens)

**Frontend:**
- **Language**: JavaScript (ES6+)
- **Styling**: CSS3
- **Build Tools**: None (vanilla JS)
- **Icons**: Emoji-based (lightweight)

**Testing:**
- **Unit Tests**: PHPUnit
- **Integration Tests**: PHPUnit
- **E2E Tests**: Playwright

---

## Development Environment Setup

### Prerequisites

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Apache or Nginx
- Composer (for PHP dependencies)
- Node.js 16+ (for E2E tests)
- Git

### Local Setup

1. **Clone the Repository**
```bash
git clone https://github.com/ebp-restaurant/restaurant-erp.git
cd restaurant-erp
```

2. **Install PHP Dependencies**
```bash
cd BACKEND
composer install
```

3. **Configure Database**
```bash
# Create database
mysql -u root -p
CREATE DATABASE ebp_restaurant_db;
```

4. **Update Configuration**
```php
// BACKEND/config/database.php
return [
    'host' => 'localhost',
    'dbname' => 'ebp_restaurant_db',
    'username' => 'ebp_app',
    'password' => 'ebp_secure_password_2026',
    'charset' => 'utf8mb4'
];
```

5. **Run Migrations**
```bash
php BACKEND/migrations/migrate.php
```

6. **Start Development Server**
```bash
# Using PHP built-in server
php -S localhost:8000 -t BACKEND/public

# Or using Apache
# Configure virtual host to point to BACKEND/public
```

7. **Access Frontend**
```
http://localhost/FRONTEND/dashboard/
```

### IDE Configuration

**Recommended IDEs:**
- VS Code
- PHPStorm
- Sublime Text

**VS Code Extensions:**
- PHP Intelephense
- PHP DocBlocker
- ESLint
- Prettier

---

## Project Structure

```
RESTAURANT_ERP/
├── BACKEND/
│   ├── config/              # Configuration files
│   ├── core/                # Core framework classes
│   │   ├── Database.php
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   └── Model.php
│   ├── modules/             # Feature modules
│   │   ├── Auth/           # Authentication module
│   │   │   ├── Controllers/
│   │   │   ├── Services/
│   │   │   ├── Repositories/
│   │   │   └── Models/
│   │   ├── Order/          # Order management
│   │   ├── Payment/        # Payment processing
│   │   ├── Menu/           # Menu management
│   │   ├── Inventory/      # Inventory management
│   │   ├── Kitchen/        # Kitchen operations
│   │   ├── Customer/       # Customer management
│   │   ├── Consumer/       # Consumer management
│   │   ├── Analytics/      # Analytics & reporting
│   │   ├── AI/             # AI & predictive analytics
│   │   └── ...             # Other modules
│   ├── public/             # Public web root
│   │   ├── index.php       # Entry point
│   │   └── assets/         # Static assets
│   ├── routes/             # API routes
│   │   └── api.php
│   ├── tests/              # Test suite
│   │   ├── unit/           # Unit tests
│   │   ├── integration/    # Integration tests
│   │   └── e2e/            # E2E tests
│   └── migrations/         # Database migrations
├── FRONTEND/
│   ├── dashboard/          # Admin dashboard
│   ├── kiosk/              # Self-service kiosk
│   ├── mobile/             # Mobile waiter app
│   ├── consumer/           # Consumer app
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript modules
│   │   ├── api-client.js   # API client
│   │   ├── auth-manager.js # Authentication manager
│   │   ├── ai-dashboard.js # AI dashboard
│   │   └── ...
│   └── index.html          # Landing page
└── DOCUMENTATION/          # Documentation
    ├── API_DOCUMENTATION.md
    ├── USER_GUIDE.md
    └── DEVELOPER_GUIDE.md
```

---

## Backend Development

### Creating a New Module

1. **Create Module Directory**
```bash
mkdir BACKEND/modules/YourModule
```

2. **Create Subdirectories**
```bash
cd BACKEND/modules/YourModule
mkdir Controllers Services Repositories Models
```

3. **Create Repository**
```php
<?php
// BACKEND/modules/YourModule/Repositories/YourModuleRepository.php

class YourModuleRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll($tenantId, $limit = 10, $offset = 0)
    {
        $sql = "SELECT * FROM your_table 
                WHERE tenant_id = ? 
                AND deleted_at IS NULL 
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id, $tenantId)
    {
        $sql = "SELECT * FROM your_table 
                WHERE id = ? AND tenant_id = ? 
                AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO your_table (tenant_id, field1, field2, created_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['field1'],
            $data['field2']
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data, $tenantId)
    {
        $sql = "UPDATE your_table 
                SET field1 = ?, field2 = ?, updated_at = NOW() 
                WHERE id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['field1'],
            $data['field2'],
            $id,
            $tenantId
        ]);
    }

    public function delete($id, $tenantId)
    {
        $sql = "UPDATE your_table 
                SET deleted_at = NOW() 
                WHERE id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $tenantId]);
    }
}
```

4. **Create Service**
```php
<?php
// BACKEND/modules/YourModule/Services/YourModuleService.php

class YourModuleService
{
    private $repository;

    public function __construct()
    {
        $this->repository = new YourModuleRepository();
    }

    public function getAll($tenantId, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        return $this->repository->findAll($tenantId, $limit, $offset);
    }

    public function getById($id, $tenantId)
    {
        $item = $this->repository->findById($id, $tenantId);
        if (!$item) {
            return [
                'success' => false,
                'message' => 'Item not found'
            ];
        }
        return [
            'success' => true,
            'data' => $item
        ];
    }

    public function create($data, $tenantId, $userId)
    {
        $data['tenant_id'] = $tenantId;
        $data['created_by'] = $userId;
        
        $id = $this->repository->create($data);
        
        return [
            'success' => true,
            'message' => 'Item created successfully',
            'data' => ['id' => $id]
        ];
    }

    public function update($id, $data, $tenantId, $userId)
    {
        $data['updated_by'] = $userId;
        
        $result = $this->repository->update($id, $data, $tenantId);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Item updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update item'
        ];
    }

    public function delete($id, $tenantId, $userId)
    {
        $result = $this->repository->delete($id, $tenantId);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Item deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete item'
        ];
    }
}
```

5. **Create Controller**
```php
<?php
// BACKEND/modules/YourModule/Controllers/YourModuleController.php

class YourModuleController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new YourModuleService();
    }

    public function index()
    {
        $tenantId = $this->getTenantId();
        $page = $this->getQueryParam('page', 1);
        $limit = $this->getQueryParam('limit', 10);
        
        $result = $this->service->getAll($tenantId, $page, $limit);
        
        $this->jsonResponse($result);
    }

    public function show($id)
    {
        $tenantId = $this->getTenantId();
        
        $result = $this->service->getById($id, $tenantId);
        
        $this->jsonResponse($result);
    }

    public function store()
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        $data = $this->getJsonInput();
        
        $result = $this->service->create($data, $tenantId, $userId);
        
        $this->jsonResponse($result, 201);
    }

    public function update($id)
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        $data = $this->getJsonInput();
        
        $result = $this->service->update($id, $data, $tenantId, $userId);
        
        $this->jsonResponse($result);
    }

    public function destroy($id)
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        
        $result = $this->service->delete($id, $tenantId, $userId);
        
        $this->jsonResponse($result);
    }
}
```

6. **Add Routes**
```php
// BACKEND/routes/api.php

// Your Module routes
$router->get('/your-module', 'YourModuleController@index');
$router->get('/your-module/{id}', 'YourModuleController@show');
$router->post('/your-module', 'YourModuleController@store');
$router->put('/your-module/{id}', 'YourModuleController@update');
$router->delete('/your-module/{id}', 'YourModuleController@destroy');
```

### Database Migrations

Create migration files in `BACKEND/migrations/`:

```php
<?php
// BACKEND/migrations/20260707_create_your_table.php

class CreateYourTable
{
    public function up($db)
    {
        $sql = "CREATE TABLE your_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            field1 VARCHAR(255) NOT NULL,
            field2 TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            INDEX idx_tenant_id (tenant_id),
            INDEX idx_deleted_at (deleted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($sql);
    }

    public function down($db)
    {
        $sql = "DROP TABLE IF EXISTS your_table";
        $db->exec($sql);
    }
}
```

---

## Frontend Development

### API Client Usage

The API client (`FRONTEND/js/api-client.js`) provides a unified interface for all API calls:

```javascript
// Get data
const orders = await window.apiClient.getOrders({ page: 1, limit: 10 });

// Create data
const newOrder = await window.apiClient.createOrder({
    table_id: 1,
    order_type: 'DINE_IN',
    items: [...]
});

// Update data
const updated = await window.apiClient.updateOrder(orderId, {
    status: 'IN_PROGRESS'
});

// Delete data
await window.apiClient.deleteOrder(orderId);
```

### Creating a New Frontend Module

1. **Add API Methods to api-client.js**
```javascript
// FRONTEND/js/api-client.js

async getYourModule(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`/your-module${queryString ? '?' + queryString : ''}`, {
        method: 'GET'
    });
}

async getYourModuleById(id) {
    return this.request(`/your-module/${id}`, {
        method: 'GET'
    });
}

async createYourModule(data) {
    return this.request('/your-module', {
        method: 'POST',
        body: JSON.stringify(data)
    });
}
```

2. **Create JavaScript Module**
```javascript
// FRONTEND/js/your-module.js

class YourModule {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadData();
    }

    bindEvents() {
        document.getElementById('createBtn')?.addEventListener('click', () => {
            this.createItem();
        });
    }

    async loadData() {
        try {
            const result = await window.apiClient.getYourModule();
            if (result.success) {
                this.renderData(result.data);
            }
        } catch (error) {
            console.error('Failed to load data:', error);
        }
    }

    renderData(data) {
        const container = document.getElementById('dataContainer');
        // Render your data
    }

    async createItem() {
        const data = {
            field1: document.getElementById('field1').value,
            field2: document.getElementById('field2').value
        };

        try {
            const result = await window.apiClient.createYourModule(data);
            if (result.success) {
                this.loadData();
            }
        } catch (error) {
            console.error('Failed to create item:', error);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('yourModulePage')) {
        window.yourModule = new YourModule();
    }
});
```

3. **Add HTML Structure**
```html
<!-- Add to appropriate HTML file -->
<div class="page" id="yourModulePage">
    <div class="page-header">
        <h3>Your Module</h3>
    </div>
    <div class="content">
        <button class="btn btn-primary" id="createBtn">Create Item</button>
        <div id="dataContainer"></div>
    </div>
</div>
```

4. **Add CSS Styles**
```css
/* Add to appropriate CSS file */
.your-module-page {
    padding: 20px;
}

.your-module-page .page-header {
    margin-bottom: 20px;
}
```

---

## Database Schema

### Multi-Tenancy

The system uses a shared database schema with tenant isolation:

- All tables include `tenant_id` column
- All queries filter by `tenant_id`
- Soft deletes using `deleted_at` timestamp

### Key Tables

**Users**
```sql
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_username (username),
    INDEX idx_deleted_at (deleted_at)
);
```

**Orders**
```sql
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    table_id INT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    order_type ENUM('DINE_IN', 'TAKEAWAY', 'DELIVERY') NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_status ENUM('PENDING', 'IN_PROGRESS', 'READY', 'SERVED', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_branch_id (branch_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (order_status),
    INDEX idx_deleted_at (deleted_at)
);
```

**Products**
```sql
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_deleted_at (deleted_at)
);
```

---

## API Development

### Authentication

All API endpoints (except login) require authentication via JWT token:

```php
// In controller or middleware
$token = $this->getBearerToken();
$payload = $this->validateToken($token);
$tenantId = $payload->tenant_id;
$userId = $payload->user_id;
```

### Request/Response Format

**Request:**
```json
{
  "field1": "value1",
  "field2": "value2"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error information"
}
```

### Headers

Required headers for authenticated requests:

```
Authorization: Bearer {jwt_token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
Content-Type: application/json
```

---

## Testing

### Unit Tests

Create unit tests in `BACKEND/tests/unit/`:

```php
<?php
// BACKEND/tests/unit/YourModuleRepositoryTest.php

use PHPUnit\Framework\TestCase;
use Modules\YourModule\Repositories\YourModuleRepository;
use Core\Database;

class YourModuleRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new YourModuleRepository();
    }

    public function testFindAll()
    {
        $items = $this->repository->findAll($this->testTenantId, 10, 0);
        $this->assertIsArray($items);
    }

    public function testCreate()
    {
        $data = [
            'tenant_id' => $this->testTenantId,
            'field1' => 'test',
            'field2' => 'test'
        ];
        
        $id = $this->repository->create($data);
        $this->assertIsNumeric($id);
        $this->assertGreaterThan(0, $id);
        
        // Cleanup
        $this->repository->delete($id, $this->testTenantId);
    }
}
```

Run unit tests:
```bash
cd BACKEND
vendor/bin/phpunit tests/unit/
```

### Integration Tests

Create integration tests in `BACKEND/tests/integration/`:

```php
<?php
// BACKEND/tests/integration/YourFlowTest.php

use PHPUnit\Framework\TestCase;
use Modules\YourModule\Services\YourModuleService;

class YourFlowTest extends TestCase
{
    private $service;
    private $testTenantId = 1;
    private $testUserId = 1;

    public function testCompleteFlow()
    {
        // Create
        $createResult = $this->service->create([...], $this->testTenantId, $this->testUserId);
        $this->assertTrue($createResult['success']);
        
        $id = $createResult['data']['id'];
        
        // Read
        $readResult = $this->service->getById($id, $this->testTenantId);
        $this->assertTrue($readResult['success']);
        
        // Update
        $updateResult = $this->service->update($id, [...], $this->testTenantId, $this->testUserId);
        $this->assertTrue($updateResult['success']);
        
        // Delete
        $deleteResult = $this->service->delete($id, $this->testTenantId, $this->testUserId);
        $this->assertTrue($deleteResult['success']);
    }
}
```

Run integration tests:
```bash
cd BACKEND
vendor/bin/phpunit tests/integration/
```

### E2E Tests

Create E2E tests in `BACKEND/tests/e2e/` using Playwright:

```typescript
// BACKEND/tests/e2e/your-module.spec.ts

import { test, expect } from '@playwright/test';

test.describe('Your Module E2E', () => {
    test('should create and view item', async ({ page }) => {
        await page.goto('http://localhost:8000/FRONTEND/dashboard/');
        
        // Login
        await page.fill('#username', 'admin');
        await page.fill('#password', 'admin123');
        await page.click('#loginBtn');
        
        // Navigate to module
        await page.click('[data-page="your-module"]');
        
        // Create item
        await page.click('#createBtn');
        await page.fill('#field1', 'test value');
        await page.click('#saveBtn');
        
        // Verify
        await expect(page.locator('.success-message')).toBeVisible();
    });
});
```

Run E2E tests:
```bash
cd BACKEND
npx playwright test
```

---

## Deployment

### Staging Deployment

1. **Prepare Code**
```bash
git pull origin main
composer install --no-dev
```

2. **Run Migrations**
```bash
php BACKEND/migrations/migrate.php
```

3. **Clear Cache**
```bash
php BACKEND/scripts/clear-cache.php
```

4. **Restart Services**
```bash
sudo systemctl restart apache2
sudo systemctl restart mysql
```

### Production Deployment

1. **Backup Database**
```bash
mysqldump -u root -p ebp_restaurant_db > backup_$(date +%Y%m%d).sql
```

2. **Deploy Code**
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
```

3. **Run Migrations**
```bash
php BACKEND/migrations/migrate.php
```

4. **Clear Cache**
```bash
php BACKEND/scripts/clear-cache.php
```

5. **Restart Services**
```bash
sudo systemctl restart apache2
sudo systemctl restart mysql
```

6. **Verify Deployment**
```bash
curl -I https://api.ebp-restaurant.com/health
```

---

## Contributing Guidelines

### Code Style

**PHP:**
- Follow PSR-12 coding standards
- Use 4 spaces for indentation
- Use camelCase for variables and methods
- Use PascalCase for classes

**JavaScript:**
- Use 2 spaces for indentation
- Use camelCase for variables and functions
- Use PascalCase for classes
- Add JSDoc comments for functions

### Commit Messages

Follow conventional commits format:

```
feat: add new feature
fix: fix bug
docs: update documentation
refactor: refactor code
test: add tests
chore: maintenance tasks
```

Example:
```
feat(auth): add JWT token refresh functionality

- Implement token refresh endpoint
- Add refresh token validation
- Update auth manager to handle refresh
```

### Pull Request Process

1. Create feature branch from `main`
2. Make changes and commit
3. Push to remote
4. Create pull request
5. Request review
6. Address feedback
7. Merge after approval

### Code Review Checklist

- [ ] Code follows style guidelines
- [ ] Tests added/updated
- [ ] Documentation updated
- [ ] No console errors
- [ ] No security vulnerabilities
- [ ] Performance considerations addressed

---

## Security Best Practices

### Input Validation

Always validate and sanitize user input:

```php
// Bad
$sql = "SELECT * FROM users WHERE id = " . $_GET['id'];

// Good
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
```

### SQL Injection Prevention

Use prepared statements:

```php
$stmt = $pdo->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);
```

### XSS Prevention

Escape output:

```php
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### CSRF Protection

Include CSRF tokens in forms:

```php
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

### Password Security

Use password hashing:

```php
$hash = password_hash($password, PASSWORD_DEFAULT);
$verify = password_verify($input, $hash);
```

---

## Performance Optimization

### Database Indexing

Add indexes to frequently queried columns:

```sql
CREATE INDEX idx_tenant_status ON orders(tenant_id, status);
```

### Caching

Implement caching for expensive operations:

```php
$cacheKey = "analytics_{$tenantId}_{$date}";
$cached = $cache->get($cacheKey);

if ($cached === null) {
    $data = $this->calculateAnalytics($tenantId, $date);
    $cache->set($cacheKey, $data, 3600); // 1 hour
}
```

### Query Optimization

Use EXPLAIN to analyze queries:

```sql
EXPLAIN SELECT * FROM orders WHERE tenant_id = 1 AND status = 'PENDING';
```

---

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Check database credentials in config
- Verify MySQL service is running
- Check firewall settings

**API Returns 401 Unauthorized**
- Verify JWT token is valid
- Check token expiration
- Ensure proper headers are sent

**Frontend Not Loading**
- Check browser console for errors
- Verify API endpoint is accessible
- Check CORS settings

---

## Support

For developer support:
- **Documentation**: https://docs.ebp-restaurant.com/developers
- **GitHub Issues**: https://github.com/ebp-restaurant/restaurant-erp/issues
- **Email**: developers@ebp-restaurant.com
- **Slack**: #erp-developers channel
