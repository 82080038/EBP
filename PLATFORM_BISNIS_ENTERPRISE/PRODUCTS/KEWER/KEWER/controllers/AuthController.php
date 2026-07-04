<?php
/**
 * Auth Controller
 * 
 * Handles authentication operations
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/error_handler.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Handle login
     */
    public function login($username, $password) {
        $user = $this->userModel->verifyPassword($username, $password);
        
        if ($user) {
            if ($user['status'] !== 'aktif') {
                return [
                    'success' => false,
                    'message' => 'Akun tidak aktif. Silakan hubungi administrator.'
                ];
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['cabang_id'] = $user['cabang_id'];
            
            logInfo("User logged in", ['user_id' => $user['id'], 'username' => $username]);
            
            return [
                'success' => true,
                'message' => 'Login berhasil',
                'user' => $user
            ];
        }
        
        logError("Login failed", ['username' => $username]);
        
        return [
            'success' => false,
            'message' => 'Username atau password salah'
        ];
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        
        session_destroy();
        
        logInfo("User logged out", ['user_id' => $userId]);
        
        return [
            'success' => true,
            'message' => 'Logout berhasil'
        ];
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $this->userModel->getById($_SESSION['user_id']);
    }
}
?>
