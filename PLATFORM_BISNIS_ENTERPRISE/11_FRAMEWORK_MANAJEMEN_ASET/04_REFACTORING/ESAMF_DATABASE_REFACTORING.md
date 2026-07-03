# ESAMF Database Refactoring

**Document ID:** ESAMF-REFACTORING-002

**Version:** 1.0

**Purpose:** Define the database refactoring standards for ESAMF

---

# Overview

Database Refactoring is the process of improving database structure, performance, and compliance with EBP standards while preserving data integrity. This standard defines the approach for migrating databases to EBP standards.

---

# Refactoring Principles

## 1. Data Integrity Preservation

**Database refactoring must preserve data integrity.**

- No data loss
- No data corruption
- Referential integrity maintained
- Business rules preserved

## 2. Backward Compatibility

**Database changes should maintain backward compatibility where possible.**

- Use migration scripts
- Support old schema during transition
- Provide rollback capability
- Document breaking changes

## 3. EBP Standards Compliance

**Refactored database must comply with EBP database standards.**

- Naming conventions
- Structure standards
- Index standards
- Constraint standards

## 4. Performance Optimization

**Database refactoring should improve performance.**

- Optimize indexes
- Optimize queries
- Optimize data types
- Optimize relationships

---

# Refactoring Process

## Phase 1: Analysis

### Step 1: Analyze Current Schema

```
Table: [Table Name]
Columns:
- [Column 1: Type, Constraints]
- [Column 2: Type, Constraints]
- [Column 3: Type, Constraints]

Indexes:
- [Index 1: Columns, Type]
- [Index 2: Columns, Type]

Relationships:
- [Relationship 1: Type, Referenced Table]
- [Relationship 2: Type, Referenced Table]
```

### Step 2: Identify Refactoring Needs

```
Naming Issues:
- [Issue 1: Current, Should be]
- [Issue 2: Current, Should be]

Structure Issues:
- [Issue 1: Description]
- [Issue 2: Description]

Performance Issues:
- [Issue 1: Description]
- [Issue 2: Description]
```

### Step 3: Create Migration Plan

```
Migration Steps:
1. [Step 1: Description, Risk]
2. [Step 2: Description, Risk]
3. [Step 3: Description, Risk]

Rollback Plan:
1. [Rollback Step 1]
2. [Rollback Step 2]
3. [Rollback Step 3]
```

### Step 4: Backup Database

```bash
# Create backup
mysqldump -u [user] -p [database] > backup_[timestamp].sql

# Verify backup
mysql -u [user] -p [database] < backup_[timestamp].sql
```

---

## Phase 2: Naming Refactoring

### Step 1: Apply EBP Naming Conventions

#### Table Naming

**EBP Standard:** snake_case, lowercase, plural

**Before:**
```sql
CREATE TABLE UserInfo (
    id INT PRIMARY KEY,
    UserName VARCHAR(255),
    EmailAddress VARCHAR(255)
);
```

**After:**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    username VARCHAR(255),
    email VARCHAR(255)
);
```

#### Column Naming

**EBP Standard:** snake_case, lowercase

**Before:**
```sql
CREATE TABLE users (
    ID INT PRIMARY KEY,
    FirstName VARCHAR(255),
    LastName VARCHAR(255),
    EmailAddress VARCHAR(255)
);
```

**After:**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    email VARCHAR(255)
);
```

#### Index Naming

**EBP Standard:** idx_[table]_[column]_[type]

**Before:**
```sql
CREATE INDEX idx_username ON users(username);
```

**After:**
```sql
CREATE INDEX idx_users_username ON users(username);
```

### Step 2: Rename Tables

```sql
-- Migration script
ALTER TABLE UserInfo RENAME TO users;

-- Rollback script
ALTER TABLE users RENAME TO UserInfo;
```

### Step 3: Rename Columns

```sql
-- Migration script
ALTER TABLE users 
CHANGE COLUMN UserName username VARCHAR(255),
CHANGE COLUMN EmailAddress email VARCHAR(255);

-- Rollback script
ALTER TABLE users 
CHANGE COLUMN username UserName VARCHAR(255),
CHANGE COLUMN email EmailAddress VARCHAR(255);
```

---

## Phase 3: Structure Refactoring

### Step 1: Add Standard Columns

