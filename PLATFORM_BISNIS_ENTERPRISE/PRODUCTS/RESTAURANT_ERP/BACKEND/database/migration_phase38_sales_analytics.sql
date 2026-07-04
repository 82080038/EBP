-- Migration Phase 38: Sales Analytics
-- Provides comprehensive sales analytics with revenue tracking, product performance, and sales trends

-- Sales Aggregates Table (daily, weekly, monthly summaries)
CREATE TABLE IF NOT EXISTS sales_aggregates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    aggregate_type ENUM('hourly', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly') NOT NULL,
    period_start DATETIME NOT NULL,
    period_end DATETIME NOT NULL,
    
    -- Sales Metrics
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(15,2) DEFAULT 0.00,
    total_cost DECIMAL(15,2) DEFAULT 0.00,
    gross_profit DECIMAL(15,2) DEFAULT 0.00,
    net_profit DECIMAL(15,2) DEFAULT 0.00,
    
    -- Order Types
    dine_in_orders INT DEFAULT 0,
    dine_in_revenue DECIMAL(15,2) DEFAULT 0.00,
    takeaway_orders INT DEFAULT 0,
    takeaway_revenue DECIMAL(15,2) DEFAULT 0.00,
    delivery_orders INT DEFAULT 0,
    delivery_revenue DECIMAL(15,2) DEFAULT 0.00,
    
    -- Payment Methods
    cash_payments INT DEFAULT 0,
    cash_amount DECIMAL(15,2) DEFAULT 0.00,
    card_payments INT DEFAULT 0,
    card_amount DECIMAL(15,2) DEFAULT 0.00,
    digital_payments INT DEFAULT 0,
    digital_amount DECIMAL(15,2) DEFAULT 0.00,
    
    -- Averages
    average_order_value DECIMAL(15,2) DEFAULT 0.00,
    average_items_per_order DECIMAL(10,2) DEFAULT 0.00,
    
    -- Customers
    unique_customers INT DEFAULT 0,
    returning_customers INT DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_aggregate_type (aggregate_type),
    INDEX idx_period_start (period_start),
    INDEX idx_period_end (period_end),
    UNIQUE KEY unique_aggregate (restaurant_id, aggregate_type, period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Sales Table
CREATE TABLE IF NOT EXISTS product_sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    menu_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    aggregate_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Sales Metrics
    quantity_sold INT DEFAULT 0,
    total_revenue DECIMAL(15,2) DEFAULT 0.00,
    total_cost DECIMAL(15,2) DEFAULT 0.00,
    gross_profit DECIMAL(15,2) DEFAULT 0.00,
    
    -- Orders
    order_count INT DEFAULT 0,
    
    -- Rank
    sales_rank INT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_menu_item_id (menu_item_id),
    INDEX idx_aggregate_type (aggregate_type),
    INDEX idx_period_start (period_start),
    UNIQUE KEY unique_product_period (restaurant_id, menu_item_id, aggregate_type, period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Category Sales Table
CREATE TABLE IF NOT EXISTS category_sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    aggregate_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Sales Metrics
    total_quantity INT DEFAULT 0,
    total_revenue DECIMAL(15,2) DEFAULT 0.00,
    total_cost DECIMAL(15,2) DEFAULT 0.00,
    gross_profit DECIMAL(15,2) DEFAULT 0.00,
    
    -- Orders
    order_count INT DEFAULT 0,
    
    -- Share
    revenue_share DECIMAL(5,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_category_id (category_id),
    INDEX idx_aggregate_type (aggregate_type),
    INDEX idx_period_start (period_start),
    UNIQUE KEY unique_category_period (restaurant_id, category_id, aggregate_type, period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hourly Sales Table
CREATE TABLE IF NOT EXISTS hourly_sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Time
    sale_date DATE NOT NULL,
    sale_hour INT NOT NULL, -- 0-23
    
    -- Sales Metrics
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(15,2) DEFAULT 0.00,
    average_order_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_sale_date (sale_date),
    INDEX idx_sale_hour (sale_hour),
    UNIQUE KEY unique_hour (restaurant_id, sale_date, sale_hour),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Targets Table
CREATE TABLE IF NOT EXISTS sales_targets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Target Details
    target_name VARCHAR(255) NOT NULL,
    target_description TEXT NULL,
    
    -- Period
    target_type ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly') NOT NULL,
    target_period_start DATE NOT NULL,
    target_period_end DATE NOT NULL,
    
    -- Targets
    revenue_target DECIMAL(15,2) NOT NULL,
    order_target INT NULL,
    profit_target DECIMAL(15,2) NULL,
    
    -- Actuals
    actual_revenue DECIMAL(15,2) DEFAULT 0.00,
    actual_orders INT DEFAULT 0,
    actual_profit DECIMAL(15,2) DEFAULT 0.00,
    
    -- Achievement
    revenue_achievement DECIMAL(5,2) DEFAULT 0.00,
    order_achievement DECIMAL(5,2) DEFAULT 0.00,
    profit_achievement DECIMAL(5,2) DEFAULT 0.00,
    
    -- Status
    target_status ENUM('active', 'completed', 'exceeded', 'missed') DEFAULT 'active',
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_target_type (target_type),
    INDEX idx_target_period_start (target_period_start),
    INDEX idx_target_status (target_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Trends Table
CREATE TABLE IF NOT EXISTS sales_trends (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Trend Details
    trend_type ENUM('revenue', 'orders', 'profit', 'aov', 'items_per_order') NOT NULL,
    trend_period ENUM('daily', 'weekly', 'monthly') NOT NULL,
    
    -- Trend Data
    current_value DECIMAL(15,2) NOT NULL,
    previous_value DECIMAL(15,2) NOT NULL,
    percentage_change DECIMAL(10,2) NOT NULL,
    trend_direction ENUM('up', 'down', 'flat') NOT NULL,
    
    -- Period
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Timestamps
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_trend_type (trend_type),
    INDEX idx_trend_period (trend_period),
    INDEX idx_period_start (period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
