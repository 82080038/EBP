<?php

if (!class_exists('SmartProcurementService')) {
    require_once __DIR__ . '/../Services/SmartProcurementService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



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

        // $permissionMiddleware = new PermissionMiddleware();

        $forecastDays = $request['params']['days'] ?? 30;

        $result = $this->service->generateProcurementRecommendation($user['tenant_id'], $user['branch_id'], $forecastDays);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
