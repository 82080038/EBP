<?php

if (!class_exists('AccountsPayableService')) {
    require_once __DIR__ . '/../Services/AccountsPayableService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class AccountsPayableController
{
    private $service;

    public function __construct()
    {
        $this->service = new AccountsPayableService();
    }

    public function createBill($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_CREATE');

        $data = $request['body'] ?? [];

        $result = $this->service->createBill($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success(['ap_id' => $result['ap_id'], 'bill_number' => $result['bill_number']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBills($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['query'] ?? [];
        $status = $params['status'] ?? null;
        $supplierId = $params['supplier_id'] ?? null;

        $result = $this->service->getBills($user['tenant_id'], $user['branch_id'], $status, $supplierId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBill($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $apId = $params['id'];

        if (!$apId) {
            Response::error('Bill ID is required');
        }

        $result = $this->service->getBill($user['tenant_id'], $user['branch_id'], $apId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function addPayment($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];
        $apId = $data['ap_id'] ?? null;

        if (!$apId) {
            Response::error('Bill ID is required');
        }

        $result = $this->service->addPayment($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAgingReport($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $result = $this->service->getAgingReport($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
