<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

if (!class_exists('CustomRoleService')) {
    require_once __DIR__ . '/CustomRoleService.php';
}

class CustomRoleController
{
    private $service;

    public function __construct()
    {
        $this->service = new CustomRoleService();
    }

    /**
     * Create role from template
     */
    public function createFromTemplate($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ROLE_MANAGE');

        $data = $request['body'] ?? [];
        $templateCode = $data['template_code'] ?? null;
        $roleName = $data['role_name'] ?? null;
        $roleCode = $data['role_code'] ?? null;

        if (!$templateCode || !$roleName) {
            Response::error('Template code and role name are required');
        }

        $result = $this->service->createRoleFromTemplate($user['tenant_id'], $templateCode, $roleName, $roleCode);

        if ($result['success']) {
            Response::success(['role_id' => $result['role_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Create custom role
     */
    public function createCustom($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ROLE_MANAGE');

        $data = $request['body'] ?? [];
        $roleData = $data['role'] ?? [];
        $permissions = $data['permissions'] ?? [];
        $modules = $data['modules'] ?? [];

        if (empty($roleData['role_code']) || empty($roleData['role_name'])) {
            Response::error('Role code and role name are required');
        }

        $result = $this->service->createCustomRole($user['tenant_id'], $roleData, $permissions, $modules);

        if ($result['success']) {
            Response::success(['role_id' => $result['role_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get role templates
     */
    public function getTemplates($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $templates = $this->service->getRoleTemplates();
        Response::success($templates, 'Role templates retrieved');
    }

    /**
     * Get template details
     */
    public function getTemplateDetails($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $templateCode = $query['template_code'] ?? null;

        if (!$templateCode) {
            Response::error('Template code is required');
        }

        $template = $this->service->getTemplateDetails($templateCode);

        if ($template) {
            Response::success($template, 'Template details retrieved');
        } else {
            Response::error('Template not found');
        }
    }

    /**
     * Clone role
     */
    public function cloneRole($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ROLE_MANAGE');

        $data = $request['body'] ?? [];
        $sourceRoleId = $data['source_role_id'] ?? null;
        $newRoleName = $data['new_role_name'] ?? null;
        $newRoleCode = $data['new_role_code'] ?? null;

        if (!$sourceRoleId || !$newRoleName) {
            Response::error('Source role ID and new role name are required');
        }

        $result = $this->service->cloneRole($user['tenant_id'], $sourceRoleId, $newRoleName, $newRoleCode);

        if ($result['success']) {
            Response::success(['role_id' => $result['role_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
