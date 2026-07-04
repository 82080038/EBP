<?php

if (!class_exists('IntegrationService')) {
    require_once __DIR__ . '/../Services/IntegrationService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class DataIntegrationController
{
    private $service;

    public function __construct()
    {
        $this->service = new IntegrationService();
    }

    // Data Integration Layer Methods

    public function getExternalSystems($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_VIEW');

        $systems = $this->service->getExternalSystems($user['tenant_id']);

        Response::success('External systems retrieved', $systems);
    }

    public function addExternalSystem($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->addExternalSystem($user['tenant_id'], $data);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateExternalSystem($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_MANAGE');

        $systemId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        if (!$systemId) {
            Response::error('System ID is required');
            return;
        }

        $result = $this->service->updateExternalSystem($user['tenant_id'], $systemId, $data);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function deleteExternalSystem($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_MANAGE');

        $systemId = $request['params']['id'] ?? null;

        if (!$systemId) {
            Response::error('System ID is required');
            return;
        }

        $result = $this->service->deleteExternalSystem($user['tenant_id'], $systemId);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function triggerSync($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_MANAGE');

        $systemId = $request['body']['system_id'] ?? null;
        $syncType = $request['body']['sync_type'] ?? 'full';

        if (!$systemId) {
            Response::error('System ID is required');
            return;
        }

        $result = $this->service->triggerSync($user['tenant_id'], $systemId, $syncType, $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getSyncLogs($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_VIEW');

        $systemId = $request['params']['system_id'] ?? null;
        $page = $request['params']['page'] ?? 1;
        $limit = $request['params']['limit'] ?? 20;

        $result = $this->service->getSyncLogs($user['tenant_id'], $systemId, $page, $limit);

        Response::success('Sync logs retrieved', $result);
    }

    public function getDataMappings($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_VIEW');

        $systemId = $request['params']['system_id'] ?? null;
        $mappingType = $request['params']['type'] ?? null;

        $mappings = $this->service->getDataMappings($user['tenant_id'], $systemId, $mappingType);

        Response::success('Data mappings retrieved', $mappings);
    }

    public function addDataMapping($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->addDataMapping($user['tenant_id'], $data);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getMonitoringData($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'INTEGRATION_VIEW');

        $systemId = $request['params']['system_id'] ?? null;
        $metricType = $request['params']['metric_type'] ?? null;
        $dateFrom = $request['params']['date_from'] ?? null;
        $dateTo = $request['params']['date_to'] ?? null;

        $data = $this->service->getMonitoringData($user['tenant_id'], $systemId, $metricType, $dateFrom, $dateTo);

        Response::success('Monitoring data retrieved', $data);
    }

    public function handleWebhook($request)
    {
        // Webhook endpoint - no authentication required (verified via signature)
        $systemId = $request['params']['system_id'] ?? null;
        $eventType = $request['headers']['X-Event-Type'] ?? null;
        $signature = $request['headers']['X-Signature'] ?? null;
        $payload = $request['body'] ?? [];

        if (!$systemId || !$eventType) {
            Response::error('Invalid webhook request', null, 400);
            return;
        }

        $result = $this->service->handleWebhook($systemId, $eventType, $signature, $payload);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message'], null, 400);
        }
    }
}
