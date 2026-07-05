<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';

class ConsumerController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get featured restaurants (public endpoint - no auth required)
     */
    public function getFeaturedRestaurants(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("
                SELECT c.company_id as id, c.company_name as name,
                       c.address, c.phone, c.email
                FROM companies c
                WHERE c.tenant_id = ? AND c.status = 'ACTIVE'
                ORDER BY c.company_name
                LIMIT 10
            ");
            $stmt->execute([$tenantId]);
            $restaurants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($restaurants, 'Featured restaurants retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve featured restaurants: ' . $e->getMessage());
        }
    }

    /**
     * Get nearby restaurants (public endpoint - no auth required)
     */
    public function getNearbyRestaurants(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;

            // For now, return all restaurants (distance calculation would require geospatial queries)
            $stmt = $pdo->prepare("
                SELECT c.company_id as id, c.company_name as name,
                       c.address, c.phone, c.email
                FROM companies c
                WHERE c.tenant_id = ? AND c.status = 'ACTIVE'
                ORDER BY c.company_name
            ");
            $stmt->execute([$tenantId]);
            $restaurants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($restaurants, 'Nearby restaurants retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve nearby restaurants: ' . $e->getMessage());
        }
    }

    /**
     * Get all restaurants (public endpoint - no auth required)
     */
    public function getRestaurants(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("
                SELECT c.company_id as id, c.company_name as name,
                       c.address, c.phone, c.email
                FROM companies c
                WHERE c.tenant_id = ? AND c.status = 'ACTIVE'
                ORDER BY c.company_name
            ");
            $stmt->execute([$tenantId]);
            $restaurants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($restaurants, 'Restaurants retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve restaurants: ' . $e->getMessage());
        }
    }

    /**
     * Get restaurant details (public endpoint - no auth required)
     */
    public function getRestaurantDetails(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $restaurantId = $request['id'] ?? 0;

            $stmt = $pdo->prepare("
                SELECT c.company_id as id, c.company_name as name,
                       c.address, c.phone, c.email, c.company_code
                FROM companies c
                WHERE c.company_id = ? AND c.status = 'ACTIVE'
            ");
            $stmt->execute([$restaurantId]);
            $restaurant = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$restaurant) {
                return Response::error('Restaurant not found', 404);
            }

            return Response::success($restaurant, 'Restaurant details retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve restaurant details: ' . $e->getMessage());
        }
    }

    /**
     * Get cuisines (public endpoint - no auth required)
     */
    public function getCuisines(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("
                SELECT DISTINCT cuisine_type
                FROM companies
                WHERE tenant_id = ? AND status = 'ACTIVE' AND cuisine_type IS NOT NULL
                ORDER BY cuisine_type
            ");
            $stmt->execute([$tenantId]);
            $cuisines = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            return Response::success($cuisines, 'Cuisines retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve cuisines: ' . $e->getMessage());
        }
    }

    /**
     * Get restaurant menu (public endpoint - no auth required)
     */
    public function getRestaurantMenu(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $restaurantId = $request['restaurant_id'] ?? 0;

            $stmt = $pdo->prepare("
                SELECT p.product_id as id, p.product_name as name, p.description,
                       p.price, p.image_url, p.is_available,
                       c.category_name as category
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.company_id = ? AND p.status = 'ACTIVE'
                ORDER BY c.category_name, p.product_name
            ");
            $stmt->execute([$restaurantId]);
            $menu = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($menu, 'Menu retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve menu: ' . $e->getMessage());
        }
    }

    /**
     * Consumer login (email/password)
     */
    public function login(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $email = $body['email'] ?? '';
            $password = $body['password'] ?? '';

            // Debug logging
            error_log("Login attempt - Email: " . $email);
            error_log("Request data: " . json_encode($request));

            if (empty($email) || empty($password)) {
                return Response::error('Email and password are required', 400);
            }

            $stmt = $pdo->prepare("
                SELECT u.user_id, u.username, u.email, u.full_name, u.password,
                       r.role_name, r.role_code
                FROM users u
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.role_id
                WHERE u.email = ? AND u.status = 'ACTIVE'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return Response::error('User not found', 401);
            }

            if (!password_verify($password, $user['password'])) {
                return Response::error('Invalid password', 401);
            }

            // Generate JWT token (simplified - should use proper JWT library)
            $token = base64_encode(json_encode([
                'user_id' => $user['user_id'],
                'email' => $user['email'],
                'exp' => time() + 3600
            ]));

            // Remove password from response
            unset($user['password']);

            return Response::success([
                'user' => $user,
                'token' => $token
            ], 'Login successful');
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return Response::error('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Send OTP (placeholder - requires SMS gateway integration)
     */
    public function sendOtp(array $request)
    {
        try {
            $body = $request['body'] ?? [];
            $phone = $body['phone'] ?? '';

            // Validate phone number
            if (empty($phone)) {
                return Response::error('Phone number is required', 400);
            }

            // Generate OTP (for demo, use 123456)
            $otp = '123456';

            // In production, integrate with SMS gateway (Twilio, etc.)
            // For now, just return success
            return Response::success([
                'otp' => $otp, // Only for demo - remove in production
                'message' => 'OTP sent successfully'
            ], 'OTP sent successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to send OTP: ' . $e->getMessage());
        }
    }

    /**
     * Verify OTP and login
     */
    public function verifyOtp(array $request)
    {
        try {
            $body = $request['body'] ?? [];
            $phone = $body['phone'] ?? '';
            $otp = $body['otp'] ?? '';

            // For demo, accept 123456
            if ($otp !== '123456') {
                return Response::error('Invalid OTP', 401);
            }

            // Create or get user by phone
            $pdo = $this->db->connect();
            
            $stmt = $pdo->prepare("
                SELECT user_id, username, email, full_name
                FROM users
                WHERE phone = ? AND status = 'ACTIVE'
            ");
            $stmt->execute([$phone]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                // Create new user
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, phone, full_name, status, created_at)
                    VALUES (?, ?, 'Guest User', 'ACTIVE', NOW())
                ");
                $stmt->execute([$phone]);
                $userId = $pdo->lastInsertId();

                $user = [
                    'user_id' => $userId,
                    'username' => $phone,
                    'email' => null,
                    'full_name' => 'Guest User'
                ];
            }

            // Generate JWT token
            $token = base64_encode(json_encode([
                'user_id' => $user['user_id'],
                'phone' => $phone,
                'exp' => time() + 3600
            ]));

            return Response::success([
                'user' => $user,
                'token' => $token
            ], 'Login successful');
        } catch (\Exception $e) {
            return Response::error('OTP verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get FAQ (public endpoint - no auth required)
     */
    public function getFaq(array $request)
    {
        try {
            // Return static FAQ for now
            $faq = [
                [
                    'question' => 'How do I place an order?',
                    'answer' => 'Browse the menu, add items to your cart, and proceed to checkout.'
                ],
                [
                    'question' => 'What payment methods do you accept?',
                    'answer' => 'We accept cash, credit cards, and digital payments.'
                ],
                [
                    'question' => 'Can I make a reservation?',
                    'answer' => 'Yes, you can make a reservation through our app or by calling us.'
                ]
            ];

            return Response::success($faq, 'FAQ retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve FAQ: ' . $e->getMessage());
        }
    }

    /**
     * Place order (requires authentication)
     */
    public function placeOrder(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $userId = $body['user_id'] ?? null;
            $items = $body['items'] ?? [];
            $orderType = $body['order_type'] ?? 'dine_in'; // dine_in, delivery, pickup
            $deliveryAddress = $body['delivery_address'] ?? null;
            $totalAmount = $body['total_amount'] ?? 0;

            if (!$userId) {
                return Response::error('User authentication required', 401);
            }

            if (empty($items)) {
                return Response::error('Order items are required', 400);
            }

            // Start transaction
            $pdo->beginTransaction();

            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Calculate subtotal from items
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += ($item['price'] * $item['quantity']);
            }

            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (tenant_id, branch_id, order_number, user_id, order_type, status, subtotal, total_amount, created_at)
                VALUES (?, ?, ?, ?, ?, 'PENDING', ?, ?, NOW())
            ");
            $stmt->execute([1, 2, $orderNumber, $userId, $orderType, $subtotal, $totalAmount]);
            $orderId = $pdo->lastInsertId();

            // Add order items
            foreach ($items as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $itemSubtotal
                ]);
            }

            // Add delivery address if applicable
            if ($orderType === 'delivery' && $deliveryAddress) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_addresses (order_id, address, created_at)
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$orderId, $deliveryAddress]);
            }

            $pdo->commit();

            return Response::success([
                'order_id' => $orderId,
                'status' => 'PENDING',
                'message' => 'Order placed successfully'
            ], 'Order placed successfully');
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return Response::error('Failed to place order: ' . $e->getMessage());
        }
    }

    /**
     * Get user orders (requires authentication)
     */
    public function getOrders(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $userId = $body['user_id'] ?? null;

            if (!$userId) {
                return Response::error('User authentication required', 401);
            }

            $stmt = $pdo->prepare("
                SELECT o.order_id, o.order_type, o.status, o.total_amount, o.created_at,
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
                FROM orders o
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$userId]);
            $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($orders, 'Orders retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve orders: ' . $e->getMessage());
        }
    }

    /**
     * Make reservation (requires authentication)
     */
    public function makeReservation(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $userId = $body['user_id'] ?? null;
            $restaurantId = $body['restaurant_id'] ?? null;
            $date = $body['date'] ?? null;
            $time = $body['time'] ?? null;
            $partySize = $body['party_size'] ?? 2;
            $specialRequests = $body['special_requests'] ?? null;

            if (!$userId) {
                return Response::error('User authentication required', 401);
            }

            if (!$restaurantId || !$date || !$time) {
                return Response::error('Restaurant, date, and time are required', 400);
            }

            // Get user info for customer name/phone
            $stmt = $pdo->prepare("SELECT full_name, phone FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Generate reservation number
            $reservationNumber = 'RES-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create reservation
            $stmt = $pdo->prepare("
                INSERT INTO reservations (tenant_id, branch_id, reservation_number, customer_name, customer_phone, reservation_date, reservation_time, party_size, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', NOW())
            ");
            $stmt->execute([
                1,
                2,
                $reservationNumber,
                $user['full_name'] ?? 'Guest',
                $user['phone'] ?? null,
                $date,
                $time,
                $partySize,
                $specialRequests
            ]);
            $reservationId = $pdo->lastInsertId();

            return Response::success([
                'reservation_id' => $reservationId,
                'reservation_number' => $reservationNumber,
                'status' => 'PENDING',
                'message' => 'Reservation made successfully'
            ], 'Reservation made successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to make reservation: ' . $e->getMessage());
        }
    }

    /**
     * Get user reservations (requires authentication)
     */
    public function getReservations(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $userId = $body['user_id'] ?? null;

            if (!$userId) {
                return Response::error('User authentication required', 401);
            }

            // Get user info to match reservations by customer name/phone
            $stmt = $pdo->prepare("SELECT full_name, phone FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("
                SELECT r.reservation_id, r.reservation_number, r.customer_name, r.customer_phone,
                       r.reservation_date, r.reservation_time, r.party_size, r.notes, r.status, r.created_at
                FROM reservations r
                WHERE r.customer_name = ? OR r.customer_phone = ?
                ORDER BY r.reservation_date DESC, r.reservation_time DESC
            ");
            $stmt->execute([$user['full_name'], $user['phone']]);
            $reservations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($reservations, 'Reservations retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve reservations: ' . $e->getMessage());
        }
    }

    /**
     * Get user loyalty points (requires authentication)
     */
    public function getLoyaltyPoints(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $userId = $body['user_id'] ?? null;

            if (!$userId) {
                return Response::error('User authentication required', 401);
            }

            $stmt = $pdo->prepare("
                SELECT points_balance, points_earned, points_redeemed, tier, next_tier, points_to_next_tier
                FROM loyalty_points
                WHERE user_id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$userId]);
            $loyalty = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$loyalty) {
                // Create default loyalty entry for user
                $stmt = $pdo->prepare("
                    INSERT INTO loyalty_points (user_id, tenant_id, points_balance, points_earned, points_redeemed, tier, next_tier, points_to_next_tier)
                    VALUES (?, 1, 0, 0, 0, 'Bronze', 'Silver', 1000)
                ");
                $stmt->execute([$userId]);
                
                $loyalty = [
                    'points_balance' => 0,
                    'points_earned' => 0,
                    'points_redeemed' => 0,
                    'tier' => 'Bronze',
                    'next_tier' => 'Silver',
                    'points_to_next_tier' => 1000
                ];
            }

            return Response::success($loyalty, 'Loyalty points retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve loyalty points: ' . $e->getMessage());
        }
    }

    /**
     * Redeem loyalty reward (requires authentication)
     */
    public function redeemReward(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $userId = $body['user_id'] ?? null;
            $rewardId = $body['reward_id'] ?? null;

            if (!$userId) {
                return Response::error('User authentication required', 401);
            }

            if (!$rewardId) {
                return Response::error('Reward ID is required', 400);
            }

            // For now, return success
            return Response::success([
                'message' => 'Reward redeemed successfully',
                'points_deducted' => 500
            ], 'Reward redeemed successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to redeem reward: ' . $e->getMessage());
        }
    }

    /**
     * Submit review (requires authentication)
     */
    public function submitReview(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $userId = $body['user_id'] ?? null;
            $restaurantId = $body['restaurant_id'] ?? null;
            $rating = $body['rating'] ?? null;
            $comment = $body['comment'] ?? null;

            if (!$userId) {
                return Response::error('User authentication required', 401);
            }

            if (!$restaurantId || !$rating) {
                return Response::error('Restaurant and rating are required', 400);
            }

            if ($rating < 1 || $rating > 5) {
                return Response::error('Rating must be between 1 and 5', 400);
            }

            // Insert review
            $stmt = $pdo->prepare("
                INSERT INTO reviews (user_id, tenant_id, restaurant_id, rating, comment)
                VALUES (?, 1, ?, ?, ?)
            ");
            $stmt->execute([$userId, $restaurantId, $rating, $comment]);
            $reviewId = $pdo->lastInsertId();

            return Response::success([
                'review_id' => $reviewId,
                'rating' => $rating,
                'message' => 'Review submitted successfully'
            ], 'Review submitted successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to submit review: ' . $e->getMessage());
        }
    }

    /**
     * Get user favorites (requires authentication)
     */
    public function getFavorites(array $request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $userId = $body['user_id'] ?? null;

            if (!$userId) {
                return Response::error('User authentication required', 401);
            }

            $stmt = $pdo->prepare("
                SELECT f.favorite_id, f.restaurant_id, c.company_name as restaurant_name, c.address, c.phone
                FROM favorites f
                LEFT JOIN companies c ON f.restaurant_id = c.company_id
                WHERE f.user_id = ? AND f.deleted_at IS NULL
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([$userId]);
            $favorites = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($favorites, 'Favorites retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve favorites: ' . $e->getMessage());
        }
    }
}
