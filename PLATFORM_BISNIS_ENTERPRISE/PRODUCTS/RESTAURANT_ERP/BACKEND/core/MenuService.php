<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

use PDO;

class MenuService
{
    private $db;
    private $roleFallbackService;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!class_exists('RoleFallbackService')) {
            require_once __DIR__ . '/RoleFallbackService.php';
        }
        $this->roleFallbackService = new RoleFallbackService();
    }

    /**
     * Get menu for user based on their roles
     */
    public function getUserMenu($userId, $tenantId)
    {
        // Get user's roles
        $sql = "SELECT r.role_id, r.role_code FROM roles r
                JOIN user_roles ur ON r.role_id = ur.role_id
                JOIN users u ON ur.user_id = u.user_id
                WHERE u.user_id = ? AND r.tenant_id = ? AND r.status = 'ACTIVE'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $tenantId]);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($roles)) {
            return [];
        }

        // Check if single-member tenant - show all modules
        if ($this->roleFallbackService->isSingleMemberTenant($tenantId)) {
            return $this->getAllModulesMenu();
        }

        // Get menu items for user's roles
        $roleIds = array_column($roles, 'role_id');
        $placeholders = str_repeat('?,', count($roleIds) - 1) . '?';

        $sql = "SELECT DISTINCT rm.* FROM role_menus rm
                WHERE rm.role_id IN ($placeholders) AND rm.is_visible = 1
                ORDER BY rm.display_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($roleIds);
        $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->buildMenuTree($menuItems);
    }

    /**
     * Get all modules menu (for single-member tenants)
     */
    public function getAllModulesMenu()
    {
        $sql = "SELECT fm.module_code as menu_code, fm.module_name as menu_name, 
                       CONCAT('/', fm.module_code) as menu_path,
                       fm.module_category as parent_menu_code,
                       fm.module_id as display_order,
                       'grid' as icon
                FROM feature_modules fm
                WHERE fm.is_enabled = 1
                ORDER BY fm.module_category, fm.module_name";
        $stmt = $this->db->query($sql);
        $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->buildMenuTree($menuItems);
    }

    /**
     * Build hierarchical menu tree
     */
    private function buildMenuTree($menuItems)
    {
        $tree = [];
        $children = [];

        // Separate parent and child items
        foreach ($menuItems as $item) {
            if (empty($item['parent_menu_code'])) {
                $tree[$item['menu_code']] = $item;
                $tree[$item['menu_code']]['children'] = [];
            } else {
                $children[$item['parent_menu_code']][] = $item;
            }
        }

        // Attach children to parents
        foreach ($children as $parentCode => $childItems) {
            if (isset($tree[$parentCode])) {
                $tree[$parentCode]['children'] = $childItems;
            }
        }

        return array_values($tree);
    }

    /**
     * Set menu items for role
     */
    public function setRoleMenu($roleId, $menuItems)
    {
        $this->db->beginTransaction();

        try {
            // Delete existing menu items for role
            $sql = "DELETE FROM role_menus WHERE role_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$roleId]);

            // Insert new menu items
            $sql = "INSERT INTO role_menus (role_id, menu_code, menu_name, menu_path, parent_menu_code, display_order, icon)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            foreach ($menuItems as $item) {
                $stmt->execute([
                    $roleId,
                    $item['menu_code'],
                    $item['menu_name'],
                    $item['menu_path'] ?? null,
                    $item['parent_menu_code'] ?? null,
                    $item['display_order'] ?? 0,
                    $item['icon'] ?? null
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Role menu configured successfully'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to configure role menu: ' . $e->getMessage()];
        }
    }

    /**
     * Get menu for specific role
     */
    public function getRoleMenu($roleId)
    {
        $sql = "SELECT * FROM role_menus WHERE role_id = ? AND is_visible = 1 ORDER BY display_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->buildMenuTree($menuItems);
    }

    /**
     * Copy menu from one role to another
     */
    public function copyRoleMenu($fromRoleId, $toRoleId)
    {
        $sql = "INSERT INTO role_menus (role_id, menu_code, menu_name, menu_path, parent_menu_code, display_order, icon)
                SELECT ?, menu_code, menu_name, menu_path, parent_menu_code, display_order, icon
                FROM role_menus WHERE role_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$toRoleId, $fromRoleId]);

        return ['success' => true, 'message' => 'Menu copied successfully'];
    }

    /**
     * Check if user has access to menu item
     */
    public function hasMenuAccess($userId, $menuCode)
    {
        $sql = "SELECT COUNT(*) as count FROM role_menus rm
                JOIN user_roles ur ON rm.role_id = ur.role_id
                JOIN users u ON ur.user_id = u.user_id
                WHERE u.user_id = ? AND rm.menu_code = ? AND rm.is_visible = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $menuCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    /**
     * Get available modules for role based on menu
     */
    public function getRoleModules($roleId)
    {
        $sql = "SELECT DISTINCT fm.* FROM feature_modules fm
                JOIN role_menus rm ON fm.module_code = rm.menu_code
                WHERE rm.role_id = ? AND rm.is_visible = 1 AND fm.is_enabled = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
