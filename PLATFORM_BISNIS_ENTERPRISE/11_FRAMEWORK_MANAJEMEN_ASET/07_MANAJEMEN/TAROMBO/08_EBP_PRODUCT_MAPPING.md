# RESTORAN - EBP Product Mapping

**Document ID:** EBP-ESAMF-RESTORAN-008

**Version:** 1.0

**Purpose:** Mapping of RESTORAN repository to EBP products

---

# 1. Mapping Overview

This document maps the RESTORAN repository components to EBP products, modules, and engines.

## Mapping Strategy

```
RESTORAN Repository
├── Core Components → EBP Core (06_CORE_CODE)
├── Shared Engines → EBP Shared Engines (07_SHARED_ENGINES)
└── Product Components → Restaurant ERP (PRODUCTS/RESTAURANT_ERP)
```

---

# 2. Repository to Product Mapping

## 2.1 Overall Mapping

```
RESTORAN Repository → Restaurant ERP Product

Product Name: Restaurant ERP
Product Domain: Restaurant Management
Product Type: ERP
Target Location: PRODUCTS/RESTAURANT_ERP/
```

## 2.2 Component Distribution

```
Total Components: 15

Core Assets: 5 (33%)
- Authentication System → 06_CORE_CODE/Authentication
- Authorization (RBAC) → 06_CORE_CODE/Authorization
- Audit Trail → 06_CORE_CODE/Audit
- Configuration Management → 06_CORE_CODE/Configuration
- Error Handling → 06_CORE_CODE/ErrorHandling

Shared Engines: 4 (27%)
- Notification System → 07_SHARED_ENGINES/NotificationEngine
- Reporting System → 07_SHARED_ENGINES/ReportingEngine
- Inventory System → 07_SHARED_ENGINES/InventoryEngine
- Pricing Engine → 07_SHARED_ENGINES/PricingEngine

Product Assets: 6 (40%)
- Point of Sale (POS) → PRODUCTS/RESTAURANT_ERP/
- Kitchen Display System → PRODUCTS/RESTAURANT_ERP/
- Menu Management → PRODUCTS/RESTAURANT_ERP/
- Recipe Management → PRODUCTS/RESTAURANT_ERP/
- Table Management → PRODUCTS/RESTAURANT_ERP/
- Reservation System → PRODUCTS/RESTAURANT_ERP/
```

---

# 3. Module to Module Mapping

## 3.1 Auth Module Mapping

```
RESTORAN Auth Module
├── Authentication → EBP Core Authentication
├── Authorization → EBP Core Authorization
└── User Management → EBP Core User Management

Mapping:
- modules/Auth/AuthController.php → 06_CORE_CODE/Authentication/AuthController.php
- modules/Auth/AuthService.php → 06_CORE_CODE/Authentication/AuthService.php
- modules/Auth/RoleService.php → 06_CORE_CODE/Authorization/RoleService.php
- modules/Auth/PermissionService.php → 06_CORE_CODE/Authorization/PermissionService.php
- modules/Auth/UserService.php → 06_CORE_CODE/UserManagement/UserService.php
```

## 3.2 Sales Module Mapping

```
RESTORAN Sales Module → Restaurant ERP Sales Module

Mapping:
- modules/Sales/ → PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Sales/

Components:
- SalesController.php → SalesController.php
- OrderService.php → OrderService.php
- PaymentService.php → PaymentService.php
- ReceiptService.php → ReceiptService.php

Dependencies:
- Uses EBP Core Authentication
- Uses EBP Core Authorization
- Uses EBP Shared Engine Inventory
- Uses EBP Shared Engine Pricing
```

## 3.3 Inventory Module Mapping

```
RESTORAN Inventory Module → EBP Shared Engine Inventory

Mapping:
- modules/Inventory/ → 07_SHARED_ENGINES/InventoryEngine/

Components:
- InventoryController.php → InventoryController.php
- InventoryService.php → InventoryService.php
- StockService.php → StockService.php

Used By:
- Restaurant ERP
- Hotel ERP
- Farming ERP
```

## 3.4 Menu Module Mapping

```
RESTORAN Menu Module → Restaurant ERP Menu Module

Mapping:
- modules/Menu/ → PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Menu/

Components:
- MenuController.php → MenuController.php
- CategoryService.php → CategoryService.php
- ProductService.php → ProductService.php
- PricingService.php → (Use EBP Shared Engine Pricing)

Dependencies:
- Uses EBP Core Authentication
- Uses EBP Shared Engine Pricing
```

## 3.5 Kitchen Module Mapping

