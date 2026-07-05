#!/bin/bash

echo "=== Consumer API Testing ==="
echo ""

echo "Test 1: Featured Restaurants"
php tests/test-featured.php
echo ""
echo "---"
echo ""

echo "Test 2: Login"
php tests/test-login.php
echo ""
echo "---"
echo ""

echo "Test 3: Send OTP"
php -r "
require_once 'bootstrap.php';
require_once 'modules/Consumer/Controllers/ConsumerController.php';
\$controller = new ConsumerController();
\$request = ['body' => ['phone' => '+6281234567890']];
echo json_encode(\$controller->sendOtp(\$request));
"
echo ""
echo "---"
echo ""

echo "Test 4: Verify OTP"
php -r "
require_once 'bootstrap.php';
require_once 'modules/Consumer/Controllers/ConsumerController.php';
\$controller = new ConsumerController();
\$request = ['body' => ['phone' => '+6281234567890', 'otp' => '123456']];
echo json_encode(\$controller->verifyOtp(\$request));
"
echo ""
echo "---"
echo ""

echo "Test 5: Place Order"
php -r "
require_once 'bootstrap.php';
require_once 'modules/Consumer/Controllers/ConsumerController.php';
\$controller = new ConsumerController();
\$request = ['body' => ['user_id' => 10, 'order_type' => 'dine_in', 'total_amount' => 50000, 'items' => [['product_id' => 1, 'quantity' => 2, 'price' => 25000]]]];
echo json_encode(\$controller->placeOrder(\$request));
"
echo ""
echo "---"
echo ""

echo "Test 6: Get Orders"
php -r "
require_once 'bootstrap.php';
require_once 'modules/Consumer/Controllers/ConsumerController.php';
\$controller = new ConsumerController();
\$request = ['body' => ['user_id' => 10]];
echo json_encode(\$controller->getOrders(\$request));
"
echo ""
echo "---"
echo ""

echo "Test 7: Make Reservation"
php -r "
require_once 'bootstrap.php';
require_once 'modules/Consumer/Controllers/ConsumerController.php';
\$controller = new ConsumerController();
\$request = ['body' => ['user_id' => 10, 'restaurant_id' => 2, 'date' => '2026-07-10', 'time' => '19:00', 'party_size' => 4, 'special_requests' => 'Near window']];
echo json_encode(\$controller->makeReservation(\$request));
"
echo ""
echo "---"
echo ""

echo "Test 8: Get Reservations"
php -r "
require_once 'bootstrap.php';
require_once 'modules/Consumer/Controllers/ConsumerController.php';
\$controller = new ConsumerController();
\$request = ['body' => ['user_id' => 10]];
echo json_encode(\$controller->getReservations(\$request));
"
echo ""
echo "---"
echo ""

echo "Test 9: Get Loyalty Points"
php -r "
require_once 'bootstrap.php';
require_once 'modules/Consumer/Controllers/ConsumerController.php';
\$controller = new ConsumerController();
\$request = ['body' => ['user_id' => 10]];
echo json_encode(\$controller->getLoyaltyPoints(\$request));
"
echo ""
echo "---"
echo ""

echo "Test 10: Submit Review"
php -r "
require_once 'bootstrap.php';
require_once 'modules/Consumer/Controllers/ConsumerController.php';
\$controller = new ConsumerController();
\$request = ['body' => ['user_id' => 10, 'restaurant_id' => 2, 'rating' => 5, 'comment' => 'Great food!']];
echo json_encode(\$controller->submitReview(\$request));
"
echo ""
echo "---"
echo ""

echo "Test 11: Get Favorites"
php -r "
require_once 'bootstrap.php';
require_once 'modules/Consumer/Controllers/ConsumerController.php';
\$controller = new ConsumerController();
\$request = ['body' => ['user_id' => 10]];
echo json_encode(\$controller->getFavorites(\$request));
"
echo ""
echo "---"
echo ""

echo "=== Testing Complete ==="
