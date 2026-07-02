<?php

if (!class_exists('SupplierPerformanceService')) {
    require_once __DIR__ . '/../Services/SupplierPerformanceService.php';
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

class SupplierPerformanceController
{
    private $service;

    public function __construct()
    {
        $this->service = new SupplierPerformanceService();
    }

    public function evaluateSupplier($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->evaluateSupplier($data, $user['tenant_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['performance_id' => $result['performance_id'], 'overall_rating' => $result['overall_rating']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getSupplierPerformance($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $supplierId = $request['params']['id'] ?? null;
        $dateFrom = $request['params']['start_date'] ?? null;
        $dateTo = $request['params']['end_date'] ?? null;

        if (!$supplierId) {
            Response::error('Supplier ID is required');
            return;
        }

        $result = $this->service->getSupplierPerformance($user['tenant_id'], $supplierId, $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getSupplierRanking($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $dateFrom = $request['params']['start_date'] ?? null;
        $dateTo = $request['params']['end_date'] ?? null;

        $result = $this->service->getSupplierRanking($user['tenant_id'], $user['branch_id'], $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
