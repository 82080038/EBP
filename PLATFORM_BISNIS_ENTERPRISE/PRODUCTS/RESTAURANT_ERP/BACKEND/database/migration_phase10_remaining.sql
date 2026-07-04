-- Migration for Remaining Features (Offline Sync, CRM Advanced, HR Advanced)

-- Offline Sync Queue
CREATE TABLE IF NOT EXISTS offline_sync_queue (
    sync_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    operation_type ENUM('CREATE', 'UPDATE', 'DELETE') NOT NULL,
    entity_type ENUM('ORDER', 'PAYMENT', 'INVENTORY', 'CUSTOMER', 'PRODUCT', 'TABLE') NOT NULL,
    entity_id BIGINT UNSIGNED,
    entity_data JSON,
    status ENUM('PENDING', 'SYNCED', 'FAILED', 'CONFLICT', 'DISCARDED') DEFAULT 'PENDING',
    error_message TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    synced_at TIMESTAMP NULL,
    PRIMARY KEY (sync_id),
    KEY idx_offline_sync_queue_tenant_id (tenant_id),
    KEY idx_offline_sync_queue_branch_id (branch_id),
    KEY idx_offline_sync_queue_status (status),
    KEY idx_offline_sync_queue_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Credit/Piutang Tracking
CREATE TABLE IF NOT EXISTS customer_credits (
    credit_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    customer_id BIGINT UNSIGNED NOT NULL,
    credit_amount DECIMAL(15,2) NOT NULL,
    credit_type ENUM('CREDIT', 'PIUTANG') NOT NULL,
    reference_type VARCHAR(50),
    reference_id BIGINT UNSIGNED,
    due_date DATE,
    status ENUM('ACTIVE', 'PAID', 'OVERDUE', 'CANCELLED') DEFAULT 'ACTIVE',
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (credit_id),
    KEY idx_customer_credits_tenant_id (tenant_id),
    KEY idx_customer_credits_branch_id (branch_id),
    KEY idx_customer_credits_customer_id (customer_id),
    KEY idx_customer_credits_status (status),
    KEY idx_customer_credits_due_date (due_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer-Specific Pricing
CREATE TABLE IF NOT EXISTS customer_pricing (
    pricing_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    customer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    special_price DECIMAL(10,2),
    discount_percentage DECIMAL(5,2),
    valid_from DATE,
    valid_until DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (pricing_id),
    UNIQUE KEY idx_customer_pricing_tenant_customer_product (tenant_id, customer_id, product_id),
    KEY idx_customer_pricing_tenant_id (tenant_id),
    KEY idx_customer_pricing_branch_id (branch_id),
    KEY idx_customer_pricing_customer_id (customer_id),
    KEY idx_customer_pricing_product_id (product_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bonus Management
CREATE TABLE IF NOT EXISTS bonuses (
    bonus_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    employee_id BIGINT UNSIGNED NOT NULL,
    bonus_type ENUM('PERFORMANCE', 'ATTENDANCE', 'SALES', 'MANUAL') NOT NULL,
    bonus_amount DECIMAL(15,2) NOT NULL,
    bonus_period_start DATE,
    bonus_period_end DATE,
    reason TEXT,
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    status ENUM('PENDING', 'APPROVED', 'PAID', 'REJECTED') DEFAULT 'PENDING',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (bonus_id),
    KEY idx_bonuses_tenant_id (tenant_id),
    KEY idx_bonuses_branch_id (branch_id),
    KEY idx_bonuses_employee_id (employee_id),
    KEY idx_bonuses_status (status),
    KEY idx_bonuses_period (bonus_period_start, bonus_period_end),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tip Distribution
CREATE TABLE IF NOT EXISTS tip_distributions (
    tip_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    order_id BIGINT UNSIGNED NOT NULL,
    total_tip_amount DECIMAL(15,2) NOT NULL,
    distribution_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (tip_id),
    KEY idx_tip_distributions_tenant_id (tenant_id),
    KEY idx_tip_distributions_branch_id (branch_id),
    KEY idx_tip_distributions_order_id (order_id),
    KEY idx_tip_distributions_date (distribution_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tip_recipients (
    recipient_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tip_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    tip_amount DECIMAL(15,2) NOT NULL,
    percentage DECIMAL(5,2),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (recipient_id),
    KEY idx_tip_recipients_tip_id (tip_id),
    KEY idx_tip_recipients_employee_id (employee_id),
    FOREIGN KEY (tip_id) REFERENCES tip_distributions(tip_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Commission Tracking
CREATE TABLE IF NOT EXISTS commissions (
    commission_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    employee_id BIGINT UNSIGNED NOT NULL,
    commission_type ENUM('SALES', 'UPSELL', 'SERVICE') NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL,
    base_amount DECIMAL(15,2) NOT NULL,
    commission_amount DECIMAL(15,2) NOT NULL,
    commission_period_start DATE,
    commission_period_end DATE,
    reference_type VARCHAR(50),
    reference_id BIGINT UNSIGNED,
    status ENUM('PENDING', 'APPROVED', 'PAID') DEFAULT 'PENDING',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (commission_id),
    KEY idx_commissions_tenant_id (tenant_id),
    KEY idx_commissions_branch_id (branch_id),
    KEY idx_commissions_employee_id (employee_id),
    KEY idx_commissions_status (status),
    KEY idx_commissions_period (commission_period_start, commission_period_end),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