```
RESTORAN Kitchen Module → Restaurant ERP Kitchen Module

Mapping:
- modules/Kitchen/ → PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Kitchen/

Components:
- KitchenController.php → KitchenController.php
- KitchenService.php → KitchenService.php
- OrderRoutingService.php → OrderRoutingService.php

Dependencies:
- Uses EBP Core Authentication
- Uses EBP Shared Engine Notification
```

## 3.6 Table Module Mapping

```
RESTORAN Table Module → Restaurant ERP Table Module

Mapping:
- modules/Table/ → PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Table/

Components:
- TableController.php → TableController.php
- TableService.php → TableService.php

Dependencies:
- Uses EBP Core Authentication
- Uses Restaurant ERP Reservation Module
```

## 3.7 Reservation Module Mapping

```
RESTORAN Reservation Module → Restaurant ERP Reservation Module

Mapping:
- modules/Reservation/ → PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Reservation/

Components:
- ReservationController.php → ReservationController.php
- ReservationService.php → ReservationService.php
- BookingService.php → BookingService.php

Dependencies:
- Uses EBP Core Authentication
- Uses Restaurant ERP Table Module
- Uses EBP Shared Engine Notification
```

## 3.8 Report Module Mapping

```
RESTORAN Report Module → EBP Shared Engine Reporting

Mapping:
- modules/Report/ → 07_SHARED_ENGINES/ReportingEngine/

Components:
- ReportController.php → ReportController.php
- ReportService.php → ReportService.php
- AnalyticsService.php → AnalyticsService.php

Used By:
- Restaurant ERP
- Hotel ERP
- Parking System
- All products
```

## 3.9 User Module Mapping

```
RESTORAN User Module → EBP Core User Management

Mapping:
- modules/User/ → 06_CORE_CODE/UserManagement/

Components:
- UserController.php → UserController.php
- UserService.php → UserService.php
- ProfileService.php → ProfileService.php

Used By:
- All products
```

## 3.10 Settings Module Mapping

```
RESTORAN Settings Module → EBP Core Configuration

Mapping:
- modules/Settings/ → 06_CORE_CODE/Configuration/

Components:
- SettingsController.php → SettingsController.php
- SettingsService.php → SettingsService.php
- ConfigurationService.php → ConfigurationService.php

Used By:
- All products
```

---

# 4. Component to Component Mapping

## 4.1 Authentication Components

```
RESTORAN → EBP Core Authentication

AuthService.php → Authentication/AuthenticationService.php
AuthController.php → Authentication/AuthenticationController.php
AuthMiddleware.php → Authentication/AuthenticationMiddleware.php
User.php → Authentication/UserModel.php
Session.php → Authentication/SessionModel.php
Token.php → Authentication/TokenModel.php
```

## 4.2 Authorization Components

```
RESTORAN → EBP Core Authorization

RoleService.php → Authorization/RoleService.php
PermissionService.php → Authorization/PermissionService.php
RBACMiddleware.php → Authorization/RBACMiddleware.php
Role.php → Authorization/RoleModel.php
Permission.php → Authorization/PermissionModel.php
RolePermission.php → Authorization/RolePermissionModel.php
```

## 4.3 Notification Components

```
RESTORAN → EBP Shared Engine Notification

NotificationService.php → NotificationEngine/NotificationService.php
EmailService.php → NotificationEngine/EmailService.php
SMSService.php → NotificationEngine/SMSService.php
PushService.php → NotificationEngine/PushService.php
```

## 4.4 Reporting Components

```
RESTORAN → EBP Shared Engine Reporting

ReportService.php → ReportingEngine/ReportService.php
AnalyticsService.php → ReportingEngine/AnalyticsService.php
ExportService.php → ReportingEngine/ExportService.php
ChartService.php → ReportingEngine/ChartService.php
```

---

# 5. Feature to Feature Mapping

## 5.1 Authentication Features

```
RESTORAN → EBP Core Authentication

Login → Login
Logout → Logout
Token Validation → Token Validation
Password Reset → Password Reset
Password Change → Password Change
Multi-Factor Authentication → Multi-Factor Authentication
```

## 5.2 Authorization Features

```
RESTORAN → EBP Core Authorization

Role Management → Role Management
Permission Management → Permission Management
Access Control → Access Control
Role Hierarchy → Role Hierarchy
Permission Inheritance → Permission Inheritance
```

## 5.3 Sales Features

```
RESTORAN → Restaurant ERP Sales

Create Order → Create Order
Process Order → Process Order
Cancel Order → Cancel Order
Process Payment → Process Payment
Generate Receipt → Generate Receipt
```

## 5.4 Inventory Features

```
RESTORAN → EBP Shared Engine Inventory

Get Stock → Get Stock
Update Stock → Update Stock
Adjust Stock → Adjust Stock
Check Low Stock → Check Low Stock
Get Inventory Report → Get Inventory Report
```

