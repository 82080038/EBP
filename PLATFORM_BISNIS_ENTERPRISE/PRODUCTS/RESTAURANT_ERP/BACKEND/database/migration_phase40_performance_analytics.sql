-- Migration Phase 40: Performance Analytics
-- Provides comprehensive performance analytics with staff performance, operational metrics, and efficiency tracking

-- Staff Performance Table
CREATE TABLE IF NOT EXISTS staff_performance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    staff_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    period_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Order Metrics
    orders_processed INT DEFAULT 0,
    orders_per_hour DECIMAL(10,2) DEFAULT 0.00,
    
    -- Revenue
    revenue_generated DECIMAL(15,2) DEFAULT 0.00,
    average_order_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Time Metrics
    average_order_time INT DEFAULT 0, -- in seconds
    average_service_time INT DEFAULT 0, -- in seconds
    
    -- Quality
    order_accuracy_rate DECIMAL(5,2) DEFAULT 0.00,
    customer_rating DECIMAL(3,2) DEFAULT 0.00,
    
    -- Tips
    total_tips DECIMAL(15,2) DEFAULT 0.00,
    tip_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_staff_id (staff_id),
    INDEX idx_period_type (period_type),
    INDEX idx_period_start (period_start),
    UNIQUE KEY unique_staff_period (restaurant_id, staff_id, period_type, period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Operational Metrics Table
CREATE TABLE IF NOT EXISTS operational_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    metric_type ENUM('hourly', 'daily', 'weekly', 'monthly') NOT NULL,
    period_start DATETIME NOT NULL,
    period_end DATETIME NOT NULL,
    
    -- Table Performance
    table_turnover_rate DECIMAL(5,2) DEFAULT 0.00,
    average_table_occupancy DECIMAL(5,2) DEFAULT 0.00,
    peak_occupancy_time TIME NULL,
    
    -- Kitchen Performance
    average_prep_time INT DEFAULT 0, -- in seconds
    average_delivery_time INT DEFAULT 0, -- in seconds
    kitchen_efficiency DECIMAL(5,2) DEFAULT 0.00,
    
    -- Service Performance
    average_seating_time INT DEFAULT 0, -- in seconds
    average_billing_time INT DEFAULT 0, -- in seconds
    service_efficiency DECIMAL(5,2) DEFAULT 0.00,
    
    -- Wait Times
    average_wait_time INT DEFAULT 0, -- in seconds
    maximum_wait_time INT DEFAULT 0, -- in seconds
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_metric_type (metric_type),
    INDEX idx_period_start (period_start),
    UNIQUE KEY unique_metric_period (restaurant_id, metric_type, period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Performance Targets Table
CREATE TABLE IF NOT EXISTS performance_targets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Target Details
    target_name VARCHAR(255) NOT NULL,
    target_description TEXT NULL,
    
    -- Target Type
    target_category ENUM('revenue', 'orders', 'service', 'efficiency', 'quality', 'staff') NOT NULL,
    
    -- Period
    target_type ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly') NOT NULL,
    target_period_start DATE NOT NULL,
    target_period_end DATE NOT NULL,
    
    -- Targets
    target_value DECIMAL(15,2) NOT NULL,
    target_comparison ENUM('greater_than', 'less_than', 'equal_to') NOT NULL,
    
    -- Actuals
    actual_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Achievement
    achievement_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Status
    target_status ENUM('active', 'achieved', 'missed', 'exceeded') DEFAULT 'active',
    
    -- Staff
    assigned_to BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_target_category (target_category),
    INDEX idx_target_type (target_type),
    INDEX idx_target_status (target_status),
    INDEX idx_assigned_to (assigned_to),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Performance Alerts Table
CREATE TABLE IF NOT EXISTS performance_alerts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Alert Details
    alert_name VARCHAR(255) NOT NULL,
    alert_description TEXT NULL,
    
    -- Condition
    metric_type VARCHAR(100) NOT NULL,
    condition_type ENUM('greater_than', 'less_than', 'equal_to', 'not_equal_to', 'percentage_change') NOT NULL,
    threshold_value DECIMAL(15,2) NOT NULL,
    
    -- Notification
    notification_channels JSON NULL, -- email, sms, app, webhook
    notification_recipients JSON NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_metric_type (metric_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Performance Alert History Table
CREATE TABLE IF NOT EXISTS performance_alert_history (
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
    FOREIGN KEY (alert_id) REFERENCES performance_alerts(id) ON DELETE CASCADE,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Efficiency Metrics Table
CREATE TABLE IF NOT EXISTS efficiency_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    metric_date DATE NOT NULL,
    
    -- Cost Efficiency
    food_cost_percentage DECIMAL(5,2) DEFAULT 0.00,
    labor_cost_percentage DECIMAL(5,2) DEFAULT 0.00,
    overhead_cost_percentage DECIMAL(5,2) DEFAULT 0.00,
    total_cost_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Revenue Efficiency
    revenue_per_seat DECIMAL(15,2) DEFAULT 0.00,
    revenue_per_hour DECIMAL(15,2) DEFAULT 0.00,
    revenue_per_staff DECIMAL(15,2) DEFAULT 0.00,
    
    -- Operational Efficiency
    table_turnover_rate DECIMAL(5,2) DEFAULT 0.00,
    staff_productivity DECIMAL(10,2) DEFAULT 0.00,
    inventory_turnover DECIMAL(5,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_metric_date (metric_date),
    UNIQUE KEY unique_date (restaurant_id, metric_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
