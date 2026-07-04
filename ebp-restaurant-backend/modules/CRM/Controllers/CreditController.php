<?php

if (!class_exists('CreditService')) {
    require_once __DIR__ . '/../Services/CreditService.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}
if (!class_exists('Messages')) {
    require_once __DIR__ . '/../../../core/Messages.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}

class CreditController
{
    private $service;

    public function __construct()
    {
        $this->service = new CreditService();
    }

    public function createCredit($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'CRM_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createCredit($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['credit_id' => $result['credit_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function payCredit($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'CRM_MANAGE');

        $creditId = $request['params']['id'] ?? null;
        $amount = $request['body']['amount'] ?? null;

        if (!$creditId || !$amount) {
            Response::error(Messages::CRM_CREDIT_ID_REQUIRED);
            return;
        }

        $result = $this->service->payCredit($creditId, $amount, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCustomerCredits($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $customerId = $request['params']['customer_id'] ?? null;

        if (!$customerId) {
            Response::error(Messages::CRM_CUSTOMER_ID_REQUIRED);
            return;
        }

        $result = $this->service->getCustomerCredits($user['tenant_id'], $user['branch_id'], $customerId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getOverdueCredits($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getOverdueCredits($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
