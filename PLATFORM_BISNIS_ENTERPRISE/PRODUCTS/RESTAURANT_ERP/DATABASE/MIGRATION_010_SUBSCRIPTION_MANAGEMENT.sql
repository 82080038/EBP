-- Migration 010: Subscription Management Tables
-- Payment Model & Pricing (RESEARCH_39)

-- Create subscription_plans table
CREATE TABLE IF NOT EXISTS subscription_plans (
    plan_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_code VARCHAR(50) NOT NULL UNIQUE,
    plan_name VARCHAR(150) NOT NULL,
    plan_tier ENUM('HOME_BASED', 'SMALL_RESTAURANT', 'REGIONAL_CHAIN', 'NATIONAL_CORPORATION', 'INTERNATIONAL_CORPORATION') NOT NULL,
    business_type ENUM('home_based', 'small_restaurant', 'regional_chain', 'national_corporation', 'international_corporation') NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'IDR',
    billing_cycle ENUM('MONTHLY', 'QUARTERLY', 'ANNUAL') DEFAULT 'MONTHLY',
    annual_discount_percentage DECIMAL(5,2) DEFAULT 0,
    max_locations INT,
    max_users INT,
    max_products INT,
    max_orders_per_month INT,
    included_features JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_plan_tier (plan_tier),
    INDEX idx_plan_business_type (business_type),
    INDEX idx_plan_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tenant_subscriptions table
CREATE TABLE IF NOT EXISTS tenant_subscriptions (
    subscription_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    subscription_code VARCHAR(50) NOT NULL UNIQUE,
    subscription_start_date DATE NOT NULL,
    subscription_end_date DATE,
    billing_cycle ENUM('MONTHLY', 'QUARTERLY', 'ANNUAL') DEFAULT 'MONTHLY',
    status ENUM('TRIAL', 'ACTIVE', 'SUSPENDED', 'CANCELLED', 'EXPIRED') DEFAULT 'TRIAL',
    trial_end_date DATE,
    auto_renew TINYINT(1) DEFAULT 1,
    current_locations INT DEFAULT 1,
    current_users INT DEFAULT 1,
    current_products INT DEFAULT 0,
    current_orders_per_month INT DEFAULT 0,
    base_price DECIMAL(10,2),
    applied_discount DECIMAL(10,2) DEFAULT 0,
    final_price DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'IDR',
    next_billing_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_sub_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    CONSTRAINT fk_sub_plan FOREIGN KEY (plan_id) REFERENCES subscription_plans(plan_id),
    INDEX idx_sub_tenant (tenant_id),
    INDEX idx_sub_plan (plan_id),
    INDEX idx_sub_status (status),
    INDEX idx_sub_next_billing (next_billing_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create subscription_payments table
CREATE TABLE IF NOT EXISTS subscription_payments (
    payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscription_id BIGINT UNSIGNED NOT NULL,
    payment_code VARCHAR(50) NOT NULL UNIQUE,
    payment_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'IDR',
    payment_method ENUM('CREDIT_CARD', 'BANK_TRANSFER', 'E_WALLET', 'QRIS', 'CRYPTO') NOT NULL,
    payment_gateway VARCHAR(50),
    payment_status ENUM('PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'REFUNDED', 'PARTIALLY_REFUNDED') DEFAULT 'PENDING',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    billing_period_start DATE,
    billing_period_end DATE,
    transaction_id VARCHAR(100),
    gateway_response TEXT,
    failure_reason TEXT,
    refunded_amount DECIMAL(10,2) DEFAULT 0,
    refund_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_payment_subscription FOREIGN KEY (subscription_id) REFERENCES tenant_subscriptions(subscription_id),
    INDEX idx_payment_subscription (subscription_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create transaction_fees table
CREATE TABLE IF NOT EXISTS transaction_fees (
    fee_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fee_code VARCHAR(50) NOT NULL UNIQUE,
    fee_name VARCHAR(150) NOT NULL,
    fee_type ENUM('PAYMENT_PROCESSING', 'MARKETPLACE', 'DELIVERY', 'ADDITIONAL') NOT NULL,
    fee_percentage DECIMAL(5,4),
    fixed_fee DECIMAL(10,2),
    fee_description TEXT,
    applies_to ENUM('ALL', 'SPECIFIC_TIER', 'SPECIFIC_REGION') DEFAULT 'ALL',
    tier_applicability JSON,
    region_applicability JSON,
    is_active TINYINT(1) DEFAULT 1,
    effective_date DATE NOT NULL,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_fee_type (fee_type),
    INDEX idx_fee_active (is_active),
    INDEX idx_fee_dates (effective_date, expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create marketplace_fees table
CREATE TABLE IF NOT EXISTS marketplace_fees (
    marketplace_fee_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    transaction_id BIGINT UNSIGNED,
    transaction_type ENUM('SUPPLIER_PURCHASE', 'STAFF_BOOKING', 'SERVICE_FEE') NOT NULL,
    fee_percentage DECIMAL(5,4),
    fixed_fee DECIMAL(10,2),
    total_fee DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'IDR',
    fee_status ENUM('PENDING', 'COLLECTED', 'WAIVED', 'REFUNDED') DEFAULT 'PENDING',
    collected_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT idx_mf_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_mf_tenant (tenant_id),
    INDEX idx_mf_transaction (transaction_id),
    INDEX idx_mf_type (transaction_type),
    INDEX idx_mf_status (fee_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create add_on_services table
CREATE TABLE IF NOT EXISTS add_on_services (
    add_on_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    add_on_code VARCHAR(50) NOT NULL UNIQUE,
    add_on_name VARCHAR(150) NOT NULL,
    add_on_category ENUM('AI_FEATURES', 'ADVANCED_ANALYTICS', 'PRIORITY_SUPPORT', 'CUSTOM_INTEGRATIONS', 'ADDITIONAL_STORAGE', 'WHITE_LABEL') NOT NULL,
    description TEXT,
    pricing_model ENUM('MONTHLY', 'QUARTERLY', 'ANNUAL', 'ONE_TIME') DEFAULT 'MONTHLY',
    base_price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'IDR',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_add_on_category (add_on_category),
    INDEX idx_add_on_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tenant_add_ons table
CREATE TABLE IF NOT EXISTS tenant_add_ons (
    tenant_add_on_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    add_on_id BIGINT UNSIGNED NOT NULL,
    subscription_start_date DATE NOT NULL,
    subscription_end_date DATE,
    status ENUM('ACTIVE', 'SUSPENDED', 'CANCELLED', 'EXPIRED') DEFAULT 'ACTIVE',
    auto_renew TINYINT(1) DEFAULT 1,
    price_paid DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'IDR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_tao_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    CONSTRAINT fk_tao_add_on FOREIGN KEY (add_on_id) REFERENCES add_on_services(add_on_id),
    INDEX idx_tao_tenant (tenant_id),
    INDEX idx_tao_add_on (add_on_id),
    INDEX idx_tao_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create geographic_pricing_adjustments table
CREATE TABLE IF NOT EXISTS geographic_pricing_adjustments (
    adjustment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country VARCHAR(50) NOT NULL,
    region VARCHAR(100),
    adjustment_percentage DECIMAL(5,2) NOT NULL,
    adjustment_type ENUM('INCREASE', 'DECREASE') NOT NULL,
    effective_date DATE NOT NULL,
    expiry_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_geo_country (country),
    INDEX idx_geo_region (region),
    INDEX idx_geo_active (is_active),
    INDEX idx_geo_dates (effective_date, expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subscription plans
INSERT INTO subscription_plans (plan_code, plan_name, plan_tier, business_type, description, base_price, billing_cycle, max_locations, max_users, max_products, max_orders_per_month) VALUES
-- Home-based
('HB_FREE', 'Home Based Free', 'HOME_BASED', 'home_based', 'Free plan for home-based businesses', 0.00, 'MONTHLY', 1, 1, 10, 100),
('HB_BASIC', 'Home Based Basic', 'HOME_BASED', 'home_based', 'Basic plan for home-based businesses', 29.00, 'MONTHLY', 1, 2, 50, 500),

-- Small Restaurant
('SR_STARTER', 'Small Restaurant Starter', 'SMALL_RESTAURANT', 'small_restaurant', 'Starter plan for small restaurants', 49.00, 'MONTHLY', 1, 3, 100, 1000),
('SR_STANDARD', 'Small Restaurant Standard', 'SMALL_RESTAURANT', 'small_restaurant', 'Standard plan for small restaurants', 99.00, 'MONTHLY', 1, 5, 200, 5000),
('SR_PROFESSIONAL', 'Small Restaurant Professional', 'SMALL_RESTAURANT', 'small_restaurant', 'Professional plan for small restaurants', 249.00, 'MONTHLY', 2, 10, 500, 10000),

-- Regional Chain
('RC_GROWTH', 'Regional Chain Growth', 'REGIONAL_CHAIN', 'regional_chain', 'Growth plan for regional chains', 149.00, 'MONTHLY', 5, 20, 1000, 50000),
('RC_SCALE', 'Regional Chain Scale', 'REGIONAL_CHAIN', 'regional_chain', 'Scale plan for regional chains', 349.00, 'MONTHLY', 10, 50, 2000, 100000),

-- National Corporation
('NC_ENTERPRISE', 'National Corporation Enterprise', 'NATIONAL_CORPORATION', 'national_corporation', 'Enterprise plan for national corporations', 499.00, 'MONTHLY', 25, 100, 5000, 250000),
('NC_PREMIUM', 'National Corporation Premium', 'NATIONAL_CORPORATION', 'national_corporation', 'Premium plan for national corporations', 999.00, 'MONTHLY', 50, 200, 10000, 500000),

-- International Corporation
('IC_GLOBAL', 'International Corporation Global', 'INTERNATIONAL_CORPORATION', 'international_corporation', 'Global plan for international corporations', 1499.00, 'MONTHLY', 100, 500, 20000, 1000000);

-- Insert default add-on services
INSERT INTO add_on_services (add_on_code, add_on_name, add_on_category, description, pricing_model, base_price) VALUES
('AI_ANALYTICS_BASIC', 'AI Analytics Basic', 'AI_FEATURES', 'Basic AI-powered analytics and insights', 'MONTHLY', 50.00),
('AI_ANALYTICS_ADVANCED', 'AI Analytics Advanced', 'AI_FEATURES', 'Advanced AI-powered analytics with predictive capabilities', 'MONTHLY', 200.00),
('ADVANCED_ANALYTICS', 'Advanced Analytics', 'ADVANCED_ANALYTICS', 'Advanced business intelligence and reporting', 'MONTHLY', 30.00),
('PRIORITY_SUPPORT_STANDARD', 'Priority Support Standard', 'PRIORITY_SUPPORT', 'Standard priority support with 24h response time', 'MONTHLY', 50.00),
('PRIORITY_SUPPORT_PREMIUM', 'Priority Support Premium', 'PRIORITY_SUPPORT', 'Premium priority support with 4h response time', 'MONTHLY', 150.00),
('CUSTOM_INTEGRATION', 'Custom Integration', 'CUSTOM_INTEGRATIONS', 'Custom third-party system integration', 'ONE_TIME', 500.00),
('ADDITIONAL_STORAGE', 'Additional Storage', 'ADDITIONAL_STORAGE', 'Additional 100GB storage space', 'MONTHLY', 20.00),
('WHITE_LABEL', 'White Label', 'WHITE_LABEL', 'White-label customization option', 'MONTHLY', 100.00);

-- Insert default transaction fees
INSERT INTO transaction_fees (fee_code, fee_name, fee_type, fee_percentage, fixed_fee, fee_description, effective_date) VALUES
('PAYMENT_PROCESSING', 'Payment Processing Fee', 'PAYMENT_PROCESSING', 0.0150, 0.00, '1.5% payment processing fee for all transactions', CURDATE()),
('MARKETPLACE_SUPPLIER', 'Marketplace Supplier Fee', 'MARKETPLACE', 0.0300, 0.00, '3% marketplace fee for supplier transactions', CURDATE()),
('MARKETPLACE_STAFF', 'Marketplace Staff Fee', 'MARKETPLACE', 0.0500, 0.00, '5% marketplace fee for staff bookings', CURDATE()),
('DELIVERY_PLATFORM', 'Delivery Platform Fee', 'DELIVERY', 0.1000, 0.00, '10% delivery platform fee (revenue share)', CURDATE());

-- Insert default geographic pricing adjustments
INSERT INTO geographic_pricing_adjustments (country, region, adjustment_percentage, adjustment_type, effective_date) VALUES
('Indonesia', NULL, 0.00, 'INCREASE', CURDATE()),
('Singapore', NULL, 20.00, 'INCREASE', CURDATE()),
('Malaysia', NULL, 20.00, 'INCREASE', CURDATE()),
('Thailand', NULL, 20.00, 'INCREASE', CURDATE()),
('Vietnam', NULL, 20.00, 'INCREASE', CURDATE()),
('Philippines', NULL, 20.00, 'INCREASE', CURDATE()),
('Australia', NULL, 30.00, 'INCREASE', CURDATE()),
('Japan', NULL, 30.00, 'INCREASE', CURDATE()),
('South Korea', NULL, 30.00, 'INCREASE', CURDATE()),
('United Kingdom', NULL, 50.00, 'INCREASE', CURDATE()),
('Germany', NULL, 50.00, 'INCREASE', CURDATE()),
('France', NULL, 50.00, 'INCREASE', CURDATE()),
('United States', NULL, 60.00, 'INCREASE', CURDATE()),
('Canada', NULL, 60.00, 'INCREASE', CURDATE());
