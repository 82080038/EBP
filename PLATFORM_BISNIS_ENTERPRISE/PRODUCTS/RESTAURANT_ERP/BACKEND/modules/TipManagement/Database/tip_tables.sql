-- Tip Management Tables

CREATE TABLE IF NOT EXISTS tips (
    tip_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT,
    user_id INT NOT NULL,
    order_id INT,
    tip_date DATE NOT NULL,
    tip_amount DECIMAL(15,2) NOT NULL,
    tip_type VARCHAR(20) DEFAULT 'cash' COMMENT 'cash, card, digital',
    payment_method VARCHAR(50),
    recorded_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_user (user_id),
    INDEX idx_date (tip_date),
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
