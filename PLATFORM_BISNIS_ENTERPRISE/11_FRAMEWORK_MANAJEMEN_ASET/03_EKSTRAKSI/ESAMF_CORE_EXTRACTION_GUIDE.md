# ESAMF Core Extraction Guide

**Document ID:** ESAMF-EXTRACTION-001

**Version:** 1.0

**Purpose:** Define the methodology for extracting Core Assets from repositories

---

# Overview

Core Extraction is the process of isolating universal components from repositories and preparing them for migration to EBP Core (06_CORE_CODE). This guide provides a systematic methodology for extracting Core Assets.

---

# Core Asset Characteristics

Before extraction, verify the component meets Core Asset criteria:

- **Universal Usage**: Used by ALL products
- **Generic Logic**: No industry-specific logic
- **Fundamental Nature**: Essential for platform operation
- **No Business Rules**: Does not contain business rules specific to any domain
- **High Reusability**: Can be used in any context without modification

---

# Extraction Process

## Phase 1: Preparation

### Step 1: Component Identification

```
Component: [Component Name]
Location: [Component Location]
Files:
- File 1: [Path]
- File 2: [Path]
- File 3: [Path]
```

### Step 2: Dependency Analysis

```
External Dependencies:
- [Dependency 1: Version, Purpose]
- [Dependency 2: Version, Purpose]

Internal Dependencies:
- [Internal Dependency 1: Purpose]
- [Internal Dependency 2: Purpose]
```

### Step 3: Impact Analysis

```
Current Usage:
- [Product 1: How it's used]
- [Product 2: How it's used]
- [Product 3: How it's used]

Extraction Impact:
- [Impact 1]
- [Impact 2]
- [Impact 3]
```

### Step 4: Create Extraction Branch

```bash
git checkout -b extract/[component-name]
```

---

## Phase 2: Isolation

### Step 1: Copy Component Files

```bash
# Create temporary directory
mkdir -p temp/[component-name]

# Copy component files
cp [component-location]/* temp/[component-name]/
```

### Step 2: Identify Repository-Specific Code

```
Repository-Specific Code:
- [Code snippet 1: Location, Reason]
- [Code snippet 2: Location, Reason]
- [Code snippet 3: Location, Reason]
```

### Step 3: Identify Hard-Coded Values

```
Hard-Coded Values:
- [Value 1: Location, Default value]
- [Value 2: Location, Default value]
- [Value 3: Location, Default value]
```

### Step 4: Identify Direct Dependencies

```
Direct Dependencies:
- [Dependency 1: How it's instantiated]
- [Dependency 2: How it's instantiated]
- [Dependency 3: How it's instantiated]
```

---

## Phase 3: Decoupling

### Step 1: Remove Repository-Specific Code

**Before:**
```php
class AuthService {
    private $restaurantId;
    
    public function login($username, $password) {
        // Restaurant-specific logic
        $restaurant = $this->getRestaurant();
        // ...
    }
}
```

**After:**
```php
class AuthService {
    public function login($username, $password, $context = []) {
        // Generic logic
        // Context can include tenant ID, etc.
    }
}
```

### Step 2: Replace Hard-Coded Values

**Before:**
```php
private $tokenExpiry = 3600; // 1 hour
```

**After:**
```php
private $tokenExpiry;
public function __construct(Config $config) {
    $this->tokenExpiry = $config->get('auth.token_expiry', 3600);
}
```

### Step 3: Replace Direct Dependencies

**Before:**
```php
class AuthService {
    private $db;
    
    public function __construct() {
        $this->db = new MySQLConnection();
    }
}
```

**After:**
```php
class AuthService {
    private $db;
    
    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
    }
}
```

### Step 4: Create Interfaces

```php
interface DatabaseInterface {
    public function query($sql, $params = []);
    public function fetch($sql, $params = []);
    public function execute($sql, $params = []);
}
```

---

## Phase 4: Generalization

### Step 1: Add Configuration Options

```php
class AuthService {
    private $config;
    
    public function __construct(array $config = []) {
        $this->config = array_merge([
            'token_expiry' => 3600,
            'max_attempts' => 5,
            'lockout_duration' => 900,
            'password_policy' => [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_number' => true,
                'require_special' => true
            ]
        ], $config);
    }
}
```

