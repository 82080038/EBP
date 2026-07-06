<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

use PDO;

class CustomRoleService
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
     * Create custom role from template
     */
    public function createRoleFromTemplate($tenantId, $templateCode, $roleName, $roleCode = null)
    {
        $this->db->beginTransaction();

        try {
            // Get template
            $sql = "SELECT * FROM role_templates WHERE template_code = ? AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$templateCode]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$template) {
                throw new Exception('Template not found');
            }

            // Generate role code if not provided
            $roleCode = $roleCode ?? strtoupper($templateCode . '_' . substr(md5(uniqid()), 0, 6));

            // Create role
            $sql = "INSERT INTO roles (tenant_id, role_code, role_name, description, is_system, status)
                    VALUES (?, ?, ?, ?, 0, 'ACTIVE')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $roleCode, $roleName, $template['description']]);
            $roleId = $this->db->lastInsertId();

            // Copy template permissions
            $sql = "INSERT INTO permissions (tenant_id, permission_code, permission_name, description, created_at)
                    SELECT ?, tp.permission_code, tp.permission_code, tp.permission_code, NOW()
                    FROM role_template_permissions tp
                    WHERE tp.template_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $template['template_id']]);

            // Assign permissions to role
            $sql = "INSERT INTO role_permissions (role_id, permission_id)
                    SELECT ?, p.permission_id
                    FROM permissions p
                    WHERE p.tenant_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$roleId, $tenantId]);

            // Copy template modules
            $sql = "INSERT INTO role_feature_modules (role_id, module_id, is_enabled)
                    SELECT ?, fm.module_id, 1
                    FROM role_template_modules rtm
                    JOIN feature_modules fm ON rtm.module_code = fm.module_code
                    WHERE rtm.template_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$roleId, $template['template_id']]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Role created from template', 'role_id' => $roleId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to create role: ' . $e->getMessage()];
        }
    }

    /**
     * Create custom role
     */
    public function createCustomRole($tenantId, $roleData, $permissions = [], $modules = [])
    {
        $this->db->beginTransaction();

        try {
            // Create role
            $sql = "INSERT INTO roles (tenant_id, role_code, role_name, description, is_system, status)
                    VALUES (?, ?, ?, ?, 0, 'ACTIVE')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $tenantId,
                $roleData['role_code'],
                $roleData['role_name'],
                $roleData['description'] ?? null
            ]);
            $roleId = $this->db->lastInsertId();

            // Add custom permissions
            foreach ($permissions as $permission) {
                $sql = "INSERT INTO custom_permissions (tenant_id, permission_code, permission_name, description, module_code)
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $tenantId,
                    $permission['permission_code'],
                    $permission['permission_name'],
                    $permission['description'] ?? null,
                    $permission['module_code'] ?? null
                ]);

                // Assign to role
                $sql = "INSERT INTO role_permissions (role_id, permission_id)
                        SELECT ?, permission_id FROM permissions WHERE permission_code = ? AND tenant_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$roleId, $permission['permission_code'], $tenantId]);
            }

            // Add module access
            foreach ($modules as $module) {
                $sql = "INSERT INTO role_feature_modules (role_id, module_id, is_enabled)
                        SELECT ?, module_id, 1 FROM feature_modules WHERE module_code = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$roleId, $module['module_code']]);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Custom role created', 'role_id' => $roleId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to create custom role: ' . $e->getMessage()];
        }
    }

    /**
     * Get role templates
     */
    public function getRoleTemplates()
    {
        $sql = "SELECT * FROM role_templates WHERE is_active = 1 ORDER BY template_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get template details with permissions and modules
     */
    public function getTemplateDetails($templateCode)
    {
        $sql = "SELECT * FROM role_templates WHERE template_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$templateCode]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$template) {
            return null;
        }

        // Get permissions
        $sql = "SELECT * FROM role_template_permissions WHERE template_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$template['template_id']]);
        $template['permissions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get modules
        $sql = "SELECT * FROM role_template_modules WHERE template_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$template['template_id']]);
        $template['modules'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $template;
    }

    /**
     * Clone existing role
     */
    public function cloneRole($tenantId, $sourceRoleId, $newRoleName, $newRoleCode = null)
    {
        $this->db->beginTransaction();

        try {
            // Get source role
            $sql = "SELECT * FROM roles WHERE role_id = ? AND tenant_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$sourceRoleId, $tenantId]);
            $sourceRole = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sourceRole) {
                throw new Exception('Source role not found');
            }

            // Generate role code if not provided
            $newRoleCode = $newRoleCode ?? strtoupper($sourceRole['role_code'] . '_CLONE_' . substr(md5(uniqid()), 0, 6));

            // Create new role
            $sql = "INSERT INTO roles (tenant_id, role_code, role_name, description, is_system, status)
                    VALUES (?, ?, ?, ?, 0, 'ACTIVE')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $newRoleCode, $newRoleName, $sourceRole['description']]);
            $newRoleId = $this->db->lastInsertId();

            // Copy permissions
            $sql = "INSERT INTO role_permissions (role_id, permission_id)
                    SELECT ?, permission_id FROM role_permissions WHERE role_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newRoleId, $sourceRoleId]);

            // Copy modules
            $sql = "INSERT INTO role_feature_modules (role_id, module_id, is_enabled)
                    SELECT ?, module_id, is_enabled FROM role_feature_modules WHERE role_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newRoleId, $sourceRoleId]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Role cloned successfully', 'role_id' => $newRoleId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to clone role: ' . $e->getMessage()];
        }
    }
}
