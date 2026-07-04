<?php
/**
 * MyWisata Application - Main Configuration
 * 
 * This file contains the main application configuration settings.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// ============================================
// APPLICATION SETTINGS
// ============================================
define('APP_NAME', 'MyWisata Application');
define('APP_ENV', 'development'); // development, staging, production
define('APP_DEBUG', true); // true in development, false in production
define('APP_VERSION', '1.0.0');

// ============================================
// URL SETTINGS
// ============================================
define('BASE_URL', 'http://localhost/mywisata/');
define('ASSETS_URL', BASE_URL . 'public/assets/');

// ============================================
// TIMEZONE
// ============================================
date_default_timezone_set('Asia/Jakarta');

// ============================================
// SECURITY SETTINGS
// ============================================
// CSRF Token (generated per session in actual implementation)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('CSRF_TOKEN', bin2hex(random_bytes(32)));

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', APP_ENV === 'production');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', 1800); // 30 minutes
}

// ============================================
// FILE UPLOAD SETTINGS
// ============================================
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/wav', 'audio/ogg']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// ============================================
// PAGINATION SETTINGS
// ============================================
define('ITEMS_PER_PAGE', 20);
define('MAX_ITEMS_PER_PAGE', 100);

// ============================================
// RATE LIMITING
// ============================================
define('RATE_LIMIT_PER_MINUTE', 60);
define('RATE_LIMIT_PER_HOUR', 1000);

// ============================================
// ERROR REPORTING
// ============================================
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// ============================================
// LOGGING
// ============================================
define('LOG_PATH', APP_ROOT . '/logs/');
define('ERROR_LOG_FILE', LOG_PATH . 'error.log');
define('AUDIT_LOG_FILE', LOG_PATH . 'audit.log');

// ============================================
// PATHS
// ============================================
define('UPLOAD_PATH', APP_ROOT . '/public/uploads/');
define('UPLOAD_URL', BASE_URL . 'public/uploads/');

// ============================================
// EMAIL SETTINGS (for notifications)
// ============================================
define('MAIL_FROM', 'admin@mywisata.com');
define('MAIL_FROM_NAME', 'MyWisata App');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_ENCRYPTION', 'tls');

// ============================================
// MAP SETTINGS (OpenStreetMap/Leaflet)
// ============================================
define('MAP_DEFAULT_LAT', -6.2088);
define('MAP_DEFAULT_LNG', 106.8456);
define('MAP_DEFAULT_ZOOM', 13);
define('MAP_TILE_LAYER', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');

// ============================================
// AI CHAT SETTINGS
// ============================================
define('AI_CHAT_ENABLED', true);
define('AI_CHAT_MODEL', 'rule-based'); // rule-based, openai, etc.

// ============================================
// AUDIO GUIDE SETTINGS
// ============================================
define('AUDIO_GUIDE_ENABLED', true);
define('MAX_AUDIO_SIZE', 10485760); // 10MB

// ============================================
// PAYMENT SETTINGS
// ============================================
define('PAYMENT_GATEWAY', 'manual'); // manual, midtrans, stripe, etc.
define('PAYMENT_TIMEOUT_HOURS', 24);

// ============================================
// CURRENCY SETTINGS
// ============================================
if (!defined('CURRENCY')) {
    define('CURRENCY', 'IDR');
}
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'Rp');
}
if (!defined('CURRENCY_DECIMALS')) {
    define('CURRENCY_DECIMALS', 0);
}

// ============================================
// DATE/TIME FORMATS
// ============================================
define('DATE_FORMAT', 'd-m-Y');
define('TIME_FORMAT', 'H:i');
define('DATETIME_FORMAT', 'd-m-Y H:i');

// ============================================
// MAINTENANCE MODE
// ============================================
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Aplikasi sedang dalam pemeliharaan. Silakan cembali lagi nanti.');
