<?php

require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../../../core/Transaction.php';
require_once __DIR__ . '/../../../core/Audit.php';

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
}
