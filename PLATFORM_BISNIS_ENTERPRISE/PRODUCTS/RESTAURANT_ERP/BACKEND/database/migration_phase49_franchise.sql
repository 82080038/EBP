-- Migration Phase 49: Franchise Management
-- Provides comprehensive franchise management with franchisees, agreements, and performance tracking

-- Franchisees Table
CREATE TABLE IF NOT EXISTS franchisees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Franchisee Details
    franchisee_name VARCHAR(255) NOT NULL,
    franchisee_code VARCHAR(50) NOT NULL,
    
    -- Contact
    contact_person VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NULL,
    country VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    
    -- Business
    business_name VARCHAR(255) NOT NULL,
    tax_id VARCHAR(50) NULL,
    business_license VARCHAR(100) NULL,
    
    -- Status
    franchisee_status ENUM('prospect', 'active', 'suspended', 'terminated', 'graduated') DEFAULT 'prospect',
    
    -- Notes
    notes TEXT NULL,
    
    -- Staff
    assigned_manager BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_franchisee_code (franchisee_code),
    INDEX idx_franchisee_status (franchisee_status),
    INDEX idx_assigned_manager (assigned_manager),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_manager) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Franchise Agreements Table
CREATE TABLE IF NOT EXISTS franchise_agreements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    franchisee_id BIGINT UNSIGNED NOT NULL,
    
    -- Agreement Details
    agreement_number VARCHAR(50) NOT NULL,
    agreement_type ENUM('master', 'unit', 'area', 'regional') NOT NULL,
    
    -- Period
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    
    -- Territory
    territory_description TEXT NULL,
    territory_exclusive BOOLEAN DEFAULT FALSE,
    
    -- Financials
    franchise_fee DECIMAL(15,2) NOT NULL,
    royalty_rate DECIMAL(5,2) DEFAULT 0.00,
    marketing_fee_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Terms
    agreement_terms TEXT NOT NULL,
    
    -- Status
    agreement_status ENUM('draft', 'active', 'expired', 'terminated', 'renewed') DEFAULT 'draft',
    
    -- Documents
    agreement_document_url VARCHAR(255) NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_franchisee_id (franchisee_id),
    INDEX idx_agreement_number (agreement_number),
    INDEX idx_agreement_status (agreement_status),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (franchisee_id) REFERENCES franchisees(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Franchise Performance Table
CREATE TABLE IF NOT EXISTS franchise_performance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    franchisee_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    evaluation_period_start DATE NOT NULL,
    evaluation_period_end DATE NOT NULL,
    
    -- Sales Metrics
    total_revenue DECIMAL(15,2) DEFAULT 0.00,
    gross_margin DECIMAL(15,2) DEFAULT 0.00,
    net_profit DECIMAL(15,2) DEFAULT 0.00,
    
    -- Operational Metrics
    customer_satisfaction_score DECIMAL(3,2) DEFAULT 0.00,
    food_quality_score DECIMAL(3,2) DEFAULT 0.00,
    service_quality_score DECIMAL(3,2) DEFAULT 0.00,
    
    -- Compliance Metrics
    brand_compliance_score DECIMAL(3,2) DEFAULT 0.00,
    operational_compliance_score DECIMAL(3,2) DEFAULT 0.00,
    
    -- Overall
    overall_score DECIMAL(3,2) DEFAULT 0.00,
    performance_rating ENUM('excellent', 'good', 'satisfactory', 'needs_improvement', 'poor') NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_franchisee_id (franchisee_id),
    INDEX idx_evaluation_period_start (evaluation_period_start),
    INDEX idx_overall_score (overall_score),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (franchisee_id) REFERENCES franchisees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Franchise Royalties Table
CREATE TABLE IF NOT EXISTS franchise_royalties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    franchisee_id BIGINT UNSIGNED NOT NULL,
    agreement_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    royalty_period_start DATE NOT NULL,
    royalty_period_end DATE NOT NULL,
    
    -- Revenue
    gross_revenue DECIMAL(15,2) NOT NULL,
    
    -- Calculations
    royalty_amount DECIMAL(15,2) NOT NULL,
    marketing_fee_amount DECIMAL(15,2) DEFAULT 0.00,
    total_due DECIMAL(15,2) NOT NULL,
    
    -- Payment
    payment_status ENUM('pending', 'partial', 'paid', 'overdue', 'waived') DEFAULT 'pending',
    payment_date DATE NULL,
    payment_reference VARCHAR(100) NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_franchisee_id (franchisee_id),
    INDEX idx_agreement_id (agreement_id),
    INDEX idx_royalty_period_start (royalty_period_start),
    INDEX idx_payment_status (payment_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (franchisee_id) REFERENCES franchisees(id) ON DELETE CASCADE,
    FOREIGN KEY (agreement_id) REFERENCES franchise_agreements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Franchise Support Tickets Table
CREATE TABLE IF NOT EXISTS franchise_support_tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    franchisee_id BIGINT UNSIGNED NOT NULL,
    
    -- Ticket Details
    ticket_number VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Category
    category ENUM('operational', 'marketing', 'financial', 'hr', 'it', 'legal', 'other') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    
    -- Status
    ticket_status ENUM('open', 'in_progress', 'resolved', 'closed', 'escalated') DEFAULT 'open',
    
    -- Assignment
    assigned_to BIGINT UNSIGNED NULL,
    
    -- Resolution
    resolution TEXT NULL,
    resolved_at DATETIME NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_franchisee_id (franchisee_id),
    INDEX idx_ticket_number (ticket_number),
    INDEX idx_category (category),
    INDEX idx_ticket_status (ticket_status),
    INDEX idx_assigned_to (assigned_to),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (franchisee_id) REFERENCES franchisees(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generate franchisee code trigger
DELIMITER //
CREATE TRIGGER generate_franchisee_code
BEFORE INSERT ON franchisees
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(franchisee_code, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM franchisees
    WHERE restaurant_id = NEW.restaurant_id;
    
    SET NEW.franchisee_code = CONCAT('FRN', LPAD(next_number, 6, '0'));
END//
DELIMITER ;

-- Generate agreement number trigger
DELIMITER //
CREATE TRIGGER generate_franchise_agreement_number
BEFORE INSERT ON franchise_agreements
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(agreement_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM franchise_agreements
    WHERE restaurant_id = NEW.restaurant_id;
    
    SET NEW.agreement_number = CONCAT('FAG', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;

-- Generate support ticket number trigger
DELIMITER //
CREATE TRIGGER generate_support_ticket_number
BEFORE INSERT ON franchise_support_tickets
FOR EACH NEW
BEGIN
    DECLARE next_number INT;
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(ticket_number, 4) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM franchise_support_tickets
    WHERE restaurant_id = NEW.restaurant_id;
    
    SET NEW.ticket_number = CONCAT('TCK', DATE_FORMAT(CURDATE(), '%Y%m%d'), LPAD(next_number, 4, '0'));
END//
DELIMITER ;
