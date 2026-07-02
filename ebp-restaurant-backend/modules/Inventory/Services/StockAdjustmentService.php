<?php

if (!class_exists('StockAdjustmentRepository')) {
    require_once __DIR__ . '/../Repositories/StockAdjustmentRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class StockAdjustmentService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new StockAdjustmentRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createAdjustment($data, $userId, $tenantId, $branchId)
    {
        try {
            // Validate required fields
            if (empty($data['adjustment_type']) || empty($data['adjustment_date']) || empty($data['items'])) {
                return [
                    'success' => false,
                    'message' => 'Adjustment type, date, and items are required'
                ];
            }

            // Generate adjustment number
            $date = date('Ymd', strtotime($data['adjustment_date']));
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM stock_adjustments WHERE tenant_id = ? AND adjustment_number LIKE ?");
            $stmt->execute([$tenantId, "ADJ-$date-%"]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
            $adjustmentNumber = "ADJ-$date-$sequence";

            $adjustmentData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'adjustment_number' => $adjustmentNumber,
                'adjustment_type' => $data['adjustment_type'],
                'adjustment_date' => $data['adjustment_date'],
                'reason' => $data['reason'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'status' => $data['status'] ?? 'DRAFT',
                'notes' => $data['notes'] ?? null
            ];

            $adjustmentId = $this->repository->create($adjustmentData);

            // Add adjustment items
            foreach ($data['items'] as $item) {
                $itemData = [
                    'adjustment_id' => $adjustmentId,
                    'inventory_id' => $item['inventory_id'],
                    'batch_number' => $item['batch_number'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'] ?? null,
                    'total_cost' => isset($item['unit_cost']) ? $item['quantity'] * $item['unit_cost'] : null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'notes' => $item['notes'] ?? null
                ];
                $this->repository->createItem($itemData);
            }

            return [
                'success' => true,
                'message' => 'Stock adjustment created successfully',
                'adjustment_id' => $adjustmentId,
                'adjustment_number' => $adjustmentNumber
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create adjustment: ' . $e->getMessage()
            ];
        }
    }

    public function getAdjustments($tenantId, $branchId = null)
    {
        try {
            $adjustments = $this->repository->getByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Adjustments retrieved successfully',
                'data' => $adjustments
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get adjustments: ' . $e->getMessage()
            ];
        }
    }

    public function approveAdjustment($adjustmentId, $userId, $tenantId)
    {
        try {
            // Check if adjustment belongs to tenant
            $stmt = $this->db->prepare("SELECT adjustment_id, status FROM stock_adjustments WHERE adjustment_id = ? AND tenant_id = ?");
            $stmt->execute([$adjustmentId, $tenantId]);
            $adjustment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$adjustment) {
                return [
                    'success' => false,
                    'message' => 'Adjustment not found or does not belong to tenant'
                ];
            }

            if ($adjustment['status'] !== 'PENDING') {
                return [
                    'success' => false,
                    'message' => 'Only pending adjustments can be approved'
                ];
            }

            $this->repository->approve($adjustmentId, $userId);
            
            // Update inventory quantities
            $this->updateInventoryQuantities($adjustmentId);
            
            return [
                'success' => true,
                'message' => 'Adjustment approved successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve adjustment: ' . $e->getMessage()
            ];
        }
    }

    private function updateInventoryQuantities($adjustmentId)
    {
        $adjustment = $this->repository->getById($adjustmentId);
        $items = $this->repository->getItems($adjustmentId);

        foreach ($items as $item) {
            $quantity = $item['quantity'];
            
            // Determine quantity change based on adjustment type
            switch ($adjustment['adjustment_type']) {
                case 'IN':
                case 'CORRECTION':
                    $change = abs($quantity);
                    break;
                case 'OUT':
                case 'DAMAGE':
                case 'EXPIRED':
                    $change = -abs($quantity);
                    break;
                default:
                    $change = 0;
            }

            // Update inventory
            $sql = "UPDATE inventory SET quantity = quantity + ? WHERE inventory_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$change, $item['inventory_id']]);
        }
    }
}
