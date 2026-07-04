-- Migration Phase 29: Order Management System
-- Provides comprehensive order management with table management, order routing, and kitchen display system

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    table_id BIGINT UNSIGNED NULL,
    order_number VARCHAR(50) NOT NULL,
    
    -- Order Details
    order_type ENUM('dine_in', 'takeaway', 'delivery', 'online') NOT NULL,
    order_status ENUM('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled') DEFAULT 'pending',
    order_channel ENUM('pos', 'online', 'delivery_platform', 'other') NOT NULL,
    
    -- Customer Information
    customer_id BIGINT UNSIGNED NULL,
    customer_name VARCHAR(255) NULL,
    customer_phone VARCHAR(50) NULL,
    customer_email VARCHAR(255) NULL,
    customer_address TEXT NULL,
    
    -- Order Timing
    order_date DATETIME NOT NULL,
    estimated_time INT NULL, -- in minutes
    actual_time INT NULL, -- in minutes
    confirmed_at DATETIME NULL,
    started_at DATETIME NULL,
    ready_at DATETIME NULL,
    served_at DATETIME NULL,
    completed_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    
    -- Financials
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    service_charge DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    delivery_fee DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    
    -- Payment
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_method VARCHAR(50) NULL,
    payment_reference VARCHAR(255) NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    confirmed_by BIGINT UNSIGNED NULL,
    served_by BIGINT UNSIGNED NULL,
    
    -- Notes
    special_instructions TEXT NULL,
    internal_notes TEXT NULL,
    cancellation_reason TEXT NULL,
    
    -- External Reference
    external_order_id VARCHAR(255) NULL, -- for delivery platforms
    external_source VARCHAR(100) NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_table_id (table_id),
    INDEX idx_order_number (order_number),
    INDEX idx_order_status (order_status),
    INDEX idx_order_type (order_type),
    INDEX idx_order_date (order_date),
    INDEX idx_customer_id (customer_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_external_order_id (external_order_id),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (served_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    menu_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Item Details
    item_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    
    -- Modifiers
    modifiers JSON NULL, -- array of modifier selections
    special_instructions TEXT NULL,
    
    -- Kitchen Routing
    preparation_station ENUM('kitchen', 'bar', 'dessert', 'other') NULL,
    preparation_status ENUM('pending', 'in_progress', 'ready', 'served', 'cancelled') DEFAULT 'pending',
    preparation_started_at DATETIME NULL,
    preparation_ready_at DATETIME NULL,
    
    -- Cancellation
    is_cancelled BOOLEAN DEFAULT FALSE,
    cancelled_at DATETIME NULL,
    cancellation_reason TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_order_id (order_id),
    INDEX idx_menu_item_id (menu_item_id),
    INDEX idx_preparation_status (preparation_status),
    INDEX idx_preparation_station (preparation_station),
    
    -- Foreign Keys
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Modifiers Table
CREATE TABLE IF NOT EXISTS order_modifiers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id BIGINT UNSIGNED NOT NULL,
    modifier_id BIGINT UNSIGNED NOT NULL,
    
    -- Modifier Details
    modifier_name VARCHAR(255) NOT NULL,
    modifier_type ENUM('add', 'remove', 'replace', 'upgrade') NOT NULL,
    price_adjustment DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_order_item_id (order_item_id),
    INDEX idx_modifier_id (modifier_id),
    
    -- Foreign Keys
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (modifier_id) REFERENCES modifiers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kitchen Orders Table (KDS)
CREATE TABLE IF NOT EXISTS kitchen_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    order_item_id BIGINT UNSIGNED NOT NULL,
    
    -- KDS Details
    station ENUM('kitchen', 'bar', 'dessert', 'grill', 'fry', 'salad', 'other') NOT NULL,
    display_order INT DEFAULT 0,
    
    -- Status
    status ENUM('pending', 'in_progress', 'ready', 'served', 'cancelled') DEFAULT 'pending',
    
    -- Timing
    sent_to_kitchen_at DATETIME NOT NULL,
    started_at DATETIME NULL,
    ready_at DATETIME NULL,
    served_at DATETIME NULL,
    
    -- Staff
    prepared_by BIGINT UNSIGNED NULL,
    
    -- Notes
    kitchen_notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_order_id (order_id),
    INDEX idx_order_item_id (order_item_id),
    INDEX idx_station (station),
    INDEX idx_status (status),
    INDEX idx_sent_to_kitchen_at (sent_to_kitchen_at),
    
    -- Foreign Keys
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (prepared_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Sessions Table
CREATE TABLE IF NOT EXISTS table_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    table_id BIGINT UNSIGNED NOT NULL,
    
    -- Session Details
    session_number VARCHAR(50) NOT NULL,
    session_status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    
    -- Timing
    started_at DATETIME NOT NULL,
    ended_at DATETIME NULL,
    duration_minutes INT NULL,
    
    -- Customer
    customer_count INT DEFAULT 0,
    customer_id BIGINT UNSIGNED NULL,
    
    -- Orders
    order_count INT DEFAULT 0,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    
    -- Staff
    server_id BIGINT UNSIGNED NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_table_id (table_id),
    INDEX idx_session_status (session_status),
    INDEX idx_started_at (started_at),
    INDEX idx_customer_id (customer_id),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (server_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Status History Table
CREATE TABLE IF NOT EXISTS order_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    
    -- Status Change
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    
    -- Details
    changed_by BIGINT UNSIGNED NULL,
    changed_at DATETIME NOT NULL,
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_order_id (order_id),
    INDEX idx_changed_at (changed_at),
    
    -- Foreign Keys
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate order number trigger
DELIMITER //
CREATE TRIGGER generate_order_number
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(order_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM orders
    WHERE restaurant_id = NEW.restaurant_id
    AND DATE(order_date) = CURDATE();
    
    SET NEW.order_number = CONCAT('ORD', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Update table session order count trigger
DELIMITER //
CREATE TRIGGER update_table_session_order_count
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    IF NEW.table_id IS NOT NULL THEN
        UPDATE table_sessions
        SET order_count = order_count + 1,
            total_amount = total_amount + NEW.total_amount
        WHERE table_id = NEW.table_id
        AND session_status = 'active'
        LIMIT 1;
    END IF;
END//
DELIMITER ;
