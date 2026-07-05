-- Create beta_programs table
CREATE TABLE IF NOT EXISTS beta_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(100) NOT NULL,
    program_description TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    max_participants INT,
    current_participants INT DEFAULT 0,
    status ENUM('planning', 'recruiting', 'active', 'completed', 'cancelled') DEFAULT 'planning',
    incentives JSON,
    requirements JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_start_date (start_date)
);

-- Create beta_participants table
CREATE TABLE IF NOT EXISTS beta_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    beta_program_id INT NOT NULL,
    tenant_id INT NOT NULL,
    participant_type ENUM('early_adopter', 'industry_expert', 'partner', 'invited') NOT NULL,
    status ENUM('invited', 'accepted', 'active', 'completed', 'declined', 'removed') DEFAULT 'invited',
    joined_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    feedback_score INT,
    feedback_text TEXT,
    incentives_claimed JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_beta_program_id (beta_program_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create geographic_expansions table
CREATE TABLE IF NOT EXISTS geographic_expansions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expansion_name VARCHAR(100) NOT NULL,
    target_country VARCHAR(100) NOT NULL,
    target_city VARCHAR(100) NOT NULL,
    target_region VARCHAR(100),
    expansion_stage ENUM('research', 'planning', 'preparation', 'launch', 'growth', 'mature') DEFAULT 'research',
    target_customers INT,
    current_customers INT DEFAULT 0,
    launch_date DATE,
    investment_budget DECIMAL(15,2),
    actual_spend DECIMAL(15,2),
    roi DECIMAL(5,2),
    status ENUM('planned', 'in_progress', 'completed', 'paused', 'cancelled') DEFAULT 'planned',
    challenges TEXT,
    lessons_learned TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_target_country (target_country),
    INDEX idx_target_city (target_city),
    INDEX idx_expansion_stage (expansion_stage),
    INDEX idx_status (status)
);

-- Create growth_metrics table
CREATE TABLE IF NOT EXISTS growth_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_date DATE NOT NULL,
    metric_type ENUM('acquisition', 'activation', 'engagement', 'retention', 'revenue') NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,2) NOT NULL,
    target_value DECIMAL(15,2),
    segment VARCHAR(50),
    channel VARCHAR(50),
    additional_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_date (metric_date),
    INDEX idx_metric_type (metric_type),
    INDEX idx_metric_name (metric_name)
);

-- Create referral_programs table
CREATE TABLE IF NOT EXISTS referral_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(100) NOT NULL,
    program_type ENUM('restaurant_referral', 'consumer_referral', 'staff_referral') NOT NULL,
    reward_type ENUM('credit', 'discount', 'cash', 'points') NOT NULL,
    reward_value DECIMAL(10,2) NOT NULL,
    referrer_reward_value DECIMAL(10,2),
    max_rewards_per_user INT,
    program_start_date DATE NOT NULL,
    program_end_date DATE,
    status ENUM('active', 'paused', 'ended') DEFAULT 'active',
    terms TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_program_type (program_type),
    INDEX idx_status (status)
);

-- Create referrals table
CREATE TABLE IF NOT EXISTS referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referral_program_id INT NOT NULL,
    referrer_id INT NOT NULL,
    referee_id INT,
    referral_code VARCHAR(50) NOT NULL,
    status ENUM('pending', 'completed', 'rewarded', 'expired') DEFAULT 'pending',
    completed_at TIMESTAMP NULL,
    rewarded_at TIMESTAMP NULL,
    reward_claimed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_referral_program_id (referral_program_id),
    INDEX idx_referrer_id (referrer_id),
    INDEX idx_referee_id (referee_id),
    INDEX idx_referral_code (referral_code),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create viral_campaigns table
CREATE TABLE IF NOT EXISTS viral_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_name VARCHAR(100) NOT NULL,
    campaign_type ENUM('social_share', 'challenge', 'contest', 'giveaway') NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    target_metric VARCHAR(50) NOT NULL,
    target_value DECIMAL(15,2),
    current_value DECIMAL(15,2) DEFAULT 0,
    status ENUM('planned', 'active', 'completed', 'cancelled') DEFAULT 'planned',
    budget DECIMAL(15,2),
    spend DECIMAL(15,2),
    roi DECIMAL(5,2),
    viral_coefficient DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_campaign_type (campaign_type),
    INDEX idx_status (status),
    INDEX idx_start_date (start_date)
);

-- Create network_effects table
CREATE TABLE IF NOT EXISTS network_effects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    effect_type ENUM('restaurant_to_restaurant', 'consumer_to_restaurant', 'restaurant_to_consumer', 'consumer_to_consumer') NOT NULL,
    effect_name VARCHAR(100) NOT NULL,
    description TEXT,
    activation_threshold INT,
    current_value INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    measurement_frequency_days INT DEFAULT 7,
    last_measured DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_effect_type (effect_type),
    INDEX idx_is_active (is_active)
);

-- Insert default referral program
INSERT INTO referral_programs (program_name, program_type, reward_type, reward_value, referrer_reward_value, max_rewards_per_user, program_start_date, terms) VALUES
('Restaurant Referral Program', 'restaurant_referral', 'credit', 500.00, 500.00, 10, CURDATE(), 'Refer a restaurant and both get $500 credit when they sign up for a paid plan.'),
('Consumer Referral Program', 'consumer_referral', 'discount', 10.00, 5.00, 50, CURDATE(), 'Refer a friend and get $10 discount, they get $5 discount on first order.');

-- Insert default network effects
INSERT INTO network_effects (effect_type, effect_name, description, activation_threshold) VALUES
('restaurant_to_restaurant', 'Supplier Marketplace', 'Restaurants sharing supplier information improves marketplace value', 50),
('consumer_to_restaurant', 'Restaurant Discovery', 'More consumers using app increases restaurant visibility', 100),
('restaurant_to_consumer', 'Menu Recommendations', 'More restaurants provide better menu recommendations', 20),
('consumer_to_consumer', 'Social Features', 'More consumers enable social sharing and reviews', 200);
