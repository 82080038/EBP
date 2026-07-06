<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleSupplierController
{
    // Simple endpoint to get suppliers without middleware
    public function getSuppliers($request = null)
    {
                $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $db = new PDO($dsn, $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT s.supplier_id, s.supplier_name, s.contact_person, s.phone, s.email, s.address, s.status
                FROM suppliers s
                ORDER BY s.supplier_name ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($suppliers);
    }
}
