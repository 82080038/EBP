-- Migration Phase 52: Segment-Specific Features
-- Provides segment-specific configurations and features for different restaurant types (fine dining, casual dining, QSR)

-- Segment Configurations Table
CREATE TABLE IF NOT EXISTS segment_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Segment Details
    segment_type ENUM('fine_dining', 'casual_dining', 'qsr', 'fast_casual', 'cafe', 'bar', 'other') NOT NULL,
    segment_name VARCHAR(255) NOT NULL,
    segment_description TEXT NULL,
    
    -- Configuration
    segment_config JSON NOT NULL,
    
    -- Features
    enabled_features JSON NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_segment_type (segment_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Segment Workflows Table
CREATE TABLE IF NOT EXISTS segment_workflows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    segment_configuration_id BIGINT UNSIGNED NOT NULL,
    
    -- Workflow Details
    workflow_name VARCHAR(255) NOT NULL,
    workflow_type ENUM('seating', 'ordering', 'payment', 'service', 'kitchen', 'cleanup', 'other') NOT NULL,
    
    -- Steps
    workflow_steps JSON NOT NULL,
    
    -- Conditions
    conditions JSON NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_segment_configuration_id (segment_configuration_id),
    INDEX idx_workflow_type (workflow_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (segment_configuration_id) REFERENCES segment_configurations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Segment Analytics Table
CREATE TABLE IF NOT EXISTS segment_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    segment_configuration_id BIGINT UNSIGNED NULL,
    
    -- Period
    metric_date DATE NOT NULL,
    metric_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    
    -- Performance Metrics
    average_ticket_size DECIMAL(15,2) DEFAULT 0.00,
    table_turnover_rate DECIMAL(5,2) DEFAULT 0.00,
    service_time_avg DECIMAL(10,2) DEFAULT 0.00,
    
    -- Customer Metrics
    customer_satisfaction DECIMAL(3,2) DEFAULT 0.00,
    repeat_customer_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Operational Metrics
    labor_cost_percentage DECIMAL(5,2) DEFAULT 0.00,
    food_cost_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Segment-specific
    segment_metrics JSON NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_segment_configuration_id (segment_configuration_id),
    INDEX idx_metric_date (metric_date),
    INDEX idx_metric_type (metric_type),
    UNIQUE KEY unique_date (restaurant_id, segment_configuration_id, metric_date, metric_type),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (segment_configuration_id) REFERENCES segment_configurations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Segment Templates Table
CREATE TABLE IF NOT EXISTS segment_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Template Details
    template_name VARCHAR(255) NOT NULL,
    segment_type ENUM('fine_dining', 'casual_dining', 'qsr', 'fast_casual', 'cafe', 'bar', 'other') NOT NULL,
    
    -- Configuration
    default_config JSON NOT NULL,
    
    -- Features
    default_features JSON NULL,
    
    -- Workflows
    default_workflows JSON NULL,
    
    -- Description
    template_description TEXT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_segment_type (segment_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default segment templates
INSERT INTO segment_templates (template_name, segment_type, default_config, default_features, template_description) VALUES
('Fine Dining Standard', 'fine_dining', '{"service_level": "premium", "table_service": true, "reservations_required": true, "dress_code": "formal"}', '["reservations", "table_service", "wine_list", "multi_course"]', 'Standard configuration for fine dining establishments'),
('Casual Dining Standard', 'casual_dining', '{"service_level": "standard", "table_service": true, "reservations_optional": true, "dress_code": "casual"}', '["table_service", "takeout", "online_ordering"]', 'Standard configuration for casual dining restaurants'),
('QSR Standard', 'qsr', '{"service_level": "self_service", "table_service": false, "reservations": false, "dress_code": "any"}', '["counter_service", "drive_thru", "delivery", "self_ordering"]', 'Standard configuration for quick service restaurants'),
('Fast Casual Standard', 'fast_casual', '{"service_level": "hybrid", "table_service": false, "reservations": false, "dress_code": "casual"}', '["counter_ordering", "table_service", "takeout", "online_ordering"]', 'Standard configuration for fast casual establishments'),
('Cafe Standard', 'cafe', '{"service_level": "standard", "table_service": true, "reservations": false, "dress_code": "casual"}', '["table_service", "takeout", "coffee_bar", "wifi"]', 'Standard configuration for cafe establishments'),
('Bar Standard', 'bar', '{"service_level": "standard", "table_service": true, "reservations": false, "dress_code": "casual"}', '["table_service", "bar_service", "entertainment", "late_night"]', 'Standard configuration for bar establishments');
