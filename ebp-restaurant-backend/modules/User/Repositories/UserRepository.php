<?php
require_once __DIR__ . "/../../../config/database.php";



class UserRepository
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function findAll(int $tenantId, ?int $branchId = null): array
    {
        $sql = "
            SELECT user_id, tenant_id, branch_id, username, email, full_name, phone, status, created_at, updated_at
            FROM users 
            WHERE tenant_id = :tenant_id AND deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY full_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User($row);
        }
        
        return $users;
    }

    public function findById(int $tenantId, int $userId): ?User
    {
        $stmt = $this->db->prepare("
            SELECT user_id, tenant_id, branch_id, username, email, password, full_name, phone, status, created_at, updated_at
            FROM users 
            WHERE tenant_id = :tenant_id AND user_id = :user_id AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'user_id' => $userId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new User($row) : null;
    }

    public function findByUsername(int $tenantId, string $username): ?User
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE tenant_id = :tenant_id AND username = :username AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'username' => $username]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new User($row) : null;
    }

    public function findByEmail(int $tenantId, string $email): ?User
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE tenant_id = :tenant_id AND email = :email AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'email' => $email]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new User($row) : null;
    }

    public function create(User $user): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO users 
            (tenant_id, branch_id, username, email, password, full_name, phone, status)
            VALUES 
            (:tenant_id, :branch_id, :username, :email, :password, :full_name, :phone, :status)
        ");
        
        return $stmt->execute([
            'tenant_id' => $user->tenant_id,
            'branch_id' => $user->branch_id,
            'username' => $user->username,
            'email' => $user->email,
            'password' => $user->password,
            'full_name' => $user->full_name,
            'phone' => $user->phone,
            'status' => $user->status ?? 'ACTIVE'
        ]);
    }

    public function update(User $user): bool
    {
        $sql = "UPDATE users 
                SET username = :username,
                    email = :email,
                    full_name = :full_name,
                    phone = :phone,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP";
        
        if (!empty($user->password)) {
            $sql .= ", password = :password";
        }
        
        $sql .= " WHERE tenant_id = :tenant_id AND user_id = :user_id";
        
        $params = [
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->user_id,
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'phone' => $user->phone,
            'status' => $user->status
        ];
        
        if (!empty($user->password)) {
            $params['password'] = $user->password;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updatePassword(int $tenantId, int $userId, string $password): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password = :password, updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND user_id = :user_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'user_id' => $userId, 'password' => $password]);
    }

    public function delete(int $tenantId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE tenant_id = :tenant_id AND user_id = :user_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'user_id' => $userId]);
    }

    public function assignRole(int $userId, int $roleId): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_roles (user_id, role_id, assigned_at)
            VALUES (:user_id, :role_id, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP
        ");
        
        return $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);
    }

    public function removeRole(int $userId, int $roleId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM user_roles 
            WHERE user_id = :user_id AND role_id = :role_id
        ");
        
        return $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);
    }

    public function getUserRoles(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.* 
            FROM roles r
            JOIN user_roles ur ON r.role_id = ur.role_id
            WHERE ur.user_id = :user_id AND r.deleted_at IS NULL
        ");
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
