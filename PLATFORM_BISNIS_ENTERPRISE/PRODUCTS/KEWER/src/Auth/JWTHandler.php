<?php
namespace Kewer\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JWTHandler {
    private static $secret;
    private static $expireHours;
    private static $refreshExpireDays;
    
    public static function init() {
        self::$secret = JWT_SECRET;
        self::$expireHours = JWT_EXPIRE_HOURS;
        self::$refreshExpireDays = JWT_REFRESH_EXPIRE_DAYS;
    }
    
    /**
     * Generate JWT token for user
     */
    public static function generateToken($userId, $userData = []) {
        self::init();
        
        $issuedAt = time();
        $expire = $issuedAt + (self::$expireHours * 3600);
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'user_id' => $userId,
            'data' => $userData
        ];
        
        return JWT::encode($payload, self::$secret, 'HS256');
    }
    
    /**
     * Generate refresh token
     */
    public static function generateRefreshToken($userId) {
        self::init();
        
        $issuedAt = time();
        $expire = $issuedAt + (self::$refreshExpireDays * 24 * 3600);
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'user_id' => $userId,
            'type' => 'refresh'
        ];
        
        return JWT::encode($payload, self::$secret, 'HS256');
    }
    
    /**
     * Validate and decode JWT token
     */
    public static function validateToken($token) {
        self::init();
        
        try {
            $decoded = JWT::decode($token, new Key(self::$secret, 'HS256'));
            return (array)$decoded;
        } catch (ExpiredException $e) {
            return ['error' => 'Token expired', 'code' => 401];
        } catch (SignatureInvalidException $e) {
            return ['error' => 'Invalid token signature', 'code' => 401];
        } catch (\Exception $e) {
            return ['error' => 'Invalid token', 'code' => 401];
        }
    }
    
    /**
     * Get user ID from token
     */
    public static function getUserIdFromToken($token) {
        $decoded = self::validateToken($token);
        
        if (isset($decoded['error'])) {
            return null;
        }
        
        return $decoded['user_id'] ?? null;
    }
    
    /**
     * Refresh access token using refresh token
     */
    public static function refreshToken($refreshToken) {
        $decoded = self::validateToken($refreshToken);
        
        if (isset($decoded['error'])) {
            return ['error' => 'Invalid refresh token', 'code' => 401];
        }
        
        if (!isset($decoded['type']) || $decoded['type'] !== 'refresh') {
            return ['error' => 'Invalid refresh token type', 'code' => 401];
        }
        
        $userId = $decoded['user_id'];
        
        // Get user data from database
        global $conn;
        $user = query("SELECT id, username, role, cabang_id FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return ['error' => 'User not found', 'code' => 404];
        }
        
        $userData = [
            'username' => $user[0]['username'],
            'role' => $user[0]['role'],
            'cabang_id' => $user[0]['cabang_id']
        ];
        
        $newToken = self::generateToken($userId, $userData);
        $newRefreshToken = self::generateRefreshToken($userId);
        
        return [
            'access_token' => $newToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => self::$expireHours * 3600
        ];
    }
}
?>
