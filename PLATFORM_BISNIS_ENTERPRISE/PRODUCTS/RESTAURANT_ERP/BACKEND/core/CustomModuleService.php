<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

use PDO;

class CustomModuleService
{
    private $db;

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
    }

    /**
     * Create custom module for tenant
     */
    public function createCustomModule($tenantId, $moduleData, $createdBy)
    {
        $sql = "INSERT INTO custom_modules (tenant_id, module_code, module_name, module_category, description, module_type, is_enabled, is_premium, pricing_tier, custom_config, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $moduleData['module_code'],
            $moduleData['module_name'],
            $moduleData['module_category'] ?? 'CUSTOM',
            $moduleData['description'] ?? null,
            $moduleData['module_type'] ?? 'CUSTOM',
            $moduleData['is_enabled'] ?? 1,
            $moduleData['is_premium'] ?? 0,
            $moduleData['pricing_tier'] ?? null,
            json_encode($moduleData['custom_config'] ?? []),
            $createdBy
        ]);

        return ['success' => true, 'message' => 'Custom module created', 'custom_module_id' => $this->db->lastInsertId()];
    }

    /**
     * Get custom modules for tenant
     */
    public function getCustomModules($tenantId)
    {
        $sql = "SELECT * FROM custom_modules WHERE tenant_id = ? AND is_enabled = 1 ORDER BY module_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all modules (system + custom) for tenant
     */
    public function getAllModules($tenantId)
    {
        // Get system modules
        $sql = "SELECT 'SYSTEM' as source, module_id, module_code, module_name, module_category, description, is_enabled, is_premium, pricing_tier
                FROM feature_modules WHERE is_enabled = 1";
        $stmt = $this->db->query($sql);
        $systemModules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get custom modules
        $sql = "SELECT 'CUSTOM' as source, custom_module_id as module_id, module_code, module_name, module_category, description, is_enabled, is_premium, pricing_tier
                FROM custom_modules WHERE tenant_id = ? AND is_enabled = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $customModules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_merge($systemModules, $customModules);
    }

    /**
     * Update custom module
     */
    public function updateCustomModule($customModuleId, $moduleData)
    {
        $sql = "UPDATE custom_modules 
                SET module_name = ?, module_category = ?, description = ?, is_enabled = ?, is_premium = ?, pricing_tier = ?, custom_config = ?, updated_at = CURRENT_TIMESTAMP
                WHERE custom_module_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $moduleData['module_name'],
            $moduleData['module_category'] ?? 'CUSTOM',
            $moduleData['description'] ?? null,
            $moduleData['is_enabled'] ?? 1,
            $moduleData['is_premium'] ?? 0,
            $moduleData['pricing_tier'] ?? null,
            json_encode($moduleData['custom_config'] ?? []),
            $customModuleId
        ]);

        return ['success' => true, 'message' => 'Custom module updated'];
    }

    /**
     * Delete custom module
     */
    public function deleteCustomModule($customModuleId)
    {
        $sql = "UPDATE custom_modules SET is_enabled = 0, updated_at = CURRENT_TIMESTAMP WHERE custom_module_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customModuleId]);

        return ['success' => true, 'message' => 'Custom module deleted'];
    }

    /**
     * Create custom permission
     */
    public function createCustomPermission($tenantId, $permissionData)
    {
        $sql = "INSERT INTO custom_permissions (tenant_id, permission_code, permission_name, description, module_code, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name), description = VALUES(description)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $permissionData['permission_code'],
            $permissionData['permission_name'],
            $permissionData['description'] ?? null,
            $permissionData['module_code'] ?? null
        ]);

        return ['success' => true, 'message' => 'Custom permission created'];
    }

    /**
     * Get custom permissions for tenant
     */
    public function getCustomPermissions($tenantId, $moduleCode = null)
    {
        $sql = "SELECT * FROM custom_permissions WHERE tenant_id = ? AND is_active = 1";
        $params = [$tenantId];

        if ($moduleCode) {
            $sql .= " AND module_code = ?";
            $params[] = $moduleCode;
        }

        $sql .= " ORDER BY permission_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all permissions (system + custom) for tenant
     */
    public function getAllPermissions($tenantId, $moduleCode = null)
    {
        // Get system permissions
        $sql = "SELECT 'SYSTEM' as source, permission_id, permission_code, permission_name, description, module_code
                FROM permissions WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($moduleCode) {
            $sql .= " AND module_code = ?";
            $params[] = $moduleCode;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $systemPermissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get custom permissions
        $sql = "SELECT 'CUSTOM' as source, custom_permission_id as permission_id, permission_code, permission_name, description, module_code
                FROM custom_permissions WHERE tenant_id = ? AND is_active = 1";
        $params = [$tenantId];

        if ($moduleCode) {
            $sql .= " AND module_code = ?";
            $params[] = $moduleCode;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $customPermissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_merge($systemPermissions, $customPermissions);
    }

    /**
     * Assign custom module to role
     */
    public function assignModuleToRole($tenantId, $roleCode, $moduleCode, $isEnabled = true)
    {
        // Check if it's a custom module
        $sql = "SELECT custom_module_id FROM custom_modules WHERE tenant_id = ? AND module_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $moduleCode]);
        $customModule = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customModule) {
            // Assign custom module (using custom_module_id as module_id in role_feature_modules)
            $sql = "INSERT INTO role_feature_modules (role_id, module_id, is_enabled)
                    SELECT r.role_id, ?, ?
                    FROM roles r
                    WHERE r.tenant_id = ? AND r.role_code = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customModule['custom_module_id'], $isEnabled ? 1 : 0, $tenantId, $roleCode]);
        } else {
            // Assign system module
            $sql = "INSERT INTO role_feature_modules (role_id, module_id, is_enabled)
                    SELECT r.role_id, fm.module_id, ?
                    FROM roles r
                    JOIN feature_modules fm ON fm.module_code = ?
                    WHERE r.tenant_id = ? AND r.role_code = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$isEnabled ? 1 : 0, $moduleCode, $tenantId, $roleCode]);
        }

        return ['success' => true, 'message' => 'Module assigned to role'];
    }
}
