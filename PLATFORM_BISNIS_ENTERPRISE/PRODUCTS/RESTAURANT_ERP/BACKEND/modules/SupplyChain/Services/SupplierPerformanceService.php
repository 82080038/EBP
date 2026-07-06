<?php

if (!class_exists('SupplierPerformanceRepository')) {
    require_once __DIR__ . '/../Repositories/SupplierPerformanceRepository.php';
}


class SupplierPerformanceService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new SupplierPerformanceRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function evaluateSupplier($data, $tenantId, $userId)
    {
        try {
            if (empty($data['supplier_id']) || empty($data['evaluation_date'])) {
                return [
                    'success' => false,
                    'message' => 'Supplier ID and evaluation date are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['evaluated_by'] = $userId;
            
            // Calculate overall rating
            $overallRating = (
                ($data['on_time_delivery_rate'] ?? 0) * 0.4 +
                ($data['quality_score'] ?? 0) * 0.3 +
                ($data['price_competitiveness'] ?? 0) * 0.3
            );
            $data['overall_rating'] = $overallRating;
            
            $performanceId = $this->repository->createPerformance($data);

            return [
                'success' => true,
                'message' => 'Supplier performance evaluated successfully',
                'performance_id' => $performanceId,
                'overall_rating' => $overallRating
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to evaluate supplier: ' . $e->getMessage()
            ];
        }
    }

    public function getSupplierPerformance($tenantId, $supplierId, $dateFrom = null, $dateTo = null)
    {
        try {
            $performance = $this->repository->getSupplierPerformance($tenantId, $supplierId, $dateFrom, $dateTo);
            
            return [
                'success' => true,
                'message' => 'Supplier performance retrieved successfully',
                'data' => $performance
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get performance: ' . $e->getMessage()
            ];
        }
    }

    public function getSupplierRanking($tenantId, $branchId, $dateFrom = null, $dateTo = null)
    {
        try {
            $ranking = $this->repository->getSupplierRanking($tenantId, $branchId, $dateFrom, $dateTo);
            
            return [
                'success' => true,
                'message' => 'Supplier ranking retrieved successfully',
                'data' => $ranking
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get ranking: ' . $e->getMessage()
            ];
        }
    }
}
