<?php

declare(strict_types=1);

/**
 * EBP Core - API Response Handler
 * 
 * This is a core component of the Enterprise Business Platform
 * Used for standardized API responses across all EBP products
 * 
 * @package EBP\Core\API
 * @version 1.0.0
 */

class Response
{
    /**
     * Send JSON response
     * 
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     * @return never
     */
    public static function json(mixed $data, int $statusCode = 200): never
    {
        // Ensure status code is valid
        $statusCode = (int)$statusCode;
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 500;
        }
        
        http_response_code($statusCode);
        header("Content-Type: application/json");
        echo json_encode($data);
        exit;
    }

    /**
     * Send success response
     * 
     * @param array $data Response data
     * @param string $message Success message
     * @return never
     */
    public static function success(array $data = [], string $message = 'Success'): never
    {
        self::json([
            "success" => true,
            "message" => $message,
            "data" => $data
        ], 200);
    }

    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Additional error details
     * @return never
     */
    public static function error(string $message, int $statusCode = 400, array $errors = []): never
    {
        self::json([
            "success" => false,
            "message" => $message,
            "errors" => $errors
        ], $statusCode);
    }

    /**
     * Send validation error response
     * 
     * @param array $errors Validation errors
     * @return never
     */
    public static function validationError(array $errors): never
    {
        self::error("Validation failed", 422, $errors);
    }

    /**
     * Send not found response
     * 
     * @param string $message Not found message
     * @return never
     */
    public static function notFound(string $message = 'Resource not found'): never
    {
        self::error($message, 404);
    }

    /**
     * Send unauthorized response
     * 
     * @param string $message Unauthorized message
     * @return never
     */
    public static function unauthorized(string $message = 'Unauthorized'): never
    {
        self::error($message, 401);
    }

    /**
     * Send forbidden response
     * 
     * @param string $message Forbidden message
     * @return never
     */
    public static function forbidden(string $message = 'Forbidden'): never
    {
        self::error($message, 403);
    }

    /**
     * Send server error response
     * 
     * @param string $message Server error message
     * @return never
     */
    public static function serverError(string $message = 'Internal server error'): never
    {
        self::error($message, 500);
    }

    /**
     * Send paginated response
     * 
     * @param array $data Response data
     * @param int $total Total records
     * @param int $page Current page
     * @param int $limit Records per page
     * @return never
     */
    public static function paginated(array $data, int $total, int $page, int $limit): never
    {
        $totalPages = (int)ceil($total / $limit);
        
        header("X-Total-Count: $total");
        header("X-Page-Count: $totalPages");
        header("X-Current-Page: $page");
        header("X-Per-Page: $limit");
        
        self::success([
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => $totalPages
            ]
        ]);
    }
}
