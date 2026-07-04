<?php
// Enable error reporting for development - report all errors
error_reporting(E_ALL | -1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Path Configuration
// Define base path for the application
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load environment configuration first
require_once BASE_PATH . '/config/env.php';

// Define common paths
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('MODELS_PATH', BASE_PATH . '/models');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');
define('API_PATH', BASE_PATH . '/api');
define('PAGES_PATH', BASE_PATH . '/pages');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('TESTS_PATH', BASE_PATH . '/tests');

// Define URL base from environment
define('BASE_URL', rtrim(APP_URL, '/'));

// Helper function to get full path
function basePath($path = '') {
    return BASE_PATH . '/' . ltrim($path, '/');
}

// Helper function to get full URL
function baseUrl($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}
