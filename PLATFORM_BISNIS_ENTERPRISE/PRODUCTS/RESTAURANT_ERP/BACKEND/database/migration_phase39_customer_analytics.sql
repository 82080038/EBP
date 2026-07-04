-- Migration Phase 39: Customer Analytics
-- Provides comprehensive customer analytics with segmentation, behavior analysis, and customer lifetime value

-- Customer Segments Table
CREATE TABLE IF NOT EXISTS customer_segments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Segment Details
    segment_name VARCHAR(255) NOT NULL,
    segment_description TEXT NULL,
    
    -- Criteria
    segment_criteria JSON NULL, -- rules for segment membership
    
    -- Metrics
    customer_count INT DEFAULT 0,
    average_lifetime_value DECIMAL(15,2) DEFAULT 0.00,
    average_order_frequency DECIMAL(10,2) DEFAULT 0.00,
    
    -- Display
    segment_color VARCHAR(7) NULL,
    sort_order INT DEFAULT 0,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Segment Assignments Table
CREATE TABLE IF NOT EXISTS customer_segment_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    segment_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Assignment Details
    assigned_at DATETIME NOT NULL,
    assigned_by ENUM('manual', 'automatic') DEFAULT 'automatic',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_customer_id (customer_id),
    INDEX idx_segment_id (segment_id),
    INDEX idx_restaurant_id (restaurant_id),
    UNIQUE KEY unique_customer_segment (customer_id, segment_id),
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (segment_id) REFERENCES customer_segments(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Behavior Table
CREATE TABLE IF NOT EXISTS customer_behavior (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    
    -- Behavior Metrics
    total_orders INT DEFAULT 0,
    total_spend DECIMAL(15,2) DEFAULT 0.00,
    average_order_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Frequency
    days_since_last_visit INT NULL,
    average_days_between_visits DECIMAL(10,2) NULL,
    visit_frequency ENUM('daily', 'weekly', 'bi_weekly', 'monthly', 'quarterly', 'rarely') NULL,
    
    -- Preferences
    preferred_order_type ENUM('dine_in', 'takeaway', 'delivery', 'mixed') NULL,
    preferred_time_slot ENUM('breakfast', 'lunch', 'dinner', 'late_night', 'mixed') NULL,
    
    -- Loyalty
    loyalty_score INT DEFAULT 0, -- 0-100
    churn_risk ENUM('low', 'medium', 'high') NULL,
    
    -- Lifetime Value
    customer_lifetime_value DECIMAL(15,2) DEFAULT 0.00,
    predicted_lifetime_value DECIMAL(15,2) NULL,
    
    -- Timestamps
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_loyalty_score (loyalty_score),
    INDEX idx_churn_risk (churn_risk),
    UNIQUE KEY unique_customer_restaurant (customer_id, restaurant_id),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Journey Table
CREATE TABLE IF NOT EXISTS customer_journey (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    
    -- Journey Stage
    journey_stage ENUM('awareness', 'consideration', 'first_purchase', 'repeat_purchase', 'loyal', 'advocate', 'dormant', 'churned') NOT NULL,
    
    -- Stage Details
    stage_entered_at DATETIME NOT NULL,
    stage_duration_days INT NULL,
    
    -- Touchpoints
    touchpoint_count INT DEFAULT 0,
    last_touchpoint VARCHAR(100) NULL,
    
    -- Conversion
    conversion_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_journey_stage (journey_stage),
    INDEX idx_stage_entered_at (stage_entered_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Cohorts Table
CREATE TABLE IF NOT EXISTS customer_cohorts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Cohort Details
    cohort_name VARCHAR(255) NOT NULL,
    cohort_description TEXT NULL,
    
    -- Cohort Definition
    cohort_type ENUM('acquisition_month', 'acquisition_quarter', 'acquisition_year', 'segment', 'custom') NOT NULL,
    cohort_criteria JSON NULL,
    
    -- Metrics
    cohort_size INT DEFAULT 0,
    retention_rate DECIMAL(5,2) DEFAULT 0.00,
    average_lifetime_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Period
    cohort_start_date DATE NOT NULL,
    cohort_end_date DATE NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_cohort_type (cohort_type),
    INDEX idx_cohort_start_date (cohort_start_date),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Cohort Data Table
CREATE TABLE IF NOT EXISTS customer_cohort_data (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cohort_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    period_number INT NOT NULL, -- months since cohort start
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Metrics
    active_customers INT DEFAULT 0,
    retention_rate DECIMAL(5,2) DEFAULT 0.00,
    churn_rate DECIMAL(5,2) DEFAULT 0.00,
    average_revenue DECIMAL(15,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_cohort_id (cohort_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_period_number (period_number),
    UNIQUE KEY unique_cohort_period (cohort_id, period_number),
    
    FOREIGN KEY (cohort_id) REFERENCES customer_cohorts(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default customer segments for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_customer_segments
AFTER INSERT ON restaurants
FOR EACH NEW
BEGIN
    -- VIP Customers
    INSERT INTO customer_segments (restaurant_id, segment_name, segment_description, segment_criteria, segment_color, sort_order, is_active)
    VALUES (NEW.id, 'VIP Customers', 'High-value customers with frequent visits', '{"min_lifetime_value": 1000000, "min_orders": 50}', '#FFD700', 1, TRUE);
    
    -- Regular Customers
    INSERT INTO customer_segments (restaurant_id, segment_name, segment_description, segment_criteria, segment_color, sort_order, is_active)
    VALUES (NEW.id, 'Regular Customers', 'Customers with consistent visits', '{"min_orders": 10, "max_days_since_visit": 30}', '#4CAF50', 2, TRUE);
    
    -- New Customers
    INSERT INTO customer_segments (restaurant_id, segment_name, segment_description, segment_criteria, segment_color, sort_order, is_active)
    VALUES (NEW.id, 'New Customers', 'Recently acquired customers', '{"max_orders": 3, "max_days_since_first_order": 30}', '#2196F3', 3, TRUE);
    
    -- At-Risk Customers
    INSERT INTO customer_segments (restaurant_id, segment_name, segment_description, segment_criteria, segment_color, sort_order, is_active)
    VALUES (NEW.id, 'At-Risk Customers', 'Customers showing signs of churn', '{"min_days_since_visit": 60, "min_orders": 5}', '#FF9800', 4, TRUE);
    
    -- Churned Customers
    INSERT INTO customer_segments (restaurant_id, segment_name, segment_description, segment_criteria, segment_color, sort_order, is_active)
    VALUES (NEW.id, 'Churned Customers', 'Customers who have stopped visiting', '{"min_days_since_visit": 90, "min_orders": 5}', '#F44336', 5, TRUE);
END//
DELIMITER ;
