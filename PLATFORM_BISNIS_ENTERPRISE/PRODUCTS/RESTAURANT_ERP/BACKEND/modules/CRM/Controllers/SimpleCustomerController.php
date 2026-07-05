<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleCustomerController
{
    // Simple endpoint to get customers without middleware
    public function getCustomers($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT c.customer_id, c.customer_name, c.email, c.phone, c.address, c.loyalty_points, c.status, c.created_at
                FROM customers c
                ORDER BY c.customer_name ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($customers);
    }
}
