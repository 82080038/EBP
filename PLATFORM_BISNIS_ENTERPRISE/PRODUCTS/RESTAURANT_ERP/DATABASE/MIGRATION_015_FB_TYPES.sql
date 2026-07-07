/*
========================================================

ENTERPRISE BUSINESS PLATFORM (EBP)

RESTAURANT & CAFE ERP

MIGRATION 015: ENHANCED F&B TYPES

This migration adds support for various F&B business types
beyond just restaurants.

========================================================
*/

USE ebp_restaurant_erp;

/*
========================================================
MODIFY TENANTS TABLE - Add ENUM for business_type
========================================================
*/

ALTER TABLE tenants 
MODIFY COLUMN business_type ENUM(
    'RESTAURANT',
    'CAFE',
    'BAKERY',
    'FOOD_COURT',
    'FAST_FOOD',
    'FINE_DINING',
    'COFFEE_SHOP',
    'TEA_HOUSE',
    'BAR_PUB',
    'NIGHTCLUB',
    'CATERING',
    'FOOD_TRUCK',
    'STALL_KIOSK',
    'CANTINE',
    'HOTEL_RESTAURANT'
) DEFAULT 'RESTAURANT';

/*
========================================================
ADD NEW TABLE: business_types (Reference table)
========================================================
*/

CREATE TABLE IF NOT EXISTS business_types (
    business_type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_code VARCHAR(50) NOT NULL UNIQUE,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    display_order INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

/*
========================================================
INSERT BUSINESS TYPES
========================================================
*/

INSERT IGNORE INTO business_types (type_code, type_name, description, icon, display_order) VALUES
('RESTAURANT', 'Restaurant', 'Full-service restaurant with table service', 'restaurant', 1),
('CAFE', 'Cafe', 'Casual dining establishment serving light meals and coffee', 'cafe', 2),
('BAKERY', 'Bakery', 'Establishment selling baked goods and pastries', 'bakery', 3),
('FOOD_COURT', 'Food Court', 'Multiple food vendors in a shared space', 'food_court', 4),
('FAST_FOOD', 'Fast Food', 'Quick-service restaurant with standardized menu', 'fast_food', 5),
('FINE_DINING', 'Fine Dining', 'Upscale restaurant with high-quality cuisine', 'fine_dining', 6),
('COFFEE_SHOP', 'Coffee Shop', 'Specialty coffee establishment', 'coffee_shop', 7),
('TEA_HOUSE', 'Tea House', 'Establishment specializing in tea service', 'tea_house', 8),
('BAR_PUB', 'Bar/Pub', 'Establishment serving alcoholic beverages and food', 'bar_pub', 9),
('NIGHTCLUB', 'Nightclub', 'Entertainment venue with food and drinks', 'nightclub', 10),
('CATERING', 'Catering', 'Food service for events and functions', 'catering', 11),
('FOOD_TRUCK', 'Food Truck', 'Mobile food service vehicle', 'food_truck', 12),
('STALL_KIOSK', 'Stall/Kiosk', 'Small food stand or booth', 'stall_kiosk', 13),
('CANTINE', 'Canteen', 'Dining facility for institutions', 'canteen', 14),
('HOTEL_RESTAURANT', 'Hotel Restaurant', 'Restaurant within a hotel', 'hotel_restaurant', 15);

/*
========================================================
ADD CUISINE TYPES REFERENCE TABLE
========================================================
*/

CREATE TABLE IF NOT EXISTS cuisine_types (
    cuisine_id INT AUTO_INCREMENT PRIMARY KEY,
    cuisine_code VARCHAR(50) NOT NULL UNIQUE,
    cuisine_name VARCHAR(100) NOT NULL,
    origin_country VARCHAR(100),
    description TEXT,
    icon VARCHAR(50),
    display_order INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

/*
========================================================
INSERT CUISINE TYPES
========================================================
*/

INSERT IGNORE INTO cuisine_types (cuisine_code, cuisine_name, origin_country, description, icon, display_order) VALUES
('INDONESIAN', 'Indonesian', 'Indonesia', 'Traditional and modern Indonesian dishes', 'indonesian', 1),
('CHINESE', 'Chinese', 'China', 'Various regional Chinese cuisines', 'chinese', 2),
('JAPANESE', 'Japanese', 'Japan', 'Sushi, ramen, and other Japanese dishes', 'japanese', 3),
('KOREAN', 'Korean', 'South Korea', 'Korean BBQ, kimchi, and other Korean dishes', 'korean', 4),
('THAI', 'Thai', 'Thailand', 'Spicy and aromatic Thai cuisine', 'thai', 5),
('INDIAN', 'Indian', 'India', 'Curries, tandoori, and other Indian dishes', 'indian', 6),
('WESTERN', 'Western', 'USA/Europe', 'American and European dishes', 'western', 7),
('ITALIAN', 'Italian', 'Italy', 'Pasta, pizza, and Italian cuisine', 'italian', 8),
('FRENCH', 'French', 'France', 'French gastronomy and pastries', 'french', 9),
('MEXICAN', 'Mexican', 'Mexico', 'Tacos, burritos, and Mexican dishes', 'mexican', 10),
('MIDDLE_EASTERN', 'Middle Eastern', 'Middle East', 'Kebabs, hummus, and Middle Eastern cuisine', 'middle_eastern', 11),
('FUSION', 'Fusion', 'Various', 'Blend of multiple culinary traditions', 'fusion', 12),
('TRADITIONAL', 'Traditional', 'Local', 'Local traditional dishes', 'traditional', 13),
('INTERNATIONAL', 'International', 'Various', 'Dishes from around the world', 'international', 14),
('LOCAL', 'Local', 'Local', 'Local specialties and street food', 'local', 15);

/*
========================================================
COMPLETED
========================================================
*/

SELECT 'Migration 015 completed successfully!' AS Status;
SELECT COUNT(*) AS TotalBusinessTypes FROM business_types;
SELECT COUNT(*) AS TotalCuisineTypes FROM cuisine_types;
