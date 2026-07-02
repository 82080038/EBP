<?php

if (!class_exists('CustomerIntelligenceService')) {
    require_once __DIR__ . '/../Services/CustomerIntelligenceService.php';
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

class CustomerIntelligenceController
{
    private $service;

    public function __construct()
    {
        $this->service = new CustomerIntelligenceService();
    }

    public function analyzeBehavior($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'REPORT_VIEW');

        $dateFrom = $request['params']['start_date'] ?? date('Y-m-01');
        $dateTo = $request['params']['end_date'] ?? date('Y-m-t');

        $result = $this->service->analyzeCustomerBehavior($user['tenant_id'], $user['branch_id'], $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
