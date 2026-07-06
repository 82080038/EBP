<?php

if (!class_exists('AccountsReceivableService')) {
    require_once __DIR__ . '/../Services/AccountsReceivableService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class AccountsReceivableController
{
    private $service;

    public function __construct()
    {
        $this->service = new AccountsReceivableService();
    }

    public function createInvoice($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_CREATE');

        $data = $request['body'] ?? [];

        $result = $this->service->createInvoice($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success(['ar_id' => $result['ar_id'], 'invoice_number' => $result['invoice_number']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getInvoices($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['query'] ?? [];
        $status = $params['status'] ?? null;
        $customerId = $params['customer_id'] ?? null;

        $result = $this->service->getInvoices($user['tenant_id'], $user['branch_id'], $status, $customerId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getInvoice($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $arId = $params['id'];

        if (!$arId) {
            Response::error('Invoice ID is required');
        }

        $result = $this->service->getInvoice($user['tenant_id'], $user['branch_id'], $arId);

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
        // $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_CREATE');

        $data = $request['body'] ?? [];
        $arId = $data['ar_id'] ?? null;

        if (!$arId) {
            Response::error('Invoice ID is required');
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
