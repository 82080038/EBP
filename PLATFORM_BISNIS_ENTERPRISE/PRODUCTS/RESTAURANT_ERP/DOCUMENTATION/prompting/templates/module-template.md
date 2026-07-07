# Module Creation Template

## Purpose

Template for creating new modules in RESTAURANT_ERP following project conventions.

## REASONS Canvas

### R - Requirements

**Module Requirements:**
- Module name: [MODULE_NAME]
- Purpose: [Describe module purpose]
- Target users: [List target users]
- Key features: [List key features]

**Definition of Done:**
- [ ] Controller implemented
- [ ] Service implemented
- [ ] Repository implemented
- [ ] Model implemented
- [ ] Routes defined
- [ ] Tests created
- [ ] Documentation added

### E - Entities

**Domain Entities:**
- [Entity1]: [Description]
- [Entity2]: [Description]
- [Entity3]: [Description]

**Entity Relationships:**
- [Entity1] has many [Entity2]
- [Entity3] belongs to [Entity1]

### A - Approach

**Implementation Strategy:**
1. Create database migration
2. Implement Model class
3. Implement Repository class
4. Implement Service class
5. Implement Controller class
6. Define routes
7. Create tests
8. Document module

### S - Structure

**Module Structure:**
```
BACKEND/modules/[MODULE_NAME]/
├── Controllers/
│   └── [MODULE_NAME]Controller.php
├── Services/
│   └── [MODULE_NAME]Service.php
├── Repositories/
│   └── [MODULE_NAME]Repository.php
├── Models/
│   └── [MODULE_NAME].php
└── routes/
    └── [MODULE_NAME].php
```

### O - Operations

**Implementation Steps:**

1. **Database Migration**
   - Create table: [TABLE_NAME]
   - Add columns: [list columns]
   - Add indexes: [list indexes]
   - Add foreign keys: [list foreign keys]

2. **Model Implementation**
   - Create [MODULE_NAME].php
   - Define properties
   - Add getters/setters
   - Add validation methods

3. **Repository Implementation**
   - Create [MODULE_NAME]Repository.php
   - Implement CRUD methods
   - Add tenant isolation
   - Add custom queries

4. **Service Implementation**
   - Create [MODULE_NAME]Service.php
   - Implement business logic
   - Add transaction handling
   - Add audit logging

5. **Controller Implementation**
   - Create [MODULE_NAME]Controller.php
   - Implement HTTP methods
   - Add input validation
   - Add error handling

6. **Routes Implementation**
   - Create [MODULE_NAME].php
   - Define endpoints
   - Add middleware
   - Add permission checks

7. **Tests Implementation**
   - Create unit tests
   - Create integration tests
   - Create E2E tests

8. **Documentation**
   - Add PHPDoc comments
   - Create module README
   - Update API documentation

### N - Norms

**Coding Standards:**
- PHP 8.x syntax
- PSR-12 coding style
- Type hints for all parameters
- PHPDoc for all methods
- Meaningful variable names

**Security Standards:**
- Use PDO prepared statements
- Validate all inputs
- Implement authentication
- Implement authorization
- Add audit logging

### S - Safeguards

**Non-negotiable Rules:**
- MUST use Database::getInstance()
- MUST use JWT for authentication
- MUST use Response class
- MUST implement tenant isolation
- MUST use Transaction for data changes
- MUST include audit logging

## Implementation

### Step 1: Database Migration

Create migration file: `DATABASE/MIGRATION_XXX_[MODULE_NAME].sql`

```sql
-- Migration: [MODULE_NAME]
-- Description: [Description]

CREATE TABLE IF NOT EXISTS [table_name] (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    -- Add columns here
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    CONSTRAINT fk_[table]_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_[table]_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 2: Model

Create file: `BACKEND/modules/[MODULE_NAME]/Models/[MODULE_NAME].php`

```php
<?php

/**
 * [MODULE_NAME] Model
 * 
 * @package EBP\Modules\[MODULE_NAME]
 * @version 1.0.0
 */

class [MODULE_NAME]
{
    private $id;
    private $tenant_id;
    // Add other properties
    
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fromArray($data);
        }
    }
    
    // Getters and setters
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenant_id; }
    
    public function setId(int $id): void { $this->id = $id; }
    public function setTenantId(int $tenantId): void { $this->tenant_id = $tenantId; }
    
    // Add other getters/setters
    
    public function fromArray(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->tenant_id = $data['tenant_id'] ?? null;
        // Map other properties
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            // Add other properties
        ];
    }
    
    // Validation methods
    public function validate(): array
    {
        $errors = [];
        
        // Add validation logic
        
        return $errors;
    }
}
```

### Step 3: Repository

Create file: `BACKEND/modules/[MODULE_NAME]/Repositories/[MODULE_NAME]Repository.php`

```php
<?php

/**
 * [MODULE_NAME] Repository
 * 
 * @package EBP\Modules\[MODULE_NAME]
 * @version 1.0.0
 */

class [MODULE_NAME]Repository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    public function findAll(int $tenantId): array
    {
        $sql = "SELECT * FROM [table_name] 
                WHERE tenant_id = :tenant_id 
                AND deleted_at IS NULL 
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        
        return $stmt->fetchAll();
    }
    
    public function findById(int $id, int $tenantId): ?array
    {
        $sql = "SELECT * FROM [table_name] 
                WHERE id = :id 
                AND tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'tenant_id' => $tenantId]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function create(array $data): int
    {
        $sql = "INSERT INTO [table_name] (tenant_id, [columns]) 
                VALUES (:tenant_id, [placeholders])";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE [table_name] 
                SET [column_updates] 
                WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    public function delete(int $id): bool
    {
        $sql = "UPDATE [table_name] 
                SET deleted_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }
}
```

### Step 4: Service

Create file: `BACKEND/modules/[MODULE_NAME]/Services/[MODULE_NAME]Service.php`

```php
<?php

