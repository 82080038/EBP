<?php
// Resolve project root locally (don't depend on BASE_PATH to avoid circular includes)
$__envBasePath = dirname(__DIR__);
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $__envBasePath);
}

// Load environment configuration
$vendorAutoload = $__envBasePath . '/vendor/autoload.php';
$dotenvLoaded = false;

if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
    
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($__envBasePath);
            $dotenv->load();
            $dotenvLoaded = true;
        } catch (Exception $e) {
            // If .env file doesn't exist, use defaults
            defineEnvDefaults();
        }
    } else {
        defineEnvDefaults();
    }
} else {
    // If vendor doesn't exist, use defaults
    defineEnvDefaults();
}

// Define environment defaults
function defineEnvDefaults() {
    define('APP_NAME', 'Koperasi');
    define('APP_ENV', 'development');
    define('APP_DEBUG', true);
    define('APP_URL', 'http://localhost/kewer');
    
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'kewer');
    define('DB_USER', 'root');
    define('DB_PASS', 'root');
    
    define('JWT_SECRET', 'kewer-super-secret-jwt-key-2024-change-in-production');
    define('JWT_EXPIRE_HOURS', 24);
    define('JWT_REFRESH_EXPIRE_DAYS', 7);
    
    define('UPLOAD_MAX_SIZE', 5242880);
    define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf');
    
    define('CACHE_DRIVER', 'file');
    define('CACHE_PREFIX', 'kewer_');
    
    define('RATE_LIMIT_ENABLED', true);
    define('RATE_LIMIT_PER_MINUTE', 60);
    
    define('CSRF_TOKEN_NAME', 'csrf_token');
    define('SESSION_LIFETIME', 7200);
}

// Define constants from environment variables (or defaults)
if (!defined('APP_NAME')) define('APP_NAME', $_ENV['APP_NAME'] ?? 'Koperasi');
if (!defined('APP_ENV')) define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
if (!defined('APP_DEBUG')) define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
if (!defined('APP_URL')) define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/kewer');

if (!defined('DB_HOST')) define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', $_ENV['DB_NAME'] ?? 'kewer');
if (!defined('DB_USER')) define('DB_USER', $_ENV['DB_USER'] ?? 'root');
if (!defined('DB_PASS')) define('DB_PASS', $_ENV['DB_PASS'] ?? 'root');

if (!defined('JWT_SECRET')) define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'kewer-super-secret-jwt-key-2024-change-in-production');
if (!defined('JWT_EXPIRE_HOURS')) define('JWT_EXPIRE_HOURS', (int)($_ENV['JWT_EXPIRE_HOURS'] ?? 24));
if (!defined('JWT_REFRESH_EXPIRE_DAYS')) define('JWT_REFRESH_EXPIRE_DAYS', (int)($_ENV['JWT_REFRESH_EXPIRE_DAYS'] ?? 7));

if (!defined('UPLOAD_MAX_SIZE')) define('UPLOAD_MAX_SIZE', (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880));
if (!defined('ALLOWED_FILE_TYPES')) define('ALLOWED_FILE_TYPES', $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,pdf');

if (!defined('CACHE_DRIVER')) define('CACHE_DRIVER', $_ENV['CACHE_DRIVER'] ?? 'file');
if (!defined('CACHE_PREFIX')) define('CACHE_PREFIX', $_ENV['CACHE_PREFIX'] ?? 'kewer_');

if (!defined('RATE_LIMIT_ENABLED')) define('RATE_LIMIT_ENABLED', filter_var($_ENV['RATE_LIMIT_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
if (!defined('RATE_LIMIT_PER_MINUTE')) define('RATE_LIMIT_PER_MINUTE', (int)($_ENV['RATE_LIMIT_PER_MINUTE'] ?? 60));

if (!defined('CSRF_TOKEN_NAME')) define('CSRF_TOKEN_NAME', $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200));

// v2.3.1 Feature Flags Configuration (In-App Notifications)
// External service integrations removed - using in-app notification system
