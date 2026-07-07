# Database Migration Template

## Purpose

Template for creating database migrations following RESTAURANT_ERP conventions.

## REASONS Canvas

### R - Requirements

**Migration Requirements:**
- Migration number: [XXX]
- Migration name: [MIGRATION_NAME]
- Purpose: [Describe migration purpose]
- Tables affected: [List tables]

### E - Entities

**Database Entities:**
- New tables: [List new tables]
- Modified tables: [List modified tables]
- Dropped tables: [List dropped tables]

### A - Approach

**Migration Strategy:**
1. Create migration file
2. Define table structure
3. Add indexes
4. Add foreign keys
5. Add constraints
6. Test migration
7. Document changes

### S - Structure

**Migration File Structure:**
```sql
-- Migration XXX: [MIGRATION_NAME]
-- Description: [Description]
-- Date: [YYYY-MM-DD]

-- Comments and documentation
```

### O - Operations

**Migration Steps:**

1. **Create Migration File**
   - Name: `MIGRATION_XXX_[NAME].sql`
   - Location: `DATABASE/`
   - Follow naming convention

2. **Define Tables**
   - Use standard column types
   - Add audit fields
   - Add indexes
   - Add foreign keys

3. **Add Constraints**
   - Primary keys
   - Foreign keys
   - Unique constraints
   - Check constraints

4. **Test Migration**
   - Test on staging
   - Verify data integrity
   - Check performance
   - Rollback if needed

### N - Norms

**Database Standards:**
- Use snake_case for table/column names
- Use utf8mb4 charset
- Use InnoDB engine
- Add audit fields (created_at, updated_at, deleted_at)
- Add tenant_id for multi-tenant tables
- Use BIGINT UNSIGNED for IDs
- Use TIMESTAMP for dates

**Naming Conventions:**
- Tables: plural snake_case (e.g., users, orders)
- Columns: snake_case (e.g., user_id, created_at)
- Foreign keys: fk_[table]_[column] (e.g., fk_orders_user_id)
- Indexes: idx_[table]_[column] (e.g., idx_orders_user_id)

### S - Safeguards

**Non-negotiable Rules:**
- MUST use InnoDB engine
- MUST use utf8mb4 charset
- MUST add tenant_id for multi-tenant tables
- MUST add audit fields
- MUST use foreign keys for relationships
- MUST add indexes for foreign keys
- MUST test before production

## Implementation

### Migration File Template

Create file: `DATABASE/MIGRATION_XXX_[MIGRATION_NAME].sql`

```sql
-- Migration XXX: [MIGRATION_NAME]
-- Description: [Detailed description of migration]
-- Author: [Author name]
-- Date: [YYYY-MM-DD]
-- Dependencies: [List dependent migrations]

-- =====================================================
-- Table Creation
-- =====================================================

CREATE TABLE IF NOT EXISTS [table_name] (
    [table_name]_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    
    -- Add columns here
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    
    -- Audit fields
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign keys
    CONSTRAINT fk_[table]_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_[table]_tenant (tenant_id),
    INDEX idx_[table]_status (status),
    INDEX idx_[table]_created_at (created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='[Table description]';

-- =====================================================
-- Table Modification
-- =====================================================

-- Add column
ALTER TABLE [table_name] 
ADD COLUMN [column_name] [COLUMN_TYPE] [CONSTRAINTS] AFTER [after_column];

-- Modify column
ALTER TABLE [table_name] 
MODIFY COLUMN [column_name] [COLUMN_TYPE] [CONSTRAINTS];

-- Drop column
ALTER TABLE [table_name] 
DROP COLUMN [column_name];

-- Add index
ALTER TABLE [table_name] 
ADD INDEX idx_[table]_[column] ([column]);

-- Drop index
ALTER TABLE [table_name] 
DROP INDEX idx_[table]_[column];

-- Add foreign key
ALTER TABLE [table_name] 
ADD CONSTRAINT fk_[table]_[column] 
FOREIGN KEY ([column]) REFERENCES [referenced_table]([referenced_column])
ON DELETE [ACTION] ON UPDATE [ACTION];

-- Drop foreign key
ALTER TABLE [table_name] 
DROP FOREIGN KEY fk_[table]_[column];

-- =====================================================
-- Data Insertion
-- =====================================================

-- Insert default data
INSERT INTO [table_name] (tenant_id, name, status, created_at) VALUES
(1, '[Default Value 1]', 'ACTIVE', NOW()),
(1, '[Default Value 2]', 'ACTIVE', NOW());

-- =====================================================
-- Data Migration
-- =====================================================

-- Migrate data from old structure to new structure
UPDATE [table_name] 
SET [new_column] = [old_column]
WHERE [condition];

-- =====================================================
-- Rollback Script (for manual rollback if needed)
-- =====================================================

-- DROP TABLE IF EXISTS [table_name];
-- ALTER TABLE [table_name] DROP COLUMN [column_name];
-- ALTER TABLE [table_name] DROP FOREIGN KEY fk_[table]_[column];
```

