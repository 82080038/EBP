<?php

if (!class_exists('QualityControlService')) {
    require_once __DIR__ . '/../Services/QualityControlService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class QualityControlController
{
    private $service;

    public function __construct()
    {
        $this->service = new QualityControlService();
    }

    public function createQualityCheck($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createQualityCheck($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['check_id' => $result['check_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateQualityCheckResult($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $checkId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        if (!$checkId) {
            Response::error('Check ID is required');
            return;
        }

        $result = $this->service->updateQualityCheckResult($checkId, $data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getQualityChecks($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $checkType = $request['params']['type'] ?? null;
        $status = $request['params']['status'] ?? null;

        $result = $this->service->getQualityChecks($user['tenant_id'], $user['branch_id'], $checkType, $status);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getQualityReport($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $dateFrom = $request['params']['start_date'] ?? date('Y-m-01');
        $dateTo = $request['params']['end_date'] ?? date('Y-m-t');

        $result = $this->service->getQualityReport($user['tenant_id'], $user['branch_id'], $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
