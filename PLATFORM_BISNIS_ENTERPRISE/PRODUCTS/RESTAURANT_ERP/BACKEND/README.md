# EBP Restaurant ERP - Backend

## Project Structure

**Note:** Frontend files are located in `../FRONTEND/frontend/` directory for better separation of concerns.

```
PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/
├── BACKEND/          (This directory - PHP API Server)
│   ├── public/
│   ├── core/
│   ├── modules/
│   ├── routes/
│   ├── database/
│   ├── tests/
│   ├── vendor/
│   └── DOCUMENTATION/
├── FRONTEND/         (Frontend assets - mobile, kiosk, consumer, dashboard, css, js)
│   └── frontend/
│       ├── consumer/
│       ├── kiosk/
│       ├── mobile/
│       ├── dashboard/
│       ├── css/
│       └── js/
├── DATABASE/         (Database schema & migrations)
└── DOCUMENTATION/    (Documentation, research, prompting)
```

├── public/
│   ├── index.php
│   └── pos.js
│
├── core/
│   ├── Router.php
│   ├── Response.php
│   ├── JWT.php
│   ├── Transaction.php
│   ├── Audit.php
│   ├── Logger.php
│   ├── Database.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── TenantMiddleware.php
│   │   ├── PermissionMiddleware.php
│   │   └── ErrorHandler.php
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
├── routes/
│   └── api.php

├── database/
│   ├── schema.sql
│   ├── current_data.sql
│   └── migration_*.sql

├── DOCUMENTATION/
│   ├── API_DOCUMENTATION.md
│   ├── CODING_STANDARD_ID.md
│   ├── TESTING_GUIDE.md
│   └── DEPLOYMENT.md

├── tests/
│   ├── unit/
│   └── integration/

├── logs/
│   └── app.log

├── .env
├── .env.example
├── bootstrap.php
├── composer.json
├── composer.lock
├── phpunit.xml
├── Dockerfile
├── docker-compose.yml
└── openapi.json
```

## Setup

1. **Database Setup:**
   - Option 1: Use automated setup script (recommended):
     ```bash
     php setup_database.php
     ```
   - Option 2: Import from current data:
     ```bash
     mysql -u ebp_app -p ebp_restaurant_db < database/current_data.sql
     ```
   - Option 3: Import schema only:
     ```bash
     mysql -u ebp_app -p ebp_restaurant_db < database/schema.sql
     ```
   - Run seed data for initial admin user:
     ```bash
     php seed_data.php
     ```
   - Run sample data seeding for testing:
     ```bash
     php seed_sample_data.php
     ```

2. Configure environment variables:
   - Copy `.env.example` to `.env`
   - Update database credentials in `.env`

3. Configure web server to point to `public/` directory

## Database

The database is synced with the project in the `database/` directory:

- **current_data.sql** - Latest database export from phpMyAdmin (schema + data)
- **schema.sql** - Database schema structure only
- **migration_phase*.sql** - Migration files for development history

**Export current database:**
```bash
mysqldump -u ebp_app -p ebp_restaurant_db > database/current_data.sql
```

**Restore database:**
```bash
mysql -u ebp_app -p ebp_restaurant_db < database/current_data.sql
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
  "order_type": "TAKE_AWAY",
  "items": [
    {
      "product_id": 1,
      "qty": 2,
      "price": 30000,
      "notes": "Test order"
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
