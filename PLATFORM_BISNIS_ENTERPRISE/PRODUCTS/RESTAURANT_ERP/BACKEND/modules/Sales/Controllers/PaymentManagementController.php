<?php

if (!class_exists('PaymentManagementService')) {
    require_once __DIR__ . '/../Services/PaymentManagementService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class PaymentManagementController
{
    private $service;

    public function __construct()
    {
        $this->service = new PaymentManagementService();
    }

    public function createCreditNote($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SALES_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createCreditNote($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['credit_note_id' => $result['credit_note_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function createVoucher($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SALES_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createVoucher($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['voucher_id' => $result['voucher_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function applyVoucher($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $data = $request['body'] ?? [];
        $voucherCode = $data['voucher_code'] ?? null;
        $orderAmount = $data['order_amount'] ?? 0;

        $result = $this->service->applyVoucher($voucherCode, $orderAmount, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function openCashDrawer($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SALES_MANAGE');

        $drawerId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->openCashDrawer($drawerId, $data['opening_balance'] ?? 0, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function closeCashDrawer($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SALES_MANAGE');

        $drawerId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->closeCashDrawer($drawerId, $data['expected_amount'] ?? 0, $data['actual_amount'] ?? 0, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
