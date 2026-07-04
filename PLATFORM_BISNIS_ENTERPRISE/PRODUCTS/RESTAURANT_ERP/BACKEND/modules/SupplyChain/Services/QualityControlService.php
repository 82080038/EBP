<?php

if (!class_exists('QualityControlRepository')) {
    require_once __DIR__ . '/../Repositories/QualityControlRepository.php';
}


class QualityControlService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new QualityControlRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createQualityCheck($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['check_type']) || empty($data['check_date'])) {
                return [
                    'success' => false,
                    'message' => 'Check type and check date are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $data['checked_by'] = $userId;
            
            $checkId = $this->repository->createQualityCheck($data);

            return [
                'success' => true,
                'message' => 'Quality check created successfully',
                'check_id' => $checkId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create quality check: ' . $e->getMessage()
            ];
        }
    }

    public function updateQualityCheckResult($checkId, $data, $tenantId)
    {
        try {
            if (empty($data['status'])) {
                return [
                    'success' => false,
                    'message' => 'Status is required'
                ];
            }

            $this->repository->updateQualityCheckResult($checkId, $data, $tenantId);

            return [
                'success' => true,
                'message' => 'Quality check result updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update result: ' . $e->getMessage()
            ];
        }
    }

    public function getQualityChecks($tenantId, $branchId, $checkType = null, $status = null)
    {
        try {
            $checks = $this->repository->getQualityChecks($tenantId, $branchId, $checkType, $status);
            
            return [
                'success' => true,
                'message' => 'Quality checks retrieved successfully',
                'data' => $checks
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get checks: ' . $e->getMessage()
            ];
        }
    }

    public function getQualityReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        try {
            $report = $this->repository->getQualityReport($tenantId, $branchId, $dateFrom, $dateTo);
            
            return [
                'success' => true,
                'message' => 'Quality report retrieved successfully',
                'data' => $report
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get report: ' . $e->getMessage()
            ];
        }
    }
}
