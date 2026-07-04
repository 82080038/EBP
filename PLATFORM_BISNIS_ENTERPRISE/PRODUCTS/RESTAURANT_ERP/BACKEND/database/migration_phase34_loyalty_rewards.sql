-- Migration Phase 34: Loyalty & Rewards
-- Provides comprehensive loyalty program with points, tiers, rewards, and campaigns

-- Loyalty Programs Table
CREATE TABLE IF NOT EXISTS loyalty_programs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Program Details
    program_name VARCHAR(255) NOT NULL,
    program_description TEXT NULL,
    
    -- Points Configuration
    points_per_currency DECIMAL(10,2) DEFAULT 1.00, -- points earned per currency unit spent
    points_per_visit INT DEFAULT 10, -- points earned per visit
    minimum_spend_for_points DECIMAL(15,2) DEFAULT 0.00,
    
    -- Redemption
    points_to_currency_ratio DECIMAL(10,2) DEFAULT 0.01, -- currency value per point
    minimum_points_to_redeem INT DEFAULT 100,
    
    -- Expiration
    points_expiration_days INT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Loyalty Tiers Table
CREATE TABLE IF NOT EXISTS loyalty_tiers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    program_id BIGINT UNSIGNED NOT NULL,
    
    -- Tier Details
    tier_name VARCHAR(100) NOT NULL,
    tier_description TEXT NULL,
    
    -- Requirements
    minimum_points INT NOT NULL,
    minimum_spend DECIMAL(15,2) NOT NULL,
    minimum_visits INT NOT NULL,
    
    -- Benefits
    points_multiplier DECIMAL(3,2) DEFAULT 1.00,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    free_delivery BOOLEAN DEFAULT FALSE,
    priority_seating BOOLEAN DEFAULT FALSE,
    special_offers BOOLEAN DEFAULT FALSE,
    
    -- Display
    tier_color VARCHAR(7) NULL,
    tier_icon VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_program_id (program_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES loyalty_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Loyalty Table
CREATE TABLE IF NOT EXISTS customer_loyalty (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    program_id BIGINT UNSIGNED NOT NULL,
    
    -- Points
    current_points INT DEFAULT 0,
    total_points_earned INT DEFAULT 0,
    total_points_redeemed INT DEFAULT 0,
    
    -- Tier
    current_tier_id BIGINT UNSIGNED NULL,
    
    -- Statistics
    total_visits INT DEFAULT 0,
    total_spend DECIMAL(15,2) DEFAULT 0.00,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    enrolled_at DATETIME NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_program_id (program_id),
    INDEX idx_current_tier_id (current_tier_id),
    INDEX idx_is_active (is_active),
    UNIQUE KEY unique_customer_program (restaurant_id, customer_id, program_id),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES loyalty_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (current_tier_id) REFERENCES loyalty_tiers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Points Transactions Table
CREATE TABLE IF NOT EXISTS points_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    customer_loyalty_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    
    -- Transaction Details
    transaction_type ENUM('earned', 'redeemed', 'expired', 'adjusted', 'bonus', 'penalty') NOT NULL,
    points_amount INT NOT NULL,
    
    -- Reference
    reference_type VARCHAR(50) NULL, -- order, visit, manual, reward, etc.
    reference_id BIGINT UNSIGNED NULL,
    reference_number VARCHAR(50) NULL,
    
    -- Balance
    balance_before INT NOT NULL,
    balance_after INT NOT NULL,
    
    -- Expiration
    expires_at DATETIME NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_loyalty_id (customer_loyalty_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_created_at (created_at),
    INDEX idx_expires_at (expires_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_loyalty_id) REFERENCES customer_loyalty(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rewards Table
CREATE TABLE IF NOT EXISTS rewards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    program_id BIGINT UNSIGNED NOT NULL,
    
    -- Reward Details
    reward_name VARCHAR(255) NOT NULL,
    reward_description TEXT NULL,
    reward_type ENUM('discount', 'free_item', 'free_delivery', 'upgrade', 'other') NOT NULL,
    
    -- Cost
    points_required INT NOT NULL,
    
    -- Value
    discount_percentage DECIMAL(5,2) NULL,
    discount_amount DECIMAL(15,2) NULL,
    free_item_id BIGINT UNSIGNED NULL,
    
    -- Availability
    is_available BOOLEAN DEFAULT TRUE,
    available_from DATE NULL,
    available_until DATE NULL,
    total_quantity INT NULL,
    remaining_quantity INT NULL,
    
    -- Limits
    max_redemptions_per_customer INT NULL,
    max_redemptions_total INT NULL,
    
    -- Display
    image_url VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_program_id (program_id),
    INDEX idx_is_active (is_active),
    INDEX idx_is_available (is_available),
    INDEX idx_sort_order (sort_order),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES loyalty_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reward Redemptions Table
CREATE TABLE IF NOT EXISTS reward_redemptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    reward_id BIGINT UNSIGNED NOT NULL,
    points_transaction_id BIGINT UNSIGNED NOT NULL,
    
    -- Redemption Details
    redemption_code VARCHAR(50) NOT NULL,
    points_used INT NOT NULL,
    
    -- Reference
    order_id BIGINT UNSIGNED NULL,
    
    -- Status
    redemption_status ENUM('pending', 'applied', 'expired', 'cancelled') DEFAULT 'pending',
    
    -- Timing
    redeemed_at DATETIME NOT NULL,
    applied_at DATETIME NULL,
    expired_at DATETIME NULL,
    
    -- Staff
    redeemed_by BIGINT UNSIGNED NULL,
    applied_by BIGINT UNSIGNED NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_reward_id (reward_id),
    INDEX idx_points_transaction_id (points_transaction_id),
    INDEX idx_redemption_code (redemption_code),
    INDEX idx_redemption_status (redemption_status),
    INDEX idx_redeemed_at (redeemed_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE CASCADE,
    FOREIGN KEY (points_transaction_id) REFERENCES points_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (redeemed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (applied_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Loyalty Campaigns Table
CREATE TABLE IF NOT EXISTS loyalty_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Campaign Details
    campaign_name VARCHAR(255) NOT NULL,
    campaign_description TEXT NULL,
    
    -- Type
    campaign_type ENUM('points_multiplier', 'bonus_points', 'tier_upgrade', 'special_reward', 'other') NOT NULL,
    
    -- Configuration
    campaign_config JSON NULL,
    
    -- Timing
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    
    -- Targeting
    target_tier_id BIGINT UNSIGNED NULL,
    target_customer_tag_id BIGINT UNSIGNED NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_campaign_type (campaign_type),
    INDEX idx_is_active (is_active),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate redemption code trigger
DELIMITER //
CREATE TRIGGER generate_redemption_code
BEFORE INSERT ON reward_redemptions
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(redemption_code, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM reward_redemptions
    WHERE restaurant_id = NEW.restaurant_id
    AND DATE(redeemed_at) = CURDATE();
    
    SET NEW.redemption_code = CONCAT('RWD', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Insert default loyalty program for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_loyalty_program
AFTER INSERT ON restaurants
FOR EACH NEW
BEGIN
    -- Create default program
    INSERT INTO loyalty_programs (restaurant_id, program_name, program_description, points_per_currency, points_per_visit, minimum_spend_for_points, points_to_currency_ratio, minimum_points_to_redeem, points_expiration_days, is_active)
    VALUES (NEW.id, 'Standard Loyalty', 'Earn points on every purchase and visit', 1.00, 10, 0.00, 0.01, 100, 365, TRUE);
    
    SET @program_id = LAST_INSERT_ID();
    
    -- Create default tiers
    INSERT INTO loyalty_tiers (restaurant_id, program_id, tier_name, tier_description, minimum_points, minimum_spend, minimum_visits, points_multiplier, discount_percentage, free_delivery, priority_seating, special_offers, tier_color, sort_order, is_active)
    VALUES 
    (NEW.id, @program_id, 'Bronze', 'Entry level tier', 0, 0, 0, 1.00, 0.00, FALSE, FALSE, FALSE, '#CD7F32', 1, TRUE),
    (NEW.id, @program_id, 'Silver', 'Mid-level tier', 1000, 500000, 10, 1.25, 5.00, FALSE, TRUE, TRUE, '#C0C0C0', 2, TRUE),
    (NEW.id, @program_id, 'Gold', 'Premium tier', 5000, 2000000, 50, 1.50, 10.00, TRUE, TRUE, TRUE, '#FFD700', 3, TRUE);
END//
DELIMITER ;
