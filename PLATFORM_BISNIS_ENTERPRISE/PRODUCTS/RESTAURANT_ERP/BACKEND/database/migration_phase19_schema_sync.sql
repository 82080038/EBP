/*
========================================================
MIGRATION PHASE 19: SCHEMA SYNC WITH DATABASE
========================================================
This migration adds missing fields to sync the actual database
with the schema.sql file
*/

USE ebp_restaurant_erp;

-- Add missing fields to roles table (created_at and updated_at already exist)
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'roles' AND column_name = 'is_system';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE roles ADD COLUMN is_system BOOLEAN DEFAULT FALSE AFTER description', 'SELECT "Column is_system already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'roles' AND column_name = 'status';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE roles ADD COLUMN status ENUM("ACTIVE", "INACTIVE") DEFAULT "ACTIVE" AFTER is_system', 'SELECT "Column status already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'roles' AND column_name = 'deleted_at';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE roles ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at', 'SELECT "Column deleted_at already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add missing fields to role_permissions table
-- Keep composite PK as is (schema.sql has composite PK too), just add missing columns
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'role_permissions' AND column_name = 'granted_at';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE role_permissions ADD COLUMN granted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER permission_id', 'SELECT "Column granted_at already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add missing fields to user_roles table
-- Keep composite PK as is, just rename created_at to assigned_at
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'user_roles' AND column_name = 'assigned_at';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE user_roles CHANGE COLUMN created_at assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'SELECT "Column assigned_at already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
