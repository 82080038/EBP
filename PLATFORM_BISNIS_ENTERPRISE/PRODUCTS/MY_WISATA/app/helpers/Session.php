<?php
/**
 * MyWisata Application - Session Class
 * 
 * Handles session management with security features.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class Session {
    
    /**
     * Start session with secure configuration
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 1800,     // 30 minutes
                'path' => '/',
                'httponly' => true,     // JS cannot access
                'secure' => APP_ENV === 'production', // HTTPS only in production
                'samesite' => 'Strict'
            ]);
            session_start();
        }
        
        // Regenerate session ID every 30 minutes
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
        
        // Session timeout check
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        } elseif (time() - $_SESSION['last_activity'] > 1800) {
            self::destroy();
            header('Location: ' . BASE_URL . 'auth/login?timeout=1');
            exit;
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Set session value
     * 
     * @param string $key Key
     * @param mixed $value Value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     * 
     * @param string $key Key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     * 
     * @param string $key Key
     * @return bool
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     * 
     * @param string $key Key
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Flash message - set message that will be available only on next request
     * 
     * @param string $key Key
     * @param mixed $value Value
     */
    public static function flash($key, $value) {
        $_SESSION['flash'][$key] = $value;
    }
    
    /**
     * Get flash message
     * 
     * @param string $key Key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function getFlash($key, $default = null) {
        $value = $_SESSION['flash'][$key] ?? $default;
        unset($_SESSION['flash'][$key]);
        return $value;
    }
    
    /**
     * Check if flash message exists
     * 
     * @param string $key Key
     * @return bool
     */
    public static function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
    
    /**
     * Get session ID
     * 
     * @return string
     */
    public static function id() {
        return session_id();
    }
}