### Step 2: Add Context Parameters

```php
public function login($username, $password, $context = []) {
    // Context can include:
    // - tenant_id
    // - ip_address
    // - user_agent
    // - custom metadata
}
```

### Step 3: Abstract Industry-Specific Logic

**Before:**
```php
if ($this->isRestaurantUser($user)) {
    // Restaurant-specific logic
}
```

**After:**
```php
// Remove industry-specific logic
// Let calling code handle domain-specific logic
```

---

## Phase 5: Standardization

### Step 1: Apply EBP Coding Standards

- **PSR-12**: Follow PSR-12 coding standards
- **Naming**: Follow EBP naming conventions
- **Documentation**: Add PHPDoc blocks
- **Comments**: Add inline comments for complex logic

### Step 2: Apply EBP Architecture Standards

- **Dependency Injection**: Use dependency injection
- **Interfaces**: Implement interfaces
- **Service Layer**: Use service layer pattern
- **Repository Pattern**: Use repository pattern for data access

### Step 3: Apply EBP Security Standards

- **Input Validation**: Validate all inputs
- **Output Encoding**: Encode all outputs
- **SQL Injection**: Use parameterized queries
- **XSS**: Escape outputs
- **CSRF**: Add CSRF protection

### Step 4: Apply EBP Error Handling Standards

- **Exceptions**: Use exceptions for error handling
- **Logging**: Log all errors
- **Error Messages**: Provide meaningful error messages
- **Error Codes**: Use standard error codes

---

## Phase 6: Testing

### Step 1: Create Unit Tests

```php
class AuthServiceTest extends TestCase {
    public function testLoginWithValidCredentials() {
        $auth = new AuthService($this->config, $this->db);
        $result = $auth->login('testuser', 'password123');
        
        $this->assertTrue($result->success);
        $this->assertNotNull($result->token);
    }
    
    public function testLoginWithInvalidCredentials() {
        $auth = new AuthService($this->config, $this->db);
        
        $this->expectException(AuthenticationException::class);
        $auth->login('testuser', 'wrongpassword');
    }
    
    public function testLoginWithLockedAccount() {
        $auth = new AuthService($this->config, $this->db);
        
        $this->expectException(AccountLockedException::class);
        $auth->login('lockeduser', 'password123');
    }
}
```

### Step 2: Create Integration Tests

```php
class AuthServiceIntegrationTest extends TestCase {
    public function testLoginWithDatabase() {
        $auth = new AuthService($this->config, $this->db);
        $result = $auth->login('testuser', 'password123');
        
        $this->assertTrue($result->success);
        $this->assertDatabaseHas('users', [
            'username' => 'testuser'
        ]);
    }
}
```

### Step 3: Achieve Test Coverage

- **Target**: > 80% code coverage
- **Critical Paths**: 100% coverage
- **Edge Cases**: Test all edge cases
- **Error Paths**: Test all error paths

---

## Phase 7: Documentation

### Step 1: Create README

```markdown
# [Component Name]

## Purpose
[Brief description of component purpose]

## Installation
```bash
composer require ebp/[component-name]
```

## Usage
```php
use EBP\Core\[Component]\[ComponentName];

$component = new [ComponentName]($config);
$result = $component->method($params);
```

## Configuration
[Configuration options]

## API
[API documentation]
```

### Step 2: Add PHPDoc Blocks

```php
/**
 * Authentication Service
 *
 * Provides user authentication and session management functionality.
 *
 * @package EBP\Core\Authentication
 * @author Petrick Software
 * @version 1.0.0
 */
class AuthService {
    /**
     * Authenticate user with username and password
     *
     * @param string $username User's username
     * @param string $password User's password
     * @param array $context Additional context (tenant_id, ip_address, etc.)
     * @return AuthResult Authentication result
     * @throws AuthenticationException If authentication fails
     * @throws AccountLockedException If account is locked
     */
    public function login($username, $password, $context = []) {
        // Implementation
    }
}
```

### Step 3: Create Migration Guide

