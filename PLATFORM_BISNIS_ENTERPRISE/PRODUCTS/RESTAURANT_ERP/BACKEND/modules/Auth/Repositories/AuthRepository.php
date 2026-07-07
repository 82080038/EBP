<?php

namespace Modules\Auth\Repositories;

use Core\Database;

class AuthRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find user by username
     */
    public function findByUsername($username)
    {
        $sql = "SELECT u.user_id, u.username, u.password_hash, u.tenant_id, u.branch_id, 
                       u.full_name, u.email, u.phone, u.is_platform_owner, u.status,
                       r.role_id, r.role_name, r.role_level
                FROM users u
                INNER JOIN user_roles ur ON u.user_id = ur.user_id
                INNER JOIN roles r ON ur.role_id = r.role_id
                WHERE u.username = :username 
                AND u.status = 'ACTIVE'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find user by ID
     */
    public function findById($userId, $tenantId = null)
    {
        $sql = "SELECT u.user_id, u.username, u.tenant_id, u.branch_id, 
                       u.full_name, u.email, u.phone, u.is_platform_owner, u.status,
                       r.role_id, r.role_name, r.role_level
                FROM users u
                INNER JOIN user_roles ur ON u.user_id = ur.user_id
                INNER JOIN roles r ON ur.role_id = r.role_id
                WHERE u.user_id = :user_id";
        
        $params = ['user_id' => $userId];
        
        if ($tenantId !== null) {
            $sql .= " AND u.tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user permissions
     */
    public function getUserPermissions($userId)
    {
        $sql = "SELECT DISTINCT p.permission_code, p.permission_name, p.permission_description
                FROM permissions p
                INNER JOIN role_permissions rp ON p.permission_id = rp.permission_id
                INNER JOIN user_roles ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create user
     */
    public function createUser($data)
    {
        $sql = "INSERT INTO users (tenant_id, branch_id, username, password_hash, full_name, email, phone, 
                                     is_platform_owner, status, created_by, created_at)
                VALUES (:tenant_id, :branch_id, :username, :password_hash, :full_name, :email, :phone,
                        :is_platform_owner, :status, :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId)
    {
        $sql = "INSERT INTO user_roles (user_id, role_id, created_at)
                VALUES (:user_id, :role_id, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);
    }
    
    /**
     * Update user
     */
    public function updateUser($userId, $data, $tenantId = null)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'user_id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE users SET " . implode(', ', $setClause) . " WHERE user_id = :user_id";
        $params = array_merge($data, ['user_id' => $userId]);
        
        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update password
     */
    public function updatePassword($userId, $passwordHash, $tenantId = null)
    {
        $sql = "UPDATE users 
                SET password_hash = :password_hash, updated_at = NOW() 
                WHERE user_id = :user_id";
        
        $params = ['user_id' => $userId, 'password_hash' => $passwordHash];
        
        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Soft delete user
     */
    public function deleteUser($userId, $tenantId)
    {
        $sql = "UPDATE users 
                SET deleted_at = NOW(), status = 'INACTIVE' 
                WHERE user_id = :user_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['user_id' => $userId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get all users for tenant
     */
    public function getAllUsers($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT u.user_id, u.username, u.full_name, u.email, u.phone, u.status,
                       r.role_name, r.role_level
                FROM users u
                INNER JOIN user_roles ur ON u.user_id = ur.user_id
                INNER JOIN roles r ON ur.role_id = r.role_id
                WHERE u.tenant_id = :tenant_id 
                AND u.deleted_at IS NULL
                ORDER BY u.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeUserId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username";
        $params = ['username' => $username];
        
        if ($excludeUserId !== null) {
            $sql .= " AND user_id != :user_id";
            $params['user_id'] = $excludeUserId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
