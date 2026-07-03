# EBP Core Code

**Version:** 1.0.0

**Status:** Initial Implementation

---

# Overview

This directory contains the core components of the Enterprise Business Platform (EBP). These components are designed to be reused across all EBP products and provide the foundational functionality for authentication, database connectivity, API handling, and more.

---

# Core Components

## Authentication

**Location:** `Authentication/`

**Components:**
- `JWT.php` - JSON Web Token implementation for authentication
- `AuthMiddleware.php` - Authentication middleware for API requests

**Features:**
- JWT token encoding and decoding
- Token validation with expiration checking
- Role-based authentication
- Tenant and branch context management
- Secure secret key management

**Usage:**
```php
use EBP\Core\Authentication\JWT;
use EBP\Core\Authentication\AuthMiddleware;

// Create JWT token
$jwt = new JWT();
$token = $jwt->encode([
    'user_id' => 1,
    'username' => 'admin',
    'tenant_id' => 1,
    'branch_id' => 2,
    'role' => 'Administrator',
    'exp' => time() + (60 * 60 * 8)
]);

// Authenticate request
$middleware = new AuthMiddleware();
$payload = $middleware->authenticate();
```

---

## Permission

**Location:** `Permission/`

**Status:** To be implemented

**Planned Components:**
- `PermissionMiddleware.php` - Permission checking middleware
- `PermissionService.php` - Permission management service

---

## Tenant

**Location:** `Tenant/`

**Status:** To be implemented

**Planned Components:**
- `TenantMiddleware.php` - Tenant context middleware
- `TenantService.php` - Tenant management service

---

## Audit

**Location:** `Audit/`

**Status:** To be implemented

**Planned Components:**
- `Audit.php` - Audit logging utility
- `AuditService.php` - Audit management service

---

## Database

**Location:** `Database/`

**Components:**
- `Database.php` - Database connection manager

**Features:**
- Singleton pattern for connection management
- Socket and host connection fallback
- PDO with proper error handling
- Connection testing
- Database information retrieval
- Environment variable support

**Usage:**
```php
use EBP\Core\Database\Database;

// Get database instance
$db = Database::getInstance();
$pdo = $db->connect();

// Or with custom config
$db = new Database([
    'host' => 'localhost',
    'dbname' => 'my_database',
    'username' => 'user',
    'password' => 'pass'
]);
$pdo = $db->connect();

// Test connection
if ($db->testConnection()) {
    echo "Connection successful";
}

// Get database info
$info = $db->getDatabaseInfo();
```

---

## API

**Location:** `API/`

**Components:**
- `Response.php` - Standardized API response handler

**Features:**
- JSON response formatting
- Success and error responses
- HTTP status code management
- Validation error handling
- Pagination support
- Standard response format

**Usage:**
```php
use EBP\Core\API\Response;

// Success response
Response::success($data, 'Operation successful');

// Error response
Response::error('Invalid input', 400, $errors);

// Validation error
Response::validationError($errors);

// Not found
Response::notFound('Resource not found');

// Unauthorized
Response::unauthorized('Invalid credentials');

// Forbidden
Response::forbidden('Access denied');

// Server error
Response::serverError('Internal error');

// Paginated response
Response::paginated($data, $total, $page, $limit);
```

---

## Logging

**Location:** `Logging/`

**Status:** To be implemented

**Planned Components:**
- `Logger.php` - Logging utility
- `LogService.php` - Log management service

---

## File

**Location:** `File/`

**Status:** To be implemented

**Planned Components:**
- `FileManager.php` - File management utility
- `StorageService.php` - Storage management service

---

# Configuration

## Environment Variables

The core components support the following environment variables:

```bash
# JWT Configuration
JWT_SECRET=your_secret_key_here

# Database Configuration
DB_HOST=localhost
DB_SOCKET=/opt/lampp/var/mysql/mysql.sock
DB_NAME=ebp_platform_db
DB_USER=ebp_app
DB_PASSWORD=ebp_secure_password_2026
```

---

# Integration Guide

## Using EBP Core in Products

1. **Include EBP Core in your product:**
   ```php
   require_once '/path/to/EBP/06_CORE_CODE/Authentication/JWT.php';
   require_once '/path/to/EBP/06_CORE_CODE/Database/Database.php';
   ```

2. **Use namespace-based imports:**
   ```php
   use EBP\Core\Authentication\JWT;
   use EBP\Core\Database\Database;
   ```

3. **Configure environment variables:**
   ```bash
   export JWT_SECRET=your_secret
   export DB_NAME=your_database
   ```

---

# Development Status

| Component | Status | Priority |
|-----------|--------|----------|
| Authentication | ✅ Complete | High |
| Permission | ⏳ Pending | High |
| Tenant | ⏳ Pending | High |
| Audit | ⏳ Pending | Medium |
| Database | ✅ Complete | High |
| API | ✅ Complete | High |
| Logging | ⏳ Pending | Medium |
| File | ⏳ Pending | Low |

---

# Next Steps

1. **Complete remaining core components**
   - Implement Permission middleware
   - Implement Tenant service
   - Implement Audit logging

2. **Add unit tests**
   - Test JWT encoding/decoding
   - Test database connections
   - Test API responses

3. **Add documentation**
   - API documentation
   - Usage examples
   - Integration guides

4. **Create shared engines**
   - Pricing Engine
   - Inventory Engine
   - Accounting Engine

---

**End of Document**