```markdown
# Migration Guide

## Changes from Original
- [Change 1]
- [Change 2]
- [Change 3]

## Breaking Changes
- [Breaking change 1]
- [Breaking change 2]

## Migration Steps
1. [Step 1]
2. [Step 2]
3. [Step 3]
```

---

## Phase 8: Code Review

### Step 1: Self-Review

- [ ] Code follows EBP standards
- [ ] Code is well-documented
- [ ] Tests are comprehensive
- [ ] No security vulnerabilities
- [ ] No performance issues

### Step 2: Peer Review

- [ ] Peer review completed
- [ ] Reviewer feedback addressed
- [ ] Changes approved

### Step 3: Architecture Review

- [ ] Architecture review completed
- [ ] Architecture concerns addressed
- [ ] Changes approved

### Step 4: Security Review

- [ ] Security review completed
- [ ] Security concerns addressed
- [ ] Changes approved

---

## Phase 9: Publication

### Step 1: Create Release

```bash
# Tag release
git tag v1.0.0
git push origin v1.0.0
```

### Step 2: Publish to Package Registry

```bash
# Publish to Composer
composer publish
```

### Step 3: Update Documentation

- Update EBP documentation
- Update Software Asset Inventory
- Update Enterprise Knowledge Graph

---

# Extraction Checklist

## Preparation
- [ ] Component identified
- [ ] Dependencies analyzed
- [ ] Impact analyzed
- [ ] Extraction branch created

## Isolation
- [ ] Component files copied
- [ ] Repository-specific code identified
- [ ] Hard-coded values identified
- [ ] Direct dependencies identified

## Decoupling
- [ ] Repository-specific code removed
- [ ] Hard-coded values replaced
- [ ] Direct dependencies replaced
- [ ] Interfaces created

## Generalization
- [ ] Configuration options added
- [ ] Context parameters added
- [ ] Industry-specific logic abstracted

## Standardization
- [ ] EBP coding standards applied
- [ ] EBP architecture standards applied
- [ ] EBP security standards applied
- [ ] EBP error handling standards applied

## Testing
- [ ] Unit tests created
- [ ] Integration tests created
- [ ] Test coverage > 80%
- [ ] All tests passing

## Documentation
- [ ] README created
- [ ] PHPDoc blocks added
- [ ] Migration guide created

## Code Review
- [ ] Self-review completed
- [ ] Peer review completed
- [ ] Architecture review completed
- [ ] Security review completed

## Publication
- [ ] Release created
- [ ] Published to package registry
- [ ] Documentation updated

---

# Common Extraction Patterns

## Pattern 1: Configuration Injection

**Problem:** Hard-coded configuration values

**Solution:** Inject configuration through constructor

```php
// Before
class Component {
    private $timeout = 30;
}

// After
class Component {
    private $timeout;
    
    public function __construct(array $config = []) {
        $this->timeout = $config['timeout'] ?? 30;
    }
}
```

## Pattern 2: Dependency Injection

**Problem:** Direct instantiation of dependencies

**Solution:** Inject dependencies through constructor

```php
// Before
class Component {
    private $db;
    
    public function __construct() {
        $this->db = new MySQLConnection();
    }
}

// After
class Component {
    private $db;
    
    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
    }
}
```

## Pattern 3: Context Parameter

**Problem:** Component assumes specific context

**Solution:** Add context parameter

```php
// Before
class Component {
    public function process($data) {
        $tenantId = $this->getCurrentTenant();
        // Process with tenant ID
    }
}

// After
class Component {
    public function process($data, $context = []) {
        $tenantId = $context['tenant_id'] ?? null;
        // Process with tenant ID
    }
}
```

## Pattern 4: Interface Abstraction

**Problem:** Concrete dependency coupling

**Solution:** Create interface

```php
// Before
class Component {
    private $db;
    
    public function __construct(MySQLConnection $db) {
        $this->db = $db;
    }
}

// After
interface DatabaseInterface {
    public function query($sql, $params = []);
}

class Component {
    private $db;
    
    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
    }
}
```

---

# Document End

**Document ID:** ESAMF-EXTRACTION-001

**Version:** 1.0
