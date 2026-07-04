-- Migration Phase 50: Ghost Kitchen
-- Provides ghost kitchen management with multi-brand support, delivery integration, and virtual brand analytics

-- Virtual Brands Table
CREATE TABLE IF NOT EXISTS virtual_brands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Brand Details
    brand_name VARCHAR(255) NOT NULL,
    brand_code VARCHAR(50) NOT NULL,
    brand_description TEXT NULL,
    
    -- Branding
    brand_logo_url VARCHAR(255) NULL,
    brand_color_hex VARCHAR(7) NULL,
    
    -- Concept
    cuisine_type VARCHAR(100) NOT NULL,
    price_range ENUM('budget', 'mid_range', 'premium', 'luxury') NOT NULL,
    
    -- Status
    brand_status ENUM('draft', 'active', 'paused', 'discontinued') DEFAULT 'draft',
    
    -- Target
    target_audience TEXT NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_brand_code (brand_code),
    INDEX idx_brand_status (brand_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Virtual Brand Menu Items Table
CREATE TABLE IF NOT EXISTS virtual_brand_menu_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    virtual_brand_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Item Details
    item_name VARCHAR(255) NOT NULL,
    item_description TEXT NULL,
    
    -- Pricing
    price DECIMAL(15,2) NOT NULL,
    
    -- Branding
    item_image_url VARCHAR(255) NULL,
    
    -- Availability
    is_available BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_virtual_brand_id (virtual_brand_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    INDEX idx_is_available (is_available),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (virtual_brand_id) REFERENCES virtual_brands(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Delivery Platforms Table
CREATE TABLE IF NOT EXISTS delivery_platforms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Platform Details
    platform_name VARCHAR(255) NOT NULL,
    platform_type ENUM('food_delivery', 'marketplace', 'custom', 'other') NOT NULL,
    
    -- Integration
    api_key VARCHAR(255) NULL,
    api_secret VARCHAR(255) NULL,
    webhook_url VARCHAR(255) NULL,
    
    -- Configuration
    platform_config JSON NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_platform_type (platform_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Virtual Brand Platform Mapping Table
CREATE TABLE IF NOT EXISTS virtual_brand_platforms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    virtual_brand_id BIGINT UNSIGNED NOT NULL,
    delivery_platform_id BIGINT UNSIGNED NOT NULL,
    
    -- Mapping Details
    external_brand_id VARCHAR(100) NULL,
    external_store_id VARCHAR(100) NULL,
    
    -- Configuration
    platform_menu_config JSON NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_virtual_brand_id (virtual_brand_id),
    INDEX idx_delivery_platform_id (delivery_platform_id),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (virtual_brand_id) REFERENCES virtual_brands(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_platform_id) REFERENCES delivery_platforms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ghost Kitchen Analytics Table
CREATE TABLE IF NOT EXISTS ghost_kitchen_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    virtual_brand_id BIGINT UNSIGNED NULL,
    
    -- Period
    metric_date DATE NOT NULL,
    metric_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    
    -- Order Metrics
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(15,2) DEFAULT 0.00,
    average_order_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Platform Breakdown
    platform_orders JSON NULL,
    platform_revenue JSON NULL,
    
    -- Performance
    preparation_time_avg DECIMAL(10,2) DEFAULT 0.00,
    on_time_delivery_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Top Items
    top_items JSON NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_virtual_brand_id (virtual_brand_id),
    INDEX idx_metric_date (metric_date),
    INDEX idx_metric_type (metric_type),
    UNIQUE KEY unique_date (restaurant_id, virtual_brand_id, metric_date, metric_type),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (virtual_brand_id) REFERENCES virtual_brands(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate virtual brand code trigger
DELIMITER //
CREATE TRIGGER generate_virtual_brand_code
BEFORE INSERT ON virtual_brands
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(brand_code, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM virtual_brands
    WHERE restaurant_id = NEW.restaurant_id;
    
    SET NEW.brand_code = CONCAT('VBR', LPAD(next_number, 6, '0'));
END//
DELIMITER ;
