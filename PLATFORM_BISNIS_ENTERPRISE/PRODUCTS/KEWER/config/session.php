<?php
// Load path configuration first
require_once __DIR__ . '/path.php';

// Enable full error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check session timeout
function checkSessionTimeout() {
    if (!isLoggedIn()) return false;
    
    // Set last activity time if not set
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Check if session has expired
    $lifetime = SESSION_LIFETIME;
    if (time() - $_SESSION['last_activity'] > $lifetime) {
        // Session expired, destroy it
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $user = query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    return $user[0] ?? null;
}

// Check user role
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        // Check if this is an API request
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unauthorized - Please login first']);
            exit();
        }
        header('Location: login.php');
        exit();
    }
    
    // Check session timeout
    if (!checkSessionTimeout()) {
        // Check if this is an API request
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Session expired - Please login again']);
            exit();
        }
        header('Location: login.php?timeout=1');
        exit();
    }
}

// Redirect if not authorized
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: dashboard.php');
        exit();
    }
}

// Get current kantor for single office structure
function getCurrentCabang() {
    // Return single office id (1) for simplified structure
    return 1;
}
