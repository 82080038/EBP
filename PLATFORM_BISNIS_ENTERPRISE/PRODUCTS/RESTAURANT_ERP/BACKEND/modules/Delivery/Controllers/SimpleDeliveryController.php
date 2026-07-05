<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleDeliveryController
{
    // Simple endpoint to get deliveries without middleware
    public function getDeliveries($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT d.delivery_id, d.order_id, d.customer_name, d.delivery_address, d.phone, 
                d.delivery_time, d.delivery_fee, d.status, d.driver_name
                FROM deliveries d
                WHERE d.status IN ('PENDING', 'IN_TRANSIT')
                ORDER BY d.delivery_time ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($deliveries);
    }
}
