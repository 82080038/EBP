<?php

require_once __DIR__ . '/../Services/DynamicPricingService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class DynamicPricingController
{
    private $service;

    public function __construct()
    {
        $this->service = new DynamicPricingService();
    }

    public function generatePricing($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SETTINGS_MANAGE');

        $productId = $request['params']['product_id'] ?? null;

        $result = $this->service->generateDynamicPricing($user['tenant_id'], $user['branch_id'], $productId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
