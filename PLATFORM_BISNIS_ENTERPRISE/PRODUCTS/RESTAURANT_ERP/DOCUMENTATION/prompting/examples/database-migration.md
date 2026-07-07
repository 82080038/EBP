# Example: Database Migration

## Scenario

Create a database migration to add a new table for tracking customer loyalty points and rewards.

## REASONS Canvas Prompt

### R - Requirements

**Problem**: Restaurant needs to track customer loyalty points, rewards redemption, and tier progression to implement a customer loyalty program.

**Definition of Done**:
- Database migration file created
- loyalty_points table with proper structure
- loyalty_rewards table with proper structure
- customer_loyalty table for tracking customer status
- Foreign key relationships defined
- Indexes for performance
- Migration tested on staging
- Rollback script prepared

### E - Entities

**Database Entities**:
- loyalty_points (point transactions)
- loyalty_rewards (available rewards)
- customer_loyalty (customer loyalty status)

**Relationships**:
- loyalty_points belongs to customer
- loyalty_rewards belongs to tenant
- customer_loyalty belongs to customer

### A - Approach

**Strategy**:
1. Create migration file following naming convention
2. Define table structures with proper columns
3. Add foreign key relationships
4. Add indexes for performance
5. Add default data for rewards
6. Test migration on staging
7. Prepare rollback script

### S - Structure

**Migration File**: `DATABASE/MIGRATION_011_LOYALTY_MANAGEMENT.sql`

**Table Structures**:
```sql
-- loyalty_points
loyalty_point_id (PK)
tenant_id (FK)
customer_id (FK)
points_earned (INT)
points_redeemed (INT)
transaction_type (ENUM)
reference_id (BIGINT)
created_at (TIMESTAMP)

-- loyalty_rewards
reward_id (PK)
tenant_id (FK)
reward_name (VARCHAR)
reward_name_en (VARCHAR)
points_required (INT)
reward_type (ENUM)
status (ENUM)
created_at (TIMESTAMP)

-- customer_loyalty
customer_loyalty_id (PK)
tenant_id (FK)
customer_id (FK)
total_points (INT)
current_tier (ENUM)
tier_progress (INT)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### O - Operations

**Implementation Steps**:
1. Create migration file `DATABASE/MIGRATION_011_LOYALTY_MANAGEMENT.sql`
2. Add loyalty_points table with columns
3. Add loyalty_rewards table with columns
4. Add customer_loyalty table with columns
5. Add foreign key constraints
6. Add indexes for frequently queried columns
7. Insert default reward data
8. Add rollback script as comments
9. Test migration on staging database
10. Verify table structure
11. Verify data integrity
12. Document migration

### N - Norms

**Standards**:
- Follow database naming conventions (snake_case)
- Use utf8mb4 charset
- Use InnoDB engine
- Add audit fields (created_at, updated_at)
- Add tenant_id for multi-tenant tables
- Use BIGINT UNSIGNED for IDs
- Use appropriate data types
- Add indexes for foreign keys

### S - Safeguards

**Non-negotiable Rules**:
- MUST use InnoDB engine
- MUST use utf8mb4 charset
- MUST add tenant_id for multi-tenant tables
- MUST add audit fields
- MUST use foreign keys for relationships
- MUST add indexes for foreign keys
- MUST test before production
- MUST have rollback plan

## Implementation Prompt

```
Create a database migration for the loyalty management system following the template: prompting/templates/database-migration-template.md

Migration Details:
- File: DATABASE/MIGRATION_011_LOYALTY_MANAGEMENT.sql
- Description: Add tables for customer loyalty points, rewards, and tier tracking

Table 1: loyalty_points
Columns:
- loyalty_point_id (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY)
- tenant_id (BIGINT UNSIGNED NOT NULL)
- customer_id (BIGINT UNSIGNED NOT NULL)
- points_earned (INT DEFAULT 0)
- points_redeemed (INT DEFAULT 0)
- transaction_type (ENUM: EARNED, REDEEMED, ADJUSTED)
- reference_id (BIGINT UNSIGNED NULL)
- reference_type (VARCHAR(50) NULL)
- notes (TEXT NULL)
- created_at (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)

Foreign Keys:
- fk_loyalty_points_tenant -> tenants(tenant_id)
- fk_loyalty_points_customer -> customers(customer_id)

