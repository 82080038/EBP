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
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_certification_number (certification_number)
) ENGINE=InnoDB;

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
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_contract_number (contract_number)
) ENGINE=InnoDB;

-- Add batch tracking to inventory (if not exists)
ALTER TABLE inventory
ADD COLUMN IF NOT EXISTS batch_number VARCHAR(50),
ADD COLUMN IF NOT EXISTS expiry_date DATE,
ADD COLUMN IF NOT EXISTS supplier_contract_id INT NULL,
ADD COLUMN IF NOT EXISTS halal_certified BOOLEAN DEFAULT FALSE,
ADD INDEX IF NOT EXISTS idx_batch_number (batch_number),
ADD INDEX IF NOT EXISTS idx_expiry_date (expiry_date),
ADD INDEX IF NOT EXISTS idx_supplier_contract_id (supplier_contract_id),
ADD INDEX IF NOT EXISTS idx_halal_certified (halal_certified);

-- Add allergen information to recipe_ingredients (if not exists)
ALTER TABLE recipe_ingredients
ADD COLUMN IF NOT EXISTS allergen_info JSON,
ADD COLUMN IF NOT EXISTS dietary_info JSON;
