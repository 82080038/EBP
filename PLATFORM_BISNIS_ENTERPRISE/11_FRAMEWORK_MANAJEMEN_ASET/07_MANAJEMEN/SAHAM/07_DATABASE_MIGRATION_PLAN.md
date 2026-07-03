# SAHAM - Database Migration Plan

**Document ID:** ESAMF-SAHAM-007

**Version:** 1.0

**Purpose:** Plan for migrating SAHAM database to EBP standards

---

# 1. Database Overview

## 1.1 Current Database

```
Database Type: MySQL

Database Version: [To be filled]

Database Name: restoran_db

Table Count: [To be filled]

Data Volume: [To be filled]
```

## 1.2 Current Schema

```
Main Tables:
- users
- roles
- permissions
- role_permissions
- menus
- categories
- products
- recipes
- recipe_ingredients
- orders
- order_items
- tables
- reservations
- inventory
- stock
- settings
- audit_logs
```

---

# 2. EBP Database Standards

## 2.1 Naming Conventions

```
Table Names:
- Use snake_case
- Use singular form
- Use descriptive names
- Prefix with module name if needed

Column Names:
- Use snake_case
- Use descriptive names
- Use consistent naming across tables

Index Names:
- Prefix with idx_
- Include table name
- Include column names

Foreign Key Names:
- Prefix with fk_
- Include table names
- Include column names
```

## 2.2 Structure Standards

```
Primary Keys:
- Use id as primary key name
- Use BIGINT UNSIGNED
- Use AUTO_INCREMENT
- Use NOT NULL

Foreign Keys:
- Use {table}_id as foreign key name
- Use same data type as referenced primary key
- Use NOT NULL if required
- Define ON DELETE and ON UPDATE

Timestamps:
- Include created_at TIMESTAMP
- Include updated_at TIMESTAMP
- Use DEFAULT CURRENT_TIMESTAMP
- Use ON UPDATE CURRENT_TIMESTAMP

Soft Deletes:
- Include deleted_at TIMESTAMP NULL
- Use for soft delete functionality
```

## 2.3 Relationship Standards

```
Relationships:
- Define foreign keys explicitly
- Use appropriate ON DELETE behavior
- Use appropriate ON UPDATE behavior
- Index foreign key columns

Relationship Types:
- One-to-One: Foreign key in either table
- One-to-Many: Foreign key in "many" table
- Many-to-Many: Junction table with two foreign keys
```

---

# 3. Schema Migration Plan

## 3.1 Core Tables Migration

### users Table

```
Current Schema:
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Target Schema (EBP Standard):
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY idx_users_username (username),
    UNIQUE KEY idx_users_email (email),
    KEY idx_users_status (status),
    KEY idx_users_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Changes:
- Change id to BIGINT UNSIGNED
- Add status column
- Add deleted_at column (soft delete)
- Add indexes
- Add engine and charset
```

### roles Table

```
Current Schema:
```sql
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Target Schema (EBP Standard):
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    is_system BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY idx_roles_name (name),
    KEY idx_roles_is_system (is_system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Changes:
- Change id to BIGINT UNSIGNED
- Add is_system column
- Add updated_at column
- Add deleted_at column
- Add indexes
- Add engine and charset
```

### permissions Table

```
Current Schema:
```sql
CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT
);
```

Target Schema (EBP Standard):
```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    module VARCHAR(50),
    action VARCHAR(50),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY idx_permissions_name (name),
    KEY idx_permissions_module (module),
    KEY idx_permissions_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Changes:
- Change id to BIGINT UNSIGNED
- Add module column
- Add action column
- Add updated_at column
- Add indexes
- Add engine and charset
```

### role_permissions Table

```
Current Schema:
```sql
CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id)
);
```

Target Schema (EBP Standard):
```sql
CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Changes:
- Change columns to BIGINT UNSIGNED
- Add foreign keys
- Add created_at column
- Add engine and charset
```

## 3.2 Product Tables Migration

### products Table

```
Current Schema:
```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    cost DECIMAL(10,2),
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Target Schema (EBP Standard):
```sql
CREATE TABLE products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id BIGINT UNSIGNED,
    sku VARCHAR(50),
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    cost DECIMAL(10,2),
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY idx_products_sku (sku),
    KEY idx_products_category_id (category_id),
    KEY idx_products_name (name),
    KEY idx_products_is_available (is_available),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Changes:
