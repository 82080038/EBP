-- Migration Phase 35: Feedback & Reviews
-- Provides comprehensive feedback and review management with ratings, responses, and analytics

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    order_id BIGINT UNSIGNED NULL,
    
    -- Review Details
    rating INT NOT NULL, -- 1-5 stars
    title VARCHAR(255) NULL,
    review_text TEXT NULL,
    
    -- Source
    review_source ENUM('internal', 'google', 'tripadvisor', 'delivery_app', 'social_media', 'other') DEFAULT 'internal',
    external_review_id VARCHAR(255) NULL,
    external_source VARCHAR(100) NULL,
    
    -- Status
    review_status ENUM('pending', 'approved', 'rejected', 'flagged') DEFAULT 'pending',
    
    -- Visibility
    is_public BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_order_id (order_id),
    INDEX idx_rating (rating),
    INDEX idx_review_status (review_status),
    INDEX idx_review_source (review_source),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review Categories Table
CREATE TABLE IF NOT EXISTS review_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Category Details
    category_name VARCHAR(100) NOT NULL,
    category_description TEXT NULL,
    
    -- Display
    icon_url VARCHAR(255) NULL,
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

-- Review Ratings Table (detailed ratings per category)
CREATE TABLE IF NOT EXISTS review_ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    
    -- Rating
    rating INT NOT NULL, -- 1-5 stars
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_review_id (review_id),
    INDEX idx_category_id (category_id),
    UNIQUE KEY unique_review_category (review_id, category_id),
    
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES review_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review Responses Table
CREATE TABLE IF NOT EXISTS review_responses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Response Details
    response_text TEXT NOT NULL,
    
    -- Staff
    responded_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    responded_at DATETIME NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_review_id (review_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_responded_at (responded_at),
    
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review Flags Table
CREATE TABLE IF NOT EXISTS review_flags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Flag Details
    flag_reason ENUM('inappropriate', 'spam', 'fake', 'offensive', 'other') NOT NULL,
    flag_description TEXT NULL,
    
    -- Staff
    flagged_by BIGINT UNSIGNED NOT NULL,
    
    -- Status
    is_resolved BOOLEAN DEFAULT FALSE,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_review_id (review_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_is_resolved (is_resolved),
    
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (flagged_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback Table (general feedback not tied to reviews)
CREATE TABLE IF NOT EXISTS feedback (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    
    -- Feedback Details
    feedback_type ENUM('complaint', 'compliment', 'suggestion', 'question', 'other') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    -- Contact
    contact_email VARCHAR(255) NULL,
    contact_phone VARCHAR(50) NULL,
    
    -- Source
    feedback_source ENUM('website', 'app', 'in_person', 'email', 'phone', 'other') DEFAULT 'website',
    
    -- Status
    feedback_status ENUM('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
    
    -- Priority
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    
    -- Staff
    assigned_to BIGINT UNSIGNED NULL,
    
    -- Timing
    resolved_at DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_feedback_type (feedback_type),
    INDEX idx_feedback_status (feedback_status),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback Comments Table
CREATE TABLE IF NOT EXISTS feedback_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    feedback_id BIGINT UNSIGNED NOT NULL,
    
    -- Comment Details
    comment_text TEXT NOT NULL,
    
    -- Staff
    commented_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_feedback_id (feedback_id),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE CASCADE,
    FOREIGN KEY (commented_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default review categories for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_review_categories
AFTER INSERT ON restaurants
FOR EACH NEW
BEGIN
    -- Food Quality
    INSERT INTO review_categories (restaurant_id, category_name, category_description, icon_url, sort_order, is_active)
    VALUES (NEW.id, 'Food Quality', 'Quality and taste of food', NULL, 1, TRUE);
    
    -- Service
    INSERT INTO review_categories (restaurant_id, category_name, category_description, icon_url, sort_order, is_active)
    VALUES (NEW.id, 'Service', 'Quality of service provided', NULL, 2, TRUE);
    
    -- Ambiance
    INSERT INTO review_categories (restaurant_id, category_name, category_description, icon_url, sort_order, is_active)
    VALUES (NEW.id, 'Ambiance', 'Atmosphere and environment', NULL, 3, TRUE);
    
    -- Value
    INSERT INTO review_categories (restaurant_id, category_name, category_description, icon_url, sort_order, is_active)
    VALUES (NEW.id, 'Value', 'Value for money', NULL, 4, TRUE);
    
    -- Cleanliness
    INSERT INTO review_categories (restaurant_id, category_name, category_description, icon_url, sort_order, is_active)
    VALUES (NEW.id, 'Cleanliness', 'Cleanliness and hygiene', NULL, 5, TRUE);
END//
DELIMITER ;
