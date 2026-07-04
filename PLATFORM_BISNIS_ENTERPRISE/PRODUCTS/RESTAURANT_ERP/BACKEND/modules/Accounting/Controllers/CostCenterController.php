<?php

if (!class_exists('CostCenterService')) {
    require_once __DIR__ . '/../Services/CostCenterService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';




class CostCenterController
{
    private $service;

    public function __construct()
    {
        $this->service = new CostCenterService();
    }

    public function createCostCenter($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createCostCenter($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['cost_center_id' => $result['cost_center_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCostCenters($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getCostCenters($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCostCenterReport($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $costCenterId = $request['params']['id'] ?? null;
        $dateFrom = $request['params']['start_date'] ?? null;
        $dateTo = $request['params']['end_date'] ?? null;

        $result = $this->service->getCostCenterReport($user['tenant_id'], $user['branch_id'], $costCenterId, $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateCostCenter($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_MANAGE');

        $costCenterId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        if (!$costCenterId) {
            Response::error(Messages::ACCOUNTING_COST_CENTER_REQUIRED);
            return;
        }

        $result = $this->service->updateCostCenter($costCenterId, $data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
