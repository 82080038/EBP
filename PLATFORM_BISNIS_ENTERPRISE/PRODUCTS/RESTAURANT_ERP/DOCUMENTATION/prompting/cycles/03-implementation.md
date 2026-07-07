# Cycle 3: Implementation Phase

## Purpose

Generate production-ready code based on design phase outputs. Implement controllers, services, repositories, models, and routes following project conventions.

## REASONS Canvas

### R - Requirements

**Implementation Requirements:**
- Follow design specifications exactly
- Use existing core components
- Implement all security controls
- Include comprehensive error handling
- Add audit logging
- Support Indonesian/English messages

**Implementation DoD:**
- All components implemented
- Code follows project standards
- Security controls in place
- Error handling complete
- Audit logging implemented
- Code documented with PHPDoc
- No syntax errors
- No obvious bugs

### E - Entities

**Entity Implementation:**
- Create Model classes with properties
- Implement validation rules
- Define relationship methods
- Add type hints
- Include PHPDoc comments

**Example Model Structure:**
```php
<?php

class Order
{
    private $order_id;
    private $tenant_id;
    private $customer_id;
    private $table_id;
    private $order_status;
    private $total_amount;
    private $created_at;
    private $updated_at;
    
    // Getters and setters
    // Validation methods
    // Relationship methods
}
```

### A - Approach

**Implementation Strategy:**
1. Start with database migration (if needed)
2. Create Model class
3. Create Repository class
4. Create Service class
5. Create Controller class
6. Define routes
7. Add middleware configuration
8. Test each component

**Code Generation Order:**
1. Database schema (migration file)
2. Model (data representation)
3. Repository (data access)
4. Service (business logic)
5. Controller (HTTP handling)
6. Routes (URL mapping)
7. Tests (unit and integration)

### S - Structure

**File Structure:**
```
BACKEND/modules/[ModuleName]/
├── Controllers/
│   └── [ModuleName]Controller.php  # HTTP request handling
├── Services/
│   └── [ModuleName]Service.php      # Business logic
├── Repositories/
│   └── [ModuleName]Repository.php  # Database operations
├── Models/
│   └── [ModuleName].php            # Data model
└── routes/
    └── [ModuleName].php             # Route definitions
```

**Code Structure Pattern:**

**Controller:**
```php
<?php

class [ModuleName]Controller
{
    private $service;
    
    public function __construct()
    {
        $this->service = new [ModuleName]Service();
    }
    
    public function index() { /* List all */ }
    public function show($id) { /* Show one */ }
    public function store() { /* Create */ }
    public function update($id) { /* Update */ }
    public function destroy($id) { /* Delete */ }
}
```

**Service:**
```php
<?php

class [ModuleName]Service
{
    private $repository;
    private $db;
    
    public function __construct()
    {
        $this->repository = new [ModuleName]Repository();
        $this->db = Database::getInstance();
    }
    
    public function getAll($tenantId) { /* Business logic */ }
    public function getById($id, $tenantId) { /* Business logic */ }
    public function create($data, $tenantId, $userId) { /* With transaction */ }
    public function update($id, $data, $tenantId, $userId) { /* With transaction */ }
    public function delete($id, $tenantId, $userId) { /* With transaction */ }
}
```

**Repository:**
```php
<?php

class [ModuleName]Repository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    public function findAll($tenantId) { /* Database query */ }
    public function findById($id, $tenantId) { /* Database query */ }
    public function create($data) { /* Database insert */ }
    public function update($id, $data) { /* Database update */ }
    public function delete($id) { /* Database delete */ }
}
```

### O - Operations

**Implementation Steps:**

1. **Database Migration** (if needed)
   - Create migration file in DATABASE/
   - Define table structure
   - Add indexes and constraints
   - Test migration

2. **Model Implementation**
   - Create Model class
   - Define properties with types
   - Implement getters/setters
   - Add validation methods
   - Include PHPDoc

