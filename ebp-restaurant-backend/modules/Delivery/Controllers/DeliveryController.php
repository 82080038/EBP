<?php

require_once __DIR__ . '/../Services/DeliveryService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class DeliveryController
{
    private $service;

    public function __construct()
    {
        $this->service = new DeliveryService();
    }

    public function create($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $data = $request['body'] ?? [];

        $result = $this->service->createDeliveryOrder($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['delivery_order_id' => $result['delivery_order_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function assignDriver($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $deliveryOrderId = $request['params']['id'] ?? null;
        $driverId = $request['body']['driver_id'] ?? null;

        $result = $this->service->assignDriver($deliveryOrderId, $driverId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateStatus($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $deliveryOrderId = $request['params']['id'] ?? null;
        $status = $request['body']['status'] ?? null;

        $result = $this->service->updateStatus($deliveryOrderId, $status, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getDeliveryOrders($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
