<?php
/**
 * Error Handling Helper
 * 
 * Provides comprehensive error handling and logging
 * Improves debugging and user experience
 */

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Don't handle suppressed errors
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorTypes = [
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
    
    $errorType = $errorTypes[$errno] ?? 'Unknown Error';
    
    // Log error
    error_log("[$errorType] $errstr in $errfile on line $errline");
    
    // Show error in development
    if (getenv('APP_ENV') === 'development' || !getenv('APP_ENV')) {
        echo "<div style='background: #ffeeee; border: 1px solid #ff0000; padding: 10px; margin: 10px;'>";
        echo "<strong>$errorType:</strong> $errstr<br>";
        echo "<em>File:</em> $errfile<br>";
        echo "<em>Line:</em> $errline<br>";
        echo "</div>";
    }
    
    return true;
}

// Custom exception handler
function customExceptionHandler($exception) {
    // Log exception
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    // Show exception in development
    if (getenv('APP_ENV') === 'development' || !getenv('APP_ENV')) {
        echo "<div style='background: #ffeeee; border: 1px solid #ff0000; padding: 10px; margin: 10px;'>";
        echo "<strong>Uncaught Exception:</strong> " . $exception->getMessage() . "<br>";
        echo "<em>File:</em> " . $exception->getFile() . "<br>";
        echo "<em>Line:</em> " . $exception->getLine() . "<br>";
        echo "<pre style='background: #f5f5f5; padding: 10px;'>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        // Show user-friendly error in production
        http_response_code(500);
        echo "<div style='background: #ffeeee; border: 1px solid #ff0000; padding: 10px; margin: 10px;'>";
        echo "<strong>Terjadi kesalahan sistem.</strong> Silakan hubungi administrator.";
        echo "</div>";
    }
}

// Register error handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

// Log application errors
function logError($message, $context = []) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if (!empty($context)) {
        $logMessage .= " - Context: " . json_encode($context);
    }
    error_log($logMessage);
    
    // Also write to application log file
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

// Log application info
function logInfo($message, $context = []) {
    $logMessage = date('Y-m-d H:i:s') . " [INFO] - " . $message;
    if (!empty($context)) {
        $logMessage .= " - Context: " . json_encode($context);
    }
    
    $logFile = __DIR__ . '/../logs/app.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

// Log application debug
function logDebug($message, $context = []) {
    if (getenv('APP_ENV') === 'development' || !getenv('APP_ENV')) {
        $logMessage = date('Y-m-d H:i:s') . " [DEBUG] - " . $message;
        if (!empty($context)) {
            $logMessage .= " - Context: " . json_encode($context);
        }
        
        $logFile = __DIR__ . '/../logs/debug.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
    }
}

// Handle database errors
function handleDatabaseError($conn, $query = '') {
    $error = $conn->error;
    logError("Database Error: " . $error, ['query' => $query]);
    
    if (getenv('APP_ENV') === 'development' || !getenv('APP_ENV')) {
        return [
            'success' => false,
            'error' => 'Database Error: ' . $error,
            'query' => $query
        ];
    }
    
    return [
        'success' => false,
        'error' => 'Terjadi kesalahan database. Silakan coba lagi.'
    ];
}

// Show error message to user
function showError($message, $type = 'danger') {
    $alertTypes = [
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'success' => 'alert-success',
        'info' => 'alert-info'
    ];
    
    $alertClass = $alertTypes[$type] ?? 'alert-danger';
    
    echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>";
    echo $message;
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";
}

// Show success message to user
function showSuccess($message) {
    showError($message, 'success');
}

// Show warning message to user
function showWarning($message) {
    showError($message, 'warning');
}

// Show info message to user
function showInfo($message) {
    showError($message, 'info');
}

// Redirect with error message
function redirectWithError($url, $message) {
    $_SESSION['error_message'] = $message;
    header('Location: ' . $url);
    exit();
}

// Redirect with success message
function redirectWithSuccess($url, $message) {
    $_SESSION['success_message'] = $message;
    header('Location: ' . $url);
    exit();
}

// Get flash message
function getFlashMessage() {
    $message = '';
    if (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return ['type' => 'danger', 'message' => $message];
    }
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        return ['type' => 'success', 'message' => $message];
    }
    return null;
}
?>
