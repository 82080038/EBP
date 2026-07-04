-- Migration Phase 48: International Expansion
-- Provides multi-currency, multi-language, and multi-region support for international operations

-- Currencies Table
CREATE TABLE IF NOT EXISTS currencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Currency Details
    currency_code VARCHAR(3) NOT NULL UNIQUE,
    currency_name VARCHAR(100) NOT NULL,
    currency_symbol VARCHAR(10) NOT NULL,
    
    -- Exchange Rate
    exchange_rate DECIMAL(15,6) NOT NULL,
    base_currency VARCHAR(3) DEFAULT 'USD',
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Languages Table
CREATE TABLE IF NOT EXISTS languages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Language Details
    language_code VARCHAR(10) NOT NULL UNIQUE,
    language_name VARCHAR(100) NOT NULL,
    native_name VARCHAR(100) NOT NULL,
    
    -- Direction
    text_direction ENUM('ltr', 'rtl') DEFAULT 'ltr',
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Countries Table
CREATE TABLE IF NOT EXISTS countries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Country Details
    country_code VARCHAR(3) NOT NULL UNIQUE,
    country_name VARCHAR(100) NOT NULL,
    
    -- Currency
    currency_id BIGINT UNSIGNED NOT NULL,
    
    -- Language
    default_language_id BIGINT UNSIGNED NOT NULL,
    
    -- Region
    region VARCHAR(100) NULL,
    
    -- Tax
    vat_rate DECIMAL(5,2) DEFAULT 0.00,
    tax_id_required BOOLEAN DEFAULT FALSE,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_currency_id (currency_id),
    INDEX idx_default_language_id (default_language_id),
    
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
    FOREIGN KEY (default_language_id) REFERENCES languages(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restaurant Countries Table (Multi-location support)
CREATE TABLE IF NOT EXISTS restaurant_countries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    country_id BIGINT UNSIGNED NOT NULL,
    
    -- Configuration
    is_primary BOOLEAN DEFAULT FALSE,
    
    -- Local Settings
    local_currency_id BIGINT UNSIGNED NOT NULL,
    local_language_id BIGINT UNSIGNED NOT NULL,
    
    -- Tax Settings
    tax_registration_number VARCHAR(100) NULL,
    vat_number VARCHAR(100) NULL,
    
    -- Legal
    legal_entity_name VARCHAR(255) NULL,
    business_address TEXT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_country_id (country_id),
    INDEX idx_local_currency_id (local_currency_id),
    INDEX idx_local_language_id (local_language_id),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    FOREIGN KEY (local_currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
    FOREIGN KEY (local_language_id) REFERENCES languages(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exchange Rate History Table
CREATE TABLE IF NOT EXISTS exchange_rate_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    currency_id BIGINT UNSIGNED NOT NULL,
    
    -- Rate Details
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    exchange_rate DECIMAL(15,6) NOT NULL,
    
    -- Source
    rate_source VARCHAR(100) NULL,
    
    -- Timestamp
    effective_date DATE NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_currency_id (currency_id),
    INDEX idx_from_currency (from_currency),
    INDEX idx_to_currency (to_currency),
    INDEX idx_effective_date (effective_date),
    
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Translation Keys Table
CREATE TABLE IF NOT EXISTS translation_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Key Details
    key_name VARCHAR(255) NOT NULL UNIQUE,
    key_category VARCHAR(100) NOT NULL,
    key_description TEXT NULL,
    
    -- Default Value
    default_value TEXT NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Translations Table
CREATE TABLE IF NOT EXISTS translations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    translation_key_id BIGINT UNSIGNED NOT NULL,
    language_id BIGINT UNSIGNED NOT NULL,
    
    -- Translation
    translated_value TEXT NOT NULL,
    
    -- Status
    is_approved BOOLEAN DEFAULT FALSE,
    
    -- Staff
    translated_by BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_translation_key_id (translation_key_id),
    INDEX idx_language_id (language_id),
    UNIQUE KEY unique_translation (translation_key_id, language_id),
    
    FOREIGN KEY (translation_key_id) REFERENCES translation_keys(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    FOREIGN KEY (translated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Regional Compliance Table
CREATE TABLE IF NOT EXISTS regional_compliance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    country_id BIGINT UNSIGNED NOT NULL,
    
    -- Compliance Type
    compliance_type ENUM('data_protection', 'tax', 'labor', 'health_safety', 'environmental', 'other') NOT NULL,
    
    -- Requirements
    compliance_requirements TEXT NOT NULL,
    
    -- Status
    compliance_status ENUM('compliant', 'non_compliant', 'pending_review', 'exempt') DEFAULT 'pending_review',
    
    -- Documents
    compliance_document_url VARCHAR(255) NULL,
    
    -- Review
    last_review_date DATE NULL,
    next_review_date DATE NULL,
    
    -- Staff
    reviewed_by BIGINT UNSIGNED NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_country_id (country_id),
    INDEX idx_compliance_type (compliance_type),
    INDEX idx_compliance_status (compliance_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default currencies
INSERT INTO currencies (currency_code, currency_name, currency_symbol, exchange_rate, base_currency) VALUES
('USD', 'US Dollar', '$', 1.000000, 'USD'),
('EUR', 'Euro', '€', 0.850000, 'USD'),
('GBP', 'British Pound', '£', 0.730000, 'USD'),
('JPY', 'Japanese Yen', '¥', 110.000000, 'USD'),
('IDR', 'Indonesian Rupiah', 'Rp', 14000.000000, 'USD'),
('SGD', 'Singapore Dollar', 'S$', 1.350000, 'USD'),
('MYR', 'Malaysian Ringgit', 'RM', 4.200000, 'USD'),
('THB', 'Thai Baht', '฿', 33.000000, 'USD'),
('AUD', 'Australian Dollar', 'A$', 1.300000, 'USD'),
('CNY', 'Chinese Yuan', '¥', 6.500000, 'USD');

-- Insert default languages
INSERT INTO languages (language_code, language_name, native_name, text_direction) VALUES
('en', 'English', 'English', 'ltr'),
('id', 'Indonesian', 'Bahasa Indonesia', 'ltr'),
('zh', 'Chinese', '中文', 'ltr'),
('ja', 'Japanese', '日本語', 'ltr'),
('ko', 'Korean', '한국어', 'ltr'),
('th', 'Thai', 'ไทย', 'ltr'),
('ms', 'Malay', 'Bahasa Melayu', 'ltr'),
('vi', 'Vietnamese', 'Tiếng Việt', 'ltr'),
('ar', 'Arabic', 'العربية', 'rtl'),
('es', 'Spanish', 'Español', 'ltr');

-- Insert default countries
INSERT INTO countries (country_code, country_name, currency_id, default_language_id, region, vat_rate, tax_id_required) VALUES
('US', 'United States', 1, 1, 'North America', 0.00, TRUE),
('ID', 'Indonesia', 5, 2, 'Asia', 10.00, TRUE),
('SG', 'Singapore', 6, 1, 'Asia', 7.00, TRUE),
('MY', 'Malaysia', 7, 7, 'Asia', 6.00, TRUE),
('TH', 'Thailand', 8, 5, 'Asia', 7.00, TRUE),
('AU', 'Australia', 9, 1, 'Oceania', 10.00, TRUE),
('CN', 'China', 10, 3, 'Asia', 13.00, TRUE),
('JP', 'Japan', 4, 4, 'Asia', 10.00, TRUE),
('GB', 'United Kingdom', 3, 1, 'Europe', 20.00, TRUE),
('DE', 'Germany', 2, 1, 'Europe', 19.00, TRUE);
