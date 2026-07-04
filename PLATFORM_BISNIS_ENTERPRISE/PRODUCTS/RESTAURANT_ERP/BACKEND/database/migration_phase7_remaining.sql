-- Migration for Remaining High-Priority Features
-- Intermediate products, Dynamic cost tracking, Stock opname, Purchase orders, Goods receipt, CRM advanced, Advanced reports, Offline sync logic

-- Add cost tracking columns to inventory
ALTER TABLE inventory 
ADD COLUMN average_cost DECIMAL(10,2) DEFAULT 0 AFTER quantity,
ADD COLUMN fifo_cost DECIMAL(10,2) DEFAULT 0 AFTER average_cost,
ADD COLUMN last_purchase_date DATE AFTER fifo_cost,
ADD COLUMN cost_method ENUM('AVERAGE', 'FIFO', 'LIFO') DEFAULT 'AVERAGE' AFTER last_purchase_date;

-- Create intermediate_products table
CREATE TABLE IF NOT EXISTS intermediate_products (
    intermediate_product_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    intermediate_code VARCHAR(50) NOT NULL,
    intermediate_name VARCHAR(150) NOT NULL,
    description TEXT,
    production_cost DECIMAL(10,2) DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (intermediate_product_id),
    UNIQUE KEY idx_intermediate_products_tenant_code (tenant_id, intermediate_code),
    KEY idx_intermediate_products_tenant_id (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create intermediate_product_ingredients table
CREATE TABLE IF NOT EXISTS intermediate_product_ingredients (
    ingredient_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    intermediate_product_id BIGINT UNSIGNED NOT NULL,
    inventory_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(10,3) NOT NULL,
    unit VARCHAR(20),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ingredient_id),
    KEY idx_intermediate_product_ingredients_intermediate_id (intermediate_product_id),
    KEY idx_intermediate_product_ingredients_inventory_id (inventory_id),
    FOREIGN KEY (intermediate_product_id) REFERENCES intermediate_products(intermediate_product_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create stock_opname table
CREATE TABLE IF NOT EXISTS stock_opname (
    opname_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    opname_number VARCHAR(50) NOT NULL,
    opname_date DATE NOT NULL,
    status ENUM('DRAFT', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'DRAFT',
    notes TEXT,
    created_by BIGINT UNSIGNED,
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (opname_id),
    UNIQUE KEY idx_stock_opname_tenant_number (tenant_id, opname_number),
    KEY idx_stock_opname_tenant_id (tenant_id),
    KEY idx_stock_opname_branch_id (branch_id),
    KEY idx_stock_opname_date (opname_date),
    KEY idx_stock_opname_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create stock_opname_items table
CREATE TABLE IF NOT EXISTS stock_opname_items (
    opname_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    opname_id BIGINT UNSIGNED NOT NULL,
    inventory_id BIGINT UNSIGNED NOT NULL,
    system_quantity DECIMAL(10,3) NOT NULL,
    physical_quantity DECIMAL(10,3) NOT NULL,
    difference DECIMAL(10,3) NOT NULL,
    unit_cost DECIMAL(10,2),
    difference_value DECIMAL(10,2),
    reason TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (opname_item_id),
    KEY idx_stock_opname_items_opname_id (opname_id),
    KEY idx_stock_opname_items_inventory_id (inventory_id),
    FOREIGN KEY (opname_id) REFERENCES stock_opname(opname_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create purchase_orders table
CREATE TABLE IF NOT EXISTS purchase_orders (
    purchase_order_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    po_number VARCHAR(50) NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    order_date DATE NOT NULL,
    expected_delivery_date DATE,
    status ENUM('DRAFT', 'PENDING', 'APPROVED', 'ORDERED', 'PARTIAL_RECEIVED', 'RECEIVED', 'CANCELLED') DEFAULT 'DRAFT',
    subtotal DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    discount DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    created_by BIGINT UNSIGNED,
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (purchase_order_id),
    UNIQUE KEY idx_purchase_orders_tenant_number (tenant_id, po_number),
    KEY idx_purchase_orders_tenant_id (tenant_id),
    KEY idx_purchase_orders_branch_id (branch_id),
    KEY idx_purchase_orders_supplier_id (supplier_id),
    KEY idx_purchase_orders_status (status),
    KEY idx_purchase_orders_date (order_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create purchase_order_items table
CREATE TABLE IF NOT EXISTS purchase_order_items (
    po_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    purchase_order_id BIGINT UNSIGNED NOT NULL,
    inventory_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(10,3) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    tax_percentage DECIMAL(5,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,
    received_quantity DECIMAL(10,3) DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (po_item_id),
    KEY idx_purchase_order_items_po_id (purchase_order_id),
    KEY idx_purchase_order_items_inventory_id (inventory_id),
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(purchase_order_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create goods_receipt table
CREATE TABLE IF NOT EXISTS goods_receipt (
    receipt_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    receipt_number VARCHAR(50) NOT NULL,
    purchase_order_id BIGINT UNSIGNED,
    supplier_id BIGINT UNSIGNED NOT NULL,
    receipt_date DATE NOT NULL,
    status ENUM('DRAFT', 'COMPLETED', 'CANCELLED') DEFAULT 'DRAFT',
    notes TEXT,
    received_by BIGINT UNSIGNED,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (receipt_id),
    UNIQUE KEY idx_goods_receipt_tenant_number (tenant_id, receipt_number),
    KEY idx_goods_receipt_tenant_id (tenant_id),
    KEY idx_goods_receipt_branch_id (branch_id),
    KEY idx_goods_receipt_po_id (purchase_order_id),
    KEY idx_goods_receipt_supplier_id (supplier_id),
    KEY idx_goods_receipt_date (receipt_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(purchase_order_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create goods_receipt_items table
CREATE TABLE IF NOT EXISTS goods_receipt_items (
    receipt_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    receipt_id BIGINT UNSIGNED NOT NULL,
    inventory_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(10,3) NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    batch_number VARCHAR(50),
    expiry_date DATE,
    manufacturing_date DATE,
    total_cost DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (receipt_item_id),
    KEY idx_goods_receipt_items_receipt_id (receipt_id),
    KEY idx_goods_receipt_items_inventory_id (inventory_id),
    FOREIGN KEY (receipt_id) REFERENCES goods_receipt(receipt_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add order history tracking to customers
ALTER TABLE customers 
ADD COLUMN first_order_date DATE AFTER last_order_date,
ADD COLUMN average_order_value DECIMAL(10,2) DEFAULT 0 AFTER first_order_date,
ADD COLUMN customer_lifetime_value DECIMAL(10,2) DEFAULT 0 AFTER average_order_value,
ADD COLUMN favorite_products JSON AFTER customer_lifetime_value,
ADD COLUMN visit_frequency ENUM('DAILY', 'WEEKLY', 'MONTHLY', 'RARELY') DEFAULT 'RARELY' AFTER favorite_products;
