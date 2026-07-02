<?php

if (!class_exists('WasteReductionService')) {
    require_once __DIR__ . '/../Services/WasteReductionService.php';
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

class WasteReductionController
{
    private $service;

    public function __construct()
    {
        $this->service = new WasteReductionService();
    }

    public function recordWaste($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->recordWaste($data, $user['tenant_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['waste_id' => $result['waste_id'], 'estimated_cost' => $result['estimated_cost']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getWasteReport($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $dateFrom = $request['params']['start_date'] ?? date('Y-m-01');
        $dateTo = $request['params']['end_date'] ?? date('Y-m-t');

        $result = $this->service->getWasteReport($user['tenant_id'], $user['branch_id'], $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
