-- Sample data for accounting module testing

-- Sample Customers
INSERT INTO customers (tenant_id, branch_id, customer_code, customer_name, email, phone, address, is_active, created_at) VALUES
(1, 2, 'CUST001', 'Test Customer 1', 'customer1@test.com', '08123456789', 'Jakarta', 1, NOW()),
(1, 2, 'CUST002', 'Test Customer 2', 'customer2@test.com', '08123456790', 'Bandung', 1, NOW());

-- Sample Suppliers
INSERT INTO suppliers (tenant_id, branch_id, supplier_code, supplier_name, email, phone, address, is_active, created_at) VALUES
(1, 2, 'SUPP001', 'Test Supplier 1', 'supplier1@test.com', '08123456791', 'Surabaya', 1, NOW()),
(1, 2, 'SUPP002', 'Test Supplier 2', 'supplier2@test.com', '08123456792', 'Medan', 1, NOW());

-- Sample Bank Accounts
INSERT INTO bank_accounts (tenant_id, branch_id, bank_name, account_number, account_name, account_type, balance, is_active, created_at) VALUES
(1, 2, 'BCA', '1234567890', 'Main Operating Account', 'CHECKING', 1000000, 1, NOW()),
(1, 2, 'Mandiri', '0987654321', 'Petty Cash Account', 'SAVINGS', 500000, 1, NOW());
