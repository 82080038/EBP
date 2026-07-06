<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

if (!class_exists('CustomModuleService')) {
    require_once __DIR__ . '/CustomModuleService.php';
}

class CustomModuleController
{
    private $service;

    public function __construct()
    {
        $this->service = new CustomModuleService();
    }

    /**
     * Create custom module
     */
    public function createModule($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'MODULE_MANAGE');

        $data = $request['body'] ?? [];

        if (empty($data['module_code']) || empty($data['module_name'])) {
            Response::error('Module code and module name are required');
        }

        $result = $this->service->createCustomModule($user['tenant_id'], $data, $user['user_id']);

        if ($result['success']) {
            Response::success(['custom_module_id' => $result['custom_module_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get custom modules
     */
    public function getCustomModules($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $modules = $this->service->getCustomModules($user['tenant_id']);
        Response::success($modules, 'Custom modules retrieved');
    }

    /**
     * Get all modules (system + custom)
     */
    public function getAllModules($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $modules = $this->service->getAllModules($user['tenant_id']);
        Response::success($modules, 'All modules retrieved');
    }

    /**
     * Update custom module
     */
    public function updateModule($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'MODULE_MANAGE');

        $customModuleId = $request['params']['custom_module_id'] ?? null;
        $data = $request['body'] ?? [];

        if (!$customModuleId) {
            Response::error('Custom module ID is required');
        }

        $result = $this->service->updateCustomModule($customModuleId, $data);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Delete custom module
     */
    public function deleteModule($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'MODULE_MANAGE');

        $customModuleId = $request['params']['custom_module_id'] ?? null;

        if (!$customModuleId) {
            Response::error('Custom module ID is required');
        }

        $result = $this->service->deleteCustomModule($customModuleId);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Create custom permission
     */
    public function createPermission($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'PERMISSION_MANAGE');

        $data = $request['body'] ?? [];

        if (empty($data['permission_code']) || empty($data['permission_name'])) {
            Response::error('Permission code and permission name are required');
        }

        $result = $this->service->createCustomPermission($user['tenant_id'], $data);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get custom permissions
     */
    public function getCustomPermissions($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $moduleCode = $query['module_code'] ?? null;

        $permissions = $this->service->getCustomPermissions($user['tenant_id'], $moduleCode);
        Response::success($permissions, 'Custom permissions retrieved');
    }

    /**
     * Get all permissions (system + custom)
     */
    public function getAllPermissions($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $moduleCode = $query['module_code'] ?? null;

        $permissions = $this->service->getAllPermissions($user['tenant_id'], $moduleCode);
        Response::success($permissions, 'All permissions retrieved');
    }

    /**
     * Assign module to role
     */
    public function assignToRole($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ROLE_MANAGE');

        $data = $request['body'] ?? [];
        $roleCode = $data['role_code'] ?? null;
        $moduleCode = $data['module_code'] ?? null;
        $isEnabled = $data['is_enabled'] ?? true;

        if (!$roleCode || !$moduleCode) {
            Response::error('Role code and module code are required');
        }

        $result = $this->service->assignModuleToRole($user['tenant_id'], $roleCode, $moduleCode, $isEnabled);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
