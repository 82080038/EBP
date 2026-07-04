<?php

namespace App\Modules\Purchase\Services;

use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Models\GoodsReceipt;
use App\Core\Database;

class PurchaseOrderService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get purchase orders
     */
    public function getPurchaseOrders($restaurantId, $status, $supplierId, $dateFrom, $dateTo, $page, $limit)
    {
        $purchaseOrderModel = new PurchaseOrder();
        return $purchaseOrderModel->getPaginated($restaurantId, $status, $supplierId, $dateFrom, $dateTo, $page, $limit);
    }

    /**
     * Get single purchase order
     */
    public function getPurchaseOrder($id, $restaurantId)
    {
        $purchaseOrderModel = new PurchaseOrder();
        $order = $purchaseOrderModel->findById($id, $restaurantId);
        
        if ($order) {
            // Get items
            $order['items'] = $purchaseOrderModel->getItems($id, $restaurantId);
            
            // Get history
            $order['history'] = $purchaseOrderModel->getHistory($id, $restaurantId);
            
            // Get goods receipts
            $receiptModel = new GoodsReceipt();
            $order['receipts'] = $receiptModel->getByPurchaseOrder($id, $restaurantId);
        }
        
        return $order;
    }

    /**
     * Create purchase order
     */
    public function createPurchaseOrder($restaurantId, $userId, $data)
    {
        $purchaseOrderModel = new PurchaseOrder();
        
        $orderData = [
            'restaurant_id' => $restaurantId,
            'supplier_id' => $data->supplier_id,
            'order_type' => $data->order_type ?? 'standard',
            'order_date' => $data->order_date ?? date('Y-m-d'),
            'expected_delivery_date' => $data->expected_delivery_date ?? null,
            'order_status' => 'draft',
            'delivery_address' => $data->delivery_address ?? null,
            'delivery_instructions' => $data->delivery_instructions ?? null,
            'internal_notes' => $data->internal_notes ?? null,
            'supplier_notes' => $data->supplier_notes ?? null,
            'created_by' => $userId
        ];
        
        $orderId = $purchaseOrderModel->create($orderData);
        
        if (!$orderId) {
            return ['success' => false, 'message' => 'Failed to create purchase order'];
        }
        
        // Add items
        if (isset($data->items) && is_array($data->items)) {
            foreach ($data->items as $item) {
                $this->addPurchaseOrderItem($orderId, $restaurantId, $item);
            }
        }
        
        // Recalculate totals
        $this->recalculateOrderTotals($orderId, $restaurantId);
        
        return ['success' => true, 'message' => 'Purchase order created', 'order_id' => $orderId];
    }

    /**
     * Add purchase order item
     */
    private function addPurchaseOrderItem($orderId, $restaurantId, $item)
    {
        $lineTotal = $item->quantity_ordered * $item->unit_price;
        $discountAmount = $lineTotal * ($item->discount_percentage / 100);
        $taxAmount = ($lineTotal - $discountAmount) * ($item->tax_percentage / 100);
        $finalTotal = $lineTotal - $discountAmount + $taxAmount;
        
        $sql = "INSERT INTO purchase_order_items (purchase_order_id, restaurant_id, inventory_item_id, supplier_sku, item_name, quantity_ordered, unit_price, discount_percentage, tax_percentage, line_total, item_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        return $this->db->query($sql, [
            $orderId,
            $restaurantId,
            $item->inventory_item_id,
            $item->supplier_sku ?? null,
            $item->item_name,
            $item->quantity_ordered,
            $item->unit_price,
            $item->discount_percentage ?? 0,
            $item->tax_percentage ?? 0,
            $finalTotal
        ]);
    }

    /**
     * Recalculate order totals
     */
    private function recalculateOrderTotals($orderId, $restaurantId)
    {
        $sql = "SELECT SUM(line_total) as subtotal FROM purchase_order_items WHERE purchase_order_id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$orderId, $restaurantId])->fetch();
        $subtotal = $result['subtotal'] ?? 0;
        
        $sql = "UPDATE purchase_orders SET subtotal = ?, total_amount = ? WHERE id = ? AND restaurant_id = ?";
        return $this->db->query($sql, [$subtotal, $subtotal, $orderId, $restaurantId]);
    }

    /**
     * Update purchase order
     */
    public function updatePurchaseOrder($id, $restaurantId, $userId, $data)
    {
        $purchaseOrderModel = new PurchaseOrder();
        $order = $purchaseOrderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Purchase order not found'];
        }
        
        if ($order['order_status'] !== 'draft') {
            return ['success' => false, 'message' => 'Cannot update non-draft order'];
        }
        
        $updateData = [];
        
        if (isset($data->expected_delivery_date)) {
            $updateData['expected_delivery_date'] = $data->expected_delivery_date;
        }
        if (isset($data->delivery_address)) {
            $updateData['delivery_address'] = $data->delivery_address;
        }
        if (isset($data->internal_notes)) {
            $updateData['internal_notes'] = $data->internal_notes;
        }
        
        $updateData['modified_by'] = $userId;
        
        $updated = $purchaseOrderModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update purchase order'];
        }
        
        return ['success' => true, 'message' => 'Purchase order updated'];
    }

    /**
     * Submit purchase order
     */
    public function submitPurchaseOrder($id, $restaurantId, $userId)
    {
        $purchaseOrderModel = new PurchaseOrder();
        $order = $purchaseOrderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Purchase order not found'];
        }
        
        if ($order['order_status'] !== 'draft') {
            return ['success' => false, 'message' => 'Cannot submit non-draft order'];
        }
        
        $updated = $purchaseOrderModel->update($id, [
            'order_status' => 'submitted',
            'modified_by' => $userId
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to submit purchase order'];
        }
        
        return ['success' => true, 'message' => 'Purchase order submitted'];
    }

    /**
     * Approve purchase order
     */
    public function approvePurchaseOrder($id, $restaurantId, $userId)
    {
        $purchaseOrderModel = new PurchaseOrder();
        $order = $purchaseOrderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Purchase order not found'];
        }
        
        if ($order['order_status'] !== 'submitted') {
            return ['success' => false, 'message' => 'Cannot approve non-submitted order'];
        }
        
        $updated = $purchaseOrderModel->update($id, [
            'order_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
            'modified_by' => $userId
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to approve purchase order'];
        }
        
        return ['success' => true, 'message' => 'Purchase order approved'];
    }

    /**
     * Reject purchase order
     */
    public function rejectPurchaseOrder($id, $restaurantId, $userId, $reason)
    {
        $purchaseOrderModel = new PurchaseOrder();
        $order = $purchaseOrderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Purchase order not found'];
        }
        
        if ($order['order_status'] !== 'submitted') {
            return ['success' => false, 'message' => 'Cannot reject non-submitted order'];
        }
        
        $updated = $purchaseOrderModel->update($id, [
            'order_status' => 'rejected',
            'rejection_reason' => $reason,
            'modified_by' => $userId
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to reject purchase order'];
        }
        
        return ['success' => true, 'message' => 'Purchase order rejected'];
    }

    /**
     * Cancel purchase order
     */
    public function cancelPurchaseOrder($id, $restaurantId, $userId)
    {
        $purchaseOrderModel = new PurchaseOrder();
        $order = $purchaseOrderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Purchase order not found'];
        }
        
        if (in_array($order['order_status'], ['completed', 'cancelled'])) {
            return ['success' => false, 'message' => 'Cannot cancel this order'];
        }
        
        $updated = $purchaseOrderModel->update($id, [
            'order_status' => 'cancelled',
            'modified_by' => $userId
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to cancel purchase order'];
        }
        
        return ['success' => true, 'message' => 'Purchase order cancelled'];
    }

    /**
     * Get goods receipts
     */
    public function getGoodsReceipts($purchaseOrderId, $restaurantId)
    {
        $receiptModel = new GoodsReceipt();
        return $receiptModel->getByPurchaseOrder($purchaseOrderId, $restaurantId);
    }

    /**
     * Create goods receipt
     */
    public function createGoodsReceipt($purchaseOrderId, $restaurantId, $userId, $data)
    {
        $purchaseOrderModel = new PurchaseOrder();
        $order = $purchaseOrderModel->findById($purchaseOrderId, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Purchase order not found'];
        }
        
        $receiptModel = new GoodsReceipt();
        
        $receiptData = [
            'restaurant_id' => $restaurantId,
            'purchase_order_id' => $purchaseOrderId,
            'supplier_id' => $order['supplier_id'],
            'receipt_date' => $data->receipt_date ?? date('Y-m-d'),
            'delivery_note_number' => $data->delivery_note_number ?? null,
            'carrier_name' => $data->carrier_name ?? null,
            'vehicle_number' => $data->vehicle_number ?? null,
            'receiving_notes' => $data->receiving_notes ?? null,
            'internal_notes' => $data->internal_notes ?? null,
            'received_by' => $userId
        ];
        
        $receiptId = $receiptModel->create($receiptData);
        
        if (!$receiptId) {
            return ['success' => false, 'message' => 'Failed to create goods receipt'];
        }
        
        // Add receipt items
        if (isset($data->items) && is_array($data->items)) {
            foreach ($data->items as $item) {
                $this->addGoodsReceiptItem($receiptId, $restaurantId, $item);
            }
        }
        
        return ['success' => true, 'message' => 'Goods receipt created', 'receipt_id' => $receiptId];
    }

    /**
     * Add goods receipt item
     */
    private function addGoodsReceiptItem($receiptId, $restaurantId, $item)
    {
        $sql = "INSERT INTO goods_receipt_items (goods_receipt_id, restaurant_id, purchase_order_item_id, inventory_item_id, quantity_received, quantity_rejected, quality_status, rejection_reason, batch_number, expiry_date, lot_number, item_notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $receiptId,
            $restaurantId,
            $item->purchase_order_item_id,
            $item->inventory_item_id,
            $item->quantity_received,
            $item->quantity_rejected ?? 0,
            $item->quality_status ?? 'accepted',
            $item->rejection_reason ?? null,
            $item->batch_number ?? null,
            $item->expiry_date ?? null,
            $item->lot_number ?? null,
            $item->item_notes ?? null
        ]);
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId)
    {
        $purchaseOrderModel = new PurchaseOrder();
        
        // Total orders
        $totalOrders = $purchaseOrderModel->countByRestaurant($restaurantId);
        
        // Pending orders
        $pendingOrders = $purchaseOrderModel->countByStatus($restaurantId, 'submitted');
        
        // Approved orders
        $approvedOrders = $purchaseOrderModel->countByStatus($restaurantId, 'approved');
        
        // Total value
        $totalValue = $purchaseOrderModel->getTotalValue($restaurantId);
        
        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'approved_orders' => $approvedOrders,
            'total_value' => $totalValue
        ];
    }
}
