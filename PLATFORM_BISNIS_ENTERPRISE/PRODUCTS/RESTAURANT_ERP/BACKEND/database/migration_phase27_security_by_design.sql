-- Migration Phase 27: Security by Design
-- Provides PCI DSS compliance, end-to-end encryption, RBAC, audit logging, and secure key management

-- Security Audit Log Table
CREATE TABLE IF NOT EXISTS security_audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    
    -- Audit Details
    action_type VARCHAR(100) NOT NULL,
    action_category ENUM('authentication', 'authorization', 'data_access', 'data_modification', 'system', 'other') NOT NULL,
    action_description TEXT NOT NULL,
    
    -- Request Details
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    request_method VARCHAR(10) NULL,
    request_url TEXT NULL,
    
    -- Resource Details
    resource_type VARCHAR(100) NULL,
    resource_id VARCHAR(255) NULL,
    
    -- Status
    action_status ENUM('success', 'failed', 'blocked') NOT NULL,
    failure_reason TEXT NULL,
    
    -- Additional Data
    action_data JSON NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_action_category (action_category),
    INDEX idx_action_status (action_status),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_address (ip_address),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security Incidents Table
CREATE TABLE IF NOT EXISTS security_incidents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Incident Details
    incident_type ENUM('unauthorized_access', 'data_breach', 'malware', 'phishing', 'ddos', 'other') NOT NULL,
    incident_severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    incident_title VARCHAR(255) NOT NULL,
    incident_description TEXT NOT NULL,
    
    -- Timeline
    detected_at DATETIME NOT NULL,
    started_at DATETIME NULL,
    resolved_at DATETIME NULL,
    
    -- Impact
    affected_users INT DEFAULT 0,
    affected_data JSON NULL,
    impact_assessment TEXT NULL,
    
    -- Response
    response_actions TEXT NULL,
    resolved_by BIGINT UNSIGNED NULL,
    resolution_notes TEXT NULL,
    
    -- Status
    incident_status ENUM('open', 'investigating', 'contained', 'resolved', 'closed') DEFAULT 'open',
    
    -- Additional Data
    incident_data JSON NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_incident_type (incident_type),
    INDEX idx_incident_severity (incident_severity),
    INDEX idx_incident_status (incident_status),
    INDEX idx_detected_at (detected_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Encryption Keys Table
CREATE TABLE IF NOT EXISTS encryption_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Key Details
    key_name VARCHAR(255) NOT NULL,
    key_type ENUM('aes', 'rsa', 'hmac', 'other') NOT NULL,
    key_purpose ENUM('data_encryption', 'api_auth', 'webhook', 'other') NOT NULL,
    
    -- Key Data (encrypted)
    key_value_encrypted TEXT NOT NULL,
    key_iv_encrypted TEXT NULL,
    
    -- Key Metadata
    key_algorithm VARCHAR(50) NULL,
    key_size INT NULL,
    key_version INT DEFAULT 1,
    
    -- Validity
    valid_from DATETIME NOT NULL,
    valid_until DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Rotation
    last_rotated_at DATETIME NULL,
    rotation_frequency_days INT NULL,
    next_rotation_date DATETIME NULL,
    
    -- Access Control
    created_by BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_key_name (key_name),
    INDEX idx_key_type (key_type),
    INDEX idx_key_purpose (key_purpose),
    INDEX idx_is_active (is_active),
    INDEX idx_next_rotation_date (next_rotation_date),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security Policies Table
CREATE TABLE IF NOT EXISTS security_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Policy Details
    policy_name VARCHAR(255) NOT NULL,
    policy_type ENUM('password', 'session', 'access', 'data', 'other') NOT NULL,
    policy_description TEXT NULL,
    
    -- Policy Configuration
    policy_config JSON NOT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_enforced BOOLEAN DEFAULT TRUE,
    
    -- Priority and Status
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_policy_name (policy_name),
    INDEX idx_policy_type (policy_type),
    INDEX idx_is_active (is_active),
    INDEX idx_is_enforced (is_enforced),
    INDEX idx_priority (priority),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Access Control List Table
CREATE TABLE IF NOT EXISTS access_control_list (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Resource Details
    resource_type VARCHAR(100) NOT NULL,
    resource_id VARCHAR(255) NULL,
    
    -- Permissions
    permission ENUM('read', 'write', 'delete', 'admin', 'custom') NOT NULL,
    custom_permissions JSON NULL,
    
    -- Constraints
    granted_by BIGINT UNSIGNED NULL,
    granted_at DATETIME NOT NULL,
    expires_at DATETIME NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_resource_type (resource_type),
    INDEX idx_resource_id (resource_id),
    INDEX idx_permission (permission),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at),
    UNIQUE KEY unique_acl (restaurant_id, user_id, resource_type, resource_id, permission),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed Login Attempts Table
CREATE TABLE IF NOT EXISTS failed_login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NULL,
    
    -- Attempt Details
    username_or_email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    
    -- Attempt Status
    attempt_status ENUM('invalid_credentials', 'account_locked', 'ip_blocked', 'other') NOT NULL,
    failure_reason TEXT NULL,
    
    -- Lockout Information
    is_locked BOOLEAN DEFAULT FALSE,
    locked_until DATETIME NULL,
    lockout_duration_minutes INT DEFAULT 15,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_username_or_email (username_or_email),
    INDEX idx_ip_address (ip_address),
    INDEX idx_attempt_status (attempt_status),
    INDEX idx_is_locked (is_locked),
    INDEX idx_locked_until (locked_until),
    INDEX idx_created_at (created_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default security policies for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_security_policies
AFTER INSERT ON restaurants
FOR EACH NEW
BEGIN
    -- Password policy
    INSERT INTO security_policies (restaurant_id, policy_name, policy_type, policy_description, policy_config, priority, is_active, is_enforced)
    VALUES (
        NEW.id,
        'Password Policy',
        'password',
        'Minimum password requirements',
        JSON_OBJECT('min_length', 8, 'require_uppercase', TRUE, 'require_lowercase', TRUE, 'require_numbers', TRUE, 'require_special_chars', TRUE, 'max_age_days', 90),
        'high',
        TRUE,
        TRUE
    );
    
    -- Session policy
    INSERT INTO security_policies (restaurant_id, policy_name, policy_type, policy_description, policy_config, priority, is_active, is_enforced)
    VALUES (
        NEW.id,
        'Session Policy',
        'session',
        'Session timeout and management',
        JSON_OBJECT('timeout_minutes', 30, 'max_concurrent_sessions', 3, 'remember_me_days', 30),
        'medium',
        TRUE,
        TRUE
    );
    
    -- Access policy
    INSERT INTO security_policies (restaurant_id, policy_name, policy_type, policy_description, policy_config, priority, is_active, is_enforced)
    VALUES (
        NEW.id,
        'Access Policy',
        'access',
        'Access control requirements',
        JSON_OBJECT('require_2fa', FALSE, 'ip_whitelist_enabled', FALSE, 'allowed_ip_ranges', []),
        'medium',
        TRUE,
        TRUE
    );
    
    -- Failed login policy
    INSERT INTO security_policies (restaurant_id, policy_name, policy_type, policy_description, policy_config, priority, is_active, is_enforced)
    VALUES (
        NEW.id,
        'Failed Login Policy',
        'access',
        'Failed login attempt limits',
        JSON_OBJECT('max_attempts', 5, 'lockout_duration_minutes', 15, 'ip_lockout_enabled', TRUE),
        'high',
        TRUE,
        TRUE
    );
END//
DELIMITER ;

-- Add security permissions to roles table
ALTER TABLE roles 
ADD COLUMN IF NOT EXISTS can_view_security_logs BOOLEAN DEFAULT FALSE AFTER can_manage_compliance,
ADD COLUMN IF NOT EXISTS can_manage_security_settings BOOLEAN DEFAULT FALSE AFTER can_view_security_logs;

-- Update admin role
UPDATE roles 
SET can_view_security_logs = TRUE,
    can_manage_security_settings = TRUE
WHERE role_name = 'admin';

-- Update manager role
UPDATE roles 
SET can_view_security_logs = TRUE,
    can_manage_security_settings = TRUE
WHERE role_name = 'manager';
