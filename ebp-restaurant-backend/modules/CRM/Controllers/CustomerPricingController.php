<?php

require_once __DIR__ . '/../Services/CustomerPricingService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class CustomerPricingController
{
    private $service;

    public function __construct()
    {
        $this->service = new CustomerPricingService();
    }

    public function setCustomerPrice($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'CRM_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->setCustomerPrice($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['pricing_id' => $result['pricing_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCustomerPrice($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $customerId = $request['params']['customer_id'] ?? null;
        $productId = $request['params']['product_id'] ?? null;

        if (!$customerId || !$productId) {
            Response::error('Customer ID and product ID are required');
            return;
        }

        $result = $this->service->getCustomerPrice($user['tenant_id'], $user['branch_id'], $customerId, $productId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCustomerPricings($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $customerId = $request['params']['customer_id'] ?? null;

        if (!$customerId) {
            Response::error('Customer ID is required');
            return;
        }

        $result = $this->service->getCustomerPricings($user['tenant_id'], $user['branch_id'], $customerId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
