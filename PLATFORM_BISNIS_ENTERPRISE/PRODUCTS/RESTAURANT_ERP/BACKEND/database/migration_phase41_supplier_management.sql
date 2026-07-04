-- Migration Phase 41: Supplier Management
-- Provides comprehensive supplier management with profiles, contracts, and performance tracking

-- Suppliers Table
CREATE TABLE IF NOT EXISTS suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Supplier Details
    supplier_code VARCHAR(50) NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    supplier_type ENUM('food', 'beverage', 'equipment', 'packaging', 'services', 'other') NOT NULL,
    
    -- Contact
    contact_person VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) DEFAULT 'Indonesia',
    
    -- Business
    tax_id VARCHAR(50) NULL,
    business_license VARCHAR(100) NULL,
    
    -- Payment
    payment_terms INT DEFAULT 30, -- days
    payment_method ENUM('bank_transfer', 'cash', 'check', 'other') DEFAULT 'bank_transfer',
    bank_account VARCHAR(255) NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_preferred BOOLEAN DEFAULT FALSE,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_supplier_code (supplier_code),
    INDEX idx_supplier_type (supplier_type),
    INDEX idx_is_active (is_active),
    INDEX idx_is_preferred (is_preferred),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier Products Table
CREATE TABLE IF NOT EXISTS supplier_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Product Details
    supplier_sku VARCHAR(100) NULL,
    product_name VARCHAR(255) NOT NULL,
    
    -- Pricing
    unit_price DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    
    -- Ordering
    minimum_order_quantity DECIMAL(15,2) DEFAULT 1.00,
    lead_time_days INT DEFAULT 7,
    
    -- Status
    is_primary BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    INDEX idx_is_primary (is_primary),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier Contracts Table
CREATE TABLE IF NOT EXISTS supplier_contracts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    
    -- Contract Details
    contract_number VARCHAR(50) NOT NULL,
    contract_name VARCHAR(255) NOT NULL,
    contract_type ENUM('purchase', 'service', 'partnership', 'other') NOT NULL,
    
    -- Period
    start_date DATE NOT NULL,
    end_date DATE NULL,
    
    -- Terms
    contract_terms TEXT NOT NULL,
    payment_terms INT DEFAULT 30,
    
    -- Value
    contract_value DECIMAL(15,2) NULL,
    
    -- Status
    contract_status ENUM('draft', 'active', 'expired', 'terminated', 'renewed') DEFAULT 'draft',
    
    -- Documents
    contract_document_url VARCHAR(255) NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_contract_number (contract_number),
    INDEX idx_contract_status (contract_status),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier Performance Table
CREATE TABLE IF NOT EXISTS supplier_performance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    evaluation_period_start DATE NOT NULL,
    evaluation_period_end DATE NOT NULL,
    
    -- Quality Metrics
    on_time_delivery_rate DECIMAL(5,2) DEFAULT 0.00,
    quality_score DECIMAL(3,2) DEFAULT 0.00, -- 0-5
    defect_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Service Metrics
    response_time_hours DECIMAL(10,2) NULL,
    order_accuracy_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Financial Metrics
    total_orders INT DEFAULT 0,
    total_value DECIMAL(15,2) DEFAULT 0.00,
    
    -- Overall
    overall_score DECIMAL(3,2) DEFAULT 0.00, -- 0-5
    performance_rating ENUM('excellent', 'good', 'satisfactory', 'needs_improvement', 'poor') NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_evaluation_period_start (evaluation_period_start),
    INDEX idx_overall_score (overall_score),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier Ratings Table
CREATE TABLE IF NOT EXISTS supplier_ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    
    -- Rating Details
    rating INT NOT NULL, -- 1-5 stars
    rating_category ENUM('quality', 'delivery', 'service', 'price', 'overall') NOT NULL,
    comment TEXT NULL,
    
    -- Staff
    rated_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_rating_category (rating_category),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (rated_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate supplier code trigger
DELIMITER //
CREATE TRIGGER generate_supplier_code
BEFORE INSERT ON suppliers
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(supplier_code, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM suppliers
    WHERE restaurant_id = NEW.restaurant_id;
    
    SET NEW.supplier_code = CONCAT('SUP', LPAD(next_number, 6, '0'));
END//
DELIMITER ;

-- Generate contract number trigger
DELIMITER //
CREATE TRIGGER generate_contract_number
BEFORE INSERT ON supplier_contracts
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(contract_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM supplier_contracts
    WHERE restaurant_id = NEW.restaurant_id;
    
    SET NEW.contract_number = CONCAT('CON', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;