Indexes:
- idx_loyalty_points_tenant (tenant_id)
- idx_loyalty_points_customer (customer_id)
- idx_loyalty_points_created_at (created_at)

Table 2: loyalty_rewards
Columns:
- reward_id (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY)
- tenant_id (BIGINT UNSIGNED NOT NULL)
- reward_code (VARCHAR(50) UNIQUE NOT NULL)
- reward_name (VARCHAR(150) NOT NULL)
- reward_name_en (VARCHAR(150) NULL)
- reward_description (TEXT NULL)
- points_required (INT NOT NULL)
- reward_type (ENUM: DISCOUNT, FREE_ITEM, UPGRADE, EXPERIENCE)
- discount_percentage (DECIMAL(5,2) NULL)
- discount_amount (DECIMAL(10,2) NULL)
- status (ENUM: ACTIVE, INACTIVE, EXPIRED) DEFAULT 'ACTIVE'
- valid_from (DATE NULL)
- valid_until (DATE NULL)
- created_at (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

Foreign Keys:
- fk_loyalty_rewards_tenant -> tenants(tenant_id)

Indexes:
- idx_loyalty_rewards_tenant (tenant_id)
- idx_loyalty_rewards_status (status)
- idx_loyalty_rewards_code (reward_code)

Table 3: customer_loyalty
Columns:
- customer_loyalty_id (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY)
- tenant_id (BIGINT UNSIGNED NOT NULL)
- customer_id (BIGINT UNSIGNED NOT NULL)
- total_points (INT DEFAULT 0)
- current_tier (ENUM: BRONZE, SILVER, GOLD, PLATINUM) DEFAULT 'BRONZE')
- tier_progress (INT DEFAULT 0)
- tier_points_required (INT DEFAULT 100)
- points_earned_lifetime (INT DEFAULT 0)
- points_redeemed_lifetime (INT DEFAULT 0)
- last_tier_upgrade (DATE NULL)
- created_at (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

Foreign Keys:
- fk_customer_loyalty_tenant -> tenants(tenant_id)
- fk_customer_loyalty_customer -> customers(customer_id)

Indexes:
- idx_customer_loyalty_tenant (tenant_id)
- idx_customer_loyalty_customer (customer_id)
- idx_customer_loyalty_tier (current_tier)

Default Data:
Insert sample loyalty rewards for each tenant:
- Welcome reward (100 points)
- Birthday reward (200 points)
- Referral reward (150 points)

Use these references:
- Database schema: prompting/context/database-schema.md
- Migration template: prompting/templates/database-migration-template.md

After creation, verify:
- Migration file follows naming convention
- Table structures are correct
- Foreign keys are properly defined
- Indexes are appropriate
- Default data is valid
- Rollback script is included
```

## Testing Prompt

```
Test the loyalty management migration.

Test Steps:
1. Backup current database
2. Apply migration to staging database
3. Verify table creation:
   - loyalty_points table exists
   - loyalty_rewards table exists
   - customer_loyalty table exists
4. Verify table structure:
   - All columns present
   - Data types correct
   - Constraints correct
5. Verify foreign keys:
   - All foreign keys created
   - Referential integrity works
6. Verify indexes:
   - All indexes created
   - Indexes work correctly
7. Verify default data:
   - Sample rewards inserted
   - Data is valid
8. Test rollback:
   - Rollback migration
   - Verify tables dropped
9. Re-apply migration
10. Document results

Use database schema reference: prompting/context/database-schema.md
```

## Code Review Prompt

```
Review the loyalty management migration using the code review checklist: prompting/evaluations/code-review-checklist.md

Focus areas:
1. Structure:
   - Table naming conventions
   - Column naming conventions
   - Data types appropriate
   - Constraints correct

2. Relationships:
   - Foreign keys defined
   - Referential integrity
   - ON DELETE/UPDATE actions

3. Performance:
   - Indexes appropriate
   - Indexes on foreign keys
   - Composite indexes if needed

4. Data Integrity:
   - NOT NULL constraints
   - DEFAULT values
   - CHECK constraints
   - ENUM values

5. Migration Quality:
   - Follows template
   - Includes rollback
   - Tested on staging
   - Documented

Report any issues with severity levels.
```

---

**Version**: 1.0  
**Last Updated**: 2026-07-05
