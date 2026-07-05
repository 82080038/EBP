-- Migration 009: Advertising Tables
-- Advertising & Monetization (Phase 13 - RESEARCH_36)

-- Create ad_campaigns table
CREATE TABLE IF NOT EXISTS ad_campaigns (
    campaign_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    campaign_code VARCHAR(50) NOT NULL UNIQUE,
    campaign_name VARCHAR(150) NOT NULL,
    campaign_type ENUM('BANNER', 'SPONSORED_PRODUCT', 'FEATURED_SUPPLIER', 'PROMOTIONAL') NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    budget DECIMAL(15,2),
    currency VARCHAR(10) DEFAULT 'IDR',
    targeting_audience JSON,
    targeting_location JSON,
    targeting_cuisine_type JSON,
    status ENUM('DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'PAUSED', 'COMPLETED', 'CANCELLED') DEFAULT 'DRAFT',
    approval_status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_ad_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_ad_tenant (tenant_id),
    INDEX idx_ad_type (campaign_type),
    INDEX idx_ad_status (status),
    INDEX idx_ad_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ad_impressions table
CREATE TABLE IF NOT EXISTS ad_impressions (
    impression_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(50),
    location VARCHAR(100),
    impression_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_impression_campaign FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(campaign_id),
    INDEX idx_impression_campaign (campaign_id),
    INDEX idx_impression_time (impression_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ad_clicks table
CREATE TABLE IF NOT EXISTS ad_clicks (
    click_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    impression_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(50),
    location VARCHAR(100),
    click_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_click_campaign FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(campaign_id),
    CONSTRAINT fk_click_impression FOREIGN KEY (impression_id) REFERENCES ad_impressions(impression_id),
    INDEX idx_click_campaign (campaign_id),
    INDEX idx_click_time (click_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ad_conversions table
CREATE TABLE IF NOT EXISTS ad_conversions (
    conversion_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    click_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
    conversion_type VARCHAR(50),
    conversion_value DECIMAL(10,2),
    conversion_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_conversion_campaign FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(campaign_id),
    CONSTRAINT fk_conversion_click FOREIGN KEY (click_id) REFERENCES ad_clicks(click_id),
    INDEX idx_conversion_campaign (campaign_id),
    INDEX idx_conversion_time (conversion_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ad_analytics table
CREATE TABLE IF NOT EXISTS ad_analytics (
    analytics_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    analytics_date DATE NOT NULL,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    conversions INT DEFAULT 0,
    ctr DECIMAL(5,4),
    conversion_rate DECIMAL(5,4),
    cost_per_impression DECIMAL(10,4),
    cost_per_click DECIMAL(10,4),
    cost_per_conversion DECIMAL(10,4),
    total_spend DECIMAL(15,2),
    revenue_generated DECIMAL(15,2),
    roi DECIMAL(5,4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_analytics_campaign FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(campaign_id),
    INDEX idx_analytics_campaign (campaign_id),
    INDEX idx_analytics_date (analytics_date),
    UNIQUE KEY uk_campaign_date (campaign_id, analytics_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create supplier_ad_placements table
CREATE TABLE IF NOT EXISTS supplier_ad_placements (
    placement_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id BIGINT UNSIGNED NOT NULL,
    campaign_id BIGINT UNSIGNED,
    placement_type ENUM('BANNER', 'SPONSORED_PRODUCT', 'FEATURED_SUPPLIER') NOT NULL,
    placement_name VARCHAR(150),
    description TEXT,
    image_url VARCHAR(255),
    target_url VARCHAR(255),
    position VARCHAR(50),
    status ENUM('DRAFT', 'PENDING_APPROVAL', 'ACTIVE', 'PAUSED', 'EXPIRED', 'REJECTED') DEFAULT 'DRAFT',
    approval_status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    budget DECIMAL(15,2),
    actual_spend DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_placement_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    CONSTRAINT fk_placement_campaign FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(campaign_id),
    INDEX idx_placement_supplier (supplier_id),
    INDEX idx_placement_type (placement_type),
    INDEX idx_placement_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create featured_restaurant_requests table
CREATE TABLE IF NOT EXISTS featured_restaurant_requests (
    request_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('FEATURED_LISTING', 'SPONSORED_SEARCH', 'PROMOTIONAL_BANNER') NOT NULL,
    description TEXT,
    budget DECIMAL(15,2),
    duration_days INT,
    start_date DATE,
    end_date DATE,
    status ENUM('PENDING', 'UNDER_REVIEW', 'APPROVED', 'REJECTED', 'ACTIVE', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
    approval_status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_featured_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_featured_tenant (tenant_id),
    INDEX idx_featured_type (request_type),
    INDEX idx_featured_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_ad_preferences table
CREATE TABLE IF NOT EXISTS user_ad_preferences (
    preference_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    allow_personalized_ads TINYINT(1) DEFAULT 1,
    allow_targeted_ads TINYINT(1) DEFAULT 1,
    ad_categories JSON,
    opted_out_campaigns JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_ad_pref_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_ad_pref_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create data_products table
CREATE TABLE IF NOT EXISTS data_products (
    product_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(50) NOT NULL UNIQUE,
    product_name VARCHAR(150) NOT NULL,
    product_type ENUM('INDUSTRY_INSIGHTS', 'SUPPLIER_LEADS', 'MARKET_TRENDS', 'CUSTOM_ANALYTICS') NOT NULL,
    description TEXT,
    data_source TEXT,
    update_frequency ENUM('DAILY', 'WEEKLY', 'MONTHLY', 'QUARTERLY') DEFAULT 'MONTHLY',
    pricing_model ENUM('SUBSCRIPTION', 'PER_LEAD', 'CUSTOM') NOT NULL,
    base_price DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'IDR',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_data_product_type (product_type),
    INDEX idx_data_product_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create data_product_subscriptions table
CREATE TABLE IF NOT EXISTS data_product_subscriptions (
    subscription_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    subscription_start_date DATE NOT NULL,
    subscription_end_date DATE,
    status ENUM('ACTIVE', 'SUSPENDED', 'CANCELLED', 'EXPIRED') DEFAULT 'ACTIVE',
    price_paid DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'IDR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_data_sub_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    CONSTRAINT fk_data_sub_product FOREIGN KEY (product_id) REFERENCES data_products(product_id),
    INDEX idx_data_sub_tenant (tenant_id),
    INDEX idx_data_sub_product (product_id),
    INDEX idx_data_sub_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default data products
INSERT INTO data_products (product_code, product_name, product_type, description, update_frequency, pricing_model, base_price) VALUES
('IND_INSIGHTS', 'Restaurant Industry Insights', 'INDUSTRY_INSIGHTS', 'Comprehensive industry insights and market analysis', 'MONTHLY', 'SUBSCRIPTION', 99.00),
('SUP_LEADS', 'Supplier Lead Generation', 'SUPPLIER_LEADS', 'Qualified leads for suppliers', 'WEEKLY', 'PER_LEAD', 5.00),
('MKT_TRENDS', 'Market Trend Reports', 'MARKET_TRENDS', 'Detailed market trend analysis and forecasts', 'MONTHLY', 'SUBSCRIPTION', 199.00),
('CUST_ANALYTICS', 'Custom Analytics Dashboard', 'CUSTOM_ANALYTICS', 'Custom-built analytics dashboard for your business', 'DAILY', 'SUBSCRIPTION', 299.00);
