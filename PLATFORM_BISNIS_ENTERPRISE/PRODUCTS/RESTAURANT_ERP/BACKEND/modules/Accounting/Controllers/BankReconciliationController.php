<?php

if (!class_exists('BankReconciliationService')) {
    require_once __DIR__ . '/../Services/BankReconciliationService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class BankReconciliationController
{
    private $service;

    public function __construct()
    {
        $this->service = new BankReconciliationService();
    }

    public function createReconciliation($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createReconciliation($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success(['reconciliation_id' => $result['reconciliation_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getReconciliations($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['query'] ?? [];
        $bankAccountId = $params['bank_account_id'] ?? null;
        $status = $params['status'] ?? null;

        $result = $this->service->getReconciliations($user['tenant_id'], $user['branch_id'], $bankAccountId, $status);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getReconciliation($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $reconciliationId = $params['id'];

        if (!$reconciliationId) {
            Response::error('Reconciliation ID is required');
        }

        $result = $this->service->getReconciliation($user['tenant_id'], $user['branch_id'], $reconciliationId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function addItem($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->addItem($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success(['item_id' => $result['item_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function reconcile($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $reconciliationId = $params['id'];

        if (!$reconciliationId) {
            Response::error('Reconciliation ID is required');
        }

        $result = $this->service->reconcile($user['tenant_id'], $user['branch_id'], $reconciliationId, $user['user_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBankAccounts($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $result = $this->service->getBankAccounts($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function createBankAccount($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createBankAccount($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success(['bank_account_id' => $result['bank_account_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
