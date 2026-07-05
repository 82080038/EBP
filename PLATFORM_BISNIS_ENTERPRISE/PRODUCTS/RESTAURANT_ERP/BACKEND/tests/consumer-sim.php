<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';

$consumerController = new ConsumerController();
$userId = 10;
$results = [];

// Test 1: Featured restaurants
$result = $consumerController->getFeaturedRestaurants([]);
$results['featured'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 2: Login
$request = ['body' => ['email' => 'consumer1@example.com', 'password' => 'password']];
$result = $consumerController->login($request);
$results['login'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 3: Send OTP
$request = ['body' => ['phone' => '+6281234567890']];
$result = $consumerController->sendOtp($request);
$results['send_otp'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 4: Verify OTP
$request = ['body' => ['phone' => '+6281234567890', 'otp' => '123456']];
$result = $consumerController->verifyOtp($request);
$results['verify_otp'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 5: Place order
$request = ['body' => ['user_id' => $userId, 'order_type' => 'dine_in', 'total_amount' => 50000, 'items' => [['product_id' => 1, 'quantity' => 2, 'price' => 25000]]]];
$result = $consumerController->placeOrder($request);
$results['place_order'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 6: Get orders
$request = ['body' => ['user_id' => $userId]];
$result = $consumerController->getOrders($request);
$results['get_orders'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 7: Make reservation
$request = ['body' => ['user_id' => $userId, 'restaurant_id' => 2, 'date' => '2026-07-10', 'time' => '19:00', 'party_size' => 4, 'special_requests' => 'Near window']];
$result = $consumerController->makeReservation($request);
$results['make_reservation'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 8: Get reservations
$request = ['body' => ['user_id' => $userId]];
$result = $consumerController->getReservations($request);
$results['get_reservations'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 9: Get loyalty
$request = ['body' => ['user_id' => $userId]];
$result = $consumerController->getLoyaltyPoints($request);
$results['get_loyalty'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 10: Submit review
$request = ['body' => ['user_id' => $userId, 'restaurant_id' => 2, 'rating' => 5, 'comment' => 'Great food!']];
$result = $consumerController->submitReview($request);
$results['submit_review'] = $result['success'] ? 'PASS' : 'FAIL';

// Test 11: Get favorites
$request = ['body' => ['user_id' => $userId]];
$result = $consumerController->getFavorites($request);
$results['get_favorites'] = $result['success'] ? 'PASS' : 'FAIL';

echo json_encode($results);