#### Add Timestamps

```sql
-- Migration script
ALTER TABLE users 
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Rollback script
ALTER TABLE users 
DROP COLUMN created_at,
DROP COLUMN updated_at;
```

#### Add Soft Delete

```sql
-- Migration script
ALTER TABLE users 
ADD COLUMN deleted_at TIMESTAMP NULL;

-- Rollback script
ALTER TABLE users 
DROP COLUMN deleted_at;
```

#### Add Tenant ID (for multi-tenancy)

```sql
-- Migration script
ALTER TABLE users 
ADD COLUMN tenant_id INT NOT NULL DEFAULT 1,
ADD INDEX idx_users_tenant_id (tenant_id);

-- Rollback script
ALTER TABLE users 
DROP COLUMN tenant_id,
DROP INDEX idx_users_tenant_id;
```

### Step 2: Standardize Primary Keys

**EBP Standard:** id, INT UNSIGNED, AUTO_INCREMENT

**Before:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT
);
```

**After:**
```sql
CREATE TABLE users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT
);
```

### Step 3: Standardize Foreign Keys

**EBP Standard:** [table]_id, INT UNSIGNED, NOT NULL, INDEX

**Before:**
```sql
CREATE TABLE orders (
    user_id INT,
    INDEX idx_user_id (user_id)
);
```

**After:**
```sql
CREATE TABLE orders (
    user_id INT UNSIGNED NOT NULL,
    INDEX idx_orders_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
);
```

### Step 4: Normalize Data

**Before:**
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY,
    user_name VARCHAR(255),
    user_email VARCHAR(255)
);
```

**After:**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255)
);

CREATE TABLE orders (
    id INT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    INDEX idx_orders_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## Phase 4: Performance Refactoring

### Step 1: Add Missing Indexes

```sql
-- Identify missing indexes
-- Add indexes for foreign keys
CREATE INDEX idx_orders_user_id ON orders(user_id);

-- Add indexes for frequently queried columns
CREATE INDEX idx_users_email ON users(email);

-- Add composite indexes for common query patterns
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
```

### Step 2: Optimize Data Types

**Before:**
```sql
CREATE TABLE users (
    id BIGINT,
    status VARCHAR(255),
    is_active TINYINT(1)
);
```

**After:**
```sql
CREATE TABLE users (
    id INT UNSIGNED,
    status ENUM('active', 'inactive', 'suspended'),
    is_active BOOLEAN
);
```

### Step 3: Remove Redundant Indexes

```sql
-- Identify redundant indexes
-- Remove duplicates
DROP INDEX idx_users_email_duplicate ON users(email);
```

### Step 4: Add Partitioning (for large tables)

```sql
-- Partition by date
ALTER TABLE orders 
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2020 VALUES LESS THAN (2021),
    PARTITION p2021 VALUES LESS THAN (2022),
    PARTITION p2022 VALUES LESS THAN (2023),
    PARTITION pmax VALUES LESS THAN MAXVALUE
);
```

---

## Phase 5: Security Refactoring

### Step 1: Add Constraints

```sql
-- Add NOT NULL constraints
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) NOT NULL;

-- Add UNIQUE constraints
ALTER TABLE users 
ADD UNIQUE INDEX idx_users_email_unique (email);

-- Add CHECK constraints (MySQL 8.0+)
ALTER TABLE users 
ADD CONSTRAINT chk_users_age CHECK (age >= 18);
```

### Step 2: Encrypt Sensitive Data

```sql
-- Use AES_ENCRYPT for sensitive columns
UPDATE users 
SET ssn = AES_ENCRYPT(ssn, '[encryption_key]');

-- Add trigger for automatic encryption
DELIMITER //
CREATE TRIGGER encrypt_ssn_before_insert
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    SET NEW.ssn = AES_ENCRYPT(NEW.ssn, '[encryption_key]');
END//
DELIMITER ;
```

### Step 3: Add Audit Trail

```sql
-- Create audit table
CREATE TABLE users_audit (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_values JSON,
    new_values JSON,
    changed_by INT UNSIGNED NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_audit_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Add trigger for audit trail
DELIMITER //
CREATE TRIGGER audit_users_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO users_audit (user_id, action, old_values, new_values, changed_by)
    VALUES (OLD.id, 'UPDATE', JSON_OBJECT(OLD.*), JSON_OBJECT(NEW.*), @current_user_id);
END//
DELIMITER ;
```

---

## Phase 6: Migration Scripts

### Step 1: Create Migration Script

```php
<?php
// database/migrations/2024_01_01_000000_refactor_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename columns
            $table->renameColumn('UserName', 'username');
            $table->renameColumn('EmailAddress', 'email');
            
            // Add standard columns
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->onUpdate('CURRENT_TIMESTAMP');
            $table->timestamp('deleted_at')->nullable();
            
            // Add indexes
            $table->index('email', 'idx_users_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Rollback changes
            $table->renameColumn('username', 'UserName');
            $table->renameColumn('email', 'EmailAddress');
            
            $table->dropIndex('idx_users_email');
            $table->dropColumn(['created_at', 'updated_at', 'deleted_at']);
        });
    }
}
```

### Step 2: Test Migration

```bash
# Run migration
php artisan migrate

# Verify schema
php artisan schema:dump

# Test application
php artisan test
```

### Step 3: Rollback if Needed

```bash
# Rollback migration
php artisan migrate:rollback

# Verify rollback
php artisan schema:dump
```

---

# Common Database Refactoring Patterns

## Pattern 1: Split Table

**When:** Table has too many columns or mixed concerns

**How:** Split into multiple related tables

**Before:**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    username VARCHAR(255),
    email VARCHAR(255),
    address VARCHAR(255),
    city VARCHAR(255),
    state VARCHAR(255),
    zip VARCHAR(255)
);
```

**After:**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    username VARCHAR(255),
    email VARCHAR(255)
);

CREATE TABLE user_addresses (
    id INT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    address VARCHAR(255),
    city VARCHAR(255),
    state VARCHAR(255),
    zip VARCHAR(255),
    INDEX idx_user_addresses_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Pattern 2: Merge Tables

**When:** Tables have similar structure and can be combined

**How:** Merge into single table with type discriminator

**Before:**
```sql
CREATE TABLE customers (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255)
);

