# RESTAURANT_ERP Coding Standards

## PHP Coding Standards

### General Standards
- **PHP Version**: PHP 8.x
- **Coding Style**: PSR-12
- **File Encoding**: UTF-8
- **Line Ending**: LF (Unix)
- **Indentation**: 4 spaces (no tabs)
- **Max Line Length**: 120 characters

### File Naming
- **Classes**: PascalCase (e.g., `OrderController.php`)
- **Functions/Methods**: camelCase (e.g., `createOrder()`)
- **Variables**: camelCase (e.g., `$orderId`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `MAX_RETRY`)

### Class Structure
```php
<?php

/**
 * Class Description
 * 
 * @package EBP\Modules\[Module]
 * @version 1.0.0
 */

class ClassName
{
    // Constants
    const CONSTANT_NAME = 'value';
    
    // Properties
    private $property;
    protected $anotherProperty;
    public $publicProperty;
    
    // Constructor
    public function __construct()
    {
        // Initialization
    }
    
    // Public methods
    public function publicMethod()
    {
        // Implementation
    }
    
    // Protected methods
    protected function protectedMethod()
    {
        // Implementation
    }
    
    // Private methods
    private function privateMethod()
    {
        // Implementation
    }
    
    // Getters and setters
    public function getProperty(): string
    {
        return $this->property;
    }
    
    public function setProperty(string $value): void
    {
        $this->property = $value;
    }
}
```

### Method Declaration
```php
public function methodName(
    string $param1,
    int $param2,
    array $param3 = []
): array
{
    // Implementation
}
```

### Type Hints
- **Required**: All parameters must have type hints
- **Required**: All return types must be declared
- **Required**: Nullable types must use `?Type` syntax
- **Required**: Use `void` for methods with no return value

### PHPDoc Comments
```php
/**
 * Method description
 * 
 * @param string $param1 Parameter description
 * @param int $param2 Parameter description
 * @return array Return value description
 * @throws Exception Exception description
 */
public function methodName(string $param1, int $param2): array
{
    // Implementation
}
```

## Database Coding Standards

### Table Naming
- **Convention**: snake_case, plural (e.g., `users`, `orders`)
- **Prefix**: No prefix (use tenant_id for multi-tenant)
- **Length**: Maximum 64 characters

### Column Naming
- **Convention**: snake_case (e.g., `user_id`, `created_at`)
- **Primary Keys**: `{table}_id` (e.g., `user_id`, `order_id`)
- **Foreign Keys**: `{referenced_table}_id` (e.g., `user_id`, `order_id`)
- **Boolean**: `is_{condition}` or `has_{condition}` (e.g., `is_active`, `has_discount`)

### Column Types
- **IDs**: `BIGINT UNSIGNED AUTO_INCREMENT`
- **Foreign Keys**: `BIGINT UNSIGNED`
- **Names**: `VARCHAR(255)`
- **Emails**: `VARCHAR(100)`
- **Phone**: `VARCHAR(20)`
- **Descriptions**: `TEXT`
- **Prices**: `DECIMAL(10,2)`
- **Quantities**: `INT`
- **Dates**: `DATE`
- **DateTimes**: `DATETIME` or `TIMESTAMP`
- **Booleans**: `TINYINT(1)`
- **Enums**: `ENUM('VALUE1', 'VALUE2', ...)`

### Standard Columns
```sql
-- Audit fields
created_by BIGINT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_by BIGINT,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
deleted_at TIMESTAMP NULL,

-- Multi-tenant field
tenant_id BIGINT UNSIGNED NOT NULL,

-- Status field
status ENUM('ACTIVE', 'INACTIVE', 'BLOCKED') DEFAULT 'ACTIVE'
```

### Index Naming
- **Convention**: `idx_{table}_{column}` (e.g., `idx_users_email`)
- **Unique Index**: `uk_{table}_{column}` (e.g., `uk_users_email`)
- **Foreign Key Index**: `idx_{table}_{fk_column}` (e.g., `idx_orders_user_id`)

### Foreign Key Naming
- **Convention**: `fk_{table}_{column}` (e.g., `fk_orders_user_id`)

## API Coding Standards

### Endpoint Naming
- **Convention**: kebab-case, plural (e.g., `/api/v1/users`, `/api/v1/orders`)
- **Version**: `/api/v1/` prefix
- **RESTful**: Follow REST conventions

### HTTP Methods
- **GET**: Retrieve resources
- **POST**: Create resources
- **PUT**: Update resources (full update)
- **PATCH**: Update resources (partial update)
- **DELETE**: Delete resources

### Response Format
```json
{
    "success": true,
    "message": "Success message",
    "data": {
        "field": "value"
    }
}
```

