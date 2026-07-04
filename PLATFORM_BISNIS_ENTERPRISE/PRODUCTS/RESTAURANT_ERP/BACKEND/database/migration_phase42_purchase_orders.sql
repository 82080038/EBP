-- Migration Phase 42: Purchase Orders
-- Provides comprehensive purchase order management with creation, approval, and tracking

-- Purchase Orders Table
CREATE TABLE IF NOT EXISTS purchase_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    
    -- Order Details
    order_number VARCHAR(50) NOT NULL,
    order_type ENUM('standard', 'urgent', 'recurring', 'return') DEFAULT 'standard',
    
    -- Dates
    order_date DATE NOT NULL,
    expected_delivery_date DATE NULL,
    actual_delivery_date DATE NULL,
    
    -- Value
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    shipping_cost DECIMAL(15,2) DEFAULT 0.00,
    discount_amount DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    
    -- Status
    order_status ENUM('draft', 'submitted', 'approved', 'rejected', 'processing', 'shipped', 'received', 'completed', 'cancelled') DEFAULT 'draft',
    
    -- Approval
    approved_by BIGINT UNSIGNED NULL,
    approved_at DATETIME NULL,
    rejection_reason TEXT NULL,
    
    -- Delivery
    delivery_address TEXT NULL,
    delivery_instructions TEXT NULL,
    
    -- Notes
    internal_notes TEXT NULL,
    supplier_notes TEXT NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    modified_by BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_order_number (order_number),
    INDEX idx_order_date (order_date),
    INDEX idx_order_status (order_status),
    INDEX idx_expected_delivery_date (expected_delivery_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (modified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Order Items Table
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Item Details
    supplier_sku VARCHAR(100) NULL,
    item_name VARCHAR(255) NOT NULL,
    
    -- Quantity
    quantity_ordered DECIMAL(15,2) NOT NULL,
    quantity_received DECIMAL(15,2) DEFAULT 0.00,
    quantity_rejected DECIMAL(15,2) DEFAULT 0.00,
    
    -- Pricing
    unit_price DECIMAL(15,2) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    tax_percentage DECIMAL(5,2) DEFAULT 0.00,
    
    -- Amounts
    line_total DECIMAL(15,2) NOT NULL,
    
    -- Status
    item_status ENUM('pending', 'ordered', 'received', 'rejected', 'partial') DEFAULT 'pending',
    
    -- Notes
    item_notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_purchase_order_id (purchase_order_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    INDEX idx_item_status (item_status),
    
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Order History Table
CREATE TABLE IF NOT EXISTS purchase_order_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- History Details
    action_type ENUM('created', 'submitted', 'approved', 'rejected', 'cancelled', 'updated', 'shipped', 'received', 'completed') NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NULL,
    
    -- Details
    action_details TEXT NULL,
    
    -- Staff
    performed_by BIGINT UNSIGNED NOT NULL,
    performed_at DATETIME NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_purchase_order_id (purchase_order_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_action_type (action_type),
    INDEX idx_performed_at (performed_at),
    
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goods Receipt Table
CREATE TABLE IF NOT EXISTS goods_receipts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    purchase_order_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    
    -- Receipt Details
    receipt_number VARCHAR(50) NOT NULL,
    receipt_date DATE NOT NULL,
    
    -- Delivery
    delivery_note_number VARCHAR(100) NULL,
    carrier_name VARCHAR(255) NULL,
    vehicle_number VARCHAR(100) NULL,
    
    -- Status
    receipt_status ENUM('draft', 'completed', 'partial', 'rejected') DEFAULT 'draft',
    
    -- Notes
    receiving_notes TEXT NULL,
    internal_notes TEXT NULL,
    
    -- Staff
    received_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_purchase_order_id (purchase_order_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_receipt_number (receipt_number),
    INDEX idx_receipt_date (receipt_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goods Receipt Items Table
CREATE TABLE IF NOT EXISTS goods_receipt_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    goods_receipt_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    purchase_order_item_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Quantity
    quantity_received DECIMAL(15,2) NOT NULL,
    quantity_rejected DECIMAL(15,2) DEFAULT 0.00,
    
    -- Quality
    quality_status ENUM('accepted', 'rejected', 'conditional') DEFAULT 'accepted',
    rejection_reason TEXT NULL,
    
    -- Details
    batch_number VARCHAR(100) NULL,
    expiry_date DATE NULL,
    lot_number VARCHAR(100) NULL,
    
    -- Notes
    item_notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_goods_receipt_id (goods_receipt_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_purchase_order_item_id (purchase_order_item_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    
    FOREIGN KEY (goods_receipt_id) REFERENCES goods_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (purchase_order_item_id) REFERENCES purchase_order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate purchase order number trigger
DELIMITER //
CREATE TRIGGER generate_purchase_order_number
BEFORE INSERT ON purchase_orders
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(order_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM purchase_orders
    WHERE restaurant_id = NEW.restaurant_id;
    
    SET NEW.order_number = CONCAT('PO', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Generate goods receipt number trigger
DELIMITER //
CREATE TRIGGER generate_goods_receipt_number
BEFORE INSERT ON goods_receipts
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(receipt_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM goods_receipts
    WHERE restaurant_id = NEW.restaurant_id;
    
    SET NEW.receipt_number = CONCAT('GR', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Log purchase order history trigger
DELIMITER //
CREATE TRIGGER log_purchase_order_history
AFTER INSERT ON purchase_orders
FOR EACH NEW
BEGIN
    INSERT INTO purchase_order_history (purchase_order_id, restaurant_id, action_type, old_status, new_status, performed_by, performed_at)
    VALUES (NEW.id, NEW.restaurant_id, 'created', NULL, NEW.order_status, NEW.created_by, NOW());
END//
DELIMITER ;

-- Log purchase order status change trigger
DELIMITER //
CREATE TRIGGER log_purchase_order_status_change
AFTER UPDATE ON purchase_orders
FOR EACH ROW
BEGIN
    IF OLD.order_status != NEW.order_status THEN
        INSERT INTO purchase_order_history (purchase_order_id, restaurant_id, action_type, old_status, new_status, performed_by, performed_at)
        VALUES (NEW.id, NEW.restaurant_id, NEW.order_status, OLD.order_status, NEW.order_status, NEW.modified_by, NOW());
    END IF;
END//
DELIMITER ;
