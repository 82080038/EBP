<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

if (!class_exists('RoleFallbackService')) {
    require_once __DIR__ . '/RoleFallbackService.php';
}

class RoleFallbackController
{
    private $service;

    public function __construct()
    {
        $this->service = new RoleFallbackService();
    }

    /**
     * Set tenant as single-member
     */
    public function setSingleMember($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'TENANT_MANAGE');

        $data = $request['body'] ?? [];
        $isSingleMember = $data['is_single_member'] ?? true;

        $result = $this->service->setSingleMemberTenant($user['tenant_id'], $isSingleMember);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Check if tenant is single-member
     */
    public function checkSingleMember($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $isSingleMember = $this->service->isSingleMemberTenant($user['tenant_id']);
        Response::success(['is_single_member' => $isSingleMember], 'Single-member status checked');
    }

    /**
     * Set fallback role
     */
    public function setFallback($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ROLE_MANAGE');

        $data = $request['body'] ?? [];
        $missingRoleCode = $data['missing_role_code'] ?? null;
        $fallbackRoleCode = $data['fallback_role_code'] ?? null;

        if (!$missingRoleCode || !$fallbackRoleCode) {
            Response::error('Missing role code and fallback role code are required');
        }

        $result = $this->service->setFallbackRole($user['tenant_id'], $missingRoleCode, $fallbackRoleCode);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get fallback configurations
     */
    public function getFallbacks($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $fallbacks = $this->service->getFallbackConfigurations($user['tenant_id']);
        Response::success($fallbacks, 'Fallback configurations retrieved');
    }

    /**
     * Get available roles
     */
    public function getAvailableRoles($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $roles = $this->service->getAvailableRoles($user['tenant_id']);
        Response::success($roles, 'Available roles retrieved');
    }

    /**
     * Check if role exists
     */
    public function checkRoleExists($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $roleCode = $query['role_code'] ?? null;

        if (!$roleCode) {
            Response::error('Role code is required');
        }

        $exists = $this->service->roleExists($user['tenant_id'], $roleCode);
        Response::success(['exists' => $exists], 'Role existence checked');
    }
}
