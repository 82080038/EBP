-- Phase 1 Schema Alignment Migration
-- Aligns database schema with Phase 1 module requirements

-- Add missing columns to categories table (for compatibility)
ALTER TABLE categories ADD COLUMN IF NOT EXISTS name VARCHAR(255);

-- Add missing columns to products table (for compatibility)
ALTER TABLE products ADD COLUMN IF NOT EXISTS name VARCHAR(255);

-- Add missing columns to orders table
ALTER TABLE orders ADD COLUMN IF NOT EXISTS customer_id INT;

-- Add missing column to shifts table
ALTER TABLE shifts ADD COLUMN IF NOT EXISTS break_duration INT DEFAULT 0;

-- Create inventory_items table if it doesn't exist
CREATE TABLE IF NOT EXISTS inventory_items (
    inventory_item_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT,
    item_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    unit VARCHAR(50) DEFAULT 'unit',
    cost_per_unit DECIMAL(15,2) DEFAULT 0.00,
    current_stock DECIMAL(10,3) DEFAULT 0.00,
    min_stock_level DECIMAL(10,3) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