- Change id to BIGINT UNSIGNED
- Change category_id to BIGINT UNSIGNED
- Add sku column
- Add updated_at column
- Add deleted_at column
- Add indexes
- Add foreign key
- Add engine and charset
```

### orders Table

```
Current Schema:
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    table_id INT,
    status VARCHAR(20) DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Target Schema (EBP Standard):
```sql
CREATE TABLE orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_number VARCHAR(50) NOT NULL,
    user_id BIGINT UNSIGNED,
    table_id BIGINT UNSIGNED,
    reservation_id BIGINT UNSIGNED,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled') DEFAULT 'pending',
    subtotal DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0,
    payment_method VARCHAR(50),
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY idx_orders_order_number (order_number),
    KEY idx_orders_user_id (user_id),
    KEY idx_orders_table_id (table_id),
    KEY idx_orders_reservation_id (reservation_id),
    KEY idx_orders_status (status),
    KEY idx_orders_payment_status (payment_status),
    KEY idx_orders_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Changes:
- Change id to BIGINT UNSIGNED
- Add order_number column
- Change user_id to BIGINT UNSIGNED
- Change table_id to BIGINT UNSIGNED
- Add reservation_id column
- Change status to ENUM
- Add subtotal, tax, discount columns
- Add payment_method, payment_status columns
- Add notes column
- Add updated_at column
- Add deleted_at column
- Add indexes
- Add foreign keys
- Add engine and charset
```

---

# 4. Data Migration Plan

## 4.1 Migration Strategy

```
Strategy: Incremental Migration

Approach:
1. Create new schema in parallel
2. Migrate data incrementally
3. Validate data integrity
4. Switch to new schema
5. Decommission old schema
```

## 4.2 Migration Steps

### Step 1: Create New Schema

```sql
-- Create new database
CREATE DATABASE ebp_restoran_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use new database
USE ebp_restoran_db;

-- Create tables with EBP standards
-- (Execute all CREATE TABLE statements from Section 3)
```

### Step 2: Migrate Core Tables

```sql
-- Migrate users
INSERT INTO ebp_restoran_db.users (id, username, email, password, status, created_at, updated_at)
SELECT 
    id, 
    username, 
    email, 
    password, 
    'active' as status,
    created_at,
    created_at as updated_at
FROM restoran_db.users;

-- Migrate roles
INSERT INTO ebp_restoran_db.roles (id, name, description, is_system, created_at, updated_at)
SELECT 
    id, 
    name, 
    description, 
    FALSE as is_system,
    created_at,
    created_at as updated_at
FROM restoran_db.roles;

-- Migrate permissions
INSERT INTO ebp_restoran_db.permissions (id, name, description, created_at, updated_at)
SELECT 
    id, 
    name, 
    description, 
    NOW() as created_at,
    NOW() as updated_at
FROM restoran_db.permissions;

-- Migrate role_permissions
INSERT INTO ebp_restoran_db.role_permissions (role_id, permission_id, created_at)
SELECT 
    role_id, 
    permission_id, 
    NOW() as created_at
FROM restoran_db.role_permissions;
```

### Step 3: Migrate Product Tables

```sql
-- Migrate categories
INSERT INTO ebp_restoran_db.categories (id, name, description, created_at, updated_at)
SELECT 
    id, 
    name, 
    description, 
    created_at,
    created_at as updated_at
FROM restoran_db.categories;

-- Migrate products
INSERT INTO ebp_restoran_db.products (id, category_id, name, description, price, cost, image, is_available, created_at, updated_at)
SELECT 
    id, 
    category_id, 
    name, 
    description, 
    price, 
    cost, 
    image, 
    is_available,
    created_at,
    created_at as updated_at
FROM restoran_db.products;
```

