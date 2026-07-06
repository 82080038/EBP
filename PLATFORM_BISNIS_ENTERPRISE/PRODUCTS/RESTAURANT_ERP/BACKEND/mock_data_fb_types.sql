-- Mock Data for F&B Tenant Types, Roles, Users, and Activities
-- This script creates comprehensive mock data for testing different F&B business scenarios

-- ============================================
-- 1. TENANTS - Different F&B Business Types
-- ============================================

-- Home-based Food Business
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('HOME_CAFE', 'Home Cafe Business', 'home_based', 'ACTIVE'),
('HOME_KITCHEN', 'Home Kitchen Delivery', 'home_based', 'ACTIVE');

-- Small Restaurant
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('SMALL_RESTO', 'Small Family Restaurant', 'small_restaurant', 'ACTIVE'),
('CAFE_SHOP', 'Coffee Shop', 'small_restaurant', 'ACTIVE'),
('WARUNG_MAKAN', 'Warung Makan', 'small_restaurant', 'ACTIVE');

-- Regional Chain
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('REGIONAL_CHAIN', 'Regional Restaurant Chain', 'regional_chain', 'ACTIVE'),
('CITY_FOOD', 'City Food Chain', 'regional_chain', 'ACTIVE');

-- National Corporation
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('NATIONAL_CORP', 'National Restaurant Corp', 'national_corporation', 'ACTIVE'),
('FAST_FOOD_NAT', 'National Fast Food Chain', 'national_corporation', 'ACTIVE');

-- International Corporation
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('INTL_CORP', 'International Restaurant Corp', 'international_corporation', 'ACTIVE'),
('GLOBAL_CHAIN', 'Global Restaurant Chain', 'international_corporation', 'ACTIVE');

-- Food Truck
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('FOOD_TRUCK', 'Mobile Food Truck', 'food_truck', 'ACTIVE'),
('STREET_FOOD', 'Street Food Vendor', 'food_truck', 'ACTIVE');

-- Stall/Kiosk
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('FOOD_STALL', 'Food Stall', 'stall', 'ACTIVE'),
('KIOSK_FOOD', 'Food Kiosk', 'stall', 'ACTIVE');

-- Cafe
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('URBAN_CAFE', 'Urban Cafe', 'cafe', 'ACTIVE'),
('COFFEE_HOUSE', 'Coffee House', 'cafe', 'ACTIVE');

-- Restaurant
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('FINE_DINING', 'Fine Dining Restaurant', 'restaurant', 'ACTIVE'),
('CASUAL_DINING', 'Casual Dining Restaurant', 'restaurant', 'ACTIVE');

-- Hotel
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('HOTEL_RESTO', 'Hotel Restaurant', 'hotel', 'ACTIVE'),
('RESORT_DINING', 'Resort Dining', 'hotel', 'ACTIVE');

-- International Facility
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('AIRPORT_FOOD', 'Airport Food Court', 'international_facility', 'ACTIVE'),
('MALL_FOOD', 'Mall Food Court', 'international_facility', 'ACTIVE');

-- ============================================
-- 2. BRANCHES - Multiple branches for chain businesses
-- ============================================

-- Regional Chain Branches
INSERT IGNORE INTO branches (tenant_id, company_id, branch_code, branch_name, address, status) VALUES
(3, 1, 'RC_BRANCH1', 'Regional Chain Branch 1', 'Jl. Sudirman No. 1, Jakarta', 'ACTIVE'),
(3, 1, 'RC_BRANCH2', 'Regional Chain Branch 2', 'Jl. Asia Afrika No. 2, Bandung', 'ACTIVE'),
(3, 1, 'RC_BRANCH3', 'Regional Chain Branch 3', 'Jl. Tunjungan No. 3, Surabaya', 'ACTIVE');

