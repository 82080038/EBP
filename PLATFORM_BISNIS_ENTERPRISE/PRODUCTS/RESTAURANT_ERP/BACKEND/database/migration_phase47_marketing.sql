-- Migration Phase 47: Marketing & Branding
-- Provides comprehensive marketing management with campaigns, promotions, and brand management

-- Marketing Campaigns Table
CREATE TABLE IF NOT EXISTS marketing_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Campaign Details
    campaign_name VARCHAR(255) NOT NULL,
    campaign_description TEXT NULL,
    campaign_type ENUM('promotion', 'brand_awareness', 'customer_acquisition', 'retention', 'loyalty', 'seasonal', 'event', 'other') NOT NULL,
    
    -- Period
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    
    -- Budget
    budget_amount DECIMAL(15,2) NOT NULL,
    actual_spent DECIMAL(15,2) DEFAULT 0.00,
    
    -- Channels
    marketing_channels JSON NULL, -- email, social_media, sms, in_app, print, radio, tv, other
    
    -- Targeting
    target_audience JSON NULL,
    target_segments JSON NULL,
    
    -- Status
    campaign_status ENUM('draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled') DEFAULT 'draft',
    
    -- Metrics
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    conversions INT DEFAULT 0,
    conversion_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- ROI
    revenue_generated DECIMAL(15,2) DEFAULT 0.00,
    roi_percentage DECIMAL(10,2) DEFAULT 0.00,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    managed_by BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_campaign_type (campaign_type),
    INDEX idx_campaign_status (campaign_status),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (managed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Promotions Table
CREATE TABLE IF NOT EXISTS promotions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Promotion Details
    promotion_name VARCHAR(255) NOT NULL,
    promotion_description TEXT NULL,
    promotion_code VARCHAR(50) NULL,
    
    -- Type
    promotion_type ENUM('discount', 'buy_x_get_y', 'percentage_off', 'fixed_amount', 'free_item', 'bundle', 'loyalty_points', 'cashback', 'other') NOT NULL,
    
    -- Value
    discount_value DECIMAL(15,2) NOT NULL,
    discount_type ENUM('percentage', 'fixed', 'other') NOT NULL,
    
    -- Applicability
    applies_to ENUM('all_items', 'specific_items', 'categories', 'orders', 'delivery', 'dine_in', 'takeaway') NOT NULL,
    applicable_items JSON NULL,
    applicable_categories JSON NULL,
    
    -- Conditions
    minimum_order_value DECIMAL(15,2) NULL,
    minimum_quantity INT NULL,
    maximum_discount DECIMAL(15,2) NULL,
    
    -- Period
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    
    -- Usage
    usage_limit INT NULL,
    usage_count INT DEFAULT 0,
    usage_limit_per_customer INT NULL,
    
    -- Status
    promotion_status ENUM('draft', 'active', 'paused', 'expired', 'cancelled') DEFAULT 'draft',
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_promotion_code (promotion_code),
    INDEX idx_promotion_type (promotion_type),
    INDEX idx_promotion_status (promotion_status),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Brand Assets Table
CREATE TABLE IF NOT EXISTS brand_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Asset Details
    asset_name VARCHAR(255) NOT NULL,
    asset_type ENUM('logo', 'image', 'video', 'document', 'font', 'color_palette', 'guidelines', 'other') NOT NULL,
    asset_category VARCHAR(100) NULL,
    
    -- File
    file_url VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    file_format VARCHAR(50) NOT NULL,
    
    -- Usage
    usage_context VARCHAR(255) NULL,
    dimensions VARCHAR(50) NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Staff
    uploaded_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_asset_type (asset_type),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Social Media Posts Table
CREATE TABLE IF NOT EXISTS social_media_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Post Details
    post_title VARCHAR(255) NULL,
    post_content TEXT NOT NULL,
    
    -- Platform
    platform ENUM('facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube', 'other') NOT NULL,
    
    -- Media
    media_urls JSON NULL,
    
    -- Scheduling
    scheduled_date DATETIME NULL,
    posted_date DATETIME NULL,
    
    -- Status
    post_status ENUM('draft', 'scheduled', 'posted', 'failed', 'deleted') DEFAULT 'draft',
    
    -- Metrics
    likes INT DEFAULT 0,
    comments INT DEFAULT 0,
    shares INT DEFAULT 0,
    views INT DEFAULT 0,
    engagement_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- External
    external_post_id VARCHAR(255) NULL,
    external_post_url VARCHAR(500) NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_platform (platform),
    INDEX idx_post_status (post_status),
    INDEX idx_scheduled_date (scheduled_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Marketing Analytics Table
CREATE TABLE IF NOT EXISTS marketing_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    metric_date DATE NOT NULL,
    metric_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    
    -- Campaign Metrics
    active_campaigns INT DEFAULT 0,
    total_impressions INT DEFAULT 0,
    total_clicks INT DEFAULT 0,
    total_conversions INT DEFAULT 0,
    average_ctr DECIMAL(5,2) DEFAULT 0.00,
    average_conversion_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Promotion Metrics
    active_promotions INT DEFAULT 0,
    promotion_redemptions INT DEFAULT 0,
    promotion_savings DECIMAL(15,2) DEFAULT 0.00,
    
    -- Social Media Metrics
    total_posts INT DEFAULT 0,
    total_engagement INT DEFAULT 0,
    average_engagement_rate DECIMAL(5,2) DEFAULT 0.00,
    follower_growth INT DEFAULT 0,
    
    -- Financial Metrics
    total_spend DECIMAL(15,2) DEFAULT 0.00,
    revenue_generated DECIMAL(15,2) DEFAULT 0.00,
    roi_percentage DECIMAL(10,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_metric_date (metric_date),
    INDEX idx_metric_type (metric_type),
    UNIQUE KEY unique_date (restaurant_id, metric_date, metric_type),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
