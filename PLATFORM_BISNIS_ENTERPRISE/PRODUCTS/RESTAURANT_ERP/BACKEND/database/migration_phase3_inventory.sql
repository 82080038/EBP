-- Migration for Phase 3: Advanced Inventory Management
-- This script adds tables for batch/expiry tracking, stock adjustments, and supplier management

-- Add batch/expiry columns to inventory table
ALTER TABLE inventory 
ADD COLUMN batch_number VARCHAR(50) AFTER product_id,
ADD COLUMN expiry_date DATE AFTER batch_number,
ADD COLUMN manufacturing_date DATE AFTER expiry_date,
ADD COLUMN supplier_id BIGINT UNSIGNED AFTER manufacturing_date,
ADD INDEX idx_inventory_batch (batch_number),
ADD INDEX idx_inventory_expiry (expiry_date),
ADD INDEX idx_inventory_supplier (supplier_id);

-- Create suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    supplier_code VARCHAR(50) NOT NULL,
    supplier_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(50),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'Indonesia',
    tax_id VARCHAR(50),
    payment_terms VARCHAR(50),
    credit_limit DECIMAL(15,2) DEFAULT 0,
    lead_time_days INT DEFAULT 7,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (supplier_id),
    UNIQUE KEY idx_suppliers_tenant_code (tenant_id, supplier_code),
    KEY idx_suppliers_tenant_id (tenant_id),
    KEY idx_suppliers_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create stock adjustments table
CREATE TABLE IF NOT EXISTS stock_adjustments (
    adjustment_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    adjustment_number VARCHAR(50) NOT NULL,
    adjustment_type ENUM('IN', 'OUT', 'TRANSFER', 'CORRECTION', 'DAMAGE', 'EXPIRED') NOT NULL,
    adjustment_date DATE NOT NULL,
    reason TEXT,
    reference_number VARCHAR(100),
    status ENUM('DRAFT', 'PENDING', 'APPROVED', 'REJECTED') DEFAULT 'DRAFT',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (adjustment_id),
    UNIQUE KEY idx_stock_adjustments_tenant_number (tenant_id, adjustment_number),
    KEY idx_stock_adjustments_tenant_id (tenant_id),
    KEY idx_stock_adjustments_branch_id (branch_id),
    KEY idx_stock_adjustments_type (adjustment_type),
    KEY idx_stock_adjustments_date (adjustment_date),
    KEY idx_stock_adjustments_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create stock adjustment items table
CREATE TABLE IF NOT EXISTS stock_adjustment_items (
    adjustment_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    adjustment_id BIGINT UNSIGNED NOT NULL,
    inventory_id BIGINT UNSIGNED NOT NULL,
    batch_number VARCHAR(50),
    quantity DECIMAL(10,3) NOT NULL,
    unit_cost DECIMAL(10,2),
    total_cost DECIMAL(10,2),
    expiry_date DATE,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (adjustment_item_id),
    KEY idx_stock_adjustment_items_adjustment_id (adjustment_id),
    KEY idx_stock_adjustment_items_inventory_id (inventory_id),
    FOREIGN KEY (adjustment_id) REFERENCES stock_adjustments(adjustment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for supplier_id in inventory
ALTER TABLE inventory 
ADD CONSTRAINT fk_inventory_supplier 
FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL ON UPDATE CASCADE;
