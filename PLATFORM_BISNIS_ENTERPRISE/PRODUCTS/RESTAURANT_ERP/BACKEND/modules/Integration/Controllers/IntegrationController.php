<?php

if (!class_exists('IntegrationService')) {
    require_once __DIR__ . '/../Services/IntegrationService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';




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

        // $permissionMiddleware = new PermissionMiddleware();

        $integrationType = $request['params']['type'] ?? null;
        $data = $request['body'] ?? [];

        if (!$integrationType) {
            Response::error(Messages::INTEGRATION_TYPE_REQUIRED);
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
            Response::error(Messages::INTEGRATION_TYPE_REQUIRED);
            return;
        }

        $result = $this->service->getIntegrationSettings($user['tenant_id'], $user['branch_id'], $integrationType);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function testConnection($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $integrationType = $request['params']['type'] ?? null;

        if (!$integrationType) {
            Response::error(Messages::INTEGRATION_TYPE_REQUIRED);
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
            Response::error(Messages::INTEGRATION_EXTERNAL_ID_REQUIRED);
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

        Response::success(Messages::INTEGRATION_LOGS_RETRIEVED, $logs);
    }
}
