<?php

/**
 * EBP Backend Bootstrap
 * 
 * This file loads all EBP Core components
 * Include this file at the beginning of your application
 */

// Load EBP Core Components
require_once __DIR__ . '/../../../../PLATFORM_BISNIS_ENTERPRISE/12_IMPLEMENTASI_KODE/API/Response.php';
require_once __DIR__ . '/../../../../PLATFORM_BISNIS_ENTERPRISE/12_IMPLEMENTASI_KODE/Autentikasi/JWT.php';
require_once __DIR__ . '/../../../../PLATFORM_BISNIS_ENTERPRISE/12_IMPLEMENTASI_KODE/Autentikasi/AuthMiddleware.php';
require_once __DIR__ . '/../../../../PLATFORM_BISNIS_ENTERPRISE/12_IMPLEMENTASI_KODE/Database/Database.php';

// Set environment variables for Database configuration
putenv('DB_HOST=localhost');
putenv('DB_SOCKET=null');
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
require_once __DIR__ . '/core/Engines/StockEngine.php';
require_once __DIR__ . '/core/Engines/KitchenEngine.php';
require_once __DIR__ . '/core/Engines/AccountingEngine.php';
