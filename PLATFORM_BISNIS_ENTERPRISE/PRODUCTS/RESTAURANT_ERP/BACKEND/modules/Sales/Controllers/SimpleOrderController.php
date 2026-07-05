<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('OrderService')) {
    require_once __DIR__ . '/../Services/OrderService.php';
}

class SimpleOrderController
{
    private $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    // Simple endpoint to get orders without middleware
    public function getAll($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT o.order_id, o.order_number, o.table_id, o.total_amount, o.status, o.created_at,
                t.table_number
                FROM orders o
                LEFT JOIN tables t ON o.table_id = t.table_id
                ORDER BY o.created_at DESC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($orders);
    }
}
