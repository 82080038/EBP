<?php

if (!class_exists('QualityComplianceService')) {
    require_once __DIR__ . '/../Services/QualityComplianceService.php';
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

class QualityComplianceController
{
    private $service;

    public function __construct()
    {
        $this->service = new QualityComplianceService();
    }

    public function createComplianceCheck($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'QUALITY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createComplianceCheck($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['check_id' => $result['check_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getComplianceReport($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'REPORT_VIEW');

        $dateFrom = $request['params']['start_date'] ?? date('Y-m-01');
        $dateTo = $request['params']['end_date'] ?? date('Y-m-t');

        $result = $this->service->getComplianceReport($user['tenant_id'], $user['branch_id'], $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function addFoodSafetyProtocol($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SETTINGS_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->addFoodSafetyProtocol($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['protocol_id' => $result['protocol_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getFoodSafetyProtocols($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getFoodSafetyProtocols($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
