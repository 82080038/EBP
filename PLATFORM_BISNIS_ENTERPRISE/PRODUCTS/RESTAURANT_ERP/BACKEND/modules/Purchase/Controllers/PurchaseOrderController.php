<?php

namespace App\Modules\Purchase\Controllers;

use App\Core\BaseController;
use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Models\GoodsReceipt;
use App\Modules\Purchase\Services\PurchaseOrderService;
use App\Core\Auth;

class PurchaseOrderController extends BaseController
{
    private $purchaseOrderService;

    public function __construct()
    {
        parent::__construct();
        $this->purchaseOrderService = new PurchaseOrderService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get purchase orders
     * GET /api/purchase-orders
     */
    public function getPurchaseOrders()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $supplierId = $this->request->get('supplier_id', null);
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->purchaseOrderService->getPurchaseOrders($restaurantId, $status, $supplierId, $dateFrom, $dateTo, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get single purchase order
     * GET /api/purchase-orders/{id}
     */
    public function getPurchaseOrder($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $order = $this->purchaseOrderService->getPurchaseOrder($id, $restaurantId);
        
        if (!$order) {
            $this->jsonResponse(['error' => 'Purchase order not found'], 404);
            return;
        }
        
        $this->jsonResponse($order);
    }

    /**
     * Create purchase order
     * POST /api/purchase-orders
     */
    public function createPurchaseOrder()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->purchaseOrderService->createPurchaseOrder($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update purchase order
     * PUT /api/purchase-orders/{id}
     */
    public function updatePurchaseOrder($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->purchaseOrderService->updatePurchaseOrder($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Submit purchase order
     * POST /api/purchase-orders/{id}/submit
     */
    public function submitPurchaseOrder($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $result = $this->purchaseOrderService->submitPurchaseOrder($id, $restaurantId, $userId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Approve purchase order
     * POST /api/purchase-orders/{id}/approve
     */
    public function approvePurchaseOrder($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $result = $this->purchaseOrderService->approvePurchaseOrder($id, $restaurantId, $userId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Reject purchase order
     * POST /api/purchase-orders/{id}/reject
     */
    public function rejectPurchaseOrder($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->purchaseOrderService->rejectPurchaseOrder($id, $restaurantId, $userId, $data->reason ?? null);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Cancel purchase order
     * POST /api/purchase-orders/{id}/cancel
     */
    public function cancelPurchaseOrder($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $result = $this->purchaseOrderService->cancelPurchaseOrder($id, $restaurantId, $userId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get goods receipts
     * GET /api/purchase-orders/{id}/receipts
     */
    public function getGoodsReceipts($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $receipts = $this->purchaseOrderService->getGoodsReceipts($id, $restaurantId);
        
        $this->jsonResponse($receipts);
    }

    /**
     * Create goods receipt
     * POST /api/purchase-orders/{id}/receipts
     */
    public function createGoodsReceipt($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->purchaseOrderService->createGoodsReceipt($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get purchase order statistics
     * GET /api/purchase-orders/statistics
     */
    public function getStatistics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $stats = $this->purchaseOrderService->getStatistics($restaurantId);
        
        $this->jsonResponse($stats);
    }
}
