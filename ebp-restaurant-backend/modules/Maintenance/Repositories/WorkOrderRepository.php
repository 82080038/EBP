<?php

require_once __DIR__ . '/../../../config/database.php';

class WorkOrderRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAssets($tenantId, $branchId)
    {
        $sql = "SELECT * FROM assets WHERE tenant_id = ? AND branch_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEquipmentHistory($tenantId, $branchId)
    {
        $sql = "SELECT * FROM equipment_history WHERE tenant_id = ? AND branch_id = ? ORDER BY event_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createWorkOrder($data)
    {
        $sql = "INSERT INTO work_orders (tenant_id, branch_id, asset_id, work_order_number, work_order_type, priority, title, description, status, assigned_to, due_date, estimated_hours, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['asset_id'] ?? null,
            $data['work_order_number'],
            $data['work_order_type'],
            $data['priority'],
            $data['title'],
            $data['description'] ?? null,
            $data['status'] ?? 'PENDING',
            $data['assigned_to'] ?? null,
            $data['due_date'] ?? null,
            $data['estimated_hours'] ?? null,
            $data['created_by']
        ]);
        return $this->db->lastInsertId();
    }

    public function updateWorkOrder($workOrderId, $data)
    {
        $sql = "UPDATE work_orders SET status = ?, completed_date = ?, actual_hours = ?, cost = ? WHERE work_order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['status'],
            $data['completed_date'] ?? null,
            $data['actual_hours'] ?? null,
            $data['cost'] ?? null,
            $workOrderId
        ]);
    }

    public function getWorkOrders($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT wo.*, a.asset_name, e.employee_name as assigned_to_name, u.username as created_by_name FROM work_orders wo LEFT JOIN assets a ON wo.asset_id = a.asset_id LEFT JOIN employees e ON wo.assigned_to = e.employee_id LEFT JOIN users u ON wo.created_by = u.user_id WHERE wo.tenant_id = ? AND wo.branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($status) {
            $sql .= " AND wo.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY wo.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addEquipmentHistory($data)
    {
        $sql = "INSERT INTO equipment_history (tenant_id, branch_id, asset_id, event_type, event_date, description, performed_by, cost, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['asset_id'],
            $data['event_type'],
            $data['event_date'],
            $data['description'] ?? null,
            $data['performed_by'] ?? null,
            $data['cost'] ?? null,
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getEquipmentHistoryByAsset($tenantId, $branchId, $assetId)
    {
        $sql = "SELECT eh.*, e.employee_name as performed_by_name FROM equipment_history eh LEFT JOIN employees e ON eh.performed_by = e.employee_id WHERE eh.tenant_id = ? AND eh.branch_id = ? AND eh.asset_id = ? ORDER BY eh.event_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $assetId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
