<?php

if (!class_exists('OfflineSyncRepository')) {
    require_once __DIR__ . '/../Repositories/OfflineSyncRepository.php';
}


class OfflineSyncService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new OfflineSyncRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function queueOperation($tenantId, $branchId, $userId, $operationType, $entityType, $entityData)
    {
        try {
            $syncId = $this->repository->createSyncQueue([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'user_id' => $userId,
                'operation_type' => $operationType,
                'entity_type' => $entityType,
                'entity_data' => json_encode($entityData),
                'status' => 'PENDING'
            ]);

            return [
                'success' => true,
                'message' => 'Operation queued for sync',
                'sync_id' => $syncId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to queue operation: ' . $e->getMessage()
            ];
        }
    }

    public function syncPendingOperations($tenantId, $branchId)
    {
        try {
            $pendingOperations = $this->repository->getPendingOperations($tenantId, $branchId);
            
            $syncedCount = 0;
            $failedCount = 0;

            foreach ($pendingOperations as $operation) {
                $result = $this->processSyncOperation($operation);
                
                if ($result['success']) {
                    $this->repository->updateSyncStatus($operation['sync_id'], 'SYNCED', null);
                    $syncedCount++;
                } else {
                    $this->repository->updateSyncStatus($operation['sync_id'], 'FAILED', $result['message']);
                    $failedCount++;
                }
            }

            return [
                'success' => true,
                'message' => 'Sync completed',
                'synced_count' => $syncedCount,
                'failed_count' => $failedCount
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
        }
    }

    public function resolveConflict($syncId, $resolution, $resolvedData = null)
    {
        try {
            $operation = $this->repository->getSyncOperation($syncId);
            
            if (!$operation) {
                return [
                    'success' => false,
                    'message' => 'Sync operation not found'
                ];
            }

            if ($resolution === 'KEEP_LOCAL') {
                // Force sync local data to server
                $result = $this->processSyncOperation($operation);
                if ($result['success']) {
                    $this->repository->updateSyncStatus($syncId, 'SYNCED', null);
                }
            } elseif ($resolution === 'KEEP_SERVER') {
                // Discard local changes, keep server data
                $this->repository->updateSyncStatus($syncId, 'DISCARDED', 'Kept server version');
            } elseif ($resolution === 'MERGE') {
                // Merge data
                $result = $this->processSyncOperation($operation, $resolvedData);
                if ($result['success']) {
                    $this->repository->updateSyncStatus($syncId, 'SYNCED', null);
                }
            }

            return [
                'success' => true,
                'message' => 'Conflict resolved'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to resolve conflict: ' . $e->getMessage()
            ];
        }
    }

    public function getSyncStatus($tenantId, $branchId)
    {
        try {
            $status = $this->repository->getSyncStatus($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Sync status retrieved',
                'data' => $status
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get sync status: ' . $e->getMessage()
            ];
        }
    }

    public function getConflicts($tenantId, $branchId)
    {
        try {
            $conflicts = $this->repository->getConflicts($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Conflicts retrieved',
                'data' => $conflicts
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get conflicts: ' . $e->getMessage()
            ];
        }
    }

    private function processSyncOperation($operation, $overrideData = null)
    {
        $entityData = $overrideData ? $overrideData : json_decode($operation['entity_data'], true);
        
        switch ($operation['entity_type']) {
            case 'ORDER':
                return $this->syncOrder($operation['operation_type'], $entityData);
            case 'PAYMENT':
                return $this->syncPayment($operation['operation_type'], $entityData);
            case 'INVENTORY':
                return $this->syncInventory($operation['operation_type'], $entityData);
            case 'CUSTOMER':
                return $this->syncCustomer($operation['operation_type'], $entityData);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown entity type: ' . $operation['entity_type']
                ];
        }
    }

    private function syncOrder($operationType, $data)
    {
        try {
            if ($operationType === 'CREATE') {
                $sql = "INSERT INTO orders (tenant_id, branch_id, table_id, customer_id, user_id, order_number, order_type, status, total_amount, tax, discount, paid_amount, payment_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $data['tenant_id'] ?? 1,
                    $data['branch_id'] ?? 1,
                    $data['table_id'] ?? null,
                    $data['customer_id'] ?? null,
                    $data['user_id'] ?? 1,
                    $data['order_number'] ?? 'OFF-' . rand(1000, 9999),
                    $data['order_type'] ?? 'DINE_IN',
                    $data['status'] ?? 'PENDING',
                    $data['total_amount'] ?? 0,
                    $data['tax'] ?? 0,
                    $data['discount'] ?? 0,
                    $data['paid_amount'] ?? 0,
                    $data['payment_status'] ?? 'UNPAID',
                    $data['notes'] ?? null
                ]);
            } elseif ($operationType === 'UPDATE') {
                $sql = "UPDATE orders SET status = ?, total_amount = ?, paid_amount = ?, payment_status = ? WHERE order_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $data['status'] ?? 'PENDING',
                    $data['total_amount'] ?? 0,
                    $data['paid_amount'] ?? 0,
                    $data['payment_status'] ?? 'UNPAID',
                    $data['order_id'] ?? 0
                ]);
            }

            return ['success' => true];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to sync order: ' . $e->getMessage()
            ];
        }
    }

    private function syncPayment($operationType, $data)
    {
        try {
            if ($operationType === 'CREATE') {
                $sql = "INSERT INTO order_payments (order_id, payment_method_id, amount, reference_number) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $data['order_id'],
                    $data['payment_method_id'],
                    $data['amount'],
                    $data['reference_number'] ?? null
                ]);
            }

            return ['success' => true];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to sync payment: ' . $e->getMessage()
            ];
        }
    }

    private function syncInventory($operationType, $data)
    {
        try {
            if ($operationType === 'UPDATE') {
                $sql = "UPDATE inventory SET quantity = ? WHERE inventory_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $data['quantity'],
                    $data['inventory_id']
                ]);
            }

            return ['success' => true];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to sync inventory: ' . $e->getMessage()
            ];
        }
    }

    private function syncCustomer($operationType, $data)
    {
        try {
            if ($operationType === 'CREATE') {
                $sql = "INSERT INTO customers (tenant_id, branch_id, customer_name, phone, email, address, loyalty_points, customer_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $data['tenant_id'],
                    $data['branch_id'],
                    $data['customer_name'],
                    $data['phone'] ?? null,
                    $data['email'] ?? null,
                    $data['address'] ?? null,
                    $data['loyalty_points'] ?? 0,
                    $data['customer_level'] ?? 'BRONZE'
                ]);
            } elseif ($operationType === 'UPDATE') {
                $sql = "UPDATE customers SET loyalty_points = ?, customer_level = ? WHERE customer_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $data['loyalty_points'],
                    $data['customer_level'],
                    $data['customer_id']
                ]);
            }

            return ['success' => true];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to sync customer: ' . $e->getMessage()
            ];
        }
    }
}
