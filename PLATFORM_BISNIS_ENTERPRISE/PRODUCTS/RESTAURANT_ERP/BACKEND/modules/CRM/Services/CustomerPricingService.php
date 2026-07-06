<?php

if (!class_exists('CustomerPricingRepository')) {
    require_once __DIR__ . '/../Repositories/CustomerPricingRepository.php';
}


class CustomerPricingService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new CustomerPricingRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function setCustomerPrice($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['customer_id']) || empty($data['product_id'])) {
                return [
                    'success' => false,
                    'message' => 'Customer ID and product ID are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            
            $existing = $this->repository->getCustomerProductPrice($tenantId, $branchId, $data['customer_id'], $data['product_id']);
            
            if ($existing) {
                $this->repository->updateCustomerPrice($existing['pricing_id'], $data);
                $pricingId = $existing['pricing_id'];
            } else {
                $pricingId = $this->repository->createCustomerPrice($data);
            }

            return [
                'success' => true,
                'message' => 'Customer pricing set successfully',
                'pricing_id' => $pricingId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to set pricing: ' . $e->getMessage()
            ];
        }
    }

    public function getCustomerPrice($tenantId, $branchId, $customerId, $productId)
    {
        try {
            $pricing = $this->repository->getCustomerProductPrice($tenantId, $branchId, $customerId, $productId);
            
            if ($pricing && $pricing['is_active']) {
                $validFrom = $pricing['valid_from'] ?? null;
                $validUntil = $pricing['valid_until'] ?? null;
                $today = date('Y-m-d');
                
                if (($validFrom === null || $today >= $validFrom) && ($validUntil === null || $today <= $validUntil)) {
                    return [
                        'success' => true,
                        'message' => 'Customer pricing retrieved',
                        'data' => $pricing
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'No valid customer pricing found'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get pricing: ' . $e->getMessage()
            ];
        }
    }

    public function getCustomerPricings($tenantId, $branchId, $customerId)
    {
        try {
            $pricings = $this->repository->getCustomerPricings($tenantId, $branchId, $customerId);
            
            return [
                'success' => true,
                'message' => 'Customer pricings retrieved successfully',
                'data' => $pricings
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get pricings: ' . $e->getMessage()
            ];
        }
    }
}
