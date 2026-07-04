-- Migration Phase 53: Integration Hub
-- Provides centralized integration management for third-party services, APIs, and external systems

-- External Integrations Table
CREATE TABLE IF NOT EXISTS external_integrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Integration Details
    integration_name VARCHAR(255) NOT NULL,
    integration_type ENUM('payment_gateway', 'accounting', 'hr_system', 'crm', 'pos', 'inventory', 'loyalty', 'delivery', 'analytics', 'other') NOT NULL,
    provider_name VARCHAR(255) NOT NULL,
    
    -- Connection
    api_endpoint VARCHAR(255) NULL,
    api_key VARCHAR(255) NULL,
    api_secret VARCHAR(255) NULL,
    webhook_url VARCHAR(255) NULL,
    
    -- Configuration
    integration_config JSON NULL,
    
    -- Sync Settings
    sync_frequency ENUM('real_time', 'hourly', 'daily', 'weekly', 'manual') DEFAULT 'daily',
    last_sync_at DATETIME NULL,
    next_sync_at DATETIME NULL,
    
    -- Status
    integration_status ENUM('disconnected', 'connected', 'active', 'error', 'maintenance') DEFAULT 'disconnected',
    
    -- Health
    last_health_check DATETIME NULL,
    health_status ENUM('healthy', 'degraded', 'unhealthy') NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_integration_type (integration_type),
    INDEX idx_integration_status (integration_status),
    INDEX idx_health_status (health_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Integration Mappings Table
CREATE TABLE IF NOT EXISTS integration_mappings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    integration_id BIGINT UNSIGNED NOT NULL,
    
    -- Mapping Details
    mapping_name VARCHAR(255) NOT NULL,
    mapping_type ENUM('data_field', 'entity', 'workflow', 'event', 'other') NOT NULL,
    
    -- Source
    source_system VARCHAR(100) NOT NULL,
    source_entity VARCHAR(100) NOT NULL,
    source_field VARCHAR(100) NULL,
    
    -- Target
    target_system VARCHAR(100) NOT NULL,
    target_entity VARCHAR(100) NOT NULL,
    target_field VARCHAR(100) NULL,
    
    -- Transformation
    transformation_rules JSON NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_integration_id (integration_id),
    INDEX idx_mapping_type (mapping_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (integration_id) REFERENCES external_integrations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Integration Sync Logs Table
CREATE TABLE IF NOT EXISTS integration_sync_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    integration_id BIGINT UNSIGNED NOT NULL,
    
    -- Sync Details
    sync_type ENUM('full', 'incremental', 'manual', 'webhook') NOT NULL,
    sync_direction ENUM('inbound', 'outbound', 'bidirectional') NOT NULL,
    
    -- Records
    records_processed INT DEFAULT 0,
    records_success INT DEFAULT 0,
    records_failed INT DEFAULT 0,
    
    -- Timing
    started_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    duration_seconds INT NULL,
    
    -- Status
    sync_status ENUM('running', 'completed', 'failed', 'partial') NOT NULL,
    
    -- Error
    error_message TEXT NULL,
    
    -- Additional Data
    sync_details JSON NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_integration_id (integration_id),
    INDEX idx_sync_status (sync_status),
    INDEX idx_started_at (started_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (integration_id) REFERENCES external_integrations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Integration Webhooks Table
CREATE TABLE IF NOT EXISTS integration_webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    integration_id BIGINT UNSIGNED NOT NULL,
    
    -- Webhook Details
    webhook_name VARCHAR(255) NOT NULL,
    webhook_event VARCHAR(255) NOT NULL,
    
    -- Configuration
    webhook_url VARCHAR(500) NOT NULL,
    webhook_method ENUM('GET', 'POST', 'PUT', 'DELETE') DEFAULT 'POST',
    webhook_headers JSON NULL,
    webhook_body_template JSON NULL,
    
    -- Retry Settings
    retry_count INT DEFAULT 3,
    retry_interval_seconds INT DEFAULT 60,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_integration_id (integration_id),
    INDEX idx_webhook_event (webhook_event),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (integration_id) REFERENCES external_integrations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Integration Analytics Table
CREATE TABLE IF NOT EXISTS integration_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    integration_id BIGINT UNSIGNED NULL,
    
    -- Period
    metric_date DATE NOT NULL,
    metric_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    
    -- Performance Metrics
    total_syncs INT DEFAULT 0,
    successful_syncs INT DEFAULT 0,
    failed_syncs INT DEFAULT 0,
    success_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Data Metrics
    records_synced INT DEFAULT 0,
    data_volume_mb DECIMAL(15,2) DEFAULT 0.00,
    
    -- Timing Metrics
    avg_sync_duration_seconds DECIMAL(10,2) DEFAULT 0.00,
    
    -- Error Metrics
    error_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_integration_id (integration_id),
    INDEX idx_metric_date (metric_date),
    INDEX idx_metric_type (metric_type),
    UNIQUE KEY unique_date (restaurant_id, integration_id, metric_date, metric_type),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (integration_id) REFERENCES external_integrations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
