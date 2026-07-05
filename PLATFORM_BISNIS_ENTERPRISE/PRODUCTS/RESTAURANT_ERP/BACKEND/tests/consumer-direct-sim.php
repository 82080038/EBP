<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';

echo "=== Consumer Simulation Test ===\n\n";

$consumerController = new ConsumerController();
$userId = 10;
$testResults = [];

// Test 1: Featured restaurants
echo "1. Featured Restaurants: ";
try {
    $result = $consumerController->getFeaturedRestaurants([]);
    $testResults['featured'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['featured'] . "\n";
} catch (Exception $e) {
    $testResults['featured'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 2: Login
echo "2. Login (email/password): ";
try {
    $request = ['body' => ['email' => 'consumer1@example.com', 'password' => 'password']];
    $result = $consumerController->login($request);
    $testResults['login'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['login'] . "\n";
} catch (Exception $e) {
    $testResults['login'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 3: Send OTP
echo "3. Send OTP: ";
try {
    $request = ['body' => ['phone' => '+6281234567890']];
    $result = $consumerController->sendOtp($request);
    $testResults['send_otp'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['send_otp'] . "\n";
} catch (Exception $e) {
    $testResults['send_otp'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 4: Verify OTP
echo "4. Verify OTP: ";
try {
    $request = ['body' => ['phone' => '+6281234567890', 'otp' => '123456']];
    $result = $consumerController->verifyOtp($request);
    $testResults['verify_otp'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['verify_otp'] . "\n";
} catch (Exception $e) {
    $testResults['verify_otp'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 5: Place order
echo "5. Place Order: ";
try {
    $request = ['body' => ['user_id' => $userId, 'order_type' => 'dine_in', 'total_amount' => 50000, 'items' => [['product_id' => 1, 'quantity' => 2, 'price' => 25000]]]];
    $result = $consumerController->placeOrder($request);
    $testResults['place_order'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['place_order'] . "\n";
} catch (Exception $e) {
    $testResults['place_order'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 6: Get orders
echo "6. Get Orders: ";
try {
    $request = ['body' => ['user_id' => $userId]];
    $result = $consumerController->getOrders($request);
    $testResults['get_orders'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['get_orders'] . "\n";
} catch (Exception $e) {
    $testResults['get_orders'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 7: Make reservation
echo "7. Make Reservation: ";
try {
    $request = ['body' => ['user_id' => $userId, 'restaurant_id' => 2, 'date' => '2026-07-10', 'time' => '19:00', 'party_size' => 4, 'special_requests' => 'Near window']];
    $result = $consumerController->makeReservation($request);
    $testResults['make_reservation'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['make_reservation'] . "\n";
} catch (Exception $e) {
    $testResults['make_reservation'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 8: Get reservations
echo "8. Get Reservations: ";
try {
    $request = ['body' => ['user_id' => $userId]];
    $result = $consumerController->getReservations($request);
    $testResults['get_reservations'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['get_reservations'] . "\n";
} catch (Exception $e) {
    $testResults['get_reservations'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 9: Get loyalty points
echo "9. Get Loyalty Points: ";
try {
    $request = ['body' => ['user_id' => $userId]];
    $result = $consumerController->getLoyaltyPoints($request);
    $testResults['get_loyalty'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['get_loyalty'] . "\n";
} catch (Exception $e) {
    $testResults['get_loyalty'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 10: Submit review
echo "10. Submit Review: ";
try {
    $request = ['body' => ['user_id' => $userId, 'restaurant_id' => 2, 'rating' => 5, 'comment' => 'Great food!']];
    $result = $consumerController->submitReview($request);
    $testResults['submit_review'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['submit_review'] . "\n";
} catch (Exception $e) {
    $testResults['submit_review'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Test 11: Get favorites
echo "11. Get Favorites: ";
try {
    $request = ['body' => ['user_id' => $userId]];
    $result = $consumerController->getFavorites($request);
    $testResults['get_favorites'] = $result['success'] ? 'PASS' : 'FAIL';
    echo $testResults['get_favorites'] . "\n";
} catch (Exception $e) {
    $testResults['get_favorites'] = 'FAIL';
    echo "FAIL - " . $e->getMessage() . "\n";
}

// Summary
echo "\n=== Summary ===\n";
$passed = 0;
$failed = 0;
foreach ($testResults as $test => $result) {
    if ($result === 'PASS') {
        $passed++;
        echo "✓ $test\n";
    } else {
        $failed++;
        echo "✗ $test\n";
    }
}
echo "\nTotal: " . count($testResults) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Success Rate: " . round(($passed / count($testResults)) * 100, 2) . "%\n";
