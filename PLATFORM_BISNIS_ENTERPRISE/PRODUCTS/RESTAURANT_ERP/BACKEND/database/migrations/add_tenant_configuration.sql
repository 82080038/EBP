-- Create tenant_configurations table
CREATE TABLE IF NOT EXISTS tenant_configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    business_type ENUM('home_based', 'small_restaurant', 'regional_chain', 'national_corporation', 'international_corporation') DEFAULT 'small_restaurant',
    physical_presence ENUM('no_building', 'home_kitchen', 'food_truck', 'stall', 'cafe', 'restaurant', 'hotel', 'international_facility') DEFAULT 'restaurant',
    cuisine_type ENUM('traditional', 'international', 'fusion') DEFAULT 'traditional',
    halal_type ENUM('halal_only', 'non_halal', 'mixed') DEFAULT 'halal_only',
    target_market ENUM('mass_market', 'niche', 'premium', 'luxury') DEFAULT 'mass_market',
    menu_complexity ENUM('single_item', 'limited', 'moderate', 'extensive') DEFAULT 'moderate',
    product_mix ENUM('food_only', 'beverage_only', 'food_beverage', 'food_non_food') DEFAULT 'food_beverage',
    enabled_features JSON,
    pricing_tier VARCHAR(50) DEFAULT 'starter',
    onboarding_completed BOOLEAN DEFAULT FALSE,
    onboarding_step INT DEFAULT 0,
    configuration_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_business_type (business_type),
    INDEX idx_pricing_tier (pricing_tier)
) ENGINE=InnoDB;

-- Create feature_modules table
CREATE TABLE IF NOT EXISTS feature_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_code VARCHAR(50) NOT NULL UNIQUE,
    module_name VARCHAR(100) NOT NULL,
    module_category VARCHAR(50) NOT NULL,
    description TEXT,
    dependencies JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_code (module_code),
    INDEX idx_module_category (module_category)
);

-- Create tenant_feature_modules table (junction table)
CREATE TABLE IF NOT EXISTS tenant_feature_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    module_id INT NOT NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    configuration JSON,
    enabled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_module_id (module_id),
    UNIQUE KEY unique_tenant_module (tenant_id, module_id)
) ENGINE=InnoDB;

-- Create business_type_pricing table
CREATE TABLE IF NOT EXISTS business_type_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_type ENUM('home_based', 'small_restaurant', 'regional_chain', 'national_corporation', 'international_corporation') NOT NULL,
    pricing_tier VARCHAR(50) NOT NULL,
    monthly_price DECIMAL(10,2) NOT NULL,
    features_included JSON,
    max_locations INT,
    max_users INT,
    max_inventory_items INT,
    api_access BOOLEAN DEFAULT FALSE,
    priority_support BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_business_tier (business_type, pricing_tier),
    INDEX idx_business_type (business_type),
    INDEX idx_pricing_tier (pricing_tier)
);

-- Create onboarding_templates table
CREATE TABLE IF NOT EXISTS onboarding_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_type ENUM('home_based', 'small_restaurant', 'regional_chain', 'national_corporation', 'international_corporation') NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    steps JSON NOT NULL,
    estimated_duration_minutes INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_business_type (business_type)
);

-- Insert default feature modules
INSERT INTO feature_modules (module_code, module_name, module_category, description, dependencies) VALUES
('pos', 'Point of Sale', 'core', 'Basic POS functionality', '[]'),
('inventory', 'Inventory Management', 'core', 'Inventory tracking and management', '[]'),
('menu', 'Menu Management', 'core', 'Menu item and category management', '[]'),
('staff', 'Staff Management', 'core', 'Staff scheduling and management', '[]'),
('reservations', 'Reservation System', 'customer', 'Table reservation management', '[]'),
('loyalty', 'Loyalty Program', 'customer', 'Customer loyalty and rewards', '[]'),
('delivery', 'Delivery Integration', 'operations', 'Delivery platform integration', '[]'),
('analytics', 'Analytics Dashboard', 'reporting', 'Business analytics and reporting', '[]'),
('multi_location', 'Multi-Location', 'enterprise', 'Multi-location management', '[]'),
('api_access', 'API Access', 'enterprise', 'API access for integrations', '[]'),
('kitchen_display', 'Kitchen Display System', 'operations', 'KDS for kitchen operations', '[]'),
('table_management', 'Table Management', 'operations', 'Table and floor management', '[]'),
('procurement', 'Procurement', 'supply_chain', 'Purchase order and supplier management', '[]'),
('franchise', 'Franchise Management', 'enterprise', 'Franchise operations management', '["multi_location"]'),
('ghost_kitchen', 'Ghost Kitchen', 'operations', 'Virtual brand and ghost kitchen management', '[]'),
('sustainability', 'Sustainability', 'reporting', 'Environmental impact tracking', '[]'),
('international', 'International', 'enterprise', 'Multi-currency and multi-language support', '[]'),
('ai_analytics', 'AI Analytics', 'advanced', 'AI-powered analytics and predictions', '["analytics"]'),
('automation', 'Automation', 'advanced', 'Workflow automation and triggers', '[]');

