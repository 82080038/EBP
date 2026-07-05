<?php

if (!class_exists('KitchenService')) {
    require_once __DIR__ . '/../Services/KitchenService.php';
}


// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';


class KitchenController
{
    private $kitchenService;

    public function __construct()
    {
        $this->kitchenService = new KitchenService();
    }

    public function getKitchenOrders(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $kitchenOrders = $this->kitchenService->getAllKitchenOrders($tenantId, $branchId);

        return Response::success($kitchenOrders);
    }

    public function getPendingOrders(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $kitchenOrders = $this->kitchenService->getKitchenOrdersByStatus($tenantId, $branchId, 'PENDING');

        return Response::success($kitchenOrders);
    }

    public function getInProgressOrders(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $kitchenOrders = $this->kitchenService->getKitchenOrdersByStatus($tenantId, $branchId, 'IN_PROGRESS');

        return Response::success($kitchenOrders);
    }

    public function getReadyOrders(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $kitchenOrders = $this->kitchenService->getKitchenOrdersByStatus($tenantId, $branchId, 'READY');

        return Response::success($kitchenOrders);
    }

    public function getKitchenOrder(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $kitchenOrderId = $request['kitchen_order_id'] ?? 0;

        $kitchenOrder = $this->kitchenService->getKitchenOrder($tenantId, $kitchenOrderId);

        if (!$kitchenOrder) {
            return Response::error(Messages::KITCHEN_ORDER_NOT_FOUND, 404);
        }

        return Response::success($kitchenOrder);
    }

    public function createKitchenOrder(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['order_id'])) {
            return Response::error(Messages::ORDER_ID_REQUIRED, 400);
        }
        if (empty($data['items']) || !is_array($data['items'])) {
            return Response::error(Messages::ORDER_ITEMS_REQUIRED, 400);
        }

        $result = $this->kitchenService->createKitchenOrder($tenantId, $data['order_id'], $data['items']);

        if ($result) {
            return Response::success(['message' => Messages::KITCHEN_ORDER_CREATED]);
        }

        return Response::error(Messages::KITCHEN_FAILED_CREATE, 500);
    }

    public function updateKitchenOrderStatus(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $kitchenOrderId = $request['kitchen_order_id'] ?? 0;
        $status = $request['body']['status'] ?? '';

        // Validation
        if (empty($kitchenOrderId)) {
            return Response::error(Messages::KITCHEN_ORDER_ID_REQUIRED, 400);
        }
        if (empty($status)) {
            return Response::error(Messages::KITCHEN_STATUS_REQUIRED, 400);
        }

        $validStatuses = ['PENDING', 'IN_PROGRESS', 'READY', 'SERVED', 'CANCELLED'];
        if (!in_array($status, $validStatuses)) {
            return Response::error(Messages::KITCHEN_STATUS_INVALID, 400);
        }

        $result = $this->kitchenService->updateKitchenOrderStatus($tenantId, $kitchenOrderId, $status);

        if ($result) {
            return Response::success(['message' => Messages::KITCHEN_ORDER_UPDATED]);
        }

        return Response::error(Messages::KITCHEN_FAILED_UPDATE_STATUS, 500);
    }

    public function updateKitchenOrderPriority(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $kitchenOrderId = $request['kitchen_order_id'] ?? 0;
        $priority = $request['body']['priority'] ?? '';

        // Validation
        if (empty($kitchenOrderId)) {
            return Response::error(Messages::KITCHEN_ORDER_ID_REQUIRED, 400);
        }
        if (empty($priority)) {
            return Response::error(Messages::KITCHEN_PRIORITY_REQUIRED, 400);
        }

        $validPriorities = ['LOW', 'NORMAL', 'HIGH', 'URGENT'];
        if (!in_array($priority, $validPriorities)) {
            return Response::error(Messages::KITCHEN_PRIORITY_INVALID, 400);
        }

        $result = $this->kitchenService->updateKitchenOrderPriority($tenantId, $kitchenOrderId, $priority);

        if ($result) {
            return Response::success(['message' => Messages::KITCHEN_ORDER_UPDATED]);
        }

        return Response::error(Messages::KITCHEN_FAILED_UPDATE_PRIORITY, 500);
    }

    public function updateKitchenItemStatus(array $request)
    {
        // Permission checking is now handled in routes
        $kitchenOrderItemId = $request['kitchen_order_item_id'] ?? 0;
        $status = $request['body']['status'] ?? '';

        // Validation
        if (empty($kitchenOrderItemId)) {
            return Response::error(Messages::KITCHEN_ORDER_ID_REQUIRED_ITEM, 400);
        }
        if (empty($status)) {
            return Response::error(Messages::KITCHEN_STATUS_REQUIRED, 400);
        }

        $validStatuses = ['PENDING', 'PREPARING', 'READY', 'SERVED', 'CANCELLED'];
        if (!in_array($status, $validStatuses)) {
            return Response::error(Messages::KITCHEN_STATUS_INVALID, 400);
        }

        $result = $this->kitchenService->updateKitchenItemStatus($kitchenOrderItemId, $status);

        if ($result) {
            return Response::success(['message' => Messages::KITCHEN_ORDER_UPDATED]);
        }

        return Response::error(Messages::KITCHEN_FAILED_UPDATE_ITEM, 500);
    }
}
