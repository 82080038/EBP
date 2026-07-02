<?php

if (!class_exists('KitchenRepository')) {
    require_once __DIR__ . '/../Repositories/KitchenRepository.php';
}
if (!class_exists('Transaction')) {
    require_once __DIR__ . '/../../../core/Transaction.php';
}
if (!class_exists('Audit')) {
    require_once __DIR__ . '/../../../core/Audit.php';
}

class KitchenService
{
    private $kitchenRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->kitchenRepository = new KitchenRepository();
        $this->transaction = new Transaction();
        $this->audit = new Audit();
    }

    public function getAllKitchenOrders(int $tenantId, ?int $branchId = null): array
    {
        $kitchenOrders = $this->kitchenRepository->findAll($tenantId, $branchId);
        
        $result = [];
        foreach ($kitchenOrders as $ko) {
            $data = $ko->toArray();
            $data['items'] = array_map(function($item) { return $item->toArray(); }, $this->kitchenRepository->getItems($ko->kitchen_order_id));
            $result[] = $data;
        }
        
        return $result;
    }

    public function getKitchenOrdersByStatus(int $tenantId, ?int $branchId = null, string $status = 'PENDING'): array
    {
        $kitchenOrders = $this->kitchenRepository->findByStatus($tenantId, $branchId, $status);
        
        $result = [];
        foreach ($kitchenOrders as $ko) {
            $data = $ko->toArray();
            $data['items'] = array_map(function($item) { return $item->toArray(); }, $this->kitchenRepository->getItems($ko->kitchen_order_id));
            $result[] = $data;
        }
        
        return $result;
    }

    public function getKitchenOrder(int $tenantId, int $kitchenOrderId): ?array
    {
        $kitchenOrder = $this->kitchenRepository->findById($tenantId, $kitchenOrderId);
        
        if ($kitchenOrder) {
            $data = $kitchenOrder->toArray();
            $data['items'] = array_map(function($item) { return $item->toArray(); }, $this->kitchenRepository->getItems($kitchenOrderId));
            return $data;
        }
        
        return null;
    }

    public function createKitchenOrder(int $tenantId, int $orderId, array $items): bool
    {
        $this->transaction->begin();
        
        try {
            // Get order details to determine branch
            $stmt = $this->transaction->getConnection()->prepare("
                SELECT branch_id FROM orders WHERE order_id = :order_id AND tenant_id = :tenant_id
            ");
            $stmt->execute(['order_id' => $orderId, 'tenant_id' => $tenantId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                $this->transaction->rollback();
                return false;
            }
            
            $branchId = $order['branch_id'];
            
            // Generate kitchen order number
            $kitchenOrderNumber = $this->kitchenRepository->generateKitchenOrderNumber($tenantId, $branchId);
            
            // Create kitchen order
            $kitchenOrder = new \Modules\Kitchen\Models\KitchenOrder([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'order_id' => $orderId,
                'kitchen_order_number' => $kitchenOrderNumber,
                'status' => 'PENDING',
                'priority' => 'NORMAL'
            ]);
            
            $result = $this->kitchenRepository->create($kitchenOrder);
            
            if ($result) {
                $kitchenOrderId = $this->transaction->getLastInsertId();
                
                // Create kitchen order items
                foreach ($items as $item) {
                    $kitchenOrderItem = new \Modules\Kitchen\Models\KitchenOrderItem([
                        'kitchen_order_id' => $kitchenOrderId,
                        'order_item_id' => $item['order_item_id'],
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'notes' => $item['notes'] ?? null,
                        'status' => 'PENDING'
                    ]);
                    
                    $this->kitchenRepository->createItem($kitchenOrderItem);
                }
                
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'KITCHEN',
                    'action' => 'CREATE_KITCHEN_ORDER',
                    'record_id' => $kitchenOrderId,
                    'table_name' => 'kitchen_orders',
                    'new_values' => json_encode(['order_id' => $orderId, 'items' => $items])
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

    public function updateKitchenOrderStatus(int $tenantId, int $kitchenOrderId, string $status): bool
    {
        $this->transaction->begin();
        
        try {
            $oldKitchenOrder = $this->kitchenRepository->findById($tenantId, $kitchenOrderId);
            
            $result = $this->kitchenRepository->updateStatus($tenantId, $kitchenOrderId, $status);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'KITCHEN',
                    'action' => 'UPDATE_KITCHEN_ORDER_STATUS',
                    'record_id' => $kitchenOrderId,
                    'table_name' => 'kitchen_orders',
                    'old_values' => json_encode(['status' => $oldKitchenOrder->status]),
                    'new_values' => json_encode(['status' => $status])
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

    public function updateKitchenOrderPriority(int $tenantId, int $kitchenOrderId, string $priority): bool
    {
        $this->transaction->begin();
        
        try {
            $oldKitchenOrder = $this->kitchenRepository->findById($tenantId, $kitchenOrderId);
            
            $result = $this->kitchenRepository->updatePriority($tenantId, $kitchenOrderId, $priority);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'KITCHEN',
                    'action' => 'UPDATE_KITCHEN_ORDER_PRIORITY',
                    'record_id' => $kitchenOrderId,
                    'table_name' => 'kitchen_orders',
                    'old_values' => json_encode(['priority' => $oldKitchenOrder->priority]),
                    'new_values' => json_encode(['priority' => $priority])
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

    public function updateKitchenItemStatus(int $kitchenOrderItemId, string $status): bool
    {
        $this->transaction->begin();
        
        try {
            $result = $this->kitchenRepository->updateItemStatus($kitchenOrderItemId, $status);
            
            if ($result) {
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
}
