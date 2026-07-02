<?php

if (!class_exists('CommissionRepository')) {
    require_once __DIR__ . '/../Repositories/CommissionRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class CommissionService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new CommissionRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCommission($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['employee_id']) || empty($data['commission_rate']) || empty($data['base_amount']) || empty($data['commission_type'])) {
                return [
                    'success' => false,
                    'message' => 'Employee ID, commission rate, base amount, and commission type are required'
                ];
            }

            $commissionAmount = $data['base_amount'] * ($data['commission_rate'] / 100);
            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $data['commission_amount'] = $commissionAmount;
            
            $commissionId = $this->repository->createCommission($data);

            return [
                'success' => true,
                'message' => 'Commission created successfully',
                'commission_id' => $commissionId,
                'commission_amount' => $commissionAmount
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create commission: ' . $e->getMessage()
            ];
        }
    }

    public function approveCommission($commissionId, $tenantId)
    {
        try {
            $this->repository->updateCommissionStatus($commissionId, 'APPROVED');
            
            return [
                'success' => true,
                'message' => 'Commission approved successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve commission: ' . $e->getMessage()
            ];
        }
    }

    public function payCommission($commissionId, $tenantId)
    {
        try {
            $this->repository->updateCommissionStatus($commissionId, 'PAID');
            
            return [
                'success' => true,
                'message' => 'Commission paid successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to pay commission: ' . $e->getMessage()
            ];
        }
    }

    public function getEmployeeCommissions($tenantId, $branchId, $employeeId, $startDate = null, $endDate = null)
    {
        try {
            $commissions = $this->repository->getEmployeeCommissions($tenantId, $branchId, $employeeId, $startDate, $endDate);
            
            return [
                'success' => true,
                'message' => 'Commissions retrieved successfully',
                'data' => $commissions
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get commissions: ' . $e->getMessage()
            ];
        }
    }

    public function getPendingCommissions($tenantId, $branchId)
    {
        try {
            $commissions = $this->repository->getPendingCommissions($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Pending commissions retrieved successfully',
                'data' => $commissions
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get pending commissions: ' . $e->getMessage()
            ];
        }
    }
}
