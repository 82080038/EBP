-- Migration for Phase 1: Advanced Order Management
-- This script adds new columns and tables to support advanced order features

-- Add new columns to orders table
ALTER TABLE orders 
ADD COLUMN order_type ENUM('DINE_IN', 'TAKE_AWAY', 'DELIVERY', 'PRE_ORDER') DEFAULT 'DINE_IN' AFTER status,
ADD COLUMN is_open_order BOOLEAN DEFAULT TRUE AFTER order_type,
ADD COLUMN is_priority BOOLEAN DEFAULT FALSE AFTER is_open_order,
ADD COLUMN is_held BOOLEAN DEFAULT FALSE AFTER is_priority,
ADD COLUMN hold_reason VARCHAR(255) AFTER is_held,
ADD COLUMN customer_name VARCHAR(255) AFTER hold_reason,
ADD COLUMN customer_phone VARCHAR(50) AFTER customer_name,
ADD COLUMN customer_address TEXT AFTER customer_phone,
ADD COLUMN delivery_fee DECIMAL(10,2) DEFAULT 0 AFTER customer_address,
ADD COLUMN delivery_time DATETIME AFTER delivery_fee,
ADD COLUMN service_charge DECIMAL(10,2) DEFAULT 0 AFTER discount;

-- Add indexes for new columns
ALTER TABLE orders 
ADD INDEX idx_orders_order_type (order_type),
ADD INDEX idx_orders_is_open_order (is_open_order),
ADD INDEX idx_orders_is_priority (is_priority);

-- Add product_variant_id to order_items
ALTER TABLE order_items 
ADD COLUMN product_variant_id BIGINT UNSIGNED AFTER product_id;

-- Create order_item_modifiers table
CREATE TABLE IF NOT EXISTS order_item_modifiers (
    order_item_modifier_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_item_id BIGINT UNSIGNED NOT NULL,
    modifier_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (order_item_modifier_id),
    KEY idx_order_item_modifiers_order_item_id (order_item_id),
    KEY idx_order_item_modifiers_modifier_id (modifier_id),
    FOREIGN KEY (order_item_id) REFERENCES order_items(order_item_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create split_bills table
CREATE TABLE IF NOT EXISTS split_bills (
    split_bill_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    split_type ENUM('PER_PERSON', 'PER_ITEM', 'CUSTOM') NOT NULL,
    total_splits INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (split_bill_id),
    KEY idx_split_bills_order_id (order_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create split_bill_items table
CREATE TABLE IF NOT EXISTS split_bill_items (
    split_bill_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    split_bill_id BIGINT UNSIGNED NOT NULL,
    order_item_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (split_bill_item_id),
    KEY idx_split_bill_items_split_bill_id (split_bill_id),
    KEY idx_split_bill_items_order_item_id (order_item_id),
    FOREIGN KEY (split_bill_id) REFERENCES split_bills(split_bill_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(order_item_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
    payment_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    split_bill_id BIGINT UNSIGNED,
    payment_method ENUM('CASH', 'QRIS', 'DEBIT', 'CREDIT', 'E_WALLET', 'TRANSFER', 'VOUCHER') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('PENDING', 'COMPLETED', 'FAILED', 'REFUNDED') DEFAULT 'COMPLETED',
    reference_number VARCHAR(100),
    payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    PRIMARY KEY (payment_id),
    KEY idx_payments_order_id (order_id),
    KEY idx_payments_split_bill_id (split_bill_id),
    KEY idx_payments_payment_method (payment_method),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (split_bill_id) REFERENCES split_bills(split_bill_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create product_variants table
CREATE TABLE IF NOT EXISTS product_variants (
    variant_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    variant_code VARCHAR(50) NOT NULL,
    variant_name VARCHAR(100) NOT NULL,
    price_adjustment DECIMAL(10,2) DEFAULT 0,
    is_default BOOLEAN DEFAULT FALSE,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (variant_id),
    UNIQUE KEY idx_product_variants_product_code (product_id, variant_code),
    KEY idx_product_variants_product_id (product_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create product_modifier_groups table
CREATE TABLE IF NOT EXISTS product_modifier_groups (
    modifier_group_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    group_code VARCHAR(50) NOT NULL,
    group_name VARCHAR(100) NOT NULL,
    is_required BOOLEAN DEFAULT FALSE,
    min_selections INT DEFAULT 0,
    max_selections INT DEFAULT 1,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (modifier_group_id),
    UNIQUE KEY idx_modifier_groups_tenant_code (tenant_id, group_code),
    KEY idx_modifier_groups_tenant_id (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create product_modifiers table
CREATE TABLE IF NOT EXISTS product_modifiers (
    modifier_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    modifier_group_id BIGINT UNSIGNED NOT NULL,
    modifier_code VARCHAR(50) NOT NULL,
    modifier_name VARCHAR(100) NOT NULL,
    price_adjustment DECIMAL(10,2) DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (modifier_id),
    UNIQUE KEY idx_modifiers_group_code (modifier_group_id, modifier_code),
    KEY idx_modifiers_modifier_group_id (modifier_group_id),
    FOREIGN KEY (modifier_group_id) REFERENCES product_modifier_groups(modifier_group_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create product_modifier_assignments table
CREATE TABLE IF NOT EXISTS product_modifier_assignments (
    assignment_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    modifier_group_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (assignment_id),
    UNIQUE KEY idx_product_modifier_assignments_product_group (product_id, modifier_group_id),
    KEY idx_product_modifier_assignments_product_id (product_id),
    KEY idx_product_modifier_assignments_modifier_group_id (modifier_group_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (modifier_group_id) REFERENCES product_modifier_groups(modifier_group_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
