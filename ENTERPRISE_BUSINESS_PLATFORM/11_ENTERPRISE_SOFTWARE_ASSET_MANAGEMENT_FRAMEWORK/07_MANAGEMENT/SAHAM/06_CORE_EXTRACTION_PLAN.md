# SAHAM - Core Extraction Plan

**Document ID:** ESAMF-SAHAM-006

**Version:** 1.0

**Purpose:** Plan for extracting core components from SAHAM repository

---

# 1. Extraction Overview

This document outlines the plan for extracting core components from the SAHAM repository for migration to EBP Core (06_CORE_CODE).

## Extraction Targets

1. Authentication System
2. Authorization (RBAC)
3. Audit Trail
4. Configuration Management
5. Error Handling

---

# 2. Authentication System Extraction

## 2.1 Component Analysis

```
Component: Authentication System

Current Location: modules/Auth/

Files to Extract:
- AuthController.php
- AuthService.php
- AuthMiddleware.php
- User.php (Model)
- Session.php (Model)
- Token.php (Model)

Dependencies:
- Database Service
- Session Service
- Configuration Service
- Audit Service
```

## 2.2 Extraction Steps

### Step 1: Isolate Component

```bash
# Create extraction branch
git checkout -b extract/authentication

# Copy component files to temporary location
mkdir -p temp/auth
cp modules/Auth/*.php temp/auth/
cp modules/Auth/models/*.php temp/auth/
```

### Step 2: Remove Repository-Specific Code

```php
// BEFORE (restaurant-specific)
class AuthService {
    private $restaurantId;
    
    public function login($username, $password) {
        // Restaurant-specific logic
        $restaurant = $this->getRestaurant();
        // ...
    }
}

// AFTER (generic)
class AuthService {
    public function login($username, $password, $context = []) {
        // Generic logic
        // Context can include tenant ID, etc.
    }
}
```

### Step 3: Replace Hard-Coded Values

```php
// BEFORE
private $tokenExpiry = 3600; // 1 hour

// AFTER
private $tokenExpiry;
public function __construct(Config $config) {
    $this->tokenExpiry = $config->get('auth.token_expiry', 3600);
}
```

### Step 4: Replace Direct Dependencies

```php
// BEFORE
class AuthService {
    private $db;
    
    public function __construct() {
        $this->db = new MySQLConnection();
    }
}

// AFTER
class AuthService {
    private $db;
    
    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
    }
}
```

### Step 5: Add Configuration Options

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

### Step 6: Apply EBP Standards

- PSR-12 coding standards
- EBP naming conventions
- EBP error handling
- EBP logging standards
- EBP documentation standards

### Step 7: Create Tests

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
    
    // More tests...
}
```

### Step 8: Document Component

Create comprehensive documentation:
- Purpose and scope
- Configuration options
- API documentation
- Usage examples
- Migration guide
- Troubleshooting

### Step 9: Code Review

Submit for code review:
- Peer review
- Architecture review
- Security review
- Performance review

### Step 10: Publish

```bash
# Create release
git tag v1.0.0
git push origin v1.0.0

# Publish to package registry
composer publish
```

## 2.3 Estimated Effort

```
Extraction: 2 days
Refactoring: 3 days
Testing: 2 days
Documentation: 1 day
Code Review: 1 day
Total: 9 days
```

---

# 3. Authorization (RBAC) Extraction

## 3.1 Component Analysis

```
Component: Authorization (RBAC)

Current Location: modules/Auth/

Files to Extract:
- RoleService.php
- PermissionService.php
- RBACMiddleware.php
- Role.php (Model)
- Permission.php (Model)
- RolePermission.php (Model)

Dependencies:
- Database Service
- Configuration Service
- Audit Service
```

## 3.2 Extraction Steps

Follow similar steps as Authentication System:

1. Isolate component
2. Remove repository-specific code
3. Replace hard-coded values
4. Replace direct dependencies
5. Add configuration options
6. Apply EBP standards
7. Create tests
8. Document component
9. Code review
10. Publish

## 3.3 Estimated Effort

```
Extraction: 2 days
Refactoring: 3 days
Testing: 2 days
Documentation: 1 day
Code Review: 1 day
Total: 9 days
```

---

# 4. Audit Trail Extraction

## 4.1 Component Analysis

```
Component: Audit Trail

Current Location: core/Audit.php

Files to Extract:
- Audit.php
- AuditService.php
- AuditModel.php

