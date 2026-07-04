/*
========================================================
MIGRATION PHASE 20: ONBOARDING ENHANCEMENT
========================================================
This migration adds fields for comprehensive onboarding
based on F&B industry best practices
*/

USE ebp_restaurant_erp;

-- Add fields to companies table for tax and currency configuration
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'companies' AND column_name = 'tax_id';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE companies ADD COLUMN tax_id VARCHAR(50) AFTER email', 'SELECT "Column tax_id already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'companies' AND column_name = 'currency_code';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE companies ADD COLUMN currency_code VARCHAR(3) DEFAULT "IDR" AFTER logo_url', 'SELECT "Column currency_code already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'companies' AND column_name = 'time_zone';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE companies ADD COLUMN time_zone VARCHAR(50) DEFAULT "Asia/Jakarta" AFTER currency_code', 'SELECT "Column time_zone already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add fields to branches table for operating hours and delivery configuration
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'branches' AND column_name = 'operating_hours';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE branches ADD COLUMN operating_hours JSON AFTER image_url', 'SELECT "Column operating_hours already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'branches' AND column_name = 'tax_rate';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE branches ADD COLUMN tax_rate DECIMAL(5,2) DEFAULT 0 AFTER operating_hours', 'SELECT "Column tax_rate already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'branches' AND column_name = 'service_charge';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE branches ADD COLUMN service_charge DECIMAL(5,2) DEFAULT 0 AFTER tax_rate', 'SELECT "Column service_charge already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'branches' AND column_name = 'tip_config';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE branches ADD COLUMN tip_config ENUM("MANDATORY", "OPTIONAL", "DISABLED") DEFAULT "OPTIONAL" AFTER service_charge', 'SELECT "Column tip_config already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'branches' AND column_name = 'delivery_fee';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE branches ADD COLUMN delivery_fee DECIMAL(10,2) DEFAULT 0 AFTER tip_config', 'SELECT "Column delivery_fee already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'branches' AND column_name = 'minimum_order_amount';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE branches ADD COLUMN minimum_order_amount DECIMAL(10,2) DEFAULT 0 AFTER delivery_fee', 'SELECT "Column minimum_order_amount already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns
WHERE table_schema = 'ebp_restaurant_erp' AND table_name = 'branches' AND column_name = 'free_delivery_threshold';
SET @sql = IF(@col_exists = 0, 'ALTER TABLE branches ADD COLUMN free_delivery_threshold DECIMAL(10,2) DEFAULT 0 AFTER minimum_order_amount', 'SELECT "Column free_delivery_threshold already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
