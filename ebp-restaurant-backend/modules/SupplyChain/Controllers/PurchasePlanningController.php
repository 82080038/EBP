<?php

if (!class_exists('PurchasePlanningService')) {
    require_once __DIR__ . '/../Services/PurchasePlanningService.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}

class PurchasePlanningController
{
    private $service;

    public function __construct()
    {
        $this->service = new PurchasePlanningService();
    }

    public function generatePurchasePlan($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

        $planningDate = $request['body']['planning_date'] ?? date('Y-m-d');

        $result = $this->service->generatePurchasePlan($user['tenant_id'], $user['branch_id'], $planningDate);

        if ($result['success']) {
            Response::success($result['message'], ['plan_id' => $result['plan_id'], 'items' => $result['items']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function approvePurchasePlan($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

        $planId = $request['params']['id'] ?? null;

        if (!$planId) {
            Response::error('Plan ID is required');
            return;
        }

        $result = $this->service->approvePurchasePlan($planId, $user['tenant_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['requisition_id' => $result['requisition_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getPurchasePlans($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $status = $request['params']['status'] ?? null;

        $result = $this->service->getPurchasePlans($user['tenant_id'], $user['branch_id'], $status);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
