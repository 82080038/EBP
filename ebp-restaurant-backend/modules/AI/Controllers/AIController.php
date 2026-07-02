<?php

if (!class_exists('AIPredictionService')) {
    require_once __DIR__ . '/../Services/AIPredictionService.php';
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

class AIController
{
    private $service;

    public function __construct()
    {
        $this->service = new AIPredictionService();
    }

    public function generateSalesForecast($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $days = $params['days'] ?? 7;

        $result = $this->service->generateSalesForecast($user['tenant_id'], $user['branch_id'], $days);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function generateInventoryPrediction($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $inventoryId = $request['params']['id'] ?? null;

        $result = $this->service->generateInventoryPrediction($user['tenant_id'], $user['branch_id'], $inventoryId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
