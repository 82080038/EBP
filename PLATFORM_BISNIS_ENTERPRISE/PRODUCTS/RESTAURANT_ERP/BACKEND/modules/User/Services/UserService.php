<?php

if (!class_exists('UserRepository')) {
    require_once __DIR__ . '/../Repositories/UserRepository.php';
}



class UserService
{
    private $userRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->transaction = new Transaction();
        $this->audit = new Audit();
    }

    public function getAllUsers(int $tenantId, ?int $branchId = null): array
    {
        $users = $this->userRepository->findAll($tenantId, $branchId);
        return array_map(function($u) { return $u->toArray(); }, $users);
    }

    public function getUser(int $tenantId, int $userId): ?array
    {
        $user = $this->userRepository->findById($tenantId, $userId);
        
        if ($user) {
            $data = $user->toArray();
            $data['roles'] = $this->userRepository->getUserRoles($userId);
            return $data;
        }
        
        return null;
    }

    public function createUser(int $tenantId, array $data): bool
    {
        $this->transaction->begin();

        try {
            // Check if username already exists
            $existing = $this->userRepository->findByUsername($tenantId, $data['username']);
            if ($existing) {
                $this->transaction->rollback();
                return false;
            }

            // Check if email already exists
            $existing = $this->userRepository->findByEmail($tenantId, $data['email']);
            if ($existing) {
                $this->transaction->rollback();
                return false;
            }

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

            $data['tenant_id'] = $tenantId;
            $user = new \Modules\User\Models\User($data);

            $result = $this->userRepository->create($user);

            if ($result) {
                $userId = $this->transaction->getLastInsertId();

                // Assign roles if provided
                if (isset($data['roles']) && is_array($data['roles'])) {
                    foreach ($data['roles'] as $roleId) {
                        $this->userRepository->assignRole($userId, $roleId);
                    }
                }

                // Assign role by code if provided
                if (isset($data['role_code'])) {
                    $this->assignRoleByCode($userId, $data['role_code'], $tenantId);
                }

                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'USER',
                    'action' => 'CREATE_USER',
                    'record_id' => $userId,
                    'table_name' => 'users',
                    'new_values' => json_encode(array_diff_key($data, ['password' => '']))
                ]);

                $this->transaction->commit();
                return true;
            }

            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function createUserWithRole(int $tenantId, int $branchId, array $userData, string $roleCode): array
    {
        $this->transaction->begin();

        try {
            // Check if username already exists
            $existing = $this->userRepository->findByUsername($tenantId, $userData['username']);
            if ($existing) {
                $this->transaction->rollback();
                return ['success' => false, 'message' => 'Username already exists'];
            }

            // Check if email already exists
            $existing = $this->userRepository->findByEmail($tenantId, $userData['email']);
            if ($existing) {
                $this->transaction->rollback();
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Hash password
            $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
            $userData['tenant_id'] = $tenantId;
            $userData['branch_id'] = $branchId;

            $user = new \Modules\User\Models\User($userData);
            $result = $this->userRepository->create($user);

            if ($result) {
                $userId = $this->transaction->getLastInsertId();

                // Assign role by code
                $roleAssigned = $this->assignRoleByCode($userId, $roleCode, $tenantId);

                if (!$roleAssigned) {
                    $this->transaction->rollback();
                    return ['success' => false, 'message' => 'Role not found'];
                }

                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'USER',
                    'action' => 'CREATE_USER_WITH_ROLE',
                    'record_id' => $userId,
                    'table_name' => 'users',
                    'new_values' => json_encode(array_merge($userData, ['role_code' => $roleCode]))
                ]);

                $this->transaction->commit();
                return ['success' => true, 'message' => 'User created successfully', 'user_id' => $userId];
            }

            $this->transaction->rollback();
            return ['success' => false, 'message' => 'Failed to create user'];
        } catch (\Exception $e) {
            $this->transaction->rollback();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function assignRoleByCode(int $userId, string $roleCode, int $tenantId): bool
    {
        $db = new \Database();
        $pdo = $db->connect();

        $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE tenant_id = ? AND role_code = ?");
        $stmt->execute([$tenantId, $roleCode]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            return false;
        }

        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, assigned_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $role['role_id']]);
    }

    public function getAvailableRoles(int $tenantId): array
    {
        $db = new \Database();
        $pdo = $db->connect();

        $stmt = $pdo->prepare("SELECT role_id, role_code, role_name, description FROM roles WHERE tenant_id = ? AND status = 'ACTIVE'");
        $stmt->execute([$tenantId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUser(int $tenantId, int $userId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldUser = $this->userRepository->findById($tenantId, $userId);
            
            $data['tenant_id'] = $tenantId;
            $data['user_id'] = $userId;
            
            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            
            $user = new \Modules\User\Models\User($data);
            
            $result = $this->userRepository->update($user);
            
            if ($result) {
                // Update roles if provided
                if (isset($data['roles']) && is_array($data['roles'])) {
                    // Remove all existing roles
                    $existingRoles = $this->userRepository->getUserRoles($userId);
                    foreach ($existingRoles as $role) {
                        $this->userRepository->removeRole($userId, $role['role_id']);
                    }
                    
                    // Add new roles
                    foreach ($data['roles'] as $roleId) {
                        $this->userRepository->assignRole($userId, $roleId);
                    }
                }
                
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'USER',
                    'action' => 'UPDATE_USER',
                    'record_id' => $userId,
                    'table_name' => 'users',
                    'old_values' => json_encode($oldUser->toArray()),
                    'new_values' => json_encode(array_diff_key($data, ['password' => '']))
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function changePassword(int $tenantId, int $userId, string $oldPassword, string $newPassword): bool
    {
        $this->transaction->begin();
        
        try {
            $user = $this->userRepository->findById($tenantId, $userId);
            
            // Verify old password
            if (!password_verify($oldPassword, $user->password)) {
                $this->transaction->rollback();
                return false;
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            $result = $this->userRepository->updatePassword($tenantId, $userId, $hashedPassword);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'USER',
                    'action' => 'CHANGE_PASSWORD',
                    'record_id' => $userId,
                    'table_name' => 'users',
                    'old_values' => json_encode(['password' => '***']),
                    'new_values' => json_encode(['password' => '***'])
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function deleteUser(int $tenantId, int $userId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldUser = $this->userRepository->findById($tenantId, $userId);
            
            $result = $this->userRepository->delete($tenantId, $userId);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'USER',
                    'action' => 'DELETE_USER',
                    'record_id' => $userId,
                    'table_name' => 'users',
                    'old_values' => json_encode($oldUser->toArray())
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function getUserPermissions(int $tenantId, int $userId): array
    {
        $db = new \Database();
        $pdo = $db->connect();

        // Get user roles
        $stmt = $pdo->prepare("
            SELECT r.role_code, r.role_name 
            FROM roles r
            JOIN user_roles ur ON r.role_id = ur.role_id
            WHERE ur.user_id = ? AND r.tenant_id = ? AND r.status = 'ACTIVE'
        ");
        $stmt->execute([$userId, $tenantId]);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($roles)) {
            return [];
        }

        // Get permissions for all user roles
        $roleCodes = array_column($roles, 'role_code');
        $placeholders = str_repeat('?,', count($roleCodes) - 1) . '?';

        $stmt = $pdo->prepare("
            SELECT DISTINCT p.permission_name, p.description
            FROM permissions p
            JOIN role_permissions rp ON p.permission_id = rp.permission_id
            JOIN roles r ON rp.role_id = r.role_id
            WHERE r.role_code IN ($placeholders) AND r.tenant_id = ? AND r.status = 'ACTIVE'
            ORDER BY p.permission_name
        ");
        $stmt->execute([...$roleCodes, $tenantId]);
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert BE underscore format to FE dot format
        $convertedPermissions = [];
        foreach ($permissions as $permission) {
            $beFormat = $permission['permission_name'];
            $feFormat = strtolower(str_replace('_', '.', $beFormat));
            $convertedPermissions[] = [
                'be_format' => $beFormat,
                'fe_format' => $feFormat,
                'description' => $permission['description']
            ];
        }

        return [
            'roles' => $roles,
            'permissions' => $convertedPermissions
        ];
    }
}
