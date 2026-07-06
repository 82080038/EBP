<?php

if (!class_exists('CustomerAdvancedService')) {
    require_once __DIR__ . '/../Services/CustomerAdvancedService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';




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

        // $permissionMiddleware = new PermissionMiddleware();

        $customerId = $request['params']['customer_id'] ?? null;
        $productId = $request['body']['product_id'] ?? null;

        if (!$customerId || !$productId) {
            Response::error(Messages::CRM_CUSTOMER_ID_REQUIRED);
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
            Response::error(Messages::CRM_CUSTOMER_ID_REQUIRED);
            return;
        }

        $result = $this->service->getCustomerFavorites($user['tenant_id'], $user['branch_id'], $customerId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
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
            Response::error(Messages::CRM_CUSTOMER_ID_REQUIRED);
            return;
        }

        $result = $this->service->getCustomerHabitAnalysis($user['tenant_id'], $user['branch_id'], $customerId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function createBirthdayPromotion($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

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
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function useBirthdayPromotion($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $promotionId = $request['params']['id'] ?? null;

        if (!$promotionId) {
            Response::error(Messages::CRM_PROMOTION_ID_REQUIRED);
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
