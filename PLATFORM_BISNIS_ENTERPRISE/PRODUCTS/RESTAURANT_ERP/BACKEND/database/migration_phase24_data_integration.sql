-- Migration Phase 24: Data Integration Layer
-- Provides unified data model architecture and API connectors for POS, payment processors, and delivery platforms

-- Unified Data Model - External Systems Table
CREATE TABLE IF NOT EXISTS external_systems (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    system_type ENUM('pos', 'payment_processor', 'delivery_platform', 'accounting', 'other') NOT NULL,
    system_name VARCHAR(100) NOT NULL,
    system_identifier VARCHAR(255) NOT NULL,
    
    -- API Configuration
    api_base_url VARCHAR(500) NULL,
    api_version VARCHAR(50) NULL,
    api_key_encrypted TEXT NULL,
    api_secret_encrypted TEXT NULL,
    api_token_encrypted TEXT NULL,
    webhook_url VARCHAR(500) NULL,
    webhook_secret_encrypted TEXT NULL,
    
    -- Integration Configuration
    integration_config JSON NULL,
    mapping_config JSON NULL,
    
    -- Sync Configuration
    sync_mode ENUM('realtime', 'scheduled', 'manual') DEFAULT 'scheduled',
    sync_frequency VARCHAR(50) NULL,
    last_sync_at DATETIME NULL,
    last_sync_status ENUM('success', 'partial', 'failed') NULL,
    last_sync_error TEXT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_system_type (system_type),
    INDEX idx_system_identifier (system_identifier),
    INDEX idx_is_active (is_active),
    INDEX idx_last_sync_at (last_sync_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Mappings Table
CREATE TABLE IF NOT EXISTS data_mappings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    external_system_id BIGINT UNSIGNED NOT NULL,
    mapping_type ENUM('product', 'category', 'customer', 'order', 'payment', 'other') NOT NULL,
    
    -- Mapping Configuration
    local_entity_id VARCHAR(255) NOT NULL,
    external_entity_id VARCHAR(255) NOT NULL,
    mapping_data JSON NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_external_system_id (external_system_id),
    INDEX idx_mapping_type (mapping_type),
    INDEX idx_local_entity_id (local_entity_id),
    INDEX idx_external_entity_id (external_entity_id),
    UNIQUE KEY unique_mapping (restaurant_id, external_system_id, mapping_type, local_entity_id, external_entity_id),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (external_system_id) REFERENCES external_systems(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sync Logs Table
CREATE TABLE IF NOT EXISTS sync_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    external_system_id BIGINT UNSIGNED NOT NULL,
    sync_type ENUM('full', 'incremental', 'webhook', 'manual') NOT NULL,
    
    -- Sync Details
    sync_direction ENUM('inbound', 'outbound', 'bidirectional') NOT NULL,
    sync_status ENUM('started', 'in_progress', 'completed', 'failed', 'cancelled') DEFAULT 'started',
    
    -- Sync Results
    total_records INT DEFAULT 0,
    successful_records INT DEFAULT 0,
    failed_records INT DEFAULT 0,
    skipped_records INT DEFAULT 0,
    
    -- Error Details
    error_message TEXT NULL,
    error_details JSON NULL,
    
    -- Execution Details
    started_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    duration_seconds INT NULL,
    
    -- Trigger Information
    triggered_by ENUM('system', 'manual', 'webhook', 'schedule') DEFAULT 'system',
    triggered_by_user_id BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_external_system_id (external_system_id),
    INDEX idx_sync_type (sync_type),
    INDEX idx_sync_status (sync_status),
    INDEX idx_started_at (started_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (external_system_id) REFERENCES external_systems(id) ON DELETE CASCADE,
    FOREIGN KEY (triggered_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Rate Limiting Table
CREATE TABLE IF NOT EXISTS api_rate_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    external_system_id BIGINT UNSIGNED NOT NULL,
    
    -- Rate Limit Configuration
    rate_limit_type ENUM('per_second', 'per_minute', 'per_hour', 'per_day') NOT NULL,
    rate_limit INT NOT NULL,
    current_count INT DEFAULT 0,
    window_start DATETIME NOT NULL,
    
    -- Status
    is_blocked BOOLEAN DEFAULT FALSE,
    blocked_until DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_external_system_id (external_system_id),
    INDEX idx_window_start (window_start),
    INDEX idx_is_blocked (is_blocked),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (external_system_id) REFERENCES external_systems(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook Events Table
CREATE TABLE IF NOT EXISTS webhook_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    external_system_id BIGINT UNSIGNED NOT NULL,
    
    -- Event Details
    event_type VARCHAR(100) NOT NULL,
    event_id VARCHAR(255) NOT NULL,
    event_data JSON NOT NULL,
    
    -- Processing Status
    processing_status ENUM('pending', 'processing', 'completed', 'failed', 'retrying') DEFAULT 'pending',
    retry_count INT DEFAULT 0,
    max_retries INT DEFAULT 3,
    
    -- Error Details
    error_message TEXT NULL,
    last_retry_at DATETIME NULL,
    
    -- Timestamps
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_external_system_id (external_system_id),
    INDEX idx_event_type (event_type),
    INDEX idx_event_id (event_id),
    INDEX idx_processing_status (processing_status),
    INDEX idx_received_at (received_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (external_system_id) REFERENCES external_systems(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Validation Rules Table
CREATE TABLE IF NOT EXISTS data_validation_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    external_system_id BIGINT UNSIGNED NOT NULL,
    rule_name VARCHAR(255) NOT NULL,
    rule_type ENUM('format', 'range', 'required', 'custom') NOT NULL,
    
    -- Rule Configuration
    entity_type VARCHAR(100) NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    rule_config JSON NOT NULL,
    
    -- Priority and Status
    priority INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_external_system_id (external_system_id),
    INDEX idx_entity_type (entity_type),
    INDEX idx_is_active (is_active),
    INDEX idx_priority (priority),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (external_system_id) REFERENCES external_systems(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Integration Monitoring Table
CREATE TABLE IF NOT EXISTS integration_monitoring (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    external_system_id BIGINT UNSIGNED NOT NULL,
    
    -- Monitoring Metrics
    metric_type ENUM('uptime', 'response_time', 'error_rate', 'sync_success', 'data_quality') NOT NULL,
    metric_value DECIMAL(15,2) NOT NULL,
    metric_unit VARCHAR(50) NULL,
    
    -- Additional Data
    metric_data JSON NULL,
    
    -- Timestamp
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_external_system_id (external_system_id),
    INDEX idx_metric_type (metric_type),
    INDEX idx_recorded_at (recorded_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (external_system_id) REFERENCES external_systems(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add integration permissions to roles table
ALTER TABLE roles 
ADD COLUMN IF NOT EXISTS can_manage_integrations BOOLEAN DEFAULT FALSE AFTER can_resolve_discrepancies,
ADD COLUMN IF NOT EXISTS can_view_integrations BOOLEAN DEFAULT FALSE AFTER can_manage_integrations;

-- Update admin role
UPDATE roles 
SET can_manage_integrations = TRUE,
    can_view_integrations = TRUE
WHERE role_name = 'admin';

-- Update manager role
UPDATE roles 
SET can_manage_integrations = TRUE,
    can_view_integrations = TRUE
WHERE role_name = 'manager';

-- Update staff role
UPDATE roles 
SET can_view_integrations = TRUE
WHERE role_name = 'staff';
