<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockTransfer;
use App\Modules\Inventory\Models\WasteLog;
use App\Modules\Inventory\Models\StockAlert;
use App\Core\Database;

class InventoryManagementService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get inventory items
     */
    public function getItems($restaurantId, $categoryId, $lowStock, $page, $limit)
    {
        $itemModel = new InventoryItem();
        return $itemModel->getPaginated($restaurantId, $categoryId, $lowStock, $page, $limit);
    }

    /**
     * Get single inventory item
     */
    public function getItem($id, $restaurantId)
    {
        $itemModel = new InventoryItem();
        $item = $itemModel->findById($id, $restaurantId);
        
        if ($item) {
            // Get recent stock movements
            $movementModel = new StockMovement();
            $item['recent_movements'] = $movementModel->getByItemId($id, 10);
        }
        
        return $item;
    }

    /**
     * Create inventory item
     */
    public function createItem($restaurantId, $userId, $data)
    {
        $itemModel = new InventoryItem();
        
        $itemData = [
            'restaurant_id' => $restaurantId,
            'item_code' => $data->item_code,
            'item_name' => $data->item_name,
            'item_description' => $data->item_description ?? null,
            'category_id' => $data->category_id ?? null,
            'unit_id' => $data->unit_id,
            'current_stock' => $data->current_stock ?? 0,
            'minimum_stock' => $data->minimum_stock ?? 0,
            'maximum_stock' => $data->maximum_stock ?? 0,
            'reorder_point' => $data->reorder_point ?? 0,
            'reorder_quantity' => $data->reorder_quantity ?? 0,
            'cost_per_unit' => $data->cost_per_unit,
            'average_cost' => $data->cost_per_unit,
            'last_purchase_price' => $data->cost_per_unit,
            'supplier_id' => $data->supplier_id ?? null,
            'supplier_item_code' => $data->supplier_item_code ?? null,
            'is_active' => true,
            'is_perishable' => $data->is_perishable ?? false,
            'shelf_life_days' => $data->shelf_life_days ?? null
        ];
        
        $itemId = $itemModel->create($itemData);
        
        if (!$itemId) {
            return ['success' => false, 'message' => 'Failed to create inventory item'];
        }
        
        // Log initial stock if provided
        if (isset($data->current_stock) && $data->current_stock > 0) {
            $this->logStockMovement($restaurantId, $itemId, 'adjustment', 'in', $data->current_stock, 0, $data->current_stock, $userId, 'Initial stock');
        }
        
        return ['success' => true, 'message' => 'Inventory item created', 'item_id' => $itemId];
    }

    /**
     * Update inventory item
     */
    public function updateItem($id, $restaurantId, $data)
    {
        $itemModel = new InventoryItem();
        $item = $itemModel->findById($id, $restaurantId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $updateData = [];
        
        if (isset($data->item_name)) {
            $updateData['item_name'] = $data->item_name;
        }
        if (isset($data->item_description)) {
            $updateData['item_description'] = $data->item_description;
        }
        if (isset($data->category_id)) {
            $updateData['category_id'] = $data->category_id;
        }
        if (isset($data->minimum_stock)) {
            $updateData['minimum_stock'] = $data->minimum_stock;
        }
        if (isset($data->maximum_stock)) {
            $updateData['maximum_stock'] = $data->maximum_stock;
        }
        if (isset($data->reorder_point)) {
            $updateData['reorder_point'] = $data->reorder_point;
        }
        if (isset($data->reorder_quantity)) {
            $updateData['reorder_quantity'] = $data->reorder_quantity;
        }
        if (isset($data->cost_per_unit)) {
            $updateData['cost_per_unit'] = $data->cost_per_unit;
            $updateData['last_purchase_price'] = $data->cost_per_unit;
        }
        if (isset($data->supplier_id)) {
            $updateData['supplier_id'] = $data->supplier_id;
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        
        $updated = $itemModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update item'];
        }
        
        return ['success' => true, 'message' => 'Item updated successfully'];
    }

    /**
     * Adjust stock
     */
    public function adjustStock($restaurantId, $userId, $data)
    {
        $itemModel = new InventoryItem();
        $item = $itemModel->findById($data->item_id, $restaurantId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $quantity = $data->quantity;
        $direction = $data->direction; // 'in' or 'out'
        $movementType = $data->movement_type; // 'purchase', 'sale', 'adjustment', 'waste', etc.
        
        $stockBefore = $item['current_stock'];
        
        if ($direction === 'out' && $stockBefore < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }
        
        $stockAfter = $direction === 'in' ? $stockBefore + $quantity : $stockBefore - $quantity;
        
        // Update item stock
        $itemModel->update($data->item_id, ['current_stock' => $stockAfter]);
        
        // Log stock movement
        $this->logStockMovement($restaurantId, $data->item_id, $movementType, $direction, $quantity, $stockBefore, $stockAfter, $userId, $data->notes ?? null);
        
        // Check for alerts
        $this->checkStockAlerts($restaurantId, $data->item_id, $stockAfter);
        
        return ['success' => true, 'message' => 'Stock adjusted successfully', 'new_stock' => $stockAfter];
    }

    /**
     * Log stock movement
     */
    private function logStockMovement($restaurantId, $itemId, $movementType, $direction, $quantity, $stockBefore, $stockAfter, $userId, $notes = null)
    {
        $movementModel = new StockMovement();
        
        $movementData = [
            'restaurant_id' => $restaurantId,
            'inventory_item_id' => $itemId,
            'movement_type' => $movementType,
            'movement_direction' => $direction,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => null,
            'reference_id' => null,
            'reference_number' => null,
            'unit_cost' => null,
            'total_cost' => null,
            'performed_by' => $userId,
            'performed_at' => date('Y-m-d H:i:s'),
            'notes' => $notes
        ];
        
        $movementModel->create($movementData);
    }

    /**
     * Check stock alerts
     */
    private function checkStockAlerts($restaurantId, $itemId, $currentStock)
    {
        $itemModel = new InventoryItem();
        $item = $itemModel->findById($itemId, $restaurantId);
        
        if (!$item) {
            return;
        }
        
        $alertModel = new StockAlert();
        
        // Check for low stock
        if ($currentStock <= $item['minimum_stock']) {
            $alertModel->create([
                'restaurant_id' => $restaurantId,
                'inventory_item_id' => $itemId,
                'alert_type' => 'low_stock',
                'alert_severity' => $currentStock === 0 ? 'critical' : 'high',
                'alert_message' => "Low stock alert: {$item['item_name']} (Current: {$currentStock}, Minimum: {$item['minimum_stock']})",
                'current_stock' => $currentStock,
                'threshold_value' => $item['minimum_stock']
            ]);
        }
        
        // Check for out of stock
        if ($currentStock === 0) {
            $alertModel->create([
                'restaurant_id' => $restaurantId,
                'inventory_item_id' => $itemId,
                'alert_type' => 'out_of_stock',
                'alert_severity' => 'critical',
                'alert_message' => "Out of stock: {$item['item_name']}",
                'current_stock' => $currentStock,
                'threshold_value' => 0
            ]);
        }
    }

    /**
     * Get stock movements
     */
    public function getStockMovements($restaurantId, $itemId, $movementType, $dateFrom, $dateTo, $page, $limit)
    {
        $movementModel = new StockMovement();
        return $movementModel->getPaginated($restaurantId, $itemId, $movementType, $dateFrom, $dateTo, $page, $limit);
    }

    /**
     * Create stock transfer
     */
    public function createStockTransfer($restaurantId, $userId, $data)
    {
        $transferModel = new StockTransfer();
        
        $transferData = [
            'restaurant_id' => $restaurantId,
            'transfer_type' => $data->transfer_type,
            'from_location_id' => $data->from_location_id ?? null,
            'to_location_id' => $data->to_location_id,
            'from_branch_id' => $data->from_branch_id ?? null,
            'to_branch_id' => $data->to_branch_id ?? null,
            'transfer_status' => 'pending',
            'transfer_date' => date('Y-m-d H:i:s'),
            'created_by' => $userId,
            'notes' => $data->notes ?? null
        ];
        
        $transferId = $transferModel->create($transferData);
        
        if (!$transferId) {
            return ['success' => false, 'message' => 'Failed to create transfer'];
        }
        
        // Add transfer items
        if (isset($data->items) && is_array($data->items)) {
            foreach ($data->items as $item) {
                $this->addTransferItem($transferId, $item);
            }
        }
        
        return ['success' => true, 'message' => 'Transfer created', 'transfer_id' => $transferId];
    }

    /**
     * Add transfer item
     */
    private function addTransferItem($transferId, $data)
    {
        $sql = "INSERT INTO stock_transfer_items (stock_transfer_id, inventory_item_id, quantity, unit_cost, total_cost, notes)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $totalCost = ($data->unit_cost ?? 0) * $data->quantity;
        
        $this->db->query($sql, [
            $transferId,
            $data->inventory_item_id,
            $data->quantity,
            $data->unit_cost ?? null,
            $totalCost,
            $data->notes ?? null
        ]);
    }

    /**
     * Get stock transfers
     */
    public function getStockTransfers($restaurantId, $status, $page, $limit)
    {
        $transferModel = new StockTransfer();
        return $transferModel->getPaginated($restaurantId, $status, $page, $limit);
    }

    /**
     * Log waste
     */
    public function logWaste($restaurantId, $userId, $data)
    {
        $wasteModel = new WasteLog();
        $itemModel = new InventoryItem();
        
        $item = $itemModel->findById($data->inventory_item_id, $restaurantId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $wasteData = [
            'restaurant_id' => $restaurantId,
            'inventory_item_id' => $data->inventory_item_id,
            'waste_quantity' => $data->waste_quantity,
            'waste_reason' => $data->waste_reason,
            'waste_reason_description' => $data->waste_reason_description ?? null,
            'unit_cost' => $item['cost_per_unit'],
            'total_cost' => $item['cost_per_unit'] * $data->waste_quantity,
            'location_id' => $data->location_id ?? null,
            'reported_by' => $userId,
            'waste_date' => date('Y-m-d H:i:s'),
            'notes' => $data->notes ?? null
        ];
        
        $wasteId = $wasteModel->create($wasteData);
        
        if (!$wasteId) {
            return ['success' => false, 'message' => 'Failed to log waste'];
        }
        
        // Adjust stock
        $this->adjustStock($restaurantId, $userId, (object)[
            'item_id' => $data->inventory_item_id,
            'quantity' => $data->waste_quantity,
            'direction' => 'out',
            'movement_type' => 'waste',
            'notes' => 'Waste logged: ' . $data->waste_reason
        ]);
        
        return ['success' => true, 'message' => 'Waste logged successfully', 'waste_id' => $wasteId];
    }

    /**
     * Get waste logs
     */
    public function getWasteLogs($restaurantId, $itemId, $reason, $dateFrom, $dateTo, $page, $limit)
    {
        $wasteModel = new WasteLog();
        return $wasteModel->getPaginated($restaurantId, $itemId, $reason, $dateFrom, $dateTo, $page, $limit);
    }

    /**
     * Get stock alerts
     */
    public function getStockAlerts($restaurantId, $alertType, $isResolved, $page, $limit)
    {
        $alertModel = new StockAlert();
        return $alertModel->getPaginated($restaurantId, $alertType, $isResolved, $page, $limit);
    }

    /**
     * Resolve stock alert
     */
    public function resolveAlert($id, $restaurantId, $userId)
    {
        $alertModel = new StockAlert();
        $alert = $alertModel->findById($id, $restaurantId);
        
        if (!$alert) {
            return ['success' => false, 'message' => 'Alert not found'];
        }
        
        $updated = $alertModel->update($id, [
            'is_resolved' => true,
            'resolved_at' => date('Y-m-d H:i:s'),
            'resolved_by' => $userId
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to resolve alert'];
        }
        
        return ['success' => true, 'message' => 'Alert resolved successfully'];
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId)
    {
        $itemModel = new InventoryItem();
        $movementModel = new StockMovement();
        $wasteModel = new WasteLog();
        $alertModel = new StockAlert();
        
        // Total items
        $totalItems = $itemModel->countByRestaurant($restaurantId);
        
        // Low stock items
        $lowStockItems = $itemModel->countLowStock($restaurantId);
        
        // Total stock value
        $sql = "SELECT SUM(current_stock * cost_per_unit) as total_value FROM inventory_items WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        $totalValue = $result['total_value'] ?? 0;
        
        // Total waste this month
        $sql = "SELECT SUM(total_cost) as total_waste FROM waste_logs WHERE restaurant_id = ? AND waste_date >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        $totalWaste = $result['total_waste'] ?? 0;
        
        // Unresolved alerts
        $unresolvedAlerts = $alertModel->countUnresolved($restaurantId);
        
        return [
            'total_items' => $totalItems,
            'low_stock_items' => $lowStockItems,
            'total_stock_value' => $totalValue,
            'total_waste_this_month' => $totalWaste,
            'unresolved_alerts' => $unresolvedAlerts
        ];
    }
}
