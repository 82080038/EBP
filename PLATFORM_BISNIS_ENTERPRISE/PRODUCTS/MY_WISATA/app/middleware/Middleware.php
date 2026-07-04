<?php
/**
 * MyWisata Application - Middleware Class
 * 
 * Handles authentication, authorization, and CSRF protection.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class Middleware {
    
    /**
     * Require user to be authenticated
     * Redirects to login if not authenticated
     */
    public static function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            if (self::isAjax()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                exit;
            }
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }
    
    /**
     * Require specific role(s) to access
     * 
     * @param string|array $roles Role(s) required
     */
    public static function requireRole($roles) {
        self::requireAuth();
        
        $userRole = $_SESSION['role'] ?? null;
        $allowedRoles = is_array($roles) ? $roles : [$roles];
        
        if (!in_array($userRole, $allowedRoles)) {
            if (self::isAjax()) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
                exit;
            }
            http_response_code(403);
            require_once APP_ROOT . '/app/views/errors/403.php';
            exit;
        }
    }
    
    /**
     * Check if user has specific role
     * 
     * @param string $role Role to check
     * @return bool
     */
    public static function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user role
     * 
     * @return string|null
     */
    public static function userRole() {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public static function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @return bool
     */
    public static function verifyCsrf($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    private static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
