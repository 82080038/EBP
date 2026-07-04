<?php

if (!class_exists('KitchenIntelligenceService')) {
    require_once __DIR__ . '/../Services/KitchenIntelligenceService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class KitchenIntelligenceController
{
    private $service;

    public function __construct()
    {
        $this->service = new KitchenIntelligenceService();
    }

    public function analyzePerformance($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'REPORT_VIEW');

        $dateFrom = $request['params']['start_date'] ?? date('Y-m-01');
        $dateTo = $request['params']['end_date'] ?? date('Y-m-t');

        $result = $this->service->analyzeKitchenPerformance($user['tenant_id'], $user['branch_id'], $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
