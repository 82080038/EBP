<?php

if (!class_exists('MaintenanceService')) {
    require_once __DIR__ . '/../Services/MaintenanceService.php';
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

class MaintenanceController
{
    private $service;

    public function __construct()
    {
        $this->service = new MaintenanceService();
    }

    public function createAsset($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MAINTENANCE_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createAsset($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['asset_id' => $result['asset_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function createSchedule($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MAINTENANCE_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createMaintenanceSchedule($data, $user['tenant_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['schedule_id' => $result['schedule_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function completeMaintenance($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MAINTENANCE_MANAGE');

        $scheduleId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->completeMaintenance($scheduleId, $user['user_id'], $data['notes'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAssets($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getAssets($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getSchedules($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getSchedules($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
