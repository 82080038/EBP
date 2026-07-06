<?php

if (!class_exists('GoodsReceiptRepository')) {
    require_once __DIR__ . '/../Repositories/GoodsReceiptRepository.php';
}


class GoodsReceiptService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new GoodsReceiptRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createGoodsReceipt($data, $userId, $tenantId, $branchId)
    {
        try {
            if (empty($data['supplier_id']) || empty($data['receipt_date']) || empty($data['items'])) {
                return [
                    'success' => false,
                    'message' => 'Supplier, receipt date, and items are required'
                ];
            }

            $date = date('Ymd', strtotime($data['receipt_date']));
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM goods_receipt WHERE tenant_id = ? AND receipt_number LIKE ?");
            $stmt->execute([$tenantId, "GR-$date-%"]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
            $receiptNumber = "GR-$date-$sequence";

            $receiptData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'receipt_number' => $receiptNumber,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'supplier_id' => $data['supplier_id'],
                'receipt_date' => $data['receipt_date'],
                'status' => 'DRAFT',
                'notes' => $data['notes'] ?? null,
                'received_by' => $userId
            ];

            $receiptId = $this->repository->create($receiptData);

            // Add items and update inventory
            foreach ($data['items'] as $item) {
                $itemData = [
                    'receipt_id' => $receiptId,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'manufacturing_date' => $item['manufacturing_date'] ?? null,
                    'total_cost' => $item['quantity'] * $item['unit_cost']
                ];

                $this->repository->createItem($itemData);
                
                // Update inventory quantity and cost
                $this->updateInventory($item['inventory_id'], $item['quantity'], $item['unit_cost']);
            }

            return [
                'success' => true,
                'message' => 'Goods receipt created successfully',
                'receipt_id' => $receiptId,
                'receipt_number' => $receiptNumber
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create goods receipt: ' . $e->getMessage()
            ];
        }
    }

    private function updateInventory($inventoryId, $quantity, $unitCost)
    {
        // Get current inventory data
        $stmt = $this->db->prepare("SELECT quantity, average_cost FROM inventory WHERE inventory_id = ?");
        $stmt->execute([$inventoryId]);
        $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($inventory) {
            $newQuantity = $inventory['quantity'] + $quantity;
            
            // Calculate new average cost
            $currentTotalValue = $inventory['quantity'] * $inventory['average_cost'];
            $newTotalValue = $currentTotalValue + ($quantity * $unitCost);
            $newAverageCost = $newQuantity > 0 ? $newTotalValue / $newQuantity : $unitCost;

            $sql = "UPDATE inventory SET quantity = ?, average_cost = ?, last_purchase_date = CURDATE() WHERE inventory_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newQuantity, $newAverageCost, $inventoryId]);
        }
    }

    public function completeGoodsReceipt($receiptId, $tenantId)
    {
        try {
            $stmt = $this->db->prepare("SELECT receipt_id, status FROM goods_receipt WHERE receipt_id = ? AND tenant_id = ?");
            $stmt->execute([$receiptId, $tenantId]);
            $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$receipt) {
                return [
                    'success' => false,
                    'message' => 'Goods receipt not found'
                ];
            }

            if ($receipt['status'] !== 'DRAFT') {
                return [
                    'success' => false,
                    'message' => 'Only draft receipt can be completed'
                ];
            }

            $this->repository->update($receiptId, ['status' => 'COMPLETED']);

            // Update PO status if linked
            $stmt = $this->db->prepare("SELECT purchase_order_id FROM goods_receipt WHERE receipt_id = ?");
            $stmt->execute([$receiptId]);
            $gr = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($gr && $gr['purchase_order_id']) {
                $this->updatePOStatus($gr['purchase_order_id']);
            }

            return [
                'success' => true,
                'message' => 'Goods receipt completed successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to complete receipt: ' . $e->getMessage()
            ];
        }
    }

    private function updatePOStatus($poId)
    {
        $sql = "UPDATE purchase_orders SET status = 'RECEIVED' WHERE po_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$poId]);
    }

    public function getGoodsReceipts($tenantId, $branchId = null)
    {
        try {
            $receipts = $this->repository->getByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Goods receipts retrieved successfully',
                'data' => $receipts
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get receipts: ' . $e->getMessage()
            ];
        }
    }
}
