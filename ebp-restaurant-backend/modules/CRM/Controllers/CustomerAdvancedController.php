<?php

require_once __DIR__ . '/../Services/CustomerAdvancedService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class CustomerAdvancedController
{
    private $service;

    public function __construct()
    {
        $this->service = new CustomerAdvancedService();
    }

    public function addFavoriteProduct($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'CRM_MANAGE');

        $customerId = $request['params']['customer_id'] ?? null;
        $productId = $request['body']['product_id'] ?? null;

        if (!$customerId || !$productId) {
            Response::error('Customer ID and product ID are required');
            return;
        }

        $result = $this->service->addFavoriteProduct($customerId, $productId, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCustomerFavorites($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $customerId = $request['params']['customer_id'] ?? null;

        if (!$customerId) {
            Response::error('Customer ID is required');
            return;
        }

        $result = $this->service->getCustomerFavorites($user['tenant_id'], $user['branch_id'], $customerId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCustomerHabitAnalysis($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $customerId = $request['params']['customer_id'] ?? null;

        if (!$customerId) {
            Response::error('Customer ID is required');
            return;
        }

        $result = $this->service->getCustomerHabitAnalysis($user['tenant_id'], $user['branch_id'], $customerId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function createBirthdayPromotion($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'CRM_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createBirthdayPromotion($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['promotion_id' => $result['promotion_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBirthdayPromotions($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $customerId = $request['params']['customer_id'] ?? null;

        $result = $this->service->getBirthdayPromotions($user['tenant_id'], $user['branch_id'], $customerId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function useBirthdayPromotion($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'CRM_MANAGE');

        $promotionId = $request['params']['id'] ?? null;

        if (!$promotionId) {
            Response::error('Promotion ID is required');
            return;
        }

        $result = $this->service->useBirthdayPromotion($promotionId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
