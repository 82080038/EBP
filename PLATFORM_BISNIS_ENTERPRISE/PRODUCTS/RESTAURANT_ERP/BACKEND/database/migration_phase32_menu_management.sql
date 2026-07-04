-- Migration Phase 32: Menu & Product Management
-- Provides comprehensive menu management with categories, modifiers, recipes, and pricing

-- Menu Categories Table
CREATE TABLE IF NOT EXISTS menu_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Category Details
    category_name VARCHAR(255) NOT NULL,
    category_description TEXT NULL,
    parent_category_id BIGINT UNSIGNED NULL,
    
    -- Display
    color_code VARCHAR(7) NULL,
    icon_url VARCHAR(255) NULL,
    image_url VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    
    -- Availability
    is_active BOOLEAN DEFAULT TRUE,
    available_from TIME NULL,
    available_until TIME NULL,
    available_days VARCHAR(20) NULL, -- comma-separated days: 1-7 (Mon-Sun)
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_parent_category_id (parent_category_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_category_id) REFERENCES menu_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    
    -- Item Details
    item_code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    
    -- Pricing
    base_price DECIMAL(15,2) NOT NULL,
    cost_price DECIMAL(15,2) NULL,
    
    -- Availability
    is_available BOOLEAN DEFAULT TRUE,
    available_from TIME NULL,
    available_until TIME NULL,
    available_days VARCHAR(20) NULL,
    
    -- Display
    image_url VARCHAR(255) NULL,
    thumbnail_url VARCHAR(255) NULL,
    display_order INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_new BOOLEAN DEFAULT FALSE,
    
    -- Dietary
    is_vegetarian BOOLEAN DEFAULT FALSE,
    is_vegan BOOLEAN DEFAULT FALSE,
    is_gluten_free BOOLEAN DEFAULT FALSE,
    is_spicy BOOLEAN DEFAULT FALSE,
    spice_level INT DEFAULT 0,
    
    -- Preparation
    preparation_time INT NULL, -- in minutes
    preparation_station ENUM('kitchen', 'bar', 'dessert', 'other') NULL,
    
    -- Tax
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_category_id (category_id),
    INDEX idx_item_code (item_code),
    INDEX idx_is_active (is_active),
    INDEX idx_is_available (is_available),
    INDEX idx_is_featured (is_featured),
    INDEX idx_display_order (display_order),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modifiers Table
CREATE TABLE IF NOT EXISTS modifiers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Modifier Details
    modifier_name VARCHAR(255) NOT NULL,
    modifier_description TEXT NULL,
    
    -- Modifier Group
    modifier_group_id BIGINT UNSIGNED NULL,
    
    -- Pricing
    price_adjustment DECIMAL(15,2) DEFAULT 0.00,
    
    -- Type
    modifier_type ENUM('add', 'remove', 'replace', 'upgrade') DEFAULT 'add',
    
    -- Selection
    is_required BOOLEAN DEFAULT FALSE,
    min_selection INT DEFAULT 0,
    max_selection INT DEFAULT 1,
    
    -- Display
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_modifier_group_id (modifier_group_id),
    INDEX idx_is_active (is_active),
    INDEX idx_display_order (display_order),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modifier Groups Table
CREATE TABLE IF NOT EXISTS modifier_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Group Details
    group_name VARCHAR(255) NOT NULL,
    group_description TEXT NULL,
    
    -- Selection Rules
    min_selection INT DEFAULT 0,
    max_selection INT DEFAULT 1,
    selection_type ENUM('single', 'multiple') DEFAULT 'single',
    
    -- Display
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_is_active (is_active),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu Item Modifiers Table (Junction)
CREATE TABLE IF NOT EXISTS menu_item_modifiers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_item_id BIGINT UNSIGNED NOT NULL,
    modifier_id BIGINT UNSIGNED NOT NULL,
    
    -- Assignment Details
    is_default BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_menu_item_id (menu_item_id),
    INDEX idx_modifier_id (modifier_id),
    UNIQUE KEY unique_assignment (menu_item_id, modifier_id),
    
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    FOREIGN KEY (modifier_id) REFERENCES modifiers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipes Table
CREATE TABLE IF NOT EXISTS recipes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    menu_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Recipe Details
    recipe_name VARCHAR(255) NOT NULL,
    recipe_description TEXT NULL,
    yield_quantity DECIMAL(15,3) NOT NULL,
    yield_unit_id BIGINT UNSIGNED NOT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_menu_item_id (menu_item_id),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipe Ingredients Table
CREATE TABLE IF NOT EXISTS recipe_ingredients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Ingredient Details
    quantity DECIMAL(15,3) NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    
    -- Cost
    unit_cost DECIMAL(15,2) NULL,
    total_cost DECIMAL(15,2) NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_recipe_id (recipe_id),
    INDEX idx_inventory_item_id (inventory_item_id),
    
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu Pricing Table (for multiple price points)
CREATE TABLE IF NOT EXISTS menu_pricing (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    menu_item_id BIGINT UNSIGNED NOT NULL,
    
    -- Price Point
    price_type ENUM('dine_in', 'takeaway', 'delivery', 'online', 'other') NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    
    -- Validity
    valid_from DATE NULL,
    valid_until DATE NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_menu_item_id (menu_item_id),
    INDEX idx_price_type (price_type),
    INDEX idx_is_active (is_active),
    UNIQUE KEY unique_price_type (restaurant_id, menu_item_id, price_type),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default menu pricing for new menu items
DELIMITER //
CREATE TRIGGER insert_default_menu_pricing
AFTER INSERT ON menu_items
FOR EACH ROW
BEGIN
    -- Dine-in price
    INSERT INTO menu_pricing (restaurant_id, menu_item_id, price_type, price, is_active)
    VALUES (NEW.restaurant_id, NEW.id, 'dine_in', NEW.base_price, TRUE);
    
    -- Takeaway price (same as base)
    INSERT INTO menu_pricing (restaurant_id, menu_item_id, price_type, price, is_active)
    VALUES (NEW.restaurant_id, NEW.id, 'takeaway', NEW.base_price, TRUE);
    
    -- Delivery price (add 10%)
    INSERT INTO menu_pricing (restaurant_id, menu_item_id, price_type, price, is_active)
    VALUES (NEW.restaurant_id, NEW.id, 'delivery', NEW.base_price * 1.10, TRUE);
    
    -- Online price (same as base)
    INSERT INTO menu_pricing (restaurant_id, menu_item_id, price_type, price, is_active)
    VALUES (NEW.restaurant_id, NEW.id, 'online', NEW.base_price, TRUE);
END//
DELIMITER ;
