-- Migration Phase 45: Future-Ready Technologies
-- Provides IoT device management, AI/ML integration, and smart restaurant features

-- IoT Devices Table
CREATE TABLE IF NOT EXISTS iot_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Device Details
    device_name VARCHAR(255) NOT NULL,
    device_type ENUM('sensor', 'smart_meter', 'camera', 'thermostat', 'smart_lock', 'kitchen_appliance', 'display', 'other') NOT NULL,
    device_category VARCHAR(100) NULL,
    
    -- Hardware
    device_serial VARCHAR(100) NULL,
    device_model VARCHAR(100) NULL,
    manufacturer VARCHAR(255) NULL,
    
    -- Connection
    connection_type ENUM('wifi', 'bluetooth', 'zigbee', 'zwave', 'cellular', 'wired', 'other') NOT NULL,
    ip_address VARCHAR(50) NULL,
    mac_address VARCHAR(50) NULL,
    
    -- Location
    location VARCHAR(255) NULL,
    installation_date DATE NULL,
    
    -- Status
    device_status ENUM('online', 'offline', 'maintenance', 'error', 'decommissioned') DEFAULT 'offline',
    last_heartbeat DATETIME NULL,
    battery_level INT NULL, -- 0-100
    
    -- Configuration
    configuration JSON NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_device_type (device_type),
    INDEX idx_device_status (device_status),
    INDEX idx_last_heartbeat (last_heartbeat),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IoT Device Readings Table
CREATE TABLE IF NOT EXISTS iot_device_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    device_id BIGINT UNSIGNED NOT NULL,
    
    -- Reading Details
    reading_type VARCHAR(100) NOT NULL,
    reading_value DECIMAL(15,2) NOT NULL,
    reading_unit VARCHAR(50) NULL,
    
    -- Quality
    reading_quality ENUM('good', 'uncertain', 'poor') DEFAULT 'good',
    
    -- Timestamp
    reading_timestamp DATETIME NOT NULL,
    
    -- Additional Data
    additional_data JSON NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_device_id (device_id),
    INDEX idx_reading_type (reading_type),
    INDEX idx_reading_timestamp (reading_timestamp),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (device_id) REFERENCES iot_devices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Models Table
CREATE TABLE IF NOT EXISTS ai_models (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Model Details
    model_name VARCHAR(255) NOT NULL,
    model_type ENUM('demand_forecasting', 'inventory_optimization', 'staff_scheduling', 'customer_segmentation', 'churn_prediction', 'price_optimization', 'quality_detection', 'other') NOT NULL,
    model_version VARCHAR(50) NOT NULL,
    
    -- Training
    training_data_start DATE NULL,
    training_data_end DATE NULL,
    training_date DATETIME NULL,
    
    -- Performance
    accuracy DECIMAL(5,2) NULL,
    precision_score DECIMAL(5,2) NULL,
    recall_score DECIMAL(5,2) NULL,
    f1_score DECIMAL(5,2) NULL,
    
    -- Status
    model_status ENUM('training', 'ready', 'deployed', 'deprecated', 'error') DEFAULT 'training',
    
    -- Configuration
    model_config JSON NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_model_type (model_type),
    INDEX idx_model_status (model_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Predictions Table
CREATE TABLE IF NOT EXISTS ai_predictions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    
    -- Prediction Details
    prediction_type VARCHAR(100) NOT NULL,
    prediction_value DECIMAL(15,2) NOT NULL,
    prediction_unit VARCHAR(50) NULL,
    
    -- Confidence
    confidence_score DECIMAL(5,2) NOT NULL, -- 0-100
    
    -- Period
    prediction_period_start DATE NULL,
    prediction_period_end DATE NULL,
    
    -- Actual vs Predicted
    actual_value DECIMAL(15,2) NULL,
    accuracy_score DECIMAL(5,2) NULL,
    
    -- Status
    prediction_status ENUM('pending', 'verified', 'incorrect') DEFAULT 'pending',
    
    -- Additional Data
    prediction_data JSON NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_model_id (model_id),
    INDEX idx_prediction_type (prediction_type),
    INDEX idx_prediction_period_start (prediction_period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES ai_models(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Smart Automations Table
CREATE TABLE IF NOT EXISTS smart_automations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Automation Details
    automation_name VARCHAR(255) NOT NULL,
    automation_description TEXT NULL,
    
    -- Trigger
    trigger_type ENUM('iot_device', 'time_based', 'event_based', 'condition_based', 'ai_based') NOT NULL,
    trigger_config JSON NOT NULL,
    
    -- Action
    action_type ENUM('notification', 'device_control', 'order_creation', 'inventory_adjustment', 'email', 'sms', 'webhook', 'other') NOT NULL,
    action_config JSON NOT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
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
    INDEX idx_trigger_type (trigger_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Automation Execution History Table
CREATE TABLE IF NOT EXISTS automation_execution_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    automation_id BIGINT UNSIGNED NOT NULL,
    
    -- Execution Details
    trigger_data JSON NULL,
    action_data JSON NULL,
    
    -- Result
    execution_status ENUM('success', 'failure', 'partial') NOT NULL,
    execution_message TEXT NULL,
    
    -- Timing
    executed_at DATETIME NOT NULL,
    execution_duration_ms INT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_automation_id (automation_id),
    INDEX idx_execution_status (execution_status),
    INDEX idx_executed_at (executed_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (automation_id) REFERENCES smart_automations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
