-- Migration 008: Launch Infrastructure Tables
-- Launch Strategy & Growth (Phase 12 - RESEARCH_35)

-- Create beta_program_participants table
CREATE TABLE IF NOT EXISTS beta_program_participants (
    participant_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    participant_type ENUM('EARLY_ADOPTER', 'INDUSTRY_EXPERT', 'PARTNER', 'EMPLOYEE') NOT NULL,
    participant_name VARCHAR(150),
    participant_email VARCHAR(100),
    participant_phone VARCHAR(50),
    company_name VARCHAR(150),
    business_type VARCHAR(50),
    status ENUM('INVITED', 'ACCEPTED', 'ACTIVE', 'COMPLETED', 'WITHDRAWN') DEFAULT 'INVITED',
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    feedback_score INT,
    feedback_count INT DEFAULT 0,
    incentive_value DECIMAL(10,2),
    incentive_status ENUM('PENDING', 'EARNED', 'PAID', 'FORFEITED') DEFAULT 'PENDING',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_beta_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_beta_tenant (tenant_id),
    INDEX idx_beta_type (participant_type),
    INDEX idx_beta_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create beta_feedback table
CREATE TABLE IF NOT EXISTS beta_feedback (
    feedback_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    participant_id BIGINT UNSIGNED NOT NULL,
    feedback_category ENUM('FEATURE_REQUEST', 'BUG_REPORT', 'UX_IMPROVEMENT', 'PERFORMANCE', 'GENERAL') NOT NULL,
    feedback_subject VARCHAR(200),
    feedback_description TEXT,
    severity ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
    status ENUM('NEW', 'REVIEWING', 'IMPLEMENTED', 'DECLINED', 'DEFERRED') DEFAULT 'NEW',
    assigned_to BIGINT UNSIGNED,
    priority INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_feedback_participant FOREIGN KEY (participant_id) REFERENCES beta_program_participants(participant_id),
    INDEX idx_feedback_participant (participant_id),
    INDEX idx_feedback_category (feedback_category),
    INDEX idx_feedback_status (status),
    INDEX idx_feedback_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create referral_programs table
CREATE TABLE IF NOT EXISTS referral_programs (
    program_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_code VARCHAR(50) NOT NULL UNIQUE,
    program_name VARCHAR(150) NOT NULL,
    program_type ENUM('RESTAURANT_REFERRAL', 'CONSUMER_REFERRAL', 'PARTNER_REFERRAL') NOT NULL,
    description TEXT,
    referrer_reward_type ENUM('CREDIT', 'DISCOUNT', 'CASH', 'POINTS') NOT NULL,
    referrer_reward_value DECIMAL(10,2) NOT NULL,
    referee_reward_type ENUM('CREDIT', 'DISCOUNT', 'CASH', 'POINTS') NOT NULL,
    referee_reward_value DECIMAL(10,2) NOT NULL,
    max_rewards_per_referrer INT,
    min_referee_purchase DECIMAL(10,2),
    program_start_date DATE NOT NULL,
    program_end_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    terms_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_program_type (program_type),
    INDEX idx_program_active (is_active),
    INDEX idx_program_dates (program_start_date, program_end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create referral_transactions table
CREATE TABLE IF NOT EXISTS referral_transactions (
    transaction_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id BIGINT UNSIGNED NOT NULL,
    referrer_id BIGINT UNSIGNED NOT NULL,
    referee_id BIGINT UNSIGNED,
    referral_code VARCHAR(50) NOT NULL,
    referral_status ENUM('PENDING', 'CONVERTED', 'COMPLETED', 'EXPIRED', 'CANCELLED') DEFAULT 'PENDING',
    conversion_date TIMESTAMP NULL,
    purchase_amount DECIMAL(10,2),
    referrer_reward_earned DECIMAL(10,2),
    referee_reward_earned DECIMAL(10,2),
    referrer_reward_status ENUM('PENDING', 'EARNED', 'PAID', 'FORFEITED') DEFAULT 'PENDING',
    referee_reward_status ENUM('PENDING', 'EARNED', 'PAID', 'FORFEITED') DEFAULT 'PENDING',
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_referral_program FOREIGN KEY (program_id) REFERENCES referral_programs(program_id),
    INDEX idx_referral_program (program_id),
    INDEX idx_referral_referrer (referrer_id),
    INDEX idx_referral_referee (referee_id),
    INDEX idx_referral_code (referral_code),
    INDEX idx_referral_status (referral_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create viral_campaigns table
CREATE TABLE IF NOT EXISTS viral_campaigns (
    campaign_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_code VARCHAR(50) NOT NULL UNIQUE,
    campaign_name VARCHAR(150) NOT NULL,
    campaign_type ENUM('SOCIAL_SHARE', 'CHALLENGE', 'CONTEST', 'GIVEAWAY', 'REFERRAL_BOOST') NOT NULL,
    description TEXT,
    campaign_start_date DATE NOT NULL,
    campaign_end_date DATE,
    target_audience JSON,
    rewards JSON,
    rules TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_campaign_type (campaign_type),
    INDEX idx_campaign_active (is_active),
    INDEX idx_campaign_dates (campaign_start_date, campaign_end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create geographic_expansions table
CREATE TABLE IF NOT EXISTS geographic_expansions (
    expansion_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    target_country VARCHAR(50) NOT NULL,
    target_city VARCHAR(100),
    target_region VARCHAR(100),
    expansion_stage ENUM('RESEARCH', 'PLANNING', 'PREPARATION', 'LAUNCH', 'GROWTH', 'MATURE') DEFAULT 'RESEARCH',
    target_customers INT,
    actual_customers INT DEFAULT 0,
    target_revenue DECIMAL(15,2),
    actual_revenue DECIMAL(15,2) DEFAULT 0,
    roi_percentage DECIMAL(5,2),
    launch_date DATE,
    lessons_learned TEXT,
    status ENUM('PLANNED', 'ACTIVE', 'PAUSED', 'COMPLETED', 'CANCELLED') DEFAULT 'PLANNED',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_expansion_country (target_country),
    INDEX idx_expansion_stage (expansion_stage),
    INDEX idx_expansion_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create growth_metrics table
CREATE TABLE IF NOT EXISTS growth_metrics (
    metric_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    metric_date DATE NOT NULL,
    metric_type ENUM('ACQUISITION', 'ACTIVATION', 'ENGAGEMENT', 'RETENTION', 'REVENUE', 'REFERRAL') NOT NULL,
    metric_value DECIMAL(15,2),
    metric_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_metric_date (metric_date),
    INDEX idx_metric_type (metric_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default referral programs
INSERT INTO referral_programs (program_code, program_name, program_type, description, referrer_reward_type, referrer_reward_value, referee_reward_type, referee_reward_value, max_rewards_per_referrer, min_referee_purchase, program_start_date) VALUES
('REST_REF_2024', 'Restaurant Referral Program 2024', 'RESTAURANT_REFERRAL', 'Refer other restaurants to earn $500 credit for both parties', 'CREDIT', 500.00, 'CREDIT', 500.00, 10, 100.00, CURDATE()),
('CONS_REF_2024', 'Consumer Referral Program 2024', 'CONSUMER_REFERRAL', 'Refer friends to earn $10 discount for referrer, $5 for referee', 'DISCOUNT', 10.00, 'DISCOUNT', 5.00, 50, 20.00, CURDATE());
