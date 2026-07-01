<?php

require_once __DIR__ . '/../Services/OrderService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class OrderController
{
    private $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    public function create()
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ORDER_CREATE');

        $input = json_decode(file_get_contents("php://input"), true);

        $result = $this->service->createOrder(
            $input,
            $user['user_id'],
            $user['tenant_id'],
            $user['branch_id']
        );

        if ($result['success']) {
            Response::success(
                $result['message'],
                [
                    'order_id' => $result['order_id'],
                    'total' => $result['total']
                ]
            );
        } else {
            Response::error($result['message']);
        }
    }

    public function update($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ORDER_UPDATE');

        $orderId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->updateOrder($orderId, $data, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function close($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ORDER_UPDATE');

        $orderId = $request['params']['id'] ?? null;

        $result = $this->service->closeOrder($orderId, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function hold($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ORDER_UPDATE');

        $orderId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $reason = $data['reason'] ?? '';

        $result = $this->service->holdOrder($orderId, $reason, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function recall($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ORDER_UPDATE');

        $orderId = $request['params']['id'] ?? null;

        $result = $this->service->recallOrder($orderId, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function setPriority($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ORDER_UPDATE');

        $orderId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $isPriority = $data['is_priority'] ?? false;

        $result = $this->service->setPriorityOrder($orderId, $isPriority, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function splitBill($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ORDER_UPDATE');

        $orderId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $splitType = $data['split_type'] ?? 'CUSTOM';
        $totalSplits = $data['total_splits'] ?? 1;
        $splitData = $data['split_data'] ?? [];

        $result = $this->service->splitBill($orderId, $splitType, $totalSplits, $splitData, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['split_bill_id' => $result['split_bill_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function addPayment($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ORDER_UPDATE');

        $orderId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $paymentMethod = $data['payment_method'] ?? 'CASH';
        $amount = $data['amount'] ?? 0;
        $referenceNumber = $data['reference_number'] ?? null;

        $result = $this->service->addPayment($orderId, $paymentMethod, $amount, $referenceNumber, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['payment_id' => $result['payment_id']]);
        } else {
            Response::error($result['message']);
        }
    }
}