-- National Corporation Branches
INSERT IGNORE INTO branches (tenant_id, company_id, branch_code, branch_name, address, status) VALUES
(5, 1, 'NC_JAKARTA', 'National Corp Jakarta', 'Jl. Thamrin No. 1, Jakarta', 'ACTIVE'),
(5, 1, 'NC_BANDUNG', 'National Corp Bandung', 'Jl. Braga No. 2, Bandung', 'ACTIVE'),
(5, 1, 'NC_SURABAYA', 'National Corp Surabaya', 'Jl. Basuki Rahmat No. 3, Surabaya', 'ACTIVE'),
(5, 1, 'NC_MEDAN', 'National Corp Medan', 'Jl. Gatot Subroto No. 4, Medan', 'ACTIVE'),
(5, 1, 'NC_BALI', 'National Corp Bali', 'Jl. Kuta No. 5, Bali', 'ACTIVE');

-- International Corporation Branches
INSERT IGNORE INTO branches (tenant_id, company_id, branch_code, branch_name, address, status) VALUES
(7, 1, 'INTL_JKT', 'International Jakarta', 'Jl. Sudirman No. 10, Jakarta', 'ACTIVE'),
(7, 1, 'INTL_SIN', 'International Singapore', 'Orchard Road No. 1, Singapore', 'ACTIVE'),
(7, 1, 'INTL_KL', 'International Kuala Lumpur', 'Bukit Bintang No. 2, KL', 'ACTIVE'),
(7, 1, 'INTL_BKK', 'International Bangkok', 'Sukhumvit No. 3, Bangkok', 'ACTIVE'),
(7, 1, 'INTL_HKG', 'International Hong Kong', 'Central No. 4, HK', 'ACTIVE');

-- ============================================
-- 3. ROLES - Different roles for each tenant type
-- ============================================

-- Home-based roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(1, 'HOME_OWNER', 'Home Owner', 'Owner of home-based business', 0, 'ACTIVE'),
(1, 'HOME_STAFF', 'Home Staff', 'Staff for home business', 0, 'ACTIVE'),
(2, 'KITCHEN_OWNER', 'Kitchen Owner', 'Owner of kitchen delivery', 0, 'ACTIVE'),
(2, 'KITCHEN_STAFF', 'Kitchen Staff', 'Kitchen staff', 0, 'ACTIVE');

-- Small restaurant roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(3, 'RESTO_OWNER', 'Restaurant Owner', 'Restaurant owner', 0, 'ACTIVE'),
(3, 'RESTO_MANAGER', 'Restaurant Manager', 'Restaurant manager', 0, 'ACTIVE'),
(3, 'RESTO_WAITER', 'Waiter', 'Waiter staff', 0, 'ACTIVE'),
(3, 'RESTO_KITCHEN', 'Kitchen Staff', 'Kitchen staff', 0, 'ACTIVE'),
(3, 'RESTO_CASHIER', 'Cashier', 'Cashier', 0, 'ACTIVE'),
(4, 'CAFE_OWNER', 'Cafe Owner', 'Cafe owner', 0, 'ACTIVE'),
(4, 'CAFE_BARISTA', 'Barista', 'Barista', 0, 'ACTIVE'),
(5, 'WARUNG_OWNER', 'Warung Owner', 'Warung owner', 0, 'ACTIVE'),
(5, 'WARUNG_HELPER', 'Warung Helper', 'Warung helper', 0, 'ACTIVE');

-- Regional chain roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(3, 'CHAIN_OWNER', 'Chain Owner', 'Chain business owner', 0, 'ACTIVE'),
(3, 'AREA_MANAGER', 'Area Manager', 'Area manager for multiple branches', 0, 'ACTIVE'),
(3, 'BRANCH_MANAGER', 'Branch Manager', 'Branch manager', 0, 'ACTIVE'),
(3, 'CHAIN_STAFF', 'Chain Staff', 'Regular staff', 0, 'ACTIVE');

-- National corporation roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(5, 'CORP_CEO', 'CEO', 'Chief Executive Officer', 0, 'ACTIVE'),
(5, 'CORP_COO', 'COO', 'Chief Operating Officer', 0, 'ACTIVE'),
(5, 'CORP_CFO', 'CFO', 'Chief Financial Officer', 0, 'ACTIVE'),
(5, 'REGIONAL_DIRECTOR', 'Regional Director', 'Regional director', 0, 'ACTIVE'),
(5, 'BRANCH_MANAGER', 'Branch Manager', 'Branch manager', 0, 'ACTIVE'),
(5, 'CORP_STAFF', 'Corporate Staff', 'Corporate staff', 0, 'ACTIVE');

