<?php

if (!class_exists('SmartProcurementService')) {
    require_once __DIR__ . '/../Services/SmartProcurementService.php';
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

class SmartProcurementController
{
    private $service;

    public function __construct()
    {
        $this->service = new SmartProcurementService();
    }

    public function generateRecommendation($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

        $forecastDays = $request['params']['days'] ?? 30;

        $result = $this->service->generateProcurementRecommendation($user['tenant_id'], $user['branch_id'], $forecastDays);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
