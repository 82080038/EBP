-- Add sourcing fields to recipes table
ALTER TABLE recipes 
ADD COLUMN sourcing_type ENUM('self_produced', 'outsourced', 'supplier_sourced', 'mixed') DEFAULT 'supplier_sourced',
ADD COLUMN halal_certified BOOLEAN DEFAULT FALSE,
ADD COLUMN halal_certification_id INT NULL,
ADD COLUMN production_cost_labor DECIMAL(10,2) DEFAULT 0,
ADD COLUMN production_cost_equipment DECIMAL(10,2) DEFAULT 0,
ADD COLUMN production_cost_overhead DECIMAL(10,2) DEFAULT 0,
ADD INDEX idx_sourcing_type (sourcing_type),
ADD INDEX idx_halal_certified (halal_certified);

-- Create halal_certifications table
CREATE TABLE IF NOT EXISTS halal_certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    certification_number VARCHAR(100) NOT NULL,
    certifying_body VARCHAR(255) NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    document_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_certification_number (certification_number)
);

-- Create supplier_contracts table
CREATE TABLE IF NOT EXISTS supplier_contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    contract_number VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    terms TEXT,
    pricing_json JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_contract_number (contract_number)
);

-- Add batch tracking to inventory_items
ALTER TABLE inventory_items
ADD COLUMN batch_number VARCHAR(50),
ADD COLUMN expiry_date DATE,
ADD COLUMN supplier_contract_id INT NULL,
ADD COLUMN halal_certified BOOLEAN DEFAULT FALSE,
ADD INDEX idx_batch_number (batch_number),
ADD INDEX idx_expiry_date (expiry_date),
ADD INDEX idx_supplier_contract_id (supplier_contract_id),
ADD INDEX idx_halal_certified (halal_certified);

-- Add allergen information to recipe_ingredients
ALTER TABLE recipe_ingredients
ADD COLUMN allergen_info JSON,
ADD COLUMN dietary_info JSON;
