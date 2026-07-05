-- Migration 005: Feature Modules Table
-- Business Scope & Flexibility (Phase 10 - RESEARCH_33)

-- Create feature_modules table
CREATE TABLE IF NOT EXISTS feature_modules (
    module_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_code VARCHAR(50) NOT NULL UNIQUE,
    module_name VARCHAR(100) NOT NULL,
    module_category ENUM('CORE', 'CUSTOMER', 'OPERATIONS', 'ENTERPRISE', 'ADVANCED') NOT NULL,
    description TEXT,
    is_enabled TINYINT(1) DEFAULT 1,
    is_premium TINYINT(1) DEFAULT 0,
    pricing_tier JSON,
    dependencies JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_module_category (module_category),
    INDEX idx_is_enabled (is_enabled),
    INDEX idx_is_premium (is_premium)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tenant_feature_modules table (link tenants to enabled features)
CREATE TABLE IF NOT EXISTS tenant_feature_modules (
    tenant_module_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    module_id BIGINT UNSIGNED NOT NULL,
    is_enabled TINYINT(1) DEFAULT 1,
    enabled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_tfm_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    CONSTRAINT fk_tfm_module FOREIGN KEY (module_id) REFERENCES feature_modules(module_id),
    INDEX idx_tfm_tenant (tenant_id),
    INDEX idx_tfm_module (module_id),
    UNIQUE KEY uk_tenant_module (tenant_id, module_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default feature modules
INSERT INTO feature_modules (module_code, module_name, module_category, description, is_premium, pricing_tier) VALUES
-- Core Modules
('pos', 'Point of Sale', 'CORE', 'Core POS functionality for order management', 0, NULL),
('inventory', 'Inventory Management', 'CORE', 'Stock tracking and inventory management', 0, NULL),
('menu', 'Menu Management', 'CORE', 'Menu and product management', 0, NULL),
('staff', 'Staff Management', 'CORE', 'Employee and staff management', 0, NULL),

-- Customer Modules
('reservations', 'Reservation Management', 'CUSTOMER', 'Table reservation system', 0, NULL),
('loyalty', 'Loyalty Program', 'CUSTOMER', 'Customer loyalty and rewards', 1, '{"basic": 30, "premium": 50}'),

-- Operations Modules
('delivery', 'Delivery Management', 'OPERATIONS', 'Delivery order management', 0, NULL),
('kitchen_display', 'Kitchen Display System', 'OPERATIONS', 'Digital kitchen display', 0, NULL),
('table_management', 'Table Management', 'OPERATIONS', 'Table and floor management', 0, NULL),
('procurement', 'Procurement', 'OPERATIONS', 'Purchase order and supplier management', 0, NULL),

-- Enterprise Modules
('multi_location', 'Multi-Location', 'ENTERPRISE', 'Multi-location management', 1, '{"basic": 100, "premium": 200}'),
('api_access', 'API Access', 'ENTERPRISE', 'API access for integrations', 1, '{"basic": 50, "premium": 100}'),
('franchise', 'Franchise Management', 'ENTERPRISE', 'Franchise operations', 1, '{"basic": 150, "premium": 300}'),
('international', 'International', 'ENTERPRISE', 'Multi-currency and multi-language', 1, '{"basic": 200, "premium": 400}'),

-- Advanced Modules
('ai_analytics', 'AI Analytics', 'ADVANCED', 'AI-powered analytics and insights', 1, '{"basic": 100, "premium": 200}'),
('automation', 'Automation', 'ADVANCED', 'Workflow automation', 1, '{"basic": 80, "premium": 150}');
