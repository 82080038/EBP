<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';

echo "=== Consumer Step-by-Step Test ===\n\n";

$consumerController = new ConsumerController();
$userId = 10;

// Step 1: Featured restaurants
echo "Step 1: Featured restaurants\n";
$result = $consumerController->getFeaturedRestaurants([]);
echo "Result: " . json_encode($result) . "\n\n";

// Step 2: Login
echo "Step 2: Login\n";
$request = [
    'body' => [
        'email' => 'consumer1@example.com',
        'password' => 'password'
    ]
];
$result = $consumerController->login($request);
echo "Result: " . json_encode($result) . "\n\n";

// Step 3: Place order
echo "Step 3: Place order\n";
$request = [
    'body' => [
        'user_id' => $userId,
        'order_type' => 'dine_in',
        'total_amount' => 50000,
        'items' => [
            ['product_id' => 1, 'quantity' => 2, 'price' => 25000]
        ]
    ]
];
$result = $consumerController->placeOrder($request);
echo "Result: " . json_encode($result) . "\n\n";

echo "=== Test Complete ===\n";
