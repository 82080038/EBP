USE ebp_restaurant_erp;

-- Tenants with various F&B types
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('EBP_RESTAURANT', 'EBP Restaurant Demo', 'RESTAURANT', 'ACTIVE'),
('EBP_CAFE', 'EBP Coffee House', 'COFFEE_SHOP', 'ACTIVE'),
('EBP_BAKERY', 'EBP Artisan Bakery', 'BAKERY', 'ACTIVE'),
('EBP_FASTFOOD', 'EBP Burger Joint', 'FAST_FOOD', 'ACTIVE'),
('EBP_JAPANESE', 'EBP Sushi Bar', 'RESTAURANT', 'ACTIVE'),
('EBP_THAI', 'EBP Thai Kitchen', 'RESTAURANT', 'ACTIVE'),
('EBP_BAR', 'EBP Sports Bar', 'BAR_PUB', 'ACTIVE'),
('EBP_CATERING', 'EBP Catering Service', 'CATERING', 'ACTIVE');

-- Company
INSERT IGNORE INTO companies (tenant_id, company_code, company_name, status) VALUES
(1, 'EBP_COMPANY', 'EBP Restaurant Company', 'ACTIVE'),
(2, 'EBP_COMPANY', 'EBP Restaurant Company', 'ACTIVE'),
(3, 'EBP_COMPANY', 'EBP Restaurant Company', 'ACTIVE'),
(4, 'EBP_COMPANY', 'EBP Restaurant Company', 'ACTIVE'),
(5, 'EBP_COMPANY', 'EBP Restaurant Company', 'ACTIVE'),
(6, 'EBP_COMPANY', 'EBP Restaurant Company', 'ACTIVE'),
(7, 'EBP_COMPANY', 'EBP Restaurant Company', 'ACTIVE'),
(8, 'EBP_COMPANY', 'EBP Restaurant Company', 'ACTIVE');

-- Branches for each tenant
INSERT IGNORE INTO branches (tenant_id, company_id, branch_code, branch_name, address, phone, opening_time, closing_time, status) VALUES
-- Restaurant (Indonesian)
(1, 1, 'JKT001', 'EBP Restaurant Jakarta', 'Jl. Sudirman No. 123', '+62 21 1234 5678', '10:00:00', '23:00:00', 'ACTIVE'),
-- Coffee Shop
(2, 2, 'CAFE001', 'EBP Coffee House Jakarta', 'Jl. Senopati No. 45', '+62 21 2345 6789', '07:00:00', '22:00:00', 'ACTIVE'),
-- Bakery
(3, 3, 'BAKERY001', 'EBP Artisan Bakery Jakarta', 'Jl. Kemang No. 67', '+62 21 3456 7890', '06:00:00', '20:00:00', 'ACTIVE'),
-- Fast Food
(4, 4, 'FF001', 'EBP Burger Joint Jakarta', 'Jl. Blok M No. 89', '+62 21 4567 8901', '10:00:00', '23:00:00', 'ACTIVE'),
-- Japanese Restaurant
(5, 5, 'JPN001', 'EBP Sushi Bar Jakarta', 'Jl. Gunawarman No. 12', '+62 21 5678 9012', '11:30:00', '22:00:00', 'ACTIVE'),
-- Thai Restaurant
(6, 6, 'THAI001', 'EBP Thai Kitchen Jakarta', 'Jl. Wolter Monginsidi No. 34', '+62 21 6789 0123', '11:00:00', '22:30:00', 'ACTIVE'),
-- Bar/Pub
(7, 7, 'BAR001', 'EBP Sports Bar Jakarta', 'Jl. Gatot Subroto No. 56', '+62 21 7890 1234', '16:00:00', '02:00:00', 'ACTIVE'),
-- Catering
(8, 8, 'CAT001', 'EBP Catering Service HQ', 'Jl. Rasuna Said No. 78', '+62 21 8901 2345', '08:00:00', '18:00:00', 'ACTIVE');

