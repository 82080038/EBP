<?php

if (!class_exists('SupplyChainService')) {
    require_once __DIR__ . '/../Services/SupplyChainService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



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

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
