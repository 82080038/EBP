<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';

echo "=== Consumer Manual Simulation Test ===\n\n";

$consumerController = new ConsumerController();
$userId = 10;

// Test 1: Featured restaurants
echo "Test 1: Featured Restaurants\n";
try {
    $result = $consumerController->getFeaturedRestaurants([]);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "Data: " . count($result['data']) . " restaurants\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 2: Login
echo "Test 2: Login (email/password)\n";
try {
    $request = ['body' => ['email' => 'consumer1@example.com', 'password' => 'password']];
    $result = $consumerController->login($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    if ($result['success']) {
        echo "User: " . $result['data']['user']['full_name'] . "\n";
        echo "Role: " . $result['data']['user']['role_name'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 3: Send OTP
echo "Test 3: Send OTP\n";
try {
    $request = ['body' => ['phone' => '+6281234567890']];
    $result = $consumerController->sendOtp($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "OTP: " . ($result['data']['otp'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 4: Verify OTP
echo "Test 4: Verify OTP\n";
try {
    $request = ['body' => ['phone' => '+6281234567890', 'otp' => '123456']];
    $result = $consumerController->verifyOtp($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "User: " . ($result['data']['user']['full_name'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 5: Place order
echo "Test 5: Place Order\n";
try {
    $request = ['body' => ['user_id' => $userId, 'order_type' => 'dine_in', 'total_amount' => 50000, 'items' => [['product_id' => 1, 'quantity' => 2, 'price' => 25000]]]];
    $result = $consumerController->placeOrder($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "Order ID: " . ($result['data']['order_id'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 6: Get orders
echo "Test 6: Get Orders\n";
try {
    $request = ['body' => ['user_id' => $userId]];
    $result = $consumerController->getOrders($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "Orders: " . count($result['data']) . "\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 7: Make reservation
echo "Test 7: Make Reservation\n";
try {
    $request = ['body' => ['user_id' => $userId, 'restaurant_id' => 2, 'date' => '2026-07-10', 'time' => '19:00', 'party_size' => 4, 'special_requests' => 'Near window']];
    $result = $consumerController->makeReservation($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "Reservation ID: " . ($result['data']['reservation_id'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 8: Get reservations
echo "Test 8: Get Reservations\n";
try {
    $request = ['body' => ['user_id' => $userId]];
    $result = $consumerController->getReservations($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "Reservations: " . count($result['data']) . "\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 9: Get loyalty points
echo "Test 9: Get Loyalty Points\n";
try {
    $request = ['body' => ['user_id' => $userId]];
    $result = $consumerController->getLoyaltyPoints($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "Points: " . ($result['data']['points'] ?? 'N/A') . "\n";
    echo "Tier: " . ($result['data']['tier'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 10: Submit review
echo "Test 10: Submit Review\n";
try {
    $request = ['body' => ['user_id' => $userId, 'restaurant_id' => 2, 'rating' => 5, 'comment' => 'Great food!']];
    $result = $consumerController->submitReview($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "Review ID: " . ($result['data']['review_id'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 11: Get favorites
echo "Test 11: Get Favorites\n";
try {
    $request = ['body' => ['user_id' => $userId]];
    $result = $consumerController->getFavorites($request);
    echo "Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "Favorites: " . count($result['data']) . "\n\n";
} catch (Exception $e) {
    echo "Status: FAIL - " . $e->getMessage() . "\n\n";
}

echo "=== Simulation Complete ===\n";
