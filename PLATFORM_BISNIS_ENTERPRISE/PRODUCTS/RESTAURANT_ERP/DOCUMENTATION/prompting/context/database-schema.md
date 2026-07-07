# RESTAURANT_ERP Database Schema

## Database Overview

- **Database Name**: ebp_restaurant_db
- **Engine**: MySQL 8.x
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Total Tables**: 78 tables
- **Migrations**: 10 migration files (001-010)

## Migration Files

### MIGRATION_001: Supplier Management
- **suppliers** - Supplier information
- **supplier_contracts** - Supplier contracts
- **supplier_products** - Supplier-product relationships

### MIGRATION_002: Recipe Sourcing
- **recipes** - Updated with sourcing fields

### MIGRATION_003: Inventory Sourcing
- **inventory** - Updated with sourcing fields

### MIGRATION_004: Tenant Configurations
- **tenant_configurations** - Tenant-specific configurations

### MIGRATION_005: Feature Modules
- **feature_modules** - Available feature modules
- **tenant_feature_modules** - Tenant-enabled features

### MIGRATION_006: Risk Management
- **risk_assessments** - Risk assessment records
- **risk_incidents** - Risk incident records
- **system_health_checks** - System health monitoring
- **backup_logs** - Backup operation logs
- **security_audit_logs** - Security audit records
- **disaster_recovery_plans** - Disaster recovery plans
- **sla_monitoring** - SLA monitoring records

### MIGRATION_007: AI Infrastructure
- **ai_models** - AI model registry
- **ai_predictions** - AI prediction records
- **ai_model_feedback** - AI model feedback
- **ai_decision_logs** - AI decision logs
- **ai_governance_logs** - AI governance logs
- **ai_autonomy_levels** - AI autonomy levels

### MIGRATION_008: Launch Infrastructure
- **beta_program_participants** - Beta program participants
- **beta_feedback** - Beta feedback records
- **referral_programs** - Referral program configurations
- **referral_transactions** - Referral transaction records
- **viral_campaigns** - Viral marketing campaigns
- **geographic_expansions** - Geographic expansion records
- **growth_metrics** - Growth tracking metrics

### MIGRATION_009: Advertising
- **ad_campaigns** - Advertising campaigns
- **ad_impressions** - Ad impression records
- **ad_clicks** - Ad click records
- **ad_conversions** - Ad conversion records
- **ad_analytics** - Ad analytics data
- **supplier_ad_placements** - Supplier ad placements
- **featured_restaurant_requests** - Featured restaurant requests
- **user_ad_preferences** - User ad preferences
- **data_products** - Data product definitions
- **data_product_subscriptions** - Data product subscriptions

### MIGRATION_010: Subscription Management
- **subscription_plans** - Subscription plan definitions
- **tenant_subscriptions** - Tenant subscription records
- **subscription_payments** - Subscription payment records
- **transaction_fees** - Transaction fee configurations
- **marketplace_fees** - Marketplace fee configurations
- **add_on_services** - Add-on service definitions
- **tenant_add_ons** - Tenant add-on subscriptions
- **geographic_pricing_adjustments** - Geographic pricing adjustments

## Core Tables

### Tenant Management
```sql
-- tenants
tenant_id (PK, BIGINT UNSIGNED)
tenant_code (VARCHAR(50), UNIQUE)
tenant_name (VARCHAR(150))
business_type (VARCHAR(50))
contact_person (VARCHAR(100))
email (VARCHAR(100))
phone (VARCHAR(50))
address (TEXT)
city (VARCHAR(100))
region (VARCHAR(100))
country (VARCHAR(50))
status (ENUM: ACTIVE, INACTIVE, BLOCKED)
subscription_plan_id (BIGINT UNSIGNED)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)
```

### User Management
```sql
-- users
user_id (PK, BIGINT UNSIGNED)
tenant_id (FK, BIGINT UNSIGNED)
username (VARCHAR(50), UNIQUE)
email (VARCHAR(100), UNIQUE)
password_hash (VARCHAR(255))
role_id (FK, BIGINT UNSIGNED)
first_name (VARCHAR(50))
last_name (VARCHAR(50))
phone (VARCHAR(20))
status (ENUM: ACTIVE, INACTIVE, BLOCKED)
last_login (TIMESTAMP)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)

-- roles
role_id (PK, BIGINT UNSIGNED)
role_name (VARCHAR(50), UNIQUE)
role_description (TEXT)
permissions (JSON)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)

-- permissions
permission_id (PK, BIGINT UNSIGNED)
permission_code (VARCHAR(100), UNIQUE)
permission_name (VARCHAR(100))
permission_description (TEXT)
module (VARCHAR(50))
created_at (TIMESTAMP)

-- user_roles
user_role_id (PK, BIGINT UNSIGNED)
user_id (FK, BIGINT UNSIGNED)
role_id (FK, BIGINT UNSIGNED)
assigned_at (TIMESTAMP)
assigned_by (BIGINT UNSIGNED)
```

