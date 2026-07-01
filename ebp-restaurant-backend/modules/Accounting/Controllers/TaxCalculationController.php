<?php

require_once __DIR__ . '/../Services/TaxCalculationService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class TaxCalculationController
{
    private $service;

    public function __construct()
    {
        $this->service = new TaxCalculationService();
    }

    public function calculateOrderTax($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_MANAGE');

        $orderId = $request['params']['id'] ?? null;

        if (!$orderId) {
            Response::error('Order ID is required');
            return;
        }

        $result = $this->service->calculateOrderTax($orderId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function calculateMonthlyTax($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_MANAGE');

        $year = $request['params']['year'] ?? date('Y');
        $month = $request['params']['month'] ?? date('m');

        $result = $this->service->calculateMonthlyTax($user['tenant_id'], $user['branch_id'], $year, $month);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function saveTaxRate($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SETTINGS_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->saveTaxRate($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['tax_rate_id' => $result['tax_rate_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getTaxRate($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getTaxRate($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function generateTaxReport($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_MANAGE');

        $year = $request['params']['year'] ?? date('Y');
        $month = $request['params']['month'] ?? date('m');

        $result = $this->service->generateTaxReport($user['tenant_id'], $user['branch_id'], $year, $month);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
