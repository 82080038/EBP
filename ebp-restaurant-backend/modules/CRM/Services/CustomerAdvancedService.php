<?php

require_once __DIR__ . '/../Repositories/CustomerRepository.php';
require_once __DIR__ . '/../../../config/database.php';

class CustomerAdvancedService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new CustomerRepository();
        $database = new Database();
        $this->db = $database->connect();
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

    public function addFavoriteProduct($customerId, $productId, $tenantId)
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