### Step 4: Migrate Transaction Tables

```sql
-- Migrate tables
INSERT INTO ebp_restoran_db.tables (id, number, capacity, status, created_at, updated_at)
SELECT 
    id, 
    number, 
    capacity, 
    status,
    created_at,
    created_at as updated_at
FROM restoran_db.tables;

-- Migrate orders
INSERT INTO ebp_restoran_db.orders (id, order_number, user_id, table_id, status, subtotal, tax, discount, total_amount, paid_amount, payment_status, created_at, updated_at)
SELECT 
    id, 
    CONCAT('ORD', id) as order_number,
    user_id, 
    table_id, 
    status,
    total_amount as subtotal,
    0 as tax,
    0 as discount,
    total_amount,
    paid_amount,
    CASE WHEN paid_amount >= total_amount THEN 'paid' ELSE 'unpaid' END as payment_status,
    created_at,
    created_at as updated_at
FROM restoran_db.orders;
```

### Step 5: Validate Data

```sql
-- Validate row counts
SELECT 'users' as table_name, COUNT(*) as count FROM restoran_db.users
UNION ALL
SELECT 'users' as table_name, COUNT(*) as count FROM ebp_restoran_db.users;

-- Validate data integrity
SELECT 'users' as table_name, COUNT(*) as count FROM restoran_db.users
WHERE id NOT IN (SELECT id FROM ebp_restoran_db.users);
```

### Step 6: Switch to New Schema

```sql
-- Update application configuration to use new database
-- (Update config/database.php)

-- Test application with new database
-- (Run smoke tests)

-- If successful, proceed
-- If failed, rollback to old database
```

### Step 7: Decommission Old Schema

```sql
-- After successful migration and testing period (e.g., 30 days)

-- Backup old database
-- (Run backup script)

-- Drop old database
DROP DATABASE restoran_db;
```

## 4.3 Rollback Plan

```
If migration fails:

1. Stop management process
2. Update application configuration to use old database
3. Verify application functionality
4. Investigate failure cause
5. Fix issues
6. Retry migration
```

---

# 5. Migration Timeline

## 5.1 Timeline

```
Week 1: Planning and Preparation
- Analyze current schema
- Design target schema
- Create migration scripts
- Test migration scripts in staging

Week 2: Data Migration
- Create new schema
- Migrate core tables
- Migrate product tables
- Migrate transaction tables

Week 3: Validation and Testing
- Validate data integrity
- Test application functionality
- Performance testing
- Security testing

Week 4: Cutover
- Schedule maintenance window
- Execute final data sync
- Switch to new schema
- Monitor application
- Decommission old schema
```

## 5.2 Downtime Estimate

```
Estimated Downtime: 2-4 hours

Breakdown:
- Final data sync: 30 minutes
- Application cutover: 15 minutes
- Smoke testing: 30 minutes
- Buffer time: 45-105 minutes
```

---

# 6. Risk Assessment

## 6.1 Data Loss Risk

```
Risk: Data loss during migration

Mitigation:
- Full backup before migration
- Test migration in staging
- Validate data integrity
- Keep backup for 30 days
```

## 6.2 Downtime Risk

```
Risk: Extended downtime

Mitigation:
- Detailed migration plan
- Practice in staging
- Buffer time in schedule
- Rollback plan ready
```

## 6.3 Application Compatibility Risk

```
Risk: Application incompatibility with new schema

Mitigation:
- Update application code before migration
- Test thoroughly in staging
- Keep old schema available for rollback
- Monitor closely after cutover
```

---

# Document End

**Document ID:** ESAMF-SAHAM-007

**Version:** 1.0
