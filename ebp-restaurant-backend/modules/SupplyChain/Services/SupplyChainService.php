<?php

if (!class_exists('SupplyChainRepository')) {
    require_once __DIR__ . '/../Repositories/SupplyChainRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class SupplyChainService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new SupplyChainRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createPurchaseRequisition($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['requisition_date'])) {
                return [
                    'success' => false,
                    'message' => 'Requisition date is required'
                ];
            }

            $reqNumber = 'PR-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $reqData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'requisition_number' => $reqNumber,
                'requisition_date' => $data['requisition_date'],
                'requested_by' => $userId,
                'status' => 'PENDING',
                'notes' => $data['notes'] ?? null
            ];

            $reqId = $this->repository->createRequisition($reqData);

            return [
                'success' => true,
                'message' => 'Purchase requisition created successfully',
                'requisition_id' => $reqId,
                'requisition_number' => $reqNumber
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create requisition: ' . $e->getMessage()
            ];
        }
    }

    public function approveRequisition($reqId, $userId, $tenantId)
    {
        try {
            $this->repository->updateRequisition($reqId, [
                'status' => 'APPROVED',
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Requisition approved successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve requisition: ' . $e->getMessage()
            ];
        }
    }

    public function getRequisitions($tenantId, $branchId = null)
    {
        try {
            $requisitions = $this->repository->getByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Requisitions retrieved successfully',
                'data' => $requisitions
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get requisitions: ' . $e->getMessage()
            ];
        }
    }
}
