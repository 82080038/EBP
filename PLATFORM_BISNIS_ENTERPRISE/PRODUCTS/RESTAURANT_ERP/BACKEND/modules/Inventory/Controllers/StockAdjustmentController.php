<?php

if (!class_exists('StockAdjustmentService')) {
    require_once __DIR__ . '/../Services/StockAdjustmentService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class StockAdjustmentController
{
    private $service;

    public function __construct()
    {
        $this->service = new StockAdjustmentService();
    }

    public function create($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createAdjustment($data, $user['user_id'], $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['adjustment_id' => $result['adjustment_id'], 'adjustment_number' => $result['adjustment_number']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getAdjustments($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function approve($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $adjustmentId = $request['params']['id'] ?? null;

        $result = $this->service->approveAdjustment($adjustmentId, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