-- Insert default pricing tiers
INSERT INTO business_type_pricing (business_type, pricing_tier, monthly_price, features_included, max_locations, max_users, max_inventory_items, api_access, priority_support) VALUES
('home_based', 'free', 0, '["pos", "inventory", "menu"]', 1, 2, 50, FALSE, FALSE),
('home_based', 'starter', 29, '["pos", "inventory", "menu", "staff"]', 1, 5, 100, FALSE, FALSE),
('small_restaurant', 'starter', 49, '["pos", "inventory", "menu", "staff", "reservations"]', 1, 10, 200, FALSE, FALSE),
('small_restaurant', 'standard', 99, '["pos", "inventory", "menu", "staff", "reservations", "loyalty", "delivery", "analytics"]', 3, 25, 500, FALSE, TRUE),
('small_restaurant', 'professional', 249, '["pos", "inventory", "menu", "staff", "reservations", "loyalty", "delivery", "analytics", "kitchen_display", "table_management", "procurement"]', 10, 50, 1000, TRUE, TRUE),
('regional_chain', 'standard', 149, '["pos", "inventory", "menu", "staff", "reservations", "loyalty", "delivery", "analytics", "multi_location"]', 5, 50, 1000, FALSE, TRUE),
('regional_chain', 'professional', 349, '["pos", "inventory", "menu", "staff", "reservations", "loyalty", "delivery", "analytics", "multi_location", "kitchen_display", "table_management", "procurement", "api_access"]', 15, 100, 2000, TRUE, TRUE),
('national_corporation', 'professional', 499, '["pos", "inventory", "menu", "staff", "reservations", "loyalty", "delivery", "analytics", "multi_location", "kitchen_display", "table_management", "procurement", "api_access", "franchise", "sustainability"]', 50, 200, 5000, TRUE, TRUE),
('national_corporation', 'enterprise', 999, '["pos", "inventory", "menu", "staff", "reservations", "loyalty", "delivery", "analytics", "multi_location", "kitchen_display", "table_management", "procurement", "api_access", "franchise", "sustainability", "international", "ai_analytics"]', 100, 500, 10000, TRUE, TRUE),
('international_corporation', 'enterprise', 1499, '["pos", "inventory", "menu", "staff", "reservations", "loyalty", "delivery", "analytics", "multi_location", "kitchen_display", "table_management", "procurement", "api_access", "franchise", "sustainability", "international", "ai_analytics", "automation"]', 999, 1000, 50000, TRUE, TRUE);

-- Insert onboarding templates
INSERT INTO onboarding_templates (business_type, template_name, steps, estimated_duration_minutes) VALUES
('home_based', 'Home-Based Quick Start', '[{"step": 1, "title": "Account Setup", "duration": 5}, {"step": 2, "title": "Business Info", "duration": 3}, {"step": 3, "title": "Menu Setup", "duration": 10}, {"step": 4, "title": "Inventory Setup", "duration": 5}, {"step": 5, "title": "Staff Setup", "duration": 2}]', 25),
('small_restaurant', 'Restaurant Standard Onboarding', '[{"step": 1, "title": "Account Setup", "duration": 5}, {"step": 2, "title": "Business Info", "duration": 5}, {"step": 3, "title": "Menu Setup", "duration": 15}, {"step": 4, "title": "Inventory Setup", "duration": 10}, {"step": 5, "title": "Staff Setup", "duration": 10}, {"step": 6, "title": "Table Setup", "duration": 5}, {"step": 7, "title": "Payment Setup", "duration": 5}]', 55),
('regional_chain', "Chain Restaurant Onboarding", '[{"step": 1, "title": "Account Setup", "duration": 10}, {"step": 2, "title": "Business Info", "duration": 10}, {"step": 3, "title": "Location Setup", "duration": 15}, {"step": 4, "title": "Menu Setup", "duration": 20}, {"step": 5, "title": "Inventory Setup", "duration": 15}, {"step": 6, "title": "Staff Setup", "duration": 15}, {"step": 7, "title": "Table Setup", "duration": 10}, {"step": 8, "title": "Payment Setup", "duration": 10}, {"step": 9, "title": "Multi-Location Config", "duration": 15}]', 120),
('national_corporation', 'Enterprise Onboarding', '[{"step": 1, "title": "Account Setup", "duration": 15}, {"step": 2, "title": "Business Info", "duration": 15}, {"step": 3, "title": "Location Setup", "duration": 20}, {"step": 4, "title": "Menu Setup", "duration": 30}, {"step": 5, "title": "Inventory Setup", "duration": 20}, {"step": 6, "title": "Staff Setup", "duration": 20}, {"step": 7, "title": "Table Setup", "duration": 15}, {"step": 8, "title": "Payment Setup", "duration": 15}, {"step": 9, "title": "Multi-Location Config", "duration": 20}, {"step": 10, "title": "API Integration", "duration": 30}, {"step": 11, "title": "Custom Configuration", "duration": 30}]', 240),
('international_corporation', 'International Enterprise Onboarding', '[{"step": 1, "title": "Account Setup", "duration": 20}, {"step": 2, "title": "Business Info", "duration": 20}, {"step": 3, "title": "Location Setup", "duration": 30}, {"step": 4, "title": "Menu Setup", "duration": 40}, {"step": 5, "title": "Inventory Setup", "duration": 30}, {"step": 6, "title": "Staff Setup", "duration": 30}, {"step": 7, "title": "Table Setup", "duration": 20}, {"step": 8, "title": "Payment Setup", "duration": 20}, {"step": 9, "title": "Multi-Location Config", "duration": 30}, {"step": 10, "title": "API Integration", "duration": 40}, {"step": 11, "title": "International Config", "duration": 30}, {"step": 12, "title": "Custom Configuration", "duration": 40}]', 360);
