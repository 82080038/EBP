<?php

require_once __DIR__ . '/../Services/CustomerService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class CustomerController
{
    private $service;

    public function __construct()
    {
        $this->service = new CustomerService();
    }

    public function create($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'CRM_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createCustomer($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['customer_id' => $result['customer_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $filters = $request['query'] ?? [];

        $result = $this->service->getCustomers($user['tenant_id'], $filters);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function update($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'CRM_MANAGE');

        $customerId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->updateCustomer($customerId, $data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function addLoyaltyPoints($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'CRM_MANAGE');

        $customerId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->addLoyaltyPoints(
            $customerId,
            $data['points'],
            $data['order_id'] ?? null,
            $data['description'] ?? 'Loyalty points earned',
            $user['tenant_id']
        );

        if ($result['success']) {
            Response::success($result['message'], ['new_points' => $result['new_points']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function recordVisit($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $customerId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->recordCustomerVisit(
            $customerId,
            $data['branch_id'] ?? $user['branch_id'],
            $data['total_spent'] ?? 0,
            $user['tenant_id']
        );

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
