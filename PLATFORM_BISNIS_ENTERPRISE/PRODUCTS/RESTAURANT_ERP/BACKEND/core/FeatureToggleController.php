<?php

if (!class_exists('FeatureToggleService')) {
    require_once __DIR__ . '/FeatureToggleService.php';
}

class FeatureToggleController
{
    private $service;

    public function __construct()
    {
        $this->service = new FeatureToggleService();
    }

    /**
     * Get all available modules
     */
    public function getAllModules($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $modules = $this->service->getAllModules();
        Response::success($modules, 'Modules retrieved successfully');
    }

    /**
     * Get features for current user
     */
    public function getUserFeatures($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $features = $this->service->getUserFeatures($user['user_id']);
        Response::success($features, 'User features retrieved successfully');
    }

    /**
     * Get features for specific user (admin only)
     */
    public function getUserFeaturesById($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'USER_MANAGE');

        $userId = $request['params']['user_id'] ?? null;
        if (!$userId) {
            Response::error('User ID is required');
        }

        $features = $this->service->getUserFeatures($userId);
        Response::success($features, 'User features retrieved successfully');
    }

    /**
     * Get features for role
     */
    public function getRoleFeatures($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $roleId = $request['params']['role_id'] ?? null;
        if (!$roleId) {
            Response::error('Role ID is required');
        }

        $features = $this->service->getRoleFeatures($roleId);
        Response::success($features, 'Role features retrieved successfully');
    }

    /**
     * Enable feature for user
     */
    public function enableFeatureForUser($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'USER_MANAGE');

        $data = $request['body'] ?? [];
        $userId = $data['user_id'] ?? null;
        $moduleCode = $data['module_code'] ?? null;
        $expiresAt = $data['expires_at'] ?? null;

        if (!$userId || !$moduleCode) {
            Response::error('User ID and module code are required');
        }

        $result = $this->service->enableFeatureForUser($userId, $moduleCode, $expiresAt);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Disable feature for user
     */
    public function disableFeatureForUser($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'USER_MANAGE');

        $data = $request['body'] ?? [];
        $userId = $data['user_id'] ?? null;
        $moduleCode = $data['module_code'] ?? null;

        if (!$userId || !$moduleCode) {
            Response::error('User ID and module code are required');
        }

        $result = $this->service->disableFeatureForUser($userId, $moduleCode);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Enable feature for role
     */
    public function enableFeatureForRole($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ROLE_MANAGE');

        $data = $request['body'] ?? [];
        $roleId = $data['role_id'] ?? null;
        $moduleCode = $data['module_code'] ?? null;
        $expiresAt = $data['expires_at'] ?? null;

        if (!$roleId || !$moduleCode) {
            Response::error('Role ID and module code are required');
        }

        $result = $this->service->enableFeatureForRole($roleId, $moduleCode, $expiresAt);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Disable feature for role
     */
    public function disableFeatureForRole($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ROLE_MANAGE');

        $data = $request['body'] ?? [];
        $roleId = $data['role_id'] ?? null;
        $moduleCode = $data['module_code'] ?? null;

        if (!$roleId || !$moduleCode) {
            Response::error('Role ID and module code are required');
        }

        $result = $this->service->disableFeatureForRole($roleId, $moduleCode);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Check if feature is enabled for current user
     */
    public function checkFeature($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $moduleCode = $request['params']['module_code'] ?? null;
        if (!$moduleCode) {
            Response::error('Module code is required');
        }

        $isEnabled = $this->service->isFeatureEnabledForUser($user['user_id'], $moduleCode);
        Response::success(['enabled' => $isEnabled], 'Feature status checked');
    }
}
