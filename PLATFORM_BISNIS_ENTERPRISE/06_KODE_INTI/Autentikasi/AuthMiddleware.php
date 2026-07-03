<?php

/**
 * EBP Core - Authentication Middleware
 * 
 * This is a core component of the Enterprise Business Platform
 * Used for authentication across all EBP products
 * 
 * @package EBP\Core\Authentication
 * @version 1.0.0
 */

namespace EBP\Core\Authentication;

use EBP\Core\Authentication\JWT;
use EBP\Core\API\Response;

class AuthMiddleware
{
    private $jwt;

    public function __construct($jwt = null)
    {
        $this->jwt = $jwt ?? new JWT();
    }

    /**
     * Authenticate request using JWT token
     * 
     * @return array Decoded JWT payload
     * @throws \Exception If authentication fails
     */
    public function authenticate()
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            throw new \Exception("Authorization header missing", 401);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        $payload = $this->jwt->decode($token);

        if (!$payload) {
            throw new \Exception("Invalid or expired token", 401);
        }

        return $payload;
    }

    /**
     * Static handle method for middleware chain
     * 
     * @param array $request Request data
     * @return array Request data with user context
     */
    public static function handle($request)
    {
        $middleware = new self();
        $payload = $middleware->authenticate();
        
        // Set user context in request
        $request['user_id'] = $payload['user_id'] ?? null;
        $request['tenant_id'] = $payload['tenant_id'] ?? null;
        $request['branch_id'] = $payload['branch_id'] ?? null;
        $request['username'] = $payload['username'] ?? null;
        $request['role'] = $payload['role'] ?? null;
        
        return $request;
    }

    /**
     * Check if user has specific role
     * 
     * @param array $payload JWT payload
     * @param string $role Role to check
     * @return bool True if user has role
     */
    public function hasRole($payload, $role)
    {
        return isset($payload['role']) && $payload['role'] === $role;
    }

    /**
     * Check if user belongs to specific tenant
     * 
     * @param array $payload JWT payload
     * @param int $tenantId Tenant ID to check
     * @return bool True if user belongs to tenant
     */
    public function belongsToTenant($payload, $tenantId)
    {
        return isset($payload['tenant_id']) && $payload['tenant_id'] == $tenantId;
    }

    /**
     * Check if user belongs to specific branch
     * 
     * @param array $payload JWT payload
     * @param int $branchId Branch ID to check
     * @return bool True if user belongs to branch
     */
    public function belongsToBranch($payload, $branchId)
    {
        return isset($payload['branch_id']) && $payload['branch_id'] == $branchId;
    }
}