CREATE TABLE suppliers (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255)
);
```

**After:**
```sql
CREATE TABLE contacts (
    id INT PRIMARY KEY,
    type ENUM('customer', 'supplier') NOT NULL,
    name VARCHAR(255),
    email VARCHAR(255),
    INDEX idx_contacts_type (type)
);
```

## Pattern 3: Add Junction Table

**When:** Many-to-many relationship needed

**How:** Create junction table

**Before:**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    role_ids VARCHAR(255) -- Comma-separated
);
```

**After:**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY
);

CREATE TABLE roles (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE user_roles (
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, role_id),
    INDEX idx_user_roles_user_id (user_id),
    INDEX idx_user_roles_role_id (role_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

## Pattern 4: Denormalize for Performance

**When:** Read performance is critical and data is read-heavy

**How:** Add redundant data

**Before:**
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL
);

CREATE TABLE order_items (
    id INT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2),
    quantity INT
);
```

**After:**
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    total DECIMAL(10, 2) -- Denormalized total
);

CREATE TABLE order_items (
    id INT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2),
    quantity INT
);
```

---

# Database Refactoring Checklist

## Analysis
- [ ] Current schema analyzed
- [ ] Refactoring needs identified
- [ ] Migration plan created
- [ ] Database backed up

## Naming
- [ ] Table naming applied
- [ ] Column naming applied
- [ ] Index naming applied
- [ ] Old names deprecated

## Structure
- [ ] Standard columns added
- [ ] Primary keys standardized
- [ ] Foreign keys standardized
- [ ] Data normalized

## Performance
- [ ] Missing indexes added
- [ ] Data types optimized
- [ ] Redundant indexes removed
- [ ] Partitioning added (if needed)

## Security
- [ ] Constraints added
- [ ] Sensitive data encrypted
- [ ] Audit trail added
- [ ] Access controls reviewed

## Migration
- [ ] Migration script created
- [ ] Migration tested
- [ ] Rollback tested
- [ ] Migration executed

## Verification
- [ ] Schema verified
- [ ] Data integrity verified
- [ ] Application tested
- [ ] Performance verified

---

# Document End

**Document ID:** ESAMF-REFACTORING-002

**Version:** 1.0
