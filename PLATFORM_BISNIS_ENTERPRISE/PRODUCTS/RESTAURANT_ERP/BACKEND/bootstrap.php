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

// Set environment variables for Database configuration
putenv('DB_HOST=localhost');
putenv('DB_SOCKET=/opt/lampp/var/mysql/mysql.sock');
putenv('DB_NAME=ebp_restaurant_db');
putenv('DB_USER=ebp_app');
putenv('DB_PASSWORD=ebp_secure_password_2026');

// Load Backend-specific Components
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Transaction.php';
require_once __DIR__ . '/core/Audit.php';
require_once __DIR__ . '/core/Messages.php';
require_once __DIR__ . '/core/Middleware/PermissionMiddleware.php';
require_once __DIR__ . '/core/Middleware/TenantMiddleware.php';
require_once __DIR__ . '/core/Middleware/ErrorHandler.php';
require_once __DIR__ . '/core/Middleware/ValidationMiddleware.php';
require_once __DIR__ . '/core/Middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/core/Engines/StockEngine.php';
require_once __DIR__ . '/core/Engines/KitchenEngine.php';
require_once __DIR__ . '/core/Engines/AccountingEngine.php';
