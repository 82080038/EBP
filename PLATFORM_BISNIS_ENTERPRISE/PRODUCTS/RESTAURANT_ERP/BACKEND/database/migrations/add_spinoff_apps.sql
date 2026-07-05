-- Create spinoff_apps table
CREATE TABLE IF NOT EXISTS spinoff_apps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(100) NOT NULL,
    app_code VARCHAR(50) NOT NULL UNIQUE,
    app_category ENUM('consumer_facing', 'supplier_facing', 'staff_facing', 'analytics', 'niche', 'international') NOT NULL,
    app_description TEXT,
    target_audience VARCHAR(100),
    problem_solved TEXT,
    market_potential ENUM('high', 'medium', 'low') NOT NULL,
    strategic_fit ENUM('high', 'medium', 'low') NOT NULL,
    feasibility ENUM('high', 'medium', 'low') NOT NULL,
    risk_level ENUM('high', 'medium', 'low') NOT NULL,
    estimated_development_months INT,
    estimated_budget DECIMAL(15,2),
    monetization_model ENUM('subscription', 'transaction', 'freemium', 'advertising', 'marketplace') NOT NULL,
    status ENUM('idea', 'validation', 'development', 'beta', 'launched', 'paused', 'cancelled') DEFAULT 'idea',
    launch_date DATE,
    parent_restaurant_erp_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_app_category (app_category),
    INDEX idx_status (status),
    INDEX idx_market_potential (market_potential)
);

-- Create supplier_marketplace table
CREATE TABLE IF NOT EXISTS supplier_marketplace (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_category VARCHAR(100) NOT NULL,
    product_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    minimum_order_quantity DECIMAL(10,2),
    available_stock DECIMAL(10,2),
    product_images JSON,
    specifications JSON,
    certifications JSON,
    delivery_options JSON,
    rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_category (product_category),
    INDEX idx_is_featured (is_featured),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB;

-- Create marketplace_orders table
CREATE TABLE IF NOT EXISTS marketplace_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(15,2) NOT NULL,
    shipping_address TEXT,
    delivery_date DATE,
    tracking_number VARCHAR(100),
    order_items JSON,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_order_status (order_status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create food_discovery_app table
CREATE TABLE IF NOT EXISTS food_discovery_app (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    discovery_type ENUM('halal_finder', 'food_waste_reduction', 'local_specialties') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    location_lat DECIMAL(10,8),
    location_lng DECIMAL(11,8),
    address VARCHAR(255),
    city VARCHAR(100),
    country VARCHAR(100),
    cuisine_type VARCHAR(100),
    price_range VARCHAR(10),
    halal_certified BOOLEAN DEFAULT FALSE,
    sustainability_score INT,
    images JSON,
    operating_hours JSON,
    contact_info JSON,
    rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_discovery_type (discovery_type),
    INDEX idx_city (city),
    INDEX idx_halal_certified (halal_certified),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB;

-- Create staff_marketplace table
CREATE TABLE IF NOT EXISTS staff_marketplace (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_title VARCHAR(100) NOT NULL,
    job_category ENUM('chef', 'waiter', 'bartender', 'manager', 'cleaner', 'other') NOT NULL,
    skills JSON,
    experience_years INT,
    hourly_rate DECIMAL(10,2),
    availability JSON,
    preferred_locations JSON,
    certifications JSON,
    bio TEXT,
    profile_image VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_job_category (job_category),
    INDEX idx_is_available (is_available),
    INDEX idx_is_verified (is_verified)
) ENGINE=InnoDB;

-- Create staff_gig_bookings table
CREATE TABLE IF NOT EXISTS staff_gig_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tenant_id INT NOT NULL,
    booking_date DATE NOT NULL,
    shift_start TIME NOT NULL,
    shift_end TIME NOT NULL,
    hourly_rate DECIMAL(10,2),
    total_amount DECIMAL(10,2),
    job_description TEXT,
    booking_status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    rating_given_by_staff INT,
    rating_given_by_restaurant INT,
    feedback_by_staff TEXT,
    feedback_by_restaurant TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_booking_date (booking_date),
    INDEX idx_booking_status (booking_status)
) ENGINE=InnoDB;

-- Create spinoff_analytics table
CREATE TABLE IF NOT EXISTS spinoff_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spinoff_app_id INT NOT NULL,
    analytics_date DATE NOT NULL,
    metric_type ENUM('users', 'revenue', 'engagement', 'retention', 'acquisition') NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,2) NOT NULL,
    segment VARCHAR(50),
    additional_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spinoff_app_id) REFERENCES spinoff_apps(id) ON DELETE CASCADE,
    INDEX idx_spinoff_app_id (spinoff_app_id),
    INDEX idx_analytics_date (analytics_date),
    INDEX idx_metric_type (metric_type)
);

-- Create spinoff_milestones table
CREATE TABLE IF NOT EXISTS spinoff_milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spinoff_app_id INT NOT NULL,
    milestone_name VARCHAR(100) NOT NULL,
    milestone_description TEXT,
    target_date DATE NOT NULL,
    actual_date DATE,
    status ENUM('pending', 'in_progress', 'completed', 'delayed') DEFAULT 'pending',
    progress_percentage INT DEFAULT 0,
    owner VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (spinoff_app_id) REFERENCES spinoff_apps(id) ON DELETE CASCADE,
    INDEX idx_spinoff_app_id (spinoff_app_id),
    INDEX idx_target_date (target_date),
    INDEX idx_status (status)
);

-- Insert default spin-off app ideas
INSERT INTO spinoff_apps (app_name, app_code, app_category, app_description, target_audience, problem_solved, market_potential, strategic_fit, feasibility, risk_level, estimated_development_months, estimated_budget, monetization_model) VALUES
('Supplier Marketplace', 'SUPP_MKT', 'supplier_facing', 'B2B marketplace connecting restaurants with suppliers', 'Restaurants and Suppliers', 'Inefficient supplier discovery and ordering', 'high', 'high', 'high', 'medium', 6, 150000.00, 'marketplace'),
('Halal Food Finder', 'HALAL_FOOD', 'consumer_facing', 'App for finding halal-certified restaurants', 'Muslim consumers', 'Difficulty finding halal food options', 'high', 'high', 'high', 'low', 4, 80000.00, 'freemium'),
('Food Waste Reduction', 'FOOD_WASTE', 'consumer_facing', 'App connecting restaurants with consumers for discounted near-expiry food', 'Eco-conscious consumers', 'Food waste in restaurants', 'medium', 'medium', 'high', 'medium', 5, 100000.00, 'transaction'),
('Staff Marketplace', 'STAFF_MKT', 'staff_facing', 'Gig economy platform for restaurant staff', 'Restaurant workers and restaurants', 'Staff shortage and flexible staffing needs', 'high', 'high', 'high', 'medium', 6, 120000.00, 'marketplace'),
('Food Traceability', 'FOOD_TRACE', 'supplier_facing', 'Blockchain-based food traceability system', 'Food industry', 'Food safety and transparency concerns', 'medium', 'medium', 'medium', 'high', 8, 200000.00, 'subscription'),
('Indonesian Food Discovery', 'INDO_FOOD', 'international', 'App for discovering Indonesian food globally', 'International foodies', 'Lack of Indonesian food visibility abroad', 'medium', 'high', 'medium', 'medium', 5, 90000.00, 'freemium');
