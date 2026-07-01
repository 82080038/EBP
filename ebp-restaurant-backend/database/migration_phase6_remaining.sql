-- Migration for Remaining High-Priority Features
-- Combo/Package Engine, Advanced Payment, Advanced KDS, Offline Mode

-- Combo/Package Engine Tables
CREATE TABLE IF NOT EXISTS combos (
    combo_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    combo_code VARCHAR(50) NOT NULL,
    combo_name VARCHAR(150) NOT NULL,
    combo_type ENUM('PICK_N', 'FLEXIBLE', 'PACKAGE', 'BUNDLE') NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    valid_from DATE,
    valid_until DATE,
    max_redemptions INT,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (combo_id),
    UNIQUE KEY idx_combos_tenant_code (tenant_id, combo_code),
    KEY idx_combos_tenant_id (tenant_id),
    KEY idx_combos_type (combo_type),
    KEY idx_combos_status (is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS combo_groups (
    combo_group_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    combo_id BIGINT UNSIGNED NOT NULL,
    group_name VARCHAR(100) NOT NULL,
    min_selections INT DEFAULT 1,
    max_selections INT DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (combo_group_id),
    KEY idx_combo_groups_combo_id (combo_id),
    FOREIGN KEY (combo_id) REFERENCES combos(combo_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS combo_items (
    combo_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    combo_group_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    price_adjustment DECIMAL(10,2) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (combo_item_id),
    KEY idx_combo_items_combo_group_id (combo_group_id),
    KEY idx_combo_items_product_id (product_id),
    FOREIGN KEY (combo_group_id) REFERENCES combo_groups(combo_group_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Advanced Payment Tables
CREATE TABLE IF NOT EXISTS payment_methods (
    payment_method_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    method_code VARCHAR(50) NOT NULL,
    method_name VARCHAR(100) NOT NULL,
    method_type ENUM('CASH', 'QRIS', 'DEBIT', 'CREDIT', 'E_WALLET', 'TRANSFER', 'VOUCHER', 'CREDIT_NOTE') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    requires_approval BOOLEAN DEFAULT FALSE,
    has_change BOOLEAN DEFAULT FALSE,
    rounding_rule ENUM('NONE', 'UP', 'DOWN', 'NEAREST') DEFAULT 'NONE',
    rounding_amount DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (payment_method_id),
    UNIQUE KEY idx_payment_methods_tenant_code (tenant_id, method_code),
    KEY idx_payment_methods_tenant_id (tenant_id),
    KEY idx_payment_methods_type (method_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS credit_notes (
    credit_note_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED,
    credit_note_number VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    remaining_amount DECIMAL(10,2) NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE,
    status ENUM('ACTIVE', 'PARTIAL', 'PAID', 'EXPIRED', 'CANCELLED') DEFAULT 'ACTIVE',
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (credit_note_id),
    UNIQUE KEY idx_credit_notes_tenant_number (tenant_id, credit_note_number),
    KEY idx_credit_notes_tenant_id (tenant_id),
    KEY idx_credit_notes_customer_id (customer_id),
    KEY idx_credit_notes_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS credit_note_installments (
    installment_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    credit_note_id BIGINT UNSIGNED NOT NULL,
    installment_number INT NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0,
    status ENUM('PENDING', 'PAID', 'OVERDUE') DEFAULT 'PENDING',
    paid_date DATE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (installment_id),
    KEY idx_credit_note_installments_credit_note_id (credit_note_id),
    KEY idx_credit_note_installments_status (status),
    FOREIGN KEY (credit_note_id) REFERENCES credit_notes(credit_note_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vouchers (
    voucher_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    voucher_code VARCHAR(50) NOT NULL,
    voucher_name VARCHAR(150) NOT NULL,
    voucher_type ENUM('PERCENTAGE', 'FIXED_AMOUNT', 'FREE_ITEM') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    max_discount DECIMAL(10,2),
    min_purchase_amount DECIMAL(10,2) DEFAULT 0,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    usage_limit INT,
    usage_count INT DEFAULT 0,
    customer_id BIGINT UNSIGNED,
    status ENUM('ACTIVE', 'INACTIVE', 'EXPIRED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (voucher_id),
    UNIQUE KEY idx_vouchers_tenant_code (tenant_id, voucher_code),
    KEY idx_vouchers_tenant_id (tenant_id),
    KEY idx_vouchers_status (status),
    KEY idx_vouchers_customer_id (customer_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cash_drawers (
    drawer_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    drawer_code VARCHAR(50) NOT NULL,
    drawer_name VARCHAR(100) NOT NULL,
    assigned_user_id BIGINT UNSIGNED,
    opening_balance DECIMAL(10,2) DEFAULT 0,
    current_balance DECIMAL(10,2) DEFAULT 0,
    status ENUM('OPEN', 'CLOSED') DEFAULT 'CLOSED',
    opened_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (drawer_id),
    UNIQUE KEY idx_cash_drawers_branch_code (branch_id, drawer_code),
    KEY idx_cash_drawers_tenant_id (tenant_id),
    KEY idx_cash_drawers_branch_id (branch_id),
    KEY idx_cash_drawers_assigned_user (assigned_user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (assigned_user_id) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cash_drawer_transactions (
    transaction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    drawer_id BIGINT UNSIGNED NOT NULL,
    transaction_type ENUM('OPENING', 'SALE', 'REFUND', 'PAYOUT', 'PAYIN', 'CLOSING') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (transaction_id),
    KEY idx_cash_drawer_transactions_drawer_id (drawer_id),
    KEY idx_cash_drawer_transactions_type (transaction_type),
    FOREIGN KEY (drawer_id) REFERENCES cash_drawers(drawer_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Advanced KDS Tables
CREATE TABLE IF NOT EXISTS kitchen_stations (
    station_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    station_code VARCHAR(50) NOT NULL,
    station_name VARCHAR(100) NOT NULL,
    station_type ENUM('HOT_KITCHEN', 'COLD_KITCHEN', 'BAR', 'PREP', 'GRILL', 'FRYER', 'DESSERT') NOT NULL,
    sla_minutes INT DEFAULT 15,
    capacity INT DEFAULT 10,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (station_id),
    UNIQUE KEY idx_kitchen_stations_branch_code (branch_id, station_code),
    KEY idx_kitchen_stations_tenant_id (tenant_id),
    KEY idx_kitchen_stations_branch_id (branch_id),
    KEY idx_kitchen_stations_type (station_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add station_id to kitchen_orders
ALTER TABLE kitchen_orders 
ADD COLUMN station_id BIGINT UNSIGNED AFTER branch_id,
ADD COLUMN sla_deadline TIMESTAMP NULL AFTER priority,
ADD COLUMN estimated_cooking_time INT DEFAULT 0 AFTER sla_deadline,
ADD COLUMN actual_cooking_time INT DEFAULT 0 AFTER estimated_cooking_time,
ADD INDEX idx_kitchen_orders_station_id (station_id),
ADD INDEX idx_kitchen_orders_sla_deadline (sla_deadline),
ADD CONSTRAINT fk_kitchen_orders_station 
FOREIGN KEY (station_id) REFERENCES kitchen_stations(station_id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Offline Sync Tables
CREATE TABLE IF NOT EXISTS sync_queue (
    sync_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT UNSIGNED,
    action ENUM('CREATE', 'UPDATE', 'DELETE') NOT NULL,
    payload JSON,
    status ENUM('PENDING', 'SYNCED', 'FAILED') DEFAULT 'PENDING',
    retry_count INT DEFAULT 0,
    error_message TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    synced_at TIMESTAMP NULL,
    PRIMARY KEY (sync_id),
    KEY idx_sync_queue_tenant_id (tenant_id),
    KEY idx_sync_queue_branch_id (branch_id),
    KEY idx_sync_queue_status (status),
    KEY idx_sync_queue_entity (entity_type, entity_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sync_conflicts (
    conflict_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT UNSIGNED,
    local_data JSON,
    remote_data JSON,
    conflict_type ENUM('VERSION', 'DATA', 'DELETE') NOT NULL,
    status ENUM('PENDING', 'RESOLVED', 'IGNORED') DEFAULT 'PENDING',
    resolved_by BIGINT UNSIGNED,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (conflict_id),
    KEY idx_sync_conflicts_tenant_id (tenant_id),
    KEY idx_sync_conflicts_branch_id (branch_id),
    KEY idx_sync_conflicts_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
