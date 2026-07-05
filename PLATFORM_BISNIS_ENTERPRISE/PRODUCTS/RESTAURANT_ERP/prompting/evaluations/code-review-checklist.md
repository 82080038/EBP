# Code Review Checklist

## Purpose

Systematic checklist for reviewing code quality, maintainability, and adherence to RESTAURANT_ERP standards.

## General Review

### File Structure
- [ ] File follows project directory structure
- [ ] File named according to conventions (PascalCase for classes, snake_case for others)
- [ ] File encoding is UTF-8
- [ ] Line endings are LF (Unix)

### Code Organization
- [ ] Code is logically organized
- [ ] Related functions are grouped together
- [ ] Clear separation of concerns
- [ ] No code duplication (DRY principle)

## PHP Code Review

### Syntax and Style
- [ ] No syntax errors (run `php -l filename.php`)
- [ ] Follows PSR-12 coding style
- [ ] Indentation is 4 spaces (no tabs)
- [ ] Maximum line length is 120 characters
- [ ] No trailing whitespace
- [ ] One statement per line

### Type Hints
- [ ] All parameters have type hints
- [ ] All return types are declared
- [ ] Nullable types use `?Type` syntax
- [ ] Void return type for methods with no return value

### PHPDoc Comments
- [ ] Class has PHPDoc comment
- [ ] All public methods have PHPDoc comments
- [ ] Protected methods have PHPDoc comments (if complex)
- [ ] @param tags for all parameters
- [ ] @return tag for return value
- [ ] @throws tag for exceptions

### Naming Conventions
- [ ] Classes: PascalCase (e.g., `OrderController`)
- [ ] Methods: camelCase (e.g., `createOrder`)
- [ ] Variables: camelCase (e.g., `$orderId`)
- [ ] Constants: UPPER_SNAKE_CASE (e.g., `MAX_RETRY`)
- [ ] Database columns: snake_case (e.g., `order_id`)

### Security
- [ ] Uses PDO prepared statements (no string concatenation)
- [ ] Inputs are validated
- [ ] Outputs are sanitized
- [ ] No hardcoded secrets/passwords
- [ ] Uses environment variables for sensitive data
- [ ] Implements authentication checks
- [ ] Implements permission checks
- [ ] Uses JWT for authentication
- [ ] Audit logging for data changes

### Error Handling
- [ ] Uses try-catch blocks for database operations
- [ ] Returns standardized error responses
- [ ] Logs errors for debugging
- [ ] Never exposes sensitive information in errors
- [ ] Provides user-friendly error messages (ID/EN)

### Database Operations
- [ ] Uses Database::getInstance() for connections
- [ ] Uses Transaction for multi-table operations
- [ ] Includes tenant_id in all tenant-specific queries
- [ ] Uses parameterized queries
- [ ] Closes database connections properly
- [ ] Handles connection errors gracefully

### Business Logic
- [ ] Business logic in Service layer
- [ ] Data access in Repository layer
- [ ] HTTP handling in Controller layer
- [ ] No business logic in Controller
- [ ] No data access in Controller
- [ ] Uses engines for complex operations (Stock, Kitchen, Accounting)

### Performance
- [ ] Database queries are optimized
- [ ] Uses indexes for WHERE, JOIN, ORDER BY
- [ ] Avoids SELECT * (specify columns)
- [ ] Implements pagination for large result sets
- [ ] Uses LIMIT for large result sets
- [ ] Caches frequently accessed data
- [ ] Avoids N+1 query problems

## JavaScript Code Review

### Syntax and Style
- [ ] No syntax errors
- [ ] Uses modern JavaScript (ES6+)
- [ ] Indentation is 4 spaces
- [ ] Single quotes for strings
- [ ] Semicolons at end of statements

### Naming Conventions
- [ ] Variables: camelCase (e.g., `orderId`)
- [ ] Constants: UPPER_SNAKE_CASE (e.g., `MAX_RETRY`)
- [ ] Functions: camelCase (e.g., `createOrder`)
- [ ] Classes: PascalCase (e.g., `OrderManager`)

### Security
- [ ] Inputs are validated
- [ ] Outputs are sanitized
- [ ] No inline sensitive data
- [ ] Uses HTTPS for API calls
- [ ] Implements CSRF protection

### Error Handling
- [ ] Uses try-catch blocks
- [ ] Handles errors gracefully
- [ ] Logs errors for debugging
- [ ] Provides user-friendly error messages

## HTML Code Review

### Structure
- [ ] Valid HTML5 doctype
- [ ] Proper nesting of elements
- [ ] All tags closed properly
- [ ] Uses semantic HTML

### Attributes
- [ ] Double quotes for attribute values
- [ ] IDs are kebab-case (e.g., `user-form`)
- [ ] Classes are kebab-case (e.g., `btn-primary`)
- [ ] Data attributes are kebab-case (e.g., `data-user-id`)

