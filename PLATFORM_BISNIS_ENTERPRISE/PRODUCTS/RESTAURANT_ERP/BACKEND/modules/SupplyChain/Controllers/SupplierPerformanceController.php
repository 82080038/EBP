<?php

if (!class_exists('SupplierPerformanceService')) {
    require_once __DIR__ . '/../Services/SupplierPerformanceService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



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

        // $permissionMiddleware = new PermissionMiddleware();

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
            Response::success($result['data'], $result['message']);
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
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
