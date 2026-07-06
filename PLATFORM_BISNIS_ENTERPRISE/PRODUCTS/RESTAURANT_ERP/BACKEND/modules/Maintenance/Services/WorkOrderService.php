<?php

if (!class_exists('WorkOrderRepository')) {
    require_once __DIR__ . '/../Repositories/WorkOrderRepository.php';
}


class WorkOrderService
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

    public function createWorkOrder($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['title']) || empty($data['work_order_type'])) {
                return [
                    'success' => false,
                    'message' => 'Title and work order type are required'
                ];
            }

            // Generate work order number
            $workOrderNumber = 'WO-' . date('Ymd') . '-' . rand(1000, 9999);
            
            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $data['work_order_number'] = $workOrderNumber;
            $data['created_by'] = $userId;
            
            $workOrderId = $this->repository->createWorkOrder($data);

            return [
                'success' => true,
                'message' => 'Work order created successfully',
                'work_order_id' => $workOrderId,
                'work_order_number' => $workOrderNumber
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create work order: ' . $e->getMessage()
            ];
        }
    }

    public function updateWorkOrder($workOrderId, $data, $tenantId)
    {
        try {
            $this->repository->updateWorkOrder($workOrderId, $data);

            // If completed, add to equipment history
            if ($data['status'] == 'COMPLETED') {
                $workOrder = $this->repository->getWorkOrders($tenantId, null);
                $completedWO = null;
                foreach ($workOrder as $wo) {
                    if ($wo['work_order_id'] == $workOrderId) {
                        $completedWO = $wo;
                        break;
                    }
                }
                
                if ($completedWO && $completedWO['asset_id']) {
                    $historyData = [
                        'tenant_id' => $tenantId,
                        'branch_id' => $completedWO['branch_id'],
                        'asset_id' => $completedWO['asset_id'],
                        'event_type' => 'MAINTENANCE',
                        'event_date' => $data['completed_date'] ?? date('Y-m-d'),
                        'description' => 'Work Order ' . $completedWO['work_order_number'] . ': ' . $completedWO['title'],
                        'performed_by' => $completedWO['assigned_to'],
                        'cost' => $data['cost'] ?? 0
                    ];
                    $this->repository->addEquipmentHistory($historyData);
                }
            }

            return [
                'success' => true,
                'message' => 'Work order updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update work order: ' . $e->getMessage()
            ];
        }
    }

    public function getWorkOrders($tenantId, $branchId, $status = null)
    {
        try {
            $workOrders = $this->repository->getWorkOrders($tenantId, $branchId, $status);
            
            return [
                'success' => true,
                'message' => 'Work orders retrieved successfully',
                'data' => $workOrders
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get work orders: ' . $e->getMessage()
            ];
        }
    }
}
