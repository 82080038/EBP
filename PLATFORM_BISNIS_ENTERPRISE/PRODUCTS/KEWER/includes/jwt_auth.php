<?php
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/src/Auth/JWTHandler.php';

use Kewer\Auth\JWTHandler;

/**
 * Authenticate API request using JWT
 */
function authenticateAPI() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization header missing']);
        exit();
    }
    
    // Extract token from "Bearer <token>"
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid authorization format']);
        exit();
    }
    
    $token = $matches[1];
    $decoded = JWTHandler::validateToken($token);
    
    if (isset($decoded['error'])) {
        http_response_code($decoded['code']);
        echo json_encode(['error' => $decoded['error']]);
        exit();
    }
    
    return $decoded;
}

/**
 * Get current authenticated user from JWT token
 */
function getAuthenticatedUser() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (!$authHeader) {
        return null;
    }
    
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return null;
    }
    
    $token = $matches[1];
    $decoded = JWTHandler::validateToken($token);
    
    if (isset($decoded['error'])) {
        return null;
    }
    
    return $decoded;
}

/**
 * Check if user has required role
 */
function hasAPIRole($requiredRole) {
    $user = getAuthenticatedUser();
    
    if (!$user) {
        return false;
    }
    
    $userData = $user['data'] ?? [];
    $userRole = $userData['role'] ?? '';
    
    // Bos has all permissions
    if ($userRole === 'bos') {
        return true;
    }
    
    return $userRole === $requiredRole;
}

/**
 * Require specific role for API access
 */
function requireAPIRole($role) {
    if (!hasAPIRole($role)) {
        http_response_code(403);
        echo json_encode(['error' => 'Insufficient permissions']);
        exit();
    }
}
?>
