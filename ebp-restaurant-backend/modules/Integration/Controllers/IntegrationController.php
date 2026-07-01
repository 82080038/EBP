<?php

require_once __DIR__ . '/../Services/IntegrationService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class IntegrationController
{
    private $service;

    public function __construct()
    {
        $this->service = new IntegrationService();
    }

    public function saveSettings($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_MANAGE');

        $integrationType = $request['params']['type'] ?? null;
        $data = $request['body'] ?? [];

        if (!$integrationType) {
            Response::error('Integration type is required');
            return;
        }

        $result = $this->service->saveIntegrationSettings($user['tenant_id'], $user['branch_id'], $integrationType, $data);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getSettings($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $integrationType = $request['params']['type'] ?? null;

        if (!$integrationType) {
            Response::error('Integration type is required');
            return;
        }

        $result = $this->service->getIntegrationSettings($user['tenant_id'], $user['branch_id'], $integrationType);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function testConnection($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_MANAGE');

        $integrationType = $request['params']['type'] ?? null;

        if (!$integrationType) {
            Response::error('Integration type is required');
            return;
        }

        $result = $this->service->testConnection($user['tenant_id'], $user['branch_id'], $integrationType);

        if ($result['success']) {
            Response::success($result['message'], $result['data'] ?? null);
        } else {
            Response::error($result['message']);
        }
    }

    public function syncOrder($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $integrationType = $request['params']['type'] ?? null;
        $externalOrderId = $request['body']['external_order_id'] ?? null;

        if (!$integrationType || !$externalOrderId) {
            Response::error('Integration type and external order ID are required');
            return;
        }

        $result = $this->service->syncOrder($user['tenant_id'], $user['branch_id'], $integrationType, $externalOrderId);

        if ($result['success']) {
            Response::success($result['message'], $result['data'] ?? null);
        } else {
            Response::error($result['message']);
        }
    }

    public function getLogs($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $integrationType = $request['params']['type'] ?? null;
        $limit = $request['params']['limit'] ?? 100;

        $logs = $this->service->repository->getLogs($user['tenant_id'], $user['branch_id'], $integrationType, $limit);

        Response::success('Logs retrieved successfully', $logs);
    }
}
