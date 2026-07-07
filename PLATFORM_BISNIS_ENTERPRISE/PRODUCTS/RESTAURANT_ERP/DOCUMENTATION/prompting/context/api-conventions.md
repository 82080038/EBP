# RESTAURANT_ERP API Conventions

## API Overview

RESTAURANT_ERP follows RESTful API conventions with standardized responses, authentication, and error handling.

## Base URL

- **Development**: `http://localhost:8000/api/v1`
- **Production**: `https://api.restaurant-erp.com/api/v1`

## Versioning

- **Current Version**: v1
- **URL Pattern**: `/api/v{version}/{resource}`
- **Backward Compatibility**: Maintained within major versions

## Authentication

### JWT Token Authentication

**Login Endpoint:**
```
POST /api/v1/auth/login
```

**Request Body:**
```json
{
    "username": "admin",
    "password": "admin123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "Bearer",
        "expires_in": 3600,
        "user": {
            "user_id": 1,
            "username": "admin",
            "role": "manager",
            "tenant_id": 1
        }
    }
}
```

**Using the Token:**
```
Authorization: Bearer {access_token}
```

### Token Refresh

**Refresh Endpoint:**
```
POST /api/v1/auth/refresh
```

**Request Headers:**
```
Authorization: Bearer {access_token}
```

## Resource Naming

### URL Patterns

- **Plural Nouns**: `/users`, `/orders`, `/products`
- **Kebab-case**: `/menu-categories`, `/order-details`
- **Hierarchical**: `/orders/{id}/items`, `/users/{id}/orders`

### Example Endpoints

```
GET    /api/v1/users                    # List all users
GET    /api/v1/users/{id}               # Get specific user
POST   /api/v1/users                    # Create user
PUT    /api/v1/users/{id}               # Update user (full)
PATCH  /api/v1/users/{id}               # Update user (partial)
DELETE /api/v1/users/{id}               # Delete user

GET    /api/v1/orders/{id}/items        # Get order items
POST   /api/v1/orders/{id}/items        # Add order item
PUT    /api/v1/orders/{id}/items/{itemId} # Update order item
DELETE /api/v1/orders/{id}/items/{itemId} # Delete order item
```

## HTTP Methods

### GET
- **Purpose**: Retrieve resources
- **Idempotent**: Yes
- **Cacheable**: Yes
- **Body**: No

### POST
- **Purpose**: Create resources
- **Idempotent**: No
- **Cacheable**: No
- **Body**: Yes

### PUT
- **Purpose**: Update resources (full replacement)
- **Idempotent**: Yes
- **Cacheable**: No
- **Body**: Yes

### PATCH
- **Purpose**: Update resources (partial update)
- **Idempotent**: No
- **Cacheable**: No
- **Body**: Yes

### DELETE
- **Purpose**: Delete resources
- **Idempotent**: Yes
- **Cacheable**: No
- **Body**: No

## Request Format

### Content-Type
```
Content-Type: application/json
```

### Request Body
```json
{
    "field1": "value1",
    "field2": "value2",
    "nested": {
        "field3": "value3"
    }
}
```

### Query Parameters
```
GET /api/v1/users?page=1&limit=20&sort=name&order=asc
```

### Filtering
```
GET /api/v1/users?status=ACTIVE&role=manager
```

### Searching
```
GET /api/v1/users?search=john
```

## Response Format

### Success Response
```json
{
    "success": true,
    "message": "Success message",
    "data": {
        "field": "value"
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": "Error description"
    }
}
```

### Validation Error Response
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": "Email is required",
        "password": "Password must be at least 8 characters"
    }
}
```

### Paginated Response
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "data": [
            { /* item 1 */ },
            { /* item 2 */ }
        ],
        "pagination": {
            "total": 100,
            "page": 1,
            "limit": 20,
            "total_pages": 5
        }
    }
}
```

## HTTP Status Codes

### Success Codes
- **200 OK**: Request successful
- **201 Created**: Resource created successfully
- **204 No Content**: Request successful, no content returned

### Client Error Codes
- **400 Bad Request**: Invalid request
- **401 Unauthorized**: Authentication required
- **403 Forbidden**: Authorization failed
- **404 Not Found**: Resource not found
- **405 Method Not Allowed**: HTTP method not allowed
- **409 Conflict**: Resource conflict
- **422 Unprocessable Entity**: Validation error
- **429 Too Many Requests**: Rate limit exceeded

