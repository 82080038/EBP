<?php

namespace App\Modules\Order\Controllers;

use App\Core\BaseController;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Models\OrderModifier;
use App\Modules\Order\Models\KitchenOrder;
use App\Modules\Order\Models\TableSession;
use App\Modules\Order\Services\OrderService;
use App\Core\Auth;

class OrderController extends BaseController
{
    private $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get orders
     * GET /api/orders
     */
    public function getOrders()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $tableId = $this->request->get('table_id', null);
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        
        // Get screen size for responsive data
        $headers = getallheaders();
        $screenSize = \ScreenSizeHelper::getScreenSize($headers, $this->request->getAll());
        
        // Get pagination with screen size defaults
        $pagination = \ScreenSizeHelper::getPaginationParams($this->request->getAll(), $screenSize, 'orders');
        $page = $pagination['page'];
        $limit = $pagination['limit'];
        
        $result = $this->orderService->getOrders($restaurantId, $status, $tableId, $dateFrom, $dateTo, $page, $limit);
        
        // Apply screen size field filtering
        $result = \ScreenSizeHelper::applyScreenSizeFilter($result, $screenSize, 'orders');
        
        $this->jsonResponse($result);
    }

    /**
     * Get single order
     * GET /api/orders/{id}
     */
    public function getOrder($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $order = $this->orderService->getOrder($id, $restaurantId);
        
        if (!$order) {
            $this->jsonResponse(['error' => 'Order not found'], 404);
            return;
        }
        
        $this->jsonResponse($order);
    }

    /**
     * Create order
     * POST /api/orders
     */
    public function createOrder()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->orderService->createOrder($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update order
     * PUT /api/orders/{id}
     */
    public function updateOrder($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->orderService->updateOrder($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Update order status
     * PATCH /api/orders/{id}/status
     */
    public function updateOrderStatus($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->orderService->updateOrderStatus($id, $restaurantId, $userId, $data->status, $data->notes ?? null);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Cancel order
     * POST /api/orders/{id}/cancel
     */
    public function cancelOrder($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->orderService->cancelOrder($id, $restaurantId, $userId, $data->reason ?? null);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Add order item
     * POST /api/orders/{id}/items
     */
    public function addOrderItem($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->orderService->addOrderItem($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update order item
     * PUT /api/orders/{orderId}/items/{itemId}
     */
    public function updateOrderItem($orderId, $itemId)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->orderService->updateOrderItem($itemId, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Remove order item
     * DELETE /api/orders/{orderId}/items/{itemId}
     */
    public function removeOrderItem($orderId, $itemId)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $result = $this->orderService->removeOrderItem($itemId, $restaurantId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse(['message' => 'Item removed successfully']);
    }

    /**
     * Get kitchen orders
     * GET /api/orders/kitchen
     */
    public function getKitchenOrders()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $station = $this->request->get('station', null);
        $status = $this->request->get('status', null);
        
        $orders = $this->orderService->getKitchenOrders($restaurantId, $station, $status);
        
        $this->jsonResponse($orders);
    }

    /**
     * Update kitchen order status
     * PATCH /api/orders/kitchen/{id}/status
     */
    public function updateKitchenOrderStatus($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->orderService->updateKitchenOrderStatus($id, $restaurantId, $userId, $data->status);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get table sessions
     * GET /api/orders/table-sessions
     */
    public function getTableSessions()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $tableId = $this->request->get('table_id', null);
        $status = $this->request->get('status', null);
        
        $sessions = $this->orderService->getTableSessions($restaurantId, $tableId, $status);
        
        $this->jsonResponse($sessions);
    }

    /**
     * Create table session
     * POST /api/orders/table-sessions
     */
    public function createTableSession()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->orderService->createTableSession($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Close table session
     * POST /api/orders/table-sessions/{id}/close
     */
    public function closeTableSession($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $result = $this->orderService->closeTableSession($id, $restaurantId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get order statistics
     * GET /api/orders/statistics
     */
    public function getStatistics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        
        $stats = $this->orderService->getStatistics($restaurantId, $dateFrom, $dateTo);
        
        $this->jsonResponse($stats);
    }
}
