# Example: Adding API Endpoint

## Scenario

Add a new API endpoint to calculate restaurant revenue for a specific date range with filtering by payment method and service type.

## REASONS Canvas Prompt

### R - Requirements

**Problem**: Restaurant owners need to view revenue reports for specific date ranges with filtering capabilities to analyze business performance.

**Definition of Done**:
- API endpoint for revenue calculation
- Date range filtering
- Payment method filtering
- Service type filtering
- Authentication and authorization
- Input validation
- Error handling
- Integration tests

### E - Entities

**Request/Response Entities**:
- Request: start_date, end_date, payment_method, service_type
- Response: total_revenue, breakdown_by_payment, breakdown_by_service, daily_revenue

### A - Approach

**Strategy**:
1. Add method to existing ReportService
2. Implement revenue calculation logic
3. Add filtering capabilities
4. Add validation for date ranges
5. Add controller method
6. Add route with authentication and authorization
7. Create integration tests

### S - Structure

**Changes Required**:
- Service: `ReportService.php` - add `calculateRevenue()` method
- Controller: `ReportController.php` - add `revenue()` method
- Routes: `report.php` - add revenue endpoint

### O - Operations

**Implementation Steps**:
1. Add `calculateRevenue()` method to ReportService
2. Implement date range validation
3. Implement payment method filtering
4. Implement service type filtering
5. Calculate total revenue
6. Calculate breakdown by payment method
7. Calculate breakdown by service type
8. Calculate daily revenue
9. Add `revenue()` method to ReportController
10. Add route `/api/v1/reports/revenue`
11. Add authentication middleware
12. Add authorization middleware (REPORT_VIEW permission)
13. Create integration tests

### N - Norms

**Standards**:
- Follow existing code patterns
- Use Indonesian/English for messages
- Return standardized response format
- Use appropriate HTTP status codes
- Include PHPDoc comments

### S - Safeguards

**Non-negotiable Rules**:
- MUST require authentication
- MUST check REPORT_VIEW permission
- MUST validate date ranges
- MUST validate payment method enum
- MUST validate service type enum
- MUST use tenant isolation
- MUST handle errors gracefully

## Implementation Prompt

```
Add a new API endpoint to calculate restaurant revenue.

Endpoint Details:
- Route: GET /api/v1/reports/revenue
- Authentication: Required
- Permission: REPORT_VIEW
- Query Parameters:
  - start_date (required, format: YYYY-MM-DD)
  - end_date (required, format: YYYY-MM-DD)
  - payment_method (optional, enum: CASH, CARD, E_WALLET, TRANSFER)
  - service_type (optional, enum: DINE_IN, TAKEAWAY, DELIVERY)

Response Format:
{
    "success": true,
    "message": "Revenue calculated successfully",
    "data": {
        "total_revenue": 15000000,
        "breakdown_by_payment": {
            "CASH": 5000000,
            "CARD": 7000000,
            "E_WALLET": 3000000
        },
        "breakdown_by_service": {
            "DINE_IN": 8000000,
            "TAKEAWAY": 4000000,
            "DELIVERY": 3000000
        },
        "daily_revenue": [
            {
                "date": "2024-01-01",
                "revenue": 5000000
            }
        ]
    }
}

Implementation Steps:
1. Add calculateRevenue() method to ReportService
2. Implement date range validation (start_date <= end_date)
3. Implement payment method filtering
4. Implement service type filtering
5. Query orders table with filters
6. Calculate total revenue
7. Calculate breakdowns
8. Add revenue() method to ReportController
9. Add route to report.php
10. Add authentication and authorization middleware

Use these references:
- API conventions: prompting/context/api-conventions.md
- Coding standards: prompting/context/coding-standards.md
- Database schema: prompting/context/database-schema.md

After implementation, verify:
- Endpoint requires authentication
- Endpoint checks REPORT_VIEW permission
- Date range validation works
- Filtering works correctly
- Tenant isolation is enforced
- Error handling is proper
```

## Testing Prompt

```
Create integration tests for the revenue endpoint following the test template: prompting/templates/test-template.md

Test cases:
1. Valid request with all parameters
2. Valid request with required parameters only
3. Invalid date range (start_date > end_date)
4. Invalid date format
5. Invalid payment method
6. Invalid service type
7. Authentication required (no token)
8. Invalid token
9. Valid token without REPORT_VIEW permission
10. Valid token with REPORT_VIEW permission
11. Tenant isolation (user cannot access other tenant data)
12. Empty result set (no orders in date range)

Use integration test patterns from: prompting/templates/test-template.md

Verify:
- All test cases pass
- Proper HTTP status codes returned
- Error messages are user-friendly
- Tenant isolation works correctly
```

## Code Review Prompt

```
Review the revenue endpoint implementation using the code review checklist: prompting/evaluations/code-review-checklist.md

Focus areas:
1. Security:
   - Authentication enforcement
   - Authorization check
   - Input validation
   - SQL injection prevention
   - Tenant isolation

2. Functionality:
   - Date range validation
   - Filtering logic
   - Revenue calculation accuracy
   - Edge case handling

3. Performance:
   - Query optimization
   - Index usage
   - Large date range handling

4. Code Quality:
   - Code organization
   - PHPDoc comments
   - Error handling
   - Response format

Report any issues with severity levels.
```

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
