# RESTAURANT ERP

# Migration Plan

**Document ID:** EBP-PRODUCT-RESTAURANT-MIGRATION-001
**Version:** 1.0
**Category:** Database
**Status:** Official Migration Strategy

---

# 1. Migration Overview

Dokumen ini mendefinisikan strategi migrasi database untuk Restaurant ERP, mencakup initial setup, incremental changes, dan rollback procedures.

---

# 2. Migration Philosophy

EBP menggunakan prinsip:

> Migration must be backward compatible, reversible, and zero-downtime.

Artinya:

* Migration tidak boleh memutus aplikasi
* Migration harus bisa di-rollback
* Migration harus mendukung multiple version

---

# 3. Migration Strategy

## 3.1 Migration Type

### Schema Migration

Perubahan struktur database:

```sql
ALTER TABLE
ADD COLUMN
DROP COLUMN
MODIFY COLUMN
CREATE INDEX
DROP INDEX
```

### Data Migration

Perubahan data:

```sql
UPDATE
INSERT
DELETE
Data transformation
```

### Feature Migration

Perubahan fitur yang memerlukan database:

* New module
* New business logic
* New integration

---

# 4. Migration Naming Convention

Format:

```
YYYYMMDD_HHMMSS_description.sql
```

Contoh:

```
20240101_080000_create_users_table.sql
20240102_090000_add_email_index.sql
20240103_100000_migrate_customer_data.sql
```

---

# 5. Migration Structure

Setiap migration file memiliki:

```sql
-- UP Migration
-- Dijalankan saat upgrade

-- DOWN Migration
-- Dijalankan saat rollback
```

Contoh:

```sql
-- UP
ALTER TABLE users ADD COLUMN phone VARCHAR(20);

-- DOWN
ALTER TABLE users DROP COLUMN phone;
```

---

# 6. Migration Phases

## Phase 1: Initial Setup

### M1-001: Create Foundation Tables

**File:** `20240101_080000_create_foundation_tables.sql`

**Description:** Membuat tabel foundation EBP

**Tables:**
- users
- roles
- permissions
- role_permissions
- user_roles
- tenants
- configurations
- audit_logs

**Dependencies:** None

**Rollback:** DROP TABLE semua tabel

---

### M1-002: Create Master Data Tables

**File:** `20240102_080000_create_master_data_tables.sql`

**Description:** Membuat tabel master data

**Tables:**
- restaurants
- branches
- tables
- menu_categories
- menu_items
- menu_modifiers
- customers
- suppliers
- employees

**Dependencies:** M1-001

**Rollback:** DROP TABLE semua tabel

---

### M1-003: Create POS Tables

**File:** `20240103_080000_create_pos_tables.sql`

**Description:** Membuat tabel operasional POS

**Tables:**
- orders
- order_items
- payments
- reservations

**Dependencies:** M1-002

**Rollback:** DROP TABLE semua tabel

---

### M1-004: Create Inventory Tables

**File:** `20240104_080000_create_inventory_tables.sql`

**Description:** Membuat tabel inventory

**Tables:**
- inventory_items
- stock_movements
- purchase_orders
- purchase_order_items
- goods_receipts
- goods_receipt_items
- stock_opnames
- stock_opname_items
- recipes
- recipe_ingredients

**Dependencies:** M1-002

**Rollback:** DROP TABLE semua tabel

---

### M1-005: Create Finance Tables

**File:** `20240105_080000_create_finance_tables.sql`

**Description:** Membuat tabel keuangan

**Tables:**
- cashier_shifts
- expenses
- accounting_journals
- journal_lines

**Dependencies:** M1-003

**Rollback:** DROP TABLE semua tabel

---

## Phase 2: Incremental Changes

### M2-001: Add User Avatar

**File:** `20240110_080000_add_user_avatar.sql`

**Description:** Menambah kolom avatar ke tabel users

**Change:**
```sql
-- UP
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) AFTER phone;

-- DOWN
ALTER TABLE users DROP COLUMN avatar;
```

**Dependencies:** M1-001

**Rollback:** DROP COLUMN avatar

---

### M2-002: Add Menu Item Tags

**File:** `20240111_080000_add_menu_item_tags.sql`

**Description:** Menambah kolom tags ke tabel menu_items

**Change:**
```sql
-- UP
ALTER TABLE menu_items ADD COLUMN tags JSON AFTER sort_order;

-- DOWN
ALTER TABLE menu_items DROP COLUMN tags;
```

**Dependencies:** M1-002

**Rollback:** DROP COLUMN tags

---

### M2-003: Add Order Source

**File:** `20240112_080000_add_order_source.sql`

**Description:** Menambah kolom source ke tabel orders

**Change:**
```sql
-- UP
ALTER TABLE orders ADD COLUMN source VARCHAR(20) DEFAULT 'POS' AFTER order_type;

-- DOWN
ALTER TABLE orders DROP COLUMN source;
```

**Dependencies:** M1-003

**Rollback:** DROP COLUMN source

---

