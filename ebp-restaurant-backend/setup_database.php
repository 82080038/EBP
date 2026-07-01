<?php

// Database setup script
// Run this to create database and import schema

$host = 'localhost';
$socket = '/opt/lampp/var/mysql/mysql.sock';
$username = 'root';
$password = ''; // Try empty password first
$dbname = 'ebp_restaurant_db';

// Try common XAMPP passwords
$passwords = ['', 'root', 'password', 'mysql'];

$pdo = null;
foreach ($passwords as $pwd) {
    try {
        $pdo = new PDO("mysql:host=$host;unix_socket=$socket;charset=utf8mb4", $username, $pwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $password = $pwd;
        echo "Connected to MySQL successfully (password: " . ($pwd ? '***' : 'empty') . ")\n";
        break;
    } catch (PDOException $e) {
        continue;
    }
}

if (!$pdo) {
    die("Could not connect to MySQL with any common password. Please check your MySQL configuration.\n");
}

// Create database if not exists
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Database created or already exists\n";

// Select the database
$pdo->exec("USE `$dbname`");
echo "Database selected\n";

// Read and execute schema
$schemaFile = __DIR__ . '/database/schema.sql';
if (file_exists($schemaFile)) {
    $schema = file_get_contents($schemaFile);
    
    // Remove the CREATE DATABASE statement from schema since we already created it
    $schema = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/s', '', $schema);
    $schema = preg_replace('/USE.*?;/s', '', $schema);
    
    // Split by semicolon but handle multi-line statements
    $lines = explode("\n", $schema);
    $currentStatement = '';
    $inComment = false;
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Skip comment blocks
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
        
        // Check if statement ends with semicolon
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

echo "\nDatabase setup completed successfully!\n";
echo "Database name: $dbname\n";
echo "You can now use the application.\n";
echo "Password used: " . ($password ? '***' : 'empty') . "\n";

// Update database config file
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    $configContent = preg_replace('/private \$dbname = ".*?";/', 'private $dbname = "' . $dbname . '";', $configContent);
    if ($password) {
        $configContent = preg_replace('/private \$password = ".*?";/', 'private $password = "' . $password . '";', $configContent);
    }
    file_put_contents($configFile, $configContent);
    echo "Database configuration updated\n";
}
