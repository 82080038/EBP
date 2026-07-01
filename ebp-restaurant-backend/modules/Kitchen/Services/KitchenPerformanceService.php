<?php

require_once __DIR__ . '/../Repositories/KitchenPerformanceRepository.php';
require_once __DIR__ . '/../../../config/database.php';

class KitchenPerformanceService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new KitchenPerformanceRepository();
        $database = new Database();
        $this->db = $database->connect();
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
