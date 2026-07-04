<?php



class MaintenanceRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createAsset($data)
    {
        $sql = "INSERT INTO assets (tenant_id, branch_id, asset_code, asset_name, asset_type, purchase_date, purchase_cost, current_value, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['asset_code'],
            $data['asset_name'],
            $data['asset_type'] ?? null,
            $data['purchase_date'] ?? null,
            $data['purchase_cost'] ?? 0,
            $data['current_value'] ?? 0,
            $data['location'] ?? null,
            $data['status'] ?? 'ACTIVE'
        ]);
        return $this->db->lastInsertId();
    }

    public function createSchedule($data)
    {
        $sql = "INSERT INTO maintenance_schedules (tenant_id, asset_id, schedule_type, scheduled_date, description, status, performed_by, completed_at, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['asset_id'],
            $data['schedule_type'],
            $data['scheduled_date'],
            $data['description'] ?? null,
            $data['status'] ?? 'PENDING',
            $data['performed_by'] ?? null,
            $data['completed_at'] ?? null,
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getAssetsByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT * FROM assets WHERE tenant_id = ? AND branch_id = ? AND deleted_at IS NULL ORDER BY asset_name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT * FROM assets WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY asset_name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSchedulesByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT ms.*, a.asset_name FROM maintenance_schedules ms LEFT JOIN assets a ON ms.asset_id = a.asset_id WHERE ms.tenant_id = ? AND ms.deleted_at IS NULL ORDER BY ms.scheduled_date ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        } else {
            $sql = "SELECT ms.*, a.asset_name FROM maintenance_schedules ms LEFT JOIN assets a ON ms.asset_id = a.asset_id WHERE ms.tenant_id = ? AND ms.deleted_at IS NULL ORDER BY ms.scheduled_date ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSchedule($scheduleId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $scheduleId;
        
        $sql = "UPDATE maintenance_schedules SET " . implode(', ', $setClauses) . " WHERE schedule_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}
