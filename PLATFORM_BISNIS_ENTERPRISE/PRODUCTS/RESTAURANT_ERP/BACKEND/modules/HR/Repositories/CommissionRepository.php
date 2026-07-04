<?php



class CommissionRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCommission($data)
    {
        $sql = "INSERT INTO commissions (tenant_id, branch_id, employee_id, commission_type, commission_rate, base_amount, commission_amount, commission_period_start, commission_period_end, reference_type, reference_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['employee_id'],
            $data['commission_type'],
            $data['commission_rate'],
            $data['base_amount'],
            $data['commission_amount'],
            $data['commission_period_start'] ?? null,
            $data['commission_period_end'] ?? null,
            $data['reference_type'] ?? null,
            $data['reference_id'] ?? null,
            $data['status'] ?? 'PENDING'
        ]);
        return $this->db->lastInsertId();
    }

    public function updateCommissionStatus($commissionId, $status)
    {
        $sql = "UPDATE commissions SET status = ? WHERE commission_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $commissionId]);
    }

    public function getEmployeeCommissions($tenantId, $branchId, $employeeId, $startDate = null, $endDate = null)
    {
        $sql = "SELECT * FROM commissions WHERE tenant_id = ? AND branch_id = ? AND employee_id = ? AND deleted_at IS NULL";
        $params = [$tenantId, $branchId, $employeeId];
        
        if ($startDate) {
            $sql .= " AND commission_period_start >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND commission_period_end <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingCommissions($tenantId, $branchId)
    {
        $sql = "SELECT c.*, e.employee_name FROM commissions c JOIN employees e ON c.employee_id = e.employee_id WHERE c.tenant_id = ? AND c.branch_id = ? AND c.status = 'PENDING' AND c.deleted_at IS NULL ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
