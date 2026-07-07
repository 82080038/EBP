<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('OrderService')) {
    require_once __DIR__ . '/../Services/OrderService.php';
}

class OrderController
{
    private $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    public function create()
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            // $permissionMiddleware = new PermissionMiddleware();

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                Response::error(Messages::VALIDATION_INVALID);
                return;
            }

            $result = $this->service->createOrder(
                $input,
                $user['user_id'],
                $user['tenant_id'],
                $user['branch_id']
            );

            if ($result['success']) {
                Response::success(
                    [
                        'order_id' => $result['order_id'],
                        'total' => $result['total']
                    ],
                    $result['message']
                );
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Order creation failed: ' . $e->getMessage());
        }
    }

    public function getAll($request)
    {
        // Permission checking is now handled in routes
        $params = $request['query'] ?? [];
        $limit = $params['limit'] ?? 50;
        $sort = $params['sort'] ?? 'created_at';
        $order = $params['order'] ?? 'DESC';
        $status = $params['status'] ?? null;

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;

        $result = $this->service->getOrders($tenantId, $branchId, $status, $limit, $sort, $order);

        if ($result['success']) {
            Response::success($result['data'], Messages::SUCCESS_RETRIEVED);
        } else {
            Response::error($result['message']);
        }
    }

    public function get($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $orderId = $request['params']['id'] ?? null;

        $result = $this->service->getOrder($orderId, $user['tenant_id']);

        if ($result['success']) {
            Response::success(Messages::SUCCESS_RETRIEVED, $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function update($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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

        // $permissionMiddleware = new PermissionMiddleware();

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
