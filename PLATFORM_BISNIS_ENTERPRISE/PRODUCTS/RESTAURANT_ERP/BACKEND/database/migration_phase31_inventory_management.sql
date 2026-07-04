-- Migration Phase 31: Inventory Management
-- Provides comprehensive inventory tracking with stock management, recipes, and waste tracking

-- Inventory Items Table
CREATE TABLE IF NOT EXISTS inventory_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Item Details
    item_code VARCHAR(50) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_description TEXT NULL,
    
    -- Category
    category_id BIGINT UNSIGNED NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    
    -- Stock
    current_stock DECIMAL(15,3) DEFAULT 0.000,
    minimum_stock DECIMAL(15,3) DEFAULT 0.000,
    maximum_stock DECIMAL(15,3) DEFAULT 0.000,
    reorder_point DECIMAL(15,3) DEFAULT 0.000,
    reorder_quantity DECIMAL(15,3) DEFAULT 0.000,
    
    -- Pricing
    cost_per_unit DECIMAL(15,2) NOT NULL,
    average_cost DECIMAL(15,2) NULL,
    last_purchase_price DECIMAL(15,2) NULL,
    
    -- Supplier
    supplier_id BIGINT UNSIGNED NULL,
    supplier_item_code VARCHAR(50) NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_perishable BOOLEAN DEFAULT FALSE,
    shelf_life_days INT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_item_code (item_code),
    INDEX idx_category_id (category_id),
    INDEX idx_unit_id (unit_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_is_active (is_active),
    INDEX idx_current_stock (current_stock),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES inventory_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES inventory_units(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory Categories Table
CREATE TABLE IF NOT EXISTS inventory_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Category Details
    category_name VARCHAR(255) NOT NULL,
    category_description TEXT NULL,
    parent_category_id BIGINT UNSIGNED NULL,
    
    -- Display
    color_code VARCHAR(7) NULL,
    icon_url VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_parent_category_id (parent_category_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_category_id) REFERENCES inventory_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory Units Table
CREATE TABLE IF NOT EXISTS inventory_units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Unit Details
    unit_name VARCHAR(50) NOT NULL,
    unit_code VARCHAR(10) NOT NULL,
    unit_abbreviation VARCHAR(10) NOT NULL,
    
    -- Conversion
    base_unit_id BIGINT UNSIGNED NULL,
    conversion_factor DECIMAL(15,3) DEFAULT 1.000,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_unit_code (unit_code),
    INDEX idx_base_unit_id (base_unit_id),
    INDEX idx_is_active (is_active),
    UNIQUE KEY unique_unit_code (restaurant_id, unit_code),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (base_unit_id) REFERENCES inventory_units(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movements Table
CREATE TABLE IF NOT EXISTS stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Movement Details
    movement_type ENUM('purchase', 'sale', 'transfer', 'adjustment', 'waste', 'return', 'production', 'other') NOT NULL,
    movement_direction ENUM('in', 'out') NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    
    -- Stock Before/After
    stock_before DECIMAL(15,3) NOT NULL,
    stock_after DECIMAL(15,3) NOT NULL,
    
    -- Reference
    reference_type VARCHAR(50) NULL,
    reference_id BIGINT UNSIGNED NULL,
    reference_number VARCHAR(50) NULL,
    
    -- Cost
    unit_cost DECIMAL(15,2) NULL,
    total_cost DECIMAL(15,2) NULL,
    
    -- Location
    location_id BIGINT UNSIGNED NULL,
    
    -- Staff
    performed_by BIGINT UNSIGNED NOT NULL,
    performed_at DATETIME NOT NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_movement_direction (movement_direction),
    INDEX idx_performed_at (performed_at),
    INDEX idx_reference_type (reference_type),
    INDEX idx_reference_id (reference_id),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Transfers Table
CREATE TABLE IF NOT EXISTS stock_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    transfer_number VARCHAR(50) NOT NULL,
    
    -- Transfer Details
    transfer_type ENUM('location', 'branch', 'other') NOT NULL,
    from_location_id BIGINT UNSIGNED NULL,
    to_location_id BIGINT UNSIGNED NOT NULL,
    from_branch_id BIGINT UNSIGNED NULL,
    to_branch_id BIGINT UNSIGNED NULL,
    
    -- Status
    transfer_status ENUM('pending', 'in_transit', 'completed', 'cancelled') DEFAULT 'pending',
    
    -- Timing
    transfer_date DATETIME NOT NULL,
    received_at DATETIME NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    received_by BIGINT UNSIGNED NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_transfer_number (transfer_number),
    INDEX idx_transfer_status (transfer_status),
    INDEX idx_transfer_date (transfer_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Transfer Items Table
CREATE TABLE IF NOT EXISTS stock_transfer_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stock_transfer_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Item Details
    quantity DECIMAL(15,3) NOT NULL,
    unit_cost DECIMAL(15,2) NULL,
    total_cost DECIMAL(15,2) NULL,
    
    -- Status
    received_quantity DECIMAL(15,3) NULL,
    item_status ENUM('pending', 'transferred', 'received', 'damaged') DEFAULT 'pending',
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_stock_transfer_id (stock_transfer_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    
    FOREIGN KEY (stock_transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waste Logs Table
CREATE TABLE IF NOT EXISTS waste_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Waste Details
    waste_quantity DECIMAL(15,3) NOT NULL,
    waste_reason ENUM('expired', 'damaged', 'spilled', 'preparation', 'theft', 'other') NOT NULL,
    waste_reason_description TEXT NULL,
    
    -- Cost
    unit_cost DECIMAL(15,2) NULL,
    total_cost DECIMAL(15,2) NULL,
    
    -- Location
    location_id BIGINT UNSIGNED NULL,
    
    -- Staff
    reported_by BIGINT UNSIGNED NOT NULL,
    approved_by BIGINT UNSIGNED NULL,
    
    -- Timing
    waste_date DATETIME NOT NULL,
    approved_at DATETIME NULL,
    
    -- Status
    is_approved BOOLEAN DEFAULT FALSE,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    INDEX idx_waste_reason (waste_reason),
    INDEX idx_waste_date (waste_date),
    INDEX idx_is_approved (is_approved),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Alerts Table
CREATE TABLE IF NOT EXISTS stock_alerts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Alert Details
    alert_type ENUM('low_stock', 'out_of_stock', 'overstock', 'expiry_warning', 'other') NOT NULL,
    alert_severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    alert_message TEXT NOT NULL,
    
    -- Thresholds
    current_stock DECIMAL(15,3) NOT NULL,
    threshold_value DECIMAL(15,3) NULL,
    
    -- Status
    is_resolved BOOLEAN DEFAULT FALSE,
    resolved_at DATETIME NULL,
    resolved_by BIGINT UNSIGNED NULL,
    
    -- Notification
    notification_sent BOOLEAN DEFAULT FALSE,
    notification_sent_at DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    INDEX idx_alert_type (alert_type),
    INDEX idx_alert_severity (alert_severity),
    INDEX idx_is_resolved (is_resolved),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate transfer number trigger
DELIMITER //
CREATE TRIGGER generate_transfer_number
BEFORE INSERT ON stock_transfers
FOR EACH ROW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(transfer_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM stock_transfers
    WHERE restaurant_id = NEW.restaurant_id
    AND DATE(transfer_date) = CURDATE();
    
    SET NEW.transfer_number = CONCAT('TRF', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Insert default inventory units for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_inventory_units
AFTER INSERT ON restaurants
FOR EACH NEW
BEGIN
    -- Base unit: Piece
    INSERT INTO inventory_units (restaurant_id, unit_name, unit_code, unit_abbreviation, base_unit_id, conversion_factor, is_active)
    VALUES (NEW.id, 'Piece', 'PCS', 'pcs', NULL, 1.000, TRUE);
    
    -- Weight: Kilogram
    INSERT INTO inventory_units (restaurant_id, unit_name, unit_code, unit_abbreviation, base_unit_id, conversion_factor, is_active)
    VALUES (NEW.id, 'Kilogram', 'KG', 'kg', NULL, 1.000, TRUE);
    
    -- Weight: Gram
    INSERT INTO inventory_units (restaurant_id, unit_name, unit_code, unit_abbreviation, base_unit_id, conversion_factor, is_active)
    VALUES (NEW.id, 'Gram', 'G', 'g', NULL, 1.000, TRUE);
    
    -- Volume: Liter
    INSERT INTO inventory_units (restaurant_id, unit_name, unit_code, unit_abbreviation, base_unit_id, conversion_factor, is_active)
    VALUES (NEW.id, 'Liter', 'L', 'L', NULL, 1.000, TRUE);
    
    -- Volume: Milliliter
    INSERT INTO inventory_units (restaurant_id, unit_name, unit_code, unit_abbreviation, base_unit_id, conversion_factor, is_active)
    VALUES (NEW.id, 'Milliliter', 'ML', 'ml', NULL, 1.000, TRUE);
END//
DELIMITER ;