### Menu Management
```sql
-- menu_categories
category_id (PK, BIGINT UNSIGNED)
tenant_id (FK, BIGINT UNSIGNED)
category_code (VARCHAR(50), UNIQUE)
category_name (VARCHAR(100))
category_name_en (VARCHAR(100))
description (TEXT)
status (ENUM: ACTIVE, INACTIVE)
sort_order (INT)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)

-- menu_products
product_id (PK, BIGINT UNSIGNED)
tenant_id (FK, BIGINT UNSIGNED)
category_id (FK, BIGINT UNSIGNED)
product_code (VARCHAR(50), UNIQUE)
product_name (VARCHAR(150))
product_name_en (VARCHAR(150))
description (TEXT)
price (DECIMAL(10,2))
cost (DECIMAL(10,2))
image_url (VARCHAR(255))
status (ENUM: ACTIVE, INACTIVE)
is_available (TINYINT(1))
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)

-- recipes
recipe_id (PK, BIGINT UNSIGNED)
product_id (FK, BIGINT UNSIGNED)
recipe_name (VARCHAR(150))
instructions (TEXT)
prep_time_minutes (INT)
cook_time_minutes (INT)
serving_size (INT)
sourcing_type (ENUM: SELF_PRODUCED, OUTSOURCED, SUPPLIER_SOURCED, MIXED)
production_cost_labor (DECIMAL(10,2))
production_cost_equipment (DECIMAL(10,2))
production_cost_overhead (DECIMAL(10,2))
halal_certified (TINYINT(1))
created_at (TIMESTAMP)
updated_at (TIMESTAMP)

-- recipe_ingredients
recipe_ingredient_id (PK, BIGINT UNSIGNED)
recipe_id (FK, BIGINT UNSIGNED)
ingredient_id (FK, BIGINT UNSIGNED)
quantity (DECIMAL(10,2))
unit (VARCHAR(20))
created_at (TIMESTAMP)
```

### Order Management
```sql
-- orders
order_id (PK, BIGINT UNSIGNED)
tenant_id (FK, BIGINT UNSIGNED)
customer_id (FK, BIGINT UNSIGNED, NULL)
table_id (FK, BIGINT UNSIGNED, NULL)
order_number (VARCHAR(50), UNIQUE)
order_type (ENUM: DINE_IN, TAKEAWAY, DELIVERY)
order_status (ENUM: PENDING, CONFIRMED, PREPARING, READY, SERVED, COMPLETED, CANCELLED)
subtotal (DECIMAL(10,2))
tax_amount (DECIMAL(10,2))
discount_amount (DECIMAL(10,2))
service_charge (DECIMAL(10,2))
total_amount (DECIMAL(10,2))
payment_status (ENUM: PENDING, PAID, REFUNDED)
notes (TEXT)
created_by (BIGINT UNSIGNED)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)

-- order_details
order_detail_id (PK, BIGINT UNSIGNED)
order_id (FK, BIGINT UNSIGNED)
product_id (FK, BIGINT UNSIGNED)
quantity (INT)
unit_price (DECIMAL(10,2))
subtotal (DECIMAL(10,2))
special_instructions (TEXT)
created_at (TIMESTAMP)

-- payments
payment_id (PK, BIGINT UNSIGNED)
order_id (FK, BIGINT UNSIGNED)
payment_method (ENUM: CASH, CARD, E_WALLET, TRANSFER)
payment_amount (DECIMAL(10,2))
payment_status (ENUM: PENDING, COMPLETED, FAILED, REFUNDED)
transaction_id (VARCHAR(100))
payment_date (TIMESTAMP)
created_at (TIMESTAMP)
```

