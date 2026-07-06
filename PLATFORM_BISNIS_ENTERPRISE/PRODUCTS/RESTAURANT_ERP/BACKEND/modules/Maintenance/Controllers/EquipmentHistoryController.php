<?php

if (!class_exists('EquipmentHistoryService')) {
    require_once __DIR__ . '/../Services/EquipmentHistoryService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



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

        // $permissionMiddleware = new PermissionMiddleware();

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
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