3. **Repository Implementation**
   - Create Repository class
   - Implement CRUD methods
   - Use PDO prepared statements
   - Add tenant isolation
   - Handle errors gracefully

4. **Service Implementation**
   - Create Service class
   - Implement business logic
   - Use Transaction for data consistency
   - Integrate with engines (if needed)
   - Add audit logging

5. **Controller Implementation**
   - Create Controller class
   - Implement HTTP methods
   - Add input validation
   - Call service methods
   - Return standardized responses

6. **Routes Implementation**
   - Create routes file
   - Define endpoint mappings
   - Add middleware configuration
   - Include authentication
   - Add permission checks

7. **Integration**
   - Load routes in main router
   - Register middleware
   - Test endpoints
   - Verify tenant isolation
   - Check permissions

### N - Norms

**Coding Standards:**
- PHP 8.x syntax
- PSR-12 coding style
- Type hints for all parameters and return types
- PHPDoc for all classes and methods
- Meaningful variable names
- Maximum line length: 120 characters
- 4 spaces for indentation

**Security Standards:**
- Use PDO prepared statements
- Validate all inputs
- Sanitize outputs
- Never trust client data
- Implement rate limiting
- Log security events

**Error Handling Standards:**
- Use try-catch blocks
- Return standardized error responses
- Log errors for debugging
- Never expose sensitive information
- Provide user-friendly error messages (ID/EN)

**Documentation Standards:**
- PHPDoc for all public methods
- Inline comments for complex logic
- Explain business rules
- Document edge cases
- Include examples

### S - Safeguards

**Non-negotiable Implementation Rules:**
- MUST use Database::getInstance() for connections
- MUST use JWT for authentication
- MUST use Response class for API responses
- MUST implement tenant isolation in all queries
- MUST use Transaction for multi-table operations
- MUST include audit logging for data changes
- MUST validate all inputs
- MUST handle errors gracefully

**Security Implementation Rules:**
- All endpoints except login must require authentication
- Implement permission checks via PermissionMiddleware
- Use parameterized queries (no string concatenation)
- Validate input types and ranges
- Sanitize output to prevent XSS
- Implement CSRF protection for state changes
- Rate limit sensitive operations

**Performance Implementation Rules:**
- Use indexes for WHERE, JOIN, ORDER BY
- Implement pagination for list endpoints
- Avoid SELECT * (specify columns)
- Use LIMIT for large result sets
- Cache frequently accessed data
- Optimize N+1 queries

## Implementation Checklist

- [ ] Database migration created (if needed)
- [ ] Model class implemented
- [ ] Repository class implemented
- [ ] Service class implemented
- [ ] Controller class implemented
- [ ] Routes defined
- [ ] Middleware configured
- [ ] Authentication implemented
- [ ] Permission checks added
- [ ] Input validation added
- [ ] Error handling implemented
- [ ] Audit logging added
- [ ] PHPDoc comments added
- [ ] Code follows PSR-12
- [ ] Type hints used
- [ ] No syntax errors
- [ ] Tenant isolation verified
- [ ] Security controls verified

## Code Quality Checks

**Before proceeding to testing:**
1. Run PHP syntax check: `php -l filename.php`
2. Check for undefined variables
3. Verify all database queries use prepared statements
4. Confirm all endpoints have authentication
5. Verify tenant isolation in all queries
6. Check error handling coverage
7. Validate PHPDoc completeness

## Output Format

After implementation, produce:

1. **Source Code**
   - Model class
   - Repository class
   - Service class
   - Controller class
   - Routes file
   - Migration file (if needed)

2. **Integration Points**
   - Routes registered
   - Middleware configured
   - Dependencies loaded

3. **Documentation**
   - PHPDoc comments
   - Inline comments
   - Usage examples

## Next Steps

After completing implementation:
1. Run syntax checks
2. Verify code quality
3. Proceed to Testing Phase (04-testing.md)

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
