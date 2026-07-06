<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

use PDO;

class RoleFallbackService
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
     * Check if tenant is single-member (owner only)
     */
    public function isSingleMemberTenant($tenantId)
    {
        $sql = "SELECT is_single_member FROM tenants WHERE tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['is_single_member'] == 1;
    }

    /**
     * Set tenant as single-member
     */
    public function setSingleMemberTenant($tenantId, $isSingleMember = true)
    {
        $sql = "UPDATE tenants SET is_single_member = ? WHERE tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$isSingleMember ? 1 : 0, $tenantId]);
        
        return ['success' => true, 'message' => 'Tenant configuration updated'];
    }

    /**
     * Get fallback role for missing role
     */
    public function getFallbackRole($tenantId, $missingRoleCode)
    {
        // Check if single-member tenant - owner gets all permissions
        if ($this->isSingleMemberTenant($tenantId)) {
            return ['role_code' => 'ADMIN', 'is_fallback' => true];
        }

        // Check configured fallback
        $sql = "SELECT fallback_role_code FROM role_fallbacks 
                WHERE tenant_id = ? AND missing_role_code = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $missingRoleCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return ['role_code' => $result['fallback_role_code'], 'is_fallback' => true];
        }

        // Default fallback to ADMIN for critical roles
        $criticalRoles = ['MANAGER', 'ADMIN', 'OWNER'];
        if (in_array($missingRoleCode, $criticalRoles)) {
            return ['role_code' => 'ADMIN', 'is_fallback' => true];
        }

        return null;
    }

    /**
     * Set fallback role
     */
    public function setFallbackRole($tenantId, $missingRoleCode, $fallbackRoleCode)
    {
        $sql = "INSERT INTO role_fallbacks (tenant_id, missing_role_code, fallback_role_code)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE fallback_role_code = ?, updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $missingRoleCode, $fallbackRoleCode, $fallbackRoleCode]);
        
        return ['success' => true, 'message' => 'Fallback role configured'];
    }

    /**
     * Get effective role for user (handles missing roles)
     */
    public function getEffectiveRole($userId, $requiredRoleCode)
    {
        // Get user's tenant and actual role
        $sql = "SELECT u.tenant_id, r.role_code 
                FROM users u
                JOIN user_roles ur ON u.user_id = ur.user_id
                JOIN roles r ON ur.role_id = r.role_id
                WHERE u.user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        // If user already has the required role, return it
        if ($result['role_code'] === $requiredRoleCode) {
            return $result;
        }

        // Check if role exists in tenant
        $sql = "SELECT role_id FROM roles WHERE tenant_id = ? AND role_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$result['tenant_id'], $requiredRoleCode]);
        $roleExists = $stmt->fetch(PDO::FETCH_ASSOC);

        // If role exists, user doesn't have it
        if ($roleExists) {
            return null;
        }

        // Role doesn't exist - check fallback
        $fallback = $this->getFallbackRole($result['tenant_id'], $requiredRoleCode);
        if ($fallback) {
            return [
                'tenant_id' => $result['tenant_id'],
                'role_code' => $fallback['role_code'],
                'is_fallback' => true,
                'original_required_role' => $requiredRoleCode
            ];
        }

        return null;
    }

    /**
     * Get all available roles for tenant
     */
    public function getAvailableRoles($tenantId)
    {
        $sql = "SELECT * FROM roles WHERE tenant_id = ? AND status = 'ACTIVE' AND deleted_at IS NULL ORDER BY role_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if role exists in tenant
     */
    public function roleExists($tenantId, $roleCode)
    {
        $sql = "SELECT role_id FROM roles WHERE tenant_id = ? AND role_code = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $roleCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Get fallback configurations for tenant
     */
    public function getFallbackConfigurations($tenantId)
    {
        $sql = "SELECT * FROM role_fallbacks WHERE tenant_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
