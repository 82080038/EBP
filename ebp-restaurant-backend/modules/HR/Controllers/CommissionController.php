<?php

require_once __DIR__ . '/../Services/CommissionService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class CommissionController
{
    private $service;

    public function __construct()
    {
        $this->service = new CommissionService();
    }

    public function createCommission($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'HR_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createCommission($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['commission_id' => $result['commission_id'], 'commission_amount' => $result['commission_amount']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function approveCommission($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'HR_MANAGE');

        $commissionId = $request['params']['id'] ?? null;

        if (!$commissionId) {
            Response::error('Commission ID is required');
            return;
        }

        $result = $this->service->approveCommission($commissionId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function payCommission($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'HR_MANAGE');

        $commissionId = $request['params']['id'] ?? null;

        if (!$commissionId) {
            Response::error('Commission ID is required');
            return;
        }

        $result = $this->service->payCommission($commissionId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getEmployeeCommissions($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $employeeId = $request['params']['employee_id'] ?? null;
        $startDate = $request['params']['start_date'] ?? null;
        $endDate = $request['params']['end_date'] ?? null;

        if (!$employeeId) {
            Response::error('Employee ID is required');
            return;
        }

        $result = $this->service->getEmployeeCommissions($user['tenant_id'], $user['branch_id'], $employeeId, $startDate, $endDate);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getPendingCommissions($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getPendingCommissions($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
