-- Create ad_campaigns table
CREATE TABLE IF NOT EXISTS ad_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_name VARCHAR(100) NOT NULL,
    campaign_type ENUM('supplier', 'equipment', 'service', 'restaurant', 'brand') NOT NULL,
    advertiser_type ENUM('supplier', 'restaurant', 'brand') NOT NULL,
    advertiser_id INT NOT NULL,
    ad_format ENUM('banner', 'sponsored_listing', 'product_listing', 'sponsored_content', 'push_notification') NOT NULL,
    targeting_criteria JSON,
    budget DECIMAL(15,2) NOT NULL,
    actual_spend DECIMAL(15,2) DEFAULT 0,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    conversions INT DEFAULT 0,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('draft', 'active', 'paused', 'completed', 'cancelled') DEFAULT 'draft',
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_campaign_type (campaign_type),
    INDEX idx_advertiser_id (advertiser_id),
    INDEX idx_status (status),
    INDEX idx_start_date (start_date)
);

-- Create ad_impressions table
CREATE TABLE IF NOT EXISTS ad_impressions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_campaign_id INT NOT NULL,
    user_id INT,
    tenant_id INT,
    impression_context JSON,
    device_type VARCHAR(50),
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ad_campaign_id (ad_campaign_id),
    INDEX idx_user_id (user_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create ad_clicks table
CREATE TABLE IF NOT EXISTS ad_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_campaign_id INT NOT NULL,
    ad_impression_id INT,
    user_id INT,
    tenant_id INT,
    click_context JSON,
    device_type VARCHAR(50),
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ad_campaign_id (ad_campaign_id),
    INDEX idx_ad_impression_id (ad_impression_id),
    INDEX idx_user_id (user_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create ad_conversions table
CREATE TABLE IF NOT EXISTS ad_conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_campaign_id INT NOT NULL,
    ad_click_id INT,
    user_id INT,
    tenant_id INT,
    conversion_type VARCHAR(50) NOT NULL,
    conversion_value DECIMAL(10,2),
    conversion_context JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ad_campaign_id (ad_campaign_id),
    INDEX idx_ad_click_id (ad_click_id),
    INDEX idx_user_id (user_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_conversion_type (conversion_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create supplier_ad_placements table
CREATE TABLE IF NOT EXISTS supplier_ad_placements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    ad_campaign_id INT NOT NULL,
    placement_type ENUM('banner', 'sponsored_product', 'featured_supplier') NOT NULL,
    placement_position VARCHAR(50),
    target_audience JSON,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'paused', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ad_campaign_id (ad_campaign_id),
    INDEX idx_placement_type (placement_type),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create featured_restaurant_requests table
CREATE TABLE IF NOT EXISTS featured_restaurant_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    request_type ENUM('featured_placement', 'sponsored_recommendation', 'boost') NOT NULL,
    request_details JSON,
    budget DECIMAL(10,2),
    duration_days INT,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_request_type (request_type),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create data_products table
CREATE TABLE IF NOT EXISTS data_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    product_type ENUM('aggregated_insights', 'lead_generation', 'market_report', 'custom_analytics') NOT NULL,
    description TEXT,
    data_source JSON,
    pricing_model ENUM('subscription', 'one_time', 'usage_based') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    availability ENUM('public', 'private', 'custom') DEFAULT 'public',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_type (product_type),
    INDEX idx_availability (availability),
    INDEX idx_is_active (is_active)
);

-- Create data_product_subscriptions table
CREATE TABLE IF NOT EXISTS data_product_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_product_id INT NOT NULL,
    subscriber_id INT NOT NULL,
    subscriber_type ENUM('restaurant', 'supplier', 'brand', 'other') NOT NULL,
    subscription_start_date DATE NOT NULL,
    subscription_end_date DATE,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    access_level JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (data_product_id) REFERENCES data_products(id) ON DELETE CASCADE,
    INDEX idx_data_product_id (data_product_id),
    INDEX idx_subscriber_id (subscriber_id),
    INDEX idx_status (status)
);

-- Create ad_analytics table
CREATE TABLE IF NOT EXISTS ad_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_campaign_id INT NOT NULL,
    analytics_date DATE NOT NULL,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    conversions INT DEFAULT 0,
    spend DECIMAL(10,2) DEFAULT 0,
    ctr DECIMAL(5,2),
    conversion_rate DECIMAL(5,2),
    cpa DECIMAL(10,2),
    additional_metrics JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ad_campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
    UNIQUE KEY unique_campaign_date (ad_campaign_id, analytics_date),
    INDEX idx_analytics_date (analytics_date)
);

-- Create user_ad_preferences table
CREATE TABLE IF NOT EXISTS user_ad_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ad_personalization_enabled BOOLEAN DEFAULT TRUE,
    ad_categories_opted_in JSON,
    ad_categories_opted_out JSON,
    data_sharing_enabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_id (user_id)
) ENGINE=InnoDB;

-- Insert default data products
INSERT INTO data_products (product_name, product_type, description, data_source, pricing_model, price) VALUES
('Restaurant Industry Insights', 'aggregated_insights', 'Aggregated data on restaurant trends, pricing, and performance', '["orders", "menus", "reviews"]', 'subscription', 99.00),
('Supplier Lead Generation', 'lead_generation', 'Qualified leads for suppliers based on restaurant demand', '["inventory", "procurement"]', 'usage_based', 5.00),
('Market Trend Reports', 'market_report', 'Monthly reports on F&B market trends and forecasts', '["orders", "searches", "reviews"]', 'subscription', 199.00),
('Custom Analytics Dashboard', 'custom_analytics', 'Tailored analytics dashboard for specific business needs', '["all"]', 'subscription', 299.00);
