<?php

if (!class_exists('KitchenService')) {
    require_once __DIR__ . '/../Services/KitchenService.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}

class KitchenController
{
    private $kitchenService;

    public function __construct()
    {
        $this->kitchenService = new KitchenService();
    }

    public function getKitchenOrders(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'KITCHEN_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $kitchenOrders = $this->kitchenService->getAllKitchenOrders($tenantId, $branchId);

        return Response::success($kitchenOrders);
    }

    public function getPendingOrders(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'KITCHEN_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $kitchenOrders = $this->kitchenService->getKitchenOrdersByStatus($tenantId, $branchId, 'PENDING');

        return Response::success($kitchenOrders);
    }

    public function getInProgressOrders(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'KITCHEN_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $kitchenOrders = $this->kitchenService->getKitchenOrdersByStatus($tenantId, $branchId, 'IN_PROGRESS');

        return Response::success($kitchenOrders);
    }

    public function getReadyOrders(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'KITCHEN_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $kitchenOrders = $this->kitchenService->getKitchenOrdersByStatus($tenantId, $branchId, 'READY');

        return Response::success($kitchenOrders);
    }

    public function getKitchenOrder(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'KITCHEN_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $kitchenOrderId = $request['kitchen_order_id'] ?? 0;

        $kitchenOrder = $this->kitchenService->getKitchenOrder($tenantId, $kitchenOrderId);

        if (!$kitchenOrder) {
            return Response::error('Kitchen order not found', 404);
        }

        return Response::success($kitchenOrder);
    }

    public function createKitchenOrder(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'KITCHEN_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['order_id'])) {
            return Response::error('Order ID is required', 400);
        }
        if (empty($data['items']) || !is_array($data['items'])) {
            return Response::error('Items are required', 400);
        }

        $result = $this->kitchenService->createKitchenOrder($tenantId, $data['order_id'], $data['items']);

        if ($result) {
            return Response::success(['message' => 'Kitchen order created successfully']);
        }

        return Response::error('Failed to create kitchen order', 500);
    }

    public function updateKitchenOrderStatus(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'KITCHEN_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $kitchenOrderId = $request['kitchen_order_id'] ?? 0;
        $status = $request['body']['status'] ?? '';

        // Validation
        if (empty($kitchenOrderId)) {
            return Response::error('Kitchen order ID is required', 400);
        }
        if (empty($status)) {
            return Response::error('Status is required', 400);
        }

        $validStatuses = ['PENDING', 'IN_PROGRESS', 'READY', 'SERVED', 'CANCELLED'];
        if (!in_array($status, $validStatuses)) {
            return Response::error('Invalid status', 400);
        }

        $result = $this->kitchenService->updateKitchenOrderStatus($tenantId, $kitchenOrderId, $status);

        if ($result) {
            return Response::success(['message' => 'Kitchen order status updated successfully']);
        }

        return Response::error('Failed to update kitchen order status', 500);
    }

    public function updateKitchenOrderPriority(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'KITCHEN_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $kitchenOrderId = $request['kitchen_order_id'] ?? 0;
        $priority = $request['body']['priority'] ?? '';

        // Validation
        if (empty($kitchenOrderId)) {
            return Response::error('Kitchen order ID is required', 400);
        }
        if (empty($priority)) {
            return Response::error('Priority is required', 400);
        }

        $validPriorities = ['LOW', 'NORMAL', 'HIGH', 'URGENT'];
        if (!in_array($priority, $validPriorities)) {
            return Response::error('Invalid priority', 400);
        }

        $result = $this->kitchenService->updateKitchenOrderPriority($tenantId, $kitchenOrderId, $priority);

        if ($result) {
            return Response::success(['message' => 'Kitchen order priority updated successfully']);
        }

        return Response::error('Failed to update kitchen order priority', 500);
    }

    public function updateKitchenItemStatus(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'KITCHEN_VIEW');

        $kitchenOrderItemId = $request['kitchen_order_item_id'] ?? 0;
        $status = $request['body']['status'] ?? '';

        // Validation
        if (empty($kitchenOrderItemId)) {
            return Response::error('Kitchen order item ID is required', 400);
        }
        if (empty($status)) {
            return Response::error('Status is required', 400);
        }

        $validStatuses = ['PENDING', 'PREPARING', 'READY', 'SERVED', 'CANCELLED'];
        if (!in_array($status, $validStatuses)) {
            return Response::error('Invalid status', 400);
        }

        $result = $this->kitchenService->updateKitchenItemStatus($kitchenOrderItemId, $status);

        if ($result) {
            return Response::success(['message' => 'Kitchen item status updated successfully']);
        }

        return Response::error('Failed to update kitchen item status', 500);
    }
}
