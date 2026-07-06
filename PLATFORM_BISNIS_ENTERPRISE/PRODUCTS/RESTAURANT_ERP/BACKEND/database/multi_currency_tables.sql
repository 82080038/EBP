-- Multi-Currency Support Tables

-- Exchange rates table
CREATE TABLE IF NOT EXISTS exchange_rates (
    exchange_rate_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    rate DECIMAL(15, 8) NOT NULL,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,
    UNIQUE KEY uk_tenant_from_to_date (tenant_id, from_currency, to_currency, effective_date),
    INDEX idx_tenant_currency (tenant_id, from_currency, to_currency),
    INDEX idx_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Currencies table
CREATE TABLE IF NOT EXISTS currencies (
    currency_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    currency_code VARCHAR(3) NOT NULL UNIQUE,
    currency_name VARCHAR(100) NOT NULL,
    currency_symbol VARCHAR(10) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default currencies
INSERT INTO currencies (currency_code, currency_name, currency_symbol, is_active) VALUES
('IDR', 'Indonesian Rupiah', 'Rp', TRUE),
('USD', 'United States Dollar', '$', TRUE),
('EUR', 'Euro', '€', TRUE),
('SGD', 'Singapore Dollar', 'S$', TRUE),
('MYR', 'Malaysian Ringgit', 'RM', TRUE),
('THB', 'Thai Baht', '฿', TRUE),
('JPY', 'Japanese Yen', '¥', TRUE),
('CNY', 'Chinese Yuan', '¥', TRUE),
('GBP', 'British Pound', '£', TRUE),
('AUD', 'Australian Dollar', 'A$', TRUE)
ON DUPLICATE KEY UPDATE currency_name = VALUES(currency_name), currency_symbol = VALUES(currency_symbol);
