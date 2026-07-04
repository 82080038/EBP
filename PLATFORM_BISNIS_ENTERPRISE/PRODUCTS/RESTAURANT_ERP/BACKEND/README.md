# Backend Restoran EBP - Enterprise Implementation

## Project Structure

```
PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/

├── public/
│   ├── index.php
│   └── pos.js
│
├── config/
│   └── database.php
│
├── core/
│   ├── Router.php
│   ├── Response.php
│   ├── JWT.php
│   ├── Transaction.php
│   ├── Audit.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── TenantMiddleware.php
│   │   └── PermissionMiddleware.php
│   └── Engines/
│       ├── StockEngine.php
│       ├── KitchenEngine.php
│       └── AccountingEngine.php
│
├── modules/
│   ├── Auth/
│   │   └── Controllers/
│   │       └── AuthController.php
│   └── Sales/
│       ├── Controllers/
│       │   └── OrderController.php
│       ├── Services/
│       │   └── OrderService.php
│       ├── Repositories/
│       │   └── OrderRepository.php
│       └── Models/
│           └── Order.php
│
└── routes/
    └── api.php
```

## Setup

1. **Database Setup:**
   - Option 1: Import from current data (recommended for development):
     ```bash
     mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/current_data.sql
     ```
   - Option 2: Import schema only:
     ```bash
     mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/schema.sql
     ```
   - Option 3: Import from original schema:
     `/ENTERPRISE_BUSINESS_PLATFORM/09_DATABASE_DESIGN/EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql`
   - Run seed data for initial admin user:
     ```bash
     php seed_data.php
     ```

2. Configure database connection in:
   `bootstrap.php` (via environment variables)

3. Configure web server to point to `public/` directory

## Database

The database is synced with the project in the `database/` directory:

- **current_data.sql** - Latest database export from phpMyAdmin (schema + data)
- **schema.sql** - Database schema structure only
- **migration_phase*.sql** - Migration files for development history

**Export current database:**
```bash
mysqldump -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db > database/current_data.sql
```

**Restore database:**
```bash
mysql -u root --socket=/opt/lampp/var/mysql/mysql.sock ebp_restaurant_db < database/current_data.sql
```

See `database/README.md` for detailed database documentation.

## API Endpoints

### Login

**POST** `/api/v1/auth/login`

**Request Body:**
```json
{
  "username": "admin",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "admin",
      "role": "manager"
    }
  }
}
```

### Create Order

**POST** `/api/v1/orders`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
  "customer_id": null,
  "items": [
    {
      "menu_id": 10,
      "qty": 2,
      "price": 25000
    },
    {
      "menu_id": 20,
      "qty": 1,
      "price": 10000
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order berhasil",
  "data": {
    "order_id": 105,
    "total": 60000
  }
}
```

## Enterprise Features Implemented

✅ **JWT Authentication** - Token-based authentication with expiration
✅ **RBAC Permission Check** - Role-based access control
✅ **Tenant Isolation** - Multi-tenant data separation
✅ **Database Transaction** - ACID compliance with rollback on error
✅ **Stock Engine** - Automatic inventory deduction from recipe
✅ **Kitchen Queue** - Kitchen order creation
✅ **Accounting Journal** - Automatic journal entry generation
✅ **Audit Trail** - Complete activity logging

## Order Transaction Flow

```
Request
  ↓
JWT Authentication
  ↓
Permission Check (ORDER_CREATE)
  ↓
Validation
  ↓
BEGIN TRANSACTION
  ↓
Create Order
  ↓
Create Order Details
  ↓
Stock Engine (Deduct Inventory)
  ↓
Kitchen Engine (Create Kitchen Order)
  ↓
Accounting Engine (Create Journal)
  ↓
Audit Trail (Log Activity)
  ↓
COMMIT TRANSACTION
  ↓
Response
```

## Architecture Layers

1. **Controller** - Handles HTTP requests, middleware execution
2. **Service** - Business logic, transaction management
3. **Repository** - Database access layer
4. **Model** - Data representation
5. **Middleware** - Authentication, authorization, tenant isolation
6. **Engines** - Business engines (Stock, Kitchen, Accounting)
7. **Audit** - Activity logging

## Security Features

- JWT token authentication
- Password hashing (bcrypt)
- Permission-based access control
- Tenant data isolation
- SQL injection prevention (PDO prepared statements)
- CORS headers configuration
