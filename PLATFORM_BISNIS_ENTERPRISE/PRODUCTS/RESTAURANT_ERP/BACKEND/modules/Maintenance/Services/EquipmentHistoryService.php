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
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
