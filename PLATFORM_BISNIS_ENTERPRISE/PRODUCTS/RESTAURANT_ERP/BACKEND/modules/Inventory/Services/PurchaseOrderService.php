<?php

if (!class_exists('PurchaseOrderRepository')) {
    require_once __DIR__ . '/../Repositories/PurchaseOrderRepository.php';
}


class PurchaseOrderService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new PurchaseOrderRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createPurchaseOrder($data, $userId, $tenantId, $branchId)
    {
        try {
            if (empty($data['supplier_id']) || empty($data['order_date']) || empty($data['items'])) {
                return [
                    'success' => false,
                    'message' => 'Supplier, order date, and items are required'
                ];
            }

            $date = date('Ymd', strtotime($data['order_date']));
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM purchase_orders WHERE tenant_id = ? AND po_number LIKE ?");
            $stmt->execute([$tenantId, "PO-$date-%"]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
            $poNumber = "PO-$date-$sequence";

            $poData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'po_number' => $poNumber,
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => 'DRAFT',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId
            ];

            $poId = $this->repository->create($poData);

            // Add items
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $discountAmount = $itemSubtotal * ($item['discount_percentage'] ?? 0) / 100;
                $taxAmount = ($itemSubtotal - $discountAmount) * ($item['tax_percentage'] ?? 0) / 100;
                $finalSubtotal = $itemSubtotal - $discountAmount + $taxAmount;

                $itemData = [
                    'purchase_order_id' => $poId,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                    'discount_amount' => $discountAmount,
                    'tax_percentage' => $item['tax_percentage'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $finalSubtotal
                ];

                $this->repository->createItem($itemData);
                $subtotal += $finalSubtotal;
            }

            // Update PO totals
            $this->repository->update($poId, [
                'subtotal' => $subtotal,
                'total_amount' => $subtotal
            ]);

            return [
                'success' => true,
                'message' => 'Purchase order created successfully',
                'po_id' => $poId,
                'po_number' => $poNumber
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create PO: ' . $e->getMessage()
            ];
        }
    }

    public function approvePurchaseOrder($poId, $userId, $tenantId)
    {
        try {
            $stmt = $this->db->prepare("SELECT po_id, status FROM purchase_orders WHERE po_id = ? AND tenant_id = ?");
            $stmt->execute([$poId, $tenantId]);
            $po = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$po) {
                return [
                    'success' => false,
                    'message' => 'Purchase order not found'
                ];
            }

            if ($po['status'] !== 'DRAFT' && $po['status'] !== 'PENDING') {
                return [
                    'success' => false,
                    'message' => 'Only draft or pending PO can be approved'
                ];
            }

            $this->repository->update($poId, [
                'status' => 'APPROVED',
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Purchase order approved successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve PO: ' . $e->getMessage()
            ];
        }
    }

    public function getPurchaseOrders($tenantId, $branchId = null)
    {
        try {
            $pos = $this->repository->getByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Purchase orders retrieved successfully',
                'data' => $pos
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get POs: ' . $e->getMessage()
            ];
        }
    }
}
