-- Food Waste Tracking Tables

CREATE TABLE IF NOT EXISTS food_waste (
    waste_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT,
    waste_date DATE NOT NULL,
    waste_type VARCHAR(50) NOT NULL COMMENT 'spoilage, preparation_error, overproduction, expired, customer_return, etc',
    inventory_item_id INT,
    quantity DECIMAL(10,3) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    reason TEXT NOT NULL,
    cost_per_unit DECIMAL(15,2) DEFAULT 0.00,
    total_cost DECIMAL(15,2) DEFAULT 0.00,
    recorded_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_date (waste_date),
    INDEX idx_type (waste_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
