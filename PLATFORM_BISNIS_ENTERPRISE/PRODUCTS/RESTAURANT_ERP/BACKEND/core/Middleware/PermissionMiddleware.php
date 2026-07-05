<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../bootstrap.php';

class PermissionMiddleware
{
    private static $permissionCache = [];
    private static $cacheTTL = 300; // 5 minutes cache TTL

    public function check($userId, $permission, $isPlatformOwner = false)
    {
        // Platform owners have all permissions by default
        if ($isPlatformOwner) {
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
}
