-- Migration 001: Supplier Management Tables
-- Recipe & Ingredient Sourcing (Phase 9 - RESEARCH_32)

-- 1. Suppliers Table
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    supplier_code VARCHAR(50) NOT NULL UNIQUE,
    supplier_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(50),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    region VARCHAR(100),
    country VARCHAR(50) DEFAULT 'Indonesia',
    tax_number VARCHAR(100),
    payment_terms VARCHAR(50),
    lead_time_days INT DEFAULT 7,
    minimum_order_quantity DECIMAL(10,2) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    status ENUM('ACTIVE', 'INACTIVE', 'BLOCKED') DEFAULT 'ACTIVE',
    halal_certified TINYINT(1) DEFAULT 0,
    halal_certification_id VARCHAR(100),
    notes TEXT,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    CONSTRAINT fk_supplier_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_supplier_tenant (tenant_id),
    INDEX idx_supplier_status (status),
    INDEX idx_supplier_halal (halal_certified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Supplier Contracts Table
CREATE TABLE IF NOT EXISTS supplier_contracts (
    contract_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    contract_number VARCHAR(50) NOT NULL UNIQUE,
    contract_type ENUM('PURCHASE', 'SERVICE', 'PARTNERSHIP') DEFAULT 'PURCHASE',
    start_date DATE NOT NULL,
    end_date DATE,
    auto_renew TINYINT(1) DEFAULT 0,
    payment_terms VARCHAR(50),
    delivery_terms TEXT,
    quality_standards TEXT,
    penalty_clauses TEXT,
    contract_value DECIMAL(15,2),
    currency VARCHAR(10) DEFAULT 'IDR',
    status ENUM('DRAFT', 'ACTIVE', 'EXPIRED', 'TERMINATED', 'SUSPENDED') DEFAULT 'DRAFT',
    notes TEXT,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    CONSTRAINT fk_contract_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    CONSTRAINT fk_contract_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    INDEX idx_contract_tenant (tenant_id),
    INDEX idx_contract_supplier (supplier_id),
    INDEX idx_contract_status (status),
    INDEX idx_contract_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Supplier Products Table (Link suppliers to products they supply)
CREATE TABLE IF NOT EXISTS supplier_products (
    supplier_product_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    supplier_sku VARCHAR(50),
    supplier_price DECIMAL(10,2),
    supplier_unit VARCHAR(20),
    minimum_order_quantity DECIMAL(10,2),
    lead_time_days INT DEFAULT 7,
    is_preferred TINYINT(1) DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_sp_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    CONSTRAINT fk_sp_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    CONSTRAINT fk_sp_product FOREIGN KEY (product_id) REFERENCES products(product_id),
    INDEX idx_sp_tenant (tenant_id),
    INDEX idx_sp_supplier (supplier_id),
    INDEX idx_sp_product (product_id),
    UNIQUE KEY uk_supplier_product (supplier_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
