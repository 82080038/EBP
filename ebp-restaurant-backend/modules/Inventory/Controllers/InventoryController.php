<?php

require_once __DIR__ . '/../Services/InventoryService.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
require_once __DIR__ . '/../../../core/Response.php';

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
            return Response::error('Inventory item not found', 404);
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
            return Response::error('Branch ID is required', 400);
        }
        if (empty($data['product_id'])) {
            return Response::error('Product ID is required', 400);
        }

        $result = $this->inventoryService->createInventory($tenantId, $data);

        if ($result) {
            return Response::success(['message' => 'Inventory created successfully']);
        }

        return Response::error('Failed to create inventory or inventory already exists for this product', 500);
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
            return Response::error('Inventory ID is required', 400);
        }

        $result = $this->inventoryService->updateInventory($tenantId, $inventoryId, $data);

        if ($result) {
            return Response::success(['message' => 'Inventory updated successfully']);
        }

        return Response::error('Failed to update inventory', 500);
    }

    public function adjustStock(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['branch_id'])) {
            return Response::error('Branch ID is required', 400);
        }
        if (empty($data['product_id'])) {
            return Response::error('Product ID is required', 400);
        }
        if (empty($data['quantity'])) {
            return Response::error('Quantity is required', 400);
        }
        if (empty($data['type'])) {
            return Response::error('Type is required (IN, OUT, or ADJUSTMENT)', 400);
        }

        $validTypes = ['IN', 'OUT', 'ADJUSTMENT'];
        if (!in_array($data['type'], $validTypes)) {
            return Response::error('Invalid type. Must be IN, OUT, or ADJUSTMENT', 400);
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
            return Response::success(['message' => 'Stock adjusted successfully']);
        }

        return Response::error('Failed to adjust stock or insufficient stock', 500);
    }

    public function deleteInventory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'INVENTORY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $inventoryId = $request['inventory_id'] ?? 0;

        // Validation
        if (empty($inventoryId)) {
            return Response::error('Inventory ID is required', 400);
        }

        $result = $this->inventoryService->deleteInventory($tenantId, $inventoryId);

        if ($result) {
            return Response::success(['message' => 'Inventory deleted successfully']);
        }

        return Response::error('Failed to delete inventory', 500);
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
