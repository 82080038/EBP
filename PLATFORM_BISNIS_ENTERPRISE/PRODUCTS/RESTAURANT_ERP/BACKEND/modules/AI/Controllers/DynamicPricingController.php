<?php

if (!class_exists('DynamicPricingService')) {
    require_once __DIR__ . '/../Services/DynamicPricingService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



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

        // $permissionMiddleware = new PermissionMiddleware();

        $productId = $request['params']['product_id'] ?? null;

        $result = $this->service->generateDynamicPricing($user['tenant_id'], $user['branch_id'], $productId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
