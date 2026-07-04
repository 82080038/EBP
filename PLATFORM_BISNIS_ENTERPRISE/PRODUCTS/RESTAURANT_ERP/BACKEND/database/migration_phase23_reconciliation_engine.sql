-- Migration Phase 23: Unified Reconciliation Engine
-- Addresses the critical gap in competitor systems where data fragmentation causes reconciliation crises
-- This module provides order-level matching across POS, processors, and delivery platforms

-- Reconciliation Transactions Table
CREATE TABLE IF NOT EXISTS reconciliation_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    external_order_id VARCHAR(255) NOT NULL,
    pos_order_id VARCHAR(255),
    processor_transaction_id VARCHAR(255),
    delivery_platform_order_id VARCHAR(255),
    delivery_platform_name ENUM('grabfood', 'gofood', 'shopeefood', 'foodpanda', 'other') NULL,
    
    -- Order Details
    order_date DATETIME NOT NULL,
    order_amount DECIMAL(15,2) NOT NULL,
    order_currency VARCHAR(3) DEFAULT 'IDR',
    
    -- Source Amounts
    pos_amount DECIMAL(15,2) NULL,
    processor_amount DECIMAL(15,2) NULL,
    delivery_platform_amount DECIMAL(15,2) NULL,
    
    -- Reconciliation Status
    reconciliation_status ENUM('pending', 'matched', 'partial_match', 'discrepancy', 'resolved') DEFAULT 'pending',
    match_confidence DECIMAL(5,2) DEFAULT 0.00,
    
    -- Discrepancy Details
    discrepancy_type ENUM('amount_mismatch', 'missing_source', 'duplicate', 'timing_mismatch', 'other') NULL,
    discrepancy_amount DECIMAL(15,2) NULL,
    discrepancy_notes TEXT NULL,
    
    -- Manual Override
    manually_matched BOOLEAN DEFAULT FALSE,
    matched_by BIGINT UNSIGNED NULL,
    matched_at DATETIME NULL,
    match_notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_external_order_id (external_order_id),
    INDEX idx_pos_order_id (pos_order_id),
    INDEX idx_processor_transaction_id (processor_transaction_id),
    INDEX idx_delivery_platform_order_id (delivery_platform_order_id),
    INDEX idx_reconciliation_status (reconciliation_status),
    INDEX idx_order_date (order_date),
    INDEX idx_created_at (created_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (matched_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reconciliation Sources Table
CREATE TABLE IF NOT EXISTS reconciliation_sources (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    source_type ENUM('pos', 'payment_processor', 'delivery_platform', 'other') NOT NULL,
    source_name VARCHAR(100) NOT NULL,
    source_identifier VARCHAR(255) NOT NULL,
    
    -- Source Configuration
    api_endpoint VARCHAR(500) NULL,
    api_key_encrypted TEXT NULL,
    api_secret_encrypted TEXT NULL,
    webhook_url VARCHAR(500) NULL,
    webhook_secret_encrypted TEXT NULL,
    
    -- Sync Configuration
    sync_frequency ENUM('realtime', 'hourly', 'daily', 'manual') DEFAULT 'daily',
    last_sync_at DATETIME NULL,
    last_sync_status ENUM('success', 'partial', 'failed') NULL,
    last_sync_error TEXT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_source_type (source_type),
    INDEX idx_is_active (is_active),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reconciliation Rules Table
CREATE TABLE IF NOT EXISTS reconciliation_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    rule_name VARCHAR(255) NOT NULL,
    rule_type ENUM('amount_tolerance', 'time_tolerance', 'auto_match', 'alert_threshold') NOT NULL,
    
    -- Rule Configuration
    rule_config JSON NOT NULL,
    
    -- Priority and Status
    priority INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_rule_type (rule_type),
    INDEX idx_is_active (is_active),
    INDEX idx_priority (priority),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reconciliation Logs Table
CREATE TABLE IF NOT EXISTS reconciliation_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    reconciliation_transaction_id BIGINT UNSIGNED NULL,
    log_type ENUM('sync', 'match', 'discrepancy', 'manual_action', 'alert') NOT NULL,
    
    -- Log Details
    log_message TEXT NOT NULL,
    log_data JSON NULL,
    
    -- Source Information
    source_type VARCHAR(50) NULL,
    source_id VARCHAR(255) NULL,
    
    -- User Information
    action_by BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_reconciliation_transaction_id (reconciliation_transaction_id),
    INDEX idx_log_type (log_type),
    INDEX idx_created_at (created_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (reconciliation_transaction_id) REFERENCES reconciliation_transactions(id) ON DELETE SET NULL,
    FOREIGN KEY (action_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reconciliation Alerts Table
CREATE TABLE IF NOT EXISTS reconciliation_alerts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    alert_type ENUM('discrepancy', 'missing_data', 'sync_failure', 'threshold_exceeded') NOT NULL,
    alert_severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    
    -- Alert Details
    alert_title VARCHAR(255) NOT NULL,
    alert_message TEXT NOT NULL,
    alert_data JSON NULL,
    
    -- Related Transaction
    reconciliation_transaction_id BIGINT UNSIGNED NULL,
    
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
    FOREIGN KEY (reconciliation_transaction_id) REFERENCES reconciliation_transactions(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reconciliation Batch Jobs Table
CREATE TABLE IF NOT EXISTS reconciliation_batch_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    job_type ENUM('sync', 'match', 'report', 'cleanup') NOT NULL,
    
    -- Job Configuration
    job_config JSON NULL,
    
    -- Job Status
    job_status ENUM('pending', 'running', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    
    -- Job Results
    total_processed INT DEFAULT 0,
    total_matched INT DEFAULT 0,
    total_discrepancies INT DEFAULT 0,
    total_errors INT DEFAULT 0,
    
    -- Execution Details
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    error_message TEXT NULL,
    
    -- Trigger Information
    triggered_by ENUM('system', 'manual', 'schedule') DEFAULT 'system',
    triggered_by_user_id BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_job_type (job_type),
    INDEX idx_job_status (job_status),
    INDEX idx_created_at (created_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (triggered_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default reconciliation rules for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_reconciliation_rules
AFTER INSERT ON restaurants
FOR EACH ROW
BEGIN
    -- Amount tolerance rule (default 1%)
    INSERT INTO reconciliation_rules (restaurant_id, rule_name, rule_type, rule_config, priority, is_active)
    VALUES (
        NEW.id,
        'Default Amount Tolerance',
        'amount_tolerance',
        JSON_OBJECT('tolerance_percentage', 1.0, 'tolerance_fixed', 1000),
        10,
        TRUE
    );
    
    -- Time tolerance rule (default 5 minutes)
    INSERT INTO reconciliation_rules (restaurant_id, rule_name, rule_type, rule_config, priority, is_active)
    VALUES (
        NEW.id,
        'Default Time Tolerance',
        'time_tolerance',
        JSON_OBJECT('tolerance_minutes', 5),
        9,
        TRUE
    );
    
    -- Auto-match rule
    INSERT INTO reconciliation_rules (restaurant_id, rule_name, rule_type, rule_config, priority, is_active)
    VALUES (
        NEW.id,
        'Auto-Match High Confidence',
        'auto_match',
        JSON_OBJECT('confidence_threshold', 95.0),
        8,
        TRUE
    );
    
    -- Alert threshold rule
    INSERT INTO reconciliation_rules (restaurant_id, rule_name, rule_type, rule_config, priority, is_active)
    VALUES (
        NEW.id,
        'Discrepancy Alert Threshold',
        'alert_threshold',
        JSON_OBJECT('discrepancy_amount', 50000, 'discrepancy_percentage', 5.0),
        7,
        TRUE
    );
END//
DELIMITER ;

-- Add reconciliation permissions to roles table if not exists
ALTER TABLE roles 
ADD COLUMN IF NOT EXISTS can_view_reconciliation BOOLEAN DEFAULT FALSE AFTER can_view_analytics,
ADD COLUMN IF NOT EXISTS can_manage_reconciliation BOOLEAN DEFAULT FALSE AFTER can_view_reconciliation,
ADD COLUMN IF NOT EXISTS can_resolve_discrepancies BOOLEAN DEFAULT FALSE AFTER can_manage_reconciliation;

-- Update admin role to have all reconciliation permissions
UPDATE roles 
SET can_view_reconciliation = TRUE,
    can_manage_reconciliation = TRUE,
    can_resolve_discrepancies = TRUE
WHERE role_name = 'admin';

-- Update manager role to have view and manage reconciliation permissions
UPDATE roles 
SET can_view_reconciliation = TRUE,
    can_manage_reconciliation = TRUE,
    can_resolve_discrepancies = TRUE
WHERE role_name = 'manager';

-- Update staff role to have view reconciliation permissions
UPDATE roles 
SET can_view_reconciliation = TRUE
WHERE role_name = 'staff';
