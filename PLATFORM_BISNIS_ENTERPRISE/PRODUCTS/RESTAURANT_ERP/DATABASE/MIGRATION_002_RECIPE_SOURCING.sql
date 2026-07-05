-- Migration 002: Recipe Sourcing Fields
-- Recipe & Ingredient Sourcing (Phase 9 - RESEARCH_32)

-- Update recipes table with sourcing fields
ALTER TABLE recipes
ADD COLUMN sourcing_type ENUM('self_produced', 'outsourced', 'supplier_sourced', 'mixed') DEFAULT 'supplier_sourced' AFTER yield_unit,
ADD COLUMN production_cost_labor DECIMAL(10,2) DEFAULT 0.00 AFTER sourcing_type,
ADD COLUMN production_cost_equipment DECIMAL(10,2) DEFAULT 0.00 AFTER production_cost_labor,
ADD COLUMN production_cost_overhead DECIMAL(10,2) DEFAULT 0.00 AFTER production_cost_equipment,
ADD COLUMN halal_certified TINYINT(1) DEFAULT 0 AFTER production_cost_overhead,
ADD COLUMN halal_certification_id VARCHAR(100) AFTER halal_certified,
ADD COLUMN preparation_time_minutes INT DEFAULT 0 AFTER halal_certification_id,
ADD COLUMN difficulty_level ENUM('EASY', 'MEDIUM', 'HARD') DEFAULT 'MEDIUM' AFTER preparation_time_minutes,
ADD INDEX idx_sourcing_type (sourcing_type),
ADD INDEX idx_halal_certified (halal_certified);

-- Update recipe_ingredients table with allergen information
ALTER TABLE recipe_ingredients
ADD COLUMN allergen_info TEXT AFTER unit,
ADD COLUMN is_critical TINYINT(1) DEFAULT 0 AFTER allergen_info,
ADD COLUMN supplier_id BIGINT UNSIGNED AFTER is_critical,
ADD CONSTRAINT fk_ri_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
ADD INDEX idx_supplier (supplier_id);
