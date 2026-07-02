<?php

/**
 * Error Handler Middleware
 * Standardized error handling for the application
 */

class ErrorHandler
{
    private static $errorLogPath = null;

    public static function init()
    {
        self::$errorLogPath = __DIR__ . '/../../logs/error.log';
        
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

        $error = [
            'type' => self::getErrorType($errno),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        self::logError($error);

        // Don't execute PHP internal error handler
        return true;
    }

    public static function handleException($exception)
    {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        self::logError($error);

        // Return error response if in API context
        if (self::isApiRequest()) {
            Response::error(
                $exception->getMessage(),
                500,
                ['trace' => $error['trace']]
            );
        }
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorData = [
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => date('Y-m-d H:i:s')
            ];

            self::logError($errorData);

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

    private static function logError($error)
    {
        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            $error['timestamp'],
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );

        if (isset($error['trace'])) {
            $logMessage .= "Stack trace:\n" . $error['trace'] . "\n";
        }

        // Ensure log directory exists
        $logDir = dirname(self::$errorLogPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        error_log($logMessage, 3, self::$errorLogPath);
    }

    private static function isApiRequest()
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api') === 0;
    }
}

// Initialize error handler
ErrorHandler::init();
