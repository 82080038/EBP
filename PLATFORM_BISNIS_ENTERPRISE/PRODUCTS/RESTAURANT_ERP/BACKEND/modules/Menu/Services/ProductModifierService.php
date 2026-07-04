<?php

if (!class_exists('ProductModifierRepository')) {
    require_once __DIR__ . '/../Repositories/ProductModifierRepository.php';
}

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class ProductModifierService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new ProductModifierRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createModifierGroup($data, $tenantId)
    {
        try {
            // Validate required fields
            if (empty($data['group_code']) || empty($data['group_name'])) {
                return [
                    'success' => false,
                    'message' => 'Group code and group name are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $result = $this->repository->createGroup($data);
            
            return [
                'success' => true,
                'message' => 'Modifier group created successfully',
                'modifier_group_id' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create modifier group: ' . $e->getMessage()
            ];
        }
    }

    public function createModifier($data, $tenantId)
    {
        try {
            // Validate required fields
            if (empty($data['modifier_group_id']) || empty($data['modifier_code']) || empty($data['modifier_name'])) {
                return [
                    'success' => false,
                    'message' => 'Modifier group ID, modifier code, and modifier name are required'
                ];
            }

            // Check if group belongs to tenant
            $stmt = $this->db->prepare("SELECT modifier_group_id FROM product_modifier_groups WHERE modifier_group_id = ? AND tenant_id = ?");
            $stmt->execute([$data['modifier_group_id'], $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Modifier group not found or does not belong to tenant'
                ];
            }

            $result = $this->repository->createModifier($data);
            
            return [
                'success' => true,
                'message' => 'Modifier created successfully',
                'modifier_id' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create modifier: ' . $e->getMessage()
            ];
        }
    }

    public function assignModifierToProduct($data, $tenantId)
    {
        try {
            // Validate required fields
            if (empty($data['product_id']) || empty($data['modifier_group_id'])) {
                return [
                    'success' => false,
                    'message' => 'Product ID and modifier group ID are required'
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

            // Check if group belongs to tenant
            $stmt = $this->db->prepare("SELECT modifier_group_id FROM product_modifier_groups WHERE modifier_group_id = ? AND tenant_id = ?");
            $stmt->execute([$data['modifier_group_id'], $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Modifier group not found or does not belong to tenant'
                ];
            }

            $result = $this->repository->createAssignment($data);
            
            return [
                'success' => true,
                'message' => 'Modifier assigned to product successfully',
                'assignment_id' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to assign modifier: ' . $e->getMessage()
            ];
        }
    }

    public function getModifierGroups($tenantId)
    {
        try {
            $groups = $this->repository->getGroupsByTenant($tenantId);
            
            return [
                'success' => true,
                'message' => 'Modifier groups retrieved successfully',
                'data' => $groups
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get modifier groups: ' . $e->getMessage()
            ];
        }
    }

    public function getModifiersByGroup($groupId, $tenantId)
    {
        try {
            // Check if group belongs to tenant
            $stmt = $this->db->prepare("SELECT modifier_group_id FROM product_modifier_groups WHERE modifier_group_id = ? AND tenant_id = ?");
            $stmt->execute([$groupId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Modifier group not found or does not belong to tenant'
                ];
            }

            $modifiers = $this->repository->getModifiersByGroup($groupId);
            
            return [
                'success' => true,
                'message' => 'Modifiers retrieved successfully',
                'data' => $modifiers
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get modifiers: ' . $e->getMessage()
            ];
        }
    }

    public function getProductModifiers($productId, $tenantId)
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

            $modifiers = $this->repository->getModifiersByProduct($productId);
            
            return [
                'success' => true,
                'message' => 'Product modifiers retrieved successfully',
                'data' => $modifiers
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get product modifiers: ' . $e->getMessage()
            ];
        }
    }
}
