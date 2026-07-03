<?php

/**
 * EBP Core - API Response Handler
 * 
 * This is a core component of the Enterprise Business Platform
 * Used for standardized API responses across all EBP products
 * 
 * @package EBP\Core\API
 * @version 1.0.0
 */

namespace EBP\Core\API;

class Response
{
    /**
     * Send JSON response
     * 
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     * @return void
     */
    public static function json($data, $statusCode = 200)
    {
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
     * @return void
     */
    public static function success($data = [], $message = 'Success')
    {
        self::json([
            "success" => true,
            "message" => $message,
            "data" => $data
        ]);
    }

    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Additional error details
     * @return void
     */
    public static function error($message, $statusCode = 400, $errors = [])
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
     * @return void
     */
    public static function validationError($errors)
    {
        self::error("Validation failed", 422, $errors);
    }

    /**
     * Send not found response
     * 
     * @param string $message Not found message
     * @return void
     */
    public static function notFound($message = 'Resource not found')
    {
        self::error($message, 404);
    }

    /**
     * Send unauthorized response
     * 
     * @param string $message Unauthorized message
     * @return void
     */
    public static function unauthorized($message = 'Unauthorized')
    {
        self::error($message, 401);
    }

    /**
     * Send forbidden response
     * 
     * @param string $message Forbidden message
     * @return void
     */
    public static function forbidden($message = 'Forbidden')
    {
        self::error($message, 403);
    }

    /**
     * Send server error response
     * 
     * @param string $message Server error message
     * @return void
     */
    public static function serverError($message = 'Internal server error')
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
     * @return void
     */
    public static function paginated($data, $total, $page, $limit)
    {
        $totalPages = ceil($total / $limit);
        
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
