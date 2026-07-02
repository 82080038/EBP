# EBP Restaurant Backend API Documentation

**Version:** 1.0.0

**Base URL:** `http://localhost:8000/api/v1`

**Authentication:** Bearer Token (JWT)

---

# Authentication

## Login

**Endpoint:** `POST /auth/login`

**Description:** Authenticate user and receive JWT token

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
  "message": "Success",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": 2,
      "username": "admin",
      "role": "Administrator"
    }
  }
}
```

**Status Codes:**
- `200` - Login successful
- `401` - Invalid credentials

---

# Mobile & Kiosk APIs

## Kiosk Menu

**Endpoint:** `GET /kiosk/menu`

**Description:** Get menu for kiosk display (grouped by category)

**Authentication:** Not required (public endpoint)

**Query Parameters:**
- `tenant_id` (required) - Tenant ID
- `branch_id` (required) - Branch ID

**Example Request:**
```
GET /api/v1/kiosk/menu?tenant_id=1&branch_id=2
```

**Response:**
```json
{
  "success": true,
  "message": [
    {
      "category_id": 2,
      "category_name": "Beverages",
      "products": [
        {
          "product_id": 3,
          "product_name": "Es Teh Manis",
          "description": null,
          "price": "5000.00",
          "image_url": null,
          "is_available": 1,
          "category_name": "Beverages",
          "category_id": 2
        }
      ]
    }
  ],
  "data": "Kiosk menu retrieved successfully"
}
```

**Status Codes:**
- `200` - Success
- `400` - Missing required parameters

## Kiosk Create Order

**Endpoint:** `POST /kiosk/orders`

**Description:** Create order from kiosk

**Authentication:** Not required (public endpoint)

**Query Parameters:**
- `tenant_id` (required) - Tenant ID
- `branch_id` (required) - Branch ID

**Request Body:**
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Kiosk order created successfully (mock)",
  "data": {
    "order_id": 0,
    "order_number": "KIOSK-20260702193335-1234"
  }
}
```

**Status Codes:**
- `200` - Success
- `400` - Invalid input

## Mobile Menu

**Endpoint:** `GET /mobile/menu`

**Description:** Get lightweight menu for mobile devices

**Authentication:** Required

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": [
    {
      "product_id": 3,
      "product_name": "Es Teh Manis",
      "price": "5000.00",
      "image_url": null,
      "is_available": 1,
      "category_name": "Beverages"
    }
  ],
  "data": "Mobile menu retrieved successfully"
}
```

**Status Codes:**
- `200` - Success
- `401` - Unauthorized

## Mobile Quick Order

**Endpoint:** `GET /mobile/quick-order/{id}`

**Description:** Get product details for quick order

**Authentication:** Required

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**
- `id` (required) - Product ID

**Response:**
```json
{
  "success": true,
  "message": {
    "product_id": 1,
    "product_name": "Nasi Goreng",
    "price": "25000.00",
    "description": null,
    "image_url": null,
    "is_available": 1
  },
  "data": "Quick order data retrieved successfully"
}
```

**Status Codes:**
- `200` - Success
- `401` - Unauthorized
- `404` - Product not found

---

# Menu Management

## Get Categories

**Endpoint:** `GET /menu/categories`

**Description:** Get all menu categories

**Authentication:** Required

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "category_id": 1,
      "tenant_id": 1,
      "category_code": "MAIN",
      "category_name": "Main Course",
      "description": null,
      "parent_id": null,
      "sort_order": 0,
      "status": "ACTIVE",
      "created_at": "2026-07-02 18:53:10",
      "updated_at": "2026-07-02 18:53:10",
      "deleted_at": null
    }
  ]
}
```

**Status Codes:**
- `200` - Success
- `401` - Unauthorized
- `403` - Permission denied

## Get Category by ID

**Endpoint:** `GET /menu/categories/{id}`

**Description:** Get specific category by ID

**Authentication:** Required

**Parameters:**
- `id` (path) - Category ID

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "category_id": 1,
    "category_name": "Main Course",
    ...
  }
}
```

## Create Category

**Endpoint:** `POST /menu/categories`

**Description:** Create new menu category

**Authentication:** Required

**Request Body:**
```json
{
  "category_code": "DESSERT",
  "category_name": "Desserts",
  "description": "Sweet dishes",
  "parent_id": null,
  "sort_order": 10
}
```

**Response:**
```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "category_id": 4,
    ...
  }
}
```

## Update Category

**Endpoint:** `PUT /menu/categories/{id}`

**Description:** Update existing category

**Authentication:** Required

**Request Body:**
```json
{
  "category_name": "Updated Name",
  "description": "Updated description"
}
```

## Delete Category

**Endpoint:** `DELETE /menu/categories/{id}`

**Description:** Soft delete category

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "message": "Category deleted successfully"
}
```

---

# Products

## Get Products

**Endpoint:** `GET /menu/products`

**Description:** Get all products

**Authentication:** Required

**Query Parameters:**
- `category_id` (optional) - Filter by category
- `status` (optional) - Filter by status

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "product_id": 1,
      "product_name": "Nasi Goreng",
      "price": 25000,
      "category_id": 1,
      ...
    }
  ]
}
```

## Create Product

**Endpoint:** `POST /menu/products`

**Description:** Create new product

**Authentication:** Required

**Request Body:**
```json
{
  "product_name": "New Product",
  "product_code": "PROD001",
  "price": 30000,
  "category_id": 1,
  "description": "Product description"
}
```

---

# Orders

## Get Orders

**Endpoint:** `GET /orders`

**Description:** Get all orders

**Authentication:** Required

**Query Parameters:**
- `status` (optional) - Filter by status
- `table_id` (optional) - Filter by table
- `date_from` (optional) - Filter by date range
- `date_to` (optional) - Filter by date range

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "order_id": 1,
      "order_number": "ORD-001",
      "table_id": 1,
      "status": "PENDING",
      "total_amount": 75000,
      ...
    }
  ]
}
```

