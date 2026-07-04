<?php

use PDO;

class KitchenEngine
{

    private $db;



    public function __construct($db)
    {

        $this->db = $db;

    }



    public function createKitchenOrder($orderId)
    {
        // Get order details to determine tenant and branch
        $sql = "SELECT tenant_id, branch_id FROM orders WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return false;
        }
        
        $tenantId = $order['tenant_id'];
        $branchId = $order['branch_id'];
        
        // Generate kitchen order number
        $sql = "SELECT COUNT(*) as count FROM kitchen_orders WHERE tenant_id = ? AND branch_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] + 1;
        $kitchenOrderNumber = 'KIT-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $sql = "
            INSERT INTO kitchen_orders
            (tenant_id, branch_id, order_id, kitchen_order_number, status, priority, created_at)
            VALUES (?, ?, ?, ?, 'PENDING', 'NORMAL', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $orderId, $kitchenOrderNumber]);

        $kitchenOrderId = $this->db->lastInsertId();

        // Get order items and insert into kitchen order items
        $sql = "SELECT order_item_id, product_id, quantity FROM order_items WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orderItems as $item) {
            $sql = "
                INSERT INTO kitchen_order_items
                (kitchen_order_id, order_item_id, product_id, quantity, status, created_at)
                VALUES (?, ?, ?, ?, 'PENDING', NOW())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$kitchenOrderId, $item['order_item_id'], $item['product_id'], $item['quantity']]);
        }

        return $kitchenOrderId;
    }

}
