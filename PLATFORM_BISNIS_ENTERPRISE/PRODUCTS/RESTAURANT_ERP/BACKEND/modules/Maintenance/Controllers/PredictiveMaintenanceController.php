<?php

if (!class_exists('PredictiveMaintenanceService')) {
    require_once __DIR__ . '/../Services/PredictiveMaintenanceService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class PredictiveMaintenanceController
{
    private $service;

    public function __construct()
    {
        $this->service = new PredictiveMaintenanceService();
    }

    public function predictNeeds($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MAINTENANCE_MANAGE');

        $result = $this->service->predictMaintenanceNeeds($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
