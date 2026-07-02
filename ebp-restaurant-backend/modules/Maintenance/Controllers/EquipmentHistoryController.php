<?php

if (!class_exists('EquipmentHistoryService')) {
    require_once __DIR__ . '/../Services/EquipmentHistoryService.php';
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

class EquipmentHistoryController
{
    private $service;

    public function __construct()
    {
        $this->service = new EquipmentHistoryService();
    }

    public function addHistory($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MAINTENANCE_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->addHistory($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['history_id' => $result['history_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getEquipmentHistory($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $assetId = $request['params']['id'] ?? null;

        if (!$assetId) {
            Response::error('Asset ID is required');
            return;
        }

        $result = $this->service->getEquipmentHistory($user['tenant_id'], $user['branch_id'], $assetId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
