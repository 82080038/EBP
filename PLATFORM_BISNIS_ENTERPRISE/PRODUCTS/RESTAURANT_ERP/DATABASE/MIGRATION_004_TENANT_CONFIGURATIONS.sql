-- Migration 004: Tenant Configurations Table
-- Business Scope & Flexibility (Phase 10 - RESEARCH_33)

-- Create tenant_configurations table
CREATE TABLE IF NOT EXISTS tenant_configurations (
    config_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
    
    -- Business Type
    business_type ENUM('home_based', 'small_restaurant', 'regional_chain', 'national_corporation', 'international_corporation') DEFAULT 'small_restaurant',
    
    -- Physical Presence
    physical_presence ENUM('no_building', 'home_kitchen', 'food_truck', 'stall', 'cafe', 'restaurant', 'hotel', 'international_facility') DEFAULT 'restaurant',
    
    -- Cuisine Type
    cuisine_type ENUM('traditional', 'international', 'fusion') DEFAULT 'traditional',
    
    -- Halal Type
    halal_type ENUM('halal_only', 'non_halal', 'mixed') DEFAULT 'halal_only',
    
    -- Target Market
    target_market ENUM('mass_market', 'niche', 'premium', 'luxury') DEFAULT 'mass_market',
    
    -- Menu Complexity
    menu_complexity ENUM('single_item', 'limited', 'moderate', 'extensive') DEFAULT 'moderate',
    
    -- Product Mix
    product_mix ENUM('food_only', 'beverage_only', 'food_beverage', 'food_non_food') DEFAULT 'food_beverage',
    
    -- Operational Settings
    operating_hours JSON,
    delivery_zones JSON,
    service_areas JSON,
    
    -- Onboarding Settings
    onboarding_template VARCHAR(50),
    onboarding_completed_at TIMESTAMP NULL,
    
    -- Feature Settings
    enabled_features JSON,
    disabled_features JSON,
    
    -- Custom Settings
    custom_settings JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_config_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_business_type (business_type),
    INDEX idx_physical_presence (physical_presence),
    INDEX idx_cuisine_type (cuisine_type),
    INDEX idx_halal_type (halal_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
