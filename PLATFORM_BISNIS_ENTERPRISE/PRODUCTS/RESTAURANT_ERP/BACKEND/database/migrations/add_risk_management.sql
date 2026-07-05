-- Create risk_assessments table
CREATE TABLE IF NOT EXISTS risk_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    risk_category ENUM('technical', 'business', 'operational', 'external') NOT NULL,
    risk_type VARCHAR(100) NOT NULL,
    risk_description TEXT,
    probability ENUM('very_low', 'low', 'medium', 'high', 'very_high') NOT NULL,
    impact ENUM('very_low', 'low', 'medium', 'high', 'very_high') NOT NULL,
    risk_score INT NOT NULL,
    mitigation_strategy TEXT,
    mitigation_status ENUM('not_started', 'in_progress', 'completed', 'on_hold') DEFAULT 'not_started',
    owner VARCHAR(100),
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_risk_category (risk_category),
    INDEX idx_risk_score (risk_score),
    INDEX idx_mitigation_status (mitigation_status)
) ENGINE=InnoDB;

-- Create risk_incidents table
CREATE TABLE IF NOT EXISTS risk_incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    risk_assessment_id INT,
    incident_type VARCHAR(100) NOT NULL,
    incident_description TEXT,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    status ENUM('open', 'investigating', 'resolved', 'closed') DEFAULT 'open',
    impact_assessment TEXT,
    resolution_actions TEXT,
    lessons_learned TEXT,
    reported_by INT,
    resolved_by INT,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_risk_assessment_id (risk_assessment_id),
    INDEX idx_incident_type (incident_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create system_health_checks table
CREATE TABLE IF NOT EXISTS system_health_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    check_type VARCHAR(50) NOT NULL,
    check_name VARCHAR(100) NOT NULL,
    status ENUM('healthy', 'warning', 'critical', 'unknown') NOT NULL,
    message TEXT,
    metrics JSON,
    last_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    check_frequency_minutes INT DEFAULT 5,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_check_type (check_type),
    INDEX idx_status (status),
    INDEX idx_last_checked (last_checked)
);

-- Create backup_logs table
CREATE TABLE IF NOT EXISTS backup_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_type ENUM('full', 'incremental', 'differential') NOT NULL,
    backup_source VARCHAR(100) NOT NULL,
    backup_location VARCHAR(255) NOT NULL,
    backup_size_bytes BIGINT,
    status ENUM('started', 'completed', 'failed', 'cancelled') NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    error_message TEXT,
    verified BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP NULL,
    INDEX idx_backup_type (backup_type),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
);

-- Create security_audit_logs table
CREATE TABLE IF NOT EXISTS security_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT,
    user_id INT,
    action_type VARCHAR(100) NOT NULL,
    action_description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_data JSON,
    response_status INT,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create disaster_recovery_plans table
CREATE TABLE IF NOT EXISTS disaster_recovery_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    plan_name VARCHAR(100) NOT NULL,
    plan_type ENUM('data_loss', 'system_outage', 'security_breach', 'natural_disaster') NOT NULL,
    recovery_objectives JSON,
    recovery_procedures TEXT,
    contact_information JSON,
    last_tested DATE,
    next_test_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_plan_type (plan_type)
) ENGINE=InnoDB;

-- Create sla_monitoring table
CREATE TABLE IF NOT EXISTS sla_monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    sla_type VARCHAR(50) NOT NULL,
    sla_target DECIMAL(5,2) NOT NULL,
    sla_actual DECIMAL(5,2),
    measurement_period VARCHAR(20) NOT NULL,
    status ENUM('met', 'breached', 'warning') NOT NULL,
    breach_reason TEXT,
    measured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_sla_type (sla_type),
    INDEX idx_status (status),
    INDEX idx_measured_at (measured_at)
) ENGINE=InnoDB;

-- Insert default system health checks
INSERT INTO system_health_checks (check_type, check_name, status, check_frequency_minutes) VALUES
('database', 'Database Connectivity', 'healthy', 1),
('database', 'Database Replication Lag', 'healthy', 5),
('api', 'API Response Time', 'healthy', 1),
('api', 'API Error Rate', 'healthy', 5),
('storage', 'Disk Space', 'healthy', 5),
('storage', 'Backup Integrity', 'healthy', 60),
('security', 'SSL Certificate', 'healthy', 60),
('security', 'Failed Login Attempts', 'healthy', 5),
('performance', 'Memory Usage', 'healthy', 1),
('performance', 'CPU Usage', 'healthy', 1),
('external', 'Payment Gateway', 'healthy', 5),
('external', 'Delivery API', 'healthy', 5);
