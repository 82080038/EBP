-- Migration Phase 43: Procurement Analytics
-- Provides comprehensive procurement analytics with spend analysis, supplier performance, and cost tracking

-- Procurement Spend Table
CREATE TABLE IF NOT EXISTS procurement_spend (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    period_type ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Spend Metrics
    total_orders INT DEFAULT 0,
    total_spend DECIMAL(15,2) DEFAULT 0.00,
    average_order_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- By Supplier
    unique_suppliers INT DEFAULT 0,
    top_supplier_id BIGINT UNSIGNED NULL,
    top_supplier_spend DECIMAL(15,2) DEFAULT 0.00,
    
    -- By Category
    food_spend DECIMAL(15,2) DEFAULT 0.00,
    beverage_spend DECIMAL(15,2) DEFAULT 0.00,
    equipment_spend DECIMAL(15,2) DEFAULT 0.00,
    other_spend DECIMAL(15,2) DEFAULT 0.00,
    
    -- Savings
    discount_savings DECIMAL(15,2) DEFAULT 0.00,
    negotiated_savings DECIMAL(15,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_period_type (period_type),
    INDEX idx_period_start (period_start),
    UNIQUE KEY unique_period (restaurant_id, period_type, period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier Spend Table
CREATE TABLE IF NOT EXISTS supplier_spend (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    period_type ENUM('monthly', 'quarterly', 'yearly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Spend Metrics
    total_orders INT DEFAULT 0,
    total_spend DECIMAL(15,2) DEFAULT 0.00,
    average_order_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Performance
    on_time_delivery_rate DECIMAL(5,2) DEFAULT 0.00,
    quality_score DECIMAL(3,2) DEFAULT 0.00,
    
    -- Share
    spend_share DECIMAL(5,2) DEFAULT 0.00,
    
    -- Rank
    spend_rank INT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_period_type (period_type),
    INDEX idx_period_start (period_start),
    UNIQUE KEY unique_supplier_period (restaurant_id, supplier_id, period_type, period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Category Spend Table
CREATE TABLE IF NOT EXISTS category_spend (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    supplier_type ENUM('food', 'beverage', 'equipment', 'packaging', 'services', 'other') NOT NULL,
    
    -- Period
    period_type ENUM('monthly', 'quarterly', 'yearly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Spend Metrics
    total_spend DECIMAL(15,2) DEFAULT 0.00,
    order_count INT DEFAULT 0,
    
    -- Trends
    previous_spend DECIMAL(15,2) DEFAULT 0.00,
    percentage_change DECIMAL(10,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_supplier_type (supplier_type),
    INDEX idx_period_type (period_type),
    INDEX idx_period_start (period_start),
    UNIQUE KEY unique_category_period (restaurant_id, supplier_type, period_type, period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Procurement Targets Table
CREATE TABLE IF NOT EXISTS procurement_targets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Target Details
    target_name VARCHAR(255) NOT NULL,
    target_description TEXT NULL,
    
    -- Target Type
    target_category ENUM('spend', 'savings', 'supplier_diversity', 'quality', 'delivery') NOT NULL,
    
    -- Period
    target_type ENUM('monthly', 'quarterly', 'yearly') NOT NULL,
    target_period_start DATE NOT NULL,
    target_period_end DATE NOT NULL,
    
    -- Targets
    target_value DECIMAL(15,2) NOT NULL,
    target_comparison ENUM('less_than', 'greater_than', 'equal_to') NOT NULL,
    
    -- Actuals
    actual_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Achievement
    achievement_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Status
    target_status ENUM('active', 'achieved', 'missed', 'exceeded') DEFAULT 'active',
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_target_category (target_category),
    INDEX idx_target_type (target_type),
    INDEX idx_target_status (target_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cost Variance Table
CREATE TABLE IF NOT EXISTS cost_variance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    variance_date DATE NOT NULL,
    
    -- Expected vs Actual
    expected_cost DECIMAL(15,2) NOT NULL,
    actual_cost DECIMAL(15,2) NOT NULL,
    variance_amount DECIMAL(15,2) NOT NULL,
    variance_percentage DECIMAL(10,2) NOT NULL,
    
    -- Reason
    variance_reason VARCHAR(255) NULL,
    
    -- Supplier
    supplier_id BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    INDEX idx_variance_date (variance_date),
    INDEX idx_supplier_id (supplier_id),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