## Create Order

**Endpoint:** `POST /orders`

**Description:** Create new order

**Authentication:** Required

**Request Body:**
```json
{
  "table_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price": 25000
    }
  ]
}
```

## Update Order Status

**Endpoint:** `PUT /orders/{id}/status`

**Description:** Update order status

**Authentication:** Required

**Request Body:**
```json
{
  "status": "PREPARING"
}
```

**Valid Statuses:**
- `PENDING`
- `PREPARING`
- `READY`
- `SERVED`
- `CLOSED`

## Close Order

**Endpoint:** `PUT /orders/{id}/close`

**Description:** Close order and process payment

**Authentication:** Required

**Request Body:**
```json
{
  "payment_method": "CASH",
  "payment_amount": 75000
}
```

---

# Tables

## Get Tables

**Endpoint:** `GET /tables`

**Description:** Get all tables

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "table_id": 1,
      "table_number": "T1",
      "capacity": 4,
      "status": "AVAILABLE",
      ...
    }
  ]
}
```

## Update Table Status

**Endpoint:** `PUT /tables/{id}/status`

**Description:** Update table status

**Authentication:** Required

**Request Body:**
```json
{
  "status": "OCCUPIED"
}
```

**Valid Statuses:**
- `AVAILABLE`
- `OCCUPIED`
- `RESERVED`
- `DIRTY`

---

# Inventory

## Get Inventory

**Endpoint:** `GET /inventory`

**Description:** Get all inventory items

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "inventory_id": 1,
      "item_name": "Rice",
      "quantity": 100,
      "unit": "kg",
      ...
    }
  ]
}
```

## Stock Adjustment

**Endpoint:** `POST /inventory/adjustments`

**Description:** Create stock adjustment

**Authentication:** Required

**Request Body:**
```json
{
  "inventory_id": 1,
  "adjustment_type": "IN",
  "quantity": 10,
  "reason": "Restock"
}
```

**Adjustment Types:**
- `IN` - Stock addition
- `OUT` - Stock removal

---

# Kitchen

## Get Kitchen Orders

**Endpoint:** `GET /kitchen/orders`

**Description:** Get orders for kitchen display

**Authentication:** Required

**Query Parameters:**
- `status` (optional) - Filter by status

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "kitchen_order_id": 1,
      "order_id": 1,
      "status": "PENDING",
      "items": [...]
    }
  ]
}
```

## Update Kitchen Order Status

**Endpoint:** `PUT /kitchen/orders/{id}/status`

**Description:** Update kitchen order status

**Authentication:** Required

**Request Body:**
```json
{
  "status": "PREPARING"
}
```

---

# Mobile App

## Get Mobile Menu

**Endpoint:** `GET /mobile/menu`

**Description:** Get menu for mobile app

**Authentication:** Optional

**Query Parameters:**
- `tenant_id` - Tenant ID
- `branch_id` - Branch ID

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "categories": [...],
    "products": [...]
  }
}
```

## Get Quick Order

**Endpoint:** `GET /mobile/quick-order/{id}`

**Description:** Get quick order details

**Authentication:** Required

---

# Kiosk App

## Get Kiosk Menu

**Endpoint:** `GET /kiosk/menu`

**Description:** Get menu for kiosk app

**Authentication:** Optional

**Query Parameters:**
- `tenant_id` - Tenant ID
- `branch_id` - Branch ID

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "categories": [...],
    "products": [...]
  }
}
```

## Create Kiosk Order

**Endpoint:** `POST /kiosk/orders`

**Description:** Create order from kiosk

**Authentication:** Optional

**Request Body:**
```json
{
  "tenant_id": 1,
  "branch_id": 2,
  "items": [...],
  "customer_name": "Walk-in Customer"
}
```

---

# Error Responses

All endpoints may return error responses in the following format:

```json
{
  "success": false,
  "message": "Error message",
  "errors": []
}
```

**Common Status Codes:**
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `500` - Internal Server Error

---

# Rate Limiting

**Rate Limit:** 100 requests per minute per IP

**Rate Limit Headers:**
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1625097600
```

---

# Pagination

Some endpoints support pagination via query parameters:

**Query Parameters:**
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 20, max: 100)

**Response Headers:**
```
X-Total-Count: 150
X-Page-Count: 8
X-Current-Page: 1
```

---

# Data Formats

**Date Format:** `YYYY-MM-DD HH:MM:SS`

**Currency:** Indonesian Rupiah (IDR)

**Number Format:** Decimal with 2 places

---

# Webhooks

## Order Status Webhook

**Endpoint:** Configured per tenant

**Description:** Notify when order status changes

**Payload:**
```json
{
  "event": "order.status_changed",
  "data": {
    "order_id": 1,
    "old_status": "PENDING",
    "new_status": "PREPARING",
    "timestamp": "2026-07-02 18:53:10"
  }
}
```

---

# Testing

## Test Credentials

**Username:** `admin`

**Password:** `admin123`

**Test Base URL:** `http://localhost:8000/api/v1`

---

# Changelog

## Version 1.0.0 (2026-07-02)
- Initial API documentation
- Core endpoints documented
- Authentication flow documented
