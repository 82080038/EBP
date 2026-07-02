<?php

if (!class_exists('SustainabilityService')) {
    require_once __DIR__ . '/../Services/SustainabilityService.php';
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

class SustainabilityController
{
    private $service;

    public function __construct()
    {
        $this->service = new SustainabilityService();
    }

    public function recordWaste($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SUSTAINABILITY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->recordWaste($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['waste_id' => $result['waste_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function recordMetrics($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SUSTAINABILITY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->recordSustainabilityMetrics($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['metric_id' => $result['metric_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getWasteTracking($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        $result = $this->service->getWasteTracking($user['tenant_id'], $user['branch_id'], $startDate, $endDate);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getSustainabilityMetrics($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        $result = $this->service->getSustainabilityMetrics($user['tenant_id'], $user['branch_id'], $startDate, $endDate);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
