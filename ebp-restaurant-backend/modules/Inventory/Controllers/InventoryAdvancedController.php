<?php

require_once __DIR__ . '/../Services/InventoryAdvancedService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class InventoryAdvancedController
{
    private $service;

    public function __construct()
    {
        $this->service = new InventoryAdvancedService();
    }

    public function repurposeStock($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->repurposeStock($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['repurposing_id' => $result['repurposing_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function zeroCostStockIn($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->zeroCostStockIn($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function createStockTransfer($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createStockTransfer($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['transfer_id' => $result['transfer_id'], 'transfer_number' => $result['transfer_number']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function receiveStockTransfer($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INVENTORY_MANAGE');

        $transferId = $request['params']['id'] ?? null;

        if (!$transferId) {
            Response::error('Transfer ID is required');
            return;
        }

        $result = $this->service->receiveStockTransfer($transferId, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getStockTransfers($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $status = $request['params']['status'] ?? null;

        $result = $this->service->getStockTransfers($user['tenant_id'], $user['branch_id'], $status);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getRepurposingHistory($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $dateFrom = $request['params']['start_date'] ?? null;
        $dateTo = $request['params']['end_date'] ?? null;

        $result = $this->service->getRepurposingHistory($user['tenant_id'], $user['branch_id'], $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
