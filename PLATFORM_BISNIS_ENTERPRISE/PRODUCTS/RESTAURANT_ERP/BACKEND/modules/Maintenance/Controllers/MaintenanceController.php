<?php

if (!class_exists('MaintenanceService')) {
    require_once __DIR__ . '/../Services/MaintenanceService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



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

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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
            Response::success($result['data'], $result['message']);
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
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
