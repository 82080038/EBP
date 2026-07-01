<?php

require_once __DIR__ . '/../../../config/database.php';

class MobileOrderService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getMobileMenu($tenantId, $branchId)
    {
        try {
            // Optimized query for mobile - lightweight data
            $sql = "
                SELECT 
                    p.product_id,
                    p.product_name,
                    p.price,
                    p.image_url,
                    p.is_available,
                    c.category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.tenant_id = ? 
                AND p.is_available = TRUE
                AND p.deleted_at IS NULL
                ORDER BY c.sort_order, p.product_name
                LIMIT 100
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => 'Mobile menu retrieved successfully',
                'data' => $products
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get mobile menu: ' . $e->getMessage()
            ];
        }
    }

    public function getQuickOrder($tenantId, $branchId, $productId)
    {
        try {
            // Get product details for quick order
            $sql = "
                SELECT 
                    p.product_id,
                    p.product_name,
                    p.price,
                    p.description,
                    p.image_url,
                    p.is_available
                FROM products p
                WHERE p.tenant_id = ? 
                AND p.product_id = ?
                AND p.is_available = TRUE
                AND p.deleted_at IS NULL
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }

            return [
                'success' => true,
                'message' => 'Quick order data retrieved successfully',
                'data' => $product
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get quick order: ' . $e->getMessage()
            ];
        }
    }
}
