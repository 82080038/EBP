<?php

if (!class_exists('InventoryAdvancedRepository')) {
    require_once __DIR__ . '/../Repositories/InventoryAdvancedRepository.php';
}


class InventoryAdvancedService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new InventoryAdvancedRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function repurposeStock($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['from_product_id']) || empty($data['to_product_id']) || empty($data['quantity'])) {
                return [
                    'success' => false,
                    'message' => 'From product ID, to product ID, and quantity are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $data['repurposing_date'] = date('Y-m-d');
            $data['created_by'] = $userId;
            
            $repurposingId = $this->repository->createRepurposing($data);
            
            // Update inventory
            $this->repository->updateInventoryQuantity($data['from_product_id'], -$data['quantity']);
            $this->repository->updateInventoryQuantity($data['to_product_id'], $data['quantity'] * ($data['conversion_ratio'] ?? 1));

            return [
                'success' => true,
                'message' => 'Stock repurposed successfully',
                'repurposing_id' => $repurposingId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to repurpose stock: ' . $e->getMessage()
            ];
        }
    }

    public function zeroCostStockIn($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['product_id']) || empty($data['quantity'])) {
                return [
                    'success' => false,
                    'message' => 'Product ID and quantity are required'
                ];
            }

            // Update inventory directly for zero-cost stock
            $this->repository->updateInventoryQuantity($data['product_id'], $data['quantity']);

            return [
                'success' => true,
                'message' => 'Zero-cost stock added successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add zero-cost stock: ' . $e->getMessage()
            ];
        }
    }

    public function createStockTransfer($data, $tenantId, $fromBranchId, $userId)
    {
        try {
            if (empty($data['to_branch_id']) || empty($data['items'])) {
                return [
                    'success' => false,
                    'message' => 'To branch ID and items are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['from_branch_id'] = $fromBranchId;
            $data['transfer_date'] = date('Y-m-d');
            $data['transfer_number'] = 'TRF-' . date('Ymd') . '-' . rand(1000, 9999);
            $data['created_by'] = $userId;
            
            $transferId = $this->repository->createStockTransfer($data);
            
            foreach ($data['items'] as $item) {
                $this->repository->addStockTransferItem([
                    'transfer_id' => $transferId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'notes' => $item['notes'] ?? null
                ]);
                
                // Deduct from source branch
                $this->repository->updateInventoryQuantityByBranch($item['product_id'], $fromBranchId, -$item['quantity']);
            }

            return [
                'success' => true,
                'message' => 'Stock transfer created successfully',
                'transfer_id' => $transferId,
                'transfer_number' => $data['transfer_number']
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create stock transfer: ' . $e->getMessage()
            ];
        }
    }

    public function receiveStockTransfer($transferId, $tenantId, $toBranchId, $userId)
    {
        try {
            $transfer = $this->repository->getStockTransfer($transferId, $tenantId);
            
            if (!$transfer) {
                return [
                    'success' => false,
                    'message' => 'Transfer not found'
                ];
            }

            if ($transfer['status'] !== 'IN_TRANSIT') {
                return [
                    'success' => false,
                    'message' => 'Transfer is not in transit'
                ];
            }

            $items = $this->repository->getStockTransferItems($transferId);
            
            foreach ($items as $item) {
                // Add to destination branch
                $this->repository->updateInventoryQuantityByBranch($item['product_id'], $toBranchId, $item['quantity']);
            }

            $this->repository->updateStockTransferStatus($transferId, 'RECEIVED', $userId);

            return [
                'success' => true,
                'message' => 'Stock transfer received successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to receive stock transfer: ' . $e->getMessage()
            ];
        }
    }

    public function getStockTransfers($tenantId, $branchId, $status = null)
    {
        try {
            $transfers = $this->repository->getStockTransfers($tenantId, $branchId, $status);
            
            return [
                'success' => true,
                'message' => 'Stock transfers retrieved successfully',
                'data' => $transfers
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get stock transfers: ' . $e->getMessage()
            ];
        }
    }

    public function getRepurposingHistory($tenantId, $branchId, $dateFrom = null, $dateTo = null)
    {
        try {
            $history = $this->repository->getRepurposingHistory($tenantId, $branchId, $dateFrom, $dateTo);
            
            return [
                'success' => true,
                'message' => 'Repurposing history retrieved successfully',
                'data' => $history
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get repurposing history: ' . $e->getMessage()
            ];
        }
    }
}
