<?php
/**
 * CSRF Protection Helper
 * 
 * Provides CSRF token generation and validation for form security
 * Prevents Cross-Site Request Forgery attacks
 */

// Generate CSRF token
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// Get CSRF token for form
function getCsrfToken() {
    return generateCsrfToken();
}

// Validate CSRF token
function validateCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Generate CSRF input field for HTML forms
function csrfField() {
    $token = getCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// Validate CSRF from POST request
function validateCsrfRequest() {
    // Skip CSRF validation in development mode for testing
    if (defined('APP_ENV') && APP_ENV === 'development') {
        return;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        
        if (!validateCsrfToken($token)) {
            // Log the attempt
            error_log('CSRF validation failed from IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            
            // Return error response
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['error' => 'CSRF token validation failed']);
                exit();
            } else {
                http_response_code(403);
                die('CSRF token validation failed. Please refresh the page and try again.');
            }
        }
    }
}

// Regenerate CSRF token (useful after login)
function regenerateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
?>
