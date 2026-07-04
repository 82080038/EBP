<?php

if (!class_exists('CustomerRepository')) {
    require_once __DIR__ . '/../Repositories/CustomerRepository.php';
}


class CustomerService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new CustomerRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCustomer($data, $tenantId)
    {
        try {
            // Validate required fields
            if (empty($data['customer_code']) || empty($data['customer_name'])) {
                return [
                    'success' => false,
                    'message' => 'Customer code and name are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $result = $this->repository->create($data);
            
            return [
                'success' => true,
                'message' => 'Customer created successfully',
                'customer_id' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ];
        }
    }

    public function getCustomers($tenantId, $filters = [])
    {
        try {
            $customers = $this->repository->getByTenant($tenantId, $filters);
            
            return [
                'success' => true,
                'message' => 'Customers retrieved successfully',
                'data' => $customers
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get customers: ' . $e->getMessage()
            ];
        }
    }

    public function updateCustomer($customerId, $data, $tenantId)
    {
        try {
            // Check if customer belongs to tenant
            $stmt = $this->db->prepare("SELECT customer_id FROM customers WHERE customer_id = ? AND tenant_id = ?");
            $stmt->execute([$customerId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Customer not found or does not belong to tenant'
                ];
            }

            $this->repository->update($customerId, $data);
            
            return [
                'success' => true,
                'message' => 'Customer updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update customer: ' . $e->getMessage()
            ];
        }
    }

    public function addLoyaltyPoints($customerId, $points, $order_id, $description, $tenantId)
    {
        try {
            // Check if customer belongs to tenant
            $stmt = $this->db->prepare("SELECT customer_id, loyalty_points FROM customers WHERE customer_id = ? AND tenant_id = ?");
            $stmt->execute([$customerId, $tenantId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer) {
                return [
                    'success' => false,
                    'message' => 'Customer not found or does not belong to tenant'
                ];
            }

            // Update customer points
            $newPoints = $customer['loyalty_points'] + $points;
            $this->repository->updateLoyaltyPoints($customerId, $newPoints);

            // Record transaction
            $this->repository->createLoyaltyTransaction([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'transaction_type' => 'EARN',
                'points' => $points,
                'order_id' => $order_id,
                'description' => $description
            ]);

            // Update loyalty tier
            $this->updateLoyaltyTier($customerId, $newPoints);

            return [
                'success' => true,
                'message' => 'Loyalty points added successfully',
                'new_points' => $newPoints
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add loyalty points: ' . $e->getMessage()
            ];
        }
    }

    private function updateLoyaltyTier($customerId, $points)
    {
        $tier = 'BRONZE';
        if ($points >= 10000) {
            $tier = 'PLATINUM';
        } elseif ($points >= 5000) {
            $tier = 'GOLD';
        } elseif ($points >= 2000) {
            $tier = 'SILVER';
        }

        $sql = "UPDATE customers SET loyalty_tier = ? WHERE customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tier, $customerId]);
    }

    public function recordCustomerVisit($customerId, $branchId, $totalSpent, $tenantId)
    {
        try {
            // Check if customer belongs to tenant
            $stmt = $this->db->prepare("SELECT customer_id FROM customers WHERE customer_id = ? AND tenant_id = ?");
            $stmt->execute([$customerId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Customer not found or does not belong to tenant'
                ];
            }

            // Check if visit already exists for today
            $stmt = $this->db->prepare("SELECT visit_id FROM customer_visits WHERE customer_id = ? AND branch_id = ? AND visit_date = CURDATE()");
            $stmt->execute([$customerId, $branchId]);
            $visit = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($visit) {
                // Update existing visit
                $sql = "UPDATE customer_visits SET order_count = order_count + 1, total_spent = total_spent + ? WHERE visit_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$totalSpent, $visit['visit_id']]);
            } else {
                // Create new visit
                $this->repository->createVisit([
                    'customer_id' => $customerId,
                    'branch_id' => $branchId,
                    'visit_date' => date('Y-m-d'),
                    'order_count' => 1,
                    'total_spent' => $totalSpent
                ]);
            }

            // Update customer stats
            $sql = "UPDATE customers SET total_orders = total_orders + 1, total_spent = total_spent + ?, last_order_date = CURDATE() WHERE customer_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$totalSpent, $customerId]);

            return [
                'success' => true,
                'message' => 'Customer visit recorded successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to record visit: ' . $e->getMessage()
            ];
        }
    }
}
