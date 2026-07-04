<?php

namespace App\Modules\Inventory\Controllers;

use App\Core\BaseController;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockTransfer;
use App\Modules\Inventory\Models\WasteLog;
use App\Modules\Inventory\Models\StockAlert;
use App\Modules\Inventory\Services\InventoryManagementService;
use App\Core\Auth;

class InventoryManagementController extends BaseController
{
    private $inventoryService;

    public function __construct()
    {
        parent::__construct();
        $this->inventoryService = new InventoryManagementService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get inventory items
     * GET /api/inventory/items
     */
    public function getItems()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $categoryId = $this->request->get('category_id', null);
        $lowStock = $this->request->get('low_stock', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->inventoryService->getItems($restaurantId, $categoryId, $lowStock, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get single inventory item
     * GET /api/inventory/items/{id}
     */
    public function getItem($id)
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $item = $this->inventoryService->getItem($id, $restaurantId);
        
        if (!$item) {
            $this->jsonResponse(['error' => 'Item not found'], 404);
            return;
        }
        
        $this->jsonResponse($item);
    }

    /**
     * Create inventory item
     * POST /api/inventory/items
     */
    public function createItem()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->inventoryService->createItem($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update inventory item
     * PUT /api/inventory/items/{id}
     */
    public function updateItem($id)
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->inventoryService->updateItem($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Adjust stock
     * POST /api/inventory/adjust-stock
     */
    public function adjustStock()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->inventoryService->adjustStock($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get stock movements
     * GET /api/inventory/stock-movements
     */
    public function getStockMovements()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $itemId = $this->request->get('item_id', null);
        $movementType = $this->request->get('movement_type', null);
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->inventoryService->getStockMovements($restaurantId, $itemId, $movementType, $dateFrom, $dateTo, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Create stock transfer
     * POST /api/inventory/stock-transfers
     */
    public function createStockTransfer()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->inventoryService->createStockTransfer($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get stock transfers
     * GET /api/inventory/stock-transfers
     */
    public function getStockTransfers()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->inventoryService->getStockTransfers($restaurantId, $status, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Log waste
     * POST /api/inventory/waste
     */
    public function logWaste()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->inventoryService->logWaste($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get waste logs
     * GET /api/inventory/waste
     */
    public function getWasteLogs()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $itemId = $this->request->get('item_id', null);
        $reason = $this->request->get('reason', null);
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->inventoryService->getWasteLogs($restaurantId, $itemId, $reason, $dateFrom, $dateTo, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get stock alerts
     * GET /api/inventory/alerts
     */
    public function getStockAlerts()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $alertType = $this->request->get('alert_type', null);
        $isResolved = $this->request->get('is_resolved', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->inventoryService->getStockAlerts($restaurantId, $alertType, $isResolved, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Resolve stock alert
     * POST /api/inventory/alerts/{id}/resolve
     */
    public function resolveAlert($id)
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $result = $this->inventoryService->resolveAlert($id, $restaurantId, $userId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get inventory statistics
     * GET /api/inventory/statistics
     */
    public function getStatistics()
    {
        $this->requirePermission('can_manage_inventory');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $stats = $this->inventoryService->getStatistics($restaurantId);
        
        $this->jsonResponse($stats);
    }
}
