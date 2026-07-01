<?php

require_once __DIR__ . '/../Services/EnterpriseService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class EnterpriseController
{
    private $service;

    public function __construct()
    {
        $this->service = new EnterpriseService();
    }

    public function createShiftSchedule($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'HR_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createShiftSchedule($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['schedule_id' => $result['schedule_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function createPerformanceEvaluation($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'HR_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createPerformanceEvaluation($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['evaluation_id' => $result['evaluation_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function recordCashFlow($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->recordCashFlow($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['cash_flow_id' => $result['cash_flow_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function createBudget($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createBudget($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['budget_id' => $result['budget_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateBudgetActuals($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->updateBudgetActuals($user['tenant_id'], $user['branch_id'], $data['period_start'], $data['period_end']);

        if ($result['success']) {
            Response::success($result['message'], ['updated_budgets' => $result['updated_budgets']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getShiftSchedules($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $date = $params['date'] ?? null;

        $result = $this->service->getShiftSchedules($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getPerformanceEvaluations($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $employeeId = $params['employee_id'] ?? null;

        $result = $this->service->getPerformanceEvaluations($user['tenant_id'], $user['branch_id'], $employeeId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCashFlow($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        $result = $this->service->getCashFlow($user['tenant_id'], $user['branch_id'], $startDate, $endDate);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBudgets($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $periodStart = $params['start_date'] ?? null;
        $periodEnd = $params['end_date'] ?? null;

        $result = $this->service->getBudgets($user['tenant_id'], $user['branch_id'], $periodStart, $periodEnd);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
