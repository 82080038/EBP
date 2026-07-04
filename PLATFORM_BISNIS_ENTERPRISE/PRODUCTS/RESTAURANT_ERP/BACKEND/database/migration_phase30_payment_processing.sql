-- Migration Phase 30: Payment Processing
-- Provides comprehensive payment management with multiple payment methods, refunds, and integration

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    
    -- Payment Details
    payment_number VARCHAR(50) NOT NULL,
    payment_method ENUM('cash', 'card', 'e_wallet', 'bank_transfer', 'voucher', 'other') NOT NULL,
    payment_status ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'partial_refund') DEFAULT 'pending',
    
    -- Amount
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'IDR',
    
    -- Payment Gateway
    payment_gateway VARCHAR(50) NULL,
    gateway_transaction_id VARCHAR(255) NULL,
    gateway_response JSON NULL,
    
    -- Card Details (encrypted)
    card_last_four VARCHAR(4) NULL,
    card_brand VARCHAR(50) NULL,
    
    -- E-Wallet Details
    e_wallet_provider VARCHAR(50) NULL,
    e_wallet_phone VARCHAR(50) NULL,
    
    -- Bank Transfer Details
    bank_name VARCHAR(100) NULL,
    account_number VARCHAR(50) NULL,
    account_name VARCHAR(255) NULL,
    
    -- Voucher Details
    voucher_code VARCHAR(50) NULL,
    voucher_amount DECIMAL(15,2) NULL,
    
    -- Timing
    processed_at DATETIME NULL,
    completed_at DATETIME NULL,
    failed_at DATETIME NULL,
    
    -- Staff
    processed_by BIGINT UNSIGNED NULL,
    
    -- Notes
    notes TEXT NULL,
    failure_reason TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_order_id (order_id),
    INDEX idx_payment_number (payment_number),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_method (payment_method),
    INDEX idx_gateway_transaction_id (gateway_transaction_id),
    INDEX idx_created_at (created_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Refunds Table
CREATE TABLE IF NOT EXISTS payment_refunds (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    
    -- Refund Details
    refund_number VARCHAR(50) NOT NULL,
    refund_amount DECIMAL(15,2) NOT NULL,
    refund_reason TEXT NOT NULL,
    
    -- Status
    refund_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    
    -- Gateway
    gateway_refund_id VARCHAR(255) NULL,
    gateway_response JSON NULL,
    
    -- Timing
    requested_at DATETIME NOT NULL,
    processed_at DATETIME NULL,
    
    -- Staff
    requested_by BIGINT UNSIGNED NOT NULL,
    processed_by BIGINT UNSIGNED NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_order_id (order_id),
    INDEX idx_refund_number (refund_number),
    INDEX idx_refund_status (refund_status),
    INDEX idx_requested_at (requested_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Methods Table (restaurant-specific)
CREATE TABLE IF NOT EXISTS payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Method Details
    method_name VARCHAR(100) NOT NULL,
    method_type ENUM('cash', 'card', 'e_wallet', 'bank_transfer', 'voucher', 'other') NOT NULL,
    method_code VARCHAR(50) NOT NULL,
    
    -- Configuration
    gateway_config JSON NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    
    -- Display
    display_order INT DEFAULT 0,
    icon_url VARCHAR(255) NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_method_type (method_type),
    INDEX idx_is_active (is_active),
    UNIQUE KEY unique_method_code (restaurant_id, method_code),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Transactions Log Table
CREATE TABLE IF NOT EXISTS payment_transaction_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NULL,
    
    -- Transaction Details
    transaction_type ENUM('payment', 'refund', 'void', 'capture', 'other') NOT NULL,
    transaction_status ENUM('success', 'failed', 'pending') NOT NULL,
    
    -- Gateway
    gateway VARCHAR(50) NULL,
    gateway_request JSON NULL,
    gateway_response JSON NULL,
    
    -- Amount
    amount DECIMAL(15,2) NULL,
    
    -- Timing
    processed_at DATETIME NOT NULL,
    
    -- Error Details
    error_code VARCHAR(50) NULL,
    error_message TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_status (transaction_status),
    INDEX idx_processed_at (processed_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tip Management Table
CREATE TABLE IF NOT EXISTS tips (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NULL,
    
    -- Tip Details
    tip_amount DECIMAL(15,2) NOT NULL,
    tip_type ENUM('percentage', 'fixed') NOT NULL,
    tip_percentage DECIMAL(5,2) NULL,
    
    -- Distribution
    distribution_method ENUM('evenly', 'custom', 'to_server') NOT NULL,
    distribution_config JSON NULL,
    
    -- Staff
    staff_id BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_order_id (order_id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_staff_id (staff_id),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate payment number trigger
DELIMITER //
CREATE TRIGGER generate_payment_number
BEFORE INSERT ON payments
FOR EACH ROW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(payment_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM payments
    WHERE restaurant_id = NEW.restaurant_id
    AND DATE(created_at) = CURDATE();
    
    SET NEW.payment_number = CONCAT('PAY', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Generate refund number trigger
DELIMITER //
CREATE TRIGGER generate_refund_number
BEFORE INSERT ON payment_refunds
FOR EACH ROW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(refund_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM payment_refunds
    WHERE restaurant_id = NEW.restaurant_id
    AND DATE(created_at) = CURDATE();
    
    SET NEW.refund_number = CONCAT('REF', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Insert default payment methods for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_payment_methods
AFTER INSERT ON restaurants
FOR EACH ROW
BEGIN
    -- Cash
    INSERT INTO payment_methods (restaurant_id, method_name, method_type, method_code, is_active, is_default, display_order)
    VALUES (NEW.id, 'Cash', 'cash', 'CASH', TRUE, TRUE, 1);
    
    -- Card
    INSERT INTO payment_methods (restaurant_id, method_name, method_type, method_code, is_active, display_order)
    VALUES (NEW.id, 'Credit/Debit Card', 'card', 'CARD', TRUE, 2);
    
    -- E-Wallet
    INSERT INTO payment_methods (restaurant_id, method_name, method_type, method_code, is_active, display_order)
    VALUES (NEW.id, 'E-Wallet', 'e_wallet', 'EWALLET', TRUE, 3);
    
    -- Bank Transfer
    INSERT INTO payment_methods (restaurant_id, method_name, method_type, method_code, is_active, display_order)
    VALUES (NEW.id, 'Bank Transfer', 'bank_transfer', 'TRANSFER', TRUE, 4);
    
    -- Voucher
    INSERT INTO payment_methods (restaurant_id, method_name, method_type, method_code, is_active, display_order)
    VALUES (NEW.id, 'Voucher', 'voucher', 'VOUCHER', TRUE, 5);
END//
DELIMITER ;
