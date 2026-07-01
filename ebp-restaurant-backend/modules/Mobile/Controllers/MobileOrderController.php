<?php

require_once __DIR__ . '/../Services/MobileOrderService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

class MobileOrderController
{
    private $service;

    public function __construct()
    {
        $this->service = new MobileOrderService();
    }

    public function getMenu($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getMobileMenu($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getQuickOrder($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $productId = $request['params']['id'] ?? null;

        if (!$productId) {
            Response::error('Product ID is required');
            return;
        }

        $result = $this->service->getQuickOrder($user['tenant_id'], $user['branch_id'], $productId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
