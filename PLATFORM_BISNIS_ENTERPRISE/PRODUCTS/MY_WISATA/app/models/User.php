<?php
/**
 * MyWisata Application - User Model
 * 
 * Handles user-related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class User extends Model {
    protected $table = 'users';
    
    /**
     * Find user by email
     * 
     * @param string $email Email address
     * @return array|false
     */
    public function findByEmail($email) {
        return $this->findBy(['email' => $email]);
    }
    
    /**
     * Register new user
     * 
     * @param array $data User data
     * @return int User ID
     */
    public function register($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }
    
    /**
     * Verify user credentials
     * 
     * @param string $email Email
     * @param string $password Password
     * @return array|false User data if valid, false otherwise
     */
    public function verify($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Check if user is active
            if ($user['status'] !== 'active') {
                return false;
            }
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update last login
     * 
     * @param int $userId User ID
     */
    public function updateLastLogin($userId) {
        $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * Update password
     * 
     * @param int $userId User ID
     * @param string $newPassword New password
     */
    public function updatePassword($userId, $newPassword) {
        $this->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT)
        ]);
    }
    
    /**
     * Get users by role
     * 
     * @param string $role Role
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getByRole($role, $limit = null, $offset = 0) {
        return $this->getAll(['role' => $role], 'created_at DESC', $limit, $offset);
    }
    
    /**
     * Update user status
     * 
     * @param int $userId User ID
     * @param string $status Status
     */
    public function updateStatus($userId, $status) {
        $this->update($userId, ['status' => $status]);
    }
}