### Accessibility
- [ ] Alt text for images
- [ ] Labels for form inputs
- [ ] ARIA attributes where needed
- [ ] Keyboard navigation support

## CSS Code Review

### Style
- [ ] Follows BEM methodology
- [ ] Indentation is 4 spaces
- [ ] Single quotes for strings
- [ ] No inline styles (except dynamic)

### Naming Conventions
- [ ] Blocks: kebab-case (e.g., `.card`)
- [ ] Elements: kebab-case with double underscore (e.g., `.card__title`)
- [ ] Modifiers: kebab-case with double dash (e.g., `.card--highlight`)

### Organization
- [ ] Base styles first
- [ ] Layout styles second
- [ ] Component styles third
- [ ] Utility styles last

## Database Review

### Table Structure
- [ ] Table names are snake_case, plural
- [ ] Primary key is `{table}_id`
- [ ] Foreign keys are `{referenced_table}_id`
- [ ] Uses utf8mb4 charset
- [ ] Uses InnoDB engine

### Columns
- [ ] Appropriate data types
- [ ] NOT NULL for required fields
- [ ] DEFAULT values where appropriate
- [ ] Audit fields (created_at, updated_at, deleted_at)
- [ ] tenant_id for multi-tenant tables

### Indexes
- [ ] Primary key indexed
- [ ] Foreign keys indexed
- [ ] Indexes for frequently queried columns
- [ ] Composite indexes for multi-column queries

### Foreign Keys
- [ ] Proper foreign key constraints
- [ ] ON DELETE/UPDATE actions defined
- [ ] Referential integrity maintained

## API Review

### Endpoint Design
- [ ] Follows RESTful conventions
- [ ] Uses plural nouns for resources
- [ ] Uses kebab-case for multi-word resources
- [ ] Appropriate HTTP methods
- [ ] Versioned (/api/v1/)

### Response Format
- [ ] Consistent response format
- [ ] Success responses include success: true
- [ ] Error responses include success: false
- [ ] Appropriate HTTP status codes
- [ ] Error messages are user-friendly

### Authentication
- [ ] All protected endpoints require authentication
- [ ] JWT token validation
- [ ] Token expiration handling
- [ ] Token refresh mechanism

### Authorization
- [ ] Permission checks implemented
- [ ] Role-based access control
- [ ] Tenant isolation enforced

### Documentation
- [ ] API documentation updated
- [ ] Request/response examples provided
- [ ] Error scenarios documented

## Testing Review

### Unit Tests
- [ ] All public methods tested
- [ ] Both success and failure cases tested
- [ ] Edge cases tested
- [ ] Mock dependencies used appropriately
- [ ] Test names are descriptive

### Integration Tests
- [ ] All API endpoints tested
- [ ] Authentication tested
- [ ] Authorization tested
- [ ] Error responses tested
- [ ] Tenant isolation tested

### E2E Tests
- [ ] Critical user flows tested
- [ ] UI interactions tested
- [ ] Responsive design tested
- [ ] Cross-browser compatibility tested

### Test Coverage
- [ ] Code coverage >80%
- [ ] Critical paths covered
- [ ] Error handling covered

## Documentation Review

### Code Comments
- [ ] Complex logic explained
- [ ] Business rules documented
- [ ] Edge cases documented
- [ ] Examples provided where helpful

### README Files
- [ ] Module has README.md
- [ ] Installation instructions included
- [ ] Usage examples provided
- [ ] API documentation included

## Performance Review

### Database Performance
- [ ] Queries are optimized
- [ ] Indexes used appropriately
- [ ] No unnecessary queries
- [ ] Query execution time acceptable

### API Performance
- [ ] Response time <200ms for simple queries
- [ ] Response time <500ms for complex queries
- [ ] Pagination implemented for large datasets
- [ ] Caching used where appropriate

### Frontend Performance
- [ ] Assets minified
- [ ] Images optimized
- [ ] Lazy loading implemented
- [ ] CDN used for static assets

## Security Review

### Authentication
- [ ] Strong password requirements
- [ ] Password hashing (bcrypt)
- [ ] Token-based authentication
- [ ] Token expiration
- [ ] Session management

### Authorization
- [ ] Principle of least privilege
- [ ] Role-based access control
- [ ] Permission checks on all protected endpoints
- [ ] Audit logging for security events

### Data Protection
- [ ] Input validation
- [ ] Output sanitization
- [ ] SQL injection prevention
- [ ] XSS prevention
- [ ] CSRF protection

### Sensitive Data
- [ ] No hardcoded secrets
- [ ] Environment variables for configuration
- [ ] Encrypted data at rest
- [ ] Encrypted data in transit (HTTPS)

## Final Approval

- [ ] All checklist items passed
- [ ] No critical issues
- [ ] No major issues
- [ ] Minor issues documented
- [ ] Ready for merge

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
