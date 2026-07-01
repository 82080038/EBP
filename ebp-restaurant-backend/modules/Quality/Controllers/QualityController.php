<?php

require_once __DIR__ . '/../Services/QualityService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class QualityController
{
    private $service;

    public function __construct()
    {
        $this->service = new QualityService();
    }

    public function createCheck($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'QUALITY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createQualityCheck($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['check_id' => $result['check_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function createIncident($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'QUALITY_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createIncident($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['incident_id' => $result['incident_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function resolveIncident($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'QUALITY_MANAGE');

        $incidentId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->resolveIncident($incidentId, $data['resolution_notes'], $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getChecks($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getQualityChecks($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getIncidents($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getIncidents($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
