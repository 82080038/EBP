<?php

if (!class_exists('BonusService')) {
    require_once __DIR__ . '/../Services/BonusService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class BonusController
{
    private $service;

    public function __construct()
    {
        $this->service = new BonusService();
    }

    public function createBonus($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createBonus($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['bonus_id' => $result['bonus_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function approveBonus($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $bonusId = $request['params']['id'] ?? null;

        if (!$bonusId) {
            Response::error('Bonus ID is required');
            return;
        }

        $result = $this->service->approveBonus($bonusId, $user['tenant_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function payBonus($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $bonusId = $request['params']['id'] ?? null;

        if (!$bonusId) {
            Response::error('Bonus ID is required');
            return;
        }

        $result = $this->service->payBonus($bonusId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getEmployeeBonuses($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $employeeId = $request['params']['employee_id'] ?? null;

        if (!$employeeId) {
            Response::error('Employee ID is required');
            return;
        }

        $result = $this->service->getEmployeeBonuses($user['tenant_id'], $user['branch_id'], $employeeId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getPendingBonuses($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getPendingBonuses($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
