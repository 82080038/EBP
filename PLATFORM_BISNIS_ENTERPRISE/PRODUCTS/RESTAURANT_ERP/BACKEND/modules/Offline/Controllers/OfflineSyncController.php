<?php

if (!class_exists('OfflineSyncService')) {
    require_once __DIR__ . '/../Services/OfflineSyncService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class OfflineSyncController
{
    private $service;

    public function __construct()
    {
        $this->service = new OfflineSyncService();
    }

    public function queueOperation($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $data = $request['body'] ?? [];

        $result = $this->service->queueOperation($user['tenant_id'], $user['branch_id'], $user['user_id'], $data['operation_type'], $data['entity_type'], $data['entity_data']);

        if ($result['success']) {
            Response::success($result['message'], ['sync_id' => $result['sync_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function syncPending($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->syncPendingOperations($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result);
        } else {
            Response::error($result['message']);
        }
    }

    public function resolveConflict($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $syncId = $request['params']['id'] ?? null;
        $resolution = $request['body']['resolution'] ?? null;
        $resolvedData = $request['body']['resolved_data'] ?? null;

        if (!$syncId || !$resolution) {
            Response::error('Sync ID and resolution are required');
            return;
        }

        $result = $this->service->resolveConflict($syncId, $resolution, $resolvedData);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getSyncStatus($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getSyncStatus($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getConflicts($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getConflicts($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
