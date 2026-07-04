-- Migration Phase 26: Compliance Automation
-- Provides automated compliance management for labor laws, taxes, food safety, and licensing

-- Compliance Rules Table
CREATE TABLE IF NOT EXISTS compliance_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    rule_type ENUM('labor_law', 'tax', 'food_safety', 'licensing', 'other') NOT NULL,
    rule_name VARCHAR(255) NOT NULL,
    rule_description TEXT NULL,
    
    -- Rule Configuration
    rule_config JSON NOT NULL,
    
    -- Schedule
    check_frequency ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'on_demand') NOT NULL,
    next_check_date DATE NULL,
    
    -- Priority and Status
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_rule_type (rule_type),
    INDEX idx_next_check_date (next_check_date),
    INDEX idx_is_active (is_active),
    INDEX idx_priority (priority),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compliance Checks Table
CREATE TABLE IF NOT EXISTS compliance_checks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    compliance_rule_id BIGINT UNSIGNED NOT NULL,
    
    -- Check Details
    check_date DATE NOT NULL,
    check_status ENUM('passed', 'failed', 'warning', 'skipped') NOT NULL,
    check_result JSON NULL,
    
    -- Violations
    violations_found INT DEFAULT 0,
    violation_details JSON NULL,
    
    -- Remediation
    remediation_required BOOLEAN DEFAULT FALSE,
    remediation_deadline DATE NULL,
    remediation_status ENUM('not_started', 'in_progress', 'completed', 'overdue') NULL,
    
    -- Audit Trail
    checked_by BIGINT UNSIGNED NULL,
    checked_at TIMESTAMP NULL,
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_compliance_rule_id (compliance_rule_id),
    INDEX idx_check_date (check_date),
    INDEX idx_check_status (check_status),
    INDEX idx_remediation_status (remediation_status),
    INDEX idx_remediation_deadline (remediation_deadline),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (compliance_rule_id) REFERENCES compliance_rules(id) ON DELETE CASCADE,
    FOREIGN KEY (checked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compliance Documents Table
CREATE TABLE IF NOT EXISTS compliance_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    document_type ENUM('license', 'permit', 'certificate', 'insurance', 'other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    
    -- Document Details
    document_number VARCHAR(255) NULL,
    issuing_authority VARCHAR(255) NULL,
    
    -- Validity
    issue_date DATE NULL,
    expiry_date DATE NULL,
    is_valid BOOLEAN DEFAULT TRUE,
    
    -- File
    file_path VARCHAR(500) NULL,
    file_name VARCHAR(255) NULL,
    file_size INT NULL,
    file_mime_type VARCHAR(100) NULL,
    
    -- Alerts
    alert_days_before_expiry INT DEFAULT 30,
    last_alert_sent_at DATETIME NULL,
    
    -- Status
    status ENUM('active', 'expired', 'revoked', 'pending_renewal') DEFAULT 'active',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_document_type (document_type),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_status (status),
    INDEX idx_is_valid (is_valid),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compliance Alerts Table
CREATE TABLE IF NOT EXISTS compliance_alerts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    alert_type ENUM('check_failed', 'document_expiry', 'remediation_overdue', 'regulatory_update', 'other') NOT NULL,
    alert_severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    
    -- Alert Details
    alert_title VARCHAR(255) NOT NULL,
    alert_message TEXT NOT NULL,
    alert_data JSON NULL,
    
    -- Related Entities
    compliance_rule_id BIGINT UNSIGNED NULL,
    compliance_check_id BIGINT UNSIGNED NULL,
    compliance_document_id BIGINT UNSIGNED NULL,
    
    -- Status
    is_resolved BOOLEAN DEFAULT FALSE,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    resolution_notes TEXT NULL,
    
    -- Notification
    notification_sent BOOLEAN DEFAULT FALSE,
    notification_sent_at DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_alert_type (alert_type),
    INDEX idx_alert_severity (alert_severity),
    INDEX idx_is_resolved (is_resolved),
    INDEX idx_created_at (created_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (compliance_rule_id) REFERENCES compliance_rules(id) ON DELETE SET NULL,
    FOREIGN KEY (compliance_check_id) REFERENCES compliance_checks(id) ON DELETE SET NULL,
    FOREIGN KEY (compliance_document_id) REFERENCES compliance_documents(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Labor Law Compliance Table
CREATE TABLE IF NOT EXISTS labor_law_compliance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    staff_id BIGINT UNSIGNED NOT NULL,
    
    -- Work Hours
    max_daily_hours DECIMAL(5,2) DEFAULT 8.0,
    max_weekly_hours DECIMAL(5,2) DEFAULT 40.0,
    max_monthly_hours DECIMAL(5,2) DEFAULT 160.0,
    
    -- Break Requirements
    required_break_after_hours DECIMAL(5,2) DEFAULT 4.0,
    break_duration_minutes INT DEFAULT 30,
    
    -- Overtime
    overtime_rate_multiplier DECIMAL(3,2) DEFAULT 1.5,
    overtime_start_after_hours DECIMAL(5,2) DEFAULT 8.0,
    
    -- Minimum Wage
    minimum_hourly_rate DECIMAL(10,2) NULL,
    
    -- Leave Entitlements
    annual_leave_days INT DEFAULT 12,
    sick_leave_days INT DEFAULT 10,
    
    -- Status
    is_compliant BOOLEAN DEFAULT TRUE,
    last_check_date DATE NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_staff_id (staff_id),
    INDEX idx_is_compliant (is_compliant),
    INDEX idx_last_check_date (last_check_date),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tax Compliance Table
CREATE TABLE IF NOT EXISTS tax_compliance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Tax Configuration
    tax_type ENUM('vat', 'income_tax', 'payroll_tax', 'service_tax', 'other') NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL,
    tax_id VARCHAR(100) NULL,
    
    -- Filing Schedule
    filing_frequency ENUM('monthly', 'quarterly', 'yearly') NOT NULL,
    next_filing_date DATE NULL,
    last_filing_date DATE NULL,
    
    -- Payment
    payment_due_date DATE NULL,
    last_payment_date DATE NULL,
    
    -- Status
    is_compliant BOOLEAN DEFAULT TRUE,
    outstanding_amount DECIMAL(15,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_tax_type (tax_type),
    INDEX idx_next_filing_date (next_filing_date),
    INDEX idx_is_compliant (is_compliant),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Food Safety Compliance Table
CREATE TABLE IF NOT EXISTS food_safety_compliance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Inspection Details
    inspection_type ENUM('routine', 'complaint', 'follow_up', 'other') NOT NULL,
    inspection_date DATE NULL,
    inspector_name VARCHAR(255) NULL,
    inspector_agency VARCHAR(255) NULL,
    
    -- Results
    inspection_score INT NULL,
    inspection_grade VARCHAR(10) NULL,
    inspection_status ENUM('passed', 'failed', 'conditional', 'pending') NULL,
    
    -- Violations
    violations_found INT DEFAULT 0,
    violation_details JSON NULL,
    
    -- Follow-up
    follow_up_required BOOLEAN DEFAULT FALSE,
    follow_up_date DATE NULL,
    follow_up_completed BOOLEAN DEFAULT FALSE,
    
    -- Certificate
    certificate_number VARCHAR(255) NULL,
    certificate_expiry DATE NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_inspection_date (inspection_date),
    INDEX idx_inspection_status (inspection_status),
    INDEX idx_certificate_expiry (certificate_expiry),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default compliance rules for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_compliance_rules
AFTER INSERT ON restaurants
FOR EACH ROW
BEGIN
    -- Labor law: Daily hours check
    INSERT INTO compliance_rules (restaurant_id, rule_type, rule_name, rule_description, rule_config, check_frequency, priority, is_active)
    VALUES (
        NEW.id,
        'labor_law',
        'Daily Work Hours Limit',
        'Check if any staff exceeded maximum daily work hours',
        JSON_OBJECT('max_daily_hours', 8.0, 'allow_overtime', TRUE),
        'daily',
        'high',
        TRUE
    );
    
    -- Labor law: Weekly hours check
    INSERT INTO compliance_rules (restaurant_id, rule_type, rule_name, rule_description, rule_config, check_frequency, priority, is_active)
    VALUES (
        NEW.id,
        'labor_law',
        'Weekly Work Hours Limit',
        'Check if any staff exceeded maximum weekly work hours',
        JSON_OBJECT('max_weekly_hours', 40.0, 'allow_overtime', TRUE),
        'weekly',
        'high',
        TRUE
    );
    
    -- Tax: VAT filing reminder
    INSERT INTO compliance_rules (restaurant_id, rule_type, rule_name, rule_description, rule_config, check_frequency, priority, is_active)
    VALUES (
        NEW.id,
        'tax',
        'VAT Filing Reminder',
        'Reminder for VAT tax filing',
        JSON_OBJECT('tax_type', 'vat', 'filing_day', 20),
        'monthly',
        'critical',
        TRUE
    );
    
    -- Food safety: Certificate expiry
    INSERT INTO compliance_rules (restaurant_id, rule_type, rule_name, rule_description, rule_config, check_frequency, priority, is_active)
    VALUES (
        NEW.id,
        'food_safety',
        'Food Safety Certificate Expiry',
        'Check food safety certificate expiry and send alerts',
        JSON_OBJECT('alert_days_before', 30),
        'monthly',
        'high',
        TRUE
    );
END//
DELIMITER ;

-- Add compliance permissions to roles table
ALTER TABLE roles 
ADD COLUMN IF NOT EXISTS can_view_compliance BOOLEAN DEFAULT FALSE AFTER can_manage_offline_data,
ADD COLUMN IF NOT EXISTS can_manage_compliance BOOLEAN DEFAULT FALSE AFTER can_view_compliance;

-- Update admin role
UPDATE roles 
SET can_view_compliance = TRUE,
    can_manage_compliance = TRUE
WHERE role_name = 'admin';

-- Update manager role
UPDATE roles 
SET can_view_compliance = TRUE,
    can_manage_compliance = TRUE
WHERE role_name = 'manager';

-- Update staff role
UPDATE roles 
SET can_view_compliance = TRUE
WHERE role_name = 'staff';