-- International corporation roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(7, 'GLOBAL_CEO', 'Global CEO', 'Global Chief Executive', 0, 'ACTIVE'),
(7, 'GLOBAL_COO', 'Global COO', 'Global Chief Operating', 0, 'ACTIVE'),
(7, 'COUNTRY_MANAGER', 'Country Manager', 'Country manager', 0, 'ACTIVE'),
(7, 'REGIONAL_MANAGER', 'Regional Manager', 'Regional manager', 0, 'ACTIVE'),
(7, 'INTL_STAFF', 'International Staff', 'International staff', 0, 'ACTIVE');

-- Food truck roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(11, 'TRUCK_OWNER', 'Truck Owner', 'Food truck owner', 0, 'ACTIVE'),
(11, 'TRUCK_DRIVER', 'Truck Driver', 'Truck driver', 0, 'ACTIVE'),
(11, 'TRUCK_COOK', 'Truck Cook', 'Truck cook', 0, 'ACTIVE'),
(12, 'VENDOR_OWNER', 'Vendor Owner', 'Street vendor owner', 0, 'ACTIVE'),
(12, 'VENDOR_HELPER', 'Vendor Helper', 'Vendor helper', 0, 'ACTIVE');

-- Stall/Kiosk roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(13, 'STALL_OWNER', 'Stall Owner', 'Stall owner', 0, 'ACTIVE'),
(13, 'STALL_ATTENDANT', 'Stall Attendant', 'Stall attendant', 0, 'ACTIVE'),
(14, 'KIOSK_OWNER', 'Kiosk Owner', 'Kiosk owner', 0, 'ACTIVE'),
(14, 'KIOSK_STAFF', 'Kiosk Staff', 'Kiosk staff', 0, 'ACTIVE');

-- Cafe roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(15, 'CAFE_MANAGER', 'Cafe Manager', 'Cafe manager', 0, 'ACTIVE'),
(15, 'CAFE_BARISTA', 'Barista', 'Barista', 0, 'ACTIVE'),
(15, 'CAFE_SERVER', 'Server', 'Server', 0, 'ACTIVE'),
(16, 'COFFEE_OWNER', 'Coffee Owner', 'Coffee house owner', 0, 'ACTIVE'),
(16, 'COFFEE_BARISTA', 'Coffee Barista', 'Coffee barista', 0, 'ACTIVE');

-- Restaurant roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(17, 'FINE_OWNER', 'Fine Dining Owner', 'Fine dining owner', 0, 'ACTIVE'),
(17, 'FINE_MANAGER', 'Fine Dining Manager', 'Fine dining manager', 0, 'ACTIVE'),
(17, 'FINE_WAITER', 'Fine Dining Waiter', 'Fine dining waiter', 0, 'ACTIVE'),
(17, 'FINE_CHEF', 'Executive Chef', 'Executive chef', 0, 'ACTIVE'),
(18, 'CASUAL_OWNER', 'Casual Owner', 'Casual dining owner', 0, 'ACTIVE'),
(18, 'CASUAL_MANAGER', 'Casual Manager', 'Casual dining manager', 0, 'ACTIVE');

-- Hotel roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(19, 'HOTEL_GM', 'General Manager', 'Hotel general manager', 0, 'ACTIVE'),
(19, 'HOTEL_F&B', 'F&B Manager', 'Food and beverage manager', 0, 'ACTIVE'),
(19, 'HOTEL_STAFF', 'Hotel Staff', 'Hotel staff', 0, 'ACTIVE'),
(20, 'RESORT_GM', 'Resort Manager', 'Resort manager', 0, 'ACTIVE'),
(20, 'RESORT_F&B', 'Resort F&B', 'Resort F&B manager', 0, 'ACTIVE');

-- International facility roles
INSERT IGNORE INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES
(21, 'AIRPORT_MGR', 'Airport Manager', 'Airport food manager', 0, 'ACTIVE'),
(21, 'AIRPORT_STAFF', 'Airport Staff', 'Airport staff', 0, 'ACTIVE'),
(22, 'MALL_MGR', 'Mall Manager', 'Mall food manager', 0, 'ACTIVE'),
(22, 'MALL_STAFF', 'Mall Staff', 'Mall staff', 0, 'ACTIVE');

