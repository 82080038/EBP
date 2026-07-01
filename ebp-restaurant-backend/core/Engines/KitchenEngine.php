<?php

class KitchenEngine
{

    private $db;



    public function __construct($db)
    {

        $this->db = $db;

    }



    public function createKitchenOrder($orderId)
    {

        $sql = "
            INSERT INTO kitchen_orders
            (order_id, status, priority, created_at)
            VALUES (?, 'PENDING', 'NORMAL', NOW())
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$orderId]);



        $kitchenOrderId = $this->db->lastInsertId();



        $sql = "
            INSERT INTO kitchen_order_details
            (kitchen_order_id, menu_id, qty, status, created_at)
            SELECT ?, product_id, quantity, 'PENDING', NOW()
            FROM order_items
            WHERE order_id = ?
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$kitchenOrderId, $orderId]);



        return $kitchenOrderId;

    }

}
