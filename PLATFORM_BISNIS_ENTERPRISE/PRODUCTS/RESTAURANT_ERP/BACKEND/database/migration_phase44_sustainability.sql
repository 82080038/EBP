-- Migration Phase 44: Sustainability Management
-- Provides comprehensive sustainability tracking with carbon footprint, waste management, and environmental metrics

-- Sustainability Metrics Table
CREATE TABLE IF NOT EXISTS sustainability_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    metric_date DATE NOT NULL,
    metric_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    
    -- Carbon Footprint
    carbon_footprint_kg DECIMAL(15,2) DEFAULT 0.00,
    carbon_footprint_per_revenue DECIMAL(10,2) DEFAULT 0.00,
    
    -- Energy
    energy_consumption_kwh DECIMAL(15,2) DEFAULT 0.00,
    energy_cost DECIMAL(15,2) DEFAULT 0.00,
    renewable_energy_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Water
    water_consumption_liters DECIMAL(15,2) DEFAULT 0.00,
    water_cost DECIMAL(15,2) DEFAULT 0.00,
    water_recycled_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Waste
    total_waste_kg DECIMAL(15,2) DEFAULT 0.00,
    food_waste_kg DECIMAL(15,2) DEFAULT 0.00,
    recycled_waste_kg DECIMAL(15,2) DEFAULT 0.00,
    composted_waste_kg DECIMAL(15,2) DEFAULT 0.00,
    recycling_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Packaging
    packaging_waste_kg DECIMAL(15,2) DEFAULT 0.00,
    sustainable_packaging_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Transport
    delivery_distance_km DECIMAL(15,2) DEFAULT 0.00,
    transport_emissions_kg DECIMAL(15,2) DEFAULT 0.00,
    
    -- Scores
    sustainability_score INT DEFAULT 0, -- 0-100
    sustainability_rating ENUM('excellent', 'good', 'satisfactory', 'needs_improvement', 'poor') NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_metric_date (metric_date),
    INDEX idx_metric_type (metric_type),
    INDEX idx_sustainability_score (sustainability_score),
    UNIQUE KEY unique_date (restaurant_id, metric_date, metric_type),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waste Tracking Table
CREATE TABLE IF NOT EXISTS waste_tracking (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Waste Details
    waste_date DATE NOT NULL,
    waste_type ENUM('food', 'packaging', 'general', 'hazardous', 'organic', 'recyclable') NOT NULL,
    waste_category VARCHAR(100) NULL,
    
    -- Quantity
    waste_quantity DECIMAL(15,2) NOT NULL,
    waste_unit ENUM('kg', 'liters', 'units', 'cubic_meters') DEFAULT 'kg',
    
    -- Disposal
    disposal_method ENUM('landfill', 'recycling', 'composting', 'donation', 'incineration', 'other') NOT NULL,
    disposal_cost DECIMAL(15,2) DEFAULT 0.00,
    
    -- Source
    waste_source ENUM('kitchen', 'dining', 'bar', 'storage', 'delivery', 'other') NOT NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Staff
    recorded_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_waste_date (waste_date),
    INDEX idx_waste_type (waste_type),
    INDEX idx_disposal_method (disposal_method),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sustainability Goals Table
CREATE TABLE IF NOT EXISTS sustainability_goals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Goal Details
    goal_name VARCHAR(255) NOT NULL,
    goal_description TEXT NULL,
    
    -- Goal Type
    goal_category ENUM('carbon', 'energy', 'water', 'waste', 'packaging', 'transport', 'overall') NOT NULL,
    
    -- Period
    goal_type ENUM('monthly', 'quarterly', 'yearly') NOT NULL,
    goal_period_start DATE NOT NULL,
    goal_period_end DATE NOT NULL,
    
    -- Targets
    target_value DECIMAL(15,2) NOT NULL,
    target_unit VARCHAR(50) NOT NULL,
    target_comparison ENUM('less_than', 'greater_than', 'equal_to') NOT NULL,
    
    -- Actuals
    actual_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Achievement
    achievement_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Status
    goal_status ENUM('active', 'achieved', 'missed', 'exceeded') DEFAULT 'active',
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_goal_category (goal_category),
    INDEX idx_goal_type (goal_type),
    INDEX idx_goal_status (goal_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sustainability Certifications Table
CREATE TABLE IF NOT EXISTS sustainability_certifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Certification Details
    certification_name VARCHAR(255) NOT NULL,
    certification_type ENUM('organic', 'fair_trade', 'sustainable_seafood', 'green_restaurant', 'carbon_neutral', 'other') NOT NULL,
    issuing_organization VARCHAR(255) NOT NULL,
    
    -- Dates
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    
    -- Status
    certification_status ENUM('active', 'expired', 'suspended', 'revoked') DEFAULT 'active',
    
    -- Documents
    certificate_number VARCHAR(100) NULL,
    certificate_document_url VARCHAR(255) NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_certification_type (certification_type),
    INDEX idx_certification_status (certification_status),
    INDEX idx_expiry_date (expiry_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sustainability Alerts Table
CREATE TABLE IF NOT EXISTS sustainability_alerts (
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

-- Sustainability Alert History Table
CREATE TABLE IF NOT EXISTS sustainability_alert_history (
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
    FOREIGN KEY (alert_id) REFERENCES sustainability_alerts(id) ON DELETE CASCADE,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