-- Menu Categories for each tenant
INSERT IGNORE INTO menu_categories (tenant_id, category_code, category_name, description, display_order, status) VALUES
-- Restaurant (Indonesian)
(1, 'CAT001', 'Main Course', 'Primary dishes', 1, 'ACTIVE'),
(1, 'CAT002', 'Appetizers', 'Starters', 2, 'ACTIVE'),
(1, 'CAT003', 'Beverages', 'Drinks', 3, 'ACTIVE'),
(1, 'CAT004', 'Desserts', 'Sweet treats', 4, 'ACTIVE'),
-- Coffee Shop
(2, 'CAT001', 'Coffee', 'Coffee beverages', 1, 'ACTIVE'),
(2, 'CAT002', 'Non-Coffee', 'Tea and other drinks', 2, 'ACTIVE'),
(2, 'CAT003', 'Pastries', 'Baked goods', 3, 'ACTIVE'),
(2, 'CAT004', 'Light Meals', 'Sandwiches and snacks', 4, 'ACTIVE'),
-- Bakery
(3, 'CAT001', 'Breads', 'Fresh breads', 1, 'ACTIVE'),
(3, 'CAT002', 'Pastries', 'Sweet pastries', 2, 'ACTIVE'),
(3, 'CAT003', 'Cakes', 'Cakes and tortes', 3, 'ACTIVE'),
(3, 'CAT004', 'Savory', 'Savory baked goods', 4, 'ACTIVE'),
-- Fast Food
(4, 'CAT001', 'Burgers', 'Burger varieties', 1, 'ACTIVE'),
(4, 'CAT002', 'Sides', 'Fries and sides', 2, 'ACTIVE'),
(4, 'CAT003', 'Drinks', 'Soft drinks', 3, 'ACTIVE'),
(4, 'CAT004', 'Desserts', 'Ice cream and desserts', 4, 'ACTIVE'),
-- Japanese
(5, 'CAT001', 'Sushi', 'Sushi and sashimi', 1, 'ACTIVE'),
(5, 'CAT002', 'Ramen', 'Noodle dishes', 2, 'ACTIVE'),
(5, 'CAT003', 'Donburi', 'Rice bowls', 3, 'ACTIVE'),
(5, 'CAT004', 'Drinks', 'Japanese drinks', 4, 'ACTIVE'),
-- Thai
(6, 'CAT001', 'Curries', 'Thai curries', 1, 'ACTIVE'),
(6, 'CAT002', 'Noodles', 'Thai noodles', 2, 'ACTIVE'),
(6, 'CAT003', 'Stir-fry', 'Stir-fried dishes', 3, 'ACTIVE'),
(6, 'CAT004', 'Desserts', 'Thai desserts', 4, 'ACTIVE'),
-- Bar
(7, 'CAT001', 'Cocktails', 'Mixed drinks', 1, 'ACTIVE'),
(7, 'CAT002', 'Beer', 'Beer selection', 2, 'ACTIVE'),
(7, 'CAT003', 'Bar Food', 'Pub food', 3, 'ACTIVE'),
(7, 'CAT004', 'Non-Alcoholic', 'Mocktails and soft drinks', 4, 'ACTIVE'),
-- Catering
(8, 'CAT001', 'Packages', 'Catering packages', 1, 'ACTIVE'),
(8, 'CAT002', 'Individual', 'Individual portions', 2, 'ACTIVE'),
(8, 'CAT003', 'Beverages', 'Drink packages', 3, 'ACTIVE'),
(8, 'CAT004', 'Desserts', 'Dessert packages', 4, 'ACTIVE');

