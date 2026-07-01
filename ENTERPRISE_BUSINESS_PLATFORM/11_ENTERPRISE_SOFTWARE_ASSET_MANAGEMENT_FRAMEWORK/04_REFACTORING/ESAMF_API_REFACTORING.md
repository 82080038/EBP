# ESAMF API Refactoring

**Document ID:** ESAMF-REFACTORING-003

**Version:** 1.0

**Purpose:** Define the API refactoring standards for ESAMF

---

# Overview

API Refactoring is the process of improving API structure, consistency, and compliance with EBP standards while maintaining backward compatibility. This standard defines the approach for migrating APIs to EBP standards.

---

# Refactoring Principles

## 1. Backward Compatibility

**API changes should maintain backward compatibility where possible.**

- Use versioning
- Deprecate old endpoints
- Provide migration guide
- Support transition period

## 2. RESTful Principles

**APIs should follow RESTful principles.**

- Proper HTTP methods
- Proper status codes
- Proper resource naming
- Proper response formats

## 3. EBP Standards Compliance

**Refactored API must comply with EBP API standards.**

- Naming conventions
- Response format
- Error handling
- Authentication/Authorization

## 4. Consistency

**APIs should be consistent across all endpoints.**

- Consistent naming
- Consistent response format
- Consistent error handling
- Consistent pagination

---

# Refactoring Process

## Phase 1: Analysis

### Step 1: Analyze Current API

```
Endpoint: [Endpoint URL]
Method: [GET/POST/PUT/DELETE]
Purpose: [What it does]
Current Response Format: [JSON example]
Current Error Format: [JSON example]
```

### Step 2: Identify Refactoring Needs

```
Naming Issues:
- [Issue 1: Current, Should be]
- [Issue 2: Current, Should be]

Structure Issues:
- [Issue 1: Description]
- [Issue 2: Description]

Consistency Issues:
- [Issue 1: Description]
- [Issue 2: Description]
```

### Step 3: Create Refactoring Plan

```
Refactoring Steps:
1. [Step 1: Description, Breaking Change]
2. [Step 2: Description, Breaking Change]
3. [Step 3: Description, Breaking Change]

Deprecation Plan:
- [Old endpoint deprecation timeline]
- [Migration guide requirements]
```

---

## Phase 2: URL Refactoring

### Step 1: Apply EBP URL Conventions

**EBP Standard:** /api/v{version}/{resource}/{id}

**Before:**
```
GET /getUser?id=1
POST /createUser
PUT /updateUser
DELETE /deleteUser
```

**After:**
```
GET /api/v1/users/1
POST /api/v1/users
PUT /api/v1/users/1
DELETE /api/v1/users/1
```

### Step 2: Use Resource-Based URLs

**Before:**
```
GET /api/getUserOrders?userId=1
```

**After:**
```
GET /api/v1/users/1/orders
```

### Step 3: Use Plural Nouns

**Before:**
```
GET /api/v1/user/1
```

**After:**
```
GET /api/v1/users/1
```

---

## Phase 3: HTTP Method Refactoring

### Step 1: Use Proper HTTP Methods

**Before:**
```
POST /api/v1/users/1/delete
POST /api/v1/users/1/activate
```

**After:**
```
DELETE /api/v1/users/1
PATCH /api/v1/users/1
```

### Step 2: Method Mapping

| Action | HTTP Method | Example |
|--------|-------------|---------|
| Create | POST | POST /api/v1/users |
| Read | GET | GET /api/v1/users/1 |
| Update | PUT/PATCH | PUT /api/v1/users/1 |
| Delete | DELETE | DELETE /api/v1/users/1 |

---

## Phase 4: Response Format Refactoring

### Step 1: Apply EBP Response Format

**EBP Standard Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "meta": {
    "timestamp": "2024-01-01T00:00:00Z",
    "request_id": "req_123456"
  }
}
```

**Before:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com"
}
```

**After:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "meta": {
    "timestamp": "2024-01-01T00:00:00Z",
    "request_id": "req_123456"
  }
}
```

### Step 2: Apply EBP Error Format

**EBP Standard Error:**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": [
      {
        "field": "email",
        "message": "Email is required"
      }
    ]
  },
  "meta": {
    "timestamp": "2024-01-01T00:00:00Z",
    "request_id": "req_123456"
  }
}
```

**Before:**
```json
{
  "error": "Validation failed"
}
```

**After:**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": [
      {
        "field": "email",
        "message": "Email is required"
      }
    ]
  },
  "meta": {
    "timestamp": "2024-01-01T00:00:00Z",
    "request_id": "req_123456"
  }
}
```

### Step 3: Apply Pagination Format

**EBP Standard Pagination:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe"
    },
    {
      "id": 2,
      "name": "Jane Doe"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 100,
      "total_pages": 5
    }
  }
}
```

