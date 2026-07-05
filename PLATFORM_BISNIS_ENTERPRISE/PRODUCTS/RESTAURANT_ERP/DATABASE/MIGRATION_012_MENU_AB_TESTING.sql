-- MIGRATION_012: Menu A/B Testing
-- This migration adds support for A/B testing menu items
-- Created: 2026-07-05

-- Table: menu_ab_tests
CREATE TABLE IF NOT EXISTS menu_ab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('DRAFT', 'ACTIVE', 'PAUSED', 'COMPLETED', 'CANCELLED') DEFAULT 'DRAFT',
    start_date DATETIME NULL,
    end_date DATETIME NULL,
    traffic_split DECIMAL(5,2) DEFAULT 50.00 COMMENT 'Percentage of traffic for variant A',
    target_audience JSON NULL COMMENT 'Target audience criteria',
    success_metric VARCHAR(100) NULL COMMENT 'Primary success metric (e.g., conversion_rate, order_value)',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT NULL,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: menu_ab_test_variants
CREATE TABLE IF NOT EXISTS menu_ab_test_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ab_test_id INT NOT NULL,
    variant_type ENUM('CONTROL', 'VARIANT_A', 'VARIANT_B', 'VARIANT_C') NOT NULL,
    variant_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    configuration JSON NOT NULL COMMENT 'Variant configuration (price, description, image, etc.)',
    is_winner BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ab_test (ab_test_id),
    INDEX idx_variant_type (variant_type),
    FOREIGN KEY (ab_test_id) REFERENCES menu_ab_tests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: menu_ab_test_items
CREATE TABLE IF NOT EXISTS menu_ab_test_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ab_test_id INT NOT NULL,
    variant_id INT NOT NULL,
    product_id INT NOT NULL,
    original_price DECIMAL(10,2) NULL,
    test_price DECIMAL(10,2) NULL,
    test_description TEXT NULL,
    test_image_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ab_test (ab_test_id),
    INDEX idx_variant (variant_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (ab_test_id) REFERENCES menu_ab_tests(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES menu_ab_test_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: menu_ab_test_results
CREATE TABLE IF NOT EXISTS menu_ab_test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ab_test_id INT NOT NULL,
    variant_id INT NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,2) NOT NULL,
    sample_size INT DEFAULT 0,
    confidence_level DECIMAL(5,2) DEFAULT 95.00,
    statistical_significance BOOLEAN DEFAULT FALSE,
    p_value DECIMAL(10,6) NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ab_test (ab_test_id),
    INDEX idx_variant (variant_id),
    INDEX idx_metric (metric_name),
    INDEX idx_recorded_at (recorded_at),
    FOREIGN KEY (ab_test_id) REFERENCES menu_ab_tests(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES menu_ab_test_variants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: menu_ab_test_user_assignments
CREATE TABLE IF NOT EXISTS menu_ab_test_user_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ab_test_id INT NOT NULL,
    variant_id INT NOT NULL,
    user_id INT NULL,
    session_id VARCHAR(255) NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ab_test (ab_test_id),
    INDEX idx_variant (variant_id),
    INDEX idx_user (user_id),
    INDEX idx_session (session_id),
    FOREIGN KEY (ab_test_id) REFERENCES menu_ab_tests(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES menu_ab_test_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing
INSERT INTO menu_ab_tests (tenant_id, branch_id, name, description, status, start_date, end_date, traffic_split, success_metric, created_by) VALUES
(1, 1, 'Price Sensitivity Test - Nasi Goreng', 'Testing different price points for Nasi Goreng to optimize revenue', 'ACTIVE', '2026-07-05 00:00:00', '2026-07-19 23:59:59', 50.00, 'conversion_rate', 1);

INSERT INTO menu_ab_test_variants (ab_test_id, variant_type, variant_name, description, configuration) VALUES
(1, 'CONTROL', 'Original Price', 'Current price at 25,000 IDR', '{"price": 25000, "description": "Nasi Goreng Spesial"}'),
(1, 'VARIANT_A', 'Lower Price', 'Reduced price at 22,000 IDR', '{"price": 22000, "description": "Nasi Goreng Spesial - Promo"}'),
(1, 'VARIANT_B', 'Higher Price', 'Increased price at 28,000 IDR', '{"price": 28000, "description": "Nasi Goreng Premium"}');

-- Rollback script
-- DROP TABLE IF EXISTS menu_ab_test_user_assignments;
-- DROP TABLE IF EXISTS menu_ab_test_results;
-- DROP TABLE IF EXISTS menu_ab_test_items;
-- DROP TABLE IF EXISTS menu_ab_test_variants;
-- DROP TABLE IF EXISTS menu_ab_tests;
