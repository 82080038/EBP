-- Migration Phase 51: Emerging Technologies
-- Provides technology integration layer with robotics, automation, and emerging tech management

-- Technology Integrations Table
CREATE TABLE IF NOT EXISTS technology_integrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Integration Details
    integration_name VARCHAR(255) NOT NULL,
    integration_type ENUM('robotics', 'automation', 'ai', 'blockchain', 'ar_vr', 'voice_assistant', 'biometric', 'other') NOT NULL,
    integration_category VARCHAR(100) NULL,
    
    -- Provider
    provider_name VARCHAR(255) NULL,
    provider_contact VARCHAR(255) NULL,
    
    -- Configuration
    api_endpoint VARCHAR(255) NULL,
    api_key VARCHAR(255) NULL,
    configuration JSON NULL,
    
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

-- Robotics Devices Table
CREATE TABLE IF NOT EXISTS robotics_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    integration_id BIGINT UNSIGNED NOT NULL,
    
    -- Device Details
    device_name VARCHAR(255) NOT NULL,
    device_type ENUM('cooking_robot', 'delivery_robot', 'cleaning_robot', 'serving_robot', 'kiosk', 'other') NOT NULL,
    device_model VARCHAR(100) NULL,
    
    -- Location
    location VARCHAR(255) NULL,
    
    -- Capabilities
    capabilities JSON NULL,
    
    -- Status
    device_status ENUM('offline', 'idle', 'busy', 'error', 'maintenance') DEFAULT 'offline',
    battery_level INT NULL,
    
    -- Performance
    total_operations INT DEFAULT 0,
    successful_operations INT DEFAULT 0,
    success_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_integration_id (integration_id),
    INDEX idx_device_type (device_type),
    INDEX idx_device_status (device_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (integration_id) REFERENCES technology_integrations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Automation Workflows Table
CREATE TABLE IF NOT EXISTS automation_workflows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Workflow Details
    workflow_name VARCHAR(255) NOT NULL,
    workflow_description TEXT NULL,
    workflow_type ENUM('kitchen', 'service', 'cleaning', 'inventory', 'other') NOT NULL,
    
    -- Trigger
    trigger_type ENUM('time_based', 'event_based', 'sensor_based', 'manual', 'api') NOT NULL,
    trigger_config JSON NOT NULL,
    
    -- Actions
    actions JSON NOT NULL,
    
    -- Conditions
    conditions JSON NULL,
    
    -- Status
    workflow_status ENUM('draft', 'active', 'paused', 'archived') DEFAULT 'draft',
    
    -- Execution
    last_executed_at DATETIME NULL,
    execution_count INT DEFAULT 0,
    success_count INT DEFAULT 0,
    failure_count INT DEFAULT 0,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_workflow_type (workflow_type),
    INDEX idx_trigger_type (trigger_type),
    INDEX idx_workflow_status (workflow_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Technology Performance Table
CREATE TABLE IF NOT EXISTS technology_performance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    integration_id BIGINT UNSIGNED NULL,
    
    -- Period
    metric_date DATE NOT NULL,
    metric_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    
    -- Performance Metrics
    uptime_percentage DECIMAL(5,2) DEFAULT 0.00,
    response_time_avg DECIMAL(10,2) DEFAULT 0.00,
    error_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Operational Metrics
    total_operations INT DEFAULT 0,
    successful_operations INT DEFAULT 0,
    failed_operations INT DEFAULT 0,
    
    -- Financial Metrics
    operational_cost DECIMAL(15,2) DEFAULT 0.00,
    cost_savings DECIMAL(15,2) DEFAULT 0.00,
    
    -- Efficiency
    efficiency_score DECIMAL(5,2) DEFAULT 0.00,
    
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
    FOREIGN KEY (integration_id) REFERENCES technology_integrations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Technology Alerts Table
CREATE TABLE IF NOT EXISTS technology_alerts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    integration_id BIGINT UNSIGNED NULL,
    
    -- Alert Details
    alert_name VARCHAR(255) NOT NULL,
    alert_description TEXT NULL,
    
    -- Condition
    metric_type VARCHAR(100) NOT NULL,
    condition_type ENUM('greater_than', 'less_than', 'equal_to', 'not_equal_to', 'percentage_change') NOT NULL,
    threshold_value DECIMAL(15,2) NOT NULL,
    
    -- Notification
    notification_channels JSON NULL,
    notification_recipients JSON NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_integration_id (integration_id),
    INDEX idx_metric_type (metric_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (integration_id) REFERENCES technology_integrations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Technology Alert History Table
CREATE TABLE IF NOT EXISTS technology_alert_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    alert_id BIGINT UNSIGNED NOT NULL,
    
    -- Alert Details
    metric_value DECIMAL(15,2) NOT NULL,
    threshold_value DECIMAL(15,2) NOT NULL,
    alert_message TEXT NULL,
    
    -- Status
    alert_status ENUM('triggered', 'acknowledged', 'resolved') DEFAULT 'triggered',
    
    -- Timing
    triggered_at DATETIME NOT NULL,
    acknowledged_at DATETIME NULL,
    acknowledged_by BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_alert_id (alert_id),
    INDEX idx_alert_status (alert_status),
    INDEX idx_triggered_at (triggered_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (alert_id) REFERENCES technology_alerts(id) ON DELETE CASCADE,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
