-- Migration for Ultimate Remaining Features

-- Dynamic Pricing Rules
CREATE TABLE IF NOT EXISTS dynamic_pricing_rules (
    rule_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,
    rule_name VARCHAR(100) NOT NULL,
    rule_type ENUM('TIME_BASED', 'DEMAND_BASED', 'COMPETITION_BASED', 'INVENTORY_BASED') NOT NULL,
    min_price DECIMAL(15,2),
    max_price DECIMAL(15,2),
    price_adjustment_percent DECIMAL(5,2),
    conditions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (rule_id),
    KEY idx_dynamic_pricing_tenant_id (tenant_id),
    KEY idx_dynamic_pricing_branch_id (branch_id),
    KEY idx_dynamic_pricing_product_id (product_id),
    KEY idx_dynamic_pricing_active (is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waste Tracking
CREATE TABLE IF NOT EXISTS waste_tracking (
    waste_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,
    waste_date DATE NOT NULL,
    waste_quantity DECIMAL(10,2) NOT NULL,
    waste_unit VARCHAR(20),
    waste_reason ENUM('EXPIRED', 'SPOILED', 'DAMAGED', 'PREPARATION_ERROR', 'OVERSTOCK', 'OTHER') NOT NULL,
    estimated_cost DECIMAL(15,2),
    recorded_by BIGINT UNSIGNED,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (waste_id),
    KEY idx_waste_tracking_tenant_id (tenant_id),
    KEY idx_waste_tracking_branch_id (branch_id),
    KEY idx_waste_tracking_date (waste_date),
    KEY idx_waste_tracking_reason (waste_reason),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Work Orders
CREATE TABLE IF NOT EXISTS work_orders (
    work_order_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    asset_id BIGINT UNSIGNED,
    work_order_number VARCHAR(50) NOT NULL,
    work_order_type ENUM('PREVENTIVE', 'CORRECTIVE', 'EMERGENCY', 'IMPROVEMENT') NOT NULL,
    priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('PENDING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
    assigned_to BIGINT UNSIGNED,
    due_date DATE,
    completed_date DATE,
    estimated_hours DECIMAL(5,2),
    actual_hours DECIMAL(5,2),
    cost DECIMAL(15,2),
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (work_order_id),
    KEY idx_work_orders_tenant_id (tenant_id),
    KEY idx_work_orders_branch_id (branch_id),
    KEY idx_work_orders_asset_id (asset_id),
    KEY idx_work_orders_status (status),
    KEY idx_work_orders_priority (priority),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES employees(employee_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Equipment History
CREATE TABLE IF NOT EXISTS equipment_history (
    history_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    asset_id BIGINT UNSIGNED,
    event_type ENUM('INSTALLATION', 'MAINTENANCE', 'REPAIR', 'REPLACEMENT', 'INSPECTION', 'FAILURE') NOT NULL,
    event_date DATE NOT NULL,
    description TEXT,
    performed_by BIGINT UNSIGNED,
    cost DECIMAL(15,2),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (history_id),
    KEY idx_equipment_history_tenant_id (tenant_id),
    KEY idx_equipment_history_branch_id (branch_id),
    KEY idx_equipment_history_asset_id (asset_id),
    KEY idx_equipment_history_date (event_date),
    KEY idx_equipment_history_type (event_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES employees(employee_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
