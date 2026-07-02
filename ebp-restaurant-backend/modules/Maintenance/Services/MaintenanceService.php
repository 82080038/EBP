<?php

if (!class_exists('MaintenanceRepository')) {
    require_once __DIR__ . '/../Repositories/MaintenanceRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class MaintenanceService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new MaintenanceRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createAsset($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['asset_code']) || empty($data['asset_name'])) {
                return [
                    'success' => false,
                    'message' => 'Asset code and name are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $assetId = $this->repository->createAsset($data);

            return [
                'success' => true,
                'message' => 'Asset created successfully',
                'asset_id' => $assetId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create asset: ' . $e->getMessage()
            ];
        }
    }

    public function createMaintenanceSchedule($data, $tenantId, $userId)
    {
        try {
            if (empty($data['asset_id']) || empty($data['schedule_type']) || empty($data['scheduled_date'])) {
                return [
                    'success' => false,
                    'message' => 'Asset, schedule type, and date are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $scheduleId = $this->repository->createSchedule($data);

            return [
                'success' => true,
                'message' => 'Maintenance schedule created successfully',
                'schedule_id' => $scheduleId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ];
        }
    }

    public function completeMaintenance($scheduleId, $userId, $notes, $tenantId)
    {
        try {
            $this->repository->updateSchedule($scheduleId, [
                'status' => 'COMPLETED',
                'performed_by' => $userId,
                'completed_at' => date('Y-m-d H:i:s'),
                'notes' => $notes
            ]);

            return [
                'success' => true,
                'message' => 'Maintenance completed successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to complete maintenance: ' . $e->getMessage()
            ];
        }
    }

    public function getAssets($tenantId, $branchId = null)
    {
        try {
            $assets = $this->repository->getAssetsByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Assets retrieved successfully',
                'data' => $assets
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get assets: ' . $e->getMessage()
            ];
        }
    }

    public function getSchedules($tenantId, $branchId = null)
    {
        try {
            $schedules = $this->repository->getSchedulesByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Schedules retrieved successfully',
                'data' => $schedules
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get schedules: ' . $e->getMessage()
            ];
        }
    }
}
