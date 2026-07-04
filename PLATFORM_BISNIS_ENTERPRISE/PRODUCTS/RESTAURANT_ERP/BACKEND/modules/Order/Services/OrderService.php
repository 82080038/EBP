<?php

namespace App\Modules\Order\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Models\OrderModifier;
use App\Modules\Order\Models\KitchenOrder;
use App\Modules\Order\Models\TableSession;
use App\Core\Database;

class OrderService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get orders
     */
    public function getOrders($restaurantId, $status, $tableId, $dateFrom, $dateTo, $page, $limit)
    {
        $orderModel = new Order();
        return $orderModel->getPaginated($restaurantId, $status, $tableId, $dateFrom, $dateTo, $page, $limit);
    }

    /**
     * Get single order
     */
    public function getOrder($id, $restaurantId)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($id, $restaurantId);
        
        if ($order) {
            // Get order items
            $itemModel = new OrderItem();
            $order['items'] = $itemModel->getByOrderId($id);
            
            // Get order status history
            $history = $this->getOrderStatusHistory($id);
            $order['status_history'] = $history;
        }
        
        return $order;
    }

    /**
     * Create order
     */
    public function createOrder($restaurantId, $userId, $data)
    {
        $orderModel = new Order();
        
        $orderData = [
            'restaurant_id' => $restaurantId,
            'table_id' => $data->table_id ?? null,
            'order_type' => $data->order_type,
            'order_status' => 'pending',
            'order_channel' => $data->order_channel ?? 'pos',
            'customer_id' => $data->customer_id ?? null,
            'customer_name' => $data->customer_name ?? null,
            'customer_phone' => $data->customer_phone ?? null,
            'customer_email' => $data->customer_email ?? null,
            'customer_address' => $data->customer_address ?? null,
            'order_date' => date('Y-m-d H:i:s'),
            'estimated_time' => $data->estimated_time ?? null,
            'subtotal' => 0.00,
            'tax_amount' => 0.00,
            'service_charge' => 0.00,
            'discount_amount' => 0.00,
            'delivery_fee' => $data->delivery_fee ?? 0.00,
            'total_amount' => 0.00,
            'paid_amount' => 0.00,
            'payment_status' => 'unpaid',
            'created_by' => $userId,
            'special_instructions' => $data->special_instructions ?? null,
            'external_order_id' => $data->external_order_id ?? null,
            'external_source' => $data->external_source ?? null
        ];
        
        $orderId = $orderModel->create($orderData);
        
        if (!$orderId) {
            return ['success' => false, 'message' => 'Failed to create order'];
        }
        
        // Add order items
        if (isset($data->items) && is_array($data->items)) {
            foreach ($data->items as $item) {
                $this->addOrderItem($orderId, $restaurantId, $item);
            }
        }
        
        // Recalculate order totals
        $this->recalculateOrderTotals($orderId);
        
        // Create table session if dine-in
        if ($data->order_type === 'dine_in' && isset($data->table_id)) {
            $this->ensureTableSession($restaurantId, $userId, $data->table_id, $orderId);
        }
        
        // Send to kitchen if applicable
        if ($data->order_type !== 'online') {
            $this->sendToKitchen($orderId);
        }
        
        return ['success' => true, 'message' => 'Order created successfully', 'order_id' => $orderId];
    }

    /**
     * Update order
     */
    public function updateOrder($id, $restaurantId, $data)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        $updateData = [];
        
        if (isset($data->customer_name)) {
            $updateData['customer_name'] = $data->customer_name;
        }
        if (isset($data->customer_phone)) {
            $updateData['customer_phone'] = $data->customer_phone;
        }
        if (isset($data->special_instructions)) {
            $updateData['special_instructions'] = $data->special_instructions;
        }
        if (isset($data->estimated_time)) {
            $updateData['estimated_time'] = $data->estimated_time;
        }
        if (isset($data->table_id)) {
            $updateData['table_id'] = $data->table_id;
        }
        
        $updated = $orderModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update order'];
        }
        
        return ['success' => true, 'message' => 'Order updated successfully'];
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($id, $restaurantId, $userId, $newStatus, $notes = null)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        $oldStatus = $order['order_status'];
        
        // Update order status
        $updateData = [
            'order_status' => $newStatus
        ];
        
        // Set timestamps based on status
        switch ($newStatus) {
            case 'confirmed':
                $updateData['confirmed_at'] = date('Y-m-d H:i:s');
                $updateData['confirmed_by'] = $userId;
                break;
            case 'preparing':
                $updateData['started_at'] = date('Y-m-d H:i:s');
                break;
            case 'ready':
                $updateData['ready_at'] = date('Y-m-d H:i:s');
                break;
            case 'served':
                $updateData['served_at'] = date('Y-m-d H:i:s');
                $updateData['served_by'] = $userId;
                break;
            case 'completed':
                $updateData['completed_at'] = date('Y-m-d H:i:s');
                break;
            case 'cancelled':
                $updateData['cancelled_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        $updated = $orderModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update order status'];
        }
        
        // Log status change
        $this->logOrderStatusChange($id, $oldStatus, $newStatus, $userId, $notes);
        
        return ['success' => true, 'message' => 'Order status updated successfully'];
    }

    /**
     * Cancel order
     */
    public function cancelOrder($id, $restaurantId, $userId, $reason = null)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        if ($order['order_status'] === 'completed' || $order['order_status'] === 'cancelled') {
            return ['success' => false, 'message' => 'Cannot cancel this order'];
        }
        
        $updated = $orderModel->update($id, [
            'order_status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => $reason
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to cancel order'];
        }
        
        // Log status change
        $this->logOrderStatusChange($id, $order['order_status'], 'cancelled', $userId, $reason);
        
        return ['success' => true, 'message' => 'Order cancelled successfully'];
    }

    /**
     * Add order item
     */
    public function addOrderItem($orderId, $restaurantId, $data)
    {
        $itemModel = new OrderItem();
        
        $itemData = [
            'order_id' => $orderId,
            'menu_item_id' => $data->menu_item_id,
            'item_name' => $data->item_name,
            'quantity' => $data->quantity,
            'unit_price' => $data->unit_price,
            'subtotal' => $data->quantity * $data->unit_price,
            'modifiers' => json_encode($data->modifiers ?? []),
            'special_instructions' => $data->special_instructions ?? null,
            'preparation_station' => $data->preparation_station ?? 'kitchen'
        ];
        
        $itemId = $itemModel->create($itemData);
        
        if (!$itemId) {
            return ['success' => false, 'message' => 'Failed to add item'];
        }
        
        // Add modifiers if any
        if (isset($data->modifiers) && is_array($data->modifiers)) {
            foreach ($data->modifiers as $modifier) {
                $this->addOrderModifier($itemId, $modifier);
            }
        }
        
        // Recalculate order totals
        $this->recalculateOrderTotals($orderId);
        
        return ['success' => true, 'message' => 'Item added successfully', 'item_id' => $itemId];
    }

    /**
     * Update order item
     */
    public function updateOrderItem($itemId, $restaurantId, $data)
    {
        $itemModel = new OrderItem();
        $item = $itemModel->findById($itemId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $updateData = [];
        
        if (isset($data->quantity)) {
            $updateData['quantity'] = $data->quantity;
        }
        if (isset($data->special_instructions)) {
            $updateData['special_instructions'] = $data->special_instructions;
        }
        
        if (!empty($updateData)) {
            $itemModel->update($itemId, $updateData);
        }
        
        // Recalculate item subtotal
        if (isset($data->quantity)) {
            $itemModel->update($itemId, [
                'subtotal' => $data->quantity * $item['unit_price']
            ]);
        }
        
        // Recalculate order totals
        $this->recalculateOrderTotals($item['order_id']);
        
        return ['success' => true, 'message' => 'Item updated successfully'];
    }

    /**
     * Remove order item
     */
    public function removeOrderItem($itemId, $restaurantId)
    {
        $itemModel = new OrderItem();
        $item = $itemModel->findById($itemId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $orderId = $item['order_id'];
        $deleted = $itemModel->delete($itemId);
        
        if (!$deleted) {
            return ['success' => false, 'message' => 'Failed to remove item'];
        }
        
        // Recalculate order totals
        $this->recalculateOrderTotals($orderId);
        
        return ['success' => true, 'message' => 'Item removed successfully'];
    }

    /**
     * Add order modifier
     */
    private function addOrderModifier($orderItemId, $data)
    {
        $modifierModel = new OrderModifier();
        
        $modifierData = [
            'order_item_id' => $orderItemId,
            'modifier_id' => $data->modifier_id,
            'modifier_name' => $data->modifier_name,
            'modifier_type' => $data->modifier_type,
            'price_adjustment' => $data->price_adjustment ?? 0.00
        ];
        
        return $modifierModel->create($modifierData);
    }

    /**
     * Recalculate order totals
     */
    private function recalculateOrderTotals($orderId)
    {
        $itemModel = new OrderItem();
        $orderModel = new Order();
        
        // Get all items for the order
        $items = $itemModel->getByOrderId($orderId);
        
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['subtotal'];
        }
        
        // Calculate tax (assuming 10% for now)
        $taxAmount = $subtotal * 0.10;
        
        // Calculate service charge (assuming 5% for dine-in)
        $order = $orderModel->findById($orderId);
        $serviceCharge = ($order['order_type'] === 'dine_in') ? $subtotal * 0.05 : 0;
        
        $totalAmount = $subtotal + $taxAmount + $serviceCharge + $order['delivery_fee'] - $order['discount_amount'];
        
        // Update order
        $orderModel->update($orderId, [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'service_charge' => $serviceCharge,
            'total_amount' => $totalAmount
        ]);
    }

    /**
     * Send to kitchen
     */
    private function sendToKitchen($orderId)
    {
        $itemModel = new OrderItem();
        $kitchenModel = new KitchenOrder();
        
        $items = $itemModel->getByOrderId($orderId);
        
        foreach ($items as $item) {
            $kitchenModel->create([
                'order_id' => $orderId,
                'order_item_id' => $item['id'],
                'station' => $item['preparation_station'],
                'display_order' => 0,
                'status' => 'pending',
                'sent_to_kitchen_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Get kitchen orders
     */
    public function getKitchenOrders($restaurantId, $station, $status)
    {
        $kitchenModel = new KitchenOrder();
        return $kitchenModel->getByRestaurant($restaurantId, $station, $status);
    }

    /**
     * Update kitchen order status
     */
    public function updateKitchenOrderStatus($id, $restaurantId, $userId, $newStatus)
    {
        $kitchenModel = new KitchenOrder();
        $kitchenOrder = $kitchenModel->findById($id);
        
        if (!$kitchenOrder) {
            return ['success' => false, 'message' => 'Kitchen order not found'];
        }
        
        $updateData = ['status' => $newStatus];
        
        switch ($newStatus) {
            case 'in_progress':
                $updateData['started_at'] = date('Y-m-d H:i:s');
                break;
            case 'ready':
                $updateData['ready_at'] = date('Y-m-d H:i:s');
                break;
            case 'served':
                $updateData['served_at'] = date('Y-m-d H:i:s');
                $updateData['prepared_by'] = $userId;
                break;
        }
        
        $updated = $kitchenModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update kitchen order'];
        }
        
        // Update order item status
        $itemModel = new OrderItem();
        $itemModel->update($kitchenOrder['order_item_id'], [
            'preparation_status' => $newStatus
        ]);
        
        return ['success' => true, 'message' => 'Kitchen order updated successfully'];
    }

    /**
     * Get table sessions
     */
    public function getTableSessions($restaurantId, $tableId, $status)
    {
        $sessionModel = new TableSession();
        return $sessionModel->getByRestaurant($restaurantId, $tableId, $status);
    }

    /**
     * Create table session
     */
    public function createTableSession($restaurantId, $userId, $data)
    {
        $sessionModel = new TableSession();
        
        $sessionData = [
            'restaurant_id' => $restaurantId,
            'table_id' => $data->table_id,
            'session_number' => $this->generateSessionNumber($restaurantId, $data->table_id),
            'session_status' => 'active',
            'started_at' => date('Y-m-d H:i:s'),
            'customer_count' => $data->customer_count ?? 0,
            'customer_id' => $data->customer_id ?? null,
            'server_id' => $userId,
            'notes' => $data->notes ?? null
        ];
        
        $sessionId = $sessionModel->create($sessionData);
        
        if (!$sessionId) {
            return ['success' => false, 'message' => 'Failed to create table session'];
        }
        
        return ['success' => true, 'message' => 'Table session created', 'session_id' => $sessionId];
    }

    /**
     * Generate session number
     */
    private function generateSessionNumber($restaurantId, $tableId)
    {
        $sql = "SELECT COUNT(*) as count FROM table_sessions 
                WHERE restaurant_id = ? AND table_id = ? AND DATE(started_at) = CURDATE()";
        $result = $this->db->query($sql, [$restaurantId, $tableId])->fetch();
        $count = $result['count'] ?? 0;
        
        return 'SES' . date('Ymd') . '-' . $tableId . '-' . ($count + 1);
    }

    /**
     * Ensure table session exists
     */
    private function ensureTableSession($restaurantId, $userId, $tableId, $orderId)
    {
        $sessionModel = new TableSession();
        
        // Check if active session exists
        $activeSession = $sessionModel->getActiveByTable($restaurantId, $tableId);
        
        if (!$activeSession) {
            // Create new session
            $this->createTableSession($restaurantId, $userId, [
                'table_id' => $tableId,
                'customer_count' => 0
            ]);
        }
    }

    /**
     * Close table session
     */
    public function closeTableSession($id, $restaurantId)
    {
        $sessionModel = new TableSession();
        $session = $sessionModel->findById($id, $restaurantId);
        
        if (!$session) {
            return ['success' => false, 'message' => 'Session not found'];
        }
        
        $endedAt = date('Y-m-d H:i:s');
        $duration = strtotime($endedAt) - strtotime($session['started_at']);
        $durationMinutes = round($duration / 60);
        
        $updated = $sessionModel->update($id, [
            'session_status' => 'completed',
            'ended_at' => $endedAt,
            'duration_minutes' => $durationMinutes
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to close session'];
        }
        
        return ['success' => true, 'message' => 'Session closed successfully'];
    }

    /**
     * Get order status history
     */
    private function getOrderStatusHistory($orderId)
    {
        $sql = "SELECT * FROM order_status_history WHERE order_id = ? ORDER BY changed_at ASC";
        return $this->db->query($sql, [$orderId])->fetchAll();
    }

    /**
     * Log order status change
     */
    private function logOrderStatusChange($orderId, $oldStatus, $newStatus, $userId, $notes = null)
    {
        $sql = "INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, changed_at, notes)
                VALUES (?, ?, ?, ?, NOW(), ?)";
        
        $this->db->query($sql, [$orderId, $oldStatus, $newStatus, $userId, $notes]);
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId, $dateFrom, $dateTo)
    {
        $orderModel = new Order();
        
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND order_date <= ?";
            $params[] = $dateTo;
        }
        
        // Total orders
        $sql = "SELECT COUNT(*) as total FROM orders {$where}";
        $totalOrders = $this->db->query($sql, $params)->fetch()['total'] ?? 0;
        
        // Total revenue
        $sql = "SELECT SUM(total_amount) as revenue FROM orders {$where} WHERE order_status != 'cancelled'";
        $totalRevenue = $this->db->query($sql, $params)->fetch()['revenue'] ?? 0;
        
        // Orders by status
        $sql = "SELECT order_status, COUNT(*) as count FROM orders {$where} GROUP BY order_status";
        $ordersByStatus = $this->db->query($sql, $params)->fetchAll();
        
        // Average order value
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $avgOrderValue,
            'orders_by_status' => $ordersByStatus
        ];
    }
}
