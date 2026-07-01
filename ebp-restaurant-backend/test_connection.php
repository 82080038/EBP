<?php

require_once 'config/database.php';

$db = new Database();
$pdo = $db->connect();

if ($pdo) {
    echo "Database connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tenants");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Tenants count: " . $result['count'] . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Users count: " . $result['count'] . "\n";
    
    echo "\nDatabase is ready for use!\n";
} else {
    echo "Database connection failed!\n";
}