### M2-004: Add Inventory Expiry Alert

**File:** `20240113_080000_add_inventory_expiry_alert.sql`

**Description:** Menambah kolom expiry_alert_days ke tabel inventory_items

**Change:**
```sql
-- UP
ALTER TABLE inventory_items ADD COLUMN expiry_alert_days INT DEFAULT 30 AFTER reorder_point;

-- DOWN
ALTER TABLE inventory_items DROP COLUMN expiry_alert_days;
```

**Dependencies:** M1-004

**Rollback:** DROP COLUMN expiry_alert_days

---

## Phase 3: Data Migration

### M3-001: Migrate Customer Phone Format

**File:** `20240120_080000_migrate_customer_phone.sql`

**Description:** Normalisasi format nomor telepon customer

**Change:**
```sql
-- UP
UPDATE customers SET phone = REPLACE(phone, '+62', '0') WHERE phone LIKE '+62%';

-- DOWN
-- Manual rollback required
```

**Dependencies:** M1-002

**Rollback:** Manual restore from backup

---

### M3-002: Migrate Menu Price History

**File:** `20240121_080000_migrate_menu_price_history.sql`

**Description:** Membuat tabel history harga menu

**Change:**
```sql
-- UP
CREATE TABLE menu_price_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    menu_item_id BIGINT NOT NULL,
    old_price DECIMAL(15, 2),
    new_price DECIMAL(15, 2),
    changed_at DATETIME NOT NULL,
    changed_by BIGINT,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- DOWN
DROP TABLE menu_price_history;
```

**Dependencies:** M1-002

**Rollback:** DROP TABLE menu_price_history

---

# 7. Migration Execution

## 7.1 Pre-Migration Checklist

Sebelum menjalankan migration:

- [ ] Backup database
- [ ] Review migration script
- [ ] Test di staging environment
- [ ] Schedule maintenance window (jika perlu)
- [ ] Notify stakeholders
- [ ] Prepare rollback plan

## 7.2 Migration Process

### Step 1: Backup

```bash
mysqldump -u root -p ebp_restaurant > backup_YYYYMMDD_HHMMSS.sql
```

### Step 2: Review

Review migration script:

```bash
# Check syntax
mysql -u root -p ebp_restaurant < migration_file.sql --dry-run
```

### Step 3: Execute

```bash
# Execute migration
mysql -u root -p ebp_restaurant < migration_file.sql
```

### Step 4: Verify

Verify migration:

```sql
-- Check table structure
DESCRIBE table_name;

-- Check data
SELECT COUNT(*) FROM table_name;

-- Check indexes
SHOW INDEX FROM table_name;
```

### Step 5: Update Version

Update migration version:

```sql
INSERT INTO migrations (version, file, executed_at) 
VALUES ('001', '20240101_080000_create_foundation_tables.sql', NOW());
```

## 7.3 Rollback Process

Jika migration gagal:

### Step 1: Stop Application

```bash
# Stop application server
systemctl stop nginx
systemctl stop php-fpm
```

### Step 2: Execute DOWN Migration

```bash
# Execute DOWN migration
mysql -u root -p ebp_restaurant < migration_file_down.sql
```

### Step 3: Restore Backup (jika perlu)

```bash
# Restore from backup
mysql -u root -p ebp_restaurant < backup_YYYYMMDD_HHMMSS.sql
```

### Step 4: Start Application

```bash
# Start application server
systemctl start php-fpm
systemctl start nginx
```

### Step 5: Verify

Verify system functionality:

```bash
# Run health check
curl http://localhost/health
```

---

# 8. Migration Tracking

## 8.1 Migrations Table

Tabel untuk tracking migration:

```sql
CREATE TABLE migrations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(20) NOT NULL,
    file VARCHAR(255) NOT NULL,
    executed_at DATETIME NOT NULL,
    execution_time INT,
    status VARCHAR(20) DEFAULT 'SUCCESS',
    error_message TEXT,
    UNIQUE KEY uk_version (version)
);
```

## 8.2 Migration Status

Status migration:

- PENDING
- RUNNING
- SUCCESS
- FAILED
- ROLLED_BACK

---

# 9. Zero-Downtime Migration

Untuk production, gunakan zero-downtime strategy:

## 9.1 Add Column

Strategy:

```sql
-- Step 1: Add column with default value
ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL;

-- Step 2: Update data in batches
UPDATE users SET phone = '...' WHERE id BETWEEN 1 AND 1000;
UPDATE users SET phone = '...' WHERE id BETWEEN 1001 AND 2000;

-- Step 3: Add NOT NULL constraint
ALTER TABLE users MODIFY COLUMN phone VARCHAR(20) NOT NULL;
```

## 9.2 Rename Column

Strategy:

```sql
-- Step 1: Add new column
ALTER TABLE users ADD COLUMN new_name VARCHAR(100);

-- Step 2: Copy data
UPDATE users SET new_name = old_name;

-- Step 3: Update application to use new column

-- Step 4: Drop old column
ALTER TABLE users DROP COLUMN old_name;
```

