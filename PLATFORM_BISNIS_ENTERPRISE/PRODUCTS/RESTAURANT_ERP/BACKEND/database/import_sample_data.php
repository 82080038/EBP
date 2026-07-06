<?php
$host = 'localhost';
$dbname = 'ebp_restaurant_db';
$username = 'ebp_app';
$password = 'ebp_secure_password_2026';
$socket = '/opt/lampp/var/mysql/mysql.sock';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Sample Customers (use INSERT IGNORE to handle duplicates)
    $db->exec("INSERT IGNORE INTO customers (tenant_id, customer_name, email, phone, address, status, created_at) VALUES
    (1, 'Test Customer 1', 'customer1@test.com', '08123456789', 'Jakarta', 'ACTIVE', NOW()),
    (1, 'Test Customer 2', 'customer2@test.com', '08123456790', 'Bandung', 'ACTIVE', NOW())");

    // Sample Suppliers (use INSERT IGNORE to handle duplicates)
    $db->exec("INSERT IGNORE INTO suppliers (tenant_id, supplier_code, supplier_name, email, phone, address, status, created_at) VALUES
    (1, 'SUPP001', 'Test Supplier 1', 'supplier1@test.com', '08123456791', 'Surabaya', 'ACTIVE', NOW()),
    (1, 'SUPP002', 'Test Supplier 2', 'supplier2@test.com', '08123456792', 'Medan', 'ACTIVE', NOW())");

    // Sample Bank Accounts (use INSERT IGNORE to handle duplicates)
    $db->exec("INSERT IGNORE INTO bank_accounts (tenant_id, branch_id, bank_name, account_number, account_name, account_type, balance, is_active, created_at) VALUES
    (1, 2, 'BCA', '1234567890', 'Main Operating Account', 'CHECKING', 1000000, 1, NOW()),
    (1, 2, 'Mandiri', '0987654321', 'Petty Cash Account', 'SAVINGS', 500000, 1, NOW())");

    echo "Sample data imported successfully\n";
} catch (PDOException $e) {
    echo "Error importing data: " . $e->getMessage() . "\n";
}
