-- Recipe Management Tables for EBP Restaurant ERP
-- Essential for food cost control and menu engineering

-- Recipes table
CREATE TABLE IF NOT EXISTS recipes (
    recipe_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT,
    recipe_code VARCHAR(50) NOT NULL UNIQUE,
    recipe_name VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    portion_size DECIMAL(10,2) DEFAULT 1.00,
    portion_unit VARCHAR(50) DEFAULT 'serving',
    preparation_time INT DEFAULT 0 COMMENT 'Preparation time in minutes',
    instructions TEXT,
    total_cost DECIMAL(15,2) DEFAULT 0.00,
    cost_per_portion DECIMAL(15,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cost_updated_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_category (category_id),
    INDEX idx_code (recipe_code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipe ingredients table
CREATE TABLE IF NOT EXISTS recipe_ingredients (
    recipe_ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    inventory_item_id INT NOT NULL,
    quantity DECIMAL(10,3) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    cost_per_unit DECIMAL(15,2) DEFAULT 0.00,
    is_optional TINYINT(1) DEFAULT 0,
    preparation_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipe (recipe_id),
    INDEX idx_inventory (inventory_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipe versions table for versioning
CREATE TABLE IF NOT EXISTS recipe_versions (
    version_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    version_number INT NOT NULL,
    version_name VARCHAR(100),
    changes_description TEXT,
    total_cost DECIMAL(15,2) DEFAULT 0.00,
    cost_per_portion DECIMAL(15,2) DEFAULT 0.00,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipe (recipe_id),
    INDEX idx_version (recipe_id, version_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipe allergens table
CREATE TABLE IF NOT EXISTS recipe_allergens (
    recipe_allergen_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    allergen_name VARCHAR(100) NOT NULL,
    allergen_type VARCHAR(50) COMMENT 'gluten, dairy, nuts, soy, eggs, fish, shellfish, etc',
    severity VARCHAR(20) DEFAULT 'medium' COMMENT 'low, medium, high',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipe (recipe_id),
    INDEX idx_allergen (allergen_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipe nutritional information table
CREATE TABLE IF NOT EXISTS recipe_nutrition (
    nutrition_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    serving_size DECIMAL(10,2) DEFAULT 1.00,
    calories DECIMAL(10,2),
    protein DECIMAL(10,2),
    carbohydrates DECIMAL(10,2),
    fat DECIMAL(10,2),
    saturated_fat DECIMAL(10,2),
    trans_fat DECIMAL(10,2),
    cholesterol DECIMAL(10,2),
    sodium DECIMAL(10,2),
    fiber DECIMAL(10,2),
    sugar DECIMAL(10,2),
    vitamin_a DECIMAL(10,2),
    vitamin_c DECIMAL(10,2),
    calcium DECIMAL(10,2),
    iron DECIMAL(10,2),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_recipe (recipe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipe preparation steps table
CREATE TABLE IF NOT EXISTS recipe_preparation_steps (
    step_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    step_number INT NOT NULL,
    step_description TEXT NOT NULL,
    estimated_time INT COMMENT 'Estimated time in minutes',
    temperature VARCHAR(50) COMMENT 'Temperature if applicable',
    equipment_needed TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipe (recipe_id),
    INDEX idx_step (recipe_id, step_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