### Error Response Format
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": "Error description"
    }
}
```

### HTTP Status Codes
- **200 OK**: Successful request
- **201 Created**: Resource created
- **400 Bad Request**: Invalid request
- **401 Unauthorized**: Authentication required
- **403 Forbidden**: Authorization failed
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation error
- **500 Internal Server Error**: Server error

## JavaScript Coding Standards

### General Standards
- **Style**: Modern JavaScript (ES6+)
- **Indentation**: 4 spaces
- **Quotes**: Single quotes for strings
- **Semicolons**: Required

### Variable Naming
- **Variables**: camelCase (e.g., `orderId`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `MAX_RETRY`)
- **Functions**: camelCase (e.g., `createOrder()`)
- **Classes**: PascalCase (e.g., `OrderManager`)

### Function Declaration
```javascript
function functionName(param1, param2) {
    // Implementation
}
```

### Arrow Functions
```javascript
const functionName = (param1, param2) => {
    // Implementation
};
```

### Async/Await
```javascript
async function functionName() {
    try {
        const result = await someAsyncOperation();
        return result;
    } catch (error) {
        console.error(error);
        throw error;
    }
}
```

## HTML Coding Standards

### General Standards
- **Doctype**: HTML5
- **Encoding**: UTF-8
- **Indentation**: 4 spaces
- **Quotes**: Double quotes for attributes

### Element Naming
- **IDs**: kebab-case (e.g., `user-form`)
- **Classes**: kebab-case (e.g., `btn-primary`)
- **Data Attributes**: kebab-case (e.g., `data-user-id`)

### Structure
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title</title>
</head>
<body>
    <div id="app">
        <!-- Content -->
    </div>
</body>
</html>
```

## CSS Coding Standards

### General Standards
- **Style**: BEM methodology (Block Element Modifier)
- **Indentation**: 4 spaces
- **Quotes**: Single quotes for strings

### Class Naming (BEM)
```css
/* Block */
.card { }

/* Element */
.card__title { }

/* Modifier */
.card--highlight { }
.card__title--large { }
```

### Selector Organization
```css
/* Base styles */
body { }

/* Layout */
.container { }

/* Components */
.card { }
.button { }

/* Utilities */
.text-center { }
.mt-4 { }
```

## Security Coding Standards

### Input Validation
- **Required**: Validate all user inputs
- **Required**: Use ValidationMiddleware
- **Required**: Sanitize outputs
- **Required**: Never trust client data

### SQL Queries
- **Required**: Use PDO prepared statements
- **Forbidden**: Never concatenate SQL strings
- **Required**: Use parameterized queries

### Authentication
- **Required**: Use JWT for authentication
- **Required**: Validate tokens on every request
- **Required**: Implement token expiration
- **Required**: Use secure token storage

### Authorization
- **Required**: Check permissions for all protected endpoints
- **Required**: Use PermissionMiddleware
- **Required**: Implement role-based access control

### Error Handling
- **Required**: Never expose sensitive information in errors
- **Required**: Log errors for debugging
- **Required**: Return user-friendly error messages
- **Required**: Use standardized error responses

## Testing Coding Standards

### Test Naming
- **Unit Tests**: `test{MethodName}{Scenario}` (e.g., `testCreateSuccess`)
- **Integration Tests**: `test{Endpoint}{Method}{Scenario}` (e.g., `testUsersPostSuccess`)
- **E2E Tests**: `{Feature}{Scenario}` (e.g., `UserLoginSuccess`)

### Test Structure (AAA)
```php
public function testMethodName()
{
    // Arrange
    $input = ['field' => 'value'];
    
    // Act
    $result = $this->service->methodName($input);
    
    // Assert
    $this->assertTrue($result['success']);
}
```

### Test Coverage
- **Required**: >80% code coverage
- **Required**: Test all public methods
- **Required**: Test all API endpoints
- **Required**: Test both success and failure cases

## Documentation Standards

### PHPDoc
- **Required**: All classes must have PHPDoc
- **Required**: All public methods must have PHPDoc
- **Required**: Include @param for all parameters
- **Required**: Include @return for return values
- **Required**: Include @throws for exceptions

### Code Comments
- **Required**: Comment complex logic
- **Required**: Explain business rules
- **Required**: Document edge cases
- **Required**: Include examples where helpful

### README Files
- **Required**: Each module must have README.md
- **Required**: Include installation instructions
- **Required**: Include usage examples
- **Required**: Include API documentation

## Git Standards

### Commit Messages
- **Format**: `{type}: {description}`
- **Types**: feat, fix, docs, style, refactor, test, chore
- **Example**: `feat: add user management module`

### Branch Naming
- **Feature**: `feature/{feature-name}`
- **Bugfix**: `bugfix/{bug-description}`
- **Hotfix**: `hotfix/{hotfix-description}`

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
