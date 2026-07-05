<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleKitchenController
{
    // Simple endpoint to get kitchen orders without middleware
    public function getKitchenOrders($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT ko.kitchen_order_id, ko.kitchen_order_number, ko.order_id, ko.status, ko.priority, ko.started_at, ko.completed_at,
                o.order_number, t.table_number
                FROM kitchen_orders ko
                LEFT JOIN orders o ON ko.order_id = o.order_id
                LEFT JOIN tables t ON o.table_id = t.table_id
                ORDER BY ko.created_at DESC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $kitchenOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($kitchenOrders);
    }
}
