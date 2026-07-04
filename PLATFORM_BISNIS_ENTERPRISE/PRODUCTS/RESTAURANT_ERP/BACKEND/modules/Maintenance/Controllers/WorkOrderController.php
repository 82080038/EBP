<?php

if (!class_exists('WorkOrderService')) {
    require_once __DIR__ . '/../Services/WorkOrderService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class WorkOrderController
{
    private $service;

    public function __construct()
    {
        $this->service = new WorkOrderService();
    }

    public function createWorkOrder($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MAINTENANCE_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createWorkOrder($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['work_order_id' => $result['work_order_id'], 'work_order_number' => $result['work_order_number']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateWorkOrder($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MAINTENANCE_MANAGE');

        $workOrderId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        if (!$workOrderId) {
            Response::error('Work Order ID is required');
            return;
        }

        $result = $this->service->updateWorkOrder($workOrderId, $data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getWorkOrders($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $status = $request['params']['status'] ?? null;

        $result = $this->service->getWorkOrders($user['tenant_id'], $user['branch_id'], $status);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
