<?php

if (!class_exists('PredictiveMaintenanceService')) {
    require_once __DIR__ . '/../Services/PredictiveMaintenanceService.php';
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
