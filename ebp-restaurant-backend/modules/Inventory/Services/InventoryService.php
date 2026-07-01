<?php

require_once __DIR__ . '/../Repositories/InventoryRepository.php';
require_once __DIR__ . '/../../../core/Transaction.php';
require_once __DIR__ . '/../../../core/Audit.php';

class InventoryService
{
    private $inventoryRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->inventoryRepository = new InventoryRepository();
        $this->transaction = new Transaction();
        $this->audit = new Audit();
    }

    public function getAllInventory(int $tenantId, ?int $branchId = null): array
    {
        $inventory = $this->inventoryRepository->findAll($tenantId, $branchId);
        return array_map(function($i) { return $i->toArray(); }, $inventory);
    }

    public function getLowStock(int $tenantId, ?int $branchId = null): array
    {
        $inventory = $this->inventoryRepository->getLowStock($tenantId, $branchId);
        return array_map(function($i) { return $i->toArray(); }, $inventory);
    }

    public function getInventory(int $tenantId, int $inventoryId): ?array
    {
        $inventory = $this->inventoryRepository->findById($tenantId, $inventoryId);
        return $inventory ? $inventory->toArray() : null;
    }

    public function createInventory(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            $inventory = new \Modules\Inventory\Models\Inventory($data);
            
            // Check if inventory already exists for this product and branch
            $existing = $this->inventoryRepository->findByProduct(
                $tenantId,
                $inventory->branch_id,
                $inventory->product_id
            );
            
            if ($existing) {
                $this->transaction->rollback();
                return false;
            }
            
            $result = $this->inventoryRepository->create($inventory);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'INVENTORY',
                    'action' => 'CREATE_INVENTORY',
                    'record_id' => $this->transaction->getLastInsertId(),
                    'table_name' => 'inventory',
                    'new_values' => json_encode($data)
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function updateInventory(int $tenantId, int $inventoryId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldInventory = $this->inventoryRepository->findById($tenantId, $inventoryId);
            
            $data['tenant_id'] = $tenantId;
            $data['inventory_id'] = $inventoryId;
            $inventory = new \Modules\Inventory\Models\Inventory($data);
            
            $result = $this->inventoryRepository->update($inventory);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'INVENTORY',
                    'action' => 'UPDATE_INVENTORY',
                    'record_id' => $inventoryId,
                    'table_name' => 'inventory',
                    'old_values' => json_encode($oldInventory->toArray()),
                    'new_values' => json_encode($data)
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function adjustStock(int $tenantId, int $branchId, int $productId, float $quantity, string $type, array $reference = []): bool
    {
        $this->transaction->begin();
        
        try {
            // Get current inventory
            $inventory = $this->inventoryRepository->findByProduct($tenantId, $branchId, $productId);
            
            if (!$inventory) {
                // Create inventory if it doesn't exist
                $inventory = new \Modules\Inventory\Models\Inventory([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'unit' => 'unit',
                    'status' => 'ACTIVE'
                ]);
                $this->inventoryRepository->create($inventory);
                $inventory = $this->inventoryRepository->findByProduct($tenantId, $branchId, $productId);
            }
            
            $oldQuantity = $inventory->quantity;
            
            // Calculate new quantity
            if ($type === 'IN') {
                $newQuantity = $oldQuantity + $quantity;
            } elseif ($type === 'OUT') {
                $newQuantity = $oldQuantity - $quantity;
                if ($newQuantity < 0) {
                    $this->transaction->rollback();
                    return false;
                }
            } else {
                $newQuantity = $quantity;
            }
            
            // Update inventory quantity
            $result = $this->inventoryRepository->updateQuantity($tenantId, $branchId, $productId, $newQuantity);
            
            if ($result) {
                // Record transaction
                $transaction = new \Modules\Inventory\Models\StockTransaction([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'transaction_type' => $type,
                    'quantity' => $quantity,
                    'unit' => $inventory->unit,
                    'reference_type' => $reference['type'] ?? null,
                    'reference_id' => $reference['id'] ?? null,
                    'notes' => $reference['notes'] ?? null
                ]);
                
                $this->inventoryRepository->recordTransaction($transaction);
                
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'INVENTORY',
                    'action' => 'ADJUST_STOCK',
                    'record_id' => $inventory->inventory_id,
                    'table_name' => 'inventory',
                    'old_values' => json_encode(['quantity' => $oldQuantity]),
                    'new_values' => json_encode(['quantity' => $newQuantity])
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function deleteInventory(int $tenantId, int $inventoryId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldInventory = $this->inventoryRepository->findById($tenantId, $inventoryId);
            
            $result = $this->inventoryRepository->delete($tenantId, $inventoryId);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'INVENTORY',
                    'action' => 'DELETE_INVENTORY',
                    'record_id' => $inventoryId,
                    'table_name' => 'inventory',
                    'old_values' => json_encode($oldInventory->toArray())
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function getTransactions(int $tenantId, ?int $branchId = null, ?int $productId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $transactions = $this->inventoryRepository->getTransactions($tenantId, $branchId, $productId, $dateFrom, $dateTo);
        return array_map(function($t) { return $t->toArray(); }, $transactions);
    }
}
