<?php
/**
 * Global Error Handler
 * 
 * Provides centralized error handling and logging
 */

// Error logging directory
define('ERROR_LOG_DIR', __DIR__ . '/../logs');

// Ensure error log directory exists
if (!is_dir(ERROR_LOG_DIR)) {
    mkdir(ERROR_LOG_DIR, 0755, true);
}

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Don't handle suppressed errors
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error_types = [
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
    
    $error_type = $error_types[$errno] ?? 'Unknown Error';
    
    // Log error to file
    $log_message = sprintf(
        "[%s] %s: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $error_type,
        $errstr,
        $errfile,
        $errline
    );
    
    error_log($log_message, 3, ERROR_LOG_DIR . '/error.log');
    
    // Show user-friendly error in development
    if (APP_DEBUG) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<strong>$error_type:</strong> $errstr<br>";
        echo "<small>File: $errfile Line: $errline</small>";
        echo "</div>";
    }
    
    // Don't execute PHP internal error handler
    return true;
}

// Custom exception handler
function customExceptionHandler($exception) {
    $log_message = sprintf(
        "[%s] Uncaught Exception: %s in %s on line %d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    error_log($log_message, 3, ERROR_LOG_DIR . '/error.log');
    
    // Check if API request
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => APP_DEBUG ? $exception->getMessage() : 'Internal server error'
        ]);
    } else {
        if (APP_DEBUG) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<strong>Uncaught Exception:</strong> " . htmlspecialchars($exception->getMessage()) . "<br>";
            echo "<small>File: " . htmlspecialchars($exception->getFile()) . " Line: " . $exception->getLine() . "</small>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<strong>Terjadi kesalahan sistem.</strong> Silakan coba lagi atau hubungi administrator.";
            echo "</div>";
        }
    }
}

// Register error handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

// Log info function
function logInfo($message, $context = []) {
    $log_message = sprintf(
        "[%s] INFO: %s %s\n",
        date('Y-m-d H:i:s'),
        $message,
        $context ? json_encode($context) : ''
    );
    error_log($log_message, 3, ERROR_LOG_DIR . '/app.log');
}

// Log error function
function logError($message, $context = []) {
    $log_message = sprintf(
        "[%s] ERROR: %s %s\n",
        date('Y-m-d H:i:s'),
        $message,
        $context ? json_encode($context) : ''
    );
    error_log($log_message, 3, ERROR_LOG_DIR . '/error.log');
}

// Log warning function
function logWarning($message, $context = []) {
    $log_message = sprintf(
        "[%s] WARNING: %s %s\n",
        date('Y-m-d H:i:s'),
        $message,
        $context ? json_encode($context) : ''
    );
    error_log($log_message, 3, ERROR_LOG_DIR . '/warning.log');
}
