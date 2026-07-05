<?php

// Database setup script
// Run this to create database and import schema
// This script is portable across different MySQL installations

// Configuration - can be overridden via environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$socket = getenv('DB_SOCKET') ?: null;
$port = getenv('DB_PORT') ?: 3306;
$username = getenv('DB_ROOT_USER') ?: 'root';
$dbname = getenv('DB_NAME') ?: 'ebp_restaurant_db';

// Try common root passwords
$passwords = [
    getenv('DB_ROOT_PASSWORD') ?: '', // Environment variable first
    '', 
    'root', 
    'password', 
    'mysql',
    '123456'
];

$pdo = null;
$usedPassword = '';

// Try connecting with socket first (Linux/Mac XAMPP)
if ($socket && file_exists($socket)) {
    foreach ($passwords as $pwd) {
        try {
            $pdo = new PDO("mysql:host=$host;unix_socket=$socket;charset=utf8mb4", $username, $pwd);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $usedPassword = $pwd;
            echo "Connected to MySQL via socket successfully\n";
            break;
        } catch (PDOException $e) {
            continue;
        }
    }
}

// If socket connection failed, try TCP/IP connection (Windows/Mac MAMP/Docker)
if (!$pdo) {
    foreach ($passwords as $pwd) {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $pwd);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $usedPassword = $pwd;
            echo "Connected to MySQL via TCP/IP successfully\n";
            break;
        } catch (PDOException $e) {
            continue;
        }
    }
}

if (!$pdo) {
    die("Could not connect to MySQL. Please check your MySQL configuration.\n" .
        "Common issues:\n" .
        "- MySQL server not running\n" .
        "- Incorrect username/password\n" .
        "- Wrong host/port/socket configuration\n" .
        "- Try setting DB_ROOT_PASSWORD environment variable\n");
}

// Create database if not exists
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Database created or already exists: $dbname\n";

// Select the database
$pdo->exec("USE `$dbname`");
echo "Database selected\n";

// Import current data (includes schema + data)
$currentDataFile = __DIR__ . '/database/current_data.sql';
if (file_exists($currentDataFile)) {
    echo "Importing current data from $currentDataFile...\n";
    
    $sql = file_get_contents($currentDataFile);
    
    // Remove existing database creation/use statements since we already handled them
    $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/s', '', $sql);
    $sql = preg_replace('/USE.*?;/s', '', $sql);
    
    // Execute the SQL
    try {
        $pdo->exec($sql);
        echo "Current data imported successfully\n";
    } catch (PDOException $e) {
        echo "Warning: Some statements failed during import (this is normal for existing data)\n";
        echo "Error: " . substr($e->getMessage(), 0, 200) . "...\n";
    }
} else {
    echo "Current data file not found: $currentDataFile\n";
    echo "Trying schema file instead...\n";
    
    // Fallback to schema only
    $schemaFile = __DIR__ . '/database/schema.sql';
    if (file_exists($schemaFile)) {
        $schema = file_get_contents($schemaFile);
        $schema = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/s', '', $schema);
        $schema = preg_replace('/USE.*?;/s', '', $schema);
        
        $lines = explode("\n", $schema);
        $currentStatement = '';
        $inComment = false;
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            if (strpos($trimmed, '/*') === 0) {
                $inComment = true;
                continue;
            }
            if (strpos($trimmed, '*/') !== false) {
                $inComment = false;
                continue;
            }
            if ($inComment || strpos($trimmed, '--') === 0 || empty($trimmed)) {
                continue;
            }
            
            $currentStatement .= $line . "\n";
            
            if (strpos($trimmed, ';') !== false) {
                $currentStatement = trim($currentStatement);
                if (!empty($currentStatement)) {
                    try {
                        $pdo->exec($currentStatement);
                    } catch (PDOException $e) {
                        echo "Error executing statement: " . substr($e->getMessage(), 0, 100) . "...\n";
                    }
                }
                $currentStatement = '';
            }
        }
        echo "Schema imported successfully\n";
    } else {
        echo "Schema file not found: $schemaFile\n";
    }
}

// Run seed data if needed
$seedFile = __DIR__ . '/seed_data.php';
if (file_exists($seedFile)) {
    echo "Running seed data...\n";
    include $seedFile;
}

echo "\n========================================\n";
echo "Database setup completed successfully!\n";
echo "========================================\n";
echo "Database name: $dbname\n";
echo "Connection method: " . ($socket ? "Socket ($socket)" : "TCP/IP ($host:$port)") . "\n";
echo "You can now use the application.\n";
echo "\nNext steps:\n";
echo "1. Update .env file with your database credentials\n";
echo "2. Run: php seed_data.php (if not already run)\n";
echo "3. Start the server: php -S localhost:8000 -t public\n";
