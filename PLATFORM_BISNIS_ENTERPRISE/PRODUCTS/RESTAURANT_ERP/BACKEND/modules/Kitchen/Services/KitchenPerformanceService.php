<?php

if (!class_exists('KitchenPerformanceRepository')) {
    require_once __DIR__ . '/../Repositories/KitchenPerformanceRepository.php';
}


class KitchenPerformanceService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new KitchenPerformanceRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function recordChefPerformance($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['employee_id']) || empty($data['performance_date'])) {
                return [
                    'success' => false,
                    'message' => 'Employee ID and performance date are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            
            $performanceId = $this->repository->createChefPerformance($data);

            return [
                'success' => true,
                'message' => 'Chef performance recorded successfully',
                'performance_id' => $performanceId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to record performance: ' . $e->getMessage()
            ];
        }
    }

    public function getKitchenMetrics($tenantId, $branchId, $dateFrom, $dateTo)
    {
        try {
            $metrics = $this->repository->getKitchenMetrics($tenantId, $branchId, $dateFrom, $dateTo);
            
            return [
                'success' => true,
                'message' => 'Kitchen metrics retrieved successfully',
                'data' => $metrics
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get metrics: ' . $e->getMessage()
            ];
        }
    }

    public function getChefPerformance($tenantId, $branchId, $employeeId, $dateFrom, $dateTo)
    {
        try {
            $performance = $this->repository->getChefPerformance($tenantId, $branchId, $employeeId, $dateFrom, $dateTo);
            
            return [
                'success' => true,
                'message' => 'Chef performance retrieved successfully',
                'data' => $performance
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get chef performance: ' . $e->getMessage()
            ];
        }
    }

    public function getBottleneckAnalysis($tenantId, $branchId, $dateFrom, $dateTo)
    {
        try {
            $analysis = $this->repository->getBottleneckAnalysis($tenantId, $branchId, $dateFrom, $dateTo);
            
            return [
                'success' => true,
                'message' => 'Bottleneck analysis retrieved successfully',
                'data' => $analysis
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get bottleneck analysis: ' . $e->getMessage()
            ];
        }
    }
}
