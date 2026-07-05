-- Migration 003: Inventory Sourcing Fields
-- Recipe & Ingredient Sourcing (Phase 9 - RESEARCH_32)

-- Update inventory table with sourcing fields
ALTER TABLE inventory
ADD COLUMN batch_number VARCHAR(50) AFTER maximum_stock,
ADD COLUMN expiry_date DATE AFTER batch_number,
ADD COLUMN manufacturing_date DATE AFTER expiry_date,
ADD COLUMN supplier_id BIGINT UNSIGNED AFTER manufacturing_date,
ADD COLUMN sourcing_type ENUM('self_produced', 'outsourced', 'supplier_sourced', 'mixed') DEFAULT 'supplier_sourced' AFTER supplier_id,
ADD COLUMN allergen_info TEXT AFTER sourcing_type,
ADD COLUMN storage_location VARCHAR(50) AFTER allergen_info,
ADD COLUMN storage_temperature VARCHAR(20) AFTER storage_location,
ADD COLUMN quality_grade ENUM('A', 'B', 'C', 'STANDARD') DEFAULT 'STANDARD' AFTER storage_temperature,
ADD COLUMN is_perishable TINYINT(1) DEFAULT 1 AFTER quality_grade,
ADD CONSTRAINT fk_inventory_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
ADD INDEX idx_batch_number (batch_number),
ADD INDEX idx_expiry_date (expiry_date),
ADD INDEX idx_supplier (supplier_id),
ADD INDEX idx_sourcing_type (sourcing_type),
ADD INDEX idx_perishable (is_perishable);
