<?php
if (!class_exists('database')) {
    require_once __DIR__ . "/../../../config/database.php";
}

class OrderRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function saveOrder($data)
    {
        // Generate order number
        $tenantId = $data['tenant_id'];
        $date = date('Ymd');
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM orders WHERE tenant_id = ? AND order_number LIKE ?");
        $stmt->execute([$tenantId, "ORD-$date-%"]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        $orderNumber = "ORD-$date-$sequence";
        
        $fields = ['tenant_id', 'branch_id', 'order_number', 'subtotal', 'total_amount', 'status'];
        $values = [$tenantId, $data['branch_id'], $orderNumber, $data['subtotal'], $data['total_amount'], $data['status'] ?? 'PENDING'];
        $placeholders = ['?', '?', '?', '?', '?', '?'];
        $fieldsList = $fields;
        
        $optionalFields = ['user_id', 'table_id', 'order_type', 'is_open_order', 'is_priority', 'is_held', 
                          'hold_reason', 'customer_name', 'customer_phone', 'customer_address', 
                          'delivery_fee', 'delivery_time', 'notes', 'paid_amount', 'payment_status'];
        
        foreach ($optionalFields as $field) {
            if (isset($data[$field])) {
                $values[] = $data[$field];
                $placeholders[] = '?';
                $fieldsList[] = $field;
            }
        }
        
        $sql = "INSERT INTO orders (" . implode(', ', $fieldsList) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        
        return $this->db->lastInsertId();
    }

    public function saveDetail($orderId, $item)
    {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['qty'],
            $item['price'],
            $item['price'] * $item['qty']
        ]);
    }

    public function updateOrder($orderId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $orderId;
        
        $sql = "UPDATE orders SET " . implode(', ', $setClauses) . " WHERE order_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function deleteOrderItems($orderId)
    {
        $sql = "DELETE FROM order_items WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
    }

    public function getOrderById($orderId)
    {
        $sql = "SELECT * FROM orders WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createSplitBill($orderId, $splitType, $totalSplits)
    {
        $sql = "INSERT INTO split_bills (order_id, split_type, total_splits) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $splitType, $totalSplits]);
        return $this->db->lastInsertId();
    }

    public function createSplitBillItem($splitBillId, $splitItem)
    {
        $sql = "INSERT INTO split_bill_items (split_bill_id, order_item_id, quantity, amount) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $splitBillId,
            $splitItem['order_item_id'],
            $splitItem['quantity'],
            $splitItem['amount']
        ]);
    }

    public function createPayment($orderId, $paymentMethod, $amount, $referenceNumber)
    {
        $sql = "INSERT INTO payments (order_id, payment_method, amount, reference_number, payment_status) VALUES (?, ?, ?, ?, 'COMPLETED')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $paymentMethod, $amount, $referenceNumber]);
        return $this->db->lastInsertId();
    }
}
