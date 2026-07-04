-- Migration for Final Remaining Features

-- Inventory Repurposing
CREATE TABLE IF NOT EXISTS inventory_repurposing (
    repurposing_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    repurposing_date DATE NOT NULL,
    from_product_id BIGINT UNSIGNED NOT NULL,
    to_product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50),
    conversion_ratio DECIMAL(10,2),
    notes TEXT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (repurposing_id),
    KEY idx_inventory_repurposing_tenant_id (tenant_id),
    KEY idx_inventory_repurposing_branch_id (branch_id),
    KEY idx_inventory_repurposing_date (repurposing_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (from_product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (to_product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inter-Outlet Stock Transfer
CREATE TABLE IF NOT EXISTS stock_transfers (
    transfer_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    from_branch_id BIGINT UNSIGNED NOT NULL,
    to_branch_id BIGINT UNSIGNED NOT NULL,
    transfer_date DATE NOT NULL,
    transfer_number VARCHAR(50) NOT NULL,
    status ENUM('PENDING', 'IN_TRANSIT', 'RECEIVED', 'CANCELLED') DEFAULT 'PENDING',
    notes TEXT,
    created_by BIGINT UNSIGNED,
    received_by BIGINT UNSIGNED,
    received_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (transfer_id),
    UNIQUE KEY idx_stock_transfers_number (transfer_number),
    KEY idx_stock_transfers_tenant_id (tenant_id),
    KEY idx_stock_transfers_from_branch (from_branch_id),
    KEY idx_stock_transfers_to_branch (to_branch_id),
    KEY idx_stock_transfers_status (status),
    KEY idx_stock_transfers_date (transfer_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (from_branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (to_branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_transfer_items (
    transfer_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    transfer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50),
    notes TEXT,
    PRIMARY KEY (transfer_item_id),
    KEY idx_stock_transfer_items_transfer_id (transfer_id),
    KEY idx_stock_transfer_items_product_id (product_id),
    FOREIGN KEY (transfer_id) REFERENCES stock_transfers(transfer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Favorite Menu
CREATE TABLE IF NOT EXISTS customer_favorites (
    favorite_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    customer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    order_count INT DEFAULT 1,
    last_ordered_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (favorite_id),
    UNIQUE KEY idx_customer_favorites_tenant_customer_product (tenant_id, customer_id, product_id),
    KEY idx_customer_favorites_tenant_id (tenant_id),
    KEY idx_customer_favorites_branch_id (branch_id),
    KEY idx_customer_favorites_customer_id (customer_id),
    KEY idx_customer_favorites_product_id (product_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Birthday Promotions
CREATE TABLE IF NOT EXISTS birthday_promotions (
    promotion_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    customer_id BIGINT UNSIGNED NOT NULL,
    promotion_year INT NOT NULL,
    promotion_type ENUM('DISCOUNT', 'FREE_ITEM', 'POINTS_BONUS') NOT NULL,
    discount_percentage DECIMAL(5,2),
    free_product_id BIGINT UNSIGNED,
    points_bonus INT,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (promotion_id),
    KEY idx_birthday_promotions_tenant_id (tenant_id),
    KEY idx_birthday_promotions_branch_id (branch_id),
    KEY idx_birthday_promotions_customer_id (customer_id),
    KEY idx_birthday_promotions_year (promotion_year),
    KEY idx_birthday_promotions_valid (valid_from, valid_until),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (free_product_id) REFERENCES products(product_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cost Centers
CREATE TABLE IF NOT EXISTS cost_centers (
    cost_center_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    cost_center_code VARCHAR(50) NOT NULL,
    cost_center_name VARCHAR(150) NOT NULL,
    cost_center_type ENUM('DEPARTMENT', 'LOCATION', 'PROJECT', 'ACTIVITY') NOT NULL,
    parent_cost_center_id BIGINT UNSIGNED,
    budget_amount DECIMAL(15,2),
    manager_id BIGINT UNSIGNED,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (cost_center_id),
    UNIQUE KEY idx_cost_centers_tenant_code (tenant_id, cost_center_code),
    KEY idx_cost_centers_tenant_id (tenant_id),
    KEY idx_cost_centers_branch_id (branch_id),
    KEY idx_cost_centers_parent (parent_cost_center_id),
    KEY idx_cost_centers_manager (manager_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (parent_cost_center_id) REFERENCES cost_centers(cost_center_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (manager_id) REFERENCES employees(employee_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chef Performance Tracking
CREATE TABLE IF NOT EXISTS chef_performance (
    performance_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    employee_id BIGINT UNSIGNED NOT NULL,
    performance_date DATE NOT NULL,
    orders_prepared INT DEFAULT 0,
    orders_on_time INT DEFAULT 0,
    average_preparation_time DECIMAL(10,2),
    quality_score DECIMAL(5,2),
    customer_rating DECIMAL(5,2),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (performance_id),
    KEY idx_chef_performance_tenant_id (tenant_id),
    KEY idx_chef_performance_branch_id (branch_id),
    KEY idx_chef_performance_employee_id (employee_id),
    KEY idx_chef_performance_date (performance_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
