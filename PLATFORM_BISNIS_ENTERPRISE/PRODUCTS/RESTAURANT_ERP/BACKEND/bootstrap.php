<?php

/**
 * EBP Backend Bootstrap
 * 
 * This file loads all EBP Core components
 * Include this file at the beginning of your application
 */

// Load EBP Core Components (local to BACKEND/core)
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/JWT.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Middleware/AuthMiddleware.php';

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

// Set default values (only used if .env doesn't exist or doesn't have these values)
if (!getenv('DB_HOST')) putenv('DB_HOST=localhost');
if (!getenv('DB_SOCKET')) putenv('DB_SOCKET=/opt/lampp/var/mysql/mysql.sock');
if (!getenv('DB_NAME')) putenv('DB_NAME=ebp_restaurant_db');
if (!getenv('DB_USER')) putenv('DB_USER=ebp_app');
if (!getenv('DB_PASSWORD')) putenv('DB_PASSWORD=ebp_secure_password_2026');
if (!getenv('JWT_SECRET')) putenv('JWT_SECRET=ebp_secret_key_change_in_production');
if (!getenv('JWT_ALGORITHM')) putenv('JWT_ALGORITHM=HS256');
if (!getenv('JWT_EXPIRATION')) putenv('JWT_EXPIRATION=3600');

// Load Backend-specific Components
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Transaction.php';
require_once __DIR__ . '/core/Audit.php';
require_once __DIR__ . '/core/Messages.php';
require_once __DIR__ . '/core/ScreenSizeHelper.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/Middleware/PermissionMiddleware.php';
require_once __DIR__ . '/core/Middleware/TenantMiddleware.php';
require_once __DIR__ . '/core/Middleware/ErrorHandler.php';
require_once __DIR__ . '/core/Middleware/ValidationMiddleware.php';
require_once __DIR__ . '/core/Middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/core/Engines/StockEngine.php';
require_once __DIR__ . '/core/Engines/KitchenEngine.php';
require_once __DIR__ . '/core/Engines/AccountingEngine.php';