## 9.3 Modify Column Type

Strategy:

```sql
-- Step 1: Add new column with new type
ALTER TABLE users ADD COLUMN phone_new VARCHAR(20);

-- Step 2: Copy and transform data
UPDATE users SET phone_new = CAST(phone AS CHAR(20));

-- Step 3: Update application to use new column

-- Step 4: Drop old column
ALTER TABLE users DROP COLUMN phone;

-- Step 5: Rename new column
ALTER TABLE users RENAME COLUMN phone_new TO phone;
```

---

# 10. Migration Testing

## 10.1 Unit Test

Test individual migration:

```php
public function testMigration()
{
    // Execute migration
    $this->runMigration('20240101_080000_create_users_table.sql');
    
    // Verify table exists
    $this->assertTableExists('users');
    
    // Verify columns
    $this->assertColumnExists('users', 'email');
    
    // Rollback
    $this->rollbackMigration('20240101_080000_create_users_table.sql');
    
    // Verify table dropped
    $this->assertTableNotExists('users');
}
```

## 10.2 Integration Test

Test migration with application:

```php
public function testMigrationWithApplication()
{
    // Execute migration
    $this->runMigration('20240101_080000_create_users_table.sql');
    
    // Test application functionality
    $user = $this->createUser();
    $this->assertNotNull($user);
    
    // Rollback
    $this->rollbackMigration('20240101_080000_create_users_table.sql');
}
```

## 10.3 Performance Test

Test migration performance:

```php
public function testMigrationPerformance()
{
    $startTime = microtime(true);
    
    $this->runMigration('20240101_080000_create_users_table.sql');
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    
    $this->assertLessThan(60, $duration); // Must complete in 60 seconds
}
```

---

# 11. Migration Best Practices

## 11.1 DO

- Always write DOWN migration
- Test migration di staging
- Backup sebelum migration
- Use transaction untuk data migration
- Monitor migration execution
- Document migration changes
- Use version control untuk migration files

## 11.2 DON'T

- Jangan mengubah data tanpa backup
- Jangan menjalankan migration di production tanpa testing
- Jangan menggabungkan banyak perubahan dalam satu migration
- Jangan menggunakan migration untuk data cleanup rutin
- Jangan menghapus column tanpa memastikan tidak ada dependency
- Jangan mengubah primary key di production

---

# 12. Migration Rollback Scenarios

## Scenario 1: Syntax Error

Masalah: Migration script memiliki syntax error

Solusi:
1. Fix syntax error
2. Re-execute migration
3. No rollback needed

## Scenario 2: Data Error

Masalah: Data tidak valid untuk schema baru

Solusi:
1. Fix data di staging
2. Create data fix migration
3. Execute data fix migration
4. Re-execute schema migration

## Scenario 3: Application Error

Masalah: Application tidak compatible dengan schema baru

Solusi:
1. Rollback migration
2. Fix application code
3. Re-execute migration
4. Deploy application

## Scenario 4: Performance Issue

Masalah: Migration terlalu lambat

Solusi:
1. Stop migration
2. Optimize migration script
3. Execute in batches
4. Re-execute migration

---

# 13. Migration Automation

## 13.1 Migration Tool

Gunakan migration tool:

```bash
# List pending migrations
php migrate:status

# Execute pending migrations
php migrate:up

# Rollback last migration
php migrate:down

# Rollback specific migration
php migrate:rollback --version=001

# Create new migration
php migrate:create add_user_avatar
```

## 13.2 CI/CD Integration

Integrate migration dengan CI/CD:

```yaml
# .gitlab-ci.yml
deploy:
  script:
    - php migrate:up
    - php artisan cache:clear
    - php artisan queue:restart
  only:
    - master
```

---

# 14. Migration Documentation

Setiap migration harus didokumentasi:

## 14.1 Migration Log

```sql
CREATE TABLE migration_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    migration_id BIGINT,
    action VARCHAR(20),
    details TEXT,
    executed_at DATETIME NOT NULL,
    executed_by VARCHAR(100),
    FOREIGN KEY (migration_id) REFERENCES migrations(id)
);
```

## 14.2 Change Log

Dokumentasi perubahan:

```markdown
## [2024-01-01] Add User Avatar

**Migration:** 20240101_080000_add_user_avatar.sql

**Description:** Menambah kolom avatar untuk profil user

**Impact:** Low

**Rollback:** DROP COLUMN avatar

**Testing:** Unit test, integration test
```

---

# 15. Emergency Procedures

## 15.1 Migration Failure

Jika migration gagal di production:

1. Stop migration
2. Assess impact
3. Decide: rollback or fix
4. Execute decision
5. Notify stakeholders
6. Document incident

## 15.2 Data Corruption

Jika data terkorupsi:

1. Stop application
2. Restore from backup
3. Investigate cause
4. Fix issue
5. Re-execute migration
6. Verify data integrity

---

# END OF DOCUMENT

Document ID: EBP-PRODUCT-RESTAURANT-MIGRATION-001
Version: 1.0
