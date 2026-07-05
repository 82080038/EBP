<?php

/**
 * Direct Consumer Controller Test
 * Tests controller methods directly without HTTP layer
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';

echo "=== Direct Consumer Controller Test ===\n\n";

$consumerController = new ConsumerController();

// Test 1: Get featured restaurants
echo "1. Testing getFeaturedRestaurants\n";
$request = [];
try {
    $result = $consumerController->getFeaturedRestaurants($request);
    echo "Result: " . json_encode($result) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Login
echo "2. Testing login\n";
$request = [
    'body' => [
        'email' => 'consumer1@example.com',
        'password' => 'password'
    ]
];
try {
    $result = $consumerController->login($request);
    echo "Result: " . json_encode($result) . "\n\n";
    $loginData = json_decode(json_encode($result), true);
    $userId = $loginData['data']['user']['user_id'] ?? null;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Place order
echo "3. Testing placeOrder\n";
$request = [
    'body' => [
        'user_id' => $userId ?? 10,
        'order_type' => 'dine_in',
        'total_amount' => 50000,
        'items' => [
            ['product_id' => 1, 'quantity' => 2, 'price' => 25000]
        ]
    ]
];
try {
    $result = $consumerController->placeOrder($request);
    echo "Result: " . json_encode($result) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Get orders
echo "4. Testing getOrders\n";
$request = [
    'body' => [
        'user_id' => $userId ?? 10
    ]
];
try {
    $result = $consumerController->getOrders($request);
    echo "Result: " . json_encode($result) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Make reservation
echo "5. Testing makeReservation\n";
$request = [
    'body' => [
        'user_id' => $userId ?? 10,
        'restaurant_id' => 2,
        'date' => '2026-07-10',
        'time' => '19:00',
        'party_size' => 4,
        'special_requests' => 'Near window'
    ]
];
try {
    $result = $consumerController->makeReservation($request);
    echo "Result: " . json_encode($result) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 6: Get reservations
echo "6. Testing getReservations\n";
$request = [
    'body' => [
        'user_id' => $userId ?? 10
    ]
];
try {
    $result = $consumerController->getReservations($request);
    echo "Result: " . json_encode($result) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 7: Get loyalty points
echo "7. Testing getLoyaltyPoints\n";
$request = [
    'body' => [
        'user_id' => $userId ?? 10
    ]
];
try {
    $result = $consumerController->getLoyaltyPoints($request);
    echo "Result: " . json_encode($result) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 8: Submit review
echo "8. Testing submitReview\n";
$request = [
    'body' => [
        'user_id' => $userId ?? 10,
        'restaurant_id' => 2,
        'rating' => 5,
        'comment' => 'Great food and service!'
    ]
];
try {
    $result = $consumerController->submitReview($request);
    echo "Result: " . json_encode($result) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 9: Get favorites
echo "9. Testing getFavorites\n";
$request = [
    'body' => [
        'user_id' => $userId ?? 10
    ]
];
try {
    $result = $consumerController->getFavorites($request);
    echo "Result: " . json_encode($result) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Test Complete ===\n";
