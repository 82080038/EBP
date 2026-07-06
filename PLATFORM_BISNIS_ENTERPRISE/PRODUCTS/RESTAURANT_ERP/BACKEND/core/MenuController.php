<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

if (!class_exists('MenuService')) {
    require_once __DIR__ . '/MenuService.php';
}

class MenuController
{
    private $service;

    public function __construct()
    {
        $this->service = new MenuService();
    }

    /**
     * Get user menu
     */
    public function getUserMenu($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $menu = $this->service->getUserMenu($user['user_id'], $user['tenant_id']);
        Response::success($menu, 'User menu retrieved successfully');
    }

    /**
     * Set role menu
     */
    public function setRoleMenu($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ROLE_MANAGE');

        $data = $request['body'] ?? [];
        $roleId = $data['role_id'] ?? null;
        $menuItems = $data['menu_items'] ?? [];

        if (!$roleId || empty($menuItems)) {
            Response::error('Role ID and menu items are required');
        }

        $result = $this->service->setRoleMenu($roleId, $menuItems);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get role menu
     */
    public function getRoleMenu($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $roleId = $query['role_id'] ?? null;

        if (!$roleId) {
            Response::error('Role ID is required');
        }

        $menu = $this->service->getRoleMenu($roleId);
        Response::success($menu, 'Role menu retrieved successfully');
    }

    /**
     * Copy role menu
     */
    public function copyRoleMenu($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ROLE_MANAGE');

        $data = $request['body'] ?? [];
        $fromRoleId = $data['from_role_id'] ?? null;
        $toRoleId = $data['to_role_id'] ?? null;

        if (!$fromRoleId || !$toRoleId) {
            Response::error('From role ID and to role ID are required');
        }

        $result = $this->service->copyRoleMenu($fromRoleId, $toRoleId);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Check menu access
     */
    public function checkAccess($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $menuCode = $query['menu_code'] ?? null;

        if (!$menuCode) {
            Response::error('Menu code is required');
        }

        $hasAccess = $this->service->hasMenuAccess($user['user_id'], $menuCode);
        Response::success(['has_access' => $hasAccess], 'Menu access checked');
    }

    /**
     * Get role modules
     */
    public function getRoleModules($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $roleId = $query['role_id'] ?? null;

        if (!$roleId) {
            Response::error('Role ID is required');
        }

        $modules = $this->service->getRoleModules($roleId);
        Response::success($modules, 'Role modules retrieved successfully');
    }
}
