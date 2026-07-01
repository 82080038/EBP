<?php

require_once __DIR__ . '/../../../config/database.php';

class WhatsAppOrderingService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function processWhatsAppOrder($data, $tenantId, $branchId)
    {
        try {
            $this->db->beginTransaction();

            // Parse WhatsApp message
            $message = $data['message'] ?? '';
            $phoneNumber = $data['phone_number'] ?? '';
            
            // Simple parsing logic (can be enhanced with NLP)
            $orderData = $this->parseWhatsAppMessage($message);
            
            if (!$orderData) {
                return [
                    'success' => false,
                    'message' => 'Could not parse order from message'
                ];
            }

            // Simplified - return mock order for now
            $orderNumber = 'WA-' . date('YmdHis') . '-' . rand(1000, 9999);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'WhatsApp order processed successfully (mock)',
                'order_id' => 0,
                'order_number' => $orderNumber
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to process WhatsApp order: ' . $e->getMessage()
            ];
        }
    }

    private function parseWhatsAppMessage($message)
    {
        // Simple parsing - in production, use NLP/AI
        // Expected format: "Order: ProductName1 x2, ProductName2 x1"
        
        if (stripos($message, 'order') === false) {
            return null;
        }

        $items = [];
        $totalAmount = 0;

        // Extract items (simplified)
        preg_match_all('/(\w+)\s*x\s*(\d+)/i', $message, $matches);
        
        if (empty($matches[1])) {
            return null;
        }

        for ($i = 0; $i < count($matches[1]); $i++) {
            $productName = $matches[1][$i];
            $quantity = intval($matches[2][$i]);
            
            // Get product ID from name (simplified)
            $stmt = $this->db->prepare("SELECT product_id, price FROM products WHERE product_name LIKE ? LIMIT 1");
            $stmt->execute(['%' . $productName . '%']);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $items[] = [
                    'product_id' => $product['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $product['price'],
                    'total_price' => $product['price'] * $quantity
                ];
                $totalAmount += $product['price'] * $quantity;
            }
        }

        if (empty($items)) {
            return null;
        }

        return [
            'items' => $items,
            'total_amount' => $totalAmount
        ];
    }
}