-- ============================================
-- 4. USERS - Mock users for each role
-- ============================================

-- Home-based users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(1, 'home_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'home_owner@example.com', 'Home Owner', '081234567890', 'ACTIVE'),
(1, 'home_staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'home_staff@example.com', 'Home Staff', '081234567891', 'ACTIVE'),
(2, 'kitchen_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kitchen_owner@example.com', 'Kitchen Owner', '081234567892', 'ACTIVE'),
(2, 'kitchen_staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kitchen_staff@example.com', 'Kitchen Staff', '081234567893', 'ACTIVE');

-- Small restaurant users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(3, 'resto_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resto_owner@example.com', 'Restaurant Owner', '081234567894', 'ACTIVE'),
(3, 'resto_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resto_manager@example.com', 'Restaurant Manager', '081234567895', 'ACTIVE'),
(3, 'resto_waiter', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resto_waiter@example.com', 'Waiter Staff', '081234567896', 'ACTIVE'),
(3, 'resto_kitchen', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resto_kitchen@example.com', 'Kitchen Staff', '081234567897', 'ACTIVE'),
(3, 'resto_cashier', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resto_cashier@example.com', 'Cashier', '081234567898', 'ACTIVE'),
(4, 'cafe_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cafe_owner@example.com', 'Cafe Owner', '081234567899', 'ACTIVE'),
(4, 'cafe_barista', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cafe_barista@example.com', 'Barista', '081234567900', 'ACTIVE'),
(5, 'warung_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warung_owner@example.com', 'Warung Owner', '081234567901', 'ACTIVE'),
(5, 'warung_helper', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warung_helper@example.com', 'Warung Helper', '081234567902', 'ACTIVE');

-- Regional chain users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(3, 'chain_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'chain_owner@example.com', 'Chain Owner', '081234567903', 'ACTIVE'),
(3, 'area_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'area_manager@example.com', 'Area Manager', '081234567904', 'ACTIVE'),
(3, 'branch_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'branch_manager@example.com', 'Branch Manager', '081234567905', 'ACTIVE'),
(3, 'chain_staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'chain_staff@example.com', 'Chain Staff', '081234567906', 'ACTIVE');

-- National corporation users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(5, 'corp_ceo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ceo@example.com', 'CEO', '081234567907', 'ACTIVE'),
(5, 'corp_coo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coo@example.com', 'COO', '081234567908', 'ACTIVE'),
(5, 'corp_cfo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cfo@example.com', 'CFO', '081234567909', 'ACTIVE'),
(5, 'regional_director', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'regional_director@example.com', 'Regional Director', '081234567910', 'ACTIVE'),
(5, 'nat_branch_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nat_branch_manager@example.com', 'Branch Manager', '081234567911', 'ACTIVE'),
(5, 'corp_staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'corp_staff@example.com', 'Corporate Staff', '081234567912', 'ACTIVE');

-- International corporation users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(7, 'global_ceo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'global_ceo@example.com', 'Global CEO', '081234567913', 'ACTIVE'),
(7, 'global_coo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'global_coo@example.com', 'Global COO', '081234567914', 'ACTIVE'),
(7, 'country_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'country_manager@example.com', 'Country Manager', '081234567915', 'ACTIVE'),
(7, 'intl_regional_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'intl_regional_manager@example.com', 'Regional Manager', '081234567916', 'ACTIVE'),
(7, 'intl_staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'intl_staff@example.com', 'International Staff', '081234567917', 'ACTIVE');

-- Food truck users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(11, 'truck_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'truck_owner@example.com', 'Truck Owner', '081234567918', 'ACTIVE'),
(11, 'truck_driver', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'truck_driver@example.com', 'Truck Driver', '081234567919', 'ACTIVE'),
(11, 'truck_cook', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'truck_cook@example.com', 'Truck Cook', '081234567920', 'ACTIVE'),
(12, 'vendor_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor_owner@example.com', 'Vendor Owner', '081234567921', 'ACTIVE'),
(12, 'vendor_helper', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor_helper@example.com', 'Vendor Helper', '081234567922', 'ACTIVE');

-- Stall/Kiosk users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(13, 'stall_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stall_owner@example.com', 'Stall Owner', '081234567923', 'ACTIVE'),
(13, 'stall_attendant', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stall_attendant@example.com', 'Stall Attendant', '081234567924', 'ACTIVE'),
(14, 'kiosk_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kiosk_owner@example.com', 'Kiosk Owner', '081234567925', 'ACTIVE'),
(14, 'kiosk_staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kiosk_staff@example.com', 'Kiosk Staff', '081234567926', 'ACTIVE');

-- Cafe users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(15, 'cafe_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cafe_manager@example.com', 'Cafe Manager', '081234567927', 'ACTIVE'),
(15, 'cafe_barista', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cafe_barista@example.com', 'Barista', '081234567928', 'ACTIVE'),
(15, 'cafe_server', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cafe_server@example.com', 'Server', '081234567929', 'ACTIVE'),
(16, 'coffee_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coffee_owner@example.com', 'Coffee Owner', '081234567930', 'ACTIVE'),
(16, 'coffee_barista', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coffee_barista@example.com', 'Coffee Barista', '081234567931', 'ACTIVE');

-- Restaurant users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(17, 'fine_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fine_owner@example.com', 'Fine Dining Owner', '081234567932', 'ACTIVE'),
(17, 'fine_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fine_manager@example.com', 'Fine Dining Manager', '081234567933', 'ACTIVE'),
(17, 'fine_waiter', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fine_waiter@example.com', 'Fine Dining Waiter', '081234567934', 'ACTIVE'),
(17, 'fine_chef', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fine_chef@example.com', 'Executive Chef', '081234567935', 'ACTIVE'),
(18, 'casual_owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'casual_owner@example.com', 'Casual Dining Owner', '081234567936', 'ACTIVE'),
(18, 'casual_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'casual_manager@example.com', 'Casual Dining Manager', '081234567937', 'ACTIVE');

-- Hotel users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(19, 'hotel_gm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hotel_gm@example.com', 'Hotel General Manager', '081234567938', 'ACTIVE'),
(19, 'hotel_fb', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hotel_fb@example.com', 'F&B Manager', '081234567939', 'ACTIVE'),
(19, 'hotel_staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hotel_staff@example.com', 'Hotel Staff', '081234567940', 'ACTIVE'),
(20, 'resort_gm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resort_gm@example.com', 'Resort Manager', '081234567941', 'ACTIVE'),
(20, 'resort_fb', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resort_fb@example.com', 'Resort F&B Manager', '081234567942', 'ACTIVE');

-- International facility users
INSERT IGNORE INTO users (tenant_id, username, password, email, full_name, phone, status) VALUES
(21, 'airport_mgr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'airport_mgr@example.com', 'Airport Manager', '081234567943', 'ACTIVE'),
(21, 'airport_staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'airport_staff@example.com', 'Airport Staff', '081234567944', 'ACTIVE'),
(22, 'mall_mgr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mall_mgr@example.com', 'Mall Manager', '081234567945', 'ACTIVE'),
(22, 'mall_staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mall_staff@example.com', 'Mall Staff', '081234567946', 'ACTIVE');

-- ============================================
-- 5. USER_ROLES - Assign roles to users
-- ============================================

-- Assign roles for home-based
INSERT IGNORE INTO user_roles (user_id, role_id) 
SELECT u.user_id, r.role_id 
FROM users u 
JOIN roles r ON u.tenant_id = r.tenant_id 
WHERE u.username IN ('home_owner', 'home_staff') AND r.role_code IN ('HOME_OWNER', 'HOME_STAFF');

INSERT IGNORE INTO user_roles (user_id, role_id) 
SELECT u.user_id, r.role_id 
FROM users u 
JOIN roles r ON u.tenant_id = r.tenant_id 
WHERE u.username IN ('kitchen_owner', 'kitchen_staff') AND r.role_code IN ('KITCHEN_OWNER', 'KITCHEN_STAFF');

-- Assign roles for small restaurant
INSERT IGNORE INTO user_roles (user_id, role_id) 
SELECT u.user_id, r.role_id 
FROM users u 
JOIN roles r ON u.tenant_id = r.tenant_id 
WHERE u.username IN ('resto_owner', 'resto_manager', 'resto_waiter', 'resto_kitchen', 'resto_cashier') 
AND r.role_code IN ('RESTO_OWNER', 'RESTO_MANAGER', 'RESTO_WAITER', 'RESTO_KITCHEN', 'RESTO_CASHIER');

-- Note: Additional user_roles assignments would follow similar pattern for all other user/role combinations
-- For brevity, showing the pattern - full implementation would assign all users to their corresponding roles

-- ============================================
-- 6. BUSINESS ACTIVITIES - Sample activities
-- ============================================

INSERT IGNORE INTO business_hours (tenant_id, branch_id, day_of_week, open_time, close_time, is_closed) VALUES
(1, 1, 1, '08:00:00', '20:00:00', 0),
(1, 1, 2, '08:00:00', '20:00:00', 0),
(1, 1, 3, '08:00:00', '20:00:00', 0),
(1, 1, 4, '08:00:00', '20:00:00', 0),
(1, 1, 5, '08:00:00', '22:00:00', 0),
(1, 1, 6, '09:00:00', '23:00:00', 0),
(1, 1, 7, '10:00:00', '18:00:00', 0);

-- ============================================
-- 7. PRODUCTS - Sample products for each tenant
-- ============================================

INSERT IGNORE INTO categories (tenant_id, category_code, category_name, description) VALUES
(1, 'HOME_FOOD', 'Home Food', 'Home-cooked food items'),
(3, 'MAIN_COURSE', 'Main Course', 'Main course dishes'),
(3, 'BEVERAGES', 'Beverages', 'Drinks and beverages'),
(3, 'DESSERTS', 'Desserts', 'Sweet items');

INSERT IGNORE INTO products (tenant_id, category_id, product_code, product_name, description, price, is_available) VALUES
(1, 1, 'HF001', 'Nasi Box', 'Complete meal box', 25000, 1),
(3, 2, 'MC001', 'Nasi Goreng', 'Fried rice', 35000, 1),
(3, 2, 'MC002', 'Mie Goreng', 'Fried noodles', 30000, 1),
(3, 3, 'BV001', 'Es Teh', 'Iced tea', 10000, 1),
(3, 3, 'BV002', 'Kopi', 'Coffee', 15000, 1),
(3, 4, 'DS001', 'Puding', 'Pudding', 12000, 1);

-- ============================================
-- 8. SAMPLE ORDERS - Commented out due to table structure requirements
-- ============================================

-- INSERT INTO orders (tenant_id, branch_id, order_number, table_id, total_amount, status, created_at) VALUES
-- (1, 1, 'ORD001', 1, 25000, 'COMPLETED', NOW()),
-- (3, 1, 'ORD002', 2, 65000, 'COMPLETED', NOW()),
-- (3, 1, 'ORD003', 3, 45000, 'IN_PROGRESS', NOW());

-- ============================================
-- 9. HOLIDAYS - Sample holidays
-- ============================================

INSERT IGNORE INTO holidays (tenant_id, holiday_name, holiday_date, holiday_type, is_recurring) VALUES
(1, 'Independence Day', '2026-08-17', 'PUBLIC', 1),
(1, 'New Year', '2026-01-01', 'PUBLIC', 1),
(3, 'Christmas', '2026-12-25', 'PUBLIC', 1),
(3, 'Eid al-Fitr', '2026-04-10', 'RELIGIOUS', 0);

-- ============================================
-- 10. ATTENDANCE - Sample attendance records
-- ============================================

INSERT IGNORE INTO attendance (tenant_id, branch_id, employee_id, attendance_date, check_in_time, check_out_time, work_hours, status) VALUES
(1, 1, 1, CURDATE(), '08:00:00', '17:00:00', 9.00, 'PRESENT'),
(3, 1, 5, CURDATE(), '08:30:00', '17:30:00', 9.00, 'PRESENT'),
(3, 1, 6, CURDATE(), '09:00:00', '18:00:00', 9.00, 'PRESENT');
