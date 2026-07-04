-- Migration for WhatsApp Notification System, Tax Calculation, and Supply Chain

-- Tax Rates
CREATE TABLE IF NOT EXISTS tax_rates (
    tax_rate_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    ppn_rate DECIMAL(5,2) NOT NULL DEFAULT 11.00,
    pb1_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    effective_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tax_rate_id),
    KEY idx_tax_rates_tenant_id (tenant_id),
    KEY idx_tax_rates_branch_id (branch_id),
    KEY idx_tax_rates_effective (effective_date),
    KEY idx_tax_rates_active (is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WhatsApp Settings
CREATE TABLE IF NOT EXISTS whatsapp_settings (
    setting_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    provider VARCHAR(50) NOT NULL DEFAULT 'FONNTE',
    api_token VARCHAR(255),
    api_url VARCHAR(255),
    sender_number VARCHAR(50),
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_id),
    KEY idx_whatsapp_settings_tenant_id (tenant_id),
    KEY idx_whatsapp_settings_branch_id (branch_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WhatsApp Message Logs
CREATE TABLE IF NOT EXISTS whatsapp_message_logs (
    log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    recipient_number VARCHAR(50) NOT NULL,
    message_type ENUM('REPORT', 'PROMOTION', 'ORDER_UPDATE', 'RESERVATION', 'ALERT', 'CUSTOM') NOT NULL,
    message_content TEXT NOT NULL,
    status ENUM('PENDING', 'SENT', 'FAILED', 'DELIVERED') DEFAULT 'PENDING',
    provider_response TEXT,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    KEY idx_whatsapp_logs_tenant_id (tenant_id),
    KEY idx_whatsapp_logs_branch_id (branch_id),
    KEY idx_whatsapp_logs_status (status),
    KEY idx_whatsapp_logs_type (message_type),
    KEY idx_whatsapp_logs_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WhatsApp Report Schedules
CREATE TABLE IF NOT EXISTS whatsapp_report_schedules (
    schedule_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    report_type ENUM('DAILY_SALES', 'WEEKLY_SALES', 'MONTHLY_SALES', 'INVENTORY', 'PERFORMANCE') NOT NULL,
    recipient_numbers TEXT NOT NULL,
    schedule_time TIME NOT NULL,
    schedule_day VARCHAR(20),
    is_enabled BOOLEAN DEFAULT TRUE,
    last_sent_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (schedule_id),
    KEY idx_whatsapp_schedules_tenant_id (tenant_id),
    KEY idx_whatsapp_schedules_branch_id (branch_id),
    KEY idx_whatsapp_schedules_type (report_type),
    KEY idx_whatsapp_schedules_enabled (is_enabled),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Plans
CREATE TABLE IF NOT EXISTS purchase_plans (
    plan_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    planning_date DATE NOT NULL,
    plan_status ENUM('DRAFT', 'APPROVED', 'REJECTED', 'COMPLETED') DEFAULT 'DRAFT',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (plan_id),
    KEY idx_purchase_plans_tenant_id (tenant_id),
    KEY idx_purchase_plans_branch_id (branch_id),
    KEY idx_purchase_plans_status (plan_status),
    KEY idx_purchase_plans_date (planning_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_plan_items (
    plan_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    plan_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    suggested_quantity DECIMAL(10,2) NOT NULL,
    current_stock DECIMAL(10,2),
    priority ENUM('URGENT', 'NORMAL', 'LOW') DEFAULT 'NORMAL',
    notes TEXT,
    PRIMARY KEY (plan_item_id),
    KEY idx_purchase_plan_items_plan_id (plan_id),
    KEY idx_purchase_plan_items_product_id (product_id),
    FOREIGN KEY (plan_id) REFERENCES purchase_plans(plan_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quality Control
CREATE TABLE IF NOT EXISTS quality_checks (
    check_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    check_type ENUM('INCOMING', 'INTERNAL', 'OUTGOING') NOT NULL,
    product_id BIGINT UNSIGNED,
    batch_number VARCHAR(100),
    check_date DATE NOT NULL,
    checked_by BIGINT UNSIGNED,
    quality_score DECIMAL(5,2),
    check_status ENUM('PASSED', 'FAILED', 'PENDING') DEFAULT 'PENDING',
    issues TEXT,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (check_id),
    KEY idx_quality_checks_tenant_id (tenant_id),
    KEY idx_quality_checks_branch_id (branch_id),
    KEY idx_quality_checks_type (check_type),
    KEY idx_quality_checks_date (check_date),
    KEY idx_quality_checks_status (check_status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (checked_by) REFERENCES employees(employee_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
