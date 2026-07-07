# RESTAURANT_ERP API Documentation

**Version**: 1.0  
**Base URL**: `http://localhost:8000/api/v1`  
**Last Updated**: July 7, 2026

---

## Table of Contents

1. [Authentication](#authentication)
2. [Orders](#orders)
3. [Payments](#payments)
4. [Menu](#menu)
5. [Inventory](#inventory)
6. [Kitchen](#kitchen)
7. [Tables](#tables)
8. [Customers](#customers)
9. [Consumers](#consumers)
10. [Analytics](#analytics)
11. [Customer Analytics](#customer-analytics)
12. [Feedback](#feedback)
13. [Reconciliation](#reconciliation)
14. [Franchise](#franchise)
15. [Ghost Kitchen](#ghost-kitchen)
16. [Innovation](#innovation)
17. [Integration Hub](#integration-hub)
18. [AI & Predictive Analytics](#ai--predictive-analytics)

---

## Authentication

### Login
```http
POST /auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "token": "jwt_token_here",
  "user": {
    "user_id": 1,
    "username": "admin",
    "full_name": "Administrator",
    "email": "admin@example.com"
  }
}
```

### Validate Token
```http
GET /auth/validate
Authorization: Bearer {token}
```

### Logout
```http
POST /auth/logout
Authorization: Bearer {token}
```

---

## Orders

### Get All Orders
```http
GET /orders?page=1&limit=10&status=PENDING
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Get Order by ID
```http
GET /orders/{order_id}
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Create Order
```http
POST /orders
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
Content-Type: application/json

{
  "table_id": 1,
  "order_type": "DINE_IN",
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price": 25000
    }
  ]
}
```

### Update Order Status
```http
PATCH /orders/{order_id}/status
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "status": "IN_PROGRESS"
}
```

---

## Payments

### Get All Payments
```http
GET /payments?page=1&limit=10&status=COMPLETED
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Create Payment
```http
POST /payments
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "order_id": 1,
  "payment_method": "CASH",
  "amount": 50000,
  "change_amount": 0
}
```

### Update Payment Status
```http
PATCH /payments/{payment_id}/status
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "payment_status": "COMPLETED"
}
```

---

## Menu

### Get All Categories
```http
GET /menu/categories
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get All Products
```http
GET /menu/products?category_id=1
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Create Product
```http
POST /menu/products
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "name": "Nasi Goreng",
  "category_id": 1,
  "price": 25000,
  "description": "Fried rice with egg",
  "status": "ACTIVE"
}
```

---

## Inventory

### Get All Inventory Items
```http
GET /inventory?page=1&limit=10
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Update Stock
```http
PATCH /inventory/{inventory_id}/stock
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "quantity": 100,
  "adjustment_type": "IN",
  "reason": "Restock"
}
```

---

## Kitchen

### Get Kitchen Orders
```http
GET /kitchen/orders?status=PENDING
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Update Order Status
```http
PATCH /kitchen/orders/{order_id}/status
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "status": "PREPARING"
}
```

---

## Tables

### Get All Tables
```http
GET /tables
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Update Table Status
```http
PATCH /tables/{table_id}/status
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "status": "OCCUPIED",
  "order_id": 1
}
```

---

## Customers

### Get All Customers
```http
GET /customers?page=1&limit=10
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Create Customer
```http
POST /customers
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "name": "John Doe",
  "phone": "08123456789",
  "email": "john@example.com"
}
```

---

## Consumers

### Get All Consumers
```http
GET /consumer?page=1&limit=10
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Search Consumers
```http
GET /consumer/search?q=john
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Consumer Orders
```http
GET /consumer/{consumer_id}/orders?limit=50
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Top Consumers
```http
GET /consumer/top?limit=10&start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## Analytics

### Get Daily Sales Summary
```http
GET /analytics/daily-sales?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Hourly Sales Summary
```http
GET /analytics/hourly-sales?date=2026-07-07
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Top Selling Products
```http
GET /analytics/top-products?start_date=2026-01-01&end_date=2026-12-31&limit=10
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Category Performance
```http
GET /analytics/category-performance?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Payment Method Breakdown
```http
GET /analytics/payment-breakdown?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Order Type Breakdown
```http
GET /analytics/order-type-breakdown?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Customer Analytics
```http
GET /analytics/customer-analytics?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Table Performance
```http
GET /analytics/table-performance?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Staff Performance
```http
GET /analytics/staff-performance?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Revenue Trends
```http
GET /analytics/revenue-trends?months=12
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## Customer Analytics

### Get Customer Behavior
```http
GET /customer-analytics/{customer_id}/behavior?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Cohort Analysis
```http
GET /customer-analytics/cohort?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Customer Journey
```http
GET /customer-analytics/{customer_id}/journey
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Customer Segment
```http
GET /customer-analytics/{customer_id}/segment
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Customer Lifetime Value
```http
GET /customer-analytics/{customer_id}/lifetime-value
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Retention Rate
```http
GET /customer-analytics/retention?start_date=2026-01-01&end_date=2026-12-31
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Churn Analysis
```http
GET /customer-analytics/churn?days=90
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## Feedback

### Get All Feedback
```http
GET /feedback?page=1&limit=10&status=PENDING
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Create Feedback
```http
POST /feedback
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "feedback_type": "COMPLAINT",
  "subject": "Service Issue",
  "message": "Slow service during lunch",
  "priority": "medium"
}
```

### Update Feedback Status
```http
PATCH /feedback/{feedback_id}/status
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "status": "RESOLVED"
}
```

### Get Feedback Summary
```http
GET /feedback/summary
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## Reconciliation

### Get Reconciliation Transactions
```http
GET /reconciliation?page=1&limit=10&status=PENDING
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Discrepancies
```http
GET /reconciliation/discrepancies
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Reconciliation Summary
```http
GET /reconciliation/summary
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Reconciliation Sources
```http
GET /reconciliation/sources
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Reconciliation Rules
```http
GET /reconciliation/rules
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## Franchise

### Get All Franchisees
```http
GET /franchise?page=1&limit=10&status=ACTIVE
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Create Franchisee
```http
POST /franchise
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "franchisee_code": "FR-001",
  "franchisee_name": "Restaurant Jakarta",
  "contact_person": "John Doe",
  "phone": "08123456789",
  "email": "jakarta@example.com"
}
```

### Get Franchise Performance
```http
GET /franchise/{franchisee_id}/performance
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## Ghost Kitchen

### Get All Virtual Brands
```http
GET /ghost-kitchen?page=1&limit=10&status=ACTIVE
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Create Virtual Brand
```http
POST /ghost-kitchen
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "brand_name": "Asian Fusion Delivery",
  "brand_code": "VB-001",
  "cuisine_type": "Asian",
  "status": "ACTIVE"
}
```

### Get Brand Menu Items
```http
GET /ghost-kitchen/{brand_id}/menu
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## Innovation

### Get All Innovation Projects
```http
GET /innovation?page=1&limit=10&status=IN_PROGRESS
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Create Innovation Project
```http
POST /innovation
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "project_name": "New Menu Development",
  "project_code": "IP-001",
  "project_type": "MENU_INNOVATION",
  "status": "DRAFT"
}
```

### Get Innovation Ideas
```http
GET /innovation/ideas?limit=10
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## Integration Hub

### Get All External Integrations
```http
GET /integration-hub?page=1&limit=10&status=ACTIVE
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Create External Integration
```http
POST /integration-hub
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "integration_name": "Payment Gateway",
  "integration_type": "PAYMENT_GATEWAY",
  "api_endpoint": "https://api.payment-gateway.com",
  "status": "ACTIVE"
}
```

### Get Integration Mappings
```http
GET /integration-hub/{integration_id}/mappings
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Integration Sync Logs
```http
GET /integration-hub/{integration_id}/sync-logs?limit=50
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## AI & Predictive Analytics

### Generate Sales Forecast
```http
POST /ai/sales-forecast
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
Content-Type: application/json

{
  "days": 7
}
```

**Response:**
```json
{
  "success": true,
  "message": "Sales forecast generated successfully",
  "data": [
    {
      "date": "2026-07-08",
      "predicted_revenue": 5000000,
      "confidence_score": 0.7
    }
  ]
}
```

### Get Sales Forecast
```http
GET /ai/sales-forecast
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Generate Inventory Prediction
```http
POST /ai/inventory-prediction
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
Content-Type: application/json

{
  "inventory_id": 1
}
```

### Get Inventory Predictions
```http
GET /ai/inventory-predictions
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Get Customer Segmentation
```http
GET /ai/customer-segmentation
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Customer Lifetime Value
```http
GET /ai/customer-ltv/{customer_id}
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Churn Prediction
```http
GET /ai/churn-prediction
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Menu Optimization
```http
GET /ai/menu-optimization
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Dynamic Pricing
```http
GET /ai/dynamic-pricing/{product_id}
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Update Dynamic Pricing
```http
POST /ai/dynamic-pricing/{product_id}
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
Content-Type: application/json

{
  "suggested_price": 30000,
  "confidence": 0.8
}
```

### Get Kitchen Efficiency
```http
GET /ai/kitchen-efficiency
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Get Preparation Time Prediction
```http
GET /ai/prep-time/{order_id}
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Waste Prediction
```http
GET /ai/waste-prediction
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Get Waste Reduction Recommendations
```http
GET /ai/waste-recommendations
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Get Procurement Recommendations
```http
GET /ai/procurement-recommendations
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
X-Branch-Id: {branch_id}
```

### Get Supplier Performance
```http
GET /ai/supplier-performance
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Advanced Insights
```http
GET /ai/advanced-insights
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

### Get Predictive Analytics
```http
GET /ai/predictive-analytics
Authorization: Bearer {token}
X-Tenant-Id: {tenant_id}
```

---

## Common Headers

All API requests (except login) should include:

| Header | Description | Example |
|--------|-------------|---------|
| `Authorization` | Bearer token for authentication | `Bearer eyJhbGciOiJIUzI1NiIs...` |
| `X-Tenant-Id` | Tenant ID for multi-tenancy | `1` |
| `X-Branch-Id` | Branch ID for multi-branch | `1` |
| `Content-Type` | Content type for POST/PUT/PATCH | `application/json` |

---

## Error Responses

All endpoints return error responses in the following format:

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error information"
}
```

### Common HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 500 | Internal Server Error |

---

## Rate Limiting

- **Default Limit**: 100 requests per minute per IP
- **Burst Limit**: 200 requests per minute per IP
- Rate limit headers are included in all responses:
  - `X-RateLimit-Limit`: Request limit per window
  - `X-RateLimit-Remaining`: Remaining requests in current window
  - `X-RateLimit-Reset`: Unix timestamp when window resets

---

## Pagination

List endpoints support pagination using the following query parameters:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number |
| `limit` | integer | 10 | Items per page (max: 100) |

**Response Format:**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "page": 1,
    "limit": 10,
    "total": 100,
    "total_pages": 10
  }
}
```

---

## Request Caching

GET requests are cached for 5 minutes by default. To bypass caching:

```http
GET /orders?skip_cache=true
```

---

## Webhooks

### Order Status Changed
```json
{
  "event": "order.status_changed",
  "data": {
    "order_id": 1,
    "old_status": "PENDING",
    "new_status": "IN_PROGRESS",
    "timestamp": "2026-07-07T12:00:00Z"
  }
}
```

### Payment Completed
```json
{
  "event": "payment.completed",
  "data": {
    "payment_id": 1,
    "order_id": 1,
    "amount": 50000,
    "timestamp": "2026-07-07T12:00:00Z"
  }
}
```

---

## SDKs

### JavaScript (Frontend)
See `FRONTEND/js/api-client.js` for the JavaScript API client implementation.

### PHP (Backend)
See `BACKEND/modules/` for PHP service and repository implementations.

---

## Support

For API support and questions, contact:
- **Email**: support@ebp-restaurant.com
- **Documentation**: https://docs.ebp-restaurant.com
- **Status Page**: https://status.ebp-restaurant.com
