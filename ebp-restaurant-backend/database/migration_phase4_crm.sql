-- Migration for Phase 4: CRM Module
-- This script adds tables for customer profiles, loyalty programs, and preferences

-- Create customers table
CREATE TABLE IF NOT EXISTS customers (
    customer_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    customer_code VARCHAR(50) NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(100),
    date_of_birth DATE,
    gender ENUM('MALE', 'FEMALE', 'OTHER'),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'Indonesia',
    preferred_branch_id BIGINT UNSIGNED,
    loyalty_points INT DEFAULT 0,
    loyalty_tier ENUM('BRONZE', 'SILVER', 'GOLD', 'PLATINUM') DEFAULT 'BRONZE',
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(15,2) DEFAULT 0,
    last_order_date DATE,
    status ENUM('ACTIVE', 'INACTIVE', 'BLACKLISTED') DEFAULT 'ACTIVE',
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (customer_id),
    UNIQUE KEY idx_customers_tenant_code (tenant_id, customer_code),
    KEY idx_customers_tenant_id (tenant_id),
    KEY idx_customers_phone (phone),
    KEY idx_customers_email (email),
    KEY idx_customers_loyalty_tier (loyalty_tier),
    KEY idx_customers_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (preferred_branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customer preferences table
CREATE TABLE IF NOT EXISTS customer_preferences (
    preference_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    preference_type VARCHAR(50) NOT NULL,
    preference_value TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (preference_id),
    KEY idx_customer_preferences_customer_id (customer_id),
    KEY idx_customer_preferences_type (preference_type),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create loyalty transactions table
CREATE TABLE IF NOT EXISTS loyalty_transactions (
    transaction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    transaction_type ENUM('EARN', 'REDEEM', 'ADJUST', 'EXPIRE') NOT NULL,
    points INT NOT NULL,
    order_id BIGINT UNSIGNED,
    reference_number VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (transaction_id),
    KEY idx_loyalty_transactions_tenant_id (tenant_id),
    KEY idx_loyalty_transactions_customer_id (customer_id),
    KEY idx_loyalty_transactions_type (transaction_type),
    KEY idx_loyalty_transactions_order_id (order_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create loyalty rewards table
CREATE TABLE IF NOT EXISTS loyalty_rewards (
    reward_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    reward_code VARCHAR(50) NOT NULL,
    reward_name VARCHAR(150) NOT NULL,
    reward_type ENUM('DISCOUNT', 'FREE_ITEM', 'POINTS_MULTIPLIER', 'VOUCHER') NOT NULL,
    points_required INT NOT NULL,
    discount_percentage DECIMAL(5,2),
    discount_amount DECIMAL(10,2),
    free_product_id BIGINT UNSIGNED,
    multiplier INT DEFAULT 1,
    description TEXT,
    valid_from DATE,
    valid_until DATE,
    max_redemptions INT,
    current_redemptions INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE', 'EXPIRED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (reward_id),
    UNIQUE KEY idx_loyalty_rewards_tenant_code (tenant_id, reward_code),
    KEY idx_loyalty_rewards_tenant_id (tenant_id),
    KEY idx_loyalty_rewards_type (reward_type),
    KEY idx_loyalty_rewards_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customer visits table
CREATE TABLE IF NOT EXISTS customer_visits (
    visit_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    visit_date DATE NOT NULL,
    order_count INT DEFAULT 1,
    total_spent DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (visit_id),
    KEY idx_customer_visits_customer_id (customer_id),
    KEY idx_customer_visits_branch_id (branch_id),
    KEY idx_customer_visits_date (visit_date),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
