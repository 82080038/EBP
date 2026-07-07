-- Test Tenant Seeding Script for RESTAURANT_ERP
-- This script creates test tenants, users, and related data for testing purposes
-- Usage: mysql -u root -p ebp_restaurant_db < test_tenants.sql

USE ebp_restaurant_db;

-- Clear existing test data (optional - uncomment to reset)
-- DELETE FROM orders WHERE tenant_id IN (1, 2, 3);
-- DELETE FROM menu_items WHERE tenant_id IN (1, 2, 3);
-- DELETE FROM branches WHERE tenant_id IN (1, 2, 3);
-- DELETE FROM users WHERE tenant_id IN (1, 2, 3);
-- DELETE FROM tenants WHERE id IN (1, 2, 3);

-- Insert Test Tenants
INSERT INTO tenants (id, name, type, status, created_at, updated_at) VALUES
(1, 'EBP Restaurant Jakarta', 'RESTAURANT', 'ACTIVE', NOW(), NOW()),
(2, 'EBP Cafe Bandung', 'CAFE', 'ACTIVE', NOW(), NOW()),
(3, 'EBP Fast Food Surabaya', 'FAST_FOOD', 'ACTIVE', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Insert Branches for Each Tenant
INSERT INTO branches (id, tenant_id, name, address, city, phone, status, created_at, updated_at) VALUES
-- Jakarta Branches
(1, 1, 'Jakarta Central', 'Jl. Sudirman No. 1', 'Jakarta', '021-12345678', 'ACTIVE', NOW(), NOW()),
(2, 1, 'Jakarta South', 'Jl. Fatmawati No. 10', 'Jakarta', '021-87654321', 'ACTIVE', NOW(), NOW()),
-- Bandung Branch
(3, 2, 'Bandung Main', 'Jl. Asia Afrika No. 50', 'Bandung', '022-11122233', 'ACTIVE', NOW(), NOW()),
-- Surabaya Branches
(4, 3, 'Surabaya Central', 'Jl. Tunjungan No. 25', 'Surabaya', '031-44455566', 'ACTIVE', NOW(), NOW()),
(5, 3, 'Surabaya East', 'Jl. Ahmad Yani No. 100', 'Surabaya', '031-77788899', 'ACTIVE', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Insert Test Users with Different Roles
-- Password for all test users: 'password123' (hashed using bcrypt)
-- In production, use proper password hashing

INSERT INTO users (id, tenant_id, branch_id, username, password, email, full_name, role, level, is_active, created_at, updated_at) VALUES
-- EBP Restaurant Jakarta Users
(1, 1, 1, 'admin_jakarta', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'admin@ebp-jakarta.com', 'Admin Jakarta', 'Administrator', 'PLATFORM_OWNER', 1, NOW(), NOW()),
(2, 1, 1, 'manager_jakarta', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'manager@ebp-jakarta.com', 'Manager Jakarta', 'Restaurant Manager', 'TENANT_OWNER', 1, NOW(), NOW()),
(3, 1, 1, 'waiter_jakarta', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'waiter@ebp-jakarta.com', 'Waiter Jakarta', 'Waiter', 'TENANT_MEMBER', 1, NOW(), NOW()),
(4, 1, 1, 'kitchen_jakarta', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'kitchen@ebp-jakarta.com', 'Kitchen Jakarta', 'Kitchen Staff', 'TENANT_MEMBER', 1, NOW(), NOW()),
(5, 1, 1, 'cashier_jakarta', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'cashier@ebp-jakarta.com', 'Cashier Jakarta', 'Cashier', 'TENANT_MEMBER', 1, NOW(), NOW()),
(6, 1, 1, 'inventory_jakarta', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'inventory@ebp-jakarta.com', 'Inventory Jakarta', 'Inventory Manager', 'TENANT_MEMBER', 1, NOW(), NOW()),
(7, 1, 1, 'host_jakarta', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'host@ebp-jakarta.com', 'Host Jakarta', 'Host/Hostess', 'TENANT_MEMBER', 1, NOW(), NOW()),

-- EBP Cafe Bandung Users
(8, 2, 3, 'admin_bandung', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'admin@ebp-bandung.com', 'Admin Bandung', 'Administrator', 'PLATFORM_OWNER', 1, NOW(), NOW()),
(9, 2, 3, 'manager_bandung', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'manager@ebp-bandung.com', 'Manager Bandung', 'Restaurant Manager', 'TENANT_OWNER', 1, NOW(), NOW()),
(10, 2, 3, 'waiter_bandung', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'waiter@ebp-bandung.com', 'Waiter Bandung', 'Waiter', 'TENANT_MEMBER', 1, NOW(), NOW()),
(11, 2, 3, 'kitchen_bandung', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'kitchen@ebp-bandung.com', 'Kitchen Bandung', 'Kitchen Staff', 'TENANT_MEMBER', 1, NOW(), NOW()),
(12, 2, 3, 'cashier_bandung', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'cashier@ebp-bandung.com', 'Cashier Bandung', 'Cashier', 'TENANT_MEMBER', 1, NOW(), NOW()),

-- EBP Fast Food Surabaya Users
(13, 3, 4, 'admin_surabaya', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'admin@ebp-surabaya.com', 'Admin Surabaya', 'Administrator', 'PLATFORM_OWNER', 1, NOW(), NOW()),
(14, 3, 4, 'manager_surabaya', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'manager@ebp-surabaya.com', 'Manager Surabaya', 'Restaurant Manager', 'TENANT_OWNER', 1, NOW(), NOW()),
(15, 3, 4, 'cashier_surabaya', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'cashier@ebp-surabaya.com', 'Cashier Surabaya', 'Cashier', 'TENANT_MEMBER', 1, NOW(), NOW()),
(16, 3, 4, 'kitchen_surabaya', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqz5qT1qWm', 'kitchen@ebp-surabaya.com', 'Kitchen Surabaya', 'Kitchen Staff', 'TENANT_MEMBER', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Insert Sample Menu Items for Each Tenant
INSERT INTO menu_items (id, tenant_id, name, category, price, description, is_available, created_at, updated_at) VALUES
-- Jakarta Menu
(1, 1, 'Nasi Goreng Spesial', 'MAIN_COURSE', 35000, 'Fried rice with special spices, egg, and chicken', 1, NOW(), NOW()),
(2, 1, 'Sate Ayam', 'APPETIZER', 25000, 'Grilled chicken skewers with peanut sauce', 1, NOW(), NOW()),
(3, 1, 'Es Teh Manis', 'BEVERAGE', 8000, 'Sweet iced tea', 1, NOW(), NOW()),
(4, 1, 'Rendang', 'MAIN_COURSE', 45000, 'Slow-cooked beef in coconut milk and spices', 1, NOW(), NOW()),
(5, 1, 'Gado-Gado', 'APPETIZER', 20000, 'Indonesian salad with peanut sauce', 1, NOW(), NOW()),
-- Bandung Menu
(6, 2, 'Kopi Susu Gula Aren', 'BEVERAGE', 18000, 'Coffee with palm sugar and milk', 1, NOW(), NOW()),
(7, 2, 'Croissant Butter', 'BAKERY', 15000, 'Butter croissant', 1, NOW(), NOW()),
(8, 2, 'Nasi Liwet', 'MAIN_COURSE', 30000, 'Rice cooked in coconut milk with side dishes', 1, NOW(), NOW()),
(9, 2, 'Es Kopi Susu', 'BEVERAGE', 15000, 'Iced coffee with milk', 1, NOW(), NOW()),
-- Surabaya Menu
(10, 3, 'Burger Beef', 'MAIN_COURSE', 40000, 'Beef burger with cheese and vegetables', 1, NOW(), NOW()),
(11, 3, 'French Fries', 'SIDE_DISH', 15000, 'Crispy french fries', 1, NOW(), NOW()),
(12, 3, 'Cola', 'BEVERAGE', 10000, 'Cola soft drink', 1, NOW(), NOW()),
(13, 3, 'Chicken Nuggets', 'MAIN_COURSE', 25000, 'Crispy chicken nuggets', 1, NOW(), NOW()),
(14, 3, 'Ice Cream Sundae', 'DESSERT', 20000, 'Ice cream with toppings', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Insert Sample Inventory Items
INSERT INTO inventory_items (id, tenant_id, name, category, unit, quantity, min_quantity, unit_price, created_at, updated_at) VALUES
-- Jakarta Inventory
(1, 1, 'Rice', 'INGREDIENT', 'kg', 50, 20, 12000, NOW(), NOW()),
(2, 1, 'Chicken', 'INGREDIENT', 'kg', 30, 10, 35000, NOW(), NOW()),
(3, 1, 'Cooking Oil', 'INGREDIENT', 'liter', 20, 5, 18000, NOW(), NOW()),
(4, 1, 'Sugar', 'INGREDIENT', 'kg', 25, 10, 15000, NOW(), NOW()),
-- Bandung Inventory
(5, 2, 'Coffee Beans', 'INGREDIENT', 'kg', 40, 15, 80000, NOW(), NOW()),
(6, 2, 'Milk', 'INGREDIENT', 'liter', 30, 10, 18000, NOW(), NOW()),
(7, 2, 'Flour', 'INGREDIENT', 'kg', 35, 15, 12000, NOW(), NOW()),
-- Surabaya Inventory
(8, 3, 'Beef Patty', 'INGREDIENT', 'kg', 25, 10, 60000, NOW(), NOW()),
(9, 3, 'Cheese', 'INGREDIENT', 'kg', 15, 5, 80000, NOW(), NOW()),
(10, 3, 'Potato', 'INGREDIENT', 'kg', 40, 15, 10000, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Insert Sample Tables
INSERT INTO tables (id, tenant_id, branch_id, table_number, capacity, status, created_at, updated_at) VALUES
-- Jakarta Tables
(1, 1, 1, 'T01', 4, 'AVAILABLE', NOW(), NOW()),
(2, 1, 1, 'T02', 4, 'AVAILABLE', NOW(), NOW()),
(3, 1, 1, 'T03', 6, 'AVAILABLE', NOW(), NOW()),
(4, 1, 1, 'T04', 2, 'AVAILABLE', NOW(), NOW()),
(5, 1, 1, 'T05', 8, 'AVAILABLE', NOW(), NOW()),
-- Bandung Tables
(6, 2, 3, 'T01', 4, 'AVAILABLE', NOW(), NOW()),
(7, 2, 3, 'T02', 4, 'AVAILABLE', NOW(), NOW()),
(8, 2, 3, 'T03', 6, 'AVAILABLE', NOW(), NOW()),
-- Surabaya Tables
(9, 3, 4, 'T01', 4, 'AVAILABLE', NOW(), NOW()),
(10, 3, 4, 'T02', 4, 'AVAILABLE', NOW(), NOW()),
(11, 3, 4, 'T03', 6, 'AVAILABLE', NOW(), NOW()),
(12, 3, 4, 'T04', 8, 'AVAILABLE', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Insert Sample Customers
INSERT INTO customers (id, tenant_id, name, email, phone, loyalty_points, created_at, updated_at) VALUES
(1, 1, 'Budi Santoso', 'budi@email.com', '081234567890', 150, NOW(), NOW()),
(2, 1, 'Siti Rahayu', 'siti@email.com', '081234567891', 200, NOW(), NOW()),
(3, 2, 'Agus Pratama', 'agus@email.com', '081234567892', 100, NOW(), NOW()),
(4, 3, 'Dewi Lestari', 'dewi@email.com', '081234567893', 180, NOW(), NOW()),
(5, 3, 'Eko Kurniawan', 'eko@email.com', '081234567894', 120, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Verify Data Insertion
SELECT 'Tenants created:' as info;
SELECT * FROM tenants WHERE id IN (1, 2, 3);

SELECT 'Branches created:' as info;
SELECT * FROM branches WHERE tenant_id IN (1, 2, 3);

SELECT 'Users created:' as info;
SELECT id, tenant_id, username, role, level FROM users WHERE tenant_id IN (1, 2, 3);

SELECT 'Menu items created:' as info;
SELECT COUNT(*) as total_menu_items FROM menu_items WHERE tenant_id IN (1, 2, 3);

SELECT 'Inventory items created:' as info;
SELECT COUNT(*) as total_inventory_items FROM inventory_items WHERE tenant_id IN (1, 2, 3);

SELECT 'Tables created:' as info;
SELECT COUNT(*) as total_tables FROM tables WHERE tenant_id IN (1, 2, 3);

SELECT 'Customers created:' as info;
SELECT COUNT(*) as total_customers FROM customers WHERE tenant_id IN (1, 2, 3);

-- Note: Default password for all test users is 'password123'
-- In production, use proper password hashing and change default passwords
