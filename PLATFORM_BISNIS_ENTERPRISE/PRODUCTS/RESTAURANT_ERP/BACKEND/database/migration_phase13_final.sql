-- Migration for Final Remaining Features

-- Supplier Performance
CREATE TABLE IF NOT EXISTS supplier_performance (
    performance_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    evaluation_date DATE NOT NULL,
    on_time_delivery_rate DECIMAL(5,2),
    quality_score DECIMAL(5,2),
    price_competitiveness DECIMAL(5,2),
    overall_rating DECIMAL(5,2),
    notes TEXT,
    evaluated_by BIGINT UNSIGNED,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (performance_id),
    KEY idx_supplier_performance_tenant_id (tenant_id),
    KEY idx_supplier_performance_supplier_id (supplier_id),
    KEY idx_supplier_performance_date (evaluation_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (evaluated_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Currencies
CREATE TABLE IF NOT EXISTS currencies (
    currency_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    currency_code VARCHAR(3) NOT NULL,
    currency_name VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    exchange_rate DECIMAL(15,6) NOT NULL DEFAULT 1.000000,
    is_base BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (currency_id),
    UNIQUE KEY idx_currencies_code (currency_code),
    KEY idx_currencies_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default currencies
INSERT INTO currencies (currency_code, currency_name, symbol, exchange_rate, is_base, is_active) VALUES 
('IDR', 'Indonesian Rupiah', 'Rp', 1.000000, TRUE, TRUE),
('USD', 'US Dollar', '$', 15000.000000, FALSE, TRUE),
('EUR', 'Euro', '€', 16500.000000, FALSE, TRUE),
('SGD', 'Singapore Dollar', 'S$', 11000.000000, FALSE, TRUE)
ON DUPLICATE KEY UPDATE currency_name=VALUES(currency_name), symbol=VALUES(symbol);

-- AI Predictions
CREATE TABLE IF NOT EXISTS ai_predictions (
    prediction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    prediction_type VARCHAR(50) NOT NULL,
    prediction_date DATE NOT NULL,
    prediction_data JSON NOT NULL,
    confidence_score DECIMAL(5,2),
    model_version VARCHAR(50),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (prediction_id),
    KEY idx_ai_predictions_tenant_id (tenant_id),
    KEY idx_ai_predictions_branch_id (branch_id),
    KEY idx_ai_predictions_type (prediction_type),
    KEY idx_ai_predictions_date (prediction_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