## Common Column Types

**Numeric:**
- `BIGINT UNSIGNED` - IDs (auto-increment)
- `INT` - Counters, small numbers
- `DECIMAL(10,2)` - Monetary values
- `FLOAT` - Percentages, ratios

**String:**
- `VARCHAR(50)` - Short strings (codes, names)
- `VARCHAR(255)` - Medium strings (names, emails)
- `TEXT` - Long text (descriptions, notes)
- `LONGTEXT` - Very long text (content)

**Date/Time:**
- `DATE` - Dates without time
- `TIME` - Time without date
- `DATETIME` - Date and time
- `TIMESTAMP` - Auto-updating timestamps
- `YEAR` - Year only

**Boolean:**
- `TINYINT(1)` - Boolean (0/1)

**Enum:**
- `ENUM('VALUE1', 'VALUE2', ...)` - Fixed set of values

## Standard Audit Fields

```sql
created_by BIGINT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_by BIGINT,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
deleted_at TIMESTAMP NULL
```

## Standard Multi-Tenant Fields

```sql
tenant_id BIGINT UNSIGNED NOT NULL,
CONSTRAINT fk_[table]_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
INDEX idx_[table]_tenant (tenant_id)
```

## Standard Status Field

```sql
status ENUM('ACTIVE', 'INACTIVE', 'BLOCKED') DEFAULT 'ACTIVE',
INDEX idx_[table]_status (status)
```

## Testing Migration

**Test on Staging:**
```bash
# Backup database
mysqldump -u root -p ebp_restaurant_db > backup_before_migration.sql

# Apply migration
mysql -u root -p ebp_restaurant_db < DATABASE/MIGRATION_XXX_[NAME].sql

# Verify
mysql -u root -p ebp_restaurant_db -e "DESCRIBE [table_name];"
mysql -u root -p ebp_restaurant_db -e "SHOW CREATE TABLE [table_name];"
```

**Rollback if needed:**
```bash
# Restore backup
mysql -u root -p ebp_restaurant_db < backup_before_migration.sql
```

## Migration Checklist

- [ ] Migration file created
- [ ] Table structure defined
- [ ] Columns properly typed
- [ ] Audit fields added
- [ ] Tenant isolation added
- [ ] Foreign keys defined
- [ ] Indexes added
- [ ] Constraints added
- [ ] Default data inserted
- [ ] Migration tested on staging
- [ ] Rollback script prepared
- [ ] Migration documented
- [ ] Database documentation updated

## Best Practices

1. **Always backup before migration**
2. **Test on staging first**
3. **Use transactions for complex migrations**
4. **Add comments for complex logic**
5. **Document breaking changes**
6. **Keep migrations reversible**
7. **Use descriptive table/column names**
8. **Add appropriate indexes**
9. **Use foreign keys for relationships**
10. **Consider data migration for existing data**

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
