<?php
/**
 * MyWisata Application - Database Configuration
 * 
 * This file contains the database connection settings.
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
// DATABASE SETTINGS
// ============================================
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'mywisata',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    
    // PDO Options
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ],
];
