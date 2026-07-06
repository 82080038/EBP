<?php

if (!class_exists('PurchasePlanningService')) {
    require_once __DIR__ . '/../Services/PurchasePlanningService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



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

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
