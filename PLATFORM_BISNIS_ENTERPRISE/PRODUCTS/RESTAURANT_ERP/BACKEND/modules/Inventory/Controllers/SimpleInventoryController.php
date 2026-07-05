<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleInventoryController
{
    // Simple endpoint to get inventory without middleware
    public function getInventory($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT i.inventory_id, i.product_id, i.quantity, i.unit, i.minimum_stock, i.maximum_stock, i.status,
                p.product_name, p.product_code
                FROM inventory i
                LEFT JOIN products p ON i.product_id = p.product_id
                ORDER BY p.product_name";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($inventory);
    }

    // Simple endpoint to get low stock items without middleware
    public function getLowStock($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT i.inventory_id, i.product_id, i.quantity, i.minimum_stock, i.unit,
                p.product_name, p.product_code
                FROM inventory i
                LEFT JOIN products p ON i.product_id = p.product_id
                WHERE i.quantity <= i.minimum_stock
                ORDER BY i.quantity ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($lowStock);
    }
}
