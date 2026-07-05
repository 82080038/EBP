<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';

echo "=== Consumer Full Feature Test & Simulation ===\n\n";

$consumerController = new ConsumerController();
$userId = 10; // consumer1
$testResults = [];

// Test 1: Get featured restaurants (public)
echo "1. Testing getFeaturedRestaurants (public)\n";
try {
    $result = $consumerController->getFeaturedRestaurants([]);
    $testResults['featured_restaurants'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "   Data: " . count($result['data'] ?? []) . " restaurants\n\n";
} catch (Exception $e) {
    $testResults['featured_restaurants'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 2: Login (email/password)
echo "2. Testing login (email/password)\n";
try {
    $request = [
        'body' => [
            'email' => 'consumer1@example.com',
            'password' => 'password'
        ]
    ];
    $result = $consumerController->login($request);
    $testResults['login'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    if ($result['success']) {
        echo "   User: " . $result['data']['user']['full_name'] . "\n";
        echo "   Role: " . $result['data']['user']['role_name'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    $testResults['login'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 3: Send OTP
echo "3. Testing sendOtp\n";
try {
    $request = [
        'body' => [
            'phone' => '+6281234567890'
        ]
    ];
    $result = $consumerController->sendOtp($request);
    $testResults['send_otp'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "   OTP: " . ($result['data']['otp'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    $testResults['send_otp'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 4: Verify OTP
echo "4. Testing verifyOtp\n";
try {
    $request = [
        'body' => [
            'phone' => '+6281234567890',
            'otp' => '123456'
        ]
    ];
    $result = $consumerController->verifyOtp($request);
    $testResults['verify_otp'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "   User: " . ($result['data']['user']['full_name'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    $testResults['verify_otp'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 5: Place order
echo "5. Testing placeOrder\n";
try {
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
    $testResults['place_order'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    if ($result['success']) {
        echo "   Order ID: " . ($result['data']['order_id'] ?? 'N/A') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    $testResults['place_order'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 6: Get orders
echo "6. Testing getOrders\n";
try {
    $request = [
        'body' => [
            'user_id' => $userId
        ]
    ];
    $result = $consumerController->getOrders($request);
    $testResults['get_orders'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "   Orders: " . count($result['data'] ?? []) . "\n\n";
} catch (Exception $e) {
    $testResults['get_orders'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 7: Make reservation
echo "7. Testing makeReservation\n";
try {
    $request = [
        'body' => [
            'user_id' => $userId,
            'restaurant_id' => 2,
            'date' => '2026-07-10',
            'time' => '19:00',
            'party_size' => 4,
            'special_requests' => 'Near window'
        ]
    ];
    $result = $consumerController->makeReservation($request);
    $testResults['make_reservation'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    if ($result['success']) {
        echo "   Reservation ID: " . ($result['data']['reservation_id'] ?? 'N/A') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    $testResults['make_reservation'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 8: Get reservations
echo "8. Testing getReservations\n";
try {
    $request = [
        'body' => [
            'user_id' => $userId
        ]
    ];
    $result = $consumerController->getReservations($request);
    $testResults['get_reservations'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "   Reservations: " . count($result['data'] ?? []) . "\n\n";
} catch (Exception $e) {
    $testResults['get_reservations'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 9: Get loyalty points
echo "9. Testing getLoyaltyPoints\n";
try {
    $request = [
        'body' => [
            'user_id' => $userId
        ]
    ];
    $result = $consumerController->getLoyaltyPoints($request);
    $testResults['get_loyalty'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    if ($result['success']) {
        echo "   Points: " . ($result['data']['points'] ?? 'N/A') . "\n";
        echo "   Tier: " . ($result['data']['tier'] ?? 'N/A') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    $testResults['get_loyalty'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 10: Submit review
echo "10. Testing submitReview\n";
try {
    $request = [
        'body' => [
            'user_id' => $userId,
            'restaurant_id' => 2,
            'rating' => 5,
            'comment' => 'Great food and service!'
        ]
    ];
    $result = $consumerController->submitReview($request);
    $testResults['submit_review'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    if ($result['success']) {
        echo "   Review ID: " . ($result['data']['review_id'] ?? 'N/A') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    $testResults['submit_review'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Test 11: Get favorites
echo "11. Testing getFavorites\n";
try {
    $request = [
        'body' => [
            'user_id' => $userId
        ]
    ];
    $result = $consumerController->getFavorites($request);
    $testResults['get_favorites'] = $result['success'] ?? false;
    echo "   Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "   Favorites: " . count($result['data'] ?? []) . "\n\n";
} catch (Exception $e) {
    $testResults['get_favorites'] = false;
    echo "   Status: FAIL - " . $e->getMessage() . "\n\n";
}

// Summary
echo "=== Test Summary ===\n";
$passed = 0;
$failed = 0;
foreach ($testResults as $test => $result) {
    if ($result) {
        $passed++;
        echo "✓ $test: PASS\n";
    } else {
        $failed++;
        echo "✗ $test: FAIL\n";
    }
}
echo "\nTotal: " . count($testResults) . " tests\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Success Rate: " . round(($passed / count($testResults)) * 100, 2) . "%\n";