### Server Error Codes
- **500 Internal Server Error**: Server error
- **503 Service Unavailable**: Service temporarily unavailable

## Error Handling

### Error Response Structure
```json
{
    "success": false,
    "message": "Human-readable error message",
    "errors": {
        "field": "Detailed error message"
    },
    "code": "ERROR_CODE"
}
```

### Common Error Codes
- **AUTH_REQUIRED**: Authentication required
- **AUTH_FAILED**: Authentication failed
- **PERMISSION_DENIED**: Permission denied
- **VALIDATION_ERROR**: Validation error
- **NOT_FOUND**: Resource not found
- **CONFLICT**: Resource conflict
- **RATE_LIMIT_EXCEEDED**: Rate limit exceeded
- **SERVER_ERROR**: Internal server error

## Pagination

### Query Parameters
- **page**: Page number (default: 1)
- **limit**: Items per page (default: 20, max: 100)
- **sort**: Sort field
- **order**: Sort order (asc, desc)

### Example
```
GET /api/v1/users?page=2&limit=10&sort=name&order=asc
```

### Response Headers
```
X-Total-Count: 100
X-Page-Count: 10
X-Current-Page: 2
X-Per-Page: 10
```

## Filtering

### Field Filtering
```
GET /api/v1/users?status=ACTIVE&role=manager
```

### Range Filtering
```
GET /api/v1/orders?created_at[gte]=2024-01-01&created_at[lte]=2024-12-31
```

### Multiple Values
```
GET /api/v1/users?role[]=manager&role[]=admin
```

## Sorting

### Single Field
```
GET /api/v1/users?sort=name&order=asc
```

### Multiple Fields
```
GET /api/v1/users?sort[]=name&sort[]=created_at&order[]=asc&order[]=desc
```

## Searching

### Full-Text Search
```
GET /api/v1/users?search=john
```

### Field-Specific Search
```
GET /api/v1/users?name=john&email=john@example.com
```

## Field Selection

### Partial Response
```
GET /api/v1/users?fields=id,name,email
```

### Excluding Fields
```
GET /api/v1/users?exclude=password,token
```

## Bulk Operations

### Bulk Create
```
POST /api/v1/users/bulk
```

**Request Body:**
```json
{
    "users": [
        { "name": "User 1", "email": "user1@example.com" },
        { "name": "User 2", "email": "user2@example.com" }
    ]
}
```

### Bulk Update
```
PUT /api/v1/users/bulk
```

**Request Body:**
```json
{
    "ids": [1, 2, 3],
    "updates": {
        "status": "ACTIVE"
    }
}
```

### Bulk Delete
```
DELETE /api/v1/users/bulk
```

**Request Body:**
```json
{
    "ids": [1, 2, 3]
}
```

## Rate Limiting

### Rate Limit Headers
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1609459200
```

### Rate Limit Exceeded
```json
{
    "success": false,
    "message": "Rate limit exceeded",
    "errors": {
        "rate_limit": "Too many requests"
    }
}
```

## CORS

### CORS Headers
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Max-Age: 86400
```

## Versioning Strategy

### URL Versioning
```
/api/v1/users
/api/v2/users
```

### Header Versioning (Alternative)
```
Accept: application/vnd.restaurant-erp.v1+json
```

## Webhooks

### Webhook Registration
```
POST /api/v1/webhooks
```

**Request Body:**
```json
{
    "url": "https://example.com/webhook",
    "events": ["order.created", "order.updated"],
    "secret": "webhook_secret"
}
```

### Webhook Payload
```json
{
    "event": "order.created",
    "data": {
        "order_id": 123,
        "order_number": "ORD-001"
    },
    "timestamp": "2024-01-01T00:00:00Z"
}
```

## Best Practices

### Request Design
- Use plural nouns for resource names
- Use kebab-case for multi-word resources
- Include appropriate HTTP methods
- Use query parameters for filtering, sorting, pagination
- Use request body for data modification

### Response Design
- Always return consistent response format
- Include appropriate HTTP status codes
- Provide meaningful error messages
- Include pagination metadata for list endpoints
- Use ISO 8601 format for dates

### Security
- Always use HTTPS in production
- Implement rate limiting
- Validate all inputs
- Sanitize all outputs
- Never expose sensitive information
- Implement CORS properly

### Performance
- Implement caching for GET endpoints
- Use pagination for large result sets
- Optimize database queries
- Use compression for large responses
- Implement ETags for conditional requests

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