/**
 * [MODULE_NAME] Service
 * 
 * @package EBP\Modules\[MODULE_NAME]
 * @version 1.0.0
 */

class [MODULE_NAME]Service
{
    private $repository;
    private $db;
    
    public function __construct()
    {
        $this->repository = new [MODULE_NAME]Repository();
        $this->db = Database::getInstance();
    }
    
    public function getAll(int $tenantId): array
    {
        return $this->repository->findAll($tenantId);
    }
    
    public function getById(int $id, int $tenantId): ?array
    {
        return $this->repository->findById($id, $tenantId);
    }
    
    public function create(array $data, int $tenantId, int $userId): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Validate data
            $model = new [MODULE_NAME]($data);
            $errors = $model->validate();
            
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
            }
            
            // Prepare data
            $data['tenant_id'] = $tenantId;
            $data['created_by'] = $userId;
            
            // Create record
            $id = $this->repository->create($data);
            
            // Audit log
            Audit::log($tenantId, $userId, '[MODULE_NAME]_CREATE', [
                'id' => $id,
                'data' => $data
            ]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => '[MODULE_NAME] created successfully',
                'data' => ['id' => $id]
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to create [MODULE_NAME]',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function update(int $id, array $data, int $tenantId, int $userId): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Validate data
            $model = new [MODULE_NAME]($data);
            $errors = $model->validate();
            
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
            }
            
            // Update record
            $data['updated_by'] = $userId;
            $result = $this->repository->update($id, $data);
            
            // Audit log
            Audit::log($tenantId, $userId, '[MODULE_NAME]_UPDATE', [
                'id' => $id,
                'data' => $data
            ]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => '[MODULE_NAME] updated successfully'
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to update [MODULE_NAME]',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function delete(int $id, int $tenantId, int $userId): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Delete record
            $result = $this->repository->delete($id);
            
            // Audit log
            Audit::log($tenantId, $userId, '[MODULE_NAME]_DELETE', [
                'id' => $id
            ]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => '[MODULE_NAME] deleted successfully'
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to delete [MODULE_NAME]',
                'error' => $e->getMessage()
            ];
        }
    }
}
```

### Step 5: Controller

Create file: `BACKEND/modules/[MODULE_NAME]/Controllers/[MODULE_NAME]Controller.php`

```php
<?php

/**
 * [MODULE_NAME] Controller
 * 
 * @package EBP\Modules\[MODULE_NAME]
 * @version 1.0.0
 */

class [MODULE_NAME]Controller
{
    private $service;
    
    public function __construct()
    {
        $this->service = new [MODULE_NAME]Service();
    }
    
    public function index()
    {
        $tenantId = $_SESSION['tenant_id'] ?? 0;
        
        $result = $this->service->getAll($tenantId);
        
        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::error($result['message']);
        }
    }
    
    public function show($id)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 0;
        
        $result = $this->service->getById($id, $tenantId);
        
        if ($result) {
            Response::success($result);
        } else {
            Response::notFound('[MODULE_NAME] not found');
        }
    }
    
    public function store()
    {
        $tenantId = $_SESSION['tenant_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? 0;
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = $this->service->create($data, $tenantId, $userId);
        
        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message'], 400, $result['errors'] ?? []);
        }
    }
    
    public function update($id)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? 0;
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = $this->service->update($id, $data, $tenantId, $userId);
        
        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message'], 400, $result['errors'] ?? []);
        }
    }
    
    public function destroy($id)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? 0;
        
        $result = $this->service->delete($id, $tenantId, $userId);
        
        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
```

### Step 6: Routes

Create file: `BACKEND/modules/[MODULE_NAME]/routes/[MODULE_NAME].php`

```php
<?php

/**
 * [MODULE_NAME] Routes
 * 
 * @package EBP\Modules\[MODULE_NAME]
 * @version 1.0.0
 */

// Require controller
require_once __DIR__ . '/../Controllers/[MODULE_NAME]Controller.php';

$controller = new [MODULE_NAME]Controller();

// Routes
$router->get('/api/v1/[module]', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('[MODULE_NAME]_VIEW');
    $controller->index();
});

$router->get('/api/v1/[module]/:id', function($id) use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('[MODULE_NAME]_VIEW');
    $controller->show($id);
});

$router->post('/api/v1/[module]', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('[MODULE_NAME]_CREATE');
    $controller->store();
});

$router->put('/api/v1/[module]/:id', function($id) use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('[MODULE_NAME]_UPDATE');
    $controller->update($id);
});

$router->delete('/api/v1/[module]/:id', function($id) use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('[MODULE_NAME]_DELETE');
    $controller->destroy($id);
});
```

### Step 7: Integration

Add to `BACKEND/routes/api.php`:

```php
// Load [MODULE_NAME] routes
require_once __DIR__ . '/../modules/[MODULE_NAME]/routes/[MODULE_NAME].php';
```

## Checklist

- [ ] Database migration created
- [ ] Model implemented
- [ ] Repository implemented
- [ ] Service implemented
- [ ] Controller implemented
- [ ] Routes defined
- [ ] Routes registered
- [ ] Tests created
- [ ] Documentation added
- [ ] Integration tested

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
