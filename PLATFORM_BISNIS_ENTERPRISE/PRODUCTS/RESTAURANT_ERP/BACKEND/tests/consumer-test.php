<?php

/**
 * Consumer API Test Script
 * Tests all consumer endpoints with mock data
 */

$baseUrl = 'http://localhost:8000';
$token = null;

echo "=== Consumer API Test Script ===\n\n";

// Test 1: Get featured restaurants (public)
echo "1. Testing GET /api/v1/consumer/restaurants/featured (public)\n";
$response = file_get_contents($baseUrl . '/api/v1/consumer/restaurants/featured');
echo "Response: " . substr($response, 0, 200) . "...\n\n";

// Test 2: Consumer login
echo "2. Testing POST /api/v1/consumer/auth/login\n";
$loginData = json_encode(['email' => 'consumer1@example.com', 'password' => 'password']);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $loginData
    ]
]);
$response = file_get_contents($baseUrl . '/api/v1/consumer/auth/login', false, $context);
echo "Response: " . $response . "\n\n";
$loginResult = json_decode($response, true);
$token = $loginResult['data']['token'] ?? null;
$userId = $loginResult['data']['user']['user_id'] ?? null;

// Test 3: Send OTP
echo "3. Testing POST /api/v1/consumer/auth/send-otp\n";
$otpData = json_encode(['phone' => '+6281234567890']);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $otpData
    ]
]);
$response = file_get_contents($baseUrl . '/api/v1/consumer/auth/send-otp', false, $context);
echo "Response: " . $response . "\n\n";

// Test 4: Verify OTP
echo "4. Testing POST /api/v1/consumer/auth/verify-otp\n";
$verifyData = json_encode(['phone' => '+6281234567890', 'otp' => '123456']);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $verifyData
    ]
]);
$response = file_get_contents($baseUrl . '/api/v1/consumer/auth/verify-otp', false, $context);
echo "Response: " . $response . "\n\n";

// Test 5: Place order (testing - no auth required)
echo "5. Testing POST /api/v1/consumer/orders (testing - no auth)\n";
$orderData = json_encode([
    'user_id' => $userId,
    'order_type' => 'dine_in',
    'total_amount' => 50000,
    'items' => [
        ['product_id' => 1, 'quantity' => 2, 'price' => 25000]
    ]
]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $orderData
    ]
]);
$response = file_get_contents($baseUrl . '/api/v1/consumer/orders', false, $context);
echo "Response: " . $response . "\n\n";

// Test 6: Get orders (testing - no auth required)
echo "6. Testing GET /api/v1/consumer/orders (testing - no auth)\n";
$response = file_get_contents($baseUrl . '/api/v1/consumer/orders');
echo "Response: " . $response . "\n\n";

// Test 7: Make reservation (testing - no auth required)
echo "7. Testing POST /api/v1/consumer/reservations (testing - no auth)\n";
$reservationData = json_encode([
    'user_id' => $userId,
    'restaurant_id' => 2,
    'date' => '2026-07-10',
    'time' => '19:00',
    'party_size' => 4,
    'special_requests' => 'Near window'
]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $reservationData
    ]
]);
$response = file_get_contents($baseUrl . '/api/v1/consumer/reservations', false, $context);
echo "Response: " . $response . "\n\n";

// Test 8: Get reservations (testing - no auth required)
echo "8. Testing GET /api/v1/consumer/reservations (testing - no auth)\n";
$response = file_get_contents($baseUrl . '/api/v1/consumer/reservations');
echo "Response: " . $response . "\n\n";

// Test 9: Get loyalty points (testing - no auth required)
echo "9. Testing GET /api/v1/consumer/loyalty (testing - no auth)\n";
$response = file_get_contents($baseUrl . '/api/v1/consumer/loyalty');
echo "Response: " . $response . "\n\n";

// Test 10: Submit review (testing - no auth required)
echo "10. Testing POST /api/v1/consumer/reviews (testing - no auth)\n";
$reviewData = json_encode([
    'user_id' => $userId,
    'restaurant_id' => 2,
    'rating' => 5,
    'comment' => 'Great food and service!'
]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $reviewData
    ]
]);
$response = file_get_contents($baseUrl . '/api/v1/consumer/reviews', false, $context);
echo "Response: " . $response . "\n\n";

// Test 11: Get favorites (testing - no auth required)
echo "11. Testing GET /api/v1/consumer/favorites (testing - no auth)\n";
$response = file_get_contents($baseUrl . '/api/v1/consumer/favorites');
echo "Response: " . $response . "\n\n";

echo "=== Test Complete ===\n";
