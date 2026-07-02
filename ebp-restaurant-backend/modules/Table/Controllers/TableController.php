<?php

if (!class_exists('TableService')) {
    require_once __DIR__ . '/../Services/TableService.php';
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

class TableController
{
    private $tableService;

    public function __construct()
    {
        $this->tableService = new TableService();
    }

    public function getTables(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'TABLE_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $tables = $this->tableService->getAllTables($tenantId, $branchId);

        return Response::success($tables);
    }

    public function getAvailableTables(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'TABLE_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $tables = $this->tableService->getAvailableTables($tenantId, $branchId);

        return Response::success($tables);
    }

    public function getTable(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'TABLE_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $tableId = $request['table_id'] ?? 0;

        $table = $this->tableService->getTable($tenantId, $tableId);

        if (!$table) {
            return Response::error('Table not found', 404);
        }

        return Response::success($table);
    }

    public function createTable(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'TABLE_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['branch_id'])) {
            return Response::error('Branch ID is required', 400);
        }
        if (empty($data['table_number'])) {
            return Response::error('Table number is required', 400);
        }

        $result = $this->tableService->createTable($tenantId, $data);

        if ($result) {
            return Response::success(['message' => 'Table created successfully']);
        }

        return Response::error('Failed to create table or table number already exists', 500);
    }

    public function updateTable(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'TABLE_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $tableId = $request['table_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($tableId)) {
            return Response::error('Table ID is required', 400);
        }

        $result = $this->tableService->updateTable($tenantId, $tableId, $data);

        if ($result) {
            return Response::success(['message' => 'Table updated successfully']);
        }

        return Response::error('Failed to update table', 500);
    }

    public function updateTableStatus(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'TABLE_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $tableId = $request['table_id'] ?? 0;
        $status = $request['body']['status'] ?? '';

        // Validation
        if (empty($tableId)) {
            return Response::error('Table ID is required', 400);
        }
        if (empty($status)) {
            return Response::error('Status is required', 400);
        }

        $validStatuses = ['AVAILABLE', 'OCCUPIED', 'RESERVED', 'CLEANING'];
        if (!in_array($status, $validStatuses)) {
            return Response::error('Invalid status', 400);
        }

        $result = $this->tableService->updateTableStatus($tenantId, $tableId, $status);

        if ($result) {
            return Response::success(['message' => 'Table status updated successfully']);
        }

        return Response::error('Failed to update table status', 500);
    }

    public function deleteTable(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'TABLE_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $tableId = $request['table_id'] ?? 0;

        // Validation
        if (empty($tableId)) {
            return Response::error('Table ID is required', 400);
        }

        $result = $this->tableService->deleteTable($tenantId, $tableId);

        if ($result) {
            return Response::success(['message' => 'Table deleted successfully']);
        }

        return Response::error('Failed to delete table', 500);
    }
}