## 5.5 Reporting Features

```
RESTORAN → EBP Shared Engine Reporting

Generate Sales Report → Generate Sales Report
Generate Inventory Report → Generate Inventory Report
Generate Performance Report → Generate Performance Report
Get Analytics → Get Analytics
Export Report → Export Report
```

---

# 6. Database to Database Mapping

## 6.1 Core Tables

```
RESTORAN → EBP Core Database

users → ebp_core.users
roles → ebp_core.roles
permissions → ebp_core.permissions
role_permissions → ebp_core.role_permissions
settings → ebp_core.settings
audit_logs → ebp_core.audit_logs
```

## 6.2 Product Tables

```
RESTORAN → Restaurant ERP Database

products → restaurant_erp.products
categories → restaurant_erp.categories
recipes → restaurant_erp.recipes
recipe_ingredients → restaurant_erp.recipe_ingredients
orders → restaurant_erp.orders
order_items → restaurant_erp.order_items
tables → restaurant_erp.tables
reservations → restaurant_erp.reservations
```

## 6.3 Shared Engine Tables

```
RESTORAN → Shared Engine Databases

inventory → inventory_engine.inventory
stock → inventory_engine.stock
notifications → notification_engine.notifications
reports → reporting_engine.reports
```

---

# 7. API to API Mapping

## 7.1 Authentication APIs

```
RESTORAN → EBP Core Authentication API

POST /api/auth/login → POST /api/core/auth/login
POST /api/auth/logout → POST /api/core/auth/logout
POST /api/auth/validate → POST /api/core/auth/validate
POST /api/auth/reset-password → POST /api/core/auth/reset-password
POST /api/auth/change-password → POST /api/core/auth/change-password
```

## 7.2 Sales APIs

```
RESTORAN → Restaurant ERP Sales API

POST /api/sales/orders → POST /api/restaurant/sales/orders
GET /api/sales/orders/{id} → GET /api/restaurant/sales/orders/{id}
PUT /api/sales/orders/{id} → PUT /api/restaurant/sales/orders/{id}
DELETE /api/sales/orders/{id} → DELETE /api/restaurant/sales/orders/{id}
POST /api/sales/orders/{id}/pay → POST /api/restaurant/sales/orders/{id}/pay
```

## 7.3 Inventory APIs

```
RESTORAN → EBP Shared Engine Inventory API

GET /api/inventory/stock → GET /api/shared/inventory/stock
PUT /api/inventory/stock → PUT /api/shared/inventory/stock
POST /api/inventory/adjust → POST /api/shared/inventory/adjust
GET /api/inventory/report → GET /api/shared/inventory/report
```

---

# 8. Configuration Mapping

## 8.1 Authentication Configuration

```
RESTORAN → EBP Core Authentication

config/auth.php → config/core/auth.php

Configuration:
- token_expiry → token_expiry
- max_attempts → max_attempts
- lockout_duration → lockout_duration
- password_policy → password_policy
```

## 8.2 Database Configuration

```
RESTORAN → EBP Database Configuration

config/database.php → config/database.php

Configuration:
- host → host
- database → database (mapped to new database)
- username → username
- password → password
```

---

# 9. Dependency Mapping

## 9.1 Core Dependencies

```
RESTORAN → EBP Core Dependencies

Database Service → EBP Core Database Service
Session Service → EBP Core Session Service
Configuration Service → EBP Core Configuration Service
Audit Service → EBP Core Audit Service
```

## 9.2 Shared Engine Dependencies

```
RESTORAN → EBP Shared Engine Dependencies

Email Service → EBP Shared Engine Notification
SMS Service → EBP Shared Engine Notification
Queue Service → EBP Shared Engine Queue
Cache Service → EBP Core Cache Service
```

---

# 10. Migration Path

## 10.1 Phase 1: Core Migration

```
Week 1-4: Extract and migrate core components
- Authentication System
- Authorization (RBAC)
- Audit Trail
- Configuration Management
- Error Handling
```

## 10.2 Phase 2: Shared Engine Migration

```
Week 5-8: Extract and migrate shared engines
- Notification System
- Reporting System
- Inventory System
- Pricing Engine
```

## 10.3 Phase 3: Product Migration

```
Week 9-12: Refactor product components
- Point of Sale (POS)
- Kitchen Display System
- Menu Management
- Recipe Management
- Table Management
- Reservation System
```

## 10.4 Phase 4: Integration

```
Week 13-16: Integrate and test
- Integrate EBP Core components
- Integrate Shared Engines
- Update dependencies
- Comprehensive testing
- Performance testing
- Security testing
```

---

# Document End

**Document ID:** EBP-ESAMF-RESTORAN-008

**Version:** 1.0
