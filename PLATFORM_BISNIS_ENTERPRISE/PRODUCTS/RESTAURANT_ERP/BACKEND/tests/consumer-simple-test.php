<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';

echo "=== Simple Consumer Test ===\n";

$consumerController = new ConsumerController();

// Test login only
echo "Testing login...\n";
$request = [
    'body' => [
        'email' => 'consumer1@example.com',
        'password' => 'password'
    ]
];

try {
    $result = $consumerController->login($request);
    echo "Login result: " . json_encode($result) . "\n";
} catch (Exception $e) {
    echo "Login error: " . $e->getMessage() . "\n";
}

echo "Done.\n";
