-- Migration 011: Loyalty Management Tables
-- Customer Loyalty Program
-- Description: Add tables for customer loyalty points, rewards, and tier tracking

-- =====================================================
-- Table 1: loyalty_points
-- =====================================================

CREATE TABLE IF NOT EXISTS loyalty_points (
    loyalty_point_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    points_earned INT DEFAULT 0,
    points_redeemed INT DEFAULT 0,
    transaction_type ENUM('EARNED', 'REDEEMED', 'ADJUSTED') NOT NULL,
    reference_id BIGINT UNSIGNED NULL,
    reference_type VARCHAR(50) NULL,
    notes TEXT NULL,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_loyalty_points_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_loyalty_points_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    
    INDEX idx_loyalty_points_tenant (tenant_id),
    INDEX idx_loyalty_points_user (user_id),
    INDEX idx_loyalty_points_type (transaction_type),
    INDEX idx_loyalty_points_created_at (created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Customer loyalty point transactions';

-- =====================================================
-- Table 2: loyalty_rewards
-- =====================================================

CREATE TABLE IF NOT EXISTS loyalty_rewards (
    reward_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    reward_code VARCHAR(50) UNIQUE NOT NULL,
    reward_name VARCHAR(150) NOT NULL,
    reward_name_en VARCHAR(150) NULL,
    reward_description TEXT NULL,
    points_required INT NOT NULL,
    reward_type ENUM('DISCOUNT', 'FREE_ITEM', 'UPGRADE', 'EXPERIENCE') NOT NULL,
    discount_percentage DECIMAL(5,2) NULL,
    discount_amount DECIMAL(10,2) NULL,
    status ENUM('ACTIVE', 'INACTIVE', 'EXPIRED') DEFAULT 'ACTIVE',
    valid_from DATE NULL,
    valid_until DATE NULL,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    CONSTRAINT fk_loyalty_rewards_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    
    INDEX idx_loyalty_rewards_tenant (tenant_id),
    INDEX idx_loyalty_rewards_status (status),
    INDEX idx_loyalty_rewards_code (reward_code),
    INDEX idx_loyalty_rewards_validity (valid_from, valid_until)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Available loyalty rewards';

-- =====================================================
-- Table 3: customer_loyalty
-- =====================================================

CREATE TABLE IF NOT EXISTS customer_loyalty (
    customer_loyalty_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    total_points INT DEFAULT 0,
    current_tier ENUM('BRONZE', 'SILVER', 'GOLD', 'PLATINUM') DEFAULT 'BRONZE',
    tier_progress INT DEFAULT 0,
    tier_points_required INT DEFAULT 100,
    points_earned_lifetime INT DEFAULT 0,
    points_redeemed_lifetime INT DEFAULT 0,
    last_tier_upgrade DATE NULL,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    CONSTRAINT fk_customer_loyalty_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_loyalty_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    
    INDEX idx_customer_loyalty_tenant (tenant_id),
    INDEX idx_customer_loyalty_user (user_id),
    INDEX idx_customer_loyalty_tier (current_tier),
    UNIQUE KEY uk_customer_loyalty (tenant_id, user_id)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Customer loyalty status and tier tracking';

-- =====================================================
-- Default Data: Sample Loyalty Rewards
-- =====================================================

-- Welcome reward for new customers
INSERT INTO loyalty_rewards (tenant_id, reward_code, reward_name, reward_name_en, reward_description, points_required, reward_type, status) VALUES
(1, 'WELCOME_BONUS', 'Bonus Selamat Datang', 'Welcome Bonus', 'Poin bonus untuk pelanggan baru', 100, 'DISCOUNT', 'ACTIVE'),
(1, 'BIRTHDAY_BONUS', 'Bonus Ulang Tahun', 'Birthday Bonus', 'Poin bonus spesial ulang tahun', 200, 'FREE_ITEM', 'ACTIVE'),
(1, 'REFERRAL_BONUS', 'Bonus Referral', 'Referral Bonus', 'Poin bonus untuk referral pelanggan baru', 150, 'DISCOUNT', 'ACTIVE');

-- =====================================================
-- Rollback Script (for manual rollback if needed)
-- =====================================================

-- DROP TABLE IF EXISTS customer_loyalty;
-- DROP TABLE IF EXISTS loyalty_rewards;
-- DROP TABLE IF EXISTS loyalty_points;
