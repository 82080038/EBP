<?php

if (!class_exists('CostCenterRepository')) {
    require_once __DIR__ . '/../Repositories/CostCenterRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class CostCenterService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new CostCenterRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCostCenter($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['cost_center_code']) || empty($data['cost_center_name']) || empty($data['cost_center_type'])) {
                return [
                    'success' => false,
                    'message' => 'Cost center code, name, and type are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            
            $costCenterId = $this->repository->createCostCenter($data);

            return [
                'success' => true,
                'message' => 'Cost center created successfully',
                'cost_center_id' => $costCenterId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create cost center: ' . $e->getMessage()
            ];
        }
    }

    public function getCostCenters($tenantId, $branchId)
    {
        try {
            $costCenters = $this->repository->getCostCenters($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Cost centers retrieved successfully',
                'data' => $costCenters
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get cost centers: ' . $e->getMessage()
            ];
        }
    }

    public function getCostCenterReport($tenantId, $branchId, $costCenterId, $dateFrom, $dateTo)
    {
        try {
            $report = $this->repository->getCostCenterReport($tenantId, $branchId, $costCenterId, $dateFrom, $dateTo);
            
            return [
                'success' => true,
                'message' => 'Cost center report retrieved successfully',
                'data' => $report
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get cost center report: ' . $e->getMessage()
            ];
        }
    }

    public function updateCostCenter($costCenterId, $data, $tenantId)
    {
        try {
            $this->repository->updateCostCenter($costCenterId, $data, $tenantId);
            
            return [
                'success' => true,
                'message' => 'Cost center updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update cost center: ' . $e->getMessage()
            ];
        }
    }
}
