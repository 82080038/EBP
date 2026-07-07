# API Endpoint Template

## Purpose

Template for creating new API endpoints following RESTAURANT_ERP conventions.

## REASONS Canvas

### R - Requirements

**Endpoint Requirements:**
- HTTP method: [GET/POST/PUT/DELETE]
- Route: `/api/v1/[route]`
- Purpose: [Describe endpoint purpose]
- Authentication: [Required/Optional]
- Permission: [PERMISSION_CODE]

### E - Entities

**Request/Response Entities:**
- Request body: [Describe request structure]
- Response body: [Describe response structure]
- Error responses: [List error scenarios]

### A - Approach

**Implementation Strategy:**
1. Define route in module routes file
2. Add controller method
3. Add service method (if needed)
4. Add repository method (if needed)
5. Add authentication/authorization
6. Add input validation
7. Add error handling
8. Add tests

### S - Structure

**Endpoint Structure:**
```php
$router->[METHOD]('/api/v1/[route]', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('[PERMISSION_CODE]');
    $controller->methodName();
});
```

### O - Operations

**Implementation Steps:**

1. **Add Route**
   - Define HTTP method
   - Define route path
   - Add authentication middleware
   - Add permission middleware
   - Link to controller method

2. **Add Controller Method**
   - Extract request data
   - Validate input
   - Call service method
   - Return response

3. **Add Service Method** (if needed)
   - Implement business logic
   - Use transaction if needed
   - Add audit logging
   - Handle errors

4. **Add Repository Method** (if needed)
   - Write database query
   - Use prepared statements
   - Add tenant isolation
   - Return results

5. **Add Tests**
   - Unit test for service
   - Integration test for endpoint
   - E2E test for user flow

### N - Norms

**API Standards:**
- Use RESTful conventions
- Standardized response format
- Proper HTTP status codes
- Consistent error messages
- Indonesian/English support

### S - Safeguards

**Non-negotiable Rules:**
- MUST use authentication (except public endpoints)
- MUST check permissions
- MUST validate input
- MUST handle errors
- MUST return standardized responses

## Implementation

### Route Definition

Add to `BACKEND/modules/[MODULE]/routes/[MODULE].php`:

```php
$router->[METHOD]('/api/v1/[route]', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('[PERMISSION_CODE]');
    $controller->methodName();
});
```

### Controller Method

Add to `BACKEND/modules/[MODULE]/Controllers/[MODULE]Controller.php`:

```php
public function methodName()
{
    $tenantId = $_SESSION['tenant_id'] ?? 0;
    $userId = $_SESSION['user_id'] ?? 0;
    
    // Extract request data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    $errors = $this->validateInput($data);
    if (!empty($errors)) {
        Response::validationError($errors);
    }
    
    // Call service
    $result = $this->service->methodName($data, $tenantId, $userId);
    
    // Return response
    if ($result['success']) {
        Response::success($result['data'], $result['message']);
    } else {
        Response::error($result['message'], 400, $result['errors'] ?? []);
    }
}

private function validateInput(array $data): array
{
    $errors = [];
    
    // Add validation rules
    
    return $errors;
}
```

### Service Method

Add to `BACKEND/modules/[MODULE]/Services/[MODULE]Service.php`:

```php
public function methodName(array $data, int $tenantId, int $userId): array
{
    $pdo = $this->db->connect();
    
    try {
        $pdo->beginTransaction();
        
        // Business logic here
        
        // Audit log
        Audit::log($tenantId, $userId, '[ACTION]', [
            'data' => $data
        ]);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Success message',
            'data' => $result
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        
        return [
            'success' => false,
            'message' => 'Error message',
            'error' => $e->getMessage()
        ];
    }
}
```

### Repository Method

Add to `BACKEND/modules/[MODULE]/Repositories/[MODULE]Repository.php`:

```php
public function methodName(array $params): array
{
    $sql = "SELECT * FROM [table] 
            WHERE tenant_id = :tenant_id 
            AND [conditions]";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}
```

## Response Format

**Success Response:**
```json
{
    "success": true,
    "message": "Success message",
    "data": {
        "field": "value"
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": "Error description"
    }
}
```

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Testing

**Integration Test:**
```php
public function testEndpoint()
{
    $response = $this->client->post('/api/v1/[route]', [
        'headers' => ['Authorization' => "Bearer {$this->token}"],
        'json' => ['field' => 'value']
    ]);
    
    $this->assertEquals(200, $response->getStatusCode());
}
```

**E2E Test:**
```typescript
test('API endpoint', async ({ page }) => {
    const response = await page.request.post('/api/v1/[route]', {
        data: { field: 'value' }
    });
    
    expect(response.status()).toBe(200);
});
```

## Checklist

- [ ] Route defined
- [ ] Controller method added
- [ ] Service method added (if needed)
- [ ] Repository method added (if needed)
- [ ] Authentication added
- [ ] Permission check added
- [ ] Input validation added
- [ ] Error handling added
- [ ] Tests created
- [ ] Documentation updated

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
