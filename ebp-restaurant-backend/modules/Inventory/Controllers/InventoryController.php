<?php

if (!class_exists('InventoryService')) {
    require_once __DIR__ . '/../Services/InventoryService.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}
if (!class_exists('Messages')) {
    require_once __DIR__ . '/../../../core/Messages.php';
}

class InventoryController
{
    private $inventoryService;

    public function __construct()
    {
        $this->inventoryService = new InventoryService();
    }

    public function getInventory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $inventory = $this->inventoryService->getAllInventory($tenantId, $branchId);

        return Response::success($inventory);
    }

    public function getLowStock(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $inventory = $this->inventoryService->getLowStock($tenantId, $branchId);

        return Response::success($inventory);
    }

    public function getInventoryItem(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $inventoryId = $request['inventory_id'] ?? 0;

        $inventory = $this->inventoryService->getInventory($tenantId, $inventoryId);

        if (!$inventory) {
            return Response::error(Messages::INVENTORY_NOT_FOUND, 404);
        }

        return Response::success($inventory);
    }

    public function createInventory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['branch_id'])) {
            return Response::error(Messages::INVENTORY_BRANCH_REQUIRED, 400);
        }
        if (empty($data['product_id'])) {
            return Response::error(Messages::INVENTORY_PRODUCT_REQUIRED, 400);
        }

        $result = $this->inventoryService->createInventory($tenantId, $data);

        if ($result) {
            return Response::success(['message' => Messages::INVENTORY_CREATED]);
        }

        return Response::error(Messages::INVENTORY_FAILED_CREATE, 500);
    }

    public function updateInventory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $inventoryId = $request['inventory_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($inventoryId)) {
            return Response::error(Messages::INVENTORY_ID_REQUIRED, 400);
        }

        $result = $this->inventoryService->updateInventory($tenantId, $inventoryId, $data);

        if ($result) {
            return Response::success(['message' => Messages::INVENTORY_UPDATED]);
        }

        return Response::error(Messages::INVENTORY_FAILED_UPDATE, 500);
    }

    public function adjustStock(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['branch_id'])) {
            return Response::error(Messages::INVENTORY_BRANCH_REQUIRED, 400);
        }
        if (empty($data['product_id'])) {
            return Response::error(Messages::INVENTORY_PRODUCT_REQUIRED, 400);
        }
        if (empty($data['quantity'])) {
            return Response::error(Messages::INVENTORY_QUANTITY_REQUIRED, 400);
        }
        if (empty($data['type'])) {
            return Response::error(Messages::INVENTORY_TYPE_REQUIRED, 400);
        }

        $validTypes = ['IN', 'OUT', 'ADJUSTMENT'];
        if (!in_array($data['type'], $validTypes)) {
            return Response::error(Messages::VALIDATION_INVALID, 400);
        }

        $reference = [
            'type' => $data['reference_type'] ?? null,
            'id' => $data['reference_id'] ?? null,
            'notes' => $data['notes'] ?? null
        ];

        $result = $this->inventoryService->adjustStock(
            $tenantId,
            $data['branch_id'],
            $data['product_id'],
            $data['quantity'],
            $data['type'],
            $reference
        );

        if ($result) {
            return Response::success(['message' => Messages::INVENTORY_ADJUSTED]);
        }

        return Response::error(Messages::INVENTORY_INSUFFICIENT, 500);
    }

    public function deleteInventory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $inventoryId = $request['inventory_id'] ?? 0;

        // Validation
        if (empty($inventoryId)) {
            return Response::error(Messages::INVENTORY_ID_REQUIRED, 400);
        }

        $result = $this->inventoryService->deleteInventory($tenantId, $inventoryId);

        if ($result) {
            return Response::success(['message' => Messages::INVENTORY_DELETED]);
        }

        return Response::error(Messages::ERROR_FAILED, 500);
    }

    public function getTransactions(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $productId = $request['product_id'] ?? null;
        $dateFrom = $request['date_from'] ?? null;
        $dateTo = $request['date_to'] ?? null;

        $transactions = $this->inventoryService->getTransactions($tenantId, $branchId, $productId, $dateFrom, $dateTo);

        return Response::success($transactions);
    }
}
