-- Migration for Quality & Safety Compliance

-- Quality Checks (Enhanced)
CREATE TABLE IF NOT EXISTS quality_compliance_checks (
    check_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    check_type ENUM('HACCP', 'FOOD_SAFETY', 'HYGIENE', 'TEMPERATURE', 'ALLERGEN') NOT NULL,
    check_date DATE NOT NULL,
    area VARCHAR(100),
    compliance_score DECIMAL(5,2),
    status ENUM('COMPLIANT', 'NON_COMPLIANT', 'PARTIALLY_COMPLIANT', 'PENDING') DEFAULT 'PENDING',
    issues TEXT,
    corrective_actions TEXT,
    checked_by BIGINT UNSIGNED,
    next_check_date DATE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (check_id),
    KEY idx_quality_compliance_tenant_id (tenant_id),
    KEY idx_quality_compliance_branch_id (branch_id),
    KEY idx_quality_compliance_type (check_type),
    KEY idx_quality_compliance_date (check_date),
    KEY idx_quality_compliance_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (checked_by) REFERENCES employees(employee_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Food Safety Protocols
CREATE TABLE IF NOT EXISTS food_safety_protocols (
    protocol_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    protocol_name VARCHAR(200) NOT NULL,
    protocol_type ENUM('STORAGE', 'PREPARATION', 'COOKING', 'SERVING', 'CLEANING') NOT NULL,
    description TEXT,
    critical_control_points JSON,
    monitoring_frequency VARCHAR(50),
    responsible_person VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (protocol_id),
    KEY idx_food_safety_tenant_id (tenant_id),
    KEY idx_food_safety_branch_id (branch_id),
    KEY idx_food_safety_type (protocol_type),
    KEY idx_food_safety_active (is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
