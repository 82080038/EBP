-- Migration Phase 36: Reservations & Waitlist
-- Provides comprehensive reservation and waitlist management with table allocation and notifications

-- Reservations Table
CREATE TABLE IF NOT EXISTS reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    
    -- Reservation Details
    reservation_number VARCHAR(50) NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    party_size INT NOT NULL,
    
    -- Table Assignment
    table_id BIGINT UNSIGNED NULL,
    table_number VARCHAR(50) NULL,
    
    -- Contact
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_email VARCHAR(255) NULL,
    
    -- Special Requests
    special_requests TEXT NULL,
    dietary_restrictions TEXT NULL,
    occasion VARCHAR(100) NULL, -- birthday, anniversary, business, etc.
    
    -- Status
    reservation_status ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    
    -- Confirmation
    is_confirmed BOOLEAN DEFAULT FALSE,
    confirmed_at DATETIME NULL,
    confirmed_by BIGINT UNSIGNED NULL,
    confirmation_method ENUM('phone', 'email', 'sms', 'app', 'other') NULL,
    
    -- Timing
    estimated_duration INT NULL, -- in minutes
    actual_arrival_time DATETIME NULL,
    seated_at DATETIME NULL,
    completed_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    modified_by BIGINT UNSIGNED NULL,
    
    -- Notes
    internal_notes TEXT NULL,
    cancellation_reason TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_reservation_number (reservation_number),
    INDEX idx_reservation_date (reservation_date),
    INDEX idx_reservation_time (reservation_time),
    INDEX idx_reservation_status (reservation_status),
    INDEX idx_table_id (table_id),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (modified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waitlist Table
CREATE TABLE IF NOT EXISTS waitlist (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    
    -- Waitlist Details
    waitlist_number VARCHAR(50) NOT NULL,
    party_size INT NOT NULL,
    
    -- Contact
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_email VARCHAR(255) NULL,
    
    -- Preferences
    preferred_table_type ENUM('standard', 'booth', 'outdoor', 'private', 'any') DEFAULT 'any',
    preferred_area VARCHAR(100) NULL,
    
    -- Special Requests
    special_requests TEXT NULL,
    
    -- Status
    waitlist_status ENUM('waiting', 'notified', 'seated', 'cancelled', 'no_show') DEFAULT 'waiting',
    
    -- Timing
    joined_at DATETIME NOT NULL,
    estimated_wait_time INT NULL, -- in minutes
    notified_at DATETIME NULL,
    seated_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    modified_by BIGINT UNSIGNED NULL,
    
    -- Notes
    internal_notes TEXT NULL,
    cancellation_reason TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_waitlist_number (waitlist_number),
    INDEX idx_waitlist_status (waitlist_status),
    INDEX idx_joined_at (joined_at),
    INDEX idx_party_size (party_size),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (modified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reservation History Table
CREATE TABLE IF NOT EXISTS reservation_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- History Details
    action_type ENUM('created', 'confirmed', 'modified', 'cancelled', 'seated', 'completed', 'no_show', 'table_changed') NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NULL,
    
    -- Details
    action_details JSON NULL,
    
    -- Staff
    performed_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    performed_at DATETIME NOT NULL,
    
    -- Indexes
    INDEX idx_reservation_id (reservation_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_action_type (action_type),
    INDEX idx_performed_at (performed_at),
    
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Availability Table
CREATE TABLE IF NOT EXISTS table_availability (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    table_id BIGINT UNSIGNED NOT NULL,
    
    -- Availability Details
    availability_date DATE NOT NULL,
    availability_time TIME NOT NULL,
    
    -- Status
    is_available BOOLEAN DEFAULT TRUE,
    
    -- Reservation
    reservation_id BIGINT UNSIGNED NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_table_id (table_id),
    INDEX idx_availability_date (availability_date),
    INDEX idx_availability_time (availability_time),
    INDEX idx_is_available (is_available),
    UNIQUE KEY unique_table_time (table_id, availability_date, availability_time),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reservation Notifications Table
CREATE TABLE IF NOT EXISTS reservation_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    reservation_id BIGINT UNSIGNED NULL,
    waitlist_id BIGINT UNSIGNED NULL,
    
    -- Notification Details
    notification_type ENUM('confirmation', 'reminder', 'cancellation', 'table_ready', 'waitlist_update', 'other') NOT NULL,
    notification_method ENUM('email', 'sms', 'app', 'other') NOT NULL,
    
    -- Recipient
    recipient_name VARCHAR(255) NOT NULL,
    recipient_email VARCHAR(255) NULL,
    recipient_phone VARCHAR(50) NULL,
    
    -- Content
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    
    -- Status
    notification_status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    
    -- Timing
    scheduled_at DATETIME NULL,
    sent_at DATETIME NULL,
    delivered_at DATETIME NULL,
    
    -- Error
    error_message TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_reservation_id (reservation_id),
    INDEX idx_waitlist_id (waitlist_id),
    INDEX idx_notification_type (notification_type),
    INDEX idx_notification_status (notification_status),
    INDEX idx_scheduled_at (scheduled_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    FOREIGN KEY (waitlist_id) REFERENCES waitlist(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate reservation number trigger
DELIMITER //
CREATE TRIGGER generate_reservation_number
BEFORE INSERT ON reservations
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(reservation_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM reservations
    WHERE restaurant_id = NEW.restaurant_id
    AND DATE(reservation_date) = CURDATE();
    
    SET NEW.reservation_number = CONCAT('RES', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Generate waitlist number trigger
DELIMITER //
CREATE TRIGGER generate_waitlist_number
BEFORE INSERT ON waitlist
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(waitlist_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM waitlist
    WHERE restaurant_id = NEW.restaurant_id
    AND DATE(joined_at) = CURDATE();
    
    SET NEW.waitlist_number = CONCAT('WL', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Log reservation history trigger
DELIMITER //
CREATE TRIGGER log_reservation_history
AFTER UPDATE ON reservations
FOR EACH NEW
BEGIN
    IF OLD.reservation_status != NEW.reservation_status THEN
        INSERT INTO reservation_history (reservation_id, restaurant_id, action_type, old_status, new_status, performed_by, performed_at)
        VALUES (NEW.id, NEW.restaurant_id, 'status_change', OLD.reservation_status, NEW.reservation_status, NEW.modified_by, NOW());
    END IF;
    
    IF OLD.table_id != NEW.table_id THEN
        INSERT INTO reservation_history (reservation_id, restaurant_id, action_type, action_details, performed_by, performed_at)
        VALUES (NEW.id, NEW.restaurant_id, 'table_changed', JSON_OBJECT('old_table_id', OLD.table_id, 'new_table_id', NEW.table_id), NEW.modified_by, NOW());
    END IF;
END//
DELIMITER ;
