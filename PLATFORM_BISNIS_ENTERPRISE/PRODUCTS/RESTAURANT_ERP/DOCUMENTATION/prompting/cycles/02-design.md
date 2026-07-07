# Cycle 2: Design Phase

## Purpose

Create detailed technical design based on analysis phase outputs. Define architecture, data models, API contracts, and implementation strategy.

## REASONS Canvas

### R - Requirements

**Design Requirements:**
- Based on analysis phase outputs
- Must support multi-tenant architecture
- Must follow REST API conventions
- Must implement security patterns (JWT, RBAC)
- Must support Indonesian/English languages
- Must consider offline capability

**Design DoD:**
- Architecture diagram created
- Data model defined
- API endpoints specified
- Security design documented
- Error handling strategy defined
- Performance considerations addressed

### E - Entities

**Entity Design:**
- Define entity properties and types
- Specify validation rules
- Define relationships (one-to-one, one-to-many, many-to-many)
- Map entity lifecycle states
- Identify audit trail requirements

**Database Design:**
- Table structure (columns, types, constraints)
- Indexes for performance
- Foreign key relationships
- Soft delete patterns
- Timestamp fields (created_at, updated_at, deleted_at)

### A - Approach

**Design Strategy:**
- Follow Service Repository Pattern
- Use existing core components (Database, JWT, Response, etc.)
- Leverage middleware stack (Auth, Permission, Tenant, Validation, RateLimit)
- Integrate with engines (Stock, Kitchen, Accounting)
- Maintain consistency with existing modules

**Architecture Layers:**
1. **Controller Layer** - HTTP request handling, validation
2. **Service Layer** - Business logic, transaction management
3. **Repository Layer** - Database operations
4. **Model Layer** - Data representation
5. **Middleware** - Cross-cutting concerns
6. **Engines** - Business engines integration

### S - Structure

**Module Structure:**
```
BACKEND/modules/[ModuleName]/
├── Controllers/
│   └── [ModuleName]Controller.php
├── Services/
│   └── [ModuleName]Service.php
├── Repositories/
│   └── [ModuleName]Repository.php
├── Models/
│   └── [ModuleName].php
└── routes/
    └── [ModuleName].php
```

**API Structure:**
- Base path: `/api/v1/[module]`
- HTTP methods: GET, POST, PUT, DELETE
- Response format: Standardized JSON (success/error)
- Authentication: Bearer token (JWT)
- Tenant isolation: Via middleware

**Database Structure:**
- Follow naming conventions (snake_case)
- Use tenant_id for multi-tenant isolation
- Include audit fields (created_by, updated_by)
- Soft delete support (deleted_at)
- Indexes for query optimization

### O - Operations

**Design Steps:**

1. **Architecture Design**
   - Create module architecture diagram
   - Define component interactions
   - Map data flow
   - Identify integration points

2. **Data Model Design**
   - Design database tables
   - Define relationships
   - Specify indexes
   - Document constraints

3. **API Design**
   - Define endpoint routes
   - Specify request/response formats
   - Document authentication requirements
   - Define error responses

4. **Security Design**
   - Define permission requirements
   - Specify input validation
   - Document security controls
   - Plan audit logging

5. **Performance Design**
   - Identify query optimization needs
   - Plan caching strategy
   - Define pagination approach
   - Consider batch operations

6. **Error Handling Design**
   - Define error codes
   - Specify error messages (ID/EN)
   - Plan exception handling
   - Document recovery strategies

### N - Norms

**Design Standards:**
- Follow PHP 8.x best practices
- Use PSR-12 coding style
- Document with PHPDoc
- Use type hints
- Follow REST API conventions
- Implement proper error handling
- Include logging for debugging

**Naming Conventions:**
- Classes: PascalCase (e.g., OrderController)
- Methods: camelCase (e.g., createOrder)
- Variables: camelCase (e.g., $orderId)
- Database: snake_case (e.g., order_id)
- Constants: UPPER_SNAKE_CASE (e.g., MAX_RETRY)

**Documentation Standards:**
- PHPDoc for all classes and methods
- Inline comments for complex logic
- API documentation in markdown
- Database schema documentation

### S - Safeguards

**Non-negotiable Design Constraints:**
- Must use existing Database.php for connections
- Must use JWT.php for authentication
- Must use Response.php for API responses
- Must implement tenant isolation via TenantMiddleware
- Must implement permission checks via PermissionMiddleware
- Must use Transaction.php for data consistency
- Must include audit logging via Audit.php

**Security Constraints:**
- All endpoints must be authenticated (except public ones)
- Input validation via ValidationMiddleware
- SQL injection prevention (PDO prepared statements)
- XSS prevention (output escaping)
- CSRF protection for state-changing operations
- Rate limiting via RateLimitMiddleware

**Performance Constraints:**
- Database queries must be optimized
- Use indexes for frequently queried fields
- Implement pagination for list endpoints
- Cache frequently accessed data
- Avoid N+1 query problems

## Design Checklist

- [ ] Architecture diagram created
- [ ] Module structure defined
- [ ] Database tables designed
- [ ] Entity relationships mapped
- [ ] API endpoints specified
- [ ] Request/response formats defined
- [ ] Authentication requirements documented
- [ ] Permission requirements defined
- [ ] Input validation specified
- [ ] Error handling strategy defined
- [ ] Security controls documented
- [ ] Performance considerations addressed
- [ ] Audit logging planned
- [ ] Multi-tenant isolation ensured
- [ ] Language support (ID/EN) planned

## Output Format

After design, produce:

1. **Architecture Document**
   - Module architecture diagram
   - Component interactions
   - Data flow diagram

2. **Data Model Document**
   - Database schema (CREATE TABLE statements)
   - Entity relationships
   - Index definitions

3. **API Specification**
   - Endpoint list with methods
   - Request/response examples
   - Authentication requirements
   - Error response formats

4. **Security Design**
   - Permission matrix
   - Validation rules
   - Security controls

5. **Implementation Guide**
   - Step-by-step implementation plan
   - Dependencies and order
   - Testing strategy

## Next Steps

After completing design:
1. Review design with team
2. Validate against requirements
3. Proceed to Implementation Phase (03-implementation.md)

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
