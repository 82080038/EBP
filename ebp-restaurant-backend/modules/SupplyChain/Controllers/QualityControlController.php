<?php

if (!class_exists('QualityControlService')) {
    require_once __DIR__ . '/../Services/QualityControlService.php';
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

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

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

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

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
            Response::success($result['message'], $result['data']);
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
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
