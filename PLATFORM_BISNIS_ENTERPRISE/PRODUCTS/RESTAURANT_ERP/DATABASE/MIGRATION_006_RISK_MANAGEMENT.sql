-- Migration 006: Risk Management Tables
-- Risk Assessment & Mitigation (Phase 11 - RESEARCH_34)

-- Create risk_assessments table
CREATE TABLE IF NOT EXISTS risk_assessments (
    risk_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    risk_code VARCHAR(50) NOT NULL,
    risk_name VARCHAR(150) NOT NULL,
    risk_category ENUM('TECHNICAL', 'BUSINESS', 'OPERATIONAL', 'EXTERNAL', 'FINANCIAL', 'REGULATORY') NOT NULL,
    risk_description TEXT,
    probability ENUM('VERY_LOW', 'LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH') DEFAULT 'MEDIUM',
    impact ENUM('VERY_LOW', 'LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH') DEFAULT 'MEDIUM',
    risk_score INT GENERATED ALWAYS AS (
        CASE probability
            WHEN 'VERY_LOW' THEN 1
            WHEN 'LOW' THEN 2
            WHEN 'MEDIUM' THEN 3
            WHEN 'HIGH' THEN 4
            WHEN 'VERY_HIGH' THEN 5
        END *
        CASE impact
            WHEN 'VERY_LOW' THEN 1
            WHEN 'LOW' THEN 2
            WHEN 'MEDIUM' THEN 3
            WHEN 'HIGH' THEN 4
            WHEN 'VERY_HIGH' THEN 5
        END
    ) STORED,
    mitigation_strategy TEXT,
    owner VARCHAR(100),
    status ENUM('OPEN', 'MITIGATING', 'MONITORING', 'CLOSED') DEFAULT 'OPEN',
    last_reviewed_at TIMESTAMP NULL,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_risk_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_risk_tenant (tenant_id),
    INDEX idx_risk_category (risk_category),
    INDEX idx_risk_score (risk_score),
    INDEX idx_risk_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create risk_incidents table
CREATE TABLE IF NOT EXISTS risk_incidents (
    incident_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    risk_id BIGINT UNSIGNED,
    incident_code VARCHAR(50) NOT NULL,
    incident_title VARCHAR(200) NOT NULL,
    incident_description TEXT,
    severity ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
    impact_description TEXT,
    affected_systems JSON,
    root_cause TEXT,
    resolution TEXT,
    status ENUM('OPEN', 'INVESTIGATING', 'RESOLVING', 'RESOLVED', 'CLOSED') DEFAULT 'OPEN',
    occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    reported_by BIGINT,
    resolved_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_incident_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    CONSTRAINT fk_incident_risk FOREIGN KEY (risk_id) REFERENCES risk_assessments(risk_id),
    INDEX idx_incident_tenant (tenant_id),
    INDEX idx_incident_risk (risk_id),
    INDEX idx_incident_severity (severity),
    INDEX idx_incident_status (status),
    INDEX idx_incident_occurred (occurred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create system_health_checks table
CREATE TABLE IF NOT EXISTS system_health_checks (
    check_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    check_name VARCHAR(100) NOT NULL,
    check_type ENUM('DATABASE', 'API', 'STORAGE', 'SECURITY', 'PERFORMANCE', 'BACKUP', 'NETWORK', 'MEMORY', 'CPU', 'DISK', 'SSL', 'SERVICE') NOT NULL,
    check_endpoint VARCHAR(255),
    expected_result TEXT,
    check_interval_minutes INT DEFAULT 5,
    status ENUM('HEALTHY', 'WARNING', 'CRITICAL', 'UNKNOWN') DEFAULT 'UNKNOWN',
    last_check_at TIMESTAMP NULL,
    last_check_result TEXT,
    last_check_duration_ms INT,
    failure_count INT DEFAULT 0,
    alert_threshold INT DEFAULT 3,
    is_enabled TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_check_type (check_type),
    INDEX idx_check_status (status),
    INDEX idx_check_enabled (is_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create backup_logs table
CREATE TABLE IF NOT EXISTS backup_logs (
    backup_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    backup_type ENUM('FULL', 'INCREMENTAL', 'DIFFERENTIAL') NOT NULL,
    backup_source ENUM('DATABASE', 'FILES', 'SYSTEM') NOT NULL,
    backup_location VARCHAR(255),
    backup_size_mb DECIMAL(10,2),
    backup_status ENUM('STARTED', 'COMPLETED', 'FAILED', 'VERIFIED') DEFAULT 'STARTED',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    duration_seconds INT,
    verification_status ENUM('PENDING', 'SUCCESS', 'FAILED') DEFAULT 'PENDING',
    verification_at TIMESTAMP NULL,
    error_message TEXT,
    retention_days INT DEFAULT 30,
    created_by BIGINT,
    
    CONSTRAINT fk_backup_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_backup_tenant (tenant_id),
    INDEX idx_backup_type (backup_type),
    INDEX idx_backup_status (backup_status),
    INDEX idx_backup_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create security_audit_logs table
CREATE TABLE IF NOT EXISTS security_audit_logs (
    audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
    event_type VARCHAR(50) NOT NULL,
    event_category ENUM('AUTHENTICATION', 'AUTHORIZATION', 'DATA_ACCESS', 'DATA_MODIFICATION', 'SYSTEM', 'NETWORK') NOT NULL,
    event_description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_method VARCHAR(10),
    request_url TEXT,
    request_params TEXT,
    response_status INT,
    risk_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'LOW',
    additional_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_audit_tenant (tenant_id),
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_event_type (event_type),
    INDEX idx_audit_category (event_category),
    INDEX idx_audit_risk (risk_level),
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create disaster_recovery_plans table
CREATE TABLE IF NOT EXISTS disaster_recovery_plans (
    drp_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    drp_code VARCHAR(50) NOT NULL UNIQUE,
    drp_name VARCHAR(150) NOT NULL,
    drp_type ENUM('DATA_RECOVERY', 'SYSTEM_RECOVERY', 'FACILITY_RECOVERY', 'FULL_RECOVERY') NOT NULL,
    recovery_objectives JSON,
    recovery_procedures TEXT,
    contact_persons JSON,
    backup_locations JSON,
    testing_schedule VARCHAR(100),
    last_tested_at TIMESTAMP NULL,
    last_test_result TEXT,
    status ENUM('DRAFT', 'ACTIVE', 'REVIEW', 'OUTDATED') DEFAULT 'DRAFT',
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_drp_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_drp_tenant (tenant_id),
    INDEX idx_drp_type (drp_type),
    INDEX idx_drp_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create sla_monitoring table
CREATE TABLE IF NOT EXISTS sla_monitoring (
    sla_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    target_value DECIMAL(10,2),
    current_value DECIMAL(10,2),
    unit VARCHAR(20),
    measurement_period ENUM('HOURLY', 'DAILY', 'WEEKLY', 'MONTHLY') DEFAULT 'DAILY',
    status ENUM('COMPLIANT', 'WARNING', 'BREACH') DEFAULT 'COMPLIANT',
    last_measured_at TIMESTAMP NULL,
    breach_count INT DEFAULT 0,
    breach_threshold INT DEFAULT 3,
    alert_recipients JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_sla_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_sla_tenant (tenant_id),
    INDEX idx_sla_service (service_name),
    INDEX idx_sla_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system health checks
INSERT INTO system_health_checks (check_name, check_type, check_endpoint, expected_result, check_interval_minutes) VALUES
('Database Connectivity', 'DATABASE', 'SELECT 1', '1', 5),
('Database Response Time', 'DATABASE', 'SELECT 1', '< 100ms', 5),
('API Response Time', 'API', '/api/health', '200 OK', 5),
('Storage Available', 'STORAGE', '/api/storage/health', '> 10GB', 10),
('SSL Certificate', 'SSL', 'api.example.com', 'Valid', 60),
('Memory Usage', 'MEMORY', '/api/system/memory', '< 80%', 5),
('CPU Usage', 'CPU', '/api/system/cpu', '< 80%', 5),
('Disk Usage', 'DISK', '/api/system/disk', '< 80%', 10),
('Network Latency', 'NETWORK', 'api.example.com', '< 50ms', 5),
('Backup Status', 'BACKUP', '/api/backup/status', 'SUCCESS', 60),
('Service Uptime', 'SERVICE', '/api/service/status', 'RUNNING', 5),
('Security Headers', 'SECURITY', '/api/security/headers', 'Present', 60);
