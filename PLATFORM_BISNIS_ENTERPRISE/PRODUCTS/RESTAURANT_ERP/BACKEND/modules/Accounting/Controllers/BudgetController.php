<?php

if (!class_exists('BudgetService')) {
    require_once __DIR__ . '/../Services/BudgetService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class BudgetController
{
    private $service;

    public function __construct()
    {
        $this->service = new BudgetService();
    }

    public function createBudget($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createBudget($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success(['budget_id' => $result['budget_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBudgets($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['query'] ?? [];
        $fiscalYear = $params['fiscal_year'] ?? null;
        $status = $params['status'] ?? null;

        $result = $this->service->getBudgets($user['tenant_id'], $user['branch_id'], $fiscalYear, $status);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBudget($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $budgetId = $params['id'];

        if (!$budgetId) {
            Response::error('Budget ID is required');
        }

        $result = $this->service->getBudget($user['tenant_id'], $user['branch_id'], $budgetId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function addBudgetItem($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->addBudgetItem($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success(['budget_item_id' => $result['budget_item_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function approveBudget($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $budgetId = $params['id'];

        if (!$budgetId) {
            Response::error('Budget ID is required');
        }

        $result = $this->service->approveBudget($user['tenant_id'], $user['branch_id'], $budgetId, $user['user_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBudgetVariance($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $budgetId = $params['id'];

        if (!$budgetId) {
            Response::error('Budget ID is required');
        }

        $result = $this->service->getBudgetVariance($user['tenant_id'], $user['branch_id'], $budgetId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
