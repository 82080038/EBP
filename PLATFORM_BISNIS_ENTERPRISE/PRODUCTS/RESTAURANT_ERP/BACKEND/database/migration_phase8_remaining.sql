-- Migration for All Remaining Features
-- AI & BI, Delivery, HR & Payroll, Accounting, Supply Chain, Maintenance, Quality & Safety, Advanced Features, Sustainability

-- AI & Business Intelligence Tables
CREATE TABLE IF NOT EXISTS ai_predictions (
    prediction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    prediction_type ENUM('SALES_FORECAST', 'INVENTORY_PREDICTION', 'KITCHEN_CAPACITY', 'CUSTOMER_CHURN', 'COST_PREDICTION', 'DEMAND_FORECAST') NOT NULL,
    model_version VARCHAR(50),
    prediction_date DATE NOT NULL,
    prediction_data JSON,
    confidence_score DECIMAL(5,2),
    actual_value DECIMAL(15,2),
    accuracy_score DECIMAL(5,2),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (prediction_id),
    KEY idx_ai_predictions_tenant_id (tenant_id),
    KEY idx_ai_predictions_branch_id (branch_id),
    KEY idx_ai_predictions_type (prediction_type),
    KEY idx_ai_predictions_date (prediction_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_insights (
    insight_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    insight_type ENUM('MENU_ENGINEERING', 'STAFF_OPTIMIZATION', 'WASTE_REDUCTION', 'PRICING_OPTIMIZATION', 'FRAUD_DETECTION', 'EXECUTIVE') NOT NULL,
    insight_title VARCHAR(200),
    insight_description TEXT,
    impact_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL'),
    recommended_action TEXT,
    status ENUM('NEW', 'REVIEWED', 'IMPLEMENTED', 'DISMISSED') DEFAULT 'NEW',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (insight_id),
    KEY idx_ai_insights_tenant_id (tenant_id),
    KEY idx_ai_insights_branch_id (branch_id),
    KEY idx_ai_insights_type (insight_type),
    KEY idx_ai_insights_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Delivery Management Tables
CREATE TABLE IF NOT EXISTS delivery_drivers (
    driver_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    driver_code VARCHAR(50) NOT NULL,
    driver_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    vehicle_type VARCHAR(50),
    vehicle_plate VARCHAR(20),
    status ENUM('ACTIVE', 'INACTIVE', 'ON_DELIVERY') DEFAULT 'ACTIVE',
    current_location_lat DECIMAL(10,8),
    current_location_lng DECIMAL(11,8),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (driver_id),
    UNIQUE KEY idx_delivery_drivers_tenant_code (tenant_id, driver_code),
    KEY idx_delivery_drivers_tenant_id (tenant_id),
    KEY idx_delivery_drivers_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_orders (
    delivery_order_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED,
    driver_id BIGINT UNSIGNED,
    delivery_type ENUM('INTERNAL', 'GOFOOD', 'GRABFOOD', 'SHOPEEFOOD', 'MAXIM') NOT NULL,
    external_order_id VARCHAR(100),
    customer_name VARCHAR(150),
    customer_phone VARCHAR(20),
    delivery_address TEXT,
    delivery_lat DECIMAL(10,8),
    delivery_lng DECIMAL(11,8),
    estimated_distance_km DECIMAL(10,2),
    estimated_time_minutes INT,
    delivery_fee DECIMAL(10,2),
    status ENUM('PENDING', 'ASSIGNED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED') DEFAULT 'PENDING',
    pickup_time TIMESTAMP NULL,
    delivery_time TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (delivery_order_id),
    KEY idx_delivery_orders_tenant_id (tenant_id),
    KEY idx_delivery_orders_branch_id (branch_id),
    KEY idx_delivery_orders_order_id (order_id),
    KEY idx_delivery_orders_driver_id (driver_id),
    KEY idx_delivery_orders_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES delivery_drivers(driver_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HR & Payroll Tables
CREATE TABLE IF NOT EXISTS employees (
    employee_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    employee_code VARCHAR(50) NOT NULL,
    employee_name VARCHAR(150) NOT NULL,
    position VARCHAR(100),
    department VARCHAR(100),
    hire_date DATE,
    status ENUM('ACTIVE', 'INACTIVE', 'RESIGNED', 'TERMINATED') DEFAULT 'ACTIVE',
    base_salary DECIMAL(15,2),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (employee_id),
    UNIQUE KEY idx_employees_tenant_code (tenant_id, employee_code),
    KEY idx_employees_tenant_id (tenant_id),
    KEY idx_employees_branch_id (branch_id),
    KEY idx_employees_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance (
    attendance_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    employee_id BIGINT UNSIGNED NOT NULL,
    attendance_date DATE NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    work_hours DECIMAL(5,2),
    status ENUM('PRESENT', 'ABSENT', 'LATE', 'LEAVE', 'HOLIDAY') DEFAULT 'PRESENT',
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (attendance_id),
    UNIQUE KEY idx_attendance_employee_date (employee_id, attendance_date),
    KEY idx_attendance_tenant_id (tenant_id),
    KEY idx_attendance_branch_id (branch_id),
    KEY idx_attendance_employee_id (employee_id),
    KEY idx_attendance_date (attendance_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payroll (
    payroll_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    payroll_number VARCHAR(50) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_gross_pay DECIMAL(15,2),
    total_deductions DECIMAL(15,2),
    total_net_pay DECIMAL(15,2),
    status ENUM('DRAFT', 'CALCULATED', 'APPROVED', 'PAID') DEFAULT 'DRAFT',
    processed_date DATE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (payroll_id),
    UNIQUE KEY idx_payroll_tenant_number (tenant_id, payroll_number),
    KEY idx_payroll_tenant_id (tenant_id),
    KEY idx_payroll_branch_id (branch_id),
    KEY idx_payroll_period (period_start, period_end),
    KEY idx_payroll_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payroll_items (
    payroll_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    payroll_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    base_salary DECIMAL(15,2),
    overtime_hours DECIMAL(5,2),
    overtime_pay DECIMAL(15,2),
    bonus DECIMAL(15,2),
    commission DECIMAL(15,2),
    deductions DECIMAL(15,2),
    net_pay DECIMAL(15,2),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (payroll_item_id),
    KEY idx_payroll_items_payroll_id (payroll_id),
    KEY idx_payroll_items_employee_id (employee_id),
    FOREIGN KEY (payroll_id) REFERENCES payroll(payroll_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Accounting Module Tables
CREATE TABLE IF NOT EXISTS chart_of_accounts (
    account_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(150) NOT NULL,
    account_type ENUM('ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE') NOT NULL,
    parent_account_id BIGINT UNSIGNED,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (account_id),
    UNIQUE KEY idx_chart_of_accounts_tenant_code (tenant_id, account_code),
    KEY idx_chart_of_accounts_tenant_id (tenant_id),
    KEY idx_chart_of_accounts_type (account_type),
    KEY idx_chart_of_accounts_parent (parent_account_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (parent_account_id) REFERENCES chart_of_accounts(account_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS journal_entries (
    journal_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    journal_number VARCHAR(50) NOT NULL,
    journal_date DATE NOT NULL,
    description TEXT,
    status ENUM('DRAFT', 'POSTED', 'REVERSED') DEFAULT 'DRAFT',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (journal_id),
    UNIQUE KEY idx_journal_entries_tenant_number (tenant_id, journal_number),
    KEY idx_journal_entries_tenant_id (tenant_id),
    KEY idx_journal_entries_branch_id (branch_id),
    KEY idx_journal_entries_date (journal_date),
    KEY idx_journal_entries_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS journal_lines (
    journal_line_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    journal_id BIGINT UNSIGNED NOT NULL,
    account_id BIGINT UNSIGNED NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0,
    credit DECIMAL(15,2) DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (journal_line_id),
    KEY idx_journal_lines_journal_id (journal_id),
    KEY idx_journal_lines_account_id (account_id),
    FOREIGN KEY (journal_id) REFERENCES journal_entries(journal_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (account_id) REFERENCES chart_of_accounts(account_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supply Chain Tables
CREATE TABLE IF NOT EXISTS purchase_requisitions (
    requisition_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    requisition_number VARCHAR(50) NOT NULL,
    requisition_date DATE NOT NULL,
    requested_by BIGINT UNSIGNED,
    status ENUM('DRAFT', 'PENDING', 'APPROVED', 'REJECTED', 'COMPLETED') DEFAULT 'DRAFT',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (requisition_id),
    UNIQUE KEY idx_purchase_requisitions_tenant_number (tenant_id, requisition_number),
    KEY idx_purchase_requisitions_tenant_id (tenant_id),
    KEY idx_purchase_requisitions_branch_id (branch_id),
    KEY idx_purchase_requisitions_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Maintenance Tables
CREATE TABLE IF NOT EXISTS assets (
    asset_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    asset_code VARCHAR(50) NOT NULL,
    asset_name VARCHAR(150) NOT NULL,
    asset_type VARCHAR(50),
    purchase_date DATE,
    purchase_cost DECIMAL(15,2),
    current_value DECIMAL(15,2),
    location VARCHAR(100),
    status ENUM('ACTIVE', 'INACTIVE', 'MAINTENANCE', 'DISPOSED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (asset_id),
    UNIQUE KEY idx_assets_tenant_code (tenant_id, asset_code),
    KEY idx_assets_tenant_id (tenant_id),
    KEY idx_assets_branch_id (branch_id),
    KEY idx_assets_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS maintenance_schedules (
    schedule_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    asset_id BIGINT UNSIGNED NOT NULL,
    schedule_type ENUM('PREVENTIVE', 'PREDICTIVE', 'CORRECTIVE') NOT NULL,
    scheduled_date DATE NOT NULL,
    description TEXT,
    status ENUM('PENDING', 'IN_PROGRESS', 'COMPLETED', 'SKIPPED') DEFAULT 'PENDING',
    performed_by BIGINT UNSIGNED,
    completed_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (schedule_id),
    KEY idx_maintenance_schedules_tenant_id (tenant_id),
    KEY idx_maintenance_schedules_asset_id (asset_id),
    KEY idx_maintenance_schedules_status (status),
    KEY idx_maintenance_schedules_date (scheduled_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quality & Safety Tables
CREATE TABLE IF NOT EXISTS quality_checks (
    check_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    check_type ENUM('TEMPERATURE', 'HYGIENE', 'HACCP', 'FOOD_SAFETY') NOT NULL,
    check_date DATE NOT NULL,
    checked_by BIGINT UNSIGNED,
    check_result ENUM('PASS', 'FAIL', 'WARNING'),
    temperature DECIMAL(5,2),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (check_id),
    KEY idx_quality_checks_tenant_id (tenant_id),
    KEY idx_quality_checks_branch_id (branch_id),
    KEY idx_quality_checks_type (check_type),
    KEY idx_quality_checks_date (check_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (checked_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS incidents (
    incident_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    incident_type VARCHAR(50) NOT NULL,
    incident_date DATETIME NOT NULL,
    severity ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL'),
    description TEXT,
    reported_by BIGINT UNSIGNED,
    status ENUM('OPEN', 'INVESTIGATING', 'RESOLVED', 'CLOSED') DEFAULT 'OPEN',
    resolved_at TIMESTAMP NULL,
    resolution_notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (incident_id),
    KEY idx_incidents_tenant_id (tenant_id),
    KEY idx_incidents_branch_id (branch_id),
    KEY idx_incidents_type (incident_type),
    KEY idx_incidents_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sustainability Tables
CREATE TABLE IF NOT EXISTS waste_tracking (
    waste_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    waste_date DATE NOT NULL,
    waste_type ENUM('FOOD', 'PACKAGING', 'ENERGY', 'WATER', 'OTHER') NOT NULL,
    quantity DECIMAL(10,3),
    unit VARCHAR(20),
    estimated_cost DECIMAL(10,2),
    reason TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (waste_id),
    KEY idx_waste_tracking_tenant_id (tenant_id),
    KEY idx_waste_tracking_branch_id (branch_id),
    KEY idx_waste_tracking_date (waste_date),
    KEY idx_waste_tracking_type (waste_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sustainability_metrics (
    metric_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    metric_date DATE NOT NULL,
    carbon_footprint_kg DECIMAL(10,2),
    energy_kwh DECIMAL(10,2),
    water_liters DECIMAL(10,2),
    waste_kg DECIMAL(10,2),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (metric_id),
    KEY idx_sustainability_metrics_tenant_id (tenant_id),
    KEY idx_sustainability_metrics_branch_id (branch_id),
    KEY idx_sustainability_metrics_date (metric_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Advanced Features Tables
CREATE TABLE IF NOT EXISTS floor_plans (
    floor_plan_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    plan_name VARCHAR(150) NOT NULL,
    layout_data JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (floor_plan_id),
    KEY idx_floor_plans_tenant_id (tenant_id),
    KEY idx_floor_plans_branch_id (branch_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qr_codes (
    qr_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    qr_type ENUM('TABLE', 'MENU', 'PAYMENT', 'PROMOTION') NOT NULL,
    reference_id BIGINT UNSIGNED,
    qr_code VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (qr_id),
    UNIQUE KEY idx_qr_codes_code (qr_code),
    KEY idx_qr_codes_tenant_id (tenant_id),
    KEY idx_qr_codes_branch_id (branch_id),
    KEY idx_qr_codes_type (qr_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
