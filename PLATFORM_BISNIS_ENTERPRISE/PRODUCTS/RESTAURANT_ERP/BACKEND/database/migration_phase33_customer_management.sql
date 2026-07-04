-- Migration Phase 33: Customer Management
-- Provides comprehensive customer management with profiles, preferences, and order history

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Customer Details
    customer_code VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    full_name VARCHAR(255) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED,
    
    -- Contact
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    
    -- Address
    address_line1 VARCHAR(255) NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    country VARCHAR(100) DEFAULT 'Indonesia',
    
    -- Preferences
    preferred_language VARCHAR(10) DEFAULT 'id',
    dietary_preferences JSON NULL, -- array of dietary restrictions
    favorite_items JSON NULL, -- array of favorite menu item IDs
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_vip BOOLEAN DEFAULT FALSE,
    
    -- Marketing
    email_subscribed BOOLEAN DEFAULT FALSE,
    sms_subscribed BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_visit_at DATETIME NULL,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_code (customer_code),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_is_active (is_active),
    INDEX idx_is_vip (is_vip),
    INDEX idx_last_visit_at (last_visit_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Preferences Table
CREATE TABLE IF NOT EXISTS customer_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Seating Preferences
    preferred_table_type ENUM('standard', 'booth', 'outdoor', 'private', 'any') DEFAULT 'any',
    preferred_area VARCHAR(100) NULL,
    
    -- Dining Preferences
    meal_type_preference JSON NULL, -- breakfast, lunch, dinner preferences
    spice_level ENUM('none', 'mild', 'medium', 'hot', 'extra_hot') NULL,
    
    -- Service Preferences
    service_level ENUM('standard', 'fast', 'relaxed') DEFAULT 'standard',
    
    -- Allergies
    allergies JSON NULL, -- array of allergy information
    
    -- Special Requests
    special_requests TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_customer_id (customer_id),
    INDEX idx_restaurant_id (restaurant_id),
    UNIQUE KEY unique_customer_restaurant (customer_id, restaurant_id),
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Addresses Table
CREATE TABLE IF NOT EXISTS customer_addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    
    -- Address Details
    address_type ENUM('home', 'work', 'other') DEFAULT 'home',
    address_label VARCHAR(100) NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) DEFAULT 'Indonesia',
    
    -- Delivery
    is_default BOOLEAN DEFAULT FALSE,
    delivery_notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_customer_id (customer_id),
    INDEX idx_is_default (is_default),
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Notes Table
CREATE TABLE IF NOT EXISTS customer_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Note Details
    note_type ENUM('general', 'complaint', 'compliment', 'special_request', 'warning', 'other') DEFAULT 'general',
    note_text TEXT NOT NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Visibility
    is_internal BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_customer_id (customer_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_note_type (note_type),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Tags Table
CREATE TABLE IF NOT EXISTS customer_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Tag Details
    tag_name VARCHAR(100) NOT NULL,
    tag_color VARCHAR(7) NULL,
    tag_description TEXT NULL,
    
    -- Display
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Tag Assignments Table (Junction)
CREATE TABLE IF NOT EXISTS customer_tag_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    
    -- Assignment Details
    assigned_at DATETIME NOT NULL,
    assigned_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_customer_id (customer_id),
    INDEX idx_tag_id (tag_id),
    UNIQUE KEY unique_assignment (customer_id, tag_id),
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES customer_tags(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Visit History Table
CREATE TABLE IF NOT EXISTS customer_visits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Visit Details
    visit_date DATETIME NOT NULL,
    party_size INT NULL,
    table_id BIGINT UNSIGNED NULL,
    
    -- Order Details
    order_id BIGINT UNSIGNED NULL,
    order_count INT DEFAULT 0,
    
    -- Financials
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    
    -- Staff
    served_by BIGINT UNSIGNED NULL,
    
    -- Rating
    rating INT NULL, -- 1-5 stars
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_customer_id (customer_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_visit_date (visit_date),
    INDEX idx_order_id (order_id),
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (served_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate customer code trigger
DELIMITER //
CREATE TRIGGER generate_customer_code
BEFORE INSERT ON customers
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(customer_code, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM customers
    WHERE restaurant_id = NEW.restaurant_id
    AND DATE(created_at) = CURDATE();
    
    SET NEW.customer_code = CONCAT('CUST', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Update customer last visit trigger
DELIMITER //
CREATE TRIGGER update_customer_last_visit
AFTER INSERT ON customer_visits
FOR EACH NEW
BEGIN
    UPDATE customers
    SET last_visit_at = NEW.visit_date
    WHERE id = NEW.customer_id;
END//
DELIMITER ;

-- Insert default customer tags for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_customer_tags
AFTER INSERT ON restaurants
FOR EACH NEW
BEGIN
    -- VIP
    INSERT INTO customer_tags (restaurant_id, tag_name, tag_color, tag_description, sort_order, is_active)
    VALUES (NEW.id, 'VIP', '#FFD700', 'Very Important Customer', 1, TRUE);
    
    -- Regular
    INSERT INTO customer_tags (restaurant_id, tag_name, tag_color, tag_description, sort_order, is_active)
    VALUES (NEW.id, 'Regular', '#4CAF50', 'Regular Customer', 2, TRUE);
    
    -- New
    INSERT INTO customer_tags (restaurant_id, tag_name, tag_color, tag_description, sort_order, is_active)
    VALUES (NEW.id, 'New', '#2196F3', 'New Customer', 3, TRUE);
    
    -- Birthday
    INSERT INTO customer_tags (restaurant_id, tag_name, tag_color, tag_description, sort_order, is_active)
    VALUES (NEW.id, 'Birthday', '#E91E63', 'Birthday Month', 4, TRUE);
END//
DELIMITER ;
