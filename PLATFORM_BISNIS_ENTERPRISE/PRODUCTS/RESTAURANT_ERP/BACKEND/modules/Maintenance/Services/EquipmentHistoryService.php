<?php

if (!class_exists('WorkOrderRepository')) {
    require_once __DIR__ . '/../Repositories/WorkOrderRepository.php';
}


class EquipmentHistoryService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new WorkOrderRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function addHistory($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['asset_id']) || empty($data['event_type']) || empty($data['event_date'])) {
                return [
                    'success' => false,
                    'message' => 'Asset ID, event type, and event date are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            
            $historyId = $this->repository->addEquipmentHistory($data);

            return [
                'success' => true,
                'message' => 'Equipment history added successfully',
                'history_id' => $historyId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add history: ' . $e->getMessage()
            ];
        }
    }

    public function getEquipmentHistory($tenantId, $branchId, $assetId)
    {
        try {
            $history = $this->repository->getEquipmentHistoryByAsset($tenantId, $branchId, $assetId);
            
            return [
                'success' => true,
                'message' => 'Equipment history retrieved successfully',
                'data' => $history
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get history: ' . $e->getMessage()
            ];
        }
    }
}