-- Menu Items for each tenant
INSERT IGNORE INTO menus (tenant_id, category_id, menu_code, menu_name, description, selling_price, cost_price, image_url, status) VALUES
-- Restaurant (Indonesian)
(1, 1, 'PROD001', 'Nasi Goreng Spesial', 'Fried rice with chicken', 35000, 20000, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=400', 'ACTIVE'),
(1, 1, 'PROD002', 'Ayam Bakar Madu', 'Grilled chicken with honey', 45000, 28000, 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=400', 'ACTIVE'),
(1, 2, 'PROD003', 'Gado-Gado', 'Indonesian salad', 28000, 16000, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400', 'ACTIVE'),
(1, 3, 'PROD004', 'Es Teh Manis', 'Sweet iced tea', 8000, 2000, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400', 'ACTIVE'),
(1, 3, 'PROD005', 'Kopi Susu Gula Aren', 'Coffee with palm sugar', 22000, 8000, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=400', 'ACTIVE'),
(1, 4, 'PROD006', 'Es Teler', 'Mixed fruit dessert', 28000, 15000, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=400', 'ACTIVE'),
-- Coffee Shop
(2, 1, 'CAFE001', 'Espresso', 'Single shot espresso', 25000, 5000, 'https://images.unsplash.com/photo-1510707577719-ae7c14805e3a?w=400', 'ACTIVE'),
(2, 1, 'CAFE002', 'Cappuccino', 'Espresso with steamed milk', 35000, 8000, 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400', 'ACTIVE'),
(2, 1, 'CAFE003', 'Latte', 'Espresso with foamed milk', 38000, 9000, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=400', 'ACTIVE'),
(2, 3, 'CAFE004', 'Croissant', 'Butter croissant', 22000, 10000, 'https://images.unsplash.com/photo-1555507036-ab1f4038808c?w=400', 'ACTIVE'),
(2, 3, 'CAFE005', 'Blueberry Muffin', 'Fresh blueberry muffin', 25000, 12000, 'https://images.unsplash.com/photo-1607958996333-41aef7caefaa?w=400', 'ACTIVE'),
-- Bakery
(3, 1, 'BAK001', 'Sourdough Bread', 'Artisan sourdough loaf', 45000, 20000, 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400', 'ACTIVE'),
(3, 1, 'BAK002', 'Baguette', 'French baguette', 25000, 10000, 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=400', 'ACTIVE'),
(3, 2, 'BAK003', 'Pain au Chocolat', 'Chocolate croissant', 18000, 8000, 'https://images.unsplash.com/photo-1555507036-ab1f4038808c?w=400', 'ACTIVE'),
(3, 3, 'BAK004', 'Chocolate Cake', 'Rich chocolate layer cake', 85000, 40000, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400', 'ACTIVE'),
-- Fast Food
(4, 1, 'FF001', 'Classic Burger', 'Beef patty with cheese', 45000, 20000, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400', 'ACTIVE'),
(4, 1, 'FF002', 'Chicken Burger', 'Crispy chicken burger', 40000, 18000, 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?w=400', 'ACTIVE'),
(4, 2, 'FF003', 'French Fries', 'Crispy french fries', 20000, 8000, 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400', 'ACTIVE'),
(4, 2, 'FF004', 'Onion Rings', 'Battered onion rings', 25000, 12000, 'https://images.unsplash.com/photo-1639024471283-03518883512d?w=400', 'ACTIVE'),
-- Japanese
(5, 1, 'JPN001', 'Salmon Sashimi', 'Fresh salmon sashimi', 95000, 50000, 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400', 'ACTIVE'),
(5, 1, 'JPN002', 'California Roll', 'Crab and avocado roll', 65000, 30000, 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400', 'ACTIVE'),
(5, 2, 'JPN003', 'Tonkotsu Ramen', 'Pork bone broth ramen', 75000, 35000, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400', 'ACTIVE'),
(5, 3, 'JPN004', 'Gyudon', 'Beef rice bowl', 55000, 25000, 'https://images.unsplash.com/photo-1583224944037-0ae4ab7103b0?w=400', 'ACTIVE'),
-- Thai
(6, 1, 'THAI001', 'Green Curry', 'Thai green curry with chicken', 65000, 30000, 'https://images.unsplash.com/photo-1455619452474-d2be8b1e70cd?w=400', 'ACTIVE'),
(6, 1, 'THAI002', 'Red Curry', 'Thai red curry with beef', 70000, 35000, 'https://images.unsplash.com/photo-1562565652-a0d8f0c59eb4?w=400', 'ACTIVE'),
(6, 2, 'THAI003', 'Pad Thai', 'Stir-fried rice noodles', 55000, 25000, 'https://images.unsplash.com/photo-1559314809-0d155014e29e?w=400', 'ACTIVE'),
(6, 3, 'THAI004', 'Pad See Ew', 'Stir-fried wide noodles', 50000, 23000, 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=400', 'ACTIVE'),
-- Bar
(7, 1, 'BAR001', 'Mojito', 'Classic mojito', 85000, 20000, 'https://images.unsplash.com/photo-1551538827-9c037cb4f32a?w=400', 'ACTIVE'),
(7, 1, 'BAR002', 'Old Fashioned', 'Whiskey old fashioned', 95000, 25000, 'https://images.unsplash.com/photo-1597075687490-8f673d9b2e2e?w=400', 'ACTIVE'),
(7, 2, 'BAR003', 'Draft Beer', 'Local draft beer', 45000, 15000, 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=400', 'ACTIVE'),
(7, 3, 'BAR004', 'Chicken Wings', 'Spicy buffalo wings', 65000, 30000, 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?w=400', 'ACTIVE'),
-- Catering
(8, 1, 'CAT001', 'Wedding Package', 'Full wedding catering', 5000000, 2500000, 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=400', 'ACTIVE'),
(8, 1, 'CAT002', 'Corporate Package', 'Corporate event catering', 3000000, 1500000, 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=400', 'ACTIVE'),
(8, 2, 'CAT003', 'Box Set A', 'Premium box meal', 75000, 35000, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400', 'ACTIVE'),
(8, 2, 'CAT004', 'Box Set B', 'Standard box meal', 55000, 25000, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400', 'ACTIVE');

-- Tables for each tenant
INSERT IGNORE INTO restaurant_tables (tenant_id, branch_id, table_number, capacity, status) VALUES
-- Restaurant
(1, 1, '1', 2, 'AVAILABLE'),
(1, 1, '2', 4, 'AVAILABLE'),
(1, 1, '3', 6, 'AVAILABLE'),
(1, 1, '4', 8, 'AVAILABLE'),
-- Coffee Shop
(2, 2, '1', 2, 'AVAILABLE'),
(2, 2, '2', 4, 'AVAILABLE'),
(2, 2, '3', 4, 'AVAILABLE'),
-- Bakery
(3, 3, '1', 2, 'AVAILABLE'),
(3, 3, '2', 4, 'AVAILABLE'),
-- Fast Food
(4, 4, '1', 2, 'AVAILABLE'),
(4, 4, '2', 4, 'AVAILABLE'),
(4, 4, '3', 6, 'AVAILABLE'),
-- Japanese
(5, 5, '1', 2, 'AVAILABLE'),
(5, 5, '2', 4, 'AVAILABLE'),
(5, 5, '3', 6, 'AVAILABLE'),
-- Thai
(6, 6, '1', 2, 'AVAILABLE'),
(6, 6, '2', 4, 'AVAILABLE'),
(6, 6, '3', 6, 'AVAILABLE'),
-- Bar
(7, 7, '1', 2, 'AVAILABLE'),
(7, 7, '2', 4, 'AVAILABLE'),
(7, 7, '3', 6, 'AVAILABLE'),
(7, 7, '4', 8, 'AVAILABLE'),
-- Catering (no tables, service-based)
(8, 8, '1', 10, 'AVAILABLE');

-- Suppliers
INSERT IGNORE INTO suppliers (tenant_id, supplier_code, supplier_name, contact_person, phone, email, address, status) VALUES
(1, 'SUP001', 'PT Daging Segar Jaya', 'Budi Santoso', '+62 21 1111 2222', 'budi@dagingsegar.com', 'Jl. Pasar Induk', 'ACTIVE'),
(1, 'SUP002', 'CV Sayur Segar', 'Rahmat Hidayat', '+62 21 9999 0000', 'rahmat@sayursegar.com', 'Jl. Pertanian', 'ACTIVE');

-- Customers
INSERT IGNORE INTO customers (tenant_id, customer_code, name, email, phone, address, membership_level, status) VALUES
(1, 'CUST001', 'John Doe', 'john@email.com', '+62 812 3456 7890', 'Jl. Sudirman', 'GOLD', 'ACTIVE'),
(1, 'CUST002', 'Jane Smith', 'jane@email.com', '+62 813 4567 8901', 'Jl. Thamrin', 'SILVER', 'ACTIVE');

SELECT 'Seed data inserted successfully!' AS Status;
