<?php

if (!class_exists('DynamicPricingService')) {
    require_once __DIR__ . '/../Services/DynamicPricingService.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}

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
