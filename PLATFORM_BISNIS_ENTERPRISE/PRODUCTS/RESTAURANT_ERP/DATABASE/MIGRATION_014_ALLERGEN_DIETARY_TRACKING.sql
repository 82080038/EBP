-- MIGRATION_014: Allergen and Dietary Tracking
-- This migration adds support for allergen information and dietary restrictions tracking
-- Created: 2026-07-05

-- Table: allergen_types
CREATE TABLE IF NOT EXISTS allergen_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    severity ENUM('MILD', 'MODERATE', 'SEVERE', 'LIFE_THREATENING') DEFAULT 'MODERATE',
    is_common BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_severity (severity),
    INDEX idx_is_common (is_common)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: dietary_restrictions
CREATE TABLE IF NOT EXISTS dietary_restrictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT NULL,
    category ENUM('RELIGIOUS', 'HEALTH', 'ETHICAL', 'PREFERENCE', 'MEDICAL') NOT NULL,
    is_common BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_common (is_common)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: product_allergens
CREATE TABLE IF NOT EXISTS product_allergens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    product_id INT NOT NULL,
    allergen_id INT NOT NULL,
    contains BOOLEAN DEFAULT TRUE COMMENT 'TRUE if contains, FALSE if may contain',
    cross_contamination_risk BOOLEAN DEFAULT FALSE,
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT NULL,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_product (tenant_id, product_id),
    INDEX idx_allergen (allergen_id),
    INDEX idx_contains (contains),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (allergen_id) REFERENCES allergen_types(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: product_dietary_info
CREATE TABLE IF NOT EXISTS product_dietary_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    product_id INT NOT NULL,
    dietary_restriction_id INT NOT NULL,
    is_compliant BOOLEAN DEFAULT FALSE,
    certification_url VARCHAR(500) NULL,
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT NULL,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_product (tenant_id, product_id),
    INDEX idx_dietary (dietary_restriction_id),
    INDEX idx_compliant (is_compliant),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (dietary_restriction_id) REFERENCES dietary_restrictions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: customer_dietary_preferences
CREATE TABLE IF NOT EXISTS customer_dietary_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    customer_id INT NOT NULL,
    allergen_id INT NULL,
    dietary_restriction_id INT NULL,
    preference_type ENUM('ALLERGY', 'INTOLERANCE', 'RESTRICTION', 'PREFERENCE') NOT NULL,
    severity ENUM('MILD', 'MODERATE', 'SEVERE', 'LIFE_THREATENING') DEFAULT 'MODERATE',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_customer (tenant_id, customer_id),
    INDEX idx_allergen (allergen_id),
    INDEX idx_dietary (dietary_restriction_id),
    INDEX idx_type (preference_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (allergen_id) REFERENCES allergen_types(id) ON DELETE SET NULL,
    FOREIGN KEY (dietary_restriction_id) REFERENCES dietary_restrictions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert common allergen types
INSERT INTO allergen_types (name, description, severity, is_common) VALUES
('Gluten', 'Wheat, barley, rye and related grains', 'SEVERE', TRUE),
('Dairy', 'Milk and milk products', 'SEVERE', TRUE),
('Eggs', 'Egg proteins', 'SEVERE', TRUE),
('Nuts', 'Tree nuts (almonds, cashews, walnuts, etc.)', 'LIFE_THREATENING', TRUE),
('Peanuts', 'Peanuts and peanut products', 'LIFE_THREATENING', TRUE),
('Soy', 'Soybeans and soy products', 'SEVERE', TRUE),
('Fish', 'Fish and fish products', 'SEVERE', TRUE),
('Shellfish', 'Crustaceans and mollusks', 'LIFE_THREATENING', TRUE),
('Sesame', 'Sesame seeds and products', 'SEVERE', TRUE),
('Sulfites', 'Sulfur dioxide and sulfites', 'MODERATE', TRUE),
('Mustard', 'Mustard and mustard products', 'SEVERE', TRUE),
('Celery', 'Celery and celeriac', 'SEVERE', TRUE),
('Lupin', 'Lupin and lupin products', 'SEVERE', FALSE),
('Mollusks', 'Mollusks (oysters, clams, mussels, etc.)', 'LIFE_THREATENING', TRUE);

-- Insert common dietary restrictions
INSERT INTO dietary_restrictions (name, code, description, category, is_common) VALUES
('Vegetarian', 'VEG', 'No meat or fish', 'ETHICAL', TRUE),
('Vegan', 'VEGAN', 'No animal products', 'ETHICAL', TRUE),
('Halal', 'HALAL', 'Permissible under Islamic law', 'RELIGIOUS', TRUE),
('Kosher', 'KOSHER', 'Permissible under Jewish law', 'RELIGIOUS', TRUE),
('Gluten-Free', 'GF', 'No gluten-containing ingredients', 'MEDICAL', TRUE),
('Dairy-Free', 'DF', 'No dairy products', 'MEDICAL', TRUE),
('Nut-Free', 'NF', 'No nuts or peanuts', 'MEDICAL', TRUE),
('Low-Sodium', 'LS', 'Reduced sodium content', 'HEALTH', TRUE),
('Low-Sugar', 'LGS', 'Reduced sugar content', 'HEALTH', TRUE),
('Keto', 'KETO', 'Ketogenic diet compliant', 'PREFERENCE', FALSE),
('Paleo', 'PALEO', 'Paleolithic diet compliant', 'PREFERENCE', FALSE),
('Low-Carb', 'LC', 'Reduced carbohydrate content', 'PREFERENCE', FALSE),
('Organic', 'ORG', 'Organic ingredients only', 'PREFERENCE', FALSE);

-- Rollback script
-- DROP TABLE IF EXISTS customer_dietary_preferences;
-- DROP TABLE IF EXISTS product_dietary_info;
-- DROP TABLE IF EXISTS product_allergens;
-- DROP TABLE IF EXISTS dietary_restrictions;
-- DROP TABLE IF EXISTS allergen_types;
