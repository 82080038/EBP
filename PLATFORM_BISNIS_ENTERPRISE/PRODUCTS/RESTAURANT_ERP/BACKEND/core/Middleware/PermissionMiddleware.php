<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../bootstrap.php';

class PermissionMiddleware
{
    private static $permissionCache = [];
    private static $cacheTTL = 300; // 5 minutes cache TTL

    public function check($userId, $permission, $isPlatformOwner = false, $isTenantOwner = false)
    {
        // Platform owners have all permissions by default
        if ($isPlatformOwner) {
            return true;
        }

        // Tenant owners have all permissions by default
        if ($isTenantOwner) {
            return true;
        }

        // Check cache first
        $cacheKey = "{$userId}_{$permission}";
        if (isset(self::$permissionCache[$cacheKey])) {
            $cached = self::$permissionCache[$cacheKey];
            // Check if cache is still valid
            if (time() - $cached['timestamp'] < self::$cacheTTL) {
                return $cached['hasPermission'];
            }
            // Cache expired, remove it
            unset(self::$permissionCache[$cacheKey]);
        }

        $database = new Database();
        $db = $database->connect();

        $sql = "
            SELECT COUNT(*) as count
            FROM user_roles ur
            INNER JOIN role_permissions rp ON ur.role_id = rp.role_id
            INNER JOIN permissions p ON rp.permission_id = p.permission_id
            WHERE ur.user_id = ? AND p.permission_code = ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $permission]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $hasPermission = $result['count'] > 0;

        // Cache the result
        self::$permissionCache[$cacheKey] = [
            'hasPermission' => $hasPermission,
            'timestamp' => time()
        ];

        return $hasPermission;
    }

    public static function handle($request, $permission)
    {
        $middleware = new self();
        // Get user_id from request (should be set by AuthMiddleware)
        $userId = $request['user_id'] ?? null;
        $isPlatformOwner = $request['is_platform_owner'] ?? false;
        if (!$userId) {
            Response::error("User not authenticated");
        }
        return $middleware->check($userId, $permission, $isPlatformOwner);
    }

    /**
     * Clear permission cache for a specific user
     * 
     * @param int $userId User ID
     * @return void
     */
    public static function clearUserCache($userId)
    {
        foreach (self::$permissionCache as $key => $value) {
            if (strpos($key, "{$userId}_") === 0) {
                unset(self::$permissionCache[$key]);
            }
        }
    }

    /**
     * Clear all permission cache
     * 
     * @return void
     */
    public static function clearAllCache()
    {
        self::$permissionCache = [];
    }

    /**
     * Check if user has permission for a specific action on a module
     * 
     * @param int $userId User ID
     * @param string $module Module name (e.g., 'menu', 'order', 'table')
     * @param string $action Action name (e.g., 'create', 'edit', 'delete', 'view')
     * @param bool $isPlatformOwner Whether user is platform owner
     * @return bool
     */
    public function checkAction($userId, $module, $action, $isPlatformOwner = false)
    {
        $permissionCode = strtoupper($module . '_' . $action);
        return $this->check($userId, $permissionCode, $isPlatformOwner);
    }

    /**
     * Get all permissions for a user
     * 
     * @param int $userId User ID
     * @param bool $isPlatformOwner Whether user is platform owner
     * @return array Array of permission codes
     */
    public function getUserPermissions($userId, $isPlatformOwner = false)
    {
        // Platform owners have all permissions
        if ($isPlatformOwner) {
            $database = new Database();
            $db = $database->connect();
            $stmt = $db->query("SELECT permission_code FROM permissions");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $database = new Database();
        $db = $database->connect();

        $sql = "
            SELECT DISTINCT p.permission_code
            FROM user_roles ur
            INNER JOIN role_permissions rp ON ur.role_id = rp.role_id
            INNER JOIN permissions p ON rp.permission_id = p.permission_id
            WHERE ur.user_id = ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get user's role names
     * 
     * @param int $userId User ID
     * @return array Array of role names
     */
    public function getUserRoles($userId)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "
            SELECT r.role_name, r.role_code
            FROM user_roles ur
            INNER JOIN roles r ON ur.role_id = r.role_id
            WHERE ur.user_id = ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if user can create items in a module
     * 
     * @param int $userId User ID
     * @param string $module Module name
     * @param bool $isPlatformOwner Whether user is platform owner
     * @return bool
     */
    public function canCreate($userId, $module, $isPlatformOwner = false)
    {
        return $this->checkAction($userId, $module, 'create', $isPlatformOwner);
    }

    /**
     * Check if user can edit items in a module
     * 
     * @param int $userId User ID
     * @param string $module Module name
     * @param bool $isPlatformOwner Whether user is platform owner
     * @return bool
     */
    public function canEdit($userId, $module, $isPlatformOwner = false)
    {
        return $this->checkAction($userId, $module, 'edit', $isPlatformOwner);
    }

    /**
     * Check if user can delete items in a module
     * 
     * @param int $userId User ID
     * @param string $module Module name
     * @param bool $isPlatformOwner Whether user is platform owner
     * @return bool
     */
    public function canDelete($userId, $module, $isPlatformOwner = false)
    {
        return $this->checkAction($userId, $module, 'delete', $isPlatformOwner);
    }

    /**
     * Check if user can view items in a module
     * 
     * @param int $userId User ID
     * @param string $module Module name
     * @param bool $isPlatformOwner Whether user is platform owner
     * @return bool
     */
    public function canView($userId, $module, $isPlatformOwner = false)
    {
        return $this->checkAction($userId, $module, 'view', $isPlatformOwner);
    }
}
