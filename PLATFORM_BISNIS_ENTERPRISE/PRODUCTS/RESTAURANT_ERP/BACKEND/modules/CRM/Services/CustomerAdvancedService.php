<?php

if (!class_exists('CustomerRepository')) {
    require_once __DIR__ . '/../Repositories/CustomerRepository.php';
}
if (!class_exists('CustomerAdvancedRepository')) {
    require_once __DIR__ . '/../Repositories/CustomerAdvancedRepository.php';
}


class CustomerAdvancedService
{
    private $repository;
    private $advancedRepository;
    private $db;

    public function __construct()
    {
        $this->repository = new CustomerRepository();
        $this->advancedRepository = new CustomerAdvancedRepository();
                $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function updateOrderHistory($customerId, $orderId, $totalAmount, $tenantId)
    {
        try {
            $stmt = $this->db->prepare("SELECT customer_id FROM customers WHERE customer_id = ? AND tenant_id = ?");
            $stmt->execute([$customerId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Customer not found'
                ];
            }

            // Update order count and total spent
            $sql = "UPDATE customers SET total_orders = total_orders + 1, total_spent = total_spent + ?, last_order_date = CURDATE() WHERE customer_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$totalAmount, $customerId]);

            // Update first order date if this is the first order
            $stmt = $this->db->prepare("SELECT first_order_date FROM customers WHERE customer_id = ?");
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer['first_order_date']) {
                $sql = "UPDATE customers SET first_order_date = CURDATE() WHERE customer_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$customerId]);
            }

            // Update average order value
            $this->updateAverageOrderValue($customerId);
            
            // Update customer lifetime value
            $this->updateCustomerLifetimeValue($customerId);

            // Update visit frequency
            $this->updateVisitFrequency($customerId);

            return [
                'success' => true,
                'message' => 'Order history updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update order history: ' . $e->getMessage()
            ];
        }
    }

    private function updateAverageOrderValue($customerId)
    {
        $sql = "UPDATE customers SET average_order_value = total_spent / total_orders WHERE customer_id = ? AND total_orders > 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
    }

    private function updateCustomerLifetimeValue($customerId)
    {
        // CLV = Average Order Value × Purchase Frequency × Customer Lifespan
        // Simplified: CLV = Total Spent (for now)
        $sql = "UPDATE customers SET customer_lifetime_value = total_spent WHERE customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
    }

    private function updateVisitFrequency($customerId)
    {
        $sql = "SELECT 
                    CASE 
                        WHEN DATEDIFF(CURDATE(), first_order_date) <= 7 THEN 'DAILY'
                        WHEN DATEDIFF(CURDATE(), first_order_date) <= 30 THEN 'WEEKLY'
                        WHEN DATEDIFF(CURDATE(), first_order_date) <= 90 THEN 'MONTHLY'
                        ELSE 'RARELY'
                    END as frequency
                 FROM customers WHERE customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $sql = "UPDATE customers SET visit_frequency = ? WHERE customer_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$result['frequency'], $customerId]);
        }
    }

    public function addFavoriteProduct($customerId, $productId, $tenantId, $branchId)
    {
        try {
            $stmt = $this->db->prepare("SELECT favorite_products FROM customers WHERE customer_id = ? AND tenant_id = ?");
            $stmt->execute([$customerId, $tenantId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer) {
                return [
                    'success' => false,
                    'message' => 'Customer not found'
                ];
            }

            $favorites = json_decode($customer['favorite_products'] ?? '[]', true);
            
            if (!in_array($productId, $favorites)) {
                $favorites[] = $productId;
                $sql = "UPDATE customers SET favorite_products = ? WHERE customer_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([json_encode($favorites), $customerId]);
            }

            // Track in customer_favorites table
            $this->advancedRepository->upsertFavorite($tenantId, $branchId, $customerId, $productId);

            return [
                'success' => true,
                'message' => 'Favorite product added successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add favorite: ' . $e->getMessage()
            ];
        }
    }

    public function getCustomerFavorites($tenantId, $branchId, $customerId)
    {
        try {
            $favorites = $this->advancedRepository->getCustomerFavorites($tenantId, $branchId, $customerId);
            
            return [
                'success' => true,
                'message' => 'Customer favorites retrieved successfully',
                'data' => $favorites
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get favorites: ' . $e->getMessage()
            ];
        }
    }

    public function getCustomerHabitAnalysis($tenantId, $branchId, $customerId)
    {
        try {
            $analysis = $this->advancedRepository->getCustomerHabitAnalysis($tenantId, $branchId, $customerId);
            
            return [
                'success' => true,
                'message' => 'Customer habit analysis retrieved successfully',
                'data' => $analysis
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get habit analysis: ' . $e->getMessage()
            ];
        }
    }

    public function createBirthdayPromotion($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['customer_id']) || empty($data['promotion_type'])) {
                return [
                    'success' => false,
                    'message' => 'Customer ID and promotion type are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $data['promotion_year'] = date('Y');
            
            $promotionId = $this->advancedRepository->createBirthdayPromotion($data);

            return [
                'success' => true,
                'message' => 'Birthday promotion created successfully',
                'promotion_id' => $promotionId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create birthday promotion: ' . $e->getMessage()
            ];
        }
    }

    public function getBirthdayPromotions($tenantId, $branchId, $customerId = null)
    {
        try {
            $promotions = $this->advancedRepository->getBirthdayPromotions($tenantId, $branchId, $customerId);
            
            return [
                'success' => true,
                'message' => 'Birthday promotions retrieved successfully',
                'data' => $promotions
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get birthday promotions: ' . $e->getMessage()
            ];
        }
    }

    public function useBirthdayPromotion($promotionId, $tenantId)
    {
        try {
            $this->advancedRepository->useBirthdayPromotion($promotionId, $tenantId);
            
            return [
                'success' => true,
                'message' => 'Birthday promotion used successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to use birthday promotion: ' . $e->getMessage()
            ];
        }
    }

    public function getCustomerLifetimeValue($customerId, $tenantId)
    {
        try {
            $stmt = $this->db->prepare("SELECT customer_lifetime_value, total_spent, total_orders, average_order_value, visit_frequency FROM customers WHERE customer_id = ? AND tenant_id = ?");
            $stmt->execute([$customerId, $tenantId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer) {
                return [
                    'success' => false,
                    'message' => 'Customer not found'
                ];
            }

            return [
                'success' => true,
                'message' => 'CLV retrieved successfully',
                'data' => $customer
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get CLV: ' . $e->getMessage()
            ];
        }
    }
}
