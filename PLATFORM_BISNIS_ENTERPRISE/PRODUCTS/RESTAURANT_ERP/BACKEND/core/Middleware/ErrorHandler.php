<?php

/**
 * Error Handler Middleware
 * Standardized error handling for the application with structured logging
 */

class ErrorHandler
{
    private static $logger = null;

    public static function init()
    {
        // Initialize logger
        self::$logger = Logger::getInstance();
        
        // Set error and exception handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorType = self::getErrorType($errno);
        $level = self::getLogLevel($errno);

        self::$logger->log($level, $errstr, [
            'error_type' => $errorType,
            'file' => $errfile,
            'line' => $errline,
            'error_code' => $errno
        ]);

        // Don't execute PHP internal error handler
        return true;
    }

    public static function handleException($exception)
    {
        self::$logger->exception($exception, [
            'exception_type' => get_class($exception)
        ]);

        // Return error response if in API context
        if (self::isApiRequest()) {
            // Use exception code if it's a valid HTTP status code, otherwise default to 500
            $statusCode = ($exception->getCode() >= 400 && $exception->getCode() < 600) ? $exception->getCode() : 500;
            
            // Hide trace in production
            $debug = getenv('APP_DEBUG') === 'true';
            $context = $debug ? ['trace' => $exception->getTraceAsString()] : [];
            
            Response::error(
                $exception->getMessage(),
                $statusCode,
                $context
            );
        }
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::$logger->critical($error['message'], [
                'error_type' => 'Fatal Error',
                'file' => $error['file'],
                'line' => $error['line'],
                'error_code' => $error['type']
            ]);

            if (self::isApiRequest()) {
                Response::error(
                    'Internal server error',
                    500
                );
            }
        }
    }

    private static function getErrorType($errno)
    {
        $types = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];

        return $types[$errno] ?? 'Unknown Error';
    }

    private static function getLogLevel($errno)
    {
        $levels = [
            E_ERROR => Logger::CRITICAL,
            E_WARNING => Logger::WARNING,
            E_PARSE => Logger::CRITICAL,
            E_NOTICE => Logger::DEBUG,
            E_CORE_ERROR => Logger::CRITICAL,
            E_CORE_WARNING => Logger::WARNING,
            E_COMPILE_ERROR => Logger::CRITICAL,
            E_COMPILE_WARNING => Logger::WARNING,
            E_USER_ERROR => Logger::ERROR,
            E_USER_WARNING => Logger::WARNING,
            E_USER_NOTICE => Logger::DEBUG,
            E_STRICT => Logger::DEBUG,
            E_RECOVERABLE_ERROR => Logger::ERROR,
            E_DEPRECATED => Logger::WARNING,
            E_USER_DEPRECATED => Logger::WARNING
        ];

        return $levels[$errno] ?? Logger::ERROR;
    }

    private static function isApiRequest()
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api') === 0;
    }
}

// Initialize error handler
ErrorHandler::init();
