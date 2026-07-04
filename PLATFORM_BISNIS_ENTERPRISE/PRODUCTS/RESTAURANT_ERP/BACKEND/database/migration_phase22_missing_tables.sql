/*
========================================================
MIGRATION PHASE 22: MISSING TABLES FROM SCHEMA
========================================================
This migration creates missing tables from schema.sql
*/

USE ebp_restaurant_erp;

-- Product modifier groups
CREATE TABLE IF NOT EXISTS product_modifier_groups (
    modifier_group_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    group_name VARCHAR(100) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    min_select INT DEFAULT 0,
    max_select INT DEFAULT 1,
    is_required BOOLEAN DEFAULT FALSE,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (modifier_group_id),
    KEY idx_modifier_group_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Product modifiers
CREATE TABLE IF NOT EXISTS product_modifiers (
    modifier_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    modifier_group_id BIGINT(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (modifier_id),
    KEY idx_modifier_tenant (tenant_id),
    KEY idx_modifier_group (modifier_group_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (modifier_group_id) REFERENCES product_modifier_groups(modifier_group_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Product modifier assignments
CREATE TABLE IF NOT EXISTS product_modifier_assignments (
    assignment_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    product_id BIGINT(20) NOT NULL,
    modifier_group_id BIGINT(20) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (assignment_id),
    KEY idx_assignment_tenant (tenant_id),
    KEY idx_assignment_product (product_id),
    KEY idx_assignment_group (modifier_group_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES menu_products(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (modifier_group_id) REFERENCES product_modifier_groups(modifier_group_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Recipe ingredients
CREATE TABLE IF NOT EXISTS recipe_ingredients (
    recipe_ingredient_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    recipe_id BIGINT(20) NOT NULL,
    ingredient_id BIGINT(20) NOT NULL,
    quantity DECIMAL(10,3) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (recipe_ingredient_id),
    KEY idx_recipe_ingredient_tenant (tenant_id),
    KEY idx_recipe_ingredient_recipe (recipe_id),
    KEY idx_recipe_ingredient_ingredient (ingredient_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Order item modifiers
CREATE TABLE IF NOT EXISTS order_item_modifiers (
    order_item_modifier_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    order_item_id BIGINT(20) NOT NULL,
    modifier_id BIGINT(20) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (order_item_modifier_id),
    KEY idx_order_item_modifier_tenant (tenant_id),
    KEY idx_order_item_modifier_item (order_item_id),
    KEY idx_order_item_modifier_modifier (modifier_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_details(detail_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (modifier_id) REFERENCES product_modifiers(modifier_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Split bills
CREATE TABLE IF NOT EXISTS split_bills (
    split_bill_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    order_id BIGINT(20) NOT NULL,
    split_type ENUM('BY_ITEM', 'BY_AMOUNT', 'BY_PERCENTAGE', 'EQUAL') NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('ACTIVE', 'CANCELLED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (split_bill_id),
    KEY idx_split_bill_tenant (tenant_id),
    KEY idx_split_bill_order (order_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Split bill items
CREATE TABLE IF NOT EXISTS split_bill_items (
    split_bill_item_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    split_bill_id BIGINT(20) NOT NULL,
    order_item_id BIGINT(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (split_bill_item_id),
    KEY idx_split_bill_item_tenant (tenant_id),
    KEY idx_split_bill_item_split (split_bill_id),
    KEY idx_split_bill_item_item (order_item_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (split_bill_id) REFERENCES split_bills(split_bill_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_details(detail_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Kitchen orders
CREATE TABLE IF NOT EXISTS kitchen_orders (
    kitchen_order_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    branch_id BIGINT(20) NOT NULL,
    order_id BIGINT(20) NOT NULL,
    status ENUM('PENDING', 'PREPARING', 'READY', 'SERVED', 'CANCELLED') DEFAULT 'PENDING',
    priority ENUM('LOW', 'NORMAL', 'HIGH', 'URGENT') DEFAULT 'NORMAL',
    estimated_time INT DEFAULT NULL,
    actual_time INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (kitchen_order_id),
    KEY idx_kitchen_order_tenant (tenant_id),
    KEY idx_kitchen_order_branch (branch_id),
    KEY idx_kitchen_order_order (order_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Kitchen order items
CREATE TABLE IF NOT EXISTS kitchen_order_items (
    kitchen_order_item_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    kitchen_order_id BIGINT(20) NOT NULL,
    order_item_id BIGINT(20) NOT NULL,
    product_id BIGINT(20) NOT NULL,
    quantity INT NOT NULL,
    notes TEXT,
    status ENUM('PENDING', 'PREPARING', 'READY', 'SERVED', 'CANCELLED') DEFAULT 'PENDING',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (kitchen_order_item_id),
    KEY idx_kitchen_order_item_tenant (tenant_id),
    KEY idx_kitchen_order_item_kitchen (kitchen_order_id),
    KEY idx_kitchen_order_item_item (order_item_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (kitchen_order_id) REFERENCES kitchen_orders(kitchen_order_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_details(detail_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES menu_products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Inventory
CREATE TABLE IF NOT EXISTS inventory (
    inventory_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    branch_id BIGINT(20) NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    sku VARCHAR(50),
    category VARCHAR(100),
    unit VARCHAR(50),
    current_quantity DECIMAL(10,3) DEFAULT 0,
    minimum_quantity DECIMAL(10,3) DEFAULT 0,
    cost_per_unit DECIMAL(10,2) DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (inventory_id),
    KEY idx_inventory_tenant (tenant_id),
    KEY idx_inventory_branch (branch_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Stock transactions
CREATE TABLE IF NOT EXISTS stock_transactions (
    transaction_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    branch_id BIGINT(20) NOT NULL,
    inventory_id BIGINT(20) NOT NULL,
    transaction_type ENUM('IN', 'OUT', 'ADJUSTMENT', 'TRANSFER') NOT NULL,
    quantity DECIMAL(10,3) NOT NULL,
    unit_cost DECIMAL(10,2),
    total_cost DECIMAL(10,2),
    reference_type VARCHAR(50),
    reference_id BIGINT(20),
    notes TEXT,
    created_by BIGINT(20),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (transaction_id),
    KEY idx_stock_transaction_tenant (tenant_id),
    KEY idx_stock_transaction_branch (branch_id),
    KEY idx_stock_transaction_inventory (inventory_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Chart of accounts
CREATE TABLE IF NOT EXISTS chart_of_accounts (
    account_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(150) NOT NULL,
    account_type ENUM('ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE') NOT NULL,
    parent_account_id BIGINT(20),
    balance DECIMAL(15,2) DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (account_id),
    UNIQUE KEY idx_account_code_tenant (account_code, tenant_id),
    KEY idx_account_tenant (tenant_id),
    KEY idx_account_parent (parent_account_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (parent_account_id) REFERENCES chart_of_accounts(account_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Journal entries
CREATE TABLE IF NOT EXISTS journal_entries (
    journal_entry_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    entry_number VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL,
    reference_type VARCHAR(50),
    reference_id BIGINT(20),
    description TEXT,
    status ENUM('DRAFT', 'POSTED', 'CANCELLED') DEFAULT 'DRAFT',
    total_debit DECIMAL(15,2) DEFAULT 0,
    total_credit DECIMAL(15,2) DEFAULT 0,
    created_by BIGINT(20),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (journal_entry_id),
    UNIQUE KEY idx_entry_number_tenant (entry_number, tenant_id),
    KEY idx_journal_entry_tenant (tenant_id),
    KEY idx_journal_entry_date (entry_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Journal lines
CREATE TABLE IF NOT EXISTS journal_lines (
    journal_line_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    journal_entry_id BIGINT(20) NOT NULL,
    account_id BIGINT(20) NOT NULL,
    debit_amount DECIMAL(15,2) DEFAULT 0,
    credit_amount DECIMAL(15,2) DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (journal_line_id),
    KEY idx_journal_line_tenant (tenant_id),
    KEY idx_journal_line_entry (journal_entry_id),
    KEY idx_journal_line_account (account_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(journal_entry_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (account_id) REFERENCES chart_of_accounts(account_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Audit logs
CREATE TABLE IF NOT EXISTS audit_logs (
    audit_log_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT(20) NOT NULL,
    user_id BIGINT(20),
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT(20),
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (audit_log_id),
    KEY idx_audit_log_tenant (tenant_id),
    KEY idx_audit_log_user (user_id),
    KEY idx_audit_log_entity (entity_type, entity_id),
    KEY idx_audit_log_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
