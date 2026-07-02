<?php

if (!class_exists('ProductVariantRepository')) {
    require_once __DIR__ . '/../Repositories/ProductVariantRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}

class ProductVariantService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new ProductVariantRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createVariant($data, $tenantId)
    {
        try {
            // Validate required fields
            if (empty($data['product_id']) || empty($data['variant_code']) || empty($data['variant_name'])) {
                return [
                    'success' => false,
                    'message' => 'Product ID, variant code, and variant name are required'
                ];
            }

            // Check if product belongs to tenant
            $stmt = $this->db->prepare("SELECT product_id FROM products WHERE product_id = ? AND tenant_id = ?");
            $stmt->execute([$data['product_id'], $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Product not found or does not belong to tenant'
                ];
            }

            $result = $this->repository->create($data);
            
            return [
                'success' => true,
                'message' => 'Variant created successfully',
                'variant_id' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create variant: ' . $e->getMessage()
            ];
        }
    }

    public function getVariantsByProduct($productId, $tenantId)
    {
        try {
            // Check if product belongs to tenant
            $stmt = $this->db->prepare("SELECT product_id FROM products WHERE product_id = ? AND tenant_id = ?");
            $stmt->execute([$productId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Product not found or does not belong to tenant'
                ];
            }

            $variants = $this->repository->getByProduct($productId);
            
            return [
                'success' => true,
                'message' => 'Variants retrieved successfully',
                'data' => $variants
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get variants: ' . $e->getMessage()
            ];
        }
    }

    public function updateVariant($variantId, $data, $tenantId)
    {
        try {
            // Check if variant belongs to tenant's product
            $stmt = $this->db->prepare("
                SELECT pv.variant_id 
                FROM product_variants pv
                INNER JOIN products p ON pv.product_id = p.product_id
                WHERE pv.variant_id = ? AND p.tenant_id = ?
            ");
            $stmt->execute([$variantId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Variant not found or does not belong to tenant'
                ];
            }

            $this->repository->update($variantId, $data);
            
            return [
                'success' => true,
                'message' => 'Variant updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update variant: ' . $e->getMessage()
            ];
        }
    }

    public function deleteVariant($variantId, $tenantId)
    {
        try {
            // Check if variant belongs to tenant's product
            $stmt = $this->db->prepare("
                SELECT pv.variant_id 
                FROM product_variants pv
                INNER JOIN products p ON pv.product_id = p.product_id
                WHERE pv.variant_id = ? AND p.tenant_id = ?
            ");
            $stmt->execute([$variantId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Variant not found or does not belong to tenant'
                ];
            }

            $this->repository->delete($variantId);
            
            return [
                'success' => true,
                'message' => 'Variant deleted successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete variant: ' . $e->getMessage()
            ];
        }
    }
}