---

## Phase 5: HTTP Status Code Refactoring

### Step 1: Use Proper Status Codes

| Status Code | Usage | Example |
|------------|-------|---------|
| 200 | Success | GET /api/v1/users/1 |
| 201 | Created | POST /api/v1/users |
| 204 | No Content | DELETE /api/v1/users/1 |
| 400 | Bad Request | Invalid input |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | Not authorized |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Resource conflict |
| 422 | Validation Error | Validation failed |
| 500 | Server Error | Internal error |

**Before:**
```php
return response()->json(['error' => 'Not found'], 200);
```

**After:**
```php
return response()->json([
    'success' => false,
    'error' => [
        'code' => 'NOT_FOUND',
        'message' => 'Resource not found'
    ]
], 404);
```

---

## Phase 6: Authentication/Authorization Refactoring

### Step 1: Use EBP Core Authentication

**Before:**
```php
public function getUser(Request $request) {
    $token = $request->header('Authorization');
    $user = $this->authService->validateToken($token);
}
```

**After:**
```php
use EBP\Core\Authentication\AuthMiddleware;

public function getUser(Request $request) {
    // Authentication handled by middleware
    $user = $request->user();
}
```

### Step 2: Use EBP Core Authorization

**Before:**
```php
public function deleteUser(Request $request, $id) {
    if (!$this->userService->canDelete($request->user(), $id)) {
        return response()->json(['error' => 'Forbidden'], 403);
    }
}
```

**After:**
```php
use EBP\Core\Authorization\AuthorizeMiddleware;

public function deleteUser(Request $request, $id) {
    // Authorization handled by middleware
    $this->userService->delete($id);
}
```

---

## Phase 7: Versioning

### Step 1: Implement API Versioning

**URL Versioning:**
```
/api/v1/users
/api/v2/users
```

**Header Versioning:**
```
GET /api/users
Headers:
  Accept: application/vnd.ebp.v1+json
```

### Step 2: Deprecation Strategy

```php
/**
 * @deprecated Use GET /api/v2/users instead
 */
public function getUsersV1(Request $request) {
    // Add deprecation header
    return response()->json($data)
        ->header('X-Deprecated', 'true')
        ->header('X-Deprecation-Date', '2024-06-01')
        ->header('X-Sunset', '2024-12-01')
        ->header('Link', '</api/v2/users>; rel="successor-version"');
}
```

---

# Common API Refactoring Patterns

## Pattern 1: Query String to URL Parameter

**Before:**
```
GET /api/v1/getUser?id=1
```

**After:**
```
GET /api/v1/users/1
```

## Pattern 2: Action to Resource

**Before:**
```
POST /api/v1/users/1/activate
POST /api/v1/users/1/deactivate
```

**After:**
```
PATCH /api/v1/users/1
{
  "status": "active"
}
```

## Pattern 3: Nested Resource

**Before:**
```
GET /api/v1/getUserOrders?userId=1
```

**After:**
```
GET /api/v1/users/1/orders
```

## Pattern 4: Response Wrapper

**Before:**
```json
{
  "id": 1,
  "name": "John Doe"
}
```

**After:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe"
  },
  "meta": {
    "timestamp": "2024-01-01T00:00:00Z"
  }
}
```

---

# API Refactoring Checklist

## Analysis
- [ ] Current API analyzed
- [ ] Refactoring needs identified
- [ ] Refactoring plan created
- [ ] Deprecation plan created

## URL
- [ ] EBP URL conventions applied
- [ ] Resource-based URLs used
- [ ] Plural nouns used
- [ ] Old URLs deprecated

## HTTP Methods
- [ ] Proper HTTP methods used
- [ ] Method mapping documented
- [ ] Old methods deprecated

## Response Format
- [ ] EBP response format applied
- [ ] EBP error format applied
- [ ] Pagination format applied
- [ ] Old formats deprecated

## Status Codes
- [ ] Proper status codes used
- [ ] Status code mapping documented
- [ ] Old codes deprecated

## Authentication/Authorization
- [ ] EBP Core Authentication used
- [ ] EBP Core Authorization used
- [ ] Middleware applied

## Versioning
- [ ] API versioning implemented
- [ ] Deprecation headers added
- [ ] Migration guide created

## Testing
- [ ] Unit tests updated
- [ ] Integration tests updated
- [ ] Backward compatibility tested
- [ ] Documentation updated

---

# Document End

**Document ID:** ESAMF-REFACTORING-003

**Version:** 1.0
