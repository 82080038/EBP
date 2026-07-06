<?php

class FeatureToggleService
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
     * Check if a feature is enabled for a specific user
     * Priority: User > Role > Tenant > Global
     */
    public function isFeatureEnabledForUser($userId, $moduleCode)
    {
        // Get module_id from module_code
        $module = $this->getModuleByCode($moduleCode);
        if (!$module) {
            return false;
        }

        // Check if globally disabled
        if (!$module['is_enabled']) {
            return false;
        }

        // Get user's tenant_id and role_id
        $user = $this->getUser($userId);
        if (!$user) {
            return false;
        }

        // Check tenant-level toggle
        if (!$this->isFeatureEnabledForTenant($user['tenant_id'], $module['module_id'])) {
            return false;
        }

        // Check user-level toggle (highest priority)
        $userFeature = $this->getUserFeature($userId, $module['module_id']);
        if ($userFeature !== null) {
            return $userFeature['is_enabled'] && $this->isNotExpired($userFeature['expires_at']);
        }

        // Check role-level toggle
        $roleFeature = $this->getRoleFeature($user['role_id'], $module['module_id']);
        if ($roleFeature !== null) {
            return $roleFeature['is_enabled'] && $this->isNotExpired($roleFeature['expires_at']);
        }

        // Default: enabled if not explicitly disabled
        return true;
    }

    /**
     * Check if a feature is enabled for a specific role
     */
    public function isFeatureEnabledForRole($roleId, $moduleCode)
    {
        $module = $this->getModuleByCode($moduleCode);
        if (!$module || !$module['is_enabled']) {
            return false;
        }

        $roleFeature = $this->getRoleFeature($roleId, $module['module_id']);
        if ($roleFeature !== null) {
            return $roleFeature['is_enabled'] && $this->isNotExpired($roleFeature['expires_at']);
        }

        return true;
    }

    /**
     * Check if a feature is enabled for a specific tenant
     */
    public function isFeatureEnabledForTenant($tenantId, $moduleId)
    {
        $sql = "SELECT is_enabled, expires_at FROM tenant_feature_modules 
                WHERE tenant_id = ? AND module_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $moduleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['is_enabled'] && $this->isNotExpired($result['expires_at']);
        }

        return true; // Default enabled
    }

    /**
     * Enable feature for user
     */
    public function enableFeatureForUser($userId, $moduleCode, $expiresAt = null)
    {
        $module = $this->getModuleByCode($moduleCode);
        if (!$module) {
            return ['success' => false, 'message' => 'Module not found'];
        }

        $sql = "INSERT INTO user_feature_modules (user_id, module_id, is_enabled, expires_at)
                VALUES (?, ?, 1, ?)
                ON DUPLICATE KEY UPDATE is_enabled = 1, expires_at = ?, updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $module['module_id'], $expiresAt, $expiresAt]);

        return ['success' => true, 'message' => 'Feature enabled for user'];
    }

    /**
     * Disable feature for user
     */
    public function disableFeatureForUser($userId, $moduleCode)
    {
        $module = $this->getModuleByCode($moduleCode);
        if (!$module) {
            return ['success' => false, 'message' => 'Module not found'];
        }

        $sql = "INSERT INTO user_feature_modules (user_id, module_id, is_enabled)
                VALUES (?, ?, 0)
                ON DUPLICATE KEY UPDATE is_enabled = 0, updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $module['module_id']]);

        return ['success' => true, 'message' => 'Feature disabled for user'];
    }

    /**
     * Enable feature for role
     */
    public function enableFeatureForRole($roleId, $moduleCode, $expiresAt = null)
    {
        $module = $this->getModuleByCode($moduleCode);
        if (!$module) {
            return ['success' => false, 'message' => 'Module not found'];
        }

        $sql = "INSERT INTO role_feature_modules (role_id, module_id, is_enabled, expires_at)
                VALUES (?, ?, 1, ?)
                ON DUPLICATE KEY UPDATE is_enabled = 1, expires_at = ?, updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId, $module['module_id'], $expiresAt, $expiresAt]);

        return ['success' => true, 'message' => 'Feature enabled for role'];
    }

    /**
     * Disable feature for role
     */
    public function disableFeatureForRole($roleId, $moduleCode)
    {
        $module = $this->getModuleByCode($moduleCode);
        if (!$module) {
            return ['success' => false, 'message' => 'Module not found'];
        }

        $sql = "INSERT INTO role_feature_modules (role_id, module_id, is_enabled)
                VALUES (?, ?, 0)
                ON DUPLICATE KEY UPDATE is_enabled = 0, updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId, $module['module_id']]);

        return ['success' => true, 'message' => 'Feature disabled for role'];
    }

    /**
     * Get all features for user
     */
    public function getUserFeatures($userId)
    {
        $sql = "SELECT fm.*, ufm.is_enabled as user_enabled, ufm.expires_at as user_expires_at
                FROM feature_modules fm
                LEFT JOIN user_feature_modules ufm ON fm.module_id = ufm.module_id AND ufm.user_id = ?
                WHERE fm.is_enabled = 1
                ORDER BY fm.module_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all features for role
     */
    public function getRoleFeatures($roleId)
    {
        $sql = "SELECT fm.*, rfm.is_enabled as role_enabled, rfm.expires_at as role_expires_at
                FROM feature_modules fm
                LEFT JOIN role_feature_modules rfm ON fm.module_id = rfm.module_id AND rfm.role_id = ?
                WHERE fm.is_enabled = 1
                ORDER BY fm.module_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all available modules
     */
    public function getAllModules()
    {
        $sql = "SELECT * FROM feature_modules ORDER BY module_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getModuleByCode($moduleCode)
    {
        $sql = "SELECT * FROM feature_modules WHERE module_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$moduleCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getUser($userId)
    {
        $sql = "SELECT user_id, tenant_id, role_id FROM users WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getUserFeature($userId, $moduleId)
    {
        $sql = "SELECT is_enabled, expires_at FROM user_feature_modules 
                WHERE user_id = ? AND module_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $moduleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getRoleFeature($roleId, $moduleId)
    {
        $sql = "SELECT is_enabled, expires_at FROM role_feature_modules 
                WHERE role_id = ? AND module_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId, $moduleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function isNotExpired($expiresAt)
    {
        if ($expiresAt === null) {
            return true;
        }
        return strtotime($expiresAt) > time();
    }
}
