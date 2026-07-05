-- MIGRATION_013: Seasonal Menu Planning
-- This migration adds support for seasonal menu planning and management
-- Created: 2026-07-05

-- Table: menu_seasons
CREATE TABLE IF NOT EXISTS menu_seasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    season_name VARCHAR(100) NOT NULL,
    season_type ENUM('SPRING', 'SUMMER', 'AUTUMN', 'WINTER', 'RAMADAN', 'CHRISTMAS', 'NEW_YEAR', 'CUSTOM') NOT NULL,
    year INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description TEXT NULL,
    theme VARCHAR(255) NULL,
    status ENUM('DRAFT', 'ACTIVE', 'COMPLETED', 'CANCELLED') DEFAULT 'DRAFT',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT NULL,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_tenant_year (tenant_id, year),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: menu_season_items
CREATE TABLE IF NOT EXISTS menu_season_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    product_id INT NOT NULL,
    item_type ENUM('SEASONAL_SPECIAL', 'SEASONAL_VARIANT', 'SEASONAL_PROMOTION', 'SEASONAL_DISCONTINUATION') NOT NULL,
    priority INT DEFAULT 0 COMMENT 'Display priority (higher = more prominent)',
    pricing_override DECIMAL(10,2) NULL COMMENT 'Seasonal price override',
    seasonal_description TEXT NULL COMMENT 'Seasonal-specific description',
    seasonal_image_url VARCHAR(500) NULL COMMENT 'Seasonal-specific image',
    availability_start_date DATE NULL,
    availability_end_date DATE NULL,
    target_audience JSON NULL COMMENT 'Target audience for this seasonal item',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_season (season_id),
    INDEX idx_product (product_id),
    INDEX idx_item_type (item_type),
    INDEX idx_priority (priority),
    FOREIGN KEY (season_id) REFERENCES menu_seasons(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: menu_seasonal_ingredients
CREATE TABLE IF NOT EXISTS menu_seasonal_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    availability_status ENUM('ABUNDANT', 'LIMITED', 'SCARCE', 'UNAVAILABLE') NOT NULL,
    expected_price_change DECIMAL(5,2) NULL COMMENT 'Expected price change percentage',
    supplier_notes TEXT NULL,
    alternative_ingredient_id INT NULL COMMENT 'Alternative ingredient if unavailable',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_season (season_id),
    INDEX idx_ingredient (ingredient_id),
    INDEX idx_availability (availability_status),
    FOREIGN KEY (season_id) REFERENCES menu_seasons(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES inventory(id) ON DELETE CASCADE,
    FOREIGN KEY (alternative_ingredient_id) REFERENCES inventory(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: menu_season_analytics
CREATE TABLE IF NOT EXISTS menu_season_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,2) NOT NULL,
    comparison_value DECIMAL(15,2) NULL COMMENT 'Comparison with previous season',
    change_percentage DECIMAL(5,2) NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_season (season_id),
    INDEX idx_metric (metric_name),
    INDEX idx_recorded_at (recorded_at),
    FOREIGN KEY (season_id) REFERENCES menu_seasons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: menu_season_templates
CREATE TABLE IF NOT EXISTS menu_season_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    template_name VARCHAR(255) NOT NULL,
    template_type ENUM('SEASONAL', 'HOLIDAY', 'PROMOTIONAL', 'EVENT') NOT NULL,
    season_type ENUM('SPRING', 'SUMMER', 'AUTUMN', 'WINTER', 'RAMADAN', 'CHRISTMAS', 'NEW_YEAR', 'CUSTOM') NULL,
    description TEXT NULL,
    configuration JSON NOT NULL COMMENT 'Template configuration',
    is_public BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT NULL,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_template_type (template_type),
    INDEX idx_season_type (season_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing
INSERT INTO menu_seasons (tenant_id, season_name, season_type, year, start_date, end_date, description, theme, status, created_by) VALUES
(1, 'Summer 2026 Menu', 'SUMMER', 2026, '2026-06-01', '2026-08-31', 'Summer seasonal menu with refreshing dishes', 'Tropical Summer', 'ACTIVE', 1),
(1, 'Ramadan 2026 Menu', 'RAMADAN', 2026, '2026-03-01', '2026-04-30', 'Special Ramadan menu with traditional dishes', 'Ramadan Blessings', 'COMPLETED', 1);

INSERT INTO menu_season_items (season_id, product_id, item_type, priority, pricing_override, seasonal_description, availability_start_date, availability_end_date) VALUES
(1, 1, 'SEASONAL_SPECIAL', 10, 28000.00, 'Refreshing tropical fruit smoothie for summer', '2026-06-01', '2026-08-31'),
(1, 2, 'SEASONAL_PROMOTION', 8, 22000.00, 'Summer special: Iced coffee with discount', '2026-06-01', '2026-08-31');

-- Rollback script
-- DROP TABLE IF EXISTS menu_season_templates;
-- DROP TABLE IF EXISTS menu_season_analytics;
-- DROP TABLE IF EXISTS menu_seasonal_ingredients;
-- DROP TABLE IF EXISTS menu_season_items;
-- DROP TABLE IF EXISTS menu_seasons;
