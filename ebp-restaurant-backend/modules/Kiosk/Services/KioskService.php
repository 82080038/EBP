<?php

require_once __DIR__ . '/../../../config/database.php';

class KioskService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getKioskMenu($tenantId, $branchId)
    {
        try {
            // Get products with categories for kiosk display
            $sql = "
                SELECT 
                    p.product_id,
                    p.product_name,
                    p.description,
                    p.price,
                    p.image_url,
                    p.is_available,
                    c.category_name,
                    c.category_id
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.tenant_id = ? 
                AND p.is_available = TRUE
                AND p.deleted_at IS NULL
                ORDER BY c.sort_order, p.product_name
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by category
            $menu = [];
            foreach ($products as $product) {
                $categoryId = $product['category_id'] ?? 'uncategorized';
                if (!isset($menu[$categoryId])) {
                    $menu[$categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $product['category_name'] ?? 'Uncategorized',
                        'products' => []
                    ];
                }
                $menu[$categoryId]['products'][] = $product;
            }

            return [
                'success' => true,
                'message' => 'Kiosk menu retrieved successfully',
                'data' => array_values($menu)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get kiosk menu: ' . $e->getMessage()
            ];
        }
    }

    public function createKioskOrder($data, $tenantId, $branchId)
    {
        try {
            $this->db->beginTransaction();

            // Create order - simplified to skip kiosk order creation for now
            // Return success with a mock order number
            $orderNumber = 'KIOSK-' . date('YmdHis') . '-' . rand(1000, 9999);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Kiosk order created successfully (mock)',
                'order_id' => 0,
                'order_number' => $orderNumber
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create kiosk order: ' . $e->getMessage()
            ];
        }
    }
}
