<?php
// JWT Helper for API Authentication
// Uses firebase/php-jwt library

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper {
    private static $secret_key = 'kewer-secret-key-2024-change-in-production';
    private static $algorithm = 'HS256';
    private static $token_expiry = 86400; // 24 hours
    
    /**
     * Generate JWT token for user
     */
    public static function generateToken($user_id, $username, $role, $cabang_id = null) {
        $issued_at = time();
        $expire = $issued_at + self::$token_expiry;
        
        $payload = [
            'iat' => $issued_at,
            'exp' => $expire,
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role,
            'cabang_id' => $cabang_id
        ];
        
        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }
    
    /**
     * Validate and decode JWT token
     */
    public static function validateToken($token) {
        try {
            if (empty($token)) {
                return null;
            }
            
            // Remove 'Bearer ' prefix if present
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get current user from JWT token
     */
    public static function getCurrentUser() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        $payload = self::validateToken($token);
        if ($payload) {
            return [
                'id' => $payload['user_id'],
                'username' => $payload['username'],
                'role' => $payload['role'],
                'cabang_id' => $payload['cabang_id']
            ];
        }
        
        return null;
    }
    
    /**
     * Check if request is authenticated
     */
    public static function isAuthenticated() {
        return self::getCurrentUser() !== null;
    }
    
    /**
     * Require authentication - send 401 if not authenticated
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized - Invalid or missing token']);
            exit();
        }
    }
}

// Backward compatibility: Allow old token for transition period
function isLegacyToken($token) {
    return $token === 'Bearer kewer-api-token-2024';
}