Dependencies:
- Database Service
```

## 4.2 Extraction Steps

1. Isolate component
2. Remove repository-specific code
3. Add configuration options
4. Apply EBP standards
5. Create tests
6. Document component
7. Code review
8. Publish

## 4.3 Estimated Effort

```
Extraction: 1 day
Refactoring: 1 day
Testing: 1 day
Documentation: 0.5 day
Code Review: 0.5 day
Total: 4 days
```

---

# 5. Configuration Management Extraction

## 5.1 Component Analysis

```
Component: Configuration Management

Current Location: config/

Files to Extract:
- Config.php
- ConfigService.php
- ConfigLoader.php

Dependencies:
- Database Service
- File System Service
```

## 5.2 Extraction Steps

1. Isolate component
2. Remove repository-specific code
3. Add support for multiple sources (file, database, environment)
4. Apply EBP standards
5. Create tests
6. Document component
7. Code review
8. Publish

## 5.3 Estimated Effort

```
Extraction: 1 day
Refactoring: 1 day
Testing: 1 day
Documentation: 0.5 day
Code Review: 0.5 day
Total: 4 days
```

---

# 6. Error Handling Extraction

## 6.1 Component Analysis

```
Component: Error Handling

Current Location: core/

Files to Extract:
- ErrorHandler.php
- ExceptionHandler.php
- ErrorLogger.php

Dependencies:
- Logging Service
```

## 6.2 Extraction Steps

1. Isolate component
2. Remove repository-specific code
3. Add support for multiple error handlers
4. Apply EBP standards
5. Create tests
6. Document component
7. Code review
8. Publish

## 6.3 Estimated Effort

```
Extraction: 1 day
Refactoring: 1 day
Testing: 1 day
Documentation: 0.5 day
Code Review: 0.5 day
Total: 4 days
```

---

# 7. Extraction Timeline

## 7.1 Overall Timeline

```
Week 1-2: Authentication System
- Days 1-2: Extraction
- Days 3-5: Refactoring
- Days 6-7: Testing
- Day 8: Documentation
- Day 9: Code Review and Publish

Week 3: Authorization (RBAC)
- Days 1-2: Extraction
- Days 3-5: Refactoring
- Days 6-7: Testing
- Day 8: Documentation
- Day 9: Code Review and Publish

Week 4: Audit Trail & Configuration Management
- Days 1-2: Audit Trail extraction and refactoring
- Days 3-4: Audit Trail testing and documentation
- Days 5-6: Configuration Management extraction and refactoring
- Days 7-8: Configuration Management testing and documentation
- Day 9: Code Review and Publish for both

Week 5: Error Handling
- Day 1: Extraction
- Day 2: Refactoring
- Day 3: Testing
- Day 4: Documentation
- Day 5: Code Review and Publish
```

## 7.2 Total Effort

```
Authentication System: 9 days
Authorization (RBAC): 9 days
Audit Trail: 4 days
Configuration Management: 4 days
Error Handling: 4 days

Total: 30 days (6 weeks)
```

---

# 8. Resource Requirements

## 8.1 Team Composition

```
Migration Architect: 1 person (part-time)
Migration Engineer: 1 person (full-time)
Database Specialist: 1 person (part-time)
Documentation Specialist: 1 person (part-time)
```

## 8.2 Skills Required

```
Required Skills:
- PHP development
- Database design
- API design
- Testing (PHPUnit)
- Documentation
- Code review
```

---

# 9. Risk Assessment

## 9.1 Technical Risks

```
Risk: Tight coupling to repository
Mitigation: Careful dependency analysis, interface design

Risk: Hard-coded business logic
Mitigation: Configuration-based approach, context parameters

Risk: Performance regression
Mitigation: Performance testing, optimization

Risk: Security vulnerabilities
Mitigation: Security review, penetration testing
```

## 9.2 Migration Risks

```
Risk: Timeline overrun
Mitigation: Buffer time, prioritize components

Risk: Resource constraints
Mitigation: Plan resources in advance, flexible scheduling

Risk: Quality issues
Mitigation: Comprehensive testing, code review
```

---

# 10. Success Criteria

Extraction is successful when:

- Component is platform-compliant
- Component is reusable
- Documentation is complete
- Tests are comprehensive
- Performance is acceptable
- Security is verified
- Team is trained

---

# Document End

**Document ID:** ESAMF-SAHAM-006

**Version:** 1.0
