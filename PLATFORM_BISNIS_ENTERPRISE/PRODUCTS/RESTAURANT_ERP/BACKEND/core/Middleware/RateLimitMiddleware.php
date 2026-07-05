<?php

/**
 * EBP Core - Rate Limiting Middleware
 * 
 * This middleware implements rate limiting for API endpoints
 * to prevent abuse and ensure fair usage
 * 
 * @package EBP\Core\Middleware
 * @version 1.0.0
 */

class RateLimitMiddleware
{
    private static $requestLog = [];
    private static $cleanupInterval = 3600; // Cleanup old logs every hour
    private static $lastCleanup = 0;

    /**
     * Check if request is within rate limit
     * 
     * @param string $identifier Unique identifier (IP address or user ID)
     * @param int $maxRequests Maximum requests allowed
     * @param int $windowSeconds Time window in seconds
     * @return bool True if within limit
     */
    public function check($identifier, $maxRequests = 100, $windowSeconds = 60)
    {
        $this->cleanupOldLogs();

        $currentTime = time();
        $windowStart = $currentTime - $windowSeconds;

        // Initialize log for this identifier if not exists
        if (!isset(self::$requestLog[$identifier])) {
            self::$requestLog[$identifier] = [];
        }

        // Remove requests outside the time window
        self::$requestLog[$identifier] = array_filter(
            self::$requestLog[$identifier],
            function($timestamp) use ($windowStart) {
                return $timestamp >= $windowStart;
            }
        );

        // Check if limit exceeded
        if (count(self::$requestLog[$identifier]) >= $maxRequests) {
            return false;
        }

        // Log this request
        self::$requestLog[$identifier][] = $currentTime;

        return true;
    }

    /**
     * Get remaining requests for identifier
     * 
     * @param string $identifier Unique identifier
     * @param int $maxRequests Maximum requests allowed
     * @param int $windowSeconds Time window in seconds
     * @return array Rate limit info
     */
    public function getRateLimitInfo($identifier, $maxRequests = 100, $windowSeconds = 60)
    {
        $this->cleanupOldLogs();

        $currentTime = time();
        $windowStart = $currentTime - $windowSeconds;

        if (!isset(self::$requestLog[$identifier])) {
            return [
                'remaining' => $maxRequests,
                'reset' => $currentTime + $windowSeconds,
                'limit' => $maxRequests
            ];
        }

        // Count requests within window
        $requestCount = 0;
        foreach (self::$requestLog[$identifier] as $timestamp) {
            if ($timestamp >= $windowStart) {
                $requestCount++;
            }
        }

        // Calculate reset time (oldest request + window)
        $oldestRequest = min(self::$requestLog[$identifier]);
        $resetTime = $oldestRequest + $windowSeconds;

        return [
            'remaining' => max(0, $maxRequests - $requestCount),
            'reset' => $resetTime,
            'limit' => $maxRequests
        ];
    }

    /**
     * Clean up old request logs
     * 
     * @return void
     */
    private function cleanupOldLogs()
    {
        $currentTime = time();

        // Run cleanup at most once per interval
        if ($currentTime - self::$lastCleanup < self::$cleanupInterval) {
            return;
        }

        self::$lastCleanup = $currentTime;
        $cutoffTime = $currentTime - 3600; // Remove logs older than 1 hour

        foreach (self::$requestLog as $identifier => $timestamps) {
            self::$requestLog[$identifier] = array_filter(
                $timestamps,
                function($timestamp) use ($cutoffTime) {
                    return $timestamp >= $cutoffTime;
                }
            );

            // Remove empty entries
            if (empty(self::$requestLog[$identifier])) {
                unset(self::$requestLog[$identifier]);
            }
        }
    }

    /**
     * Static handle method for middleware chain
     * 
     * @param array $request Request data
     * @param int $maxRequests Maximum requests allowed
     * @param int $windowSeconds Time window in seconds
     * @return array Request data if within limit
     */
    public static function handle($request, $maxRequests = 100, $windowSeconds = 60)
    {
        $middleware = new self();
        
        // Use IP address as identifier (or user_id if authenticated)
        $identifier = $request['user_id'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        if (!$middleware->check($identifier, $maxRequests, $windowSeconds)) {
            $rateLimitInfo = $middleware->getRateLimitInfo($identifier, $maxRequests, $windowSeconds);
            
            header('X-RateLimit-Limit: ' . $rateLimitInfo['limit']);
            header('X-RateLimit-Remaining: ' . $rateLimitInfo['remaining']);
            header('X-RateLimit-Reset: ' . $rateLimitInfo['reset']);
            header('Retry-After: ' . ($rateLimitInfo['reset'] - time()));
            
            Response::error('Rate limit exceeded. Please try again later.', 429);
        }
        
        // Add rate limit headers to response
        $rateLimitInfo = $middleware->getRateLimitInfo($identifier, $maxRequests, $windowSeconds);
        header('X-RateLimit-Limit: ' . $rateLimitInfo['limit']);
        header('X-RateLimit-Remaining: ' . $rateLimitInfo['remaining']);
        header('X-RateLimit-Reset: ' . $rateLimitInfo['reset']);
        
        return $request;
    }

    /**
     * Clear rate limit for specific identifier
     * 
     * @param string $identifier Unique identifier
     * @return void
     */
    public static function clear($identifier)
    {
        if (isset(self::$requestLog[$identifier])) {
            unset(self::$requestLog[$identifier]);
        }
    }

    /**
     * Clear all rate limits
     * 
     * @return void
     */
    public static function clearAll()
    {
        self::$requestLog = [];
    }
}