### Inventory Management
```sql
-- inventory
inventory_id (PK, BIGINT UNSIGNED)
tenant_id (FK, BIGINT UNSIGNED)
ingredient_id (FK, BIGINT UNSIGNED)
quantity (DECIMAL(10,2))
unit (VARCHAR(20))
minimum_quantity (DECIMAL(10,2))
maximum_quantity (DECIMAL(10,2))
reorder_point (DECIMAL(10,2))
sourcing_type (ENUM: SELF_PRODUCED, OUTSOURCED, SUPPLIER_SOURCED, MIXED)
batch_number (VARCHAR(50))
expiry_date (DATE)
allergen_info (TEXT)
cost_per_unit (DECIMAL(10,2))
last_restocked (TIMESTAMP)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)

-- ingredients
ingredient_id (PK, BIGINT UNSIGNED)
ingredient_name (VARCHAR(150))
ingredient_name_en (VARCHAR(150))
category (VARCHAR(50))
unit (VARCHAR(20))
description (TEXT)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### Table Management
```sql
-- restaurant_tables
table_id (PK, BIGINT UNSIGNED)
tenant_id (FK, BIGINT UNSIGNED)
table_number (VARCHAR(20), UNIQUE)
capacity (INT)
section (VARCHAR(50))
status (ENUM: AVAILABLE, OCCUPIED, RESERVED, DIRTY, MAINTENANCE)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)
```

### Reservation Management
```sql
-- reservations
reservation_id (PK, BIGINT UNSIGNED)
tenant_id (FK, BIGINT UNSIGNED)
customer_id (FK, BIGINT UNSIGNED)
table_id (FK, BIGINT UNSIGNED)
reservation_date (DATE)
reservation_time (TIME)
party_size (INT)
status (ENUM: PENDING, CONFIRMED, SEATED, COMPLETED, CANCELLED, NO_SHOW)
special_requests (TEXT)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)
```

### Kitchen Operations
```sql
-- kitchen_orders
kitchen_order_id (PK, BIGINT UNSIGNED)
order_id (FK, BIGINT UNSIGNED)
kitchen_status (ENUM: PENDING, PREPARING, READY, SERVED)
priority (ENUM: LOW, NORMAL, HIGH, URGENT)
started_at (TIMESTAMP)
completed_at (TIMESTAMP)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)

-- kitchen_order_items
kitchen_order_item_id (PK, BIGINT UNSIGNED)
kitchen_order_id (FK, BIGINT UNSIGNED)
product_id (FK, BIGINT UNSIGNED)
quantity (INT)
special_instructions (TEXT)
status (ENUM: PENDING, PREPARING, READY)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### Customer Management
```sql
-- customers
customer_id (PK, BIGINT UNSIGNED)
tenant_id (FK, BIGINT UNSIGNED)
customer_code (VARCHAR(50), UNIQUE)
first_name (VARCHAR(50))
last_name (VARCHAR(50))
email (VARCHAR(100))
phone (VARCHAR(20))
address (TEXT)
city (VARCHAR(100))
date_of_birth (DATE)
loyalty_points (INT)
loyalty_tier (ENUM: BRONZE, SILVER, GOLD, PLATINUM)
preferences (JSON)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)
```

## Standard Column Patterns

### Primary Keys
- **Pattern**: `{table}_id`
- **Type**: `BIGINT UNSIGNED AUTO_INCREMENT`
- **Example**: `user_id`, `order_id`

### Foreign Keys
- **Pattern**: `{referenced_table}_id`
- **Type**: `BIGINT UNSIGNED`
- **Example**: `user_id`, `order_id`

### Tenant Isolation
- **Pattern**: `tenant_id`
- **Type**: `BIGINT UNSIGNED NOT NULL`
- **Foreign Key**: `REFERENCES tenants(tenant_id)`
- **Index**: `INDEX idx_{table}_tenant (tenant_id)`

### Audit Fields
```sql
created_by BIGINT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_by BIGINT,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
deleted_at TIMESTAMP NULL
```

### Status Fields
```sql
status ENUM('ACTIVE', 'INACTIVE', 'BLOCKED') DEFAULT 'ACTIVE'
```

## Index Patterns

### Primary Index
- **Auto-created**: On primary key

### Foreign Key Index
- **Pattern**: `INDEX idx_{table}_{column} (column)`
- **Example**: `INDEX idx_orders_user_id (user_id)`

### Unique Index
- **Pattern**: `UNIQUE KEY uk_{table}_{column} (column)`
- **Example**: `UNIQUE KEY uk_users_email (email)`

### Composite Index
- **Pattern**: `INDEX idx_{table}_{column1}_{column2} (column1, column2)`
- **Example**: `INDEX idx_orders_tenant_status (tenant_id, status)`

## Foreign Key Patterns

### Standard Foreign Key
```sql
CONSTRAINT fk_{table}_{column} 
FOREIGN KEY (column) 
REFERENCES referenced_table(referenced_column)
ON DELETE CASCADE
ON UPDATE CASCADE
```

### Soft Delete Foreign Key
```sql
CONSTRAINT fk_{table}_{column} 
FOREIGN KEY (column) 
REFERENCES referenced_table(referenced_column)
ON DELETE SET NULL
ON UPDATE CASCADE
```

## Database Relationships

### One-to-One
- **Example**: User Profile
- **Implementation**: Foreign key with UNIQUE constraint

### One-to-Many
- **Example**: Orders to Order Details
- **Implementation**: Foreign key in child table

### Many-to-Many
- **Example**: Products to Categories
- **Implementation**: Junction table with two foreign keys

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
