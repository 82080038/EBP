-- Migration Phase 37: Business Intelligence Dashboard
-- Provides comprehensive dashboard configuration, KPI tracking, and data visualization

-- Dashboard Configurations Table
CREATE TABLE IF NOT EXISTS dashboard_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Configuration Details
    dashboard_name VARCHAR(255) NOT NULL,
    dashboard_description TEXT NULL,
    
    -- Layout
    layout_config JSON NULL, -- widget positions and sizes
    
    -- Settings
    is_default BOOLEAN DEFAULT FALSE,
    is_public BOOLEAN DEFAULT FALSE,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_default (is_default),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dashboard Widgets Table
CREATE TABLE IF NOT EXISTS dashboard_widgets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    dashboard_id BIGINT UNSIGNED NULL,
    
    -- Widget Details
    widget_type VARCHAR(100) NOT NULL, -- kpi_card, chart, table, metric, etc.
    widget_name VARCHAR(255) NOT NULL,
    widget_config JSON NULL, -- widget-specific configuration
    
    -- Data Source
    data_source VARCHAR(100) NULL, -- orders, sales, customers, etc.
    data_query TEXT NULL,
    
    -- Display
    position_x INT DEFAULT 0,
    position_y INT DEFAULT 0,
    width INT DEFAULT 4,
    height INT DEFAULT 3,
    
    -- Refresh
    refresh_interval INT DEFAULT 300, -- in seconds
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_dashboard_id (dashboard_id),
    INDEX idx_widget_type (widget_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (dashboard_id) REFERENCES dashboard_configurations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KPI Definitions Table
CREATE TABLE IF NOT EXISTS kpi_definitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- KPI Details
    kpi_code VARCHAR(50) NOT NULL,
    kpi_name VARCHAR(255) NOT NULL,
    kpi_description TEXT NULL,
    
    -- Calculation
    kpi_type ENUM('revenue', 'count', 'percentage', 'average', 'sum', 'custom') NOT NULL,
    calculation_formula TEXT NULL,
    
    -- Data Source
    data_source_table VARCHAR(100) NULL,
    data_source_field VARCHAR(100) NULL,
    
    -- Target
    target_value DECIMAL(15,2) NULL,
    target_comparison ENUM('greater_than', 'less_than', 'equal_to') NULL,
    
    -- Display
    unit VARCHAR(50) NULL,
    decimal_places INT DEFAULT 2,
    icon VARCHAR(255) NULL,
    color VARCHAR(7) NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_kpi_code (kpi_code),
    INDEX idx_kpi_type (kpi_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KPI Values Table (historical KPI data)
CREATE TABLE IF NOT EXISTS kpi_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    kpi_id BIGINT UNSIGNED NOT NULL,
    
    -- Value
    kpi_value DECIMAL(15,2) NOT NULL,
    
    -- Period
    period_type ENUM('hourly', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly') NOT NULL,
    period_start DATETIME NOT NULL,
    period_end DATETIME NOT NULL,
    
    -- Comparison
    previous_value DECIMAL(15,2) NULL,
    percentage_change DECIMAL(10,2) NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_kpi_id (kpi_id),
    INDEX idx_period_type (period_type),
    INDEX idx_period_start (period_start),
    UNIQUE KEY unique_kpi_period (restaurant_id, kpi_id, period_type, period_start),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (kpi_id) REFERENCES kpi_definitions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alert Rules Table
CREATE TABLE IF NOT EXISTS alert_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Alert Details
    alert_name VARCHAR(255) NOT NULL,
    alert_description TEXT NULL,
    
    -- Condition
    kpi_id BIGINT UNSIGNED NOT NULL,
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
    INDEX idx_kpi_id (kpi_id),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (kpi_id) REFERENCES kpi_definitions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alert History Table
CREATE TABLE IF NOT EXISTS alert_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    alert_rule_id BIGINT UNSIGNED NOT NULL,
    kpi_id BIGINT UNSIGNED NOT NULL,
    
    -- Alert Details
    alert_value DECIMAL(15,2) NOT NULL,
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
    INDEX idx_alert_rule_id (alert_rule_id),
    INDEX idx_kpi_id (kpi_id),
    INDEX idx_alert_status (alert_status),
    INDEX idx_triggered_at (triggered_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (alert_rule_id) REFERENCES alert_rules(id) ON DELETE CASCADE,
    FOREIGN KEY (kpi_id) REFERENCES kpi_definitions(id) ON DELETE CASCADE,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default KPI definitions for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_kpi_definitions
AFTER INSERT ON restaurants
FOR EACH NEW
BEGIN
    -- Total Revenue
    INSERT INTO kpi_definitions (restaurant_id, kpi_code, kpi_name, kpi_description, kpi_type, data_source_table, data_source_field, unit, decimal_places, icon, color, is_active)
    VALUES (NEW.id, 'TOTAL_REVENUE', 'Total Revenue', 'Total revenue from all orders', 'revenue', 'orders', 'total_amount', 'IDR', 0, '💰', '#10B981', TRUE);
    
    -- Total Orders
    INSERT INTO kpi_definitions (restaurant_id, kpi_code, kpi_name, kpi_description, kpi_type, data_source_table, data_source_field, unit, decimal_places, icon, color, is_active)
    VALUES (NEW.id, 'TOTAL_ORDERS', 'Total Orders', 'Total number of orders', 'count', 'orders', 'id', 'orders', 0, '📦', '#3B82F6', TRUE);
    
    -- Average Order Value
    INSERT INTO kpi_definitions (restaurant_id, kpi_code, kpi_name, kpi_description, kpi_type, data_source_table, data_source_field, unit, decimal_places, icon, color, is_active)
    VALUES (NEW.id, 'AVG_ORDER_VALUE', 'Average Order Value', 'Average value per order', 'average', 'orders', 'total_amount', 'IDR', 0, '📊', '#8B5CF6', TRUE);
    
    -- Customer Count
    INSERT INTO kpi_definitions (restaurant_id, kpi_code, kpi_name, kpi_description, kpi_type, data_source_table, data_source_field, unit, decimal_places, icon, color, is_active)
    VALUES (NEW.id, 'CUSTOMER_COUNT', 'Customer Count', 'Total number of customers', 'count', 'customers', 'id', 'customers', 0, '👥', '#F59E0B', TRUE);
    
    -- Table Turnover
    INSERT INTO kpi_definitions (restaurant_id, kpi_code, kpi_name, kpi_description, kpi_type, unit, decimal_places, icon, color, is_active)
    VALUES (NEW.id, 'TABLE_TURNOVER', 'Table Turnover', 'Average number of times tables are turned over', 'average', 'turns', 1, '🔄', '#EF4444', TRUE);
END//
DELIMITER ;
