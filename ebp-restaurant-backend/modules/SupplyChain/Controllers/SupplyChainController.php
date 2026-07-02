<?php

if (!class_exists('SupplyChainService')) {
    require_once __DIR__ . '/../Services/SupplyChainService.php';
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

class SupplyChainController
{
    private $service;

    public function __construct()
    {
        $this->service = new SupplyChainService();
    }

    public function createRequisition($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SUPPLY_CHAIN_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createPurchaseRequisition($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['requisition_id' => $result['requisition_id'], 'requisition_number' => $result['requisition_number']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function approveRequisition($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SUPPLY_CHAIN_MANAGE');

        $reqId = $request['params']['id'] ?? null;

        $result = $this->service->approveRequisition($reqId, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getRequisitions($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
