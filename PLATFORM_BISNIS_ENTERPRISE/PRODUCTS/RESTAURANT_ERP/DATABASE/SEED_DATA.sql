USE ebp_restaurant_erp;

-- Tenant
INSERT IGNORE INTO tenants (tenant_code, tenant_name, business_type, status) VALUES
('EBP_RESTAURANT', 'EBP Restaurant Demo', 'RESTAURANT', 'ACTIVE');

-- Company
INSERT IGNORE INTO companies (tenant_id, company_code, company_name, status) VALUES
(1, 'EBP_COMPANY', 'EBP Restaurant Company', 'ACTIVE');

-- Branches
INSERT IGNORE INTO branches (tenant_id, company_id, branch_code, branch_name, address, phone, opening_time, closing_time, status) VALUES
(1, 1, 'JKT001', 'EBP Restaurant Jakarta', 'Jl. Sudirman No. 123', '+62 21 1234 5678', '10:00:00', '23:00:00', 'ACTIVE');

-- Menu Categories
INSERT IGNORE INTO menu_categories (tenant_id, category_code, category_name, description, display_order, status) VALUES
(1, 'CAT001', 'Main Course', 'Primary dishes', 1, 'ACTIVE'),
(1, 'CAT002', 'Appetizers', 'Starters', 2, 'ACTIVE'),
(1, 'CAT003', 'Beverages', 'Drinks', 3, 'ACTIVE'),
(1, 'CAT004', 'Desserts', 'Sweet treats', 4, 'ACTIVE');

-- Menu Items
INSERT IGNORE INTO menus (tenant_id, category_id, menu_code, menu_name, description, selling_price, cost_price, image_url, status) VALUES
(1, 1, 'PROD001', 'Nasi Goreng Spesial', 'Fried rice with chicken', 35000, 20000, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=400', 'ACTIVE'),
(1, 1, 'PROD002', 'Ayam Bakar Madu', 'Grilled chicken with honey', 45000, 28000, 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=400', 'ACTIVE'),
(1, 2, 'PROD003', 'Gado-Gado', 'Indonesian salad', 28000, 16000, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400', 'ACTIVE'),
(1, 3, 'PROD004', 'Es Teh Manis', 'Sweet iced tea', 8000, 2000, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400', 'ACTIVE'),
(1, 3, 'PROD005', 'Kopi Susu Gula Aren', 'Coffee with palm sugar', 22000, 8000, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=400', 'ACTIVE'),
(1, 4, 'PROD006', 'Es Teler', 'Mixed fruit dessert', 28000, 15000, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=400', 'ACTIVE');

-- Tables
INSERT IGNORE INTO restaurant_tables (tenant_id, branch_id, table_number, capacity, status) VALUES
(1, 1, '1', 2, 'AVAILABLE'),
(1, 1, '2', 4, 'AVAILABLE'),
(1, 1, '3', 6, 'AVAILABLE'),
(1, 1, '4', 8, 'AVAILABLE');

-- Suppliers
INSERT IGNORE INTO suppliers (tenant_id, supplier_code, supplier_name, contact_person, phone, email, address, status) VALUES
(1, 'SUP001', 'PT Daging Segar Jaya', 'Budi Santoso', '+62 21 1111 2222', 'budi@dagingsegar.com', 'Jl. Pasar Induk', 'ACTIVE'),
(1, 'SUP002', 'CV Sayur Segar', 'Rahmat Hidayat', '+62 21 9999 0000', 'rahmat@sayursegar.com', 'Jl. Pertanian', 'ACTIVE');

-- Customers
INSERT IGNORE INTO customers (tenant_id, customer_code, name, email, phone, address, membership_level, status) VALUES
(1, 'CUST001', 'John Doe', 'john@email.com', '+62 812 3456 7890', 'Jl. Sudirman', 'GOLD', 'ACTIVE'),
(1, 'CUST002', 'Jane Smith', 'jane@email.com', '+62 813 4567 8901', 'Jl. Thamrin', 'SILVER', 'ACTIVE');

SELECT 'Seed data inserted successfully!' AS Status;
