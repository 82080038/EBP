<?php

if (!class_exists('TableService')) {
    require_once __DIR__ . '/../Services/TableService.php';
}


// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';


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
            return Response::error(Messages::TABLE_NOT_FOUND, 404);
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
            return Response::error(Messages::TABLE_BRANCH_REQUIRED, 400);
        }
        if (empty($data['table_number'])) {
            return Response::error(Messages::TABLE_NUMBER_REQUIRED, 400);
        }

        $result = $this->tableService->createTable($tenantId, $data);

        if ($result) {
            return Response::success(['message' => Messages::TABLE_CREATED]);
        }

        return Response::error(Messages::TABLE_FAILED_CREATE, 500);
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
            return Response::error(Messages::TABLE_ID_REQUIRED, 400);
        }

        $result = $this->tableService->updateTable($tenantId, $tableId, $data);

        if ($result) {
            return Response::success(['message' => Messages::TABLE_UPDATED]);
        }

        return Response::error(Messages::TABLE_FAILED_UPDATE, 500);
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
            return Response::error(Messages::TABLE_ID_REQUIRED, 400);
        }
        if (empty($status)) {
            return Response::error(Messages::TABLE_STATUS_REQUIRED, 400);
        }

        $validStatuses = ['AVAILABLE', 'OCCUPIED', 'RESERVED', 'CLEANING'];
        if (!in_array($status, $validStatuses)) {
            return Response::error(Messages::VALIDATION_INVALID, 400);
        }

        $result = $this->tableService->updateTableStatus($tenantId, $tableId, $status);

        if ($result) {
            return Response::success(['message' => Messages::TABLE_UPDATED]);
        }

        return Response::error(Messages::TABLE_FAILED_UPDATE, 500);
    }

    public function deleteTable(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'TABLE_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $tableId = $request['table_id'] ?? 0;

        // Validation
        if (empty($tableId)) {
            return Response::error(Messages::TABLE_ID_REQUIRED, 400);
        }

        $result = $this->tableService->deleteTable($tenantId, $tableId);

        if ($result) {
            return Response::success(['message' => Messages::TABLE_DELETED]);
        }

        return Response::error(Messages::TABLE_FAILED_DELETE, 500);
    }
}
